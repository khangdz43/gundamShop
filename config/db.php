<?php
$servername = "localhost";
$username = "root";
$password = "123456789";
$database = "gundam_store";

$conn = new mysqli($servername, $username, $password, $database);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
