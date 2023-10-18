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
class hikashopDiscount_displayType{
	function load(){
		$this->values = array();
		$this->values[4] = JHTML::_('select.option', 4,JText::_('DISPLAY_BOTH'));
		$this->values[2] = JHTML::_('select.option', 2,JText::_('DISPLAY_PRICE_BEFORE_DISCOUNT'));
		$this->values[1] = JHTML::_('select.option', 1,JText::_('DISPLAY_DISCOUNT_AMOUNT'));
		$this->values[0] = JHTML::_('select.option', 0,JText::_('HIKASHOP_NO'));
		if(hikaInput::get()->getCmd('from_display',false) == false){
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['show_discount']))
				$default = ' ('.$this->values[$defaultParams['show_discount']]->text.')';
			$this->values[3] = JHTML::_('select.option', 3,JText::_('HIKA_INHERIT').$default);
		}
	}
	function display($map,$value, $attributes=''){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1" '.$attributes, 'value', 'text', (int)$value );
	}
}
