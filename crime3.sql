/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.6.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: crime3
-- ------------------------------------------------------
-- Server version	11.6.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `crime_evidence_files`
--

DROP TABLE IF EXISTS `crime_evidence_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crime_evidence_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  CONSTRAINT `crime_evidence_files_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `crime_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crime_evidence_files`
--

LOCK TABLES `crime_evidence_files` WRITE;
/*!40000 ALTER TABLE `crime_evidence_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `crime_evidence_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crime_reports`
--

DROP TABLE IF EXISTS `crime_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crime_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `location` varchar(255) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time DEFAULT NULL,
  `description` text NOT NULL,
  `witnesses` text DEFAULT NULL,
  `evidence_description` text DEFAULT NULL,
  `evidence_file_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','investigating','resolved','closed') NOT NULL DEFAULT 'pending',
  `officer_assigned` int(11) DEFAULT NULL,
  `officer_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_crime_reports_user_id` (`user_id`),
  KEY `idx_crime_reports_status` (`status`),
  KEY `idx_crime_reports_date` (`incident_date`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crime_reports`
--

LOCK TABLES `crime_reports` WRITE;
/*!40000 ALTER TABLE `crime_reports` DISABLE KEYS */;
INSERT INTO `crime_reports` VALUES
(1,1,'Theft','123 Main Street, Cityville','2025-04-01','14:30:00','My bicycle was stolen from outside the grocery store. It was a blue mountain bike with the brand name \"SpeedCycle\".','A store employee may have witnessed the incident. Their name was John according to their nametag.','There might be security camera footage from the grocery store.',NULL,'pending',NULL,NULL,'2025-04-06 06:05:44','2025-04-06 06:05:44'),
(3,1,'Assault','Lingsat, SFC','2025-04-06',NULL,'We were wallking down the street of Lingsat near DPWH when a student of Maritime approach us then squatted us right away. ','mary mary mary mary mary ',NULL,NULL,'pending',NULL,NULL,'2025-04-06 06:37:22','2025-04-06 06:37:22'),
(8,2,'Theft','Tanqui Lipit, San Fernando City, La Union','2025-05-18',NULL,'Around 7 am today, my bike was stolen by a unknown person.','None','uploads/68293147429a0-Retail-Theft-Risk.jpg','uploads/68293147429a0-Retail-Theft-Risk.jpg','pending',NULL,NULL,'2025-05-18 01:00:55','2025-05-18 01:23:34'),
(10,2,'Other','Lingsat, SFC','2025-05-18',NULL,'Car Robbery !!!!!!! ','Bunch of students of Lorma Colleges','uploads/6829371c4867b-auto-theft-thief.jpg','uploads/6829371c4867b-auto-theft-thief.jpg','investigating',NULL,NULL,'2025-05-18 01:25:48','2025-05-18 01:25:57'),
(11,2,'Fraud','Balaoan, La Union','2025-05-15',NULL,'A Indian man accused of fraudery ','The victim, Samantha Lorente','uploads/682937f0b1d1e-fraud.jpg','uploads/682937f0b1d1e-fraud.jpg','resolved',NULL,NULL,'2025-05-18 01:29:20','2025-05-18 01:29:48'),
(12,2,'Assault','Carlatan, San Fernando City, La Union','2025-05-05',NULL,'Assault battery within a family','Princess, the wife','uploads/6829389026cd6-Threatening-to-Commit-a-Crime-in-Massachusetts-896x598.jpg','uploads/6829389026cd6-Threatening-to-Commit-a-Crime-in-Massachusetts-896x598.jpg','pending',NULL,NULL,'2025-05-18 01:32:00','2025-05-18 01:32:00');
/*!40000 ALTER TABLE `crime_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_comments`
--

DROP TABLE IF EXISTS `report_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `officer_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  CONSTRAINT `report_comments_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `crime_reports` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_comments`
--

LOCK TABLES `report_comments` WRITE;
/*!40000 ALTER TABLE `report_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*justin@example.com | m*********************1*/;
INSERT INTO `users` VALUES
(1,'','mary@example.com','$2y$10$0dnQBu.egPa0PZKhHX5er.UhdaZ7M0XxEs3APBKkT88vd6GIZ53rm',NULL,'2025-04-06 05:53:28'),
(2,'Justin Joseph E. Sanchez','justin@example.com','$2y$10$iWQvEWC5hdCmuG8UF9vRa.cY.9GDUDaD4nqb6aQ1oSkvR1XB1tX.m',NULL,'2025-05-12 15:52:13'),
(3,'','aaron@example.com','$2y$10$F9Z53bVIF.q131o94XUGGu4kukysqTFARYT5GRHx1Opxj1GdmVJ9S',NULL,'2025-05-12 16:53:51'),
(4,'Mary Ann Mzana','mzana@g.com','$2y$10$eygB71RfNjuo0v9zibIRuu.5N7xWnAJ7SZv/XqzbvUf5D.2kf/BEe',NULL,'2025-05-15 12:59:51');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-05-18  9:59:46
