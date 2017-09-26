<?php
    defined('_JEXEC') or die('Access Deny');

    require_once __DIR__ .DS.'php-google-api-client'.DS.'vendor'.DS.'autoload.php';
    define('APPLICATION_NAME', 'Google Calendar API PHP Emundus');
    define('CREDENTIALS_PATH', __DIR__ .DS.'php-google-api-client'.DS.'credentials'.DS.'calendar-php-quickstart.json');
    define('CLIENT_SECRET_PATH', __DIR__ .DS.'php-google-api-client'.DS.'certificates'.DS.'client_secret.json');

    define('SCOPES', implode(' ', array(
        Google_Service_Calendar::CALENDAR) // CALENDAR_READONLY
    ));


    class modEmundusCalendarAddHelper {

        public function __construct() {
            
        }

        public function google_authenticate($clientID, $clientSecret, $refreshToken) {
            
            // Get the API client and construct the service object.
            $client = $this->getClient($clientID, $clientSecret);
            $client->refreshToken($refreshToken);
            $service = new Google_Service_Calendar($client);

            
        
        }

    }
    
?>