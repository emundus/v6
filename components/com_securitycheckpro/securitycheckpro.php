<?php
/**
 * @ author Jose A. Luque
 * @copyright Copyright (c) 2013 - Jose A. Luque
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

// Load library
require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' .
DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'loader.php';
require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'json.php';

$jinput = JFactory::getApplication()->input;
$view = $jinput->get('view','','word');
$controller = $view;

if ( $controller == "json") {
	try{
		// Creamos el controlador
		$classname = 'SecuritycheckprosController' . $controller;
		$controller = new $classname;

		// Realizamos la tarea requerida
		$controller->execute($view);
	} catch (Exception $e)
	{		
					
	}
}
