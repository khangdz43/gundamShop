<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
redirect('../public/order/history.php');
