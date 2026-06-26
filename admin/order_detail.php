<?php
require_once '../includes/auth.php';
requireEmployee();

$basePath = '../';
$orderId = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) redirect('orders.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $oldStatus = $order['status'];
    if (in_array($newStatus, ['pending','confirmed','shipping','delivered','cancelled'], true) && $newStatus !== $oldStatus) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
        $stmt->execute();
        $stmt->close();

        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            restockOrderItems($conn, $orderId);
        }

        notifyOrderStatusChange($conn, $order, $oldStatus, $newStatus);

        setFlash('order', 'Cập nhật trạng thái thành công');
        redirect('order_detail.php?id=' . $orderId);
    }
}

$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$flash = getFlash('order');
$pageTitle = 'Chi tiết đơn hàng #' . $order['order_code'];
include '../includes/header.php';
?>

<div class="container">
    <?php if ($flash): ?><div class="alert alert-success"><?php echo htmlspecialchars($flash['message']); ?></div><?php endif; ?>

    <h1 class="page-title">ĐƠN HÀNG #<?php echo htmlspecialchars($order['order_code']); ?></h1>

    <div class="checkout-grid">
        <div class="card">
            <h2 style="margin-top:0">Sản phẩm</h2>
            <?php foreach ($items as $item): ?>
            <div style="display:flex;gap:16px;padding:12px 0;border-bottom:1px solid var(--border-color)">
                <img src="../assets/images/<?php echo htmlspecialchars($item['product_image']); ?>" style="width:70px;height:70px;object-fit:contain;background:#1a1a1a;border-radius:6px">
                <div style="flex:1">
                    <div><?php echo htmlspecialchars($item['product_name']); ?></div>
                    <div style="color:var(--text-gray);font-size:0.85rem"><?php echo formatPrice($item['price']); ?> x <?php echo $item['quantity']; ?></div>
                </div>
                <div style="font-weight:bold"><?php echo formatPrice($item['subtotal']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2 style="margin-top:0">Thông tin & Cập nhật</h2>
            <p><strong>Khách:</strong> <?php echo htmlspecialchars($order['full_name']); ?> (@<?php echo htmlspecialchars($order['username']); ?>)</p>
            <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
            <?php if ($order['note']): ?><p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['note']); ?></p><?php endif; ?>
            <p><strong>Thanh toán:</strong> <?php echo $order['payment_method'] === 'cod' ? 'COD' : 'Chuyển khoản'; ?></p>
            <p><strong>Tổng:</strong> <span style="color:var(--primary-blue);font-size:1.2rem;font-weight:bold"><?php echo formatPrice($order['total']); ?></span></p>

            <form method="POST" style="margin-top:20px">
                <div class="form-group">
                    <label>Cập nhật trạng thái</label>
                    <select name="status" class="form-control">
                        <?php foreach (['pending','confirmed','shipping','delivered','cancelled'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo getOrderStatusLabel($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-blue" style="width:100%"><i class="fas fa-save"></i> Lưu trạng thái</button>
            </form>
            <a href="orders.php" class="btn btn-gray" style="width:100%;margin-top:10px"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
