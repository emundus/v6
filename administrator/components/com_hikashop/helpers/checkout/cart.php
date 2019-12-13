<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutCartHelper extends hikashopCheckoutHelperInterface {
	protected $params = array(
		'readonly' =>  array(
			'name' => 'READ_ONLY',
			'type' => 'boolean',
			'default' => 0
		),
		'show_cart_image' => array(
			'name' => 'SHOW_IMAGE',
			'type' => 'inherit',
			'tooltip' => 'show_cart_image',
			'default' => -1
		),
		'link_to_product_page' => array(
			'name' => 'LINK_TO_PRODUCT_PAGE',
			'type' => 'inherit',
			'default' => -1
		),
		'show_product_code' => array(
			'name' => 'DISPLAY_CODE',
			'type' => 'inherit',
			'default' => -1
		),
		'show_price' => array(
			'name' => 'DISPLAY_PRICE',
			'type' => 'boolean',
			'default' => 1
		),
		'price_with_tax' => array(
			'name' => 'PRICE_WITH_TAX',
			'type' => 'inherit',
			'default' => -1,
			'showon' => array(
				'key' => 'show_price',
				'values' => array(1)
			)
		),
		'show_delete' => array(
			'name' => 'SHOW_CART_DELETE',
			'type' => 'boolean',
			'tooltip' => 'checkout_cart_delete',
			'default' => 1,
			'showon' => array(
				'key' => 'readonly',
				'values' => array(0)
			)
		),
	);

	public function getParams() {
		$config = hikashop_config();
		$this->params['show_cart_image']['inherit'] = $config->get('show_cart_image');
		$this->params['show_product_code']['inherit'] = $config->get('show_code');
		$this->params['price_with_tax']['inherit'] = $config->get('price_with_tax');
		return parent::getParams();
	}


	public function check(&$controller, &$params) {
		return true;
	}

	public function validate(&$controller, &$params, $data = array()) {
		if(empty($data))
			$data = hikaInput::get()->get('checkout', array(), 'array');
		if(empty($data['cart']))
			return true;
		if(empty($data['cart']['item']))
			return false;

		$items = array();
		foreach($data['cart']['item'] as $k => $v) {
			if((int)$v == 0 && !is_numeric($v))
				continue;
			$items[] = array(
				'id' => (int)$k,
				'qty' => (int)$v
			);
		}
		if(empty($items))
			return true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		$msg_cpt = !empty($cart->messages) ? count($cart->messages) : 0;

		$cartClass = hikashop_get('class.cart');
		$ret = $cartClass->updateProduct($cart->cart_id, $items);

		$cart = $checkoutHelper->getCart(true);

		if(empty($cart->products)) {
			$checkoutHelper->redirectBeforeDisplay = JText::_('CART_EMPTY');
		}

		if(!$ret && !empty($cart->messages) && count($cart->messages) > $msg_cpt) {
			return false;
		}

		if(!$ret)
			return true;

		if(!empty($params['src']['context']) && $params['src']['context'] == 'submitstep') {
			$checkoutHelper->addMessage('cart.updated', array(
				JText::_('CART_UPDATED'),
				'success'
			));
		}

		$eventParams = null;
		if(!empty($params['src']))
			$eventParams = array('src' => $params['src']);
		$checkoutHelper->addEvent('checkout.cart.updated', $eventParams);
		return true;
	}

	public function display(&$view, &$params) {
		if(!isset($params['show_cart_image']) || (int)$params['show_cart_image'] === -1)
			$params['show_cart_image'] = (int)$view->config->get('show_cart_image');
		if(!isset($params['show_product_code']) || (int)$params['show_product_code'] === -1)
			$params['show_product_code'] = (int)$view->config->get('show_code');
		if(!isset($params['price_with_tax']) || (int)$params['price_with_tax'] === -1)
			$params['price_with_tax'] = (int)$view->config->get('price_with_tax');
		if(!isset($params['show_price']))
			$params['show_price'] = true;
		if(!isset($params['show_delete']))
			$params['show_delete'] = true;

		if(!isset($params['link_to_product_page']) || (int)$params['link_to_product_page'] === -1) {
			$defaultParams = $view->config->get('default_params');
			$params['link_to_product_page'] = !empty($defaultParams['link_to_product_page']);
		}

		if(!empty($params['readonly']))
			$params['status'] = true;

		$view->loadFields();
		hikashop_loadJslib('notify');
		hikashop_loadJslib('translations');
	}

}
