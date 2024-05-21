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
class userController extends hikashopController {
	var $delete = array();
	var $modify = array();
	var $modify_views = array();
	var $add = array();

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config,$skip);
		if(!$skip){
			$this->registerDefaultTask('cpanel');
		}

		$this->display = array_merge($this->display, array(
			'cpanel',
			'form',
			'register',
			'downloads',
			'activate',
			'guest_register',
			'guest_form'
		));
	}

	public function register() {
		if(empty($_REQUEST['data']))
			return $this->form();

		$userClass = hikashop_get('class.user');
		$status = $userClass->registerLegacy($this, 'user');

		if(!empty($status)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('THANK_YOU_FOR_REGISTERING', HIKASHOP_LIVE));
			hikaInput::get()->set('layout', 'after_register');
			return parent::display();
		}
		$this->form();
	}
	public function guest_register() {
		$order = $this->_checkGuestOrder();
		if(!$order)
			return false;

		if(empty($_REQUEST['data']) || !is_array($_REQUEST['data']) || !isset($_REQUEST['data']['register']))
			return $this->guest_form();

		$data = new stdClass();
		$requestData = hikaInput::get()->getVar('data');
		foreach($requestData['register'] as $k => $v){
			$data->$k = $v;
		}

		$userClass = hikashop_get('class.user');
		$status = $userClass->registerGuest($order->order_user_id, $data);

		if(!empty($status['status']) && $status['status']) {
			$app = JFactory::getApplication();
			hikashop_get('helper.checkout');
			$checkoutHelper = hikashopCheckoutHelper::get();
			$cart = $checkoutHelper->getCart();

			$jsession = JFactory::getSession();
			$old_session = $jsession->getId();

			$options = array(
				'return' => true,
				'remember' => false
			);
			$credentials = array(
				'username' => (string)$data->username,
				'password' => (string)$data->password
			);

			$old_messages = $app->getMessageQueue();

			$result = $app->login($credentials, $options);

			$user = JFactory::getUser();

			if($result !== true || $user->guest) {
				$new_messages = $app->getMessageQueue();
				if(count($old_messages) == count($new_messages)) {
					$app->enqueueMessage(JText::_('LOGIN_NOT_VALID'), 'error');
				}
				return false;
			}

			$jsession = JFactory::getSession();
			$new_session = $jsession->getId();

			$user_id = $userClass->getID($user->get('id'));
			if(!empty($user_id)) {
				$app->setUserState(HIKASHOP_COMPONENT.'.user_id', $user_id);

				if(!empty($cart)) {
					$cartClass = hikashop_get('class.cart');
					if($cartClass->sessionToUser($cart->cart_id, $old_session, $user_id))
						$checkoutHelper->getCart(true);
				}
			}

			$app->enqueueMessage(JText::sprintf('THANK_YOU_FOR_REGISTERING', HIKASHOP_LIVE));
			hikaInput::get()->set('layout', 'after_register');
			return parent::display();
		}

		foreach($status['messages'] as $message){
			if(empty($message))
				continue;
			$app = JFactory::getApplication();
			$app->enqueueMessage($message[0], $message[1]);
		}

		return $this->guest_form();
	}

	public function guest_form() {
		if(!$this->_checkGuestOrder())
			return false;

		hikaInput::get()->set('layout', 'guest_form');
		return $this->display();
	}

	protected function _checkGuestOrder(){
		$app = JFactory::getApplication();
		$config = hikashop_config();
		if(!$config->get('register_after_guest', 1)){
			$app->enqueueMessage(JText::_('REGISTRATION_AFTER_GUEST_CHECKOUT_NOT_ALLOWED'));
			return false;
		}

		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_users');
		if((int)$params->get('allowUserRegistration') == 0) {
			$app->enqueueMessage(JText::_('REGISTRATION_AFTER_GUEST_CHECKOUT_NOT_ALLOWED'));
			return false;
		}

		$user = JFactory::getUser();
		if(!$user->guest) {
			global $Itemid;
			$url_itemid=(!empty($Itemid)?'&Itemid='.$Itemid:'');
			$app->redirect(hikashop_completeLink('user&task=cpanel'.$url_itemid, false, true));
			return false;
		}

		$token = hikaInput::get()->getVar('order_token');
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			$app->enqueueMessage(JText::_('INVALID_REQUEST'));
			return false;
		}

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->get($order_id);
		if(empty($order)){
			$app->enqueueMessage(JText::sprintf('ORDER_X_NOT_FOUND', $order_id));
			return false;
		}

		$userClass = hikashop_get('class.user');
		$user = $userClass->get($order->order_user_id);
		if(empty($user)){
			$app->enqueueMessage(JText::_('INVALID_REQUEST'));
			return false;
		}

		if(empty($user->user_cms_id) || (int)$user->user_cms_id == 0){
			if(empty($order->order_token) || $token != $order->order_token){
				$app->enqueueMessage(JText::_('INVALID_REQUEST'));
				return false;
			}
		}else{
			$app->enqueueMessage(JText::_('USER_ACCOUNT_ALREADY_CREATED'));
			return false;
		}
		return $order;
	}

	public function cpanel() {
		if(!$this->_checkLogin())
			return true;
		hikaInput::get()->set('layout', 'cpanel');
		return parent::display();
	}

	function form() {
		$user = JFactory::getUser();
		if($user->guest) {
			hikaInput::get()->set('layout', 'form');
			return $this->display();
		}

		$app = JFactory::getApplication();
		$config = hikashop_config();
		$url = $config->get('redirect_url_when_registration_form_access_while_already_logged', '');
		if(empty($url)) {
			global $Itemid;
			$url_itemid=(!empty($Itemid)?'&Itemid='.$Itemid:'');
			$url = hikashop_completeLink('user&task=cpanel'.$url_itemid, false, true);
		} else {
			$url = hikashop_cleanURL($url);
		}
		$app->redirect($url);
		return false;
	}

	public function downloads() {
		if(!$this->_checkLogin())
			return true;
		hikaInput::get()->set('layout', 'downloads');
		return parent::display();
	}

	protected function _checkLogin() {
		$user = JFactory::getUser();
		if(!$user->guest)
			return true;

		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));

		global $Itemid;
		$url = '';
		if(!empty($Itemid))
			$url = '&Itemid='.$Itemid;

		$url = 'index.php?option=com_users&view=login'.$url;
		$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('', false))), false));
		return false;
	}

	public function activate() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$usersConfig = JComponentHelper::getParams('com_users');
		$userActivation = (int)$usersConfig->get('useractivation');
		$allowUserRegistration = (int)$usersConfig->get('allowUserRegistration');

		if($user->get('id')) {
			$app->redirect(hikashop_completeLink('checkout',false,true));
		}

		if($allowUserRegistration == 0 || $userActivation == 0) {
			$app->enqueueMessage(JText::_('Access Forbidden'), 'error');
			return false;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_user',JPATH_SITE);
		jimport('joomla.user.helper');

		$activation = hikashop_getEscaped(hikaInput::get()->getVar('activation', '', '', 'alnum'));

		if(empty($activation)) {
			$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_NOT_FOUND'));
			return false;
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
			$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_NOT_FOUND'));
			return false;
		}

		$app->enqueueMessage(JText::_('HIKA_REG_ACTIVATE_COMPLETE'));

		$id = hikaInput::get()->getInt('id', 0);
		$userClass = hikashop_get('class.user');
		$user = $userClass->get($id);

		if($id && file_exists(JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.php') && $userActivation < 2) {
			$userClass->addAndConfirmUserInCB($user);
		}

		$infos = hikaInput::get()->getVar('infos', '');

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;

		if(!empty($infos) && function_exists('json_decode')) {
			$infos = json_decode(base64_decode($infos), true);
			if(empty($infos['pass']) && !empty($infos['passwd']))
				$infos['pass'] = $infos['passwd'];
			JPluginHelper::importPlugin('user');
			if($userActivation < 2 && !empty($infos['pass']) && !empty($infos['username']) && $userClass->login($infos['username'], $infos['pass'])) {
				$page = hikaInput::get()->getString('page', 'checkout');
				if($page == 'checkout') {
					$app->redirect(hikashop_completeLink('checkout'.$url_itemid, false, true));
				} else {
					hikaInput::get()->set('layout', 'activate');
					return parent::display();
				}
			} elseif($userActivation >= 2) {
				$app->enqueueMessage(JText::_('HIKA_ADMIN_CONFIRM_ACTIVATION'));
			}
		}

		$url = 'index.php?option=com_users&view=login'.$url_itemid;
		$app->redirect(JRoute::_($url, false));
	}
}
