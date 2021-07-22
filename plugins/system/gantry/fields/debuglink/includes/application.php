<?php
/**
 * @version   $Id: application.php 5317 2012-11-20 23:03:43Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2020 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('JPATH_PLATFORM') or die;

final class JAdministrator extends JApplication
{
	public function initialise($options = array())
	{
	}

	/**
	 * Display the application.
	 */
	public function render()
	{
		$user = JFactory::getUser();
		$conf = JFactory::getConfig();

		if ($user->guest || !JFactory::getUser()->authorise('core.admin', 'com_cache')) {
			//TODO change this to the the proper access acl
			echo JText::_('Unauthorized');
			exit;
		}

		$logfile = $this->input->getPath('logfile', null);

		if (is_null($logfile)) {
			echo JText::_('Log not found.');
			exit;
		}

		$logfile_path = JPATH_ROOT . '/logs/' . $logfile;

		if (!is_file($logfile_path)) {
			echo JText::_('Log not found.');
			exit;
		}
		JResponse::clearHeaders();
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.pathinfo($logfile,PATHINFO_FILENAME).'.log');
		header('Content-Length: '.filesize($logfile_path) . ';');
		header('Content-Transfer-Encoding: binary');
		readfile($logfile_path);
		exit;
	}
}