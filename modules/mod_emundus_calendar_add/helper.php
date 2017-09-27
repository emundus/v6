<?php
    defined('_JEXEC') or die('Access Deny');

    require_once(JPATH_SITE.DS."components".DS."com_emundus".DS."models".DS."php-google-api-client".DS."vendor".DS.'autoload.php');
    define('APPLICATION_NAME', 'Google Calendar API PHP Emundus');
    define('CREDENTIALS_PATH', JPATH_SITE.DS."components".DS."com_emundus".DS."models".DS.'php-google-api-client'.DS.'credentials'.DS.'calendar-php-quickstart.json');
    define('CLIENT_SECRET_PATH', JPATH_SITE.DS."components".DS."com_emundus".DS."models".DS.'php-google-api-client'.DS.'certificates'.DS.'client_secret.json');

    define('SCOPES', implode(' ', array(
        Google_Service_Calendar::CALENDAR) // CALENDAR_READONLY
    ));


    class modEmundusCalendarAddHelper {

        public function getPrograms() {
            
            $db = JFactory::getDbo();
            $db->setQuery('SELECT code, label FROM #__emundus_setup_programmes');
            return $db->loadObjectList();
        
        }

        public function google_authenticate($clientID, $clientSecret, $refreshToken) {
            
            // Get the API client and construct the service object.
            $client = $this->getClient($clientID, $clientSecret);
            $client->refreshToken($refreshToken);
            $service = new Google_Service_Calendar($client);

            return $service;
        
        }


        public function google_add_calendar($google_api_service, $title) {

            // Creates a new google calendar using the API.
            $calendar = new Google_Service_Calendar_Calendar();
            $calendar->setSummary($title);
            $calendar->setTimeZone('Europe/Paris');
            $createdCalendar = $google_api_service->calendars->insert($calendar);  
            
            return $createdCalendar->getId();

        }

    }
    
?>