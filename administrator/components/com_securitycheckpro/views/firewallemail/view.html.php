<?php
/**
* FirewallEmail View para el Componente Securitycheckpro
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


class SecuritycheckprosViewFirewallEmail extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('PLG_SECURITYCHECKPRO_EMAIL_NOTIFICATIONS_LABEL'), 'securitycheckpro' );	
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
$email_active= null;
$email_subject = null;
$email_body = null;
$email_add_applied_rule = null;
$email_to = null;
$email_from_domain = null;
$email_from_name = null;
$email_max_number = null;

if ( !is_null($items['email_active']) ) {
	$email_active = $items['email_active'];	
}

if ( !is_null($items['email_subject']) ) {
	$email_subject = $items['email_subject'];	
}

if ( !is_null($items['email_body']) ) {
	$email_body = $items['email_body'];	
}

if ( !is_null($items['email_add_applied_rule']) ) {
	$email_add_applied_rule = $items['email_add_applied_rule'];	
}

if ( !is_null($items['email_to']) ) {
	$email_to = $items['email_to'];	
}

if ( !is_null($items['email_from_domain']) ) {
	$email_from_domain = $items['email_from_domain'];	
}

if ( !is_null($items['email_from_name']) ) {
	$email_from_name = $items['email_from_name'];	
}

if ( !is_null($items['email_max_number']) ) {
	$email_max_number = $items['email_max_number'];	
}

// ... y los ponemos en el template
$this->assignRef('email_active',$email_active);
$this->assignRef('email_subject',$email_subject);
$this->assignRef('email_body',$email_body);
$this->assignRef('email_add_applied_rule',$email_add_applied_rule);
$this->assignRef('email_to',$email_to);
$this->assignRef('email_from_domain',$email_from_domain);
$this->assignRef('email_from_name',$email_from_name);
$this->assignRef('email_max_number',$email_max_number);

parent::display($tpl);
}
}