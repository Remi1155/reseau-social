-- MySQL dump 10.13  Distrib 8.0.39, for Linux (x86_64)
--
-- Host: localhost    Database: reseaux_sociaux
-- ------------------------------------------------------
-- Server version	8.0.39-0ubuntu0.22.04.1

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
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id_comment` int NOT NULL AUTO_INCREMENT,
  `id_publication` int DEFAULT NULL,
  `id_compte` int DEFAULT NULL,
  `contenu` text NOT NULL,
  `date_heure` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_comment`),
  KEY `comments_ibfk_1` (`id_publication`),
  KEY `comments_ibfk_2` (`id_compte`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`id_publication`) REFERENCES `publication` (`id_publication`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`id_compte`) REFERENCES `compte` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (16,16,6,'Kaiza lesy','2024-09-27 04:08:34'),(17,16,6,'comms','2024-09-27 06:31:31'),(19,19,6,'Miandry any i Kiala Martine','2024-09-28 19:09:28'),(20,19,6,'Miandry anao','2024-09-28 19:37:47'),(21,19,5,'Saramiandry eny antaninarenina','2024-09-28 20:15:55'),(22,15,6,'Salama','2024-09-30 05:39:48'),(23,19,6,'DKSD','2024-10-02 01:44:52'),(24,19,6,'JJK','2024-10-02 01:44:59'),(25,19,6,'dfdf','2024-10-02 01:56:07'),(26,16,6,'hfd','2024-10-02 02:22:41'),(27,16,6,'dd','2024-10-02 04:38:59'),(28,16,6,'test comms','2024-10-02 06:56:06'),(29,20,6,'Hahaha','2024-10-02 09:39:28');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compte`
--

DROP TABLE IF EXISTS `compte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compte` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `mail` varchar(50) DEFAULT NULL,
  `mdp` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail` (`mail`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compte`
--

LOCK TABLES `compte` WRITE;
/*!40000 ALTER TABLE `compte` DISABLE KEYS */;
INSERT INTO `compte` VALUES (5,'Tanjonilaina','Xavi','xavi@gmail.com','tanjonilaina'),(6,'Remi','Xavier','remixavier@gmail.com','remi');
/*!40000 ALTER TABLE `compte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publication`
--

DROP TABLE IF EXISTS `publication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publication` (
  `id_publication` int NOT NULL AUTO_INCREMENT,
  `id_compte` int DEFAULT NULL,
  `contenu` text NOT NULL,
  `date_heure` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_publication`),
  KEY `id_compte` (`id_compte`),
  CONSTRAINT `publication_ibfk_1` FOREIGN KEY (`id_compte`) REFERENCES `compte` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publication`
--

LOCK TABLES `publication` WRITE;
/*!40000 ALTER TABLE `publication` DISABLE KEYS */;
INSERT INTO `publication` VALUES (15,5,'                        Salama daholo e','2024-09-25 05:31:03'),(16,5,'                        kaiza\r\n','2024-09-25 08:21:56'),(18,6,'                        De aona ny fandeany\r\n','2024-09-27 06:29:14'),(19,6,'                        Frère jacque;\r\nAiza ianao?','2024-09-28 19:07:18'),(20,5,'                        De aona oa\r\n','2024-09-28 20:18:13');
/*!40000 ALTER TABLE `publication` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reaction_comment`
--

DROP TABLE IF EXISTS `reaction_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reaction_comment` (
  `id_reaction` int NOT NULL AUTO_INCREMENT,
  `type` enum('jaime','jadore','haha','triste') NOT NULL,
  `id_comment` int NOT NULL,
  `id_compte` int NOT NULL,
  PRIMARY KEY (`id_reaction`),
  UNIQUE KEY `unique_reaction` (`id_comment`,`id_compte`),
  KEY `reaction_comment_ibfk_2` (`id_compte`),
  CONSTRAINT `reaction_comment_ibfk_1` FOREIGN KEY (`id_comment`) REFERENCES `comments` (`id_comment`) ON DELETE CASCADE,
  CONSTRAINT `reaction_comment_ibfk_2` FOREIGN KEY (`id_compte`) REFERENCES `compte` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reaction_comment`
--

LOCK TABLES `reaction_comment` WRITE;
/*!40000 ALTER TABLE `reaction_comment` DISABLE KEYS */;
INSERT INTO `reaction_comment` VALUES (1,'jaime',29,6),(2,'triste',21,6);
/*!40000 ALTER TABLE `reaction_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reaction_publication`
--

DROP TABLE IF EXISTS `reaction_publication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reaction_publication` (
  `id_reaction` int NOT NULL AUTO_INCREMENT,
  `type` enum('jaime','jadore','haha','triste') NOT NULL,
  `id_publication` int DEFAULT NULL,
  `id_compte` int DEFAULT NULL,
  PRIMARY KEY (`id_reaction`),
  KEY `reaction_publication_ibfk_1` (`id_publication`),
  KEY `reaction_publication_ibfk_2` (`id_compte`),
  CONSTRAINT `reaction_publication_ibfk_1` FOREIGN KEY (`id_publication`) REFERENCES `publication` (`id_publication`) ON DELETE CASCADE,
  CONSTRAINT `reaction_publication_ibfk_2` FOREIGN KEY (`id_compte`) REFERENCES `compte` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reaction_publication`
--

LOCK TABLES `reaction_publication` WRITE;
/*!40000 ALTER TABLE `reaction_publication` DISABLE KEYS */;
INSERT INTO `reaction_publication` VALUES (1,'haha',16,6),(2,'jadore',15,6),(3,'jaime',16,5),(4,'triste',15,5),(5,'jaime',18,5),(6,'haha',20,6),(7,'jadore',18,6);
/*!40000 ALTER TABLE `reaction_publication` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-02 14:50:47
