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
jimport('joomla.application.component.model');
/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusworkflowControllerworkflow extends JControllerLegacy {

    var $model= null;
    var $model_campaign = null;
    var $_campaigns = null;
    var $_campaigns_name = null;

    public function __construct($config=array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel("workflow");

        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_onboard/models');
        $this->model_campaign = JModelLegacy::getInstance('campaign', 'EmundusonboardModel');
        $this->_campaigns = $this->model_campaign->getAssociatedCampaigns(null,null,null,null,null);
    }

    //delete workflow --> data from Vue
    public function deleteworkflow() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else {
            $_wid = $this->model;

            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('wid');        //get data from Vue template

            //do stuff
            $workflows = $_wid->deleteWorkflow($data);

            if ($workflows) {
                $tab = array('status' => 1, 'msg' => JText::_("WORKFLOW_DELETED"), 'data' => $workflows);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_DELETE_WORKFLOW"), 'data' => $workflows);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //get all workflowS
    public function getallworkflows() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else {
            $_wid = $this->model;

            //do stuff
            $workflows = $_wid->getAllWorkflows();

            if (count($workflows) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("WORKFLOW_RETRIEVED"), 'data' => $workflows, 'count' => count($workflows));
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_RETRIEVE_WORKFLOW"), 'data' => $workflows, 'count' => count($workflows));
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //get workflow by id --> call to model "getWorkflowByID"
    public function getworkflowbyid() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('wid');

            $_wid = $this->model;

            $_workflow = $_wid->getWorkflowByID($data);

            if($_workflow) {
                $tab = array('status' => 1, 'msg' => JText::_('RETRIEVED_WORKFLOW_BY_ID'), 'data' => $_workflow);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('CANNOT_RETRIEVED_WORKFLOW_BY_ID'), 'data' => $_workflow);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    //get associated campaigns
    public function getassociatedcampaigns() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            if(count($this->_campaigns) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_RETRIEVED'), 'data' => $this->_campaigns);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('CANNOT_RETRIEVE_CAMPAIGN'), 'data' => $this->_campaigns);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    //get campaign by id
    public function getcampaignbyid() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_wid = $this->model;

            $_workflow = $_wid->getCampaignByID($data);

            if($_workflow) {
                $tab = array('status' => 1, 'msg' => JText::_('GET_CAMPAIGN'), 'data' => $_workflow);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('CANNOT_GET_CAMPAIGN'), 'data' => $_workflow);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    public function createworkflow() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');
            $data['user_id'] = $user->id;
            $data['created_at'] = date('Y-m-d H:i:s');

            $_wid = $this->model;

            $_workflow = $_wid->createWorkflow($data);

            if($_workflow) {
                $tab = array('status' => 1, 'msg' => JText::_('WORKFLOW_CREATED'), 'data' => $_workflow);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('CANNOT_CREATE_WORKFLOW'), 'data' => $_workflow);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    //tracking last saved time of workflow
    public function updatelastsavedworkflow() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_wid = $this->model;

            $_workflow = $_wid->updateLastSavingWorkflow($data);

            if($_workflow) {
                $tab = array('status' => 1, 'msg' => JText::_('UPDATE_LAST_SAVING'), 'data' => $_workflow);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('CANNOT_UPDATE_LAST_SAVING'), 'data' => $_workflow);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    //update workflow
    public function updateworkflow() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_wid = $this->model;

            $_workflow = $_wid->updateWorkflow($data);

            if($_workflow) {
                $tab = array('status' => 1, 'msg' => JText::_('UPDATE_WORKFLOW'), 'data' => $_workflow);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('CANNOT_UPDATE_WORKFLOW'), 'data' => $_workflow);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }
}
