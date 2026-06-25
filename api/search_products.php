<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    jsonResponse(['success' => true, 'products' => []]);
}

$like = '%' . $query . '%';
$startsWith = $query . '%';
$limit = 8;

$stmt = $conn->prepare(
    "SELECT id, name, price, old_price, image, type, stock
     FROM products
     WHERE status = 'active'
       AND (name LIKE ? OR description LIKE ? OR series LIKE ? OR category LIKE ? OR type LIKE ?)
     ORDER BY CASE WHEN name LIKE ? THEN 0 ELSE 1 END, id DESC
     LIMIT ?"
);
$stmt->bind_param('ssssssi', $like, $like, $like, $like, $like, $startsWith, $limit);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $image = $row['image'] ?: 'LOGO.jpg';
    if (!is_file(__DIR__ . '/../assets/images/' . $image)) {
        $image = 'LOGO.jpg';
    }

    $products[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'price' => formatPrice($row['price']),
        'old_price' => $row['old_price'] ? formatPrice($row['old_price']) : null,
        'image' => 'assets/images/' . $image,
        'type' => $row['type'],
        'stock' => (int)$row['stock'],
        'url' => 'products_detail.php?id=' . (int)$row['id'],
    ];
}

$stmt->close();
jsonResponse(['success' => true, 'products' => $products]);
