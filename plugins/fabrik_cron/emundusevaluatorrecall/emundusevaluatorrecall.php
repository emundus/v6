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


        $empty_evals = $this->getEmptyEvals($reminder_deadline, $status_for_send);

        if (empty($empty_evals)) {
            return false;
        }

        $users = $this->getEmundusUsers($acl_aro_groups);
        $group_users = $this->getGroupsByProg($acl_aro_groups);

        $users = array_unique(array_merge($users, $group_users));

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
                include_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
                include_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');

                $m_profile = new EmundusModelProfile();
                $c_messages = new EmundusControllerMessages();

                $emailArray = [];

                foreach ($evaluators as $evaluator => $val) {
                    foreach ($empty_evals as $applicant) {
                        // We check if the fnum is in the list of associated fnums
                        // If it is, this means we have to notify the evaluator, meaning, adding it in the emailArray
                        if (array_search($applicant->fnum, $val) !== false) {

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
                    $menu = JMenu::getInstance('site');

                    foreach ($emailArray as $emailUser => $emailCampaigns) {
                        foreach ($emailCampaigns as $campaign) {
                            // We send a email for each campaign for each user
                            $user = JFactory::getUser($emailUser);

                            $menutype = $m_profile->getProfileByApplicant($user->id)['menutype'];
                            $items = $menu->getItems('menutype', $menutype);

                            // We're getting the first link in the user's menu that's from com_emundus, which is PROBABLY a files/evaluation view, but this does not guarantee it.
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
                                $userLink = $items[$index]->link.'&Itemid='.$items[0]->id;
                            }

                            $fnumList = '<ul>';
                            foreach ($campaign['fnums'] as $fnum) {
                                $fnumList .= '<li><a href="'.JURI::root().$userLink.'#'.$fnum.'|open">'.$fnum.'</a></li>';
                            }
                            $fnumList .= '</ul>';

                            $post = array(
                                'FNUMS' => $fnumList,
                                'CAMPAIGN_LABEL' => $campaign['label'],
                                'EVALUATION_END' => strftime("%d/%m/%Y %H:%M", strtotime($campaign['eval_end_date'])),
                                'NAME' => $user->name,
                            );
                            $c_messages->sendEmailNoFnum($user->email, $reminder_mail_id, $post, $user->id);
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
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON ' . $db->quoteName('esp.id') . ' = '. $db->quoteName('eup.profile_id'))
            ->where($db->quoteName('esp.acl_aro_groups') . ' IN (' . $profile. ')');

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Gets all eMundus users where the group is linked to a program
     *
     * @param string $profile Joomla acl_group
     * @return mixed
     *
     */
    public function getGroupsByProg($profile) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(eu.user_id)'])
            ->from($db->quoteName('#__emundus_users', 'eu'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON ' . $db->quoteName('esp.id') . ' = '. $db->quoteName('eu.profile'))
            ->leftJoin($db->quoteName('#__emundus_groups', 'eg') . ' ON ' . $db->quoteName('eg.user_id') . ' = '. $db->quoteName('eu.user_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'esgrc') . ' ON ' . $db->quoteName('esgrc.parent_id') . ' = '. $db->quoteName('eg.group_id'))
            ->leftJoin($db->quoteName('#__emundus_acl', 'ea') . ' ON ' . $db->quoteName('eg.group_id') . ' = '. $db->quoteName('ea.group_id'))
            ->where($db->quoteName('esp.acl_aro_groups') . ' IN(' . $profile . ') AND ' . $db->quoteName('ea.action_id') . ' = 5 AND ' . $db->quoteName('ea.c') . ' = 1');

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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
            JLog::add('SQL Error -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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
            JLog::add('SQL Error -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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
            ->select(['ecc.*', 'esc.*'])
            ->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
            ->leftJoin($db->quoteName('#__emundus_evaluations', 'ee') . ' ON ' . $db->quoteName('ecc.fnum') . ' = '. $db->quoteName('ee.fnum'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.id') . ' = '. $db->quoteName('ecc.campaign_id'))
            ->where($db->quoteName('ecc.status') . ' NOT IN ('. $status_for_send .') AND ' . $db->quoteName('ee.user') . ' IS NULL AND DATEDIFF( ' . $db->quoteName('esc.eval_end_date') . ' , now()) IN ('.$reminder_deadline.')');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}

