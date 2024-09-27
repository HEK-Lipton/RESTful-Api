-- MySQL dump 10.19  Distrib 10.3.39-MariaDB, for Linux (x86_64)
--
-- Host: studdb.csc.liv.ac.uk    Database: sghlipto
-- ------------------------------------------------------
-- Server version	10.5.22-MariaDB-log

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
-- Table structure for table `Teams`
--

DROP TABLE IF EXISTS `Teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Teams` (
  `teamId` int(5) NOT NULL,
  `teamName` varchar(30) DEFAULT NULL,
  `teamSports` varchar(30) DEFAULT NULL,
  `avgAge` int(2) DEFAULT NULL,
  PRIMARY KEY (`teamId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Teams`
--

LOCK TABLES `Teams` WRITE;
/*!40000 ALTER TABLE `Teams` DISABLE KEYS */;
INSERT INTO `Teams` VALUES (1,'Liverpool FC','FootBall',23),(2,'Giants','Rugby',26),(3,'Rovers','Hockey',23);
/*!40000 ALTER TABLE `Teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Players`
--

DROP TABLE IF EXISTS `Players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Players` (
  `playerId` int(5) NOT NULL,
  `playerSurname` varchar(30) DEFAULT NULL,
  `playerForname` varchar(30) DEFAULT NULL,
  `playerNation` varchar(30) DEFAULT NULL,
  `playerBirth` char(10) DEFAULT NULL,
  `playsFor` int(5) NOT NULL,
  PRIMARY KEY (`playerId`),
  KEY `playsFor` (`playsFor`),
  CONSTRAINT `Players_ibfk_1` FOREIGN KEY (`playsFor`) REFERENCES `Teams` (`teamId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Players`
--

LOCK TABLES `Players` WRITE;
/*!40000 ALTER TABLE `Players` DISABLE KEYS */;
INSERT INTO `Players` VALUES (1,'John','Smith','England','1998-04-01',1),(2,'Joel','Parr','France','2001-04-01',1),(3,'Tarron','Chloe','Brazil','2003-07-01',1),(4,'Brown','Sam','England','1995-01-08',2),(5,'Green','Harry','Germany','2003-02-08',2),(6,'Taylor','Jacob','Spain','1995-06-11',2),(7,'Walker','Adam','Wales','1998-02-21',3),(8,'Johnson','Hannah','Ireland','2001-07-09',3),(9,'Baker','Mark','Scotland','2002-02-15',3);
/*!40000 ALTER TABLE `Players` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-05-08  0:11:52
