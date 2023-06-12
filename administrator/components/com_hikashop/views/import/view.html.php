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

class ImportViewImport extends hikashopView{
	var $ctrl= 'import';
	var $icon = 'download';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function show(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$config =& hikashop_config();

		hikashop_setTitle(JText::_('IMPORT'),$this->icon,$this->ctrl.'&task=show');
		$importIcon = 'import';
		$this->toolbar = array(
			array('name' => 'custom', 'icon' => $importIcon, 'alt' => JText::_('IMPORT'), 'task' => 'import', 'check' => false),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl),
			'dashboard'
		);

		$importData = array();

		$import = new stdClass();
		$import->text = JText::_('PRODUCTS_FROM_CSV');
		$import->key = 'file';
		$importData[] = $import;

		$textArea = new stdClass();
		$textArea->text = JText::_('PRODUCTS_FROM_TEXTAREA');
		$textArea->key = 'textarea';
		$importData[] = $textArea;

		$folder = new stdClass();
		$folder->text = JText::_('PRODUCTS_FROM_FOLDER');
		$folder->key = 'folder';
		$importData[] = $folder;

		$database = JFactory::getDBO();
		$query = 'SHOW TABLES LIKE '.$database->Quote($database->getPrefix().substr(hikashop_table('virtuemart_products',false),3));
		$database->setQuery($query);
		$table = $database->loadResult();
		if (empty($table))
		{
			$query='SHOW TABLES LIKE '.$database->Quote($database->getPrefix().substr(hikashop_table('vm_product',false),3));
			$database->setQuery($query);
			$table = $database->loadResult();
			if (empty($table))
				$vm_here = false;
			else
			{
				$vm_here = true;
				$version = 1;
				$this->assignRef('vmversion',$version);
			}
		}
		else
		{
			$vm_here = true;
			$version = 2;
			$this->assignRef('vmversion',$version);
		}
		$this->assignRef('vm',$vm_here);

		$vm = new stdClass();
		$vm->text = JText::sprintf('PRODUCTS_FROM_X','Virtuemart');
		$vm->key = 'vm';
		$importData[] = $vm;

		$mijo = new stdClass();
		$mijo->text = JText::sprintf('PRODUCTS_FROM_X','Mijoshop');
		$mijo->key = 'mijo';
		$importData[] = $mijo;

		$query='SHOW TABLES LIKE '.$database->Quote($database->getPrefix().substr(hikashop_table('mijoshop_product',false),3));
		$database->setQuery($query);
		$table = $database->loadResult();
		if (empty($table))
			$mijo_here = false;
		else
			$mijo_here = true;
		$this->assignRef('mijo',$mijo_here);

		$reds = new stdClass();
		$reds->text = JText::sprintf('PRODUCTS_FROM_X','Redshop 1.x or 2.x');
		$reds->key = 'redshop';
		$importData[] = $reds;

		$query='SHOW TABLES LIKE '.$database->Quote($database->getPrefix().substr(hikashop_table('redshop_product',false),3));
		$database->setQuery($query);
		$table = $database->loadResult();
		if (empty($table))
			$reds_here = false;
		else
			$reds_here = true;
		$this->assignRef('reds',$reds_here);

		$openc = new stdClass();
		$openc->text = JText::sprintf('PRODUCTS_FROM_X','Opencart');
		$openc->key = 'openc';
		$importData[] = $openc;

		JPluginHelper::importPlugin( 'hikashop' );
		$app = JFactory::getApplication();
		$app->triggerEvent( 'onDisplayImport', array( & $importData) );

		$this->assignRef('importData',$importData);
		$importValues = array();
		foreach($importData as $data){
			if(!empty($data->key)){
				$importValues[] = JHTML::_('select.option', $data->key,$data->text);
			}
		}
		$this->assignRef('importValues', $importValues);
		$importFolders = array(JHTML::_('select.option', 'images',JText::_('HIKA_IMAGES')),JHTML::_('select.option', 'files',JText::_('HIKA_FILES')),JHTML::_('select.option', 'both',JText::_('FILES').' & '.JText::_('HIKA_IMAGES')));
		$this->assignRef('importFolders', $importFolders);
		$js = '
		var currentoption = \'file\';
		function updateImport(newoption){
			document.getElementById(currentoption).style.display = "none";
			document.getElementById(newoption).style.display = \'block\';
			currentoption = newoption;
		}
		var currentoptionFolder = \'images\';
		function updateImportFolder(newoption){
			document.getElementById(currentoptionFolder).style.display = "none";
			document.getElementById(newoption).style.display = \'block\';
			currentoptionFolder = newoption;
		}';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$this->nameboxType = hikashop_get('type.namebox');
		hikashop_loadJsLib('tooltip');

	}

}
