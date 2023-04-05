<?php
/**
 * RulesLogs View para el Componente Securitycheckpro
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
class SecuritycheckprosViewRulesLogs extends JViewLegacy
{

    protected $state;

    /**
     Securitycheckpros view método 'display'
     **/
    function display($tpl = null)
    {

        JToolBarHelper::title(JText::_('Securitycheck Pro').' | ' .JText::_('COM_SECURITYCHECKPRO_RULES_VIEW_LOGS'), 'securitycheckpro');

        // Obtenemos los datos del modelo        
        $this->state= $this->get('State');
        $search = $this->state->get('filter.rules_search');
    
        $model = $this->getModel("ruleslogs");
        $log_details = $model->load_rules_logs();
    
        // Información para la barra de navegación
        include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/library/model.php';
        $common_model = new SecuritycheckproModel();

        $logs_pending = $common_model->LogsPending();
        $trackactions_plugin_exists = $common_model->PluginStatus(8);
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;
    
        // Ponemos los datos y la paginación en el template
        $this->log_details = $log_details;
        
        if (!empty($log_details)) {
            $this->pagination = $this->get('Pagination');    
        }

        parent::display($tpl);
    }
}
