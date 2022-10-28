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

class PlgFabrik_Cronemundusreferentrecall extends PlgFabrik_Cron {

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
        jimport('joomla.log.logger');

        $params = $this->getParams();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $reminder_mail_id = $params->get('reminder_mail_id', '');
        $reminder_programme_code = $params->get('reminder_programme_code', '');
        $reminder_deadline = $params->get('reminder_deadline', '30, 7, 1, 0');
        $attachments_id =  $params->get('attachment_id', '4, 6, 21');

        $status_for_send = $eMConfig->get('status_for_send', '');

        $this->log = '';

        // Get list of applicants to notify
        $db = FabrikWorker::getDbo();

        $query = 'SELECT DISTINCT efr.attachment_id, u.id, u.email, eu.firstname, eu.lastname, ecc.fnum, ecc.applicant_id, esc.start_date, esc.end_date, esc.label, efr.uploaded, efr.keyid,  DATEDIFF( esc.end_date , now()) as left_days
					FROM #__emundus_files_request as efr
					LEFT JOIN #__emundus_campaign_candidature as ecc ON efr.fnum = ecc.fnum
					LEFT JOIN #__users as u ON u.id=ecc.applicant_id
					LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
					LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id=ecc.campaign_id
					WHERE ecc.published = 1 AND u.block = 0 AND esc.published = 1  AND efr.uploaded = 0 AND efr.attachment_id IN('.$attachments_id.') AND DATEDIFF(esc.end_date , now()) IN ('.$reminder_deadline.') ';


        if (isset($status_for_send) && $status_for_send !== '' ) {
            $query .= ' AND ecc.status in ('.$status_for_send.')';
        }
        if (isset($reminder_programme_id) && !empty($reminder_programme_id)) {
            $query .= ' AND esc.training IN ('.$reminder_programme_code.')';
        }

        $db->setQuery($query);
        $applicants = $db->loadObjectList();

        // Generate emails from template and store it in message table
        if (!empty($applicants)) {
            include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
            $m_emails = new EmundusModelEmails;

            if (!empty($reminder_mail_id)) {
                $email = $m_emails->getEmailById($reminder_mail_id);
            } else {
                $email = $m_emails->getEmail('referent_letter');
            }


            foreach ($applicants as $applicant) {

                if ($this->getFilesExist($applicant->fnum, $applicant->attachment_id) == 0) {

                    $mailer = JFactory::getMailer();
                    $mailer->SMTPDebug = true;

                    $baseurl = JURI::root();
                    $link_upload = $baseurl . 'index.php?option=com_fabrik&c=form&view=form&formid=68&tableid=71&keyid=' . $applicant->keyid . '&sid=' . $applicant->applicant_id;

                    $referentEmails = $this->getFilesRequest($applicant->fnum,$applicant->attachment_id);


                    if (!empty($referentEmails)) {
                        $post = array(
                            'FNUM' => $applicant->fnum,
                            'DEADLINE' => JHTML::_('date', $applicant->end_date, JText::_('DATE_FORMAT_OFFSET1'), null),
                            'CAMPAIGN_LABEL' => $applicant->label,
                            'CAMPAIGN_START' => JHTML::_('date', $applicant->start_date, JText::_('DATE_FORMAT_OFFSET1'), null),
                            'CAMPAIGN_END' => JHTML::_('date', $applicant->end_date, JText::_('DATE_FORMAT_OFFSET1'), null),
                            'FIRSTNAME' => $applicant->firstname,
                            'LASTNAME' => strtoupper($applicant->lastname),
                            'UPLOAD_URL' => $link_upload,
                            'SITE_URL' => JURI::base(),
                        );
                        $tags = $m_emails->setTags($applicant->id, $post, $applicant->fnum, '', $email->emailfrom.$email->name.$email->subject.$email->message);

                        $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
                        $from_id = 62;
                        $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
                        $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
                        $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
                        $body = $m_emails->setTagsFabrik($body, [$applicant->fnum]);
                        $to_id = $applicant->id;

                        $config = JFactory::getConfig();

                        $email_from_sys = $config->get('mailfrom');
                        $email_from = $from;

                        // If the email sender has the same domain as the system sender address.
                        if (!empty($email_from) && substr(strrchr($email_from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
                            $mail_from_address = $email_from;
                        } else {
                            $mail_from_address = $email_from_sys;
                        }

                        foreach ($referentEmails as $referentEmail) {
                            $to = $referentEmail->email;

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

                            $mailer->clearAddresses();
                            $mailer->clearAllRecipients();
                            $mailer->smtpClose();

                            if ($send !== true) {
                                $this->log .= "\n Error sending email : " . $to;
                            } else {
                                $message = array(
                                    'user_id_from' => $from_id,
                                    'user_id_to' => $to_id,
                                    'subject' => $subject,
                                    'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $to . '</i><br>' . $body
                                );
                                $m_emails->logEmail($message);
                                $this->log .= '\n' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $to . ' :: ' . $body;
                            }

                            // to avoid being considered as a spam process or DDoS
                            sleep(5);
                        }
                    }
                }
            }
        }

        $this->log .= "\n process " . count($applicants) . " applicant(s)";
        return count($applicants);
    }


    public function getFilesRequest($fnum, $attachment_id) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
	    JLog::addLogger(['text_file' => 'com_emundus.cron.referentRecall.error.php'], JLog::ERROR, 'com_emundus');

        $query->select($db->quoteName('email'))
            ->from($db->quoteName('#__emundus_files_request'))
            ->where($db->quoteName('attachment_id') . ' IN (' . $db->quote($attachment_id).') AND '.$db->quoteName('fnum').' LIKE '.$db->quote($fnum))
            ->setLimit(1)
            ->order('email DESC');

        $db->setQuery($query);
        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
	        JLog::add('Error getting emails : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
	        return null;
        }
    }


    public function getFilesExist($fnum, $attachment_id) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
	    JLog::addLogger(['text_file' => 'com_emundus.cron.referentRecall.error.php'], JLog::ERROR, 'com_emundus');

        $query->select('COUNT(id)')
            ->from($db->quoteName('#__emundus_uploads'))
            ->where($db->quoteName('attachment_id') . ' IN (' . $db->quote($attachment_id) .') AND'. $db->quoteName('fnum') .' LIKE '. $db->quote($fnum));

        $db->setQuery($query);
        try {
	        return $db->loadResult();
        } catch (Exception $e) {
        	JLog::add('Error getting uploads : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        	return null;
        }
    }
}
