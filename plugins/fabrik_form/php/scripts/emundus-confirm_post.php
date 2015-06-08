<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: confirm_post.php 89 2013-09-18 Benjamin Rivalland
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
$student =  JFactory::getUser();
//$mailer = JFactory::getMailer();
include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
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

//$eMConfig = JComponentHelper::getParams('com_emundus');
//$alert_new_applicant = $eMConfig->get('alert_new_applicant');

// get current applicant course
$campaigns = new EmundusModelCampaign;
$campaign = $campaigns->getCampaignByID($student->campaign_id);

$emails = new EmundusModelEmails;

$post = array(  'DEADLINE' => strftime("%A %d %B %Y %H:%M", strtotime($campaign['end_date'])),
                'APPLICANTS_LIST' => '',
                'EVAL_CRITERIAS' => '',
                'EVAL_PERIOD' => '',
                'CAMPAIGN_LABEL' => $campaign['label'],
                'CAMPAIGN_YEAR' => $campaign['year'],
                'CAMPAIGN_START' => $campaign['start_date'],
                'CAMPAIGN_END' => $campaign['end_date'],
                'CAMPAIGN_CODE' => $campaign['training']
);
/*
$tags = $emails->setTags($student->id, $post);
$email = $emails->getEmail("confirm_post");
*/
// Apllicant cannot delete this attachments now
$query = 'UPDATE #__emundus_uploads SET can_be_deleted = 0 WHERE user_id = '.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery( $query );
try {
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
}

// Confirm candidature
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
/*
// Mail 
$from = $email->emailfrom;
$from_id = 62;
$fromname =$email->name;
$recipient[] = $student->email;
$subject = $email->subject;
//$body = preg_replace($patterns, $replacements, $email->message);
$body = preg_replace($tags['patterns'], $tags['replacements'], $email->message); 
$mode = 1;

//$attachment[] = $path_file;
$replyto = $email->emailfrom;
$replytoname = $email->name;

$student->candidature_posted = 1;

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
    $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
				VALUES ('".$from_id."', '".$student->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
    $db->setQuery( $sql );
    try {
        $db->execute();
    } catch (Exception $e) {
        // catch any database errors.
    }
}

unset($recipient);

if ($alert_new_applicant==1) {


// Alert by email evaluators
// get evaluator list
	$evaluators = $groups->getUsersByGroups($group_list);

	$email = $emails->getEmail("new_applicant");
	if (count($evaluators) > 0) {
		foreach ($evaluators as $evaluator) {
			$eval_user = &JFactory::getUser($evaluator);
			$tags = $emails->setTags($eval_user->id, $post);
			// Mail
			$from = $email->emailfrom;
			$from_id = 62;
			$fromname = $email->name;
			$recipient = $eval_user->email;
			$subject = $email->subject;
			//$body = preg_replace($patterns, $replacements, $email->message);
			$body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
			$mode = 1;

			//$attachment[] = $path_file;
			$replyto = $email->emailfrom;
			$replytoname = $email->name;
            $student->candidature_posted = 1;

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
                $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
						VALUES ('" . $from_id . "', '" . $eval_user->id . "', " . $db->quote($subject) . ", " .
                    $db->quote($body) . ", NOW())";
                $db->setQuery($sql);
                try {
                    $db->execute();
                } catch (Exception $e) {
                    // catch any database errors.
                }
            }
		}
	}
}

// get evaluators groups for current applicant course
$groups = new EmundusModelGroups;
$group_list = $groups->getGroupsIdByCourse($campaign['training']);

// Link groups to current application
$groups->affectEvaluatorsGroups($group_list, $student->id);

*/
// Send emails defined in trigger
$emails = new EmundusModelEmails;
$step = 1;
$code = array($student->code);
$to_applicant = '0, 1';
$trigger_emails = $emails->getEmailTrigger($step, $code, $to_applicant);
//var_dump($trigger_emails); die();
if (count($trigger_emails) > 0) {

    foreach ($trigger_emails as $key => $trigger_email) {

        foreach ($trigger_email[$student->code]['to']['recipients'] as $key => $recipient) {
            $mailer     = JFactory::getMailer();

            //$post = array();
            $tags = $emails->setTags($recipient['id'], $post);

            $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['emailfrom']);
            $from_id = 62;
            $fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['name']);
            $to = $recipient['email'];
            $to_id = $recipient['id'];
            $subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['subject']);
            $body = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['message']);

            //$attachment[] = $path_file;
            $replyto = $from;
            $replytoname = $fromname;

            $config = JFactory::getConfig();
            $sender = array(
                $config->get( $from ),
                $config->get( $fromname )
            );

            $mailer->setSender($sender);
            $mailer->addRecipient($to);
            $mailer->setSubject($subject);
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);

            $send = $mailer->Send();
            if ( $send !== true ) {
                echo 'Error sending email: ' . $send->__toString();
                JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
                die();
            } else {
                $message = array(
                    'user_id_from' => $from_id,
                    'user_id_to' => $to_id,
                    'subject' => $subject,
                    'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                );
                $emails->logEmail($message);
                JLog::add($to.' '.$body, JLog::INFO, 'com_emundus');
            }
        }
    }
}

?>