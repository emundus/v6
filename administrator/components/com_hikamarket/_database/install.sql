CREATE TABLE IF NOT EXISTS `#__hikamarket_config` (
	`config_namekey` varchar(200) NOT NULL,
	`config_value` text NOT NULL,
	`config_default` text NOT NULL,
 	PRIMARY KEY (`config_namekey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__hikamarket_customer_vendor` (
	`customer_id` INT(10) UNSIGNED NOT NULL,
	`vendor_id` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`customer_id`,`vendor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__hikamarket_vendor` (
	`vendor_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`vendor_admin_id` INT(10) NOT NULL DEFAULT 0,
	`vendor_name` VARCHAR(255) NOT NULL,
	`vendor_alias` VARCHAR(255) NOT NULL DEFAULT '',
	`vendor_canonical` VARCHAR(255) NOT NULL DEFAULT '',
	`vendor_email` VARCHAR(255) NOT NULL,
	`vendor_published` tinyint(4) NOT NULL DEFAULT 0,
	`vendor_currency_id` INT(10) NOT NULL DEFAULT 0,
	`vendor_description` TEXT NULL,
	`vendor_access` TEXT NULL,
	`vendor_shippings` TEXT NULL,
	`vendor_params` TEXT NULL,
	`vendor_image` VARCHAR(255) NOT NULL DEFAULT '',
	`vendor_created` INT(11) NOT NULL DEFAULT 0,
	`vendor_modified` INT(11) NOT NULL DEFAULT 0,
	`vendor_template_id` VARCHAR(255) NOT NULL DEFAULT '',
	`vendor_address_company` TEXT NULL,
	`vendor_address_street` TEXT NULL,
	`vendor_address_street2` TEXT NULL,
	`vendor_address_post_code` TEXT NULL,
	`vendor_address_city` TEXT NULL,
	`vendor_address_telephone` TEXT NULL,
	`vendor_address_fax` TEXT NULL,
	`vendor_address_state` TEXT NULL,
	`vendor_address_country` TEXT NULL,
	`vendor_address_vat` TEXT NULL,
	`vendor_zone_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`vendor_site_id` VARCHAR(255) NOT NULL DEFAULT '',
	`vendor_average_score` decimal(16,5) NOT NULL DEFAULT '0.00000',
	`vendor_total_vote` INT NOT NULL DEFAULT 0,
	`vendor_terms` TEXT NULL,
	`vendor_location_lat` DECIMAL(9, 6) NULL,
	`vendor_location_long` DECIMAL(9, 6) NULL,
	PRIMARY KEY (`vendor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__hikamarket_vendor_user` (
	`vendor_id` INT(10) NOT NULL,
	`user_id` INT(10) NOT NULL,
	`user_access` TEXT NULL,
	`ordering` INT(10) NOT NULL DEFAULT 1,
	PRIMARY KEY (`vendor_id`, `user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__hikamarket_fee` (
	`fee_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`fee_type` varchar(255) NOT NULL DEFAULT 'product',
	`fee_target_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
	`fee_currency_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
	`fee_value` decimal(16,5) NOT NULL DEFAULT '0.00000',
	`fee_fixed` decimal(16,5) NOT NULL DEFAULT '0.00000',
	`fee_percent` decimal(16,5) NOT NULL DEFAULT '0.00000',
	`fee_min_quantity` int(10) UNSIGNED NOT NULL DEFAULT '0',
	`fee_min_price` decimal(16,5) NOT NULL DEFAULT '0.00000',
	`fee_group` int(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`fee_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__hikamarket_customer_vendor` (
	`customer_id` INT(10) NOT NULL,
	`vendor_id` INT(10) NOT NULL,
	PRIMARY KEY (`customer_id`,`vendor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__hikamarket_order_transaction` (
	`order_transaction_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` INT(10) UNSIGNED NOT NULL,
	`vendor_id` INT(10) UNSIGNED NOT NULL,
	`order_transaction_created` INT(11) NOT NULL DEFAULT 0,
	`order_transaction_status` varchar(255) NOT NULL DEFAULT '',
	`order_transaction_price` decimal(12,5) NOT NULL DEFAULT '0.00000',
	`order_transaction_currency_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`order_transaction_paid` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`order_transaction_valid` INT(4) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`order_transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
