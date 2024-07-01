<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: evaluation_received.php 89 2013-06-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Accusé de réception à la saisie d'une évaluation
 */

include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php'); 

$m_emails = new EmundusModelEmails;
$user = JFactory::getUser();
$mailer = JFactory::getMailer();

$email 		= $m_emails->getEmail('evaluation_received');

$body 		= $m_emails->setBody($user, $email->message);
$from 	    = $m_emails->setBody($user, $email->emailfrom);
$fromname 	= $m_emails->setBody($user, $email->name);
$subject 	= $m_emails->setBody($user, $email->subject);

$config = JFactory::getConfig();
$sender = array(
    $config->get( $from ),
    $config->get( $fromname )
);
$recipient = $user->email;

// setup mail
$app    = JFactory::getApplication();
$email_from_sys = $app->getCfg('mailfrom');
$sender = array(
    $email_from_sys,
    $fromname
);
$mailer = JFactory::getMailer();

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

$send = $mailer->Send();
if ( $send !== true ) {
    echo 'Error sending email: ' . $send->__toString(); die();
} else {
    $message = array(
        'user_id_from' => 62,
        'user_id_to' => $user->id,
        'subject' => $subject,
        'message' => $body,
        'email_to' => $recipient
    );
    $m_emails->logEmail($message);
}

?>