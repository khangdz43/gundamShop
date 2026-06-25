<?php
require_once 'includes/auth.php';
requireLogin();

if (isAdmin()) redirect('admin/index.php');

$userId = getUserId();
$user = getCurrentUser($conn);

// Handle cart actions
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $cartId = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    $stmt->close();
    setFlash('cart', 'Đã xóa sản phẩm khỏi giỏ hàng');
    redirect('cart.php');
}

if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    setFlash('cart', 'Đã xóa tất cả sản phẩm');
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

// Get cart items
$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, p.old_price, p.is_sale, p.stock 
    FROM cart c JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? ORDER BY c.added_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
$totalItems = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

$shipping = calculateShippingFee($total);
$flash = getFlash('cart');
$pageTitle = 'Giỏ hàng - Gundam Store';
include 'includes/header.php';
?>

<div class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
    <?php endif; ?>

    <h1 class="page-title">GIỎ HÀNG</h1>
    <p class="page-subtitle"><?php echo $totalItems; ?> sản phẩm</p>

    <?php if (empty($cartItems)): ?>
        <div class="card" style="text-align:center;padding:60px">
            <i class="fas fa-shopping-cart" style="font-size:4rem;color:#555;margin-bottom:20px"></i>
            <h3>Giỏ hàng trống</h3>
            <p style="color:var(--text-gray);margin:15px 0 25px">Khám phá bộ sưu tập Gunpla của chúng tôi!</p>
            <a href="products.php" class="btn btn-blue"><i class="fas fa-shopping-bag"></i> Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <div class="checkout-grid">
            <div class="card">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <img src="assets/images/<?php echo htmlspecialchars($item['image'] ?: 'LOGO.jpg'); ?>" alt="" class="cart-item-img" onerror="this.src='assets/images/LOGO.jpg'">
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="cart-item-price"><?php echo formatPrice($item['price']); ?></div>
                        <?php if ($item['quantity'] > $item['stock']): ?>
                            <span class="stock-badge out-stock" style="margin-top: 5px; display: inline-block;">Chỉ còn <?php echo $item['stock']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="cart-qty-adjuster">
                        <?php if ($item['quantity'] <= 1): ?>
                            <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-gray btn-sm" onclick="return confirm('Xóa sản phẩm khỏi giỏ hàng?')">
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
                    
                    <div class="cart-item-subtotal"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
                    <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" style="color: var(--primary-red); font-size: 1.1rem; padding: 5px;" onclick="return confirm('Bạn muốn xóa sản phẩm này khỏi giỏ hàng?')" title="Xóa">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card cart-summary-box">
                <h2>Tóm tắt đơn hàng</h2>
                <div class="summary-row">
                    <span style="color: var(--text-muted)">Tạm tính</span>
                    <strong><?php echo formatPrice($total); ?></strong>
                </div>
                <div class="summary-row">
                    <span style="color: var(--text-muted)">Phí vận chuyển</span>
                    <strong><?php echo $shipping > 0 ? formatPrice($shipping) : 'Miễn phí'; ?></strong>
                </div>
                <?php if ($total < 2000000): ?>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: -5px; margin-bottom: 15px;">
                        Miễn phí ship cho đơn từ 2.000.000₫
                    </p>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Tổng cộng</span>
                    <strong><?php echo formatPrice($total + $shipping); ?></strong>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 25px;">
                    <a href="checkout.php" class="btn btn-blue" style="width:100%"><i class="fas fa-credit-card"></i> Thanh toán</a>
                    <a href="products.php" class="btn btn-gray" style="width:100%"><i class="fas fa-arrow-left"></i> Tiếp tục mua</a>
                    <a href="cart.php?action=clear" class="btn btn-red btn-sm" style="width:100%" onclick="return confirm('Xóa tất cả?')"><i class="fas fa-trash"></i> Xóa giỏ hàng</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
