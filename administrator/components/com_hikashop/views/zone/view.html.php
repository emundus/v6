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
class ZoneViewZone extends hikashopView
{
	var $type = '';
	var $ctrl= 'zone';
	var $nameListing = 'ZONES';
	var $nameForm = 'ZONES';
	var $icon = 'map-marker-alt';

	function display($tpl = null)
	{
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
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.zone_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );

		$pageInfo->filter->type = $selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(hikaInput::get()->getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = HikaStringHelper::strtolower(trim($pageInfo->search));
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$this->searchOptions = array('type'=> '', 'country' => 0);
		$this->openfeatures_class = "hidden-features";
		$database	= JFactory::getDBO();
		$searchMap = array('a.zone_code_3','a.zone_code_2','a.zone_name_english','a.zone_name','a.zone_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}

		$query = ' FROM '.hikashop_table('zone').' AS a';
		if(!empty($selectedType)){
			$filters[] = 'a.zone_type = '.$database->Quote($selectedType);
			if($selectedType=='state'){
				$selectedCountry = $app->getUserStateFromRequest( $this->paramBase.".filter_country",'filter_country',0,'int');
				if($selectedCountry){
					$query = ' FROM '.hikashop_table('zone').' AS c LEFT JOIN '.hikashop_table('zone_link') .' AS b ON c.zone_namekey=b.zone_parent_namekey LEFT JOIN '.hikashop_table('zone').' AS a ON b.zone_child_namekey=a.zone_namekey';
					$filters[] = 'c.zone_id = '.$database->Quote($selectedCountry);
				}
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
			$rows = hikashop_search($pageInfo->search,$rows,'zone_id');
		}
		$database->setQuery('SELECT count(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);


		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_zone_manage','all'));
		$this->assignRef('manage',$manage);

		$this->toolbar = array(
			array('name'=>'publishList','display'=>$manage),
			array('name'=>'unpublishList','display'=>$manage),
			'|',
			array('name' => 'custom', 'icon'=>'copy','alt'=>JText::_('HIKA_COPY'), 'task' => 'copy','display'=>$manage),
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_zone_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$filters = new stdClass();
		$zoneType = hikashop_get('type.zone');
		$filters->type = $zoneType->display('filter_type',$selectedType);
		if($selectedType=='state'){
			$countryType = hikashop_get('type.country');
			$filters->country = $countryType->display('filter_country',$selectedCountry);
		}else{
			$filters->country = '';
		}
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('filters',$filters);
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
	}

	function selectchildlisting(){
		$this->paramBase .= '_child';
		$this->listing();
		$control=hikaInput::get()->getWord('type');
		$this->assignRef('type',$control);
		$subcontrol=hikaInput::get()->getVar('subtype');
		$this->assignRef('subtype',$subcontrol);
		$map=hikaInput::get()->getVar('map');
		$this->assignRef('map',$map);
		$column=hikaInput::get()->getVar('column');
		$this->assignRef('column',$column);
	}

	function form(){
		$zone_id = hikashop_getCID('zone_id',false);
		if(!empty($zone_id)){
			$class = hikashop_get('class.zone');
			$element = $class->get($zone_id);
			$task='edit';
		}else{
			$element = hikaInput::get()->getVar('fail');
			if(empty($element)){
				$element = new stdClass();
				$app = JFactory::getApplication();
				$element->zone_type = $app->getUserState( $this->paramBase.".filter_type");
			}
			$task='add';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&zone_id='.$zone_id);

		$this->toolbar = array(
			'save-group',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$zoneType = hikashop_get('type.zone');
		$this->assignRef('element',$element);
		$this->assignRef('type',$zoneType);

		$control=hikaInput::get()->getWord('type');
		$this->assignRef('control',$control);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);

		$this->_childZones($zone_id,@$element->zone_namekey);

	}

	function newchildform(){
		$element = new stdClass();
		$app = JFactory::getApplication();
		$this->paramBase .= '_child';
		$main_id = hikaInput::get()->getInt('main_id');
		if(!empty($main_id)){
			$zoneClass = hikashop_get('class.zone');
			$parent = $zoneClass->get($main_id);
			if($parent->zone_type=='country'){
				$element->zone_type='state';
			}else{
				$element->zone_type='country';
			}
		}else{
			$element->zone_type = $app->getUserState( $this->paramBase.".filter_type");
		}
		$element->zone_published = 1;
		$zoneType = hikashop_get('type.zone');
		$this->assignRef('element',$element);
		$this->assignRef('type',$zoneType);
	}

	function savechild(){
		$database = JFactory::getDBO();
		$id = hikaInput::get()->getInt( 'cid' );
		if(!empty($id)){
			$query = 'SELECT a.* FROM '.hikashop_table('zone').' AS a WHERE a.zone_id='.$id;
			$database->setQuery($query);
			$rows =  $database->loadObjectList();
		}else{
			$rows = array();
		}
		$this->assignRef('list',$rows);
		$main_namekey = hikaInput::get()->getCmd('main_namekey');
		$this->assignRef('main_namekey',$main_namekey);
		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);
		$document= JFactory::getDocument();
		$js = "window.hikashop.ready( function() {
				var dstTable = window.top.document.getElementById('list_0_data');
				var srcTable = document.getElementById('result');
				for (var c = 0,m=srcTable.rows.length;c<m;c++){
					var rowData = srcTable.rows[c].cloneNode(true);
					dstTable.appendChild(rowData);
				}
				window.top.hikashop.closeBox();
		});";
		$document->addScriptDeclaration($js);
		$this->setLayout('newchild');
	}

	function newchild(){
		$document = JFactory::getDocument();
		$database = JFactory::getDBO();
		$childNamekeys = hikaInput::get()->get('cid', array(), 'array');
		if(!empty($childNamekeys)){
			$query = 'SELECT a.* FROM '.hikashop_table('zone').' AS a WHERE a.zone_namekey  IN (';
			foreach($childNamekeys as $namekey){
				$query.=$database->Quote($namekey).',';
			}
			$query=rtrim($query,',').');';
			$database->setQuery($query);
			$rows =  $database->loadObjectList();
		}else{
			$rows = array();
		}
		$this->assignRef('list',$rows);
		$main_namekey = hikaInput::get()->getCmd('main_namekey');
		$this->assignRef('main_namekey',$main_namekey);
		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);
		$js = "window.hikashop.ready( function() {
				var dstTable = window.top.document.getElementById('list_0_data');
				var srcTable = document.getElementById('result');
				for (var c = 0,m=srcTable.rows.length;c<m;c++){
					var rowData = srcTable.rows[c].cloneNode(true);
					dstTable.appendChild(rowData);
				}
				window.top.hikashop.closeBox();
		});";
		$document->addScriptDeclaration($js);
	}

	function addchild(){
		$document= JFactory::getDocument();
		$database = JFactory::getDBO();
		$zone_id = hikashop_getCID( 'zone_id');
		if(!empty($zone_id)){
			$query = 'SELECT a.* FROM '.hikashop_table('zone').' AS a WHERE a.zone_id  ='.$zone_id;
			$database->setQuery($query);
			$element =  $database->loadObject();
		}else{
			$element = new stdClass();
		}
		if(empty($element->zone_name_english)){
			if(!empty($element->zone_name)){
				$element->zone_name_english = $element->zone_name;
			}else{
				$element->zone_name_english=JText::_('ZONE_NOT_FOUND');
			}
		}
		$subtype=hikaInput::get()->getVar('subtype');
		if(empty($subtype)){
			$subtype='zone_id';
		}

		$js = "window.hikashop.ready( function() {
					window.top.document.getElementById('".$subtype."').innerHTML = document.getElementById('result').innerHTML;
					window.top.hikashop.closeBox();
			});";
		$document->addScriptDeclaration($js);
		$this->assignRef('element',$element);
	}

	function _childZones($zone_id,$zone_namekey){
		$toggleClass = hikashop_get('helper.toggle');
		if(!empty($zone_id)){
			$zoneClass = hikashop_get('class.zone');
			$rows =  $zoneClass->getChildren($zone_namekey);
			$this->assignRef('list',$rows);
			$this->assignRef('main_id',$zone_id);
			$this->assignRef('main_namekey',$zone_namekey);
			$this->assignRef('toggleClass',$toggleClass);
		}
		$toggleClass->addDeleteJS();
	}

}
