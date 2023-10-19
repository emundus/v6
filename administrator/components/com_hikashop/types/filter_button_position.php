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
class hikashopFilter_button_positionType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'left',JText::_('HIKA_LEFT'));
		$this->values[] = JHTML::_('select.option', 'right',JText::_('HIKA_RIGHT'));
		$this->values[] = JHTML::_('select.option', 'inside',JText::_('HIKA_INSIDE'));

	}
	function display($map,$value, $options=''){
		$this->load();
		$attribs = 'class="custom-select" size="1"';

		return JHTML::_('select.genericlist',   $this->values, $map, $attribs.$options, 'value', 'text', $value );
	}
}
