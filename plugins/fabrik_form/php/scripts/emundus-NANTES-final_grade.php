<?php
defined('_JEXEC') or die();
/**
 * @version 1: final_grade.php 89 2015-06-15 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Validation finale du dossier de candidature
 */

$db = JFactory::getDBO();
$query = $db->getQuery(true);
$jinput	= JFactory::getApplication()->input->post;

$fnum = $jinput->get('jos_emundus_final_grade___fnum');
$status = $jinput->get('jos_emundus_final_grade___final_grade')[0];

if (!empty($status)) {

	jimport('joomla.log.log');
	JLog::addLogger(['text_file' => 'com_emundus.finalGrade.php'], JLog::ALL, ['com_emundus']);

	if ($status == '11' && $jinput->get('jos_emundus_final_grade___va_accepted') == 'Oui') {
		$status = '2';
	}

	$query->select($db->quoteName(['tu.max_occupants', 'cc.campaign_id', 'c.training']))
		->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
		->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('cc.campaign_id'))
		->leftJoin($db->quoteName('#__emundus_setup_teaching_unity', 'tu').' ON '.$db->quoteName('tu.code').' = '.$db->quoteName('c.training'))
		->where($db->quoteName('cc.fnum').' LIKE '.$db->quote($fnum));
	$db->setQuery($query);

	try {
		$res = $db->loadObject();
	} catch (Exception $e) {
		JLog::add('Error getting information about fnum in plugin/final_grade at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		return false;
	}

	// Manage accepted applications in case the capacity of the class is full.
	if ($status == '2' && !empty($res->max_occupants)) {

		// If user is from program FCESHU or FCSEXO then the $occupants needs to be from all of THOSE cc entries combined.
		if ($res->training == 'FCESHU' || $res->training == 'FCSEXO') {
			$query->clear()->select($db->quoteName('cc.status'))
				->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
				->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('cc.campaign_id'))
				->where($db->quoteName('c.training').' IN ('.$db->quote('FCESHU').', '.$db->quote('FCSEXO').') AND '.$db->quoteName('cc.status').' IN (2,9,12,13,14)');
		} else {
			// To get the number of people registered in a class : count all status 2.
			$query->clear()->select($db->quoteName('cc.status'))
				->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
				->where($db->quoteName('cc.campaign_id').' = '.$res->campaign_id.' AND '.$db->quoteName('cc.status').' IN (2,9,12,13,14)');
		}

		$db->setQuery($query);
		try {
			$occupants = count($db->loadColumn());
		} catch (Exception $e) {
			JLog::add('Error getting information about occupants in plugin/final_grade at query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}

		$status = ($occupants >= $res->max_occupants)?'8':'2';
	}

	$query->clear()->update($db->quoteName('#__emundus_campaign_candidature'))
			->set($db->quoteName('status').' = '.$status)
			->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));

	try {

		$db->setQuery($query);
		$db->execute();

	} catch(Exception $e) {
		JLog::add('Unable to set status in plugin/emundusFinalGrade at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
	}

	// Get triggered email
	include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
	include_once(JPATH_BASE.'/components/com_emundus/models/files.php');
	$m_email = new EmundusModelEmails;
	$m_files = new EmundusModelFiles;
	$trigger_emails = $m_email->getEmailTrigger($status, array($res->training), 1);

	if (count($trigger_emails) > 0) {

		$fnumsInfos = $m_files->getFnumsInfos([$fnum]);

		foreach ($trigger_emails as $trigger_email) {

			// Manage with default recipient by programme
			foreach ($trigger_email as $trigger) {

				if ($trigger['to']['to_applicant'] == 1) {

					// Manage with selected fnum
					foreach ($fnumsInfos as $file) {
						$mailer = JFactory::getMailer();

						$post = array('FNUM' => $file['fnum']);
						$tags = $m_email->setTags($file['applicant_id'], $post, $file['fnum'], $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

						$from = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
						$from_id = 62;
						$fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
						$to = $file['email'];
						$subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
						$body = $trigger['tmpl']['message'];

						// Add the email template model.
						if (!empty($trigger['tmpl']['template'])) {
							$body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $trigger['tmpl']['template']);
						}
						$body = preg_replace($tags['patterns'], $tags['replacements'], $body);
						$body = $m_email->setTagsFabrik($body, array($file['fnum']));

						// If the email sender has the same domain as the system sender address.
						if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
							$mail_from_address = $from;
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

						$send = $mailer->Send();
						if ($send !== true) {
							$msg .= '<div class="alert alert-dismissable alert-danger">'.JText::_('EMAIL_NOT_SENT').' : '.$to.' '.$send->__toString().'</div>';
							JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
						} else {
							$message = array(
								'user_id_from' => $from_id,
								'user_id_to' => $file['applicant_id'],
								'subject' => $subject,
								'message' => $body,
                                'email_to' => $to
							);
							$m_email->logEmail($message);
							$msg .= JText::_('EMAIL_SENT').' : '.$to.'<br>';
							JLog::add($to.' '.$body, JLog::INFO, 'com_emundus');
						}
					}
				}

				foreach ($trigger['to']['recipients'] as $recipient) {
					$mailer = JFactory::getMailer();

					$post = array();
					$tags = $m_email->setTags($recipient['id'], $post, $fnum, $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

					$from = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
					$from_id = 62;
					$fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
					$to = $recipient['email'];
					$subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
					$body = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['message']);
					$body = $m_email->setTagsFabrik($body, [$fnum]);

					// If the email sender has the same domain as the system sender address.
					if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
						$mail_from_address = $from;
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

					$send = $mailer->Send();
					if ($send !== true) {
						$msg .= '<div class="alert alert-dismissable alert-danger">'.JText::_('EMAIL_NOT_SENT').' : '.$to.' '.$send->__toString().'</div>';
						JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
					} else {
						$message = array(
							'user_id_from' => $from_id,
							'user_id_to' => $recipient['id'],
							'subject' => $subject,
							'message' => $body,
                            'email_to' => $to
						);
						$m_email->logEmail($message);
						$msg .= JText::_('EMAIL_SENT').' : '.$to.'<br>';
						JLog::add($to.' '.$body, JLog::INFO, 'com_emundus');
					}
				}
			}
		}
	}
}
