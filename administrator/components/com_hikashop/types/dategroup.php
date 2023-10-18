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
class hikashopDategroupType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '%H %j %Y',JText::_('HOURS'));
		$this->values[] = JHTML::_('select.option', '%j %Y',JText::_('DAYS'));
		$this->values[] = JHTML::_('select.option', '%u %Y',JText::_('WEEKS'));
		$this->values[] = JHTML::_('select.option', '%m %Y',JText::_('MONTHS'));
		$this->values[] = JHTML::_('select.option', '%Y',JText::_('YEARS'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', $value );
	}
}
