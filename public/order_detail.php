<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$id = $_GET['id'] ?? 0;
redirect('order/detail.php?id=' . (int)$id);
