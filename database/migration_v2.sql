-- ============================================
-- Migration V2 - Gundam Store Nâng cấp
-- Chạy file này 1 lần để cập nhật DB
-- ============================================

-- 1. Thêm cột position vào bảng users
ALTER TABLE users 
    MODIFY COLUMN `role` enum('user','admin','employee') DEFAULT 'user';

ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS `position` 
    enum('admin','order_manager','return_manager','staff') 
    DEFAULT NULL COMMENT 'Chức vụ nhân viên (NULL nếu là user thường)';

-- Cập nhật admin hiện tại
UPDATE users SET `position` = 'admin' WHERE `role` = 'admin';

-- 2. Tạo bảng notifications nếu chưa có
CREATE TABLE IF NOT EXISTS `notifications` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tạo bảng notification_reads (đánh dấu đã đọc broadcast)
CREATE TABLE IF NOT EXISTS `notification_reads` (
    `id` int NOT NULL AUTO_INCREMENT,
    `user_id` int NOT NULL,
    `notification_id` int NOT NULL,
    `read_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_read` (`user_id`,`notification_id`),
    KEY `notification_id` (`notification_id`),
    CONSTRAINT `nr_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `nr_ibfk_2` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tạo bảng order_returns nếu chưa có
CREATE TABLE IF NOT EXISTS `order_returns` (
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
    KEY `user_id` (`user_id`),
    CONSTRAINT `or_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `or_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Hoàn thành migration
SELECT 'Migration V2 hoàn thành!' as status;
