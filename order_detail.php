<?php
require_once 'includes/auth.php';
requireLogin();

$orderId = (int)($_GET['id'] ?? 0);
$userId = getUserId();
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) redirect('orders.php');

$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check for return request
$stmt = $conn->prepare("SELECT * FROM order_returns WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$returnRequest = $stmt->get_result()->fetch_assoc();
$stmt->close();

$flash = getFlash('order');
$pageTitle = 'Chi tiết đơn hàng - Gundam Store';
include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title">CHI TIẾT ĐƠN HÀNG</h1>
    <p class="page-subtitle">Mã: <?php echo htmlspecialchars($order['order_code']); ?></p>

    <?php if ($flash): ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" style="margin-bottom: 20px;">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="checkout-grid">
        <div class="card">
            <h2 style="margin-top:0">Sản phẩm</h2>
            <?php foreach ($items as $item): ?>
            <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid var(--border-color)">
                <img src="assets/images/<?php echo htmlspecialchars($item['product_image']); ?>" style="width:80px;height:80px;object-fit:contain;background:#1a1a1a;border-radius:8px;padding:6px">
                <div style="flex:1">
                    <div style="font-weight:600"><?php echo htmlspecialchars($item['product_name']); ?></div>
                    <div style="color:var(--text-gray);font-size:0.9rem"><?php echo formatPrice($item['price']); ?> x <?php echo $item['quantity']; ?></div>
                </div>
                <div style="font-weight:bold"><?php echo formatPrice($item['subtotal']); ?></div>
            </div>
            <?php endforeach; ?>
            
            <?php if ($order['payment_method'] === 'bank_transfer' && in_array($order['status'], ['pending', 'confirmed'])): ?>
            <!-- VietQR block inside customer order detail -->
            <div style="margin-top: 30px; padding: 20px; background: rgba(31, 95, 255, 0.08); border: 2px dashed var(--primary-blue); border-radius: 12px; text-align: center;">
                <h3 style="margin-top: 0; color: white;"><i class="fas fa-qrcode"></i> Quét mã QR thanh toán</h3>
                <p style="color: var(--text-gray); font-size: 0.9rem; margin-bottom: 20px;">Quét mã VietQR dưới đây để thanh toán chuyển khoản nhanh chóng và chính xác.</p>
                <div style="display: inline-block; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); margin-bottom: 15px;">
                    <img src="https://img.vietqr.io/image/vietinbank-113366668888-compact.jpg?amount=<?php echo urlencode($order['total']); ?>&addInfo=<?php echo urlencode($order['order_code']); ?>" alt="VietQR Payment" style="max-width: 250px; display: block; border-radius: 4px;">
                </div>
                <div style="text-align: left; max-width: 350px; margin: 0 auto; font-size: 0.95rem;">
                    <div style="margin-bottom: 5px;"><strong>Ngân hàng:</strong> Vietinbank</div>
                    <div style="margin-bottom: 5px;"><strong>Số tài khoản:</strong> <span style="color: var(--primary-blue); font-weight: bold;">113366668888</span></div>
                    <div style="margin-bottom: 5px;"><strong>Chủ tài khoản:</strong> GUNDAM STORE</div>
                    <div style="margin-bottom: 5px;"><strong>Số tiền:</strong> <strong style="color: var(--primary-blue);"><?php echo formatPrice($order['total']); ?></strong></div>
                    <div><strong>Nội dung CK:</strong> <strong style="color: var(--primary-blue);"><?php echo htmlspecialchars($order['order_code']); ?></strong></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2 style="margin-top:0">Thông tin</h2>
            <p><strong>Trạng thái:</strong> <span class="status-badge <?php echo getOrderStatusClass($order['status']); ?>"><?php echo getOrderStatusLabel($order['status']); ?></span></p>
            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
            <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
            <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
            <?php if ($order['note']): ?><p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['note']); ?></p><?php endif; ?>
            <hr style="border-color:var(--border-color)">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px"><span>Tạm tính</span><span><?php echo formatPrice($order['subtotal']); ?></span></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px"><span>Phí ship</span><span><?php echo formatPrice($order['shipping_fee']); ?></span></div>
            <div style="display:flex;justify-content:space-between;font-size:1.2rem;font-weight:bold"><span>Tổng</span><span style="color:var(--primary-blue)"><?php echo formatPrice($order['total']); ?></span></div>
            
            <?php if ($returnRequest): ?>
                <div style="margin-top:20px; padding:15px; background:rgba(255,255,255,0.05); border-radius:8px; border:1px solid #444; text-align: left;">
                    <h3 style="margin-top:0; color:#ffc107; font-size:1.05rem;"><i class="fas fa-undo"></i> Yêu cầu đổi trả</h3>
                    <p style="margin: 8px 0; font-size:0.9rem;"><strong>Lý do:</strong> <?php echo htmlspecialchars($returnRequest['reason']); ?></p>
                    <p style="margin: 8px 0; font-size:0.9rem;"><strong>Trạng thái:</strong> 
                        <?php if ($returnRequest['status'] === 'pending'): ?>
                            <span class="status-badge status-pending">Đang xử lý</span>
                        <?php elseif ($returnRequest['status'] === 'approved'): ?>
                            <span class="status-badge status-delivered">Đã chấp nhận</span>
                        <?php else: ?>
                            <span class="status-badge status-cancelled">Bị từ chối</span>
                        <?php endif; ?>
                    </p>
                    <?php if ($returnRequest['admin_comment']): ?>
                        <p style="margin: 8px 0; font-size:0.9rem; color:#ffc107;"><strong>Phản hồi:</strong> <?php echo htmlspecialchars($returnRequest['admin_comment']); ?></p>
                    <?php endif; ?>
                </div>
            <?php elseif ($order['status'] === 'delivered'): ?>
                <a href="return_request.php?order_id=<?php echo $order['id']; ?>" class="btn btn-blue" style="width:100%; margin-top:20px; background:#e10600; justify-content: center;"><i class="fas fa-undo"></i> Yêu cầu đổi trả</a>
            <?php endif; ?>
            
            <?php if ($order['status'] === 'pending'): ?>
            <button type="button" id="btnCancelOrder" onclick="confirmCancelOrder(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_code']); ?>')"
                class="btn" style="width:100%;margin-top:12px;background:linear-gradient(135deg,#c0392b,#e74c3c);color:white;border:none;cursor:pointer;justify-content:center;display:flex;align-items:center;gap:8px;font-weight:600;">
                <i class="fas fa-times-circle"></i> Hủy đơn hàng
            </button>
            <?php endif; ?>
            
            <a href="orders.php" class="btn btn-gray" style="width:100%;margin-top:10px; justify-content: center;"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>
    </div>
</div>

<!-- Modal xác nhận hủy đơn -->
<div id="cancelModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card,#1a1a1a);border:1px solid #333;border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.5);animation:modalIn 0.3s ease;">
        <div style="width:70px;height:70px;background:rgba(231,76,60,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;border:2px solid rgba(231,76,60,0.4);">
            <i class="fas fa-exclamation-triangle" style="font-size:2rem;color:#e74c3c;"></i>
        </div>
        <h3 style="color:white;margin:0 0 10px;font-size:1.3rem;">Xác nhận hủy đơn hàng?</h3>
        <p style="color:#aaa;font-size:0.95rem;margin-bottom:8px;">Mã đơn: <strong id="cancelOrderCode" style="color:white;"></strong></p>
        <p style="color:#e74c3c;font-size:0.85rem;margin-bottom:24px;">⚠️ Hành động này không thể hoàn tác. Đơn hàng sẽ bị hủy vĩnh viễn.</p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <button onclick="closeCancelModal()" style="flex:1;padding:12px;background:#333;color:white;border:none;border-radius:8px;cursor:pointer;font-size:0.95rem;font-weight:600;">
                <i class="fas fa-arrow-left"></i> Không, giữ lại
            </button>
            <button onclick="doCancel()" id="confirmCancelBtn" style="flex:1;padding:12px;background:linear-gradient(135deg,#c0392b,#e74c3c);color:white;border:none;border-radius:8px;cursor:pointer;font-size:0.95rem;font-weight:600;">
                <i class="fas fa-times-circle"></i> Xác nhận hủy
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity:0; transform:scale(0.85) translateY(-20px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
</style>

<script>
var _cancelOrderId = 0;

function confirmCancelOrder(orderId, orderCode) {
    _cancelOrderId = orderId;
    document.getElementById('cancelOrderCode').textContent = orderCode;
    var modal = document.getElementById('cancelModal');
    modal.style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

function doCancel() {
    var btn = document.getElementById('confirmCancelBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    var basePath = document.body.dataset.basePath || '';
    var formData = new FormData();
    formData.append('order_id', _cancelOrderId);

    fetch(basePath + 'api/cancel_order.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        closeCancelModal();
        if (data.success) {
            // Hiển thị thông báo thành công rồi reload
            var flash = document.createElement('div');
            flash.className = 'alert alert-success';
            flash.style.cssText = 'position:fixed;top:90px;right:20px;z-index:9998;padding:15px 20px;border-radius:10px;background:rgba(40,167,69,0.9);color:white;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.3);';
            flash.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
            document.body.appendChild(flash);
            setTimeout(function(){ window.location.reload(); }, 1500);
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể hủy đơn hàng'));
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-times-circle"></i> Xác nhận hủy';
        }
    })
    .catch(function() {
        closeCancelModal();
        alert('Có lỗi kết nối. Vui lòng thử lại.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-times-circle"></i> Xác nhận hủy';
    });
}

// Đóng modal khi click bên ngoài
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) closeCancelModal();
});
</script>

<?php include 'includes/footer.php'; ?>
