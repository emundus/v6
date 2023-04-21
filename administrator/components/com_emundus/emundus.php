<?php
/**
 * Entry point to Tchooz's administration pages
 * @package Joomla.Administrator
 * @subpackage eMundus
 * @copyright Copyright (C) 2015-2023 emundus.fr. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\MVC\Controller\BaseController;

use Joomla\CMS\Factory;

$app = Factory::getApplication();
$input = $app->input;

// Access check.
if (!$app->getIdentity()->authorise('core.manage', 'com_emundus'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
}

// Load front end language file as well
$lang = $app->getLanguage();
$lang->load('com_emundus', JPATH_SITE . '/components/com_emundus');

$view = $app->input->get('view');
$layout = $app->input->get('layout', '');

// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

$controller	= BaseController::getInstance('EmundusAdmin');

// Execute the task.
$controller->execute($input->get('task', 'panel.display'));

$controller->redirect();

?>
