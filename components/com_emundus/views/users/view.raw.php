<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
//error_reporting(E_ALL);
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */


class EmundusViewUsers extends JViewLegacy
{
	var $_user = null;
	var $_db = null;
    var $filts_details = null;

	function __construct($config = array()) {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();

        $menu = @JFactory::getApplication()->getMenu();
        $current_menu   = $menu->getActive();
        $menu_params    = $menu->getParams(@$current_menu->id);

        //Pre-filters
        //$tables 		= explode(',', $menu_params->get('em_tables_id'));
        $filts_names 	= explode(',', $menu_params->get('em_filters_names'));
        $filts_values	= explode(',', $menu_params->get('em_filters_values'));
        //$filts_types  	= explode(',', $menu_params->get('em_filters_options'));

        foreach ($filts_names as $key => $filt_name) {
            if (array_key_exists($key, $filts_values) && !empty($filts_values[$key]))
                $this->filts_details[$filt_name] = explode('|', $filts_values[$key]);
            else
                $this->filts_details[$filt_name] = null;
        }

		parent::__construct($config);
	}

	private function _loadData() {
		$m_users = new EmundusModelUsers();
        $m_users->filts_details = $this->filts_details;
		$users = $m_users->getUsers();
		$applicant_profiles = $m_users->getApplicantProfiles();
		$applicant_profiles = array_column($applicant_profiles, 'id');

		foreach ($users as $user) {
			if(!empty($user->o_profiles)) {
				$o_profiles = explode(',', $user->o_profiles);
				$profile_details = $m_users->getProfilesByIDs($o_profiles);
				$user->o_profiles = array_map((function($a) use ($applicant_profiles,$profile_details) {
					if(in_array($a, $applicant_profiles)) {
						return JText::_('COM_EMUNDUS_APPLICANT');
					} else {
						return $profile_details[$a]->label;
					}
				}), $o_profiles);

				$user->o_profiles = implode('<br>', $user->o_profiles);
			}
		}
		$this->assignRef('users', $users);

		$pagination = $m_users->getPagination();
		$this->assignRef('pagination', $pagination);

		$lists['order_dir'] = JFactory::getSession()->get( 'filter_order_Dir' );
		$lists['order']     = JFactory::getSession()->get( 'filter_order' );
		$this->assignRef('lists', $lists);
	}

	private function _loadFilter() {
        $m_users = new EmundusModelUsers();
		$model = new EmundusModelFiles;

        $model->code = $m_users->getUserGroupsProgrammeAssoc($this->_user->id);
        $model->fnum_assoc = $m_users->getApplicantsAssoc($this->_user->id);
        $this->assignRef('code', $model->code);
        $this->assignRef('fnum_assoc', $model->fnum_assoc);

        // reset filter
        $filters = @EmundusHelperFiles::resetFilter();
        $this->assignRef('filters', $filters);
	}

	private function _loadUserForm() {
		$m_users = new EmundusModelUsers();
		$edit = JFactory::getApplication()->input->getInt('edit', null);

        include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
        $m_profiles = new EmundusModelProfile;
        $app_prof = $m_profiles->getApplicantsProfilesArray();

        $eMConfig = JComponentHelper::getParams('com_emundus');

		if ($edit == 1) {
			$uid = JFactory::getApplication()->input->getInt('user', null);
			$user  = $m_users->getUserInfos($uid);

			$uGroups = $m_users->getUserGroups($uid);
            if($eMConfig->get('showJoomlagroups',0)) {
                $juGroups = $m_users->getUsersIntranetGroups($uid);
            }
			$uCamps = $m_users->getUserCampaigns($uid);
			$uOprofiles = $m_users->getUserOprofiles($uid);

			$this->assignRef('user', $user);
			$this->assignRef('uGroups', $uGroups);
            if($eMConfig->get('showJoomlagroups',0)) {
                $this->assignRef('juGroups', $juGroups);
            }
			$this->assignRef('uCamps', $uCamps);
			$this->assignRef('uOprofiles', $uOprofiles);
			$this->assignRef('app_prof', $app_prof);
		}
		$this->assignRef('edit', $edit);

        if (!empty($this->filts_details['profile_users']))
    		$profiles = $m_users->getProfilesByIDs($this->filts_details['profile_users']);
		else
            $profiles = $m_users->getProfiles();
		
		$this->assignRef('profiles', $profiles);

		$groups = $m_users->getGroups();
		$this->assignRef('groups', $groups);

        if($eMConfig->get('showJoomlagroups',0)) {
            $jgroups = $m_users->getLascalaIntranetGroups();
            $this->assignRef('jgroups', $jgroups);
        }

		$campaigns = $m_users->getAllCampaigns();
		$this->assignRef('campaigns', $campaigns);

		$universities = $m_users->getUniversities();
		$this->assignRef('universities', $universities);

		// Get the LDAP elements.
		$params = JComponentHelper::getParams('com_emundus');
		$ldapElements = $params->get('ldapElements');
		$this->assignRef('ldapElements', $ldapElements);
	}

	private function _loadGroupForm() {
		$m_users = new EmundusModelUsers();
		$actions = $m_users->getActions();
		$prog = $m_users->getProgramme();
		$this->assignRef('actions', $actions);
		$this->assignRef('progs', $prog);
	}

	private function _loadAffectForm() {
		$m_users = new EmundusModelUsers();
		$groups = $m_users->getGroups();
		$this->assignRef('groups', $groups);
	}

	private function _loadAffectIntranetForm()
	{
		$m_users = new EmundusModelUsers();
		$groups = $m_users->getLascalaIntranetGroups();
		$this->assignRef('groups', $groups);
	}

	private function _loadRightsForm() {
		$m_users = new EmundusModelUsers();
		$uid = JFactory::getApplication()->input->getInt('user', null);
		$groups = $m_users->getUserGroups($uid);
		$g = array();
		foreach ($groups as $key => $label) {
			$g[$key]['label'] = $label;
			$g[$key]['progs'] = $m_users->getGroupProgs($key);
			$g[$key]['acl'] = $m_users->getGroupsAcl($key);
		}
		//$acl = $m_users->getUserACL($uid);

		$this->assignRef('groups', $g);
	}

	function display($tpl = null) {

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id))
			die("ACCESS_DENIED");

		$layout = JFactory::getApplication()->input->getString('layout', null);
        $m_files = new EmundusModelFiles();
		switch ($layout) {
			case 'user':
				$this->_loadData();
				break;
			case 'filter':
				$this->_loadFilter();
				break;
			case 'adduser':
				$this->_loadUserForm();
				break;
			case 'addgroup':
				$this->_loadGroupForm();
				break;
			case 'affectintranetlascala':
				$this->_loadAffectIntranetForm();
				break;
			case 'affectgroup':
				$this->_loadAffectForm();
				break;
			case 'showrights':
				$this->_loadRightsForm();
				break;
			case 'menuactions':
				$display = JFactory::getApplication()->input->getString('display', 'none');
				$menu = JFactory::getApplication()->getMenu();
				$current_menu  = $menu->getActive();
				$params = $menu->getParams($current_menu->id);

				$items = EmundusHelperFiles::getMenuList($params);
                //$actions = @EmundusHelperFiles::getActionsACL();
                $actions = $m_files->getAllActions();

				$menuActions = array();
				foreach ($items as $key => $item) {
					if (!empty($item->note)) {
						$note = explode('|', $item->note);
						if ($actions[$note[0]][$note[1]] == 1) {
							$actions[$note[0]]['multi'] = $note[2];
							$actions[$note[0]]['grud'] = $note[1];
							$item->action = $actions[$note[0]];
							$menuActions[] = $item;
						}
					} else
						$menuActions[] = $item;
				}

				$this->assignRef('items', $menuActions);
				$this->assignRef('display', $display);

			break;
		}
		// Javascript
		$onSubmitForm = EmundusHelperJavascript::onSubmitForm();
		$this->assignRef('onSubmitForm', $onSubmitForm);

		$itemId = JFactory::getApplication()->input->getInt('Itemid', null);
		$this->assignRef('itemId', $itemId);

		parent::display($tpl);
	}

}
