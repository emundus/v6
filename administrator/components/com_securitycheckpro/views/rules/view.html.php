<?php
/**
 * Vista Rules para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

class SecuritycheckprosViewRules extends JViewLegacy
{
    protected $items;

    //protected $pagination;

    //protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {

        JToolBarHelper::title(JText::_('Securitycheck Pro').' | ' . JText::_('COM_SECURITYCHECKPRO_CPANEL_RULES_TEXT'), 'securitycheckpro');
        JToolBarHelper::publish('apply_rules', JText::_('COM_SECURITYCHECKPRO_RULES_APPLY'), true);
        JToolBarHelper::unpublish('not_apply_rules', JText::_('COM_SECURITYCHECKPRO_RULES_NOT_APPLY'), true);
        JToolBarHelper::custom('rules_logs', 'users', 'users', 'COM_SECURITYCHECKPRO_RULES_VIEW_LOGS', false);

        // Filtro por tipo de extensión
        $this->state= $this->get('State');
        $acl_search = $this->state->get('filter.acl_search');

        $model = $this->getModel("rules");
        $items = $model->load();

        // Información para la barra de navegación
        include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/library/model.php';
        $common_model = new SecuritycheckproModel();

        $logs_pending = $common_model->LogsPending();
        $trackactions_plugin_exists = $common_model->PluginStatus(8);
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;

        // Ponemos los datos en el template
        $this->items = $items;

        if (!empty($items)) {
            $this->pagination = $this->get('Pagination');    
        }

        parent::display($tpl);
    }
    
}
