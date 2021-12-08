<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketUpdateHelper {
	private $db;

	public function __construct() {
		$this->db = JFactory::getDBO();
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$this->update = hikaInput::get()->getBool('update');
	}

	public function addDefaultModules() {
	}

	public function createUploadFolders() {
		$file = hikamarket::get('shop.class.file');
		$path = $file->getPath('file');
		if(!JFile::exists($path.'.htaccess')) {
			$text = 'deny from all';
			JFile::write($path.'.htaccess', $text);
		}
		$path = $file->getPath('image');
	}

	public function installExtensions() {
		$path = HIKAMARKET_BACK.'extensions';
		if(!is_dir($path))
			return;
		$dirs = JFolder::folders($path);

		$query = 'SELECT CONCAT(`folder`,`element`) FROM `#__extensions` WHERE `folder` IN '.
				"( 'hikashop','hikamarket','hikashoppayment' ) ".
				"OR `element` LIKE '%hikamarket%' OR `element` LIKE '%_market_%' OR `element` LIKE '%hikashop%' ";
		$this->db->setQuery($query);
		$existingExtensions = $this->db->loadColumn();

		$success = array();
		$plugins = array();
		$modules = array();

		$exts = array(
			'plg_hikashop_market' => array('HikaMarket - HikaShop Integration plugin', 0, 1),
			'plg_system_hikamarketoverrides' => array('HikaMarket - HikaShop overrides plugin', 0, 1),
			'plg_hikashoppayment_mangopay' => array('Hikashop (market) MangoPay Adaptive Payment Plugin', 0, 0),
			'plg_hikashoppayment_paypaladaptive' => array('Hikashop (market) Paypal Adaptive Payment Plugin', 0, 1),
			'mod_market_locationsearch' => array('HikaMarket Location Search module', 0, 0),
			'plg_hikamarket_duplicateproducts' => array('HikaMarket - Duplicate products', 0, 0),
			'plg_hikamarket_mangopay' => array('HikaMarket MangoPay integration', 0, 0),
			'plg_hikamarket_vendorlocationfilter' => array('HikaMarket Vendor User Location Filter', 0, 0),
			'plg_hikamarket_vendorusergroup' => array('HikaMarket vendor user group', 0, 0),
			'plg_hikashop_market_vendorselectfield' => array('HikaShop: Vendor Selection Custom Field', 0, 1),
			'plg_hikashop_marketmodule_vendorrelated' => array('HikaShop - Vendor same products (listing module)', 0, 0),
			'plg_hikashop_productfiltervendor' => array('HikaShop - Product Filter for vendors', 0, 0),
			'plg_hikashop_productforcevendorcategory' => array('HikaShop - product force vendor category', 0, 0),
			'plg_hikashop_userpointstovendor' => array('Hikashop - UserPoints to Vendor', 0, 0),
			'plg_hikashop_vendorgroupafterpurchase' => array('HikaShop - Vendor group after purchase', 0, 1),
			'plg_hikashop_vendorlocationfilter' => array('HikaShop Vendor User Location Filter', 1, 0),
			'plg_hikashop_vendorpoints' => array('HikaShop - Vendor points', 0, 1),
			'plg_search_hikamarket_vendors' => array('Search - HikaMarket Vendors', 0, 1),
			'plg_user_hikamarket_vendorgroup' => array('HikaMarket Vendor groups', 0, 1),
		);

		$listTables = $this->db->getTableList();
		$this->errors = array();
		foreach($dirs as $dir) {
			$arguments = explode('_', $dir, 3);
			$report = true;
			if(!empty($exts[$dir][3])) {
				$report = false;
			}
			$prefix = array_shift($arguments);

			if($prefix != 'plg' && $prefix != 'mod') {
				hikamarket::display('Could not handle : '.$dir, 'error');
				continue;
			}

			$newExt = new stdClass();
			$newExt->enabled = 1;
			$newExt->params = '{}';
			$newExt->name = isset($exts[$dir][0])?$exts[$dir][0]:$dir;
			$newExt->ordering = isset($exts[$dir][1])?$exts[$dir][1]:0;

			if(!isset($exts[$dir]) && function_exists('simplexml_load_file')) {
				if($prefix == 'plg')
					$xmlFile = $path.DS.$dir.DS.$arguments[1].'.xml';
				else
					$xmlFile = $path.DS.$dir.DS.$dir.'.xml';
				$xml = simplexml_load_file($xmlFile);
				if (!empty($xml) && ($xml->getName() == 'install' || $xml->getName() == 'extension')) {
					$newExt->name = (string)$xml->name;
					if(isset($xml->hikainstall)) {
						$attribs = $xml->hikainstall->attributes();
						$newExt->ordering = (int)$attribs->ordering;
						$newExt->enabled = (int)$attribs->enable;
						$report = (int)$attribs->report;
					}
				}
				unset($xml);
			}

			if($prefix == 'plg') {

				$newExt->type = 'plugin';
				$newExt->folder = array_shift($arguments);
				$newExt->element = implode('_', $arguments);

				if(isset($exts[$dir][2]) && is_numeric($exts[$dir][2])) {
					$newExt->enabled = (int)$exts[$dir][2];
				}

				if(!hikamarket::createDir(HIKAMARKET_ROOT.'plugins'.DS.$newExt->folder, $report))
					continue;

				$destinationFolder = HIKAMARKET_ROOT.'plugins'.DS.$newExt->folder.DS.$newExt->element;
				if(!hikamarket::createDir($destinationFolder))
					continue;

				if(!$this->copyFolder($path.DS.$dir, $destinationFolder))
					continue;

				if(in_array($newExt->folder.$newExt->element, $existingExtensions))
					continue;

				$plugins[] = $newExt;

			} else {

				$newExt->type = 'module';
				$newExt->folder = '';
				$newExt->element = $dir;

				$destinationFolder = HIKAMARKET_ROOT.'modules'.DS.$dir;

				if(!hikamarket::createDir($destinationFolder))
					continue;

				if(!$this->copyFolder($path.DS.$dir, $destinationFolder))
					continue;

				if(in_array($newExt->element, $existingExtensions))
					continue;

				$modules[] = $newExt;
			}
		}

		if(!empty($this->errors))
			hikamarket::display($this->errors, 'error');

		if( empty($plugins) && empty($modules) ) {
			return;
		}

		$extensions = array_merge($plugins, $modules);

		$success = array();
		if(!empty($extensions)) {
			$query = 'INSERT INTO `#__extensions` (`name`,`element`,`folder`,`enabled`,`ordering`,`type`,`access`) VALUES ';

			$sep = '';
			foreach($extensions as $oneExt) {
				$query .= $sep.'('.$this->db->Quote($oneExt->name).','.$this->db->Quote($oneExt->element).','.$this->db->Quote($oneExt->folder).','.$oneExt->enabled.','.$oneExt->ordering.','.$this->db->Quote($oneExt->type).',1)';
				if($oneExt->type!='module') {
					$success[] = JText::sprintf('PLUG_INSTALLED', $oneExt->name);
				}
				$sep = ',';
			}

			$this->db->setQuery($query);
			$this->db->execute();
		}

		if(!empty($modules)) {
			foreach($modules as $oneModule) {
				$query = 'INSERT INTO `#__modules` (`title`,`position`,`published`,`module`,`access`,`language`) VALUES '.
					'('.$this->db->Quote($oneModule->name).",'position-7',0,".$this->db->Quote($oneModule->element).",1,'*')";
				$this->db->setQuery($query);
				$this->db->execute();
				$moduleId = $this->db->insertid();

				$this->db->setQuery('INSERT IGNORE INTO `#__modules_menu` (`moduleid`,`menuid`) VALUES ('.$moduleId.',0)');
				$this->db->execute();

				$success[] = JText::sprintf('MODULE_INSTALLED', $oneModule->name);
			}
		}

		if(!empty($success)) {
			hikamarket::display($success, 'success');
		}
	}

	public function copyFolder($from, $to) {
		$ret = true;

		$allFiles = JFolder::files($from);
		foreach($allFiles as $oneFile) {
			if(file_exists($to.DS.'index.html') && $oneFile == 'index.html')
				continue;
			if(JFile::copy($from.DS.$oneFile,$to.DS.$oneFile) !== true) {
				$this->errors[] = 'Could not copy the file from '.$from.DS.$oneFile.' to '.$to.DS.$oneFile;
				$ret = false;
			}
		}
		$allFolders = JFolder::folders($from);
		if(!empty($allFolders)) {
			foreach($allFolders as $oneFolder) {
				if(!hikamarket::createDir($to.DS.$oneFolder))
					continue;
				if(!$this->copyFolder($from.DS.$oneFolder,$to.DS.$oneFolder))
					$ret = false;
			}
		}
		return $ret;
	}

	public function installMenu($code = '') {
		if(empty($code)) {
			$lang = JFactory::getLanguage();
			$code = $lang->getTag();
		}
		$path = hikashop_getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.'.HIKAMARKET_COMPONENT.'.ini';
		if(!file_exists($path))
			return;
		$content = file_get_contents($path);
		if(empty($content))
			return;

		$menuFileContent = strtoupper(HIKAMARKET_COMPONENT).'="'.HIKAMARKET_NAME.'"'."\r\n".strtoupper(HIKAMARKET_NAME).'="'.HIKAMARKET_NAME.'"'."\r\n";
		$menuStrings = array('CONFIG','VENDORS','HELP','UPDATE_ABOUT');
		foreach($menuStrings as $s) {
			preg_match('#(\n|\r)(HIKA_)?'.$s.'="(.*)"#i',$content,$matches);
			if(empty($matches[3]))
				continue;
			$menuFileContent .= $s.'="'.$matches[3].'"'."\r\n";
		}

		preg_match_all('#(\n|\r)(COM_HIKAMARKET_.*)="(.*)"#iU', $content, $matches);
		if(!empty($matches))
			$menuFileContent .= implode('', $matches[0]);
		$menuFileContent .= "\r\n" . strtoupper(HIKAMARKET_COMPONENT) . '_CONFIGURATION="'.HIKAMARKET_NAME.'"';

		$menuPath = HIKAMARKET_ROOT.'administrator'.DS.'language'.DS.$code.DS.$code.'.'.HIKAMARKET_COMPONENT.'.sys.ini';
		if(!JFile::write($menuPath, $menuFileContent)) {
			hikamarket::display(JText::sprintf('FAIL_SAVE',$menuPath),'error');
		}
	}

	private function installOne($folder) {
		if(empty($folder))
			return false;
		unset($GLOBALS['_JREQUEST']['installtype']);
		unset($GLOBALS['_JREQUEST']['install_directory']);
		hikaInput::get()->set('installtype', 'folder');
		hikaInput::get()->set('install_directory', $folder);
		$_REQUEST['installtype'] = 'folder';
		$_REQUEST['install_directory'] = $folder;
		$controller = new hikashopBridgeController(array(
			'base_path'=> HIKAMARKET_ROOT.'administrator'.DS.'components'.DS.'com_installer',
			'name' => 'Installer',
			'default_task' => 'installform'
		));
		$model = $controller->getModel('Install');
		return $model->install();
	}

	public function getUrl() {
		$urls = parse_url(HIKAMARKET_LIVE);
		$lurl = preg_replace('#^www2?\.#Ui', '', $urls['host'], 1);
		if(!empty($urls['path']))
			$lurl .= $urls['path'];
		return strtolower(rtrim($lurl, '/'));
	}

	public function addJoomfishElements() {
		$dstFolder = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_joomfish'.DS.'contentelements'.DS;
		if(JFolder::exists($dstFolder)) {
			$srcFolder = HIKAMARKET_BACK.'translations'.DS;
			$files = JFolder::files($srcFolder);
			if(!empty($files)) {
				foreach($files as $file) {
					JFile::copy($srcFolder.$file,$dstFolder.$file);
				}
			}
		}
		return true;
	}

	public function addUpdateSite() {
		$config = hikamarket::config();
		$newconfig = new stdClass();
		$newconfig->website = HIKASHOP_LIVE;
		$config->save($newconfig);

		$query = 'SELECT update_site_id FROM #__update_sites WHERE location LIKE \'%hikamarket%\' AND type = \'extension\'';
		$this->db->setQuery($query);
		$update_site_id = $this->db->loadResult();

		$object = new stdClass();
		$object->name = 'Hikamarket';
		$object->type = 'extension';
		$object->enabled = 1;
		$object->location = 'http://www.hikashop.com/component/updateme/updatexml/component-hikamarket/version-'.$config->get('version').'/level-'.$config->get('level').'/li-'.urlencode(base64_encode(HIKASHOP_LIVE)).'/file-extension.xml';

		if(empty($update_site_id)){
			$this->db->insertObject('#__update_sites', $object);
			$update_site_id = $this->db->insertid();
		} else {
			$object->update_site_id = $update_site_id;
			$this->db->updateObject('#__update_sites', $object, 'update_site_id');
		}

		$query = 'SELECT extension_id FROM #__extensions WHERE `name` = \'hikamarket\' AND type = \'component\'';
		$this->db->setQuery($query);
		$extension_id = $this->db->loadResult();
		if(empty($update_site_id) || empty($extension_id))
			return false;

		$query = 'INSERT IGNORE INTO #__update_sites_extensions (update_site_id, extension_id) values ('.$update_site_id.','.$extension_id.')';
		$this->db->setQuery($query);
		$this->db->execute();

		return true;
	}

	public function addDefaultData() {
		$query = 'SELECT * FROM `#__menu` WHERE `title` IN (\'com_hikamarket\',\'hikamarket\',\'HikaMarket\') AND `client_id`=1 AND `parent_id`=1 AND menutype IN (\'main\',\'mainmenu\',\'menu\')';
		$this->db->setQuery($query);
		$parentData = $this->db->loadObject();
		$parent = $parentData->id;

		$query = 'SELECT id FROM `#__menu` WHERE `parent_id`='.$parent;
		$this->db->setQuery($query);
		$submenu = $this->db->loadColumn();
		$old = count($submenu);

		$query = 'DELETE FROM `#__menu` WHERE `parent_id`='.$parent;
		$this->db->setQuery($query);
		$this->db->execute();

		$query = 'UPDATE `#__menu` SET `rgt`=`rgt`-'.($old*2).' WHERE `rgt`>='.$parentData->rgt;
		$this->db->setQuery($query);
		$this->db->execute();

		$elems = array(
			array('config', 'icon-16-config.png', 'Configuration', 'Configuration'),
			array('vendor', 'icon-16-user.png', 'Vendors', 'Vendors'),
			array('plugins', 'icon-16-plugin.png', 'Plugins', 'Plugins'),
			array('documentation', 'icon-16-help.png', 'Help', 'Help'),
			array('update', 'icon-16-help-jrd.png', 'Update / About', 'Update_About'),
		);

		$nbItems = count($elems) * 2;

		$query = 'UPDATE `#__menu` SET `rgt`=`rgt` + '.$nbItems.' WHERE `rgt`>='.$parentData->rgt;
		$this->db->setQuery($query);
		$this->db->execute();

		$query = 'UPDATE `#__menu` SET `lft`=`lft` + '.$nbItems.' WHERE `lft`>'.$parentData->lft;
		$this->db->setQuery($query);
		$this->db->execute();

		$left = $parentData->lft;
		$cid = $parentData->component_id;

		$query  = 'INSERT IGNORE INTO `#__menu` (`type`,`link`,`menutype`,`img`,`alias`,`title`,`client_id`,`parent_id`,`level`,`language`,`lft`,`rgt`,`component_id`) VALUES ';
		$l = $left;
		foreach($elems as $k => $elem) {
			if($k > 0)
				$query .= ',';
			$query .= "('component','index.php?option=com_hikamarket&ctrl=".$elem[0]."','".$parentData->menutype."','./templates/bluestork/images/menu/".$elem[1]."','".$elem[2]."','".$elem[3]."',1,".$parent.",2,'*',".($l+1).",".($l+2).",".$cid.")";
			$l += 2;
		}
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function onBeforecheckDB(&$createTable, &$custom_fields, &$structure, &$helper) {
		$structs = array(
			'user' => array(
				'user_vendor_id' => 'user_vendor_id INT(10) NOT NULL DEFAULT 0',
				'user_vendor_access' => 'user_vendor_access text NOT NULL DEFAULT \'\''
			),
			'product' => array(
				'product_status' => 'product_status VARCHAR(255) NOT NULL DEFAULT \'\'',
				'product_vendor_params' => 'product_vendor_params TEXT NOT NULL DEFAULT \'\''
			),
			'order' => array(
				'order_parent_id' => 'order_parent_id INT(10) NOT NULL DEFAULT 0',
				'order_vendor_id' => 'order_vendor_id INT(10) NOT NULL DEFAULT 0',
				'order_vendor_price' => 'order_vendor_price decimal(12,5) NOT NULL DEFAULT \'0.00000\'',
				'order_vendor_paid' => 'order_vendor_paid INT(10) NOT NULL DEFAULT 0',
				'order_vendor_params' => 'order_vendor_params TEXT NOT NULL DEFAULT \'\''
			),
			'order_product' => array(
				'order_product_parent_id' => 'order_product_parent_id INT(10) NOT NULL DEFAULT 0',
				'order_product_vendor_price' => 'order_product_vendor_price decimal(12,5) NOT NULL DEFAULT \'0.00000\'',
			),
			'discount' => array(
				'discount_target_vendor' => 'discount_target_vendor INT(10) NOT NULL DEFAULT 0'
			),
			'characteristic' => array(
				'characteristic_vendor_id' => 'characteristic_vendor_id INT(10) NOT NULL DEFAULT 0'
			),
			'shipping' => array(
				'shipping_vendor_id' => 'shipping_vendor_id INT(10) NOT NULL DEFAULT 0'
			),
			'payment' => array(
				'payment_vendor_id' => 'payment_vendor_id INT(10) NOT NULL DEFAULT 0'
			),
			'plugin' => array(
				'plugin_vendor_id' => 'plugin_vendor_id INT(10) NOT NULL DEFAULT 0'
			),
		);
		foreach($structs as $k => $v) {
			if(!isset($structure['#__hikashop_'.$k]))
				continue;
			$structure['#__hikashop_'.$k] = array_merge($structure['#__hikashop_'.$k], $v);
		}
	}

	public function onAfterCheckDB(&$ret) {
		$query = 'INSERT IGNORE INTO `'.hikamarket::table('customer_vendor').'` (customer_id, vendor_id) '.
			' SELECT DISTINCT order_user_id, order_vendor_id FROM `'.hikamarket::table('shop.order').'` AS o '.
			' WHERE o.order_type = ' . $this->db->Quote('subsale') . ' AND o.order_vendor_id > 1';
		try {
			$this->db->setQuery($query);
			$result = $this->db->execute();
			if($result){
				$ret[] = array(
					'success',
					'Customer vendor synchronized'
				);
			}
		} catch(Exception $e) {
			$ret[] = array(
				'error',
				$e->getMessage()
			);
		}
	}

	public function processMigration_Transaction() {
		$app = JFactory::getApplication();
		$config = hikamarket::config();

		$valid_order_statuses = explode(',', $config->get('valid_order_statuses', 'confirmed,shipped'));
		foreach($valid_order_statuses as &$valid_order_status) {
			$valid_order_status = $this->db->Quote($valid_order_status);
		}
		unset($valid_order_status);

		$query = 'INSERT IGNORE INTO `#__hikamarket_order_transaction` '.
			' (order_id, vendor_id, order_transaction_created, order_transaction_status, order_transaction_price, order_transaction_currency_id, order_transaction_paid, order_transaction_valid)'.
			' SELECT o.order_parent_id, o.order_vendor_id, o.order_created, o.order_status, o.order_vendor_price, o.order_currency_id, o.order_vendor_paid, 0 '.
			' FROM `#__hikashop_order` AS o WHERE o.order_type = \'subsale\'';
		$this->db->setQuery($query);
		try { $this->db->execute(); }
		catch(Exception $e) { hikashop_writeToLog('Error during migration: Transactions 01', 'HikaMarket'); }

		$query = 'UPDATE `#__hikamarket_order_transaction` SET order_transaction_valid = 1 WHERE order_transaction_status IN ('.implode(',', $valid_order_statuses).')';
		$this->db->setQuery($query);
		try { $this->db->execute(); }
		catch(Exception $e) { hikashop_writeToLog('Error during migration: Transactions 02', 'HikaMarket'); }

		$query = 'INSERT IGNORE INTO `#__hikamarket_order_transaction` '.
			' (order_id, vendor_id, order_transaction_created, order_transaction_status, order_transaction_price, order_transaction_currency_id, order_transaction_paid, order_transaction_valid)'.
			' SELECT vr.order_parent_id, vr.order_vendor_id, o.order_created, vr.order_status, vr.order_vendor_price, o.order_currency_id, vr.order_vendor_paid, 2 '.
			' FROM `#__hikashop_order` AS vr INNER JOIN `#__hikashop_order` AS o ON o.order_id = vr.order_parent_id '.
			' WHERE vr.order_type = \'vendorrefund\' AND vr.order_vendor_price != 0.00';
		$this->db->setQuery($query);
		try { $this->db->execute(); }
		catch(Exception $e) { hikashop_writeToLog('Error during migration: Transactions 03', 'HikaMarket'); }

		$query = 'UPDATE `#__hikashop_order` SET order_type = \'vendorrefund_legacy\' WHERE order_type = \'vendorrefund\'';
		$this->db->setQuery($query);
		try { $this->db->execute(); }
		catch(Exception $e) { hikashop_writeToLog('Error during migration: Transactions 04', 'HikaMarket'); }

		$query = 'UPDATE `#__hikashop_order` AS o INNER JOIN `#__hikashop_order` AS op ON o.order_parent_id = op.order_id '.
			' SET o.order_status = op.order_status WHERE o.order_type = \'subsale\' AND o.order_vendor_paid > 0';
		$this->db->setQuery($query);
		try { $this->db->execute(); }
		catch(Exception $e) { hikashop_writeToLog('Error during migration: Transactions 05', 'HikaMarket'); }

		$query = 'UPDATE `#__hikashop_order` AS o INNER JOIN ( '.
			'  SELECT order_parent_id, order_vendor_id, order_vendor_price as total '.
			'  FROM `#__hikashop_order` WHERE order_type = \'vendorrefund_legacy\' AND order_parent_id > 0 '.
			' ) AS sub ON o.order_parent_id = sub.order_parent_id AND o.order_vendor_id = sub.order_vendor_id '.
			' SET o.order_vendor_price = o.order_vendor_price + sub.total '.
			' WHERE o.order_type = \'subsale\' AND o.order_status IN ('.implode(',', $valid_order_statuses).') AND o.order_vendor_paid > 0 AND sub.total != 0.00 ';
		$this->db->setQuery($query);
		try { $this->db->execute(); }
		catch(Exception $e) { hikashop_writeToLog('Error during migration: Transactions 06', 'HikaMarket'); }

		$query = 'UPDATE `#__hikashop_order` AS o INNER JOIN ( '.
			'  SELECT order_parent_id, order_vendor_id, order_vendor_price as total '.
			'  FROM `#__hikashop_order` WHERE order_type = \'vendorrefund_legacy\' AND order_parent_id > 0 '.
			' ) AS sub ON o.order_parent_id = sub.order_parent_id AND o.order_vendor_id = sub.order_vendor_id '.
			' SET o.order_vendor_price = -sub.total '.
			' WHERE o.order_type = \'subsale\' AND o.order_status NOT IN ('.implode(',', $valid_order_statuses).') AND o.order_vendor_paid > 0 AND sub.total != 0.00 ';
		$this->db->setQuery($query);
		try { $this->db->execute(); }
		catch(Exception $e) { hikashop_writeToLog('Error during migration: Transactions 07', 'HikaMarket');}
	}
}
