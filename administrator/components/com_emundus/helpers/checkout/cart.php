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

class hikashopCheckoutCartHelper extends hikashopCheckoutHelperInterface {
	public function check(&$controller, &$params) {
		return true;
	}

	public function validate(&$controller, &$params, $data = array()) {
		if(empty($data))
			$data = JRequest::getVar('checkout', array(), '', 'array');
		if(empty($data['cart']))
			return true;
		if(empty($data['cart']['item']))
			return false;

		$items = array();
		foreach($data['cart']['item'] as $k => $v) {
			$items[] = array(
				'id' => (int)$k,
				'qty' => (int)$v
			);
		}

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		$msg_cpt = !empty($cart->messages) ? count($cart->messages) : 0;

		$cartClass = hikashop_get('class.cart');
		$ret = $cartClass->updateProduct($cart->cart_id, $items);

		$cart = $checkoutHelper->getCart(true);

		if(empty($cart->products)){
			$checkoutHelper->redirectBeforeDisplay = JText::_('CART_EMPTY');
		}

		if(!empty($cart->messages) && count($cart->messages) > $msg_cpt) {
			foreach($cart->messages as $i => $msg) {
				$checkoutHelper->addMessage('cart.error.'.$i, $msg);
			}
		}
		if(!$ret && !empty($cart->messages) && count($cart->messages) > $msg_cpt) {
			return false;
		}

		if(!$ret)
			return true;

		$eventParams = null;
		if(!empty($params['src']))
			$eventParams = array('src' => $params['src']);
		$checkoutHelper->addEvent('checkout.cart.updated', $eventParams);
		return true;
	}

	public function display(&$view, &$params) {
		$params['show_cart_image'] = $view->config->get('show_cart_image');
		$params['show_product_code'] = $view->config->get('show_code');
		$params['price_with_tax'] = $view->config->get('price_with_tax');
		$params['show_delete'] = true;

		$defaultParams = $view->config->get('default_params');
		$params['link_to_product_page'] = !empty($defaultParams['link_to_product_page']);

		if(!empty($params['readonly']))
			$params['status'] = true;

		$view->loadFields();
	}

}
