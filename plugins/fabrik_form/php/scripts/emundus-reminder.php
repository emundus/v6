<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: confirm_post.php 89 2012-07-16 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 D�cision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description G�n�re le param�trage de la relance email en fonction de la date d'envoie du dossier :: par ex. : alerte pour un envoie de rapport au bout d'un an
 */

$db =& JFactory::getDBO();

$eMConfig =& JComponentHelper::getParams('com_emundus');
$remind_report_mail = $eMConfig->get('report_remind_mail_id');

$student = & JFactory::getSession()->get('emundusUser');
$student->candidature_posted = 1;

$patterns = array ('/\[NAME]/','/\[SIGNATURE]/','/\[CANDIDATURE_START]/','/\[SITE_URL]/','/\[DEADLINE]/');
$replacements = array ($student->id, $student->name, $student->email, JURI::base(), strftime("%A %d %B %Y %H:%M", strtotime($student->candidature_end) ).' (GMT)');


//delete old email alerts
$query = 'DELETE FROM #__emundus_emailalert WHERE user_id='.$student->id;
$db->setQuery( $query );
$db->execute();
//insert an email alert to send report
$query = 'INSERT INTO #__emundus_emailalert (user_id, email_id, date_time, periode) VALUES ('.$student->id.', '.$remind_report_mail.', "'.date('Y-m-d G:i:s').'", 0)';
$db->setQuery( $query );
$db->execute();
?>