<?php
/**
* FilesIntegrity View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

/**
* FilesIntegrity View
*
*/
class SecuritycheckprosViewFilesIntegrity extends JViewLegacy{

protected $state;
/**
* FileIntegrity view método 'display'
**/
function display($tpl = null)
{

JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_CPANEL_FILE_INTEGRITY_CONTROL_PANEL_TEXT'), 'securitycheckpro' );
JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');
JToolBarHelper::custom('redireccion_system_info','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_SYSTEM_INFO');

// Obtenemos los datos del modelo
$model = $this->getModel("filemanager");
$last_check_integrity = $model->loadStack("fileintegrity_resume","last_check_integrity");
$files_scanned_integrity = $model->loadStack("fileintegrity_resume","files_scanned_integrity");
$files_with_bad_integrity = $model->loadStack("fileintegrity_resume","files_with_bad_integrity");

$task_ended = $model->get_campo_filemanager("estado_integrity");

// Si no se está ejecutando ninguna tarea, mostramos la opción 'view files integrity'
if ( strtoupper($task_ended) != 'IN_PROGRESS' ) {
	JToolBarHelper::custom( 'view_files_integrity', 'locked', 'locked', 'COM_SECURITYCHECKPRO_VIEW_FILES_INTEGRITY' ); 
}

// Obtenemos el algoritmo seleccionado para crear el valor hash y si está habilitada la opción para escanear sólo ficheros ejecutables
$params = JComponentHelper::getParams('com_securitycheckpro');
$hash_alg = $params->get('file_integrity_hash_alg','SHA1');
$scan_executables_only = $params->get('scan_executables_only',0);

// Ponemos los datos en el template
$this->assignRef('last_check_integrity', $last_check_integrity);
$this->assignRef('files_scanned_integrity', $files_scanned_integrity);
$this->assignRef('hash_alg', $hash_alg); 
$this->assignRef('files_with_bad_integrity', $files_with_bad_integrity); 
$this->assignRef('scan_executables_only', $scan_executables_only);

parent::display($tpl);
}
}