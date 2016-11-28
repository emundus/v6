<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: attachement_public.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email à l'étudiant lors d'un upload de document demandé par consortium à une tierce personne 
 *						(Banque ou Professeur Référent). 
 */
/*$this->formModel->_arErrors['jos_emundus_uploads___filename'][] = 'woops!';
return false;
*/
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$baseurl = JURI::base();
$db =& JFactory::getDBO();
$eMConfig = JComponentHelper::getParams('com_emundus');
$alert_new_attachment = $eMConfig->get('alert_new_attachment');
$mailer = JFactory::getMailer();

$files 	= JRequest::get('FILES');
$key_id = JRequest::getVar('keyid', null,'get');
$user_id = $jinput->get('jos_emundus_uploads___user_id');
$sid=JRequest::getVar('sid', null,'get');
$attachment_id = $jinput->get('jos_emundus_uploads___attachment_id');
//$params =& $this->getParams();

$db->setQuery('SELECT student_id, attachment_id, keyid FROM #__emundus_files_request WHERE keyid="'.mysql_real_escape_string($key_id).'"');
$file_request=$db->loadObject();

if($files['jos_emundus_uploads___filename']['size'] == 0){
		$link_upload = $baseurl.'index.php?option=com_fabrik&view=form&formid=68&jos_emundus_uploads___user_id[value]='.$sid.'&jos_emundus_uploads___attachment_id[value]='.$file_request->attachment_id.'&sid='.$sid.'&keyid='.$key_id;
		if($files['jos_emundus_uploads___filename']['error'] == 4)
			JError::raiseWarning(500, JText::_('WARNING: No file selected, please select a file','error')); // no file
		else
			JError::raiseWarning(500, JText::_('WARNING: You just upload an empty file, please check out your file','error')); // file empty
		$mainframe->redirect($link_upload);
		exit();
}

if($user_id != $file_request->student_id || $attachment_id != $file_request->attachment_id) {
	// die('data1:'.$file_request->student_id.'-'.$user_id.'-'.$file_request->attachment_id.'-'.$attachment_id.'-'.$key_id.'-'.$db->getErrorMsg());
	header('Location: '.$baseurl.'index.php');
	exit();
}

$student = &JUser::getInstance($user_id);


if(!isset($student)) {
	// die('data2:'.$key_id.'-'.$user_id.'-'.$attachment_id);
	header('Location: '.$baseurl.'index.php');
	exit();
}

$query = 'SELECT profile FROM #__emundus_users WHERE user_id='.$user_id.'';
$db->setQuery( $query );
$profile=$db->loadResult();
//die('data2:'.$profile.'-'.print_r($attachement_params, true).'-'.print_r($upload, true).'-'.$db->getErrorMsg());

// 1. Récupération des informations sur l'étudiant et le fichier qui doit être chargé par la tierce personne
$query = 'SELECT ap.displayed, attachment.lbl, attachment.value
			FROM #__emundus_setup_attachments AS attachment
			LEFT JOIN #__emundus_setup_attachment_profiles AS ap ON attachment.id = ap.attachment_id AND ap.profile_id='.$profile.'
			WHERE attachment.id ='.$attachment_id.' ';
$db->setQuery( $query );
$attachement_params=$db->loadObject();
//die('data3:'.$attachement_params->displayed.'-'.$attachement_params->lbl.'-'.$attachment_id.'-'.$profile.'-'.$db->getErrorMsg().'-'.$db->getQuery());
// 2. Récupération des données du fichier qui vient d'être uplodé par la tierce personne
$query = 'SELECT id, filename FROM #__emundus_uploads WHERE attachment_id='.$attachment_id.' AND user_id='.$user_id.' ORDER BY id DESC';
$db->setQuery( $query );
$upload=$db->loadObject();
$nom = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($student->name,ENT_NOQUOTES,'UTF-8'))));
if(!isset($attachement_params->displayed) || $attachement_params->displayed === '0') $nom.= "_locked";
$nom .= $attachement_params->lbl.rand().'.'.end(explode('.', $upload->filename));

if(!file_exists(EMUNDUS_PATH_ABS.$user_id)) {
	if (!mkdir(EMUNDUS_PATH_ABS.$user_id, 0777, true) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user_id.DS.'index.html')) 
			die(JError::raiseWarning(500, 'ERROR_CANNOT_CREATE_USER_FILE'));
}
	

if (!rename(JPATH_SITE.$upload->filename, EMUNDUS_PATH_ABS.$user_id.DS.$nom))
	die("ERROR_MOVING_UPLOAD_FILE");

$db->setQuery('UPDATE #__emundus_uploads SET filename="'.$nom.'" WHERE id='.$upload->id);
$db->execute();
$query = 'UPDATE #__emundus_files_request SET uploaded=1, filename="'.$nom.'" WHERE keyid="'.$key_id.'"';
$db->setQuery( $query );
$db->execute();

// Récupération des données du mail
$query = 'SELECT id, subject, emailfrom, name, message
				FROM #__emundus_setup_emails
				WHERE lbl="attachment"';
$db->setQuery( $query );
$obj=$db->loadObject();
	
	
	//$user = JFactory::getUser();
	
	$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/');
	$replacements = array ($student->id, $student->name, $student->email, '<br />');
	
 	// Mail au candidat
	$fileURL = $baseurl.'/'.EMUNDUS_PATH_REL.$upload->user_id.'/'.$nom;
	$from = $obj->emailfrom;
	$fromname =$obj->name;
	$recipient[] = $student->email;
	$subject = $obj->subject;
	$body = preg_replace($patterns, $replacements, $obj->message).'<br/>';
	$mode = 1;
	//$cc = $user->email;
	//$bcc = $user->email;
	//$attachment[] = $path_file;
	$replyto = $obj->emailfrom;
	$replytoname = $obj->name;

    // setup mail
    $app    = JFactory::getApplication();
	$email_from_sys = $app->getCfg('mailfrom');
    $sender = array(
        $email_from_sys,
        $fromname
    );
    $mailer = JFactory::getMailer();

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
				VALUES ('62', '".$student->id."', '".$subject."', '".$body."', NOW())";
        $db->setQuery( $sql );
        $db->execute();
    }
	
// if($alert_new_attachment == 1){
//	// Envoie d'une copie au user, le CC ou BCC n'est pas utilisé car cela bug avec les serveurs de Paris1
//	// Récupération des données du mail de notification
//	$query = 'SELECT id, subject, emailfrom, name, message
//					FROM #__emundus_setup_emails
//					WHERE lbl="attachment_notification"';
//	$db->setQuery( $query );
//	$notification=$db->loadObject();
//	
//	// Envoie d'une copie au user, le CC ou BCC n'est pas utilisé car cela bug avec les serveurs de Paris1
//	
//	// Récupération des emails des Coordinateurs
//	$query = 'SELECT id, email
//				FROM #__users
//				WHERE usertype="Editor"';
//	$db->setQuery( $query );
//	$coord=$db->loadObjectList();
//	foreach ( $coord as $row ) {
//		$cc[] = $row->email;
//	}
//	$from = $notification->emailfrom;
//	$fromname =$notification->name;
//	//$recipient[] = $student->email;
//	$subject = $notification->subject;
//	$body = preg_replace($patterns, $replacements, $notification->message).'<br/>'.$baseurl.'/'.EMUNDUS_PATH_REL.$upload->user_id.'/'.$nom;
//	$mode = 1;
//	//$cc = $user->email;
//	//$bcc = $user->email;
//	//$attachment[] = $path_file;
//	$replyto = $notification->emailfrom;
//	$replytoname = $notification->name;
//	// Envoyer un mail au coordinateur du consortium
//	// JUtility::sendMail($from, $fromname, $cc, $subject, $body, $mode, null, null, $attachment, $replyto, $replytoname);
//	// $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
//				// VALUES ";
//	// foreach ( $cc as $row ) {
//		// $sql .= "('62', '".$row->id."', '".$subject."', '".$body."', NOW()),";
//	// }
//	// $sql = substr($sql, 0, -1)
//	// $db->setQuery( $sql );
//	// $db->query();
//	}
	
	header('Location: '.$baseurl.'index.php?option=com_content&view=article&id=18');
	exit();
?>