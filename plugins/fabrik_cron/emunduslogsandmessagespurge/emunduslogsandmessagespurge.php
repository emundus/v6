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
		$m_logs     = new EmundusModelLogs();
		$m_messages = new EmundusModelMessages();

		$params      = $this->getParams();
		$amount_time = $params->get('amount_time');
		$unit_time   = $params->get('unit_time');
		$export      = $params->get('export_zip');

		$now = $this->getDate($amount_time, $unit_time);

		if ($export)
		{
			$zip_filename = JPATH_SITE . '/tmp/backup_logs_and_messages_' . date('Y-m-d_H-i-s') . '.zip';
			$zip          = new ZipArchive();
			if ($zip->open($zip_filename, ZipArchive::CREATE) === true)
			{
				$filename_logs     = $m_logs->exportLogsBeforeADate($now);
				$filename_messages = $m_messages->exportMessagesBeforeADate($now);
				$zip->addFile($filename_logs, basename($filename_logs));
				$zip->addFile($filename_messages, basename($filename_messages));
				$zip->close();
				unlink($filename_messages);
				unlink($filename_logs);
			}
		}

		$logs     = $m_logs->deleteLogsBeforeADate($now);
		$messages = $m_messages->deleteMessagesBeforeADate($now);
		$tmp_documents = 0;

		// Clean tmp documents older than $now
		foreach (glob(JPATH_SITE . '/tmp/*') as $document)
		{
			if (!preg_match('/^backup_logs_and_messages_[a-zA-Z0-9_-]+\.zip$/', basename($document)) && basename($document) !== '.gitignore' && basename($document) !== 'index.html')
			{
				$creation_date_time = new DateTime('@' . filectime($document));
				if ($creation_date_time < $now)
				{
					if (is_file($document))
					{
						unlink($document);
						$tmp_documents += 1;
					}
					else if (is_dir($document))
					{
						$files = glob($document . '/*');
						foreach ($files as $file)
						{
							unlink($file);
						}
						rmdir($document);
						$tmp_documents += 1;
					}
				}
			}
		}

		return $logs + $messages + $tmp_documents;
	}

	private function getDate($amount_time, $unit_time)
	{
		if (version_compare(JVERSION, '4.0', '>='))
		{
			$config = Factory::getApplication()->getConfig();
		}
		else
		{
			$config = Factory::getConfig();
		}
		$offset = $config->get('offset', 'Europe/Paris');
		$now    = DateTime::createFromFormat('Y-m-d H:i:s', EmundusHelperDate::getNow($offset));

		switch ($unit_time)
		{
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

		return $now;
	}
}
