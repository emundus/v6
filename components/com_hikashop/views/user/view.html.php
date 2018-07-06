<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.5.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
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
		$url_itemid='';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;

		$buttons = array(
			'address' => array(
				'link' => hikashop_completeLink('address'.$url_itemid),
				'level' => 0,
				'image' => 'address',
				'text' => JText::_('ADDRESSES'),
				'description' => JText::_('MANAGE_ADDRESSES')
			),
			'order' => array(
				'link' => hikashop_completeLink('order'.$url_itemid),
				'level' => 0,
				'image' => 'order',
				'text' => JText::_('ORDERS'),
				'description' => JText::_('VIEW_ORDERS')
			),
		);
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		JPluginHelper::importPlugin('hikashopshipping');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onUserAccountDisplay', array(&$buttons));

		$this->assignRef('buttons',$buttons);

		$app = JFactory::getApplication();
		$pathway = $app->getPathway();
		$items = $pathway->getPathway();
		if(!count($items))
			$pathway->addItem(JText::_('CUSTOMER_ACCOUNT'), hikashop_completeLink('user'));

		hikashop_setPageTitle('CUSTOMER_ACCOUNT');
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

	public function form(){
		$this->registration();
		hikashop_setPageTitle('HIKA_REGISTRATION');
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
	}

	public function downloads() {
		$user = hikashop_loadUser(true);
		if(empty($user))
			return false;
	}
}
