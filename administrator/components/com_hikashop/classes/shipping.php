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
class hikashopShippingClass extends hikashopClass {
	var $tables = array('shipping');
	var $pkeys = array('shipping_id');
	var $deleteToggle = array('shipping' => array('shipping_type', 'shipping_id'));
	var $toggle = array('shipping_published' => 'shipping_id');

	function save(&$element, $reorder = true) {
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$new = empty($element->shipping_id);
		if($new)
			$app->triggerEvent('onBeforeHikaPluginCreate', array('shipping', &$element, &$do));
		else {
			if(!isset($element->old))
				$element->old = parent::get($element->shipping_id);
			$app->triggerEvent('onBeforeHikaPluginUpdate', array('shipping', &$element, &$do));
		}

		if(!$do)
			return false;

		if(isset($element->shipping_params) && !is_string($element->shipping_params)){
			$element->shipping_params = serialize($element->shipping_params);
		}

		if(isset($element->shipping_currency) && is_array($element->shipping_currency)) {
			$element->shipping_currency = implode(",", $element->shipping_currency);
			if(!empty($element->shipping_currency))
				$element->shipping_currency = ','.$element->shipping_currency.',';
		}

		$status = parent::save($element);
		if(!$status)
			return $status;

		$this->get('reset_cache');

		if($status) {
			if($new)
				$app->triggerEvent('onAfterHikaPluginCreate', array('shipping', &$element));
			else
				$app->triggerEvent('onAfterHikaPluginUpdate', array('shipping', &$element));
		}

		$translationHelper = hikashop_get('helper.translation');
		if($translationHelper->isMulti()) {
			$columns = array('shipping_name', 'shipping_description');
			$translationHelper->checkTranslations($element, $columns);
		}

		if(empty($element->shipping_id)) {
			$element->shipping_id = $status;
			if($reorder) {
				$orderHelper = hikashop_get('helper.order');
				$orderHelper->pkey = 'shipping_id';
				$orderHelper->table = 'shipping';
				$orderHelper->groupMap = 'shipping_type';
				$orderHelper->groupVal = $element->shipping_type;
				$orderHelper->orderingMap = 'shipping_ordering';
				$orderHelper->reOrder();
			}
		}

		if(!empty($element->shipping_published) && !empty($element->shipping_id)) {
			$db = JFactory::getDBO();
			$query = 'SELECT shipping_type FROM ' . hikashop_table('shipping') . ' WHERE shipping_id = ' . (int)$element->shipping_id;
			$db->setQuery($query);
			$name = $db->loadResult();

			$query = 'UPDATE '.hikashop_table('extensions',false).' SET enabled = 1 WHERE enabled = 0 AND type = ' . $db->Quote('plugin') . ' AND element = ' . $db->Quote($name) . ' AND folder = ' . $db->Quote('hikashopshipping');
			$db->setQuery($query);
			$db->execute();
		}
		return $status;
	}

	function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeHikaPluginDelete', array('shipping', &$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterHikaPluginDelete', array('shipping', &$elements));
		}
		if(!$status)
			return $status;

		$orderHelper = hikashop_get('helper.order');
		$orderHelper->pkey = 'shipping_id';
		$orderHelper->table = 'shipping';
		$orderHelper->groupMap = 'shipping_type';
		$orderHelper->orderingMap = 'shipping_ordering';
		$app = JFactory::getApplication();
		$orderHelper->groupVal = $app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.shipping_plugin_type','shipping_plugin_type','manual');
		$orderHelper->reOrder();
		return $status;
	}

	function get($id, $default = '') {
		static $cachedElements = array();
		if($id === 'reset_cache') {
			$cachedElements = array();
			return null;
		}
		if(is_array($id))
			$cache_id = implode(',', $id);
		else
			$cache_id = (int)$id;

		if(!isset($cachedElements[$cache_id])) {
			$result = parent::get($id, $default);
			if(is_object($result) && !empty($result->shipping_params)){
				$result->shipping_params = hikashop_unserialize($result->shipping_params);
				if(!empty($result->shipping_name))
					$result->shipping_name = hikashop_translate($result->shipping_name);
				if(!empty($result->shipping_description))
					$result->shipping_description = hikashop_translate($result->shipping_description);
			} else if(is_array($id) && is_array($result)) {
				foreach($result as &$r) {
					if(!empty($r->shipping_params))
						$r->shipping_params = hikashop_unserialize($r->shipping_params);

					if(!empty($r->shipping_name))
						$r->shipping_name = hikashop_translate($r->shipping_name);

					if(!empty($r->shipping_description))
						$r->shipping_description = hikashop_translate($r->shipping_description);
				}
			}
			$cachedElements[$cache_id] = $result;
		}
		if(is_array($id) && !is_array($cachedElements[$cache_id]))
			return array($cachedElements[$cache_id]);
		return $cachedElements[$cache_id];
	}

	function getMethods(&$order, $currency = ''){
		$pluginClass = hikashop_get('class.plugins');
		$rates = $pluginClass->getMethods('shipping', '', '', $currency);

		if(isset($order->total->prices[0]->price_value) && bccomp(sprintf('%F',$order->total->prices[0]->price_value),0,5) && !empty($rates)){
			$currencyClass = hikashop_get('class.currency');
			$currencyClass->convertShippings($rates);
		}
		return $rates;
	}

	function &getShippings(&$order, $reset = false) {
		static $usable_methods = null;
		static $shipping_groups = null;
		static $errors = array();
		if($reset) {
			$usable_methods = null;
			$errors = array();
			$shipping_groups = null;
		}
		if($reset === 'return')
			return $usable_methods;
		if(!is_null($usable_methods)) {
			$this->errors = $errors;
			$order->shipping_groups =& $shipping_groups;
			return $usable_methods;
		}

		$config =& hikashop_config();
		$usable_methods = array();

		if(!$config->get('force_shipping') && ((isset($order->package['weight']) && $order->package['weight']['value'] <= 0.0) || (isset($order->weight) && bccomp(sprintf('%F',$order->weight), 0, 5) <= 0)))
			return $usable_methods;

		$this->getShippingProductsData($order);

		$zoneClass = hikashop_get('class.zone');
		$zones = $zoneClass->getOrderZones($order, $config->get('shipping_methods_zone_address_type','shipping_address'));

		$currency = @$order->total->prices[0]->price_currency_id;
		if(empty($currency))
			$currency = hikashop_getCurrency();

		$rates = $this->getMethods($order,$currency);


		if(empty($rates)) {
			$errors['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
			$this->errors = $errors;
			return $usable_methods;
		}

		$app = JFactory::getApplication();
		$order_clone = new stdClass();
		$variables = array('products','cart_id','coupon','shipping_address','volume','weight','volume_unit','weight_unit');
		foreach($variables as $var){
			if(isset($order->$var)) $order_clone->$var = $order->$var;
		}
		$use_cache = $config->get('use_shipping_cache', true);
		$shipping_key = sha1(serialize($order_clone).serialize($rates));
		if(!isset($order->cache))
			$order->cache = new stdClass();
		$order->cache->shipping_key = $shipping_key;
		$order->cache->shipping = null;
		if($use_cache)
			$order->cache->shipping = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_cache.usable_methods', null);


		foreach($rates as $k => $rate) {
			if(!empty($rate->shipping_zone_namekey) && !in_array($rate->shipping_zone_namekey, $zones)) {
				unset($rates[$k]);
				continue;
			}

			if(!empty($rate->shipping_params->shipping_zip_prefix) || !empty($rate->shipping_params->shipping_min_zip) || !empty($rate->shipping_params->shipping_max_zip) || !empty($rate->shipping_params->shipping_zip_suffix)) {
				$checkDone = false;
				if(!empty($order->shipping_address) && !empty($order->shipping_address->address_post_code)) {
					if(preg_match('#([a-z]*)([0-9]+)(.*)#i', preg_replace('#[^a-z0-9]#i', '', $order->shipping_address->address_post_code), $match)) {
						$checkDone = true;
						$prefix = strtolower($match[1]);
						$main = $match[2];
						$suffix = strtolower($match[3]);
						if(!empty($rate->shipping_params->shipping_zip_prefix) && strtolower($rate->shipping_params->shipping_zip_prefix) != $prefix) {
							unset($rates[$k]);
							continue;
						}
						if(!empty($rate->shipping_params->shipping_min_zip) && $rate->shipping_params->shipping_min_zip > $main) {
							unset($rates[$k]);
							continue;
						}
						if(!empty($rate->shipping_params->shipping_max_zip) && $rate->shipping_params->shipping_max_zip < $main) {
							unset($rates[$k]);
							continue;
						}
						if(!empty($rate->shipping_params->shipping_zip_suffix) && strtolower($rate->shipping_params->shipping_zip_suffix) != $suffix) {
							unset($rates[$k]);
							continue;
						}
					}
				}
				if(!$checkDone) {
					unset($rates[$k]);
					continue;
				}
			}
			if(!empty($rate->shipping_params->shipping_zip_regex)) {
				$checkDone = false;
				if(!empty($order->shipping_address) && !empty($order->shipping_address->address_post_code) && preg_match($rate->shipping_params->shipping_zip_regex, $order->shipping_address->address_post_code, $matches))
						$checkDone = true;

				if(!$checkDone) {
					unset($rates[$k]);
					continue;
				}
			}
		}

		if(empty($rates)) {
			if(hikashop_loadUser())
				$errors['no_shipping_to_your_zone'] = JText::_('NO_SHIPPING_TO_YOUR_ZONE');
			$this->errors = $errors;
			return $usable_methods;
		}

		$shipping_groups = $this->getShippingGroups($order, $rates);

		$sort_shipping_by_price = (int)$config->get('sort_shipping_by_price', 0);

		JPluginHelper::importPlugin('hikashopshipping');
		$app = JFactory::getApplication();

		if(!empty($shipping_groups) && count($shipping_groups) > 1) {
			$order_backup = new stdClass();
			$order_backup->products = $order->products;
			$order_backup->package = isset($order->package) ? $order->package : null;
			$order_backup->weight = isset($order->weight) ? $order->weight : null;
			$order_backup->weight_unit = isset($order->weight_unit) ? $order->weight_unit : null;
			$order_backup->volume = isset($order->volume) ? $order->volume : null;
			$order_backup->volume_unit = isset($order->volume_unit) ? $order->volume_unit : null;
			$order_backup->total = $order->total;
			$order_backup->total_quantity = isset($order->total_quantity) ? $order->total_quantity : null;

			$cartClass = hikashop_get('class.cart');
			$currencyClass = hikashop_get('class.currency');
			$warehouse_order = 0;
			foreach($shipping_groups as $key => &$group) {
				$warehouse_order++;
				$order->products = $group->products;
				$group_usable_methods = array();
				$rates_copy = array();
				if(is_int($key))
					$key = ''.$key;

				$shipping_group_struct = array();
				if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $key, $keys)) {
					$shipping_group_struct = array_combine($keys[1], $keys[2]);
				}

				foreach($rates as $rate) {
					if(empty($rate->shipping_published))
						continue;

					$add_rate = true;
					if(!empty($rate->shipping_params->shipping_warehouse_filter)) {
						$add_rate = false;
						if($key === $rate->shipping_params->shipping_warehouse_filter) {
							$add_rate = true;
						} else {
							$keys = array();
							$tmp = array('' => $rate->shipping_params->shipping_warehouse_filter);
							if(is_string($rate->shipping_params->shipping_warehouse_filter) && preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $rate->shipping_params->shipping_warehouse_filter, $keys)) {
								$tmp = array_combine($keys[1], $keys[2]);
							}
							if(is_array($rate->shipping_params->shipping_warehouse_filter))
								$tmp = $rate->shipping_params->shipping_warehouse_filter;

							if($tmp[''] == $shipping_group_struct[''] || empty($tmp[''])) {
								$add_rate = true;
								foreach($tmp as $k => $v) {
									if($k != '' && (!isset($shipping_group_struct[$k]) || $shipping_group_struct[$k] != $v)) {
										$add_rate = false;
										break;
									}
								}
							}
						}
					}

					if($add_rate)
						$rates_copy[] = clone($rate);
				}

				$cartClass->calculateWeightAndVolume($order);
				if(((isset($order->package['weight']) && $order->package['weight']['value'] <= 0.0) || (isset($order->weight) && bccomp(sprintf('%F',$order->weight), 0, 5) <= 0)) && !$config->get('force_shipping')) {
					$group->no_weight = true;
					continue;
				}
				$currencyClass->calculateTotal($order->products, $order, $order->total->prices[0]->price_currency_id);

				$order->shipping_warehouse_id = $key;
				$order->cache->shipping_key = $shipping_key.'_'.$key;
				$local_errors = array();

				$app->triggerEvent('onShippingDisplay', array(&$order, &$rates_copy, &$group_usable_methods, &$local_errors));

				unset($order->shipping_warehouse_id);
				$order->cache->shipping_key = $shipping_key;

				if(empty($group_usable_methods)) {
					$name = (!empty($group->name) ? $group->name : $warehouse_order);
					$local_errors['no_rates'] = JText::sprintf('NO_SHIPPING_METHOD_FOUND_FOR_WAREHOUSE', $name);
				} else {
					foreach($group_usable_methods as $method) {
						if(isset($method->shipping_warehouse_id) && $method->shipping_warehouse_id != $key)
							$method = clone($method);
						if(!in_array($method->shipping_id, $group->shippings))
							$group->shippings[] = $method->shipping_id;
						$method->shipping_warehouse_id = $key;
						$usable_methods[] = $method;
					}
				}
				unset($method);

				if(!empty($local_errors)) {
					$errors = array_merge($errors, $local_errors);
					$group->errors = $local_errors;
				}

			}

			foreach($order_backup as $k => $v) {
				$order->$k = $v;
			}
		} else {
			$key = array_keys($shipping_groups);
			$key = reset($key);
			if(is_int($key) && !empty($key))
				$key = ''.$key;

			$keys = array();
			if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $key, $keys)) {
				if(count($keys[0]) > 1)
					$key = array_combine($keys[1], $keys[2]);
			}

			foreach($rates as $i => $rate) {
				$rem_rate = false;
				if(empty($rate->shipping_params->shipping_warehouse_filter))
					continue;

				$rem_rate = true;
				$keys = array();
				if(!is_array($key)) {
					if($key === $rate->shipping_params->shipping_warehouse_filter) {
						$rem_rate = false;
					} elseif(substr($rate->shipping_params->shipping_warehouse_filter, 0, 1) == '0') {
						$wf = substr($rate->shipping_params->shipping_warehouse_filter, 1);
						$rem_rate = (empty($wf) || substr($key, 1) != $wf);
					}
				} else if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $rate->shipping_params->shipping_warehouse_filter, $keys)) {
					$tmp = array_combine($keys[1], $keys[2]);
					if($tmp[''] == $key['']) {
						$rem_rate = false;
						foreach($tmp as $k => $v) {
							if(!isset($key[$k]) || $key[$k] != $v) {
								$rem_rate = true;
								break;
							}
						}
					}
				}

				if($rem_rate) {
					$rates[$i] = null;
					unset($rates[$i]);
				}
			}

			$warehouse_id = array_keys($shipping_groups);
			$warehouse_id = reset($warehouse_id);
			$order->shipping_warehouse_id = $warehouse_id;
			$app->triggerEvent('onShippingDisplay', array(&$order, &$rates, &$usable_methods, &$errors));
			unset($order->shipping_warehouse_id);

			if($sort_shipping_by_price)
				uasort($usable_methods, array($this, "sortShippingByPrice"));
			else
				uasort($usable_methods, array($this, "sortShipping"));

			$g = reset($shipping_groups);
			if(empty($g)) {
				$g = new stdClass();
				$g->shippings = array();
			}
			foreach($usable_methods as $method) {
				if(!in_array($method->shipping_id, $g->shippings))
					$g->shippings[] = $method->shipping_id;
				$method->shipping_warehouse_id = $key;
			}
		}

		if(empty($usable_methods)) {
			$errors['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
			$this->errors = $errors;
			return $usable_methods;
		} else {
			$i = 0;
			$shipping_ordering = array();
			foreach($usable_methods as $key => $shipping_method) {
				if($sort_shipping_by_price)
					$shipping_ordering[$key] = sprintf('%05.5d', $shipping_method->shipping_price).'_'.sprintf('%05d', $shipping_method->shipping_ordering).'_'.sprintf('%05d', $i);
				else
					$shipping_ordering[$key] = sprintf('%05d', $shipping_method->shipping_ordering).'_'.sprintf('%05d', $i);
				$i++;
			}
			array_multisort($shipping_ordering, SORT_ASC, $usable_methods);
		}
		$this->errors = $errors;

		if($use_cache)
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_cache.usable_methods', $order->cache->shipping);

		return $usable_methods;
	}

	protected function sortShipping($a, $b) {
		$sort_a = $a->shipping_ordering;
		if(strpos($sort_a, '_') === false) $sort_a = array($sort_a, 0);
		else $sort_a = explode('_', $sort_a, 2);

		$sort_b = $b->shipping_ordering;
		if(strpos($sort_b, '_') === false) $sort_b = array($sort_b, 0);
		else $sort_b = explode('_', $sort_b, 2);

		if((int)$sort_a[0] == (int)$sort_b[0] && (int)$sort_a[1] == (int)$sort_b[1]) {
			$index_a = explode('-', $a->shipping_id, 2);
			$index_b = explode('-', $b->shipping_id, 2);

			if((int)$index_a[0] == (int)$index_b[0])
				return ((int)$index_a[1] > (int)$index_b[1]) ? +1 : -1;
			return ((int)$index_a[0] > (int)$index_b[0]) ? +1 : -1;
		}

		if((int)$sort_a[0] == (int)$sort_b[0])
			return ((int)$sort_a[1] > (int)$sort_b[1]) ? +1 : -1;
		return ((int)$sort_a[0] > (int)$sort_b[0]) ? +1 : -1;
	}

	protected function sortShippingByPrice($a, $b) {
		$price_a = $a->shipping_price;
		$price_b = $b->shipping_price;
		if($price_a == $price_b)
			return $this->sortShipping($a, $b);
		return ($price_a > $price_b) ? +1 : -1;
	}

	function getShippingProductsData(&$order, $products = array()) {
		if(empty($order->shipping_prices)) {
			$order->shipping_prices = array();
		}

		if(!isset($order->shipping_prices[0])) {
			$order->shipping_prices[0] = new stdClass();
			$order->shipping_prices[0]->all_with_tax = 0;
			$order->shipping_prices[0]->all_without_tax = 0;
			if(isset($order->total->prices[0]->price_value_with_tax)) {
				$order->shipping_prices[0]->all_with_tax = $order->total->prices[0]->price_value_with_tax;
			}
			if(isset($order->full_total->prices[0]->price_value_without_shipping_with_tax)) {
				$order->shipping_prices[0]->all_with_tax = $order->full_total->prices[0]->price_value_without_shipping_with_tax;
			}
			if(isset($order->total->prices[0]->price_value)) {
				$order->shipping_prices[0]->all_without_tax = $order->total->prices[0]->price_value;
			}
			if(isset($order->full_total->prices[0]->price_value_without_shipping)) {
				$order->shipping_prices[0]->all_without_tax = $order->full_total->prices[0]->price_value_without_shipping;
			}

			$order->shipping_prices[0]->weight = @$order->weight;
			$order->shipping_prices[0]->volume = @$order->volume;
			$order->shipping_prices[0]->total_quantity = @$order->total_quantity;
			$order->shipping_prices[0]->total_quantity_real = @$order->total_quantity_real;
		}

		$key = 0;
		if(!empty($products)) {
			$product_keys = array_keys($products);
			sort($product_keys);
			$key = implode(',', $product_keys);

			if(!isset($order->shipping_prices[$key]))
				$order->shipping_prices[$key] = new stdClass();
		}

		$order->shipping_prices[$key]->real_with_tax = 0.0;
		$order->shipping_prices[$key]->real_without_tax = 0.0;
		$order->shipping_prices[$key]->products = array();
		$order->shipping_prices[$key]->volume = 0.0;
		$order->shipping_prices[$key]->weight = 0.0;
		$order->shipping_prices[$key]->total_quantity = 0;
		$order->shipping_prices[$key]->total_quantity_real = 0;

		if(empty($order->products))
			return $key;

		$all_products = new stdClass();
		$all_products->products = array();
		$real_products = new stdClass();
		$real_products->products = array();

		$volumeHelper = hikashop_get('helper.volume');
		$weightHelper = hikashop_get('helper.weight');

		$config = hikashop_config();
		$group_options = $config->get('group_options',0);
		$shipping_group_product_options = $config->get('shipping_group_product_options',0);

		foreach($order->products as $k => $row) {
			if(!empty($products) && !isset($products[$k]))
				continue;

			if($group_options == 1 && (int)$shipping_group_product_options != 0 && isset($row->cart_product_option_parent_id) && (int)$row->cart_product_option_parent_id > 0)
				continue;
			if(empty($order->shipping_prices[$key]->products[$row->product_id]))
				$order->shipping_prices[$key]->products[$row->product_id] = 0;
			$order->shipping_prices[$key]->products[$row->product_id] += @$row->cart_product_quantity;

			if(!empty($row->product_parent_id)) {
				if(!isset($order->shipping_prices[$key]->products[$row->product_parent_id]))
					$order->shipping_prices[$key]->products[$row->product_parent_id] = 0;
				$order->shipping_prices[$key]->products[$row->product_parent_id] += @$row->cart_product_quantity;
			}

			if(@$row->product_weight > 0)
				$real_products->products[] = $row;

			if($key !== 0)
				$all_products->products[] = $row;

			if($key === 0 || empty($row->cart_product_quantity))
				continue;


			if(!empty($row->cart_product_parent_id)) {
				if(!bccomp(sprintf('%F',$row->product_length), 0, 5) || !bccomp(sprintf('%F',$row->product_width), 0, 5) || !bccomp(sprintf('%F',$row->product_height), 0, 5)) {
					foreach($order->products as $l => $elem) {
						if($elem->cart_product_id == $row->cart_product_parent_id) {
							$row->product_length = $elem->product_length;
							$row->product_width = $elem->product_width;
							$row->product_height = $elem->product_height;
							$row->product_dimension_unit = $elem->product_dimension_unit;
							break;
						}
					}
				}
				if(!bccomp(sprintf('%F',$row->product_weight), 0, 5)) {
					foreach($order->products as $l => $elem) {
						if($elem->cart_product_id == $row->cart_product_parent_id) {
							$row->product_weight = $elem->product_weight;
							$row->product_weight_unit = $elem->product_weight_unit;
							break;
						}
					}
				}
			}

			if(bccomp(sprintf('%F',$row->product_length), 0, 5) && bccomp(sprintf('%F',$row->product_width), 0, 5) && bccomp(sprintf('%F',$row->product_height), 0, 5)) {
				if(!isset($row->product_total_volume)) {
					$row->product_volume = $row->product_length * $row->product_width * $row->product_height;
					$row->product_total_volume = $row->product_volume * $row->cart_product_quantity;
					$row->product_total_volume_orig = $row->product_total_volume;
					$row->product_dimension_unit_orig = $row->product_dimension_unit;
					$row->product_total_volume = $volumeHelper->convert($row->product_total_volume, $row->product_dimension_unit);
					$row->product_dimension_unit = $order->volume_unit;
				}

				$order->shipping_prices[$key]->volume += $row->product_total_volume;
			}

			if(bccomp(sprintf('%F',$row->product_weight), 0, 5)) {

				$order_weight_unit = isset($order->weight_unit) ? $order->weight_unit : @$order->weight['unit'];
				if($row->product_weight_unit != $order_weight_unit) {
					$row->product_weight_orig = $row->product_weight;
					$row->product_weight_unit_orig = $row->product_weight_unit;
					$row->product_weight = $weightHelper->convert($row->product_weight, $row->product_weight_unit);
					$row->product_weight_unit = $order_weight_unit;
				}

				$order->shipping_prices[$key]->weight += $row->product_weight * $row->cart_product_quantity;
			}

			$order->shipping_prices[$key]->total_quantity += $row->cart_product_quantity;

			if(bccomp(sprintf('%F',$row->product_weight), 0, 5)) {
				$order->shipping_prices[$key]->total_quantity_real += $row->cart_product_quantity;
			}
		}

		$currencyClass = hikashop_get('class.currency');
		$currencyClass->calculateTotal($real_products->products, $real_products->total, hikashop_getCurrency());

		$order->shipping_prices[$key]->real_with_tax = $real_products->total->prices[0]->price_value_with_tax;
		$order->shipping_prices[$key]->real_without_tax = $real_products->total->prices[0]->price_value;

		if($key !== 0) {
			$currencyClass->calculateTotal($all_products->products, $all_products->total, hikashop_getCurrency());
			$order->shipping_prices[$key]->all_with_tax =  $all_products->total->prices[0]->price_value_with_tax;
			$order->shipping_prices[$key]->all_without_tax = $all_products->total->prices[0]->price_value;
			if (!empty($order->coupon)) {
				if (@$order->coupon->discount_flat_amount != 0) {
					$order->shipping_prices[$key]->all_with_tax -= $order->coupon->discount_flat_amount;
					$order->shipping_prices[$key]->all_without_tax -= $order->coupon->discount_flat_amount;
				} elseif (@$order->coupon->discount_percent_amount != 0) {
					$order->shipping_prices[$key]->all_with_tax -= $order->shipping_prices[$key]->all_with_tax * ($order->coupon->discount_percent_amount / 100);
					$order->shipping_prices[$key]->all_without_tax -= $order->shipping_prices[$key]->all_without_tax * ($order->coupon->discount_percent_amount / 100);
				}
			}
		}

		unset($real_products->products);
		unset($real_products);

		return $key;
	}

	function &getShippingGroups(&$order, &$rates) {
		if(!empty($order->shipping_groups))
			return $order->shipping_groups;

		$shipping_groups = array();

		$warehouse = new stdClass();
		$warehouse->name = '';
		$warehouse->products = array();
		$warehouse->shippings = array();

		$shipping_groups[0] = $warehouse;

		if(!empty($order->products)) {
			$config = hikashop_config();
			$group_options = $config->get('group_options', 0);

			foreach($order->products as $i => &$product) {
				if(@$product->cart_product_quantity <= 0)
					continue;

				$product_parent = -1;
				if(!empty($product->cart_product_parent_id) || (!empty($product->cart_product_option_parent_id) && $group_options)) {
					foreach($order->products as $l => $elem){
						if(!empty($product->cart_product_parent_id) && $elem->cart_product_id == $product->cart_product_parent_id) {
							$product_parent = $l;
							if(empty($product->product_warehouse_id))
								$product->product_warehouse_id = $elem->product_warehouse_id;
							break;
						}
						if($group_options && !empty($product->cart_product_option_parent_id) && $elem->cart_product_id == $product->cart_product_option_parent_id) {
							$product->product_warehouse_id = $elem->product_warehouse_id;
						}
					}
				}

				if(!empty($product->product_warehouse_id)) {
					if(!isset($shipping_groups[$product->product_warehouse_id])) {
						$w = new stdClass();
						$w->name = '';
						$w->products = array();
						$w->shippings = array();

						$shipping_groups[$product->product_warehouse_id] = $w;
					}
					$shipping_groups[$product->product_warehouse_id]->products[$i] =& $product;
					if($product_parent >= 0)
						$shipping_groups[$product->product_warehouse_id]->products[$product_parent] =& $order->products[$product_parent];
				} else {
					$shipping_groups[0]->products[$i] =& $product;
					if($product_parent >= 0)
						$shipping_groups[0]->products[$product_parent] =& $order->products[$product_parent];
				}
			}
			unset($product);
		}

		if(empty($shipping_groups[0]->products)) {
			$shipping_groups[0] = null;
			unset($shipping_groups[0]);
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onShippingWarehouseFilter', array(&$shipping_groups, &$order, &$rates));

		foreach($shipping_groups as $group_id => $shipping_group) {
			if(empty($shipping_group->products)) {
				$shipping_groups[$group_id] = null;
				unset($shipping_groups[$group_id]);
			}
		}

		$order->shipping_groups =& $shipping_groups;
		return $shipping_groups;
	}

	public function checkCartMethods(&$cart, $force_selection = false) {
		$cart_shipping_ids = array();
		$shipping_modified = false;

		$config = hikashop_config();
		$auto_select_default = (int)$config->get('auto_select_default', 2);
		if($auto_select_default == 1 && count($cart->usable_methods->shipping) > 1)
			$auto_select_default = 0;
		if($force_selection)
			$auto_select_default = 2;

		if(empty($cart->cart_shipping_ids) && !$auto_select_default)
			return true;

		if(empty($cart->usable_methods->shipping)) {
			if(empty($cart->cart_shipping_ids))
				return true;
			$cart->cart_shipping_ids = array('0');
			return false;
		}

		foreach($cart->shipping_groups as $shipping_key => $shipping_group) {
			$found = false;

			if(!empty($cart->cart_shipping_ids)) {
				foreach($cart->cart_shipping_ids as $cart_shipping_id) {
					if(strpos($cart_shipping_id, '@') !== false) {
						list($shipping_id, $warehouse_id) = explode('@', $cart_shipping_id, 2);
						if(is_numeric($warehouse_id) && !is_string($shipping_key))
							$warehouse_id = (int)$warehouse_id;
					} else {
						$shipping_id = $cart_shipping_id;
						$warehouse_id = 0;
					}
					if($shipping_key !== $warehouse_id)
						continue;

					if(in_array($shipping_id, $shipping_group->shippings)) {
						$found = true;
						$cart_shipping_ids[] = $cart_shipping_id;
					}
				}
			}

			if($found == true)
				continue;

			$shipping_modified = true;
			if($auto_select_default) {
				$p = reset($shipping_group->shippings);
				$cart_shipping_ids[] = $p.'@'.$shipping_key;
			}
		}


		if($shipping_modified)
			$cart->cart_shipping_ids = $cart_shipping_ids;

		return !$shipping_modified;
	}

	function getAllShippingNames(&$order) {
		$names = array();
		if(empty($order->order_shipping_method) && empty($order->shippings)) {
			$names[] = JText::_('NONE');
		} else if(!empty($order->order_shipping_method)) {
			if(!is_numeric($order->order_shipping_id)){
				$shipping_name = $this->getShippingName($order->order_shipping_method, $order->order_shipping_id);
				$names[] = $shipping_name;
			}else{
				$shipping = $this->get($order->order_shipping_id);
				if(empty($shipping))
					$names[] = $this->getShippingName($order->order_shipping_method, $order->order_shipping_id);
				else
					$names[] = $shipping->shipping_name;
			}
		} else {
			$shipping_ids = explode(';', $order->order_shipping_id);
			foreach($shipping_ids as $key) {
				$shipping_data = '';
				list($k, $w) = explode('@', $key);
				$shipping_id = $k;

				if(isset($order->shippings[$shipping_id])) {
					$shipping = $order->shippings[$shipping_id];
					$shipping_data = $shipping->shipping_name;
				} else {
					foreach($order->products as $order_product) {
						if($order_product->order_product_shipping_id == $key) {
							if(!is_numeric($order_product->order_product_shipping_id)){
								$shipping_name = $this->getShippingName($order_product->order_product_shipping_method, $shipping_id);
								$shipping_data = $shipping_name;
							}else{
								$shipping_method_data = $this->get($shipping_id);
								$shipping_data = $shipping_method_data->shipping_name;
							}
							break;
						}
					}
					if(empty($shipping_data))
						$shipping_data = '[ ' . $key . ' ]';
				}
				if(isset($order->order_shipping_params->prices[$key])) {
					$price_params = $order->order_shipping_params->prices[$key];
					$config = hikashop_config();
					$currencyClass = hikashop_get('class.currency');
					if($config->get('price_with_tax')){
						$shipping_data .= ' (' . $currencyClass->format($price_params->price_with_tax, $order->order_currency_id) . ')';
					}else{
						$shipping_data .= ' (' . $currencyClass->format($price_params->price_with_tax-@$price_params->tax, $order->order_currency_id) . ')';
					}
				}
				$names[] = $shipping_data;
			}
		}
		return $names;
	}

	function getShippingName($shipping_method, $shipping_id) {
		$shipping_name = $shipping_method . ' ' . $shipping_id;
		if(strpos($shipping_id, '-') !== false) {
			$shipping_ids = explode('-', $shipping_id, 2);
			$shipping = $this->get($shipping_ids[0]);
			if(!empty($shipping->shipping_params) && is_string($shipping->shipping_params))
				$shipping->shipping_params = hikashop_unserialize($shipping->shipping_params);
			$shippingMethod = hikashop_import('hikashopshipping', $shipping_method);
			$methods = array();
			if(method_exists($shippingMethod, 'shippingMethods'))
				$methods = $shippingMethod->shippingMethods($shipping);
			unset($shippingMethod);

			if(isset($methods[$shipping_id])){
				$shipping_name = JText::sprintf('SHIPPING_METHOD_COMPLEX_NAME',$shipping->shipping_name, $methods[$shipping_id]);
			}else{
				$shipping_name = $shipping_id;
			}
			unset($methods);
			unset($shipping);
		}
		return $shipping_name;
	}

	function displayErrors() {
		if(empty($this->errors))
			return false;

		foreach($this->errors as $k => $errors) {
			if(is_array($errors)) {
				foreach($errors as $key => $value) {
					$this->_displayErrors($key,$value);
					return true;
				}
			} else {
				$this->_displayErrors($k,$errors);
				return true;
			}
		}
		return true;
	}

	function _displayErrors($key, $value, $display = true) {
		static $displayed = array();
		if($display && isset($displayed[$key.$value]))
			return;
		if($display)
			$displayed[$key.$value] = true;

		$number = $value;
		switch($key) {
			case 'min_price':
				$value = 'ORDER_TOTAL_TOO_LOW_FOR_SHIPPING_METHODS';
				break;
			case 'max_price':
				$value = 'ORDER_TOTAL_TOO_HIGH_FOR_SHIPPING_METHODS';
				break;
			case 'min_volume':
				$value = 'ITEMS_VOLUME_TOO_SMALL_FOR_SHIPPING_METHODS';
				break;
			case 'max_volume':
				$value = 'ITEMS_VOLUME_TOO_BIG_FOR_SHIPPING_METHODS';
				break;
			case 'min_weight':
				$value = 'ITEMS_WEIGHT_TOO_SMALL_FOR_SHIPPING_METHODS';
				break;
			case 'max_weight':
				$value = 'ITEMS_WEIGHT_TOO_BIG_FOR_SHIPPING_METHODS';
				break;
			case 'min_quantity':
				$value = 'ORDER_QUANTITY_TOO_SMALL_FOR_SHIPPING_METHODS';
				break;
			case 'max_quantity':
				$value = 'ORDER_QUANTITY_TOO_HIGH_FOR_SHIPPING_METHODS';
				break;
			case 'product_excluded':
				$value = 'X_PRODUCTS_ARE_NOT_SHIPPABLE_TO_YOU';
			default:
				if(strtoupper($key) == $key)
					$value = $key;
				break;
		}

		$transKey = strtoupper(str_replace(' ', '_', $value));
		$trans = JText::_($transKey);
		if(strpos($trans, '%s') !== false) {
			$trans = JText::sprintf($transKey, $number);
		}
		if($trans != $transKey) {
			$value = $trans;
		}

		if(!$display) {
			return $value;
		}

		static $translatedDisplayed = array();
		if(isset($translatedDisplayed[$value]))
			return;
		$translatedDisplayed[$value] = true;

		$app = JFactory::getApplication();
		$app->enqueueMessage($value);
	}

	function fillListingColumns(&$rows, &$listing_columns, &$view, $type = null) {
		$listing_columns['price'] = array(
			'name' => 'PRODUCT_PRICE',
			'col' => 'col_display_price'
		);
		$listing_columns['restriction'] = array(
			'name' => 'HIKA_RESTRICTIONS',
			'col' => 'col_display_restriction'
		);

		foreach($rows as &$row) {
			if(!empty($row->shipping_params) && is_string($row->shipping_params))
				$row->plugin_params = hikashop_unserialize($row->shipping_params);

			$prices = array();
			if(bccomp(sprintf('%F',$row->shipping_price), 0, 3))
				$prices[] = $view->currencyClass->displayPrices(array($row), 'shipping_price', 'shipping_currency_id');
			if(isset($row->plugin_params->shipping_percentage) && bccomp(sprintf('%F',$row->plugin_params->shipping_percentage), 0, 3))
				$prices[] = $row->plugin_params->shipping_percentage.'%';
			if(!empty($row->plugin_params->shipping_formula))
				$prices[] = $row->plugin_params->shipping_formula;
			$row->col_display_price = implode('<br/>', $prices);

			$restrictions = array();
			if(!empty($row->plugin_params->shipping_min_volume))
				$restrictions[] = JText::_('SHIPPING_MIN_VOLUME') . ': ' . $row->plugin_params->shipping_min_volume . $row->plugin_params->shipping_size_unit;
			if(!empty($row->plugin_params->shipping_max_volume))
				$restrictions[] = JText::_('SHIPPING_MAX_VOLUME') . ': ' . $row->plugin_params->shipping_max_volume . $row->plugin_params->shipping_size_unit;

			if(!empty($row->plugin_params->shipping_min_weight))
				$restrictions[] = JText::_('SHIPPING_MIN_WEIGHT') . ': ' . $row->plugin_params->shipping_min_weight . $row->plugin_params->shipping_weight_unit;
			if(!empty($row->plugin_params->shipping_max_weight))
				$restrictions[] = JText::_('SHIPPING_MAX_WEIGHT') . ': ' . $row->plugin_params->shipping_max_weight . $row->plugin_params->shipping_weight_unit;

			if(isset($row->plugin_params->shipping_min_price) && bccomp(sprintf('%F',$row->plugin_params->shipping_min_price), 0, 5)) {
				$row->shipping_min_price = $row->plugin_params->shipping_min_price;
				$restrictions[] = JText::_('SHIPPING_MIN_PRICE') . ': ' . $view->currencyClass->displayPrices(array($row), 'shipping_min_price', 'shipping_currency_id');
			}
			if(isset($row->plugin_params->shipping_max_price) && bccomp(sprintf('%F',$row->plugin_params->shipping_max_price), 0, 5)) {
				$row->shipping_max_price = $row->plugin_params->shipping_max_price;
				$restrictions[] = JText::_('SHIPPING_MAX_PRICE') . ': ' . $view->currencyClass->displayPrices(array($row), 'shipping_max_price', 'shipping_currency_id');
			}
			if(!empty($row->plugin_params->shipping_zip_prefix))
				$restrictions[] = JText::_('SHIPPING_PREFIX') . ': ' . $row->plugin_params->shipping_zip_prefix;
			if(!empty($row->plugin_params->shipping_min_zip))
				$restrictions[] = JText::_('SHIPPING_MIN_ZIP') . ': ' . $row->plugin_params->shipping_min_zip;
			if(!empty($row->plugin_params->shipping_max_zip))
				$restrictions[] = JText::_('SHIPPING_MAX_ZIP') . ': ' . $row->plugin_params->shipping_max_zip;
			if(!empty($row->plugin_params->shipping_zip_suffix))
				$restrictions[] = JText::_('SHIPPING_SUFFIX') . ': ' . $row->plugin_params->shipping_zip_suffix;
			if(!empty($row->shipping_zone_namekey)) {
				$zone = $view->zoneClass->get($row->shipping_zone_namekey);
				if(!empty($zone))
					$restrictions[] = JText::_('ZONE') . ': ' . $zone->zone_name_english;
				else
					$restrictions[] = JText::_('ZONE') . ': ' . 'INVALID';
			}
			if(!empty($row->shipping_access) && $row->shipping_access != 'all') {
				$joomlaAcl = hikashop_get('type.joomla_acl');
				$accesses = explode(',',$row->shipping_access);
				$list = array();
				$groups = $joomlaAcl->getList();
				foreach($accesses as $access){
					if(empty($access))
						continue;
					foreach($groups as $group){
						if($group->id == $access){
							$list[$access] = $group->text;
							break;
						}
					}
				}
				if(count($list))
					$restrictions[] = JText::_('ACCESS_LEVEL') . ': ' . implode(', ', $list);
			}

			if(!empty($row->shipping_currency)) {
				$null = null;
				$currency_ids = explode(',', $row->shipping_currency);
				$currencies = $view->currencyClass->getCurrencies($currency_ids, $null);
				if(count($currencies)) {
					$list = array();
					foreach($currencies as $c) {
						$list[] = $c->currency_code;
					}
					$restrictions[] = JText::_('CURRENCY') . ': ' . implode(', ', $list);
				}
			}

			if(!empty($row->plugin_params->shipping_warehouse_filter)) {
				$warehouse_ids = explode(',', $row->plugin_params->shipping_warehouse_filter);
				$list = array();
				foreach($warehouse_ids as $warehouse_id) {
					if(!empty($view->warehouses[$warehouse_id])){
						$list[] = $view->warehouses[$warehouse_id]->warehouse_name;
					} else {
						$list[] = JText::_('WAREHOUSE').' ' .$warehouse_id;
					}
				}
				$restrictions[] = JText::_('WAREHOUSE') . ': ' . implode(', ', $list);
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

		if(!isset($options['main_only']))
			$options['main_only'] = false;

		$query = 'SELECT * FROM ' . hikashop_table('shipping') . ' WHERE shipping_published = 1 ORDER BY shipping_ordering';
		$this->db->setQuery($query);
		$methods = $this->db->loadObjectList('shipping_id');
		foreach($methods as $method) {
			if($options['main_only']) {
				$ret[0][$method->shipping_id] = $method->shipping_name;
				continue;
			}
			$plugin = null;
			if($method->shipping_type != 'manual')
				$plugin = hikashop_import('hikashopshipping', $method->shipping_type);

			if(!empty($plugin) && method_exists($plugin, 'shippingMethods')) {
				if(is_string($method->shipping_params) && !empty($method->shipping_params))
					$method->shipping_params = hikashop_unserialize($method->shipping_params);
				$instances = $plugin->shippingMethods($method);
				if(!empty($instances)) {
					foreach($instances as $id => $instance) {
						$shipping_namekey = $method->shipping_type . '_' . $id;
						$ret[0][$shipping_namekey] = $method->shipping_name . ' - ' . $instance;
					}
				}
			} else {
				$shipping_namekey = $method->shipping_type . '_' . $method->shipping_id;
				$ret[0][$shipping_namekey] = $method->shipping_name;
			}
		}

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
