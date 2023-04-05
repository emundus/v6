<?php
/**
 * Track ACtions para el Componente Securitycheckpro
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
 * Logs View
 */
class SecuritycheckprosViewTrackActions_Logs extends JViewLegacy
{

    protected $state;

    /**
     Securitycheckpros view método 'display'
     **/
    function display($tpl = null)
    {

        JToolBarHelper::title(JText::_('Securitycheck Pro').' | ' .JText::_('COM_SECURITYCHECKPRO_CPANEL_VIEW_TRACKACTIONS_LOGS_TEXT'), 'securitycheckpro');
        if (JFactory::getUser()->authorise('core.delete', 'com_securitycheckpro')) {
            JToolBarHelper::custom('delete', 'delete', 'delete', 'COM_SECURITYCHECKPRO_DELETE');
            JToolBarHelper::custom('delete_all', 'delete', 'delete', 'COM_SECURITYCHECKPRO_DELETE_ALL', false);
        }
        if (JFactory::getUser()->authorise('core.admin', 'com_userlogs') || JFactory::getUser()->authorise('core.options', 'com_userlogs')) {
            JToolBarHelper::custom('exportLogs', 'out-2', 'out-2', 'COM_SECURITYCHECKPRO_EXPORT_LOGS_CSV', false);
        }

        // Obtenemos los datos del modelo
            
        $this->state= $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $search = $this->state->get('filter.search');
        $user = $this->state->get('filter.user');
        $extension= $this->state->get('filter.extension');
        $ip_address = $this->state->get('filter.ip_address');
        $daterange = $this->state->get('daterange');
            
        $app        = JFactory::getApplication();
        $search = $app->getUserState('filter.search', '');
        $listDirn = $this->state->get('list.direction');
        $listOrder = $this->state->get('list.ordering');

        // Extraemos información necesaria 
        include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/library/model.php';
        $common_model = new SecuritycheckproModel();

        $logs_pending = $common_model->LogsPending();
        $trackactions_plugin_exists = $common_model->PluginStatus(8);
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;    

        //  Parámetros del componente
        $items= $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // ... y los ponemos en el template
        $this->items = $items;

        if (!empty($items)) {
            $this->pagination = $this->get('Pagination');            
        }

        parent::display($tpl);
    }
}
