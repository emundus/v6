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
class hikashopWaitlistType{
	function load(){
		$this->values = array(
			JHTML::_('select.option', 0,JText::_('HIKASHOP_NO')),
			JHTML::_('select.option', 2,JText::_('FOR_ALL_PRODUCTS') ),
			JHTML::_('select.option', 1,JText::_('ON_A_PER_PRODUCT_BASIS')),
		);
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', (int)$value );
	}
}
