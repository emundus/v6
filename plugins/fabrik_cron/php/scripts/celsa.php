<?php

/**
 * @package     Joomla.Plugin
 * Send a mail to all candidates that have not sent their application
 * 
 */

jimport('joomla.mail.helper');


if (!empty($params)) {
	$eMConfig = JComponentHelper::getParams('com_emundus');
	$reminder_mail_id = '15';
	$reminder_programme_code = '';
	$reminder_days = '15';
	$reminder_choice =  '1';
	$reminder_deadline = '15, 7, 1, 0';
	$status_for_send = $eMConfig->get('status_for_send', 0);

	if(!isset($status_for_send)) {
        return false;
    }

	$db = JFactory::getDbo();
	$query = 'SELECT u.id, u.email, eu.firstname, eu.lastname, ecc.fnum, ecc.status, jesc775r.acces_start_date, jesc775r.acces_end_date, ecc.data_voie_d_acces,  esc.label, DATEDIFF( esc.end_date , now()) as left_days
    FROM jos_emundus_campaign_candidature as ecc
    LEFT JOIN jos_users as u ON u.id=ecc.applicant_id
    LEFT JOIN jos_emundus_users as eu ON eu.user_id=u.id
    LEFT JOIN jos_emundus_setup_campaigns as esc ON esc.id = ecc.campaign_id
    LEFT JOIN jos_emundus_setup_campaigns_775_repeat jesc775r on esc.id = jesc775r.parent_id
    WHERE ecc.published = 1 AND u.block = 0 AND esc.published = 1 AND ecc.status in ('.$status_for_send.') AND jesc775r.acces_end_date > NOW() AND DATEDIFF(jesc775r.acces_end_date , now()) IN ('.$reminder_deadline.') AND jesc775r.voie_d_acces_assoc = ecc.data_voie_d_acces';

	$db->setQuery($query);
	$applicants = $db->loadObjectList();

	if (!empty($applicants)) {
		include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
		include_once(JPATH_SITE.'/components/com_emundus/models/messages.php');

		$m_emails = new EmundusModelEmails;
		$m_messages = new EmundusModelMessages;
		$email = $m_messages->getEmail($reminder_mail_id);
		foreach ($applicants as $applicant) {
			$mailer = JFactory::getMailer();

			$post = array(
				'FNUM' => $applicant->fnum,
				'DEADLINE' => strftime("%A %d %B %Y %H:%M", strtotime($applicant->end_date)),
				'CAMPAIGN_LABEL' => $applicant->label,
				'CAMPAIGN_START' => $applicant->start_date,
				'CAMPAIGN_END' => $applicant->end_date,
				'FIRSTNAME' => $applicant->firstname,
				'LASTNAME' => strtoupper($applicant->lastname)
			);
			$tags = $m_emails->setTags($applicant->id, $post, $applicant->fnum, '', $email->emailfrom.$email->name.$email->subject.$email->message);

			$from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
			$from_id = 62;
			$fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
			$to = $applicant->email;
			$to_id = $applicant->id;
			$subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
			$body = $email->message;
			$body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $email->Template);
			$body = preg_replace($tags['patterns'], $tags['replacements'], $body);
			$body = $m_emails->setTagsFabrik($body, [$applicant->fnum]);

			$config = JFactory::getConfig();
			$email_from_sys = $config->get('mailfrom');
			$email_from = $email->emailfrom;

			// If the email sender has the same domain as the system sender address.
			if (!empty($email_from) && substr(strrchr($email_from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
				$mail_from_address = $email_from;
			} else {
				$mail_from_address = $email_from_sys;
			}

			// Set sender
			$sender = [
				$mail_from_address,
				$fromname
			];

			$mailer->setSender($sender);
			$mailer->addReplyTo($from, $fromname);
			$mailer->addRecipient($to);
			$mailer->setSubject($subject);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($body);

			// Send emails
			$send = $mailer->Send();
			if ($send !== true) {
				JLog::add("\n Error sending email : $to", JLog::ERROR, 'com_emundus');
			} else {
				$message = array(
					'user_id_from' => $from_id,
					'user_id_to' => $to_id,
					'subject' => $subject,
					'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body,
					'email_id' => $reminder_mail_id
				);
				$m_emails->logEmail($message, $applicant->fnum);
			}
			// to avoid been considered as a spam process or DDoS
			sleep(0.1);
		}

		JLog::add('Process  ' . count($applicants) . ' applicant(s)', JLog::NOTICE, 'com_emundus');
		return count($applicants);
	}
} else {
	return 0;
}
