<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: new_job.php 89 2015-02-26 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2015 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email aux déposants d'une fiche emploi pour les informer d'un nouveau candidat
 */


include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
//include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
//include_once(JPATH_BASE.'/components/com_emundus/models/groups.php');

$db 		= JFactory::getDBO();
$student 	= JFactory::getUser();


//$eMConfig = JComponentHelper::getParams('com_emundus');


// Confirm candidature
// Insert data in #__emundus_campaign_candidature
$query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW(), status=1 WHERE applicant_id='.$student->id.' AND campaign_id='.$student->campaign_id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery($query);
try {
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
}
echo $query;
$query = 'UPDATE #__emundus_declaration SET time_date=NOW() WHERE user='.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery($query);
try {
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
}

// Université du poste sélectionné par le candidat
$query ='SELECT etablissement
		 FROM #__emundus_emploi_etudiant_candidat
		 WHERE fnum like '.$db->Quote($student->fnum);
try {
    $db->setQuery($query);
    $university = $db->loadResult();
} catch (Exception $e) {
    // catch any database errors.
}

// Liste des déposants de fiche emplois
$deposant = JAccess::getUsersByGroup(16);
$referents = JAccess::getUsersByGroup(17);
$query ='SELECT eu.firstname, eu.lastname, u.email, u.id
		 FROM #__users as u
		 LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
		 WHERE u.id IN ('.implode(',', $referents).')
		 AND eu.university_id = '.$university;
try {
    $db->setQuery($query);
    $referents = $db->loadObjectList();
} catch (Exception $e) {
    // catch any database errors.
}
$query ='SELECT eu.firstname, eu.lastname, u.email, u.id
		 FROM #__users as u
		 LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
		 WHERE u.id IN ('.implode(',', $deposant).') 
		 AND eu.university_id = '.$university;
try {
    $db->setQuery($query);
    $deposants = $db->loadObjectList();
} catch (Exception $e) {
    // catch any database errors.
}

$recipients = array_merge($referents, $deposants);

// Ouverture des droits d'accès aux dossiers du candidat pour le Déposant et le Référent de l'Université correspondante à l'offre d'emploi
if (count($recipients) > 0) {
    foreach ($recipients as $referent) {

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
						('.$referent->id.', 13, '.$db->Quote($student->fnum).', 0,1,1,0),
						('.$referent->id.', 14, '.$db->Quote($student->fnum).', 1,1,1,0)';
        $db->setQuery( $query );
        $db->execute();
    }
}

// Send emails defined in trigger
$emails = new EmundusModelEmails;
$step = 1;
$code = array($student->code);
$to_applicant = 0;
$trigger_emails = $emails->getEmailTrigger($step, $code, $to_applicant);

if (count($trigger_emails) > 0) {

    foreach ($trigger_emails as $key => $trigger_email) {
        /*if ($trigger_email[$student->code]['to']['to_current_user']) {
            $post = array();
            $tags = $emails->setTags($student->id, $post);

            $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['emailfrom']);
            $from_id = 62;
            $fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['name']);
            $to = $student->email;
            $subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['subject']);
            $body = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['message']);
            $mode = 1;
            //$attachment[] = $path_file;
            $replyto = $from;
            $replytoname = $fromname;

            $res = JUtility::sendMail( $from, $fromname, $to, $subject, $body, true );
            if($res){
                $message = array(
                    'user_id_from' => $from_id,
                    'user_id_to' => $student->id,
                    'subject' => $subject,
                    'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                );
                $emails->logEmail($message);
            }
        }

*/
        foreach ($trigger_email[$student->code]['to']['recipients'] as $key => $recipient) {
            // only for logged user or Deposant
            if ($student->id == $recipient['id'] || in_array($recipient['id'], $deposant)) {

                $post = array();
                $tags = $emails->setTags($recipient['id'], $post);

                $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['emailfrom']);
                $from_id = 62;
                $fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['name']);
                $to = $recipient['email'];
                $subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['subject']);
                $body = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['message']);
                $mode = 1;
                //$attachment[] = $path_file;
                $replyto = $from;
                $replytoname = $fromname;

                $res = JFactory::getMailer()->sendMail( $from, $fromname, $to, $subject, $body, true );

                if($res){
                    $message = array(
                        'user_id_from' => $from_id,
                        'user_id_to' => $student->id,
                        'subject' => $subject,
                        'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                    );
                    $emails->logEmail($message);
                }
            }
        }
    }
}

?>