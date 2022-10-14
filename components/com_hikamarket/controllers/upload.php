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

class uploadMarketController extends hikamarketController {

	protected $rights = array(
		'display' => array('upload','image','galleryimage'),
		'add' => array(),
		'edit' => array('addimage','galleryselect'),
		'modify' => array('upload'),
		'delete' => array()
	);

	protected $controller = null;

	public function __construct($config = array())	{
		parent::__construct($config);
		$this->registerDefaultTask('galleryimage');
		$this->config = hikamarket::config();

		$controllerName = hikaInput::get()->getCmd('uploader', '');
		if(substr($controllerName, 0, 11) == 'plg.market.')
			$controllerName = substr($controllerName, 11);

		if(!empty($controllerName)) {
			$this->controller = hikamarket::get('controller.'.$controllerName);
			if(!method_exists($this->controller, 'getUploadSetting'))
				$this->controller = null;
		}
	}

	public function image() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;

		$upload_key = hikaInput::get()->getString('field', '');
		if(empty($this->controller))
			return false;

		$uploadConfig = $this->controller->getUploadSetting($upload_key);
		if($uploadConfig === false)
			return false;

		if(!empty($uploadConfig['type']) && $uploadConfig['type'] != 'image')
			return false;

		hikaInput::get()->set('layout', 'sendfile');
		hikaInput::get()->set('uploadConfig', $uploadConfig);
		return parent::display();
	}

	public function galleryimage() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;

		$upload_key = hikaInput::get()->getString('field', '');
		if(empty($this->controller))
			return false;

		$uploadConfig = $this->controller->getUploadSetting($upload_key);
		if($uploadConfig === false)
			return false;

		if(!empty($uploadConfig['type']) && $uploadConfig['type'] != 'image')
			return false;

		hikaInput::get()->set('layout', 'galleryimage');
		hikaInput::get()->set('uploadConfig', $uploadConfig);
		return parent::display();
	}

	public function addImage() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;

		$upload_key = hikaInput::get()->getString('field', '');
		if(empty($this->controller))
			return false;

		$uploadConfig = $this->controller->getUploadSetting($upload_key);
		if($uploadConfig === false)
			return false;

		if(!empty($uploadConfig['type']) && $uploadConfig['type'] != 'image')
			return false;

		$layout = 'uploadmarket';
		if(!empty($uploadConfig['layout']))
			$layout = $uploadConfig['layout'];
		$viewName = '';
		if(!empty($uploadConfig['view']))
			$viewName = $uploadConfig['view'];
		$type = 'image';
		if(!empty($uploadConfig['type']))
			$type = $uploadConfig['type'];
		if(empty($viewName))
			$viewName = ($type == 'image') ? 'image_entry' : 'file_entry';

		$extra_data = array();
		if(!empty($uploadConfig['extra']))
			$extra_data = $uploadConfig['extra'];

		if(empty($extra_data['field']))
			$extra_data['field'] = $upload_key;

		$options = array();
		if(!empty($uploadConfig['options']))
			$options = $uploadConfig['options'];

		$this->processUploadOption($options, $type);
		if(empty($options) || empty($options['upload_dir']))
			return false;

		$uploadHelper = hikamarket::get('helper.upload');
		$ret = $uploadHelper->processFallback($options);

		$output = '[]';
		if($ret !== false && empty($ret->error)) {
			$helperImage = null;
			if($type == 'image') {
				$helperImage = hikamarket::get('shop.helper.image');
			}

			$out = array();
			foreach($ret as &$r) {
				if(!empty($r->error))
					continue;

				$file = new stdClass();
				$file->file_description = '';
				$file->file_name = $r->name;
				$file->file_type = $type;
				$file->file_path = $options['sub_folder'].$r->name;
				$file->file_url = $options['upload_url'].$options['sub_folder'];

				foreach($extra_data as $k => $v) {
					$file->$k = $v;
				}

				if(strpos($file->file_name, '.') !== false) {
					$file->file_name = substr($file->file_name, 0, strrpos($file->file_name, '.'));
				}

				$r->html = '';
				$js = '';

				if(!empty($options['processing']) && $options['processing'] == 'custom' && method_exists($this->controller, 'processUploadFile'))
					$this->controller->processUploadFile($upload_key, $file, $uploadConfig, 'addimage');

				if($type == 'image') {
					if(!empty($options['processing']) && $options['processing'] == 'resize') {
						$helperImage->resizeImage($file->file_path, 'image', null, null);
					}
					$img = $helperImage->getThumbnail($file->file_path, array(100, 100), array('default' => true));
					$r->thumbnail_url = $img->url;

					$params = new stdClass();
					$params->file_path = $file->file_path;
					$params->file_name = $file->file_name;
					$params->file_url = $file->file_url;
				} else {
					$params = new stdClass();
					$params->file_name = $file->file_name;
					$params->file_path = $file->file_path;
					$params->file_url = $file->file_url;
					$params->file_limit = -1;
					$params->file_size = @filesize($options['upload_dir'] . $options['sub_folder'] . $file->file_name);
				}

				foreach($extra_data as $k => $v) {
					$params->$k = $v;
				}

				$r->params = $params;
				$this->controller->manageUpload($upload_key, $r, $uploadConfig, 'addimage');

				if(empty($r->html))
					$r->html = hikamarket::getLayout($layout, $viewName, $params, $js);

				$out[] = $r->html;

				unset($r->path);
				unset($r->params);
				unset($r);
			}

			if(!empty($out))
				$output = json_encode($out);
			unset($out);
			unset($ret);
		}

		$js = 'window.hikashop.ready(function(){window.parent.hikashop.submitBox({images:'.$output.'});});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		return true;
	}

	public function galleryselect() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;

		$upload_key = hikaInput::get()->getString('field', '');
		if(empty($this->controller))
			return false;

		$uploadConfig = $this->controller->getUploadSetting($upload_key, 'galleryselect');
		if($uploadConfig === false)
			return false;

		if(!empty($uploadConfig['type']) && $uploadConfig['type'] != 'image')
			return false;

		$layout = 'uploadmarket';
		if(!empty($uploadConfig['layout']))
			$layout = $uploadConfig['layout'];
		$viewName = '';
		if(!empty($uploadConfig['view']))
			$viewName = $uploadConfig['view'];
		$type = 'image';
		if(!empty($uploadConfig['type']))
			$type = $uploadConfig['type'];
		if(empty($viewName))
			$viewName = ($type == 'image') ? 'image_entry' : 'file_entry';

		$options = array();
		if(!empty($uploadConfig['options']))
			$options = $uploadConfig['options'];

		$extra_data = array();
		if(!empty($uploadConfig['extra']))
			$extra_data = $uploadConfig['extra'];

		if(empty($extra_data['field']))
			$extra_data['field'] = $upload_key;

		$this->processUploadOption($options, $type);
		if(empty($options) || empty($options['upload_dir']))
			return false;

		$filesData = hikaInput::get()->get('files', array(), 'array');

		$output = '[]';
		if(!empty($filesData)) {
			$helperImage = hikamarket::get('shop.helper.image');
			$ret = array();
			$out = array();
			foreach($filesData as $filename) {
				$r = new stdClass();
				$r->name = $filename;
				$r->url = str_replace('//', '/', $options['upload_url'].$options['sub_folder'].rawurlencode($filename));
				$r->path = str_replace('//', '/', $options['upload_dir'].$options['sub_folder'].$filename);
				$r->type = $type;
				$r->size = filesize($r->path);

				$params = new stdClass();
				$params->file_path = str_replace('//', '/', $options['sub_folder'].$filename);
				$params->file_name = $filename;
				$params->file_url = $r->url;

				foreach($extra_data as $k => $v) {
					$params->$k = $v;
				}

				$r->params = $params;
				$this->controller->manageUpload($upload_key, $r, $uploadConfig, 'galleryselect');

				if(empty($r->html))
					$r->html = hikamarket::getLayout($layout, $viewName, $r->params, $js);

				unset($r->params);
				$ret[] = $r;
				$out[] = $r->html;
				unset($r);
			}
			if(!empty($out))
				$output = json_encode($out);
			unset($out);
			unset($ret);
		}

		$js = 'window.hikashop.ready(function(){window.parent.hikashop.submitBox({images:'.$output.'});});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		return true;
	}

	public function upload() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;

		JSession::checkToken() || die('Invalid Token');

		while(ob_get_level())
			@ob_end_clean();

		$config = hikamarket::config();
		$upload_key = hikaInput::get()->getString('field', '');
		if(empty($this->controller))
			exit;

		$uploadConfig = $this->controller->getUploadSetting($upload_key, 'upload');
		if($uploadConfig === false) {
			header('HTTP/1.1 403 Forbidden');
			exit;
		}

		$layout = 'uploadmarket';
		if(!empty($uploadConfig['layout']))
			$layout = $uploadConfig['layout'];

		$viewName = '';
		if(!empty($uploadConfig['view']))
			$viewName = $uploadConfig['view'];

		$type = 'image';
		if(!empty($uploadConfig['type']))
			$type = $uploadConfig['type'];

		$options = array();
		if(!empty($uploadConfig['options']))
			$options = $uploadConfig['options'];

		$extra_data = array();
		if(!empty($uploadConfig['extra']))
			$extra_data = $uploadConfig['extra'];

		if(empty($extra_data['field']))
			$extra_data['field'] = $upload_key;

		if(empty($viewName))
			$viewName = ($type == 'image') ? 'image_entry' : 'file_entry';

		$this->processUploadOption($options, $type);
		if(empty($options) || empty($options['upload_dir']))
			return false;

		$max_width = (int)$config->get('max_image_size_width', 0);
		$max_height = (int)$config->get('max_image_size_height', 0);

		$uploadHelper = hikamarket::get('helper.upload');
		$ret = $uploadHelper->process($options);

		if($ret === false || !empty($ret->error) || !empty($ret->partial)) {
			if($ret !== false) {
				unset($ret->path);
				unset($ret->params);
			}
			echo json_encode($ret);
			exit;
		}

		$helperImage = null;
		if($type == 'image') {
			$helperImage = hikamarket::get('shop.helper.image');
		}

		$file = new stdClass();
		$file->file_description = '';
		$file->file_name = $ret->name;
		$file->file_type = $type;
		$file->file_path = $options['sub_folder'].$ret->name;
		$file->file_url = $options['upload_url'].$options['sub_folder'];

		foreach($extra_data as $k => $v) {
			$file->$k = $v;
		}

		if(strpos($file->file_name, '.') !== false) {
			$file->file_name = substr($file->file_name, 0, strrpos($file->file_name, '.'));
		}

		$ret->file = $file;
		$ret->html = '';
		$js = '';

		if(!empty($options['processing']) && $options['processing'] == 'custom' && method_exists($this->controller, 'processUploadFile'))
			$this->controller->processUploadFile($upload_key, $file, $uploadConfig, 'upload');

		if($type == 'image') {
			if($max_height > 0 || $max_width > 0) {
			}
			if(!empty($options['processing']) && $options['processing'] == 'resize') {
				$helperImage->resizeImage($file->file_path, 'image', null, null);
			}
			$img = $helperImage->getThumbnail($file->file_path, array(100, 100), array('default' => true));
			$ret->thumbnail_url = $img->url;

			$params = new stdClass();
			$params->file_path = $file->file_path;
			$params->file_name = $file->file_name;
			$params->file_url = $file->file_url;
		} else {
			$params = new stdClass();
			$params->file_name = $file->file_name;
			$params->file_path = $file->file_path;
			$params->file_url = $file->file_url;
			$params->file_limit = -1;
			$params->file_size = @filesize($options['upload_dir'] . $options['sub_folder'] . $file->file_name);
		}

		foreach($extra_data as $k => $v) {
			$params->$k = $v;
		}

		$ret->params = $params;

		$this->controller->manageUpload($upload_key, $ret, $uploadConfig, 'upload');

		if(empty($ret->html))
			$ret->html = hikamarket::getLayout($layout, $viewName, $ret->params, $js);

		unset($ret->path);
		unset($ret->params);

		echo json_encode($ret);
		exit;
	}

	private function processUploadOption(&$options, $type = 'image') {
		$shopConfig = hikamarket::config(false);

		if($type == 'image') {
			if(empty($options['upload_dir']))
				$options['upload_dir'] = $shopConfig->get('uploadfolder');
			if(empty($options['type']))
				$options['type'] = 'image';
		} else {
			if(empty($options['upload_dir']))
				$options['upload_dir'] = $shopConfig->get('uploadsecurefolder');
			if(empty($options['type']))
				$options['type'] = 'file';
		}

		if(empty($options) || empty($options['upload_dir']))
			return false;

		if(empty($options['sub_folder']))
			$options['sub_folder'] = '';
		else
			$options['sub_folder'] = str_replace(DS,'/',rtrim($options['sub_folder'], DS).DS);

		$options['upload_url'] = ltrim(JPath::clean(html_entity_decode($options['upload_dir'])),DS);
		$options['upload_url'] = str_replace(DS,'/',rtrim($options['upload_url'],DS).DS);
		$app = JFactory::getApplication();
		if(hikamarket::isAdmin()) {
			$options['upload_url'] = '../'.$options['upload_url'];
		} else {
			$options['upload_url'] = rtrim(JURI::base(true),'/').'/'.$options['upload_url'];
		}

		$options['upload_dir'] = rtrim(JPath::clean(html_entity_decode($options['upload_dir'])), DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$options['upload_dir']) && (substr($options['upload_dir'], 0, 1) != '/' || !is_dir($options['upload_dir']))) {
			$options['upload_dir'] = JPath::clean(HIKASHOP_ROOT.DS.trim($options['upload_dir'], DS.' ').DS);
		}

		return true;
	}
}
