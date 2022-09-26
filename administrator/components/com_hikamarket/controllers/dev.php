<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class devMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array('listing', 'generate_orders'),
		'add' => array(),
		'edit' => array(),
		'modify' => array('apply','save'),
		'delete' => array()
	);

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function generate_orders() {
		return $this->renderingLayout('generate_orders');
	}

	public function apply() {
		$status = $this->store();
		$subtask = hikaInput::get()->getCmd('subtask', '');
		switch($subtask) {
			case 'generate_orders':
				return $this->renderingLayout('generate_orders');
		}
		return $this->listing();
	}

	public function store() {
		$subtask = hikaInput::get()->getCmd('subtask', '');
		switch($subtask) {
			case 'generate_orders':
				return $this->process_generate_orders();
		}
		return false;
	}

	protected function process_generate_orders() {
		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		$shopOrderClass = hikamarket::get('class.order');

		$formData = hikaInput::get()->get('data', array(), 'array');

		if(empty($formData['generate_orders'])) {
			$app->enqueueMessage('Invalid data', 'error');
			return false;
		}

		$user = (int)@$formData['generate_orders']['user'];
		if(empty($user))
			$user = hikamarket::loadUser(false);

		$currency_id = hikashop_getCurrency();

		$order_status = @$formData['generate_orders']['order_status'];
		if(empty($order_status))
			$order_status = $shopConfig->get('order_created_status', 'created');

		$products = $formData['generate_orders']['products'];
		hikamarket::toInteger($products);

		$order = new stdClass();
		$order->order_type = 'sale';
		$order->order_user_id = $user;
		$order->order_status = $order_status;
		$order->order_currency_id = $currency_id;

		$order->cart = new stdClass();
		$order->cart->products = $products;


		return true;
	}
}
