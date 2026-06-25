<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng', 'redirect' => '../login.php']);
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'add' || !isset($_POST['id'])) {
    jsonResponse(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
}

$product_id = (int)$_POST['id'];
$quantity = max(1, min(99, (int)($_POST['quantity'] ?? 1)));

$stmt = $conn->prepare("SELECT id, stock, name FROM products WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    jsonResponse(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã ngừng bán']);
}

$stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$cartItem = $stmt->get_result()->fetch_assoc();
$stmt->close();

$newQty = $quantity + ($cartItem ? (int)$cartItem['quantity'] : 0);
if ($newQty > $product['stock']) {
    jsonResponse(['success' => false, 'message' => 'Chỉ còn ' . $product['stock'] . ' sản phẩm trong kho']);
}

if ($cartItem) {
    $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
}

$success = $stmt->execute();
$stmt->close();

jsonResponse([
    'success' => $success,
    'message' => $success ? 'Đã thêm vào giỏ hàng' : 'Có lỗi xảy ra',
    'count' => getCartCount($conn, $user_id)
]);
