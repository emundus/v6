<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopPaymentType{
	var $extra = '';
	function load($form, $value = ''){
		$this->values = array();
		$pluginsClass = hikashop_get('class.plugins');
		$methods = $pluginsClass->getMethods('payment');

		if(!$form){
			$this->values[] = JHTML::_('select.option', '', JText::_('ALL_PAYMENT_METHODS') );
		}

		if(!empty($methods)){
			foreach($methods as $method){
				if(isset($method->enabled) && !$method->enabled && $method->payment_id != $value) continue;
				$this->values[] = JHTML::_('select.option', $method->payment_id, $method->payment_name );
			}
		}
	}
	function display($map,$value,$form=true,$attribute='size="1"'){
		$this->load($form, $value);
		if(!$form){
			$attribute .= ' onchange="document.adminForm.submit();"';
		}
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" '.$this->extra.' '.$attribute, 'value', 'text', $value );
	}
}
