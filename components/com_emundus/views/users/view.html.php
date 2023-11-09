<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * User view
 *
 * @package        Joomla.Site
 * @subpackage     com_emundus
 * @since          1.6
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
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');

		$this->_user = JFactory::getUser();
		$this->_db   = JFactory::getDBO();

		parent::__construct($config);
	}

	private function _loadData()
	{
		$m_users     = new EmundusModelUsers();
		$this->users = $m_users->getUsers();

		$this->pagination = $m_users->getPagination();

		$lists['order_dir'] = JFactory::getSession()->get('filter_order_Dir');
		$lists['order']     = JFactory::getSession()->get('filter_order');
		$this->lists        = $lists;
	}

	private function _loadFilter()
	{

		$filts_details = [
			'profile_users'   => 1,
			'o_profiles'      => 1,
			'evaluator_group' => 1,
			'schoolyear'      => 1,
			'campaign'        => 1,
			'programme'       => 1,
			'newsletter'      => 1,
			'group'           => 1,
			'institution'     => 1,
			'spam_suspect'    => 0,
			'not_adv_filter'  => 1,
		];
		$filts_options = [
			'profile_users'   => null,
			'o_profiles'      => null,
			'evaluator_group' => null,
			'schoolyear'      => null,
			'campaign'        => null,
			'programme'       => null,
			'newsletter'      => null,
			'spam_suspect'    => null,
			'not_adv_filter'  => null,
		];

		$this->filters = EmundusHelperFiles::createFilterBlock($filts_details, $filts_options, array());
	}

	private function _loadUserForm()
	{
		$m_users    = new EmundusModelUsers();
		$this->edit = JFactory::getApplication()->input->getInt('edit', null);

		if ($this->edit == 1) {
			$uid              = JFactory::getApplication()->input->getInt('user', null);
			$this->user       = $m_users->getUserInfos($uid);
			$this->uGroups    = $m_users->getUserGroups($uid);
			$this->uCamps     = $m_users->getUserCampaigns($uid);
			$this->uOprofiles = $m_users->getUserOprofiles($uid);

		}

		$this->profiles = $m_users->getProfiles();
		$this->groups   = $m_users->getGroups();

		$this->campaigns = $m_users->getAllCampaigns();

		$this->universities = $m_users->getUniversities();
	}

	private function _loadGroupForm()
	{
		$model         = new EmundusModelFiles();
		$umodel        = new EmundusModelUsers();
		$this->actions = $model->getActions('1,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23');
		$this->progs   = $umodel->getProgramme();
	}

	private function _loadAffectForm()
	{
		$m_users      = new EmundusModelUsers();
		$this->groups = $m_users->getGroups();
	}

	private function _loadAffectIntranetForm()
	{
		$m_users      = new EmundusModelUsers();
		$this->groups = $m_users->getLascalaIntranetGroups();
	}

	private function _loadRightsForm()
	{
		$m_users = new EmundusModelUsers();
		$uid     = JFactory::getApplication()->input->getInt('user', null);
		$groups  = $m_users->getUserGroups($uid);
		$g       = array();
		foreach ($groups as $key => $label) {
			$g[$key]['label'] = $label;
			$g[$key]['progs'] = $m_users->getGroupProgs($key);
			$g[$key]['acl']   = $m_users->getGroupsAcl($key);
		}

		$this->groups = $g;
	}

	private function _loadGroupRights($gid)
	{
		$m_users       = new EmundusModelUsers();
		$group         = $m_users->getGroupProgs($gid);
		$g[0]['label'] = $group[0]['group_label'];
		$g[0]['progs'] = $m_users->getGroupProgs($gid);
		$g[0]['acl']   = $m_users->getGroupsAcl($gid);
		$this->groups  = $g;
		$this->users   = $m_users->getGroupUsers($gid);
	}

	function display($tpl = null)
	{
		JHtml::stylesheet('media/com_emundus/css/emundus_files.css');

		$edit_profile = 0;

		$layout = JFactory::getApplication()->input->getString('layout', null);
		switch ($layout) {
			case 'user':
				$this->_loadData();
				break;
			case 'filter':
				//$this->_loadFilter();
				break;
			case 'adduser':
				$this->_loadUserForm();
				break;
			case 'addgroup':
				$this->_loadGroupForm();
				break;
			case 'affectgroup':
				$this->_loadAffectForm();
				break;
			case 'affectintranetlascala':
				$this->_loadAffectIntranetForm();
				break;
			case 'showrights':
				$this->_loadRightsForm();
				break;
			case 'showgrouprights':
				// We are running the group Sync code here, this makes sure that group rights are always present, no matter if groups are made in some other way.
				require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'actions.php');
				$gid       = JFactory::getApplication()->input->getInt('rowid', null);
				$m_actions = new EmundusModelActions;
				$m_actions->syncAllActions(false, $gid);
				$this->_loadGroupRights($gid);
				break;
			case 'edit':
				$edit_profile = 1;
				break;
			default :
				JHTML::script('media/com_emundus/js/em_user.js');
				@EmundusHelperFiles::clear();
				$m_users = new EmundusModelUsers();
				$actions = $m_users->getActions("19,20,21,22,23,24,25,26");

				$acts = array('user' => array(), 'group' => array());
				if (!empty($actions)) {
					foreach ($actions as $action) {
						if (preg_match('/.*_user/', $action['name']) === 1) {
							$acts['user'][] = $action;
						}
						elseif (preg_match('/.*_group/', $action['name']) === 1) {
							$acts['group'][] = $action;
						}
						else {
							if ($action['name'] != 'user') {
								$acts['other'][] = $action;
							}
						}
					}
				}
				$this->actions = $acts;
				break;
		}

		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) && !$edit_profile) {
			die('ACCESS_DENIED');
		}

		$this->onSubmitForm = EmundusHelperJavascript::onSubmitForm();
		$this->itemId       = JFactory::getApplication()->input->getInt('Itemid', null);

		parent::display($tpl);
	}
}
