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
class hikashopEffectType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'slide',JText::_('SLIDE'));
		$this->values[] = JHTML::_('select.option', 'fade',JText::_('FADE'));
	}
	function display($map,$value, $options=''){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1" '.$options, 'value', 'text', $value );
	}
}
