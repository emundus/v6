<?php
/**
 * A cron task to purge database logs and messages older than a certain time
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2024 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to purge database logs and messages older than a certain time
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emunduslogsandmessagespurge
 * @since       3.0
 */

class PlgFabrik_Cronemunduslogsandmessagespurge extends PlgFabrik_Cron{

    /**
	 * Check if the user can use the plugin
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
     *
     * @since 6.9.3
	 */
	public function canUse($location = null, $event = null){
		return true;
	}


    /**
     * Do the plugin action
     *
     * @param   array  &$data data
     *
     * @return  int  Number of rows deleted (logs + messages)
     *
     * @since 6.9.3
     * @throws Exception
     */
	public function process(&$data, &$listModel)
	{
		include_once(JPATH_SITE . '/components/com_emundus/models/logs.php');
		include_once(JPATH_SITE . '/components/com_emundus/models/messages.php');
		include_once(JPATH_SITE . '/components/com_emundus/helpers/date.php');
		$m_logs = new EmundusModelLogs();
		$m_messages = new EmundusModelMessages();

		$params = $this->getParams();
		$amount_time = $params->get('amount_time');
		$unit_time = $params->get('unit_time');
		$export = $params->get('export_zip');

		if (version_compare(JVERSION, '4.0', '>=')) {
			$config = Factory::getApplication()->getConfig();
		} else {
			$config = Factory::getConfig();
		}
		$offset = $config->get('offset', 'Europe/Paris');
		$now = DateTime::createFromFormat('Y-m-d H:i:s', EmundusHelperDate::getNow($offset));

		switch ($unit_time) {
			case 'hour':
				$now->modify('-' . $amount_time . ' hours');
				break;
			case 'day':
				$now->modify('-' . $amount_time . ' days');
				break;
			case 'week':
				$now->modify('-' . $amount_time . ' weeks');
				break;
			case 'month':
				$now->modify('-' . $amount_time . ' months');
				break;
			case 'year':
				$now->modify('-' . $amount_time . ' years');
				break;
		}

		$logs = $m_logs->deleteLogsAfterADate($now, $export);
		$messages = $m_messages->deleteMessagesAfterADate($now, $export);

		$zip_filename = JPATH_SITE . '/tmp/logs_and_messages_' . date('Y-m-d_H-i-s') . '.zip';
		$zip = new ZipArchive();
		if ($export && $zip->open($zip_filename, ZipArchive::CREATE) === TRUE) {
			$zip->addFile($logs['csvFilename'], basename($logs['csvFilename']));
			$zip->addFile($messages['csvFilename'], basename($messages['csvFilename']));
			$zip->close();
			unlink($messages['csvFilename']);
			unlink($logs['csvFilename']);
		}
		return $logs['deletedLogs'] + $messages['deletedMessages'];
	}

}
