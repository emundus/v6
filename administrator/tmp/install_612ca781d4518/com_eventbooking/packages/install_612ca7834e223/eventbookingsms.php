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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

class plgSystemEventbookingSms extends CMSPlugin
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
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array    $config
	 */
	public function __construct(&$subject, $config = [])
	{
		if (!file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Generate invoice number after registrant complete payment for registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 *
	 * @return bool
	 */
	public function onAfterPaymentSuccess($row)
	{
		// Workaround to prevent listening to event trigger with same name (from our other extensions)
		if (!property_exists($row, 'event_id')
			|| !property_exists($row, 'group_id')
			|| !property_exists($row, 'first_sms_reminder_sent'))
		{
			return;
		}

		if ($row->group_id == 0 && strpos($row->payment_method, 'os_offline') === false)
		{
			$this->sendSMSMessageToAdmin($row);
		}
	}

	/**
	 * Generate invoice number after registrant complete registration in case he uses offline payment
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	public function onAfterStoreRegistrant($row)
	{
		if ($row->group_id == 0 && (strpos($row->payment_method, 'os_offline') !== false))
		{
			$this->sendSMSMessageToAdmin($row);
		}
	}

	/**
	 * Method to send SMS message to administrator
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	private function sendSMSMessageToAdmin($row)
	{
		$phones = $this->params->get('phones');

		if (!$phones)
		{
			return;
		}

		$message = EventbookingHelper::getMessages();

		if (!trim($message->new_registration_admin_sms))
		{
			return;
		}

		$phones = explode(',', $phones);
		$phones = array_filter($phones);

		if (!count($phones))
		{
			return;
		}

		$row = clone $row;

		// Get extra data for the registration record
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);

		// Admin does not allow sending SMS, stop
		if (!$event->enable_sms_reminder)
		{
			return;
		}

		$row->event_title    = $event->title;
		$row->event_date     = $event->event_date;
		$row->event_end_date = $event->event_end_date;

		if ($event->location_id)
		{
			$location = EventbookingHelperDatabase::getLocation($event->location_id, $fieldSuffix);

			$row->location_name    = $location->name;
			$row->location_address = $location->address;
		}
		else
		{
			$row->location_name = $row->location_address = '';
		}

		$admins = [];

		foreach ($phones as $phone)
		{
			$admin = clone $row;

			$admin->phone = $phone;

			$smsMessage = trim($message->new_registration_admin_sms);

			$replaces = $this->buildTags($admin);

			foreach ($replaces as $key => $value)
			{
				$smsMessage = str_ireplace('[' . $key . ']', $value, $smsMessage);
			}

			$admin->sms_message = $smsMessage;

			$admins[] = $admin;
		}

		// Trigger
		if (count($admins))
		{
			PluginHelper::importPlugin('eventbookingsms');

			$this->app->triggerEvent('onEBSendingSMSReminder', [$admins]);
		}
	}

	/**
	 * Handle onAfterRespond event to send SMS reminder
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

		if (!EventbookingHelperPlugin::checkAndStoreLastRuntime($this->params, $cacheTime, $this->_name))
		{
			return;
		}

		if (!$this->canRun())
		{
			return;
		}

		// Send first reminder
		$this->sendSMSReminder(1);

		// Send second reminder
		$this->sendSMSReminder(2);
	}

	/**
	 * Method to send sms reminder to registrants
	 */
	private function sendSMSReminder($number)
	{
		if (!in_array($number, [1, 2]))
		{
			return;
		}

		switch ($number)
		{
			case 1:
				$smsMessageField   = 'first_reminder_sms';
				$sendReminderField = 'b.send_first_reminder';
				$reminderSentField = 'a.first_sms_reminder_sent';
				break;
			default:
				$smsMessageField   = 'second_reminder_sms';
				$sendReminderField = 'b.send_second_reminder';
				$reminderSentField = 'a.second_sms_reminder_sent';
				break;
		}

		$db      = $this->db;
		$message = EventbookingHelper::getMessages();

		// Stop processing it further if the sms message is not configured
		if (!trim($message->{$smsMessageField}))
		{
			return;
		}

		$now                     = $db->quote(Factory::getDate('now', Factory::getApplication()->get('offset'))->toSql(true));
		$numberEmailSendEachTime = (int) $this->params->get('number_registrants', 0) ?: 15;

		$query = $db->getQuery(true)
			->select('a.*')
			->select('b.title AS event_title, b.event_date, b.event_end_date')
			->select('l.name AS location_name, l.address AS location_address')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->leftJoin('#__eb_locations AS l ON b.location_id = l.id')
			->where('b.enable_sms_reminder = 1')
			->where("$reminderSentField = 0")
			->where("$sendReminderField != 0")
			->where("IF($sendReminderField > 0, $sendReminderField >= DATEDIFF(b.event_date, $now) AND DATEDIFF(b.event_date, $now) >= 0, DATEDIFF($now, b.event_date) >= ABS($sendReminderField) AND DATEDIFF($now, b.event_date) <= 40)")
			->order('b.event_date, a.register_date');

		$this->filterRegistrants($query);

		$db->setQuery($query, 0, $numberEmailSendEachTime);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (Exception  $e)
		{
			$rows = [];
		}

		if (!count($rows))
		{
			return;
		}

		$ids = [];

		foreach ($rows as $row)
		{
			$ids[] = $row->id;

			if (!$row->phone)
			{
				continue;
			}

			$smsMessage = $message->{$smsMessageField};

			$replaces = $this->buildTags($row);

			foreach ($replaces as $key => $value)
			{
				$smsMessage = str_ireplace('[' . $key . ']', $value, $smsMessage);
			}

			$row->sms_message = $smsMessage;
		}

		PluginHelper::importPlugin('eventbookingsms');

		$result = $this->app->triggerEvent('onEBSendingSMSReminder', [$rows]);

		if (in_array(true, $result, true))
		{
			$query->clear()
				->update('#__eb_registrants AS a')
				->set("$reminderSentField = 1")
				->where('id IN (' . implode(',', $ids) . ')');

			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Method to build tags for replacing in SMS message
	 *
	 * @param   EventbookingTableRegistrant  $row
	 *
	 * @return array
	 */
	private function buildTags($row)
	{
		$config     = EventbookingHelper::getConfig();
		$timeFormat = $config->event_time_format ?: 'g:i a';
		$nullDate   = $this->db->getNullDate();

		$replaces = [];

		$replaces['event_id']    = $row->event_id;
		$replaces['event_title'] = $row->event_title;

		if ($row->event_date == EB_TBC_DATE)
		{
			$replaces['event_date']      = Text::_('EB_TBC');
			$replaces['event_date_date'] = Text::_('EB_TBC');
			$replaces['event_date_time'] = Text::_('EB_TBC');
		}
		else
		{
			$replaces['event_date']      = HTMLHelper::_('date', $row->event_date, $config->event_date_format, null);
			$replaces['event_date_date'] = HTMLHelper::_('date', $row->event_date, $config->date_format, null);
			$replaces['event_date_time'] = HTMLHelper::_('date', $row->event_date, $timeFormat, null);
		}

		if ($row->event_end_date != $nullDate)
		{
			$replaces['event_end_date']      = HTMLHelper::_('date', $row->event_end_date, $config->event_date_format, null);
			$replaces['event_end_date_date'] = HTMLHelper::_('date', $row->event_end_date, $config->date_format, null);
			$replaces['event_end_date_time'] = HTMLHelper::_('date', $row->event_end_date, $timeFormat, null);
		}
		else
		{
			$replaces['event_end_date']      = '';
			$replaces['event_end_date_date'] = '';
			$replaces['event_end_date_time'] = '';
		}

		$replaces['location_name']    = $row->location_name;
		$replaces['location_address'] = $row->location_address;

		$fields = [
			'first_name',
			'last_name',
			'organization',
			'address',
			'address2',
			'city',
			'zip',
			'state',
			'country',
			'phone',
			'fax',
			'email',
			'comment',
		];

		foreach ($fields as $field)
		{
			$replaces[$field] = $row->{$field};
		}

		return $replaces;
	}

	/**
	 * Apply filter to query to return list of registrants base on parameters configured for the plugin
	 *
	 * @param   JDatabaseQuery  $query
	 */
	private function filterRegistrants($query)
	{
		$params = $this->params;

		if (!$params->get('send_to_group_billing', 1))
		{
			$query->where('a.is_group_billing = 0');
		}

		if (!$params->get('send_to_group_members', 1))
		{
			$query->where('a.group_id = 0');
		}

		if (!$params->get('send_to_unpublished_events', 0))
		{
			$query->where('b.published = 1');
		}

		if ($params->get('only_send_to_checked_in_registrants', 0))
		{
			$query->where('a.checked_in = 1');
		}

		if ($params->get('only_send_to_paid_registrants', 0))
		{
			$query->where('a.published = 1');
		}
		else
		{
			$query->where('(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published = 0))');
		}
	}

	/**
	 * Method to check whether this plugin should be run
	 *
	 * @return bool
	 */
	private function canRun()
	{
		// Process sending reminder on every page load if debug mode enabled
		if ($this->params->get('debug', 0))
		{
			return true;
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
