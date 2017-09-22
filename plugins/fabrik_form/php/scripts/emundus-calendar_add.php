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
        'text_file' => 'com_emundus.calendar_add.php'
    ),
    JLog::ALL,
    array('com_emundus')
);

$user       = JFactory::getUser();
$mainframe  = JFactory::getApplication();
$jinput     = $mainframe->input;
$db         = JFactory::getDBO();

$eMConfig       = JComponentHelper::getParams('com_emundus');
$id_applicants  = $eMConfig->get('id_applicants', '0');
$applicants     = explode(',',$id_applicants);

$title          = $jinput->getValue('jos_dpcalendar_extcalendars___title');
$color          = $jinput->getValue('jos_dpcalendar_extcalendars___color');
$aliasTransform = str_replace(' ', '-', $title);
$aliasSet       = $jinput->set('jos_dpcalendar_extcalendars___alias',$aliasTransform);
$alias          = $jinput->getValue('jos_dpcalendar_extcalendars___alias');

try {

    $m_calendar = new EmundusModelCalendar;
    $result = $m_calendar->createCalendar($title, $alias, $color);
    $m_calendar->saveParams();
    die("<div class='col-md-12'><center><h1>Calendar Created.</h1></center></div>");

} catch(Exception $e) {
    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
    JLog::add($error, JLog::ERROR, 'com_emundus');
    die($e->getMessage());
}


?>



