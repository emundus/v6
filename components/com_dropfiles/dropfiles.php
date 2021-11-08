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
JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
JLoader::register('DropfilesDropbox', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesDropbox.php');

$config = array();

$view = JFactory::getApplication()->input->get('view', null);
$task = JFactory::getApplication()->input->get('task', null);
header('Access-Control-Allow-Origin: *');
if (preg_match('/^front.*/', $task) ||
    ($task === null && preg_match('/^front.*/', $view)) ||
    $task === 'frontsearch.search') {
    DropfilesBase::initFrontComponent();
    require_once JPATH_COMPONENT . '/helpers/category.php';
    require_once JPATH_COMPONENT . '/helpers/query.php';
    require_once JPATH_COMPONENT . '/helpers/class.exceltotext.php';
    require_once JPATH_COMPONENT . '/helpers/class.filetotext.php';
} else {
    if ($view !== 'singlecategory' && $view !== 'files'  && $view !== 'manage' && !DropfilesHelper::validateFrontTask($task) && !JFactory::getUser()->authorise('core.manage', 'com_dropfiles')) {
        $app = JFactory::getApplication();
        $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
        return false;
    }

    DropfilesBase::initComponent();

    // Execute the task.
    if ($view === 'dropfiles' || $view === 'users' || $view === null || strpos($task, 'googledrive.') === 0
        || strpos($task, 'dropbox.') === 0 || strpos($task, 'onedrive.') === 0) {
        $config['base_path'] = JPATH_ADMINISTRATOR . '/components/com_dropfiles';
    }
}
// Include dependancies
jimport('joomla.application.component.controller');


$controller = JControllerLegacy::getInstance('Dropfiles', $config);
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
