<?php
/**
 * Dashboard model used for the new dashboard in homepage.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\CMS\Date\Date;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_onboard/models');

class EmundusonboardModeldashboard extends JModelList
{
    var $_db = null;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->offset=JFactory::getApplication()->get('offset', 'UTC');

        $this->_db = JFactory::getDBO();

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
        $query = $this->_db->getQuery(true);

        try {
            $query->select('sc.*, cc.id as files')
                ->from($this->_db->quoteName('#__emundus_setup_campaigns', 'sc'))
                ->leftJoin($this->_db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->_db->quoteName('cc.campaign_id') . ' = ' . $this->_db->quoteName('sc.id'))
                ->where('sc.published=1 AND "' . $this->now . '" <= sc.end_date and "' . $this->now . '">= sc.start_date')
                ->group('sc.id')
                ->order('sc.start_date DESC');

            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/dashboard | Error when try to get last active campaign : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }

    public function getwidgets(){
        $this->_db = JFactory::getDbo();
        $query = $this->_db->getQuery(true);

        try {
            $query->select('params')
                ->from($this->_db->quoteName('#__modules'))
                ->where($this->_db->quoteName('module') . ' LIKE ' . $this->_db->quote('mod_emundus_dashboard_vue'));

            $this->_db->setQuery($query);
            $modules = $this->_db->loadColumn();

            $widgets = array();

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

            return $widgets;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/dashboard | Error when try to get widgets : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
        }
    }

    public function getfilescountbystatus(){
        $this->_db = JFactory::getDbo();
        $query = $this->_db->getQuery(true);

        try {
            $query->select('*')
                ->from($this->_db->quoteName('#__emundus_setup_status'));
            $this->_db->setQuery($query);
            $status = $this->_db->loadObjectList();

            $files = [];

            foreach ($status as $statu) {
                $file = new stdClass;
                $file->label = $statu->value;

                $query->clear()
                    ->select('COUNT(id) as files')
                    ->from($this->_db->quoteName('#__emundus_campaign_candidature'))
                    ->where($this->_db->quoteName('status') . '=' . $this->_db->quote($statu->step));

                $this->_db->setQuery($query);
                $file->value = $this->_db->loadResult();
                $files[] = $file;
            }

            return array('files' => $files, 'status' => $status);
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/dashboard | Error when try to get files count by status : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return array('files' => '', 'status' => '');
        }
    }

    public function getfilesbycampaign($cid){
        $this->_db = JFactory::getDbo();
        $query = $this->_db->getQuery(true);

        try {
            $query->select('COUNT(id) as files')
                ->from($this->_db->quoteName('#__emundus_campaign_candidature'))
                ->where($this->_db->quoteName('campaign_id') . '=' . $this->_db->quote($cid));

            $this->_db->setQuery($query);
            return $this->_db->loadResult();
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/dashboard | Error when try to get files by campaign : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }

    public function getusersbyday(){
        $this->_db = JFactory::getDbo();

        $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
        $dateTime = $dateTime->setTimezone(new DateTimeZone($this->offset));

        try {
            $users = [];
            $days = [];

            $query = 'SELECT COUNT(id) as users
                            FROM jos_users';
            $this->_db->setQuery($query);
            $totalUsers = $this->_db->loadResult();

            for ($d = 1;$d < 31;$d++){
                $user = new stdClass;
                $day = new stdClass;
                $query = 'SELECT COUNT(id) as users
                            FROM jos_users
                            WHERE id != 62 AND YEAR(registerDate) = ' . $dateTime->format('Y') . ' AND MONTH(registerDate) = ' . $dateTime->format('m') . ' AND DAY(registerDate) = ' . $d;

                $this->_db->setQuery($query);
                $user->value = $this->_db->loadResult();
                $day->label = (string) $d;
                $users[] = $user;
                $days[] = $day;
            }

            return array('users' => $users, 'days' => $days, 'total' => $totalUsers);
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/dashboard | Error when try to get users by day : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return array('users' => '', 'days' => '', 'total' => 0);
        }
    }
}
