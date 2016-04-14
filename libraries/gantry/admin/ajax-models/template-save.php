<?php
/**
 * @version   $Id: template-save.php 30234 2016-03-30 07:30:17Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();

/** @var $gantry Gantry */
		global $gantry;

$action = JFactory::getApplication()->input->getString('action');


switch ($action) {
	case 'save':
	case 'apply':
		echo gantryAjaxSaveTemplate();
		break;
	default:
		echo "error";
}

function gantryAjaxSaveTemplate()
{
	// Check for request forgeries
	gantry_checktoken() or jexit('Invalid Token');

	GantryLegacyJModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_gantry/models');
	$model = GantryLegacyJModel::getInstance("Template", 'GantryModel');
	$data  = JFactory::getApplication()->input->post->get('jform', array(), 'array');
	if (!$model->save($data)) {
		return 'error';
	}

	// Clear the front end gantry cache after each call
	$cache = GantryCache::getInstance(false);
	$cache->clearGroupCache();

	$task = JFactory::getApplication()->input->getCmd('task');
	if ($task == 'apply') {
		return JText::_('Template settings have been successfully applied.');
	} else {
		return JText::_('Template settings have been successfully saved.');
	}


}
