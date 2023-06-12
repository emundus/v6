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
class plgHikashopMassaction_category extends JPlugin
{
	public $message = '';
	public $massaction = null;
	public $category = null;

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		$this->massaction = hikashop_get('class.massaction');
		$this->massaction->datecolumns = array( 'category_created',
												'category_modified'
											);
		$this->category = hikashop_get('class.category');
	}

	function onMassactionTableLoad(&$externalValues){
		$obj = new stdClass();
		$obj->table ='category';
		$obj->value ='category';
		$obj->text =JText::_('CATEGORY');
		$externalValues[] = $obj;
	}

	function onMassactionTableTriggersLoad(&$table, &$triggers, &$triggers_html, &$loadedData) {
		if($table->table != 'category')
			return true;

		$triggers['onBeforeCategoryCreate']=JText::_('BEFORE_A_CATEGORY_IS_CREATED');
		$triggers['onBeforeCategoryUpdate']=JText::_('BEFORE_A_CATEGORY_IS_UPDATED');
		$triggers['onBeforeCategoryDelete']=JText::_('BEFORE_A_CATEGORY_IS_DELETED');
		$triggers['onAfterCategoryCreate']=JText::_('AFTER_A_CATEGORY_IS_CREATED');
		$triggers['onAfterCategoryUpdate']=JText::_('AFTER_A_CATEGORY_IS_UPDATED');
		$triggers['onAfterCategoryDelete']=JText::_('AFTER_A_CATEGORY_IS_DELETED');
	}

	function onMassactionTableFiltersLoad(&$table,&$filters,&$filters_html,&$loadedData){
		if($table->table != 'category')
			return true;

		$type = 'filter';
		$tables = array('category','parent_category');

		$filters['categoryType']=JText::_('CATEGORY_TYPE');
		$loadedData->massaction_filters['__num__'] = new stdClass();
		$loadedData->massaction_filters['__num__']->type = 'category';
		$loadedData->massaction_filters['__num__']->data = array();
		$loadedData->massaction_filters['__num__']->name = 'categoryType';
		$loadedData->massaction_filters['__num__']->data['type'] = 'all';
		$loadedData->massaction_filters['__num__']->html = '';

		foreach($loadedData->massaction_filters as $key => &$value) {
			if($value->name != 'categoryType' || ($table->table != $loadedData->massaction_table && is_int($key)))
				continue;

			$value->type = 'category';
			$category = hikashop_get('type.category');
			$category->onchange='countresults(\''.$table->table.'\','.$key.');';
			$output = $category->display('filter['.$table->table.']['.$key.'][categoryType][type]',$value->data['type']);
			$filters_html[$value->name] = $this->massaction->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
		}
	}

	function onProcessCategoryMassFilterlimit(&$elements, &$query,$filter,$num){
		$query->start = (int)$filter['start'];
		$query->value = (int)$filter['value'];
	}

	function onProcessCategoryMassFilterordering(&$elements, &$query,$filter,$num){
		if(!empty($filter['value'])){
			if(isset($query->ordering['default']))
				unset($query->ordering['default']);
			$query->ordering[] = $filter['value'];
		}
	}

	function onProcessCategoryMassFilterdirection(&$elements, &$query,$filter,$num){
		if(empty($query->ordering))
			$query->ordering['default'] = 'category_id';
		$query->direction = $filter['value'];
	}

	function onProcessCategoryMassFiltercategoryColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->category_type != 'product'){ unset($elements[$k]); continue; }
				$in = $this->massaction->checkInElement($element, $filter);
				if(!$in) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->where[] = $this->massaction->getRequest($filter,'hk_category');
			}
		}
	}
	function onCountCategoryMassFiltercategoryColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessCategoryMassFiltercategoryColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_category.category_id'));
	}
	function onProcessCategoryMassFiltercategoryType(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->category_type!=$filter['type']) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			$query->where[] = 'hk_category.category_type = '.$db->quote($filter['type']);
		 }
	}
	function onCountCategoryMassFiltercategoryType(&$query,$filter,$num){
		$elements = array();
		$this->onProcessCategoryMassFiltercategoryType($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_category.category_id'));
	}

	function onProcessCategoryMassFilterparent_categoryColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$db = JFactory::getDBO();
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->category_type != 'product'){ unset($elements[$k]); continue; }
				$categoryClass = hikashop_get('class.category');
				$result = $categoryClass->getParents($element->category_id,$element->category_parent_id);
				$in = $this->massaction->checkInElement($result[0], $filter);
				if(!$in) unset($elements[$k]);
			}
		}else{
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->leftjoin['parent_category'] = hikashop_table('category').' AS hk_parent_category ON hk_parent_category.category_id = hk_category.category_parent_id';
				$query->where[] = $this->massaction->getRequest($filter,'hk_parent_category');
			}
		 }
	}
	function onCountCategoryMassFilterparent_categoryColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessCategoryMassFilterparent_categoryColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_category.category_id'));
	}
	function onProcessCategoryMassFilteraccessLevel(&$elements,&$query,$filter,$num){
		$this->massaction->_onProcessMassFilteraccessLevel($elements,$query,$filter,$num,'category');
	}
	function onCountCategoryMassFilteraccessLevel(&$query,$filter,$num){
		$elements = array();
		$this->onProcessCategoryMassFilteraccessLevel($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_CATEGORIES',$query->count('hk_category.category_id'));
	}

	function onProcessCategoryMassActiondisplayResults(&$elements,&$action,$k){
		$params = $this->massaction->_displayResults('category',$elements,$action,$k);
		$params->action_id = $k;
		$js = '';
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') && hikaInput::get()->getVar('ctrl','massaction') == 'massaction'){
			echo hikashop_getLayout('massaction','results',$params,$js);
		}

	}
	function onProcessCategoryMassActionexportCsv(&$elements,&$action,$k){
		$formatExport = $action['formatExport']['format'];
		$path = $action['formatExport']['path'];
		$email = $action['formatExport']['email'];
		if(!empty($path)){
			$url = $this->massaction->setExportPaths($path);
		}else{
			$url = array('server'=>'','web'=>'');
			ob_get_clean();
		}
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') || (!hikashop_isClient('administrator') && !empty($path))){
			$action['category']['category_id'] = 'category_id';
			unset($action['formatExport']);
			$params = $this->massaction->_displayResults('category',$elements,$action,$k);
			$params->formatExport = $formatExport;
			$params->path = $url['server'];
			$params = $this->massaction->sortResult($params->table,$params);
			$this->massaction->_exportCSV($params);
		}
		if(!empty($email) && !empty($path)){
			$config = hikashop_config();
			$mailClass = hikashop_get('class.mail');
			$content = array('type' => 'csv_export');
			$mail = $mailClass->get('massaction_notification',$content);
			$mail->subject = JText::_('MASS_CSV_EMAIL_SUBJECT');
			$mail->html = '1';
			$csv = new stdClass();
			$csv->name = basename($path);
			$csv->filename = $url['server'];
			$csv->url = $url['web'];
			$mail->attachments = array($csv);
			$mail->dst_name = '';
			$mail->dst_email = explode(',',$email);
			$mailClass->sendMail($mail);
		}
	}
	function onProcessCategoryMassActionupdateValues(&$elements,&$action,$k){
		$current = 'category';
		$current_id = $current.'_id';
		$ids = array();
		foreach($elements as $element){
			$ids[] = $element->$current_id;
			if(isset($element->$action['type']))
				$element->$action['type'] = $action['value'];

		}
		$action['type'] = strip_tags($action['type']);

		$alias = array('category');

		$queryTables = array($current);
		$possibleTables = array($current);
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$value = $this->massaction->updateValuesSecure($action,$possibleTables,$queryTables);
		hikashop_toInteger($ids);
		$db = JFactory::getDBO();

		$max = 500;
		if(count($ids) > $max){
			$c = ceil((int)count($ids) / $max);
			for($i = 0; $i < $c; $i++){
				$offset = $max * $i;
				$id = array_slice($ids, $offset, $max);
				$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
				$query .= 'SET hk_'.$alias[0].'.'.$action['type'].' = '.$value.' ';
				$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$id).')';
				$db->setQuery($query);
				$db->execute();
			}
		}else{
			$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
			$query .= 'SET hk_'.$alias[0].'.'.$action['type'].' = '.$value.' ';
			$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$ids).')';
			$db->setQuery($query);
			$db->execute();
		}
	}
	function onProcessCategoryMassActiondeleteElements(&$elements,&$action,$k){
		$ids = array();
		foreach($elements as $element){
			if(in_array($element->category_namekey,array('root','product','manufacturer','tax')) || $element->category_type == 'status')
				continue;
			$ids[] = $element->category_id;
		}
		$categoryClass = hikashop_get('class.category');

		$max = 500;
		if(count($ids) > $max){
			$c = ceil((int)count($ids) / $max);
			for($i = 0; $i < $c; $i++){
				$offset = $max * $i;
				$id = array_slice($ids, $offset, $max);
				$result = $categoryClass->delete($id);
			}
		}else{
			$result = $categoryClass->delete($ids);
		}
	}
	function onProcessCategoryMassActionsendEmail(&$elements,&$action,$k){
		if(!empty($action['emailAddress'])){
			$config = hikashop_config();
			$mailClass = hikashop_get('class.mail');
			$content = array('elements' => $elements, 'action' => $action, 'type' => 'category_notification');
			$mail = $mailClass->get('massaction_notification',$content);
			$mail->subject = !empty($action['emailSubject'])?JText::_($action['emailSubject']):JText::_('MASS_NOTIFICATION_EMAIL_SUBJECT');
			$mail->body = $action['bodyData'];
			$mail->html = '1';
			$mail->dst_name = '';
			if(!empty($action['emailAddress']))
				$mail->dst_email = explode(',',$action['emailAddress']);
			else
				$mail->dst_email = $config->get('from_email');
			$mailClass->sendMail($mail);
		}
	}
	function onBeforeCategoryCreate(&$element,&$do){
		$elements = array($element);
		$this->massaction->trigger('onBeforeCategoryCreate',$elements);
	}

	function onAfterCategoryCreate(&$element){
		$elements = array($element);
		$this->massaction->trigger('onAfterCategoryCreate',$elements);
	}

	function onBeforeCategoryUpdate(&$element,&$do){
		$getCategory = $this->category->get($element->category_id);
		if(empty($getCategory)) return true;
		foreach($getCategory as $key => $value){
			if(isset($element->$key) && $getCategory->$key != $element->$key){
				$getCategory->$key = $element->$key;
			}
		}
		$categories = array($getCategory);
		$this->massaction->trigger('onBeforeCategoryUpdate',$categories);
	}

	function onAfterCategoryUpdate(&$element){
		$getCategory = $this->category->get($element->category_id);
		if(empty($getCategory)) return true;
		foreach($getCategory as $key => $value){
			if(isset($element->$key) && $getCategory->$key != $element->$key){
				$getCategory->$key = $element->$key;
			}
		}
		$categories = array($getCategory);
		$this->massaction->trigger('onAfterCategoryUpdate',$categories);
	}

	function onBeforeCategoryDelete(&$ids,&$do){
		$toDelete = array();
		if(!is_array($ids)) $clone = array($ids);
		else $clone = $ids;
		foreach($clone as $id){
			$getCategory = $this->category->get($id);
			if(empty($getCategory)) continue;
			$toDelete[] = $getCategory;
		}
		$this->deletedCat = &$toDelete;
		if(!count($toDelete)) return true;
		$this->massaction->trigger('onBeforeCategoryDelete',$toDelete);
	}

	function onAfterCategoryDelete(&$ids){
		if(!count($this->deletedCat)) return true;
		$this->massaction->trigger('onAfterCategoryDelete',$this->deletedCat);
	}
}
