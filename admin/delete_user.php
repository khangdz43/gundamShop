<?php
require_once '../includes/auth.php';
requireAdmin();

$userId = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || $user['role'] === 'admin') {
    redirect('users.php');
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

redirect('users.php');
