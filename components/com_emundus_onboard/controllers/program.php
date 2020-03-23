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
 * Program Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusonboardControllerprogram extends JControllerLegacy {

    var $model = null;
    function __construct($config = array()){
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('program');
    }

    public function getallprogram() {
        $user = JFactory::getUser();
        $m_prog = $this->model;

        $filter = $_GET['filter'];
        $sort = $_GET['sort'];
        $recherche = $_GET['recherche'];
        $lim = $_GET['lim'];
        $page = $_GET['page'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $programs = $m_prog->getAllPrograms($user->id, $lim, $page, $filter, $sort, $recherche);

            if (count($programs) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $programs);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS'), 'data' => $programs);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getprogramcount() {
        $user = JFactory::getUser();
        $m_prog = $this->model;
        $filterCount = $_GET['filterCount'];
        $rechercheCount = $_GET['rechercheCount'];

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $programs = $m_prog->getProgramCount($user->id, $filterCount, $rechercheCount);

            $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $programs);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getprogrambyid() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->get->get('id');
        $m_prog = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $program = $m_prog->getProgramById($id);
            if (!empty($program)) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $program);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS'), 'data' => $program);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createprogram() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $m_prog = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_prog->addProgram($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMS'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function updateprogram() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('body');
        $code = $jinput->getRaw('code');
        $m_prog = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_prog->updateProgram($code, $data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMS'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
    
    public function deleteprogram() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_prog = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_prog->deleteProgram($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMS'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function unpublishprogram() {

        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_prog = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_prog->unpublishProgram($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMS'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function publishprogram() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getRaw('id');
        $m_prog = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $m_prog->publishProgram($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAM_PUBLISHED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_PUBLISH_PROGRAM'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getprogramcategories() {
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $m_prog = $this->model;

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $program = $m_prog->getProgramCategories();
            if (!empty($program)) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $program);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS'), 'data' => $program);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }



}

