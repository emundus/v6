<?php
/**
 * Users Model for eMundus Component
 *
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class EmundusModelGroups extends JModelList
{
	var $_total = null;
	var $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		global $option;

		$mainframe = JFactory::getApplication();

		// Get pagination request variables
		$limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'lastname', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'asc', 'word');

		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}


	function _buildContentOrderBy()
	{
		global $option;

		$mainframe = JFactory::getApplication();

		$orderby          = '';
		$filter_order     = $this->getState('filter_order');
		$filter_order_Dir = $this->getState('filter_order_Dir');

		$can_be_ordering = array('user', 'id', 'lastname', 'nationality', 'time_date', 'profile');
		/* Error handling is never a bad thing*/
		if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)) {
			$orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
		}

		return $orderby;
	}

	function getCampaign()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
		$db->setQuery($query);
		$syear = $db->loadRow();

		return $syear[0];
	}

	function getProfileAcces($user)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id=' . $user;
		$db->setQuery($query);
		$profiles = $db->loadAssocList();

		return $profiles;
	}

	function _buildQuery()
	{
		$gid           = JRequest::getVar('groups', null, 'POST', 'none', 0);
		$profile       = JRequest::getVar('profile', null, 'POST', 'none', 0);
		$uid           = JRequest::getVar('user', null, 'POST', 'none', 0);
		$quick_search  = JRequest::getVar('s', null, 'POST', 'none', 0);
		$search        = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
		$schoolyears   = JRequest::getVar('schoolyears', null, 'POST', 'none', 0);

		$db = JFactory::getDbo();

		// Starting a session.
		$session           = JFactory::getSession();
		$s_elements        = $session->get('s_elements');
		$s_elements_values = $session->get('s_elements_values');
		if (empty($schoolyears) && $session->has('schoolyears')) $schoolyears = $session->get('schoolyears');

		if (count($search) == 0) {
			$search        = $s_elements;
			$search_values = $s_elements_values;
		}
		$user  = JFactory::getUser();
		$query = 'SELECT ed.user, ed.time_date, ed.validated,
					eu.firstname, eu.lastname, eu.profile, eu.schoolyear,
					u.id, u.name, u.email, u.username, u.usertype, u.registerDate, u.block,
					epd.nationality, epd.gender
					FROM #__emundus_declaration ed
					LEFT JOIN #__emundus_users AS eu ON ed.user = eu.user_id
					LEFT JOIN #__emundus_personal_detail AS epd ON ed.user = epd.user
					LEFT JOIN #__users AS u ON ed.user = u.id ';
		if (isset($gid) && !empty($gid) || (isset($uid) && !empty($uid)))
			$query .= 'LEFT JOIN #__emundus_groups_eval AS ege ON ege.applicant_id = epd.user ';

		if (!empty($search)) {
			$i = 0;
			foreach ($search as $s) {
				$tab = explode('.', $s);
				//die(print_r($tab));
				if (count($tab) > 1) {
					$query .= 'LEFT JOIN ' . $tab[0] . ' AS j' . $i . ' ON j' . $i . '.user=ed.user ';
					$i++;
				}
			}

		}

		$query .= 'WHERE ed.validated=1';
		$and   = true;
		if (empty($schoolyears)) $query .= ' AND schoolyear like "%' . $this->getCampaign() . '%"';
		if (!empty($profile))
			$query .= ' AND eu.user_id IN (' . implode(',', $this->getApplicantsByProfile($profile)) . ')';

		$no_filter = array("Super Users", "Administrator");
		if (!in_array($user->usertype, $no_filter))
			$query .= ' AND eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id in (' . implode(',', $this->getProfileAcces($user->id)) . ')) ';

		if (!empty($search)) {
			$i = 0;
			foreach ($search as $s) {
				$tab = explode('.', $s);
				if (count($tab) > 1) {
					$query .= ' AND ';
					$query .= 'j' . $i . '.' . $tab[1] . ' like "%' . $search_values[$i] . '%"';
					$i++;
				}
			}

		}
		if (isset($quick_search) && !empty($quick_search)) {
			if ($and) $query .= ' AND ';
			else {
				$and   = true;
				$query .= 'WHERE ';
			}
			if (is_numeric($quick_search))
				$query .= 'u.id = ' . $quick_search . ' ';
			else
				$query .= '(eu.lastname LIKE ' . $db->Quote('%' . $quick_search . '%') . '
							OR eu.firstname LIKE ' . $db->Quote('%' . $quick_search . '%') . '
							OR u.email LIKE ' . $db->Quote('%' . $quick_search . '%') . '
							OR u.username LIKE ' . $db->Quote('%' . $quick_search . '%');
		}

		if (isset($gid) && !empty($gid)) {
			if ($and) $query .= ' AND ';
			else {
				$and   = true;
				$query .= 'WHERE ';
			}
			$query .= 'ege.group_id=' . $db->Quote($gid) . ' OR ege.user_id IN (select user_id FROM #__emundus_groups WHERE group_id=' . $db->Quote($gid) . ')';
		}
		if (isset($uid) && !empty($uid)) {
			if ($and) $query .= ' AND ';
			else {
				$and   = true;
				$query .= 'WHERE ';
			}
			$query .= '(ege.user_id=' . $db->Quote($uid) . ' OR ege.group_id IN (select e.group_id FROM #__emundus_groups e WHERE e.user_id=' . $db->Quote($uid) . '))';
		}
		if (isset($schoolyears) && !empty($schoolyears)) {
			if ($and) $query .= ' AND ';
			else {
				$and   = true;
				$query .= 'WHERE ';
			}
			$query .= 'eu.schoolyear="' . $db->Quote($schoolyears) . '"';
		}

		//die($query);

		return $query;
	}

	function getUsers()
	{
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();

		return $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	}


	function getProfiles()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft
		FROM #__emundus_setup_profiles esp
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
		ORDER BY caag.lft, esp.label';
		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	function getProfilesByIDs($ids)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft
		FROM #__emundus_setup_profiles esp
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
		WHERE esp.id IN (' . implode(',', $ids) . ')
		ORDER BY caag.lft, esp.label';
		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	function getAuthorProfiles()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, esp.evaluation_start, esp.evaluation_end, caag.lft
		FROM #__emundus_setup_profiles esp
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
		WHERE esp.acl_aro_groups=19';
		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	function getEvaluators()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT u.id, u.name
		FROM #__users u, #__emundus_users_profiles eup , #__emundus_setup_profiles esp
		WHERE u.id=eup.user_id AND esp.id=eup.profile_id AND esp.is_evaluator=1';
		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	function getApplicantsProfiles()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label FROM #__emundus_setup_profiles esp WHERE esp.published=1 ORDER BY esp.label';
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	function getApplicantsByProfile($profile)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT eup.user_id FROM #__emundus_users_profiles eup WHERE eup.profile_id=' . $profile;
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	function getGroups()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esg.id, esg.label
		FROM #__emundus_setup_groups esg
		WHERE esg.published=1
		ORDER BY esg.label';
		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	function getGroupsByCourse($course)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esg.id, esg.label
		FROM #__emundus_setup_groups esg
		LEFT JOIN #__emundus_setup_groups_repeat_course esgrc ON esgrc.parent_id=esg.id
		WHERE esg.published=1 AND esgrc.course=' . $db->Quote($course) . '
		ORDER BY esg.label';
		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	function getGroupsIdByCourse($course)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esg.id
		FROM #__emundus_setup_groups esg
		LEFT JOIN #__emundus_setup_groups_repeat_course esgrc ON esgrc.parent_id=esg.id
		WHERE esg.published=1 AND esgrc.course=' . $db->Quote($course) . '
		ORDER BY esg.label';
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	function getGroupsEval()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT ege.id, ege.applicant_id, ege.user_id, ege.group_id
		FROM #__emundus_groups_eval ege';
		$db->setQuery($query);

		return $db->loadObjectList('applicant_id');
	}

	function getUsersGroups()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT eg.user_id, eg.group_id
		FROM #__emundus_groups eg';
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	function getUsersByGroup($gid)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT eg.user_id, eg.group_id
		FROM #__emundus_groups eg
		WHERE eg.group_id=' . $gid;
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	function getUsersByGroups($gids)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT eg.user_id, eg.group_id
		FROM #__emundus_groups eg
		WHERE eg.group_id IN (' . implode(",", $gids) . ')';
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	function affectEvaluatorsGroups($groups, $aid)
	{
		$db = JFactory::getDBO();
		foreach ($groups as $group) {
			$query = "INSERT INTO #__emundus_groups_eval (applicant_id, group_id) VALUES (" . $aid . ", " . $group . ")";

			$db->setQuery($query);
			try {
				$db->execute();
			}
			catch (Exception $e) {
				// catch any database errors.
			}
		}

	}

	function getAuthorUsers()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT u.id, u.gid, u.name
		FROM #__users u
		WHERE u.gid=19';
		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	function getMobility()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esm.id, esm.label, esm.value
		FROM #__emundus_setup_mobility esm
		ORDER BY ordering';
		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	function getElements()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT element.id, element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin,
				 groupe.label AS group_label, INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated,
				 tab.db_table_name AS table_name, tab.label AS table_label
			FROM jos_fabrik_elements element
				 INNER JOIN jos_fabrik_groups AS groupe ON element.group_id = groupe.id
				 INNER JOIN jos_fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				 INNER JOIN jos_fabrik_lists AS tab ON tab.form_id = formgroup.form_id
				 INNER JOIN jos_menu AS menu ON tab.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 4), "&", 1)
				 INNER JOIN jos_emundus_setup_profiles AS profile ON profile.menutype = menu.menutype
			WHERE tab.published = 1 AND profile.id =9 AND tab.created_by_alias = "form" AND element.published=1 AND element.hidden=0 AND element.label!=" " AND element.label!=""
			ORDER BY menu.ordering, formgroup.ordering, element.ordering';
		$db->setQuery($query);

		//die(print_r($db->loadObjectList('id')));
		return $db->loadObjectList('id');
	}

	function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query        = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	function getSchoolyears()
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT DISTINCT(schoolyear) as schoolyear
		FROM #__emundus_users
		WHERE schoolyear is not null AND schoolyear != ""
		ORDER BY schoolyear';
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * @param   array  $programmes  the programme newly added that should be affected to groups
	 *
	 * @return boolean
	 * Add new groups
	 */
	public function addGroupsByProgrammes($programmes)
	{

		if (count($programmes) > 0) {

			$db = $this->getDbo();

			try {
				foreach ($programmes as $v) {
					// Check if a group is already declared for the organisation
					$query = 'SELECT * FROM `#__emundus_setup_groups`
	        				WHERE  label LIKE "%' . $v['organisation'] . '%"
	        					OR label LIKE "%' . $v['organisation_code'] . '%"
	        					OR description LIKE "%' . $v['organisation_code'] . '%"';
					$db->setQuery($query);
					$groups = $db->loadObjectList();

					if (count($groups) > 0) {
						foreach ($groups as $group) {
							$query = 'DELETE FROM `#__emundus_setup_groups_repeat_course` WHERE parent_id=' . $group->id . ' AND course LIKE ' . $db->Quote($v['code']);
							$db->setQuery($query);
							$db->execute();

							$query = 'INSERT INTO `#__emundus_setup_groups_repeat_course` (`parent_id`, `course`) VALUES (' . $group->id . ', ' . $db->Quote($v['code']) . ')';
							$db->setQuery($query);
							$db->execute();
						}
					}
					else {
						$query = 'INSERT INTO `#__emundus_setup_groups` (`label`, `description`, `published`) VALUES (' . $db->Quote($v['organisation'] . ' [' . $v['organisation_code'] . ']') . ', ' . $db->Quote($v['organisation_code']) . ', 1)';
						$db->setQuery($query);
						$db->execute();
						$lastid = $db->insertid();

						$query = 'INSERT INTO `#__emundus_setup_groups_repeat_course` (`parent_id`, `course`) VALUES (' . $lastid . ', ' . $db->Quote($v['code']) . ')';
						$db->setQuery($query);
						$db->execute();

						// define default access right for group
						$params             = JComponentHelper::getParams('com_emundus');
						$default_actions    = $params->get('default_actions', 0);
						$actions_evaluators = json_decode($default_actions);

						$values = array();
						foreach ($actions_evaluators as $action) {
							$values[] = '(' . $lastid . ', "' . implode('","', (array) $action) . '")';
						}

						$query = 'INSERT INTO `#__emundus_acl` (`group_id`, `action_id`, `c`, `r`, `u`, `d`) VALUE ' . implode(',', $values);
						$db->setQuery($query);
						$db->execute();
					}

					// add for All access group
					$query = 'INSERT INTO `#__emundus_setup_groups_repeat_course` (`parent_id`, `course`) VALUES (1, ' . $db->Quote($v['code']) . ')';
					$db->setQuery($query);
					$db->execute();

				}
			}
			catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');

				return $e->getMessage();
			}

		}
		else {
			return JText::_('NO_GROUP_TO_ADD');
		}

		return true;
	}


	/**
	 * @param $group_ids
	 *
	 * @return array|bool
	 *
	 * @since version
	 */
	public function getFabrikGroupsAssignedToEmundusGroups($group_ids)
	{

		if (!is_array($group_ids)) {
			$group_ids = [$group_ids];
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$result = [];
		foreach ($group_ids as $group_id) {
			$query
				->clear()
				->select($db->quoteName('fabrik_group_link'))
				->from($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
				->where($db->quoteName('parent_id') . ' = ' . $db->quote($group_id));
			$db->setQuery($query);

			try {
				$f_groups = $db->loadColumn();

				// In the case of a group having no assigned Fabrik groups, it can get them all.
				if (empty($f_groups)) {
					return true;
				}

				$result = array_merge($result, $f_groups);
			}
			catch (Exception $e) {
				return false;
			}
		}

		if (empty($result)) {
			return true;
		}
		else {
			return array_keys(array_flip($result));
		}
	}
}
