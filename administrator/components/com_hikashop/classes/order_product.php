<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopOrder_productClass extends hikashopClass {
	var $tables = array('order_product');
	var $pkeys = array('order_product_id');

	public function get($order_product_id, $default = null) {
		$result = parent::get($order_product_id);
		if(!empty($result->order_product_tax_info))
			$result->order_product_tax_info = hikashop_unserialize($result->order_product_tax_info);
		return $result;
	}

	public function save(&$products) {
		if(empty($products))
			return true;

		$config = hikashop_config();

		$items = array();
		$updates = array();
		$wishlistUpdates = array();
		$productsQuantity = array();
		$discounts = array();
		$fields = array(
			'order_id',
			'product_id',
			'order_product_quantity',
			'order_product_name',
			'order_product_code',
			'order_product_price',
			'order_product_tax',
			'order_product_options',
			'order_product_option_parent_id',
			'order_product_tax_info',
			'order_product_wishlist_id',
			'order_product_wishlist_product_id',
			'order_product_shipping_id',
			'order_product_shipping_method',
			'order_product_shipping_price',
			'order_product_shipping_tax',
			'order_product_shipping_params'
		);


		$productClass = hikashop_get('class.product');
		$product_ids = array();
		foreach($products as $product) {
			$product_ids[ (int)$product->product_id ] = (int)$product->product_id;
		}


		$query = 'SELECT product_id, product_type, product_parent_id FROM ' . hikashop_table('product') . ' WHERE product_id IN ('.implode(',', $product_ids).')';
		unset($product_ids);
		$this->database->setQuery($query);
		$products_data = $this->database->loadObjectList('product_id');

		$order_id = 0;
		foreach($products as $product) {
			$order_id = (int)$product->order_id;

			if(isset($product->order_product_tax_info) && !is_string($product->order_product_tax_info))
				$product->order_product_tax_info = serialize($product->order_product_tax_info);
			if(!empty($product->order_product_options) && (!is_string($product->order_product_options) || is_numeric($product->order_product_options)))
				$product->order_product_options = serialize($product->order_product_options);

			$line = array(
				$order_id,
				(int)$product->product_id,
				(int)$product->order_product_quantity,
				$this->database->Quote($product->order_product_name),
				$this->database->Quote($product->order_product_code),
				$this->database->Quote(@$product->order_product_price),
				$this->database->Quote(@$product->order_product_tax),
				$this->database->Quote(@$product->order_product_options),
				(int)@$product->cart_product_id,
				$this->database->Quote(@$product->order_product_tax_info),
				(int)@$product->order_product_wishlist_id,
				(int)@$product->order_product_wishlist_product_id,
				$this->database->Quote(@$product->order_product_shipping_id),
				$this->database->Quote(@$product->order_product_shipping_method),
				(float)@$product->order_product_shipping_price,
				(float)@$product->order_product_shipping_tax,
				$this->database->Quote(@$product->order_product_shipping_params)
			);
			if(!empty($itemFields)) {
				foreach($itemFields as $field) {
					if($field->field_type == 'customtext')
						continue;

					$namekey = $field->field_namekey;
					$line[] = $this->database->Quote(@$product->$namekey);
				}
			}

			$items[] = '('.implode(',',$line).')';

			if(!empty($product->product_id) && empty($product->no_update_qty)) {
				if(empty($productsQuantity[(int)$product->product_id]))
					$productsQuantity[(int)$product->product_id] = 0;

				$quantity = $product->order_product_quantity;

				if(empty($quantity) && !empty($product->order_product_options)){
					$options = hikashop_unserialize($product->order_product_options);
					if(!empty($options['type']) && $options['type'] == 'bundle' && !empty($options['related_quantity']) && !empty($product->cart_product_option_parent_id)){
						foreach($products as $main_product){
							if($main_product->cart_product_id == $product->cart_product_option_parent_id){
								$quantity = $options['related_quantity'] * $main_product->order_product_quantity;
								break;
							}
						}
					}
				}
				$productsQuantity[(int)$product->product_id] += $quantity;

				$prod = $products_data[(int)$product->product_id];
				if($prod->product_type == 'variant' && !empty($prod->product_parent_id)) {
					if(empty($productsQuantity[(int)$prod->product_parent_id]))
						$productsQuantity[(int)$prod->product_parent_id] = 0;
					$productsQuantity[(int)$prod->product_parent_id] += $quantity;
				}
			}

			if(!empty($product->discount)) {
				if(empty($discounts[$product->discount->discount_code]))
					$discounts[$product->discount->discount_code] = 0;
				$discounts[$product->discount->discount_code] += (int)$product->order_product_quantity;
			}

			if(isset($product->order_product_wishlist_id) && (int)$product->order_product_wishlist_id != 0) {
				$wishlistUpdates[] = array(
					'product' => $product->product_id,
					'cart' => (int)$product->order_product_wishlist_id,
					'cart_product' => (int)@$product->order_product_wishlist_product_id,
					'qty' => (int)$product->order_product_quantity
				);
			}
		}

		if(!empty($productsQuantity)) {
			foreach($productsQuantity as $id => $qty) {
				if(empty($updates[$qty]))
					$updates[$qty] = array();
				$updates[$qty][] = $id;
			}
		}

		if(!empty($updates)) {
			$this->updateQuantityAndSales($updates);
		}

		if(!empty($wishlistUpdates) && (int)$config->get('update_wishlist_quantity', 0)) {
			$this->updateWishlistProducts($wishlistUpdates);
		}
		unset($wishlistUpdates);

		$query = 'INSERT IGNORE INTO '.hikashop_table('order_product').' ('.implode(',', $fields).') VALUES '.implode(',', $items);
		$this->database->setQuery($query);
		$this->database->query();

		$query = 'SELECT * FROM '.hikashop_table('order_product').' WHERE order_id = '.$order_id;
		$this->database->setQuery($query);
		$newProducts = $this->database->loadObjectList('order_product_option_parent_id');

		$mainProducts = array();
		foreach($products as &$product) {
			if(!empty($product->cart_product_option_parent_id) && (int)$product->cart_product_id > 0)
				$mainProducts[$product->cart_product_option_parent_id][] = (int)$product->cart_product_id;

			if(!empty($product->cart_product_id) && (int)$product->cart_product_id > 0 && isset($newProducts[$product->cart_product_id]))
				$product->order_product_id = (int)$newProducts[$product->cart_product_id]->order_product_id;
		}
		unset($product);

		$keep = array();
		if(!empty($mainProducts)) {
			foreach($mainProducts as $k => $v) {
				$keep[] = (int)@$newProducts[$k]->order_product_id;
				$query = 'UPDATE ' . hikashop_table('order_product') .
					' SET order_product_option_parent_id = ' . (int)@$newProducts[$k]->order_product_id .
					' WHERE order_product_option_parent_id IN (' . implode(',', $v) . ') AND order_id = ' . $order_id;
				$this->database->setQuery($query);
				$this->database->query();
			}
		}

		$query = 'UPDATE '.hikashop_table('order_product').
			' SET order_product_option_parent_id = 0 '.
			' WHERE order_id = ' . $order_id;
		if(!empty($keep))
			$query .= ' AND order_product_option_parent_id NOT IN (' . implode(',', $keep) . ')';
		$this->database->setQuery($query);
		$this->database->query();

		if(!empty($discounts)) {
			$discountUpdates = array();
			foreach($discounts as $code => $qty) {
				if(empty($discountUpdates[$qty]))
					$discountUpdates[$qty] = array();
				$discountUpdates[$qty][] = $this->database->Quote($code);
			}
			foreach($discountUpdates as $k => $update) {
				$query = 'UPDATE ' . hikashop_table('discount') .
					' SET discount_used_times = discount_used_times + ' . (int)$k.
					' WHERE discount_code IN (' . implode(',', $update) . ')';
				$this->database->setQuery($query);
				$this->database->query();
			}
		}

		$ret = array();
		foreach($products as $product) {
			if(empty($product->cart_product_id) || (int)$product->cart_product_id <= 0)
				continue;
			$ret[ (int)$product->cart_product_id ] = isset($newProducts[$product->cart_product_id]) ? (int)$newProducts[$product->cart_product_id]->order_product_id : 0;
		}
		if(empty($ret))
			$ret = true;
		return $ret;
	}

	protected function updateQuantityAndSales(&$updates, $cancel = false){
		foreach($updates as $k => $update) {
			$localCancel = $cancel;
			if($k < 0) {
				$k = -$k;
				$localCancel = !$cancel;
			}
			if($localCancel) {
				$query = 'UPDATE '.hikashop_table('product').' SET product_quantity = product_quantity + '.(int)$k.' WHERE product_id IN ('.implode(',',$update).') AND product_quantity > -1';
			} else {
				$query = 'UPDATE '.hikashop_table('product').' SET product_quantity = GREATEST(0, product_quantity - '.(int)$k.') WHERE product_id IN ('.implode(',',$update).') AND product_quantity >= 0';
			}
			$this->database->setQuery($query);
			$this->database->query();

			if($localCancel) {
				$query = 'UPDATE '.hikashop_table('product').' SET product_sales = (GREATEST('.(int)$k.', product_sales) - '.(int)$k.') WHERE product_id IN ('.implode(',',$update).')';
			} else {
				$query = 'UPDATE '.hikashop_table('product').' SET product_sales = product_sales + '.(int)$k.' WHERE product_id IN ('.implode(',',$update).')';
			}
			$this->database->setQuery($query);
			$this->database->query();
		}
	}


	protected function updateWishlistProducts($wishlistUpdates) {
	}

	public function update(&$product) {
		$old = null;
		if(!empty($product->order_product_id))
			$old = $this->get($product->order_product_id);

		$update_quantities = empty($product->no_update_qty);
		unset($product->no_update_qty);

		if($update_quantities && (isset($product->change) || ((empty($old) && !empty($product->product_id)) || (!empty($old->product_id) && $old->order_product_quantity != $product->order_product_quantity)))) {
			$k = (int)$product->order_product_quantity;
			if(!empty($old)){
				$k = $product->order_product_quantity - $old->order_product_quantity;
				if(isset($product->change) && $product->change == 'plus') {
					$k = -(int)$product->order_product_quantity;
				} elseif(isset($product->change) && $product->change == 'minus') {
					$k = (int)$product->order_product_quantity;
				}
			}

			if(!empty($product->product_id))
				$product_id = (int)$product->product_id;
			else
				$product_id = (int)$old->product_id;

			$productClass = hikashop_get('class.product');
			$prod = $productClass->get($product_id);

			$updates = array($k => array((int)$product_id));
			if(!empty($prod) && $prod->product_type == 'variant' && !empty($prod->product_parent_id)) {
				$updates[$k][] = (int)$prod->product_parent_id;
			}
			$this->updateQuantityAndSales($updates);
		}

		if(!empty($product->tax_namekey)) {
			$tax = new stdClass();
			if(!empty($product->product_id) && !empty($old)) {
				if(is_string($old->order_product_tax_info))
					$old->order_product_tax_info = hikashop_unserialize($old->order_product_tax_info);
				$tax = reset($old->order_product_tax_info);
			}
			$tax->tax_namekey = $product->tax_namekey;
			$tax->tax_amount = $product->order_product_tax;
			$product->order_product_tax_info = array($tax);
		}

		if(isset($product->order_product_tax_info) && !is_string($product->order_product_tax_info)) {
			$product->order_product_tax_info = serialize($product->order_product_tax_info);
		}

		if(empty($product->order_product_quantity) && @$product->order_product_code != 'order additional') {
			if(empty($product->order_product_id)) {
				return false;
			}
			return $this->delete($product->order_product_id);
		}

		unset($product->change);
		unset($product->tax_namekey);

		$product->order_product_id = parent::save($product);
		return $product->order_product_id;
	}
}
