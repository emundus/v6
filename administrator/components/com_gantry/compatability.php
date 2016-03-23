<?php
/**
 * @version   $Id: compatability.php 6307 2013-01-05 06:00:18Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('_JEXEC') or die('Restricted access');

if (!class_exists('GantryLegacyJView', false)) {
	$jversion = new JVersion();
	if (version_compare($jversion->getShortVersion(), '2.5.5', '>')) {
		class GantryLegacyJView extends JViewLegacy
		{
		}

		class GantryLegacyJController extends JControllerLegacy
		{
		}

		class GantryLegacyJModel extends JModelLegacy
		{
		}
	} else {
		jimport('joomla.application.component.view');
		jimport('joomla.application.component.controller');
		jimport('joomla.application.component.model');
		class GantryLegacyJView extends JView
		{
		}

		class GantryLegacyJController extends JController
		{
		}

		class GantryLegacyJModel extends JModel
		{
		}
	}
}

if (method_exists('JSession','checkToken')) {
	function gantry_checktoken($method = 'post')
	{
		if ($method == 'default')
		{
			$method = 'request';
		}
		return JSession::checkToken($method);
	}
} else {
	function gantry_checktoken($method = 'post')
	{
		return JRequest::checkToken($method);
	}
}

 
