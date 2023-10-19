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
		hikashop_setTitle(JText::_('CHECK_DATABASE'),'server','config');
		$this->toolbar = array('dashboard');

		$databaseHelper = hikashop_get('helper.database');
		$results = $databaseHelper->getCheckResults();
		$this->assignRef('results', $results);
	}

	public function config($tpl = null) {
		$config =& hikashop_config();
		$this->assignRef('config', $config);

		hikaInput::get()->set('inherit', false);
		if($config->get('website') != HIKASHOP_LIVE) {
			$updateHelper = hikashop_get('helper.update');
			$updateHelper->addUpdateSite();
		}

		hikashop_setTitle(JText::_('HIKA_CONFIGURATION'), 'wrench', 'config');

		$manage = hikashop_isAllowed($config->get('acl_config_manage','all'));
		$this->assignRef('manage',$manage);

		hikashop_loadJsLib('tooltip');

		$this->toolbar = array(
			array('name' => 'custom', 'icon' => (HIKASHOP_J30) ? 'shield' : 'upload', 'alt' => JText::_('CHECK_DATABASE'), 'task' => 'checkdb', 'check' => false, 'display' => $manage),
			'|',
			array('name' => 'group', 'buttons' => array(
				array('name' => 'apply', 'display' => $manage),
				array('name' => 'save', 'display' => $manage),
			)),
			'close',
			'|',
			array('name' => 'pophelp', 'target' => 'config'),
			'dashboard'
		);

		$this->loadRef(array(
			'encodingType' => 'type.encoding',
			'charsetType' => 'type.charset',
			'contentparserType' => 'type.contentparser',
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

		$checkout_menu_id = $config->get('checkout_itemid');
		$this->menusType->load($checkout_menu_id);
		if(!isset($this->menusType->menus[$checkout_menu_id])) {
			$config->set('checkout_itemid', 0);
			$save = array('checkout_itemid'=>0);
			$config->save($save);
		} elseif (strpos($this->menusType->menus[$checkout_menu_id]->link, 'index.php?option=com_hikashop')===false) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('THE_MENU_ITEM_SELECTED_NEEDS_TO_BE_A_HIKASHOP_TYPE_MENU'), 'error');
		}

		$lg = JFactory::getLanguage();
		$lg->load('com_hikashop_config', JPATH_SITE);

		$language = $lg->getTag();
		$styleRemind = 'float:right;margin-right:30px;position:relative;';

		$loadLink = '<a onclick="hikashopHideWarning();return true;" href="index.php?option=com_hikashop&amp;ctrl=config&amp;task=latest&amp;code='.$language.'">'.JText::_('LOAD_LATEST_LANGUAGE').'</a>';
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

		if(hikashop_level(1)) {
			$cronTypeReport = hikashop_get('type.cronreport');
			$elements->cron_sendreport = $cronTypeReport->display('config[cron_sendreport]', $config->get('cron_sendreport', 2));

			$cronTypeReportSave = hikashop_get('type.cronreportsave');
			$elements->cron_savereport = $cronTypeReportSave->display('config[cron_savereport]', $config->get('cron_savereport', 0));

			$elements->deleteReport = $this->popup->display(
				'<button type="button" class="btn" onclick="return false">'.JText::_('REPORT_DELETE').'</button>',
				JText::_('REPORT_DELETE'),
				hikashop_completeLink('config&task=cleanreport',true),
				'deleteReport',
				760, 480, '', '', 'link'
			);
			$elements->seeReport = $this->popup->display(
				'<button type="button" class="btn" onclick="return false">'.JText::_('REPORT_SEE').'</button>',
				JText::_('REPORT_SEE'),
				hikashop_completeLink('config&task=seereport',true),
				'seeReport',
				760, 480, '', '', 'link'
			);
			if(hikashop_level(2)) {
				$elements->editReportEmail = $this->popup->display(
					'<button type="button" class="btn" onclick="return false">'.JText::_('REPORT_EDIT').'</button>',
					'REPORT_EDIT',
					hikashop_completeLink('email&task=edit&mail_name=cron_report',true),
					'editReportEmail',
					760, 480, '', '', 'link'
				);
			}

			$elements->cron_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=cron';
			$item = $config->get('itemid');
			if(!empty($item))
				$elements->cron_url .= '&Itemid='.$item;

			$elements->cron_edit = $this->popup->display(
				'<button type="button" class="btn" onclick="return false">'.JText::_('CREATE_CRON').'</button>',
				'CREATE_CRON',
				'https://www.hikashop.com/index.php?option=com_updateme&ctrl=launcher&task=edit&cronurl='.urlencode($elements->cron_url),
				'cron_edit',
				760, 480, '', '', 'link'
			);
		}

		$this->assignRef('elements', $elements);

		$translate = hikashop_get('helper.translation');
		$languages = $translate->getAllLanguages();
		$this->assignRef('languages', $languages);

		$popup_plugins = $this->popup->getPlugins();
		$this->assignRef('popup_plugins', $popup_plugins);

		$db = JFactory::getDBO();
		$db->setQuery("SELECT name,enabled as published,extension_id as id FROM `#__extensions` WHERE (`folder` = 'hikashop' || ".
			"(`folder` != 'hikashoppayment' AND `folder` != 'hikashopshipping' AND `element` LIKE '%hikashop%')) AND type='plugin' ORDER BY enabled DESC, ordering ASC");
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
		if(!empty($plugin)) {
			$affiliate_active = true;
			$plugin = $pluginClass->getByName($plugin->type,$plugin->name);

			$query = 'SELECT * FROM '.hikashop_table('extensions',false).' WHERE type=\'plugin\' AND enabled = 1 AND access <> 1 AND folder=\'system\' AND element=\'hikashopaffiliate\'';
			$db->setQuery($query);
			$pluginData = $db->loadObject();
			if(!empty($pluginData)) {
				$app->enqueueMessage(JText::sprintf('PLUGIN_ACCESS_WARNING','('.$pluginData->name.')'),'warning');
			}
		}

		if(empty($plugin)) {
			$plugin = new stdClass();
		}
		if(!isset($plugin->params) || !is_array($plugin->params)) {
			$plugin->params = array();
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

		if(hikashop_level(1)) {
			$this->loadRef(array(
				'display_method' => 'type.display_method',
				'default_registration_view' => 'type.default_registration_view',
				'registration' => 'type.registration',
			));
		}
		if(hikashop_level(2)) {
			$filterButtonType = hikashop_get('type.filter_button_position');
			$this->assignRef('filterButtonType', $filterButtonType);
		}

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
		$app = JFactory::getApplication();
		$list = array();
		$app->triggerEvent('onCheckoutStepList', array(&$list));
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
		$aclcats['orderstatus'] = array('view','manage');
		$aclcats['plugins'] = array('view','manage');
		$aclcats['product'] = array('view','manage','delete', 'customize');
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

		$this->acl_translations = array();
		foreach($this->aclcats as $category => $actions){ 
			$trans='';

			if(!empty($this->acltrans[$category])){
				$trans = JText::_(strtoupper($this->acltrans[$category]));
				if($trans == strtoupper($this->acltrans[$category])){
				$trans = '';
				}
			}
			if(empty($trans)) $trans = JText::_('HIKA_'.strtoupper($category));
			if($trans == 'HIKA_'.strtoupper($category)) $trans = JText::_(strtoupper($category));

			$this->acl_translations[$category] = $trans;
		}
		uasort($this->acl_translations, function ($a, $b) {
            return strnatcmp($a,$b);
        });
		$doc = JFactory::getDocument();
		$this->assignRef('doc', $doc);
	}

	function cmp($a, $b) {
		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}

	protected function checkPlugins() {
		$db = JFactory::getDBO();

		if(!isset($_SESSION['check_anticopy_framing'])) {
			$db->setQuery("SELECT extension_id FROM `#__extensions` WHERE `folder` = 'system' AND `element` = 'anticopy' AND `enabled` = '1' AND params LIKE '%\"disallow_framing\":\"1\"%'");
			$_SESSION['check_anticopy_framing'] = $db->loadResult();
			if(!empty($_SESSION['check_anticopy_framing'])) {
				hikashop_display('The extension AntiCopy is enabled with the "Framing" option set to "Disallow". This will prevent popups to display properly on your frontend. Please disable that option of that plugin via the Joomla plugins manager.','error');
			}
		}

		if(!isset($_SESSION['check_contentprotect_framing'])) {
			$db->setQuery("SELECT extension_id FROM `#__extensions` WHERE `folder` = 'system' AND `element` = 'jts_contentprotect' AND `enabled` = '1' AND params LIKE '%\"no_iframe\":\"1\"%'");
			$_SESSION['check_contentprotect_framing'] = $db->loadResult();
			if(!empty($_SESSION['check_contentprotect_framing'])) {
				hikashop_display('The extension JTS Content Protect is enabled with the "Framing" option set to "Disallow". This will prevent popups to display properly on your frontend. Please disable that option of that plugin via the Joomla plugins manager.','error');
			}
		}

		if(!isset($_SESSION['check_system_user'])) {
			$db->setQuery("SELECT extension_id FROM `#__extensions` WHERE `folder` = 'system' AND `element` = 'hikashopuser' AND `enabled` = '1'");
			$_SESSION['check_system_user'] = $db->loadResult();
			if(empty($_SESSION['check_system_user'])) {
				hikashop_display('The HikaShop user synchronization plugin has been either removed or disabled from the website. It is a critical part of HikaShop and should not be disabled if you\'re using HikaShop on your website.Please enable that plugin via the Joomla plugins manager and then logout/login from the backend.','error');
			}
		}

		$db->setQuery("SELECT payment_id FROM `#__hikashop_payment` WHERE `payment_type` = 'paypal' AND `payment_published` = '1'");
		$check_paypal = (int)$db->loadResult();
		if(!empty($check_paypal) && $check_paypal > 0) {
			hikashop_display(JText::_('YOUR_PAYPAL_PAYMENT_METHOD_IS_OBSOLETE_PLEASE_SWITCH_TO_PAYPAL_CHECKOUT'),'error');
		}

		$path = rtrim(JPATH_SITE,DS).DS.'plugins'.DS.'hikashop'.DS.'history'.DS.'history.php';
		if(!file_exists($path)) {
	 		$folders = array('* Joomla / Plugins','* Joomla / Plugins / User','* Joomla / Plugins / System','* Joomla / Plugins / Search');
			hikashop_display(JText::_('ERROR_PLUGINS_1').'<br/>'.JText::_('ERROR_PLUGINS_2').'<br/>'.implode('<br/>',$folders).'<br/><a href="index.php?option=com_hikashop&amp;ctrl=update&amp;task=install">'.JText::_('ERROR_PLUGINS_3').'</a>','warning');
		}
	}

	protected function handleImages() {
		$images = array(
			'icon-16-edit.png' => 'menu',
			'icon-16-new.png' => 'menu',
			'icon-16-levels.png' => 'menu',
			'icon-16-info.png' => 'menu',
		);
		foreach($images as $oneImage => $folder) {
			$to = HIKASHOP_MEDIA.'images'.DS.'icons'.DS.$oneImage;
			$from = rtrim(JPATH_ADMINISTRATOR,DS).DS.'templates'.DS.'bluestork'.DS.'images'.DS.$folder.DS.$oneImage;
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
function displayGroupoptionsChange(value) {
	var el = document.getElementById("hikashop_groupoptions_change_row");
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
		$path = hikashop_getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_hikashop.ini';
		$file->path = $path;
		jimport('joomla.filesystem.file');
		$showLatest = true;
		$loadLatest = false;
		if(JFile::exists($path)){
			$file->content = file_get_contents($path);
			if(empty($file->content)){
				hikashop_display('File not found : '.$path,'error');
			}
		}else{
			$loadLatest = true;
			hikashop_display(JText::_('HIKASHOP_LOAD_ENGLISH_1').'<br/>'.JText::_('LOAD_ENGLISH_2').'<br/>'.JText::_('LOAD_ENGLISH_3'),'info');
			$file->content = file_get_contents(hikashop_getLanguagePath(JPATH_ROOT).DS.'en-GB'.DS.'en-GB.com_hikashop.ini');
		}
		if($loadLatest OR hikaInput::get()->getString('task') == 'latest'){
			$doc = JFactory::getDocument();
			$doc->addScript(HIKASHOP_UPDATEURL.'languageload&code='.hikaInput::get()->getString('code'));
			$showLatest = false;
		}elseif(hikaInput::get()->getString('task') == 'save') $showLatest = false;
		$override_content = '';
		$override_path = hikashop_getLanguagePath(JPATH_ROOT).DS.'overrides'.DS.$code.'.override.ini';
		if(JFile::exists($override_path)){
			$override_content = file_get_contents($override_path);
		}

		$this->assignRef('override_content',$override_content);
		$this->assignRef('showLatest',$showLatest);
		$this->assignRef('file',$file);

		$config = hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_config_manage','all'));
		hikashop_setTitle(JText::_('HIKA_FILE').' : '.$file->name, 'flag', 'config&task='.hikaInput::get()->getString('task').'&code='.$file->name);

		$this->toolbar = array(
			array('name' => 'custom', 'icon' => 'share-alt', 'alt' => JText::_('SHARE'), 'task' => 'share', 'check' => false, 'display' => $manage),
			array('name' => 'custom', 'icon' => 'apply', 'alt' => JText::_('HIKA_SAVE'), 'task' => 'savelanguage', 'check' => false, 'display' => $manage),
			array('name' => 'custom', 'icon' => 'cancel', 'alt' => JText::_('HIKA_CLOSE'), 'task' => 'config', 'check' => false),
		);


		if(!empty($this->showLatest)){
			array_unshift($this->toolbar, array('name' => 'custom', 'icon' => 'import', 'alt' => JText::_('LOAD_LATEST_LANGUAGE'), 'task' => 'latest', 'check' => false, 'display' => $manage));
		}
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
		$config = hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_config_manage','all'));
		hikashop_setTitle(JText::_('SHARE').' : '.$file->name, 'flag', 'config&task='.hikaInput::get()->getString('task').'&code='.$file->name);

		$this->toolbar = array(
			array('name' => 'custom', 'icon' => 'share-alt', 'alt' => JText::_('SHARE'), 'task' => 'send', 'check' => false, 'display' => $manage),
			array('name' => 'custom', 'icon' => 'cancel', 'alt' => JText::_('HIKA_CLOSE'), 'task' => 'config', 'check' => false),
		);

	}

	public function leftmenu($name, $data) {
		$this->menuname = $name;
		$this->menudata = $data;
		$this->setLayout('leftmenu');
		return $this->loadTemplate();
	}
}
