<?php
require_once '../includes/auth.php';
requireEmployee();

$basePath = '../';
$orderId = (int)($_GET['id'] ?? 0);

// 1. Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    redirect('orders.php');
    exit;
}

// 2. Lấy check trạng thái đổi trả đơn hàng để phục vụ logic xử lý POST và hiển thị
$stmt = $conn->prepare("SELECT * FROM order_returns WHERE order_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$returnRequest = $stmt->get_result()->fetch_assoc();
$stmt->close();

$isLockedByReturn = (!empty($returnRequest) && $returnRequest['status'] === 'pending');

// 3. Xử lý cập nhật trạng thái từ Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    // Nếu đơn hàng đang bị khóa do có yêu cầu đổi trả, chặn không cho xử lý POST bẩn từ bên ngoài
    if ($isLockedByReturn) {
        setFlash('order', 'Không thể cập nhật trạng thái khi đơn hàng đang có yêu cầu đổi trả chưa xử lý!');
        redirect('order_detail.php?id=' . $orderId);
        exit;
    }

    $newStatus = $_POST['status'];
    $oldStatus = $order['status'];
    
    if (in_array($newStatus, ['pending','processing','shipped','completed','cancelled'], true) && $newStatus !== $oldStatus) {
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
        exit;
    }
}

// 4. Lấy danh sách sản phẩm trong đơn hàng
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
    <?php if ($flash): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($flash['message']); ?></div>
    <?php endif; ?>

    <h1 class="page-title">ĐƠN HÀNG #<?php echo htmlspecialchars($order['order_code']); ?></h1>

    <div class="checkout-grid">
        <!-- Cột hiển thị sản phẩm -->
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

        <!-- Cột thông tin đơn hàng & Cập nhật -->
        <div class="card">
            <h2 style="margin-top:0">Thông tin & Cập nhật</h2>
            <p><strong>Khách:</strong> <?php echo htmlspecialchars($order['full_name']); ?> (@<?php echo htmlspecialchars($order['username']); ?>)</p>
            <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
            <?php if ($order['note']): ?><p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['note']); ?></p><?php endif; ?>
            
            <p><strong>Trạng thái thực tế:</strong>
                <?php if ($isLockedByReturn): ?>
                    <span class="status-badge status-pending">Yêu cầu đổi trả (Chờ duyệt)</span>
                    <br><small style="color:var(--text-muted);">Trạng thái gốc: <?php echo getOrderStatusLabel($order['status']); ?></small>
                <?php else: ?>
                    <?php if (!empty($returnRequest) && $returnRequest['status'] === 'approved'): ?>
                        <span class="status-badge status-success" style="background:#27ae60; color:white;">Đổi trả hoàn tất</span>
                    <?php elseif (!empty($returnRequest) && $returnRequest['status'] === 'rejected'): ?>
                        <span class="status-badge status-danger" style="background:#c0392b; color:white;">Từ chối đổi trả</span>
                        <br><small style="color:var(--text-muted);">Đơn hàng: <?php echo getOrderStatusLabel($order['status']); ?></small>
                    <?php else: ?>
                        <span class="status-badge <?php echo getOrderStatusClass($order['status']); ?>"><?php echo getOrderStatusLabel($order['status']); ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
            
            <p><strong>Thanh toán:</strong> <?php echo $order['payment_method'] === 'cod' ? 'COD' : 'Chuyển khoản'; ?></p>
            <p><strong>Tổng:</strong> <span style="color:var(--primary-blue);font-size:1.2rem;font-weight:bold"><?php echo formatPrice($order['total']); ?></span></p>

            <!-- Form xử lý trạng thái -->
            <form method="POST" style="margin-top:20px">
                <div class="form-group">
                    <label>Cập nhật trạng thái đơn hàng</label>
                    <select name="status" class="form-control" <?php echo $isLockedByReturn ? 'disabled' : ''; ?>>
                        <?php foreach (['pending','processing','shipped','completed','cancelled'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo getOrderStatusLabel($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <?php if ($isLockedByReturn): ?>
                        <!-- Giữ lại dữ liệu cũ khi select bị disabled -->
                        <input type="hidden" name="status" value="<?php echo $order['status']; ?>">
                        <p style="color:#e74c3c; font-size:0.85rem; margin-top:6px; line-height:1.4;">
                            ⚠️ <strong>Khóa chức năng:</strong> Vui lòng giải quyết yêu cầu đổi trả của khách hàng trước khi thay đổi tiến trình đơn hàng.
                        </p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-blue" style="width:100%" <?php echo $isLockedByReturn ? 'disabled' : ''; ?>><i class="fas fa-save"></i> Lưu trạng thái</button>
            </form>
            
            <!-- Khu vực thông tin khiếu nại (Nếu có) -->
            <?php if (!empty($returnRequest)): ?>
                <div style="margin-top:20px; padding:15px; background:rgba(255,193,7,0.1); border:1px solid #ffc107; border-radius:8px;">
                    <h3 style="margin-top:0; color:#ffc107; font-size:1rem;"><i class="fas fa-undo"></i> Chi tiết yêu cầu đổi trả</h3>
                    <p style="font-size:0.9rem; margin:5px 0;"><strong>Lý do khách điền:</strong> <?php echo htmlspecialchars($returnRequest['reason']); ?></p>
                    <p style="font-size:0.9rem; margin:5px 0;"><strong>Trạng thái xử lý:</strong> 
                        <?php if($returnRequest['status'] === 'pending'): ?> <span style="color:#ffc107; font-weight:bold;">Chờ duyệt</span>
                        <?php elseif($returnRequest['status'] === 'approved'): ?> <span style="color:#27ae60; font-weight:bold;">Đã chấp thuận</span>
                        <?php else: ?> <span style="color:#c0392b; font-weight:bold;">Đã từ chối</span> <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <a href="orders.php" class="btn btn-gray" style="width:100%;margin-top:10px"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>