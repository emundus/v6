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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;

	        $jinput = JFactory::getApplication()->input;
	        $filter = $jinput->getString('filter');
	        $sort = $jinput->getString('sort');
	        $recherche = $jinput->getString('recherche');
	        $lim = $jinput->getInt('lim');
	        $page = $jinput->getInt('page');
            $programs = $m_prog->getAllPrograms($lim, $page, $filter, $sort, $recherche);

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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

        	$m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $filterCount = $jinput->getString('filterCount');
	        $rechercheCount = $jinput->getString('rechercheCount');

            $programs = $m_prog->getProgramCount($filterCount, $rechercheCount);

            $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $programs);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getprogrambyid() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $id = $jinput->getInt('id');
	        $m_prog = $this->model;
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

        if (!EmundusonboardHelperAccess::isCoordinator($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getRaw('body');
	        $m_prog = $this->model;
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


        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getRaw('body');
	        $code = $jinput->getString('code');
	        $m_prog = $this->model;
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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_prog = $this->model;
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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_prog = $this->model;
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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_prog = $this->model;
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

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $m_prog = $this->model;
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

    public function getuserstoaffect() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $m_prog = $this->model;
	        $group = $jinput->getInt('group');
            $users = $m_prog->getuserstoaffect($group);
            if (!empty($users)) {
                $tab = array('status' => 1, 'msg' => JText::_('MANAGERS_RETRIEVED'), 'data' => $users);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $users);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getuserstoaffectbyterm() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

        	$jinput = JFactory::getApplication()->input;
	        $m_prog = $this->model;
	        $group = $jinput->getInt('group');
	        $term = $jinput->getString('term');
            $users = $m_prog->getuserstoaffectbyterm($group,$term);

            if (!empty($users)) {
                $tab = array('status' => 1, 'msg' => JText::_('MANAGERS_RETRIEVED'), 'data' => $users);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $users);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getmanagers() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $m_prog = $this->model;
	        $group = $jinput->getInt('group');
            $managers = $m_prog->getManagers($group);

            if (!empty($managers)) {
                $tab = array('status' => 1, 'msg' => JText::_('MANAGERS_RETRIEVED'), 'data' => $managers);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_MANAGERS'), 'data' => $managers);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getevaluators() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $m_prog = $this->model;
	        $group = $jinput->getInt('group');
            $evaluators = $m_prog->getEvaluators($group);

            if (!empty($evaluators)) {
                $tab = array('status' => 1, 'msg' => JText::_('EVALUATORS_RETRIEVED'), 'data' => $evaluators);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EVALUATORS'), 'data' => $evaluators);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function affectusertogroup() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $group = $jinput->getInt('group');
	        $profile = $jinput->getInt('profile');
	        $email = $jinput->getString('email');

            if ($profile == 5) {
                $changeresponse = $m_prog->affectusertomanagergroups($group, $email);
            } else {
                $changeresponse = $m_prog->affectusertoevaluatorgroups($group, $email);
            }
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function affectuserstomanagergroup() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $group = $jinput->getInt('group');
	        $managers = $jinput->getRaw('users');

            $changeresponse = $m_prog->affectuserstomanagergroup($group, $managers);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function affectuserstoevaluatorgroup() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $group = $jinput->getInt('group');
	        $evaluators = $jinput->getRaw('users');

            $changeresponse = $m_prog->affectuserstoevaluatorgroup($group, $evaluators);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function removefrommanagergroup() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $userid = $jinput->getInt('id');
	        $group = $jinput->getInt('group');

            $changeresponse = $m_prog->removefrommanagergroup($userid, $group);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function removefromevaluatorgroup() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $userid = $jinput->getInt('id');
	        $group = $jinput->getInt('group');

            $changeresponse = $m_prog->removefromevaluatorgroup($userid, $group);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getusers() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

        	$m_prog = $this->model;
            $jinput = JFactory::getApplication()->input;
            $filters = $jinput->getRaw('filters');
            $users = $m_prog->getusers($filters);

            if (!empty($users)) {
                $tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $users);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $users);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getuserswithoutapplicants() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $m_prog = $this->model;
            $users = $m_prog->getuserswithoutapplicants();

            if (!empty($users)) {
                $tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $users);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $users);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function searchuserbytermwithoutapplicants() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $term = $jinput->getString('term');
	        $m_prog = $this->model;

            $users = $m_prog->searchuserbytermwithoutapplicants($term);
            if (!empty($users)) {
                $tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $users);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $users);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatevisibility() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $visibility = $jinput->getBool('visibility');
	        $cid = $jinput->getInt('cid');
	        $gid = $jinput->getInt('gid');

            $changeresponse = $m_prog->updateVisibility($cid,$gid,$visibility);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getevaluationgrid() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $m_prog = $this->model;
	        $pid = $jinput->getInt('pid');

            $grid = $m_prog->getEvaluationGrid($pid);
            if ($grid) {
                $tab = array('status' => 1, 'msg' => JText::_('GRID_RETRIEVED'), 'data' => $grid);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_GRID'), 'data' => $grid);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getgridsmodel() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
            $grids = $m_prog->getGridsModel();

            if ($grids) {
                $tab = array('status' => 1, 'msg' => JText::_('GRID_RETRIEVED'), 'data' => $grids);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_GRID'), 'data' => $grids);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function creategrid() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $label = $jinput->getString('label');
	        $intro = $jinput->getString('intro');
	        $modelid = $jinput->getInt('modelid');
	        $template = $jinput->getBool('template');
	        $pid = $jinput->getInt('pid');

            if ($modelid != -1) {
                $changeresponse = $m_prog->createGridFromModel($label, $intro, $modelid, $pid);
            } else {
                $changeresponse = $m_prog->createGrid($label, $intro, $pid, $template);
            }
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function affectgrouptoprogram() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $group = $jinput->getInt('group');
	        $pid = $jinput->getInt('pid');

            $changeresponse = $m_prog->affectGroupToProgram($group, $pid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function deletegroupfromprogram() {
        $user = JFactory::getUser();


        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_prog = $this->model;
	        $jinput = JFactory::getApplication()->input;
	        $group = $jinput->getInt('group');
	        $pid = $jinput->getInt('pid');

            $changeresponse = $m_prog->deleteGroupFromProgram($group, $pid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }
}

