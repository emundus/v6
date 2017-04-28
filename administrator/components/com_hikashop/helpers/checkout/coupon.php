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

class hikashopCheckoutCouponHelper extends hikashopCheckoutHelperInterface {
	public function check(&$controller, &$params) {
		return true;
	}

	public function validate(&$controller, &$params, $data = array()) {
		$checkout = JRequest::getVar('checkout', array(), '', 'array');
		$coupon = null;
		if(isset($checkout['coupon']) && is_string($checkout['coupon']))
			$coupon = $checkout['coupon'];
		$qty = 1;

		if(empty($coupon)) {
			if(isset($checkout['removecoupon']) && is_string($checkout['removecoupon']))
				$coupon = $checkout['removecoupon'];
			$qty = 0;
		}

		$coupon = trim($coupon);
		if(empty($coupon))
			return true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		$cartClass = hikashop_get('class.cart');

		$ret = false;
		if($qty == 1) {
			$ret = $cartClass->addCoupon($cart->cart_id, $coupon);
		} else {
			$ret = $cartClass->removeCoupon($cart->cart_id, $cart->cart_coupon);
		}

		if(!empty($ret))
			return true;

		$error_message = JRequest::getVar('coupon_error_message', '');
		if(empty($error_message))
			$error_message = JText::_('COUPON_NOT_VALID');

		$checkoutHelper->addMessage('coupon.invalid', array(
			$error_message,
			'error'
		));
		return false;
	}

	public function display(&$view, &$params) {
	}
}
