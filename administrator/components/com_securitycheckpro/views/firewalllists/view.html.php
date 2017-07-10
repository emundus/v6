<?php
/**
* Logs View para el Componente Securitycheckpro
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
class SecuritycheckprosViewFirewallLists extends SecuritycheckproView
{

protected $state;

function __construct() 	{
	parent::__construct();
	
	JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('PLG_SECURITYCHECKPRO_LISTS_LABEL'), 'securitycheckpro' );	
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
$blacklist_elements= null;
$pagination_blacklist = null;
if ( (!is_null($items['blacklist'])) && ($items['blacklist'] != '') ) {
	$items['blacklist'] = str_replace(' ','',$items['blacklist']);
	$blacklist_elements = explode(',',trim($items['blacklist']));
	$blacklist_elements = $model->filter_data($blacklist_elements,$pagination_blacklist);
}

$dynamic_blacklist_elements= $model->get_dynamic_blacklist_ips();

$whitelist_elements= null;
$pagination_whitelist = null;

if ( (!is_null($items['whitelist'])) && ($items['whitelist'] != '') ) {	
	$items['whitelist'] = str_replace(' ','',$items['whitelist']);
	$whitelist_elements = explode(',',trim($items['whitelist']));
	$whitelist_elements = $model->filter_data($whitelist_elements,$pagination_whitelist);
}


// ... y los ponemos en el template
$this->assignRef('blacklist_elements',$blacklist_elements);
$this->assignRef('dynamic_blacklist_elements',$dynamic_blacklist_elements);
$this->assignRef('whitelist_elements',$whitelist_elements);
$this->assignRef('dynamic_blacklist',$items['dynamic_blacklist']);
$this->assignRef('dynamic_blacklist_time',$items['dynamic_blacklist_time']);
$this->assignRef('dynamic_blacklist_counter',$items['dynamic_blacklist_counter']);
$this->assignRef('blacklist_email',$items['blacklist_email']);
$this->assignRef('priority1',$items['priority1']);
$this->assignRef('priority2',$items['priority2']);
$this->assignRef('priority3',$items['priority3']);
$this->assignRef('priority4',$items['priority4']);

// Cargamos las librerías para extraer información de las ips
require_once JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/ip.php';
		
$ipmodel = new SecuritycheckProsModelIP;
							
// Extraemos la geolocalización de las distintas listas...
$blacklist_elements_geolocation= array();
$whitelist_elements_geolocation= array();
$dymanic_elements_geolocation= array();

if ( !is_null($blacklist_elements) ) {
	foreach($blacklist_elements as $element) {
		// Extraemos la ip de cada elemento, eliminando los comodines si es necesario
		$ip = $model->change_wildcards($element);
		// Si tenemos formato CIDR extraemos, por ejemplo, la última ip del rango para la geolocalización
		if ( ( strstr($element,"/") != false ) ) {
			$ip_range_info = $ipmodel->get_ip_info($element);
			$ip = $ip_range_info["hostmax"];			
		}
		$is_valid = filter_var($ip,FILTER_VALIDATE_IP);		
		if ( $is_valid ) {
			// Extraemos la geolocalización
			$geo = $model->geolocation($ip);
			$blacklist_elements_geolocation[] = $geo;	
		}
		
	}
}

if ( !is_null($whitelist_elements) ) {
	foreach($whitelist_elements as $element) {
		// Extraemos la ip de cada elemento, eliminando los comodines si es necesario
		$ip = $model->change_wildcards($element);
		// Si tenemos formato CIDR extraemos, por ejemplo, la ltima ip del rango para la geolocalizacin
		if ( ( strstr($element,"/") != false ) ) {
			$ip_range_info = $ipmodel->get_ip_info($element);
			$ip = $ip_range_info["hostmax"];			
		}
		$is_valid = filter_var($ip,FILTER_VALIDATE_IP);
		if ( $is_valid ) {
			// Extraemos la geolocalización
			$geo = $model->geolocation($ip);
			$whitelist_elements_geolocation[] = $geo;	
		}
	}
}

if ( !is_null($dynamic_blacklist_elements) ) {
	foreach($dynamic_blacklist_elements as $element) {
		$is_valid = filter_var($element,FILTER_VALIDATE_IP);
		if ( $is_valid ) {
			// Extraemos la geolocalización
			$geo = $model->geolocation($element);
			$dynamic_elements_geolocation[] = $geo;	
		}
	}
}

// Añadimos la información
$this->assignRef('blacklist_elements_geolocation',$blacklist_elements_geolocation);
$this->assignRef('whitelist_elements_geolocation',$whitelist_elements_geolocation);
$this->assignRef('dynamic_elements_geolocation',$dynamic_elements_geolocation);


// Añadimos también la paginación (comparamos las dos paginaciones y asignamos la mayor)
if ( (!is_null($pagination_blacklist)) && (!is_null($pagination_whitelist)) ) {
	if ( ($pagination_blacklist->get('total')) > ($pagination_whitelist->get('total')) ) {
		$this->assignRef('pagination', $pagination_blacklist);
	} else {
		$this->assignRef('pagination', $pagination_whitelist);				
	}
} else if ( !is_null($pagination_blacklist) ) {
	$this->assignRef('pagination', $pagination_blacklist);	
} else if ( !is_null($pagination_whitelist) ) {
	$this->assignRef('pagination', $pagination_whitelist);	
}

parent::display($tpl);
}
}