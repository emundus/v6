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

class PlgFabrik_Cronemundusglobalrecall extends PlgFabrik_Cron {

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
        $acl_aro_groups = $params->get('acl_aro_groups', '');
        $emundus_groups = $params->get('emundus_groups', '');
        $emundus_profiles = $params->get('emundus_profiles', '');
        $status = $params->get('status', '');
        $tags = $params->get('tags', '');

        $acl_users = array();
        if(!empty($acl_aro_groups)) {
            $acl_users = $this->getEmundusUsersByAclGroups($acl_aro_groups);
        }

        $emundus_groups_users = array();
        if(!empty($emundus_groups)) {
            $emundus_groups_users = $this->getEmundusUsersByGroups($emundus_groups);
        }

        $emundus_profiles_users = array();
        if(!empty($emundus_profiles)) {
            $emundus_profiles_users = $this->getEmundusUsersByProfiles($emundus_profiles);
        }

        $users = array_unique(array_merge($acl_users, $emundus_groups_users, $emundus_profiles_users));

        if (!empty($users)) {
            // Store the evaluators
            $assoc_users = [];

            foreach ($users as $user) {
                $fnums = $this->getFnumAssoc($user,$status,$tags);
                //$gFnums = $this->getFnumGroupAssoc($user,$status,$tags);
                //merge both results to have one list of fnums
                //$assoc_users[$user] = array_unique(array_merge($fnums, $gFnums));
                $assoc_users[$user] = $fnums;
            }

            if (!empty($assoc_users)) {
                include_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
                include_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
                include_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
                include_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');

                $m_profile = new EmundusModelProfile();
                $m_emails = new EmundusModelEmails();
                $m_files = new EmundusModelFiles();
                $c_messages = new EmundusControllerMessages();

                $emailArray = [];

                foreach ($assoc_users as $assoc_user => $fnums) {
                    foreach ($fnums as $fnum) {
                        $fnumInfos = $m_files->getFnumInfos($fnum);

                        $emailArray[$assoc_user][$fnumInfos['id']]["label"] = $fnumInfos['label'];
                        $emailArray[$assoc_user][$fnumInfos['id']]["fnums"][] = $fnum;
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
                                $fnumList .= '<li><p>'.$fnum.'</p></li>';
                            }
                            $fnumList .= '</ul>';

                            if (!empty($params->get('logo')->custom->image)) {
                                $logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
                                $logo = !empty($logo['path']) ? JURI::base().$logo['path'] : "";

                            } else {
                                $logo_module = JModuleHelper::getModuleById('90');
                                preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
                                $pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

                                if ((bool) preg_match($pattern, $tab[1])) {
                                    $tab[1] = parse_url($tab[1], PHP_URL_PATH);
                                }

                                $logo = JURI::base().$tab[1];
                            }

                            $post = array(
                                'FNUMS' => $fnumList,
                                'CAMPAIGN_LABEL' => $campaign['label'],
                                'EVALUATION_END' => strftime("%d/%m/%Y %H:%M", strtotime($campaign['eval_end_date'])),
                                'NAME' => $user->name,
                                'SITE_URL' => JURI::base(),
                                'SITE_NAME' => JFactory::getConfig()->get('sitename'),
                                'LOGO' => $logo,
                            );

                            $c_messages->sendEmailNoFnum($user->email, $reminder_mail_id, $post);
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
    public function getEmundusUsersByAclGroups($acl_groups) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(eu.user_id)'])
            ->from($db->quoteName('#__emundus_users', 'eu'))
            ->leftJoin($db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $db->quoteName('eu.user_id') . ' = '. $db->quoteName('eup.user_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON ' . $db->quoteName('esp.id') . ' = '. $db->quoteName('eup.profile_id'))
            ->where($db->quoteName('esp.acl_aro_groups') . ' IN (' . $acl_groups. ')');

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getEmundusUsersByGroups($groups) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(eu.user_id)'])
            ->from($db->quoteName('#__emundus_users', 'eu'))
            ->leftJoin($db->quoteName('#__emundus_groups', 'eg') . ' ON ' . $db->quoteName('eu.user_id') . ' = '. $db->quoteName('eg.user_id'))
            ->where($db->quoteName('eg.group_id') . ' IN (' . $groups. ')');

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getEmundusUsersByProfiles($profiles) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(eu.user_id)'])
            ->from($db->quoteName('#__emundus_users', 'eu'))
            ->leftJoin($db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $db->quoteName('eu.user_id') . ' = '. $db->quoteName('eup.user_id'))
            ->where($db->quoteName('eup.profile_id') . ' IN (' . $profiles. ')');

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
    public function getFnumAssoc($user,$status,$tags) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('distinct eua.fnum')
            ->from($db->quoteName('#__emundus_users_assoc','eua'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature','cc').' ON '.$db->quoteName('cc.fnum').' = '.$db->quoteName('eua.fnum'))
            ->where($db->quoteName('eua.user_id') . ' = ' . $user );

        if(!empty($status)){
            $query->where($db->quoteName('cc.status') . ' IN (' . $status . ')');
        }

        if(!empty($tags)){
            $query->leftJoin($db->quoteName('#__emundus_tag_assoc','eta').' ON '.$db->quoteName('eta.fnum').' = '.$db->quoteName('cc.fnum'))
                ->where($db->quoteName('eta.id_tag') . ' IN (' . $tags . ')');
        }

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
    public function getFnumGroupAssoc($user,$status,$tags) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select('distinct ega.fnum')
            ->from($db->quoteName('#__emundus_group_assoc', 'ega'))
            ->leftJoin($db->quoteName('#__emundus_groups', 'eg') . ' ON ' . $db->quoteName('eg.group_id') . ' = '. $db->quoteName('ega.group_id'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature','cc').' ON '.$db->quoteName('cc.fnum').' = '.$db->quoteName('ega.fnum'))
            ->where($db->quoteName('eg.user_id') . ' = ' . $user );

        if(!empty($status)){
            $query->where($db->quoteName('cc.status') . ' IN (' . $status . ')');
        }

        if(!empty($tags)){
            $query->leftJoin($db->quoteName('#__emundus_tag_assoc','eta').' ON '.$db->quoteName('eta.fnum').' = '.$db->quoteName('cc.fnum'))
                ->where($db->quoteName('eta.id_tag') . ' IN (' . $tags . ')');
        }

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('SQL Error -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}

