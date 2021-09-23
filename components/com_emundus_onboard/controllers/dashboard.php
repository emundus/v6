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
        $this->model = $this->getModel('dashboard');
    }

    /**
     * Get the last active campaign
     */
    public function getLastCampaignActive(){
        try {
            $campaigns = $this->model->getLastCampaignActive();

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $campaigns);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getwidgets(){
        try {
            $widgets = $this->model->getwidgets();

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $widgets);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatus(){
        try {
            $results = $this->model->getfilescountbystatus();

            $tab = array('msg' => 'success', 'files' => $results['files'], 'status' => $results['status']);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilesbycampaign(){
        $jinput = JFactory::getApplication()->input;

        $cid = $jinput->getInt('cid');

        try {
            $files = $this->model->getfilesbycampaign($cid);

            $tab = array('msg' => 'success', 'data' => $files);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getusersbyday(){
        try {
            $results = $this->model->getusersbyday();

            $tab = array('msg' => 'success', 'users' => $results['users'], 'days' => $results['days'], 'total' => $results['total']);
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
