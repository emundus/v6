<?php
/**
 * A cron task to purge applicative logs older than a certain time
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to purge logs older than a certain time
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emunduslogspurge
 * @since       3.0
 */

class PlgFabrik_Cronemunduslogspurge extends PlgFabrik_Cron{

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
     * @return  int  number of logs deleted
     *
     * @since 6.9.3
     * @throws Exception
     */
	public function process(&$data, &$listModel)
	{
		include_once(JPATH_SITE . '/components/com_emundus/models/logs.php');
		include_once(JPATH_SITE . '/components/com_emundus/helpers/date.php');
		$m_logs = new EmundusModelLogs();

		$params      = $this->getParams();
		$amount_time = $params->get('amount_time');
		$unit_time   = $params->get('unit_time');

		$now = DateTime::createFromFormat('Y-m-d H:i:s', EmundusHelperDate::getNow());

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

		return $m_logs->deleteLogsAfterADate($now);
	}
}
