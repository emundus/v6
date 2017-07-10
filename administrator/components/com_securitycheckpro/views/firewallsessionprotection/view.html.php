<?php
/**
* FirewallSessionProtection View para el Componente Securitycheckpro
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


class SecuritycheckprosViewFirewallSessionProtection extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_LABEL'), 'securitycheckpro' );	
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
$session_protection_active= null;
$session_hijack_protection = null;
$track_failed_logins = null;
$write_log = null;
$logins_to_monitorize = null;
$include_password_in_log = null;
$actions_failed_login = null;
$email_on_admin_login = null;
$forbid_admin_frontend_login = null;
$forbid_new_admins = null;

if ( !is_null($items['session_protection_active']) ) {
	$session_protection_active = $items['session_protection_active'];	
}

if ( !is_null($items['session_hijack_protection']) ) {
	$session_hijack_protection = $items['session_hijack_protection'];	
}

if ( !is_null($items['track_failed_logins']) ) {
	$track_failed_logins = $items['track_failed_logins'];	
}

if ( !is_null($items['write_log']) ) {
	$write_log = $items['write_log'];	
}

if ( !is_null($items['logins_to_monitorize']) ) {
	$logins_to_monitorize = $items['logins_to_monitorize'];	
}

if ( !is_null($items['include_password_in_log']) ) {
	$include_password_in_log = $items['include_password_in_log'];	
}

if ( !is_null($items['actions_failed_login']) ) {
	$actions_failed_login = $items['actions_failed_login'];	
}

if ( !is_null($items['email_on_admin_login']) ) {
	$email_on_admin_login = $items['email_on_admin_login'];	
}

if ( !is_null($items['forbid_admin_frontend_login']) ) {
	$forbid_admin_frontend_login = $items['forbid_admin_frontend_login'];	
}

if ( !is_null($items['forbid_new_admins']) ) {
	$forbid_new_admins = $items['forbid_new_admins'];	
}

// ... y los ponemos en el template
$this->assignRef('session_protection_active',$session_protection_active);
$this->assignRef('session_hijack_protection',$session_hijack_protection);
$this->assignRef('track_failed_logins',$track_failed_logins);
$this->assignRef('include_password_in_log',$include_password_in_log);
$this->assignRef('write_log',$write_log);
$this->assignRef('logins_to_monitorize',$logins_to_monitorize);
$this->assignRef('actions_failed_login',$actions_failed_login);
$this->assignRef('session_protection_groups',$items['session_protection_groups']);
$this->assignRef('email_on_admin_login',$email_on_admin_login);
$this->assignRef('forbid_admin_frontend_login',$forbid_admin_frontend_login);
$this->assignRef('forbid_new_admins',$forbid_new_admins);

parent::display($tpl);
}
}