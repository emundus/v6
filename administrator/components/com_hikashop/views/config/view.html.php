<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class configViewConfig extends hikashopView
{
	var $triggerView = true;

	public function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this, $function))
			$this->$function();
		parent::display($tpl);
	}

	public function checkdb() {
		hikashop_setTitle(JText::_('CHECK_DATABASE'),'config','config');
		$this->toolbar = array('dashboard');

		$databaseHelper = hikashop_get('helper.database');
		$results = $databaseHelper->getCheckResults();
		$this->assignRef('results', $results);
	}

	public function config($tpl = null) {
		JHTML::_('behavior.modal');

		$config =& hikashop_config();
		$this->assignRef('config', $config);

		hikaInput::get()->set('inherit', false);

		hikashop_setTitle(JText::_('HIKA_CONFIGURATION'), 'config', 'config');

		$manage = hikashop_isAllowed($config->get('acl_config_manage','all'));
		$this->assignRef('manage',$manage);

		hikashop_loadJsLib('tooltip');

		$this->toolbar = array(
			array('name' => 'custom', 'icon' => (HIKASHOP_J30) ? 'shield' : 'upload', 'alt' => JText::_('CHECK_DATABASE'), 'task' => 'checkdb', 'check' => false, 'display' => $manage),
			'|',
			array('name' => 'save', 'display' => $manage),
			array('name' => 'apply', 'display' => $manage),
			'close',
			'|',
			array('name' => 'pophelp', 'target' => 'config'),
			'dashboard'
		);

		$this->loadRef(array(
			'encodingType' => 'type.encoding',
			'charsetType' => 'type.charset',
			'editorType' => 'type.editor',
			'cssType' => 'type.css',
			'menusType' => 'type.menus',
			'uploaderType' => 'type.uploader',
			'imageHelper' => 'helper.image',
			'delayType' => 'type.delay',
			'toggleClass' => 'helper.toggle',
			'tabs' => 'helper.tabs',
			'popup' => 'helper.popup',
			'nameboxType' => 'type.namebox',
		));


		$lg = JFactory::getLanguage();
		$lg->load('com_hikashop_config', JPATH_SITE);

		$language = $lg->getTag();
		$styleRemind = 'float:right;margin-right:30px;position:relative;';

		$loadLink = '<a onclick="hikashopHideWarning();return true;" class="modal" rel="{handler: \'iframe\', size: {x: 800, y: 500}}" href="index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=config&amp;task=latest&amp;code='.$language.'">'.JText::_('LOAD_LATEST_LANGUAGE').'</a>';
		if(!file_exists(HIKASHOP_ROOT.'language'.DS.$language.DS.$language.'.com_hikashop.ini')){
			if($config->get('errorlanguagemissing',1)){
				$notremind = '<small style="'.$styleRemind.'">'.$this->toggleClass->delete('hikashop_messages_warning','errorlanguagemissing-0','config',false,JText::_('DONT_REMIND')).'</small>';
				hikashop_display(JText::_('MISSING_LANGUAGE').' '.$loadLink.' '.$notremind,'warning');
			}
		}elseif(version_compare(JText::_('HIKA_LANG_VERSION'),$config->get('version'),'<')){
			if($config->get('errorlanguageupdate',1)){
				$notremind = '<small style="'.$styleRemind.'">'.$this->toggleClass->delete('hikashop_messages_warning','errorlanguageupdate-0','config',false,JText::_('DONT_REMIND')).'</small>';
				hikashop_display(JText::_('UPDATE_LANGUAGE').' '.$loadLink.' '.$notremind,'warning');
			}
		}

		$this->checkPlugins();

		$elements = new stdClass();


		$this->assignRef('elements', $elements);

		$translate = hikashop_get('helper.translation');
		$languages = $translate->getAllLanguages();
		$this->assignRef('languages', $languages);

		$popup_plugins = $this->popup->getPlugins();
		$this->assignRef('popup_plugins', $popup_plugins);

		$db = JFactory::getDBO();
		if(!HIKASHOP_J16) {
			$db->setQuery("SELECT name,published,id FROM `#__plugins` WHERE `folder` = 'hikashop' || ".
				"(`folder` != 'hikashoppayment' AND `folder` != 'hikashopshipping' AND `element` LIKE '%hikashop%') ORDER BY published DESC, ordering ASC");
		} else {
			$db->setQuery("SELECT name,enabled as published,extension_id as id FROM `#__extensions` WHERE (`folder` = 'hikashop' || ".
				"(`folder` != 'hikashoppayment' AND `folder` != 'hikashopshipping' AND `element` LIKE '%hikashop%')) AND type='plugin' ORDER BY enabled DESC, ordering ASC");
		}
		$plugins = $db->loadObjectList();
		$this->assignRef('plugins', $plugins);

		$app = JFactory::getApplication();
		$defaultPanel = $app->getUserStateFromRequest( $this->paramBase.'.default_panel', 'default_panel', 0, 'int' );
		$this->assignRef('default_tab', $defaultPanel);

		$this->initConfigJS();

		$pluginClass = hikashop_get('class.plugins');

		$affiliate_active = false;
		if(hikashop_level(2)) {
			$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		}
		if(empty($plugin)) {
			$plugin = new stdClass();
			$plugin->params = array();
		} else {
			$affiliate_active = true;
			$plugin = $pluginClass->getByName($plugin->type,$plugin->name);
			if(HIKASHOP_J16){
				$query = 'SELECT * FROM '.hikashop_table('extensions',false).' WHERE type=\'plugin\' AND enabled = 1 AND access <> 1 AND folder=\'system\' AND element=\'hikashopaffiliate\'';
				$db->setQuery($query);
				$pluginData = $db->loadObject();
				if(!empty($pluginData)) {
					$app->enqueueMessage(JText::sprintf('PLUGIN_ACCESS_WARNING','('.$pluginData->name.')'),'warning');
				}
			}
		}
		if(empty($plugin->params['partner_key_name'])) {
			$plugin->params['partner_key_name'] = 'partner_id';
		}
		$this->assignRef('affiliate_params', $plugin->params);
		$this->assignRef('affiliate_active', $affiliate_active);

		$this->loadRef(array(
			'joomlaAclType' => 'type.joomla_acl',
			'auto_select' => 'type.select',
			'contact' => 'type.contact',
			'waitlist' => 'type.waitlist',
			'compare' => 'type.compare',
			'csvType' => 'type.csv',
			'csvDecimalType' => 'type.csvdecimal',
			'discountDisplayType' => 'type.discount_display',
			'currency' => 'type.currency',
			'tax' => 'type.tax',
			'tax_zone' => 'type.tax_zone',
			'order_status' => 'type.order_status',
			'button' => 'type.button',
			'paginationType' => 'type.pagination',
			'menu_style' => 'type.menu_style',
			'vat' => 'type.vat',
			'checkout' => 'type.checkout',
			'checkout_workflow' => 'type.checkout_workflow',
			'cart_redirect' => 'type.cart_redirect',
			'multilang' => 'type.multilang',
			'contentType' => 'type.content',
			'layoutType' => 'type.layout',
			'orderdirType' => 'type.orderdir',
			'childdisplayType' => 'type.childdisplay',
			'orderType' => 'type.order',
			'pricetaxType' => 'type.pricetax',
			'colorType' => 'type.color',
			'listType' => 'type.list',
			'itemType' => 'type.item',
			'priceDisplayType' => 'type.pricedisplay',
			'characteristicdisplayType' => 'type.characteristicdisplay',
			'characteristicorderType' => 'type.characteristicorder',
			'quantity' => 'type.quantity',
			'productSyncType' => 'type.productsync',
			'productDisplayType' => 'type.productdisplay',
			'quantityDisplayType' => 'type.quantitydisplay',
			'acltable' => 'type.acltable',
		));

		$this->delayTypeRates =& $this->delayType;
		$this->delayTypeCarts =& $this->delayType;
		$this->delayTypeRetaining =& $this->delayType;
		$this->delayTypeDownloads =& $this->delayType;
		$this->delayTypeAffiliate =& $this->delayType;
		$this->delayTypeOrder =& $this->delayType;
		$this->delayTypeClick =& $this->delayType;


		$zoneClass = hikashop_get('class.zone');
		$zone = $zoneClass->get($config->get('main_tax_zone'));
		$this->assignRef('zone', $zone);

		$default_params = $config->get('default_params',null);
		if(empty($default_params['selectparentlisting'])) {
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'root\' AND category_parent_id=0 LIMIT 1';
			$db->setQuery($query);
			$root = $db->loadResult();
			if(empty($root))
				$app->enqueueMessage('It appears the root category has been deleted. That category shouldn\'t be removed as it is crucial to the proper functioning of the category system. Please click on the "check datbaase" button of the toolbar which will restore it.','error');
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'product\' AND category_parent_id='.(int)$root.' LIMIT 1';
			$db->setQuery($query);
			$default_params['selectparentlisting'] = $db->loadResult();
		}
		$this->assignRef('default_params', $default_params);

		$categoryClass = hikashop_get('class.category');
		$element = $categoryClass->get($default_params['selectparentlisting']);
		$this->assignRef('element', $element);

		$js = null;
		$this->assignRef('js', $js);

		$images = array(
			'icon-48-user.png' => 'header',
			'icon-48-category.png' => 'header',
			'icon-32-save.png' => 'toolbar',
			'icon-32-new.png' => 'toolbar',
			'icon-32-apply.png' => 'toolbar',
			'icon-32-print.png' => 'toolbar',
			'icon-32-edit.png' => 'toolbar',
			'icon-32-help.png' => 'toolbar',
			'icon-32-cancel.png' => 'toolbar',
			'icon-32-back.png' => 'toolbar'
		);
		jimport('joomla.filesystem.file');

		$checkoutlist = array(
			'login' => JText::_('HIKASHOP_CHECKOUT_LOGIN'),
			'address' => JText::_('HIKASHOP_CHECKOUT_ADDRESS'),
			'shipping' => JText::_('HIKASHOP_CHECKOUT_SHIPPING'),
			'payment' => JText::_('HIKASHOP_CHECKOUT_PAYMENT'),
			'coupon' => JText::_('HIKASHOP_CHECKOUT_COUPON'),
			'cart' => JText::_('HIKASHOP_CHECKOUT_CART'),
			'cartstatus' => JText::_('HIKASHOP_CHECKOUT_CART_STATUS'),
			'status' => JText::_('HIKASHOP_CHECKOUT_STATUS'),
			'fields' => JText::_('HIKASHOP_CHECKOUT_FIELDS'),
			'terms' => JText::_('HIKASHOP_CHECKOUT_TERMS')
		);
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$dispatcher = JDispatcher::getInstance();
		$list = array();
		$dispatcher->trigger('onCheckoutStepList', array(&$list));
		if(!empty($list)) {
			foreach($list as $k => $v) {
				if(!isset($checkoutlist[$k]))
					$checkoutlist[$k] = $v;
			}
		}
		$this->assignRef('checkoutlist', $checkoutlist);

		$this->handleImages();

		$address_format = $this->config->getAddressFormat();
		$this->assignRef('address_format', $address_format);
		$address_format_reset = $address_format != $this->config->getAddressFormat(true);
		$this->assignRef('address_format_reset', $address_format_reset);


		$aclcats = array();
		$acltrans = array();
		$aclcats['affiliates'] = array('view','manage','delete');
		$aclcats['badge'] = array('view','manage','delete');
		$aclcats['banner'] = array('view','manage','delete');
		$aclcats['category'] = array('view','manage','delete');
		$aclcats['characteristic'] = array('view','manage','delete');
		$acltrans['characteristic'] = 'characteristics';
		$aclcats['cart'] = array('view','manage','delete');
		$acltrans['cart'] = 'HIKASHOP_CHECKOUT_CART';
		$aclcats['config'] = array('view','manage');
		$acltrans['config'] = 'hika_configuration';
		$aclcats['currency'] = array('view','manage','delete');
		$aclcats['dashboard'] = array('view','manage','delete');
		$acltrans['dashboard'] = 'hikashop_cpanel';
		$aclcats['discount'] = array('view','manage','delete');
		$aclcats['email'] = array('view','manage','delete');
		$aclcats['entry'] = array('view','manage','delete');
		$acltrans['entry'] = 'hikashop_entry';
		$aclcats['field'] = array('view','manage','delete');
		$aclcats['filter'] = array('view','manage','delete');
		$aclcats['forum'] = array('view');
		$aclcats['documentation'] = array('view');
		$acltrans['documentation'] = 'help';
		$aclcats['import'] = array('view');
		$aclcats['limit'] = array('view','manage','delete');
		$aclcats['massaction'] = array('view','manage','delete');
		$aclcats['menus'] = array('view','manage','delete');
		$aclcats['modules'] = array('view','manage','delete');
		$aclcats['order'] = array('view','manage','delete');
		$acltrans['order'] = 'hikashop_order';
		$aclcats['plugins'] = array('view','manage');
		$aclcats['product'] = array('view','manage','delete');
		$aclcats['report'] = array('view','manage', 'delete');
		$aclcats['taxation'] = array('view','manage','delete');
		$aclcats['update_about'] = array('view');
		$aclcats['user'] = array('view','manage','delete');
		$aclcats['view'] = array('view','manage','delete');
		$aclcats['vote'] = array('view','manage','delete');
		$aclcats['warehouse'] = array('view','manage','delete');
		if($this->config->get('product_waitlist'))
			$aclcats['waitlist'] = array('view','manage','delete');
		if($this->config->get('enable_wishlist'))
			$aclcats['wishlist'] = array('view','manage','delete');
		$aclcats['zone'] = array('view','manage','delete');
		$this->assignRef('aclcats', $aclcats);
		$this->assignRef('acltrans', $acltrans);
	}

	protected function checkPlugins() {
		$db = JFactory::getDBO();

		if(!isset($_SESSION['check_anticopy_framing'])) {
			if(!HIKASHOP_J16) {
				$db->setQuery("SELECT id FROM `#__plugins` WHERE `folder` = 'system' AND `element` = 'anticopy' AND `published` = '1' AND params LIKE '%disallow_framing=1%'");
			} else {
				$db->setQuery("SELECT extension_id FROM `#__extensions` WHERE `folder` = 'system' AND `element` = 'anticopy' AND `enabled` = '1' AND params LIKE '%\"disallow_framing\":\"1\"%'");
			}
			$_SESSION['check_anticopy_framing'] = $db->loadResult();
			if(!empty($_SESSION['check_anticopy_framing'])) {
				hikashop_display('The extension AntiCopy is enabled with the "Framing" option set to "Disallow". This will prevent popups to display properly on your frontend. Please disable that option of that plugin via the Joomla plugins manager.','error');
			}
		}

		if(!isset($_SESSION['check_contentprotect_framing'])) {
			if(!HIKASHOP_J16) {
				$db->setQuery("SELECT id FROM `#__plugins` WHERE `folder` = 'system' AND `element` = 'jts_contentprotect' AND `published` = '1' AND params LIKE '%no_iframe=1%'");
			} else {
				$db->setQuery("SELECT extension_id FROM `#__extensions` WHERE `folder` = 'system' AND `element` = 'jts_contentprotect' AND `enabled` = '1' AND params LIKE '%\"no_iframe\":\"1\"%'");
			}
			$_SESSION['check_contentprotect_framing'] = $db->loadResult();
			if(!empty($_SESSION['check_contentprotect_framing'])) {
				hikashop_display('The extension JTS Content Protect is enabled with the "Framing" option set to "Disallow". This will prevent popups to display properly on your frontend. Please disable that option of that plugin via the Joomla plugins manager.','error');
			}
		}

		if(!isset($_SESSION['check_system_user'])) {
			if(!HIKASHOP_J16) {
				$db->setQuery("SELECT id FROM `#__plugins` WHERE `folder` = 'system' AND `element` = 'hikashopuser' AND `published` = '1'");
			} else {
				$db->setQuery("SELECT extension_id FROM `#__extensions` WHERE `folder` = 'system' AND `element` = 'hikashopuser' AND `enabled` = '1'");
			}
			$_SESSION['check_system_user'] = $db->loadResult();
			if(empty($_SESSION['check_system_user'])) {
				hikashop_display('The HikaShop user synchronization plugin has been either removed or disabled from the website. It is a critical part of HikaShop and should not be disabled if you\'re using HikaShop on your website.Please enable that plugin via the Joomla plugins manager and then logout/login from the backend.','error');
			}
		}

		if(!HIKASHOP_J16) {
			$path = rtrim(JPATH_SITE,DS).DS.'plugins'.DS.'hikashop'.DS.'history.php';
		} else {
			$path = rtrim(JPATH_SITE,DS).DS.'plugins'.DS.'hikashop'.DS.'history'.DS.'history.php';
		}
		if(!file_exists($path)) {
	 		$folders = array('* Joomla / Plugins','* Joomla / Plugins / User','* Joomla / Plugins / System','* Joomla / Plugins / Search');
			hikashop_display(JText::_('ERROR_PLUGINS_1').'<br/>'.JText::_('ERROR_PLUGINS_2').'<br/>'.implode('<br/>',$folders).'<br/><a href="index.php?option=com_hikashop&amp;ctrl=update&amp;task=install">'.JText::_('ERROR_PLUGINS_3').'</a>','warning');
		}
	}

	protected function handleImages() {
		if(version_compare(JVERSION,'1.6', '<')) {
			$from = HIKASHOP_ROOT.DS.'images'.DS.'M_images'.DS.'edit.png';
			$to = HIKASHOP_MEDIA.'images'.DS.'icons'.DS.'icon-16-edit.png';
			if(!file_exists($to) && file_exists($from)){
				if(!JFile::copy($from,$to)){
					hikashop_display('Could not copy the file '.$from.' to '.$to.'. Please check the persmissions of the folder '.dirname($to));
				}
			}
			$from = HIKASHOP_ROOT.DS.'images'.DS.'M_images'.DS.'new.png';
			$to = HIKASHOP_MEDIA.'images'.DS.'icons'.DS.'icon-16-new.png';
			if(!file_exists($to) && file_exists($from)){
				if(!JFile::copy($from,$to)){
					hikashop_display('Could not copy the file '.$from.' to '.$to.'. Please check the persmissions of the folder '.dirname($to));
				}
			}
			$from = HIKASHOP_ROOT.DS.'images'.DS.'M_images'.DS.'con_info.png';
			$to = HIKASHOP_MEDIA.'images'.DS.'icons'.DS.'icon-16-info.png';
			if(!file_exists($to) && file_exists($from)){
				if(!JFile::copy($from,$to)){
					hikashop_display('Could not copy the file '.$from.' to '.$to.'. Please check the persmissions of the folder '.dirname($to));
				}
			}
			$from = rtrim(JPATH_ADMINISTRATOR,DS).DS.'templates'.DS.'khepri'.DS.'images'.DS.'menu'.DS.'icon-16-user.png';
			$to = HIKASHOP_MEDIA.'images'.DS.'icons'.DS.'icon-16-levels.png';
			if(!file_exists($to) && file_exists($from)){
				if(!JFile::copy($from,$to)){
					hikashop_display('Could not copy the file '.$from.' to '.$to.'. Please check the persmissions of the folder '.dirname($to));
				}
			}
		} else {
			$images['icon-16-edit.png'] = 'menu';
			$images['icon-16-new.png'] = 'menu';
			$images['icon-16-levels.png'] = 'menu';
			$images['icon-16-info.png'] = 'menu';
		}
		foreach($images as $oneImage => $folder) {
			$to = HIKASHOP_MEDIA.'images'.DS.'icons'.DS.$oneImage;
			if(!HIKASHOP_J16) {
				$from = rtrim(JPATH_ADMINISTRATOR,DS).DS.'templates'.DS.'khepri'.DS.'images'.DS.$folder.DS.$oneImage;
			} else {
				$from = rtrim(JPATH_ADMINISTRATOR,DS).DS.'templates'.DS.'bluestork'.DS.'images'.DS.$folder.DS.$oneImage;
			}
			if(!file_exists($to) && file_exists($from)) {
				if(!JFile::copy($from, $to)) {
					hikashop_display('Could not copy the file '.$from.' to '.$to.'. Please check the persmissions of the folder '.dirname($to));
				}
			}
		}
	}

	protected function initConfigJS() {
		$id = (HIKASHOP_J30) ? 'config_force_sslurl' : 'config[force_ssl]url';

		$js = '
function hikashopHideWarning() {
	var el = document.getElementById("hikashop_messages_warning");
	if(el) el.style.display = "none";
	el = document.getElementById("alert-warning");
	if(el) el.style.display = "none";
}
function jSelectArticle(id, title, object) {
	document.getElementById("affiliate_terms").value = id;
	hikashop.closeBox();
}
function setSefVisible(value) {
	var d = document, value = parseInt(value);
	value = (value == 1) ? "" : "none";
	d.getElementById("sef_cat_name").style.display = value;
	d.getElementById("sef_prod_name").style.display = value;
	d.getElementById("sef_checkout_name").style.display = value;
}
function displaySslField(){
	var el = document.getElementById("force_ssl_url");
	if(!el) return;
	var chk = document.getElementById("'.$id.'");
	if(!chk) return;
	el.style.display = (chk.checked == true) ? "" : "none";
}
function displayPaymentChange(value) {
	var el = document.getElementById("hikashop_payment_change_row");
	if(!el) return;
	el.style.display = (value == "1") ? "" : "none";
}

function registrationAvailable(value, checked) {
	var d = document,
		displayMethod = d.getElementById("config[display_method]1").checked,
		normal = d.getElementById("config_simplified_registration_normal"),
		simple = d.getElementById("config_simplified_registration_simple"),
		simple_pwd = d.getElementById("config_simplified_registration_simple_pwd"),
		nbChecked = 0;

	changeDefaultRegistrationViewType();
	if(value == 2 && checked == false)
		return false;
	if(value == 2 && (normal.checked == true || simple.checked == true || simple_pwd.checked == true))
		return false;

	var fctEnable = function(el) { el.disabled = false; el.parentNode.className = ""; }
	fctEnable(normal);
	fctEnable(simple);
	fctEnable(simple_pwd);

	if(normal.checked) { nbChecked++; }
	if(simple.checked) { nbChecked++; }
	if(simple_pwd.checked) { nbChecked++; }

	if(value == 2 && checked == true && nbChecked > 1) {
		normal.checked = false;
		simple.checked = false;
		simple_pwd.checked = false;
	}

	if(displayMethod != 1)
		return;

	var fctDisable = function(el) { el.disabled = true; el.parentNode.className = "labelDisabled"; el.checked = false; }

	if(value == 0 && checked == true) {
		fctDisable(simple);
		fctDisable(simple_pwd);
	} else if(value == 1 && checked == true) {
		fctDisable(normal);
		fctDisable(simple_pwd);
	} else if(value == 3 && checked == true){
		fctDisable(normal);
		fctDisable(simple);
	}
}
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

	public function language() {
		$code = hikaInput::get()->getString('code');
		if(empty($code)){
			hikashop_display('Code not specified','error');
			return;
		}
		$file = new stdClass();
		$file->name = $code;
		$path = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_hikashop.ini';
		$file->path = $path;
		jimport('joomla.filesystem.file');
		$showLatest = true;
		$loadLatest = false;
		if(JFile::exists($path)){
			$file->content = JFile::read($path);
			if(empty($file->content)){
				hikashop_display('File not found : '.$path,'error');
			}
		}else{
			$loadLatest = true;
			hikashop_display(JText::_('HIKASHOP_LOAD_ENGLISH_1').'<br/>'.JText::_('LOAD_ENGLISH_2').'<br/>'.JText::_('LOAD_ENGLISH_3'),'info');
			$file->content = JFile::read(JLanguage::getLanguagePath(JPATH_ROOT).DS.'en-GB'.DS.'en-GB.com_hikashop.ini');
		}
		if($loadLatest OR hikaInput::get()->getString('task') == 'latest'){
			$doc = JFactory::getDocument();
			$doc->addScript(HIKASHOP_UPDATEURL.'languageload&code='.hikaInput::get()->getString('code'));
			$showLatest = false;
		}elseif(hikaInput::get()->getString('task') == 'save') $showLatest = false;
		$override_content = '';
		$override_path = JLanguage::getLanguagePath(JPATH_ROOT).DS.'overrides'.DS.$code.'.override.ini';
		if(JFile::exists($override_path)){
			$override_content = JFile::read($override_path);
		}
		$this->assignRef('override_content',$override_content);
		$this->assignRef('showLatest',$showLatest);
		$this->assignRef('file',$file);
	}

	public function getDoc($key) {
		$namekey = 'HK_CONFIG_' . strtoupper(trim($key));
		$ret = JText::_($namekey);
		if($ret == $namekey) {
			return '';
		}
		return $ret;
	}

	public function docTip($key) {
		$ret = $this->getDoc($key);
		if(empty($ret))
			return '';
		return 	' data-toggle="hk-tooltip" data-title="'.htmlspecialchars($ret, ENT_COMPAT, 'UTF-8').'"';
	}

	public function css() {
		$file = hikaInput::get()->getCmd('file');
		if(empty($file)) {
			$var = hikaInput::get()->getCmd('var');
			if(in_array($var, array('frontend','backend', 'style'))) {
				$file = $var . '_default';
			}
		}

		if(!preg_match('#^([-_A-Za-z0-9]*)_([-_A-Za-z0-9]*)$#i', $file, $result)) {
			hikashop_display('Could not load the file '.$file.' properly');
			exit;
		}

		if(empty($result[2])) {
			hikashop_display('Please select a CSS file if you want to edit one. The "None" value cannot be edited');
			exit;
		}

		$type = $result[1];
		$fileName = $result[2];
		$content = hikaInput::get()->getString('csscontent');
		if(empty($content))
			$content = file_get_contents(HIKASHOP_MEDIA.'css'.DS.$type.'_'.$fileName.'.css');

		if(in_array($fileName, array('default','old'))) {
			$fileName = 'custom';
			$i = 1;
			while(file_exists(HIKASHOP_MEDIA.'css'.DS.$type.'_'.$fileName.'.css')) {
				$fileName = 'custom'.$i;
				$i++;
			}
		}

		$this->assignRef('content', $content);
		$this->assignRef('fileName', $fileName);
		$this->assignRef('type', $type);
		$editor = hikashop_get('helper.editor');
		$this->assignRef('editor', $editor);
	}

	public function share(){
		$file = new stdClass();
		$file->name = hikaInput::get()->getString('code');
		$this->assignRef('file',$file);
	}

	public function leftmenu($name, $data) {
		$this->menuname = $name;
		$this->menudata = $data;
		$this->setLayout('leftmenu');
		return $this->loadTemplate();
	}
}
