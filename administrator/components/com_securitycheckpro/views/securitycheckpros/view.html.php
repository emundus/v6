<?php
/**
* Securitycheckpros View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

/**
* Securitycheckpros View
*
*/
class SecuritycheckprosViewSecuritycheckpros extends JViewLegacy{

protected $state;
/**
* Securitycheckpros view método 'display'
**/
function display($tpl = null)
{

JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | '.JText::_('COM_SECURITYCHECKPRO_VULNERABILITIES'), 'securitycheckpro' );
JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_CONTROL_PANEL');
JToolBarHelper::custom('redireccion_system_info','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_SYSTEM_INFO');
JToolBarHelper::custom( 'mostrar', 'database', 'database', 'COM_SECURITYCHECKPRO_LIST' );

// Obtenemos los datos del modelo...
$model = $this->getModel();
$update_database_plugin_enabled = $model->PluginStatus(3);
$update_database_plugin_exists = $model->PluginStatus(4);
$last_check = $model->get_campo_bbdd('securitycheckpro_update_database','last_check');
$database_version = $model->get_campo_bbdd('securitycheckpro_update_database','version');
$database_message = $model->get_campo_bbdd('securitycheckpro_update_database','message');

if ( $update_database_plugin_exists ) {
	$plugin_id = $model->get_plugin_id(1);
	$last_update = $model->get_last_update();	
} else {
	$last_update = 'Dec 11 2017';
}

// Filtro por tipo de extensión
$this->state= $this->get('State');
$type= $this->state->get('filter.extension_type');
$vulnerable= $this->state->get('filter.vulnerable');

if ( ($type == '') && ($vulnerable == '') ) { //No hay establecido ningún filtro de búsqueda
			$items = $this->get('Data');
			$pagination = $this->get('Pagination');
		} else {			
			$items = $this->get('FilterData');
			$pagination = $this->get('FilterPagination');
		}

// Obtenemos los datos del modelo (junto con '$items' y '$pagination' obtenidos anteriormente)
$eliminados = JRequest::getVar('comp_eliminados');
$core_actualizado = JRequest::getVar('core_actualizado');
$comps_actualizados = JRequest::getVar('componentes_actualizados');
$comp_ok = JRequest::getVar('comp_ok');

// Ponemos los datos y la paginación en el template
$this->assignRef('items', $items);
$this->assignRef('pagination', $pagination);
$this->assignRef('eliminados', $eliminados);
$this->assignRef('core_actualizado', $core_actualizado);
$this->assignRef('comps_actualizados', $comps_actualizados);
$this->assignRef('comp_ok', $comp_ok);
$this->assignRef('update_database_plugin_exists', $update_database_plugin_exists);
$this->assignRef('update_database_plugin_enabled', $update_database_plugin_enabled);
$this->assignRef('last_check', $last_check);
$this->assignRef('database_version', $database_version);
$this->assignRef('database_message', $database_message);
$this->assignRef('last_update', $last_update);
if ( $update_database_plugin_exists ) {
	$this->assignRef('plugin_id', $plugin_id);
}

parent::display($tpl);
}
}