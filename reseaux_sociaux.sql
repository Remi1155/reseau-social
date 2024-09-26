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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,1,3,'commentaire de Test\r\n','2024-09-24 06:54:33'),(2,15,4,'Salama lesy','2024-09-25 07:32:50'),(3,15,4,'Salama lesy','2024-09-25 07:33:59'),(8,4,5,'ok','2024-09-25 08:00:00'),(10,16,4,'Dia ahoana','2024-09-25 08:53:07'),(11,1,4,'fdf','2024-09-26 06:31:23'),(12,16,4,'salama','2024-09-26 06:31:41'),(13,1,4,'haha','2024-09-26 18:26:34'),(14,4,4,'Hello hello','2024-09-26 18:37:08'),(15,16,4,'Gg','2024-09-26 18:38:28');
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
INSERT INTO `compte` VALUES (3,'test','test','test@gmail.com','test'),(4,'tanjona','tanjona','tanjona@gmail.com','tanjona'),(5,'Tanjonilaina','Xavi','xavi@gmail.com','tanjonilaina'),(6,'Remi','Xavier','remixavier@gmail.com','remi');
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publication`
--

LOCK TABLES `publication` WRITE;
/*!40000 ALTER TABLE `publication` DISABLE KEYS */;
INSERT INTO `publication` VALUES (1,3,'Voici une publication','2024-09-24 06:43:52'),(4,4,'Hello, Je suis Tanjona. Voici ma premiere publication\r\n','2024-09-24 07:35:45'),(15,5,'                        Salama daholo e','2024-09-25 05:31:03'),(16,5,'                        kaiza\r\n','2024-09-25 08:21:56');
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
  `type` enum('j''aime','j''adore','haha','triste') NOT NULL,
  `id_comment` int DEFAULT NULL,
  `id_compte` int DEFAULT NULL,
  PRIMARY KEY (`id_reaction`),
  UNIQUE KEY `unique_reaction` (`id_comment`,`id_compte`),
  KEY `reaction_comment_ibfk_2` (`id_compte`),
  CONSTRAINT `reaction_comment_ibfk_1` FOREIGN KEY (`id_comment`) REFERENCES `comments` (`id_comment`),
  CONSTRAINT `reaction_comment_ibfk_2` FOREIGN KEY (`id_compte`) REFERENCES `compte` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reaction_comment`
--

LOCK TABLES `reaction_comment` WRITE;
/*!40000 ALTER TABLE `reaction_comment` DISABLE KEYS */;
INSERT INTO `reaction_comment` VALUES (15,'j\'adore',8,4);
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
  `type` enum('j''aime','j''adore','haha','triste') NOT NULL,
  `id_publication` int DEFAULT NULL,
  `id_compte` int DEFAULT NULL,
  PRIMARY KEY (`id_reaction`),
  KEY `id_compte` (`id_compte`),
  KEY `reaction_publication_ibfk_1` (`id_publication`),
  CONSTRAINT `reaction_publication_ibfk_1` FOREIGN KEY (`id_publication`) REFERENCES `publication` (`id_publication`) ON DELETE CASCADE,
  CONSTRAINT `reaction_publication_ibfk_2` FOREIGN KEY (`id_compte`) REFERENCES `compte` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reaction_publication`
--

LOCK TABLES `reaction_publication` WRITE;
/*!40000 ALTER TABLE `reaction_publication` DISABLE KEYS */;
INSERT INTO `reaction_publication` VALUES (1,'j\'adore',1,3),(2,'haha',1,3),(5,'haha',15,4),(6,'j\'adore',4,4),(7,'haha',1,4),(8,'j\'adore',16,4);
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

-- Dump completed on 2024-09-26 23:55:09
