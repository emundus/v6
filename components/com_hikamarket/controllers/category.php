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
class categoryMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array('show','listing','upload','image','edit_translation','galleryimage','gettree','findlist'),
		'add' => array('add'),
		'edit' => array('edit','addimage','toggle', 'galleryselect'),
		'modify' => array('apply', 'save', 'save_translation', 'saveorder', 'save2new'),
		'delete' => array('delete')
	);

	protected $ordering = array(
		'type' => 'category',
		'pkey' => 'category_id',
		'table' => 'category',
		'groupMap' => 'category_parent_id',
		'orderingMap' => 'category_ordering',
		'groupVal' => 0
	);

	protected $type = 'category';
	protected $config = null;

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		if(!$skip)
			$this->registerDefaultTask('listing');
		$this->config = hikamarket::config();
	}

	public function listing() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		if(!hikamarket::acl('category/listing'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CATEGORY_LISTING')));
		hikaInput::get()->set('layout', 'listing');
		return parent::display();
	}

	public function saveorder(){
		if( !hikamarket::loginVendor() )
			return false;
		if( !$this->config->get('frontend_edition',0) )
			return false;
		if( !hikamarket::acl('category/edit') )
			return hikamarket::deny('category', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CATEGORY_EDIT')));

		$category_id = hikamarket::getCID('category_id');
		if(!hikamarket::isVendorCategory($category_id))
			return hikamarket::deny('category', JText::_('HIKAM_PAGE_DENY'));

		$this->ordering['groupVal'] = $category_id;
		return parent::saveorder();
	}

	public function authorize($task) {
		if($task == 'toggle' || $task == 'delete') {
			$completeTask = hikaInput::get()->getCmd('task');
			if(strrpos($completeTask, '-') !== false) {
				$category_id = (int)substr($completeTask, strrpos($completeTask, '-') + 1);
			} else {
				$category_id = hikaInput::get()->getInt('category_id');
				if(empty($category_id))
					$category_id = hikaInput::get()->getInt('value');
			}

			if(!hikamarket::loginVendor())
				return false;
			if(!$this->config->get('frontend_edition',0))
				return false;
			if(!JSession::checkToken('request'))
				return false;
			if($task == 'toggle' && !hikamarket::acl('category/edit/published'))
				return false;
			if($task == 'delete' && !hikamarket::acl('category/delete'))
				return false;
			if(!hikamarket::isVendorCategory($category_id))
				return false;
			return true;
		}
		return parent::authorize($task);
	}

	public function show() {
		$this->edit();
	}

	public function save2new() {
		$this->store(true);
		return $this->add();
	}

	public function edit() {
		if( !hikamarket::loginVendor() )
			return false;
		if( !$this->config->get('frontend_edition',0) )
			return false;
		if( !hikamarket::acl('category/edit') )
			return hikamarket::deny('category', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CATEGORY_EDIT')));

		$category_id = hikamarket::getCID('category_id');
		if(!hikamarket::isVendorCategory($category_id))
			return hikamarket::deny('category', JText::_('HIKAM_PAGE_DENY'));

		hikaInput::get()->set('layout', 'form');
		return parent::display();
	}

	public function add() {
		if( !hikamarket::loginVendor() )
			return false;
		if( !$this->config->get('frontend_edition',0) )
			return false;
		if( !hikamarket::acl('category/add') )
			return hikamarket::deny('category', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CATEGORY_EDIT')));


		hikaInput::get()->set('layout', 'form');
		return parent::display();
	}

	public function edit_translation() {
		if( !hikamarket::loginVendor() )
			return false;
		if( !$this->config->get('frontend_edition',0) )
			return false;
		if( !hikamarket::acl('category/edit/translations') )
			return hikamarket::deny('category', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CATEGORY_EDIT')));

		$category_id = hikamarket::getCID('category_id');
		if(!hikamarket::isVendorProduct($category_id))
			return hikamarket::deny('category', JText::_('HIKAM_PAGE_DENY'));

		hikaInput::get()->set('layout', 'edit_translation');
		return parent::display();
	}

	public function save_translation() {
		if( !hikamarket::loginVendor() )
			return false;
		if( !$this->config->get('frontend_edition',0) )
			return false;
		if( !hikamarket::acl('category/edit/translations') )
			return hikamarket::deny('category', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CATEGORY_EDIT')));

		$category_id = hikamarket::getCID('category_id');
		if(!hikamarket::isVendorCategory($category_id))
			return hikamarket::deny('category', JText::_('HIKAM_PAGE_DENY'));

		$category = null;
		$categoryClass = hikamarket::get('class.category');
		$category = $categoryClass->getRaw($category_id);
		if(!empty($category->category_id)) {
			$translationHelper = hikamarket::get('shop.helper.translation');
			$translationHelper->getTranslations($product);
			$translationHelper->handleTranslations('category', $category->category_id, $category);
		}
		$js = 'window.hikashop.ready(function(){window.parent.hikamarket.submitBox();});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

	public function delete() {
		if( !$this->config->get('frontend_edition', 0)) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;

		$completeTask = hikaInput::get()->getCmd('task');
		if(strrpos($completeTask, '-') !== false) {
			$category_id = (int)substr($completeTask, strrpos($completeTask, '-') + 1);
		} else {
			$category_id = hikaInput::get()->getInt('category_id');
		}
		if(empty($category_id))
			return false;

		if(!hikamarket::acl('category/delete') || !hikamarket::isVendorCategory($category_id))
			return false;

		$categoryClass = hikamarket::get('shop.class.category');
		$ret = $categoryClass->delete($category_id);

		$app = JFactory::getApplication();
		if(!empty($ret) && $ret > 0) {
			$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS', 1), 'message');
		}
		$app->redirect(hikamarket::completeLink('category&task=listing', false, true));
	}

	public function store($new = false) {
		if(!hikamarket::loginVendor())
			return false;
		if( !$this->config->get('frontend_edition',0) )
			return false;
		if( !hikamarket::acl('category/edit') )
			return hikamarket::deny('category', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_CATEGORY_EDIT')));

		$categoryClass = hikamarket::get('class.category');
		if( $categoryClass === null )
			return false;
		$status = $categoryClass->frontSaveForm();
		if($status) {
			hikaInput::get()->set('cid', $status);
			if($new)
				hikaInput::get()->set('cid', 0);
			hikaInput::get()->set('fail', null);
		}
		return $status;
	}

	public function getUploadSetting($upload_key, $caller = '') {
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('category/edit') )
			return false;

		$category_id = hikaInput::get()->getInt('category_id', 0);
		if(empty($upload_key) || (!empty($category_id) && !hikamarket::isVendorCategory($category_id)))
			return false;

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

		$shopConfig = hikamarket::config(false);
		$vendor_id = hikamarket::loadVendor(false, false);

		$options = array();
		if($upload_value['type'] == 'image')
			$options['upload_dir'] = $shopConfig->get('uploadfolder');
		else
			$options['upload_dir'] = $shopConfig->get('uploadsecurefolder');

		if($vendor_id > 1)
			$options['sub_folder'] = 'vendor'.$vendor_id.DS;

		return array(
			'limit' => 1,
			'type' => $upload_value['type'],
			'layout' => 'categorymarket',
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

		$config = hikamarket::config();
		$vendor_id = hikamarket::loadVendor(false, false);
		$category_id = (int)$uploadConfig['extra']['category_id'];
		if(!empty($category_id) && !hikamarket::isVendorCategory($category_id))
			return;

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

			$fileClass = hikamarket::get('shop.class.file');
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

			$fileClass = hikamarket::get('shop.class.file');
			$status = $fileClass->save($file);

			$ret->file_id = $status;
			$ret->params->file_id = $status;

			return;
		}
	}

	public function getTree() {
		hikamarket::headerNoCache();
		hikamarket::cleanBuffers();

		if(!hikamarket::loginVendor() || !$this->config->get('frontend_edition',0)) {
			echo '[]';
			exit;
		}

		$category_id = hikaInput::get()->getInt('category_id', 0);
		$displayFormat = hikaInput::get()->getString('displayFormat', '');
		$search = hikaInput::get()->getString('search', null);

		$nameboxType = hikamarket::get('type.namebox');
		$options = array(
			'start' => $category_id,
			'displayFormat' => $displayFormat
		);

		if(!hikamarket::isVendorCategory($category_id, null, true)) {
			echo '[]';
			exit;
		}

		$ret = $nameboxType->getValues($search, 'category', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}

	public function findList() {
		hikamarket::headerNoCache();
		hikamarket::cleanBuffers();

		if(!hikamarket::loginVendor() || !$this->config->get('frontend_edition',0)) {
			echo '[]';
			exit;
		}

		$search = hikaInput::get()->getString('search', '');
		$start = hikaInput::get()->getInt('start', 0);
		$type = hikaInput::get()->getString('category_type', '');
		$displayFormat = hikaInput::get()->getString('displayFormat', '');

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

		$nameboxType = hikamarket::get('type.namebox');
		$elements = $nameboxType->getValues($search, $type, $options);
		echo json_encode($elements);
		exit;
	}
}
