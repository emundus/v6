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
class plgHikashoppaymentUserpoints extends hikashopPaymentPlugin {
	var $multiple = true;
	var $name = 'userpoints';
	var $accepted_currencies = array();

	public function onPaymentDisplay(&$order, &$methods, &$usable_methods) {
		$user = JFactory::getUser();
		if(empty($user->id))
			return false;

		$ordered_methods = array();
		foreach($methods as &$method) {
			if($method->payment_type != $this->name || !$method->enabled || !$method->payment_published)
				continue;
			if(!empty($method->payment_params->virtual_coupon))
				$ordered_methods[] =& $method;
		}
		foreach($methods as &$method) {
			if($method->payment_type != $this->name || !$method->enabled || !$method->payment_published)
				continue;
			if(empty($method->payment_params->virtual_coupon))
				$ordered_methods[] =& $method;
		}

		if(empty($ordered_methods))
			return;

		parent::onPaymentDisplay($order, $ordered_methods, $usable_methods);
	}

	public function readGeneralOptions() {
		$default = array(
			'checkout_step' => 0,
			'default_no_use' => 0
		);
		if(isset($this->userpointsplugins)) {
			if(!empty($this->userpointsplugins))
				return $this->userpointsplugins->plugin_options;
			return $default;
		}
		$this->userpointsplugins = hikashop_import('hikashop', 'userpoints');
		if(!empty($this->userpointsplugins)) {
			$this->userpointsplugins->_readOptions();
			return $this->userpointsplugins->plugin_options;
		}
		return $default;
	}

	public function checkPaymentDisplay(&$method, &$order) {
		$this->payment_params = $method->payment_params;
		$this->plugin_params = $method->payment_params;

		if(!empty($method->payment_params->virtual_coupon)) {
			return false; //$this->checkPaymentDisplay_VirtualCoupon($method, $order);
		}

		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency', 1);
		$this->currency_id = hikashop_getCurrency();
		$this->points = $this->getUserPoints(null, 'all');
		$this->virtualpoints = $this->getVirtualPoints($order, 'all');
		$this->virtual_coupon_used = false;

		$ret = $this->checkPaymentDisplay_ClassicalMethod($method, $order);

		unset($this->main_currency);
		unset($this->currency_id);
		unset($this->points);
		unset($this->virtualpoints);
		unset($this->virtual_coupon_used);

		return $ret;
	}

	protected function checkPaymentDisplay_ClassicalMethod(&$method, &$order) {
		$userPoints = 0;
		if($this->checkRules($order, $userPoints) == false)
			return false;

		if(!$method->payment_params->virtual_coupon && !empty($order->coupon->discount_code)) {
			if(preg_match('#^POINTS_[a-zA-Z0-9]{30}$#', $order->coupon->discount_code))
				return false;
		}

		$config = hikashop_config();
		$currencyClass = hikashop_get('class.currency');
		$rate = $method->payment_params->value;
		if($this->main_currency != $this->currency_id)
			$rate = $currencyClass->convertUniquePrice($rate, $this->main_currency, $this->currency_id);

		if(isset($order->order_currency_id))
			$curr = $order->order_currency_id;
		else
			$curr = hikashop_getCurrency();

		$price = $currencyClass->format($this->pointsToCurrency($userPoints, $method, $order), $curr);

		$price_value = $this->pointsToCurrency($userPoints, $method, $order);

		$tax_id = (int)@$method->payment_params->tax_id;
		if(!empty($tax_id) && $config->get('price_with_tax')) {
			$round = $currencyClass->getRounding($curr, true);
			$zone_id = hikashop_getZone();
			$price_value = $currencyClass->getTaxedPrice($price_value, $zone_id, $tax_id, $round);
		}
		$price = $currencyClass->format($price_value, $curr);

		$method->payment_description .= JText::sprintf('YOU_HAVE', $userPoints, $price);

		$fullOrderPoints = $this->finalPriceToPoints($order, $userPoints, false);

		if($method->payment_params->partialpayment == 0 ) {
			if( $method->payment_params->allowshipping == 1 ) {
				$method->payment_description .= JText::sprintf('PAY_FULL_ORDER_POINTS', $fullOrderPoints);
			} else {
				$method->payment_description .= JText::sprintf('PAY_FULL_ORDER_NO_SHIPPING', $fullOrderPoints);
				$method->payment_description .= JText::sprintf('COUPON_GENERATE');
				$method->payment_description .= JText::sprintf('CAUTION_POINTS');
			}
		} else {
			$check = $this->checkPoints($order);
			if( $check >= $fullOrderPoints ) {
				$method->payment_description .= JText::sprintf('PAY_FULL_ORDER_POINTS', $fullOrderPoints);
			} else {
				$coupon = $check * $rate;

				if(!empty($tax_id) && $config->get('price_with_tax')) {
					$currency_id = (isset($order->order_currency_id)) ? $order->order_currency_id : hikashop_getCurrency();
					$round = $currencyClass->getRounding($currency_id, true);
					$coupon = $currencyClass->getTaxedPrice($coupon, $zone_id, $tax_id, $round);
				}

				$price = $currencyClass->format($coupon, $this->currency_id);
				$method->payment_description .= JText::sprintf('COUPON_GENERATE_PARTIAL', $price);
				$method->payment_description .= JText::sprintf('CAUTION_POINTS');
			}
		}
		return true;
	}

	public function onAfterCartShippingLoad(&$cart) {
		$app = JFactory::getApplication();
		$opt = $this->readGeneralOptions();

		$no_virtual_coupon = (int)$app->getUserState(HIKASHOP_COMPONENT.'.userpoints_no_virtual_coupon', (int)(@$opt['checkout_step'] && @$opt['default_no_use']));
		if(!empty($no_virtual_coupon)) {
			unset($cart->additional['userpoints']);
			unset($cart->additional['userpoints_points']);
			return;
		}

		$user = JFactory::getUser();
		if(empty($user->id))
			return false;

		if($cart->cart_type != 'cart')
			return false;

		if(isset($cart->additional['userpoints']))
			return;

		$ret = $this->getCartUsedPoints($cart);

		if(empty($ret))
			return;

		$pointsToLoose = $ret['points'];
		$coupon = $ret['value'];

		if(isset($cart->order_currency_id))
			$currency_id = $cart->order_currency_id;
		else
			$currency_id = hikashop_getCurrency();

		$tax_id = (int)@$this->payment_params->tax_id;
		$currencyClass = hikashop_get('class.currency');

		$userpoints = new stdClass();
		$userpoints->name = 'USERPOINTS_DISCOUNT';
		$userpoints->value = '';
		$userpoints->price_currency_id = $currency_id;
		$userpoints->price_value = -$coupon;
		$userpoints->price_value_with_tax = -$coupon;
		if(!empty($tax_id)) {
			$round = $currencyClass->getRounding($currency_id, true);
			$zone_id = hikashop_getZone();
			$userpoints->price_value_with_tax = $currencyClass->getTaxedPrice($userpoints->price_value, $zone_id, $tax_id, $round);
			$userpoints->taxes = $currencyClass->taxRates;
		}
		$cart->additional['userpoints'] = $userpoints;

		$userpoints_points = new stdClass();
		$userpoints_points->name = 'USERPOINTS_USE_POINTS';
		$userpoints_points->value = $pointsToLoose.' '.JText::_('USERPOINTS_POINTS');
		$userpoints_points->price_currency_id = 0;
		$userpoints_points->price_value = 0;
		$userpoints_points->price_value_with_tax = 0;

		$cart->additional['userpoints_points'] = $userpoints_points;
	}

	public function getCartUsedPoints(&$cart) {
		$check = 0;
		if(!empty($this->virtual_coupon_used))
			$check = $this->virtual_coupon_value;

		$ids = array();

		$currencyClass = hikashop_get('class.currency');
		$config =& hikashop_config();
		$currency_id = (isset($cart->order_currency_id)) ? $cart->order_currency_id : hikashop_getCurrency();
		$this->main_currency = $config->get('main_currency', 1);

		$this->virtualpoints = $this->getVirtualPoints($cart, 'all');

		$zone_id = hikashop_getZone();
		$tax_id = (int)@$this->payment_params->tax_id;
		$round = $currencyClass->getRounding($currency_id, true);

		parent::listPlugins($this->name, $ids, false);
		foreach($ids as $id) {
			parent::pluginParams($id);
			$this->payment_params =& $this->plugin_params;

			if(hikashop_level(2) && !hikashop_isAllowed($this->plugin_data->payment_access))
				continue;
			if(!@$this->payment_params->virtual_coupon)
				continue;
			if($this->payment_params->partialpayment == 0)
				continue;

			$userPoints = 0;
			if(empty($check) && $this->checkRules($cart, $userPoints) == false)
				continue;

			if($this->main_currency != $currency_id)
				$this->payment_params->value = $currencyClass->convertUniquePrice($this->payment_params->value, $this->main_currency, $currency_id);

			if(empty($check))
				$check = $this->checkPoints($cart);
			if($check !== false && $check > 0) {
				$coupon = $check * $this->payment_params->value;
				$pointsToLoose = $check;
				$virtual_points = $this->getVirtualPoints($cart, $this->payment_params->points_mode);
				if(!empty($virtual_points)) {
					if($virtual_points <= $check)
						$pointsToLoose = $check - $virtual_points;
					else
						$pointsToLoose = 0;
				}
				$coupon_with_tax = $coupon;
				if(!empty($this->payment_params->tax_id)) {
					$coupon_with_tax = $currencyClass->getTaxedPrice($coupon, $zone_id, (int)$this->payment_params->tax_id, $round);
				}

				return array(
					'points' => $pointsToLoose,
					'value' => $coupon,
					'tax' => $coupon_with_tax,
					'mode' => $this->payment_params->points_mode
				);
			}
		}
		unset($this->payment_params);

		return null;
	}

	public function onAfterOrderConfirm(&$order, &$methods,$method_id) {
		$this->removeCart = true;

		$currencyClass = hikashop_get('class.currency');
		$this->amount = $currencyClass->format($order->order_full_price, $order->order_currency_id);
		$this->order_number = $order->order_number;
		$this->order = $order;
		$this->url = $this->getOrderUrl($order);
		$this->showPage('end');
	}

	public function onBeforeOrderCreate(&$order, &$do) {
		if( !empty($order->order_type) && $order->order_type != 'sale' )
			return true;

		if(empty($order->order_payment_params))
			$order->order_payment_params = new stdClass();
		if(empty($order->order_payment_params->userpoints))
			$order->order_payment_params->userpoints = new stdClass();
		if(empty($order->order_payment_params->userpoints->use_points))
			$order->order_payment_params->userpoints->use_points = 0;
		if(empty($order->order_payment_params->userpoints->earn_points))
			$order->order_payment_params->userpoints->earn_points = array();

		$earnPoints = $this->getPointsEarned($order, 'all');
		if(!empty($earnPoints)) {
			foreach($earnPoints as $mode => $pts) {
				if(empty($order->order_payment_params->userpoints->earn_points[$mode]))
					$order->order_payment_params->userpoints->earn_points[$mode] = 0;
				$order->order_payment_params->userpoints->earn_points[$mode] += $pts;
			}
		}

		if((empty($order->order_payment_method) || $order->order_payment_method != $this->name) && !empty($order->cart->additional)) {
			return $this->onBeforeOrderCreate_VirtualCoupon($order, $do);
		}
		return $this->onBeforeOrderCreate_ClassicalMethod($order, $do);
	}

	protected function onBeforeOrderCreate_VirtualCoupon(&$order, &$do) {
		$ids = array();
		parent::listPlugins($this->name, $ids, false);
		foreach($ids as $id) {
			parent::pluginParams($id);
			if(empty($this->payment_params) || empty($this->payment_params->virtual_coupon))
				continue;

			$checkPoints = $points = $this->checkPoints($order);
			$usePts = -1;
			foreach($order->cart->additional as $additional) {
				if($additional->name != 'USERPOINTS_USE_POINTS')
					continue;
				$matches = array();
				if(preg_match('#-([0-9]+)#', $additional->value, $matches)) {
					$usePts = (int)$matches[1];
				} else {
					$usePts = substr($additional->value, 0, strpos($additional->value, ' '));
					$usePts = (int)trim(str_replace('-','',$usePts));
				}
				break;
			}

			if($checkPoints > $usePts) {
				if(!isset($order->order_payment_params->userpoints->earn_points[$this->plugin_params->points_mode]))
					$order->order_payment_params->userpoints->earn_points[$this->plugin_params->points_mode] = 0;
				$order->order_payment_params->userpoints->earn_points[$this->plugin_params->points_mode] += ($usePts - $checkPoints);
				$points = $usePts;
			}

			if($usePts > 0)
				$points = $usePts;
			if($points !== false && $points > 0) {
				$order->order_payment_params->userpoints->use_points += $points;
				$order->order_payment_params->userpoints->use_mode = $this->plugin_params->points_mode;
				$order->order_payment_params->userpoints->give_back = !empty($this->plugin_params->givebackpoints);
			}
			break;
		}

		return true;
	}

	protected function onBeforeOrderCreate_ClassicalMethod(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!empty($order->cart->coupon->discount_code) && (preg_match('#^POINTS_[a-zA-Z0-9]{30}$#', $order->cart->coupon->discount_code) || preg_match('#^POINTS_([-a-zA-Z0-9]+)_[a-zA-Z0-9]{25}$#', $order->cart->coupon->discount_code))) {
			if(@$this->payment_params->partialpayment === 0 && $order->cart->full_total->prices[0]->price_value_without_discount != $order->cart->coupon->discount_value) {
				$do = false;
				echo JText::_('ERROR_POINTS');
				return true;
			}
		}

		$check = $this->checkPoints($order);
		$userPoints = $this->getUserPoints(null, $this->payment_params->points_mode);
		$fullOrderPoints = $this->finalPriceToPoints($order, $userPoints);

		if($this->payment_params->partialpayment == 0 && $this->payment_params->allowshipping == 1 && $check !== false && $check > 0 && $userPoints >= $check) {
			if(empty($this->payment_params->verified_status)) {
				$config = hikashop_config();
				$this->payment_params->verified_status = $config->get('order_confirmed_status', 'confirmed');
			}
			$order->order_status = $this->payment_params->verified_status;
		}

		if(($this->payment_params->partialpayment == 1 || $this->payment_params->allowshipping == 0) && ($check !== false && $check > 0) && ($check < $fullOrderPoints) && $userPoints) {
			$discountClass = hikashop_get('class.discount');
			$cartClass = hikashop_get('class.cart');
			$config =& hikashop_config();
	 		$currency = hikashop_getCurrency();

			$app = JFactory::getApplication();
			$newCoupon = new stdClass();
			$newCoupon->discount_type = 'coupon';
			$newCoupon->discount_currency_id = $currency;

			$newCoupon->discount_flat_amount = $check * $this->payment_params->value;
			$newCoupon->discount_quota = 1;
			jimport('joomla.user.helper');
			if(!empty($this->payment_params->givebackpoints)) {
				$newCoupon->discount_code = 'POINTS_' . $this->payment_params->points_mode . '_' . JUserHelper::genRandomPassword(25);
			} else {
				$newCoupon->discount_code = 'POINTS_' . JUserHelper::genRandomPassword(30);
			}
			$newCoupon->discount_published = 1;
			$discountClass->save($newCoupon);
			$coupon = $newCoupon;
			if(!empty($coupon)) {
				$cartClass->update($coupon->discount_code, 1, 0, 'coupon');
			}
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_method', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_id', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_data', null);
			$do = false;
			if(empty($order->customer)) {
				$userClass = hikashop_get('class.user');
				$order->customer = $userClass->get($order->order_user_id);
			}
			$this->addPoints(-$check, $order, JText::_('HIKASHOP_COUPON').' '.$coupon->discount_code);
		}
	}

	public function onAfterOrderCreate(&$order, &$send_email) {
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator'))
			return true;

		if( !empty($order->order_type) && $order->order_type != 'sale' )
			return true;

		if(empty($order->order_payment_method) || $order->order_payment_method != $this->name) {
			return true;
		}

		$this->loadOrderData($order);
		$this->loadPaymentParams($order);
		if(empty($this->payment_params)) {
			$do = false;
			return true;
		}

		$ret = true;
		$order_status = null;
		$config =& hikashop_config();

		$points = $this->checkpoints($order);
		if($points !== false && $points > 0) {
			$this->addPoints(-$points, $order);

			if(empty($this->payment_params->verified_status))
				$this->payment_params->verified_status = $config->get('order_confirmed_status', 'confirmed');
			$order_status = $this->payment_params->verified_status;
		} else {
			$ret = false;
		}

		if($order_status === null || $order->order_status == $order_status)
			return $ret;

		$orderObj = new stdClass();
		$orderObj->order_status = $order_status;
		$orderObj->order_id = $order->order_id;
		$orderObj->userpoints_process = new stdClass();
		$orderObj->userpoints_process->updated = true;

		$orderClass = hikashop_get('class.order');
		$orderClass->save($orderObj);

		return $ret;
	}

	public function addPoints($points, $order, $data = null) {
		$plugin = hikashop_import('hikashop', 'userpoints');
		return $plugin->addPoints($points, $order, $data, $this->plugin_params->points_mode);
	}

	public function getUserPoints($cms_user_id = null, $mode = 'all') {
		$plugin = hikashop_import('hikashop', 'userpoints');
		return $plugin->getUserPoints($cms_user_id, $mode);
	}

	public function getPointsEarned($order, $mode = 'all') {
		$plugin = hikashop_import('hikashop', 'userpoints');
		$points = 0;
		if($mode == 'all')
			$points = array();
		$plugin->onGetUserPointsEarned($order, $points, $mode);
		return $points;
	}

	public function getVirtualPoints($order, $mode = 'all') {
		$plugin = hikashop_import('hikashop', 'userpoints');
		$points = 0;
		if($mode == 'all')
			$points = array();
		$plugin->onGetUserPointsEarned($order, $points, $mode, true);
		return $points;
	}

	public function giveAndGiveBack(&$order) {
		$plugin = hikashop_import('hikashop', 'userpoints');
		return $plugin->giveAndGiveBack($order);
	}

	public function checkRules($order, &$userPoints) {
		if(empty($this->plugin_params))
			return false;

		if(isset($this->points[$this->plugin_params->points_mode]))
			$userPoints = $this->points[$this->plugin_params->points_mode];
		else
			$userPoints = $this->getUserPoints(null, $this->plugin_params->points_mode);

		$check = $this->checkPoints($order, true);

		$virtualPoints = 0;
		if($userPoints == 0 && !empty($this->virtualpoints) && !empty($this->virtualpoints[$this->plugin_params->points_mode])) {
			$virtualPoints = $this->virtualpoints[$this->plugin_params->points_mode];
		}

		if($check === false || $check == 0 || ($userPoints == 0 && $virtualPoints == 0))
			return false;

		if(!isset($order->full_total)) {
			$total = $order->order_full_price;
			$total_without_shipping = $total - $order->order_shipping_price;
		} else {
			$total = $order->full_total->prices[0]->price_value_with_tax;
			$total_without_shipping = $order->total->prices[0]->price_value_with_tax;
		}

		if(isset($order->additional['userpoints'])) {
			$total -= $order->additional['userpoints']->price_value;
			if(!isset($order->full_total))
				$total_without_shipping -= $order->additional['userpoints']->price_value;
		}

		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		if(!isset($this->currency_id))
			$this->currency_id = hikashop_getCurrency();
		if($this->main_currency != $this->currency_id)
			$this->plugin_params->minimumcost = $currencyClass->convertUniquePrice($this->plugin_params->minimumcost, $this->main_currency, $this->currency_id);

		if($this->plugin_params->allowshipping == 1)
			$calculatedPrice = $total;
		else
			$calculatedPrice = $total_without_shipping;

		if($this->plugin_params->minimumcost > $calculatedPrice)
			return false;

		$neededpoints = ((int)$this->plugin_params->percent / 100) * $calculatedPrice;
		$useablePoints = $this->pointsToCurrency($userPoints);
		if($useablePoints < $neededpoints)
			return false;

		if($this->plugin_params->partialpayment == 0)
			$this->plugin_params->percentmax = 100;

		if($this->plugin_params->percentmax <= 0)
			return false;

		if(!empty($this->plugin_data->payment_currency) && strpos($this->plugin_data->payment_currency, ','.$this->currency_id.',') === false)
			return false;

		if(!empty($this->plugin_data->payment_zone_namekey)) {
			if(empty($this->order_zones)) {
				$zoneClass = hikashop_get('class.zone');
				$this->order_zones = $zoneClass->getOrderZones($order);
			}
			if(!in_array($this->plugin_data->payment_zone_namekey, $this->order_zones))
				return false;
		}

		if(!empty($this->plugin_params->payment_zip_prefix) || !empty($this->plugin_params->payment_min_zip) || !empty($this->plugin_params->payment_max_zip) || !empty($this->plugin_params->payment_zip_suffix)) {
			$prefix = null;
			if(!empty($order->shipping_address) && !empty($order->shipping_address->address_post_code) && preg_match('#([a-z]*)([0-9]+)(.*)#i', preg_replace('#[^a-z0-9]#i', '', $order->shipping_address->address_post_code), $match)) {
				$prefix = $match[1];
				$main = $match[2];
				$suffix = $match[3];
			}
			if($prefix === null)
				return false;
			if(!empty($this->plugin_param->payment_zip_prefix) && $this->plugin_param->payment_zip_prefix != $prefix)
				return false;
			if(!empty($this->plugin_param->payment_min_zip) && $this->plugin_param->payment_min_zip > $main)
				return false;
			if(!empty($this->plugin_param->payment_max_zip) && $this->plugin_param->payment_max_zip < $main)
				return false;
			if(!empty($this->plugin_param->payment_zip_suffix) && $this->plugin_param->payment_zip_suffix != $suffix)
				return false;
		}

		if(!empty($this->plugin_param->payment_max_volume) && bccomp((float)@$this->plugin_param->payment_max_volume, 0, 3)) {
			if(empty($this->volumeHelper))
				$this->volumeHelper = hikashop_get('helper.volume');
			$this->plugin_param->payment_max_volume_orig = $this->plugin_param->payment_max_volume;
			$this->plugin_param->payment_max_volume = $this->volumeHelper->convert($this->plugin_param->payment_max_volume, @$this->plugin_param->payment_size_unit);
			if($this->plugin_param->payment_max_volume < $order->volume)
				return false;
		}
		if(!empty($this->plugin_param->payment_min_volume) && bccomp((float)@$this->plugin_param->payment_min_volume, 0, 3)) {
			if(empty($this->volumeHelper))
				$this->volumeHelper = hikashop_get('helper.volume');
			$this->plugin_param->payment_min_volume_orig = $this->plugin_param->payment_min_volume;
			$this->plugin_param->payment_min_volume = $this->volumeHelper->convert($this->plugin_param->payment_min_volume, @$this->plugin_param->payment_size_unit);
			if($this->plugin_param->payment_min_volume > $order->volume)
				return false;
		}

		if(!empty($this->plugin_param->payment_max_weight) && bccomp((float)@$this->plugin_param->payment_max_weight, 0, 3)) {
			if(empty($this->weightHelper))
				$this->weightHelper = hikashop_get('helper.weight');
			$this->plugin_param->payment_max_weight_orig = $this->plugin_param->payment_max_weight;
			$this->plugin_param->payment_max_weight = $this->weightHelper->convert($this->plugin_param->payment_max_weight, @$this->plugin_param->payment_weight_unit);
			if($this->plugin_param->payment_max_weight < $order->weight)
				return false;
		}
		if(!empty($this->plugin_param->payment_min_weight) && bccomp((float)@$this->plugin_param->payment_min_weight,0,3)){
			if(empty($this->weightHelper))
				$this->weightHelper = hikashop_get('helper.weight');
			$this->plugin_param->payment_min_weight_orig = $this->plugin_param->payment_min_weight;
			$this->plugin_param->payment_min_weight = $this->weightHelper->convert($this->plugin_param->payment_min_weight, @$this->plugin_param->payment_weight_unit);
			if($this->plugin_param->payment_min_weight > $order->weight)
				return false;
		}

		if(!empty($this->plugin_param->payment_max_quantity) && (int)$this->plugin_param->payment_max_quantity && $this->plugin_param->payment_max_quantity < $order->total_quantity)
			return false;
		if(!empty($this->plugin_param->payment_min_quantity) && (int)$this->plugin_param->payment_min_quantity && $this->plugin_param->payment_min_quantity > $order->total_quantity)
			return false;

		return true;
	}

	public function pointsToCurrency($userPoints) {
		if(empty($this->plugin_params))
			return false;
		$coupon = $userPoints * hikashop_toFloat($this->plugin_params->value);
		return $coupon;
	}

	public function checkPoints(&$order, $showWarning = false) {
		static $displayed = false;

		if(empty($this->plugin_params))
			return false;

		if(isset($this->points[$this->plugin_params->points_mode]))
			$userPoints = $this->points[$this->plugin_params->points_mode];
		else
			$userPoints = $this->getUserPoints(null, $this->plugin_params->points_mode);
		if(empty($userPoints))
			$userPoints = 0;
		if(isset($this->virtualpoints[$this->plugin_params->points_mode]))
			$userPoints += $this->virtualpoints[$this->plugin_params->points_mode];
		else
			$userPoints += $this->getVirtualPoints($order, $this->plugin_params->points_mode);

		$fullOrderPoints = $this->finalPriceToPoints($order, $userPoints, false);
		$points = $fullOrderPoints;

		if($this->plugin_params->partialpayment == 0) {
			if((int)$userPoints >= $fullOrderPoints)
				return $fullOrderPoints;
			return 0;
		}

		if(!empty($this->plugin_params->percentmax) && ((int)$this->plugin_params->percentmax > 0) && ((int)$this->plugin_params->percentmax <= 100))
			$points = $points * ( (int)$this->plugin_params->percentmax / 100 );

		if((int)$userPoints < $points)
			$points = (int)$userPoints;

		if(isset($this->plugin_params->grouppoints) && ((int)$this->plugin_params->grouppoints > 1)) {
			if($showWarning && !$displayed) {
				$this->plugin_params->grouppoints = (int)$this->plugin_params->grouppoints;
				if(isset($this->plugin_params->grouppoints_warning_lvl) && ((int)$this->plugin_params->grouppoints_warning_lvl >= 1) ) {
					if($points < $this->plugin_params->grouppoints && ($points + (int)$this->plugin_params->grouppoints_warning_lvl) >= $this->plugin_params->grouppoints) {
						$app = JFactory::getApplication();
						$currencyClass = hikashop_get('class.currency');

						if(isset($cart->order_currency_id)) {
							$currency_id = $cart->order_currency_id;
						} else {
							$currency_id = hikashop_getCurrency();
						}
						$possible_coupon = $this->plugin_params->grouppoints * $this->plugin_params->value;
						$price = $currencyClass->format($possible_coupon, $currency_id);

						$app->enqueueMessage(JText::sprintf('MISSING_X_POINTS_TO_REDUCTION', $this->plugin_params->grouppoints - $points, $price));
						$displayed = true;
					}
				}
			}
			$points -= ($points % $this->plugin_params->grouppoints);
		}

		if(isset($this->plugin_params->maxpoints) && ((int)$this->plugin_params->maxpoints > 0) && $points > (int)$this->plugin_params->maxpoints) {
			$points = (int)$this->plugin_params->maxpoints;

			if(isset($this->plugin_params->grouppoints) && ((int)$this->plugin_params->grouppoints > 1) ) {
				$points -= ($points % (int)$this->plugin_params->grouppoints);
			}
		}

		if($points < (int)$userPoints)
			return (int)$points;
		return (int)$userPoints;
	}

	public function finalPriceToPoints(&$order, &$userPoints, $additional = true) {
		if(empty($this->plugin_params))
			return 0;
		if(empty($this->plugin_params->value) || bccomp(sprintf('%F',$this->plugin_params->value), 0, 5) < 1)
			return 0;
		if(isset($order->order_subtotal) && isset($order->order_shipping_price)) {
			if($this->plugin_params->allowshipping == 1) {
				$final_price = @$order->order_subtotal + $order->order_shipping_price;
			} else {
				$final_price = @$order->order_subtotal;
			}
		} else if(empty($order->cart->full_total->prices[0]->price_value_with_tax)) {
			if($this->plugin_params->allowshipping == 1) {
				$final_price = @$order->full_total->prices[0]->price_value_with_tax;
				if(!empty($order->additional['userpoints']->price_value_with_tax)){
					$final_price -= $order->additional['userpoints']->price_value_with_tax;
				}
			}else{
				$final_price = @$order->total->prices[0]->price_value_with_tax;
			}
		} else {
			if($this->plugin_params->allowshipping == 1) {
				$final_price = @$order->cart->full_total->prices[0]->price_value_with_tax;
				if(!empty($order->cart->additional['userpoints']->price_value_with_tax)){
					$final_price -= $order->cart->additional['userpoints']->price_value_with_tax;
				}
			} else {
				$final_price = @$order->cart->total->prices[0]->price_value_with_tax;
			}
		}


		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		if(!isset($this->currency_id))
			$this->currency_id = hikashop_getCurrency();
		$rate = $this->plugin_params->value;
		if($this->main_currency != $this->currency_id){
			$rate = $currencyClass->convertUniquePrice($rate, $this->main_currency, $this->currency_id);
		}

		$pointsDecrease = $final_price * ( 1 / $rate );
		return ceil($pointsDecrease);
	}

	public function loadFullOrder($order_id) {
		if(!empty($this->fullOrder) && $this->fullOrder->order_id == $order_id)
			return true;

		$classOrder =& hikashop_get('class.order');
		$this->fullOrder = $classOrder->loadFullOrder($order_id, false, false);
		if(!empty($this->fullOrder->customer))
			return true;
		$userClass = hikashop_get('class.user');
		$this->fullOrder->customer = $userClass->get($this->fullOrder->order_user_id);
	}

	public function getAUP($warning = false, $init = false) {
		static $aup = null;
		static $aup_init = false;
		if(!isset($aup)) {
			$aup = false;
			$api_AUP = JPATH_SITE.DS.'components'.DS.'com_userpoints'.DS.'helper.php';
			if(file_exists($api_AUP)) {
				require_once ($api_AUP);
				if(class_exists('UserPointsHelper')){
					$aup = true;
					if(!class_exists('AlphaUserPointsHelper')) {
						require_once(JPATH_SITE.DS.'plugins'.DS.'hikashop'.DS.'userpoints'.DS.'userpoints_bup_compat.php');
					}
				}
			} else {
				$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
				if(file_exists($api_AUP)) {
					require_once ($api_AUP);
					if(class_exists('AlphaUserPointsHelper'))
						$aup = true;
				} else {
					$api_AUP = JPATH_SITE.DS.'components'.DS.'com_altauserpoints'.DS.'helper.php';
					if (file_exists($api_AUP) ) {
						require_once ($api_AUP);

						if(class_exists('AltaUserPointsHelper')){
							$aup = true;
							if(!class_exists('AlphaUserPointsHelper')) {
								require_once(JPATH_SITE.DS.'plugins'.DS.'hikashop'.DS.'userpoints'.DS.'userpoints_aup_compat.php');
							}
						}
					}
				}
			}
			if(!$aup && $warning) {
				$app = JFactory::getApplication();
				if(hikashop_isClient('administrator'))
					$app->enqueueMessage('The HikaShop UserPoints payment plugin requires one of the components AlphaUserPoints, AltaUserPoints or UserPoints to be installed. If you want to use it, please install the component or use another mode.');
			}
		}
		if($aup === true && $init && !$aup_init) {
			$db = JFactory::getDBO();
			$query = 'SELECT id FROM '.hikashop_table('alpha_userpoints_rules', false).' WHERE rule_name=' . $db->Quote('Order_validation');
			$db->setQuery($query);
			$exist = $db->loadResult();
			if(empty($exist)) {
				$data = array(
					'rule_name' => $db->Quote('Order_validation'),
					'rule_description' => $db->Quote('Give points to customer when the order is validate'),
					'rule_plugin' => $db->Quote('com_hikashop'),
					'plugin_function' => $db->Quote('plgaup_orderValidation'),
					'access' => 1,
					'points' => 0,
					'published' => 1,
					'system' => 0,
					'autoapproved' => 1
				);
				$query = 'INSERT INTO '.hikashop_table('alpha_userpoints_rules',false) . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', $data) . ')';
				$db->setQuery($query);
				$db->execute();
			}
			$aup_init = true;
		}
		return $aup;
	}

	public function getEasysocial($warning = false) {
		static $foundry = null;

		if(isset($foundry))
			return $foundry;

		$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
		jimport('joomla.filesystem.file');
		$foundry = JFile::exists($file);

		if($foundry) {
			include_once($file);
		} else if($warning) {
			$app = JFactory::getApplication();
			if(hikashop_isClient('administrator'))
				$app->enqueueMessage('The HikaShop UserPoints plugin requires the component EasySocial to be installed. If you want to use it, please install the component or use another mode.');
		}

		return $foundry;
	}

	public function onPaymentConfiguration(&$element) {
		parent::onPaymentConfiguration($element);

		$this->modes = array();
		if($this->getAUP(false, true))
		{
			$name = JText::_('ALPHA_USER_POINTS');
			if(HIKASHOP_J40) {
				$name = 'UserPoints';
			}
			$this->modes[] = JHTML::_('select.option', 'aup', $name);
		}
		if($this->getEasysocial(false))
			$this->modes[] = JHTML::_('select.option', 'esp', JText::_('EASYSOCIAL_POINTS'));
		$this->modes[] = JHTML::_('select.option', 'hk', JText::_('HIKASHOP_USER_POINTS'));

		$this->pointsTaxType = hikashop_get('type.categorysub');
		$this->pointsTaxType->type = 'tax';
		$this->pointsTaxType->field = 'category_id';

		$this->address = hikashop_get('type.address');
		if(!empty($element->payment_params->categories))
			$this->categories = hikashop_unserialize($element->payment_params->categories);

		$ids = array();
		if(!empty($this->categories)) {
			foreach($this->categories as $cat) {
				$ids[] = $cat->category_id;
			}
			$db = JFactory::getDBO();
			$db->setQuery('SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$ids).')');
			$cats = $db->loadObjectList('category_id');
			foreach($this->categories as $k => $cat) {
				if(!empty($cats[$cat->category_id])) {
					$this->categories[$k]->category_name = $cats[$cat->category_id]->category_name;
				} else {
					$this->categories[$k]->category_name = JText::_('CATEGORY_NOT_FOUND');
				}
			}
		}

		$db = JFactory::getDBO();
		$db->setQuery('SELECT a.*, a.title as text, a.id as value  FROM #__usergroups AS a ORDER BY a.lft ASC');
		$this->groups = $db->loadObjectList('id');
		foreach($this->groups as $id => $group) {
			if(isset($this->groups[$group->parent_id])) {
				$this->groups[$id]->level = intval(@$this->groups[$group->parent_id]->level) + 1;
				$this->groups[$id]->text = str_repeat('- - ',$this->groups[$id]->level).$this->groups[$id]->text;
			}
		}


		if(!empty($element->payment_params->groups)) {
			$element->payment_params->groups = hikashop_unserialize($element->payment_params->groups);
			foreach($this->groups as $id => $group) {
				$this->groups[$id]->points = (int)@$element->payment_params->groups[$group->value];
			}
	 	}

		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currency = hikashop_get('class.currency');
		$this->currency = $currency->get($this->main_currency);

		$js='
function setVisible(value){
	value = (parseInt(value) == 1) ? "" : "none";
	document.getElementById("opt").style.display = value;
	document.getElementById("opt2").style.display = value;
}
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

	public function onPaymentConfigurationSave(&$element) {
		$categories = hikaInput::get()->get('category', array(), 'array');
		hikashop_toInteger($categories);
		$cats = array();
		if(!empty($categories)) {
			$category_points = hikaInput::get()->get('category_points', array(), 'array');
			foreach($categories as $id => $category) {
				if((int)@$category_points[$id] == 0)
					continue;
				$obj = new stdClass();
				$obj->category_id = $category;
				$obj->category_points = (int)@$category_points[$id];
				$cats[] = $obj;
			}
		}
		$element->payment_params->categories = serialize($cats);

		$groups = hikaInput::get()->get('groups', array(), 'array');
		hikashop_toInteger($groups);
		$element->payment_params->groups = serialize($groups);

		$element->payment_params->value = hikashop_toFloat($element->payment_params->value);

		if($element->payment_params->virtual_coupon && $element->payment_params->partialpayment == 0) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('The Virtual coupon mode cannot be used for partial payment with points. Either deactivate the Virtual coupon mode or the partial payment. Otherwise, you won\'t see any payment with points on the checkout');
		}

		if($element->payment_params->points_mode == 'aup' && !$this->getAUP(true, true)) {
			$element->payment_params->points_mode = 'hk';
		}
		if($element->payment_params->points_mode == 'esp' && !$this->getEasysocial(true)) {
			$element->payment_params->points_mode = 'hk';
		}

		if($element->payment_params->points_mode == 'hk') {
			$user = hikashop_loadUser(true);
			if(!isset($user->user_points)) {
				$field = new stdClass();
				$field->field_table = 'user';
				$field->field_realname = Jtext::_('HIKASHOP_USER_POINTS');
				$field->field_namekey = 'user_points';
				$field->field_type = 'text';
				$field->field_published = 1;
				$field->field_default = 0;

				$fieldClass = hikashop_get('class.field');
				$fieldClass->save($field);
			}
		}
	}

	public function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'Pay with Points';
		$element->payment_description = 'You can pay with points using this payment method';
		$element->payment_images = '';

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
		$element->payment_params->percentmax = 100;
		$element->payment_params->virtual_coupon = true;
	}
}
