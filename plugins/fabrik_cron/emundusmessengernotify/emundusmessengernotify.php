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

        // Get list of applicants to notify
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('distinct cc.fnum, cc.applicant_id')
            ->from($db->quoteName('#__emundus_chatroom','c'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature','cc').' ON '.$db->quoteName('cc.fnum').' = '.$db->quoteName('c.fnum'));

        $db->setQuery($query);
        $chatrooms = $db->loadObjectList();

        $applicants_to_send = [];

        // Generate emails from template and store it in message table
        if (!empty($chatrooms)) {
            include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
            include_once(JPATH_SITE.'/components/com_emundus/models/files.php');
            include_once(JPATH_SITE.'/components/com_emundus/models/profile.php');
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

                if ($messages_not_read > 0) {
                    if(!in_array($chatroom->applicant_id,$applicants_to_send)) {
                        $applicants_to_send[] = $chatroom->applicant_id;
                    }
                }
            }

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

            // Send notifications to coordinators
            $fnums_no_readed = array();
            $users_to_send = array();

            foreach ($chatrooms as $chatroom) {
                $query->clear()
                    ->select('distinct g.user_id')
                    ->from($db->quoteName('#__emundus_groups', 'g'))
                    ->leftJoin($db->quoteName('#__emundus_group_assoc', 'ga') . ' ON ' . $db->quoteName('ga.group_id') . ' = ' . $db->quoteName('g.group_id'))
                    ->where($db->quoteName('ga.fnum') . ' LIKE ' . $db->quote($chatroom->fnum));
                $db->setQuery($query);

                $groups_associated = $db->loadColumn();
                $users_associated = $m_files->getAssessorsByFnums((array)$chatroom->fnum,'uids');
                foreach ($users_associated as $key => $user_associated){
                    if(!is_string($user_associated)){
                        unset($users_associated[$key]);
                    }
                }
                $users_to_send = array_unique(array_merge($groups_associated,$users_associated));

                if(empty($users_to_send)){
                    $query->clear()
                        ->select('distinct eu.user_id')
                        ->from($db->quoteName('#__emundus_users','eu'))
                        ->leftJoin($db->quoteName('#__emundus_users_profiles','eup').' ON '.$db->quoteName('eu.user_id').' = '.$db->quoteName('eup.user_id'))
                        ->where($db->quoteName('eup.profile_id') . ' = 2');
                    $db->setQuery($query);
                    $users_to_send = $db->loadColumn();
                }

                $query->clear()
                    ->select('count(m.message_id)')
                    ->from($db->quoteName('#__messages', 'm'))
                    ->leftJoin($db->quoteName('#__emundus_chatroom', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('m.page'))
                    ->where($db->quoteName('c.fnum') . ' LIKE ' . $db->quote($chatroom->fnum))
                    ->andWhere($db->quoteName('m.user_id_from') . ' NOT IN (' . implode(',',$users_to_send) . ')')
                    ->andWhere($db->quoteName('m.state') . ' = 0');

                $db->setQuery($query);
                $messages_not_read = $db->loadResult();

                if ($messages_not_read > 0) {
                    if(!in_array($chatroom->fnum,$fnums_no_readed)) {
                        $fnums_no_readed[] = $chatroom->fnum;
                    }
                }
            }

            if(!empty($fnums_no_readed)) {
                foreach ($users_to_send as $user_to_send) {
                    $query->clear()
                        ->select('id, email, name')
                        ->from($db->quoteName('#__users'))
                        ->where($db->quoteName('id') . ' = ' . $user_to_send);
                    $db->setQuery($query);
                    $user_info = $db->loadObject();

                    $to = $user_info->email;

                    $menu = JMenu::getInstance('site');
                    $menutype = $m_profile->getProfileByApplicant($user_info->id)['menutype'];
                    $items = $menu->getItems('menutype', $menutype);

                    // We're getting the first link in the user's menu that's from com_emundus
                    // which is PROBABLY a files/evaluation view, but this does not guarantee it.
                    $index = 0;
                    foreach ($items as $k => $item) {
                        if ($item->component === 'com_emundus') {
                            $index = $k;
                            break;
                        }
                    }

                    if (JFactory::getConfig()->get('sef') == 1) {
                        $userLink = $items[$index]->alias;
                    } else {
                        $userLink = $items[$index]->link . '&Itemid=' . $items[0]->id;
                    }

                    $fnumList = '<ul>';
                    $fnumListNoLink = '<ul>';
                    foreach ($fnums_no_readed as $fnum) {
                        $fnumList .= '
                        <li>
                            <a href="' . JURI::root() . $userLink . '#' . $fnum . '|open">' . $fnum . '</a>
                        </li>';
                        $fnumListNoLink .= '<li>' . $fnum . '</li>';
                    }
                    $fnumList .= '</ul>';
                    $fnumListNoLink .= '</ul>';

                    $post = array(
                        'FNUMS' => $fnumList,
                        'FNUMS_LIST' => $fnumListNoLink,
                        'NAME' => $user_info->name,
                        'SITE_URL' => JURI::base(),
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
