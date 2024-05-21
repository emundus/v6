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

class hikashopFilterClass extends hikashopClass {

	var $tables = array('filter');
	var $pkeys = array('filter_id');
	var $toggle = array('filter_published' => 'filter_id');
	var $canBeUsed = true;

	function saveForm() {
		$app = JFactory::getApplication();
		$filter = new stdClass();
		$filter->filter_id = hikashop_getCID('filter_id');

		$problem = false;
		$new = empty($filter->filter_id);

		$formData = hikaInput::get()->get('data', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		foreach($formData['filter'] as $column => $value) {
			hikashop_secureField($column);
			if(is_array($value))
				$value = implode(',',$value);
			$filter->$column = $safeHtmlFilter->clean($value, 'string');
		}

		if(empty($filter->filter_name)) {
			$app->enqueueMessage(JText::_('SPECIFY_A_NAME'), 'error');
			$problem = true;
		}
		if(!empty($formData['filter']['attribute'])){
			$filter->filter_options['attribute']=$safeHtmlFilter->clean($formData['filter']['attribute'], 'string');
			if(!preg_match('#^([a-z0-9_-]+ *= *"[^"\\\]+" *)* *$#i', $filter->filter_options['attribute'])){
				$app->enqueueMessage('Please specify a correct attribute', 'error');
				$problem = true;
			}
		}

		if(empty($filter->filter_id)) {
			$filter->filter_namekey=$filter->filter_name;
			$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,-");
			$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,_");
			$test = str_replace($search, $replace, $filter->filter_namekey);
			$filter->filter_namekey = preg_replace('#[^a-z0-9_]#i','',$test);

			if(empty($test)) {
				$filter->filter_namekey = 'filter';
			}
			$query = 'SELECT MAX(`filter_id`) FROM '.hikashop_table('filter');
			$this->database->setQuery($query);
			$last_pid = (int)$this->database->loadResult() + 1;
			$filter->filter_namekey .= '_'.$last_pid;
		}

		if($formData['filter']['filter_type']=='text'){
			$value = $safeHtmlFilter->clean(@$formData['filter']['filter_data_text'], 'array');
			$filter->filter_data=serialize($value);
		}else if($formData['filter']['filter_type']=='cursor'){
			$value = $safeHtmlFilter->clean(@$formData['filter']['filter_data_cursor'], 'string');
			$filter->filter_data=serialize($value);
		}else{
			$value = $safeHtmlFilter->clean(@$formData['filter']['filter_data'], 'string');
			$filter->filter_data=serialize($value);
		}

		if($formData['filter']['filter_type']=='checkbox' && $formData['filter']['filter_data']=='sort'){
			$app->enqueueMessage(JText::sprintf('NOT_COMPAT_WITH_SORTING',JText::_('FIELD_CHECKBOX'),JText::_('FIELD_RADIO')));
			$filter->filter_type='radio';
		}
		if($formData['filter']['filter_type']=='multipledropdown' && $formData['filter']['filter_data']=='sort'){
			$app->enqueueMessage(JText::sprintf('NOT_COMPAT_WITH_SORTING',JText::_('FIELD_MULTIPLEDROPDOWN'),JText::_('FIELD_SINGLEDROPDOWN')));
			$filter->filter_type='singledropdown';
		}

		if(($formData['filter']['filter_type']=='checkbox' || $formData['filter']['filter_type'] == 'radio' || $formData['filter']['filter_type'] == 'cursor') && @$formData['filter']['title_position'] == 'inside') {
			$app->enqueueMessage(JText::_('NO_TITLE_INSIDE'));
			$filter->filter_options['title_position']='top';
		} else {
			$filter->filter_options['title_position']=$safeHtmlFilter->clean(@$formData['filter']['title_position'], 'string');
		}

		if(empty($formData['filter']['filter_currencies']) && $formData['filter']['filter_data'] == 'price' && $formData['filter']['filter_type'] != 'text' && $formData['filter']['filter_type'] != 'cursor') {
			$app->enqueueMessage(JText::_('NO_CURRENCY_SELECTED'));
			$formData['filter']['filter_currencies'] = array(0 => 1);
		}

		if(empty($formData['filter']['filter_category_id'])){
			$filter->filter_category_id = '';
		}

		if($formData['filter']['filter_data'] == 'custom_field') {
			$found = false;
			if(!empty($formData['filter']['custom_field'])) {
				$filters = '';
				if(!empty($formData['filter']['filter_category_id'])){
					if($formData['filter']['filter_category_childs']) {
						$categories_filter=array();
						$categoryClass = hikashop_get('class.category');
						$childs = $categoryClass->getChildren($formData['filter']['filter_category_id'],true);
						foreach($childs as $cat) {
							 $categories_filter[] = 'field_categories LIKE \'%,'.$cat->category_id.',%\'';
						}
						$filters=implode(' OR ', $categories_filter);
					}
					if(!is_array($formData['filter']['filter_category_id'])){
						$formData['filter']['filter_category_id'] = array($formData['filter']['filter_category_id']);
					}
					foreach($formData['filter']['filter_category_id'] as $category_id){
						if(!empty($filters)) {
							$filters .= 'OR ';
						}
						$filters .= 'field_categories LIKE \'%,'.(int)$category_id.',%\'';
					}
				}else{
					$found = true;
				}
				if(!empty($filters)){
					$filters .= ' OR ';
				}

				$database = JFactory::getDBO();
				$database->setQuery('SELECT * FROM '.hikashop_table('field').' WHERE ('.$filters.'field_categories LIKE "all") AND field_table IN ("product") AND field_published=1');
				$fields=$database->loadObjectList('field_realname');
				if(!empty($fields)){
					foreach($fields as $field){
						if($field->field_namekey==$formData['filter']['custom_field']){
							$filter->filter_options['custom_field']=$safeHtmlFilter->clean($formData['filter']['custom_field'], 'string');
							$found=true;
							break;
						}
					}
				}
			}
			if(!$found){
				$app->enqueueMessage(JText::_('CUSTOM_FIELD_NOT_AVAILABLE_FOR_CATEGORY'));
				if(!empty($fields)){
					$fields=reset($fields);
					$filter->filter_options['custom_field']=$fields->field_namekey;
				}else{
					$filter->filter_options['custom_field']='';
				}
			}
		}

		$filterValues = hikaInput::get()->get('filter_values', array(), 'array' );
		if(!empty($filterValues)){
			$filterValuesFinal=array();
			foreach($filterValues['value'] as $key => $value){
				if(is_numeric($value)){
					$filterValuesFinal[]= $safeHtmlFilter->clean($key, 'string').'::'.$safeHtmlFilter->clean($value, 'string');
				}
			}
			$filter->filter_value=implode("\n",$filterValuesFinal);
		}

		$filter->filter_options['column_width']=$safeHtmlFilter->clean($formData['filter']['column_width'], 'string');
		if(!empty($formData['filter']['filter_size'])){
			$filter->filter_options['filter_size']=$safeHtmlFilter->clean($formData['filter']['filter_size'], 'string');
		}else{
			$filter->filter_options['filter_size']=1;
		}
		if($formData['filter']['filter_type']=='cursor'){

			if(empty($formData['filter']['cursor_min'])){
				$formData['filter']['cursor_min']='0';
			}

			if(!strlen($formData['filter']['cursor_max']) || $formData['filter']['cursor_min']==$formData['filter']['cursor_max']){
				$app->enqueueMessage(JText::_('MIN_MAX_NOT_SET'), 'error');
				$problem = true;
			}

			$filter->filter_options['input']=(int)$formData['filter']['input'];
			$filter->filter_options['label_format']=$safeHtmlFilter->clean($formData['filter']['label_format'], 'string');
			$filter->filter_options['cursor_min']=$safeHtmlFilter->clean($formData['filter']['cursor_min'], 'string');
			$filter->filter_options['cursor_max']=$safeHtmlFilter->clean($formData['filter']['cursor_max'], 'string');
			$filter->filter_options['cursor_step']=$safeHtmlFilter->clean($formData['filter']['cursor_step'], 'string');
			$filter->filter_options['cursor_effect']=$safeHtmlFilter->clean($formData['filter']['cursor_effect'], 'string');
			$filter->filter_options['cursor_width']=$safeHtmlFilter->clean($formData['filter']['cursor_width'], 'string');
			$filter->filter_options['cursor_width']=$safeHtmlFilter->clean($formData['filter']['cursor_width'], 'string');
			$filter->filter_options['title_position']=$safeHtmlFilter->clean($formData['filter']['title_position_cursor'], 'string');
		}
		if(!empty($formData['filter']['defined_limits'])){
			$filter->filter_options['defined_limits']=$safeHtmlFilter->clean(@$formData['filter']['defined_limits'], 'string');
		}
		if($formData['filter']['filter_data']=='price'){
			$filter->filter_options['currencies']=$safeHtmlFilter->clean(@$formData['filter']['filter_currencies'], 'array');
		}
		if($formData['filter']['filter_type']=='text'){
			$filter->filter_options['max_char']=$safeHtmlFilter->clean($formData['filter']['max_char'], 'string');
			$filter->filter_options['textBoxSize']=$safeHtmlFilter->clean($formData['filter']['textBoxSize'], 'string');
			$filter->filter_options['searchProcessing']=$safeHtmlFilter->clean($formData['filter']['searchProcessing'], 'string');
		}

		if($formData['filter']['filter_data']=='characteristic'){
			$filter->filter_options['filter_charac']=$safeHtmlFilter->clean($formData['filter']['filter_charac'], 'string');
		}

		if($formData ['filter']['filter_data']=='information' || $formData ['filter']['filter_type']=='cursor'){
			if(($formData['filter']['product_information']=='weight' && $formData ['filter']['filter_type']!='cursor') || (($formData['filter']['filter_data_cursor']=='weight' && $formData ['filter']['filter_type']=='cursor')) ){
				$filter->filter_options['information_unit']=$safeHtmlFilter->clean($formData['filter']['weight_unit'], 'string');
			}else{
				$filter->filter_options['information_unit']=$safeHtmlFilter->clean($formData['filter']['dimension_unit'], 'string');
			}
		}

		if($formData ['filter']['filter_type']!='cursor'){
			$filter->filter_options['product_information']=$safeHtmlFilter->clean($formData['filter']['product_information'], 'string');
		}else{
			$filter->filter_options['product_information']=$safeHtmlFilter->clean($formData['filter']['filter_data'], 'string');
		}
		$filter->filter_options['button_align'] = $safeHtmlFilter->clean($formData['filter']['button_align'], 'string');
		$filter->filter_options['logic'] = $safeHtmlFilter->clean($formData['filter']['logic'], 'string');

		if($formData['filter']['filter_data']=='sort'){
			if(isset($formData['filter']['sort_by'])){
				$filter->filter_options['sort_by']=$safeHtmlFilter->clean($formData['filter']['sort_by'], 'array');
			}else{
				$app->enqueueMessage(JText::_('CHOOSE_SORT_OPTIONS'), 'error');
				$problem = true;
			}
		}
		$filter->filter_options['parent_category_id']=$safeHtmlFilter->clean($formData['filter']['parent_category_id'], 'string');

		unset($filter->column_width);
		unset($filter->title_position);
		unset($filter->filter_size);
		unset($filter->cursor_number);
		unset($filter->label_format);
		unset($filter->cursor_min);
		unset($filter->cursor_max);
		unset($filter->filter_currencies);
		unset($filter->max_char);
		unset($filter->defined_limits);
		unset($filter->filter_charac);
		unset($filter->sort_by);
		unset($filter->product_information);
		unset($filter->custom_field);
		unset($filter->filter_data_text);
		unset($filter->filter_data_cursor);
		unset($filter->button_align);
		unset($filter->dimension_unit);
		unset($filter->weight_unit);
		unset($filter->information_unit);
		unset($filter->parent_category_id);
		unset($filter->textBoxSize);
		unset($filter->cursor_step);
		unset($filter->cursor_effect);
		unset($filter->cursor_width);
		unset($filter->title_position_cursor);
		unset($filter->searchProcessing);
		unset($filter->logic);
		unset($filter->attribute);
		unset($filter->input);

		if($problem){
			hikaInput::get()->set( 'fail', $filter  );
		}else{
			$status = $this->save($filter);

			if($new && $status){
				$orderHelper = hikashop_get('helper.order');
				$orderHelper->pkey = 'filter_id';
				$orderHelper->table = 'filter';
				$orderHelper->orderingMap = 'filter_ordering';
				$orderHelper->reOrder();
			}
			hikaInput::get()->set( 'filter_id', $filter->filter_id);
			return $status;
		}
		return false;
	}
	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeFilterDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterFilterDelete', array(&$elements));
		}
		return $status;
	}

	function save(&$element){
		if(isset($element->filter_options) && is_array($element->filter_options)){
			$element->filter_options=serialize($element->filter_options);
		}

		$isNew = empty($element->filter_id);
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		if($isNew) {
			$app->triggerEvent('onBeforeFilterCreate', array( &$element, &$do ));
		} else {
			$app->triggerEvent('onBeforeFilterUpdate', array( &$element, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return $status;

		if($isNew) {
			$app->triggerEvent('onAfterFilterCreate', array( &$element ));
		} else {
			$app->triggerEvent('onAfterFilterUpdate', array( &$element ));
		}
		return $status;
	}

	function getFilters($category_id){
		$database = JFactory::getDBO();
		$conditions = array('filter_published = 1');
		hikashop_addACLFilters($conditions,'filter_access');
		$query='SELECT * FROM '.hikashop_table('filter').' WHERE '.implode(' AND ',$conditions).' ORDER BY filter_ordering';
		$database->setQuery($query);
		$filters=$database->loadObjectList();

		$filterList=array();
		foreach($filters as $filter){
			$filter->filter_options = hikashop_unserialize($filter->filter_options);
			$filter->filter_data = hikashop_unserialize($filter->filter_data);
			if(!empty($filter->filter_value)){
				$filter->filter_value=explode("\n", $filter->filter_value);
				foreach($filter->filter_value as $key => $val){
					$temp=explode("::", $val);
					$filter->filter_value[$key]=$temp[1];
				}
				sort($filter->filter_value);
			}
		}

		if(empty($category_id))
			return $filters;

		$categoryClass = hikashop_get('class.category');
		$parents = $categoryClass->getParents($category_id);
		foreach($filters as $filter){
			if(empty($filter->filter_category_id)){
				$filterList[]=$filter;
				continue;
			}

			$filter->filter_category_id = explode(',',$filter->filter_category_id);
			if(in_array($category_id,$filter->filter_category_id)){
				$filterList[]=$filter;
				continue;
			}
			if($filter->filter_category_childs!=1){
				continue;
			}

			foreach($parents as $parent){
				if(in_array($parent->category_id,$filter->filter_category_id)){
					$filterList[]=$filter;
					break;
				}
			}
		}

		return $filterList;
	}

	function displayFilter($filter, $divName, &$parent, $datas=''){
		if(isset($filter->datas))
			$datas = $filter->datas;
		$html ='';
		if($filter->filter_type=='text' || $filter->filter_type=='singledropdown' || $filter->filter_type=='multipledropdown' || $filter->filter_type=='radio' || $filter->filter_type=='list' || $filter->filter_type=='checkbox' || $filter->filter_type=='cursor' || $filter->filter_type=='instockcheckbox'){
			$classType = 'hikashop'.ucfirst($filter->filter_type).'Class';
			$class = new $classType();
			$html = $class->display($filter, $divName, $parent, $datas);
			$this->canBeUsed = $class->canBeUsed;
		}else{
			JPluginHelper::importPlugin( 'hikashop' );
			$app = JFactory::getApplication();
			$app->triggerEvent( 'onFilterDisplay', array( & $filter ,&$html, &$divName, &$parent, &$datas) );
		}
		return $html;
	}

	function canBeUsed() {
		return $this->canBeUsed;
	}

	function addFilter(&$filter,&$filters,&$select,&$select2,&$a,&$b,&$on,&$order,&$parent, $divName){
		if($filter->filter_type=='text' || $filter->filter_type=='singledropdown' || $filter->filter_type=='multipledropdown' || $filter->filter_type=='radio' || $filter->filter_type=='list' || $filter->filter_type=='checkbox' || $filter->filter_type=='cursor' || $filter->filter_type=='instockcheckbox'){
			$classType = 'hikashop'.ucfirst($filter->filter_type).'Class';
			$class = new $classType();
			$html = $class->addFilter($filter,$filters,$select,$select2,$a,$b,$on,$order,$parent, $divName);
		}else{
			JPluginHelper::importPlugin( 'hikashop' );
			$app = JFactory::getApplication();
			$app->triggerEvent( 'onFilterAdd', array( & $filter ,&$filters, &$select, &$select2, &$a, &$b, &$on, &$order, &$divName, &$parent) );
		}
	}

	function getFieldToLoad($filter, $divName, &$parent){
		$html ='';
		if($filter->filter_type=='text' || $filter->filter_type=='singledropdown' || $filter->filter_type=='multipledropdown' || $filter->filter_type=='radio' || $filter->filter_type=='list' || $filter->filter_type=='checkbox' || $filter->filter_type=='cursor' || $filter->filter_type=='instockcheckbox'){
			$classType = 'hikashop'.ucfirst($filter->filter_type).'Class';
			$class = new $classType();
			$html = $class->getFieldToLoad($filter, $divName, $parent);
		}else{
			JPluginHelper::importPlugin( 'hikashop' );
			$app = JFactory::getApplication();
			$app->triggerEvent( 'onFilterToLoad', array( & $filter ,&$html, &$divName, &$parent) );
		}
		return $html;
	}

	function getProductList(&$parent, &$filters, $currentFilter = null){
		$fields = array();
		$fieldsLimits = array();
		$datas = array();
		$copy_query = $parent->listingQuery;
		if(!is_null($currentFilter) && isset($filters[$currentFilter]) && !empty($filters[$currentFilter]->queryBeforeFiltering)) {
			$copy_query = $filters[$currentFilter]->queryBeforeFiltering;
		}
		if(!empty($filters)){
			foreach($filters as $k => $filter){
				if(!is_null($currentFilter) && isset($filters[$currentFilter])) {
					if($currentFilter != $k)
						continue;
				}
				$fields[$filter->filter_namekey]=$this->getFieldToLoad($filter, $parent->params->get('main_div_name'), $parent);
				if($filter->filter_data=='category' && strpos($copy_query,' '.hikashop_table('product_category').' AS a')===false){
					$p = strrpos($copy_query, 'WHERE');
					if($p !== false)
						$copy_query = substr_replace($copy_query, 'LEFT JOIN '.hikashop_table('product_category').' AS a ON b.product_id=a.product_id ', $p, 0);
				}

				if($filter->filter_type=='cursor' && $filter->filter_data=='price'){

					$product_table = 'b';
					if(preg_match('#hikashop_price AS ([a-z0-9_]+)#i',$copy_query,$matches)){
						if(preg_match('#price[0-9]+#',$matches[1],$results)){
							$matches[1] = 'b';
						}
						$table = $matches[1];
						$fields[$filter->filter_namekey]=$table.'.price_value';
					}elseif(strpos($copy_query,' '.hikashop_table('price').' AS pprice')===false){
						$table = 'pprice';
						if(preg_match('#hikashop_product AS ([a-z0-9_]+)#i',$copy_query,$matches)){
							$product_table = $matches[1];
						}
						$replace = 'LEFT JOIN '.hikashop_table('price').' AS pprice ON '.$product_table.'.product_id=pprice.price_product_id WHERE';
						$conditions = array();
						hikashop_addACLFilters($conditions, 'price_access', 'pprice', 2, true);
						$conditions[] = "(pprice.price_users = '' OR pprice.price_users LIKE '%,".(int)hikashop_loadUser(false).",%')";
						if(count($conditions))
							$replace .= ' '.$conditions[0].' AND';
						$copy_query = str_replace('WHERE', $replace, $copy_query);
					}

					$currencyClass = hikashop_get('class.currency');
					$rounding = $currencyClass->getRounding(hikashop_getCurrency());
					$typeClass = new hikashopFilterTypeClass();
					$select = $typeClass->getPriceSelect($table,$product_table);
					$fieldsLimits[] = 'round(min('.$select.'),'.$rounding.') as min_'.$filter->filter_namekey;
					$fieldsLimits[] = 'round(max('.$select.'),'.$rounding.') as max_'.$filter->filter_namekey;
					$fields[$filter->filter_namekey]='';
				}
			}
		}

		if(!empty($fields)){
			foreach($fields as $key => $field){
				if(empty($field)){
					unset($fields[$key]);
				}else{
					$fields[$key]=$field;
					$fieldsLimits[]='max(cast('.$field.' AS DECIMAL(12,3))) as max_'.$key.'';
					$fieldsLimits[]='min(cast('.$field.' AS DECIMAL(12,3))) as min_'.$key.'';
				}
			}
			$fields=implode(', ', $fields);
			if(!empty($fields)){ $fields=','.$fields; }
		}else{
			$fields = '';
		}
		if(!empty($fieldsLimits)){
			$fieldsLimits=implode(', ', $fieldsLimits);
			$fieldsLimits=','.$fieldsLimits;
		}else{
			$fieldsLimits='';
		}

		$database = JFactory::getDBO();
		$query='SELECT DISTINCT b.product_id'.$fields.' '.$copy_query;
		$database->setQuery($query);
		$datas['products']=$database->loadObjectList();
		$query='SELECT DISTINCT b.product_id'.$fieldsLimits.' '.$copy_query;
		$database->setQuery($query);
		$datas['limits']=$database->loadObject();

		if(!is_null($currentFilter) && isset($filters[$currentFilter]))
			$filters[$currentFilter]->datas = $datas;
		return $datas;
	}

	function cleanFilter($filter){
		if($filter->filter_type!='cursor' && $filter->filter_type!='instockcheckbox' && $filter->filter_data=='price' ){
			$currency = hikashop_getCurrency();
			if(!empty($filter->filter_options['currencies'])){
				if(is_array($filter->filter_options['currencies']) && !in_array($currency, $filter->filter_options['currencies']))
					return false;
				if(!is_array($filter->filter_options['currencies']) && $currency != $filter->filter_options['currencies'])
					return false;
			}
			if(empty($filter->filter_value)){
				return false;
			}
		}
		return true;
	}


	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$fullLoad = false;
		$displayFormat = !empty($options['displayFormat']) ? $options['displayFormat'] : @$typeConfig['displayFormat'];

		$start = (int)@$options['start']; // TODO
		$limit = (int)@$options['limit'];
		$page = (int)@$options['page'];
		if($limit <= 0)
			$limit = 50;

		$select = array('f.*');
		$where = array();

		if(!empty($search)) {
			$searchMap = array('f.filter_id', 'f.filter_name', 'f.filter_type');
			if(!HIKASHOP_J30)
				$searchVal = '\'%' . $this->db->getEscaped(HikaStringHelper::strtolower($search), true) . '%\'';
			else
				$searchVal = '\'%' . $this->db->escape(HikaStringHelper::strtolower($search), true) . '%\'';
			$where['search'] = '('.implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal.')';
		}

		$order = ' ORDER BY f.filter_id DESC';

		if(count($where))
			$where = ' WHERE ' . implode(' AND ', $where);
		else
			$where = '';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . hikashop_table('filter').' AS f' . $where . $order;
		$this->db->setQuery($query, $page, $limit);

		$ret[0] = $this->db->loadObjectList('filter_namekey');

		if(count($ret[0]) < $limit)
			$fullLoad = true;

		if(!empty($value)) {
			if(is_array($value)) {
				$values = [];
				foreach($value as $v) {
					$values[] = $this->db->quote($v);
				}
				$where = ' WHERE f.filter_namekey IN ('. implode(',', $values).')';
			}else {
				$where = ' WHERE f.filter_namekey = '. $this->db->quote($value);
			}
			$query = 'SELECT '.implode(', ', $select) . ' FROM ' . hikashop_table('filter').' AS f' . $where;
			$this->db->setQuery($query, $page, $limit);
			$ret[1] = $this->db->loadObjectList('filter_namekey');
			foreach($ret[1] as $k => $v) {
				if(!isset($ret[0][$k]))
					$ret[0][$k] = $v;
			}

		}
		return $ret;
	}

}

class hikashopFilterTypeClass extends hikashopClass {

	var $receivedFilter = '';
	var $parent = null;
	var $canBeUsed = true;

	function display(&$filter, $divName, &$parent, $datas = ''){
		$this->parent = $parent;
		$app = JFactory::getApplication();
		$cid = hikaInput::get()->getInt("cid",'itemid_'.hikaInput::get()->getInt("Itemid",0));
		$filter->page_key = 'page_'.$cid;

		$completion = '';
		if(hikaInput::get()->getVar('reseted')==1){
			$app->setUserState('com_hikashop.'.$cid.'_filter_'.$filter->filter_namekey.$completion, '');
			return array();
		}
		if(hikaInput::get()->getVar('filtered')==1){
			$infoGet = hikaInput::get()->getVar('filter_'.$filter->filter_namekey.$completion);
			$app->setUserState('com_hikashop.'.$cid.'_filter_'.$filter->filter_namekey.$completion, $infoGet);
		}else{
			$infoGet = $app->getUserStateFromRequest( 'com_hikashop.'.$cid.'_filter_'.$filter->filter_namekey.$completion, 'filter_'.$filter->filter_namekey.$completion);
		}

		if(!empty($infoGet)){
			$copyGet=$infoGet[0];
			$result=explode(',',$copyGet);
			if(!($result[0]==$infoGet[0])){
				foreach($result as $key => $res){
					$infoGet[$key]=$res;
				}
			}
		}
		return $infoGet;
	}

	function isActive(&$data) {
		if(empty($data))
			return false;
		if(is_string($data)) {
			if(!in_array(trim($data), array('', 'none', '::')))
				return true;
			return false;
		}

		$active = false;
		foreach($data as $d) {
			if(is_string($d) && !in_array(trim($d), array('', 'none', '::')))
				$active = true;
		}
		return $active;
	}

	function canBeUsed() {
		return $this->canBeUsed;
	}

	function addFilter(&$filter,&$filters,&$select,&$select2, &$a,&$b,&$on,&$order,&$parent, $divName){
		static $i = 0;
		$i++;
		$app = JFactory::getApplication();
		$database = JFactory::getDBO();
		$infoGet= array();
		$config = hikashop_config();

		static $pagination_reset = false;

		if(!$pagination_reset){
			$new_filter = hikaInput::get()->getVar('filter_'.$filter->filter_namekey,null);
			$new_filter_with_post=false;
			if(isset($_POST['filter_'.$filter->filter_namekey])){
				$new_filter_with_post=true;
			}
			$cid = hikaInput::get()->getInt("cid",'itemid_'.hikaInput::get()->getInt("Itemid",0));
			$oldValue = $app->getUserState('com_hikashop.'.$cid.'_filter_'.$filter->filter_namekey, '');

			if(!is_null($new_filter) && $new_filter!=$oldValue && $new_filter_with_post){
				$moduleHelper = hikashop_get('helper.module');
				$moduleHelper->initialize($this);
				$data = $this->params->get('data', false);
				if($data === false) {
					$data = new stdClass();
					$data->hk_product = $this->params->get('hk_product', false);
					if(!empty($data->hk_product))
						$data->hk_product = (object)$data->hk_product;
				}
				if(isset($data->hk_product) && is_object($data->hk_product) && !empty($data->hk_product->category)) {
					$this->params->set('selectparentlisting', (int)$data->hk_product->category);
				}
				$cid = hikaInput::get()->getInt("cid",$this->params->get('selectparentlisting'));
				if($this->params->get('content_type')!='product'){
					$base = 'limitstart_hikashop_category_information_module_';
					foreach($this->modules as $module){
						hikaInput::get()->set($base.$module->id.'_'.$cid,0);
						$_POST[$base.$module->id.'_'.$cid] = 0;
						hikaInput::get()->set($base.$module->id,0);
						$_POST[$base.$module->id] = 0;
					}
					hikaInput::get()->set($base.'_'.$cid,0);
					$_POST[$base.'_'.$cid] = 0;
				}else{
					hikaInput::get()->set('limitstart_'.$this->params->get('main_div_name').'_'.$cid,0);
					$_POST['limitstart_'.$this->params->get('main_div_name').'_'.$cid] = 0;
					hikaInput::get()->set('limitstart_'.$this->params->get('main_div_name'),0);
					$_POST['limitstart_'.$this->params->get('main_div_name')] = 0;
				}

				$pagination_reset=true;
			}
		}

		$cid = hikaInput::get()->getInt("cid",'itemid_'.hikaInput::get()->getInt("Itemid",0));
		$filter->page_key = 'page_'.$cid;
		if(hikaInput::get()->getVar('filtered')==1 || $config->get('redirect_post',0)){
			$infoGet = hikaInput::get()->getVar('filter_'.$filter->filter_namekey);
			$app->setUserState('com_hikashop.'.$cid.'_filter_'.$filter->filter_namekey, $infoGet);
		}else{
			$infoGet = $app->getUserStateFromRequest( 'com_hikashop.'.$cid .'_filter_'.$filter->filter_namekey, 'filter_'.$filter->filter_namekey, $infoGet, 'array' );
		}

		if(hikaInput::get()->getVar('reseted')==1 || (is_string($infoGet) && $infoGet=='filter_'.$filter->filter_namekey) || (isset($infoGet[0]) && $infoGet[0]=='filter_'.$filter->filter_namekey)){
			$app->setUserState('com_hikashop.'.$cid.'_filter_'.$filter->filter_namekey, '');
			unset($_SESSION['hk_filter'][$filter->filter_id][$filter->page_key]);
			return array();
		}

		if(!is_array($infoGet)){
			$infoGet = array($infoGet);
		}

		if(isset($infoGet[0])){
 			$infoGet[0] = trim($infoGet[0]);
 		}

		if(!isset($infoGet) || count($infoGet) == 0 || !isset($infoGet[0]) || (empty($infoGet[0]) && !strlen($infoGet[0])))
			return false;

		$parent->filter_set = true;

		if($filter->filter_type=='instockcheckbox' && $infoGet[0]=='in_stock'){
			$filters[]=' b.product_quantity!=0 ';

			$query = 'SELECT pr.product_id'.
			' FROM '.hikashop_table('product_related').' AS pr '.
			' INNER JOIN '.hikashop_table('product').' AS p ON pr.product_related_id = p.product_id '.
			' WHERE pr.product_related_type = ' . $this->db->Quote('bundle') . ' AND (p.product_quantity = 0 OR p.product_quantity < pr.product_related_quantity)';
			$database->setQuery($query);
			$out_of_stock_bundled_products = $database->loadObjectList('product_id');
			if(!empty($out_of_stock_bundled_products) && count($out_of_stock_bundled_products)) {
				$filters[]=' b.product_id NOT IN ('.implode(',', array_keys($out_of_stock_bundled_products)).')';
			}
			return true;
		}

		if($filter->filter_type=='text'){
			if($filter->filter_name == $infoGet[0]){
				return false;
			}
			if(!empty($filter->filter_options['max_char']) && strlen($infoGet[0])>$filter->filter_options['max_char']){
				$app->enqueueMessage(JTEXT::_( 'TEXT_ENTERED_TOO_LONG' ));
				return false;
			}
			if(empty($filter->filter_data) || $filter->filter_data[0]=='all'){
				$query = 'SELECT * FROM '.hikashop_table('product').' LIMIT 1';
				$database->setQuery($query);
				$object = $database->loadObject();
				foreach($object as $key => $obj){
					$searchField[]=$key;
				}
			}else{
				$searchField=$filter->filter_data;
			}
			$list=array();
			$translationFilters=array();
			$translationList=array();
			$fields=array();
			$searchProcessing='any';

			if(isset($filter->filter_options['searchProcessing']))
				$searchProcessing=$filter->filter_options['searchProcessing'];

			$translationHelper = hikashop_get('helper.translation');
			$multi = $translationHelper->isMulti(true);
			if($multi && !$translationHelper->falang) {
				jimport('joomla.filesystem.folder');
				$path = hikashop_getLanguagePath(JPATH_ROOT);
				$lg = JFactory::getLanguage();
				$language_code = $lg->getTag();
				$override_file_path = $path . '/overrides/'.$language_code.'.override.ini';
				$overrides = array();
				if(file_exists($override_file_path)) {
					$overrides = parse_ini_file($override_file_path);
				}
			}

			$terms=$infoGet[0];
			if($searchProcessing=='exact'){
				if(!empty($terms) || strlen($terms)){
					foreach($searchField as $column){
						$list[]=' b.'.$column.' LIKE \''.hikashop_getEscaped($terms, true).'\'';
						$fields[] = ' reference_field = \''.$column.'\' ';
					}
					if($multi) {
						if($translationHelper->falang) {
							$translationList[] = 'value LIKE \''.hikashop_getEscaped($terms, true).'\'';
							$translationFilters =  '('.implode(' OR ', $translationList).')';
						} else {
							foreach($overrides as $k => $v) {
								if(strpos($v, $terms) !== false) {
									foreach($searchField as $column){
										$list[] =' b.'.$column.' = '.$database->Quote($k);
									}
								}
							}
						}
					}
					$filters[]='('.implode(' OR ', $list).')';
				}
			}else{
				if(!isset($filter->filter_options['searchProcessing'])) $filter->filter_options['searchProcessing']='any';
				if($filter->filter_options['searchProcessing']=='operators'){
					$searchProcessing='any';
					if(preg_match('*. .*',$infoGet[0])){
						$searchProcessing='any';
					}
					if(preg_match('*.\+.*',$infoGet[0])){
						$terms=str_replace('+',' ',$terms);
						$searchProcessing='every';
					}
					if(preg_match('#^(["\']).*\1$#',$infoGet[0])){
						$searchProcessing='complete';
						$terms=trim($terms, '"');
					}
				}

				if($searchProcessing == 'complete'){
					$terms = array($terms);
				}else{
					$terms = explode(' ',$terms);
				}
				if(@$searchProcessing != 'any'){
					if($multi && !$translationHelper->falang) {
						foreach($overrides as $k => $v) {
							$found_all = true;
							foreach($terms as $term){
								if(empty($term) && !strlen($term))
									continue;
								if(strpos($v, $term) === false)
									$found_all = false;
							}
							if($found_all) {
								foreach($searchField as $column){
									$list[] =' b.'.$column.' = '.$database->Quote($k);
								}
							}
						}
					}
					foreach($searchField as $column){
						$array =array();
						foreach($terms as $term){
							if(empty($term) && !strlen($term))
								continue;
							$array[]=' b.'.$column.' LIKE \'%'.hikashop_getEscaped($term, true).'%\' ';
							if($multi) {
								if($translationHelper->falang) {
									$translationList[] = 'value LIKE \'%'.hikashop_getEscaped($term, true).'%\'';
								}
							}
						}
						$list[]='('.implode(' AND ', $array).')';
						if($multi) {
							if($translationHelper->falang) {
								$fields[] = ' reference_field = \''.$column.'\' ';
							}
						}
					}
					$filters[]='('.implode(' OR ', $list).')';
					if($multi) {
						if($translationHelper->falang) {
							$translationFilters =  '('.implode(' AND ', $translationList).')';
						}
					}
				}else{
					foreach($terms as $term){
						if(empty($term) && !strlen($term))
							continue;
						foreach($searchField as $column){
							$list[]=' b.'.$column.' LIKE \'%'.hikashop_getEscaped($term, true).'%\' ';
							$fields[] = ' reference_field = \''.$column.'\' ';
						}

						if($multi) {
							if($translationHelper->falang) {
								$translationList[] = 'value LIKE \'%'.hikashop_getEscaped($term, true).'%\'';
							}
						} else {
							foreach($overrides as $k => $v) {
								if(strpos($v, $term) !== false) {
									foreach($searchField as $column){
										$list[] =' b.'.$column.' = '.$database->Quote($k);
									}
								}
							}
						}
					}
					$filters[]='('.implode(' OR ', $list).')';
					$translationFilters =  '('.implode(' OR ', $translationList).')';
				}
			}

			if($multi) {
				if($translationHelper->falang) {
					$trans_table = 'falang_content';
					$query = 'SELECT DISTINCT reference_id FROM '.hikashop_table($trans_table,false).' WHERE reference_table=\'hikashop_product\' AND '.$translationFilters.' AND ('.implode(' OR ', $fields).') AND published=1 ORDER BY reference_id ASC';
					$this->database->setQuery($query);
					$translatedProducts = $database->loadColumn();

					if(!empty($translatedProducts)){
						$filters[] = '('.array_pop($filters).' OR b.product_id IN ('.implode(',', $translatedProducts).'))';
					}
				}
			}

			return true;
		}

		if($filter->filter_data=='category'){
			if(!($infoGet[0] == $filter->filter_namekey && count($infoGet) == 1)){
				$categoriesList='';
				foreach($infoGet as $cat){
					if($cat == $filter->filter_namekey || (int)$cat == 0)
						continue;

					if(is_numeric($cat)){
						$categoriesList.=(int)$cat.',';
					}elseif(is_string($cat)){
						$cat = explode('::',$cat);
						foreach($cat as $selectedCategory){
							$selectedCategory = trim($selectedCategory);
							if(!empty($selectedCategory))
								$categoriesList.=(int)$selectedCategory.',';
						}
					}
				}
				if(!empty($categoriesList)){
					$categoriesList=substr($categoriesList,0,-1);

					$table = 'a';
					if($a=='#__hikashop_product AS b'){
						$table = 'b';
					}elseif($a[0]!='(' && strpos($a,') AS b') && preg_match('#hikashop_product AS ([a-z0-9_]+)#i',$a,$matches)){
						$table = $matches[1];
					}
					$filter->filter_namekey = str_replace('-', '_', $filter->filter_namekey);
					$on.=' INNER JOIN '.hikashop_table('product_category').' AS '.$filter->filter_namekey.$i.' ON '.$filter->filter_namekey.$i.'.product_id='.$table.'.product_id AND '.$filter->filter_namekey.$i.'.category_id IN ('.$categoriesList.')';
				}
			}
		}

		if(($filter->filter_data=='information'||$filter->filter_data=='price') && $filter->filter_type != 'cursor'){
			$limit=array();
			$type=$filter->filter_options['product_information'];
			if($filter->filter_data=='price') $type='price';
			$case='( '.$this->getUnitSelect($filter, $type,$i).' )';
			$copyGet=$infoGet[0];
			$result=explode(',',$copyGet);
			if(!($result[0]==$infoGet[0])){
				foreach($result as $key => $res){
					$infoGet[(int)$key]=(int)$res;
				}
			}
			foreach($infoGet as $slice){
				$values=explode('::', $slice);
				if($config->get('redirect_post')){
					$elements = array_chunk($values, 2);
				}else{
					$elements = array($values);
				}
				foreach($elements as $values){
					$values[0] = trim($values[0]);
					$values[1] = trim(@$values[1]);
					if(empty($values[0]) && empty($values[1]))
						continue;
					if(empty($values[0])){ $limit[]=' '.$case.' <= '.(int)$values[1].' '; }
					else if(empty($values[1])){ $limit[]=' '.$case.' >= '.(int)$values[0].' '; }
					else{ $limit[]=' '.$case.' BETWEEN '.(int)$values[0].' AND '.(int)$values[1].' '; }
				}
			}
			$filters[]=' ('.implode('OR', $limit).') ';
		}

		if($filter->filter_data=='characteristic'){
			if(($infoGet[0] != $filter->filter_namekey && (int)$infoGet[0] != 0) || count($infoGet) > 1) {
				$charList = array();
				foreach($infoGet as $charac){
					$charac = explode('::',$charac);
					foreach($charac as $selectedValue){
						$selectedValue = trim($selectedValue);
						if(!empty($selectedValue))
							$charList[] = (int)$selectedValue;
					}
				}
				if(count($charList)) {
					if(strpos($on,' LEFT JOIN '.hikashop_table('product').' AS c ON b.product_id=c.product_parent_id')===false){
						$on.=' LEFT JOIN '.hikashop_table('product').' AS c ON b.product_id=c.product_parent_id';
					}
					$filter->filter_namekey = str_replace('-', '_', $filter->filter_namekey);
					$on.=' LEFT JOIN '.hikashop_table('variant').' AS '.$filter->filter_namekey.$i.' ON '.$filter->filter_namekey.$i.'.variant_product_id=c.product_id';
					$filters[]=' '.$filter->filter_namekey.$i.'.variant_characteristic_id IN ('.implode(',', $charList).') ';
					$filters[]=' c.product_quantity!=0 ';
					$filters[]=' c.product_published > 0 ';
				}
			}
		}

		if($filter->filter_data == 'quantity' && !($infoGet[0] == $filter->filter_namekey && count($infoGet) == 1)) {
			$filters[] = ' b.product_quantity!=0 ';
		}

		if($filter->filter_data=='custom_field'){
			if(empty($filter->filter_value)){
				if(!($infoGet[0]==$filter->filter_namekey && count($infoGet)==1)){
					$fieldsList=array();
					foreach($infoGet as $field){
						if(!empty($field) && $field!=$filter->filter_namekey && $field!=' ' && $field!=$filter->filter_namekey && $field!='none'){
							$selectedValues = explode('::',$field);
							foreach($selectedValues as $oneValue){
								$oneValue = trim($oneValue);
								if(empty($oneValue))
									continue;
								$fieldCondition = '( b.'.$filter->filter_options['custom_field'].' =  '.$database->Quote($oneValue);
								$fieldCondition .= 'OR b.'.$filter->filter_options['custom_field'].' LIKE \''.hikashop_getEscaped($oneValue,true).',%\'';
								$fieldCondition .= 'OR b.'.$filter->filter_options['custom_field'].' LIKE \'%,'.hikashop_getEscaped($oneValue,true).'\'';
								$fieldCondition .= 'OR b.'.$filter->filter_options['custom_field'].' LIKE \'%,'.hikashop_getEscaped($oneValue,true).',%\' )';
								$fieldsList[] = $fieldCondition;
							}
						}
					}
					if(!empty($fieldsList) && count($fieldsList)){
						$logic = ' OR ';
						if(!empty($filter->filter_options['logic']) && $filter->filter_options['logic'] == 'AND') {
							$logic = ' AND ';
						}
						$fieldsList = '('.implode($logic, $fieldsList).')';
						$filters[] = $fieldsList;
					}
				}
			}else{
				$limit=array();
				foreach($infoGet as $slice){
					$values=explode('::', $slice);
					if($config->get('redirect_post')){
						$elements = array_chunk($values, 2);
					}else{
						$elements = array($values);
					}
					foreach($elements as $values){
						$values[0] = trim($values[0]);
						$values[1] = trim($values[1]);
						if(empty($values[0]) && empty($values[1]))
							continue;
						if(empty($values[0])){ $limit[]=' b.'.$filter->filter_options['custom_field'].' <= '.(int)$values[1].' '; }
						else if(empty($values[1])){ $limit[]=' b.'.$filter->filter_options['custom_field'].' >= '.(int)$values[0].' '; }
						else{ $limit[]=' b.'.$filter->filter_options['custom_field'].' BETWEEN '.(int)$values[0].' AND '.(int)$values[1].' '; }
					}
				}
				if(!empty($limit) && count($limit)) {

					$logic = ' OR ';
					if(!empty($filter->filter_options['logic']) && $filter->filter_options['logic'] == 'AND') {
						$logic = ' AND ';
					}
					$filters[]=' ('.implode($logic, $limit).') ';
				}
			}
		}

		$infos=explode('--',$infoGet[0]);
		$sort_by_price=false;
		if(isset($filter->filter_options['sort_by'])){
			if(!is_array($filter->filter_options['sort_by'])){
				$temp = $filter->filter_options['sort_by'];
				$filter->filter_options['sort_by'] = array();
				$filter->filter_options['sort_by'][] = $temp;
			}
			foreach($filter->filter_options['sort_by'] as $sort_by) {
				if(in_array($sort_by, array('price', 'price--lth', 'price--htl')))
					$sort_by_price = true;
			}
			$sort_by_price = $sort_by_price && ($infos[0] == 'price');
		}
		if(isset($infos[1]) && ($filter->filter_data=='sort' && $sort_by_price && (($infos[1]=='lth') || ($infos[1]=='htl')))|| $filter->filter_data=='price'){
			$subfilters = array();
			$where = '';
			hikashop_addACLFilters($subfilters,'price_access','price'.$i,2,true);
			$subfilters[] = "(ISNULL(price".$i.".price_users) OR price".$i.".price_users = '' OR price".$i.".price_users LIKE '%,".(int)hikashop_loadUser(false).",%')";
			$subfilters[]='product'.$i.'.product_type=\'main\'';
			$where = ' WHERE '.implode(' AND ',$subfilters);
			$subquery ='SELECT * FROM '.hikashop_table('product').' AS product'.$i.' LEFT JOIN '.hikashop_table('price').' AS price'.$i.' ON product'.$i.'.product_id=price'.$i.'.price_product_id '.$where.' GROUP BY product'.$i.'.product_id ORDER BY price'.$i.'.price_min_quantity ASC';
			$a = '('.$subquery.') AS b';
		}

		if($filter->filter_data=='sort' && isset($infoGet[0]) && $infoGet[0]!='none'){
			$type = hikashop_secureField($infos[0]);
			$case = $this->getUnitSelect($filter, $type, $i);
			if(!empty($case)){
				$f = $database->quoteName('product_'.$type.'_normalized');
				$select2.=' ,'.$case.' as '.$f;
				if($infos[1]=='lth'){
					$order= ' ORDER BY '.$f.' * 1 ASC';
				}elseif($infos[1]=='htl'){
					$order= ' ORDER BY '.$f.' * 1 DESC';
				}
			}else{
				$type = $database->quoteName($type);
				if($infos[1]=='lth'){
					$order= ' ORDER BY '.$type.' ASC';
				}elseif($infos[1]=='htl'){
					$order= ' ORDER BY '.$type.' DESC';
				}
			}
		}

		if($filter->filter_data=='manufacturers'){
			if(!(in_array($infoGet[0],array($filter->filter_namekey,0)) && count($infoGet)==1)){
				$manufacturerList=array();

				foreach($infoGet as $manufacturer_id){
					$manufacturer_id = explode('::',$manufacturer_id);
					foreach($manufacturer_id as $id){
						$id = trim($id);
						if(empty($id) || $id == 'none')
							continue;
							$manufacturerList[]=(int)$id;
					}
				}

				if(count($manufacturerList)) {
					$manufacturerList=implode(',', $manufacturerList);
					$filter->filter_namekey = str_replace('-', '_', $filter->filter_namekey);
					$on.=' INNER JOIN  '.hikashop_table('category').' AS '.$filter->filter_namekey.$i.' ON '.$filter->filter_namekey.$i.'.category_id=b.product_manufacturer_id AND b.product_manufacturer_id IN ('.$manufacturerList.') ';
				}
			}
		}

		if($filter->filter_type=='cursor'){
			if(empty($infoGet[0])){
				return false;
			}
			$infoGet = $app->getUserStateFromRequest( $parent->paramBase.'filter_'.$filter->filter_namekey.'_values', 'filter_'.$filter->filter_namekey.'_values', $infoGet, 'array' );

			if(is_array($infoGet)){
				$data =& $infoGet[0];
			}else{
				$data =& $infoGet;
			}

			$sep = ' - ';
			if(strpos($data, '::') !== false)
				$sep = '::';

			$values=explode($sep, $data);

			if(count($values)!=2 || ($values[0]==$filter->filter_options['cursor_min'] && $values[1]==$filter->filter_options['cursor_max'])){
				return false;
			}

			if(in_array($filter->filter_data,array('weight','length','width','height','surface','volume','price'))){
				$type=$filter->filter_data;
				$column = $this->getUnitSelect($filter, $type, $i);
				if($filter->filter_data=='price'){
					$currencyClass = hikashop_get('class.currency');
					$rounding = $currencyClass->getRounding(hikashop_getCurrency());
					$typeClass = new hikashopFilterTypeClass();
					$column = 'round('.$column.','.$rounding.')';
				}
				$limit=' ( '.$column.' ) BETWEEN '.(float)$values[0].' AND '.(float)$values[1].' ';
			}else{
				if(strpos($filter->filter_data,'b.')!==0) $filter->filter_data = 'b.'.$filter->filter_data;
				$limit=' '.$filter->filter_data.' BETWEEN '.(float)$values[0].' AND '.(float)$values[1].' ';
			}
			$filters[]=$limit;
		}
	}

	function getFieldsForSort() {
		$null=null;
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('frontcomp', 'product', false, $null);
		if(empty($fields))
			return $fields;
		$return = array();
		foreach($fields as $k => $field) {
			$fields[$k]->field_realname = hikashop_translate($field->field_realname);
			$return[$k] = $fields[$k];
			$return['b.'.$k] = $return[$k];
		}
		return $return;
	}

	function getFieldToLoad($filter, $divName, &$parent){
		if($filter->filter_data=='category')
			return 'a.category_id';

		if($filter->filter_data=='information') {
			if($filter->filter_options['product_information']=='surface')
				return 'b.product_width*b.product_length';
			if($filter->filter_options['product_information']=='volume')
				return 'b.product_width*b.product_length*b.product_height';
			return 'b.product_'.$filter->filter_options['product_information'];
		}

		if($filter->filter_data=='characteristic')
			return '';

		if($filter->filter_data=='custom_field' && !empty($filter->filter_options['custom_field']))
			return 'b.'.$filter->filter_options['custom_field'];

		if($filter->filter_data=='sort' )
			return '';

		if($filter->filter_data=='manufacturers')
			return '';
		return '';
	}

	function displayInList($filter, $divName, $selected, $tab=""){
		$html='';
		$br='<br/>';

		if($filter->filter_type=='text'){
			$html.='<a class="hikashop_filter_list_selected" style="font-weight:bold">'.$selected[0].'</a>, ';
			$html=$this->getDeleteButton($filter, $divName, $selected[0].' ', $html, $br);
		}

		if($filter->filter_data=='category'){
			$categories_name=$this->getCategories($filter);
			$val='';
			if(empty($categories_name) || !count($categories_name)) {
				$this->canBeUsed = false;
			} else {
				foreach($categories_name as $cat){
					if(is_array($selected) && in_array($cat->category_id, $selected)){
						$html.='<a class="hikashop_filter_list_selected" style="font-weight:bold">'.$cat->category_name.'</a>, ';
						$val.=$cat->category_id.',';
					}
				}
			}
			$html=$this->getDeleteButton($filter, $divName, $val, $html, $br);
		}

		if(($filter->filter_data=='price' || $filter->filter_data=='information') && !empty($filter->filter_value)){
			if($this->checkCurrency($filter)==false){ return false;}
			$size=count($filter->filter_value);
			$val='';
			if(empty($filter->filter_value) || !count($filter->filter_value)) {
				$this->canBeUsed = false;
			} else {
				foreach($filter->filter_value as $key => $value){
					list($formatVal, $oldVal)=$this->formatUnits($filter, $key, $value);
					if($key==0 && is_array($selected) && in_array('::'.$value, $selected)){
						$val .= '::'.$value.',';
						$html .= '<a class="hikashop_filter_list_selected" style="font-weight:bold">'.JText::sprintf('X_AND_INFERIOR',$formatVal).'</a>, ';
					}else if($key!=0 && is_array($selected) && in_array($filter->filter_value[$key-1].'::'.$value, $selected)) {
						$val .= $filter->filter_value[$key-1].'::'.$value.',';
						$html .= '<a class="hikashop_filter_list_selected" style="font-weight:bold">'.JText::sprintf('FROM_X_TO_Y', $oldVal, $formatVal ).'</a>, ';
					}
					if($key==$size-1 && is_array($selected) && in_array($value.'::', $selected)){
						$val .= $value.'::,';
						$html .= '<a class="hikashop_filter_list_selected" style="font-weight:bold">'.JText::sprintf('X_AND_SUPERIOR', $formatVal ).'</a>, ';
					}
				}
			}
			$html = $this->getDeleteButton($filter, $divName, $val, $html, $br);
		}

		if($filter->filter_data=='characteristic'){
			$characteristic_values=$this->getCharacteristics($filter);
			$values='';

			if(empty($characteristic_values) || !count($characteristic_values)) {
				$this->canBeUsed = false;
			} else {
				foreach($characteristic_values as $val){
					if(!empty($selected) && is_array($selected) && in_array($val->characteristic_id, $selected)){
						$html.='> <a class="hikashop_filter_list_selected" style="font-weight:bold">'.$val->characteristic_value.'</a>, ';
						$values.=$val->characteristic_id.',';
					}
				}
			}
			$html=$this->getDeleteButton($filter, $divName, $values, $html, $br);
		}

		if($filter->filter_data=='manufacturers'){
			$manufacturers=$this->getManufacturers($filter);
			$val='';
			if(empty($manufacturers) || !count($manufacturers)) {
				$this->canBeUsed = false;
			} else {
				foreach($manufacturers as $manufacturer){
					if(!empty($selected) && is_array($selected) && in_array($manufacturer->category_id, $selected)){
						$html.='> <a class="hikashop_filter_list_selected" style="font-weight:bold">'.$manufacturer->category_name.'</a>, ';
						$val.=$manufacturer->category_id.',';
					}
				}
			}
			$html=$this->getDeleteButton($filter, $divName, $val, $html,$br);
		}

		if($filter->filter_data=='custom_field'){
			$field=$this->getFields($filter);
			if(is_array($field)) $field=reset($field);
			$values='';
			if(isset($field->field_value) && is_array($field->field_value) && count($field->field_value)){
				if(!empty($selected) && is_array($selected) && count($selected) == 1 && strpos($selected[0], '::'))
					$selected = explode('::', $selected[0]);
				$html = '<ul class="hikashop_filter_selected_list">';
				foreach($field->field_value as $val){
					if(!empty($selected) && is_array($selected) && in_array($val[0], $selected)){
						$html.='<li>'.JText::_($val[1]).'</li>';
						$values.=$val[0].',';
					}
				}
				$html .= '</ul>';
			}else{
				$this->canBeUsed = false;
				if(is_array($selected)){
					$value = implode(' - ',$selected);
				}
				$value = str_replace('::',' - ',$value);
				$html.='<a class="hikashop_filter_list_selected" style="font-weight:bold">'.$value.'</a> ';
			}
			$html=$this->getDeleteButton($filter, $divName, $selected, $html, $br);
		}

		if($filter->filter_data=='sort'){
			if($selected[0]=='lth'){
				$html.='> <a class="hikashop_filter_list_selected" style="font-weight:bold">'.JText::_('ASCENDING' ).'</a>, ';
			}
			if($selected[0]=='htl'){
				$html.='> <a class="hikashop_filter_list_selected" style="font-weight:bold">'.JText::_('DESCENDING' ).'</a>, ';
			}
			$html=$this->getDeleteButton($filter, $divName, $selected[0].',', $html, $br);
		}

		if($filter->filter_type=='cursor'){
			if(!is_array($selected)){
				$selected = explode(' - ', $selected[0]);
			}
			$html = '<a class="hikashop_filter_list_selected" style="font-weight:bold">'.@$selected[0].' - '.@$selected[1].'</a>, ';
			$html = $this->getDeleteButton($filter, $divName, $selected, $html, $br);
		}
		$html = '<span id="filter_values_container_'.$filter->filter_namekey.'_'.$divName.'" class="filter_values_container">'.$html.'</span>';
		$html = $this->titlePosition($filter, $html, true);
		return $html;
	}

	function getDeleteButton($filter, $divName, $val, $html, $br, $onlyButton=false, $id=''){
		$submitOnClick = '';

		if($this->parent->ajax  && ($this->parent->params->get('module') != 'mod_hikashop_filter' || !$this->parent->params->get('force_redirect',0) || $this->parent->itemid == $this->parent->params->get('itemid'))) {
			$submitOnClick = 'window.hikashop.refreshFilters(this);';
		} else {
			$submitOnClick = 'document.forms[\'hikashop_filter_form_'.$divName.'\'].submit();';
		}

		if($onlyButton){
			if(empty($id)){ $id='filter_'.$filter->filter_namekey.'_'.$divName; }
			$empty = 'document.getElementById(\''.$id.'\').value=\' \'; ';
			if(is_array($id)){
				$empty = '';
				foreach($id as $i){
					$empty .= 'document.getElementById(\''.$i.'\').value=\' \'; ';
				}
			}
			return '<a href="#" style="cursor:pointer; text-decoration:none" data-container-div="hikashop_filter_form_'.$divName.'" onclick="'.$empty.$submitOnClick.'return false;" title="'.JText::_('REMOVE_THIS_SELECTION').'"><i class="fa fa-times-circle"></i></a>';
		}
		$html=trim($html,' ,');
		$ids = array();
		if(is_array($val)){
			if($filter->filter_type == 'cursor') {
				$i = 'filter_'.$filter->filter_namekey.'_'.$divName.'_values';
				$ids[] = $i;
				$html.='<input type="hidden" name="filter_'.$filter->filter_namekey.'_values" id="'.$i.'" value="'.implode('::', $val).'" />';
			}
			foreach($val as $v){
				$i = 'filter_'.$filter->filter_namekey.'_'.$divName.'_'.$v;
				$ids[] = $i;
				$html.='<input type="hidden" name="filter_'.$filter->filter_namekey.'[]" id="'.$i.'" value="'.$v.'" />';
			}
		}else{
			if($filter->filter_data=='sort'){
				if(isset($val) && !empty($val)){
					$selected=explode('--',$val);
					$selected[1]=substr($selected[1], 0, 3);
				}

				if($selected[0]=='b.product_name'){ $typeName= 'PRODUCT_NAME'; }
				else if($selected[0]=='b.product_code'){ $typeName= 'PRODUCT_CODE'; }
				else if($selected[0]=='price'){ $typeName= 'PRICE'; }
				else if($selected[0]=='b.product_average_score'){ $typeName= 'RATING'; }
				else if($selected[0]=='b.product_created' || $selected[0]=='b.product_modified'){ $typeName= 'RECENT'; }
				else if($selected[0]=='b.product_sales'){ $typeName= 'SALES'; }
				else if($selected[0]=='b.product_hit'){ $typeName= 'CLICKS'; }
				else{ $typeName='PRODUCT_'.str_replace('b.','',$selected[0]); }

				if(JText::_('SORT_ASCENDING_'.$typeName)!='SORT_ASCENDING_'.$typeName){ $asc_name=JText::_('SORT_ASCENDING_'.$typeName); }
				else{ $asc_name=JText::sprintf('SORT_ASCENDING', JText::_($typeName)); }

				if(JText::_('SORT_DESCENDING_'.$typeName)!='SORT_DESCENDING_'.$typeName){ $desc_name=JText::_('SORT_DESCENDING_'.$typeName); }
				else{ $desc_name=JText::sprintf('SORT_DESCENDING', JText::_($typeName)); }

				if($selected[1]=='lth'){ $html.='<span>'.$asc_name.'</span>'; }
				else{  $html.='<span>'.$desc_name.'</span>'; }
			}
			$val = substr($val,0,-1);
			$i = 'filter_'.$filter->filter_namekey.'_'.$divName;
			$ids[] = $i;
			$html.='<input type="hidden" name="filter_'.$filter->filter_namekey.'" id="'.$i.'" value="'.$val.'" />';
		}
		$empty = '';
		foreach($ids as $i){
			$empty .= 'document.getElementById(\''.$i.'\').value=\' \'; ';
		}
		$html.='  <a href="#" style="cursor:pointer; text-decoration:none" data-container-div="hikashop_filter_form_'.$divName.'" onclick="'.$empty.$submitOnClick.'return false;" title="'.JText::_('REMOVE_THIS_SELECTION').'"><i class="fa fa-times-circle"></i></a><br/>';
		return $html;
	}

	function getUnitSelect($filter, $type, $i=0){
		$weightHelper=hikashop_get('helper.weight');
		$volumeHelper=hikashop_get('helper.volume');
		$config =& hikashop_config();
		$defaulUnit='cm';
		if($type=='weight'){
			$infoType='b.product_weight';
			$unitType='b.product_weight_unit';
			$units=$weightHelper->conversion;
			$defaulUnit='kg';
		}else if($type=='volume'){
			$infoType='(b.product_width*b.product_length*b.product_height)';
			$unitType='b.product_dimension_unit';
			$units=$volumeHelper->conversion;
		}else if($type=='surface'){
			$infoType='(b.product_width*b.product_length)';
			$unitType='b.product_dimension_unit';
			$units=$volumeHelper->conversionDimension;
		}else if($type=='height' || $type=='length' || $type=='width'){
			$unitType='b.product_dimension_unit';
			$units=$volumeHelper->conversionDimension;
			if($type=='height'){ $infoType='b.product_height';	}
			if($type=='length'){ $infoType='b.product_length';	}
			if($type=='width'){ $infoType='b.product_width';	}
		}elseif($type=='price'){
			return $this->getPriceSelect();
		}else{
			return '';
		}

		if(isset($filter->filter_options['information_unit'])) {
			$selectedUnit = $filter->filter_options['information_unit'];
		}else{
			$selectedUnit = $defaulUnit;
		}
		$case =' case';
		foreach( $units as $key => $unit) {
			$calculatedVal='';
			if($key == $selectedUnit)
				$val = 1;
			elseif(!empty($unit[$selectedUnit]))
				$val = $unit[$selectedUnit];
			else
				$val = 1;
			$calculatedVal = $infoType . '*' . $val;
			$case .= ' when ' . $unitType . ' = \'' . $key . '\' then ' . $calculatedVal;
		}
		$case .= ' else ' . $infoType . ' end ';
		return $case;
	}

	function getPriceSelect($price_table='b',$product_table='b'){
		$case=' case';
		$currentCurrency = hikashop_getCurrency();
		$unitType=$price_table.'.price_value';
		$currencyType = hikashop_get('type.currency');
		$currencyClass = hikashop_get('class.currency');
		$dstCurrency = $currencyClass->get($currentCurrency);
		$currencyType->load(0);
		$currencies = $currencyType->currencies;
		$config =& hikashop_config();
		$main_currency = $config->get('main_currency',1);
		if(
			($config->get('price_with_tax') && !$config->get('floating_tax_prices'))
			||
			(!$config->get('price_with_tax') && $config->get('floating_tax_prices'))
			){
			$categoryClass=hikashop_get('class.category');
			$main = 'tax';
			$categoryClass->getMainElement($main);
			$tax_categories = $categoryClass->getChildren($main);
			$taxes = array();
			foreach($tax_categories as $tax_category){
				$taxes[$tax_category->category_id] = (float)$currencyClass->getTax(hikashop_getZone(),$tax_category->category_id);
			}
			$taxes[0] = 0;
		}
		$case .= ' when '.$price_table.'.price_currency_id IS NULL then 0';
		foreach($currencies as $currency){

			$calculatedVal=$unitType;
			if($main_currency!=$currency->currency_id){
				if(bccomp(sprintf('%F',$currency->currency_percent_fee),0,2)){
					$calculatedVal='('.$calculatedVal.'*'.(floatval($currency->currency_percent_fee+100)/100.0).')';
				}
				$calculatedVal='('.$calculatedVal.'/'.floatval($currency->currency_rate).')';
			}
			if($main_currency!=$currentCurrency){
				$calculatedVal='('.$calculatedVal.'*'.floatval($dstCurrency->currency_rate).')';
				if(bccomp(sprintf('%F',$dstCurrency->currency_percent_fee),0,2)){
					$calculatedVal='('.$calculatedVal.'*'.(floatval($dstCurrency->currency_percent_fee+100)/100.0).')';
				}
			}
			if(!empty($taxes)){
				$ids=array();
				foreach($taxes as $id => $tax){
					if($id!=0){
						$ids[]=$id;
						if($config->get('floating_tax_prices')){
							$case .= ' when '.$price_table.'.price_currency_id = \''.$currency->currency_id.'\' and '.$product_table.'.product_tax_id = \''.$id.'\' then '.$calculatedVal.'/'.(1+$tax);
						}else{
							$case .= ' when '.$price_table.'.price_currency_id = \''.$currency->currency_id.'\' and '.$product_table.'.product_tax_id = \''.$id.'\' then '.$calculatedVal.'+'.$calculatedVal.'*'.$tax;
						}
					}
				}
				$case .= ' when '.$price_table.'.price_currency_id = \''.$currency->currency_id.'\' and '.$product_table.'.product_tax_id NOT IN (\''.implode('\',\'',$ids).'\') then '.$calculatedVal;
			}else{
				$case .= ' when '.$price_table.'.price_currency_id = \''.$currency->currency_id.'\' then '.$calculatedVal;
			}

		}
		$case.= ' end ';
		return $case;
	}

	function titlePosition($filter, $html='', $list=false, $opt=''){
		$name=$this->trans($filter->filter_name);
		if($list && $filter->filter_options['title_position'] == 'inside'){
			$position='top';
		}
		else{
			$position=$filter->filter_options['title_position'];
		}
		switch ($position){
			case 'bottom':
				$html=$html.'<br/>'.$name.' '.$opt;
				break;
			case 'top':
				$html=$name.' '.$opt.'<br/>'.$html;
				break;
			case 'left':
				$html=$name.' '.$opt.' '.$html;
				break;
			case 'right':
				$html=$html.' '.$name.' '.$opt;
				break;
		}
		return $html;
	}

	function trans($name){
		$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($name));
		$trans = JText::_($val);
		if($val==$trans){
			$trans = $name;
		}
		return '<span class="hikashop_filter_title">'.$trans.'</span>';
	}

	function getCategories($filter, $datas=''){
		if(!empty($filter->filterActive) && !empty($_SESSION['hk_filter'][$filter->filter_id][$filter->page_key])) {
			return $_SESSION['hk_filter'][$filter->filter_id][$filter->page_key];
		}
		$left='';
		$filters = array('a.category_published=1');
		if(!empty($datas['products'])){
			$left=' LEFT JOIN '.hikashop_table('product_category').' AS b ON a.category_id=b.category_id ';
			$optionElement = array();
			foreach($datas['products'] as $data){
				if($data->product_id)
					$optionElement[(int)$data->product_id]=(int)$data->product_id;
			}
			if(count($optionElement))
				$filters[]='b.product_id IN ('.implode(',', $optionElement).')';
		}elseif(isset($datas['products'])) {
			return null;
		}

		$database = JFactory::getDBO();
		$orderby = 'a.category_name ASC';
		if(!empty($filter->filter_options['parent_category_id'])){
			$parentCat=$filter->filter_options['parent_category_id'];
			$filters[]='a.category_parent_id='.(int)$parentCat;
			$orderby = 'a.category_ordering ASC';
		} else {
			$filters[]='a.category_type='.$database->Quote('product');
		}

		$config = hikashop_config();
		$override = $config->get('ordering_for_filter_categories', '');
		if(!empty($override)) {
			$orderby = $override;
		}

		hikashop_addACLFilters($filters,'category_access','a');
		$query='SELECT * FROM '.hikashop_table('category').' AS a '.$left.' WHERE '.implode(' AND ',$filters).' ORDER BY '.$orderby;
		$database->setQuery($query);
		$categories_name=$database->loadObjectList('category_id');
		foreach($categories_name as $k => $category_name) {
			$categories_name[$k]->category_name = hikashop_translate($category_name->category_name);
		}
		if(!empty($datas))
			$this->storeValuesInSession($filter, $categories_name);
		return $categories_name;
	}

	function getCharacteristics($filter, $datas=''){
		if(!empty($filter->filterActive) && !empty($_SESSION['hk_filter'][$filter->filter_id][$filter->page_key])) {
			return $_SESSION['hk_filter'][$filter->filter_id][$filter->page_key];
		}
		$filters = array('characteristic_parent_id='.$filter->filter_options['filter_charac']);
		$left='';
		$database = JFactory::getDBO();
		if(!empty($datas['products'])){
			$left=' LEFT JOIN '.hikashop_table('variant').' AS b ON a.characteristic_id=b.variant_characteristic_id ';
			$option_pid = array();
			foreach($datas['products'] as $data){
				if($data->product_id)
					$option_pid[(int)$data->product_id] = (int)$data->product_id;
			}
			$query = 'SELECT product_id FROM '.hikashop_table('product');
			if(count($option_pid))
				$query .= ' WHERE product_parent_id IN ('.implode(',',$option_pid).') AND product_published > 0';
			$database->setQuery($query);
			$results = $database->loadObjectList();
			$result_pid = array();
			foreach($results as $result){
				$result_pid[] = $result->product_id;
			}
			if(!empty($result_pid))
				$filters[] = 'b.variant_product_id IN ('.implode(',', $result_pid).')';
		}elseif(isset($datas['products'])) {
			return null;
		}
		$config =& hikashop_config();
		$sort = $config->get('characteristics_values_sorting');
		if($sort=='old'){
			$order = 'characteristic_id ASC';
		}elseif($sort=='alias'){
			$order = 'characteristic_alias ASC';
		}elseif($sort=='ordering'){
			$order = 'characteristic_ordering ASC';
		}else{
			$order = 'characteristic_value ASC';
		}
		$query='SELECT DISTINCT a.* FROM '.hikashop_table('characteristic').' AS a '.$left.' WHERE ' . implode(' AND ', $filters) . ' ORDER BY a.'.$order;
		$database->setQuery($query);
		$characteristic_values=$database->loadObjectList();

		foreach($characteristic_values as $k => $characteristic_value) {
			$characteristic_values[$k]->characteristic_value = hikashop_translate($characteristic_value->characteristic_value);
		}
		if(!empty($datas))
			$this->storeValuesInSession($filter, $characteristic_values);
		return $characteristic_values;
	}

	function storeValuesInSession(&$filter, $values) {
		if(!isset($_SESSION['hk_filter']))
			$_SESSION['hk_filter'] = array();
		if(!isset($_SESSION['hk_filter'][$filter->filter_id]))
			$_SESSION['hk_filter'][$filter->filter_id] = array();
		$_SESSION['hk_filter'][$filter->filter_id][$filter->page_key] = $values;
	}

	function getManufacturers($filter, $datas=''){
		if(!empty($filter->filterActive) && !empty($_SESSION['hk_filter'][$filter->filter_id][$filter->page_key])) {
			return $_SESSION['hk_filter'][$filter->filter_id][$filter->page_key];
		}
		$optionElement = ''; $left='';
		if(!empty($datas['products'])){
			$optionElement = array();
			$left = ' LEFT JOIN '.hikashop_table('product').' AS b ON a.category_id=b.product_manufacturer_id ';
			foreach($datas['products'] as $data){
				if($data->product_id)
					$optionElement[(int)$data->product_id] = (int)$data->product_id;
			}
			if(count($optionElement))
				$optionElement = 'AND b.product_id IN ('.implode(',', $optionElement).')';
		}elseif(isset($datas['products'])) {
			return null;
		}
		$filters = array(
			'a.category_type = \'manufacturer\'',
			'a.category_parent_id != 1 ' . $optionElement,
			'a.category_published = 1'
		);
		hikashop_addACLFilters($filters,'category_access','a');
		$database = JFactory::getDBO();
		$query='SELECT a.* FROM '.hikashop_table('category').' AS a '.$left.' WHERE ('.implode(') AND (',$filters).') ORDER BY a.category_ordering ASC';
		$database->setQuery($query);
		$manufacturers=$database->loadObjectList('category_id');
		foreach($manufacturers as $k => $manufacturer) {
			$manufacturers[$k]->category_name = hikashop_translate($manufacturer->category_name);
		}
		if(!empty($datas))
			$this->storeValuesInSession($filter, $manufacturers);
		return $manufacturers;
	}

	function getFields($filter, $datas=''){
		if(!empty($filter->filterActive) && !empty($_SESSION['hk_filter'][$filter->filter_id][$filter->page_key])) {
			return $_SESSION['hk_filter'][$filter->filter_id][$filter->page_key];
		}
		$fieldClass = hikashop_get('class.field');
		$field = $fieldClass->getField($filter->filter_options['custom_field'], 'product');

		if(!empty($filter->filter_options['custom_field']) && !empty($datas['products'])){
			$optionElement = array();
			foreach($datas['products'] as $data){
				if($data->product_id)
					$optionElement[(int)$data->product_id] = (int)$data->product_id;
			}

			$query = 'SELECT DISTINCT '.$filter->filter_options['custom_field'].' FROM '.hikashop_table('product');
			if(count($optionElement))
				$query .= ' WHERE product_id IN ('.implode(',', $optionElement).')';
			$database = JFactory::getDBO();
			$database->setQuery($query);
			$values=$database->loadColumn();

			if(!empty($values)){
				foreach($values as $val){
					$els = explode(',',$val);
					if(count($els)>1){
						foreach($els as $el){
							$values[]=$el;
						}
					}
				}
			}
		}

		if(!empty($field->field_value)){
			$unset=array();
			$config = hikashop_config();
			foreach($field->field_value as $key => $val){
				if(isset($datas['products']) && (empty($values) || !in_array($key,$values))) {
					$unset[]=$key;
				}else if(!$config->get('use_fields_disabled_values_in_filters', 1) && !empty($val->disabled)) {
  					$unset[]=$key;
				}else{
					$field->field_value[$key]=array($key, $val->value);
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($field->field_value[$u]);
				}
			}
		}
		if(!empty($datas))
			$this->storeValuesInSession($filter, $field);
		return $field;
	}

	function formatUnits($filter, $key, $value){
		$currencyClass = hikashop_get('class.currency');
		$currency = hikashop_getCurrency();
		$formatVal= $value;
		$oldVal=0;
		if($key!=0){ $oldVal=$filter->filter_value[$key-1]; }
		if(in_array($filter->filter_data, array('price', 'b.product_sort_price'))) {
			$formatVal=$currencyClass->format($formatVal,$currency);
			if($key!=0){  $oldVal=$currencyClass->format($oldVal,$currency); }
		}else if(isset($filter->filter_options['information_unit'])){
			$formatVal=$formatVal.$filter->filter_options['information_unit'];
			if($key!=0){  $oldVal=$oldVal.$filter->filter_options['information_unit']; }
		}
		$array=array();
		$array[]=$formatVal;
		$array[]=$oldVal;
		return $array;
	}

	function checkCurrency($filter){
		$currency = hikashop_getCurrency();
		if($filter->filter_data=='price' && !in_array($currency, $filter->filter_options['currencies'])){
			return false;
		}
		return true;
	}
}

class hikashopTextClass extends hikashopFilterTypeClass{

	function getFieldToLoad($filter, $divName, &$parent){
		return '';
	}

	function display(&$filter, $divName, &$parent, $datas=''){
		$html='';
		$name='';
		$selected=parent::display($filter, $divName, $parent);

		if(!is_array($selected)){
			$selected = array($selected);
		}

		if($filter->filter_deletable && ( isset($selected[0]) && $selected[0]!='none' && !in_array($selected[0],array(" ",'')))){
			$html=parent::displayInList($filter, $divName, $selected);
			return $html;
		}

		$attributes = '';

		if($filter->filter_options['textBoxSize']){
			$attributes .= ' style="width: 90%;"';
		}

		if(!empty($selected) && !empty($selected[0])){
			$name=htmlentities($selected[0], ENT_COMPAT, 'UTF-8');
		}
		if($name == ' ')
			$name = '';
		if($filter->filter_direct_application) {
			if($parent->ajax  && ($parent->params->get('module') != 'mod_hikashop_filter' || !$parent->params->get('force_redirect',0) || $parent->itemid == $parent->params->get('itemid'))) {
				$refresh = 'window.hikashop.refreshFilters(this, true);';
				if($parent->params->get('module') != 'mod_hikashop_filter') {
					$refresh = 'window.hikashop.refreshFilters(this);';
				}
				$attributes .= ' data-old="'.$name.'" oninput="if(this.value != this.getAttribute(\'data-old\')) { '.$refresh.' }" data-container-div="hikashop_filter_form_'.$divName.'"';
			} else {
				$attributes .= ' data-old="'.$name.'" onchange="if(this.value != this.getAttribute(\'data-old\')) { document.forms[\'hikashop_filter_form_'.$divName.'\'].submit(); }"';
			}
		}
		if(!empty($filter->filter_options['attribute'])) {
			$attributes .= ' '.$filter->filter_options['attribute'];
		}
		if($filter->filter_options['title_position']=='inside' && empty($name)){
			$attributes .= ' placeholder="'.strip_tags($this->trans($filter->filter_name)).'"';
		}
		$html = '<input class="'.HK_FORM_CONTROL_CLASS.'" name="filter_'.$filter->filter_namekey.'"'.$attributes.' type="text" id="filter_text_'.$filter->filter_namekey.'" value="'.$name.'">';
		$html = '<span id="filter_values_container_'.$filter->filter_namekey.'_'.$divName.'" class="filter_values_container">'.$html.'</span>';
		$html=parent::titlePosition($filter, $html);
		return $html;
	}
}

class hikashopSingledropdownClass extends hikashopFilterTypeClass{

	function display(&$filter, $divName, &$parent, $datas='', $multiple='', $tab=''){
		$selected=parent::display($filter, $divName, $parent);
		if(!is_array($selected)){
			$selected=array($selected);
		}
		if(!($filter->filter_dynamic)){
			$datas='';
		}

		if(!empty($selected)){
			if($filter->filter_deletable && $selected[0] != $filter->filter_namekey && $selected[0] != ('filter_'.$filter->filter_namekey) && $selected[0]!='none' && ( isset($selected[0]) && trim(str_replace('::','',$selected[0])) != '')) {
				$html=parent::displayInList($filter, $divName, $selected, $tab);
				return $html;
			}
		}

		$onClick='';
		if($filter->filter_direct_application){
			if($parent->ajax  && ($parent->params->get('module') != 'mod_hikashop_filter' || !$parent->params->get('force_redirect',0) || $parent->itemid == $parent->params->get('itemid'))) {
				$onClick = 'onchange="window.hikashop.refreshFilters(this);" data-container-div="hikashop_filter_form_'.$divName.'"';
			} else {
				$onClick = 'onchange="document.forms[\'hikashop_filter_form_'.$divName.'\'].submit();"';
			}
		}
		if(!empty($filter->filter_options['attribute'])) {
			$onClick .= ' '.$filter->filter_options['attribute'];
		}
		$html='';
		if(!empty($tab)){
			$html.= '<input type="hidden" name="filter_'.$filter->filter_namekey.'_'.$divName.'" value=" "/>';
		}
		$html.='<SELECT class="'.HK_FORM_SELECT_CLASS.'" '.$multiple.$onClick.' size='.$filter->filter_options['filter_size'].' name="filter_'.$filter->filter_namekey.$tab.'" id="filter_'.$filter->filter_namekey.'_'.$divName.$tab.'">';

		if($filter->filter_options['title_position']=='inside'){
			$html.='<OPTION VALUE="none">'.$this->trans($filter->filter_name).'</OPTION>';
		}else if($filter->filter_data!='sort'){
			$html.='<OPTION VALUE="filter_'.$filter->filter_namekey.'">'.JText::_( 'HIKA_ALL' ).'</OPTION>';
		}

		if($filter->filter_data=='category'){
			$categories_name=parent::getCategories($filter, $datas);
			if(!empty($categories_name) && count($categories_name)){
				foreach($categories_name as $cat){
					$selectedItem='';
					if(!empty($selected) && in_array($cat->category_id, $selected)){
						$selectedItem='selected="selected"';
					}
					$html.='<OPTION '.$onClick.' '.$selectedItem.' value="'.$cat->category_id.'">'.$cat->category_name.'</OPTION>';
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if(($filter->filter_data=='price' || $filter->filter_data=='information' || $filter->filter_data=='custom_field') && !empty($filter->filter_value)){
			if(parent::checkCurrency($filter)==false){ return false;}
			$size=count($filter->filter_value);
			if($size && count($filter->filter_value)){
				foreach($filter->filter_value as $key => $value){
					$selectedItem='';
					list($formatVal, $oldVal)=parent::formatUnits($filter, $key, $value);
					if($key==0){
						if(!empty($selected) && in_array('::'.$value, $selected)){
							$selectedItem='selected="selected"';
						}
						if(empty($filter->filter_options['defined_limits'])){
							$html.='<OPTION '.$selectedItem.' value="::'.$value.'">'.JText::sprintf('X_AND_INFERIOR',$formatVal).'</OPTION>';
						}
					}else {
						if(!empty($selected) && in_array($filter->filter_value[$key-1].'::'.$value, $selected)){
							$selectedItem='selected="selected"';
						}
						$html.='<OPTION '.$selectedItem.' value="'.$filter->filter_value[$key-1].'::'.$value.'">'.JText::sprintf('FROM_X_TO_Y', $oldVal, $formatVal ).'</OPTION>';
					}
					if($key==$size-1){
						$selectedItem='';
						if(!empty($selected) && in_array($value.'::', $selected)){
							$selectedItem='selected="selected"';
						}
						if(empty($filter->filter_options['defined_limits'])){
							$html.='<OPTION '.$selectedItem.' value="'.$value.'::">'.JText::sprintf('X_AND_SUPERIOR', $formatVal ).'</OPTION>';
						}
					}
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='characteristic'){
			$characteristic_values=parent::getCharacteristics($filter, $datas);
			if(!empty($characteristic_values) && count($characteristic_values)){
				foreach($characteristic_values as $val){
					$selectedItem='';
					if(!empty($selected) && in_array($val->characteristic_id, $selected)){
						$selectedItem='selected="selected"';
					}
					$html.='<OPTION '.$selectedItem.' value="'.$val->characteristic_id.'">'.$val->characteristic_value.'</OPTION>';
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='manufacturers'){
			$manufacturers=parent::getManufacturers($filter, $datas);
			if(!empty($manufacturers) && count($manufacturers)){
				foreach($manufacturers as $manufacturer){
					$selectedItem='';
					if(!empty($selected) && in_array($manufacturer->category_id, $selected)){
						$selectedItem='selected="selected"';
					}
					$html.='<OPTION '.$selectedItem.' name="'.$filter->filter_data.'" value="'.$manufacturer->category_id.'">'.$manufacturer->category_name.'</OPTION>';
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='custom_field'){
			$field = parent::getFields($filter, $datas);
			if(isset($field->field_value) && is_array($field->field_value) && count($field->field_value)){
				foreach($field->field_value as $val){
					$selectedItem='';
					if(!empty($selected) && in_array($val[0], $selected)){
						$selectedItem='selected="selected"';
					}
					if(empty($val[0]) && ($field->field_required || $filter->filter_options['title_position'] == 'inside')) continue;
					$html.='<OPTION '.$selectedItem.' name="'.$filter->filter_data.'" value="'.$val[0].'">'.JText::_($val[1]).'</OPTION>';
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='sort'){
			if($filter->filter_options['title_position']!='inside'){
				$html.='<OPTION value="none">'.JText::_('HIKA_NONE' ).'</OPTION>';
			}

			$fields = $this->getFieldsForSort();

			if(!is_array($filter->filter_options['sort_by'])){
				$temp = $filter->filter_options['sort_by'];
				$filter->filter_options['sort_by'] = array();
				$filter->filter_options['sort_by'][] = $temp;
			}
			if(empty($filter->filter_options['sort_by']) || !count($filter->filter_options['sort_by'])) {
				$this->canBeUsed = false;
			} else {
				foreach($filter->filter_options['sort_by'] as $theType){
					$selectedItem1=''; $selectedItem2='';
					if(!empty($selected)){
						if($selected[0]==$theType.'--lth' || $selected[0]==$theType){
							$selectedItem1='selected="selected"';
						}
						if($selected[0]==$theType.'--htl' || $selected[0]==$theType){
							$selectedItem2='selected="selected"';
						}
					}
					$fullType = $theType;
					if(in_array(substr($theType,-5), array('--lth', '--htl')))
						$theType = substr($theType, 0, -5);

					if(isset($fields[$theType])){ $typeName=$fields[$theType]->field_realname; }
					else if($theType=='b.product_name'){ $typeName= 'PRODUCT_NAME'; }
					else if($theType=='b.product_code'){ $typeName= 'PRODUCT_CODE'; }
					else if($theType=='price'){ $typeName= 'PRICE'; }
					else if($theType=='b.product_average_score'){ $typeName= 'RATING'; }
					else if($theType=='b.product_created' || $theType=='b.product_modified'){ $typeName= 'RECENT'; }
					else if($theType=='b.product_sales'){ $typeName= 'SALES'; }
					else if($theType=='b.product_hit'){ $typeName= 'CLICKS'; }
					else{ $typeName='PRODUCT_'.str_replace('b.','',$theType); }

					if(JText::_('SORT_ASCENDING_'.$typeName)!='SORT_ASCENDING_'.$typeName){ $asc_name=JText::_('SORT_ASCENDING_'.$typeName); }
					else{ $asc_name=JText::sprintf('SORT_ASCENDING', JText::_($typeName)); }

					if(JText::_('SORT_DESCENDING_'.$typeName)!='SORT_DESCENDING_'.$typeName){ $desc_name=JText::_('SORT_DESCENDING_'.$typeName); }
					else{ $desc_name=JText::sprintf('SORT_DESCENDING', JText::_($typeName)); }
					if($fullType == $theType || $fullType == $theType.'--lth')
						$html.='<OPTION '.$selectedItem1.' value="'.$theType.'--lth">'.$asc_name.'</OPTION>';
					if($fullType == $theType || $fullType == $theType.'--htl')
						$html.='<OPTION '.$selectedItem2.' value="'.$theType.'--htl">'.$desc_name.'</OPTION>';
				}
			}
		}
		$html.='</SELECT>';
		$html = '<span id="filter_values_container_'.$filter->filter_namekey.'_'.$divName.'" class="filter_values_container">'.$html.'</span>';
		if($filter->filter_options['title_position']!='inside'){
			$html=parent::titlePosition($filter, $html);
		}

		return $html;
	}

}

class hikashopRadioClass extends hikashopFilterTypeClass{

	function display(&$filter, $divName, &$parent, $datas='', $type='radio', $tab=''){
		$selected=parent::display($filter, $divName, $parent);

		if(!($filter->filter_dynamic)){
			$datas='';
		}
		if(!is_array($selected)){
			if($type=='checkbox'){
				$selected = explode('::',$selected);
			}else{
				$selected = array($selected);
			}
		}

		if($filter->filter_deletable && isset($selected[0]) && $selected[0]!='none' && !in_array($selected[0],array(" ",''))){
			$html=parent::displayInList($filter, $divName, $selected, $tab);
			return $html;
		}

		$onClick='';
		if($filter->filter_direct_application){
			if($parent->ajax  && ($parent->params->get('module') != 'mod_hikashop_filter' || !$parent->params->get('force_redirect',0) || $parent->itemid == $parent->params->get('itemid'))) {
				$onClick = 'onchange="window.hikashop.refreshFilters(this);" data-container-div="hikashop_filter_form_'.$divName.'"';
			} else {
				$onClick = 'onchange="document.forms[\'hikashop_filter_form_'.$divName.'\'].submit();"';
			}
		}

		$html='';
		$br='';
		if(isset($filter->filter_options['button_align']) && $filter->filter_options['button_align']==0){ $br='<br/>'; }
		if(!empty($tab)){
			$html.= '<input type="hidden" name="filter_'.$filter->filter_namekey.'"  id="filter_'.$filter->filter_namekey.'_'.$divName.'" value=" "/>';
		}
		if($filter->filter_data=='category'){
			$categories_name=parent::getCategories($filter, $datas);
			if(!empty($categories_name) && count($categories_name)){
				foreach($categories_name as $cat){
					$checked='';$deleteButton='';
					if(!empty($selected) && is_array($selected) && (in_array($cat->category_id, $selected) || strpos($selected[0], (string)$cat->category_id) !== false)) {
						$checked = 'checked="checked"';
						if($type == 'radio') {
							$deleteButton = '  '.parent::getDeleteButton($filter, $divName, '', $html, '', true, 'filter_'.$filter->filter_id.'_'.$cat->category_id.'_'.$divName);
						}
					}
					$html.='<span class="hikashop_filter_checkbox"><input '.$onClick.' '.$checked.' name="filter_'.$filter->filter_namekey.$tab.'"  type="'.$type.'" value="'.$cat->category_id.'" id="filter_'.$filter->filter_id.'_'.$cat->category_id.'_'.$divName.'"/><label class="filter_'.$filter->filter_id.'_'.$cat->category_id.'_'.$divName.'" for="filter_'.$filter->filter_id.'_'.$cat->category_id.'_'.$divName.'">'.$cat->category_name.'</label>'.$deleteButton.'</span>'.$br;
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if(($filter->filter_data=='price' || $filter->filter_data=='information' || $filter->filter_data=='custom_field') && !empty($filter->filter_value)){
			if(parent::checkCurrency($filter)==false){ return false;}
			$size=count($filter->filter_value);
			if($size){
				if(is_array($selected) && count($selected) >= 2){
					$selected = array_chunk($selected, 2);
					foreach($selected as $k => $v){
						$selected[$k] = implode('::', $v);
					}
				}

				foreach($filter->filter_value as $key => $value){
					$checked=''; $deleteButton='';
					if($key>0){ $previousVal=$filter->filter_value[$key-1];}
					list($formatVal, $oldVal) = parent::formatUnits($filter, $key, $value);
					if($key==0){
						if(!empty($selected) && is_array($selected) && in_array('::'.$value, $selected)){
							$checked='checked="checked"';
							if($type=='radio'){ $deleteButton='  '.parent::getDeleteButton($filter, $divName, '', $html, '', true, 'filter_'.$filter->filter_id.'_'.$value.'' ); }
						}
						$html.='<span class="hikashop_filter_checkbox"><input '.$onClick.' '.$checked.' type="'.$type.'" name="filter_'.$filter->filter_namekey.''.$tab.'" value="::'.$value.'" id="filter_'.$filter->filter_id.'_'.$value.'"/><label class="filter_'.$filter->filter_namekey.''.$tab.'" for="filter_'.$filter->filter_id.'_'.$value.'">'.JText::sprintf('X_AND_INFERIOR',$formatVal).'</label>'.$deleteButton.'</span>'.$br;
						parent::getDeleteButton($filter, $divName, '', $html, true);
					}else{
						if(!empty($selected) && is_array($selected) && in_array($filter->filter_value[$key-1].'::'.$value, $selected)){
							$checked='checked="checked"';
							if($type=='radio'){ $deleteButton='  '.parent::getDeleteButton($filter, $divName, '', $html, '', true, 'filter_'.$filter->filter_id.'_'.$value.'' ); }
						}
						$html.='<span class="hikashop_filter_checkbox"><input '.$onClick.' '.$checked.' type="'.$type.'" name="filter_'.$filter->filter_namekey.''.$tab.'" value="'.$filter->filter_value[$key-1].'::'.$value.'" id="filter_'.$filter->filter_id.'_'.$value.'"/><label class="filter_'.$filter->filter_namekey.''.$tab.'" for="filter_'.$filter->filter_id.'_'.$value.'">'.JText::sprintf('FROM_X_TO_Y', $oldVal, $formatVal ).'</label>'.$deleteButton.'</span>'.$br;
					}
					if($key==$size-1){
						$checked=''; $deleteButton='';
						if(!empty($selected) && is_array($selected) && in_array($value.'::', $selected)){
							$checked='checked="checked"';
							if($type=='radio'){ $deleteButton='  '.parent::getDeleteButton($filter, $divName, '', $html, '', true, 'filter_'.$filter->filter_id.'_'.$value.''); }
						}
						$html.='<span class="hikashop_filter_checkbox"><input '.$onClick.' '.$checked.' type="'.$type.'" name="filter_'.$filter->filter_namekey.''.$tab.'" value="'.$value.'::" id="filter_'.$filter->filter_id.'_'.$value.'_plus"/><label class="filter_'.$filter->filter_namekey.''.$tab.'" for="filter_'.$filter->filter_id.'_'.$value.'_plus">'.JText::sprintf('X_AND_SUPERIOR', $formatVal ).'</label>'.$deleteButton.'</span>'.$br;
						parent::getDeleteButton($filter, $divName, '', $html, true);
					}
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='characteristic'){
			$characteristic_values=parent::getCharacteristics($filter, $datas);
			if(!empty($characteristic_values) && count($characteristic_values)){
				foreach($characteristic_values as $val){
					$checked=''; $deleteButton='';
					if(!empty($selected) && is_array($selected) && in_array($val->characteristic_id, $selected)){
						$checked='checked="checked"';
						if($type=='radio'){ $deleteButton='  '.parent::getDeleteButton($filter, $divName, '', $html, '', true, 'filter_'.$filter->filter_id.'_'.$val->characteristic_id.'_'.$divName); }
					}
					$html.='<span class="hikashop_filter_checkbox"><input '.$onClick.' '.$checked.' type="'.$type.'" name="filter_'.$filter->filter_namekey.$tab.'" value="'.$val->characteristic_id.'" id="filter_'.$filter->filter_id.'_'.$val->characteristic_id.'_'.$divName.'"/><label class="filter_'.$filter->filter_namekey.'_'.$val->characteristic_id.'_'.$divName.'" for="filter_'.$filter->filter_id.'_'.$val->characteristic_id.'_'.$divName.'">'.$val->characteristic_value.'</label>'.$deleteButton.'</span>'.$br;
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='manufacturers'){
			$manufacturers=parent::getManufacturers($filter, $datas);
			if(!empty($manufacturers) && count($manufacturers)){
				foreach($manufacturers as $manufacturer){
					$checked=''; $deleteButton='';
					if(!empty($selected) && is_array($selected) && in_array($manufacturer->category_id, $selected)){
						$checked='checked="checked"';
						if($type=='radio'){ $deleteButton='  '.parent::getDeleteButton($filter, $divName, '', $html, '', true, 'filter_'.$filter->filter_id.'_'.$manufacturer->category_id.'_'.$divName); }
					}
					$html.='<span class="hikashop_filter_checkbox"><input '.$onClick.' '.$checked.' type="'.$type.'" name="filter_'.$filter->filter_namekey.$tab.'" value="'.$manufacturer->category_id.'" id="filter_'.$filter->filter_id.'_'.$manufacturer->category_id.'_'.$divName.'"/><label class="filter_'.$filter->filter_id.'_'.$manufacturer->category_id.'_'.$divName.'" for="filter_'.$filter->filter_id.'_'.$manufacturer->category_id.'_'.$divName.'">'.$manufacturer->category_name.'</label>'.$deleteButton.'</span>'.$br;
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data == 'custom_field' && empty($filter->filter_value)) {
			$field = parent::getFields($filter, $datas);
			if(!empty($field) && is_array($field->field_value) && count($field->field_value)) {
				foreach($field->field_value as $key => $val) {
					$checked = '';
					$deleteButton = '';
					$input = '';
					if(!empty($selected) && is_array($selected)){
						foreach($selected as $selectedElement){
							if(preg_match('#^('.$selectedElement.'|.*\:\:'.$selectedElement.'|'.$selectedElement.'::.*)$#',$val[0],$match)){
								$checked = 'checked="checked"';
								if($type == 'radio') {
									$deleteButton = '  '.parent::getDeleteButton($filter, $divName, '', $html, '', true, 'field_'.$filter->filter_id.'_'.$key.'_'.$divName);
								}
								break;
							}
						}
					}
					if($type!='hidden') $input = '<input '.$onClick.' '.$checked.' type="'.$type.'" name="filter_'.$filter->filter_namekey.$tab.'" value="'.htmlentities($val[0], ENT_COMPAT, 'UTF-8').'" id="field_'.$filter->filter_id.'_'.$key.'_'.$divName.'">';
					$html.='<span class="hikashop_filter_checkbox">'.$input.'<label class="filter_'.$filter->filter_id.'_'.$key.'_'.$divName.'"  for="field_'.$filter->filter_id.'_'.$key.'_'.$divName.'">'.JText::_(JText::_($val[1])).'</label>'.$deleteButton.'</span>'.$br;
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='sort'){
			$fields = $this->getFieldsForSort();

			if(!is_array($filter->filter_options['sort_by'])){
				$temp = $filter->filter_options['sort_by'];
				$filter->filter_options['sort_by'] = array();
				$filter->filter_options['sort_by'][] = $temp;
			}
			if(empty($filter->filter_options['sort_by']) || !count($filter->filter_options['sort_by'])) {
				$this->canBeUsed = false;
			} else {
				foreach($filter->filter_options['sort_by'] as $theType){
					$checked1=''; $checked2='';$deleteButton=''; $deleteButton2='';
					if(!empty($selected)){
						if($selected[0]==$theType.'--lth' || $selected[0]==$theType){
							$checked1='checked="checked" id="filter_'.$filter->filter_namekey.'_'.$divName.'"';
							if($type=='radio'){ $deleteButton='  '.parent::getDeleteButton($filter, $divName, '', $html, '', true); }
						}
						if($selected[0]==$theType.'--htl' || $selected[0]==$theType){
							$checked2='checked="checked" id="filter_'.$filter->filter_namekey.'_'.$divName.'"';
							if($type=='radio'){ $deleteButton2='  '.parent::getDeleteButton($filter, $divName, '', $html, '', true); }
						}
					}

					$fullType = $theType;
					if(in_array(substr($theType,-5), array('--lth', '--htl')))
						$theType = substr($theType, 0, -5);

					if(isset($fields[$theType])){ $typeName=$fields[$theType]->field_realname; }
					else if($theType=='b.product_name'){ $typeName= 'PRODUCT_NAME'; }
					else if($theType=='b.product_code'){ $typeName= 'PRODUCT_CODE'; }
					else if($theType=='price'){ $typeName= 'PRICE'; }
					else if($theType=='b.product_average_score'){ $typeName= 'RATING'; }
					else if($theType=='b.product_created' || $theType=='b.product_modified'){ $typeName= 'RECENT'; }
					else if($theType=='b.product_sales'){ $typeName= 'SALES'; }
					else if($theType=='b.product_hit'){ $typeName= 'CLICKS'; }
					else{ $typeName='PRODUCT_'.str_replace('b.','',$theType);	}

					if(JText::_('SORT_ASCENDING_'.$typeName)!='SORT_ASCENDING_'.$typeName){ $asc_name=JText::_('SORT_ASCENDING_'.$typeName); }
					else{ $asc_name=JText::sprintf('SORT_ASCENDING', JText::_($typeName)); }

					if(JText::_('SORT_DESCENDING_'.$typeName)!='SORT_DESCENDING_'.$typeName){ $desc_name=JText::_('SORT_DESCENDING_'.$typeName); }
					else{ $desc_name=JText::sprintf('SORT_DESCENDING', JText::_($typeName)); }

					if($fullType == $theType || $fullType == $theType.'--lth')
						$html.='<span class="hikashop_filter_checkbox"><input '.$onClick.' '.$checked1.' type="'.$type.'" name="filter_'.$filter->filter_namekey.$tab.'" id="field_'.$filter->filter_id.'_'.$theType.'_'.$divName.'_lth" value="'.$theType.'--lth"><label for="field_'.$filter->filter_id.'_'.$theType.'_'.$divName.'_lth">'.$asc_name.'</label>'.$deleteButton.'</span>'.$br;
					if($fullType == $theType || $fullType == $theType.'--htl')
						$html.='<span class="hikashop_filter_checkbox"><input '.$onClick.' '.$checked2.' type="'.$type.'" name="filter_'.$filter->filter_namekey.$tab.'" id="field_'.$filter->filter_id.'_'.$theType.'_'.$divName.'_htl" value="'.$theType.'--htl"><label for="field_'.$filter->filter_id.'_'.$theType.'_'.$divName.'_htl">'.$desc_name.'</label>'.$deleteButton2.'</span>'.$br;
				}
			}
		}

		$html = '<span id="filter_values_container_'.$filter->filter_namekey.'_'.$divName.'" class="filter_values_container">'.$html.'</span>';
		$html=parent::titlePosition($filter, $html);
		return $html;
	}

}

class hikashopListClass extends hikashopFilterTypeClass{

	function display(&$filter, $divName, &$parent, $datas=''){
		$selected=parent::display($filter, $divName, $parent);

		if(!($filter->filter_dynamic)){
			$datas='';
		}

		if($filter->filter_deletable && !empty($selected)) {
			if(is_string($selected))
				$selected = array($selected);
			if(isset($selected[0]) && !in_array($selected[0],array(" ", '', 'none'))) {
				$html=parent::displayInList($filter, $divName, $selected);
				return $html;
			}
		}

		$html='<input type="hidden" id="filter_'.$filter->filter_namekey.'_'.$divName.'" name="filter_'.$filter->filter_namekey.'" value=""/><ul class="hikashop_filter_list_style">';

		if($parent->ajax  && ($parent->params->get('module') != 'mod_hikashop_filter' || !$parent->params->get('force_redirect',0) || $parent->itemid == $parent->params->get('itemid'))) {
			$submit = 'window.hikashop.refreshFilters(this);';
		} else {
			$submit = 'document.forms[\'hikashop_filter_form_'.$divName.'\'].submit();';
		}
		if($filter->filter_data=='category'){
			$categories_name=parent::getCategories($filter, $datas);
			if(!empty($categories_name) && count($categories_name)){
				foreach($categories_name as $cat){
					if(!empty($selected) &&  (is_numeric($selected)&&$cat->category_id==$selected) ||(is_array($selected) && in_array($cat->category_id, $selected))){
						$html.='<li><a class="hikashop_filter_list_selected" style="font-weight:bold">'.$cat->category_name.'</a>';
						$html.='<a style="cursor:pointer; text-decoration:none" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\'\'; '.$submit.'" title="'.JText::_('REMOVE_THIS_SELECTION').'"> <i class="fa fa-times-circle"></i></a></li>';
					 }else{
						$html.='<li><a class="hikashop_filter_list" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\''.$cat->category_id.'\';'.$submit.'">'.$cat->category_name.'</a></li>';
					}
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if(($filter->filter_data=='price' || $filter->filter_data=='information' || $filter->filter_data=='custom_field') && !empty($filter->filter_value)){
			if(parent::checkCurrency($filter) == false){ return false;}
			$size=count($filter->filter_value);
			if(!empty($size)){
				foreach($filter->filter_value as $key => $value){
					list($formatVal, $oldVal)=parent::formatUnits($filter, $key, $value);

					if($key==0){
						if(!empty($selected) &&  (is_array($selected) && in_array('::'.$value, $selected)) || (is_string($selected) && $selected === '::'.$value)){
							$html.='<li><a class="hikashop_filter_list_selected" style="font-weight:bold">'.JText::sprintf('X_AND_INFERIOR',$formatVal).'</a>';
							$html.='<a style="cursor:pointer; text-decoration:none" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\'\';'.$submit.'" title="'.JText::_('REMOVE_THIS_SELECTION').'"> <i class="fa fa-times-circle"></i></a></li>';
						}else{
							$html.='<li><a class="hikashop_filter_list" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\'::'.$value.'\';'.$submit.'">'.JText::sprintf('X_AND_INFERIOR',$formatVal).'</a></li>';
						}
					}else{
						if(!empty($selected) &&  (is_array($selected) && in_array($filter->filter_value[$key-1].'::'.$value, $selected)) || (is_string($selected) && $selected === $filter->filter_value[$key-1].'::'.$value)){
							$html.='<li><a class="hikashop_filter_list_selected" style="font-weight:bold">'.JText::sprintf('FROM_X_TO_Y', $oldVal, $formatVal ).'</a>';
							$html.='<a style="cursor:pointer; text-decoration:none" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\'\';'.$submit.'" title="'.JText::_('REMOVE_THIS_SELECTION').'"> <i class="fa fa-times-circle"></i></a></li>';
						}else{
							$html.='<li><a class="hikashop_filter_list" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\''.$filter->filter_value[$key-1].'::'.$value.'\';'.$submit.'">'	.JText::sprintf('FROM_X_TO_Y', $oldVal, $formatVal ).'</a></li>';
						}
					}
					if($key==$size-1){
						if(!empty($selected) &&  (is_array($selected) && in_array($value.'::', $selected)) || (is_string($selected) && $selected === $value.'::')){
							$html.='<li><a class="hikashop_filter_list_selected" style="font-weight:bold">'.JText::sprintf('X_AND_SUPERIOR', $formatVal ).'</a>';
							$html.='<a style="cursor:pointer; text-decoration:none" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\'\';'.$submit.'"> title="'.JText::_('REMOVE_THIS_SELECTION').'" <i class="fa fa-times-circle"></i></a></li>';
						}else{
							$html.='<li><a class="hikashop_filter_list" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\''.$value.'::\';'.$submit.'">'.JText::sprintf('X_AND_SUPERIOR', $formatVal ).'</a></li>';
						}
					}
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='characteristic'){
			$characteristic_values=parent::getCharacteristics($filter, $datas);
			if(!empty($characteristic_values) && count($characteristic_values)){
				if(!is_array($selected))
					$selected = array($selected);
				foreach($characteristic_values as $val){
					if(!empty($selected) &&  is_array($selected) && in_array($val->characteristic_id, $selected)){
						$html.='<li><a class="hikashop_filter_list_selected" style="font-weight:bold">'.$val->characteristic_value.'</a>';
						$html.='<a style="cursor:pointer; text-decoration:none" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\'\';'.$submit.'" title="'.JText::_('REMOVE_THIS_SELECTION').'"> <i class="fa fa-times-circle"></i></a></li>';
					 }else{
						$html.='<li><a class="hikashop_filter_list" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\''.$val->characteristic_id.'\';'.$submit.'">'.$val->characteristic_value.'</a></li>';
					}
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='manufacturers'){
			$manufacturers=parent::getManufacturers($filter, $datas);
			if(!empty($manufacturers) && count($manufacturers)){
				if(!is_array($selected))
					$selected = array($selected);
				foreach($manufacturers as $manufacturer){
					if(!empty($selected) && is_array($selected) && in_array($manufacturer->category_id, $selected)){
						$html.='<li><a class="hikashop_filter_list_selected" style="font-weight:bold">'.$manufacturer->category_name.'</a>';
						$html.='<a style="cursor:pointer; text-decoration:none" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\'\';'.$submit.'" title="'.JText::_('REMOVE_THIS_SELECTION').'"> <i class="fa fa-times-circle"></i></a></li>';
					 }else{
						$html.='<li><a class="hikashop_filter_list" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\''.$manufacturer->category_id.'\';'.$submit.'">'.$manufacturer->category_name.'</a></li>';
					 }
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data=='custom_field'){
			$field=parent::getFields($filter, $datas);
			if(is_array($field->field_value) && count($field->field_value)){
				if(!is_array($selected))
					$selected = array($selected);
				foreach($field->field_value as $key => $val){
					if(!empty($selected) && is_array($selected) && in_array($val[0], $selected)){
						$html.='<li><a class="hikashop_filter_list_selected" style="font-weight:bold">'.JText::_($val[1]).'</a>';
						$html.='<a style="cursor:pointer; text-decoration:none" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\'\';'.$submit.'" title="'.JText::_('REMOVE_THIS_SELECTION').'"> <i class="fa fa-times-circle"></i></a></li>';
					 }else{
						$html.='<li><a class="hikashop_filter_list" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\''.$val[0].'\';'.$submit.'">'.JText::_($val[1]).'</a></li>';
					}
				}
			} else {
				$this->canBeUsed = false;
			}
		}

		if($filter->filter_data == 'sort') {
			$fields = $this->getFieldsForSort();

			if(!is_array($filter->filter_options['sort_by'])){
				$temp = $filter->filter_options['sort_by'];
				$filter->filter_options['sort_by'] = array();
				$filter->filter_options['sort_by'][] = $temp;
			}
			if(empty($filter->filter_options['sort_by']) || !count($filter->filter_options['sort_by'])) {
				$this->canBeUsed = false;
			} else {
				foreach($filter->filter_options['sort_by'] as $theType){
					$checked1=''; $checked2='';$deleteButton=''; $deleteButton2='';
					if(!empty($selected)){
						if(!is_array($selected))
							$selected = array($selected);
						if($selected[0]==$theType.'--lth' || $selected[0] == $theType){
							$checked1='checked="checked" id="filter_'.$filter->filter_namekey.'_'.$divName.'"';
							$deleteButton='  '.parent::getDeleteButton($filter, $divName, '', $html, '', true);
						}
						if($selected[0]==$theType.'--htl' || $selected[0] == $theType){
							$checked2='checked="checked" id="filter_'.$filter->filter_namekey.'_'.$divName.'"';
							$deleteButton2='  '.parent::getDeleteButton($filter, $divName, '', $html, '', true);
						}
					}

					$fullType = $theType;
					if(in_array(substr($theType,-5), array('--lth', '--htl')))
						$theType = substr($theType, 0, -5);

					if(isset($fields[$theType])){ $typeName=$fields[$theType]->field_realname; }
					else if($theType=='b.product_name'){ $typeName= 'PRODUCT_NAME'; }
					else if($theType=='b.product_code'){ $typeName= 'PRODUCT_CODE'; }
					else if($theType=='price'){ $typeName= 'PRICE'; }
					else if($theType=='b.product_average_score'){ $typeName= 'RATING'; }
					else if($theType=='b.product_created' || $theType=='b.product_modified'){ $typeName= 'RECENT'; }
					else if($theType=='b.product_sales'){ $typeName= 'SALES'; }
					else if($theType=='b.product_hit'){ $typeName= 'CLICKS'; }
					else{ $typeName='PRODUCT_'.str_replace('b.','',$theType);	}

					if(JText::_('SORT_ASCENDING_'.$typeName)!='SORT_ASCENDING_'.$typeName){ $asc_name=JText::_('SORT_ASCENDING_'.$typeName); }
					else{ $asc_name=JText::sprintf('SORT_ASCENDING', JText::_($typeName)); }

					if(JText::_('SORT_DESCENDING_'.$typeName)!='SORT_DESCENDING_'.$typeName){ $desc_name=JText::_('SORT_DESCENDING_'.$typeName); }
					else{ $desc_name=JText::sprintf('SORT_DESCENDING', JText::_($typeName)); }
					if($fullType == $theType || $fullType == $theType.'--lth') {
						if(!empty($checked1)){
							$html.='<li><a class="hikashop_filter_list_selected" style="font-weight:bold">'.$asc_name.'</a>'.$deleteButton.'</li>';
						}else{
							$html.='<li><a  class="hikashop_filter_list" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\''.$theType.'--lth\';'.$submit.'">'.$asc_name.'</a></li>';
						}
					}
					if($fullType == $theType || $fullType == $theType.'--htl') {
						if(!empty($checked2)){
							$html.='<li><a class="hikashop_filter_list_selected" style="font-weight:bold">'.$desc_name.'</a>'.$deleteButton2.'</li>';
						}else{
							$html.='<li><a class="hikashop_filter_list" data-container-div="hikashop_filter_form_'.$divName.'" onclick="document.getElementById(\'filter_'.$filter->filter_namekey.'_'.$divName.'\').value=\''.$theType.'--htl\';'.$submit.'">'.$desc_name.'</a></li>';
						}
					}
				}
			}
		}

		$html.='</ul>';
		$html=parent::titlePosition($filter, $html);
		return $html;
	}

}

class hikashopCursorClass extends hikashopFilterTypeClass{

	function display(&$filter, $divName, &$parent, $datas=''){
		$selected = parent::display($filter, $divName, $parent);

		if(!empty($selected) && !empty($selected[0]) && $selected != ' '){
			$values=parent::display($filter, $divName, $parent, '_values');
			if(!is_array($values)){
				$sep = ' - ';
				if(strpos($values, '::') !== false)
					$sep = '::';
				$values=explode($sep, $values);
			}
			if($filter->filter_deletable && $values[0] != 'none' && ( isset($values[0]) && !in_array($selected[0],array(" ",'')))){
				$html = parent::displayInList($filter, $divName, $values);
				return $html;
			}
		}

		$cursorMin=0;
		$cursorMax=1000;
		$cursorWidth='width:250px;';
		$float='';
		$deleteButton='';
		$options = array('step' => 1);
		$extrajs = '';
		$before_input = '';
		$after_input = '';

		if(!empty($datas) && $filter->filter_dynamic){
			if(!empty($filter->filterActive) && !empty($_SESSION['hk_filter'][$filter->filter_id][$filter->page_key])) {
				$cursorMin = $_SESSION['hk_filter'][$filter->filter_id][$filter->page_key][0];
				$cursorMax = $_SESSION['hk_filter'][$filter->filter_id][$filter->page_key][1];
			} else {
				$nameMax='max_'.$filter->filter_namekey;
				$nameMin='min_'.$filter->filter_namekey;
				$array = $datas['limits'];

				if(!empty($array->$nameMax)){
					$cursorMax=(float)$array->$nameMax;
				}else{
					$cursorMax=1000;
				}
				if(!empty($array->$nameMin)){
					$cursorMin=(float)$array->$nameMin;
				}else{
					$cursorMin=0;
				}
				$this->storeValuesInSession($filter, array($cursorMin, $cursorMax));
			}
		}

		if($cursorMin==0 && $filter->filter_options['cursor_min']!='none' && isset($filter->filter_options['cursor_min']) && !in_array($filter->filter_options['cursor_min'],array(" ",''))){
			$cursorMin=$filter->filter_options['cursor_min'];
		}
		if($cursorMax==1000 && !empty($filter->filter_options['cursor_max'])){
			$cursorMax=$filter->filter_options['cursor_max'];
		}
		$minVal=$cursorMin;
		$maxVal=$cursorMax;
		if(!empty($filter->filter_options['cursor_effect'])){
			$options['animate'] = 'true';
		}
		if(!empty($filter->filter_options['cursor_width'])){
			$cursorWidth='width:'.$filter->filter_options['cursor_width'].'px;';
		}
		if(!empty($filter->filter_options['cursor_step'])){
			$step = $filter->filter_options['cursor_step'];
			$options['step'] = $filter->filter_options['cursor_step'];
		}
		if(isset($values)){
			if(is_array($values) && count($values)!=2){
				$data =& $values[0];
			}else{
				$data =& $values;
			}

			if( isset($data) && (is_array($data) || !in_array($data,array(" ",'')))){
				if(!is_array($data)){
					$sep = ' - ';
					if(strpos($data, '::') !== false)
						$sep = '::';
					$data=explode($sep, $data);
				}

				$minVal=(float)@$data[0];
				$maxVal=(float)@$data[1];
			}
		}

		if($minVal>$maxVal){
			$tmp = $maxVal;
			$maxVal = $minVal;
			$minVal = $tmp;
		}
		if($cursorMin>$cursorMax){
			$tmp = $cursorMax;
			$cursorMax = $cursorMin;
			$cursorMin = $tmp;
		}
		$diff = $cursorMax - $cursorMin;
		if($diff) {
			$remainder = $this->modulo($diff, $options['step']);
			$cursorMax += $remainder;
		}

		$options['range'] = '{min: '.$cursorMin.', max: '.$cursorMax.'}';
		$options['start'] = '['.$minVal.', '.$maxVal.']';

		$onchange = '
			var id = "filter_'.$filter->filter_namekey.'_'.$divName.'_values";
			document.getElementById(id).value = values[ 0 ] + " - " + values[ 1 ];

			var defaultMin = Number(document.getElementById(id).getAttribute("data-min")),
			defaultMax = Number(document.getElementById(id).getAttribute("data-max"));
			if(defaultMin != Number(values[ 0 ]) || defaultMax != Number(values[ 1 ]))
				document.getElementById("filter_'.$filter->filter_namekey.'_'.$divName.'" ).value = 1;
			else
				document.getElementById("filter_'.$filter->filter_namekey.'_'.$divName.'" ).value = 0;
		';
		$extrajs.= '
			document.getElementById("filter_'.$filter->filter_namekey.'_'.$divName.'_values").setAttribute("data-min", "'.$minVal.'");
			document.getElementById("filter_'.$filter->filter_namekey.'_'.$divName.'_values").setAttribute("data-max", "'.$maxVal.'");
		';

		$change = '';
		if($filter->filter_direct_application){
			$change = 'document.forms[\'hikashop_filter_form_'.$divName.'\'].submit();';
			if($parent->ajax  && ($parent->params->get('module') != 'mod_hikashop_filter' || !$parent->params->get('force_redirect',0) || $parent->itemid == $parent->params->get('itemid'))) {
				$change = '
					window.hikashop.refreshFilters(document.getElementById("slider_'.$filter->filter_namekey.'_'.$divName.'"));
				';
			}
			$onchange .= '
				if(typeof window.localPage.drag_'.$filter->filter_namekey.'_'.$divName.' !== \'undefined\' && !window.localPage.drag_'.$filter->filter_namekey.'_'.$divName.' && (defaultMin != Number(values[ 0 ]) || defaultMax != Number(values[ 1 ]))) {
					'.$change.'
				}';
		}

		$options['connect'] = '[false, true, false]';
		$html='';
		$currencies = null;
		$currency = '';
		$currency_id = hikashop_getCurrency();

		if(in_array($filter->filter_data, array('price', 'b.product_sort_price'))) {
			$currencyClass = hikashop_get('class.currency');
			$currencies = $currencyClass->getCurrencies($currency_id, $currencies);
			$format = $currencies[$currency_id]->currency_format;
			if(strpos($format, '%n') !== false) {
				$currency.= ' '.$currencies[$currency_id]->currency_symbol;
			}
			if(strpos($format, '%i') !== false) {
				$currency.= ' '.$currencies[$currency_id]->currency_code;
			}
			$currency = trim($currency);
		}
		$currentLanguage = JFactory::getLanguage();
		$options['direction'] = "'ltr'";
		if($currentLanguage->isRtl())
			$options['direction'] = "'rtl'";
		$onupdate = '';
		if(!empty($filter->filter_options['input'])) {
			$options['tooltips'] = '[false, false]';
			$extrajs.="
document.getElementById('slider_".$filter->filter_namekey.'_'.$divName."_start').addEventListener('change', function() {
	this.value = this.value.replace(',','.');
	hkSlider.noUiSlider.set([this.value, null]);
	".$change."
});
document.getElementById('slider_".$filter->filter_namekey.'_'.$divName."_end').addEventListener('change', function() {
	this.value = this.value.replace(',','.');
	hkSlider.noUiSlider.set([null, this.value]);
	".$change."
});
			";
			$done = false;
			$value = 'values[handle]';
			if(isset($currencies[$currency_id])) {
				if($currencies[$currency_id]->currency_locale['p_cs_precedes']){
					$before_input = '<span class="hikashop_filter_cursor_currency" >'.$currency.'</span>';
					$done = true;
				}
				$value = 'parseFloat(values[handle]).toFixed('.(int)$currencies[$currency_id]->currency_locale['int_frac_digits'].')';
			}
			$onupdate .= "
if(handle)
	document.getElementById('slider_".$filter->filter_namekey.'_'.$divName."_end').value = ".$value.";
else
	document.getElementById('slider_".$filter->filter_namekey.'_'.$divName."_start').value = ".$value.";
			";

			if(!$done) {
				$after_input = '<span class="hikashop_filter_cursor_currency" >'.$currency.'</span>';
			}
		} else {

			$options['tooltips'] = '[true, true]';
			$format = null;
			if(in_array($filter->filter_data, array('price', 'b.product_sort_price'))) {
				$format = array(
					'mark' => '.',
					'thousand' => ' ',
					'prefix' => '',
					'suffix' => $currency,
					'decimals' => '2'
				);
				if(isset($currencies[$currency_id])) {
					if($currencies[$currency_id]->currency_locale['p_cs_precedes']){
						$format['prefix'] = $currency;
						if($currencies[$currency_id]->currency_locale['p_sep_by_space'])
							$format['prefix'] .= ' ';
						$format['suffix'] = '';
					}elseif($currencies[$currency_id]->currency_locale['p_sep_by_space'])
						$format['suffix'] = ' '.$format['suffix'];
					$format['thousand'] = $currencies[$currency_id]->currency_locale['mon_thousands_sep'];
					$format['mark'] = $currencies[$currency_id]->currency_locale['mon_decimal_point'];
					$format['decimals'] = $currencies[$currency_id]->currency_locale['int_frac_digits'];
				}

			}elseif(!empty($filter->filter_options['label_format'])) {
				$format = json_decode($filter->filter_options['label_format']);
			}
			if(!empty($format)) {
				$params = '';
				foreach($format as $key => $val) {
					$params.="\r\n".$key.': \''.$val.'\',';
				}

				$tipFormat = ' wNumb({'.$params.'})';
				$options['tooltips'] = '['.$tipFormat.', '.$tipFormat.']';
				hikashop_loadJslib('wnumb');
			}
		}

		if($cursorMin != $cursorMax) {
			$params = '';
			foreach($options as $key => $option) {
				$params.="\r\n".$key.': '.$option.',';
			}
			hikashop_loadJslib('nouislider');
			$js = "
window.hikashop.ready(function(){
	if(!window.localPage) window.localPage = {};
	var hkSlider = document.getElementById('slider_".$filter->filter_namekey.'_'.$divName."');
	".$extrajs."

	noUiSlider.create(hkSlider, {
		".$params."
	});

	hkSlider.noUiSlider.on('update', function (values, handle) {
		".$onupdate."
	});
	hkSlider.noUiSlider.on('set', function (values, handle) {
		".$onchange."
	});
	hkSlider.noUiSlider.on('start', function (values, handle) {
		window.localPage.drag_".$filter->filter_namekey.'_'.$divName." = true;
	});
	hkSlider.noUiSlider.on('end', function (values, handle) {
		window.localPage.drag_".$filter->filter_namekey.'_'.$divName." = false;
		".$change."
	});
});
";

		}elseif(empty($filter->filter_options['input'])) {
			if(in_array($filter->filter_data, array('price', 'b.product_sort_price'))) {
				$currencyClass = hikashop_get('class.currency');
				$html = $currencyClass->format($minVal, $currency_id);
			} else {
				$html = $minVal;
			}
		}

		$hasValue='';
		if(!empty($selected) && $selected[0] == 1 && $values[0] != 'none' && isset($data) && !in_array($data, array(" ",''))) {
			$deleteButton = '  ' . parent::getDeleteButton($filter, $divName, '11', $html, '', true);
			$hasValue = '1';
		}

		if($filter->filter_options['title_position'] == 'left') {
			$float = 'float:left;';
		}

		$html = '<div style="'.$cursorWidth.' '.$float.' margin: 10px 20px;" id="slider_'.$filter->filter_namekey.'_'.$divName.'" data-container-div="hikashop_filter_form_'.$divName.'">'.$html.'</div>';
		if(!empty($js))
			$html .= '<script>'.$js.'</script>';
		if(!empty($filter->filter_options['input'])) {
			$html = '
<span class="slider_input_start_group">
	<span class="slider_input_text slider_input_start_text">'.JText::_('CURSOR_FILTER_FROM').'</span>'.
	$before_input.
	'<input class="slider_input slider_input_start" id="slider_'.$filter->filter_namekey.'_'.$divName.'_start" value="'.$minVal.'"/>'.
	$after_input.
'</span>
<span class="slider_input_end_group">
	<span class="slider_input_text slider_input_end_text">'. JText::_('CURSOR_FILTER_TO').'</span>'.
	$before_input.
	'<input class="slider_input slider_input_end" id="slider_'.$filter->filter_namekey.'_'.$divName.'_end" value="'.$maxVal.'"/>'.
	$after_input.
'</span>'.
$html;
		}

		$val = '';
		if($hasValue) {
			$val = $minVal.' - '.$maxVal;
		}

		$html = $this->cursorTitlePosition($filter, $html, $cursorWidth, $divName, $deleteButton, $val);

		return $html;
	}

	function modulo($x, $y) {
		$i = floor($x / $y);
		return $x - $i * $y;
	}

	function cursorTitlePosition($filter, $html, $width, $divName, $deleteButton, $val){
		$unit='';
		if(empty($filter->filter_options['range_size'])){
			$size=10;
		}else{
			$size=$filter->filter_options['range_size'];
		}
		$input='<input size="'.$size.'" type="hidden" name="filter_'.$filter->filter_namekey.'" id="filter_'.$filter->filter_namekey.'_'.$divName.'" value="'.(empty($val) ? '0' : '1').'"/>';
		$input.='<input size="'.$size.'" type="hidden" name="filter_'.$filter->filter_namekey.'_values" id="filter_'.$filter->filter_namekey.'_'.$divName.'_values" '.(empty($val)? '' : 'values="'.$val.'"').'/>';
		$input.='<span id="filter_span_'.$filter->filter_namekey.'_'.$divName.'" class="hikashop_filter_cursor_range"></span>'.$deleteButton;
		if($filter->filter_data=='weight' || $filter->filter_data=='length' || $filter->filter_data=='width' || $filter->filter_data=='height' || $filter->filter_data=='surface' || $filter->filter_data=='volume'){
			$unit=' ('.hikashop_translate($filter->filter_options['information_unit']).')';
		}
		$name=parent::trans($filter->filter_name).$unit;
		$position=$filter->filter_options['title_position'];

		switch($position){
			case 'top_left':
				$html=$name.JText::_('CURSOR_TITLE_SEPARATOR').$input.$html;
				break;
			case 'top_right':
				$html='<div class="filter_'.$filter->filter_namekey.'_'.$divName.'_title_div" style="'.$width.' text-align:right;">'.$name.JText::_('CURSOR_TITLE_SEPARATOR').$input.''.$html.'</div>';
				break;
			case 'top_center':
				$html='<div class="filter_'.$filter->filter_namekey.'_'.$divName.'_title_div" style="'.$width.' text-align:center;">'.$name.JText::_('CURSOR_TITLE_SEPARATOR').$input.' '.$html.'</div>';
				break;
			case 'bottom_left':
				$html=$html.$name.JText::_('CURSOR_TITLE_SEPARATOR').$input;
				break;
			case 'bottom_right':
				$html='<div class="filter_'.$filter->filter_namekey.'_'.$divName.'_title_div" style="'.$width.' text-align:right;">'.$html.$name.JText::_('CURSOR_TITLE_SEPARATOR').$input.'</div>';
				break;
			case 'bottom_center':
				$html='<div class="filter_'.$filter->filter_namekey.'_'.$divName.'_title_div" style="'.$width.' text-align:center;">'.$html.$name.JText::_('CURSOR_TITLE_SEPARATOR').$input.'</div>';
				break;
		}
		return $html;
	}

	function getFieldToLoad($filter, $divName, &$parent){
		if($filter->filter_data=='weight' || $filter->filter_data=='length' || $filter->filter_data=='width' || $filter->filter_data=='height' || $filter->filter_data=='surface' || $filter->filter_data=='volume'){
			if($filter->filter_data=='surface')
				return 'b.product_width*b.product_length';
			if($filter->filter_data=='volume')
				return 'b.product_width*b.product_length*b.product_height';
			return 'b.product_'.$filter->filter_data;
		}
		if($filter->filter_data=='price')
			return 'pprice.price_value';
		if(substr($filter->filter_data,0,2)!='b.')
			return 'b.'.$filter->filter_data;
		return $filter->filter_data;
	}

}

class hikashopMultipledropdownClass extends hikashopSingledropdownClass{
	function display(&$filter, $divName, &$parent, $datas='', $multiple='', $tab=''){
		$multiple='multiple="multiple" size="5" data-placeholder="'.$filter->filter_name.'"';
		$tab='[]';
		return parent::display($filter, $divName, $parent, $datas, $multiple, $tab);
	}
}

class hikashopCheckboxClass extends hikashopRadioClass{
	function display(&$filter, $divName, &$parent, $datas='', $type='',$tab=''){
		$type='checkbox';
		$tab='[]';
		return parent::display($filter, $divName, $parent, $datas, $type,$tab);
	}
}

class hikashopInStockCheckboxClass extends hikashopFilterTypeClass{

	function getFieldToLoad($filter, $divName, &$parent){
		return '';
	}

	function display(&$filter, $divName, &$parent, $datas='', $type='',$tab=''){
		$html = '';
		$selected = parent::display($filter, $divName, $parent);
		$checked = '';
		$value = '';

		if($selected == 'in_stock'){
			$checked = 'checked="checked"';
			$value = 'in_stock';
		}
		$onClick = '';
		if($filter->filter_direct_application){
			if($parent->ajax  && ($parent->params->get('module') != 'mod_hikashop_filter' || !$parent->params->get('force_redirect',0) || $parent->itemid == $parent->params->get('itemid'))) {
				$onClick = 'window.hikashop.refreshFilters(this);';
			} else {
				$onClick = 'document.forms[\'hikashop_filter_form_'.$divName.'\'].submit();';
			}
		}
		$onClick = 'onchange="var hiddenInput = document.getElementById(\'filter_'.$filter->filter_namekey.$tab.'_hidden\');if(this.checked){ hiddenInput.value=\'in_stock\'; }else{ hiddenInput.value=\'\'; } '.$onClick.'"';

		$html.= '<span class="hikashop_filter_checkbox"><input data-container-div="hikashop_filter_form_'.$divName.'"'.$onClick.' '.$checked.' type="checkbox" name="filter_'.$filter->filter_namekey.$tab.'_checkbox" value="in_stock"><input id="filter_'.$filter->filter_namekey.$tab.'_hidden" type="hidden" name="filter_'.$filter->filter_namekey.$tab.'" value="'.$value.'"></span>';
		$html.='<span style="margin-left: 5px;">'.$filter->filter_name.'</span>';
		return $html;
	}
}
