<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'groups.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');

class plgEmundusLimit_obtained_alert extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.limit_obtained_alert.php'), JLog::ALL, array('com_emundus'));
    }


    function onAfterStatusChange($fnum) {

        $cid = (int)substr($fnum, 14, 7);
        $email = $this->params->get('limit_alert_email', 'limit_obtained_alert');

        if (empty($cid) || empty($email)) {
            return false;
        }

        $limit_set = $this->isLimitSet($cid);

        if ($limit_set === true) {
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $all_rights_grp = $eMConfig->get('all_rights_group', 1);

            $m_groups = new EmundusModelGroups();
            $users = $m_groups->getUsersByGroup($all_rights_grp);
            $m_campaign = new EmundusModelCampaign;
            $campaign = $m_campaign->getCampaignByID($cid);
            $c_messages = new EmundusControllerMessages();

            foreach ($users as $user) {
                $user = JFactory::getUser($user);
                $post = [
                    'NAME' => $user->name,
                    'SITE_NAME' => JFactory::getConfig()->get('sitename'),
                    'CAMPAIGN_LABEL' => $campaign["label"]
                ];
                $c_messages->sendEmailNoFnum($user->email, $email, $post);
            }
        }
        return true;
    }


    function onAfterSubmitFile($student_id, $fnum) {

        $cid = (int)substr($fnum, 14, 7);
        $email = $this->params->get('limit_alert_email');

        if (empty($cid) || empty($email)) {
            return false;
        }

        $limit_set = $this->isLimitSet($cid);

        if ($limit_set === true) {
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $all_rights_grp = $eMConfig->get('all_rights_group', 1);

            $m_groups = new EmundusModelGroups();
            $users = $m_groups->getUsersByGroup($all_rights_grp);
            $m_campaign = new EmundusModelCampaign;
            $campaign = $m_campaign->getCampaignByID($cid);
            $c_messages = new EmundusControllerMessages();

            foreach ($users as $user) {
                $user = JFactory::getUser($user);
                $post = [
                    'NAME' => $user->name,
                    'SITE_NAME' => JFactory::getConfig()->get('sitename'),
                    'CAMPAIGN_LABEL' => $campaign["label"]
                ];
                $c_messages->sendEmailNoFnum($user->email, $email, $post);
            }
        }
        return true;
    }

    private function isLimitSet($cid) {
        $m_campaign = new EmundusModelCampaign;
        $limit = $m_campaign->getLimit($cid);

        if (!empty($limit->is_limited)) {

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('COUNT(id)')
                ->from($db->quoteName('#__emundus_campaign_candidature'))
                ->where($db->quoteName('status') . ' IN (' . $limit->steps . ')');

            try {

                $db->setQuery($query);
                return $limit->limit == $db->loadResult();

            } catch (Exception $exception) {

                JLog::add('Error checking obtained limit at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                return null;

            }
        }
    }

}
