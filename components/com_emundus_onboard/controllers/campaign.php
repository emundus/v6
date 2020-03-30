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
class EmundusonboardControllercampaign extends JControllerLegacy {

    var $model = null;
    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('campaign');
    }

    public function getcampaigncount() {
        $user = JFactory::getUser();
        $m_camp = $this->model;
        $filterCount = $_GET['filterCount'];
        $rechercheCount = $_GET['rechercheCount'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $campaigns = $m_camp->getCampaignCount($user->id, $filterCount, $rechercheCount);

            if ($campaigns > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $campaigns);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CAMPAIGNS'), 'data' => $campaigns);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getallcampaign() {
        $user = JFactory::getUser();
        $m_camp = $this->model;

        $filter = $_GET['filter'];
        $sort = $_GET['sort'];
        $recherche = $_GET['recherche'];
        $lim = $_GET['lim'];
        $page = $_GET['page'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $campaigns = $m_camp->getAssociatedCampaigns($user->id, $filter, $sort, $recherche, $lim, $page);

            if (count($campaigns) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $campaigns);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CAMPAIGNS'), 'data' => $campaigns);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function deletecampaign() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_camp->deleteCampaign($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_DELETED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function unpublishcampaign() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_camp->unpublishCampaign($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_UNPUBLISHED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UNPUBLISH_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function publishcampaign() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_camp->publishCampaign($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_PUBLISHED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_PUBLISH_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function duplicatecampaign() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_camp->duplicateCampaign($data);

            if ($result) {
                $this->getallcampaign();
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DUPLICAT_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //TODO Throw in the years controller
    public function getyears() {
        $user = JFactory::getUser();
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $years = $m_camp->getYears($user->id);

            if ($years > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $years);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CAMPAIGNS'), 'data' => $years);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //TODO Throw into profile controller
    public function getapplicantprofiles() {
        $user = JFactory::getUser();
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $profiles = $m_camp->getApplicantsProfiles();

            if ($profiles > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('PROFILES_RETRIEVED'), 'data' => $profiles);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CPROFILES'), 'data' => $profiles);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createcampaign() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $data['user'] = $user->id;

            $result = $m_camp->createCampaign($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatecampaign() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $cid = $jinput->getRaw('cid');
        $m_camp = $this->model;


        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $data['user'] = $user->id;

            $result = $m_camp->updateCampaign($data, $cid);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_UPDATED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //TODO Throw in the years controller
    //TODO Not getting the program category in data... Gonna need to get it
    public function createyear() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $m_camp = $this->model;


        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $result = $m_camp->createYear($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('YEAR_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_YEAR'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getcampaignbyid() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->get->get('id');
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $campaign = $m_camp->getCampaignById($id);
            if (!empty($campaign)) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_RETRIEVED'), 'data' => $campaign);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CAMPAIGN'), 'data' => $campaign);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getcreatedcampaign() {
        $user = JFactory::getUser();
        $m_camp = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $campaign = $m_camp->getCreatedCampaign();
            if (!empty($campaign)) {
                $tab = array('status' => 1, 'msg' => JText::_('CREATED_CAMPAIGN_RETRIEVED'), 'data' => $campaign);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CREATED_CAMPAIGN'), 'data' => $campaign);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createprofile() {
        $jinput = JFactory::getApplication()->input;
        $profile = $jinput->get('profile');
        $m_camp = $this->model;


        $result = $m_camp->createProfile($profile);

        if ($result) {
            $tab = array('status' => 1, 'msg' => JText::_('PROFILE_ADDED'), 'data' => $result);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROFILE'), 'data' => $result);
        }

        echo json_encode((object)$tab);
        exit;
    }
    
    public function getallprofiles() {
        $m_camp = $this->model;

        $result = $m_camp->getAllProfiles();

        if ($result) {
            $tab = array('status' => 1, 'msg' => JText::_('PROFILE_ADDED'), 'data' => $result);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROFILE'), 'data' => $result);
        }

        echo json_encode((object)$tab);
        exit;
    }
}

