<?php
defined('_JEXEC') or die();
/**
 * @version 1: attachement_public.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2019 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email à l'étudiant lors d'un upload de document demandé par consortium à une tierce personne
 */

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$baseurl = JURI::base();
$db = JFactory::getDBO();
$eMConfig = JComponentHelper::getParams('com_emundus');
$alert_new_attachment = $eMConfig->get('alert_new_attachment');
$mailer = JFactory::getMailer();
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'checklist.php');
$m_files = new EmundusModelFiles();
$h_checklist = new EmundusHelperChecklist();
$m_emails   = new EmundusModelEmails();

$files = JRequest::get('FILES');
$key_id = $jinput->get->get('keyid');
$user_id = $jinput->get('jos_emundus_uploads___user_id');
$fnum = $jinput->get('jos_emundus_uploads___fnum');
$sid = $jinput->get->get('sid');
$attachment_id = $jinput->get('jos_emundus_uploads___attachment_id');

jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.filerequest.php'], JLog::ALL, ['com_emundus']);

try {

	$db->setQuery('SELECT student_id, attachment_id, keyid FROM #__emundus_files_request WHERE keyid='.$db->Quote($key_id));
	$file_request = $db->loadObject();

	if ($files['jos_emundus_uploads___filename']['size'] == 0) {

		$link_upload = $baseurl.'index.php?option=com_fabrik&view=form&formid=68&jos_emundus_uploads___user_id[value]='.$sid.'&jos_emundus_uploads___attachment_id[value]='.$file_request->attachment_id.'&sid='.$sid.'&keyid='.$key_id;

		if ($files['jos_emundus_uploads___filename']['error'] == 4) {
			JError::raiseWarning(500, JText::_('WARNING: No file selected, please select a file', 'error'));
		} else {
			JError::raiseWarning(500, JText::_('WARNING: You just upload an empty file, please check out your file', 'error'));
		}
		$mainframe->redirect($link_upload);
		exit();
	}

	if ($user_id != $file_request->student_id || $attachment_id != $file_request->attachment_id) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_ACCESS_DENIED"), JLog::ERROR, 'com_emundus');
		header('Location: '.$baseurl.'index.php');
		exit();
	}

	$student = JUser::getInstance($user_id);
	if (!isset($student)) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_STUDENT_NOT_SET"), JLog::ERROR, 'com_emundus');
		header('Location: '.$baseurl.'index.php');
		exit();
	}

	$query = 'SELECT profile FROM #__emundus_users WHERE user_id='.$user_id.'';
	$db->setQuery($query);
	$profile = $db->loadResult();

	// 1. Récupération des informations sur l'étudiant et le fichier qui doit être chargé par la tierce personne
	$query = 'SELECT ap.displayed, attachment.lbl, attachment.value
				FROM #__emundus_setup_attachments AS attachment
				LEFT JOIN #__emundus_setup_attachment_profiles AS ap ON attachment.id = ap.attachment_id AND ap.profile_id='.$profile.'
				WHERE attachment.id ='.$attachment_id.' ';
	$db->setQuery($query);
	$attachement_params = $db->loadObject();

	// 2. Récupération des données du fichier qui vient d'être uplodé par la tierce personne
	$query = 'SELECT id, filename FROM #__emundus_uploads WHERE attachment_id='.$attachment_id.' AND user_id='.$user_id.' ORDER BY id DESC';
	$db->setQuery($query);
	$upload = $db->loadObject();

	if (count($upload) == 0) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_FILE_NOT_FOUND"), JLog::ERROR, 'com_emundus');
		die(JText::_("ERROR_FILE_NOT_FOUND"));
	}

	//$nom = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($student->name,ENT_NOQUOTES,'UTF-8'))));
	$fnumInfos = $m_files->getFnumInfos($fnum);
	$nom = $h_checklist->setAttachmentName($upload->filename, $attachement_params->lbl, $fnumInfos);

	//$nom .= $attachement_params->lbl.rand().'.'.end(explode('.', $upload->filename));

	if (!file_exists(EMUNDUS_PATH_ABS.$user_id) && (!mkdir(EMUNDUS_PATH_ABS.$user_id, 0777, true) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user_id.DS.'index.html'))) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_CANNOT_CREATE_USER_FILE"), JLog::ERROR, 'com_emundus');
		die(JError::raiseWarning(500, 'ERROR_CANNOT_CREATE_USER_FILE'));
	}

	if (!rename(JPATH_SITE.$upload->filename, EMUNDUS_PATH_ABS.$user_id.DS.$nom)) {
		JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_MOVING_UPLOAD_FILE"), JLog::ERROR, 'com_emundus');
		die(JText::_("ERROR_MOVING_UPLOAD_FILE"));
	}

	$db->setQuery('UPDATE #__emundus_uploads SET filename="'.$nom.'" WHERE id='.$upload->id);
	$db->execute();

	$query = 'UPDATE #__emundus_files_request SET uploaded=1, filename="'.$nom.'" WHERE keyid='.$db->Quote($key_id);
	$db->setQuery($query);
	$db->execute();

	// Récupération des données du mail
	$query = 'SELECT se.id, se.subject, se.emailfrom, se.name, se.message, et.Template
					FROM #__emundus_setup_emails AS se
					LEFT JOIN #__emundus_email_templates AS et ON se.email_tmpl = et.id
					WHERE se.lbl="attachment"';
	$db->setQuery($query);
	$obj = $db->loadObject();

    $post = [
        'FNUM'           => $fnum,
        'USER_NAME'      => $student->name,
        'SITE_URL'       => JURI::base(),
        'USER_EMAIL'     => $student->email,
        'ID'             => $student->id,
        'NAME'           => $student->name,
    ];
    $tags = $m_emails->setTags($student->id, $post, $fnum, '', $obj->subject.$obj->message);

	$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/');
	$replacements = array ($student->id, $student->name, $student->email, '<br />');

    $message = $m_emails->setTagsFabrik($obj->message, [$fnum]);
    $subject = $m_emails->setTagsFabrik($obj->subject, [$fnum]);

 	// Mail au candidat
	$fileURL = $baseurl.'/'.EMUNDUS_PATH_REL.$upload->user_id.'/'.$nom;
	$from = $obj->emailfrom;
	$fromname = $obj->name;
	$recipient[] = $student->email;
	$subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);
	$body = $message;
	$mode = 1;
	$replyto = $obj->emailfrom;
	$replytoname = $obj->name;

	if ($obj->Template) {
        $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $obj->Template);
	}
	$body = preg_replace($tags['patterns'], $tags['replacements'], $body);

    // setup mail
    $app = JFactory::getApplication();
	$email_from_sys = $app->getCfg('mailfrom');
	$email_from = $obj->emailfrom;

	// If the email sender has the same domain as the system sender address.
	if (!empty($email_from) && substr(strrchr($email_from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
		$mail_from_address = $email_from;
	} else {
		$mail_from_address = $email_from_sys;
	}

	// Set sender
	$sender = [
		$mail_from_address,
		$fromname
	];

    $mailer = JFactory::getMailer();
    $mailer->setSender($sender);
    $mailer->addReplyTo($from, $fromname);
    $mailer->addRecipient($recipient);
    $mailer->setSubject($subject);
    $mailer->isHTML(true);
    $mailer->Encoding = 'base64';
    $mailer->setBody($body);

    $send = $mailer->Send();
    if ( $send !== true ) {
    	JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_CANNOT_SEND_EMAIL").$send->__toString(), JLog::ERROR, 'com_emundus');
        echo 'Error sending email: ' . $send->__toString(); die();
    } else {
		$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
				VALUES ('62', '".$student->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
        $db->setQuery($sql);
        $db->execute();
    }
}
catch (Exception $e) {
    // catch any database errors.
    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
}

header('Location: '.$baseurl.'index.php?option=com_content&view=article&id=18');
exit();
