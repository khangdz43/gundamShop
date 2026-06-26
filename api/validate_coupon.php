<?php
require_once '../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$code = trim($_GET['code'] ?? $_POST['code'] ?? '');
$subtotal = (float)($_GET['subtotal'] ?? $_POST['subtotal'] ?? 0);

$result = validateCoupon($conn, $code, $subtotal);

if (!$result['valid']) {
    jsonResponse(['success' => false, 'message' => $result['message']]);
}

jsonResponse([
    'success' => true,
    'code' => $result['code'],
    'discount' => $result['discount'],
    'discount_formatted' => formatPrice($result['discount']),
    'coupon_id' => $result['coupon_id'],
]);
