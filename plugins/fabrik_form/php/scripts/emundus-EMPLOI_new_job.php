<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: new_job.php 89 2016-06-30 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2015 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email au R�f�rents pour mentionner une nouvelle fiche
 */


include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
//include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
//include_once(JPATH_BASE.'/components/com_emundus/models/groups.php');

$db = JFactory::getDBO();
$user =  JFactory::getUser();
$app    = JFactory::getApplication();
$email_from_sys = $app->getCfg('mailfrom');


//$eMConfig = JComponentHelper::getParams('com_emundus');

$referents = JAccess::getUsersByGroup(17);
$id = $fabrikFormData['id'][0];
$university = $fabrikFormData['etablissement_raw'][0];
$intitule_poste = $fabrikFormData['intitule_poste_raw'];

$deposant = (object) array('firstname' => $user->name, 'lastname' => $user->name, 'email' => $user->email,'id' => $user->id);

$query ='SELECT eu.firstname, eu.lastname, u.email, u.id 
         FROM #__users as u 
         LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id 
         WHERE u.block!=1 AND u.id IN ('.implode(',', $referents).')
         AND eu.university_id = '.$university;
try {
    $db->setQuery($query);
    $recipients = $db->loadObjectList();
} catch (Exception $e) {
    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
}
$recipients[] = $deposant;

if (count($recipients) > 0) {

    $emails = new EmundusModelEmails;

    $post = array('FICHE_EMPLOI' => $intitule_poste, 'FICHE_ID' => $id);
    $email = $emails->getEmail("new_job");

    foreach ($recipients as $referent) {
        $mailer = JFactory::getMailer();
        $tags = $emails->setTags($referent->id, $post, null, '', $email->message);
        // Mail 
        $from = $user->email;
        $from_id = $user->id;
        $fromname =$user->name;
        $recipient = $referent->email;
        $subject = $email->subject;
        $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message); 
        $mode = 1;

        //$attachment[] = $path_file;
        $replyto = $user->email;
        $replytoname = $user->name;

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
                'user_id_from' => $user->id,
                'user_id_to' => $referent->id,
                'subject' => $subject,
                'message' => $body,
                'email_to' => $recipient
            );
            $emails->logEmail($message);
        }
    }
}

?>