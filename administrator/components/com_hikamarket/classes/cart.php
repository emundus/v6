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
class hikamarketCartClass extends hikamarketClass {

	protected $tables = array();
	protected $pkeys = array();
	protected $toggle = array();

	protected $config = null;
	static protected $zone_id = null;
	static protected $vendor_id = null;

	public function  __construct($config = array()) {
		parent::__construct($config);
		$this->config = hikamarket::config();
	}

	public function onAfterCartProductsLoad(&$cart) {
		if(!empty($cart->products))
			$this->manageZoneVendor($cart->products);
	}

	public function manageZoneVendor(&$products, $address = null) {
		if(empty($products) || !$this->config->get('allow_zone_vendor', 0))
			return;

		$zone_id = hikamarket::getZone('shipping');
		if(self::$zone_id != $zone_id) {
			self::$zone_id = $zone_id;
			self::$vendor_id = null;

			$zoneClass = hikamarket::get('shop.class.zone');
			$zones = $zoneClass->getZoneParents($zone_id);

			$zonesQuoted = array();
			foreach($zones as $z) {
				$zonesQuoted[] = $this->db->Quote($z);
			}

			$query = 'SELECT vendor.vendor_id, vendor.vendor_zone_id, zone.zone_namekey, zone.zone_type '.
				' FROM ' . hikamarket::table('vendor') . ' AS vendor '.
				' INNER JOIN ' . hikamarket::table('shop.zone') . ' AS zone ON vendor.vendor_zone_id = zone.zone_id '.
				' WHERE zone.zone_namekey IN ('.implode(',', $zonesQuoted).') ORDER BY vendor.vendor_id ASC';
			$this->db->setQuery($query);
			$vendors = $this->db->loadObjectList('zone_namekey');

			if(!empty($vendors)) {
				foreach($zones as $z) {
					if(isset($vendors[$z])) {
						self::$vendor_id = (int)$vendors[$z]->vendor_id;
						break;
					}
				}
			}
		}

		if(!empty(self::$vendor_id)) {
			foreach($products as &$product) {
				if($product->product_vendor_id == 0) // || $product->product_vendor_id == 1)
					$product->product_vendor_id = self::$vendor_id;
			}
			unset($product);
		}
	}

	public function onAfterProductCheckQuantities(&$products, &$cart, $options) {
		if(!empty($cart->cart_type) && $cart->cart_type != 'cart')
			return;

		$limit_vendors_in_cart = $this->config->get('vendors_in_cart', 0);
		if($limit_vendors_in_cart == 0)
			return;

		if(empty($cart->cart_products))
			return;

		$cart_products = array();
		$pids = array();
		foreach($cart->cart_products as $k => $cp) {
			if(!is_numeric($k) && substr($k, 0, 1) == 'p')
				continue;

			$o = new stdClass();
			$o->product_id = $cp->product_id;
			$o->product_type = $cp->product_type;
			$o->product_parent_id = $cp->product_parent_id;

			$pids[ (int)$cp->product_id ] = (int)$cp->product_id;
			$pids[ (int)$cp->product_parent_id ] = (int)$cp->product_parent_id;

			$cart_products[ $k ] = $o;
		}

		foreach($products as $p) {
			$pid = !empty($p['id']) ? (int)$p['id'] : (int)@$p['data']->product_id;
			if(empty($pid))
				continue;
			$pids[ $pid ] = (int)$pid;
			if(!empty($p['data']) && !empty($p['data']->product_parent_id)) {
				$i = (int)$p['data']->product_parent_id;
				$pids[$i] = $i;
			}
		}

		unset($pids[0]);
		$query = 'SELECT product_id, product_vendor_id FROM '.hikamarket::table('shop.product').' WHERE product_id IN ('.implode(',', $pids).')';
		$this->db->setQuery($query);
		$product_vendors = $this->db->loadObjectList('product_id');

		$add_vendors = array();
		foreach($products as $p) {
			$pid = !empty($p['id']) ? (int)$p['id'] : (int)@$p['data']->product_id;
			if(empty($pid))
				continue;

			$v = 0;
			if(isset($product_vendors[ $pid ]))
				$v = (int)$product_vendors[ $pid ]->product_vendor_id;
			if($v == 0 && !empty($p['data']) && !empty($p['data']->product_parent_id))
				$v = (int)$product_vendors[ (int)$p['data']->product_parent_id ]->product_vendor_id;

			if($v == 0)
				$v = 1;

			if(!isset($add_vendors[ $v ]))
				$add_vendors[ $v ] = 0;
			$add_vendors[ $v ]++;
		}

		if($limit_vendors_in_cart == 2 && count($add_vendors) == 1 && !empty($add_vendors[1]) && !$this->config->get('allow_zone_vendor', 0))
			return;

		foreach($cart_products as $k => &$cp) {
			if(!is_numeric($k) && substr($k, 0, 1) == 'p')
				continue;

			$cp->product_vendor_id = (int)@$product_vendors[ (int)$cp->product_id ]->product_vendor_id;
			if(!empty($cp->product_vendor_id) || empty($cp->product_parent_id))
				continue;
			$cp->product_vendor_id = (int)@$product_vendors[ (int)$cp->product_parent_id ]->product_vendor_id;
		}
		unset($cp);

		$this->manageZoneVendor($cart_products);

		$current_vendor_id = 1;
		$cart_vendors = array();
		foreach($cart_products as $k => $cp) {
			if(empty($cart_vendors[ (int)$cp->product_vendor_id ]))
				$cart_vendors[ (int)$cp->product_vendor_id ] = 0;
			$cart_vendors[ (int)$cp->product_vendor_id ]++;

			if((int)$cp->product_vendor_id > 1)
				$current_vendor_id = (int)$cp->product_vendor_id;
		}

		if(!empty($options['message'])) {
			$vendor_ids = array_merge(array(1), array_keys($add_vendors), array_keys($cart_vendors));
			$query = 'SELECT vendor_id, vendor_name FROM ' . hikamarket::table('vendor') . ' as v WHERE v.vendor_id IN (' . implode(',', $vendor_ids). ')';
			$this->db->setQuery($query);
			$vendors = $this->db->loadObjectList('vendor_id');
		}

		$cartClass = hikamarket::get('shop.class.cart');
		foreach($products as &$p) {
			$pid = !empty($p['id']) ? (int)$p['id'] : (int)@$p['data']->product_id;
			if(empty($pid))
				continue;

			$v = isset($product_vendors[ $pid ]) ? (int)$product_vendors[ $pid ]->product_vendor_id : 0;
			if($v == 0 && !empty($p['data']) && !empty($p['data']->product_parent_id))
				$v = (int)$product_vendors[ (int)$p['data']->product_parent_id ]->product_vendor_id;
			if($v == 0 && !empty(self::$zone_id))
				$v = self::$vendor_id;

			if(isset($cart_vendors[$v]))
				continue;
			if($limit_vendors_in_cart == 2 && $v <= 1)
				continue;

			$p['qty'] = 0;

			if(empty($options['message']))
				continue;

			if($v == 0)
				$v = 1;

			$msg = JText::sprintf('VENDOR_CART_PRODUCT_REFUSED', $p['data']->product_name, $vendors[$v]->vendor_name, $vendors[$current_vendor_id]->vendor_name);
			if($limit_vendors_in_cart == 2 && $v > 1 && isset($vendors[1]) && !empty($vendors[1]->vendor_name))
				$msg = JText::sprintf('VENDOR_CART_PRODUCT_REFUSED_2', $p['data']->product_name, $vendors[$v]->vendor_name, $vendors[$current_vendor_id]->vendor_name, $vendors[1]->vendor_name);
			$cartClass->addMessage($cart, array(
				'msg' => $msg,
				'product_id' => $pid,
				'type' => 'notice'
			));
		}
		unset($p);
	}

	public function onAfterCheckCartQuantities(&$cart, $parent_products, &$ret) {
		if($cart->cart_type != 'cart')
			return;

		$limit_vendors_in_cart = $this->config->get('vendors_in_cart', 0);
		if($limit_vendors_in_cart == 0)
			return;

		if(empty($cart->cart_products))
			return;

		$vendor_zone = (int)$this->config->get('allow_zone_vendor', 0);
		if(empty($vendor_zone) && count($cart->cart_products) == 1)
			return;

		$cart_vendors = array();
		foreach($cart->products as $k => &$p) {
			if(!is_numeric($k) && substr($k, 0, 1) == 'p')
				continue;
			$v = (int)$p->product_vendor_id;
			if(empty($v) && $p->product_type == 'variant') {
				$v = (int)$cart->products['p'.$k]->product_vendor_id;
				$p->product_vendor_id = $v;
			}

			if(empty($cart_vendors[$v]))
				$cart_vendors[$v] = 0;
			$cart_vendors[$v]++;
		}
		unset($p);

		if($vendor_zone)
			$this->manageZoneVendor($cart->products);
		if(!empty($cart_vendors[0])) {
			if(!empty(self::$zone_id)) {
				if(empty($cart_vendors[self::$vendor_id]))
					$cart_vendors[self::$vendor_id] = 0;
				$cart_vendors[self::$vendor_id] += $cart_vendors[0];
			} else {
				if(empty($cart_vendors[1]))
					$cart_vendors[1] = 0;
				$cart_vendors[1] += $cart_vendors[0];
			}
			unset($cart_vendors[0]);
		}
		ksort($cart_vendors);

		if(count($cart_vendors) == 1)
			return;
		if($limit_vendors_in_cart == 2 && count($cart_vendors) == 2 && !empty($cart_vendors[1]))
			return;

		if($limit_vendors_in_cart == 2)
			unset($cart_vendors[1]);
		sort($cart_vendors);
		$keep_vendor = array_keys($cart_vendors);
		$keep_vendor = array_shift($keep_vendor);
		$product_removed = 0;
		foreach($cart->cart_products as $k => &$cart_product) {
			if(!is_numeric($k) && substr($k, 0, 1) == 'p')
				continue;
			$v = (int)$cart->products[$k]->product_vendor_id;
			if(empty($v) && !empty(self::$zone_id))
				$v = self::$vendor_id;

			if($limit_vendors_in_cart == 2 && $v <= 1)
				continue;
			if($v == $keep_vendor)
				continue;

			$cart_product->cart_product_quantity = 0;
			$product_removed++;
		}
		unset($cart_product);

		if(empty($product_removed))
			return;

		$cartClass = hikamarket::get('shop.class.cart');
		$cartClass->addMessage($cart, array(
			'msg' => JText::_('VENDOR_RESTRICTION_PRODUCT_REMOVED'),
			'type' => 'notice'
		));
	}

	public function onAfterProductQuantityCheck(&$product, &$wantedQuantity, &$quantity, &$cartContent, &$cart_product_id_for_product, &$displayErrors) {
		if(empty($cartContent))
			return;

		$this->manageZoneVendor($cartContent);

		$limit_vendors_in_cart = $this->config->get('vendors_in_cart', 0);
		if($limit_vendors_in_cart == 0)
			return;

		$vendor_id = (int)$product->product_vendor_id;
		if($vendor_id == 0 && $product->product_type == 'variant' && !empty($product->product_parent_id))
			$vendor_id = (int)$this->getProductVendor( (int)$product->product_parent_id );
		if($vendor_id == 1)
			$vendor_id = 0;

		if($limit_vendors_in_cart == 2 && $vendor_id == 0)
			return;

		$refuse = false;
		$vendor_ids = array();
		foreach($cartContent as $p) {
			if((int)$p->cart_product_quantity == 0)
				continue;

			$v = (int)$p->product_vendor_id;
			if($v == 0 && $p->product_type == 'variant') {
				if(isset($cartContent[ (int)$p->cart_product_parent_id ]))
					$v = (int)$cartContent[ (int)$p->cart_product_parent_id ]->product_vendor_id;
				else
					$v = $this->getProductVendor( (int)$p->product_parent_id );
			}

			if($v == 1)
				$v = 0;

			if($v > 1)
				$vendor_ids[$v] = $v;
			else
				$vendor_ids[1] = 1;

			if($limit_vendors_in_cart == 2 && $v == 0)
				continue;

			if($v != $vendor_id) {
				$refuse = true;
				break;
			}
		}

		if(!$refuse)
			return;

		$quantity = 0;
		$displayErrors = false;

		$app = JFactory::getApplication();

		if($vendor_id == 0)
			$vendor_id = 1;

		$this->db->setQuery('SELECT vendor_id, vendor_name FROM ' . hikamarket::table('vendor') . ' as v WHERE v.vendor_id IN (' . $vendor_id . ',' . implode(',', $vendor_ids) . ')');
		$vendors = $this->db->loadObjectList('vendor_id');

		$wantedVendor = $vendors[$vendor_id]->vendor_name;
		$otherVendor = null;
		foreach($vendors as $v) {
			if($v->vendor_id > 1 && $v->vendor_id != $vendor_id)
				$otherVendor = $v->vendor_name;
		}
		if($limit_vendors_in_cart == 2 && $otherVendor !== null && isset($vendors[1])) {
			$app->enqueueMessage(JText::sprintf('VENDOR_CART_PRODUCT_REFUSED_2', $product->product_name, $wantedVendor, $otherVendor, $vendors[1]->vendor_name));
		} else {
			if($otherVendor === null)
				$otherVendor = $vendors[1]->vendor_name;
			$app->enqueueMessage(JText::sprintf('VENDOR_CART_PRODUCT_REFUSED', $product->product_name, $wantedVendor, $otherVendor));
		}
	}
	private function getProductVendor($product_id) {
		if(empty($product_id))
			return 0;

		static $cache = array();
		if(isset($cache[$product_id]))
			return $cache[$product_id];

		$db = JFactory::getDBO();
		$db->setQuery('SELECT product_vendor_id FROM '.hikamarket::table('shop.product').' WHERE product_id = '.(int)$product_id);
		$ret = (int)$db->loadResult();
		if($ret == 1)
			$ret = 0;
		$cache[$product_id] = $ret;
		return $cache[$product_id];
	}
}
