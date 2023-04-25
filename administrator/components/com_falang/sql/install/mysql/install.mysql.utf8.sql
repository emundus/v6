CREATE TABLE IF NOT EXISTS `#__falang_content` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`language_id` int(11) NOT NULL default '0',
	`reference_id` int(11) NOT NULL default '0',
	`reference_table` varchar(100) NOT NULL default '',
	`reference_field` varchar(100) NOT NULL default '',
	`value` mediumtext  NOT NULL,
	`original_value` varchar(255) default NULL,
	`original_text` mediumtext,
	`modified` datetime NOT NULL default '0000-00-00 00:00:00',
	`modified_by` int(11) unsigned NOT NULL default '0',
	`published` tinyint(1) unsigned NOT NULL default '0',
	PRIMARY KEY  (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__falang_tableinfo` (
	`id` int(11) NOT NULL auto_increment,
	`joomlatablename` varchar(100) NOT NULL default '',
	`tablepkID` varchar(100) NOT NULL default '',
	PRIMARY KEY  (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;