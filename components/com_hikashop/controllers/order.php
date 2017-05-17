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
class orderController extends hikashopController {
	var $modify = array();
	var $delete = array();
	var $modify_views = array();

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config,$skip);

		$this->display = array_merge($this->display, array(
			'invoice', 'cancel', 'download',
			'pay', 'cancel_order', 'reorder'
		));
	}

	public function authorize($task) {
		if($this->isIn($task, array('display'))) {
			return true;
		}
		return false;
	}

	protected function isLogged($message = true, $guest_authorized = true) {
		$user = JFactory::getUser();
		if(!$user->guest)
			return true;

		$hk_user_id = hikashop_loadUser(false);
		if(!empty($hk_user_id) && $guest_authorized)
			return true;

		$app = JFactory::getApplication();
		if($message)
			$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));

		global $Itemid;
		$suffix = (!empty($Itemid) ? '&Itemid=' . $Itemid : '');

		if(!HIKASHOP_J16) {
			$url = 'index.php?option=com_user&view=login';
		} else {
			$url = 'index.php?option=com_users&view=login';
		}

		$app->redirect(JRoute::_($url . $suffix . '&return='.urlencode(base64_encode(hikashop_currentUrl('', false))), false));
		return false;
	}

	protected function _check($message = true) {
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id))
			return $this->listing();

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->get($order_id);
		if(empty($order))
			return $this->listing();

		$userClass = hikashop_get('class.user');
		$user = $userClass->get($order->order_user_id);
		if(empty($user))
			return $this->listing();

		if(empty($user->user_cms_id) || (int)$user->user_cms_id == 0){
			$token = JRequest::getVar('order_token');
			if(empty($order->order_token) || $token != $order->order_token){
				return false;
			}
		}elseif(!$this->isLogged($message)){
			return false;
		}

		return true;
	}

	public function listing() {
		if(!$this->isLogged(true, false))
			return false;

		return parent::listing();
	}

	public function show() {
		if(!$this->_check())
			return false;

		return parent::show();
	}

	public function invoice() {
		if(!$this->_check())
			return false;

		JRequest::setVar('layout', 'invoice');
		return parent::display();
	}

	public function cancel_order() {
		$app = JFactory::getApplication();
		$order_id = hikashop_getCID('order_id');
		$order_id_in_session = (int)$app->getUserState(HIKASHOP_COMPONENT.'.order_id');

		$user_id = hikashop_loadUser(false);
		$connected = !empty($user_id);
		if(!$connected){
			if(!$order_id_in_session || ($order_id && $order_id_in_session != $order_id)){
				if(!$this->isLogged())
					return false;
			}
		}

		if(empty($order_id))
			$order_id = $order_id_in_session;

		if(empty($order_id))
			return false;

		$orderClass = hikashop_get('class.order');
		$user_id = hikashop_loadUser();
		$config =& hikashop_config();

		$order = $orderClass->get($order_id);

		if(!$connected && !empty($order->order_user_id))
			return false;

		$checkout = explode(',', $config->get('checkout'));
		$step = JRequest::getInt('step', 0);
		if(empty($step))
			$step = max(count($checkout) - 2, 0);

		$itemid_for_checkout = $config->get('checkout_itemid','0');
		$item = (!empty($itemid_for_checkout) ? '&Itemid='.(int)$itemid_for_checkout : '');

		$cancel_url =  hikashop_completeLink('checkout&step=' . $step . $item, false, true);

		if(empty($order) || $order->order_user_id != $user_id) {
			$redirect_url = JRequest::getVar('redirect_url');

			if(!empty($redirect_url) && !hikashop_disallowUrlRedirect($redirect_url))
				$cancel_url = $redirect_url;

			$app->redirect($cancel_url);
			return true;
		}

		$status = $config->get('cancelled_order_status');
		$unpaid_statuses = explode(',', trim($config->get('order_unpaid_statuses', 'created'), ','));
		$cancellable_statuses = explode(',', trim($config->get('cancellable_order_status'), ','));

		if(!empty($status) && (in_array($order->order_status, $unpaid_statuses) || in_array($order->order_status, $cancellable_statuses))) {
			$statuses = explode(',', trim($status, ','));

			$newOrder = new stdClass();
			$newOrder->order_status = reset($statuses);
			$newOrder->order_id = $order_id;
			$ret = $orderClass->save($newOrder);

			if($ret && JRequest::getInt('email', false)) {
				$order->order_status = $newOrder->order_status;

				$mailClass = hikashop_get('class.mail');
				$infos = null;
				$infos =& $order;
				$mail = $mailClass->get('order_cancel', $infos);
				if( !empty($mail) ) {
					$mail->subject = JText::sprintf($mail->subject, HIKASHOP_LIVE);
					$mail->dst_email = (!empty($infos->email) ? $infos->email : $config->get('from_email'));
					$mail->dst_name = (!empty($infos->name) ? $infos->name : $config->get('from_name'));

					$mailClass->sendMail($mail);
				}
			}
		}

		$db = JFactory::getDBO();
		$query = 'SELECT * FROM '.hikashop_table('payment').
			' WHERE payment_type = '.$db->Quote($order->order_payment_method).' AND payment_id = '.(int)$order->order_payment_id;
		$db->setQuery($query);
		$paymentData = $db->loadObjectList();

		$pluginsClass = hikashop_get('class.plugins');
		$pluginsClass->params($paymentData, 'payment');

		$paymentOptions = reset($paymentData);
		if(!empty($paymentOptions->payment_params->cancel_url)) {
			$cancel_url = $paymentOptions->payment_params->cancel_url;
		}

		$redirect_url = JRequest::getVar('redirect_url');
		if(!empty($redirect_url) && !hikashop_disallowUrlRedirect($redirect_url))
			$cancel_url = $redirect_url;

		$app->redirect($cancel_url);
		return true;
	}

	public function reorder() {
		if(!hikashop_level(1) || !$this->_check()) {
			return false;
		}

		$app = JFactory::getApplication();
		$config = hikashop_config();
		$order_id = hikashop_getCID('order_id');

		if(empty($order_id) || !$config->get('allow_reorder', 0))
			return false;

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->loadFullOrder($order_id);

		if(empty($order)) {
			$app->enqueueMessage(JText::_('ORDER_NOT_FOUND'), 'error');
			parent::listing();
			return false;
		}

		$cartClass = hikashop_get('class.cart');
		$products = $cartClass->cartProductsToArray( $order->products );

		$ret = $cartClass->addProduct(0, $products);
		if($ret === false) {
			parent::listing();
			return false;
		}

		if(is_array($ret) && @$ret['status'] === false && !empty($ret['errors'])) {
			$app->enqueueMessage(JText::_('HIKASHOP_SOME_PRODUCT_NOT_ADDED_TO_CART'));
		}

		if(!empty($order->order_discount_code)) {
			$cartClass->addCoupon(0, $order->order_discount_code);
		}

		$itemid_for_checkout = $config->get('checkout_itemid','0');
		$item = (!empty($itemid_for_checkout) ? '&Itemid='.(int)$itemid_for_checkout : '');
		$app->redirect( hikashop_completeLink('checkout'.$item, false, true) );
	}

	public function pay() {
		if(!$this->_check()) {
			return false;
		}

		$app = JFactory::getApplication();
		$config = hikashop_config();
		$user_id = hikashop_loadUser(false);

		if(!hikashop_level(1) || !$config->get('allow_payment_button', 1))
			return false;

		$order_id = hikashop_getCID('order_id');
		if(empty($order_id))
			return false;

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->loadFullOrder($order_id);

		if(empty($order) || empty($order->order_id)) {
			$app->enqueueMessage(JText::sprintf('ORDER_X_NOT_FOUND', $order_id), 'error');
			$app->redirect( hikashop_completeLink('order&task=listing', false, true) );
			return false;
		}

		if(isset($order->customer->user_cms_id) && (int)$order->customer->user_cms_id == 0){
			$token = JRequest::getVar('order_token');
			if(empty($order->order_token) || $token != $order->order_token){
				$app->enqueueMessage(JText::sprintf('ORDER_X_NOT_FOUND', $order_id), 'error');
				$app->redirect( hikashop_completeLink('order&task=listing', false, true) );
				return false;
			}
		}elseif($order->order_user_id != $user_id){
			$app->enqueueMessage(JText::sprintf('ORDER_X_NOT_FOUND', $order_id), 'error');
			$app->redirect( hikashop_completeLink('order&task=listing', false, true) );
		}

		$unpaid_statuses = explode(',', $config->get('order_unpaid_statuses', 'created'));
		if(!in_array($order->order_status, $unpaid_statuses)) {
			$app->enqueueMessage(JText::sprintf('ORDER_X_NOT_PAID_ANYMORE', $order->order_number));
			$app->redirect( hikashop_completeLink('order&task=listing', false, true) );
			return false;
		}

		$new_payment_method = JRequest::getVar('new_payment_method', null);
		$payment_change = $config->get('allow_payment_change', 1);
		if($payment_change && $new_payment_method === null) {
			JRequest::setVar('layout', 'pay');
			return $this->display();
		}

		if($payment_change && !empty($new_payment_method)) {
			$ret = $this->changePaymentMethod($order, $new_payment_method);
			if(!$ret)
				return false;
		}

		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_address', $order->order_shipping_address_id);
		$app->setUserState(HIKASHOP_COMPONENT.'.billing_address', $order->order_billing_address_id);

		$paymentPlugin = hikashop_import('hikashoppayment', $order->order_payment_method);

		if(empty($paymentPlugin)) {
			return false;
		}

		$paymentClass = hikashop_get('class.payment');
		$needCC = false;
		$paymentMethod = null;
		if( method_exists($paymentPlugin, 'needCC') ) {
			$paymentMethod = $paymentClass->get($order->order_payment_id);
			$needCC = $paymentPlugin->needCC($paymentMethod);
		}

		$app->setUserState(HIKASHOP_COMPONENT.'.order_id', $order_id);
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_method', $order->order_payment_method);
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_id', $order->order_payment_id);
		if(!empty($paymentMethod))
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_data', $paymentMethod);

		if(!empty($needCC) && !$paymentClass->readCC()) {
			JRequest::setVar('layout', 'pay');
			return $this->display();
		}
		if(!empty($paymentMethod) && !empty($paymentMethod->custom_html) && (JRequest::getInt('payment_custom_html', 0) == 0 || !JRequest::checkToken())) {
			JRequest::setVar('layout', 'pay');
			return $this->display();
		}

		$userClass = hikashop_get('class.user');
		$order->customer = $userClass->get($order->order_user_id);
		$order->cart =& $order;

		$price = new stdClass();
		$price->price_value_with_tax = $order->order_full_price;

		$order->cart->full_total = new stdClass();
		$order->cart->full_total->prices = array($price);

		$order->cart->total = new stdClass();

		if($config->get('group_options',0)){
			foreach($order->cart->products as $k => $product){
				if(!empty($product->order_product_option_parent_id)){
					foreach($order->cart->products as $k2 => $product2){
						if($product->order_product_option_parent_id == $product2->order_product_id){
							$product2->order_product_price += $product->order_product_price;
							$product2->order_product_tax += $product->order_product_tax;
							$product2->order_product_total_price_no_vat += $product->order_product_total_price_no_vat;
							$product2->order_product_total_price += $product->order_product_total_price;
						}
					}
				}
			}
		}

		$currencyClass = hikashop_get('class.currency');
		$currencyClass->calculateTotal($order->cart->products, $order->cart->total, $order->order_currency_id);

		if(bccomp($order->order_discount_price, 0, 5) !== 0) {
			$order->cart->coupon = new stdClass();
			$order->cart->coupon->discount_value =& $order->order_discount_price;
		}

		$paymentMethods = $paymentClass->getMethods($order->cart);

		if(!empty($paymentMethod) && !empty($paymentMethod->custom_html)) {
			$order->cart->payment = $paymentPlugin->onPaymentSave($order->cart, $paymentMethods, $order->order_payment_id);

			$app->setUserState( HIKASHOP_COMPONENT.'.payment_data', $paymentMethod);
			unset($methods);
		}

		if(!empty($order->order_shipping_method)) {
			$shippingClass = hikashop_get('class.shipping');
			$shippingMethods = $shippingClass->getMethods($order->cart);
			$shippingPlugin = hikashop_import('hikashopshipping', $order->order_shipping_method);
			if(!empty($shippingPlugin))
				$order->cart->shipping = $shippingPlugin->onShippingSave($order->cart, $shippingMethods, $order->order_shipping_id);
		}

		$old_order_status = $order->order_status;

		$do = true;
		if(method_exists($paymentPlugin, 'onBeforeOrderCreate'))
			$paymentPlugin->onBeforeOrderCreate($order, $do);

		if(!$do) {
			$app->enqueueMessage(JText::_('PAYMENT_REFUSED'), 'error');
			$app->redirect( hikashop_completeLink('order&task=listing', false, true) );
			return false;
		}

		if(empty($needCC)) {
			$itemid_for_checkout = (int)$config->get('checkout_itemid', 0);
			if($itemid_for_checkout) {
				global $Itemid;
				$Itemid = $itemid_for_checkout;
			}

			if(method_exists($paymentPlugin, 'onAfterOrderConfirm'))
				$paymentPlugin->onAfterOrderConfirm($order, $paymentMethods, $order->order_payment_id);

			$html = ob_get_clean();
			if(empty($html)) {
				$app->enqueueMessage('The payment method '.$order->order_payment_method.' does not handle payments after the order has been created');
				$app->redirect( hikashop_completeLink('order&task=listing', false, true) );
				return false;
			}
		}

		if($old_order_status != $order->order_status) {
			if(empty($order->history))
				$order->history = new stdClass();
			$order->history->history_notified = 1;

			$updateOrder = new stdClass();
			$updateOrder->order_id = $order->order_id;
			$updateOrder->order_status = $order->order_status;
			$updateOrder->order_payment_id = $order->order_payment_id;
			$updateOrder->order_payment_method = $order->order_payment_method;
			$updateOrder->history =& $order->history;

			$orderClass->save($updateOrder);
		}

		if(empty($needCC) && !empty($html)) {
			echo $html;
			return true;
		}

		$app->redirect( hikashop_completeLink('checkout&task=after_end', false, true) );
		return false;
	}

	protected function changePaymentMethod(&$order, $new_payment_method) {
		$new_payment_method = explode('_', $new_payment_method);
		$payment_id = array_pop($new_payment_method);
		$payment_method = implode('_', $new_payment_method);

		if($order->order_payment_id == $payment_id && $order->order_payment_method == $payment_method)
			return true;

		$pluginPaymentType = hikashop_get('type.plugins');
		$pluginPaymentType->type = 'payment';
		$pluginPaymentType->order = $order;
		$pluginPaymentType->preload(false);

		$methods = $pluginPaymentType->methods['payment'][(string)$order->order_id];
		$found = false;
		foreach($methods as $method) {
			if($method->payment_id != $payment_id || $method->payment_type != $payment_method)
				continue;
			$found = $method;
			break;
		}
		if(!$found) {
			$app->enqueueMessage(JText::_('INVALID_DATA'), 'error');
			return false;
		}

		$updateOrder = new stdClass();
		$updateOrder->order_id = $order->order_id;
		$updateOrder->order_payment_id = $payment_id;
		$updateOrder->order_payment_method = $payment_method;

		$paymentClass = hikashop_get('class.payment');
		$payment = $paymentClass->get($payment_id);
		if(!empty($payment->payment_params) && is_string($payment->payment_params)) {
			$payment->payment_params = hikashop_unserialize($payment->payment_params);
		}

		$full_price_without_payment = $order->order_full_price - $order->order_payment_price;


		$new_payment = $payment;
		$new_payment_price = $paymentClass->computePrice( $order, $new_payment, $full_price_without_payment, @$payment->payment_price, hikashop_getCurrency());
		$new_payment_tax = @$new_payment->payment_tax;
		$updateOrder->order_payment_price = $new_payment_price;
		$updateOrder->order_full_price = $full_price_without_payment + $new_payment_price + $new_payment_tax;

		$updateOrder->history = new stdClass();
		$updateOrder->history->history_payment_id = $payment_id;
		$updateOrder->history->history_payment_method = $payment_method;

		$orderClass = hikashop_get('class.order');
		$orderClass->save($updateOrder);

		$order->order_payment_id = $payment_id;
		$order->order_payment_method = $payment_method;
		$order->order_payment_price = $updateOrder->order_payment_price;
		$order->order_full_price = $updateOrder->order_full_price;

		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.cc_number', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.cc_month', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.cc_year', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.cc_CCV', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.cc_type', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.cc_owner', null);

		return true;
	}

	public function download() {
		$file_id = JRequest::getInt('file_id');
		if(empty($file_id)){
			$field_table = JRequest::getString('field_table');
			$field_namekey = base64_decode(urldecode(JRequest::getString('field_namekey')));
			$name = base64_decode(urldecode(JRequest::getString('name')));
			if(empty($field_table)||empty($field_namekey)||empty($name)){
				$app=JFactory::getApplication();
				$app->enqueueMessage(JText::_('FILE_NOT_FOUND'));
				return false;
			}else{
				$options = array(
					'thumbnail_x' => JRequest::getInt('thumbnail_x', 0),
					'thumbnail_y' => JRequest::getInt('thumbnail_y', 0)
				);
				$fileClass = hikashop_get('class.file');
				$fileClass->downloadFieldFile($name, $field_table, $field_namekey, $options);
				exit;
			}
		}

		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)) {
			parent::listing();
			return false;
		}

		$file_pos = JRequest::getInt('file_pos', 1);
		$order_token = JRequest::getVar('order_token', '');

		if(empty($order_token))
			$order_token = JRequest::getVar('email', '');

		$fileClass = hikashop_get('class.file');
		if(!$fileClass->download($file_id, $order_id, $file_pos, $order_token)) {
			switch($fileClass->error_type){
				case 'login':
					$this->_check(false);
					break;
				case 'no_order';
					parent::listing();
					break;
				default:
					parent::show();
					break;
			}
		}
		return true;
	}

	public function cancel() {
		$cancel_redirect = JRequest::getString('cancel_redirect');
		if(!empty($cancel_redirect)) {
			$cancel_redirect = urldecode($cancel_redirect);
			if(hikashop_disallowUrlRedirect($cancel_redirect))
				return false;
			$this->setRedirect($cancel_redirect);
			return true;
		}

		$cancel_url = JRequest::getString('cancel_url');
		if(!empty($cancel_url)) {
			$cancel_url = urldecode($cancel_url);
			if(hikashop_disallowUrlRedirect($cancel_url))
				return false;
			$this->setRedirect(base64_decode($cancel_url));
			return true;
		}

		$order_id = hikashop_getCID('order_id');
		if(!empty($order_id)) {
			return $this->listing();
		}

		global $Itemid;
		$url = (!empty($Itemid) ? '&Itemid='.$Itemid : '');
		$this->setRedirect(hikashop_completeLink('user'.$url, false, true));
	}

	public function getUploadSetting($upload_key, $caller = '') {
		if(empty($upload_key))
			return false;
		if(strpos($upload_key, '-') === false)
			return false;
		if(in_array($caller, array('galleryimage', 'galleryselect', 'image')))
			return false;

		list($field_table, $field_namekey) = explode('-', $upload_key, 2);

		$fieldClass = hikashop_get('class.field');
		$field = $fieldClass->getField($field_namekey, $field_table);

		if(empty($field) || ($field->field_type != 'ajaxfile' && $field->field_type != 'ajaximage'))
			return false;

		$map = JRequest::getString('field_map', '');
		if(empty($map))
			return false;

		$config = hikashop_config();
		$options = array(
			'upload_dir' => $config->get('uploadsecurefolder')
		);

		$type = ($field->field_type == 'ajaxfile') ? 'file' : 'image';

		return array(
			'limit' => 1,
			'type' => $type,
			'options' => $options,
			'extra' => array(
				'field_name' => $map,
				'delete' => empty($field->field_required),
				'uploader_id' => JRequest::getString('uploader_id', '')
			)
		);
	}

	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret) || empty($ret->name))
			return;

		if(empty($upload_key))
			return;
		if(strpos($upload_key, '-') === false)
			return;

		list($field_table, $field_namekey) = explode('-', $upload_key);

		$fieldClass = hikashop_get('class.field');
		$field = $fieldClass->getField($field_namekey, $field_table);

		if(empty($field) || ($field->field_type != 'ajaxfile' && $field->field_type != 'ajaximage'))
			return;

		$map = JRequest::getString('field_map', '');
		if(empty($map))
			return;

		if($field_table == 'item') {
			$app = JFactory::getApplication();
			$itemsData = $app->getUserState(HIKASHOP_COMPONENT.'.items_fields');
			if(empty($itemsData)) $itemsData = array();
			$newItem = new stdClass();
			$newItem->$field_namekey = $ret->name;
			$itemsData[] = $newItem;
			$app->setUserState(HIKASHOP_COMPONENT.'.items_fields', $itemsData);
		}

		if($field_table == 'order') {
			$app = JFactory::getApplication();
			$orderData = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_fields');
			if(empty($orderData)) $orderData = new stdClass();
			$orderData->$field_namekey = $ret->name;
			$app->setUserState(HIKASHOP_COMPONENT.'.checkout_fields', $orderData);
		}

		if(substr($field_table, 0, 4) == 'plg.') {
			$externalValues = array();
			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onTableFieldsLoad', array( &$externalValues ) );
			$found = false;
			foreach($externalValues as $external) {
				if($external->value == $field_table) {
					$found = true;
					break;
				}
			}
			if($found) {
				$app = JFactory::getApplication();
				$elemData = $app->getUserState(HIKASHOP_COMPONENT.'.plg_fields.' . substr($field_table, 4));
				if(empty($elemData)) $elemData = array();
				$newItem = new stdClass();
				$newItem->$field_namekey = $ret->name;
				$elemData[] = $newItem;
				$app->setUserState(HIKASHOP_COMPONENT.'.plg_fields.' . substr($field_table, 4), $elemData);
			}
		}

		if($field->field_type == 'ajaxfile')
			$ajaxFileClass = new hikashopFieldAjaxfile($fieldClass);
		else
			$ajaxFileClass = new hikashopFieldAjaximage($fieldClass);
		$ajaxFileClass->_manageUpload($field, $ret, $map, $uploadConfig, $caller);
	}
}
