<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;

//Register  base class
JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
JLoader::register('DropfilesDropbox', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesDropbox.php');
JLoader::register('DropfilesOneDrive', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDrive.php');
JLoader::register('DropfilesOneDriveBusiness', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDriveBusiness.php');

DropfilesBase::initComponent();
// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_dropfiles')) {
    $app = JFactory::getApplication();
    return $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
}

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller = JControllerLegacy::getInstance('Dropfiles');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
