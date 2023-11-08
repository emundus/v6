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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

use Joomla\CMS\Factory;

/**
 * campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerProgramme extends JControllerLegacy
{
	protected $app;

	private $_user;
	private $m_programme;

	function __construct($config = array())
	{
		parent::__construct($config);

		$this->app   = Factory::getApplication();
		$this->_user = $this->app->getIdentity();

		$this->m_programme = $this->getModel('programme');
	}

	function display($cachable = false, $urlparams = false)
	{
		// Set a default view if none exists
		if (!$this->input->get('view')) {
			$default = 'programme';
			$this->input->set('view', $default);
		}
		parent::display();
	}

	public function getprogrammes()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$programmes = $this->m_programme->getProgrammes();

			if (count($programmes) > 0) {
				$tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_RETRIEVED'), 'data' => $programmes);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMMES'), 'data' => $programmes);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function addprogrammes()
	{
		$data = $this->input->get('data', null, 'POST', 'none', 0);

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$result = $this->m_programme->addProgrammes($data);

			if ($result === true) {
				$tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_ADDED'), 'data' => $result);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMMES'), 'data' => $result);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function editprogrammes()
	{
		$data = $this->input->get('data', null, 'POST', 'none', 0);

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$result = $this->m_programme->editProgrammes($data);

			if ($result === true) {
				$tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_EDITED'), 'data' => $result);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_EDIT_PROGRAMMES'), 'data' => $result);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}


	public function favorite()
	{


		$pid = $this->input->post->getInt('programme_id');
		$uid = $this->input->post->getInt('user_id');

		if (empty($uid)) {
			$uid = $this->_user->id;
		}

		$result         = new stdClass();
		$result->status = false;

		if (empty($uid) || empty($pid)) {
			echo json_encode($result);
			exit;
		}

		$result->status = $this->m_programme->favorite($pid, $uid);

		echo json_encode($result);
		exit;
	}


	public function unfavorite()
	{

		$pid = $this->input->post->getInt('programme_id');
		$uid = $this->input->post->getInt('user_id');

		if (empty($uid)) {
			$uid = $this->_user->id;
		}

		$result         = new stdClass();
		$result->status = false;

		if (empty($uid) || empty($pid)) {
			echo json_encode($result);
			exit;
		}

		$result->status = $this->m_programme->unfavorite($pid, $uid);

		echo json_encode($result);
		exit;
	}

	public function getallprogramforfilter()
	{
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$programs = $this->m_programme->getAllPrograms(9999, 0, '', 'DESC', '');

			if (count((array) $programs) > 0) {
				$values = [];
				foreach ($programs['datas'] as $key => $program) {
					$values[] = [
						'label' => $program->label,
						'value' => $program->code
					];
				}

				$response = ['status' => true, 'msg' => JText::_('PROGRAMS_FILTER_RETRIEVED'), 'data' => $values];
			}
			else {
				$response['msg'] = JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS');
			}
		}
		echo json_encode((object) $response);
		exit;
	}

	public function getallprogram()
	{
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {


			$filter    = $this->input->getString('filter');
			$sort      = $this->input->getString('sort');
			$recherche = $this->input->getString('recherche');
			$lim       = $this->input->getInt('lim');
			$page      = $this->input->getInt('page');

			$programs = $this->m_programme->getAllPrograms($lim, $page, $filter, $sort, $recherche);

			if (count((array) $programs) > 0) {
				foreach ($programs['datas'] as $key => $program) {
					$programs['datas'][$key]->label              = ['fr' => JText::_($program->label), 'en' => JText::_($program->label)];
					$programs['datas'][$key]->additional_columns = [
						[
							'key'     => JText::_('COM_EMUNDUS_ONBOARD_PROGCODE'),
							'value'   => $program->code,
							'classes' => 'em-font-size-14 em-neutral-700-color',
							'display' => 'all'
						],
						[
							'key'     => JText::_('COM_EMUNDUS_ONBOARD_CATEGORY'),
							'value'   => $program->programmes,
							'classes' => 'em-font-size-14 em-neutral-700-color',
							'display' => 'all'
						],
						[
							'key'     => JText::_('COM_EMUNDUS_ONBOARD_STATE'),
							'value'   => $program->published ? JText::_('PUBLISHED') : JText::_('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH'),
							'classes' => $program->published ? 'em-p-5-12 em-bg-main-100 em-text-neutral-900 em-font-size-14 em-border-radius' : 'em-p-5-12 em-bg-neutral-200 em-text-neutral-900 em-font-size-14 em-border-radius',
							'display' => 'table'
						],
						[
							'key'     => JText::_('COM_EMUNDUS_ONBOARD_PROGRAM_APPLY_ONLINE'),
							'value'   => $program->apply_online ? JText::_('JYES') : JText::_('JNO'),
							'classes' => '',
							'display' => 'table'
						],
						[
							'key'     => JText::_('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED_TITLE'),
							'value'   => $program->nb_campaigns,
							'classes' => '',
							'display' => 'table'
						],
						[
							'type'    => 'tags',
							'key'     => JText::_('COM_EMUNDUS_ONBOARD_PROGRAM_TAGS'),
							'values'  => [
								[
									'key'     => JText::_('COM_EMUNDUS_ONBOARD_STATE'),
									'value'   => $program->published ? JText::_('PUBLISHED') : JText::_('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH'),
									'classes' => $program->published ? 'em-p-5-12 em-font-weight-600 em-bg-main-100 em-text-neutral-900 em-font-size-14 em-border-radius' : 'em-p-5-12 em-font-weight-600 em-bg-neutral-200 em-text-neutral-900 em-font-size-14 em-border-radius',
								],
								[
									'key'     => JText::_('COM_EMUNDUS_ONBOARD_PROGRAM_APPLY_ONLINE'),
									'value'   => $program->apply_online ? JText::_('COM_EMUNDUS_ONBOARD_PROGRAM_APPLY_ONLINE') : JText::_(''),
									'classes' => $program->apply_online ? 'em-p-5-12 em-font-weight-600 em-bg-neutral-200 em-text-neutral-900 em-font-size-14 em-border-radius' : 'hidden',
								],
								[
									'key'     => '',
									'value'   => $program->nb_campaigns > 1 ? $program->nb_campaigns . ' ' . JText::_('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED') : $program->nb_campaigns . ' ' . JText::_('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED_SINGLE'),
									'classes' => 'em-p-5-12 em-font-weight-600 em-bg-neutral-200 em-text-neutral-900 em-font-size-14 em-border-radius',
								]
							],
							'display' => 'blocs',
							'classes' => 'em-mt-8 em-mb-8'
						]
					];
				}

				$response = ['status' => true, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $programs];
			}
			else {
				$response['msg'] = JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS');
			}
		}
		echo json_encode((object) $response);
		exit;
	}

	public function getallsessions()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$sessions = $this->m_programme->getAllSessions();

			if (count((array) $sessions) > 0) {
				$tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $sessions);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS'), 'data' => $sessions);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getprogramcount()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$filterCount    = $this->input->getString('filterCount');
			$rechercheCount = $this->input->getString('rechercheCount');

			$programs = $this->m_programme->getProgramCount($filterCount, $rechercheCount);

			$tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $programs);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getprogrambyid()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$id = $this->input->getInt('id');

			$program = $this->m_programme->getProgramById($id);

			if (!empty($program)) {
				$tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $program);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS'), 'data' => $program);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function createprogram()
	{
		if (!EmundusHelperAccess::isCoordinator($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$data = $this->input->getRaw('body');

			$result = $this->m_programme->addProgram($data);

			if (is_array($result)) {
				$tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMS'), 'data' => $result);
			}
		}

		echo json_encode((object) $tab);
		exit;
	}


	public function updateprogram()
	{
		$tab = array('status' => 0, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {


			$data = $this->input->getRaw('body');
			$id   = $this->input->getString('id');

			if (!empty($id) && !empty($data)) {
				$result = $this->m_programme->updateProgram($id, $data);

				if ($result) {
					$tab = array('status' => 1, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result);
				}
				else {
					$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMS'), 'data' => $result);
				}
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('MISSING_PARAMS'));
			}
		}

		echo json_encode((object) $tab);
		exit;
	}

	public function deleteprogram()
	{
		$response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED')];

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {

			$data   = $this->input->getInt('id');
			$result = $this->m_programme->deleteProgram($data);

			if ($result) {
				$response = ['status' => true, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result];
			}
			else {
				$response['msg'] = JText::_('ERROR_CANNOT_ADD_PROGRAMS');
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function unpublishprogram()
	{
		$response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED')];

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {

			$data   = $this->input->getInt('id');
			$result = $this->m_programme->unpublishProgram($data);

			if ($result) {
				$response = array('status' => 1, 'msg' => JText::_('PROGRAMS_ADDED'), 'data' => $result);
			}
			else {
				$response = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMS'), 'data' => $result);
			}
		}
		echo json_encode((object) $response);
		exit;
	}

	public function publishprogram()
	{
		$response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED')];

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {

			$data = $this->input->getInt('id');

			$result = $this->m_programme->publishProgram($data);

			if ($result) {
				$response = array('status' => 1, 'msg' => JText::_('PROGRAM_PUBLISHED'), 'data' => $result);
			}
			else {
				$response = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_PUBLISH_PROGRAM'), 'data' => $result);
			}
		}
		echo json_encode((object) $response);
		exit;
	}

	public function getprogramcategories()
	{
		$response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED')];

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$categories = $this->m_programme->getProgramCategories();

			if (!empty($categories)) {
				$response = array('status' => true, 'msg' => JText::_('PROGRAMS_RETRIEVED'), 'data' => $categories);
			}
			else {
				$response = array('status' => false, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMS'));
			}
		}
		echo json_encode((object) $response);
		exit;
	}

	public function getuserstoaffect()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group = $this->input->getInt('group');

			$this->_users = $this->m_programme->getuserstoaffect($group);

			if (!empty($this->_users)) {
				$tab = array('status' => 1, 'msg' => JText::_('MANAGERS_RETRIEVED'), 'data' => $this->_users);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $this->_users);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getuserstoaffectbyterm()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group = $this->input->getInt('group');
			$term  = $this->input->getString('term');

			$this->_users = $this->m_programme->getuserstoaffectbyterm($group, $term);

			if (!empty($this->_users)) {
				$tab = array('status' => 1, 'msg' => JText::_('MANAGERS_RETRIEVED'), 'data' => $this->_users);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $this->_users);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getmanagers()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group = $this->input->getInt('group');

			$managers = $this->m_programme->getManagers($group);

			if (!empty($managers)) {
				$tab = array('status' => 1, 'msg' => JText::_('MANAGERS_RETRIEVED'), 'data' => $managers);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_MANAGERS'), 'data' => $managers);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getevaluators()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group = $this->input->getInt('group');

			$evaluators = $this->m_programme->getEvaluators($group);

			if (!empty($evaluators)) {
				$tab = array('status' => 1, 'msg' => JText::_('EVALUATORS_RETRIEVED'), 'data' => $evaluators);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EVALUATORS'), 'data' => $evaluators);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function affectusertogroup()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group      = $this->input->getInt('group');
			$prog_group = $this->input->getInt('prog_group');
			$email      = $this->input->getString('email');

			$changeresponse = $this->m_programme->affectusertogroups($group, $email, $prog_group);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function affectuserstogroup()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group      = $this->input->getInt('group');
			$prog_group = $this->input->getInt('prog_group');
			$managers   = $this->input->getRaw('users');

			$changeresponse = $this->m_programme->affectuserstogroup($group, $managers, $prog_group);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function removefromgroup()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$user_id    = $this->input->getInt('id');
			$group      = $this->input->getInt('group');
			$prog_group = $this->input->getInt('prog_group');

			$changeresponse = $this->m_programme->removefromgroup($user_id, $group, $prog_group);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function getusers()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$filters = $this->input->getRaw('filters');
			$page    = $this->input->getRaw('page');

			$user_ids = $this->m_programme->getusers($filters, $page['page']);

			if (!empty($this->_users)) {
				$tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $user_ids);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $user_ids);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getuserswithoutapplicants()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$user_ids = $this->m_programme->getuserswithoutapplicants();

			if (!empty($user_ids)) {
				$tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $user_ids);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $user_ids);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function searchuserbytermwithoutapplicants()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$term = $this->input->getString('term');

			$user_ids = $this->m_programme->searchuserbytermwithoutapplicants($term);
			if (!empty($this->_users)) {
				$tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $user_ids);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $user_ids);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function updatevisibility()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$visibility = $this->input->getBool('visibility');
			$cid        = $this->input->getInt('cid');
			$gid        = $this->input->getInt('gid');

			$changeresponse = $this->m_programme->updateVisibility($cid, $gid, $visibility);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function getevaluationgrid()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$pid = $this->input->getInt('pid');

			$grid = $this->m_programme->getEvaluationGrid($pid);

			if ($grid) {
				$tab = array('status' => 1, 'msg' => JText::_('GRID_RETRIEVED'), 'data' => $grid);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_GRID'), 'data' => $grid);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getgridsmodel()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$grids = $this->m_programme->getGridsModel();

			if ($grids) {
				$tab = array('status' => 1, 'msg' => JText::_('GRID_RETRIEVED'), 'data' => $grids);
			}
			else {
				$tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_GRID'), 'data' => $grids);
			}
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function creategrid()
	{


		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$label    = $this->input->getString('label');
			$intro    = $this->input->getString('intro');
			$modelid  = $this->input->getInt('modelid');
			$template = $this->input->getBool('template');
			$pid      = $this->input->getInt('pid');

			if ($modelid != -1) {
				$changeresponse = $this->m_programme->createGridFromModel($label, $intro, $modelid, $pid);
			}
			else {
				$changeresponse = $this->m_programme->createGrid($label, $intro, $pid, $template);
			}
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function deletegrid()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$grid = $this->input->getInt('grid');
			$pid  = $this->input->getInt('pid');

			$changeresponse = $this->m_programme->deleteGrid($grid, $pid);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function affectgrouptoprogram()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group = $this->input->getInt('group');
			$pid   = $this->input->getInt('pid');

			$changeresponse = $this->m_programme->affectGroupToProgram($group, $pid);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function deletegroupfromprogram()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group = $this->input->getInt('group');
			$pid   = $this->input->getInt('pid');

			$changeresponse = $this->m_programme->deleteGroupFromProgram($group, $pid);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function getgroupsbyprograms()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$programs = $this->input->getRaw('programs');

			$groups = $this->m_programme->getGroupsByPrograms($programs);

			$tab = array('status' => 1, 'msg' => JText::_('GRID_RETRIEVED'), 'groups' => $groups);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getcampaignsbyprogram()
	{
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$program = $this->input->getInt('pid');

			$campaigns = $this->m_programme->getCampaignsByProgram($program);

			$tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'campaigns' => $campaigns);
		}
		echo json_encode((object) $tab);
		exit;
	}

}
