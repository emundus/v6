<?php
/**
 * A cron task to email evaluators on un-evaluated files
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

class PlgFabrik_Cronemundusevaluatorrecall extends PlgFabrik_Cron {

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
	 * @return  mixed  number of records updated
	 * @throws Exception
	 */
	public function process(&$data, &$listModel) {

        jimport('joomla.mail.helper');

        // LOGGER
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.evaluatorrecall.info.php'], JLog::INFO, 'com_emundus');
        JLog::addLogger(['text_file' => 'com_emundus.evaluatorrecall.error.php'], JLog::ERROR, 'com_emundus');



		$params = $this->getParams();

		$reminder_mail_id = $params->get('reminder_mail_id', '');
        $acl_aro_groups = $params->get('acl_aro_groups', '13');
        $reminder_deadline = $params->get('reminder_deadline', '14,7,1');
        $status_for_send = $params->get('reminder_status', '');


        $users = $this->getEmundusUsers($acl_aro_groups);

		if (!empty($users)) {
		    // Store the evaluators
		    $evaluators = [];

            foreach ($users as $user) {
                $fnums = $this->getFnumAssoc($user);
                $gFnums = $this->getFnumGroupAssoc($user);
                //merge both results to have one list of fnums
                $evaluators[$user] = array_merge($fnums, $gFnums);
            }

            if (!empty($evaluators)) {

                include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
                $m_emails = new EmundusModelEmails;
                
                $email = $m_emails->getEmailById($reminder_mail_id);
                $EmptyEvals = $this->getEmptyEvals($reminder_deadline, $status_for_send);
                
                $emailArray = [];


                foreach ($evaluators as $evaluator => $val) {
                    foreach ($EmptyEvals as $applicant) {
                        // We check if the fnum is in the list of associated fnums 
                        // If it is, this means we have to notify the evaluator, meaning, adding it in the emailArray
                        if(array_search($applicant->fnum, $val) !== false) {

                            /**
                             * the list is built as the following :
                             *
                             * [user_id] => {
                             *      [campaign_id] => {
                             *          [campaign_label] => ""
                             *          [eval_end_date] => ""
                             *          [fnums] => {
                             *              ...
                             *          }
                             *      }
                             * }
                             *
                             **/
                            
                            $emailArray[$evaluator][$applicant->campaign_id]["label"] = $applicant->label;
                            $emailArray[$evaluator][$applicant->campaign_id]["eval_end_date"] = $applicant->eval_end_date;
                            $emailArray[$evaluator][$applicant->campaign_id]["fnums"][] = $applicant->fnum;
                        }
                    }
                }
                if (!empty($emailArray)) {
                    foreach ($emailArray as $emailUser => $emailCampaigns) {
                        foreach ($emailCampaigns as $campaign) {
                            // We send a email for each campaign for each user
                            $mailer = JFactory::getMailer();
                            $user = JFactory::getUser($emailUser);

                            $post = array(
                                'FNUM' => '<ul><li>' . implode('</li><li>',$campaign['fnums']) . '</li></ul>',
                                'CAMPAIGN_LABEL' => $campaign['label'],
                                'EVALUATION_END' => strftime("%d/%m/%Y %H:%M", strtotime($campaign['eval_end_date'])),
                                'NAME' => $user->name,
                            );

                            $tags = $m_emails->setTags($user->id, $post);

                            $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
                            $from_id = 62;
                            $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
                            $to = $user->email;
                            $to_id = $user->id;
                            $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
                            $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);

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
                                    'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                                );
                                $m_emails->logEmail($message);
                                $this->log .= '\n' . JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.' :: '.$body;
                            }
                            // to avoid been considered as a spam process or DDoS
                            sleep(0.1);
                        }
                    }
                    JLog::add("\n process " . sizeof($emailArray) . " emails sent", JLog::INFO, 'com_emundus');
                    return sizeof($emailArray);
                }
            }
        }
        return false;
	}

	
    /**
     * Gets all eMundus users linked to the Joomla acl_groups set in XML params
     *
     * @param string $profile Joomla acl_group
     * @return mixed
     *
     */
    public function getEmundusUsers($profile) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(eu.user_id)'])
            ->from($db->quoteName('#__emundus_users', 'eu'))
            ->leftJoin($db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $db->quoteName('eu.user_id') . ' = '. $db->quoteName('eup.user_id'))
            ->where($db->quoteName('eu.profile') . ' IN (' . $profile. ') OR ' . $db->quoteName('eup.profile_id') . ' IN (' . $profile. ')');

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    
    /**
     * Gets all fnums linked to a user through the user assoc table
     *
     * @param int $user eMundus User
     * @return mixed
     *
     */
    public function getFnumAssoc($user) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName(['fnum']))
            ->from($db->quoteName('#__emundus_users_assoc'))
            ->where($db->quoteName('action_id') .' = 5 AND ' . $db->quoteName('c'). ' = 1 AND ' . $db->quoteName('user_id') . ' = ' . $user );

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    
    /**
     * Gets all fnums linked to a user through the group assoc table
     *
     * @param int $user eMundus User
     * @return mixed
     *
     */
    public function getFnumGroupAssoc($user) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName(['fnum']))
            ->from($db->quoteName('#__emundus_group_assoc', 'ega'))
            ->leftJoin($db->quoteName('#__emundus_groups', 'eg') . ' ON ' . $db->quoteName('eg.group_id') . ' = '. $db->quoteName('ega.group_id'))
            ->where($db->quoteName('ega.action_id') .' = 5 AND ' . $db->quoteName('ega.c'). ' = 1 AND ' . $db->quoteName('eg.user_id') . ' = ' . $user );

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    /**
     * Gets all empty evals linked to a program that have been sent and have a Date Diff with the evaluation end date
     *
     * @param string $reminder_deadline Days before eval end
     * @param string $status_for_send Statuses to exclude 
     * @return mixed
     *
     */
    public function getEmptyEvals($reminder_deadline, $status_for_send) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select([$db->quoteName('ecc.*'), $db->quoteName('esc.*')])
            ->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
            ->leftJoin($db->quoteName('#__emundus_evaluations', 'ee') . ' ON ' . $db->quoteName('ecc.fnum') . ' = '. $db->quoteName('ee.fnum'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.id') . ' = '. $db->quoteName('ecc.campaign_id'))
            ->where($db->quoteName('ecc.status') . ' NOT IN ('. $status_for_send .') AND ' . $db->quoteName('ee.user') . ' IS NULL AND DATEDIFF( ' . $db->quoteName('esc.eval_end_date') . ' , now()) IN ('.$reminder_deadline.')');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}
