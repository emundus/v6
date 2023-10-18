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
class uploadViewupload extends hikashopView {
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
		$this->assignRef('uploader', $uploader);
		$field = hikaInput::get()->getCmd('field', '');
		$this->assignRef('field', $field);
	}

	public function galleryimage() {
		hikashop_loadJslib('otree');

		$app = JFactory::getApplication();
		$config = hikashop_config();
		$this->assignRef('config', $config);

		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName().'.gallery';

		$uploadConfig = hikaInput::get()->getVar('uploadConfig', null);
		if(empty($uploadConfig) || !is_array($uploadConfig))
			return false;
		$this->assignRef('uploadConfig', $uploadConfig);
		$uploader = hikaInput::get()->getCmd('uploader', '');
		$this->assignRef('uploader', $uploader);
		$field = hikaInput::get()->getCmd('field', '');
		$this->assignRef('field', $field);

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

	public function image_entry() {
		$this->imageHelper = hikashop_get('helper.image');
		$this->popup = hikashop_get('helper.popup');

		$field = hikaInput::get()->getString('field');
		if(!empty($field)) {
			$parts = explode('-', $field, 2);
			if(count($parts) == 2) {
				$fieldClass = hikashop_get('class.field');
				$field = $fieldClass->getField($parts[1], $parts[0]);
				if($field) {
					$fileClass = hikashop_get('class.file');
					$imagePath = $fileClass->getPath('image', '', $field);
					if($imagePath != $this->imageHelper->uploadFolder) {
						$imageUrlPath =  rtrim(JURI::base(true),'/').'/'.str_replace(JPATH_ROOT, '',$imagePath);
						$this->imageHelper->uploadFolder = $imagePath;
						$this->imageHelper->uploadFolder_url = $imageUrlPath;
					}
				}
			}
		}
	}
}
