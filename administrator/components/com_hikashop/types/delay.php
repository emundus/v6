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
class hikashopDelayType {
	var $values = array();
	var $onChange = '';
	var $num;

	public function __construct() {
		static $init = false;
		if($init)
			return;

		$init = true;
		$js = '
function hikashop_updateDelay(num) {
	var d = document,
		delayvar = d.getElementById("delayvar" + num),
		delaytype = d.getElementById("delaytype" + num).value,
		delayvalue = d.getElementById("delayvalue" + num),
		realValue = delayvalue.value;
	if(delaytype == "minute"){ realValue *= 60; }
	if(delaytype == "hour"){ realValue *= 3600; }
	if(delaytype == "day"){ realValue *= 86400; }
	if(delaytype == "week"){ realValue *= 604800; }
	if(delaytype == "month"){ realValue *= 2592000; }
	if(delaytype == "year"){ realValue *= 31556926; }
	delayvar.value = realValue;
}
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

	public function display($map, $value, $type = 1) {
		static $i = 0;
		$i++;
		$this->num = $i;

		$this->values = array();
		if($type == 0) {
			$this->values = array(
				JHTML::_('select.option', 'second', JText::_('HIKA_SECONDS')),
				JHTML::_('select.option', 'minute', JText::_('HIKA_MINUTES')),
			);
		} elseif($type == 1) {
			$this->values = array(
				JHTML::_('select.option', 'minute', JText::_('HIKA_MINUTES')),
				JHTML::_('select.option', 'hour', JText::_('HOURS')),
				JHTML::_('select.option', 'day', JText::_('DAYS')),
				JHTML::_('select.option', 'week', JText::_('WEEKS')),
			);
		} elseif($type == 2) {
			$this->values = array(
				JHTML::_('select.option', 'minute', JText::_('HIKA_MINUTES')),
				JHTML::_('select.option', 'hour', JText::_('HOURS')),
			);
		} elseif($type == 3) {
			$this->values = array(
				JHTML::_('select.option', 'hour', JText::_('HOURS')),
				JHTML::_('select.option', 'day', JText::_('DAYS')),
				JHTML::_('select.option', 'week', JText::_('WEEKS')),
				JHTML::_('select.option', 'month', JText::_('MONTHS')),
			);
		} elseif($type == 4) {
			$this->values = array(
				JHTML::_('select.option', 'day', JText::_('DAYS')),
				JHTML::_('select.option', 'week', JText::_('WEEKS')),
				JHTML::_('select.option', 'month', JText::_('MONTHS')),
				JHTML::_('select.option', 'year', JText::_('YEARS')),
			);
		}

		$return = $this->get($value,$type);
		$delayValue = '<input class="inputbox" onchange="hikashop_updateDelay('.$this->num.');'.$this->onChange.'" type="text" name="delayvalue'.$this->num.'" id="delayvalue'.$this->num.'" size="10" value="'.$return->value.'" /> ';
		$delayVar = '<input type="hidden" name="'.$map.'" id="delayvar'.$this->num.'" value="'.$value.'"/>';
		return $delayValue.JHTML::_('select.genericlist', $this->values, 'delaytype'.$this->num, 'class="custom-select" size="1" onchange="hikashop_updateDelay('.$this->num.');'.$this->onChange.'"', 'value', 'text', $return->type ,'delaytype'.$this->num).$delayVar;
	}

	public function get($value, $type) {
		$ret = new stdClass();
		$ret->value = $value;

		if($value % 31556926 == 0) {
			$ret->type = 'year';
			$ret->value = (int) $value / 31556926;
			return $ret;
		}

		$ret->type = ($type == 0) ? 'second' : 'minute';

		if($ret->value < 60 || $ret->value % 60 != 0)
			return $ret;

		$ret->value /= 60;
		$ret->type = 'minute';

		if($type == 0 || $ret->value < 60 || $ret->value % 60 != 0)
			return $ret;

		$ret->value /= 60;
		$ret->type = 'hour';

		if($type == 2 || $ret->value < 24 || $ret->value % 24 != 0)
			return $ret;

		$ret->value /= 24;
		$ret->type = 'day';

		if($type == 3 && $ret->value >= 30 && $ret->value % 30 == 0) {
			$ret->value /= 30;
			$ret->type = 'month';
		} elseif($ret->value >= 7 && $ret->value % 7 == 0) {
			$ret->value /= 7;
			$ret->type = 'week';
		}

		return $ret;
	}

	public function displayDelay($value) {
		if(empty($value))
			return 0;

		$type = 'HIKA_SECONDS';
		if($value < 60 || $value % 60 != 0)
			return $value.' '.JText::_($type);

		$value /= 60;
		$type = 'HIKA_MINUTES';

		if($value < 60 || $value % 60 != 0)
			return $value.' '.JText::_($type);

		$value /= 60;
		$type = 'HOURS';

		if($value < 24 || $value % 24 != 0)
			return $value.' '.JText::_($type);

		$value /= 24;
		$type = 'DAYS';

		if($value >= 30 && $value % 30 == 0) {
			$value = $value / 30;
			$type = 'MONTHS';
		} elseif($value >= 7 && $value % 7 == 0) {
			$value = $value / 7;
			$type = 'WEEKS';
		}
		return $value.' '.JText::_($type);
	}

	public function displayDelaySECtoDAY($value, $type) {
		if($type == 0) {
			$value = round((int)$value / 60);
		}
		if($type == 1) {
			$value = round((int)$value / 3600);
		}
		if($type == 2) {
			$value = round((int)$value / 86400);
		}
		return $value;
	}
}
