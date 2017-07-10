DROP TABLE IF EXISTS `#__securitycheckpro_sessions`;
CREATE TABLE IF NOT EXISTS `#__securitycheckpro_sessions` (
`userid` TINYINT(3) UNSIGNED NOT NULL,
`session_id` VARCHAR(200) NOT NULL,
`username` VARCHAR(150) NOT NULL,
`ip` BIGINT NOT NULL,
`user_agent` VARCHAR(300) NOT NULL,
PRIMARY KEY (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__securitycheckpro_logs` ADD `geolocation` VARCHAR(150) DEFAULT '---' AFTER `ip`;