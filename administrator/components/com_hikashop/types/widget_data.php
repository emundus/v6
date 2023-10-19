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
class hikashopwidget_dataType{
	function load(){
		$this->values = array(
			JHTML::_('select.option', 'gauge',JText::_('GAUGE')),
			JHTML::_('select.option', 'graph',JText::_('GRAPH')),
			JHTML::_('select.option', 'line',JText::_('LINE')),
			JHTML::_('select.option', 'area',JText::_('AREA')),
			JHTML::_('select.option', 'pie',JText::_('PIE')),
			JHTML::_('select.option', 'map',JText::_('MAP')),
			JHTML::_('select.option', 'listing',JText::_('LISTING')),
			JHTML::_('select.option', 'table',JText::_('TABLE')),
		);
	}

	function display($map, $value, $option = '', $id = null, $widget_id = null, $row_id = null, $widget_display = null){
		$this->load();

		$js = "
function updateDisplay(){
	var d = document, oldType = displayType, values = new Array('gauge', 'graph', 'line', 'pie', 'area', 'map', 'listing', 'table');
	for(var i=0; i < values.length; i++){
		var newType = d.getElementById('widget_display_'+values[i]).checked;
		if(newType){
			displayType = d.getElementById('widget_display_'+values[i]).value;
		}
	}
	if(displayType=='table'){
		d.getElementById('widget_type').style.display='none';
		if(d.getElementById('widget_options'))d.getElementById('widget_options').style.display='none';
		d.getElementById('widget_date').style.display='none';
		d.getElementById('widget_group').style.display='none';
		d.getElementById('widget_period').style.display='none';
		d.getElementById('products_options').style.display='none';
		d.getElementById('filters').style.display='none';
		d.getElementById('customers_options').style.display='none';
		d.getElementById('partners_options').style.display='none';
		d.getElementById('orders_options').style.display='none';
		d.getElementById('product_datas').style.display='none';
		d.getElementById('widget_compare').style.display='none';
		d.getElementById('widget_limit').style.display='none';
		d.getElementById('map_options').style.display='none';
	}else{
		var show = new Array('widget_type', 'widget_options', 'filters','widget_date', 'widget_specific_options', 'widget_compare', 'widget_group','widget_period','widget_limit','type_listing_sales','type_listing_taxes','partners_button','customers_button');
		var hide = new Array('type_listing_discounts','orders_options', 'widget_region','products_options','customers_options','partners_options','type_listing_prod','type_listing_cat','map_options');

		for(var i = 0; i < show.length; i++ ) {
			var e = d.getElementById(show[i]);
			if(e) {
				e.style.display = '';
			} else {
			}
		}
		for(var i = 0; i < hide.length; i++ ) {
			var e = d.getElementById(hide[i]);
			if(e) {
				e.style.display = 'none';
			} else {
			}
		}

		if(displayType=='map'){
			updateDisplayType();
		}
		if(displayType=='listing'){
			if(d.getElementById('type_listing_prod')) d.getElementById('type_listing_prod').style.display='';
			if(d.getElementById('type_listing_cat')) d.getElementById('type_listing_cat').style.display='';
			if(d.getElementById('type_listing_discounts')) d.getElementById('type_listing_discounts').style.display='';
			if(d.getElementById('type_listing_sales')) d.getElementById('type_listing_sales').style.display='none';
			if(d.getElementById('type_listing_taxes')) d.getElementById('type_listing_taxes').style.display='none';
			if(d.getElementById('widget_compare')) d.getElementById('widget_compare').style.display='none';
			updateDisplayType();
		}
		if(displayType=='gauge' || displayType=='pie' || displayType=='map'){
			d.getElementById('widget_compare').style.display='none';
			d.getElementById('widget_limit').style.display='none';
		}
		if(displayType=='listing' || displayType=='pie' || displayType=='map'){
			d.getElementById('widget_group').style.display='none';
		}
		if(displayType=='map'){
			d.getElementById('widget_region').style.display='';
		}
		if(displayType=='pie'){
			d.getElementById('partners_button').style.display='none';
			d.getElementById('customers_button').style.display='none';
		}
		updateDisplayType();
	}
}

function displayTablePopup(){
	var d = document, values = new Array('gauge', 'graph', 'line', 'pie', 'area', 'map', 'listing', 'table');
	for(var i=0; i<values.length; i++){
		newType = d.getElementById('widget_display_'+values[i]).checked;
		if(newType==true){
			displayType = d.getElementById('widget_display_'+values[i]).value;
		}
	}

	widget_display='".$widget_display."';
	if(displayType!='table' || widget_display=='table'){ return 0; }
	d.getElementById('widget_display_'+widget_display).checked=true;
	updateDisplay();
	hikashop.openBox('table_popup_link','".hikashop_completeLink('report&task=tableform&widget_id='.$widget_id.'&row_id='.$row_id.'&first=true',true, true )."');
}

window.hikashop.ready( function(){ updateDisplay(); });";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );

		hikashop_loadJslib('vex');
		return '<a href="#" data-hk-popup="vex" data-vex="{x: 760, y: 480}" style="display:none;" id="table_popup_link"></a>'.JHTML::_('hikaselect.radiolist',   $this->values, $map, 'class="custom-select" size="1" onchange="updateDisplay(); displayTablePopup();"'.$option, 'value', 'text', $value, $id.'_' );
	}
}
