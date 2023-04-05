<?php

/**
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * Securitycheck View
 */
class SecuritycheckprosViewsysinfo extends JViewLegacy
{
    /**
     * Método display de la vista Securitycheck (muestra los detalles de las vulnerabilidades del producto escogido)
     **/
    function display($tpl = null)
    {
        JToolBarHelper::title(JText::_('Securitycheck Pro').' | '.JText::_('COM_SECURITYCHECKPRO_SYSTEM_INFORMATION'), 'securitycheckpro');
                        
        // Obtenemos los datos del modelo
        $model = $this->getModel("sysinfo");
        
        $system_info = $model->getInfo();        
        $logs_pending = $model->LogsPending();
        $trackactions_plugin_exists = $model->PluginStatus(8);
                                
        // Ponemos los datos y la paginación en el template
        $this->system_info = $system_info;
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;
                            
        parent::display($tpl);
    }
}
