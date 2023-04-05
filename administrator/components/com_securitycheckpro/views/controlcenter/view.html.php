<?php
/**
 * ControlCenter View para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

class SecuritycheckprosViewControlCenter extends SecuritycheckproView
{
    
    protected $state;

    function __construct()
    {
        parent::__construct();    
    }

    /**
     Securitycheckpros view método 'display'
     **/
    function display($tpl = null)
    {

        JToolBarHelper::title(JText::_('Securitycheck Pro').' | ' .JText::_('COM_SECURITYCHECKPRO_CPANEL_CONTROLCENTER_TEXT'), 'securitycheckpro');

        // Obtenemos el modelo
        $model = $this->getModel();

        //  Parámetros del plugin
        $items= $model->getControlCenterConfig();

        // Información para la barra de navegación
        $logs_pending = $model->LogsPending();
        $trackactions_plugin_exists = $model->PluginStatus(8);
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;
		
		$filemanager_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('filemanager', 'SecuritycheckprosModel');
		$this->log_filename = $filemanager_model->get_log_filename("controlcenter_log", true);
		if ( !empty($this->log_filename) ) {
			JFactory::getApplication()->setUserState('download_controlcenter_log', $this->log_filename);
		} else {
			JFactory::getApplication()->setUserState('download_controlcenter_log', null);
		}
		
		// Chequeamos si existe el fichero de error
		$this->error_file_exists = 0;
		$folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
		if (file_exists($folder_path . "error.php")) {
			$this->error_file_exists = 1;
		}

        // Extraemos los elementos que nos interesan...
        $control_center_enabled= null;
        $secret_key= null;
		$control_center_url = null;


        if (!is_null($items['control_center_enabled'])) {
            $control_center_enabled = $items['control_center_enabled'];    
        }

        if (!is_null($items['secret_key'])) {
            $secret_key = $items['secret_key'];    
        }
		
		if (!is_null($items['control_center_url'])) {
            $control_center_url = $items['control_center_url'];    
        }

        // ... y los ponemos en el template
        $this->control_center_enabled = $control_center_enabled;
        $this->secret_key = $secret_key;
		$this->control_center_url = $control_center_url;


        parent::display($tpl);
    }
}