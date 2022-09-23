<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: IMT_referent_letter.php
 * @package Fabrik
 * @copyright Copyright (C) 2017 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email aux professeurs référents choisis par l'étudiant. C'ette version est utilisé pour envoyer un mail a un seul référent demandant un formulaire et non l'envoie d'une pièce jointe. Spécification Institut Mines Télécom.
 */

jimport( 'joomla.utilities.utility' );
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
//include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
$baseurl = JURI::root();

// Get reference information.
$student_id = $data['jos_emundus_references___user'];
$fnum = $formModel->getElementData('jos_emundus_references___fnum', false, '');
$time_date = $formModel->getElementData('jos_emundus_references___time_date', false, '');

// No attachement IDs and only one email compared to the standard form.
$recipients[] = array('email' => $formModel->getElementData('jos_emundus_references___Email_1', false, ''));


$student = JFactory::getUser($student_id);
$current_user = JFactory::getSession()->get('emundusUser');
if (empty($current_user->fnum) || !isset($current_user->fnum)) {
    $current_user->fnum = $fnum;
}


$db = JFactory::getDBO();

// Récupération des données du mail
try {

    $query = 'SELECT id, subject, emailfrom, name, message
                    FROM #__emundus_setup_emails
                    WHERE lbl="referent_form"';
    $db->setQuery($query);
    $db->execute();
    $obj = $db->loadObject();

} catch (Exception $e) {
    JLog::add('Plugin IMT_reference-letter -> ERROR at query: '.$query, JLog::ERROR, 'com_emundus');
}

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

$m_emails   = new EmundusModelEmails();
$m_profile  = new EmundusModelProfile;

$fnum_detail = $m_profile->getFnumDetails($current_user->fnum);

// setup mail
$app = JFactory::getApplication();
$email_from_sys = $app->getCfg('mailfrom');


$from       = $obj->emailfrom;
$fromname   = $obj->name;
$from_id    = 62;
$to_id      = -1;

// If the email sender has the same domain as the system sender address.
if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
    $mail_from_address = $from;
else
    $mail_from_address = $email_from_sys;

// Set sender
$sender = [
    $mail_from_address,
    $fromname
];


foreach ($recipients as $recipient) {
    if (isset($recipient['email']) && !empty($recipient['email'])) {

        // Does the candidate already have a form filled out to his fnum?
        try {

            $query = 'SELECT count(id)
                        FROM #__emundus_reference_letter
                        WHERE fnum = '.$db->Quote($current_user->fnum);

            $db->setQuery($query);
            $form_filled = $db->loadResult();

        } catch (Exception $e) {
            JLog::add('Plugin IMT_reference-letter -> ERROR at query: '.$query, JLog::ERROR, 'com_emundus');
        }


        if ($form_filled == 0) {
            $key = md5(date('Y-m-d h:m:i').'::'.$fnum.'::'.$student_id.'::'.$fnum_detail['campaign_id'].'::'.rand());

            // 2. MAJ de la table emundus_files_request
            $query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, campaign_id, fnum, attachment_id, email)
                          VALUES (NOW(), '.$student->id.', "'.$key.'", "'.$fnum_detail['campaign_id'].'", '.$current_user->fnum.', 4, '.$db->Quote($recipient['email']).')';
            $db->setQuery( $query );
            $db->execute();


            // 3. Envoi du lien vers lequel le professeur va pouvoir remplir le formulaire de référence
            $link_form = $baseurl.'index.php?option=com_fabrik&c=form&view=form&formid=272&tableid=283&keyid='.$key.'&sid='.$student->id;


            // template replacements (patterns)
            $post = array(
                'REFERENCE_FORM_URL'    => $link_form,
                'CAMPAIGN_LABEL'        => $current_user->campaign_name
            );
            $tags       = $m_emails->setTags($user->id, $post, $current_user->fnum, '', $obj->message);
            $body       = preg_replace($tags['patterns'], $tags['replacements'], $obj->message);
            $body       = $m_emails->setTagsFabrik($body, array($current_user->fnum));
            $subject    = $m_emails->setTagsFabrik($obj->subject, array($current_user->fnum));

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

                if ($send === false) {
                    JLog::add('No mailer set-up!', JLog::ERROR, 'com_emundus');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('MESSAGE_NOT_SENT').' : '.$recipient['email'], 'error');
                    JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
                }

            } else {

                JFactory::getApplication()->enqueueMessage(JText::_('MESSAGE_SENT').' : '.$recipient['email'], 'message');
                try {
                    $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
                                VALUES ('62', '-1', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
                    $db->setQuery( $sql );
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add($sql, JLog::ERROR, 'com_emundus');
                }

            }
            unset($replacements);
            unset($mailer);
        } else {
            JError::raiseWarning(500, JText::_('ERROR: a form has already been submitted for the user','error'));
        }
    }
}

?>