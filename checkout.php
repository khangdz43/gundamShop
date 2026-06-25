<?php
require_once 'includes/auth.php';
requireLogin();

if (isAdmin()) redirect('admin/index.php');

$userId = getUserId();
$user = getCurrentUser($conn);

// Get cart
$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, p.stock 
    FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cartItems)) {
    setFlash('checkout', 'Giỏ hàng trống', 'error');
    redirect('cart.php');
}

$subtotal = 0;
foreach ($cartItems as $item) {
    if ($item['quantity'] > $item['stock']) {
        setFlash('checkout', 'Sản phẩm "' . $item['name'] . '" không đủ hàng', 'error');
        redirect('cart.php');
    }
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = calculateShippingFee($subtotal);
$total = $subtotal + $shipping;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $payment = in_array($_POST['payment_method'] ?? '', ['cod', 'bank_transfer']) ? $_POST['payment_method'] : 'cod';

    if (empty($fullName)) $errors[] = 'Vui lòng nhập họ tên';
    if (empty($phone) || !preg_match('/^[0-9]{9,11}$/', $phone)) $errors[] = 'Số điện thoại không hợp lệ';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';
    if (empty($address)) $errors[] = 'Vui lòng nhập địa chỉ giao hàng';

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $orderCode = generateOrderCode();
            $stmt = $conn->prepare("INSERT INTO orders (order_code, user_id, full_name, phone, email, address, note, subtotal, shipping_fee, total, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssssddds", $orderCode, $userId, $fullName, $phone, $email, $address, $note, $subtotal, $shipping, $total, $payment);
            $stmt->execute();
            $orderId = $conn->insert_id;
            $stmt->close();

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

            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
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
            $errors[] = 'Đặt hàng thất bại: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Thanh toán - Gundam Store';
include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title">THANH TOÁN</h1>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div>
    <?php endforeach; ?>

    <div class="checkout-grid">
        <div class="card">
            <h2 style="margin-top:0"><i class="fas fa-shipping-fast"></i> Thông tin giao hàng</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Họ và tên *</label>
                    <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? $user['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Số điện thoại *</label>
                    <input type="tel" name="phone" class="form-control" required value="<?php echo htmlspecialchars($_POST['phone'] ?? $user['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Địa chỉ giao hàng *</label>
                    <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? $user['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['note'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Phương thức thanh toán</label>
                    <select name="payment_method" class="form-control">
                        <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                        <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-blue" style="width:100%"><i class="fas fa-check"></i> Xác nhận đặt hàng</button>
            </form>
        </div>

        <div class="card">
            <h2 style="margin-top:0; border-bottom: 2px solid var(--primary-blue); padding-bottom: 12px;">Đơn hàng (<?php echo count($cartItems); ?> SP)</h2>
            <?php foreach ($cartItems as $item): ?>
            <div class="checkout-item">
                <img src="assets/images/<?php echo htmlspecialchars($item['image'] ?: 'LOGO.jpg'); ?>" class="checkout-item-img" onerror="this.src='assets/images/LOGO.jpg'">
                <div style="flex:1">
                    <div class="checkout-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div style="color:var(--text-muted);font-size:0.85rem">x<?php echo $item['quantity']; ?></div>
                </div>
                <div style="font-weight:700; color:var(--text-white);"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:20px">
                <div class="summary-row">
                    <span style="color: var(--text-muted)">Tạm tính</span>
                    <strong><?php echo formatPrice($subtotal); ?></strong>
                </div>
                <div class="summary-row">
                    <span style="color: var(--text-muted)">Phí vận chuyển</span>
                    <strong><?php echo $shipping ? formatPrice($shipping) : 'Miễn phí'; ?></strong>
                </div>
                <div class="summary-row total">
                    <span>Tổng cộng</span>
                    <strong><?php echo formatPrice($total); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
