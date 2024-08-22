<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: csc-confirm_post.php 89 2013-09-18 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 D�cision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email � l'�tudiant lors de la validation de son dossier de candidature
 */

$db = JFactory::getDBO();
$student =  JFactory::getSession()->get('emundusUser');
//$mailer = JFactory::getMailer();
include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
include_once(JPATH_BASE.'/components/com_emundus/models/thesis.php');
//include_once(JPATH_BASE.'/components/com_emundus/models/groups.php');
jimport('joomla.log.log');
JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_emundus.email.php'
    ),
    // Sets messages of all log levels to be sent to the file
    JLog::ALL,
    // The log category/categories which should be recorded in this file
    // In this case, it's just the one category from our extension, still
    // we need to put it inside an array
    array('com_emundus')
);

// get current applicant course
$campaigns = new EmundusModelCampaign;
$campaign = $campaigns->getCampaignByID($student->campaign_id);

$thesiss = new EmundusModelThesis;
$thesis = $thesiss->getLastThesisApplied($student->fnum);

$emails = new EmundusModelEmails;

$post = array(  'FNUM'      => $student->fnum,
                'DEADLINE' => JHTML::_('date', $campaign['end_date'], JText::_('DATE_FORMAT_OFFSET1'), null),
                'CAMPAIGN_LABEL' => $campaign['label'],
                'CAMPAIGN_YEAR' => $campaign['year'],
                'CAMPAIGN_START' => JHTML::_('date', $campaign['start_date'], JText::_('DATE_FORMAT_OFFSET1'), null),
                'CAMPAIGN_END' => JHTML::_('date', $campaign['end_date'], JText::_('DATE_FORMAT_OFFSET1'), null),
                'CAMPAIGN_CODE' => $campaign['training'],
                'FIRSTNAME' => $student->firstname,
                'LASTNAME' => strtoupper($student->lastname),
                'THESIS_PROPOSAL' => $thesis->titre,
                'THESIS_DIRECTOR' => $thesis->thesis_supervisor,
                'DOCTORAL_SCHOOL' => $thesis->title
);

// Apllicant cannot delete this attachments now
$query = 'UPDATE #__emundus_uploads SET can_be_deleted = 0 WHERE user_id = '.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery( $query );
try {
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
}

// Insert data in #__emundus_campaign_candidature
$query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW(), status=1 WHERE applicant_id='.$student->id.' AND campaign_id='.$student->campaign_id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery($query);
try {
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
}
$query = 'UPDATE #__emundus_declaration SET time_date=NOW() WHERE user='.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery($query);
try {
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
}

$student->candidature_posted = 1;

// Directeur ED
$ed_directors = JAccess::getUsersByGroup(23);
$query ='SELECT eu.firstname, eu.lastname, u.email, u.id
         FROM #__users as u
         LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
         WHERE u.block!=1 AND u.id IN ('.implode(',', $ed_directors).')
         AND eu.university_id = '.$thesis->doctoral_school;
try {
    $db->setQuery($query);
    $ed_director = $db->loadObjectList();
} catch (Exception $e) {
    // catch any database errors.
}

// Directeur thèse
$query = 'SELECT et.titre, eu.firstname, eu.lastname, u.email, u.id
            FROM #__emundus_thesis as et 
            LEFT JOIN #__users as u ON u.id=et.user 
            LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
            WHERE u.block!=1 AND et.id='.$thesis->thesis_proposal;
try {
    $db->setQuery($query);
    $thesis_director = $db->loadObjectList();
} catch (Exception $e) {
    // catch any database errors.
}

$post['THESIS_DIRECTOR_FIRSTNAME'] = $thesis_director[0]->firstname;
$post['THESIS_DIRECTOR_LASTNAME'] = $thesis_director[0]->lastname;

//$recipients = array_merge($ed_director, $thesis_director);


// Ouverture des droits d'accès aux dossiers du candidat pour le Directeur ED et le Directeur de thèse de l'Université correspondante au sujet
if (count($ed_director) > 0) {
    foreach ($ed_director as $referent) {

        // Association de l'ID user Referent avec le candidat (#__emundus_groups_eval)
        $query = 'INSERT INTO `#__emundus_users_assoc` (`user_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`)
                VALUES  ('.$referent->id.', 1, '.$db->Quote($student->fnum).', 0,1,0,0), 
                        ('.$referent->id.', 4, '.$db->Quote($student->fnum).', 1,1,0,0),
                        ('.$referent->id.', 5, '.$db->Quote($student->fnum).', 0,1,0,0),
                        ('.$referent->id.', 6, '.$db->Quote($student->fnum).', 1,0,0,0),
                        ('.$referent->id.', 7, '.$db->Quote($student->fnum).', 1,0,0,0),
                        ('.$referent->id.', 8, '.$db->Quote($student->fnum).', 1,0,0,0),
                        ('.$referent->id.', 9, '.$db->Quote($student->fnum).', 1,1,0,0),
                        ('.$referent->id.', 10, '.$db->Quote($student->fnum).', 1,1,0,0),
                        ('.$referent->id.', 29, '.$db->Quote($student->fnum).', 1,1,1,0)';
        try {
            $db->setQuery( $query );
            $db->execute();
        } catch (Exception $e) {
            // catch any database errors.
            JLog::add("CSC : Access right ! :: ".$query, JLog::ERROR, 'com_emundus');
        }
        $mail_to[] = $referent->id;
    }
}
else {
    JLog::add("CSC : no ed director found for access right ! :: ".$query, JLog::ERROR, 'com_emundus');
}
if (count($thesis_director) > 0) {
    foreach ($thesis_director as $referent) {

        // Association de l'ID user Referent avec le candidat (#__emundus_groups_eval)
        $query = 'INSERT INTO `#__emundus_users_assoc` (`user_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`)
                VALUES  ('.$referent->id.', 1, '.$db->Quote($student->fnum).', 0,1,0,0), 
                        ('.$referent->id.', 4, '.$db->Quote($student->fnum).', 0,1,0,0),
                        ('.$referent->id.', 5, '.$db->Quote($student->fnum).', 1,1,1,0),
                        ('.$referent->id.', 6, '.$db->Quote($student->fnum).', 1,0,0,0),
                        ('.$referent->id.', 7, '.$db->Quote($student->fnum).', 1,0,0,0),
                        ('.$referent->id.', 8, '.$db->Quote($student->fnum).', 1,0,0,0),
                        ('.$referent->id.', 9, '.$db->Quote($student->fnum).', 1,1,0,0),
                        ('.$referent->id.', 10, '.$db->Quote($student->fnum).', 1,1,0,0),
                        ('.$referent->id.', 29, '.$db->Quote($student->fnum).', 0,1,0,0)';
        try {
            $db->setQuery( $query );
            $db->execute();
        } catch (Exception $e) {
            // catch any database errors.
            JLog::add("CSC : Access right ! :: ".$query, JLog::ERROR, 'com_emundus');
        }
        $mail_to[] = $referent->id;
    }
}
else {
    JLog::add("CSC : no thesis director found for access right ! :: ".$query, JLog::ERROR, 'com_emundus');
}
// Send emails defined in trigger
$emails = new EmundusModelEmails;
$step = 1;
$code = array($student->code);
$to_applicant = '0, 1';
$trigger_emails = $emails->getEmailTrigger($step, $code, $to_applicant);

if (count($trigger_emails) > 0) {

    foreach ($trigger_emails as $key => $trigger_email) {

        foreach ($trigger_email[$student->code]['to']['recipients'] as $key => $recipient) {
            $mailer     = JFactory::getMailer();

            // only for logged user or Theisis director and ED director
            if ($student->id == $recipient['id'] || in_array($recipient['id'], $mail_to)) {
                $tags = $emails->setTags($recipient['id'], $post, $student->fnum, '', $trigger_email[$student->code]['tmpl']['emailfrom'].$trigger_email[$student->code]['tmpl']['name'].$trigger_email[$student->code]['tmpl']['subject'].$trigger_email[$student->code]['tmpl']['message']);

                $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['emailfrom']);
                $from_id = 62;
                $fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['name']);
                $to = $recipient['email'];
                $to_id = $recipient['id'];
                $subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['subject']);
                $body = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['message']);
                $body = $emails->setTagsFabrik($body, array($student->fnum));

                //$attachment[] = $path_file;
                $replyto = $from;
                $replytoname = $fromname;

                // setup mail
                $app    = JFactory::getApplication();
                $email_from_sys = $app->getCfg('mailfrom');
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
                    echo 'Error sending email: ' . $send->__toString();
                    JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
                } else {
                    $message = array(
                        'user_id_from' => $from_id,
                        'user_id_to' => $to_id,
                        'subject' => $subject,
                        'message' => $body,
                        'email_to' => $to
                    );
                    $emails->logEmail($message);
                    JLog::add($to.' '.$body, JLog::INFO, 'com_emundus');
                }

            }
        }
    }
}

?>
