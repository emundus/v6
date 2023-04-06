-- MySQL dump 10.13  Distrib 8.0.18, for osx10.15 (x86_64)
--
-- Host: 127.0.0.1    Database: joomla4_test
-- ------------------------------------------------------
-- Server version	8.0.31

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
-- Table structure for table `jos_action_log_config`
--

DROP TABLE IF EXISTS `jos_action_log_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_action_log_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `id_holder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_holder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_action_log_config`
--

LOCK TABLES `jos_action_log_config` WRITE;
/*!40000 ALTER TABLE `jos_action_log_config` DISABLE KEYS */;
INSERT INTO `jos_action_log_config` VALUES (1,'article','com_content.article','id','title','#__content','PLG_ACTIONLOG_JOOMLA'),(2,'article','com_content.form','id','title','#__content','PLG_ACTIONLOG_JOOMLA'),(3,'banner','com_banners.banner','id','name','#__banners','PLG_ACTIONLOG_JOOMLA'),(4,'user_note','com_users.note','id','subject','#__user_notes','PLG_ACTIONLOG_JOOMLA'),(5,'media','com_media.file','','name','','PLG_ACTIONLOG_JOOMLA'),(6,'category','com_categories.category','id','title','#__categories','PLG_ACTIONLOG_JOOMLA'),(7,'menu','com_menus.menu','id','title','#__menu_types','PLG_ACTIONLOG_JOOMLA'),(8,'menu_item','com_menus.item','id','title','#__menu','PLG_ACTIONLOG_JOOMLA'),(9,'newsfeed','com_newsfeeds.newsfeed','id','name','#__newsfeeds','PLG_ACTIONLOG_JOOMLA'),(10,'link','com_redirect.link','id','old_url','#__redirect_links','PLG_ACTIONLOG_JOOMLA'),(11,'tag','com_tags.tag','id','title','#__tags','PLG_ACTIONLOG_JOOMLA'),(12,'style','com_templates.style','id','title','#__template_styles','PLG_ACTIONLOG_JOOMLA'),(13,'plugin','com_plugins.plugin','extension_id','name','#__extensions','PLG_ACTIONLOG_JOOMLA'),(14,'component_config','com_config.component','extension_id','name','','PLG_ACTIONLOG_JOOMLA'),(15,'contact','com_contact.contact','id','name','#__contact_details','PLG_ACTIONLOG_JOOMLA'),(16,'module','com_modules.module','id','title','#__modules','PLG_ACTIONLOG_JOOMLA'),(17,'access_level','com_users.level','id','title','#__viewlevels','PLG_ACTIONLOG_JOOMLA'),(18,'banner_client','com_banners.client','id','name','#__banner_clients','PLG_ACTIONLOG_JOOMLA'),(19,'application_config','com_config.application','','name','','PLG_ACTIONLOG_JOOMLA'),(20,'task','com_scheduler.task','id','title','#__scheduler_tasks','PLG_ACTIONLOG_JOOMLA');
/*!40000 ALTER TABLE `jos_action_log_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_action_logs`
--

DROP TABLE IF EXISTS `jos_action_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_action_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_language_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_date` datetime NOT NULL,
  `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_id` int NOT NULL DEFAULT '0',
  `item_id` int NOT NULL DEFAULT '0',
  `ip_address` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.0.0.0',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_user_id_logdate` (`user_id`,`log_date`),
  KEY `idx_user_id_extension` (`user_id`,`extension`),
  KEY `idx_extension_item_id` (`extension`,`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_action_logs`
--

LOCK TABLES `jos_action_logs` WRITE;
/*!40000 ALTER TABLE `jos_action_logs` DISABLE KEYS */;
INSERT INTO `jos_action_logs` VALUES (1,'PLG_ACTIONLOG_JOOMLA_USER_LOGGED_IN','{\"action\":\"login\",\"userid\":650,\"username\":\"sysadmin\",\"accountlink\":\"index.php?option=com_users&task=user.edit&id=650\",\"app\":\"PLG_ACTIONLOG_JOOMLA_APPLICATION_ADMINISTRATOR\"}','2023-04-06 13:48:30','com_users',650,0,'COM_ACTIONLOGS_DISABLED');
/*!40000 ALTER TABLE `jos_action_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_action_logs_extensions`
--

DROP TABLE IF EXISTS `jos_action_logs_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_action_logs_extensions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `extension` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_action_logs_extensions`
--

LOCK TABLES `jos_action_logs_extensions` WRITE;
/*!40000 ALTER TABLE `jos_action_logs_extensions` DISABLE KEYS */;
INSERT INTO `jos_action_logs_extensions` VALUES (1,'com_banners'),(2,'com_cache'),(3,'com_categories'),(4,'com_config'),(5,'com_contact'),(6,'com_content'),(7,'com_installer'),(8,'com_media'),(9,'com_menus'),(10,'com_messages'),(11,'com_modules'),(12,'com_newsfeeds'),(13,'com_plugins'),(14,'com_redirect'),(15,'com_tags'),(16,'com_templates'),(17,'com_users'),(18,'com_checkin'),(19,'com_scheduler');
/*!40000 ALTER TABLE `jos_action_logs_extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_action_logs_users`
--

DROP TABLE IF EXISTS `jos_action_logs_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_action_logs_users` (
  `user_id` int unsigned NOT NULL,
  `notify` tinyint unsigned NOT NULL,
  `extensions` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `idx_notify` (`notify`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_action_logs_users`
--

LOCK TABLES `jos_action_logs_users` WRITE;
/*!40000 ALTER TABLE `jos_action_logs_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_action_logs_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_assets`
--

DROP TABLE IF EXISTS `jos_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_assets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int NOT NULL DEFAULT '0' COMMENT 'Nested set parent.',
  `lft` int NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `level` int unsigned NOT NULL COMMENT 'The cached level in the nested tree.',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The unique name for the asset.\n',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The descriptive title for the asset.',
  `rules` varchar(5120) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'JSON encoded access control.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_asset_name` (`name`),
  KEY `idx_lft_rgt` (`lft`,`rgt`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_assets`
--

LOCK TABLES `jos_assets` WRITE;
/*!40000 ALTER TABLE `jos_assets` DISABLE KEYS */;
INSERT INTO `jos_assets` VALUES (1,0,0,167,0,'root.1','Root Asset','{\"core.login.site\":{\"6\":1,\"2\":1},\"core.login.admin\":{\"6\":1},\"core.login.api\":{\"8\":1},\"core.login.offline\":{\"6\":1},\"core.admin\":{\"8\":1},\"core.manage\":{\"7\":1},\"core.create\":{\"6\":1,\"3\":1},\"core.delete\":{\"6\":1},\"core.edit\":{\"6\":1,\"4\":1},\"core.edit.state\":{\"6\":1,\"5\":1},\"core.edit.own\":{\"6\":1,\"3\":1}}'),(2,1,1,2,1,'com_admin','com_admin','{}'),(3,1,3,6,1,'com_banners','com_banners','{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1}}'),(4,1,7,8,1,'com_cache','com_cache','{\"core.admin\":{\"7\":1},\"core.manage\":{\"7\":1}}'),(5,1,9,10,1,'com_checkin','com_checkin','{\"core.admin\":{\"7\":1},\"core.manage\":{\"7\":1}}'),(6,1,11,12,1,'com_config','com_config','{}'),(7,1,13,16,1,'com_contact','com_contact','{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1}}'),(8,1,17,38,1,'com_content','com_content','{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1},\"core.create\":{\"3\":1},\"core.edit\":{\"4\":1},\"core.edit.state\":{\"5\":1},\"core.execute.transition\":{\"6\":1,\"5\":1}}'),(9,1,39,40,1,'com_cpanel','com_cpanel','{}'),(10,1,41,42,1,'com_installer','com_installer','{\"core.manage\":{\"7\":0},\"core.delete\":{\"7\":0},\"core.edit.state\":{\"7\":0}}'),(11,1,43,46,1,'com_languages','com_languages','{\"core.admin\":{\"7\":1}}'),(12,1,47,48,1,'com_login','com_login','{}'),(14,1,49,50,1,'com_massmail','com_massmail','{}'),(15,1,51,52,1,'com_media','com_media','{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1},\"core.create\":{\"3\":1},\"core.delete\":{\"5\":1}}'),(16,1,53,56,1,'com_menus','com_menus','{\"core.admin\":{\"7\":1}}'),(17,1,57,58,1,'com_messages','com_messages','{\"core.admin\":{\"7\":1},\"core.manage\":{\"7\":1}}'),(18,1,59,132,1,'com_modules','com_modules','{\"core.admin\":{\"7\":1}}'),(19,1,133,136,1,'com_newsfeeds','com_newsfeeds','{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1}}'),(20,1,137,138,1,'com_plugins','com_plugins','{\"core.admin\":{\"7\":1}}'),(21,1,139,140,1,'com_redirect','com_redirect','{\"core.admin\":{\"7\":1}}'),(23,1,141,142,1,'com_templates','com_templates','{\"core.admin\":{\"7\":1}}'),(24,1,147,150,1,'com_users','com_users','{\"core.admin\":{\"7\":1}}'),(26,1,151,152,1,'com_wrapper','com_wrapper','{}'),(27,8,18,19,2,'com_content.category.2','Uncategorised','{}'),(28,3,4,5,2,'com_banners.category.3','Uncategorised','{}'),(29,7,14,15,2,'com_contact.category.4','Uncategorised','{}'),(30,19,134,135,2,'com_newsfeeds.category.5','Uncategorised','{}'),(32,24,148,149,2,'com_users.category.7','Uncategorised','{}'),(33,1,153,154,1,'com_finder','com_finder','{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1}}'),(34,1,155,156,1,'com_joomlaupdate','com_joomlaupdate','{}'),(35,1,157,158,1,'com_tags','com_tags','{}'),(36,1,159,160,1,'com_contenthistory','com_contenthistory','{}'),(37,1,161,162,1,'com_ajax','com_ajax','{}'),(38,1,163,164,1,'com_postinstall','com_postinstall','{}'),(39,18,60,61,2,'com_modules.module.1','Main Menu','{}'),(40,18,62,63,2,'com_modules.module.2','Login','{}'),(41,18,64,65,2,'com_modules.module.3','Popular Articles','{}'),(42,18,66,67,2,'com_modules.module.4','Recently Added Articles','{}'),(43,18,68,69,2,'com_modules.module.8','Toolbar','{}'),(44,18,70,71,2,'com_modules.module.9','Notifications','{}'),(45,18,72,73,2,'com_modules.module.10','Logged-in Users','{}'),(46,18,74,75,2,'com_modules.module.12','Admin Menu','{}'),(48,18,80,81,2,'com_modules.module.14','User Status','{}'),(49,18,82,83,2,'com_modules.module.15','Title','{}'),(50,18,84,85,2,'com_modules.module.16','Login Form','{}'),(51,18,86,87,2,'com_modules.module.17','Breadcrumbs','{}'),(52,18,88,89,2,'com_modules.module.79','Multilanguage status','{}'),(53,18,92,93,2,'com_modules.module.86','Joomla Version','{}'),(54,16,54,55,2,'com_menus.menu.1','Main Menu','{}'),(55,18,96,97,2,'com_modules.module.87','Sample Data','{}'),(56,8,20,37,2,'com_content.workflow.1','COM_WORKFLOW_BASIC_WORKFLOW','{}'),(57,56,21,22,3,'com_content.stage.1','COM_WORKFLOW_BASIC_STAGE','{}'),(58,56,23,24,3,'com_content.transition.1','Unpublish','{}'),(59,56,25,26,3,'com_content.transition.2','Publish','{}'),(60,56,27,28,3,'com_content.transition.3','Trash','{}'),(61,56,29,30,3,'com_content.transition.4','Archive','{}'),(62,56,31,32,3,'com_content.transition.5','Feature','{}'),(63,56,33,34,3,'com_content.transition.6','Unfeature','{}'),(64,56,35,36,3,'com_content.transition.7','Publish & Feature','{}'),(65,1,143,144,1,'com_privacy','com_privacy','{}'),(66,1,145,146,1,'com_actionlogs','com_actionlogs','{}'),(67,18,76,77,2,'com_modules.module.88','Latest Actions','{}'),(68,18,78,79,2,'com_modules.module.89','Privacy Dashboard','{}'),(70,18,90,91,2,'com_modules.module.103','Site','{}'),(71,18,94,95,2,'com_modules.module.104','System','{}'),(72,18,98,99,2,'com_modules.module.91','System Dashboard','{}'),(73,18,100,101,2,'com_modules.module.92','Content Dashboard','{}'),(74,18,102,103,2,'com_modules.module.93','Menus Dashboard','{}'),(75,18,104,105,2,'com_modules.module.94','Components Dashboard','{}'),(76,18,106,107,2,'com_modules.module.95','Users Dashboard','{}'),(77,18,108,109,2,'com_modules.module.99','Frontend Link','{}'),(78,18,110,111,2,'com_modules.module.100','Messages','{}'),(79,18,112,113,2,'com_modules.module.101','Post Install Messages','{}'),(80,18,114,115,2,'com_modules.module.102','User Status','{}'),(82,18,116,117,2,'com_modules.module.105','3rd Party','{}'),(83,18,118,119,2,'com_modules.module.106','Help Dashboard','{}'),(84,18,120,121,2,'com_modules.module.107','Privacy Requests','{}'),(85,18,122,123,2,'com_modules.module.108','Privacy Status','{}'),(86,18,124,125,2,'com_modules.module.96','Popular Articles','{}'),(87,18,126,127,2,'com_modules.module.97','Recently Added Articles','{}'),(88,18,128,129,2,'com_modules.module.98','Logged-in Users','{}'),(89,18,130,131,2,'com_modules.module.90','Login Support','{}'),(90,1,165,166,1,'com_scheduler','com_scheduler','{}'),(91,11,44,45,2,'com_languages.language.2','French (fr-FR)','{}');
/*!40000 ALTER TABLE `jos_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_associations`
--

DROP TABLE IF EXISTS `jos_associations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_associations` (
  `id` int NOT NULL COMMENT 'A reference to the associated item.',
  `context` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The context of the associated item.',
  `key` char(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The key for the association computed from an md5 on associated ids.',
  PRIMARY KEY (`context`,`id`),
  KEY `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_associations`
--

LOCK TABLES `jos_associations` WRITE;
/*!40000 ALTER TABLE `jos_associations` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_associations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_banner_clients`
--

DROP TABLE IF EXISTS `jos_banner_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_banner_clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `extrainfo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` tinyint NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `metakey` text COLLATE utf8mb4_unicode_ci,
  `own_prefix` tinyint NOT NULL DEFAULT '0',
  `metakey_prefix` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `purchase_type` tinyint NOT NULL DEFAULT '-1',
  `track_clicks` tinyint NOT NULL DEFAULT '-1',
  `track_impressions` tinyint NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  KEY `idx_own_prefix` (`own_prefix`),
  KEY `idx_metakey_prefix` (`metakey_prefix`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_banner_clients`
--

LOCK TABLES `jos_banner_clients` WRITE;
/*!40000 ALTER TABLE `jos_banner_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_banner_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_banner_tracks`
--

DROP TABLE IF EXISTS `jos_banner_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_banner_tracks` (
  `track_date` datetime NOT NULL,
  `track_type` int unsigned NOT NULL,
  `banner_id` int unsigned NOT NULL,
  `count` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`track_date`,`track_type`,`banner_id`),
  KEY `idx_track_date` (`track_date`),
  KEY `idx_track_type` (`track_type`),
  KEY `idx_banner_id` (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_banner_tracks`
--

LOCK TABLES `jos_banner_tracks` WRITE;
/*!40000 ALTER TABLE `jos_banner_tracks` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_banner_tracks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_banners`
--

DROP TABLE IF EXISTS `jos_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_banners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cid` int NOT NULL DEFAULT '0',
  `type` int NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `imptotal` int NOT NULL DEFAULT '0',
  `impmade` int NOT NULL DEFAULT '0',
  `clicks` int NOT NULL DEFAULT '0',
  `clickurl` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `state` tinyint NOT NULL DEFAULT '0',
  `catid` int unsigned NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `custombannercode` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sticky` tinyint unsigned NOT NULL DEFAULT '0',
  `ordering` int NOT NULL DEFAULT '0',
  `metakey` text COLLATE utf8mb4_unicode_ci,
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `own_prefix` tinyint NOT NULL DEFAULT '0',
  `metakey_prefix` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `purchase_type` tinyint NOT NULL DEFAULT '-1',
  `track_clicks` tinyint NOT NULL DEFAULT '-1',
  `track_impressions` tinyint NOT NULL DEFAULT '-1',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `publish_up` datetime DEFAULT NULL,
  `publish_down` datetime DEFAULT NULL,
  `reset` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT '0',
  `version` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`),
  KEY `idx_own_prefix` (`own_prefix`),
  KEY `idx_metakey_prefix` (`metakey_prefix`(100)),
  KEY `idx_banner_catid` (`catid`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_banners`
--

LOCK TABLES `jos_banners` WRITE;
/*!40000 ALTER TABLE `jos_banners` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_categories`
--

DROP TABLE IF EXISTS `jos_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `parent_id` int unsigned NOT NULL DEFAULT '0',
  `lft` int NOT NULL DEFAULT '0',
  `rgt` int NOT NULL DEFAULT '0',
  `level` int unsigned NOT NULL DEFAULT '0',
  `path` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `published` tinyint NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `access` int unsigned NOT NULL DEFAULT '0',
  `params` text COLLATE utf8mb4_unicode_ci,
  `metadesc` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'The keywords for the page.',
  `metadata` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL,
  `modified_user_id` int unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL,
  `hits` int unsigned NOT NULL DEFAULT '0',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `version` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `cat_idx` (`extension`,`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`(100)),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`(100)),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_categories`
--

LOCK TABLES `jos_categories` WRITE;
/*!40000 ALTER TABLE `jos_categories` DISABLE KEYS */;
INSERT INTO `jos_categories` VALUES (1,0,0,0,11,0,'','system','ROOT','root','','',1,NULL,NULL,1,'{}','','','{}',650,'2023-04-06 13:47:53',650,'2023-04-06 13:47:53',0,'*',1),(2,27,1,1,2,1,'uncategorised','com_content','Uncategorised','uncategorised','','',1,NULL,NULL,1,'{\"category_layout\":\"\",\"image\":\"\",\"workflow_id\":\"use_default\"}','','','{\"author\":\"\",\"robots\":\"\"}',650,'2023-04-06 13:47:53',650,'2023-04-06 13:47:53',0,'*',1),(3,28,1,3,4,1,'uncategorised','com_banners','Uncategorised','uncategorised','','',1,NULL,NULL,1,'{\"category_layout\":\"\",\"image\":\"\"}','','','{\"author\":\"\",\"robots\":\"\"}',650,'2023-04-06 13:47:53',650,'2023-04-06 13:47:53',0,'*',1),(4,29,1,5,6,1,'uncategorised','com_contact','Uncategorised','uncategorised','','',1,NULL,NULL,1,'{\"category_layout\":\"\",\"image\":\"\"}','','','{\"author\":\"\",\"robots\":\"\"}',650,'2023-04-06 13:47:53',650,'2023-04-06 13:47:53',0,'*',1),(5,30,1,7,8,1,'uncategorised','com_newsfeeds','Uncategorised','uncategorised','','',1,NULL,NULL,1,'{\"category_layout\":\"\",\"image\":\"\"}','','','{\"author\":\"\",\"robots\":\"\"}',650,'2023-04-06 13:47:53',650,'2023-04-06 13:47:53',0,'*',1),(7,32,1,9,10,1,'uncategorised','com_users','Uncategorised','uncategorised','','',1,NULL,NULL,1,'{\"category_layout\":\"\",\"image\":\"\"}','','','{\"author\":\"\",\"robots\":\"\"}',650,'2023-04-06 13:47:53',650,'2023-04-06 13:47:53',0,'*',1);
/*!40000 ALTER TABLE `jos_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_contact_details`
--

DROP TABLE IF EXISTS `jos_contact_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_contact_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `con_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `suburb` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `misc` mediumtext COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_con` tinyint unsigned NOT NULL DEFAULT '0',
  `published` tinyint NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` int NOT NULL DEFAULT '0',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `catid` int NOT NULL DEFAULT '0',
  `access` int unsigned NOT NULL DEFAULT '0',
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `webpage` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sortname1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sortname2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sortname3` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `language` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT '0',
  `metakey` text COLLATE utf8mb4_unicode_ci,
  `metadesc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `featured` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Set if contact is featured.',
  `publish_up` datetime DEFAULT NULL,
  `publish_down` datetime DEFAULT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `hits` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_contact_details`
--

LOCK TABLES `jos_contact_details` WRITE;
/*!40000 ALTER TABLE `jos_contact_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_contact_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_content`
--

DROP TABLE IF EXISTS `jos_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_content` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `introtext` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `fulltext` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` tinyint NOT NULL DEFAULT '0',
  `catid` int unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `publish_up` datetime DEFAULT NULL,
  `publish_down` datetime DEFAULT NULL,
  `images` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `urls` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribs` varchar(5120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `ordering` int NOT NULL DEFAULT '0',
  `metakey` text COLLATE utf8mb4_unicode_ci,
  `metadesc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `access` int unsigned NOT NULL DEFAULT '0',
  `hits` int unsigned NOT NULL DEFAULT '0',
  `metadata` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `featured` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The language code for the article.',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_alias` (`alias`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_content`
--

LOCK TABLES `jos_content` WRITE;
/*!40000 ALTER TABLE `jos_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_content_frontpage`
--

DROP TABLE IF EXISTS `jos_content_frontpage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_content_frontpage` (
  `content_id` int NOT NULL DEFAULT '0',
  `ordering` int NOT NULL DEFAULT '0',
  `featured_up` datetime DEFAULT NULL,
  `featured_down` datetime DEFAULT NULL,
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_content_frontpage`
--

LOCK TABLES `jos_content_frontpage` WRITE;
/*!40000 ALTER TABLE `jos_content_frontpage` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_content_frontpage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_content_rating`
--

DROP TABLE IF EXISTS `jos_content_rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_content_rating` (
  `content_id` int NOT NULL DEFAULT '0',
  `rating_sum` int unsigned NOT NULL DEFAULT '0',
  `rating_count` int unsigned NOT NULL DEFAULT '0',
  `lastip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_content_rating`
--

LOCK TABLES `jos_content_rating` WRITE;
/*!40000 ALTER TABLE `jos_content_rating` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_content_rating` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_content_types`
--

DROP TABLE IF EXISTS `jos_content_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_content_types` (
  `type_id` int unsigned NOT NULL AUTO_INCREMENT,
  `type_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type_alias` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `table` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `rules` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_mappings` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `router` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content_history_options` varchar(5120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'JSON string for com_contenthistory options',
  PRIMARY KEY (`type_id`),
  KEY `idx_alias` (`type_alias`(100))
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_content_types`
--

LOCK TABLES `jos_content_types` WRITE;
/*!40000 ALTER TABLE `jos_content_types` DISABLE KEYS */;
INSERT INTO `jos_content_types` VALUES (1,'Article','com_content.article','{\"special\":{\"dbtable\":\"#__content\",\"key\":\"id\",\"type\":\"ArticleTable\",\"prefix\":\"Joomla\\\\Component\\\\Content\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"state\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"introtext\", \"core_hits\":\"hits\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"attribs\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"urls\", \"core_version\":\"version\", \"core_ordering\":\"ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"asset_id\":\"asset_id\", \"note\":\"note\"}, \"special\":{\"fulltext\":\"fulltext\"}}','ContentHelperRoute::getArticleRoute','{\"formFile\":\"administrator\\/components\\/com_content\\/forms\\/article.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\"],\"ignoreChanges\":[\"modified_by\", \"modified\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"ordering\"],\"convertToInt\":[\"publish_up\", \"publish_down\", \"featured\", \"ordering\"],\"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ]}'),(2,'Contact','com_contact.contact','{\"special\":{\"dbtable\":\"#__contact_details\",\"key\":\"id\",\"type\":\"ContactTable\",\"prefix\":\"Joomla\\\\Component\\\\Contact\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"name\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"address\", \"core_hits\":\"hits\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"image\", \"core_urls\":\"webpage\", \"core_version\":\"version\", \"core_ordering\":\"ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"asset_id\":\"null\"}, \"special\":{\"con_position\":\"con_position\",\"suburb\":\"suburb\",\"state\":\"state\",\"country\":\"country\",\"postcode\":\"postcode\",\"telephone\":\"telephone\",\"fax\":\"fax\",\"misc\":\"misc\",\"email_to\":\"email_to\",\"default_con\":\"default_con\",\"user_id\":\"user_id\",\"mobile\":\"mobile\",\"sortname1\":\"sortname1\",\"sortname2\":\"sortname2\",\"sortname3\":\"sortname3\"}}','ContactHelperRoute::getContactRoute','{\"formFile\":\"administrator\\/components\\/com_contact\\/forms\\/contact.xml\",\"hideFields\":[\"default_con\",\"checked_out\",\"checked_out_time\",\"version\"],\"ignoreChanges\":[\"modified_by\", \"modified\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\"],\"convertToInt\":[\"publish_up\", \"publish_down\", \"featured\", \"ordering\"], \"displayLookup\":[ {\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ] }'),(3,'Newsfeed','com_newsfeeds.newsfeed','{\"special\":{\"dbtable\":\"#__newsfeeds\",\"key\":\"id\",\"type\":\"NewsfeedTable\",\"prefix\":\"Joomla\\\\Component\\\\Newsfeeds\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"name\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"link\", \"core_version\":\"version\", \"core_ordering\":\"ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"asset_id\":\"null\"}, \"special\":{\"numarticles\":\"numarticles\",\"cache_time\":\"cache_time\",\"rtl\":\"rtl\"}}','NewsfeedsHelperRoute::getNewsfeedRoute','{\"formFile\":\"administrator\\/components\\/com_newsfeeds\\/forms\\/newsfeed.xml\",\"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\"],\"ignoreChanges\":[\"modified_by\", \"modified\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\"],\"convertToInt\":[\"publish_up\", \"publish_down\", \"featured\", \"ordering\"],\"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ]}'),(4,'User','com_users.user','{\"special\":{\"dbtable\":\"#__users\",\"key\":\"id\",\"type\":\"User\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"name\",\"core_state\":\"null\",\"core_alias\":\"username\",\"core_created_time\":\"registerDate\",\"core_modified_time\":\"lastvisitDate\",\"core_body\":\"null\", \"core_hits\":\"null\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"access\":\"null\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"null\", \"core_language\":\"null\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"null\", \"core_ordering\":\"null\", \"core_metakey\":\"null\", \"core_metadesc\":\"null\", \"core_catid\":\"null\", \"asset_id\":\"null\"}, \"special\":{}}','',''),(5,'Article Category','com_content.category','{\"special\":{\"dbtable\":\"#__categories\",\"key\":\"id\",\"type\":\"CategoryTable\",\"prefix\":\"Joomla\\\\Component\\\\Categories\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"version\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"parent_id\", \"asset_id\":\"asset_id\"}, \"special\":{\"parent_id\":\"parent_id\",\"lft\":\"lft\",\"rgt\":\"rgt\",\"level\":\"level\",\"path\":\"path\",\"extension\":\"extension\",\"note\":\"note\"}}','ContentHelperRoute::getCategoryRoute','{\"formFile\":\"administrator\\/components\\/com_categories\\/forms\\/category.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\",\"lft\",\"rgt\",\"level\",\"path\",\"extension\"], \"ignoreChanges\":[\"modified_user_id\", \"modified_time\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"path\"],\"convertToInt\":[\"publish_up\", \"publish_down\"], \"displayLookup\":[{\"sourceColumn\":\"created_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"parent_id\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}]}'),(6,'Contact Category','com_contact.category','{\"special\":{\"dbtable\":\"#__categories\",\"key\":\"id\",\"type\":\"CategoryTable\",\"prefix\":\"Joomla\\\\Component\\\\Categories\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"version\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"parent_id\", \"asset_id\":\"asset_id\"}, \"special\":{\"parent_id\":\"parent_id\",\"lft\":\"lft\",\"rgt\":\"rgt\",\"level\":\"level\",\"path\":\"path\",\"extension\":\"extension\",\"note\":\"note\"}}','ContactHelperRoute::getCategoryRoute','{\"formFile\":\"administrator\\/components\\/com_categories\\/forms\\/category.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\",\"lft\",\"rgt\",\"level\",\"path\",\"extension\"], \"ignoreChanges\":[\"modified_user_id\", \"modified_time\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"path\"],\"convertToInt\":[\"publish_up\", \"publish_down\"], \"displayLookup\":[{\"sourceColumn\":\"created_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"parent_id\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}]}'),(7,'Newsfeeds Category','com_newsfeeds.category','{\"special\":{\"dbtable\":\"#__categories\",\"key\":\"id\",\"type\":\"CategoryTable\",\"prefix\":\"Joomla\\\\Component\\\\Categories\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"version\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"parent_id\", \"asset_id\":\"asset_id\"}, \"special\":{\"parent_id\":\"parent_id\",\"lft\":\"lft\",\"rgt\":\"rgt\",\"level\":\"level\",\"path\":\"path\",\"extension\":\"extension\",\"note\":\"note\"}}','NewsfeedsHelperRoute::getCategoryRoute','{\"formFile\":\"administrator\\/components\\/com_categories\\/forms\\/category.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\",\"lft\",\"rgt\",\"level\",\"path\",\"extension\"], \"ignoreChanges\":[\"modified_user_id\", \"modified_time\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"path\"],\"convertToInt\":[\"publish_up\", \"publish_down\"], \"displayLookup\":[{\"sourceColumn\":\"created_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"parent_id\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}]}'),(8,'Tag','com_tags.tag','{\"special\":{\"dbtable\":\"#__tags\",\"key\":\"tag_id\",\"type\":\"TagTable\",\"prefix\":\"Joomla\\\\Component\\\\Tags\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"urls\", \"core_version\":\"version\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"null\", \"asset_id\":\"null\"}, \"special\":{\"parent_id\":\"parent_id\",\"lft\":\"lft\",\"rgt\":\"rgt\",\"level\":\"level\",\"path\":\"path\"}}','TagsHelperRoute::getTagRoute','{\"formFile\":\"administrator\\/components\\/com_tags\\/forms\\/tag.xml\", \"hideFields\":[\"checked_out\",\"checked_out_time\",\"version\", \"lft\", \"rgt\", \"level\", \"path\", \"urls\", \"publish_up\", \"publish_down\"],\"ignoreChanges\":[\"modified_user_id\", \"modified_time\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"path\"],\"convertToInt\":[\"publish_up\", \"publish_down\"], \"displayLookup\":[{\"sourceColumn\":\"created_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"}, {\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}, {\"sourceColumn\":\"modified_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"}]}'),(9,'Banner','com_banners.banner','{\"special\":{\"dbtable\":\"#__banners\",\"key\":\"id\",\"type\":\"BannerTable\",\"prefix\":\"Joomla\\\\Component\\\\Banners\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"name\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"description\", \"core_hits\":\"null\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"link\", \"core_version\":\"version\", \"core_ordering\":\"ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"asset_id\":\"null\"}, \"special\":{\"imptotal\":\"imptotal\", \"impmade\":\"impmade\", \"clicks\":\"clicks\", \"clickurl\":\"clickurl\", \"custombannercode\":\"custombannercode\", \"cid\":\"cid\", \"purchase_type\":\"purchase_type\", \"track_impressions\":\"track_impressions\", \"track_clicks\":\"track_clicks\"}}','','{\"formFile\":\"administrator\\/components\\/com_banners\\/forms\\/banner.xml\", \"hideFields\":[\"checked_out\",\"checked_out_time\",\"version\", \"reset\"],\"ignoreChanges\":[\"modified_by\", \"modified\", \"checked_out\", \"checked_out_time\", \"version\", \"imptotal\", \"impmade\", \"reset\"], \"convertToInt\":[\"publish_up\", \"publish_down\", \"ordering\"], \"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}, {\"sourceColumn\":\"cid\",\"targetTable\":\"#__banner_clients\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"}, {\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ]}'),(10,'Banners Category','com_banners.category','{\"special\":{\"dbtable\":\"#__categories\",\"key\":\"id\",\"type\":\"CategoryTable\",\"prefix\":\"Joomla\\\\Component\\\\Categories\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"version\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"parent_id\", \"asset_id\":\"asset_id\"}, \"special\": {\"parent_id\":\"parent_id\",\"lft\":\"lft\",\"rgt\":\"rgt\",\"level\":\"level\",\"path\":\"path\",\"extension\":\"extension\",\"note\":\"note\"}}','','{\"formFile\":\"administrator\\/components\\/com_categories\\/forms\\/category.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\",\"lft\",\"rgt\",\"level\",\"path\",\"extension\"], \"ignoreChanges\":[\"modified_user_id\", \"modified_time\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"path\"], \"convertToInt\":[\"publish_up\", \"publish_down\"], \"displayLookup\":[{\"sourceColumn\":\"created_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"parent_id\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}]}'),(11,'Banner Client','com_banners.client','{\"special\":{\"dbtable\":\"#__banner_clients\",\"key\":\"id\",\"type\":\"ClientTable\",\"prefix\":\"Joomla\\\\Component\\\\Banners\\\\Administrator\\\\Table\\\\\"}}','','','','{\"formFile\":\"administrator\\/components\\/com_banners\\/forms\\/client.xml\", \"hideFields\":[\"checked_out\",\"checked_out_time\"], \"ignoreChanges\":[\"checked_out\", \"checked_out_time\"], \"convertToInt\":[], \"displayLookup\":[]}'),(12,'User Notes','com_users.note','{\"special\":{\"dbtable\":\"#__user_notes\",\"key\":\"id\",\"type\":\"NoteTable\",\"prefix\":\"Joomla\\\\Component\\\\Users\\\\Administrator\\\\Table\\\\\"}}','','','','{\"formFile\":\"administrator\\/components\\/com_users\\/forms\\/note.xml\", \"hideFields\":[\"checked_out\",\"checked_out_time\", \"publish_up\", \"publish_down\"],\"ignoreChanges\":[\"modified_user_id\", \"modified_time\", \"checked_out\", \"checked_out_time\"], \"convertToInt\":[\"publish_up\", \"publish_down\"],\"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}, {\"sourceColumn\":\"created_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"}, {\"sourceColumn\":\"user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"}, {\"sourceColumn\":\"modified_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"}]}'),(13,'User Notes Category','com_users.category','{\"special\":{\"dbtable\":\"#__categories\",\"key\":\"id\",\"type\":\"CategoryTable\",\"prefix\":\"Joomla\\\\Component\\\\Categories\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}','','{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"version\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"parent_id\", \"asset_id\":\"asset_id\"}, \"special\":{\"parent_id\":\"parent_id\",\"lft\":\"lft\",\"rgt\":\"rgt\",\"level\":\"level\",\"path\":\"path\",\"extension\":\"extension\",\"note\":\"note\"}}','','{\"formFile\":\"administrator\\/components\\/com_categories\\/forms\\/category.xml\", \"hideFields\":[\"checked_out\",\"checked_out_time\",\"version\",\"lft\",\"rgt\",\"level\",\"path\",\"extension\"], \"ignoreChanges\":[\"modified_user_id\", \"modified_time\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"path\"], \"convertToInt\":[\"publish_up\", \"publish_down\"], \"displayLookup\":[{\"sourceColumn\":\"created_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"}, {\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"parent_id\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}]}');
/*!40000 ALTER TABLE `jos_content_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_contentitem_tag_map`
--

DROP TABLE IF EXISTS `jos_contentitem_tag_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_contentitem_tag_map` (
  `type_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `core_content_id` int unsigned NOT NULL COMMENT 'PK from the core content table',
  `content_item_id` int NOT NULL COMMENT 'PK from the content type table',
  `tag_id` int unsigned NOT NULL COMMENT 'PK from the tag table',
  `tag_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date of most recent save for this tag-item',
  `type_id` mediumint NOT NULL COMMENT 'PK from the content_type table',
  UNIQUE KEY `uc_ItemnameTagid` (`type_id`,`content_item_id`,`tag_id`),
  KEY `idx_tag_type` (`tag_id`,`type_id`),
  KEY `idx_date_id` (`tag_date`,`tag_id`),
  KEY `idx_core_content_id` (`core_content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Maps items from content tables to tags';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_contentitem_tag_map`
--

LOCK TABLES `jos_contentitem_tag_map` WRITE;
/*!40000 ALTER TABLE `jos_contentitem_tag_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_contentitem_tag_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_extensions`
--

DROP TABLE IF EXISTS `jos_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_extensions` (
  `extension_id` int NOT NULL AUTO_INCREMENT,
  `package_id` int NOT NULL DEFAULT '0' COMMENT 'Parent package ID for extensions installed as a package.',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `element` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changelogurl` text COLLATE utf8mb4_unicode_ci,
  `folder` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` tinyint NOT NULL,
  `enabled` tinyint NOT NULL DEFAULT '0',
  `access` int unsigned NOT NULL DEFAULT '1',
  `protected` tinyint NOT NULL DEFAULT '0' COMMENT 'Flag to indicate if the extension is protected. Protected extensions cannot be disabled.',
  `locked` tinyint NOT NULL DEFAULT '0' COMMENT 'Flag to indicate if the extension is locked. Locked extensions cannot be uninstalled.',
  `manifest_cache` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` int DEFAULT '0',
  `state` int DEFAULT '0',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`extension_id`),
  KEY `element_clientid` (`element`,`client_id`),
  KEY `element_folder_clientid` (`element`,`folder`,`client_id`),
  KEY `extension` (`type`,`element`,`folder`,`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_extensions`
--

LOCK TABLES `jos_extensions` WRITE;
/*!40000 ALTER TABLE `jos_extensions` DISABLE KEYS */;
INSERT INTO `jos_extensions` VALUES (1,0,'com_wrapper','component','com_wrapper',NULL,'',1,1,1,0,1,'{\"name\":\"com_wrapper\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2007 Open Source Matters, Inc.\\n\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_WRAPPER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"wrapper\"}','','',NULL,NULL,0,0,NULL),(2,0,'com_admin','component','com_admin',NULL,'',1,1,1,1,1,'{\"name\":\"com_admin\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_ADMIN_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(3,0,'com_banners','component','com_banners',NULL,'',1,1,1,0,1,'{\"name\":\"com_banners\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_BANNERS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"banners\"}','{\"purchase_type\":\"3\",\"track_impressions\":\"0\",\"track_clicks\":\"0\",\"metakey_prefix\":\"\",\"save_history\":\"1\",\"history_limit\":10}','',NULL,NULL,0,0,NULL),(4,0,'com_cache','component','com_cache',NULL,'',1,1,1,1,1,'{\"name\":\"com_cache\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_CACHE_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(5,0,'com_categories','component','com_categories',NULL,'',1,1,1,1,1,'{\"name\":\"com_categories\",\"type\":\"component\",\"creationDate\":\"2007-12\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2007 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_CATEGORIES_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(6,0,'com_checkin','component','com_checkin',NULL,'',1,1,1,1,1,'{\"name\":\"com_checkin\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_CHECKIN_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(7,0,'com_contact','component','com_contact',NULL,'',1,1,1,0,1,'{\"name\":\"com_contact\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_CONTACT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"contact\"}','{\"contact_layout\":\"_:default\",\"show_contact_category\":\"hide\",\"save_history\":\"1\",\"history_limit\":10,\"show_contact_list\":\"0\",\"presentation_style\":\"sliders\",\"show_tags\":\"1\",\"show_info\":\"1\",\"show_name\":\"1\",\"show_position\":\"1\",\"show_email\":\"0\",\"show_street_address\":\"1\",\"show_suburb\":\"1\",\"show_state\":\"1\",\"show_postcode\":\"1\",\"show_country\":\"1\",\"show_telephone\":\"1\",\"show_mobile\":\"1\",\"show_fax\":\"1\",\"show_webpage\":\"1\",\"show_image\":\"1\",\"show_misc\":\"1\",\"image\":\"\",\"allow_vcard\":\"0\",\"show_articles\":\"0\",\"articles_display_num\":\"10\",\"show_profile\":\"0\",\"show_user_custom_fields\":[\"-1\"],\"show_links\":\"0\",\"linka_name\":\"\",\"linkb_name\":\"\",\"linkc_name\":\"\",\"linkd_name\":\"\",\"linke_name\":\"\",\"contact_icons\":\"0\",\"icon_address\":\"\",\"icon_email\":\"\",\"icon_telephone\":\"\",\"icon_mobile\":\"\",\"icon_fax\":\"\",\"icon_misc\":\"\",\"category_layout\":\"_:default\",\"show_category_title\":\"1\",\"show_description\":\"1\",\"show_description_image\":\"0\",\"maxLevel\":\"-1\",\"show_subcat_desc\":\"1\",\"show_empty_categories\":\"0\",\"show_cat_items\":\"1\",\"show_cat_tags\":\"1\",\"show_base_description\":\"1\",\"maxLevelcat\":\"-1\",\"show_subcat_desc_cat\":\"1\",\"show_empty_categories_cat\":\"0\",\"show_cat_items_cat\":\"1\",\"filter_field\":\"0\",\"show_pagination_limit\":\"0\",\"show_headings\":\"1\",\"show_image_heading\":\"0\",\"show_position_headings\":\"1\",\"show_email_headings\":\"0\",\"show_telephone_headings\":\"1\",\"show_mobile_headings\":\"0\",\"show_fax_headings\":\"0\",\"show_suburb_headings\":\"1\",\"show_state_headings\":\"1\",\"show_country_headings\":\"1\",\"show_pagination\":\"2\",\"show_pagination_results\":\"1\",\"initial_sort\":\"ordering\",\"captcha\":\"\",\"show_email_form\":\"1\",\"show_email_copy\":\"0\",\"banned_email\":\"\",\"banned_subject\":\"\",\"banned_text\":\"\",\"validate_session\":\"1\",\"custom_reply\":\"0\",\"redirect\":\"\",\"show_feed_link\":\"1\",\"sef_ids\":1,\"custom_fields_enable\":\"1\"}','',NULL,NULL,0,0,NULL),(8,0,'com_cpanel','component','com_cpanel',NULL,'',1,1,1,1,1,'{\"name\":\"com_cpanel\",\"type\":\"component\",\"creationDate\":\"2007-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2007 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_CPANEL_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(9,0,'com_installer','component','com_installer',NULL,'',1,1,1,1,1,'{\"name\":\"com_installer\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_INSTALLER_XML_DESCRIPTION\",\"group\":\"\"}','{\"cachetimeout\":\"6\",\"minimum_stability\":\"4\"}','',NULL,NULL,0,0,NULL),(10,0,'com_languages','component','com_languages',NULL,'',1,1,1,1,1,'{\"name\":\"com_languages\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_LANGUAGES_XML_DESCRIPTION\",\"group\":\"\"}','{\"administrator\":\"en-GB\",\"site\":\"fr-FR\"}','',NULL,NULL,0,0,NULL),(11,0,'com_login','component','com_login',NULL,'',1,1,1,1,1,'{\"name\":\"com_login\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_LOGIN_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(12,0,'com_media','component','com_media',NULL,'',1,1,0,1,1,'{\"name\":\"com_media\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"COM_MEDIA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"media\"}','{\"upload_maxsize\":\"10\",\"file_path\":\"images\",\"image_path\":\"images\",\"restrict_uploads\":\"1\",\"allowed_media_usergroup\":\"3\",\"restrict_uploads_extensions\":\"bmp,gif,jpg,jpeg,png,webp,ico,mp3,m4a,mp4a,ogg,mp4,mp4v,mpeg,mov,odg,odp,ods,odt,pdf,png,ppt,txt,xcf,xls,csv\",\"check_mime\":\"1\",\"image_extensions\":\"bmp,gif,jpg,png,jpeg,webp\",\"audio_extensions\":\"mp3,m4a,mp4a,ogg\",\"video_extensions\":\"mp4,mp4v,mpeg,mov,webm\",\"doc_extensions\":\"odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv\",\"ignore_extensions\":\"\",\"upload_mime\":\"image\\/jpeg,image\\/gif,image\\/png,image\\/bmp,image\\/webp,audio\\/ogg,audio\\/mpeg,audio\\/mp4,video\\/mp4,video\\/webm,video\\/mpeg,video\\/quicktime,application\\/msword,application\\/excel,application\\/pdf,application\\/powerpoint,text\\/plain,application\\/x-zip\"}','',NULL,NULL,0,0,NULL),(13,0,'com_menus','component','com_menus',NULL,'',1,1,1,1,1,'{\"name\":\"com_menus\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_MENUS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"menus\"}','{\"page_title\":\"\",\"show_page_heading\":0,\"page_heading\":\"\",\"pageclass_sfx\":\"\"}','',NULL,NULL,0,0,NULL),(14,0,'com_messages','component','com_messages',NULL,'',1,1,1,1,1,'{\"name\":\"com_messages\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_MESSAGES_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(15,0,'com_modules','component','com_modules',NULL,'',1,1,1,1,1,'{\"name\":\"com_modules\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_MODULES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"modules\"}','','',NULL,NULL,0,0,NULL),(16,0,'com_newsfeeds','component','com_newsfeeds',NULL,'',1,1,1,0,1,'{\"name\":\"com_newsfeeds\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_NEWSFEEDS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"newsfeeds\"}','{\"newsfeed_layout\":\"_:default\",\"save_history\":\"1\",\"history_limit\":5,\"show_feed_image\":\"1\",\"show_feed_description\":\"1\",\"show_item_description\":\"1\",\"feed_character_count\":\"0\",\"feed_display_order\":\"des\",\"float_first\":\"right\",\"float_second\":\"right\",\"show_tags\":\"1\",\"category_layout\":\"_:default\",\"show_category_title\":\"1\",\"show_description\":\"1\",\"show_description_image\":\"1\",\"maxLevel\":\"-1\",\"show_empty_categories\":\"0\",\"show_subcat_desc\":\"1\",\"show_cat_items\":\"1\",\"show_cat_tags\":\"1\",\"show_base_description\":\"1\",\"maxLevelcat\":\"-1\",\"show_empty_categories_cat\":\"0\",\"show_subcat_desc_cat\":\"1\",\"show_cat_items_cat\":\"1\",\"filter_field\":\"1\",\"show_pagination_limit\":\"1\",\"show_headings\":\"1\",\"show_articles\":\"0\",\"show_link\":\"1\",\"show_pagination\":\"1\",\"show_pagination_results\":\"1\",\"sef_ids\":1}','',NULL,NULL,0,0,NULL),(17,0,'com_plugins','component','com_plugins',NULL,'',1,1,1,1,1,'{\"name\":\"com_plugins\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_PLUGINS_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(18,0,'com_templates','component','com_templates',NULL,'',1,1,1,1,1,'{\"name\":\"com_templates\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_TEMPLATES_XML_DESCRIPTION\",\"group\":\"\"}','{\"template_positions_display\":\"0\",\"upload_limit\":\"10\",\"image_formats\":\"gif,bmp,jpg,jpeg,png,webp\",\"source_formats\":\"txt,less,ini,xml,js,php,css,scss,sass,json\",\"font_formats\":\"woff,woff2,ttf,otf\",\"compressed_formats\":\"zip\"}','',NULL,NULL,0,0,NULL),(19,0,'com_content','component','com_content',NULL,'',1,1,0,1,1,'{\"name\":\"com_content\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_CONTENT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"content\"}','{\"article_layout\":\"_:default\",\"show_title\":\"1\",\"link_titles\":\"1\",\"show_intro\":\"1\",\"info_block_position\":\"0\",\"info_block_show_title\":\"1\",\"show_category\":\"1\",\"link_category\":\"1\",\"show_parent_category\":\"0\",\"link_parent_category\":\"0\",\"show_associations\":\"0\",\"flags\":\"1\",\"show_author\":\"1\",\"link_author\":\"0\",\"show_create_date\":\"0\",\"show_modify_date\":\"0\",\"show_publish_date\":\"1\",\"show_item_navigation\":\"1\",\"show_readmore\":\"1\",\"show_readmore_title\":\"1\",\"readmore_limit\":100,\"show_tags\":\"1\",\"record_hits\":\"1\",\"show_hits\":\"1\",\"show_noauth\":\"0\",\"urls_position\":0,\"captcha\":\"\",\"show_publishing_options\":\"1\",\"show_article_options\":\"1\",\"show_configure_edit_options\":\"1\",\"show_permissions\":\"1\",\"show_associations_edit\":\"1\",\"save_history\":\"1\",\"history_limit\":10,\"show_urls_images_frontend\":\"0\",\"show_urls_images_backend\":\"1\",\"targeta\":0,\"targetb\":0,\"targetc\":0,\"float_intro\":\"left\",\"float_fulltext\":\"left\",\"category_layout\":\"_:blog\",\"show_category_title\":\"0\",\"show_description\":\"0\",\"show_description_image\":\"0\",\"maxLevel\":\"1\",\"show_empty_categories\":\"0\",\"show_no_articles\":\"1\",\"show_category_heading_title_text\":\"1\",\"show_subcat_desc\":\"1\",\"show_cat_num_articles\":\"0\",\"show_cat_tags\":\"1\",\"show_base_description\":\"1\",\"maxLevelcat\":\"-1\",\"show_empty_categories_cat\":\"0\",\"show_subcat_desc_cat\":\"1\",\"show_cat_num_articles_cat\":\"1\",\"num_leading_articles\":1,\"blog_class_leading\":\"\",\"num_intro_articles\":4,\"blog_class\":\"\",\"num_columns\":1,\"multi_column_order\":\"0\",\"num_links\":4,\"show_subcategory_content\":\"0\",\"link_intro_image\":\"0\",\"show_pagination_limit\":\"1\",\"filter_field\":\"hide\",\"show_headings\":\"1\",\"list_show_date\":\"0\",\"date_format\":\"\",\"list_show_hits\":\"1\",\"list_show_author\":\"1\",\"display_num\":\"10\",\"orderby_pri\":\"order\",\"orderby_sec\":\"rdate\",\"order_date\":\"published\",\"show_pagination\":\"2\",\"show_pagination_results\":\"1\",\"show_featured\":\"show\",\"show_feed_link\":\"1\",\"feed_summary\":\"0\",\"feed_show_readmore\":\"0\",\"sef_ids\":1,\"custom_fields_enable\":\"1\",\"workflow_enabled\":\"0\"}','',NULL,NULL,0,0,NULL),(20,0,'com_config','component','com_config',NULL,'',1,1,0,1,1,'{\"name\":\"com_config\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_CONFIG_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"config\"}','{\"filters\":{\"1\":{\"filter_type\":\"NH\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"9\":{\"filter_type\":\"NH\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"6\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"7\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"2\":{\"filter_type\":\"NH\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"3\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"4\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"5\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"8\":{\"filter_type\":\"NONE\",\"filter_tags\":\"\",\"filter_attributes\":\"\"}}}','',NULL,NULL,0,0,NULL),(21,0,'com_redirect','component','com_redirect',NULL,'',1,1,0,0,1,'{\"name\":\"com_redirect\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_REDIRECT_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(22,0,'com_users','component','com_users',NULL,'',1,1,0,1,1,'{\"name\":\"com_users\",\"type\":\"component\",\"creationDate\":\"2006-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_USERS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"users\"}','{\"allowUserRegistration\":\"0\",\"new_usertype\":\"2\",\"guest_usergroup\":\"9\",\"sendpassword\":\"0\",\"useractivation\":\"2\",\"mail_to_admin\":\"1\",\"captcha\":\"\",\"frontend_userparams\":\"1\",\"site_language\":\"0\",\"change_login_name\":\"0\",\"reset_count\":\"10\",\"reset_time\":\"1\",\"minimum_length\":\"12\",\"minimum_integers\":\"0\",\"minimum_symbols\":\"0\",\"minimum_uppercase\":\"0\",\"save_history\":\"1\",\"history_limit\":5,\"mailSubjectPrefix\":\"\",\"mailBodySuffix\":\"\"}','',NULL,NULL,0,0,NULL),(23,0,'com_finder','component','com_finder',NULL,'',1,1,0,0,1,'{\"name\":\"com_finder\",\"type\":\"component\",\"creationDate\":\"2011-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_FINDER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"finder\"}','{\"enabled\":\"0\",\"show_description\":\"1\",\"description_length\":255,\"allow_empty_query\":\"0\",\"show_url\":\"1\",\"show_autosuggest\":\"1\",\"show_suggested_query\":\"1\",\"show_explained_query\":\"1\",\"show_advanced\":\"1\",\"show_advanced_tips\":\"1\",\"expand_advanced\":\"0\",\"show_date_filters\":\"0\",\"sort_order\":\"relevance\",\"sort_direction\":\"desc\",\"highlight_terms\":\"1\",\"opensearch_name\":\"\",\"opensearch_description\":\"\",\"batch_size\":\"50\",\"title_multiplier\":\"1.7\",\"text_multiplier\":\"0.7\",\"meta_multiplier\":\"1.2\",\"path_multiplier\":\"2.0\",\"misc_multiplier\":\"0.3\",\"stem\":\"1\",\"stemmer\":\"snowball\",\"enable_logging\":\"0\"}','',NULL,NULL,0,0,NULL),(24,0,'com_joomlaupdate','component','com_joomlaupdate',NULL,'',1,1,0,1,1,'{\"name\":\"com_joomlaupdate\",\"type\":\"component\",\"creationDate\":\"2021-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2012 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.3\",\"description\":\"COM_JOOMLAUPDATE_XML_DESCRIPTION\",\"group\":\"\"}','{\"updatesource\":\"default\",\"customurl\":\"\"}','',NULL,NULL,0,0,NULL),(25,0,'com_tags','component','com_tags',NULL,'',1,1,1,0,1,'{\"name\":\"com_tags\",\"type\":\"component\",\"creationDate\":\"2013-12\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_TAGS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"tags\"}','{\"tag_layout\":\"_:default\",\"save_history\":\"1\",\"history_limit\":5,\"show_tag_title\":\"0\",\"tag_list_show_tag_image\":\"0\",\"tag_list_show_tag_description\":\"0\",\"tag_list_image\":\"\",\"tag_list_orderby\":\"title\",\"tag_list_orderby_direction\":\"ASC\",\"show_headings\":\"0\",\"tag_list_show_date\":\"0\",\"tag_list_show_item_image\":\"0\",\"tag_list_show_item_description\":\"0\",\"tag_list_item_maximum_characters\":0,\"return_any_or_all\":\"1\",\"include_children\":\"0\",\"maximum\":200,\"tag_list_language_filter\":\"all\",\"tags_layout\":\"_:default\",\"all_tags_orderby\":\"title\",\"all_tags_orderby_direction\":\"ASC\",\"all_tags_show_tag_image\":\"0\",\"all_tags_show_tag_description\":\"0\",\"all_tags_tag_maximum_characters\":20,\"all_tags_show_tag_hits\":\"0\",\"filter_field\":\"1\",\"show_pagination_limit\":\"1\",\"show_pagination\":\"2\",\"show_pagination_results\":\"1\",\"tag_field_ajax_mode\":\"1\",\"show_feed_link\":\"1\"}','',NULL,NULL,0,0,NULL),(26,0,'com_contenthistory','component','com_contenthistory',NULL,'',1,1,1,0,1,'{\"name\":\"com_contenthistory\",\"type\":\"component\",\"creationDate\":\"2013-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_CONTENTHISTORY_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"contenthistory\"}','','',NULL,NULL,0,0,NULL),(27,0,'com_ajax','component','com_ajax',NULL,'',1,1,1,1,1,'{\"name\":\"com_ajax\",\"type\":\"component\",\"creationDate\":\"2013-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_AJAX_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"ajax\"}','','',NULL,NULL,0,0,NULL),(28,0,'com_postinstall','component','com_postinstall',NULL,'',1,1,1,1,1,'{\"name\":\"com_postinstall\",\"type\":\"component\",\"creationDate\":\"2013-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_POSTINSTALL_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(29,0,'com_fields','component','com_fields',NULL,'',1,1,1,0,1,'{\"name\":\"com_fields\",\"type\":\"component\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_FIELDS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"fields\"}','','',NULL,NULL,0,0,NULL),(30,0,'com_associations','component','com_associations',NULL,'',1,1,1,0,1,'{\"name\":\"com_associations\",\"type\":\"component\",\"creationDate\":\"2017-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_ASSOCIATIONS_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(31,0,'com_privacy','component','com_privacy',NULL,'',1,1,1,0,1,'{\"name\":\"com_privacy\",\"type\":\"component\",\"creationDate\":\"2018-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"COM_PRIVACY_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"privacy\"}','','',NULL,NULL,0,0,NULL),(32,0,'com_actionlogs','component','com_actionlogs',NULL,'',1,1,1,0,1,'{\"name\":\"com_actionlogs\",\"type\":\"component\",\"creationDate\":\"2018-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"COM_ACTIONLOGS_XML_DESCRIPTION\",\"group\":\"\"}','{\"ip_logging\":0,\"csv_delimiter\":\",\",\"loggable_extensions\":[\"com_banners\",\"com_cache\",\"com_categories\",\"com_checkin\",\"com_config\",\"com_contact\",\"com_content\",\"com_installer\",\"com_media\",\"com_menus\",\"com_messages\",\"com_modules\",\"com_newsfeeds\",\"com_plugins\",\"com_redirect\",\"com_scheduler\",\"com_tags\",\"com_templates\",\"com_users\"]}','',NULL,NULL,0,0,NULL),(33,0,'com_workflow','component','com_workflow',NULL,'',1,1,0,1,1,'{\"name\":\"com_workflow\",\"type\":\"component\",\"creationDate\":\"2017-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_WORKFLOW_XML_DESCRIPTION\",\"group\":\"\"}','{}','',NULL,NULL,0,0,NULL),(34,0,'com_mails','component','com_mails',NULL,'',1,1,1,1,1,'{\"name\":\"com_mails\",\"type\":\"component\",\"creationDate\":\"2019-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"COM_MAILS_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(35,0,'com_scheduler','component','com_scheduler',NULL,'',1,1,1,0,1,'{\"name\":\"com_scheduler\",\"type\":\"component\",\"creationDate\":\"2021-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2021 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.1.0\",\"description\":\"COM_SCHEDULER_XML_DESCRIPTION\",\"group\":\"\"}','{}','',NULL,NULL,0,0,NULL),(36,0,'lib_joomla','library','joomla',NULL,'',0,1,1,1,1,'{\"name\":\"lib_joomla\",\"type\":\"library\",\"creationDate\":\"2008-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2008 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"https:\\/\\/www.joomla.org\",\"version\":\"13.1\",\"description\":\"LIB_JOOMLA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"joomla\"}','','',NULL,NULL,0,0,NULL),(37,0,'lib_phpass','library','phpass',NULL,'',0,1,1,1,1,'{\"name\":\"lib_phpass\",\"type\":\"library\",\"creationDate\":\"2004-01\",\"author\":\"Solar Designer\",\"copyright\":\"\",\"authorEmail\":\"solar@openwall.com\",\"authorUrl\":\"https:\\/\\/www.openwall.com\\/phpass\\/\",\"version\":\"0.3\",\"description\":\"LIB_PHPASS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"phpass\"}','','',NULL,NULL,0,0,NULL),(38,0,'mod_articles_archive','module','mod_articles_archive',NULL,'',0,1,1,0,1,'{\"name\":\"mod_articles_archive\",\"type\":\"module\",\"creationDate\":\"2006-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_ARTICLES_ARCHIVE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_articles_archive\"}','','',NULL,NULL,0,0,NULL),(39,0,'mod_articles_latest','module','mod_articles_latest',NULL,'',0,1,1,0,1,'{\"name\":\"mod_articles_latest\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_LATEST_NEWS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_articles_latest\"}','','',NULL,NULL,0,0,NULL),(40,0,'mod_articles_popular','module','mod_articles_popular',NULL,'',0,1,1,0,1,'{\"name\":\"mod_articles_popular\",\"type\":\"module\",\"creationDate\":\"2006-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_POPULAR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_articles_popular\"}','','',NULL,NULL,0,0,NULL),(41,0,'mod_banners','module','mod_banners',NULL,'',0,1,1,0,1,'{\"name\":\"mod_banners\",\"type\":\"module\",\"creationDate\":\"2006-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_BANNERS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_banners\"}','','',NULL,NULL,0,0,NULL),(42,0,'mod_breadcrumbs','module','mod_breadcrumbs',NULL,'',0,1,1,0,1,'{\"name\":\"mod_breadcrumbs\",\"type\":\"module\",\"creationDate\":\"2006-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_BREADCRUMBS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_breadcrumbs\"}','','',NULL,NULL,0,0,NULL),(43,0,'mod_custom','module','mod_custom',NULL,'',0,1,1,0,1,'{\"name\":\"mod_custom\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_CUSTOM_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_custom\"}','','',NULL,NULL,0,0,NULL),(44,0,'mod_feed','module','mod_feed',NULL,'',0,1,1,0,1,'{\"name\":\"mod_feed\",\"type\":\"module\",\"creationDate\":\"2005-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_FEED_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_feed\"}','','',NULL,NULL,0,0,NULL),(45,0,'mod_footer','module','mod_footer',NULL,'',0,1,1,0,1,'{\"name\":\"mod_footer\",\"type\":\"module\",\"creationDate\":\"2006-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_FOOTER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_footer\"}','','',NULL,NULL,0,0,NULL),(46,0,'mod_login','module','mod_login',NULL,'',0,1,1,0,1,'{\"name\":\"mod_login\",\"type\":\"module\",\"creationDate\":\"2006-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_LOGIN_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_login\"}','','',NULL,NULL,0,0,NULL),(47,0,'mod_menu','module','mod_menu',NULL,'',0,1,1,0,1,'{\"name\":\"mod_menu\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_MENU_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_menu\"}','','',NULL,NULL,0,0,NULL),(48,0,'mod_articles_news','module','mod_articles_news',NULL,'',0,1,1,0,1,'{\"name\":\"mod_articles_news\",\"type\":\"module\",\"creationDate\":\"2006-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_ARTICLES_NEWS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_articles_news\"}','','',NULL,NULL,0,0,NULL),(49,0,'mod_random_image','module','mod_random_image',NULL,'',0,1,1,0,1,'{\"name\":\"mod_random_image\",\"type\":\"module\",\"creationDate\":\"2006-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_RANDOM_IMAGE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_random_image\"}','','',NULL,NULL,0,0,NULL),(50,0,'mod_related_items','module','mod_related_items',NULL,'',0,1,1,0,1,'{\"name\":\"mod_related_items\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_RELATED_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_related_items\"}','','',NULL,NULL,0,0,NULL),(51,0,'mod_stats','module','mod_stats',NULL,'',0,1,1,0,1,'{\"name\":\"mod_stats\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_STATS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_stats\"}','','',NULL,NULL,0,0,NULL),(52,0,'mod_syndicate','module','mod_syndicate',NULL,'',0,1,1,0,1,'{\"name\":\"mod_syndicate\",\"type\":\"module\",\"creationDate\":\"2006-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_SYNDICATE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_syndicate\"}','','',NULL,NULL,0,0,NULL),(53,0,'mod_users_latest','module','mod_users_latest',NULL,'',0,1,1,0,1,'{\"name\":\"mod_users_latest\",\"type\":\"module\",\"creationDate\":\"2009-12\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2009 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_USERS_LATEST_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_users_latest\"}','','',NULL,NULL,0,0,NULL),(54,0,'mod_whosonline','module','mod_whosonline',NULL,'',0,1,1,0,1,'{\"name\":\"mod_whosonline\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_WHOSONLINE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_whosonline\"}','','',NULL,NULL,0,0,NULL),(55,0,'mod_wrapper','module','mod_wrapper',NULL,'',0,1,1,0,1,'{\"name\":\"mod_wrapper\",\"type\":\"module\",\"creationDate\":\"2004-10\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_WRAPPER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_wrapper\"}','','',NULL,NULL,0,0,NULL),(56,0,'mod_articles_category','module','mod_articles_category',NULL,'',0,1,1,0,1,'{\"name\":\"mod_articles_category\",\"type\":\"module\",\"creationDate\":\"2010-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2010 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_ARTICLES_CATEGORY_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_articles_category\"}','','',NULL,NULL,0,0,NULL),(57,0,'mod_articles_categories','module','mod_articles_categories',NULL,'',0,1,1,0,1,'{\"name\":\"mod_articles_categories\",\"type\":\"module\",\"creationDate\":\"2010-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2010 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_ARTICLES_CATEGORIES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_articles_categories\"}','','',NULL,NULL,0,0,NULL),(58,0,'mod_languages','module','mod_languages',NULL,'',0,1,1,0,1,'{\"name\":\"mod_languages\",\"type\":\"module\",\"creationDate\":\"2010-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2010 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.5.0\",\"description\":\"MOD_LANGUAGES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_languages\"}','','',NULL,NULL,0,0,NULL),(59,0,'mod_finder','module','mod_finder',NULL,'',0,1,0,0,1,'{\"name\":\"mod_finder\",\"type\":\"module\",\"creationDate\":\"2011-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_FINDER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_finder\"}','','',NULL,NULL,0,0,NULL),(60,0,'mod_custom','module','mod_custom',NULL,'',1,1,1,0,1,'{\"name\":\"mod_custom\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_CUSTOM_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_custom\"}','','',NULL,NULL,0,0,NULL),(61,0,'mod_feed','module','mod_feed',NULL,'',1,1,1,0,1,'{\"name\":\"mod_feed\",\"type\":\"module\",\"creationDate\":\"2005-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_FEED_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_feed\"}','','',NULL,NULL,0,0,NULL),(62,0,'mod_latest','module','mod_latest',NULL,'',1,1,1,0,1,'{\"name\":\"mod_latest\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_LATEST_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_latest\"}','','',NULL,NULL,0,0,NULL),(63,0,'mod_logged','module','mod_logged',NULL,'',1,1,1,0,1,'{\"name\":\"mod_logged\",\"type\":\"module\",\"creationDate\":\"2005-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_LOGGED_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_logged\"}','','',NULL,NULL,0,0,NULL),(64,0,'mod_login','module','mod_login',NULL,'',1,1,1,0,1,'{\"name\":\"mod_login\",\"type\":\"module\",\"creationDate\":\"2005-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_LOGIN_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_login\"}','','',NULL,NULL,0,0,NULL),(65,0,'mod_loginsupport','module','mod_loginsupport',NULL,'',1,1,1,0,1,'{\"name\":\"mod_loginsupport\",\"type\":\"module\",\"creationDate\":\"2019-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"MOD_LOGINSUPPORT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_loginsupport\"}','','',NULL,NULL,0,0,NULL),(66,0,'mod_menu','module','mod_menu',NULL,'',1,1,1,0,1,'{\"name\":\"mod_menu\",\"type\":\"module\",\"creationDate\":\"2006-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_MENU_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_menu\"}','','',NULL,NULL,0,0,NULL),(67,0,'mod_popular','module','mod_popular',NULL,'',1,1,1,0,1,'{\"name\":\"mod_popular\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_POPULAR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_popular\"}','','',NULL,NULL,0,0,NULL),(68,0,'mod_quickicon','module','mod_quickicon',NULL,'',1,1,1,0,1,'{\"name\":\"mod_quickicon\",\"type\":\"module\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_QUICKICON_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_quickicon\"}','','',NULL,NULL,0,0,NULL),(69,0,'mod_frontend','module','mod_frontend',NULL,'',1,1,1,0,1,'{\"name\":\"mod_frontend\",\"type\":\"module\",\"creationDate\":\"2019-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"MOD_FRONTEND_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_frontend\"}','','',NULL,NULL,0,0,NULL),(70,0,'mod_messages','module','mod_messages',NULL,'',1,1,1,0,1,'{\"name\":\"mod_messages\",\"type\":\"module\",\"creationDate\":\"2019-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"MOD_MESSAGES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_messages\"}','','',NULL,NULL,0,0,NULL),(71,0,'mod_post_installation_messages','module','mod_post_installation_messages',NULL,'',1,1,1,0,1,'{\"name\":\"mod_post_installation_messages\",\"type\":\"module\",\"creationDate\":\"2019-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"MOD_POST_INSTALLATION_MESSAGES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_post_installation_messages\"}','','',NULL,NULL,0,0,NULL),(72,0,'mod_user','module','mod_user',NULL,'',1,1,1,0,1,'{\"name\":\"mod_user\",\"type\":\"module\",\"creationDate\":\"2019-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"MOD_USER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_user\"}','','',NULL,NULL,0,0,NULL),(73,0,'mod_title','module','mod_title',NULL,'',1,1,1,0,1,'{\"name\":\"mod_title\",\"type\":\"module\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_TITLE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_title\"}','','',NULL,NULL,0,0,NULL),(74,0,'mod_toolbar','module','mod_toolbar',NULL,'',1,1,1,0,1,'{\"name\":\"mod_toolbar\",\"type\":\"module\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_TOOLBAR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_toolbar\"}','','',NULL,NULL,0,0,NULL),(75,0,'mod_multilangstatus','module','mod_multilangstatus',NULL,'',1,1,1,0,1,'{\"name\":\"mod_multilangstatus\",\"type\":\"module\",\"creationDate\":\"2011-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_MULTILANGSTATUS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_multilangstatus\"}','{\"cache\":\"0\"}','',NULL,NULL,0,0,NULL),(76,0,'mod_version','module','mod_version',NULL,'',1,1,1,0,1,'{\"name\":\"mod_version\",\"type\":\"module\",\"creationDate\":\"2012-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2012 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_VERSION_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_version\"}','{\"cache\":\"0\"}','',NULL,NULL,0,0,NULL),(77,0,'mod_stats_admin','module','mod_stats_admin',NULL,'',1,1,1,0,1,'{\"name\":\"mod_stats_admin\",\"type\":\"module\",\"creationDate\":\"2004-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_STATS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_stats_admin\"}','{\"serverinfo\":\"0\",\"siteinfo\":\"0\",\"counter\":\"0\",\"increase\":\"0\",\"cache\":\"1\",\"cache_time\":\"900\",\"cachemode\":\"static\"}','',NULL,NULL,0,0,NULL),(78,0,'mod_tags_popular','module','mod_tags_popular',NULL,'',0,1,1,0,1,'{\"name\":\"mod_tags_popular\",\"type\":\"module\",\"creationDate\":\"2013-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.1.0\",\"description\":\"MOD_TAGS_POPULAR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_tags_popular\"}','{\"maximum\":\"5\",\"timeframe\":\"alltime\",\"owncache\":\"1\"}','',NULL,NULL,0,0,NULL),(79,0,'mod_tags_similar','module','mod_tags_similar',NULL,'',0,1,1,0,1,'{\"name\":\"mod_tags_similar\",\"type\":\"module\",\"creationDate\":\"2013-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.1.0\",\"description\":\"MOD_TAGS_SIMILAR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_tags_similar\"}','{\"maximum\":\"5\",\"matchtype\":\"any\",\"owncache\":\"1\"}','',NULL,NULL,0,0,NULL),(80,0,'mod_sampledata','module','mod_sampledata',NULL,'',1,1,1,0,1,'{\"name\":\"mod_sampledata\",\"type\":\"module\",\"creationDate\":\"2017-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.8.0\",\"description\":\"MOD_SAMPLEDATA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_sampledata\"}','{}','',NULL,NULL,0,0,NULL),(81,0,'mod_latestactions','module','mod_latestactions',NULL,'',1,1,1,0,1,'{\"name\":\"mod_latestactions\",\"type\":\"module\",\"creationDate\":\"2018-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"MOD_LATESTACTIONS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_latestactions\"}','{}','',NULL,NULL,0,0,NULL),(82,0,'mod_privacy_dashboard','module','mod_privacy_dashboard',NULL,'',1,1,1,0,1,'{\"name\":\"mod_privacy_dashboard\",\"type\":\"module\",\"creationDate\":\"2018-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"MOD_PRIVACY_DASHBOARD_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_privacy_dashboard\"}','{}','',NULL,NULL,0,0,NULL),(83,0,'mod_submenu','module','mod_submenu',NULL,'',1,1,1,0,1,'{\"name\":\"mod_submenu\",\"type\":\"module\",\"creationDate\":\"2006-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"MOD_SUBMENU_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_submenu\"}','{}','',NULL,NULL,0,0,NULL),(84,0,'mod_privacy_status','module','mod_privacy_status',NULL,'',1,1,1,0,1,'{\"name\":\"mod_privacy_status\",\"type\":\"module\",\"creationDate\":\"2019-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"MOD_PRIVACY_STATUS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"mod_privacy_status\"}','{}','',NULL,NULL,0,0,NULL),(85,0,'plg_actionlog_joomla','plugin','joomla',NULL,'actionlog',0,1,1,0,1,'{\"name\":\"plg_actionlog_joomla\",\"type\":\"plugin\",\"creationDate\":\"2018-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_ACTIONLOG_JOOMLA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"joomla\"}','{}','',NULL,NULL,1,0,NULL),(86,0,'plg_api-authentication_basic','plugin','basic',NULL,'api-authentication',0,0,1,0,1,'{\"name\":\"plg_api-authentication_basic\",\"type\":\"plugin\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_API-AUTHENTICATION_BASIC_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"basic\"}','{}','',NULL,NULL,1,0,NULL),(87,0,'plg_api-authentication_token','plugin','token',NULL,'api-authentication',0,1,1,0,1,'{\"name\":\"plg_api-authentication_token\",\"type\":\"plugin\",\"creationDate\":\"2019-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_API-AUTHENTICATION_TOKEN_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"token\"}','{}','',NULL,NULL,2,0,NULL),(88,0,'plg_authentication_cookie','plugin','cookie',NULL,'authentication',0,1,1,0,1,'{\"name\":\"plg_authentication_cookie\",\"type\":\"plugin\",\"creationDate\":\"2013-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_AUTHENTICATION_COOKIE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"cookie\"}','','',NULL,NULL,1,0,NULL),(89,0,'plg_authentication_joomla','plugin','joomla',NULL,'authentication',0,1,1,1,1,'{\"name\":\"plg_authentication_joomla\",\"type\":\"plugin\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_AUTHENTICATION_JOOMLA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"joomla\"}','','',NULL,NULL,2,0,NULL),(90,0,'plg_authentication_ldap','plugin','ldap',NULL,'authentication',0,0,1,0,1,'{\"name\":\"plg_authentication_ldap\",\"type\":\"plugin\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_LDAP_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"ldap\"}','{\"host\":\"\",\"port\":\"389\",\"use_ldapV3\":\"0\",\"negotiate_tls\":\"0\",\"no_referrals\":\"0\",\"auth_method\":\"bind\",\"base_dn\":\"\",\"search_string\":\"\",\"users_dn\":\"\",\"username\":\"admin\",\"password\":\"bobby7\",\"ldap_fullname\":\"fullName\",\"ldap_email\":\"mail\",\"ldap_uid\":\"uid\"}','',NULL,NULL,3,0,NULL),(91,0,'plg_behaviour_taggable','plugin','taggable',NULL,'behaviour',0,1,1,0,1,'{\"name\":\"plg_behaviour_taggable\",\"type\":\"plugin\",\"creationDate\":\"2015-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_BEHAVIOUR_TAGGABLE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"taggable\"}','{}','',NULL,NULL,1,0,NULL),(92,0,'plg_behaviour_versionable','plugin','versionable',NULL,'behaviour',0,1,1,0,1,'{\"name\":\"plg_behaviour_versionable\",\"type\":\"plugin\",\"creationDate\":\"2015-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_BEHAVIOUR_VERSIONABLE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"versionable\"}','{}','',NULL,NULL,2,0,NULL),(93,0,'plg_captcha_recaptcha','plugin','recaptcha',NULL,'captcha',0,0,1,0,1,'{\"name\":\"plg_captcha_recaptcha\",\"type\":\"plugin\",\"creationDate\":\"2011-12\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.4.0\",\"description\":\"PLG_CAPTCHA_RECAPTCHA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"recaptcha\"}','{\"public_key\":\"\",\"private_key\":\"\",\"theme\":\"clean\"}','',NULL,NULL,1,0,NULL),(94,0,'plg_captcha_recaptcha_invisible','plugin','recaptcha_invisible',NULL,'captcha',0,0,1,0,1,'{\"name\":\"plg_captcha_recaptcha_invisible\",\"type\":\"plugin\",\"creationDate\":\"2017-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.8\",\"description\":\"PLG_CAPTCHA_RECAPTCHA_INVISIBLE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"recaptcha_invisible\"}','{\"public_key\":\"\",\"private_key\":\"\",\"theme\":\"clean\"}','',NULL,NULL,2,0,NULL),(95,0,'plg_content_confirmconsent','plugin','confirmconsent',NULL,'content',0,0,1,0,1,'{\"name\":\"plg_content_confirmconsent\",\"type\":\"plugin\",\"creationDate\":\"2018-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_CONTENT_CONFIRMCONSENT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"confirmconsent\"}','{}','',NULL,NULL,1,0,NULL),(96,0,'plg_content_contact','plugin','contact',NULL,'content',0,1,1,0,1,'{\"name\":\"plg_content_contact\",\"type\":\"plugin\",\"creationDate\":\"2014-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2014 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.2.2\",\"description\":\"PLG_CONTENT_CONTACT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"contact\"}','','',NULL,NULL,2,0,NULL),(97,0,'plg_content_emailcloak','plugin','emailcloak',NULL,'content',0,1,1,0,1,'{\"name\":\"plg_content_emailcloak\",\"type\":\"plugin\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_CONTENT_EMAILCLOAK_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"emailcloak\"}','{\"mode\":\"1\"}','',NULL,NULL,3,0,NULL),(98,0,'plg_content_fields','plugin','fields',NULL,'content',0,1,1,0,1,'{\"name\":\"plg_content_fields\",\"type\":\"plugin\",\"creationDate\":\"2017-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_CONTENT_FIELDS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"fields\"}','','',NULL,NULL,4,0,NULL),(99,0,'plg_content_finder','plugin','finder',NULL,'content',0,1,1,0,1,'{\"name\":\"plg_content_finder\",\"type\":\"plugin\",\"creationDate\":\"2011-12\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_CONTENT_FINDER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"finder\"}','','',NULL,NULL,5,0,NULL),(100,0,'plg_content_joomla','plugin','joomla',NULL,'content',0,1,1,0,1,'{\"name\":\"plg_content_joomla\",\"type\":\"plugin\",\"creationDate\":\"2010-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2010 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_CONTENT_JOOMLA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"joomla\"}','','',NULL,NULL,6,0,NULL),(101,0,'plg_content_loadmodule','plugin','loadmodule',NULL,'content',0,1,1,0,1,'{\"name\":\"plg_content_loadmodule\",\"type\":\"plugin\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_LOADMODULE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"loadmodule\"}','{\"style\":\"xhtml\"}','',NULL,NULL,7,0,NULL),(102,0,'plg_content_pagebreak','plugin','pagebreak',NULL,'content',0,1,1,0,1,'{\"name\":\"plg_content_pagebreak\",\"type\":\"plugin\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_CONTENT_PAGEBREAK_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"pagebreak\"}','{\"title\":\"1\",\"multipage_toc\":\"1\",\"showall\":\"1\"}','',NULL,NULL,8,0,NULL),(103,0,'plg_content_pagenavigation','plugin','pagenavigation',NULL,'content',0,1,1,0,1,'{\"name\":\"plg_content_pagenavigation\",\"type\":\"plugin\",\"creationDate\":\"2006-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_PAGENAVIGATION_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"pagenavigation\"}','{\"position\":\"1\"}','',NULL,NULL,9,0,NULL),(104,0,'plg_content_vote','plugin','vote',NULL,'content',0,0,1,0,1,'{\"name\":\"plg_content_vote\",\"type\":\"plugin\",\"creationDate\":\"2005-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_VOTE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"vote\"}','','',NULL,NULL,10,0,NULL),(105,0,'plg_editors-xtd_article','plugin','article',NULL,'editors-xtd',0,1,1,0,1,'{\"name\":\"plg_editors-xtd_article\",\"type\":\"plugin\",\"creationDate\":\"2009-10\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2009 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_ARTICLE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"article\"}','','',NULL,NULL,1,0,NULL),(106,0,'plg_editors-xtd_contact','plugin','contact',NULL,'editors-xtd',0,1,1,0,1,'{\"name\":\"plg_editors-xtd_contact\",\"type\":\"plugin\",\"creationDate\":\"2016-10\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_EDITORS-XTD_CONTACT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"contact\"}','','',NULL,NULL,2,0,NULL),(107,0,'plg_editors-xtd_fields','plugin','fields',NULL,'editors-xtd',0,1,1,0,1,'{\"name\":\"plg_editors-xtd_fields\",\"type\":\"plugin\",\"creationDate\":\"2017-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_EDITORS-XTD_FIELDS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"fields\"}','','',NULL,NULL,3,0,NULL),(108,0,'plg_editors-xtd_image','plugin','image',NULL,'editors-xtd',0,1,1,0,1,'{\"name\":\"plg_editors-xtd_image\",\"type\":\"plugin\",\"creationDate\":\"2004-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_IMAGE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"image\"}','','',NULL,NULL,4,0,NULL),(109,0,'plg_editors-xtd_menu','plugin','menu',NULL,'editors-xtd',0,1,1,0,1,'{\"name\":\"plg_editors-xtd_menu\",\"type\":\"plugin\",\"creationDate\":\"2016-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_EDITORS-XTD_MENU_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"menu\"}','','',NULL,NULL,5,0,NULL),(110,0,'plg_editors-xtd_module','plugin','module',NULL,'editors-xtd',0,1,1,0,1,'{\"name\":\"plg_editors-xtd_module\",\"type\":\"plugin\",\"creationDate\":\"2015-10\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2015 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.5.0\",\"description\":\"PLG_MODULE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"module\"}','','',NULL,NULL,6,0,NULL),(111,0,'plg_editors-xtd_pagebreak','plugin','pagebreak',NULL,'editors-xtd',0,1,1,0,1,'{\"name\":\"plg_editors-xtd_pagebreak\",\"type\":\"plugin\",\"creationDate\":\"2004-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_EDITORSXTD_PAGEBREAK_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"pagebreak\"}','','',NULL,NULL,7,0,NULL),(112,0,'plg_editors-xtd_readmore','plugin','readmore',NULL,'editors-xtd',0,1,1,0,1,'{\"name\":\"plg_editors-xtd_readmore\",\"type\":\"plugin\",\"creationDate\":\"2006-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_READMORE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"readmore\"}','','',NULL,NULL,8,0,NULL),(113,0,'plg_editors_codemirror','plugin','codemirror',NULL,'editors',0,1,1,0,1,'{\"name\":\"plg_editors_codemirror\",\"type\":\"plugin\",\"creationDate\":\"28 March 2011\",\"author\":\"Marijn Haverbeke\",\"copyright\":\"Copyright (C) 2014 - 2021 by Marijn Haverbeke <marijnh@gmail.com> and others\",\"authorEmail\":\"marijnh@gmail.com\",\"authorUrl\":\"https:\\/\\/codemirror.net\\/\",\"version\":\"5.65.6\",\"description\":\"PLG_CODEMIRROR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"codemirror\"}','{\"lineNumbers\":\"1\",\"lineWrapping\":\"1\",\"matchTags\":\"1\",\"matchBrackets\":\"1\",\"marker-gutter\":\"1\",\"autoCloseTags\":\"1\",\"autoCloseBrackets\":\"1\",\"autoFocus\":\"1\",\"theme\":\"default\",\"tabmode\":\"indent\"}','',NULL,NULL,1,0,NULL),(114,0,'plg_editors_none','plugin','none',NULL,'editors',0,1,1,1,1,'{\"name\":\"plg_editors_none\",\"type\":\"plugin\",\"creationDate\":\"2005-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_NONE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"none\"}','','',NULL,NULL,2,0,NULL),(115,0,'plg_editors_tinymce','plugin','tinymce',NULL,'editors',0,1,1,0,1,'{\"name\":\"plg_editors_tinymce\",\"type\":\"plugin\",\"creationDate\":\"2005-08\",\"author\":\"Tiny Technologies, Inc\",\"copyright\":\"Tiny Technologies, Inc\",\"authorEmail\":\"N\\/A\",\"authorUrl\":\"https:\\/\\/www.tiny.cloud\",\"version\":\"5.10.7\",\"description\":\"PLG_TINY_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"tinymce\"}','{\"configuration\":{\"toolbars\":{\"2\":{\"toolbar1\":[\"bold\",\"underline\",\"strikethrough\",\"|\",\"undo\",\"redo\",\"|\",\"bullist\",\"numlist\",\"|\",\"pastetext\"]},\"1\":{\"menu\":[\"edit\",\"insert\",\"view\",\"format\",\"table\",\"tools\"],\"toolbar1\":[\"bold\",\"italic\",\"underline\",\"strikethrough\",\"|\",\"alignleft\",\"aligncenter\",\"alignright\",\"alignjustify\",\"|\",\"formatselect\",\"|\",\"bullist\",\"numlist\",\"|\",\"outdent\",\"indent\",\"|\",\"undo\",\"redo\",\"|\",\"link\",\"unlink\",\"anchor\",\"code\",\"|\",\"hr\",\"table\",\"|\",\"subscript\",\"superscript\",\"|\",\"charmap\",\"pastetext\",\"preview\"]},\"0\":{\"menu\":[\"edit\",\"insert\",\"view\",\"format\",\"table\",\"tools\"],\"toolbar1\":[\"bold\",\"italic\",\"underline\",\"strikethrough\",\"|\",\"alignleft\",\"aligncenter\",\"alignright\",\"alignjustify\",\"|\",\"styleselect\",\"|\",\"formatselect\",\"fontselect\",\"fontsizeselect\",\"|\",\"searchreplace\",\"|\",\"bullist\",\"numlist\",\"|\",\"outdent\",\"indent\",\"|\",\"undo\",\"redo\",\"|\",\"link\",\"unlink\",\"anchor\",\"image\",\"|\",\"code\",\"|\",\"forecolor\",\"backcolor\",\"|\",\"fullscreen\",\"|\",\"table\",\"|\",\"subscript\",\"superscript\",\"|\",\"charmap\",\"emoticons\",\"media\",\"hr\",\"ltr\",\"rtl\",\"|\",\"cut\",\"copy\",\"paste\",\"pastetext\",\"|\",\"visualchars\",\"visualblocks\",\"nonbreaking\",\"blockquote\",\"template\",\"|\",\"print\",\"preview\",\"codesample\",\"insertdatetime\",\"removeformat\"]}},\"setoptions\":{\"2\":{\"access\":[\"1\"],\"skin\":\"0\",\"skin_admin\":\"0\",\"mobile\":\"0\",\"drag_drop\":\"1\",\"path\":\"\",\"entity_encoding\":\"raw\",\"lang_mode\":\"1\",\"text_direction\":\"ltr\",\"content_css\":\"1\",\"content_css_custom\":\"\",\"relative_urls\":\"1\",\"newlines\":\"0\",\"use_config_textfilters\":\"0\",\"invalid_elements\":\"script,applet,iframe\",\"valid_elements\":\"\",\"extended_elements\":\"\",\"resizing\":\"1\",\"resize_horizontal\":\"1\",\"element_path\":\"1\",\"wordcount\":\"1\",\"image_advtab\":\"0\",\"advlist\":\"1\",\"autosave\":\"1\",\"contextmenu\":\"1\",\"custom_plugin\":\"\",\"custom_button\":\"\"},\"1\":{\"access\":[\"6\",\"2\"],\"skin\":\"0\",\"skin_admin\":\"0\",\"mobile\":\"0\",\"drag_drop\":\"1\",\"path\":\"\",\"entity_encoding\":\"raw\",\"lang_mode\":\"1\",\"text_direction\":\"ltr\",\"content_css\":\"1\",\"content_css_custom\":\"\",\"relative_urls\":\"1\",\"newlines\":\"0\",\"use_config_textfilters\":\"0\",\"invalid_elements\":\"script,applet,iframe\",\"valid_elements\":\"\",\"extended_elements\":\"\",\"resizing\":\"1\",\"resize_horizontal\":\"1\",\"element_path\":\"1\",\"wordcount\":\"1\",\"image_advtab\":\"0\",\"advlist\":\"1\",\"autosave\":\"1\",\"contextmenu\":\"1\",\"custom_plugin\":\"\",\"custom_button\":\"\"},\"0\":{\"access\":[\"7\",\"4\",\"8\"],\"skin\":\"0\",\"skin_admin\":\"0\",\"mobile\":\"0\",\"drag_drop\":\"1\",\"path\":\"\",\"entity_encoding\":\"raw\",\"lang_mode\":\"1\",\"text_direction\":\"ltr\",\"content_css\":\"1\",\"content_css_custom\":\"\",\"relative_urls\":\"1\",\"newlines\":\"0\",\"use_config_textfilters\":\"0\",\"invalid_elements\":\"script,applet,iframe\",\"valid_elements\":\"\",\"extended_elements\":\"\",\"resizing\":\"1\",\"resize_horizontal\":\"1\",\"element_path\":\"1\",\"wordcount\":\"1\",\"image_advtab\":\"1\",\"advlist\":\"1\",\"autosave\":\"1\",\"contextmenu\":\"1\",\"custom_plugin\":\"\",\"custom_button\":\"\"}}},\"sets_amount\":3,\"html_height\":\"550\",\"html_width\":\"750\"}','',NULL,NULL,3,0,NULL),(116,0,'plg_extension_finder','plugin','finder',NULL,'extension',0,1,1,0,1,'{\"name\":\"plg_extension_finder\",\"type\":\"plugin\",\"creationDate\":\"2018-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_EXTENSION_FINDER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"finder\"}','','',NULL,NULL,1,0,NULL),(117,0,'plg_extension_joomla','plugin','joomla',NULL,'extension',0,1,1,0,1,'{\"name\":\"plg_extension_joomla\",\"type\":\"plugin\",\"creationDate\":\"2010-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2010 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_EXTENSION_JOOMLA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"joomla\"}','','',NULL,NULL,2,0,NULL),(118,0,'plg_extension_namespacemap','plugin','namespacemap',NULL,'extension',0,1,1,1,1,'{\"name\":\"plg_extension_namespacemap\",\"type\":\"plugin\",\"creationDate\":\"2017-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_EXTENSION_NAMESPACEMAP_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"namespacemap\"}','{}','',NULL,NULL,3,0,NULL),(119,0,'plg_fields_calendar','plugin','calendar',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_calendar\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_CALENDAR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"calendar\"}','','',NULL,NULL,1,0,NULL),(120,0,'plg_fields_checkboxes','plugin','checkboxes',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_checkboxes\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_CHECKBOXES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"checkboxes\"}','','',NULL,NULL,2,0,NULL),(121,0,'plg_fields_color','plugin','color',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_color\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_COLOR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"color\"}','','',NULL,NULL,3,0,NULL),(122,0,'plg_fields_editor','plugin','editor',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_editor\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_EDITOR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"editor\"}','{\"buttons\":0,\"width\":\"100%\",\"height\":\"250px\",\"filter\":\"JComponentHelper::filterText\"}','',NULL,NULL,4,0,NULL),(123,0,'plg_fields_imagelist','plugin','imagelist',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_imagelist\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_IMAGELIST_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"imagelist\"}','','',NULL,NULL,5,0,NULL),(124,0,'plg_fields_integer','plugin','integer',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_integer\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_INTEGER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"integer\"}','{\"multiple\":\"0\",\"first\":\"1\",\"last\":\"100\",\"step\":\"1\"}','',NULL,NULL,6,0,NULL),(125,0,'plg_fields_list','plugin','list',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_list\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_LIST_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"list\"}','','',NULL,NULL,7,0,NULL),(126,0,'plg_fields_media','plugin','media',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_media\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_MEDIA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"media\"}','','',NULL,NULL,8,0,NULL),(127,0,'plg_fields_radio','plugin','radio',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_radio\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_RADIO_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"radio\"}','','',NULL,NULL,9,0,NULL),(128,0,'plg_fields_sql','plugin','sql',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_sql\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_SQL_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"sql\"}','','',NULL,NULL,10,0,NULL),(129,0,'plg_fields_subform','plugin','subform',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_subform\",\"type\":\"plugin\",\"creationDate\":\"2017-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_FIELDS_SUBFORM_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"subform\"}','','',NULL,NULL,11,0,NULL),(130,0,'plg_fields_text','plugin','text',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_text\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_TEXT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"text\"}','','',NULL,NULL,12,0,NULL),(131,0,'plg_fields_textarea','plugin','textarea',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_textarea\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_TEXTAREA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"textarea\"}','{\"rows\":10,\"cols\":10,\"maxlength\":\"\",\"filter\":\"JComponentHelper::filterText\"}','',NULL,NULL,13,0,NULL),(132,0,'plg_fields_url','plugin','url',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_url\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_URL_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"url\"}','','',NULL,NULL,14,0,NULL),(133,0,'plg_fields_user','plugin','user',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_user\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_USER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"user\"}','','',NULL,NULL,15,0,NULL),(134,0,'plg_fields_usergrouplist','plugin','usergrouplist',NULL,'fields',0,1,1,0,1,'{\"name\":\"plg_fields_usergrouplist\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_FIELDS_USERGROUPLIST_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"usergrouplist\"}','','',NULL,NULL,16,0,NULL),(135,0,'plg_filesystem_local','plugin','local',NULL,'filesystem',0,1,1,0,1,'{\"name\":\"plg_filesystem_local\",\"type\":\"plugin\",\"creationDate\":\"2017-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_FILESYSTEM_LOCAL_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"local\"}','{}','',NULL,NULL,1,0,NULL),(136,0,'plg_finder_categories','plugin','categories',NULL,'finder',0,1,1,0,1,'{\"name\":\"plg_finder_categories\",\"type\":\"plugin\",\"creationDate\":\"2011-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_FINDER_CATEGORIES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"categories\"}','','',NULL,NULL,1,0,NULL),(137,0,'plg_finder_contacts','plugin','contacts',NULL,'finder',0,1,1,0,1,'{\"name\":\"plg_finder_contacts\",\"type\":\"plugin\",\"creationDate\":\"2011-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_FINDER_CONTACTS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"contacts\"}','','',NULL,NULL,2,0,NULL),(138,0,'plg_finder_content','plugin','content',NULL,'finder',0,1,1,0,1,'{\"name\":\"plg_finder_content\",\"type\":\"plugin\",\"creationDate\":\"2011-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_FINDER_CONTENT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"content\"}','','',NULL,NULL,3,0,NULL),(139,0,'plg_finder_newsfeeds','plugin','newsfeeds',NULL,'finder',0,1,1,0,1,'{\"name\":\"plg_finder_newsfeeds\",\"type\":\"plugin\",\"creationDate\":\"2011-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_FINDER_NEWSFEEDS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"newsfeeds\"}','','',NULL,NULL,4,0,NULL),(140,0,'plg_finder_tags','plugin','tags',NULL,'finder',0,1,1,0,1,'{\"name\":\"plg_finder_tags\",\"type\":\"plugin\",\"creationDate\":\"2013-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_FINDER_TAGS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"tags\"}','','',NULL,NULL,5,0,NULL),(141,0,'plg_installer_folderinstaller','plugin','folderinstaller',NULL,'installer',0,1,1,0,1,'{\"name\":\"plg_installer_folderinstaller\",\"type\":\"plugin\",\"creationDate\":\"2016-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.6.0\",\"description\":\"PLG_INSTALLER_FOLDERINSTALLER_PLUGIN_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"folderinstaller\"}','','',NULL,NULL,2,0,NULL),(142,0,'plg_installer_override','plugin','override',NULL,'installer',0,1,1,0,1,'{\"name\":\"plg_installer_override\",\"type\":\"plugin\",\"creationDate\":\"2018-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_INSTALLER_OVERRIDE_PLUGIN_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"override\"}','','',NULL,NULL,4,0,NULL),(143,0,'plg_installer_packageinstaller','plugin','packageinstaller',NULL,'installer',0,1,1,0,1,'{\"name\":\"plg_installer_packageinstaller\",\"type\":\"plugin\",\"creationDate\":\"2016-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.6.0\",\"description\":\"PLG_INSTALLER_PACKAGEINSTALLER_PLUGIN_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"packageinstaller\"}','','',NULL,NULL,1,0,NULL),(144,0,'plg_installer_urlinstaller','plugin','urlinstaller',NULL,'installer',0,1,1,0,1,'{\"name\":\"plg_installer_urlinstaller\",\"type\":\"plugin\",\"creationDate\":\"2016-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.6.0\",\"description\":\"PLG_INSTALLER_URLINSTALLER_PLUGIN_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"urlinstaller\"}','','',NULL,NULL,3,0,NULL),(145,0,'plg_installer_webinstaller','plugin','webinstaller',NULL,'installer',0,1,1,0,1,'{\"name\":\"plg_installer_webinstaller\",\"type\":\"plugin\",\"creationDate\":\"2017-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_INSTALLER_WEBINSTALLER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"webinstaller\"}','{\"tab_position\":\"1\"}','',NULL,NULL,5,0,NULL),(146,0,'plg_media-action_crop','plugin','crop',NULL,'media-action',0,1,1,0,1,'{\"name\":\"plg_media-action_crop\",\"type\":\"plugin\",\"creationDate\":\"2017-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_MEDIA-ACTION_CROP_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"crop\"}','{}','',NULL,NULL,1,0,NULL),(147,0,'plg_media-action_resize','plugin','resize',NULL,'media-action',0,1,1,0,1,'{\"name\":\"plg_media-action_resize\",\"type\":\"plugin\",\"creationDate\":\"2017-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_MEDIA-ACTION_RESIZE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"resize\"}','{}','',NULL,NULL,2,0,NULL),(148,0,'plg_media-action_rotate','plugin','rotate',NULL,'media-action',0,1,1,0,1,'{\"name\":\"plg_media-action_rotate\",\"type\":\"plugin\",\"creationDate\":\"2017-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_MEDIA-ACTION_ROTATE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"rotate\"}','{}','',NULL,NULL,3,0,NULL),(149,0,'plg_privacy_actionlogs','plugin','actionlogs',NULL,'privacy',0,1,1,0,1,'{\"name\":\"plg_privacy_actionlogs\",\"type\":\"plugin\",\"creationDate\":\"2018-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_PRIVACY_ACTIONLOGS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"actionlogs\"}','{}','',NULL,NULL,1,0,NULL),(150,0,'plg_privacy_consents','plugin','consents',NULL,'privacy',0,1,1,0,1,'{\"name\":\"plg_privacy_consents\",\"type\":\"plugin\",\"creationDate\":\"2018-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_PRIVACY_CONSENTS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"consents\"}','{}','',NULL,NULL,2,0,NULL),(151,0,'plg_privacy_contact','plugin','contact',NULL,'privacy',0,1,1,0,1,'{\"name\":\"plg_privacy_contact\",\"type\":\"plugin\",\"creationDate\":\"2018-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_PRIVACY_CONTACT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"contact\"}','{}','',NULL,NULL,3,0,NULL),(152,0,'plg_privacy_content','plugin','content',NULL,'privacy',0,1,1,0,1,'{\"name\":\"plg_privacy_content\",\"type\":\"plugin\",\"creationDate\":\"2018-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_PRIVACY_CONTENT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"content\"}','{}','',NULL,NULL,4,0,NULL),(153,0,'plg_privacy_message','plugin','message',NULL,'privacy',0,1,1,0,1,'{\"name\":\"plg_privacy_message\",\"type\":\"plugin\",\"creationDate\":\"2018-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_PRIVACY_MESSAGE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"message\"}','{}','',NULL,NULL,5,0,NULL),(154,0,'plg_privacy_user','plugin','user',NULL,'privacy',0,1,1,0,1,'{\"name\":\"plg_privacy_user\",\"type\":\"plugin\",\"creationDate\":\"2018-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_PRIVACY_USER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"user\"}','{}','',NULL,NULL,6,0,NULL),(155,0,'plg_quickicon_joomlaupdate','plugin','joomlaupdate',NULL,'quickicon',0,1,1,0,1,'{\"name\":\"plg_quickicon_joomlaupdate\",\"type\":\"plugin\",\"creationDate\":\"2011-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_QUICKICON_JOOMLAUPDATE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"joomlaupdate\"}','','',NULL,NULL,1,0,NULL),(156,0,'plg_quickicon_extensionupdate','plugin','extensionupdate',NULL,'quickicon',0,1,1,0,1,'{\"name\":\"plg_quickicon_extensionupdate\",\"type\":\"plugin\",\"creationDate\":\"2011-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_QUICKICON_EXTENSIONUPDATE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"extensionupdate\"}','','',NULL,NULL,2,0,NULL),(157,0,'plg_quickicon_overridecheck','plugin','overridecheck',NULL,'quickicon',0,1,1,0,1,'{\"name\":\"plg_quickicon_overridecheck\",\"type\":\"plugin\",\"creationDate\":\"2018-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_QUICKICON_OVERRIDECHECK_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"overridecheck\"}','','',NULL,NULL,3,0,NULL),(158,0,'plg_quickicon_downloadkey','plugin','downloadkey',NULL,'quickicon',0,1,1,0,1,'{\"name\":\"plg_quickicon_downloadkey\",\"type\":\"plugin\",\"creationDate\":\"2019-10\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_QUICKICON_DOWNLOADKEY_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"downloadkey\"}','','',NULL,NULL,4,0,NULL),(159,0,'plg_quickicon_privacycheck','plugin','privacycheck',NULL,'quickicon',0,1,1,0,1,'{\"name\":\"plg_quickicon_privacycheck\",\"type\":\"plugin\",\"creationDate\":\"2018-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_QUICKICON_PRIVACYCHECK_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"privacycheck\"}','{}','',NULL,NULL,5,0,NULL),(160,0,'plg_quickicon_phpversioncheck','plugin','phpversioncheck',NULL,'quickicon',0,1,1,0,1,'{\"name\":\"plg_quickicon_phpversioncheck\",\"type\":\"plugin\",\"creationDate\":\"2016-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_QUICKICON_PHPVERSIONCHECK_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"phpversioncheck\"}','','',NULL,NULL,6,0,NULL),(161,0,'plg_sampledata_blog','plugin','blog',NULL,'sampledata',0,1,1,0,1,'{\"name\":\"plg_sampledata_blog\",\"type\":\"plugin\",\"creationDate\":\"2017-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.8.0\",\"description\":\"PLG_SAMPLEDATA_BLOG_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"blog\"}','','',NULL,NULL,1,0,NULL),(162,0,'plg_sampledata_multilang','plugin','multilang',NULL,'sampledata',0,1,1,0,1,'{\"name\":\"plg_sampledata_multilang\",\"type\":\"plugin\",\"creationDate\":\"2018-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_SAMPLEDATA_MULTILANG_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"multilang\"}','','',NULL,NULL,2,0,NULL),(163,0,'plg_system_accessibility','plugin','accessibility',NULL,'system',0,0,1,0,1,'{\"name\":\"plg_system_accessibility\",\"type\":\"plugin\",\"creationDate\":\"2020-02-15\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_SYSTEM_ACCESSIBILITY_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"accessibility\"}','{}','',NULL,NULL,1,0,NULL),(164,0,'plg_system_actionlogs','plugin','actionlogs',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_actionlogs\",\"type\":\"plugin\",\"creationDate\":\"2018-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_SYSTEM_ACTIONLOGS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"actionlogs\"}','{}','',NULL,NULL,2,0,NULL),(165,0,'plg_system_cache','plugin','cache',NULL,'system',0,0,1,0,1,'{\"name\":\"plg_system_cache\",\"type\":\"plugin\",\"creationDate\":\"2007-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2007 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_CACHE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"cache\"}','{\"browsercache\":\"0\",\"cachetime\":\"15\"}','',NULL,NULL,3,0,NULL),(166,0,'plg_system_debug','plugin','debug',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_debug\",\"type\":\"plugin\",\"creationDate\":\"2006-12\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_DEBUG_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"debug\"}','{\"profile\":\"1\",\"queries\":\"1\",\"memory\":\"1\",\"language_files\":\"1\",\"language_strings\":\"1\",\"strip-first\":\"1\",\"strip-prefix\":\"\",\"strip-suffix\":\"\"}','',NULL,NULL,4,0,NULL),(167,0,'plg_system_fields','plugin','fields',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_fields\",\"type\":\"plugin\",\"creationDate\":\"2016-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.7.0\",\"description\":\"PLG_SYSTEM_FIELDS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"fields\"}','','',NULL,NULL,5,0,NULL),(168,0,'plg_system_highlight','plugin','highlight',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_highlight\",\"type\":\"plugin\",\"creationDate\":\"2011-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_SYSTEM_HIGHLIGHT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"highlight\"}','','',NULL,NULL,6,0,NULL),(169,0,'plg_system_httpheaders','plugin','httpheaders',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_httpheaders\",\"type\":\"plugin\",\"creationDate\":\"2017-10\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_SYSTEM_HTTPHEADERS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"httpheaders\"}','{}','',NULL,NULL,7,0,NULL),(170,0,'plg_system_jooa11y','plugin','jooa11y',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_jooa11y\",\"type\":\"plugin\",\"creationDate\":\"2022-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2021 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.0\",\"description\":\"PLG_SYSTEM_JOOA11Y_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"jooa11y\"}','','',NULL,NULL,8,0,NULL),(171,0,'plg_system_languagecode','plugin','languagecode',NULL,'system',0,0,1,0,1,'{\"name\":\"plg_system_languagecode\",\"type\":\"plugin\",\"creationDate\":\"2011-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2011 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_SYSTEM_LANGUAGECODE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"languagecode\"}','','',NULL,NULL,9,0,NULL),(172,0,'plg_system_languagefilter','plugin','languagefilter',NULL,'system',0,0,1,0,1,'{\"name\":\"plg_system_languagefilter\",\"type\":\"plugin\",\"creationDate\":\"2010-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2010 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_SYSTEM_LANGUAGEFILTER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"languagefilter\"}','','',NULL,NULL,10,0,NULL),(173,0,'plg_system_log','plugin','log',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_log\",\"type\":\"plugin\",\"creationDate\":\"2007-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2007 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_LOG_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"log\"}','','',NULL,NULL,11,0,NULL),(174,0,'plg_system_logout','plugin','logout',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_logout\",\"type\":\"plugin\",\"creationDate\":\"2009-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2009 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_SYSTEM_LOGOUT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"logout\"}','','',NULL,NULL,12,0,NULL),(175,0,'plg_system_logrotation','plugin','logrotation',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_logrotation\",\"type\":\"plugin\",\"creationDate\":\"2018-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_SYSTEM_LOGROTATION_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"logrotation\"}','{\"lastrun\":1680788907}','',NULL,NULL,13,0,NULL),(176,0,'plg_system_privacyconsent','plugin','privacyconsent',NULL,'system',0,0,1,0,1,'{\"name\":\"plg_system_privacyconsent\",\"type\":\"plugin\",\"creationDate\":\"2018-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_SYSTEM_PRIVACYCONSENT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"privacyconsent\"}','{}','',NULL,NULL,14,0,NULL),(177,0,'plg_system_redirect','plugin','redirect',NULL,'system',0,0,1,0,1,'{\"name\":\"plg_system_redirect\",\"type\":\"plugin\",\"creationDate\":\"2009-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2009 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_SYSTEM_REDIRECT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"redirect\"}','','',NULL,NULL,15,0,NULL),(178,0,'plg_system_remember','plugin','remember',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_remember\",\"type\":\"plugin\",\"creationDate\":\"2007-04\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2007 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_REMEMBER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"remember\"}','','',NULL,NULL,16,0,NULL),(179,0,'plg_system_schedulerunner','plugin','schedulerunner',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_schedulerunner\",\"type\":\"plugin\",\"creationDate\":\"2021-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2021 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.1\",\"description\":\"PLG_SYSTEM_SCHEDULERUNNER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"schedulerunner\"}','{}','',NULL,NULL,17,0,NULL),(180,0,'plg_system_sef','plugin','sef',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_sef\",\"type\":\"plugin\",\"creationDate\":\"2007-12\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2007 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_SEF_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"sef\"}','','',NULL,NULL,18,0,NULL),(181,0,'plg_system_sessiongc','plugin','sessiongc',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_sessiongc\",\"type\":\"plugin\",\"creationDate\":\"2018-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.8.6\",\"description\":\"PLG_SYSTEM_SESSIONGC_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"sessiongc\"}','','',NULL,NULL,19,0,NULL),(182,0,'plg_system_shortcut','plugin','shortcut',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_shortcut\",\"type\":\"plugin\",\"creationDate\":\"2022-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2022 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.0\",\"description\":\"PLG_SYSTEM_SHORTCUT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"shortcut\"}','{}','',NULL,NULL,0,0,NULL),(183,0,'plg_system_skipto','plugin','skipto',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_skipto\",\"type\":\"plugin\",\"creationDate\":\"2020-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_SYSTEM_SKIPTO_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"skipto\"}','{}','',NULL,NULL,20,0,NULL),(184,0,'plg_system_stats','plugin','stats',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_stats\",\"type\":\"plugin\",\"creationDate\":\"2013-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.5.0\",\"description\":\"PLG_SYSTEM_STATS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"stats\"}','','',NULL,NULL,21,0,NULL),(185,0,'plg_system_task_notification','plugin','tasknotification',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_task_notification\",\"type\":\"plugin\",\"creationDate\":\"2021-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2021 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.1\",\"description\":\"PLG_SYSTEM_TASK_NOTIFICATION_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"tasknotification\"}','','',NULL,NULL,22,0,NULL),(186,0,'plg_system_updatenotification','plugin','updatenotification',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_updatenotification\",\"type\":\"plugin\",\"creationDate\":\"2015-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2015 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.5.0\",\"description\":\"PLG_SYSTEM_UPDATENOTIFICATION_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"updatenotification\"}','{\"lastrun\":1680788907}','',NULL,NULL,23,0,NULL),(187,0,'plg_system_webauthn','plugin','webauthn',NULL,'system',0,1,1,0,1,'{\"name\":\"plg_system_webauthn\",\"type\":\"plugin\",\"creationDate\":\"2019-07-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_SYSTEM_WEBAUTHN_DESCRIPTION\",\"group\":\"\",\"filename\":\"webauthn\"}','{}','',NULL,NULL,24,0,NULL),(188,0,'plg_task_check_files','plugin','checkfiles',NULL,'task',0,1,1,0,1,'{\"name\":\"plg_task_check_files\",\"type\":\"plugin\",\"creationDate\":\"2021-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2021 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.1\",\"description\":\"PLG_TASK_CHECK_FILES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"checkfiles\"}','{}','',NULL,NULL,1,0,NULL),(189,0,'plg_task_demo_tasks','plugin','demotasks',NULL,'task',0,1,1,0,1,'{\"name\":\"plg_task_demo_tasks\",\"type\":\"plugin\",\"creationDate\":\"2021-07\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2021 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.1\",\"description\":\"PLG_TASK_DEMO_TASKS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"demotasks\"}','{}','',NULL,NULL,2,0,NULL),(190,0,'plg_task_requests','plugin','requests',NULL,'task',0,1,1,0,1,'{\"name\":\"plg_task_requests\",\"type\":\"plugin\",\"creationDate\":\"2021-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2021 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.1\",\"description\":\"PLG_TASK_REQUESTS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"requests\"}','{}','',NULL,NULL,3,0,NULL),(191,0,'plg_task_site_status','plugin','sitestatus',NULL,'task',0,1,1,0,1,'{\"name\":\"plg_task_site_status\",\"type\":\"plugin\",\"creationDate\":\"2021-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2021 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.1\",\"description\":\"PLG_TASK_SITE_STATUS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"sitestatus\"}','{}','',NULL,NULL,4,0,NULL),(192,0,'plg_multifactorauth_totp','plugin','totp',NULL,'multifactorauth',0,1,1,0,1,'{\"name\":\"plg_multifactorauth_totp\",\"type\":\"plugin\",\"creationDate\":\"2013-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.2.0\",\"description\":\"PLG_MULTIFACTORAUTH_TOTP_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"totp\"}','','',NULL,NULL,1,0,NULL),(193,0,'plg_multifactorauth_yubikey','plugin','yubikey',NULL,'multifactorauth',0,1,1,0,1,'{\"name\":\"plg_multifactorauth_yubikey\",\"type\":\"plugin\",\"creationDate\":\"2013-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.2.0\",\"description\":\"PLG_MULTIFACTORAUTH_YUBIKEY_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"yubikey\"}','','',NULL,NULL,2,0,NULL),(194,0,'plg_multifactorauth_webauthn','plugin','webauthn',NULL,'multifactorauth',0,1,1,0,1,'{\"name\":\"plg_multifactorauth_webauthn\",\"type\":\"plugin\",\"creationDate\":\"2022-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2022 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.0\",\"description\":\"PLG_MULTIFACTORAUTH_WEBAUTHN_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"webauthn\"}','','',NULL,NULL,3,0,NULL),(195,0,'plg_multifactorauth_email','plugin','email',NULL,'multifactorauth',0,1,1,0,1,'{\"name\":\"plg_multifactorauth_email\",\"type\":\"plugin\",\"creationDate\":\"2022-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2022 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.0\",\"description\":\"PLG_MULTIFACTORAUTH_EMAIL_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"email\"}','','',NULL,NULL,4,0,NULL),(196,0,'plg_multifactorauth_fixed','plugin','fixed',NULL,'multifactorauth',0,0,1,0,1,'{\"name\":\"plg_multifactorauth_fixed\",\"type\":\"plugin\",\"creationDate\":\"2022-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2022 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.0\",\"description\":\"PLG_MULTIFACTORAUTH_FIXED_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"fixed\"}','','',NULL,NULL,5,0,NULL),(197,0,'plg_user_contactcreator','plugin','contactcreator',NULL,'user',0,0,1,0,1,'{\"name\":\"plg_user_contactcreator\",\"type\":\"plugin\",\"creationDate\":\"2009-08\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2009 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_CONTACTCREATOR_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"contactcreator\"}','{\"autowebpage\":\"\",\"category\":\"4\",\"autopublish\":\"0\"}','',NULL,NULL,1,0,NULL),(198,0,'plg_user_joomla','plugin','joomla',NULL,'user',0,1,1,0,1,'{\"name\":\"plg_user_joomla\",\"type\":\"plugin\",\"creationDate\":\"2006-12\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_USER_JOOMLA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"joomla\"}','{\"autoregister\":\"1\",\"mail_to_user\":\"1\",\"forceLogout\":\"1\"}','',NULL,NULL,2,0,NULL),(199,0,'plg_user_profile','plugin','profile',NULL,'user',0,0,1,0,1,'{\"name\":\"plg_user_profile\",\"type\":\"plugin\",\"creationDate\":\"2008-01\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2008 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.0.0\",\"description\":\"PLG_USER_PROFILE_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"profile\"}','{\"register-require_address1\":\"1\",\"register-require_address2\":\"1\",\"register-require_city\":\"1\",\"register-require_region\":\"1\",\"register-require_country\":\"1\",\"register-require_postal_code\":\"1\",\"register-require_phone\":\"1\",\"register-require_website\":\"1\",\"register-require_favoritebook\":\"1\",\"register-require_aboutme\":\"1\",\"register-require_tos\":\"1\",\"register-require_dob\":\"1\",\"profile-require_address1\":\"1\",\"profile-require_address2\":\"1\",\"profile-require_city\":\"1\",\"profile-require_region\":\"1\",\"profile-require_country\":\"1\",\"profile-require_postal_code\":\"1\",\"profile-require_phone\":\"1\",\"profile-require_website\":\"1\",\"profile-require_favoritebook\":\"1\",\"profile-require_aboutme\":\"1\",\"profile-require_tos\":\"1\",\"profile-require_dob\":\"1\"}','',NULL,NULL,3,0,NULL),(200,0,'plg_user_terms','plugin','terms',NULL,'user',0,0,1,0,1,'{\"name\":\"plg_user_terms\",\"type\":\"plugin\",\"creationDate\":\"2018-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2018 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_USER_TERMS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"terms\"}','{}','',NULL,NULL,4,0,NULL),(201,0,'plg_user_token','plugin','token',NULL,'user',0,1,1,0,1,'{\"name\":\"plg_user_token\",\"type\":\"plugin\",\"creationDate\":\"2019-11\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"3.9.0\",\"description\":\"PLG_USER_TOKEN_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"token\"}','{}','',NULL,NULL,5,0,NULL),(202,0,'plg_webservices_banners','plugin','banners',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_banners\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_BANNERS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"banners\"}','{}','',NULL,NULL,1,0,NULL),(203,0,'plg_webservices_config','plugin','config',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_config\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_CONFIG_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"config\"}','{}','',NULL,NULL,2,0,NULL),(204,0,'plg_webservices_contact','plugin','contact',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_contact\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_CONTACT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"contact\"}','{}','',NULL,NULL,3,0,NULL),(205,0,'plg_webservices_content','plugin','content',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_content\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_CONTENT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"content\"}','{}','',NULL,NULL,4,0,NULL),(206,0,'plg_webservices_installer','plugin','installer',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_installer\",\"type\":\"plugin\",\"creationDate\":\"2020-06\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_INSTALLER_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"installer\"}','{}','',NULL,NULL,5,0,NULL),(207,0,'plg_webservices_languages','plugin','languages',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_languages\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_LANGUAGES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"languages\"}','{}','',NULL,NULL,6,0,NULL),(208,0,'plg_webservices_media','plugin','media',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_media\",\"type\":\"plugin\",\"creationDate\":\"2021-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2021 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.1.0\",\"description\":\"PLG_WEBSERVICES_MEDIA_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"media\"}','{}','',NULL,NULL,7,0,NULL),(209,0,'plg_webservices_menus','plugin','menus',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_menus\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_MENUS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"menus\"}','{}','',NULL,NULL,7,0,NULL),(210,0,'plg_webservices_messages','plugin','messages',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_messages\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_MESSAGES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"messages\"}','{}','',NULL,NULL,8,0,NULL),(211,0,'plg_webservices_modules','plugin','modules',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_modules\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_MODULES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"modules\"}','{}','',NULL,NULL,9,0,NULL),(212,0,'plg_webservices_newsfeeds','plugin','newsfeeds',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_newsfeeds\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_NEWSFEEDS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"newsfeeds\"}','{}','',NULL,NULL,10,0,NULL),(213,0,'plg_webservices_plugins','plugin','plugins',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_plugins\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_PLUGINS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"plugins\"}','{}','',NULL,NULL,11,0,NULL),(214,0,'plg_webservices_privacy','plugin','privacy',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_privacy\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_PRIVACY_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"privacy\"}','{}','',NULL,NULL,12,0,NULL),(215,0,'plg_webservices_redirect','plugin','redirect',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_redirect\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_REDIRECT_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"redirect\"}','{}','',NULL,NULL,13,0,NULL),(216,0,'plg_webservices_tags','plugin','tags',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_tags\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_TAGS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"tags\"}','{}','',NULL,NULL,14,0,NULL),(217,0,'plg_webservices_templates','plugin','templates',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_templates\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_TEMPLATES_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"templates\"}','{}','',NULL,NULL,15,0,NULL),(218,0,'plg_webservices_users','plugin','users',NULL,'webservices',0,1,1,0,1,'{\"name\":\"plg_webservices_users\",\"type\":\"plugin\",\"creationDate\":\"2019-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WEBSERVICES_USERS_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"users\"}','{}','',NULL,NULL,16,0,NULL),(219,0,'plg_workflow_featuring','plugin','featuring',NULL,'workflow',0,1,1,0,1,'{\"name\":\"plg_workflow_featuring\",\"type\":\"plugin\",\"creationDate\":\"2020-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WORKFLOW_FEATURING_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"featuring\"}','{}','',NULL,NULL,1,0,NULL),(220,0,'plg_workflow_notification','plugin','notification',NULL,'workflow',0,1,1,0,1,'{\"name\":\"plg_workflow_notification\",\"type\":\"plugin\",\"creationDate\":\"2020-05\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WORKFLOW_NOTIFICATION_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"notification\"}','{}','',NULL,NULL,2,0,NULL),(221,0,'plg_workflow_publishing','plugin','publishing',NULL,'workflow',0,1,1,0,1,'{\"name\":\"plg_workflow_publishing\",\"type\":\"plugin\",\"creationDate\":\"2020-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.0.0\",\"description\":\"PLG_WORKFLOW_PUBLISHING_XML_DESCRIPTION\",\"group\":\"\",\"filename\":\"publishing\"}','{}','',NULL,NULL,3,0,NULL),(222,0,'atum','template','atum',NULL,'',1,1,1,0,1,'{\"name\":\"atum\",\"type\":\"template\",\"creationDate\":\"2016-09\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2016 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"\",\"version\":\"1.0\",\"description\":\"TPL_ATUM_XML_DESCRIPTION\",\"group\":\"\",\"inheritable\":true,\"filename\":\"templateDetails\"}','','',NULL,NULL,0,0,NULL),(223,0,'cassiopeia','template','cassiopeia',NULL,'',0,1,1,0,1,'{\"name\":\"cassiopeia\",\"type\":\"template\",\"creationDate\":\"2017-02\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2017 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"\",\"version\":\"1.0\",\"description\":\"TPL_CASSIOPEIA_XML_DESCRIPTION\",\"group\":\"\",\"inheritable\":true,\"filename\":\"templateDetails\"}','{\"logoFile\":\"\",\"fluidContainer\":\"0\",\"sidebarLeftWidth\":\"3\",\"sidebarRightWidth\":\"3\"}','',NULL,NULL,0,0,NULL),(224,0,'files_joomla','file','joomla',NULL,'',0,1,1,1,1,'{\"name\":\"files_joomla\",\"type\":\"file\",\"creationDate\":\"2023-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.9\",\"description\":\"FILES_JOOMLA_XML_DESCRIPTION\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(225,0,'English (en-GB) Language Pack','package','pkg_en-GB',NULL,'',0,1,1,1,1,'{\"name\":\"English (en-GB) Language Pack\",\"type\":\"package\",\"creationDate\":\"2023-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.9.1\",\"description\":\"en-GB language pack\",\"group\":\"\",\"filename\":\"pkg_en-GB\"}','','',NULL,NULL,0,0,NULL),(226,225,'English (en-GB)','language','en-GB',NULL,'',0,1,1,1,1,'{\"name\":\"English (en-GB)\",\"type\":\"language\",\"creationDate\":\"2023-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2006 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.9\",\"description\":\"en-GB site language\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(227,225,'English (en-GB)','language','en-GB',NULL,'',1,1,1,1,1,'{\"name\":\"English (en-GB)\",\"type\":\"language\",\"creationDate\":\"2023-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.9\",\"description\":\"en-GB administrator language\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(228,225,'English (en-GB)','language','en-GB',NULL,'',3,1,1,1,1,'{\"name\":\"English (en-GB)\",\"type\":\"language\",\"creationDate\":\"2023-03\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"4.2.9\",\"description\":\"en-GB api language\",\"group\":\"\"}','','',NULL,NULL,0,0,NULL),(229,232,'French (fr-FR)','language','fr-FR','','',0,1,0,0,0,'{\"name\":\"French (fr-FR)\",\"type\":\"language\",\"creationDate\":\"Mars 2023\",\"author\":\"Joomla! Project - French translation team\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"traduction@joomla.fr\",\"authorUrl\":\"www.joomla.fr\",\"version\":\"4.2.9\",\"description\":\"fr-FR site language\",\"group\":\"\",\"filename\":\"install\"}','{}','',NULL,NULL,0,0,NULL),(230,232,'French (fr-FR)','language','fr-FR','','',1,1,0,0,0,'{\"name\":\"French (fr-FR)\",\"type\":\"language\",\"creationDate\":\"Mars 2023\",\"author\":\"Joomla! Project - French translation team\",\"copyright\":\"(C) 2013 Open Source Matters, Inc.\",\"authorEmail\":\"traduction@joomla.fr\",\"authorUrl\":\"www.joomla.fr\",\"version\":\"4.2.9\",\"description\":\"fr-FR Administrator language\",\"group\":\"\",\"filename\":\"install\"}','{}','',NULL,NULL,0,0,NULL),(231,232,'French (fr-FR)','language','fr-FR','','',3,1,0,0,0,'{\"name\":\"French (fr-FR)\",\"type\":\"language\",\"creationDate\":\"Mars 2023\",\"author\":\"Joomla! Project - French translation team\",\"copyright\":\"(C) 2020 Open Source Matters, Inc.\",\"authorEmail\":\"traduction@joomla.fr\",\"authorUrl\":\"www.joomla.fr\",\"version\":\"4.2.9\",\"description\":\"fr-FR api language\",\"group\":\"\",\"filename\":\"install\"}','{}','',NULL,NULL,0,0,NULL),(232,0,'French (fr-FR) Language pack','package','pkg_fr-FR','','',0,1,1,0,0,'{\"name\":\"French (fr-FR) Language pack\",\"type\":\"package\",\"creationDate\":\"Mars 2023\",\"author\":\"Joomla! Project - French translation team\",\"copyright\":\"Copyright (C) 2019 Open Source Matters, Inc.\",\"authorEmail\":\"traduction@joomla.fr\",\"authorUrl\":\"www.joomla.fr\",\"version\":\"4.2.9.3\",\"description\":\"<div style=\\\"text-align: left;\\\">\\n<h3>Joomla! 4 Full French (fr-FR) Language Package - Version 4.2.9 v3<\\/h3>\\n<h3>Pack de langue Joomla! 4 fran\\u00e7ais (fr-FR) complet - Version 4.2.9 v3<\\/h3>\\n<p><a href=\\\"https:\\/www.joomla.fr\\\" target=\\\"_blank\\\">www.joomla.fr<\\/a> - <a href=\\\"mailto:traduction@joomla.fr\\\">traduction@joomla.fr<\\/a><\\/p>\\n<\\/div>\",\"group\":\"\",\"filename\":\"pkg_fr-FR\"}','{}','',NULL,NULL,0,0,NULL),(233, 0, 'Securitycheck Pro Info Module', 'module', 'mod_scpadmin_quickicons', '', '', 1, 1, 1, 0, 0, '{"name":"Securitycheck Pro Info Module","type":"module","creationDate":"02-06-2022","author":"Jos\\u00e9 A. Luque ","copyright":"Copyright www.protegetuordenador.com","authorEmail":"contacto@protegetuordenador.com","authorUrl":"www.protegetuordenador.com","version":"3.5.0","description":"MOD_SECURITYCHECKPRO_DESCRIPTION","group":"","filename":"mod_scpadmin_quickicons"}', '{"check_vulnerable_extensions":"1","check_not_readed_logs":"1","check_file_permissions":"1","check_file_integrity":"1","check_malwarescan":"1"}', '', null, null, 0, 0, null),(234, 0, 'System - Securitycheck Pro', 'plugin', 'securitycheckpro', '', 'system', 0, 1, 1, 0, 0, '{"name":"System - Securitycheck Pro","type":"plugin","creationDate":"02-06-2022","author":"Jos\\u00e9 A. Luque ","copyright":"Copyright www.protegetuordenador.com","authorEmail":"contacto@protegetuordenador.com","authorUrl":"www.protegetuordenador.com","version":"3.5.0","description":"PLG_SECURITYCHECKPRO_DESCRIPTION","group":"","filename":"securitycheckpro"}', '{}', '', null, null, 0, 0, null),(235, 0, 'Securitycheck Pro', 'component', 'com_securitycheckpro', null, '', 1, 1, 1, 0, 0, '{"name":"Securitycheck Pro","type":"component","creationDate":"02-06-2022","author":"Jose A. Luque","copyright":"Copyright www.protegetuordenador.com","authorEmail":"contacto@protegetuordenador.com","authorUrl":"http:\\/\\/www.protegetuordenador.com","version":"3.5.0","description":"COM_SECURITYCHECKPRO_DESCRIPTION","group":"","filename":"securitycheckpro"}', '{}', '', null, null, 0, 0, null),(236, 0, 'System - Securitycheck Pro Cron', 'plugin', 'securitycheckpro_cron', '', 'system', 0, 0, 1, 0, 0, '{"name":"System - Securitycheck Pro Cron","type":"plugin","creationDate":"02-06-2022","author":"Jos\\u00e9 A. Luque ","copyright":"Copyright www.protegetuordenador.com","authorEmail":"contacto@protegetuordenador.com","authorUrl":"www.protegetuordenador.com","version":"3.5.0","description":"PLG_SECURITYCHECKPRO_CRON_DESCRIPTION","group":"","filename":"securitycheckpro_cron"}', '{}', '', null, null, 0, 0, null);;
/*!40000 ALTER TABLE `jos_extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_fields`
--

DROP TABLE IF EXISTS `jos_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT '0',
  `context` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `group_id` int unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `default_value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` tinyint NOT NULL DEFAULT '0',
  `required` tinyint NOT NULL DEFAULT '0',
  `only_use_in_subform` tinyint NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` int NOT NULL DEFAULT '0',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fieldparams` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_time` datetime NOT NULL,
  `created_user_id` int unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT '0',
  `access` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_created_user_id` (`created_user_id`),
  KEY `idx_access` (`access`),
  KEY `idx_context` (`context`(191)),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_fields`
--

LOCK TABLES `jos_fields` WRITE;
/*!40000 ALTER TABLE `jos_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_fields_categories`
--

DROP TABLE IF EXISTS `jos_fields_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_fields_categories` (
  `field_id` int NOT NULL DEFAULT '0',
  `category_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_fields_categories`
--

LOCK TABLES `jos_fields_categories` WRITE;
/*!40000 ALTER TABLE `jos_fields_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_fields_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_fields_groups`
--

DROP TABLE IF EXISTS `jos_fields_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_fields_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT '0',
  `context` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` tinyint NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` int NOT NULL DEFAULT '0',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT '0',
  `access` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_access` (`access`),
  KEY `idx_context` (`context`(191)),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_fields_groups`
--

LOCK TABLES `jos_fields_groups` WRITE;
/*!40000 ALTER TABLE `jos_fields_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_fields_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_fields_values`
--

DROP TABLE IF EXISTS `jos_fields_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_fields_values` (
  `field_id` int unsigned NOT NULL,
  `item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Allow references to items which have strings as ids, eg. none db systems.',
  `value` text COLLATE utf8mb4_unicode_ci,
  KEY `idx_field_id` (`field_id`),
  KEY `idx_item_id` (`item_id`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_fields_values`
--

LOCK TABLES `jos_fields_values` WRITE;
/*!40000 ALTER TABLE `jos_fields_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_fields_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_filters`
--

DROP TABLE IF EXISTS `jos_finder_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_filters` (
  `filter_id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` tinyint NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `map_count` int unsigned NOT NULL DEFAULT '0',
  `data` text COLLATE utf8mb4_unicode_ci,
  `params` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_filters`
--

LOCK TABLES `jos_finder_filters` WRITE;
/*!40000 ALTER TABLE `jos_finder_filters` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_finder_filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_links`
--

DROP TABLE IF EXISTS `jos_finder_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_links` (
  `link_id` int unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `indexdate` datetime NOT NULL,
  `md5sum` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `published` tinyint NOT NULL DEFAULT '1',
  `state` int NOT NULL DEFAULT '1',
  `access` int NOT NULL DEFAULT '0',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `publish_start_date` datetime DEFAULT NULL,
  `publish_end_date` datetime DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `list_price` double unsigned NOT NULL DEFAULT '0',
  `sale_price` double unsigned NOT NULL DEFAULT '0',
  `type_id` int NOT NULL,
  `object` mediumblob,
  PRIMARY KEY (`link_id`),
  KEY `idx_type` (`type_id`),
  KEY `idx_title` (`title`(100)),
  KEY `idx_md5` (`md5sum`),
  KEY `idx_url` (`url`(75)),
  KEY `idx_language` (`language`),
  KEY `idx_published_list` (`published`,`state`,`access`,`publish_start_date`,`publish_end_date`,`list_price`),
  KEY `idx_published_sale` (`published`,`state`,`access`,`publish_start_date`,`publish_end_date`,`sale_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_links`
--

LOCK TABLES `jos_finder_links` WRITE;
/*!40000 ALTER TABLE `jos_finder_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_finder_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_links_terms`
--

DROP TABLE IF EXISTS `jos_finder_links_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_links_terms` (
  `link_id` int unsigned NOT NULL,
  `term_id` int unsigned NOT NULL,
  `weight` float unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_links_terms`
--

LOCK TABLES `jos_finder_links_terms` WRITE;
/*!40000 ALTER TABLE `jos_finder_links_terms` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_finder_links_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_logging`
--

DROP TABLE IF EXISTS `jos_finder_logging`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_logging` (
  `searchterm` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `md5sum` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `query` blob NOT NULL,
  `hits` int NOT NULL DEFAULT '1',
  `results` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`md5sum`),
  KEY `searchterm` (`searchterm`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_logging`
--

LOCK TABLES `jos_finder_logging` WRITE;
/*!40000 ALTER TABLE `jos_finder_logging` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_finder_logging` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_taxonomy`
--

DROP TABLE IF EXISTS `jos_finder_taxonomy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_taxonomy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned NOT NULL DEFAULT '0',
  `lft` int NOT NULL DEFAULT '0',
  `rgt` int NOT NULL DEFAULT '0',
  `level` int unsigned NOT NULL DEFAULT '0',
  `path` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `alias` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `state` tinyint unsigned NOT NULL DEFAULT '1',
  `access` tinyint unsigned NOT NULL DEFAULT '1',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`),
  KEY `idx_access` (`access`),
  KEY `idx_path` (`path`(100)),
  KEY `idx_level` (`level`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`(100)),
  KEY `idx_language` (`language`),
  KEY `idx_parent_published` (`parent_id`,`state`,`access`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_taxonomy`
--

LOCK TABLES `jos_finder_taxonomy` WRITE;
/*!40000 ALTER TABLE `jos_finder_taxonomy` DISABLE KEYS */;
INSERT INTO `jos_finder_taxonomy` VALUES (1,0,0,1,0,'','ROOT','root',1,1,'*');
/*!40000 ALTER TABLE `jos_finder_taxonomy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_taxonomy_map`
--

DROP TABLE IF EXISTS `jos_finder_taxonomy_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_taxonomy_map` (
  `link_id` int unsigned NOT NULL,
  `node_id` int unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`node_id`),
  KEY `link_id` (`link_id`),
  KEY `node_id` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_taxonomy_map`
--

LOCK TABLES `jos_finder_taxonomy_map` WRITE;
/*!40000 ALTER TABLE `jos_finder_taxonomy_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_finder_taxonomy_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_terms`
--

DROP TABLE IF EXISTS `jos_finder_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_terms` (
  `term_id` int unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `stem` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `common` tinyint unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint unsigned NOT NULL DEFAULT '0',
  `weight` float unsigned NOT NULL DEFAULT '0',
  `soundex` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `links` int NOT NULL DEFAULT '0',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`term_id`),
  UNIQUE KEY `idx_term_language` (`term`,`language`),
  KEY `idx_stem` (`stem`),
  KEY `idx_term_phrase` (`term`,`phrase`),
  KEY `idx_stem_phrase` (`stem`,`phrase`),
  KEY `idx_soundex_phrase` (`soundex`,`phrase`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_terms`
--

LOCK TABLES `jos_finder_terms` WRITE;
/*!40000 ALTER TABLE `jos_finder_terms` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_finder_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_terms_common`
--

DROP TABLE IF EXISTS `jos_finder_terms_common`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_terms_common` (
  `term` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `custom` int NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_term_language` (`term`,`language`),
  KEY `idx_lang` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_terms_common`
--

LOCK TABLES `jos_finder_terms_common` WRITE;
/*!40000 ALTER TABLE `jos_finder_terms_common` DISABLE KEYS */;
INSERT INTO `jos_finder_terms_common` VALUES ('a','en',0),('about','en',0),('above','en',0),('after','en',0),('again','en',0),('against','en',0),('all','en',0),('am','en',0),('an','en',0),('and','en',0),('any','en',0),('are','en',0),('aren\'t','en',0),('as','en',0),('at','en',0),('be','en',0),('because','en',0),('been','en',0),('before','en',0),('being','en',0),('below','en',0),('between','en',0),('both','en',0),('but','en',0),('by','en',0),('can\'t','en',0),('cannot','en',0),('could','en',0),('couldn\'t','en',0),('did','en',0),('didn\'t','en',0),('do','en',0),('does','en',0),('doesn\'t','en',0),('doing','en',0),('don\'t','en',0),('down','en',0),('during','en',0),('each','en',0),('few','en',0),('for','en',0),('from','en',0),('further','en',0),('had','en',0),('hadn\'t','en',0),('has','en',0),('hasn\'t','en',0),('have','en',0),('haven\'t','en',0),('having','en',0),('he','en',0),('he\'d','en',0),('he\'ll','en',0),('he\'s','en',0),('her','en',0),('here','en',0),('here\'s','en',0),('hers','en',0),('herself','en',0),('him','en',0),('himself','en',0),('his','en',0),('how','en',0),('how\'s','en',0),('i','en',0),('i\'d','en',0),('i\'ll','en',0),('i\'m','en',0),('i\'ve','en',0),('if','en',0),('in','en',0),('into','en',0),('is','en',0),('isn\'t','en',0),('it','en',0),('it\'s','en',0),('its','en',0),('itself','en',0),('let\'s','en',0),('me','en',0),('more','en',0),('most','en',0),('mustn\'t','en',0),('my','en',0),('myself','en',0),('no','en',0),('nor','en',0),('not','en',0),('of','en',0),('off','en',0),('on','en',0),('once','en',0),('only','en',0),('or','en',0),('other','en',0),('ought','en',0),('our','en',0),('ours','en',0),('ourselves','en',0),('out','en',0),('over','en',0),('own','en',0),('same','en',0),('shan\'t','en',0),('she','en',0),('she\'d','en',0),('she\'ll','en',0),('she\'s','en',0),('should','en',0),('shouldn\'t','en',0),('so','en',0),('some','en',0),('such','en',0),('than','en',0),('that','en',0),('that\'s','en',0),('the','en',0),('their','en',0),('theirs','en',0),('them','en',0),('themselves','en',0),('then','en',0),('there','en',0),('there\'s','en',0),('these','en',0),('they','en',0),('they\'d','en',0),('they\'ll','en',0),('they\'re','en',0),('they\'ve','en',0),('this','en',0),('those','en',0),('through','en',0),('to','en',0),('too','en',0),('under','en',0),('until','en',0),('up','en',0),('very','en',0),('was','en',0),('wasn\'t','en',0),('we','en',0),('we\'d','en',0),('we\'ll','en',0),('we\'re','en',0),('we\'ve','en',0),('were','en',0),('weren\'t','en',0),('what','en',0),('what\'s','en',0),('when','en',0),('when\'s','en',0),('where','en',0),('where\'s','en',0),('which','en',0),('while','en',0),('who','en',0),('who\'s','en',0),('whom','en',0),('why','en',0),('why\'s','en',0),('with','en',0),('won\'t','en',0),('would','en',0),('wouldn\'t','en',0),('you','en',0),('you\'d','en',0),('you\'ll','en',0),('you\'re','en',0),('you\'ve','en',0),('your','en',0),('yours','en',0),('yourself','en',0),('yourselves','en',0);
/*!40000 ALTER TABLE `jos_finder_terms_common` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_tokens`
--

DROP TABLE IF EXISTS `jos_finder_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_tokens` (
  `term` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `stem` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `common` tinyint unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint unsigned NOT NULL DEFAULT '0',
  `weight` float unsigned NOT NULL DEFAULT '1',
  `context` tinyint unsigned NOT NULL DEFAULT '2',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  KEY `idx_word` (`term`),
  KEY `idx_stem` (`stem`),
  KEY `idx_context` (`context`),
  KEY `idx_language` (`language`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_tokens`
--

LOCK TABLES `jos_finder_tokens` WRITE;
/*!40000 ALTER TABLE `jos_finder_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_finder_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_tokens_aggregate`
--

DROP TABLE IF EXISTS `jos_finder_tokens_aggregate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_tokens_aggregate` (
  `term_id` int unsigned NOT NULL,
  `term` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `stem` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `common` tinyint unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint unsigned NOT NULL DEFAULT '0',
  `term_weight` float unsigned NOT NULL DEFAULT '0',
  `context` tinyint unsigned NOT NULL DEFAULT '2',
  `context_weight` float unsigned NOT NULL DEFAULT '0',
  `total_weight` float unsigned NOT NULL DEFAULT '0',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  KEY `token` (`term`),
  KEY `keyword_id` (`term_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_tokens_aggregate`
--

LOCK TABLES `jos_finder_tokens_aggregate` WRITE;
/*!40000 ALTER TABLE `jos_finder_tokens_aggregate` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_finder_tokens_aggregate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_finder_types`
--

DROP TABLE IF EXISTS `jos_finder_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_finder_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_finder_types`
--

LOCK TABLES `jos_finder_types` WRITE;
/*!40000 ALTER TABLE `jos_finder_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_finder_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_history`
--

DROP TABLE IF EXISTS `jos_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_history` (
  `version_id` int unsigned NOT NULL AUTO_INCREMENT,
  `item_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version_note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Optional version name',
  `save_date` datetime NOT NULL,
  `editor_user_id` int unsigned NOT NULL DEFAULT '0',
  `character_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'Number of characters in this version.',
  `sha1_hash` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SHA1 hash of the version_data column.',
  `version_data` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'json-encoded string of version data',
  `keep_forever` tinyint NOT NULL DEFAULT '0' COMMENT '0=auto delete; 1=keep',
  PRIMARY KEY (`version_id`),
  KEY `idx_ucm_item_id` (`item_id`),
  KEY `idx_save_date` (`save_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_history`
--

LOCK TABLES `jos_history` WRITE;
/*!40000 ALTER TABLE `jos_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_languages`
--

DROP TABLE IF EXISTS `jos_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_languages` (
  `lang_id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT '0',
  `lang_code` char(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_native` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sef` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metakey` text COLLATE utf8mb4_unicode_ci,
  `metadesc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sitename` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `published` int NOT NULL DEFAULT '0',
  `access` int unsigned NOT NULL DEFAULT '0',
  `ordering` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`lang_id`),
  UNIQUE KEY `idx_sef` (`sef`),
  UNIQUE KEY `idx_langcode` (`lang_code`),
  KEY `idx_access` (`access`),
  KEY `idx_ordering` (`ordering`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_languages`
--

LOCK TABLES `jos_languages` WRITE;
/*!40000 ALTER TABLE `jos_languages` DISABLE KEYS */;
INSERT INTO `jos_languages` VALUES (1,0,'en-GB','English (en-GB)','English (United Kingdom)','en','en_gb','','','','',1,1,2),(2,91,'fr-FR','French (fr-FR)','Français (France)','fr','fr_fr','',NULL,'','',0,1,1);
/*!40000 ALTER TABLE `jos_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_mail_templates`
--

DROP TABLE IF EXISTS `jos_mail_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_mail_templates` (
  `template_id` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `extension` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `htmlbody` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`template_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_mail_templates`
--

LOCK TABLES `jos_mail_templates` WRITE;
/*!40000 ALTER TABLE `jos_mail_templates` DISABLE KEYS */;
INSERT INTO `jos_mail_templates` VALUES ('com_actionlogs.notification','com_actionlogs','','COM_ACTIONLOGS_EMAIL_SUBJECT','COM_ACTIONLOGS_EMAIL_BODY','COM_ACTIONLOGS_EMAIL_HTMLBODY','','{\"tags\":[\"message\",\"date\",\"extension\",\"username\"]}'),('com_config.test_mail','com_config','','COM_CONFIG_SENDMAIL_SUBJECT','COM_CONFIG_SENDMAIL_BODY','','','{\"tags\":[\"sitename\",\"method\"]}'),('com_contact.mail','com_contact','','COM_CONTACT_ENQUIRY_SUBJECT','COM_CONTACT_ENQUIRY_TEXT','','','{\"tags\":[\"sitename\",\"name\",\"email\",\"subject\",\"body\",\"url\",\"customfields\"]}'),('com_contact.mail.copy','com_contact','','COM_CONTACT_COPYSUBJECT_OF','COM_CONTACT_COPYTEXT_OF','','','{\"tags\":[\"sitename\",\"name\",\"email\",\"subject\",\"body\",\"url\",\"customfields\",\"contactname\"]}'),('com_messages.new_message','com_messages','','COM_MESSAGES_NEW_MESSAGE','COM_MESSAGES_NEW_MESSAGE_BODY','','','{\"tags\":[\"subject\",\"message\",\"fromname\",\"sitename\",\"siteurl\",\"fromemail\",\"toname\",\"toemail\"]}'),('com_privacy.notification.admin.export','com_privacy','','COM_PRIVACY_EMAIL_ADMIN_REQUEST_SUBJECT_EXPORT_REQUEST','COM_PRIVACY_EMAIL_ADMIN_REQUEST_BODY_EXPORT_REQUEST','','','{\"tags\":[\"sitename\",\"url\",\"tokenurl\",\"formurl\",\"token\"]}'),('com_privacy.notification.admin.remove','com_privacy','','COM_PRIVACY_EMAIL_ADMIN_REQUEST_SUBJECT_REMOVE_REQUEST','COM_PRIVACY_EMAIL_ADMIN_REQUEST_BODY_REMOVE_REQUEST','','','{\"tags\":[\"sitename\",\"url\",\"tokenurl\",\"formurl\",\"token\"]}'),('com_privacy.notification.export','com_privacy','','COM_PRIVACY_EMAIL_REQUEST_SUBJECT_EXPORT_REQUEST','COM_PRIVACY_EMAIL_REQUEST_BODY_EXPORT_REQUEST','','','{\"tags\":[\"sitename\",\"url\",\"tokenurl\",\"formurl\",\"token\"]}'),('com_privacy.notification.remove','com_privacy','','COM_PRIVACY_EMAIL_REQUEST_SUBJECT_REMOVE_REQUEST','COM_PRIVACY_EMAIL_REQUEST_BODY_REMOVE_REQUEST','','','{\"tags\":[\"sitename\",\"url\",\"tokenurl\",\"formurl\",\"token\"]}'),('com_privacy.userdataexport','com_privacy','','COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_SUBJECT','COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_BODY','','','{\"tags\":[\"sitename\",\"url\"]}'),('com_users.massmail.mail','com_users','','COM_USERS_MASSMAIL_MAIL_SUBJECT','COM_USERS_MASSMAIL_MAIL_BODY','','','{\"tags\":[\"subject\",\"body\",\"subjectprefix\",\"bodysuffix\"]}'),('com_users.password_reset','com_users','','COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT','COM_USERS_EMAIL_PASSWORD_RESET_BODY','','','{\"tags\":[\"name\",\"email\",\"sitename\",\"link_text\",\"link_html\",\"token\"]}'),('com_users.registration.admin.new_notification','com_users','','COM_USERS_EMAIL_ACCOUNT_DETAILS','COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY','','','{\"tags\":[\"name\",\"sitename\",\"siteurl\",\"username\"]}'),('com_users.registration.admin.verification_request','com_users','','COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT','COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_BODY','','','{\"tags\":[\"name\",\"sitename\",\"email\",\"username\",\"activate\"]}'),('com_users.registration.user.admin_activated','com_users','','COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT','COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_BODY','','','{\"tags\":[\"name\",\"sitename\",\"siteurl\",\"username\"]}'),('com_users.registration.user.admin_activation','com_users','','COM_USERS_EMAIL_ACCOUNT_DETAILS','COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW','','','{\"tags\":[\"name\",\"sitename\",\"activate\",\"siteurl\",\"username\"]}'),('com_users.registration.user.admin_activation_w_pw','com_users','','COM_USERS_EMAIL_ACCOUNT_DETAILS','COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY','','','{\"tags\":[\"name\",\"sitename\",\"activate\",\"siteurl\",\"username\",\"password_clear\"]}'),('com_users.registration.user.registration_mail','com_users','','COM_USERS_EMAIL_ACCOUNT_DETAILS','COM_USERS_EMAIL_REGISTERED_BODY_NOPW','','','{\"tags\":[\"name\",\"sitename\",\"siteurl\",\"username\"]}'),('com_users.registration.user.registration_mail_w_pw','com_users','','COM_USERS_EMAIL_ACCOUNT_DETAILS','COM_USERS_EMAIL_REGISTERED_BODY','','','{\"tags\":[\"name\",\"sitename\",\"siteurl\",\"username\",\"password_clear\"]}'),('com_users.registration.user.self_activation','com_users','','COM_USERS_EMAIL_ACCOUNT_DETAILS','COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW','','','{\"tags\":[\"name\",\"sitename\",\"activate\",\"siteurl\",\"username\"]}'),('com_users.registration.user.self_activation_w_pw','com_users','','COM_USERS_EMAIL_ACCOUNT_DETAILS','COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY','','','{\"tags\":[\"name\",\"sitename\",\"activate\",\"siteurl\",\"username\",\"password_clear\"]}'),('com_users.reminder','com_users','','COM_USERS_EMAIL_USERNAME_REMINDER_SUBJECT','COM_USERS_EMAIL_USERNAME_REMINDER_BODY','','','{\"tags\":[\"name\",\"username\",\"sitename\",\"email\",\"link_text\",\"link_html\"]}'),('plg_multifactorauth_email.mail','plg_multifactorauth_email','','PLG_MULTIFACTORAUTH_EMAIL_EMAIL_SUBJECT','PLG_MULTIFACTORAUTH_EMAIL_EMAIL_BODY','','','{\"tags\":[\"code\",\"sitename\",\"siteurl\",\"username\",\"email\",\"fullname\"]}'),('plg_system_privacyconsent.request.reminder','plg_system_privacyconsent','','PLG_SYSTEM_PRIVACYCONSENT_EMAIL_REMIND_SUBJECT','PLG_SYSTEM_PRIVACYCONSENT_EMAIL_REMIND_BODY','','','{\"tags\":[\"sitename\",\"url\",\"tokenurl\",\"formurl\",\"token\"]}'),('plg_system_tasknotification.failure_mail','plg_system_tasknotification','','PLG_SYSTEM_TASK_NOTIFICATION_FAILURE_MAIL_SUBJECT','PLG_SYSTEM_TASK_NOTIFICATION_FAILURE_MAIL_BODY','','','{\"tags\": [\"task_id\", \"task_title\", \"exit_code\", \"exec_data_time\", \"task_output\"]}'),('plg_system_tasknotification.fatal_recovery_mail','plg_system_tasknotification','','PLG_SYSTEM_TASK_NOTIFICATION_FATAL_MAIL_SUBJECT','PLG_SYSTEM_TASK_NOTIFICATION_FATAL_MAIL_BODY','','','{\"tags\": [\"task_id\", \"task_title\"]}'),('plg_system_tasknotification.orphan_mail','plg_system_tasknotification','','PLG_SYSTEM_TASK_NOTIFICATION_ORPHAN_MAIL_SUBJECT','PLG_SYSTEM_TASK_NOTIFICATION_ORPHAN_MAIL_BODY','','','{\"tags\": [\"task_id\", \"task_title\"]}'),('plg_system_tasknotification.success_mail','plg_system_tasknotification','','PLG_SYSTEM_TASK_NOTIFICATION_SUCCESS_MAIL_SUBJECT','PLG_SYSTEM_TASK_NOTIFICATION_SUCCESS_MAIL_BODY','','','{\"tags\":[\"task_id\", \"task_title\", \"exec_data_time\", \"task_output\"]}'),('plg_system_updatenotification.mail','plg_system_updatenotification','','PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_SUBJECT','PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_BODY','','','{\"tags\":[\"newversion\",\"curversion\",\"sitename\",\"url\",\"link\",\"releasenews\"]}'),('plg_user_joomla.mail','plg_user_joomla','','PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT','PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY','','','{\"tags\":[\"name\",\"sitename\",\"url\",\"username\",\"password\",\"email\"]}');
/*!40000 ALTER TABLE `jos_mail_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_menu`
--

DROP TABLE IF EXISTS `jos_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menutype` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The type of menu this item belongs to. FK to #__menu_types.menutype',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The display title of the menu item.',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'The SEF alias of the menu item.',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `path` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The computed path of the menu item based on the alias field.',
  `link` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The actually link the menu item refers to.',
  `type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator',
  `published` tinyint NOT NULL DEFAULT '0' COMMENT 'The published state of the menu link.',
  `parent_id` int unsigned NOT NULL DEFAULT '1' COMMENT 'The parent menu item in the menu tree.',
  `level` int unsigned NOT NULL DEFAULT '0' COMMENT 'The relative level in the tree.',
  `component_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'FK to #__extensions.id',
  `checked_out` int unsigned DEFAULT NULL COMMENT 'FK to #__users.id',
  `checked_out_time` datetime DEFAULT NULL COMMENT 'The time the menu item was checked out.',
  `browserNav` tinyint NOT NULL DEFAULT '0' COMMENT 'The click behaviour of the link.',
  `access` int unsigned NOT NULL DEFAULT '0' COMMENT 'The access level required to view the menu item.',
  `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The image of the menu item.',
  `template_style_id` int unsigned NOT NULL DEFAULT '0',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'JSON encoded data for the menu item.',
  `lft` int NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `home` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Indicates if this menu item is the home or default page.',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `client_id` tinyint NOT NULL DEFAULT '0',
  `publish_up` datetime DEFAULT NULL,
  `publish_down` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_client_id_parent_id_alias_language` (`client_id`,`parent_id`,`alias`(100),`language`),
  KEY `idx_componentid` (`component_id`,`menutype`,`published`,`access`),
  KEY `idx_menutype` (`menutype`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`(100)),
  KEY `idx_path` (`path`(100)),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_menu`
--

LOCK TABLES `jos_menu` WRITE;
/*!40000 ALTER TABLE `jos_menu` DISABLE KEYS */;
INSERT INTO `jos_menu` VALUES (1,'','Menu_Item_Root','root','','','','',1,0,0,0,NULL,NULL,0,0,'',0,'',0,43,0,'*',0,NULL,NULL),(2,'main','com_banners','Banners','','Banners','index.php?option=com_banners','component',1,1,1,3,NULL,NULL,0,0,'class:bookmark',0,'',1,10,0,'*',1,NULL,NULL),(3,'main','com_banners','Banners','','Banners/Banners','index.php?option=com_banners&view=banners','component',1,2,2,3,NULL,NULL,0,0,'class:banners',0,'',2,3,0,'*',1,NULL,NULL),(4,'main','com_banners_categories','Categories','','Banners/Categories','index.php?option=com_categories&view=categories&extension=com_banners','component',1,2,2,5,NULL,NULL,0,0,'class:banners-cat',0,'',4,5,0,'*',1,NULL,NULL),(5,'main','com_banners_clients','Clients','','Banners/Clients','index.php?option=com_banners&view=clients','component',1,2,2,3,NULL,NULL,0,0,'class:banners-clients',0,'',6,7,0,'*',1,NULL,NULL),(6,'main','com_banners_tracks','Tracks','','Banners/Tracks','index.php?option=com_banners&view=tracks','component',1,2,2,3,NULL,NULL,0,0,'class:banners-tracks',0,'',8,9,0,'*',1,NULL,NULL),(7,'main','com_contact','Contacts','','Contacts','index.php?option=com_contact','component',1,1,1,7,NULL,NULL,0,0,'class:address-book',0,'',11,20,0,'*',1,NULL,NULL),(8,'main','com_contact_contacts','Contacts','','Contacts/Contacts','index.php?option=com_contact&view=contacts','component',1,7,2,7,NULL,NULL,0,0,'class:contact',0,'',12,13,0,'*',1,NULL,NULL),(9,'main','com_contact_categories','Categories','','Contacts/Categories','index.php?option=com_categories&view=categories&extension=com_contact','component',1,7,2,5,NULL,NULL,0,0,'class:contact-cat',0,'',14,15,0,'*',1,NULL,NULL),(10,'main','com_newsfeeds','News Feeds','','News Feeds','index.php?option=com_newsfeeds','component',1,1,1,16,NULL,NULL,0,0,'class:rss',0,'',23,28,0,'*',1,NULL,NULL),(11,'main','com_newsfeeds_feeds','Feeds','','News Feeds/Feeds','index.php?option=com_newsfeeds&view=newsfeeds','component',1,10,2,16,NULL,NULL,0,0,'class:newsfeeds',0,'',24,25,0,'*',1,NULL,NULL),(12,'main','com_newsfeeds_categories','Categories','','News Feeds/Categories','index.php?option=com_categories&view=categories&extension=com_newsfeeds','component',1,10,2,5,NULL,NULL,0,0,'class:newsfeeds-cat',0,'',26,27,0,'*',1,NULL,NULL),(13,'main','com_finder','Smart Search','','Smart Search','index.php?option=com_finder','component',1,1,1,23,NULL,NULL,0,0,'class:search-plus',0,'',29,38,0,'*',1,NULL,NULL),(14,'main','com_tags','Tags','','Tags','index.php?option=com_tags&view=tags','component',1,1,1,25,NULL,NULL,0,1,'class:tags',0,'',39,40,0,'',1,NULL,NULL),(15,'main','com_associations','Multilingual Associations','','Multilingual Associations','index.php?option=com_associations&view=associations','component',1,1,1,30,NULL,NULL,0,0,'class:language',0,'',21,22,0,'*',1,NULL,NULL),(16,'main','mod_menu_fields','Contact Custom Fields','','contact/Custom Fields','index.php?option=com_fields&context=com_contact.contact','component',1,7,2,29,NULL,NULL,0,0,'class:messages-add',0,'',16,17,0,'*',1,NULL,NULL),(17,'main','mod_menu_fields_group','Contact Custom Fields Group','','contact/Custom Fields Group','index.php?option=com_fields&view=groups&context=com_contact.contact','component',1,7,2,29,NULL,NULL,0,0,'class:messages-add',0,'',18,19,0,'*',1,NULL,NULL),(18,'main','com_finder_index','Smart-Search-Index','','Smart Search/Index','index.php?option=com_finder&view=index','component',1,13,2,23,NULL,NULL,0,0,'class:finder',0,'',30,31,0,'*',1,NULL,NULL),(19,'main','com_finder_maps','Smart-Search-Maps','','Smart Search/Maps','index.php?option=com_finder&view=maps','component',1,13,2,23,NULL,NULL,0,0,'class:finder-maps',0,'',32,33,0,'*',1,NULL,NULL),(20,'main','com_finder_filters','Smart-Search-Filters','','Smart Search/Filters','index.php?option=com_finder&view=filters','component',1,13,2,23,NULL,NULL,0,0,'class:finder-filters',0,'',34,35,0,'*',1,NULL,NULL),(21,'main','com_finder_searches','Smart-Search-Searches','','Smart Search/Searches','index.php?option=com_finder&view=searches','component',1,13,2,23,NULL,NULL,0,0,'class:finder-searches',0,'',36,37,0,'*',1,NULL,NULL),(101,'mainmenu','Home','home','','home','index.php?option=com_content&view=featured','component',1,1,1,19,NULL,NULL,0,1,'',0,'{\"featured_categories\":[\"\"],\"layout_type\":\"blog\",\"blog_class_leading\":\"\",\"blog_class\":\"\",\"num_leading_articles\":\"1\",\"num_intro_articles\":\"3\",\"num_links\":\"0\",\"link_intro_image\":\"\",\"orderby_pri\":\"\",\"orderby_sec\":\"front\",\"order_date\":\"\",\"show_pagination\":\"2\",\"show_pagination_results\":\"1\",\"show_title\":\"\",\"link_titles\":\"\",\"show_intro\":\"\",\"info_block_position\":\"\",\"info_block_show_title\":\"\",\"show_category\":\"\",\"link_category\":\"\",\"show_parent_category\":\"\",\"link_parent_category\":\"\",\"show_associations\":\"\",\"show_author\":\"\",\"link_author\":\"\",\"show_create_date\":\"\",\"show_modify_date\":\"\",\"show_publish_date\":\"\",\"show_item_navigation\":\"\",\"show_vote\":\"\",\"show_readmore\":\"\",\"show_readmore_title\":\"\",\"show_hits\":\"\",\"show_tags\":\"\",\"show_noauth\":\"\",\"show_feed_link\":\"1\",\"feed_summary\":\"\",\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"1\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"robots\":\"\"}',41,42,1,'*',0,NULL,NULL);
/*!40000 ALTER TABLE `jos_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_menu_types`
--

DROP TABLE IF EXISTS `jos_menu_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_menu_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT '0',
  `menutype` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(48) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `client_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_menutype` (`menutype`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_menu_types`
--

LOCK TABLES `jos_menu_types` WRITE;
/*!40000 ALTER TABLE `jos_menu_types` DISABLE KEYS */;
INSERT INTO `jos_menu_types` VALUES (1,0,'mainmenu','Main Menu','The main menu for the site',0);
/*!40000 ALTER TABLE `jos_menu_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_messages`
--

DROP TABLE IF EXISTS `jos_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_messages` (
  `message_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id_from` int unsigned NOT NULL DEFAULT '0',
  `user_id_to` int unsigned NOT NULL DEFAULT '0',
  `folder_id` tinyint unsigned NOT NULL DEFAULT '0',
  `date_time` datetime NOT NULL,
  `state` tinyint NOT NULL DEFAULT '0',
  `priority` tinyint unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `useridto_state` (`user_id_to`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_messages`
--

LOCK TABLES `jos_messages` WRITE;
/*!40000 ALTER TABLE `jos_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_messages_cfg`
--

DROP TABLE IF EXISTS `jos_messages_cfg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_messages_cfg` (
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `cfg_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cfg_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  UNIQUE KEY `idx_user_var_name` (`user_id`,`cfg_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_messages_cfg`
--

LOCK TABLES `jos_messages_cfg` WRITE;
/*!40000 ALTER TABLE `jos_messages_cfg` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_messages_cfg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_modules`
--

DROP TABLE IF EXISTS `jos_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content` text COLLATE utf8mb4_unicode_ci,
  `ordering` int NOT NULL DEFAULT '0',
  `position` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `publish_up` datetime DEFAULT NULL,
  `publish_down` datetime DEFAULT NULL,
  `published` tinyint NOT NULL DEFAULT '0',
  `module` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access` int unsigned NOT NULL DEFAULT '0',
  `showtitle` tinyint unsigned NOT NULL DEFAULT '1',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` tinyint NOT NULL DEFAULT '0',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `published` (`published`,`access`),
  KEY `newsfeeds` (`module`,`published`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_modules`
--

LOCK TABLES `jos_modules` WRITE;
/*!40000 ALTER TABLE `jos_modules` DISABLE KEYS */;
INSERT INTO `jos_modules` VALUES (1,39,'Main Menu','','',1,'sidebar-right',NULL,NULL,NULL,NULL,1,'mod_menu',1,1,'{\"menutype\":\"mainmenu\",\"startLevel\":\"0\",\"endLevel\":\"0\",\"showAllChildren\":\"1\",\"tag_id\":\"\",\"class_sfx\":\"\",\"window_open\":\"\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"1\",\"cache_time\":\"900\",\"cachemode\":\"itemid\"}',0,'*'),(2,40,'Login','','',1,'login',NULL,NULL,NULL,NULL,1,'mod_login',1,1,'',1,'*'),(3,41,'Popular Articles','','',6,'cpanel',NULL,NULL,NULL,NULL,1,'mod_popular',3,1,'{\"count\":\"5\",\"catid\":\"\",\"user_id\":\"0\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\", \"bootstrap_size\": \"12\",\"header_tag\":\"h2\"}',1,'*'),(4,42,'Recently Added Articles','','',4,'cpanel',NULL,NULL,NULL,NULL,1,'mod_latest',3,1,'{\"count\":\"5\",\"ordering\":\"c_dsc\",\"catid\":\"\",\"user_id\":\"0\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\", \"bootstrap_size\": \"12\",\"header_tag\":\"h2\"}',1,'*'),(8,43,'Toolbar','','',1,'toolbar',NULL,NULL,NULL,NULL,1,'mod_toolbar',3,1,'',1,'*'),(9,44,'Notifications','','',3,'icon',NULL,NULL,NULL,NULL,1,'mod_quickicon',3,1,'{\"context\":\"update_quickicon\",\"header_icon\":\"icon-sync\",\"show_jupdate\":\"1\",\"show_eupdate\":\"1\",\"show_oupdate\":\"1\",\"show_privacy\":\"1\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":1,\"cache_time\":900,\"style\":\"0\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\"}',1,'*'),(10,45,'Logged-in Users','','',2,'cpanel',NULL,NULL,NULL,NULL,1,'mod_logged',3,1,'{\"count\":\"5\",\"name\":\"1\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\", \"bootstrap_size\": \"12\",\"header_tag\":\"h2\"}',1,'*'),(12,46,'Admin Menu','','',1,'menu',NULL,NULL,NULL,NULL,1,'mod_menu',3,1,'{\"layout\":\"\",\"moduleclass_sfx\":\"\",\"shownew\":\"1\",\"showhelp\":\"1\",\"cache\":\"0\"}',1,'*'),(15,49,'Title','','',1,'title',NULL,NULL,NULL,NULL,1,'mod_title',3,1,'',1,'*'),(16,50,'Login Form','','',7,'sidebar-right',NULL,NULL,NULL,NULL,1,'mod_login',1,1,'{\"greeting\":\"1\",\"name\":\"0\"}',0,'*'),(17,51,'Breadcrumbs','','',1,'breadcrumbs',NULL,NULL,NULL,NULL,1,'mod_breadcrumbs',1,1,'{\"moduleclass_sfx\":\"\",\"showHome\":\"1\",\"homeText\":\"\",\"showComponent\":\"1\",\"separator\":\"\",\"cache\":\"0\",\"cache_time\":\"0\",\"cachemode\":\"itemid\"}',0,'*'),(79,52,'Multilanguage status','','',2,'status',NULL,NULL,NULL,NULL,1,'mod_multilangstatus',3,1,'{\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\"}',1,'*'),(86,53,'Joomla Version','','',1,'status',NULL,NULL,NULL,NULL,1,'mod_version',3,1,'{\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\"}',1,'*'),(87,55,'Sample Data','','',1,'cpanel',NULL,NULL,NULL,NULL,1,'mod_sampledata',6,1,'{\"bootstrap_size\": \"12\",\"header_tag\":\"h2\"}',1,'*'),(88,67,'Latest Actions','','',3,'cpanel',NULL,NULL,NULL,NULL,1,'mod_latestactions',6,1,'{\"bootstrap_size\": \"12\",\"header_tag\":\"h2\"}',1,'*'),(89,68,'Privacy Dashboard','','',5,'cpanel',NULL,NULL,NULL,NULL,1,'mod_privacy_dashboard',6,1,'{\"bootstrap_size\": \"12\",\"header_tag\":\"h2\"}',1,'*'),(90,89,'Login Support','','',1,'sidebar',NULL,NULL,NULL,NULL,1,'mod_loginsupport',1,1,'{\"forum_url\":\"https://forum.joomla.org/\",\"documentation_url\":\"https://docs.joomla.org/\",\"news_url\":\"https://www.joomla.org/announcements.html\",\"automatic_title\":1,\"prepare_content\":1,\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":1,\"cache_time\":900,\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}',1,'*'),(91,72,'System Dashboard','','',1,'cpanel-system',NULL,NULL,NULL,NULL,1,'mod_submenu',1,0,'{\"menutype\":\"*\",\"preset\":\"system\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\",\"style\":\"System-none\"}',1,'*'),(92,73,'Content Dashboard','','',1,'cpanel-content',NULL,NULL,NULL,NULL,1,'mod_submenu',1,0,'{\"menutype\":\"*\",\"preset\":\"content\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\",\"style\":\"System-none\"}',1,'*'),(93,74,'Menus Dashboard','','',1,'cpanel-menus',NULL,NULL,NULL,NULL,1,'mod_submenu',1,0,'{\"menutype\":\"*\",\"preset\":\"menus\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\",\"style\":\"System-none\"}',1,'*'),(94,75,'Components Dashboard','','',1,'cpanel-components',NULL,NULL,NULL,NULL,1,'mod_submenu',1,0,'{\"menutype\":\"*\",\"preset\":\"components\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\",\"style\":\"System-none\"}',1,'*'),(95,76,'Users Dashboard','','',1,'cpanel-users',NULL,NULL,NULL,NULL,1,'mod_submenu',1,0,'{\"menutype\":\"*\",\"preset\":\"users\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\",\"style\":\"System-none\"}',1,'*'),(96,86,'Popular Articles','','',3,'cpanel-content',NULL,NULL,NULL,NULL,1,'mod_popular',3,1,'{\"count\":\"5\",\"catid\":\"\",\"user_id\":\"0\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\", \"bootstrap_size\": \"12\",\"header_tag\":\"h2\"}',1,'*'),(97,87,'Recently Added Articles','','',4,'cpanel-content',NULL,NULL,NULL,NULL,1,'mod_latest',3,1,'{\"count\":\"5\",\"ordering\":\"c_dsc\",\"catid\":\"\",\"user_id\":\"0\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\", \"bootstrap_size\": \"12\",\"header_tag\":\"h2\"}',1,'*'),(98,88,'Logged-in Users','','',2,'cpanel-users',NULL,NULL,NULL,NULL,1,'mod_logged',3,1,'{\"count\":\"5\",\"name\":\"1\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\", \"bootstrap_size\": \"12\",\"header_tag\":\"h2\"}',1,'*'),(99,77,'Frontend Link','','',5,'status',NULL,NULL,NULL,NULL,1,'mod_frontend',1,1,'',1,'*'),(100,78,'Messages','','',4,'status',NULL,NULL,NULL,NULL,1,'mod_messages',3,1,'',1,'*'),(101,79,'Post Install Messages','','',3,'status',NULL,NULL,NULL,NULL,1,'mod_post_installation_messages',3,1,'',1,'*'),(102,80,'User Status','','',6,'status',NULL,NULL,NULL,NULL,1,'mod_user',3,1,'',1,'*'),(103,70,'Site','','',1,'icon',NULL,NULL,NULL,NULL,1,'mod_quickicon',1,1,'{\"context\":\"site_quickicon\",\"header_icon\":\"icon-desktop\",\"show_users\":\"1\",\"show_articles\":\"1\",\"show_categories\":\"1\",\"show_media\":\"1\",\"show_menuItems\":\"1\",\"show_modules\":\"1\",\"show_plugins\":\"1\",\"show_templates\":\"1\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":1,\"cache_time\":900,\"style\":\"0\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\"}',1,'*'),(104,71,'System','','',2,'icon',NULL,NULL,NULL,NULL,1,'mod_quickicon',1,1,'{\"context\":\"system_quickicon\",\"header_icon\":\"icon-wrench\",\"show_global\":\"1\",\"show_checkin\":\"1\",\"show_cache\":\"1\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":1,\"cache_time\":900,\"style\":\"0\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\"}',1,'*'),(105,82,'3rd Party','','',4,'icon',NULL,NULL,NULL,NULL,1,'mod_quickicon',1,1,'{\"context\":\"mod_quickicon\",\"header_icon\":\"icon-boxes\",\"load_plugins\":\"1\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":1,\"cache_time\":900,\"style\":\"0\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\"}',1,'*'),(106,83,'Help Dashboard','','',1,'cpanel-help',NULL,NULL,NULL,NULL,1,'mod_submenu',1,0,'{\"menutype\":\"*\",\"preset\":\"help\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"style\":\"System-none\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\"}',1,'*'),(107,84,'Privacy Requests','','',1,'cpanel-privacy',NULL,NULL,NULL,NULL,1,'mod_privacy_dashboard',1,1,'{\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":1,\"cache_time\":900,\"cachemode\":\"static\",\"style\":\"0\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\"}',1,'*'),(108,85,'Privacy Status','','',1,'cpanel-privacy',NULL,NULL,NULL,NULL,1,'mod_privacy_status',1,1,'{\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":1,\"cache_time\":900,\"cachemode\":\"static\",\"style\":\"0\",\"module_tag\":\"div\",\"bootstrap_size\":\"12\",\"header_tag\":\"h2\",\"header_class\":\"\"}',1,'*');
/*!40000 ALTER TABLE `jos_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_modules_menu`
--

DROP TABLE IF EXISTS `jos_modules_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_modules_menu` (
  `moduleid` int NOT NULL DEFAULT '0',
  `menuid` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`moduleid`,`menuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_modules_menu`
--

LOCK TABLES `jos_modules_menu` WRITE;
/*!40000 ALTER TABLE `jos_modules_menu` DISABLE KEYS */;
INSERT INTO `jos_modules_menu` VALUES (1,0),(2,0),(3,0),(4,0),(6,0),(7,0),(8,0),(9,0),(10,0),(12,0),(14,0),(15,0),(16,0),(17,0),(79,0),(86,0),(87,0),(88,0),(89,0),(90,0),(91,0),(92,0),(93,0),(94,0),(95,0),(96,0),(97,0),(98,0),(99,0),(100,0),(101,0),(102,0),(103,0),(104,0),(105,0),(106,0),(107,0),(108,0);
/*!40000 ALTER TABLE `jos_modules_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_newsfeeds`
--

DROP TABLE IF EXISTS `jos_newsfeeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_newsfeeds` (
  `catid` int NOT NULL DEFAULT '0',
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `link` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `published` tinyint NOT NULL DEFAULT '0',
  `numarticles` int unsigned NOT NULL DEFAULT '1',
  `cache_time` int unsigned NOT NULL DEFAULT '3600',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` int NOT NULL DEFAULT '0',
  `rtl` tinyint NOT NULL DEFAULT '0',
  `access` int unsigned NOT NULL DEFAULT '0',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT '0',
  `metakey` text COLLATE utf8mb4_unicode_ci,
  `metadesc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `publish_up` datetime DEFAULT NULL,
  `publish_down` datetime DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `hits` int unsigned NOT NULL DEFAULT '0',
  `images` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_newsfeeds`
--

LOCK TABLES `jos_newsfeeds` WRITE;
/*!40000 ALTER TABLE `jos_newsfeeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_newsfeeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_overrider`
--

DROP TABLE IF EXISTS `jos_overrider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_overrider` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `constant` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `string` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_overrider`
--

LOCK TABLES `jos_overrider` WRITE;
/*!40000 ALTER TABLE `jos_overrider` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_overrider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_postinstall_messages`
--

DROP TABLE IF EXISTS `jos_postinstall_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_postinstall_messages` (
  `postinstall_message_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `extension_id` bigint NOT NULL DEFAULT '700' COMMENT 'FK to #__extensions',
  `title_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Lang key for the title',
  `description_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Lang key for description',
  `action_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `language_extension` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'com_postinstall' COMMENT 'Extension holding lang keys',
  `language_client_id` tinyint NOT NULL DEFAULT '1',
  `type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'link' COMMENT 'Message type - message, link, action',
  `action_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'RAD URI to the PHP file containing action method',
  `action` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Action method name or URL',
  `condition_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'RAD URI to file holding display condition method',
  `condition_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Display condition method, must return boolean',
  `version_introduced` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3.2.0' COMMENT 'Version when this message was introduced',
  `enabled` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`postinstall_message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_postinstall_messages`
--

LOCK TABLES `jos_postinstall_messages` WRITE;
/*!40000 ALTER TABLE `jos_postinstall_messages` DISABLE KEYS */;
INSERT INTO `jos_postinstall_messages` VALUES (1,224,'COM_CPANEL_WELCOME_BEGINNERS_TITLE','COM_CPANEL_WELCOME_BEGINNERS_MESSAGE','','com_cpanel',1,'message','','','','','3.2.0',1),(2,224,'COM_CPANEL_MSG_STATS_COLLECTION_TITLE','COM_CPANEL_MSG_STATS_COLLECTION_BODY','','com_cpanel',1,'message','','','admin://components/com_admin/postinstall/statscollection.php','admin_postinstall_statscollection_condition','3.5.0',1),(3,224,'PLG_SYSTEM_UPDATENOTIFICATION_POSTINSTALL_UPDATECACHETIME','PLG_SYSTEM_UPDATENOTIFICATION_POSTINSTALL_UPDATECACHETIME_BODY','PLG_SYSTEM_UPDATENOTIFICATION_POSTINSTALL_UPDATECACHETIME_ACTION','plg_system_updatenotification',1,'action','site://plugins/system/updatenotification/postinstall/updatecachetime.php','updatecachetime_postinstall_action','site://plugins/system/updatenotification/postinstall/updatecachetime.php','updatecachetime_postinstall_condition','3.6.3',1),(4,224,'PLG_SYSTEM_HTTPHEADERS_POSTINSTALL_INTRODUCTION_TITLE','PLG_SYSTEM_HTTPHEADERS_POSTINSTALL_INTRODUCTION_BODY','PLG_SYSTEM_HTTPHEADERS_POSTINSTALL_INTRODUCTION_ACTION','plg_system_httpheaders',1,'action','site://plugins/system/httpheaders/postinstall/introduction.php','httpheaders_postinstall_action','site://plugins/system/httpheaders/postinstall/introduction.php','httpheaders_postinstall_condition','4.0.0',1),(5,224,'COM_USERS_POSTINSTALL_MULTIFACTORAUTH_TITLE','COM_USERS_POSTINSTALL_MULTIFACTORAUTH_BODY','COM_USERS_POSTINSTALL_MULTIFACTORAUTH_ACTION','com_users',1,'action','admin://components/com_users/postinstall/multifactorauth.php','com_users_postinstall_mfa_action','admin://components/com_users/postinstall/multifactorauth.php','com_users_postinstall_mfa_condition','4.2.0',1);
/*!40000 ALTER TABLE `jos_postinstall_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_privacy_consents`
--

DROP TABLE IF EXISTS `jos_privacy_consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_privacy_consents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `state` int NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `remind` tinyint NOT NULL DEFAULT '0',
  `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_privacy_consents`
--

LOCK TABLES `jos_privacy_consents` WRITE;
/*!40000 ALTER TABLE `jos_privacy_consents` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_privacy_consents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_privacy_requests`
--

DROP TABLE IF EXISTS `jos_privacy_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_privacy_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `requested_at` datetime NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `request_type` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `confirm_token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `confirm_token_created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_privacy_requests`
--

LOCK TABLES `jos_privacy_requests` WRITE;
/*!40000 ALTER TABLE `jos_privacy_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_privacy_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_redirect_links`
--

DROP TABLE IF EXISTS `jos_redirect_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_redirect_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `old_url` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referer` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `hits` int unsigned NOT NULL DEFAULT '0',
  `published` tinyint NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `header` smallint NOT NULL DEFAULT '301',
  PRIMARY KEY (`id`),
  KEY `idx_old_url` (`old_url`(100)),
  KEY `idx_link_modified` (`modified_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_redirect_links`
--

LOCK TABLES `jos_redirect_links` WRITE;
/*!40000 ALTER TABLE `jos_redirect_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_redirect_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_scheduler_tasks`
--

DROP TABLE IF EXISTS `jos_scheduler_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_scheduler_tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'unique identifier for job defined by plugin',
  `execution_rules` text COLLATE utf8mb4_unicode_ci COMMENT 'Execution Rules, Unprocessed',
  `cron_rules` text COLLATE utf8mb4_unicode_ci COMMENT 'Processed execution rules, crontab-like JSON form',
  `state` tinyint NOT NULL DEFAULT '0',
  `last_exit_code` int NOT NULL DEFAULT '0' COMMENT 'Exit code when job was last run',
  `last_execution` datetime DEFAULT NULL COMMENT 'Timestamp of last run',
  `next_execution` datetime DEFAULT NULL COMMENT 'Timestamp of next (planned) run, referred for execution on trigger',
  `times_executed` int DEFAULT '0' COMMENT 'Count of successful triggers',
  `times_failed` int DEFAULT '0' COMMENT 'Count of failures',
  `locked` datetime DEFAULT NULL,
  `priority` smallint NOT NULL DEFAULT '0',
  `ordering` int NOT NULL DEFAULT '0' COMMENT 'Configurable list ordering',
  `cli_exclusive` smallint NOT NULL DEFAULT '0' COMMENT 'If 1, the task is only accessible via CLI',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_state` (`state`),
  KEY `idx_last_exit` (`last_exit_code`),
  KEY `idx_next_exec` (`next_execution`),
  KEY `idx_locked` (`locked`),
  KEY `idx_priority` (`priority`),
  KEY `idx_cli_exclusive` (`cli_exclusive`),
  KEY `idx_checked_out` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_scheduler_tasks`
--

LOCK TABLES `jos_scheduler_tasks` WRITE;
/*!40000 ALTER TABLE `jos_scheduler_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_scheduler_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_schemas`
--

DROP TABLE IF EXISTS `jos_schemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_schemas` (
  `extension_id` int NOT NULL,
  `version_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`extension_id`,`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_schemas`
--

LOCK TABLES `jos_schemas` WRITE;
/*!40000 ALTER TABLE `jos_schemas` DISABLE KEYS */;
INSERT INTO `jos_schemas` VALUES (224,'4.2.9-2023-03-07');
/*!40000 ALTER TABLE `jos_schemas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro`
--

DROP TABLE IF EXISTS `jos_securitycheckpro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro` (
                                        `id` int unsigned NOT NULL AUTO_INCREMENT,
                                        `Product` text COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `sc_type` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                        `Installedversion` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '---',
                                        `Vulnerable` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
                                        PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro`
--

LOCK TABLES `jos_securitycheckpro` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_blacklist`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_blacklist` (
                                                  `ip` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
                                                  PRIMARY KEY (`ip`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_blacklist`
--

LOCK TABLES `jos_securitycheckpro_blacklist` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_blacklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_db`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_db`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_db` (
                                           `id` int unsigned NOT NULL AUTO_INCREMENT,
                                           `Product` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `vuln_type` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                           `Vulnerableversion` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '---',
                                           `modvulnversion` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT '==',
                                           `Joomlaversion` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Notdefined',
                                           `modvulnjoomla` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '==',
                                           `description` varchar(90) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                           `vuln_class` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                           `published` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                           `vulnerable` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                           `solution_type` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT '???',
                                           `solution` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                           PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=492 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_db`
--

LOCK TABLES `jos_securitycheckpro_db` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_db` DISABLE KEYS */;
INSERT INTO `jos_securitycheckpro_db` VALUES (1,'Joomla!','core','3.0.0','==','3.0.0','==','Joomla! XSS Vulnerability','Typographical error','Oct 09 2012','Joomla! 3.0.0','update','3.0.1'),(2,'com_fss','component','1.9.1.1447','<=','3.0.0','>=','Joomla Freestyle Support Component','SQL Injection Vulnerability','Oct 19 2012','Versions prior to 1.9.1.1447','none','No details'),(3,'com_commedia','component','3.1','<=','3.0.0','>=','Joomla Commedia Component','SQL Injection Vulnerability','Oct 19 2012','Versions prior to 3.1','update','3.2'),(4,'Joomla!','core','3.0.1','<=','3.0.1','<=','Joomla! Core Clickjacking Vulnerability','Inadequate protection','Nov 08 2012','Joomla! 3.0.1 and 3.0.0 versions','update','3.0.2'),(5,'com_jnews','component','7.9.1','<','3.0.0','>=','Joomla jNews Component','Arbitrary File Creation Vulnerability','Nov 19 2012','Versions prior to 7.9.1','update','7.9.1'),(6,'com_bch','component','---','==','3.0.0','>=','Joomla Bch Component','Shell Upload Vulnerability','Dec 26 2012','Not especificed','none','No details'),(7,'com_aclassif','component','---','==','3.0.0','>=','Joomla Aclassif Component','Cross Site Scripting Vulnerability','Dec 26 2012','Not especificed','none','No details'),(8,'com_rsfiles','component','1.0.0 Rev 11','==','3.0.0','>=','Joomla RSFiles! Component','SQL Injection Vulnerability','Mar 19 2013','Version 1.0.0 Rev 11','update','1.0.0 Rev 12'),(9,'Joomla!','core','3.0.2','<=','3.0.2','<=','Joomla! XSS Vulnerability','Inadequate filtering','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),(10,'Joomla!','core','3.0.2','<=','3.0.2','<=','Joomla! DOS Vulnerability','Object unserialize method','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),(11,'Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),(12,'Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! Information Disclosure Vulnerability','Inadequate permission checking','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),(13,'Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! XSS Vulnerability','Use of old version of Flash-based file uploader','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),(14,'Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! Privilege Escalation Vulnerability','Inadequate permission checking','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),(15,'Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),(16,'com_jnews','component','8.0.1','<=','3.0.0','>=','Joomla Jnews Component','Cross Site Scripting Vulnerability','May 14 2013','Version 8.0.1 an earlier','update','8.1.x'),(17,'com_attachments','component','3.1.1','<','3.0.0','>=','Joomla Com_Attachments Component','Arbitrary File Upload Vulnerability','Jul 09 2013','Versions prior to 3.1.1','update','3.1.1'),(18,'System - Google Maps','plugin','3.1','<','3.0.0','>=','Joomla Googlemaps Plugin','XSS/XML Injection/Path Disclosure/DoS Vulnerabilities','Jul 17 2013','Version 3.1 and maybe above','update','3.1'),(19,'System - Google Maps','plugin','3.2','<=','3.0.0','>=','Joomla Googlemaps Plugin','XSS/DoS Vulnerabilities','Jul 26 2013','Version 3.2','update','3.x'),(20,'Joomla!','core','3.1.4','<=','3.0.0','>=','Joomla! Unauthorised Uploads Vulnerability','Inadequate filtering','Jul 31 2013','Joomla! 3.1.4 and earlier 3.x versions','update','3.1.5'),(21,'com_sectionex','component','2.5.96','<=','3.0.0','>=','Joomla SectionEx Component','SQL Injection Vulnerability','Aug 05 2013','Version 2.5.96 and maybe earlier','update','2.5.104'),(22,'com_joomsport','component','2.0.1','<','3.0.0','>=','Joomla joomsport Component','Multiple Vulnerabilities','Aug 20 2013','Versions prior to 2.0.1','update','2.0.1'),(23,'Joomla!','core','3.1.5','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering','Nov 06 2013','Joomla! 3.1.5 and all earlier 3.x versions','update','3.2'),(24,'Joomla!','core','3.1.5','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering','Nov 06 2013','Joomla! 3.1.5 and all earlier 3.x versions','update','3.2'),(25,'Joomla!','core','3.1.5','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering','Nov 06 2013','Joomla! 3.1.5 and all earlier 3.x versions','update','3.2'),(26,'com_flexicontent','component','2.1.3','<=','3.0.0','>=','Joomla Flexicontent Component','Remote Code Execution Vulnerability','Dec 08 2013','Version 2.1.3 and earlier','none','No details'),(27,'com_mijosearch','component','2.0.1','<=','3.0.0','>=','Joomla MijoSearch Component','Cross Site Scripting/Exposure Vulnerability','Dec 16 2013','Version 2.0.1 and maybe earlier','update','2.0.4'),(28,'com_acesearch','component','3.0','==','3.0.0','>=','Joomla AceSearch Component','Cross Site Scripting Vulnerability','Jan 06 2014','Version 3.0','none','No details'),(29,'com_melody','component','1.6.25','<=','3.0.0','>=','Joomla Melody Component','Cross Site Scripting Vulnerability','Jan 10 2014','Version 1.6.25 and maybe earlier','none','No details'),(30,'com_sexypolling','component','1.0.8','<=','3.0.0','>=','Joomla Sexy Polling Component','SQL Injection Vulnerability','Jan 16 2014','Version 1.0.8 and maybe earlier','update','1.0.9'),(31,'com_komento','component','1.7.2','<=','3.0.0','>=','Joomla Komento Component','Cross Site Scripting Vulnerability','Jan 24 2014','Version 1.7.2 and maybe earlier','update','1.7.4'),(32,'com_community','component','2.6','==','3.0.0','>=','Joomla JomSocial Component','Code Execution Vulnerability','Jan 31 2014','Version 2.6','update','3.1.0'),(33,'Joomla!','core','3.2.2','<=','3.0.0','>=','Joomla! SQL Injection Vulnerability','Inadequate escaping','Mar 06 2014','Joomla! 3.1.0 through 3.2.2','update','3.2.3'),(34,'Joomla!','core','3.2.2','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate escaping','Mar 06 2014','Joomla! 3.1.2 through 3.2.2','update','3.2.3'),(35,'com_youtubegallery','component','3.4.0','==','3.0.0','>=','Joomla Youtube Gallery Component','Cross Site Scripting','Mar 15 2014','Version 3.4.0','update','3.8.3'),(36,'com_pbbooking','component','2.4','==','3.0.0','>=','Joomla Pbbooking Component','Cross Site Scripting','Mar 15 2014','Version 3.4.0','none','No details'),(37,'com_extplorer','component','2.1.3','==','3.0.0','>=','Joomla eXtplorer Component','Cross Site Scripting','Mar 15 2014','Version 2.1.3','update','2.1.5'),(38,'com_freichat','component','3.5','<=','3.0.0','>=','Joomla Freichat Component','Cross Site Scripting','Mar 15 2014','Version 3.4.0','none','No details'),(39,'com_multicalendar','component','4.0.2','==','3.0.0','>=','Joomla Multi Calendar Component','Cross Site Scripting','Mar 15 2014','Version 4.0.2','update','4.8.9'),(40,'com_kunena','component','3.0.4','==','3.0.0','>=','Joomla Kunena Component','Cross Site scripting Vulnerability','Mar 27 2014','Version 3.0.4','update','3.0.5'),(41,'com_jchat','component','2.2','==','3.0.0','>=','Joomla JChatSocial Component','Cross Site scripting Vulnerability','Jul 07 2014','Version 2.2 and maybe lower','update','2.3'),(42,'com_youtubegallery','component','4.1.7','<=','3.0.0','>=','Joomla Youtube Gallery Component','SQL Injection Vulnerability','Jul 17 2014','Version 4.1.7 and maybe lower','update','4.2.0'),(43,'com_kunena','component','3.0.5','==','3.0.0','>=','Joomla Kunena Component','Cross Site scripting Vulnerability','Jul 30 2014','Version 3.0.5','update','3.0.6'),(44,'com_kunena','component','3.0.5','==','3.0.0','>=','Joomla Kunena Component','SQL Injection Vulnerability','Jul 30 2014','Version 3.0.5','update','3.0.6'),(45,'com_spidervideoplayer','component','2.8.3','==','3.0.0','>=','Joomla Spider Video Player Component','SQL Injection Vulnerability','Aug 26 2014','Version 2.8.3','none','No details'),(46,'com_akeeba','component','3.11.4','<','3.0.0','>=','Joomla Akeeba Backup Component','Not specified','Aug 20 2014','Version 3.11.4 and lower','update','3.11.4'),(47,'com_spidercalendar','component','3.2.6','<=','3.0.0','>=','Joomla Spider Calendar Component','SQL Injection Vulnerability','Sept 08 2014','Version 3.2.6 and lower','update','3.2.7'),(48,'com_spidercontacts','component','1.3.6','<=','3.0.0','>=','Joomla Spider Contacts Component','SQL Injection Vulnerability','Sept 10 2014','Version 1.3.6 and lower','update','1.3.7'),(49,'com_formmaker','component','3.4.1','<','3.0.0','>=','Joomla Spider Form Maker Component','SQL Injection Vulnerability','Sept 12 2014','Version 3.4.0 and lower','update','3.4.1'),(50,'com_facegallery','component','1.0','==','3.0.0','>=','Joomla Face Gallery Component','SQL Injection / File Download Vulnerabilities','Sept 22 2014','Version 1.0','none','No details'),(51,'com_macgallery','component','1.5','<=','3.0.0','>=','Joomla Mac Gallery Component','Arbitrary File Download Vulnerability','Sept 22 2014','Version 1.5 and lower','none','No details'),(52,'Joomla!','core','3.3.4','<','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate escaping','Sep 23 2014','Joomla! 3.3.0 through 3.3.3','update','3.3.4'),(53,'Joomla!','core','3.3.4','<','3.0.0','>=','Joomla! Unauthorised Logins Vulnerability','Inadequate checking','Sep 23 2014','Joomla! 3.3.0 through 3.3.4','update','3.3.4'),(54,'Joomla!','core','3.3.4','<=','3.0.0','>=','Joomla! Remote File Inclusion Vulnerability','Inadequate checking','Sep 30 2014','Joomla! 3.3.0 through 3.3.4','update','3.3.5'),(55,'Joomla!','core','3.3.4','<=','3.0.0','>=','Joomla! Denial of service Vulnerability','Inadequate checking','Sep 30 2014','Joomla! 3.3.0 through 3.3.4','update','3.3.5'),(56,'com_creativecontactform','component','2.0.0','<=','3.0.0','>=','Joomla Creative Contact Form Component','Shell Upload Vulnerability','Oct 23 2014','Version 2.0.0 and lower','none','No details'),(57,'com_xcloner-backupandrestore','component','3.5.1','==','3.0.0','>=','Joomla XCloner Component','Command Execution/Password Disclosure Vulnerabilities','Nov 07 2014','Version 3.5.1','update','3.5.2'),(58,'com_hdflvplayer','component','2.1.0.1','==','3.0.0','>=','Joomla HD FLV Component','SQL Injection Vulnerability','Nov 13 2014','Version 2.1.0.1','none','No details'),(59,'mod_simpleemailform','module','1.8.5','<=','3.0.0','>=','Simple Email Form Module','Cross site Scripting Vulnerability','Nov 19 2014','Version 1.8.5 and maybe lower','none','No details'),(60,'com_jclassifiedsmanager','component','2.0.0','<','3.0.0','>=','JClassifiedsManager Component','Cross Site Scripting/SQL Injection Vulnerabilities','Jan 26 2015','Versions prior to 2.0.0','update','2.0.0'),(61,'com_simplephotogallery','component','1.0','==','3.0.0','>=','Simple Photo Gallery Component','SQL Injection Vulnerability','Mar 16 2015','Version 1.0','update','1.1'),(62,'com_ecommercewd','component','1.2.5','==','3.0.0','>=','ECommerce-WD Component','SQL Injection Vulnerabilities','Mar 19 2015','Version 1.2.5','update','1.2.6'),(63,'com_spiderfaq','component','1.1','==','3.0.0','>=','Spider FAQ Component','SQL Injection Vulnerabilities','Mar 22 2015','Version 1.1','none','No details'),(64,'com_rand','component','1.5','==','3.0.0','>=','Spider Random Article Component','SQL Injection Vulnerabilities','Mar 25 2015','Version 1.5','none','No details'),(65,'com_gallery_wd','component','1.2.5','==','3.0.0','>=','Gallery WD Component','SQL Injection Vulnerabilities','Mar 30 2015','Version 1.2.5','none','No details'),(66,'com_contactformmaker','component','1.0.1','==','3.0.0','>=','Form Maker Component','SQL Injection Vulnerabilities','Mar 30 2015','Version 1.0.1','none','No details'),(67,'com_osproperty','component','2.8.0','<=','3.0.0','>=','OSProperty Component','SQL Injection Vulnerability','Apr 29 2015','Versions prior to 2.8.0 ','update','2.8.0'),(68,'com_eqfullevent','component','1.0.0','<=','3.0.0','>=','EQ Event Calendar Component','SQL Injection Vulnerability','Jun 8 2015','Version 1.0.0 and lower ','none','No details'),(69,'Joomla!','core','3.4.1','<=','3.0.0','>=','Joomla! Low level Vulnerability','Not especified','Jul 01 2015','Joomla! 3.x series','update','3.4.2'),(70,'com_kunena','component','4.0.2','==','3.0.0','>=','Joomla Kunena Component','Cross Site scripting Vulnerability','Jul 03 2015','Version 4.0.2','update','4.0.3'),(71,'com_j2store','component','3.1.6','==','2.5.0,3.0.0','>=,>=','Joomla J2Store Component','SQL Injection Vulnerabilities','Jul 11 2015','Version 3.1.6','update','3.1.7'),(72,'com_kunena','component','4.0.3','<=','2.5.0,3.0.0','>=,>=','Joomla Kunena Component','Full Path Disclosure Vulnerability','Jul 17 2015','Version 4.0.3 and lower','none','No details'),(73,'com_helpdeskpro','component','1.4.0','<','2.5.0,3.0.0','>=,>=','Joomla Helpdesk Pro Component','Multiple Vulnerabilities','Jul 21 2015','Version 1.4.0 and lower','update','1.4.0'),(74,'mod_jshopping_products_wfl','module','4.10.4','<=','3.0.0','>=','JoomShopping Module','Blind Sql injection Vulnerability','Aug 09 2015','Version 4.10.4 and lower','none','No details'),(75,'Joomla!','core','3.4.3','<=','3.0.0','>=','Joomla! Cross site scripting Vulnerability','Inadequate checking','Sep 09 2015','Joomla! 3.4 series','update','3.4.4'),(76,'com_komento','component','2.0.5','<','3.0.0','>=','Joomla Komento Component','Cross Site scripting Vulnerability','Oct 04 2015','Version 2.0.4 and lower','update','2.0.5'),(77,'Joomla!','core','3.4.4','<=','3.0.0','>=','Joomla! SQL Injection Vulnerability','Inadequate filtering','Oct 22 2015','Joomla! versions 3.2.0 through 3.4.4','update','3.4.5'),(78,'Joomla!','core','3.4.4','<=','3.0.0','>=','Joomla! ACL violations Vulnerability','Inadequate ACL checks','Oct 22 2015','Joomla! versions 3.2.0 through 3.4.4','update','3.4.5'),(79,'Joomla!','core','3.4.4','<=','3.0.0','>=','Joomla! ACL violations Vulnerability','Inadequate ACL checks','Oct 22 2015','Joomla! versions 3.0.0 through 3.4.4','update','3.4.5'),(80,'com_rpl','component','8.9.2','==','3.0.0','>=','Realtyna RPL Component','Multiple SQL Injection Vulnerabilities','Oct 23 2015','Version 8.9.2','update','8.9.3'),(81,'com_rpl','component','8.9.2','==','3.0.0','>=','Realtyna RPL Component','Persistent XSS And CSRF Vulnerabilities','Oct 23 2015','Version 8.9.2','update','8.9.3'),(82,'com_jnews','component','8.5.1','<=','2.5.0,3.0.0','>=,>=','Joomla JNews Component','Sql injection Vulnerability','Oct 29 2015','Version 8.5.1 and maybe lower','none','No details'),(83,'Joomla!','core','3.4.5','<=','3.0.0','>=','Joomla! Remote Code Execution Vulnerability','Browser information not filtered','Dec 14 2015','Joomla! versions 1.5.0 through 3.4.5','update','3.4.6'),(84,'Joomla!','core','3.4.5','<=','3.0.0','>=','Joomla! CSRF Vulnerability','CSRF','Dec 14 2015','Joomla! versions 3.2.0 through 3.4.5','update','3.4.6'),(85,'Joomla!','core','3.4.5','<=','3.0.0','>=','Joomla! Directory Traversa Vulnerability','Not sanitise input data','Dec 14 2015','Joomla! versions 3.4.0 through 3.4.5','update','3.4.6'),(86,'s5_media_player','plugin','2.0','==','2.5.0,3.0.0','>=,>=','Shape 5 MP3 Player Plugin','Local File Disclosure Vulnerability','Dec 14 2015','Version 2.0','none','No details'),(87,'Joomla!','core','3.4.6','<=','3.0.0','>=','Joomla! Session Hardening','Improved Session handling','Dec 21 2015','Joomla! versions 1.5.0 through 3.4.6','update','3.4.7'),(88,'Joomla!','core','3.4.6','<=','3.0.0','>=','Joomla! SQL Injection Vulnerability','Inadequate filtering of request data','Dec 21 2015','Joomla! versions 3.0.0 through 3.4.6','update','3.4.7'),(89,'fsave','plugin','2.0','==','2.5.0,3.0.0','>=,>=','Fsave Plugin','Local File Disclosure Vulnerability','Jan 20 2016','Version 2.0','none','No details'),(90,'com_pricelist','component','2.3.1','==','3.0.0','>=','Joomla Pricelist Component','Sql injection Vulnerability','Feb 07 2016','Version 2.3.1','update','3.3.0'),(91,'com_poweradmin','component','2.3.0','<=','3.0.0','>=','Joomla JSN PowerAdmin Component','Remote Command Execution Via CSRF and XSS vulnerabilities','Feb 25 2016','Version 2.3.0 and maybe lower','update','2.3.2'),(92,'com_easy_youtube_gallery','component','1.0.2','==','3.0.0','>=','Joomla Easy Youtube Gallery Component','SQL Injection vulnerability','Mar 23 2016','Version 1.0.2','none','No details'),(93,'com_icagenda','component','3.5.15','<=','2.5.0,3.0.0','>=,>=','iCagenda Component','Cross Site Scripting Vulnerability','Mar 23 2015','Version 3.5.5 up to 3.5.15','update','3.5.16'),(94,'mod_news_pro_gk5','module','1.9.3.4','<','2.5.0,3.0.0','>=,>=','News Show Pro GK5 Module','Sql injection Vulnerability','Apr 04 2016','Not specified','update','1.9.3.4'),(95,'com_jem','component','2.1.15','<=','2.5.0,3.0.0','>=,>=','Event Manager Component','Cross Site Scripting Vulnerability','May 14 2016','2.x versions','none','No details'),(96,'com_extplorer','component','2.1.9','==','2.5.0,3.0.0','>=,>=','Extplorer Component','Path traversal Vulnerability','May 16 2016','2.1.9 version','none','No details'),(97,'com_securitycheck','component','2.8.10','<','2.5.0,3.0.0','>=,>=','Securitycheck Component','XSS and Sql Injection Vulnerabilities','May 31 2016','2.8.9 version and lower','update','2.8.10'),(98,'com_securitycheckpro','component','2.8.10','<','2.5.0,3.0.0','>=,>=','Securitycheck Pro Component','XSS and Sql Injection Vulnerabilities','May 31 2016','2.8.9 version and lower','update','2.8.10'),(99,'com_jumi','component','3.0.5','==','2.5.0,3.0.0','>=,>=','Jumi Component','Cross site Scripting Vulnerability','Jun 03 2016','3.0.5','none','No details'),(100,'com_jobgrokapp','component','3.1-1.2.55','==','3.0.0','>=','Joomla JobGrokApp Component','SQL Injection vulnerability','Jun 07 2016','Version 3.1-1.2.55','none','No details'),(101,'com_joomdoc','component','4.0.3','==','2.5.0,3.0.0','>=,>=','JoomDoc Component','Path Disclosure Vulnerability','Jun 08 2016','4.0.3','none','No details'),(102,'com_payplans','component','3.3.6','==','3.0.0','>=','Pay Plans Component','SQL Injection Vulnerability','Jun 14 2016','3.3.6','none','No details'),(103,'com_affiliate','component','1.0.3','==','2.5.0','>=','Affiliate Component','SQL Injection Vulnerability','Jun 14 2016','1.0.3','none','No details'),(104,'com_maqmahelpdesk','component','4.2.3','==','3.0.0','>=','Maqma Helpdesk Component','Cross Site scripting Vulnerability','Jun 14 2016','4.2.3','none','No details'),(105,'com_affiliatetracker','component','2.0.3','==','2.5.0,3.0.0','>=,>=','Affiliate Tracker Component','SQL Injection Vulnerability','Jun 14 2016','2.0.3','none','No details'),(106,'com_enmasse','component','6.4','<=','3.0.0','>=','En-Masse Component','SQL Injection Vulnerability','Jun 15 2016','Versions 5.1 to 6.4','none','No details'),(107,'com_bt_media','component','1.0','==','2.5.0,3.0.0','>=,>=','BT Media Component','SQL Injection Vulnerability','Jun 20 2016','1.0','none','No details'),(108,'com_publisher','component','3.0.11','==','2.5.0,3.0.0','>=,>=','Publisher Component','SQL Injection Vulnerability','Jun 22 2016','3.0.11','none','No details'),(109,'com_services','component','---','==','3.0.0','>=','Services Component','SQL Injection Vulnerability','Jul 12 2016','Not specified','none','No details'),(110,'com_branch','component','3.0','==','3.0.0','>=','Branch Component','SQL Injection Vulnerability','Jul 12 2016','3.0','none','No details'),(111,'com_zhgooglemap','component','8.1.2.0','==','2.5.0,3.0.0','>=,>=','Zh GoogleMap Component','Blind SQL Injection Vulnerability','Jul 15 2016','8.1.2.0','none','No details'),(112,'com_guru','component','5.0.1','<=','2.5.0,3.0.0','>=,>=','Guru Pro Component','SQL Injection Vulnerability','Jul 14 2016','All versions','none','No details'),(113,'com_gallery','component','1.1.5','==','3.0.0','>=','Huge IT Gallery Component','Cross Site scripting/SQL Injection vulnerabilities','Jul 26 2016','1.1.5','update','1.1.6'),(114,'com_catalog','component','1.0.4','==','3.0.0','>=','Huge IT Catalog Component','Cross Site scripting/SQL Injection vulnerabilities','Jul 27 2016','1.0.4','update','1.0.5'),(115,'com_slider','component','1.0.9','==','3.0.0','>=','Huge IT slider Component','Cross Site scripting/SQL Injection vulnerabilities','Jul 28 2016','1.0.9','none','No details'),(116,'Joomla!','core','3.6.0','==','3.0.0','>=','Joomla! CSRF Vulnerability','csrf hardening','Aug 03 2016','Joomla! version 3.6.0','update','3.6.1'),(117,'com_videoflow','component','1.1.5','<=','2.5.0,3.0.0','>=,>=','Video Flow Component','SQL Injection Vulnerability','Aug 06 2016','1.1.3 to 1.1.5','update','1.2.0'),(118,'com_k2','component','2.7.1','<','2.5.0,3.0.0','>=,>=','K2 Component','Cross Site scripting Vulnerability','Aug 06 2016','Versions prior to 2.7.1','update','2.7.1'),(119,'com_registrationpro','component','3.2.12','==','3.0.0','>=','Registration Pro Component','SQL Injection vulnerability','Aug 14 2016','Versions 3.2.10 to 3.2.12','update','3.2.13'),(120,'com_videogallerylite','component','1.0.9','<=','3.0.0','>=','Huge IT Video Gallery Component','SQL Injection vulnerability','Sep 23 2016','1.0.9','update','1.1.0'),(121,'com_eventbooking','component','2.10.1','==','3.0.0','>=','Event Booking Component','SQL Injection vulnerability','Sep 25 2016','2.10.1','update','2.10.2'),(122,'com_videogallerylite','component','1.1.1','<=','3.0.0','>=','Huge IT Video Gallery Component','Cross Site Scripting vulnerability','Oct 01 2016','1.0.9','none','No details'),(123,'com_catalog','component','1.0.7','==','3.0.0','>=','Huge IT Catalog Component','SQL Injection vulnerabilities','Sep 30 2016','1.0.7','none','No details'),(124,'com_portfoliogallery','component','1.0.6','==','3.0.0','>=','Huge IT Portfolio Gallery Component','SQL Injection vulnerabilities','Oct 01 2016','1.0.6','none','No details'),(125,'mod_dvfoldercontent','module','1.0.2','==','3.0.0','>=','DVFolderContent Module','Local File Disclosure vulnerabilities','Oct 01 2016','1.0.2','none','No details'),(126,'com_googlemaps','component','1.0.9','==','3.0.0','>=','Huge IT Googlemaps Component','SQL Injection vulnerabilities','Oct 01 2016','1.0.9','none','No details'),(127,'Joomla!','core','3.6.3','<=','3.0.0','>=','Joomla! Account Creation','Inadequate checks','Oct 25 2016','Joomla! versions 3.4.4 through 3.6.3','update','3.6.4'),(128,'Joomla!','core','3.6.3','<=','3.0.0','>=','Joomla! Elevated Privileges','Incorrect use of unfiltered data','Oct 25 2016','Joomla! versions 3.4.4 through 3.6.3','update','3.6.4'),(129,'Joomla!','core','3.6.3','<=','3.0.0','>=','Joomla! Account Modifications','Incorrect use of unfiltered data','Oct 26 2016','Joomla! versions 3.4.4 through 3.6.3','update','3.6.4'),(130,'com_kunena','component','5.0.3','<=','3.0.0','>=','Joomla Kunena Component','File upload Vulnerability','Nov 26 2016','Version 4.0.0 to 5.0.3','update','5.0.4'),(131,'Joomla!','core','3.6.4','<=','3.0.0','>=','Joomla! Elevated Privileges','Incorrect use of unfiltered data','Dec 13 2016','Joomla! versions 1.6.0 to 3.6.4','update','3.6.5'),(132,'Joomla!','core','3.6.4','<=','3.0.0','>=','Joomla! Shell upload','Incorrect filesystem checks','Dec 13 2016','Joomla! versions 3.0.0 to 3.6.4','update','3.6.5'),(133,'Joomla!','core','3.6.4','<=','3.0.0','>=','Joomla! Information disclosure','Incorrect ACL checks','Dec 13 2016','Joomla! versions 3.0.0 to 3.6.4','update','3.6.5'),(134,'com_dtregister','component','3.1.12','<','3.0.0','>=','DT Register Component','SQL Injection Vulnerability','Dec 16 2016','Versions prior to 3.1.12','update','3.1.12'),(135,'com_rpl','component','8.9.2','==','3.0.0','>=','RPL Component','SQL Injection Vulnerability','Dec 21 2016','Version 8.9.2','update','8.9.5'),(136,'com_kunena','component','5.0.5','<','3.0.0','>=','Joomla Kunena Component','XSS Vulnerability','Jan 04 2017','Version 5.0.2 to 5.0.5','update','5.0.5'),(137,'com_rsmonials','component','2.2','<=','3.0.0','>=','Joomla RsMonials Component','XSS Vulnerability','Jan 11 2017','Version 2.2 and previous','none','No details'),(138,'com_storelocator','component','2.3.1.0','==','3.0.0','>=','Joomla Store Locator Component','Cross site scripting Vulnerability','Feb 01 2017','Version 2.3.1.0','none','No details'),(139,'com_jtagcalendar','component','6.2.4','==','3.0.0','>=','Joomla JTAG Calendar Component','SQL Injection Vulnerability','Feb 01 2017','Version 6.2.4','none','No details'),(140,'com_joominaflileselling','component','2.2','==','3.0.0','>=','Joomla Joominaflileselling Component','SQL Injection Vulnerability','Feb 14 2017','Version 2.2','none','No details'),(141,'com_soccerbet','component','4.1.5','==','3.0.0','>=','Joomla Soccer Bet Component','SQL Injection Vulnerability','Feb 14 2017','Version 4.1.5','none','No details'),(142,'com_vikbooking','component','1.7','==','3.0.0','>=','Joomla Vik Booking Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.7','none','No details'),(143,'com_onisquotes','component','2.5','==','3.0.0','>=','Joomla Onisquotes Component','SQL Injection Vulnerability','Feb 14 2017','Version 2.5','none','No details'),(144,'com_sponsorwall','component','7.0','==','3.0.0','>=','Joomla Sponsor Wall Component','SQL Injection Vulnerability','Feb 14 2017','Version 7.0','none','No details'),(145,'com_onispetitions','component','2.5','==','3.0.0','>=','Joomla onisPetitions Component','SQL Injection Vulnerability','Feb 14 2017','Version 2.5','none','No details'),(146,'com_onismusic','component','2','==','3.0.0','>=','Joomla onisMusic Component','SQL Injection Vulnerability','Feb 14 2017','Version 2','none','No details'),(147,'com_jemessenger','component','1.2.2','==','3.0.0','>=','Joomla JE Messenger Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.2.2','none','No details'),(148,'com_jequoteform','component','1.4','==','3.0.0','>=','Joomla JE QuoteForm Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.4','none','No details'),(149,'com_jegridfolio','component','---','==','3.0.0','>=','Joomla JE Grid Folio Component','SQL Injection Vulnerability','Feb 14 2017','Version not specified','none','No details'),(150,'com_jeticket','component','1.2','==','3.0.0','>=','Joomla JE Ticket System Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.2','none','No details'),(151,'com_jeportfolio','component','1.2','==','3.0.0','>=','Joomla JE Portfolio Creator Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.2','none','No details'),(152,'com_jeformcr','component','1.8','==','3.0.0','>=','Joomla JE Form Creator Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.8','none','No details'),(153,'com_jeshop','component','1.3','==','3.0.0','>=','Joomla JE Shop Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.3','none','No details'),(154,'com_jeclassifyads','component','1.2','==','3.0.0','>=','Joomla JE Classify Ads Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.2','none','No details'),(155,'com_jegallery','component','1.3','==','3.0.0','>=','Joomla JE Gallery Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.3','none','No details'),(156,'com_jedirectory','component','1.7','==','3.0.0','>=','Joomla JE Directory Ads Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.7','none','No details'),(157,'com_jepropertyfinder','component','1.6.3','==','3.0.0','>=','Joomla JE Property Finder Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.6.3','none','No details'),(158,'com_jequizmanagement','component','2.3','==','3.0.0','>=','Joomla JE Quiz Component','SQL Injection Vulnerability','Feb 14 2017','Version 2.3','none','No details'),(159,'com_hbooking','component','1.9.9','==','3.0.0','>=','Joomla Hbooking Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.9.9','none','No details'),(160,'com_jetour','component','2.0','==','3.0.0','>=','Joomla JE Tour Component','SQL Injection Vulnerability','Feb 14 2017','Version 2.0','none','No details'),(161,'com_jevideorate','component','1.0','==','3.0.0','>=','Joomla JE Video Rate Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.0','none','No details'),(162,'com_jeauction','component','1.6','==','3.0.0','>=','Joomla JE Auction Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.6','none','No details'),(163,'com_jeauto','component','1.5','==','3.0.0','>=','Joomla JE Auto Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.5','none','No details'),(164,'com_jeawdsong','component','1.8','==','3.0.0','>=','Joomla JE Awd Song Component','SQL Injection Vulnerability','Feb 14 2017','Version 1.8','none','No details'),(165,'com_geocontent','component','4.5','==','3.0.0','>=','Joomla Geocontent Component','Cross site scripting Vulnerability','Feb 14 2017','Version 4.5','none','No details'),(166,'com_fastball','component','3.2.8','==','3.0.0','>=','Joomla Fastball Component','SQL Injection Vulnerability','Feb 14 2017','Version 3.2.8','none','No details'),(167,'com_gameserver','component','3.4','==','3.0.0','>=','Joomla Gameserver! Component','SQL Injection Vulnerability','Feb 14 2017','Version 3.4','none','No details'),(168,'com_muscol','component','3.0.3','==','3.0.0','>=','Joomla Music Collection Component','SQL Injection Vulnerability','Feb 14 2017','Version 3.0.3','none','No details'),(169,'com_jsplocation','component','2.2','==','3.0.0','>=','Joomla JSP Store Locator Component','SQL Injection Vulnerability','Feb 20 2017','Version 2.2','none','No details'),(170,'com_spiderfacebook','component','1.6.1','==','3.0.0','>=','Joomla Spider Facebook Component','SQL Injection Vulnerability','Feb 20 2017','Version 1.6.1','none','No details'),(171,'com_spiderfaq','component','1.3.1','==','3.0.0','>=','Joomla Spider FAQ Lite Component','SQL Injection Vulnerability','Feb 20 2017','Version 1.3.1','none','No details'),(172,'com_jembedall','component','1.4','==','3.0.0','>=','Joomla JEmbedAll Component','SQL Injection Vulnerability','Feb 20 2017','Version 1.4','none','No details'),(173,'com_spidercatalog','component','1.8.10','==','3.0.0','>=','Joomla Spider Catalog Lite Component','SQL Injection Vulnerability','Feb 20 2017','Version 1.8.10','none','No details'),(174,'com_joomblog','component','1.3.1','==','3.0.0','>=','Joomla Joomblog Component','SQL Injection Vulnerability','Feb 20 2017','Version 1.3.1','none','No details'),(175,'com_wmt_content_timeline','component','1.0','==','3.0.0','>=','Joomla WMT Content Timeline Component','SQL Injection Vulnerability','Feb 20 2017','Version 1.0','none','No details'),(176,'com_groovygallery','component','1.0.0','==','3.0.0','>=','Joomla Groovy Gallery Component','SQL Injection Vulnerability','Feb 20 2017','Version 1.0.0','none','No details'),(177,'com_spidercalendar','component','3.2.16','==','3.0.0','>=','Joomla Spider Calendar Lite Component','SQL Injection Vulnerability','Feb 20 2017','Version 3.2.16','none','No details'),(178,'com_teamdisplay','component','1.2.1','==','3.0.0','>=','Joomla Team Display Component','SQL Injection Vulnerability','Feb 20 2017','Version 1.2.1','none','No details'),(179,'com_anief','component','1.5','==','3.0.0','>=','Joomla Anief Component','SQL Injection Vulnerability','Feb 21 2017','Version 1.5','none','No details'),(180,'com_djcatalog2','component','1.5','==','3.0.0','>=','Joomla DjCatalog2 Component','SQL Injection Vulnerability','Feb 21 2017','Version 1.5','none','No details'),(181,'com_mostwantedrealestate','component','1.1.0','==','3.0.0','>=','Joomla Most Wanted Real Estate Component','SQL Injection Vulnerability','Feb 22 2017','Version 1.1.0','none','No details'),(182,'com_googlemaplocator','component','4.4','==','3.0.0','>=','Joomla Google Map Store Locator Component','SQL Injection Vulnerability','Feb 22 2017','Version 4.4','none','No details'),(183,'com_joomloc','component','4.1.3','==','3.0.0','>=','Joomla Joomloc-CAT Component','SQL Injection Vulnerability','Feb 22 2017','Version 4.1.3','none','No details'),(184,'com_joomloclite','component','1.3.2','==','3.0.0','>=','Joomla Most Wanted Real Estate Component','SQL Injection Vulnerability','Feb 22 2017','Version 1.3.2','none','No details'),(185,'com_awdwall','component','4.0','==','3.0.0','>=','Joomla JoomWALL Component','SQL Injection Vulnerability','Feb 22 2017','Version 4.0','none','No details'),(186,'com_osproperty','component','3.0.8','==','3.0.0','>=','Joomla OS Property Component','SQL Injection Vulnerability','Feb 22 2017','Version 3.0.8','none','No details'),(187,'com_bazaar','component','3.0','==','3.0.0','>=','Joomla Bazaar Platform Component','SQL Injection Vulnerability','Feb 22 2017','Version 3.0','none','No details'),(188,'com_viewcontent','component','1.6','==','3.0.0','>=','Joomla View Content Component','SQL Injection Vulnerability','Feb 22 2017','Version 1.6','none','No details'),(189,'com_docman','component','1.6','==','3.0.0','>=','Joomla Docman Component','SQL Injection Vulnerability','Feb 22 2017','Version 1.6','none','No details'),(190,'com_dcrc','component','1.6','==','3.0.0','>=','Joomla DCRC Component','SQL Injection Vulnerability','Feb 22 2017','Version 1.6','none','No details'),(191,'com_topics','component','1.6','==','3.0.0','>=','Joomla Topics Component','SQL Injection Vulnerability','Feb 22 2017','Version 1.6','none','No details'),(192,'com_maqmahelpdesk','component','4.2.7','==','3.0.0','>=','Joomla MaQma Helpdesk Component','SQL Injection Vulnerability','Feb 22 2017','Version 4.2.7','none','No details'),(193,'com_maxcomment','component','1.6','==','3.0.0','>=','Joomla Maxcomment Component','SQL Injection Vulnerability','Feb 22 2017','Version 1.6','none','No details'),(194,'com_eshop','component','2.5.1','==','3.0.0','>=','Joomla Eshop Component','SQL Injection Vulnerability','Feb 22 2017','Version 2.5.1','none','No details'),(195,'com_docmanpaypal','component','3.1','==','3.0.0','>=','Joomla PayPal IPN for DOCman Component','SQL Injection Vulnerability','Feb 22 2017','Version 3.1','none','No details'),(196,'com_jhotelreservation','component','6.0.2','==','3.0.0','>=','Joomla J-HotelPortal Component','SQL Injection Vulnerability','Feb 24 2017','Version 6.0.2','none','No details'),(197,'com_rsgallery2','component','1.6','==','3.0.0','>=','Joomla RSGallery2 Component','SQL Injection Vulnerability','Feb 24 2017','Version 1.6','none','No details'),(198,'com_contentmap','component','1.3.8','==','3.0.0','>=','Joomla ContentMap Component','SQL Injection Vulnerability','Feb 24 2017','Version 1.3.8','none','No details'),(199,'com_jbusinessdirectory','component','4.6.8','==','3.0.0','>=','Joomla J-BusinessDirectory Component','SQL Injection Vulnerability','Feb 24 2017','Version 4.6.8','none','No details'),(200,'com_eventix','component','1.0','==','3.0.0','>=','Joomla Eventix Events Calendar Component','SQL Injection Vulnerability','Feb 24 2017','Version 1.0','none','No details'),(201,'com_booklibrary','component','3.6.1','==','3.0.0','>=','Joomla BookLibrary Component','SQL Injection Vulnerability','Feb 24 2017','Version 3.6.1','none','No details'),(202,'com_rsappt_pro3','component','4.0.1','==','3.0.0','>=','Joomla AppointmentBookingPro Component','SQL Injection Vulnerability','Feb 24 2017','Version 4.0.1','none','No details'),(203,'com_jhotelreservation','component','6.0.2','==','3.0.0','>=','Joomla J-MultipleHotelReservation Standard Component','SQL Injection Vulnerability','Feb 24 2017','Version 6.0.2','none','No details'),(204,'com_directorix','component','1.1.1','==','3.0.0','>=','Joomla Directorix Directory Manager Component','SQL Injection Vulnerability','Feb 24 2017','Version 1.1.1','none','No details'),(205,'com_jcruisereservation','component','3.0','==','3.0.0','>=','Joomla J-CruiseReservation Standard Component','SQL Injection Vulnerability','Feb 24 2017','Version 3.0','none','No details'),(206,'com_realestatemanager','component','3.9','==','3.0.0','>=','Joomla RealEstateManager Component','SQL Injection Vulnerability','Feb 24 2017','Version 3.9','none','No details'),(207,'com_vehiclemanager','component','3.9','==','3.0.0','>=','Joomla VehicleManager Component','SQL Injection Vulnerability','Feb 24 2017','Version 3.9','none','No details'),(208,'com_magicdealsweb','component','1.2.0','==','3.0.0','>=','Joomla Magic Deals Web Component','SQL Injection Vulnerability','Feb 24 2017','Version 1.2.0','none','No details'),(209,'com_booklibrary','component','3.5','==','3.0.0','>=','Joomla MediaLibrary Basic Component','SQL Injection Vulnerability','Feb 24 2017','Version 3.5','none','No details'),(210,'com_userextranet','component','1.3.1','==','3.0.0','>=','Joomla UserExtranet Component','SQL Injection Vulnerability','Feb 24 2017','Version 1.3.1','none','No details'),(211,'com_multitier','component','3.1','==','3.0.0','>=','Joomla MultiTier Component','SQL Injection Vulnerability','Feb 24 2017','Version 3.1','none','No details'),(212,'com_k2store','component','3.8.2','==','3.0.0','>=','Joomla Store for K2 Component','SQL Injection Vulnerability','Feb 24 2017','Version 3.8.2','none','No details'),(213,'com_redshop','component','1.5','==','3.0.0','>=','Joomla RedShop Component','SQL Injection Vulnerability','Feb 24 2017','Version 1.5','none','No details'),(214,'com_jajobboard','component','1.5','==','3.0.0','>=','Joomla JaJobBoard Component','SQL Injection Vulnerability','Feb 24 2017','Version 1.5','none','No details'),(215,'com_securitycheck','component','2.8.18','<','3.0.0','>=','Joomla Securitycheck Component','XSS Vulnerability','Jan 24 2017','Version 2.8.18 and lower','update','2.8.18'),(216,'com_securitycheckpro','component','2.8.18','<','3.0.0','>=','Joomla Securitycheck Pro Component','XSS Vulnerability','Jan 24 2017','Version 2.8.18 and lower','update','2.8.18'),(217,'com_rsmonials','component','2.2','<=','3.0.0','>=','Joomla Rsmonials Component','XSS Vulnerability','Jan 11 2017','Version 2.2 and lower','none','No details'),(218,'com_altauserpoints','component','1.1','==','3.0.0','>=','Joomla AltaUserpoints Component','SQL Injection Vulnerability','Mar 05 2017','Version 1.1','none','No details'),(219,'com_os_cck','component','1.1','==','3.0.0','>=','Joomla Content ConstructionKit Component','SQL Injection Vulnerability','Mar 05 2017','Version 1.1','none','No details'),(220,'com_aysquiz','component','1.0','==','3.0.0','>=','Joomla AYS Quiz Component','SQL Injection Vulnerability','Mar 05 2017','Version 1.0','none','No details'),(221,'com_monthlyarchive','component','3.6.4','==','3.0.0','>=','Joomla Monthly archive Component','SQL Injection Vulnerability','Mar 05 2017','Version 3.6.4','none','No details'),(222,'com_jux_eventon','component','1.0.1','==','3.0.0','>=','Joomla JUX EventOn Component','SQL Injection Vulnerability','Mar 05 2017','Version 1.0.1','none','No details'),(223,'com_product','component','2.2','==','3.0.0','>=','Joomla com_product Component','SQL Injection Vulnerability','Mar 10 2017','Version 2.2','none','No details'),(224,'com_advertisementboard','component','3.0.4','==','3.0.0','>=','Joomla Advertisement Board Component','SQL Injection Vulnerability','Mar 14 2017','Version 3.0.4','none','No details'),(225,'com_simplemembership','component','3.3.3','==','3.0.0','>=','Joomla Simple Membership Component','SQL Injection Vulnerability','Mar 14 2017','Version 3.3.3','none','No details'),(226,'com_alfcontact','component','3.2.3','==','3.0.0','>=','Joomla ALFContact Component','SQL Injection Vulnerability','Mar 14 2017','Version 3.2.3','none','No details'),(227,'com_vikrentcar','component','1.11','==','3.0.0','>=','Joomla Vik Rent Car Component','SQL Injection Vulnerability','Mar 15 2017','Version 1.11','none','No details'),(228,'com_vikrentitems','component','1.3','==','3.0.0','>=','Joomla Vik Rent Items Component','SQL Injection Vulnerability','Mar 15 2017','Version 1.3','none','No details'),(229,'com_vikappointments','component','1.5','==','3.0.0','>=','Joomla Vik Rent Appointments Component','SQL Injection Vulnerability','Mar 15 2017','Version 1.5','none','No details'),(230,'com_jcart','component','2.0','==','3.0.0','>=','Joomla jCart for Opencart Component','SQL Injection Vulnerability','Mar 20 2017','Version 2.0','none','No details'),(231,'com_opencart','component','2.0','==','3.0.0','>=','Joomla JooCart Component','SQL Injection Vulnerability','Mar 20 2017','Version 2.0','none','No details'),(232,'com_extrasearch','component','2.2.8','==','3.0.0','>=','Joomla Extra search Component','SQL Injection Vulnerability','Mar 21 2017','Version 2.2.8','none','No details'),(233,'com_modern_booking','component','1.0','==','3.0.0','>=','Joomla Modern Booking Component','SQL Injection Vulnerability','Mar 23 2017','Version 1.0','none','No details'),(234,'com_focalpoint','component','1.2.3','==','3.0.0','>=','Joomla Focal Point Component','SQL Injection Vulnerability','Mar 24 2017','Version 1.2.3','none','No details'),(235,'com_jobgroklist','component','3.1-1.2.58','==','3.0.0','>=','Joomla JobGrok Listing Component','SQL Injection Vulnerability','Apr 03 2017','Version 3.1-1.2.58','none','No details'),(236,'com_jobgrokapp','component','3.1-1.2.55','==','3.0.0','>=','Joomla JobGrok Application Component','SQL Injection Vulnerability','Apr 03 2017','Version 3.1-1.2.55','none','No details'),(237,'com_joomloclite','component','1.3.3','==','3.0.0','>=','Joomla Joomloc-Lite Component','SQL Injection Vulnerability','Mar 13 2017','Version 1.3.3','update','1.4.1'),(238,'com_joomloc','component','4.1.3','==','3.0.0','>=','Joomla Joomloc-CAT Component','SQL Injection Vulnerability','Mar 13 2017','Version 4.1.3','update','4.2.1'),(239,'com_osproperty','component','3.0.9','==','3.0.0','>=','Joomla OS Property Component','SQL Injection Vulnerability','Mar 14 2017','Version 3.0.9','update','3.10.0'),(240,'com_booklibrary','component','3.6.14','==','3.0.0','>=','Joomla BookLibrary Component','SQL Injection Vulnerability','Mar 17 2017','Version 3.6.14','update','3.6.15'),(241,'com_vehiclemanager','component','3.9.4','==','3.0.0','>=','Joomla Vehicle manager Component','SQL Injection Vulnerability','Mar 17 2017','Version 3.9.4','update','3.9.5'),(242,'mod_repowa','module','1.0','==','3.0.0','>=','Joomla Responsive Portfolio Wall Module','XSS Vulnerability Vulnerability','Mar 18 2017','Version 1.0','update','1.1'),(243,'mod_ap_portfolio','module','3.3.1','<=','3.0.0','>=','Joomla AP Portfolio Wall Module','XSS Vulnerability Vulnerability','Mar 18 2017','Version 3.3.1 and lower','update','3.3.2'),(244,'com_realstatemanager','component','3.9.7','==','3.0.0','>=','Joomla Real Estate manager Component','SQL Injection Vulnerability','Mar 22 2017','Version 3.9.7','update','3.9.8'),(245,'com_booklibrary','component','3.5.4','==','3.0.0','>=','Joomla MeadiaLibrary Component','SQL Injection Vulnerability','Mar 22 2017','Version 3.5.4','update','3.5.5'),(246,'com_rsappt-pro','component','4.0.1','==','3.0.0','>=','Joomla AppointmentBookingPro Component','SQL Injection Vulnerability','Mar 22 2017','Version 4.0.1','update','4.0.2'),(247,'com_modern_booking','component','1.0','==','3.0.0','>=','Joomla Modern Booking Component','SQL Injection Vulnerability','Apr 09 2017','Version 1.0','none','No details'),(248,'com_directorix','component','1.1.1','==','3.0.0','>=','Joomla Directorix Directory Manager Component','SQL Injection Vulnerability','Mar 20 2017','Version 1.1.1','none','No details'),(249,'com_jcruisereservation','component','3.0','==','3.0.0','>=','Joomla J-CruiseReservation Standard Component','SQL Injection Vulnerability','Mar 15 2017','Version 3.0','none','No details'),(250,'com_mostwantedrealestate','component','1.1.0','==','3.0.0','>=','Joomla Most Wanted Real Estate Component','SQL Injection Vulnerability','Mar 15 2017','Version 1.1.0','none','No details'),(251,'com_googlemaplocator','component','3.0','==','3.0.0','>=','Joomla Google Map Store Locator Component','SQL Injection Vulnerability','Mar 13 2017','Version 3.0','none','No details'),(252,'com_docmanpaypal','component','3.1','==','3.0.0','>=','Joomla PayPal IPN for DOCman Component','SQL Injection Vulnerability','Mar 13 2017','Version 3.1','none','No details'),(253,'com_eventix','component','1.0','==','3.0.0','>=','Joomla Eventix Events Calendar Component','SQL Injection Vulnerability','Mar 13 2017','Version 1.0','none','No details'),(254,'com_magicdealsweb','component','1.2.0','==','3.0.0','>=','Joomla Magic Deals Web Component','SQL Injection Vulnerability','Mar 13 2017','Version 1.2.0','none','No details'),(255,'com_multitier','component','3.1','==','3.0.0','>=','Joomla Multitier Component','SQL Injection Vulnerability','Mar 08 2017','Version 3.1','none','No details'),(256,'com_userextranet','component','1.3.1','==','3.0.0','>=','Joomla UserExtranet Component','SQL Injection Vulnerability','Mar 08 2017','Version 1.3.1','none','No details'),(257,'com_guesser','component','1.0.4','==','3.0.0','>=','Joomla Guesser Component','SQL Injection Vulnerability','Mar 06 2017','Version 1.0.4','none','No details'),(258,'com_recipe','component','2.2','==','3.0.0','>=','Joomla Recipe Manager Component','SQL Injection Vulnerability','Mar 06 2017','Version 2.2','none','No details'),(259,'com_abstract','component','2.1','==','3.0.0','>=','Joomla Abstract Manager Component','SQL Injection Vulnerability','Mar 06 2017','Version 2.1','none','No details'),(260,'com_k2ajaxsearch','component','2.2','==','3.0.0','>=','Joomla AJAX Search for K2 Component','SQL Injection Vulnerability','Mar 06 2017','Version 2.2','none','No details'),(261,'com_jegridfolio','component','---','==','3.0.0','>=','Joomla JE Grid Folio Component','SQL Injection Vulnerability','Feb 20 2017','Version not specified','none','No details'),(262,'com_jepropertyfinder','component','1.6.3','==','3.0.0','>=','Joomla Property Finder Component','SQL Injection Vulnerability','Feb 20 2017','Version 1.6.3','none','No details'),(263,'Joomla!','core','3.6.5','<=','3.0.0','>=','Joomla! Information disclosure','Inadequate filtering','Apr 25 2017','Joomla! versions 1.5.0 through 3.6.5','update','3.7.0'),(264,'Joomla!','core','3.6.5','<=','3.0.0','>=','Joomla! XSS vulnerability','Mail sent using the JMail API leaked','Apr 25 2017','Joomla! versions 3.2.0 through 3.6.5','update','3.7.0'),(265,'Joomla!','core','3.6.5','<=','3.0.0','>=','Joomla! XSS vulnerability','Inadequate filtering','Apr 25 2017','Joomla! versions 3.2.0 through 3.6.5','update','3.7.0'),(266,'Joomla!','core','3.6.5','<=','3.0.0','>=','Joomla! XSS vulnerability','Inadequate filtering','Apr 25 2017','Joomla! versions 1.5.0 through 3.6.5','update','3.7.0'),(267,'Joomla!','core','3.6.5','<=','3.0.0','>=','Joomla! XSS vulnerability','Inadequate escaping','Apr 25 2017','Joomla! versions 3.2.0 through 3.6.5','update','3.7.0'),(268,'Joomla!','core','3.6.5','<=','3.0.0','>=','Joomla! ACL violation','Inadequate filtering','Apr 25 2017','Joomla! versions 1.6.0 through 3.6.5','update','3.7.0'),(269,'Joomla!','core','3.6.5','<=','3.0.0','>=','Joomla! ACL violation','Inadequate mime type checks','Apr 25 2017','Joomla! versions 3.2.0 through 3.6.5','update','3.7.0'),(270,'Joomla!','core','3.6.5','<=','3.0.0','>=','Joomla! Information disclosures','Multiple files caused full path disclosures','Apr 25 2017','Joomla! versions 3.4.0 through 3.6.5','update','3.7.0'),(271,'com_jdbexport','component','3.2.10','==','3.0.0','>=','Joomla jDBexport Component','Cross Site Scripting/Path Disclosure Vulnerabilities','Apr 28 2017','Version 3.2.10','none','No details'),(272,'com_myportfolio','component','3.0.2','==','3.0.0','>=','Joomla MyPortfolio Component','SQL Injection Vulnerability','Apr 28 2017','Version 3.0.2','none','No details'),(273,'com_jgrid','component','4.44','==','3.0.0','>=','Joomla JGrid Component','SQL Injection Vulnerability','May 02 2017','Version 4.44','none','No details'),(274,'Joomla!','core','3.7.0','==','3.0.0','>=','Joomla! SQL Injection Vulnerability','Inadequate filtering','May 17 2017','Joomla! versions 3.7.0','update','3.7.1'),(275,'com_videoflow','component','1.2.0','==','3.0.0','>=','Joomla VideoFlow Component','SQL Injection Vulnerability','May 24 2017','Version 1.2.0','none','No details'),(276,'com_kunena','component','5.0.9','<','3.0.0','>=','Joomla Kunena Component','Cross Site scripting Vulnerability','May 24 2017','Version 4.0.0 through 5.0.8','update','5.0.9'),(277,'com_payage','component','2.0.6','<','3.0.0','>=','Joomla Payage Component','SQL Injection Vulnerability','Jun 22 2017','Version 2.0.5 and lower','update','2.0.6'),(278,'com_hikashop','component','3.1.0','==','3.0.0','>=','Joomla Hikashop Business Component','SQL Injection Vulnerability','Jun 22 2017','Version 3.1.0','update','3.1.1'),(279,'Joomla!','core','3.7.2','<=','3.0.0','>=','Joomla! Information disclosure Vulnerability','Inadequate filtering','Jul 04 2017','versions 1.7.3-3.7.2','update','3.7.3'),(280,'Joomla!','core','3.7.2','<=','3.0.0','>=','Joomla! XSS Vulnerability','Missing CSRF token checks and improper input validation','Jul 04 2017','versions 1.7.3-3.7.2','update','3.7.3'),(281,'Joomla!','core','3.6.5','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering of multibyte characters','Jul 04 2017','versions 1.5.0 through 3.6.5','update','3.7.3'),(282,'Joomla!','core','3.7.3','<=','3.0.0','>=','Joomla! core','Lack of Ownership Verification','Jul 25 2017','versions 1.0.0 through 3.7.3','update','3.7.4'),(283,'Joomla!','core','3.7.3','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering of potentially malicious HTML tags','Jul 25 2017','versions 1.5.0 through 3.7.3','update','3.7.4'),(284,'com_ccnewsletter','component','2.1.9','==','3.0.0','>=','CCNewsLetter Component','SQL Injection Vulnerability','Aug 01 2017','Version 2.1.9','none','No details'),(285,'com_extplorer','component','2.1.9','<=','3.0.0','>=','Extplorer Component','Directory Transversal Vulnerability','Aug 04 2017','Version 2.1.9 and previous','none','No details'),(286,'com_calendarplanner','component','1.0.1','==','3.0.0','>=','Calendar Planner Component','SQL Injection Vulnerability','Aug 24 2017','Version 1.0.1','update','1.0.2'),(287,'mod_byebyepassword','module','1.0.4','<=','3.0.0','>=','Bye bye Password Module','Information Disclosure Vulnerability','Aug 24 2017','Version 1.0.4 and lower','none','No details'),(288,'com_zcalendar','component','4.3.6','<=','3.0.0','>=','Zcalendar Component','SQL Injection Vulnerability','Aug 26 2017','Version 4.3.6 and lower','update','4.3.7'),(289,'com_ccnewsletter','component','2.1.9','<=','3.0.0','>=','Ccnewsletter Component','SQL Injection Vulnerability','Aug 26 2017','Version 2.1.9 and lower','update','2.2.0'),(290,'com_lmsking','component','3.2.3.19','<=','3.0.0','>=','LMS King Component','SQL Injection Vulnerability','Aug 30 2017','Version 3.2.319 and lower','update','3.2.3.20'),(291,'com_twitchtv','component','1.1','==','3.0.0','>=','Twitch tv Component','SQL Injection Vulnerability','Aug 30 2017','Version 1.1','none','No details'),(292,'com_kissgallery','component','1.0.0','==','3.0.0','>=','Kissgallery Component','SQL Injection Vulnerability','Aug 30 2017','Version 1.0.0','none','No details'),(293,'com_registrationpro','component','4.1.3','==','3.0.0','>=','Event Registration Pro Component','SQL Injection Vulnerability','Sep 07 2017','Version 4.1.3','none','No details'),(294,'com_rpl','component','1.0.0','>=','3.0.0','>=','Realtyna RPL Component','SQL Injection Vulnerability','Sep 07 2017','All versions','none','No details'),(295,'com_simgenealogy','component','2.1.8','<','3.0.0','>=','Simgenealogy Component','SQL Injection Vulnerability','Sep 10 2017','Version 2.1.7 and previous','update','2.1.8'),(296,'com_payplans','component','3.6.3','<','3.0.0','>=','Payplans Component','Price modification Vulnerability','Sep 13 2017','Version 3.6.2 and previous','update','3.6.3'),(297,'com_joomanager','component','2.0.0','<=','3.0.0','>=','Joomanager Component','Arbitrary File Download Vulnerability','Sep 14 2017','2.0.0 and previous','none','No details'),(298,'Joomla!','core','3.7.5','<=','3.0.0','>=','Joomla! Information disclosures','Bug in SQL query','Sep 19 2017','Joomla! versions 3.7.0 through 3.7.5','update','3.8.0'),(299,'Joomla!','core','3.7.5','<=','3.0.0','>=','Joomla! LDAP Information disclosures','Inadequate escaping in LDAP autenthication plugin','Sep 19 2017','Joomla! versions 1.5.0 through 3.7.5','update','3.8.0'),(300,'com_userextranet','component','1.3.2','<=','3.0.0','>=','UserExtranet Component','SQL Injection Vulnerability','Sep 21 2017','1.3.2 and previous','update','1.3.3'),(301,'com_surveyforce','component','3.2.4','==','3.0.0','>=','Survey Force Deluxe Component','SQL Injection Vulnerability','Sep 21 2017','3.2.4','update','3.2.5'),(302,'com_spmoviedb','component','1.3','==','3.0.0','>=','SP Movie Database Component','SQL Injection Vulnerability','Sep 22 2017','1.3','update','1.4'),(303,'com_ns_downloadshop','component','2.2.6','==','3.0.0','>=','NS Download Shop Component','SQL Injection Vulnerability','Oct 01 2017','Version 2.2.6','none','No details'),(304,'com_zhyandexmap','component','6.1.1.0','==','3.0.0','>=','Zh YandexMap Component','SQL Injection Vulnerability','Oct 01 2017','Version 6.1.1.0','update','6.2.0.0'),(305,'com_price_alert','component','3.0.4','<=','3.0.0','>=','Price Alert for Virtuemart Component','SQL Injection Vulnerability','Oct 18 2017','Version 3.0.4 and previous','none','No details'),(306,'com_ajaxquiz','component','1.8.0','==','3.0.0','>=','Ajax Quiz Component','SQL Injection Vulnerability','Oct 18 2017','Version 1.8.0','none','No details'),(307,'plugin_googlemap3','plugin','3.5.2','<=','3.0.0','>=','Google Maps by Reumer','Malicious update','Oct 21 2017','Version 3.5','update','3.5.2'),(308,'com_hdwplayer','component','4.0.0','<=','3.0.0','>=','HDW Player Component','RCE Vulnerability','Oct 26 2017','Version 4.0.0 and all previous','none','No details'),(309,'com_jsjobs','component','1.1.8','==','3.0.0','>=','Js Jobs Component','RCE Vulnerability','Oct 26 2017','Version 1.1.8','update','1.1.9'),(310,'Joomla!','core','3.8.1','<=','3.0.0','>=','Joomla! LDAP Information Disclosure','Inadequate escaping in the LDAP authentication plugin','Nov 07 2017','Joomla! versions 1.5.0 through 3.8.1','update','3.8.2'),(311,'Joomla!','core','3.8.1','<=','3.0.0','>=','Joomla! 2-factor-authentication bypass vulnerability','---','Nov 07 2017','Joomla! versions 3.2.0 through 3.8.1','update','3.8.2'),(312,'Joomla!','core','3.8.1','<=','3.0.0','>=','Joomla! Information Disclosure vulnerability','logic bug in com_fields','Nov 07 2017','Joomla! versions 3.7.0 through 3.8.1','update','3.8.2'),(313,'com_virtuemart','component','3.2.4','==','3.0.0','>=','Virtuemart Component','XSS Vulnerability','Dec 04 2017','Version 3.2.4','update','3.2.6'),(314,'com_jbuildozer','component','1.4.1','==','3.0.0','>=','JBuildozer Component','SQL Injection Vulnerability','Dec 20 2017','Version 1.4.1','none','No details'),(315,'com_jevideogallery','component','3.0.5','==','3.0.0','>=','JEXTN Video Gallery Component','SQL Injection Vulnerability','Dec 20 2017','Version 3.0.5','none','No details'),(316,'com_nge','component','2.1.0','==','3.0.0','>=','NextGen Editor Component','SQL Injection Vulnerability','Dec 20 2017','Version 2.1.0','none','No details'),(317,'com_bigfileuploader','component','1.0.2','==','3.0.0','>=','Big File uploader Editor Component','File upload Vulnerability','Dec 20 2017','Version 1.0.2','none','No details'),(318,'com_bookpro','component','1.0','==','3.0.0','>=','JB Visa Component','SQL Injection Vulnerability','Jan 06 2018','Version 1.0','none','No details'),(319,'com_b2jcontact','component','2.1.14','<=','3.0.0','>=','B2j Contact Component','Several Vulnerabilities','Dec 20 2017','Version 2.1.14 and lower','update','2.1.15'),(320,'com_myproject','component','2.0','==','3.0.0','>=','My Projects Component','SQL Injection Vulnerability','Dec 20 2017','Version 2.0','update','2.1'),(321,'com_userbench','component','1.0','==','3.0.0','>=','User Bench Component','SQL Injection Vulnerability','Jan 01 2018','Version 1.0','update','1.1'),(322,'com_easydiscuss','component','4.0.20','<=','3.0.0','>=','Easy Discuss Component','XSS Vulnerability','Jan 08 2018','Version 4.0.20 and previous','update','4.0.21'),(323,'com_guru','component','5.0.15','<=','3.0.0','>=','Guru Pro Component','SQL Injection Vulnerability','Jan 08 2018','Version 5.0.15 and previous','update','5.0.16'),(324,'com_ajaxquiz','component','2.0','<=','3.0.0','>=','Ajax Quiz Component','SQL Injection Vulnerability','Jan 09 2018','Version 2.0 and lower','update','2.1'),(325,'com_enmasse','component','1.0','>=','3.0.0','>=','En Masse Component','SQL Injection Vulnerability','Jan 15 2018','all known versions','none','No details'),(326,'com_simplephotogallery','component','3.5.0','<=','3.0.0','>=','Simple Image Gallery (free) Component','XSS Vulnerability','Jan 30 2018','Version 3.5.0 and previous','update','3.6.0'),(327,'com_jtagmembersdirectory','component','5.3.7','==','3.0.0','>=','Jtag Members Directory Component','Arbitrary file upload Vulnerability','Jan 30 2018','Version 5.3.7','none','No details'),(328,'com_jssupportticket','component','1.1.0','==','3.0.0','>=','JS Support Ticket Component','Cross site request forgery Vulnerability','Jan 30 2018','Version 1.1.0','none','No details'),(329,'Joomla!','core','3.8.3','<=','3.0.0','>=','Joomla! XSS vulnerability','Lack of escaping in module chromes','Jan 30 2018','Joomla! versions 3.0.0 through 3.8.3','update','3.8.4'),(330,'Joomla!','core','3.8.3','<=','3.0.0','>=','Joomla! XSS vulnerability','Inadequate input filtering in com_fields','Jan 30 2018','Joomla! versions 3.7.0 through 3.8.3','update','3.8.4'),(331,'Joomla!','core','3.8.3','<=','3.0.0','>=','Joomla! XSS vulnerability','Inadequate input filtering in Uri class','Jan 30 2018','Joomla! versions 1.5.0 through 3.8.3','update','3.8.4'),(332,'Joomla!','core','3.8.3','<=','3.0.0','>=','Joomla! SQL Injection vulnerability','Lack of type casting in SQL statement','Jan 30 2018','Joomla! versions 3.7.0 through 3.8.3','update','3.8.4'),(333,'com_jce','component','2.5.25','==','3.0.0','>=','JCE Editor Component','Cross site scripting Vulnerability','Feb 02 2018','Version 2.5.25','update','2.5.26'),(334,'com_jssupportticket','component','1.1.0','==','3.0.0','>=','JS Support Ticket Component','Cross site scripting Vulnerability','Feb 21 2018','Version 1.1.0','update','1.1.1'),(335,'com_jsjobs','component','1.1.9','<=','3.0.0','>=','JS Jobs Component','SQL Injection Vulnerability','Feb 21 2018','Version 1.1.9 and previous','update','1.2.0'),(336,'com_ccnewsletter','component','2.2.2','<=','3.0.0','>=','ccnewsletter Component','SQL Injection Vulnerability','Feb 21 2018','Version 2.2.2 and previous','update','2.2.3'),(337,'com_jimtawl','component','2.2.6','==','3.0.0','>=','Jimtawl Component','Arbitrary File upload Vulnerability','Feb 23 2018','Version 2.2.6','update','2.2.7'),(338,'com_zhgooglemap','component','8.4.0.0','<=','3.0.0','>=','ZH GoogleMap Component','Sql Injection Vulnerability','Feb 23 2018','Version 8.4.0.0 and previous','update','8.4.1.0'),(339,'com_zhyandexmap','component','6.2.1.0','<=','3.0.0','>=','ZH YandexMap Component','Sql Injection Vulnerability','Feb 23 2018','Version 6.2.1.0 and previous','update','6.3.1.0'),(340,'com_zhbaidumap','component','3.0.0.1','<=','3.0.0','>=','ZH BaiduMap Component','Sql Injection Vulnerability','Feb 23 2018','Version 3.0.0.1 and previous','update','3.0.1.0'),(341,'com_jsptickets','component','1.1','<=','3.0.0','>=','JSP Tickets Component','Sql Injection Vulnerability','Feb 23 2018','Version 1.1 and previous','update','1.2'),(342,'com_solidres','component','2.5','<=','3.0.0','>=','Solidres Component','Sql Injection Vulnerability','Feb 25 2018','Version 2.5 and previous','update','2.5.1'),(343,'com_timetable','component','1.6','<=','3.0.0','>=','Timetable Responsive Schedule Component','Sql Injection Vulnerability','Feb 25 2018','Version 1.6 and previous','update','1.7'),(344,'com_jsplocation','component','2.4','<=','3.0.0','>=','JSP Store Locator Component','Sql Injection Vulnerability','Feb 25 2018','Version 2.4 and previous','update','2.5'),(345,'com_smartshoutbox','component','2.9.5','<=','3.0.0','>=','Smart Shoutbox Component','Sql Injection Vulnerability','Feb 25 2018','Version 2.9.5 and previous','update','3.0.0'),(346,'plg_sige','plugin','3.2.3','<=','3.0.0','>=','Kubik-Rubik Simple Image Gallery Extended Plugin','Cross site scripting Vulnerability','Feb 26 2018','Version 3.2.3 and previous','update','3.3.0'),(347,'com_saxumastro','component','4.0.14','<=','3.0.0','>=','Saxum Astro Component','Sql Injection Vulnerability','Feb 26 2018','Version 4.0.14 and previous','none','No details'),(348,'com_saxumnumerology','component','3.0.4','<=','3.0.0','>=','Saxum Numerology Component','Sql Injection Vulnerability','Feb 26 2018','Version 3.0.4 and previous','none','No details'),(349,'com_saxumpicker','component','3.2.10','<=','3.0.0','>=','Saxum Picker Component','Sql Injection Vulnerability','Feb 26 2018','Version 3.2.10 and previous','none','No details'),(350,'com_biblestudy','component','9.1.1','<=','3.0.0','>=','Proclaim Component','Arbitrary file upload Vulnerability','Feb 27 2018','Version 9.1.1 and previous','update','9.1.2'),(351,'com_socialpinboard','component','2.0','==','3.0.0','>=','Joomla! Pinterest Clone Social Pinboard Component','Sql Injection Vulnerability','Feb 28 2018','Version 2.0','none','No details'),(352,'com_osproperty','component','3.12.8','<=','3.0.0','>=','OS Property Component','Sql Injection Vulnerability','Feb 28 2018','Version 3.12.8 and previous','update','3.12.9'),(353,'com_realpin','component','1.5.04','<=','3.0.0','>=','Realpin Component','Sql Injection Vulnerability','Mar 01 2018','Version 1.5.04 and previous','none','No details'),(354,'com_media_library','component','4.0.12','<=','3.0.0','>=','Media Library Free Component','Sql Injection Vulnerability','Mar 01 2018','Version 4.0.12 and previous','none','No details'),(355,'com_jsautoz','component','1.0.9','<=','3.0.0','>=','JS Autoz Component','Sql Injection Vulnerability','Mar 03 2018','Version 1.0.9 and previous','none','No details'),(356,'com_jticketing','component','2.0.16','<=','3.0.0','>=','Jticketing Component','Sql Injection Vulnerability','Mar 03 2018','Version 2.0.16 and previous','update','2.0.18'),(357,'com_invitex','component','3.0.5','<=','3.0.0','>=','InviteX Component','Sql Injection Vulnerability','Mar 03 2018','Version 3.0.5 and previous','update','3.0.6'),(358,'com_abook','component','3.1.3','<=','3.0.0','>=','Alexandria Book Library Component','Sql Injection Vulnerability','Mar 05 2018','Version 3.1.3 and previous','none','No details'),(359,'com_jmsmusic','component','1.1.1','<=','3.0.0','>=','JM Music Component','Sql Injection Vulnerability','Mar 05 2018','Version 1.1.1 and previous','none','No details'),(360,'com_formmaker','component','3.6.14','<=','3.0.0','>=','Form Maker Component','Sql Injection Vulnerability','Mar 05 2018','Version 3.6.14 and previous','update','3.6.15'),(361,'com_jgive','component','2.0.9','<=','3.0.0','>=','JGive Component','Sql Injection Vulnerability','Mar 05 2018','Version 2.0.9 and previous','update','2.0.11'),(362,'com_ekrishta','component','2.9','<=','3.0.0','>=','Ek Rishta Component','Sql Injection Vulnerability','Mar 07 2018','Version 2.9 and previous','none','No details'),(363,'com_prayercenter','component','3.0.2','<=','3.0.0','>=','PrayerCenter Component','Sql Injection Vulnerability','Mar 07 2018','Version 3.0.2 and previous','none','No details'),(364,'com_cwtags','component','2.0.8','<=','3.0.0','>=','CW Tags Component','Sql Injection Vulnerability','Mar 07 2018','Version 2.0.8 and previous','none','No details'),(365,'com_squadmanagement','component','1.0.3','<=','3.0.0','>=','SquadManagement Component','Sql Injection Vulnerability','Mar 07 2018','Version 1.0.3 and previous','none','No details'),(366,'com_neorecruit','component','4.2.1','<=','3.0.0','>=','NeoRecruit Component','Sql Injection Vulnerability','Mar 07 2018','Version 4.2.1 and previous','update','4.2.2'),(367,'com_checklist','component','1.1.1.003','<=','3.0.0','>=','Checklist Component','Sql Injection Vulnerability','Mar 07 2018','Version 1.1.1.003 and previous','update','1.1.1.004'),(368,'com_simplecalendar','component','3.1.9','<=','3.0.0','>=','Simple Calendar Component','Sql Injection Vulnerability','Mar 08 2018','Version 3.1.9 and previous','none','No details'),(369,'com_bookpro','component','2.3','==','3.0.0','>=','JB Bus Component','Sql Injection Vulnerability','Mar 08 2018','Version 2.3','none','No details'),(370,'com_dtracker','component','3.0','==','3.0.0','>=','File Download Tracker Component','Sql Injection Vulnerability','Mar 08 2018','Version 3.0','none','No details'),(371,'com_jquickcontact','component','1.3.2.3','<=','3.0.0','>=','JQuickContact Component','Sql Injection Vulnerability','Mar 08 2018','Version 1.3.2.3 and previous','update','1.3.2.4'),(372,'com_fastball','component','10.0.0','<=','3.0.0','>=','Fastball Component','Sql Injection Vulnerability','Mar 08 2018','All versions','none','No details'),(373,'com_dtregister','component','3.2.7','<=','3.0.0','>=','DT REgister Component','Sql Injection Vulnerability','Mar 08 2018','Version 3.2.7 and previous','none','No details'),(374,'com_jomestate','component','3.7','<=','3.0.0','>=','JomEstate Component','Sql Injection Vulnerability','Mar 13 2018','Version 3.7 and previous','update','3.8'),(375,'Joomla!','core','3.8.5','<=','3.0.0','>=','Joomla! Sql Injection vulnerability','Lack of type casting of a variable in SQL statement of user Notes','Mar 13 2018','Joomla!  versions 3.5.0 through 3.8.5','update','3.8.6'),(376,'com_kunena','component','5.0.13','<=','3.0.0','>=','Kunena Component','Other Vulnerability','Mar 14 2018','Version 5.0.13 and previous','update','5.0.14'),(377,'com_gmap','component','4.2.3','<=','3.0.0','>=','Google Map Landkarten Component','Sql Injection Vulnerability','Mar 20 2018','Version 4.2.3 and previous','none','No details'),(378,'com_attachments','component','3.2.5','<=','3.0.0','>=','Attachments Component','Sql Injection Vulnerability','Mar 20 2018','Version 3.2.5 and previous','none','No details'),(379,'com_visualcalendar','component','3.1.5','<=','3.0.0','>=','Visual Calendar Component','Sql Injection Vulnerability','Mar 20 2018','Version 3.1.5 and previous','update','3.1.6'),(380,'com_cpeventcalendar','component','3.0.2','<=','3.0.0','>=','CP Event Calendar Component','Sql Injection Vulnerability','Mar 26 2018','Version 3.0.2 and previous','update','3.0.3'),(381,'com_acymailing','component','5.9.5','<=','3.0.0','>=','Acymailing Component','Csv Injection Vulnerability','Mar 26 2018','Version 5.9.5 and previous','update','5.9.5'),(382,'com_acysms','component','3.5.0','<=','3.0.0','>=','Acysms Component','Csv Injection Vulnerability','Mar 29 2018','Version 3.5.0 and previous','update','3.5.1'),(383,'com_jsjobs','component','1.2.0','<=','3.0.0','>=','Js Jobs Component','Cross site scripting Vulnerability','Mar 31 2018','Version 1.2.0 and previous','update','1.2.1'),(384,'com_prayercenter','component','3.0.2','<=','3.0.0','>=','Prayercenter Component','Sql injection Vulnerability','Mar 31 2018','Version 3.0.2 and previous','update','3.0.3'),(385,'com_virtuemart','component','3.2.12','<=','3.0.0','>=','Virtuemart Component','Cross site scripting Vulnerability','Apr 07 2018','Version 3.2.12 and previous','update','3.2.14'),(386,'com_gridbox','component','2.4.0','<=','3.0.0','>=','Gridbox Component','Multiple Vulnerabilities','Apr 10 2018','Version 2.4.0 and previous','none','No details'),(387,'com_jdownloads','component','3.2.58','<=','3.0.0','>=','JDownloads Component','Cross site scripting Vulnerability','Apr 10 2018','Version 3.2.58 and previous','update','3.2.59'),(388,'com_cwtags','component','2.0.8','<=','3.0.0','>=','CW Tags Component','Sql injection Vulnerability','Apr 10 2018','Version 2.0.8 and previous','update','2.1.1'),(389,'com_convertforms','component','2.0.3','<=','3.0.0','>=','Convert Forms Component','Csv Injection Vulnerability','Apr 13 2018','Version 2.0.3 and previous','update','2.0.4'),(390,'com_nexevocontact','component','1.0.1','<=','3.0.0','>=','Nexevo contact form Component','Backdoor Vulnerability','May 15 2018','Version 1.0.1 and previous','none','No details'),(391,'Joomla!','core','3.8.7','<=','3.0.0','>=','Joomla! multiple vulnerabilities','9 security vulnerabilities','May 22 2018','Joomla!  versions 1.5.0 through 3.8.7','update','3.8.8'),(392,'com_ekrishta','component','2.10','==','3.0.0','>=','EkRishta Component','Sql injection Vulnerability','Jun 13 2018','Version 2.10','none','No details'),(393,'com_cb','component','2.1.4','<=','3.0.0','>=','Community Builder Component','XSS Vulnerability','Jun 14 2018','Version 2.1.4 and previous','update','2.1.5'),(394,'com_jomres','component','9.11.2','==','3.0.0','>=','Jomres Component','CSRF Vulnerability','Jun 19 2018','Version 9.11.2','none','No details'),(395,'Joomla!','core','3.8.8','<=','3.0.0','>=','Joomla! multiple vulnerabilities','2 security vulnerabilities','Jun 26 2018','Joomla! versions 1.5.0 through 3.8.8','update','3.8.9'),(396,'com_medialibrary','component','4.0.12','<=','3.0.0','>=','MediaLibrary Free Component','SQL Injection Vulnerability','Jul 06 2018','Version 4.0.12 and previous','update','4.0.21'),(397,'com_advertisementboard','component','3.1.0','<=','3.0.0','>=','Advertisement Board Component','SQL Injection Vulnerability','Jul 06 2018','Version 3.1.0 and previous','update','3.4.1'),(398,'com_kunena','component','5.1.1','<=','3.0.0','>=','Kunena Component','Attachments Vulnerability','Jul 16 2018','Version 5.0.0 through 5.1.1','update','5.1.2'),(399,'com_jbusinessdirectory','component','4.9.3','<=','3.0.0','>=','J-Business Directory Component','SQL Injection Vulnerability','Aug 12 2018','Version 4.9.3 and previous','update','4.9.4'),(400,'com_jcomments','component','3.0.5','<=','3.0.0','>=','JComments Component','Input Validation Vulnerability','Aug 17 2018','Version 3.0.5 and previous','update','3.0.6'),(401,'com_mobile','component','2.1.24','==','3.0.0','>=','Mobilejoomla Component','Malicious redirects Vulnerability','Aug 17 2018','Version 2.1.24','update','2.1.25'),(402,'Joomla!','core','3.8.11','<=','3.0.0','>=','Joomla! multiple vulnerabilities','3 security vulnerabilities','Aug 28 2018','Joomla! versions 1.5.0 through 3.8.11','update','3.8.12'),(403,'com_virtuemart_magiczoomplus','component','4.9.4','<=','3.0.0','>=','Magiczoomplus for Virtuemart Component','Multiple Vulnerabilities','Sep 17 2018','Version 4.9.4 and previous','update','4.9.6'),(404,'com_magiczoomplus','component','3.3.4','<=','3.0.0','>=','Magiczoomplus for Joomla Component','Multiple Vulnerabilities','Sep 17 2018','Version 3.3.4 and previous','update','3.3.6'),(405,'com_timetableschedule','component','3.6.8','==','3.0.0','>=','Timetable Schedule Component','SQL Injection vulnerability','Oct 01 2018','Version 3.6.8','none','No details'),(406,'com_articleman','component','4.3.9','==','3.0.0','>=','Article Factory Manager Component','SQL Injection vulnerability','Oct 01 2018','Version 4.3.9','none','No details'),(407,'com_aindexdictionaries','component','1.0','==','3.0.0','>=','AlphaIndex Dictionaries Component','SQL Injection vulnerability','Oct 01 2018','Version 1.0','none','No details'),(408,'com_rbids','component','4.3.8','==','3.0.0','>=','Reverse Auction Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 4.3.8','none','No details'),(409,'com_collectionfactory','component','4.1.9','==','3.0.0','>=','Collection Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 4.1.9','none','No details'),(410,'com_swapfactory','component','2.2.1','==','3.0.0','>=','Swap Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 2.2.1','none','No details'),(411,'com_extroform','component','2.1.5','==','3.0.0','>=','eXtroForms Component','SQL Injection vulnerability','Oct 01 2018','Version 2.1.5','none','No details'),(412,'com_dutchfactory','component','2.0.2','==','3.0.0','>=','Dutch Auction Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 2.0.2','none','No details'),(413,'com_socialfactory','component','3.8.3','==','3.0.0','>=','Social Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 3.8.3','none','No details'),(414,'com_jobsfactory','component','2.0.4','==','3.0.0','>=','Jobs Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 2.0.4','none','No details'),(415,'com_questions','component','1.4.3','==','3.0.0','>=','Questions Component','SQL Injection vulnerability','Oct 01 2018','Version 1.4.3','none','No details'),(416,'com_pennyfactory','component','2.0.4','==','3.0.0','>=','Penny Auction Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 2.0.4','none','No details'),(417,'com_rafflefactory','component','3.5.2','==','3.0.0','>=','Raffle Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 3.5.2','none','No details'),(418,'com_pofos','component','1.6.1','==','3.0.0','>=','Responsive Portfolio Component','SQL Injection vulnerability','Oct 01 2018','Version 1.6.1','none','No details'),(419,'com_auctionfactory','component','4.5.5','==','3.0.0','>=','Auction Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 4.5.5','none','No details'),(420,'com_microdealfactory','component','2.0.4','==','3.0.0','>=','Micro Deal Factory Component','SQL Injection vulnerability','Oct 01 2018','Version 2.0.4','none','No details'),(421,'jckeditor','plugin','6.4.4','==','3.0.0','>=','JCK Editor Plugin','SQL Injection vulnerability','Oct 01 2018','Version 6.4.4','none','No details'),(422,'cwattachments','plugin','1.0.6','==','3.0.0','>=','CW Article Attachments Plugin','SQL Injection vulnerability','Oct 01 2018','Version 1.0.6','none','No details'),(423,'Joomla!','core','3.8.12','<=','3.0.0','>=','Joomla! multiple vulnerabilities','4 security vulnerabilities','Oct 09 2018','Joomla! versions 1.5.0 through 3.8.12','update','3.8.13'),(424,'com_kunena','component','5.1.5','<','3.0.0','>=','Kunena Component','Two low vulnerabilities','Oct 22 2018','Version 3.0 through 5.1.4','update','5.1.5'),(425,'com_jimtawl','component','2.2.7','==','3.0.0','>=','Jimtawl Component','Sql Injection vulnerabilitiy','Nov 18 2018','Version 2.2.7','update','2.2.8'),(426,'com_kunena','component','5.1.7','<','3.0.0','>=','Kunena Component','Xss vulnerability','Nov 21 2018','Version 5.1.6 and lower','update','5.1.7'),(427,'com_jephotogallery','component','1.1','==','3.0.0','>=','JE Photo Gallery Component','Sql Injection vulnerabilitiy','Dec 04 2018','Version 1.1','none','No details'),(428,'com_muscol','component','3.0.3','<=','3.0.0','>=','Music collection Component','Sql Injection vulnerability','Dec 04 2018','Version 3.0.3','update','3.0.6'),(429,'com_jomres','component','9.14.0','<=','3.0.0','>=','Joomres Component','Third party vulnerabilitiy','Dec 07 2018','Version 9.14.0 and lower','update','9.15.0'),(430,'com_kunena','component','5.1.7','<=','3.0.0','>=','Kunena Component','Tree XSS vulnerabilities','Jan 03 2019','Version 5.1.7 and lower','update','5.1.8'),(431,'com_joomcrm','component','1.1.1','==','3.0.0','>=','JoomCRM Component Component','Sql Injection vulnerabilitiy','Jan 15 2019','Version 1.1.0','update','1.1.2'),(432,'com_jpprojects','component','1.1.3.2','==','3.0.0','>=','JoomProject Component','Information Disclosure vulnerabilitiy','Jan 15 2019','Version 1.1.3.2','update','1.1.3.3'),(433,'mod_jw_srfr','module','3.5.0','<=','3.0.0','>=','Simple RSS Feed Reader Module','Open Redirect vulnerability','Jan 15 2019','Version 3.6.0','none','No details'),(434,'Joomla!','core','3.9.1','<=','3.0.0','>=','Joomla! multiple vulnerabilities','4 low vulnerabilities','Jan 15 2019','Joomla 2.5.0 through 3.9.1','update','3.9.2'),(435,'com_jcruisereservation','component','6.0.2','==','3.0.0','>=','J-CruiseReservation Component','Sql Injection vulnerability','Jan 21 2019','Version 6.0.2','update','6.0.4'),(436,'com_easyshop','component','1.2.3','==','3.0.0','>=','EasyShop Component','Local file inclusion vulnerability','Feb 04 2019','Version 1.2.3','update','1.2.4'),(437,'Joomla!','core','3.9.2','<=','3.0.0','>=','Joomla! multiple vulnerabilities','6 low vulnerabilities','Feb 12 2019','Joomla 2.5.0 through 3.9.2','update','3.9.3'),(438,'com_edocman','component','1.11.7','==','3.0.0','>=','Edocman Component','Sql Injection vulnerability','Feb 23 2019','Version 1.11.7','update','1.11.8'),(439,'com_kunena','component','5.1.10','<','3.0.0','>=','Kunena Component','XSS vulnerabilities','Mar 05 2019','Version 5.1.0 through 5.1.10','update','5.1.10'),(440,'Joomla!','core','3.9.3','<=','3.0.0','>=','Joomla! multiple vulnerabilities','3 low vulnerabilities and one high vulnerability','Mar 13 2019','Joomla 3.0.0 through 3.9.3','update','3.9.4'),(441,'com_jevents','component','3.4.50','<','3.0.0','>=','JEvents Component','Multiple vulnerabilities','Mar 17 2019','Version 3.4.49 and maybe lower','update','3.4.50'),(442,'com_acymailing','component','5.10.6','==','3.0.0','>=','Acymailing Component','Multiple vulnerabilities','Mar 26 2019','Version 5.10.6','update','5.10.7'),(443,'Joomla!','core','3.9.4','<=','3.0.0','>=','Joomla! multiple vulnerabilities','one moderate, one low and one high vulnerabilities','Apr 09 2019','Joomla 3.0.0 through 3.9.4','update','3.9.5'),(444,'com_bookpro','component','2.3','==','3.0.0','>=','JB Bus Component','SQL Injection vulnerability','Apr 12 2019','Version 2.3','update','2.4'),(445,'com_kunena','component','5.1.12','<','3.0.0','>=','Kunena Component','XSS vulnerability','Apr 23 2019','Version 5.1.3 through 5.1.12','update','5.1.12.1'),(446,'com_phocagallery','component','4.3.17','<','3.0.0','>=','Phoca Gallery Component','XSS vulnerability','Apr 26 2019','Version 4.3.15 prior','update','4.3.17'),(447,'Joomla!','core','3.9.5','<=','3.0.0','>=','Joomla! core','Xss vulnerability','May 07 2019','Joomla 1.7.0 through 3.9.5','update','3.9.6'),(448,'com_rsform','component','2.2.0','==','3.0.0','>=','RSForm! Pro Component','Csv injection vulnerability','May 13 2019','Version 2.2.0','update','2.2.1'),(449,'com_rsmembership','component','1.22.10','<=','3.0.0','>=','RSMembership! Component','Csv injection vulnerability','May 13 2019','Version 1.22.10 and lower','update','1.22.11'),(450,'com_rsevents','component','2.2.0','<=','3.0.0','>=','RSEvents! Pro Component','Csv injection vulnerability','May 13 2019','Version 2.2.0 and lower','update','2.2.1'),(451,'com_oziogallery','component','5.0.1','==','3.0.0','>=','Oziogallery Component','Xss vulnerability','May 14 2019','Version 5.0.1','update','5.0.2'),(452,'com_loginguard','component','3.1.1','<=','3.0.0','>=','Akeeba LoginGuard Component','Information disclosure vulnerability','May 18 2019','Version 3.1.1 and lower','update','3.2.0'),(453,'com_extplorer','component','2.1.12','<=','3.0.0','>=','Extplorer Component','Several vulnerabilities','May 20 2019','Version 2.1.12 and maybe lower','update','2.1.13'),(454,'com_comprofiler','component','2.4.1','<=','3.0.0','>=','Community Builder Component','jQuery vulnerability','May 22 2019','Version 2.4.1 and lower','update','2.4.2'),(455,'com_akeeba','component','6.4.2.1','<=','3.0.0','>=','Akeeba Backup Component','Xss vulnerability','Jun 03 2019','versions 5.3.0.b1 to 6.4.2.1 inclusive','update','6.5.1'),(456,'Joomla!','core','3.9.6','<=','3.0.0','>=','Joomla! core','Several vulnerabilities','Jun 11 2019','Joomla 3.6.0 through 3.9.6','update','3.9.7'),(457,'Joomla!','core','3.9.8','<=','3.0.0','>=','Joomla! core','Remote code execution Vulnerability','Jul 09 2019','Joomla 3.9.7 through 3.9.8','update','3.9.9'),(458,'com_easydiscuss','component','4.1.9','==','3.0.0','>=','Easy discuss Component','Sql injection vulnerability','Aug 09 2019','version 4.1.9','update','4.1.10'),(459,'Joomla!','core','3.9.10','<=','3.0.0','>=','Joomla! core','Hardening com_contact contact form','Aug 13 2019','Joomla 1.6.2 through 3.9.10','update','3.9.11'),(460,'com_jssupportticket','component','1.1.5','==','3.0.0','>=','JS support ticket Component','Directory Traversal vulnerability','Aug 13 2019','version 1.1.5','update','1.1.7'),(461,'com_jssupportticket','component','1.1.6','==','3.0.0','>=','JS support ticket Component','Sql injection vulnerability','Aug 13 2019','version 1.1.6','update','1.1.7'),(462,'com_kunena','component','5.1.14','<','3.0.0','>=','Kunena Component','XSS vulnerability','Aug 15 2019','Version 5.0.x through 5.1.14','update','5.1.4'),(463,'com_jdownloads','component','3.2.64','<=','3.0.0','>=','JDownloads Component','Sql injection Vulnerability','Aug 26 2018','Version 3.2.64 and previous','update','3.2.65'),(464,'com_payplans','component','4.0','==','3.0.0','>=','Payplans Component','Information disclosure Vulnerability','Sep 13 2018','Version 4.0','update','4.0.13'),(465,'Joomla!','core','3.9.11','<=','3.0.0','>=','Joomla! core','XSS in core logo','Sep 25 2019','Joomla 3.0.0 through 3.9.11','update','3.9.12'),(466,'com_jsjobs','component','1.2.6','<=','3.0.0','>=','Js Jobs Component','Other Vulnerabilities','Oct 14 2019','Version 1.1.5, 1.1.6, 1.2.5 and 1.2.6','update','1.2.7'),(467,'Joomla!','core','3.9.12','<=','3.0.0','>=','Joomla! core','Two vulnerabilities','Nov 05 2019','Joomla 3.2.0 through 3.9.12','update','3.9.13'),(468,'com_j2store','component','3.3.9','<=','3.0.0','>=','J2Store Component','Xss vulnerability','Nov 14 2019','Version 3.3.9 and previous','none','No details'),(469,'Joomla!','core','3.9.13','<=','3.0.0','>=','Joomla! core','Two vulnerabilities','Dec 18 2019','Joomla 2.5.0 through 3.9.13','update','3.9.14'),(470,'Joomla!','core','3.9.14','<=','3.0.0','>=','Joomla! core','Three low vulnerabilities','Jan 28 2020','Joomla 3.0.0 through 3.9.14','update','3.9.15'),(471,'Joomla!','core','3.9.15','<=','3.0.0','>=','Joomla! core','Six low vulnerabilities','Mar 10 2020','Joomla 1.7.0 through 3.9.15','update','3.9.16'),(472,'com_simplecalendar','component','3.1.9','<=','3.0.0','>=','Simple Calendar Component','Sql injection Vulnerability','Mar 24 2020','Version 3.1.9 and lower','update','3.2.1'),(473,'com_acym','component','6.9.2','<','3.0.0','>=','Acymailing 6 Component','File upload Vulnerability','Mar 24 2020','Version 6','update','6.9.2'),(474,'com_gmapfp','component','3.30','==','3.0.0','>=','GMapFP Component','Other Vulnerability','Apr 14 2020','Version 3.30','update','3.52'),(475,'Joomla!','core','3.9.16','<=','3.0.0','>=','Joomla! core','Three low vulnerabilities','Apr 22 2020','Joomla 2.5.0 through 3.9.16','update','3.9.18'),(476,'Joomla!','core','3.9.18','<=','3.0.0','>=','Joomla! core','Five low vulnerabilities','Jun 03 2020','Joomla 2.5.0 through 3.9.18','update','3.9.19'),(477,'Joomla!','core','3.9.19','<=','3.0.0','>=','Joomla! core','Several vulnerabilities','Jul 14 2020','Joomla 2.5.0 through 3.9.19','update','3.9.20'),(478,'Joomla!','core','3.9.20','<=','3.0.0','>=','Joomla! core','Three low vulnerabilities','Aug 25 2020','Joomla 2.5.0 through 3.9.20','update','3.9.21'),(479,'com_pago','component','2.5.9.0','==','3.0.0','>=','paGO Commerce Component','SQL Injection','Nov 02 2020','Version 2.5.9.0','none','No details'),(480,'Joomla!','core','3.9.22','<=','3.0.0','>=','Joomla! core','Seven low vulnerabilities','Nov 11 2020','Joomla 1.7.0 through 3.9.22','update','3.9.23'),(481,'Joomla!','core','3.9.23','<=','3.0.0','>=','Joomla! core','Three low vulnerabilities','Jan 12 2020','Joomla 3.0.0 through 3.9.23','update','3.9.24'),(482,'Joomla!','core','3.9.24','<=','3.0.0','>=','Joomla! core','Nine low vulnerabilities','Mar 03 2020','Joomla 2.5.0 through 3.9.24','update','3.9.25'),(483,'Joomla!','core','3.9.25','<=','3.0.0','>=','Joomla! core','Two low vulnerabilities','Apr 13 2020','Joomla 3.0.0 through 3.9.25','update','3.9.26'),(484,'com_publisher','component','3.0.19','==','3.0.0','>=','Publisher Component','Xss vulnerability','Apr 21 2021','Version 3.0.19','update','3.0.20'),(485,'com_yoorecipe','component','1.0.0','>=','3.0.0','>=','Yoorecipe Component','Sql Injection vulnerability','Apr 21 2021','All versions','none','No details'),(486,'Joomla!','core','3.9.26','<=','3.0.0','>=','Joomla! core','Three low vulnerabilities','May 25 2020','Joomla 3.0.0 through 3.9.26','update','3.9.27'),(487,'Joomla!','core','3.9.27','<=','3.0.0','>=','Joomla! core','Five low vulnerabilities','Jul 07 2020','Joomla 2.5.0 through 3.9.27','update','3.9.28'),(488,'Joomla!','core','4.0.0','<=','4.0.0','>=','Joomla! core','One vulnerability','Aug 24 2021','Joomla 4.0.0','update','4.0.1'),(489,'Joomla!','core','3.10.8','<=','3.0.0','>=','Joomla! core','Three low vulnerabilities','Mar 31 2022','Joomla 2.5.0 through 3.10.6','update','3.10.8'),(490,'Joomla!','core','4.1.2','<=','4.0.0','>=','Joomla! core','Seven low vulnerabilities','Mar 31 2022','Joomla 4.0.0 through 4.1.0','update','4.1.2'),(491,'com_sexypolling','component','2.1.7','<=','3.0.0','>=','Sexypolling Component','Sql Injection vulnerability','May 20 2022','Versions bellow 2.1.8','update','2.1.8');
/*!40000 ALTER TABLE `jos_securitycheckpro_db` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_dynamic_blacklist`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_dynamic_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_dynamic_blacklist` (
                                                          `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                          `timeattempt` datetime NOT NULL,
                                                          `counter` int NOT NULL DEFAULT '1',
                                                          PRIMARY KEY (`ip`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jos_securitycheckpro_emails`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_emails` (
                                               `id` int unsigned NOT NULL AUTO_INCREMENT,
                                               `envoys` tinyint DEFAULT '0',
                                               `send_date` date DEFAULT '2012-01-01',
                                               PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_emails`
--

LOCK TABLES `jos_securitycheckpro_emails` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_emails` DISABLE KEYS */;
INSERT INTO `jos_securitycheckpro_emails` VALUES (1,0,'2012-01-01');
/*!40000 ALTER TABLE `jos_securitycheckpro_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_file_manager`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_file_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_file_manager` (
                                                     `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                     `last_check` datetime DEFAULT NULL,
                                                     `last_check_integrity` datetime DEFAULT NULL,
                                                     `files_scanned` int DEFAULT '0',
                                                     `files_scanned_integrity` int DEFAULT '0',
                                                     `files_with_incorrect_permissions` int DEFAULT '0',
                                                     `files_with_bad_integrity` int DEFAULT '0',
                                                     `estado` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'IN_PROGRESS',
                                                     `estado_integrity` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'IN_PROGRESS',
                                                     `hash_alg` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                                     `estado_cambio_permisos` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'IN_PROGRESS',
                                                     `estado_clear_data` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'DELETING_ENTRIES',
                                                     `last_task` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'INTEGRITY',
                                                     `cron_tasks_launched` tinyint(1) DEFAULT '0',
                                                     `last_check_malwarescan` datetime DEFAULT NULL,
                                                     `files_scanned_malwarescan` int DEFAULT '0',
                                                     `suspicious_files` int DEFAULT '0',
                                                     `estado_malwarescan` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT 'IN_PROGRESS',
                                                     `last_online_check_malwarescan` datetime DEFAULT NULL,
                                                     `online_checked_files` int DEFAULT '0',
                                                     `online_checked_hashes` int DEFAULT '0',
                                                     `last_check_database` datetime DEFAULT NULL,
                                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_file_manager`
--

LOCK TABLES `jos_securitycheckpro_file_manager` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_file_manager` DISABLE KEYS */;
INSERT INTO `jos_securitycheckpro_file_manager` VALUES (1,NULL,NULL,0,0,0,0,'ENDED','ENDED',NULL,'ENDED','DELETING_ENTRIES','INTEGRITY',0,NULL,0,0,'ENDED',NULL,0,0,NULL);
/*!40000 ALTER TABLE `jos_securitycheckpro_file_manager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_logs`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_logs` (
                                             `id` int unsigned NOT NULL AUTO_INCREMENT,
                                             `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                             `geolocation` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '---',
                                             `username` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '---',
                                             `time` datetime NOT NULL,
                                             `tag_description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `description` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
                                             `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `uri` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `component` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '---',
                                             `marked` tinyint(1) DEFAULT '0',
                                             `original_string` mediumtext COLLATE utf8mb4_unicode_ci,
                                             PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_logs`
--

LOCK TABLES `jos_securitycheckpro_logs` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_logs` DISABLE KEYS */;
INSERT INTO `jos_securitycheckpro_logs` VALUES (1,'192.168.48.1','---','sysadmin','2023-04-06 15:39:52','SESSION_PROTECTION','User access attempt with one session active','SESSION_PROTECTION','/administrator/index.php?option=com_installer&view=manage','---',0,'VXNlcm5hbWU6IHN5c2FkbWlu');
/*!40000 ALTER TABLE `jos_securitycheckpro_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_online_checks`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_online_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_online_checks` (
                                                      `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                      `filename` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                      `files_checked` int DEFAULT '0',
                                                      `threats_found` int DEFAULT '0',
                                                      `scan_date` datetime DEFAULT NULL,
                                                      `infected_files` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_online_checks`
--

LOCK TABLES `jos_securitycheckpro_online_checks` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_online_checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro_online_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_own_logs`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_own_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_own_logs` (
                                                 `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                 `time` datetime NOT NULL,
                                                 `description` varchar(1200) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_own_logs`
--

LOCK TABLES `jos_securitycheckpro_own_logs` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_own_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro_own_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_rules`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_rules` (
                                              `id` int unsigned NOT NULL AUTO_INCREMENT,
                                              `group_id` int DEFAULT NULL,
                                              `rules_applied` tinyint(1) DEFAULT '0',
                                              `last_change` datetime DEFAULT NULL,
                                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_rules`
--

LOCK TABLES `jos_securitycheckpro_rules` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_rules_logs`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_rules_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_rules_logs` (
                                                   `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                   `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                   `username` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                   `last_entry` datetime DEFAULT NULL,
                                                   `reason` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_rules_logs`
--

LOCK TABLES `jos_securitycheckpro_rules_logs` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_rules_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro_rules_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_sessions`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_sessions` (
                                                 `userid` int unsigned NOT NULL,
                                                 `session_id` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `username` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `user_agent` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_sessions`
--

LOCK TABLES `jos_securitycheckpro_sessions` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_sessions` DISABLE KEYS */;
INSERT INTO `jos_securitycheckpro_sessions` VALUES (650,'ed8d274ee63cbf90cd461e914474f29e','sysadmin','192.168.48.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36');
/*!40000 ALTER TABLE `jos_securitycheckpro_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_storage`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_storage` (
                                                `storage_key` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
                                                `storage_value` longtext COLLATE utf8mb3_unicode_ci NOT NULL,
                                                PRIMARY KEY (`storage_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_storage`
--

LOCK TABLES `jos_securitycheckpro_storage` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_storage` DISABLE KEYS */;
INSERT INTO `jos_securitycheckpro_storage` VALUES ('locked','0');
/*!40000 ALTER TABLE `jos_securitycheckpro_storage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_trackactions`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_trackactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_trackactions` (
                                                     `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                     `message` text COLLATE utf8mb4_unicode_ci,
                                                     `log_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                                                     `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                                     `user_id` int NOT NULL DEFAULT '0',
                                                     `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.0.0.0',
                                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_trackactions`
--

LOCK TABLES `jos_securitycheckpro_trackactions` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_trackactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro_trackactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_trackactions_extensions`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_trackactions_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_trackactions_extensions` (
                                                                `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                                `extension` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                                                PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_trackactions_extensions`
--

LOCK TABLES `jos_securitycheckpro_trackactions_extensions` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_trackactions_extensions` DISABLE KEYS */;
INSERT INTO `jos_securitycheckpro_trackactions_extensions` VALUES (1,'com_banners'),(2,'com_cache'),(3,'com_categories'),(4,'com_config'),(5,'com_contact'),(6,'com_content'),(7,'com_installer'),(8,'com_media'),(9,'com_menus'),(10,'com_messages'),(11,'com_modules'),(12,'com_newsfeeds'),(13,'com_plugins'),(14,'com_redirect'),(15,'com_tags'),(16,'com_templates'),(17,'com_users');
/*!40000 ALTER TABLE `jos_securitycheckpro_trackactions_extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_trackactions_tables_data`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_trackactions_tables_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_trackactions_tables_data` (
                                                                 `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                                 `type_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                                                 `type_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                                                 `title_holder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                                                 `table_values` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_trackactions_tables_data`
--

LOCK TABLES `jos_securitycheckpro_trackactions_tables_data` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_trackactions_tables_data` DISABLE KEYS */;
INSERT INTO `jos_securitycheckpro_trackactions_tables_data` VALUES (1,'article','com_content.article','title','{\"table_type\":\"Content\",\"table_prefix\":\"JTable\"}'),(2,'article','com_content.form','title','{\"table_type\":\"Content\",\"table_prefix\":\"JTable\"}'),(3,'banner','com_banners.banner','name','{\"table_type\":\"Banner\",\"table_prefix\":\"BannersTable\"}'),(4,'user_note','com_users.note','subject','{\"table_type\":\"Note\",\"table_prefix\":\"UsersTable\"}'),(5,'media','com_media.file','name','{\"table_type\":\"\",\"table_prefix\":\"\"}'),(6,'category','com_categories.category','title','{\"table_type\":\"Category\",\"table_prefix\":\"JTable\"}'),(7,'menu','com_menus.menu','title','{\"table_type\":\"Menu\",\"table_prefix\":\"JTable\"}'),(8,'menu_item','com_menus.item','title','{\"table_type\":\"Menu\",\"table_prefix\":\"JTable\"}'),(9,'newsfeed','com_newsfeeds.newsfeed','name','{\"table_type\":\"Newsfeed\",\"table_prefix\":\"NewsfeedsTable\"}'),(10,'link','com_redirect.link','old_url','{\"table_type\":\"Link\",\"table_prefix\":\"RedirectTable\"}'),(11,'tag','com_tags.tag','title','{\"table_type\":\"Tag\",\"table_prefix\":\"TagsTable\"}'),(12,'style','com_templates.style','title','{\"table_type\":\"\",\"table_prefix\":\"\"}'),(13,'plugin','com_plugins.plugin','name','{\"table_type\":\"Extension\",\"table_prefix\":\"JTable\"}'),(14,'component_config','com_config.component','name','{\"table_type\":\"\",\"table_prefix\":\"\"}'),(15,'contact','com_contact.contact','name','{\"table_type\":\"Contact\",\"table_prefix\":\"ContactTable\"}'),(16,'module','com_modules.module','title','{\"table_type\":\"Module\",\"table_prefix\":\"JTable\"}'),(17,'access_level','com_users.level','title','{\"table_type\":\"Viewlevel\",\"table_prefix\":\"JTable\"}'),(18,'banner_client','com_banners.client','name','{\"table_type\":\"Client\",\"table_prefix\":\"BannersTable\"}');
/*!40000 ALTER TABLE `jos_securitycheckpro_trackactions_tables_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_update_database`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_update_database`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_update_database` (
                                                        `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                        `version` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                                        `last_check` datetime DEFAULT NULL,
                                                        `message` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                                        PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_update_database`
--

LOCK TABLES `jos_securitycheckpro_update_database` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_update_database` DISABLE KEYS */;
INSERT INTO `jos_securitycheckpro_update_database` VALUES (1,'1.3.8',NULL,NULL);
/*!40000 ALTER TABLE `jos_securitycheckpro_update_database` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_url_inspector_logs`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_url_inspector_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_url_inspector_logs` (
                                                           `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                           `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                           `uri` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                                           `forbidden_words` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                           `date_added` datetime DEFAULT NULL,
                                                           PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_url_inspector_logs`
--

LOCK TABLES `jos_securitycheckpro_url_inspector_logs` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_url_inspector_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro_url_inspector_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_users_control`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_users_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_users_control` (
                                                      `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                      `users` text COLLATE utf8mb4_unicode_ci NOT NULL,
                                                      `contador` int unsigned NOT NULL,
                                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_users_control`
--

LOCK TABLES `jos_securitycheckpro_users_control` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_users_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro_users_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_vuln_components`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_vuln_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_vuln_components` (
                                                        `id` int unsigned NOT NULL AUTO_INCREMENT,
                                                        `Product` text COLLATE utf8mb4_unicode_ci NOT NULL,
                                                        `vuln_id` int unsigned NOT NULL,
                                                        PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_securitycheckpro_vuln_components`
--

LOCK TABLES `jos_securitycheckpro_vuln_components` WRITE;
/*!40000 ALTER TABLE `jos_securitycheckpro_vuln_components` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_securitycheckpro_vuln_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_securitycheckpro_whitelist`
--

DROP TABLE IF EXISTS `jos_securitycheckpro_whitelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_securitycheckpro_whitelist` (
                                                  `ip` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
                                                  PRIMARY KEY (`ip`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jos_session`
--

DROP TABLE IF EXISTS `jos_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_session` (
  `session_id` varbinary(192) NOT NULL,
  `client_id` tinyint unsigned DEFAULT NULL,
  `guest` tinyint unsigned DEFAULT '1',
  `time` int NOT NULL DEFAULT '0',
  `data` mediumtext COLLATE utf8mb4_unicode_ci,
  `userid` int DEFAULT '0',
  `username` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`session_id`),
  KEY `userid` (`userid`),
  KEY `time` (`time`),
  KEY `client_id_guest` (`client_id`,`guest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_session`
--

LOCK TABLES `jos_session` WRITE;
/*!40000 ALTER TABLE `jos_session` DISABLE KEYS */;
INSERT INTO `jos_session` VALUES (_binary '49a672096233c88da75ce551b50fc244',1,0,1680788912,'joomla|s:768:\"TzoyNDoiSm9vbWxhXFJlZ2lzdHJ5XFJlZ2lzdHJ5IjozOntzOjc6IgAqAGRhdGEiO086ODoic3RkQ2xhc3MiOjQ6e3M6Nzoic2Vzc2lvbiI7Tzo4OiJzdGRDbGFzcyI6Mzp7czo3OiJjb3VudGVyIjtpOjc7czo1OiJ0aW1lciI7Tzo4OiJzdGRDbGFzcyI6Mzp7czo1OiJzdGFydCI7aToxNjgwNzg4OTA2O3M6NDoibGFzdCI7aToxNjgwNzg4OTEyO3M6Mzoibm93IjtpOjE2ODA3ODg5MTI7fXM6NToidG9rZW4iO3M6MzI6ImI1ZTUyN2RiNWUzYjJhYjgyMjVhY2UyMjcxODY1MjRkIjt9czo4OiJyZWdpc3RyeSI7TzoyNDoiSm9vbWxhXFJlZ2lzdHJ5XFJlZ2lzdHJ5IjozOntzOjc6IgAqAGRhdGEiO086ODoic3RkQ2xhc3MiOjA6e31zOjE0OiIAKgBpbml0aWFsaXplZCI7YjowO3M6OToic2VwYXJhdG9yIjtzOjE6Ii4iO31zOjQ6InVzZXIiO086MjA6Ikpvb21sYVxDTVNcVXNlclxVc2VyIjoxOntzOjI6ImlkIjtpOjY1MDt9czo5OiJjb21fdXNlcnMiO086ODoic3RkQ2xhc3MiOjE6e3M6MTE6Im1mYV9jaGVja2VkIjtpOjE7fX1zOjE0OiIAKgBpbml0aWFsaXplZCI7YjowO3M6OToic2VwYXJhdG9yIjtzOjE6Ii4iO30=\";',650,'sysadmin');
/*!40000 ALTER TABLE `jos_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_tags`
--

DROP TABLE IF EXISTS `jos_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_tags` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned NOT NULL DEFAULT '0',
  `lft` int NOT NULL DEFAULT '0',
  `rgt` int NOT NULL DEFAULT '0',
  `level` int unsigned NOT NULL DEFAULT '0',
  `path` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` tinyint NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `access` int unsigned NOT NULL DEFAULT '0',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadesc` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'The keywords for the page.',
  `metadata` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL,
  `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `modified_user_id` int unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL,
  `images` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `urls` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `hits` int unsigned NOT NULL DEFAULT '0',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `publish_up` datetime DEFAULT NULL,
  `publish_down` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tag_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`(100)),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`(100)),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_tags`
--

LOCK TABLES `jos_tags` WRITE;
/*!40000 ALTER TABLE `jos_tags` DISABLE KEYS */;
INSERT INTO `jos_tags` VALUES (1,0,0,1,0,'','ROOT','root','','',1,NULL,NULL,1,'','','','',650,'2023-04-06 13:47:52','',650,'2023-04-06 13:47:52','','',0,'*',1,NULL,NULL);
/*!40000 ALTER TABLE `jos_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_template_overrides`
--

DROP TABLE IF EXISTS `jos_template_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_template_overrides` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `hash_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `extension_id` int DEFAULT '0',
  `state` tinyint NOT NULL DEFAULT '0',
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `client_id` tinyint unsigned NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL,
  `modified_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_template` (`template`),
  KEY `idx_extension_id` (`extension_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_template_overrides`
--

LOCK TABLES `jos_template_overrides` WRITE;
/*!40000 ALTER TABLE `jos_template_overrides` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_template_overrides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_template_styles`
--

DROP TABLE IF EXISTS `jos_template_styles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_template_styles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `client_id` tinyint unsigned NOT NULL DEFAULT '0',
  `home` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `inheritable` tinyint NOT NULL DEFAULT '0',
  `parent` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_template` (`template`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_client_id_home` (`client_id`,`home`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_template_styles`
--

LOCK TABLES `jos_template_styles` WRITE;
/*!40000 ALTER TABLE `jos_template_styles` DISABLE KEYS */;
INSERT INTO `jos_template_styles` VALUES (10,'atum',1,'1','Atum - Default',1,'','{\"hue\":\"hsl(214, 63%, 20%)\",\"bg-light\":\"#f0f4fb\",\"text-dark\":\"#495057\",\"text-light\":\"#ffffff\",\"link-color\":\"#2a69b8\",\"special-color\":\"#001b4c\",\"monochrome\":\"0\",\"loginLogo\":\"\",\"loginLogoAlt\":\"\",\"logoBrandLarge\":\"\",\"logoBrandLargeAlt\":\"\",\"logoBrandSmall\":\"\",\"logoBrandSmallAlt\":\"\"}'),(11,'cassiopeia',0,'1','Cassiopeia - Default',1,'','{\"brand\":\"1\",\"logoFile\":\"\",\"siteTitle\":\"\",\"siteDescription\":\"\",\"useFontScheme\":\"0\",\"colorName\":\"colors_standard\",\"fluidContainer\":\"0\",\"stickyHeader\":0,\"backTop\":0}');
/*!40000 ALTER TABLE `jos_template_styles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_ucm_base`
--

DROP TABLE IF EXISTS `jos_ucm_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_ucm_base` (
  `ucm_id` int unsigned NOT NULL,
  `ucm_item_id` int NOT NULL,
  `ucm_type_id` int NOT NULL,
  `ucm_language_id` int NOT NULL,
  PRIMARY KEY (`ucm_id`),
  KEY `idx_ucm_item_id` (`ucm_item_id`),
  KEY `idx_ucm_type_id` (`ucm_type_id`),
  KEY `idx_ucm_language_id` (`ucm_language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_ucm_base`
--

LOCK TABLES `jos_ucm_base` WRITE;
/*!40000 ALTER TABLE `jos_ucm_base` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_ucm_base` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_ucm_content`
--

DROP TABLE IF EXISTS `jos_ucm_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_ucm_content` (
  `core_content_id` int unsigned NOT NULL AUTO_INCREMENT,
  `core_type_alias` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'FK to the content types table',
  `core_title` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `core_alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `core_body` mediumtext COLLATE utf8mb4_unicode_ci,
  `core_state` tinyint NOT NULL DEFAULT '0',
  `core_checked_out_time` datetime DEFAULT NULL,
  `core_checked_out_user_id` int unsigned DEFAULT NULL,
  `core_access` int unsigned NOT NULL DEFAULT '0',
  `core_params` text COLLATE utf8mb4_unicode_ci,
  `core_featured` tinyint unsigned NOT NULL DEFAULT '0',
  `core_metadata` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'JSON encoded metadata properties.',
  `core_created_user_id` int unsigned NOT NULL DEFAULT '0',
  `core_created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `core_created_time` datetime NOT NULL,
  `core_modified_user_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'Most recent user that modified',
  `core_modified_time` datetime NOT NULL,
  `core_language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `core_publish_up` datetime DEFAULT NULL,
  `core_publish_down` datetime DEFAULT NULL,
  `core_content_item_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'ID from the individual type table',
  `asset_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `core_images` text COLLATE utf8mb4_unicode_ci,
  `core_urls` text COLLATE utf8mb4_unicode_ci,
  `core_hits` int unsigned NOT NULL DEFAULT '0',
  `core_version` int unsigned NOT NULL DEFAULT '1',
  `core_ordering` int NOT NULL DEFAULT '0',
  `core_metakey` text COLLATE utf8mb4_unicode_ci,
  `core_metadesc` text COLLATE utf8mb4_unicode_ci,
  `core_catid` int unsigned NOT NULL DEFAULT '0',
  `core_type_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`core_content_id`),
  KEY `tag_idx` (`core_state`,`core_access`),
  KEY `idx_access` (`core_access`),
  KEY `idx_alias` (`core_alias`(100)),
  KEY `idx_language` (`core_language`),
  KEY `idx_title` (`core_title`(100)),
  KEY `idx_modified_time` (`core_modified_time`),
  KEY `idx_created_time` (`core_created_time`),
  KEY `idx_content_type` (`core_type_alias`(100)),
  KEY `idx_core_modified_user_id` (`core_modified_user_id`),
  KEY `idx_core_checked_out_user_id` (`core_checked_out_user_id`),
  KEY `idx_core_created_user_id` (`core_created_user_id`),
  KEY `idx_core_type_id` (`core_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contains core content data in name spaced fields';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_ucm_content`
--

LOCK TABLES `jos_ucm_content` WRITE;
/*!40000 ALTER TABLE `jos_ucm_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_ucm_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_update_sites`
--

DROP TABLE IF EXISTS `jos_update_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_update_sites` (
  `update_site_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `location` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` int DEFAULT '0',
  `last_check_timestamp` bigint DEFAULT '0',
  `extra_query` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  PRIMARY KEY (`update_site_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Update Sites';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_update_sites`
--

LOCK TABLES `jos_update_sites` WRITE;
/*!40000 ALTER TABLE `jos_update_sites` DISABLE KEYS */;
INSERT INTO `jos_update_sites` VALUES (1,'Joomla! Core','collection','https://update.joomla.org/core/list.xml',1,1680788907,'',NULL,NULL),(2,'Accredited Joomla! Translations','collection','https://update.joomla.org/language/translationlist_4.xml',1,1680788893,'',NULL,NULL),(3,'Joomla! Update Component','extension','https://update.joomla.org/core/extensions/com_joomlaupdate.xml',1,1680788912,'',NULL,NULL);
/*!40000 ALTER TABLE `jos_update_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_update_sites_extensions`
--

DROP TABLE IF EXISTS `jos_update_sites_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_update_sites_extensions` (
  `update_site_id` int NOT NULL DEFAULT '0',
  `extension_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`update_site_id`,`extension_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Links extensions to update sites';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_update_sites_extensions`
--

LOCK TABLES `jos_update_sites_extensions` WRITE;
/*!40000 ALTER TABLE `jos_update_sites_extensions` DISABLE KEYS */;
INSERT INTO `jos_update_sites_extensions` VALUES (1,224),(2,225),(2,232),(3,24);
/*!40000 ALTER TABLE `jos_update_sites_extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_updates`
--

DROP TABLE IF EXISTS `jos_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_updates` (
  `update_id` int NOT NULL AUTO_INCREMENT,
  `update_site_id` int DEFAULT '0',
  `extension_id` int DEFAULT '0',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `element` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `folder` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `client_id` tinyint DEFAULT '0',
  `version` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `detailsurl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `infourl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `changelogurl` text COLLATE utf8mb4_unicode_ci,
  `extra_query` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`update_id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Available Updates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_updates`
--

LOCK TABLES `jos_updates` WRITE;
/*!40000 ALTER TABLE `jos_updates` DISABLE KEYS */;
INSERT INTO `jos_updates` VALUES (52,2,0,'Afrikaans','','pkg_af-ZA','package','',0,'4.2.8.1','','https://update.joomla.org/language/details4/af-ZA_details.xml','','',''),(53,2,0,'Arabic Unitag','','pkg_ar-AA','package','',0,'4.0.2.1','','https://update.joomla.org/language/details4/ar-AA_details.xml','','',''),(54,2,0,'Bulgarian','','pkg_bg-BG','package','',0,'4.2.7.1','','https://update.joomla.org/language/details4/bg-BG_details.xml','','',''),(55,2,0,'Catalan','','pkg_ca-ES','package','',0,'4.0.4.2','','https://update.joomla.org/language/details4/ca-ES_details.xml','','',''),(56,2,0,'Chinese, Simplified','','pkg_zh-CN','package','',0,'4.2.0.1','','https://update.joomla.org/language/details4/zh-CN_details.xml','','',''),(57,2,0,'Chinese, Traditional','','pkg_zh-TW','package','',0,'4.2.3.1','','https://update.joomla.org/language/details4/zh-TW_details.xml','','',''),(58,2,0,'Czech','','pkg_cs-CZ','package','',0,'4.2.0.1','','https://update.joomla.org/language/details4/cs-CZ_details.xml','','',''),(59,2,0,'Danish','','pkg_da-DK','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/da-DK_details.xml','','',''),(60,2,0,'Dutch','','pkg_nl-NL','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/nl-NL_details.xml','','',''),(61,2,0,'English, Australia','','pkg_en-AU','package','',0,'4.2.8.2','','https://update.joomla.org/language/details4/en-AU_details.xml','','',''),(62,2,0,'English, Canada','','pkg_en-CA','package','',0,'4.2.8.1','','https://update.joomla.org/language/details4/en-CA_details.xml','','',''),(63,2,0,'English, New Zealand','','pkg_en-NZ','package','',0,'4.2.8.1','','https://update.joomla.org/language/details4/en-NZ_details.xml','','',''),(64,2,0,'English, USA','','pkg_en-US','package','',0,'4.2.8.1','','https://update.joomla.org/language/details4/en-US_details.xml','','',''),(65,2,0,'Estonian','','pkg_et-EE','package','',0,'4.2.7.3','','https://update.joomla.org/language/details4/et-EE_details.xml','','',''),(66,2,0,'Finnish','','pkg_fi-FI','package','',0,'4.1.1.2','','https://update.joomla.org/language/details4/fi-FI_details.xml','','',''),(67,2,0,'Flemish','','pkg_nl-BE','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/nl-BE_details.xml','','',''),(68,2,0,'Georgian','','pkg_ka-GE','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/ka-GE_details.xml','','',''),(69,2,0,'German','','pkg_de-DE','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/de-DE_details.xml','','',''),(70,2,0,'German, Austria','','pkg_de-AT','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/de-AT_details.xml','','',''),(71,2,0,'German, Liechtenstein','','pkg_de-LI','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/de-LI_details.xml','','',''),(72,2,0,'German, Luxembourg','','pkg_de-LU','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/de-LU_details.xml','','',''),(73,2,0,'German, Switzerland','','pkg_de-CH','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/de-CH_details.xml','','',''),(74,2,0,'Greek','','pkg_el-GR','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/el-GR_details.xml','','',''),(75,2,0,'Hungarian','','pkg_hu-HU','package','',0,'4.2.7.1','','https://update.joomla.org/language/details4/hu-HU_details.xml','','',''),(76,2,0,'Irish','','pkg_ga-IE','package','',0,'4.2.8.1','','https://update.joomla.org/language/details4/ga-IE_details.xml','','',''),(77,2,0,'Italian','','pkg_it-IT','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/it-IT_details.xml','','',''),(78,2,0,'Japanese','','pkg_ja-JP','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/ja-JP_details.xml','','',''),(79,2,0,'Kazakh','','pkg_kk-KZ','package','',0,'4.1.2.1','','https://update.joomla.org/language/details4/kk-KZ_details.xml','','',''),(80,2,0,'Latvian','','pkg_lv-LV','package','',0,'4.3.0.1','','https://update.joomla.org/language/details4/lv-LV_details.xml','','',''),(81,2,0,'Lithuanian','','pkg_lt-LT','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/lt-LT_details.xml','','',''),(82,2,0,'Macedonian','','pkg_mk-MK','package','',0,'4.2.4.1','','https://update.joomla.org/language/details4/mk-MK_details.xml','','',''),(83,2,0,'Norwegian Bokmål','','pkg_nb-NO','package','',0,'4.0.1.1','','https://update.joomla.org/language/details4/nb-NO_details.xml','','',''),(84,2,0,'Persian Farsi','','pkg_fa-IR','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/fa-IR_details.xml','','',''),(85,2,0,'Polish','','pkg_pl-PL','package','',0,'4.2.8.2','','https://update.joomla.org/language/details4/pl-PL_details.xml','','',''),(86,2,0,'Portuguese, Brazil','','pkg_pt-BR','package','',0,'4.0.3.1','','https://update.joomla.org/language/details4/pt-BR_details.xml','','',''),(87,2,0,'Portuguese, Portugal','','pkg_pt-PT','package','',0,'4.0.0-rc4.2','','https://update.joomla.org/language/details4/pt-PT_details.xml','','',''),(88,2,0,'Romanian','','pkg_ro-RO','package','',0,'4.2.7.1','','https://update.joomla.org/language/details4/ro-RO_details.xml','','',''),(89,2,0,'Russian','','pkg_ru-RU','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/ru-RU_details.xml','','',''),(90,2,0,'Serbian, Cyrillic','','pkg_sr-RS','package','',0,'4.2.7.1','','https://update.joomla.org/language/details4/sr-RS_details.xml','','',''),(91,2,0,'Serbian, Latin','','pkg_sr-YU','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/sr-YU_details.xml','','',''),(92,2,0,'Slovak','','pkg_sk-SK','package','',0,'4.0.6.1','','https://update.joomla.org/language/details4/sk-SK_details.xml','','',''),(93,2,0,'Slovenian','','pkg_sl-SI','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/sl-SI_details.xml','','',''),(94,2,0,'Spanish','','pkg_es-ES','package','',0,'4.2.3.1','','https://update.joomla.org/language/details4/es-ES_details.xml','','',''),(95,2,0,'Swedish','','pkg_sv-SE','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/sv-SE_details.xml','','',''),(96,2,0,'Tamil, India','','pkg_ta-IN','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/ta-IN_details.xml','','',''),(97,2,0,'Thai','','pkg_th-TH','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/th-TH_details.xml','','',''),(98,2,0,'Turkish','','pkg_tr-TR','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/tr-TR_details.xml','','',''),(99,2,0,'Ukrainian','','pkg_uk-UA','package','',0,'4.2.5.1','','https://update.joomla.org/language/details4/uk-UA_details.xml','','',''),(100,2,0,'Vietnamese','','pkg_vi-VN','package','',0,'4.2.2.1','','https://update.joomla.org/language/details4/vi-VN_details.xml','','',''),(101,2,0,'Welsh','','pkg_cy-GB','package','',0,'4.2.9.1','','https://update.joomla.org/language/details4/cy-GB_details.xml','','','');
/*!40000 ALTER TABLE `jos_updates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_user_keys`
--

DROP TABLE IF EXISTS `jos_user_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_user_keys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `series` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uastring` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `series` (`series`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_user_keys`
--

LOCK TABLES `jos_user_keys` WRITE;
/*!40000 ALTER TABLE `jos_user_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_user_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_user_mfa`
--

DROP TABLE IF EXISTS `jos_user_mfa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_user_mfa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `method` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default` tinyint NOT NULL DEFAULT '0',
  `options` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_on` datetime NOT NULL,
  `last_used` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multi-factor Authentication settings';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_user_mfa`
--

LOCK TABLES `jos_user_mfa` WRITE;
/*!40000 ALTER TABLE `jos_user_mfa` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_user_mfa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_user_notes`
--

DROP TABLE IF EXISTS `jos_user_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_user_notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `catid` int unsigned NOT NULL DEFAULT '0',
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` tinyint NOT NULL DEFAULT '0',
  `checked_out` int unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `created_user_id` int unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL,
  `modified_user_id` int unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL,
  `review_time` datetime DEFAULT NULL,
  `publish_up` datetime DEFAULT NULL,
  `publish_down` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_category_id` (`catid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_user_notes`
--

LOCK TABLES `jos_user_notes` WRITE;
/*!40000 ALTER TABLE `jos_user_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_user_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_user_profiles`
--

DROP TABLE IF EXISTS `jos_user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_user_profiles` (
  `user_id` int NOT NULL,
  `profile_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordering` int NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_user_id_profile_key` (`user_id`,`profile_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Simple user profile storage table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_user_profiles`
--

LOCK TABLES `jos_user_profiles` WRITE;
/*!40000 ALTER TABLE `jos_user_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_user_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_user_usergroup_map`
--

DROP TABLE IF EXISTS `jos_user_usergroup_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_user_usergroup_map` (
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to #__users.id',
  `group_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to #__usergroups.id',
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_user_usergroup_map`
--

LOCK TABLES `jos_user_usergroup_map` WRITE;
/*!40000 ALTER TABLE `jos_user_usergroup_map` DISABLE KEYS */;
INSERT INTO `jos_user_usergroup_map` VALUES (650,8);
/*!40000 ALTER TABLE `jos_user_usergroup_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_usergroups`
--

DROP TABLE IF EXISTS `jos_usergroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_usergroups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'Adjacency List Reference Id',
  `lft` int NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_usergroup_parent_title_lookup` (`parent_id`,`title`),
  KEY `idx_usergroup_title_lookup` (`title`),
  KEY `idx_usergroup_adjacency_lookup` (`parent_id`),
  KEY `idx_usergroup_nested_set_lookup` (`lft`,`rgt`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_usergroups`
--

LOCK TABLES `jos_usergroups` WRITE;
/*!40000 ALTER TABLE `jos_usergroups` DISABLE KEYS */;
INSERT INTO `jos_usergroups` VALUES (1,0,1,18,'Public'),(2,1,8,15,'Registered'),(3,2,9,14,'Author'),(4,3,10,13,'Editor'),(5,4,11,12,'Publisher'),(6,1,4,7,'Manager'),(7,6,5,6,'Administrator'),(8,1,16,17,'Super Users'),(9,1,2,3,'Guest');
/*!40000 ALTER TABLE `jos_usergroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_users`
--

DROP TABLE IF EXISTS `jos_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `username` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `block` tinyint NOT NULL DEFAULT '0',
  `sendEmail` tinyint DEFAULT '0',
  `registerDate` datetime NOT NULL,
  `lastvisitDate` datetime DEFAULT NULL,
  `activation` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastResetTime` datetime DEFAULT NULL COMMENT 'Date of last password reset',
  `resetCount` int NOT NULL DEFAULT '0' COMMENT 'Count of password resets since lastResetTime',
  `otpKey` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Two factor authentication encrypted keys',
  `otep` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Backup Codes',
  `requireReset` tinyint NOT NULL DEFAULT '0' COMMENT 'Require user to reset password on next login',
  `authProvider` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name of used authentication plugin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_username` (`username`),
  KEY `idx_name` (`name`(100)),
  KEY `idx_block` (`block`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=651 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_users`
--

LOCK TABLES `jos_users` WRITE;
/*!40000 ALTER TABLE `jos_users` DISABLE KEYS */;
INSERT INTO `jos_users` VALUES (650,'Administrator','sysadmin','sysadmin@emundus.io','$2y$10$J.xsN5O4Ta9IUfAUVpkwzO7AWhvfzMjf5RZm4wewBdZhnLlPkXvbm',0,1,'2023-04-06 13:47:54','2023-04-06 13:48:30','0','',NULL,0,'','',0,'');
/*!40000 ALTER TABLE `jos_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_viewlevels`
--

DROP TABLE IF EXISTS `jos_viewlevels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_viewlevels` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `ordering` int NOT NULL DEFAULT '0',
  `rules` varchar(5120) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'JSON encoded access control.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_assetgroup_title_lookup` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_viewlevels`
--

LOCK TABLES `jos_viewlevels` WRITE;
/*!40000 ALTER TABLE `jos_viewlevels` DISABLE KEYS */;
INSERT INTO `jos_viewlevels` VALUES (1,'Public',0,'[1]'),(2,'Registered',2,'[6,2,8]'),(3,'Special',3,'[6,3,8]'),(5,'Guest',1,'[9]'),(6,'Super Users',4,'[8]');
/*!40000 ALTER TABLE `jos_viewlevels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_webauthn_credentials`
--

DROP TABLE IF EXISTS `jos_webauthn_credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_webauthn_credentials` (
  `id` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Credential ID',
  `user_id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'User handle',
  `label` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Human readable label',
  `credential` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Credential source data, JSON format',
  PRIMARY KEY (`id`(100)),
  KEY `user_id` (`user_id`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_webauthn_credentials`
--

LOCK TABLES `jos_webauthn_credentials` WRITE;
/*!40000 ALTER TABLE `jos_webauthn_credentials` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_webauthn_credentials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_workflow_associations`
--

DROP TABLE IF EXISTS `jos_workflow_associations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_workflow_associations` (
  `item_id` int NOT NULL DEFAULT '0' COMMENT 'Extension table id value',
  `stage_id` int NOT NULL COMMENT 'Foreign Key to #__workflow_stages.id',
  `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`item_id`,`extension`),
  KEY `idx_item_stage_extension` (`item_id`,`stage_id`,`extension`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_stage_id` (`stage_id`),
  KEY `idx_extension` (`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_workflow_associations`
--

LOCK TABLES `jos_workflow_associations` WRITE;
/*!40000 ALTER TABLE `jos_workflow_associations` DISABLE KEYS */;
/*!40000 ALTER TABLE `jos_workflow_associations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_workflow_stages`
--

DROP TABLE IF EXISTS `jos_workflow_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_workflow_stages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT '0',
  `ordering` int NOT NULL DEFAULT '0',
  `workflow_id` int NOT NULL,
  `published` tinyint NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `default` tinyint NOT NULL DEFAULT '0',
  `checked_out_time` datetime DEFAULT NULL,
  `checked_out` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_workflow_id` (`workflow_id`),
  KEY `idx_checked_out` (`checked_out`),
  KEY `idx_title` (`title`(191)),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_default` (`default`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_workflow_stages`
--

LOCK TABLES `jos_workflow_stages` WRITE;
/*!40000 ALTER TABLE `jos_workflow_stages` DISABLE KEYS */;
INSERT INTO `jos_workflow_stages` VALUES (1,57,1,1,1,'COM_WORKFLOW_BASIC_STAGE','',1,NULL,NULL);
/*!40000 ALTER TABLE `jos_workflow_stages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_workflow_transitions`
--

DROP TABLE IF EXISTS `jos_workflow_transitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_workflow_transitions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT '0',
  `ordering` int NOT NULL DEFAULT '0',
  `workflow_id` int NOT NULL,
  `published` tinyint NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_stage_id` int NOT NULL,
  `to_stage_id` int NOT NULL,
  `options` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `checked_out` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`(191)),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_checked_out` (`checked_out`),
  KEY `idx_from_stage_id` (`from_stage_id`),
  KEY `idx_to_stage_id` (`to_stage_id`),
  KEY `idx_workflow_id` (`workflow_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_workflow_transitions`
--

LOCK TABLES `jos_workflow_transitions` WRITE;
/*!40000 ALTER TABLE `jos_workflow_transitions` DISABLE KEYS */;
INSERT INTO `jos_workflow_transitions` VALUES (1,58,1,1,1,'UNPUBLISH','',-1,1,'{\"publishing\":\"0\"}',NULL,NULL),(2,59,2,1,1,'PUBLISH','',-1,1,'{\"publishing\":\"1\"}',NULL,NULL),(3,60,3,1,1,'TRASH','',-1,1,'{\"publishing\":\"-2\"}',NULL,NULL),(4,61,4,1,1,'ARCHIVE','',-1,1,'{\"publishing\":\"2\"}',NULL,NULL),(5,62,5,1,1,'FEATURE','',-1,1,'{\"featuring\":\"1\"}',NULL,NULL),(6,63,6,1,1,'UNFEATURE','',-1,1,'{\"featuring\":\"0\"}',NULL,NULL),(7,64,7,1,1,'PUBLISH_AND_FEATURE','',-1,1,'{\"publishing\":\"1\",\"featuring\":\"1\"}',NULL,NULL);
/*!40000 ALTER TABLE `jos_workflow_transitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jos_workflows`
--

DROP TABLE IF EXISTS `jos_workflows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jos_workflows` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT '0',
  `published` tinyint NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default` tinyint NOT NULL DEFAULT '0',
  `ordering` int NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `modified_by` int NOT NULL DEFAULT '0',
  `checked_out_time` datetime DEFAULT NULL,
  `checked_out` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_title` (`title`(191)),
  KEY `idx_extension` (`extension`),
  KEY `idx_default` (`default`),
  KEY `idx_created` (`created`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_modified` (`modified`),
  KEY `idx_modified_by` (`modified_by`),
  KEY `idx_checked_out` (`checked_out`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jos_workflows`
--

LOCK TABLES `jos_workflows` WRITE;
/*!40000 ALTER TABLE `jos_workflows` DISABLE KEYS */;
INSERT INTO `jos_workflows` VALUES (1,56,1,'COM_WORKFLOW_BASIC_WORKFLOW','','com_content.article',1,1,'2023-04-06 13:47:53',650,'2023-04-06 13:47:53',650,NULL,NULL);
/*!40000 ALTER TABLE `jos_workflows` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-04-06 15:49:46
