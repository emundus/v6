<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: new_job.php 89 2015-02-18 Benjamin Rivalland
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
$user =  JFactory::getUser();
$mailer = JFactory::getMailer();

//$eMConfig = JComponentHelper::getParams('com_emundus');

$referents = JAccess::getUsersByGroup(23);
$university = $fabrikFormData['doctoral_school_raw'][0];
$titre  =   $fabrikFormData['titre'];

$query ='SELECT eu.firstname, eu.lastname, u.email, u.id, c.title
		 FROM #__users as u 
		 LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id 
         LEFT JOIN #__categories as c ON c.id=eu.university_id 
		 WHERE u.id IN ('.implode(',', $referents).')
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
            'UNIVERSITY'    => $referent[0]->title,
            'THESIS_TITLE'  => $titre
            );
	//$tags = $emails->setTags($user->id, $post);
	$email = $emails->getEmail("csc_new_thesis_to_validate");

	foreach ($recipients as $referent) {
		$tags = $emails->setTags($referent->id, $post);
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

        $config = JFactory::getConfig();
        $sender = array(
            $config->get( $from ),
            $config->get( $fromname )
        );

        $mailer->setSender($sender);
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
                'message' => $body
            );
            $emails->logEmail($message);
        }
	}
}

?>