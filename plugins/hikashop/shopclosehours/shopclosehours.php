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
class plgHikashopShopclosehours extends hikashopPlugin {

	var $multiple = true;
	var $name = 'shopclosehours';

	var $pluginConfig = array(
		'categories' => array('HIKA_CATEGORIES', 'category'),
		'subcategories' => array('INCLUDING_SUB_CATEGORIES', 'boolean'),
		'store_open_day' => array('OPENS_ON', 'list',array(
			'0' => 'HIKA_ALL',
			'1' => 'MONDAY',
			'2' => 'TUESDAY',
			'3' => 'WEDNESDAY',
			'4' => 'THURSDAY',
			'5' => 'FRIDAY',
			'6' => 'SATURDAY',
			'7' => 'SUNDAY'
		)),
		'store_open_time' => array('OPENS_AT', 'time'),
		'store_close_day' => array('CLOSES_ON', 'list',array(
			'0' => 'HIKA_ALL',
			'1' => 'MONDAY',
			'2' => 'TUESDAY',
			'3' => 'WEDNESDAY',
			'4' => 'THURSDAY',
			'5' => 'FRIDAY',
			'6' => 'SATURDAY',
			'7' => 'SUNDAY'
		)),
		'store_close_time' => array('CLOSES_AT', 'time'),
		'on_add_to_cart' => array('CHECK_ON_ADD_TO_CART', 'boolean', false),
	);

	public function pluginConfigDisplay($fieldType, $data, $type, $paramsType, $key, $element){
		if($fieldType=='time'){
			$map = 'data['.$type.']['.$paramsType.']['.$key.']';
			$value = @$element->$paramsType->$key;
			return '<input type="text" style="width:50px" name="'.$map.'[hour]" placeholder="'.JText::_('HIKA_HH').'" value="'.@$value['hour'].'"/> : <input type="text" style="width:50px" name="'.$map.'[minute]" placeholder="'.JText::_('HIKA_MM').'" value="'.@$value['minute'].'"/>';
		}
	}

	public function onBeforeProductQuantityCheck(&$products, &$cart, &$options) {
		$this->loadRanges();
		if(empty($this->ranges)){
			return;
		}
		$add_to_cart_ranges = array();
		foreach($this->ranges as $range) {
			if(!empty($range->on_add_to_cart)) {
				$add_to_cart_ranges[] = $range;
			}
		}
		if(!count($add_to_cart_ranges))
			return;

		$productsForCheck = array();
		$productClass = hikashop_get('class.product');
		foreach($products as $k => &$product) {
			if(empty($product['qty']))
				continue;

			if(!empty($product['pid'])) {
				$id = (int)$cart->cart_products[ (int)$product['pid'] ]->product_id;
			} else {
				$id = $product['id'];
			}

			$data = $productClass->get($id);

			$p = new stdClass();
			$p->product_id = $id;
			$p->product_name = $data->product_name;
			$productsForCheck[] = $p;
		}

		$tmp = hikashop_copy($this->ranges);
		$this->ranges = $add_to_cart_ranges;
		$isClosed = $this->isShopClosed($productsForCheck);
		if(!$isClosed)
			return;

		foreach($products as $k => $p) {
			$products[$k]['qty'] = 0;
			unset($products[$k]['data']);
		}

		$messages = $this->getMessages();
		$this->ranges = $tmp;
		$cartClass = hikashop_get('class.cart');
		$cartClass->addMessage($cart, array('msg' => implode('<br/>', $messages), 'product_id' => $id, 'type' => 'error'));

	}

	public function onCheckoutWorkflowLoad(&$checkout_workflow, &$shop_closed, $cart_id) {
		if(!hikashop_level(1))
			return;

		$this->loadRanges();
		if(empty($this->ranges)){
			return;
		}

		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->getFullCart($cart_id);

		if(empty($cart->products))
			return;
		$isClosed = $this->isShopClosed($cart->products);
		if(!$isClosed)
			return;

		$shop_closed = true;

		$messages = $this->getMessages();
		$checkoutHelper = hikashopCheckoutHelper::get();
		$msg = JText::_('THE_CHECKOUT_IS_NOT_POSSIBLE').'<br/>';
		$msg .= implode('<br/>', $messages);
		$checkoutHelper->addMessage('shop_closed',$msg);
	}

	public function getMessages() {

		$messages = array();
		foreach($this->productsWithIssue as $issue){
			$ranges = array();
			$product_names = array();
			foreach($issue as $p) {
				$product_names[] = $p->product_name;
				$ranges = $p->shopClosedRanges;
			}
			if(count($product_names) > 1) {
				$messages[] = JText::sprintf('THE_PRODUCTS_X_CAN_ONLY_BE_PURCHASED', implode(', ', $product_names));
			} else {
				$messages[] = JText::sprintf('THE_PRODUCT_X_CAN_ONLY_BE_PURCHASED', implode(', ', $product_names));
			}
			foreach($ranges as $range) {
				if($range->store_open_day == '0'&& $range->store_close_day == '0')
					$messages []= JText::sprintf('EVERY_DAY_FROM_X_TO_X',$range->store_open_hour.':'.sprintf('%02d', $range->store_open_minute),$range->store_close_hour.':'.sprintf('%02d', $range->store_close_minute));
				else
					$messages []= JText::sprintf('FROM_X_ON_X_TO_X_ON_X',JText::_($this->pluginConfig['store_open_day'][2][$range->store_open_day]),$range->store_open_hour.':'.sprintf('%02d', $range->store_open_minute),JText::_($this->pluginConfig['store_open_day'][2][$range->store_close_day]),$range->store_close_hour.':'.sprintf('%02d', $range->store_close_minute));
			}
		}
		return $messages;
	}

	public function onBeforeOrderCreate(&$order, &$do) {
		if(!hikashop_level(1))
			return;

		$app = JFactory::getApplication();
		$option = hikaInput::get()->getString('option', '');
		if(hikashop_isClient('administrator') || $option != 'com_hikashop')
			return;

		$this->loadRanges();
		if(empty($this->ranges)){
			return false;
		}

		$isClosed = $this->isShopClosed($order->cart->products);
		if(!$isClosed)
			return;

		$do = false;
	}

	public function loadRanges(){
		if(!empty($this->ranges))
			return;

		$this->ranges = array();
		$ids = array();
		parent::listPlugins($this->name, $ids, false);


		if(empty($ids) || !count($ids)){
			return;
		}
		foreach($ids as $id) {
			parent::pluginParams($id);

			$this->plugin_params->store_open_hour = $this->plugin_params->store_open_time['hour'];
			$this->plugin_params->store_open_minute = $this->plugin_params->store_open_time['minute'];
			$this->plugin_params->store_close_hour = $this->plugin_params->store_close_time['hour'];
			$this->plugin_params->store_close_minute = $this->plugin_params->store_close_time['minute'];

			if(!strlen($this->plugin_params->store_open_hour) || !strlen($this->plugin_params->store_close_hour) || !strlen($this->plugin_params->store_open_minute) || !strlen($this->plugin_params->store_close_minute)){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('PLUGIN_X_IS_NOT_CONFIGURED_CORRECTLY_MISSING_DATA',$this->plugin_data->plugin_name));
				continue;
			}

			if(($this->plugin_params->store_open_day == '0' && $this->plugin_params->store_close_day != '0') || ($this->plugin_params->store_open_day != '0' && $this->plugin_params->store_close_day == '0')){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('PLUGIN_X_IS_NOT_CONFIGURED_CORRECTLY_DAYS_ISSUE',$this->plugin_data->plugin_name));
				continue;
			}

			if($this->plugin_params->store_open_day == '0' && $this->plugin_params->store_close_day == '0' && ($this->plugin_params->store_close_hour<$this->plugin_params->store_open_hour || $this->plugin_params->store_close_hour==$this->plugin_params->store_open_hour && $this->plugin_params->store_close_minute<$this->plugin_params->store_open_minute)){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('PLUGIN_X_IS_NOT_CONFIGURED_CORRECTLY_DAYS_ISSUE',$this->plugin_data->plugin_name));
				continue;
			}

			if(!empty($this->plugin_params->categories)) {
				if(!is_array($this->plugin_params->categories))
					$this->plugin_params->categories = explode(',', $this->plugin_params->categories);
				hikashop_toInteger($this->plugin_params->categories);
			}

			$this->ranges[] = $this->plugin_params;
		}

	}

	private function isShopClosed(&$products) {
		$categoryClass = hikashop_get('class.category');

		foreach($products as $k => $p) {
			$products[$k]->shopClosedRanges = array();
		}

		foreach($this->ranges as $id => $r) {
			if(!empty($r->categories)) {
				$found = false;
				foreach($r->categories as $c) {
					$category_products = $categoryClass->getProductsIn($c, $products, (bool)$r->subcategories);
					if(!empty($category_products) && count($category_products)) {
						if(!isset($r->products))
							$r->products = array();
						$r->products = array_merge($r->products, $category_products);
						$found = true;
						foreach($products as $k => $p) {
							if(in_array($p->product_id, $category_products)){
								$products[$k]->shopClosedRanges[$id] = $r;
							}
						}
					}
				}
				if(!$found) {
					$r->skipped = true;
					continue;
				}
			} else {
				foreach($products as $k => $p) {
					$products[$k]->shopClosedRanges[$id] = $r;
				}
			}
		}

		$global_result = false;

		$this->productsWithIssue = array();

		foreach($products as $k => $p) {
			if(!empty($p->shopClosedRanges)) {
				$result = $this->_productNotPurchasable($p->shopClosedRanges);
				if($result) {
					$key = implode('_',array_keys($p->shopClosedRanges));
					if(!isset($this->productsWithIssue[$key]))
						$this->productsWithIssue[$key] = array();
					$this->productsWithIssue[$key][] = $p;
					$global_result = true;
				}
			}
		}

		return $global_result;
	}

	private function _productNotPurchasable($ranges) {
		$now = time();
		$current_day = hikashop_getDate($now, 'N');
		$current_hour = hikashop_getDate($now, '%H');
		$current_minute = hikashop_getDate($now, '%M');
		$closed = true;
		foreach($ranges as $r) {
			if($r->store_open_day == '0'){
				if($r->store_open_hour == $r->store_close_hour && $r->store_open_minute == $r->store_close_minute)
				continue;

				if($r->store_open_hour < $r->store_close_hour || ($r->store_open_hour == $r->store_close_hour && $r->store_open_minute < $r->store_close_minute)) {
					$closed = false;
					if($current_hour < $r->store_open_hour || ($current_hour == $r->store_open_hour && $current_minute < $r->store_open_minute)) {
						$closed = true;
					}
					if($r->store_close_hour<$current_hour || ($current_hour == $r->store_close_hour && $r->store_close_minute < $current_minute)) {
						$closed = true;
					}
				} else {
					if($current_hour < $r->store_close_hour || ($current_hour == $r->store_close_hour && $current_minute < $r->store_close_minute)) {
						$closed = false;
					}
					if($r->store_open_hour < $current_hour || ($current_hour == $r->store_open_hour && $r->store_open_minute < $current_minute)) {
						$closed = false;
					}
				}

				if(!$closed){
					return false;
				}
				continue;
			}

			if($r->store_open_day<=$r->store_close_day){
				if($r->store_open_day>$current_day || $r->store_close_day<$current_day){
					continue;
				}
				if($r->store_open_day<$current_day && $r->store_close_day>$current_day)
					return false;
			}else{
				if($r->store_open_day<$current_day || $r->store_close_day>$current_day){
					continue;
				}
				if($r->store_open_day>$current_day && $r->store_close_day<$current_day)
					return false;
			}
			if($r->store_close_day == $current_day){
				if($current_hour < $r->store_close_hour || ($current_hour == $r->store_close_hour && $current_minute < $r->store_close_minute)) {
					if($r->store_open_day != $current_day){
						return false;
					}
				}else{
					continue;
				}
			}
			if($r->store_open_day == $current_day){
				if($r->store_open_hour < $current_hour || ($current_hour == $r->store_open_hour && $r->store_open_minute < $current_minute)) {
					return false;
				}
				continue;
			}
		}
		if(!$closed){
			return false;
		}

		return true;
	}
}
