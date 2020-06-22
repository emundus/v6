
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

ALTER TABLE `jos_emundus_campaign_candidature` ADD `can_be_deleted` int(1) DEFAULT NULL;

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params) VALUES ('can_be_deleted', 254, 'yesno', 'CAN_BE_DELETED', 0, null, '2020-06-08 15:00:50', 62, 'sysadmin_nantes', null, 0, 0, 0, '', 0, 0, 11, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"yesno_default":"1","options_per_row":"4","toggle_others":"0","toggle_where":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
