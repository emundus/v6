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
        $notify_users_programs = $params->get('notify_users_programs', '0');
        $notify_users_groups = $params->get('notify_users_groups', '1');
        $notify_users_assoc = $params->get('notify_users_assoc', '1');
        $notify_groups = $params->get('notify_groups', '');
        $notify_profiles = $params->get('notify_profiles', '');
        $notify_coordinators = $params->get('notify_coordinators', '0');

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
                $fnumInfos = $m_files->getFnumInfos($chatroom->fnum);

                // Check if unread messages from the chatroom come from the applicant
                $query->clear()
                    ->select('count(m.message_id)')
                    ->from($db->quoteName('#__messages', 'm'))
                    ->leftJoin($db->quoteName('#__emundus_chatroom', 'ec') . ' ON ' . $db->quoteName('ec.id') . ' = ' . $db->quoteName('m.page'))
                    ->where($db->quoteName('ec.fnum') . ' LIKE ' . $db->quote($chatroom->fnum))
                    ->andWhere($db->quoteName('m.user_id_from') . ' = ' . $db->quote($chatroom->applicant_id))
                    ->andWhere($db->quoteName('m.state') . ' = 0');
                $db->setQuery($query);
                $messages_not_read = $db->loadResult();

                if ($messages_not_read > 0) {
                    // Get users associated to the file by their group and the campaign program
                    if ($notify_users_programs == 1 || !empty($notify_groups) || !empty($notify_profiles)) {
                        $query->clear()
                            ->select('distinct eu.user_id')
                            ->from($db->quoteName('#__emundus_groups', 'eg'))
                            ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'esgrc') . ' ON ' . $db->quoteName('esgrc.parent_id') . ' = ' . $db->quoteName('eg.group_id'))
                            ->innerJoin($db->quoteName('#__emundus_users', 'eu') . ' ON ' . $db->quoteName('eu.user_id') . ' = ' . $db->quoteName('eg.user_id'));
                        if (!empty($notify_profiles)) {
                            $query->leftJoin($db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $db->quoteName('eup.user_id') . ' = ' . $db->quoteName('eu.user_id'));
                        }

                        $query->where($db->quoteName('esgrc.course') . ' = ' . $db->quote($fnumInfos['training']));

                        // Keep only users associated to groups given in parameter
                        if (!empty($notify_groups)) {
                            $query->andWhere($db->quoteName('eg.group_id') . ' IN (' . $notify_groups . ')');
                        }
                        // Keep only users associated to profiles given in parameter
                        if (!empty($notify_profiles)) {
                            $query->andWhere($db->quoteName('eu.profile') . ' IN (' . $notify_profiles . ') OR ' . $db->quoteName('eup.profile_id') . ' IN (' . $notify_profiles . ')');
                        }

                        $db->setQuery($query);
                        $group_associated_prog = $db->loadColumn();

                        // Remove eventual null values
                        if (!empty($group_associated_prog)) {
                            $group_associated_prog = array_filter($group_associated_prog);
                        }
                    }

                    // Get users associated to the file by their group directly
                    if ($notify_users_groups == 1 || !empty($notify_groups) || !empty($notify_profiles)) {
                        $query->clear()
                            ->select('distinct eu.user_id')
                            ->from($db->quoteName('#__emundus_groups', 'eg'))
                            ->leftJoin($db->quoteName('#__emundus_group_assoc', 'ega') . ' ON ' . $db->quoteName('ega.group_id') . ' = ' . $db->quoteName('eg.group_id'))
                            ->innerJoin($db->quoteName('#__emundus_users', 'eu') . ' ON ' . $db->quoteName('eu.user_id') . ' = ' . $db->quoteName('eg.user_id'));
                        if (!empty($notify_profiles)) {
                            $query->leftJoin($db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $db->quoteName('eup.user_id') . ' = ' . $db->quoteName('eu.user_id'));
                        }

                        $query->where($db->quoteName('ega.fnum') . ' LIKE ' . $db->quote($chatroom->fnum));

                        // Keep only users associated to groups given in parameter
                        if (!empty($notify_groups)) {
                            $query->andWhere($db->quoteName('eg.group_id') . ' IN (' . $notify_groups . ')');
                        }
                        // Keep only users associated to profiles given in parameter
                        if (!empty($notify_profiles)) {
                            $query->andWhere($db->quoteName('eu.profile') . ' IN (' . $notify_profiles . ') OR ' . $db->quoteName('eup.profile_id') . ' IN (' . $notify_profiles . ')');
                        }

                        $db->setQuery($query);
                        $group_associated_direct = $db->loadColumn();

                        // Remove eventual null values
                        if (!empty($group_associated_direct)) {
                            $group_associated_direct = array_filter($group_associated_direct);
                        }
                    }

                    // Get users associated to the file directly
                    if ($notify_users_assoc == 1 || !empty($notify_groups) || !empty($notify_profiles)) {
                        $query->clear()
                            ->select('distinct eu.user_id')
                            ->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
                            ->leftJoin($db->quoteName('#__emundus_users_assoc', 'eua') . ' ON ' . $db->quoteName('eua.fnum') . ' LIKE ' . $db->quoteName('ecc.fnum'))
                            ->innerJoin($db->quoteName('#__emundus_users', 'eu') . ' ON ' . $db->quoteName('eu.user_id') . ' = ' . $db->quoteName('eua.user_id'));
                        if (!empty($notify_profiles)) {
                            $query->leftJoin($db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $db->quoteName('eup.user_id') . ' = ' . $db->quoteName('eu.user_id'));
                        }

                        $query->where($db->quoteName('ecc.fnum') . ' LIKE ' . $db->quote($chatroom->fnum));

                        // Keep only users associated to groups given in parameter
                        if (!empty($notify_groups)) {
                            $query->andWhere($db->quoteName('eg.group_id') . ' IN (' . $notify_groups . ')');
                        }
                        // Keep only users associated to profiles given in parameter
                        if (!empty($notify_profiles)) {
                            $query->andWhere($db->quoteName('eu.profile') . ' IN (' . $notify_profiles . ') OR ' . $db->quoteName('eup.profile_id') . ' IN (' . $notify_profiles . ')');
                        }

                        $db->setQuery($query);
                        $users_associated_direct = $db->loadColumn();

                        // Remove eventual null values
                        if (!empty($users_associated_direct)) {
                            $users_associated_direct = array_filter($users_associated_direct);
                        }
                    }

                    // Merge the lists
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
                    } else if ($notify_coordinators == 1) {
                        $query->clear()
                            ->select('distinct eu.user_id')
                            ->from($db->quoteName('#__emundus_users','eu'))
                            ->leftJoin($db->quoteName('#__emundus_users_profiles','eup').' ON '.$db->quoteName('eu.user_id').' = '.$db->quoteName('eup.user_id'))
                            ->where($db->quoteName('eup.profile_id') . ' = 2');
                        $db->setQuery($query);
                        $users_coordinators = $db->loadColumn();

                        // Merge the lists
                        $users_to_send = array_unique(array_merge($users_to_send,$users_coordinators));
                    }

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
                        ->select(array('id, email, name'))
                        ->from($db->quoteName('#__users'))
                        ->where($db->quoteName('id') . ' = ' . $user_to_send);
                    $db->setQuery($query);
                    $user_info = $db->loadObject();

                    $to = $user_info->email;

                    $menutype = $m_profile->getProfileByApplicant($user_info->id)['menutype'];

                    // Get the first link from the partner's menu that corresponds to the file list.
                    $query->clear()
                        ->select(array('id', 'path'))
                        ->from($db->quoteName('#__menu'))
                        ->where($db->quoteName('menutype') . ' = ' . $db->quote($menutype))
                        ->andWhere($db->quoteName('published') . ' = ' . $db->quote(1))
                        ->andWhere($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_emundus&view=files%') . ' OR ' . $db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_emundus&view=evaluation%') . ' OR ' . $db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_emundus&view=decision%'))
                        ->order($db->quoteName('lft'));
                    $db->setQuery($query);
                    $userLink = $db->loadObject();

                    // Check if a translation exists for this link
                    // In english
                    $query->clear()
                        ->select($db->quoteName('value'))
                        ->from($db->quoteName('#__falang_content'))
                        ->where($db->quoteName('language_id') . ' = ' . $db->quote(1))
                        ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote('menu'))
                        ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote('path'))
                        ->andWhere($db->quoteName('reference_id') . ' = ' . $db->quote($userLink->id))
                        ->andWhere($db->quoteName('published') . ' = ' . $db->quote(1));
                    $db->setQuery($query);
                    $path_en = $db->loadResult();

                    // In french
                    $query->clear()
                        ->select($db->quoteName('value'))
                        ->from($db->quoteName('#__falang_content'))
                        ->where($db->quoteName('language_id') . ' = ' . $db->quote(2))
                        ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote('menu'))
                        ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote('path'))
                        ->andWhere($db->quoteName('reference_id') . ' = ' . $db->quote($userLink->id))
                        ->andWhere($db->quoteName('published') . ' = ' . $db->quote(1));
                    $db->setQuery($query);
                    $path_fr = $db->loadResult();

                    // If there are both en and fr translations, use no link in the mail
                    if ((!empty($path_fr) && !empty($path_en)) && $path_fr !== $path_en) {
                        $userLink = '';
                    } else {
                        if (!empty($path_fr)) {
                            // If there is only a french one, use the french translation of the link
                            $userLink->path = $path_fr;
                        } else if (!empty($path_en)) {
                            // If there is only an english one, use the english translation of the link
                            $userLink->path = $path_en;
                        }
                    }

                    // Here we build the fnumList to be added to the email
                    $fnumList = '<ul>';
                    // Using the fnums array stocked as a value for each user_id, we can list all the associated fnums with unread messages for each user specifically
                    foreach ($fnums_not_read as $fnum) {
                        if (!empty($userLink)) {
                            $fnumList .= '<li><a href="' . JURI::root() . $userLink->path . '#' . $fnum . '|open">' . $fnum . '</a></li>';
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
