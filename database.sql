-- MySQL dump 10.13  Distrib 8.0.26, for Linux (x86_64)
--
-- Host: localhost    Database: lpev
-- ------------------------------------------------------
-- Server version	8.0.26-0ubuntu0.20.04.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `level`
--

DROP TABLE IF EXISTS `level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `level` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` text,
  `width` int NOT NULL,
  `height` int NOT NULL,
  `start_x` int NOT NULL,
  `start_y` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `level`
--

LOCK TABLES `level` WRITE;
/*!40000 ALTER TABLE `level` DISABLE KEYS */;
INSERT INTO `level` VALUES (1,'Premier niveau','',15,15,0,0);
/*!40000 ALTER TABLE `level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record`
--

DROP TABLE IF EXISTS `record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time` time NOT NULL,
  `name` varchar(30) NOT NULL,
  `level_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_record_level` (`level_id`),
  CONSTRAINT `fk_record_level` FOREIGN KEY (`level_id`) REFERENCES `level` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `tile`
--

DROP TABLE IF EXISTS `tile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tile` (
  `level_id` int NOT NULL,
  `x` int NOT NULL,
  `y` int NOT NULL,
  `type` text NOT NULL,
  PRIMARY KEY (`level_id`,`x`,`y`),
  KEY `fk_tile_level` (`level_id`),
  CONSTRAINT `fk_tile_level` FOREIGN KEY (`level_id`) REFERENCES `level` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tile`
--

LOCK TABLES `tile` WRITE;
/*!40000 ALTER TABLE `tile` DISABLE KEYS */;
INSERT INTO `tile` VALUES (1,0,0,'floor'),(1,0,1,'floor'),(1,0,2,'wall'),(1,0,3,'wall'),(1,0,4,'wall'),(1,0,5,'wall'),(1,0,6,'wall'),(1,0,7,'wall'),(1,0,8,'wall'),(1,0,9,'wall'),(1,0,10,'wall'),(1,0,11,'wall'),(1,0,12,'wall'),(1,0,13,'wall'),(1,0,14,'wall'),(1,1,0,'floor'),(1,1,1,'floor'),(1,1,2,'floor'),(1,1,3,'floor'),(1,1,4,'floor'),(1,1,5,'floor'),(1,1,6,'floor'),(1,1,7,'floor'),(1,1,8,'wall'),(1,1,9,'floor'),(1,1,10,'wall'),(1,1,11,'floor'),(1,1,12,'floor'),(1,1,13,'floor'),(1,1,14,'wall'),(1,2,0,'wall'),(1,2,1,'floor'),(1,2,2,'wall'),(1,2,3,'wall'),(1,2,4,'wall'),(1,2,5,'floor'),(1,2,6,'wall'),(1,2,7,'floor'),(1,2,8,'wall'),(1,2,9,'floor'),(1,2,10,'wall'),(1,2,11,'floor'),(1,2,12,'wall'),(1,2,13,'floor'),(1,2,14,'wall'),(1,3,0,'wall'),(1,3,1,'floor'),(1,3,2,'floor'),(1,3,3,'floor'),(1,3,4,'wall'),(1,3,5,'floor'),(1,3,6,'wall'),(1,3,7,'floor'),(1,3,8,'wall'),(1,3,9,'floor'),(1,3,10,'floor'),(1,3,11,'floor'),(1,3,12,'wall'),(1,3,13,'floor'),(1,3,14,'wall'),(1,4,0,'wall'),(1,4,1,'floor'),(1,4,2,'wall'),(1,4,3,'floor'),(1,4,4,'wall'),(1,4,5,'wall'),(1,4,6,'wall'),(1,4,7,'wall'),(1,4,8,'wall'),(1,4,9,'floor'),(1,4,10,'wall'),(1,4,11,'wall'),(1,4,12,'wall'),(1,4,13,'wall'),(1,4,14,'wall'),(1,5,0,'wall'),(1,5,1,'floor'),(1,5,2,'wall'),(1,5,3,'floor'),(1,5,4,'floor'),(1,5,5,'floor'),(1,5,6,'floor'),(1,5,7,'floor'),(1,5,8,'floor'),(1,5,9,'floor'),(1,5,10,'wall'),(1,5,11,'floor'),(1,5,12,'floor'),(1,5,13,'floor'),(1,5,14,'wall'),(1,6,0,'wall'),(1,6,1,'wall'),(1,6,2,'wall'),(1,6,3,'wall'),(1,6,4,'wall'),(1,6,5,'floor'),(1,6,6,'wall'),(1,6,7,'floor'),(1,6,8,'wall'),(1,6,9,'floor'),(1,6,10,'wall'),(1,6,11,'wall'),(1,6,12,'wall'),(1,6,13,'floor'),(1,6,14,'wall'),(1,7,0,'wall'),(1,7,1,'floor'),(1,7,2,'floor'),(1,7,3,'floor'),(1,7,4,'wall'),(1,7,5,'floor'),(1,7,6,'wall'),(1,7,7,'floor'),(1,7,8,'wall'),(1,7,9,'floor'),(1,7,10,'floor'),(1,7,11,'floor'),(1,7,12,'wall'),(1,7,13,'floor'),(1,7,14,'wall'),(1,8,0,'wall'),(1,8,1,'floor'),(1,8,2,'wall'),(1,8,3,'wall'),(1,8,4,'wall'),(1,8,5,'wall'),(1,8,6,'wall'),(1,8,7,'floor'),(1,8,8,'wall'),(1,8,9,'floor'),(1,8,10,'wall'),(1,8,11,'floor'),(1,8,12,'wall'),(1,8,13,'floor'),(1,8,14,'wall'),(1,9,0,'wall'),(1,9,1,'floor'),(1,9,2,'floor'),(1,9,3,'floor'),(1,9,4,'wall'),(1,9,5,'floor'),(1,9,6,'floor'),(1,9,7,'floor'),(1,9,8,'wall'),(1,9,9,'floor'),(1,9,10,'wall'),(1,9,11,'floor'),(1,9,12,'floor'),(1,9,13,'floor'),(1,9,14,'wall'),(1,10,0,'wall'),(1,10,1,'floor'),(1,10,2,'wall'),(1,10,3,'wall'),(1,10,4,'wall'),(1,10,5,'floor'),(1,10,6,'wall'),(1,10,7,'wall'),(1,10,8,'wall'),(1,10,9,'wall'),(1,10,10,'wall'),(1,10,11,'floor'),(1,10,12,'wall'),(1,10,13,'floor'),(1,10,14,'wall'),(1,11,0,'wall'),(1,11,1,'floor'),(1,11,2,'floor'),(1,11,3,'floor'),(1,11,4,'wall'),(1,11,5,'floor'),(1,11,6,'wall'),(1,11,7,'floor'),(1,11,8,'wall'),(1,11,9,'floor'),(1,11,10,'wall'),(1,11,11,'floor'),(1,11,12,'wall'),(1,11,13,'floor'),(1,11,14,'wall'),(1,12,0,'wall'),(1,12,1,'floor'),(1,12,2,'wall'),(1,12,3,'wall'),(1,12,4,'wall'),(1,12,5,'floor'),(1,12,6,'wall'),(1,12,7,'floor'),(1,12,8,'wall'),(1,12,9,'floor'),(1,12,10,'wall'),(1,12,11,'wall'),(1,12,12,'wall'),(1,12,13,'wall'),(1,12,14,'wall'),(1,13,0,'wall'),(1,13,1,'floor'),(1,13,2,'floor'),(1,13,3,'floor'),(1,13,4,'floor'),(1,13,5,'floor'),(1,13,6,'floor'),(1,13,7,'floor'),(1,13,8,'floor'),(1,13,9,'floor'),(1,13,10,'floor'),(1,13,11,'floor'),(1,13,12,'floor'),(1,13,13,'floor'),(1,13,14,'floor'),(1,14,0,'wall'),(1,14,1,'wall'),(1,14,2,'wall'),(1,14,3,'wall'),(1,14,4,'wall'),(1,14,5,'wall'),(1,14,6,'wall'),(1,14,7,'wall'),(1,14,8,'wall'),(1,14,9,'wall'),(1,14,10,'wall'),(1,14,11,'wall'),(1,14,12,'wall'),(1,14,13,'floor'),(1,14,14,'finish');
/*!40000 ALTER TABLE `tile` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-11-16 16:38:41
