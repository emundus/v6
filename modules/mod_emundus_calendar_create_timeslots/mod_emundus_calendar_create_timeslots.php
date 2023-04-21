<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');


JHtml::stylesheet('media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css');
JHtml::stylesheet('media/com_emundus/css/mod_emundus_calendar_add.css');

$user   = Jfactory::getUser();
$helper = new modEmundusTimeslotsHelper;

$calendars = $helper->getCalendars();

if ($calendars !== false)
    require(JModuleHelper::getLayoutPath('mod_emundus_calendar_create_timeslots'));

?>