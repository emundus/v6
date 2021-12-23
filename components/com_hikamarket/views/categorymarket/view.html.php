<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class categorymarketViewcategorymarket extends HikamarketView {

	protected $ctrl = 'category';
	protected $icon = 'category';

	public function display($tpl = null, $params = array()) {
		$this->params =& $params;

		global $Itemid;
		$this->url_itemid = '';
		if(!empty($Itemid))
			$this->url_itemid = '&Itemid='.$Itemid;

		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		parent::display($tpl);
	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);

		$singleSelection = hikaInput::get()->getInt('single', 0);
		$confirm = hikaInput::get()->getInt('confirm', 1);
		$defaultId = hikaInput::get()->getInt('default', 0);
		$type = hikaInput::get()->getString('type', 'product,vendor,manufacturer');
		$getRoot = true;

		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.listing';

		$cid = hikamarket::getCID();
		if(empty($cid))
			$cid = 1;
		$this->assignRef('cid', $cid);

		$this->loadRef(array(
			'imageHelper' => 'shop.helper.image',
			'toggleClass' => 'helper.toggle',
			'childdisplayType' => 'shop.type.childdisplay',
			'shopCategoryType' => 'type.shop_category',
			'dropdownHelper' => 'shop.helper.dropdown',
		));

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$vendorClass = hikamarket::get('class.vendor');
		$rootCategory = $vendorClass->getRootCategory($vendor);
		if(empty($rootCategory))
			$rootCategory = 1;
		$this->assignRef('rootCategory', $rootCategory);

		$this->manage = hikamarket::acl('category/edit');

		$category_parent_id = 0;

		$query = 'SELECT category_id, category_left, category_right, category_depth, category_parent_id FROM '.hikamarket::table('shop.category').' WHERE category_id IN ('.(int)$cid.','.(int)$rootCategory.')';
		$db->setQuery($query);
		$categories = $db->loadObjectList('category_id');
		if(!isset($categories[$rootCategory]))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ERR_ROOTCATEGORY_NOT_EXIST')));

		if(!isset($categories[$cid]) || (
			$categories[$cid]->category_left < $categories[$rootCategory]->category_left
				||
			$categories[$cid]->category_left > $categories[$rootCategory]->category_right
			)
		 ) {
			$cid = $rootCategory;
		}

		if($cid != $rootCategory)
			$category_parent_id = $categories[$cid]->category_parent_id;

		$query = 'SELECT cats.category_id, cats.category_depth, cats.category_name, cats.category_parent_id '.
			' FROM '.hikamarket::table('shop.category').' AS cats INNER JOIN '.hikamarket::table('shop.category').' AS basecat ON cats.category_left <= basecat.category_left AND cats.category_right >= basecat.category_right '.
			' WHERE basecat.category_id = '.(int)$cid.' AND cats.category_depth >= '.$categories[$rootCategory]->category_depth.' ORDER BY category_depth';
		$db->setQuery($query);
		$breadcrumb = $db->loadObjectList();
		$this->assignRef('breadcrumb', $breadcrumb);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$cfg = array(
			'table' => 'shop.category',
			'main_key' => 'category_id',
			'order_sql_value' => 'category.category_id'
		);

		$default_sort_value = trim($config->get('category_listing_default_sort_value', $cfg['order_sql_value']));
		if(empty($default_sort_value))
			$default_sort_value = $cfg['order_sql_value'];
		$default_sort_dir = trim($config->get('category_listing_default_sort_dir', 'asc'));
		if(empty($default_sort_dir) || !in_array($default_sort_dir, array('asc', 'desc')))
			$default_sort_dir = 'asc';

		$pageInfo = $this->getPageInfo($default_sort_value, $default_sort_dir);
		$filters = array();

		$pageInfo->selectedType = $app->getUserStateFromRequest($this->paramBase.'.filter_type', 'filter_type', 0, 'int');

		$pageInfo->filter->order = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($this->paramBase.'.filter_order', 'filter_order', $cfg['order_sql_value'], 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($this->paramBase.'.filter_order_Dir', 'filter_order_Dir', 'asc', 'word');

		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value))
			$pageInfo->limit->value = 500;
		if(hikaInput::get()->getString('search') != $app->getUserState($this->paramBase.'.search')) {
			$app->setUserState($this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		} else {
			$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart', 0, 'int');
		}

		$pageInfo->search = HikaStringHelper::strtolower($app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string'));
		$this->assignRef('pageInfo', $pageInfo);

		$filters = array();
		$searchMap = array(
			'category.category_name',
			'category.category_id'
		);

		$filters[] = 'category.category_left > '.$categories[$cid]->category_left.' AND category.category_right < '.$categories[$cid]->category_right;
		if(!$pageInfo->selectedType) {
			$filters[] = 'category.category_depth = '.($categories[$cid]->category_depth + 1);
		}

		if(!empty($type)) {
			$type = explode(',', $type);
			if($getRoot && !in_array('root', $type))
				$type[] = 'root';
			$types = array();
			foreach($type as $t) {
				$types[] = $db->Quote($t);
			}
			$filters[] = 'category.category_type IN ('.implode(',',$types).')';
		}

		$fieldsClass = hikamarket::get('shop.class.field');
		$parent_cat_ids = array();
		foreach($breadcrumb as $catElem) {
			$parent_cat_ids[] = $catElem->category_id;
		}
		$field_categories = array('originals' => array($cid), 'parents' => $parent_cat_ids);
		$fields = $fieldsClass->getData('display:vendor_category_listing=1', 'category', false, $field_categories);
		$this->assignRef('fields', $fields);
		$this->assignRef('fieldsClass', $fieldsClass);

		foreach($fields as $fieldName => $field) {
			if($field->field_type == 'customtext')
				continue;
			$searchMap[] = 'category.' . $fieldName;
		}

		if(!empty($pageInfo->search)) {
			$searchVal = '\'%' . $db->escape(HikaStringHelper::strtolower($pageInfo->search), true) . '%\'';
			$filters[] = '(' . implode(' LIKE '.$searchVal.' OR ',$searchMap).' LIKE '.$searchVal . ')';
		}

		$order_by = ' ORDER BY category.category_left ASC';
		if(!empty($pageInfo->filter->order->value)) {
			$order_by = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$query = ' FROM '.hikamarket::table('shop.category').' AS category WHERE ' . implode(' AND ', $filters);
		$db->setQuery('SELECT category.*'.$query.$order_by, $pageInfo->limit->start, $pageInfo->limit->value);
		$elements = $db->loadObjectList('category_id');
		if(!is_numeric($defaultId)) {
			$categoryClass = hikamarket::get('shop.class.category');
			$categoryClass->getMainElement($defaultId);
		}

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($elements);

		if(!empty($elements)) {
			$ids = array();
			foreach($elements as $element) {
				$ids[] = $element->category_id;
			}
			$db->setQuery('SELECT * FROM '.hikamarket::table('shop.file').' WHERE file_type=\'category\' AND file_ref_id IN ('.implode(',',$ids).')');
			$images = $db->loadObjectList();
		} else {
			$images = array();
		}

		foreach($elements as &$element) {
			$element->file_path = '';
			foreach($images as $image) {
				if($image->file_ref_id == $element->category_id) {
					$element->file_path = $image->file_path;
					break;
				}
			}
			if(empty($element->value)){
				$val = str_replace(array(' ',','),'_',strtoupper($element->category_name));
				$element->value = JText::_($val);
				if($val == $element->value) {
					$element->value = $element->category_name;
				}
			}
			$element->category_name = $element->value;

			if($element->category_namekey == 'root') {
				if(empty($defaultId)) {
					$defaultId = $element->category_id;
				}
				$element->category_parent_id = -1;
			}
			unset($element);
		}

		$this->assignRef('categories', $elements);
		$this->assignRef('elements', $elements); // Legacy
		$this->assignRef('singleSelection', $singleSelection);
		$this->assignRef('confirm', $confirm);

		if(!empty($elements)) {
			$category_ids = array_keys($elements);

			$query = 'SELECT category_parent_id, COUNT(*) as `children` FROM '.hikamarket::table('shop.category').' WHERE category_parent_id IN ('.implode(',', $category_ids).') GROUP BY category_parent_id';
			$db->setQuery($query);
			$stats = $db->loadObjectList('category_parent_id');

			foreach($elements as $k => &$category) {
				$category->children = 0;
				if(!isset($stats[$k]))
					continue;
				$category->children = (int)$stats[$k]->children;
			}
			unset($category);

			$query = 'SELECT category_id, COUNT(product_id) as `products` FROM '.hikamarket::table('shop.product_category').' WHERE category_id IN ('.implode(',', $category_ids).') GROUP BY category_id';
			$db->setQuery($query);
			$stats = $db->loadObjectList('category_id');

			foreach($elements as $k => &$category) {
				$category->products = 0;
				if(!isset($stats[$k]))
					continue;
				$category->products = (int)$stats[$k]->products;
			}
			unset($category);
		}

		$fieldsClass->handleZoneListing($fields, $elements);

		$display_edit_root = false;
		if($rootCategory > 1) {
			$categoryClass = hikamarket::get('shop.class.category');
			$cat = $categoryClass->get($rootCategory);
			$display_edit_root = ($cat->category_type == 'vendor' && $cat->category_namekey == 'vendor_' . $vendor->vendor_id);
		}

		$sorting_possible = false;
		if($pageInfo->selectedType == 0) {
			$sorting_possible = true;
		} else if($category_explorer && isset($categories[$cid])) {
		}

		$this->category_explorer = $this->config->get('show_category_explorer', 1);
		$this->category_action_publish = hikamarket::acl('category/edit/published');
		$this->category_action_delete = hikamarket::acl('category/delete');
		$this->category_action_sort = ($vendor->vendor_id == 0 || $vendor->vendor_id == 1) && hikamarket::acl('category/sort') && $this->category_explorer && $sorting_possible;

		$text_asc = JText::_('ASCENDING');
		$text_desc = JText::_('DESCENDING');
		$ordering_values = array(
			'category.category_ordering' => JText::_('SORT_ORDERING'),
			'category.category_id' => JText::_('SORT_ID'),
			'category.category_name' => JText::_('SORT_NAME'),
		);
		$this->ordering_values = array();
		foreach($ordering_values as $k => $v) {
			if($k == 'category.category_ordering' && !$this->category_action_sort)
				continue;
			$this->ordering_values[$k.' asc'] = $v . ' ' .$text_asc;
			$this->ordering_values[$k.' desc'] = $v . ' ' .$text_desc;
		}
		$this->full_ordering = $this->pageInfo->filter->order->value . ' ' . strtolower($this->pageInfo->filter->order->dir);

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('vendor'.$this->url_itemid)
			),
			'up' => array(
				'icon' => 'parent-category',
				'fa' => 'fa-folder-open',
				'name' => JText::_('CATEGORY_PARENT'),
				'url' => hikamarket::completeLink('category&task=listing&cid=' . $category_parent_id . $this->url_itemid),
				'display' => ($category_parent_id > 0)
			),
			'ordering' => array(
				'icon' => 'ordering',
				'fa' => 'fa-sort-amount-down',
				'name' => JText::_('HIKA_SAVE_ORDER'),
				'url' => '#',
				'linkattribs' => 'onclick="return hikamarket.submitform(\'saveorder\',\'adminForm\')"',
				'pos' => 'right',
				'display' => $this->category_action_sort
			),
			'edit_main' => array(
				'icon' => 'category',
				'fa' => 'fa-folder',
				'name' => JText::_('HIKAM_EDIT_MAIN_CATEGORY'),
				'url' => hikamarket::completeLink('category&task=edit&cid='.$rootCategory.$this->url_itemid),
				'pos' => 'right',
				'acl' => hikamarket::acl('category/edit'),
				'display' => $display_edit_root
			),
			'new' => array(
				'icon' => 'new',
				'fa' => 'fa-plus-circle',
				'name' => JText::_('HIKA_NEW'),
				'url' => hikamarket::completeLink('category&task=add&category_parent_id='.$cid.$this->url_itemid),
				'pos' => 'right',
				'acl' => hikamarket::acl('category/add')
			)
		);

		if($pageInfo->limit->value == 500)
			$pageInfo->limit->value = 100;
		$pagination = hikamarket::get('shop.helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$this->assignRef('pagination', $pagination);

		$doOrdering = !$pageInfo->selectedType;
		$this->assignRef('doOrdering', $doOrdering);
		if($doOrdering) {
			$ordering = new stdClass();
			$ordering->ordering = false;
			$ordering->orderUp = 'orderup';
			$ordering->orderDown = 'orderdown';
			$ordering->reverse = false;
			if($pageInfo->filter->order->value == 'category.category_ordering') {
				$ordering->ordering = true;
				if($pageInfo->filter->order->dir == 'desc') {
					$ordering->orderUp = 'orderdown';
					$ordering->orderDown = 'orderup';
					$ordering->reverse = true;
				}
			}
			$this->assignRef('ordering', $ordering);
		}

		$pathway = $app->getPathway();
		$items = $pathway->getPathway();
		if(!count($items)) {
			$pathway->addItem(JText::_('VENDOR_ACCOUNT'), hikamarket::completeLink('vendor'.$this->url_itemid));
		}
		$pathway->addItem(JText::_('HIKA_CATEGORIES'), hikamarket::completeLink('category&task=listing'.$this->url_itemid));
	}

	public function form() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.edit';

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'popup' => 'shop.helper.popup',
			'radioType' => 'shop.type.radio',
		));

		$category_id = hikamarket::getCID('product_id');
		$categoryClass = hikamarket::get('class.category');
		$vendorClass = hikamarket::get('class.vendor');

		$category = new stdClass();
		$category->category_description = '';
		$category->category_id = $category_id;
		$category->category_parent_id = 0;

		$category_parent_id = hikaInput::get()->getInt('category_parent_id', 0);
		if($category_parent_id > 0)
			$category->category_parent_id = $category_parent_id;

		if(!empty($category_id)) {
			$category = $categoryClass->getRaw($category_id, true);

			$translationHelper = hikamarket::get('shop.helper.translation');
			if($translationHelper && $translationHelper->isMulti()) {
				$translationHelper->load('hikashop_category', @$category->category_id, $category);
				$this->assignRef('translationHelper', $translationHelper);
			}
		}

		$fail_category = hikaInput::get()->getVar('fail', null);
		if(!empty($fail_category) && (isset($fail_category->category_id) && ($category->category_id == $fail_category->category_id) || (empty($fail_category->category_id) && empty($category_id)))) {
			foreach($fail_category as $k => $v) {
				$category->$k = $v;
			}
		}

		$editor = hikamarket::get('shop.helper.editor');
		$editor->setEditor($config->get('editor', ''));
		$editor->name = 'category_description';
		$editor->content = $category->category_description;
		$editor->height = 200;
		if($config->get('editor_disable_buttons', 0))
			$editor->options = false;
		$this->assignRef('editor', $editor);

		$this->assignRef('category', $category);

		$categoryType = hikamarket::get('type.shop_category');
		$this->assignRef('categoryType', $categoryType);

		if(hikashop_level(2)) {
			$joomlaAcl = hikamarket::get('type.joomla_acl');
			$this->assignRef('joomlaAcl', $joomlaAcl);
		}

		$imageHelper = hikamarket::get('shop.helper.image');
		$this->assignRef('imageHelper',$imageHelper);
		$uploaderType = hikamarket::get('shop.type.uploader');
		$this->assignRef('uploaderType',$uploaderType);

		$rootCategory = $vendorClass->getRootCategory($vendor);
		$this->assignRef('rootCategory', $rootCategory);

		$isVendorRoot = ($vendor->vendor_id > 1 && !empty($rootCategory) && (int)$rootCategory == (int)@$category->category_id);
		$this->assignRef('isVendorRoot', $isVendorRoot);

		$fieldsClass = hikamarket::get('shop.class.field');
		$fields = $fieldsClass->getFields('display:vendor_category_edit=1', $category, 'category', 'field&task=state');
		foreach($fields as $fieldName => $extraField) {
			if(empty($extraField->field_display) || strpos($extraField->field_display, ';vendor_category_edit=1;') === false) {
				unset($fields[$fieldName]);
			}
		}
		$null = array();
		$fieldsClass->addJS($null, $null, $null);
		$fieldsClass->jsToggle($fields, $category, 0);
		$this->assignRef('fieldsClass', $fieldsClass);
		$this->assignRef('fields', $fields);

		$this->toolbar = array(
			'cancel' => array(
				'url' => hikamarket::completeLink('category&task=listing&cid=' . $category->category_parent_id.$this->url_itemid),
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK')
			),
			'save2new' => array(
				'url' => '#save_and_new',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save2new\',\'hikamarket_categories_form\');"',
				'icon' => 'save',
				'fa' => 'fa-save',
				'name' => JText::_('HIKA_SAVE_NEW'), 'pos' => 'right'
			),
			'sep01' => array(
				'sep' => true, 'pos' => 'right',
				'display' => 1
			),
			'apply' => array(
				'url' => '#apply',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'apply\',\'hikamarket_categories_form\');"',
				'icon' => 'apply',
				'fa' => 'fa-check-circle',
				'name' => JText::_('HIKA_APPLY'), 'pos' => 'right'
			),
			'save' => array(
				'url' => '#save',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save\',\'hikamarket_categories_form\');"',
				'icon' => 'save',
				'fa' => 'fa-save',
				'name' => JText::_('HIKA_SAVE'), 'pos' => 'right'
			)
		);

		$cancel_action = hikaInput::get()->getCmd('cancel_action', '');
		if(!empty($cancel_action)) {
			switch($cancel_action) {
				case 'category':
					if(!empty($product->product_id))
						$this->toolbar['cancel']['url'] = hikamarket::completeLink('shop.category&task=listing&cid='.$category->category_id.$this->url_itemid);
					break;
			}
		}

		$pathway = $app->getPathway();
		$items = $pathway->getPathway();
		if(!count($items)) {
			$pathway->addItem(JText::_('VENDOR_ACCOUNT'), hikamarket::completeLink('vendor'.$this->url_itemid));
		}
		$pathway->addItem(JText::_('HIKA_CATEGORIES'), hikamarket::completeLink('category&task=listing'.$this->url_itemid));

		if(!empty($category->category_name))
			$itemName = $category->category_name;
		else
			$itemName = JText::_('CATEGORY');
		$pathway->addItem($itemName, hikamarket::completeLink('category&task=form&cid='.@$category->category_id.$this->url_itemid));
	}

	public function edit_translation() {
		$language_id = hikaInput::get()->getInt('language_id', 0);
		$this->assignRef('language_id', $language_id);

		$category_id = hikamarket::getCID('category_id');

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$categoryClass = hikamarket::get('class.category');
		$category = $categoryClass->getRaw($category_id);

		$translationHelper = hikamarket::get('shop.helper.translation');
		if($translationHelper && $translationHelper->isMulti()) {
			$translationHelper->load('hikashop_category', @$category->category_id, $category, $language_id);
			$this->assignRef('translationHelper', $translationHelper);
		}

		$editor = hikamarket::get('shop.helper.editor');
		$editor->setEditor($config->get('editor', ''));
		$editor->content = @$category->category_description;
		$editor->height = 300;
		if($config->get('editor_disable_buttons', 0))
			$editor->options = false;
		$this->assignRef('editor', $editor);

		$toggle = hikamarket::get('helper.toggle');
		$this->assignRef('toggle', $toggle);

		$this->assignRef('category', $category);

		$this->toolbar = array(
			array(
				'url' => '#save',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save_translation\',\'hikamarket_translation_form\');"',
				'icon' => 'save',
				'fa' => 'fa-save',
				'name' => JText::_('HIKA_SAVE'), 'pos' => 'right'
			)
		);
	}

	public function image() {
		$file_id = (int)hikamarket::getCID();
		$this->assignRef('cid', $file_id);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$element = null;
		if(!empty($file_id)){
			$fileClass = hikamarket::get('shop.class.file');
			$element = $fileClass->get($file_id);
		}
		$this->assignRef('element', $element);

		$category_id = hikaInput::get()->getInt('pid', 0);
		$this->assignRef('category_id', $category_id);

		$imageHelper = hikamarket::get('shop.helper.image');
		$this->assignRef('imageHelper', $imageHelper);

		$editor = hikamarket::get('shop.helper.editor');
		$editor->setEditor($config->get('editor', ''));
		$editor->name = 'file_description';
		$editor->content = @$element->file_description;
		$editor->height = 200;
		if($config->get('editor_disable_buttons', 0))
			$editor->options = false;
		$this->assignRef('editor', $editor);
	}

	public function galleryimage() {
		hikamarket::loadJslib('otree');

		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.gallery';

		$vendor = hikamarket::loadVendor(true);

		$uploadFolder = ltrim(JPath::clean(html_entity_decode($shopConfig->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$basePath = JPATH_ROOT.DS.$uploadFolder.DS;
		if($vendor->vendor_id > 1) {
			$basePath .= 'vendor' . $vendor->vendor_id . DS;
		}

		$pageInfo = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', 20, 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.'.search', 'search', '', 'string');

		$this->assignRef('pageInfo', $pageInfo);

		jimport('joomla.filesystem.folder');
		if(!JFolder::exists($basePath))
			JFolder::create($basePath);

		$galleryHelper = hikamarket::get('shop.helper.gallery');
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
		$imageHelper = hikamarket::get('shop.helper.image');
		$this->assignRef('imageHelper', $imageHelper);
	}

	public function addimage() {
		$files_id = hikaInput::get()->get('cid', array(), 'array');
		$category_id = hikaInput::get()->getInt('category_id', 0);

		$output = '[]';
		if(!empty($files_id)) {
			hikamarket::toInteger($files_id);
			$query = 'SELECT * FROM '.hikamarket::table('shop.file').' WHERE file_id IN ('.implode(',',$files_id).')';
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$files = $db->loadObjectList();

			$helperImage = hikamarket::get('shop.helper.image');
			$ret = array();
			foreach($files as $file) {

				$params = new stdClass();
				$params->category_id = $category_id;
				$params->file_id = $file->file_id;
				$params->file_path = $file->file_path;
				$params->file_name = $file->file_name;

				$ret[] = hikamarket::getLayout('categorymarket', 'form_image_entry', $params, $js);
			}
			if(!empty($ret)) {
				$output = json_encode($ret);
			}
		}
		$js = 'window.hikashop.ready(function(){window.parent.hikashop.submitBox({images:'.$output.'});});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		return false;
	}
}
