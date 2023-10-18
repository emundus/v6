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
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutShippingHelper extends hikashopCheckoutHelperInterface {
	protected $params = array(
		'read_only' =>  array(
			'name' => 'READ_ONLY',
			'type' => 'boolean',
			'default' => 0
		),
		'show_title' =>  array(
			'name' => 'SHOW_TITLE',
			'type' => 'boolean',
			'default' => 1
		),
		'show_shipping_products' => array(
			'name' => 'MULTI_GROUP_PRODUCT_DISPLAY',
			'type' => 'boolean',
			'default' => 1,
		),
		'shipping_selector' => array(
			'name' => 'HIKASHOP_CHECKOUT_DISPLAY_SELECTOR',
			'type' => 'radio',
			'tooltip' => 'checkout_shipping_selector',
			'default' => 1,
			'showon' => array(
				'key' => 'read_only',
				'values' => array(0)
			)
		),
		'price_with_tax' => array(
			'name' => 'PRICE_WITH_TAX',
			'type' => 'inherit',
			'default' => -1,
		),
		'display_errors' =>  array(
			'name' => 'DISPLAY_ERRORS',
			'type' => 'boolean',
			'default' => 1,
			'tooltip' => 'shipping_display_errors',
		),
	);

	public function getParams() {
		$config = hikashop_config();
		$values = array(
			JHTML::_('select.option', 1, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_LIST')),
			JHTML::_('select.option', 2, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_DROPDOWN'))
		);
		$this->params['shipping_selector']['values'] = $values;

		$this->params['price_with_tax']['values'] = array(
			JHTML::_('select.option', '2', JText::_('WIZARD_BOTH'))
		);

		return parent::getParams();
	}

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


	public function haveEmptyContent(&$controller, &$params) {
		$checkoutHelper = hikashopCheckoutHelper::get();
		if(!$checkoutHelper->isShipping())
			return true;
		return false;
	}

	public function validate(&$controller, &$params, $data = array()) {
		if(!empty($params['read_only']))
			return true;

		if(empty($data))
			$data = hikaInput::get()->get('checkout', array(), 'array');
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

		if(count($shipping_ids) == 0)
			return false;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		$shipping_price = $this->getShippingPrice($cart);

		if(empty($_POST['selectionOnly'])){
			foreach($cart->shipping_groups as $group_id => $group_info) {
				if(empty($group->shippings) && !empty($group_info->no_weight) && empty($group_info->errors)) {
					continue;
				}
				$group_check = false;
				foreach($shipping_ids as $ship_group_id => $ship_id){
					if($ship_group_id == $group_id)
						$group_check = true;
				}
				if(!$group_check)
					return false;
			}
		}

		$selectionOnly = hikaInput::get()->getInt('selectionOnly', 0);
		if($selectionOnly) {
			$cart_markers = $checkoutHelper->getCartMarkers();
		}

		$cartClass = hikashop_get('class.cart');
		$ret = $cartClass->updateShipping($cart->cart_id, $shipping_ids);

		$cart = $checkoutHelper->getCart(true);

		if($ret && !empty($data['shipping']['custom'])) {
			$checkout_custom = array();
			foreach($shipping_ids as $group => $id) {
				if(!isset($data['shipping']['custom'][$group][$id]))
					continue;

				$warehouse_struct = $group;
				if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $group, $tmp))
					$warehouse_struct = array_combine($tmp[1], $tmp[2]);

				$shipping = null;
				foreach($cart->shipping as $s) {
					if($s->shipping_id != $id || ($s->shipping_warehouse_id !== $warehouse_struct && $s->shipping_warehouse_id !== $group))
						continue;
					if(empty($s->custom_html))
						continue;

					$plugin = hikashop_import('hikashopshipping', $s->shipping_type);
					$ret = $plugin->onShippingCustomSave($cart, $s, $group, $data['shipping']['custom'][$group][$id]);
					if($ret === false)
						break;

					if(!isset($checkout_custom[ $group ]))
						$checkout_custom[$group] = array();
					if(!isset($checkout_custom[ $group ]))
						$checkout_custom[$group][$id] = array();
					$checkout_custom[$group][$id] = $ret;
				}
			}
			$cartClass->updateShippingCustomData($cart->cart_id, $checkout_custom);
		}

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		if($selectionOnly && in_array($tmpl, array('ajax', 'raw', 'component'))) {
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
		if(!isset($params['show_shipping_products']))
			$params['show_shipping_products'] = true;
		if(!isset($params['read_only']))
			$params['read_only'] = false;
		if(!isset($params['show_title']))
			$params['show_title'] = true;
		if(!isset($params['shipping_selector']))
			$params['shipping_selector'] = 0;
		if($params['read_only'])
			$params['shipping_selector'] = 0;
		if(!isset($params['display_errors']))
			$params['display_errors'] = true;


		if(!isset($params['price_with_tax']))
			$params['price_with_tax'] = -1;
		if($params['price_with_tax'] == -1) {
			$config = hikashop_config();
			$params['price_with_tax'] = $config->get('price_with_tax', 0);
		}

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

			$msg = $this->getShippingErrorMessage($group->errors);
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
