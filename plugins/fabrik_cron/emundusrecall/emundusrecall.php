<?php
/**
 * A cron task to email a recall to incomplet applications
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgFabrik_Cronemundusrecall extends PlgFabrik_Cron
{
	/**
	 * Check if the user can use the plugin
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
	 */

	public function canUse($location = null, $event = null)
	{
		return true;
	}

	/**
	 * Do the plugin action
	 *
	 * @param   array  &$data  data
	 *
	 * @return  int  number of records updated
	 */

	public function process(&$data) {
		$app = JFactory::getApplication();
		jimport('joomla.mail.helper');

		$reminder_mail_id = $app->getCfg('reminder_mail_id', '15');
		$reminder_programme_code = $app->getCfg('reminder_programme_code', '');
		$reminder_days = $app->getCfg('reminder_days', '30');
		$reminder_deadline = $app->getCfg('reminder_deadline', '30, 15, 7, 1, 0');

		$this->log = '';

// Get list of applicants to notify
		$db = FabrikWorker::getDbo();
		$query = 'SELECT u.id, u.email, eu.firstname, eu.lastname, ecc.fnum, esc.start_date, esc.end_date, esc.label, DATEDIFF( esc.end_date , now()) as left_days
					FROM #__emundus_campaign_candidature as ecc
					LEFT JOIN #__users as u ON u.id=ecc.applicant_id
					LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
					LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id=ecc.campaign_id
					WHERE ecc.status = 0 AND u.block = 0 AND esc.published = 1 AND DATEDIFF( esc.end_date , now()) IN ('.$reminder_deadline.')';

		if (isset($reminder_programme_id) && !empty($reminder_programme_id))
			$query .= ' AND esc.training IN ('.$reminder_programme_code.')';

		$db->setQuery($query);

		$applicants = $db->loadObjectList();

		// Generate emails from template and store it in message table
		if (count($applicants > 0)) {
			include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
			$emails = new EmundusModelEmails;
			$email = $emails->getEmailById($reminder_mail_id);

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
				$tags = $emails->setTags($applicant->id, $post);

				$from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
                $from_id = 62;
                $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
                $to = $applicant->email;
                $to_id = $applicant->id;
                $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
                $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);

                //$attachment[] = $path_file;
                $replyto = $from;
                $replytoname = $fromname;

                $config = JFactory::getConfig();
                $email_from_sys = $config->get('mailfrom');
				$email_from = $email->emailfrom;

				// If the email sender has the same domain as the system sender address.
				if (!empty($email_from) && substr(strrchr($email_from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
					$mail_from_address = $email_from;
				else
					$mail_from_address = $email_from_sys;

				// Set sender
				$sender = [
					$mail_from_address,
					$fromname
				];

                $mailer->setSender($sender);
                $mailer->addRecipient($to);
                $mailer->setSubject($subject);
                $mailer->isHTML(true);
                $mailer->Encoding = 'base64';
                $mailer->setBody($body);

				// Send emails
               	$send = $mailer->Send();
                if ($send !== true) {
                    $this->log .= "\n Error sending email : " . $to;
                } else {
                    $message = array(
                        'user_id_from' => $from_id,
                        'user_id_to' => $to_id,
                        'subject' => $subject,
                        'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                    );
                    $emails->logEmail($message);
                    $this->log .= '\n' . JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.' :: '.$body;
                }

			}
		}

		$this->log .= "\n process " . count($applicants) . " applicant(s)";

		return count($applicants);
	}
}
