<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: evaluation_ESA.php 89 2017-11-16 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2017 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Mise à jours du statut de la candidature à une campagne
 */

$db	= JFactory::getDbo();

// To determine if the candidate was interviewed, we check if his evaluation form has been filled out with a grade for his oral.
$interviewed = !empty(@$_REQUEST['jos_emundus_evaluations___oral'][0]);
$fnum = $_REQUEST['jos_emundus_evaluations___fnum'];
$student = JUser::getInstance(substr($fnum,-7));

try {

	// If user is already interviewed, then dont change his status.
	$query = 'SELECT status FROM #__emundus_campaign_candidature
				WHERE fnum LIKE '.$db->Quote($fnum);
	$db->setQuery($query);
	$status = $db->loadResult();

} catch (Exception $e) {
	JLog::add('Error in plugin evaluation-ESA on query : '.$query, JLog::ERROR, 'com_emundus');
}

if ($interviewed) {

	try {

		// Update to status 'Interviewed'
		$query = 'UPDATE #__emundus_campaign_candidature
					SET status = 5
					WHERE fnum LIKE '.$db->Quote($fnum);
		$db->setQuery($query);
		$db->execute();

	} catch (Exception $e) {
		JLog::add('Error in plugin evaluation-ESA on query : '.$query, JLog::ERROR, 'com_emundus');
	}

} elseif ($status != 5) {

	include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');

	$app = JFactory::getApplication();
	$m_emails = new EmundusModelEmails();
	$eMConfig = JComponentHelper::getParams('com_emundus');

	try {

		// Update to status 'Evaluated'
		$query = 'UPDATE #__emundus_campaign_candidature
					SET status = 3
					WHERE fnum LIKE '.$db->Quote($fnum);
		$db->setQuery($query);
		$db->execute();

		// Get the email reminding the candidate to login and book an interview.
		/*$query = 'SELECT id, subject, emailfrom, name, message
					FROM #__emundus_setup_emails
					WHERE lbl = "book_interview_reminder"';
		$db->setQuery($query);
		$obj = $db->loadObject(); */

	} catch (Exception $e) {
		JLog::add('Error in plugin evaluation-ESA on query : '.$query, JLog::ERROR, 'com_emundus');
	}

	// EMAILS ARE NOT SENT AUTOMATICALLY BY OLAGE.
	/*
	$post = [
		'FNUM'      => $fnum->fnum,
		'USER_NAME' => $student->name,
	];

	$tags = $m_emails->setTags($student->id, $post);
	$body = $m_emails->setTagsFabrik($obj->message, [$fnum->fnum]);

	$subject = preg_replace($tags['patterns'], $tags['replacements'], $obj->subject);
	$body = preg_replace($tags['patterns'], $tags['replacements'], $body);

	// template replacements (patterns)
	$subject    = $m_emails->setTagsFabrik($subject, array($fnum));
	$body       = $m_emails->setTagsFabrik($body, array($fnum));

	// Mail au candidat
	$from           = $obj->emailfrom;
	$fromname       = $obj->name;
	$recipient      = array($student->email);
	$mode           = 1;
	$replyto        = $obj->emailfrom;
	$replytoname    = $obj->name;

	// setup mail
	$email_from_sys = $app->getCfg('mailfrom');

	// If the email sender has the same domain as the system sender address.
	if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
		$mail_from_address = $from;
	else
		$mail_from_address = $email_from_sys;

	// Set sender
	$sender = array(
		$mail_from_address,
		$mail_from_name
	);

	$mailer = JFactory::getMailer();
	$mailer->setSender($sender);
	$mailer->addReplyTo($from, $fromname);
	$mailer->addRecipient($recipient);
	$mailer->setSubject($subject);
	$mailer->isHTML(true);
	$mailer->Encoding = 'base64';
	$mailer->setBody($body);

	$send = $mailer->Send();

	if ($send !== true) {

		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_CANNOT_SEND_EMAIL").$send->__toString(), JLog::ERROR, 'com_emundus');
		echo 'Error sending email: ' . $send->__toString();

	} else {

		try {

			$sql = "INSERT INTO #__messages (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
					VALUES ('62', '".$student->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
			$db->setQuery($sql);
			$db->execute();

		} catch (Exception $e) {
			JLog::add('Error in plugin evaluation-ESA on query : '.$query, JLog::ERROR, 'com_emundus');
		}

	}
	*/

}
?>