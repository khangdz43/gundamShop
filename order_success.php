<?php
require_once 'includes/auth.php';
requireLogin();

$code = $_GET['code'] ?? '';
if (empty($code)) redirect('orders.php');

$userId = getUserId();
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = ? AND user_id = ?");
$stmt->bind_param("si", $code, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) redirect('orders.php');

$pageTitle = __('order_success_title') . ' - Gundam Store';
include 'includes/header.php';
?>

<div class="container" style="max-width:600px;text-align:center;padding:60px 20px">
    <div class="card">
        <i class="fas fa-check-circle" style="font-size:5rem;color:#28a745;margin-bottom:20px"></i>
        <h1 style="margin:0 0 10px"><?php echo __('order_success_title'); ?></h1>
        <p style="color:var(--text-gray);margin-bottom:25px"><?php echo __('order_success_desc'); ?></p>
        <div style="background:rgba(31,95,255,0.1);padding:16px;border-radius:8px;margin-bottom:25px">
            <div style="color:var(--text-gray);font-size:0.9rem"><?php echo __('order_code_display'); ?></div>
            <div style="font-size:1.5rem;font-weight:800;color:var(--primary-blue)"><?php echo htmlspecialchars($order['order_code']); ?></div>
            <div style="margin-top:10px"><?php echo __('order_total'); ?>: <strong><?php echo formatPrice($order['total']); ?></strong></div>
        </div>
        <?php if ($order['payment_method'] === 'bank_transfer'): ?>
        <div style="margin-top: 25px; margin-bottom: 25px; padding: 20px; background: rgba(31, 95, 255, 0.08); border: 2px dashed var(--primary-blue); border-radius: 12px; text-align: center;">
            <h3 style="margin-top: 0; color: var(--text-main);"><i class="fas fa-qrcode"></i> <?php echo __('qr_payment'); ?></h3>
            <p style="color: var(--text-gray); font-size: 0.9rem; margin-bottom: 15px;"><?php echo __('qr_payment_desc'); ?></p>
            <div style="display: inline-block; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); margin-bottom: 15px;">
                <img src="https://img.vietqr.io/image/vietinbank-113366668888-compact.jpg?amount=<?php echo urlencode($order['total']); ?>&addInfo=<?php echo urlencode($order['order_code']); ?>" alt="VietQR Payment" style="max-width: 220px; display: block; border-radius: 4px;">
            </div>
            <div style="text-align: left; max-width: 320px; margin: 0 auto; font-size: 0.9rem;">
                <div style="margin-bottom: 4px;"><strong><?php echo __('bank'); ?>:</strong> Vietinbank</div>
                <div style="margin-bottom: 4px;"><strong><?php echo __('account_number'); ?>:</strong> <span style="color: var(--primary-blue); font-weight: bold;">113366668888</span></div>
                <div style="margin-bottom: 4px;"><strong><?php echo __('account_holder'); ?>:</strong> GUNDAM STORE</div>
                <div style="margin-bottom: 4px;"><strong><?php echo __('amount'); ?>:</strong> <strong style="color: var(--primary-blue);"><?php echo formatPrice($order['total']); ?></strong></div>
                <div><strong><?php echo __('transfer_content'); ?>:</strong> <strong style="color: var(--primary-blue);"><?php echo htmlspecialchars($order['order_code']); ?></strong></div>
            </div>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-blue"><i class="fas fa-receipt"></i> <?php echo __('view_detail'); ?></a>
            <a href="products.php" class="btn btn-gray"><i class="fas fa-shopping-bag"></i> <?php echo __('continue_shopping'); ?></a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
