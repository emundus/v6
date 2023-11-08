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

use Joomla\CMS\Factory;

/**
 * FormBuilder Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerFormbuilder extends JControllerLegacy
{

	protected $app;
	private $m_formbuilder;

	public function __construct($config = array())
	{
		parent::__construct($config);

		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'formbuilder.php');

		$this->app           = Factory::getApplication();
		$this->m_formbuilder = $this->getModel('Formbuilder');
	}

	public function updateOrder()
	{
		$update = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
		$user   = JFactory::getUser();

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {


			$elements = $this->input->getString('elements');
			$elements = json_decode($elements, true);
			$group_id = $this->input->getInt('group_id');
			$moved_el = $this->input->getString('moved_el');
			$moved_el = json_decode($moved_el, true);

			if (empty($moved_el)) {
				$update['msg'] = JText::_('INVALID_PARAMETERS');
			}
			else {
				$update['status'] = $this->m_formbuilder->updateOrder($elements, $group_id, $user->id, $moved_el);
				$update['msg']    = $update['status'] ? JText::_('SUCCESS') : JText::_('FAILURE');
			}
		}

		echo json_encode((object) $update);
		exit;
	}

	public function updateelementorder()
	{
		$return = array(
			'status' => 0,
			'msg'    => JText::_("INVALID_PARAMETERS")
		);

		$user = JFactory::getUser();
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$return['msg'] = JText::_("ACCESS_DENIED");
		}
		else {

			$group_id   = $this->input->getInt('group_id');
			$element_id = $this->input->getInt('element_id');
			$new_index  = $this->input->getInt('new_index', 0);

			if (empty($group_id) || empty($element_id)) {
				$return['msg'] = JText::_("INVALID_PARAMETERS " . $group_id . " " . $element_id . " " . $new_index);
			}
			else {
				$return = $this->m_formbuilder->updateElementOrder($group_id, $element_id, $new_index);
			}
		}

		echo json_encode((object) $return);
		exit;
	}

	public function updategroupparams()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$update = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group_id = $this->input->getInt('group_id');
			$params   = $this->input->getString('params');
			$params   = json_decode($params, true);
			$lang     = $this->input->getString('lang', '');

			if (!empty($params)) {
				$update = array(
					'status' => 1,
					'data'   => $this->m_formbuilder->updateGroupParams($group_id, $params, $lang)
				);
			}
			else {
				$update = array(
					'status' => 0,
					'msg'    => JText::_('MISSING_PARAMS')
				);
				JLog::add("Nothing to update in group params", JLog::WARNING, 'com_emundus');
			}
		}
		echo json_encode($update);
		exit;
	}

	public function changerequire()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$element = $this->input->getRaw('element');

			$changeresponse = $this->m_formbuilder->ChangeRequire($element, $user->id);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}


	public function publishunpublishelement()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$update = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$element = $this->input->getInt('element');

			$update = $this->m_formbuilder->publishUnpublishElement($element);
		}
		echo json_encode((object) $update);
		exit;
	}

	public function hiddenunhiddenelement()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$update = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$element = $this->input->getInt('element');

			$update = $this->m_formbuilder->hiddenUnhiddenElement($element);
		}
		echo json_encode((object) $update);
		exit;
	}


	public function updateparams()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$element = $this->input->getRaw('element');
			$element = json_decode($element, true);

			$changeresponse = $this->m_formbuilder->UpdateParams($element, $user->id);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function duplicateelement()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$eid       = $this->input->getInt('id');
			$group     = $this->input->getInt('group');
			$old_group = $this->input->getInt('old_group');
			$form_id   = $this->input->getInt('form_id');

			$changeresponse = $this->m_formbuilder->duplicateElement($eid, $group, $old_group, $form_id);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	/**
	 * Update translations of an element
	 *
	 * @throws Exception
	 */
	public function formsTrad()
	{
		$response = ['status' => false, 'msg' => JText::_('ACCESS_DENIED')];
		$user     = JFactory::getUser();

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {


			$element     = $this->input->getInt('element', null);
			$group       = $this->input->getInt('group', null);
			$page        = $this->input->getInt('page', null);
			$labelTofind = $this->input->getString('labelTofind');
			$newLabel    = $this->input->getRaw('NewSubLabel');

			if (!empty($labelTofind) && !empty($newLabel)) {
				$results = $this->m_formbuilder->formsTrad($labelTofind, $newLabel, $element, $group, $page);

				if (!empty($results)) {
					$response = ['status' => true, 'msg' => 'Traductions effectués avec succès', 'data' => $results];
				}
				else {
					$response['msg'] = JText::_('NO_TRANSLATION_FOUND');
				}
			}
			else {
				$response['msg'] = JText::_('MISSING_PARAMS');
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function updateelementlabelwithouttranslation()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$eid   = $this->input->getInt('eid');
			$label = $this->input->getString('label');

			$changeresponse = $this->m_formbuilder->updateElementWithoutTranslation($eid, $label);
		}

		echo json_encode((object) $changeresponse);
		exit;
	}

	public function updategrouplabelwithouttranslation()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$gid   = $this->input->getInt('gid');
			$label = $this->input->getString('label');

			$changeresponse = $this->m_formbuilder->updateGroupWithoutTranslation($gid, $label);
		}

		echo json_encode((object) $changeresponse);
		exit;
	}

	public function updatepagelabelwithouttranslation()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$pid   = $this->input->getInt('pid');
			$label = $this->input->getString('label');

			$changeresponse = $this->m_formbuilder->updatePageWithoutTranslation($pid, $label);
		}

		echo json_encode((object) $changeresponse);
		exit;
	}

	public function updatepageintrowithouttranslation()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$pid   = $this->input->getInt('pid');
			$intro = $this->input->getString('label');

			$changeresponse = $this->m_formbuilder->updatePageIntroWithoutTranslation($pid, $intro);
		}

		echo json_encode((object) $changeresponse);
		exit;
	}

	public function getJTEXTA()
	{
		$response = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));
		$user     = JFactory::getUser();

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {


			$toJTEXT = $this->input->getString('toJTEXT');

			$response = $this->m_formbuilder->getJTEXTA($toJTEXT);
		}

		echo json_encode((object) $response);
		exit;
	}

	public function getJTEXT()
	{


		$toJTEXT = $this->input->getString('toJTEXT');

		$getJtext = $this->m_formbuilder->getJTEXT($toJTEXT);

		echo json_encode((string) $getJtext);
		exit;
	}

	public function getalltranslations()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result   = 0;
			$getJtext = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$toJTEXT = $this->input->getString('toJTEXT');

			$languages = JLanguageHelper::getLanguages();

			$getJtext = new stdClass();
			foreach ($languages as $language) {
				$getJtext->{$language->sef} = $this->m_formbuilder->getTranslation($toJTEXT, $language->lang_code);
			}
		}
		echo json_encode((object) $getJtext);
		exit;
	}


	public function createMenu()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {

			$label    = $this->input->getRaw('label');
			$intro    = $this->input->getRaw('intro');
			$prid     = $this->input->getInt('prid');
			$modelid  = $this->input->getInt('modelid');
			$template = $this->input->getString('template');

			$label = json_decode($label, true);
			$intro = json_decode($intro, true);
			if ($modelid != -1) {
				$keep_structure = $this->input->getBool('keep_structure', false);
				$response       = $this->m_formbuilder->createMenuFromTemplate($label, $intro, $modelid, $prid, $keep_structure);
			}
			else {
				$response = $this->m_formbuilder->createApplicantMenu($label, $intro, $prid, $template);
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function checkifmodeltableisusedinform()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {

			$model_id   = $this->input->getInt('model_id', 0);
			$profile_id = $this->input->getInt('profile_id', 0);

			if (!empty($model_id) && !empty($profile_id)) {
				$response['data']   = $this->m_formbuilder->checkIfModelTableIsUsedInForm($model_id, $profile_id);
				$response['status'] = true;
				$response['msg']    = '';
			}
			else {
				$response['msg'] = JText::_('MISSING_PARAMS');
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function deletemenu()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$mid = $this->input->getInt('mid');

			$changeresponse = $this->m_formbuilder->deleteMenu($mid);
		}

		echo json_encode((object) $changeresponse);
		exit;
	}


	public function savemenuastemplate()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$menu     = $this->input->getRaw('menu');
			$template = $this->input->getString('template');

			$changeresponse = $this->m_formbuilder->saveAsTemplate($menu, $template);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}


	public function createsimplegroup()
	{
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
		$user     = JFactory::getUser();

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {


			$fid = $this->input->getInt('fid');
			if ($this->input->getRaw('label')) {
				$label = $this->input->getRaw('label');
			}
			else {
				$label = array(
					'fr' => 'Nouveau groupe',
					'en' => 'New group'
				);
			}

			$group = $this->m_formbuilder->createGroup($label, $fid);

			if (!empty($group['group_id'])) {
				$response           = $group;
				$response['status'] = true;
			}
		}
		echo json_encode((object) $response);
		exit;
	}


	public function deleteGroup()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$gid = $this->input->getInt('gid');

			$changeresponse = $this->m_formbuilder->deleteGroup($gid);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function getElement()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$element = $this->input->getInt('element');
			$gid     = $this->input->getInt('gid');

			$changeresponse = $this->m_formbuilder->getElement($element, $gid);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}

	public function retriveElementFormAssociatedDoc()
	{


		$docid = $this->input->getInt('docid');
		$gid   = $this->input->getInt('gid');

		$changeresponse = $this->m_formbuilder->retriveElementFormAssociatedDoc($gid, $docid);
		echo json_encode((object) $changeresponse);
		exit;
	}


	public function createsimpleelement()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$response['msg'] = JText::_('MISSING_PLUGIN_OR_GROUP');


			$gid    = $this->input->getInt('gid');
			$plugin = $this->input->getString('plugin');

			if (!empty($plugin) && !empty($gid)) {
				$mode       = $this->input->getString('mode');
				$evaluation = $mode == 'eval';
				if ($this->input->getString('attachmentId')) {
					$attachmentId = $this->input->getString('attachmentId');
				}

				if (isset($attachmentId)) {
					$response['data'] = $this->m_formbuilder->createSimpleElement($gid, $plugin, $attachmentId, $evaluation);
				}
				else {
					$response['data'] = $this->m_formbuilder->createSimpleElement($gid, $plugin, 0, $evaluation);
				}

				if (!empty($response['data'])) {
					$response['status'] = true;
					$response['msg']    = JText::_('COM_EMUNDUS_FORMBUILDER_ELEMENT_CREATED');
				}
				else {
					$response['msg'] = JText::_('COM_EMUNDUS_FORMBUILDER_ELEMENT_NOT_CREATED');
				}
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function createsectionsimpleelements()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {

			$gid    = $this->input->getInt('gid', 0);
			$plugin = $this->input->getString('plugins');
			$mode   = $this->input->getString('mode', 'form');

			if (!empty($gid)) {
				$response['data'] = $this->m_formbuilder->createSectionSimpleElements($gid, $plugin, $mode);

				if (!empty($response['data'])) {
					$response['status'] = true;
					$response['msg']    = JText::_('ELEMENTS_CREATED');
				}
				else {
					$response['msg'] = JText::_('ELEMENTS_NOT_CREATED');
				}
			}
			else {
				$response['msg'] = JText::_('MISSING_PARAMS');
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function createcriteria()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$gid    = $this->input->getInt('gid');
			$plugin = $this->input->getString('plugin');

			$changeresponse = $this->m_formbuilder->createSimpleElement($gid, $plugin, null, 1);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}


	public function deleteElement()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$element = $this->input->getInt('element');

			$changeresponse = $this->m_formbuilder->deleteElement($element);
		}
		echo json_encode((object) $changeresponse);
		exit;
	}


	public function reordermenu()
	{
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
		$user     = JFactory::getUser();

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {

			$menus   = json_decode($_POST['menus']);
			$profile = $this->input->getInt('profile');

			if (!empty($profile)) {
				$response['status'] = $this->m_formbuilder->reorderMenu($menus, $profile);
				$response['msg']    = $response['status'] ? JText::_('MENU_ORDER_UPDATED') : JText::_('MENU_ORDER_NOT_UPDATED');
			}
			else {
				$response['msg'] = JText::_('MISSING_PARAMS');
			}
		}

		echo json_encode((object) $response);
		exit;
	}


	public function getGroupOrdering()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$gid = $this->input->getInt('gid');
			$fid = $this->input->getInt('fid');

			$changeresponse = $this->m_formbuilder->getGroupOrdering($gid, $fid);
		}
		echo $changeresponse;
		exit;
	}

	public function reordergroups()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {

			$groups = $this->input->getString('groups');
			$fid    = $this->input->getInt('fid');

			if (!empty($groups)) {
				$groups = json_decode($groups, true);

				foreach ($groups as $group) {
					$changeresponse[] = $this->m_formbuilder->reorderGroup($group['id'], $fid, $group['order']);
				}
			}
		}

		echo json_encode((object) $changeresponse);
		exit;
	}

	public function getPagesModel()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result         = 0;
			$changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$changeresponse = $this->m_formbuilder->getPagesModel();
		}

		echo json_encode((object) $changeresponse);
		exit;
	}

	public function checkconstraintgroup()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$cid = $this->input->getInt('cid');

			$visibility = $this->m_formbuilder->checkConstraintGroup($cid);

			$tab = array('status' => 1, 'msg' => 'worked', 'data' => $visibility);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function checkvisibility()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$group = $this->input->getInt('group');
			$cid   = $this->input->getInt('cid');

			$visibility = $this->m_formbuilder->checkVisibility($group, $cid);

			$tab = array('status' => 1, 'msg' => 'worked', 'data' => $visibility);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getdatabasesjoin()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$databases = $this->m_formbuilder->getDatabasesJoin();

			$tab = array('status' => 1, 'msg' => 'worked', 'data' => $databases);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getDatabaseJoinOrderColumns()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {

			$database_name = $this->input->getString('database_name');

			if (!empty($database_name)) {
				$database_name_columns = $this->m_formbuilder->getDatabaseJoinOrderColumns($database_name);
				$tab                   = array('status' => 1, 'msg' => 'worked', 'data' => $database_name_columns);
			}
			else {
				$tab = array('status' => 0, 'msg' => 'Missing database_name parameter');
			}
		}

		echo json_encode((object) $tab);
		exit;
	}

	public function enablegrouprepeat()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$gid = $this->input->getInt('gid');

			$state = $this->m_formbuilder->enableRepeatGroup($gid);

			$tab = array('status' => $state, 'msg' => 'worked');
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function disablegrouprepeat()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$gid = $this->input->getInt('gid');

			$state = $this->m_formbuilder->disableRepeatGroup($gid);

			$tab = array('status' => $state, 'msg' => 'worked');
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function displayhidegroup()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$gid = $this->input->getInt('gid');

			$state = $this->m_formbuilder->displayHideGroup($gid);

			$tab = array('status' => $state, 'msg' => 'worked');
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function updatemenulabel()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$label = $this->input->getRaw('label');
			$pid   = $this->input->getString('pid');

			$state = $this->m_formbuilder->updateMenuLabel($label, $pid);

			$tab = array('status' => $state, 'msg' => 'worked');
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function gettestingparams()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$prid = $this->input->getInt('prid');

			$campaign_files = $this->m_formbuilder->getFormTesting($prid, $user->id);

			$tab = array('status' => true, 'user' => $user, 'campaign_files' => $campaign_files);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function createtestingfile()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$cid = $this->input->getInt('cid');

			$fnum = $this->m_formbuilder->createTestingFile($cid, $user->id);

			$tab = array('status' => true, 'fnum' => $fnum);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function deletetestingfile()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$fnum = $this->input->getString('file');

			$status = $this->m_formbuilder->deleteFormTesting($fnum, $user->id);

			$tab = array('status' => $status, 'userid' => $user->id);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function updatedocument()
	{
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
		$user     = JFactory::getUser();

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {

			$document_id = $this->input->getInt('document_id');
			$profile_id  = $this->input->getInt('profile_id');
			$document    = $this->input->getString('document');
			$document    = json_decode($document, true);
			$types       = $this->input->getString('types');
			$types       = json_decode($types, true);
			$params      = ['has_sample' => $this->input->getBool('has_sample', false)];

			if ($params['has_sample'] && !empty($_FILES['file'])) {
				$params['file'] = $_FILES['file'];
			}

			if (!empty($document_id) && !empty($document) && !empty($profile_id)) {
				require_once JPATH_SITE . '/components/com_emundus/models/campaign.php';
				$m_campaign = $this->getModel('Campaign');

				$result = $m_campaign->updateDocument($document, $types, $document_id, $profile_id, $params);

				if ($result) {
					$response['status'] = true;
					$response['msg']    = 'SUCCESS';
				}
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function updatedefaultvalue()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$eid   = $this->input->getInt('eid');
			$value = $this->input->getRaw('value');

			$status = $this->m_formbuilder->updateDefaultValue($eid, $value);

			$tab = array('status' => $status, 'userid' => $user->id);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getalldatabases()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {
			$databases = $this->m_formbuilder->getAllDatabases();

			$tab = array('status' => 1, 'msg' => 'worked', 'data' => $databases);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function getsection()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {


			$section = $this->input->getInt('section');

			$group = $this->m_formbuilder->getSection($section);

			$tab = array('status' => true, 'group' => $group);
		}
		echo json_encode((object) $tab);
		exit;
	}

	public function updateElementOption()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {

			$element        = $this->input->getInt("element");
			$options        = json_decode($this->input->getString("options"), true);
			$index          = $this->input->getInt("index");
			$newTranslation = $this->input->getString("newTranslation");
			$lang           = $this->input->getString("lang");

			if (!empty($element) && !empty($options) && !empty($newTranslation)) {
				$translated = $this->m_formbuilder->updateElementOption($element, $options, $index, $newTranslation, $lang);
				$tab        = array('status' => $translated);
			}
			else {
				$tab = array('status' => false, 'msg' => "MISSING_PARAMETERS");
			}
		}

		echo json_encode((object) $tab);
		exit;
	}

	public function getelementsuboptions()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {

			$element = $this->input->getInt("element");

			if (!empty($element)) {
				$options = $this->m_formbuilder->getElementSubOption($element);
				$tab     = array('status' => !empty($options), 'new_options' => $options);
			}
			else {
				$tab = array('status' => false, 'msg' => "MISSING_PARAMETERS");
			}
		}

		echo json_encode((object) $tab);
		exit;
	}

	public function addElementSubOption()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {

			$element   = $this->input->getInt("element");
			$newOption = $this->input->getString("newOption");
			$lang      = $this->input->getString("lang");

			if (!empty($element) && !empty($newOption)) {
				$options = $this->m_formbuilder->addElementSubOption($element, $newOption, $lang);
				$tab     = array('status' => !empty($options), 'options' => $options);
			}
			else {
				$tab = array('status' => false, 'msg' => "MISSING_PARAMETERS");
			}
		}

		echo json_encode((object) $tab);
		exit;
	}

	public function deleteElementSubOption()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {

			$element = $this->input->getInt("element");
			$index   = $this->input->getInt("index");

			if (!empty($element) && !empty($index)) {
				$deleted = $this->m_formbuilder->deleteElementSubOption($element, $index);
				$tab     = array('status' => $deleted);
			}
			else {
				$tab = array('status' => false, 'msg' => "MISSING_PARAMETERS");
			}
		}

		echo json_encode((object) $tab);
		exit;
	}

	public function updateElementSubOptionsOrder()
	{
		$user = JFactory::getUser();

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$result = 0;
			$tab    = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
		}
		else {

			$element   = $this->input->getInt("element");
			$old_order = json_decode($this->input->getString("options_old_order"), true);
			$new_order = json_decode($this->input->getString("options_new_order"), true);

			if (!empty($element) && !empty($new_order) && !empty($old_order)) {
				$updated = $this->m_formbuilder->updateElementSubOptionsOrder($element, $old_order, $new_order);
				$tab     = array('status' => $updated);
			}
			else {
				$tab = array('status' => false, 'msg' => "MISSING_PARAMETERS");
			}
		}

		echo json_encode((object) $tab);
		exit;
	}

	public function getpagemodels()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));
		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$models             = $this->m_formbuilder->getPagesModel();
			$response['status'] = true;
			$response['data']   = $models;
			$response['msg']    = 'Succès';
		}

		echo json_encode((object) $response);
		exit;
	}

	public function getallmodels()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
			$models             = $this->m_formbuilder->getPagesModel();
			$response['status'] = true;
			$response['data']   = ['datas' => $models, 'count' => count($models)];
			$response['msg']    = 'Succès';
		}

		echo json_encode((object) $response);
		exit;
	}

	public function addformmodel()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {

			$form_id = $this->input->getInt('form_id');
			$label   = $this->input->getString('label');

			if (!empty($form_id) && !empty($label)) {
				$response['status'] = $this->m_formbuilder->addFormModel($form_id, $label);
				$response['msg']    = $response['status'] ? JText::_('SUCCESS') : JText::_('FAILED');
			}
			else {
				$response['msg'] = JText::_('MISSING_PARAMS');
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function deleteformmodel()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {

			$form_id = $this->input->getInt('form_id');

			if (!empty($form_id)) {
				$response['status'] = $this->m_formbuilder->deleteFormModel($form_id);
				$response['msg']    = $response['status'] ? JText::_('SUCCESS') : JText::_('FAILED');
			}
			else {
				$response['msg'] = JText::_('MISSING_PARAMS');
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function deleteformmodelfromids()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {

			$model_ids = $this->input->getString('model_ids');
			$model_ids = json_decode($model_ids, true);

			if (!empty($model_ids)) {
				$model_ids          = is_array($model_ids) ? $model_ids : array($model_ids);
				$response['status'] = $this->m_formbuilder->deleteFormModelFromIds($model_ids);
				$response['msg']    = $response['status'] ? JText::_('SUCCESS') : JText::_('FAILED');
			}
			else {
				$response['msg'] = JText::_('MISSING_PARAMS');
			}
		}

		echo json_encode((object) $response);
		exit;
	}

	public function getdocumentsample()
	{
		$user     = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'), 'code' => 403);

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$response = array('status' => false, 'msg' => JText::_('MISSING_PARAMS'));


			$document_id = $this->input->getInt('document_id');
			$profile_id  = $this->input->getInt('profile_id');

			if (!empty($document_id) && !empty($profile_id)) {
				$document = $this->m_formbuilder->getDocumentSample($document_id, $profile_id);
				$document = empty($document) ? array('has_sample' => 0, 'sample_filepath' => '') : $document;
				$response = array('status' => true, 'msg' => JText::_('SUCCESS'), 'code' => 200, 'data' => $document);
			}
		}

		echo json_encode((object) $response);
		exit;
	}
}


