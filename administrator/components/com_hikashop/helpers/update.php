<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopUpdateHelper{
	var $db;
	function __construct(){
		$this->db = JFactory::getDBO();
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$this->update = hikaInput::get()->getBool('update');
		$this->freshinstall = hikaInput::get()->getBool('freshinstall');
	}
	function addDefaultModules(){
	}

	function createUploadFolders(){
		$fileClass = hikashop_get('class.file');
		$path = $fileClass->getPath('file');
		if(!JFile::exists($path.'.htaccess')){
			$text = 'deny from all';
			JFile::write($path.'.htaccess',$text);
		}
		$path = $fileClass->getPath('image');
	}


	function installExtensions($install = false) {
		$path = HIKASHOP_BACK.'extensions';
		jimport('joomla.filesystem.folder');
		if($install != true)
			$dirs = JFolder::folders($path);

		$this->db->setQuery("SELECT CONCAT(`folder`,`element`) FROM #__extensions WHERE `folder` IN ('hikashop','hikashoppayment','hikashopshipping') OR `element` LIKE '%hikashop%' OR (`folder`='system' AND `element` IN ('vm_redirect','reds_redirect','mijo_redirect','custom_price','custom_quantity_tax','nossloutsidecheckout'))");
		$existingExtensions = $this->db->loadColumn();

		if(empty($existingExtensions))
			$existingExtensions = array();

		$success = array();
		$plugins = array();
		$modules = array();
		$extensioninfo = array(
			'mod_hikashop' => array('HikaShop Content Module')
			,'mod_hikashop_cart' => array('HikaShop Cart Module')
			,'mod_hikashop_currency' => array('HikaShop Currency Switcher Module')
			,'mod_hikashop_dashboard' => array('HikaShop Dashboard',0,0,array('admin'))
			,'mod_hikashop_filter' => array('HikaShop Filter Module',0,0)
			,'mod_hikashop_wishlist' => array('HikaShop Wishlist Module',0,0)
			,'plg_acymailing_hikashop' => array('AcyMailing : HikaShop integration',0,1,1)
			,'plg_authentication_opencart' => array('Hikashop OpenCart authentication Plugin',0,0)
			,'plg_editors-xtd_hikashopproduct' => array('Hikashop product tag insertion plugin',0,1)
			,'plg_finder_hikashop' => array('Smart Search - HikaShop Products',0,1)
			,'plg_hikashop_acymailing' => array('HikaShop trigger for AcyMailing filters',0,1)
			,'plg_hikashop_cartnotify' => array('HikaShop Cart notification Plugin',0,1)
			,'plg_hikashop_content_markdown' => array('HikaShop Content Markdown Plugin',0,1)
			,'plg_hikashop_datafeed' => array('Hikashop Products Cron Update Plugin',0,0)
			,'plg_hikashop_datepickerfield' => array('Hikashop Date Picker Plugin',0,1)
			,'plg_hikashop_email_history' => array('Hikashop Email History Plugin',0,1)
			,'plg_hikashop_google_products' => array('Hikashop Google Products Plugin',0,1)
			,'plg_hikashop_group' => array('HikaShop group plugin',0,1)
			,'plg_hikashop_history' => array('HikaShop order history plugin',0,1)
			,'plg_hikashop_kashflow' => array('HikaShop KashFlow plugin',0,0)
			,'plg_hikashop_massaction_address' => array('Hikashop Massaction Address Plugin',0,1)
			,'plg_hikashop_massaction_category' => array('Hikashop Massaction Category Plugin',0,1)
			,'plg_hikashop_massaction_order' => array('Hikashop Massaction Order Plugin',0,1)
			,'plg_hikashop_massaction_product' => array('Hikashop Massaction Product Plugin',0,1)
			,'plg_hikashop_massaction_user' => array('Hikashop Massaction User Plugin',0,1)
			,'plg_hikashop_order_auto_cancel' => array('Hikashop Orders Automatic Cancel Plugin',0,0)
			,'plg_hikashop_out_of_stock' => array('HikaShop Out of stock notification Plugin',0,0)
			,'plg_hikashop_rates' => array('HikaShop rates auto update plugin',0,1)
			,'plg_hikashop_shippingmanual_prices' => array('HikaShop Shipping manual - Prices per product plugin',0,1)
			,'plg_hikashop_shopclosehours' => array('HikaShop Shop Close Hours plugin',0,1)
			,'plg_hikashop_taxcloud' => array('Hikashop TaxCloud Plugin',0,0)
			,'plg_hikashop_user_account' => array('HikaShop joomla user account link plugin',0,1)
			,'plg_hikashop_userpoints' => array('HikaShop User Points plugin',0,1)
			,'plg_hikashop_validate_free_order' => array('HikaShop Free orders validation Plugin',0,1)
			,'plg_hikashop_waitlist_notify' => array('HikaShop Product Wait List Notification plugin',0,0)
			,'plg_hikashoppayment_adyen' => array('HikaShop Adyen payment plugin',0,0)
			,'plg_hikashoppayment_alertpay' => array('HikaShop Payza payment plugin',0,0)
			,'plg_hikashoppayment_alipay' => array('HikaShop AliPay payment plugin',0,0)
			,'plg_hikashoppayment_alphauserpoints' => array('Hikashop AlphaUserPoints Plugin',0,0)
			,'plg_hikashoppayment_amazon' => array('HikaShop Amazon payment plugin',0,0)
			,'plg_hikashoppayment_atos' => array('HikaShop Worldline SIPS payment plugin',0,0)
			,'plg_hikashoppayment_atossips' => array('HikaShop Worldline SIPS V2 payment plugin',0,0)
			,'plg_hikashoppayment_authorize' => array('HikaShop Authorize.net payment plugin',0,0)
			,'plg_hikashoppayment_banktransfer' => array('HikaShop bank transfer payment plugin',0,0)
			,'plg_hikashoppayment_be2bill' => array('HikaShop Be2Bill payment plugin',0,0)
			,'plg_hikashoppayment_beanstream' => array('HikaShop Beanstream payment plugin',0,0)
			,'plg_hikashoppayment_bf_rbsbusinessgateway' => array('HikaShop WorldPay Business Gateway payment plugin',0,0)
			,'plg_hikashoppayment_bf_rbsglobalgateway' => array('HikaShop WorldPay Global Gateway payment plugin',0,0)
			,'plg_hikashoppayment_bluepaid' => array('HikaShop Bluepaid payment plugin',0,0)
			,'plg_hikashoppayment_borgun' => array('HikaShop Borgun payment plugin',0,0)
			,'plg_hikashoppayment_cardsave' => array('HikaShop CardSave payment plugin',0,0)
			,'plg_hikashoppayment_check' => array('HikaShop check payment plugin',0,0)
			,'plg_hikashoppayment_cmcic' => array('HikaShop Monetico payment plugin',0,0)
			,'plg_hikashoppayment_collectondelivery' => array('HikaShop collect on delivery payment plugin',0,0)
			,'plg_hikashoppayment_common' => array('HikaShop common payment API plugin',0,1)
			,'plg_hikashoppayment_creditcard' => array('HikaShop credit card payment plugin',0,0)
			,'plg_hikashoppayment_epay' => array('HikaShop ePay payment plugin',0,0)
			,'plg_hikashoppayment_eselect' => array('HikaShop eSelect/Moneris payment plugin',0,0)
			,'plg_hikashoppayment_eway' => array('HikaShop eWAY payment plugin',0,0)
			,'plg_hikashoppayment_firstdata' => array('HikaShop First Data payment plugin',0,0)
			,'plg_hikashoppayment_googlecheckout' => array('HikaShop Google Checkout payment plugin',0,0)
			,'plg_hikashoppayment_googlewallet' => array('HikaShop Google Wallet payment plugin',0,0)
			,'plg_hikashoppayment_hsbc' => array('HikaShop HSBC payment plugin',0,0)
			,'plg_hikashoppayment_innovativegateway' => array('HikaShop Innovative Gateway payment plugin',0,0)
			,'plg_hikashoppayment_ipaydna' => array('HikaShop iPayDNA payment plugin',0,0)
			,'plg_hikashoppayment_iveri' => array('HikaShop iVeri payment plugin',0,0)
			,'plg_hikashoppayment_migsvpc' => array('HikaShop MIGS VPC payment plugin',0,0)
			,'plg_hikashoppayment_monetico' => array('HikaShop Monetico payment plugin',0,0)
			,'plg_hikashoppayment_moneybookers' => array('HikaShop Moneybookers payment plugin',0,0)
			,'plg_hikashoppayment_nets' => array('HikaShop NETS payment plugin',0,0)
			,'plg_hikashoppayment_ogone' => array('HikaShop Ogone payment plugin',0,0)
			,'plg_hikashoppayment_paybox' => array('HikaShop PayBox payment plugin',0,0)
			,'plg_hikashoppayment_payfast' => array('HikaShop PayFast payment plugin',0,0)
			,'plg_hikashoppayment_paygate' => array('HikaShop PayGate payment plugin',0,0)
			,'plg_hikashoppayment_payjunction' => array('HikaShop PayJunction payment plugin',0,0)
			,'plg_hikashoppayment_paymentexpress' => array('HikaShop Payment Express PxPost payment plugin',0,0)
			,'plg_hikashoppayment_paypal' => array('HikaShop Paypal (legacy) payment plugin',0,0)
			,'plg_hikashoppayment_paypaladvanced' => array('HikaShop Paypal Advanced payment plugin',0,0)
			,'plg_hikashoppayment_paypalcheckout' => array('HikaShop Paypal Checkout payment plugin',0,0)
			,'plg_hikashoppayment_paypalexpress' => array('HikaShop Paypal Express Checkout payment plugin',0,0)
			,'plg_hikashoppayment_paypalintegralevolution' => array('Hikashop Paypal Website Payments Pro Hosted Payment plugin',0,0)
			,'plg_hikashoppayment_paypalpro' => array('HikaShop PayPal Pro payment plugin',0,0)
			,'plg_hikashoppayment_payplug' => array('HikaShop PayPlug payment plugin',0,0)
			,'plg_hikashoppayment_payplug2' => array('HikaShop PayPlug v2 payment plugin',0,0)
			,'plg_hikashoppayment_payuindia' => array('HikaShop PayU India payment plugin',0,0)
			,'plg_hikashoppayment_postfinance' => array('HikaShop Post Finance payment plugin',0,0)
			,'plg_hikashoppayment_purchaseorder' => array('HikaShop Purchase Order payment plugin',0,0)
			,'plg_hikashoppayment_pxpay' => array('HikaShop Payment Express PxPay payment plugin',0,0)
			,'plg_hikashoppayment_servired' => array('HikaShop Servired payment plugin',0,0)
			,'plg_hikashoppayment_userpoints' => array('HikaShop User Points payment plugin',0,0)
			,'plg_hikashoppayment_virtualmerchant' => array('HikaShop VirtualMerchant payment plugin',0,0)
			,'plg_hikashoppayment_westernunion' => array('HikaShop Western Union payment plugin',0,0)
			,'plg_hikashoppayment_westpacapi' => array('HikaShop WestPac API payment plugin',0,0)
			,'plg_hikashoppayment_worldnettps' => array('HikaShop WorldNetTPS payment plugin',0,0)
			,'plg_hikashopshipping_aupost' => array('HikaShop Australia Post shipping plugin (deprecated)',0,0)
			,'plg_hikashopshipping_aupost2' => array('HikaShop Australia Post shipping plugin V2',0,0)
			,'plg_hikashopshipping_canadapost' => array('HikaShop Canada Post shipping plugin',0,0)
			,'plg_hikashopshipping_canpar' => array('HikaShop CANPAR shipping plugin',0,0)
			,'plg_hikashopshipping_fedex' => array('HikaShop Fedex shipping plugin',0,0)
			,'plg_hikashopshipping_manual' => array('HikaShop manual shipping plugin',0,0)
			,'plg_hikashopshipping_ups' => array('HikaShop UPS shipping plugin',0,0)
			,'plg_hikashopshipping_usps' => array('HikaShop USPS shipping plugin',0,0)
			,'plg_quickicon_hikashop' => array('HikaShop Quickicon plugin',0,1)
			,'plg_search_hikashop_categories' => array('HikaShop categories search plugin',0,1)
			,'plg_search_hikashop_products' => array('HikaShop products search plugin',0,1)
			,'plg_system_custom_price' => array('HikaShop Donation plugin',0,0)
			,'plg_system_hikashopaffiliate' => array('HikaShop affiliate plugin',0,1)
			,'plg_system_hikashopanalytics' => array('HikaShop Google Analytics plugin',0,0)
			,'plg_system_hikashopgeolocation' => array('HikaShop geolocation plugin',0,0)
			,'plg_system_hikashopmassaction' => array('HikaShop massaction plugin',0,1)
			,'plg_system_hikashoppayment' => array ('HikaShop Payment Notification plugin',0,1)
			,'plg_system_hikashopproductinsert' => array('HikaShop product tag translation plugin',0,1)
			,'plg_system_hikashopproducttag' => array('HikaShop Microdata on Product Page plugin',0,0)
			,'plg_system_hikashopregistrationredirect' => array('Redirect Joomla registration to HikaShop plugin',0,0)
			,'plg_system_hikashopremarketing' => array('HikaShop Google Dynamic Remarketing (conversion tracking) plugin',0,0)
			,'plg_system_hikashopsocial' => array('Hikashop Social Networks plugin',0,0)
			,'plg_system_hikashopuser' => array('HikaShop user synchronization plugin',0,1)
			,'plg_system_mijo_redirect' => array('Mijoshop Fallback Redirect plugin',0,0)
			,'plg_system_nossloutsidecheckout' => array('HikaShop no SSL outside checkout plugin',0,0)
			,'plg_system_reds_redirect' => array('Redshop Fallback Redirect plugin',0,0)
			,'plg_system_vm_redirect' => array('VirtueMart Fallback Redirect plugin',0,0)
		);

		if($install == true) {
			$this->checkExtensions($extensioninfo, $plugins, $modules);
		} else {
			$this->installExtensionsFromDir($extensioninfo, $plugins, $modules, $dirs, $path, $existingExtensions);
		}

		$delete = array(
			'plg_hikashoppayment_sagepay' => array('hikashoppayment','sagepay','The plugin &quot;SagePay&quot; has been removed. For more details please contact the HikaShop support team.')
		);
		foreach($delete as $name => $struct) {
			$oneFolder = $path . DS . $name;
			if(!JFolder::exists($oneFolder))
				continue;
			JFolder::delete($oneFolder);
			if(substr($name, 0, 4) != 'plg_')
				continue;
			$destinationFolder = HIKASHOP_ROOT.'plugins'.DS.$struct[0].DS.$struct[1];
			if(JFolder::exists($destinationFolder))
				JFolder::delete($destinationFolder);
			$query = 'DELETE FROM `#__extensions` WHERE type = \'plugin\' AND element = '.$this->db->Quote($struct[1]).' AND folder = '.$this->db->Quote($struct[0]);
			$this->db->setQuery($query);
			$this->db->execute();

			if(empty($struct[2]))
				hikashop_display('The plugin &quot;'.$struct[1].'&quot; has been removed', 'error');
			else
				hikashop_display($struct[2], 'error');
		}

		$extensions = array_merge($plugins,$modules);

		$success = array();
		if(!empty($extensions)) {
			$ext_fields = array('`name`','`element`','`folder`','`enabled`','`ordering`','`type`','`access`','`client_id`');
			if(HIKASHOP_J40)
				$ext_fields = array_merge($ext_fields, array('`manifest_cache`','`params`'));

			$queryExtensions = 'INSERT INTO `#__extensions` ('.implode(',', $ext_fields).') VALUES ';

			foreach($extensions as $oneExt) {
				$extensionData = new stdClass();
				$extensionData->name = $oneExt->name;
				$extensionData->type = $oneExt->type;
				$extensionData->creationDate = date('d m Y');
				$extensionData->author = 'HikaShop';
				$extensionData->copyright = '(C) 2011-'.date('Y').' HIKARI SOFTWARE. All rights reserved.';
				$extensionData->authorEmail = 'contact@hikashop.com';
				$extensionData->authorUrl = 'https://www.hikashop.com';
				$extensionData->version = '4.7.4';
				$extensionData->description = $oneExt->name;
				$extensionData->group = '';
				$extensionData->filename = $oneExt->element;
				$manifest = json_encode($extensionData);
				$data = array(
					$this->db->Quote($oneExt->name),
					$this->db->Quote($oneExt->element),
					$this->db->Quote($oneExt->folder),
					$oneExt->enabled,
					$oneExt->ordering,
					$this->db->Quote($oneExt->type),
					1,
					(int)@$oneExt->client_id,
				);
				if(HIKASHOP_J40)
					$data = array_merge($data, array($this->db->Quote($manifest), "''"));

				$queryExtensions .= '('. implode(',', $data) . '),';
				if($oneExt->type != 'module') {
					$success[] = JText::sprintf('PLUG_INSTALLED', $oneExt->name);
				}
			}
			$queryExtensions = trim($queryExtensions,',');

			$this->db->setQuery($queryExtensions);
			$this->db->execute();
		}

		if(!empty($modules)) {
			$front_position = 'position-7';
			if(HIKASHOP_J40)
				$front_position = 'sidebar-right';
			foreach($modules as $oneModule) {
				$position = empty($oneModule->client_id) ? $front_position : 'cpanel';
				$ext_fields = array('`title`','`position`','`published`','`module`','`access`','`language`','`client_id`');
				if(HIKASHOP_J40)
					$ext_fields = array_merge($ext_fields, array('`params`'));

				$queryModule = 'INSERT INTO `#__modules` ('.implode(',', $ext_fields).') VALUES ';
				$data = array(
					$this->db->Quote($oneModule->name),
					$this->db->Quote($position),
					0,
					$this->db->Quote($oneModule->element),
					1,
					$this->db->Quote('*'),
					(int)@$oneModule->client_id
				);
				if(HIKASHOP_J40)
					$data = array_merge($data, array("''"));
				$queryModule .= '('. implode(',', $data) . ')';

				$this->db->setQuery($queryModule);
				$this->db->execute();
				$moduleId = $this->db->insertid();

				$this->db->setQuery('INSERT IGNORE INTO `#__modules_menu` (`moduleid`,`menuid`) VALUES ('.$moduleId.',0)');
				$this->db->execute();

				$success[] = JText::sprintf('MODULE_INSTALLED',$oneModule->name);
			}
		}

		$pluginsClass = hikashop_get('class.plugins');
		$pluginsClass->cleanPluginCache();

		if(JFolder::exists($path))
			JFolder::delete($path);

		if(!empty($success) && $install == false)
			hikashop_display($success,'success');
	}

	protected function checkExtensions($extensioninfo, &$plugins, &$modules) {
		foreach($extensioninfo as $extension => $info) {
			$ext = explode('_', $extension);
			$prefix = array_shift($ext);

			$path = rtrim(JPATH_ROOT, DS);

			if($prefix == 'plg') {
				$folder = array_shift($ext);
				$element = implode('_',$ext);
				$path .= DS."plugins".DS.$folder.DS.$element;
				if(!is_dir($path))
					continue;

				$newPlugin = new stdClass();
				$newPlugin->name = $extension;
				if(isset($info[0]))
					$newPlugin->name = $info[0];
				$newPlugin->type = 'plugin';
				$newPlugin->folder = $folder;
				$newPlugin->element = $element;
				$newPlugin->enabled = 1;
				if(isset($info[2]) && is_numeric($info[2]))
					$newPlugin->enabled = (int)$info[2];
				$newPlugin->params = '{}';
				$newPlugin->ordering = 0;
				if(isset($info[1]))
					$newPlugin->ordering = $info[1];

				$plugins[] = $newPlugin;

			} elseif($prefix == 'mod') {
				$isAdmin = (isset($info[3]) && is_array($info[3]) && in_array('admin', $info[3]));
				if($isAdmin)
					$path = rtrim(JPATH_ADMINISTRATOR, DS);

				$path .= DS.'modules'.DS.'mod_'.implode('_', $ext);
				if(!is_dir($path))
					continue;

				$newModule = new stdClass();
				$newModule->name = $extension;
				if(isset($info[0]))
					$newModule->name = $info[0];

				$newModule->type = 'module';
				$newModule->folder = '';
				$newModule->element = $extension;
				$newModule->enabled = 1;
				$newModule->params = '{}';
				$newModule->ordering = 0;
				$newModule->client_id = ($isAdmin) ? 1 : 0;


				$modules[] = $newModule;
			} else {
				hikashop_display('Could not handle : ' . $extension, 'error');
				continue;
			}
		}
	}

	protected function installExtensionsFromDir($extensioninfo, &$plugins, &$modules, $dirs, $path, $existingExtensions) {
		if(empty($dirs) || !is_array($dirs))
			return;

		$listTables = $this->db->getTableList();
		$this->errors = array();

		foreach($dirs as $oneDir) {
			$arguments = explode('_', $oneDir);

			if(!isset($extensioninfo[$oneDir]))
				continue;

			$report = empty($extensioninfo[$oneDir][3]);
			$prefix = array_shift($arguments);

			if($prefix == 'plg') {
				$newPlugin = new stdClass();
				$newPlugin->name = $oneDir;
				if(isset($extensioninfo[$oneDir][0]))
					$newPlugin->name = $extensioninfo[$oneDir][0];
				$newPlugin->type = 'plugin';
				$newPlugin->folder = array_shift($arguments);
				$newPlugin->element = implode('_', $arguments);
				$newPlugin->enabled = 1;

				if(isset($extensioninfo[$oneDir][2])) {
					if(is_numeric($extensioninfo[$oneDir][2]))
						$newPlugin->enabled = $extensioninfo[$oneDir][2];
				}
				$newPlugin->params = '{}';
				$newPlugin->ordering = 0;
				if(isset($extensioninfo[$oneDir][1]))
					$newPlugin->ordering = $extensioninfo[$oneDir][1];

				if(!hikashop_createDir(HIKASHOP_ROOT.'plugins'.DS.$newPlugin->folder, $report))
					continue;

				$destinationFolder = HIKASHOP_ROOT.'plugins'.DS.$newPlugin->folder.DS.$newPlugin->element;
				if(!hikashop_createDir($destinationFolder))
					continue;

				if(!$this->copyFolder($path.DS.$oneDir, $destinationFolder))
					continue;

				if(in_array($newPlugin->folder.$newPlugin->element, $existingExtensions))
					continue;

				$plugins[] = $newPlugin;

			} elseif($prefix == 'mod') {
				$newModule = new stdClass();
				$newModule->name = $oneDir;
				if(isset($extensioninfo[$oneDir][0]))
					$newModule->name = $extensioninfo[$oneDir][0];
				$newModule->type = 'module';
				$newModule->folder = '';
				$newModule->element = $oneDir;
				$newModule->enabled = 1;
				$newModule->params = '{}';
				$newModule->ordering = 0;
				if(isset($extensioninfo[$oneDir][1]))
					$newModule->ordering = $extensioninfo[$oneDir][1];
				$newModule->client_id = (isset($extensioninfo[$oneDir][3]) && is_array($extensioninfo[$oneDir][3]) && in_array('admin', $extensioninfo[$oneDir][3])) ? 1 : 0;

				$destinationFolder = HIKASHOP_ROOT.'modules'.DS.$oneDir;
				if($newModule->client_id == 1)
					$destinationFolder = rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'modules' . DS . $oneDir;

				if(!hikashop_createDir($destinationFolder))
					continue;
				if(!$this->copyFolder($path.DS.$oneDir, $destinationFolder))
					continue;

				if($newModule->element == 'mod_hikashop_filter'){
					$this->db->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\'hikashop-menu-for-products-listing\'');
					$menu_id = $this->db->loadResult();
					if($menu_id) {
						$fileContent = file_get_contents($destinationFolder.DS.'mod_hikashop_filter.xml');
						if(!empty($fileContent)) {
							$fileContent = str_replace('name="itemid" type="text" default=""', 'name="itemid" type="text" default="'.$menu_id.'"', $fileContent);
							JFile::write($destinationFolder.DS.'mod_hikashop_filter.xml', $fileContent);
						}
					}
				}
				if(in_array($newModule->element, $existingExtensions))
					continue;

				$modules[] = $newModule;
			} else {
				hikashop_display('Could not handle : '.$oneDir, 'error');
			}
		}

		if(!empty($this->errors))
			hikashop_display($this->errors, 'error');
	}

	public function copyFolder($from, $to) {
		$return = true;

		$allFiles = JFolder::files($from);
		foreach($allFiles as $oneFile) {
			if(file_exists($to.DS.'index.html') && $oneFile == 'index.html')
				continue;

			if(JFile::copy($from.DS.$oneFile,$to.DS.$oneFile) !== true) {
				$this->errors[] = 'Could not copy the file from '.$from.DS.$oneFile.' to '.$to.DS.$oneFile;
				$return = false;
			}
		}

		$allFolders = JFolder::folders($from);
		if(!empty($allFolders)) {
			foreach($allFolders as $oneFolder) {
				if(!hikashop_createDir($to.DS.$oneFolder))
					continue;
				if(!$this->copyFolder($from.DS.$oneFolder,$to.DS.$oneFolder))
					$return = false;
			}
		}
		return $return;
	}

	function installTags() {
		$tagsHelper = hikashop_get('helper.tags');
		$tagsHelper->initTags();
	}

	function installMenu($code = '') {
		if(empty($code)){
			$lang = JFactory::getLanguage();
			$code = $lang->getTag();
		}
		$path = hikashop_getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_hikashop.ini';
		if(!file_exists($path)) return;
		$content = file_get_contents($path);
		if(empty($content)) return;

		$menuFileContent = 'COM_HIKASHOP="HikaShop"'."\r\n".'HIKASHOP="HikaShop"'."\r\n".'COM_HIKASHOP_DASHBOARD_VIEW_TITLE="DASHBOARD"'."\r\n";
		$menuStrings = array('PRODUCTS','CATEGORIES','USERS','ORDERS','CONFIGURATION','DISCOUNTS','HELP','UPDATE_ABOUT');
		foreach($menuStrings as $oneString){
			preg_match('#(\n|\r)(HIKA_)?'.$oneString.'="(.*)"#i',$content,$matches);
			if(empty($matches[3])) continue;
			$menuFileContent .= $oneString.'="'.$matches[3].'"'."\r\n";
		}

		preg_match_all('#(\n|\r)(COM_HIKASHOP_.*)="(.*)"#iU',$content,$matches);

		if(!empty($matches)){
			$menuFileContent .= implode('',$matches[0]);
		}
		$menuFileContent.="\r\n".'COM_HIKASHOP_CONFIGURATION="HikaShop"';


		$menuPath = HIKASHOP_ROOT.'administrator'.DS.'language'.DS.$code.DS.$code.'.com_hikashop.sys.ini';
		if(!JFile::write($menuPath, $menuFileContent)){
			hikashop_display(JText::sprintf('FAIL_SAVE',$menuPath),'error');
		}
	}

	function _installOne($folder){
		if(empty($folder)) return false;
		unset($GLOBALS['_JREQUEST']['installtype']);
		unset($GLOBALS['_JREQUEST']['install_directory']);
		hikaInput::get()->set('installtype','folder');
		hikaInput::get()->set('install_directory',$folder);
		$_REQUEST['installtype']='folder';
		$_REQUEST['install_directory']=$folder;
		$controller = new JController(array('base_path'=>
		HIKASHOP_ROOT.'administrator'.DS.'components'.DS.'com_installer','name'=>'Installer','default_task'
		=> 'installform'));
		$model  = $controller->getModel('Install');
		return $model->install();
	}
	function getUrl(){
		$urls = parse_url(HIKASHOP_LIVE);
		$lurl = preg_replace('#^www2?\.#Ui','',$urls['host'],1);
		if(!empty($urls['path'])) $lurl .= $urls['path'];
		return strtolower(rtrim($lurl,'/'));
	}
	function addJoomfishElements($force=true){
		$dstFolder = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_joomfish'.DS.'contentelements'.DS;
		if(JFolder::exists($dstFolder)){
			$srcFolder = HIKASHOP_BACK.'translations'.DS;
			$files = JFolder::files($srcFolder);
			if(!empty($files)){
				$db = JFactory::getDBO();
				$query = 'SHOW TABLES LIKE '.$db->Quote($db->getPrefix().substr(hikashop_table('jf_content',false),3));
				$db->setQuery($query);
				$table = $db->loadResult();
				$type = (!empty($table)) ? 'jf' : null;

				foreach($files as $file){
					$this->processJoomfishFile($file, $srcFolder.$file, $dstFolder.$file, $force, $type);
				}
			}
		}
		$dstFolder = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_falang'.DS.'contentelements'.DS;
		if(JFolder::exists($dstFolder)){
			$srcFolder = HIKASHOP_BACK.'falang'.DS;
			$files = JFolder::files($srcFolder);
			if(!empty($files)){
				$db = JFactory::getDBO();
				$query = 'SHOW TABLES LIKE '.$db->Quote($db->getPrefix().substr(hikashop_table('falang_content',false),3));
				$db->setQuery($query);
				$table = $db->loadResult();
				$type = (!empty($table)) ? 'falang' : null;

				foreach($files as $file){
					$this->processJoomfishFile($file, $srcFolder.$file, $dstFolder.$file, $force, $type);
				}
			}
		}
		return true;
	}

	function processJoomfishFile($file, $source, $destination, $force = true, $type = 'falang') {
		$types = array(
			'hikashop_product.xml' => array(
				'type' => 'product',
				'name' => 'Product %s'
			),
			'hikashop_category.xml' => array(
				'type' => 'category',
				'name' => 'Category %s'
			)
		);
		if(empty($types[$file])) {
			if($force || !file_exists($destination))
				JFile::copy($source, $destination);
			return;
		}

		static $custom_fields = array();
		$db = JFactory::getDBO();

		if(!isset($custom_fields[$file])) {
			$query = 'SELECT * FROM '.hikashop_table('field').' WHERE field_table = ' . $db->Quote($types[$file]['type']); // $types[$file]['type']
			$db->setQuery($query);
			$custom_fields[$file] = $db->loadObjectList('field_namekey');
		}
		$fields = $custom_fields[$file];

		if(empty($fields)) {
			JFile::copy($source, $destination);
			return;
		}

		$xml = simplexml_load_file($source);

		$unpublish = array();
		$publish = array();
		foreach($fields as $fieldName => $oneExtraField) {
			if(!empty($oneExtraField->field_options) && is_string($oneExtraField->field_options))
				$oneExtraField->field_options = hikashop_unserialize($oneExtraField->field_options);
			if(empty($oneExtraField->field_options['translatable'])){
				$unpublish[] = $fieldName;
				continue;
			}else{
				$publish[] = $fieldName;
			}

			$fieldTitle = JText::sprintf($types[$file]['name'], $fieldName);
			if($oneExtraField->field_type == 'textarea' || $oneExtraField->field_type == 'text') {
				$field = $xml->reference->table->addChild('field', $fieldTitle);
				$field->addAttribute('type', 'text');
				$field->addAttribute('name', $fieldName);
				$field->addAttribute('length', '255');
				$field->addAttribute('maxlength', '255');
				$field->addAttribute('translate', '1');
			}
			if($oneExtraField->field_type == 'wysiwyg') {
				$field = $xml->reference->table->addChild('field', $fieldTitle);
				$field->addAttribute('type', 'htmltext');
				$field->addAttribute('name', $fieldName);
				$field->addAttribute('translate', '1');
			}
		}

		if(!empty($unpublish) && !empty($type)){

			foreach($unpublish as $k => $v){
				$unpublish[$k] = $db->Quote($v);
			}
			$db->setQuery('UPDATE #__'.$type.'_content SET published=0 WHERE reference_field IN ('.implode(',',$unpublish).') AND reference_table='.$db->Quote('hikashop_'.$types[$file]['type']));
			$db->execute();
		}

		if(!empty($publish) && !empty($type)){

			foreach($publish as $k => $v){
				$publish[$k] = $db->Quote($v);
			}
			$db->setQuery('UPDATE #__'.$type.'_content SET published=1 WHERE reference_field IN ('.implode(',',$publish).') AND reference_table='.$db->Quote('hikashop_'.$types[$file]['type']));
			$db->execute();
		}

		$xml->asXML($destination);
	}

	public function addUpdateSite() {
		$config = hikashop_config();
		$newconfig = new stdClass();
		$newconfig->website = HIKASHOP_LIVE;
		$config->save($newconfig);
		$query="SELECT update_site_id FROM #__update_sites WHERE location LIKE '%hikashop%' AND type LIKE 'extension'";
		$this->db->setQuery($query);
		$update_site_id = $this->db->loadResult();
		$object = new stdClass();
		$object->name='Hikashop';
		$object->type='extension';
		if(hikashop_level(1)){
			$object->location='http://www.hikashop.com/component/updateme/updatexml/component-hikashop/version-'.$config->get('version').'/level-'.$config->get('level').'/li-'.urlencode(base64_encode(HIKASHOP_LIVE)).'/file-extension.xml';
		}else{
			$object->location='http://www.hikashop.com/component/updateme/updatexml/component-hikashop/level-'.$config->get('level').'/file-extension.xml';
		}
		$object->enabled=1;
		if(empty($update_site_id)){
			$this->db->insertObject("#__update_sites",$object);
			$update_site_id = $this->db->insertid();
		}else{
			$object->update_site_id = $update_site_id;
			$this->db->updateObject("#__update_sites",$object,'update_site_id');
		}
		$query="SELECT extension_id FROM #__extensions WHERE `name` LIKE 'hikashop' AND type LIKE 'component'";
		$this->db->setQuery($query);
		$extension_id = $this->db->loadResult();
		if(empty($update_site_id) OR empty($extension_id))  return false;
		$query='INSERT IGNORE INTO #__update_sites_extensions (update_site_id, extension_id) values ('.$update_site_id.','.$extension_id.')';
		$this->db->setQuery($query);
		$this->db->execute();
		return true;
	}

	function addDefaultData(){
		$query = 'SELECT * FROM `#__menu` WHERE `title` IN (\'com_hikashop\',\'hikashop\',\'HikaShop\') AND `client_id`=1 AND `parent_id`=1 AND menutype IN (\'main\',\'mainmenu\',\'menu\')';
		$this->db->setQuery($query);
		$parentData = $this->db->loadObject();

		if(!empty($parentData)) {
			$parent = $parentData->id;
			$query = 'SELECT id FROM `#__menu` WHERE `parent_id`='.(int)$parent;
			$this->db->setQuery($query);
			$submenu = $this->db->loadColumn();
			$old = count($submenu);

			$query = 'DELETE FROM `#__menu` WHERE `parent_id` = '.(int)$parent;
			$this->db->setQuery($query);
			$this->db->execute();

			$query = 'UPDATE `#__menu` SET `rgt`=`rgt`-'.($old*2).' WHERE `rgt` >= '.(int)$parentData->rgt;
			$this->db->setQuery($query);
			$this->db->execute();

			$query = 'UPDATE `#__menu` SET `rgt`=`rgt`+16 WHERE `rgt` >= '.(int)$parentData->rgt;
			$this->db->setQuery($query);
			$this->db->execute();

			$query = 'UPDATE `#__menu` SET `lft`=`lft`+16 WHERE `lft` > '.(int)$parentData->lft;
			$this->db->setQuery($query);
			$this->db->execute();

			$left = $parentData->lft;
			$cid = $parentData->component_id;
			$query  = "INSERT IGNORE INTO `#__menu` (`published`,`type`,`link`,`menutype`,`img`,`alias`,`title`,`client_id`,`parent_id`,`level`,`language`,`lft`,`rgt`,`component_id`) VALUES
			('1','component','index.php?option=com_hikashop&ctrl=product','".$parentData->menutype."','./templates/bluestork/images/menu/icon-16-article.png','Products','Products',1,".(int)$parent.",2,'*',".($left+1).",".($left+2).",".$cid."),
			('1','component','index.php?option=com_hikashop&ctrl=category&filter_id=product','".$parentData->menutype."','./templates/bluestork/images/menu/icon-16-category.png','Categories','Categories',1,".(int)$parent.",2,'*',".($left+3).",".($left+4).",".$cid."),
			('1','component','index.php?option=com_hikashop&ctrl=user&filter_partner=0','".$parentData->menutype."','./templates/bluestork/images/menu/icon-16-user.png','Users','Users',1,".(int)$parent.",2,'*',".($left+5).",".($left+6).",".$cid."),
			('1','component','index.php?option=com_hikashop&ctrl=order&order_type=sale&filter_partner=0','".$parentData->menutype."','./templates/bluestork/images/menu/icon-16-content.png','Orders','Orders',1,".(int)$parent.",2,'*',".($left+7).",".($left+8).",".$cid."),
			('1','component','index.php?option=com_hikashop&ctrl=config','".$parentData->menutype."','./templates/bluestork/images/menu/icon-16-config.png','Configuration','Configuration',1,".(int)$parent.",2,'*',".($left+9).",".($left+10).",".$cid."),
			('1','component','index.php?option=com_hikashop&ctrl=discount','".$parentData->menutype."','./templates/bluestork/images/menu/icon-16-default.png','Discounts','Discounts',1,".(int)$parent.",2,'*',".($left+11).",".($left+12).",".$cid."),
			('1','component','index.php?option=com_hikashop&ctrl=documentation','".$parentData->menutype."','./templates/bluestork/images/menu/icon-16-help.png','Help','Help',1,".(int)$parent.",2,'*',".($left+13).",".($left+14).",".$cid."),
			('1','component','index.php?option=com_hikashop&ctrl=update','".$parentData->menutype."','./templates/bluestork/images/menu/icon-16-help-jrd.png','Update / About','Update_About',1,".(int)$parent.",2,'*',".($left+15).",".($left+16).",".$cid.");
			";
			$this->db->setQuery($query);
			$this->db->execute();

			$query = 'UPDATE '.hikashop_table('menu',false).' SET component_id = '.$cid.' WHERE menutype = '.$this->db->quote('hikashop_default');
			$this->db->setQuery($query);
			$this->db->execute();
		}

		$query = 'INSERT IGNORE INTO `#__hikashop_user` (`user_email`,`user_cms_id`,`user_created`) SELECT `email`, `id`,'.time().' FROM `#__users`';
		$this->db->setQuery($query);
		try{$this->db->execute();}catch(Exception $e){}

		$query = 'UPDATE `#__hikashop_user` AS hku JOIN `#__users` AS ju ON hku.`user_email`=ju.`email` SET hku.`user_cms_id`=ju.`id` WHERE hku.`user_cms_id`!=ju.`id`';
		$this->db->setQuery($query);
		try{$this->db->execute();}catch(Exception $e){}

		$query = "INSERT IGNORE INTO `#__hikashop_category` (`category_id`, `category_parent_id`, `category_type`, `category_name`, `category_description`, `category_published`, `category_ordering`, `category_left`, `category_right`, `category_depth`, `category_namekey`) VALUES
(1, 0, 'root', 'ROOT', '', 0, 0, 1, 22, 0, 'root'),
(2, 1, 'product', 'product category', '', 1, 1, 2, 3, 1, 'product'),
(3, 1, 'tax', 'taxation category', '', 1, 2, 4, 7, 1, 'tax'),
(4, 1, 'status', 'order status', '', 1, 3, 8, 19, 1, 'status'),
(5, 4, 'status', 'created', 'When a customer finishes a checkout, an order is created with the status created', 1, 1, 9, 10, 2, 'created'),
(6, 4, 'status', 'confirmed', 'When the payment is confirmed or that the payment is done at delivery the order becomes confirmed', 1, 2, 11, 12, 2, 'confirmed'),
(7, 4, 'status', 'cancelled', 'When an order is cancelled before receiving a payment', 1, 3, 13, 14, 2, 'cancelled'),
(8, 4, 'status', 'refunded', 'When an order is cancelled after receiving a payment', 1, 4, 15, 16, 2, 'refunded'),
(9, 4, 'status', 'shipped', 'When an order has been shipped', 1, 5, 17, 18, 2, 'shipped'),
(10, 1, 'manufacturer', 'manufacturer', '', 1, 5, 20, 21, 1, 'manufacturer'),
(11, 3, 'tax', 'Default tax category', '', 1, 1, 5, 6, 2, 'default_tax');";
		$this->db->setQuery($query);
		$this->db->execute();

		$query = "INSERT IGNORE INTO `#__hikashop_orderstatus` (`orderstatus_id`, `orderstatus_name`, `orderstatus_description`, `orderstatus_published`, `orderstatus_ordering`, `orderstatus_namekey`, `orderstatus_email_params`, `orderstatus_links_params`) VALUES
(1, 'Created', 'When a customer finishes a checkout, an order is created with the status created', 1, 1, 'created', '', ''),
(2, 'Confirmed', 'When the payment is confirmed or that the payment is done at delivery the order becomes confirmed', 1, 2, 'confirmed', '', ''),
(3, 'cancelled', 'When an order is cancelled before receiving a payment', 1, 3, 'cancelled', '', ''),
(4, 'refunded', 'When an order is cancelled after receiving a payment', 1, 4, 'refunded', '', ''),
(5, 'shipped', 'When an order has been shipped', 1, 5, 'shipped', '', ''),
(6, 'pending', 'When an order is created and the payment is still pending', 1, 6, 'pending', '', ''),
(7, 'returned', 'When an order is returned by the user', 1, 7, 'pending', '', '');";
		$this->db->setQuery($query);
		$this->db->execute();

		$query = <<<EOD
INSERT IGNORE INTO `#__hikashop_field` (`field_table`, `field_realname`, `field_namekey`, `field_type`, `field_value`, `field_published`, `field_ordering`, `field_options`, `field_core`, `field_required`, `field_backend`, `field_frontcomp`, `field_default`, `field_backend_listing`) VALUES
('address', 'Title', 'address_title', 'singledropdown', 'Mr::HIKA_TITLE_MR\nMrs::HIKA_TITLE_MRS\nMiss::HIKA_TITLE_MISS\nMs::HIKA_TITLE_MS\nDr::HIKA_TITLE_DR', 1, 1, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 1, 1, 1, '', 0),
('address', 'Firstname', 'address_firstname', 'text', '', 1, 2, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 1, 1, 1, '', 0),
('address', 'Middle name', 'address_middle_name', 'text', '', 0, 3, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 0, 1, 1, '', 0),
('address', 'Lastname', 'address_lastname', 'text', '', 1, 4, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 1, 1, 1, '', 0),
('address', 'Company', 'address_company', 'text', '', 0, 5, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 0, 1, 1, '', 0),
('address', 'Street', 'address_street', 'text', '', 1, 6, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 1, 1, 1, '', 0),
('address', 'Complement', 'address_street2', 'text', '', 0, 7, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 0, 1, 1, '', 0),
('address', 'Post code', 'address_post_code', 'text', '', 1, 8, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 0, 1, 1, '', 0),
('address', 'City', 'address_city', 'text', '', 1, 9, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 1, 1, 1, '', 0),
('address', 'Telephone', 'address_telephone', 'text', '', 1, 10, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 1, 1, 1, '', 0),
('address', 'Telephone', 'address_telephone2', 'text', '', 0, 11, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 0, 1, 1, '', 0),
('address', 'Fax', 'address_fax', 'text', '', 0, 12, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 0, 1, 1, '', 0),
('address', 'Country', 'address_country', 'zone', '', 1, 13, 'a:6:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:9:"zone_type";s:7:"country";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 1, 1, 1, 'country_France_73', 0),
('address', 'State', 'address_state', 'zone', '', 1, 14, 'a:6:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:9:"zone_type";s:5:"state";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 1, 1, 1, 'state_Rh__ne_1375', 0),
('address', 'VAT number', 'address_vat', 'text', '', 0, 15, 'a:6:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:9:"zone_type";s:7:"country";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 0, 1, 1, '', 0);
EOD;
		$this->db->setQuery($query);
		$this->db->execute();

		$query = <<<EOD
INSERT IGNORE INTO `#__hikashop_widget` (`widget_id`, `widget_name`, `widget_params`, `widget_published`, `widget_ordering`, `widget_access`) VALUES
(1, 'Last seven orders', 'O:8:"stdClass":27:{s:7:"display";s:7:"listing";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:7:"last30d";s:6:"period";s:1:"0";s:7:"content";s:6:"orders";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"best_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:7:"periods";s:14:"period_compare";s:4:"none";s:5:"limit";s:1:"7";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:8:"shipping";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"last";s:8:"compares";N;s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 1, 1, 'all'),
(2, 'Sales this month', 'O:8:"stdClass":27:{s:7:"display";s:5:"graph";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:7:"last30d";s:6:"period";s:1:"0";s:7:"content";s:5:"sales";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"last_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:7:"periods";s:14:"period_compare";s:4:"none";s:5:"limit";s:1:"7";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:8:"shipping";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"last";s:8:"compares";N;s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 1, 2, 'all'),
(3, 'Main Statistics', 'O:8:"stdClass":6:{s:7:"display";s:5:"table";s:10:"categories";s:3:"all";s:7:"coupons";s:6:"a:0:{}";s:5:"table";a:5:{i:0;O:8:"stdClass":2:{s:8:"row_name";s:16:"Sales this month";s:13:"widget_params";O:8:"stdClass":15:{s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:9:"thisMonth";s:5:"start";s:0:"";s:3:"end";s:0:"";s:6:"period";s:1:"0";s:7:"content";s:5:"sales";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:7:"payment";s:9:"_multiple";s:15:"category_childs";s:1:"0";s:9:"customers";s:13:"last_customer";s:8:"apply_on";s:7:"product";s:7:"display";s:5:"table";s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";}}i:1;O:8:"stdClass":2:{s:8:"row_name";s:22:"Best product this year";s:13:"widget_params";O:8:"stdClass":14:{s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:8:"thisYear";s:5:"start";s:0:"";s:3:"end";s:0:"";s:6:"period";s:1:"0";s:7:"content";s:4:"best";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:13:"last_customer";s:8:"apply_on";s:7:"product";s:7:"display";s:5:"table";s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";}}i:2;O:8:"stdClass":2:{s:8:"row_name";s:22:"Best customer all time";s:13:"widget_params";O:8:"stdClass":15:{s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:3:"all";s:5:"start";s:1:"0";s:3:"end";s:1:"0";s:6:"period";s:1:"0";s:7:"content";s:9:"customers";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:13:"best_customer";s:8:"partners";s:13:"last_partners";s:8:"apply_on";s:7:"product";s:7:"display";s:5:"table";s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";}}i:3;O:8:"stdClass":2:{s:8:"row_name";s:20:"Orders created today";s:13:"widget_params";O:8:"stdClass":15:{s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:5:"today";s:5:"start";s:0:"";s:3:"end";s:0:"";s:6:"period";s:1:"0";s:7:"content";s:6:"orders";s:7:"filters";s:72:"a:1:{s:14:"a.order_status";a:2:{i:0;s:7:"created";i:1;s:9:"confirmed";}}";s:7:"payment";s:9:"_multiple";s:15:"category_childs";s:1:"0";s:9:"customers";s:13:"last_customer";s:8:"apply_on";s:7:"product";s:7:"display";s:5:"table";s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";}}i:4;O:8:"stdClass":2:{s:8:"row_name";s:24:"Best category this month";s:13:"widget_params";O:8:"stdClass":15:{s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:8:"thisWeek";s:5:"start";s:0:"";s:3:"end";s:0:"";s:6:"period";s:1:"0";s:7:"content";s:4:"best";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:7:"payment";s:9:"_multiple";s:15:"category_childs";s:1:"0";s:9:"customers";s:13:"last_customer";s:8:"apply_on";s:8:"category";s:7:"display";s:5:"table";s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";}}}s:6:"status";s:0:"";s:8:"products";s:6:"a:0:{}";}', 1, 3, 'all'),
(4, 'Weekly performance', 'O:8:"stdClass":27:{s:7:"display";s:5:"gauge";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%u %Y";s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:3:"all";s:6:"period";s:1:"0";s:7:"content";s:5:"sales";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"last_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:7:"periods";s:14:"period_compare";s:4:"none";s:5:"limit";s:1:"7";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:8:"shipping";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"last";s:8:"compares";N;s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 0, 4, 'all'),
(5, 'Top sales', 'O:8:"stdClass":27:{s:7:"display";s:7:"listing";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:7:"last30d";s:6:"period";s:1:"0";s:7:"content";s:8:"products";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"last_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:7:"periods";s:14:"period_compare";s:4:"none";s:5:"limit";s:1:"7";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:8:"shipping";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"last";s:8:"compares";N;s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 0, 5, 'all'),
(6, 'Orders statuses this week', 'O:8:"stdClass":27:{s:7:"display";s:3:"pie";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:8:"thisWeek";s:6:"period";s:1:"0";s:7:"content";s:6:"orders";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"last_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:7:"periods";s:14:"period_compare";s:4:"none";s:5:"limit";s:1:"7";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:8:"shipping";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"last";s:8:"compares";N;s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 0, 6, 'all'),
(7, 'Sales comparison between this year vs last year', 'O:8:"stdClass":27:{s:7:"display";s:4:"area";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:7:"last30d";s:6:"period";s:1:"0";s:7:"content";s:5:"sales";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"last_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:7:"periods";s:14:"period_compare";s:9:"last_year";s:5:"limit";s:1:"7";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:8:"shipping";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"last";s:8:"compares";N;s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 0, 7, 'all'),
(8, 'Five best categories over the last two weeks', 'O:8:"stdClass":27:{s:7:"display";s:4:"line";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:10:"periodType";s:14:"specificPeriod";s:5:"start";i:1342367760;s:3:"end";i:1343663760;s:6:"period";s:7:"1296000";s:7:"content";s:6:"orders";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"last_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:6:"values";s:8:"compares";s:50:"a:1:{s:13:"c.category_id";s:15:"c.category_name";}";s:5:"limit";s:1:"5";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:8:"shipping";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"last";s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 0, 8, 'all'),
(9, 'Map', 'O:8:"stdClass":27:{s:7:"display";s:3:"map";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:3:"all";s:6:"period";s:1:"0";s:7:"content";s:6:"orders";s:7:"filters";s:45:"a:1:{s:14:"a.order_status";a:1:{i:0;s:0:"";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"best_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:7:"periods";s:14:"period_compare";s:4:"none";s:5:"limit";s:1:"5";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:7:"billing";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"best";s:8:"compares";N;s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 0, 9, 'all'),
(10, 'Orders by currency', 'O:8:"stdClass":26:{s:7:"display";s:6:"column";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:8:"thisWeek";s:6:"period";s:1:"0";s:7:"content";s:6:"orders";s:7:"filters";s:90:"a:2:{s:14:"a.order_status";a:1:{i:0;s:0:"";}s:19:"a.order_currency_id";a:1:{i:0;s:1:"1";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"last_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:6:"values";s:8:"compares";s:56:"a:1:{s:19:"a.order_currency_id";s:15:"d.currency_name";}";s:5:"limit";s:1:"7";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:8:"shipping";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"last";s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 0, 10, 'all'),
(11, 'Order to ship', 'O:8:"stdClass":27:{s:7:"display";s:7:"listing";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:10:"periodType";s:14:"proposedPeriod";s:14:"proposedPeriod";s:3:"all";s:6:"period";s:1:"0";s:7:"content";s:6:"orders";s:7:"filters";s:54:"a:1:{s:14:"a.order_status";a:1:{i:0;s:9:"confirmed";}}";s:15:"category_childs";s:1:"0";s:9:"customers";s:14:"last_customers";s:15:"customers_order";s:5:"sales";s:8:"partners";s:14:"last_customers";s:14:"partners_order";s:5:"sales";s:12:"compare_with";s:7:"periods";s:14:"period_compare";s:4:"none";s:5:"limit";s:0:"";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";s:10:"map_source";s:8:"shipping";s:12:"product_data";s:5:"sales";s:16:"product_order_by";s:4:"best";s:15:"orders_order_by";s:4:"last";s:8:"compares";N;s:10:"categories";s:3:"all";s:8:"products";s:6:"a:0:{}";s:7:"coupons";s:6:"a:0:{}";s:6:"status";s:0:"";}', 0, 11, 'all');
EOD;
		$this->db->setQuery($query);
		$this->db->execute();

		$this->processSQLfile('currencies.sql');

		$this->db->setQuery($query);
		$this->db->execute();

		$config = hikashop_config();
		if((int)$config->get('no_zone_import', false))
			return true;

		$this->processSQLfile('zones.sql');

		if($this->update)
			return true;

		$query = <<<EOD
INSERT IGNORE INTO `#__hikashop_widget` (`widget_id`, `widget_name`, `widget_params`) VALUES
(1, 'Last 7 orders', 'O:8:"stdClass":11:{s:7:"content";s:6:"orders";s:7:"display";s:7:"listing";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:5:"start";s:0:"";s:3:"end";s:0:"";s:6:"period";s:1:"0";s:6:"status";s:0:"";s:5:"limit";s:1:"7";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";}'),
(2, 'Sales over the last 30 days', 'O:8:"stdClass":11:{s:7:"content";s:5:"sales";s:7:"display";s:5:"graph";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:5:"start";s:0:"";s:3:"end";s:0:"";s:6:"period";s:7:"2592000";s:6:"status";s:17:"confirmed,shipped";s:5:"limit";s:0:"";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";}'),
(3, 'Last 7 customers', 'O:8:"stdClass":11:{s:7:"content";s:9:"customers";s:7:"display";s:7:"listing";s:9:"date_type";s:7:"created";s:10:"date_group";s:5:"%j %Y";s:5:"start";s:0:"";s:3:"end";s:0:"";s:6:"period";s:1:"0";s:6:"status";s:0:"";s:5:"limit";s:1:"7";s:6:"region";s:5:"world";s:6:"format";s:5:"UTF-8";}');
EOD;

		$this->processSQLfile('zone_links.sql');
	}

	protected function processSQLfile($filename) {
		if(!preg_match('/^[-_a-zA-Z0-9]+\.sql$/i', $filename))
			return false;

		$folder = HIKASHOP_BACK . '_database' . DS;

		jimport('joomla.filesystem.file');
		if(!JFile::exists($folder . $filename))
			return false;

		$query = '';
		$h = fopen($folder.$filename, 'r');
		if(!$h)
			return false;

		if(empty($this->db))
			$this->db = JFactory::getDBO();

		while($c = fread($h, 4096)) {
			$query .= $c;

			$p1 = strpos($query, 'INSERT IGNORE INTO `#__', 1);
			$p2 = strpos($query, 'INSERT INTO `#__', 1);

			if($p1 === false && $p2 === false)
				continue;

			if($p1 !== false) {
				$c = substr($query, $p1);
				$query = trim(substr($query, 0, $p1));
			} else {
				$c = substr($query, $p2);
				$query = trim(substr($query, 0, $p2));
			}

			if(!empty($query) && $this->checkSQLquery($query)) {
				$this->db->setQuery($query);
				try {
					 $this->db->execute();
				} catch(Exception $e) {}
			}

			$query = $c;
		}

		$query = trim($query);
		if(!empty($query) && $this->checkSQLquery($query)) {
			$this->db->setQuery($query);
			try {
				 $this->db->execute();
			} catch(Exception $e) {}
		}
		unset($query);

		return true;
	}

	protected function checkSQLquery($query) {
		$regexs = array(
			"/(\%27)|(\-\-)|(\%23)/ix",
			"/((\%3D)|(=))[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/i",
			"/((\%27)|(\'))union/ix",
		);
		foreach($regexs as $regex) {
			if(preg_match($regex, $query))
				return false;
		}
		return true;
	}
}
