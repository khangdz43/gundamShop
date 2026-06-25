<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
}

$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$success = $stmt->execute();
$stmt->close();

jsonResponse(['success' => $success, 'count' => 0]);
