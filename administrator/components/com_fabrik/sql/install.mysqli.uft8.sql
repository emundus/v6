CREATE TABLE IF NOT EXISTS `#__fabrik_connections` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`host` VARCHAR(255) NOT NULL DEFAULT '',
	`user` VARCHAR(255) NOT NULL DEFAULT '',
	`password` VARCHAR(255) NOT NULL DEFAULT '',
	`database` VARCHAR(255) NOT NULL DEFAULT '',
	`description` VARCHAR(255) NOT NULL DEFAULT '',
	`published` INT(1) NOT NULL default '0',
	`checked_out` INT(4) NOT NULL default '0',
	`checked_out_time` DATETIME NULL DEFAULT NULL,
	`default` INT(1) NOT NULL DEFAULT '0',
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_cron` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`label` VARCHAR(100) NOT NULL DEFAULT '',
	`frequency` SMALLINT(6) NOT NULL  DEFAULT 0,
	`unit` VARCHAR(15) NOT NULL DEFAULT '',
	`created` DATETIME NOT NULL,
	`created_by` INT(6) NOT NULL DEFAULT 0,
	`created_by_alias` VARCHAR(30) NOT NULL DEFAULT '',
	`modified` DATETIME NULL DEFAULT NULL,
	`modified_by` VARCHAR(30) NOT NULL DEFAULT '',
	`checked_out` INT(6) NOT NULL DEFAULT 0,
	`checked_out_time` DATETIME NULL DEFAULT NULL,
	`published` TINYINT(1) NOT NULL DEFAULT 1,
	`plugin` VARCHAR(50) NOT NULL DEFAULT '',
	`lastrun` DATETIME NULL DEFAULT NULL,
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_elements` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL DEFAULT '',
	`group_id` INT(4) NOT NULL DEFAULT 0,
	`plugin` VARCHAR(100) NOT NULL DEFAULT '',
	`label` TEXT NULL,
	`checked_out` INT(11) NOT NULL DEFAULT 0,
	`checked_out_time` DATETIME NULL DEFAULT NULL,
	`created` DATETIME NOT NULL,
	`created_by` INT(11) NOT NULL DEFAULT 0,
	`created_by_alias` varchar(100) NOT NULL DEFAULT '',
	`modified` DATETIME NULL DEFAULT NULL,
	`modified_by` INT(11) NOT NULL DEFAULT 0,
	`width` INT(4) NOT NULL DEFAULT 0,
	`height` INT(4) NOT NULL DEFAULT 0,
	`default` TEXT,
	`hidden` INT(1) NOT NULL DEFAULT 0,
	`eval` INT(1) NOT NULL DEFAULT 0,
	`ordering` INT(4) NOT NULL DEFAULT 0,
	`show_in_list_summary` INT(1) DEFAULT 0, 
	`filter_type` VARCHAR (20) DEFAULT '',
	`filter_exact_match` INT(1) DEFAULT 0,
	`published` INT(1) NOT NULL default '0',
	`link_to_detail` INT(1) NOT NULL default '0',
	`primary_key` INT(1) NOT NULL default '0',
	`auto_increment` INT(1) NOT NULL default '0',
	`access` INT(1) NOT NULL default '0',
	`use_in_page_title` INT(1) NOT NULL default '0',
	`parent_id` MEDIUMINT(6) NOT NULL DEFAULT 0,
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`),
	KEY `show_in_list_summary_INDEX` (`show_in_list_summary`),
	KEY `plugin_INDEX` (`plugin`(10)),
	KEY `checked_out_INDEX` (`checked_out`),
	KEY `group_id_INDEX` (`group_id`),
	KEY `parent_id_INDEX` (`parent_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_formgroup` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`form_id` INT(4) NOT NULL DEFAULT 0,
	`group_id` INT(4) NOT NULL DEFAULT 0,
	`ordering` INT(4) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `form_id_INDEX` (`form_id`),
	KEY `group_id_INDEX` (`group_id`),
	KEY `ordering_INDEX` (`ordering`)

) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_forms` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`label` VARCHAR(255) NOT NULL DEFAULT '',
	`record_in_database` INT(4) NOT NULL DEFAULT 0,
	`error` VARCHAR(150) NOT NULL DEFAULT '',
	`intro` TEXT,
	`created` DATETIME NOT NULL,
	`created_by` INT(11) NOT NULL DEFAULT 0,
	`created_by_alias` VARCHAR(100) NOT NULL DEFAULT '',
	`modified` DATETIME NULL DEFAULT NULL,
	`modified_by` INT(11) NOT NULL DEFAULT 0,
	`checked_out` INT(11) NOT NULL DEFAULT 0,
	`checked_out_time` DATETIME NULL DEFAULT NULL,
	`publish_up` DATETIME NULL DEFAULT NULL, 
	`publish_down` DATETIME NULL DEFAULT NULL,
	`reset_button_label` VARCHAR (100) NOT NULL DEFAULT '',
	`submit_button_label` VARCHAR (100) NOT NULL DEFAULT '',
	`form_template` varchar(255) DEFAULT '', 
	`view_only_template` varchar(255) DEFAULT '',
	`published` INT(1) NOT NULL DEFAULT 0,
	`private` TINYINT(1) NOT NULL DEFAULT '0',
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`),
	KEY `published_INDEX` (`published`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_form_sessions` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`hash` VARCHAR(255) NOT NULL DEFAULT '',
	`user_id` INT(6) NOT NULL DEFAULT 0,
	`form_id` INT(6) NOT NULL DEFAULT 0,
	`row_id` INT(10) NOT NULL DEFAULT 0,
	`last_page` INT(4) NOT NULL DEFAULT 0,
	`referring_url` VARCHAR(255) NOT NULL DEFAULT '',
	`data` MEDIUMTEXT,
	`time_date` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_groups` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL DEFAULT '',
	`css` TEXT,
	`label` VARCHAR(100) NOT NULL DEFAULT '',
	`published` INT(1) NOT NULL default '0',
	`created` DATETIME NOT NULL,
	`created_by` INT(11) NOT NULL DEFAULT 0,
	`created_by_alias` VARCHAR(100) NOT NULL DEFAULT '',
	`modified` DATETIME NULL DEFAULT NULL,
	`modified_by` INT(11) NOT NULL DEFAULT 0,
	`checked_out` INT(11) NOT NULL DEFAULT 0,
	`checked_out_time` DATETIME NULL DEFAULT NULL,
	`is_join` INT(1) NOT NULL DEFAULT '0',
	`private` TINYINT(1) NOT NULL DEFAULT '0',
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`),
	KEY `published_INDEX` (`published`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_joins` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`list_id` INT(6) NOT NULL DEFAULT 0,
	`element_id` INT(6) NOT NULL DEFAULT 0,
	`join_from_table` VARCHAR(255) NOT NULL DEFAULT '',
	`table_join` VARCHAR(255) NOT NULL DEFAULT '',
	`table_key` VARCHAR(255) NOT NULL DEFAULT '',
	`table_join_key` VARCHAR(255) NOT NULL DEFAULT '', 
	`join_type` VARCHAR(255) NOT NULL DEFAULT '',
	`group_id` INT(10) NOT NULL DEFAULT 0,
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`),
	KEY `list_id_INDEX` (`list_id`),
	KEY `element_id_INDEX` (`element_id`),
	KEY `group_id_INDEX` (`group_id`),
	KEY `table_join_INDEX` (`table_join`(10))
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_jsactions` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`element_id` INT(10) NOT NULL DEFAULT 0, 
	`action` VARCHAR(255) NOT NULL DEFAULT '',
	`code` TEXT,
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`),
	KEY `element_id_INDEX` (`element_id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__fabrik_lists` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`label` VARCHAR (255) NOT NULL DEFAULT '',
	`introduction` TEXT,
	`form_id` INT(4) NOT NULL DEFAULT 0,
	`db_table_name` VARCHAR(255) NOT NULL DEFAULT '',
	`db_primary_key` VARCHAR(255) NOT NULL DEFAULT '',
	`auto_inc` INT(1) NOT NULL DEFAULT 0,
	`connection_id` INT(6) NOT NULL DEFAULT 0,
	`created` DATETIME NOT NULL, 
	`created_by` INT(4) NOT NULL DEFAULT 0, 
	`created_by_alias` VARCHAR(255) NOT NULL DEFAULT '', 
	`modified` DATETIME NULL DEFAULT NULL,
	`modified_by` INT(4) NOT NULL DEFAULT 0,
	`checked_out` INT(4) NOT NULL DEFAULT 0,
	`checked_out_time` DATETIME NULL DEFAULT NULL, 
	`published` INT(1) NOT NULL DEFAULT 0,
	`publish_up` DATETIME NULL DEFAULT NULL, 
	`publish_down` DATETIME NULL DEFAULT NULL, 
	`access` INT(4) NOT NULL DEFAULT 0, 
	`hits` INT(4) NOT NULL DEFAULT 0,
	`rows_per_page` INT(5) NOT NULL DEFAULT 0,
	`template` varchar (255) NOT NULL DEFAULT '',
	`order_by` varchar (255) NOT NULL DEFAULT '',
	`order_dir` varchar(255) NOT NULL default 'ASC',
	`filter_action` varchar(30) NOT NULL DEFAULT '', 
	`group_by` VARCHAR(255) NOT NULL DEFAULT '', 
	`private` TINYINT(1) NOT NULL DEFAULT '0',
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`),
	KEY `form_id_INDEX` (`form_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_log` (
	`id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`timedate_created` TIMESTAMP NOT NULL,
	`flag` SMALLINT(3) NOT NULL DEFAULT 0,
	`referring_url` VARCHAR(255) NOT NULL DEFAULT '',
	`message_source` VARCHAR(255) NOT NULL DEFAULT '',
	`message_type` CHAR(60) NOT NULL DEFAULT '',
	`message` TEXT
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_validations` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`element_id` INT(4) NOT NULL DEFAULT 0,
	`validation_plugin` VARCHAR (100) NOT NULL DEFAULT '',
	`message` varchar(255) NULL DEFAULT '',
	`client_side_validation` INT(1) NOT NULL default 0,
	`checked_out` INT(4) NOT NULL DEFAULT 0,
	`checked_out_time` DATETIME NULL DEFAULT NULL, 
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__fabrik_visualizations` (
	`id` INT(6) NOT NULL AUTO_INCREMENT,
	`plugin` VARCHAR(100) NOT NULL DEFAULT '',
	`label` VARCHAR(255) NOT NULL DEFAULT '',
	`intro_text` TEXT,
	`created` DATETIME NOT NULL,
	`created_by` INT(11) NOT NULL DEFAULT 0,
	`created_by_alias` VARCHAR(100) NOT NULL DEFAULT '',
	`modified` DATETIME DEFAULT NULL,
	`modified_by` INT(11) NOT NULL DEFAULT 0,
	`checked_out` INT(11) NOT NULL DEFAULT 0,
	`checked_out_time` DATETIME NULL DEFAULT NULL,
	`publish_up` DATETIME NULL DEFAULT NULL,
	`publish_down` DATETIME NULL DEFAULT NULL,
	`published` INT(1) NOT NULL DEFAULT 0,
	`access` INT(6) NOT NULL DEFAULT 0,
	`params` MEDIUMTEXT,
	PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
				