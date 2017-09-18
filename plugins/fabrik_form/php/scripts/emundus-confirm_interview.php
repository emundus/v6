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
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'candidatelist.php');
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
$valueId= $jinput->getValue('jos_dpcalendar_events___date');
$validInterview = $jinput->getValue('jos_dpcalendar_events___Confirmation');


$myUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
$urlExplode = explode('?', $myUrl);
$url = $urlExplode[1];
$rowIdExplode = explode('&', $url);
$rowId = $rowIdExplode[3];
$valueRowId = explode('=', $rowId);
$calId = $valueRowId[1];  


try
{
	
	$confirm = new EmundusModelCandidatelist;
	$confirm->confirmInterview($calId,$validInterview);
    die("<div class='col-md-12'><center><h1>Interview Updated.</h1></center></div>");
	

}
catch(Exception $e)
{
    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
    JLog::add($error, JLog::ERROR, 'com_emundus');
}


?>