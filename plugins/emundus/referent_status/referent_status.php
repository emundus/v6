<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgEmundusReferent_status extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.emundusreferent_status.php'), JLog::ALL, array('com_emundus'));
    }


    function onAfterStatusChange($fnum,$state) {

        $referent_mail_id = $this->params->get('referent_status_mail_id', '');
        $status_to_check = $this->params->get('referent_status_step', '');
        $fabrik_elts = $this->params->get('referent_status_fabrik_elements', '');
        $attachments_id = $this->params->get('referent_status_attachments_letters', '');
        $campaigns = $this->params->get('referent_status_campaigns', '');

        if ($status_to_check != $state) {
            return false;
        }

        $this->log = '';

        // Get list of applicants to notify
        $db = JFactory::getDbo();

        $query = 'SELECT DISTINCT u.id, u.email, eu.firstname, eu.lastname, ecc.fnum, ecc.campaign_id, ecc.applicant_id, esc.start_date, esc.end_date, esc.label, DATEDIFF( esc.end_date , now()) as left_days
					FROM #__emundus_campaign_candidature as ecc
					LEFT JOIN #__users as u ON u.id=ecc.applicant_id
					LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
					LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id=ecc.campaign_id
					WHERE ecc.fnum = '.$fnum.' AND ecc.campaign_id IN ('.$campaigns.') AND ecc.status IN('.$status_to_check.')';

        $db->setQuery($query);
        $applicant = $db->loadObject();

        // Generate emails from template and store it in message table
        if (!empty($applicant)) {
            include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
            $m_emails = new EmundusModelEmails;

            if (!empty($referent_mail_id)) {
                $email = $m_emails->getEmailById($referent_mail_id);
            } else {
                $email = $m_emails->getEmail('referent_letter');
            }

            if ($this->getFilesExist($applicant->fnum, $attachments_id) == 0 && $this->getFilesRequest($applicant->fnum,$attachments_id) == 0) {
                $mailer = JFactory::getMailer();
                $mailer->SMTPDebug = true;

                $baseurl = JURI::root();
                $link_upload = $baseurl . 'index.php?option=com_fabrik&c=form&view=form&formid=68&tableid=71&keyid=' . $applicant->keyid . '&sid=' . $applicant->applicant_id;

                $referents_emails = array();
                $elts = explode(',',$fabrik_elts);

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                foreach ($elts as $elt){
                    $table = explode('__',$elt)[0];
                    $field = explode('__',$elt)[1];

                    $query->clear()
                        ->select($field)
                        ->from($db->quoteName('#__' . $table))
                        ->where($db->quoteName('fnum') .' LIKE '. $db->quote($applicant->fnum));

                    $db->setQuery($query);
                    $referents_emails[] = $db->loadResult();
                }


                foreach ($referents_emails as $key => $referentEmail) {

                    $post = array(
                        'FNUM' => $applicant->fnum,
                        'DEADLINE' => strftime("%A %d %B %Y %H:%M", strtotime($applicant->end_date)),
                        'CAMPAIGN_LABEL' => $applicant->label,
                        'CAMPAIGN_START' => $applicant->start_date,
                        'CAMPAIGN_END' => $applicant->end_date,
                        'FIRSTNAME' => $applicant->firstname,
                        'LASTNAME' => strtoupper($applicant->lastname),
                        'UPLOAD_URL' => $link_upload,
                        'SITE_URL' => JURI::base(),
                    );
                    $tags = $m_emails->setTags($applicant->id, $post);

                    $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
                    $from_id = 62;
                    $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
                    $to = $referentEmail;
                    $to_id = $applicant->id;
                    $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
                    $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
                    $body = $m_emails->setTagsFabrik($body, [$applicant->fnum]);


                    $config = JFactory::getConfig();

                    $email_from_sys = $config->get('mailfrom');
                    $email_from = $from;

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
                        $attachments = explode(',',$attachments_id);
                        $this->setEmailSend($applicant->fnum,$attachments[$key],$applicant->id,$referentEmail,$applicant->campaign_id);
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
        return true;
    }


    public function getFilesRequest($fnum, $attachment_id) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        JLog::addLogger(['text_file' => 'com_emundus.cron.referentStatus.error.php'], JLog::ERROR, 'com_emundus');

        $query->select('COUNT(id)')
            ->from($db->quoteName('#__emundus_files_request'))
            ->where($db->quoteName('attachment_id') . ' IN (' . $db->quote($attachment_id).') AND '.$db->quoteName('fnum').' LIKE '.$db->quote($fnum));

        $db->setQuery($query);
        try {
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting emails : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return null;
        }
    }


    private function getFilesExist($fnum, $attachments_id) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        JLog::addLogger(['text_file' => 'com_emundus.cron.referentStatus.error.php'], JLog::ERROR, 'com_emundus');

        $query->select('COUNT(id)')
            ->from($db->quoteName('#__emundus_uploads'))
            ->where($db->quoteName('attachment_id') . ' IN (' . $db->quote($attachments_id) .') AND'. $db->quoteName('fnum') .' LIKE '. $db->quote($fnum));

        $db->setQuery($query);
        try {
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting uploads : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return null;
        }
    }

    private function setEmailSend($fnum,$attachment_id,$applicant_id,$referent_email,$campaign_id){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        JLog::addLogger(['text_file' => 'com_emundus.cron.referentStatus.error.php'], JLog::ERROR, 'com_emundus');
        try {
            $key = $key = md5($this->rand_string(20).time());
            $query->clear()
                ->insert($db->quoteName('#__emundus_files_request'));
            $query->set($db->quoteName('time_date') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('student_id') . ' = ' . $db->quote($applicant_id))
                ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum))
                ->set($db->quoteName('keyid') . ' = ' . $db->quote($key))
                ->set($db->quoteName('attachment_id') . ' = ' . $db->quote($attachment_id))
                ->set($db->quoteName('campaign_id') . ' = ' . $db->quote($campaign_id))
                ->set($db->quoteName('email') . ' = ' . $db->quote($referent_email));
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch (Exception $e) {
            JLog::add('Error getting uploads : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return null;
        }
    }

    private function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789') {
        $string = '';
        for ($i = 0; $i < $len; $i++) {
            $pos = rand(0, strlen($chars)-1);
            $string .= $chars{$pos};
        }
        return $string;
    }
}
