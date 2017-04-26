<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$hikashop_config =& hikashop_config();
if($hikashop_config->get('checkout_legacy', 0)) {
	require_once dirname(__FILE__) . '/checkout_legacy.php';
} else {
	class checkoutLegacyController extends hikashopController {}
}

class checkoutController extends checkoutLegacyController {
	public $display = array(
		'show', 'showblock', '',
		'step', 'state', 'notice', 'notify',
		'activate', 'submitblock',
		'submitstep', 'termsandconditions',
		'confirm','after_end',
		'activate_page',
	);
	public $modify_views = array();
	public $add = array();
	public $modify = array();
	public $delete = array();

	protected $config = null;
	protected $app = null;
	protected $dispatcher = null;
	protected $checkout_workflow = null;
	protected $steps = null;
	protected $redirect_url = null;

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		$this->config =& hikashop_config();
		$this->app = JFactory::getApplication();

		if($skip)
			return;

		if($this->config->get('checkout_legacy', 0))
			$this->registerDefaultTask('step');
		else
			$this->registerDefaultTask('show');

		if($this->config->get('checkout_legacy', 0))
			return;

		$cart_id = JRequest::getInt('cart_id', 0);

		hikashop_get('helper.checkout');
		$checkoutHelper = hikashopCheckoutHelper::get($cart_id);
		$this->workflow = $checkoutHelper->checkout_workflow;
	}

	public function display($cachable = false, $urlparams = array()) {
		$document = JFactory::getDocument();
		$view = $this->getView('', $document->getType(), '');
		if($view->getLayout() == 'default' && JRequest::getString('layout', '') != '')
			$view->setLayout(JRequest::getString('layout'));
		return parent::display($cachable, $urlparams);
	}

	public function termsandconditions() {
		JRequest::setVar('layout', 'termsandconditions');
		return $this->display();
	}

	public function step() {
		if($this->config->get('checkout_legacy', 0))
			return parent::step();
		return $this->show();
	}

	public function state() {
		if(!headers_sent())
			header('Content-Type:text/html; charset=utf-8');

		$namekey = JRequest::getCmd('namekey', '');
		$field_namekey = JRequest::getString('field_namekey', '');
		$field_id = JRequest::getString('field_id', '');
		$field_type = JRequest::getString('field_type', '');

		$zoneClass = hikashop_get('class.zone');
		echo $zoneClass->getStateDropdownContent($namekey, $field_namekey, $field_id, $field_type);
		exit;
	}

	public function show() {
		if($this->config->get('checkout_legacy', 0))
			return parent::step();

		hikashop_nocache();

		$checkoutHelper = hikashopCheckoutHelper::get();

		$cart = $checkoutHelper->getCart();
		if(empty($cart) || empty($cart->cart_id) || empty($cart->products)) {
			if(!empty($cart->messages)) {
				foreach($cart->messages as $msg) {
					$this->app->enqueueMessage($msg['msg'], $msg['type']);
				}
			}
			$this->setRedirect($checkoutHelper->getRedirectUrl(), JText::_('CART_EMPTY'));
			return true;
		}

		global $Itemid;
		$checkout_itemid = $Itemid;
		$itemid_for_checkout = (int)$this->config->get('checkout_itemid', 0);
		if(!empty($itemid_for_checkout) && $checkout_itemid != $itemid_for_checkout && (int)$this->app->getUserState('com_hikashop.checkout_itemid', 0) > 0) {
			$checkout_itemid = $itemid_for_checkout;
			$this->app->setUserState('com_hikashop.checkout_itemid', $itemid_for_checkout);
		} else if((int)$this->app->getUserState('com_hikashop.checkout_itemid', 0) > 0)
			$this->app->setUserState('com_hikashop.checkout_itemid', 0);

		$ssl = false;
		if(( (int)$this->config->get('force_ssl', 0) == 1 || $this->config->get('force_ssl', 0) == 'url') && $this->app->getUserState('com_hikashop.ssl_redirect') != 1 && !hikashop_isSSL()) {
			$ssl = true;
			$this->app->setUserState('com_hikashop.ssl_redirect', 1);
		}

		if($ssl || $checkout_itemid != $Itemid) {
			if($ssl && $this->config->get('force_ssl', 0) == 'url') {
				$url = str_replace('http://', 'https://', $this->config->get('force_ssl_url'));
				if(strpos($url, 'https://') === false)
					$url = 'https://' . $url;

				$requestUri = $_SERVER['PHP_SELF'];
				$str_start = strpos($requestUri, 'index.php');
				if($str_start > 0)
					$requestUri = substr($requestUri, $str_start - 1, strlen($requestUri));
				if(!empty($_SERVER['QUERY_STRING']))
					$requestUri = rtrim($requestUri, '/') . '?' . $_SERVER['QUERY_STRING'];

				$this->app->redirect($url . $requestUri);
				return true;
			}

			$url = '';

			$cart_id = JRequest::getInt('cart_id', 0);
			$url .= (!empty($cart_id)) ? '&cart_id='.$cart_id : '';

			$url .= ($checkout_itemid != $Itemid) ? ('&Itemid=' . $checkout_itemid) : '';
			$this->setRedirect(JRoute::_('index.php?option=' . HIKASHOP_COMPONENT . '&ctrl=checkout' . $url, false, $ssl));
			return true;
		}

		if($checkoutHelper->isStoreClosed()) {
			JRequest::setVar('layout', 'shop_closed');
			return $this->display();
		}

		$cart_id = $checkoutHelper->getCartId();
		$url_cart_param = ($cart_id > 0) ? '&cart_id='.$cart_id : '';

		$step = hikashop_getCID('step');
		if($step < 0 || $step >= count($this->workflow['steps']))
			$this->app->redirect(hikashop_completeLink('checkout&task=show'.$url_cart_param.'&Itemid='.$checkout_itemid, false, true));

		if($step > 0)
			$step--;

		$check = $this->checkWorkflowSteps($step);
		if($check !== true)
			$this->app->redirect(hikashop_completeLink('checkout&task=show&cid=' . ((int)$check + 1).$url_cart_param.'&Itemid='.$checkout_itemid, false, true));

		$check = $this->checkWorkflowEmptyStep($step);
		if($check !== true && $check !== false && $check > 0 && $check != $step) {
			$this->app->redirect(hikashop_completeLink('checkout&task=show&cid=' . ((int)$check + 1).$url_cart_param.'&Itemid='.$checkout_itemid, false, true));
		}

		JRequest::setVar('layout', 'show');
		return $this->display();
	}

	public function showblock() {
		hikashop_nocache();

		$checkoutHelper = hikashopCheckoutHelper::get();
		$tmpl = JRequest::getCmd('tmpl', '');


		JRequest::setVar('layout', 'showblock');
		if($tmpl == 'component' || $tmpl == 'ajax') {
			ob_end_clean();
			$this->display();
			exit;
		}
		return $this->display();
	}

	public function submitblock() {
		if(!JRequest::checkToken('request')){
			$tmpl = JRequest::getCmd('tmpl', '');
			if($tmpl == 'ajax'){
				echo '401';
				exit;
			}
			jexit('Invalid Token');
		}

		$checkoutHelper = hikashopCheckoutHelper::get();



		$workflow_step = hikashop_getCID();
		if($workflow_step > 0)
			$workflow_step--;
		$step = ($workflow_step + 1);

		$block_task = JRequest::getCmd('blocktask', '');
		$block_pos = JRequest::getInt('blockpos', 0);

		$workflow = $checkoutHelper->checkout_workflow;
		if(empty($workflow['steps'][$workflow_step]['content']))
			return false;
		if(empty($workflow['steps'][$workflow_step]['content'][$block_pos]))
			return false;
		if($workflow['steps'][$workflow_step]['content'][$block_pos]['task'] != $block_task)
			return false;

		$content = $workflow['steps'][$workflow_step]['content'][$block_pos];
		if(empty($content['params']))
			$content['params'] = array();

		$content['params']['src'] = array(
			'step' => $step,
			'workflow_step' => $workflow_step,
			'pos' => $block_pos
		);

		$cartMarkers = $checkoutHelper->getCartMarkers();

		$ctrl = hikashop_get('helper.checkout-' . $block_task);
		if(!empty($ctrl)) {
			$ret = $ctrl->validate($this, $content['params']);
		} else {
			$this->initDispatcher();
			$go_back = false;
			$original_go_back = false;
			$ret = $this->dispatcher->trigger('onAfterCheckoutStep', array($block_task, &$go_back, $original_go_back, &$this));
		}

		if(!empty($ret)) {
			if(!empty($checkoutHelper->redirectBeforeDisplay)) {
				$new_messages = array(array('msg' => $checkoutHelper->redirectBeforeDisplay, 'type' => 'message'));
				$cart = $checkoutHelper->getCart();
				if(!empty($cart->messages))
					$new_messages = array_merge($new_messages, $cart->messages);
				$session = JFactory::getSession();
				$old_messages = $session->get('application.queue', array());
				$session->set('application.queue', array_merge($old_messages, $new_messages));

				$checkoutHelper->addEvent('cart.empty', null);
			}

			$checkoutHelper->generateBlockEvents($cartMarkers, array(
				'src' => array('step' => $step, 'pos' => $block_pos)
			));

			$emptyStep = $this->checkWorkflowEmptyStep($workflow_step);
			if($emptyStep !== false && $emptyStep !== true && $emptyStep > 0 && $emptyStep != $workflow_step) {
				$checkoutHelper->addEvent('checkout.step.completed');
			}
		}

		return $this->showblock();
	}

	public function submitstep() {
		JRequest::checkToken('request') || jexit('Invalid Token');

		$checkoutHelper = hikashopCheckoutHelper::get();
		$step = hikashop_getCID();

		$workflow_step = hikashop_getCID();
		if($workflow_step > 0)
			$workflow_step--;
		$step = ($workflow_step + 1);

		$workflow = $checkoutHelper->checkout_workflow;
		if(empty($workflow['steps'][$workflow_step]['content']))
			return false;

		$cartMarkers = $checkoutHelper->getCartMarkers();

		$errors = 0;
		foreach($workflow['steps'][$workflow_step]['content'] as $block_pos => &$step_content) {
			if($step_content['task'] == 'confirm')
				continue;
			$ctrl = hikashop_get('helper.checkout-' . $step_content['task']);

			if(empty($step_content['params']))
				$step_content['params'] = array();
			$step_content['params']['src'] = array(
				'step' => $step,
				'workflow_step' => $workflow_step,
				'pos' => $block_pos
			);

			if(!empty($ctrl)) {
				$ret = $ctrl->validate($this, $step_content['params']);
			} else {
				$this->initDispatcher();
				$go_back = false;
				$original_go_back = false;
				$ret = $this->dispatcher->trigger('onAfterCheckoutStep', array($step_content['task'], &$go_back, $original_go_back, &$this));

				if(is_array($ret) && empty($ret))
					$ret = true;
				if($go_back == true)
					$ret = false;
			}
			if(!$ret)
				$errors++;
		}
		unset($step_content);

		if(!empty($checkoutHelper->redirectBeforeDisplay)){
			$this->app->enqueueMessage($checkoutHelper->redirectBeforeDisplay);
			$this->app->redirect($checkoutHelper->getRedirectUrl());
		}

		if($errors > 0)
			return $this->show();

		$newMarkers = $checkoutHelper->getCartMarkers();
		foreach($cartMarkers as $k => $v) {
			$check = true;
			foreach($workflow['steps'][$workflow_step]['content'] as $block_pos => $step_content) {
				$ctrl = hikashop_get('helper.checkout-' . $step_content['task']);
				if(!empty($ctrl)) {
					$check = $ctrl->checkMarker($k, $cartMarkers, $newMarkers, $this, $step_content['params']);
				} else {
				}
				if(!$check)
					break;
			}
			if($check && $v !== $newMarkers[$k])
				return $this->show();
		}

		global $Itemid;
		$checkout_itemid = (int)$Itemid;
		$itemid_for_checkout = (int)$this->config->get('checkout_itemid', 0);
		if(!empty($itemid_for_checkout) && $checkout_itemid != $itemid_for_checkout)
			$checkout_itemid = $itemid_for_checkout;

		$cart_id = $checkoutHelper->getCartId();
		$url_cart_param = ($cart_id > 0) ? '&cart_id='.$cart_id : '';

		$valid = $this->checkWorkflowSteps($workflow_step);
		if($valid !== true) {
			$this->app->redirect(hikashop_completeLink('checkout&task=show&cid='.($valid + 1).$url_cart_param.'&Itemid='.$checkout_itemid, false, true));
		}

		if($step + 1 == count($workflow['steps'])) {
			$cart = $checkoutHelper->getCart();
			$this->app->redirect(hikashop_completeLink('checkout&task=confirm&cart_id='.$cart->cart_id.'&Itemid='.$checkout_itemid, false, true));
		}

		$this->app->redirect(hikashop_completeLink('checkout&task=show&cid='.($step + 1).$url_cart_param.'&Itemid='.$checkout_itemid, false, true));
	}

	private function initDispatcher() {
		if($this->dispatcher !== null)
			return $this->dispatcher;

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		JPluginHelper::importPlugin('hikashopshipping');
		$this->dispatcher = JDispatcher::getInstance();
		return $this->dispatcher;
	}

	private function checkWorkflowSteps($step) {
		for($i = 0; $i < $step; $i++) {
			$validated = true;

			foreach($this->workflow['steps'][$i]['content'] as $k => $content) {
				$task = $content['task'];

				$ctrl = hikashop_get('helper.checkout-' . $task);
				if(!empty($ctrl)) {
					$ret = $ctrl->check($this, $content['params']);
					if($ret === false)
						$validated = false;
				} else {
					$this->initDispatcher();

					$go_back = ($validated == false);
					$original_go_back = ($validated == false);
					$this->dispatcher->trigger('onAfterCheckoutStep', array($task, &$go_back, $original_go_back, &$this));
					if($go_back)
						$validated = false;
				}
			}

			if(!$validated)
				return $i;
		}
		return true;
	}

	private function checkWorkflowEmptyStep($step) {
		if(empty($this->workflow['steps'][$step]['content']))
			return true;

		$empty = true;
		foreach($this->workflow['steps'][$step]['content'] as $k => $content) {
			$task = $content['task'];
			$ctrl = hikashop_get('helper.checkout-' . $task);
			if(!empty($ctrl)) {
				$ret = $ctrl->haveEmptyContent($this, $content['params']);
				if($ret !== true)
					$empty = false;
			} else {
				$empty = false;
			}
			if($empty == false)
				break;
		}
		if($empty == false)
			return true;
		return ($step + 1);
	}

	public function notify() {
		hikashop_nocache();
		ob_start();

		$plugin = JRequest::getCmd('notif_payment');
		$type = 'payment';

		if(empty($plugin)) {
			$plugin = JRequest::getCmd('notif_shipping');
			$type = 'shipping';
		}

		if(empty($plugin)) {
			$plugin = JRequest::getCmd('notif_hikashop');
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

	public function threedsecure() {
		hikashop_nocache();
		ob_start();

		$payment = JRequest::getCmd('3dsecure_payment');

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

	public function after_end() {
		$cartClass = hikashop_get('class.cart');
		$cartClass->cleanCartFromSession();

		JRequest::setVar('layout', 'after_end');
		return $this->display();
	}

	public function confirm() {
		$checkoutHelper = hikashopCheckoutHelper::get();

		global $Itemid;
		$checkout_itemid = (int)$Itemid;
		$itemid_for_checkout = (int)$this->config->get('checkout_itemid', 0);
		if(!empty($itemid_for_checkout) && $checkout_itemid != $itemid_for_checkout)
			$checkout_itemid = $itemid_for_checkout;

		$cart_id = $checkoutHelper->getCartId();
		if($cart_id == 0)
			$this->app->redirect(hikashop_completeLink('checkout&task=show&Itemid='.$checkout_itemid, false, true));

		$url_cart_param = '&cart_id='.$cart_id;

		$step = -1;
		if(!empty($this->workflow['steps']))
			$step = count($this->workflow['steps']);

		if($step < 0)
			$this->app->redirect(hikashop_completeLink('checkout&task=show'.$url_cart_param.'&Itemid='.$checkout_itemid, false, true));

		if($step > 0)
			$step--;

		if($checkoutHelper->isStoreClosed()) {
			JRequest::setVar('layout', 'shop_closed');
			return $this->display();
		}

		$check = $this->checkWorkflowSteps($step);

		if($check !== true)
			$this->app->redirect(hikashop_completeLink('checkout&task=show&cid=' . ((int)$check + 1).$url_cart_param.'&Itemid='.$checkout_itemid, false, true));

		$old_messages = $this->app->getMessageQueue();

		$cart = $checkoutHelper->getCart();
		$orderClass = hikashop_get('class.order');
		$order = $orderClass->createFromCart($cart->cart_id);

		if($order === false) {
			$new_messages = $this->app->getMessageQueue();
			if(count($new_messages) <= count($old_messages)) {
				$this->app->enqueueMessage('A plugin cancelled the update of the order product without displaying any error message.');
			}
			$this->app->redirect(hikashop_completeLink('checkout&task=show&cid=' . ((int)$step + 1).$url_cart_param.'&Itemid='.$checkout_itemid, false, true));
		}
		unset($old_messages);

		$this->app->setUserState('com_hikashop.order_id', $order->order_id);

		if(!empty($order->options->remove_cart) || $this->config->get('clean_cart') == 'order_created' || $order->order_status == $this->config->get('order_confirmed_status', 'confirmed') ) {
			$order_id = false;

			if(!empty($order->options->remove_cart))
				$order_id = (int)$order->order_id;

			$cartClass = hikashop_get('class.cart');
			$cartClass->cleanCartFromSession($order_id, $cart->cart_id);
		}

		JRequest::setVar('layout', 'end');
		return $this->display();
	}

	public function activate_page() {
		JRequest::setVar('layout', 'activate_page');
		return parent::display();
	}

	public function notice() {
		$cart_type = JRequest::getVar('cart_type', '', 'post');
		if(!empty($cart_type)) {
			$this->app->setUserState(HIKASHOP_COMPONENT.'.popup_cart_type', $cart_type);
		}
		JRequest::setVar('layout', 'notice');
		return $this->display();
	}

	public function initCart($reset = false) {
		if($this->config->get('checkout_legacy', 0))
			return parent::initCart($reset);

		$checkoutHelper = hikashopCheckoutHelper::get();
		return $checkoutHelper->getCart($reset);
	}
}
