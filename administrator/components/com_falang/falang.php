<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

//Global definitions
if( !defined('DS') ) {
    define( 'DS', DIRECTORY_SEPARATOR );
}

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_falang')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}


jimport('joomla.filesystem.path');

// disable Zend php4 compatability mode - this causes problem with passing translations by reference
// see http://forum.joomla.org/index.php/topic,80065.msg451560.html#msg451560 for details of problem
// See http://uk.php.net/ini.core for description of the flag
@ini_set("zend.ze1_compatibility_mode","Off");

/** required standard extentions **/
require_once( JPATH_SITE .DS. 'components' .DS. 'com_falang' .DS. 'helpers' .DS. 'defines.php' );
JLoader::register('FalangManager', FALANG_ADMINPATH .DS. 'classes' .DS. 'FalangManager.class.php' );
JLoader::register('FalangExtensionHelper', FALANG_ADMINPATH .DS. 'helpers' .DS. 'extensionHelper.php' );
JLoader::register('FalangVersion', FALANG_ADMINPATH .DS. 'version.php' );
$falangManager = FalangManager::getInstance( dirname( __FILE__ ) );

$cmd = JRequest::getCmd('task', 'cpanel.show');

if (strpos($cmd, '.') != false) {
	// We have a defined controller/task pair -- lets split them out
	list($controllerName, $task) = explode('.', $cmd);
	
	// Define the controller name and path
	$controllerName	= strtolower($controllerName);
	$controllerPath	= FALANG_ADMINPATH.DS.'controllers'.DS.$controllerName.'.php';
	
	// If the controller file path exists, include it ... else lets die with a 500 error
	if (file_exists($controllerPath)) {
		require_once($controllerPath);
	} else {
		JError::raiseError(500, 'Invalid Controller');
	}
} else {
	// Base controller, just set the task 
	$controllerName = null;
	$task = $cmd;
}

// Set the name for the controller and instantiate it
$controllerClass = ucfirst($controllerName).'Controller';
if (class_exists($controllerClass)) {
	$controller = new $controllerClass();
} else {
	JError::raiseError(500, 'Invalid Controller Class - '.$controllerClass );
}

$config	= JFactory::getConfig();

// Perform the Request task
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
