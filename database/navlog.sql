-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: navlog_school
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

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
-- Table structure for table `aircraft`
--

DROP TABLE IF EXISTS `aircraft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aircraft` (
  `idAircraft` int(11) NOT NULL AUTO_INCREMENT,
  `tacho_beg` int(11) DEFAULT NULL,
  `tacho_end` int(11) DEFAULT NULL,
  `offblocks` time DEFAULT NULL,
  `engine_off` time DEFAULT NULL,
  `takeoff_time` time DEFAULT NULL,
  `landing_time` time DEFAULT NULL,
  PRIMARY KEY (`idAircraft`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aircraft`
--

LOCK TABLES `aircraft` WRITE;
/*!40000 ALTER TABLE `aircraft` DISABLE KEYS */;
INSERT INTO `aircraft` VALUES (1,678,689,'10:00:00','11:00:00','11:05:00','13:00:00');
/*!40000 ALTER TABLE `aircraft` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checkpoint`
--

DROP TABLE IF EXISTS `checkpoint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checkpoint` (
  `idCheckpoint` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(45) DEFAULT NULL,
  `radio_freq` int(11) DEFAULT NULL,
  PRIMARY KEY (`idCheckpoint`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checkpoint`
--

LOCK TABLES `checkpoint` WRITE;
/*!40000 ALTER TABLE `checkpoint` DISABLE KEYS */;
INSERT INTO `checkpoint` VALUES (3,'Mike EHRD',118205),(4,'PAM',117800),(5,'MIKE EHLE',135180),(6,'test point',123450),(7,'test eham',118105);
/*!40000 ALTER TABLE `checkpoint` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flight`
--

DROP TABLE IF EXISTS `flight`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flight` (
  `idFlight` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `departure` varchar(45) DEFAULT NULL,
  `destination` varchar(45) DEFAULT NULL,
  `departure_elevation` varchar(45) DEFAULT NULL,
  `destination_elevation` varchar(45) DEFAULT NULL,
  `departure_alt` int(11) DEFAULT NULL,
  `destination_alt` int(11) DEFAULT NULL,
  `TAS` int(11) DEFAULT NULL,
  PRIMARY KEY (`idFlight`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flight`
--

LOCK TABLES `flight` WRITE;
/*!40000 ALTER TABLE `flight` DISABLE KEYS */;
INSERT INTO `flight` VALUES (1,'2026-03-17','EHRD','EHLE','-14','70',1500,1500,105),(2,'2026-05-08','EHRD','EHAM','-14','-11',1500,1500,105);
/*!40000 ALTER TABLE `flight` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flight_has_aircraft`
--

DROP TABLE IF EXISTS `flight_has_aircraft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flight_has_aircraft` (
  `Flight_idFlight` int(11) NOT NULL,
  `Aircraft_idAircraft` int(11) NOT NULL,
  PRIMARY KEY (`Flight_idFlight`,`Aircraft_idAircraft`),
  KEY `fk_Flight_has_Aircraft_Aircraft1_idx` (`Aircraft_idAircraft`),
  KEY `fk_Flight_has_Aircraft_Flight1_idx` (`Flight_idFlight`),
  CONSTRAINT `fk_Flight_has_Aircraft_Aircraft1` FOREIGN KEY (`Aircraft_idAircraft`) REFERENCES `aircraft` (`idAircraft`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Flight_has_Aircraft_Flight1` FOREIGN KEY (`Flight_idFlight`) REFERENCES `flight` (`idFlight`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flight_has_aircraft`
--

LOCK TABLES `flight_has_aircraft` WRITE;
/*!40000 ALTER TABLE `flight_has_aircraft` DISABLE KEYS */;
INSERT INTO `flight_has_aircraft` VALUES (1,1);
/*!40000 ALTER TABLE `flight_has_aircraft` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leg`
--

DROP TABLE IF EXISTS `leg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leg` (
  `idLeg` int(11) NOT NULL AUTO_INCREMENT,
  `time_acc` int(11) DEFAULT NULL,
  `time_int` int(11) DEFAULT NULL,
  `ETO` time DEFAULT NULL,
  `RETO` time DEFAULT NULL,
  `ATO` time DEFAULT NULL,
  `MEF` int(11) DEFAULT NULL,
  `cruise` int(11) DEFAULT NULL,
  `MH` int(11) DEFAULT NULL,
  `var` int(11) DEFAULT NULL,
  `TH` int(11) DEFAULT NULL,
  `WCA` int(11) DEFAULT NULL,
  `wind_dir` int(11) DEFAULT NULL,
  `wind_v` int(11) DEFAULT NULL,
  `tt` int(11) DEFAULT NULL,
  `dist_int` int(11) DEFAULT NULL,
  `dist_acc` int(11) DEFAULT NULL,
  `gs` int(11) DEFAULT NULL,
  `Checkpoint_idCheckpoint` int(11) NOT NULL,
  `Flight_idFlight` int(11) NOT NULL,
  PRIMARY KEY (`idLeg`),
  KEY `fk_Leg_Checkpoint1_idx` (`Checkpoint_idCheckpoint`),
  KEY `fk_Leg_Flight1_idx` (`Flight_idFlight`),
  CONSTRAINT `fk_Leg_Checkpoint1` FOREIGN KEY (`Checkpoint_idCheckpoint`) REFERENCES `checkpoint` (`idCheckpoint`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Leg_Flight1` FOREIGN KEY (`Flight_idFlight`) REFERENCES `flight` (`idFlight`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leg`
--

LOCK TABLES `leg` WRITE;
/*!40000 ALTER TABLE `leg` DISABLE KEYS */;
INSERT INTO `leg` VALUES (1,0,4,NULL,NULL,NULL,800,1000,59,3,62,2,100,6,60,6,32,100,3,1),(2,19,15,NULL,NULL,NULL,500,1500,59,3,62,2,100,6,60,6,32,102,4,1),(3,34,15,NULL,NULL,NULL,500,1000,59,3,62,2,100,6,60,6,32,102,5,1),(4,50,10,NULL,NULL,NULL,600,1200,90,2,92,1,100,8,90,12,44,105,6,1),(5,0,12,NULL,NULL,NULL,700,1500,85,2,87,1,100,8,90,20,20,105,7,2);
/*!40000 ALTER TABLE `leg` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-08 16:28:58
