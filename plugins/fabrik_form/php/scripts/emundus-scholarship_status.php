<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: scholarship_status.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email à l'étudiant lors de la modifiation de l'état d'acceptation de la bourse
 */
$baseurl = JURI::base();
$db =& JFactory::getDBO();
$mailer = JFactory::getMailer();

$r_scholarship_request = $_REQUEST['jos_emundus_scholarship___scholarship_request'];
$r_scholarship_status = $_REQUEST['jos_emundus_scholarship___scholarship_status'];
$r_student_id = $_REQUEST['jos_emundus_scholarship___user'];

$result = '<p><h3>'.$r_scholarship_request.' : '.$r_scholarship_status[0].'</h3></p>';

	// Récupération des données du mail
	$query = 'SELECT id, subject, emailfrom, name, message
					FROM #__emundus_setup_emails
					WHERE lbl="scholarship_status"';
	$db->setQuery( $query );
	$db->execute();
	$obj=$db->loadObjectList();
	
	$student = & JUser::getInstance($r_student_id);
	//$user = JFactory::getUser();
	
	$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/');
	$replacements = array ($student->id, $student->name, $student->email, '<br />');
	
	// Mail à l'étudiant et confirmation au user
	$from = $obj[0]->emailfrom;
	$fromname =$obj[0]->name;
	$from_id = $obj[0]->id;
	$recipient[] = $student->email;
	$subject = $obj[0]->subject;
	$body = $result.preg_replace($patterns, $replacements, $obj[0]->message);
	$mode = 1;
	//$cc = $user->email;
	//$bcc = $user->email;
	$attachment[] = $path_file;
	$replyto = $obj[0]->emailfrom;
	$replytoname = $obj[0]->name;

    $app    = JFactory::getApplication();
	$email_from_sys = $app->getCfg('mailfrom');
	// setup mail
	$sender = array(
	    $email_from_sys,
	    $fromname
	);

	$mailer->setSender($sender);
	$mailer->addReplyTo($from, $fromname);
    $mailer->addRecipient($recipient);
    $mailer->setSubject($subject);
    $mailer->isHTML(true);
    $mailer->Encoding = 'base64';
    $mailer->setBody($body);
    $mailer->addAttachment($attachment);

    $send = $mailer->Send();
    if ( $send !== true ) {
        echo 'Error sending email: ' . $send->__toString(); die();
    } else {
        $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
				VALUES ('".$from_id."', '".$student->id."', '".$subject."', '".$body."', NOW())";
        $db->setQuery( $sql );
        try {
            $db->execute();
        } catch (Exception $e) {
            // catch any database errors.
        }
    }
?>