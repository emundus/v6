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
class CategoryViewCategory extends hikashopView
{
	var $type = '';
	var $ctrl= 'category';
	var $nameListing = 'HIKA_CATEGORIES';
	var $nameForm = 'HIKA_CATEGORIES';
	var $icon = 'folder';
	var $triggerView = true;

	function display($tpl = null, $params = array())
	{
		$this->params =& $params;
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		if(empty($this->displayCompleted))
			parent::display($tpl);
	}

	function points_row() {
		$this->namebox = hikashop_get('type.namebox');
		$this->id = time();
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();

		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.category_ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		if(hikaInput::get()->getVar('search')!=$app->getUserState($this->paramBase.".search") || hikaInput::get()->getVar('filter_id')!=$app->getUserState($this->paramBase.".filter_id")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = HikaStringHelper::strtolower(trim($pageInfo->search));
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );

		$pageInfo->selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type',0,'int');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		$pageInfo->filter->filter_id = $safeHtmlFilter->clean(strip_tags($app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id',0,'string')));

		$database = JFactory::getDBO();

		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$category_image = $config->get('category_image',1);

		$class = hikashop_get('class.category');
		$type='';
		$categories = false;
		$category_id = $pageInfo->filter->filter_id;
		if(is_numeric($pageInfo->filter->filter_id)){
			$cat=$class->get($pageInfo->filter->filter_id);
			if(@$cat->category_type!='root'){
				$type = @$cat->category_type;
			}
		}else{
			$type = $pageInfo->filter->filter_id;
			$class->getMainElement($category_id);
		}
		if($pageInfo->selectedType){
			$childs = $class->getChildren((int)$category_id,true,array(),'',0,0);
			$cat_ids = array();
			foreach($childs as $child){
				$cat_ids[$child->category_id]=$child->category_id;
			}
		}else{
			$cat_ids = array((int)$category_id);
		}
		$parent_cat_ids = array();
		if(!empty($cat_ids)){
			$parents = $class->getParents($cat_ids,true,array(),'',0,0);
			if(!empty($parents)){
				foreach($parents as $parent){
					$parent_cat_ids[]=$parent->category_id;
				}
			}
		}
		$categories=array('originals'=>$cat_ids,'parents'=>$parent_cat_ids);
		$searchMap = array('a.category_name','a.category_description','a.category_id');
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('backend_listing','category',false,$categories);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		foreach($fields as $field){
			$searchMap[]='a.'.$field->field_namekey;
		}
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		if($type=='tax'||$type=='status'){
			$category_image = false;
		}
		$this->assignRef('type',$type);

		$rows = $class->loadAllWithTrans($pageInfo->filter->filter_id,$pageInfo->selectedType,$filters,$order,$pageInfo->limit->start,$pageInfo->limit->value,$category_image);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,array('category_id','file_path'));
		}
		$database->setQuery('SELECT COUNT(*)'.$class->query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		if($category_image){
			$image=hikashop_get('helper.image');
			$this->assignRef('image',$image);
		}

		$this->addHeader();
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$childClass = hikashop_get('type.childdisplay');
		$childDisplay = $childClass->display('filter_type', $pageInfo->selectedType, false);
		$this->assignRef('childDisplay', $childDisplay);
		$breadcrumbClass = hikashop_get('type.breadcrumb');
		$breadCrumb = $breadcrumbClass->display('filter_id',$pageInfo->filter->filter_id,$type);
		$this->assignRef('breadCrumb', $breadCrumb);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$order = new stdClass();
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.category_ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}
		$this->assignRef('order',$order);

		$this->assignRef('category_image',$category_image);
		$this->getPagination();
	}

	function addHeader(){
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_category_manage','all'));
		$this->assignRef('manage',$manage);
		$importIcon = 'upload';
		if(HIKASHOP_J30) {
			$importIcon = 'import';
		}
		$this->toolbar = array(
			array('name' => 'custom', 'icon' => $importIcon, 'alt' => JText::_('REBUILD'), 'task' => 'rebuild', 'check' => false, 'display'=>$manage),
			array('name' => 'popup', 'icon' => 'cogs', 'title' => JText::_('HIKASHOP_ACTIONS'), 'alt' => JText::_('HIKASHOP_ACTIONS'), 'url' => hikashop_completeLink('category&task=batch&tmpl=component'), 'width' => $config->get('actions_popup_width','1024'), 'height' => $config->get('actions_popup_height','520'), 'check' => true, 'display' => $manage),
			array('name' => 'addNew', 'display' => $manage),
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'display' => hikashop_isAllowed($config->get('acl_category_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
		if($this->manage) {
			$massactionClass = hikashop_get('class.massaction');
			$massactionClass->addActionButtons($this->toolbar, 'category');
		}
	}

	function selectstatus(){
		$class = hikashop_get('class.category');
		$rows = $class->loadAllWithTrans('status');
		$selected = hikaInput::get()->getVar('values','','','string');
		$selectedvalues = explode(',',$selected);
		$translated=false;
		if(!empty($rows)){
			foreach($rows as $id => $oneRow){
				if(in_array($oneRow->category_name,$selectedvalues)){
					$rows[$id]->selected = true;
				}
				if(isset($oneRow->translation)){
					$translated = true;
				}
			}
		}
		$this->assignRef('translated',$translated);
		$this->assignRef('rows',$rows);
		$controlName = hikaInput::get()->getString('control','');
		$this->assignRef('controlName',$controlName);
	}

	function selectparentlisting(){
		$this->paramBase .='_parent';
		$control = hikaInput::get()->getCmd('control');
		$id = hikaInput::get()->getCmd('id');
		$name = hikaInput::get()->getCmd('name');
		if(empty($id)){ $id='changeParent'; }
		if(!empty($control)){
			$js ='
			function changeParent(id,name){
				parent.document.getElementById("'.$id.'").innerHTML= id+" "+name;
				parent.document.getElementById("'.$control.'selectparentlisting").value=id;
			}';
		}else{
			$js ='
			function changeParent(id,name){
				parent.document.getElementById("'.$id.'").innerHTML= id+" "+name;
				var el = document.createElement("input");
				el.type = "hidden";
				el.name = "data[category][category_parent_id]";
				el.value = id;
				parent.document.getElementById("'.$id.'").appendChild(el);
			}';
		}
		$this->assignRef('control',$control);
		$this->assignRef('id',$id);

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$this->listing();
	}

	function form(){
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);

		$category_id = hikashop_getCID('category_id');
		$class = hikashop_get('class.category');
		if(!empty($category_id)){
			$element = $class->get($category_id,true, false);
			$task='edit';
		}else{
			$element = hikaInput::get()->getVar('fail');
			if(empty($element)){
				$element = new stdClass();
				$element->category_published=1;
				$app = JFactory::getApplication();
				$filter_id = $app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id','','string');
				if(!is_numeric($filter_id)){
					$class->getMainElement($filter_id);
				}
				$element->category_parent_id=(int)$filter_id;
			}
			$task='add';
		}
		if(!empty($element->category_parent_id)){
			$parentData = $class->get($element->category_parent_id);
			$element->category_parent_name = $parentData->category_name;
			if(empty($element->category_type)&&$parentData->category_type!='root'){
				$element->category_type=$parentData->category_type;
			}
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&category_id='.$category_id);

		$this->toolbar = array(
			'save-group',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$this->_addCustom($element);
		$this->assignRef('element',$element);

		$categoryType = hikashop_get('type.category');
		$this->assignRef('categoryType',$categoryType);
		$mainCategory = !empty($element->category_parent_id)?0:1;
		$this->assignRef('mainCategory',$mainCategory);
		$config =& hikashop_config();
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_category',@$element->category_id,$element);
			$this->assignRef('transHelper',$transHelper);
		}
		$multilang_display = $config->get('multilang_display','tabs');
		if($multilang_display=='popups') $multilang_display = 'tabs';
		$tabs = hikashop_get('helper.tabs');
		$this->assignRef('tabs',$tabs);
		$this->assignRef('config',$config);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('translation',$translation);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'category_description';
		$editor->content = @$element->category_description;
		$this->assignRef('editor',$editor);
		$productDisplayType = hikashop_get('type.productdisplay');
		$this->assignRef('productDisplayType',$productDisplayType);

		$category_image = $config->get('category_image',1);
		if($category_image){
			$image=hikashop_get('helper.image');
			$this->assignRef('image',$image);
			$this->assignRef('imageHelper',$image);
			$uploaderType = hikashop_get('type.uploader');
			$this->assignRef('uploaderType',$uploaderType);
		}
		if(!empty($element->category_type) && ($element->category_type=='tax'||$element->category_type=='status')){
			$category_image = false;
		}
		$this->assignRef('category_image',$category_image);
		$quantityDisplayType = hikashop_get('type.quantitydisplay');
		$this->assignRef('quantityDisplayType',$quantityDisplayType);
		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);

	}

	function edit_translation(){
		$language_id = hikaInput::get()->getInt('language_id',0);
		$category_id = hikashop_getCID('category_id');
		$class = hikashop_get('class.category');
		$element = $class->get($category_id, false, false);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_category',@$element->category_id,$element,$language_id);
			$this->assignRef('transHelper',$transHelper);
		}

		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('backend', $element, 'category', 'field&task=state');
		$this->assignRef('fieldsClass', $fieldsClass);
		$this->assignRef('fields', $fields);

		$this->assignRef('translation',$translation);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'category_description';
		$editor->content = @$element->category_description;
		$editor->height=300;
		$this->assignRef('editor',$editor);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('element',$element);
		$tabs = hikashop_get('helper.tabs');
		$this->assignRef('tabs',$tabs);
	}

	function _addCustom(&$element){
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('',$element,'category','field&task=state');
		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($fields,$element,0);
		$this->assignRef('fieldsClass',$fieldsClass);
		$this->assignRef('fields',$fields);
	}
	public function image() {
		$file_id = (int)hikashop_getCID();
		$this->assignRef('cid', $file_id);

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$element = null;
		if(!empty($file_id)){
			$fileClass = hikashop_get('class.file');
			$element = $fileClass->get($file_id);
		}
		$this->assignRef('element', $element);

		$category_id = hikaInput::get()->getInt('pid', 0);
		$this->assignRef('category_id', $category_id);

		$imageHelper = hikashop_get('helper.image');
		$this->assignRef('imageHelper', $imageHelper);

		$editor = hikashop_get('helper.editor');
		$editor->setEditor($config->get('editor', ''));
		$editor->name = 'file_description';
		$editor->content = @$element->file_description;
		$editor->height = 200;
		if($config->get('editor_disable_buttons', 0))
			$editor->options = false;
		$this->assignRef('editor', $editor);
	}

	public function galleryimage() {
		hikashop_loadJslib('otree');

		$app = JFactory::getApplication();
		$config = hikashop_config();
		$this->assignRef('config', $config);

		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName().'.gallery';


		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$basePath = JPATH_ROOT.DS.$uploadFolder.DS;

		$pageInfo = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', 20, 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.'.search', 'search', '', 'string');

		$this->assignRef('pageInfo', $pageInfo);

		jimport('joomla.filesystem.folder');
		if(!JFolder::exists($basePath))
			JFolder::create($basePath);

		$galleryHelper = hikashop_get('helper.gallery');
		$galleryHelper->setRoot($basePath);
		$this->assignRef('galleryHelper', $galleryHelper);

		$folder = str_replace('|', '/', hikaInput::get()->getString('folder', ''));
		$destFolder = rtrim($folder, '/\\');
		if(!$galleryHelper->validatePath($destFolder))
			$destFolder = '';
		if(!empty($destFolder)) $destFolder .= '/';
		$this->assignRef('destFolder', $destFolder);

		$galleryOptions = array(
			'filter' => '.*' . str_replace(array('.','?','*','$','^'), array('\.','\?','\*','$','\^'), $pageInfo->search) . '.*',
			'offset' => $pageInfo->limit->start,
			'length' => $pageInfo->limit->value
		);
		$this->assignRef('galleryOptions', $galleryOptions);

		$treeContent = $galleryHelper->getTreeList(null, $destFolder);
		$this->assignRef('treeContent', $treeContent);

		$dirContent = $galleryHelper->getDirContent($destFolder, $galleryOptions);
		$this->assignRef('dirContent', $dirContent);

		jimport('joomla.html.pagination');
		$pagination = new JPagination( $galleryHelper->filecount, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->assignRef('pagination', $pagination);
	}

	public function form_image_entry() {
		$imageHelper = hikashop_get('helper.image');
		$this->assignRef('imageHelper', $imageHelper);
		$popupHelper = hikashop_get('helper.popup');
		$this->assignRef('popup', $popupHelper);
	}
	public function addimage(){
		$files_id = hikaInput::get()->get('cid', array(), 'array');
		$category_id = hikaInput::get()->getInt('category_id', 0);

		$output = '[]';
		if(!empty($files_id)) {
			hikashop_toInteger($files_id);
			$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_id IN ('.implode(',',$files_id).')';
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$files = $db->loadObjectList();

			$helperImage = hikashop_get('helper.image');
			$ret = array();
			foreach($files as $file) {

				$params = new stdClass();
				$params->category_id = $category_id;
				$params->file_id = $file->file_id;
				$params->file_path = $file->file_path;
				$params->file_name = $file->file_name;

				$ret[] = hikashop_getLayout('category', 'form_image_entry', $params, $js);
			}
			if(!empty($ret))
				$output = json_encode($ret);
		}
		$js = 'window.hikashop.ready(function(){window.top.hikashop.submitBox({images:'.$output.'});});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		return false;
	}
	public function selectimage(){
		$id = (int)hikashop_getCID( 'file_id');
		if(!empty($id)){
			$class = hikashop_get('class.file');
			$element = $class->get($id);
		}else{
			$element = new stdClass();
		}
		$this->assignRef('cid',$id);
		$this->assignRef('element',$element);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'file_description';
		$editor->content = @$element->file_description;
		$editor->height=200;
		$this->assignRef('editor',$editor);
	}

}
