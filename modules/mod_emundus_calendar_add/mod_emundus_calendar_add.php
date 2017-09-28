<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

if (!isset($_POST["calTitle"], $_POST["calProgram"], $_POST["calColor"])) {

    JHtml::stylesheet('media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css');
    JHtml::stylesheet('media/com_emundus/css/mod_emundus_calendar_add.css');

    $google_client_id       = $params->get('clientId');
    $google_secret_key      = $params->get('clientSecret');
    $google_refresh_token   = $params->get('refreshToken');

    $user   = Jfactory::getUser();
    $helper = new modEmundusCalendarAddHelper;

    $programs = $helper->getPrograms();

    require(JModuleHelper::getLayoutPath('mod_emundus_calendar_add'));

} else {
    

    // This is where we'll call the helper functions that create the calendar.
    $service = $helper->google_authenticate($google_client_id, $google_secret_key, $google_refresh_token);

}

?>