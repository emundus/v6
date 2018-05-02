<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: attachement_public.php 89 2017-11-06 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2017 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email à l'étudiant lors d'un envoie de formulaire de référence fait par le référent désigné par l'utilisateur.
 */

include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');

$eMConfig   = JComponentHelper::getParams('com_emundus');
$alert_new_attachment = $eMConfig->get('alert_new_attachment');

$mainframe  = JFactory::getApplication();
$jinput     = $mainframe->input;
$user_id    = $jinput->get('jos_emundus_reference_letter___user');
$fnum       = $jinput->get('jos_emundus_reference_letter___fnum');

$mailer     = JFactory::getMailer();
$db         = JFactory::getDBO();
$baseurl    = JURI::base();

$key_id     = JRequest::getVar('keyid', null, 'get');
$sid        = JRequest::getVar('sid', null, 'get');

jimport('joomla.log.log');
JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_emundus.filerequest.php'
    ),
    // Sets messages of all log levels to be sent to the file
    JLog::ALL,
    // The log category/categories which should be recorded in this file
    // In this case, it's just the one category from our extension, still
    // we need to put it inside an array
    array('com_emundus')
);


$student = JUser::getInstance($user_id);
$m_emails = new EmundusModelEmails();

if (!isset($student)) {
    JLog::add("PLUGIN emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_STUDENT_NOT_SET"), JLog::ERROR, 'com_emundus');
    header('Location: '.$baseurl.'index.php');
    exit();
}

try {
    $query = 'UPDATE #__emundus_files_request SET uploaded = 1 WHERE keyid = '.$db->Quote($key_id);
    $db->setQuery($query);
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
}

// Récupération des données du mail
try {
    $query = 'SELECT id, subject, emailfrom, name, message
                    FROM #__emundus_setup_emails
                    WHERE lbl = "reference_form_complete"';
    $db->setQuery($query);
    $obj = $db->loadObject();
} catch (Exception $e) {
    // catch any database errors.
    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
}

// template replacements (patterns)
$subject    = $m_emails->setTagsFabrik($obj->subject, array($fnum));
$body       = $m_emails->setTagsFabrik($obj->message, array($fnum));

// Mail au candidat
$from           = $obj->emailfrom;
$fromname       = $obj->name;
$recipient      = array($student->email);
$mode           = 1;
$replyto        = $obj->emailfrom;
$replytoname    = $obj->name;

// setup mail
$email_from_sys = $mainframe->getCfg('mailfrom');

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
        die($sql);
    }
}


try {

    // Step one is to get the email of the referent.
    $query = 'SELECT Email_1 FROM #__emundus_references as er
                WHERE er.fnum IN (
                    SELECT fnum
                    FROM #__emundus_files_request as efr
                    WHERE efr.keyid = "'.$key_id.'"
                )';

    $db->setQuery($query);
    $recipient = $db->loadResult();

} catch (Exception $e) {
    // catch any database errors.
    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
}

try {
    // Récupération des données du mail
    $query = 'SELECT id, subject, emailfrom, name, message
        FROM #__emundus_setup_emails
        WHERE lbl = "reference_form_received"';
    $db->setQuery($query);
    $obj = $db->loadObject();
} catch (Exception $e) {
    // catch any database errors.
    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
}

// template replacements (patterns)
$subject    = $m_emails->setTagsFabrik($obj->subject, array($fnum));
$body       = $m_emails->setTagsFabrik($obj->message, array($fnum));

// Mail au référent
$from           = $obj->emailfrom;
$fromname       = $obj->name;
$mode           = 1;
$replyto        = $obj->emailfrom;
$replytoname    = $obj->name;

// If the email sender has the same domain as the system sender address.
if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
    $mail_from_address = $from;
else
    $mail_from_address = $email_from_sys;

// Set sender
$sender = array(
    $mail_from_address,
    $fromname
);

$mailer = JFactory::getMailer();
$mailer->setSender($sender);
$mailer->addReplyTo($from, $fromname);
$mailer->addRecipient(array($recipient, "admissions@mines-albi.fr"));
$mailer->setSubject($subject);
$mailer->isHTML(true);
$mailer->Encoding = 'base64';
$mailer->setBody($body);

$send = $mailer->Send();
if ($send !== true) {
    JLog::add("PLUGIN IMT_emundus-attachment_public [".$key_id."]: ".JText::_("ERROR_CANNOT_SEND_EMAIL").$send->__toString(), JLog::ERROR, 'com_emundus');
    echo 'Error sending email: ' . $send->__toString();
} else {
    $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
            VALUES ('62', '".$student->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
    $db->setQuery($sql);
    $db->execute();
}



header('Location: '.$baseurl.'index.php?option=com_content&view=article&id=18');
exit();
?>