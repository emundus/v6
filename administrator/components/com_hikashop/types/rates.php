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
class hikashopRatesType{
	public $results = null;
	public $values = null;

	function load($form, $allowNoRate=false){
		$this->values = array();
		$query = 'SELECT * FROM '.hikashop_table('tax');
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$this->results = $db->loadObjectList();
		if(!$form){
			$this->values[] = JHTML::_('select.option', '',JText::_('ALL_RATES'));
		}
		if($allowNoRate) {
			$this->values[] = JHTML::_('select.option', '-1',JText::_('NO_TAX'));
		}
		foreach($this->results as $result){
			$this->values[] = JHTML::_('select.option', $result->tax_namekey,hikashop_translate($result->tax_namekey).' ('.($result->tax_rate*100.0).'%)');
		}
	}
	function display($map,$value,$form=true, $options='', $allowNoRate=false){
		$this->load($form, $allowNoRate);
		$options .= ' class="custom-select" size="1"';
		if(!$form){
			$options .=' onchange="document.adminForm.submit();"';
		}
		if($allowNoRate && empty($value))
			$value = '-1';
		return JHTML::_('select.genericlist',   $this->values, $map, $options, 'value', 'text', $value );
	}
}
