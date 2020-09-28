<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopRegistrationType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 0,JText::_('HIKA_REGISTRATION') );
		$this->values[] = JHTML::_('select.option', 1,JText::_('SIMPLIFIED_REGISTRATION'));
		$this->values[] = JHTML::_('select.option', 3,JText::_('SIMPLIFIED_REGISTRATION_WITH_PASSWORD'));
		$this->values[] = JHTML::_('select.option', 2,JText::_('NO_REGISTRATION'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.radiolist',   $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', (int)$value );
	}
}
