<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

JHtml::stylesheet('media/com_emundus/css/mod_emundus_calendar_add.css');

$google_client_id = $params->get('clientId');
$google_secret_key = $params->get('clientSecret');
$google_refresh_token = $params->get('refreshToken');

?>