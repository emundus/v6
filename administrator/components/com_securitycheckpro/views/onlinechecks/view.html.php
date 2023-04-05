<?php
/**
 * Onlinechecks View para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport('joomla.application.component.view');
jimport('joomla.plugin.helper');

class SecuritycheckprosViewOnlineChecks extends JViewLegacy
{

    protected $state;
    /**
     Securitycheckpros view método 'display'
     **/
    function display($tpl = null)
    {

        JToolBarHelper::title(JText::_('Securitycheck Pro').' | ' .JText::_('COM_SECURITYCHECKPRO_ONLINE_CHECK_LOGS'), 'securitycheckpro');
        JToolBarHelper::custom('download_log_file', 'out-2', 'out-2', 'COM_SECURITYCHECKPRO_DOWNLOAD_LOG', false);
        JToolBarHelper::custom('delete_files', 'remove', 'remove', 'COM_SECURITYCHECKPRO_DELETE_FILE');
        JToolBarHelper::custom('view_log', 'eye', 'eye', 'COM_SECURITYCHECKPRO_REPAIR_VIEW_LOG_MESSAGE');


        // Filtro
        $this->state= $this->get('State');
        $managedevices_search = $this->state->get('filter.onlinechecks_search');

        // Obtenemos el modelo
        $model = $this->getModel();

        //  Parámetros del componente
        $items= $model->load();

        // ... y los ponemos en el template
        $this->items = $items;

        // Información para la barra de navegación
        $logs_pending = $model->LogsPending();
        $trackactions_plugin_exists = $model->PluginStatus(8);
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;

        if (!empty($items)) {
            $pagination = $this->get('Pagination');
            $this->pagination = $pagination;    
        }

        parent::display($tpl);
    }
}
