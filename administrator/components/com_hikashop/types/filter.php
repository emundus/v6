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

class hikashopFilterType{
	var $allValues;
	function load(){
		$this->allValues = array();
		$this->allValues["text"] = JText::_('FIELD_TEXT');
		$this->allValues["radio"] = JText::_('FIELD_RADIO');
		$this->allValues["checkbox"] = JText::_('FIELD_CHECKBOX');
		$this->allValues["singledropdown"] = JText::_('FIELD_SINGLEDROPDOWN');
		$this->allValues["multipledropdown"] = JText::_('FIELD_MULTIPLEDROPDOWN');
		$this->allValues["cursor"] = JText::_('CURSOR');
		$this->allValues["list"] = JText::_('LIST');
		$this->allValues["instockcheckbox"] = JText::_('IN_STOCK_CHECKBOX');
		JPluginHelper::importPlugin( 'hikashop' );
		$app = JFactory::getApplication();
		$app->triggerEvent( 'onFilterTypeDisplay', array( & $this->allValues ) );
	}

	function display($map,$value){
		$this->load();
		$js = "function updateFilterType(){
			newType = document.getElementById('filtertype').value;
			hiddenAll = new Array('rangeSize', 'titlePositionCursor', 'cursorWidth', 'cursorEffet', 'cursorStep','applyOnCursor', 'textBoxSize', 'filterSize','titlePosition','applyOntext','applyOn','filterValues','filterCategories', 'cursorNumber', 'cursorMax', 'cursorMin', 'currencies', 'filter_categories','max_char', 'characteristic', 'sort_by', 'product_information', 'button_align', 'dimension_unit', 'weight_unit', 'searchProcessing');
			allTypes = new Array();
			allTypes['text'] = new Array('applyOntext','titlePosition','max_char', 'textBoxSize', 'searchProcessing', 'attribute');
			allTypes['radio'] = new Array('applyOn','titlePosition','filterValues','filterCategories', 'button_align');
			allTypes['checkbox'] = new Array('applyOn','titlePosition','filterValues','filterCategories', 'button_align');
			allTypes['singledropdown'] = new Array('applyOn','titlePosition','filterValues','filterCategories','filterSize');
			allTypes['multipledropdown'] = new Array('applyOn','titlePosition','filterValues','filterCategories','filterSize');
			allTypes['cursor'] = new Array('applyOnCursor','cursorStep', 'cursorNumber', 'cursorMax', 'cursorMin', 'cursorEffet', 'cursorWidth', 'titlePositionCursor', 'rangeSize');
			allTypes['instockcheckbox'] = new Array('');
			allTypes['list'] = new Array('applyOn','titlePosition','filterValues','filterCategories','filterSize');
			for (var i=0; i < hiddenAll.length; i++){
				elems = document.querySelectorAll('tr[id='+hiddenAll[i]+']');
				for(var j = 0; j < elems.length; j++) {
					elems[j].style.display = 'none';
				}
			}

			if(newType=='instockcheckbox'){
				return;
			}

			for (var i=0; i < allTypes[newType].length; i++){
				elems = document.querySelectorAll('tr[id='+allTypes[newType][i]+']');
				for(var j = 0; j < elems.length; j++) {
					elems[j].style.display = '';
				}
			}
			if(newType!='text' && newType!='cursor'){
				updateDataType();
			}
			if(newType=='cursor'){
				data_type = document.getElementById('datafilterfilter_data_cursor').value;
				setVisibleUnit(data_type);
			}
		}
		window.hikashop.ready( function(){ updateFilterType(); });";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );

		$this->values = array();
		foreach($this->allValues as $oneType => $oneVal){
			$this->values[] = JHTML::_('select.option', $oneType,$oneVal);
		}


		return JHTML::_('select.genericlist', $this->values, $map , 'class="custom-select" size="1" onchange="updateFilterType();"', 'value', 'text', (string) $value,'filtertype');
	}
}
