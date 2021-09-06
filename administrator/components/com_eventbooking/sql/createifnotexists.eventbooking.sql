CREATE TABLE IF NOT EXISTS `#__eb_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `number_events` int(11) NOT NULL DEFAULT '0',
  `event_ids` tinytext,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `discount_type` tinyint(4) NOT NULL DEFAULT '1',
  `from_date` datetime DEFAULT NULL,
  `to_date` datetime DEFAULT NULL,
  `times` int(11) NOT NULL DEFAULT '0',
  `used` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_discount_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_id` int(11) NOT NULL DEFAULT '0',
  `event_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_type` varchar(50) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `sent_to` tinyint(4) NOT NULL DEFAULT '0',
  `email` varchar(100) DEFAULT '0',
  `subject` varchar(255) DEFAULT NULL,
  `body` text,
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_ticket_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(11) DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `discount_rules` text,
  `price` decimal(10,2) DEFAULT 0.00,
  `capacity` int(11) DEFAULT 0,
  `weight` int(11) NOT NULL DEFAULT 1,
  `max_tickets_per_booking` int(11) NOT NULL DEFAULT 0,
  `parent_ticket_type_id` int(11) NOT NULL DEFAULT 0,
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(11) NOT NULL DEFAULT 1,
  `ordering` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_registrant_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registrant_id` int(11) DEFAULT NULL,
  `ticket_type_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `#__eb_field_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_coupon_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `message_key` VARCHAR(50) NULL,
  `message` TEXT NULL,
  PRIMARY KEY(`id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `md5_key` varchar(32) DEFAULT NULL,
  `query` text,
  PRIMARY KEY (`id`),
  KEY `idx_md5_key` (`md5_key`(32))
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_coupon_categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) DEFAULT '0',
  `category_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_speakers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(10) UNSIGNED DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `description` TEXT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_sponsors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(10) UNSIGNED DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,  
  `logo` varchar(255) DEFAULT NULL,  
  `website` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
   KEY `idx_event_id` (`event_id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_agendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(10) UNSIGNED DEFAULT '0',
  `time` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `creation_date` varchar(50) DEFAULT NULL,
  `copyright` varchar(255) DEFAULT NULL,
  `license` varchar(255) DEFAULT NULL,
  `author_email` varchar(50) DEFAULT NULL,
  `author_url` varchar(50) DEFAULT NULL,
  `version` varchar(20) DEFAULT NULL,
  `description` text,
  `params` text,
  `ordering` int(11) DEFAULT NULL,
  `published` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_published` (`published`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_galleries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(10) UNSIGNED DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `image` VARCHAR (255),
  `ordering` int(11) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_event_speakers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) DEFAULT NULL,
  `speaker_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_speaker_id` (`speaker_id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_event_sponsors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) DEFAULT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_sponsor_id` (`sponsor_id`)
) CHARACTER SET `utf8`;