-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: rental_motor
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_password_history`
--

DROP TABLE IF EXISTS `admin_password_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_password_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_password_history_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_password_history`
--

LOCK TABLES `admin_password_history` WRITE;
/*!40000 ALTER TABLE `admin_password_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_password_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Super Admin','Admin') DEFAULT 'Admin',
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$xPli3SXtUZjVz643JWe2EusffTiAESfHCRj6VKoyr7.ds1VUcZzJm','Super Admin','Main Administrator','alfidiassaputra@gmail.com','081455667788',NULL,NULL,'2026-02-14 07:57:53'),(2,'Alfye','$2y$10$aD1Vk5MQWYjtG5UiO/9YJ.Xqy0Z361KWkhEGvVBN/H8t5o8osMHzK','Admin','Alfye','alfidias1511@gmail.com','083801294607',NULL,NULL,'2026-02-14 10:33:01');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `motor_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `location` text DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `promo_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('Pending','Disewa','Selesai','Batal') DEFAULT 'Pending',
  `snap_token` varchar(255) DEFAULT NULL,
  `midtrans_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `motor_id` (`motor_id`),
  KEY `fk_booking_promo` (`promo_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`motor_id`) REFERENCES `motors` (`id`),
  CONSTRAINT `fk_booking_promo` FOREIGN KEY (`promo_id`) REFERENCES `promos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (49,3,'kadal','083801294607','alfidiassaputra@gmail.com','1212121021212121','aaaaaaaaaaaaaaaaaa','prabowo','083801294601','2026-02-17','2026-02-19',2,'Antar ke Lokasi',240000.00,NULL,0.00,'Selesai','8ec89d2c-5200-4757-b596-5114268b4dba','ORDER-49-1771264073','','2026-02-16 17:47:53'),(50,6,'Ular','083801294607','alfidiassaputra@gmail.com','1212121021212121','Luragung Ya guys','prabowo','083801294601','2026-02-17','2026-02-18',1,'Antar ke Lokasi',60000.00,NULL,0.00,'Selesai','7a205177-26e2-4a56-b1cc-395ac0e8e909','ORDER-50-1771271762','','2026-02-16 19:56:02'),(51,2,'Maung','083801294607','alfidiassaputra@gmail.com','1212121021212121','Luragung Kuningan','prabowo','083801294601','2026-02-17','2026-02-28',11,'Antar ke Lokasi',660000.00,NULL,0.00,'Disewa','341940e2-e3a5-4b8a-af6c-18c70229f6d8','ORDER-51-1771313945',NULL,'2026-02-17 07:39:05'),(52,26,'Gaajah','083801294607','alfidiassaputra@gmail.com','1212121021212121','Luragung Kuningan','prabowo','083801294601','2026-02-17','2026-02-22',5,'Antar ke Lokasi',300000.00,NULL,0.00,'Disewa','ca936ae0-75ea-4967-8e45-3f630229ba37','ORDER-52-1771314093',NULL,'2026-02-17 07:41:33'),(53,3,'Zigen','083801294607',NULL,NULL,NULL,NULL,NULL,'2026-02-17','2026-02-20',3,'Luragung Kuningan',360000.00,NULL,0.00,'Selesai',NULL,NULL,'Aman','2026-02-17 07:47:20');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motor_images`
--

DROP TABLE IF EXISTS `motor_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motor_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_model_images` (`model_id`),
  CONSTRAINT `fk_model_images` FOREIGN KEY (`model_id`) REFERENCES `motor_models` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motor_images`
--

LOCK TABLES `motor_images` WRITE;
/*!40000 ALTER TABLE `motor_images` DISABLE KEYS */;
INSERT INTO `motor_images` VALUES (2,4,'1771059640_u0.png',0),(3,1,'1771072930_0.jpg',1),(4,2,'1771247366_PCX 160.png',0),(6,3,'655ec3e4fbe54caa697a_1771264383.jpg',0),(7,5,'ee4e16811a55de4481a7_1771265012.jpg',0);
/*!40000 ALTER TABLE `motor_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motor_models`
--

DROP TABLE IF EXISTS `motor_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motor_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) NOT NULL,
  `model` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motor_models`
--

LOCK TABLES `motor_models` WRITE;
/*!40000 ALTER TABLE `motor_models` DISABLE KEYS */;
INSERT INTO `motor_models` VALUES (1,'Honda','Beat Street','Matic',2026,'GASSS POOLLL!!!',60000.00,1,1,'2026-02-17 07:11:30'),(2,'Honda','PCX 160','Matic',2026,'AMAN, NYAMAN, MANTAP',120000.00,1,1,'2026-02-17 07:11:30'),(3,'Honda','Scoopy','Matic',2026,'Ringan dan nyaman',75000.00,1,1,'2026-02-17 07:11:30'),(4,'Honda','Vario 110','Matic',2026,'KENCENGGG BANGETTTTT',150000.00,1,1,'2026-02-17 07:11:30'),(5,'Yamaha','Gear 125','Matic',2026,'Bersih',60000.00,1,1,'2026-02-17 07:11:30');
/*!40000 ALTER TABLE `motor_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motors`
--

DROP TABLE IF EXISTS `motors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) DEFAULT NULL,
  `plate_number` varchar(20) NOT NULL,
  `color` varchar(30) NOT NULL,
  `status` enum('Tersedia','Disewa','Maintenance') DEFAULT 'Tersedia',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `fk_motor_model` (`model_id`),
  CONSTRAINT `fk_motor_model` FOREIGN KEY (`model_id`) REFERENCES `motor_models` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motors`
--

LOCK TABLES `motors` WRITE;
/*!40000 ALTER TABLE `motors` DISABLE KEYS */;
INSERT INTO `motors` VALUES (1,4,'AB 5503 V0','Hitam','Tersedia',1,'2026-02-14 08:13:17'),(2,1,'AB 1234 CA','Merah','Disewa',1,'2026-02-14 12:42:10'),(3,2,'AB 5803 AS','Hitam','Tersedia',1,'2026-02-16 13:09:26'),(5,3,'AA 1231 BB','Putih','Tersedia',1,'2026-02-16 17:53:03'),(6,5,'AB 1292 V7','Hitam','Tersedia',1,'2026-02-16 18:03:32'),(7,4,'DUMMY-1-2','Hitam','Tersedia',1,'2026-02-17 07:11:30'),(8,4,'DUMMY-1-3','Hitam','Tersedia',1,'2026-02-17 07:11:30'),(9,4,'DUMMY-1-4','Hitam','Tersedia',1,'2026-02-17 07:11:30'),(10,4,'DUMMY-1-5','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(11,4,'DUMMY-1-6','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(12,4,'DUMMY-1-7','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(13,4,'DUMMY-1-8','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(14,4,'DUMMY-1-9','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(15,4,'DUMMY-1-10','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(16,4,'DUMMY-1-11','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(17,4,'DUMMY-1-12','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(18,4,'DUMMY-1-13','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(19,4,'DUMMY-1-14','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(20,4,'DUMMY-1-15','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(21,4,'DUMMY-1-16','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(22,4,'DUMMY-1-17','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(23,4,'DUMMY-1-18','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(24,4,'DUMMY-1-19','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(25,4,'DUMMY-1-20','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(26,1,'DUMMY-2-2','Merah','Disewa',1,'2026-02-17 07:11:31'),(27,1,'DUMMY-2-3','Merah','Tersedia',1,'2026-02-17 07:11:31'),(28,1,'DUMMY-2-4','Merah','Tersedia',1,'2026-02-17 07:11:31'),(29,1,'DUMMY-2-5','Merah','Tersedia',1,'2026-02-17 07:11:31'),(30,1,'DUMMY-2-6','Merah','Tersedia',1,'2026-02-17 07:11:31'),(31,1,'DUMMY-2-7','Merah','Tersedia',1,'2026-02-17 07:11:31'),(32,1,'DUMMY-2-8','Merah','Tersedia',1,'2026-02-17 07:11:31'),(33,1,'DUMMY-2-9','Merah','Tersedia',1,'2026-02-17 07:11:31'),(34,1,'E 1234 AB','Putih','Tersedia',1,'2026-02-17 07:11:31'),(35,1,'DUMMY-2-11','Merah','Tersedia',1,'2026-02-17 07:11:31'),(36,1,'DUMMY-2-12','Merah','Tersedia',1,'2026-02-17 07:11:31'),(37,1,'DUMMY-2-13','Merah','Tersedia',1,'2026-02-17 07:11:31'),(38,1,'DUMMY-2-14','Merah','Tersedia',1,'2026-02-17 07:11:31'),(39,1,'DUMMY-2-15','Merah','Tersedia',1,'2026-02-17 07:11:31'),(40,1,'DUMMY-2-16','Merah','Tersedia',1,'2026-02-17 07:11:31'),(41,1,'DUMMY-2-17','Merah','Tersedia',1,'2026-02-17 07:11:31'),(42,1,'DUMMY-2-18','Merah','Tersedia',1,'2026-02-17 07:11:31'),(43,1,'DUMMY-2-19','Merah','Tersedia',1,'2026-02-17 07:11:31'),(44,1,'DUMMY-2-20','Merah','Tersedia',1,'2026-02-17 07:11:31'),(45,2,'DUMMY-3-2','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(46,2,'DUMMY-3-3','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(47,2,'DUMMY-3-4','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(48,2,'DUMMY-3-5','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(49,2,'DUMMY-3-6','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(50,2,'DUMMY-3-7','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(51,2,'DUMMY-3-8','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(52,2,'DUMMY-3-9','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(53,2,'DUMMY-3-10','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(54,3,'DUMMY-5-2','Putih','Tersedia',1,'2026-02-17 07:11:31'),(55,3,'DUMMY-5-3','Putih','Tersedia',1,'2026-02-17 07:11:31'),(56,3,'DUMMY-5-4','Putih','Tersedia',1,'2026-02-17 07:11:31'),(57,3,'DUMMY-5-5','Putih','Tersedia',1,'2026-02-17 07:11:31'),(58,3,'DUMMY-5-6','Putih','Tersedia',1,'2026-02-17 07:11:31'),(59,3,'DUMMY-5-7','Putih','Tersedia',1,'2026-02-17 07:11:31'),(60,3,'DUMMY-5-8','Putih','Tersedia',1,'2026-02-17 07:11:31'),(61,3,'DUMMY-5-9','Putih','Tersedia',1,'2026-02-17 07:11:31'),(62,3,'DUMMY-5-10','Putih','Tersedia',1,'2026-02-17 07:11:31'),(63,3,'DUMMY-5-11','Putih','Tersedia',1,'2026-02-17 07:11:31'),(64,3,'DUMMY-5-12','Putih','Tersedia',1,'2026-02-17 07:11:31'),(65,3,'DUMMY-5-13','Putih','Tersedia',1,'2026-02-17 07:11:31'),(66,3,'DUMMY-5-14','Putih','Tersedia',1,'2026-02-17 07:11:31'),(67,3,'DUMMY-5-15','Putih','Tersedia',1,'2026-02-17 07:11:31'),(68,5,'DUMMY-6-2','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(69,5,'DUMMY-6-3','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(70,5,'DUMMY-6-4','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(71,5,'DUMMY-6-5','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(72,5,'DUMMY-6-6','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(73,5,'DUMMY-6-7','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(74,5,'DUMMY-6-8','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(75,5,'DUMMY-6-9','Hitam','Tersedia',1,'2026-02-17 07:11:31'),(76,5,'DUMMY-6-10','Hitam','Tersedia',1,'2026-02-17 07:11:31');
/*!40000 ALTER TABLE `motors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promos`
--

DROP TABLE IF EXISTS `promos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `discount_type` enum('Percent','Nominal') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promos`
--

LOCK TABLES `promos` WRITE;
/*!40000 ALTER TABLE `promos` DISABLE KEYS */;
/*!40000 ALTER TABLE `promos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('about_desc_1','Berawal dari semangat untuk mempermudah mobilitas para wisatawan dan mahasiswa di Yogyakarta, JS Rental hadir sebagai solusi transportasi yang praktis, aman, dan terjangkau. Sejak didirikan pada tahun 2018, kami telah melayani ribuan pelanggan dengan komitmen memberikan unit terbaik.'),('about_desc_2','Kami memahami bahwa kenyamanan adalah kunci. Setiap armada kami melalui proses perawatan rutin yang ketat dan verifikasi keamanan sebelum sampai ke tangan Anda. Tim profesional kami siap membantu 24/7 untuk memastikan perjalanan Anda di Jogja berkesan.'),('about_exp_label','Pengalaman Melayani'),('about_exp_years','5+ Tahun'),('about_image','about_1771298433.jpg'),('about_misi','Menyediakan armada terbaru, kemudahan transaksi online, serta menjamin keamanan dan kenyamanan di setiap perjalanan.'),('about_nilai','Integritas dalam pelayanan, Kepedulian terhadap pelanggan, dan Keandalan armada adalah fondasi utama kami.'),('about_tag','Tentang Kami'),('about_title','Membangun Kepercayaan Sejak Tahun 2020'),('about_visi','Menjadi penyedia layanan rental kendaraan roda dua nomor satu di Yogyakarta yang mengedepankan inovasi dan kepuasan.'),('address','Jl. Malioboro No. 123, Yogyakarta 55273'),('delivery_fee','0'),('email','motorjoaja@gmail.com'),('fac1_icon','helmet-safety'),('fac1_title','2 Helm SNI'),('fac2_icon','cloud-rain'),('fac2_title','Jas Hujan'),('fac3_icon','gas-pump'),('fac3_title','Tips Wisata'),('fac4_icon','clock'),('fac4_title','Antar Jemput'),('facilities_1_desc','Setiap penyewaan dilengkapi 2 helm standar SNI yang bersih dan layak pakai untuk keamanan berkendara Anda.'),('facilities_1_title','Helm Standar SNI'),('facilities_2_desc','Jas hujan gratis untuk mengantisipasi cuaca Jogja yang tidak menentu. Tetap nyaman meski hujan tiba-tiba.'),('facilities_2_title','Jas Hujan Gratis'),('facilities_3_desc','Motor diserahkan dalam kondisi tangki penuh. Hemat waktu Anda, langsung jalan tanpa perlu isi bensin dulu.'),('facilities_3_title','Bensin Penuh'),('facilities_4_desc','Layanan antar jemput gratis area Jogja untuk pemesanan minimal 3 hari. Pesan kapan saja, kami siap melayani.'),('facilities_4_title','Antar Jemput 24/7'),('facilities_desc','Kami melengkapi perjalanan Anda dengan fasilitas penunjang yang memadai untuk menjamin keamanan dan kenyamanan.'),('facilities_tag','Value Lebih'),('facilities_title','Lebih Dari Sekedar Sewa'),('footer_contact_title','Kontak Support'),('footer_copyright_loc','Yogyakarta'),('footer_description','Penyedia layanan sewa motor terpercaya di Yogyakarta sejak 2018. Kami mengutamakan kualitas armada dan kepuasan pelanggan di setiap perjalanan.'),('footer_designer_text','Designed with Special for You'),('footer_explore_title','Jelajahi'),('footer_services_title','Layanan'),('google_maps_url','https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3952.98418214697!2d110.33243678486978!3d-7.791498872190057!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a596c80e44265%3A0xe993f8a4b7df4af0!2sRental%20Motor%20Jogja%20Gamping%20-%20JS%20Rent!5e0!3m2!1sid!2sid!4v1771083151141!5m2!1sid!2sid'),('hero_background','hero_bg_1771298189.png'),('hero_subtitle','Sewa motor premium dengan syarat mudah. Armada terbaru, layanan antar jemput, dan harga terbaik menanti Anda.'),('hero_tag','#1 Motor Rental in Jogja'),('hero_title','Jelajahi Jogja dengan Gaya & Kenyamanan'),('late_fee_per_day','10000'),('meta_description','Penyedia layanan sewa motor terbaik di Yogyakarta dengan harga terjangkau dan pelayanan terpercaya.'),('meta_keywords','sewa motor jogja, rental motor yogyakarta, sewa motor murah'),('operational_hours','09:00 - 21:00 WIB'),('section_contact_desc','Jangan ragu untuk menghubungi kami jika ada pertanyaan seputar sewa motor atau rekomendasi wisata.'),('section_contact_tag','Hubungi Kami'),('section_contact_title','Siap Membantu Liburan Anda'),('section_motor_desc','Unit motor selalu dalam kondisi prima, servis rutin, dan surat-surat lengkap untuk kenyamanan berkendara Anda.'),('section_motor_tag','Armada Kami'),('section_motor_title','Pilihan Motor Terbaru'),('section_testi_tag','Testimoni'),('section_testi_title','Apa Kata Mereka?'),('site_logo','logo_1771297505.svg'),('site_name','JS RENT'),('social_facebook','https://facebook.com/'),('social_instagram','https://instagram.com/alfisptraaa_'),('social_tiktok','https://tiktok.com/'),('social_youtube','https://youtube.com/'),('step1_desc','Telusuri katalog kami dan pilih motor yang paling cocok dengan rute wisata Anda.'),('step1_icon','id-card'),('step1_title','Pilih Unit'),('step2_desc','Klik tombol pesan untuk terhubung dengan admin kami via WhatsApp dan atur jadwal.'),('step2_icon','motorcycle'),('step2_title','Konfirmasi Chat'),('step3_desc','Tim kami akan mengantar unit ke lokasi Anda. Berikan identitas, dan perjalanan dimulai!'),('step3_icon','hand-holding-usd'),('step3_title','Unit Diantar'),('steps_tag','Cara Sewa Mudah guys'),('steps_title','Hanya 3 Langkah untuk Memulai'),('terms_card_1_content','• Menjaminkan 2 identitas asli\r\n• Wajib KTP/SIM/NPWP/BPJS\r\n• Kartu Mahasiswa/Karyawan'),('terms_card_1_desc','Wajib menyerahkan KTP/SIM asli sebagai jaminan selama masa sewa. Dokumen akan dikembalikan saat pengembalian motor dalam kondisi baik.'),('terms_card_1_title','Identitas'),('terms_card_2_content','• Hitungan per 24 Jam\r\n• Overtime IDR 10K/Jam\r\n• Pengembalian tepat waktu'),('terms_card_2_desc','Perhitungan sewa dimulai dari jam pengambilan motor. Keterlambatan pengembalian dikenakan biaya tambahan Rp 20.000/jam atau sesuai kesepakatan.'),('terms_card_2_title','Waktu Sewa'),('terms_card_3_content','Penyewa bertanggung jawab penuh atas segala kerusakan, kehilangan kunci, atau kehilangan unit selama masa penyewaan.'),('terms_card_3_desc','Penyewa bertanggung jawab penuh atas kerusakan, kehilangan, atau tilang selama masa sewa. Wajib mengisi bahan bakar sebelum pengembalian.'),('terms_card_3_title','Tanggung Jawab'),('terms_desc','Kami menjaga transparansi demi kenyamanan bersama. Harap baca poin-poin penting berikut sebelum melakukan pemesanan.'),('terms_point_1','Proses verifikasi identitas cepat'),('terms_point_2','Tanpa jaminan uang tunai (deposit)'),('terms_tag','Ketentuan Kami'),('terms_title','Syarat & Kebijakan Penyewaan'),('whatsapp_number','081214025894');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `message` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `is_displayed` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (2,'Fyeliaa',5,'Ramah dan Baik sekali','1771074367.jpg',1,'2026-02-14 12:59:58'),(3,'Aulia Hasna',4,'Nyaman dan ramah','1771247449.jpg',1,'2026-02-16 13:10:49'),(4,'Alfye',1,'Tidak ramah','1771260891.png',1,'2026-02-16 16:54:51'),(5,'Budi Santoso',5,'Gacor','1771260987.jpg',1,'2026-02-16 16:56:27'),(6,'Zigen',5,'bagus\r\n','testi_1771264797.jpg',1,'2026-02-16 17:59:10'),(7,'Goku',5,'Mantap dah','testi_1771264879.png',1,'2026-02-16 18:01:19');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-17 14:47:45
