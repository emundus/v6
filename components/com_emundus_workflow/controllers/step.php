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
}