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

use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');
//error_reporting(E_ALL);
jimport('joomla.application.component.view');

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
	var $user = null;
	var $users = [];
	var $pagination = [];
	var $lists = [];
	var $code = null;
	var $fnum_assoc = null;
	var $filters = null;
	var $uGroups = null;
	var $juGroups = null;
	var $uCamps = null;
	var $uOprofiles = null;
	var $app_prof = null;
	var $edit = null;
	var $profiles = null;
	var $groups = null;
	var $jgroups = null;
	var $campaigns = null;
	var $universities = null;
	var $ldapElements = null;
	var $actions = null;
	var $progs = null;
	var $items = null;
	var $display = null;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'javascript.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');

		$this->_user = JFactory::getUser();
		$this->_db   = JFactory::getDBO();

		$menu         = @JFactory::getApplication()->getMenu();
		$current_menu = $menu->getActive();
		$menu_params  = $menu->getParams(@$current_menu->id);

		//Pre-filters
		$filts_names  = explode(',', $menu_params->get('em_filters_names'));
		$filts_values = explode(',', $menu_params->get('em_filters_values'));

		foreach ($filts_names as $key => $filt_name) {
			if (array_key_exists($key, $filts_values) && !empty($filts_values[$key]))
				$this->filts_details[$filt_name] = explode('|', $filts_values[$key]);
			else
				$this->filts_details[$filt_name] = null;
		}

		parent::__construct($config);
	}

	private function _loadData()
	{
		$m_users                = new EmundusModelUsers();
		$m_users->filts_details = $this->filts_details;
		$users                  = $m_users->getUsers();
		$this->users            = $users;

		$pagination       = $m_users->getPagination();
		$this->pagination = $pagination;

		$lists['order_dir'] = JFactory::getSession()->get('filter_order_Dir');
		$lists['order']     = JFactory::getSession()->get('filter_order');
		$this->lists        = $lists;
	}

	private function _loadFilter()
	{
		$m_users = new EmundusModelUsers();
		$model   = new EmundusModelFiles;

		$model->code       = $m_users->getUserGroupsProgrammeAssoc($this->_user->id);
		$model->fnum_assoc = $m_users->getApplicantsAssoc($this->_user->id);
		$this->code        = $model->code;
		$this->fnum_assoc  = $model->fnum_assoc;

		$this->filters = @EmundusHelperFiles::resetFilter();
	}

	private function _loadUserForm()
	{
		$m_users = new EmundusModelUsers();
		$edit    = JFactory::getApplication()->input->getInt('edit', null);

		include_once(JPATH_BASE . '/components/com_emundus/models/profile.php');
		$m_profiles = new EmundusModelProfile;
		$app_prof   = $m_profiles->getApplicantsProfilesArray();

		$eMConfig = JComponentHelper::getParams('com_emundus');

		if ($edit == 1) {
			$uid  = JFactory::getApplication()->input->getInt('user', null);
			$user = $m_users->getUserInfos($uid);

			$uGroups = $m_users->getUserGroups($uid);
			if ($eMConfig->get('showJoomlagroups', 0)) {
				$juGroups = $m_users->getUsersIntranetGroups($uid);
			}
			$uCamps     = $m_users->getUserCampaigns($uid);
			$uOprofiles = $m_users->getUserOprofiles($uid);

			$this->user    = $user;
			$this->uGroups = $uGroups;
			if ($eMConfig->get('showJoomlagroups', 0)) {
				$this->juGroups = $juGroups;
			}
			$this->uCamps     = $uCamps;
			$this->uOprofiles = $uOprofiles;
			$this->app_prof   = $app_prof;

		}
		$this->edit = $edit;

		if (!empty($this->filts_details['profile_users'])) {
			$this->profiles = $m_users->getProfilesByIDs($this->filts_details['profile_users']);
		}
		else {
			$this->profiles = $m_users->getProfiles();
		}

		$this->groups = $m_users->getGroups();

		if ($eMConfig->get('showJoomlagroups', 0)) {
			$this->jgroups = $m_users->getLascalaIntranetGroups();
		}

		$this->campaigns    = $m_users->getAllCampaigns();
		$this->universities = $m_users->getUniversities();

		// Get the LDAP elements.
		$params             = JComponentHelper::getParams('com_emundus');
		$this->ldapElements = $params->get('ldapElements');
	}

	private function _loadGroupForm()
	{
		$m_users       = new EmundusModelUsers();
		$this->actions = $m_users->getActions();
		$this->progs   = $m_users->getProgramme();
	}

	private function _loadAffectForm()
	{
		$m_users      = new EmundusModelUsers();
		$this->groups = $m_users->getGroups();
	}

	private function _loadAffectIntranetForm()
	{
		$m_users = new EmundusModelUsers();
		$groups  = $m_users->getLascalaIntranetGroups();
	}

	private function _loadRightsForm()
	{
		$m_users = new EmundusModelUsers();
		$uid     = Factory::getApplication()->input->getInt('user', null);
		$groups  = $m_users->getUserGroups($uid);

		$g = array();
		foreach ($groups as $key => $label) {
			$g[$key]['label'] = $label;
			$g[$key]['progs'] = $m_users->getGroupProgs($key);
			$g[$key]['acl']   = $m_users->getGroupsAcl($key);
		}

		$this->groups = $g;
	}

	function display($tpl = null)
	{

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id))
			die('ACCESS_DENIED');

		$layout  = JFactory::getApplication()->input->getString('layout', null);
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
				$display      = JFactory::getApplication()->input->getString('display', 'none');
				$menu         = JFactory::getApplication()->getMenu();
				$current_menu = $menu->getActive();
				$params       = $menu->getParams($current_menu->id);

				$items   = EmundusHelperFiles::getMenuList($params);
				$actions = $m_files->getAllActions();

				$menuActions = array();
				foreach ($items as $key => $item) {
					if (!empty($item->note)) {
						$note = explode('|', $item->note);
						if ($actions[$note[0]][$note[1]] == 1) {
							$actions[$note[0]]['multi'] = $note[2];
							$actions[$note[0]]['grud']  = $note[1];
							$item->action               = $actions[$note[0]];
							$menuActions[]              = $item;
						}
					}
					else
						$menuActions[] = $item;
				}

				$this->items   = $menuActions;
				$this->display = $display;
				break;
		}

		$this->onSubmitForm = EmundusHelperJavascript::onSubmitForm();
		$this->itemId       = Factory::getApplication()->input->getInt('Itemid', null);

		parent::display($tpl);
	}

}
