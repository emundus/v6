<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      Benjamin Rivalland
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerProgramme extends JControllerLegacy {
    var $_user = null;
    var $_db = null;
    var $m_programme = null;

    function __construct($config = array()){
        parent::__construct($config);

        $this->_user = JFactory::getUser();
        $this->_db = JFactory::getDBO();
        $this->m_programme = $this->getModel('programme');
    }

    function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) ) {
            $default = 'programme';
            JRequest::setVar('view', $default );
        }
        parent::display();
    }

    public function getprogrammes(){
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $programmes = $this->m_programme->getProgrammes();

            if (count($programmes) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_RETRIEVED'), 'data' => $programmes);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMMES'), 'data' => $programmes);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function addprogrammes(){
        $data = JRequest::getVar('data', null, 'POST', 'none',0);

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $this->m_programme->addProgrammes($data);

            if ($result === true) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMMES'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function editprogrammes() {
        $data = JRequest::getVar('data', null, 'POST', 'none',0);

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $result = $this->m_programme->editProgrammes($data);

            if ($result === true) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_EDITED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_EDIT_PROGRAMMES'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function favorite() {
    	$jinput = JFactory::getApplication()->input;

    	$pid = $jinput->post->getInt('programme_id');
    	$uid = $jinput->post->getInt('user_id');

    	if (empty($uid)) {
            $uid = $this->_user->id;
        }

    	$result = new stdClass();
	    $result->status = false;

    	if (empty($uid) || empty($pid)) {
    		echo json_encode($result);
    		exit;
	    }

	    $result->status = $this->m_programme->favorite($pid, $uid);

	    echo json_encode($result);
	    exit;
    }


	public function unfavorite() {
		$jinput = JFactory::getApplication()->input;
		$pid = $jinput->post->getInt('programme_id');
		$uid = $jinput->post->getInt('user_id');

		if (empty($uid)) {
            $uid = $this->_user->id;
        }

		$result = new stdClass();
		$result->status = false;

		if (empty($uid) || empty($pid)) {
			echo json_encode($result);
			exit;
		}

		$result->status = $this->m_programme->unfavorite($pid, $uid);

		echo json_encode($result);
		exit;
	}

    public function getallprogram() {
	    $response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

	    if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $jinput = JFactory::getApplication()->input;

            $filter = $jinput->getString('filter');
            $sort = $jinput->getString('sort');
            $recherche = $jinput->getString('recherche');
            $lim = $jinput->getInt('lim');
            $page = $jinput->getInt('page');

            $programs = $this->m_programme->getAllPrograms($lim, $page, $filter, $sort, $recherche);

            if (count((array)$programs) > 0) {
	            $response = ['status' => true, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $programs];
            } else {
	            $response['msg'] = JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS');
            }
        }
        echo json_encode((object)$response);
        exit;
    }

    public function getallsessions() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $sessions = $this->m_programme->getAllSessions();

            if (count((array)$sessions) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $sessions);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS'), 'data' => $sessions);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getprogramcount() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $filterCount = $jinput->getString('filterCount');
            $rechercheCount = $jinput->getString('rechercheCount');

            $programs = $this->m_programme->getProgramCount($filterCount, $rechercheCount);

            $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $programs);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getprogrambyid() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $id = $jinput->getInt('id');

            $program = $this->m_programme->getProgramById($id);

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
        if (!EmundusHelperAccess::isCoordinator($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getRaw('body');

            $result = $this->m_programme->addProgram($data);

            if (is_array($result)) {
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMS'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function updateprogram() {
        $tab = array('status' => 0, 'msg' => JText::_('ACCESS_DENIED'));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getRaw('body');
            $id = $jinput->getString('id');

            if (!empty($id) && !empty($data)) {
                $result = $this->m_programme->updateProgram($id, $data);

                if ($result) {
                    $tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result);
                } else {
                    $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMS'), 'data' => $result);
                }
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('MISSING_PARAMS'));
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function deleteprogram() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $result = $this->m_programme->deleteProgram($data);

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
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $result = $this->m_programme->unpublishProgram($data);

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
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $result = $this->m_programme->publishProgram($data);

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
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $program = $this->m_programme->getProgramCategories();

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
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $group = $jinput->getInt('group');

            $this->_users = $this->m_programme->getuserstoaffect($group);

            if (!empty($this->_users)) {
                $tab = array('status' => 1, 'msg' => JText::_('MANAGERS_RETRIEVED'), 'data' => $this->_users);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $this->_users);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getuserstoaffectbyterm() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $group = $jinput->getInt('group');
            $term = $jinput->getString('term');

            $this->_users = $this->m_programme->getuserstoaffectbyterm($group,$term);

            if (!empty($this->_users)) {
                $tab = array('status' => 1, 'msg' => JText::_('MANAGERS_RETRIEVED'), 'data' => $this->_users);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $this->_users);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getmanagers() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $group = $jinput->getInt('group');

            $managers = $this->m_programme->getManagers($group);

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
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;

            $group = $jinput->getInt('group');

            $evaluators = $this->m_programme->getEvaluators($group);

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
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $group = $jinput->getInt('group');
            $prog_group = $jinput->getInt('prog_group');
            $email = $jinput->getString('email');

            $changeresponse = $this->m_programme->affectusertogroups($group, $email, $prog_group);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function affectuserstogroup() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $group = $jinput->getInt('group');
            $prog_group = $jinput->getInt('prog_group');
            $managers = $jinput->getRaw('users');

            $changeresponse = $this->m_programme->affectuserstogroup($group, $managers, $prog_group);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function removefromgroup() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $user_id = $jinput->getInt('id');
            $group = $jinput->getInt('group');
            $prog_group = $jinput->getInt('prog_group');

            $changeresponse = $this->m_programme->removefromgroup($user_id, $group, $prog_group);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getusers() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $filters = $jinput->getRaw('filters');
            $page = $jinput->getRaw('page');

            $user_ids = $this->m_programme->getusers($filters,$page['page']);

            if (!empty($this->_users)) {
                $tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $user_ids);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $user_ids);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getuserswithoutapplicants() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $user_ids = $this->m_programme->getuserswithoutapplicants();

            if (!empty($user_ids)) {
                $tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $user_ids);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $user_ids);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function searchuserbytermwithoutapplicants() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $term = $jinput->getString('term');

            $user_ids = $this->m_programme->searchuserbytermwithoutapplicants($term);
            if (!empty($this->_users)) {
                $tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $user_ids);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $user_ids);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatevisibility() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $visibility = $jinput->getBool('visibility');
            $cid = $jinput->getInt('cid');
            $gid = $jinput->getInt('gid');

            $changeresponse = $this->m_programme->updateVisibility($cid,$gid,$visibility);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getevaluationgrid() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $pid = $jinput->getInt('pid');

            $grid = $this->m_programme->getEvaluationGrid($pid);

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
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $grids = $this->m_programme->getGridsModel();

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


        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $label = $jinput->getString('label');
            $intro = $jinput->getString('intro');
            $modelid = $jinput->getInt('modelid');
            $template = $jinput->getBool('template');
            $pid = $jinput->getInt('pid');

            if ($modelid != -1) {
                $changeresponse = $this->m_programme->createGridFromModel($label, $intro, $modelid, $pid);
            } else {
                $changeresponse = $this->m_programme->createGrid($label, $intro, $pid, $template);
            }
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function deletegrid() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $grid = $jinput->getInt('grid');
            $pid = $jinput->getInt('pid');

            $changeresponse = $this->m_programme->deleteGrid($grid,$pid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function affectgrouptoprogram() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $group = $jinput->getInt('group');
            $pid = $jinput->getInt('pid');

            $changeresponse = $this->m_programme->affectGroupToProgram($group, $pid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function deletegroupfromprogram() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $group = $jinput->getInt('group');
            $pid = $jinput->getInt('pid');

            $changeresponse = $this->m_programme->deleteGroupFromProgram($group, $pid);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getgroupsbyprograms() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $programs = $jinput->getRaw('programs');

            $groups = $this->m_programme->getGroupsByPrograms($programs);

            $tab = array('status' => 1, 'msg' => JText::_('GRID_RETRIEVED'), 'groups' => $groups);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getcampaignsbyprogram() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $program = $jinput->getInt('pid');

            $campaigns = $this->m_programme->getCampaignsByProgram($program);

            $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'campaigns' => $campaigns);
        }
        echo json_encode((object)$tab);
        exit;
    }

}
