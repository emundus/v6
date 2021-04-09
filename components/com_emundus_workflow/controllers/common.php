<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');

class EmundusworkflowControllercommon extends JControllerLegacy {
    var $_common_model = null;

    var $_published_profile_model = null;
    var $_published_profile = null;

    var $_status_model = null;
    var $_status = null;

    var $_message_model = null;
    var $_message = null;

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

        $this->_published_profile = $this->_published_profile_model->getAllFormsPublished();
        $this->_status = $this->_status_model->getStatus();
        $this->_message = $this->_message_model->getAllEmails(null,null,null,null,null);
    }

    // get all published forms
    public function getallpublishedforms() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status'=> 0, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            if ($this->_published_profile) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_PUBLISHED_PROFILE_SUCCESSFULLY"), 'data' => $this->_published_profile);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_PUBLISHED_PROFILE_FAILED"), 'data' => $this->_published_profile);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    // get all status [BRICE]
    public function getallstatusBrice() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            if (count($this->_status) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_STATUS_SUCCESSFULLY"), 'data' => $this->_status);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_STATUS_FAILED"), 'data' => $this->_status);
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
            $_receivers = $this->_common_model->getAllAssociatedGroup();

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
            if (count($this->_message) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_MESSAGE_TEMPLATE_SUCCESSFULLY"), 'data' => $this->_message);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_MESSAGE_TEMPLATE_FAILED"), 'data' => $this->_message);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //get all status [DUY]
    public function getallstatus() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $_status = $this->_common_model->getAllStatus();

            if (count($_status) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_STATUS_SUCCESSFULLY"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_STATUS_FAILED"), 'data' => $_status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}