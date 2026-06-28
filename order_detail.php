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
$pageTitle = __('order_detail_title') . ' - Gundam Store';
include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title"><?php echo __('order_detail_title'); ?></h1>
    <p class="page-subtitle"><?php echo __('order_code_label'); ?>: <?php echo htmlspecialchars($order['order_code']); ?></p>

    <?php if ($flash): ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" style="margin-bottom: 20px;">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="checkout-grid">
        <div class="card">
            <h2 style="margin-top:0"><?php echo __('order_products'); ?></h2>
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
                <h3 style="margin-top: 0; color: var(--text-main);"><i class="fas fa-qrcode"></i> <?php echo __('qr_payment'); ?></h3>
                <p style="color: var(--text-gray); font-size: 0.9rem; margin-bottom: 20px;"><?php echo __('qr_payment_desc'); ?></p>
                <div style="display: inline-block; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); margin-bottom: 15px;">
                    <img src="https://img.vietqr.io/image/vietinbank-113366668888-compact.jpg?amount=<?php echo urlencode($order['total']); ?>&addInfo=<?php echo urlencode($order['order_code']); ?>" alt="VietQR Payment" style="max-width: 250px; display: block; border-radius: 4px;">
                </div>
                <div style="text-align: left; max-width: 350px; margin: 0 auto; font-size: 0.95rem;">
                    <div style="margin-bottom: 5px;"><strong><?php echo __('bank'); ?>:</strong> Vietinbank</div>
                    <div style="margin-bottom: 5px;"><strong><?php echo __('account_number'); ?>:</strong> <span style="color: var(--primary-blue); font-weight: bold;">113366668888</span></div>
                    <div style="margin-bottom: 5px;"><strong><?php echo __('account_holder'); ?>:</strong> GUNDAM STORE</div>
                    <div style="margin-bottom: 5px;"><strong><?php echo __('amount'); ?>:</strong> <strong style="color: var(--primary-blue);"><?php echo formatPrice($order['total']); ?></strong></div>
                    <div><strong><?php echo __('transfer_content'); ?>:</strong> <strong style="color: var(--primary-blue);"><?php echo htmlspecialchars($order['order_code']); ?></strong></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2 style="margin-top:0"><?php echo __('order_info'); ?></h2>
            <p><strong><?php echo __('status'); ?>:</strong> <span class="status-badge <?php echo getOrderStatusClass($order['status']); ?>"><?php echo getOrderStatusLabel($order['status']); ?></span></p>
            <p><strong><?php echo __('order_date_label'); ?>:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
            <p><strong><?php echo __('order_recipient'); ?>:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
            <p><strong><?php echo __('phone'); ?>:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            <p><strong><?php echo __('email'); ?>:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong><?php echo __('address'); ?>:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
            <?php if ($order['note']): ?><p><strong><?php echo __('order_note'); ?>:</strong> <?php echo htmlspecialchars($order['note']); ?></p><?php endif; ?>
            <hr style="border-color:var(--border-color)">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px"><span><?php echo __('subtotal'); ?></span><span><?php echo formatPrice($order['subtotal']); ?></span></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px"><span><?php echo __('ship_fee'); ?></span><span><?php echo formatPrice($order['shipping_fee']); ?></span></div>
            <div style="display:flex;justify-content:space-between;font-size:1.2rem;font-weight:bold"><span><?php echo __('order_total'); ?></span><span style="color:var(--primary-blue)"><?php echo formatPrice($order['total']); ?></span></div>
            
            <?php if ($returnRequest): ?>
                <div style="margin-top:20px; padding:15px; background:rgba(255,255,255,0.05); border-radius:8px; border:1px solid #444; text-align: left;">
                    <h3 style="margin-top:0; color:#ffc107; font-size:1.05rem;"><i class="fas fa-undo"></i> <?php echo __('return_request'); ?></h3>
                    <p style="margin: 8px 0; font-size:0.9rem;"><strong><?php echo __('return_reason'); ?>:</strong> <?php echo htmlspecialchars($returnRequest['reason']); ?></p>
                    <p style="margin: 8px 0; font-size:0.9rem;"><strong><?php echo __('return_status'); ?>:</strong> 
                        <?php if ($returnRequest['status'] === 'pending'): ?>
                            <span class="status-badge status-pending"><?php echo __('return_processing'); ?></span>
                        <?php elseif ($returnRequest['status'] === 'approved'): ?>
                            <span class="status-badge status-delivered"><?php echo __('return_approved'); ?></span>
                        <?php else: ?>
                            <span class="status-badge status-cancelled"><?php echo __('return_rejected'); ?></span>
                        <?php endif; ?>
                    </p>
                    <?php if ($returnRequest['admin_comment']): ?>
                        <p style="margin: 8px 0; font-size:0.9rem; color:#ffc107;"><strong><?php echo __('return_admin_reply'); ?>:</strong> <?php echo htmlspecialchars($returnRequest['admin_comment']); ?></p>
                    <?php endif; ?>
                </div>
            <?php elseif ($order['status'] === 'delivered'): ?>
                <a href="return_request.php?order_id=<?php echo $order['id']; ?>" class="btn btn-blue" style="width:100%; margin-top:20px; background:#e10600; justify-content: center;"><i class="fas fa-undo"></i> <?php echo __('return_request'); ?></a>
            <?php endif; ?>
            
            <?php if (in_array($order['status'], ['pending', 'confirmed'], true)): ?>
            <button type="button" id="btnCancelOrder" onclick="confirmCancelOrder(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_code']); ?>')"
                class="btn" style="width:100%;margin-top:12px;background:linear-gradient(135deg,#c0392b,#e74c3c);color:white;border:none;cursor:pointer;justify-content:center;display:flex;align-items:center;gap:8px;font-weight:600;">
                <i class="fas fa-times-circle"></i> <?php echo __('cancel_order'); ?>
            </button>
            <?php endif; ?>
            
            <a href="orders.php" class="btn btn-gray" style="width:100%;margin-top:10px; justify-content: center;"><i class="fas fa-arrow-left"></i> <?php echo __('back_to_orders'); ?></a>
        </div>
    </div>
</div>

<!-- Modal xác nhận hủy đơn -->
<div id="cancelModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card,#1a1a1a);border:1px solid #333;border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.5);animation:modalIn 0.3s ease;">
        <div style="width:70px;height:70px;background:rgba(231,76,60,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;border:2px solid rgba(231,76,60,0.4);">
            <i class="fas fa-exclamation-triangle" style="font-size:2rem;color:#e74c3c;"></i>
        </div>
        <h3 style="color:var(--text-main);margin:0 0 10px;font-size:1.3rem;"><?php echo __('cancel_confirm_title'); ?></h3>
        <p style="color:var(--text-muted);font-size:0.95rem;margin-bottom:8px;"><?php echo __('cancel_confirm_code'); ?>: <strong id="cancelOrderCode" style="color:var(--text-main);"></strong></p>
        <p style="color:#e74c3c;font-size:0.85rem;margin-bottom:24px;">⚠️ <?php echo __('cancel_confirm_warning'); ?></p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <button onclick="closeCancelModal()" style="flex:1;padding:12px;background:#333;color:white;border:none;border-radius:8px;cursor:pointer;font-size:0.95rem;font-weight:600;">
                <i class="fas fa-arrow-left"></i> <?php echo __('cancel_keep'); ?>
            </button>
            <button onclick="doCancel()" id="confirmCancelBtn" style="flex:1;padding:12px;background:linear-gradient(135deg,#c0392b,#e74c3c);color:white;border:none;border-radius:8px;cursor:pointer;font-size:0.95rem;font-weight:600;">
                <i class="fas fa-times-circle"></i> <?php echo __('cancel_confirm_btn'); ?>
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
var _cancelProcessing = '<?php echo addslashes(__('cancel_processing')); ?>';
var _cancelConfirmBtn = '<?php echo addslashes(__('cancel_confirm_btn')); ?>';
var _cancelErrorPrefix = '<?php echo addslashes(__('cancel_error')); ?>';
var _cancelErrorConnection = '<?php echo addslashes(__('cancel_error_connection')); ?>';

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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + _cancelProcessing;

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
            alert(_cancelErrorPrefix + ': ' + (data.message || ''));
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-times-circle"></i> ' + _cancelConfirmBtn;
        }
    })
    .catch(function() {
        closeCancelModal();
        alert(_cancelErrorConnection);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-times-circle"></i> ' + _cancelConfirmBtn;
    });
}

// Đóng modal khi click bên ngoài
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) closeCancelModal();
});
</script>

<?php include 'includes/footer.php'; ?>
