<?php

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class EmundusworkflowControllerstep extends JControllerLegacy {
    var $model = null;

    public function __construct($config = array()) {
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('step');
    }

    public function getallsteps() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_cit = $this->model;

            $_items = $_cit->getAllStepsByWorkflow($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_STEPS"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_ALL_STEPS"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;

    }

    public function createstep() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_cit = $this->model;
            $_items = $_cit->createStep($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("STEP_CREATED"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_CREATE_STEP"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;

    }

    public function deletestep() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('id');

            $_cit = $this->model;
            $_items = $_cit->deleteStep($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("DELETE_STEP"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_DELETE_STEP"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updateparams() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_cit = $this->model;
            $_items = $_cit->updateStepParams($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("UPDATE_STEP_PARAMS"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_UPDATE_STEP_PARAMS"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getcurrentparams() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('sid');

            $_cit = $this->model;
            $_items = $_cit->getCurrentParamsByStep($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_CURRENT_PARAMS"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_CURRENT_PARAMS"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getavailablestatus() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_cit = $this->model;
            $_status_in = $_cit->getAvailableStatus($data, 'in');
            $_status_out = $_cit->getAvailableStatus($data, 'out');

            if ($_status_in and $_status_out) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_AVAILABLE_STATUS"), 'dataIn' => $_status_in, 'dataOut' => $_status_out);
            }

            else if ($_status_in and empty($_status_out)){
                $tab = array('status' => 1, 'msg' => JText::_("GET_AVAILABLE_STATUS"), 'dataIn' => $_status_in, 'dataOut' => null);
            }

            else if (empty($_status_in) and $_status_out) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_AVAILABLE_STATUS"), 'dataIn' => null, 'dataOut' => $_status_out);
            }
            else {
                $tab = array('status' => 1, 'msg' => JText::_("GET_AVAILABLE_STATUS"), 'dataIn' => null, 'dataOut' => null);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatesteplabel() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            var_dump($data);die;

            $_cit = $this->model;
            $_items = $_cit->updateStepLabel($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("UPDATE_STEP_LABEL"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_UPDATE_STEP_LABEL"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}