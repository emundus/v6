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
            
            try {
                
                $db = JFactory::getDbo();
                $db->setQuery('SELECT code, label FROM #__emundus_setup_programmes');
                return $db->loadObjectList();
            
            } catch (Exception $e) {
                die($e->getMessage());
            }
        
        }

    }
    
?>