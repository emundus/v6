<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: attachement.php 89 2016-01-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email à l'étudiant lors d'un upload de document par le consortium. 
 *				Une copie est envoyée au user qui upload le document
 */

$mainframe 		= JFactory::getApplication();
$jinput 		= $mainframe->input;
$baseurl 		= JURI::base();
$db 			= JFactory::getDBO();

//aid = $_REQUEST['jos_emundus_uploads___attachment_id'];
//$fnum = $_REQUEST['jos_emundus_uploads___fnum'];
$aid 		= $fabrikFormData['attachment_id_raw'];
$student_id = $fabrikFormData['user_id_raw'];
$fnum 		= $fabrikFormData['fnum_raw'];
$can_be_view= $fabrikFormData['can_be_viewed_raw'];
$inform_applicant_by_email = $fabrikFormData['inform_applicant_by_email_raw'][0];
$id 		= $fabrikFormData['id_raw'];

if(is_array($aid))
	$aid = $aid[0];

//$can_be_view 	= $jinput->get('jos_emundus_uploads___can_be_viewed');
//$inform_applicant_by_email 	= $jinput->get('jos_emundus_uploads___inform_applicant_by_email');

$db->setQuery('SELECT id, user_id, filename FROM #__emundus_uploads WHERE id='.$id);
$upload = $db->loadObject();
$student = JFactory::getUser($upload->user_id);
$query = 'SELECT * FROM #__emundus_users WHERE user_id='.$upload->user_id;
$db->setQuery( $query );
$em_user=$db->loadObject();

$query = 'SELECT ap.displayed, attachment.lbl 
			FROM #__emundus_setup_attachments AS attachment
			LEFT JOIN #__emundus_setup_attachment_profiles AS ap ON attachment.id = ap.attachment_id AND ap.profile_id='.$em_user->profile.'
			WHERE attachment.id ='.$aid.' ';
$db->setQuery( $query );
$attachment_params = $db->loadObject();

$nom = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($student->name,ENT_NOQUOTES,'UTF-8'))));
if(!isset($attachment_params->displayed) || $attachment_params->displayed === '0') 
	$nom.= "_locked";
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
	/*$checkdouble_query = 'SELECT count(user_id) FROM #__emundus_uploads WHERE attachment_id=(SELECT id FROM #__emundus_setup_attachments WHERE lbl="_photo") AND user_id='.$student->id. ' AND fnum like '.$db->Quote($fnum);
	$db->setQuery($checkdouble_query);
	$cpt = $db->loadResult();
	if ($cpt>0) {
		$query = '';
	} else {*/
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
	//}
}

// Pour tous les mails
$user = JFactory::getUser();

$mode = 1;
if ($can_be_view == 1) {
	$attachment[] = EMUNDUS_PATH_ABS.$upload->user_id.DS.$nom;
	$file_url = '<br/>'.$baseurl.EMUNDUS_PATH_REL.$upload->user_id.'/'.$nom;
}
$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[FIRSTNAME\]/', '/\[LASTNAME\]/', '/\[EMAIL\]/','/\[ATTACHMENT_LINK\]/');
$replacements = array ($student->id, $student->name, $em_user->firstname, $em_user->lastname, $student->email, $file_url);

$from_id = $user->id;

if ($inform_applicant_by_email == 1) {
	// Récupération des données du mail à l'étudiant
	$db->setQuery('SELECT id, subject, emailfrom, name, message FROM #__emundus_setup_emails WHERE lbl like "csc_letter_uploaded"');
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
					VALUES ('".$from_id."', '".$student->id."', '".$subject."', '".$body."', NOW())";
        $db->setQuery( $sql );
        try {
            $db->execute();
        } catch (Exception $e) {
            // catch any database errors.
        }
    }
}

?>