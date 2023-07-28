<?php
$version = explode('.',PHP_VERSION);
if($version[0] < 5 || ($version[0] == 5 && $version[1] < 4)) {
	echo '<html><body><h1>This extension works with PHP 5.4.0 or newer.</h1>'.
		'<h2>Please contact your web hosting provider to update your PHP version</h2>'.
		'installation aborted...</body></html>';
	exit;
}
function com_hikashop_install() {
	if(!defined('DS'))
		define('DS', DIRECTORY_SEPARATOR);
	define('HIKASHOP_INSTALL_PRECHECK',true);
	include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php');
	$lang = JFactory::getLanguage();
	$lang->load(HIKASHOP_COMPONENT,JPATH_SITE);
	$installClass = new hikashopInstall();
	$installClass->addPref();
	$installClass->updatePref();
	$installClass->addMenus();
	$installClass->addModules();
	$installClass->updateSQL();
	$installClass->displayInfo();
}

if(!function_exists('com_install')) {
	function com_install() {
		return com_hikashop_install();
	}
}

class hikashopInstall {
	var $level = 'Business';
	var $version = '4.7.4';
	var $freshinstall = true;
	var $update = false;
	var $fromLevel = '';
	var $fromVersion = '';
	var $db;

	public function __construct() {
		$this->db = JFactory::getDBO();
		$this->db->setQuery("SELECT COUNT(*) as `count` FROM `#__hikashop_config` WHERE `config_namekey` IN ('version','level') LIMIT 2");
		$results = $this->db->loadObject();

		$this->databaseHelper = hikashop_get('helper.database');
		if(!empty($results) && $results->count == 2)
			$this->freshinstall = false;
		if(empty($results)) {
			$install_sql = file_get_contents(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hikashop'.DIRECTORY_SEPARATOR.'tables.sql');
			$queries = explode(';', $install_sql);
			foreach($queries as $query) {
				$query = trim($query);
				if(!empty($query)) {
					$this->db->setQuery($query);
					$this->db->execute();
				}
			}
		}
	}

	public function displayInfo() {
		unset($_SESSION['hikashop']['li']);
		$url = 'index.php?option=com_hikashop&ctrl=update&task=install&fromversion='.$this->fromVersion.'&update='.(int)$this->update.'&freshinstall='.(int)$this->freshinstall;

		echo '<h1>Please wait...</h1>'.
			'<h2>HikaShop will now automatically install the Plugins and the Modules</h2>' .
			'<a href="'.$url.'">Please click here if you are not automatically redirected within 3 seconds</a>'.
			'<script language="javascript" type="text/javascript">document.location.href = "'.$url.'";</script>';
	}

	public function updatePref() {
		$this->db->setQuery("SELECT `config_namekey`, `config_value` FROM `#__hikashop_config` WHERE `config_namekey` IN ('version','level') LIMIT 2");
		$results = $this->db->loadObjectList('config_namekey');
		$this->fromLevel = $results['level']->config_value;
		$this->fromVersion = $results['version']->config_value;
		if($results['version']->config_value == $this->version && $results['level']->config_value == $this->level)
			return true;
		$this->update = true;

		if(version_compare($this->fromVersion,'1.5.6','<')){
			$config =& hikashop_config();
			$this->db->setQuery("INSERT IGNORE #__hikashop_config (`config_value`,`config_default`,`config_namekey`) VALUES ('0','1','detailed_tax_display'),('0','1','simplified_breadcrumbs'),(".(int)$config->get('thumbnail_x',100).",'100','product_image_x'),(".(int)$config->get('thumbnail_y',100).",'100','product_image_y');");
			$this->db->execute();
		}

		$query = "REPLACE INTO `#__hikashop_config` (`config_namekey`,`config_value`) VALUES ('level',".$this->db->Quote($this->level)."),('version',".$this->db->Quote($this->version)."),('installcomplete','0')";
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function updateSQL() {
		if(!$this->update)
			return true;

		if(version_compare($this->fromVersion,'1.0.2','<')){
			$query = 'UPDATE `#__hikashop_user` AS a LEFT JOIN `#__hikashop_user` AS b ON a.user_email=b.user_email SET a.user_email=CONCAT(\'old_\',a.user_email) WHERE a.user_id>b.user_id';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}
			$this->databaseHelper->addColumns("user","UNIQUE (`user_email`)");
		}
		if(version_compare($this->fromVersion,'1.1.2','<')){
			$this->databaseHelper->addColumns("product","`product_max_per_order` INT UNSIGNED DEFAULT 0");
		}
		if(version_compare($this->fromVersion,'1.3.4','<')){
			$this->databaseHelper->addColumns("discount","`discount_auto_load` TINYINT UNSIGNED DEFAULT 0");
		}
		if(version_compare($this->fromVersion,'1.3.3','>') && version_compare($this->fromVersion,'1.3.6','<')){
			$this->db->setQuery("DELETE FROM `#__modules` WHERE module='HikaShop Content Module' OR module='HikaShop Cart Module' OR module='HikaShop Currency Switcher Module'");
			try{$this->db->execute();}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion,'1.4.1','<')){
			$rand=rand(0,999999999);
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'media/com_hikashop/upload',`config_default` = 'media/com_hikashop/upload' WHERE `config_namekey` = 'uploadfolder' AND `config_value` LIKE 'components/com_hikashop/upload%' ");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'media/com_hikashop/upload/safe',`config_default` = 'media/com_hikashop/upload/safe' WHERE `config_namekey` = 'uploadsecurefolder' AND `config_value` LIKE 'components/com_hikashop/upload/safe%' ");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'media/com_hikashop/upload/safe/logs/report_".$rand.".log',`config_default` = 'media/com_hikashop/upload/safe/logs/report_".$rand.".log' WHERE `config_namekey` IN ('cron_savepath','payment_log_file') ");
			try{$this->db->execute();}catch(Exception $e){}

			$updateHelper = hikashop_get('helper.update');
			$removeFiles = array(
				HIKASHOP_FRONT.'css'.DS.'backend_default.css',
				HIKASHOP_FRONT.'css'.DS.'frontend_default.css',
				HIKASHOP_FRONT.'mail'.DS.'cron_report.html.php',
				HIKASHOP_FRONT.'mail'.DS.'order_admin_notification.text.php',
				HIKASHOP_FRONT.'mail'.DS.'order_creation_notification.text.php',
				HIKASHOP_FRONT.'mail'.DS.'order_creation_notification.html.php',
				HIKASHOP_FRONT.'mail'.DS.'order_notification.text.php',
				HIKASHOP_FRONT.'mail'.DS.'order_notification.html.php',
				HIKASHOP_FRONT.'mail'.DS.'order_status_notification.text.php',
				HIKASHOP_FRONT.'mail'.DS.'order_status_notification.html.php',
				HIKASHOP_FRONT.'mail'.DS.'user_account.text.php',
				HIKASHOP_FRONT.'mail'.DS.'user_account.html.php',
				HIKASHOP_FRONT.'mail'.DS.'user_account_admin_notification.html.php',
				HIKASHOP_FRONT.'mail'.DS.'user_account_admin_notification.html.php',
			);
			foreach($removeFiles as $oneFile){
				if(is_file($oneFile)) JFile::delete($oneFile);
			}

			$fromFolders = array();
			$toFolders = array();
			$fromFolders[] = HIKASHOP_FRONT.'css';
			$toFolders[] = HIKASHOP_MEDIA.'css';
			$fromFolders[] = HIKASHOP_FRONT.'mail';
			$toFolders[] = HIKASHOP_MEDIA.'mail';
			$fromFolders[] = HIKASHOP_FRONT.'upload';
			$toFolders[] = HIKASHOP_MEDIA.'upload';

			foreach($fromFolders as $i => $oneFolder){
				if(!is_dir($oneFolder)) continue;
				if(is_dir($toFolders[$i]) || !@rename($oneFolder,$toFolders[$i])) $updateHelper->copyFolder($oneFolder,$toFolders[$i]);
			}

			$deleteFolders = array(
				HIKASHOP_FRONT.'css',
				HIKASHOP_FRONT.'images',
				HIKASHOP_FRONT.'js'
			);
			foreach($deleteFolders as $oneFolder){
				if(!is_dir($oneFolder)) continue;
				JFolder::delete($oneFolder);
			}

		}
		if(version_compare($this->fromVersion,'1.4.2','<')){
			$this->databaseHelper->addColumns("discount","`discount_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->databaseHelper->addColumns("category","`category_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->databaseHelper->addColumns("product","`product_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->databaseHelper->addColumns("price","`price_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->databaseHelper->addColumns("zone","`zone_currency_id` INT UNSIGNED DEFAULT 0");

			$query = 'UPDATE `#__extensions` SET `enabled`=0 WHERE `element`=\'geolocation\' AND `folder`=\'hikashop\'';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion,'1.4.5','<')){
			$this->databaseHelper->addColumns("product",array("`product_group_after_purchase` VARCHAR( 255 ) NOT NULL DEFAULT ''","`product_contact` SMALLINT UNSIGNED DEFAULT 0"));
		}
		if(version_compare($this->fromVersion,'1.4.6','<')){
			$this->db->setQuery('ALTER TABLE `#__hikashop_product_related` DROP PRIMARY KEY, ADD PRIMARY KEY (`product_id`,`product_related_id`,`product_related_type`)');
			try{$this->db->execute();}catch(Exception $e){}
			$this->databaseHelper->addColumns("product","`product_min_per_order` INT UNSIGNED DEFAULT 0");
		}
		if(version_compare($this->fromVersion,'1.4.7','<')){
			$this->databaseHelper->addColumns("payment","`payment_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->databaseHelper->addColumns("shipping","`shipping_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
		}
		if(version_compare($this->fromVersion,'1.4.8','<')){
			$this->databaseHelper->addColumns("history","`history_user_id` INT UNSIGNED DEFAULT 0");
			$this->databaseHelper->addColumns("discount","`discount_tax_id` INT UNSIGNED DEFAULT 0");
			$this->databaseHelper->addColumns("order",array("`order_discount_tax` decimal(12,5) NOT NULL DEFAULT '0.00000'","`order_shipping_tax` decimal(12,5) NOT NULL DEFAULT '0.00000'"));
		}
		if(version_compare($this->fromVersion,'1.4.9','<')){
			$this->databaseHelper->addColumns("order","`order_number` VARCHAR( 255 ) NOT NULL DEFAULT ''");
			$this->db->setQuery("SELECT order_id,order_created FROM ".hikashop_table('order').' WHERE order_number=\'\'');
			$orders = $this->db->loadObjectList();
			if(!empty($orders)){
				foreach($orders as $k => $order){
					$orders[$k]->order_number = hikashop_encode($order);
				}
				$i = 0;
				$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_order_number` (`order_id` int(10) unsigned NOT NULL DEFAULT '0',`order_number` VARCHAR( 255 ) NOT NULL DEFAULT '') ENGINE=MyISAM ;");
				try{$this->db->execute();}catch(Exception $e){}
				$inserts = array();
				foreach($orders as $k => $order){
					$i++;
					$inserts[]='('.$order->order_id.','.$this->db->Quote($order->order_number).')';
					if($i >= 500){
						$i=0;
						$this->db->setQuery('INSERT IGNORE INTO `#__hikashop_order_number` (order_id,order_number) VALUES '.implode(',',$inserts));
						try{$this->db->execute();}catch(Exception $e){}
						$inserts = array();
					}
				}
				$this->db->setQuery('INSERT IGNORE INTO `#__hikashop_order_number` (order_id,order_number) VALUES '.implode(',',$inserts));
				try{$this->db->execute();}catch(Exception $e){}
				$this->db->setQuery('UPDATE `#__hikashop_order` AS a , `#__hikashop_order_number` AS b SET a.order_number=b.order_number WHERE a.order_id=b.order_id AND a.order_number=\'\'');
				try{$this->db->execute();}catch(Exception $e){}
				$this->db->setQuery('DROP TABLE IF EXISTS `#__hikashop_order_number`');
				try{$this->db->execute();}catch(Exception $e){}
			}
		}
		if(version_compare($this->fromVersion,'1.5.0','<')){
			$this->databaseHelper->addColumns("field","`field_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->databaseHelper->addColumns("product","`product_min_per_order` INT UNSIGNED DEFAULT 0");

			$query = 'UPDATE `#__extensions` SET `enabled` = 0 WHERE `element` = \'hikashop\' AND `folder` = \'user\'';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}
			$this->databaseHelper->addColumns("discount",array("`discount_quota_per_user` INT UNSIGNED DEFAULT 0","`discount_minimum_products` INT UNSIGNED DEFAULT 0"));
		}
		if(version_compare($this->fromVersion,'1.5.2','<')){
			$this->databaseHelper->addColumns("category","`category_keywords` VARCHAR(255) NOT NULL");
			$this->databaseHelper->addColumns("category","`category_meta_description` varchar(155) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("product_related","`product_related_ordering` INT UNSIGNED DEFAULT 0");
			$this->databaseHelper->addColumns("product","`product_last_seen_date` INT UNSIGNED DEFAULT 0");
			$this->databaseHelper->addColumns("file","`file_free_download` tinyint(3) unsigned NOT NULL DEFAULT '0'");

			$manufacturer = new stdClass();
			$manufacturer->category_type = 'manufacturer';
			$manufacturer->category_name = 'manufacturer';
			$categoryClass = hikashop_get('class.category');
			$categoryClass->save($manufacturer);
		}
		if(version_compare($this->fromVersion,'1.5.3','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_limit` (
	`limit_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`limit_product_id` int(11) NOT NULL DEFAULT '0',
	`limit_category_id` int(11) NOT NULL DEFAULT '0',
	`limit_per_product` tinyint(4) NOT NULL DEFAULT '0',
	`limit_periodicity` varchar(255) NOT NULL DEFAULT '',
	`limit_type` varchar(255) NOT NULL DEFAULT '',
	`limit_value` int(10) NOT NULL DEFAULT '0',
	`limit_unit` varchar(255) DEFAULT NULL,
	`limit_currency_id` int(11) NOT NULL DEFAULT '0',
	`limit_access` varchar(255) NOT NULL DEFAULT '',
	`limit_status` varchar(255) NOT NULL DEFAULT '',
	`limit_published` tinyint(4) NOT NULL DEFAULT '0',
	`limit_created` int(10) DEFAULT NULL,
	`limit_modified` int(10) DEFAULT NULL,
	`limit_start` int(10) DEFAULT NULL,
	`limit_end` int(10) DEFAULT NULL,
	PRIMARY KEY (`limit_id`)
) ENGINE=MyISAM ;");
			try{$this->db->execute();}catch(Exception $e){}
			$this->databaseHelper->addColumns("zone","INDEX ( `zone_code_3` )");
			$this->databaseHelper->addColumns("product","`product_sales` INT UNSIGNED DEFAULT 0");
			$this->databaseHelper->addColumns("field",array("`field_with_sub_categories` TINYINT( 1 ) NOT NULL DEFAULT 0","`field_categories` VARCHAR( 255 ) NOT NULL DEFAULT 'all'"));
			$this->databaseHelper->addColumns("payment","`payment_shipping_methods` TEXT NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("cart_product","`cart_product_option_parent_id` INT UNSIGNED DEFAULT 0");
			$this->databaseHelper->addColumns("order_product","`order_product_option_parent_id` INT UNSIGNED DEFAULT 0");
			$this->databaseHelper->addColumns("taxation","`taxation_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");

			$categoryClass = hikashop_get('class.category');
			$tax = new stdClass();
			$tax->category_type = 'tax';
			$tax->category_parent_id = 'tax';
			$categoryClass->getMainElement($tax->category_parent_id);
			$tax->category_name = 'Default tax category';
			$tax->category_namekey = 'default_tax';
			$tax->category_depth = 2;
			$categoryClass->save($tax);
		}
		if(version_compare($this->fromVersion,'1.5.4','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_filter` (
	`filter_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	`filter_name` varchar(250) NOT NULL,
	`filter_namekey` varchar(50) NOT NULL,
	`filter_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
	`filter_type` varchar(50) DEFAULT NULL,
	`filter_category_id` int(10) unsigned NOT NULL,
	`filter_ordering` smallint(5) unsigned DEFAULT '99',
	`filter_options` text,
	`filter_data` text NOT NULL,
	`filter_access` varchar(250) NOT NULL DEFAULT 'all',
	`filter_direct_application` tinyint(3) NOT NULL DEFAULT '0',
	`filter_value` text NOT NULL,
	`filter_category_childs` tinyint(3) unsigned NOT NULL,
	`filter_height` int(50) unsigned NOT NULL,
	`filter_deletable` tinyint(3) unsigned NOT NULL,
	`filter_dynamic` tinyint(3) unsigned NOT NULL,
	PRIMARY KEY (`filter_id`)
) ENGINE=MyISAM ;");
			try{$this->db->execute();}catch(Exception $e){}

			$this->databaseHelper->addColumns("payment","`payment_currency` VARCHAR( 255 ) NOT NULL");
		}
		if(version_compare($this->fromVersion,'1.5.5','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_waitlist` (
	`waitlist_id` int(11) NOT NULL AUTO_INCREMENT,
	`product_id` int(11) NOT NULL,
	`date` int NOT NULL,
	`email` varchar(255) NOT NULL,
	`name` varchar(255) DEFAULT NULL,
	`product_item_id` int(11) NOT NULL,
	PRIMARY KEY (`waitlist_id`)
) ENGINE=MyISAM ;");
			try{$this->db->execute();}catch(Exception $e){}

			$this->databaseHelper->addColumns("product","`product_waitlist` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'");
			$this->databaseHelper->addColumns("discount","`discount_coupon_nodoubling` TINYINT NULL;");
			$this->databaseHelper->addColumns("discount","`discount_coupon_product_only` TINYINT NULL;");
		}
		if(version_compare($this->fromVersion,'1.5.6','<')){
			$this->databaseHelper->addColumns("taxation","`taxation_cumulative` TINYINT NULL;");
			$this->databaseHelper->addColumns("order","`order_tax_info` text NOT NULL");
			$this->databaseHelper->addColumns("order_product","`order_product_tax_info` text NOT NULL");
			$this->databaseHelper->addColumns("category","`category_layout` varchar(255) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("product","`product_layout` varchar(255) NOT NULL DEFAULT ''");
		}
		if(version_compare($this->fromVersion,'1.5.7','<')){
			$this->databaseHelper->addColumns("characteristic","`characteristic_alias` varchar(255) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("product",array("`product_average_score` FLOAT NOT NULL","`product_total_vote` INT NOT NULL DEFAULT '0'"));
			$this->databaseHelper->addColumns("address","`address_default` TINYINT NOT NULL DEFAULT '0'");
			$this->databaseHelper->addColumns("file",array("`file_ordering` INT UNSIGNED NOT NULL DEFAULT 0","`file_limit` INT NOT NULL DEFAULT 0"));
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_vote_user` (
	`vote_user_id` int(11) NOT NULL,
	`vote_user_user_id` varchar(26) NOT NULL,
	`vote_user_useful` tinyint(4) NOT NULL
) ENGINE=MyISAM ;");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_vote` (
	`vote_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`vote_ref_id` int(11) NOT NULL,
	`vote_type` varchar(15) NOT NULL,
	`vote_user_id` varchar(26) NOT NULL,
	`vote_rating` float NOT NULL,
	`vote_comment` varchar(255) NOT NULL,
	`vote_useful` int(11) NOT NULL,
	`vote_pseudo` varchar(25) NOT NULL,
	`vote_ip` varchar(15) NOT NULL,
	`vote_email` varchar(80) NOT NULL,
	`vote_date` int(10) unsigned NOT NULL,
	`vote_published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`vote_id`)
) ENGINE=MyISAM");
			try{$this->db->execute();}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion,'1.5.8','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_vote` CHANGE `vote_comment` `vote_comment` TEXT NOT NULL;");
			try{$this->db->execute();}catch(Exception $e){}
			$this->databaseHelper->addColumns("order","`order_payment_price` decimal(17,5) NOT NULL DEFAULT '0.00000'");
			$this->databaseHelper->addColumns("payment","`payment_price` decimal(17,5) NOT NULL DEFAULT '0.00000'");
		}
		if(version_compare($this->fromVersion,'1.5.9','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_shipping_price` (
	`shipping_price_id` int(11) NOT NULL AUTO_INCREMENT,
	`shipping_id` int(11) NOT NULL,
	`shipping_price_ref_id` int(11) NOT NULL,
	`shipping_price_ref_type` varchar(255) NOT NULL DEFAULT 'product',
	`shipping_price_min_quantity` int(11) NOT NULL DEFAULT '0',
	`shipping_price_value` decimal(15,7) NOT NULL DEFAULT '0',
	`shipping_fee_value` decimal(15,7) NOT NULL DEFAULT '0',
	PRIMARY KEY (`shipping_price_id`)
) ENGINE=MyISAM;");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = '0',`config_default` = '1' WHERE `config_namekey`='variant_increase_perf';");
			try{$this->db->execute();}catch(Exception $e){}
			$this->databaseHelper->addColumns("product","`product_page_title` varchar(255) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("category","`category_page_title` varchar(255) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("characteristic","`characteristic_ordering` INT( 12 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `characteristic_alias`");

			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_badge` (
	`badge_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`badge_name` varchar(255) NOT NULL DEFAULT '',
	`badge_image` varchar(255) NOT NULL DEFAULT '',
	`badge_start` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_end` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_category_id` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_category_childs` tinyint(4) NOT NULL DEFAULT '0',
	`badge_discount_id` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_ordering` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_size` float(12,2) unsigned NOT NULL,
	`badge_position` varchar(255) NOT NULL DEFAULT 'bottomleft',
	`badge_vertical_distance` int(10) NOT NULL DEFAULT '0',
	`badge_horizontal_distance` int(10) NOT NULL DEFAULT '0',
	`badge_margin` int(10) NOT NULL DEFAULT '0',
	`badge_published` tinyint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`badge_id`)
) ENGINE=MyISAM;");
			try{$this->db->execute();}catch(Exception $e){}

			$this->databaseHelper->addColumns("cart",array("`cart_type` varchar(25) NOT NULL DEFAULT 'cart'",
					"`cart_name` varchar(50) NOT NULL",
					"`cart_share` varchar(255) NOT NULL DEFAULT 'nobody'",
					"`cart_current` INT NOT NULL DEFAULT '0'"));

			$this->databaseHelper->addColumns("cart_product","`cart_product_wishlist_id` INT NOT NULL DEFAULT '0'");
			$this->databaseHelper->addColumns("order_product","`order_product_wishlist_id` INT NOT NULL DEFAULT '0'");

			$this->databaseHelper->addColumns("widget",array("`widget_published` tinyint(4) NOT NULL DEFAULT 1",
				"`widget_ordering` int(11) NOT NULL DEFAULT 0",
				"`widget_access` varchar(250) NOT NULL DEFAULT 'all'"));

			$this->db->setQuery("ALTER TABLE `#__hikashop_field` CHANGE `field_value` `field_value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
			try{$this->db->execute();}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion,'1.6.0','<')){
			$this->databaseHelper->addColumns("address","`address_street2` TEXT NOT NULL");
		}

		if(version_compare($this->fromVersion,'2.0.0','<')){
			$this->databaseHelper->addColumns("order",array("`order_invoice_number` VARCHAR( 255 ) NOT NULL DEFAULT ''","`order_invoice_id` INT NOT NULL DEFAULT '0'"));
			$this->db->setQuery("UPDATE `#__hikashop_order` SET `order_invoice_number`=`order_number`;");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("UPDATE `#__hikashop_order` SET `order_invoice_id`=`order_id`;");
			try{$this->db->execute();}catch(Exception $e){}
			$this->databaseHelper->addColumns("download","`file_pos` int(10) NOT NULL DEFAULT '1'");
			$this->db->setQuery("ALTER TABLE `#__hikashop_download` DROP PRIMARY KEY , ADD PRIMARY KEY (`file_id`, `order_id`, `file_pos`);");
			try{$this->db->execute();}catch(Exception $e){}
			$this->databaseHelper->addColumns("product_category`","`product_parent_id` INT NOT NULL DEFAULT '0'");

			$file = HIKASHOP_BACK.'admin.hikashop.php';
			if(file_exists($file)) JFile::delete($file);
		}
		if(version_compare($this->fromVersion,'2.0.0','=')){
			$this->databaseHelper->addColumns("product_category","`product_parent_id` INT NOT NULL DEFAULT '0'");
		}
		if(version_compare($this->fromVersion,'2.1.0','<')){
			$this->databaseHelper->addColumns("product","`product_alias` VARCHAR( 255 ) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("category","`category_alias` VARCHAR( 255 ) NOT NULL DEFAULT ''");

			if($this->level=='starter'){
				$this->db->setQuery("DELETE FROM `#__hikashop_widget` ;");
				try{$this->db->execute();}catch(Exception $e){}
			}
			$this->databaseHelper->addColumns("order","`order_invoice_created` INT(10) UNSIGNED NOT NULL DEFAULT '0'");
			$this->db->setQuery("UPDATE #__hikashop_order SET `order_invoice_created` = `order_created` WHERE `order_invoice_created`=0 AND `order_invoice_id`>0;");
			try{$this->db->execute();}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion,'2.1.1','<')){
			$this->databaseHelper->addColumns("product","`product_price_percentage` decimal(15,7) NOT NULL DEFAULT '0'");
			$this->databaseHelper->addColumns("discount","`discount_affiliate` INT(10) NOT NULL DEFAULT '0'");
			$this->databaseHelper->addColumns("badge","`badge_keep_size` INT(10) NOT NULL DEFAULT '0'");
		}
		if(version_compare($this->fromVersion,'2.1.2','<')){
			$this->databaseHelper->addColumns("product",array("`product_canonical` VARCHAR( 255 ) NOT NULL DEFAULT ''","`product_msrp` decimal(15,7) NULL DEFAULT '0'"));
			$this->databaseHelper->addColumns("badge","`badge_quantity` VARCHAR( 255 ) NULL DEFAULT ''");
			$this->databaseHelper->addColumns("category",array("`category_canonical` VARCHAR( 255 ) NOT NULL DEFAULT ''","`category_site_id` VARCHAR( 255 ) NULL DEFAULT ''"));
		}
		if(version_compare($this->fromVersion, '2.2.0', '<')) {
			$this->databaseHelper->addColumns("payment",array("`payment_ordering` int(10) unsigned NOT NULL DEFAULT '0'",
				"`payment_published` tinyint(4) NOT NULL DEFAULT '1'"));

			$this->db->setQuery("ALTER TABLE `#__hikashop_payment` DROP INDEX payment_type");
			try{$this->db->execute();}catch(Exception $e){}

			$this->databaseHelper->addColumns("order",array("`order_shipping_params` text NOT NULL DEFAULT ''",
				"`order_payment_params` text NOT NULL DEFAULT ''"));

			$this->databaseHelper->addColumns("order_product",array(
				"`order_product_shipping_id` varchar(255) NOT NULL DEFAULT ''",
				"`order_product_shipping_method` varchar(255) NOT NULL DEFAULT ''",
				"`order_product_shipping_price` decimal(17,5) NOT NULL DEFAULT '0.00000'",
				"`order_product_shipping_tax` decimal(17,5) NOT NULL DEFAULT '0.00000'",
				"`order_product_shipping_params` varchar(255) NOT NULL DEFAULT ''"));

			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_massaction` (
	`massaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`massaction_name` varchar(255) NOT NULL DEFAULT '',
	`massaction_description` text NOT NULL,
	`massaction_table` varchar(255) NOT NULL DEFAULT 'product',
	`massaction_published` tinyint(4) NOT NULL DEFAULT '1',
	`massaction_lasttime` int(10) unsigned NOT NULL DEFAULT '0',
	`massaction_triggers` text NOT NULL,
	`massaction_filters` text NOT NULL,
	`massaction_actions` text NOT NULL,
	`massaction_report` text NOT NULL,
	PRIMARY KEY (`massaction_id`),
	KEY `massaction_table` (`massaction_table`)
) ENGINE=MyISAM;");
			try{$this->db->execute();}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion, '2.2.1', '<')) {
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_plugin` (
	`plugin_id` INT(10) NOT NULL AUTO_INCREMENT,
	`plugin_type` VARCHAR(255) NOT NULL,
	`plugin_published` INT(4) NOT NULL DEFAULT 0,
	`plugin_name` VARCHAR(255) NOT NULL,
	`plugin_ordering` INT(10) NOT NULL DEFAULT 0,
	`plugin_description` TEXT NOT NULL DEFAULT '',
	`plugin_params` TEXT NOT NULL DEFAULT '',
	`plugin_access` VARCHAR(255) NOT NULL DEFAULT 'all',
	PRIMARY KEY (`plugin_id`)
) ENGINE=MyISAM");
			try{$this->db->execute();}catch(Exception $e){}

			$this->databaseHelper->addColumns("field","`field_display` text NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("badge","`badge_url` VARCHAR( 255 ) NULL DEFAULT ''");
		}
		if(version_compare($this->fromVersion, '2.2.2', '<')) {
			$this->databaseHelper->addColumns("taxation","`taxation_post_code` VARCHAR( 255 ) NULL DEFAULT ''");
			$this->databaseHelper->addColumns("product","`product_display_quantity_field` SMALLINT DEFAULT 0");

			jimport('joomla.filesystem.file');
			jimport('joomla.filesystem.folder');
			$lng_override_folder = hikashop_getLanguagePath(JPATH_ROOT).DS.'overrides';
			if(JFolder::exists($lng_override_folder)) {
				$lngFiles = JFolder::files($lng_override_folder);
				if(!empty($lngFiles)) {
					foreach($lngFiles as $lngfile) {
						$content = file_get_contents($lng_override_folder.DS.$lngfile);
						if(!empty($content) && strpos($content, 'PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER="') !== false) {
							$content = preg_replace('#PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER="(.*)"#', 'PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER="\1"'."\r\n".'PLEASE_ACCEPT_TERMS="\1"', $content);
							JFile::write($lng_override_folder.DS.$lngfile, $content);
							unset($content);
						}
					}
					unset($lngFiles);
				}
			}
		}
		if(version_compare($this->fromVersion, '2.2.3', '<')) {
			$this->databaseHelper->addColumns("cart","`cart_params` text NOT NULL DEFAULT ''");
		}
		if(version_compare($this->fromVersion, '2.3.0', '<')) {
			$this->databaseHelper->addColumns("taxation",array("`taxation_date_start` int(10) unsigned NOT NULL DEFAULT '0'","`taxation_date_end` int(10) unsigned NOT NULL DEFAULT '0'"));
			$this->db->setQuery("
			CREATE TABLE IF NOT EXISTS `#__hikashop_warehouse` (
				`warehouse_id` INT(10) NOT NULL AUTO_INCREMENT,
				`warehouse_name` VARCHAR(255) NOT NULL DEFAULT '',
				`warehouse_published` tinyint(4) NOT NULL DEFAULT '1',
				`warehouse_description` TEXT NOT NULL,
				`warehouse_ordering` INT(10) NOT NULL DEFAULT 0,
				`warehouse_created` int(10) DEFAULT NULL,
				`warehouse_modified` int(10) DEFAULT NULL,
				PRIMARY KEY (`warehouse_id`)
			) ENGINE=MyISAM");
			try{$this->db->execute();}catch(Exception $e){}

			$this->databaseHelper->addColumns("product","`product_warehouse_id` int(10) unsigned NOT NULL DEFAULT '0'");

			if(file_exists(HIKASHOP_MEDIA.'css'.DS.'frontend_old.css')){
				$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'old',`config_default` = 'old' WHERE `config_namekey` = 'css_frontend' AND `config_value` = 'default' ");
				try{$this->db->execute();}catch(Exception $e){}
			}
		}
		if(version_compare($this->fromVersion, '2.3.1', '<')) {
			$this->databaseHelper->addColumns("product","`product_quantity_layout` varchar(255) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("category","`category_quantity_layout` varchar(255) NOT NULL DEFAULT ''");
		}
		if(version_compare($this->fromVersion, '2.3.2', '<')) {
			$this->databaseHelper->addColumns("order","`order_site_id` varchar(255) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("price","`price_site_id` varchar(255) NOT NULL DEFAULT ''");

			$this->databaseHelper->addColumns("characteristic",array(
				"`characteristic_display_type` varchar(255) NOT NULL DEFAULT ''",
				"`characteristic_params` TEXT NOT NULL DEFAULT ''"
			));
		}
		if(version_compare($this->fromVersion, '2.3.4', '<')) {
			$this->databaseHelper->addColumns("taxation",array(
				"`taxation_internal_code` varchar(15) NOT NULL DEFAULT ''",
				"`taxation_note` TEXT NOT NULL",
				"`taxation_site_id` varchar(255) NOT NULL DEFAULT ''"
			));
			$this->databaseHelper->addColumns("shipping","`shipping_currency` varchar(255) NOT NULL DEFAULT ''");
		}
		if(version_compare($this->fromVersion, '2.4.0', '<')) {
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` CHANGE `discount_product_id` `discount_product_id` VARCHAR(255) NOT NULL DEFAULT '';");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` CHANGE `discount_category_id` `discount_category_id` VARCHAR(255) NOT NULL DEFAULT '';");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` CHANGE `discount_zone_id` `discount_zone_id` VARCHAR(255) NOT NULL DEFAULT '';");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("ALTER TABLE `#__hikashop_badge` CHANGE `badge_discount_id` `badge_discount_id` VARCHAR(255) NOT NULL DEFAULT '';");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("ALTER TABLE `#__hikashop_badge` CHANGE `badge_category_id` `badge_category_id` VARCHAR(255) NOT NULL DEFAULT '';");
			try{$this->db->execute();}catch(Exception $e){}
			$this->databaseHelper->addColumns("field","`field_products` varchar(255) NOT NULL DEFAULT ''");
		}
		if(version_compare($this->fromVersion, '2.5.0', '<')) {
			$this->databaseHelper->addColumns("order","`order_currency_info` text NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns("taxation","`taxation_ordering` int(10) unsigned NOT NULL DEFAULT '0'");
			$this->databaseHelper->addColumns("characteristic","`characteristic_display_method` varchar(255) NOT NULL DEFAULT ''");
		}
		if(version_compare($this->fromVersion, '2.6.0', '<')) {
			$this->db->setQuery("ALTER TABLE `#__hikashop_filter` CHANGE `filter_category_id` `filter_category_id` VARCHAR(255) NOT NULL DEFAULT '';");
			try{$this->db->execute();}catch(Exception $e){}

			$this->databaseHelper->addColumns("discount", "`discount_site_id` VARCHAR(255) NULL DEFAULT '';");

			$this->databaseHelper->addColumns("order", array("`order_payment_tax` decimal(12,5) NOT NULL DEFAULT '0.00000'"));
		}
		if(version_compare($this->fromVersion, '2.6.1', '<')) {
			$this->databaseHelper->addColumns("badge", "`badge_access` varchar(255) NOT NULL DEFAULT 'all';");
		}
		if(version_compare($this->fromVersion, '2.6.2', '<')) {
			$this->db->setQuery("UPDATE `#__hikashop_field` SET `field_display` = CONCAT(CASE WHEN `field_display` = '' THEN CONCAT(';',`field_display`) ELSE `field_display` END ,'field_product_show=',`field_frontcomp`,';field_product_compare=',`field_frontcomp`,';field_product_frontend_cart_details=0;field_product_form=',`field_backend`,';field_product_invoice=0;field_product_shipping_invoice=0;field_product_order_form=0;field_product_backend_cart_details=0;field_product_order_notification=0;field_product_order_status_notification=0;field_product_order_creation_notification=0;field_product_order_admin_notification=0;field_product_payment_notification=0;field_product_frontend_listing=0;field_product_listing=',`field_backend_listing`,';') WHERE `field_table` LIKE 'product';");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("UPDATE `#__hikashop_field` SET `field_display`= CONCAT(CASE WHEN `field_display` = '' THEN CONCAT(';',`field_display`) ELSE `field_display` END ,'field_order_show=',`field_frontcomp`,';field_order_checkout=',`field_frontcomp`,';field_order_invoice=',`field_backend`,';field_order_shipping_invoice=',`field_backend`,';field_order_form=',`field_backend`,';field_order_edit_fields=',`field_backend`,';field_order_notification=',`field_backend`,';field_order_status_notification=',`field_backend`,';field_order_creation_notification=',`field_backend`,';field_order_admin_notification=',`field_backend`,';field_order_payment_notification=',`field_backend`,';field_order_listing=0;') WHERE `field_table` LIKE 'order';");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("UPDATE `#__hikashop_field` SET `field_display`= CONCAT(CASE WHEN `field_display` = '' THEN CONCAT(';',`field_display`) ELSE `field_display` END ,'field_item_show_cart=',`field_frontcomp`,';field_item_checkout=',`field_frontcomp`,';field_item_order=',`field_frontcomp`,';field_item_product_listing=',`field_frontcomp`,';field_item_product_show=',`field_frontcomp`,';field_item_product_cart=',`field_frontcomp`,';field_item_order_form=',`field_backend`,';field_item_invoice=',`field_backend`,';field_item_shipping_invoice=',`field_backend`,';field_item_edit_product_order=',`field_backend`,';field_item_backend_cart_details=0;field_item_order_notification=',`field_backend`,';field_item_order_status_notification=',`field_backend`,';field_item_order_creation_notification=',`field_backend`,';field_item_order_admin_notification=',`field_backend`,';field_item_payment_notification=',`field_backend`,';') WHERE `field_table` LIKE 'item';");
			try{$this->db->execute();}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion, '3.0.0', '<')) {
			$this->databaseHelper->addColumns('product', "`product_sort_price` decimal(17,5) NOT NULL DEFAULT '0.00000'");
			$this->databaseHelper->addColumns('address', "`address_type` varchar(50) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns('order', array(
				"`order_lang` varchar(255) NOT NULL DEFAULT ''",
				"`order_token` varchar(255) NOT NULL DEFAULT ''",
			));

			$this->databaseHelper->addColumns('order_product', array(
				"`order_product_status` varchar(255) NOT NULL DEFAULT ''",
				"`order_product_wishlist_product_id` int(11) NOT NULL DEFAULT '0'"
			));
			$this->databaseHelper->addColumns('cart_product', "`cart_product_wishlist_product_id` int(11) NOT NULL DEFAULT '0'");

			$this->databaseHelper->addColumns('cart', array(
				"`cart_currency_id` int(10) unsigned NOT NULL DEFAULT '0'",
				"`cart_payment_id` int(10) unsigned NOT NULL DEFAULT '0'",
				"`cart_shipping_ids` varchar(255) NOT NULL DEFAULT ''",
				"`cart_billing_address_id` int(10) unsigned NOT NULL DEFAULT '0'",
				"`cart_shipping_address_ids` varchar(255) NOT NULL DEFAULT ''",
				"`cart_fields` longtext NOT NULL DEFAULT ''",
			));

			$this->db->setQuery("ALTER TABLE `#__hikashop_order_product` CHANGE `order_product_shipping_params` `order_product_shipping_params` text NOT NULL DEFAULT '';");
			try{$this->db->execute();}catch(Exception $e){}

			$query = 'UPDATE `#__hikashop_cart` AS cart '.
					' JOIN `#__hikashop_user` AS hk_user ON cart.user_id = hk_user.user_cms_id '.
					' SET cart.user_id = hk_user.user_id';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}

			$this->db->setQuery("
				CREATE TABLE IF NOT EXISTS `#__hikashop_orderstatus` (
					`orderstatus_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`orderstatus_name` varchar(255) NOT NULL,
					`orderstatus_description` text NOT NULL,
					`orderstatus_published` tinyint(4) NOT NULL DEFAULT '0',
					`orderstatus_ordering` int(10) unsigned NOT NULL DEFAULT '0',
					`orderstatus_namekey` varchar(255) NOT NULL,
					`orderstatus_email_params` text NOT NULL DEFAULT '',
					`orderstatus_links_params` text NOT NULL DEFAULT '',
					PRIMARY KEY (`orderstatus_id`),
					UNIQUE KEY `orderstatus_namekey` (`orderstatus_namekey`)
				) ENGINE=MyISAM");
			try{$this->db->execute();}catch(Exception $e){}

			$query = 'INSERT IGNORE INTO `#__hikashop_orderstatus` (orderstatus_name, orderstatus_description, orderstatus_published, orderstatus_ordering, orderstatus_namekey) '.
					' SELECT category_name, category_description, category_published, category_ordering, category_namekey FROM `#__hikashop_category` AS c '.
					' WHERE c.category_type = \'status\' AND c.category_depth > 1';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}

			$query = 'UPDATE `#__hikashop_order` AS o JOIN `#__hikashop_orderstatus` AS os ON o.order_status = os.orderstatus_namekey SET o.order_status = os.orderstatus_name '.
					' WHERE os.orderstatus_namekey LIKE \'status_%\'';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}

			$query = 'UPDATE `#__hikashop_order` AS o JOIN `#__hikashop_user` AS u ON o.order_user_id = u.user_id SET o.order_token = u.user_email';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}

			$query = 'UPDATE `#__hikashop_orderstatus` SET orderstatus_namekey = orderstatus_name '.
					' WHERE orderstatus_namekey LIKE \'status_%\'';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}

			$query = 'UPDATE `#__hikashop_config` SET config_value = 1 '.
					' WHERE config_namekey = \'show_quantity_field\' AND config_value < 2';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}

			$hikashopEmails = array(
				'order_admin_notification',
				'order_status_notification',
				'order_creation_notification',
				'order_notification',
				'waitlist_notificationation',
				'contact_request',
				'user_account_admin_notification',
				'user_account',
				'new_comment',
				'order_cancel',
				'subscription_eot',
				'cron_report'
			);

			jimport('joomla.filesystem.file');

			foreach($hikashopEmails as $hikashopEmail){
				$path = HIKASHOP_MEDIA . 'mail' . DS . $hikashopEmail . '.html.modified.php';
				if(JPath::check($path) && file_exists($path)){
					$this->db->setQuery("UPDATE `#__hikashop_config` SET `config_value` = '' WHERE `config_namekey` = ".$this->db->Quote($hikashopEmail.'.template'));
					try{$this->db->execute();}catch(Exception $e){}
				}
			}
		}
		if(version_compare($this->fromVersion, '3.1.0', '<')) {
			$this->databaseHelper->addColumns('price', "`price_users` varchar(255) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns('product_related', "`product_related_quantity` int(10) unsigned NOT NULL DEFAULT '0'");
		}
		if(version_compare($this->fromVersion, '3.1.1', '<')) {
			$query = 'UPDATE `#__hikashop_config` SET config_value = 1 '.
					' WHERE config_namekey = \'carousel_legacy\'';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion, '3.2.0', '<')) {
			$this->databaseHelper->addColumns('waitlist', "`language` varchar(255) NOT NULL DEFAULT ''");
			$this->databaseHelper->addColumns('cart', "`cart_ip` varchar(255) NOT NULL DEFAULT ''");
		}

		if(version_compare($this->fromVersion, '3.3.0', '<')) {
			$this->databaseHelper->addColumns('discount', array(
				"`discount_tax` tinyint(3) unsigned DEFAULT '0'",
				"`discount_user_id` varchar(255) NOT NULL DEFAULT ''",
			));
			$this->databaseHelper->addColumns('product', array(
				"`product_description_raw` text NULL",
				"`product_description_type` varchar(255) NULL",
				"`product_option_method` smallint(5) unsigned NOT NULL DEFAULT '0',",
				"`product_condition` varchar(255) NULL"
			));
			$this->databaseHelper->addColumns('price', array(
				"`price_start_date` int(11) unsigned NOT NULL DEFAULT '0'",
				"`price_end_date` int(11) unsigned NOT NULL DEFAULT '0'",
			));

			$this->db->setQuery("ALTER TABLE `#__hikashop_product` CHANGE `product_meta_description` `product_meta_description` text NOT NULL;");
			try{$this->db->execute();}catch(Exception $e){}
			$this->db->setQuery("ALTER TABLE `#__hikashop_category` CHANGE `category_meta_description` `category_meta_description` text NOT NULL;");
			try{$this->db->execute();}catch(Exception $e){}
		}

		if(version_compare($this->fromVersion, '3.4.0', '<')) {
			$this->databaseHelper->addColumns('discount', array(
				"`discount_shipping_percent` decimal(12,3) NOT NULL DEFAULT '0.000'",
			));
		}

		if(version_compare($this->fromVersion, '3.4.1', '<')) {
			$config = hikashop_config();
			$discount_before_tax = $config->get('discount_before_tax');
			$query = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES
				('.$this->db->Quote('coupon_before_tax').','.$this->db->Quote($discount_before_tax).','.$this->db->Quote($discount_before_tax).')';
			$this->db->setQuery($query);
			$this->db->execute();
		}

		if(version_compare($this->fromVersion, '3.6.0', '<')) {
			$this->databaseHelper->addColumns('order_product', array(
				"`order_product_params` TEXT NULL",
			));
			$this->databaseHelper->addColumns('order', array(
				"`order_parent_id` int(10) unsigned NOT NULL DEFAULT '0'",
			));
			$this->databaseHelper->addColumns('cart_product', array(
				"`cart_product_ref_price` decimal(17,5) DEFAULT NULL",
			));
		}
		if(version_compare($this->fromVersion, '4.0.1', '<')) {
			$this->databaseHelper->addColumns('shipping_price', array(
				"`shipping_blocked` tinyint(3) unsigned NOT NULL DEFAULT '0'",
			));

			$query = 'UPDATE `#__hikashop_shipping_price` SET shipping_price_value = 0, shipping_fee_value = 0, shipping_blocked = 1 '.
					' WHERE shipping_price_value = -1 OR shipping_fee_value = -1';

			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion, '4.0.3', '<')) {
			$query = 'ALTER TABLE `#__hikashop_product` CHANGE `product_option_method` `product_option_method` VARCHAR(255) NOT NULL DEFAULT \'\'';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}

		}
		if(version_compare($this->fromVersion, '4.1.0', '<')) {
			$this->databaseHelper->addColumns("badge", "`badge_new_period` int(10) unsigned NOT NULL DEFAULT '0'");
		}

		if(version_compare($this->fromVersion, '4.1.1', '<')) {
			$this->databaseHelper->addColumns("field", "`field_address_type` varchar(50) DEFAULT ''");
		}
		if(version_compare($this->fromVersion, '4.3.0', '<')) {
			$this->databaseHelper->addColumns("orderstatus", "`orderstatus_color` varchar(255) NOT NULL DEFAULT ''");
		}

		if(version_compare($this->fromVersion, '4.4.0', '<')) {
			$query = 'UPDATE `#__hikashop_config` SET config_value = 0 '.
					' WHERE config_namekey = \'add_to_cart_legacy\'';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}
			$query = 'UPDATE `#__hikashop_config` SET config_value = 0 '.
					' WHERE config_namekey = \'checkout_legacy\'';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}
			$query = 'UPDATE `#__hikashop_config` SET config_value = \'vex\' '.
					' WHERE config_namekey = \'popup_mode\'';
			$this->db->setQuery($query);
			try{$this->db->execute();}catch(Exception $e){}

			$this->databaseHelper->addColumns("order", array(
				"`order_weight` decimal(12,3) unsigned NULL",
				"`order_weight_unit` varchar(255) NULL",
				"`order_volume` decimal(12,3) unsigned NULL",
				"`order_dimension_unit` varchar(255) NULL",
			));
			$this->databaseHelper->addColumns("order_product", array(
				"`order_product_weight` decimal(12,3) unsigned NULL",
				"`order_product_weight_unit` varchar(255) NULL",
				"`order_product_width` decimal(12,3) unsigned NULL",
				"`order_product_length` decimal(12,3) unsigned NULL",
				"`order_product_height` decimal(12,3) unsigned NULL",
				"`order_product_dimension_unit` varchar(255) NULL",
			));
		}
		if(version_compare($this->fromVersion, '4.4.1', '<')) {
			$this->databaseHelper->addColumns("field", array(
				"`field_shipping_id` varchar(255) NOT NULL DEFAULT ''",
				"`field_payment_id` varchar(255) NOT NULL DEFAULT ''",
			));

			$this->db->setQuery("ALTER TABLE `#__hikashop_limit` CHANGE `limit_category_id` `limit_category_id` VARCHAR(255) NOT NULL DEFAULT '';");
			try{$this->db->execute();}catch(Exception $e){}
		}

		if(version_compare($this->fromVersion, '4.4.2', '<')) {
			$this->databaseHelper->addColumns("file", array(
				"`file_access` varchar(255) NOT NULL DEFAULT 'all'",
				"`file_time_limit`int(11) NOT NULL DEFAULT '0'",
			));
		}

		if(version_compare($this->fromVersion, '4.4.4', '<')) {
			$this->databaseHelper->addColumns("discount", array(
				"`discount_maximum_order` decimal(17,5) NOT NULL DEFAULT '0.00000'",
				"`discount_maximum_products` int(10) unsigned DEFAULT '0'",
			));
		}
		if(version_compare($this->fromVersion, '4.4.5', '<')) {
			$config = hikashop_config();
			$query = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES
				(\'action_button_type\',\'a\',\'button\')';
			$this->db->setQuery($query);
			$this->db->execute();
		}

		if(version_compare($this->fromVersion, '4.5.1', '<')) {
			$this->databaseHelper->addColumns("order_product", array(
				"`order_product_price_before_discount` decimal(17,5) NOT NULL DEFAULT '0.00000'",
				"`order_product_tax_before_discount` decimal(17,5) NOT NULL DEFAULT '0.00000'",
				"`order_product_discount_code` varchar(255) NULL",
				"`order_product_discount_info` text NULL",
			));
		}

		if(version_compare($this->fromVersion, '4.5.2', '<')) {
			$this->databaseHelper->addColumns("massaction", array(
				"`massaction_button` tinyint(4) signed DEFAULT '0'",
			));
			$this->databaseHelper->addColumns("characteristic", array(
				"`characteristic_values_on_listing` tinyint(4) signed DEFAULT '0'",
			));

		}

	}

	public function addPref() {
		$app = JFactory::getApplication();
		$jconf = JFactory::getConfig();

		$this->level = ucfirst($this->level);
		$allPref = array(
			'level' => $this->level,
			'version' => $this->version,
			'bounce_email' => '',
			'add_names' => 1,
			'encoding_format' => 'base64',
			'charset' => 'UTF-8',
			'word_wrapping' => 150,
			'embed_images' => 0,
			'embed_files' => 1,
			'multiple_part' => 1,
			'allowedfiles' => 'zip,doc,docx,pdf,xls,txt,gz,gzip,rar,jpg,jpeg,gif,tar.gz,xlsx,pps,csv,bmp,epg,ico,odg,odp,ods,odt,png,svg,webp,ppt,xcf,wmv,avi,mkv,mp3,ogg,flac,wma,fla,flv,mp4,wav,aac,mov,epub',
			'allowedimages' => 'gif,jpg,jpeg,png,svg,webp',
			'uploadfolder' => 'images/com_hikashop/upload/',
			'uploadsecurefolder' => 'media/com_hikashop/upload/safe/',
			'editor' => 0,
			'cron_next' => 1251990901,
			'cron_last' => 0,
			'cron_fromip' => '',
			'cron_report' => '',
			'cron_frequency' => 900,
			'cron_sendreport' => 2,
			'cron_fullreport' => 1,
			'cron_savereport' => 2,
			'cron_savepath' => 'media/com_hikashop/upload/safe/logs/report_'.rand(0,999999999).'.log',
			'payment_log_file' => 'media/com_hikashop/upload/safe/logs/report_'.rand(0,999999999).'.log',
			'notification_created' => '',
			'notification_accept' => '',
			'notification_refuse' => '',
			'bootstrap_design' => 0,
			'characteristics_values_sorting' => 'ordering',
			'popup_mode' => 'vex',
			'force_canonical_urls' => 0,

			'opacity' => 100,
			'order_number_format' => '{automatic_code}',
			'checkout_cart_delete' => 1,
			'variant_default_publish' => 1,
			'force_ssl' => 0,
			'simplified_registration' => 0,
			'tax_zone_type' => 'billing',
			'discount_before_tax' => 0,
			'default_type' => 'individual',
			'main_tax_zone' => 1375,
			'main_currency' => 1,

			'order_created_status' => 'created',
			'order_confirmed_status' => 'confirmed',
			'invoice_order_statuses' => 'confirmed,shipped',
			'order_status_for_download' => 'shipped,confirmed',
			'partner_valid_status' => 'confirmed,shipped',
			'cancelled_order_status' => 'cancelled,refunded',
			'cancellable_order_status' => '',
			'order_unpaid_statuses' => 'created',

			'download_time_limit' => 2592000,
			'click_validity_period' => 2592000,
			'click_min_delay' => 86400,
			'partner_currency' => 1,
			'allow_currency_selection' => 0,
			'partner_click_fee' => 0,
			'partner_lead_fee' => 0,
			'ajax_add_to_cart' => 0,
			'partner_percent_fee' => 0,
			'partner_flat_fee' => 0,
			'affiliate_terms' => '',
			'download_number_limit' => 50,
			'button_style' => 'normal',
			'readmore' => 0,
			'menu_style' => 'title_bottom',
			'show_cart_image' => 1,
			'thumbnail' => 1,
			'thumbnail_x' => 100,
			'thumbnail_y' => 100,
			'product_image_x' => 100,
			'product_image_y' => 100,
			'image_x' => '',
			'image_y' => '',
			'add_webp_images' => 0,
			'max_x_popup' => 760,
			'max_y_popup' => 480,
			'vat_check' => 0,
			'default_translation_publish' => 1,
			'multilang_display' => 'popups',
			'volume_symbols' => 'm,dm,cm,mm,in,ft,yd',
			'weight_symbols' => 'kg,g,mg,lb,oz,ozt',
			'store_address' => "ACME Corporation\nGuildhall\nPO Box 270, London\nUnited Kingdom",
			'checkout' => 'login_address_shipping_payment_confirm_coupon_cart_status_fields,end',
			'display_checkout_bar' => 0,
			'show_vote_product' => 1,
			'affiliate_advanced_stats' => 1,
			'cart_retaining_period' => 2592000,
			'default_params' => '',
			'default_image' => 'barcode.png',
			'characteristic_display' => 'dropdown',
			'characteristic_display_text' => 1,
			'show_quantity_field' => 1,
			'show_cart_price' => 1,
			'show_cart_quantity' => 1,
			'show_cart_delete' => 1,
			'catalogue' => 0,
			'redirect_url_after_add_cart' => 'checkout',
			'redirect_url_when_cart_is_empty' => '',
			'cart_retaining_period_checked' => 1278664651,
			'auto_submit_methods' => 1,
			'clean_cart_when_order_created' => 'order_confirmed',
			'display_add_to_cart_for_free_products' => 1,

			'category_image' => 1, // not changeable yet
			'category_explorer' => 1,
			'detailed_tax_display' => 1,
			'order_status_notification.subject' => 'ORDER_STATUS_NOTIFICATION_SUBJECT',
			'order_creation_notification.subject' => 'ORDER_CREATION_NOTIFICATION_SUBJECT',
			'order_notification.subject' => 'ORDER_NOTIFICATION_SUBJECT',
			'user_account.subject' => 'USER_ACCOUNT_SUBJECT',
			'user_account_admin_notification.subject' => 'HIKA_USER_ACCOUNT_ADMIN_NOTIFICATION_SUBJECT',
			'cron_report.subject' => 'CRON_REPORT_SUBJECT',
			'order_status_notification.html' => 1,
			'order_status_notification.published' => 1,
			'order_status_notification.template' => 'default',
			'order_creation_notification.html' => 1,
			'order_creation_notification.published' => 1,
			'order_creation_notification.template' => 'default',
			'order_notification.html' => 1,
			'order_notification.published' => 1,
			'order_notification.template' => 'default',
			'order_admin_notification.html' => 1,
			'order_admin_notification.subject' => 'ORDER_ADMIN_NOTIFICATION_SUBJECT',
			'order_admin_notification.published' => 1,
			'order_admin_notification.template' => 'admin',
			'payment_notification.html' => 1,
			'payment_notification.subject' => 'PAYMENT_NOTIFICATION_SUBJECT',
			'payment_notification.published' => 1,
			'payment_notification.template' => 'admin',
			'new_comment.html' => 1,
			'new_comment.template' => 'admin_notification',
			'new_comment.subject' => 'NEW_COMMENT_NOTIFICATION_SUBJECT',
			'new_comment.published' => 1,
			'contact_request.html' => 1,
			'contact_request.published' => 1,
			'contact_request.template' => 'default',
			'massaction_notification.html' => 1,
			'massaction_notification.published' => 1,
			'unfinished_order.published' => 1,
			'user_account.html' => 1,
			'user_account.template' => 'user_notification',
			'subscription_eot.template' => 'user_notification',
			'user_account_admin_notification.html' => 1,
			'user_account_admin_notification.template' => 'admin_notification',
			'out_of_stock.html' => 1,
			'out_of_stock.template' => 'admin_notification',
			'out_of_stock.subject' => 'OUT_OF_STOCK_NOTIFICATION_SUBJECT',
			'user_account.published' => 1,
			'user_account_admin_notification.published' => 1,
			'cron_report.html' => 1,
			'cron_report.template' => 'admin_notification',
			'cron_report.published' => 1,
			'out_of_stock.published' => 1,
			'waitlist_notification.html' => 1,
			'waitlist_notification.subject' => 'WAITLIST_NOTIFICATION_SUBJECT',
			'waitlist_notification.published' => 1,
			'waitlist_notification.template' => 'default',
			'order_cancel.html' => 1,
			'order_cancel.template' => 'admin_notification',
			'order_cancel.subject' => 'ORDER_CANCEL_SUBJECT',
			'order_cancel.published' => 1,
			'wishlist_share.html' => 1,
			'wishlist_share.subject' => 'WISHLIST_SHARE_EMAIL_SUBJECT',
			'wishlist_share.published' => 1,
			'wishlist_share.template' => 'default',

			'variant_increase_perf' => 1,

			'checkout_legacy' => 0,
			'add_to_cart_legacy' => 0,
			'legacy_widgets' => 0,
			'carousel_legacy' => 0,

			'show_footer' => 1,
			'no_css_header' => 0,
			'pathway_sef_name' => 'category_pathway',
			'related_sef_name' => 'related_product',
			'css_module' => 'default',
			'css_frontend' => 'default',
			'css_backend' => 'default',
			'installcomplete' => 0,
			'Starter' => 0,
			'Essential' => 1,
			'Business' => 2,
			'Enterprise' => 3,
			'Unlimited' => 9,
		);

		if(version_compare(JVERSION, '3.0', '<')) {
			$allPref['from_name'] = $jconf->getValue('config.fromname');
			$allPref['from_email'] = $jconf->getValue('config.mailfrom');
			$allPref['reply_name'] = $jconf->getValue('config.fromname');
			$allPref['reply_email'] = $jconf->getValue('config.mailfrom');

			$allPref['payment_notification_email'] = $allPref['order_creation_notification_email'] = $allPref['cron_sendto'] = $jconf->getValue('config.mailfrom');
		} else {
			$allPref['from_name'] = $jconf->get('fromname');
			$allPref['from_email'] = $jconf->get('mailfrom');
			$allPref['reply_name'] = $jconf->get('fromname');
			$allPref['reply_email'] = $jconf->get('mailfrom');

			$allPref['payment_notification_email'] = $allPref['order_creation_notification_email'] = $allPref['cron_sendto'] = $jconf->get('mailfrom');
		}

		$descriptions = array('Joomla!® Shopping Cart Extension','Joomla!® E-Commerce Extension','Joomla!® Online Shop System','Joomla!® Online Store Component');
		$allPref['description_starter'] = $descriptions[rand(0,3)];
		$allPref['description_essential'] = $descriptions[rand(0,3)];
		$allPref['description_business'] = $descriptions[rand(0,3)];

		$border_visible = (version_compare(JVERSION, '3.0', '<') ? 1 : 2);
		$allPref['default_params'] = base64_encode('a:34:{s:14:"border_visible";s:1:"'.$border_visible.'";s:11:"add_to_cart";s:1:"1";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"21";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:15:"moduleclass_sfx";s:0:"";s:7:"modules";s:0:"";s:19:"content_synchronize";s:1:"1";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:6:"random";s:1:"0";s:19:"product_synchronize";s:1:"1";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"1";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:17:"div_custom_fields";s:0:"";s:6:"height";s:3:"150";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:24:"links_on_main_categories";s:1:"0";s:20:"link_to_product_page";s:1:"1";s:14:"display_badges";s:1:"1";}');

		if(version_compare(JVERSION,'3.0', '>=') || in_array($app->getTemplate(), array('rt_missioncontrol','aplite'))) {
			$allPref['menu_style'] = 'content_top';
		}

		$query = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES ';
		foreach($allPref as $namekey => $value) {
			$query .= '('.$this->db->Quote($namekey).','.$this->db->Quote($value).','.$this->db->Quote($value).'),';
		}
		$query = rtrim($query,',');
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function addModules() {
		if(!$this->freshinstall)
			return true;

		$elements = array(new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass());
		$elements[0]->title = JText::_('HIKASHOP_RANDOM_MODULE');
		$elements[1]->title = JText::_('RECENTLY_VIEWED');
		$elements[2]->title = JText::_('HIKASHOP_CATEGORIES_1_MODULE');
		$elements[3]->title = JText::_('HIKASHOP_CATEGORIES_2_MODULE');
		$elements[4]->title = JText::_('HIKASHOP_BEST_SELLERS_MODULE');
		$elements[5]->title = JText::_('HIKASHOP_LATEST_MODULE');
		$elements[6]->title = JText::_('MANUFACTURERS');
		$elements[7]->title = JText::_('HIKASHOP_BEST_RATED_MODULE');
		$elements[8]->title = JText::_('RELATED_PRODUCTS');

		$modulesClass = hikashop_get('class.modules');
		$params = array();
		foreach($elements as $k => $element){
			if(version_compare(JVERSION,'4.0', '>=')) {
				$elements[$k]->position = 'sidebar-right';
			} else {
				$elements[$k]->position = 'position-7';
			}
			$elements[$k]->language = '*';
			$elements[$k]->access = 1;
			$elements[$k]->published = 0;
			$elements[$k]->module = 'mod_hikashop';
			$elements[$k]->params = '';
			$params[$k] = new stdClass();
			$params[$k]->id = $modulesClass->save($element);
		}
		$query = 'INSERT IGNORE INTO `#__modules_menu` (`moduleid`,`menuid`) VALUES ';
		foreach($params as $param){
			$query .= '('.$this->db->Quote($param->id).',0),';
		}
		$query = rtrim($query,',');
		$this->db->setQuery($query);
		$this->db->execute();

		$categoriesLength = strlen($this->menuid->categories);
		$brandsLength = strlen($this->menuid->brands);
		$id_related_module = $params[8]->id;

		$params[0]->params = 'a:39:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:1:"3";s:6:"random";s:1:"1";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"1";s:13:"product_order";s:8:"ordering";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:1:"1";s:15:"add_to_wishlist";s:1:"1";s:20:"link_to_product_page";s:1:"1";s:17:"show_vote_product";s:1:"0";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:2:"-1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:11:"pane_height";s:0:"";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:14:"border_visible";s:1:"0";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:13:"ul_class_name";s:0:"";s:15:"enable_carousel";s:1:"0";}';

		$params[1]->params = 'a:39:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:1:"3";s:6:"random";s:2:"-1";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:7:"inherit";s:19:"product_synchronize";s:1:"4";s:15:"recently_viewed";s:1:"1";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:7:"inherit";s:18:"child_display_type";s:7:"inherit";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:2:"-1";s:18:"number_of_products";s:2:"-1";s:16:"only_if_products";s:2:"-1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";s:15:"enable_carousel";s:1:"0";}';

		$params[2]->params = 'a:39:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:8:"category";s:11:"layout_type";s:4:"list";s:7:"columns";s:1:"1";s:5:"limit";s:2:"21";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:8:"ordering";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:1:"1";s:15:"add_to_wishlist";s:1:"1";s:20:"link_to_product_page";s:1:"1";s:17:"show_vote_product";s:1:"0";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"0";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:9:"allchilds";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:11:"pane_height";s:0:"";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:14:"border_visible";s:1:"0";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:13:"ul_class_name";s:0:"";s:15:"enable_carousel";s:1:"0";}';

		$params[3]->params = 'a:39:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:8:"category";s:11:"layout_type";s:4:"list";s:7:"columns";s:1:"1";s:5:"limit";s:2:"21";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:8:"ordering";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:1:"1";s:15:"add_to_wishlist";s:1:"1";s:20:"link_to_product_page";s:1:"1";s:17:"show_vote_product";s:1:"0";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"0";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:15:"allchildsexpand";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:11:"pane_height";s:0:"";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:14:"border_visible";s:1:"0";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:13:"ul_class_name";s:0:"";s:15:"enable_carousel";s:1:"0";}';

		$params[4]->params = 'a:39:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"21";s:6:"random";s:1:"0";s:9:"order_dir";s:4:"DESC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:13:"product_sales";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:15:"allchildsexpand";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";s:15:"enable_carousel";s:1:"0";}';

		$params[5]->params = 'a:39:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"21";s:6:"random";s:1:"0";s:9:"order_dir";s:4:"DESC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:15:"product_created";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:15:"allchildsexpand";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";s:15:"enable_carousel";s:1:"0";}';

		$params[6]->params = 'a:39:{s:6:"itemid";s:'.$brandsLength.':"'.$this->menuid->brands.'";s:12:"content_type";s:8:"category";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"21";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:2:"10";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:21:"product_average_score";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:11:"category_id";s:18:"child_display_type";s:9:"allchilds";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";s:15:"enable_carousel";s:1:"0";}';

		$params[7]->params = 'a:39:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"21";s:6:"random";s:1:"0";s:9:"order_dir";s:4:"DESC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:21:"product_average_score";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:11:"category_id";s:18:"child_display_type";s:9:"allchilds";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";s:15:"enable_carousel";s:1:"0";}';

		$params[8]->params = 'a:39:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"3";s:5:"limit";s:2:"21";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"1";s:13:"product_order";s:8:"ordering";s:19:"product_synchronize";s:1:"2";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:11:"category_id";s:18:"child_display_type";s:9:"allchilds";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";s:15:"enable_carousel";s:1:"0";}';

		$query = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`) VALUES ';
		if(version_compare(JVERSION,'3.0', '>=')) {
			foreach($params as $param){
				$param->params = '{"hikashopmodule":'.json_encode(hikashop_unserialize($param->params)).'}';
				$this->db->setQuery('UPDATE `#__modules` SET params = '.$this->db->quote($param->params).' WHERE id = '.(int)$param->id);
				$this->db->execute();
			}
		} else {
			foreach($params as $param){
				$param->id = 'params_'.$param->id;
				$param->params = base64_encode($param->params);
				$query .= '('.$this->db->Quote($param->id).','.$this->db->Quote($param->params).'),';
			}
		}
		$query .='(\'product_show_modules\',\''.$id_related_module.'\')';
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function addMenus($display = true) {
		if(!$this->freshinstall)
			return true;

		$elements = array(new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass());

		$elements[0]->menutype = 'hikashop_default';
		$elements[0]->link = 'index.php?option=com_hikashop&view=category&layout=listing';
		$elements[0]->title = JText::_('COM_HIKASHOP_CATEGORY_VIEW_DEFAULT_TITLE');
		$elements[0]->alias = 'hikashop-menu-for-categories-listing';
		$elements[1]->menutype = 'hikashop_default';
		$elements[1]->link = 'index.php?option=com_hikashop&view=product&layout=listing';
		$elements[1]->title = JText::_('COM_HIKASHOP_PRODUCT_VIEW_DEFAULT_TITLE');
		$elements[1]->alias = 'hikashop-menu-for-products-listing';
		$elements[2]->menutype = 'hikashop_default';
		$elements[2]->link = 'index.php?option=com_hikashop&view=user&layout=cpanel';
		$elements[2]->title = JText::_('COM_HIKASHOP_USER_PANEL_VIEW_DEFAULT_TITLE');
		$elements[2]->alias = 'hikashop-menu-for-user-control-panel';
		$elements[3]->menutype = 'hikashop_default';
		$elements[3]->link = 'index.php?option=com_hikashop&view=user&layout=form';
		$elements[3]->title = JText::_('COM_HIKASHOP_USER_VIEW_DEFAULT_TITLE');
		$elements[3]->alias = 'hikashop-menu-for-hikashop-registration';
		$elements[4]->menutype = 'hikashop_default';
		$elements[4]->link = 'index.php?option=com_hikashop&view=category&layout=listing';
		$elements[4]->title = JText::_('COM_HIKASHOP_BRAND_VIEW_DEFAULT_TITLE');
		$elements[4]->alias = 'hikashop-menu-for-brands-listing';

		foreach($elements as $k => $element){
			$elements[$k]->type = 'component';
			$elements[$k]->published = 1;
			$elements[$k]->path = $elements[$k]->alias;
			$elements[$k]->client_id = 0;
			$elements[$k]->language = '*';
			$elements[$k]->level = 1;
			$elements[$k]->parent_id = 1;
			$elements[$k]->access = 1;
		}

		$this->db->setQuery('SELECT menutype FROM '.hikashop_table('menu_types',false).' WHERE menutype=\'hikashop_default\'');
		$mainMenu = $this->db->loadResult();
		if(empty($mainMenu)){
			$this->db->setQuery('INSERT INTO '.hikashop_table('menu_types',false).' ( `menutype`,`title`,`description` ) VALUES ( \'hikashop_default\',\'HikaShop default menus\',\'This menu is used by HikaShop to store menus configurations\' )');
			$this->db->execute();
		}
		$this->menuid = new stdClass();
		if(version_compare(JVERSION,'3.0','>=')) {
			$productOptions = 'a:35:{s:14:"border_visible";s:1:"2";s:11:"add_to_cart";s:1:"1";s:12:"content_type";s:7:"product";s:11:"layout_type";s:7:"inherit";s:7:"columns";s:1:"3";s:5:"limit";s:2:"21";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:15:"moduleclass_sfx";s:0:"";s:7:"modules";s:0:"";s:19:"content_synchronize";s:1:"1";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:6:"random";s:1:"0";s:19:"product_synchronize";s:1:"1";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"1";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:17:"div_custom_fields";s:0:"";s:6:"height";s:3:"150";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:24:"links_on_main_categories";s:1:"0";s:20:"link_to_product_page";s:1:"1";s:14:"display_badges";s:1:"1";s:15:"enable_carousel";s:1:"0";}';
		} else {
			$config = hikashop_config();
		}
		$menusClass = hikashop_get('class.menus');

		foreach($elements as $element) {
			$this->db->setQuery('SELECT rgt FROM '.hikashop_table('menu',false).' WHERE id=1');
			$root = $this->db->loadResult();
			$element->lft = $root;
			$element->rgt = $root+1;
			$this->db->setQuery('UPDATE '.hikashop_table('menu',false).' SET rgt='.($root+2).' WHERE id=1');
			$this->db->execute();

			$menuId = $menusClass->save($element);
			if(empty($menuId))
				continue;

			$menuParams = null;

			if($element->alias == 'hikashop-menu-for-brands-listing') {
				$this->menuid->brands = $menuId;
				$categoryOptions = 'a:38:{s:10:"show_image";s:1:"0";s:16:"show_description";s:1:"1";s:11:"layout_type";s:7:"inherit";s:7:"columns";s:1:"3";s:5:"limit";s:2:"21";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:2:"10";s:7:"modules";s:0:"";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:1:"1";s:20:"link_to_product_page";s:1:"1";s:17:"show_vote_product";s:1:"0";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";s:12:"content_type";s:12:"manufacturer";s:15:"enable_carousel";s:1:"0";}';
				if(version_compare(JVERSION, '3.0', '>=')) {
					$menuParams = '{"hk_category":'.json_encode(hikashop_unserialize($categoryOptions));
					$menuParams .= ',"hk_product":'.json_encode(hikashop_unserialize($productOptions)).'}';
				} else {
					$moduleOtpions = base64_encode($categoryOptions);
					$query = "UPDATE `#__hikashop_config` SET `config_value`=".$this->db->quote($moduleOtpions)." WHERE `config_namekey`= 'menu_".$menuId."' ";
					$this->db->setQuery($query);
					$this->db->execute();
					$config->set('menu_'.$menuId,$moduleOtpions);
					$menusClass->attachAssocModule($menuId, $display);
				}
			} elseif($element->alias == 'hikashop-menu-for-categories-listing') {
				$this->menuid->categories = $menuId;
				$categoryOptions = 'a:32:{s:12:"content_type";s:7:"product";s:11:"layout_type";s:7:"inherit";s:7:"columns";i:3;s:5:"limit";s:2:"21";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:15:"moduleclass_sfx";s:0:"";s:7:"modules";s:0:"";s:19:"content_synchronize";s:1:"1";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:6:"random";i:0;s:19:"product_synchronize";s:1:"1";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"1";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:17:"div_custom_fields";s:0:"";s:6:"height";s:3:"150";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:24:"links_on_main_categories";s:1:"0";s:20:"link_to_product_page";s:1:"1";s:15:"enable_carousel";s:1:"0";}';
				if(version_compare(JVERSION, '3.0', '>=')) {
					$menuParams = '{"hk_category":'.json_encode(hikashop_unserialize($categoryOptions));
					$menuParams .= ',"hk_product":'.json_encode(hikashop_unserialize($productOptions)).'}';
				} else {
					$moduleOtpions = base64_encode($categoryOptions);
					$query = "UPDATE `#__hikashop_config` SET `config_value`=".$this->db->quote($moduleOtpions)." WHERE `config_namekey`= 'menu_".$menuId."' ";
					$this->db->setQuery($query);
					$this->db->execute();
					$config->set('menu_'.$menuId,$moduleOtpions);
					$menusClass->attachAssocModule($menuId, $display);
				}
			} elseif($element->alias == 'hikashop-menu-for-products-listing') {
				$productOptions = 'a:32:{s:12:"content_type";s:7:"product";s:11:"layout_type";s:7:"inherit";s:7:"columns";i:3;s:5:"limit";s:2:"21";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:15:"moduleclass_sfx";s:0:"";s:7:"modules";s:0:"";s:19:"content_synchronize";s:1:"1";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:6:"random";i:0;s:19:"product_synchronize";s:1:"1";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"1";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:17:"div_custom_fields";s:0:"";s:6:"height";s:3:"150";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:24:"links_on_main_categories";s:1:"0";s:20:"link_to_product_page";s:1:"1";s:15:"enable_carousel";s:1:"0";}';
				if(version_compare(JVERSION, '3.0', '>=')) {
					$menuParams = '{"hk_product":'.json_encode(hikashop_unserialize($productOptions)).'}';
				} else {
					$moduleOtpions = base64_encode($productOptions);
					$query = "UPDATE `#__hikashop_config` SET `config_value`=".$this->db->quote($moduleOtpions)." WHERE `config_namekey`= 'menu_".$menuId."' ";
					$this->db->setQuery($query);
					$this->db->execute();
					$config->set('menu_'.$menuId,$moduleOtpions);
				}
			}

			if(!empty($menuParams)) {
				$this->db->setQuery('UPDATE '.hikashop_table('menu',false).' SET params='.$this->db->quote($menuParams).' WHERE id='.(int)$menuId);
				$this->db->execute();
			}
		}
	}
}

class com_hikashopInstallerScript {
	public function install($parent) {
		com_hikashop_install();
	}

	public function update($parent) {
		com_hikashop_install();
	}

	public function uninstall($parent)	{
		$db = JFactory::getDBO();
		$db->setQuery("DELETE FROM `#__hikashop_config` WHERE `config_namekey` = 'li' LIMIT 1");
		$db->execute();

		$db->setQuery("DELETE FROM `#__menu` WHERE link LIKE '%com_hikashop%'");
		$db->execute();

		$db->setQuery("UPDATE `#__modules` SET `published` = 0 WHERE `module` LIKE '%hikashop%'");
		$db->execute();

		$db->setQuery("UPDATE `#__extensions` SET `enabled` = 0 WHERE `type` = 'plugin' AND `element` LIKE '%hikashop%' AND `folder` NOT LIKE '%hikashop%'");
		$db->execute();
	}

	public function preflight($type, $parent) {
		return true;
	}
	public function postflight($type, $parent) {
		return true;
	}
}
