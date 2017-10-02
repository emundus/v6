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

$user   = JFactory::getUser();
$app    = JFactory::getApplication();
$db     = JFactory::getDBO();
$jinput = $app->input;

$eMConfig = JComponentHelper::getParams('com_emundus');

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
	
	$m_calendar = new EmundusModelCalendar;
	$m_calendar->confirmInterview($calId,$validInterview);
    die("<div class='col-md-12'><center><h1>Interview Updated.</h1></center></div>");

}
catch(Exception $e)
{
    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
    JLog::add($error, JLog::ERROR, 'com_emundus');
}


?>