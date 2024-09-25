<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: new_thesis_to_validate.php 89 2015-02-18 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2015 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email au Directeur de l'école doctoral pour validation du sujet de thèse
 */

include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');

$db = JFactory::getDBO();
$user =  JFactory::getSession()->get('emundusUser');
$app = JFactory::getApplication();

//$eMConfig = JComponentHelper::getParams('com_emundus');

$referents = JAccess::getUsersByGroup(23);
$university = $fabrikFormData['doctoral_school_raw'][0];
$titre  =   $fabrikFormData['titre'];
$thesis_id  =   $fabrikFormData['id'];
$doctoral_school  =   $fabrikFormData['doctoral_school'];
$laboratory_director  =   $fabrikFormData['laboratory_director'];
$research_laboratory  =   $fabrikFormData['research_laboratory'];
$email_laboratory_director   =   $fabrikFormData['email_laboratory_director'];


$query ='SELECT eu.firstname, eu.lastname, u.email, u.id, c.title
		 FROM #__users as u 
		 LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id 
         LEFT JOIN #__categories as c ON c.id=eu.university_id 
		 WHERE u.disabled!=1 AND u.id IN ('.implode(',', $referents).')
         AND eu.university_id = '.$university;
try {
	$db->setQuery($query);
    $recipients = $db->loadObjectList();
} catch (Exception $e) {
	// catch any database errors.
}

if (count($recipients) > 0) {

	$emails = new EmundusModelEmails;

	$post = array(
            'FIRSTNAME'       => $user->firstname,
            'LASTNAME'        => $user->lastname,
            'DOCTORAL_SCHOOL' => $recipients[0]->title,
            'TITRE'           => $titre,
            'LABORATORY_DIRECTOR' => $laboratory_director,
            'RESEARCH_LABORATORY' => $research_laboratory,
            'URL_THESIS_PROPOSAL' => JURI::base().'index.php?option=com_emundus&view=thesis&id='.$thesis_id
            );
    //
	// email to thesis director
    //
	$email      = $emails->getEmail("csc_new_thesis");
    $mailer		= JFactory::getMailer();
    $tags 		= $emails->setTags($user->id, $post, null, '', $email->emailfrom.$email->name.$email->subject.$email->message);
    // Mail 
    $from      = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
    $from_id   = 62;
    $fromname  = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
    $recipient = $user->email;
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
    if ( $send ) {
        $message = array(
            'user_id_from' => $from_id,
            'user_id_to' => $referent->id,
            'subject' => $subject,
            'message' => $body,
            'email_to' => $recipient
        );
        $emails->logEmail($message);
        $app->enqueueMessage(JText::_('EMAIL_SENT'). ' : '.$recipient, 'message');
    }

    //
    // email to labo director
    //
    $email  = $emails->getEmail("csc_new_thesis_info");
    $mailer = JFactory::getMailer();
    $tags = $emails->setTags($user->id, $post, null, '', $email->emailfrom.$email->name.$email->subject.$email->message);

    $from      = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
    $from_id   = 62;
    $fromname  = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
    $recipient = $email_laboratory_director;
    $subject   = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
    $body      = preg_replace($tags['patterns'], $tags['replacements'], $email->message); 
    
    $mailer->setSender($sender);
    $mailer->addRecipient($recipient);
    $mailer->setSubject($subject);
    $mailer->isHTML(true);
    $mailer->Encoding = 'base64';
    $mailer->setBody($body);

    $send = $mailer->Send();

    if ( $send ) {
        $app->enqueueMessage(FText::_('EMAIL SENT'). ' : '.$recipient, 'message');
    }

    //
    // email to ED director
    //
    $email_director = $emails->getEmail("csc_new_thesis_to_validate");
	foreach ($recipients as $referent) {
        $mailer = JFactory::getMailer();

        $post['FIRSTNAME_DIRECTOR'] = $referent->firstname;
        $post['LASTNAME_DIRECTOR']  = $referent->lastname;

		$tags = $emails->setTags($referent->id, $post, null, '', $email_director->emailfrom.$email_director->name.$email_director->subject.$email_director->message);

		// Mail 
		$from      = preg_replace($tags['patterns'], $tags['replacements'], $email_director->emailfrom);
		$from_id   = 62;
		$fromname  = preg_replace($tags['patterns'], $tags['replacements'], $email_director->name);
		$recipient = $referent->email;
		$subject   = preg_replace($tags['patterns'], $tags['replacements'], $email_director->subject);
		$body      = preg_replace($tags['patterns'], $tags['replacements'], $email_director->message); 

		//$attachment[] = $path_file;
		//$replyto = $user->email;
		//$replytoname = $user->name;

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
        if ( $send ) {
            $message = array(
                'user_id_from' => $from_id,
                'user_id_to' => $referent->id,
                'subject' => $subject,
                'message' => $body,
                'email_to' => $recipient
            );
            $emails->logEmail($message);
            $app->enqueueMessage(FText::_('EMAIL SENT'). ' : '.$recipient, 'message');
        }
	}
} else {
    $app->enqueueMessage(FText::_('NO DIRECTOR DECLARED TO VALIDATE YOUR PROPOSAL'), 'error');
}

?>