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
class hikamarketWarehouseClass extends hikamarketClass {

	protected $tables = array('shop.warehouse');
	protected $pkeys = array('warehouse_id');

	protected $toggle = array('warehouse_published' => 'warehouse_id');
	protected $toggleAcl = array('warehouse_published' => 'warehouse_edit_published');
	protected $deleteToggle = array('shop.warehouse' => 'warehouse_id');

	public function get($key, $default = null) {
		static $cachedElements = array();
		if($key === 'reset_cache') {
			$cachedElements = array();
			return null;
		}

		if(!isset($cachedElements[0])) {
			$o = new stdClass();
			$o->warehouse_id = 0;
			$o->warehouse_name = '';
			$o->warehouse_published = 1;
			$cachedElements[0] = $o;
		}

		$shipping_group_struct = array();
		if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $key, $keys)) {
			hikamarket::toInteger($keys[2]);
			$shipping_group_struct = array_combine($keys[1], $keys[2]);
		}

		$cache_key = (int)$key;
		if(!empty($shipping_group_struct)) {
			$cache_key = $shipping_group_struct[''];
			unset($shipping_group_struct['']);
		}

		if(!isset($cachedElements[$cache_key])) {
			$cachedElements[$cache_key] = parent::get($cache_key, $default);
		}
		$ret = clone($cachedElements[$cache_key]);
		$ret->struct = $shipping_group_struct;

		if(!empty($shipping_group_struct) && isset($shipping_group_struct['v'])) {
			$vendorClass = hikamarket::get('class.vendor');
			$shipping_group_struct['v'] = (int)$shipping_group_struct['v'];
			if(empty($shipping_group_struct['v']))
				$shipping_group_struct['v'] = 1;
			$vendor = $vendorClass->get($shipping_group_struct['v']);

			$ret->name = JText::sprintf('SOLD_BY_VENDOR', $vendor->vendor_name);
			if(!empty($ret->warehouse_name))
				$ret->name .= ' (' . $ret->warehouse_name . ')';

			unset($shipping_group_struct['v']);
		}
		if(!empty($shipping_group_struct)) {
			$app = JFactory::getApplication();
			JPluginHelper::importPlugin('hikashop');
			JPluginHelper::importPlugin('hikashopshipping');

			$ret->name = $ret->warehouse_name;
			$app->triggerEvent('onVirtualWarehouseGet', array(&$ret, $shipping_group_struct));
		}

		return $ret;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {

		$ret = array(
			0 => array(),
			1 => array()
		);

		$db = JFactory::getDBO();

		$limit = (int)@$typeConfig['limit'];
		if(!empty($options['limit']))
			$limit = (int)$options['limit'];
		if(empty($limit))
			$limit = 30;

		if(!empty($search)) {
			$searchStr = "'%" . ((HIKASHOP_J30) ? $db->escape($search, true) : $db->getEscaped($search, true) ) . "%'";
			$query = 'SELECT warehouse_id, warehouse_name '.
				' FROM ' . hikamarket::table('shop.warehouse') .
				' WHERE warehouse_published = 1 AND warehouse_name LIKE ' . $searchStr .
				' ORDER BY warehouse_name';
		} else {
			$query = 'SELECT warehouse_id, warehouse_name '.
				' FROM ' . hikamarket::table('shop.warehouse') .
				' WHERE warehouse_published = 1 '.
				' ORDER BY warehouse_name';
		}

		$db->setQuery($query, 0, $limit);
		$warehouses = $db->loadObjectList('warehouse_id');
		foreach($warehouses as $warehouse) {
			$ret[0][$warehouse->warehouse_id] = $warehouse;
		}

		if(count($warehouses) == $limit)
			$fullLoad = false;

		if(!empty($value)) {
			if(!is_array($value))
				$value = array($value);

			if($fullLoad) {
				foreach($value as $v) {
					if(isset($ret[0][(int)$v]))
						$ret[1][(int)$v] = $ret[0][(int)$v];
				}
			} else {
				hikamarket::toInteger($value);

				$query = 'SELECT warehouse_id, warehouse_name '.
					' FROM ' . hikamarket::table('shop.warehouse') .
					' WHERE warehouse_id IN ('.implode(',', $value).') '.
					' ORDER BY warehouse_name';
				$db->setQuery($query);
				$ret[1] = $db->loadObjectList('warehouse_id');
			}
		}

		return $ret;
	}

	public function getTreeList($serialized = false, $display = '', $limit = 20) {
		$query = 'SELECT * FROM '.hikamarket::table('shop.warehouse');

		if($limit > 0)
			$this->db->setQuery($query, 0, $limit);
		else
			$this->db->setQuery($query);

		$warehouses = $this->db->loadObjectList();

		if(!$serialized)
			return $warehouses;

		$elements = array();
		foreach($warehouses as $element) {
			$obj = new stdClass();
			$obj->status = 0;
			$obj->name = $element->warehouse_name;
			$obj->value = $element->warehouse_id;

			$elements[] =& $obj;
			unset($obj);
		}
		return $elements;
	}

	public function findTreeList($search = '', $serialized = false, $display = '', $limit = 20) {
		if(HIKASHOP_J30)
			$searchStr = "'%" . $this->db->escape($search, true) . "%'";
		else
			$searchStr = "'%" . $this->db->getEscaped($search, true) . "%'";

		$query = 'SELECT * FROM '.hikamarket::table('shop.warehouse').' WHERE warehouse_name LIKE '.$search.'';

		if($limit > 0)
			$this->db->setQuery($query, 0, $limit);
		else
			$this->db->setQuery($query);
		$warehouses = $this->db->loadObjectList();

		if(!$serialized)
			return $warehouses;

		$elements = array();
		foreach($warehouses as $element) {
			$obj = new stdClass();
			$obj->status = 0;
			$obj->name = $element->warehouse_name;
			$obj->value = $element->warehouse_id;

			$elements[] =& $obj;
			unset($obj);
		}
		return $elements;
	}
}
