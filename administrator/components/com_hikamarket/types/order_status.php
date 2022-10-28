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
class hikamarketOrder_statusType {
	protected $values;

	public function __construct() {
		$this->values = array();
	}

	protected function load() {
		$categoryClass = hikamarket::get('shop.class.category');
		$filters = array();
		$rows = $categoryClass->loadAllWithTrans('status', false, $filters);
		foreach($rows as $row) {
			if(!empty($row->translation)) {
				$this->values[$row->category_name] = JHTML::_('select.option', $row->category_name, hikamarket::orderStatus($row->translation));
			} else {
				$this->values[$row->category_name] = JHTML::_('select.option', $row->category_name, hikamarket::orderStatus($row->category_name));
			}
		}
	}

	public function display($map, $value, $extra = '', $addAll = false, $filters = array()) {
		if(empty($this->values))
			$this->load();
		if($addAll) {
			$values = array_merge(
				array(JHTML::_('select.option', '', JText::_('ALL_STATUSES'))),
				$this->values
			);
		} else {
			$values = $this->values;
		}

		if(!empty($filters)) {
			if(is_string($filters))
				$filters = explode(',', $filters);

			foreach($values as $k => $v) {
				if(!in_array($k, $filters))
					unset($values[$k]);
			}
		}

		return JHTML::_('select.genericlist', $values, $map, $extra, 'value', 'text', $value);
	}
}
