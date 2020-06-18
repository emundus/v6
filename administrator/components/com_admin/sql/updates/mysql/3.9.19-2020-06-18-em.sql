-- MySQL dump 10.13  Distrib 5.7.19, for macos10.12 (x86_64)
--
-- Host: 127.0.0.1    Database: emundus_hesam
-- ------------------------------------------------------
-- Server version	5.7.26

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `jos_emundus_chatroom`
--

DROP TABLE IF EXISTS `jos_emundus_chatroom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jos_emundus_chatroom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fnum` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jos_chatroom_id_uindex` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_emundus_chatroom`
--

LOCK TABLES `jos_emundus_chatroom` WRITE;
/*!40000 ALTER TABLE `jos_emundus_chatroom` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_emundus_chatroom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_emundus_chatroom_users`
--

DROP TABLE IF EXISTS `jos_emundus_chatroom_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jos_emundus_chatroom_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chatroom_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jos_emundus_chatroom_users_chatroom_id_user_id_uindex` (`chatroom_id`,`user_id`),
  UNIQUE KEY `jos_emundus_chatroom_users_id_uindex` (`id`),
  KEY `jos_emundus_chatroom_users_jos_emundus_users_user_id_fk` (`user_id`),
  CONSTRAINT `jos_emundus_chatroom_users_jos_emundus_chatroom_id_fk` FOREIGN KEY (`chatroom_id`) REFERENCES `jos_emundus_chatroom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jos_emundus_chatroom_users_jos_emundus_users_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `jos_emundus_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_emundus_chatroom_users`
--

LOCK TABLES `jos_emundus_chatroom_users` WRITE;
/*!40000 ALTER TABLE `jos_emundus_chatroom_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_emundus_chatroom_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-06-18 12:25:49
