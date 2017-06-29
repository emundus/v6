<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: referent_letter.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi d'un email à la banque lorsque toutes les pièces nécessaire à l'ouverture d'un compte sont fournie par l'étudiant
 */
$baseurl = JURI::base();
$student_id = $_REQUEST['jos_emundus_bank___student_id'];
$mailer = JFactory::getMailer();

$db =& JFactory::getDBO();
// Récupération des données du mail
$query = 'SELECT id, subject, emailfrom, name, message
				FROM #__emundus_setup_emails
				WHERE lbl="bank_letter"';
$db->setQuery( $query );
$db->execute();
$obj=$db->loadObject();

// Email de la banque
$query = 'SELECT id, email_to 
				FROM #__contact_details
				WHERE alias="bank"';
$db->setQuery( $query );
$db->execute();
$bank=$db->loadObject();

// Pièces jointes nécessaires à l'ouverture d'un compte
		$query = 'SELECT attachments.attachment_id, uploads.filename, uploads.description, attachments.lbl, attachments.value, attachments.description AS adescription
					FROM #__emundus_uploads AS uploads
					INNER JOIN #__emundus_setup_attachment_profiles AS attachments ON uploads.attachment_id=attachments.attachment_id
					WHERE uploads.user_id = '.$student_id.' AND attachments.bank_needed = 1
					ORDER BY attachments.value';

$db->setQuery( $query );
$db->execute();
$attachments=$db->loadObjectList();
	
foreach ( $attachments as $row ) {
	if ($row->description != '')
				$link = $row->value.' ('.$row->description.')';
			else
				$link = $row->value;
	$list_files .= '- <a href="'.$this->baseurl.'/'.EMUNDUS_PATH_REL.$item->user_id.'/'.$row->filename.'" target="_new">'.$link.'</a><br />';
	$path_file = EMUNDUS_PATH_ABS.$student_id.DS.$row->filename;
	$attachment[] = $path_file;
}


//////////////////////////  SET FILES REQUEST  /////////////////////////////
// 
// Génération de l'id du prochain fichier qui devra être ajouté par la banque

// 1. Génération aléatoire de l'ID
function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789') {
    $string = '';
    for ($i = 0; $i < $len; $i++) {
        $pos = rand(0, strlen($chars)-1);
        $string .= $chars{$pos};
    }
    return $string;
}

$key = md5(rand_string(20).time());
$attachment_id = 11;

// 2. MAJ de la table emundus_files_request
$query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id) 
					  VALUES (NOW(), '.$student_id.', "'.$key.'", "'.$attachment_id.'")';
$db->setQuery( $query );
$db->execute();

// 3. Envoi du lien vers lequel la bank va pouvoir uploader le RIB
$link_upload = $baseurl.'index.php?option=com_fabrik&c=form&view=form&formid=46&tableid=48&keyid='.$key.'&sid='.$student_id;
$link_html = 'Cliquez <a href="'.$link_upload.'">ICI</a> pour acc&eacute;der à l\'interface d\'upload du RIB<br /><br />';
$link_html .= 'Si ce lien ne fonctionne pas merci de copier-coller l\'adresse suivante dans votre navigateur : <br />'.$link_upload.'<br />';

///////////////////////////////////////////////////////

$student = & JUser::getInstance($student_id);
//$user = JFactory::getUser();

$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/');
$replacements = array ($student->id, $student->name, $student->email, '<br />');

////////////////////////  EMAIL  //////////////////////// 
$from = $obj->emailfrom;
$fromname =$obj->name;
$recipient[] = $bank->email_to;
$subject = $obj->subject;
$body = preg_replace($patterns, $replacements, $obj->message).'<p>'.$list_files.'</p>'.$link_html;
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

$mailer->setSender($sender);
$mailer->addReplyTo($from, $fromname);
$mailer->addRecipient($recipient);
$mailer->setSubject($subject);
$mailer->isHTML(true);
$mailer->Encoding = 'base64';
$mailer->setBody($body);
$mailer->addAttachment($attachment);

$send = $mailer->Send();
if ($send !== true)
    echo 'Error sending email: ' . $send->__toString(); die();

?>