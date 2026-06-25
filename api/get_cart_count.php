<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['count' => 0]);
}

jsonResponse(['count' => getCartCount($conn, (int)$_SESSION['user_id'])]);
