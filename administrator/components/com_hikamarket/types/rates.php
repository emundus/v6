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
class hikamarketRatesType {
	protected $values = array();

	private function load() {
		$this->values = array(
			'none' => JHTML::_('select.option', '', JText::_('HIKA_NONE'))
		);
		$this->values['none']->rate = 0.0;
		$this->values['none']->extra = 'data-rate="0.0"';

		$query = 'SELECT * FROM ' . hikamarket::table('shop.tax');
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$this->results = $db->loadObjectList();

		foreach($this->results as $result) {
			$this->values[$result->tax_namekey] = JHTML::_('select.option', $result->tax_namekey, $result->tax_namekey . ' (' . round($result->tax_rate * 100.0, 2) . '%)');
			$this->values[$result->tax_namekey]->rate = (float)hikamarket::toFloat($result->tax_rate);
			$this->values[$result->tax_namekey]->extra = 'data-rate="'.$this->values[$result->tax_namekey]->rate.'"';
		}
	}

	public function display($map, $value, $rate = 0.0, $options = '', $id = false) {
		if(empty($this->values))
			$this->load();

		$values = $this->values;
		$rate = (float)hikamarket::toFloat($rate);

		if($rate != 0.0 || (!empty($value) && !isset($values[$value]))) {
			$f = 0;
			foreach($values as $k => $v) {
				if($v->rate == $rate || ($v->rate - $rate <= 0.00001))
					$f++;
			}

			if(empty($f) || ($rate > 0.0 && empty($value) && $f > 1) || (!empty($value) && !isset($values[$value]) && $rate > 0.0)) {
				$values[':'.$rate] = JHTML::_('select.option', ':'.$rate, JText::_('CUSTOM_RATE').' - '.round($rate * 100.0, 2).'%');
				$values[':'.$rate]->rate = $rate;
				$values[':'.$rate]->extra = 'data-rate="'.$values[':'.$rate]->rate.'"';

				if(empty($value))
					$value = ':'.$rate;

			} else if($rate > 0.0 && empty($value) && $f == 1) {
				foreach($values as $k => $v) {
					if($v->rate == $rate) {
						$value = $k;
						break;
					}
				}
			}
		}

		if(empty($id))
			$id = false;

		$opt = array(
			'id' => $id,
			'list.attr' => $options,
			'list.translate' => false,
			'option.key' => 'value',
			'option.text' => 'text',
			'list.select' => $value,
			'option.attr' => 'extra',
		);
		return JHTML::_('select.genericlist', $values, $map, $opt);
	}
}
