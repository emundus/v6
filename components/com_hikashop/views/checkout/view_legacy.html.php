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
class CheckoutViewCheckoutLegacy extends hikashopView {
	var $ctrl= 'checkout';
	var $nameListing = 'CHECKOUT';
	var $nameForm = 'CHECKOUT';
	var $icon = 'checkout';
	var $extraFields=array();
	var $requiredFields = array();
	var $validMessages = array();
	var $triggerView = array('hikashop','hikashopshipping','hikashoppayment');

	function display($tpl = null, $params = array()) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();

		jimport('joomla.html.parameter');
		$params = new HikaParameter('');
		$this->assignRef('params', $params);

		$conf =& hikashop_config();
		$checkout = trim($conf->get('checkout','login_address_shipping_payment_confirm_coupon_cart_status,end'));
		$this->steps = explode(',',$checkout);

		if(method_exists($this, $function))
			$this->$function();

		if(hikaInput::get()->getInt('popup') && empty($_COOKIE['popup']) && hikaInput::get()->getVar('tmpl') != 'component') {
			$cartHelper = hikashop_get('helper.cart');
			$this->init();
			$cartHelper->getJS($this->params->get('url'));
			$doc = JFactory::getDocument();
			$js = '
window.hikashop.ready(function(){ SqueezeBox.fromElement(\'hikashop_notice_box_trigger_link\',{parse: \'rel\'}); });
';
			$doc->addScriptDeclaration($js);
		}

		$this->assignRef('config', $conf);
		parent::display($tpl);
	}

	function notice() {
		global $Itemid;
		$url_itemid = (!empty($Itemid) ? '&Itemid='.$Itemid : '');
		$this->assignRef('url_itemid', $url_itemid);

		jimport('joomla.html.parameter');

		$cartHelper = hikashop_get('helper.cart');
		$this->assignRef('cartClass', $cartHelper);

		$config =& hikashop_config();
		$this->assignRef('config', $config);
	}

	function step() {
		$module = hikashop_get('helper.module');
		$module->initialize($this);

		$config =& hikashop_config();
		$this->display_checkout_bar = $config->get('display_checkout_bar', 2);
		$this->continueShopping = $config->get('continue_shopping');
		$this->continueShopping = hikashop_translate($this->continueShopping);

		$step = hikaInput::get()->getInt('step',0);
		if(!isset($this->steps[$step])) {
			$step=0;
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();

		$display = trim($this->steps[$step]);
		$layouts = explode('_',$display);
		$obj =& $this;
		foreach($layouts as $layout) {
			$layout = trim($layout);
			if(method_exists($this, $layout)) {
				$this->$layout();
			} else {
				$app->triggerEvent('onInitCheckoutStep', array($layout, &$obj));
			}
		}
		$this->assignRef('steps',$this->steps);
		$this->assignRef('step',$step);
		$this->assignRef('layouts',$layouts);

		$js = '
function isSelected(radiovar){
	if(radiovar.checked){
		return true;
	}
	for(var a=0; a < radiovar.length; a++){
		if(radiovar[a].checked && radiovar[a].value.length>0) return true;
	}
	return false;
}

function hikashopCheckMethods() {
	var varform =  document["hikashop_checkout_form"];

	if(typeof varform.elements["hikashop_payment"] != "undefined" && !isSelected(varform.elements[\'hikashop_payment\'])) {
		alert("'. JText::_('SELECT_PAYMENT',true).'");
		return false;
	}

	if(typeof varform.elements["hikashop_shippings"] != "undefined") {
		var shippings = varform.elements["hikashop_shippings"];
		if(shippings) {
			shippings = shippings.value.split(";");
			if(shippings.length > 1) {
				for(var i = 0; i < shippings.length; i++) {
					if(!varform.elements["hikashop_shipping_" + shippings[i] ] || !isSelected(varform.elements["hikashop_shipping_" + shippings[i] ])) {
						alert("'. JText::_('SELECT_SHIPPING',true).'");
						return false;
					}
				}
			}else{
				if(typeof varform.elements["hikashop_shipping"] != "undefined" && !isSelected(varform.elements[\'hikashop_shipping\'])) {
					alert("'. JText::_('SELECT_SHIPPING',true).'");
					return false;
				}
			}
		}
	}

	return true;
}';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
		$this->assignRef('doc', $doc);

		$app = JFactory::getApplication();
		$this->assignRef('app', $app);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid)) {
			$url_itemid = '&Itemid='.$Itemid;
		}
		$this->assignRef('url_itemid', $url_itemid);

		$this->assignRef('continueShopping',$this->continueShopping);
		$this->assignRef('display_checkout_bar',$this->display_checkout_bar);
		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);

		hikashop_setPageTitle('CHECKOUT');
	}

	function cartstatus(){
		return $this->cart();
	}

	function cart() {
		$cart = $this->initCart();

		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$config = hikashop_config();

		$cartClass = hikashop_get('class.cart');
		$cartHelper = hikashop_get('helper.cart');
		$productClass = hikashop_get('class.product');
		$currencyClass = hikashop_get('class.currency');
		$imageHelper = hikashop_get('helper.image');

		$this->assignRef('cart', $cartHelper);
		$this->assignRef('productClass', $productClass);
		$this->assignRef('currencyHelper', $currencyClass);
		$this->assignRef('image', $imageHelper);

		$this->init();

		$cartHelper->cartCount(true);
		$cartHelper->getJS($this->params->get('url'));

		if(!empty($cart->total->prices[0]->price_currency_id) && $cart->total->prices[0]->price_currency_id != hikashop_getCurrency()) {
			$app = JFactory::getApplication();
			$app->enqueueMessage( JText::_('CURRENCY_NOT_ACCEPTED_FOR_PAYMENT'));
		}

		$paymentType = $cartClass->checkSubscription($cart);
		$this->assignRef('paymentType', $paymentType);

		$ids = array();
		foreach($cart->products as $row) {
			$ids[] = $row->product_id;
		}
		$productClass->getProducts($ids);

		$this->assignRef('fullCart', $cart);
		$this->assignRef('coupon', $cart->coupon);
		$this->assignRef('shipping', $cart->shipping);
		$this->assignRef('payment', $cart->payment);

		$this->assignRef('additional', $cart->additional);

		$this->assignRef('total', $cart->total);
		$this->assignRef('rows', $cart->products);

		$this->params->set('show_delete', $config->get('checkout_cart_delete', 1));
		$this->params->set('show_cart_image', $config->get('show_cart_image'));

		global $Itemid;
		$checkout_itemid = $config->get('checkout_itemid');
		if(!empty($checkout_itemid)) {
			$Itemid = $checkout_itemid;
		}
		$this->url_itemid = '';
		if(!empty($Itemid)) {
			$this->url_itemid = '&Itemid='.$Itemid;
		}

		if(hikashop_level(2)) {
			$fieldsClass = hikashop_get('class.field');
			$this->assignRef('fieldsClass',$fieldsClass);
			$null = null;
			$this->extraFields['item'] = $fieldsClass->getFields('display:checkout=1',$null,'item');
			$this->assignRef('extraFields',$this->extraFields);

			foreach($this->rows as $i => &$row) {
				if(!isset($cart->cart_products[$i]))
					continue;
				$item = $cart->cart_products[$i];
				foreach($this->extraFields['item'] as $field) {
					$namekey = $field->field_namekey;
					if(empty($item->$namekey) || !strlen($item->$namekey))
						continue;
					$row->$namekey = $item->$namekey;
				}
			}
			unset($row);
		}
	}

	function &initCart() {
		static $done = false;
		if(!empty($done))
			return $done;

		$cartClass = hikashop_get('class.cart');
		$done = $cartClass->loadFullCart(true);
		$app = JFactory::getApplication();
		if(empty($done)) {
			$config =& hikashop_config();
			$redirect_url = $config->get('redirect_url_when_cart_is_empty');
			$redirect_url = hikashop_translate($redirect_url);
			if(!preg_match('#^https?://#',$redirect_url)) $redirect_url = JURI::base().ltrim($redirect_url,'/');
			$app->enqueueMessage( JText::_('CART_EMPTY'));
			$app->redirect( JRoute::_($redirect_url,false));
			return true;
		}
		$shipping = (!empty($done->usable_methods->shipping) || !empty($done->package['weight']['value']));

		$config =& hikashop_config();
		$this->params->set('price_with_tax',$config->get('price_with_tax'));
		$this->has_shipping = $shipping || $config->get('force_shipping');
		$this->assignRef('has_shipping', $this->has_shipping);
		$this->assignRef('full_total', $done->full_total);
		$this->assignRef('full_cart', $done);

		return $done;
	}

	function init() {
		$url = $this->params->get('url');
		if(empty($url)){
			$url = hikashop_currentURL();
		}
		$this->params->set('url', urlencode($url));
	}

	function coupon() {
		$cart = $this->initCart();

		$js = '
function hikashopCheckCoupon(id){
	var el = document.getElementById(id);
	if(!el) return false;
	if(el.value == "") {
		el.className = "hikashop_red_border";
	} else {
		el.form.submit();
	}
	return false;
}
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$coupon_error_message = hikaInput::get()->getVar('coupon_error_message', '');
		if(!empty($coupon_error_message)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage( $coupon_error_message, 'notice');
		}
		if(isset($cart->coupon))
			$this->assignRef('coupon', $cart->coupon);
	}

	function login() {
		$mainUser = JFactory::getUser();
		if(empty($mainUser->id)){
			$data = @$_SESSION['hikashop_main_user_data'];
			if(!empty($data)){
				foreach($data as $key => $val){
					$mainUser->$key = $val;
				}
			}
		}

		$this->assignRef('mainUser',$mainUser);
		$lang = JFactory::getLanguage();
		$lang->load('com_user',JPATH_SITE);
		$user_id = hikashop_loadUser();
		$identified = $user_id ? true : false;
		$this->assignRef('identified',$identified);

		$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
		if(version_compare($jversion, '3.4.0', '>='))
			JHTML::_('behavior.formvalidator');
		else
			JHTML::_('behavior.formvalidation');

		$user = @$_SESSION['hikashop_user_data'];
		$address = @$_SESSION['hikashop_address_data'];
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->skipAddressName=true;

		$this->extraFields['user'] = $fieldsClass->getFields('frontcomp',$user,'user');
		$this->extraFields['address'] = $fieldsClass->getFields('frontcomp',$address,'address');
		$this->assignRef('extraFields',$this->extraFields);
		$this->assignRef('user',$user);
		$this->assignRef('address',$address);

		$config =& hikashop_config();
		$simplified_reg = $config->get('simplified_registration',1);
		$this->assignRef('simplified_registration',$simplified_reg);
		$display_method = $config->get('display_method', 0);
		if(!hikashop_level(1)) $display_method = 0;
		$this->assignRef('display_method',$display_method);

		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($this->extraFields['user'],$user,0);
		$fieldsClass->jsToggle($this->extraFields['address'],$address,0);

		$values = array('address'=>$address,'user'=>$user);
		$fieldsClass->checkFieldsForJS($this->extraFields,$this->requiredFields,$this->validMessages,$values);

		$main = array('name','username','email','password','password2');
		if($simplified_reg){
			$main = array('email');
		}
		foreach($main as $field){
			$this->requiredFields['register'][] = $field;
			$this->validMessages['register'][] = addslashes(JText::sprintf('FIELD_VALID',$fieldsClass->trans($field)));
		}
		$fieldsClass->addJS($this->requiredFields,$this->validMessages,array('register','user','address'));

		$js = '
function displayRegistration(el) {
	if(!el) return;
	var value = el.value,
	checked = el.checked,
	name = document.getElementById("hikashop_registration_name_line"),
	username = document.getElementById("hikashop_registration_username_line"),
	pwd = document.getElementById("hikashop_registration_password_line"),
	pwd2 = document.getElementById("hikashop_registration_password2_line"),
	registration_div = document.getElementById("hikashop_checkout_registration"),
	login_div = document.getElementById("hikashop_checkout_login_form");

	if(value=="login" && checked==true) {
		if(login_div) login_div.className="";
		if(registration_div) registration_div.className="hikashop_hidden_checkout";
	} else if((value==0 || value==1 || value==3) && checked==true) {
		if(login_div) login_div.className="hikashop_hidden_checkout";
		if(registration_div) registration_div.className="";
		document.getElementById("hika_registration_type").innerHTML="'.JText::_('HIKA_REGISTRATION',true).'";
		document.getElementById("hikashop_register_form_button").value="'.JText::_('HIKA_REGISTER',true).'";
		if(value==0)
		{
			if(name) name.style.display="";
			if(username) username.style.display="";
			if(pwd) pwd.style.display="";
			if(pwd2) pwd2.style.display="";
		} else if(value==1) {
			if(name) name.style.display="none";
			if(username) username.style.display="none";
			if(pwd) pwd.style.display="none";
			if(pwd2) pwd2.style.display="none";
		} else if(value==3) {
			if(pwd) pwd.style.display="";
			if(pwd2) pwd2.style.display="";
		}
	} else if(value==2 && checked==true) {
		if(login_div) login_div.className="hikashop_hidden_checkout";
		if(registration_div) registration_div.className="";
		document.getElementById("hika_registration_type").innerHTML="'.JText::_('GUEST',true).'";
		document.getElementById("hikashop_register_form_button").value="'.JText::_('HIKA_NEXT',true).'";

		if(name) name.style.display="none";
		if(username) username.style.display="none";
		if(pwd) pwd.style.display="none";
		if(pwd2) pwd2.style.display="none";
	}
}
';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
	}

	function activate() {
	}

	function activate_page() {
	}

	function fields() {
		if(!hikashop_level(2))
			return;

		$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
		if(version_compare($jversion, '3.4.0', '>='))
			JHTML::_('behavior.formvalidator');
		else
			JHTML::_('behavior.formvalidation');

		$app = JFactory::getApplication();
		$order = $app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields',null);

		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$cart = $this->initCart();
		$order->products =& $cart->products;
		$this->extraFields['order'] = $fieldsClass->getFields('frontcomp',$order,'order');
		$this->assignRef('extraFields',$this->extraFields);

		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($this->extraFields['order'],$order,0);

		$this->assignRef('order',$order);

		$values = array('order'=>$order);
		$fieldsClass->checkFieldsForJS($this->extraFields,$this->requiredFields,$this->validMessages,$values);
		$fieldsClass->addJS($this->requiredFields,$this->validMessages,array('order'));
	}

	function state() {
		$database	= JFactory::getDBO();
		$namekey = hikaInput::get()->getCmd('namekey','');
		if(!headers_sent()){
			header('Content-Type:text/html; charset=utf-8');
		}
		if(!empty($namekey)){
			$field_namekey = hikaInput::get()->getString('field_namekey', '');
			if(empty($field_namekey))
				$field_namekey = 'address_state';

			$field_id = hikaInput::get()->getString('field_id', '');
			if(empty($field_id))
				$field_id = 'address_state';

			$field_type = hikaInput::get()->getString('field_type', '');
			if(empty($field_type))
				$field_type = 'address';

			$query = 'SELECT * FROM '.hikashop_table('field').' WHERE field_namekey = '.$database->Quote($field_namekey);
			$database->setQuery($query,0,1);
			$field = $database->loadObject();
			$countryType = hikashop_get('type.country');
			echo $countryType->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type,'', $field->field_options);
		} else {
			echo '<span class="state_no_country">'.JText::_('PLEASE_SELECT_COUNTRY_FIRST').'</span>';
		}
		exit;
	}

	function address() {
		$app = JFactory::getApplication();
		$addresses = array();
		$fields = null;
		$user_id = hikashop_loadUser();

		if($user_id){
			$addressClass = hikashop_get('class.address');
			$addresses = $addressClass->loadUserAddresses($user_id);
			if(!empty($addresses)){
				$addressClass->loadZone($addresses);
				$fields =& $addressClass->fields;
			}
		}

		$cart = $this->initCart();
		if(!$this->has_shipping) {
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_address', null);
		}

		$this->assignRef('fields', $fields);
		$this->assignRef('addresses', $addresses);

		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$identified = (bool)$user_id;
		$this->assignRef('identified', $identified);

		$config = hikashop_config();
		$address_selector = (int)$config->get('checkout_address_selector', 0);
		if(empty($address_selector))
			JHTML::_('behavior.modal');

		$billing_address = (int)$cart->cart_billing_address_id;
		$shipping_address = (int)$cart->cart_shipping_address_ids;

		$this->assignRef('shipping_address', $shipping_address);
		$this->assignRef('billing_address', $billing_address);

		$currentShipping = array();
		if(!empty($cart->shipping)) {
			foreach($cart->shipping as $shipping) {
				$method = $shipping->shipping_type;
				$currentShipping[] = hikashop_import('hikashopshipping', $method);
			}
		}
		$this->assignRef('currentShipping', $currentShipping);

		$auto = '';
		if($config->get('auto_submit_methods',1)) {
			$auto = ' document.forms[\'hikashop_checkout_form\'].submit();';
		}

		$js = "
function hikashopEditAddress(obj,val,new_address){
	var same_address = document.getElementById('same_address');
	if(val && same_address && (new_address && same_address.checked || !new_address && !same_address.checked)){
		var nextChar = '?';
		if(obj.href.indexOf('?')!='-1'){ nextChar='&'; }
		obj.href+=nextChar+'makenew=1';
	}
	window.hikashop.openBox(obj,obj.href);
	return false;
}
function hikashopSameAddress(value){
	var shipdiv = document.getElementById('hikashop_checkout_shipping_div');
	if(shipdiv){
		if(!value){
			shipdiv.style.display='';
		}else{
			shipdiv.style.display='none';".$auto."
		}
	}
	return true;
}";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
	}

	function shipping() {
		$app = JFactory::getApplication();

		$order =& $this->initCart();
		$shippingClass = hikashop_get('class.shipping');
		$usable_rates =& $shippingClass->getShippings($order);

		$imageHelper = hikashop_get('helper.image');
		$this->assignRef('imageHelper', $imageHelper);

		$config =& hikashop_config();
		$this->params->set('price_with_tax',$config->get('price_with_tax'));

		if($this->params->get('show_original_price','-1') == '-1') {
			$defaultParams = $config->get('default_params');
			$this->params->set('show_original_price',$defaultParams['show_original_price']);
		}
		if(empty($usable_rates)) {
			$user_id = hikashop_loadUser(false);
			if(!empty($user_id) && !$shippingClass->displayErrors() && $this->has_shipping) {
				$app->enqueueMessage(JText::_('NO_SHIPPING_METHOD_FOUND'));
			}
		} else {

			$shipping_groups = $shippingClass->getShippingGroups($order, $usable_rates);
			$this->assignRef('shipping_groups', $shipping_groups);

			$warehouse_order = 0;
			$config =& hikashop_config();
			$force_shipping = $config->get('force_shipping');
			foreach($shipping_groups as $shipping_group){
				$warehouse_order++;
				if(empty($shipping_group->products) || !empty($shipping_group->shippings))
					continue;

				if($force_shipping){
					if(!empty($shipping_group->name))
						$app->enqueueMessage(JText::sprintf('NO_SHIPPING_METHOD_FOUND_FOR_WAREHOUSE',$shipping_group->name));
					else
						$app->enqueueMessage(JText::sprintf('NO_SHIPPING_METHOD_FOUND_FOR_WAREHOUSE',$warehouse_order));
					continue;
				}

				foreach($shipping_group->products as $group_product){
					if(isset($group_product->product_weight) && $group_product->product_weight > 0){
						if(!empty($shipping_group->name))
							$app->enqueueMessage(JText::sprintf('NO_SHIPPING_METHOD_FOUND_FOR_WAREHOUSE',$shipping_group->name));
						else
							$app->enqueueMessage(JText::sprintf('NO_SHIPPING_METHOD_FOUND_FOR_WAREHOUSE',$warehouse_order));
						continue;
					}
				}
			}
			$currencyClass = hikashop_get('class.currency');

			$currencyClass->processShippings($usable_rates,$order);

			$shipping_method = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_method');
			$shipping_id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id');

			$config =& hikashop_config();
			$auto_select_default = $config->get('auto_select_default', 2);
			if($auto_select_default == 1 && count($usable_rates) > 1)
				$auto_select_default = 0;

			if($auto_select_default && empty($shipping_id) && count($usable_rates)) {
				$rates = array();
				$shipping_id = array();
				$shipping_method = array();
				foreach($shipping_groups as $key => $shipping_group) {
					foreach($usable_rates as $rate) {
						if(in_array($rate->shipping_id, $shipping_group->shippings)) {
							$rates[] = $rate;
							$shipping_id[] = $rate->shipping_id.'@'.$key;
							$shipping_method[] = $rate->shipping_type.'@'.$key;

							break;
						}
					}
				}

				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', $rates);
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', $shipping_id);
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', $shipping_method);

				$order->shipping = $rates;

				$currencyClass->processShippings($order->shipping,$order);

				$order->full_total =& $currencyClass->addShipping($order->shipping, $order->full_total);
			}

			if(empty($shipping_id))
				$shipping_id = array();
			if(!is_array($shipping_id))
				$shipping_id = array($shipping_id);

			if(empty($shipping_method))
				$shipping_method = array();
			if(!is_array($shipping_method))
				$shipping_method = array($shipping_method);

			$this->assignRef('shipping_messages', $shippingClass->errors);
			$this->assignRef('currencyHelper', $currencyClass);
			$this->assignRef('rates', $usable_rates);
			$this->assignRef('orderInfos', $order);
			$this->assignRef('shipping_method', $shipping_method);
			$this->assignRef('shipping_id', $shipping_id);
		}

		$this->_getImagesName('shipping');
	}

	function payment() {
		$order = $this->initCart();

		$this->assignRef('orderInfos', $order);
		if(!isset($order->full_total->prices[0]->price_value_with_tax) || bccomp($order->full_total->prices[0]->price_value_with_tax, 0, 5) == 0) {
			return true;
		}

		$paymentClass = hikashop_get('class.payment');
		$usable_methods = $paymentClass->getPayments($order);

		$app = JFactory::getApplication();

		$payment_id = $order->cart_payment_id;
		$payment_method = @$order->payment->payment_type;

		$this->assignRef('methods', $usable_methods);
		$this->assignRef('payment_method', $payment_method);
		$this->assignRef('payment_id', $payment_id);

		$js = "
function moveOnMax(field,nextFieldID){
	if(field.value.length >= field.maxLength){
		document.getElementById(nextFieldID).focus();
	}
}
window.hikashop.ready( function(){
";
		$done=false;
		if(empty($usable_methods)) {
			if(count($paymentClass->errors)) {
				foreach($paymentClass->errors as $error) {
					if(!empty($error))
						$app->enqueueMessage($error);
				}
			}
		} else {
			$config =& hikashop_config();
			$auto_select_default = $config->get('auto_select_default',2);
			if($auto_select_default == 0) $done = true;
			foreach($usable_methods as $method){
				$show = false;
				if(($payment_method==$method->payment_type && $payment_id==$method->payment_id)|| (empty($payment_id)&&!$done)){
					$done = true;
					$show = true;
				}
				$js.="
	if(typeof(hkjQuery) == 'undefined') window.hkjQuery = window.jQuery;
	var mySlide_".$method->payment_type.'_'.$method->payment_id." = hkjQuery('#hikashop_credit_card_".$method->payment_type.'_'.$method->payment_id."');
";
				if(!$show){
					$js.="
	mySlide_".$method->payment_type.'_'.$method->payment_id.".hide();
	var hikashop_last_opened_slide = null;
";
				}else{
					$js.="
	var hikashop_last_opened_slide = mySlide_".$method->payment_type.'_'.$method->payment_id.";
";
				}
				$js.="
	hkjQuery('#radio_".$method->payment_type.'_'.$method->payment_id."').click(function(el) {
		if(hikashop_last_opened_slide) {
			if(mySlide_".$method->payment_type.'_'.$method->payment_id." == hikashop_last_opened_slide)
				return;
			hikashop_last_opened_slide.toggle();
		}
		mySlide_".$method->payment_type.'_'.$method->payment_id.".toggle();
		hikashop_last_opened_slide = mySlide_".$method->payment_type.'_'.$method->payment_id.";
	});
";
			}
		}
		$js.="
});
var ccHikaErrors = {
	3: '".JText::_('CREDIT_CARD_INVALID')."',
	5: '".JText::_('CREDIT_CARD_EXPIRED')."'
}
";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");

		$this->_getImagesName('payment');

		$currencyClass = hikashop_get('class.currency');
		$currencyClass->processPayments($this->methods, $order);
		$this->assignRef('currencyHelper',$currencyClass);
	}

	function _getImagesName($type){
		$images_folder = HIKASHOP_MEDIA .'images'.DS.$type.DS;
		jimport('joomla.filesystem.folder');
		$files = JFolder::files($images_folder);
		$images = array();
		if(!empty($files)){
			foreach($files as $file){
				$parts = explode('.',$file);
				array_pop($parts);
				$name = implode('.',$parts);
				$images[$name] = $file;
			}
		}
		$this->assignRef('images_'.$type,$images);
	}

	function confirm(){
	}

	function after_end(){
		$order_id = hikaInput::get()->getInt('order_id');
		if(empty($order_id)){
			$app = JFactory::getApplication();
			$order_id = $app->getUserState('com_hikashop.order_id');
		}
		$order =null;
		if(!empty($order_id)){
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->loadFullOrder($order_id,false,false);
		}

		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');

		$this->assignRef('order',$order);
	}

	function status() {
		$app = JFactory::getApplication();
		$cart = $this->initCart();

		$shipping_id = $cart->cart_shipping_ids;
		$shipping_methods = array();
		if(!empty($cart->cart_shipping_ids)) {
			foreach($cart->cart_shipping_ids as $shipping_id) {
				if(strpos($shipping_id, '@') === false) {
					$extra = '';
					$i = (int)$shipping_id;
				} else {
					list($i, $extra) = explode('@', $shipping_id);
					$i = (int)$i;
					$extra = '@'.$extra;
				}
				foreach($cart->shipping as $shipping) {
					if((int)$shipping->shipping_id != $i)
						continue;
		 			$shipping_methods[] = $shipping->shipping_type . $extra;
					break;
				}
			}
		}
		$shipping_data = @$cart->shipping;

		$payment_id = (int)$cart->cart_payment_id;
		$payment_method = (isset($cart->payment->payment_type) ? $cart->payment->payment_type : '');
		$payment_data = @$cart->payment;

		if(empty($shipping_id))
			$shipping_id = array();
		if(!is_array($shipping_id))
			$shipping_id = array($shipping_id);

		if(empty($shipping_method))
			$shipping_method = array();
		if(!is_array($shipping_method))
			$shipping_method = array($shipping_method);

		if(empty($shipping_data))
			$shipping_data = array();
		if(!is_array($shipping_data))
			$shipping_data = array($shipping_data);

		$this->assignRef('payment_method', $payment_method);
		$this->assignRef('payment_id', $payment_id);
		$this->assignRef('payment_data', $payment_data);
		$this->assignRef('shipping_method', $shipping_methods);
		$this->assignRef('shipping_id', $shipping_id);
		$this->assignRef('shipping_data', $shipping_data);
	}

	function terms() {
		$app = JFactory::getApplication();
		$terms = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_terms');
		if($terms){
			$terms = 'checked="checked"';
		}else{
			$terms = '';
		}
		$this->assignRef('terms_checked',$terms);
	}

	function end() {
		$html = hikaInput::get()->getRaw('hikashop_plugins_html', '');
		$this->assignRef('html',$html);
		$noform = hikaInput::get()->getInt('noform', 1);
		$this->assignRef('noform',$noform);

		$order_id = hikaInput::get()->getInt('order_id');
		if(empty($order_id)){
			$app = JFactory::getApplication();
			$order_id = $app->getUserState('com_hikashop.order_id');
		}
		$order =null;
		if(!empty($order_id)){
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->loadFullOrder($order_id,false,false);
		}

		$this->assignRef('order',$order);
	}

	function printcart() {
		$this->cart();
		$this->status();
		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');
	}

	function ccinfo() {
		$app = JFactory::getApplication();

		if(HIKASHOP_J40)
			JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'left'));
		else
			JHTML::_('behavior.tooltip');

		$payment_method = $app->getUserState( HIKASHOP_COMPONENT.'.payment_method');
		$payment_id = $app->getUserState( HIKASHOP_COMPONENT.'.payment_id');
		$payment_data = $app->getUserState( HIKASHOP_COMPONENT.'.payment_data');

		$this->assignRef('payment_id', $payment_id);
		$this->assignRef('payment_method', $payment_method);
		$this->assignRef('method', $payment_data);
		$this->assignRef('payment_data', $payment_data);

		$display_form = true;
		$this->assignRef('display_form', $display_form);

		$cartHelper = hikashop_get('helper.cart');
		$this->assignRef('cart', $cartHelper);

		$js = "
function moveOnMax(field,nextFieldID){
	if(field.value.length >= field.maxLength){
		document.getElementById(nextFieldID).focus();
	}
}
var ccHikaErrors = new Array ();
ccHikaErrors [3] = '".JText::_('CREDIT_CARD_INVALID')."';
ccHikaErrors [5] = '".JText::_('CREDIT_CARD_EXPIRED')."';
";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}
}
