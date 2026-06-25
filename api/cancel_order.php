<?php
/**
 * API: Hủy đơn hàng
 * POST /api/cancel_order.php
 * Body: order_id (int)
 * Chỉ hủy được khi status = 'pending'
 */
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ'], 405);
}

$orderId = (int)($_POST['order_id'] ?? 0);
$userId  = getUserId();

if ($orderId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Mã đơn hàng không hợp lệ'], 400);
}

// Kiểm tra đơn hàng thuộc user và đang ở trạng thái pending
$stmt = $conn->prepare("SELECT id, status, order_code FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    jsonResponse(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
}

if ($order['status'] !== 'pending') {
    jsonResponse([
        'success' => false,
        'message' => 'Chỉ có thể hủy đơn hàng đang chờ xác nhận'
    ], 400);
}

// Bắt đầu transaction
$conn->begin_transaction();

try {
    // 1. Cập nhật trạng thái đơn hàng
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $stmt->close();

    // 2. Hoàn lại tồn kho cho từng sản phẩm
    $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($items as $item) {
        $stmtStock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmtStock->bind_param("ii", $item['quantity'], $item['product_id']);
        $stmtStock->execute();
        $stmtStock->close();
    }

    // 3. Ghi thông báo cho user
    $title = "Đơn hàng đã hủy";
    $message = "Đơn hàng #{$order['order_code']} của bạn đã được hủy thành công.";
    $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'info')");
    $stmtNotif->bind_param("iss", $userId, $title, $message);
    $stmtNotif->execute();
    $stmtNotif->close();

    $conn->commit();

    jsonResponse([
        'success' => true,
        'message' => "Đơn hàng #{$order['order_code']} đã được hủy thành công!"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    jsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'], 500);
}
