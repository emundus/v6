<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');

class EmundusworkflowControllercommon extends JControllerLegacy {
    var $_common_model = null;

    var $_published_form_model = null;
    var $_published_form = null;

    var $_status_model = null;
    var $_status = null;

    var $_emails_model = null;
    var $_emails = null;

    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);

        // call to "common model
        $this->_common_model = $this->getModel('common');

        //call to EmundusonboardModel
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_onboard/models');

        $this->_published_form_model = JModelLegacy::getInstance('form', 'EmundusonboardModel');
        $this->_status_model = JModelLegacy::getInstance('settings', 'EmundusonboardModel');
        $this->_emails_model = JModelLegacy::getInstance('email', 'EmundusonboardModel');

        $this->_published_form = $this->_published_form_model->getAllFormsPublished();
        $this->_status = $this->_status_model->getStatus();
        $this->_emails = $this->_emails_model->getAllEmails(null,null,null,null,null);
    }

    // get all published forms
    public function getallpublishedforms() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            if ($this->_published_form) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_PUBLISHED_FORM"), 'data' => $this->_published_form);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("NOTHING_PUBLISH_FORM"), 'data' => $this->_published_form);
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
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_STATUS"), 'data' => $this->_status);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_STATUS"), 'data' => $this->_status);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    // get all associated groups
    public function getalldestinations() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            $_gid = $this->_common_model;

            $groups = $_gid->getAllAssociatedGroup();

            if (count($groups) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_DESTINATIONS"), 'data' => $groups);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_DESTINATIONS"), 'data' => $groups);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    // get all messages
    public function getallmessages() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }

        else {
            if (count($this->_emails) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_EMAIL_MODELS"), 'data' => $this->_emails);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_EMAIL_MODELS"), 'data' => $this->_emails);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    //get all status [DUY]
    public function getallstatus() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $_wit = $this->_common_model;

            //do stuff
            $_status = $_wit->getAllStatus();

            if (count($_status) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("OK_GET_STATUS"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("KO_NO_STATUS"), 'data' => $_status);
            }

        }
        echo json_encode((object)$tab);
        exit;
    }
}