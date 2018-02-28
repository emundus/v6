<?php

/**
 * @version     $Id: calendar.php 750 2012-01-23 22:29:38Z hmoracchini $
 * @package     Joomla
 * @copyright   (C) 2017 eMundus LLC. All rights reserved.
 * @license     GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'calendar.php');

/**
 * Custom report controller
 * @package     Emundus
 */

class EmundusControllerCalendar extends JControllerLegacy {

    /**
    * Creates a calendar using the google API and by manually creating a category for dpcalendar to use.
    *
    * @return bool
    */
    public function createcalendar() {

        $m_calendar = new EmundusModelCalendar();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $jinput = JFactory::getApplication()->input;

        // Calendar information is obtained via the post data
        $calendar_title     = $jinput->get("calTitle", null, "string");
        $calendar_program   = $jinput->get("calProgram", null, "string");
        $calendar_color     = $jinput->get("calColor", null, "string");
        $calendar_alias     = str_replace(' ', '-', $calendar_title);

        // Google API info is obtained via the module params
        $google = $eMConfig->get('useGoogle');

        if ($google == 1) {
            $google_client_id       = $eMConfig->get('clientId');
            $google_secret_key      = $eMConfig->get('clientSecret');
            $google_refresh_token   = $eMConfig->get('refreshToken');
        }
        $dpcalendar_parent_id   = $eMConfig->get('parentCalId');

        if ($google == 1) {
            $service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);
            $google_calendar_id = $m_calendar->google_add_calendar($service, $calendar_title);
        } else {
            $google_calendar_id = '';
        }

        $m_calendar->dpcalendar_add_calendar($calendar_title, $calendar_alias, $calendar_color, $google_calendar_id, $dpcalendar_parent_id, $calendar_program);

        echo json_encode(['status' => true]);

    }

    public function bookinterview() {

        $m_calendar = new EmundusModelCalendar();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $jinput = JFactory::getApplication()->input;

        $event_id       = $jinput->get('eventId', null, 'string');
        $user_id        = $jinput->get('userId', null, 'string');
        $fnum           = $jinput->get('fnum', null, 'string');
        $contact_info   = $jinput->get('contactInfo', null, 'array');

        $google = $eMConfig->get('useGoogle');

        if ($google == 1) {
            $google_client_id       = $eMConfig->get('clientId');
            $google_secret_key      = $eMConfig->get('clientSecret');
            $google_refresh_token   = $eMConfig->get('refreshToken');
        }

        $dpcalendar_event = $m_calendar->dpcalendar_confirm_interview($event_id, $user_id, $fnum, $contact_info);

        $result = true;

        if ($google == 1) {
            $service    = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);
            $result     = $m_calendar->google_add_event($service, $dpcalendar_event);
        }

        $m_calendar->email_event($event_id, true);

        echo json_encode(['status' => $result]);

    }

    public function cancelinterview() {

        $m_calendar = new EmundusModelCalendar();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $jinput = JFactory::getApplication()->input;
        $event_id = $jinput->get("eventId", null, "string");

        $google = $eMConfig->get('useGoogle');

        if ($google == 1) {
            $google_client_id       = $eMConfig->get('clientId');
            $google_secret_key      = $eMConfig->get('clientSecret');
            $google_refresh_token   = $eMConfig->get('refreshToken');

            $service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);
            $m_calendar->google_delete_event($event_id, $service);
        }

        $result = $m_calendar->dpcalendar_delete_interview($event_id);

        $m_calendar->email_event($event_id, false);

        echo json_encode(['status' => $result]);

    }


    public function createtimeslots() {

        $m_calendar = new EmundusModelCalendar();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $jinput = JFactory::getApplication()->input;

        // Information about the timeslots to create is obtained through the POST data.
        $calendar_id    = $jinput->get('calId', null, 'INT');
        $start_date     = $jinput->get('sDate', null, 'STR');
        $end_date       = $jinput->get('eDate', null, 'STR');
        $start_time     = $jinput->get('sTime', null, 'STR');
        $end_time       = $jinput->get('eTime', null, 'STR');
        $ts_length      = $jinput->get('tsLength', 50, 'INT');
        $pause_length   = $jinput->get('pLength', 10, 'INT');

        // Convert to PHP date object.
        $start_date = date('Y-M-d', strtotime($start_date));
        $end_date   = date('Y-M-d', strtotime($end_date));
        $now_date   = date('Y-m-d');

        // Check if the dates selected are in the future.
        if ($now_date > $start_date || $now_date > $end_date) {
            echo json_encode([
                'status' => false,
                'error' => JText::_('CALENDAR_DATES_TOO_EARLY')
            ]);
            die;
        }

        // Check that the start date is before the end date.
        if ($start_date > $end_date) {
            echo json_encode([
                'status' => false,
                'error' => JText::_('CALENDAR_END_DATE_TOO_EARLY')
            ]);
            die;
        }

        // Convert time to PHP date object.
        $start_time = date('H:i', strtotime($start_time));
        $end_time   = date('H:i', strtotime($end_time));


        // Check that there is at least enough room for an entire time slot per day.
        $start_time_with_timeslot = $start_time;
        $start_time_with_timeslot->modify("+{$ts_length} minutes");
        if ($start_time_with_timeslot > $end_time) {
            echo json_encode([
                'status' => false,
                'error' => JText::_('CALENDAR_END_TIME_TOO_EARLY')
            ]);
            die;
        }

        $date = $start_date;
        do {

            // Reset time to the beggining
            $time = $start_time;

            do {

                // Check that there is at least enough room for an entire time slot.
                $time_with_timeslot = $time;
                $time_with_timeslot->modify("+{$ts_length} minutes");

                if ($time_with_timeslot > $end_time)
                    break;

                // Make a timeslot at the time selected of the defined length.
                $m_calendar->createTimeslot($calendar_id, $date, $time, $ts_length);

                // Edit time to take into account the length of the timeslot as well as the break in between.
                $time->modify("+{$ts_length} minutes");
                $time->modify("+{$pause_length} minutes");

            } while ($time <= $end_time);

            // Add a date to the date.
            $date->modify('+1 day');

        } while ($date <= $end_date);

        echo json_encode(['status' => true]);

    }

}