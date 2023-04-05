<?php
/**
 * Cron View para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

// Load plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_system_securitycheckpro_cron');


class SecuritycheckprosViewCron extends SecuritycheckproView
{

    protected $state;

    function __construct()
    {
        parent::__construct();
    
        JToolBarHelper::title(JText::_('Securitycheck Pro').' | ' .JText::_('PLG_SECURITYCHECKPRO_CRON_SCHEDULE_LABEL'), 'securitycheckpro');    
    }

    /**
     Securitycheckpros Cron método 'display'
     **/
    function display($tpl = null)
    {

        // Filtro
        $this->state= $this->get('State');
        $lists = $this->state->get('filter.lists_search');

        // Obtenemos el modelo
        $model = $this->getModel();

        //  Parámetros del plugin
        $items= $model->getCronConfig();

        // Información para la barra de navegación
        $logs_pending = $model->LogsPending();
        $trackactions_plugin_exists = $model->PluginStatus(8);
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;

        // Extraemos los elementos que nos interesan...
        $tasks= null;
        $launch_time = null;
        $periodicity = null;

        if (!is_null($items['tasks'])) {
            $tasks = $items['tasks'];    
        }

        if (!is_null($items['launch_time'])) {
            $launch_time = $items['launch_time'];    
        }

        if (!is_null($items['periodicity'])) {
            $periodicity = $items['periodicity'];    
        }
        // ... y los ponemos en el template
        $this->tasks = $tasks;
        $this->launch_time = $launch_time;
        $this->periodicity = $periodicity;

        parent::display($tpl);
    }
}
