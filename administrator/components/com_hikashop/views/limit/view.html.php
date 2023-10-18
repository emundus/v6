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
class LimitViewLimit extends hikashopView{
	var $type = '';
	var $ctrl= 'limit';
	var $nameListing = 'LIMIT';
	var $nameForm = 'LIMIT';
	var $icon = 'tachometer fa-tachometer-alt';

	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.limit_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = HikaStringHelper::strtolower(trim($pageInfo->search));
		$pageInfo->filter->limit_type = $app->getUserStateFromRequest( $this->paramBase.".limit_type",'limit_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$searchMap = array('a.limit_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}

		$query = ' FROM '.hikashop_table('limit').' AS a';
		if(!empty($pageInfo->filter->limit_type)){
			switch($pageInfo->filter->limit_type){
				case 'all':
					break;
				default:
					$filters[] = 'a.limit_type = '.$database->Quote($pageInfo->filter->limit_type);
					break;
			}
		}
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'limit_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->elements->page){
			$productIds = array();
			$categoryIds = array();
			$zoneIds = array();
			foreach($rows as $k => $row){
				if(!empty($row->limit_product_id)) $productIds[] = $row->limit_product_id;
				if(!empty($row->limit_category_id)) {
					$rows[$k]->limit_category_id = explode(',', $row->limit_category_id);
					foreach($rows[$k]->limit_category_id as $id) {
						if(!empty($id))
							$categoryIds[] = $id;
					}
				}
			}
			if(!empty($productIds)){
				$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$productIds).')';
				$database->setQuery($query);
				$products = $database->loadObjectList();
				foreach($rows as $k => $row){
					if(!empty($row->limit_product_id)){
						$found = false;
						foreach($products as $product){
							if($product->product_id==$row->limit_product_id){
								foreach(get_object_vars($product) as $field => $value){
									$rows[$k]->$field = $product->$field;
								}
								$found = true;
							}
						}
						if(!$found){
							$rows[$k]->product_name=JText::_('PRODUCT_NOT_FOUND');
						}
					}
				}
			}
			if(!empty($categoryIds)){
				$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$categoryIds).')';
				$database->setQuery($query);
				$categories = $database->loadObjectList();
				foreach($rows as $k => $row){
					if(!empty($row->limit_category_id)){
						$rows[$k]->categories = array();
						foreach($row->limit_category_id as $i => $id) {
							if(empty($id))
								continue;
							$found = false;
							foreach($categories as $category){
								if($category->category_id==$id){
									$rows[$k]->categories[] = $category;
									$found = true;
									break;
								}
							}
							if(!$found){
								$category = new stdClass();
								$category->category_name = JText::_('CATEGORY_NOT_FOUND');
								$category->category_id = $id;
								$rows[$k]->categories[] = $category;
							}
						}
					}
				}
			}
		}

		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 0;

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_limit_manage','all'));
		$this->assignRef('manage',$manage);

		$this->manage_category = hikashop_isAllowed($config->get('acl_category_manage','all'));
		$this->toolbar = array(
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_limit_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$limitType = hikashop_get('type.limit');
		$this->assignRef('limit_type',$limitType);

		$weightType = hikashop_get('type.weight');
		$this->assignRef('limit_unit',$weightType);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();

		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
	}

	function form(){
		$limit_id = hikashop_getCID('limit_id',false);
		if(!empty($limit_id)){
			$class = hikashop_get('class.limit');
			$element = $class->get($limit_id);
			$task='edit';
		}else{
			$element = hikaInput::get()->getVar('fail');
			if(empty($element)){
				$element = new stdClass();
				$app = JFactory::getApplication();
				$type = $app->getUserState( $this->paramBase.".filter_type");
				if(!in_array($type,array('all','nochilds'))){
					$element->limit_type = $type;
				}else{
					$element->limit_type = 'limit';
				}
				$element->limit_published=1;
				$element->limit_status = array('created','confirmed','shipped');
			}
			$task='add';
		}

		if(!empty($element->limit_category_id)){
			$element->limit_category_id = explode(',', trim($element->limit_category_id, ','));
		}

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&limit_id='.$limit_id);

		$this->toolbar = array(
			array('name' => 'group', 'buttons' => array( 'apply', 'save')),
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$this->assignRef('element',$element);
		$discountType = hikashop_get('type.limit');
		$this->assignRef('type',$discountType);
		$weightType = hikashop_get('type.weight');
		$this->assignRef('unit',$weightType);
		$status = hikashop_get('type.categorysub');
		$status->type='status';
		$this->assignRef('status',$status);
		$currencyType = hikashop_get('type.currency');
		$this->assignRef('currency',$currencyType);
		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type='tax';
		$categoryType->field='category_id';
		$this->assignRef('categoryType',$categoryType);

		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);
	}

}
