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

class PlgFabrik_Cronemundusrecall extends PlgFabrik_Cron {

    /**
     * Check if the user can use the plugin
     *
     * @param   string  $location  To trigger plugin on
     * @param   string  $event     To trigger plugin on
     *
     * @return  bool can use or not
     */
    public function canUse($location = null, $event = null) {
        return true;
    }

    /**
     * Do the plugin action
     *
     * @param array  &$data data
     *
     * @return  int  number of records updated
     * @throws Exception
     */
    public function process(&$data, &$listModel) {
        jimport('joomla.mail.helper');

        $params = $this->getParams();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $reminder_mail_id = $params->get('reminder_mail_id', '15');
        $reminder_programme_code = $params->get('reminder_programme_code', '');
        $reminder_days = $params->get('reminder_days', '30');
        $reminder_choice = $params->get('reminder_option', '1');
        $reminder_deadline = $params->get('reminder_deadline', '30, 15, 7, 1, 0');

        $status_for_send = $params->get('reminder_status', '');
        if ($status_for_send === "") {
            $status_for_send = $eMConfig->get('status_for_send', 0);
        }

        if(strlen($status_for_send) == 0){
            return false;
        }

        $this->log = '';

        // Get list of applicants to notify
        $db = FabrikWorker::getDbo();
        if($reminder_choice == 1){
            $query = 'SELECT u.id, u.email, eu.firstname, eu.lastname, ecc.fnum, ecc.status, IF (ecw.start_date IS NULL OR ecw.start_date = \'0000-00-00 00:00:00\', esc.start_date, ecw.start_date) AS start_date, IF (ecw.end_date IS NULL OR ecw.end_date = \'0000-00-00 00:00:00\', esc.end_date, ecw.end_date) AS end_date, esc.label
                    FROM #__emundus_campaign_candidature as ecc
                    LEFT JOIN #__users as u ON u.id = ecc.applicant_id
                    LEFT JOIN #__emundus_users as eu ON eu.user_id = u.id
                    LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id = ecc.campaign_id
                    LEFT JOIN #__emundus_campaign_workflow_repeat_entry_status ecwres on ecwres.entry_status = ecc.status
                    LEFT JOIN #__emundus_campaign_workflow_repeat_campaign ecwrc ON ecwrc.parent_id = ecwres.parent_id
                    LEFT JOIN #__emundus_campaign_workflow ecw ON ecw.id = ecwres.parent_id
                    WHERE ecc.published = 1
                    AND u.block = 0
                    AND esc.published = 1
                    AND IF (ecwrc.campaign IS NOT NULL, ecwrc.campaign = ecc.campaign_id, true)
                    AND ecc.status in ('.$status_for_send.')
                    GROUP BY ecc.fnum
                    HAVING (DATEDIFF(end_date,now()) IN ('.$reminder_deadline.'))';
        }
        else{
            $query = 'SELECT u.id, u.email, eu.firstname, eu.lastname, ecc.fnum, ecc.status, IF (ecw.start_date IS NULL OR ecw.start_date = \'0000-00-00 00:00:00\', esc.start_date, ecw.start_date) AS start_date, IF (ecw.end_date IS NULL OR ecw.end_date = \'0000-00-00 00:00:00\', esc.end_date, ecw.end_date) AS end_date, esc.label
                    FROM #__emundus_campaign_candidature as ecc
                    LEFT JOIN #__users as u ON u.id = ecc.applicant_id
                    LEFT JOIN #__emundus_users as eu ON eu.user_id = u.id
                    LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id = ecc.campaign_id
                    LEFT JOIN #__emundus_campaign_workflow_repeat_entry_status ecwres on ecwres.entry_status = ecc.status
                    LEFT JOIN #__emundus_campaign_workflow_repeat_campaign ecwrc ON ecwrc.parent_id = ecwres.parent_id
                    LEFT JOIN #__emundus_campaign_workflow ecw ON ecw.id = ecwres.parent_id
					WHERE ecc.published = 1
                    AND u.block = 0
                    AND esc.published = 1
                    AND IF (ecwrc.campaign IS NOT NULL, ecwrc.campaign = ecc.campaign_id, true)
                    AND ecc.status in ('.$status_for_send.')
                    GROUP BY ecc.fnum
                    HAVING DAY(now()) IN ('.$reminder_deadline.') AND end_date > now()';
        }

	    if (!empty($reminder_programme_code)) {
		    $reminder_programme_code = explode(',',$reminder_programme_code);
		    foreach ($reminder_programme_code as $key => $code) {
			    $reminder_programme_code[$key] = $db->quote($code);
		    }
		    $query .= ' AND esc.training IN ('.implode(',',$reminder_programme_code).')';
	    }

        $db->setQuery($query);
        $applicants = $db->loadObjectList();

        // Generate emails from template and store it in message table
        if (!empty($applicants)) {
            include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
            include_once(JPATH_SITE.'/components/com_emundus/models/messages.php');
            include_once(JPATH_SITE.'/components/com_emundus/helpers/date.php');
            $m_emails = new EmundusModelEmails;
            $m_messages = new EmundusModelMessages;
            $h_date = new EmundusHelperDate;
            $email = $m_messages->getEmail($reminder_mail_id);

            foreach ($applicants as $applicant) {
                $mailer = JFactory::getMailer();

                $start_date = $applicant->start_date;
                $end_date = $applicant->end_date;

                $post = array(
                    'FNUM' => $applicant->fnum,
                    'DEADLINE' => $h_date->displayDate($end_date),
                    'CAMPAIGN_LABEL' => $applicant->label,
                    'CAMPAIGN_START' => $start_date,
                    'CAMPAIGN_END' => $end_date,
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
                    $this->log .= "\n Error sending email : " . $to;
                } else {
                    $message = array(
                        'user_id_from' => $from_id,
                        'user_id_to' => $to_id,
                        'subject' => $subject,
                        'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body,
                        'email_id' => $reminder_mail_id,
                    );
                    $m_emails->logEmail($message, $applicant->fnum);
                    $this->log .= '\n' . JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.' :: '.$body;
                }
                // to avoid been considered as a spam process or DDoS
                sleep(0.1);
            }

        }
        $this->log .= "\n process " . count($applicants) . " applicant(s)";

        return count($applicants);
    }
}
