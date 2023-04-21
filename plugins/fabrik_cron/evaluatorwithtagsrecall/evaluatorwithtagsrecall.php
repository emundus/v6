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

class PlgFabrik_Cronevaluatorwithtagsrecall extends PlgFabrik_Cron {

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
        JLog::addLogger(['text_file' => 'com_emundus.evaluatorwithtagsrecall.info.php'], JLog::INFO, 'com_emundus_eval_recall');
        JLog::addLogger(['text_file' => 'com_emundus.evaluatorwithtagsrecall.error.php'], JLog::ERROR, 'com_emundus_eval_recall');

        $params = $this->getParams();

        $reminder_mail_id = $params->get('reminder_mail_id', 'share_with_evaluator');
        $acl_aro_groups = explode(',',trim($params->get('acl_aro_groups', '')));
        $emundus_profiles = explode(',',trim($params->get('reminder_profile', '')));
        $tags = explode(',',trim($params->get('reminder_tags', null)));
        $date_interval =  $params->get('remider_interval');
        $elements_to_replace =  $params->get('remider_elements');
        $now = new DateTime();

        // Check if profile is applicant
        if(!empty($emundus_profiles)){
            $applicant_profiles = $this->getApplicantProfiles();
            if(!empty(array_intersect($applicant_profiles,$emundus_profiles))){
                $tag_fnums = $this->getFnumsByTags($tags);
                if (!empty($tag_fnums)) {

                    // Now check if the tagged date is a modulo of our param $date_interval
                    // We are filtering out the files that aren't a modulo of the $date_interval
                    $applicants = [];
                    foreach ($tag_fnums as $key => $fnum) {

                        $createDate = new DateTime($fnum);
                        $difference = $now->diff($createDate);
                        $days = $difference->days;

                        if($date_interval != 0) {
                            if ((($days % $date_interval == 0) && $difference->days > 0)) {
                                $applicants[] = $key;
                            }
                        } else {
                            if($days == 0){
                                $applicants[] = $key;
                            }
                        }

                        if (!empty($applicants)) {
                            $cpt = 0;
                            include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
                            include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
                            include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');

                            $m_files = new EmundusModelFiles();
                            $c_messages = new EmundusControllerMessages();

                            foreach ($applicants as $applicant) {
                                // We send a email for each campaign for each user
                                $fnumInfos = $m_files->getFnumInfos($applicant);

                                $post = array(
                                    'NAME' => $fnumInfos['name'],
                                );

                                $c_messages->sendEmail($applicant, $reminder_mail_id, $post);
                                $cpt++;
                            }
                            JLog::add("\n process " . sizeof($applicants) . " emails sent", JLog::INFO, 'com_emundus_eval_recall');
                            return $cpt;
                        }
                    }
                }
            }
        }

        // Get users by Joomla group
        $users_by_group = $this->getEmundusUsers($acl_aro_groups);
        $group_users = $this->getGroupsByProg($acl_aro_groups);
        if(!empty($group_users)) {
            $users_by_group = array_unique(array_merge($users_by_group, $group_users));
        }

        // Get users by eMundus Profile
        $users_by_profile = $this->getEmundusUsersByProfile($emundus_profiles);

        // Merge users
        $users = array_unique(array_merge($users_by_group,$users_by_profile));
        $tag_fnums = $this->getFnumsByTags($tags);

        if (!empty($users) && !empty($tag_fnums)) {
            // Store the evaluators
            $evaluators = [];

            foreach ($users as $user) {
                $fnums = $this->getFnumAssoc($user);
                $gFnums = $this->getFnumGroupAssoc($user);
                //merge both results to have one list of fnums
                $evaluators[$user] = array_merge($fnums, $gFnums);
            }

            if (!empty($evaluators)) {
                // Remove empty users
                $evaluators = array_filter($evaluators, function ($value) {
                    return !empty($value);
                });


                // Check if the tagged fnums are in our list of evaluators
                // If they are, we add the tagged date
                foreach ($evaluators as $evaluator => $val) {
                    foreach ($tag_fnums as $fnum => $date) {
                        if (array_key_exists($fnum, $val)) {
                            $evaluators[$evaluator][$fnum]['date'] = $date;
                        }
                    }
                }

                foreach ($evaluators as $key => $evaluator){
                    foreach ($evaluator as $val => $fnum){
                        if(!array_key_exists('date',$fnum)){
                            unset($evaluators[$key][$val]);
                        }
                    }
                }

                // Now check if the tagged date is a modulo of our param $date_interval
                // We are filtering out the files that aren't a modulo of the $date_interval
                foreach ($evaluators as $emailUser => $fnums) {
                    $evaluators[$emailUser] = array_filter($fnums, function ($data) use ($now, $date_interval) {

                        $createDate = new DateTime($data['date']);
                        $difference = $now->diff($createDate);
                        $days = $difference->days;


                        if($date_interval != 0) {
                            return (($days % $date_interval == 0) && $difference->days > 0);
                        } else {
                            if($days == 0){
                                return 1;
                            }
                        }
                    });
                }

                if (!empty($evaluators)) {
                    $cpt = 0;
                    include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
                    include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');
                    include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');

                    $m_profile = new EmundusModelProfile();
                    $m_emails = new EmundusModelEmails();
                    $c_messages = new EmundusControllerMessages();


                    $menu = JMenu::getInstance('site');

                    foreach ($evaluators as $emailUser => $fnums) {
                        if (empty($fnums)) {
                            continue;
                        }
                        // We send a email for each campaign for each user
                        $user = JFactory::getUser($emailUser);

                        $menutype = $m_profile->getProfileByApplicant($emailUser)['menutype'];
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
                        foreach ($fnums as $fnum) {
                            if(!empty($elements_to_replace)) {
                                $elements = $m_emails->setTagsFabrik($elements_to_replace, (array)$fnum);
                                $fnumList .= '
                                <li>
                                    <a href="'.JURI::base().'/dossiers#' . $fnum['fnum'] . '">' . $fnum['fnum'] . '</a>
                                </li>';
                            } else {
                                $fnumList .= '
                                <li>
                                    <a href="'.JURI::base().'/dossiers#' . $fnum['fnum'] . '">' . $fnum['fnum'] . '</a>
                                </li>';
                            }
                        }
                        $fnumList .= '</ul>';

                        $post = array(
                            'FNUMS' => $fnumList,
                            'NAME' => $user->name,
                        );

                        $c_messages->sendEmailNoFnum($user->email, $reminder_mail_id, $post, $user->id);
                        $cpt++;
                    }
                    JLog::add("\n process " . sizeof($evaluators) . " emails sent", JLog::INFO, 'com_emundus_eval_recall');
                    return $cpt;
                }
            }
        }
        return false;
    }

    /**
     * Gets all eMundus users linked to the Joomla acl_groups set in XML params
     *
     * @param array $groups Joomla acl_group
     * @return array
     *
     */
    function getEmundusUsers(array $groups): array {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(eu.user_id)'])
            ->from($db->quoteName('#__emundus_users', 'eu'))
            ->leftJoin($db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $db->quoteName('eu.user_id') . ' = ' . $db->quoteName('eup.user_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON ' . $db->quoteName('esp.id') . ' = ' . $db->quoteName('eup.profile_id'))
            ->where($db->quoteName('esp.acl_aro_groups') . ' IN (' . implode(', ', $groups) . ')');

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus_eval_recall');
            return [];
        }
    }

    /**
     * Gets all eMundus users linked to the eMundus profiles set in XML params
     *
     * @param array $profiles eMundus profiles
     * @return array
     *
     */
    function getEmundusUsersByProfile(array $profiles): array {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(eu.user_id)'])
            ->from($db->quoteName('#__emundus_users', 'eu'))
            ->leftJoin($db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $db->quoteName('eu.user_id') . ' = ' . $db->quoteName('eup.user_id'))
            ->where($db->quoteName('eup.profile_id') . ' IN (' . implode(', ', $profiles) . ')');

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus_eval_recall');
            return [];
        }
    }

    /**
     * Gets all eMundus users where the group is linked to a program
     *
     * @param array $profile Joomla acl_group
     * @return array
     *
     */
    public function getGroupsByProg(array $profile): array {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(eu.user_id)'])
            ->from($db->quoteName('#__emundus_users', 'eu'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON ' . $db->quoteName('esp.id') . ' = ' . $db->quoteName('eu.profile'))
            ->leftJoin($db->quoteName('#__emundus_groups', 'eg') . ' ON ' . $db->quoteName('eg.user_id') . ' = ' . $db->quoteName('eu.user_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'esgrc') . ' ON ' . $db->quoteName('esgrc.parent_id') . ' = ' . $db->quoteName('eg.group_id'))
            ->leftJoin($db->quoteName('#__emundus_acl', 'ea') . ' ON ' . $db->quoteName('eg.group_id') . ' = ' . $db->quoteName('ea.group_id'))
            ->where($db->quoteName('esp.acl_aro_groups') . ' IN(' . implode(', ', $profile) . ') AND ' . $db->quoteName('ea.action_id') . ' = 5 AND ' . $db->quoteName('ea.c') . ' = 1');

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus_eval_recall');
            return [];
        }
    }


    /**
     * Gets all fnums linked to a user through the user assoc table
     *
     * @param int $user eMundus User
     * @return array
     *
     */
    public function getFnumAssoc(int $user): array {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName('fnum'))
            ->from($db->quoteName('#__emundus_users_assoc'))
            ->where($db->quoteName('user_id') . ' = ' . $user);

        try {
            $db->setQuery($query);
            return $db->loadAssocList('fnum');
        } catch (Exception $e) {
            JLog::add('SQL Error -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus_eval_recall');
            return [];
        }
    }

    /**
     * Gets all fnums linked to a user through the group assoc table
     *
     * @param int $user eMundus User
     * @return array
     *
     */
    public function getFnumGroupAssoc(int $user): array {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName('fnum'))
            ->from($db->quoteName('#__emundus_group_assoc', 'ega'))
            ->leftJoin($db->quoteName('#__emundus_groups', 'eg') . ' ON ' . $db->quoteName('eg.group_id') . ' = ' . $db->quoteName('ega.group_id'))
            ->where($db->quoteName('ega.action_id') . ' = 5 AND ' . $db->quoteName('ega.c') . ' = 1 AND ' . $db->quoteName('eg.user_id') . ' = ' . $user);

        try {

            $db->setQuery($query);
            return $db->loadAssocList('fnum');
        } catch (Exception $e) {
            JLog::add('SQL Error -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus_eval_recall');
            return [];
        }
    }


    /**
     * Returns list of fnums linked to a specific tag
     * @param $tags
     * @return array
     */
    public function getFnumsByTags(array $tags): array{

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select([$db->quoteName('fnum'), 'DATE_FORMAT(date_time, "%Y-%m-%d") AS date_time'])
            ->from($db->quoteName('jos_emundus_tag_assoc'))
            ->where($db->quoteName('id_tag') . ' IN (' . implode(', ', $tags) . ')');

        $db->setQuery($query);

        try {
            return $db->loadAssocList('fnum', 'date_time');

        } catch (Exception $e) {
            JLog::add('SQL Error -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus_eval_recall');
            return [];
        }
    }

    /**
     * Return list of applicant profiles
     *
     * @return array
     */
    public function getApplicantProfiles(): array{
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__emundus_setup_profiles'))
            ->where($db->quoteName('published') . ' = 1 AND ' . $db->quoteName('status') . ' = 1');

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus_eval_recall');
            return [];
        }
    }
}

