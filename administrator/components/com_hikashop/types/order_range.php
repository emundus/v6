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
class hikashopOrder_rangeType {
	public function __construct() {
		$this->values = array();
	}

	protected function load() {

		$this->values = array(
			'last 30 days' => JHTML::_('select.option', 'last 30 days', JText::_('IN_LAST_30_DAYS')),
			'last 6 months' => JHTML::_('select.option', 'last 6 months', JText::_('IN_LAST_6_MONTHS')),
		);
		$current_year = date("Y");
		$config = hikashop_config();
		$numberOfYears = (int)$config->get('order_range_filter_years', 6);
		for($i = 0 ; $i < $numberOfYears ; $i++) {
			$this->values[$current_year-$i] = JHTML::_('select.option', $current_year-$i, JText::sprintf('IN_YEAR', $current_year-$i));
		}
	}

	public function display($map, $value, $extra = '', $addAll = false) {
		if(empty($this->values))
			$this->load();

		if($addAll) {
			$values = array_merge(
				array(JHTML::_('select.option', '', JText::_('IN_TOTAL'))),
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
}
