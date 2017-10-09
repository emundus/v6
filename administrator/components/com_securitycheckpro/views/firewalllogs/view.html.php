<?php
/**
* FirewallLogs View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

// Load plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_system_securitycheckpro');


class SecuritycheckprosViewFirewallLogs extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('PLG_SECURITYCHECKPRO_LOGS_LABEL'), 'securitycheckpro' );	
}

/**
* Securitycheckpros FirewallConfig método 'display'
**/
function display($tpl = null)
{

// Filtro
$this->state= $this->get('State');
$lists = $this->state->get('filter.lists_search');

// Obtenemos el modelo
$model = $this->getModel();

//  Parámetros del plugin
$items= $model->getConfig();

// Extraemos los elementos que nos interesan...
$logs_attacks= null;
$log_limits_per_ip_and_day = null;
$add_geoblock_logs = null;
$add_access_attempts_logs = null;

if ( !is_null($items['logs_attacks']) ) {
	$logs_attacks = $items['logs_attacks'];	
}

if ( !is_null($items['log_limits_per_ip_and_day']) ) {
	$log_limits_per_ip_and_day = $items['log_limits_per_ip_and_day'];	
}

if ( !is_null($items['add_geoblock_logs']) ) {
	$add_geoblock_logs = $items['add_geoblock_logs'];	
}

if ( !is_null($items['add_access_attempts_logs']) ) {
	$add_access_attempts_logs = $items['add_access_attempts_logs'];	
}

// ... y los ponemos en el template
$this->assignRef('logs_attacks',$logs_attacks);
$this->assignRef('log_limits_per_ip_and_day',$log_limits_per_ip_and_day);
$this->assignRef('add_geoblock_logs',$add_geoblock_logs);
$this->assignRef('add_access_attempts_logs',$add_access_attempts_logs);

parent::display($tpl);
}
}