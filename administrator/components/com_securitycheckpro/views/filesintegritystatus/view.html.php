<?php
/**
* FilesIntegrityStatus View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

class SecuritycheckprosViewFilesIntegrityStatus extends JViewLegacy{

protected $state;
/**
* Securitycheckpros view método 'display'
**/
function display($tpl = null)
{

JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_FILEINTEGRITY_PANEL_TEXT'), 'securitycheckpro' );
JToolBarHelper::custom('redireccion_file_integrity_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_FILE_INTEGRITY_CONTROL_PANEL');
JToolBarHelper::custom('mark_all_unsafe_files_as_safe','flag-2','flag-2','COM_SECURITYCHECKPRO_FILEINTEGRITY_MARK_ALL_UNSAFE_FILES_AS_SAFE');
JToolBarHelper::custom('mark_checked_files_as_safe','flag','flag','COM_SECURITYCHECKPRO_FILEINTEGRITY_MARK_CHECKED_FILES_AS_SAFE');
JToolBarHelper::custom('export_logs_integrity', 'out-2', 'out-2', 'COM_SECURITYCHECKPRO_EXPORT_INFO_CSV', false);


// Filtro por tipo de extensión
$this->state= $this->get('State');
$fileintegrity_search = $this->state->get('filter.fileintegrity_search');
$filter_fileintegrity_status = $this->state->get('filter.fileintegrity_status');

// Establecemos el valor del filtro 'fileintegrity_status' a cero para que muestre sólo los permisos incorrectos
if ( $filter_fileintegrity_status == ''){
	$this->state->set('filter.fileintegrity_status',0);
}

$model = $this->getModel("filesintegritystatus");
$items = $model->loadStack("integrity","file_integrity");
$files_with_incorrect_integrity = $model->loadStack("fileintegrity_resume","files_with_incorrect_integrity");
$show_all = $this->state->get('showall',0);
$database_error = $model->get_campo_filemanager("estado_integrity");

// Ponemos los datos en el template
$this->assignRef('items', $items);
$this->assignRef('files_with_incorrect_integrity', $files_with_incorrect_integrity);
$this->assignRef('show_all', $show_all);
$this->assignRef('database_error', $database_error);

if ( !empty($items) ) {
	$pagination = $this->get('Pagination');
	$this->assignRef('pagination', $pagination);
}

parent::display($tpl);
}
}