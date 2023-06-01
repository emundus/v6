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
class hikashopOrder_statusType {
	public function __construct() {
		$this->values = array();
		$this->reverse = array();
	}

	protected function load() {
		$orderstatusClass = hikashop_get('class.orderstatus');

		$filters = array();
		$options = array();
		$rows = $orderstatusClass->getList($filters, $options);
		foreach($rows as $row) {
			$name = hikashop_orderStatus($row->orderstatus_namekey);
			if($name == $row->orderstatus_namekey)
				$name = $row->orderstatus_name;
			$this->values[$row->orderstatus_namekey] = JHTML::_('select.option', $row->orderstatus_namekey, $name);
			$this->reverse[$row->orderstatus_name] = $row->orderstatus_namekey;
		}
	}

	public function display($map, $value, $extra = '', $addAll = false) {
		if(empty($this->values))
			$this->load();

		if(is_string($value) && !isset($this->values[$value]) && isset($this->reverse[$value]))
			$value = $this->reverse[$value];

		if($addAll) {
			if(empty($value))
				$value = 'all';
			$values = array_merge(
				array(JHTML::_('select.option', 'all', JText::_('ALL_STATUSES'))),
				$this->values
			);
		} else {
			$values = $this->values;
		}
		if(empty($extra))
			$extra = 'class="custom-select"';
		return JHTML::_('select.genericlist', $values, $map, $extra, 'value', 'text', $value);
	}

	public function displayFilter($key, $filterValues, $extra = '', $addAll = true) {
		return $this->display('filter_'.$key, @$filterValues->$key, $extra.' class="custom-select" onchange="this.form.submit();"', $addAll);
	}

	public function displayMultiple($map, $values, $delete = true) {
		if(empty($this->nameboxType))
			$this->nameboxType = hikashop_get('type.namebox');

		$first_element = reset($values);
		if(is_object($first_element))
			$values = array_keys($values);

		return $this->nameboxType->display(
			$map,
			$values,
			hikashopNameboxType::NAMEBOX_MULTIPLE,
			'order_status',
			array(
				'delete' => $delete,
				'sort' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
	}
}
