-- Migration V3: Mã giảm giá + cột đơn hàng
CREATE TABLE IF NOT EXISTS `coupons` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration V3 hoàn thành!' as status;
