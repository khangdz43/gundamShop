-- MySQL dump 10.13  Distrib 8.0.29, for Win64 (x86_64)
--
-- Host: localhost    Database: gundam_store
-- ------------------------------------------------------
-- Server version	8.0.29

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `selected` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES (6,17,12,1,'2026-08-03 08:10:41',1),(7,17,16,1,'2026-08-03 08:10:43',1),(8,17,7,1,'2026-08-03 08:10:44',1),(9,17,31,3,'2026-08-03 09:14:23',1);
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Gunpla - Mô Hình Lắp Ráp','gunpla-mo-hinh-lap-rap','Các mẫu Gundam và Mobile Suit nổi tiếng, phù hợp cho người mới lẫn người sưu tầm lâu năm.','2026-07-31 02:28:06'),(2,'Dụng Cụ Mô Hình (Tools)','dung-cu-mo-hinh-tools','Dao cắt, kéo, kìm, bàn chải và các dụng cụ cần thiết cho việc lắp ráp.','2026-07-31 02:28:06'),(3,'Sơn & Phụ Kiện (Paints & Decal)','son-va-phu-kien-paints-decal','Sơn, decal, base, phụ kiện và vật dụng hỗ trợ hoàn thiện mô hình.','2026-07-31 02:28:06'),(4,'Bộ Sưu Tập Limited','bo-suu-tap-limited','Các mẫu giới hạn, phiên bản collector và sự kiện đặc biệt.','2026-08-03 03:18:04'),(5,'Phụ Kiện Trang Trí','phu-kien-trang-tri','Tượng, stand, display case và các món phụ kiện để trưng bày.','2026-07-31 02:44:14'),(6,'Gundam tự do','undam-t-do',NULL,'2026-08-03 03:08:53');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'system, user, assistant',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_chat_messages_session_created` (`session_id`,`created_at`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` VALUES (1,1,17,'user','Gợi ý mô hình cho người mới','2026-08-03 03:29:26'),(2,1,17,'assistant','Minh la tro ly tu van cua Gundam Store HUMG. Ban co the hoi minh ve cach chon HG/RG/MG/PG, san pham cho nguoi moi, giao hang, thanh toan hoac cac mau dang sale.','2026-08-03 03:29:27'),(3,1,17,'user','Gợi ý mô hình cho người mới','2026-08-03 08:48:15'),(4,1,17,'assistant','Minh la tro ly tu van cua Gundam Store HUMG. Ban co the hoi minh ve cach chon HG/RG/MG/PG, san pham cho nguoi moi, giao hang, thanh toan hoac cac mau dang sale.','2026-08-03 08:48:15');
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_sessions`
--

DROP TABLE IF EXISTS `chat_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_chat_sessions_user_updated` (`user_id`,`updated_at`),
  CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_sessions`
--

LOCK TABLES `chat_sessions` WRITE;
/*!40000 ALTER TABLE `chat_sessions` DISABLE KEYS */;
INSERT INTO `chat_sessions` VALUES (1,17,'Tu van Gundam','2026-08-03 02:21:46','2026-08-03 08:48:15');
/*!40000 ALTER TABLE `chat_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'percent' COMMENT 'percent, fixed',
  `discount_value` decimal(10,2) DEFAULT '0.00',
  `min_order` decimal(12,2) DEFAULT '0.00',
  `max_uses` int DEFAULT NULL,
  `used_count` int DEFAULT '0',
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'WELCOME10','Giảm 10% cho đơn hàng đầu tiên','percent',10.00,500000.00,100,0,'2026-01-01 00:00:00','2026-12-31 23:59:59',1,'2026-07-31 02:44:15'),(2,'SUMMER20','Giảm 20% cho đơn hàng mùa hè','percent',20.00,1000000.00,50,0,'2026-06-01 00:00:00','2026-08-31 23:59:59',1,'2026-07-31 02:44:15'),(3,'SHIPFREE','Miễn phí vận chuyển','fixed',30000.00,800000.00,30,0,'2026-07-01 00:00:00','2026-09-30 23:59:59',1,'2026-07-31 02:44:15');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_users`
--

DROP TABLE IF EXISTS `notification_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `notification_id` int NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_notification` (`user_id`,`notification_id`),
  KEY `notification_id` (`notification_id`),
  CONSTRAINT `notification_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notification_users_ibfk_2` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_users`
--

LOCK TABLES `notification_users` WRITE;
/*!40000 ALTER TABLE `notification_users` DISABLE KEYS */;
INSERT INTO `notification_users` VALUES (1,15,4,0,NULL),(2,16,4,0,NULL),(3,17,4,1,'2026-08-03 02:29:41'),(4,17,5,1,'2026-08-03 02:42:49'),(5,5,6,0,NULL),(7,13,6,0,NULL),(8,14,6,0,NULL),(9,15,6,0,NULL),(10,16,6,0,NULL),(11,17,6,1,'2026-08-03 02:44:41'),(12,17,7,1,'2026-08-03 02:47:15'),(13,17,8,1,'2026-08-03 02:47:15'),(14,5,9,0,NULL),(16,13,9,0,NULL),(17,14,9,0,NULL),(18,15,9,0,NULL),(19,16,9,0,NULL),(20,17,9,1,'2026-08-03 02:52:40'),(21,17,10,1,'2026-08-03 02:55:12'),(22,5,11,0,NULL),(24,13,11,0,NULL),(25,14,11,0,NULL),(26,15,11,0,NULL),(27,16,11,0,NULL),(28,17,11,1,'2026-08-03 02:55:12'),(29,17,12,1,'2026-08-03 02:55:12'),(30,17,13,1,'2026-08-03 02:55:12'),(31,17,14,1,'2026-08-03 02:56:07'),(32,17,15,1,'2026-08-03 02:56:13');
/*!40000 ALTER TABLE `notification_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'system' COMMENT 'system, personal, order_update',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'Khuyến mãi tháng 7','Giảm giá cực sốc cho các mẫu Gunpla mới và dụng cụ lắp ráp.','system','2026-07-31 02:44:15'),(2,'Bộ sưu tập mới','Các mẫu Limited Edition đã về cửa hàng, đừng bỏ lỡ.','system','2026-07-31 02:44:15'),(3,'Hỗ trợ giao hàng','Giao hàng nội thành trong 24 giờ khi đặt trước 18h.','order_update','2026-07-31 02:44:15'),(4,'Chương trình ưu đãi 2026','Nếu ai mua 1 được tặng 2 nhé mọi người','system','2026-08-03 02:29:34'),(5,'Cập nhật đơn hàng','Đơn hàng #GD2608036BA86F đã chuyển sang: Hoàn thành','order_update','2026-08-03 02:42:41'),(6,'Yêu cầu đổi trả mới','Người dùng @khang123 yêu cầu đổi trả đơn hàng #GD2608036BA86F.','system','2026-08-03 02:43:19'),(7,'Đơn hàng đã hủy','Đơn hàng #GD2608034213B7 của bạn đã được hủy thành công.','info','2026-08-03 02:44:51'),(8,'Cập nhật đơn hàng','Đơn hàng #GD260803A43A3E đã chuyển sang: Hoàn thành','order_update','2026-08-03 02:45:18'),(9,'Yêu cầu đổi trả mới','Người dùng @khang123 yêu cầu đổi trả đơn hàng #GD260803A43A3E.','system','2026-08-03 02:51:04'),(10,'Cập nhật đơn hàng','Đơn hàng #GD260803FA757C đã chuyển sang: Hoàn thành','order_update','2026-08-03 02:52:58'),(11,'Yêu cầu đổi trả mới','Người dùng @khang123 yêu cầu đổi trả đơn hàng #GD260803FA757C.','system','2026-08-03 02:53:14'),(12,'Cập nhật yêu cầu đổi trả','Yêu cầu đổi trả cho đơn hàng #GD260803FA757C đã được chấp nhận. Phản hồi: Đổi trả thành công','order_return','2026-08-03 02:54:58'),(13,'Cập nhật yêu cầu đổi trả','Yêu cầu đổi trả cho đơn hàng #GD260803A43A3E đã được từ chối. Phản hồi: ','order_return','2026-08-03 02:55:02'),(14,'Cập nhật đơn hàng','Đơn hàng #GD260803AB0D67 đã chuyển sang: Đang xử lý','order_update','2026-08-03 02:56:04'),(15,'Cập nhật đơn hàng','Đơn hàng #GD260803AB0D67 đã chuyển sang: Đang giao hàng','order_update','2026-08-03 02:56:11'),(18,'Khuyến mãi tháng 7','Giảm giá cực sốc cho các mẫu Gunpla mới và dụng cụ lắp ráp.','system','2026-08-03 03:19:29'),(19,'Bộ sưu tập mới','Các mẫu Limited Edition đã về cửa hàng, đừng bỏ lỡ.','system','2026-08-03 03:19:29'),(20,'Hỗ trợ giao hàng','Giao hàng nội thành trong 24 giờ khi đặt trước 18h.','order_update','2026-08-03 03:19:29'),(21,'Khuyến mãi tháng 7','Giảm giá cực sốc cho các mẫu Gunpla mới và dụng cụ lắp ráp.','system','2026-08-03 03:19:50'),(22,'Bộ sưu tập mới','Các mẫu Limited Edition đã về cửa hàng, đừng bỏ lỡ.','system','2026-08-03 03:19:50'),(23,'Hỗ trợ giao hàng','Giao hàng nội thành trong 24 giờ khi đặt trước 18h.','order_update','2026-08-03 03:19:50'),(24,'Khuyến mãi tháng 7','Giảm giá cực sốc cho các mẫu Gunpla mới và dụng cụ lắp ráp.','system','2026-08-03 03:20:13'),(25,'Bộ sưu tập mới','Các mẫu Limited Edition đã về cửa hàng, đừng bỏ lỡ.','system','2026-08-03 03:20:13'),(26,'Hỗ trợ giao hàng','Giao hàng nội thành trong 24 giờ khi đặt trước 18h.','order_update','2026-08-03 03:20:13'),(27,'Khuyến mãi tháng 7','Giảm giá cực sốc cho các mẫu Gunpla mới và dụng cụ lắp ráp.','system','2026-08-03 03:23:08'),(28,'Bộ sưu tập mới','Các mẫu Limited Edition đã về cửa hàng, đừng bỏ lỡ.','system','2026-08-03 03:23:08'),(29,'Hỗ trợ giao hàng','Giao hàng nội thành trong 24 giờ khi đặt trước 18h.','order_update','2026-08-03 03:23:08'),(30,'Khuyến mãi tháng 7','Giảm giá cực sốc cho các mẫu Gunpla mới và dụng cụ lắp ráp.','system','2026-08-03 03:23:52'),(31,'Bộ sưu tập mới','Các mẫu Limited Edition đã về cửa hàng, đừng bỏ lỡ.','system','2026-08-03 03:23:52'),(32,'Hỗ trợ giao hàng','Giao hàng nội thành trong 24 giờ khi đặt trước 18h.','order_update','2026-08-03 03:23:52');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (4,4,18,'Kyrios','kyrios_mg.jpg',4590000.00,5,22950000.00),(5,5,18,'Kyrios','kyrios_mg.jpg',4590000.00,1,4590000.00),(6,6,7,'RX-78-2 Gundam','RX-78-2.jpg',2490000.00,2,4980000.00),(7,7,17,'Dynames','dynames_hg.jpg',2190000.00,1,2190000.00),(8,8,14,'Sazabi Ver.Ka','Sazabi Ver.Ka.jpg',6890000.00,1,6890000.00),(14,1,20,'HG 1/144 Char Zaku II Red','char_zaku_red_hg.jpg',450000.00,1,450000.00),(15,14,24,'RG 1/144 Gundam Epyon','epyon_rg.jpg',1150000.00,1,1150000.00),(16,3,28,'PG Unleashed 1/60 RX-78-2 Gundam','PG Unleashed.jpg',7850000.00,1,7850000.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_returns`
--

DROP TABLE IF EXISTS `order_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_returns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'pending, approved, rejected',
  `admin_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_returns_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_returns`
--

LOCK TABLES `order_returns` WRITE;
/*!40000 ALTER TABLE `order_returns` DISABLE KEYS */;
INSERT INTO `order_returns` VALUES (1,6,'tôi thấy ko còn hứng thú nữa','pending',NULL,'2026-08-03 02:43:19','2026-08-03 02:43:19'),(2,4,'f','rejected','','2026-08-03 02:51:04','2026-08-03 02:55:02'),(3,7,'không cần nữa','approved','Đổi trả thành công','2026-08-03 02:53:14','2026-08-03 02:54:58');
/*!40000 ALTER TABLE `order_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'cod' COMMENT 'cod, vnpay, momo',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'pending, processing, shipped, completed, cancelled',
  `coupon_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_status` (`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'GD260701A1',15,'Nguyễn Văn An','0905555555','customer1@gmail.com','Bắc Ninh','Giao giờ hành chính',2490000.00,30000.00,249000.00,2520000.00,'cod','completed','WELCOME10','2026-07-31 02:44:15','2026-08-03 03:23:52'),(3,'GD260710C3',15,'Nguyễn Văn An','0905555555','customer1@gmail.com','Đà Nẵng','Cho vào hộp đỏ',5890000.00,35000.00,30000.00,5925000.00,'vnpay','pending','SHIPFREE','2026-07-31 02:44:15','2026-08-03 03:23:52'),(4,'GD260803A43A3E',17,'Nguyễn Hữu Khang','0357765793','khangkutr634e2@gmail.com','An mỹ mỹ đức hà nội','Giao tới gọi sdt tôi trước nhé',22950000.00,0.00,2295000.00,20655000.00,'cod','completed','WELCOME10','2026-08-03 02:23:38','2026-08-03 02:45:18'),(5,'GD2608034213B7',17,'Nguyễn Hữu Khang','0357765793','khangkutr634e2@gmail.com','An mỹ mỹ đức hà nội','',4590000.00,0.00,0.00,4590000.00,'cod','cancelled',NULL,'2026-08-03 02:25:24','2026-08-03 02:44:51'),(6,'GD2608036BA86F',17,'Nguyễn Hữu Khang','0357765793','khangkutr634e2@gmail.com','An mỹ mỹ đức hà nội','',4980000.00,0.00,0.00,4980000.00,'bank_transfer','completed',NULL,'2026-08-03 02:25:42','2026-08-03 02:42:41'),(7,'GD260803FA757C',17,'Nguyễn Hữu Khang','0357765793','khangkutr634e2@gmail.com','An mỹ mỹ đức hà nội','',2190000.00,0.00,0.00,2190000.00,'cod','cancelled',NULL,'2026-08-03 02:52:47','2026-08-03 02:54:58'),(8,'GD260803AB0D67',17,'Nguyễn Hữu Khang','0357765793','khangkutr634e2@gmail.com','An mỹ mỹ đức hà nội','',6890000.00,0.00,0.00,6890000.00,'cod','cancelled',NULL,'2026-08-03 02:55:54','2026-08-03 02:56:27'),(14,'GD260702B2',16,'Trần Thị Bình','0906666666','customer2@gmail.com','Hải Phòng','Gọi trước khi giao',3990000.00,30000.00,798000.00,4020000.00,'momo','processing','SUMMER20','2026-08-03 03:23:08','2026-08-03 03:23:52');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `old_price` decimal(12,2) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'models_default_img.jpeg',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `grade` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'HG' COMMENT 'HG, RG, MG, PG, EG, SD, MGEX, Other',
  `series` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int DEFAULT '50',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_sale` tinyint(1) DEFAULT '0',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT 'active, inactive, out_of_stock',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `idx_products_status` (`status`),
  KEY `idx_products_grade` (`grade`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (7,'RX-78-2 Gundam','rx-78-2-gundam',2500000.00,2990000.00,1,'RX-78-2.jpg','M? h?nh Gundam RX-78-2 phi?n b?n High Grade v?i chi ti?t l?p r?p ??p m?t, m?u s?c trung t?nh v? ?? chi ti?t cao, ph? h?p cho ng??i m?i b?t ??u l?n ng??i s?u t?m l?u n?m.','HG','Universal Century','1/144',22,1,1,'active','2026-07-31 02:38:02','2026-08-03 03:08:16'),(8,'Zaku II Green','zaku-ii-green',1890000.00,2190000.00,1,'Zaku II Green.jpg','Phi?n b?n Zaku II m?u xanh l? n?i b?t, n?i b?t v?i ???ng n?t c?ng c?p v? c?c chi ti?t v? kh? ??y c?m h?ng chi?n ??u.','HG','Universal Century','1/144',18,0,1,'active','2026-07-31 02:38:02','2026-07-31 02:38:02'),(9,'Gundam Exia','gundam-exia',2290000.00,2690000.00,1,'Gundam Exia.jpg','Gundam Exia l? l?a ch?n ho?n h?o cho fan 00 v?i thi?t k? ??n gi?n nh?ng v?n r?t m?nh m?, n?i b?t trong b? s?u t?p.','HG','Mobile Suit Gundam 00','1/144',20,1,0,'active','2026-07-31 02:38:02','2026-07-31 02:38:02'),(10,'00 Raiser','00-raiser',3590000.00,3990000.00,1,'00_raiser_rg.jpg','M? h?nh 00 Raiser Real Grade mang ??n ?? chi ti?t cao v? c?u tr?c l?p r?p tinh x?o trong phong c?ch chi?n ??u c?a 00.','RG','Mobile Suit Gundam 00','1/144',12,1,1,'active','2026-07-31 02:38:02','2026-07-31 02:38:02'),(11,'Strike Freedom Gundam','strike-freedom-gundam',3990000.00,4490000.00,1,'Strike Freedom Gundam.png','Strike Freedom Gundam l? m?u n?i b?t c?a Cosmic Era v?i c?nh ?u?i h?nh d?ng ??c tr?ng v? c?c chi ti?t m?u s?c n?i b?t.','RG','Gundam Seed Destiny','1/144',14,1,0,'active','2026-07-31 02:38:02','2026-07-31 02:38:02'),(12,'Nu Gundam','nu-gundam',4290000.00,4790000.00,1,'Nu Gundam (RG).jpg','Nu Gundam Real Grade th? hi?n s? m?nh m? v? ??i m?i trong thi?t k? c?a d?ng Universal Century v?i c?c ???ng n?t s?c n?t.','RG','Universal Century','1/144',10,0,1,'active','2026-07-31 02:38:02','2026-07-31 02:38:02'),(13,'Unicorn Gundam','unicorn-gundam',5890000.00,6690000.00,1,'Unicorn Gundam.jpg','M? h?nh Unicorn Gundam Master Grade c? c?u tr?c ch?n ?? v? chi ti?t b?c thang cao, r?t ph? h?p cho ng??i y?u th?ch c?c m?u chi?n ??u n?i ti?ng.','MG','Universal Century','1/100',8,1,0,'active','2026-07-31 02:38:02','2026-07-31 02:38:02'),(14,'Sazabi Ver.Ka','sazabi-ver-ka',6890000.00,7490000.00,1,'Sazabi Ver.Ka.jpg','Sazabi Ver.Ka l? m?t trong nh?ng m?u Gunpla ??ng c?p v?i ki?u d?ng h?ng v? v? ?? chi ti?t ?n t??ng trong ph?n kh?c Master Grade.','MG','Universal Century','1/100',6,1,1,'active','2026-07-31 02:38:02','2026-08-03 02:56:27'),(15,'Astray Blue Frame','astray-blue-frame',8990000.00,9990000.00,1,'astray_blue_pg.jpg','Astray Blue Frame Perfect Grade r?t l?nh, ??m ch?t chi?n binh v?i c?u tr?c ??c ??o v? ?? ho?n thi?n c?c cao.','PG','Gundam Seed','1/60',5,1,0,'active','2026-07-31 02:38:02','2026-07-31 02:38:02'),(16,'Astray Red Frame','astray-red-frame',4990000.00,5490000.00,1,'astray_red_mg.jpg','Astray Red Frame Master Grade mang ??n c?m gi?c nhanh nh?n v? m?nh m? v?i c?c chi ti?t ph?n c?nh v? v? kh? s?c n?t.','MG','Gundam Seed','1/100',9,0,1,'active','2026-07-31 02:38:02','2026-07-31 02:38:02'),(17,'Dynames','dynames',2190000.00,2490000.00,1,'dynames_hg.jpg','Dynames v?i thi?t k? c? ??ng v? m?u s?c t?i gi?n, ph? h?p cho nh?ng ai th?ch b? s?u t?p Gunpla 00 theo phong c?ch hi?n ??i.','HG','Mobile Suit Gundam 00','1/144',16,0,0,'active','2026-07-31 02:38:02','2026-08-03 02:54:58'),(18,'Kyrios','kyrios',4590000.00,4990000.00,1,'kyrios_mg.jpg','Kyrios Master Grade s? h?u ???ng n?t tinh t? v? c?u tr?c l?p r?p ??p, l?m n?i b?t phong c?ch chi?n ??u c?a Gundam 00.','MG','Mobile Suit Gundam 00','1/100',2,1,0,'active','2026-07-31 02:38:02','2026-08-03 02:44:51'),(19,'RX-TT66',NULL,350000.00,600000.00,2,'6a7003dfb64dd_1785725919.jpeg','Gundam thế hệ mới , dòng chính hãng mang sức mạnh tuyệt đối của dòng RX-TT66 bền bỉ kĩ thuật','MG','',NULL,50,0,1,'active','2026-08-03 02:58:39','2026-08-03 02:58:39'),(20,'HG 1/144 Char Zaku II Red','hg-1-144-char-zaku-ii-red',450000.00,NULL,1,'char_zaku_red_hg.jpg','Mô hình Zaku II màu đỏ đặc trưng của Char Aznable. Khớp nối linh hoạt, kèm đầy đủ vũ khí cơ bản.','HG',NULL,NULL,25,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(21,'HG 1/144 Gundam Lfrith Ur','hg-1-144-gundam-lfrith-ur',520000.00,NULL,1,'lfrith_ur_hg.jpg','Mô hình thuộc series The Witch from Mercury với thiết kế hầm hố, trang bị khẩu Gatling Gun cỡ lớn.','HG',NULL,NULL,15,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(22,'HGUC 1/144 Nightingale','hguc-1-144-nightingale',1850000.00,NULL,1,'Nightingale (HG).jpg','Mẫu HG kích thước siêu khủng, chi tiết giáp vai và phần đuôi được tạo hình vô cùng sắc nét.','HG',NULL,NULL,8,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(23,'RG 1/144 Crossbone Gundam X1','rg-1-144-crossbone-gundam-x1',750000.00,NULL,1,'crossbone_rg.jpg','Công nghệ Advanced MS Joint cho khung xương siêu nhỏ gọn, chi tiết áo khoác ABC Cloak ấn tượng.','HG',NULL,NULL,18,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(24,'RG 1/144 Gundam Epyon','rg-1-144-gundam-epyon',1150000.00,NULL,1,'epyon_rg.jpg','Khả năng biến hình sang dạng MA mượt mà, roi Heat Rod khớp linh hoạt cực cao.','HG',NULL,NULL,12,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(25,'MG 1/100 MS-07B-3 Gouf Custom','mg-1-100-gouf-custom',980000.00,NULL,1,'Gouf Custom (MG).jpg','Huyền thoại từ 08th MS Team với khẩu Gatling Shield hầm hố và dây Heat Rod cáp dẻo.','HG',NULL,NULL,10,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(26,'MG 1/100 Sazabi Ver.Ka','mg-1-100-sazabi-ver-ka',2450000.00,NULL,1,'Sazabi Ver.Ka.jpg','Kiệt tác thiết kế từ Katoki Hajime với cơ chế mở giáp lộ khung xương cơ khí chi tiết đỉnh cao.','HG',NULL,NULL,6,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(27,'MG 1/100 Gundam Virtue','mg-1-100-gundam-virtue',2100000.00,NULL,1,'virtue_mg.jpg','Tích hợp lớp giáp dày đặc bên ngoài và có thể tháo rời hoàn toàn để biến thành GN-004 Nadleeh.','HG',NULL,NULL,9,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(28,'PG Unleashed 1/60 RX-78-2 Gundam','pg-unleashed-1-60-rx-78-2-gundam',7850000.00,NULL,4,'PG Unleashed.jpg','Đỉnh cao công nghệ Gunpla với cấu trúc khung xương 5 lớp, hệ thống LED tích hợp và kim loại đúc.','HG',NULL,NULL,3,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(29,'PG 1/60 Strike Freedom Gundam','pg-1-60-strike-freedom-gundam',6900000.00,NULL,4,'strike_freedom_pg.jpg','Mô hình tỉ lệ 1/60 khổng lồ, bộ cánh Super DRAGOON xòe rộng với các chi tiết mạ vàng sang trọng.','HG',NULL,NULL,4,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(30,'SD EX-Standard Unicorn Gundam','sd-ex-standard-unicorn-gundam',180000.00,NULL,1,'SD Unicorn.jpg','Dòng SD nhỏ gọn, tỉ lệ chibi đáng yêu, lắp ráp nhanh chóng, thích hợp trưng bày.','HG',NULL,NULL,30,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28'),(31,'SD BB Senshi Knight Unicorn Gundam','sd-bb-senshi-knight-unicorn-gundam',320000.00,NULL,1,'sd_knight_unicorn.jpg','Phiên bản hiệp sĩ huyền thoại với bộ giáp bạc mạ bóng và khả năng biến hình mặt nạ độc đáo.','HG',NULL,NULL,15,0,0,'active','2026-08-03 03:19:28','2026-08-03 03:19:28');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_chk_1` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,11,5,5,'Mẫu Gundam đẹp, đóng rất dễ và màu sắc bắt mắt.','2026-07-31 02:44:15'),(3,13,5,5,'Một mẫu Master Grade rất ấn tượng, đáng mua.','2026-07-31 02:44:15'),(5,15,5,5,'Mẫu Perfect Grade rất đẹp và chất lượng tuyệt vời.','2026-07-31 02:44:15'),(7,7,17,5,'Rất ok','2026-08-03 03:24:20');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `role` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user' COMMENT 'user, admin, staff',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `remember_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_expires` datetime DEFAULT NULL,
  `position` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (5,'admin','$2y$10$MsfigA2y6GafoycHCysJIed.UP5q8w9ES8j8psCLl9UbCnCp63iiS','admin@gundamstore.vn','Quản trị viên','0901111111','Hà Nội','admin',1,'2026-06-18 13:57:49','2026-08-03 03:23:52','c59da43bc28fa0911726f77adc77184d3f2735ee2fe6bc2fbf01fbc695603ce3','2026-09-02 04:37:50','admin'),(13,'order_mgr','$2y$10$hAjmxYOkF7gGJ5W9HOWpxeYhbvOJH7fWlrOakzENbeInECceJDo7u','order@gundamstore.vn','Quản lý đơn hàng','0903333333','Hồ Chí Minh','employee',1,'2026-07-31 02:44:15','2026-08-03 03:23:52',NULL,NULL,'order_manager'),(14,'product_mgr','$2y$10$z5eY16ihkgN32YbmTHmfn.xfpZiW8a7zl/VM5/aVRGzw3bkrSTO9u','product@gundamstore.vn','Quản lý sản phẩm','0904444444','Cần Thơ','employee',1,'2026-07-31 02:44:15','2026-08-03 03:23:52',NULL,NULL,'product_manager'),(15,'customer1','$2y$10$FrZKeVDWlBsxsBd47lN.F.MhIbINwW/Xx9k1OfvDIicZiFwxl5cVK','customer1@gmail.com','Nguyễn Văn An','0905555555','Bắc Ninh','user',1,'2026-07-31 02:44:15','2026-08-03 03:23:52',NULL,NULL,NULL),(16,'customer2','$2y$10$COS0y4mt3PWIK2EcpOnkGepFZcKWg6xIfLP.akALNg/iFBjuMh.VS','customer2@gmail.com','Trần Thị Bình','0906666666','Hải Phòng','user',1,'2026-07-31 02:44:15','2026-08-03 03:23:52',NULL,NULL,NULL),(17,'khang123','$2y$10$PkEczwblLKW3yITYxNPPWOjk.2AHPqNgEmS7f7b4VUbFxH/kePlZ6','khangkutr634e2@gmail.com','Nguyễn Hữu Khang','0357765793','An mỹ mỹ đức hà nội','user',1,'2026-08-03 02:11:50','2026-08-03 02:49:01',NULL,NULL,NULL),(19,'staff1','$2y$10$NlNakowQ1.eWnxMBR0CHWeHpC83IW4y4eDryJMl9HgWqBvOvhfECi','staff1@gundamstore.vn','Nhân viên bán hàng','0902222222','Đà Nẵng','employee',1,'2026-08-03 03:19:29','2026-08-03 03:23:52',NULL,NULL,'staff');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'gundam_store'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-03 21:21:08
