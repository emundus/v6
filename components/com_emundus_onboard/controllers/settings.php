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

/**
 * Settings Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusonboardControllersettings extends JControllerLegacy {

    var $model = null;
    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('settings');
    }

    public function getstatus() {
        $user = JFactory::getUser();
        $m_settings = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $status = $m_settings->getStatus();
            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('STATUS_RETRIEVED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_STATUS'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function gettags() {
        $user = JFactory::getUser();
        $m_settings = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $status = $m_settings->getTags();
            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('STATUS_RETRIEVED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_STATUS'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createtag() {
        $user = JFactory::getUser();
        $m_settings = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_settings->createTag();
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function deletetag() {
        $user = JFactory::getUser();
        $m_settings = $this->model;
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->getRaw('id');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_settings->deleteTag($id);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatestatus() {
        $user = JFactory::getUser();
        $m_settings = $this->model;
        $jinput = JFactory::getApplication()->input;
        $status = $jinput->getRaw('status');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_settings->updateStatus($status);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatetags() {
        $user = JFactory::getUser();
        $m_settings = $this->model;
        $jinput = JFactory::getApplication()->input;
        $tags = $jinput->getRaw('tags');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_settings->updateTags($tags);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function gethomepagearticle() {
        $user = JFactory::getUser();
        $m_settings = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $content = $m_settings->getHomepageArticle();
            if (!empty($content)) {
                $tab = array('status' => 1, 'msg' => JText::_('STATUS_RETRIEVED'), 'data' => $content);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_STATUS'), 'data' => $content);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatehomepage() {
        $user = JFactory::getUser();
        $m_settings = $this->model;
        $jinput = JFactory::getApplication()->input;
        $content = $jinput->getRaw('content');

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $m_settings->updateHomepage($content);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }
}

