<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: referent_letter.php 
 * @package Fabrik
 * @copyright Copyright (C) 2016 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email aux professeurs référents choisis par l'étudiant
 */

jimport( 'joomla.utilities.utility' );
jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.filerequest.php'], JLog::ALL, ['com_emundus']);
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
$baseurl = JURI::root();

$student_id = $data['jos_emundus_references___user'];
$fnum = $formModel->getElementData('jos_emundus_references___fnum', false, '');
$time_date = $formModel->getElementData('jos_emundus_references___time_date', false, '');

$recipients[] = array('attachment_id' => $formModel->getElementData('jos_emundus_references___attachment_id_1', false, 4), 'email' => $formModel->getElementData('jos_emundus_references___Email_1', false, ''));
$recipients[] = array('attachment_id' => $formModel->getElementData('jos_emundus_references___attachment_id_2', false, 6), 'email' => $formModel->getElementData('jos_emundus_references___Email_2', false, ''));
$recipients[] = array('attachment_id' => $formModel->getElementData('jos_emundus_references___attachment_id_3', false, 21), 'email' => $formModel->getElementData('jos_emundus_references___Email_3', false, ''));
$recipients[] = array('attachment_id' => $formModel->getElementData('jos_emundus_references___attachment_id_4', false, 19), 'email' => $formModel->getElementData('jos_emundus_references___Email_4', false, ''));

$student = JFactory::getUser($student_id);
$current_user = JFactory::getSession()->get('emundusUser');
if (empty($current_user->fnum) || !isset($current_user->fnum)) {
    $current_user->fnum = $fnum;
}


$db = JFactory::getDBO();

// Récupération des données du mail
$query = 'SELECT se.id, se.subject, se.emailfrom, se.name, se.message, et.Template
					FROM #__emundus_setup_emails AS se
					LEFT JOIN #__emundus_email_templates AS et ON se.email_tmpl = et.id
                	WHERE se.lbl="referent_letter"';
$db->setQuery($query);
$db->execute();
$obj = $db->loadResult();

// Récupération de la pièce jointe : modele de lettre
$query = 'SELECT esp.reference_letter
                FROM #__emundus_setup_profiles as esp
                WHERE esp.id = '.$current_user->profile;
$db->setQuery($query);
$db->execute();
$obj_letter = $db->loadResult();

//////////////////////////  SET FILES REQUEST  /////////////////////////////
// 
// Génération de l'id du prochain fichier qui devra être ajouté par le referent

// 1. Génération aléatoire de l'ID
function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789') {
    $string = '';
    for ($i = 0; $i < $len; $i++) {
        $pos = rand(0, strlen($chars)-1);
        $string .= $chars{$pos};
    }
    return $string;
}

$profile = new EmundusModelProfile;
$fnum_detail = $profile->getFnumDetails($current_user->fnum);

$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[UPLOAD_URL\]/', '/\[PROGRAMME_NAME\]/');

// setup mail
$app = JFactory::getApplication();
$email_from_sys = $app->getCfg('mailfrom');

$from = $obj->emailfrom;
$fromname = $obj->name;
$from_id = 62;
$to_id = -1;

$sender = array(
    $email_from_sys,
    $fromname
);
$attachment = array();
if (!empty($obj_letter)) {
	$attachment[] = JPATH_BASE.str_replace("\\", "/", $obj_letter);
}


foreach ($recipients as $recipient) {
    if (isset($recipient['email']) && !empty($recipient['email'])) {
        $attachment_id = $recipient['attachment_id']; //ID provenant de la table emundus_attachments
        $query = 'SELECT count(id) as cpt FROM #__emundus_uploads WHERE user_id='.$student->id.' AND attachment_id='.$attachment_id.' AND fnum like '.$db->Quote($current_user->fnum);
        $db->setQuery($query);
        $db->execute();
        $is_uploaded = $db->loadResult();

        if ($is_uploaded == 0) {
            $key = md5(date('Y-m-d h:m:i').'::'.$fnum.'::'.$student_id.'::'.$attachment_id.'::'.rand());
            // 2. MAJ de la table emundus_files_request
            $query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id, fnum, email) 
                          VALUES (NOW(), '.$student->id.', "'.$key.'", "'.$attachment_id.'", '.$current_user->fnum.', '.$db->Quote($recipient['email']).')';
            $db->setQuery($query);
            $db->execute();
            
            // 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
            $link_upload = $baseurl.'index.php?option=com_fabrik&c=form&view=form&formid=68&tableid=71&keyid='.$key.'&sid='.$student->id;
            $link_html = '<p>Click <a href="'.$link_upload.'">HERE</a> to upload reference letter<br><br>';
            $link_html .= 'If link does not work, please copy and paste that hyperlink in your browser : <br>'.$link_upload.'</p>';

            $replacements = array($student->id, $student->name, $student->email, $link_upload, $fnum_detail['label']);
            $subject = preg_replace($patterns, $replacements, $obj->subject);
            $body = preg_replace($patterns, $replacements, $obj->message);

	        if ($obj->Template) {
		        $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $obj->Template);
	        }

            $to = array($recipient['email']);

            $mailer = JFactory::getMailer();

            $mailer->setSender($sender);
            $mailer->addReplyTo($from, $fromname);
            $mailer->addRecipient($to);
            $mailer->setSubject($subject);
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);
            $mailer->addAttachment($attachment);

            $send = $mailer->Send();

            if ($send !== true) {
                JFactory::getApplication()->enqueueMessage(JText::_('MESSAGE_NOT_SENT').' : '.$recipient['email'], 'error');
                JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
            } else {
                JFactory::getApplication()->enqueueMessage(JText::_('MESSAGE_SENT').' : '.$recipient['email'], 'message');
                $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
                            VALUES ('62', '-1', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
                $db->setQuery($sql);
                try {
                    $db->execute();
                } catch (Exception $e) {
	                JLog::add('Error logging email : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                }
            }
            unset($replacements);
            unset($mailer);
        }
    }   
}