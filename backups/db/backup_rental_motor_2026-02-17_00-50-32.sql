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
INSERT INTO `admins` VALUES (1,'admin','$2y$10$a/B0w5JDbqMd.GNbwDPlsePkebJVm6fJd0eeGZo2gBkOr71ZiTZna','Super Admin','Main Administrator','alfidiassaputra@gmail.com','081455667788',NULL,NULL,'2026-02-14 07:57:53'),(2,'Alfye','$2y$10$aD1Vk5MQWYjtG5UiO/9YJ.Xqy0Z361KWkhEGvVBN/H8t5o8osMHzK','Admin','Alfye','alfidias1511@gmail.com','083801294607',NULL,NULL,'2026-02-14 10:33:01');
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
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (49,3,'kadal','083801294607','alfidiassaputra@gmail.com','1212121021212121','aaaaaaaaaaaaaaaaaa','prabowo','083801294601','2026-02-17','2026-02-19',2,'Antar ke Lokasi',240000.00,NULL,0.00,'Disewa','8ec89d2c-5200-4757-b596-5114268b4dba','ORDER-49-1771264073','','2026-02-16 17:47:53');
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
  `motor_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `motor_id` (`motor_id`),
  CONSTRAINT `motor_images_ibfk_1` FOREIGN KEY (`motor_id`) REFERENCES `motors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motor_images`
--

LOCK TABLES `motor_images` WRITE;
/*!40000 ALTER TABLE `motor_images` DISABLE KEYS */;
INSERT INTO `motor_images` VALUES (2,1,'1771059640_u0.png',0),(3,2,'1771072930_0.jpg',1),(4,3,'1771247366_PCX 160.png',0);
/*!40000 ALTER TABLE `motor_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motors`
--

DROP TABLE IF EXISTS `motors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) NOT NULL,
  `model` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `color` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `stok` int(11) DEFAULT 1,
  `status` enum('Tersedia','Disewa','Maintenance') DEFAULT 'Tersedia',
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number` (`plate_number`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motors`
--

LOCK TABLES `motors` WRITE;
/*!40000 ALTER TABLE `motors` DISABLE KEYS */;
INSERT INTO `motors` VALUES (1,'Honda','Vario 110','Matic',2026,'AB 5503 V0','Hitam','KENCENGGG BANGETTTTT',150000.00,20,'Tersedia',1,1,'2026-02-14 08:13:17'),(2,'Honda','Beat Street','Matic',2026,'AB 1234 CA','Merah','GASSS POOLLL!!!',60000.00,20,'Tersedia',1,1,'2026-02-14 12:42:10'),(3,'Honda','PCX 160','Matic',2026,'AB 5803 AS','Hitam','AMAN, NYAMAN, MANTAP',120000.00,9,'Tersedia',1,1,'2026-02-16 13:09:26');
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
INSERT INTO `settings` VALUES ('about_desc_1','Berawal dari semangat untuk mempermudah mobilitas para wisatawan dan mahasiswa di Yogyakarta, JS Rental hadir sebagai solusi transportasi yang praktis, aman, dan terjangkau. Sejak didirikan pada tahun 2018, kami telah melayani ribuan pelanggan dengan komitmen memberikan unit terbaik.'),('about_desc_2','Kami memahami bahwa kenyamanan adalah kunci. Setiap armada kami melalui proses perawatan rutin yang ketat dan verifikasi keamanan sebelum sampai ke tangan Anda. Tim profesional kami siap membantu 24/7 untuk memastikan perjalanan Anda di Jogja berkesan.'),('about_exp_label','Pengalaman Melayani'),('about_exp_years','5+ Tahun'),('about_image','about_1771256387.jpg'),('about_misi','Menyediakan armada terbaru, kemudahan transaksi online, serta menjamin keamanan dan kenyamanan di setiap perjalanan.'),('about_nilai','Integritas dalam pelayanan, Kepedulian terhadap pelanggan, dan Keandalan armada adalah fondasi utama kami.'),('about_tag','Tentang Kami'),('about_title','Membangun Kepercayaan Sejak Tahun 2018'),('about_visi','Menjadi penyedia layanan rental kendaraan roda dua nomor satu di Yogyakarta yang mengedepankan inovasi dan kepuasan.'),('address','Jl. Malioboro No. 123, Yogyakarta 55272'),('delivery_fee','0'),('email','motorjoaja@gmail.com'),('fac1_title','2 Helm SNI'),('fac2_title','Jas Hujan'),('fac3_title','Tips Wisata'),('fac4_title','Antar Jemput'),('facilities_desc','Kami melengkapi perjalanan Anda dengan fasilitas penunjang yang memadai untuk menjamin keamanan dan kenyamanan.'),('facilities_tag','Value Lebih'),('facilities_title','Lebih Dari Sekedar Sewa'),('footer_description','Penyedia layanan sewa motor terpercaya di Yogyakarta sejak 2018. Kami mengutamakan kualitas armada dan kepuasan pelanggan di setiap perjalanan.'),('google_maps_url','https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3952.98418214697!2d110.33243678486978!3d-7.791498872190057!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a596c80e44265%3A0xe993f8a4b7df4af0!2sRental%20Motor%20Jogja%20Gamping%20-%20JS%20Rent!5e0!3m2!1sid!2sid!4v1771083151141!5m2!1sid!2sid'),('hero_background','hero_bg_1771239867.png'),('hero_subtitle','Sewa motor premium dengan syarat mudah. Armada terbaru, layanan antar jemput, dan harga terbaik menanti Anda.'),('hero_tag','#1 Motor Rental in Jogja'),('hero_title','Jelajahi Jogja dengan Gaya & Kenyamanan'),('late_fee_per_day','10000'),('meta_description','Penyedia layanan sewa motor terbaik di Yogyakarta dengan harga terjangkau dan pelayanan terpercaya.'),('meta_keywords','sewa motor jogja, rental motor yogyakarta, sewa motor murah'),('operational_hours','08:00 - 21:00 WIB'),('site_logo','logo_1771069487.svg'),('site_name','JS RENT'),('social_facebook','https://facebook.com/'),('social_instagram','https://instagram.com/alfisptraaa_'),('social_tiktok','https://tiktok.com/'),('social_youtube','https://youtube.com/'),('step1_desc','Telusuri katalog kami dan pilih motor yang paling cocok dengan rute wisata Anda.'),('step1_title','Pilih Unit'),('step2_desc','Klik tombol pesan untuk terhubung dengan admin kami via WhatsApp dan atur jadwal.'),('step2_title','Konfirmasi Chat'),('step3_desc','Tim kami akan mengantar unit ke lokasi Anda. Berikan identitas, dan perjalanan dimulai!'),('step3_title','Unit Diantar'),('steps_tag','Cara Sewa Mudah'),('steps_title','Hanya 3 Langkah untuk Memulai'),('terms_card_1_content','• Menjaminkan 2 identitas asli\n• Wajib KTP/SIM/NPWP/BPJS\n• Kartu Mahasiswa/Karyawan'),('terms_card_1_title','Identitas'),('terms_card_2_content','• Hitungan per 24 Jam\n• Overtime IDR 10K/Jam\n• Pengembalian tepat waktu'),('terms_card_2_title','Waktu Sewa'),('terms_card_3_content','Penyewa bertanggung jawab penuh atas segala kerusakan, kehilangan kunci, atau kehilangan unit selama masa penyewaan.'),('terms_card_3_title','Tanggung Jawab'),('terms_desc','Kami menjaga transparansi demi kenyamanan bersama. Harap baca poin-poin penting berikut sebelum melakukan pemesanan.'),('terms_point_1','Proses verifikasi identitas cepat'),('terms_point_2','Tanpa jaminan uang tunai (deposit)'),('terms_tag','Ketentuan Kami'),('terms_title','Syarat & Kebijakan Penyewaan'),('whatsapp_number','6281214025894');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (2,'Fyeliaa',5,'Ramah dan Baik sekali','1771074367.jpg',1,'2026-02-14 12:59:58'),(3,'Aulia Hasna',4,'Nyaman dan ramah','1771247449.jpg',1,'2026-02-16 13:10:49'),(4,'Alfye',1,'Tidak ramah','1771260891.png',1,'2026-02-16 16:54:51'),(5,'Budi Santoso',5,'Gacor','1771260987.jpg',1,'2026-02-16 16:56:27');
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

-- Dump completed on 2026-02-17  0:50:32
