<?php
require_once __DIR__ . '/../includes/auth.php';
$id = $_GET['id'] ?? 0;
redirect('product/detail.php?id=' . (int)$id);
