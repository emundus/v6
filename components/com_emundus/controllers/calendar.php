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
        
        // Calendar information is obtained via the post data
        $calendar_title     = $_POST["calTitle"];
        $calendar_program   = $_POST["calProgram"];
        $calendar_color     = $_POST["calColor"];
        $calendar_alias     = str_replace(' ', '-', $calendar_title);

        // Google API info is obtained via the module params
        // TODO: Get google API info from dpcalendar params instead
        $google_client_id       = $eMConfig->get('clientId');
        $google_secret_key      = $eMConfig->get('clientSecret');
        $google_refresh_token   = $eMConfig->get('refreshToken');
        $dpcalendar_parent_id   = $eMConfig->get('parentCalId');

        $service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);

        $google_calendar_id = $m_calendar->google_add_calendar($service, $calendar_title);

        $result = $m_calendar->dpcalendar_add_calendar($calendar_title, $calendar_alias, $calendar_color, $google_calendar_id, $dpcalendar_parent_id, $calendar_program);

        echo json_encode(['status' => $result]);
    
    }

    public function bookinterview() {

        $m_calendar = new EmundusModelCalendar();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $event_id = $_POST["eventId"][0];
        $user_id  = $_POST["userId"][0];

        $google_client_id       = $eMConfig->get('clientId');
        $google_secret_key      = $eMConfig->get('clientSecret');
        $google_refresh_token   = $eMConfig->get('refreshToken');

        $dpcalendar_event = $m_calendar->dpcalendar_confirm_interview($event_id, $user_id); 

        $service = $m_calendar->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);

        $result = $m_calendar->google_add_event($service, $dpcalendar_event);

        echo json_encode(['status' => $result]);

    }

}  