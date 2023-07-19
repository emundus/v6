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

class FilterViewFilter extends hikashopView{

	var $ctrl= 'filter';
	var $nameListing = 'FILTERS';
	var $nameForm = 'FILTER';
	var $icon = 'filter';

	function display($tpl = null){
		$function = $this->getLayout();
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		if(method_exists($this,$function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.filter_ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = HikaStringHelper::strtolower(trim($pageInfo->search));
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$searchMap = array('a.filter_id', 'a.filter_name');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}

		$query = ' FROM '.hikashop_table('filter').' AS a';
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'filter_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 0;

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_filter_manage','all'));
		$this->assignRef('manage',$manage);

		$this->toolbar = array(
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_filter_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();

		$order = new stdClass();
		if($this->pageInfo->filter->order->value!='a.filter_ordering'){
			$order->ordering = false;
		}else{
			$order->ordering = true;
		}

		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.filter_ordering'){
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}
		$this->assignRef('order',$order);
	}

	function form(){
		$filter_id = hikashop_getCID('filter_id',false);
		if(!empty($filter_id)){
			$class = hikashop_get('class.filter');
			$element = $class->get($filter_id);
			$task='edit';
		}else{
			$element = hikaInput::get()->getVar('fail');
			if(empty($element)){
				$element = new stdClass();
				$app = JFactory::getApplication();
				$type = $app->getUserState( $this->paramBase.".filter_type");
				if(!in_array($type,array('all','nochilds'))){
					$element->filter_type = $type;
				}else{
					$element->filter_type = 'textarea';
				}
				$element->filter_published=1;
				$element->filter_options['title_position']='top';
				$element->filter_options['cursor_number']='2';
			}
			$task='add';
		}
		$database = JFactory::getDBO();
		if(!empty($filter_id)){
			$query = 'SELECT category_name FROM '.hikashop_table('filter').' AS a INNER JOIN '.hikashop_table('category').' AS b ON a.filter_category_id=b.category_id WHERE filter_id = '.(int)$filter_id;
			$database->setQuery($query);
			$element->filter_category_name = $database->loadResult();
			$element->filter_options = hikashop_unserialize($element->filter_options);

			$element->filter_data = hikashop_unserialize($element->filter_data);

			$categories=array();
			if(!empty($element->filter_options['parent_category_id'])){
				$query= 'SELECT category_name FROM '.hikashop_table('category').' WHERE category_id='.(int)$element->filter_options['parent_category_id'].'';
				$database->setQuery($query);
				$element->filter_options['parent_category_name']=$database->loadResult();
			}
		}

		$fields=array();
		$filters = '';

		if(isset($element->filter_category_id)){
			$filter_category_ids = explode(',', $element->filter_category_id);
			if($element->filter_category_childs){
				$categories_filter=array();
				$categoryClass = hikashop_get('class.category');
				$children = $categoryClass->getChildren($filter_category_ids,true);
				$parents = $categoryClass->getParents($filter_category_ids);
				foreach($children as $cat){
					 $categories_filter[]='field_categories LIKE \'%,'.$cat->category_id.',%\'';
				}
				$filters=implode(' OR ',$categories_filter);
				$categories_filter=array();
				if(!empty($parents)){
					foreach($parents as $cat){
						 $categories_filter[]='field_categories LIKE \'%,'.$cat->category_id.',%\'';
					}
					if(!empty($filters)){ $filters.=' OR ';}
					$filters.=' ('.implode(' OR ',$categories_filter).' AND field_with_sub_categories=1) ';
				}
			}
			foreach($filter_category_ids as $category_id){
				if(!empty($filters)) {
					$filters .= ' OR ';
				}
				$filters .= 'field_categories LIKE \'%,'.(int)$category_id.',%\'';
			}
		}
		if(!empty($filters)){
			$filters .= ' OR ';
		}
		$database->setQuery('SELECT * FROM '.hikashop_table('field').' WHERE ('.$filters.'field_categories LIKE "all") AND field_table IN ("product") AND field_published=1');
		$fields=$database->loadObjectList('field_realname');

		if(!empty($element->filter_value)){
				$element->filter_value=explode("\n", $element->filter_value);
			 foreach($element->filter_value as $key => $val){
				 $temp=explode("::", $val);
				 $element->filter_value[$key]=$temp[1];
			 }
		}

		if(empty($element->filter_options['label_format'])) {
			$element->filter_options['label_format'] = '{"mark":".","thousand":" ","prefix":"","suffix":"","decimals":"2"}';
		}

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&filter_id='.$filter_id);

		$js='
		function addLine(){
			var myTable=window.document.getElementById("tablevalues");
			var newline = document.createElement(\'tr\');
			var column2 = document.createElement(\'td\');
			var input = document.createElement(\'input\');
			input.type = \'text\';
			input.name = \'filter_values[value][]\';
			column2.appendChild(input);
			newline.appendChild(column2);
			myTable.appendChild(newline);
		}

		function deleteRow(divName,inputName,rowName){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display="none";
			}
			return false;
		}

		function setVisibleUnit(value){
		if(value==\'weight\'){
			document.getElementById(\'weight_unit\').style.display = \'\';
			document.getElementById(\'dimension_unit\').style.display = \'none\';
		}else if(value==\'height\' || value==\'volume\' || value==\'surface\' || value==\'length\' || value==\'width\'){
			document.getElementById(\'weight_unit\').style.display = \'none\';
			document.getElementById(\'dimension_unit\').style.display = \'\';
		}else{
			document.getElementById(\'weight_unit\').style.display = \'none\';
			document.getElementById(\'dimension_unit\').style.display = \'none\';
		}
	}
		';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js);

		$this->toolbar = array(
			array('name' => 'group', 'buttons' => array( 'apply', 'save')),
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$this->assignRef('element',$element);
		$status = hikashop_get('type.categorysub');
		$status->type='status';
		$filterType = hikashop_get('type.filter');
		$this->assignRef('filterType',$filterType);
		$positionType = hikashop_get('type.position');
		$this->assignRef('positionType',$positionType);
		$divPositionType = hikashop_get('type.div_position');
		$this->assignRef('div_positionType',$divPositionType);
		$data_filterType = hikashop_get('type.data_filter');
		$this->assignRef('data_filterType',$data_filterType);
		$orderType = hikashop_get('type.order');
		$this->assignRef('orderType',$orderType);
		$volumeType = hikashop_get('type.volume');
		$this->assignRef('volume',$volumeType);
		$weightType = hikashop_get('type.weight');
		$this->assignRef('weight',$weightType);
		$currencyType = hikashop_get('type.currency');
		$this->assignRef('currencyType',$currencyType);
		$product_informationType = hikashop_get('type.product_information');
		$this->assignRef('product_informationType',$product_informationType);
		$characteristiclistType = hikashop_get('type.characteristiclist');
		$this->assignRef('characteristiclistType',$characteristiclistType);
		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type='tax';
		$categoryType->field='category_id';
		$this->assignRef('categoryType',$categoryType);
		$this->assignRef('fields',$fields);
		$popup=hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);
		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);

	}

}
