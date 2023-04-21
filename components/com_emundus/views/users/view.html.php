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
 * @package		Joomla.Site
 * @subpackage	com_emundus
 * @since 1.6
 */

class EmundusViewUsers extends JViewLegacy {
	var $_user = null;
	var $_db = null;

	function __construct($config = array()){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();

		parent::__construct($config);
	}

    private function _loadData() {
		$m_users = new EmundusModelUsers();
		$users = $m_users->getUsers();
		$this->assignRef('users', $users);

		$pagination = $m_users->getPagination();
		$this->assignRef('pagination', $pagination);

		$lists['order_dir'] = JFactory::getSession()->get( 'filter_order_Dir' );
		$lists['order']   = JFactory::getSession()->get( 'filter_order' );
		$this->assignRef('lists', $lists);
	}

    private function _loadFilter() {

		$filts_details	= [
			'profile_users'		=> 1,
			'o_profiles'		=> 1,
			'evaluator_group'	=> 1,
			'schoolyear'		=> 1,
			'campaign'			=> 1,
			'programme'			=> 1,
			'newsletter'		=> 1,
			'group'             => 1,
			'institution'       => 1,
			'spam_suspect'		=> 0,
			'not_adv_filter'	=> 1,
		];
		$filts_options 	= [
			'profile_users'		=> NULL,
			'o_profiles'		=> NULL,
			'evaluator_group'	=> NULL,
			'schoolyear'		=> NULL,
			'campaign'			=> NULL,
			'programme'			=> NULL,
			'newsletter'		=> NULL,
			'spam_suspect'		=> NULL,
			'not_adv_filter'	=> NULL,
		];

		$filters = EmundusHelperFiles::createFilterBlock($filts_details, $filts_options, array());
		$this->assignRef('filters', $filters);
	}

	private function _loadUserForm()
	{
		$m_users = new EmundusModelUsers();
		$edit = JFactory::getApplication()->input->getInt('edit', null);

		if ($edit == 1)
		{
			$uid = JFactory::getApplication()->input->getInt('user', null);
			$user  		= $m_users->getUserInfos($uid);
			$uGroups 	= $m_users->getUserGroups($uid);
			$uCamps 	= $m_users->getUserCampaigns($uid);
			$uOprofiles = $m_users->getUserOprofiles($uid);
			$this->assignRef('user', $user);
			$this->assignRef('uGroups', $uGroups);
			$this->assignRef('uCamps', $uCamps);
			$this->assignRef('uOprofiles', $uOprofiles);

		}
		$this->assignRef('edit', $edit);

		$profiles = $m_users->getProfiles();
		$this->assignRef('profiles', $profiles);
		$groups = $m_users->getGroups();
		$this->assignRef('groups', $groups);

		$campaigns = $m_users->getAllCampaigns();
		$this->assignRef('campaigns', $campaigns);

		$universities = $m_users->getUniversities();
		$this->assignRef('universities', $universities);
	}

	private function _loadGroupForm()
	{
		$model = new EmundusModelFiles();
		$umodel = new EmundusModelUsers();
		$actions = $model->getActions('1,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23');
		$prog = $umodel->getProgramme();
		$this->assignRef('actions', $actions);
		$this->assignRef('progs', $prog);
	}
	private function _loadAffectForm()
	{
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
	private function _loadRightsForm()
	{
		$m_users = new EmundusModelUsers();
		$uid = JFactory::getApplication()->input->getInt('user', null);
		$groups = $m_users->getUserGroups($uid);
		$g = array();
		foreach($groups as $key => $label)
		{
			$g[$key]['label'] = $label;
			$g[$key]['progs'] = $m_users->getGroupProgs($key);
			$g[$key]['acl'] = $m_users->getGroupsAcl($key);

		}

		$this->assignRef('groups', $g);
	}

	private function _loadGroupRights($gid) {
		$m_users = new EmundusModelUsers();
		$group = $m_users->getGroupProgs($gid);
		$g[0]['label'] = $group[0]['group_label'];
		$g[0]['progs'] = $m_users->getGroupProgs($gid);
		$g[0]['acl'] = $m_users->getGroupsAcl($gid);
		$users = $m_users->getGroupUsers($gid);

		$this->assignRef('groups', $g);
		$this->assignRef('users', $users);
	}

    function display($tpl = null) {
	    JHtml::stylesheet( 'media/com_emundus/css/emundus_files.css');

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
				require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'actions.php');
                $gid = JFactory::getApplication()->input->getInt('rowid', null);
                $m_actions = new EmundusModelActions;
				$m_actions->syncAllActions(false, $gid);
				$this->_loadGroupRights($gid);
				break;
            case 'edit':
                $edit_profile = 1;
                break;
			default :
                JHTML::script( 'media/com_emundus/js/em_user.js');
                @EmundusHelperFiles::clear();
			    $m_users = new EmundusModelUsers();
			    $actions = $m_users->getActions("19,20,21,22,23,24,25,26");

			    $acts = array('user' => array(), 'group' => array());
			    if (!empty($actions)) {
				    foreach ($actions as $action) {
					    if (preg_match('/.*_user/', $action['name']) === 1 ) {
						    $acts['user'][] = $action;
					    } elseif (preg_match('/.*_group/', $action['name']) === 1) {
						    $acts['group'][] = $action;
					    } else {
						    if ($action['name'] != 'user') {
							    $acts['other'][] = $action;
						    }
					    }
				    }
			    }
			    $this->assignRef('actions', $acts);
			 break;
		}

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) && !$edit_profile) {
            die("ACCESS_DENIED");
        }

		$onSubmitForm = EmundusHelperJavascript::onSubmitForm();
		$this->assignRef('onSubmitForm', $onSubmitForm);

		$itemId = JFactory::getApplication()->input->getInt('Itemid', null);
        $this->assignRef('itemId',$itemId);

		parent::display($tpl);
    }
}
