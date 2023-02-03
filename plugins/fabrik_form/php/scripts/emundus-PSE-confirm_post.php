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
$student = JFactory::getSession()->get('emundusUser');
$mailer = JFactory::getMailer();
$app    = JFactory::getApplication();
$email_from_sys = $app->getCfg('mailfrom');
include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
include_once(JPATH_BASE.'/components/com_emundus/models/groups.php');

$eMConfig = JComponentHelper::getParams('com_emundus');
$alert_new_applicant = $eMConfig->get('alert_new_applicant');


$references_id = array(4, 6, 21, 19); // document ID from #__emundus_setup_attachments

// get current applicant course
$campaigns = new EmundusModelCampaign;
$campaign = $campaigns->getCampaignByID($student->campaign_id);

$emails = new EmundusModelEmails;

$post = array(  'DEADLINE' => JHTML::_('date', $campaign['end_date'], JText::_('DATE_FORMAT_OFFSET1'), null),
				'APPLICANTS_LIST' => '',
				'EVAL_CRITERIAS' => '',
				'EVAL_PERIOD' => '',
				'CAMPAIGN_LABEL' => $campaign['label'],
				'CAMPAIGN_YEAR' => $campaign['year'],
				'CAMPAIGN_START' => JHTML::_('date', $campaign['start_date'], JText::_('DATE_FORMAT_OFFSET1'), null),
				'CAMPAIGN_END' => JHTML::_('date', $campaign['end_date'], JText::_('DATE_FORMAT_OFFSET1'), null),
				'CAMPAIGN_CODE' => $campaign['training']
			);

$email = $emails->getEmail("confirm_post");
$tags = $emails->setTags($student->id, $post, $student->fnum, '', $email->message);

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
$db->setQuery('SELECT count(id) FROM #__emundus_uploads WHERE fnum like '.$db->Quote($student->fnum).' AND attachment_id IN ('.implode(',', $references_id).')');
$nb_references=$db->loadResult();


if ($nb_references >= 2 || strpos($campaign['training'], "summer") !== false)
	$db->setQuery('UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW(), status=2 WHERE fnum like '.$db->Quote($student->fnum));
else
	$db->setQuery('UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW(), status=1 WHERE fnum like '.$db->Quote($student->fnum));
/*
$query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW(), status=1 WHERE applicant_id='.$student->id.' AND campaign_id='.$student->campaign_id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery($query);*/
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
if ($send !== true) {
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
if ($alert_new_applicant == 1) {
	// get evaluators groups for current applicant course
	$groups = new EmundusModelGroups;
	$group_list = $groups->getGroupsIdByCourse($campaign['training']);

	// Link groups to current application
	$groups->affectEvaluatorsGroups($group_list, $student->id);

	// Alert by email evaluators
	// get evaluator list
	$evaluators = $groups->getUsersByGroups($group_list);

	$email = $emails->getEmail("new_applicant");
	if (count($evaluators) > 0) {
		foreach ($evaluators as $evaluator) {
			$mailer = JFactory::getMailer();

			$eval_user = & JFactory::getUser($evaluator);
			$tags = $emails->setTags($eval_user->id, $post, null, '', $email->message);
			// Mail 
			$from = $email->emailfrom;
			$from_id = 62;
			$fromname =$email->name;
			$recipient = $eval_user->email; 
			$subject = $email->subject;
			//$body = preg_replace($patterns, $replacements, $email->message);
			$body = preg_replace($tags['patterns'], $tags['replacements'], $email->message); 
			$mode = 1;

			//$attachment[] = $path_file;
			$replyto = $email->emailfrom;
			$replytoname = $email->name;

			$student->candidature_posted = 1;

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
                $sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
							VALUES ('".$from_id."', '".$eval_user->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
                $db->setQuery( $sql );
                try {
                    $db->execute();
                } catch (Exception $e) {
                    // catch any database errors.
                }
            }
		}
	}
}
?>