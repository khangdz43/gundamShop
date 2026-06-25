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
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
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
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Gundam Universal Century','gundam-uc','Các mô hình thuộc vũ trụ Universal Century','2026-06-18 13:57:49'),(2,'Gundam SEED','gundam-seed','Các mô hình thuộc vũ trụ Cosmic Era','2026-06-18 13:57:49'),(3,'Iron-Blooded Orphans','ibo','Các mô hình từ Mobile Suit Gundam: Iron-Blooded Orphans','2026-06-18 13:57:49'),(4,'Zaku & Zeon','zaku','Các mô hình phe Zeon','2026-06-18 13:57:49'),(5,'SD Gundam','sd','Dòng Super Deformed','2026-06-18 13:57:49');
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
  `role` enum('user','assistant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_chat_messages_session_created` (`session_id`,`created_at`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` VALUES (1,1,2,'user','Gợi ý mô hình cho người mới','2026-06-18 15:27:32'),(2,1,2,'assistant','Chào bạn, rất vui được hỗ trợ!\n\nVới người mới bắt đầu, mình gợi ý hai dòng mô hình sau nhé:\n\n1.  **HG (High Grade):** Đây là lựa chọn tuyệt vời nhất. Mô hình HG có độ chi tiết tốt, khớp cử động linh hoạt, và quan trọng nhất là rất dễ lắp ráp, không cần keo hay sơn. Bạn sẽ nhanh chóng làm quen với quá trình lắp ráp Gunpla.\n2.  **SD (Super Deformed):** Nếu bạn muốn một mô hình nhỏ gọn, dễ thương và cực kỳ nhanh để hoàn thành, SD là lựa chọn lý tưởng. Chúng có ít bộ phận hơn HG và cũng không cần keo/sơn.\n\nBạn có thể tham khảo các mẫu HG hoặc SD trên trang Sản phẩm của Gundam Store HUMG, hoặc liên hệ trực tiếp hotline 0969 946 335 để được tư vấn mẫu cụ thể hơn nhé!','2026-06-18 15:27:35'),(3,1,2,'user','Chính sách giao hàng và thanh toán','2026-06-18 15:28:25'),(4,1,2,'assistant','Chào bạn, đây là chính sách giao hàng và thanh toán của Gundam Store HUMG nhé:\n\n**Giao hàng:**\n*   **Miễn phí vận chuyển** cho các đơn hàng từ **2.000.000 VNĐ** trở lên.\n*   Với các đơn hàng dưới 2.000.000 VNĐ, phí ship cố định là **30.000 VNĐ**.\n\n**Thanh toán:**\nBạn có thể lựa chọn 1 trong 2 hình thức sau:\n*   **Thanh toán khi nhận hàng (COD)**\n*   **Chuyển khoản ngân hàng**\n\nNếu cần thêm thông tin, bạn cứ hỏi nhé!','2026-06-18 15:28:36'),(5,1,2,'user','Khác nhau HG RG MG PG là gì?','2026-06-18 15:57:40'),(6,1,2,'assistant','Chào bạn, đây là điểm khác biệt chính giữa các dòng mô hình HG, RG, MG và PG nhé:\n\n*   **HG (High Grade):**\n    *   **Tỉ lệ:** 1/144\n    *   **Độ phức tạp:** Dễ lắp ráp nhất, lý tưởng cho người mới bắt đầu.\n    *   **Chi tiết:** Tốt, khớp cử động linh','2026-06-18 15:57:43'),(7,2,1,'user','tôi cần chọn mô hình gundam','2026-06-19 04:40:57'),(8,2,1,'assistant','Chào bạn! Rất vui được hỗ trợ bạn chọn mô hình Gundam tại Gundam Store HUMG.\n\nĐể mình tư vấn tốt nhất, bạn cho mình biết một chút nhé:\n\n1.  **Bạn mới bắt đầu lắp hay đã có kinh nghiệm rồi ạ?**\n2.  **Bạn thích mô hình dễ lắp, nhiều chi tiết phức tạp, hay kích thước lớn để trưng bày?**\n3.  **Bạn có thích một mẫu Gundam cụ thể nào không (ví dụ: RX-78-2, Freedom, Unicorn...)?**\n\nDựa vào đó, mình sẽ gợi ý các dòng HG, RG, MG, PG, SD, MGEX phù hợp','2026-06-19 04:41:01'),(9,1,2,'user','Chính sách giao hàng và thanh toán','2026-06-22 14:36:15'),(10,1,2,'assistant','Shop ho tro giao hang toan quoc. Don tu 2.000.000d duoc mien phi ship, don thap hon phi ship 30.000d. Ban co the thanh toan COD hoac chuyen khoan.','2026-06-22 14:36:16'),(11,1,2,'user','helo','2026-06-22 14:36:19'),(12,1,2,'assistant','Minh la tro ly tu van cua Gundam Store HUMG. Ban co the hoi minh ve cach chon HG/RG/MG/PG, san pham cho nguoi moi, giao hang, thanh toan hoac cac mau dang sale.','2026-06-22 14:36:20'),(13,1,2,'user','chào bạn','2026-06-22 14:36:23'),(14,1,2,'assistant','Chào bạn, rất vui được trò chuyện! Bạn đang tìm kiếm thông tin gì về Gunpla hay cần mình tư vấn mẫu nào không? ?','2026-06-22 14:36:24'),(15,1,2,'user','bạn đang làm gì','2026-06-22 14:36:52'),(16,1,2,'assistant','Tôi là trợ lý AI của Gundam Store HUMG, chuyên tư vấn về các sản phẩm Gunpla chính hãng Bandai (HG, RG, MG, PG, SD, MGEX), hướng dẫn chọn mô hình phù hợp, và giải đáp các thắc mắc về chính sách giao hàng, thanh toán.\n\nBạn cần mình giúp gì không? ?','2026-06-22 14:36:54'),(17,1,2,'user','chào bạn nhaaa','2026-06-22 14:43:41'),(18,1,2,'assistant','Chào bạn! Rất vui được gặp lại. Bạn có câu hỏi gì về Gunpla hoặc cần mình tư vấn mẫu nào không? ?','2026-06-22 14:43:43'),(19,1,2,'user','Chính sách giao hàng và thanh toán','2026-06-24 15:45:18'),(20,1,2,'assistant','Shop ho tro giao hang toan quoc. Don tu 2.000.000d duoc mien phi ship, don thap hon phi ship 30.000d. Ban co the thanh toan COD hoac chuyen khoan.','2026-06-24 15:45:21'),(21,2,1,'user','sss','2026-06-25 03:26:57'),(22,2,1,'assistant','À, có vẻ tin nhắn vừa rồi bị lỗi bạn nhỉ.\n\nBạn cứ cho mình biết lại nhé:\n\n1.  **Bạn mới bắt đầu lắp hay đã có kinh nghiệm rồi ạ?**\n2.  **Bạn thích mô hình dễ lắp, nhiều chi tiết phức tạp, hay kích thước lớn để trưng bày?**\n3.  **Bạn có thích một mẫu Gundam cụ thể nào không (ví dụ: RX-78-2, Freedom, Unicorn...)?**\n\nMình sẵn sàng tư vấn ngay ạ! ?','2026-06-25 03:27:08'),(23,2,1,'user','Khác nhau HG RG MG PG là gì?','2026-06-25 04:04:43'),(24,2,1,'assistant','HG de lap va gia mem, hop nguoi moi. RG nho nhung chi tiet cao. MG ti le 1/100, dep de trung bay. PG lon nhat, nhieu chi tiet va hop nguoi da co kinh nghiem.','2026-06-25 04:04:45'),(25,2,1,'user','Khác nhau HG RG MG PG là gì?','2026-06-25 14:44:18'),(26,2,1,'assistant','Chào bạn, mình xin giải thích chi tiết về các dòng mô hình HG, RG, MG, PG','2026-06-25 14:44:22');
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
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_chat_sessions_user_updated` (`user_id`,`updated_at`),
  CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_sessions`
--

LOCK TABLES `chat_sessions` WRITE;
/*!40000 ALTER TABLE `chat_sessions` DISABLE KEYS */;
INSERT INTO `chat_sessions` VALUES (1,2,'Tu van Gundam','2026-06-18 15:27:29','2026-06-24 15:45:22'),(2,1,'Tu van Gundam','2026-06-19 04:40:45','2026-06-25 14:44:22'),(3,3,'Tu van Gundam','2026-06-25 03:47:15','2026-06-25 03:47:15');
/*!40000 ALTER TABLE `chat_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_reads`
--

DROP TABLE IF EXISTS `notification_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_reads` (
  `user_id` int NOT NULL,
  `notification_id` int NOT NULL,
  `read_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`notification_id`),
  KEY `notification_id` (`notification_id`),
  CONSTRAINT `notification_reads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notification_reads_ibfk_2` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_reads`
--

LOCK TABLES `notification_reads` WRITE;
/*!40000 ALTER TABLE `notification_reads` DISABLE KEYS */;
INSERT INTO `notification_reads` VALUES (2,1,'2026-06-25 03:58:56'),(2,8,'2026-06-25 03:58:53'),(2,9,'2026-06-25 03:58:52'),(2,10,'2026-06-25 03:58:51');
/*!40000 ALTER TABLE `notification_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,NULL,'heheheh','adasdsada',0,'2026-06-24 15:30:36'),(2,NULL,'heheheh','adasdsada',0,'2026-06-24 15:38:00'),(3,NULL,'heheheh','adasdsada',0,'2026-06-24 15:38:08'),(4,NULL,'sdqweq','wwwwwwwwwwwwwww',0,'2026-06-24 15:38:13'),(5,NULL,'aaaaaaaaaaaaaaaaaaaaaaaa','aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',0,'2026-06-24 15:39:07'),(6,NULL,'Yêu cầu đổi trả mới','Người dùng @khang123 yêu cầu đổi trả đơn hàng #GD260624E250C3.',0,'2026-06-24 15:46:38'),(7,2,'Cập nhật yêu cầu đổi trả','Yêu cầu đổi trả cho đơn hàng #1 đã được từ chối. Phản hồi: êwrwerw',0,'2026-06-24 15:46:47'),(8,NULL,'sadadasd','assssssssssssss',0,'2026-06-25 03:26:05'),(9,NULL,'sdfsdfsdf','fffffffffffffffffff',0,'2026-06-25 03:55:04'),(10,NULL,'sdfsdfsdf','fffffffffffffffffff',0,'2026-06-25 03:58:43');
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
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,9,'SD Gundam Unicorn','SD Unicorn.jpg',250000.00,1,250000.00),(2,2,5,'Wing Gundam Zero EW (MG)','Wing Gundam Zero EW.jpg',1100000.00,3,3300000.00),(3,3,25,'RX-78-2 Gundam (PG)','models_default_img.jpeg',3200000.00,1,3200000.00),(4,4,3,'Gundam Barbatos Lupus (HG)','Gundam Barbatos Lupus.webp',480000.00,4,1920000.00),(5,5,33,'Graze Standard (HG)','models_default_img.jpeg',400000.00,1,400000.00),(6,6,4,'Unicorn Gundam (RG)','Unicorn Gundam.jpg',1050000.00,8,8400000.00),(7,7,34,'Vidar (HG)','models_default_img.jpeg',550000.00,3,1650000.00);
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
  `user_id` int NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `order_returns_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_returns_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_returns`
--

LOCK TABLES `order_returns` WRITE;
/*!40000 ALTER TABLE `order_returns` DISABLE KEYS */;
INSERT INTO `order_returns` VALUES (1,7,2,'nhu cc','rejected','êwrwerw','2026-06-24 15:46:38','2026-06-24 15:46:47');
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
  `order_code` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `note` text,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_method` enum('cod','bank_transfer') DEFAULT 'cod',
  `status` enum('pending','confirmed','shipping','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'GD260618E212E5',2,'khang123','0357765793','khangkutr6342@gmail.com','123123123','232',250000.00,30000.00,280000.00,'cod','pending','2026-06-18 14:01:50','2026-06-18 14:01:50'),(2,'GD260618DC950D',2,'khang123','0357765793','khangkutr6342@gmail.com','2222','ádasdasda',3300000.00,0.00,3300000.00,'bank_transfer','pending','2026-06-18 14:52:29','2026-06-18 14:52:29'),(3,'GD2606187BBEFD',2,'khang123','0357765793','khangkutr6342@gmail.com','2222','',3200000.00,0.00,3200000.00,'cod','pending','2026-06-18 14:58:15','2026-06-18 14:58:15'),(4,'GD260618127E99',2,'khang123','0357765793','khangkutr6342@gmail.com','2222','',1920000.00,30000.00,1950000.00,'bank_transfer','pending','2026-06-18 15:13:05','2026-06-18 15:13:05'),(5,'GD260618CC6A72',2,'khang123','0357765793','khangkutr6342@gmail.com','2222','',400000.00,30000.00,430000.00,'cod','cancelled','2026-06-18 15:13:16','2026-06-18 15:14:34'),(6,'GD2606249120EB',2,'khang123','0357765793','khangkutr6342@gmail.com','2222','',8400000.00,0.00,8400000.00,'bank_transfer','pending','2026-06-24 15:29:29','2026-06-24 15:29:29'),(7,'GD260624E250C3',2,'khang123','0357765793','khangkutr6342@gmail.com','2222','',1650000.00,30000.00,1680000.00,'bank_transfer','delivered','2026-06-24 15:43:26','2026-06-24 15:46:17');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `idx_password_resets_user` (`user_id`),
  KEY `idx_password_resets_expires` (`expires_at`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,2,'d64fe0bae7c7a79ade001f3d408cedd724d11ca54643f182602033b235feaba6','2026-06-18 18:45:45','2026-06-18 22:46:00','2026-06-18 15:45:45'),(2,2,'3a296bddec1c4721a1c33d4af0d1a97bef426a22040c9f4461a0bf2ccf6b13d6','2026-06-18 18:46:00','2026-06-18 22:51:12','2026-06-18 15:46:00'),(3,2,'02af1640b39b0b20979d41bb876921a77c62f8ead471974f2a01b4ef2b53a33d','2026-06-18 18:51:12','2026-06-18 22:53:36','2026-06-18 15:51:12'),(4,2,'58a34ae2ad47805a6a2899514399bde60ab46286b5d548bd05a968d43283c525','2026-06-18 18:53:36','2026-06-18 22:53:42','2026-06-18 15:53:36'),(5,2,'3884ca238b6bffd3320a2fcace60dfeaab4a35adb3dd71511c2614f888d5b299','2026-06-18 18:53:42','2026-06-18 22:56:56','2026-06-18 15:53:42'),(6,2,'0f74f4551f5e41dcb8a1df2ab2f76f4ab5333b9fe4df117b4b62ffcd8368b541','2026-06-18 18:56:56','2026-06-19 11:34:40','2026-06-18 15:56:56'),(7,2,'ca6a6ecc105674e2a924aaf5ca17782bbb3b1426db5ac4ed2d4913c28bdc0978','2026-06-19 07:34:40','2026-06-19 11:35:08','2026-06-19 04:34:40'),(8,2,'402e50e7968f36051eee11c8f89d00212538e2cf52a9e858f42d3ecc3709102e','2026-06-19 07:35:08','2026-06-19 11:36:44','2026-06-19 04:35:08'),(9,2,'3f8878524ed6b9d8226686a28a8d9ebc469a653b04c8e166aa269aa0514f46a3','2026-06-19 07:36:44',NULL,'2026-06-19 04:36:44');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `old_price` decimal(12,2) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `category` varchar(50) DEFAULT 'Gundam',
  `image` varchar(255) DEFAULT 'models_default_img.jpeg',
  `description` text,
  `type` enum('HG','RG','MG','PG','SD','MGEX','Other') DEFAULT 'HG',
  `series` varchar(100) DEFAULT NULL,
  `scale` varchar(20) DEFAULT NULL,
  `stock` int DEFAULT '50',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_sale` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'RX-78-2 Gundam (HG 1/144)',NULL,350000.00,NULL,NULL,'Gundam','RX-78-2 Gundam.avif','Mô hình Gundam cổ điển - kit HG phù hợp người mới.','HG','Mobile Suit Gundam','1/144',100,1,0,'active','2026-06-18 13:57:49','2026-06-18 13:57:49'),(2,'Strike Freedom Gundam (MGEX)',NULL,1950000.00,NULL,NULL,'Gundam','Strike Freedom Gundam.png','Phiên bản MGEX cao cấp với khung xương chi tiết.','MGEX','Gundam SEED Destiny','1/100',25,1,0,'active','2026-06-18 13:57:49','2026-06-18 13:57:49'),(3,'Gundam Barbatos Lupus (HG)',NULL,480000.00,520000.00,NULL,'Gundam','Gundam Barbatos Lupus.webp','Từ Iron-Blooded Orphans - thiết kế hung hãn.','HG','Iron-Blooded Orphans','1/144',76,1,1,'active','2026-06-18 13:57:49','2026-06-18 15:13:05'),(4,'Unicorn Gundam (RG)',NULL,1050000.00,1200000.00,NULL,'Gundam','Unicorn Gundam.jpg','Chuyển đổi Psycho-Frame với chi tiết siêu cao.','RG','Gundam Unicorn','1/144',32,1,1,'active','2026-06-18 13:57:49','2026-06-24 15:29:29'),(5,'Wing Gundam Zero EW (MG)',NULL,1100000.00,1300000.00,NULL,'Gundam','Wing Gundam Zero EW.jpg','Endless Waltz - cánh angel wings biểu tượng.','MG','Gundam Wing EW','1/100',32,1,1,'active','2026-06-18 13:57:49','2026-06-18 14:52:29'),(6,'Zaku II Green (HG)',NULL,320000.00,NULL,NULL,'Zaku','Zaku II Green.jpg','Mô hình Zaku cổ điển phe Zeon.','HG','Mobile Suit Gundam','1/144',120,1,0,'active','2026-06-18 13:57:49','2026-06-18 13:57:49'),(7,'Gundam Exia (MG)',NULL,850000.00,NULL,NULL,'Gundam','Gundam Exia.jpg','Mô hình MG chi tiết cao từ Gundam 00.','MG','Gundam 00','1/100',45,0,0,'active','2026-06-18 13:57:49','2026-06-18 13:57:49'),(8,'Sazabi Ver.Ka (MG)',NULL,2200000.00,NULL,NULL,'Gundam','Sazabi Ver.Ka.jpg','Phiên bản Ver.Ka cao cấp của Char Aznable.','MG','Char\'s Counterattack','1/100',15,0,0,'active','2026-06-18 13:57:49','2026-06-18 13:57:49'),(9,'SD Gundam Unicorn',NULL,250000.00,NULL,NULL,'Gundam','SD Unicorn.jpg','Mô hình SD dễ thương, lắp ráp nhanh.','SD','Gundam Unicorn','SD',199,0,0,'active','2026-06-18 13:57:49','2026-06-18 14:01:50'),(10,'PG Unleashed RX-78-2',NULL,6500000.00,NULL,NULL,'Gundam','PG Unleashed.jpg','Phiên bản Perfect Grade cao cấp nhất.','PG','Mobile Suit Gundam','1/60',8,0,0,'active','2026-06-18 13:57:49','2026-06-18 13:57:49'),(11,'Gouf (HG 1/144)',NULL,420000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam - Zeon MS','HG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(12,'GM (HG 1/144)',NULL,380000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam - Federation MS','HG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(13,'Zaku II (HGUC)',NULL,450000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam - Zeon MS','HG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(14,'Geara Zulu (HG)',NULL,520000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam UC','HG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(15,'Sinanju (HG)',NULL,650000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam UC','HG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(16,'Banshee Norn (RG)',NULL,880000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam UC','RG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(17,'Nu Gundam (RG)',NULL,950000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam: Char\'s Counterattack','RG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(18,'Sazabi (RG)',NULL,1000000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam: Char\'s Counterattack','RG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(19,'Hi-Nu Gundam (MG)',NULL,1350000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam: Char\'s Counterattack','MG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(20,'Crossbone Gundam (MG)',NULL,1450000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam F91','MG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(21,'God Gundam (MG)',NULL,1300000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam Wing','MG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(22,'Wing Gundam (MG)',NULL,1200000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam Wing','MG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(23,'Tallgeese (MG)',NULL,1250000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam Wing','MG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(24,'RX-0 Unicorn Gundam (PG)',NULL,3500000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam UC - Perfect Grade','PG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(25,'RX-78-2 Gundam (PG)',NULL,3200000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam - Perfect Grade','PG',NULL,NULL,49,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:58:15'),(26,'Qubeley (MG)',NULL,1500000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam: Char\'s Counterattack','MG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(27,'Exia (MG)',NULL,1100000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam 00','MG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(28,'Dynames (MG)',NULL,1150000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam 00','MG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(29,'Kyrios (HG)',NULL,480000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam 00','HG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(30,'Virtue (HG)',NULL,520000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam 00','HG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(31,'00 Raiser (HG)',NULL,580000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam 00','HG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(32,'Barbatos (HG)',NULL,450000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam Iron-Blooded Orphans','HG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36'),(33,'Graze Standard (HG)',NULL,400000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam Iron-Blooded Orphans','HG',NULL,NULL,49,0,0,'active','2026-06-18 14:28:36','2026-06-18 15:13:16'),(34,'Vidar (HG)',NULL,550000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam Iron-Blooded Orphans','HG',NULL,NULL,47,0,0,'active','2026-06-18 14:28:36','2026-06-24 15:43:26'),(35,'Gundam Astray Red Frame (MG)',NULL,1050000.00,NULL,NULL,'Gundam','models_default_img.jpeg','Mobile Suit Gundam SEED Astray','MG',NULL,NULL,50,0,0,'active','2026-06-18 14:28:36','2026-06-18 14:28:36');
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
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_chk_1` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
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
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `role` enum('user','employee','admin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `remember_token` varchar(64) DEFAULT NULL,
  `remember_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$Ou2Mle54EQzBm2TQQ/Jaoe9a5jAaXvqYOdpye8VmKmdubGVhDmdlW','admin@gundamstore.vn','Quản trị viên',NULL,NULL,'admin',1,'2026-06-18 13:57:49','2026-06-25 14:56:10',NULL,NULL),(2,'khang123','$2y$10$R8PIlF2AkXTHDgHhWCHooO9RQbBOPMYRlSQXlnrmqj3r2FZhXYGOC','khangkutr6342@gmail.com','khang123','0357765793','2222','user',1,'2026-06-18 14:00:47','2026-06-25 03:53:09',NULL,NULL),(3,'user12','$2y$10$GxxXaaFwuAUbbHzo02eetOKdsQWqsihfpP4xXFV1AE65FLObD0yb2','phuog2220147@gmail.com','nguyen van a','035776533793',NULL,'employee',1,'2026-06-25 03:37:44','2026-06-25 03:47:20',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-25 22:52:40
