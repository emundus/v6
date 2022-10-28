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
class hikamarketPaymentClass extends hikamarketClass {

	protected $tables = array('shop.payment');
	protected $pkeys = array('payment_id');
	protected $toggle = array('payment_published' => 'payment_id');
	protected $toggleAcl = array('payment' => 'payment_published');
	protected $deleteToggle = array('shop.payment' => array('payment_id', 'payment_type'));

	public function save(&$element) {
		$shopClass = hikamarket::get('shop.class.payment');
		return $shopClass->save($element);
	}

	public function onPluginConfiguration(&$plugin, &$element, &$extra_config, &$extra_blocks) {
		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin())
			return;

		if(!empty($plugin->market_support)) {
			$aclType = hikamarket::get('type.joomla_acl');

			$extra_blocks[] = '
<div class="hikashop_backend_tile_edition">
<div class="hkc-xl-12 hikashop_tile_block"><div style="min-height: 150px;">
	<div class="hikashop_tile_title">' . JText::_('MAIN_INFORMATION') . '</div>
	<dl class="hika_options">
		<dt class="hikamarket_shipping_vendor_filter"><label for="data[payment][payment_params][payment_market_mode]">'.JText::_('HIKAM_MODE_COMMISSION').'</label></dt>
		<dd class="hikamarket_shipping_vendor_filter">'.JText::_('PLUGIN_COMPATIBLE_MARKET').'</dd>
		<dt class="hikamarket_vendor_name"><label for="data[payment][payment_params][payment_vendorgroup_filter]">' . JText::_('HIKAM_VENDOR_GROUP') . '</label></dt>
		<dd class="hikamarket_vendor_name input_large">'.
			$aclType->displayList('data[payment][payment_params][payment_vendorgroup_filter]', @$element->payment_params->payment_vendorgroup_filter, 'HIKA_NONE').
		'</dd>
	</dl>
</div></div>
</div>
<div style="clear:both"></div>
';
			return;
		}

		$nameboxType = hikamarket::get('type.namebox');

		$vendor_id = '';
		if(!empty($element->payment_vendor_id))
			$vendor_id = (int)$element->payment_vendor_id;
		if(empty($vendor_id) && !empty($element->payment_params->payment_vendor_id))
			$vendor_id = (int)$element->payment_params->payment_vendor_id;

		$market_modes = array(
			JHTML::_('select.option', '', JText::_('HIKA_INHERIT')),
			JHTML::_('select.option', 'fee', JText::_('MARKETMODE_FEE_DETAILED')),
			JHTML::_('select.option', 'com', JText::_('MARKETMODE_COMMISSION_DETAILED'))
		);
		$market_mode = '';
		if(!empty($element->payment_params->payment_market_mode))
			$market_mode = $element->payment_params->payment_market_mode;

		$config = hikamarket::config();
		$msg = '';
		$cart_restriction = (int)$config->get('vendors_in_cart', 0);
		if($cart_restriction != 1) {
			$msg = '
		<tr>
			<td colspan="2">'.JText::_('WARNING_VENDOR_LIMITATION_NOT_SET').'</td>
		</tr>';
		}

		$extra_blocks[] = '
<div class="hikashop_tile_block"><div style="min-height:auto;">
	<div class="hikashop_tile_title">'.JText::_('VENDOR_OPTIONS').'</div>
	<table class="admintable table">'.$msg.'
		<tr>
			<td class="key">
				<label for="data[payment][payment_vendor_id]">'.JText::_('HIKA_VENDOR').'</label>
			</td>
			<td>'.
				$nameboxType->display(
					'data[payment][payment_vendor_id]',
					(int)$vendor_id,
					hikamarketNameboxType::NAMEBOX_SINGLE,
					'vendor',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
					)
				).
			'</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][payment_market_mode]">'.JText::_('HIKAM_MODE_COMMISSION').'</label>
			</td>
			<td>'.
				JHTML::_('select.genericlist', $market_modes, 'data[payment][payment_params][payment_market_mode]', '', 'value', 'text', $market_mode).
			'</td>
		</tr>
	</table>
</div></div>';
	}

	public function onBeforePluginSave(&$element, &$do, $new = false) {

	}

	public function onPaymentDisplay(&$order, &$methods, &$usable_methods) {
		$config = hikamarket::config();
		$cart_restriction = (int)$config->get('vendors_in_cart', 0);
		$only_vendor_payments = (int)$config->get('only_vendor_payments', 0);

		$vendor_id = 0;
		if(!empty($order->products)) {
			foreach($order->products as $product) {
				if(isset($product->product_vendor_id) && (int)$product->product_vendor_id >= 1) {
					if($cart_restriction > 0)
						$vendor_id = (int)$product->product_vendor_id;
					elseif($vendor_id == 0)
						$vendor_id = (int)$product->product_vendor_id;
					else
						$vendor_id = -1;
				}
			}
		}

		if($vendor_id > 0 && $only_vendor_payments) {
			$found = false;
			if(!empty($usable_methods)) {
				foreach($usable_methods as $method) {
					if(!$this->isVendorMethod($method, $vendor_id))
						continue;
					$found = true;
					break;
				}
			}
			if(!empty($methods) && !$found) {
				foreach($methods as $method) {
					if(!$this->isVendorMethod($method, $vendor_id))
						continue;
					$found = true;
					break;
				}
			}

			if(!$found) {
				$vendor_id = -1;
				$cart_restriction = 0;
			}
		}

		if(!empty($usable_methods)) {
			foreach($usable_methods as $k => &$method) {
				if((isset($method->enabled) && $method->enabled == false) || empty($method->payment_published))
					continue;
				if(!$this->checkMethodForCheckout($method, $cart_restriction, $vendor_id, $only_vendor_payments)) {
					$method->enabled = false;
					$method->payment_published = false;
					unset($usable_methods[$k]);
				}
			}
			unset($method);
		}

		if(!empty($methods)) {
			foreach($methods as &$method) {
				if((isset($method->enabled) && $method->enabled == false) || empty($method->payment_published))
					continue;
				if(!$this->checkMethodForCheckout($method, $cart_restriction, $vendor_id, $only_vendor_payments)) {
					$method->enabled = false;
					$method->payment_published = false;
				}
			}
			unset($method);
		}
	}

	private function isVendorMethod(&$method, $cart_vendor_id) {
		if((isset($method->enabled) && $method->enabled == false) || empty($method->payment_published))
			return false;

		if(!empty($method->payment_params->market_support) || $method->payment_type == 'paypaladaptive')
			return true;

		$vendor_id = (int)@$method->payment_vendor_id;
		if(empty($vendor_id) && !empty($method->payment_params->payment_vendor_id))
			$vendor_id = (int)$method->payment_params->payment_vendor_id;

		return $vendor_id == $cart_vendor_id;
	}

	private function checkMethodForCheckout(&$method, $cart_restriction, $cart_vendor_id, $only_vendor_payments) {
		$vendor_id = (int)@$method->payment_vendor_id;
		if(empty($vendor_id) && !empty($method->payment_params->payment_vendor_id))
			$vendor_id = (int)$method->payment_params->payment_vendor_id;
		if(!empty($method->payment_params->market_support) || $method->payment_type == 'paypaladaptive') {
			if(!empty($method->payment_params->payment_vendorgroup_filter)) {
				$vendorClass = hikamarket::get('class.vendor');
				$vendor = $vendorClass->get( (int)$cart_vendor_id );
				$vendor_accesses = explode(',', $vendor->vendor_access);
				unset($vendor);
				return in_array('@'.$method->payment_params->payment_vendorgroup_filter, $vendor_accesses);
			}
			if($cart_vendor_id == $vendor_id || $vendor_id == 0 || $vendor_id == 1)
				return true;
			return false;
		}

		if($cart_restriction == 0 && $cart_vendor_id <= 1) {
			if($vendor_id > 1)
				return false;
			return true;
		}
		if($cart_vendor_id == $vendor_id || (!$only_vendor_payments && ($vendor_id == 0 || $vendor_id == 1)))
			return true;
		return false;
	}

	public function onAfterHikaPluginConfigurationListing($type, &$rows, &$listing_columns, &$view) {
		$vendors = array();
		foreach($rows as &$row) {
			if(empty($row->payment_vendor_id) && !empty($row->payment_params->payment_vendor_id))
				$row->payment_vendor_id = (int)$row->payment_params->payment_vendor_id;
			if(!empty($row->payment_vendor_id)) {
				$id = (int)$row->payment_vendor_id;
				$vendors[$id] = $id;
			}
		}
		unset($row);

		if(!empty($vendors)) {
			$listing_columns['vendor'] = array(
				'name' => 'HIKA_VENDOR',
				'col' => 'vendor_name'
			);

			$db = JFactory::getDBO();
			$query = 'SELECT * FROM ' . hikamarket::table('vendor') . ' WHERE vendor_id IN ('.implode(',', $vendors) . ')';
			$db->setQuery($query);
			$vendors = $db->loadObjectList('vendor_id');

			foreach($rows as &$row) {
				$row->vendor_name = '';
				if(!empty($row->payment_vendor_id)) {
					$id = (int)$row->payment_vendor_id;
					if(isset($vendors[ $id ]))
						$row->vendor_name = $vendors[ $id ]->vendor_name;
					else
						$row->vendor_name = $id;
				}
			}
			unset($row);
		}
	}

	public function onBeforeHikaPluginConfigurationListing($type, &$filters, &$order, &$searchMap, &$extrafilters, &$view) {

	}

	public function loadConfigurationFields() {
		$main_form = array(
			'payment_images' => array(
				'name' => 'HIKA_IMAGES',
				'type' => 'plugin_images',
				'format' => 'arrayString'
			),
			'payment_price' => array(
				'name' => 'PRICE',
				'type' => 'price',
				'format' => 'float',
				'link' => 'params.payment_currency',
				'linkformat' => 'int',
			),
			'params.payment_percentage' => array(
				'name' => 'DISCOUNT_PERCENT_AMOUNT',
				'type' => 'input',
				'format' => 'float',
				'append' => '%'
			),
			'params.payment_tax_id' => array(
				'name' => 'TAXATION_CATEGORY',
				'type' => 'tax',
				'format' => 'int'
			),
		);

		$restriction_form = array(
			'payment_currency' => array(
				'name' => 'CURRENCY',
				'type' => 'currencies',
				'format' => 'arrayInt',
				'category' => 'currency'
			),
			'params.payment_min_price' => array(
				'name' => 'SHIPPING_MIN_PRICE',
				'type' => 'input',
				'format' => 'float',
				'category' => 'price'
			),
			'params.payment_max_price' => array(
				'name' => 'SHIPPING_MAX_PRICE',
				'type' => 'input',
				'format' => 'float',
				'category' => 'price'
			),
			'params.payment_price_use_tax' => array(
				'name' => 'WITH_TAX',
				'type' => 'boolean',
				'default' => '1',
				'format' => 'boolean',
				'category' => 'price',
				'category_check' => false
			),
			'params.payment_min_quantity' => array(
				'name' => 'SHIPPING_MIN_QUANTITY',
				'type' => 'input',
				'format' => 'int',
				'category' => 'quantity'
			),
			'params.payment_max_quantity' => array(
				'name' => 'SHIPPING_MAX_QUANTITY',
				'type' => 'input',
				'format' => 'int',
				'category' => 'quantity'
			),
			'payment_zone_namekey' => array(
				'name' => 'ZONE',
				'type' => 'zone',
				'format' => 'string',
				'category' => 'zone'
			),
			'payment_shipping_methods' => array(
				'name' => 'HIKASHOP_SHIPPING_METHOD',
				'type' => 'shipping_method',
				'format' => 'arrayString',
				'category' => 'shipping'
			),
			'params.payment_min_weight' => array(
				'name' => 'SHIPPING_MIN_WEIGHT',
				'type' => 'weight',
				'format' => 'float',
				'link' => 'payment_weight_unit',
				'linkformat' => 'string',
				'category' => 'weight'
			),
			'params.payment_max_weight' => array(
				'name' => 'SHIPPING_MAX_WEIGHT',
				'type' => 'weight',
				'format' => 'float',
				'link' => 'payment_weight_unit',
				'linkformat' => 'string',
				'category' => 'weight'
			),
			'params.payment_min_volume' => array(
				'name' => 'SHIPPING_MIN_VOLUME',
				'type' => 'volume',
				'format' => 'float',
				'link' => 'payment_size_unit',
				'linkformat' => 'string',
				'category' => 'volume'
			),
			'params.payment_max_volume' => array(
				'name' => 'SHIPPING_MAX_VOLUME',
				'type' => 'volume',
				'format' => 'float',
				'link' => 'payment_size_unit',
				'linkformat' => 'string',
				'category' => 'volume'
			),
			'params.payment_zip_prefix' => array(
				'name' => 'SHIPPING_PREFIX',
				'type' => 'input',
				'format' => 'string',
				'category' => 'postcode'
			),
			'params.payment_min_zip' => array(
				'name' => 'SHIPPING_MIN_ZIP',
				'type' => 'input',
				'format' => 'string',
				'category' => 'postcode'
			),
			'params.payment_max_zip' => array(
				'name' => 'SHIPPING_MAX_ZIP',
				'type' => 'input',
				'format' => 'string',
				'category' => 'postcode'
			),
			'params.payment_zip_suffix' => array(
				'name' => 'SHIPPING_SUFFIX',
				'type' => 'input',
				'format' => 'string',
				'category' => 'postcode'
			),
		);
		if(hikashop_level(2)) {
			$restriction_form['payment_access'] = array(
				'name' => 'ACCESS_LEVEL',
				'type' => 'acl',
				'format' => 'arrayString',
				'category' => 'acl',
				'empty_value' => 'all'
			);
		}

		return array(
			'main' => $main_form,
			'restriction' => $restriction_form
		);
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$query = 'SELECT * FROM ' . hikamarket::table('shop.payment') . ' WHERE payment_published = 1';
		$this->db->setQuery($query);
		$methods = $this->db->loadObjectList('payment_id');
		foreach($methods as $method) {
			$payment_namekey = $method->payment_type . '_' . $method->payment_id;
			$ret[0][$payment_namekey] = $method->payment_name;
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
			if(!hikamarket::isAdmin() && ((int)$value == 0 || empty($this->toggle[$task]) || !hikamarket::acl('paymentplugin/edit/'.str_replace('payment_', '', $task)) || !hikamarket::isVendorPlugin((int)$value, 'payment') ))
				return false;
		}
		if(!empty($this->toggle[$task]))
			return $this->toggle[$task];
		return false;
	}

	public function toggleDelete($value1 = '', $value2 = '') {
		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin() && ((int)$value1 == 0 || !hikamarket::acl('paymentplugin/delete') || !hikamarket::isVendorPlugin((int)$value1, 'payment')))
			return false;
		if(!empty($this->deleteToggle))
			return $this->deleteToggle;
		return false;
	}
}
