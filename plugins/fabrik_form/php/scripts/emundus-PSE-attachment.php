<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: attachement.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email à l'étudiant lors d'un upload de document par le consortium. 
 *						Une copie est envoyée au user qui upload le document
 */

$mainframe 		= JFactory::getApplication();
$mailer = JFactory::getMailer();
$jinput 		= $mainframe->input;
$baseurl 		= JURI::base();
$db 			= JFactory::getDBO();

$references_id = array(4, 6, 21, 19);

$aid = $_REQUEST['jos_emundus_uploads___attachment_id'];
$fnum = $_REQUEST['jos_emundus_uploads___fnum'];
if(is_array($aid))
	$aid = $aid[0];

$can_be_view 	= $jinput->get('jos_emundus_uploads___can_be_viewed');
$can_be_deleted 	= $jinput->get('jos_emundus_uploads___can_be_deleted');
$inform_applicant_by_email 	= $jinput->get('jos_emundus_uploads___inform_applicant_by_email');

$db->setQuery('SELECT * FROM #__emundus_uploads WHERE id='.$jinput->get('jos_emundus_uploads___id'));
$upload = $db->loadObject();
$student = JFactory::getUser($upload->user_id);
$query = 'SELECT profile FROM #__emundus_users WHERE user_id='.$upload->user_id.'';
$db->setQuery( $query );
$profile=$db->loadResult();
$query = 'SELECT ap.displayed, attachment.lbl, attachment.value 
			FROM #__emundus_setup_attachments AS attachment
			LEFT JOIN #__emundus_setup_attachment_profiles AS ap ON attachment.id = ap.attachment_id AND ap.profile_id='.$profile.'
			WHERE attachment.id ='.$aid.' ';
$db->setQuery( $query );
$attachment_params = $db->loadObject();

$nom = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($student->name,ENT_NOQUOTES,'UTF-8'))));
if(!isset($attachment_params->displayed) || $attachment_params->displayed === '0') $nom.= "_locked";
$nom .= $attachment_params->lbl.rand().'.'.end(explode('.', $upload->filename));

// test if directory exist
if (!file_exists(EMUNDUS_PATH_ABS.$upload->user_id)) {
	mkdir(EMUNDUS_PATH_ABS.$upload->user_id, 0777, true);
}

if (!rename(JPATH_SITE.$upload->filename, EMUNDUS_PATH_ABS.$upload->user_id.DS.$nom))
	die("ERROR_MOVING_UPLOAD_FILE");

$db->setQuery('UPDATE #__emundus_uploads SET filename="'.$nom.'" WHERE id='.$upload->id);
$db->execute();

// PHOTOS
if ($attachment_params->lbl=="_photo") {
	$pathToThumbs = EMUNDUS_PATH_ABS.$student->id.DS.$nom;
	$file_src = EMUNDUS_PATH_ABS.$student->id.DS.$nom;
	list($w_src, $h_src, $type) = getimagesize($file_src);  // create new dimensions, keeping aspect ratio

	switch ($type){
		case 1:   //   gif -> jpg
			$img = imagecreatefromgif($file_src);
		break;
		case 2:   //   jpeg -> jpg
			$img = imagecreatefromjpeg($file_src);
		break;
		case 3:  //   png -> jpg
			$img = imagecreatefrompng($file_src);
		break;
		default:
			$img = imagecreatefromjpeg($file_src);
		break;
	}
	$new_width = 200;
	$new_height = floor( $h_src * ( $new_width / $w_src ) );
	$tmp_img = imagecreatetruecolor( $new_width, $new_height );
	imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $w_src, $h_src );
	imagejpeg( $tmp_img, EMUNDUS_PATH_ABS.$student->id.DS.'tn_'.$nom);
	$student->avatar = $nom;
	$email_tmpl = "attachment";
}
// Si le type de document est une lettre de référence
elseif (in_array($aid, $references_id)) {
	$email_tmpl = "reference";

	$db->setQuery('SELECT count(id) FROM #__emundus_uploads WHERE fnum like '.$db->Quote($fnum).' AND attachment_id IN ('.implode(',', $references_id).')');
	$nb_references=$db->loadResult();

	$db->setQuery('SELECT submitted FROM #__emundus_campaign_candidature WHERE fnum like '.$db->Quote($fnum));
	$submitted=$db->loadResult();

	if ($nb_references >= 2 && $submitted > 0) {
		$db->setQuery('UPDATE #__emundus_campaign_candidature SET status=2 WHERE fnum like '.$db->Quote($fnum));
		$db->execute();
	} 
} else $email_tmpl = "attachment";


if ($inform_applicant_by_email == 1) {
	// Pour tous les mails
	$user = JFactory::getUser();
	$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[DOCUMENT_TYPE\]/','/\n/');
	$replacements = array ($student->id, $student->name, $student->email, $attachment_params->value, '<br />');
	$mode = 1;
	if ($can_be_view == 1) {
		$attachment[] = EMUNDUS_PATH_ABS.$upload->user_id.DS.$nom;
		$file_url = '<br/>'.$baseurl.'/'.EMUNDUS_PATH_REL.$upload->user_id.'/'.$nom;
	}
	$from_id = $user->id;

	// Récupération des données du mail à l'étudiant
	$db->setQuery('SELECT id, subject, emailfrom, name, message FROM #__emundus_setup_emails WHERE lbl='.$db->Quote($email_tmpl));
	$email=$db->loadObject();
	$from = $email->emailfrom;
	$fromname =$email->name;
	$recipient[] = $student->email;
	$subject = $email->subject;
	$body = preg_replace($patterns, $replacements, $email->message).'<br/>'.@$file_url;
	$replyto = $email->emailfrom;
	$replytoname = $email->name;


    // setup mail
    $app    = JFactory::getApplication();
	$email_from_sys = $app->getCfg('mailfrom');
    $sender = array(
        $email_from_sys,
        $fromname
    );


    $mailer->setSender($sender);
    $mailer->addRecipient($recipient);
    $mailer->setSubject($subject);
    $mailer->isHTML(true);
    $mailer->Encoding = 'base64';
    $mailer->setBody($body);
    $mailer->addAttachment($attachment);

    $send = $mailer->Send();
    if ( $send !== true ) {
        echo 'Error sending email: ' . $send->__toString(); var_dump($recipient);
        die();
    } else {
        $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
					VALUES ('".$from_id."', '".$student->id."', '".$subject."', '".$body."', NOW())";
        $db->setQuery( $sql );
        $db->execute();
    }
}
?>