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
            $data = $jinput->getRaw('wid');

            $_steps = $this->model->getAllStepsByWorkflow($data);

            if ($_steps) {
                $tab = array('status' => 1, 'msg' => JText::_("RETRIEVED_ALL_STEPS"), 'data' => $_steps);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_RETRIEVE_ALL_STEPS"), 'data' => $_steps);
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

            $_steps = $this->model->createStep($data);

            if ($_steps) {
                $tab = array('status' => 1, 'msg' => JText::_("CREATE_STEP"), 'data' => $_steps);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_CREATE_STEP"), 'data' => $_steps);
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
            $data = $jinput->getRaw('data');

            $_results = $this->model->deleteStep($data);

            if ($_results) {
                $tab = array('status' => 1, 'msg' => JText::_("DELETE_STEP"), 'data' => $_results);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_DELETE_STEP"), 'data' => $_results);
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

            $_update = $this->model->updateStepParams($data);

            if ($_update) {
                $tab = array('status' => 1, 'msg' => JText::_("UPDATE_STEP_PARAMS"), 'data' => $_update);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_UPDATE_STEP_PARAMS"), 'data' => $_update);
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

            $_params = $this->model->getCurrentParamsByStep($data);

            if ($_params) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_CURRENT_PARAMS"), 'data' => $_params);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_CURRENT_PARAMS"), 'data' => $_params);
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

            $_status_in = $this->model->getAvailableStatus($data, 'in');
            $_status_out = $this->model->getAvailableStatus($data, 'out');

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
}