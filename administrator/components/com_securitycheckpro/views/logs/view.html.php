<?php
/**
 * Logs View para el Componente Securitycheckpro
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
class SecuritycheckprosViewlogs extends JViewLegacy
{

    protected $state;

    /**
     Securitycheckpros view método 'display'
     **/
    function display($tpl = null)
    {

        JToolBarHelper::title(JText::_('Securitycheck Pro').' | ' .JText::_('COM_SECURITYCHECKPRO_CPANEL_VIEW_FIREWALL_LOGS_TEXT'), 'securitycheckpro');
        JToolBarHelper::custom('csv_export', 'out-2', 'out-2', 'COM_SECURITYCHECKPRO_EXPORT_LOGS_CSV', false);
        JToolBarHelper::custom('mark_read', 'checkbox', 'checkbox', 'COM_SECURITYCHECKPRO_LOG_READ_CHANGE');
        JToolBarHelper::custom('mark_unread', 'checkbox-unchecked', 'checkbox-unchecked', 'COM_SECURITYCHECKPRO_LOG_NO_READ_CHANGE');
        JToolBarHelper::custom('delete', 'delete', 'delete', 'COM_SECURITYCHECKPRO_DELETE');
        JToolBarHelper::custom('delete_all', 'delete', 'delete', 'COM_SECURITYCHECKPRO_DELETE_ALL', false);
        JToolBarHelper::custom('add_to_blacklist', 'plus_blacklist', 'plus', 'COM_SECURITYCHECKPRO_ADD_TO_BLACKLIST');
        JToolBarHelper::custom('add_to_whitelist', 'plus', 'plus', 'COM_SECURITYCHECKPRO_ADD_TO_WHITELIST');
		JToolBarHelper::custom('add_exception', 'plus', 'plus', 'COM_SECURITYCHECKPRO_ADD_EXCEPTION');

        // Obtenemos los datos del modelo
            
        $this->state= $this->get('State');
        $search = $this->state->get('filter.search');
        $description = $this->state->get('filter.description');
        $type= $this->state->get('filter.type');
        $leido = $this->state->get('filter.leido');
        if (empty($leido) ) {
            $this->state->set('filter.leido', 0);
        }
        $datefrom = $this->state->get('datefrom');
        $dateto = $this->state->get('dateto');        
            
        $app        = JFactory::getApplication();
        $search = $app->getUserState('filter.search', '');
        $listDirn = $this->state->get('list.direction');
        $listOrder = $this->state->get('list.ordering');

        //  Parámetros del componente
        $this->items= $this->get('Items');

        if (!empty($this->items)) {
            $this->pagination = $this->get('Pagination');    
        }
            
        // Obtenemos los parámetros del plugin...
        $model = $this->getModel();    
        $config= $model->getConfig();

        // Extraemos información necesaria 
        include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/library/model.php';
        $common_model = new SecuritycheckproModel();

        $logs_pending = $common_model->LogsPending();
        $trackactions_plugin_exists = $common_model->PluginStatus(8);
            
        $logs_attacks = 0;
        if (!is_null($config['logs_attacks'])) {
            $logs_attacks = $config['logs_attacks'];    
        }
                    
        // ... y los ponemos en el template
        $this->logs_attacks = $logs_attacks;    
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;

        parent::display($tpl);
    }
}
