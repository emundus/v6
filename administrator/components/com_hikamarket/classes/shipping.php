<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketShippingClass extends hikamarketClass {

	protected $tables = array('shop.shipping');
	protected $pkeys = array('shipping_id');
	protected $toggle = array('shipping_published' => 'shipping_id');
	protected $toggleAcl = array('shipping' => 'shipping_published');
	protected $deleteToggle = array('shop.shipping' => array('shipping_id', 'shipping_type'));

	public function save(&$element) {
		$shopClass = hikamarket::get('shop.class.shipping');
		return $shopClass->save($element);
	}

	public function onShippingWarehouseFilter(&$shipping_groups, &$order, &$rates) {
		$config = hikamarket::config();
		if(!$config->get('shipping_per_vendor', 1))
			return;

		$orderClass = hikamarket::get('class.order');
		$assignedProducts = $orderClass->getProductVendorAttribution($order);

		$singlegroup = (count($shipping_groups) == 1);

		$new_groups = array();
		$vendors = array();
		$moveGroups = array();

		foreach($shipping_groups as $group_id => $shipping_group) {
			foreach($shipping_group->products as $k => $product) {
				$vendor_id = (int)$product->product_vendor_id;
				if(isset($assignedProducts[(int)$product->cart_product_id]) && !empty($assignedProducts[(int)$product->cart_product_id]['vendor']))
					$vendor_id = (int)$assignedProducts[(int)$product->cart_product_id]['vendor'];
				if($vendor_id > 1) {
					$key = $group_id . ';' . $vendor_id;
					if(!isset($new_groups[$key])) {
						$new_groups[$key] = new stdClass();
						$new_groups[$key]->products = array();
						$new_groups[$key]->shippings = array();
					}
					$new_groups[$key]->products[] = $product;
					$vendors[] = $vendor_id;
					unset($shipping_group->products[$k]);
				}
			}

			if(!empty($shipping_group->products))
				$moveGroups[] = $group_id;
		}

		foreach($moveGroups as $group_id) {
			$move_shipping_group =& $shipping_groups[$group_id];
			unset($shipping_groups[$group_id]);
			$shipping_groups[$group_id.'v1'] =& $move_shipping_group;
			unset($move_shipping_group);
		}

		if(!empty($new_groups)) {
			$query = 'SELECT vendor_id, vendor_name, vendor_access FROM '.hikamarket::table('vendor').' WHERE vendor_id in (' . implode(',', $vendors).')';
			$this->db->setQuery($query);
			$vendorNames = $this->db->loadObjectList('vendor_id');
			foreach($new_groups as $key => $new_group) {
				list($group_id, $vendor_id) = explode(';', $key, 2);
				$vendor_id = (int)$vendor_id;

				$vendor_access = explode(',', $vendorNames[$vendor_id]->vendor_access);
				foreach($vendor_access as $k => &$v) {
					if(substr($v, 0, 1) != '@') {
						unset($vendor_access[$k]);
						continue;
					}
					$v = (int)substr($v, 1);
				}
				unset($v);

				$new_group->name = JText::sprintf('SOLD_BY_VENDOR', $vendorNames[$vendor_id]->vendor_name);
				$new_group->vendor_id = $vendor_id;
				$new_group->vendor_groups = $vendor_access;

				$shipping_groups[$group_id.'v'.$vendor_id] = $new_group;
			}

			if($singlegroup) {
				$vendorClass = hikamarket::get('class.vendor');
				$mainVendor = $vendorClass->get(1);
				if(isset($shipping_groups['0v1']))
					$shipping_groups['0v1']->name = JText::sprintf('SOLD_BY_VENDOR', $mainVendor->vendor_name);
				else {
					$id = array_keys($shipping_groups);
					$id = reset($id);
					$shipping_groups[$id]->name = JText::sprintf('SOLD_BY_VENDOR', $mainVendor->vendor_name);
				}
			}
		}
	}

	public function onShippingDisplay(&$order, &$rates, &$usable_rates, &$errors) {
		$warehouse_key = null;
		if(isset($order->shipping_warehouse_id)) {
			$warehouse_key = $order->shipping_warehouse_id;
		} else {
			$keys = array_keys($order->shipping_groups);
			$warehouse_key = reset($keys);
			unset($keys);
		}

		if(strpos($warehouse_key, 'v') === false)
			return;

		$vendor_id = (int)substr($warehouse_key, strpos($warehouse_key, 'v') + 1);
		$shipping_group = $order->shipping_groups[$warehouse_key];

		if(!empty($rates)) {
			foreach($rates as $k => &$rate) {
				if(isset($rate->shipping_published) && $rate->shipping_published == false)
					continue;

				if(!$this->checkRate($rate, $vendor_id, $warehouse_key, $shipping_group)) {
					$rate->shipping_published = false;
					unset($rates[$k]);
				}
			}
			unset($rate);
		}

		if(!empty($usable_rates)) {
			foreach($usable_rates as $k => &$rate) {
				if(isset($rate->shipping_published) && $rate->shipping_published == false)
					continue;

				if(!$this->checkRate($rate, $vendor_id, $warehouse_key, $shipping_group)) {
					$rate->shipping_published = false;
					unset($usable_rates[$k]);
				}
			}
			unset($rate);
		}
	}

	private function checkRate(&$rate, $vendor_id, $warehouse_key, $shipping_group) {
		if(empty($rate->shipping_params->shipping_vendorgroup_filter))
			return true;

		$groups = explode(',', $rate->shipping_params->shipping_vendorgroup_filter);
		hikamarket::toInteger($groups);

		if(empty($shipping_group->vendor_groups))
			return false;

		$intersect = array_intersect($shipping_group->vendor_groups, $groups);
		if(empty($intersect) && !in_array(0, $groups))
			return false;
		return true;
	}

	public function onPluginConfiguration(&$plugin, &$element, &$extra_config, &$extra_blocks) {
		$app = JFactory::getApplication();
		$current_vendor_id = 0;
		$vendor_id = '';
		$vendor_groups = '';

		if(!hikamarket::isAdmin())
			$current_vendor_id = hikamarket::loadVendor(false);

		if(!empty($element->shipping_params->shipping_warehouse_filter)) {
			if(strpos($element->shipping_params->shipping_warehouse_filter, 'v') !== false) {
				list($data, $vendor_id) = explode('v', $element->shipping_params->shipping_warehouse_filter, 2);
				if(substr($vendor_id, 0, 1) == '@') {
					$vendor_groups = substr($vendor_id, 1);
				} else {
					$vendor_id = (int)$vendor_id;
				}

				if($vendor_id === 0)
					$vendor_id = '';
				$element->shipping_params->shipping_warehouse_filter = $data;
			}
		}

		if($current_vendor_id > 1)
			return;

		if(empty($vendor_id) && isset($element->shipping_vendor_id))
			$vendor_id = (int)$element->shipping_vendor_id;

		$nameboxType = hikamarket::get('type.namebox');
		$aclType = hikamarket::get('type.joomla_acl');

		$acl_min = !empty($vendorgroups);

		$extra_blocks[] = '
<div class="hikashop_backend_tile_edition">
<div class="hkc-xl-12 hikashop_tile_block"><div style="min-height: 150px;">
	<div class="hikashop_tile_title">' . JText::_('MAIN_INFORMATION') . '</div>
	<dl class="hika_options">
		<dt class="hikamarket_shipping_vendor_filter"><label for="data[vendor][vendor_name]">' . JText::_('HIKA_VENDOR') . '</label></dt>
		<dd class="hikamarket_shipping_vendor_filter">'.
			$nameboxType->display(
				'data[shipping][shipping_params][shipping_vendor_filter]',
				(int)$vendor_id,
				hikamarketNameboxType::NAMEBOX_SINGLE,
				'vendor',
				array(
					'delete' => true,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
				)
			).
		'</dd>
		<dt class="hikamarket_vendor_name"><label for="data[shipping][shipping_params][shipping_vendorgroup_filter]">' . JText::_('HIKAM_VENDOR_GROUP') . '</label></dt>
		<dd class="hikamarket_vendor_name input_large">'.
			$aclType->displayList('data[shipping][shipping_params][shipping_vendorgroup_filter]', @$element->shipping_params->shipping_vendorgroup_filter, 'HIKA_NONE').
		'</dd>
	</dl>
</div></div>
</div>
<div style="clear:both"></div>
';
	}

	public function onBeforePluginSave(&$element, &$do, $new = false) {
		$app = JFactory::getApplication();
		if(hikamarket::isAdmin()) {
			if(isset($element->shipping_params->shipping_vendor_filter))
				$element->shipping_vendor_id = (int)$element->shipping_params->shipping_vendor_filter;

		} else {
			if(isset($element->shipping_params) && isset($element->shipping_vendor_id) && (!isset($element->shipping_params->shipping_vendor_filter) || (int)$element->shipping_vendor_id > 1))
				$element->shipping_params->shipping_vendor_filter = (int)$element->shipping_vendor_id;
		}

		if(empty($element->shipping_params->shipping_vendor_filter) && empty($element->shipping_params->shipping_vendorgroup_filter))
			return;

		if(!empty($element->shipping_params->shipping_vendor_filter) && !empty($element->shipping_params->shipping_vendorgroup_filter)) {
			$app->enqueueMessage('Please do not assign a vendor and a vendor group in the same time', 'error');
			$do = false;
			return;
		}

		if(!empty($element->shipping_params->shipping_vendor_filter)) {
			$shipping_vendor_filter = (int)$element->shipping_params->shipping_vendor_filter;

			if(!empty($element->shipping_params->shipping_warehouse_filter))
				$element->shipping_params->shipping_warehouse_filter .= 'v' . $shipping_vendor_filter;
			else
				$element->shipping_params->shipping_warehouse_filter = '0v' . $shipping_vendor_filter;
		}
	}

	public function loadConfigurationFields() {
		$main_form = array(
			'shipping_price' => array(
				'name' => 'PRICE',
				'type' => 'price',
				'format' => 'float',
				'link' => 'shipping_currency_id',
				'data' => 'shipping_currency_id',
				'linkformat' => 'int',
			),
			'params.shipping_percentage' => array(
				'name' => 'DISCOUNT_PERCENT_AMOUNT',
				'type' => 'input',
				'format' => 'float',
				'append' => '%'
			),
			'params.shipping_tax' => array(
				'name' => 'AUTOMATIC_TAXES',
				'type' => 'boolean',
				'format' => 'boolean',
				'default' => '0'
			),
			'shipping_tax_id' => array(
				'name' => 'TAXATION_CATEGORY',
				'type' => 'tax',
				'format' => 'int',
				'display' => array(
					'params.shipping_tax' => array(null, 0)
				)
			),
			'params.shipping_per_product' => array(
				'name' => 'USE_PRICE_PER_PRODUCT',
				'type' => 'boolean',
				'format' => 'boolean',
				'default' => '0'
			),
			'params.shipping_price_per_product' => array(
				'name' => 'PRICE_PER_PRODUCT',
				'type' => 'input',
				'format' => 'float',
				'display' => array(
					'params.shipping_per_product' => 1
				)
			),
			'params.shipping_override_address' => array(
				'name' => 'OVERRIDE_SHIPPING_ADDRESS',
				'type' => 'list',
				'format' => 'int',
				'data' => array(
					0 => 'HIKASHOP_NO',
					1 => 'STORE_ADDRESS',
					2 => 'HIKA_HIDE',
					3 => 'TEXT_VERSION',
					4 => 'HTML_VERSION'
				)
			),
			'params.shipping_override_address_text' => array(
				'name' => 'OVERRIDE_SHIPPING_ADDRESS_TEXT',
				'type' => 'textarea',
				'format' => 'string',
				'display' => array(
					'params.shipping_override_address' => array(3, 4)
				)
			),
			'params.override_tax_zone' => array(
				'name' => 'OVERRIDE_TAX_ZONE',
				'type' => 'zone',
				'format' => 'string'
			)
		);

		$restriction_form = array(
			'shipping_zone_namekey' => array(
				'name' => 'ZONE',
				'type' => 'zone',
				'format' => 'string',
				'category' => 'zone'
			),
			'shipping_currency' => array(
				'name' => 'CURRENCY',
				'type' => 'currencies',
				'format' => 'arrayInt',
				'category' => 'currency'
			),
			'params.shipping_warehouse_filter' => array(
				'name' => 'WAREHOUSE',
				'type' => 'warehouse',
				'format' => 'string',
				'category' => 'warehouse'
			),
			'params.shipping_min_price' => array(
				'name' => 'SHIPPING_MIN_PRICE',
				'type' => 'input',
				'format' => 'float',
				'category' => 'price'
			),
			'params.shipping_max_price' => array(
				'name' => 'SHIPPING_MAX_PRICE',
				'type' => 'input',
				'format' => 'float',
				'category' => 'price'
			),
			'params.shipping_virtual_included' => array(
				'name' => 'INCLUDE_VIRTUAL_PRODUCTS_PRICE',
				'type' => 'boolean',
				'format' => 'boolean',
				'default' => '0',
				'category' => 'price',
				'category_check' => false
			),
			'params.shipping_price_use_tax' => array(
				'name' => 'WITH_TAX',
				'type' => 'boolean',
				'format' => 'boolean',
				'default' => '1',
				'category' => 'price',
				'category_check' => false
			),
			'params.shipping_min_quantity' => array(
				'name' => 'SHIPPING_MIN_QUANTITY',
				'type' => 'input',
				'format' => 'int',
				'category' => 'quantity'
			),
			'params.shipping_max_quantity' => array(
				'name' => 'SHIPPING_MAX_QUANTITY',
				'type' => 'input',
				'format' => 'int',
				'category' => 'quantity'
			),
			'params.shipping_min_weight' => array(
				'name' => 'SHIPPING_MIN_WEIGHT',
				'type' => 'weight',
				'format' => 'float',
				'link' => 'shipping_weight_unit',
				'linkformat' => 'string',
				'category' => 'weight'
			),
			'params.shipping_max_weight' => array(
				'name' => 'SHIPPING_MAX_WEIGHT',
				'type' => 'weight',
				'format' => 'float',
				'link' => 'shipping_weight_unit',
				'linkformat' => 'string',
				'category' => 'weight'
			),
			'params.shipping_min_volume' => array(
				'name' => 'SHIPPING_MIN_VOLUME',
				'type' => 'volume',
				'format' => 'float',
				'link' => 'shipping_size_unit',
				'linkformat' => 'string',
				'category' => 'volume'
			),
			'params.shipping_max_volume' => array(
				'name' => 'SHIPPING_MAX_VOLUME',
				'type' => 'volume',
				'format' => 'float',
				'link' => 'shipping_size_unit',
				'linkformat' => 'string',
				'category' => 'volume'
			),
			'params.shipping_zip_prefix' => array(
				'name' => 'SHIPPING_PREFIX',
				'type' => 'input',
				'format' => 'string',
				'category' => 'postcode'
			),
			'params.shipping_min_zip' => array(
				'name' => 'SHIPPING_MIN_ZIP',
				'type' => 'input',
				'format' => 'string',
				'category' => 'postcode'
			),
			'params.shipping_max_zip' => array(
				'name' => 'SHIPPING_MAX_ZIP',
				'type' => 'input',
				'format' => 'string',
				'category' => 'postcode'
			),
			'params.shipping_zip_suffix' => array(
				'name' => 'SHIPPING_SUFFIX',
				'type' => 'input',
				'format' => 'string',
				'category' => 'postcode'
			),
		);
		if(hikashop_level(2)) {
			$restriction_form['shipping_access'] = array(
				'name' => 'ACCESS_LEVEL',
				'type' => 'acl',
				'format' => 'acl',
				'category' => 'acl',
				'empty_value' => 'all'
			);
		}

		return array(
			'main' => $main_form,
			'restriction' => $restriction_form
		);
	}

	public function onBeforeHikaPluginConfigurationListing($type, &$filters, &$order, &$searchMap, &$extrafilters, &$view) {

	}

	public function onAfterHikaPluginConfigurationListing($type, &$rows, &$listing_columns, &$view) {
		$vendors = array();
		$groups = array();
		foreach($rows as &$row) {
			if(is_string($row->shipping_params))
				$row->shipping_params = hikamarket::unserialize($row->shipping_params);

			if(empty($row->shipping_vendor_id) && !empty($row->shipping_params->shipping_vendor_id))
				$row->shipping_vendor_id = (int)$row->shipping_params->shipping_vendor_id;
			if(empty($row->shipping_vendor_group) && !empty($row->shipping_params->shipping_vendorgroup_filter))
				$row->shipping_vendor_group = (int)$row->shipping_params->shipping_vendorgroup_filter;
			if(!empty($row->shipping_vendor_id)) {
				$id = (int)$row->shipping_vendor_id;
				$vendors[$id] = $id;
			}
			if(!empty($row->shipping_vendor_group)) {
				$id = (int)$row->shipping_vendor_group;
				$groups[$id] = $id;
			}
		}
		unset($row);

		if(empty($vendors) && empty($groups))
			return;

		$listing_columns['vendor'] = array(
			'name' => 'HIKA_VENDOR',
			'col' => 'vendor_name'
		);

		if(!empty($vendors)) {
			$db = JFactory::getDBO();
			$query = 'SELECT * FROM ' . hikamarket::table('vendor') . ' WHERE vendor_id IN ('.implode(',', $vendors) . ')';
			$db->setQuery($query);
			$vendors = $db->loadObjectList('vendor_id');
		}

		if(!empty($groups)) {
			$joomlaAclType = hikamarket::get('type.joomla_acl');
			$groupList = $joomlaAclType->getList();
			foreach($groupList as $g) {
				if(!isset($groups[$g->id]))
					continue;
				$groups[$g->id] = $g->text;
			}
			unset($groupList);
		}

		foreach($rows as &$row) {
			$row->vendor_name = '';
			if(!empty($row->shipping_vendor_id)) {
				$id = (int)$row->shipping_vendor_id;
				if(isset($vendors[ $id ]))
					$row->vendor_name = $vendors[ $id ]->vendor_name;
				else
					$row->vendor_name = $id;
			}
			if(!empty($row->shipping_vendor_group)) {
				if(!empty($row->vendor_name))
					$row->vendor_name .= '<br/>';

				$id = (int)$row->shipping_vendor_group;
				if(isset($groups[ $id ]))
					$row->vendor_name .= '@'.$groups[ $id ];
				else
					$row->vendor_name .= '@'.$id;
			}
		}
		unset($row);
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$query = 'SELECT * FROM ' . hikamarket::table('shop.shipping') . ' WHERE shipping_published = 1';
		$this->db->setQuery($query);
		$methods = $this->db->loadObjectList('shipping_id');
		foreach($methods as $method) {
			$plugin = null;
			if($method->shipping_type != 'manual')
				$plugin = hikamarket::import('hikashopshipping', $method->shipping_type);

			if(!empty($plugin) && method_exists($plugin, 'shippingMethods')) {
				if(is_string($method->shipping_params) && !empty($method->shipping_params))
					$method->shipping_params = hikamarket::unserialize($method->shipping_params);
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
			if($mode == hikamarketNameboxType::NAMEBOX_SINGLE) {
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

	public function toggleId($task, $value = null) {
		if($value !== null) {
			$app = JFactory::getApplication();
			if(!hikamarket::isAdmin() && ((int)$value == 0 || empty($this->toggle[$task]) || !hikamarket::acl('shippingplugin/edit/'.str_replace('shipping_', '', $task)) || !hikamarket::isVendorPlugin((int)$value, 'shipping') ))
				return false;
		}
		if(!empty($this->toggle[$task]))
			return $this->toggle[$task];
		return false;
	}

	public function toggleDelete($value1 = '', $value2 = '') {
		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin() && ((int)$value1 == 0 || !hikamarket::acl('shippingplugin/delete') || !hikamarket::isVendorPlugin((int)$value1, 'shipping')))
			return false;
		if(!empty($this->deleteToggle))
			return $this->deleteToggle;
		return false;
	}
}
