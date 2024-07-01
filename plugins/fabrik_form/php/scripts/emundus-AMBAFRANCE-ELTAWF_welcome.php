<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: AMBAFRANCE-ELTANC_welcome.php 89 2016-06-30 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2015 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email au candidat et au referent au début de la saisie du dossier
 */


include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');

$mainframe  = JFactory::getApplication();
$jinput     = $mainframe->input;
$last_name       = $jinput->get('jos_emundus_personal_detail___last_name');
$first_name       = $jinput->get('jos_emundus_personal_detail___first_name');

$db = JFactory::getDBO();
$user =  JFactory::getUser();
$app    = JFactory::getApplication();
$email_from_sys = $app->getCfg('mailfrom');

$recipients[] = (object) array('email' => $user->email, 'id' => $user->id, 'email_tmpl' => 'eltawf_welcome');
//$recipients[] = (object) array('email' => 'benjamin@rivalland.info', 'id' => 62, 'email_tmpl' => 'eltanc_welcome_inform');
$recipients[] = (object) array('email' => 'eleanor.harley@diplomatie.gouv.fr', 'id' => 62, 'email_tmpl' => 'eltawf_welcome_inform');

if (count($recipients) > 0) {

    $emails = new EmundusModelEmails;

    $post = array('FIRSTNAME' => $first_name, 'LASTNAME' => strtoupper($last_name));

    foreach ($recipients as $recipient) {
        $email = $emails->getEmail($recipient->email_tmpl);

        $mailer = JFactory::getMailer();
        // Mail
        $from = $email->emailfrom;
        $from_id = 62;
        $fromname =$email->name;
        $to = $recipient->email;
        $subject = $email->subject;
        $tags = $emails->setTags($user->id, $post, null, '', $email->message);
        $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
        $mode = 1;

        //$attachment[] = $path_file;
        //$replyto = $user->email;
        //$replytoname = $user->name;

        // setup mail
        $sender = array(
            $email_from_sys,
            $fromname
        );

        $mailer->setSender($sender);
        $mailer->addReplyTo($from, $fromname);
        $mailer->addRecipient($to);
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
                'user_id_to' => $recipient->id,
                'subject' => $subject,
                'message' => $body,
                'email_to' => $to
            );
            $emails->logEmail($message);
        }
    }
}

?>
