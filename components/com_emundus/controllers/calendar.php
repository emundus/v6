<?php

/**
 * @version         $Id: calendar.php 750 2012-01-23 22:29:38Z hmoracchini $
 * @package         Joomla
 * @copyright   (C) 2017 eMundus LLC. All rights reserved.
 * @license         GNU General Public License
 */

// ensure this file is being included by a parent file
defined('_JEXEC') or die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

use Joomla\CMS\Factory;


/**
 * Custom report controller
 * @package     Emundus
 */
class EmundusControllerCalendar extends JControllerLegacy
{
	private $_user;
	protected $app;

	public function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');

		$this->app   = Factory::getApplication();
		$this->_user = $this->app->getIdentity();

		parent::__construct($config);
	}

	/**
	 * Creates a calendar using the google API and by manually creating a category for dpcalendar to use.
	 *
	 * @throws Exception
	 */
	public function createcalendar()
	{

		$m_calendar = $this->getModel('Calendar');
		$eMConfig   = JComponentHelper::getParams('com_emundus');

		// Calendar information is obtained via the post data
		$calendar_title   = $this->input->get("calTitle", null, "string");
		$calendar_program = $this->input->get("calProgram", null, "string");
		$calendar_color   = $this->input->get("calColor", null, "string");
		$calendar_alias   = str_replace(' ', '-', $calendar_title);

		// Google API info is obtained via the module params
		$google = $eMConfig->get('useGoogle');

		if ($google == 1) {
			$google_client_id     = $eMConfig->get('clientId');
			$google_secret_key    = $eMConfig->get('clientSecret');
			$google_refresh_token = $eMConfig->get('refreshToken');
		}
		$dpcalendar_parent_id = $eMConfig->get('parentCalId');

		if ($google == 1) {
			$service            = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);
			$google_calendar_id = $m_calendar->google_add_calendar($service, $calendar_title);
		}
		else {
			$google_calendar_id = '';
		}

		echo json_encode(['status' => $m_calendar->dpcalendar_add_calendar($calendar_title, $calendar_alias, $calendar_color, $google_calendar_id, $dpcalendar_parent_id, $calendar_program)]);
	}

	/**
	 * @throws Exception
	 */
	public function bookinterview()
	{

		$m_calendar = $this->getModel('Calendar');
		$eMConfig   = JComponentHelper::getParams('com_emundus');

		$event_id     = $this->input->get('eventId', null, 'string');
		$user_id      = $this->input->get('userId', null, 'string');
		$fnum         = $this->input->get('fnum', null, 'string');
		$contact_info = $this->input->get('contactInfo', null, 'array');

		$google = $eMConfig->get('useGoogle');

		if ($google == 1) {
			$google_client_id     = $eMConfig->get('clientId');
			$google_secret_key    = $eMConfig->get('clientSecret');
			$google_refresh_token = $eMConfig->get('refreshToken');
		}

		$dpcalendar_event = $m_calendar->dpcalendar_confirm_interview($event_id, $user_id, $fnum, $contact_info);

		$result = true;

		if ($google == 1) {
			$service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);
			$result  = $m_calendar->google_add_event($service, $dpcalendar_event);
		}

		$m_calendar->email_event($event_id, true);

		echo json_encode(['status' => $result]);

	}

	public function cancelinterview()
	{

		$m_calendar = $this->getModel('Calendar');
		$eMConfig   = JComponentHelper::getParams('com_emundus');

		$event_id = $this->input->get("eventId", null, "string");

		$google = $eMConfig->get('useGoogle');

		if ($google == 1) {
			$google_client_id     = $eMConfig->get('clientId');
			$google_secret_key    = $eMConfig->get('clientSecret');
			$google_refresh_token = $eMConfig->get('refreshToken');

			$service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);
			$m_calendar->google_delete_event($event_id, $service);
		}

		$result = $m_calendar->dpcalendar_delete_interview($event_id);

		$m_calendar->email_event($event_id, false);

		echo json_encode(['status' => $result]);
	}


	public function createtimeslots()
	{

		$m_calendar = $this->getModel('Calendar');

		// Information about the timeslots to create is obtained through the POST data.
		$calendar_id  = $this->input->get('calId', null, 'INT')[0];
		$start_day    = $this->input->get('sDate', null, 'STR')[0];
		$end_day      = $this->input->get('eDate', null, 'STR')[0];
		$start_time   = $this->input->get('sTime', null, 'STR')[0];
		$end_time     = $this->input->get('eTime', null, 'STR')[0];
		$ts_length    = $this->input->get('tsLength', 50, 'INT')[0];
		$pause_length = $this->input->get('pLength', 10, 'INT')[0];
		$today        = date('Y-m-d');


		if ($calendar_id == 0) {
			echo json_encode([
				'status' => false,
				'error'  => JText::_('NO_CALENDAR')
			]);
			die;
		}


		// Check if the days selected are in the future.
		if ($today > $start_day || $today > $end_day) {
			echo json_encode([
				'status' => false,
				'error'  => JText::_('CALENDAR_DATES_TOO_EARLY')
			]);
			die;
		}

		// Check that the start day is before the end day.
		if ($start_day > $end_day) {
			echo json_encode([
				'status' => false,
				'error'  => JText::_('CALENDAR_END_DATE_TOO_EARLY')
			]);
			die;
		}

		// Convert time to PHP date object.
		$start_time = new DateTime($start_time);
		$end_time   = new DateTime($end_time);

		// Check that there is at least enough room for an entire time slot in a day.
		$start_time_with_timeslot = new DateTime($start_time->format('H:i'));
		$start_time_with_timeslot->modify("+{$ts_length} minutes");
		if ($start_time_with_timeslot > $end_time) {
			echo json_encode([
				'status' => false,
				'error'  => JText::_('CALENDAR_END_TIME_TOO_EARLY')
			]);
			die;
		}

		// Build a single start date from both the start time and and the start date.
		$current_date = new DateTime($start_day);
		$current_date->modify("+ " . $start_time->format('H') . " hours");
		$current_date->modify("+ " . $start_time->format('i') . " minutes");
		$start_date = new DateTime($current_date->format('Y-m-d H:i'));

		// Build a single end date from the end time and end date.
		$end_date = new DateTime($end_day);
		$end_date->modify("+ " . $end_time->format('H') . " hours");
		$end_date->modify("+ " . $end_time->format('i') . " minutes");

		$nb_days     = 0;
		$start_dates = array();
		$end_dates   = array();
		while ($current_date < $end_date) {

			// Check that there is at least enough room for an entire time slot.
			$time_with_timeslot = new DateTime($current_date->format('Y-m-d H:i'));
			$time_with_timeslot->modify("+{$ts_length} minutes");

			// If the next timeslot will cause the day to overflow then we should just move on to the next day.
			// Thins also means reseting the hours and minutes.
			if ($time_with_timeslot->format('H:i') > $end_date->format('H:i')) {

				// Reset the date back to the beggining, this helps reset hours and minutes.
				$current_date = new DateTime($start_date->format('Y-m-d H:i'));

				// If it's a Friday, skip the weekend.
				if ($current_date->format('w') == 5) {
					$nb_days = $nb_days + 3;
				}
				else {
					$nb_days++;
				}

				// Move the current date to the next day.
				$current_date->modify('+' . $nb_days . ' day');
				continue;

			}

			// Add the current timedate to the list of start dates.
			$start_dates[] = new DateTime($current_date->format('Y-m-d H:i'));

			// Move the date up for the duration of the even times.
			$current_date->modify("+{$ts_length} minutes");

			// Current time is now at the event end.
			$end_dates[] = new DateTime($current_date->format('Y-m-d H:i'));

			// Add the buffer time in between events.
			$current_date->modify("+{$pause_length} minutes");

		}

		$result = $m_calendar->createTimeslots($calendar_id, $start_dates, $end_dates);

		echo json_encode(['status' => $result]);

	}


	/**
	 * Used for first activation with the calendar.
	 */
	public function authenticateclient()
	{
		$m_calendar = $this->getModel('Calendar');

		$m_calendar->authenticateClient();

		$this->app->redirect('/index.php');
	}

}
