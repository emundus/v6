<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 23/05/14
 * Time: 11:39
 * @package        Joomla
 * @subpackage    eMundus
 * @link        http://www.emundus.fr
 * @copyright    Copyright (C) 2008 - 2014 Décision Publique. All rights reserved.
 * @license        GNU/GPL
 * @author        Decision Publique - Yoan Durand
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
/*
if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    use PhpOffice\PhpWord\Exception\rootException;
}
*/
jimport('joomla.application.component.model');
require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS. 'helpers' . DS . 'files.php');
require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS. 'helpers' . DS . 'list.php');

/**
 * Class EmundusModelFiles
 */
class EmundusModelFiles extends JModelLegacy
{
	/**
	 * @var null
	 */
	private $_total = null;
	/**
	 * @var null
	 */
	private $_pagination = null;
	/**
	 * @var array
	 */
	private $_applicants = array();
	/**
	 * @var array
	 */
	private $subquery = array();
	/**
	 * @var array
	 */
	private $_elements_default;
	/**
	 * @var array
	 */
	private $_elements;

    public $fnum_assoc;
    public $code;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe = JFactory::getApplication();

		// Get current menu parameters
		$current_user = JFactory::getUser();
		$menu = @JSite::getMenu();
		$current_menu = $menu->getActive();

		/*
		** @TODO : gestion du cas Itemid absent à prendre en charge dans la vue
		*/

		if (empty($current_menu))
			return false;
		$menu_params = $menu->getParams($current_menu->id);

		//$em_filters_names = explode(',', $menu_params->get('em_filters_names'));
		//$em_filters_values = explode(',', $menu_params->get('em_filters_values'));
		$em_other_columns = explode(',', $menu_params->get('em_other_columns'));

		$session = JFactory::getSession();
		if (!$session->has('filter_order'))
		{
			$session->set('filter_order', 'c.fnum');
			$session->set('filter_order_Dir', 'desc');
		}

		if(!$session->has('limit'))
		{
			$limit = $mainframe->getCfg('list_limit');
			$limitstart = 0;
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$session->set('limit', $limit);
			$session->set('limitstart', $limitstart);
		}
		else
		{
			$limit = intval($session->get('limit'));
			$limitstart = intval($session->get('limitstart'));
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$session->set('limit', $limit);
			$session->set('limitstart', $limitstart);
		}

		$col_elt = $this->getState('elements');
		$col_other = $this->getState('elements_other');

		$this->elements_id = $menu_params->get('em_elements_id');
		if ($session->has('adv_cols'))
		{
			$adv = $session->get('adv_cols');
			if (!empty($adv))
			{
				$this->elements_id .= ','.implode(',', $adv);
			}

		}
		$this->elements_values = explode(',', $menu_params->get('em_elements_values'));

		$this->_elements_default = array();
		$this->_elements = @EmundusHelperFiles::getElementsName($this->elements_id);

		if (!empty($this->_elements))
		{
			foreach ($this->_elements as $def_elmt)
			{
				$group_params = json_decode($def_elmt->group_attribs);

				if($def_elmt->element_plugin == 'date') {
					if (@$group_params->repeat_group_button == 1) {
						$this->_elements_default[] = '(
														SELECT  GROUP_CONCAT(DATE_FORMAT('.$def_elmt->table_join.'.'.$def_elmt->element_name.', "%d/%m/%Y %H:%i:%m") SEPARATOR ", ")
														FROM '.$def_elmt->table_join.'
														WHERE '.$def_elmt->table_join.'.parent_id = '.$def_elmt->tab_name.'.id
													  ) AS `'.$def_elmt->table_join.'___' . $def_elmt->element_name.'`';
					} else
						$this->_elements_default[] = $def_elmt->tab_name . '.' . $def_elmt->element_name.' AS `'.$def_elmt->tab_name . '___' . $def_elmt->element_name.'`';
				}
				elseif ($def_elmt->element_plugin == 'databasejoin') {
					$attribs = json_decode($def_elmt->element_attribs);
					$column = (!empty($attribs->join_val_column_concat) && $attribs->join_val_column_concat!='')?'CONCAT('.$attribs->join_val_column_concat.')':$attribs->join_val_column;

					if (@$group_params->repeat_group_button == 1) {
						$query = '(
									select GROUP_CONCAT('.$column.' SEPARATOR ", ") 
									from '.$attribs->join_db_name.' 
									where '.$attribs->join_db_name.'.'.$attribs->join_key_column.' IN 
										( select '.$def_elmt->table_join.'.' . $def_elmt->element_name.'
										  from '.$def_elmt->table_join.'
										  where '.$def_elmt->table_join.'.parent_id='.$def_elmt->tab_name.'.id
										)
								  ) AS `'.$def_elmt->tab_name . '___' . $def_elmt->element_name.'`';
					} else
						$query = '(select DISTINCT '.$column.' from '.$attribs->join_db_name.' where `'.$attribs->join_db_name.'`.`'.$attribs->join_key_column.'`=`'.$def_elmt->tab_name . '`.`' . $def_elmt->element_name.'`) AS `'.$def_elmt->tab_name . '___' . $def_elmt->element_name.'`';

					$this->_elements_default[] = $query;
					//$this->_elements_default[] = ' (SELECT esc.label FROM jos_emundus_setup_campaigns AS esc WHERE esc.id = jos_emundus_campaign_candidature.campaign_id) as `jos_emundus_campaign_candidature.campaign_id` ';
				}
                elseif($def_elmt->element_plugin == 'cascadingdropdown') {
                    $attribs = json_decode($def_elmt->element_attribs);
                    $cascadingdropdown_id = $attribs->cascadingdropdown_id;
                    $r1 = explode('___', $cascadingdropdown_id);
                    $cascadingdropdown_label = $attribs->cascadingdropdown_label;
                    $r2 = explode('___', $cascadingdropdown_label);
                    $select = !empty($attribs->cascadingdropdown_label_concat)?"CONCAT(".$attribs->cascadingdropdown_label_concat.")":$r2[1];
                    $from = $r2[0];
                    $where = $r1[1].'='.$def_elmt->element_name;
                    $query = "(SELECT ".$select." FROM ".$from." WHERE ".$where.") AS `".$def_elmt->tab_name . "___" . $def_elmt->element_name."`";
                    $query = preg_replace('#{thistable}#', $from, $query);
                    $query = preg_replace('#{my->id}#', $current_user->id, $query);
                    $this->_elements_default[] = $query;
                }
				elseif ($def_elmt->element_plugin == 'dropdown' || $def_elmt->element_plugin == 'radiobutton') {

                    if (@$group_params->repeat_group_button == 1) {
                        $this->_elements_default[] = '(
                                    SELECT  GROUP_CONCAT('.$def_elmt->table_join.'.' . $def_elmt->element_name.' SEPARATOR ", ")
                                    FROM '.$def_elmt->table_join.'
                                    WHERE '.$def_elmt->table_join.'.parent_id = '.$def_elmt->tab_name.'.id
                                  ) AS `'.$def_elmt->table_join.'___' . $def_elmt->element_name.'`';
                    } else {
                        $element_attribs = json_decode($def_elmt->element_attribs);
                        $select = $def_elmt->tab_name . '.' . $def_elmt->element_name;
                        foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                            $select = 'REPLACE(' . $select . ', "' . $value . '", "' .
                                $element_attribs->sub_options->sub_labels[$key] . '")';
                        }
                        $this->_elements_default[] = $select . ' AS ' . $def_elmt->tab_name . '___' . $def_elmt->element_name;
                    }
				}
				else {
					if (@$group_params->repeat_group_button == 1) {
						$this->_elements_default[] = '(
														SELECT  GROUP_CONCAT('.$def_elmt->table_join.'.' . $def_elmt->element_name.'  SEPARATOR ", ")
														FROM '.$def_elmt->table_join.'
														WHERE '.$def_elmt->table_join.'.parent_id = '.$def_elmt->tab_name.'.id
													  ) AS `'.$def_elmt->table_join.'___' . $def_elmt->element_name.'`';
					} else
						$this->_elements_default[] = $def_elmt->tab_name . '.' . $def_elmt->element_name.' AS '.$def_elmt->tab_name . '___' . $def_elmt->element_name;
				}

				//$this->_elements_default_name[] = $def_elmt->tab_name . '.' . $def_elmt->element_name.' AS '.$def_elmt->tab_name . '___' . $def_elmt->element_name;
			}
		}
		if(in_array('overall', $em_other_columns))
			$this->_elements_default[] = '(SELECT AVG(ee.overall) FROM jos_emundus_evaluations as ee WHERE ee.fnum like c.fnum GROUP BY ee.fnum) as overall ';

		if (count($col_elt) == 0)
			$col_elt = array();
		if (count($col_other) == 0)
			$col_other = array();
		if (count(@$this->_elements_default_name) == 0)
			$this->_elements_default_name = array();

		$this->col = array_merge($col_elt, $col_other, $this->_elements_default_name);

		$elements_names = '"' . implode('", "', $this->col) . '"';
		$result = @EmundusHelperList::getElementsDetails($elements_names);

		$result = @EmundusHelperFiles::insertValuesInQueryResult($result, array("sub_values", "sub_labels"));

		$this->details = new stdClass();
		foreach ($result as $res)
		{
			$this->details->{$res->tab_name . '___' . $res->element_name} = array('element_id' => $res->element_id,
			                                                                     'plugin' => $res->element_plugin,
			                                                                     'attribs' => $res->params,
			                                                                     'sub_values' => $res->sub_values,
			                                                                     'sub_labels' => $res->sub_labels,
			                                                                     'group_by' => $res->tab_group_by);
		}
	}

	/**
	 * @return array
	 */
	public function getElementsVar()
	{
		return $this->_elements;
	}

	/**
	 * @return string
	 */
	public function _buildContentOrderBy()
	{
		$filter_order = JFactory::getSession()->get('filter_order');
		$filter_order_Dir = JFactory::getSession()->get('filter_order_Dir');

		$can_be_ordering = array();
		if(count($this->_elements) > 0) {
			foreach ($this->_elements as $element) {
				$can_be_ordering[] = $element->tab_name.'___'.$element->element_name;
				$can_be_ordering[] = $element->tab_name.'.'.$element->element_name;
			}
		}

		$can_be_ordering[] = 'c.fnum';
		$can_be_ordering[] = 'c.status';
		$can_be_ordering[] = 'u.name';
		$can_be_ordering[] = 'eta.id_tag';
		$can_be_ordering[] = 'overall';

		if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering))
		{
			return ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
		}

		return '';
	}

	/**
	 * @param array $multi_array
	 * @param $sort_key
	 * @param int $sort
	 * @return array|int
	 */
	public function multi_array_sort($multi_array = array(), $sort_key, $sort = SORT_ASC)
	{
		if (is_array($multi_array))
		{
			foreach ($multi_array as $key => $row_array)
			{
				if (is_array($row_array))
				{
					@$key_array[$key] = $row_array[$sort_key];
				}
				else
				{
					return -1;
				}
			}
		} else {
			return -1;
		}
		if (!empty($key_array))
			array_multisort($key_array, $sort, $multi_array);
		return $multi_array;
	}

	/**
	 * @return mixed
	 */
	public function getCampaign()
	{
		return @EmundusHelperFiles::getCampaign();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getCurrentCampaign()
	{
		return @EmundusHelperFiles::getCurrentCampaign();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getCurrentCampaignsID()
	{
		return @EmundusHelperFiles::getCurrentCampaignsID();
	}

	/**
	 * @param $user
	 * @return mixed
	 */
	public function getProfileAcces($user)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id=' . $user;
		$db->setQuery($query);
		$profiles = $db->loadResultArray();

		return $profiles;
	}

	/**
	 * @param $tab
	 * @param $elem
	 * @return array
	 */
	public function setSubQuery($tab, $elem)
	{
		$search = JRequest::getVar('elements', NULL, 'POST', 'array', 0);
		$search_values = JRequest::getVar('elements_values', NULL, 'POST', 'array', 0);
		$search_other = JRequest::getVar('elements_other', NULL, 'POST', 'array', 0);
		$search_values_other = JRequest::getVar('elements_values_other', NULL, 'POST', 'array', 0);

		$db = JFactory::getDBO();

		$query = 'SELECT DISTINCT(#__emundus_users.user_id), ' . $tab . '.' . $elem . ' AS ' . $tab . '___' . $elem;
		$query .= '	FROM #__emundus_campaign_candidature
					LEFT JOIN #__emundus_users ON #__emundus_users.user_id=#__emundus_campaign_candidature.applicant_id
					LEFT JOIN #__users ON #__users.id=#__emundus_users.user_id';

		// subquery JOINS
		$joined = array('jos_emundus_users');
		$this->setJoins($search, $query, $joined);
		$this->setJoins($search_other, $query, $joined);
		$this->setJoins($this->_elements_default, $query, $joined);

		// subquery WHERE
		$query .= ' WHERE #__emundus_campaign_candidature.submitted=1 AND ' .
		          $this->details->{$tab . '___' . $elem}['group_by'] . '=#__users.id';
		$query = @EmundusHelperFiles::setWhere($search, $search_values, $query);
		$query = @EmundusHelperFiles::setWhere($search_other, $search_values_other, $query);
		$query = @EmundusHelperFiles::setWhere($this->_elements_default, $this->elements_values, $query);

		$db->setQuery($query);
		$obj = $db->loadObjectList();
		$list = array();
		$tmp = '';
		foreach ($obj as $unit) {
			if ($tmp != $unit->user_id)
				$list[$unit->user_id] =
					@EmundusHelperList::getBoxValue($this->details->{$tab . '___' . $elem}, $unit->{$tab . '___' . $elem},
						$elem);
			else
				$list[$unit->user_id] .= ',' . @EmundusHelperList::getBoxValue($this->details->{$tab . '___' . $elem},
						$unit->{$tab . '___' . $elem}, $elem);
			$tmp = $unit->user_id;
		}
		return $list;
	}

	/**
	 * @param $search
	 * @return array|string
	 */
	public function setSelect($search)
	{
		$cols = array();
		if (!empty($search)) {
			asort($search);
			$i = 0;
			$old_table = '';

			foreach ($search as $c) {
				if (!empty($c)) {
					$tab = explode('.', $c);
					if ($tab[0] == 'jos_emundus_training') {
						$cols[] = ' search_' . $tab[0] . '.label as ' . $tab[1] . ' ';
					} else {
						if ($this->details->{$tab[0] . '___' . $tab[1]}['group_by'])
							$this->subquery[$tab[0] . '___' . $tab[1]] = $this->setSubQuery($tab[0], $tab[1]);
						else $cols[] = $c . ' AS ' . $tab[0] . '___' . $tab[1];
					}
				}
				$i++;
			}
			if (count($cols > 0) && !empty($cols))
				$cols = implode(', ', $cols);
		}
		return $cols;
	}

	/**
	 * @param $tab
	 * @param $joined
	 * @return bool
	 */
	public function isJoined($tab, $joined)
	{
		foreach ($joined as $j)
			if ($tab == $j) return true;
		return false;
	}

	/**
	 * @param $search
	 * @param $query
	 * @param $joined
	 * @return array
	 */
	public function setJoins($search, &$query, &$joined)
	{
		$tables_list = array();
		if (!empty($search)) {
			$old_table = '';
			$i = 0;
			foreach ($search as $s) {
				$tab = explode('.', $s);
				if (count($tab) > 1) {
					if ($tab[0] != $old_table && !$this->isJoined($tab[0], $joined)) {
						if ($tab[0] == 'jos_emundus_groups_eval' || $tab[0] == 'jos_emundus_comments')
							$query .= ' LEFT JOIN ' . $tab[0] . ' ON ' . $tab[0] . '.applicant_id=#__users.id ';
						elseif ($tab[0] == 'jos_emundus_evaluations' || $tab[0] == 'jos_emundus_final_grade' ||
						        $tab[0] == 'jos_emundus_academic_transcript'
						        || $tab[0] == 'jos_emundus_bank' || $tab[0] == 'jos_emundus_files_request' ||
						        $tab[0] == 'jos_emundus_mobility'
						)
							$query .= ' LEFT JOIN ' . $tab[0] . ' ON ' . $tab[0] . '.student_id=#__users.id ';
						elseif ($tab[0] == "jos_emundus_training")
							$query .=
								' LEFT JOIN #__emundus_setup_teaching_unity AS search_' . $tab[0] . ' ON search_' .
								$tab[0] . '.code=#__emundus_setup_campaigns.training ';
						else
							$query .= ' LEFT JOIN ' . $tab[0] . ' ON ' . $tab[0] . '.user=#__users.id ';
						$joined[] = $tab[0];
					}
					$old_table = $tab[0];
				}
				$i++;
			}
		}
		return $tables_list;
	}

	/**
	 * @param $tables_list
	 * @param $tables_list_other
	 * @param $tables_list_default
	 * @return string
	 */
	public function _buildSelect(&$tables_list, &$tables_list_other, &$tables_list_default)
	{
		$current_user = JFactory::getUser();
		$search = $this->getState('elements');
		$search_other = $this->getState('elements_other');
		$schoolyears = $this->getState('schoolyear');
		$programmes = $this->getState('programme');
		$gid = $this->getState('groups');
		$uid = $this->getState('user');
		$miss_doc = $this->getState('missing_doc');
		$validate_application = $this->getState('validate');

		$menu = JSite::getMenu();
		$current_menu = $menu->getActive();
		$menu_params = $menu->getParams($current_menu->id);
		$this->validate_details = @EmundusHelperList::getElementsDetailsByID($menu_params->get('em_validate_id'));
		$col_validate = "";
		foreach ($this->validate_details as $vd) {
			$col_validate .= $vd->tab_name . '___' . $vd->element_name . ',';
		}
		$col_validate = substr($col_validate, 0, strlen($col_validate) - 1);

		$cols = $this->setSelect($search);
		$cols_other = $this->setSelect($search_other);
		$cols_default = $this->setSelect($this->_elements_default);

		$joined = array('jos_emundus_users', 'jos_users', 'jos_emundus_setup_profiles', 'jos_emundus_final_grade',
		                'jos_emundus_declaration');

		$query = 'SELECT #__emundus_users.user_id, #__emundus_users.user_id as user, #__emundus_users.user_id as id, #__emundus_users.lastname, #__emundus_users.firstname, #__users.name, #__users.registerDate, #__users.email, #__emundus_setup_profiles.id as profile, #__emundus_declaration.validated, #__emundus_campaign_candidature.date_submitted,
		#__emundus_setup_campaigns.year as schoolyear, #__emundus_setup_campaigns.label, #__emundus_campaign_candidature.date_submitted, #__emundus_campaign_candidature.campaign_id';
		if (!empty($cols)) $query .= ', ' . $cols;
		if (!empty($cols_other)) $query .= ', ' . $cols_other;
		if (!empty($cols_default)) $query .= ', ' . $cols_default;
		if (!empty($col_validate)) $query .= ', ' . $col_validate;
		$query .= '	FROM #__emundus_campaign_candidature
					LEFT JOIN #__emundus_declaration ON #__emundus_declaration.user =  #__emundus_campaign_candidature.applicant_id
					LEFT JOIN #__emundus_users ON #__emundus_declaration.user=#__emundus_users.user_id
					LEFT JOIN #__emundus_setup_campaigns ON #__emundus_setup_campaigns.id=#__emundus_campaign_candidature.campaign_id
					LEFT JOIN #__users ON #__users.id=#__emundus_users.user_id
					LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
					LEFT JOIN #__emundus_final_grade ON #__emundus_final_grade.student_id=#__emundus_users.user_id';

		$this->setJoins($search, $query, $joined);
		$this->setJoins($search_other, $query, $joined);
		$this->setJoins($this->_elements_default, $query, $joined);

		if (((isset($gid) && !empty($gid)) || (isset($uid) && !empty($uid))) &&
		    !$this->isJoined('jos_emundus_groups_eval', $joined)
		)
			$query .= ' LEFT JOIN #__emundus_groups_eval ON #__emundus_groups_eval.applicant_id=#__users.id ';

		if (!empty($miss_doc) && !$this->isJoined('jos_emundus_uploads', $joined))
			$query .= ' LEFT JOIN #__emundus_uploads ON #__emundus_uploads.user_id=#__users.id';

		if (!empty($validate_application) && !$this->isJoined('jos_emundus_declaration', $joined))
			$query .= ' LEFT JOIN #__emundus_declaration ON #__emundus_declaration.user=#__users.id';

		$query .= ' WHERE #__emundus_campaign_candidature.submitted = 1 AND #__users.block = 0 ';
		if (empty($schoolyears))
			$query .= ' AND #__emundus_campaign_candidature.year IN ("' . implode('","', $this->getCurrentCampaign()) . '")';

		if (!empty($programmes) && isset($programmes) && $programmes[0] != "%")
			$query .= ' AND #__emundus_setup_campaigns.training IN ("' . implode('","', $programmes) . '")';

		if (!EmundusHelperAccess::isAdministrator($current_user->id) &&
		    !EmundusHelperAccess::isCoordinator($current_user->id)
		) {
			$pa = EmundusHelperAccess::getProfileAccess($current_user->id);
			$query .= ' AND (#__emundus_users.user_id IN (
								SELECT user_id
								FROM #__emundus_users_profiles
								WHERE profile_id in (' . implode(',', $pa) . ')) OR #__emundus_users.user_id IN (
									SELECT user_id
									FROM #__emundus_users
									WHERE profile in (' . implode(',', $pa) . '))
							) ';
		}
		return $query;
	}

	/**
	 * @description : Generate values for array of data for all applicants
	 * @param    array $search filters elements
	 * @param    array $eval_list reference of result list
	 * @param    array $head_val header name
	 * @param    object $applicant array of applicants indexed by database column
	 **/
	public function setEvalList($search, &$eval_list, $head_val, $applicant)
	{
		//print_r($applicant); die();
		if (!empty($search)) {
			foreach ($search as $c) {
				if (!empty($c)) {
					$name = explode('.', $c);
					if (!in_array($name[0] . '___' . $name[1], $head_val)) {
						$print_val = '';
						if ($this->details->{$name[0] . '___' . $name[1]}['group_by']
						    && array_key_exists($name[0] . '___' . $name[1], $this->subquery)
						    && array_key_exists($applicant->user_id, $this->subquery[$name[0] . '___' . $name[1]])
						) {
							$eval_list[$name[0] . '___' . $name[1]] = @EmundusHelperList::createHtmlList(explode(",",
								$this->subquery[$name[0] . '___' . $name[1]][$applicant->user_id]));
						} elseif ($name[0] == 'jos_emundus_training') {
							$eval_list[$name[1]] = $applicant->{$name[1]};
						} elseif (!$this->details->{$name[0] . '___' . $name[1]}['group_by']) {
							$eval_list[$name[0] . '___' . $name[1]] =
								@EmundusHelperList::getBoxValue($this->details->{$name[0] . '___' . $name[1]},
									$applicant->{$name[0] . '___' . $name[1]}, $name[1]);
						} else
							$eval_list[$name[0] . '___' . $name[1]] = $applicant->{$name[0] . '___' . $name[1]};
					}
				}
			}
		}
	}


	/**
	 * @param array $tableAlias
	 * @return array
	 */
	private function _buildWhere($tableAlias = array())
	{
		$params = JFactory::getSession()->get('filt_params');
        $db = JFactory::getDBO();

        if(!isset($params['published']) || empty($params['published']))
            $params['published'] = 1;

		$query = array('q' => '', 'join' => '');
		if(!empty($params))
		{
			foreach($params as $key => $value)
			{

				switch ($key)
				{
					case 'elements':
						if(!empty($value))
						{
							foreach ($value as $k => $v)
							{
								$tab = explode('.', $k);
								
								if (count($tab)>1)
								{
									if(!empty($v))
									{
										if($tab[0] == 'jos_emundus_training')
										{
											$query['q'] .= ' AND ';
											$query['q'] .= ' search_'.$tab[0].'.id like "%' . $v . '%"';
										}
										else
										{
											$query['q'] .= ' AND ';
											// Check if it is a join table
											$sql = 'SELECT join_from_table FROM #__fabrik_joins WHERE table_join like '.$db->Quote($tab[0]);
											$db->setQuery($sql);
											$join_from_table = $db->loadResult();
											if (!empty($join_from_table)) {
												$table = $join_from_table;
												$table_join = $tab[0];

												$query['q'] .= $table_join.'.'.$tab[1].' like "%' . $v . '%"';

												if(!isset($query[$table]))
												{
													$query[$table] = true;
													if (!array_key_exists($table, $tableAlias) && !in_array($table, $tableAlias))
														$query['join'] .= ' left join '.$table.' on ' .$table.'.fnum like c.fnum ';
												}
												if(!isset($query[$table_join]))
												{
													$query[$table_join] = true;
													if (!array_key_exists($table_join, $tableAlias) && !in_array($table_join, $tableAlias))
														$query['join'] .= ' left join '.$table_join.' on ' .$table.'.id='.$table_join.'.parent_id';
												}
											} 
											else {
												$query['q'] .= $tab[0].'.'.$tab[1].' like "%' . $v . '%"';

												if(!isset($query[$tab[0]]))
												{
													$query[$tab[0]] = true;
													if (!array_key_exists($tab[0], $tableAlias) && !in_array($tab[0], $tableAlias))
														$query['join'] .= ' left join '.$tab[0].' on ' .$tab[0].'.fnum like c.fnum ';
												}
											}
										}
									}
									
								}
								
							}
						}
						break;
					case 'elements_other':
						if(!empty($value))
						{
							if(!empty($value))
							{
								foreach ($value as $k => $v)
								{
									if(!empty($value))
									{
										if(!empty($v))
										{
											$tab = explode('.', $k);
											if (count($tab)>1) {
												if($tab[0]=='jos_emundus_training')
												{
													$query['q'] .= ' AND ';
													$query['q'] .= ' search_'.$tab[0].'.id like "%' . $v . '%"';
												}
												else
												{
													$query['q'] .= ' AND ';
													$query['q'] .= $tab[0].'.'.$tab[1].' like "%' . $v . '%"';

													if(!isset($query[$tab[0]]))
													{
														$query[$tab[0]] = true;
														if (!array_key_exists($tab[0], $tableAlias))
															$query['join'] .= ' left join '.$tab[0].' on ' .$tab[0].'.fnum like c.fnum ';
													}
												}
											}
										}
									}
								}
							}
						}
						break;
					case 's':
						if (!empty($value))
						{
							$q = $this->_buildSearch($value, $tableAlias);
							$query['q'] .= ' and ' . $q['q'];
							$query['join'] .= $q['join'];
							if (isset($q['users']))
							{
								$query['users'] = true;
							}
							if (isset($q['em_user']))
							{
								$query['em_user'] = true;

							}
						}
						break;
					case 'finalgrade':
						if (!empty($value))
						{
							$query['q'] .= ' and fg.final_grade like "%' . $value . '%"';
							if(!isset($query['final_g']))
							{
								$query['final_g'] = true;
								if (!array_key_exists('jos_emundus_final_grade', $tableAlias))
									$query['join'] .=' left join #__emundus_final_grade as fg on fg.fnum like c.fnum ';
							}
						}
						break;
					case 'schoolyear':
						if (!empty($value))
						{
								if (($value[0] == "%") || empty($value[0]))
								$query['q'] .= '';
							else
							{
								$query['q'] .= ' and esc.year IN ("' . implode('","', $value) . '") ';
								/*if(!isset($query['campaign']))
								{
									$query['campaign'] = true;
									if (!array_key_exists('jos_emundus_setup_campaigns', $tableAlias))
										$query['join'] .= ' left join #__emundus_setup_campaigns as esc on esc.id = c.campaign_id ';
								}*/
							}
						}
						break;
					case 'programme':
						if(!empty($value))
						{
							if ($value[0] == "%" || empty($value[0]))
								$query['q'] .= ' ';
							else
							{
								$query['q'] .= ' and sp.code IN ("' . implode('","', $value) . '") ';
								/*if (!isset($query['campaign']))
								{
									$query['campaign'] = true;
									if (!in_array('jos_emundus_setup_campaigns', $tableAlias))
										$query['join'] .= ' left join #__emundus_setup_campaigns as esc on esc.id = c.campaign_id ';
								}*/
								/*if (!in_array('jos_emundus_setup_programmes', $tableAlias)) 
									$query['join'] .= ' left join #__emundus_setup_programmes as esp on esp.code like esc.training ';*/
							}

						}
						break;
					case 'campaign':
						if ($value)
						{
							if ($value[0] == "%" || empty($value[0]))
								$query['q'] .= ' ';
							else
							{
								$query['q'] .= ' and esc.id IN (' . implode(',', $value) . ') ';
								/*if(!isset($query['campaign']))
								{
									$query['campaign'] = true;
									if (!array_key_exists('jos_emundus_setup_campaigns', $tableAlias))
										$query['join'] .= ' left join #__emundus_setup_campaigns as esc on esc.id = c.campaign_id ';
								}*/
							}
						}
						break;
					case 'groups':
						if(!empty($value))
						{
							$query['q'] .= ' and  (ge.group_id=' . mysql_real_escape_string($value) . ' OR ge.user_id IN (select user_id FROM #__emundus_groups WHERE group_id=' .mysql_real_escape_string($value) . ')) ';

							if(!isset($query['group_eval']))
							{
								$query['group_eval'] = true;
								if (!array_key_exists('jos_emundus_groups_eval', $tableAlias))
									$query['join'] .= ' left join #__emundus_groups_eval as ge on ge.applicant_id = c.applicant_id and ge.campaign_id = c.campaign_id ';
							}

						}
						break;
					case 'user':
						if(!empty($value))
						{
							$query['q'] .= ' and (ge.user_id=' . mysql_real_escape_string($value) .
							          ' OR ge.group_id IN (select e.group_id FROM #__emundus_groups e WHERE e.user_id=' .
							          mysql_real_escape_string($value) . '))';
							if(!isset($query['group_eval']))
							{
								$query['group_eval'] = true;
								if (!array_key_exists('jos_emundus_groups_eval', $tableAlias))
									$query['join'] .= ' left join #__emundus_groups_eval as ge on ge.applicant_id = c.applicant_id and ge.campaign_id = c.campaign_id ';
							}
						}
						break;
					case 'profile':
						if(!empty($value) && @$value[0]!=0)
						{
							$query['q'] .= 'and (spro.id = ' . $value . ' OR fg.result_for = ' . $value . ' OR ue.user_id IN (select user_id from #__emundus_users_profiles where profile_id = ' . $value . ')) ';

							if(!isset($query['final_g']))
							{
								$query['final_g'] = true;
								if (!array_key_exists('jos_emundus_final_grade', $tableAlias))
									$query['join'] .=' left join #__emundus_final_grade as fg on fg.fnum like c.fnum ';
							}
							if(isset($query['em_user']))
							{
								$query['em_user'] = true;
								if (!array_key_exists('jos_emundus_users', $tableAlias))
									$query['join'] .= ' left join #__emundus_users as ue on ue.id = c.applicant_id ';
							}
							if (!array_key_exists('jos_emundus_setup_profiles', $tableAlias))
								$query['join'] .= ' left join #__emundus_setup_profiles as spro on spro.id = ue.profile ';
						}
						break;
					case 'missing_doc':
						if(!empty($value))
						{
							$query['q'] .=' and (' . $value . ' NOT IN (SELECT attachment_id FROM #__emundus_uploads eup WHERE #__emundus_uploads.user_id = u.id)) ';
							if (!array_key_exists('jos_emundus_uploads', $tableAlias))
								$query['join'] = ' left join #__emundus_uploads on #__emundus_uploads.user_id = c.applicant_id ';
						}
						break;
					case 'complete':
						if(!empty($value))
						{
							if ($value == 1)
								$query['q'] .= 'and #__users.id IN (SELECT user FROM #__emundus_declaration ed WHERE #__emundus_declaration.user = #__users.id) ';
							else
								$query['q'] .= 'and #__users.id NOT IN (SELECT user FROM #__emundus_declaration ed WHERE #__emundus_declaration.user = #__users.id) ';
						}
						break;
					case 'validate':
							if(!empty($value))
							{
								if ($value == 1)
									$query['q'] .= ' and #__emundus_declaration.validated = 1 ';
								else
									$query['q'] .= ' and #__emundus_declaration.validated = 0 ';
							}
						break;
					case 'status':
						if ($value)
						{
							if ( $value[0] == "%" || !isset($value[0]) )
								$query['q'] .= ' ';
							else
							{
								$query['q'] .= ' and c.status IN (' . implode(',', $value) . ') ';
							}
						}
						break;
                    case 'tag':
                        if ($value)
                        {
                            if ( $value[0] == "%" || !isset($value[0]) )
                                $query['q'] .= ' ';
                            else
                            {
                                $query['q'] .= ' and eta.id_tag IN (' . implode(',', $value) . ') ';
                            }
                        }
                        break;
                    case 'published':
                        if ($value == "-1") {
                            $query['q'] .= ' and c.published=-1 ';
                        } elseif ($value == 0) {
                            $query['q'] .= ' and c.published=0 ';
                        } else {
                            $query['q'] .= ' and c.published=1 ';
                        }
                        break;
				}
			}
		}
		return $query;
	}

	/**
	 * @param $str
	 * @param array $tableAlias
	 * @return array
	 */
	private function _buildSearch($str, $tableAlias = array())
	{

		$q = array('q' => '', 'join' => '');
		if (is_numeric($str))
		{
			//possibly fnum ou uid
			$q['q'] .= ' (u.id = ' . $str . ' or c.fnum like "'.$str.'%") ';
			if (!in_array('jos_users', $tableAlias))
				$q['join'] .= ' left join #__users as u on u.id = c.applicant_id ';
			$q['users'] = true;

		}
		else
		{
			if(filter_var($str, FILTER_VALIDATE_EMAIL) !== false)
			{
				//the request is an email
				$q['q'] .= 'u.email = "'.mysql_real_escape_string($str).'"';
				if (!in_array('jos_users', $tableAlias))
					$q['join'] .= ' left join #__users as u on u.id = c.applicant_id ';
				$q['users'] = true;

			}
			else
			{
				$q['q'] .= ' (ue.lastname LIKE "%' . mysql_real_escape_string($str) . '%" OR ue.firstname LIKE "%' . mysql_real_escape_string($str) . '%" OR u.email LIKE "%' . mysql_real_escape_string($str) . '%" OR u.username LIKE "%' . mysql_real_escape_string($str) . '%" ) ';
				if (!in_array('jos_users', $tableAlias))
					$q['join'] .= ' left join #__users as u on u.id = c.applicant_id';
				$q['join'] .= ' left join #__emundus_users as ue on ue.user_id = c.applicant_id ';
				$q['users'] = true;
				$q['em_user'] = true;

			}
		}
		return $q;
	}

	/**
	 * @return mixed
	 */
	public function getUsers()
	{
		$dbo = $this->getDbo();
		$query = 'select c.fnum, ss.value as status, u.name ';

		// prevent double left join on query 
		$lastTab = array('#__emundus_setup_status', 'jos_emundus_setup_status', 
						 '#__emundus_setup_programmes', 'jos_emundus_setup_programmes',
						 '#__emundus_setup_campaigns', 'jos_emundus_setup_campaigns',
						 '#__users', 'jos_users',
						 '#__emundus_tag_assoc', 'jos_emundus_tag_assoc'
						 );
		if (count($this->_elements)>0) {
			$leftJoin = '';
			foreach ($this->_elements as $elt)
			{
				if(!isset($lastTab))
				{
					$lastTab = array();
				}
				if(!in_array($elt->tab_name, $lastTab))
				{ 
					$leftJoin .= 'left join ' . $elt->tab_name .  ' ON '. $elt->tab_name .'.fnum like c.fnum ';
				}
				$lastTab[] = $elt->tab_name;
			}
		}
		if (count($this->_elements_default)>0) {
			$query .= ', '.implode(',', $this->_elements_default);
		}


		$query .= ' FROM #__emundus_campaign_candidature as c 
					LEFT JOIN #__emundus_setup_status as ss on ss.step = c.status 
					LEFT JOIN #__emundus_setup_campaigns as esc on esc.id = c.campaign_id 
					LEFT JOIN #__emundus_setup_programmes as sp on sp.code like esc.training  
					LEFT JOIN #__users as u on u.id = c.applicant_id
					LEFT JOIN #__emundus_tag_assoc as eta on eta.fnum=c.fnum  ';

		$q = $this->_buildWhere($lastTab);

		if (!empty($leftJoin))
		{
			$query .= $leftJoin;
		}
		$query .= $q['join'];
		$query .= " where 1=1 ".$q['q'];
        //echo($query);die();


		// ONLY FILES LINKED TO MY GROUPS OR TO MY ACCOUNT
       // if(count($this->code)>0)
            $sql_code = ' sp.code IN ("'.implode('","', $this->code).'") ';
        //if(count($this->fnum_assoc)>0)
            $sql_fnum = ' OR c.fnum IN ("'.implode('","', $this->fnum_assoc).'") ';
		$query .= ' AND ('.$sql_code.' '.$sql_fnum.') ';
		//////////////////////////////////////////////////////////////

        $query .= ' GROUP BY c.fnum';

        $query .=  $this->_buildContentOrderBy();

        $dbo->setQuery($query);
		try
		{
			$res = $dbo->loadAssocList();
			$this->_applicants = $res;

			$limit = JFactory::getSession()->get('limitstart');

			$limitStart = JFactory::getSession()->get('limit');
			if($limitStart > 0)
			{
				$query .= " limit $limit, $limitStart ";
			}
			$dbo->setQuery($query);
			$res = $dbo->loadAssocList();
 //echo '<hr>FILES:'.str_replace('#_', 'jos', $query).'<hr>';
			return $res;
		}
		catch(Exception $e)
		{
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
		}
	}


	// get emundus groups for user
	/**
	 * @param $uid
	 * @return mixed
	 * @throws Exception
	 */
	public function getUserGroups($uid)
	{
		return @EmundusHelperFiles::getUserGroups($uid);
	}

	/**
	 * @return array
	 */
	public function getDefaultElements()
	{
		return $this->_elements;
	}

	/**
	 * @return array|string
	 */
	public function getSelectList()
	{
		$lists = '';

		if (!empty($this->col)) {
			foreach ($this->col as $c) {
				if (!empty($c)) {
					$tab = explode('.', $c);
					$names = @$tab[1];
					$tables = $tab[0];

					$query = 'SELECT distinct(fe.name), fe.label, ft.db_table_name as table_name
						FROM #__fabrik_elements fe
						LEFT JOIN #__fabrik_formgroup ff ON ff.group_id = fe.group_id
						LEFT JOIN #__fabrik_lists ft ON ft.form_id = ff.form_id
						WHERE fe.name = "' . $names . '"
						AND ft.db_table_name = "' . $tables . '"';
					$this->_db->setQuery($query);
					$cols[] = $this->_db->loadObject();
				}
			}
			if (!empty($cols)) {
				foreach ($cols as $c) {
					if (!empty($c)) {
						$list = array();
						$list['name'] = @$c->table_name . '___' . $c->name;
						$list['label'] = @ucfirst($c->label);
						$lists[] = $list;
					}
				}
			}
		}
		return $lists;
	}

	/**
	 * @return mixed
	 */
	public function getProfiles()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft FROM #__emundus_setup_profiles esp
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
		ORDER BY caag.lft, esp.label';
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	/**
	 * @param $id
	 * @return mixed
	 */
	public function getProfileByID($id)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esp.* FROM jos_emundus_setup_profiles as esp
				LEFT JOIN jos_emundus_users as eu ON eu.profile=esp.id
				WHERE eu.user_id=' . $id;
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * @param $ids
	 * @return mixed
	 */
	public function getProfilesByIDs($ids)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft
		FROM #__emundus_setup_profiles esp
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
		WHERE esp.id IN (' . implode(',', $ids) . ')
		ORDER BY caag.lft, esp.label';
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	/**
	 * @return mixed
	 */
	public function getAuthorProfiles()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft
		FROM #__emundus_setup_profiles esp
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
		WHERE esp.acl_aro_groups=19';
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	/**
	 * @return mixed
	 */
	public function getApplicantsProfiles()
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label FROM #__emundus_setup_profiles esp
				  WHERE esp.published=1 ';
		$no_filter = array("Super Users", "Administrator");
		if (!in_array($user->usertype, $no_filter))
			$query .= ' AND esp.id IN (select profile_id from #__emundus_users_profiles where profile_id in (' .
			          implode(',', $this->getProfileAcces($user->id)) . ')) ';
		$query .= ' ORDER BY esp.label';
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * @param $profile
	 * @return mixed
	 */
	public function getApplicantsByProfile($profile)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT eup.user_id FROM #__emundus_users_profiles eup WHERE eup.profile_id=' . $profile;
		$db->setQuery($query);
		return $db->loadResultArray();
	}


	/**
	 * @return mixed
	 */
	public function getAuthorUsers()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT u.id, u.gid, u.name
		FROM #__users u
		WHERE u.gid=19';
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	/**
	 * @return mixed
	 */
	public function getMobility()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esm.id, esm.label, esm.value
		FROM #__emundus_setup_mobility esm
		ORDER BY ordering';
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}

	/**
	 * @return mixed
	 */
	public function getElements()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT element.id, element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin,
				 groupe.label AS group_label, INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated,
				 tab.db_table_name AS table_name, tab.label AS table_label
			FROM jos_fabrik_elements element
				 INNER JOIN jos_fabrik_groups AS groupe ON element.group_id = groupe.id
				 INNER JOIN jos_fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				 INNER JOIN jos_fabrik_lists AS tab ON tab.form_id = formgroup.form_id
				 INNER JOIN jos_menu AS menu ON tab.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
				 INNER JOIN jos_emundus_setup_profiles AS profile ON profile.menutype = menu.menutype
			WHERE tab.published = 1 AND element.published=1 AND element.hidden=0 AND element.label!=" " AND element.label!=""
			ORDER BY menu.ordering, formgroup.ordering, element.ordering';
		$db->setQuery($query);
		//die(print_r($db->loadObjectList('id')));
		return $db->loadObjectList('id');
	}

	/**
	 * @return mixed
	 */
	public function getElementsName()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT element.id, element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin,
				 groupe.label AS group_label, INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated,
				 tab.db_table_name AS table_name, tab.label AS table_label
			FROM jos_fabrik_elements element
				 INNER JOIN jos_fabrik_groups AS groupe ON element.group_id = groupe.id
				 INNER JOIN jos_fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				 INNER JOIN jos_fabrik_lists AS tab ON tab.form_id = formgroup.form_id
				 INNER JOIN jos_menu AS menu ON tab.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
				 INNER JOIN jos_emundus_setup_profiles AS profile ON profile.menutype = menu.menutype
			WHERE tab.published = 1 AND element.published=1 AND element.hidden=0 AND element.label!=" " AND element.label!=""
			ORDER BY menu.ordering, formgroup.ordering, element.ordering';
		$db->setQuery($query);
//echo str_replace('#_', 'jos', $query);
		return $db->loadObjectList('element_name');
	}

	/*public function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}*/
	/**
	 * @return int|null
	 */
	public function getTotal()
	{
		if (empty($this->_total))
			$this->_total = count($this->_applicants);
		return $this->_total;
	}

	// get applicant columns
	/**
	 * @return array
	 */
	public function getApplicantColumns()
	{
		$cols = array();
		$cols[] = array('name' => 'user_id', 'label' => 'User id');
		$cols[] = array('name' => 'user', 'label' => 'User id');
		$cols[] = array('name' => 'name', 'label' => 'Name');
		$cols[] = array('name' => 'email', 'label' => 'Email');
		$cols[] = array('name' => 'profile', 'label' => 'Profile');

		return $cols;
	}

	/**
	 * @return JPagination|null
	 */
	public function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$session = JFactory::getSession();
			$this->_pagination = new JPagination($this->getTotal(), $session->get('limitstart'), $session->get('limit'));
		}
		return $this->_pagination;
	}

	/**
	 * @return mixed
	 */
	public function getSchoolyears()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT DISTINCT(schoolyear) as schoolyear
		FROM #__emundus_users
		WHERE schoolyear is not null AND schoolyear != ""
		ORDER BY schoolyear';
		$db->setQuery($query);
		return $db->loadResultArray();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getAllActions()
	{

		$query = 'select distinct * from #__emundus_setup_actions as esa where esa.status = 1 ORDER BY ordering';
		$db = $this->getDbo();

		try
		{
			$db->setQuery($query);
			return $db->loadAssocList();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getEvalGroups()
	{
		try
		{
			$db = $this->getDbo();
			$query = 'select * from #__emundus_setup_groups where 1';
			$db->setQuery($query);
			$evalGroups['groups'] = $db->loadAssocList();
			$query = 'SELECT eu.user_id, CONCAT( eu.firstname, " ", eu.lastname ) as name
						FROM #__emundus_users AS eu
						LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile
						LEFT JOIN #__emundus_users_profiles AS eup ON eup.user_id = eu.user_id
						WHERE eu.profile !=1
						AND esp.published !=1';
//echo str_replace('#_', 'jos', $query);
			$db->setQuery($query);
			$evalGroups['users'] = $db->loadAssocList();
			return $evalGroups;

		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param $groups
	 * @param $actions
	 * @param $fnums
	 * @return bool|mixed
	 */
	public function shareGroups($groups, $actions, $fnums)
	{
		try
		{
			$db = $this->getDbo();
			$query = 'insert into #__emundus_group_assoc (group_id, action_id, c, r, u, d, fnum) values';
			$value = "";
			foreach ($fnums as $fnum)
			{
				foreach ($actions as $action)
				{
					foreach($groups as $group)
					{
						$ac = (array) $action;
						$value .= "($group, ".implode(',', $ac).", $fnum),";
					}
				}
			}
			$query .= rtrim($value, ',');

			$db->setQuery($query);
			return $db->execute();
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * @param $users
	 * @param $actions
	 * @param $fnums
	 * @return bool|string
	 */
	public function shareUsers($users, $actions, $fnums)
	{
        $error = null;
        $query = null;
		try
		{
			$db = $this->getDbo();
			//$query = 'insert into #__emundus_users_assoc (user_id, action_id, c, r, u, d, fnum) values';
			//$value = "";
			foreach ($fnums as $fnum)
			{
				foreach($users as $user)
				{
					foreach ($actions as $action)
					{
						$ac = (array) $action;
						//$value .= "($user, ".implode(',', $ac).", $fnum),";
						$query = 'insert into #__emundus_users_assoc (user_id, action_id, c, r, u, d, fnum) values ('.$user.', '.implode(',', $ac).', '.$fnum.')';
						$db->setQuery($query);
						if (!$db->execute()) {
							$error = $fnum.' - '.implode(',', $ac).' - '.$user.'<br>';
							$query = 'UPDATE #__emundus_users_assoc SET c='.$ac['c'].', r='.$ac['r'].', u='.$ac['u'].', d='.$ac['d'].' WHERE user_id='.$user.' AND fnum like '.$db->Quote($fnum).' AND action_id='.$ac['id'];
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}
			//$query .= rtrim($value, ',');
			//$db->setQuery($query);
			return $error;
		}
		catch (Exception $e)
		{
			error_log($e->getMessage());
			error_log($query);
			return false;
		}
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getAllTags()
	{
		$query = 'select * from #__emundus_setup_action_tag where 1';
		$db = $this->getDbo();

		try
		{
			$db->setQuery($query);
			return $db->loadAssocList();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getAllStatus()
	{
		$query = 'select * from #__emundus_setup_status where 1';
		$db = $this->getDbo();

		try
		{
			$db->setQuery($query);
			return $db->loadAssocList();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param $id
	 * @return mixed
	 * @throws Exception
	 */
	public function getStatusByID($id)
	{
		$query = 'select * from #__emundus_setup_status where id='.$id;
		$db = $this->getDbo();

		try
		{
			$db->setQuery($query);
			return $db->loadAssocList();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param $fnums
	 * @return mixed
	 * @throws Exception
	 */
	public function getStatusByFnums($fnums)
	{
		$query = 'select * 
				  from #__emundus_campaign_candidature as ecc  
				  left join #__emundus_setup_status as ess on ess.step=ecc.status 
				  where ecc.fnum in ("'.implode('","', $fnums).'")';
		$db = $this->getDbo();

		try
		{
			$db->setQuery($query);
			return $db->loadAssocList('fnum');
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param $fnums
	 * @param $tag
	 * @return bool|mixed
	 */
	public function tagFile($fnums, $tag)
	{
		try
		{
			$db = $this->getDbo();
			$user = JFactory::getUser()->id;
			foreach ($fnums as $fnum)
			{
				$query = 'insert into #__emundus_tag_assoc(fnum, id_tag, user_id) VALUES ("'.$fnum.'", '.$tag.','.$user.'); ';
				$db->setQuery($query);
				$db->execute();
			}
			return true;
		}
		catch (Exception $e)
		{
			error_log($e->getMessage());
			error_log($query);
			return false;
		}
	}

	/**
	 * @param null $tag
	 * @return mixed
	 * @throws Exception
	 */
	public function getTaggedFile($tag = null)
	{
		$db = $this->getDbo();
		$query = 'select t.fnum, sat.class from #__emundus_tag_assoc as t join #__emundus_setup_action_tag as sat on sat.id = t.id_tag where';
		$user = JFactory::getUser()->id;

		if(is_null($tag))
		{
			$query .= ' t.user_id = ' . $user;
			try
			{
				$db->setQuery($query);
				return $db->loadAssocList('fnum');
			}
			catch(Exception $e)
			{
				throw $e;
			}
		}
		else
		{
			$user = JFactory::getUser()->id;
			$query .= ' t.id_tag = '.$tag. ' and t.user_id = ' . $user;
			try
			{
				$db->setQuery($query);
				return $db->loadAssocList();
			}
			catch(Exception $e)
			{
				throw $e;
			}
		}


	}

	/**
	 * @param $fnums
	 * @param $state
	 * @return bool|mixed
	 */
	public function updateState($fnums, $state)
	{
		try
		{
			$db = $this->getDbo();
			foreach ($fnums as $fnum)
			{
				$query = 'update #__emundus_campaign_candidature set status = '.$state.' WHERE fnum like '.$db->Quote($fnum) ; 
				$db->setQuery($query);
				$res = $db->execute();
			}
			return $res;
		}
		catch (Exception $e)
		{
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}

	}
    /**
     * @param $fnums
     * @param $publish
     * @return bool|mixed
     */
    public function updatePublish($fnums, $publish)
    {
        try
        {
            $db = $this->getDbo();
            foreach ($fnums as $fnum)
            {
                $query = 'update #__emundus_campaign_candidature set published = '.$publish.' WHERE fnum like '.$db->Quote($fnum) ;
                $db->setQuery($query);
                $res = $db->execute();
            }
            return $res;
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

    }

	/**
	 * @return mixed|null
	 */
	public function getPhotos()
	{
		try
		{
			$db = $this->getDbo();
			$query = 'select emu.id, emu.user_id, c.fnum, emu.filename  from #__emundus_uploads as emu left join #__emundus_campaign_candidature as c on c.applicant_id = emu.user_id where attachment_id = 10';
			$db->setQuery($query);
			return $db->loadAssocList('fnum');
		}
		catch(Exception $e)
		{
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return null;
		}

	}

	/**
	 * @return mixed|null
	 */
	public function getEvaluatorsFromGroup()
	{
		try
		{
			$db = $this->getDbo();
			$query = 'select distinct ga.fnum, u.name, g.title, g.id  from #__emundus_group_assoc  as ga left join #__user_usergroup_map as uum on uum.group_id = ga.group_id left join #__users as u on u.id = uum.user_id left join #__usergroups as g on g.id = ga.group_id where 1 order by ga.fnum asc, g.title';
			$db->setQuery($query);
			return $db->loadAssocList();
		}
		catch(Exception $e)
		{
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return null;
		}
	}

	/**
	 * @return mixed|null
	 */
	public function getEvaluators()
	{
		try
		{
			$db = $this->getDbo();
			$query = 'select * from #__usergroups where title = "Evaluator"';
			$db->setQuery($query);
			$eval = $db->loadAssoc();

			$query = 'select distinct ua.fnum, u.name, u.id from #__emundus_users_assoc  as ua left join #__users as u on u.id = ua.user_id where 1';
			$db->setQuery($query);
			return $db->loadAssocList();
		}
		catch(Exception $e)
		{
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return null;
		}
	}

	/**
	 * @param $fnum
	 * @param $id
	 * @param $isGroup
	 * @return bool|mixed
	 */
	public function unlinkEvaluators($fnum, $id, $isGroup)
	{
		try
		{
			$db = JFactory::getDbo();

			if($isGroup)
			{
				$query = 'delete from #__emundus_group_assoc where fnum like '.$db->Quote($fnum) . ' and group_id =' . $id;
			}
			else
			{
				$query = 'delete from #__emundus_users_assoc where fnum like '.$db->Quote($fnum) . ' and user_id =' . $id;
			}

			$db->setQuery($query);
			return $db->query() ;
		}
		catch(Exception $e)
		{
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	/**
	 * @param $fnum
	 * @return bool|mixed
	 */
	public function getFnumInfos($fnum)
	{
		try
		{
			$db = $this->getDbo();
			$query = 'select u.name, c.label, cc.fnum, c.training 
						from #__emundus_campaign_candidature as cc 
						left join #__emundus_setup_campaigns as c on c.id = cc.campaign_id left join 
						#__users as u on u.id = cc.applicant_id where cc.fnum like '.$db->Quote($fnum);
			$db->setQuery($query);

			return $db->loadAssoc();
		}
		catch (Exception $e)
		{
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	/**
	 * @param $fnums
	 * @return bool|mixed
	 */
	public function getFnumsInfos($fnums)
	{ 
		try
		{
			$db = $this->getDbo();
			$query = 'select u.name, u.email, cc.fnum, sc.label, sc.start_date, sc.end_date, sc.year, sc.published, sc.training, cc.applicant_id
						from #__emundus_campaign_candidature as cc
 						left join #__users as u on u.id = cc.applicant_id
 						left join #__emundus_setup_campaigns as sc on sc.id = cc.campaign_id
 						where cc.fnum in ("'. implode('","', $fnums).'")';
			$db->setQuery($query);

			return $db->loadAssocList('fnum');
		}
		catch (Exception $e)
		{
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	/**
	 * @param $fnums
	 * @return bool|mixed
	 */
	public function getFnumsTagsInfos($fnums)
	{
		try
		{
			$db = $this->getDbo();
			$query = 'select u.name as applicant_name, u.email as applicant_email, u.username as applicant_email, cc.fnum, sc.id as campaign_id , sc.label as campaign_label, sc.training as campaign_code,  sc.start_date as campaign_start, sc.end_date as campaign_end, sc.year as campaign_year,  jesp.code as training_code, jesp.label as training_programme, cc.applicant_id as applicant_id, jess.value as application_status, group_concat(jesat.label) as application_tags
						from jos_emundus_campaign_candidature as cc
 						left join jos_users as u on u.id = cc.applicant_id
 						left join jos_emundus_setup_campaigns as sc on sc.id = cc.campaign_id
 						left join jos_emundus_setup_programmes as jesp on jesp.code = sc.training
 						left join jos_emundus_setup_status as jess on jess.step = cc.status
 						left join jos_emundus_tag_assoc as jeta on jeta.fnum like cc.fnum
 						left join jos_emundus_setup_action_tag as jesat on jesat.id = jeta.id_tag
 						where cc.fnum in ("'. implode('","', $fnums).'")
 						group by cc.fnum';
			$db->setQuery($query);
			return $db->loadAssocList('fnum');
		}
		catch (Exception $e)
		{
			return false;
		}
	}


	/**
	 * @param $fnum
	 * @param int $published
	 * @return bool|mixed
	 */
	public function changePublished($fnum, $published = -1)
	{
		try
		{
			$db = $this->getDbo();
			$query = "update #__emundus_campaign_candidature set published = ".$published." where fnum like " . $db->quote($fnum);
			$db->setQuery($query);
			$res = $db->execute();
			return $res;
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	public function getAllFnums()
	{
		try
		{
			$db = $this->getDbo();
			$query = "SELECT c.fnum 
						FROM #__emundus_campaign_candidature as c 
						LEFT JOIN #__users as u on u.id = c.applicant_id 
						LEFT JOIN #__emundus_setup_campaigns as esc on esc.id = c.campaign_id 
						LEFT JOIN #__emundus_setup_programmes as sp on sp.code=esc.training ";
			$leftJoin = '';
			if(!isset($lastTab))
			{
					$lastTab = array(
						'#__emundus_campaign_candidature', 'jos_emundus_campaign_candidature', 
						'#__emundus_setup_campaigns', 'jos_emundus_setup_campaigns', 
						'#__emundus_setup_programmes', 'jos_emundus_setup_programmes', 
						'#__users', 'jos_users'
							);
			} else {
				$lastTab[] = '#__emundus_campaign_candidature';
				$lastTab[] = 'jos_emundus_campaign_candidature';
				$lastTab[] = '#__emundus_setup_campaigns';
				$lastTab[] = 'jos_emundus_setup_campaigns';
				$lastTab[] = '#__emundus_setup_programmes';
				$lastTab[] = 'jos_emundus_setup_programmes';
				$lastTab[] = '#__users';
				$lastTab[] = 'jos_users';
			}
			if(count($this->_elements) > 0) {
				foreach ($this->_elements as $elt)
				{
					if(!in_array($elt->tab_name, $lastTab))
					{
						$leftJoin .= ' left join ' . $elt->tab_name .  ' on '. $elt->tab_name .'.fnum like c.fnum ';
					}
					$lastTab[] = $elt->tab_name;
				}
			}

			$q = $this->_buildWhere($lastTab);
			if (!empty($leftJoin))
			{
				$query .= $leftJoin;
			}
			$query .= $q['join'];
			$query .= ' where c.published = 1 ' . $q['q']; 
			$db->setQuery($query);

			return $db->loadColumn();
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	/*
	* 	Get values of elements by list of files numbers
	*	@param fnums 	List of application files numbers
	*	@param elements 	array of element to get value
	* 	@return array
	*/
	/**
	 * @param $fnums
	 * @param $elements
	 * @param $methode  aggregate in one cell (0) or split one data per line
	 * @return bool|mixed
	 */
	public function getFnumArray($fnums, $elements, $methode=0)
	{ 
		try
		{
			$db = $this->getDbo();
			$query = 'select c.fnum, u.email, esc.label, sp.code, esc.id as campaign_id';
			$leftJoin = '';
			$leftJoinMulti = '';
			$tableAlias = array('jos_emundus_setup_campaigns' => 'esc',
			                    'jos_emundus_campaign_candidature' => 'c',
			                    'jos_emundus_setup_programmes' => 'sp',
								'jos_users' => 'u',
								'jos_emundus_tag_assoc' => 'eta');
            $lastTab = array();
			foreach ($elements as $elt)
			{
				$params_group = json_decode($elt->group_attribs);

				if (array_key_exists($elt->tab_name, $tableAlias))
				{
					if ($params_group->repeat_group_button == 1) {
						if ($methode == 1) {
							$query .= ', '.$elt->table_join.'.'.$elt->element_name.' AS '. $elt->table_join.'___'.$elt->element_name;
							if(!in_array($elt->table_join, $lastTab)) {
								$leftJoinMulti .= ' left join ' . $elt->table_join.' on '. $elt->table_join.'.parent_id='.$elt->tab_name.'.id ';
							}
							$lastTab[] = $elt->table_join;
						}
						else {
							$query .= ', (
										SELECT GROUP_CONCAT('.$elt->table_join.'.'.$elt->element_name.'.'.$elt->element_name.' SEPARATOR ", ")
										FROM '.$elt->table_join.'
										WHERE '.$elt->table_join.'.parent_id='.$tableAlias[$elt->tab_name].'.id
									  )	AS '. $elt->table_join.'___'.$elt->element_name;
						}
					}
					else {
                        $select = $tableAlias[$elt->tab_name].'.'.$elt->element_name;
                        if ($elt->element_plugin == 'dropdown' || $elt->element_plugin == 'radiobutton') {
                            $element_attribs = json_decode($elt->element_attribs);
                            foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                                $select = 'REPLACE('.$select.', "'.$value.'", "'.$element_attribs->sub_options->sub_labels[$key].'")';
                            }
                        }

                        $query .= ', ' . $select . ' AS ' . $tableAlias[$elt->tab_name] . '___' . $elt->element_name;
                    }
				}
				else
				{
					if ($params_group->repeat_group_button == 1) {
						if ($methode == 1) {
							$query .= ', '.$elt->table_join.'.'.$elt->element_name.' AS '. $elt->table_join.'___'.$elt->element_name;
							if(!in_array($elt->table_join, $lastTab)) {
								$leftJoinMulti .= ' left join ' . $elt->table_join.' on '. $elt->table_join.'.parent_id='.$elt->tab_name.'.id ';
							}
							$lastTab[] = $elt->table_join;
						}
						else {
							$query .= ', (
										SELECT GROUP_CONCAT('.$elt->table_join.'.'.$elt->element_name.' SEPARATOR ", ")
										FROM '.$elt->table_join.'
										WHERE '.$elt->table_join.'.parent_id='.$elt->tab_name.'.id
									  )	AS '. $elt->table_join.'___'.$elt->element_name;
						}
					}
					else {
                        $select = $elt->tab_name.'.'.$elt->element_name;

                        if ($elt->element_plugin == 'dropdown' || $elt->element_plugin == 'radiobutton') {
                            $element_attribs = json_decode($elt->element_attribs);
                            foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                                $select = 'REPLACE('.$select.', "'.$value.'", "'.$element_attribs->sub_options->sub_labels[$key].'")';
                            }
                        }
					    $query .= ', '.$select. ' AS '.$elt->tab_name.'___'.$elt->element_name;
                    }

					if(!isset($lastTab))
					{
						$lastTab = array();
					}

					if(!in_array($elt->tab_name, $lastTab))
					{
						$leftJoin .= ' left join ' . $elt->tab_name .  ' on '. $elt->tab_name .'.fnum like c.fnum ';
					}
					
					$lastTab[] = $elt->tab_name;
				}
			}
			$query .= ' from #__emundus_campaign_candidature as c 
						left join #__users as u on u.id = c.applicant_id 
						left join #__emundus_setup_campaigns as esc on esc.id = c.campaign_id 
						left join #__emundus_setup_programmes as sp on sp.code like esc.training ';

			$query .= $leftJoin. ' '. $leftJoinMulti;

			//$q = $this->_buildWhere($tableAlias);

			//$query .= $q['join'];

			$query .= 'where c.fnum in ("'.implode('","', $fnums).'") ';
//die( str_replace('#_', 'jos', $query) );
			$db->setQuery($query);
			return $db->loadAssocList();
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	/**
	 * @param $fnums
	 * @return bool|mixed
	 */
	public function getEvalsByFnum($fnums)
	{
		try
		{
			$db = $this->getDbo();
			$query = 'select * from #__emundus_evaluations where fnum in ("'.implode('","', $fnums).'")';
			$db->setQuery($query);
			return $db->loadAssocList('fnum');
		}
		catch(Exception $e)
		{
			return false;
		}
	}

    /**
     * @param $fnum
     * @return bool|mixed
     */
    public function getEvalByFnum($fnum)
    {
        try
        {
            $db = $this->getDbo();
            $query = 'select * from #__emundus_evaluations where fnum in ("'.$fnum.'")';
            $db->setQuery($query);
            return $db->loadAssocList('fnum');
        }
        catch(Exception $e)
        {
            return false;
        }
    }

	/**
	 * @param $fnums
	 * @return bool|mixed
	 */
	public function getCommentsByFnum($fnums)
	{
		try
		{
			$db = $this->getDbo();
			$query = 'select * from #__emundus_comments where fnum in ("'.implode('","', $fnums).'")';
			$db->setQuery($query);
			return $db->loadAssocList();
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	/**
	 * @param $fnums
	 * @return bool|mixed
	 */
	public function getFilesByFnums($fnums)
	{
		$db = $this->getDbo();
		$query = 'select fu.* from #__emundus_uploads as fu where fu.fnum in ("'.implode('","', $fnums).'") order by fu.fnum';
		$db->setQuery($query);
		try
		{
			$res = $db->loadAssocList();
			return $res;
		}
		catch(Exception $e)
		{
			echo $e;
            return false;
		}
	}

	/**
	 * @param $fnums
	 * @return bool|mixed
	 */
	public function getGroupsByFnums($fnums)
	{
		$query = 'select cc.fnum,  GROUP_CONCAT( DISTINCT esg.label ) as groupe 
					from #__emundus_campaign_candidature as cc 
					left join #__emundus_setup_campaigns as esc on esc.id = cc.campaign_id 
					left join #__emundus_setup_groups_repeat_course as esgrc on esgrc.course = esc.training 
					left join #__emundus_setup_groups as esg on esg.id = esgrc.parent_id
					where cc.fnum in ("'.implode('","', $fnums).'") group by cc.fnum';
		try
		{
		    $db = $this->getDbo();
			$db->setQuery($query);
			return $db->loadAssocList('fnum', 'groupe');
		}
		catch(Exception $e)
		{
		    error_log($e->getMessage(), 0);
		    return false;
		}
	}

	/**
	 * @param $fnums
	 * @return bool|mixed
	 */
	public function getAssessorsByFnums($fnums)
	{
		$query = 'select cc.fnum,  GROUP_CONCAT( DISTINCT u.name ) as uname
                  from #__emundus_campaign_candidature as cc
                  left join #__emundus_users_assoc as eua on eua.fnum = cc.fnum
                  left join #__users as u on u.id = eua.user_id
                  where cc.fnum in ("'.implode('","', $fnums).'") group by cc.fnum';
		try
		{
			$db = $this->getDbo();
			$db->setQuery($query);
			return $db->loadAssocList('fnum', 'uname');
		}
		catch(Exception $e)
		{
		    error_log($e->getMessage(), 0);
		    return false;
		}
	}

	/**
	 * @param $params
	 * @return array|mixed
	 */
	public function getMenuList($params)
	{
		return @EmundusHelperFiles::getMenuList($params);
	}

	/**
	 * @return mixed
	 */
	public function getActionsACL()
	{
		return @EmundusHelperFiles::getMenuActions();
	}

	/*
	* 	Get evaluation Fabrik formid from fnum
	*	@param fnum 	fnum to evaluate
	* 	@return int 	Fabrik formid
	*/
	/**
	 * @param $fnum
	 * @return bool|mixed
	 */
	public function getFormidByFnum($fnum)
	{
		try
		{
			$db = $this->getDbo();
			$query = "SELECT form_id
						FROM `#__fabrik_formgroup`
						WHERE group_id IN (
							SELECT esp.fabrik_group_id
							FROM  `#__emundus_campaign_candidature` AS ecc
							LEFT JOIN `#__emundus_setup_campaigns` AS esc ON esc.id = ecc.campaign_id
							LEFT JOIN `#__emundus_setup_programmes` AS esp ON esp.code = esc.training
							WHERE ecc.fnum LIKE  " . $db->quote($fnum) .")";
			$db->setQuery($query);
			$res = $db->loadResult();
			return $res;
		}
		catch(Exception $e)
		{
			return false;
		}
	}

    /**
     * @param $fnum
     * @return bool|mixed
     */
    public function getFormByFnum($fnum)
    {
        try
        {
            $db = $this->getDbo();
            $query = "SELECT *
						FROM `#__fabrik_formgroup`
						WHERE group_id IN (
							SELECT esp.fabrik_group_id
							FROM  `#__emundus_campaign_candidature` AS ecc
							LEFT JOIN `#__emundus_setup_campaigns` AS esc ON esc.id = ecc.campaign_id
							LEFT JOIN `#__emundus_setup_programmes` AS esp ON esp.code = esc.training
							WHERE ecc.fnum LIKE  " . $db->quote($fnum) .")";
            $db->setQuery($query);
            $res = $db->loadResult();
            return $res;
        }
        catch(Exception $e)
        {
            return false;
        }
    }

	/**
	 * @param $fnums
	 * @return array|bool
	 */
	public function getAccessorByFnums($fnums)
	{
		$query = "SELECT jecc.fnum, concat('<span class=\'label label-default\' >', group_concat(jesg.label SEPARATOR '</span><span class=\'label label-default\' >'), '</span>') as gname FROM #__emundus_campaign_candidature as jecc
  LEFT JOIN #__emundus_setup_campaigns as jesc on jesc.id = jecc.campaign_id
  LEFT JOIN #__emundus_setup_programmes as jesp on jesp.code = jesc.training
  LEFT JOIN #__emundus_setup_groups_repeat_course as jesgrc on jesgrc.course = jesp.code
  LEFT JOIN #__emundus_setup_groups as jesg on jesg.id = jesgrc.parent_id
  LEFT JOIN #__emundus_acl as jea on jea.group_id = jesg.id
  WHERE jea.action_id = 1 and jea.r = 1 and jecc.fnum in ('".implode("','", $fnums)."')
  GROUP BY jecc.fnum";

		try
		{
			$db = $this->getDbo();
			$db->setQuery($query);
			$res = $db->loadAssocList();
			$access = array();
			foreach($res as $r)
			{
				$access[$r['fnum']] = $r['gname'];
			}
			$query = "SELECT jega.fnum, concat('<span class=\'label label-default\' >', group_concat(jesg.label SEPARATOR '</span><span class=\'label label-default\' >'), '</span>') as gname FROM #__emundus_group_assoc as jega
  LEFT JOIN #__emundus_setup_groups as jesg on jesg.id = jega.group_id
  where jega.action_id = 1 and jega.r = 1  and jega.fnum in ('".implode("','", $fnums)."')
  GROUP BY jega.fnum";

			$db = $this->getDbo();
			$db->setQuery($query);
			$res = $db->loadAssocList('fnum');
			foreach($res as $r)
			{
				if(isset($access[$r['fnum']]))
				{
					$access[$r['fnum']] .= ''.$r['gname'];
				}
				else
				{
					$access[$r['fnum']] = $r['gname'];
				}
			}
			
			$query = "SELECT jeua.fnum, concat('<span class=\'label label-default\' ><span class=\'glyphicon glyphicon-user\'></span> ', group_concat(ju.name SEPARATOR '</span><span class=\'label label-default\'><span class=\'glyphicon glyphicon-user\'></span> '), '</span>') as uname FROM #__emundus_users_assoc as jeua
  LEFT JOIN #__users as ju on ju.id = jeua.user_id
  where jeua.action_id = 1 and jeua.r = 1  and jeua.fnum in ('".implode("','", $fnums)."')
  GROUP BY jeua.fnum";
			$db = $this->getDbo();
			$db->setQuery($query);
			$res = $db->loadAssocList('fnum');
			foreach($res as $r)
			{
				if(isset($access[$r['fnum']]))
				{
					$access[$r['fnum']] .= ''.$r['uname'];
				}
				else
				{
					$access[$r['fnum']] = $r['uname'];
				}
			}
			return $access;
		}
		catch(Exception $e)
		{
			error_log($e->getMessage(), 0);
			return false;
		}
	}

	/**
	 * @param $fnums
	 * @return mixed
	 * @throws Exception
	 */
	public function getEvaluationAverageByFnum($fnums)
	{
		$dbo = $this->getDbo();
		$query = 'SELECT AVG(overall) AS overall, fnum FROM #__emundus_evaluations WHERE fnum IN ("'.implode('","', $fnums).'") GROUP BY fnum';

		try
		{
			$dbo->setQuery($query);
			return $dbo->loadAssocList('fnum', 'overall');
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param $fnums
	 * @return mixed
	 * @throws Exception
	 */
	public function getTagsByFnum($fnums)
	{
		$dbo = $this->getDbo();
		$query = 'SELECT eta.*, esat.*, u.name FROM #__emundus_tag_assoc as eta
					LEFT JOIN #__emundus_setup_action_tag as esat on esat.id=eta.id_tag
					LEFT JOIN #__users AS u on u.id=eta.user_id
					WHERE fnum IN ("'.implode('","', $fnums).'")
					ORDER BY esat.label';

		try
		{
			$dbo->setQuery($query);
			return $dbo->loadAssocList();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param $fnums
	 * @return Exception|mixed|Exception
	 */
	public function getProgByFnums($fnums)
	{
		$dbo = $this->getDbo();
		try
		{
			$query = 'select  jesp.code, jesp.label  from #__emundus_campaign_candidature as jecc
						left join #__emundus_setup_campaigns as jesc on jesc.id = jecc.campaign_id
						left join #__emundus_setup_programmes as jesp on jesp.code like jesc.training
						left join jos_emundus_setup_letters_repeat_training as jeslrt on jeslrt.training like jesp.code
						where jecc.fnum in ("'.implode('","', $fnums).'") and jeslrt.parent_id IS NOT NULL  group by jesp.code order by jesp.code';
			$dbo->setQuery($query);
			return $dbo->loadAssocList('code', 'label');
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * @param $code
	 * @return Exception|mixed|Exception
	 */
	public function getDocsByProg($code)
	{
		$dbo = $this->getDbo();
		try
		{
			$query = 'select jesl.title, jesl.template_type, jesl.id as file_id from jos_emundus_setup_letters as jesl
						left join jos_emundus_setup_letters_repeat_training as jeslrt on jeslrt.parent_id = jesl.id
						where jeslrt.training = '.$dbo->quote($code);
			$dbo->setQuery($query);
			return $dbo->loadAssocList();
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * @param $id
	 * @return mixed
	 */
	public function getAttachmentInfos($id)
	{
		$dbo = $this->getDbo();
		$query = "select * from jos_emundus_setup_attachments where id = {$id}";
		$dbo->setQuery($query);
		return $dbo->loadAssoc();
	}

	/**
	 * @param $fnum
	 * @param $name
	 * @param $uid
	 * @param $cid
	 * @param $attachment_id
	 * @param $desc
	 * @return int
	 */
	public function addAttachment($fnum, $name, $uid, $cid, $attachment_id,$desc)
	{
		$dbo = $this->getDbo();
		$query = "insert into jos_emundus_uploads (user_id, fnum, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id) values ({$uid}, {$dbo->quote($fnum)}, {$attachment_id}, {$dbo->quote($name)}, {$dbo->quote($desc)}, 0, 0, {$cid})";
		$dbo->setQuery($query);
		$dbo->execute();
		return $dbo->insertid();
	}

	/**
	 * @param $code
	 * @param $fnums
	 * @return mixed
	 */
	public function checkFnumsDoc($code, $fnums)
	{
		$dbo = $this->getDbo();
		$query = "select distinct (jecc.fnum) from jos_emundus_campaign_candidature as jecc
					left join jos_emundus_setup_letters_repeat_status as jeslrs on jeslrs.status = jecc.status
					left join jos_emundus_setup_letters as jesl on jesl.id = jeslrs.parent_id
					left join jos_emundus_setup_letters_repeat_training as jeslrt on jeslrt.training like {$dbo->quote($code)}
					WHERE jecc.fnum in ('".implode("','", $fnums)."') group by jecc.fnum";
		$dbo->setQuery($query);
		return $dbo->loadColumn();
	}

	/**
	 * @param $ids
	 * @return mixed
	 * @throws Exception
	 */
	public function getAttachmentsById($ids)
	{
		$dbo = $this->getDbo();
		$query = 'select * from jos_emundus_uploads where id in ("'.implode('","', $ids).'")';
		try
		{
			$dbo->setQuery($query);
			return $dbo->loadAssocList();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param $idFabrik
	 * @return mixed
	 * @throws Exception
	 */
	public function getValueFabrikByIds($idFabrik)
	{
		$dbo = $this->getDbo();
		$select = "select jfe.id, jfe.name, jfe.plugin, jfe.params, jfg.params as group_params, jfg.id as group_id, jfl.db_table_name, jfj.table_join
                    from jos_fabrik_elements as jfe
					left join #__fabrik_formgroup as jff on jff.group_id = jfe.group_id
					left join #__fabrik_groups as jfg on jfg.id = jff.group_id
					left join #__fabrik_forms as jff2 on jff2.id = jff.form_id
					left join #__fabrik_lists as jfl on jfl.form_id = jff2.id
					LEFT JOIN #__fabrik_joins AS jfj ON jfl.id = jfj.list_id AND jfg.id=jfj.group_id
					where jfe.id in (".implode(',', $idFabrik).")";
		try
		{
			$dbo->setQuery($select);
			return $dbo->loadAssocList();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param $elt
     * @param null $fnums
	 * @param $params
	 * @param $groupRepeat
	 * @return mixed
	 */
	public function getFabrikValueRepeat($elt, $fnums, $params = null, $groupRepeat)
	{
        //$gid = $elt['group_id'];
        $tableName = $elt['db_table_name'];
        $tableJoin = $elt['table_join'];
        $name = $elt['name'];
        $plugin = $elt['plugin'];

		$isFnumsNull = ($fnums === null);
		$isDatabaseJoin = ($plugin === 'databasejoin');
		$isMulti = (@$params->database_join_display_type == "multilist" || @$params->database_join_display_type == "checkbox");
		$dbo = $this->getDbo();

		if($plugin === 'date')
		{
			$query = 'select GROUP_CONCAT(DATE_FORMAT(t_repeat.' . $name.', '.$dbo->quote($params->date_form_format).')  SEPARATOR ", ") as val, , t_origin.fnum ';
		}
		elseif($isDatabaseJoin)
		{
			if($groupRepeat)
			{
					$query = 'select GROUP_CONCAT(t_origin.' . $params->join_val_column . '  SEPARATOR ", ") as val , t_table.fnum ';
			}
			else
			{
				if($isMulti)
				{
					$query = 'select GROUP_CONCAT(t_origin.' . $params->join_val_column . '  SEPARATOR ", ") as val , t_elt.fnum ';
				}
				else
				{
					$query = 'select t_origin.' . $params->join_val_column . ' as val, t_elt.fnum ';
				}
			}
		}
		else
		{
			$query = 'SELECT  GROUP_CONCAT(t_repeat.' . $name.'  SEPARATOR ", ") as val , t_origin.fnum ';
		}

		if($isDatabaseJoin)
		{
			if($groupRepeat)
			{
				$tableName2 = $tableJoin;
				if($isMulti)
				{
					$query .= ' FROM ' . $params->join_db_name . ' as  t_origin left join '.$tableName.'_repeat_'.$name .' as t_repeat on t_repeat.' . $name . " = t_origin.".$params->join_key_column . ' left join ' . $tableName2 . ' as t_elt on t_elt.id = t_repeat.parent_id left join '.$tableName.' as t_table on t_table.id = t_elt.parent_id ';
				}
				else
				{
					$query .= ' FROM ' . $params->join_db_name . ' as  t_origin left join '.$tableName2.' as t_elt on t_elt.' . $name . " = t_origin.".$params->join_key_column." left join $tableName as t_table on t_table.id = t_elt.parent_id ";
				}
			}
			else
			{
				if($isMulti)
				{
					$query .= ' FROM ' . $params->join_db_name . ' as  t_origin left join '.$tableName.'_repeat_'.$name .' as t_repeat on t_repeat.' . $name . " = t_origin.".$params->join_key_column . ' left join ' . $tableName . ' as t_elt on t_elt.id = t_repeat.parent_id ';
				}
				else
				{
					$query .= ' FROM ' . $params->join_db_name . ' as  t_origin left join '.$tableName.' as t_elt on t_elt.' . $name . " = t_origin.".$params->join_key_column;
				}
			}

		}
		else
		{
			$query .= ' FROM ' . $tableJoin . ' as t_repeat  left join '.$tableName.' as t_origin on t_origin.id = t_repeat.parent_id';
		}

		if($isMulti || $isDatabaseJoin)
		{
			if($groupRepeat)
			{
				$query .= ' where t_table.fnum in ("'.implode('","', $fnums).'") group by t_table.fnum';
			}
			else
			{
				$query .= ' where t_elt.fnum in ("'.implode('","', $fnums).'") group by t_elt.fnum';
			}
		}
		else
		{
			$query .= ' where t_origin.fnum in ("'.implode('","', $fnums).'") group by t_origin.fnum';
		}



		$dbo->setQuery($query);

		if(!$isFnumsNull)
		{
			$res = $dbo->loadAssocList('fnum');
		}
		else
		{
			$res = $dbo->loadAssocList();
		}

		return $res;
	}

	/**
	 * @param $fnums
	 * @param $tableName
	 * @param $name
	 * @return mixed
	 * @throws Exception
	 */
	public function getFabrikValue($fnums, $tableName, $name, $dateFormat = null)
	{
		$dbo = $this->getDbo();
		if($dateFormat !== null)
		{
			$query = "select fnum, DATE_FORMAT({$name}, ".$dbo->quote($dateFormat).") as val from {$tableName} where fnum in ('".implode("','", $fnums)."')";
		}
		else
		{
			$query = "select fnum, {$name} as val from {$tableName} where fnum in ('".implode("','", $fnums)."')";
		}
		try
		{
			$dbo->setQuery($query);
			return $dbo->loadAssocList('fnum');
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
}
