<?php
/**
* FirewallExceptions View para el Componente Securitycheckpro
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

/**
* Logs View
*
*/
class SecuritycheckprosViewFirewallExceptions extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('PLG_SECURITYCHECKPRO_EXCEPTIONS_LABEL'), 'securitycheckpro' );	
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

// Extraemos los elementos de las distintas listas...
$check_header_referer= null;
$check_base_64 = null;
$base64_exceptions = null;
$strip_tags_exceptions = null;
$duplicate_backslashes_exceptions = null;
$line_comments_exceptions = null;
$sql_pattern_exceptions = null;
$if_statement_exceptions = null;
$using_integers_exceptions = null;
$escape_strings_exceptions = null;
$lfi_exceptions = null;
$second_level_exceptions = null;
$exclude_exceptions_if_vulnerable = 1;
$strip_all_tags = null;
$tags_to_filter = null;

if ( !is_null($items['check_header_referer']) ) {
	$check_header_referer = $items['check_header_referer'];	
}

if ( !is_null($items['check_base_64']) ) {
	$check_base_64 = $items['check_base_64'];	
}

if ( !is_null($items['base64_exceptions']) ) {
	$base64_exceptions = $items['base64_exceptions'];	
}

if ( !is_null($items['strip_tags_exceptions']) ) {
	$strip_tags_exceptions = $items['strip_tags_exceptions'];	
}

if ( !is_null($items['duplicate_backslashes_exceptions']) ) {
	$duplicate_backslashes_exceptions = $items['duplicate_backslashes_exceptions'];	
}

if ( !is_null($items['line_comments_exceptions']) ) {
	$line_comments_exceptions = $items['line_comments_exceptions'];	
}

if ( !is_null($items['sql_pattern_exceptions']) ) {
	$sql_pattern_exceptions = $items['sql_pattern_exceptions'];	
}

if ( !is_null($items['if_statement_exceptions']) ) {
	$if_statement_exceptions = $items['if_statement_exceptions'];	
}

if ( !is_null($items['using_integers_exceptions']) ) {
	$using_integers_exceptions = $items['using_integers_exceptions'];	
}

if ( !is_null($items['lfi_exceptions']) ) {
	$lfi_exceptions = $items['lfi_exceptions'];	
}

if ( !is_null($items['escape_strings_exceptions']) ) {
	$escape_strings_exceptions = $items['escape_strings_exceptions'];	
}

if ( !is_null($items['second_level_exceptions']) ) {
	$second_level_exceptions = $items['second_level_exceptions'];	
}

$exclude_exceptions_if_vulnerable = $items['exclude_exceptions_if_vulnerable'];	

if ( !is_null($items['strip_all_tags']) ) {
	$strip_all_tags = $items['strip_all_tags'];	
}

if ( !is_null($items['tags_to_filter']) ) {
	$tags_to_filter = $items['tags_to_filter'];	
}
				
// ... y los ponemos en el template
$this->assignRef('check_header_referer',$items['check_header_referer']);
$this->assignRef('check_base_64',$items['check_base_64']);
$this->assignRef('base64_exceptions',$items['base64_exceptions']);
$this->assignRef('strip_tags_exceptions',$items['strip_tags_exceptions']);
$this->assignRef('duplicate_backslashes_exceptions',$items['duplicate_backslashes_exceptions']);
$this->assignRef('line_comments_exceptions',$items['line_comments_exceptions']);
$this->assignRef('sql_pattern_exceptions',$items['sql_pattern_exceptions']);
$this->assignRef('if_statement_exceptions',$items['if_statement_exceptions']);
$this->assignRef('using_integers_exceptions',$items['using_integers_exceptions']);
$this->assignRef('lfi_exceptions',$items['lfi_exceptions']);
$this->assignRef('escape_strings_exceptions',$items['escape_strings_exceptions']);
$this->assignRef('second_level_exceptions',$items['second_level_exceptions']);
$this->assignRef('exclude_exceptions_if_vulnerable',$items['exclude_exceptions_if_vulnerable']);
$this->assignRef('strip_all_tags',$items['strip_all_tags']);
$this->assignRef('tags_to_filter',$items['tags_to_filter']);

parent::display($tpl);
}
}