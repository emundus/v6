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
class SecuritycheckprosViewUpload extends JViewLegacy
{
    /**
     * M�todo display de la vista Securitycheck (muestra los detalles de las vulnerabilidades del producto escogido)
     **/
    function display($tpl = null)
    {
        JToolBarHelper::title(JText::_('Securitycheck Pro').' | '.JText::_('COM_SECURITYCHECKPRO_IMPORT_CONFIG_TITLE'), 'securitycheckpro');
        
        // Informaci�n para la barra de navegaci�n
        include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/library/model.php';
        $common_model = new SecuritycheckproModel();

        $logs_pending = $common_model->LogsPending();
        $trackactions_plugin_exists = $common_model->PluginStatus(8);
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;
        
        parent::display($tpl);
    }
}
