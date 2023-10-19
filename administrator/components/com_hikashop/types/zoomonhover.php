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
class hikashopZoomonhoverType {
	function load(){
		$this->values = array(
			0 => JHTML::_('select.option', 0,JText::_('HIKASHOP_NO')),
			1 => JHTML::_('select.option', 1, JText::_('HIKASHOP_YES'))
		);
		if(hikaInput::get()->getCmd('from_display',false) == false){
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['zoom_on_hover']))
				$default = ' ('.$this->values[$defaultParams['zoom_on_hover']]->text.')';
			$this->values['inherit'] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT').$default);
		}
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', $value );
	}
}
