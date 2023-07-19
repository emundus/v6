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

class reportViewReport extends hikashopView {
	var $ctrl= 'report';
	var $nameListing = 'HIKASHOP_REPORTS';
	var $nameForm = 'HIKASHOP_REPORT';
	var $icon = 'chart-bar';

	var $charttype = 'ColumnChart';
	var $interval = 'month';

	function display($tpl = null) {
		$doc = JFactory::getDocument();
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$doc->addScript(((empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://')."www.google.com/jsapi");

		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();

		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.widget_ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();

		$filters = array();
		$searchMap = array('a.widget_id','a.widget_name');

		if(!empty($pageInfo->search)){
		$searchVal = '\'%'.hikashop_getEscaped(HikaStringHelper::strtolower(trim($pageInfo->search)),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		hikashop_addACLFilters($filters,'widget_access','a');
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('widget').' AS a '.$filters.$order;
		$database->setQuery('SELECT a.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'widget_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$order = new stdClass();
		$order->ordering = true;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.widget_ordering'){
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}
		$this->assignRef('order',$order);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_report_manage','all'));
		$this->assignRef('manage',$manage);
		$viewAccess = hikashop_isAllowed($config->get('acl_report_view','all'));
		$this->assignRef('viewAccess',$viewAccess);
		$this->toolbar = array(
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$viewAccess),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_report_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
	}

	function form(){
		$dashboard=false;
		$config =& hikashop_config();
		$widget_id = hikashop_getCID('widget_id');
		$class = hikashop_get('class.widget');
		$db = JFactory::getDBO();
		if(!empty($widget_id)){
			$element = $class->get($widget_id);
			$task='edit';
		}else{
			$element = new stdClass();
			$element->widget_published = 1;
			$task='add';
			$element->widget_params = new stdClass();
			$element->widget_params->display='line';
			$element->widget_params->content='sales';
			$element->widget_params->date_group='%j %Y';
			$element->widget_params->date_type='created';
			$element->widget_params->periodType='proposedPeriod';
			$element->widget_params->proposedPeriod='thisMonth';
			$element->widget_params->format='UTF-8';
			$element->widget_params->period_compare='none';
			$element->widget_name='New report '.$widget_id;
			$element->widget_params->limit='7';
		}
		if(empty($element->widget_params->content))
			$element->widget_params->content = 'sales';
		if(empty($element->widget_params->date_group))
			$element->widget_params->date_group = '%j %Y';
		if(empty($element->widget_params->date_type))
			$element->widget_params->date_type = 'created';
		if(empty($element->widget_params->periodType))
			$element->widget_params->periodType = 'proposedPeriod';
		if(empty($element->widget_params->proposedPeriod))
			$element->widget_params->proposedPeriod = 'thisMonth';

		$class->loadDatas($element);
		if(isset($element->widget_params->table)){
			$row_id=count($element->widget_params->table);
			$this->assignRef('row_id',$row_id);
			foreach($element->widget_params->table as $row){
			$class->loadDatas($row);
			}
		}else{
			$row_id=0;
			$this->assignRef('row_id',$row_id);
		}
		if($element->widget_params->display!='table'){
			if($element->widget_params->display!='listing' && ($element->widget_params->content=='products' || $element->widget_params->content=='categories' || $element->widget_params->content=='discount')){
			$element->widget_params->content='orders';
			}
		}

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&widget_id='.$widget_id);


		$this->toolbar = array(
			array(
				'name' => 'link',
				'icon'=>'archive',
				'alt'=>JText::_('HIKA_EXPORT'),
				'url'=> hikashop_completeLink('report&task=csv&cid[]='.$widget_id).'&'.hikashop_getFormToken().'=1',
				'display'=>hikashop_level(2) && !empty($widget_id) && hikashop_isAllowed($config->get('acl_report_view','all'))
				),
			'|',
			array('name' => 'group', 'buttons' => array(
				array('name'=>'apply','display'=>hikashop_isAllowed($config->get('acl_report_manage','all'))),
				array('name'=>'save','display'=>hikashop_isAllowed($config->get('acl_report_manage','all'))),
				array('name' => 'save2new', 'display' => hikashop_isAllowed($config->get('acl_report_manage','all'))),
			)),
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);
		$this->assignRef('element',$element);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_widget',@$element->widget_id,$element);
			$config =& hikashop_config();
			$multilang_display=$config->get('multilang_display','tabs');
			if($multilang_display=='popups') $multilang_display = 'tabs';
			$tabs = hikashop_get('helper.tabs');
			$this->assignRef('tabs',$tabs);
			$this->assignRef('transHelper',$transHelper);

		}
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('translation',$translation);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$periodType = hikashop_get('type.period');
		$this->assignRef('periodType',$periodType);
		$widget_dataType = hikashop_get('type.widget_data');
		$this->assignRef('widget_dataType',$widget_dataType);
		$status = hikashop_get('type.categorysub');
		$status->type='status';
		$this->assignRef('status',$status);
		$delay = hikashop_get('type.delay');
		$this->assignRef('delay',$delay);
		$region = hikashop_get('type.region');
		$this->assignRef('region',$region);
		if(hikashop_level(2)){
			$encoding = hikashop_get('type.charset');
			$this->assignRef('encoding',$encoding);
		}
		$widgetClass = hikashop_get('class.widget');
		$this->assignRef('widgetClass',$widgetClass);
		$dateGroup = hikashop_get('type.dategroup');
		$this->assignRef('dateGroup',$dateGroup);
		$dateType = hikashop_get('type.datetype');
		$this->assignRef('dateType',$dateType);
		$shippingMethods = hikashop_get('type.plugins');
		$shippingMethods->type='shipping';
		$shippingMethods->manualOnly=true;
		$this->assignRef('shippingMethods',$shippingMethods);
		$paymentMethods = hikashop_get('type.plugins');
		$paymentMethods->type='payment';
		$paymentMethods->manualOnly=true;
		$this->assignRef('paymentMethods',$paymentMethods);
		$dashboard = hikaInput::get()->getVar( 'dashboard');
		$this->assignRef('dashboard',$dashboard);

		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);

		$script = "
	function deleteRow(divName,inputName,rowName){
		var d = document.getElementById(divName);
		var olddiv = document.getElementById(inputName);
		if(d && olddiv){
			d.removeChild(olddiv);
			document.getElementById(rowName).style.display='none';
		}
		return false;
	}

	function updatePeriodSelection(){
		selectedPeriod = document.getElementById('display_proposed_period').checked;
		document.getElementById('period_start').disabled=false;
		document.getElementById('period_end').disabled=false;
		document.getElementById('delayvalue1').disabled=false;
		document.getElementById('delaytype1').disabled=false;
		document.getElementById('datawidgetwidget_paramsproposedPeriod').disabled=false;
		if(selectedPeriod==true){
			document.getElementById('period_start').disabled=true;
			document.getElementById('period_end').disabled=true;
			document.getElementById('delayvalue1').disabled=true;
			document.getElementById('delaytype1').disabled=true;
		}else{
			document.getElementById('datawidgetwidget_paramsproposedPeriod').disabled=true;
		}
	}

	function updateCompare(){
		selectedCompare = document.getElementById('compare_with_values').checked;
		document.getElementById('compares_order_status').disabled=false;
		document.getElementById('compares_order_currency_id').disabled=false;
		document.getElementById('compares_order_payment_method').disabled=false;
		document.getElementById('compares_order_shipping_method').disabled=false;
		document.getElementById('compares_order_discount_code').disabled=false;
		document.getElementById('compares_products').disabled=false;
		document.getElementById('compares_categories').disabled=false;
		document.getElementById('compare_period').disabled=false;
		if(selectedCompare==true){
			document.getElementById('compare_period').disabled=true;
		}else{
			document.getElementById('compares_order_status').disabled=true;
			document.getElementById('compares_order_currency_id').disabled=true;
			document.getElementById('compares_order_payment_method').disabled=true;
			document.getElementById('compares_order_shipping_method').disabled=true;
			document.getElementById('compares_order_discount_code').disabled=true;
			document.getElementById('compares_products').disabled=true;
			document.getElementById('compares_categories').disabled=true;
		}
	}

	function updateDisplayType(){
		theType=false;
		displayType = null;
		values = new Array('gauge', 'graph', 'line', 'pie', 'area', 'map', 'listing', 'table');
		for(var i=0; i<values.length; i++){
			newType = document.getElementById('widget_display_'+values[i]).checked;
			if(newType==true){
				displayType = document.getElementById('widget_display_'+values[i]).value;
			}
		}
		if(displayType=='pie'){
			values = document.getElementsByName('data[widget][widget_params][content]');
			for(var i=0; i<values.length; i++){
				newType = values[i].checked;
				if(newType==true){
						theType = values[i].value;
					}
			}
			if(theType=='customers' || theType=='partners'){
				 document.getElementById('type_orders').checked=true;
			}
		}
		if(displayType=='map'){
			values = document.getElementsByName('data[widget][widget_params][content]');
			for(var i=0; i<values.length; i++){
				newType = values[i].checked;
				if(newType==true){
					theType = values[i].value;
				}
			}

			document.getElementById('map_options').style.display='none';
			document.getElementById('filters').style.display='';
			if(theType=='orders' || theType=='sales' ||  theType=='taxes'){
				document.getElementById('map_options').style.display='';
			}
			if(theType=='customers' ||  theType=='partners'){
			}
		}
		if(displayType=='gauge' || displayType=='line' || displayType=='area' || displayType=='graph' || displayType=='column'){
			values = document.getElementsByName('data[widget][widget_params][content]');
			for(var i=0; i<values.length; i++){
				newType = values[i].checked;
				if(newType==true){
						theType = values[i].value;
					}
			}
			if(theType==false){
				theType='orders';
			 	theType = document.getElementById('type_orders').checked=true;
			}
			document.getElementById('widget_compare').style.display='';
			if(theType=='orders' || theType=='sales' ||  theType=='taxes'){
				document.getElementById('widget_compare').style.display='';
			}
			if(theType=='customers' ||  theType=='partners'){
				document.getElementById('widget_compare').style.display='none';
			}
		}
		if(displayType=='table'){
			document.getElementById('products_options').style.display='none';
			document.getElementById('filters').style.display='none';
			document.getElementById('customers_options').style.display='none';
			document.getElementById('partners_options').style.display='none';
			document.getElementById('orders_options').style.display='none';
			document.getElementById('product_datas').style.display='none';
			document.getElementById('widget_compare').style.display='none';
			document.getElementById('widget_limit').style.display='none';
			document.getElementById('widget_region').style.display='none';
			document.getElementById('map_options').style.display='none';
		}
		if(displayType!='listing'){ return 0; }

		values = document.getElementsByName('data[widget][widget_params][content]');
		for(var i=0; i<values.length; i++){
			newType = values[i].checked;
			if(newType==true){
				theType = values[i].value;
			}
		}
		if(!theType){
			document.getElementById('type_orders').checked=true;
			theType='orders';
		}
		document.getElementById('products_options').style.display='none';
		document.getElementById('filters').style.display='none';
		document.getElementById('customers_options').style.display='none';
		document.getElementById('partners_options').style.display='none';
		document.getElementById('orders_options').style.display='none';
		document.getElementById('product_datas').style.display='none';

		if(theType=='orders' || theType=='products' || theType=='categories' || theType=='discounts' || theType=='customers' || theType== 'partners' || theType== 'taxes' || theType== 'sales'){
			document.getElementById('filters').style.display='';
			document.getElementById('product_datas').style.display='';
			if(theType=='categories'){
				document.getElementById('data_hits').style.display='none';
				clicksValue = document.getElementById('data_clicks').checked;
				if(clicksValue==true){
					document.getElementById('data_orders').checked=true;
				}
			}else if(theType=='products'){
				document.getElementById('data_hits').style.display='';
			}
		}
		if(theType=='discounts'){
			document.getElementById('product_datas').style.display='none';
		}
		if(theType=='products' || theType=='categories' || theType=='discounts'){
			document.getElementById('products_options').style.display='';
		}
		if(theType=='customers'){
			document.getElementById('customers_options').style.display='';
		}
		if(theType=='partners'){
			document.getElementById('partners_options').style.display='';
		}
		if(theType=='orders'){
			document.getElementById('orders_options').style.display='';
		}

	}
	window.hikashop.ready( function(){ updateDisplayType(); });
	window.hikashop.ready( function(){ updatePeriodSelection(); });
	window.hikashop.ready( function(){ updateCompare(); });
";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $script);

		$js='';
		$params= $element;
		$couponslist = @$this->element->widget_params->coupons_list;
		echo hikashop_getLayout('dashboard','widget',$params,$js);
		$this->element->widget_params->coupons_list = $couponslist;
	}

	function tableform(){
		$widget_id = hikaInput::get()->getVar( 'widget_id');
		$row_id = hikaInput::get()->getVar( 'row_id');
		$first = hikaInput::get()->getVar( 'first');
		$class = hikashop_get('class.widget');
		if(!empty($widget_id)){
			$element = $class->get($widget_id);
			$task='edit';
		}
		if(!isset($first) && isset($element->widget_params->table[$row_id])){
			$class->loadDatas($element->widget_params->table[$row_id]);
			$row=$element->widget_params->table[$row_id];
			$row->row_id=$row_id;
		}else{
			$row = new stdClass();
			$row->row_name='New row';
			$row->row_id=$row_id;
			$row->widget_params = new stdClass();
			$row->widget_params->periodType='proposedPeriod';
			$row->widget_params->proposedPeriod='thisMonth';
			$row->widget_params->content='sales';
			$row->widget_params->payment_id = array();
			$row->widget_params->payment_type = array();
		}

		$this->assignRef('first',$first);
		$this->assignRef('element',$element);
		$this->assignRef('row',$row);

		$dateGroup = hikashop_get('type.dategroup');
		$this->assignRef('dateGroup',$dateGroup);
		$periodType = hikashop_get('type.period');
		$this->assignRef('periodType',$periodType);
		$delay = hikashop_get('type.delay');
		$this->assignRef('delay',$delay);
		$status = hikashop_get('type.categorysub');
		$status->type='status';
		$this->assignRef('status',$status);
		$shippingMethods = hikashop_get('type.plugins');
		$shippingMethods->type='shipping';
		$shippingMethods->manualOnly=true;
		$this->assignRef('shippingMethods',$shippingMethods);
		$paymentMethods = hikashop_get('type.plugins');
		$paymentMethods->type='payment';
		$paymentMethods->manualOnly=true;
		$this->assignRef('paymentMethods',$paymentMethods);
		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);
		$this->_addUpdateJS();
	}

	function _addUpdateJS(){

		$js="
function updateDisplay(){
	var values = new Array('orders', 'sales', 'customers', 'partners', 'taxes', 'best', 'worst');
	for(var i=0; i<values.length; i++){
		e = document.getElementById('type_'+values[i]);
		if(e){
			newType = e.checked;
			if(newType==true){
				theType = e.value;
			}
		}else {
		}
	}
	var d = document;
	var show = new Array('best_worst_shipping', 'best_worst_payment', 'best_worst_currency','best_worst_country');
	var hide = new Array('customers_options','partners_options', 'best_options','filters', 'sales_options');

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


	if(theType=='sales' || theType=='orders' || theType=='taxes'){
		d.getElementById('filters').style.display='';
	}
	if(theType=='customers'){
		d.getElementById('customers_options').style.display='';
	}
	if(theType=='partners'){
		d.getElementById('partners_options').style.display='';
	}
	if(theType=='best' || theType=='worst'){
		d.getElementById('best_options').style.display='';
		if(theType=='worst'){
			d.getElementById('best_worst_shipping').style.display='none';
			d.getElementById('best_worst_payment').style.display='none';
			d.getElementById('best_worst_currency').style.display='none';
			d.getElementById('best_worst_country').style.display='none';
		}
	}
	if(theType=='sales'){
			document.getElementById('sales_options').style.display='';
		}

}
window.hikashop.ready( function(){updateDisplay(); });

function deleteRow(divName,inputName,rowName){
	var d = document, div = d.getElementById(divName), olddiv = d.getElementById(inputName);
	if(div && olddiv){
		div.removeChild(olddiv);
		d.getElementById(rowName).style.display='none';
	}
	return false;
}";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}

	function csv(){
		$widgetClass = hikashop_get('class.widget');
		$widgetClass->csv();
	}
}
