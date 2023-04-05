<?php
/**
 * FilesIntegrity View para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport('joomla.application.component.view');
jimport('joomla.plugin.helper');

/**
 * FilesIntegrity View
 */
class SecuritycheckprosViewFilesIntegrity extends JViewLegacy
{

    protected $state;
    /**
     FileIntegrity view método 'display'
     **/
    function display($tpl = null)
    {

        JToolBarHelper::title(JText::_('Securitycheck Pro').' | ' .JText::_('COM_SECURITYCHECKPRO_CPANEL_FILE_INTEGRITY_CONTROL_PANEL_TEXT'), 'securitycheckpro');

        // Obtenemos los datos del modelo
        $model = $this->getModel("filemanager");
        $last_check_integrity = $model->loadStack("fileintegrity_resume", "last_check_integrity");
        $files_scanned_integrity = $model->loadStack("fileintegrity_resume", "files_scanned_integrity");
        $files_with_bad_integrity = $model->loadStack("fileintegrity_resume", "files_with_bad_integrity");
        $this->log_filename = $model->get_log_filename("fileintegrity_log", true);

        $task_ended = $model->get_campo_filemanager("estado_integrity");

        // Obtenemos el algoritmo seleccionado para crear el valor hash y si está habilitada la opción para escanear sólo ficheros ejecutables
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $hash_alg = $params->get('file_integrity_hash_alg', 'SHA1');
        $scan_executables_only = $params->get('scan_executables_only', 0);
		$this->file_manager_include_exceptions_in_database = $params->get('file_manager_include_exceptions_in_database', 0);
		// Consultamos dónde han de ir los 'checkboxes'
		$this->checkbox_position = $params->get('checkbox_position','0');

        // Información para la barra de navegación
        $logs_pending = $model->LogsPending();
        $trackactions_plugin_exists = $model->PluginStatus(8);
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;

        // Ponemos los datos en el template
        $this->last_check_integrity = $last_check_integrity;
        $this->files_scanned_integrity = $files_scanned_integrity;
        $this->hash_alg = $hash_alg; 
        $this->files_with_bad_integrity = $files_with_bad_integrity; 
        $this->scan_executables_only = $scan_executables_only;

        // Filesstatus

        // Filtro por tipo de extensión
        $this->state= $model->getState();

        $fileintegrity_search = $this->state->get('filter.fileintegrity_search');
        $filter_fileintegrity_status = $this->state->get('filter.fileintegrity_status');

        // Establecemos el valor del filtro 'fileintegrity_status' a cero para que muestre sólo los permisos incorrectos
        if ($filter_fileintegrity_status == '') {
            $this->state->set('filter.fileintegrity_status', 0);
        }

        $items = $model->loadStack("integrity", "file_integrity");

        $show_all = $this->state->get('showall', 0);
        $database_error = $model->get_campo_filemanager("estado_integrity");

        // Ponemos los datos en el template
        $this->items = $items;
        $this->show_all = $show_all;
        $this->database_error = $database_error;    
        $this->installs = $model->get_installs();
    
        if (!empty($items)) {
            $pagination = $model->getPagination();
            $this->pagination = $pagination;
            JToolBarHelper::custom('mark_all_unsafe_files_as_safe', 'flag-2', 'flag-2', 'COM_SECURITYCHECKPRO_FILEINTEGRITY_MARK_ALL_UNSAFE_FILES_AS_SAFE', false);
            JToolBarHelper::custom('mark_checked_files_as_safe', 'flag', 'flag', 'COM_SECURITYCHECKPRO_FILEINTEGRITY_MARK_CHECKED_FILES_AS_SAFE');
            JToolBarHelper::custom('export_logs_integrity', 'out-2', 'out-2', 'COM_SECURITYCHECKPRO_EXPORT_INFO_CSV', false);
        }

        parent::display($tpl);
    }
}
