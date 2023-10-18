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
class hikashopWarehouseClass extends hikashopClass {
	var $tables = array('warehouse');
	var $pkeys = array('warehouse_id');
	var $toggle = array('warehouse_published'=>'warehouse_id');

	function saveForm() {
		$element = new stdClass();
		$element->warehouse_id = hikashop_getCID('warehouse_id');
		$formData = hikaInput::get()->get('data', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		foreach($formData['warehouse'] as $column => $value) {
			hikashop_secureField($column);
			$element->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
		}
		$translationHelper = hikashop_get('helper.translation');
		$translationHelper->getTranslations($element);
		$status = $this->save($element);

		return $status;
	}

	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeWarehouseDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterWarehouseDelete', array(&$elements));
		}
		return $status;
	}
	function save(&$element) {
		$isNew = empty($element->warehouse_id);
		$element->warehouse_modified=time();
		if($isNew) {
			$element->warehouse_created=$element->warehouse_modified;
			$orderHelper = hikashop_get('helper.order');
			$orderHelper->pkey = 'warehouse_id';
			$orderHelper->table = 'warehouse';
			$orderHelper->orderingMap = 'warehouse_ordering';
			$orderHelper->reOrder();
		}

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		if($isNew) {
			$app->triggerEvent('onBeforeWarehouseCreate', array( &$element, &$do ));
		} else {
			$app->triggerEvent('onBeforeWarehouseUpdate', array( &$element, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return $status;

		if($isNew) {
			$app->triggerEvent('onAfterWarehouseCreate', array( &$element ));
		} else {
			$app->triggerEvent('onAfterWarehouseUpdate', array( &$element ));
		}
		return $status;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {

		$ret = array(
			0 => array(),
			1 => array()
		);

		$db = JFactory::getDBO();

		$start = (int)@$typeConfig['start'];
		if(!empty($options['start']))
			$start = (int)@$options['start'];

		$limit = (int)@$typeConfig['limit'];
		if(!empty($options['limit']))
			$limit = (int)$options['limit'];
		if(empty($limit))
			$limit = 30;

		if(!empty($search)) {
			$searchStr = "'%" . ((HIKASHOP_J30) ? $db->escape($search, true) : $db->getEscaped($search, true) ) . "%'";
			$query = 'SELECT warehouse_id, warehouse_name '.
				' FROM ' . hikashop_table('warehouse') .
				' WHERE warehouse_published = 1 AND warehouse_name LIKE ' . $searchStr .
				' ORDER BY warehouse_name';
		} else {
			$query = 'SELECT warehouse_id, warehouse_name '.
				' FROM ' . hikashop_table('warehouse') .
				' WHERE warehouse_published = 1 '.
				' ORDER BY warehouse_name';
		}

		$db->setQuery($query, $start, $limit);
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
				hikashop_toInteger($value);

				$query = 'SELECT warehouse_id, warehouse_name '.
					' FROM ' . hikashop_table('warehouse') .
					' WHERE warehouse_id IN ('.implode(',', $value).') '.
					' ORDER BY warehouse_name';
				$db->setQuery($query);
				$ret[1] = $db->loadObjectList('warehouse_id');
			}
		}

		return $ret;
	}
}
