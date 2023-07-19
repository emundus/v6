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

class EntryViewEntry extends hikashopView{
	var $ctrl= 'entry';
	var $nameListing = 'HIKASHOP_ENTRIES';
	var $nameForm = 'HIKASHOP_ENTRY';
	var $icon = 'users';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing($doexport=false,$export=''){
		$fieldsClass = hikashop_get('class.field');
		$data=new stdClass;
		$fields = $fieldsClass->getFields('backend_listing',$data,'entry');
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'b.entry_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->filter->filter_status = $app->getUserStateFromRequest( $this->paramBase.'.filter_status', 'filter_status', '', 'string' );
		$database = JFactory::getDBO();
		$filters = array();

		$database->setQuery('SELECT COUNT(*) FROM '.hikashop_table('field').' WHERE field_table = '.$database->Quote('entry').' AND field_published = 1');
		$nb_entries = (int)$database->loadResult();
		if (empty($nb_entries) || $nb_entries == 0)
			$app->enqueueMessage(JText::sprintf('ENTRIES_FIRST'));

		if(!empty($export)){
			hikashop_toInteger($export);
			$filters[]='b.entry_id IN ('.implode(',',$export).')';
		}
		switch($pageInfo->filter->filter_status){
			case '':
			case 'all':
				break;
			default:
				$filters[]='a.order_status = '.$database->Quote($pageInfo->filter->filter_status);
				break;
		}
		$searchMap = array('a.order_id','b.entry_id');
		foreach($fields as $field){
			$searchMap[]='b.'.$field->field_namekey;
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(HikaStringHelper::strtolower(trim($pageInfo->search)),true).'%\'';
			$id = hikashop_decode($pageInfo->search);
			$filter = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
			if(!empty($id)){
				$filter .= " OR a.order_id LIKE '%".hikashop_getEscaped($id,true).'%\'';
			}
			$filters[] =  $filter;
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('entry').' AS b LEFT JOIN '.hikashop_table('order').' AS a ON b.order_id=a.order_id '.$filters.$order;
		if($doexport){
			$database->setQuery('SELECT b.*'.$query);
		}else{
			$database->setQuery('SELECT a.*,b.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		}

		$rows = $database->loadObjectList();

		if(!$doexport){
			if(!empty($pageInfo->search)){
				$rows = hikashop_search($pageInfo->search,$rows,'entry_id');
			}
			$database->setQuery('SELECT COUNT(*)'.$query);
			$pageInfo->elements = new stdClass();
			$pageInfo->elements->total = $database->loadResult();
			$pageInfo->elements->page = count($rows);

			hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

			$config =& hikashop_config();

			$displayNew = hikashop_isAllowed($config->get('acl_entry_manage','all'));
			if (empty($nb_entries) || $nb_entries == 0)
				$displayNew = FALSE;

			$this->toolbar = array(
				array('name' => 'export'),
				array('name'=>'addNew','display'=>$displayNew),
				array('name'=>'editList','display'=>hikashop_isAllowed($config->get('acl_entry_manage','all'))),
				array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_entry_delete','all'))),
				'|',
				array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
				'dashboard'
			);

			$manage = hikashop_isAllowed($config->get('acl_order_manage','all'));
			$this->assignRef('manage',$manage);

			$this->assignRef('fields',$fields);
			$this->assignRef('fieldsClass',$fieldsClass);
			$fieldsClass->handleZoneListing($fields,$rows);
			$category = hikashop_get('type.categorysub');
			$category->type = 'status';
			$this->assignRef('category',$category);

		}
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();

		$this->assignRef('rows',$rows);

	}

	function export(){
		$this->listing(true,$_REQUEST['cid']);
	}

	function form(){
		$app = JFactory::getApplication();
		$entry_id = hikashop_getCID('entry_id');
		$entry = new stdClass();
		if(!empty($entry_id)){
			$class=hikashop_get('class.entry');
			$entry = $class->get($entry_id);
			$task='edit';
		}else{
			$task='new';
		}
		$extraFields=array();
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$address = null;
		$extraFields['entry'] = $fieldsClass->getFields('backend',$address,'entry','user&task=state');

		if (empty($extraFields['entry']))
			$app->enqueueMessage(JText::sprintf('ENTRIES_FIRST'));

		$this->assignRef('extraFields',$extraFields);
		$this->assignRef('entry',$entry);
		$cart=hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		jimport('joomla.html.parameter');
		$params = new HikaParameter('');
		$this->assignRef('params',$params);

		if(hikaInput::get()->getVar('tmpl','')!='component'){
			hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&entry_id='.$entry_id);
			$this->toolbar = array(
				array('name' => 'group', 'buttons' => array( 'apply', 'save')),
				'cancel',
				'|',
				array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
			);
		}
	}
}
