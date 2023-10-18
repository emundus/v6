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
class hikashopWidgetcontentType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'orders',JText::_('ORDERS'));
		$this->values[] = JHTML::_('select.option', 'sales',JText::_('SALES'));
		$this->values[] = JHTML::_('select.option', 'taxes',JText::_('TAXES'));
		$this->values[] = JHTML::_('select.option', 'customers',JText::_('CUSTOMERS'));
		if(hikashop_level(2)){
			$this->values[] = JHTML::_('select.option', 'partners',JText::_('PARTNERS'));
		}
	}
	function display($map,$value){
		$this->load();
		if(empty($value))$value='orders';
		$js="
		function switchPie(name,value){
			var elSel = document.getElementById(name);
			if(value=='orders' || value=='sales' || value=='taxes'){
			 var i;
			 var found=false;
			 for (i = elSel.length - 1; i>=0; i--) {
				if (elSel.options[i].value=='pie') {
					found=true;
				}
			 }
			 if(!found){
				 var elOptNew = document.createElement('option');
				 elOptNew.text = '".JText::_('PIE',true)."';
				 elOptNew.value = 'pie';
				 var elSel = document.getElementById(name);
				 try {
					 elSel.add(elOptNew, null);
				 }
				 catch(ex) {
					 elSel.add(elOptNew);
				 }
			}
			}else{
			 var i;
			 for (i = elSel.length - 1; i>=0; i--) {
				if (elSel.options[i].value=='pie') {
					elSel.remove(i);
				}
			 }
			}
			switchDisplay(document.getElementById('widget_display').value,'widget_limit','listing',1);
		}
		function switchListing(name,value){
			var elSel = document.getElementById(name);
			if(value!='taxes'){
			 var i;
			 var found=false;
			 for (i = elSel.length - 1; i>=0; i--) {
				if (elSel.options[i].value=='listing') {
					found=true;
				}
			 }
			 if(!found){
				 var elOptNew = document.createElement('option');
				 elOptNew.text = '".JText::_('LISTING',true)."';
				 elOptNew.value = 'listing';
				 var elSel = document.getElementById(name);
				 try {
					 elSel.add(elOptNew, null);
				 }
				 catch(ex) {
					 elSel.add(elOptNew);
				 }
			}
			}else{
			 var i;
			 for (i = elSel.length - 1; i>=0; i--) {
				if (elSel.options[i].value=='listing') {
					elSel.remove(i);
				}
			 }
			}
			switchDisplay(document.getElementById('widget_display').value,'widget_limit','listing',1);
		}
		function switchDate(value,name,activevalue1,activevalue2,inverse){
			var el = document.getElementById(name);
			if(el){
				if(inverse){
					var show = value==activevalue1 || value==activevalue2;
				}else{
					var show = value!=activevalue1 && value!=activevalue2;
				}
				if(show){
					el.style.display='';
				}else{
					el.style.display='none';
				}
			}
		}
		window.hikashop.ready( function(){ switchListing('widget_display','".$value."'); switchPie('widget_display','".$value."'); switchDate('".$value."','widget_date','partners','customers',0); switchDate('".$value."','widget_status','partners','customers',0);});
		";
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		$options=' onchange="switchListing(\'widget_display\',this.value); switchPie(\'widget_display\',this.value);switchDate(this.value,\'widget_date\',\'orders\',\'sales\',1);switchDate(this.value,\'widget_status\',\'orders\',\'sales\',1);"';
		return JHTML::_('hikaselect.genericlist',   $this->values, $map, 'class="custom-select" size="1"'.$options, 'value', 'text', $value );
	}
}
