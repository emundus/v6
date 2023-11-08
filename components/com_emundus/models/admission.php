<?php
/**
 * @package        Joomla
 * @subpackage     eMundus
 * @link           http://www.emundus.fr
 * @copyright      Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license        GNU/GPL
 * @author         Yoan Durand
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
require_once(JPATH_SITE . '/components/com_emundus/helpers/files.php');
require_once(JPATH_SITE . '/components/com_emundus/helpers/list.php');
require_once(JPATH_SITE . '/components/com_emundus/helpers/access.php');
require_once(JPATH_SITE . '/components/com_emundus/models/files.php');

class EmundusModelAdmission extends JModelList
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
	public function __construct()
	{

		parent::__construct();

		$this->_files = new EmundusModelFiles;
		$db           = JFactory::getDbo();
		$mainframe    = JFactory::getApplication();

		$current_user = JFactory::getUser();

		// Get current menu parameters
		$menu         = @JFactory::getApplication()->getMenu();
		$current_menu = $menu->getActive();

		/*
		** @TODO : gestion du cas Itemid absent à prendre en charge dans la vue
		*/

		if (empty($current_menu)) {
			return false;
		}

		$menu_params     = $menu->getParams($current_menu->id);
		$em_blocks_names = explode(',', $menu_params->get('em_blocks_names'));
		$session         = JFactory::getSession();

		if (!$session->has('filter_order') || $session->get('filter_order') == 'c.id') {
			if (in_array('overall', $em_blocks_names)) {

				$session->set('filter_order', 'overall');
				$session->set('filter_order_Dir', 'desc');

			}
			else {

				$session->set('filter_order', 'c.id');
				$session->set('filter_order_Dir', 'desc');

			}
		}

		if (!$session->has('limit')) {
			$limit      = $mainframe->getCfg('list_limit');
			$limitstart = 0;
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$session->set('limit', $limit);
			$session->set('limitstart', $limitstart);

		}
		else {

			$limit      = intval($session->get('limit'));
			$limitstart = intval($session->get('limitstart'));
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$session->set('limit', $limit);
			$session->set('limitstart', $limitstart);
		}

		$col_elt   = $this->getState('elements');
		$col_other = $this->getState('elements_other');

		$this->elements_id = $menu_params->get('em_elements_id');

		// get evaluation element
		$show_in_list_summary = 1;
		$hidden               = 0;
		$elements_eval        = $this->getAllAdmissionElements($show_in_list_summary, $hidden);

		if (!empty($elements_eval)) {
			$this->elements_id .= implode(',', $elements_eval);
		}

		if ($session->has('adv_cols')) {
			$adv = $session->get('adv_cols');
			if (!empty($adv)) {
				$this->elements_id .= ',' . implode(',', $adv);
			}
		}

		$this->elements_values   = explode(',', $menu_params->get('em_elements_values'));
		$this->_elements_default = array();
		$this->_elements         = @EmundusHelperFiles::getElementsName($this->elements_id);

		if (!empty($this->_elements)) {
			foreach ($this->_elements as $def_elmt) {
				$group_params = json_decode($def_elmt->group_attribs);

				if ($def_elmt->element_plugin == 'date') {
					if ($group_params->repeat_group_button == 1) {
						$this->_elements_default[] = '(
														SELECT  GROUP_CONCAT(DATE_FORMAT(' . $def_elmt->table_join . '.' . $def_elmt->element_name . ', "%d/%m/%Y %H:%i:%m") SEPARATOR ", ")
														FROM ' . $def_elmt->table_join . '
														WHERE ' . $def_elmt->table_join . '.parent_id = ' . $def_elmt->tab_name . '.id
													  ) AS `' . $def_elmt->table_join . '___' . $def_elmt->element_name . '`';
					}
					else
						$this->_elements_default[] = $def_elmt->tab_name . '.' . $def_elmt->element_name . ' AS `' . $def_elmt->tab_name . '___' . $def_elmt->element_name . '`';

				}
				elseif ($def_elmt->element_plugin == 'databasejoin') {
					$attribs                = json_decode($def_elmt->element_attribs);
					$join_val_column_concat = str_replace('{thistable}', $attribs->join_db_name, $attribs->join_val_column_concat);
					$join_val_column_concat = str_replace('{shortlang}', substr(JFactory::getLanguage()->getTag(), 0, 2), $join_val_column_concat);
					$join_val_column        = (!empty($join_val_column_concat) && $join_val_column_concat != '') ? 'CONCAT(' . $join_val_column_concat . ')' : $attribs->join_val_column;

					// Check if the db table has a published column. So we don't get the unpublished value
					$db->setQuery("SHOW COLUMNS FROM $attribs->join_db_name LIKE 'published'");
					$publish_query = ($db->loadResult()) ? " AND $attribs->join_db_name.published = 1 " : '';

					if ($group_params->repeat_group_button == 1) {
						$query = '(
									select GROUP_CONCAT(' . $join_val_column . ' SEPARATOR ", ")
									from ' . $attribs->join_db_name . '
									where ' . $attribs->join_db_name . '.' . $attribs->join_key_column . ' IN
										( select ' . $def_elmt->table_join . '.' . $def_elmt->element_name . '
										  from ' . $def_elmt->table_join . '
										  where ' . $def_elmt->table_join . '.parent_id=' . $def_elmt->tab_name . '.id
										)
                                    ' . $publish_query . '
								  ) AS `' . $def_elmt->tab_name . '___' . $def_elmt->element_name . '`';
					}
					else {
						if ($attribs->database_join_display_type == "checkbox") {

							$t     = $def_elmt->tab_name . '_repeat_' . $def_elmt->element_name;
							$query = '(
                                SELECT GROUP_CONCAT(' . $t . '.' . $def_elmt->element_name . ' SEPARATOR ", ")
                                FROM ' . $t . '
                                WHERE ' . $t . '.parent_id=' . $def_elmt->tab_name . '.id
                                ' . $publish_query . '
                              ) AS `' . $t . '`';
						}
						else {
							$query = '(SELECT GROUP_CONCAT(' . $join_val_column . ' SEPARATOR ", ")
                                from ' . $attribs->join_db_name . '
                                where `' . $attribs->join_db_name . '`.`' . $attribs->join_key_column . '`=`' . $def_elmt->tab_name . '`.`' . $def_elmt->element_name . '`
                                ' . $publish_query . '
                                ) AS `' . $def_elmt->tab_name . '___' . $def_elmt->element_name . '`';
						}
					}
					$this->_elements_default[] = $query;
				}
				elseif ($def_elmt->element_plugin == 'cascadingdropdown') {
					$attribs                 = json_decode($def_elmt->element_attribs);
					$cascadingdropdown_id    = $attribs->cascadingdropdown_id;
					$r1                      = explode('___', $cascadingdropdown_id);
					$cascadingdropdown_label = $attribs->cascadingdropdown_label;
					$r2                      = explode('___', $cascadingdropdown_label);
					$select                  = !empty($attribs->cascadingdropdown_label_concat) ? "CONCAT(" . $attribs->cascadingdropdown_label_concat . ")" : $r2[1];
					$from                    = $r2[0];
					$where                   = $r1[1];

					if (@$group_params->repeat_group_button == 1) {
						$query = '(
                                    select GROUP_CONCAT(' . $select . ' SEPARATOR ", ")
                                    from ' . $from . '
                                    where ' . $where . ' IN
                                        ( select ' . $def_elmt->table_join . '.' . $def_elmt->element_name . '
                                          from ' . $def_elmt->table_join . '
                                          where ' . $def_elmt->table_join . '.parent_id=' . $def_elmt->tab_name . '.id
                                        )
                                  ) AS `' . $def_elmt->tab_name . '___' . $def_elmt->element_name . '`';
					}
					else {
						$query = "(SELECT DISTINCT(" . $select . ") FROM " . $from . " WHERE " . $where . "=" . $def_elmt->element_name . " LIMIT 0,1) AS `" . $def_elmt->tab_name . "___" . $def_elmt->element_name . "`";
					}

					$query                     = preg_replace('#{thistable}#', $from, $query);
					$query                     = preg_replace('#{my->id}#', $current_user->id, $query);
					$query                     = preg_replace('{shortlang}', substr(JFactory::getLanguage()->getTag(), 0, 2), $query);
					$this->_elements_default[] = $query;
				}
				elseif ($def_elmt->element_plugin == 'dropdown' || $def_elmt->element_plugin == 'checkbox') {
					if (@$group_params->repeat_group_button == 1) {
						$element_attribs = json_decode($def_elmt->element_attribs);
						$select          = $def_elmt->tab_name . '.' . $def_elmt->element_name;
						foreach ($element_attribs->sub_options->sub_values as $key => $value) {
							$select = 'REGEXP_REPLACE(' . $select . ', "\\\b' . $value . '\\\b", "' . JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
						}
						$select = str_replace($def_elmt->tab_name . '.' . $def_elmt->element_name, 'GROUP_CONCAT(' . $def_elmt->table_join . '.' . $def_elmt->element_name . ' SEPARATOR ", ")', $select);

						$this->_elements_default[] = '(
                                    SELECT ' . $select . '
                                    FROM ' . $def_elmt->table_join . '
                                    WHERE ' . $def_elmt->table_join . '.parent_id = ' . $def_elmt->tab_name . '.id
                                  ) AS `' . $def_elmt->table_join . '___' . $def_elmt->element_name . '`';
					}
					else {
						$element_attribs = json_decode($def_elmt->element_attribs);
						$select          = $def_elmt->tab_name . '.' . $def_elmt->element_name;
						foreach ($element_attribs->sub_options->sub_values as $key => $value) {
							$select = 'REPLACE(' . $select . ', "' . $value . '", "' .
								JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
						}
						$this->_elements_default[] = $select . ' AS ' . $def_elmt->tab_name . '___' . $def_elmt->element_name;
					}
				}
				elseif ($def_elmt->element_plugin == 'radiobutton') {
					if (!empty($group_params->repeat_group_button) && $group_params->repeat_group_button == 1) {
						$element_attribs = json_decode($def_elmt->element_attribs);
						$select          = $def_elmt->tab_name . '.' . $def_elmt->element_name;
						foreach ($element_attribs->sub_options->sub_values as $key => $value) {
							$select = 'REGEXP_REPLACE(' . $select . ', "\\\b' . $value . '\\\b", "' . JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
						}
						$select                    = str_replace($def_elmt->tab_name . '.' . $def_elmt->element_name, 'GROUP_CONCAT(' . $def_elmt->table_join . '.' . $def_elmt->element_name . ' SEPARATOR ", ")', $select);
						$this->_elements_default[] = '(
                                    SELECT ' . $select . '
                                    FROM ' . $def_elmt->table_join . '
                                    WHERE ' . $def_elmt->table_join . '.parent_id = ' . $def_elmt->tab_name . '.id
                                  ) AS `' . $def_elmt->table_join . '___' . $def_elmt->element_name . '`';
					}
					else {
						$element_attribs = json_decode($def_elmt->element_attribs);

						$element_replacement = $def_elmt->tab_name . '___' . $def_elmt->element_name;
						$select              = $def_elmt->tab_name . '.' . $def_elmt->element_name . ' AS ' . $db->quote($element_replacement) . ', CASE ';
						foreach ($element_attribs->sub_options->sub_values as $key => $value) {
							$select .= ' WHEN ' . $def_elmt->tab_name . '.' . $def_elmt->element_name . ' = ' . $db->quote($value) . ' THEN ' . $db->quote(JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])));
						}
						$select .= ' ELSE ' . $def_elmt->tab_name . '.' . $def_elmt->element_name;
						$select .= ' END AS ' . $db->quote($element_replacement);

						$this->_elements_default[] = $select;
					}
				}
				elseif ($def_elmt->element_plugin == 'yesno') {
					if (@$group_params->repeat_group_button == 1) {
						$this->_elements_default[] = '(
                                                        SELECT REPLACE(REPLACE(GROUP_CONCAT(' . $def_elmt->table_join . '.' . $def_elmt->element_name . '  SEPARATOR ", "), "0", "' . JText::_('JNO') . '"), "1", "' . JText::_('JYES') . '")
                                                        FROM ' . $def_elmt->table_join . '
                                                        WHERE ' . $def_elmt->table_join . '.parent_id = ' . $def_elmt->tab_name . '.id
                                                      ) AS `' . $def_elmt->table_join . '___' . $def_elmt->element_name . '`';
					}
					else {
						$this->_elements_default[] = 'REPLACE(REPLACE(' . $def_elmt->tab_name . '.' . $def_elmt->element_name . ', "0", "' . JText::_('JNO') . '"), "1", "' . JText::_('JYES') . '")  AS ' . $def_elmt->tab_name . '___' . $def_elmt->element_name;
					}
				}
				else {
					if (@$group_params->repeat_group_button == 1) {
						$this->_elements_default[] = '(
                                                        SELECT  GROUP_CONCAT(' . $def_elmt->table_join . '.' . $def_elmt->element_name . '  SEPARATOR ", ")
                                                        FROM ' . $def_elmt->table_join . '
                                                        WHERE ' . $def_elmt->table_join . '.parent_id = ' . $def_elmt->tab_name . '.id
                                                      ) AS `' . $def_elmt->table_join . '___' . $def_elmt->element_name . '`';
					}
					else {
						$this->_elements_default[] = $def_elmt->tab_name . '.' . $def_elmt->element_name . ' AS ' . $def_elmt->tab_name . '___' . $def_elmt->element_name;
					}
				}
			}
		}
		if (in_array('overall', $em_blocks_names)) {
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
		$result         = @EmundusHelperList::getElementsDetails($elements_names);

		$result = @EmundusHelperFiles::insertValuesInQueryResult($result, array("sub_values", "sub_labels"));

		$this->details = new stdClass();

		foreach ($result as $res) {
			$this->details->{$res->tab_name . '__' . $res->element_name} = array('element_id' => $res->element_id,
			                                                                     'plugin'     => $res->element_plugin,
			                                                                     'attribs'    => $res->params,
			                                                                     'sub_values' => $res->sub_values,
			                                                                     'sub_labels' => $res->sub_labels,
			                                                                     'group_by'   => $res->tab_group_by);
		}
	}

	public function getElementsVar()
	{
		return $this->_elements;
	}

	/**
	 * Get list of admission elements
	 *
	 * @param   int   $show_in_list_summary
	 * @param   int   $hidden
	 * @param   null  $code
	 *
	 * @return array list of Fabrik element ID used in admission form
	 * @throws Exception
	 */
	public function getAdmissionElementsName($show_in_list_summary = 1, $hidden = 0, $code = null)
	{
		$session = JFactory::getSession();
		$h_list  = new EmundusHelperList;

		if ($session->has('filt_params')) {

			$filt_params = $session->get('filt_params');

			if (is_array($filt_params['programme']) && count(@$filt_params['programme']) > 0) {

				foreach (array_unique($filt_params['programme']) as $value) {
					$groups = $this->getGroupsAdmissionByProgramme($value);
					if (empty($groups)) {
						$decision_elt_list = array();
					}
					else {
						$decision_elt_list = $this->getElementsByGroups($groups, $show_in_list_summary, $hidden);
						if (count($decision_elt_list) > 0) {
							foreach ($decision_elt_list as $del) {
								if (isset($del->element_id) && !empty($del->element_id)) {
									$elements[] = $h_list->getElementsDetailsByID($del->element_id)[0];
								}
							}
						}
					}
				}
			}
			else {
				if (!empty($code)) {
					foreach ($code as $value) {
						$groups = $this->getGroupsAdmissionByProgramme($value);
						if (empty($groups)) {
							$decision_elt_list = array();
						}
						else {
							$decision_elt_list = $this->getElementsByGroups($groups, $show_in_list_summary, $hidden);
							if (count($decision_elt_list) > 0) {
								foreach ($decision_elt_list as $del) {
									if (isset($del->element_id) && !empty($del->element_id)) {
										$elements[] = $h_list->getElementsDetailsByID($del->element_id)[0];
									}
								}
							}
						}
					}
				}
			}
		}

		return @$elements;
	}

	/**
	 * Get list of admission elements
	 *
	 * @param   int displayed in Fabrik List ; yes=1
	 * @param   int hidden from Fabrik List ; yes=1
	 * @param   array code get elements from Decision form defined for programme list
	 *
	 * @return array list of Fabrik element ID used in admission form
	 **@throws Exception
	 */
	public function getApplicantAdmissionElementsName($show_in_list_summary = 1, $hidden = 0, $code = null, $all = null)
	{
		$session = JFactory::getSession();
		$h_list  = new EmundusHelperList;
		$jinput  = JFactory::getApplication()->input;
		$view    = $jinput->getString('view', null);

		$elements = array();
		if ($session->has('filt_params') || !empty($all)) {

			$filt_params = $session->get('filt_params');

			if ($view != 'export_select_columns' && is_array(@$filt_params['programme']) && count(@$filt_params['programme']) > 0) {
				$programmes = array_unique($filt_params['programme']);
			}
			elseif (!empty($code)) {
				$programmes = array_unique($code);
			}
			else {
				return array();
			}

			foreach ($programmes as $value) {
				$groups = $this->getGroupsApplicantAdmissionByProgramme($value);
				if (empty($groups)) {
					$admission_elt_list = array();
				}
				else {
					$admission_elt_list = $this->getElementsByGroups($groups, $show_in_list_summary, $hidden);

					if (count($admission_elt_list) > 0) {
						foreach ($admission_elt_list as $ael) {
							if (isset($ael->element_id) && !empty($ael->element_id)) {
								$elements[] = $h_list->getElementsDetailsByID($ael->element_id)[0];
							}
						}
					}
				}
			}
		}

		return $elements;
	}


	/**
	 * Get list of ALL admission elements
	 *
	 * @param   int displayed in Fabrik List ; yes=1
	 * @param   string code of the programme
	 *
	 * @return    array list of Fabrik element ID used in admission form
	 **/
	public function getAllAdmissionElements($show_in_list_summary, $programme_code)
	{
		$session = JFactory::getSession();

		if ($session->has('filt_params')) {
			$elements_id = array();
			$filt_params = $session->get('filt_params');

			if (is_array(@$filt_params['programme']) && $filt_params['programme'][0] != '%') {

				foreach ($filt_params['programme'] as $value) {
					if ($value == $programme_code) {
						$groups = $this->getGroupsAdmissionByProgramme($value);
						if (!empty($groups)) {
							$admission_elt_list = $this->getAllElementsByGroups($groups, $show_in_list_summary); // $show_in_list_summary
							if (count($admission_elt_list) > 0) {
								foreach ($admission_elt_list as $eel) {
									$elements_id[] = $eel->element_id;
								}
							}
						}
					}
				}

			}
			else {

				$groups = $this->getGroupsAdmissionByProgramme($programme_code);
				if (!empty($groups)) {
					$admission_elt_list = $this->getAllElementsByGroups($groups, $show_in_list_summary); // $show_in_list_summary
					if (count($admission_elt_list) > 0) {
						foreach ($admission_elt_list as $eel) {
							$elements_id[] = $eel->element_id;
						}
					}
				}
			}

		}

		return @$elements_id;
	}

	/**
	 * Get list of ALL admission elements from applicant form
	 *
	 * @param   int displayed in Fabrik List ; yes=1
	 * @param   string code of the programme
	 *
	 * @return    array list of Fabrik element ID used in admission form
	 **/
	public function getAllApplicantAdmissionElements($show_in_list_summary, $programme_code)
	{
		$session = JFactory::getSession();

		if ($session->has('filt_params')) {
			$elements_id = array();
			$filt_params = $session->get('filt_params');

			if (is_array(@$filt_params['programme']) && $filt_params['programme'][0] != '%') {
				foreach ($filt_params['programme'] as $value) {
					if ($value == $programme_code) {
						$groups = $this->getGroupsApplicantAdmissionByProgramme($value);
						if (!empty($groups)) {
							$admission_elt_list = $this->getAllElementsByGroups($groups, $show_in_list_summary); // $show_in_list_summary
							if (count($admission_elt_list) > 0) {
								foreach ($admission_elt_list as $eel) {
									$elements_id[] = $eel->element_id;
								}
							}
						}
					}
				}

			}
			else {

				$groups = $this->getGroupsApplicantAdmissionByProgramme($programme_code);
				if (!empty($groups)) {
					$admission_elt_list = $this->getAllElementsByGroups($groups, $show_in_list_summary); // $show_in_list_summary
					if (count($admission_elt_list) > 0) {
						foreach ($admission_elt_list as $eel) {
							$elements_id[] = $eel->element_id;
						}
					}
				}
			}

		}
		else {

			$groups = $this->getGroupsApplicantAdmissionByProgramme($programme_code);
			if (!empty($groups)) {
				$admission_elt_list = $this->getAllElementsByGroups($groups, $show_in_list_summary); // $show_in_list_summary
				if (count($admission_elt_list) > 0) {
					foreach ($admission_elt_list as $eel) {
						$elements_id[] = $eel->element_id;
					}
				}
			}
		}

		return @$elements_id;
	}

	public function _buildContentOrderBy()
	{

		$menu            = @JFactory::getApplication()->getMenu();
		$current_menu    = $menu->getActive();
		$menu_params     = $menu->getParams($current_menu->id);
		$em_blocks_names = explode(',', $menu_params->get('em_blocks_names'));

		$filter_order     = JFactory::getSession()->get('filter_order');
		$filter_order_Dir = JFactory::getSession()->get('filter_order_Dir');

		$can_be_ordering = array();
		if (count($this->_elements) > 0) {
			foreach ($this->_elements as $element) {
				if (!empty($element->table_join)) {
					$can_be_ordering[] = $element->table_join . '___' . $element->element_name;
					$can_be_ordering[] = $element->table_join . '.' . $element->element_name;
				}
				else {
					$can_be_ordering[] = $element->tab_name . '___' . $element->element_name;
					$can_be_ordering[] = $element->tab_name . '.' . $element->element_name;
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
		if (in_array('overall', $em_blocks_names))
			$can_be_ordering[] = 'overall';

		if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering))
			return ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;

		return '';
	}

	public function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
	{
		if (is_array($multi_array)) {
			foreach ($multi_array as $key => $row_array) {

				if (is_array($row_array))
					@$key_array[$key] = $row_array[$sort_key];
				else return -1;

			}
		}
		else return -1;

		if (!empty($key_array))
			array_multisort($key_array, $sort, $multi_array);

		return $multi_array;
	}

	public function getCampaign()
	{
		return @EmundusHelperFiles::getCampaign();
	}

	public function getCurrentCampaign()
	{
		return @EmundusHelperFiles::getCurrentCampaign();
	}

	public function getCurrentCampaignsID()
	{
		return @EmundusHelperFiles::getCurrentCampaignsID();
	}

	public function getProfileAcces($user)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id=' . $user;
		$db->setQuery($query);
		$profiles = $db->loadResultArray();

		return $profiles;
	}

	public function setSubQuery($tab, $elem)
	{
		$search              = JFactory::getApplication()->input->get('elements', null, 'POST', 'array', 0);
		$search_values       = JFactory::getApplication()->input->get('elements_values', null, 'POST', 'array', 0);
		$search_other        = JFactory::getApplication()->input->get('elements_other', null, 'POST', 'array', 0);
		$search_values_other = JFactory::getApplication()->input->get('elements_values_other', null, 'POST', 'array', 0);

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
		$obj  = $db->loadObjectList();
		$list = array();
		$tmp  = '';

		foreach ($obj as $unit) {
			if ($tmp != $unit->user_id)
				$list[$unit->user_id] = @EmundusHelperList::getBoxValue($this->details->{$tab . '__' . $elem}, $unit->{$tab . '__' . $elem}, $elem);
			else
				$list[$unit->user_id] .= ',' . @EmundusHelperList::getBoxValue($this->details->{$tab . '__' . $elem}, $unit->{$tab . '__' . $elem}, $elem);
			$tmp = $unit->user_id;
		}

		return $list;
	}

	public function setSelect($search)
	{
		$cols = array();
		if (!empty($search)) {

			asort($search);
			$i         = 0;
			$old_table = '';
			foreach ($search as $c) {

				if (!empty($c)) {
					$tab = explode('.', $c);

					if ($tab[0] == 'jos_emundus_training') {
						$cols[] = ' search_' . $tab[0] . '.label as ' . $tab[1] . ' ';
					}
					else {
						if ($this->details->{$tab[0] . '__' . $tab[1]}['group_by'])
							$this->subquery[$tab[0] . '__' . $tab[1]] = $this->setSubQuery($tab[0], $tab[1]);
						else
							$cols[] = $c . ' AS ' . $tab[0] . '__' . $tab[1];
					}
				}
				$i++;
			}
			if (!empty($cols))
				$cols = implode(', ', $cols);
		}

		return $cols;
	}

	public function isJoined($tab, $joined)
	{
		foreach ($joined as $j) {
			if ($tab == $j)
				return true;
		}

		return false;
	}

	public function setJoins($search, $query, $joined)
	{

		$tables_list = array();

		if (!empty($search)) {
			$old_table = '';
			$i         = 0;

			foreach ($search as $s) {

				$tab = explode('.', $s);
				if (count($tab) > 1) {
					if ($tab[0] != $old_table && !$this->isJoined($tab[0], $joined)) {

						if ($tab[0] == 'jos_emundus_groups_eval' || $tab[0] == 'jos_emundus_comments')
							$query .= ' LEFT JOIN ' . $tab[0] . ' ON ' . $tab[0] . '.applicant_id=#__users.id ';
						elseif ($tab[0] == 'jos_emundus_evaluations' || $tab[0] == 'jos_emundus_admissions' || $tab[0] == 'jos_emundus_files_request' || $tab[0] == 'jos_emundus_mobility')
							$query .= ' LEFT JOIN ' . $tab[0] . ' ON ' . $tab[0] . '.student_id=#__users.id ';
						elseif ($tab[0] == "jos_emundus_training")
							$query .= ' LEFT JOIN #__emundus_setup_teaching_unity AS search_' . $tab[0] . ' ON search_' . $tab[0] . '.code=#__emundus_setup_campaigns.training ';
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

	public function _buildSelect(&$tables_list, &$tables_list_other, &$tables_list_default)
	{

		$current_user = JFactory::getSession()->get('emundusUser');

		$search               = $this->getState('elements');
		$search_other         = $this->getState('elements_other');
		$schoolyears          = $this->getState('schoolyear');
		$programmes           = $this->getState('programme');
		$gid                  = $this->getState('groups');
		$uid                  = $this->getState('user');
		$miss_doc             = $this->getState('missing_doc');
		$validate_application = $this->getState('validate');

		$menu         = @JFactory::getApplication()->getMenu();
		$current_menu = $menu->getActive();
		$menu_params  = $menu->getParams($current_menu->id);

		$this->validate_details = @EmundusHelperList::getElementsDetailsByID($menu_params->get('em_validate_id'));
		$col_validate           = "";

		foreach ($this->validate_details as $vd)
			$col_validate .= $vd->tab_name . '.' . $vd->element_name . ',';

		$col_validate = substr($col_validate, 0, strlen($col_validate) - 1);

		$cols         = $this->setSelect($search);
		$cols_other   = $this->setSelect($search_other);
		$cols_default = $this->setSelect($this->_elements_default);

		$joined = array('jos_emundus_users', 'jos_users',
			'jos_emundus_setup_profiles',
			'jos_emundus_admission',
			'jos_emundus_declaration');

		$query = 'SELECT #__emundus_users.user_id, #__emundus_users.user_id as user, #__emundus_users.user_id as id, #__emundus_users.lastname, #__emundus_users.firstname,
		#__users.name, #__users.registerDate, #__users.email, #__emundus_setup_profiles.id as profile, #__emundus_campaign_candidature.date_submitted,
		#__emundus_setup_campaigns.year as schoolyear, #__emundus_setup_campaigns.label, #__emundus_campaign_candidature.date_submitted, #__emundus_campaign_candidature.campaign_id';
		if (!empty($cols)) $query .= ', ' . $cols;
		if (!empty($cols_other)) $query .= ', ' . $cols_other;
		if (!empty($cols_default)) $query .= ', ' . $cols_default;
		if (!empty($col_validate)) $query .= ', ' . $col_validate;
		$query .= '	FROM #__emundus_campaign_candidature
					LEFT JOIN #__emundus_users ON #__emundus_declaration.user=#__emundus_users.user_id
					LEFT JOIN #__emundus_setup_campaigns ON #__emundus_setup_campaigns.id=#__emundus_campaign_candidature.campaign_id
					LEFT JOIN #__users ON #__users.id=#__emundus_users.user_id
					LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
					LEFT JOIN #__emundus_admission ON #__emundus_admission.user=#__emundus_users.user_id';

		$this->setJoins($search, $query, $joined);
		$this->setJoins($search_other, $query, $joined);
		$this->setJoins($this->_elements_default, $query, $joined);

		if (((isset($gid) && !empty($gid)) || (isset($uid) && !empty($uid))) && !$this->isJoined('jos_emundus_groups_eval', $joined))
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

		if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id)) {
			$pa    = EmundusHelperAccess::getProfileAccess($current_user->id);
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
	 *
	 * @param   array   $search     filters elements
	 * @param   array   $eval_list  reference of result list
	 * @param   array   $head_val   header name
	 * @param   object  $applicant  array of applicants indexed by database column
	 **/
	public function setEvalList($search, &$eval_list, $head_val, $applicant)
	{
		//print_r($applicant); die();
		if (!empty($search)) {

			foreach ($search as $c) {
				if (!empty($c)) {
					$name = explode('.', $c);
					if (!in_array($name[0] . '__' . $name[1], $head_val)) {

						$print_val = '';
						if ($this->details->{$name[0] . '__' . $name[1]}['group_by'] && array_key_exists($name[0] . '__' . $name[1], $this->subquery) && array_key_exists($applicant->user_id, $this->subquery[$name[0] . '__' . $name[1]]))
							$eval_list[$name[0] . '__' . $name[1]] = @EmundusHelperList::createHtmlList(explode(",", $this->subquery[$name[0] . '__' . $name[1]][$applicant->user_id]));
						elseif ($name[0] == 'jos_emundus_training')
							$eval_list[$name[1]] = $applicant->{$name[1]};
						elseif (!$this->details->{$name[0] . '__' . $name[1]}['group_by'])
							$eval_list[$name[0] . '__' . $name[1]] = @EmundusHelperList::getBoxValue($this->details->{$name[0] . '__' . $name[1]}, $applicant->{$name[0] . '__' . $name[1]}, $name[1]);
						else
							$eval_list[$name[0] . '__' . $name[1]] = $applicant->{$name[0] . '__' . $name[1]};

					}
				}
			}
		}
	}


	private function _buildWhere($tableAlias = array())
	{
		$h_files = new EmundusHelperFiles();

		return $h_files->_buildWhere($tableAlias, 'admission', array(
			'fnum_assoc' => $this->fnum_assoc,
			'code'       => $this->code
		));
	}

	public function getUsers($current_fnum = null)
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');

		$app      = JFactory::getApplication();
		$session  = JFactory::getSession();
		$dbo      = $this->getDbo();
		$eMConfig = JComponentHelper::getParams('com_emundus');

		$current_menu    = $app->getMenu()->getActive();
		$menu_params     = $current_menu->getParams();
		$em_blocks_names = explode(',', $menu_params->get('em_blocks_names'));

		$query = 'select jecc.fnum, ss.step, ss.value as status, ss.class as status_class, concat(upper(trim(eu.lastname))," ",eu.firstname) AS name ';

		// prevent double left join on query
		$lastTab = [
			'#__emundus_setup_status', 'jos_emundus_setup_status',
			'#__emundus_setup_programmes', 'jos_emundus_setup_programmes',
			'#__emundus_setup_campaigns', 'jos_emundus_setup_campaigns',
			'#__emundus_admission', 'jos_emundus_admission',
			'#__emundus_users', 'jos_emundus_users',
			'#__users', 'jos_users',
			'#__emundus_tag_assoc', 'jos_emundus_tag_assoc'
		];

		if (in_array('overall', $em_blocks_names))
			$lastTab[] = ['#__emundus_evaluations', 'jos_emundus_evaluations'];

		$leftJoin = '';

		if (count($this->_elements) > 0) {
			foreach ($this->_elements as $elt) {

				if (!isset($lastTab))
					$lastTab = array();
				if (!in_array($elt->tab_name, $lastTab))
					$leftJoin .= 'left join ' . $elt->tab_name . ' ON ' . $elt->tab_name . '.fnum = jecc.fnum ';
				if (!empty($elt->table_join)) {
					$lastTab[] = $elt->table_join;
					$group_by  .= ', ' . $elt->table_join . '___' . $elt->element_name;
				}
				else {
					$lastTab[] = $elt->tab_name;
					$group_by  .= ', ' . $elt->tab_name . '___' . $elt->element_name;
				}

			}
		}
		if (count($this->_elements_default) > 0)
			$query .= ', ' . implode(',', $this->_elements_default);

		$query .= ', jos_emundus_admission.id AS admission_id, CONCAT(eue.lastname," ",eue.firstname) AS recorded_by';

		$query .= ' FROM #__emundus_campaign_candidature as jecc
					LEFT JOIN #__emundus_setup_status as ss on ss.step = jecc.status
					LEFT JOIN #__emundus_setup_campaigns as esc on esc.id = jecc.campaign_id
					LEFT JOIN #__emundus_setup_programmes as sp on sp.code = esc.training
					LEFT JOIN #__emundus_users as eu on eu.user_id = jecc.applicant_id
					LEFT JOIN #__users as u on u.id = jecc.applicant_id
					LEFT JOIN #__emundus_admission as jos_emundus_admission on jos_emundus_admission.fnum = jecc.fnum
					LEFT JOIN #__emundus_tag_assoc as eta on eta.fnum=jecc.fnum  ';

		if (in_array('overall', $em_blocks_names))
			$query .= ' LEFT JOIN #__emundus_evaluations as ee on ee.fnum = jecc.fnum ';

		$q = $this->_buildWhere($lastTab);

		if (!empty($leftJoin))
			$query .= $leftJoin;

		$query .= ' LEFT JOIN #__emundus_users as eue on eue.user_id = jos_emundus_admission.user ';
		$query .= $q['join'];

		$query .= ' where jecc.status > 0 ' . $q['q'];

		if (isset($current_fnum) && !empty($current_fnum))
			$query .= ' AND jecc.fnum like ' . $dbo->Quote($current_fnum);

		// ONLY FILES LINKED TO MY GROUPS
		//$code = $userModel->getUserGroupsProgrammeAssoc(JFactory::getUser()->id);
		//$fnum_assoc = $userModel->getApplicantsAssoc(JFactory::getUser()->id);
		$query .= ' AND (sp.code IN ("' . implode('","', $this->code) . '") OR jecc.fnum IN ("' . implode('","', $this->fnum_assoc) . '")) ';
		//////////////////////////////////////////////////////////////

//		if (in_array('overall', $em_blocks_names))
		$query .= ' GROUP BY jecc.fnum';

		$query .= $this->_buildContentOrderBy();

		try {
			$dbo->setQuery($query);
			$res               = $dbo->loadAssocList();
			$this->_applicants = $res;

			$limit      = $session->get('limitstart');
			$limitStart = $session->get('limit');
			if ($limitStart > 0)
				$query .= " limit $limit, $limitStart ";

			$dbo->setQuery($query);
			$res = $dbo->loadAssocList();
		}
		catch (Exception $e) {
			echo $e->getMessage();
			$res = [];
		}

		return $res;
	}

	// get elements by groups
	// @params string List of Fabrik groups comma separated
	function getElementsByGroups($groups, $show_in_list_summary = 1, $hidden = 0)
	{
		return @EmundusHelperFilters::getElementsByGroups($groups, $show_in_list_summary, $hidden);
	}

	// get ALL elements by groups
	// @params string List of Fabrik groups comma separated
	function getAllElementsByGroups($groups, $show_in_list_summary = null, $hidden = null)
	{
		return @EmundusHelperFilters::getAllElementsByGroups($groups, $show_in_list_summary, $hidden);
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
		if (empty($this->_pagination)) {

			jimport('joomla.html.pagination');
			$session           = JFactory::getSession();
			$this->_pagination = new JPagination($this->getTotal(), $session->get('limitstart'), $session->get('limit'));

		}

		return $this->_pagination;
	}

	public function getPageNavigation(): string
	{
		if ($this->getPagination()->pagesTotal <= 1) {
			return '';
		}

		$pageNavigation = "<div class='em-container-pagination-selectPage'>";
		$pageNavigation .= "<ul class='pagination pagination-sm'>";
		$pageNavigation .= "<li><a href='#em-data' id='" . $this->getPagination()->pagesStart . "'><span class='material-icons'>navigate_before</span></a></li>";
		if ($this->getPagination()->pagesTotal > 15) {
			for ($i = 1; $i <= 5; $i++) {
				$pageNavigation .= "<li ";
				if ($this->getPagination()->pagesCurrent == $i) {
					$pageNavigation .= "class='active'";
				}
				$pageNavigation .= "><a id='" . $i . "' href='#em-data'>" . $i . "</a></li>";
			}
			$pageNavigation .= "<li class='disabled'><span>...</span></li>";
			if ($this->getPagination()->pagesCurrent <= 5) {
				for ($i = 6; $i <= 10; $i++) {
					$pageNavigation .= "<li ";
					if ($this->getPagination()->pagesCurrent == $i) {
						$pageNavigation .= "class='active'";
					}
					$pageNavigation .= "><a id=" . $i . " href='#em-data'>" . $i . "</a></li>";
				}
			}
			else {
				for ($i = $this->getPagination()->pagesCurrent - 2; $i <= $this->getPagination()->pagesCurrent + 2; $i++) {
					if ($i <= $this->getPagination()->pagesTotal) {
						$pageNavigation .= "<li ";
						if ($this->getPagination()->pagesCurrent == $i) {
							$pageNavigation .= "class='active'";
						}
						$pageNavigation .= "><a id=" . $i . " href='#em-data'>" . $i . "</a></li>";
					}
				}
			}
			$pageNavigation .= "<li class='disabled'><span>...</span></li>";
			for ($i = $this->getPagination()->pagesTotal - 4; $i <= $this->getPagination()->pagesTotal; $i++) {
				$pageNavigation .= "<li ";
				if ($this->getPagination()->pagesCurrent == $i) {
					$pageNavigation .= "class='active'";
				}
				$pageNavigation .= "><a id='" . $i . "' href='#em-data'>" . $i . "</a></li>";
			}
		}
		else {
			for ($i = 1; $i <= $this->getPagination()->pagesStop; $i++) {
				$pageNavigation .= "<li ";
				if ($this->getPagination()->pagesCurrent == $i) {
					$pageNavigation .= "class='active'";
				}
				$pageNavigation .= "><a id='" . $i . "' href='#em-data'>" . $i . "</a></li>";
			}
		}
		$pageNavigation .= "<li><a href='#em-data' id='" . $this->getPagination()->pagesTotal . "'><span class='material-icons'>navigate_next</span></a></li></ul></div>";

		return $pageNavigation;
	}

	// get applicant columns
	public function getApplicantColumns()
	{

		$cols   = array();
		$cols[] = array('name' => 'user_id', 'label' => 'User id');
		$cols[] = array('name' => 'user', 'label' => 'User id');
		$cols[] = array('name' => 'name', 'label' => 'Name');
		$cols[] = array('name' => 'email', 'label' => 'Email');
		$cols[] = array('name' => 'profile', 'label' => 'Profile');

		return $cols;
	}

	// get string of fabrik group ID use for application form
	public function getGroupsAdmissionByProgramme($code)
	{
		$db    = $this->getDbo();
		$query = 'select fabrik_admission_group_id from #__emundus_setup_programmes where code like ' . $db->Quote($code);

		try {

			if (!empty($code)) {
				$db->setQuery($query);

				return $db->loadResult();
			}
			else {
				return null;
			}
		}
		catch (Exception $e) {
			throw $e;
		}
	}

	// get string of fabrik group ID use for applicant application form
	public function getGroupsApplicantAdmissionByProgramme($code)
	{
		$db    = $this->getDbo();
		$query = 'select fabrik_applicant_admission_group_id from #__emundus_setup_programmes where code like ' . $db->Quote($code);

		try {

			if (!empty($code)) {
				$db->setQuery($query);

				return $db->loadResult();
			}
			else {
				return null;
			}

		}
		catch (Exception $e) {
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

			return null;
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
	* 	Get evaluations for fnum done by a user
	*	@param fnum 		Application File number
	*	@param user 		user
	* 	@return array
	*/
	function getAdmissionFnum($fnum)
	{
		$res = [];

		try {
			$query = 'SELECT *
					FROM #__emundus_final_grade fg
					WHERE fg.fnum like ' . $this->_db->Quote($fnum);
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			echo $e->getMessage();
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $res;
	}

	/*
	* 	Get evaluations form ID By programme code
	*	@param code 		code of the programme
	* 	@return int 		The fabrik ID for the admission form
	*/
	function getAdmissionFormByProgramme($code = null)
	{
		$res = new stdClass();

		if ($code === null) {
			$session = JFactory::getSession();
			if ($session->has('filt_params')) {
				$filt_params = $session->get('filt_params');
				if (!empty(@$filt_params['programme'])) {
					$code = $filt_params['programme'][0];
				}
			}
		}

		try {

			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName(['ff.form_id', 'l.db_table_name']))
				->from($this->_db->quoteName('#__fabrik_formgroup', 'ff'))
				->leftJoin($this->_db->quoteName('jos_fabrik_lists', 'l') . ' ON ' . $this->_db->quoteName('l.form_id') . ' = ' . $this->_db->quoteName('ff.form_id'))
				->where('ff.group_id IN (SELECT fabrik_admission_group_id FROM #__emundus_setup_programmes WHERE code LIKE ' . $this->_db->Quote($code) . ')');
			$this->_db->setQuery($query);
			$res = $this->_db->loadObject();
		}
		catch (Exception $e) {
			echo $e->getMessage();
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $res;
	}

	/*
	* 	Get row id of admission user
	*	@param form_table 		table name of admission form
	*	@param fnum 		fnum of the user
	* 	@return int 		The row Id of the user admission
	*/
	function getAdmissionId($form_table, $fnum)
	{
		$res = 0;

		try {
			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName('id'))
				->from($this->_db->quoteName($form_table))
				->where('fnum LIKE ' . $this->_db->Quote($fnum));
			$this->_db->setQuery($query);
			$res = $this->_db->loadResult();
		}
		catch (Exception $e) {
			echo $e->getMessage();
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $res;
	}

	/*
	 *  @description    Set the value of a fabrik element using its element ID and fnum
	 *  @param          $fum            string   application file number
	 *  @param          $element_id     string   Fabrik element ID
	 *  @param          $value          string   Value to insert into DB
	 *  @return
	*/
	function setAdmissionByFabrikElementsId($fnum, $element_id, $value)
	{
		$db   = JFactory::getDBO();
		$user = JFactory::getUser();

		// Datetime is automatically added to the DB

		$student_id = (int) substr($fnum, -7);

		$h_files         = new EmundusHelperFiles;
		$element_details = $h_files->getElementsDetailsByID($element_id);

		try {

			$query = 'INSERT INTO ' . $element_details[0]->tab_name . ' (' . $element_details[0]->element_name . ', fnum, user';

			// Student id info is only important for elements belonging to the final grade table
			if ($element_details[0]->tab_name == "jos_emundus_final_grade")
				$query .= ', student_id';

			$query .= ') VALUES (' . $db->Quote($value) . ', ' . $db->Quote($fnum) . ', ' . $db->Quote($user->id);

			if ($element_details[0]->tab_name == "jos_emundus_final_grade")
				$query .= ', ' . $db->Quote($student_id);

			$query .= ')';

			$db->setQuery($query);
			$db->execute() or die($db->getErrorMsg());

		}
		catch (Exception $e) {
			echo $e->getMessage();
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}
	}

	/*
	 *  @description    Update the value of a fabrik element using its element ID and fnum
	 *  @param          $fum            string   application file number
	 *  @param          $element_id     string   Fabrik element ID
	 *  @param          $value          string   Value to update into DB
	 *  @return
	*/
	function updateAdmissionByFabrikElementsId($fnum, $element_id, $value)
	{
		$db = JFactory::getDBO();

		$h_files         = new EmundusHelperFiles;
		$element_details = $h_files->getElementsDetailsByID($element_id);

		try {

			$query = 'UPDATE ' . $element_details[0]->tab_name . ' SET ' . $element_details[0]->element_name . ' = ' . $db->Quote($value) . ' WHERE fnum like' . $db->Quote($fnum);
			$db->setQuery($query);
			$db->execute() or die($db->getErrorMsg());

		}
		catch (Exception $e) {
			echo $e->getMessage();
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}
	}

	public function getAdmissionInfo($sid)
	{
		$db = JFactory::getDBO();

		$query = "SELECT * FROM #__emundus_final_grade WHERE student_id = " . $sid . " GROUP BY fnum ORDER BY time_date DESC";
		$db->setQuery($query);
		$admissionInfo = $db->loadObject();

		if (empty($admissionInfo)) {
			$mainframe = JFactory::getApplication();
			$offset    = $mainframe->get('offset', 'UTC');
			$dateTime  = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
			$dateTime  = $dateTime->setTimezone(new DateTimeZone($offset));
			$now       = $dateTime->format('Y-m-d H:i:s');

			$query = $db->getQuery(true);
			$query->select($db->quoteName('fnum'))
				->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
				->leftJoin($db->quoteName('#__emundus_setup_status', 'ss') . ' ON ' . $db->quoteName('ss.step') . ' = ' . $db->quoteName('cc.status'))
				->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $db->quoteName('sc.id') . ' = ' . $db->quoteName('cc.campaign_id'))
				->where($db->quoteName('cc.applicant_id') . ' = ' . $sid . ' AND ' . $db->quoteName('ss.profile') . ' = 8 AND ' . $db->quoteName('sc.admission_start_date') . ' <= ' . $db->Quote($now) . ' AND ' . $db->quoteName('sc.admission_end_date') . ' >= ' . $db->Quote($now))
				->order($db->quoteName('cc.date_time') . ' DESC');

			try {
				$db->setQuery($query);
				$admissionInfo = $db->loadObject();
			}
			catch (Exception $e) {
				JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

		$query = "SELECT form_id FROM #__fabrik_formgroup as fg
			LEFT JOIN #__emundus_setup_programmes AS sp ON sp.fabrik_applicant_admission_group_id = fg.group_id
			LEFT JOIN #__emundus_setup_campaigns AS sc ON sc.training LIKE sp.code
			LEFT JOIN #__emundus_campaign_candidature AS cc ON cc.campaign_id = sc.id
			WHERE cc.fnum LIKE " . $db->quote($admissionInfo->fnum);

		try {
			$db->setQuery($query);
			$admissionInfo->form_id = $db->loadresult();
		}
		catch (Exception $e) {
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		// Getting the Item ID of the application form in order to redirect correctly.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('m.id'))
			->from($db->quoteName('#__menu', 'm'))
			->leftJoin($db->quoteName('#__emundus_setup_profiles', 'sp') . ' ON ' . $db->quoteName('m.menutype') . ' LIKE ' . $db->quoteName('sp.menutype'))
			->where($db->quoteName('sp.id') . ' = 8 AND ' . $db->quoteName('m.link') . ' LIKE ' . $db->quote('index.php?option=com_fabrik&view=form&formid=' . $admissionInfo->form_id) . ' AND ' . $db->quoteName('m.published') . ' = 1');
		try {
			$db->setQuery($query);
			$admissionInfo->item_id = $db->loadResult();
		}
		catch (Exception $e) {
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		// This is for retrocompatibility with OLAGE.
		if (empty($admissionInfo->item_id)) {
			$admissionInfo->item_id = '2720';
		}

		return $admissionInfo;

	}

}
