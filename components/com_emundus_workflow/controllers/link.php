<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class EmundusworkflowControllerlink extends JControllerLegacy {
    var $model = null;

    public function __construct($config = array()) {
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('link'); //get item model
    }

    //create new link
    public function createlink() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_link = $this->model->createLink($data);

            if ($_link) {
                $tab = array('status' => 1, 'msg' => JText::_("CREATE_LINK_SUCCESSFULLY"), 'data' => $_link);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CREATE_LINK_FAILED"), 'data' => $_link);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //delete link
    public function deletelink() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_result = $this->model->deleteLink($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("DELETE_LINK_SUCCESSFULLY"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("DELETE_LINK_FAILED"), 'data' => $_result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //get all link
    public function getalllinks() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_links = $this->model->getAllLinksByStep($data);

            if ($_links) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_LINK_SUCCESSFULLY"), 'data' => $_links);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_LINK_FAILED"), 'data' => $_links);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}
