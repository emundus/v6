<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class userViewUser extends HikaShopView {

	public $extraFields=array();
	public $requiredFields = array();
	public $validMessages = array();
	public $triggerView = array('hikashop');

	public function display($tpl = null, $params = null) {
		$function = $this->getLayout();
		$this->params =& $params;
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	public function after_register() {
		$this->user = hikashop_loadUser(true);
	}

	public function cpanel() {
		$config =& hikashop_config();
		$this->assignRef('config', $config);

		global $Itemid;
		$this->url_itemid = '';
		if(!empty($Itemid))
			$this->url_itemid = '&Itemid=' . $Itemid;

		hikashop_loadJsLib('tooltip');

		$buttons = array(
			'address' => array(
				'link' => hikashop_completeLink('address'.$this->url_itemid),
				'level' => 0,
				'image' => 'address',
				'text' => JText::_('ADDRESSES'),
				'description' => JText::_('MANAGE_ADDRESSES'),
				'fontawesome' => '<i class="fas fa-map-marker-alt fa-stack-2x"></i>'
			),
			'order' => array(
				'link' => hikashop_completeLink('order'.$this->url_itemid),
				'level' => 0,
				'image' => 'order',
				'text' => JText::_('ORDERS'),
				'description' => JText::_('VIEW_ORDERS'),
				'fontawesome' => ''.
					'<i class="fas fa-align-justify fa-stack-2x"></i>'.
					'<i class="fas fa-circle fa-stack-1x fa-inverse" style="top:28%; left:33%;"></i>'.
					'<i class="fas fa-check fa-stack-1x hk-icon-dark" style="top:28%; left:33%;"></i>'
			),
		);
		if(hikashop_level(1)) {
			if($config->get('enable_multicart'))
				$buttons['cart'] = array(
					'link' => hikashop_completeLink('cart&task=listing'.$this->url_itemid),
					'level' => 0,
					'image' => 'cart',
					'text' => JText::_('CARTS'),
					'description' => JText::_('DISPLAY_THE_CARTS'),
					'fontawesome' => '<i class="fas fa-shopping-cart fa-stack-2x"></i>'
				);
			else
				$buttons['cart'] = array(
					'link' => hikashop_completeLink('cart&task=show'.$this->url_itemid),
					'level' => 0,
					'image' => 'cart',
					'text' => JText::_('CARTS'),
					'description' => JText::_('DISPLAY_THE_CART'),
					'fontawesome' => ''.
						'<i class="fas fa-shopping-cart fa-stack-2x"></i>'
				);

			if($config->get('enable_wishlist')) {
				if($config->get('enable_multiwishlist', 1))
					$buttons['wishlist'] = array(
						'link' => hikashop_completeLink('cart&task=listing&cart_type=wishlist'.$this->url_itemid),
						'level' => 0,
						'image' => 'wishlist',
						'text' => JText::_('WISHLISTS'),
						'description' => JText::_('DISPLAY_THE_WISHLISTS'),
						'fontawesome' => ''.
							'<i class="fas fa-list-ul fa-stack-2x"></i>'.
							'<i class="fas fa-star fa-stack-1x hk-icon-dark" style="top:-38%;left:-40%;font-size:18px"></i>'
					);
				else
					$buttons['wishlist'] = array(
						'link' => hikashop_completeLink('cart&task=show&cart_type=wishlist'.$this->url_itemid),
						'level' => 0,
						'image' => 'wishlist',
						'text' => JText::_('WISHLISTS'),
						'description' => JText::_('DISPLAY_THE_WISHLIST'),
						'fontawesome' => ''.
							'<i class="fas fa-list-ul fa-stack-2x"></i>'.
							'<i class="fas fa-star fa-stack-1x hk-icon-dark" style="top:-38%;left:-40%;font-size:18px"></i>'
					);
			}

			if($config->get('enable_customer_downloadlist') && hikashop_level(1))
				$buttons['download'] = array(
					'link' => hikashop_completeLink('user&task=downloads'.$this->url_itemid),
					'level' => 0,
					'image' => 'downloads',
					'text' => JText::_('DOWNLOADS'),
					'description' => JText::_('DISPLAY_THE_DOWNLOADS'),
					'fontawesome' => ''.
						'<i class="far fa-circle fa-stack-2x"></i>'.
						'<i class="fas fa-arrow-down fa-stack-1x"></i>'
				);
		}
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$app->triggerEvent('onUserAccountDisplay', array(&$buttons));

		$this->assignRef('buttons',$buttons);

		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$show_page_heading = true;
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$show_page_heading = $params->get('show_page_heading');
		}
		if(is_null($show_page_heading)) {
			$com_menus = JComponentHelper::getParams('com_menus');
			$show_page_heading = $com_menus->get('show_page_heading');
		}
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			if($show_page_heading)
				$this->title = $params->get('page_heading');
			$title = $params->get('page_title');
			if(empty($title))
				$title = $menu->title;
			hikashop_setPageTitle($title);

			$robots = $params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc = JFactory::getDocument();
				$doc->setMetadata('robots', $robots);
			}
		} else {
			if($show_page_heading)
				$this->title = JText::_('CUSTOMER_ACCOUNT');
			hikashop_setPageTitle('CUSTOMER_ACCOUNT');
		}

		$pathway = $app->getPathway();
		$items = $pathway->getPathway();
		if(!count($items))
			$pathway->addItem(JText::_('CUSTOMER_ACCOUNT'), hikashop_completeLink('user'));

		$legacy = (int)$this->config->get('cpanel_legacy', false);
		if($legacy)
			return;
		$this->cpanel_orders();
	}

	protected function cpanel_orders() {
		$db = JFactory::getDBO();
		$user_id = hikashop_loadUser();

		$this->loadRef(array(
			'orderClass' => 'class.order',
			'currencyClass' => 'class.currency',
			'imageHelper' => 'helper.image',
			'popupHelper' => 'helper.popup',
			'dropdownHelper' => 'helper.dropdown'
		));

		$nb_display_option = (int)$this->config->get('order_number_acc', 3);
		if($nb_display_option <= 0)
			$nb_display_option = 3;

		$filters = array('`order_type` = \'sale\'', '`order_user_id` = '. (int)$user_id);
		$status = $this->config->get('orders_listing_default_status','');
		if(!empty($status)) {
			$filters[] = 'order_status = '.$db->Quote($status);
		}
		$filters = implode(' AND ', $filters);
		$query = 'SELECT `order_id` FROM ' .  hikashop_table('order') . ' WHERE ' . $filters . '  ORDER BY `order_created` DESC';
		$db->setQuery($query, 0, $nb_display_option);
		$last_order_ids = $db->loadColumn();

		if(hikashop_level(1) && $this->config->get('allow_payment_button', 1)) {
			$unpaid_statuses = explode(',', $this->config->get('order_unpaid_statuses', 'created'));

			$payment_change = $this->config->get('allow_payment_change', 1);
			$this->assignRef('payment_change', $payment_change);

			$pluginsPayment = hikashop_get('type.plugins');
			$pluginsPayment->type = 'payment';
			$this->assignRef('paymentPluginsType', $pluginsPayment);

			$paymentClass = hikashop_get('class.payment');
			$this->assignRef('paymentClass', $paymentClass);
		}

		$orders = array();
		$product_ids = array();

		foreach($last_order_ids as $order_id) {
			$orders[$order_id] = $this->orderClass->loadFullOrder($order_id, true);
			foreach($orders[$order_id]->products as $product) {
				$product_ids[] = (int)$product->product_id;
			}
		}

		$this->products = array();
		if(count($product_ids)) {
			$db->setQuery('SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',', $product_ids). ')');
			$this->products = $db->loadObjectList('product_id');
			$parent_ids = array();
			$productClass = hikashop_get('class.product');
			foreach($this->products as $k => $product) {
				if(!empty($product->product_parent_id))
					$parent_ids[$product->product_id] = (int)$product->product_parent_id;
				else {
					$productClass->addAlias($this->products[$k]);
				}
			}
			if(count($parent_ids)) {
				$db->setQuery('SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',', $parent_ids). ')');
				$parents = $db->loadObjectList('product_id');
				foreach($parent_ids as $variant_id => $parent_id){
					if(!isset($parents[$parent_id]))
						continue;
					$productClass->addAlias($parents[$parent_id]);
					$this->products[$variant_id]->product_alias = $parents[$parent_id]->product_alias;
					$this->products[$variant_id]->product_canonical = $parents[$parent_id]->product_canonical;
				}
			}
		}

		$this->action_column = false;

		if(hikashop_level(1) && $this->config->get('allow_payment_button', 1)) {
			$unpaid_statuses = explode(',', $this->config->get('order_unpaid_statuses', 'created'));
			foreach($orders as $key =>$order) {
				if(in_array($order->order_status, $unpaid_statuses)) {
					$orders[$key]->show_payment_button = true;
					$this->action_column = true;
				}
			}
			unset($order);

			$payment_change = $this->config->get('allow_payment_change', 1);
			$this->assignRef('payment_change', $payment_change);

			$pluginsPayment = hikashop_get('type.plugins');
			$pluginsPayment->type = 'payment';
			$this->assignRef('paymentPluginsType', $pluginsPayment);

			$paymentClass = hikashop_get('class.payment');
			$this->assignRef('paymentClass', $paymentClass);
		}

		$cancellable_order_status = explode(',', trim($this->config->get('cancellable_order_status', ''), ', '));
		if(!empty($cancellable_order_status)) {
			foreach($orders as $key =>$order) {
				if(in_array($order->order_status, $cancellable_order_status)) {
					$orders[$key]->show_cancel_button = true;
					$this->action_column = true;
				}
			}
			unset($order);
		}

		$print_statuses = explode(',', trim($this->config->get('print_invoice_statuses', 'confirmed,shipped,refunded'), ', '));
		if(hikashop_level(1) && !empty($orders)) {
			foreach($orders as &$order) {
				if(in_array($order->order_status, $print_statuses)) {
					$order->show_print_button = true;
					$this->action_column = true;
				}
			}
			unset($order);
		}

		$contact_statuses = explode(',', trim($this->config->get('contact_button_orders', 'created,confirmed,shipped,refunded,pending,cancelled'), ', '));
		if(hikashop_level(1) && !empty($orders)) {
			foreach($orders as &$order) {
				if(in_array($order->order_status, $contact_statuses)) {
					$order->show_contact_button = true;
					$this->action_column = true;
				}
			}
			unset($order);
		}


		if($this->config->get('allow_reorder', 0)) {
			$this->action_column = true;
		}

		$this->cpanel_data = new stdClass();
		$this->cpanel_data->cpanel_title = JText::_('YOUR_LAST_ORDERS');
		$this->cpanel_data->cpanel_order_image = true;
		$this->cpanel_data->cpanel_orders = $orders;
	}

	public function guest_form(){
		hikashop_setPageTitle('HIKA_REGISTRATION');
		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid)){
			$url_itemid = '&Itemid=' . $Itemid;
		}
		$this->assignRef('url_itemid', $url_itemid);

		$user = (object)@$_SESSION['hikashop_guest_data'];

		if(empty($user)){
			$user = new stdClass();
			$user->username = '';
			$user->name = '';
		}

		$this->assignRef('user', $user);

		$config = hikashop_config();
		$this->assignRef('config', $config);
		$this->_privacy_consent();
	}

	public function form() {
		$app = JFactory::getApplication();

		$this->registration();

		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$show_page_heading = true;
		$params = null;
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$show_page_heading = $params->get('show_page_heading');
		}
		if(is_null($show_page_heading)) {
			$com_menus = JComponentHelper::getParams('com_menus');
			if(!empty($com_menus))
				$show_page_heading = $com_menus->get('show_page_heading');
		}
		if(!empty($menu) && method_exists($menu, 'getParams') && $menu->link == 'index.php?option=com_hikashop&view=user&layout=form') {
			if($show_page_heading)
				$this->title = $params->get('page_heading');
			$title = $params->get('page_title');
			if(empty($title))
				$title = $menu->title;
			hikashop_setPageTitle($title);

			$robots = $params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc = JFactory::getDocument();
				$doc->setMetadata('robots', $robots);
			}

		} else {
			if($show_page_heading)
				$this->title = JText::_('HIKA_REGISTRATION');
			hikashop_setPageTitle('HIKA_REGISTRATION');

		}
	}

	public function registration() {

		$js ='
function hikashopSubmitForm(form, action) {
	var d = document,
		button = d.getElementById(\'login_view_action\'),
		 currentForm = d.forms[form];

	if(!currentForm)
		return false;

	if(form == "hikashop_registration_form") {
		hikashopSubmitFormRegister(form,button,currentForm);
		return false;
	}

	if(form != "hikashop_checkout_form")
		return false;

	if(action && action == "login") {
		hikashopSubmitFormLog(form,button,currentForm);
		return false;
	}
	if(action && action == "register") {
		hikashopSubmitFormRegister(form,button,currentForm);
		return false;
	}

	var registrationMethod = currentForm.elements["data[register][registration_method]"];
	if (registrationMethod)
	{
		if (registrationMethod[0].id == "data[register][registration_method]login" && registrationMethod[0].checked)
			hikashopSubmitFormLog(form,button,currentForm);
		else
			hikashopSubmitFormRegister(form,button,currentForm);

		return false;
	}

	var usernameValue = "", passwdValue = "", el = null;
	el = d.getElementById("username");
	if(el) usernameValue = el.value;

	el = d.getElementById("passwd");
	if(el) passwdValue = el.value;

	var registeremailValue = "", registeremailconfValue = "", firstnameValue = "", lastnameValue = "";
	el = d.getElementById("register_email");
	if(el) registeremailValue = el.value;
	el = d.getElementById("register_email_confirm");
	if(el) registeremailconfValue = el.value;

	el = d.getElementById("address_firstname");
	if(el) firstnameValue = el.value;
	el = d.getElementById("address_lastname");
	if(el) lastnameValue = el.value;

	if (usernameValue != "" && passwdValue != "") {
		hikashopSubmitFormLog(form,button,currentForm);
	} else if ((usernameValue != "" ||  passwdValue != "") && (registeremailValue == "" && registeremailconfValue == "" && firstnameValue == "" && lastnameValue == "")) {
		hikashopSubmitFormLog(form,button,currentForm);
	} else {
		hikashopSubmitFormRegister(form,button,currentForm);
	}

	return false;
}

function hikashopSubmitFormRegister(form, button, currentForm) {
	if( hikashopCheckChangeForm("register",form) && hikashopCheckChangeForm("user",form) && hikashopCheckChangeForm("address",form) ) {
		if(button)
			button.value="register";
		currentForm.submit();
	}
}
function hikashopSubmitFormLog(form,button,currentForm) {
	if(button)
		button.value="login";
	currentForm.submit();
}

var hkKeyPress = function(e) {
	var keyCode = (window.event) ? e.which : e.keyCode;
	if (keyCode != 13)
		return true;

	if (e.srcElement)  elem = e.srcElement;
	else if (e.target) elem = e.target;

	if( elem.name == "username" || elem.name == "passwd" ){
		var button = document.getElementById("login_view_action"),
		currentForm = document.forms["hikashop_checkout_form"];
		if(currentForm && button){
			hikashopSubmitFormLog("hikashop_checkout_form",button,currentForm);
			if (e.stopPropagation) {
				e.stopPropagation();
				e.preventDefault();
			}
			e.returnValue = false;
			return false;
		}
	} else {
	}
	return true;
};

if(document.addEventListener)
	document.addEventListener("keypress", hkKeyPress);
else
	document.attachEvent("onkeypress", hkKeyPress);
';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("\n<!--".$js."//-->\n");

		global $Itemid;
		$url_itemid = (!empty($Itemid) ? '&Itemid='.$Itemid : '');
		$this->assignRef('url_itemid', $url_itemid);

		$mainUser = JFactory::getUser();
		$data = @$_SESSION['hikashop_main_user_data'];
		if(!empty($data)){
			foreach($data as $key => $val){
				$mainUser->$key = $val;
			}
		}

		$this->assignRef('mainUser', $mainUser);

		$lang = JFactory::getLanguage();
		$lang->load('com_user', JPATH_SITE);

		$user_id = hikashop_loadUser();

		$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
		if(version_compare($jversion, '3.4.0', '>='))
			JHTML::_('behavior.formvalidator');
		else
			JHTML::_('behavior.formvalidation');

		$user = @$_SESSION['hikashop_user_data'];
		$address = @$_SESSION['hikashop_address_data'];
		if(!empty($_SESSION['hikashop_billing_address_data']))
			$address = $_SESSION['hikashop_billing_address_data'];
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);
		$fieldsClass->skipAddressName = true;

		$extraFields['user'] = $fieldsClass->getFields('frontcomp', $user, 'user');

		$config =& hikashop_config();
		$this->assignRef('config', $config);

		$this->assignRef('extraFields', $extraFields);
		$this->assignRef('user', $user);

		$simplified_reg = 0;
		if(hikashop_level(1))
			$simplified_reg = $config->get('simplified_registration', 0);
		$this->assignRef('simplified_registration', $simplified_reg);

		$display_method = $config->get('display_method', 0);
		if(!hikashop_level(1))
			$display_method = 0;
		$this->assignRef('display_method', $display_method);

		$null = array();
		$fieldsClass->addJS($null, $null, $null);
		$fieldsClass->jsToggle($this->extraFields['user'], $user, 0);

		$values = array('user' => $user);

		if($config->get('address_on_registration', 1)) {
			$extraFields['address'] = $fieldsClass->getFields('frontcomp', $address, 'address');
			$this->assignRef('address', $address);
			$fieldsClass->jsToggle($this->extraFields['address'], $address, 0);
			$values['address'] = $address;
		}

		$fieldsClass->checkFieldsForJS($this->extraFields,$this->requiredFields,$this->validMessages,$values);

		$main = array('name','username', 'email','password','password2');
		if($simplified_reg && $simplified_reg != 3 && $simplified_reg != 0) {
			$main = array('email');
		} else if ($simplified_reg == 3) {
			$main = array('email','password','password2');
		}

		if($config->get('show_email_confirmation_field')) {
			$i = 0;
			foreach($main as $k) {
				$i++;
				if($k == 'email')
					array_splice($main, $i, 0, 'email_confirm');
			}
		}

		foreach($main as $field) {
			$this->requiredFields['register'][] = $field;

			if($field=='name') $field = 'HIKA_USER_NAME';
			if($field=='username') $field = 'HIKA_USERNAME';
			if($field=='email') $field = 'HIKA_EMAIL';
			if($field=='email_confirm') $field = 'HIKA_EMAIL_CONFIRM';
			if($field=='password') $field = 'HIKA_PASSWORD';
			if($field=='password2') $field = 'HIKA_VERIFY_PASSWORD';

			$this->validMessages['register'][] = addslashes(JText::sprintf('FIELD_VALID', $fieldsClass->trans($field)));
		}

		$fieldsClass->addJS($this->requiredFields, $this->validMessages, array('register', 'user', 'address'));
		jimport('joomla.html.parameter');

		$params = new HikaParameter('');
		$this->assignRef('params',$params);

		$cartHelper = hikashop_get('helper.cart');
		$this->assignRef('cartClass', $cartHelper);

		$affiliate = $config->get( 'affiliate_registration_default', 0);
		if($affiliate) {
			$affiliate = 'checked="checked"';
		} else {
			$affiliate = '';
		}
		$this->assignRef('affiliate_checked', $affiliate);
		$this->_privacy_consent();
	}
	private function _privacy_consent() {

		$userClass = hikashop_get('class.user');
		$privacy = $userClass->getPrivacyConsentSettings();
		$this->options = array();
		if($privacy) {
			$this->options['privacy'] = true;
			$this->options['privacy_type'] = $privacy['type'];
			$this->options['privacy_id'] = $privacy['id'];
			$this->options['privacy_url'] = $privacy['url'];
			$this->options['privacy_text'] = $privacy['text'];
		}
	}

	public function downloads() {
		hikashop_loadJslib('tooltip');
		$user = hikashop_loadUser(true);
		if(empty($user))
			return false;
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();

		$order_statuses = explode(',', $config->get('order_status_for_download', 'shipped,confirmed'));
		foreach($order_statuses as $k => $o) {
			$order_statuses[$k] = $db->Quote( trim($o) );
		}

		$download_time_limit = (int)$config->get('download_time_limit', 0);
		$this->assignRef('download_time_limit', $download_time_limit);

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();

		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.'.filter_order', 'filter_order', 'max_order_created', 'cmd');
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest($paramBase.'.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.'.search', 'search', '', 'string');
		$pageInfo->search = HikaStringHelper::strtolower($pageInfo->search);
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$oldValue = $app->getUserState($paramBase.'.list_limit');
		$searchMap = array(
			'op.order_product_name',
			'f.file_name'
		);
		$order = '';
		if(!empty($pageInfo->filter->order->value)) {
			if($pageInfo->filter->order->value == 'f.file_name')
				$order = ' ORDER BY f.file_name '.$pageInfo->filter->order->dir.', f.file_path '.$pageInfo->filter->order->dir;
			else
				$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$filters = array(
			'o.order_type = \'sale\'',
			'o.order_status IN ('.implode(',', $order_statuses).')',
			'f.file_ref_id > 0',
			'f.file_type = \'file\'',
			'o.order_user_id = ' . $user->user_id,
		);
		if(!empty($pageInfo->search)) {
			$searchVal = '\'%'.hikashop_getEscaped(HikaStringHelper::strtolower(trim($pageInfo->search)),true).'%\'';
			$filter = '('.implode(' LIKE '.$searchVal.' OR ',$searchMap).' LIKE '.$searchVal.')';
			$filters[] =  $filter;
		}
		$filters = implode(' AND ',$filters);

		if(empty($oldValue)) {
			$oldValue = $app->getCfg('list_limit');
		}

		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if($oldValue!=$pageInfo->limit->value) {
			$pageInfo->limit->start = 0;
			$app->setUserState($paramBase.'.limitstart',0);
		}
		$select = 'o.order_id, o.order_created, p.*, f.*, op.* ';
		$selectSum = ', MIN(o.order_created) as min_order_created, MAX(o.order_created) as max_order_created, SUM(op.order_product_quantity) as file_quantity ';
		$selectUniq = ', IF( REPLACE(LEFT(f.file_path, 1) , \'#\', \'@\') = \'@\', CONCAT(f.file_id, \'@\', o.order_id), f.file_id ) as uniq_id';
		$query = ' FROM '.hikashop_table('order').' AS o ' .
			' INNER JOIN '.hikashop_table('order_product').' AS op ON op.order_id = o.order_id ' .
			' INNER JOIN '.hikashop_table('product').' AS p ON op.product_id = p.product_id ' .
			' INNER JOIN '.hikashop_table('file').' AS f ON (op.product_id = f.file_ref_id OR p.product_parent_id = f.file_ref_id) ' .
			' WHERE ' . $filters;
		$groupBy = ' GROUP BY uniq_id ';

		$sql_query = 'SELECT '. $select . $selectSum . $selectUniq . $query . $groupBy . $order;
		$db->setQuery($sql_query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);
		$downloadData = $db->loadObjectList('uniq_id');

		$db->setQuery('SELECT COUNT(*) as all_results_count FROM (SELECT f.file_id ' . $selectUniq . $query . $groupBy . ') AS all_results');

		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();

		$file_ids = array();
		$order_ids = array();

		$productClass = hikashop_get('class.product');
		foreach($downloadData as $k => $data) {
			if((int)$data->order_id > 0)
				$order_ids[(int)$data->order_id] = (int)$data->order_id;
			$downloadData[$k]->download_total = 0;
			$downloadData[$k]->downloads = array();
			$downloadData[$k]->orders = array();
			if(!empty($data->product_id) && !empty($data->product_parent_id) && $data->product_type == 'variant') {
				$variant_query = 'SELECT * FROM '.hikashop_table('variant').' AS v '.
					' LEFT JOIN '.hikashop_table('characteristic') .' AS c ON v.variant_characteristic_id = c.characteristic_id '.
					' WHERE v.variant_product_id = '.(int)$data->product_id.' ORDER BY v.ordering';
				$db->setQuery($variant_query);
				$downloadData[$k]->characteristics = $db->loadObjectList();

				$parentProduct = $productClass->get((int)$data->product_parent_id);
				$productClass->checkVariant($downloadData[$k], $parentProduct);
			}
			$productClass->addAlias($downloadData[$k]);
			if(strpos($k,'@') === false)
				$file_ids[] = $k;
		}

		if(!empty($pageInfo->search)) {
			$downloadData = hikashop_search($pageInfo->search,$downloadData,array('order_id', 'alias', 'product_canonical'));
		}
		$pageInfo->elements->page = count($downloadData);


		if(!empty($file_ids)) {
			$db->setQuery('SELECT ' . $select . $query . ' AND f.file_id IN (' . implode(',', $file_ids) . ')');
			$orders = $db->loadObjectList();
			foreach($orders as $o) {
				if(isset($downloadData[$o->file_id])) {
					$downloadData[$o->file_id]->orders[(int)$o->order_id] = $o;
					$downloadData[$o->file_id]->orders[(int)$o->order_id]->file_qty = 0;
					$downloadData[$o->file_id]->orders[(int)$o->order_id]->download_total = 0;
				}
				$order_ids[(int)$o->order_id] = (int)$o->order_id;
			}
		}

		if(!empty($order_ids)) {
			$db->setQuery('SELECT * FROM ' . hikashop_table('download') . ' WHERE order_id IN (' . implode(',', $order_ids) . ')');
			$downloads = $db->loadObjectList();
			foreach($downloads as $download) {
				$uniq_id = $download->file_id . '@' . $download->order_id;
				if(isset($downloadData[$uniq_id])) {
					$downloadData[$uniq_id]->download_total += (int)$download->download_number;
					$downloadData[$uniq_id]->downloads[$download->file_pos] = $download;
				} else if(isset($downloadData[$download->file_id])) {
					$downloadData[$download->file_id]->download_total += (int)$download->download_number;
					if(isset($downloadData[$download->file_id]->orders[$download->order_id])) {
						$downloadData[$download->file_id]->orders[$download->order_id]->file_qty++;
						$downloadData[$download->file_id]->orders[$download->order_id]->download_total += (int)$download->download_number;
					}
				}
			}
		}

		jimport('joomla.html.pagination');
		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$pagination->hikaSuffix = '';

		$this->assignRef('pagination', $pagination);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('downloadData', $downloadData);



		global $Itemid;
		$this->Itemid = $Itemid;

		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$this->toolbar = array();
		$show_page_heading = true;
		$params = null;
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$show_page_heading = $params->get('show_page_heading');
		}
		if(is_null($show_page_heading)) {
			$com_menus = JComponentHelper::getParams('com_menus');
			if(!empty($com_menus))
				$show_page_heading = $com_menus->get('show_page_heading');
		}
		if(!empty($menu) && method_exists($menu, 'getParams') && $menu->link == 'index.php?option=com_hikashop&view=user&layout=downloads') {
			if($show_page_heading)
				$this->title = $params->get('page_heading');
			hikashop_setPageTitle($menu->title);
		} else {
			if($show_page_heading)
				$this->title = JText::_('DOWNLOADS');
			hikashop_setPageTitle('DOWNLOADS');
			$pathway = $app->getPathway();
			$pathway->addItem(JText::_('DOWNLOADS'), hikashop_completeLink('user&task=downloads&Itemid='.$Itemid));

			$this->toolbar = array(
				'back' => array(
					'icon' => 'back',
					'name' => JText::_('HIKA_BACK'),
					'url' => hikashop_completeLink('user&task=cpanel&Itemid='.$Itemid),
					'fa' => array('html' => '<i class="fas fa-arrow-circle-left"></i>')
				)
			);
		}
	}
}
