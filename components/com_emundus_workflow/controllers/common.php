<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');

class EmundusworkflowControllercommon extends JControllerLegacy {
    var $_common_model = null;

    var $_published_profile_model = null;
    var $_status_model = null;
    var $_message_model = null;

    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);

        // call to "common model
        $this->_common_model = $this->getModel('common');

        //call to EmundusonboardModel
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_onboard/models');

        $this->_published_profile_model = JModelLegacy::getInstance('form', 'EmundusonboardModel');
        $this->_status_model = JModelLegacy::getInstance('settings', 'EmundusonboardModel');
        $this->_message_model = JModelLegacy::getInstance('email', 'EmundusonboardModel');
    }

    // get all published forms
    public function getallpublishedforms() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            $_publishedForms = $this->_published_profile_model->getAllFormsPublished();
            if ($_publishedForms) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_PUBLISHED_PROFILE_SUCCESSFULLY"), 'data' => $_publishedForms);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_PUBLISHED_PROFILE_FAILED"), 'data' => $_publishedForms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getallstatus() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            $_status = $this->_status_model->getStatus();
            if (count($_status) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_STATUS_SUCCESSFULLY"), 'data' => $_status);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_STATUS_FAILED"), 'data' => $_status);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    // get all associated groups
    public function getalldestinations() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            $_receivers = $this->_common_model->getAllDestinations();

            if (count($_receivers) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_DESTINATIONS_SUCCESSFULLY"), 'data' => $_receivers);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_DESTINATIONS_FAILED"), 'data' => $_receivers);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    // get all messages
    public function getallmessages() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            $_messages = $this->_message_model->getAllEmails(null,null,null,null,null);
            if (count($_messages) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_MESSAGE_TEMPLATE_SUCCESSFULLY"), 'data' => $_messages);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_MESSAGE_TEMPLATE_FAILED"), 'data' => $_messages);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    // get all users
    public function getallusers() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            $_users = $this->_common_model->getAllUsers();

            if (count($_users) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_USERS"), 'data' => $_users);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_USERS_FAILED"), 'data' => $_users);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    // create trigger
    public function createtrigger() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('trigger');
            $users = $jinput->getRaw('users');

            $_triggers = $this->_common_model->createEmailTriggerForCampaign($data,$users);

            if ($_triggers) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_USERS"), 'data' => $_triggers);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_USERS_FAILED"), 'data' => $_triggers);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /// create new html element
    public function createelement() {
        $user = JFactory::getUser();
        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');
            $_element = $this->_common_model->createElement($data);

            if ($_element) {
                $tab = array('status' => 1, 'msg' => JText::_("CREATE_ELEMENT_SUCCESSFULLY"), 'data' => $_element);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("CREATE_ELEMENT_FAILED"), 'data' => $_element);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /// remove html element
    public function deleteelement() {
        $user = JFactory::getUser();
        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getRaw('id');

            $_element = $this->_common_model->deleteElement($id);

            if ($_element) {
                $tab = array('status' => 1, 'msg' => JText::_("DELETE_ELEMENT_SUCCESSFULLY"), 'data' => $_element);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("DELETE_ELEMENT_FAILED"), 'data' => $_element);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /// get all html elements
    public function getallelements() {
        $user = JFactory::getUser();
        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;

            $_elements = $this->_common_model->getAllElements();

            if ($_elements) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_ELEMENTS_SUCCESSFULLY"), 'data' => $_elements);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_ELEMENTS_FAILED"), 'data' => $_elements);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /// get all html elements by parent type
    public function getelementsbytype() {
        $user = JFactory::getUser();
        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_elements = $this->_common_model->getElementsByType($data);

            if ($_elements) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ELEMENTS_BY_TYPE_SUCCESSFULLY"), 'data' => $_elements);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ELEMENTS_BY_TYPE_FAILED"), 'data' => $_elements);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}