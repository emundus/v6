<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * User view
 *
 * @package		Joomla.Site
 * @subpackage	com_emundus
 * @since 1.6
 */

class EmundusViewUsers extends JViewLegacy
{
	var $_user = null;
	var $_db = null;

	function __construct($config = array()){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
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
		$menu 		= JSite::getMenu()->getActive();
		$access 	= !empty($menu)?$menu->access : 0;
		$state 		= EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access)  ? '' : NULL;
		$session 	= JFactory::getSession();
		$params 	= $session->get('filt_params');
		$state 		= $params;

		$filts_details	= [
			'profile_users'		=> 1,
			'o_profiles'		=> 1,
			'evaluator_group'	=> 1,
			'schoolyear'		=> 1,
			'campaign'			=> 1,
			'programme'			=> 1,
			//'finalgrade'		=> $state,
			'newsletter'		=> 1,
			'group'             => 1,
			'institution'       => 1,
			'spam_suspect'		=> 1,
			'not_adv_filter'	=> 1,
		];
		$filts_options 	= [
			'profile_users'		=> NULL,
			'o_profiles'		=> NULL,
			'evaluator_group'	=> NULL,
			'schoolyear'		=> NULL,
			'campaign'			=> NULL,
			'programme'			=> NULL,
			//'finalgrade'		=> NULL,
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

		if($edit == 1)
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

	private function _loadGroupRights()
	{
		$m_users = new EmundusModelUsers();
		$gid = JFactory::getApplication()->input->getInt('rowid', null);
		$group = $m_users->getGroupProgs($gid);
		$g[0]['label'] = $group[0]['group_label'];
		$g[0]['progs'] = $m_users->getGroupProgs($gid);
		$g[0]['acl'] = $m_users->getGroupsAcl($gid);
//var_dump($g[0]['acl']);
		$users = $m_users->getGroupUsers($gid);

		$this->assignRef('groups', $g);
		$this->assignRef('users', $users);
	}

    function display($tpl = null)
    {
	   // JHtml::script( JURI::base(true) . 'media/com_emundus/lib/jquery-1.10.2.min.js');
	   // JHtml::script( JURI::base(true) . 'media/com_emundus/lib/bootstrap-emundus/js/bootstrap.min.js');
	   // JHTML::script( "media/com_emundus/lib/chosen/chosen.jquery.min.js" );
	    JHTML::script( 'media/com_emundus/js/em_user.js');
	   // JHTML::script( 'media/com_emundus/js/jquery-form.js');
	   // JHtml::styleSheet( JURI::base(true)."media/com_emundus/lib/chosen/chosen.min.css");
	   // JHtml::stylesheet( JURI::base(true) . 'media/com_emundus/lib/bootstrap-emundus/css/bootstrap.min.css');
	    JHtml::stylesheet( JURI::base(true) . 'media/com_emundus/css/emundus_files.css');

	    if(!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id))
	    {
			die("ACCESS_DENIED");
		}



		$layout = JFactory::getApplication()->input->getString('layout', null);
		switch  ($layout)
		{
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
			case 'showrights':
				$this->_loadRightsForm();
				break;
			case 'showgrouprights':
				$this->_loadGroupRights();
				break;
			default :
				@EmundusHelperFiles::clear();
			    $m_users = new EmundusModelUsers();
			    $actions = $m_users->getActions("19,20,21,22,23,24,25,26");

			    $acts = array('user' => array(), 'group' => array());
			    if(!empty($actions))
			    {
				    foreach ($actions as $key => $action)
				    {
					    if(preg_match('/.*_user/', $action['name']) === 1 )
					    {
						    $acts['user'][] = $action;
					    }
					    elseif(preg_match('/.*_group/', $action['name']) === 1)
					    {
						    $acts['group'][] = $action;
					    }
					    else
					    {
						    if($action['name'] != 'user')
						    {
							    $acts['other'][] = $action;
						    }
					    }

				    }
			    }
			    $this->assignRef('actions', $acts);
			 break;
		}

		$onSubmitForm = EmundusHelperJavascript::onSubmitForm();
		$this->assignRef('onSubmitForm', $onSubmitForm);

        $this->assignRef('itemId', @JFactory::getApplication()->input->getInt('Itemid', null));


		parent::display($tpl);
    }
}
