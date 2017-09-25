<?php
defined( '_JEXEC' ) or die();
/**
 * @version 3: calencar_sync.php 89 2016-12-07 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2016 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Synchronisation du calendrier DPCal avec GoogleCal
 */
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'calendar.php');
JLoader::import('components.com_dpcalendar.libraries.dpcalendar.syncplugin', JPATH_ADMINISTRATOR);
JPluginHelper::importPlugin( 'dpcalendar' );

jimport('joomla.log.log');
JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_emundus.calendar_sync.php'
    ),
    JLog::ALL,
    array('com_emundus')
);

$user       = JFactory::getUser();
$mainframe  = JFactory::getApplication();
$db         = JFactory::getDBO();
$jinput     = $mainframe->input;

$eMConfig       = JComponentHelper::getParams('com_emundus');
$id_applicants  = $eMConfig->get('id_applicants', '0');
$applicants     = explode(',',$id_applicants);
$calendarIDs    = $eMConfig->get('calendarIds');

$fnum               = $jinput->get('rowid', null);
$itemid             = $jinput->get('Itemid'); 
$reload             = $jinput->get('rq', 0);
$cal                = $jinput->get('catid', 105);
$valeurCatId        = $jinput->getValue('jos_dpcalendar_events___catid');
$title              = $jinput->getValue('jos_dpcalendar_events___title');
$description        = $jinput->getValue('jos_dpcalendar_events___description');
$valStartDateTime   = $jinput->getValue('jos_dpcalendar_events___start_date');
$valEndDateTime     = $jinput->getValue('jos_dpcalendar_events___end_date');
$startTime          = $jinput->getValue('jos_dpcalendar_events___start_time');
$endTime            = $jinput->getValue('jos_dpcalendar_events___end_time');
$startDateTime      = explode(' ', $valStartDateTime[date]);
$startDate          = $startDateTime[0];
$startTime          = $startDateTime[1];

$endDateTime = explode(' ', $valEndDateTime[date]);
$endDate = $endDateTime[0];
$endTime = $endDateTime[1];


// The category ID is only used as a means to get to the calendarID.
$catIdValue = $jinput->getValue('jos_dpcalendar_events___catid');

// Using the category ID we can get the current calendars ID.
$query = "SELECT calId FROM jos_categories WHERE id = ".$catIdValue[0];

try {
    $db->setQuery($query);
    $calendarID = $db->loadResult(); 
} catch (Exception $e) {
    die($e->getMessage());
}

// Here we are simply checking that the calendar ID is in the params, this isn't really useful but helps add a safety check.
if (!in_array($calendarID, explode(",", $calendarIDs)))
    die('Error: current calendar ID cannot be found in params.');

// Gets the arguements to the URI, used for knowing whether we are updating an event or making a new one.
// uri['rowid'] = event ID
parse_str($_SERVER['REQUEST_URI'], $uri);

try {

    $sync = new EmundusModelCalendar;
    $sync->deleteEvent($calendarID, $uri['rowid']);
    die("<div class='col-md-12'><center><h1>Record Deleted.</h1></center></div>");

} catch(Exception $e) {
    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
    JLog::add($error, JLog::ERROR, 'com_emundus');
}


?>



