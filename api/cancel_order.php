<?php
/**
 * API: Hủy đơn hàng
 * POST /api/cancel_order.php
 * Body: order_id (int)
 * Chỉ hủy được khi status = pending hoặc confirmed (chưa vận chuyển)
 */
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => __('err_login')], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => __('err_invalid_method')], 405);
}

$orderId = (int)($_POST['order_id'] ?? 0);
$userId  = getUserId();

if ($orderId <= 0) {
    jsonResponse(['success' => false, 'message' => __('err_invalid_order')], 400);
}

$stmt = $conn->prepare("SELECT id, status, order_code FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    jsonResponse(['success' => false, 'message' => __('err_order_not_found')], 404);
}

$cancelable = ['pending', 'confirmed'];
if (!in_array($order['status'], $cancelable, true)) {
    jsonResponse([
        'success' => false,
        'message' => __('cancel_only_before_shipping')
    ], 400);
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $stmt->close();

    restockOrderItems($conn, $orderId);

    sendUserNotification(
        $conn,
        $userId,
        __('notif_order_cancelled_by_user'),
        sprintf(__('notif_order_cancelled_by_user_msg'), $order['order_code']),
        'info'
    );

    $conn->commit();

    jsonResponse([
        'success' => true,
        'message' => sprintf(__('cancel_success'), $order['order_code'])
    ]);

} catch (Exception $e) {
    $conn->rollback();
    jsonResponse(['success' => false, 'message' => __('err_generic')], 500);
}
