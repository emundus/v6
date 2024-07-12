<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: csc-evaluation.php 89 2013-09-18 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 D�cision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email en fonction du résulat de l'évaluation et modification du status
 */

$db = JFactory::getDBO();
$student_id = $fabrikFormData['student_id_raw'];
$fnum = $fabrikFormData['fnum_raw'];
$step = $fabrikFormData['criteria01_raw'][0];

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

$query = 'SELECT * 
            FROM #__emundus_campaign_candidature as ecc 
            LEFT JOIN #__emundus_users as eu ON eu.user_id=ecc.applicant_id
            LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id=ecc.campaign_id
            WHERE ecc.applicant_id = '.$student_id.' AND ecc.fnum like '.$db->Quote($fnum);
try {
    $db->setQuery($query);
    $student = $db->loadObject();
    $student->id = $student->applicant_id;
} catch (Exception $e) {
    // catch any database errors.
}

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
                'TITRE' => $thesis->titre,
                'THESIS_DIRECTOR' => $thesis->thesis_supervisor,
                'DOCTORAL_SCHOOL' => $thesis->title
);

// UPDATE data in #__emundus_campaign_candidature
$query = 'UPDATE #__emundus_campaign_candidature SET status='.$step.' WHERE applicant_id='.$student->id.' AND campaign_id='.$student->campaign_id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery($query);
try {
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
}

// Directeur ED
$ed_directors = JAccess::getUsersByGroup(23);
$query ='SELECT eu.firstname, eu.lastname, u.email, u.id
         FROM #__users as u
         LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
         WHERE u.disabled!=1 AND u.id IN ('.implode(',', $ed_directors).')
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
            WHERE u.disabled!=1 AND et.id='.$thesis->thesis_proposal;
try {
    $db->setQuery($query);
    $thesis_director = $db->loadObjectList();
} catch (Exception $e) {
    // catch any database errors.
}

$post['THESIS_DIRECTOR_FIRSTNAME'] = $thesis_director[0]->firstname;
$post['THESIS_DIRECTOR_LASTNAME'] = $thesis_director[0]->lastname;
$post['LABORATORY_DIRECTOR'] = $thesis_director[0]->firstname.' '.$thesis_director[0]->lastname;

if (count($ed_director) > 0) {
    foreach ($ed_director as $referent) {
        $mail_to[] = $referent->id;
    }
}
if (count($thesis_director) > 0) {
    foreach ($thesis_director as $referent) {
        $mail_to[] = $referent->id;
    }
}

// Send emails defined in trigger
$emails = new EmundusModelEmails;

$code = array($student->training);
$to_applicant = '0, 1';
$trigger_emails = $emails->getEmailTrigger($step, $code, $to_applicant);

if (count($trigger_emails) > 0) {

    foreach ($trigger_emails as $key => $trigger_email) {

        foreach ($trigger_email[$student->training]['to']['recipients'] as $key => $recipient) {
            $mailer     = JFactory::getMailer();

            // only for logged user or Theisis director and ED director
            if ($student->id == $recipient['id'] || in_array($recipient['id'], $mail_to)) {
                $tags = $emails->setTags($recipient['id'], $post, $student->fnum, '', $trigger_email[$student->training]['tmpl']['emailfrom'].$trigger_email[$student->training]['tmpl']['name'].$trigger_email[$student->training]['tmpl']['subject'].$trigger_email[$student->training]['tmpl']['message']);

                $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->training]['tmpl']['emailfrom']);
                $from_id = 62;
                $fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->training]['tmpl']['name']);
                $to = $recipient['email'];
                $to_id = $recipient['id'];
                $subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->training]['tmpl']['subject']);
                $body = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->training]['tmpl']['message']);

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
                    JFactory::getApplication()->enqueueMessage(JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to);
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
