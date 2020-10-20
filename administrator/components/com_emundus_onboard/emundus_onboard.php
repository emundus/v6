<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_emundus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// emundus helpers
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_emundus'))
{
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}
// Include dependancies
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
$version = new JVersion();
$sversion = $version->getShortVersion();
// TODO: Update this to PHP 7 once done
if (version_compare( phpversion(), '5.0.0', '<')) {
    echo 'Sorry you are using ' .  phpversion() . ". You need to have PHP5 installed to run eMundus\n";
    return;
}

// Update this to Joomla 3.7.3 once done
if (version_compare( phpversion(), '5.3', '>=') && ($version->RELEASE <= 1.5 && $version->DEV_LEVEL <= 14)) {
    JError::raiseNotice(500, 'You are using PHP ' .  phpversion() . ". but Joomla $sversion does not fully support this!");
}

if (ini_get('magic_quotes_sybase') == 1){
    echo "You have the PHP directive magic_quotes_sybase turned ON Fabrik requires you to turn this directive off, either by editing your php.ini file or adding:<p> php_value magic_quotes_sybase 0</p> to your .htaccess file";
    return;
}

if (in_array( 'suhosin', get_loaded_extensions()) ) {
    JError::raiseWarning(500, JText::_('Looks like your server has suhosin installed - this may cause issues when submitting large forms, or forms with long element names'));
}

// LOGGER
jimport('joomla.log.log');
jimport('joomla.methods');
JLog::addLogger(
    array(
        'text_file' => 'com_emundus.error.php'
    ),
    JLog::ALL,
    array('com_emundus')
);
JLog::addLogger(
    array(
        'text_file' => 'com_emundus.email.php'
    ),
    JLog::ALL,
    array('com_emundus.email')
);

// Require the base controller
require_once( JPATH_COMPONENT.DS.'controller.php' );

$controllers = explode(',', 'panel');
if (!JRequest::getWord('controller'))
    JRequest::setVar( 'controller', $controllers[0] );

foreach ($controllers as $controller) {
    $link = JRoute::_("index.php?option=com_emundus_onboard&controller=".$controller);
    $selected = ($controller == JRequest::getWord('controller'));
    JSubMenuHelper::addEntry(JText::_( 'MENU_' . strtoupper($controller) ), "index.php?option=com_emundus_onboard&controller=".$controller, ($controller == JRequest::getWord('controller')));
}
JRequest::setVar( 'view', JRequest::getWord('controller') );


// Require specific controller if requested; allways, in standard execution
if ($controller = JRequest::getWord('controller')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path))
        require_once $path;
    else
        $controller = '';
}

// Create the controller
$classname	= 'EmundusController'.$controller;
$controller	= new $classname( );

// Perform the Request task
$controller->execute( JRequest::getCmd( 'task' ) );

// Redirect if set by the controller
$controller->redirect();

?>
