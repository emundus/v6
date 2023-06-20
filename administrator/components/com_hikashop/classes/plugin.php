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
class hikashopPluginClass extends hikashopClass {
	var $tables = array('plugin');
	var $pkeys = array('plugin_id');
	var $toggle = array('plugin_published' => 'plugin_id');
	var $deleteToggle = array('plugin' => array('plugin_type', 'plugin_id'));

	function get($id, $default = '') {
		$result = parent::get($id);
		if(!empty($result->plugin_params))
			$result->plugin_params = hikashop_unserialize($result->plugin_params);
		return $result;
	}

	function save(&$element, $reorder = true) {
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$new = empty($element->payment_id);
		if($new)
			$app->triggerEvent('onBeforeHikaPluginCreate', array('plugin', &$element, &$do));
		else
			$app->triggerEvent('onBeforeHikaPluginUpdate', array('plugin', &$element, &$do));

		if(!$do)
			return false;

		if(isset($element->plugin_params) && !is_string($element->plugin_params))
			$element->plugin_params = serialize($element->plugin_params);

		if(empty($element->plugin_id))
			unset($element->plugin_id);

		$status = parent::save($element);
		if($status) {
			if($new)
				$app->triggerEvent('onAfterHikaPluginCreate', array('plugin', &$element));
			else
				$app->triggerEvent('onAfterHikaPluginUpdate', array('plugin', &$element));
			if(empty($element->plugin_id)) {
				$element->plugin_id = $status;
				if($reorder) {
					$orderHelper = hikashop_get('helper.order');
					$orderHelper->pkey = 'plugin_id';
					$orderHelper->table = 'plugin';
					$orderHelper->groupVal = $element->plugin_type;
					$orderHelper->orderingMap = 'plugin_ordering';
					$orderHelper->reOrder();
				}
			}
		}

		if($status && !empty($element->plugin_published) && !empty($element->plugin_id)) {
			$db = JFactory::getDBO();
			$query = 'SELECT plugin_type FROM ' . hikashop_table('plugin') . ' WHERE plugin_id = ' . (int)$element->plugin_id;
			$db->setQuery($query);
			$name = $db->loadResult();

			$query = 'UPDATE '.hikashop_table('extensions',false).' SET enabled = 1 WHERE enabled = 0 AND type = ' . $db->Quote('plugin') . ' AND element = ' . $db->Quote($name) . ' AND folder = ' . $db->Quote('hikashop');
			$db->setQuery($query);
			$db->execute();
		}
		return $status;
	}


	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeHikaPluginDelete', array('plugin', &$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterHikaPluginDelete', array('plugin', &$elements));
		}
		return $status;
	}

	public function fillListingColumns(&$rows, &$listing_columns, &$view, $type = null) {
		if(empty($type))
			$type = hikaInput::get()->getCmd('plugin_type', 'payment');
		if(!in_array($type, array('payment', 'shipping')))
			return false;

		$listing_columns['price'] = array(
			'name' => 'PRODUCT_PRICE',
			'col' => 'col_display_price'
		);
		$listing_columns['restriction'] = array(
			'name' => 'HIKA_RESTRICTIONS',
			'col' => 'col_display_restriction'
		);

		if(empty($rows)) return;
		$joomlaAcl = hikashop_get('type.joomla_acl');

		$currency_field = $type.'_currency';
		if($type == 'shipping')
			$currency_field = 'shipping_currency_id';

		foreach($rows as &$row) {
			if(!empty($row->{$type.'_params'}) && is_string($row->{$type.'_params'}))
				$row->plugin_params = hikashop_unserialize($row->{$type.'_params'});

			$row->col_display_price = array();
			$row->col_display_restriction = array();

			if(bccomp(sprintf('%F',$row->{$type.'_price'}), 0, 3)) {
				if($type == 'shipping')
					$row->col_display_price['fixed'] = $view->currencyClass->displayPrices(array($row), $type.'_price', $currency_field);
				else
					$row->col_display_price['fixed'] = $view->currencyClass->displayPrices(array($row), $type.'_price', array($type.'_params', $currency_field));
			}
			if(isset($row->plugin_params->{$type.'_percentage'}) && bccomp(sprintf('%F',$row->plugin_params->{$type.'_percentage'}), 0, 3)) {
				$row->col_display_price['percent'] = $row->plugin_params->{$type.'_percentage'};
			}

			if(!empty($row->plugin_params->{$type.'_min_volume'}))
				$row->col_display_restriction['min_volume'] = array('name' => 'SHIPPING_MIN_VOLUME', 'value' => $row->plugin_params->{$type.'_min_volume'} . $row->plugin_params->{$type.'_size_unit'});
			if(!empty($row->plugin_params->{$type.'_max_volume'}))
				$row->col_display_restriction['max_volume'] = array('name' => 'SHIPPING_MAX_VOLUME', 'value' => $row->plugin_params->{$type.'_max_volume'} . $row->plugin_params->{$type.'_size_unit'});

			if(!empty($row->plugin_params->{$type.'_min_weight'}))
				$row->col_display_restriction['min_weight'] = array('name' => 'SHIPPING_MIN_WEIGHT', 'value' => $row->plugin_params->{$type.'_min_weight'} . $row->plugin_params->{$type.'_weight_unit'});
			if(!empty($row->plugin_params->{$type.'_max_weight'}))
				$row->col_display_restriction['max_weight'] = array('name' => 'SHIPPING_MAX_WEIGHT', 'value' => $row->plugin_params->{$type.'_max_weight'} . $row->plugin_params->{$type.'_weight_unit'});

			if(isset($row->plugin_params->{$type.'_min_price'}) && bccomp(sprintf('%F',$row->plugin_params->{$type.'_min_price'}), 0, 5)) {
				$row->{$type.'_min_price'} = $row->plugin_params->{$type.'_min_price'};
				$row->col_display_restriction['min_price'] = array('name' => 'SHIPPING_MIN_PRICE', 'value' => $view->currencyClass->displayPrices(array($row), $type.'_min_price', $currency_field));
			}
			if(isset($row->plugin_params->{$type.'_max_price'}) && bccomp(sprintf('%F',$row->plugin_params->{$type.'_max_price'}), 0, 5)) {
				$row->{$type.'_max_price'} = $row->plugin_params->{$type.'_max_price'};
				$row->col_display_restriction['max_price'] = array('name' => 'SHIPPING_MAX_PRICE', 'value' => $view->currencyClass->displayPrices(array($row), $type.'_max_price', $currency_field));
			}
			if(!empty($row->plugin_params->{$type.'_zip_prefix'}))
				$row->col_display_restriction['zip_prefix'] = array('name' => 'SHIPPING_PREFIX', 'value' => $row->plugin_params->{$type.'_zip_prefix'});
			if(!empty($row->plugin_params->{$type.'_min_zip'}))
				$row->col_display_restriction['min_zip'] = array('name' => 'SHIPPING_MIN_ZIP', 'value' => $row->plugin_params->{$type.'_min_zip'});
			if(!empty($row->plugin_params->{$type.'_max_zip'}))
				$row->col_display_restriction['max_zip'] = array('name' => 'SHIPPING_MAX_ZIP', 'value' => $row->plugin_params->{$type.'_max_zip'});
			if(!empty($row->plugin_params->{$type.'_zip_suffix'}))
				$row->col_display_restriction['zip_suffix'] = array('name' => 'SHIPPING_SUFFIX', 'value' => $row->plugin_params->{$type.'_zip_suffix'});
			if(!empty($row->{$type.'_zone_namekey'})) {
				if($view->zoneClass)
					$view->zoneClass = hikashop_get('class.zone');
				$zone = $view->zoneClass->get($row->{$type.'_zone_namekey'});
				if(!empty($zone))
					$row->col_display_restriction['zone'] = array('name' => 'ZONE', 'value' => $zone->zone_name_english);
				else
					$row->col_display_restriction['zone'] = array('name' => 'ZONE', 'value' => 'INVALID');
			}
			if(hikashop_level(2) && !empty($row->{$type.'_access'}) && $row->{$type.'_access'} != 'all') {
				$accesses = explode(',', $row->{$type.'_access'});
				$list = array();
				if(empty($groups))
					$groups = $joomlaAcl->getList();
				foreach($accesses as $access) {
					if(empty($access))
						continue;
					foreach($groups as $group) {
						if($group->id == $access) {
							$list[$access] = $group->text;
							break;
						}
					}
				}
				if(count($list))
					$row->col_display_restriction['acl'] = array('name' => 'ACCESS_LEVEL', 'value' => implode(', ', $list));
			}
		}
		unset($row);
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		if(isset($typeConfig['params']['type']) && $typeConfig['params']['type'] == 'images') {
			$image_type = @$options['type'];
			if(!in_array($image_type, array('shipping', 'payment')))
				return $ret;

			$path = HIKASHOP_MEDIA.'images'.DS.$image_type.DS;
			jimport('joomla.filesystem.folder');
			$images = JFolder::files($path);
			$rows = array();
			foreach($images as $image){
				$parts = explode('.',$image);
				$row = new stdClass();
				$row->ext = array_pop($parts);
				if(!in_array(strtolower($row->ext), array('gif','png','jpg','jpeg','svg')))
					continue;
				$row->id = implode($parts);
				$row->image_name = str_replace('_', ' ', $row->id);
				$row->image_file = $image;
				$row->image_url = '<img src="'.HIKASHOP_IMAGES .$image_type.'/'. $row->image_file.'" />';
				$rows[$row->id] = $row;
			}

			if(!empty($value)) {
				if(is_string($value))
					$value = explode(',', $value);

				foreach($value as $v) {
					if(isset($rows[$v]))
						$ret[1][$v] = $rows[$v];
				}
			}

			if(!empty($rows))
				$ret[0] = $rows;
		}

		return $ret;
	}
}
