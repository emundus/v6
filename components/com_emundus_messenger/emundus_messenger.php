<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_emundus_messenger
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// emundus helpers
require_once (JPATH_SITE.DS.'components/com_emundus_onboard/helpers'.DS.'access.php');

// LOGGER
jimport('joomla.log.log');
jimport('joomla.methods');
JLog::addLogger(
    array(
        'text_file' => 'com_emundus_messenger.error.php'
    ),
    JLog::ALL,
    array('com_emundus_messenger')
);
JLog::addLogger(
    array(
        'text_file' => 'com_emundus_messenger.email.php'
    ),
    JLog::ALL,
    array('com_emundus_messenger.email')
);

$current_user = JFactory::getUser();

if (!EmundusonboardHelperAccess::asPartnerAccessLevel($current_user->id)) {
    die( JText::_('RESTRICTED_ACCESS') );
}

$app = JFactory::getApplication();

// Require specific controller if requested
if ($controller = $app->input->get('controller', '', 'WORD')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
        // Create the controller
        $classname    = 'EmundusmessengerController'.$controller;
        $controller   = new $classname();
    }
    else {
        $controller = '';
    }

    $controller->execute($app->input->get('task'));
}
else {
    $controller = JControllerLegacy::getInstance('Emundusmessenger');
}

// Perform the Request task
$controller->execute($app->input->get('task'));

// Redirect if set by the controller
$controller->redirect();

