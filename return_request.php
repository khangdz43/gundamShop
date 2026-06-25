<?php
require_once 'includes/auth.php';
requireLogin();

if (isAdmin()) redirect('admin/index.php');

$orderId = (int)($_GET['order_id'] ?? 0);
$userId = getUserId();

// Verify order exists, belongs to user, and is delivered
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    redirect('orders.php');
}

if ($order['status'] !== 'delivered') {
    setFlash('order', 'Bạn chỉ có thể yêu cầu đổi trả cho đơn hàng đã giao thành công.', 'error');
    redirect('order_detail.php?id=' . $orderId);
}

// Check if return request already exists
$stmt = $conn->prepare("SELECT * FROM order_returns WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$existingReturn = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existingReturn) {
    setFlash('order', 'Yêu cầu đổi trả cho đơn hàng này đã được gửi trước đó.', 'warning');
    redirect('order_detail.php?id=' . $orderId);
}

$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');
    
    if (empty($reason)) {
        $message = "Vui lòng nhập lý do đổi trả.";
    } else {
        $stmt = $conn->prepare("INSERT INTO order_returns (order_id, user_id, reason, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iis", $orderId, $userId, $reason);
        if ($stmt->execute()) {
            // Log a notification for admin
            $adminNotificationSql = "INSERT INTO notifications (user_id, title, message) VALUES (NULL, 'Yêu cầu đổi trả mới', 'Người dùng @" . $_SESSION['username'] . " yêu cầu đổi trả đơn hàng #" . $order['order_code'] . ".')";
            $conn->query($adminNotificationSql);

            setFlash('order', 'Gửi yêu cầu đổi trả thành công. Chúng tôi sẽ duyệt trong thời gian sớm nhất.');
            redirect('order_detail.php?id=' . $orderId);
        } else {
            $message = "Có lỗi xảy ra, vui lòng thử lại sau.";
        }
        $stmt->close();
    }
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Yêu cầu đổi trả - Gundam Store';
include 'includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 40px auto;">
    <h1 class="page-title">YÊU CẦU ĐỔI TRẢ SẢN PHẨM</h1>
    <p class="page-subtitle">Đơn hàng #<?php echo htmlspecialchars($order['order_code']); ?></p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card">
        <h2 style="margin-top:0"><i class="fas fa-undo"></i> Chi tiết đơn hàng đổi trả</h2>
        <div style="margin-bottom:20px;">
            <?php foreach ($items as $item): ?>
            <div style="display:flex;gap:16px;padding:12px 0;border-bottom:1px solid var(--border-color)">
                <img src="assets/images/<?php echo htmlspecialchars($item['product_image']); ?>" style="width:60px;height:60px;object-fit:contain;background:#1a1a1a;border-radius:6px;padding:4px">
                <div style="flex:1">
                    <div style="font-weight:600"><?php echo htmlspecialchars($item['product_name']); ?></div>
                    <div style="color:var(--text-gray);font-size:0.9rem"><?php echo formatPrice($item['price']); ?> x <?php echo $item['quantity']; ?></div>
                </div>
                <div style="font-weight:bold"><?php echo formatPrice($item['subtotal']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="reason" class="required">Lý do đổi trả sản phẩm</label>
                <textarea id="reason" name="reason" class="form-control" rows="5" required 
                          placeholder="Vui lòng ghi rõ lý do bạn muốn đổi trả sản phẩm này (ví dụ: sản phẩm bị lỗi do nhà sản xuất, sai mẫu mã, hư hỏng trong quá trình vận chuyển...)..."></textarea>
            </div>

            <div style="display:flex; gap:15px; margin-top:20px;">
                <a href="order_detail.php?id=<?php echo $orderId; ?>" class="btn btn-gray" style="flex:1; text-align:center;">Hủy bỏ</a>
                <button type="submit" class="btn btn-blue" style="flex:2;"><i class="fas fa-paper-plane"></i> Gửi yêu cầu</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
