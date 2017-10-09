<?php
/**
* FirewallSpam View para el Componente Securitycheckpro
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


class SecuritycheckprosViewFirewallSpam extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_LABEL'), 'securitycheckpro' );	
}


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
$check_if_user_is_spammer= null;
$spammer_action=null;
$spammer_write_log=null;
$spammer_limit=3;
$plugin_installed=false;

// Chequeamos si el plugin 'Spam protection' está instalado
$plugin_installed = $model->is_plugin_installed();

if ( !is_null($items['check_if_user_is_spammer']) ) {
	$check_if_user_is_spammer = $items['check_if_user_is_spammer'];	
}
if ( !is_null($items['spammer_action']) ) {
	$spammer_action = $items['spammer_action'];	
}
if ( !is_null($items['spammer_write_log']) ) {
	$spammer_write_log = $items['spammer_write_log'];	
}
if ( !is_null($items['spammer_limit']) ) {
	$spammer_limit = $items['spammer_limit'];	
}
if ( !is_null($items['spammer_what_to_check']) ) {
	$spammer_what_to_check = $items['spammer_what_to_check'];	
}

// ... y los ponemos en el template
$this->assignRef('check_if_user_is_spammer',$check_if_user_is_spammer);
$this->assignRef('spammer_action',$spammer_action);
$this->assignRef('spammer_write_log',$spammer_write_log);
$this->assignRef('spammer_limit',$spammer_limit);
$this->assignRef('plugin_installed',$plugin_installed);
$this->assignRef('spammer_what_to_check',$spammer_what_to_check);

parent::display($tpl);
}
}