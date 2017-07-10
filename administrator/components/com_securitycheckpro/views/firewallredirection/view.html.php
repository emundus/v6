<?php
/**
* FirewallRedirection View para el Componente Securitycheckpro
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


class SecuritycheckprosViewFirewallRedirection extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('PLG_SECURITYCHECKPRO_REDIRECTION_LABEL'), 'securitycheckpro' );	
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
$redirect_after_attack= null;
$redirect_options = null;

if ( !is_null($items['redirect_after_attack']) ) {
	$redirect_after_attack = $items['redirect_after_attack'];	
}

if ( !is_null($items['redirect_options']) ) {
	$redirect_options = $items['redirect_options'];	
}

$redirect_url = $items['redirect_url'];	
$custom_code = $items['custom_code'];

if ( is_null($custom_code) ) {
	$custom_code = "<h1 style=\"text-align:center;\">" . JText::_('COM_SECURITYCHECKPRO_403_ERROR') . "</h1>";
}

// ... y los ponemos en el template
$this->assignRef('redirect_after_attack',$redirect_after_attack);
$this->assignRef('redirect_options',$redirect_options);
$this->assignRef('redirect_url',$redirect_url);
$this->assignRef('custom_code',$custom_code);

parent::display($tpl);
}
}