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
class hikashopShippingPlugin extends hikashopPlugin {
	var $type = 'shipping';
	var $use_cache = true;

	public function onShippingDisplay(&$order, &$dbrates, &$usable_rates, &$messages) {
		$config =& hikashop_config();
		if(!$config->get('force_shipping') && ((isset($order->package['weight']) && $order->package['weight']['value'] <= 0.0) || (isset($order->weight) && bccomp(sprintf('%F',$order->weight), 0, 5) <= 0)))
			return false;
		if(empty($dbrates) || empty($this->name))
			return false;

		$rates = array();
		foreach($dbrates as $k => $rate) {
			if($rate->shipping_type == $this->name && !empty($rate->shipping_published)) {
				$rates[] = $rate;
			}
		}
		if(empty($rates))
			return false;

		if($this->use_cache) {
			if($this->loadShippingCache($order, $usable_rates, $messages))
				return true;
			$local_cache_shipping = array();
			$local_cache_errors = array();
		}

		$currencyClass = hikashop_get('class.currency');
		$shippingClass = hikashop_get('class.shipping');
		$this->volumeHelper = hikashop_get('helper.volume');
		$this->weightHelper = hikashop_get('helper.weight');

		if(!empty($order->cart_currency_id))
			$currentCurrency = $order->cart_currency_id;
		else
			$currentCurrency = hikashop_getCurrency();

		foreach($rates as &$rate) {
			$rate->shippingkey = $shippingClass->getShippingProductsData($order, $order->products);
			$shipping_prices = $order->shipping_prices[$rate->shippingkey];

			if(!isset($rate->shipping_params->shipping_price_use_tax)) $rate->shipping_params->shipping_price_use_tax = 1;

			if(!isset($rate->shipping_params->shipping_virtual_included) || $rate->shipping_params->shipping_virtual_included) {
				if($rate->shipping_params->shipping_price_use_tax)
					$price = $shipping_prices->all_with_tax;
				else
					$price = $shipping_prices->all_without_tax;
				$total_quantity = $shipping_prices->total_quantity;
			} else {
				if($rate->shipping_params->shipping_price_use_tax)
					$price = $shipping_prices->real_with_tax;
				else
					$price = $shipping_prices->real_without_tax;
				$total_quantity = $shipping_prices->total_quantity_real;
			}

			if($rate->shipping_currency_id != $currentCurrency){
				$rate->shipping_price = $currencyClass->convertUniquePrice($rate->shipping_price, $rate->shipping_currency_id, $currentCurrency);
				$rate->shipping_currency_id_orig = $rate->shipping_currency_id;
				$rate->shipping_currency_id = $currentCurrency;
			}
			if(bccomp(sprintf('%F',$price), 0, 5) && isset($rate->shipping_params->shipping_percentage) && bccomp(sprintf('%F',$rate->shipping_params->shipping_percentage), 0, 3)){
				$rate->shipping_price = $rate->shipping_price + $price * $rate->shipping_params->shipping_percentage / 100;
			}
			if(!empty($rate->shipping_params->shipping_formula)) {
				$tags = array('{price}', '{volume}', '{weight}', '{quantity}');
				$values = array($price, $shipping_prices->volume, $shipping_prices->weight, $total_quantity);
				$this->onCalculateShippingFormula($order, $rate, $tags, $values, $rate->shipping_params->shipping_formula);
				$formula = str_replace($tags, $values, $rate->shipping_params->shipping_formula);
				$e = hikashop_get('inc.expression');
				try {
					$result = $e->evaluate($formula);
					$rate->shipping_price = $rate->shipping_price + $result;
				} catch(Exception $e) {
					$app = JFactory::getApplication();
					$app->enqueueMessage($e->getMessage());
				}
			}

			$rate->shipping_price = $currencyClass->round($rate->shipping_price, $currencyClass->getRounding($rate->shipping_currency_id, true));

			if(!empty($rate->shipping_params->shipping_min_price) && bccomp(sprintf('%F',$rate->shipping_params->shipping_min_price), sprintf('%F',$price), 5) == 1)
				$rate->errors['min_price'] = (hikashop_toFloat($rate->shipping_params->shipping_min_price) - $price);

			if(!empty($rate->shipping_params->shipping_max_price) && bccomp(sprintf('%F',$rate->shipping_params->shipping_max_price), sprintf('%F',$price), 5) == -1)
				$rate->errors['max_price'] = ($price - hikashop_toFloat($rate->shipping_params->shipping_max_price));

			if(!empty($rate->shipping_params->shipping_max_volume) && bccomp(sprintf('%F',@$rate->shipping_params->shipping_max_volume), 0, 3)) {
				$rate->shipping_params->shipping_max_volume_orig = $rate->shipping_params->shipping_max_volume;
				$rate->shipping_params->shipping_max_volume = $this->volumeHelper->convert($rate->shipping_params->shipping_max_volume, @$rate->shipping_params->shipping_size_unit);
				if(bccomp(sprintf('%.10F',$rate->shipping_params->shipping_max_volume), sprintf('%.10F',$shipping_prices->volume), 10) == -1)
					$rate->errors['max_volume'] = ($rate->shipping_params->shipping_max_volume - $shipping_prices->volume);
			}

			if(!empty($rate->shipping_params->shipping_min_volume) && bccomp(sprintf('%F',@$rate->shipping_params->shipping_min_volume), 0, 3)) {
				$rate->shipping_params->shipping_min_volume_orig = $rate->shipping_params->shipping_min_volume;
				$rate->shipping_params->shipping_min_volume = $this->volumeHelper->convert($rate->shipping_params->shipping_min_volume, @$rate->shipping_params->shipping_size_unit);
				if(bccomp(sprintf('%.10F',$rate->shipping_params->shipping_min_volume), sprintf('%.10F',$shipping_prices->volume), 10) == 1)
					$rate->errors['min_volume'] = ($shipping_prices->volume - $rate->shipping_params->shipping_min_volume);
			}

			if(!empty($rate->shipping_params->shipping_max_weight) && bccomp(sprintf('%F',@$rate->shipping_params->shipping_max_weight), 0, 3)) {
				$rate->shipping_params->shipping_max_weight_orig = $rate->shipping_params->shipping_max_weight;
				$rate->shipping_params->shipping_max_weight = $this->weightHelper->convert($rate->shipping_params->shipping_max_weight, @$rate->shipping_params->shipping_weight_unit);
				if(bccomp(sprintf('%.3F',$rate->shipping_params->shipping_max_weight), sprintf('%.3F',$shipping_prices->weight), 3) == -1)
					$rate->errors['max_weight'] = ($rate->shipping_params->shipping_max_weight - $shipping_prices->weight);
			}

			if(!empty($rate->shipping_params->shipping_min_weight) && bccomp(sprintf('%F',@$rate->shipping_params->shipping_min_weight),0,3)){
				$rate->shipping_params->shipping_min_weight_orig = $rate->shipping_params->shipping_min_weight;
				$rate->shipping_params->shipping_min_weight = (float)$this->weightHelper->convert($rate->shipping_params->shipping_min_weight, @$rate->shipping_params->shipping_weight_unit);
				if(bccomp(sprintf('%.3F',$rate->shipping_params->shipping_min_weight), sprintf('%.3F',$shipping_prices->weight), 3) == 1)
					$rate->errors['min_weight'] = ($shipping_prices->weight - $rate->shipping_params->shipping_min_weight);
			}

			if(!empty($rate->shipping_params->shipping_max_quantity) && (int)$rate->shipping_params->shipping_max_quantity) {
				if((int)$rate->shipping_params->shipping_max_quantity < (int)$shipping_prices->total_quantity)
					$rate->errors['max_quantity'] = ($rate->shipping_params->shipping_max_quantity - $shipping_prices->total_quantity);
			}
			if(!empty($rate->shipping_params->shipping_min_quantity) && (int)$rate->shipping_params->shipping_min_quantity){
				if((int)$rate->shipping_params->shipping_min_quantity > (int)$shipping_prices->total_quantity)
					$rate->errors['min_quantity'] = ($shipping_prices->total_quantity - $rate->shipping_params->shipping_min_quantity);
			}

			if(isset($rate->shipping_params->shipping_per_product) && $rate->shipping_params->shipping_per_product) {
				if(!isset($order->shipping_prices[$rate->shippingkey]->price_per_product)){
					$order->shipping_prices[$rate->shippingkey]->price_per_product = array();
				}
				$order->shipping_prices[$rate->shippingkey]->price_per_product[$rate->shipping_id] = array(
					'price' => (float)$rate->shipping_params->shipping_price_per_product,
					'products' => array()
				);
			}

			unset($rate);
		}

		foreach($order->shipping_prices as $key => $shipping_price) {
			if(empty($shipping_price->price_per_product) || empty($shipping_price->products))
				continue;

			$shipping_ids = array_keys($shipping_price->price_per_product);
			hikashop_toInteger($shipping_ids);

			$product_ids = array_keys($shipping_price->products);
			hikashop_toInteger($product_ids);

			$implode_product_ids = implode(',', $product_ids);
			if(empty($product_ids) || empty($implode_product_ids))
				continue;
			$query = 'SELECT a.shipping_id, a.shipping_price_ref_id as `ref_id`, a.shipping_price_min_quantity as `min_quantity`, a.shipping_price_value as `price`, a.shipping_fee_value as `fee`, a.shipping_blocked as `blocked`'.
				' FROM ' . hikashop_table('shipping_price') . ' AS a '.
				' WHERE a.shipping_id IN (' . implode(',', $shipping_ids) . ') '.
				' AND a.shipping_price_ref_id IN (' . implode(',', $product_ids) . ') AND a.shipping_price_ref_type = \'product\' '.
				' ORDER BY a.shipping_id, a.shipping_price_ref_id, a.shipping_price_min_quantity';

			$db = JFactory::getDBO();
			$db->setQuery($query);
			$ret = $db->loadObjectList();
			if(empty($ret))
				continue;

			$products_qty = $shipping_price->products;

			foreach($order->products as $ordered_product) {
				if($ordered_product->product_parent_id == 0)
					continue;
				foreach($ret as $ship) {
					if($ordered_product->product_id == $ship->ref_id) {
						$products_qty[ (int)$ordered_product->product_parent_id ] -= $products_qty[ (int)$ordered_product->product_id ];
					}
				}
			}
			foreach($ret as $ship) {
				if(!isset($order->shipping_prices[$key]->price_per_product[$ship->shipping_id]['blocked']))
					$order->shipping_prices[$key]->price_per_product[$ship->shipping_id]['blocked'] = 0;
				if($products_qty[$ship->ref_id] > 0 && $ship->min_quantity <= $products_qty[$ship->ref_id] && $ship->blocked)
					$order->shipping_prices[$key]->price_per_product[$ship->shipping_id]['blocked'] = 1;

				if($products_qty[$ship->ref_id] > 0 && $ship->min_quantity <= $products_qty[$ship->ref_id])
					$order->shipping_prices[$key]->price_per_product[$ship->shipping_id]['products'][$ship->ref_id] = ($ship->price * $products_qty[$ship->ref_id]) + $ship->fee;
			}
			unset($products_qty);
		}

		foreach($rates as &$rate) {
			if(!isset($rate->shippingkey))
				continue;

			$shipping_prices =& $order->shipping_prices[$rate->shippingkey];

			if(isset($shipping_prices->price_per_product[$rate->shipping_id]) && !empty($order->products)) {
				$rate_prices =& $order->shipping_prices[$rate->shippingkey]->price_per_product[$rate->shipping_id];

				$price = 0;
				$rate_prices['products']['product_names'] = array();
				foreach($order->products as $k => $row) {
					if(!empty($rate->products) && !in_array($row->product_id, $rate->products))
						continue;

					if(isset($rate_prices['products'][$row->product_id])) {
						$price += $rate_prices['products'][$row->product_id];
						if($rate_prices['blocked'])
							$rate_prices['products']['product_names'][] = '"' . $row->product_name . '"';
						$rate_prices['products'][$row->product_id] = 0;
					} elseif(isset($rate_prices['products'][$row->product_parent_id])) {
						$price += $rate_prices['products'][$row->product_parent_id];
						if($rate_prices['blocked'])
							$rate_prices['products']['product_names'][] = '"' . $order->products['p'.$row->product_id]->product_name . '"';
						$rate_prices['products'][$row->product_parent_id] = 0;
					} elseif(!isset($rate->shipping_params->shipping_virtual_included) || $rate->shipping_params->shipping_virtual_included || $row->product_weight > 0) {
						$price += $rate_prices['price'] * $row->cart_product_quantity;
					}
				}
				if(count($rate_prices['products']['product_names'])) {
					$rate->errors['X_PRODUCTS_ARE_NOT_SHIPPABLE_TO_YOU'] = implode(', ', $rate_prices['products']['product_names']);
					$rate->errors['X_PRODUCTS_ARE_NOT_SHIPPABLE_TO_YOU'] = '';
					foreach($rate_prices['products']['product_names'] as $product_name) {
						if(empty($product_name) || $product_name == '""')
							continue;
						$rate->errors['X_PRODUCTS_ARE_NOT_SHIPPABLE_TO_YOU'] .= $product_name . ', ';
					}
					trim($rate->errors['X_PRODUCTS_ARE_NOT_SHIPPABLE_TO_YOU'], ', ');
				} else {
					if(!isset($rate->shipping_price_base))
						$rate->shipping_price_base = hikashop_toFloat($rate->shipping_price);
					else
						$rate->shipping_price = $rate->shipping_price_base;
					$rate->shipping_price = $currencyClass->round($rate->shipping_price + $price, $currencyClass->getRounding($rate->shipping_currency_id, true));
				}

				unset($rate_prices);
			}

			unset($shipping_prices);

			if(empty($rate->errors)) {
				$usable_rates[$rate->shipping_id] = $rate;
				if($this->use_cache)
					$local_cache_shipping[$rate->shipping_id] = $rate;
			} else {
				$messages[] = $rate->errors;
				if($this->use_cache)
					$local_cache_errors[] = $rate->errors;
			}
		}

		if($this->use_cache)
			$this->setShippingCache($order, $local_cache_shipping, $local_cache_errors);

		return true;
	}

	function onCalculateShippingFormula(&$order, &$rate, &$tags, &$values, &$formula) {

	}

	public function onShippingSave(&$cart, &$methods, &$shipping_id, $warehouse_id = null) {
		$usable_methods = array();
		$errors = array();

		$shipping = hikashop_get('class.shipping');
		$usable_methods = $shipping->getShippings($cart);

		if(is_numeric($warehouse_id)) $warehouse_id = (int)$warehouse_id;

		foreach($usable_methods as $k => $usable_method) {
			if(is_numeric($usable_method->shipping_warehouse_id)) $usable_method->shipping_warehouse_id = (int)$usable_method->shipping_warehouse_id;
			if(($usable_method->shipping_id == $shipping_id) && ($warehouse_id === null || (isset($usable_method->shipping_warehouse_id) && $usable_method->shipping_warehouse_id === $warehouse_id)))
				return $usable_method;
		}
		return false;
	}

	public function onShippingCustomSave(&$cart, &$method, $warehouse, $formData) {
		return $formData;
	}

	public function onShippingConfiguration(&$element) {
		$this->pluginConfiguration($element);

		if(empty($element) || empty($element->shipping_type)) {
			$element = new stdClass();
			$element->shipping_type = $this->pluginName;
			$element->shipping_params = new stdClass();
			$this->getShippingDefaultValues($element);
		}

		$this->currency = hikashop_get('type.currency');
		$this->weight = hikashop_get('type.weight');
		$this->volume = hikashop_get('type.volume');
	}

	public function onShippingConfigurationSave(&$element) {
		if(empty($this->pluginConfig))
			return true;

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(!isset($formData['shipping']['shipping_params']))
			return true;

		foreach($this->pluginConfig as $key => $config) {
			if($config[1] == 'textarea' || $config[1] == 'big-textarea') {
				$element->shipping_params->$key = @$formData['shipping']['shipping_params'][$key];
			}
		}
		return true;
	}

	public function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		$this->order = $order;
		return true;
	}

	public function getShippingCache(&$order) {
		if(empty($this->name) || empty($order->cache->shipping) || empty($order->cache->shipping_key))
			return false;
		$key = $order->cache->shipping_key;
		if(empty($order->cache->shipping[$key]))
			return false;
		if(isset($order->shipping_warehouse_id)) {
			if(isset($order->cache->shipping[$key][(int)$order->shipping_warehouse_id][$this->name]))
				return $order->cache->shipping[$key][(int)$order->shipping_warehouse_id][ $this->name ];
			return false;
		}
		if(isset($order->cache->shipping[$key][$this->name]))
			return $order->cache->shipping[$key][ $this->name ];
		return false;
	}

	public function loadShippingCache(&$order, &$usable_rates, &$messages) {
		$cache = $this->getShippingCache($order);
		if($cache === false)
			return false;

		list($methods, $msg) = $cache;
		if(!empty($methods)) {
			foreach($methods as $i => $rate) {
				$usable_rates[$rate->shipping_id] = $rate;
			}
		}
		if(!empty($msg)) {
			foreach($msg as $i => $err) {
				$messages[] = $err;
			}
		}
		return true;
	}

	public function setShippingCache(&$order, $data, $messages = null) {
		if(empty($this->name) || empty($order->cache->shipping_key))
			return false;
		$key = $order->cache->shipping_key;

		if(empty($order->cache->shipping)) $order->cache->shipping = array();
		if(empty($order->cache->shipping[$key])) $order->cache->shipping[$key] = array();

		if(isset($order->shipping_warehouse_id)) {
			if(empty($order->cache->shipping[$key][(int)$order->shipping_warehouse_id]))
				$order->cache->shipping[$key][(int)$order->shipping_warehouse_id] = array();
			$order->cache->shipping[$key][(int)$order->shipping_warehouse_id][$this->name] = array($data, $messages);
			return true;
		}
		$order->cache->shipping[$key][ $this->name ] = array($data, $messages);
		return false;
	}

	public function getShippingAddress($id = 0, $order = null) {
		$app = JFactory::getApplication();
		if($id == 0 && !hikashop_isClient('administrator')) {
			$id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
			if(!empty($id) && is_array($id))
				$id = (int)reset($id);
			else
				$id = 0;
		} elseif(is_array($id)) {
			$id = (int)reset($id);
		}

		if(empty($id))
			return false;

		$shippingClass = hikashop_get('class.shipping');
		$shipping = $shippingClass->get($id);
		if(!$shipping || $shipping->shipping_type != $this->name)
			return false;

		$params = $shipping->shipping_params;
		if(is_string($params) && !empty($params))
			$params = hikashop_unserialize($params);
		$override = 0;
		if(isset($params->shipping_override_address)) {
			$override = (int)$params->shipping_override_address;
		}

		switch($override) {
			case 4:
				if(!empty($params->shipping_override_address_text))
					return $params->shipping_override_address_text;
				break;
			case 3:
				if(!empty($params->shipping_override_address_text))
					return str_replace(array("\r\n","\n","\r"),"<br/>", htmlentities($params->shipping_override_address_text, ENT_COMPAT, 'UTF-8') );
				break;
			case 2:
				return '';
			case 1:
				$config =& hikashop_config();
				return str_replace(array("\r\n","\n","\r"),"<br/>", $config->get('store_address'));
			case 0:
			default:
				return false;
		}
		return false;
	}

	public function getShippingDefaultValues(&$element) {}

	public function getOrderPackage(&$order, $options = array()) {
		$ret = array();
		if(empty($order->products))
			return array('w' => 0, 'x' => 0, 'y' => 0, 'z' => 0, 'price' => 0);

		$weight_unit = !empty($order->weight_unit) ? $order->weight_unit : 'lb';
		$volume_unit = !empty($order->volume_unit) ? $order->volume_unit : 'in';

		if(!empty($options['weight_unit']))
			$weight_unit = $options['weight_unit'];
		if(!empty($options['volume_unit']))
			$volume_unit = $options['volume_unit'];

		$current = array('w' => 0, 'x' => 0, 'y' => 0, 'z' => 0, 'price' => 0);
		$error = false;
		foreach($order->products as $k => $product) {
			$qty = 1;
			if(isset($product->cart_product_quantity))
				$qty = (int)$product->cart_product_quantity;
			if(isset($product->order_product_quantity))
				$qty = (int)$product->order_product_quantity;

			if($qty == 0)
				continue;

			$weight = 0;
			if($product->product_weight_unit == $weight_unit) {
				$weight += ((float)$product->product_weight);
			} else if(!empty($product->product_weight_unit_orig) && $product->product_weight_unit_orig == $weight_unit) {
				$weight += ((float)hikashop_toFloat($product->product_weight_orig));
			} else {
				if(empty($this->weightHelper))
					$this->weightHelper = hikashop_get('helper.weight');
				$weight += ((float)$this->weightHelper->convert($product->product_weight, $product->product_weight_unit, $weight_unit));
			}

			if($weight == 0)
				continue;

			$w = (float)hikashop_toFloat($product->product_width);
			$h = (float)hikashop_toFloat($product->product_height);
			$l = (float)hikashop_toFloat($product->product_length);
			$price = 0;
			if(isset($product->prices[0]->unit_price->price_value))
				$price = (float)$product->prices[0]->unit_price->price_value;

			if($product->product_dimension_unit !== $volume_unit) {
				if(empty($this->volumeHelper))
					$this->volumeHelper = hikashop_get('helper.volume');
				if(!empty($w))
					$w = $this->volumeHelper->convert($w, $product->product_dimension_unit, $volume_unit, 'dimension');
				if(!empty($h))
					$h = $this->volumeHelper->convert($h, $product->product_dimension_unit, $volume_unit, 'dimension');
				if(!empty($l))
					$l = $this->volumeHelper->convert($l, $product->product_dimension_unit, $volume_unit, 'dimension');
			}

			$d = array($w,$h,$l);
			sort($d); // x = d[0] // y = d[1] // z = d[2]
			$p = array(
				'w' => $weight,
				'x' => $d[0],
				'y' => $d[1],
				'z' => $d[2],
				'price' => $price,
			);

			if(!empty($options['required_dimensions'])) {
				if(!$this->checkDimensions($product, $p, $options['required_dimensions'])) {
					$error = true;
					continue;
				}
			}
			if(!empty($options['limit'])) {
				$total_quantity = $qty;

				while ($total_quantity > 0) {
					foreach ($options['limit'] as $limit_key => $limit_value) {
						$valid = $this->processPackageLimit($limit_key, $limit_value , $p, $total_quantity, $current, array('weight' => $weight_unit, 'volume' => $volume_unit));

						if ($valid === false)
							$total_quantity = 0;
						else if (is_int($valid))
							$total_quantity = min($total_quantity, $valid);

						if ($total_quantity === 0)
							break;
					}

					if ($total_quantity === 0) {
						if(empty($current['w']) && empty($current['x']) && empty($current['y']) && empty($current['z']))
							return false;

						$ret[] = $current;
						$total_quantity = $qty;
						$current = array('w' => 0, 'x' => 0, 'y' => 0, 'z' => 0, 'price' => 0);
					} else if($total_quantity < $qty) {
						$factor = 1;
						if(empty($current['w']) && empty($current['x']) && empty($current['y']) && empty($current['z']) && $total_quantity*2 <= $qty)
							$factor = floor($qty / $total_quantity);

						$current['w'] += $weight * $total_quantity;
						$current['x'] += ($d[0] * $total_quantity);
						$current['y'] = max($current['y'], $d[1]);
						$current['z'] = max($current['z'], $d[2]);
						$current['price'] += $price * $total_quantity;
						$ret[] = $current;

						for($i = 1; $i < $factor; $i++) {
							$ret[] = $current;
						}

						$current = array('w' => 0, 'x' => 0, 'y' => 0, 'z' => 0, 'price' => 0);
						$qty -= $total_quantity * $factor;
						$total_quantity = $qty;
					} else
						$total_quantity = 0;
				}
			}
			if($qty > 0) {
				$current['w'] += $weight * $qty;
				$current['x'] += ($d[0] * $qty);
				$current['y'] = max($current['y'], $d[1]);
				$current['z'] = max($current['z'], $d[2]);
				$current['price'] += $price * $qty;
			}
		}
		if($error)
			return false;
		if(empty($ret))
			return $current;
		if($current['w'] != 0 || $current['x'] != 0 || $current['y'] != 0 || $current['z'] != 0)
			$ret[] = $current;
		return $ret;
	}

	public function checkDimensions($product, $dimensions, $requirements = array()) {
		if(empty($requirements) || !count($requirements))
			return true;

		if(empty($dimensions['w']) && empty($dimensions['x']) && empty($dimensions['y']) && empty($dimensions['z']))
			return true;

		$available_requirements = array(
			'w' => 'PRODUCT_WEIGHT',
			'x' => 'PRODUCT_WIDTH',
			'y' => 'PRODUCT_LENGTH',
			'z' => 'PRODUCT_HEIGHT',
		);

		$return = true;
		static $already = array();
		foreach($requirements as $requirement){
			if(!empty($dimensions[$requirement]))
				continue;

			if(!isset($available_requirements[$requirement]))
				continue;
			$dimension = $available_requirements[$requirement];

			if(empty($already[$dimension . '_' . $product->product_id])) {
				$already[$dimension . '_' . $product->product_id] = true;
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('THE_X_IS_MISSING_FOR_THE_PRODUCT_X', JText::_($dimension), $product->product_name));
			}
			$return = false;
		}
		return $return;
	}

	public function processPackageLimit($limit_key, $limit_value , $product, $qty, $package, $units) {
		switch ($limit_key) {
			case 'unit':
				if($qty > $limit_value)
					return (int)$limit_value;
				return (int)$qty;
			case 'x':
				if(empty($product['x']) || $product['x'] > $limit_value)
					return false;
				$limit_value = max(0.0, $limit_value - $package['x']);
				return (int)floor($limit_value / $product['x']);
			case 'y':
				if(empty($product['y']) || $product['y'] > $limit_value)
					return false;
				return (int)floor($limit_value / $product['y']);
			case 'z':
				if(empty($product['z']) || $product['z'] > $limit_value)
					return false;
				return (int)floor($limit_value / $product['z']);
			case 'w':
				if(empty($product['w']) || $product['w'] > $limit_value)
					return false;
				$limit_value = max(0.0, $limit_value - $package['w']);
				return (int)floor($limit_value / $product['w']);
		}
		return 0;
	}

	public function groupPackages(&$data, $caracs) {
		$data['weight_unit'] = $caracs['weight_unit'];
		$data['dimension_unit'] = $caracs['dimension_unit'];
		$tmpHeight = $data['height'] + round($caracs['height'], 2);
		$tmpLength = $data['length'] + round($caracs['length'], 2);
		$tmpWidth = $data['width'] + round($caracs['width'], 2);
		$dim = $tmpLength + (2 * $tmpWidth) + (2 * $tmpHeight);

		$d = array($caracs['width'], $caracs['height'], $caracs['length']);
		sort($d);

		return array(
			'x' => $d[0],
			'y' => $d[1],
			'z' => $d[2],
			'dim' => $dim,
			'tmpHeight' => $tmpHeight,
			'tmpLength' => $tmpLength,
			'tmpWidth' => $tmpWidth,
		);
	}

	function _convertCharacteristics(&$product, $data, $forceUnit = false) {
		$carac = array();

		if(!isset($product->product_dimension_unit_orig))
			$product->product_dimension_unit_orig = $product->product_dimension_unit;
		if(!isset($product->product_weight_unit_orig))
			$product->product_weight_unit_orig = $product->product_weight_unit;
		if(!isset($product->product_weight_orig))
			$product->product_weight_orig = $product->product_weight;

		if($forceUnit) {
			if(empty($this->weightHelper))
				$this->weightHelper = hikashop_get('helper.weight');
			if(empty($this->volumeHelper))
				$this->volumeHelper = hikashop_get('helper.volume');
			$carac['weight'] = $this->weightHelper->convert($product->product_weight_orig, $product->product_weight_unit_orig, 'lb');
			$carac['weight_unit'] = 'LBS';
			$carac['height'] = $this->volumeHelper->convert($product->product_height, $product->product_dimension_unit_orig, 'in' , 'dimension');
			$carac['length'] = $this->volumeHelper->convert($product->product_length, $product->product_dimension_unit_orig, 'in', 'dimension');
			$carac['width'] = $this->volumeHelper->convert($product->product_width, $product->product_dimension_unit_orig, 'in', 'dimension');
			$carac['dimension_unit'] = 'IN';
			return $carac;
		}

		if(empty($data['units']))
			$data['units'] = 'kg';
		$c = ($data['units'] == 'kg') ? array('v' => 'kg', 'vu' => 'KGS', 'd' => 'cm', 'du' => 'CM' ) : array('v' => 'lb', 'vu' => 'LBS', 'd' => 'in', 'du' => 'IN');
		if($product->product_weight_unit_orig == $c['v']){
			$carac['weight'] = $product->product_weight_orig;
			$carac['weight_unit'] = $this->convertUnit[$product->product_weight_unit_orig];
		} else {
			if(empty($this->weightHelper))
				$this->weightHelper = hikashop_get('helper.weight');
			$carac['weight'] = $this->weightHelper->convert($product->product_weight_orig, $product->product_weight_unit_orig, $c['v']);
			$carac['weight_unit'] = $c['vu'];
		}

		if($product->product_dimension_unit_orig == $c['d']) {
			$carac['height'] = $product->product_height;
			$carac['length'] = $product->product_length;
			$carac['width'] = $product->product_width;
			$carac['dimension_unit'] = $this->convertUnit[$product->product_dimension_unit_orig];
		} else {
			if(empty($this->volumeHelper))
				$this->volumeHelper = hikashop_get('helper.volume');
			$carac['height'] = $this->volumeHelper->convert($product->product_height, $product->product_dimension_unit_orig, $c['d'], 'dimension');
			$carac['length'] = $this->volumeHelper->convert($product->product_length, $product->product_dimension_unit_orig, $c['d'], 'dimension');
			$carac['width'] = $this->volumeHelper->convert($product->product_width, $product->product_dimension_unit_orig, $c['d'], 'dimension');
			$carac['dimension_unit'] = $c['du'];
		}
		return $carac;
	}

	function _currencyConversion(&$usableMethods, &$order) {
		$currency = $this->shipping_currency_id;
		$currencyClass = hikashop_get('class.currency');
		foreach($usableMethods as $i => $method){
			if((int)$method['currency_id'] == (int)$currency)
				continue;

			$usableMethods[$i]['value'] = $currencyClass->convertUniquePrice($method['value'], (int)$method['currency_id'], $currency);
			$usableMethods[$i]['old_currency_id'] = (int)$usableMethods[$i]['currency_id'];
			$usableMethods[$i]['old_currency_code'] = $usableMethods[$i]['currency_code'];
			$usableMethods[$i]['currency_id'] = (int)$currency;
			$usableMethods[$i]['currency_code'] = $this->shipping_currency_code;
		}
		return $usableMethods;
	}

	function displayDelaySECtoDAY($value, $type) {
		$c = array(
			0 => 60, // Min
			1 => 3600, // Hour
			2 => 86400 // Day
		);
		if(!empty($c[$type]))
			return round( (int)$value / $c[$type] );
		return $value;
	}
}
