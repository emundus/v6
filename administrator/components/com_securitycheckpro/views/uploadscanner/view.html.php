<?php
/**
* FirewallLogs View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

// Load plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_system_securitycheckpro');


class SecuritycheckprosViewUploadScanner extends SecuritycheckproView {

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('PLG_SECURITYCHECKPRO_LOGS_LABEL'), 'securitycheckpro' );	
}

function display($tpl = null) {

// Obtenemos el modelo
$model = $this->getModel();

//  Parámetros del plugin
$items= $model->getConfig();

// Extraemos los elementos que nos interesan...
$upload_scanner_enabled = 0;
$check_multiple_extensions = 0;
$extensions_blacklist  = "php,js,exe,xml";
$delete_files = 0;
$actions_upload_scanner = 0;

$upload_scanner_enabled = $items['upload_scanner_enabled'];	
$check_multiple_extensions = $items['check_multiple_extensions'];	
$extensions_blacklist = $items['extensions_blacklist'];
$delete_files = $items['delete_files'];
$actions_upload_scanner = $items['actions_upload_scanner'];

// ... y los ponemos en el template
$this->assignRef('upload_scanner_enabled',$upload_scanner_enabled);
$this->assignRef('check_multiple_extensions',$check_multiple_extensions);
$this->assignRef('extensions_blacklist',$extensions_blacklist);
$this->assignRef('delete_files',$delete_files);
$this->assignRef('actions_upload_scanner',$actions_upload_scanner);

parent::display($tpl);
}
}