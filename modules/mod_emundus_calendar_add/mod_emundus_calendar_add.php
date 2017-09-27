<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

JHtml::stylesheet('media/com_emundus/css/mod_emundus_calendar_add.css');

$google_client_id       = $params->get('clientId');
$google_secret_key      = $params->get('clientSecret');
$google_refresh_token   = $params->get('refreshToken');

$user   = Jfactory::getUser();
$helper = new modEmundusCalendarAddHelper;

if (isset($_POST["em-calendar-title"], $_POST["em-calendar-program"], $_POST["em-calendar-color"])) {
    
    // This is where we'll call the helper functions that create the calendar.
    $service = $helper->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);

}

$programs = $helper->getPrograms();

require(JModuleHelper::getLayoutPath('mod_emundus_calendar_add'));

?>