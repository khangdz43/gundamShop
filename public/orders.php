<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
redirect('order/history.php');
