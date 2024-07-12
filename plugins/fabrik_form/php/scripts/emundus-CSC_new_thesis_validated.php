<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: new_thesis_validated.php 89 2015-02-18 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2015 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email au Directeur de thèse pour l'informer de l'état de validation de son sujet
 */

include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');

$db = JFactory::getDBO();
$app = JFactory::getApplication();

$referents = JAccess::getUsersByGroup(23);
$university = $fabrikFormData['doctoral_school_raw'][0];
$titre  =   $fabrikFormData['titre'];
$thesis_id  =   $fabrikFormData['id'];
$doctoral_school  =   $fabrikFormData['doctoral_school'];
$laboratory_director  =   $fabrikFormData['laboratory_director'];
$research_laboratory  =   $fabrikFormData['research_laboratory'];
$email_laboratory_director   =   $fabrikFormData['email_laboratory_director'];

$valide = $fabrikFormData['valide_raw'][0];
$thesis_supervisor_user_id   =   $fabrikFormData['user_raw'][0];
$thesis_supervisor_user   =   JFactory::getUser($thesis_supervisor_user_id);

if($valide == 1) {
    $query ='SELECT eu.firstname, eu.lastname, u.email, u.id, c.title
    		 FROM #__users as u 
    		 LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id 
             LEFT JOIN #__categories as c ON c.id=eu.university_id 
    		 WHERE u.disabled!=1 AND u.id ='.$thesis_supervisor_user_id;
    try {
    	$db->setQuery($query);
        $recipients = $db->loadObjectList();
    } catch (Exception $e) {
    	// catch any database errors.
    }

    if (count($recipients) > 0) {

    	$emails = new EmundusModelEmails;

    	$post = array(
                'FIRSTNAME'       => $recipients[0]->firstname,
                'LASTNAME'        => $recipients[0]->lastname,
                'DOCTORAL_SCHOOL' => $recipients[0]->title,
                'TITRE'           => $titre,
                'LABORATORY_DIRECTOR' => $laboratory_director,
                'RESEARCH_LABORATORY' => $research_laboratory,
                'URL_THESIS_PROPOSAL' => JURI::base().'index.php?option=com_emundus&view=thesis&id='.$thesis_id
                );
        //
    	// email to thesis director
        //
    	$email  = $emails->getEmail("csc_new_thesis_validated");
        $mailer = JFactory::getMailer();
        $tags = $emails->setTags($thesis_supervisor_user_id, $post, null, '', $email->emailfrom.$email->name.$email->subject.$email->message);
        // Mail 
        $from      = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
        $from_id   = 62;
        $fromname  = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
        $recipient = $recipients[0]->email;
        $subject   = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
        $body      = preg_replace($tags['patterns'], $tags['replacements'], $email->message); 

        $sender = array(
            $from,
            $fromname
        );

        $mailer->setSender($sender);
        $mailer->addRecipient($recipient);
        $mailer->setSubject($subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        $send = $mailer->Send();
        if ( $send !== true ) {
            echo '1. Error sending email: ' . $send->__toString(); die();
        } else {
            $message = array(
                'user_id_from' => $from_id,
                'user_id_to' => $referent->id,
                'subject' => $subject,
                'message' => $body,
                'email_to' => $recipient
            );
            $emails->logEmail($message);
        }
    } else {
        $app->enqueueMessage(FText::_('CANNOT CONTACT THESIS DIRECTOR BY EMAIL'), 'error');
    }
} else {
    $app->enqueueMessage(FText::_('THESIS PROPOSAL MODIFIED'), 'message');
}

?>