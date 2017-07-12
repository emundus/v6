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
('Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! DOS Vulnerability','Object unserialize method','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! Information Disclosure Vulnerability','Inadequate permission checking','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! XSS Vulnerability','Use of old version of Flash-based file uploader','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! Privilege Escalation Vulnerability','Inadequate permission checking','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('Joomla!','core','3.0.2','<=','3.0.0','>=','Joomla! XSS Vulnerability','Inadequate filtering','Apr 24 2013','Joomla! version 3.0.2 and earlier 3.0.x versions','update','3.1.0'),
('com_jnews','component','8.0.1','<=','3.0.0','>=','Joomla Jnews Component','Cross Site Scripting Vulnerability','May 14 2013','Version 8.0.1 an earlier','update','8.1.x');

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

ALTER TABLE `#__securitycheckpro_logs` ADD `username` VARCHAR(150) DEFAULT '---' AFTER `ip`;
ALTER TABLE `#__securitycheckpro_logs` ADD `component` VARCHAR(150) DEFAULT '---' AFTER `uri`;

DROP TABLE IF EXISTS `#__securitycheckpro_rules`;
CREATE TABLE `#__securitycheckpro_rules` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`group_id` INT(10),
`rules_applied` TINYINT(1) DEFAULT 0,
`last_change` DATETIME,
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__securitycheckpro_rules_logs`;
CREATE TABLE IF NOT EXISTS `#__securitycheckpro_rules_logs` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ip` VARCHAR(35) NOT NULL,
`username` VARCHAR(150) NOT NULL,
`last_entry` DATETIME,
`reason` VARCHAR(300),
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;