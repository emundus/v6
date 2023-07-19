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
		$this->display[] = 'points_row';
		$this->display[] = 'form';
		$this->modify_views[] = 'edit_translation';
		$this->modify[] = 'save_translation';
		$this->modify[] = 'rebuild';
		$this->modify_views[] = 'selectparentlisting';
		$this->modify_views[] = 'selectimage';
		$this->modify[] = 'addimage';
		$this->modify_views[] = 'batch';
	}
	function form(){
		return $this->edit();
	}
	public function batch(){
		$params = new HikaParameter('');
		$params->set('table', 'category');
		$js = '';
		echo hikashop_getLayout('massaction', 'batch', $params, $js);
	}

	function addimage(){
		if($this->_saveFile())
			hikaInput::get()->set('layout', 'addimage');
		else
			hikaInput::get()->set('layout', 'selectimage');
		return parent::display();
	}
	function points_row(){
		hikaInput::get()->set('layout', 'points_row');
		return parent::display();
	}

	function selectimage(){
		hikaInput::get()->set('layout', 'selectimage');
		return parent::display();
	}

	function _saveFile() {
		$file = new stdClass();
		$file->file_id = hikashop_getCID('file_id');
		$formData = hikaInput::get()->get('data', array(), 'array');
		foreach($formData['file'] as $column => $value){
			hikashop_secureField($column);
			$file->$column = strip_tags($value);
		}
		unset($file->file_path);

		$filemode = 'upload';
		if(!empty($formData['filemode']))
			$filemode = $formData['filemode'];
		if(!empty($file->file_id))
			$filemode = null;

		$fileClass = hikashop_get('class.file');
		hikaInput::get()->set('cid', 0);

		switch($filemode) {
			case 'upload':
				if(empty($file->file_id)) {
					$ids = $fileClass->storeFiles($file->file_type,$file->file_ref_id);
					if(is_array($ids)&&!empty($ids)) {
						$file->file_id = array_shift($ids);
						if(isset($file->file_path))
							unset($file->file_path);
					} else
						return false;
				}
				break;

			case 'path':
			default:
				if(isset($formData['filepath']))
					$file->file_path = trim($formData['filepath']);
				if(isset($formData['file']['file_path']))
					$file->file_path = trim($formData['file']['file_path']);

				$config = hikashop_config();
				$store_locally = $config->get('store_external_files_locally',0);
				if(isset($formData['download']))
					$store_locally = $formData['download'];
				if($store_locally && empty($file->file_id) && (substr($file->file_path, 0, 7) == 'http://' || substr($file->file_path, 0, 8) == 'https://')) {
					$parts = explode('/',$file->file_path);
					$name = array_pop($parts);
					$secure_path = $fileClass->getPath($file->file_type);
					if(!file_exists($secure_path.$name)) {
						$data = @file_get_contents($file->file_path);
						if(empty($data)) {
							$app = JFactory::getApplication();
							$app->enqueueMessage('The file could not be retrieved.');
							return false;
						}
						JFile::write($secure_path . $name, $data);
					} else {
						$size = $this->getSizeFile($file->file_path);
						if($size != filesize($secure_path . $name)) {
							$name = $size . '_' . $name;
							if(!file_exists($secure_path.$name))
								JFile::write($secure_path.$name,file_get_contents($file));
						}
					}

					$file->file_path = $name;
				}
				break;
		}

		if(isset($file->file_path)) {
			$app = JFactory::getApplication();
			if(strpos($file->file_path, '..') !== false) {
				$app->enqueueMessage('Invalid data', 'error');
				return false;
			}

			$firstChar = substr($file->file_path, 0, 1);
			$isVirtual = in_array($firstChar, array('#', '@'));
			$isLink = (substr($file->file_path, 0, 7) == 'http://' || substr($file->file_path, 0, 8) == 'https://');

			if(!$isLink && !$isVirtual) {
				$app = JFactory::getApplication();
				$config = hikashop_config();

				if($firstChar == '/' || preg_match('#:[\/\\\]{1}#', $file->file_path)) {
					$clean_filename = JPath::clean($file->file_path);
					$secure_path = $fileClass->getPath($file->file_type);

					if((JPATH_ROOT != '') && strpos($clean_filename, JPath::clean(JPATH_ROOT)) !== 0 && strpos($clean_filename, JPath::clean($secure_path)) !== 0) {
						$app->enqueueMessage('The file path you entered is an absolute path but it is outside of your upload folder: '.JPath::clean($secure_path), 'error');
						return false;
					}

					if(!file_exists($file->file_path)) {
						$app->enqueueMessage('The file path you entered is an absolute path but it doesn\'t exist.', 'error');
						return false;
					}
				} else {
					$secure_path = $fileClass->getPath($file->file_type);
					$clean_filename = JPath::clean($secure_path . '/' . $file->file_path);
					if(!JFile::exists($clean_filename) && (JPATH_ROOT == '' || !JFile::exists(JPATH_ROOT . DS . $clean_filename))) {
						$app->enqueueMessage('File does not exists', 'error');
						return false;
					}
				}
			}
		}

		if(isset($file->file_ref_id) && empty($file->file_ref_id))
			unset($file->file_ref_id);

		if(isset($file->file_limit)) {
			$limit = (int)$file->file_limit;
			if($limit == 0 && $file->file_limit !== 0 && $file->file_limit != '0')
				$file->file_limit = -1;
			else
				$file->file_limit = $limit;
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$app->triggerEvent('onHikaBeforeFileSave', array(&$file, &$do));

		if(!$do)
			return false;

		if(empty($file->file_path) && empty($file->file_id)) {
			return false;
		}

		$status = $fileClass->save($file);
		if(empty($file->file_id)) {
			$file->file_id = $status;
		}
		hikaInput::get()->set('cid',$file->file_id);

		$app->triggerEvent('onHikaAfterFileSave', array(&$file));

		return true;
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
			$database->execute();
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

		$shopConfig = hikashop_config();

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

		$config = hikashop_config();
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

			$fileClass = hikashop_get('class.file');
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

			$fileClass = hikashop_get('class.file');
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
