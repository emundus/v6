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
class hikashopPublishedType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 0,JText::_('HIKA_ALL'));
		$this->values[] = JHTML::_('select.option', 2,JText::_('HIKA_PUBLISHED'));
		$this->values[] = JHTML::_('select.option', 1,JText::_('HIKA_UNPUBLISHED'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int)$value );
	}
}
