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

class WaitlistViewWaitlist extends hikashopView{
	var $ctrl= 'waitlist';
	var $nameListing = 'HIKA_WAITLIST';
	var $nameForm = 'HIKA_WAITLIST';
	var $icon = 'history';
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
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.waitlist_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();

		$filters = array();
		$searchMap = array('a.waitlist_id','a.email','a.name','a.product_id','b.product_name','b.product_code');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(HikaStringHelper::strtolower(trim($pageInfo->search)),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$join = 'LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_id';
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			if($pageInfo->filter->order->value == 'b.product_name'){
				$order = ' ORDER BY CONCAT_WS(\' \', c.product_name, b.product_name) '.$pageInfo->filter->order->dir;
				$join .= ' LEFT JOIN '.hikashop_table('product').' AS c ON b.product_parent_id=c.product_id';
			}else{
				$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
			}
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('waitlist').' AS a '.$join.$filters.$order;
		$database->setQuery('SELECT a.*,b.* '.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$class = hikashop_get('class.product');
		foreach($rows as $i => $element){
			if($element->product_type=='variant'){
				$database->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic') .' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$element->product_id.' ORDER BY a.ordering');
				$element->characteristics = $database->loadObjectList();
				$parentProduct = $class->get((int)$element->product_parent_id);
				$class->checkVariant($element,$parentProduct);
				$rows[$i] = $element;
			}
		}

		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'waitlist_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_waitlist_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_waitlist_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
	}
	function form(){
		$waitlist_id = hikashop_getCID('waitlist_id');
		$class = hikashop_get('class.waitlist');
		if(!empty($waitlist_id)){
			$element = $class->get($waitlist_id);
			if(@$element->product_type=='variant'){
				$db = JFactory::getDBO();
				$db->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic') .' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$element->product_id.' ORDER BY a.ordering');
				$element->characteristics = $db->loadObjectList();
				$class = hikashop_get('class.product');
				$parentProduct = $class->get((int)$element->product_parent_id);
				$class->checkVariant($element,$parentProduct);
			}
			$task='edit';
		}else{
			$element = new stdClass();
			$task='add';
		}

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&waitlist_id='.$waitlist_id);

		$this->toolbar = array(
			'save-group',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing')
		);

		$this->assignRef('element',$element);
		$popup=hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);
		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);

	}
}
