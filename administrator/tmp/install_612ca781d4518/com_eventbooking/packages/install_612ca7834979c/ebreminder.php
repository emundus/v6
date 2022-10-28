<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class plgSystemEBReminder extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array    $config   An optional associative array of configuration settings.
	 *
	 */
	public function __construct(&$subject, $config = array())
	{
		if (!file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Send reminder to registrants
	 *
	 * @return void
	 * @throws Exception
	 */
	public function onAfterRespond()
	{
		if (!$this->app)
		{
			return;
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		$cacheTime = (int) $this->params->get('cache_time', 20) * 60; // 60 minutes

		if (!$this->canRun())
		{
			return;
		}

		// We only need to check and store last runtime if cron job is not configured
		if (!$this->params->get('trigger_reminder_code')
			&& !EventbookingHelperPlugin::checkAndStoreLastRuntime($this->params, $cacheTime, $this->_name))
		{
			return;
		}

		$bccEmail                = $this->params->get('bcc_email', '');
		$numberEmailSendEachTime = (int) $this->params->get('number_registrants', 0);

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		// Send first reminder
		EventbookingHelper::callOverridableHelperMethod('Mail', 'sendReminder', [$numberEmailSendEachTime, $bccEmail, $this->params]);

		// Send second reminder
		EventbookingHelper::callOverridableHelperMethod('Mail', 'sendSecondReminder', [$numberEmailSendEachTime, $bccEmail, $this->params]);
	}

	/**
	 * Method to check whether this plugin should be run
	 *
	 * @return bool
	 */
	private function canRun()
	{
		if (!$this->app)
		{
			return false;
		}

		// If trigger reminder code is set, we will only process sending reminder from cron job
		if (trim($this->params->get('trigger_reminder_code')))
		{
			if ($this->params->get('trigger_reminder_code') == $this->app->input->getString('trigger_reminder_code'))
			{
				return true;
			}

			return false;
		}

		// If time ranges is set and current time is not within these specified ranges, we won't process sending reminder
		if ($this->params->get('time_ranges'))
		{
			$withinTimeRage = false;
			$date           = Factory::getDate('Now', Factory::getApplication()->get('offset'));
			$currentHour    = $date->format('G', true);
			$timeRanges     = explode(';', $this->params->get('time_ranges'));// Time ranges format 6,10;14,20

			foreach ($timeRanges as $timeRange)
			{
				if (strpos($timeRange, ',') == false)
				{
					continue;
				}

				list($fromHour, $toHour) = explode(',', $timeRange);

				if ($fromHour <= $currentHour && $toHour >= $currentHour)
				{
					$withinTimeRage = true;
					break;
				}
			}

			if (!$withinTimeRage)
			{
				return false;
			}
		}

		return true;
	}
}
