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
        $google_client_id       = $eMConfig->get('clientId');
        $google_secret_key      = $eMConfig->get('clientSecret');
        $google_refresh_token   = $eMConfig->get('refreshToken');
        $dpcalendar_parent_id   = $eMConfig->get('parentCalId');

        $service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);

        $google_calendar_id = $m_calendar->google_add_calendar($service, $calendar_title);

        $m_calendar->dpcalendar_add_calendar($calendar_title, $calendar_alias, $calendar_color, $google_calendar_id, $dpcalendar_parent_id, $calendar_program);

        echo json_encode(['status' => true]);

    }

    public function bookinterview() {

        $m_calendar = new EmundusModelCalendar();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $jinput = JFactory::getApplication()->input;

        $event_id   = $jinput->get("eventId", null, "string");
        $user_id    = $jinput->get("userId", null, "string");
        $fnum       = $jinput->get("fnum", null, "string");

        $google_client_id       = $eMConfig->get('clientId');
        $google_secret_key      = $eMConfig->get('clientSecret');
        $google_refresh_token   = $eMConfig->get('refreshToken');

        $dpcalendar_event = $m_calendar->dpcalendar_confirm_interview($event_id[0], $user_id, $fnum);

        $service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);

        $result = $m_calendar->google_add_event($service, $dpcalendar_event);

        if ($result)
            $result = $m_calendar->email_event($event_id[0], true);

        echo json_encode(['status' => $result]);

    }

    public function cancelinterview() {

        $m_calendar = new EmundusModelCalendar();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $jinput = JFactory::getApplication()->input;
        $event_id = $jinput->get("eventId", null, "string");

        $google_client_id       = $eMConfig->get('clientId');
        $google_secret_key      = $eMConfig->get('clientSecret');
        $google_refresh_token   = $eMConfig->get('refreshToken');

        $service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);
        $m_calendar->google_delete_event($event_id, $service);
        $result = $m_calendar->dpcalendar_delete_interview($event_id);

        if ($result)
            $result = $m_calendar->email_event($event_id, false);

        echo json_encode(['status' => $result]);

    }

}