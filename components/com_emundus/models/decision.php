<?php
/**
 * @package        Joomla
 * @subpackage    eMundus
 * @link        http://www.emundus.fr
 * @copyright    Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license        GNU/GPL
 * @author        Yoan Durand
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'files.php');
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'list.php');
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
require_once(JPATH_COMPONENT . DS . 'models' . DS . 'files.php');

class EmundusModelDecision extends JModelList
{
	private $_total = null;
	private $_pagination = null;
	private $_applicants = array();
	private $subquery = array();
	private $_elements_default;
	private $_elements;
	private $_files;
    public $fnum_assoc;
    public $code;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct() {

		parent::__construct();

		$this->_files = new EmundusModelFiles;
        $db = JFactory::getDbo();
		$mainframe = JFactory::getApplication();

		// Get current menu parameters
		$menu = @JFactory::getApplication()->getMenu();
		$current_menu = $menu->getActive();
		/*
		** @TODO : gestion du cas Itemid absent à prendre en charge dans la vue
		*/

        if (empty($current_menu)) {
            return false;
        }

		$menu_params = $menu->getParams($current_menu->id);
		$em_other_columns = explode(',', $menu_params->get('em_other_columns'));
		$session = JFactory::getSession();

		if (!$session->has('filter_order') || $session->get('filter_order') == 'c.id') {

            if (in_array('overall', $em_other_columns)) {

                $session->set('filter_order', 'overall');
                $session->set('filter_order_Dir', 'desc');

            } else {

                $session->set('filter_order', 'c.id');
                $session->set('filter_order_Dir', 'desc');

			}
        }

		if (!$session->has('limit')) {
			$limit = $mainframe->getCfg('list_limit');
			$limitstart = 0;
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$session->set('limit', $limit);
			$session->set('limitstart', $limitstart);
		} else {
			$limit = intval($session->get('limit'));
			$limitstart = intval($session->get('limitstart'));
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$session->set('limit', $limit);
			$session->set('limitstart', $limitstart);
		}

		$col_elt = $this->getState('elements');
		$col_other = $this->getState('elements_other');

		$this->elements_id = $menu_params->get('em_elements_id');

		// get evaluation element
		$show_in_list_summary = 1;
        $hidden = 0;
		$elements_eval = $this->getAllDecisionElements($show_in_list_summary, $hidden);
		if (is_array($elements_eval) && count($elements_eval)) {
			$this->elements_id .= implode(',', $elements_eval);
		}

		if ($session->has('adv_cols')) {
			$adv = $session->get('adv_cols');
			if (!empty($adv)) {
				$this->elements_id .= ','.implode(',', $adv);
			}

		}
		$this->elements_values = explode(',', $menu_params->get('em_elements_values'));

		$this->_elements_default = array();
		$this->_elements = @EmundusHelperFiles::getElementsName($this->elements_id);


		if (!empty($this->_elements)) {
			foreach ($this->_elements as $def_elmt) {
				$group_params = json_decode($def_elmt->group_attribs);

				if ($def_elmt->element_plugin == 'date') {
					if ($group_params->repeat_group_button == 1) {
						$this->_elements_default[] = '(
														SELECT  GROUP_CONCAT(DATE_FORMAT('.$def_elmt->table_join.'.' . $def_elmt->element_name.', "%d/%m/%Y %H:%i:%m") SEPARATOR ", ")
														FROM '.$def_elmt->table_join.'
														WHERE '.$def_elmt->table_join.'.parent_id = '.$def_elmt->tab_name.'.id
													  ) AS `'.$def_elmt->table_join.'___' . $def_elmt->element_name.'`';
					} else {
						$this->_elements_default[] = $def_elmt->tab_name.'.'.$def_elmt->element_name.' AS `'.$def_elmt->tab_name.'___'.$def_elmt->element_name.'`';
					}
				} elseif ($def_elmt->element_plugin == 'databasejoin') {
					$attribs = json_decode($def_elmt->element_attribs);
					$join_val_column_concat = str_replace('{thistable}', $attribs->join_db_name, $attribs->join_val_column_concat);
					$join_val_column_concat = str_replace('{shortlang}', substr(JFactory::getLanguage()->getTag(), 0 , 2), $join_val_column_concat);
					$join_val_column = (!empty($join_val_column_concat) && $join_val_column_concat!='')?'CONCAT('.$join_val_column_concat.')':$attribs->join_val_column;

                    // Check if the db table has a published column. So we don't get the unpublished value
                    $db->setQuery("SHOW COLUMNS FROM $attribs->join_db_name LIKE 'published'");
                    $publish_query = ($db->loadResult()) ? " AND $attribs->join_db_name.published = 1 " : '';

					if ($group_params->repeat_group_button == 1) {
						$query = '(
									select GROUP_CONCAT('.$join_val_column.' SEPARATOR ", ")
									from '.$attribs->join_db_name.'
									where '.$attribs->join_db_name.'.'.$attribs->join_key_column.' IN
										( select '.$def_elmt->table_join.'.' . $def_elmt->element_name.'
										  from '.$def_elmt->table_join.'
										  where '.$def_elmt->table_join.'.parent_id='.$def_elmt->tab_name.'.id
										)
                                    '.$publish_query.'
								  ) AS `'.$def_elmt->tab_name . '___' . $def_elmt->element_name.'`';
					} else {
                        if ($attribs->database_join_display_type == "checkbox") {

                            $t = $def_elmt->tab_name.'_repeat_'.$def_elmt->element_name;
                            $query = '(
                                SELECT GROUP_CONCAT('.$t.'.'.$def_elmt->element_name.' SEPARATOR ", ")
                                FROM '.$t.'
                                WHERE '.$t.'.parent_id='.$def_elmt->tab_name.'.id
                                '.$publish_query.'
                              ) AS `'.$t.'`';
                        } else {
                            $query = '(
                                select DISTINCT '.$join_val_column.' 
                                from '.$attribs->join_db_name.' 
                                where `'.$attribs->join_db_name.'`.`'.$attribs->join_key_column.'`=`'.$def_elmt->tab_name . '`.`' . $def_elmt->element_name.'`
                                '.$publish_query.'
                                ) AS `'.$def_elmt->tab_name . '___' . $def_elmt->element_name.'`';
                        }
                    }

					$this->_elements_default[] = $query;

				} elseif ($def_elmt->element_plugin == 'cascadingdropdown') {
					$attribs = json_decode($def_elmt->element_attribs);
                    $cascadingdropdown_id = $attribs->cascadingdropdown_id;
                    $r1 = explode('___', $cascadingdropdown_id);
                    $cascadingdropdown_label = $attribs->cascadingdropdown_label;
                    $r2 = explode('___', $cascadingdropdown_label);
                    $select = !empty($attribs->cascadingdropdown_label_concat)?"CONCAT(".$attribs->cascadingdropdown_label_concat.")":$r2[1];
                    $from = $r2[0];
                    $where = $r1[1];

                   if (@$group_params->repeat_group_button == 1) {
                        $query = '(
                                    select GROUP_CONCAT('.$select.' SEPARATOR ", ")
                                    from '.$from.'
                                    where '.$where.' IN
                                        ( select '.$def_elmt->table_join.'.' . $def_elmt->element_name.'
                                          from '.$def_elmt->table_join.'
                                          where '.$def_elmt->table_join.'.parent_id='.$def_elmt->tab_name.'.id
                                        )
                                  ) AS `'.$def_elmt->tab_name . '___' . $def_elmt->element_name.'`';
                    } else {
                        $query = "(SELECT DISTINCT(".$select.") FROM ".$from." WHERE ".$where."=".$def_elmt->element_name." LIMIT 0,1) AS `".$def_elmt->tab_name . "___" . $def_elmt->element_name."`";
                    }

                    $query = preg_replace('#{thistable}#', $from, $query);
                    $query = preg_replace('#{my->id}#', $current_user->id, $query);
                    $query = preg_replace('{shortlang}', substr(JFactory::getLanguage()->getTag(), 0 , 2), $query);
                    $this->_elements_default[] = $query;

		        }elseif ($def_elmt->element_plugin == 'dropdown' || $def_elmt->element_plugin == 'radiobutton') {

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

				} else {
					if (@$group_params->repeat_group_button == 1) {
						$this->_elements_default[] = '(
														SELECT  GROUP_CONCAT('.$def_elmt->table_join.'.' . $def_elmt->element_name.'  SEPARATOR ", ")
														FROM '.$def_elmt->table_join.'
														WHERE '.$def_elmt->table_join.'.parent_id = '.$def_elmt->tab_name.'.id
													  ) AS `'.$def_elmt->table_join.'___' . $def_elmt->element_name.'`';
					} else {
						$this->_elements_default[] = $def_elmt->tab_name . '.' . $def_elmt->element_name.' AS '.$def_elmt->tab_name . '___' . $def_elmt->element_name;
					}
				}
			}
		}

		if (in_array('overall', $em_other_columns)) {
			$this->_elements_default[] = ' AVG(ee.overall) as overall ';
		}
		if (empty($col_elt)) {
			$col_elt = array();
		}
		if (empty($col_other)) {
			$col_other = array();
		}
		if (empty(@$this->_elements_default_name)) {
			$this->_elements_default_name = array();
		}

		$this->col = array_merge($col_elt, $col_other, $this->_elements_default_name);

		$elements_names = '"' . implode('", "', $this->col) . '"';
		$result = @EmundusHelperList::getElementsDetails($elements_names);

		$result = @EmundusHelperFiles::insertValuesInQueryResult($result, array("sub_values", "sub_labels"));

		$this->details = new stdClass();
		foreach ($result as $res) {
			$this->details->{$res->tab_name . '__' . $res->element_name} = array('element_id' => $res->element_id,
			                                                                     'plugin' => $res->element_plugin,
			                                                                     'attribs' => $res->params,
			                                                                     'sub_values' => $res->sub_values,
			                                                                     'sub_labels' => $res->sub_labels,
			                                                                     'group_by' => $res->tab_group_by);
		}
	}

	public function getElementsVar() {
		return $this->_elements;
	}


	/**
	 * Get list of decision elements
	 *
	 * @param   int show_in_list_summary get elements displayed in Fabrik List ; yes=1
	 * @param   int hidden get hidden elements ; yes=1
	 * @param   array code get elements from Decision form defined for programme list
	 *
	 * @return  mixed list of Fabrik element ID used in decision form
	 **@throws Exception
	 */
    public function getDecisionElementsName($show_in_list_summary=1, $hidden=0, $code = array(), $all = null) {
        $session = JFactory::getSession();
        $h_list = new EmundusHelperList;

        $jinput = JFactory::getApplication()->input;
        $view = $jinput->getString('view', null);

        $elements = array();

        if ($session->has('filt_params') || !empty($all)) {

            $filt_params = $session->get('filt_params');

            if ($view != 'export_select_columns' && is_array(@$filt_params['programme']) && count(@$filt_params['programme']) > 0) {
	            $programmes = $filt_params['programme'];
            } elseif (!empty($code)) {
	            $programmes = $code;
            } else {
	            return array();
            }

            foreach ($programmes as $value) {
                $groups = $this->getGroupsDecisionByProgramme($value);

                if (empty($groups)) {
                    $eval_elt_list = array();
                } else {
                    $eval_elt_list = $this->getElementsByGroups($groups, $show_in_list_summary, $hidden);

                    if (count($eval_elt_list)>0) {
                        foreach ($eval_elt_list as $eel) {
                            if(isset($eel->element_id) && !empty($eel->element_id))
                                $elements[] = $h_list->getElementsDetailsByID($eel->element_id)[0];
                        }
                    }
                }
            }
        }

        return $elements;
    }


	/**
	 * Get list of ALL decision elements
	 *
	 * @param   int displayed in Fabrik List ; yes=1
	 * @param   string code of the programme
	 *
	 * @return    array list of Fabrik element ID used in evaluation form
	 * @throws Exception
	 */
    public function getAllDecisionElements($show_in_list_summary=1, $programme_code) {
        $session = JFactory::getSession();

        if ($session->has('filt_params')) {
            $elements_id = array();
			$filt_params = $session->get('filt_params');

            if (is_array(@$filt_params['programme']) && $filt_params['programme'][0] != '%') {
                foreach ($filt_params['programme'] as $value) {
                    if ($value == $programme_code) {
                        $groups = $this->getGroupsDecisionByProgramme($value);
                        if (!empty($groups)) {
                            $eval_elt_list = $this->getElementsByGroups($groups, $show_in_list_summary); // $show_in_list_summary
                            if (count($eval_elt_list)>0) {
                                foreach ($eval_elt_list as $eel) {
                                    $elements_id[] = $eel->element_id;
                                }
                            }
                        }
                    }
                }
            } else {
				$groups = $this->getGroupsDecisionByProgramme($programme_code);
                if (!empty($groups)) {
                    $eval_elt_list = $this->getElementsByGroups($groups, $show_in_list_summary); // $show_in_list_summary
                    if (count($eval_elt_list)>0) {
                        foreach ($eval_elt_list as $eel) {
                            $elements_id[] = $eel->element_id;
                        }
                    }
                }
            }
        }
        return @$elements_id;
    }

	/**
	 *
	 * @return string
	 *
	 * @throws Exception
	 * @since version
	 */
	public function _buildContentOrderBy() {

		$menu = @JFactory::getApplication()->getMenu();
		$current_menu = $menu->getActive();
		$menu_params = $menu->getParams($current_menu->id);
	    $em_other_columns = explode(',', $menu_params->get('em_other_columns'));

        $filter_order = JFactory::getSession()->get('filter_order');
        $filter_order_Dir = JFactory::getSession()->get('filter_order_Dir');

        $can_be_ordering = array();
        if (count($this->_elements) > 0) {
            foreach ($this->_elements as $element) {
                if(!empty($element->table_join)) {
	                $can_be_ordering[] = $element->table_join.'___'.$element->element_name;
	                $can_be_ordering[] = $element->table_join.'.'.$element->element_name;
                }
                else {
                	$can_be_ordering[] = $element->tab_name.'___'.$element->element_name;
	                $can_be_ordering[] = $element->tab_name.'.'.$element->element_name;
                }
            }
        }

        $can_be_ordering[] = 'jos_emundus_campaign_candidature.id';
        $can_be_ordering[] = 'jos_emundus_campaign_candidature.fnum';
        $can_be_ordering[] = 'jos_emundus_campaign_candidature.status';
        $can_be_ordering[] = 'fnum';
        $can_be_ordering[] = 'status';
        $can_be_ordering[] = 'c.status';
        $can_be_ordering[] = 'name';
		$can_be_ordering[] = 'eta.id_tag';
		if (in_array('overall', $em_other_columns)) {
			$can_be_ordering[] = 'overall';
		}

        if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)) {
            return ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
        }

        return '';
    }

	/**
	 * @param   array  $multi_array
	 * @param          $sort_key
	 * @param   int    $sort
	 *
	 * @return array|int
	 *
	 * @since version
	 */
	public function multi_array_sort($multi_array = array(), $sort_key, $sort = SORT_ASC) {
		if (is_array($multi_array)) {
			foreach ($multi_array as $key => $row_array) {
				if (is_array($row_array)) {
					@$key_array[$key] = $row_array[$sort_key];
				} else {
					return -1;
				}
			}
		} else {
			return -1;
		}
		if (!empty($key_array)) {
			array_multisort($key_array, $sort, $multi_array);
		}
		return $multi_array;
	}

	public function getCampaign() {
		return @EmundusHelperFiles::getCampaign();
	}

	public function getCurrentCampaign() {
		return @EmundusHelperFiles::getCurrentCampaign();
	}

	public function getCurrentCampaignsID() {
		return @EmundusHelperFiles::getCurrentCampaignsID();
	}

	/**
	 * @param $user
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public function getProfileAcces($user) {
		$db = JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id=' . $user;
		$db->setQuery($query);
		return $db->loadResultArray();
	}

	/**
	 * @param $tab
	 * @param $elem
	 *
	 * @return array
	 *
	 * @since version
	 */
	public function setSubQuery($tab, $elem) {
		$search = JRequest::getVar('elements', NULL, 'POST', 'array', 0);
		$search_values = JRequest::getVar('elements_values', NULL, 'POST', 'array', 0);
		$search_other = JRequest::getVar('elements_other', NULL, 'POST', 'array', 0);
		$search_values_other = JRequest::getVar('elements_values_other', NULL, 'POST', 'array', 0);

		$db = JFactory::getDBO();

		$query = 'SELECT DISTINCT(#__emundus_users.user_id), ' . $tab . '.' . $elem . ' AS ' . $tab . '__' . $elem;
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
		          $this->details->{$tab . '__' . $elem}['group_by'] . '=#__users.id';
		$query = @EmundusHelperFiles::setWhere($search, $search_values, $query);
		$query = @EmundusHelperFiles::setWhere($search_other, $search_values_other, $query);
		$query = @EmundusHelperFiles::setWhere($this->_elements_default, $this->elements_values, $query);

		$db->setQuery($query);
		$obj = $db->loadObjectList();
		$list = array();
		$tmp = '';
		foreach ($obj as $unit) {
			if ($tmp != $unit->user_id) {
				$list[$unit->user_id] = @EmundusHelperList::getBoxValue($this->details->{$tab.'__'.$elem}, $unit->{$tab.'__'.$elem}, $elem);
			} else {
				$list[$unit->user_id] .= ','.@EmundusHelperList::getBoxValue($this->details->{$tab.'__'.$elem}, $unit->{$tab.'__'.$elem}, $elem);
			}
			$tmp = $unit->user_id;
		}
		return $list;
	}

	/**
	 * @param $search
	 *
	 * @return array|string
	 *
	 * @since version
	 */
	public function setSelect($search) {
		$cols = array();
		if (!empty($search)) {
			asort($search);
			$i = 0;
			foreach ($search as $c) {
				if (!empty($c)) {
					$tab = explode('.', $c);
					if ($tab[0] == 'jos_emundus_training') {
						$cols[] = ' search_' . $tab[0] . '.label as ' . $tab[1] . ' ';
					} else {
						if ($this->details->{$tab[0] . '__' . $tab[1]}['group_by']) {
							$this->subquery[$tab[0].'__'.$tab[1]] = $this->setSubQuery($tab[0], $tab[1]);
						} else {
							$cols[] = $c . ' AS ' . $tab[0] . '__' . $tab[1];
						}
					}
				}
				$i++;
			}
			if (count($cols > 0) && !empty($cols)) {
				$cols = implode(', ', $cols);
			}
		}
		return $cols;
	}

	/**
	 * @param $tab
	 * @param $joined
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function isJoined($tab, $joined) {
		// This function is just like in_array() but less optimized..
		foreach ($joined as $j) {
			if ($tab == $j) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $search
	 * @param $query
	 * @param $joined
	 *
	 * @return array
	 *
	 * @since version
	 */
	public function setJoins($search, $query, $joined) {
		$tables_list = array();
		if (!empty($search)) {
			$old_table = '';
			$i = 0;
			foreach ($search as $s) {
				$tab = explode('.', $s);
				if (count($tab) > 1) {
					if ($tab[0] != $old_table && !$this->isJoined($tab[0], $joined)) {
						if ($tab[0] == 'jos_emundus_groups_eval' || $tab[0] == 'jos_emundus_comments') {
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.applicant_id=#__users.id ';
						} elseif ($tab[0] == 'jos_emundus_evaluations' || $tab[0] == 'jos_emundus_final_grade' || $tab[0] == 'jos_emundus_academic_transcript' || $tab[0] == 'jos_emundus_bank' || $tab[0] == 'jos_emundus_files_request' || $tab[0] == 'jos_emundus_mobility') {
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.student_id=#__users.id ';
						} elseif ($tab[0] == "jos_emundus_training") {
							$query .= ' LEFT JOIN #__emundus_setup_teaching_unity AS search_'.$tab[0].' ON search_'.$tab[0].'.code=#__emundus_setup_campaigns.training ';
						} else {
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.user=#__users.id ';
						}
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
	 *
	 * @return string
	 *
	 * @throws Exception
	 * @since version
	 */
	public function _buildSelect(&$tables_list, &$tables_list_other, &$tables_list_default) {
		$current_user = JFactory::getUser();
		$search = $this->getState('elements');
		$search_other = $this->getState('elements_other');
		$schoolyears = $this->getState('schoolyear');
		$programmes = $this->getState('programme');
		$gid = $this->getState('groups');
		$uid = $this->getState('user');
		$miss_doc = $this->getState('missing_doc');
		$validate_application = $this->getState('validate');

		$menu = @JFactory::getApplication()->getMenu();
		$current_menu = $menu->getActive();
		$menu_params = $menu->getParams($current_menu->id);
		$this->validate_details = @EmundusHelperList::getElementsDetailsByID($menu_params->get('em_validate_id'));
		$col_validate = "";
		foreach ($this->validate_details as $vd) {
			$col_validate .= $vd->tab_name . '.' . $vd->element_name . ',';
		}
		$col_validate = substr($col_validate, 0, strlen($col_validate) - 1);

		$cols = $this->setSelect($search);
		$cols_other = $this->setSelect($search_other);
		$cols_default = $this->setSelect($this->_elements_default);

		$joined = array('jos_emundus_users', 'jos_users',
						'jos_emundus_setup_profiles',
						'jos_emundus_final_grade',
		                'jos_emundus_declaration');

		$query = 'SELECT #__emundus_users.user_id, #__emundus_users.user_id as user, #__emundus_users.user_id as id, #__emundus_users.lastname, #__emundus_users.firstname,
		#__users.name, #__users.registerDate, #__users.email, #__emundus_setup_profiles.id as profile, #__emundus_campaign_candidature.date_submitted,
		#__emundus_setup_campaigns.year as schoolyear, #__emundus_setup_campaigns.label, #__emundus_campaign_candidature.date_submitted, #__emundus_campaign_candidature.campaign_id';
		if (!empty($cols)) {
			$query .= ', ' . $cols;
		}
		if (!empty($cols_other)) {
			$query .= ', ' . $cols_other;
		}
		if (!empty($cols_default)) {
			$query .= ', ' . $cols_default;
		}
		if (!empty($col_validate)) {
			$query .= ', ' . $col_validate;
		}
		$query .= '	FROM #__emundus_campaign_candidature
					LEFT JOIN #__emundus_users ON #__emundus_declaration.user=#__emundus_users.user_id
					LEFT JOIN #__emundus_setup_campaigns ON #__emundus_setup_campaigns.id=#__emundus_campaign_candidature.campaign_id
					LEFT JOIN #__users ON #__users.id=#__emundus_users.user_id
					LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
					LEFT JOIN #__emundus_final_grade ON #__emundus_final_grade.student_id=#__emundus_users.user_id';

		$this->setJoins($search, $query, $joined);
		$this->setJoins($search_other, $query, $joined);
		$this->setJoins($this->_elements_default, $query, $joined);

		if (((isset($gid) && !empty($gid)) || (isset($uid) && !empty($uid))) && !$this->isJoined('jos_emundus_groups_eval', $joined)) {
			$query .= ' LEFT JOIN #__emundus_groups_eval ON #__emundus_groups_eval.applicant_id=#__users.id ';
		}

		if (!empty($miss_doc) && !$this->isJoined('jos_emundus_uploads', $joined)) {
			$query .= ' LEFT JOIN #__emundus_uploads ON #__emundus_uploads.user_id=#__users.id';
		}

		if (!empty($validate_application) && !$this->isJoined('jos_emundus_declaration', $joined)) {
			$query .= ' LEFT JOIN #__emundus_declaration ON #__emundus_declaration.user=#__users.id';
		}

		$query .= ' WHERE #__emundus_campaign_candidature.submitted = 1 AND #__users.block = 0 ';
		if (empty($schoolyears)) {
			$query .= ' AND #__emundus_campaign_candidature.year IN ("'.implode('","', $this->getCurrentCampaign()).'")';
		}

		if (!empty($programmes) && isset($programmes) && $programmes[0] != "%") {
			$query .= ' AND #__emundus_setup_campaigns.training IN ("'.implode('","', $programmes).'")';
		}

		if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id)) {
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
	public function setEvalList($search, &$eval_list, $head_val, $applicant) {
		if (!empty($search)) {
			foreach ($search as $c) {
				if (!empty($c)) {
					$name = explode('.', $c);
					if (!in_array($name[0] . '__' . $name[1], $head_val)) {

						if ($this->details->{$name[0] . '__' . $name[1]}['group_by'] && array_key_exists($name[0] . '__' . $name[1], $this->subquery) && array_key_exists($applicant->user_id, $this->subquery[$name[0] . '__' . $name[1]])) {
							$eval_list[$name[0] . '__' . $name[1]] = @EmundusHelperList::createHtmlList(explode(",",
								$this->subquery[$name[0] . '__' . $name[1]][$applicant->user_id]));
						} elseif ($name[0] == 'jos_emundus_training') {
							$eval_list[$name[1]] = $applicant->{$name[1]};
						} elseif (!$this->details->{$name[0] . '__' . $name[1]}['group_by']) {
							$eval_list[$name[0] . '__' . $name[1]] =
								@EmundusHelperList::getBoxValue($this->details->{$name[0] . '__' . $name[1]},
									$applicant->{$name[0] . '__' . $name[1]}, $name[1]);
						} else {
							$eval_list[$name[0].'__'.$name[1]] = $applicant->{$name[0].'__'.$name[1]};
						}

					}
				}
			}
		}
	}


	/**
	 * @param   array  $tableAlias
	 *
	 * @return array
	 *
	 * @since version
	 */
	private function _buildWhere($tableAlias = array()) {
		$session = JFactory::getSession();
        $params = $session->get('filt_params'); // came from search box
        $filt_menu = $session->get('filt_menu'); // came from menu filter (see EmundusHelperFiles::resetFilter)

        $db = JFactory::getDBO();

        if (!is_numeric(@$params['published']) || is_null(@$params['published'])) {
	        $params['published'] = 1;
        }

		$query = array('q' => '', 'join' => '');
		if (!empty($params)) {
			foreach ($params as $key => $value) {

				switch ($key) {
					case 'elements':
						if (!empty($value)) {
							foreach ($value as $k => $v) {
								$tab = explode('.', $k);

								if (isset($v['select'])) {
									$adv_select = $v['select'];
								}

								if (isset($v['value'])) {
									$v = $v['value'];
								}

								if (count($tab)>1 && !empty($v)) {

									if ($tab[0] == 'jos_emundus_training') {

										// Do not do LIKE %% search on elements that come from a <select>, we should get the exact value.
										if (isset($adv_select) && $adv_select) {
											$query['q'] .= ' AND search_'.$tab[0].'.id like "'.$v.'"';
										} else {
											$query['q'] .= ' AND search_'.$tab[0].'.id like "%'.$v.'%"';
										}

									} else {
                                        $query['q'] .= ' AND ';
                                        // Check if it is a join table
                                        $sql = 'SELECT join_from_table FROM #__fabrik_joins WHERE table_join like '.$db->Quote($tab[0]);
                                        $db->setQuery($sql);
                                        $join_from_table = $db->loadResult();
                                        if (!empty($join_from_table)) {
                                            $table = $join_from_table;
                                            $table_join = $tab[0];

	                                        // Do not do LIKE %% search on elements that come from a <select>, we should get the exact value.
	                                        if (isset($adv_select) && $adv_select) {
		                                        $query['q'] .= $table_join.'.'.$tab[1].' like "' . $v . '"';
	                                        } else {
		                                        $query['q'] .= $table_join.'.'.$tab[1].' like "%' . $v . '%"';
	                                        }

                                            if (!isset($query[$table])) {
                                                $query[$table] = true;
                                                if (!array_key_exists($table, $tableAlias) && !in_array($table, $tableAlias)) {
	                                                $query['join'] .= ' left join '.$table.' on '.$table.'.fnum like c.fnum ';
                                                }
                                            }
                                            if (!isset($query[$table_join])) {
                                                $query[$table_join] = true;
                                                if (!array_key_exists($table_join, $tableAlias) && !in_array($table_join, $tableAlias)) {
	                                                $query['join'] .= ' left join '.$table_join.' on '.$table.'.id='.$table_join.'.parent_id';
                                                }
                                            }
                                        } else {

	                                        $sql = 'SELECT plugin FROM #__fabrik_elements WHERE name like '.$db->Quote($tab[1]);
	                                        $db->setQuery($sql);
	                                        $res = $db->loadResult();
	                                        if ($res == "radiobutton" || $res == "dropdown" || $res == "databasejoin" || (isset($adv_select) && $adv_select)) {
		                                        $query['q'] .= $tab[0].'.'.$tab[1].' like "' . $v . '"';
	                                        } else {
		                                        $query['q'] .= $tab[0].'.'.$tab[1].' like "%' . $v . '%"';
	                                        }

                                            if (!isset($query[$tab[0]])) {
                                                $query[$tab[0]] = true;
                                                if (!array_key_exists($tab[0], $tableAlias) && !in_array($tab[0], $tableAlias)) {
	                                                $query['join'] .= ' left join '.$tab[0].' on '.$tab[0].'.fnum like c.fnum ';
                                                }
                                            }
                                        }
                                    }
								}
							}
						}
						break;
					case 'elements_other':
						if (!empty($value)) {
							foreach ($value as $k => $v) {
								if (!empty($v)) {
									$tab = explode('.', $k);
									if (count($tab)>1) {
										if ($tab[0]=='jos_emundus_training') {
											$query['q'] .= ' AND ';
											$query['q'] .= ' search_'.$tab[0].'.id like "%' . $v . '%"';
										} else {
											$query['q'] .= ' AND ';
											$query['q'] .= $tab[0].'.'.$tab[1].' like "%' . $v . '%"';

											if (!isset($query[$tab[0]])) {
												$query[$tab[0]] = true;
												if (!array_key_exists($tab[0], $tableAlias)) {
													$query['join'] .= ' left join '.$tab[0].' on '.$tab[0].'.fnum like c.fnum ';
												}
											}
										}
									}
								}
							}

						}
						break;
					case 's':
						if (!empty($value)) {
							$q = $this->_buildSearch($value, $tableAlias);
							foreach ($q['q'] as $v) {
								$query['q'] .= $v;
							}

                            foreach ($q['join'] as $u) {
	                            $query['join'] .= $u;
                            }

							if (isset($q['users'])) {
								$query['users'] = true;
							}

							if (isset($q['em_user'])) {
								$query['em_user'] = true;
							}
						}
						break;

					case 'finalgrade':
						if (!empty($value)) {
							$query['q'] .= ' and fg.final_grade like "%' . $value . '%"';
							if(!isset($query['final_g'])) {
								$query['final_g'] = true;
								if (!array_key_exists('jos_emundus_final_grade', $tableAlias)) {
									$query['join'] .= ' left join #__emundus_final_grade as fg on fg.fnum like c.fnum ';
								}
							}
						}
						break;

					case 'schoolyear':
						if (!empty($value)) {
							if (($value[0] == "%") || empty($value[0])) {
								$query['q'] .= '';
							} else {
								$query['q'] .= ' and esc.year IN ("' . implode('","', $value) . '") ';
							}
						}
						break;

					case 'programme':
						if (!empty($value)) {
							if ($value[0] == "%" || empty($value[0])) {
								$query['q'] .= ' ';
							} else {
								$query['q'] .= ' and sp.code IN ("' . implode('","', $value) . '") ';
							}
						}
						break;

					case 'campaign':
						if ($value) {
							$query['q'] .= ' AND esc.published=1 ';
							if ($value[0] == "%" || empty($value[0])) {
								$query['q'] .= ' ';
							} else {
								$query['q'] .= ' and esc.id IN (' . implode(',', $value) . ') ';
							}
						}
						break;

					case 'groups':
						if (!empty($value)) {
							$query['q'] .= ' and  (ge.group_id=' . $db->Quote($value) . ' OR ge.user_id IN (select user_id FROM #__emundus_groups WHERE group_id=' .$db->Quote($value) . ')) ';

							if (!isset($query['group_eval'])) {
								$query['group_eval'] = true;
								if (!array_key_exists('jos_emundus_groups_eval', $tableAlias)) {
									$query['join'] .= ' left join #__emundus_groups_eval as ge on ge.applicant_id = c.applicant_id and ge.campaign_id = c.campaign_id ';
								}
							}
						}
						break;

					case 'user':
						if (!empty($value)) {
							$query['q'] .= ' and (ge.user_id=' . $db->Quote($value) .
							          ' OR ge.group_id IN (select e.group_id FROM #__emundus_groups e WHERE e.user_id=' .
							          $db->Quote($value) . '))';
							if (!isset($query['group_eval'])) {
								$query['group_eval'] = true;
								if (!array_key_exists('jos_emundus_groups_eval', $tableAlias)) {
									$query['join'] .= ' left join #__emundus_groups_eval as ge on ge.applicant_id = c.applicant_id and ge.campaign_id = c.campaign_id ';
								}
							}
						}
						break;

					case 'missing_doc':
						if (!empty($value)) {
							$query['q'] .=' and (' . $value . ' NOT IN (SELECT attachment_id FROM #__emundus_uploads eup WHERE #__emundus_uploads.user_id = u.id)) ';
							if (!array_key_exists('jos_emundus_uploads', $tableAlias)) {
								$query['join'] = ' left join #__emundus_uploads on #__emundus_uploads.user_id = c.applicant_id ';
							}
						}
						break;

					case 'complete':
						if (!empty($value)) {
							if ($value == 1) {
								$query['q'] .= 'and #__users.id IN (SELECT user FROM #__emundus_declaration ed WHERE #__emundus_declaration.user = #__users.id) ';
							} else {
								$query['q'] .= 'and #__users.id NOT IN (SELECT user FROM #__emundus_declaration ed WHERE #__emundus_declaration.user = #__users.id) ';
							}
						}
						break;

					case 'validate':
							if (!empty($value)) {
								if ($value == 1) {
									$query['q'] .= ' and #__emundus_declaration.validated = 1 ';
								} else {
									$query['q'] .= ' and #__emundus_declaration.validated = 0 ';
								}
							}
						break;

					case 'status':
						if ($value) {
                            $filt_menu_defined = ( isset($filt_menu['status'][0]) && $filt_menu['status'][0] != '' && $filt_menu['status'] != "%" ) ? true : false;

                            // session filter is empty
                            if ($value[0] == "%" || !isset($value[0]) || $value[0] == '') {
                                if (!$filt_menu_defined) {
	                                $query['q'] .= ' ';
                                } else {
	                                $query['q'] .= ' and c.status IN ('.implode(',', $filt_menu['status']).') ';
                                }
                            } else {
                                // Check if session filter exist in menu filter, if at least one session filter not in menu filter, reset to menu filter
                                $diff = array();
                                if (is_array($value) && $filt_menu_defined) {
	                                $diff = array_diff($value, $filt_menu['status']);
                                }

                                if (count($diff) == 0) {
	                                $query['q'] .= ' and c.status IN ('.implode(',', $value).') ';
                                } else {
	                                $query['q'] .= ' and c.status IN ('.implode(',', $filt_menu['status']).') ';
                                }
                            }
                        }
						break;

					case 'tag':
                        if ($value) {
                            if ($value[0] == "%" || !isset($value[0]) || $value[0] === '') {
	                            $query['q'] .= ' ';
                            } else {

	                            if (isset($filt_menu['tag'][0]) && $filt_menu['tag'][0] != '' && $filt_menu['tag'] != "%") {
		                            // This allows hiding of files by tag.
		                            $filt_menu_not = array_filter($filt_menu['tag'], function($e) {
			                            return strpos($e, '!') === 0;
		                            });
	                            }

	                            // This allows hiding of files by tag.
	                            $not_in = array_filter($value, function($e) {
		                            return strpos($e, '!') === 0;
	                            });

	                            if (is_array($not_in) && !empty($filt_menu_not)) {
		                            $not_in = array_unique(array_merge($not_in, $filt_menu_not));
	                            }

	                            if (!empty($not_in)) {
		                            $value = array_diff($value, $not_in);
		                            $not_in = array_map(function($v) {
			                            return ltrim($v, '!');
		                            }, $not_in);
		                            $query['q'] .= ' and c.fnum NOT IN (SELECT cc.fnum FROM jos_emundus_campaign_candidature AS cc LEFT JOIN jos_emundus_tag_assoc as ta ON ta.fnum = cc.fnum WHERE ta.id_tag IN (' . implode(',', $not_in) . ')) ';
	                            }

	                            if (!empty($value)) {
		                            $query['q'] .= ' and eta.id_tag IN ('.implode(',', $value).') ';
	                            }
                            }
                        }
                        break;

					case 'group_assoc':
						if (!empty($value)) {
							$query['join'] .= ' 
	                            LEFT JOIN #__emundus_group_assoc as ga on ga.fnum = c.fnum 
	                            LEFT JOIN #__emundus_setup_groups_repeat_course as grc on grc.course LIKE esc.training 
	                            LEFT JOIN #__emundus_setup_groups as sg on grc.parent_id = sg.id ';
							$query['q'] .= ' and (ga.group_id IN ('.implode(',', $value).') OR sg.id IN ('.implode(',', $value).')) ';
						}
						break;

                    case 'published':
                        if ($value == "-1") {
                            $query['q'] .= ' and c.published=-1 ';
                        } elseif ($value == "0") {
                            $query['q'] .= ' and c.published=0 ';
                        } else {
                            $query['q'] .= ' and c.published=1 ';
                        }
                        break;
				}
			}
		}
		// force menu filter
        if (!empty($filt_menu['status']) && $filt_menu['status'][0] != "%") {
            $query['q'] .= ' AND c.status IN ("' . implode('","', $filt_menu['status']) . '") ';
        }

        if (isset($filt_menu['programme'][0]) && $filt_menu['programme'][0] == "%"){
			$sql_code = '1=1';
			$and = ' AND ';
		} elseif(isset($filt_menu['programme'][0]) && !empty($filt_menu['programme'][0])) {
			// ONLY FILES LINKED TO MY GROUPS OR TO MY ACCOUNT
			// if(count($this->code)>0)
			$sql_code = ' sp.code IN ("'.implode('","', $this->code).'") ';
			$and = ' OR ';
		} else {
			if ($filt_menu['programme'][0] != "" && count($filt_menu['programme']) > 0) {
	            $sql_code = ' sp.code in ("'.implode('","', $filt_menu['programme']).'") ';
	            $and = ' AND ';
	        }
		}
		$sql_fnum = '';
		if (count($this->fnum_assoc) > 0) {
			$sql_fnum = $and.' c.fnum IN ("'.implode('","', $this->fnum_assoc).'") ';
		}

		if (!empty($sql_code) || !empty($sql_fnum)) {
            $query['q'] .= ' AND ('.$sql_code.' '.$sql_fnum.') ';
        } else {
			$query['q'] .= ' AND (1=2) ';
		}

        return $query;
	}

	/**
	 * @param          $str_array
	 * @param   array  $tableAlias
	 *
	 * @return array
	 *
	 * @since version
	 */
	private function _buildSearch($str_array, $tableAlias = array()) {

		$q = array('q' => array(), 'join' => array());
		$queryGroups = [
			'all' => '',
			'fnum' => '',
			'id' => '',
			'email' => '',
			'username' => '',
			'lastname' => '',
			'firstname' => ''
		];
		$first = true;

		foreach ($str_array as $str) {

			$val = explode(': ', $str);

			if ($val[0] == "ALL") {

				if (is_numeric($val[1])) {

					//possibly fnum ou uid
					if (!empty($queryGroups['all'])) {
						$queryGroups['all'] .= ' or (u.id = ' . $val[1] . ' or c.fnum like "'.$val[1].'%") ';
					} else {
						if ($first) {
							$queryGroups['all'] .= ' and (((u.id = ' . $val[1] . ' or c.fnum like "'.$val[1].'%") ';
							$first = false;
						} else {
							$queryGroups['all'] .= ' and ((u.id = ' . $val[1] . ' or c.fnum like "'.$val[1].'%") ';
						}

					}

					if (!in_array('jos_users', $tableAlias)) {
						$q['join'][] .= ' left join #__users as u on u.id = c.applicant_id ';
					}

					$q['users'] = true;

				} else {

					if (filter_var($val[1], FILTER_VALIDATE_EMAIL) !== false) {
						//the request is an email
						if (!empty($queryGroups['all'])) {
							$queryGroups['all'] .= ' or (u.email = "'.$val[1].'") ';
						} else {
							if ($first) {
								$queryGroups['all'] .= ' and (((u.email = "'.$val[1].'") ';
								$first = false;
							} else {
								$queryGroups['all'] .= ' and ((u.email = "'.$val[1].'") ';
							}

						}

						if (!in_array('jos_users', $tableAlias)) {
							$q['join'][] .= ' left join #__users as u on u.id = c.applicant_id ';
						}

						$q['users'] = true;

					} else {

						if (!empty($queryGroups['all'])) {
							$queryGroups['all'] .= ' or (eu.lastname LIKE "%' . ($val[1]) . '%" OR eu.firstname LIKE "%' . ($val[1]) . '%" OR u.email LIKE "%' . ($val[1]) . '%" OR u.username LIKE "%' . ($val[1]) . '%" ) ';
						} else {
							if ($first) {
								$queryGroups['all'] .= ' and (((eu.lastname LIKE "%' . ($val[1]) . '%" OR eu.firstname LIKE "%' . ($val[1]) . '%" OR u.email LIKE "%' . ($val[1]) . '%" OR u.username LIKE "%' . ($val[1]) . '%" ) ';
								$first = false;
							} else {
								$queryGroups['all'] .= ' and ((eu.lastname LIKE "%' . ($val[1]) . '%" OR eu.firstname LIKE "%' . ($val[1]) . '%" OR u.email LIKE "%' . ($val[1]) . '%" OR u.username LIKE "%' . ($val[1]) . '%" ) ';
							}

						}

						if (!in_array('jos_users', $tableAlias)) {
							$q['join'][] .= ' left join #__users as u on u.id = c.applicant_id';
							$q['users'] = true;
						}

						if (!in_array('jos_emundus_users', $tableAlias)){
							$q['join'][] .= ' left join #__emundus_users as eu on eu.user_id = c.applicant_id ';
							$q['em_user'] = true;
						}
					}
				}
			}


			if ($val[0] == "FNUM" && is_numeric($val[1])) {
				//possibly fnum ou uid
				if (!empty($queryGroups['fnum'])) {
					$queryGroups['fnum'] .= ' or (c.fnum like "'.$val[1].'%") ';
				} else {
					if ($first) {
						$queryGroups['fnum'] .= ' and (((c.fnum like "'.$val[1].'%") ';
						$first = false;
					} else {
						$queryGroups['fnum'] .= ' and ((c.fnum like "'.$val[1].'%") ';
					}
				}

				if (!in_array('jos_users', $tableAlias)) {
					$q['join'][] = ' left join #__users as u on u.id = c.applicant_id ';
				}
				$q['users'] = true;
			}


			if ($val[0] == "ID" && is_numeric($val[1])) {
				//possibly fnum ou uid
				if (!empty($queryGroups['id'])) {
					$queryGroups['id'] .= ' or (u.id = ' . $val[1] . ') ';
				} else {
					if ($first) {
						$queryGroups['id'] .= ' and (((u.id = ' . $val[1] . ') ';
						$first = false;
					} else {
						$queryGroups['id'] .= ' and ((u.id = ' . $val[1] . ') ';
					}
				}

				if (!in_array('jos_users', $tableAlias)) {
					$q['join'][] = ' left join #__users as u on u.id = c.applicant_id ';
				}
				$q['users'] = true;
			}


			if ($val[0] == "EMAIL") {
				//the request is an email
				if (!empty($queryGroups['email'])) {
					$queryGroups['email'] .= ' or (u.email like "%'.$val[1].'%") ';
				} else {
					if ($first) {
						$queryGroups['email'] .= ' and (((u.email like "%'.$val[1].'%") ';
						$first = false;
					} else {
						$queryGroups['email'] .= ' and ((u.email like "%'.$val[1].'%") ';
					}
				}

				if (!in_array('jos_users', $tableAlias)) {
					$q['join'][] = ' left join #__users as u on u.id = c.applicant_id ';
				}

				$q['users'] = true;
			}


			if ($val[0] == "USERNAME") {
				//the request is an username
				if (!empty($queryGroups['username'])) {
					$queryGroups['username'] .= ' or (u.username LIKE "%' . ($val[1]) . '%" ) ';
				} else {
					if ($first) {
						$queryGroups['username'] .= ' and (((u.username LIKE "%' . ($val[1]) . '%" ) ';
						$first = false;
					} else {
						$queryGroups['username'] .= ' and ((u.username LIKE "%' . ($val[1]) . '%" ) ';
					}
				}

				if (!in_array('jos_users', $tableAlias)) {
					$q['join'][] = ' left join #__users as u on u.id = c.applicant_id ';
				}
				$q['users'] = true;
			}

			if ($val[0] == "LAST_NAME") {
				//the request is a lastname
				if (!empty($queryGroups['lastname'])) {
					$queryGroups['lastname'] .= ' or (eu.lastname LIKE "%' . ($val[1]) . '%" ) ';
				} else {
					if ($first) {
						$queryGroups['lastname'] .= ' and (((eu.lastname LIKE "%' . ($val[1]) . '%" ) ';
						$first = false;
					} else {
						$queryGroups['lastname'] .= ' and ((eu.lastname LIKE "%' . ($val[1]) . '%" ) ';
					}
				}

				if (!in_array('jos_emundus_users', $tableAlias)){
					$q['join'][] .= ' left join #__emundus_users as eu on eu.user_id = c.applicant_id ';
					$q['em_user'] = true;
				}
			}

			if ($val[0] == "FIRST_NAME") {
				//the request is a firstname
				if (!empty($queryGroups['firstname'])) {
					$queryGroups['firstname'] .= ' or (eu.firstname LIKE "%' . ($val[1]) . '%" ) ';
				} else {
					if ($first) {
						$queryGroups['firstname'] .= ' and (((eu.firstname LIKE "%' . ($val[1]) . '%" ) ';
						$first = false;
					} else {
						$queryGroups['firstname'] .= ' and ((eu.firstname LIKE "%' . ($val[1]) . '%" ) ';
					}
				}

				if (!in_array('jos_emundus_users', $tableAlias)) {
					$q['join'][] .= ' left join #__emundus_users as eu on eu.user_id = c.applicant_id ';
					$q['em_user'] = true;
				}
			}
		}

		// Close all group parentheses.
		foreach ($queryGroups as $k => $v) {
			if (!empty($v)) {
				$queryGroups[$k] .= ')';
			}
		}
		$query = $queryGroups['all'].$queryGroups['fnum'].$queryGroups['id'].$queryGroups['email'].$queryGroups['username'].$queryGroups['lastname'].$queryGroups['firstname'];

		if (!empty($query)) {
			$query .= ')';
		}

		$q['q'][] = $query;
		return $q;
	}

	/**
	 * @param null  $current_fnum
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 * @since version
	 */
	public function getUsers($current_fnum = null) {

		$session = JFactory::getSession();
		$app = JFactory::getApplication();
		$dbo = $this->getDbo();

		$current_menu = $app->getMenu()->getActive();
		$menu_params = $current_menu->params;
		$em_other_columns = explode(',', $menu_params->get('em_other_columns'));

		$query = 'select c.fnum, ss.step, ss.value as status, ss.class as status_class, concat(upper(trim(eu.lastname))," ",eu.firstname) AS name ';
		// prevent double left join on query
		$lastTab = [
			'#__emundus_setup_status', 'jos_emundus_setup_status',
			'#__emundus_setup_programmes', 'jos_emundus_setup_programmes',
			'#__emundus_setup_campaigns', 'jos_emundus_setup_campaigns',
			'#__emundus_final_grade', 'jos_emundus_final_grade',
			'#__emundus_users', 'jos_emundus_users',
			'#__users', 'jos_users',
			'#__emundus_tag_assoc', 'jos_emundus_tag_assoc'
		];
		if (in_array('overall', $em_other_columns)) {
			$lastTab[] = ['#__emundus_evaluations', 'jos_emundus_evaluations'];
		}

		$leftJoin = '';

		if (count($this->_elements)>0) {
			foreach ($this->_elements as $elt) {
				if (!isset($lastTab)) {
					$lastTab = array();
				}
				if (!in_array($elt->tab_name, $lastTab)) {
					$leftJoin .= 'left join ' . $elt->tab_name .  ' ON '. $elt->tab_name .'.fnum = c.fnum ';
				}
				if(!empty($elt->table_join)) {
			        $lastTab[] = $elt->table_join;
					$group_by .= ', '.$elt->table_join.'___'.$elt->element_name;
                } else {
					$lastTab[] = $elt->tab_name;
					$group_by .= ', '.$elt->tab_name.'___'.$elt->element_name;
				}
			}
		}
		if (count($this->_elements_default) > 0) {
			$query .= ', '.implode(',', $this->_elements_default);
		}
		$query .= ', jos_emundus_final_grade.id AS evaluation_id, CONCAT(eue.lastname," ",eue.firstname) AS evaluator';

		$query .= ' FROM #__emundus_campaign_candidature as c
					LEFT JOIN #__emundus_setup_status as ss on ss.step = c.status
					LEFT JOIN #__emundus_setup_campaigns as esc on esc.id = c.campaign_id
					LEFT JOIN #__emundus_setup_programmes as sp on sp.code = esc.training
					LEFT JOIN #__emundus_users as eu on eu.user_id = c.applicant_id
					LEFT JOIN #__users as u on u.id = c.applicant_id
					LEFT JOIN #__emundus_final_grade as jos_emundus_final_grade on jos_emundus_final_grade.fnum = c.fnum
					LEFT JOIN #__emundus_tag_assoc as eta on eta.fnum=c.fnum  ';

		if (in_array('overall', $em_other_columns)) {
			$query .= ' LEFT JOIN #__emundus_evaluations as ee on ee.fnum = c.fnum ';
		}


		$q = $this->_buildWhere($lastTab);

		if (!empty($leftJoin)) {
			$query .= $leftJoin;
		}
		$query .= ' LEFT JOIN #__emundus_users as eue on eue.user_id = jos_emundus_final_grade.user ';
		$query .= $q['join'];
		$query .= ' where c.status > 0 ' . $q['q'];

		if (isset($current_fnum) && !empty($current_fnum)) {
			$query .= ' AND c.fnum like '.$dbo->Quote($current_fnum);
		}

		$query .= ' GROUP BY c.fnum';
		$query .= $this->_buildContentOrderBy();
		$dbo->setQuery($query);

		try {
			$res = $dbo->loadAssocList();
			$this->_applicants = $res;

			$limit = $session->get('limit');

			$limitStart = $session->get('limitstart');
			if ($limit > 0) {
				$query .= " limit $limitStart, $limit ";
			}

			$dbo->setQuery($query);
			return $dbo->loadAssocList();

		} catch (Exception $e) {
			echo $e->getMessage();
			JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
		}
	}

	// get elements by groups
	// @params string List of Fabrik groups comma separated
	function getElementsByGroups($groups, $show_in_list_summary=1, $hidden=0){
		return @EmundusHelperFilters::getElementsByGroups($groups, $show_in_list_summary, $hidden);
	}

	// get ALL elements by groups
	// @params string List of Fabrik groups comma separated
	function getAllElementsByGroups($groups){
		return @EmundusHelperFilters::getAllElementsByGroups($groups);
	}


	public function getActionsACL()
	{
		return $this->_files->getActionsACL();
	}

	public function getDefaultElements()
	{
		return $this->_elements;
	}

	public function getSelectList()
	{
		return $this->_files->getSelectList();
	}

	public function getProfiles()
	{
		return $this->_files->getProfiles();
	}

	public function getProfileByID($id)
	{
		return $this->_files->getProfileByID($id);
	}

	public function getProfilesByIDs($ids)
	{
		return $this->_files->getProfilesByIDs($ids);
	}

	public function getAuthorProfiles()
	{
		return $this->_files->getAuthorProfiles();
	}

	public function getApplicantsProfiles()
	{
		return $this->_files->getApplicantsProfiles();
	}

	public function getApplicantsByProfile($profile)
	{
		return $this->_files->getApplicantsByProfile($profile);
	}


	public function getAuthorUsers()
	{
		return $this->_files->getAuthorUsers();
	}

	public function getMobility()
	{
		return $this->_files->getMobility();
	}

	public function getElements()
	{
		return $this->_files->getElements();
	}

	public function getElementsName()
	{
		return $this->_files->getElementsName();
	}

	public function getTotal()
	{
		if (empty($this->_total))
			$this->_total = count($this->_applicants);
		return $this->_total;
	}

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

    public function getPageNavigation() : string {
        if ($this->getPagination()->pagesTotal <= 1) {
          return '';
        }

        $pageNavigation = "<div class='em-container-pagination-selectPage'>";
        $pageNavigation .= "<ul class='pagination pagination-sm'>";
        $pageNavigation .= "<li><a href='#em-data' id='" . $this->getPagination()->pagesStart . "'><span class='material-icons'>navigate_before</span></a></li>";
        if ($this->getPagination()->pagesTotal > 15) {
            for ($i = 1; $i <= 5; $i++ ) {
                $pageNavigation .= "<li ";
                if ($this->getPagination()->pagesCurrent == $i) {
                    $pageNavigation .= "class='active'";
                }
                $pageNavigation .= "><a id='" . $i . "' href='#em-data'>" . $i . "</a></li>";
            }
            $pageNavigation .= "<li class='disabled'><span>...</span></li>";
            if ($this->getPagination()->pagesCurrent <= 5) {
                for ($i = 6; $i <= 10; $i++ ) {
                    $pageNavigation .= "<li ";
                    if ($this->getPagination()->pagesCurrent == $i) {
                        $pageNavigation .= "class='active'";
                    }
                    $pageNavigation .= "><a id=" . $i . " href='#em-data'>" . $i . "</a></li>";
                }
            } else {
                for ( $i = $this->getPagination()->pagesCurrent - 2 ; $i <= $this->getPagination()->pagesCurrent + 2 ; $i++) {
                    if ( $i <= $this->getPagination()->pagesTotal ) {
                        $pageNavigation .= "<li ";
                        if ( $this->getPagination()->pagesCurrent == $i ) {
                            $pageNavigation .= "class='active'";
                        }
                        $pageNavigation .= "><a id=" . $i . " href='#em-data'>" . $i . "</a></li>";
                    }
                }
            }
            $pageNavigation .= "<li class='disabled'><span>...</span></li>";
            for ( $i = $this->getPagination()->pagesTotal - 4 ; $i <= $this->getPagination()->pagesTotal ; $i++ ) {
                $pageNavigation .= "<li ";
                if ( $this->getPagination()->pagesCurrent == $i ) {
                    $pageNavigation .= "class='active'";
                }
                $pageNavigation .= "><a id='" . $i . "' href='#em-data'>" . $i . "</a></li>";
            }
        } else {
            for ( $i = 1 ; $i <= $this->getPagination()->pagesStop ; $i++) {
                $pageNavigation .= "<li ";
                if ( $this->getPagination()->pagesCurrent == $i ) {
                    $pageNavigation .= "class='active'";
                }
                $pageNavigation .= "><a id='" . $i . "' href='#em-data'>" . $i . "</a></li>";
            }
        }
        $pageNavigation .= "<li><a href='#em-data' id='" .$this->getPagination()->pagesTotal . "'><span class='material-icons'>navigate_next</span></a></li></ul></div>";

        return $pageNavigation;
    }

	// get applicant columns
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

    // get string of fabrik group ID use for evaluation form
    public function getGroupsEvalByProgramme($code){
        $db = $this->getDbo();
        $query = 'select fabrik_group_id from #__emundus_setup_programmes where code like '.$db->Quote($code);
        try
        {
            if(!empty($code)) {
                $db->setQuery($query);
                return $db->loadResult();
            } else return null;
        }
        catch(Exception $e)
        {
            throw $e;
        }

    }


    // get string of fabrik group ID use for evaluation form
    public function getGroupsDecisionByProgramme($code){
        $db = $this->getDbo();
        $query = 'select fabrik_decision_group_id from #__emundus_setup_programmes where code like '.$db->Quote($code);
        try
        {
            if(!empty($code)) {
                $db->setQuery($query);
                return $db->loadResult();
            } else return null;
        }
        catch(Exception $e)
        {
            throw $e;
        }

    }

	public function getSchoolyears()
	{
		return $this->_files->getSchoolyears();
	}

	public function getAllActions()
	{

		return $this->_files->getAllActions();
	}

	public function getEvalGroups()
	{
		return $this->_files->getEvalGroups();
	}

	public function shareGroups($groups, $actions, $fnums)
	{
		return $this->_files->shareGroups($groups, $actions, $fnums);
	}

	public function shareUsers($users, $actions, $fnums)
	{
		return $this->_files->shareUsers($users, $actions, $fnums);
	}

	public function getAllTags()
	{
		return $this->_files->getAllTags();
	}

	public function getAllStatus()
	{
		return $this->_files->getAllStatus();
	}

	public function tagFile($fnums, $tag)
	{
		return $this->_files->tagFile($fnums, $tag);
	}

	public function getTaggedFile($tag = null)
	{
		return $this->_files->getTaggedFile($tag = null);
	}

	public function updateState($fnums, $state)
	{
		return $this->_files->updateState($fnums, $state);
	}

	public function getPhotos()
	{
		return $this->_files->getPhotos();
	}

	public function getEvaluatorsFromGroup()
	{
		return $this->_files->getEvaluatorsFromGroup();
	}
	public function getEvaluators()
	{
		return $this->_files->getEvaluators();
	}

	public function unlinkEvaluators($fnum, $id, $isGroup)
	{
		return $this->_files->unlinkEvaluators($fnum, $id, $isGroup);
	}

	public function getFnumInfos($fnum)
	{
		return $this->_files->getFnumInfos($fnum);
	}

	public function changePublished($fnum, $published = -1)
	{
		return $this->_files->changePublished($fnum, $published = -1);
	}

	public function getAllFnums()
	{
		return $this->_files->getAllFnums();
	}

	/*
	* 	Get values of elements by list of files numbers
	*	@param fnums 	List of application files numbers
	*	@param elements 	array of element to get value
	* 	@return array
	*/
	public function getFnumArray($fnums, $elements)
	{
		return $this->_files->getFnumArray($fnums, $elements);
	}

	public function getEvalsByFnum($fnums)
	{
		return $this->_files->getEvalsByFnum($fnums);
	}

	public function getCommentsByFnum($fnums)
	{
		return $this->_files->getCommentsByFnum($fnums);
	}

	public function getFilesByFnums($fnums)
	{
		return $this->_files->getFilesByFnums($fnums);
	}

	/*
	* 	Get list of expert emails send by applicant for programme SU convergence
	*	@param fnum 		Application File number
	*	@param select 		Select elements
	*	@param table 		table and join table
	* 	@return array
	*/
	function getExperts($fnum, $select, $table) {
		$query = "SELECT ".$select." FROM ".$table." WHERE `fnum` like ".$this->_db->Quote($fnum);
		$this->_db->setQuery($query);
		return $this->_db->loadAssocList();
	}

	/*
	* 	Get evaluations for fnum
	*	@param fnum 		Application File number
	* 	@return array
	*/
	function getEvaluationsFnum($fnum) {
		$query = 'SELECT *
					FROM #__emundus_evaluations ee
					WHERE ee.fnum like '.$this->_db->Quote($fnum);
//die(str_replace('#_', 'jos', $query));
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

    /*
    * 	Get evaluations for fnum done by a user
    *	@param fnum 		Application File number
    *	@param user 		user
    * 	@return array
    */
    function getEvaluationsFnumUser($fnum, $user) {
        try {
            $query = 'SELECT *
					FROM #__emundus_evaluations ee
					WHERE ee.fnum like ' . $this->_db->Quote($fnum) . ' AND user = ' . $user;
//die(str_replace('#_', 'jos', $query));
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    /*
* 	Get evaluations for fnum done by a user
*	@param fnum 		Application File number
*	@param user 		user
* 	@return array
*/
    function getDecisionFnum($fnum) {
        try {
            $query = 'SELECT *
					FROM #__emundus_final_grade efg
					WHERE efg.fnum like ' . $this->_db->Quote($fnum);
//die(str_replace('#_', 'jos', $query));
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

	function getLettersTemplate($eligibility, $training) {
		$query = "SELECT * FROM #__emundus_setup_letters WHERE status=".$eligibility." AND training=".$this->_db->Quote($training);
		$this->_db->setQuery($query);
		return $this->_db->loadAssocList();
	}

	function getLettersTemplateByID($id) {
		try {
            $query = "SELECT * FROM #__emundus_setup_letters WHERE id=" . $id;
            $this->_db->setQuery($query);
            return $this->_db->loadAssocList();
        }
        catch(Exception $e)
        {
		    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            echo $e->getMessage();
        }
	}

    /*
    * 	Get evaluations form ID By programme code
    *	@param code 		code of the programme
    * 	@return int
    */
    function getEvaluationFormByProgramme($code=null) {
        if ($code === NULL) {
            $session = JFactory::getSession();
            if ($session->has('filt_params'))
            {
                $filt_params = $session->get('filt_params');
                if (count(@$filt_params['programme'])>0) {
                    $code = $filt_params['programme'][0];
                }
            }
        }
        try {
            $query = 'SELECT ff.form_id
					FROM #__fabrik_formgroup ff
					WHERE ff.group_id IN (SELECT fabrik_group_id FROM #__emundus_setup_programmes WHERE code like ' .
                $this->_db->Quote($code) . ')';
//die(str_replace('#_', 'jos', $query));
            $this->_db->setQuery($query);

            return $this->_db->loadResult();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    /*
* 	Get Decision form ID By programme code
*	@param code 		code of the programme
* 	@return int
*/
    function getDecisionFormByProgramme($code=null) {
        if ($code === NULL) {
            $session = JFactory::getSession();
            if ($session->has('filt_params'))
            {
                $filt_params = $session->get('filt_params');
                if (count(@$filt_params['programme'])>0) {
                    $code = $filt_params['programme'][0];
                }
            }
        }
        try {
            $query = 'SELECT ff.form_id
					FROM #__fabrik_formgroup ff
					WHERE ff.group_id IN (SELECT fabrik_decision_group_id FROM #__emundus_setup_programmes WHERE code like ' .
                $this->_db->Quote($code) . ')';
//die(str_replace('#_', 'jos', $query));
            $this->_db->setQuery($query);

            return $this->_db->loadResult();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }
}

?>
