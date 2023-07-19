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

class ImportController extends hikashopController
{
	var $type='import';
	var $helperImport;
	var $db;

	public function __construct() {
		parent::__construct();
		$this->db = JFactory::getDBO();
		$this->modify[] = 'import';
		$this->registerDefaultTask('show');
		$this->importHelper = hikashop_get('helper.import');
	}

	public function import() {
		JSession::checkToken('request') || die('Invalid Token');

		$function = hikaInput::get()->getCmd('importfrom');
		$this->importHelper->addTemplate(hikaInput::get()->getInt('template_product',0));

		switch($function){
			case 'file':
				$this->_file();
				break;
			case 'textarea':
				$this->_textarea();
				break;
			case 'folder':
				if(hikashop_level(2)){
					$this->_folder();
				}else{
					$app = JFactory::getApplication();
					$app->enqueueMessage(Text::_('ONLY_FROM_HIKASHOP_BUSINESS'),'error');
				}
				break;
			case 'vm':
				$query = 'SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('virtuemart_products',false),3));
				$this->db->setQuery($query);
				$table = $this->db->loadResult();
				if (empty($table))
				{
					$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('vm_product',false),3));
					$this->db->setQuery($query);
					$table = $this->db->loadResult();
					if (empty($table))
					{
						$app = JFactory::getApplication();
						$app->enqueueMessage('VirtueMart has not been found in the database','error');
					}
					else
					{
						$this->helperImport = hikashop_get('helper.import-vm1', $this);
						$this->_vm();
					}
				}
				else
				{
					$this->helperImport = hikashop_get('helper.import-vm2', $this);
					$this->_vm();
				}
				break;
			case 'mijo':
				$this->helperImport = hikashop_get('helper.import-mijo',$this);
				$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('mijoshop_product',false),3));
				$this->db->setQuery($query);
				$table = $this->db->loadResult();
				if (empty($table))
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage('Mijoshop has not been found in the database','error');
				}
				else
				{
					$this->_mijo();
				}
				break;
			case 'redshop':
				$this->helperImport = hikashop_get('helper.import-reds',$this);
				$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('redshop_product',false),3));
				$this->db->setQuery($query);
				$table = $this->db->loadResult();
				if (empty($table))
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage('Redshop has not been found in the database','error');
				}
				else
				{
					$this->_redshop();
				}
				break;
			case 'openc':
				$this->helperImport = hikashop_get('helper.import-openc',$this);
				$this->_opencart();
				break;
			default:
				$plugin = hikashop_import('hikashop',$function);
				if($plugin)
					$plugin->onImportRun();
				break;
		}
		return $this->show();
	}

	function _textarea(){
		$content = hikaInput::get()->getRaw('textareaentries', '');
		$this->importHelper->overwrite = hikaInput::get()->getInt('textarea_update_products');
		$this->importHelper->createCategories = hikaInput::get()->getInt('textarea_create_categories');
		$this->importHelper->force_published = hikaInput::get()->getInt('textarea_force_publish');
		$this->importHelper->update_product_quantity = hikaInput::get()->getInt('textarea_update_product_quantity');
		$this->importHelper->store_images_locally = hikaInput::get()->getInt('textarea_store_images_locally', 1);
		$this->importHelper->store_files_locally = hikaInput::get()->getInt('textarea_store_files_locally', 1);
		$this->importHelper->keep_other_variants = hikaInput::get()->getInt('keep_other_variants', 1);
		return $this->importHelper->handleContent($content);
	}

	function _folder(){
		$type = hikaInput::get()->getCmd('importfolderfrom');
		$delete = hikaInput::get()->getInt('delete_files_automatically');
		$uploadFolder = hikaInput::get()->getVar($type.'_folder','');
		return $this->importHelper->importFromFolder($type,$delete,$uploadFolder);
	}

	function _file(){
		$importFile =  hikaInput::get()->files->getVar('importfile', array(), 'array');
		if(@$importFile['error'] !== 0) {
			$app = JFactory::getApplication();
			$phpFileUploadErrors = array(
				0 => 'There is no error, the file uploaded with success',
				1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
				2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
				3 => 'The uploaded file was only partially uploaded',
				4 => 'No file was uploaded',
				6 => 'Missing a temporary folder',
				7 => 'Failed to write file to disk.',
				8 => 'A PHP extension stopped the file upload.',
			);
			if(isset($phpFileUploadErrors[$importFile['error']]))
				$app->enqueueMessage($phpFileUploadErrors[$importFile['error']], 'error');
			return false;
		}
		$this->importHelper->overwrite = hikaInput::get()->getInt('file_update_products');
		$this->importHelper->createCategories = hikaInput::get()->getInt('file_create_categories');
		$this->importHelper->force_published = hikaInput::get()->getInt('file_force_publish');
		$this->importHelper->update_product_quantity = hikaInput::get()->getInt('file_update_product_quantity');
		$this->importHelper->store_images_locally = hikaInput::get()->getInt('file_store_images_locally', 1);
		$this->importHelper->store_files_locally = hikaInput::get()->getInt('file_store_files_locally', 1);
		$this->importHelper->keep_other_variants = hikaInput::get()->getInt('keep_other_variants', 1);
		return $this->importHelper->importFromFile($importFile);
	}


	function _vm() {
		return $this->helperImport->importFromVM();
	}

	function _mijo() {
		return $this->helperImport->importFromMijo();
	}

	function _redshop() {
		return $this->helperImport->importFromRedshop();
	}

	function _opencart() {
		return $this->helperImport->importFromOpenc();
	}

}
