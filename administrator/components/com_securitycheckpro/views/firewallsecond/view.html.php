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


class SecuritycheckprosViewFirewallSecond extends SecuritycheckproView
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

// Extraemos los elementos que nos interesan...
$second_level= null;
$second_level_redirect = null;
$second_level_limit_words = null;
$second_level_words = null;

if ( !is_null($items['second_level']) ) {
	$second_level = $items['second_level'];	
}

if ( !is_null($items['second_level_redirect']) ) {
	$second_level_redirect = $items['second_level_redirect'];	
}

if ( !is_null($items['second_level_limit_words']) ) {
	$second_level_limit_words = $items['second_level_limit_words'];	
}

if ( !is_null($items['second_level_words']) ) {
	$second_level_words = $items['second_level_words'];	
}


// ... y los ponemos en el template
$this->assignRef('second_level',$second_level);
$this->assignRef('second_level_redirect',$second_level_redirect);
$this->assignRef('second_level_limit_words',$second_level_limit_words);
$this->assignRef('second_level_words',$second_level_words);

parent::display($tpl);
}
}