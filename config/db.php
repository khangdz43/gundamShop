<?php
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'gundam_store';
$port = getenv('DB_PORT') ?: 3306;

$conn = new mysqli($servername, $username, $password, $database, (int)$port);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

