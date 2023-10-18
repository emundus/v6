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

class hikashopData_filterType{
	var $allValues;
	function load($cursor){
		$this->allValues = array();
		if(!$cursor){
			$this->allValues["price"] = JText::_('PRICE');
			$this->allValues["category"] = JText::_('CATEGORY');
			$this->allValues["characteristic"] = JText::_('CHARACTERISTICS');
			$this->allValues["manufacturers"] = JText::_('MANUFACTURERS');
			$this->allValues["sort"] = JText::_('SORT');
			$this->allValues["custom_field"] = JText::_('FIELDS');
		}
		$this->allValues["information"] = JText::_('PRODUCT_INFORMATION');
	}

	function display($map,$value, $cursor=false){
		$this->load($cursor);
		$id='';
		if($cursor){ $id='_cursor'; }

		$js = "function updateDataType".$id."(){
			newType = document.getElementById('filtertype').value;
			if(newType=='text' || newType=='cursor' || newType=='instockcheckbox' ){
				return 0;
			}
			unitType = document.getElementById('product_information_value').value;
			if(unitType=='weight'){
				var unit='weight_unit';
			}else{
				var unit='dimension_unit';
			}

			newType = document.getElementById('datatype".$id."').value;
			hiddenAll = new Array('filterValues','filter_categories','sortOption','filterCharacteristics', 'productInfo', 'manufacturers', 'currencies', 'characteristic', 'sort_by', 'product_information', 'custom_field', 'dimension_unit', 'weight_unit');
			allTypes = new Array();
			allTypes['price'] = new Array('filterValues', 'currencies');
			allTypes['quantity'] = new Array();
			allTypes['category'] = new Array('filter_categories');
			allTypes['characteristic'] = new Array('characteristic');
			allTypes['manufacturers'] = new Array('manufacturers');
			allTypes['sort'] = new Array('sort_by');
			allTypes['information'] = new Array('product_information','filterValues', unit);
			allTypes['custom_field'] = new Array('custom_field', 'filterValues');
			for (var i=0; i < hiddenAll.length; i++){
				elems = document.querySelectorAll('tr[id='+hiddenAll[i]+']');
				for(var j = 0; j < elems.length; j++) {
					elems[j].style.display = 'none';
				}
			}

			for (var i=0; i < allTypes[newType].length; i++){
				elems = document.querySelectorAll('tr[id='+allTypes[newType][i]+']');
				for(var j = 0; j < elems.length; j++) {
					elems[j].style.display = '';
				}
			}
		}
		window.hikashop.ready( function(){ updateDataType".$id."(); });";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );

		$this->values = array();
		foreach($this->allValues as $oneType => $oneVal){
			$this->values[] = JHTML::_('select.option', $oneType,$oneVal);
		}
		$select='select.genericlist';

		if(is_array($value)) $value = reset($value);

		return JHTML::_($select, $this->values, $map , 'class="custom-select" size="1" onchange="updateDataType'.$id.'();"', 'value', 'text', (string) $value,'datatype'.$id.'');
	}
}
