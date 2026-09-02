<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
redirect('order/success.php' . (!empty($_GET['code']) ? '?code=' . urlencode($_GET['code']) : ''));
