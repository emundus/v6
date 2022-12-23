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
require_once (JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emunduscampaignstartcall
 * @since       3.0
 */

class PlgFabrik_Cronemunduscampaignstartcall extends PlgFabrik_Cron {

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

        $params = $this->getParams();

        $reminderHours = $params->get('reminder_hours',null);
        $allow_element = $params->get('allow_element',null);
        $email_tmpl = $params->get('email_tmpl',null);

        $app        = JFactory::getApplication();

        $m_model = new EmundusModelEmails();
        $this->log = '';

        $db = JFactory::getDbo();

        if(!empty($reminderHours) && !empty($allow_element) && !empty($email_tmpl)) {
            $query = 'select jesc.id,jesc.label,jecw.start_date,jecw.end_date
                    from jos_emundus_campaign_workflow_repeat_campaign as jecwr
                    left join jos_emundus_campaign_workflow jecw on jecwr.parent_id = jecw.id
                    left join jos_emundus_setup_campaigns jesc on jecwr.campaign = jesc.id
                    where jecw.start_date BETWEEN now() and DATE_ADD(now(),INTERVAL  ' . $reminderHours . '  HOUR)';
            $db->setQuery($query);
            $campaigns = $db->loadObjectList();

            if(empty($campaigns)) {
                $query = 'select id,label,start_date,end_date from jos_emundus_setup_campaigns where start_date BETWEEN now() and DATE_ADD(now(),INTERVAL ' . $reminderHours . ' HOUR)';
                $db->setQuery($query);
                $campaigns = $db->loadObjectList();
            }

            $query = $db->getQuery(true);
            $query->select('user_id,email,name')
                ->from($db->quoteName('#__emundus_users'))
                ->where($db->quoteName($allow_element) . ' LIKE ' . $db->quote('%Oui%'));
            $db->setQuery($query);
            $users_to_notified = $db->loadObjectList();

            foreach ($campaigns as $campaign) {
                $query->clear()
                    ->select('user')
                    ->from($db->quoteName('#__emundus_notifications_mails'))
                    ->where($db->quoteName('campaign') . ' = ' . $campaign->id);
                $db->setQuery($query);
                $users_already_notified = $db->loadColumn();

                foreach ($users_to_notified as $user_to_notified) {
                    if (!in_array($user_to_notified->user_id, $users_already_notified)) {
                        // Récupération des données du mail
                        $query = 'SELECT id, subject, emailfrom, name, message, email_tmpl
                    FROM #__emundus_setup_emails
                    WHERE lbl="' . $email_tmpl . '"';
                        $db->setQuery($query);
                        $db->execute();
                        $obj = $db->loadObject();

                        // Get template
                        $query = 'SELECT Template as tmpl
                    FROM #__emundus_email_templates
                    WHERE id="' . $obj->email_tmpl . '"';
                        $db->setQuery($query);
                        $db->execute();
                        $tmpl = $db->loadObject();

                        // setup mail
                        $email_from_sys = $app->getCfg('mailfrom');

                        $from = $obj->emailfrom;
                        $fromname = $obj->name;

                        $post = [
                            'APPLICANT_NAME' => $user_to_notified->name,
                            'CAMPAIGN_LABEL' => $campaign->label,
                            'CAMPAIGN_START_DATE' => strftime("%d/%m/%Y %H:%M", strtotime($campaign->start_date)),
                            'CAMPAIGN_END_DATE' => strftime("%d/%m/%Y %H:%M", strtotime($campaign->end_date)),
                            'SITE_URL' => JURI::base(),
                        ];

                        $tags = $m_model->setTags($user_to_notified->user_id, $post, null, '', $obj->emailfrom . $obj->name . $obj->subject . $obj->message);

                        $to = $user_to_notified->email;
                        $subject = preg_replace($tags['patterns'], $tags['replacements'], $obj->subject);
                        $body = $obj->message;

                        if (!empty($tmpl)) {
                            $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $tmpl->tmpl);
                        }
                        $body = preg_replace($tags['patterns'], $tags['replacements'], $body);


                        // If the email sender has the same domain as the system sender address.
                        if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
                            $mail_from_address = $from;
                        } else {
                            $mail_from_address = $email_from_sys;
                        }

                        // Set sender
                        $sender = [
                            $mail_from_address,
                        ];

                        $mailer = JFactory::getMailer();
                        $mailer->setSender($sender);
                        $mailer->addReplyTo($from, $fromname);
                        $mailer->addRecipient($to);
                        $mailer->setSubject($subject);
                        $mailer->isHTML(true);
                        $mailer->Encoding = 'base64';
                        $mailer->setBody($body);
                        $send = $mailer->Send();

                        if ($send !== true) {
                            echo 'Error sending email: ' . $send->__toString();
                            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
                        } else {
                            // Save email sended to this user
                            $query = $db->getQuery(true);
                            $query->insert($db->quoteName('#__emundus_notifications_mails'));
                            $query->set($db->quoteName('user') . ' = ' . $user_to_notified->user_id)
                                ->set($db->quoteName('campaign') . ' = ' . $campaign->id);
                            $db->setQuery($query);
                            $db->execute();
                            //
                        }
                    }
                }
            }
        }


        return true;
    }


}
