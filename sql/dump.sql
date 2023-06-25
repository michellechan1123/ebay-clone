-- MySQL dump 10.13  Distrib 8.0.19, for osx10.14 (x86_64)
--
-- Host: 127.0.0.1    Database: auction_site
-- ------------------------------------------------------
-- Server version	5.7.34-log

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
-- Table structure for table `bids`
--

DROP TABLE IF EXISTS `bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bids` (
  `item_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `bid_price` decimal(13,2) NOT NULL,
  `bid_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `outbid_notified` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`,`bid_price`),
  KEY `buyer_id` (`buyer_id`,`item_id`,`bid_price`),
  CONSTRAINT `fk_bid_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bid_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bids`
--

LOCK TABLES `bids` WRITE;
/*!40000 ALTER TABLE `bids` DISABLE KEYS */;
INSERT INTO `bids` VALUES (1,2,43200.00,'2021-02-04 12:26:05',0),(2,4,2050.00,'2021-02-07 12:26:38',0),(3,4,99999.00,'2021-02-06 12:26:30',1),(3,6,999999.00,'2021-03-09 12:26:54',0),(4,6,5600.00,'2021-02-05 12:26:15',0),(5,1,230.00,'2021-02-03 12:25:54',1),(5,2,330.00,'2021-02-19 12:26:45',0),(8,4,10.00,'2021-12-05 17:56:34',0);
/*!40000 ALTER TABLE `bids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Clothing'),(2,'Shoes & Accessories'),(3,'Food'),(4,'Books'),(5,'Home'),(6,'Equipment');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_recommendations`
--

DROP TABLE IF EXISTS `item_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_recommendations` (
  `buyer_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_score` int(11) NOT NULL,
  PRIMARY KEY (`buyer_id`,`item_id`),
  KEY `fk_recommendation_item` (`item_id`),
  CONSTRAINT `fk_recommendation_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_recommendation_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_recommendations`
--

LOCK TABLES `item_recommendations` WRITE;
/*!40000 ALTER TABLE `item_recommendations` DISABLE KEYS */;
INSERT INTO `item_recommendations` VALUES (6,8,1);
/*!40000 ALTER TABLE `item_recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_watches`
--

DROP TABLE IF EXISTS `item_watches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_watches` (
  `buyer_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  PRIMARY KEY (`buyer_id`,`item_id`),
  KEY `fk_watch_item` (`item_id`),
  CONSTRAINT `fk_watch_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_watch_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_watches`
--

LOCK TABLES `item_watches` WRITE;
/*!40000 ALTER TABLE `item_watches` DISABLE KEYS */;
INSERT INTO `item_watches` VALUES (2,1),(4,1),(1,2),(1,3),(4,3),(6,3),(1,4),(2,4),(4,4),(6,5),(4,6);
/*!40000 ALTER TABLE `item_watches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `minimum_bid` decimal(13,2) NOT NULL DEFAULT '1.00',
  `starting_price` decimal(13,2) NOT NULL DEFAULT '0.00',
  `reserve_price` decimal(13,2) NOT NULL DEFAULT '0.00',
  `auction_start_datetime` datetime NOT NULL,
  `auction_end_datetime` datetime NOT NULL,
  `auction_end_notified` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`),
  KEY `fk_item_seller` (`seller_id`),
  KEY `fk_item_category` (`category_id`),
  KEY `auction_start_datetime` (`auction_start_datetime`,`auction_end_datetime`),
  CONSTRAINT `fk_item_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_item_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,'boots','used, black',2,3,10.00,100.00,250.00,'2021-11-05 12:19:41','2021-01-12 12:20:21',1),(2,'wine','new, Masseto',3,5,0.00,50.00,150.00,'2021-03-08 12:19:44','2021-04-01 12:20:28',1),(3,'coat','new, neon pink',1,3,0.00,20.00,40.00,'2021-05-05 12:19:52','2021-05-10 12:20:37',0),(4,'iPhone13 Pro','new, baby blue',6,3,5.00,700.00,900.00,'2021-11-01 12:19:56','2021-11-06 12:20:43',0),(5,'Harry Potter 2','used',4,5,100.00,100000.00,200000.00,'2021-08-09 12:20:03','2021-08-25 12:20:49',0),(6,'Water bottle','brand new',3,7,1.00,10.00,20.00,'2021-11-26 17:32:04','2021-12-26 17:32:14',0),(7,'Telescope','650mm focal length, rarely used',6,7,50.00,1000.00,1200.00,'2021-12-04 11:49:21','2021-12-31 10:05:00',0),(8,'Moka pot','3-cup, silver, used',6,7,2.00,5.00,20.00,'2021-12-04 11:51:19','2021-12-18 10:51:00',0),(9,'Airpods Pro','Brand new',6,7,10.00,100.00,200.00,'2021-12-04 11:52:58','2022-01-05 10:52:00',0),(10,'Starry Night Reproduction','Hand-painted',5,7,200.00,2000.00,10000.00,'2021-12-04 11:54:12','2022-01-10 10:54:00',0),(11,'Impression Sunrise Reproduction','Oil painting',5,7,200.00,1000.00,5000.00,'2021-12-04 11:55:01','2022-01-20 10:54:00',0);
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `fk_notification_user` (`user_id`),
  CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (9,2,'Congratulations! You have won the auction for boots!',0,'2021-12-01 16:02:05'),(10,3,'You have sold boots to Michelle for £43200.00!',0,'2021-12-01 16:02:05'),(11,4,'Congratulations! You have won the auction for wine!',0,'2021-12-01 16:02:05'),(12,5,'You have sold wine to Yunsy for £2050.00!',0,'2021-12-01 16:02:05');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'buyer'),(2,'seller');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `registration_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_address` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email_address` (`email_address`),
  KEY `fk_users_role_id` (`role_id`),
  CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Dennis','Yung','2021-11-05 12:17:56','djung@gmail.com','$2y$10$VrTtU9ko4RLqtkYPPI24rOm1cNWLkn7ILZjEUrYvwEAK/gtOxPW4W',1),(2,'Michelle','Chan','2021-11-05 12:17:56','mchan@gmail.com','$2y$10$gnucE.JktmEyk75TLh8U/eCebq3PRb6a6m8LU/PWvQ5DUFUtB6qi.',1),(3,'Vanessa','Chan','2021-11-05 12:17:56','vchan@gmail.com','$2y$10$0LXu1RoFDFt36yK32dr0O.rWJpdnIW6Heuv7YNlYRH6hn/tp7T4GW',2),(4,'Yunsy','Yin','2021-11-05 12:17:56','yyin@gmail.com','$2y$10$RnONFrIvSR/QFS0xuhE/beXEvaKAjqoXCbyjhN.nKawt/yCU1wv2G',1),(5,'John','Lin','2021-11-05 12:17:56','jlin@gmail.com','jkhkj1239',2),(6,'Buyer','B','2021-11-26 16:32:47','buyer@gmail.com','$2y$10$3Q8Hi6OpbBiX6Ojft/yF9..MhIp8QDMdqOclFoAldZ156KyDoGkeS',1),(7,'Seller','S','2021-11-26 16:38:34','seller@gmail.com','$2y$10$0KZGZkNP7ZUClk0mvlfM2u.x6BgvzU/BxUsdolpRKL983uxApTClC',2),(8,'Buyertwo','B','2021-11-27 01:56:37','buyer2@gmail.com','$2y$10$yRQ4VhzlHeK1Ct0vzF.JgujZor3H.P6N2Mxx4sMF3Vx0EV89QcHLW',1);
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

SET GLOBAL event_scheduler = ON;
DROP EVENT IF EXISTS `refresh_recommendations`;
CREATE EVENT refresh_recommendations
    ON SCHEDULE
        EVERY 1 DAY
            STARTS '2021-11-01 04:00:00'
    DO
    BEGIN
        TRUNCATE item_recommendations;
        INSERT INTO item_recommendations
            (SELECT buyer_bids.buyer_id,
                    similar_buyers_bids.item_id AS recommended_items,
                    COUNT(*)                    AS item_score
             FROM bids buyer_bids
                      INNER JOIN bids similar_buyers # other buyers who bought the same items
                                 ON buyer_bids.item_id = similar_buyers.item_id
                                     AND similar_buyers.buyer_id != buyer_bids.buyer_id
                      INNER JOIN bids similar_buyers_bids # items bought by similar buyers
                                 ON similar_buyers.buyer_id = similar_buyers_bids.buyer_id
                      JOIN items i
                           ON similar_buyers_bids.item_id = i.item_id AND
                              i.auction_end_datetime > NOW() # filter ended products
             WHERE NOT EXISTS( # filter out items already bought by the buyer
                     SELECT *
                     FROM bids
                     WHERE buyer_bids.buyer_id = bids.buyer_id
                       AND similar_buyers_bids.item_id = bids.item_id
                 )
             GROUP BY buyer_bids.buyer_id, similar_buyers_bids.item_id);
    END;

-- Dump completed on 2021-12-06 14:18:48
