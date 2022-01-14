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

    public function getpalettecolors(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $app    = JFactory::getApplication();
            $menu   = $app->getMenu();
            $active = $menu->getActive();
            if(empty($active)){
                $menuid = 1079;
            } else {
                $menuid = $active->id;
            }

            $query->select('m.params')
                ->from($db->quoteName('#__modules','m'))
                ->leftJoin($db->quoteName('#__modules_menu','mm').' ON '.$db->quoteName('mm.moduleid').' = '.$db->quoteName('m.id'))
                ->where($db->quoteName('m.module') . ' LIKE ' . $db->quote('mod_emundus_dashboard_vue'))
                ->andWhere($db->quoteName('mm.menuid') . ' = ' . $menuid);

            $db->setQuery($query);
            $modules = $db->loadColumn();

            foreach ($modules as $module) {
                $params = json_decode($module, true);
                if (in_array(JFactory::getSession()->get('emundusUser')->profile,$params['profile'])) {
                    $colors = $params['colors'];
                }
            }

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $colors);
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

    public function getfilescountbystatusgroupbydate(){
        try {
            $jinput = JFactory::getApplication()->input;

            $program = $jinput->getString('program');

            $results = $this->model->getfilescountbystatusgroupbydate($program);

            $tab = array('msg' => 'success', 'dataset' => $results['dataset'], 'category' => $results['category']);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatusandsession(){
        try {
            $jinput = JFactory::getApplication()->input;

            $program = $jinput->getString('program');

            $results = $this->model->getfilescountbystatusandsession($program);

            $tab = array('msg' => 'success', 'dataset' => $results['dataset'], 'category' => $results['category']);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatusandcourses(){
        try {
            $jinput = JFactory::getApplication()->input;
            $program = $jinput->getString('program');
            $session = $jinput->getString('session');

            $results = $this->model->getfilescountbystatusandcourses($program,$session);

            $tab = array('msg' => 'success', 'dataset' => $results['dataset'], 'category' => $results['category']);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatusandcoursesprecollege(){
        try {
            $jinput = JFactory::getApplication()->input;
            $session = $jinput->getString('session');

            $results = $this->model->getfilescountbystatusandcoursesprecollege($session);

            $tab = array('msg' => 'success', 'dataset' => $results['dataset'], 'category' => $results['category']);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbynationalities(){
        try {
            $jinput = JFactory::getApplication()->input;
            $program = $jinput->getString('program');

            $results = $this->model->getfilescountbynationalities($program);

            $tab = array('msg' => 'success', 'dataset' => $results['dataset'], 'category' => $results['category']);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }
}
