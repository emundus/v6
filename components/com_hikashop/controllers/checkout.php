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
		'confirm','after_end','threedsecure',
		'privacyconsent',
		'activate_page',
	);
	public $modify_views = array();
	public $add = array();
	public $modify = array();
	public $delete = array();

	protected $config = null;
	protected $app = null;
	protected $dispatcher = null;

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

		$cart_id = hikaInput::get()->getInt('cart_id', 0);

		hikashop_get('helper.checkout');
		$checkoutHelper = hikashopCheckoutHelper::get($cart_id);
		$this->workflow = $checkoutHelper->checkout_workflow;
	}

	public function display($cachable = false, $urlparams = array()) {
		$document = JFactory::getDocument();
		$view = $this->getView('', $document->getType(), '');
		if($view->getLayout() == 'default' && hikaInput::get()->getString('layout', '') != '')
			$view->setLayout(hikaInput::get()->getString('layout'));
		return parent::display($cachable, $urlparams);
	}

	public function termsandconditions() {
		hikaInput::get()->set('layout', 'termsandconditions');
		return $this->display();
	}

	public function privacyconsent() {
		hikaInput::get()->set('layout', 'privacyconsent');
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

		$namekey = hikaInput::get()->getCmd('namekey', '');
		$field_namekey = hikaInput::get()->getString('field_namekey', '');
		$field_id = hikaInput::get()->getString('field_id', '');
		$field_type = hikaInput::get()->getString('field_type', '');

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
			$override = false;
			if($this->app->getUserState('com_hikashop.cart_empty_redirect')  > time()-1) {
				$this->app->setUserState('com_hikashop.cart_empty_redirect', 0);
				$override = true;
			} else {
				$this->app->setUserState('com_hikashop.cart_empty_redirect', time());
			}
			$this->setRedirect($checkoutHelper->getRedirectUrl($override), JText::_('CART_EMPTY'));
			return true;
		}
		$cart_id_param = hikaInput::get()->getInt('cart_id', 0);
		if(!empty($cart_id_param) && $cart_id_param != $checkoutHelper->getCartId()) {
			$override = false;
			if($this->app->getUserState('com_hikashop.cart_empty_redirect') > time()-1) {
				$this->app->setUserState('com_hikashop.cart_empty_redirect', 0);
				$override = true;
			} else {
				$this->app->setUserState('com_hikashop.cart_empty_redirect', time());
			}
			$this->setRedirect($checkoutHelper->getRedirectUrl($override), JText::_('CART_EMPTY'));
			return true;
		}


		$task = hikaInput::get()->getString('task', '');
		if($task != 'submitstep') {
			global $Itemid;
			$checkout_itemid = $Itemid;
			$itemid_for_checkout = (int)$this->config->get('checkout_itemid', 0);

			$lang = JFactory::getLanguage();
			$code = $lang->getTag();
			if(!empty($itemid_for_checkout) && $checkout_itemid != $itemid_for_checkout) {
				JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
				$associations = MenusHelper::getAssociations($Itemid);
				$menuClass = hikashop_get('class.menus');
				$menu = $menuClass->get($checkout_itemid);
				if($menu->link == 'index.php?option=com_hikashop&view=checkout&layout=show' && $menu->language == $code)
					$associations[$code] = $menu->id;
				if(!empty($associations) && !empty($associations[$code])) {
					$itemid_for_checkout = $associations[$code];
				}
			}

			if(!empty($itemid_for_checkout) && $checkout_itemid != $itemid_for_checkout && (int)$this->app->getUserState('com_hikashop.checkout_itemid.'.$code, 0) == 0) {
				$checkout_itemid = $itemid_for_checkout;
				$this->app->setUserState('com_hikashop.checkout_itemid.'.$code, $itemid_for_checkout);
			} else if((int)$this->app->getUserState('com_hikashop.checkout_itemid.'.$code, 0) > 0)
				$this->app->setUserState('com_hikashop.checkout_itemid.'.$code, 0);

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

				$menusClass = hikashop_get('class.menus');
				$valid_menu = $menusClass->loadAMenuItemId('checkout', 'show', $checkout_itemid);
				if(empty($valid_menu)) {
					$url .= '&ctrl=checkout';
				}
				$cart_id = hikaInput::get()->getInt('cart_id', 0);
				$url .= (!empty($cart_id)) ? '&cart_id='.$cart_id : '';
				$url .= ($checkout_itemid != $Itemid) ? ('&Itemid=' . $checkout_itemid) : '';
				$this->setRedirect(JRoute::_('index.php?option=' . HIKASHOP_COMPONENT . $url, false, $ssl));
				return true;
			}
		}

		if($checkoutHelper->isStoreClosed()) {
			hikaInput::get()->set('layout', 'shop_closed');
			return $this->display();
		}

		$url_cart_param = ($cart_id_param > 0) ? '&cart_id='.$cart_id_param : '';

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
			if((int)$check + 1 == count($this->workflow['steps'])) {
				$cart = $checkoutHelper->getCart();
				$this->app->redirect(hikashop_completeLink('checkout&task=confirm'.$url_cart_param.'&Itemid='.$checkout_itemid, false, true));
			}
			$this->app->redirect(hikashop_completeLink('checkout&task=show&cid=' . ((int)$check + 1).$url_cart_param.'&Itemid='.$checkout_itemid, false, true));
		}

		$this->app->setUserState('com_hikashop.cart_empty_redirect', 0);
		$this->app->setUserState('com_hikashop.checkout_itemid', 0);

		hikaInput::get()->set('layout', 'show');
		return $this->display();
	}

	public function showblock() {
		hikashop_nocache();

		$checkoutHelper = hikashopCheckoutHelper::get();
		$tmpl = hikaInput::get()->getCmd('tmpl', '');


		hikaInput::get()->set('layout', 'showblock');
		if(in_array($tmpl, array('component', 'ajax', 'raw'))) {
			ob_end_clean();
			echo hikashop_getHTML(function() {
				$this->display();
			});
			if(!headers_sent())
				header('X-Robots-Tag: noindex');
			$this->app->triggerEvent('onAfterRender');
			exit;
		}
		return $this->display();
	}

	public function submitblock() {
		if(!JSession::checkToken('request')) {
			$tmpl = hikaInput::get()->getCmd('tmpl', '');
			if(in_array($tmpl, array('ajax', 'raw', 'component'))) {
				echo '401';
				if(!headers_sent())
					header('X-Robots-Tag: noindex');
				exit;
			}
			jexit('Invalid Token');
		}

		$checkoutHelper = hikashopCheckoutHelper::get();



		$workflow_step = hikashop_getCID('step');
		if($workflow_step > 0)
			$workflow_step--;
		$step = ($workflow_step + 1);

		$block_task = hikaInput::get()->getCmd('blocktask', '');
		if(empty($block_task)) {
			echo 'Task could not be retrieved from input. Please check that you have the blocktask parameter in your request';
			return false;
		}

		$block_pos = hikaInput::get()->getInt('blockpos', 0);

		$workflow = $checkoutHelper->checkout_workflow;
		if(empty($workflow['steps'][$workflow_step]['content'])) {
			echo 'Workflow for step ' . $workflow_step . ' could not be found';
			return false;
		}
		if(empty($workflow['steps'][$workflow_step]['content'][$block_pos])) {
			echo 'Workflow for position ' . $block_pos . ' of step ' . $workflow_step . ' could not be found';
			return false;
		}
		if($workflow['steps'][$workflow_step]['content'][$block_pos]['task'] != $block_task) {
			echo 'Task "' . $block_task . '" incompatible with the task "' . $workflow['steps'][$workflow_step]['content'][$block_pos]['task'] . '" of the workflow for position ' . $block_pos . ' of step ' . $workflow_step . ' could not be found';
			return false;
		}

		$content = $workflow['steps'][$workflow_step]['content'][$block_pos];
		if(empty($content['params']))
			$content['params'] = array();

		$content['params']['src'] = array(
			'step' => $step,
			'workflow_step' => $workflow_step,
			'pos' => $block_pos,
			'context' => 'submitblock'
		);

		$cartMarkers = $checkoutHelper->getCartMarkers();

		$ctrl = hikashop_get('helper.checkout-' . $block_task);
		if(!empty($ctrl)) {
			$ret = $ctrl->validate($this, $content['params']);
		} else {
			$this->initDispatcher();
			$go_back = false;
			$original_go_back = false;
			$obj =& $this;
			$ret = $this->app->triggerEvent('onAfterCheckoutStep', array($block_task, &$go_back, $original_go_back, &$obj));
		}

		if(!empty($ret)) {
			if(!is_null($checkoutHelper->redirectBeforeDisplay)) {
				$new_messages = array(array('msg' => $checkoutHelper->redirectBeforeDisplay, 'type' => 'message'));
				$cart = $checkoutHelper->getCart();
				if(!empty($cart->messages))
					$new_messages = array_merge($new_messages, $cart->messages);
				$session = JFactory::getSession();
				$old_messages = $session->get('application.queue', array());
				$session->set('application.queue', array_merge($old_messages, $new_messages));

				$eventParams = null;
				if(!empty($checkoutHelper->modifiedProduct))
					$eventParams = array('src' => $content['params']['src'], 'product' => $checkoutHelper->modifiedProduct);
				$checkoutHelper->addEvent('cart.empty', $eventParams);
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
		JSession::checkToken('request') || die('Invalid Token');

		$checkoutHelper = hikashopCheckoutHelper::get();
		$step = hikashop_getCID('step');

		$workflow_step = hikashop_getCID('step');
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
				'pos' => $block_pos,
				'context' => 'submitstep'
			);

			if(!empty($ctrl)) {
				$ret = $ctrl->validate($this, $step_content['params']);
			} else {
				$this->initDispatcher();
				$go_back = false;
				$original_go_back = false;
				$obj =& $this;
				$ret = $this->app->triggerEvent('onAfterCheckoutStep', array($step_content['task'], &$go_back, $original_go_back, &$obj));

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
			if($k == 'plugins')
				continue;

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

		if(!empty($cartMarkers['plugins'])) {
			foreach($cartMarkers['plugins'] as $k => $v) {
				if($v === $newMarkers['plugins'][$k])
					continue;
				return $this->show();
			}
		}

		global $Itemid;
		$checkout_itemid = (int)$Itemid;
		$itemid_for_checkout = (int)$this->config->get('checkout_itemid', 0);
		if(!empty($itemid_for_checkout) && $checkout_itemid != $itemid_for_checkout)
			$checkout_itemid = $itemid_for_checkout;

		$cart_id_param = hikaInput::get()->getInt('cart_id', 0);
		$url_cart_param = ($cart_id_param > 0) ? '&cart_id='.$cart_id_param : '';

		$valid = $this->checkWorkflowSteps($workflow_step);
		if($valid !== true) {
			$url = $checkoutHelper->completeLink('cid='.($valid + 1).$url_cart_param, false, true, false, $checkout_itemid);
			$this->app->redirect($url);
		}

		if($step + 1 == count($workflow['steps'])) {
			$cart = $checkoutHelper->getCart();
			$this->app->redirect(hikashop_completeLink('checkout&task=confirm'.$url_cart_param.'&Itemid='.$checkout_itemid, false, true));
		}
		$url = $checkoutHelper->completeLink('cid='.($step + 1).$url_cart_param, false, true, false, $checkout_itemid);
		$this->app->redirect($url);
	}

	private function initDispatcher() {
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
	}

	private function checkWorkflowSteps($step) {
		for($i = 0; $i < $step; $i++) {
			$validated = true;

			foreach($this->workflow['steps'][$i]['content'] as $k => $content) {
				$task = $content['task'];

				if(empty($content['params']))
					$content['params'] = array();

				$content['params']['src'] = array(
					'step' => $i+1,
					'workflow_step' => $i,
					'pos' => $k,
					'context' => 'submitblock'
				);

				$ctrl = hikashop_get('helper.checkout-' . $task);
				if(!empty($ctrl)) {
					$ret = $ctrl->check($this, $content['params']);
					if($ret === false)
						$validated = false;
				} else {
					$this->initDispatcher();

					$go_back = ($validated == false);
					$original_go_back = ($validated == false);
					$obj =& $this;
					$this->app->triggerEvent('onAfterCheckoutStep', array($task, &$go_back, $original_go_back, &$obj));
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

	public function threedsecure() {
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

	public function after_end() {
		if($this->config->get('checkout_legacy', 0)) {
			return parent::after_end();
		}

		$order_id = hikaInput::get()->getInt('order_id');
		if(empty($order_id)) {
			$app = JFactory::getApplication();
			$order_id = $app->getUserState('com_hikashop.order_id');
		}

		$cartClass = hikashop_get('class.cart');
		$cartClass->cleanCartFromSession();

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->get($order_id);

		$order_token = hikaInput::get()->getString('order_token');
		if(empty($order_token)) {
			$app = JFactory::getApplication();
			$order_token = $app->getUserState('com_hikashop.order_token');
		}

		if(empty($order) || (hikashop_loadUser(false) != $order->order_user_id && $order->order_token != $order_token))
			return false;
		hikaInput::get()->set('layout', 'after_end');
		return $this->display();
	}

	public function confirm() {
		$checkoutHelper = hikashopCheckoutHelper::get();

		global $Itemid;
		$checkout_itemid = (int)$Itemid;
		$itemid_for_checkout = (int)$this->config->get('checkout_itemid', 0);
		if(!empty($itemid_for_checkout) && $checkout_itemid != $itemid_for_checkout)
			$checkout_itemid = $itemid_for_checkout;

		$step = -1;
		if(!empty($this->workflow['steps']))
			$step = count($this->workflow['steps']);

		if($step < 0)
			$this->app->redirect($checkoutHelper->completeLink('', false, true, false, $checkout_itemid));

		if($step > 0)
			$step--;

		if($checkoutHelper->isStoreClosed()) {
			hikaInput::get()->set('layout', 'shop_closed');
			return $this->display();
		}

		$cart = $checkoutHelper->getCart();
		if(empty($cart) || empty($cart->cart_id) || empty($cart->products)) {
			if(!empty($cart->messages)) {
				foreach($cart->messages as $msg) {
					$this->app->enqueueMessage($msg['msg'], $msg['type']);
				}
			}
			$this->app->setUserState('com_hikashop.cart_empty_redirect', 1);
			$this->setRedirect($checkoutHelper->getRedirectUrl(), JText::_('CART_EMPTY'));
			return true;
		}

		$check = $this->checkWorkflowSteps($step);

		if($check !== true)
			$this->app->redirect($checkoutHelper->completeLink('cid='.((int)$check + 1), false, true, false, $checkout_itemid));

		$old_messages = $this->app->getMessageQueue();

		$cart = $checkoutHelper->getCart();

		if(!empty($cart->messages)) {
			foreach($cart->messages as $msg) {
				$this->app->enqueueMessage($msg['msg'], $msg['type']);
			}
			$this->app->redirect($checkoutHelper->completeLink('cid='.((int)$step + 1), false, true, false, $checkout_itemid));
		}

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->createFromCart($cart->cart_id);

		if($order === false) {
			$new_messages = $this->app->getMessageQueue();
			if(count($new_messages) <= count($old_messages)) {
				$this->app->enqueueMessage('A plugin cancelled the update of the order creation without displaying any error message.');
			}
			$this->app->redirect($checkoutHelper->completeLink('cid='.((int)$step + 1), false, true, false, $checkout_itemid));
		}
		unset($old_messages);

		$this->app->setUserState('com_hikashop.order_id', $order->order_id);
		$this->app->setUserState('com_hikashop.order_token', @$order->order_token);
		hikaInput::get()->set('order_token', $order->order_token );

		if(!empty($order->options->remove_cart) || $this->config->get('clean_cart') == 'order_created' || $order->order_status == $this->config->get('order_confirmed_status', 'confirmed') ) {
			$order_id = false;

			if(!empty($order->options->remove_cart))
				$order_id = (int)$order->order_id;

			$cartClass = hikashop_get('class.cart');
			$cartClass->cleanCartFromSession($order_id, $cart->cart_id);
		}

		hikaInput::get()->set('layout', 'end');
		return $this->display();
	}

	public function activate_page() {
		hikaInput::get()->set('layout', 'activate_page');
		return parent::display();
	}

	public function notice() {
		$cart_type = hikaInput::get()->post->getVar('cart_type', '');
		if(!empty($cart_type)) {
			$this->app->setUserState(HIKASHOP_COMPONENT.'.popup_cart_type', $cart_type);
		}
		hikaInput::get()->set('layout', 'notice');
		return $this->display();
	}

	public function initCart($reset = false) {
		if($this->config->get('checkout_legacy', 0))
			return parent::initCart($reset);

		$checkoutHelper = hikashopCheckoutHelper::get();
		return $checkoutHelper->getCart($reset);
	}
}
