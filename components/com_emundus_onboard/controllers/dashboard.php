<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusonboardControllerdashboard extends JControllerLegacy
{

    var $model = null;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->offset=JFactory::getApplication()->get('offset', 'UTC');
        try {
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($this->offset));
            $this->now = $dateTime->format('Y-m-d H:i:s');
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/controllers/dashboard | Error at defining the offset datetime : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    /**
     * Get the last active campaign
     */
    public function getLastCampaignActive(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('sc.*, cc.id as files')
                ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $db->quoteName('cc.campaign_id') . ' = ' . $db->quoteName('sc.id'))
                ->where('sc.published=1 AND "' . $this->now . '" <= sc.end_date and "' . $this->now . '">= sc.start_date')
                ->group('sc.id')
                ->order('sc.start_date DESC');

            $db->setQuery($query);
            $campaigns = $db->loadObjectList();

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $campaigns);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getwidgets(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('params')
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_dashboard_vue'));

            $db->setQuery($query);
            $modules = $db->loadColumn();

            foreach ($modules as $module) {
                $params = json_decode($module, true);
                if (JFactory::getSession()->get('emundusUser')->profile == $params['profile']) {
                    $widgets[] = $params['widget1'];
                    $widgets[] = $params['widget2'];
                    $widgets[] = $params['widget3'];
                    $widgets[] = $params['widget4'];
                    $widgets[] = $params['widget5'];
                    $widgets[] = $params['widget6'];
                    $widgets[] = $params['widget7'];
                    $widgets[] = $params['widget8'];
                }
            }

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $widgets);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatus(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('*')
                ->from($db->quoteName('#__emundus_setup_status'));
            $db->setQuery($query);
            $status = $db->loadObjectList();

            $files = [];

            foreach ($status as $statu) {
                $file = new stdClass;
                $file->label = $statu->value;

                $query->clear()
                    ->select('COUNT(id) as files')
                    ->from($db->quoteName('#__emundus_campaign_candidature'))
                    ->where($db->quoteName('status') . '=' . $db->quote($statu->step));

                $db->setQuery($query);
                $file->value = $db->loadResult();
                $files[] = $file;
            }

            $tab = array('msg' => 'success', 'files' => $files, 'status' => $status);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilesbycampaign(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $jinput = JFactory::getApplication()->input;

        $cid = $jinput->getInt('cid');

        try {
            $query->select('COUNT(id) as files')
                ->from($db->quoteName('#__emundus_campaign_candidature'))
                ->where($db->quoteName('campaign_id') . '=' . $db->quote($cid));

            $db->setQuery($query);
            $files = $db->loadResult();

            $tab = array('msg' => 'success', 'data' => $files);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getusersbyday(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
        $dateTime = $dateTime->setTimezone(new DateTimeZone($this->offset));

        try {
            $users = [];
            $days = [];

            $query = 'SELECT COUNT(id) as users
                            FROM jos_users';
            $db->setQuery($query);
            $totalUsers = $db->loadResult();

            for ($d = 1;$d < 31;$d++){
                $user = new stdClass;
                $day = new stdClass;
                $query = 'SELECT COUNT(id) as users
                            FROM jos_users
                            WHERE YEAR(registerDate) = ' . $dateTime->format('Y') . ' AND MONTH(registerDate) = ' . $dateTime->format('m') . ' AND DAY(registerDate) = ' . $d;

                $db->setQuery($query);
                $user->value = $db->loadResult();
                $day->label = (string) $d;
                $users[] = $user;
                $days[] = $day;
            }

            $tab = array('msg' => 'success', 'users' => $users, 'days' => $days, 'total' => $totalUsers);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfirstcoordinatorconnection(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $user = JFactory::getUser();
            $table = JTable::getInstance('user', 'JTable');
            $table->load($user->id);

            $params = $user->getParameters();
            if ($params->get('first_login_date')) {
                $register_at = $params->get('first_login_date');
            } else {
                $register_at = '0000-00-00 00:00:00';
            }

            $tab = array('msg' => 'success', 'data' => $register_at);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }
}
