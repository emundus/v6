<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: validate_job.php 89 2015-03-18 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2015 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email au déposant pour mentionner une validation de la fiche emploi
 */


include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
include_once(JPATH_BASE.'/components/com_emundus/helpers/files.php');

$db = JFactory::getDBO();
$user =  JFactory::getUser();
$mailer = JFactory::getMailer();
$app    = JFactory::getApplication();
$email_from_sys = $app->getCfg('mailfrom');

//$eMConfig = JComponentHelper::getParams('com_emundus');

$deposant = $fabrikFormData['user_raw'][0];
$deposant =  JFactory::getUser($deposant);
$university = $fabrikFormData['etablissement_raw'][0];
$valide = $fabrikFormData['valide_raw'][0];
$valide_comite = $fabrikFormData['valide_comite_raw'][0];
$intitule_poste = $fabrikFormData['intitule_poste_raw'];

$elements_valide = @EmundusHelperFiles::getElementsValuesOther(2280);
$i=0;
foreach ($elements_valide->sub_values as $key => $value) {
    if ($value == $valide) {
        $valide = $elements_valide->sub_labels[$i];
        break;
    }
    $i++;
}
$elements_valide_comite = @EmundusHelperFiles::getElementsValuesOther(3872);
$i=0;
foreach ($elements_valide_comite->sub_values as $key => $value) {
    if ($value == $valide_comite) {
        $valide_comite = $elements_valide_comite->sub_labels[$i];
        break;
    }
    $i++;
}

$emails = new EmundusModelEmails;

$post = array(  'FICHE_EMPLOI'              => $intitule_poste,
                'FICHE_EMPLOI_VALIDE'       => $valide,
                'FICHE_EMPLOI_VALIDE_COMITE'=> $valide_comite
);
$email = $emails->getEmail("validate_job");

$tags = $emails->setTags($deposant->id, $post, null, '', $email->message);
// Mail
$from = $email->emailfrom;
$from_id = 62;
$fromname =$email->name;
$recipient = $deposant->email;
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

if (isset($recipient) && !empty($recipient)) {
    $mailer->setSender($sender);
    $mailer->addReplyTo($from, $fromname);
    $mailer->addRecipient($recipient);
    $mailer->setSubject($subject);
    $mailer->isHTML(true);
    $mailer->Encoding = 'base64';
    $mailer->setBody($body);

    $send = $mailer->Send();
    if ( $send !== true ) {
        echo 'Error sending email: TO '.$recipient.' FROM '.$from.' ' . $send->__toString(); die();
    } else {
        $message = array(
            'user_id_from' => $from_id,
            'user_id_to' => $deposant->id,
            'subject' => $subject,
            'message' => $body,
            'email_to' => $recipient
        );
        $emails->logEmail($message);
    }
}
?>