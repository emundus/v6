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
        try {
            $module = JModuleHelper::getModule('mod_emundus_dashboard_vue','Dashboard vue');
            $params = new JRegistry($module->params);
            $widgets[] = $params['widget1'];
            $widgets[] = $params['widget2'];
            $widgets[] = $params['widget3'];
            $widgets[] = $params['widget4'];
            $widgets[] = $params['widget5'];
            $widgets[] = $params['widget6'];
            $widgets[] = $params['widget7'];
            $widgets[] = $params['widget8'];

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

        $jinput = JFactory::getApplication()->input;

        $status = $jinput->getString('status');

        try {
            if($status != null){
                $condition = 'ss.step=' . $status;
                $query->select('ss.*,COUNT(cc.id) as files')
                    ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
                    ->leftJoin($db->quoteName('#__emundus_setup_status','ss').' ON '.$db->quoteName('ss.step').' = '.$db->quoteName('cc.status'))
                    ->where($condition);
            } else {
                $query->select('COUNT(cc.id) as files')
                    ->from($db->quoteName('#__emundus_campaign_candidature','cc'));
            }

            $db->setQuery($query);
            $files = $db->loadObject();

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $files);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }
}
