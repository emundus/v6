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
class hikashopDiscountClass extends hikashopClass {
	var $tables = array('discount');
	var $pkeys = array('discount_id');
	var $namekeys = array('');
	var $toggle = array('discount_published'=>'discount_id');

	public function saveForm() {
		$discount = new stdClass();
		$discount->discount_id = hikashop_getCID('discount_id');

		$app = JFactory::getApplication();
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		$nameboxes = array('discount_product_id','discount_category_id','discount_zone_id','discount_user_id');

		$formData = hikaInput::get()->get('data', array(), 'array');

		foreach($formData['discount'] as $column => $value) {
			hikashop_secureField($column);
			if(!in_array($column,$nameboxes)) {
				$discount->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
				continue;
			}

			if($column == 'discount_zone_id') {
				$discount->$column = array();
				foreach($value as $i => $v) {
					$discount->{$column}[] = $safeHtmlFilter->clean(strip_tags($v), 'string');
				}
			} else {
				hikashop_toInteger($value);
			}
			$discount->$column = $value;
		}

		foreach($nameboxes as $namebox) {
			if(!isset($discount->$namebox)) {
				$discount->$namebox = '';
			}
		}

		if(!empty($discount->discount_category_id) && !empty($discount->discount_product_id)) {
			$discount->discount_category_id = '';
			$discount->discount_category_childs = 0;
			$app->enqueueMessage('If you set both categories and products in a discount/coupon, only the products will be taken into account.', 'error');
		}

		if(!empty($discount->discount_start)) {
			$discount->discount_start = hikashop_getTime($discount->discount_start);
		}
		if(!empty($discount->discount_end)) {
			$discount->discount_end = hikashop_getTime($discount->discount_end);
		}

		if(!empty($discount->discount_id) && !empty($discount->discount_code)) {
			$query = 'SELECT discount_id FROM '.hikashop_table('discount').' WHERE discount_code  = '.$this->database->Quote($discount->discount_code);
			$this->database->setQuery($query, 0, 1);
			$res = (int)$this->database->loadResult();

			if(!empty($res) && $res != (int)$discount->discount_id) {
				$app->enqueueMessage(JText::_('DISCOUNT_CODE_ALREADY_USED'), 'error');
				hikaInput::get()->set('fail', $discount);
				return false;
			}
		}

		$status = $this->save($discount);
		if(!$status) {
			hikaInput::get()->set('fail', $discount);
			$app->enqueueMessage(JText::_('DISCOUNT_CODE_ALREADY_USED'));
		}
		return $status;
	}

	public function save(&$discount) {
		if(empty($discount->discount_id)) {
			if(empty($discount->discount_type) || ($discount->discount_type=='coupon' && empty($discount->discount_code))){
				return false;
			}
			$new = true;
		}

		if(!empty($discount->discount_code))
			$discount->discount_code = trim($discount->discount_code);

		$lists = array('discount_product_id', 'discount_category_id', 'discount_user_id');
		foreach($lists as $key) {
			if(empty($discount->$key) || !is_array($discount->$key))
				continue;
			hikashop_toInteger($discount->$key);
			$discount->$key = ',' . implode(',', $discount->$key) . ',';
		}
		if(!empty($discount->discount_zone_id) && is_array($discount->discount_zone_id)) {
			$discount->discount_zone_id = ','.implode(',',$discount->discount_zone_id).',';
		}

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		if(!empty($new)) {
			$app->triggerEvent('onBeforeDiscountCreate', array( &$discount, &$do ));
		} else {
			$app->triggerEvent('onBeforeDiscountUpdate', array( &$discount, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($discount);
		if(!$status)
			return $status;

		if(!empty($new)) {
			$app->triggerEvent('onAfterDiscountCreate', array( &$discount ));
		} else {
			$app->triggerEvent('onAfterDiscountUpdate', array( &$discount ));
		}
		return $status;
	}

	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeDiscountDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterDiscountDelete', array(&$elements));
		}
		return $status;
	}

	public function load($coupon) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$item = $app->triggerEvent('onBeforeCouponLoad', array(&$coupon, &$do));
		if(!$do)
			return current($item);

		if(is_array($coupon))
			$coupon = reset($coupon);
		if(!is_string($coupon))
			return false;

		$coupon = trim($coupon);

		static $coupons = array();
		if(!isset($coupons[$coupon])) {
			$filters = array(
				'discount_code = '.$this->database->Quote($coupon),
				'discount_type = \'coupon\'',
				'discount_published = 1'
			);
			$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE ('.implode(') AND (', $filters).')';
			$this->database->setQuery($query);
			$coupons[$coupon] = $this->database->loadObject();
		}
		return $coupons[$coupon];
	}

	public function loadAndCheck($coupon_code, &$total, $zones, &$products, $display_error = true) {
		$coupon = $this->load($coupon_code);
		return $this->check($coupon, $total, $zones, $products, $display_error);
	}

	public function check(&$coupon, &$total, $zones, &$products, $display_error = true) {
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$error_message = '';
		$do = true;

		if(isset($coupon->discount_value)) {
			$coupon = $this->get($coupon->discount_id);
		}

		$app->triggerEvent('onBeforeCouponCheck', array( &$coupon, &$total, &$zones, &$products, &$display_error, &$error_message, &$do ));
		if($do) {
			$error_message = $this->couponChecks($coupon, $total, $products, $zones, $error_message);
		}

		$app->triggerEvent('onAfterCouponCheck', array( &$coupon, &$total, &$zones, &$products, &$display_error, &$error_message, &$do ));

		if(!empty($error_message)) {
			$cartClass = hikashop_get('class.cart');
			$cartClass->update('', 0, 0, 'coupon');
			if($display_error) {
				hikaInput::get()->set('coupon_error_message', $error_message);
			}
			return null;
		}
		hikaInput::get()->set('coupon_error_message','');

		if(!$do)
			return $coupon;

		$currencyClass = hikashop_get('class.currency');
		$currencyClass->convertCoupon($coupon, $total->prices[0]->price_currency_id);

		if(!isset($coupon->total))
			$coupon->total = new stdClass();
		$coupon->total->prices = array(hikashop_copy(reset($total->prices)));


		$id = array();
		$this->recalculateDiscountValue($coupon, $products, $id);

		switch( @$coupon->discount_coupon_nodoubling ) {
			case 1:
			case 2:
				$coupon = $this->addCoupon($coupon, $products, $currencyClass, $coupon->discount_coupon_nodoubling);
				break;
			default:
				$currencyClass->addCoupon($coupon->total,$coupon, $products, $id);
				break;
		}

		return $coupon;

	}

	protected function couponChecks(&$coupon, &$total, &$products, $zones, $error_message) {
		$currencyClass = hikashop_get('class.currency');
		if(empty($coupon))
			return JText::_('COUPON_NOT_VALID');

		if($coupon->discount_start > time())
			return JText::_('COUPON_NOT_YET_USABLE');

		if($coupon->discount_end && $coupon->discount_end < time())
			return JText::_('COUPON_EXPIRED');

		if(hikashop_level(2) && !empty($coupon->discount_access) && $coupon->discount_access != 'all' && ($coupon->discount_access == 'none' || !hikashop_isAllowed($coupon->discount_access)))
			return JText::_('COUPON_NOT_FOR_YOU');

		if(hikashop_level(2) && !empty($coupon->discount_user_id)) {
			$ids = explode(',', $coupon->discount_user_id);
			$user_id = hikashop_loadUser();
			if(empty($user_id) || !in_array($user_id, $ids))
				return JText::_('COUPON_NOT_FOR_YOU');
		}

		if(empty($error_message) && hikashop_level(1) && !empty($coupon->discount_quota) && $coupon->discount_quota <= $coupon->discount_used_times)
			return JText::_('QUOTA_REACHED_FOR_COUPON');

		if(!empty($error_message) || !hikashop_level(1))
			return $error_message;

		if(!empty($coupon->discount_quota_per_user)) {
			$user_id = hikashop_loadUser();
			if($user_id){
				$config =& hikashop_config();
				$cancelled_order_status = explode(',', $config->get('cancelled_order_status'));
				foreach($cancelled_order_status as &$order_status){
					$order_status = $this->database->quote($order_status);
				}
				unset($order_status);

				$query = 'SELECT COUNT(order_id) AS already_used FROM '.hikashop_table('order').' WHERE order_user_id = '.(int)$user_id.' AND order_status NOT IN ('.implode(',',$cancelled_order_status).') AND order_discount_code='.$this->database->quote($coupon->discount_code);
				$this->database->setQuery($query);
				$already_used = (int)$this->database->loadResult();
				if((int)$coupon->discount_quota_per_user <= $already_used) {
					return JText::_('QUOTA_REACHED_FOR_COUPON');
				}
			}
		}

		if(empty($error_message) && $coupon->discount_zone_id) {
			if(!is_array($coupon->discount_zone_id))
				$coupon->discount_zone_id = explode(',',trim($coupon->discount_zone_id,','));
			$zoneClass = hikashop_get('class.zone');
			$zone = $zoneClass->getZones($coupon->discount_zone_id,'zone_namekey','zone_namekey',true);
			if($zone && !count(array_intersect($zone, $zones))){
				return JText::_('COUPON_NOT_AVAILABLE_IN_YOUR_ZONE');
			}
		}

		$ids = array();
		$qty = 0;
		foreach($products as $prod) {
			$qty += (int)$prod->cart_product_quantity;
			if(!empty($prod->product_parent_id))
				$ids[$prod->product_parent_id] = (int)$prod->product_parent_id;
			$ids[$prod->product_id] = (int)$prod->product_id;
		}

		if(empty($ids))
			return JText::_('COUPON_NOT_FOR_EMPTY_CART');

		if(!empty($coupon->discount_product_id) && is_string($coupon->discount_product_id))
			$coupon->discount_product_id = explode(',', $coupon->discount_product_id);
		if(empty($error_message) && !empty($coupon->discount_product_id) && count(array_intersect($ids, $coupon->discount_product_id)) == 0)
			return JText::_('COUPON_NOT_FOR_THOSE_PRODUCTS');

		if(empty($error_message) && $coupon->discount_category_id) {
			if(!is_array($coupon->discount_category_id))
				$coupon->discount_category_id = explode(',', trim($coupon->discount_category_id, ','));
			if($coupon->discount_category_childs) {
				$filters = array('b.category_type IN (\'product\',  \'manufacturer\')','a.product_id IN ('.implode(',',$ids).')');

				$categoryClass = hikashop_get('class.category');
				$categories = $categoryClass->getCategories($coupon->discount_category_id,'category_left, category_right');

				if(!empty($categories)) {
					$categoriesFilters = array();
					foreach($categories as $category) {
						$categoriesFilters[] = 'b.category_left >= '.$category->category_left.' AND b.category_right <= '.$category->category_right;
					}
					if(count($categoriesFilters)) {
						$filters[] = '(('.implode(') OR (',$categoriesFilters).'))';
						hikashop_addACLFilters($filters,'category_access','b');

						$select = 'SELECT a.product_id FROM '.hikashop_table('category').' AS b LEFT JOIN '.hikashop_table('product_category').' AS a ON b.category_id=a.category_id WHERE '.implode(' AND ',$filters);
						$this->database->setQuery($select);
						$id = $this->database->loadRowList();

						$select = 'SELECT a.product_id FROM '.hikashop_table('category').' AS b LEFT JOIN '.hikashop_table('product').' AS a ON b.category_id=a.product_manufacturer_id WHERE '.implode(' AND ',$filters);
						$this->database->setQuery($select);
						$brand_id = $this->database->loadRowList();

						if(empty($id) && empty($brand_id)) {
							return JText::_('COUPON_NOT_FOR_PRODUCTS_IN_THOSE_CATEGORIES');
						}
					}
				}
			} else {
				hikashop_toInteger($coupon->discount_category_id);
				$filters = array('b.category_id IN ('.implode(',',$coupon->discount_category_id).')' ,'a.product_id IN ('.implode(',',$ids).')');
				hikashop_addACLFilters($filters,'category_access','b');

				$select = 'SELECT a.product_id FROM '.hikashop_table('category').' AS b LEFT JOIN '.hikashop_table('product_category').' AS a ON b.category_id=a.category_id WHERE '.implode(' AND ',$filters);
				$this->database->setQuery($select);
				$id = $this->database->loadRowList();

				$select = 'SELECT a.product_id FROM '.hikashop_table('category').' AS b LEFT JOIN '.hikashop_table('product').' AS a ON b.category_id=a.product_manufacturer_id WHERE '.implode(' AND ',$filters);
				$this->database->setQuery($select);
				$brand_id = $this->database->loadRowList();

				if(empty($id) && empty($brand_id)) {
					return JText::_('COUPON_NOT_FOR_PRODUCTS_IN_THOSE_CATEGORIES');
				}
			}
		}

		$coupon->products = array();
		if(!empty($coupon->discount_product_id)) {
			foreach ($products as $product) {
				if(!in_array($product->product_id, $coupon->discount_product_id)) {
					foreach ($products as $product2) {
						if(isset($product->cart_product_parent_id) && $product2->cart_product_id == $product->cart_product_parent_id && in_array($product2->product_id, $coupon->discount_product_id)){
							$coupon->products[] = $product;
						}
					}
				} else {
					$coupon->products[] = $product;
				}
			}
		} else if(!empty($id)) {
			foreach ($products as $product) {
				foreach ($id as $productid) {
					if($product->product_id !== $productid[0]) {
						foreach ($products as $product2) {
							if(!empty($product->cart_product_parent_id) && $product2->cart_product_id == $product->cart_product_parent_id && $product2->product_id == $productid[0]) {
								$coupon->products[] = $product;
								break 2;
							}
						}
					} else {
						$coupon->products[] = $product;
						break;
					}
				}
			}
		} else {
			foreach($products as $product) {
				$coupon->products[] = $product;
			}
			$coupon->all_products = true;
		}
		$min_order = bccomp(sprintf('%F',$coupon->discount_minimum_order), 0, 5);
		$max_order = bccomp(sprintf('%F',$coupon->discount_maximum_order), 0, 5);
		if(empty($error_message) && ($min_order || $max_order)) {

			$currencyClass->convertCoupon($coupon, $total->prices[0]->price_currency_id);
			$config =& hikashop_config();
			$discount_before_tax = $config->get('coupon_before_tax');
			$var = 'price_value_with_tax';
			if($discount_before_tax) {
				$var = 'price_value';
			}

			$total_amount = 0;
			if(!empty($coupon->products)) {
				foreach($coupon->products as $product) {
					if($product->cart_product_quantity > 0)
						$total_amount += @$product->prices[0]->$var;
				}
			}
			if($coupon->discount_minimum_order > 0 && $coupon->discount_minimum_order > $total_amount)
				return JText::sprintf('ORDER_NOT_EXPENSIVE_ENOUGH_FOR_COUPON',$currencyClass->format($coupon->discount_minimum_order,$coupon->discount_currency_id));
			if($coupon->discount_maximum_order > 0 && $coupon->discount_maximum_order < $total_amount)
				return JText::sprintf('ORDER_TOO_PRODUCTS_FOR_COUPON',$currencyClass->format($coupon->discount_maximum_order,$coupon->discount_currency_id));
		}
		$min_qty = (int)$coupon->discount_minimum_products;
		$max_qty = (int)$coupon->discount_maximum_products;

		if(empty($error_message) && ($min_qty > 0 || $max_qty > 0)) {
			$qty = 0;
			if(!empty($coupon->products)) {
				foreach($coupon->products as $product) {
					$qty += $product->cart_product_quantity;
				}
			}

			if($min_qty > 0 && $min_qty > $qty)
				return JText::sprintf('NOT_ENOUGH_PRODUCTS_FOR_COUPON', $min_qty);
			if($max_qty > 0 && $max_qty < $qty)
				return JText::sprintf('TOO_MUCH_PRODUCTS_FOR_COUPON', $max_qty);
		}

		return $error_message;
	}


	function recalculateDiscountValue(&$coupon, &$products, &$id) {
		if(bccomp(sprintf('%F',$coupon->discount_percent_amount), 0, 5) === 0 || (empty($coupon->discount_coupon_product_only) && (empty($coupon->products) || !empty($coupon->all_products))))
			return;

		$coupon->discount_flat_amount = 0;

		$config = hikashop_config();
		$price = 'price_value';
		$price_without_discount = 'price_value_without_discount';
		if(((!empty($coupon->discount_tax_id) || !empty($coupon->discount_tax)) && !$config->get('coupon_before_tax', 1)) || $config->get('floating_tax_prices', 0) ) {
			$price = 'price_value_with_tax';
			$price_without_discount = 'price_value_without_discount_with_tax';
		}

		if(!empty($coupon->discount_product_id)) {
			if(!is_array($coupon->discount_product_id)) {
				$coupon->discount_product_id = explode(',', $coupon->discount_product_id);
			}
			$variantsFirst = array_reverse($products);
			foreach ($variantsFirst as $product) {
				if(!isset($product->prices[0]))
					continue;
				if(empty($product->cart_product_quantity))
					continue;
				if(in_array($product->product_id,$coupon->discount_product_id)  || (!empty($product->product_parent_id) && in_array($product->product_parent_id,$coupon->discount_product_id))) {
					switch($coupon->discount_coupon_nodoubling) {
						case 2:
							if(isset($product->prices[0]->$price_without_discount)) {
								$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->$price_without_discount) / 100;
								$coupon->discount_flat_amount -= $product->prices[0]->$price_without_discount - $product->prices[0]->$price;

								break;
							}
						case 1:
							if(isset($product->prices[0]->$price_without_discount)) {
								break;
							}
						default:
							$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->$price) / 100;
							break;
					}
				}
			}
		} else if(!empty($coupon->products)) {

			$variantsFirst = array_reverse($products);
			foreach ($variantsFirst as $product) {
				if(!isset($product->prices[0]) || empty($product->cart_product_quantity))
					continue;

				foreach ($coupon->products as $prod) {
					if(!empty($prod->product_parent_id))
						$productid = $prod->product_parent_id;
					else
						$productid = $prod->product_id;

					if($product->product_id == $productid && empty($product->variants) || (!empty($product->product_parent_id) && $product->product_parent_id == $productid)) {
						switch($coupon->discount_coupon_nodoubling) {
							case 2:
								if(isset($product->prices[0]->$price_without_discount)) {
									$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->$price_without_discount) / 100;
									$coupon->discount_flat_amount -= $product->prices[0]->$price_without_discount - $product->prices[0]->$price;
									break;
								}
							case 1:
								if(isset($product->prices[0]->$price_without_discount))
									break;

							default:
								if(isset($product->prices[0]->price_value))
									$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->$price) / 100;
								break;
						}
						break;
					}
				}

			}
		}

		if($coupon->discount_flat_amount < 0)
			$coupon->discount_flat_amount = 0;

		if (bccomp(sprintf('%F',$coupon->discount_flat_amount), 0, 5)) {
			$coupon->discount_percent_amount_orig = $coupon->discount_percent_amount;
			$coupon->discount_percent_amount = 0;
			$coupon->discount_coupon_nodoubling_orig = $coupon->discount_coupon_nodoubling;
			$coupon->discount_coupon_nodoubling = null;
		}

		$currencyHelper = hikashop_get('class.currency');
		$coupon->discount_flat_amount = $currencyHelper->round($coupon->discount_flat_amount, $currencyHelper->getRounding($coupon->discount_currency_id, true));
	}


	function addCoupon(&$coupon1, &$products, &$currencyClass, $discountmode) {
		$totaldiscount = 0.0;
		$totaldiscount_with_tax = 0.0;
		$totalprice = 0.0;
		$totalprice_with_tax = 0.0;
		$totalnondiscount = 0.0;
		$totalnondiscount_with_tax = 0.0;

		foreach($products as $k => $product) {
			if(!empty($product->prices)&&$product->cart_product_quantity>0){
				$price = reset($product->prices);
				if (isset($price->price_value)) {
					$totalprice += $price->price_value;
					if (isset($price->price_value_without_discount)){
						$totaldiscount += $price->price_value_without_discount - $price->price_value;
					}
					else {
						$totalnondiscount += $price->price_value;
					}
				}
				if (isset($price->price_value_with_tax)) {
					$totalprice_with_tax += $price->price_value_with_tax;
					if (isset($price->price_value_without_discount_with_tax)){
						$totaldiscount_with_tax += $price->price_value_without_discount_with_tax - $price->price_value_with_tax;
					}
					else {
						$totalnondiscount_with_tax += $price->price_value_with_tax;
					}
				}
			}
		}

		if (!bccomp(sprintf('%F',$totaldiscount_with_tax), 0, 5) || !bccomp(sprintf('%F',$totaldiscount), 0, 5)) {
			$currencyClass->addCoupon($coupon1->total,$coupon1);
			return $coupon1;
		}

		if (bccomp(sprintf('%F',$coupon1->discount_flat_amount), 0, 5) && $totalnondiscount >= $coupon1->discount_flat_amount) {
			$currencyClass->addCoupon($coupon1->total,$coupon1);
			return $coupon1;
		}

		$totalprice += $totaldiscount;
		$totalprice_with_tax += $totaldiscount_with_tax;

		$coupon2 = clone($coupon1);
		$coupon2->total = clone($coupon1->total);
		$coupon2->total->prices = $this->copyStandardPrices($coupon1->total->prices);
		switch ($discountmode) {
			case 2:
				$coupon2->total->prices[0]->price_value_with_tax = $totalprice_with_tax;
				$coupon2->total->prices[0]->price_value = $totalprice;

				$currencyClass->addCoupon($coupon2->total,$coupon2);
				$coupon2->total->prices[0]->price_value_without_discount_with_tax -= $totaldiscount_with_tax;
				$coupon2->total->prices[0]->price_value_without_discount -= $totaldiscount;
				if(isset($coupon2->discount_percent_amount_calculated_without_tax))
					$coupon2->discount_percent_amount_calculated_without_tax -= $totaldiscount;
				$coupon2->discount_value_without_tax -= $totaldiscount;
				if(isset($coupon2->discount_percent_amount_calculated))
					$coupon2->discount_percent_amount_calculated -= $totaldiscount;
				$coupon2->discount_value -= $totaldiscount;
				$coupon2->discount_flat_amount = $coupon2->discount_value;
				break;
			default:
				if($coupon2->discount_flat_amount > $totalnondiscount_with_tax) {
					$coupon2->discount_flat_amount=0;
				}
				$total = new stdClass();
				$obj = new stdClass();
				$total->prices = array($obj);
				$total->prices[0]->price_value_with_tax = $totalnondiscount_with_tax;
				$total->prices[0]->price_value = $totalnondiscount;
				$total->prices[0]->taxes = array();
				$currencyClass->addCoupon($total,$coupon2);
				break;
		}

		if (isset($coupon2->discount_percent_amount_calculated) && $discountmode < 2) {
			$price_diff = $coupon2->discount_percent_amount_calculated - $totaldiscount_with_tax;
		}
		elseif(isset($coupon2->discount_percent_amount_calculated) && $discountmode == 2){
			$price_diff = $coupon2->discount_value;
		}else if(isset($coupon2->discount_percent_amount_calculated) ){
			$price_diff = $coupon2->discount_percent_amount_calculated - $totaldiscount_with_tax;
			$price_diff += $totaldiscount;
		}

		if (@$price_diff <= 0) {
			$couponErrorMessage = JText::_('COUPON_NO_VALUE_WHEN_DISCOUNT');
			hikaInput::get()->set('coupon_error_message',$couponErrorMessage);
			return $coupon1;
		}
		if(!(isset($coupon2->discount_percent_amount_calculated) && $discountmode == 2)){
			$coupon2->discount_percent_amount_calculated_without_tax = $price_diff + $totaldiscount;
			$coupon2->discount_value_without_tax = $price_diff + $totaldiscount;
			$coupon2->discount_percent_amount_calculated = $price_diff + $totaldiscount_with_tax;
			$coupon2->discount_value = $price_diff + $totaldiscount_with_tax;
		}
		if ($discountmode == 1) {
			$coupon2->total->prices[0]->price_value_without_discount_with_tax = $totalprice_with_tax;
			$coupon2->total->prices[0]->price_value_without_discount = $totalprice;
			$coupon2->total->prices[0]->price_value_with_tax -= $coupon2->discount_value;
			$coupon2->total->prices[0]->price_value -= $coupon2->discount_value_without_tax;
		}

		if ($coupon2->discount_flat_amount != $coupon2->discount_value_without_tax) {
			$couponErrorMessage = JText::_('COUPON_LIMITED_VALUE_WHEN_DISCOUNT');
			hikaInput::get()->set('coupon_error_message',$couponErrorMessage);
		}
		return $coupon2;
	}

	public function afterShippingProcessing(&$cart){
		if(empty($cart->coupon))
			return;
		if(empty($cart->coupon->discount_shipping_percent) || bccomp(sprintf('%F',$cart->coupon->discount_shipping_percent), 0, 5) === 0)
			return;
		if(empty($cart->shipping))
			return;

		$currencyClass = hikashop_get('class.currency');
		$round = $currencyClass->getRounding(@$cart->full_total->prices[0]->price_currency_id,true);
		$shipping_price = 0.0;
		$shipping_price_with_tax = 0.0;
		$taxes = array();
		foreach($cart->shipping as &$shipping) {
			if(empty($shipping->shipping_price_with_tax) || bccomp(sprintf('%F',$shipping->shipping_price_with_tax), 0, 5) === 0)
				continue;

			$shipping_price_with_tax += $currencyClass->round($cart->coupon->discount_shipping_percent * $shipping->shipping_price_with_tax / 100, $round);
			$shipping_price += $currencyClass->round($cart->coupon->discount_shipping_percent * $shipping->shipping_price / 100, $round);
			if(!empty($shipping->taxes)) {
				foreach($shipping->taxes as $tax){
					$tax->tax_amount = $currencyClass->round($cart->coupon->discount_shipping_percent * $tax->tax_amount / 100, $round);
					$tax->amount = $currencyClass->round($cart->coupon->discount_shipping_percent * $tax->amount / 100, $round);
					if(isset($taxes[$tax->tax_namekey])) {
						$taxes[$tax->tax_namekey]->tax_amount += $tax->tax_amount;
						$taxes[$tax->tax_namekey]->amount += $tax->amount;
					} else
						$taxes[$tax->tax_namekey] = clone($tax);
				}
			}
		}

		if($shipping_price_with_tax == 0.0)
			return;

		$cart->coupon->discount_value_without_tax += $shipping_price;
		$cart->coupon->discount_value += $shipping_price_with_tax;
		if(!isset($cart->coupon->taxes))
			$cart->coupon->taxes = array();
		foreach($taxes as $tax) {
			if(isset($cart->coupon->taxes[$tax->tax_namekey])) {
				$cart->coupon->taxes[$tax->tax_namekey]->tax_amount += $tax->tax_amount;
				$cart->coupon->taxes[$tax->tax_namekey]->amount += $tax->amount;
			} else
				$cart->coupon->taxes[$tax->tax_namekey] = clone($tax);
		}

		$cart->full_total->prices[0]->price_value -= $shipping_price;
		$cart->full_total->prices[0]->price_value_with_tax -= $shipping_price_with_tax;
		foreach($taxes as $tax) {
			$tax->tax_amount = -$tax->tax_amount;
			$tax->amount = -$tax->amount;
			if(isset($cart->full_total->prices[0]->taxes[$tax->tax_namekey])) {
				$cart->full_total->prices[0]->taxes[$tax->tax_namekey]->tax_amount += $tax->tax_amount;
				$cart->full_total->prices[0]->taxes[$tax->tax_namekey]->amount += $tax->amount;
			} else
				$cart->full_total->prices[0]->taxes[$tax->tax_namekey]->tax_amount = clone($tax);
		}
	}

	function copyStandardPrices($prices) {
		$copiedPrices = array();
		for ($i=0; $i<count($prices); $i++) $copiedPrices[$i] = clone($prices[$i]);
		return $copiedPrices;
	}


	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		static $multiTranslation = null;
		if($multiTranslation === null) {
			$translationHelper = hikashop_get('helper.translation');
			$multiTranslation = $translationHelper->isMulti(true) && $translationHelper->falang;
		}

		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$category_type = array('discount','coupon');
		$fullLoad = false;

		$depth = (int)@$options['depth'];
		$start = (int)@$options['start'];
		$limit = (int)@$options['limit'];


		if($depth <= 0)
			$depth = 1;
		if($limit <= 0)
			$limit = 200;

		if(@$options['type']) {
			$category_type = explode(',',$options['type']);
		}

		$category_types = array();
		foreach($category_type as $t) {
			$category_types[] = $db->Quote($t);
		}

		$select = array('d.*');
		$table = array(hikashop_table('discount').' AS d');
		$where = array('d.discount_type IN ('.implode(',', $category_types).')');
		$order = ' ORDER BY d.discount_type ASC, d.discount_code ASC';

		if(!empty($search))
			$where[] = 'd.discount_code LIKE \'%'.$db->escape($search).'%\'';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . ' WHERE ' . implode(' AND ', $where);
		$db->setQuery($query, $start, $limit);

		if(!hikashop_isClient('administrator') && $multiTranslation && class_exists('JFalangDatabase')) {
			$discounts = $db->loadObjectList('discount_id', 'stdClass', false);
		} elseif(!hikashop_isClient('administrator') && $multiTranslation && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
			$discounts = $db->loadObjectList('discount_id', false);
		} else {
			$discounts = $db->loadObjectList('discount_id');
		}
		if(count($discounts) < 200)
			$fullLoad = true;

		foreach($discounts as $discount) {
			$ret[0][$discount->discount_id] = (!empty($discount->translation)) ? $discount->translation : $discount->discount_code;
		}

		if(!empty($value)) {
			if(!is_array($value))
				$value = array($value);

			$filter_id = array();
			foreach($value as $v) {
				$filter_id[] = (int)$v;
			}
			$query = 'SELECT d.* '.
					' FROM ' . hikashop_table('discount') . ' AS d '.
					' WHERE d.discount_id IN ('.implode(',', $filter_id).')';
			$db->setQuery($query);

			if(!hikashop_isClient('administrator') && $multiTranslation && class_exists('JFalangDatabase')) {
				$discounts = $db->loadObjectList('discount_id', 'stdClass', false);
			} elseif(!hikashop_isClient('administrator') && $multiTranslation && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
				$discounts = $db->loadObjectList('discount_id', false);
			} else {
				$discounts = $db->loadObjectList('discount_id');
			}

			if(!empty($discounts)) {
				foreach($discounts as $discount) {
					$discount->name = (!empty($discount->translation)) ? $discount->translation :  JText::_($discount->discount_code);
					$ret[1][$discount->discount_id] = $discount;
				}
			}
			unset($discounts);

			if($mode == hikashopNameboxType::NAMEBOX_SINGLE)
				$ret[1] = reset($ret[1]);
		}

		return $ret;
	}
}
