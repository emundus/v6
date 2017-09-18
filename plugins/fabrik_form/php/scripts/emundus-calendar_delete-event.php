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

$user = JFactory::getUser();
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$db 		= JFactory::getDBO();

$eMConfig = JComponentHelper::getParams('com_emundus');
$id_applicants 			 = $eMConfig->get('id_applicants', '0');
$applicants 			 = explode(',',$id_applicants);
$calendarID1 = $eMConfig->get('calendarId1');
$calendarID2 = $eMConfig->get('calendarId2');
$calendarID3 = $eMConfig->get('calendarId3');
$calendarID4 = $eMConfig->get('calendarId4');
$calendarID5 = $eMConfig->get('calendarId5');
$calendarID6 = $eMConfig->get('calendarId6');
$calendarID7 = $eMConfig->get('calendarId7');
$calendarID8 = $eMConfig->get('calendarId8');
$calendarID9 = $eMConfig->get('calendarId9');
$calendarID10 = $eMConfig->get('calendarId10');
$calendarID11 = $eMConfig->get('calendarId11');
$calendarID12 = $eMConfig->get('calendarId12');
$calendarID13 = $eMConfig->get('calendarId13');
$calendarID14 = $eMConfig->get('calendarId14');
$calendarID15 = $eMConfig->get('calendarId15');
$calendarID16 = $eMConfig->get('calendarId16');
$calendarID17 = $eMConfig->get('calendarId17');
$calendarID18 = $eMConfig->get('calendarId18');
$calendarID19 = $eMConfig->get('calendarId19');
$calendarID20 = $eMConfig->get('calendarId20');

$fnum = $jinput->get('rowid', null);
$itemid = $jinput->get('Itemid'); 
$reload = $jinput->get('rq', 0);
$cal = $jinput->get('catid', 105);
$valeurCatId = $jinput->getValue('jos_dpcalendar_events___catid');
$title = $jinput->getValue('jos_dpcalendar_events___title');
$description = $jinput->getValue('jos_dpcalendar_events___description');
$valStartDateTime = $jinput->getValue('jos_dpcalendar_events___start_date');
$valEndDateTime = $jinput->getValue('jos_dpcalendar_events___end_date');
$startTime = $jinput->getValue('jos_dpcalendar_events___start_time');
$endTime = $jinput->getValue('jos_dpcalendar_events___end_time');
$startDateTime = explode(' ', $valStartDateTime[date]);
$startDate = $startDateTime[0];
$startTime = $startDateTime[1];

$endDateTime = explode(' ', $valEndDateTime[date]);
$endDate = $endDateTime[0];
$endTime = $endDateTime[1];

$myUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
$urlExplode = explode('?', $myUrl);
$url = $urlExplode[1];
$rowIdExplode = explode('&', $url);
$rowId = $rowIdExplode[3];
$valueRowId = explode('=', $rowId);
$eventId = $valueRowId[1];
$compareForm = $valueRowId[0];



if ($valeurCatId == array('105')) {
    $calendarID = $calendarID1;
} elseif ($valeurCatId == array('106')){
   $calendarID = $calendarID2;
 }  elseif ($valeurCatId == array('107')){
   $calendarID = $calendarID3;
} elseif ($valeurCatId == array('108')){
   $calendarID = $calendarID4;
} elseif ($valeurCatId == array('109')){
   $calendarID = $calendarID5;
} elseif ($valeurCatId == array('110')){
   $calendarID = $calendarID6;
} elseif ($valeurCatId == array('111')){
   $calendarID = $calendarID7;
} elseif ($valeurCatId == array('112')){
   $calendarID = $calendarID8;
} elseif ($valeurCatId == array('113')){
   $calendarID = $calendarID9;
} elseif ($valeurCatId == array('114')){
   $calendarID = $calendarID10;
} elseif ($valeurCatId == array('115')){
   $calendarID = $calendarID11;
} elseif ($valeurCatId == array('116')){
   $calendarID = $calendarID12;
} elseif ($valeurCatId == array('117')){
   $calendarID = $calendarID13;
}elseif ($valeurCatId == array('118')){
   $calendarID = $calendarID14;
}elseif ($valeurCatId == array('119')){
   $calendarID = $calendarID15;
}elseif ($valeurCatId == array('120')){
   $calendarID = $calendarID16;
}elseif ($valeurCatId == array('121')){
   $calendarID = $calendarID17;
}elseif ($valeurCatId == array('122')){
   $calendarID = $calendarID18;
}elseif ($valeurCatId == array('123')){
   $calendarID = $calendarID19;
}elseif ($valeurCatId == array('124')){
   $calendarID = $calendarID20;
}

try
{

$sync = new EmundusModelCalendar;
$sync->deleteEvent($calendarID,$eventId);
die("<div class='col-md-12'><center><h1>Record Deleted.</h1></center></div>");

}
catch(Exception $e)
{
    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
    JLog::add($error, JLog::ERROR, 'com_emundus');
}


?>



