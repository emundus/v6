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
class hikashopPaginationType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '',JText::_('HIKASHOP_NO'));
		$this->values[] = JHTML::_('select.option', 'top',JText::_('HIKA_TOP'));
		$this->values[] = JHTML::_('select.option', 'bottom',JText::_('HIKA_BOTTOM'));
		$this->values[] = JHTML::_('select.option', 'both',JText::_('DISPLAY_BOTH_PAGINATION'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', $value );
	}
}
