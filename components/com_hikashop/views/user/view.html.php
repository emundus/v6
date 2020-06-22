<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class userViewUser extends HikaShopView {

	public $extraFields=array();
	public $requiredFields = array();
	public $validMessages = array();
	public $triggerView = array('hikashop');

	public function display($tpl = null) {
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	public function after_register() {
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
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		JPluginHelper::importPlugin('hikashopshipping');
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

		$simplified_reg = $config->get('simplified_registration', 1);
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

		$userClass = hikashop_get('class.user');
		$privacy = $userClass->getPrivacyConsentSettings();
		$this->options = array();
		if($privacy) {
			$this->options['privacy'] = true;
			$this->options['privacy_id'] = $privacy['id'];
			$this->options['privacy_text'] = $privacy['text'];
		}


	}

	public function downloads() {
		$user = hikashop_loadUser(true);
		if(empty($user))
			return false;
	}
}
