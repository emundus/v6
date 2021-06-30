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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Program Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusonboardControlleremail extends JControllerLegacy {

    var $model = null;
    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('email');
    }

    /**
     * Get the number of email with filters
     */
    public function getemailcount() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_emails = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $filterCount = $jinput->getString('filterCount');
	        $rechercheCount = $jinput->getString('rechercheCount');

            $emails = $m_emails->getEmailCount($filterCount, $rechercheCount);

            if ($emails > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_RETRIEVED'), 'data' => $emails);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EMAIL'), 'data' => $emails);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get emails filtered
     */
    public function getallemail() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_emails = $this->model;

	        $jinput = JFactory::getApplication()->input;
	        $filter = $jinput->getString('filter');
	        $sort = $jinput->getString('sort');
	        $recherche = $jinput->getString('recherche');
	        $lim = $jinput->getInt('lim');
	        $page = $jinput->getInt('page');

            $emails = $m_emails->getAllEmails($lim, $page, $filter, $sort, $recherche);

            if (count($emails) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_RETRIEVED'), 'data' => $emails);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EMAIL'), 'data' => $emails);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function deleteemail() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_email = $this->model;

            $emails = $m_email->deleteEmail($data);

            if ($emails) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_DELETED'), 'data' => $emails);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_EMAIL'), 'data' => $emails);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function unpublishemail() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_email = $this->model;

            $emails = $m_email->unpublishEmail($data);

            if ($emails) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_UNPUBLISHED'), 'data' => $emails);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UNPUBLISH_EMAIL'), 'data' => $emails);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function publishemail() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_email = $this->model;

            $emails = $m_email->publishEmail($data);

            if ($emails) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_PUBLISHED'), 'data' => $emails);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_PUBLISH_EMAIL'), 'data' => $emails);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function duplicateemail() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_email = $this->model;

            $email = $m_email->duplicateEmail($data);

            if ($email) {
                $this->getallemail();
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DUPLICATE_EMAIL'), 'data' => $email);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createemail() {

        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getRaw('body');
	        $m_email = $this->model;

            $tags = $jinput->getRaw('tags');
            $documents = $jinput->getRaw('documents');

            $result = $m_email->createEmail($data, $tags, $documents);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_EMAIL'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function updateemail() {

        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getRaw('body');
	        $code = $jinput->getString('code');
	        $m_email = $this->model;

            $tags = $jinput->getRaw('tags');
            $documents = $jinput->getRaw('documents');

            $result = $m_email->updateEmail($code, $data, $tags, $documents);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('EMAIL'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getemailbyid() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $id = $jinput->getInt('id');
	        $m_email = $this->model;

            $email = $m_email->getEmailById($id);
            if (!empty($email)) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_RETRIEVED'), 'data' => $email);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EMAIL'), 'data' => $email);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getemailcategories() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_email = $this->model;

            $email = $m_email->getEmailCategories();
            if (!empty($email)) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_RETRIEVED'), 'data' => $email);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EMAIL'), 'data' => $email);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getemailtypes() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_email = $this->model;
            $email = $m_email->getEmailTypes();

            if (!empty($email)) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_RETRIEVED'), 'data' => $email);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EMAIL'), 'data' => $email);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getstatus() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_email = $this->model;
            $status = $m_email->getStatus();

            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('STATUS_RETRIEVED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_STATUS'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function gettriggersbyprogram() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $pid = $jinput->getInt('pid');
	        $m_email = $this->model;

            $triggers = $m_email->getTriggersByProgramId($pid);
            if (!empty($triggers)) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGERS_RETRIEVED'), 'data' => $triggers);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_TRIGGERS'), 'data' => $triggers);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function gettriggerbyid() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $tid = $jinput->getInt('tid');
	        $m_email = $this->model;

            $trigger = $m_email->getTriggerById($tid);
            if (!empty($trigger)) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGER_RETRIEVED'), 'data' => $trigger);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_TRIGGER'), 'data' => $trigger);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createtrigger() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_email = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $trigger = $jinput->getRaw('trigger');
	        $users = $jinput->getRaw('users');

            $status = $m_email->createTrigger($trigger, $users, $user);
            if ($status) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGER_CREATED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_CREATE_TRIGGER'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatetrigger() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_email = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $tid = $jinput->getInt('tid');
	        $trigger = $jinput->getRaw('trigger');
	        $users = $jinput->getRaw('users');

            $status = $m_email->updateTrigger($tid, $trigger, $users);
            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGER_CREATED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_CREATE_TRIGGER'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function removetrigger() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_email = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $tid = $jinput->getInt('tid');

            $status = $m_email->removeTrigger($tid);
            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGER_CREATED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_CREATE_TRIGGER'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}

