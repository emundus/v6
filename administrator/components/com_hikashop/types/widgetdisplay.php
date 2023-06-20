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
class hikashopWidgetdisplayType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'gauge',JText::_('GAUGE'));
		$this->values[] = JHTML::_('select.option', 'graph',JText::_('GRAPH'));
		$this->values[] = JHTML::_('select.option', 'listing',JText::_('LISTING'));
		if(hikashop_level(2)){
			$this->values[] = JHTML::_('select.option', 'map',JText::_('MAP'));
		}
		$this->values[] = JHTML::_('select.option', 'pie',JText::_('PIE'));
	}
	function display($map,$value){
		$this->load();

		if(empty($value))$value='gauge';
		$js = "
		function switchDisplay(value,name,activevalue,inverse){
			var el = document.getElementById(name);
			if(el){
				if(inverse){
					var show = value==activevalue;
				}else{
					var show = value!=activevalue;
				}
				if(show){
					el.style.display='';
				}else{
					el.style.display='none';
				}
			}
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
		window.hikashop.ready( function(){ switchDisplay('".$value."','widget_region','map',1); switchDisplay('".$value."','widget_limit','listing',1); switchDate('".$value."','widget_group','graph','gauge',1);}); ";
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		$options=' onchange="switchDisplay(this.value,\'widget_region\',\'map\',1); switchDisplay(this.value,\'widget_limit\',\'listing\',1); switchDate(this.value,\'widget_group\',\'graph\',\'gauge\',1);"';
		return JHTML::_('hikaselect.genericlist',   $this->values, $map, 'class="custom-select" size="1"'.$options, 'value', 'text', $value,'widget_display' );
	}
}
