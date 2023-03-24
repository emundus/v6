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

class PlgFabrik_Cronemundusmessengernotify extends PlgFabrik_Cron {

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

        $reminder_mail_id = $params->get('reminder_mail_id', '79');
        $reminder_mail_coordinator_id = $params->get('reminder_mail_id_coordinator', '80');

        $this->log = '';

        // Get list of fnums with a chatroom containing an unread message
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('distinct cc.fnum, cc.applicant_id')
            ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
            ->innerJoin($db->quoteName('#__emundus_chatroom','c').' ON '.$db->quoteName('c.fnum').' = '.$db->quoteName('cc.fnum'))
            ->innerJoin($db->quoteName('#__messages','m').' ON '.$db->quoteName('m.page').' = '.$db->quoteName('c.id') . ' AND ' . $db->quoteName('m.state') . ' = ' . $db->quote(0));
        $db->setQuery($query);
        $chatrooms = $db->loadObjectList();

        // <------- Process to send email to applicants ------>
        $applicants_to_send = array();

        // Generate emails for applicant from template
        if (!empty($chatrooms)) {
            include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');
            include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
            include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
            include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');

            $m_emails = new EmundusModelEmails;
            $m_files = new EmundusModelFiles;
            $m_profile = new EmundusModelProfile;

            $c_messages = new EmundusControllerMessages();
            $email = $m_emails->getEmailById($reminder_mail_id);

            $query->clear()
                ->select('Template')
                ->from($db->quoteName('#__emundus_email_templates'))
                ->where($db->quoteName('id') . ' = ' . $email->email_tmpl);
            $db->setQuery($query);
            $email->Template = $db->loadResult();

            // For every chatroom, check if the applicant has an unread message
            foreach ($chatrooms as $chatroom) {
                $query->clear()
                    ->select('count(m.message_id)')
                    ->from($db->quoteName('#__messages', 'm'))
                    ->leftJoin($db->quoteName('#__emundus_chatroom', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('m.page'))
                    ->where($db->quoteName('c.fnum') . ' LIKE ' . $db->quote($chatroom->fnum))
                    ->andWhere($db->quoteName('m.user_id_from') . ' <> ' . $db->quote($chatroom->applicant_id))
                    ->andWhere($db->quoteName('m.state') . ' = 0');

                $db->setQuery($query);
                $messages_not_read = $db->loadResult();

                // If there is an unread message and the applicant is not already in the list of applicants to notify, add them
                if ($messages_not_read > 0) {
                    if(!in_array($chatroom->applicant_id,$applicants_to_send)) {
                        $applicants_to_send[] = $chatroom->applicant_id;
                    }
                }
            }

            // Send the mail to each applicant
            foreach ($applicants_to_send as $applicant_to_send){
                $mailer = JFactory::getMailer();

                $query->clear()
                    ->select('id, email, name')
                    ->from($db->quoteName('#__users'))
                    ->where($db->quoteName('id') . ' = ' . $applicant_to_send);
                $db->setQuery($query);
                $applicant = $db->loadObject();

                $from = $email->emailfrom;
                $from_id = 62;
                $fromname = $email->name;
                $to = $applicant->email;
                $to_id = $applicant->id;
                $subject = $email->subject;
                $body = $email->message;

                if ($email->Template) {
                    $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $email->Template);
                }

                $site_url = JURI::base();

                $body = preg_replace("/\[SITE_URL\]/",$site_url, $body);

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
                }
                // to avoid been considered as a spam process or DDoS
                sleep(0.1);
            }

            // <------ Process to send email to partners ------>
            $users_fnum_assoc = array();

            foreach ($chatrooms as $chatroom) {
                $group_associated_prog = array();
                $group_associated_direct = array();
                $users_associated_direct = array();
                $users_to_send = array();

                $fnumInfos = $m_files->getFnumInfos($chatroom->fnum);

                // Get users associated to the file by their group and the campaign program
                $query->clear()
                    ->select('distinct u.id')
                    ->from($db->quoteName('#__emundus_groups', 'g'))
                    ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'grc') . ' ON ' . $db->quoteName('grc.parent_id') . ' = ' . $db->quoteName('g.group_id'))
                    ->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('g.user_id'))
                    ->where($db->quoteName('grc.course') . ' = ' . $db->quote($fnumInfos['training']));
                $db->setQuery($query);
                $group_associated_prog = $db->loadColumn();

                // Get users associated to the file by their group directly
                $query->clear()
                    ->select('distinct u.id')
                    ->from($db->quoteName('#__emundus_groups', 'g'))
                    ->leftJoin($db->quoteName('#__emundus_group_assoc', 'ga') . ' ON ' . $db->quoteName('ga.group_id') . ' = ' . $db->quoteName('g.group_id'))
                    ->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('g.user_id'))
                    ->where($db->quoteName('ga.fnum') . ' LIKE ' . $db->quote($chatroom->fnum));
                $db->setQuery($query);
                $group_associated_direct = $db->loadColumn();

                // Get users associated to the file directly
                $query->clear()
                    ->select('distinct u.id')
                    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($db->quoteName('#__emundus_users_assoc', 'eua') . ' ON ' . $db->quoteName('eua.fnum') . ' LIKE ' . $db->quoteName('cc.fnum'))
                    ->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('eua.user_id'))
                    ->where($db->quoteName('cc.fnum') . ' LIKE ' . $db->quote($chatroom->fnum))
                    ->group($db->quoteName('cc.fnum'));
                $db->setQuery($query);
                $users_associated_direct = $db->loadColumn();

                // Merge the two lists
                $users_to_send = array_unique(array_merge($group_associated_prog,$group_associated_direct,$users_associated_direct));

                // If there are no users associated to the file, get a list of all coordinators
                if(empty($users_to_send)){
                    $query->clear()
                        ->select('distinct eu.user_id')
                        ->from($db->quoteName('#__emundus_users','eu'))
                        ->leftJoin($db->quoteName('#__emundus_users_profiles','eup').' ON '.$db->quoteName('eu.user_id').' = '.$db->quoteName('eup.user_id'))
                        ->where($db->quoteName('eup.profile_id') . ' = 2');
                    $db->setQuery($query);
                    $users_to_send = $db->loadColumn();
                }

                // Check if unread messages from the chatroom come from another user than the ones in our final list
                $query->clear()
                    ->select('count(m.message_id)')
                    ->from($db->quoteName('#__messages', 'm'))
                    ->leftJoin($db->quoteName('#__emundus_chatroom', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('m.page'))
                    ->where($db->quoteName('c.fnum') . ' LIKE ' . $db->quote($chatroom->fnum))
                    ->andWhere($db->quoteName('m.user_id_from') . ' = ' . $db->quote($chatroom->applicant_id))
                    ->andWhere($db->quoteName('m.state') . ' = 0');

                $db->setQuery($query);
                $messages_not_read = $db->loadResult();

                // If an unread message from the chatroom was not sent by a partner from our final list, then it must be the applicant, add the users and the fnum to the list of emails to send
                if ($messages_not_read > 0) {
                    foreach ($users_to_send as $user_fnum_assoc) {
                        $users_fnum_assoc[$user_fnum_assoc][] = $chatroom->fnum;
                    }
                }
            }

            // $users_fnum_assoc has for key the user_id of the partner and as a value an array of all the fnums the user must be notified for
            if(!empty($users_fnum_assoc)) {
                // So we have to send one email to these partners
                foreach ($users_fnum_assoc as $user_to_send => $fnums_not_read) {
                    $query->clear()
                        ->select('id, email, name')
                        ->from($db->quoteName('#__users'))
                        ->where($db->quoteName('id') . ' = ' . $user_to_send);
                    $db->setQuery($query);
                    $user_info = $db->loadObject();

                    $to = $user_info->email;

                    $menutype = $m_profile->getProfileByApplicant($user_info->id)['menutype'];

                    // Get the first link from the partner's menu that corresponds to the file list.
                    $query->clear()
                        ->select($db->quoteName('path'))
                        ->from($db->quoteName('#__menu'))
                        ->where($db->quoteName('menutype') . ' = ' . $db->quote($menutype))
                        ->andWhere($db->quoteName('published') . ' = ' . $db->quote(1))
                        ->andWhere($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_emundus&view=files%') . ' OR ' . $db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_emundus&view=evaluation%') . ' OR ' . $db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_emundus&view=decision%'))
                        ->order($db->quoteName('lft'));
                    $db->setQuery($query);
                    $userLink = $db->loadResult();

                    // Here we build the fnumList to be added to the email
                    $fnumList = '<ul>';
                    // Using the fnums array stocked as a value for each user_id, we can list all the associated fnums with unread messages for each user specifically
                    foreach ($fnums_not_read as $fnum) {
                        if (!empty($userLink)) {
                            $fnumList .= '<li><a href="' . JURI::root() . $userLink . '#' . $fnum . '|open">' . $fnum . '</a></li>';
                        } else {
                            $fnumList .= '<li>' . $fnum . '</li>';
                        }
                    }
                    $fnumList .= '</ul>';

                    $post = array(
                        'FNUMS' => $fnumList,
                        'NAME' => $user_info->name,
                        'SITE_URL' => JURI::root(),
                    );

                    $c_messages->sendEmailNoFnum($to, $reminder_mail_coordinator_id, $post, $user_info->id);
                    // to avoid been considered as a spam process or DDoS
                    sleep(0.1);
                }
            }
        }
        $this->log .= "\n process " . count($applicants_to_send) . " applicant(s)";

        return count($applicants_to_send);
    }
}
