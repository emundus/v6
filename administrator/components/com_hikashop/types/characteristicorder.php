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
class hikashopCharacteristicorderType {
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'old',JText::_('ID'));
		$this->values[] = JHTML::_('select.option', 'alphabetic',JText::_('ALPHABETIC'));
		$this->values[] = JHTML::_('select.option', 'ordering',JText::_('ORDERING'));
		$this->values[] = JHTML::_('select.option', 'alias',JText::_('HIKA_ALIAS'));
		$config = hikashop_config();
		if($config->get('characteristic_display') == 'list') {
			$this->values[] = JHTML::_('select.option', 'price',JText::_('PRODUCT_PRICE'));
		}
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', $value );
	}
}
