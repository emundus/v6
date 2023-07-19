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
class hikashopLimitClass extends hikashopClass {
	var $tables = array('limit');
	var $pkeys = array('limit_id');
	var $toggle = array('limit_published' => 'limit_id');

	public function get($id, $default = null) {
		$result = parent::get($id);
		$result->limit_status = explode(',', $result->limit_status);
		return $result;
	}

	public function saveForm() {
		$limit = new stdClass();
		$limit->limit_id = hikashop_getCID('limit_id');

		$formData = hikaInput::get()->get('data', array(), 'array');

		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		foreach($formData['limit'] as $column => $value) {
			hikashop_secureField($column);
			if($column == 'limit_category_id') {
				hikashop_toInteger($value);
				$limit->$column = $value;
				continue;
			}
			if(is_array($value)) {
				$value = implode(',', $value);
			}
			$limit->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
		}
		if(!empty($limit->limit_start)) {
			$limit->limit_start = hikashop_getTime($limit->limit_start);
		}
		if(!empty($limit->limit_end)) {
			$limit->limit_end = hikashop_getTime($limit->limit_end);
		}

		if(empty($limit->limit_id)) {
			$limit->limit_created = time();
		}
		$limit->limit_modified = time();

		$status = $this->save($limit);
		return $status;
	}

	public function save(&$element) {
		if(empty($element->limit_type) || $element->limit_type != 'weight' ) {
			$element->limit_unit = '';
		}
		if(!empty($element->limit_status) && is_array($element->limit_status)){
			$element->limit_status = implode(',',$element->limit_status);
		}

		if(!empty($element->limit_category_id) && is_array($element->limit_category_id)){
			$element->limit_category_id = ','.implode(',',$element->limit_category_id).',';
		} else {
			$element->limit_category_id = '';
		}
		$new = empty($element->currency_id);
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		if($new) {
			$app->triggerEvent('onBeforeLimitCreate', array( &$element, &$do ));
		} else {
			$app->triggerEvent('onBeforeLimitUpdate', array( &$element, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return $status;

		if($new) {
			$app->triggerEvent('onAfterLimitCreate', array( &$element ));
		} else {
			$app->triggerEvent('onAfterLimitUpdate', array( &$element ));
		}
		return $status;
	}
	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeLimitDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterLimitDelete', array(&$elements));
		}
		return $status;
	}


	public function getProducts($products, $all = true) {
		$ret = false;
		$product_ids = array();

		if($all)
			$product_ids[0] = 0;

		foreach($products as $product) {
			if(is_numeric($product)) {
				$i = (int)$product;
				$product_ids[ $i ] = $i;
				continue;
			}
			if(is_array($product) && isset($product['id'])) {
				$i = (int)$product['id'];
				$product_ids[ $i ] = $i;
				if(!empty($product['data']) && $product['data']->product_type == 'variant' && (int)$product['data']->product_parent_id > 0) {
					$i = (int)$product['data']->product_parent_id;
					$product_ids[ $i ] = $i;
				}
				continue;
			}
			if(is_object($product) && isset($product->product_id)) {
				$i = (int)$product->product_id;
				$product_ids[ $i ] = $i;
				continue;
			}
		}

		$query = 'SELECT category_id, product_id '.
			' FROM '.hikashop_table('product_category').
			' WHERE product_id IN (' . implode(',', $product_ids) . ')';
		$this->db->setQuery($query);
		$categories = $this->db->loadObjectList('product_id');

		$now = time();
		$filters = array(
			'hk_limit.limit_published = 1',
			'hk_limit.limit_start = 0 OR hk_limit.limit_start <= ' . $now,
			'hk_limit.limit_end = 0 OR hk_limit.limit_end >= ' . $now,
			'hk_limit.limit_product_id IN (' . implode(',', $product_ids) . ')',
			'hk_limit.limit_category_id = 0',
		);

		if(!empty($categories)) {
			$conditions = array('hk_limit.limit_category_id = 0');
			foreach($categories as $id => $c) {
				$conditions[] = 'hk_limit.limit_category_id LIKE \'%,'.$id.',%\'';
			}
			$filters[] = '('.implode(' OR ', $conditions).')';
		} else {
			$filters[] = 'hk_limit.limit_category_id = 0';
		}
		hikashop_addACLFilters($filters, 'limit_access', 'hk_limit');

		$query = 'SELECT DISTINCT hk_limit.* '.
			' FROM '.hikashop_table('limit').' AS hk_limit '.
			' WHERE (' . implode(') AND (', $filters) . ')';
		$this->db->setQuery($query);
		$limiters = $this->db->loadObjectList('limit_id');

		if(empty($limiters))
			return $ret;

		$ret = $limiters;
		return $ret;
	}

	public function getCart(&$cart, $products = null) {
		$ret = false;
		$p = array();
		if(!empty($cart->cart_products))
			$p = $cart->cart_products;
		if(!empty($products))
			$p = array_merge($p, $products);

		if(!empty($p))
			$ret = $this->getProducts($p);
		return $ret;
	}


	public function checkLimits($limiters, &$cart, $products = null) {
		$ret = true;
		if(empty($limiters))
			return true;

		if($cart->cart_type == 'wishlist')
			return true;

		$cartClass = hikashop_get('class.cart');

		$d = getdate();
		$periodicity = array(
			'forever' => 0,
			'yearly' => 1,
			'quarterly' => 2,
			'monthly' => 3,
			'weekly' => 4,
			'daily' => 5,
			'cart' => 6
		);
		$baseDates = array(
			0 => 0,
			1 => mktime(0,0,0,1,1,$d['year']),
			2 => mktime(0,0,0,$d['mon']-(($d['mon']-1)%4),1,$d['year']),
			3 => mktime(0,0,0,$d['mon'],1,$d['year']),
			4 => mktime(0,0,0,$d['mon'],$d['mday']-$d['wday'],$d['year']),
			5 => mktime(0,0,0,$d['mon'],$d['mday'],$d['year']),
			6 => -1
		);

		$limiterTypes = array(
			'price' => false, 'quantity' => false, 'weight' => false,
			'filter_category' => false
		);
		$limit_rules = array();

		if(empty($this->weightHelper))
			$this->weightHelper = hikashop_get('helper.weight');
		$main_unit = $this->weightHelper->getSymbol();

		foreach($limiters as $limiter) {
			$limiterTypes[ $limiter->limit_type ] = true;

			if(!empty($limiter->limit_category_id)) {
				$limiter->limit_category_id = explode(',', $limiter->limit_category_id);
				$limiterTypes['filter_category'] = true;
			}

			if($limiter->limit_type == 'quantity') {
				$limiter->limit_value = (int)$limiter->limit_value;
			} else {
				$limiter->limit_value = (float)hikashop_toFloat($limiter->limit_value);
			}

			if($limiter->limit_type == 'weight' && $limiter->limit_unit != $main_unit) {
				$limiter->limit_value_orign = $limiter->limit_value;
				$limiter->limit_value = $this->weightHelper->convert($limiter->limit_value, $limiter->limit_unit, $main_unit);
			}

			if(!isset($periodicity[$limiter->limit_periodicity]))
				continue;

			$p = $periodicity[$limiter->limit_periodicity];
			$dl = $baseDates[$p];
			if($dl < 0)
				continue;

			if(!isset($limit_rules[$dl])) {
				$limit_rules[$dl] = array(
					'product' => array(),
					'category' => array(),
					'status' => array(),
					'currency' => array()
				);
			}
			$limit_rules[$dl]['status'] = array_unique(array_merge($limit_rules[$dl]['status'], explode(',', $limiter->limit_status)));
			if($limiter->limit_type == 'price') {
				$limit_rules[$dl]['currency'][(int)$limiter->limit_currency_id ] = (int)$limiter->limit_currency_id;
			}
			if($limiter->limit_product_id > 0 && $limit_rules[$dl]['product'] !== false) {
				$limit_rules[$dl]['product'][(int)$limiter->limit_product_id] = (int)$limiter->limit_product_id;
			}
			if(!empty($limiter->limit_category_id)) {
				foreach($limiter->limit_category_id as $id) {
					if($id > 0 && $limit_rules[$dl]['category'] !== false) {
						$limit_rules[$dl]['category'][(int)$id] = (int)$id;
					}
				}
			}
			if(empty($limiter->limit_category_id) && $limiter->limit_product_id == 0) {
				$limit_rules[$dl]['product'] = false;
				$limit_rules[$dl]['category'] = false;
			}
		}

		$data = array(
			'products' => array(),
			'ids' => null
		);
		if(!empty($cart->user_id) && !empty($limit_rules)) {
			$data = $this->getUserOrderProducts($cart->user_id, $limit_rules);
		}

		if($limiterTypes['weight'])
			$this->loadProductWeight($data, $cart->cart_products, $products);
		if($limiterTypes['price'])
			$this->loadProductPrice($data, $cart->cart_products, $products);
		if($limiterTypes['filter_category'])
			$this->loadProductCategories($data, $cart->cart_products, $products);

		if(!empty($data['products']))
			$this->loadProductQuantities($data['products']);

		$limiter_product_updates = array();
		if(!empty($products)) {
			foreach($products as $p) {
				if(empty($p['pid']))
					continue;
				$limiter_product_updates[ $p['pid'] ] = $p['pid'];
			}
		}

		$ids = array();
		foreach($cart->cart_products as $k => &$cart_product) {
			if(!empty($cart_product->product_parent_id))
				$ids[] = $cart_product->product_parent_id;
			if(!empty($cart_product->product_id))
				$ids[] = $cart_product->product_id;
		}
		if(!empty($ids)) {
			$query = 'SELECT category_id, product_id '.
				' FROM '.hikashop_table('product_category').
				' WHERE product_id IN (' . implode(',', $ids) . ')';
			$this->db->setQuery($query);
			$categories = $this->db->loadObjectList('product_id');
		} else {
			$categories = array();
		}

		foreach($limiters as $limiter) {
			$baseDate = $baseDates[ $periodicity[ $limiter->limit_periodicity ] ];
			$parsedIds = array();
			$value = 0;

			foreach($data['products'] as $entry) {
				if( $entry->order_created < $baseDate || (!empty($limiter->limit_status) && strpos(','.$limiter->limit_status.',', ','.$entry->order_status.',') === false) )
					continue;

				$pid = (int)$entry->order_product_id;
				if(isset($parsedIds[$pid]))
					continue;

				if($limiter->limit_product_id > 0 && $limiter->limit_product_id != $entry->product_id && $limiter->limit_product_id != $entry->product_main_id)
					continue;
				if(!empty($limiter->limit_category_id) && !in_array($entry->category_id, $limiter->limit_category_id))
					continue;

				$value += $this->getValue($limiter, $entry);

				$parsedIds[$pid] = true;
			}
			unset($parsedIds);

			foreach($cart->cart_products as $k => &$cart_product) {
				if(!is_numeric($k) && substr($k, 0, 1) == 'p')
					continue;

				if((int)$limiter->limit_product_id > 0 && (int)$limiter->limit_product_id != $cart_product->product_id && (int)$limiter->limit_product_id != $cart_product->product_parent_id)
					continue;

				if(!empty($limiter->limit_category_id)){
					$product_categories = array();
					foreach($categories as $c) {
						if($c->product_id == $cart_product->product_id || $c->product_id == $cart_product->product_parent_id)
							$product_categories[] = $c->category_id;
					}
					$intersect = array_intersect($product_categories, $limiter->limit_category_id);
					if(count($intersect)<1)
						continue;
				}

				if(empty($cart_product->cart_product_quantity))
					continue;

				if(!empty($limiter_product_updates[$k]))
					continue;

				$qty = $this->checkLimiterForProduct($limiter, $value, $cart_product, $data, $cart_product->cart_product_quantity);

				if($cart_product->cart_product_quantity == $qty)
					continue;

				$cart_product->cart_product_quantity = $qty;

				if(isset($cart->products[$k]))
					$cart->products[$k]->cart_product_quantity = $qty;

				$ret = false;

				$product_name = '';
				if(isset($cart->products) && isset($cart->products[$k])){
					$product_name = $cart->products[$k]->product_name;
				}
				$cartClass->addMessage($cart, array(
					'msg' => JText::sprintf( (($qty == 0) ? 'LIMIT_REACHED_REMOVED' : 'LIMIT_REACHED'), $product_name),
					'product_id' => $cart_product->product_id,
					'type' => 'warning'
				));
			}
			unset($cart_product);

			if(!empty($products)) {
				foreach($products as &$p) {
					$i = (isset($p['id']) ? $p['id'] : (int)$p['data']->product_id);
					$parent_id = (isset($p['data']) ? (int)$p['data']->product_parent_id : 0);

					if((int)$limiter->limit_product_id > 0 && (int)$limiter->limit_product_id != $i && (int)$limiter->limit_product_id != $parent_id)
						continue;

					if(!empty($limiter->limit_category_id)){
						$product_categories = array();
						foreach($categories as $c) {
							if($c->product_id == $i || $c->product_id == $parent_id)
								$product_categories[] = $c->category_id;
						}
						$intersect = array_intersect($product_categories, $limiter->limit_category_id);
						if(count($intersect)<1)
							continue;
					}

					$qty = $this->checkLimiterForProduct($limiter, $value, $p['data'], $data, $p['qty']);
					if($p['qty'] == (int)$qty)
						continue;

					$p['qty'] = (int)$qty;

					$ret = false;

					$message = ($p['qty'] == 0) ? 'LIMIT_REACHED_REMOVED' : 'LIMIT_REACHED';
					if(empty($p['pid'])) {
						$message = 'LIMIT_REACHED_PRODUCT_NOT_ADDED_TO_CART';
					}

					$cartClass->addMessage($cart, array(
						'msg' => JText::sprintf( $message, $p['data']->product_name, $p['qty']),
						'product_id' => $i,
						'type' => 'warning'
					));
				}
				unset($p);
			}
		}
		unset($data);
		return $ret;
	}

	protected function getValue($limiter, $entry) {
		if(empty($limiter->limit_type))
			return 0;

		if(empty($entry) || empty($entry->order_product_quantity))
			return 0;

		if($limiter->limit_type == 'quantity') {
			return (int)$entry->order_product_quantity;
		}

		if($limiter->limit_type == 'price') {
			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$app->triggerEvent('onBeforeCalculateProductPriceForQuantityInOrder', array( &$entry ));

			if(function_exists('hikashop_product_price_for_quantity_in_order')) {
				hikashop_product_price_for_quantity_in_order($entry);
			} else {
				$entry->order_product_total_price_no_vat = (float)$entry->order_product_price * (int)$entry->order_product_quantity;
				$entry->order_product_total_price = ((float)$entry->order_product_price + (float)$entry->order_product_tax) * (int)$entry->order_product_quantity;
			}

			$app->triggerEvent('onAfterCalculateProductPriceForQuantityInOrder', array( &$entry ));

			return (float)$entry->order_product_total_price;
		}

		if($limiter->limit_type == 'weight') {
			return (float)$entry->product_weight * (int)$entry->order_product_quantity;
		}

		return 0;
	}

	protected function checkLimiterForProduct(&$limiter, &$limit_value, &$product, &$data, $qty) {
		$value = 0;

		if($limiter->limit_type == 'quantity') {
			$value = 1;
		} elseif($limiter->limit_type == 'price') {
			$value = 0;
			$key = (int)$product->product_id . '-' . $qty;
			if(isset($data['prices'][$key]))
				$value = (float)hikashop_toFloat($data['prices'][$key]);
		} elseif($limiter->limit_type == 'weight') {
			$value = 0;
			if(isset($product->product_weight))
				$value = (float)hikashop_toFloat($product->product_weight);
			elseif(isset($data['weight'][(int)$product->product_id]))
				$value = (float)hikashop_toFloat($data['weight'][(int)$product->product_id]);
		}

		if(empty($value))
			return $qty;

		if($limit_value >= $limiter->limit_value)
			return 0;

		if($limit_value + ($value * $qty) <= $limiter->limit_value) {
			$limit_value += ($value * $qty);
			return $qty;
		}

		return floor( ($limiter->limit_value - $limit_value) / $value );
	}

	protected function loadProductWeight(&$data, $cart_products, $products = null) {
		$ids = array();
		if(!empty($cart_products)) {
			foreach($cart_products as $cart_product) {
				$ids[ (int)$cart_product->product_id ] = (int)$cart_product->product_id;
			}
		}

		if(empty($data['ids']) && empty($ids))
			return;

		if(!empty($data['ids']) && is_array($data['ids']))
			$ids = array_merge($ids, $data['ids']);
		hikashop_toInteger($ids);

		if(empty($this->weightHelper))
			$this->weightHelper = hikashop_get('helper.weight');
		$main_unit = $this->weightHelper->getSymbol();

		$query = 'SELECT product_id, product_weight, product_weight_unit FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',', $ids).')';
		$this->db->setQuery($query);
		$weights = $this->db->loadObjectList('product_id');

		if(!empty($data['products'])) {
			foreach($data['products'] as &$entry) {
				$entry->product_weight = 0.0;

				if(isset($weights[ $entry->product_id ])) {
					$w =& $weights[ $entry->product_id ];
				} elseif(isset($weights[ $entry->product_main_id ])) {
					$w =& $weights[ $entry->product_main_id ];
				} else {
					continue;
				}

				$entry->product_weight = (float)$w->product_weight;
				if($w->product_weight_unit != $main_unit) {
					$entry->product_weight = $this->weightHelper->convert($entry->product_weight, $w->product_weight_unit, $main_unit);
				}

				unset($w);
			}
			unset($entry);
		}

		if(!empty($cart_products)) {
			$data['weight'] = array();
			foreach($cart_products as $cart_product) {
				$pid = (int)$cart_product->product_id;
				if(isset($data['weight'][ $pid ]) || !isset($weights[$pid]))
					continue;
				if($weights[$pid]->product_weight_unit == $main_unit) {
					$data['weight'][ $pid ] = (float)hikashop_toFloat($weights[$pid]->product_weight);
				} else {
					$data['weight'][ $pid ] = $this->weightHelper->convert($weights[$pid]->product_weight, $weights[$pid]->product_weight_unit, $main_unit);
				}
			}
		}
	}

	protected function loadProductQuantities(&$products) {
		if(empty($products))
			return;

		$qty = array();
		foreach($products as $entry) {
			if(empty($entry->cart_product_quantity) && empty($entry->order_product_quantity))
				continue;

			$value = (empty($entry->cart_product_quantity) ? (int)$entry->order_product_quantity : (int)$entry->cart_product_quantity);

			if(empty($qty[$entry->product_id]))
				$qty[$entry->product_id] = 0;
			$qty[$entry->product_id] += $value;

			if(empty($entry->product_parent_id))
				continue;

			if(empty($qty[$entry->product_parent_id]))
				$qty[$entry->product_parent_id] = 0;
			$qty[$entry->product_parent_id] += $value;
		}

		foreach($products as &$entry) {
			$entry->cart_product_total_quantity = $qty[$entry->product_id];
			if(empty($entry->product_parent_id))
				$entry->cart_product_total_variants_quantity = $entry->cart_product_total_quantity;
			else
				$entry->cart_product_total_variants_quantity = $qty[$entry->product_parent_id];
		}
		unset($entry);
	}

	protected function loadProductPrice(&$data, $cart_products, $products = null) {
		if(empty($data['prices']))
			$data['prices'] = array();

		$ids = array();
		$tmp = array();

		foreach($cart_products as $cart_product) {
			$key = (int)$cart_product->product_id . '-' . (int)$cart_product->cart_product_quantity;
			if(isset($tmp[$key]))
				continue;

			$p = new stdClass();
			$p->product_id = (int)$cart_product->product_id;
			$p->cart_product_quantity = (int)$cart_product->cart_product_quantity;
			$p->product_tax_id = (int)@$cart_product->product_tax_id;
			$p->product_parent_id = (int)@$cart_product->product_parent_id;

			$tmp[$key] = $p;

			$ids[(int)$p->product_id] = (int)$p->product_id;

			if(!empty($cart_product->product_parent_id)) {
				$ids[ (int)$cart_product->product_parent_id ] = (int)$cart_product->product_parent_id;

				$key = (int)$cart_product->product_parent_id . '-' . (int)$cart_product->cart_product_quantity;
				$p = clone($p);
				$p->product_id = (int)$cart_product->product_parent_id;
				$p->product_parent_id = 0;
				$tmp[$key] = $p;
			}
		}

		if(!empty($ids)) {
			$query = 'SELECT product_id, product_tax_id FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',', $ids).')';
			$this->db->setQuery($query);
			$p_taxes = $this->db->loadObjectList('product_id');
			foreach($tmp as $k => &$v) {
				if(isset($p_taxes[$k]))
					$v->product_tax_id = (int)$p_taxes[$k]->product_tax_id;
				if(empty($v->product_tax_id) && !empty($v->product_parent_id) && isset($p_taxes[$v->product_parent_id]))
					$v->product_tax_id = (int)$p_taxes[$v->product_parent_id]->product_tax_id;
			}
			unset($v);
			unset($p_taxes);
		}

		if(!empty($products)) {
			foreach($products as $product) {
				$key = (int)$product['id'] . '-' . (int)$product['qty'];
				if(isset($tmp[$key]))
					continue;

				$p = new stdClass();
				$p->product_id = (int)$product['id'];
				$p->cart_product_quantity = (int)$product['qty'];
				$p->product_tax_id = (int)$product['data']->product_tax_id;

				$tmp[$key] = $p;

				$ids[(int)$p->product_id] = (int)$p->product_id;

				if(!empty($product['data']) && !empty($product['data']->product_parent_id)) {
					$ids[ (int)$product['data']->product_parent_id ] = (int)$product['data']->product_parent_id;

					$key = (int)(int)$product['data']->product_parent_id . '-' . (int)$product['qty'];
					$p = clone($p);
					$p->product_id = (int)$product['data']->product_parent_id;
					$p->product_parent_id = 0;
					$tmp[$key] = $p;
				}
			}
		}
		unset($ids[0]);

		if(empty($ids))
			return;

		$config = hikashop_config();
		$currencyClass = hikashop_get('class.currency');
		$currency_id = hikashop_getCurrency();
		$main_currency = (int)$config->get('main_currency', 1);
		if(!in_array($currency_id, $currencyClass->publishedCurrencies()))
			$currency_id = $main_currency;

		$tax_zone_id = ($config->get('tax_zone_type','shipping') == 'billing') ? hikashop_getZone('billing') : hikashop_getZone('shipping');
		$discount_before_tax = (int)$config->get('discount_before_tax', 0);

		$currencyClass->getPrices($tmp, $ids, $currency_id, $main_currency, $tax_zone_id, $discount_before_tax);
		if(empty($tmp))
			return;

		foreach($tmp as $k => $t) {
			$data['prices'][$k] = isset($t->prices[0]->price_value_with_tax) ?$t->prices[0]->price_value_with_tax : $t->prices[0]->price_value;
		}
		unset($tmp);
	}

	protected function loadProductCategories(&$data, $cart_products, $products = null) {
		$pids = array();

		if(!empty($cart_products)) {
			foreach($cart_products as $cart_product) {
				$pids[ (int)$cart_product->product_id ] = (int)$cart_product->product_id;
				if(!empty($cart_product->product_parent_id))
					$pids[ (int)$cart_product->product_parent_id ] = (int)$cart_product->product_parent_id;
			}
		}
		if(!empty($products)) {
			foreach($products as $product) {
				$i = (isset($product['id']) ? $product['id'] : (int)$product['data']->product_id);
				$pids[ (int)$i ] = (int)$i;
				if(!empty($product['data']) && !empty($product['data']->product_parent_id))
					$pids[ (int)$product['data']->product_parent_id ] = (int)$product['data']->product_parent_id;
			}
		}
		if(empty($pids))
			return;

		$query = 'SELECT product_id, category_id FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',', $pids).')';
		$this->db->setQuery($query);
		$data['categories'] = $this->db->loadObjectList();
	}

	protected function getUserOrderProducts($user_id, $rules = array()) {

		$select = array(
			'order_product_id' => 'op.order_product_id',
			'product_id' => 'op.product_id',
			'order_product_quantity' => 'op.order_product_quantity',
			'order_product_price' => 'op.order_product_price',
			'order_product_tax' => 'op.order_product_tax',
			'order_currency_id' => 'o.order_currency_id',
			'order_created' => 'o.order_created',
			'order_status' => 'o.order_status',
			'product_main_id' => 'p.product_id as product_main_id',
			'category_id' => 'pc.category_id'
		);
		$joins = array(
			'order' => 'INNER JOIN '.hikashop_table('order').' AS o ON op.order_id = o.order_id',
			'product' => 'INNER JOIN '.hikashop_table('product').' AS p ON op.product_id = p.product_id',
			'product_category' => 'INNER JOIN '.hikashop_table('product_category').' AS pc ON (p.product_id = pc.product_id OR p.product_parent_id = pc.product_id)'
		);
		$filters = array(
			'order_type' => 'o.order_type = '. $this->db->Quote('sale'),
			'product_valid' => 'op.product_id > 0',
			'order_user_id' => 'o.order_user_id = ' . $user_id
		);

		$f = reset($rules);
		$c = array('status' => @$f['status'], 'currency' => @$f['currency']);
		foreach($rules as $date => $rule) {
			if($rule['status'] != $c['status'])
				$c['status'] = false;
			if($rule['currency'] != $c['currency'])
				$c['currency'] = false;
		}
		if(!empty($c['status'])) {
			$s = hikashop_db_quote($c['status']);
			$filters['order_status'] = 'o.order_status IN ('.implode(',', $s).')';
		}
		if(!empty($c['currency'])) {
			hikashop_toInteger($c['currency']);
			if(count($c['currency']) == 1)
				$filters['currency'] = 'o.order_currency_id = '.(int)reset($c['currency']);
			else
				$filters['currency'] = 'o.order_currency_id IN ('.implode(',', $c['currency']).')';
		}

		$filters['rules'] = array();
		foreach($rules as $date => $rule) {
			$sql = array(
				'o.order_created >= ' . (int)$date
			);
			if(!empty($rule['status']) && empty($filters['order_status'])) {
				$s = hikashop_db_quote($c['status']);
				$sql[] = 'o.order_status IN ('.implode(',', $s).')';
			}
			if(!empty($rule['currency']) && empty($filters['currency'])) {
				$sql[] = 'o.order_currency_id = '.(int)$c['currency'];
			}
			if(!empty($rule['product'])) {
				hikashop_toInteger($rule['product']);
				$sql[] = '(op.product_id IN ('.implode(',', $rule['product']).') OR p.product_parent_id IN ('.implode(',', $rule['product']).'))';
			}
			if(!empty($rule['category'])) {
				hikashop_toInteger($rule['category']);
				$sql[] = 'pc.category_id IN ('.implode(',', $rule['category']).')';
			}
			$filters['rules'][] = implode(' AND ', $sql);
		}
		if(!empty($filters['rules'])) {
			$filters['rules'] = '(' . implode(') OR (', $filters['rules']) . ')';
		} else {
			unset($filters['rules']);
		}

		$query = 'SELECT '.implode(', ', $select).' FROM '.hikashop_table('order_product').' AS op '.implode(' ', $joins).' WHERE ('.implode(') AND (', $filters).')';
		$this->db->setQuery($query);
		$products = $this->db->loadObjectList();

		unset($query); unset($select); unset($joins); unset($filters);

		$ids = array();
		foreach($products as $p) {
			if(!empty($p->product_main_id))
				$ids[(int)$p->product_main_id] = (int)$p->product_main_id;
		}

		return array(
			'products' => $products,
			'ids' => $ids
		);
	}

}
