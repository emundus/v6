<?php
/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load library
require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'loader.php');

$controller = 'json';
require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'json.php');

// Creamos el controlador
$classname = 'SecuritycheckprosController'.$controller;
$controller = new $classname( );
// Realizamos la tarea requerida
$controller->execute('json');