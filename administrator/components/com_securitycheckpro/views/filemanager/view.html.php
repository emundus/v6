<?php
/**
* FileManager View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

/**
* FileManager View
*
*/
class SecuritycheckprosViewFileManager extends JViewLegacy{

protected $state;
/**
* FileManager view método 'display'
**/
function display($tpl = null)
{

JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_CPANEL_FILE_MANAGER_CONTROL_PANEL_TEXT'), 'securitycheckpro' );
JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');
JToolBarHelper::custom('redireccion_system_info','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_SYSTEM_INFO');

// Obtenemos los datos del modelo
$model = $this->getModel("filemanager");
$last_check = $model->loadStack("filemanager_resume","last_check");
$files_scanned = $model->loadStack("filemanager_resume","files_scanned");
$incorrect_permissions = $model->loadStack("filemanager_resume","files_with_incorrect_permissions");

$task_ended = $model->get_campo_filemanager("estado");

// Si no se está ejecutando ninguna tarea, mostramos la opción 'view files integrity'
if ( strtoupper($task_ended) != 'IN_PROGRESS' ) {
	JToolBarHelper::custom( 'view_file_permissions', 'eye', 'eye', 'COM_SECURITYCHECKPRO_VIEW_FILE_PERMISSIONS' );
}

// Obtenemos si está habilitada la opción para escanear sólo ficheros ejecutables
$params = JComponentHelper::getParams('com_securitycheckpro');
$scan_executables_only = $params->get('scan_executables_only',0);

// Ponemos los datos en el template
$this->assignRef('last_check', $last_check);
$this->assignRef('files_scanned', $files_scanned);
$this->assignRef('incorrect_permissions', $incorrect_permissions);
$this->assignRef('scan_executables_only', $scan_executables_only);

parent::display($tpl);
}
}