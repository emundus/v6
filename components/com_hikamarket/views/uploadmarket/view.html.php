<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class uploadmarketViewuploadmarket extends hikamarketView {
	const ctrl = 'upload';
	const name = 'HIKA_UPLOAD';
	const icon = 'upload';

	public function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		parent::display($tpl);
	}

	public function sendfile() {
		$uploadConfig = hikaInput::get()->getVar('uploadConfig', null);
		if(empty($uploadConfig) || !is_array($uploadConfig))
			return false;

		$this->assignRef('uploadConfig', $uploadConfig);
		$uploader = hikaInput::get()->getCmd('uploader', '');
		if(substr($uploader, 0, 11) == 'plg.market.')
			$uploader = substr($uploader, 11);
		$this->assignRef('uploader', $uploader);
		$field = hikaInput::get()->getCmd('field', '');
		$this->assignRef('field', $field);
	}

	public function galleryimage() {
		hikamarket::loadJslib('otree');

		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.gallery';

		$uploadConfig = hikaInput::get()->getVar('uploadConfig', null);
		if(empty($uploadConfig) || !is_array($uploadConfig))
			return false;

		$this->assignRef('uploadConfig', $uploadConfig);
		$uploader = hikaInput::get()->getCmd('uploader', '');
		if(substr($uploader, 0, 11) == 'plg.market.')
			$uploader = substr($uploader, 11);
		$this->assignRef('uploader', $uploader);
		$field = hikaInput::get()->getCmd('field', '');
		$this->assignRef('field', $field);

		$uploadFolder = ltrim(JPath::clean(html_entity_decode($shopConfig->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$basePath = JPATH_ROOT.DS.$uploadFolder.DS;

		if(!empty($uploadConfig['options']['upload_dir']))
			$basePath = rtrim(JPATH_ROOT,DS).DS.str_replace(array('\\','/'), DS, $uploadConfig['options']['upload_dir']);

		$pageInfo = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', 20, 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.'.search', 'search', '', 'string');

		$this->assignRef('pageInfo', $pageInfo);

		jimport('joomla.filesystem.folder');
		if(!JFolder::exists($basePath))
			JFolder::create($basePath);

		$vendorBase = $basePath;
		if(!empty($uploadConfig['options']['sub_folder']))
			$vendorBase .= rtrim(str_replace(array('\\','/'), DS, $uploadConfig['options']['sub_folder']), DS).DS;

		if(!JFolder::exists($vendorBase)) {
			JFolder::create($vendorBase);

			if(!JFolder::exists($vendorBase))
				return false;
		}

		$galleryHelper = hikamarket::get('shop.helper.gallery');
		$galleryHelper->setRoot($vendorBase);
		$this->assignRef('galleryHelper', $galleryHelper);

		$folder = str_replace(array('|', '\/'), array(DS, DS), hikaInput::get()->getString('folder', ''));
		if(!empty($uploadConfig['options']['sub_folder']) && substr($folder, 0, strlen($uploadConfig['options']['sub_folder'])) == $uploadConfig['options']['sub_folder']) {
			$folder = substr($folder, strlen($uploadConfig['options']['sub_folder']));
			if($folder === false)
				$folder = '';
		}

		$destFolder = rtrim($folder, '/\\');
		if(!$galleryHelper->validatePath($destFolder))
			$destFolder = '';
		if(!empty($destFolder)) $destFolder .= '/';
		$treeContent = $galleryHelper->getTreeList(null, $destFolder);
		$this->assignRef('treeContent', $treeContent);

		$galleryHelper->setRoot($basePath);

		$destFolder = '';
		if(!empty($uploadConfig['options']['sub_folder']))
			$destFolder .= rtrim(str_replace(array('\\','/'),DS,$uploadConfig['options']['sub_folder']), DS).DS;
		$destFolder .= rtrim($folder, '/\\');
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

		$dirContent = $galleryHelper->getDirContent($destFolder, $galleryOptions);
		$this->assignRef('dirContent', $dirContent);

		$vendorPath = '';
		if(!empty($uploadConfig['options']['sub_folder']))
			$vendorPath = rtrim(str_replace(array('\\','/'),DS,$uploadConfig['options']['sub_folder']), DS).DS;
		$this->assignRef('vendorPath', $vendorPath);

		jimport('joomla.html.pagination');
		$pagination = new JPagination( $galleryHelper->filecount, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->assignRef('pagination', $pagination);
	}

	public function image_entry() {
		$imageHelper = hikamarket::get('shop.helper.image');
		$this->assignRef('imageHelper', $imageHelper);
		$popup = hikamarket::get('shop.helper.popup');
		$this->assignRef('popup', $popup);
	}
}
