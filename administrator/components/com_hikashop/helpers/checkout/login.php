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
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutLoginHelper extends hikashopCheckoutHelperInterface {
	public function check(&$controller, &$params) {
		$checkoutHelper = hikashopCheckoutHelper::get();
		return $checkoutHelper->isLoggedUser();
	}

	public function haveEmptyContent(&$controller, &$params) {
		$user = JFactory::getUser();
		return !$user->guest;
	}

	public function validate(&$controller, &$params, $data = array()) {
		$user = JFactory::getUser();

		if(!$user->guest)
			return true;

		$app = JFactory::getApplication();
		$user_id = $app->getUserState(HIKASHOP_COMPONENT.'.user_id');
		if(!empty($user_id)){
			$logout = hikaInput::get()->getInt('hikashop_checkout_guest_logout', 0);
			if($logout) {
				$app->setUserState(HIKASHOP_COMPONENT.'.user_id', 0);
				hikashop_loadUser(false, true);

				$checkoutHelper = hikashopCheckoutHelper::get();
				$cart = $checkoutHelper->getCart();

				$cartClass = hikashop_get('class.cart');
				$cartClass->sessionToUser($cart->cart_id, $cart->session_id, 0, false);

				$cartToSave = $cartClass->get($cart->cart_id);
				$cartToSave->cart_billing_address_id = 0;
				$cartToSave->cart_shipping_address_ids = 0;
				$cartClass->save($cartToSave);

				$checkoutHelper->getCart(true);
			}
			return true;
		}
		JPluginHelper::importPlugin('user');

		$data = hikaInput::get()->getVar('data');
		if(isset($data['register']['registration_method'])) {
			$checkoutHelper = hikashopCheckoutHelper::get();
			$step = $params['src']['workflow_step'];
			$block_pos = $params['src']['pos'];
			$content =& $checkoutHelper->checkout_workflow['steps'][$step]['content'][$block_pos];
			if(empty($content['params']))
				$content['params'] = array();
			$content['params']['default_registration_view'] = $data['register']['registration_method'];
			unset($content);

			if($data['register']['registration_method'] == 'login')
				return $this->validateLogin($controller, $params);
			else
				return $this->validateRegistration($controller, $params);
		}

		$register = hikaInput::get()->getString('register','');
		$action = hikaInput::get()->getString('login_view_action','');

		if($action == 'register' || ($action != 'login' && !empty($register)))
			return $this->validateRegistration($controller, $params);

		$login = hikaInput::get()->get('login', array(), 'array');
		if($action == 'login' || (!empty($login['username']) && !empty($login['passwd'])))
			return $this->validateLogin($controller, $params);

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(!empty($formData['register']['email']))
			return $this->validateRegistration($controller, $params);

		$config =& hikashop_config();
		if($config->get('display_login', 1)) {
			$username = hikaInput::get()->request->getUsername('username', '');
			if(!empty($username))
				return $this->validateLogin($controller, $params);


			$checkoutHelper = hikashopCheckoutHelper::get();
			$checkoutHelper->addMessage('login', array(JText::_('PLEASE_FILL_FORM_BEFORE_PROCEEDING'),'error'));

			return;
		}
		return $this->validateRegistration($controller, $params);
	}

	protected function validateLogin(&$controller, &$params) {
		$login = hikaInput::get()->get('login', array(), 'array');
		if(empty($login))
			return false;

		$app = JFactory::getApplication();
		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		$jsession = JFactory::getSession();
		$old_session = $jsession->getId();

		$options = array(
			'return' => true,
			'remember' => !empty($login['remember'])
		);
		$credentials = array(
			'username' => (string)$login['username'],
			'password' => (string)$login['passwd']
		);

		$old_messages = $app->getMessageQueue();

		$error = $app->login($credentials, $options);

		$user = JFactory::getUser();

		if(JError::isError($error) || $user->guest) {
			$new_messages = $app->getMessageQueue();
			if(count($old_messages) < count($new_messages)) {
				$new_messages = array_slice($new_messages, count($old_messages));
				foreach($new_messages as $msg) {
					$checkoutHelper->addMessage('login', array(
						'msg' => $msg['message'],
						'type' => $msg['type']
					));
				}
			} else {
				$checkoutHelper->addMessage('login', array(JText::_('LOGIN_NOT_VALID'),'error'));
			}
			return false;
		}

		$jsession = JFactory::getSession();
		$new_session = $jsession->getId();

		$userClass = hikashop_get('class.user');
		$user_id = $userClass->getID($user->get('id'));
		if(!empty($user_id)) {
			$app->setUserState(HIKASHOP_COMPONENT.'.user_id', $user_id);

			$cartClass = hikashop_get('class.cart');
			if($cartClass->sessionToUser($cart->cart_id, $old_session, $user_id)) {
				$cartClass->get('reset_cache');
				$checkoutHelper->getCart(true);
			}
		}

		$params['login_done'] = true;

		$checkoutHelper->addEvent('checkout.user.updated', null);
		return true;
	}

	protected function validateRegistration(&$controller, &$params) {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		$config =& hikashop_config();

		$jsession = JFactory::getSession();
		$old_session = $jsession->getId();

		$formData = hikaInput::get()->get('data', array(), 'array');
		$data = array(
			'register' => null,
			'user' => null,
			'address' => null
		);

		if(isset($formData['register']))
			$data['register'] = $formData['register'];
		if(isset($formData['user']))
			$data['user'] = $formData['user'];
		if($config->get('address_on_registration', 1) && isset($formData['address']))
			$data['address'] = $formData['address'];

		$mode = $config->get('simplified_registration', 0);

		$display = $config->get('display_method', 0);
		if(!hikashop_level(1))
			$display = 0;

		if($display == 1) {
			$mode = explode(',', $mode);
			$formData = hikaInput::get()->get('data', array(), 'array');
			if(isset($formData['register']['registration_method']) && in_array($formData['register']['registration_method'], $mode)) {
				$mode = $formData['register']['registration_method'];
			} else {
				$mode = array_shift($mode);
			}
		}

		$userClass = hikashop_get('class.user');
		$ret = $userClass->register($data, $mode);

		if($ret === false || !isset($ret['status']))
			return false;

		$step = $params['src']['workflow_step'];
		$block_pos = $params['src']['pos'];
		$content =& $checkoutHelper->checkout_workflow['steps'][$step]['content'][$block_pos];

		if(empty($content['params']))
			$content['params'] = array();

		if(empty($ret['status']) || $ret['status'] == false) {
			if(!empty($ret['raise_error_msg']))
				$checkoutHelper->addMessage('login', array($ret['raise_error_msg'], 'error'));
			if(!empty($ret['raise_warning_msg']))
				$checkoutHelper->addMessage('login', array($ret['raise_warning_msg'], 'warning'));
			if(!empty($ret['messages'])) {
				foreach($ret['messages'] as $k => $msg) {
					$checkoutHelper->addMessage('login.'.$k, $msg);
				}
				$content['params']['registration_invalid_fields'] = array_keys($ret['messages']);
			}
			return false;
		}

		if(!empty($ret['status']) && $ret['status'] == true && !empty($ret['userActivation']) && $ret['userActivation'] > 0){
			if(!empty($ret['messages'])) {
				foreach($ret['messages'] as $k => $msg) {
					$checkoutHelper->addMessage('login.'.$k, $msg);
				}
			}
			$content['params']['registration'] = false;
			return false;
		}

		$app = JFactory::getApplication();
		if(isset($ret['user_id']))
			$app->setUserState(HIKASHOP_COMPONENT.'.user_id', $ret['user_id']);

		$config =& hikashop_config();
		$simplified = $config->get('simplified_registration',0);

		$display = $config->get('display_method',0);
		if(!hikashop_level(1))
			$display = 0;

		if($display == 1) {
			$simplified = explode(',', $simplified);
			if($config->get('display_login', 1))
				$simplified[] = 'login';

			if(count($simplified) == 1) {
				$simplified = array_shift($simplified);
			} else {
				$formData = hikaInput::get()->get('data', array(), 'array');
				$simplified = @$formData['register']['registration_method'];
			}
		}

		if($simplified != 2 && @$ret['userActivation'] == 0) {
			$options = array(
				'return' => true,
				'remember' => false
			);
			$credentials = array(
				'username' => (string)$ret['registerData']->username,
				'password' => (string)$ret['registerData']->password
			);
			$error = $app->login($credentials, $options);
			$juser = JFactory::getUser();

			if(!JError::isError($error) && !$juser->guest) {
				$userClass = hikashop_get('class.user');
				$user_id = $userClass->getID($juser->get('id'));
				if(!empty($user_id)) {
					$app->setUserState(HIKASHOP_COMPONENT.'.user_id', $user_id);

					$cartClass = hikashop_get('class.cart');
					if($cartClass->sessionToUser($cart->cart_id, $old_session, $user_id)) {
						$cartClass->get('reset_cache');
						$checkoutHelper->getCart(true);
					}
				}

				$checkoutHelper->addEvent('checkout.user.updated', null);
			} else {
				$params['register_done'] = true;
			}
		}
		if($simplified == 2) {
			$params['register_done'] = true;
		}

		return $ret['status'];
	}

	public function display(&$view, &$params) {
		$params['show_login'] = $view->config->get('display_login', 1);

		$params['current_login'] = hikashop_loadUser(true);
		$view->mainUser = JFactory::getUser();

		$checkoutHelper = hikashopCheckoutHelper::get();
		if($checkoutHelper->isLoggedUser())
			return;

		if(!isset($params['registration']))
			$params['registration'] = true;

		$params['display_method'] = 0;

		$joomla_params = JComponentHelper::getParams('com_users');
		if(((int)$joomla_params->get('allowUserRegistration') == 0)) {
			$params['registration'] = strpos($view->config->get('simplified_registration', 0),'2')!==false;
			$params['registration_not_allowed'] = true;
		}

		if(!empty($params['registration'])) {
			$this->loadRegistrationparams($view, $params);

			$this->initRegistration($view, $params);
		}
	}

	protected function loadRegistrationparams(&$view, &$params) {
		$params['registration_email_confirmation'] = $view->config->get('show_email_confirmation_field', 0);
		$params['address_on_registration'] = $view->config->get('address_on_registration', 1);
		$params['affiliate_registration'] = $view->config->get('affiliate_registration', 0);
		$params['user_group_registration'] = $view->config->get('user_group_registration', '');
		if(!isset($params['default_registration_view']))
			$params['default_registration_view'] = $view->config->get('default_registration_view', '');

		$params['display_method'] = 0;
		$params['registration_registration'] = true;
		$params['registration_count'] = 1;

	}

	protected function initRegistration(&$view, &$params) {
		$simplified_registration = $view->config->get('simplified_registration', 0);

		$params['js'] = '';

		$jversion = preg_replace('#[^0-9\.]#i','', JVERSION);
		if(version_compare($jversion, '3.4.0', '>='))
			JHTML::_('behavior.formvalidator');
		else
			JHTML::_('behavior.formvalidation');

		$data = @$_SESSION['hikashop_main_user_data'];
		if(!empty($data)) {
			if(empty($view->mainUser))
				$view->mainUser = new stdClass();
			foreach($data as $key => $val) {
				$view->mainUser->$key = $val;
			}
		}

		$view->user = @$_SESSION['hikashop_user_data'];

		if(empty($view->fieldsClass))
			$view->fieldsClass = hikashop_get('class.field');
		$view->extraFields['user'] = $view->fieldsClass->getFields('frontcomp', $view->user, 'user');

		$params['js'] .= $view->fieldsClass->jsToggle($view->extraFields['user'], $view->user, 0, 'hikashop_', array('return_data' => true, 'suffix_type' => '_'.$view->step.'_'.$view->block_position));

		$check_values = array('user' => $view->user);

		if(!empty($params['address_on_registration'])) {
			$view->address = @$_SESSION['hikashop_address_data'];
			$view->extraFields['address'] = $view->fieldsClass->getFields('frontcomp', $view->address, 'address');
			$params['js'] .= $view->fieldsClass->jsToggle($view->extraFields['address'], $view->address, 0, 'hikashop_', array('return_data' => true, 'suffix_type' => '_'.$view->step.'_'.$view->block_position));
			$check_values['address'] = $view->address;
		}
	}

	public function checkMarker($markerName, $oldMarkers, $newMarkers, &$controller, $params) {
		if(!in_array($markerName, array('billing_address', 'shipping_address', 'user')))
			return true;

		if(!empty($params['register_done'])){
			if(!isset($params['address_on_registration'])){
				$config = hikashop_config();
				$params['address_on_registration'] = (int)$config->get('address_on_registration', 1);
			}
			if($params['address_on_registration'])
				return false;

			$checkoutHelper = hikashopCheckoutHelper::get();
			$workflow = $checkoutHelper->checkout_workflow;
			foreach($workflow['steps'] as $step) {
				foreach($step['content'] as $step_content) {
					if($step_content['task'] == 'address')
						return true;
				}
			}
			return false;
		}

		return true;
	}
}
