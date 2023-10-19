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
class hikashopLimitType{
	function load($listing = false){
		$this->values = array();
		if($listing)
			$this->values[] = JHTML::_('select.option', '',JText::_('HIKA_ALL'));
		$this->values[] = JHTML::_('select.option', 'quantity',JText::_('PRODUCT_QUANTITY'));
		$this->values[] = JHTML::_('select.option', 'price',JText::_('PRICE'));
		$this->values[] = JHTML::_('select.option', 'weight',JText::_('PRODUCT_WEIGHT'));
	}
	function display($map, $value, $listing = false, $attributes = ''){
		$this->load($listing);
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1" '.$attributes, 'value', 'text', $value );
	}
}
