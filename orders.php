<?php
require_once 'includes/auth.php';
requireLogin();

if (isAdmin()) redirect('admin/orders.php');

$userId = getUserId();
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Đơn hàng của tôi - Gundam Store';
include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title">ĐƠN HÀNG CỦA TÔI</h1>

    <?php if (empty($orders)): ?>
        <div class="card" style="text-align:center;padding:50px">
            <i class="fas fa-receipt" style="font-size:3rem;color:#555;margin-bottom:15px"></i>
            <p>Bạn chưa có đơn hàng nào.</p>
            <a href="products.php" class="btn btn-blue" style="margin-top:15px">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <div class="card" style="overflow-x:auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo formatPrice($order['total']); ?></td>
                        <td><?php echo $order['payment_method'] === 'cod' ? 'COD' : 'Chuyển khoản'; ?></td>
                        <td><span class="status-badge <?php echo getOrderStatusClass($order['status']); ?>"><?php echo getOrderStatusLabel($order['status']); ?></span></td>
                        <td><a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-blue btn-sm">Chi tiết</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
