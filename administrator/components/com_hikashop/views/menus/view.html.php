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
class MenusViewMenus extends hikashopView {
	var $ctrl = 'menus';
	var $nameListing = 'MENUS';
	var $nameForm = 'MENU';
	var $icon = 'menu';

	function display($tpl = null, $params = null) {
		$this->config = hikashop_config();
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function($params);
		parent::display($tpl);
	}

	function _loadCategory(&$element) {
		if(empty($element))
			$element = new stdClass();
		if(!isset($element->hikashop_params))
			$element->hikashop_params = array();

		if(empty($element->hikashop_params['selectparentlisting'])) {
			$db = JFactory::getDBO();

			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'root\' AND category_parent_id=0 LIMIT 1';
			$db->setQuery($query);
			$root = $db->loadResult();

			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'product\' AND category_parent_id='.$root.' LIMIT 1';
			$db->setQuery($query);
			$element->hikashop_params['selectparentlisting'] = $db->loadResult();
		}else{
			$class = hikashop_get('class.category');
			$element->category = $class->get($element->hikashop_params['selectparentlisting']);
		}
	}

	function _assignTypes() {
		$js = "
var old_value_layout = '';
var old_value_content = '';
function switchPanel(name,options,type){
	var len = options.length;
	if(type=='layout'){
		if(name=='table'){
			el4 = document.getElementById('content_select');
			if(el4 && (el4.value=='category' || el4.value=='manufacturer')){
				el5 = document.getElementById('layout_select');
				el5.value = old_value_layout;
				alert('".JText::_('CATEGORY_CONTENT_DOES_NOT_SUPPORT_TABLE_LAYOUT',true)."');
				return;
			}
		}
		el3 = document.getElementById('number_of_columns');
		if(el3){
			if(name=='table'){
				el3.style.display='none';
			}else{
				el3.style.display='';
			}
		}
	}else if(type=='content'){
		if(name=='manufacturer'){
			name = 'category';
		}
		if(name=='category'){
			el4 = document.getElementById('layout_select');
			if(el4 && el4.value=='table'){
				el5 = document.getElementById('content_select');
				el5.value = old_value_content;
				alert('".JText::_('CATEGORY_CONTENT_DOES_NOT_SUPPORT_TABLE_LAYOUT',true)."');
				return;
			}
		}
	}
	for (var i = 0; i < len; i++){
		var el = document.getElementById(type+'_'+options[i]);
		if(el) el.style.display='none';
	}
	if(type=='layout'){
		old_value_layout = name;
	}else{
		old_value_content = name;
	}
	var el2 = document.getElementById(type+'_'+name);
	if(el2) el2.style.display='block';
}
function switchDisplay(value,name,activevalue){
	var el = document.getElementById(name);
	if(el){
		if(value==activevalue){
			el.style.display='';
		}else{
			el.style.display='none';
		}
	}
}
";
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($js);

		$colorType = hikashop_get('type.color');
		$this->assignRef('colorType',$colorType);
		$listType = hikashop_get('type.list');
		$this->assignRef('listType',$listType);
		$contentType = hikashop_get('type.content');
		$this->assignRef('contentType',$contentType);
		$layoutType = hikashop_get('type.layout');
		$this->assignRef('layoutType',$layoutType);
		$orderdirType = hikashop_get('type.orderdir');
		$this->assignRef('orderdirType',$orderdirType);
		$orderType = hikashop_get('type.order');
		$this->assignRef('orderType',$orderType);
		$itemType = hikashop_get('type.item');
		$this->assignRef('itemType',$itemType);
		$childdisplayType = hikashop_get('type.childdisplay');
		$this->assignRef('childdisplayType',$childdisplayType);
		$showpopupoptionType = hikashop_get('type.showpopupoption');
		$this->assignRef('showpopupoptionType',$showpopupoptionType);
		$zoomonhoverType = hikashop_get('type.zoomonhover');
		$this->assignRef('zoomonhoverType',$zoomonhoverType);
		$pricetaxType = hikashop_get('type.pricetax');
		$this->assignRef('pricetaxType',$pricetaxType);
		$priceDisplayType = hikashop_get('type.pricedisplay');
		$this->assignRef('priceDisplayType',$priceDisplayType);
		$discountDisplayType = hikashop_get('type.discount_display');
		$this->assignRef('discountDisplayType',$discountDisplayType);
		$transition_effectType = hikashop_get('type.transition_effect');
		$this->assignRef('transition_effectType',$transition_effectType);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);

		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		if(!empty($this->toolbarJoomlaMenu)){
			array_unshift($this->toolbar,'|');
			array_unshift($this->toolbar,$this->toolbarJoomlaMenu);
		}
	}

	protected function getMenuData($cid) {
		$element =  new stdClass();
		$element->hikashop_params = array();
		if(empty($cid))
			return $element;

		$menusClass = hikashop_get('class.menus');
		$elementFromDB = $menusClass->get($cid);
		if(!empty($elementFromDB->content_type) && !in_array($elementFromDB->content_type, array('product','category'))) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('HIKA_MENU_TYPE_NOT_SUPPORTED'), 'error');
			$url = JRoute::_('index.php?option=com_menus&task=item.edit&id='.$cid, false);
			$app->redirect($url);
		}
		$element = $elementFromDB;
		if(!isset($element->hikashop_params['layout_type']))
			$element->hikashop_params['layout_type'] = 'div';

		return $element;
	}

	protected function getModuleData($id) {
		$element =  new stdClass();
		$element->hikashop_params = array();
		if(empty($id))
			return $element;

		$modulesClass = hikashop_get('class.modules');
		$elementFromDB = $modulesClass->get($id);
		if(!empty($elementFromDB->content_type) && $elementFromDB->content_type != 'product') {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('HIKA_MODULE_TYPE_NOT_SUPPORTED'), 'error');
			$url = JRoute::_('index.php?option=com_modules&task=item.edit&id='.$id, false);
			$app->redirect($url);
		}

		$element = $elementFromDB;

		if(empty($element))
			$element = new stdClass();
		if(!isset($element->hikashop_params))
			$element->hikashop_params = array();
		if(!isset($element->hikashop_params['layout_type']))
			$element->hikashop_params['layout_type'] = 'div';

		return $element;
	}

	function options(&$params) {
		$this->id = $params->get('id');
		$this->name = str_replace('[]', '', $params->get('name'));
		$this->element = $params->get('value');
		$this->type = $params->get('type');
		$this->menu = $params->get('menu');

		$data = array(
			'layoutType' => 'type.layout',
			'orderdirType' => 'type.orderdir',
			'showpopupoptionType' => 'type.showpopupoption',
			'zoomonhoverType' => 'type.zoomonhover',
			'childdisplayType' => 'type.childdisplay',
			'orderType' => 'type.order',
			'listType' => 'type.list',
			'nameboxType' => 'type.namebox',
			'effectType' => 'type.effect',
			'directionType' => 'type.direction',
			'transition_effectType' => 'type.transition_effect',
			'slide_paginationType' => 'type.slide_pagination',
			'positionType' => 'type.position',
			'pricetaxType' => 'type.pricetax',
			'discountDisplayType' => 'type.discount_display',
			'priceDisplayType' => 'type.priceDisplay',
			'colorType' => 'type.color',
			'itemType' => 'type.item',

			'categoryClass' => 'class.category',
		);
		foreach($data as $k => $v) {
			$this->$k = hikashop_get($v);
		}

		$this->mainProductCategory = 'product';
		$this->categoryClass->getMainElement($this->mainProductCategory);

		$cid = hikaInput::get()->getInt('id','');
		if(empty($cid))
			$cid = hikashop_getCID();

		if(empty($this->element)) {
			$menu = $this->getMenuData($cid);
			$this->element = $menu->hikashop_params;
			if(!isset($this->element['category']) && isset($this->element['selectparentlisting']))
				$this->element['category'] = $this->element['selectparentlisting'];

			if(isset($this->element['modules']) && $this->type != $this->menu) {
				$db = JFactory::getDBO();
				$db->setQuery('SELECT template FROM '.hikashop_table('template_styles',false).' WHERE client_id = 0 AND home = 1');
				$template = $db->loadResult();
				if(file_exists(JPATH_ROOT .'/templates/'.$template.'/html/com_hikashop/category/listing.php')){
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('CATEGORY_LISTING_VIEW_OVERRIDE_WARNING'),'warning');
				}

				$moduleIds = explode(',', $this->element['modules']);
				$module = $this->getModuleData(reset($moduleIds));
				$this->element = $module->hikashop_params;
			}
		}

		$this->default_params = $this->config->get('default_params');

		hikashop_loadJslib('tooltip');

		$extra_blocks = array(
			'products' => array(),
			'layouts' => array()
		);
		$element = new stdClass;
		$element->content_type = $this->type;
		$element->hikashop_params =& $this->element;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onHkContentParamsDisplay', array('menu', $this->name, &$element, &$extra_blocks));
		JHtmlHikaselect::$event = false;
		$this->assignRef('extra_blocks', $extra_blocks);
	}

	function form() {
		$cid = hikashop_getCID('id');
		if(empty($cid)) {
			$element = new stdClass();
			$element->hikashop_params = $this->config->get('default_params');
			$task = 'add';
			$control = 'config[menu_0]';

			$inherit_data = array(
				'link_to_product_page' => '1',
				'border_visible' => true,
				'layout_type' => 'inherit',
				'columns' => '',
				'limit' => '',
				'random' => '-1',
				'order_dir' => 'inherit',
				'filter_type' => 2,
				'product_order' => 'inherit',
				'recently_viewed' => '-1',
				'add_to_cart' => '-1',
				'add_to_wishlist' => '-1',
				'link_to_product_page' => '-1',
				'show_vote_product' => '-1',
				'show_price' => '-1',
				'price_with_tax' => 3,
				'product_popup_mode' => 'inherit',
				'zoom_on_hover' => '-1',
				'show_original_price' => '-1',
				'show_discount' => 3,
				'price_display_type' => 'inherit',
				'display_custom_item_fields' => '-1',
				'display_badges' => '-1',
				'category_order' => 'inherit',
				'child_display_type' => 'inherit',
				'child_limit' => '',
				'number_of_products' => '-1',
				'only_if_products' => '-1',
				'div_item_layout_type' => 'inherit',
				'background_color' => '',
				'margin' => '',
				'border_visible' => '-1',
				'rounded_corners' => '-1',
				'enable_switcher' => '-1',
				'text_center' => '-1',
				'ul_class_name' => '',
			);
			$element->hikashop_params = array_merge($element->hikashop_params, $inherit_data);
		} else {
			$modulesClass = hikashop_get('class.menus');
			$element = $modulesClass->get($cid);
			$task = 'edit';
			$control = 'config[menu_'.$cid.']';
			if(strpos($element->link,'view=product') !== false) {
				$element->hikashop_params['content_type'] = 'product';
			} elseif(empty($element->hikashop_params['content_type']) || !in_array($element->hikashop_params['content_type'], array('manufacturer','category'))) {
				$element->hikashop_params['content_type'] = 'category';
			}
			$element->content_type = $element->hikashop_params['content_type'];

			if(!isset($element->hikashop_params['link_to_product_page'])) {
				$element->hikashop_params['link_to_product_page'] = '1';
			}
		}
		if(!isset($element->hikashop_params['layout_type'])) {
			$element->hikashop_params['layout_type'] = 'div';
		}

		hikashop_setTitle(JText::_($this->nameForm), $this->icon, $this->ctrl.'&task='.$task.'&cid[]='.$cid);
		$this->_loadCategory($element);
		if(!empty($cid)) {
			$url = JRoute::_('index.php?option=com_menus&task=item.edit&id='.$element->id);
			$this->toolbarJoomlaMenu = array('name'=>'link','icon'=>'upload','alt'=> JText::_('JOOMLA_MENU_OPTIONS'),'url'=>$url);
		}

		$js = '
function setVisibleLayoutEffect(value) {
	var d = document,
		e1 = d.getElementById("product_effect"),
		e2 = d.getElementById("product_effect_duration");
	if(value == "slider_vertical" || value == "slider_horizontal") {
		e1.style.display = "";
		e2.style.display = "";
	} else if(value == "fade") {
		e1.style.display = "none";
		e2.style.display = "";
	} else {
		e1.style.display = "none";
		e2.style.display = "none";
	}
}
';
		$doc = JFactory::getDocument();
	 	$doc->addScriptDeclaration($js);

		$this->assignRef('element',$element);
		$this->assignRef('control',$control);
		$this->_assignTypes();

		$extra_blocks = array(
			'products' => array(),
			'layouts' => array()
		);
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onHkContentParamsDisplay', array('menu', $control, &$element, &$extra_blocks));
		JHtmlHikaselect::$event = false;
		$this->assignRef('extra_blocks', $extra_blocks);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$database	= JFactory::getDBO();

		$query = 'SELECT extension_id FROM '.hikashop_table('extensions',false).' WHERE type=\'component\' AND element=\''.HIKASHOP_COMPONENT.'\' LIMIT 1';
		$database->setQuery($query);
		$filters = array('(component_id='.$database->loadResult().' OR (component_id=0 AND link LIKE \'%option='.HIKASHOP_COMPONENT.'%\'))','type=\'component\'','client_id=0');
		$searchMap = array('alias','link','title');

		$filters[] = 'published > -2';
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(HikaStringHelper::strtolower(trim($pageInfo->search)),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
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
		$query = ' FROM '.hikashop_table('menu',false).' '.$filters.$order;
		$database->setQuery('SELECT *'.$query);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$unset=array();
		foreach($rows as $k => $row){
			if(strpos($row->link,'view=product')!==false  && strpos($row->link,'layout=show')===false){
				$rows[$k]->hikashop_params = $this->config->get('menu_'.$row->id);
				$rows[$k]->hikashop_params['content_type'] = 'product';
			}elseif(strpos($row->link,'view=category')!==false || strpos($row->link,'view=')===false){
				$rows[$k]->hikashop_params = $this->config->get('menu_'.$row->id);
				$rows[$k]->hikashop_params['content_type'] = 'category';
			}else{
				$unset[]=$k;
				continue;
			}
			if(empty($rows[$k]->hikashop_params)){
				$rows[$k]->hikashop_params = $this->config->get('default_params');
			}

			$rows[$k]->content_type = $rows[$k]->hikashop_params['content_type'];
		}
		foreach($unset as $u) {
			unset($rows[$u]);
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$manage = hikashop_isAllowed($this->config->get('acl_menus_manage','all'));
		$this->assignRef('manage',$manage);

		$this->toolbar = array(
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($this->config->get('acl_menus_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
	}
}
