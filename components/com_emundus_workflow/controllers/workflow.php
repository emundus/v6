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
            $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('wid');        //get data from Vue template

            $_result = $this->model->deleteWorkflow($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("DELETE_WORKFLOW_SUCCESSFULLY"), 'data' => $_result);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("DELETE_WORKFLOW_FAILED"), 'data' => $_result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //get all workflows
    public function getallworkflows() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else {
            $_workflows = $this->model->getAllWorkflows();

            if (count($_workflows) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_WORKFLOWS_SUCCESSFULLY"), 'data' => $_workflows, 'count' => count($_workflows));
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_WORKFLOWS_FAILED"), 'data' => $_workflows, 'count' => count($_workflows));
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //get workflow by id --> call to model "getWorkflowByID"
    public function getworkflowbyid() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('wid');

            $_workflow = $this->model->getWorkflowByID($data);

            if($_workflow) {
                $tab = array('status' => 1, 'msg' => JText::_('GET_WORKFLOW_SUCCESSFULLY'), 'data' => $_workflow);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('GET_WORKFLOW_FAILED'), 'data' => $_workflow);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    public function createworkflow() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $data['user_id'] = $user->id;
            $data['created_at'] = date('Y-m-d H:i:s');

            $_workflow = $this->model->createWorkflow($data);

            if($_workflow) {
                $tab = array('status' => 1, 'msg' => JText::_('CREATE_WORKFLOW_SUCCESSFULLY'), 'data' => $_workflow);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('CREATE_WORKFLOW_FAILED'), 'data' => $_workflow);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    //update workflow -->
    public function updateworkflowlabel() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_result = $this->model->updateWorkflowLabel($data);

            if($_result) {
                $tab = array('status' => 1, 'msg' => JText::_('UPDATE_WORKFLOW_SUCCESSFULLY'), 'data' => $_result);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('UPDATE_WORKFLOW_FAILED'), 'data' => $_result);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    //get all available campaigns
    public function getallavailablecampaigns() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else {
            $_campaigns = $this->model->getAllAvailableCampaigns();

            if (count($_campaigns) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_AVAILABLES_CAMPAIGNS"), 'data' => $_campaigns, 'count' => count($_campaigns));
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_ALL_AVAILABLE_CAMPAIGNS"), 'data' => $_campaigns, 'count' => count($_campaigns));
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}
