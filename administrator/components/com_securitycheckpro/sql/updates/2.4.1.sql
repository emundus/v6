DROP TABLE IF EXISTS `#__securitycheckpro_db`;
CREATE TABLE `#__securitycheckpro_db` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`Product` VARCHAR(35) NOT NULL,
`Type` VARCHAR(35),
`Vulnerableversion` VARCHAR(10) DEFAULT '---',
`modvulnversion` VARCHAR(2) DEFAULT '==',
`Joomlaversion` VARCHAR(10) DEFAULT 'Notdefined',
`modvulnjoomla` VARCHAR(2) DEFAULT '==',
`description` VARCHAR(90),
`class` VARCHAR(70),
`published` VARCHAR(35),
`vulnerable` VARCHAR(70),
`solution_type` VARCHAR(35) DEFAULT '???',
`solution` VARCHAR(70),
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
INSERT INTO `#__securitycheckpro_db` (`product`,`type`,`vulnerableversion`,`modvulnversion`,`Joomlaversion`,
`modvulnjoomla`,`description`,`class`,`published`,`vulnerable`,`solution_type`,`solution`) VALUES 
('Joomla!','core','3.0.0','==','3.0.0','==','Joomla! XSS Vulnerability','Typographical error','Oct 09 2012','Joomla! 3.0.0','update','3.0.1'),
('com_fss','component','1.9.1.1447','<=','3.0.0','>=','Joomla Freestyle Support Component','SQL Injection Vulnerability','Oct 19 2012','Versions prior to 1.9.1.1447','none','No details'),
('com_commedia','component','3.1','<=','3.0.0','>=','Joomla Commedia Component','SQL Injection Vulnerability','Oct 19 2012','Versions prior to 3.1','update','3.2'),
('Joomla!','core','3.0.1','<=','3.0.1','<=','Joomla! Core Clickjacking Vulnerability','Inadequate protection','Nov 08 2012','Joomla! 3.0.1 and all earlier 3.0.x versions','update','3.0.2'),
('com_jnews','component','7.9.1','<','3.0.0','>=','Joomla jNews Component','Arbitrary File Creation Vulnerability','Nov 19 2012','Versions prior to 7.9.1','update','7.9.1'),
('com_bch','component','---','==','3.0.0','>=','Joomla Bch Component','Shell Upload Vulnerability','Dec 26 2012','Not especificed','none','No details'),
('com_aclassif','component','---','==','3.0.0','>=','Joomla Aclassif Component','Cross Site Scripting Vulnerability','Dec 26 2012','Not especificed','none','No details'),
('com_rsfiles','component','1.0.0 Rev 11','==','3.0.0','>=','Joomla RSFiles! Component','SQL Injection Vulnerability','Mar 19 2013','Version 1.0.0 Rev 11','update','1.0.0 Rev 12'),
('Joomla!','core','3.0.2','<=','3.0.2','<=','Joomla! XSS Vulnerability','Inadequate filtering','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.2','<=','Joomla! DOS Vulnerability','Object unserialize method','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.2','<=','Joomla! XSS Vulnerability','Inadequate filtering','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.2','<=','Joomla! Information Disclosure Vulnerability','Inadequate permission checking','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.2','<=','Joomla! XSS Vulnerability','Use of old version of Flash-based file uploader','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.2','<=','Joomla! Privilege Escalation Vulnerability','Inadequate permission checking','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.2','<=','Joomla! XSS Vulnerability','Inadequate filtering','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0');

DROP TABLE IF EXISTS `#__securitycheckpro_sessions`;
CREATE TABLE IF NOT EXISTS `#__securitycheckpro_sessions` (
`userid` TINYINT(3) UNSIGNED NOT NULL,
`session_id` VARCHAR(200) NOT NULL,
`username` VARCHAR(150) NOT NULL,
`ip` BIGINT NOT NULL,
`user_agent` VARCHAR(300) NOT NULL,
PRIMARY KEY (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__securitycheckpro_file_permissions`;

ALTER TABLE `#__securitycheckpro_storage` CHANGE `key` `storage_key` varchar(255) NOT NULL;
ALTER TABLE `#__securitycheckpro_storage` CHANGE `value` `storage_value` longtext NOT NULL;

DROP TABLE IF EXISTS `#__securitycheckpro_file_manager`;
CREATE TABLE IF NOT EXISTS `#__securitycheckpro_file_manager` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`last_check` DATETIME,
`last_check_integrity` DATETIME,
`files_scanned` INT(10) DEFAULT 0,
`files_scanned_integrity` INT(10) DEFAULT 0,
`files_with_incorrect_permissions` INT(10) DEFAULT 0,
`files_with_bad_integrity` INT(10) DEFAULT 0,
`estado` VARCHAR(40) DEFAULT 'IN_PROGRESS',
`estado_integrity` VARCHAR(40) DEFAULT 'IN_PROGRESS',
`hash_alg` VARCHAR(30),
`estado_cambio_permisos` VARCHAR(40) DEFAULT 'IN_PROGRESS',
`estado_clear_data` VARCHAR(40) DEFAULT 'DELETING_ENTRIES',
`last_task` VARCHAR(40) DEFAULT 'INTEGRITY',
`cron_tasks_launched` TINYINT(1) DEFAULT 0,
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
INSERT INTO `#__securitycheckpro_file_manager` (`estado`,`estado_integrity`,`estado_cambio_permisos`,`estado_clear_data`) VALUES 
('ENDED','ENDED','ENDED','DELETING_ENTRIES');