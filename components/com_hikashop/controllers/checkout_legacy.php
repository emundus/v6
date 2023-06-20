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
class checkoutLegacyController extends hikashopController {
	var $cart_update = false;
	var $modify_views = array();
	var $add = array();
	var $modify = array();
	var $delete = array();
	var $controllers = array();

	var $checkout_workflow = null;
	var $steps = null;
	var $redirect_url = null;

	function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		$this->display = array('convert','step','notice','state','deleteaddress','notify','after_end','activate_page','activate','resetcart','threedsecure','printcart','termsandconditions','show','privacyconsent','');
		if(!$skip) {
			$this->registerDefaultTask('step');
		}

		$conf =& hikashop_config();
		$this->checkout_workflow = trim($conf->get('checkout','login_address_shipping_payment_coupon_cart_status_confirm,end'));
		$this->steps = explode(',',$this->checkout_workflow);
		$this->redirect_url = $conf->get('redirect_url_when_cart_is_empty');
		$this->redirect_url = hikashop_translate($this->redirect_url);

		if(!empty($this->redirect_url)) {
			if(!preg_match('#^https?://#',$this->redirect_url))
				$this->redirect_url = JURI::base().ltrim($this->redirect_url,'/');
			$this->redirect_url = JRoute::_($this->redirect_url,false);
		} else {
			global $Itemid;
			$url = '';
			$itemid_to_use = $Itemid;
			$menuClass = hikashop_get('class.menus');
			if(!empty($itemid_to_use))
				$ok = $menuClass->loadAMenuItemId('product', 'listing', $itemid_to_use);
			if(empty($ok))
				$ok = $menuClass->loadAMenuItemId('product', 'listing');
			if($ok)
				$itemid_to_use = $ok;

			if(!empty($itemid_to_use))
				$url = '&Itemid=' . $itemid_to_use;
			$this->redirect_url = hikashop_completeLink('product&task=listing' . $url, false, true);
		}
	}

	function authorize($task) {
		return $this->isIn($task, array('display'));
	}

	public function privacyconsent() {
		hikaInput::get()->set('layout', 'privacyconsent');
		return $this->display();
	}

	function printcart() {
		hikaInput::get()->set('layout', 'printcart');
		return parent::display();
	}
	function activate_page() {
		hikaInput::get()->set('layout', 'activate_page');
		return parent::display();
	}

	function state() {
		hikaInput::get()->set('layout', 'state');
		return parent::display();
	}

	function notice() {
		$cart_type = hikaInput::get()->post->getVar('cart_type','');
		if(!empty($cart_type)){
			$app = JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.popup_cart_type',$cart_type);
		}
		hikaInput::get()->set( 'layout', 'notice' );
		return parent::display();
	}

	function resetcart() {
		$cart = hikashop_get('class.cart');
		$cart->resetCart();
		$app = JFactory::getApplication();
		$app->redirect( $this->redirect_url );
	}

	function activate() {
		$app = JFactory::getApplication();
		$db			= JFactory::getDBO();
		$user 		= JFactory::getUser();
		$usersConfig = JComponentHelper::getParams( 'com_users' );
		$userActivation			= $usersConfig->get('useractivation');
		$allowUserRegistration	= $usersConfig->get('allowUserRegistration');

		if ($user->get('id')) {
			$app->redirect( hikashop_completeLink('checkout',false,true) );
		}

		if ($allowUserRegistration == '0' || $userActivation == '0') {
			$app->enqueueMessage(JText::_('Access Forbidden'), 'error');
			return;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_user',JPATH_SITE);
		jimport('joomla.user.helper');

		$activation = hikashop_getEscaped(hikaInput::get()->getVar('activation', '', '', 'alnum'));
		if (empty($activation)) {
			$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_NOT_FOUND'));
			return;
		}

		if(HIKASHOP_J30) {
			JModelLegacy::addIncludePath(HIKASHOP_ROOT . DS . 'components' . DS . 'com_users' . DS . 'models');
		} else {
			JModel::addIncludePath(HIKASHOP_ROOT . DS . 'components' . DS . 'com_users' . DS . 'models');
		}
		$model = $this->getModel('Registration', 'UsersModel',array(),true);
		$language = JFactory::getLanguage();
		$language->load('com_users', JPATH_SITE, $language->getTag(), true);
		if($model)
			$result = $model->activate($activation);


		if(!$result) {
			$app->enqueueMessage(JText::_( 'HIKA_REG_ACTIVATE_NOT_FOUND' ));
			return;
		}

		$app->enqueueMessage(JText::_( 'HIKA_REG_ACTIVATE_COMPLETE' ));
		$id = hikaInput::get()->getInt('id',0);

		$userClass = hikashop_get('class.user');
		$user = $userClass->get($id);
		if($id && file_exists(JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.php') && $userActivation < 2) {
			$userClass->addAndConfirmUserInCB($user);
		}

		$infos = hikaInput::get()->getVar('infos','');
		global $Itemid;
		$url = (!empty($Itemid) ? '&Itemid='.$Itemid : '');

		if(!empty($infos) && function_exists('json_decode')) {
			$infos = json_decode(base64_decode($infos), true);

			if(empty($infos['pass']) && !empty($infos['passwd']))
				$infos['pass'] = $infos['passwd'];

			JPluginHelper::importPlugin('user');
			if($userActivation < 2 && !empty($infos['pass']) && !empty($infos['username']) && $this->_doLogin($infos['username'], $infos['pass'], false)) {
				$page = hikaInput::get()->getString('page','checkout');

				if($page == 'checkout'){
					$this->before_address();
					$app->redirect( hikashop_completeLink('checkout'.$url,false,true) );
					return;
				}

				hikaInput::get()->set('layout', 'activate');
				return parent::display();

			} elseif($userActivation >= 2) {
				$app->enqueueMessage(JText::_( 'HIKA_ADMIN_CONFIRM_ACTIVATION' ));
			}
		}

		$url = 'index.php?option=com_users&view=login'.$url;
		$app->redirect( JRoute::_($url, false) );
	}

	function deleteaddress() {
		$addressdelete = hikaInput::get()->getInt('address_id', 0);
		if(empty($addressdelete)) {
			$this->step();
			return;
		}

		JSession::checkToken('request') || die('Invalid Token');
		$addressClass = hikashop_get('class.address');
		$oldData = $addressClass->get($addressdelete);
		if(empty($oldData)) {
			$this->step();
			return;
		}

		$user_id = hikashop_loadUser();
		if($user_id != $oldData->address_user_id) {
			$this->step();
			return;
		}

		$addressClass->delete($addressdelete);

		$app = JFactory::getApplication();

		$oldShip = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_address');
		if($oldShip == $addressdelete) {
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address', 0);
		}
		$oldBill = $app->getUserState(HIKASHOP_COMPONENT.'.billing_address');
		if($oldBill == $addressdelete) {
			$app->setUserState( HIKASHOP_COMPONENT.'.billing_address', 0);
		}

		$this->step();
		return;
	}

	function convert() {
		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->get();

		$result = $cartClass->convert($cart->cart_id);

		if($result) {
			$app = JFactory::getApplication();
			$app->setUserState(HIKASHOP_COMPONENT.'.cart_id', 0);
			$app->setUserState(HIKASHOP_COMPONENT.'.wishlist_id', $cart->cart_id);
		}

		global $Itemid;
		$url = (!empty($Itemid) ? '&Itemid=' . $Itemid : '');

		$url = hikashop_completeLink('wishlist&refresh=true', false, true);
		$this->setRedirect($url);
	}

	function step() {
		hikashop_nocache();

		if(isset($_POST['unique_id'])) {
			$unique_id = $_POST['unique_id'];
			$ck_submital = isset($_SESSION['ck_submital']) ? $_SESSION['ck_submital'] : array();
			static $done = false;

			if(!$done && isset($ck_submital[$unique_id])) {
				hikaInput::get()->set('step', hikaInput::get()->getInt('previous', 0));
				hikaInput::get()->set('layout', 'step');
				return $this->display();
			}

			$ck_submital[$unique_id] = true;
			$_SESSION['ck_submital'] = $ck_submital;
			$done = true;
		}

		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->get(0);

		if(empty($cart->cart_id)) {
			$this->setRedirect($this->redirect_url, JText::_('CART_EMPTY'));
			return true;
		}

		$config =& hikashop_config();
		$app = JFactory::getApplication();

		global $Itemid;
		$redirect = false;
		$ssl = false;
		$new_item_id = (int)$Itemid;
		$itemid_for_checkout = $config->get('checkout_itemid', 0);

		if(!empty($itemid_for_checkout)) {
			if($new_item_id != $itemid_for_checkout && empty($_SESSION['hikashop_new_itemid'])) {
				$new_item_id = $itemid_for_checkout;
				$_SESSION['hikashop_new_itemid'] = $new_item_id;
				$redirect = true;
			} else {
				$_SESSION['hikashop_new_itemid'] = '';
			}
		}

		if(($config->get('force_ssl', 0) == 1 || $config->get('force_ssl', 0) == 'url') && $app->getUserState('com_hikashop.ssl_redirect') != 1) {
			if( !hikashop_isSSL()) {
				$ssl = 1;
				$redirect = true;

				$app->setUserState('com_hikashop.ssl_redirect', 1);
			}
		}

		if($redirect) {
			$url = (!empty($new_item_id) ? '&Itemid='.$new_item_id : '');

			if($config->get('force_ssl', 0) != 'url') {
				$this->setRedirect( JRoute::_('index.php?option='.HIKASHOP_COMPONENT.'&ctrl=checkout'.$url, false, $ssl));
				return true;
			}

			$url = $config->get('force_ssl_url');
			$url = str_replace('http://', 'https://', $url);

			if(strpos($url, 'https://') === false)
				$url = 'https://' . $url;

			$requestUri = $_SERVER['PHP_SELF'];
			$str_start = strpos($requestUri,'index.php');
			if(strpos($requestUri, 'index.php') != 0)
				$requestUri = substr($requestUri, $str_start-1, strlen($requestUri));
			if(!empty($_SERVER['QUERY_STRING']))
				$requestUri = rtrim($requestUri, '/') . '?' . $_SERVER['QUERY_STRING'];

			$app->redirect($url . $requestUri);
			return true;
		}

		$go_back = false;
		$this->previous = hikaInput::get()->getInt('previous', 0);
		$this->current = hikaInput::get()->getInt('step', 0);

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();

		if(isset($_REQUEST['previous'])) {
			if(!isset($this->steps[$this->previous]))
				$this->previous = 0;

			$this->controllers = trim($this->steps[$this->previous]);
			$this->controllers = explode('_', $this->controllers);

			$newArray = array();
			$found = false;
			$cart = false;
			$coupon = false;
			$login = false;
			$address = false;

			foreach($this->controllers as $v) {
				if($v == 'confirm') {
					$found = true;
				} elseif($v == 'cart') {
					$cart = true;
				} elseif($v=='login') {
					$login = true;
				} elseif($v == 'address') {
					$address = true;
				} elseif($v == 'coupon') {
					$coupon = true;
				} else {
					$newArray[] = $v;
				}
			}

			if($cart)
				array_unshift($newArray, 'cart');
			if($coupon)
				array_unshift($newArray, 'coupon');
			if($login)
				array_unshift($newArray, 'login');
			if($address)
				array_unshift($newArray, 'address');
			if($found)
				$newArray[] = 'confirm';

			$this->controllers = $newArray;
			$this->beforeControllers = $newArray;

			foreach($this->controllers as $controller) {
				$method = 'after_'.trim($controller);
				$original_go_back = $go_back;

				if(method_exists($this,$method)) {
					if(!$this->$method(!$go_back)) {
						$go_back = true;
					}
				} else {
					$obj =& $this;
					$app->triggerEvent('onAfterCheckoutStep', array($controller, &$go_back, $original_go_back, &$obj));
				}
			}
		} elseif($this->previous == 0) {
			$auto_select_default = $config->get('auto_select_default', 2);
			if($auto_select_default) {
				$this->before_shipping(true);
				$this->before_payment(true);
			}
		}

		if(!$go_back) {
			$this->controllers = trim(@$this->steps[$this->current]);
			$this->controllers = explode('_', $this->controllers);

			foreach($this->controllers as $controller) {
				$method = 'before_'.trim($controller);
				$original_go_back = $go_back;

				if(method_exists($this, $method)) {
					if(!$this->$method()) {
						$go_back = true;
					}
				} else {
					$obj =& $this;
					$app->triggerEvent('onBeforeCheckoutStep', array($controller, &$go_back, $original_go_back, &$obj));
				}
			}
		}

		if($go_back && isset($this->previous)) {
			hikaInput::get()->set('step', $this->previous);
		}

		hikaInput::get()->set('layout', 'step');
		return $this->display();
	}

	function before_coupon() {
		return true;
	}

	function after_coupon($success) {
		$coupon = hikaInput::get()->getString('coupon','');
		$qty = 1;

		if(empty($coupon)){
			$coupon = hikaInput::get()->getInt('removecoupon', 0);
			$qty = 0;
		}
		if(empty($coupon))
			return true;

		$cartClass = hikashop_get('class.cart');
		if(!$cartClass->update($coupon, $qty, 0, 'coupon'))
			return true;

		if(strpos($this->checkout_workflow, 'shipping') !== false)
			$this->before_shipping(true);

		if(strpos($this->checkout_workflow, 'payment') !== false)
			$this->before_payment(true);

		$this->initCart(true);
		$this->cart_update = true;
		return false;
	}

	function check_coupon() {
		return true;
	}

	function before_terms() {
		return true;
	}

	function termsandconditions() {
		hikaInput::get()->set('layout', 'termsandconditions');
		return $this->display();
	}

	function after_terms($success) {
		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.checkout_terms', hikaInput::get()->getInt('hikashop_checkout_terms', 0));
		if(!$this->cart_update && $success) {
			return $this->check_terms();
		}
		return true;
	}

	function check_terms() {
		$app = JFactory::getApplication();
		$status = (bool)$app->getUserState( HIKASHOP_COMPONENT.'.checkout_terms', 0);
		if(!$status) {
			$app->enqueueMessage(JText::_('PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER'));
		}
		return $status;
	}

	function before_fields() {
		return true;
	}

	function after_fields() {
		if(!hikashop_level(2))
			return true;

		$app = JFactory::getApplication();
		$fieldClass = hikashop_get('class.field');

		$old = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_fields_ok', 0);
		$oldData = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_fields');

		if(is_null($oldData))
			$oldData = new stdClass();
		$cart = $this->initCart();
		$oldData->products = $cart->products;

		$orderData = $fieldClass->getFilteredInput('order', $oldData, !$this->cart_update);
		if($orderData !== false) {
			$app->setUserState(HIKASHOP_COMPONENT.'.checkout_fields_ok', 1);
			$app->setUserState(HIKASHOP_COMPONENT.'.checkout_fields', $orderData);
			$changed = false;
		}

		if((!$old && $orderData === false) || (!empty($orderData) && $changed && $this->_getStep('confirm', (int)$this->previous) === (int)$this->previous)) {
			return false;
		}
		return true;
	}

	function check_fields() {
		if(!hikashop_level(2))
			return true;

		$app = JFactory::getApplication();
		$status = (bool)$app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields_ok',0 );
		if(!$status){
			$app->enqueueMessage(JText::_('PLEASE_FILL_ADDITIONAL_INFO'));
		}
		return $status;
	}

	function before_cart() {
		return true;
	}

	function after_cart($success) {
		$modified = false;
		$cartClass = hikashop_get('class.cart');

		$formData = hikaInput::get()->get('item', array(), 'array');
		if(!empty($formData)) {
			$modified = $cartClass->update($formData, 0, 0, 'item');
		} else {
			$formData = hikaInput::get()->get('data', array(), 'array');
			if(!empty($formData)) {
				$modified = $cartClass->update($formData, 0, 0);
			}
		}

		if($modified === 0)
			return true;
		if(!$modified)
			return false;

		$cartClass->get(0);

		if(strpos($this->checkout_workflow, 'shipping') !== false) {
			$this->before_shipping(true);
		}
		if(strpos($this->checkout_workflow, 'payment') !== false) {
			$this->before_payment(true);
		}
		$this->initCart(true);
		$this->cart_update = true;
		return false;
	}

	function check_cart() {
		$cart = $this->initCart();
		if(empty($cart->products) || !is_array($cart->products) || !count($cart->products)){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('CART_EMPTY'));
			$app->redirect( $this->redirect_url );
		}
		return true;
	}

	function before_login() {
		if(count($this->controllers) != 1)
			return true;

		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$user_id = $app->getUserState(HIKASHOP_COMPONENT.'.user_id', 0);
		if($user->guest && empty($user_id))
			return true;

		$controllersCheck = trim($this->steps[$this->previous]);
		$controllersCheck = explode('_', $controllersCheck);
		$current = $this->current + 1;
		if(count($controllersCheck) == 1 && $controllersCheck[0] == 'login')
			$current = $this->previous + 1;

		hikaInput::get()->set('step', $current);
		hikaInput::get()->set('previous', 0);
		$this->step();
		return true;
	}

	function after_login($success) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		$status = true;
		$user_id = $app->getUserState(HIKASHOP_COMPONENT.'.user_id');

		global $messDisplay;
		$messDisplay = (is_null($messDisplay) ? 0 : 1);

		if($this->cart_update || !$user->guest || !empty($user_id))
			return true;

		JPluginHelper::importPlugin('user');
		$register = hikaInput::get()->getString('register', '');
		$action = hikaInput::get()->getString('login_view_action', '');
		$login = hikaInput::get()->getString('login', '');
		$username = hikaInput::get()->request->getUsername('username', '');

		if($action == 'register' || ($action != 'login' && !empty($register))) {
			$status = $this->_doRegister();
		} elseif(($action == 'login' || !empty($login)) && !empty($username)) {
			$status = $this->_doLogin();
		} else {
			$name = @$_REQUEST['data']['register']['email'];
			if(!empty($name)) {
				$status = $this->_doRegister();
			} elseif(!empty($username)) {
				$status = $this->_doLogin();
			} elseif(empty($name) && empty($username) && $messDisplay == 0) {
				$app->enqueueMessage(JText::_('PLEASE_FILL_FORM_BEFORE_PROCEEDING'), 'error');
			}
		}

		if(!$status)
			return $status;

		if($this->_getStep('address',$this->previous) !== false || $this->_getStep('confirm', (int)$this->previous) === (int)$this->previous) {
			$status = false;
		}
		if(!$this->before_address()) {
			$status = false;
		}
		if(!$status || $this->_getStep('shipping', $this->previous) !== false) {
			$this->before_shipping();
		}
		$this->before_login();

		return $status;
	}

	function _doRegister() {
		$userClass = hikashop_get('class.user');
		$status = $userClass->registerLegacy($this);
		$app = JFactory::getApplication();
		if(!$status)
			return $status;

		$this->cart_update = true;
		$app->setUserState(HIKASHOP_COMPONENT.'.user_id', (int)$userClass->user_id);

		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_address', 0);
		$app->setUserState(HIKASHOP_COMPONENT.'.billing_address', 0);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.payment_method', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.payment_id', 0);
		$app->setUserState(HIKASHOP_COMPONENT.'.payment_data', null);

		return $status;
	}

	function _doLogin($user = '', $pass = '', $checkToken = true) {
		$options = array(
			'remember' => hikaInput::get()->getBool('remember', false),
			'return' => false,
		);

		$credentials = array();
		if(empty($user)) {
			$credentials['username'] = hikaInput::get()->request->getUsername('username', '');
		} else {
			$credentials['username'] = $user;
		}
		if(empty($pass)) {
			$credentials['password'] = hikaInput::get()->request->getRaw('passwd', '');
		} else {
			$credentials['password'] = $pass;
		}

		$app = JFactory::getApplication();
		$result = $app->login($credentials, $options);

		$user = JFactory::getUser();

		if($result!==true || $user->guest) {
			return false;
		}

		$userClass = hikashop_get('class.user');
		$user_id = $userClass->getID($user->get('id'));
		if(!empty($user_id)) {
			$app->setUserState(HIKASHOP_COMPONENT.'.user_id', $user_id);
			$hk_user = hikashop_loadUser(true, true);
		}

		$cartClass = hikashop_get('class.cart');
		$cartClass->get('reset_cache');
		$this->initCart(true);

		$this->cart_update = true;

		return true;
	}

	function check_login() {
		$logged = (bool)hikashop_loadUser();
		if(!$logged) {
			$app = JFactory::getApplication();
			$app->enqueueMessage( JText::_('LOGIN_OR_REGISTER_ACCOUNT') );
		}
		return $logged;
	}

	public function getShippingAddress() {
		$app = JFactory::getApplication();
		$ret = (int)$app->getUserState(HIKASHOP_COMPONENT.'.shipping_address', 0);

		if(empty($this->cartClass))
			$this->cartClass = hikashop_get('class.cart');
		$cart = $this->cartClass->get(0);
		if(empty($cart))
			return 0;
		if((int)$cart->cart_shipping_address_ids != $ret) {
			$this->cartClass->updateAddress(0, 'shipping', $ret);
		}
		return $ret;
	}
	public function getBillingAddress() {
		$app = JFactory::getApplication();
		$ret = (int)$app->getUserState(HIKASHOP_COMPONENT.'.billing_address', 0);

		if(empty($this->cartClass))
			$this->cartClass = hikashop_get('class.cart');
		$cart = $this->cartClass->get(0);
		if(empty($cart))
			return $ret;
		if((int)$cart->cart_billing_address_id != $ret) {
			$this->cartClass->updateAddress(0, 'billing', $ret);
		}
		return $ret;
	}

	public function setShippingAddress($address_id) {
		if(empty($this->cartClass))
			$this->cartClass = hikashop_get('class.cart');
		$this->cartClass->updateAddress(0, 'shipping', $address_id);

		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_address', $address_id);
	}
	public function setBillingAddress($address_id) {
		if(empty($this->cartClass))
			$this->cartClass = hikashop_get('class.cart');
		$this->cartClass->updateAddress(0, 'billing', $address_id);

		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.billing_address', $address_id);
	}

	function before_address() {
		$status = $this->_checkLogin();
		if(!$status)
			return $status;

		$user_id = hikashop_loadUser();
		if(empty($user_id))
			return $status;

		$app = JFactory::getApplication();
		$shipping = $this->getShippingAddress(); // $app->getUserState(HIKASHOP_COMPONENT.'.shipping_address', 0);
		$billing = $this->getBillingAddress(); //  $app->getUserState(HIKASHOP_COMPONENT.'.billing_address', 0);

		if(!empty($billing)) {
			$db = JFactory::getDBO();
			$db->setQuery('SELECT address_id FROM '.hikashop_table('address').' WHERE address_published=1 AND address_id='.$billing);
			$result = $db->loadResult();
			if($billing == $shipping) {
				$billing = $shipping = $result;
				$shipping_done = true;
			} else {
				$billing = $result;
			}
		}

		if(!empty($shipping) && empty($shipping_done)) {
			$db = JFactory::getDBO();
			$db->setQuery('SELECT address_id FROM '.hikashop_table('address').' WHERE address_published=1 AND address_id='.$shipping);
			$shipping = $db->loadResult();
		}

		if(empty($shipping) || empty($billing)) {
			$db = JFactory::getDBO();
			$db->setQuery('SELECT address_id FROM '.hikashop_table('address').' WHERE address_published=1 AND address_user_id='.$user_id);
			$address_id = $db->loadResult();

			$this->setBillingAddress($address_id);
			$this->setShippingAddress($address_id);

			if(strpos($this->checkout_workflow,'shipping') !== false) {
				if(!$this->before_shipping(true) && $this->_getStep('shipping', $this->previous) !== false) {
					$status = false;
				}
			}
			if(strpos($this->checkout_workflow, 'payment') !== false) {
				if(!$this->before_payment(true) && $this->_getStep('payment', $this->previous) !== false) {
					$status = false;
				}
			}

			$this->initCart(true);
			$this->cart_update = true;
			$this->initCart();
		}

		return $status;
	}

	function after_address($success) {
		if($this->cart_update) {
			return true;
		}
		$logged = (bool)hikashop_loadUser();
		if(!$logged) {
			return true;
		}

		$addressClass = hikashop_get('class.address');
		$result = $addressClass->frontSaveForm();
		if($result === false) {
			return false;
		}

		$billing = hikaInput::get()->getInt('hikashop_address_billing', 0);
		$shipping = hikaInput::get()->getInt('hikashop_address_shipping', 0);

		if(!empty($result) && count($result)) {
			if(isset($result['billing_address'])) {
				$billing = $result['billing_address']->id;
			}
			if(isset($result['shipping_address'])) {
				$shipping = $result['shipping_address']->id;
			}
		}

		if(empty($billing)) {
			if(!$this->cart_update && !hikaInput::get()->getInt('removecoupon', 0)) {
				hikaInput::get()->set(HIKASHOP_COMPONENT.'.address_error', 1);

				$app = JFactory::getApplication();
				$app->enqueueMessage( JText::_('CREATE_OR_SELECT_ADDRESS') );
			}
			return false;
		}

		if(hikaInput::get()->getString('same_address','') == 'yes' || empty($shipping)) {
			$shipping = $billing;
		}
		$old_billing_address = $this->getBillingAddress();
		$old_shipping_address = $this->getShippingAddress();

		if($billing != $old_billing_address) {
			$this->setBillingAddress($billing);
		}

		if($shipping != $old_shipping_address) {
			$this->setShippingAddress($shipping);
		}

		if($shipping != $old_shipping_address) {
			$cart = $this->initCart();
			if($cart->has_shipping) {
				$this->cart_update=true;
				if(strpos($this->checkout_workflow, 'shipping') !== false) {
					$this->before_shipping(true);
				}
				if(strpos($this->checkout_workflow, 'payment') !== false) {
					$this->before_payment(true);
				}
				return false;
			}
		}
		if($billing != $old_billing_address) {
			return false;
		}
		return true;
	}

	function check_address() {
		$shipping_address = $this->getShippingAddress();

		if(!empty($shipping_address))
			return true;

		hikaInput::get()->set(HIKASHOP_COMPONENT.'.address_error', 1);

		$app = JFactory::getApplication();
		$app->enqueueMessage( JText::_('CREATE_OR_SELECT_ADDRESS') );

		return false;
	}

	function before_shipping($directCall = false) {
		$app = JFactory::getApplication();
		$ok = true;

		if(!$directCall) {
			$ok = $this->_checkLogin();
			if(!$ok)
				return $ok;
		} else {
			$this->initCart(true);
		}

		$shipping_address = $this->getShippingAddress();

		if(empty($shipping_address) && !$directCall) {
			$found = $this->_getStep('address');
			if($found !== false && $found != $this->current) {
				static $done = false;
				hikaInput::get()->set('step', $found);
				hikaInput::get()->set('previous', 0);
				if(!$done) {
					$done = true;
					$this->step();
				}
				return false;
			}
		}

		$config = hikashop_config();
		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->getFullCart(0);

		$hasShipping = !empty($cart->usable_methods->shipping) || !empty($cart->package['weight']['value']) || $config->get('force_shipping', 0);

		if(!$hasShipping) {
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', null);
			return true;
		}

		$shipping_methods = array();
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
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', $shipping_methods);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', $cart->cart_shipping_ids);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', $cart->shipping);

		return true;
	}

	function after_shipping($success) {
		if($this->cart_update)
			return true;

		$cart = $this->initCart();
		if(!$cart->has_shipping)
			return true;

		$shipping = hikaInput::get()->getString('hikashop_shippings', '');

		$app = JFactory::getApplication();
		$shippingClass = hikashop_get('class.shipping');
		$methods =& $shippingClass->getShippings($cart);
		if(empty($methods))
			return false;

		if($shipping != implode(';', array_keys($cart->shipping_groups)) && !(empty($shipping) && implode(';', array_keys($cart->shipping_groups)) == '0')) {
			return false;
		}

		$shippings = array();
		$shipping_ids_cart = array();
		$shipping_ids = array();
		$shipping_datas = array();
		$several_shipping = (count($cart->shipping_groups) > 1);
		if($several_shipping)
			$order_products = $cart->products;

		foreach($cart->shipping_groups as $group_key => $shipping_group) {
			$input_name = 'hikashop_shipping';
			if($several_shipping)
				$input_name .= '_'.$group_key;
			$shipping = hikaInput::get()->getString($input_name, null);
			if(empty($shipping))
				return false;

			$key_lng = strlen($group_key) + 1;
			if($several_shipping && substr($shipping, -$key_lng) == '_' . $group_key) {
				$shipping = substr($shipping, 0, strlen($shipping) - $key_lng);
			}

			if($shipping == '-' && empty($shipping_group->shippings))
				continue;

			$shipping = explode('_', $shipping);
			if(count($shipping) <= 1)
				return false;

			$shipping_id = array_pop($shipping);

			$shipping = implode('_', $shipping);
			if(empty($shipping))
				return false;

			$data = hikashop_import('hikashopshipping', $shipping);

			if($several_shipping) {
				$cart->products = $shipping_group->products;
				$rates_copy = array();
				foreach($methods as $rate) {
					if(!isset($rate->shipping_warehouse_id) || $rate->shipping_warehouse_id == $group_key)
						$rates_copy[] = clone($rate);
				}
				$shipping_data = $data->onShippingSave($cart, $rates_copy, $shipping_id, $group_key);
				unset($rates_copy);
			} else {
				$shipping_data = $data->onShippingSave($cart, $methods, $shipping_id);
			}

			if($shipping_data === false) {
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', null);
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', null);

				$cartClass = hikashop_get('class.cart');
				$cartClass->updateShipping(0, array());

				return false;
			}

			$shippings[] = $shipping . '@' . $group_key;
			$shipping_ids[] = $shipping_id . '@' . $group_key;
			$shipping_datas[] = $shipping_data;

			$shipping_ids_cart[$group_key] = $shipping_id;
		}
		if($several_shipping)
			$cart->products = $order_products;

		$old_shipping_methods = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_method');
		$old_shipping_ids = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id');
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', $shippings);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', $shipping_ids);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', $shipping_datas);

		$cartClass = hikashop_get('class.cart');
		$cartClass->updateShipping(0, $shipping_ids_cart);

		if(($old_shipping_ids !== $shipping_ids || $old_shipping_methods !== $shippings) && strpos($this->checkout_workflow, 'payment') !== false) {
			$this->cart_update = true;
			$this->initCart(true);
			$this->before_payment(true);
		}

		if(($old_shipping_ids !== $shipping_ids || $old_shipping_methods !== $shippings) && ($this->_getStep('cart', (int)$this->previous) === (int)$this->previous || $this->_getStep('confirm', (int)$this->previous) === (int)$this->previous)) {
			return false;
		}

		return true;
	}

	function check_shipping() {
		$app = JFactory::getApplication();
		$shipping_done = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_method');
		$shipping_done = !empty($shipping_done);

		if($shipping_done)
			return $shipping_done;

		$cart = $this->initCart();
		if(!$cart->has_shipping) {
			return true;
		}

		$app->enqueueMessage( JText::_('SELECT_SHIPPING') );
		return $shipping_done;
	}

	function initCart($reset = false) {
		static $cart = false;
		if($reset) {
			$cart = false;
			return true;
		}
		if(!empty($cart))
			return $cart;

		$cartClass = hikashop_get('class.cart');
		$config = hikashop_config();

		$cart = $cartClass->getFullCart(0);
		if(empty($cart->products)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('CART_EMPTY'));
			$app->redirect( $this->redirect_url );
		}

		$cart->has_shipping = (!empty($cart->usable_methods->shipping) || !empty($cart->package['weight']['value']) || $config->get('force_shipping', 0));
		return $cart;
	}

	function before_payment($directCall = false) {
		$ok = true;
		if(!$directCall) {
			$ok = $this->_checkLogin();
			if(!$ok)
				return $ok;
		}

		$app = JFactory::getApplication();

		$cart = $this->initCart();

		if(empty($cart->payment) && !empty($cart->usable_methods->payment_valid))
			return true;

		$payment_method = !empty($cart->payment->payment_type) ? $cart->payment->payment_type : '';
		$payment_id = $cart->cart_payment_id;

		if(!empty($payment_method) && !$this->cart_update)
			return $ok;

		$payment = hikaInput::get()->getString('hikashop_payment', '');
		if(!empty($payment)){
			$payment = explode('_', $payment);
			if(count($payment) > 1) {
				$new_payment_id = array_pop($payment);
				$payment = implode('_',$payment);
				if($new_payment_id != $payment_id || $payment != $payment_method) {
					$payment_method = $payment;
					$payment_id = $new_payment_id;
					$status = $this->_readPayment();
					if(!$directCall)
						return $status;
				}
			}
		}

		$methods = $cart->usable_methods->payment;

		if(!empty($methods)) {
			$reset_payment = true;
			if($this->cart_update) {
				$found = false;
				foreach($methods as $m) {
					if($m->payment_id == $payment_id && $m->payment_type == $payment_method) {
						$found = true;
						break;
					}
				}
				$reset_payment = !$found;
			}

			if($reset_payment) {
				$config =& hikashop_config();
				$auto_select_default = $config->get('auto_select_default',2);
				if($auto_select_default == 1 && count($methods) > 1) $auto_select_default = 0;
				$ok = false;
				if($auto_select_default) {
					$method = reset($methods);

					$app->setUserState( HIKASHOP_COMPONENT.'.payment_method', $method->payment_type);
					$app->setUserState( HIKASHOP_COMPONENT.'.payment_id', $method->payment_id);
					$app->setUserState( HIKASHOP_COMPONENT.'.payment_data', $method);
				} else {
					$app->setUserState( HIKASHOP_COMPONENT.'.payment_method','');
					$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',0);
					$app->setUserState( HIKASHOP_COMPONENT.'.payment_data','');
					if(($payment_method == '' && $payment_id == '') || $directCall || !empty($this->beforeControllers) && count($this->beforeControllers) == 1) {
						$ok = true;
					}
				}
			}
		}

		return $ok;
	}

	function after_payment($success) {
		if($this->cart_update)
			return true;

		$cart = $this->initCart();
		if(empty($cart->full_total->prices[0]->price_value_with_tax) || bccomp($cart->full_total->prices[0]->price_value_with_tax, 0, 5) == 0) {
			$app = JFactory::getApplication();
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_method', '');
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_id', 0);
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_data', '');
			return true;
		}

		return $this->_readPayment();
	}

	function _readPayment() {
		$payment = hikaInput::get()->getString('hikashop_payment','');
		if(empty($payment))
			return false;

		$payment = explode('_', $payment);
		if(empty($payment) || count($payment) == 0)
			return false;

		$payment_id = array_pop($payment);
		$payment = implode('_', $payment);
		if(empty($payment))
			return false;


		$cart = $this->initCart();

		$pluginsClass = hikashop_get('class.plugins');
		$rates = $pluginsClass->getMethods('payment');

		$data = hikashop_import('hikashoppayment', $payment);
		$paymentData = $data->onPaymentSave($cart, $rates, $payment_id);
		if($paymentData === false)
			return false;

		$old_payment_id = $cart->cart_payment_id;

		$app = JFactory::getApplication();
		$cartClass = hikashop_get('class.cart');
		$ret = $cartClass->updatePayment($cart->cart_id, $payment_id);
		if($ret === false)
			return false;
		$this->initCart(true);

		$currencyClass = hikashop_get('class.currency');
		$currencyClass->convertPayments($rates);
		$app->setUserState(HIKASHOP_COMPONENT.'.payment_method', $payment);
		$app->setUserState(HIKASHOP_COMPONENT.'.payment_id', $payment_id);
		$app->setUserState(HIKASHOP_COMPONENT.'.payment_data', $paymentData);

		if(!empty($paymentData->ask_cc)) {
			$paymentClass = hikashop_get('class.payment');
			if(!$paymentClass->readCC()) {
				$app->enqueueMessage( JText::_('FILL_CREDIT_CARD_INFO') );
				return false;
			}
		}

		if($old_payment_id != $payment_id && ($this->_getStep('cart', (int)$this->previous) === (int)$this->previous || $this->_getStep('confirm', (int)$this->previous) === (int)$this->previous))
			return false;

		return true;
	}

	function check_payment() {
		$cart = $this->initCart();
		$app = JFactory::getApplication();
		if(empty($cart->full_total->prices[0]->price_value_with_tax) || bccomp($cart->full_total->prices[0]->price_value_with_tax, 0, 5) == 0) {
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_method', '');
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_id', 0);
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_data', '');
			return true;
		}

		$payment_method = !empty($cart->payment->payment_type) ? $cart->payment->payment_type : '';
		$payment_session = $app->getUserState(HIKASHOP_COMPONENT.'.payment_method');
		if(empty($payment_session) || $payment_session != $payment_method)
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_method', $payment_method);

		$payment_done = !empty($cart->payment);

		if(!$payment_done) {
			$app->enqueueMessage( JText::_('SELECT_PAYMENT') );
			return $payment_done;
		}

		$paymentData = $cart->payment;
		if(!empty($paymentData->ask_cc)) {
			$cc_number = $app->getUserState(HIKASHOP_COMPONENT.'.cc_number');
			$cc_month = $app->getUserState(HIKASHOP_COMPONENT.'.cc_month');
			$cc_year = $app->getUserState(HIKASHOP_COMPONENT.'.cc_year');
			$cc_CCV = $app->getUserState(HIKASHOP_COMPONENT.'.cc_CCV');
			$cc_owner = $app->getUserState(HIKASHOP_COMPONENT.'.cc_owner');
			if(empty($cc_number) || empty($cc_month) || empty($cc_year) || (empty($cc_CCV) && !empty($paymentData->ask_ccv)) || (empty($cc_owner) && !empty($paymentData->ask_owner))) {
				$app->enqueueMessage( JText::_('FILL_CREDIT_CARD_INFO') );
				$payment_done = false;
			}
		}
		return $payment_done;
	}

	function _checkToken() {
		static $done = false;
		if($done)
			return;

		$done = true;
		JSession::checkToken('request') || die('Invalid Token');
	}

	function notify() {
		hikashop_nocache();

		ob_start();

		$plugin = hikaInput::get()->getCmd('notif_payment');
		$type = 'payment';

		if(empty($plugin)) {
			$plugin = hikaInput::get()->getCmd('notif_shipping');
			$type = 'shipping';
		}

		if(empty($plugin)) {
			$plugin = hikaInput::get()->getCmd('notif_hikashop');
			$type = '';
		}

		$pluginInstance = hikashop_import('hikashop' . $type, $plugin);
		if(empty($pluginInstance))
			return false;

		$function = 'on'.ucfirst($type).'Notification';
		if(!method_exists($pluginInstance, $function))
			return false;

		$translationHelper = hikashop_get('helper.translation');
		$cleaned_statuses = $translationHelper->getStatusTrans();

		$data = $pluginInstance->$function($cleaned_statuses);

		$dbg = ob_get_clean();
		if(!empty($dbg)) {
			hikashop_logData($dbg, ucfirst($type). 'Notification: ' . $plugin);
		}
		if(is_string($data) && !empty($data)) {
			echo $data;
		}
	}

	function threedsecure() {
		hikashop_nocache();

		ob_start();
		$payment = hikaInput::get()->getCmd('3dsecure_payment');

		$pluginInstance = hikashop_import('hikashoppayment', $payment);
		if(empty($pluginInstance))
			return false;

		if(!method_exists($pluginInstance, 'onThreeDSecure'))
			return false;

		$trans = hikashop_get('helper.translation');
		$cleaned_statuses = $trans->getStatusTrans();

		$data = $pluginInstance->onThreeDSecure($cleaned_statuses);

		$dbg = ob_get_clean();
		if(!empty($dbg)) {
			hikashop_logData($dbg, '3DSecure: ' . $payment);
		}
		if(is_string($data) && !empty($data)) {
			echo $data;
		}
	}

	function before_confirm() {
		foreach($this->steps as $i => $step) {
			if(intval($i)!=intval($this->current)) {
				$this->_checkStep(trim($step), $i);
			}
		}
		return true;
	}

	function _checkStep($step,$i) {
		$controllers = explode('_', $step);
		$ok = true;
		foreach($controllers as $controller) {
			$fct = 'check_'.trim($controller);

			if(!method_exists($this, $fct))
				continue;

			if(!$this->$fct()) {
				$ok = false;
			}
		}

		if($ok)
			return true;

		$this->setRedirect( hikashop_completeLink('checkout&task=step&step='.$i, false, true) );
		$this->redirect();
	}

	function after_confirm($success) {
		if(!$success){
			return false;
		}
		if(!hikaInput::get()->getVar('validate', 0)) {
			return false;
		}
		if($this->current==$this->previous) {
			return true;
		}

		foreach($this->steps as $i => $step) {
			$this->_checkStep(trim($step), $i);
		}

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config =& hikashop_config();
		$pluginsClass = hikashop_get('class.plugins');

		$cart = $this->initCart();

		$shippings = array();
		$shipping = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_method');
		$shipping_id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id');
		if(!empty($shipping)) {
			foreach($shipping as $ship) {
				$ship = explode('@', $ship, 2);
				$current_id = 0;
				foreach($shipping_id as $sid) {
					list($i, $k) = explode('@', $sid, 2);
					if($k == $ship[1]) {
						$current_id = $i;
						break;
					}
				}
				$shippings[$ship[1]] = array('id' => $current_id, 'name' => $ship[0]);
			}

			$shippingClass = hikashop_get('class.shipping');
			$methods =& $shippingClass->getShippings($cart);
			$shipping_groups = $shippingClass->getShippingGroups($cart, $methods);
		}

		$payment = $app->getUserState( HIKASHOP_COMPONENT.'.payment_method');
		$payment_id = $app->getUserState( HIKASHOP_COMPONENT.'.payment_id');

		$ids = array();
		foreach($cart->products as $product){
			if($product->cart_product_quantity > 0 && $product->product_type == 'variant') {
				$ids[$product->product_id] = (int)$product->product_id;
			}
		}
		if(!empty($ids)){
			$database = JFactory::getDBO();
			$query = 'SELECT a.variant_product_id as product_id, b.characteristic_id as value_id, b.characteristic_value as value, c.characteristic_id as name_id, c.characteristic_value as name '.
					' FROM '.hikashop_table('variant').' AS a '.
					' LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id = b.characteristic_id '.
					' LEFT JOIN '.hikashop_table('characteristic').' AS c ON b.characteristic_parent_id = c.characteristic_id '.
					' WHERE a.variant_product_id IN ('.implode(',', $ids).')';
			$database->setQuery($query);
			$characteristics = $database->loadObjectList();

			if(!empty($characteristics)) {
				foreach($characteristics as $characteristic) {
					foreach($cart->products as $k => $product) {
						if($product->product_id != $characteristic->product_id)
							continue;

						if(empty($product->characteristics)) {
							$product->characteristics = array($characteristic->name => $characteristic->value);
						} else {
							$product->characteristics[$characteristic->name] = $characteristic->value;
						}
					}
				}
			}
		}
		if(hikashop_level(2)) {
			$element = null;
			$fieldsClass = hikashop_get('class.field');
			$itemFields = $fieldsClass->getFields('', $element, 'item');
		}

		$cart->full_products =& $cart->products;
		unset($cart->products);
		$cart->products = array();
		foreach($cart->full_products as &$product) {
			if((int)$product->cart_product_quantity <= 0)
				continue;

			$orderProduct = new stdClass();
			$orderProduct->product_id = $product->product_id;
			$orderProduct->order_product_quantity = $product->cart_product_quantity;
			$orderProduct->order_product_name = $product->product_name;
			$orderProduct->cart_product_id = $product->cart_product_id;
			$orderProduct->cart_product_option_parent_id = $product->cart_product_option_parent_id;
			$orderProduct->order_product_code = $product->product_code;
			$orderProduct->order_product_price = @$product->prices[0]->unit_price->price_value;
			$orderProduct->order_product_wishlist_id = @$product->cart_product_wishlist_id;
			$orderProduct->product_subscription_id = @$product->product_subscription_id;

			$tax = 0;
			if(!empty($product->prices[0]->unit_price->price_value_with_tax) && bccomp($product->prices[0]->unit_price->price_value_with_tax,0,5))
				$tax = $product->prices[0]->unit_price->price_value_with_tax-$product->prices[0]->unit_price->price_value;
			$orderProduct->order_product_tax = $tax;

			$characteristics = '';
			if(!empty($product->characteristics))
				$characteristics = serialize($product->characteristics);
			$orderProduct->order_product_options = $characteristics;

			if(!empty($product->discount)) {
				$orderProduct->discount = clone($product->discount);
				$orderProduct->discount->price_value_without_discount = $product->prices[0]->unit_price->price_value_without_discount;
				$orderProduct->discount->price_value_without_discount_with_tax = @$product->prices[0]->unit_price->price_value_without_discount_with_tax;
				$orderProduct->discount->taxes_without_discount = @$product->prices[0]->unit_price->taxes_without_discount;
			}

			if(!empty($cart->item_fields)) {
				foreach($cart->item_fields as $field) {
					$namekey = $field->field_namekey;
					if(isset($product->$namekey))
						$orderProduct->$namekey = $product->$namekey;
				}
			}elseif(!empty($itemFields)) {
				foreach($itemFields as $field) {
					$namekey = $field->field_namekey;
					if(isset($product->$namekey))
						$orderProduct->$namekey = $product->$namekey;
				}
			}

			if(isset($product->prices[0]->unit_price->taxes))
				$orderProduct->order_product_tax_info = $product->prices[0]->unit_price->taxes;

			if(isset($product->files))
				$orderProduct->files =& $product->files;

			if(!empty($shipping)) {
				$shipping_done = false;
				foreach($shipping_groups as $group_key => $group_products) {
					if(!isset($shippings[$group_key]))
						continue;
					foreach($group_products->products as $group_product) {
						if((int)$group_product->cart_product_id == (int)$product->cart_product_id) {
							$orderProduct->order_product_shipping_id = $shippings[$group_key]['id'] . '@' . $group_key;
							$orderProduct->order_product_shipping_method = $shippings[$group_key]['name'];
							$shipping_done = true;
							break;
						}
					}
					if($shipping_done)
						break;
				}
			}
			$cart->products[] = $orderProduct;
		}
		unset($product);

		$shipping_address = (int)$cart->cart_shipping_address_ids;
		$billing_address = $cart->cart_billing_address_id;
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = (int)$app->getUserState(HIKASHOP_COMPONENT.'.currency_id', $main_currency);

		$order = new stdClass();
		$order->order_user_id = @hikashop_loadUser();
		$order->order_status = $config->get('order_created_status');
		$order->order_shipping_address_id = $shipping_address;
		$order->order_billing_address_id = $billing_address;
		$order->order_discount_code = @$cart->coupon->discount_code;
		$order->order_currency_id = $cart->full_total->prices[0]->price_currency_id;

		$order->order_currency_info = new stdClass();
		$currencyClass = hikashop_get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id, $currencies);
		$currency = $currencies[$order->order_currency_id];
		$order->order_currency_info->currency_code = $currency->currency_code;
		$order->order_currency_info->currency_rate = $currency->currency_rate;
		$order->order_currency_info->currency_percent_fee = $currency->currency_percent_fee;
		$order->order_currency_info->currency_modified = $currency->currency_modified;

		$order->order_type = 'sale';
		$order->order_full_price = $cart->full_total->prices[0]->price_value_with_tax;
		$order->order_tax_info = @$cart->full_total->prices[0]->taxes;

		$order->order_shipping_price = 0.0;
		$order->order_shipping_tax = 0.0;
		$order->order_shipping_params = null;
		if(!empty($cart->shipping)) {
			$order->order_shipping_params = new stdClass();
			$order->order_shipping_params->prices = array();
			foreach($cart->shipping as $cart_shipping) {
				$price_key = $cart_shipping->shipping_id;
				if(isset($cart_shipping->shipping_warehouse_id)) {
					if(is_string($cart_shipping->shipping_warehouse_id) || is_int($cart_shipping->shipping_warehouse_id)) {
						$price_key .= '@' . $cart_shipping->shipping_warehouse_id;
					} else {
						$price_key .= '@';
						foreach($cart_shipping->shipping_warehouse_id as $k => $v) {
							$price_key .= $k . $v;
						}
					}
				}

				$order->order_shipping_params->prices[$price_key] = new stdClass();
				$order->order_shipping_params->prices[$price_key]->price_with_tax = $cart_shipping->shipping_price_with_tax;

				$order->order_shipping_price += $cart_shipping->shipping_price_with_tax;

				if(!empty($cart_shipping->shipping_price_with_tax) && !empty($cart_shipping->shipping_price)) {
					$order->order_shipping_tax += $cart_shipping->shipping_price_with_tax - $cart_shipping->shipping_price;
					$order->order_shipping_params->prices[$price_key]->tax = $cart_shipping->shipping_price_with_tax - $cart_shipping->shipping_price;
					if(!empty($cart_shipping->taxes)) {
						$order->order_shipping_params->prices[$price_key]->taxes = array();
						foreach($cart_shipping->taxes as $tax) {
							$order->order_shipping_params->prices[$price_key]->taxes[$tax->tax_namekey] = $tax->tax_amount;
							if(isset($order->order_tax_info[$tax->tax_namekey])) {
								if(empty($order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping))
									$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping = 0;
								$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping += $tax->tax_amount;
							} elseif(!empty($order->order_tax_info[$tax->tax_namekey]->tax_amount) && $order->order_tax_info[$tax->tax_namekey]->tax_amount>0) {
								$order->order_tax_info[$tax->tax_namekey] = $tax;
								$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping = $order->order_tax_info[$tax->tax_namekey]->tax_amount;
								$order->order_tax_info[$tax->tax_namekey]->tax_amount = 0;
							}
						}
					}
				}
			}
		}

		$order->order_payment_price = @$cart->payment->payment_price_with_tax;
		if(!empty($cart->payment) && !empty($cart->payment->payment_price_with_tax) && !empty($cart->payment->payment_price)) {
			$order->order_payment_tax = $cart->payment->payment_price_with_tax - $cart->payment->payment_price;
			if(!empty($cart->payment->taxes)) {
				foreach($cart->payment->taxes as $tax) {
					if(isset($order->order_tax_info[$tax->tax_namekey])) {
						$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_payment = $tax->tax_amount;
					} elseif(!empty($order->order_tax_info[$tax->tax_namekey]->tax_amount) && $order->order_tax_info[$tax->tax_namekey]->tax_amount>0) {
						$order->order_tax_info[$tax->tax_namekey] = $tax;
						$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_payment = $order->order_tax_info[$tax->tax_namekey]->tax_amount;
						$order->order_tax_info[$tax->tax_namekey]->tax_amount = 0;
					}
				}
			}
		}
		$discount_price = 0;
		$discount_tax = 0;

		if(!empty($cart->coupon)&& !empty($cart->coupon->total->prices[0]->price_value_without_discount_with_tax)){
			$discount_price=@$cart->coupon->total->prices[0]->price_value_without_discount_with_tax-@$cart->coupon->total->prices[0]->price_value_with_tax;
			if(!empty($cart->coupon->total->prices[0]->price_value_with_tax)&&!empty($cart->coupon->total->prices[0]->price_value)){
				$discount_tax = (@$cart->coupon->total->prices[0]->price_value_without_discount_with_tax-@$cart->coupon->total->prices[0]->price_value_without_discount)-(@$cart->coupon->total->prices[0]->price_value_with_tax-@$cart->coupon->total->prices[0]->price_value);
				if(isset($cart->coupon->taxes)){
					foreach($cart->coupon->taxes as $tax){
						if(isset($order->order_tax_info[$tax->tax_namekey])){
							$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_coupon = $tax->tax_amount;
						}else{
							$order->order_tax_info[$tax->tax_namekey]=$tax;
							$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_coupon = $order->order_tax_info[$tax->tax_namekey]->tax_amount;
							$order->order_tax_info[$tax->tax_namekey]->tax_amount = 0;
						}
					}
				}
			}
		}
		$order->order_discount_tax = $discount_tax;
		$order->order_discount_price = $discount_price;
		$order->order_shipping_id = $shipping_id;
		$order->order_shipping_method = $shipping;
		$order->order_payment_id = $payment_id;
		$order->order_payment_method = $payment;
		$order->cart =& $cart;
		$order->history = new stdClass();
		$order->history->history_reason = JText::_('ORDER_CREATED');
		$order->history->history_notified = 0;
		$order->history->history_type = 'creation';
		$app = JFactory::getApplication();
		if(hikashop_level(2)) {
			$orderData = $app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields');
			if(!empty($orderData)){
				foreach(get_object_vars($orderData) as $key => $val){
					$order->$key = $val;
				}
			}
		}

		if(!empty($shippings)) {
			if(count($shippings) == 1) {
				$s = reset($shippings);
				$order->order_shipping_id = $s['id'];
				$order->order_shipping_method = $s['name'];
			} else {
				$ids = array();
				foreach($shippings as $key => $ship)
					$ids[] = $ship['id'] . '@' . $key;
				$order->order_shipping_id = implode(';', $ids);
				$order->order_shipping_method = '';
			}
		}

		$paymentClass = hikashop_get('class.payment');
		$paymentClass->checkPaymentOptions($order);

		$orderClass = hikashop_get('class.order');
		$order->order_id = $orderClass->save($order);
		$removeCart = false;
		if(empty($order->order_id))
			return false;
		$app->setUserState( HIKASHOP_COMPONENT.'.checkout_fields_ok',0);
		$entriesData = $app->getUserState( HIKASHOP_COMPONENT.'.entries_fields');
		if(!empty($entriesData)){
			$entryClass = hikashop_get('class.entry');
			foreach($entriesData as $entryData){
				$entryData->order_id = $order->order_id;
				$entryClass->save($entryData);
			}
			$app->setUserState( HIKASHOP_COMPONENT.'.entries_fields',null);
		}

		if(!empty($payment)){
			$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($payment);
			$db->setQuery($query);
			$paymentData = $db->loadObjectList('payment_id');
			$pluginsClass->params($paymentData,'payment');
		}else{
			$paymentData = null;
		}
		if(!empty($shipping)) {
			$shippings_quoted = array();
			foreach($shippings as $ship) {
				$shippings_quoted[] = $db->Quote($ship['name']);
			}
			$query = 'SELECT * FROM '.hikashop_table('shipping').' WHERE shipping_type IN (' . implode(',', $shippings_quoted) . ')';
			$db->setQuery($query);
			$shippingData = $db->loadObjectList('shipping_id');
			$pluginsClass->params($shippingData,'shipping');
		} else {
			$shippingData = null;
		}

		ob_start();
		if(!empty($shippingData)) {
			foreach($shippings as $ship) {
				$data = hikashop_import('hikashopshipping', $ship['name']);
				$data->onAfterOrderConfirm($order, $shippingData, $ship['id']);
				if(!empty($data->removeCart))
					$removeCart = true;
			}
		}
		if(!empty($paymentData)){
			$data = hikashop_import('hikashoppayment',$payment);
			$data->onAfterOrderConfirm($order, $paymentData, $payment_id);
			if(!empty($data->removeCart)){
				$removeCart = true;
			}
		}
		hikaInput::get()->set('hikashop_plugins_html',ob_get_clean());

		$app->setUserState( HIKASHOP_COMPONENT.'.order_id', $order->order_id);

		if($config->get('clean_cart','order_created') == 'order_created' || $removeCart) {

			$cartClass = hikashop_get('class.cart');
			$cartClass->cleanCartFromSession(false);

			$user = JFactory::getUser();
			if($user->guest)
				$this->app->setUserState( HIKASHOP_COMPONENT.'.user_id', 0);
		}
		return true;
	}

	function before_status() {
		return true;
	}

	function after_status() {
		return true;
	}

	function check_status() {
		return true;
	}

	function before_end() {
		$app = JFactory::getApplication();
		$order = $app->getUserState( HIKASHOP_COMPONENT.'.order_id', 0);
		if(empty($order)) {
			return $this->after_confirm(true);
		}
		return true;
	}

	function after_end() {
		if(isset($this->current))
			return true;

		$cartClass = hikashop_get('class.cart');
		$cartClass->cleanCartFromSession();
		hikaInput::get()->set('layout', 'after_end');
		return $this->display();
	}

	function _checkLogin() {
		if(count($this->controllers) != 1)
			return true;

		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$user_id = $app->getUserState( HIKASHOP_COMPONENT.'.user_id');
		if($user->guest && empty($user_id)) {
			$found = $this->_getStep('login');

			if($found !== false) {
				hikaInput::get()->set('step',$found);
				hikaInput::get()->set('previous',0);
				unset($_REQUEST['previous']);
				$this->step();
				return false;
			} else {
				$userData = new stdClass();
				$userData->user_created_ip = hikashop_getIP();
				$userClass = hikashop_get('class.user');
				$userData->user_id = $userClass->save($userData);
				$app->setUserState( HIKASHOP_COMPONENT.'.user_id', $userData->user_id);
			}

		}
		return true;
	}

	function _getStep($search, $onStep = null) {
		$found = false;
		foreach($this->steps as $k => $step){
			if(isset($onStep) && $onStep!=$k)
				continue;

			if(strpos($step, $search) !== false) {
				$found = $k;
				break;
			}
		}
		return $found;
	}

	function display($cachable = false, $urlparams = array()){
		static $done = false;
		$result = true;
		if(!$done) {
			$done = true;
			$result = parent::display();
		}
		return $result;
	}
}
