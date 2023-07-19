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
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutFieldsHelper extends hikashopCheckoutHelperInterface {

	public function getParams() {
		if(!hikashop_level(2))
			return '<span style="color:red">'.JText::_('ONLY_FROM_HIKASHOP_BUSINESS').'</span>';

		$type = hikashop_get('type.namebox');
		$this->params = array(
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
			'show_submit' =>  array(
				'name' => 'SHOW_SUBMIT_BUTTON',
				'type' => 'boolean',
				'default' => 0,
				'showon' => array(
					'key' => 'read_only',
					'values' => array(0)
				)
			),
			'fields' => array(
				'name' => 'FIELDS',
				'type' => 'namebox',
				'namebox' => 'field',
				'default' => '',
				'select' => hikashopNameboxType::NAMEBOX_MULTIPLE,
				'namebox_params' => array(
					'delete' => true,
					'returnOnEmpty' => false,
					'table' => 'order',
					'default_text' => '<em>'.JText::_('HIKA_ALL').'</em>',
					'url_params' => array(
						'TABLE' => 'order',
					),
				),
			),
		);
		return parent::getParams();
	}

	public function check(&$controller, &$params) {
		if(!hikashop_level(2))
			return true;


		return true;
	}

	public function validate(&$controller, &$params, $data = array()) {
		if(!hikashop_level(2))
			return true;
		if(!empty($params['read_only']))
			return true;

		if(empty($data))
			$data = hikaInput::get()->get('data', array(), 'array');
		$key = 'order_' . $params['src']['step'] . '_' .  $params['src']['pos'];

		if(empty($data))
			$data = hikaInput::get()->get('checkout', array(), 'array');
		if(!isset($data[$key]))
			$key = 'fields';

		if(!is_array($data))
			$data = array();
		if(!isset($data[$key]))
			$data[$key] = array();

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->getFullCart($cart->cart_id);
		if(!empty($cart->cart_fields) && is_string($cart->cart_fields))
			$cart->cart_fields = json_decode($cart->cart_fields);

		$old = new stdClass();
		$old->products = $cart->products;
		$old->cart_payment_id = @$cart->cart_payment_id;
		$old->cart_shipping_ids = @$cart->cart_shipping_ids;


		if(!empty($params['fields']) && is_string($params['fields']))
			$params['fields'] = explode(',',$params['fields']);
		$fieldClass = hikashop_get('class.field');
		$orderData = $fieldClass->getFilteredInput('order', $old, 'msg', $data[$key], false, '', @$params['fields']);

		if($orderData === false) {
			$messages = $fieldClass->messages;
			$fieldClass->messages = array();

			$cpt = 0;
			foreach($messages as $msg) {
				$checkoutHelper->addMessage('fields.'.($cpt++), array( $msg, 'error'));
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

	public function haveEmptyContent(&$controller, &$params) {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		if(empty($cart->order_fields))
			return true;
		return false;
	}

	public function display(&$view, &$params) {
		if(!hikashop_level(2))
			return;

		if(!isset($params['show_title']))
			$params['show_title'] = true;
		if(!isset($params['show_submit']))
			$params['show_submit'] = false;
		if(!isset($params['read_only']))
			$params['read_only'] = false;

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
