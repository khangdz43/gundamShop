<?php
require_once 'includes/auth.php';
requireLogin();

if (isAdmin()) redirect('admin/index.php');

ensureCartSelectedColumn($conn);

$userId = getUserId();
$user = getCurrentUser($conn);

if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $cartId = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    $stmt->close();
    setFlash('cart', __('flash_item_removed'));
    redirect('cart.php');
}

if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    setFlash('cart', __('flash_cart_cleared'));
    redirect('cart.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $cartId = (int)$_POST['cart_id'];
    $quantity = max(0, min(99, (int)$_POST['quantity']));

    if ($quantity === 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE cart c JOIN products p ON c.product_id = p.id SET c.quantity = LEAST(?, p.stock) WHERE c.id = ? AND c.user_id = ?");
        $stmt->bind_param("iii", $quantity, $cartId, $userId);
        $stmt->execute();
        $stmt->close();
    }
    redirect('cart.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_selection'])) {
    $selectedIds = array_map('intval', $_POST['selected'] ?? []);
    $stmt = $conn->prepare("UPDATE cart SET selected = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    if (!empty($selectedIds)) {
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $types = str_repeat('i', count($selectedIds)) . 'i';
        $params = array_merge($selectedIds, [$userId]);
        $stmt = $conn->prepare("UPDATE cart SET selected = 1 WHERE id IN ($placeholders) AND user_id = ?");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
    }
    redirect('cart.php');
}

$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, p.old_price, p.is_sale, p.stock 
    FROM cart c JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? ORDER BY c.added_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
$totalItems = 0;
$selectedTotal = 0;
$selectedCount = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
    if (!empty($item['selected'])) {
        $selectedTotal += $item['price'] * $item['quantity'];
        $selectedCount += $item['quantity'];
    }
}

$shipping = calculateShippingFee($selectedTotal);
$cartDiscount = 0;
$cartCouponCode = '';
$cartCouponMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $cartCouponCode = strtoupper(trim($_POST['coupon_code'] ?? ''));
    if ($cartCouponCode !== '') {
        $cr = validateCoupon($conn, $cartCouponCode, $selectedTotal);
        if ($cr['valid']) {
            $cartDiscount = $cr['discount'];
            $cartCouponMsg = __('coupon_applied');
        } else {
            $cartCouponMsg = $cr['message'] ?? __('coupon_invalid');
        }
    } else {
        $cartCouponMsg = __('coupon_invalid');
    }
}
$flash = getFlash('cart');
$pageTitle = __('cart_title') . ' - Gundam Store';
include 'includes/header.php';
?>

<div class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
    <?php endif; ?>

    <h1 class="page-title"><?php echo __('cart_title'); ?></h1>
    <p class="page-subtitle"><?php echo sprintf(__('cart_items_count'), $totalItems); ?></p>

    <?php if (empty($cartItems)): ?>
        <div class="card" style="text-align:center;padding:60px">
            <i class="fas fa-shopping-cart" style="font-size:4rem;color:#555;margin-bottom:20px"></i>
            <h3><?php echo __('cart_empty'); ?></h3>
            <p style="color:var(--text-gray);margin:15px 0 25px"><?php echo __('cart_empty_desc'); ?></p>
            <a href="products.php" class="btn btn-blue"><i class="fas fa-shopping-bag"></i> <?php echo __('shop_now'); ?></a>
        </div>
    <?php else: ?>
        <form method="POST" id="cartSelectionForm">
            <input type="hidden" name="update_selection" value="1">
            <div class="checkout-grid">
                <div class="card">
                    <div class="cart-select-bar">
                        <label class="cart-select-all">
                            <input type="checkbox" id="selectAllCart" <?php echo count(array_filter($cartItems, fn($i) => !empty($i['selected']))) === count($cartItems) ? 'checked' : ''; ?>>
                            <span><?php echo __('cart_select_all'); ?></span>
                        </label>
                        <span style="color:var(--text-muted);font-size:0.85rem;"><?php echo __('cart_select_to_buy'); ?></span>
                    </div>
                    <?php foreach ($cartItems as $item):
                        $itemSubtotal = $item['price'] * $item['quantity'];
                        $isSelected = !empty($item['selected']);
                    ?>
                    <div class="cart-item" data-price="<?php echo $itemSubtotal; ?>" data-qty="<?php echo $item['quantity']; ?>">
                        <label class="cart-item-check">
                            <input type="checkbox" name="selected[]" value="<?php echo $item['id']; ?>" class="cart-item-checkbox" <?php echo $isSelected ? 'checked' : ''; ?>>
                        </label>
                        <img src="assets/images/<?php echo htmlspecialchars($item['image'] ?: 'LOGO.jpg'); ?>" alt="" class="cart-item-img" onerror="this.src='assets/images/LOGO.jpg'">
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="cart-item-price"><?php echo formatPrice($item['price']); ?></div>
                            <?php if ($item['quantity'] > $item['stock']): ?>
                                <span class="stock-badge out-stock" style="margin-top: 5px; display: inline-block;"><?php echo sprintf(__('cart_only_left'), $item['stock']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="cart-qty-adjuster">
                            <?php if ($item['quantity'] <= 1): ?>
                                <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-gray btn-sm" onclick="return confirm('<?php echo addslashes(__('cart_remove_one_confirm')); ?>')">
                                    <i class="fas fa-minus"></i>
                                </a>
                            <?php else: ?>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">
                                    <input type="hidden" name="update_quantity" value="1">
                                    <button type="submit" class="btn btn-gray btn-sm"><i class="fas fa-minus"></i></button>
                                </form>
                            <?php endif; ?>

                            <span style="min-width:30px; text-align:center; font-weight: bold;"><?php echo $item['quantity']; ?></span>

                            <form method="POST" style="display:inline">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="quantity" value="<?php echo min($item['quantity'] + 1, $item['stock']); ?>">
                                <input type="hidden" name="update_quantity" value="1">
                                <button type="submit" class="btn btn-gray btn-sm" <?php echo $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>><i class="fas fa-plus"></i></button>
                            </form>
                        </div>

                        <div class="cart-item-subtotal"><?php echo formatPrice($itemSubtotal); ?></div>
                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" style="color: var(--primary-red); font-size: 1.1rem; padding: 5px;" onclick="return confirm('<?php echo addslashes(__('cart_remove_confirm')); ?>')" title="<?php echo __('cart_clear'); ?>">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="card cart-summary-box">
                    <h2><?php echo __('cart_summary'); ?></h2>
                    <div class="summary-row">
                        <span style="color: var(--text-muted)"><?php echo __('subtotal'); ?></span>
                        <strong id="cartSubtotal"><?php echo formatPrice($selectedTotal); ?></strong>
                    </div>
                    <div class="summary-row" id="cartDiscountRow" style="<?php echo $cartDiscount > 0 ? '' : 'display:none;'; ?>">
                        <span style="color: var(--text-muted)"><?php echo __('discount'); ?></span>
                        <strong id="cartDiscount" style="color:var(--accent-emerald);">-<?php echo formatPrice($cartDiscount); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span style="color: var(--text-muted)"><?php echo __('shipping'); ?></span>
                        <strong id="cartShipping"><?php echo $shipping > 0 ? formatPrice($shipping) : __('free_shipping'); ?></strong>
                    </div>
                    <?php if ($selectedTotal < 2000000 && $selectedTotal > 0): ?>
                        <p id="freeShipNote" style="font-size: 0.8rem; color: var(--text-muted); margin-top: -5px; margin-bottom: 15px;">
                            <?php echo __('free_ship_note'); ?>
                        </p>
                    <?php else: ?>
                        <p id="freeShipNote" style="display:none;font-size: 0.8rem; color: var(--text-muted); margin-top: -5px; margin-bottom: 15px;">
                            <?php echo __('free_ship_note'); ?>
                        </p>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span><?php echo __('grand_total'); ?></span>
                        <strong id="cartGrandTotal"><?php echo formatPrice(max(0, $selectedTotal - $cartDiscount + $shipping)); ?></strong>
                    </div>
                    <form method="POST" style="display:flex;gap:8px;margin-top:15px;">
                        <input type="text" name="coupon_code" class="form-control" placeholder="Mã giảm giá" value="<?php echo htmlspecialchars($cartCouponCode); ?>" style="text-transform:uppercase;flex:1;">
                        <button type="submit" name="apply_coupon" class="btn btn-gray"><?php echo __('apply_coupon'); ?></button>
                    </form>
                    <?php if (!empty($cartCouponMsg)): ?>
                        <p id="cartCouponMsg" style="margin-top:8px;font-size:0.85rem;<?php echo strpos($cartCouponMsg, 'đã') !== false ? 'color:var(--accent-emerald)' : 'color:var(--primary-red)'; ?>"><?php echo htmlspecialchars($cartCouponMsg); ?></p>
                    <?php endif; ?>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 25px;">
                        <button type="button" id="btnCheckout" class="btn btn-blue" style="width:100%"><i class="fas fa-credit-card"></i> <?php echo __('checkout'); ?></button>
                        <a href="products.php" class="btn btn-gray" style="width:100%"><i class="fas fa-arrow-left"></i> <?php echo __('cart_continue'); ?></a>
                        <a href="cart.php?action=clear" class="btn btn-red btn-sm" style="width:100%" onclick="return confirm('<?php echo addslashes(__('cart_clear_confirm')); ?>')"><i class="fas fa-trash"></i> <?php echo __('cart_clear'); ?></a>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php if (!empty($cartItems)): ?>
<script>
(function() {
    var form = document.getElementById('cartSelectionForm');
    var selectAll = document.getElementById('selectAllCart');
    var checkboxes = document.querySelectorAll('.cart-item-checkbox');
    var freeShipThreshold = 2000000;
    var baseShipping = 30000;

    function formatPrice(n) {
        return Math.round(n).toLocaleString('vi-VN') + ' ₫';
    }

    function updateSummary() {
        var subtotal = 0;
        checkboxes.forEach(function(cb) {
            if (cb.checked) {
                var row = cb.closest('.cart-item');
                subtotal += parseFloat(row.dataset.price) || 0;
            }
        });
        var shipping = subtotal >= freeShipThreshold ? 0 : (subtotal > 0 ? baseShipping : 0);
        document.getElementById('cartSubtotal').textContent = formatPrice(subtotal);
        document.getElementById('cartShipping').textContent = shipping > 0 ? formatPrice(shipping) : '<?php echo addslashes(__('free_shipping')); ?>';
        document.getElementById('cartGrandTotal').textContent = formatPrice(subtotal + shipping);
        var note = document.getElementById('freeShipNote');
        if (note) note.style.display = subtotal > 0 && subtotal < freeShipThreshold ? 'block' : 'none';
        if (selectAll) {
            var allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(function(c) { return c.checked; });
            selectAll.checked = allChecked;
        }
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            updateSummary();
            var fd = new FormData(form);
            var base = document.body.dataset.basePath || '';
            fetch(base + 'api/update_cart_selection.php', { method: 'POST', body: fd });
        });
    });

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
            updateSummary();
            var fd = new FormData(form);
            var base = document.body.dataset.basePath || '';
            fetch(base + 'api/update_cart_selection.php', { method: 'POST', body: fd });
        });
    }

    document.getElementById('btnCheckout').addEventListener('click', function() {
        var selected = Array.from(checkboxes).filter(function(c) { return c.checked; });
        if (selected.length === 0) {
            alert('<?php echo addslashes(__('cart_no_selected')); ?>');
            return;
        }
        var ids = selected.map(function(c) { return c.value; }).join(',');
        window.location.href = 'checkout.php?ids=' + encodeURIComponent(ids);
    });

    updateSummary();
})();
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
