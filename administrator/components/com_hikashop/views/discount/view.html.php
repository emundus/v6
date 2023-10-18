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
class DiscountViewDiscount extends hikashopView {
	var $type = '';
	var $ctrl= 'discount';
	var $nameListing = 'DISCOUNTS';
	var $nameForm = 'DISCOUNTS';
	var $icon = 'percent';

	function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	public function listing($extendedData = true) {
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.discount_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = HikaStringHelper::strtolower(trim($pageInfo->search));
		$pageInfo->filter->filter_type = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$searchMap = array('a.discount_code','a.discount_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}

		$query = ' FROM '.hikashop_table('discount').' AS a';
		if(!empty($pageInfo->filter->filter_type)){
			switch($pageInfo->filter->filter_type){
				case 'all':
					break;
				default:
					$filters[] = 'a.discount_type = '.$database->Quote($pageInfo->filter->filter_type);
					if($pageInfo->filter->filter_type=='coupon'){
						$this->nameListing = 'COUPONS';
					}
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
			$rows = hikashop_search($pageInfo->search,$rows,'discount_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->elements->page && $extendedData){

			$types = array(
				'product' => 'product_name',
				'category'  => 'category_name',
				'zone'  => 'zone_name_english',
				'user' => 'user_email');
			$productClass = hikashop_get('class.product');
			foreach($types as $type => $name){
				$ids = array();
				$key = 'discount_'.$type.'_id';
				foreach($rows as $row){
					if(empty($row->$key)) continue;

					$row->$key = explode(',',$row->$key);
					foreach($row->$key as $v){
						if(is_numeric($v)){
							$ids[$v]=$v;
						}else{
							$ids[$v]=$database->Quote($v);
						}
					}
				}
				if(!count($ids)){
					continue;
				}

				if($type=='zone'){
					$primary = $type.'_namekey';
				}else{
					$primary = $type.'_id';
				}
				$query = 'SELECT * FROM '.hikashop_table($type).' WHERE '.$primary.' IN ('.implode(',',$ids).')';
				$database->setQuery($query);
				$elements = $database->loadObjectList();

				static $parents = array();

				foreach($rows as $k => $row){
					if(empty($row->$key))
						continue;
					$display = array();
					foreach($row->$key as $el){
						foreach($elements as $element){
							if($element->$primary!=$el)
								continue;
							if(!empty($element->product_parent_id)){
								if(!isset($parents[$element->product_parent_id]))
									$parents[$element->product_parent_id] = $productClass->get($element->product_parent_id);
								if(!empty($parents[$element->product_parent_id])){
									$db = JFactory::getDBO();
									$query = 'SELECT * FROM '.hikashop_table('variant').' AS v '.
										' LEFT JOIN '.hikashop_table('characteristic') .' AS c ON v.variant_characteristic_id = c.characteristic_id '.
										' WHERE v.variant_product_id = '.(int)$element->product_id.' ORDER BY v.ordering';
									$db->setQuery($query);
									$element->characteristics = $db->loadObjectList();
									$productClass->checkVariant($element, $parents[$element->product_parent_id]);
								}
							}
							$display[] = $element->$name;
							$found = true;
							break;
						}
					}
					if(!count($display)){
						$display = array(JText::_(strtoupper($type).'_NOT_FOUND'));
					}
					$rows[$k]->$key = implode(', ',$display);
				}
			}
		}

		if($pageInfo->limit->value == 500)
			$pageInfo->limit->value = 100;

		hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_discount_manage','all'));
		$this->assignRef('manage',$manage);
		$exportIcon = 'archive';
		if(HIKASHOP_J30) {
			$exportIcon = 'export';
		}
		$this->toolbar = array(
			array('name' => 'export'),
			array('name' => 'copy','display'=>$manage),
			array('name' => 'addNew','display'=>$manage),
			array('name' => 'editList','display'=>$manage),
			array('name' => 'deleteList','display'=>hikashop_isAllowed($config->get('acl_discount_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$this->loadRef(array(
			'searchType' => 'type.search',
			'filter_type' => 'type.discount',
			'toggleClass' => 'helper.toggle',
			'currencyHelper' => 'class.currency',
		));

		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
	}

	public function export() {
		$this->listing(false);
	}

	public function selection($tpl = null) {
		$this->listing($tpl, true);

		$elemStruct = array(
			'discount_id',
			'discount_code'
		);
		$this->assignRef('elemStruct', $elemStruct);

		$singleSelection = hikaInput::get()->getVar('single', false);
		$this->assignRef('singleSelection', $singleSelection);
	}

	public function useselection() {
		$selection = hikaInput::get()->get('cid', array(), 'array');
		$rows = array();
		$data = '';

		$elemStruct = array(
			'discount_id',
			'discount_code'
		);

		if(!empty($selection)) {
			hikashop_toInteger($selection);
			$db = JFactory::getDBO();
			$query = 'SELECT a.* FROM '.hikashop_table('discount').' AS a  WHERE a.discount_id IN ('.implode(',',$selection).')';
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if(!empty($rows)) {
				$data = array();
				foreach($rows as $v) {
					$d = '{id:'.$v->user_id;
					foreach($elemStruct as $s) {
						if($s == 'id')
							continue;
						$d .= ','.$s.':"'. str_replace('"', '\"', $v->$s).'"';
					}
					$data[] = $d.'}';
				}
				$data = '['.implode(',', $data).']';
			}
		}
		$this->assignRef('rows', $rows);
		$this->assignRef('data', $data);

		$confirm = hikaInput::get()->getVar('confirm', true, '', 'boolean');
		$this->assignRef('confirm', $confirm);
		if($confirm) {
			$js = 'window.hikashop.ready( function(){window.top.hikashop.submitBox('.$data.');});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
	}

	public function form() {
		$discount_id = hikashop_getCID('discount_id', false);

		if(!empty($discount_id)) {
			$disountClass = hikashop_get('class.discount');
			$element = $disountClass->get($discount_id);
			$task = 'edit';
		} else {
			$element = hikaInput::get()->getVar('fail');
			if(empty($element)) {
				$element = new stdClass();
				$app = JFactory::getApplication();
				$type = $app->getUserState($this->paramBase . '.filter_type');
				if(!in_array($type,array('all','nochilds'))) {
					$element->discount_type = $type;
				} else {
					$element->discount_type = 'discount';
				}
				if($type == 'coupon') {
					$element->discount_tax = 1;
				}
				$element->discount_published=1;

			}
			$task = 'add';
		}

		if($element->discount_type == 'coupon')
			$this->nameForm = 'HIKASHOP_COUPON';
		if($element->discount_type == 'discount')
			$element->discount_tax = 0;


		$this->assignRef('element', $element);

		hikashop_setTitle(JText::_($this->nameForm), $this->icon,$this->ctrl.'&task='.$task.'&discount_id='.$discount_id);

		hikashop_loadJsLib('tooltip');

		$this->toolbar = array(
			'save-group',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type = 'tax';
		$categoryType->field = 'category_id';
		$this->assignRef('categoryType', $categoryType);

		$this->loadRef(array(
			'type' => 'type.discount',
			'currency' => 'type.currency',
			'popup' => 'helper.popup',
			'nameboxType' => 'type.namebox',
		));
	}

	function select_coupon() {
		$badge = hikaInput::get()->getVar('badge','false');
		$this->assignRef('badge',$badge);
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.discount_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = HikaStringHelper::strtolower(trim($pageInfo->search));
		$pageInfo->filter->filter_type = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$searchMap = array('a.discount_code','a.discount_id');
		$filters = array();
		if($badge!='false'){ $filters[]='a.discount_type="discount"'; }
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$query = ' FROM '.hikashop_table('discount').' AS a';
		if($badge=='false' && !empty($pageInfo->filter->filter_type)){
			switch($pageInfo->filter->filter_type){
				case 'all':
					break;
				default:
					$filters[] = 'a.discount_type = '.$database->Quote($pageInfo->filter->filter_type);
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
			$rows = hikashop_search($pageInfo->search,$rows,'discount_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements=new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_discount_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name' => 'custom', 'icon' => 'copy', 'task' => 'copy', 'alt' => JText::_('HIKA_COPY'),'display'=>$manage),
			array('name' => 'addNew','display'=>$manage),
			array('name' => 'editList','display'=>$manage),
			array('name' => 'deleteList','display'=>hikashop_isAllowed($config->get('acl_discount_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
		$discountType = hikashop_get('type.discount');
		$this->assignRef('filter_type',$discountType);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
	}

	function add_coupon(){
		$discounts = hikaInput::get()->get('cid', array(), 'array');

		$badge = hikaInput::get()->getVar('badge');
		if(!isset($badge)) {
			$badge = 'false';
		}
		$this->assignRef('badge',$badge);

		$rows = array();
		if(!empty($discounts)) {
			hikashop_toInteger($discounts);
			$db = JFactory::getDBO();

			$filter = '';
			if($badge == 'false') {
				$filter = ' AND discount_type = '.$db->Quote('coupon');
			}
			$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE discount_id IN ('.implode(',', $discounts).')' . $filter;

			$db->setQuery($query);
			$rows = $db->loadObjectList();
		}
		$this->assignRef('rows', $rows);

		if($badge == 'false') {
			$js = '
window.hikashop.ready( function() {
	var dstTable = window.parent.document.getElementById("coupon_listing");
	var srcTable = document.getElementById("result");
	for(var c = 0, m = srcTable.rows.length; c < m; c++) {
		var rowData = srcTable.rows[c].cloneNode(true);
		dstTable.appendChild(rowData);
	}
	window.parent.hikashop.closeBox();
});
';
		} else {
			$js = '
window.hikashop.ready( function() {
	var field = window.parent.document.getElementById("changeDiscount");
	var result = document.getElementById("result").innerHTML;
	field.innerHTML = result;
	window.parent.hikashop.closeBox();
});
';
		}

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

}
