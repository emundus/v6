<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.5.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class CategoryController extends hikashopController {
	var $type = 'category';
	var $pkey = 'category_id';
	var $table = 'category';
	var $groupMap = 'category_parent_id';
	var $orderingMap = 'category_ordering';
	var $groupVal = 0;

	function __construct() {
		parent::__construct();

		$this->display[] = 'selectstatus';
		$this->display[] = 'getTree';
		$this->display[] = 'findList';
		$this->modify_views[] = 'edit_translation';
		$this->modify[] = 'save_translation';
		$this->modify[] = 'rebuild';
		$this->modify_views[] = 'selectparentlisting';
	}

	function edit_translation() {
		hikaInput::get()->set('layout', 'edit_translation');
		return parent::display();
	}

	function save_translation() {
		$category_id = hikashop_getCID('category_id');
		$categoryClass = hikashop_get('class.category');
		$element = $categoryClass->get($category_id);
		if(!empty($element->category_id)) {
			$translationHelper = hikashop_get('helper.translation');
			$translationHelper->getTranslations($element);
			$translationHelper->handleTranslations('category', $element->category_id, $element);
		}
		$document= JFactory::getDocument();
		$document->addScriptDeclaration('window.top.hikashop.closeBox();');
	}

	function rebuild() {
		$categoryClass = hikashop_get('class.category');
		$database = JFactory::getDBO();

		$query = 'SELECT category_left,category_right,category_depth,category_id,category_parent_id FROM #__hikashop_category ORDER BY category_left ASC';
		$database->setQuery($query);
		$root = null;
		$categories = $database->loadObjectList();
		$categoryClass->categories = array();
		foreach($categories as $cat) {
			$categoryClass->categories[$cat->category_parent_id][] = $cat;
			if(empty($cat->category_parent_id)) {
				$root = $cat;
			}
		}

		if(!empty($root)) {
			$query = 'UPDATE `#__hikashop_category` SET category_parent_id = '.(int)$root->category_id.' WHERE category_parent_id = 0 AND category_id != '.(int)$root->category_id.'';
			$database->setQuery($query);
			$database->query();
		}

		$categoryClass->rebuildTree($root, 0, 1);
		$app= JFactory::getApplication();
		$app->enqueueMessage(JText::_('CATEGORY_TREE_REBUILT'));
		$this->listing();
	}

	function orderdown() {
		$this->getGroupVal();
		return parent::orderdown();
	}

	function orderup() {
		$this->getGroupVal();
		return parent::orderup();
	}
	function saveorder() {
		$this->getGroupVal();
		return parent::saveorder();
	}

	function getGroupVal() {
		$app = JFactory::getApplication();
		$this->groupVal = $app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.category.filter_id','filter_id',0,'string');
		if(!is_numeric($this->groupVal)){
			$categoryClass = hikashop_get('class.category');
			$categoryClass->getMainElement($this->groupVal);
		}
	}

	function selectparentlisting() {
		hikaInput::get()->set('layout', 'selectparentlisting');
		return parent::display();
	}

	function selectstatus() {
		hikaInput::get()->set('layout', 'selectstatus');
		return parent::display();
	}


	public function getUploadSetting($upload_key, $caller = '') {

		$category_id = hikaInput::get()->getInt('category_id', 0);

		$upload_value = null;
		$upload_keys = array(
			'category_image' => array(
				'type' => 'image',
				'view' => 'form_image_entry'
			)
		);

		if(empty($upload_keys[$upload_key]))
			return false;
		$upload_value = $upload_keys[$upload_key];

		$shopConfig = hikashop::config();

		$options = array();
		if($upload_value['type'] == 'image')
			$options['upload_dir'] = $shopConfig->get('uploadfolder');
		else
			$options['upload_dir'] = $shopConfig->get('uploadsecurefolder');

		return array(
			'limit' => 1,
			'type' => $upload_value['type'],
			'layout' => 'category',
			'view' => $upload_value['view'],
			'options' => $options,
			'extra' => array(
				'category_id' => $category_id
			)
		);
	}


	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret))
			return;

		$config = hikashop::config();
		$category_id = (int)$uploadConfig['extra']['category_id'];

		$file_type = 'category';
		if(!empty($uploadConfig['extra']['file_type']))
			$file_type = $uploadConfig['extra']['file_type'];

		$sub_folder = '';
		if(!empty($uploadConfig['options']['sub_folder']))
			$sub_folder = str_replace('\\', '/', $uploadConfig['options']['sub_folder']);

		if($caller == 'upload' || $caller == 'addimage') {
			$file = new stdClass();
			$file->file_description = '';
			$file->file_name = $ret->name;
			$file->file_type = $file_type;
			$file->file_ref_id = $category_id;
			$file->file_path = $sub_folder . $ret->name;

			if(strpos($file->file_name, '.') !== false) {
				$file->file_name = substr($file->file_name, 0, strrpos($file->file_name, '.'));
			}

			$fileClass = hikashop::get('class.file');
			$status = $fileClass->save($file, $file_type);

			$ret->file_id = $status;
			$ret->params->file_id = $status;
			return;
		}

		if($caller == 'galleryselect') {
			$file = new stdClass();
			$file->file_type = 'category';
			$file->file_ref_id = $category_id;
			$file->file_path = $sub_folder . $ret->name;

			$fileClass = hikashop::get('class.file');
			$status = $fileClass->save($file);

			$ret->file_id = $status;
			$ret->params->file_id = $status;

			return;
		}
	}


	function getTree() {
		hikashop_nocache();
		hikashop_cleanBuffers();

		$category_id = hikaInput::get()->getInt('category_id', 0);
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');
		$search = hikaInput::get()->getVar('search', null);

		$nameboxType = hikashop_get('type.namebox');
		$options = array(
			'start' => $category_id,
			'displayFormat' => $displayFormat
		);
		$ret = $nameboxType->getValues($search, 'category', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}

	public function findList() {
		$search = hikaInput::get()->getVar('search', '');
		$start = hikaInput::get()->getInt('start', 0);
		$type = hikaInput::get()->getVar('category_type', '');
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');

		$types = array(
			'manufacturer' => 'brand',
			'order_status' => 'order_status'
		);
		if(!isset($types[$type])) {
			echo '[]';
			exit;
		}
		$type = $types[$type];
		$options = array();

		if(!empty($displayFormat))
			$options['displayFormat'] = $displayFormat;
		if($start > 0)
			$options['page'] = $start;

		$nameboxType = hikashop_get('type.namebox');
		$elements = $nameboxType->getValues($search, $type, $options);
		echo json_encode($elements);
		exit;
	}

}
