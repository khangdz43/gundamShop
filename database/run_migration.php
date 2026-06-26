<?php
/**
 * Migration Runner - Chạy file này 1 lần để cập nhật database
 * Truy cập: http://localhost/KNOWLEDGE%20IT/BÁO%20CÁO%20TTDN-MINH/GUNDAM/database/run_migration.php
 */
require_once __DIR__ . '/../config/db.php';

$errors = [];
$success = [];

function runSQL($conn, $sql, $name) {
    global $errors, $success;
    if ($conn->query($sql)) {
        $success[] = "✅ $name";
    } else {
        $errors[] = "❌ $name: " . $conn->error;
    }
}

// 1. Sửa role enum để thêm 'employee'
runSQL($conn, "ALTER TABLE users MODIFY COLUMN `role` enum('user','admin','employee') DEFAULT 'user'", "Cập nhật role enum");

// 2. Thêm cột position
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'position'");
if ($check && $check->num_rows === 0) {
    runSQL($conn, "ALTER TABLE users ADD COLUMN `position` enum('admin','order_manager','return_manager','staff') DEFAULT NULL COMMENT 'Chức vụ nhân viên'", "Thêm cột position");
} else {
    $success[] = "✅ Cột position đã tồn tại";
}

// 3. Cập nhật admin
runSQL($conn, "UPDATE users SET `position` = 'admin' WHERE `role` = 'admin' AND `position` IS NULL", "Cập nhật position cho admin");

// 4. Tạo bảng notifications
runSQL($conn, "CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int NOT NULL AUTO_INCREMENT,
    `user_id` int DEFAULT NULL COMMENT 'NULL = broadcast to all',
    `title` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `type` enum('info','success','warning','error') DEFAULT 'info',
    `is_read` tinyint(1) DEFAULT '0',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "Tạo bảng notifications");

// 5. Tạo bảng notification_reads
runSQL($conn, "CREATE TABLE IF NOT EXISTS `notification_reads` (
    `id` int NOT NULL AUTO_INCREMENT,
    `user_id` int NOT NULL,
    `notification_id` int NOT NULL,
    `read_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_read` (`user_id`,`notification_id`),
    KEY `notification_id` (`notification_id`),
    CONSTRAINT `nr_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `nr_ibfk_2` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "Tạo bảng notification_reads");

// 6. Tạo bảng order_returns
runSQL($conn, "CREATE TABLE IF NOT EXISTS `order_returns` (
    `id` int NOT NULL AUTO_INCREMENT,
    `order_id` int NOT NULL,
    `user_id` int NOT NULL,
    `reason` text NOT NULL,
    `status` enum('pending','approved','rejected') DEFAULT 'pending',
    `admin_comment` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci", "Tạo bảng order_returns");

// 7. Migration V3 - Coupons
runSQL($conn, "CREATE TABLE IF NOT EXISTS `coupons` (
    `id` int NOT NULL AUTO_INCREMENT,
    `code` varchar(50) NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
    `discount_value` decimal(10,2) NOT NULL DEFAULT '0.00',
    `min_order` decimal(12,2) NOT NULL DEFAULT '0.00',
    `max_uses` int DEFAULT NULL,
    `used_count` int NOT NULL DEFAULT '0',
    `starts_at` datetime DEFAULT NULL,
    `expires_at` datetime DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "Tạo bảng coupons");

$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'coupon_code'");
if ($check && $check->num_rows === 0) {
    runSQL($conn, "ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(50) DEFAULT NULL", "Thêm cột orders.coupon_code");
} else {
    $success[] = "✅ Cột orders.coupon_code đã tồn tại";
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'discount_amount'");
if ($check && $check->num_rows === 0) {
    runSQL($conn, "ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(12,2) DEFAULT 0", "Thêm cột orders.discount_amount");
} else {
    $success[] = "✅ Cột orders.discount_amount đã tồn tại";
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Migration V2</title>
    <style>
        body { font-family: monospace; background: #111; color: #eee; padding: 30px; }
        h1 { color: #1f5fff; }
        .success { color: #28a745; margin: 5px 0; }
        .error { color: #dc3545; margin: 5px 0; }
        .done { background: #1f5fff; color: white; padding: 10px 20px; border-radius: 8px; margin-top: 20px; display: inline-block; }
    </style>
</head>
<body>
<h1>🚀 Migration V2 - Gundam Store</h1>
<?php foreach ($success as $s): ?>
    <div class="success"><?php echo htmlspecialchars($s); ?></div>
<?php endforeach; ?>
<?php foreach ($errors as $e): ?>
    <div class="error"><?php echo htmlspecialchars($e); ?></div>
<?php endforeach; ?>
<div class="done">
    ✅ Hoàn thành! <?php echo count($success); ?> thành công, <?php echo count($errors); ?> lỗi.
</div>
<br><br>
<a href="../admin/index.php" style="color:#1f5fff">→ Về Admin Dashboard</a>
</body>
</html>
