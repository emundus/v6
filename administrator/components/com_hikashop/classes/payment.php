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
class hikashopPaymentClass extends hikashopClass {
	var $tables = array('payment');
	var $pkeys = array('payment_id');
	var $toggle = array('payment_published' => 'payment_id');
	var $deleteToggle = array('payment' => array('payment_type', 'payment_id'));

	function get($id, $default = '') {
		static $cachedElements = array();
		if($id=='reset_cache'){
			$cachedElements = array();
		}
		if(!isset($cachedElements[$id])){
			$result = parent::get($id);
			if(!empty($result->payment_params)) {
				$result->payment_params = hikashop_unserialize($result->payment_params);
			}
			if(!empty($result->payment_name))
				$result->payment_name = hikashop_translate($result->payment_name);
			if(!empty($result->payment_description))
				$result->payment_description = hikashop_translate($result->payment_description);
			$cachedElements[$id] = $result;
		}

		return $cachedElements[$id];
	}

	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeHikaPluginDelete', array('payment', &$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterHikaPluginDelete', array('payment', &$elements));
		}
		return $status;
	}


	function save(&$element, $reorder = true) {
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$new = empty($element->payment_id);
		if($new)
			$app->triggerEvent('onBeforeHikaPluginCreate', array('payment', &$element, &$do));
		else {
			if(!isset($element->old))
				$element->old = parent::get($element->payment_id);
			$app->triggerEvent('onBeforeHikaPluginUpdate', array('payment', &$element, &$do));
		}
		if(!$do)
			return false;

		if(isset($element->payment_params) && !is_string($element->payment_params)){
			$element->payment_params = serialize($element->payment_params);
		}

		if(isset($element->payment_shipping_methods) && is_array($element->payment_shipping_methods)) {
			$element->payment_shipping_methods = implode("\n", $element->payment_shipping_methods);
		}
		if(isset($element->payment_currency) && is_array($element->payment_currency)) {
			$element->payment_currency = implode(",", $element->payment_currency);
			if(!empty($element->payment_currency))
				$element->payment_currency = ','.$element->payment_currency.',';
		}

		if(empty($element->payment_id))
			unset($element->payment_id);

		$status = parent::save($element);
		if($status){
			if(empty($element->payment_id))
				$element->payment_id = $status;
			if($new)
				$app->triggerEvent('onAfterHikaPluginCreate', array('payment', &$element));
			else
				$app->triggerEvent('onAfterHikaPluginUpdate', array('payment', &$element));
			$this->get('reset_cache');
			$translationHelper = hikashop_get('helper.translation');
			if($translationHelper->isMulti()) {
				$columns = array('payment_name', 'payment_description');
				$translationHelper->checkTranslations($element, $columns);
			}
			if($reorder && !empty($element->payment_type)) {
				$orderHelper = hikashop_get('helper.order');
				$orderHelper->pkey = 'payment_id';
				$orderHelper->table = 'payment';
				$orderHelper->groupVal = $element->payment_type;
				$orderHelper->orderingMap = 'payment_ordering';
				$orderHelper->reOrder();
			}
		}

		if($status && !empty($element->payment_published) && !empty($element->payment_id)) {
			$db = JFactory::getDBO();
			$query = 'SELECT payment_type FROM ' . hikashop_table('payment') . ' WHERE payment_id = ' . (int)$element->payment_id;
			$db->setQuery($query);
			$name = $db->loadResult();

			$query = 'UPDATE '.hikashop_table('extensions',false).' SET enabled = 1 WHERE enabled = 0 AND type = ' . $db->Quote('plugin') . ' AND element = ' . $db->Quote($name) . ' AND folder = ' . $db->Quote('hikashoppayment');
			$db->setQuery($query);
			$db->execute();
		}
		return $status;
	}

	function getMethods(&$order, $currency = '') {
		$pluginClass = hikashop_get('class.plugins');
		$shipping = '';
		if(!empty($order->shipping))
			$shipping = $order->shipping[0]->shipping_type.'_'.$order->shipping[0]->shipping_id;
		$rates = $pluginClass->getMethods('payment', '', $shipping, $currency);

		if(bccomp(sprintf('%F',$order->total->prices[0]->price_value), 0, 5) && !empty($rates)) {
			$currencyClass = hikashop_get('class.currency');
			$currencyClass->convertPayments($rates);
		}
		return $rates;
	}

	function &getPayments(&$order, $reset = false) {
		static $usable_methods = null;
		static $errors = array();
		if($reset) {
			$usable_methods = null;
			$errors = array();
		}
		if(!is_null($usable_methods)) {
			$this->errors = $errors;
			return $usable_methods;
		}

		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$max = 0;
		$payment = '';

		if(!empty($order->payment->payment_type) && !empty($order->payment->payment_id))
			$payment = $order->payment->payment_type.'_'.$order->payment->payment_id;

		$currency = @$order->total->prices[0]->price_currency_id;
		if(empty($currency))
			$currency = hikashop_getCurrency();

		$methods = $this->getMethods($order, $currency);

		if(empty($methods)) {
			$errors[] = JText::_('CONFIGURE_YOUR_PAYMENT_METHODS');
			$this->errors = $errors;
			$usable_methods = false;
			return $usable_methods;
		}
		$already = array();
		$price_all = @$order->full_total->prices[0]->price_value_with_tax;
		if(isset($order->full_total->prices[0]->price_value_without_payment_with_tax)) {
			$price_all = $order->full_total->prices[0]->price_value_without_payment_with_tax;
		}

		$zoneClass = hikashop_get('class.zone');
		$config = hikashop_config();
		$zones = $zoneClass->getOrderZones($order, $config->get('payment_methods_zone_address_type','billing_address'));

		foreach($methods as $k => $method) {
			if(!empty($method->payment_zone_namekey) && !in_array($method->payment_zone_namekey, $zones)) {
				unset($methods[$k]);
				continue;
			}

			if(!empty($method->payment_params->payment_zip_prefix) || !empty($method->payment_params->payment_min_zip) || !empty($method->payment_params->payment_max_zip) || !empty($method->payment_params->payment_zip_suffix)) {
				$checkDone = false;
				if(!empty($order->shipping_address) && !empty($order->shipping_address->address_post_code)) {
					if(preg_match('#([a-z]*)([0-9]+)(.*)#i', preg_replace('#[^a-z0-9]#i', '', $order->shipping_address->address_post_code), $match)) {
						$checkDone = true;
						$prefix = $match[1];
						$main = $match[2];
						$suffix = $match[3];
						if(!empty($method->payment_params->payment_zip_prefix) && $method->payment_params->payment_zip_prefix != $prefix) {
							unset($methods[$k]);
							continue;
						}
						if(!empty($method->payment_params->payment_min_zip) && $method->payment_params->payment_min_zip > $main) {
							unset($methods[$k]);
							continue;
						}
						if(!empty($method->payment_params->payment_max_zip) && $method->payment_params->payment_max_zip < $main) {
							unset($methods[$k]);
							continue;
						}
						if(!empty($method->payment_params->payment_zip_suffix) && $method->payment_params->payment_zip_suffix != $suffix) {
							unset($methods[$k]);
							continue;
						}
					}
				}
				if(!$checkDone) {
					unset($methods[$k]);
					continue;
				}
			}
			if(!empty($method->payment_params->payment_zip_regex)) {
				$checkDone = false;
				if(!empty($order->shipping_address) && !empty($order->shipping_address->address_post_code) && preg_match($method->payment_params->payment_zip_regex, $order->shipping_address->address_post_code, $matches))
						$checkDone = true;

				if(!$checkDone) {
					unset($methods[$k]);
					continue;
				}
			}
			$currencyClass = hikashop_get('class.currency');
			if(!empty($method->payment_params->payment_percentage))
				$methods[$k]->payment_price_without_percentage = $methods[$k]->payment_price;
			$methods[$k]->payment_price = $currencyClass->round(($price_all * (float)@$method->payment_params->payment_percentage / 100) + @$method->payment_price, $currencyClass->getRounding($currency,true));

			$methods[$k]->ordering = $method->payment_ordering;

			if(!empty($method->ordering) && $max < $method->ordering){
				$max = $method->ordering;
			}
		}
		foreach($methods as $k => $method) {
			if(empty($method->ordering)) {
				$max++;
				$methods[$k]->ordering = $max;
			}
			while(isset($already[$methods[$k]->ordering])) {
				$max++;
				$methods[$k]->ordering = $max;
			}
			$already[$methods[$k]->ordering] = true;
		}

		$order->paymentOptions = array(
			'recurring' => false,
			'term' => false,
			'refund' => false
		);
		$this->checkPaymentOptions($order);

		$usable_methods = array();
		$app->triggerEvent('onPaymentDisplay', array(&$order, &$methods, &$usable_methods));

		if(is_array($usable_methods) && !empty($usable_methods)) {
			foreach($usable_methods as $k => $usable_method) {
				if(!empty($order->paymentOptions['recurring']) && empty($order->paymentOptions['recurring']['optional']) && empty($usable_method->features['recurring'])) {
					unset($usable_methods[$k]);
					continue;
				}

				if(!empty($order->paymentOptions['term']) && empty($usable_method->features['authorize_capture'])) {
					unset($usable_methods[$k]);
					continue;
				}

				if(!empty($order->paymentOptions['refund']) && empty($usable_method->features['refund'])) {
					unset($usable_methods[$k]);
					continue;
				}
			}
		}

		if(empty($usable_methods)) {
			$message = 'NO_PAYMENT_METHODS_FOUND';
			if(!empty($order->paymentOptions['recurring']) && empty($order->paymentOptions['recurring']['optional']))
				$message = 'NO_RECURRING_PAYMENT_METHODS_FOUND';
			elseif(!empty($order->paymentOptions['term']))
				$message = 'NO_PAYMENT_METHODS_FOUND_SUPPORTING_AUTHORIZE_CAPTURE_MODE';
			elseif(!empty($order->paymentOptions['refund']))
				$message = 'NO_PAYMENT_METHODS_FOUND_SUPPORTING_REFUND_MODE';
			$errors[] = JText::_($message);
			$this->errors = $errors;
			$usable_methods = false;
			return $usable_methods;
		}

		ksort($usable_methods);
		$this->errors = $errors;
		return $usable_methods;
	}

	function computePrice($order, &$payment, $price_all, $payment_price, $currency) {
		$currencyClass = hikashop_get('class.currency');
		$zone_id = hikashop_getZone('shipping');
		$payment->payment_currency_id = $currency;

		if(isset($payment->payment_price_with_tax))
			return;

		if( !empty( $payment->payment_params->payment_algorithm) && $payment->payment_params->payment_algorithm == 'realcost') {
			if( !empty( $payment->payment_params->payment_tax_id) && @$order->full_total->prices[0]->price_value != @$order->full_total->prices[0]->price_value_with_tax) {
				$payment_price_with_tax = $currencyClass->getTaxedPrice($payment_price, $zone_id, $payment->payment_params->payment_tax_id);
				$payment_percentage = ((float)@$payment->payment_params->payment_percentage / 100);
				$payment_percentage_with_tax = $currencyClass->getTaxedPrice($payment_percentage, $zone_id, $payment->payment_params->payment_tax_id);
			} else {
				$payment_price_with_tax = $payment_price;
				$payment_percentage_with_tax = ((float)@$payment->payment_params->payment_percentage / 100);
			}
			$payment_checkout = ($price_all + $payment_price_with_tax) / (1- $payment_percentage_with_tax);
			$payment->payment_price_with_tax = $currencyClass->round($payment_checkout - $price_all, $currencyClass->getRounding( $currency, true));
			$payment->payment_price = $currencyClass->getUntaxedPrice($payment->payment_price_with_tax, $zone_id, $payment->payment_params->payment_tax_id);
			$payment->payment_tax = $payment->payment_price_with_tax - $payment->payment_price;

			return;
		}

		$payment->payment_price = $currencyClass->round(($price_all * (float)@$payment->payment_params->payment_percentage / 100) + $payment_price, $currencyClass->getRounding($currency, true));

		if( !empty( $payment->payment_params->payment_tax_id)) { // && isset($order->full_total->prices[0]->price_value_without_payment_with_tax)) {
			$payment->payment_price_with_tax = $currencyClass->getTaxedPrice($payment->payment_price, $zone_id, $payment->payment_params->payment_tax_id);
			$payment->payment_tax = $payment->payment_price_with_tax - $payment->payment_price;

			return;
		}

		$payment->payment_tax = 0;
		$payment->payment_price_with_tax = $payment->payment_price;

		return;
	}

	public function checkCartMethods(&$cart, $force_selection = false) {
		$cart_payment_ids = array();
		$payment_modified = false;

		$config = hikashop_config();
		$auto_select_default = (int)$config->get('auto_select_default', 2);
		if($auto_select_default == 1 && is_array($cart->usable_methods->payment) && count($cart->usable_methods->payment) > 1)
			$auto_select_default = 0;
		if($force_selection)
			$auto_select_default = 2;

		if($cart->cart_payment_id == 0 && !$auto_select_default)
			return true;

		if($cart->cart_payment_id == 0 && empty($cart->usable_methods->payment))
			return true;

		$found = false;
		if(is_array($cart->usable_methods->payment)){
			foreach($cart->usable_methods->payment as $payment) {
				if($payment->payment_id == $cart->cart_payment_id) {
					$found = true;
					break;
				}
			}
		}
		if($found)
			return true;

		if($auto_select_default && is_array($cart->usable_methods->payment)) {
			$first = reset($cart->usable_methods->payment);
			$cart->cart_payment_id = $first->payment_id;
		} else
			$cart->cart_payment_id = 0;

		return false;
	}

	public function checkPaymentOptions(&$order) {
		if(empty($order->paymentOptions)) {
			$order->paymentOptions = array(
				'recurring' => false,
				'term' => false,
				'refund' => false
			);
		}

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$app->triggerEvent('onCheckPaymentOptions', array( &$order->paymentOptions, &$order ) );

		if(!empty($order->paymentOptions['recurring'])) {
			if(empty($order->order_payment_params))
				$order->order_payment_params = new stdClass();

			$order->order_payment_params->recurring = $order->paymentOptions['recurring'];
		}

		if(!empty($order->paymentOptions['term'])) {
			if(empty($order->order_payment_params))
				$order->order_payment_params = new stdClass();

			$order->order_payment_params->need_authorization = true;

			if(isset($order->order_full_price))
				$order->order_payment_params->authorization_price = $order->order_full_price;
			if(isset($order->full_total)) {
				$order->order_payment_params->authorization_price = $order->full_total->prices[0]->price_value;
				if(isset($order->full_total->prices[0]->price_value_with_tax))
					$order->order_payment_params->authorization_price = $order->full_total->prices[0]->price_value_with_tax;
			}
		}

		if(!empty($order->paymentOptions['refund'])) {
			if(empty($order->order_payment_params))
				$order->order_payment_params = new stdClass();

			if(isset($order->order_full_price))
				$order->order_payment_params->original_price = $order->order_full_price;
			if(isset($order->full_total)) {
				$order->order_payment_params->original_price = $order->full_total->prices[0]->price_value;
				if(isset($order->full_total->prices[0]->price_value_with_tax))
					$order->order_payment_params->original_price = $order->full_total->prices[0]->price_value_with_tax;
			}
		}
	}

	public function readCC() {
		$app = JFactory::getApplication();

		$payment = $app->getUserState(HIKASHOP_COMPONENT.'.payment_method');
		$payment_id = $app->getUserState(HIKASHOP_COMPONENT.'.payment_id');
		$payment_data = $app->getUserState(HIKASHOP_COMPONENT.'.payment_data');
		$ret = true;

		if(empty($payment_data->ask_cc))
			return $ret;

		$cc_number = $app->getUserState(HIKASHOP_COMPONENT.'.cc_number');
		$cc_month = $app->getUserState(HIKASHOP_COMPONENT.'.cc_month');
		$cc_year = $app->getUserState(HIKASHOP_COMPONENT.'.cc_year');
		$cc_CCV = $app->getUserState(HIKASHOP_COMPONENT.'.cc_CCV');
		$cc_type = $app->getUserState(HIKASHOP_COMPONENT.'.cc_type');
		$cc_owner = $app->getUserState(HIKASHOP_COMPONENT.'.cc_owner');
		if(empty($cc_number) || empty($cc_month) || empty($cc_year) || (empty($cc_CCV) && !empty($payment_data->ask_ccv)) || (empty($cc_owner) && !empty($payment_data->ask_owner))) {
			$ret = false;
			$cc_numbers = hikaInput::get()->get('hikashop_credit_card_number', array(), 'array');
			$cc_number='';
			if(!empty($cc_numbers[$payment.'_'.$payment_id])){
				$cc_number=preg_replace('#[^0-9]#','',$cc_numbers[$payment.'_'.$payment_id]);
			}
			$cc_months = hikaInput::get()->get('hikashop_credit_card_month', array(), 'array');
			$cc_month='';
			if(!empty($cc_months[$payment.'_'.$payment_id])){
				$cc_month=substr(preg_replace('#[^0-9]#','',$cc_months[$payment.'_'.$payment_id]),0,2);
				if(strlen($cc_month)==1){
					$cc_month='0'.$cc_month;
				}
			}
			$cc_years = hikaInput::get()->get('hikashop_credit_card_year', array(), 'array');
			$cc_year='';
			if(!empty($cc_years[$payment.'_'.$payment_id])){
				$cc_year=substr(preg_replace('#[^0-9]#','',$cc_years[$payment.'_'.$payment_id]),0,2);
				if(strlen($cc_year)==1){
					$cc_year='0'.$cc_year;
				}
			}
			$cc_CCVs = hikaInput::get()->get('hikashop_credit_card_CCV', array(), 'array');
			$cc_CCV='';
			if(!empty($cc_CCVs[$payment.'_'.$payment_id])){
				$cc_CCV=substr(preg_replace('#[^0-9]#','',$cc_CCVs[$payment.'_'.$payment_id]),0,4);
				if(strlen($cc_CCV)<3){
					$cc_CCV='';
				}
			}
			$cc_types = hikaInput::get()->get('hikashop_credit_card_type', array(), 'array');
			$cc_type='';
			if(!empty($cc_types[$payment.'_'.$payment_id])){
				$cc_type=$cc_types[$payment.'_'.$payment_id];
			}
			$cc_owners = hikaInput::get()->get('hikashop_credit_card_owner', array(), 'array');
			$cc_owner='';
			if(!empty($cc_owners[$payment.'_'.$payment_id])){
				$cc_owner=strip_tags($cc_owners[$payment.'_'.$payment_id]);
			}
			$new_cc_valid = !(empty($cc_number) || empty($cc_month) || empty($cc_year) || (empty($cc_CCV)&&!empty($payment_data->ask_ccv)) || (empty($cc_owner)&&!empty($payment_data->ask_owner)));
			if($new_cc_valid) {
				$app->setUserState(HIKASHOP_COMPONENT.'.cc_number',base64_encode($cc_number));
				$app->setUserState(HIKASHOP_COMPONENT.'.cc_month',base64_encode($cc_month));
				$app->setUserState(HIKASHOP_COMPONENT.'.cc_year',base64_encode($cc_year));
				$app->setUserState(HIKASHOP_COMPONENT.'.cc_CCV',base64_encode($cc_CCV));
				$app->setUserState(HIKASHOP_COMPONENT.'.cc_type',base64_encode($cc_type));
				$app->setUserState(HIKASHOP_COMPONENT.'.cc_owner',base64_encode($cc_owner));

				$ret = true;
			}
		}
		return $ret;
	}

	public function fillListingColumns(&$rows, &$listing_columns, &$view, $type = null) {
		$listing_columns['price'] = array(
			'name' => 'PRODUCT_PRICE',
			'col' => 'col_display_price'
		);
		$listing_columns['restriction'] = array(
			'name' => 'HIKA_RESTRICTIONS',
			'col' => 'col_display_restriction'
		);

		if(empty($rows)) return;

		foreach($rows as &$row) {
			if(!empty($row->payment_params) && is_string($row->payment_params))
				$row->plugin_params = hikashop_unserialize($row->payment_params);

			$row->col_display_price = '';
			if(bccomp(sprintf('%F',$row->payment_price), 0, 3)) {
				$row->col_display_price = $view->currencyClass->displayPrices(array($row), 'payment_price', array('payment_params', 'payment_currency'));
			}
			if(isset($row->plugin_params->payment_percentage) && bccomp(sprintf('%F',$row->plugin_params->payment_percentage), 0, 3)) {
				$row->col_display_price .= '<br/>';
				$row->col_display_price .= $row->plugin_params->payment_percentage.'%';
			}

			$restrictions = array();
			if(!empty($row->plugin_params->payment_min_volume))
				$restrictions[] = JText::_('SHIPPING_MIN_VOLUME') . ':' . $row->plugin_params->payment_min_volume . $row->plugin_params->payment_size_unit;
			if(!empty($row->plugin_params->payment_max_volume))
				$restrictions[] = JText::_('SHIPPING_MAX_VOLUME') . ':' . $row->plugin_params->payment_max_volume . $row->plugin_params->payment_size_unit;

			if(!empty($row->plugin_params->payment_min_weight))
				$restrictions[] = JText::_('SHIPPING_MIN_WEIGHT') . ':' . $row->plugin_params->payment_min_weight . $row->plugin_params->payment_weight_unit;
			if(!empty($row->plugin_params->payment_max_weight))
				$restrictions[] = JText::_('SHIPPING_MAX_WEIGHT') . ':' . $row->plugin_params->payment_max_weight . $row->plugin_params->payment_weight_unit;

			if(isset($row->plugin_params->payment_min_price) && bccomp(sprintf('%F',$row->plugin_params->payment_min_price), 0, 5)) {
				$row->payment_min_price = $row->plugin_params->payment_min_price;
				$restrictions[] = JText::_('SHIPPING_MIN_PRICE') . ':' . $view->currencyClass->displayPrices(array($row), 'payment_min_price', 'payment_currency');
			}
			if(isset($row->plugin_params->payment_max_price) && bccomp(sprintf('%F',$row->plugin_params->payment_max_price), 0, 5)) {
				$row->payment_max_price = $row->plugin_params->payment_max_price;
				$restrictions[] = JText::_('SHIPPING_MAX_PRICE') . ':' . $view->currencyClass->displayPrices(array($row), 'payment_max_price', 'payment_currency');
			}
			if(!empty($row->plugin_params->payment_zip_prefix))
				$restrictions[] = JText::_('SHIPPING_PREFIX') . ':' . $row->plugin_params->payment_zip_prefix;
			if(!empty($row->plugin_params->payment_min_zip))
				$restrictions[] = JText::_('SHIPPING_MIN_ZIP') . ':' . $row->plugin_params->payment_min_zip;
			if(!empty($row->plugin_params->payment_max_zip))
				$restrictions[] = JText::_('SHIPPING_MAX_ZIP') . ':' . $row->plugin_params->payment_max_zip;
			if(!empty($row->plugin_params->payment_zip_suffix))
				$restrictions[] = JText::_('SHIPPING_SUFFIX') . ':' . $row->plugin_params->payment_zip_suffix;
			if(!empty($row->payment_zone_namekey)) {
				$zone = $view->zoneClass->get($row->payment_zone_namekey);
				if(!empty($zone))
					$restrictions[] = JText::_('ZONE') . ':' . $zone->zone_name_english;
				else
					$restrictions[] = JText::_('ZONE') . ':' . 'INVALID';
			}

			if(!empty($row->payment_currency)) {
				$null = null;
				$currency_ids = explode(',', $row->payment_currency);
				$currencies = $view->currencyClass->getCurrencies($currency_ids, $null);
				if(count($currencies)) {
					$list = array();
					foreach($currencies as $c) {
						$list[] = $c->currency_code;
					}
					$restrictions[] = JText::_('CURRENCY') . ': ' . implode(', ', $list);
				}
			}

			$row->col_display_restriction = implode('<br/>', $restrictions);
		}
		unset($row);
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$query = 'SELECT * FROM ' . hikashop_table('payment') . ' WHERE payment_published = 1 ORDER BY payment_ordering';
		$this->db->setQuery($query);
		$ret[0] = $this->db->loadObjectList('payment_id');

		if(!empty($value)) {
			if($mode == hikashopNameboxType::NAMEBOX_SINGLE) {
				$ret[1] = $ret[0][$value];
			} else {
				if(!is_array($value))
					$value = array($value);
				foreach($value as $v) {
					if(isset($ret[0][$v]))
						$ret[1][$v] = $ret[0][$v];
				}
			}
		}

		return $ret;
	}
}
