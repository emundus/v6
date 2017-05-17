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

class hikashopCheckoutFieldsHelper extends hikashopCheckoutHelperInterface {
	public function check(&$controller, &$params) {
		if(!hikashop_level(2))
			return true;


		return true;
	}

	public function validate(&$controller, &$params, $data = array()) {
		if(!hikashop_level(2))
			return true;

		if(empty($data))
			$data = JRequest::getVar('checkout', array(), '', 'array');
		if(empty($data['fields']))
			return true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		if(!empty($cart->cart_fields) && is_string($cart->cart_fields))
			$cart->cart_fields = json_decode($cart->cart_fields);

		$old = new stdClass();
		$old->products = $cart->products;

		$fieldClass = hikashop_get('class.field');
		$orderData = $fieldClass->getInput('order', $old, 'msg', $data['fields']);

		if($orderData === false) {
			$messages = $fieldClass->messages;
			$fieldClass->messages = array();

			$cpt = 0;
			foreach($messages as $msg) {
				$checkoutHelper->addMessage('fields.'.($cpt++), $msg);
			}
			return false;
		}

		if(empty($cart->cart_fields)) {
			$cart->cart_fields = $orderData;
		} else {
			foreach($orderData as $k => $v) {
		 		$cart->cart_fields->$k = $v;
			}
		}

		$cartClass = hikashop_get('class.cart');
		$ret = $cartClass->save($cart);

		if(!$ret)
			return false;

		$checkoutHelper->getCart(true);
		return true;
	}

	public function display(&$view, &$params) {
		if(!hikashop_level(2))
			return;


		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		if(empty($cart->order_fields))
			return;

		$params['js'] = '';

		if(empty($view->fieldClass))
			$view->fieldClass = hikashop_get('class.field');

		$null = array();
		$view->fieldClass->addJS($null, $null, $null);

		$params['js'] .= $view->fieldClass->jsToggle($cart->order_fields, $cart->cart_fields, 0, 'hikashop_', array('return_data' => true, 'suffix_type' => '_'.$view->step.'_'.$view->block_position));

	}
}
