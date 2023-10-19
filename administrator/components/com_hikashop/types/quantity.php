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
class hikashopQuantityType {
	protected $values = array();

	protected function load($config) {
		$this->values = array(
			1 => JHTML::_('select.option', 1, JText::_('TYPE_QTY_ONE_PER_PRODUCT')),
			2 => JHTML::_('select.option', 2, JText::_('TYPE_QTY_GLOBAL')),
		);
		return $this->values;
	}

	public function display($map, $value, $config = true) {
		$this->load($config);
		return JHTML::_('hikaselect.radiolist', $this->values, $map, 'class="custom-select"', 'value', 'text', (int)$value);
	}

	public function displayInput($map, $value) {
		$attribs = '';
		$label = '';
		$id = str_replace(array('][','[',']'),array('__','_',''), $map);
		$app = JFactory::getApplication();
		$backend = hikashop_isClient('administrator');
		if(($backend && HIKASHOP_BACK_RESPONSIVE) || (!$backend && HIKASHOP_RESPONSIVE)) {
			hikashop_loadJsLib('tooltip');
			$ret = '<div class="input-append">'.
				'<input type="text" name="'.$map.'" id="'.$id.'" value="'.$value.'" onfocus="this.setSelectionRange(0, this.value.length)" '.$attribs.'/>'.
				'<span class="add-on" data-toggle="hk-tooltip" data-title="'.JText::_('UNLIMITED', true).'" onclick="document.getElementById(\''.$id.'\').value=\''.JText::_('UNLIMITED').'\';return false;"><i class="fas fa-infinity"></i></span>'.
				'</div>';
		} else {
			$ret = '<div class="product_quantity_j25" style="display: inline; margin-left: 2px;"><input type="text" name="'.$map.'" id="'.$id.'" value="'.$value.'" onfocus="this.setSelectionRange(0, this.value.length)" '.$attribs.'/>' .
				'<a class="infinityButton" href="#" onclick="document.getElementById(\''.$id.'\').value=\''.JText::_('UNLIMITED').'\';return false;"><span>X</span></a></div>';
		}
		return $ret;
	}
}
