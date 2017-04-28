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
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutShippingHelper extends hikashopCheckoutHelperInterface {
	public function check(&$controller, &$params) {
		if(!empty($params['read_only']))
			return true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		if(!isset($cart->usable_methods) || $cart->usable_methods->shipping_valid == true)
			return true;

		$checkoutHelper->addMessage('shipping.checkfailed', array(
			JText::_('SELECT_SHIPPING'),
			'error'
		));

		return false;
	}

	public function validate(&$controller, &$params, $data = array()) {
		if(empty($data))
			$data = JRequest::getVar('checkout', array(), '', 'array');
		if(empty($data['shipping']))
			return true;


		$shipping_ids = array();
		foreach($data['shipping'] as $group => $shipping) {
			if(!isset($shipping['id']))
				continue;
			if(is_numeric($group))
				$group = (int)$group;
			if(is_numeric($shipping['id']))
				$shipping['id'] = (int)$shipping['id'];
			$shipping_ids[$group] = $shipping['id'];
		}

		if(empty($shipping_ids))
			return false;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		$shipping_price = $this->getShippingPrice($cart);

		$selectionOnly = JRequest::getInt('selectionOnly', 0);
		if($selectionOnly) {
			$cart_markers = $checkoutHelper->getCartMarkers();
		}

		$cartClass = hikashop_get('class.cart');
		$ret = $cartClass->updateShipping($cart->cart_id, $shipping_ids);

		$cart = $checkoutHelper->getCart(true);

		if($selectionOnly && JRequest::getCmd('tmpl', '') == 'ajax') {
			$data = array(
				'ret' => $ret,
				'events' => array(),
			);

			if($ret) {
				$data['events'][] = 'checkout.shipping.updated';

				$checkoutHelper->generateBlockEvents($cart_markers);
				$events = $checkoutHelper->getEvents();
				foreach($events as $evtName => $params) {
					$data['events'][] = $evtName;
				}
			}

			$new_shipping_price = $this->getShippingPrice($cart);
			if($new_shipping_price != $shipping_price)
				$data['events'][] = 'checkout.cart.updated';

			ob_end_clean();
			echo json_encode($data);
			exit;
		}

		if($ret) {
			$eventParams = null;
			if(!empty($params['src']))
				$eventParams = array('src' => $params['src']);
			$checkoutHelper->addEvent('checkout.shipping.updated', $eventParams);
		}

		return $ret;
	}

	protected function getShippingPrice(&$cart) {
		$shipping_price = 0.0;
		if(empty($cart->shipping))
			return $shipping_price;

		if(isset($cart->shipping->shipping_price))
			return (float)hikashop_toFloat($cart->shipping->shipping_price);

		foreach($cart->shipping as $s) {
			$shipping_price += hikashop_toFloat($s->shipping_price);
		}
		return $shipping_price;
	}

	public function display(&$view, &$params) {
		$params['show_shipping_products'] = true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		if(!$checkoutHelper->isMessages('shipping')) {
			$cart = $checkoutHelper->getCart();
			$this->addShippingErrors($cart);
		}
	}

	protected function addShippingErrors(&$cart) {
		if(!empty($cart->usable_methods->shipping_valid))
			return;

		$several_groups = isset($cart->shipping_groups) ? (count($cart->shipping_groups) > 1) : false;

		if(!$several_groups && !empty($cart->usable_methods->shipping))
			return;

		$checkoutHelper = hikashopCheckoutHelper::get();

		if(!$several_groups) {
			$msg = $this->getShippingErrorMessage(@$cart->usable_methods->shipping_errors);
			$checkoutHelper->addMessage('shipping.error', array($msg, 'error'));
			return;
		}

		$warehouse_order = 0;
		foreach($cart->shipping_groups as $group) {
			$warehouse_order++;
			if(!empty($group->shippings) || empty($group->errors))
				continue;

			$name = (!empty($group->name) ? $group->name : $warehouse_order);
			$msg = JText::sprintf('NO_SHIPPING_METHOD_FOUND_FOR_WAREHOUSE', $name) . '<br/>' .
				$this->getShippingErrorMessage($group->errors);

			$checkoutHelper->addMessage('shipping.warehouse_'.$warehouse_order, array($msg, 'error'));
		}
	}

	private function getShippingErrorMessage($errors) {
		if(empty($errors)) {
			return JText::_('NO_SHIPPING_METHOD_FOUND');
		}

		$shippingClass = hikashop_get('class.shipping');

		foreach($errors as $k => $err) {
			if(is_array($err)) {
				$n = array_keys($err);
				$n = array_shift($n);
				return $shippingClass->_displayErrors($n, array_shift($err), false);
			}
			return $shippingClass->_displayErrors($k, $err, false);
		}
	}
}
