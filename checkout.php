<?php
require_once 'includes/auth.php';
requireLogin();

if (isAdmin()) redirect('admin/index.php');

ensureCartSelectedColumn($conn);

$userId = getUserId();
$user = getCurrentUser($conn);

$cartIdsParam = $_GET['ids'] ?? $_POST['cart_ids'] ?? '';
$cartIds = array_filter(array_map('intval', explode(',', $cartIdsParam)));

if (!empty($cartIds)) {
    $placeholders = implode(',', array_fill(0, count($cartIds), '?'));
    $types = str_repeat('i', count($cartIds)) . 'i';
    $params = array_merge($cartIds, [$userId]);
    $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, p.stock 
        FROM cart c JOIN products p ON c.product_id = p.id 
        WHERE c.id IN ($placeholders) AND c.user_id = ?");
    $stmt->bind_param($types, ...$params);
} else {
    $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, p.stock 
        FROM cart c JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND c.selected = 1");
    $stmt->bind_param("i", $userId);
}
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cartItems)) {
    setFlash('checkout', __('checkout_empty'), 'error');
    redirect('cart.php');
}

$checkoutCartIds = array_column($cartItems, 'id');

$subtotal = 0;
foreach ($cartItems as $item) {
    if ($item['quantity'] > $item['stock']) {
        setFlash('checkout', sprintf(__('checkout_stock_error'), $item['name']), 'error');
        redirect('cart.php');
    }
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = calculateShippingFee($subtotal);
$discountAmount = 0;
$appliedCoupon = null;
$couponCodeInput = strtoupper(trim($_POST['coupon_code'] ?? $_GET['coupon'] ?? ''));

if ($couponCodeInput !== '') {
    $couponResult = validateCoupon($conn, $couponCodeInput, $subtotal);
    if ($couponResult['valid']) {
        $discountAmount = $couponResult['discount'];
        $appliedCoupon = $couponResult;
    }
}
$total = max(0, $subtotal - $discountAmount + $shipping);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $payment = in_array($_POST['payment_method'] ?? '', ['cod', 'bank_transfer']) ? $_POST['payment_method'] : 'cod';
    $postCoupon = strtoupper(trim($_POST['coupon_code'] ?? ''));

    if (empty($fullName)) $errors[] = __('err_full_name');
    if (empty($phone) || !preg_match('/^[0-9]{9,11}$/', $phone)) $errors[] = __('err_phone');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = __('err_email');
    if (empty($address)) $errors[] = __('err_address');

    $orderDiscount = 0;
    $orderCouponCode = null;
    $orderCouponId = null;
    if ($postCoupon !== '') {
        $cr = validateCoupon($conn, $postCoupon, $subtotal);
        if ($cr['valid']) {
            $orderDiscount = $cr['discount'];
            $orderCouponCode = $cr['code'];
            $orderCouponId = $cr['coupon_id'];
        } else {
            $errors[] = $cr['message'];
        }
    }
    $orderShipping = calculateShippingFee($subtotal);
    $orderTotal = max(0, $subtotal - $orderDiscount + $orderShipping);

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $orderCode = generateOrderCode();
            ensureCouponsTable($conn);
            $stmt = $conn->prepare("INSERT INTO orders (order_code, user_id, full_name, phone, email, address, note, subtotal, shipping_fee, total, payment_method, coupon_code, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssssdddssd", $orderCode, $userId, $fullName, $phone, $email, $address, $note, $subtotal, $orderShipping, $orderTotal, $payment, $orderCouponCode, $orderDiscount);
            $stmt->execute();
            $orderId = $conn->insert_id;
            $stmt->close();

            if ($orderCouponId) {
                incrementCouponUsage($conn, $orderCouponId);
            }

            foreach ($cartItems as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissdid", $orderId, $item['product_id'], $item['name'], $item['image'], $item['price'], $item['quantity'], $itemSubtotal);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                $stmt->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
                $stmt->execute();
                if ($stmt->affected_rows === 0) throw new Exception('Hết hàng: ' . $item['name']);
                $stmt->close();
            }

            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND id IN (" . implode(',', array_fill(0, count($checkoutCartIds), '?')) . ")");
            $delTypes = 'i' . str_repeat('i', count($checkoutCartIds));
            $delParams = array_merge([$userId], $checkoutCartIds);
            $stmt->bind_param($delTypes, ...$delParams);
            $stmt->execute();
            $stmt->close();

            // Update user profile
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("sssi", $fullName, $phone, $address, $userId);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            redirect('order_success.php?code=' . urlencode($orderCode));
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = sprintf(__('order_failed'), $e->getMessage());
        }
    }
}

$pageTitle = __('checkout_title') . ' - Gundam Store';
include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title"><?php echo __('checkout_title'); ?></h1>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div>
    <?php endforeach; ?>

    <div class="checkout-grid">
        <div class="card">
            <h2 style="margin-top:0"><i class="fas fa-shipping-fast"></i> <?php echo __('shipping_info'); ?></h2>
            <form method="POST">
                <input type="hidden" name="cart_ids" value="<?php echo htmlspecialchars(implode(',', $checkoutCartIds)); ?>">
                <div class="form-group">
                    <label><?php echo __('full_name'); ?> *</label>
                    <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? $user['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo __('phone'); ?> *</label>
                    <input type="tel" name="phone" class="form-control" required value="<?php echo htmlspecialchars($_POST['phone'] ?? $user['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo __('email'); ?> *</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo __('address'); ?> *</label>
                    <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? $user['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo __('note'); ?></label>
                    <textarea name="note" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['note'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo __('payment_method'); ?></label>
                    <select name="payment_method" class="form-control">
                        <option value="cod"><?php echo __('payment_cod'); ?></option>
                        <option value="bank_transfer"><?php echo __('payment_bank'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo __('coupon_code'); ?></label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="coupon_code" id="couponCodeInput" class="form-control" placeholder="VD: GUNDAM10" value="<?php echo htmlspecialchars($couponCodeInput); ?>" style="text-transform:uppercase;">
                        <button type="button" class="btn btn-gray" id="applyCouponBtn" onclick="applyCoupon()"><?php echo __('apply_coupon'); ?></button>
                    </div>
                    <div id="couponMessage" style="margin-top:6px;font-size:0.85rem;color:var(--text-muted);"></div>
                </div>
                <button type="submit" class="btn btn-blue" style="width:100%"><i class="fas fa-check"></i> <?php echo __('confirm_order'); ?></button>
            </form>
        </div>

        <div class="card">
            <h2 style="margin-top:0; border-bottom: 2px solid var(--primary-blue); padding-bottom: 12px;"><?php echo sprintf(__('order_items'), count($cartItems)); ?></h2>
            <?php foreach ($cartItems as $item): ?>
            <div class="checkout-item">
                <img src="assets/images/<?php echo htmlspecialchars($item['image'] ?: 'LOGO.jpg'); ?>" class="checkout-item-img" onerror="this.src='assets/images/LOGO.jpg'">
                <div style="flex:1">
                    <div class="checkout-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div style="color:var(--text-muted);font-size:0.85rem">x<?php echo $item['quantity']; ?></div>
                </div>
                <div style="font-weight:700; color:var(--text-main);"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:20px">
                <div class="summary-row">
                    <span style="color: var(--text-muted)"><?php echo __('subtotal'); ?></span>
                    <strong id="summarySubtotal"><?php echo formatPrice($subtotal); ?></strong>
                </div>
                <div class="summary-row" id="discountRow" style="<?php echo $discountAmount > 0 ? '' : 'display:none;'; ?>">
                    <span style="color: var(--text-muted)"><?php echo __('discount'); ?></span>
                    <strong id="summaryDiscount" style="color:var(--accent-emerald);">-<?php echo formatPrice($discountAmount); ?></strong>
                </div>
                <div class="summary-row">
                    <span style="color: var(--text-muted)"><?php echo __('shipping'); ?></span>
                    <strong id="summaryShipping"><?php echo $shipping ? formatPrice($shipping) : __('free_shipping'); ?></strong>
                </div>
                <div class="summary-row total">
                    <span><?php echo __('grand_total'); ?></span>
                    <strong id="summaryTotal"><?php echo formatPrice($total); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const checkoutSubtotal = <?php echo (float)$subtotal; ?>;
const checkoutShipping = <?php echo (float)$shipping; ?>;

async function applyCoupon() {
    const code = document.getElementById('couponCodeInput').value.trim();
    const msg = document.getElementById('couponMessage');
    if (!code) { msg.textContent = ''; return; }

    try {
        const base = document.body.dataset.basePath || '';
        const res = await fetch(base + 'api/validate_coupon.php?code=' + encodeURIComponent(code) + '&subtotal=' + checkoutSubtotal);
        const data = await res.json();
        if (data.success) {
            const discount = parseFloat(data.discount) || 0;
            const total = Math.max(0, checkoutSubtotal - discount + checkoutShipping);
            document.getElementById('discountRow').style.display = '';
            document.getElementById('summaryDiscount').textContent = '-' + data.discount_formatted;
            document.getElementById('summaryTotal').textContent = total.toLocaleString('vi-VN') + ' ₫';
            msg.style.color = 'var(--accent-emerald)';
            msg.textContent = '<?php echo addslashes(__('coupon_applied')); ?>'.replace('%s', data.code);
        } else {
            document.getElementById('discountRow').style.display = 'none';
            document.getElementById('summaryTotal').textContent = (checkoutSubtotal + checkoutShipping).toLocaleString('vi-VN') + ' ₫';
            msg.style.color = 'var(--primary-red)';
            msg.textContent = data.message || '<?php echo addslashes(__('coupon_invalid')); ?>';
        }
    } catch (e) {
        msg.style.color = 'var(--primary-red)';
        msg.textContent = '<?php echo addslashes(__('coupon_invalid')); ?>';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
