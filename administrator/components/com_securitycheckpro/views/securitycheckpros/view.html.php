<?php
/**
 * Securitycheckpros View para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;

/**
 * Securitycheckpros View
 */
class SecuritycheckprosViewSecuritycheckpros extends JViewLegacy
{

    protected $state;
    /**
     Securitycheckpros view método 'display'
     **/
    function display($tpl = null)
    {

        JToolBarHelper::title(JText::_('Securitycheck Pro').' | '.JText::_('COM_SECURITYCHECKPRO_VULNERABILITIES'), 'securitycheckpro');
        JToolBarHelper::custom('mostrar', 'database', 'database', 'COM_SECURITYCHECKPRO_LIST', false);

        $jinput = JFactory::getApplication()->input;

        // Obtenemos los datos del modelo...
        $model = $this->getModel();
        $update_database_plugin_enabled = $model->PluginStatus(3);
        $update_database_plugin_exists = $model->PluginStatus(4);
        $last_check = $model->get_campo_bbdd('securitycheckpro_update_database', 'last_check');
        $database_version = $model->get_campo_bbdd('securitycheckpro_update_database', 'version');
        $database_message = $model->get_campo_bbdd('securitycheckpro_update_database', 'message');
        $logs_pending = $model->LogsPending();
       $trackactions_plugin_exists = $model->PluginStatus(8);

        if ($update_database_plugin_exists) {
            $plugin_id = $model->get_plugin_id(1);
            $last_update = $model->get_last_update();    
        } else 
        {
            $last_update = 'Jun 02 2022';
        }

        // Filtro por tipo de extensión
         $this->state= $this->get('State');
        $type= $this->state->get('filter.extension_type');
        $vulnerable= $this->state->get('filter.vulnerable');
		
        if (($type == '') && ($vulnerable == '')) { //No hay establecido ningún filtro de búsqueda
			$this->items = $this->get('Data');
            $this->pagination = $this->get('Pagination');
        } else 
        {        		
			$this->items = $this->get('FilterData');
            $this->pagination = $this->get('FilterPagination');
        }

        // Obtenemos los datos del modelo (junto con '$items' y '$pagination' obtenidos anteriormente)
		$this->eliminados = $jinput->get('comp_eliminados', '0', 'string');
        $this->core_actualizado = $jinput->get('core_actualizado', '0', 'string');
        $this->comps_actualizados = $jinput->get('componentes_actualizados', '0', 'string');
        $this->comp_ok = $jinput->get('comp_ok', '0', 'string');

        // Ponemos los datos y la paginación en el template
        $this->update_database_plugin_exists = $update_database_plugin_exists;
        $this->update_database_plugin_enabled = $update_database_plugin_enabled;
        $this->last_check = $last_check;
        $this->database_version = $database_version;
        $this->database_message = $database_message;
        $this->last_update = $last_update;

        if ($update_database_plugin_exists) {
            $this->plugin_id = $plugin_id;
        }
        $this->logs_pending = $logs_pending;
        $this->trackactions_plugin_exists = $trackactions_plugin_exists;

        parent::display($tpl);
    }
}
