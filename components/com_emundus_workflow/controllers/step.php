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
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_STEPS_SUCCESSFULLY"), 'data' => $_steps);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_STEPS_FAILED_OR_NOTHING_STEP"), 'data' => $_steps);
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

            $_step = $this->model->createStep($data);

            if ($_step) {
                $tab = array('status' => 1, 'msg' => JText::_("CREATE_STEP_SUCCESSFULLY"), 'data' => $_step);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CREATE_STEP_FAILED"), 'data' => $_step);
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

            $_result = $this->model->deleteStep($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("DELETE_STEP_SUCCESSFULLY"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("DELETE_STEP_FAILED"), 'data' => $_result);
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

            $_result = $this->model->updateStepParams($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("UPDATE_STEP_PARAMS_SUCCESSFULLY"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("UPDATE_STEP_PARAMS_FAILED"), 'data' => $_result);
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
                $tab = array('status' => 1, 'msg' => JText::_("GET_CURRENT_PARAMS_SUCCESSFULLY"), 'data' => $_params);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_CURRENT_PARAMS_FAILED_OR_NOTHING_PARAMS"), 'data' => $_params);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getstatusattributs() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('sid');

            $_params = $this->model->getStatusAttributsFromStep($data);

            if ($_params) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_CURRENT_PARAMS_SUCCESSFULLY"), 'data' => $_params);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_CURRENT_PARAMS_FAILED_OR_NOTHING_PARAMS"), 'data' => $_params);
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
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_AVAILABLE_STATUS_SUCCESSFULLY"), 'dataIn' => $_status_in, 'dataOut' => $_status_out);
            }

            else if ($_status_in and empty($_status_out)){
                $tab = array('status' => 1, 'msg' => JText::_("JUST_GET_AVAILABLE_INPUT_STATUS"), 'dataIn' => $_status_in, 'dataOut' => null);
            }

            else if (empty($_status_in) and $_status_out) {
                $tab = array('status' => 1, 'msg' => JText::_("JUST_GET_AVAILABLE_OUTPUT_STATUS"), 'dataIn' => null, 'dataOut' => $_status_out);
            }
            else {
                $tab = array('status' => 1, 'msg' => JText::_("GET_AVAILABLE_STATUS_FAILED"), 'dataIn' => null, 'dataOut' => null);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /// update the step ordering when chaning
    public function updatestepordering() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');            // get data from vuejs
            $wid = $jinput->getRaw('wid');

            $_newOrder = $this->model->updateStepOrdering($data, $wid);

            if (!empty($_newOrder)) {
                $tab = array('status' => 1, 'msg' => JText::_("UPDATE_NEW_STEP_ORDER"), 'data' => $_newOrder);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_UPDATE_NEW_STEP_ORDER"), 'data' => $_newOrder);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }
}