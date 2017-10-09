<?php
/**
* FirewallSecond View para el Componente Securitycheckpro
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


class SecuritycheckprosViewFirewallInspector extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('PLG_SECURITYCHECKPRO_SECOND_LABEL'), 'securitycheckpro' );	
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

//  Esta el plugin habilitado
$url_inspector_enabled= $model->PluginStatus(7);

// Extraemos los elementos que nos interesan...
$inspector_forbidden_words= null;
$write_log_inspector= null;
$action_inspector= 2;
$send_email_inspector = 0;

if ( !is_null($items['inspector_forbidden_words']) ) {
	$inspector_forbidden_words = $items['inspector_forbidden_words'];	
}

if ( !is_null($items['write_log_inspector']) ) {
	$write_log_inspector = $items['write_log_inspector'];	
}

if ( !is_null($items['action_inspector']) ) {
	$action_inspector = $items['action_inspector'];	
}

if ( !is_null($items['send_email_inspector']) ) {
	$send_email_inspector = $items['send_email_inspector'];	
}

// ... y los ponemos en el template
$this->assignRef('inspector_forbidden_words',$inspector_forbidden_words);
$this->assignRef('write_log_inspector',$write_log_inspector);
$this->assignRef('action_inspector',$action_inspector);
$this->assignRef('send_email_inspector',$send_email_inspector);
$this->assignRef('url_inspector_enabled',$url_inspector_enabled);

parent::display($tpl);
}
}