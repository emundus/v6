<?php
/**
 * @package        	Joomla
 * @subpackage    	eMundus
 * @link        	http://www.emundus.fr
 * @copyright    	Copyright (C) 2018 eMundus. All rights reserved.
 * @license         GNU/GPL
 * @author        	Benjamin Rivalland - Yoan Durand
 */

// No direct access

defined('_JEXEC') or die('Restricted access');
define('R_MD5_MATCH', '/^[a-f0-9]{32}$/i');

jimport('joomla.application.component.model');
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'files.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'list.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'access.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');


class EmundusModelEvaluation extends JModelList {
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
        $current_user = JFactory::getUser();
        /*
        ** @TODO : gestion du cas Itemid absent à prendre en charge dans la vue
        */

        if (empty($current_menu)) {
            return false;
        }

        $menu_params = $menu->getParams($current_menu->id);

        $session = JFactory::getSession();
        if (!$session->has('filter_order')) {
            $session->set('filter_order', 'jos_emundus_campaign_candidature.fnum');
            $session->set('filter_order_Dir', 'desc');
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
        $this->elements_id = rtrim($this->elements_id, ',');

        // get evaluation element
        $show_in_list_summary = 1;
        $hidden = 0;
        $elements_eval = $this->getEvaluationElements($show_in_list_summary, $hidden);
        if (is_array($elements_eval) && count($elements_eval)) {
            $this->elements_id .= implode(',', $elements_eval);
        }

        if ($session->has('adv_cols')) {
            $adv = $session->get('adv_cols');
            if (!empty($adv) && !is_null($adv)) {
                $this->elements_id .= ','.implode(',', $adv);
            }
        }
        $this->elements_values = explode(',', $menu_params->get('em_elements_values'));

        $this->_elements_default = array();

        if (!is_null($this->elements_id)) {
            $this->_elements = @EmundusHelperFiles::getElementsName($this->elements_id);
        }

        if (!empty($this->_elements)) {
            foreach ($this->_elements as $def_elmt) {
                $group_params = json_decode($def_elmt->group_attribs);

                if ($def_elmt->element_plugin == 'date') {
                    if (isset($group_params->repeat_group_button) && $group_params->repeat_group_button == 1) {
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

                    if (isset($group_params->repeat_group_button) && $group_params->repeat_group_button == 1) {
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
                              ) AS `'.$t.'___'.$def_elmt->element_name.'`';
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

                    if (isset($group_params->repeat_group_button) && $group_params->repeat_group_button == 1) {
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
                } elseif ($def_elmt->element_plugin == 'dropdown' || $def_elmt->element_plugin == 'radiobutton' || $def_elmt->element_plugin == 'checkbox') {

                    if (isset($group_params->repeat_group_button) && $group_params->repeat_group_button == 1) {
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
                                JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
                        }
                        $this->_elements_default[] = $select . ' AS ' . $def_elmt->tab_name . '___' . $def_elmt->element_name;
                    }
                } else {
                    if (isset($group_params->repeat_group_button) && $group_params->repeat_group_button == 1) {
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
        if (isset($em_other_columns) && in_array('overall', $em_other_columns)) {
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

        if (count($this->col) > 0) {

            $elements_names = '"'.implode('", "', $this->col).'"';

            $h_list = new EmundusHelperList;
            $h_files = new EmundusHelperFiles();

            $result = $h_list->getElementsDetails($elements_names);
            $result = $h_files->insertValuesInQueryResult($result, array("sub_values", "sub_labels"));

            $this->details = new stdClass();
            foreach ($result as $res) {
                $this->details->{$res->tab_name . '___' . $res->element_name} = array('element_id' => $res->element_id,
                    'plugin' => $res->element_plugin,
                    'attribs' => $res->params,
                    'sub_values' => $res->sub_values,
                    'sub_labels' => $res->sub_labels,
                    'group_by' => $res->tab_group_by);
            }
        }
    }

    public function getElementsVar() {
        return $this->_elements;
    }

    /**
     * Get list of evaluation element
     *
     * @param      int displayed in Fabrik List ; yes=1
     *
     * @return    string list of Fabrik element ID used in evaluation form
     **@throws Exception
     */
    public function getEvaluationElements($show_in_list_summary=1, $hidden=0) {
        $session = JFactory::getSession();
        $h_files = new EmundusHelperFiles;
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getString('cfnums', null);

        if ($session->has('filt_params'))
        {
            //var_dump($session->get('filt_params'));
            $element_id = array();
            $filt_params = $session->get('filt_params');

            if (is_array($filt_params['programme']) && count(@$filt_params['programme']) > 0) {
                foreach ($filt_params['programme'] as $value) {
                    $groups = $this->getGroupsEvalByProgramme($value);
                    if (empty($groups)) {
                        $eval_elt_list = array();
                    } else {
                        $eval_elt_list = $this->getElementsByGroups($groups, $show_in_list_summary, $hidden);
                        if (count($eval_elt_list)>0) {
                            foreach ($eval_elt_list as $eel) {
                                if(isset($eel->element_id) && !empty($eel->element_id))
                                    $elements_id[] = $eel->element_id;
                            }
                        }
                    }
                }
            }
            if (!empty($filt_params['campaign']) && is_array($filt_params['campaign']) && count(@$filt_params['campaign']) > 0) {
                foreach ($filt_params['campaign'] as $value) {
                    $campaign = $h_files->getCampaignByID($value);
                    $groups = $this->getGroupsEvalByProgramme($campaign['training']);
                    if (empty($groups)) {
                        $eval_elt_list = array();
                    } else {
                        $eval_elt_list = $this->getElementsByGroups($groups, $show_in_list_summary, $hidden);
                        if (is_array($eval_elt_list) && count($eval_elt_list) > 0) {
                            foreach ($eval_elt_list as $eel) {
                                if (isset($eel->element_id) && !empty($eel->element_id))
                                    $elements_id[] = $eel->element_id;
                            }
                        }
                    }
                }
            }
        }
//die(var_dump($elements_id));
        return @$elements_id;
    }

    /**
     * Get list of evaluation elements
     *
     * @param      int show_in_list_summary get elements displayed in Fabrik List ; yes=1
     * @param      int hidden get hidden elements ; yes=1
     * @param      array code get elements from Evaluation form defined for programme list
     *
     * @return    array list of Fabrik element ID used in evaluation form
     **@throws Exception
     */
    public function getEvaluationElementsName($show_in_list_summary=1, $hidden=0, $code = array(), $all = null) {
        $session = JFactory::getSession();
        $h_list = new EmundusHelperList;

        $elements = array();
        if ($session->has('filt_params') ||!empty($all)) {

            $filt_params = $session->get('filt_params');

            if (!empty($code)) {
                $programmes = array_unique($code);
            } elseif ($filt_params['programme'][0] !== '%' && is_array(@$filt_params['programme']) && count(@$filt_params['programme']) > 0) {
                $programmes = array_unique($filt_params['programme']);
            } else {
                return array();
            }
            foreach ($programmes as $value) {
                $groups = $this->getGroupsEvalByProgramme($value);

                if (empty($groups)) {
                    $eval_elt_list = array();
                } else {
                    $eval_elt_list = $this->getElementsByGroups($groups, $show_in_list_summary, $hidden);

                    if (count($eval_elt_list) > 0) {
                        foreach ($eval_elt_list as $eel) {
                            if (isset($eel->element_id) && !empty($eel->element_id)) {
                                $elements[] = $h_list->getElementsDetailsByID($eel->element_id)[0];
                            }
                        }
                    }
                }
            }
        }

        return $elements;
    }

    /**
     * Get list of ALL evaluation element
     *
     * @param int $show_in_list_summary
     * @param      string code of the programme
     *
     * @return array list of Fabrik element ID used in evaluation form
     * @throws Exception
     */
    public function getAllEvaluationElements($show_in_list_summary=1, $programme_code) {
        $session = JFactory::getSession();

        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getString('cfnums', null);

        if ($session->has('filt_params')) {
            //var_dump($session->get('filt_params'));
            $elements_id = array();
            $filt_params = $session->get('filt_params');

            if (is_array(@$filt_params['programme']) && $filt_params['programme'][0] != '%' && !empty(array_filter($filt_params['programme']))) {
                foreach ($filt_params['programme'] as $value) {
                    if ($value == $programme_code) {
                        $groups = $this->getGroupsEvalByProgramme($value);
                        if (!empty($groups)) {
                            $eval_elt_list = $this->getAllElementsByGroups($groups); // $show_in_list_summary
                            if (count($eval_elt_list)>0) {
                                foreach ($eval_elt_list as $eel) {
                                    $elements_id[] = $eel->element_id;
                                }
                            }
                        }
                    }
                }
            } else {
                $groups = $this->getGroupsEvalByProgramme($programme_code);
                if (!empty($groups)) {
                    $eval_elt_list = $this->getAllElementsByGroups($groups); // $show_in_list_summary
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
     * Get list of ALL decision elements
     *
     * @param      int displayed in Fabrik List ; yes=1
     * @param      string code of the programme
     *
     * @return    array list of Fabrik element ID used in evaluation form
     **@throws Exception
     */
    public function getAllDecisionElements($show_in_list_summary=1, $programme_code) {
        $session = JFactory::getSession();

        $jinput = JFactory::getApplication()->input;
        //$fnums = $jinput->getString('cfnums', null);

        if ($session->has('filt_params'))
        {
            //var_dump($session->get('filt_params'));
            $elements_id = array();
            $filt_params = $session->get('filt_params');

            if (is_array(@$filt_params['programme']) && $filt_params['programme'][0] != '%') {
                foreach ($filt_params['programme'] as $value) {
                    if ($value == $programme_code) {
                        $groups = $this->getGroupsDecisionByProgramme($value);
                        if (!empty($groups)) {
                            $eval_elt_list = $this->getAllElementsByGroups($groups, $show_in_list_summary); // $show_in_list_summary
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
                    $eval_elt_list = $this->getAllElementsByGroups($groups, $show_in_list_summary); // $show_in_list_summary
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

    public function _buildContentOrderBy() {
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
        $can_be_ordering[] = 'jos_emundus_evaluations.user';
        $can_be_ordering[] = 'fnum';
        $can_be_ordering[] = 'status';
        $can_be_ordering[] = 'c.status';
        $can_be_ordering[] = 'name';
        $can_be_ordering[] = 'eta.id_tag';
        $can_be_ordering[] = 'overall';

        if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering))
            return ' ORDER BY '.$filter_order.' '.$filter_order_Dir;

        return '';
    }

    public function multi_array_sort($multi_array = array(), $sort_key, $sort = SORT_ASC) {

        if (is_array($multi_array)) {
            foreach ($multi_array as $key => $row_array) {

                if (is_array($row_array))
                    @$key_array[$key] = $row_array[$sort_key];
                else
                    return -1;

            }

        } else return -1;

        if (!empty($key_array))
            array_multisort($key_array, $sort, $multi_array);

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

    public function getProfileAcces($user) {
        $db = JFactory::getDBO();
        $query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id=' . $user;
        $db->setQuery($query);
        return $db->loadResultArray();
    }

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
            if ($tmp != $unit->user_id)
                $list[$unit->user_id] =
                    @EmundusHelperList::getBoxValue($this->details->{$tab . '__' . $elem}, $unit->{$tab . '__' . $elem},
                        $elem);
            else
                $list[$unit->user_id] .= ',' . @EmundusHelperList::getBoxValue($this->details->{$tab . '__' . $elem},
                        $unit->{$tab . '__' . $elem}, $elem);
            $tmp = $unit->user_id;
        }
        return $list;
    }

    public function setSelect($search) {
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
                        if ($this->details->{$tab[0] . '__' . $tab[1]}['group_by'])
                            $this->subquery[$tab[0] . '__' . $tab[1]] = $this->setSubQuery($tab[0], $tab[1]);
                        else $cols[] = $c . ' AS ' . $tab[0] . '__' . $tab[1];
                    }
                }
                $i++;
            }
            if (count($cols > 0) && !empty($cols))
                $cols = implode(', ', $cols);
        }
        return $cols;
    }

    public function isJoined($tab, $joined) {
        foreach ($joined as $j)
            if ($tab == $j) return true;
        return false;
    }

    public function setJoins($search, $query, $joined) {
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
        if (!empty($cols)) $query .= ', ' . $cols;
        if (!empty($cols_other)) $query .= ', ' . $cols_other;
        if (!empty($cols_default)) $query .= ', ' . $cols_default;
        if (!empty($col_validate)) $query .= ', ' . $col_validate;
        $query .= '	FROM #__emundus_campaign_candidature
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
    public function setEvalList($search, &$eval_list, $head_val, $applicant)  {
        if (!empty($search)) {
            foreach ($search as $c) {
                if (!empty($c)) {
                    $name = explode('.', $c);
                    if (!in_array($name[0] . '__' . $name[1], $head_val)) {
                        $print_val = '';
                        if ($this->details->{$name[0] . '__' . $name[1]}['group_by']
                            && array_key_exists($name[0] . '__' . $name[1], $this->subquery)
                            && array_key_exists($applicant->user_id, $this->subquery[$name[0] . '__' . $name[1]])
                        ) {
                            $eval_list[$name[0] . '__' . $name[1]] = @EmundusHelperList::createHtmlList(explode(",",
                                $this->subquery[$name[0] . '__' . $name[1]][$applicant->user_id]));
                        } elseif ($name[0] == 'jos_emundus_training') {
                            $eval_list[$name[1]] = $applicant->{$name[1]};
                        } elseif (!$this->details->{$name[0] . '__' . $name[1]}['group_by']) {
                            $eval_list[$name[0] . '__' . $name[1]] =
                                @EmundusHelperList::getBoxValue($this->details->{$name[0] . '__' . $name[1]},
                                    $applicant->{$name[0] . '__' . $name[1]}, $name[1]);
                        } else
                            $eval_list[$name[0] . '__' . $name[1]] = $applicant->{$name[0] . '__' . $name[1]};
                    }
                }
            }
        }
    }



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

                                if (count($tab) > 1 && !empty($v)) {

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

                                            } if (!isset($query[$table_join])) {

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
                                if (!empty($value)) {

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
                                                    if (!array_key_exists($tab[0], $tableAlias))
                                                        $query['join'] .= ' left join '.$tab[0].' on ' .$tab[0].'.fnum like c.fnum ';
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
                            if (!isset($query['final_g'])) {
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
                                if (!array_key_exists('jos_emundus_groups_eval', $tableAlias))
                                    $query['join'] .= ' left join #__emundus_groups_eval as ge on ge.applicant_id = c.applicant_id and ge.campaign_id = c.campaign_id ';
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

                    /*case 'profile':
                        if(!empty($value))
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
                        break;*/

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

                            $filt_menu_defined = (isset($filt_menu['status'][0]) && $filt_menu['status'][0] != '' && $filt_menu['status'] != "%");

                            // session filter is empty
                            if ($value[0] == "%" || !isset($value[0]) || $value[0] == '' ) {

                                if (!$filt_menu_defined) {
                                    $query['q'] .= ' ';
                                } else {
                                    $query['q'] .= ' and c.status IN (' . implode(',', $filt_menu['status']) . ') ';
                                }

                            } else {
                                // Check if session filter exist in menu filter, if at least one session filter not in menu filter, reset to menu filter
                                $diff = array();
                                if (is_array($value) && $filt_menu_defined) {
                                    $diff = array_diff($value, $filt_menu['status']);
                                }

                                if (count($diff) == 0) {
                                    $query['q'] .= ' and c.status IN (' . implode(',', $value) . ') ';
                                } else {
                                    $query['q'] .= ' and c.status IN (' . implode(',', $filt_menu['status']) . ') ';
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
                                    $query['q'] .= 'AND c.fnum IN (SELECT ta.fnum FROM jos_emundus_tag_assoc as ta WHERE ta.id_tag IN ('.implode(',', $value).'))';
                                }
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

        // force menu filter
        if ((is_array($filt_menu['status']) && count($filt_menu['status']) > 0) && isset($filt_menu['status'][0]) && !empty($filt_menu['status'][0]) && $filt_menu['status'][0] != "%") {
            $query['q'] .= ' AND c.status IN ("' . implode('","', $filt_menu['status']) . '") ';
        }

        if (isset($filt_menu['programme'][0]) && $filt_menu['programme'][0] == "%") {
            $sql_code = '1=1';
            $and = ' AND ';
        } elseif (isset($filt_menu['programme'][0]) && !empty($filt_menu['programme'][0])) {
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
            $query['q'] .= ' AND (' . $sql_code . ' ' . $sql_fnum . ') ';
        } else {
            $query['q'] .= ' AND 1=2 ';
        }
        return $query;
    }

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

    public function getUsers($current_fnum = null) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');

        $session = JFactory::getSession();
        $dbo = $this->getDbo();
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $evaluators_can_see_other_eval = $eMConfig->get('evaluators_can_see_other_eval', '0');
        $current_user = JFactory::getUser();

        $query = 'select c.fnum, ss.step, ss.value as status, concat(upper(trim(eu.lastname))," ",eu.firstname) AS name, ss.class as status_class, sp.code ';

        $group_by = 'GROUP BY c.fnum ';

        // prevent double left join on query
        $lastTab = array('#__emundus_setup_status', 'jos_emundus_setup_status',
            '#__emundus_setup_programmes', 'jos_emundus_setup_programmes',
            '#__emundus_setup_campaigns', 'jos_emundus_setup_campaigns',
            '#__emundus_evaluations', 'jos_emundus_evaluations',
            '#__emundus_users', 'jos_emundus_users',
            '#__users', 'jos_users',
            '#__emundus_tag_assoc', 'jos_emundus_tag_assoc'
        );
        $leftJoin = '';
        if (count($this->_elements) > 0) {
            foreach ($this->_elements as $elt) {
                if (!isset($lastTab)) {
                    $lastTab = array();
                }
                if (!in_array($elt->tab_name, $lastTab)) {
                    $leftJoin .= 'left join '.$elt->tab_name.' ON '.$elt->tab_name.'.fnum = c.fnum ';
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
        $query .= ', jos_emundus_evaluations.id AS evaluation_id, CONCAT(eue.lastname," ",eue.firstname) AS evaluator';
        $group_by .= ', evaluation_id';
        if (count($this->_elements_default) > 0) {
            $query .= ', '.implode(',', $this->_elements_default);
        }



        $query .= ' FROM #__emundus_campaign_candidature as c
					LEFT JOIN #__emundus_setup_status as ss on ss.step = c.status
					LEFT JOIN #__emundus_setup_campaigns as esc on esc.id = c.campaign_id
					LEFT JOIN #__emundus_setup_programmes as sp on sp.code = esc.training
					LEFT JOIN #__emundus_users as eu on eu.user_id = c.applicant_id
					LEFT JOIN #__users as u on u.id = c.applicant_id
                    LEFT JOIN (
					  SELECT GROUP_CONCAT(id_tag SEPARATOR ", ") id_tag, fnum
					  FROM jos_emundus_tag_assoc
					  GROUP BY fnum
					) eta ON c.fnum = eta.fnum ' ;
        $q = $this->_buildWhere($lastTab);

        if (EmundusHelperAccess::isCoordinator($current_user->id)
            || (EmundusHelperAccess::asEvaluatorAccessLevel($current_user->id) && $evaluators_can_see_other_eval == 1)
            || EmundusHelperAccess::asAccessAction(5, 'r', $current_user->id)) {
            $query .= ' LEFT JOIN #__emundus_evaluations as jos_emundus_evaluations on jos_emundus_evaluations.fnum = c.fnum ';
        } else {
            $query .= ' LEFT JOIN #__emundus_evaluations as jos_emundus_evaluations on jos_emundus_evaluations.fnum = c.fnum AND (jos_emundus_evaluations.user='.$current_user->id.' OR jos_emundus_evaluations.user IS NULL)';
        }

        if (!empty($leftJoin)) {
            $query .= $leftJoin;
        }
        $query .= ' LEFT JOIN #__emundus_users as eue on eue.user_id = jos_emundus_evaluations.user ';
        $query .= $q['join'];

        if (empty($current_fnum)) {
            $query .= ' WHERE c.status > 0 ';
        } else {
            $query .= ' WHERE c.fnum like '.$current_fnum;
        }


        $query .= $q['q'];
        $query .= $group_by;

        $query .=  $this->_buildContentOrderBy();

        /*
        if (JFactory::getUser()->id == 63)
            echo '<hr>FILES:'.str_replace('#_', 'jos', $query).'<hr>';
        */
        $dbo->setQuery($query);
        try {
            $res = $dbo->loadAssocList();
            $this->_applicants = $res;

            if (empty($current_fnum)) {
                $limit = $session->get('limit');

                $limitStart = $session->get('limitstart');
                if ($limit > 0) {
                    $query .= " limit $limitStart, $limit ";
                }
            }

            $dbo->setQuery($query);
            return $dbo->loadAssocList();
        } catch(Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.str_replace('#_', 'jos', $query), JLog::ERROR, 'com_emundus');
        }
    }

    // get elements by groups
    // @param 	  $show_in_list_summary int show item defined as displayed in Fabrik List ; yes=1
    // @param 	  $hidden int show item defined as hidden in Fabrik List  ; yes=1
    // @params 	  $groups string List of Fabrik groups comma separated
    function getElementsByGroups($groups, $show_in_list_summary=1, $hidden=0){
        return @EmundusHelperFilters::getElementsByGroups($groups, $show_in_list_summary, $hidden);
    }

    // get ALL elements by groups
    // @params string List of Fabrik groups comma separated
    function getAllElementsByGroups($groups){
        return @EmundusHelperFilters::getAllElementsByGroups($groups);
    }

    public function getActionsACL() {
        return $this->_files->getActionsACL();
    }

    public function getDefaultElements() {
        return $this->_elements;
    }

    public function getSelectList() {
        return $this->_files->getSelectList();
    }

    public function getProfiles() {
        return $this->_files->getProfiles();
    }

    public function getProfileByID($id) {
        return $this->_files->getProfileByID($id);
    }

    public function getProfilesByIDs($ids) {
        return $this->_files->getProfilesByIDs($ids);
    }

    public function getAuthorProfiles() {
        return $this->_files->getAuthorProfiles();
    }

    public function getApplicantsProfiles() {
        return $this->_files->getApplicantsProfiles();
    }

    public function getApplicantsByProfile($profile) {
        return $this->_files->getApplicantsByProfile($profile);
    }

    public function getAuthorUsers() {
        return $this->_files->getAuthorUsers();
    }

    public function getMobility() {
        return $this->_files->getMobility();
    }

    public function getElements() {
        return $this->_files->getElements();
    }

    public function getElementsName() {
        return $this->_files->getElementsName();
    }

    public function getTotal() {
        if (empty($this->_total))
            $this->_total = count($this->_applicants);
        return $this->_total;
    }

    public function getPagination() {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
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
    public function getApplicantColumns() {
        $cols = array();
        $cols[] = array('name' => 'user_id', 'label' => 'User id');
        $cols[] = array('name' => 'user', 'label' => 'User id');
        $cols[] = array('name' => 'name', 'label' => 'Name');
        $cols[] = array('name' => 'email', 'label' => 'Email');
        $cols[] = array('name' => 'profile', 'label' => 'Profile');

        return $cols;
    }

    // get string of fabrik group ID use for evaluation form
    public function getGroupsEvalByProgramme($code) {
        $db = $this->getDbo();
        $query = 'select fabrik_group_id from #__emundus_setup_programmes where code like '.$db->Quote($code);
        try {
            if (!empty($code)) {
                $db->setQuery($query);
                return $db->loadResult();
            } else return null;
        } catch(Exception $e) {
            throw $e;
        }
    }


    // get string of fabrik group ID use for evaluation form
    public function getGroupsDecisionByProgramme($code){
        $db = $this->getDbo();
        $query = 'select fabrik_decision_group_id from #__emundus_setup_programmes where code like '.$db->Quote($code);
        try {
            if (!empty($code)) {
                $db->setQuery($query);
                return $db->loadResult();
            } else return null;
        } catch(Exception $e) {
            throw $e;
        }
    }

    public function getSchoolyears() {
        return $this->_files->getSchoolyears();
    }

    public function getAllActions() {
        return $this->_files->getAllActions();
    }

    public function getEvalGroups() {
        return $this->_files->getEvalGroups();
    }

    public function shareGroups($groups, $actions, $fnums) {
        return $this->_files->shareGroups($groups, $actions, $fnums);
    }

    public function shareUsers($users, $actions, $fnums) {
        return $this->_files->shareUsers($users, $actions, $fnums);
    }

    public function getAllTags() {
        return $this->_files->getAllTags();
    }

    public function getAllStatus() {
        return $this->_files->getAllStatus();
    }

    public function tagFile($fnums, $tag) {
        return $this->_files->tagFile($fnums, $tag);
    }

    public function getTaggedFile($tag = null) {
        return $this->_files->getTaggedFile($tag = null);
    }

    public function updateState($fnums, $state) {
        return $this->_files->updateState($fnums, $state);
    }

    public function getPhotos() {
        return $this->_files->getPhotos();
    }

    public function getEvaluatorsFromGroup() {
        return $this->_files->getEvaluatorsFromGroup();
    }
    public function getEvaluators() {
        return $this->_files->getEvaluators();
    }

    public function unlinkEvaluators($fnum, $id, $isGroup) {
        return $this->_files->unlinkEvaluators($fnum, $id, $isGroup);
    }

    public function getFnumInfos($fnum) {
        return $this->_files->getFnumInfos($fnum);
    }

    public function changePublished($fnum, $published = -1) {
        return $this->_files->changePublished($fnum, $published = -1);
    }

    public function getAllFnums() {
        return $this->_files->getAllFnums();
    }

    /*
    * 	Get values of elements by list of files numbers
    *	@param fnums 	List of application files numbers
    *	@param elements 	array of element to get value
    * 	@return array
    */
    public function getFnumArray($fnums, $elements) {
        return $this->_files->getFnumArray($fnums, $elements);
    }

    public function getEvalsByFnum($fnums) {
        return $this->_files->getEvalsByFnum($fnums);
    }

    public function getCommentsByFnum($fnums) {
        return $this->_files->getCommentsByFnum($fnums);
    }

    public function getFilesByFnums($fnums) {
        return $this->_files->getFilesByFnums($fnums);
    }

    /*
    * 	Get list of expert emails send by applicant for programme SU convergence
    * 	@return array
    */
    function getExperts() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select([$db->quoteName('c.last_name'), $db->quoteName('c.first_name'), $db->quoteName('c.email'), 'CONCAT(g.name) AS '.$db->quoteName('group')])
            ->from($db->quoteName('#__jcrm_contacts', 'c'))
            ->leftJoin($db->quoteName('#__jcrm_group_contact', 'gc').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('gc.contact_id'))
            ->leftJoin($db->quoteName('#__jcrm_groups', 'g').' ON '.$db->quoteName('gc.group_id').' = '.$db->quoteName('g.id'))
            ->where($db->quoteName('c.state').' = 1')
            ->andWhere($db->quoteName('c.email').' <> ""');

        $this->_db->setQuery($query);
        return $this->_db->loadAssocList();
    }

    /**
     * Get list of documents generated for email attachment
     *
     * @param          $fnum
     * @param          $campaign_id
     * @param int|null $doc_to_attach
     *
     * @return array|mixed
     */
    function getEvaluationDocuments($fnum, $campaign_id, $doc_to_attach = null) {
        $query = 'SELECT *, eu.id as id, esa.id as attachment_id
					FROM #__emundus_uploads eu
					LEFT JOIN #__emundus_setup_attachments esa ON esa.id=eu.attachment_id
					WHERE eu.fnum like '.$this->_db->Quote($fnum).' AND campaign_id='.$campaign_id.'
					AND eu.attachment_id IN (
						SELECT DISTINCT(esl.attachment_id) FROM #__emundus_setup_letters esl
					)';

        if (!empty($doc_to_attach)) {
            $query .= ' AND eu.attachment_id = '.(int)$doc_to_attach;
        }

        $query .= ' AND eu.filename NOT LIKE "%lock%"
					ORDER BY eu.timedate';

        $this->_db->setQuery( $query );
        return $this->_db->loadObjectList();
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

    /**
     * 	Get evaluations for fnum done by a user
     *	@param fnum 		Application File number
     *	@param user 		user
     * 	@return array
     **/
    function getEvaluationsFnumUser($fnum, $user) {

        try {

            $query = 'SELECT *
					FROM #__emundus_evaluations ee
					WHERE ee.fnum like ' . $this->_db->Quote($fnum) . ' AND user = ' . $user;
//die(str_replace('#_', 'jos', $query));
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    /**
     * 	Get all evaluations accross all programs for a student
     *	@param user 		the student in question
     * 	@return array
     **/
    function getEvaluationsByStudent($user) {
        try {

            $query = 'SELECT * FROM #__emundus_evaluations ee WHERE ee.student_id = ' . $user;
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    /**
     * 	Get all evaluations accross all programs for a student application file
     *	@param fnum 		the file number in question
     * 	@return array
     **/
    function getEvaluationsByFnum($fnum) {
        try {

            $query = 'SELECT * FROM #__emundus_evaluations ee WHERE ee.fnum like ' . $fnum.' ORDER BY ee.id DESC' ;
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    /**
     * 	Copy a line by ID from the evaluation table and use it to overrite or create another line
     *	@param $fromID 		line to copy data from
     *  @param $toID  		line to copy data to
     * 	@return boolean
     **/
    function copyEvaluation($fromID, $toID = null, $fnum = null, $student = null, $user = null) {

        try {

            $query = 'SELECT * FROM #__emundus_evaluations ee WHERE ee.id = ' . $this->_db->Quote($fromID);
            $this->_db->setQuery($query);
            $fromDATA = $this->_db->loadAssoc();

            $query = 'SELECT count(*) as total FROM #__emundus_evaluations ee WHERE ee.id = ' . $this->_db->Quote($toID);
            $this->_db->setQuery($query);
            $toDATA = $this->_db->loadAssoc();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        // Check if we are updating an existing evaluation or adding a new one
        if ($toDATA['total'] == 1) {

            $query = "UPDATE #__emundus_evaluations as ee SET ";
            foreach ($fromDATA as $fromK => $fromV) {
                if ($fromK != "id" && $fromK != "time_date" && $fromK != "user" && $fromK != "fnum" && $fromK != "student_id" && isset($fromV))
                    $query .= $fromK." = ".$this->_db->Quote($fromV).", ";
            }
            $query = rtrim($query, ", ");
            $query .= " WHERE id = ".$this->_db->Quote($toID);

        } else {

            $config = JFactory::getConfig();
            $timezone = new DateTimeZone( $config->get('offset') );
            $now = JFactory::getDate()->setTimezone($timezone);


            $query = "INSERT INTO #__emundus_evaluations ( ";
            foreach ($fromDATA as $fromK => $fromV) {
                $query .= $fromK.", ";
            }
            $query = rtrim($query, ", ");
            $query .= ") VALUES ( ";

            foreach ($fromDATA as $fromK => $fromV) {

                if ($fromK == "id" || empty($fromV))
                    $query .= "'', ";
                elseif ($fromK == "time_date")
                    $query .= $this->_db->Quote($now).", ";
                elseif ($fromK == "user")
                    $query .= $this->_db->Quote($user).", ";
                elseif ($fromK == "fnum")
                    $query .= $this->_db->Quote($fnum).", ";
                elseif ($fromK == "student_id")
                    $query .= $this->_db->Quote($student).", ";
                else
                    $query .= $this->_db->Quote($fromV).", ";

            }
            $query = rtrim($query, ", ");
            $query .= " )";
        }

        try {

            $this->_db->setQuery($query);
            $this->_db->execute();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        return true;
    }

    /**
     * 	Get evaluations for fnum done by a user
     *	@param fnum 		Application File number
     * 	@return array
     **/
    function getDecisionFnum($fnum) {

        try {
            $query = 'SELECT *
					FROM #__emundus_final_grade efg
					WHERE efg.fnum like ' . $this->_db->Quote($fnum);
//die(str_replace('#_', 'jos', $query));
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    function getLettersTemplate($eligibility, $training) {

        //$query = "SELECT * FROM #__emundus_setup_letters WHERE status=".$eligibility." AND training=".$this->_db->Quote($training);
        $query = "SELECT `l`.`id`, `l`.`header`, `l`.`body`, `l`.`footer`, `l`.`title`, `l`.`attachment_id`, `l`.`template_type`, `l`.`file`, `lrs`.`status` , `lrt`.`training`
					FROM jos_emundus_setup_letters as l
					left join jos_emundus_setup_letters_repeat_status as lrs on lrs.parent_id=l.id
					left join jos_emundus_setup_letters_repeat_training as lrt on lrt.parent_id=l.id
					WHERE `lrs`.`status` IN (".$eligibility.") AND `lrt`.`training` in (".$this->_db->Quote($training).")
					GROUP BY l.id";
        try {
            $this->_db->setQuery($query);
            return $this->_db->loadAssocList();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }

    }

    function getLettersTemplateByID($id = null) {

        try {
            $query = "SELECT l.*, GROUP_CONCAT( DISTINCT(`lrs`.`status`) SEPARATOR ',' ) as `status`, CONCAT('\"',GROUP_CONCAT( DISTINCT(`lrt`.`training`) SEPARATOR '\",\"' ), '\"') as `training`
            			FROM #__emundus_setup_letters as l
            			left join #__emundus_setup_letters_repeat_status as lrs on lrs.parent_id=l.id
						left join #__emundus_setup_letters_repeat_training as lrt on lrt.parent_id=l.id ";

            if (isset($id) && !empty($id))
                $query .= 'WHERE l.id=' . $id;

            $this->_db->setQuery($query);
            return $this->_db->loadAssocList();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }
    /*
    * 	Get evaluations form ID By programme code
    *	@param code 		code of the programme
    * 	@return int
    */
    function getEvaluationFormByProgramme($code = null) {

        if ($code === NULL) {
            $session = JFactory::getSession();
            if ($session->has('filt_params')) {
                $filt_params = $session->get('filt_params');
                if (isset($filt_params['programme']) && !empty($filt_params['programme'])) {
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

        } catch (Exception $e) {
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
            if ($session->has('filt_params')) {
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

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    /**
     * @param $fnums
     * @return mixed
     * @throws Exception
     */
    public function getEvaluationAverageByFnum($fnums) {
        $dbo = $this->getDbo();
        $query = 'SELECT AVG(overall) AS overall, fnum FROM #__emundus_evaluations WHERE fnum IN ("'.implode('","', $fnums).'") AND overall IS NOT NULL GROUP BY fnum';

        try {

            $dbo->setQuery($query);
            return $dbo->loadAssocList('fnum', 'overall');

        } catch(Exception $e) {
            JLog::add('Error getting evaluation averages : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getEvalsByFnums($fnums) {
        try {

            $query = 'SELECT * FROM #__emundus_evaluations ee WHERE ee.fnum IN ("'.implode('","', $fnums).'") ORDER BY ee.id DESC' ;
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();

        } catch(Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

    public function delevaluation($id) {
        try {

            $query = 'DELETE FROM #__emundus_evaluations WHERE id='.$id;
            $this->_db->setQuery($query);
            return $this->_db->Query();

        } catch (Exception $e) {
            JLog::add('Error in model/evaluation at query: '.$query, JLog::ERROR, 'com_emundus');
        }
    }

    function getEvaluationById($id) {
        try {
            $query = 'SELECT * FROM #__emundus_evaluations ee WHERE ee.id = ' . $id;
            $this->_db->setQuery($query);
            return $this->_db->loadObject();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }


    /**
     * @param $fnum
     *
     * @return array|bool
     */
    function getScore($fnum) {

        $fnumInfos = $this->_files->getFnumInfos($fnum);
        $query = 'SELECT fe.id, fe.name, fe.label, fe.params, fl.db_table_name FROM #__fabrik_elements AS fe
				LEFT JOIN #__fabrik_groups AS fg ON fg.id = fe.group_id
				LEFT JOIN #__fabrik_formgroup AS ffg ON ffg.group_id = fg.id
				LEFT JOIN #__fabrik_forms AS ff ON ffg.form_id = ff.id 
				LEFT JOIN #__fabrik_lists as fl ON fl.form_id = ff.id
				WHERE fg.id IN (
					SELECT p.fabrik_group_id FROM #__emundus_setup_programmes AS p WHERE p.code LIKE '.$this->_db->quote($fnumInfos['training']).'
				) AND fe.published = 1 AND fe.params NOT LIKE '.$this->_db->quote('%"comment":""%');
        $this->_db->setQuery($query);
        try {
            $elements = $this->_db->loadObjectList('id');
        } catch (Exception $e) {
            return false;
        }

        $query = $this->_db->getQuery(true);
        $return = [];
        foreach ($elements as $element) {

            $query->clear()
                ->select($this->_db->quoteName($element->name))
                ->from($this->_db->quoteName($element->db_table_name))
                ->where('fnum LIKE '.$this->_db->quote($fnum));
            $this->_db->setQuery($query);

            try {
                $value = $this->_db->loadResult();
            } catch (Exception $e) {
                continue;
            }

            if (empty($value)) {
                $value = 0;
            }

            // The highest possible value of the evaluation criteria is hidden in the comment.
            $max = json_decode($element->params)->comment;

            // Make an array containing all element names brought to 10.
            $return[$element->label] = ($value/$max)*10;

        }

        return $return;
    }

    /// get distinct attachment
    public function getAttachmentByIds(array $ids) : array{
        $query = $this->_db->getQuery(true);

        $query
            ->select('*')
            ->from($this->_db->quoteName('#__emundus_setup_attachments'))
            ->where($this->_db->quoteName('#__emundus_setup_attachments.id') . 'IN ('. implode(',', $ids) . ')');

        $this->_db->setQuery($query);
        try {
            return $this->_db->loadAssocList();
        } catch(Exception $e) {
            return [];
        }
    }

    /// get letters by fnums
    public function getLettersByFnums($fnums, $attachments = false) {

        if (empty($fnums)) {
            return false;
        }

        try {
            /// first --> from fnum --> get fnum info
            $_mFile = new EmundusModelFiles;
            $_fnumArray = explode(',', $fnums);

            $_fnumsInfo = $_mFile->getFnumsInfos($_fnumArray);

            $_programs = [];
            $_campaigns = [];
            $_status = [];

            foreach($_fnumsInfo as $fnum) {
                $_programs[] = $fnum['training'];
                $_campaigns[] = $fnum['campaign_id'];
                $_status[] = $fnum['step'];
            }

            $_letters = $this->getLettersByProgrammesStatusCampaigns($_programs,$_status, $_campaigns);

            if (!empty($_letters)) {
                if ($attachments == true) {
                    /// from $_letters --> get distinct attachment_id
                    $_letter_attachment_ids = [];
                    foreach ($_letters as $letter) {
                        $_letter_attachment_ids[$letter->attachment_id] = $letter->attachment_id;
                    }
                    return $this->getAttachmentByIds($_letter_attachment_ids);
                } else {
                    /// from $_letters -> det distinct letter id
                    $_letter_ids = [];
                    foreach ($_letters as $letter) {
                        $_letter_ids[] = $letter->id;
                    }
                    return $_letter_ids;
                }
            } else {
                return [];
            }
        } catch(Exception $e) {
            return [];
        }
    }

    /// get letters by traininng and status
    public function getLettersByProgrammesStatusCampaigns($programs=array(), $status=array(), $campaigns=array()) : array{
        $query = $this->_db->getQuery(true);

        try {
            $query
                ->select('jesl.*')
                ->from($this->_db->quoteName('#__emundus_setup_letters', 'jesl'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_letters_repeat_status', 'jeslrs') . ' ON ' . $this->_db->quoteName('jesl.id') . ' = ' . $this->_db->quoteName('jeslrs.parent_id'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_letters_repeat_training', 'jeslrt') . ' ON ' . $this->_db->quoteName('jesl.id') . ' = ' . $this->_db->quoteName('jeslrt.parent_id'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_letters_repeat_campaign', 'jeslrc') . ' ON ' . $this->_db->quoteName('jesl.id') . ' = ' . $this->_db->quoteName('jeslrc.parent_id'))
                ->where($this->_db->quoteName('jeslrs.status') . ' IN (' . implode(',', $status) . ')')
                ->andWhere($this->_db->quoteName('jeslrt.training') . ' IN (' . implode(',', $this->_db->quote($programs)) . ') OR ' .$this->_db->quoteName('jeslrc.campaign') . ' IN (' . implode(',', $this->_db->quote($campaigns)) . ')');

            $this->_db->setQuery($query);

            return $this->_db->loadObjectList();

        } catch(Exception $e) {
            return [];
        }
    }

    /// get exactly letter id by fnum and template (32,33,34)
    public function getLetterTemplateForFnum($fnum,$templates=array()) : array {
        if (empty($fnum) || empty($templates)) { return []; }

        $query = $this->_db->getQuery(true);

        try {
            /// first :: get fnum info
            $_mFile = new EmundusModelFiles;
            $fnum_infos = $_mFile->getFnumInfos($fnum);

            $_fnumStatus = $fnum_infos['status'];
            $_fnumProgram = $fnum_infos['training'];
            $_fnumCampaign = $fnum_infos['id'];

            /// second :: status, program, templates --> detect the letter id to generate
            $query
                ->select('jesl.*')
                ->from($this->_db->quoteName('#__emundus_setup_letters', 'jesl'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_letters_repeat_status', 'jeslrs') . ' ON ' . $this->_db->quoteName('jesl.id') . ' = ' . $this->_db->quoteName('jeslrs.parent_id'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_letters_repeat_training', 'jeslrt') . ' ON ' . $this->_db->quoteName('jesl.id') . ' = ' . $this->_db->quoteName('jeslrt.parent_id'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_letters_repeat_campaign', 'jeslrc') . ' ON ' . $this->_db->quoteName('jesl.id') . ' = ' . $this->_db->quoteName('jeslrc.parent_id'))
                ->where($this->_db->quoteName('jeslrs.status') . ' = ' . $_fnumStatus)
                ->andWhere($this->_db->quoteName('jeslrt.training') . ' = ' . $this->_db->quote($_fnumProgram) . ' OR ' . $this->_db->quoteName('jeslrc.campaign') . ' = ' . $this->_db->quote($_fnumCampaign))
                ->andWhere($this->_db->quoteName('jesl.attachment_id') . ' IN (' . implode(',', $templates) . ')')
                ->order('id ASC');

            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch(Exception $e) {
            return [];
        }
    }

    /// get affected letters by [fnums] and [templates]
    public function getLettersByFnumsTemplates($fnums=array(), $templates=array()) {
        if (empty($fnum) || empty($templates)) {
            return [];
        }
        /// from each fnum --> get fnum infos
        $letter_ids = [];
        $fnum_Array = explode(',', $fnums);

        foreach($fnum_Array as $fnum) {
            $letter_ids[] = $this->getLetterTemplateForFnum($fnum,$templates);
        }

        return $letter_ids;
    }

    /// get uploaded files by document type and fnums
    public function getFilesByAttachmentFnums($attachment, $fnums=array()) {
        if (empty($fnums) || empty($attachment)) {
            return [];
        }
        $query = $this->_db->getQuery(true);

        $query
            ->select('#__emundus_uploads.*, #__emundus_setup_attachments.lbl, #__emundus_setup_attachments.value')
            ->from($this->_db->quoteName('#__emundus_uploads'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_attachments') . 'ON' . $this->_db->quoteName('#__emundus_setup_attachments.id') . ' = ' . $this->_db->quoteName('#__emundus_uploads.attachment_id'))
            ->where($this->_db->quoteName('#__emundus_uploads.attachment_id') . ' = ' . (int)$attachment)
            ->andWhere($this->_db->quoteName('#__emundus_uploads.fnum') . ' IN (' . implode(', ', $this->_db->quote($fnums)) . ')');
        $this->_db->setQuery($query);

        try {
            return $this->_db->loadObjectList();
        } catch(Exception $e) {
            return [];
        }
    }

    /// generate letters
    public function generateLetters($fnums, $templates, $canSee, $showMode, $mergeMode) {
        $user = JFactory::getUser();

        $eMConfig = JComponentHelper::getParams('com_emundus');

        /* replace old documents by the latest */
        $replace_document = $eMConfig->get('export_replace_doc', 0);

        $tmp_path = JPATH_SITE . DS . 'tmp' . DS;

        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'evaluation.php');
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'emails.php');
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'users.php');
        require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'fpdi.php');

        $_mEval = new EmundusModelEvaluation;
        $_mFile = new EmundusModelFiles;
        $_mEmail = new EmundusModelEmails;
        $_mUser = new EmundusModelUsers;

        $user = JFactory::getUser();

        $fnum_Array = explode(',', $fnums);

        $res = new stdClass();
        $res->status = true;
        $res->files = [];

        $letters_ids = $_mEval->getLettersByFnumsTemplates($fnums,$templates);

        $letter_count = [];

        foreach($letters_ids as $key => $letter) {
            foreach($letter as $data => $value) {
                $letter_count[] = $value->id;
            }
        }

        $available_fnums = [];

        /// a partir de $fnums + $templates --> generer les lettres qui correspondent
        foreach($fnum_Array as $key => $fnum) {
            $generated_letters = $_mEval->getLetterTemplateForFnum($fnum,$templates); // return :: Array
            $fnumInfo = $_mFile->getFnumsTagsInfos([$fnum]);

            if(empty($generated_letters)) {

                $path = EMUNDUS_PATH_ABS . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'] . '_tmp';
                $url = JURI::base() . EMUNDUS_PATH_REL . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'] . '_tmp' . DS;

                if(!file_exists($path)) {mkdir($path, 0755, true);}
                continue;
            } else {
                $available_fnums[] = $fnum;
            }

            foreach($generated_letters as $key => $letter) {
                // get attachment info
                $attachInfo = $_mFile->getAttachmentInfos($letter->attachment_id);

                /* before to generate letter, refresh all previous generated letters of current day - if $replace_document = true */
                if($replace_document == 1) {
                    $refreshQuery = 'DELETE FROM #__emundus_uploads WHERE #__emundus_uploads.attachment_id = ' . $attachInfo['id'] .
                        ' AND DATE(#__emundus_uploads.timedate) = current_date() ' .
                        ' AND #__emundus_uploads.fnum LIKE ' . $this->_db->quote($fnum);

                    $this->_db->setQuery($refreshQuery);
                    $this->_db->execute();
                }

                $type = $letter->template_type;

                switch ((int)$type) {
                    case 1:     // simple file
                        /*@unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']) . '_' . $fnum . $attachInfo['lbl'] . '_' . ".pdf");          //// remove existing file
                        @unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnum) . $attachInfo['lbl'] . '_.' . ".pdf");*/                                                    //// remove existing file
                        $file = JPATH_SITE . $letter->file;
                        if (file_exists($file)) {
                            $res->status = true;
                            $rand = rand(0, 1000000);
                            $post = [
                                'TRAINING_CODE' => $fnumInfo[$fnum]['campaign_code'],
                                'TRAINING_PROGRAMME' => $fnumInfo[$fnum]['campaign_label'],
                                'CAMPAIGN_LABEL' => $fnumInfo[$fnum]['campaign_label'],
                                'CAMPAIGN_YEAR' => $fnumInfo[$fnum]['campaign_year'],
                                'USER_NAME' => $fnumInfo[$fnum]['applicant_name'],
                                'USER_EMAIL' => $fnumInfo[$fnum]['applicant_email'],
                                'FNUM' => $fnum
                            ];
                            // make file name --- logically, we should avoid to generate many files which have same contents but different name --> fnum will distinguish the file name
                            $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);

                            if (!$anonymize_data) {
                                //$name = $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']) . '_' . $fnum . $attachInfo['lbl'] . '_.' . pathinfo($file)['extension'];
                                $eMConfig = JComponentHelper::getParams('com_emundus');
                                $generated_doc_name = $eMConfig->get('generated_doc_name', "");
                                if (!empty($generated_doc_name)) {
                                    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
                                    $m_checklist = new EmundusModelChecklist;
                                    $name = $m_checklist->formatFileName($generated_doc_name, $fnum, $post);
                                } else {
                                    $name = $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']);
                                }
                                $name = $name.$attachInfo['lbl']."-".md5($rand.time())."_.".pathinfo($file)['extension'];
                            } else {
                                $name = $this->sanitize_filename($fnum) . $attachInfo['lbl'] . '_.' . pathinfo($file)['extension'];
                            }

                            // get file path --> original path + file path, e.g: images/emundus/files/95
                            $original_path = EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'];
                            $original_name = $original_path . DS . $name;

                            // get file path --> letter path + letter file path, e.g: images/emundus/files/95--letters (they will be removed after using)
                            $path = EMUNDUS_PATH_ABS . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'];               /// temp path (remove '_tmp')
                            $path_name = $path . DS . $name;

                            // get url of both original and letter cases
                            $original_url = JURI::base() . EMUNDUS_PATH_REL . $fnumInfo[$fnum]['applicant_id'] . DS;
                            $url = JURI::base() . EMUNDUS_PATH_REL . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'] . '_tmp' . DS;               /// temp url

                            // mkdir original folder if does not exist
                            if(!file_exists($original_path)) { mkdir($original_path, 0755, true); }

                            // mkdir letter folder if does not exist
                            if (!file_exists($path)) { mkdir($path, 0755, true); }

                            /// if exists
                            if (file_exists($original_name) or file_exists($path_name))   {
                                /// remove this file and then create new file (good idea?)
                                unlink($path_name);
                                unlink($original_name);

                                /// remove it in database

                                $query = 'DELETE FROM #__emundus_uploads 
                                                WHERE #__emundus_uploads.fnum LIKE ' . $this->_db->quote($fnum) .
                                                    ' AND #__emundus_uploads.filename = ' . $this->_db->quote($name) .
                                                        ' AND DATE(#__emundus_uploads.timedate) = current_date()';

                                $this->_db->setQuery($query);
                                $this->_db->execute();

                                /// recopy
                                copy($file, $path_name);
                                copy($file, $original_name);

                                /// reupdate in database
                                $upId = $_mFile->addAttachment($fnum, $name, $user->id, $fnumInfo[$fnum]['campaign_id'], $letter->attachment_id, '', $canSee);
                                $res->files[] = array('filename' => $name, 'upload' => $upId, 'url' => $original_url, 'type' => $attachInfo['id']);
                            } else {
                                if (copy($file, $path_name) and copy($file, $original_name)) {
                                    $upId = $_mFile->addAttachment($fnum, $name, $user->id, $fnumInfo[$fnum]['campaign_id'], $letter->attachment_id, '', $canSee);

                                    $res->files[] = array('filename' => $name, 'upload' => $upId, 'url' => $original_url, 'type' => $attachInfo['id']);
                                }
                            }
                        } else {
                            $res->status = false;
                            $res->msg = JText::_('COM_EMUNDUS_LETTERS_ERROR_CANNOT_GENERATE_FILE');
                        }
                        break;

                    /// end of case 1 ///

                    case 2:     /// pdf file from html (tinymce)
                        /*@unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']) . '_' . $fnum . $attachInfo['lbl'] . '_' . ".pdf");
                        @unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnum) . $attachInfo['lbl'] . '_' . ".pdf");*/
                        if (isset($fnumInfo)) {
                            $post = [
                                'TRAINING_CODE' => $fnumInfo[$fnum]['campaign_code'],
                                'TRAINING_PROGRAMME' => $fnumInfo[$fnum]['campaign_label'],
                                'CAMPAIGN_LABEL' => $fnumInfo[$fnum]['campaign_label'],
                                'CAMPAIGN_YEAR' => $fnumInfo[$fnum]['campaign_year'],
                                'USER_NAME' => $fnumInfo[$fnum]['applicant_name'],
                                'USER_EMAIL' => $fnumInfo[$fnum]['applicant_email'],
                                'FNUM' => $fnum
                            ];

                            // Generate PDF
                            $tags = $_mEmail->setTags($fnumInfo[$fnum]['applicant_id'], $post, $fnum, '', $letter->title.$letter->body.$letter->footer);

                            require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'MYPDF.php');
                            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                            $pdf->SetCreator(PDF_CREATOR);
                            $pdf->SetAuthor($user->name);
                            $pdf->SetTitle($letter->title);

                            // Set margins
                            $pdf->SetMargins(5, 40, 5);
                            $pdf->footer = $letter->footer;

                            // Get logo
                            preg_match('#src="(.*?)"#i', $letter->header, $tab);
                            $pdf->logo = JPATH_SITE . DS . @$tab[1];

                            // Get footer
                            preg_match('#src="(.*?)"#i', $letter->footer, $tab);
                            $pdf->logo_footer = JPATH_SITE . DS . @$tab[1];
                            unset($logo, $logo_footer);

                            $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

                            // Set default monospaced font
                            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                            // Set default font subsetting mode
                            $pdf->setFontSubsetting(true);

                            // Set font
                            $pdf->SetFont('freeserif', '', 8);

                            $htmldata = $_mEmail->setTagsFabrik($letter->body, array($fnum));

                            // clean html
                            $htmldata = preg_replace($tags['patterns'], $tags['replacements'], preg_replace("/<span[^>]+\>/i", "", preg_replace("/<\/span\>/i", "", preg_replace("/<br[^>]+\>/i", "<br>", $htmldata))));

                            // base64 images to link
                            $htmldata = preg_replace_callback('#(<img\s(?>(?!src=)[^>])*?src=")data:image/(gif|png|jpeg);base64,([\w=+/]++)("[^>]*>)#', function ($match) {
                                list(, $img, $type, $base64, $end) = $match;

                                $bin = base64_decode($base64);
                                $md5 = md5($bin);   // generate a new temporary filename
                                $fn = "tmp/$md5.$type";
                                file_exists($fn) or file_put_contents($fn, $bin);

                                return "$img$fn$end";  // new <img> tag
                            }, $htmldata);

                            $htmldata = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $htmldata);

                            $pdf->AddPage();

                            // Print text using writeHTMLCell()
                            $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $htmldata, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);

                            $rand = rand(0, 1000000);

                            // make file name --- logically, we should avoid to generate many files which have same contents but different name --> fnum will distinguish the file name
                            $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);
                            if (!$anonymize_data) {
                                //$name = $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']) . '_' . $fnum . $attachInfo['lbl'] . '_' . ".pdf";
                                $eMConfig = JComponentHelper::getParams('com_emundus');
                                $generated_doc_name = $eMConfig->get('generated_doc_name', "");
                                if (!empty($generated_doc_name)) {
                                    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
                                    $m_checklist = new EmundusModelChecklist;
                                    $name = $m_checklist->formatFileName($generated_doc_name, $fnum, $post);
                                } else {
                                    $name = $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']);
                                }
                                $name = $name.$attachInfo['lbl']."-".md5($rand.time()).".pdf";
                            } else {
                                $name = $this->sanitize_filename($fnum) . $attachInfo['lbl'] . '_' . ".pdf";
                            }

                            $original_path = EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'];
                            $original_name = $original_path . DS . $name;

                            $path = EMUNDUS_PATH_ABS . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'];           /// tmp path (remove '_tmp')
                            $path_name = $path . DS . $name;

                            $original_url = JURI::base() . EMUNDUS_PATH_REL . $fnumInfo[$fnum]['applicant_id'] . DS;
                            $url = JURI::base() . EMUNDUS_PATH_REL . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'] . '_tmp' . DS;     /// tmp path

                            ///@ mkdir original folder if does not exists
                            if(!file_exists($original_path)) { mkdir($original_path, 0755, true); }

                            ///@ mkdir new folder which contains only the generated documents
                            if (!file_exists($path)) { mkdir($path, 0755, true); }

                            if (file_exists($path_name) or file_exists($original_name)) {
                                // remove old file and reupdate in database
                                unlink($original_name);
                                unlink($path_name);

                                $query = 'DELETE FROM #__emundus_uploads 
                                                WHERE #__emundus_uploads.fnum LIKE ' . $this->_db->quote($fnum) .
                                                    ' AND #__emundus_uploads.filename = ' . $this->_db->quote($name) .
                                                        ' AND DATE(#__emundus_uploads.timedate) = current_date()';

                                $this->_db->setQuery($query);
                                $this->_db->execute();
                            }
                            /// copy generated letter to --letters folder
                            $upId = $_mFile->addAttachment($fnum, $name, $user->id, $fnumInfo[$fnum]['campaign_id'], $letter->attachment_id, '', $canSee);         ////

                            $pdf->Output($path_name, 'F');
                            $pdf->Output($original_name, 'F');
                            $res->files[] = array('filename' => $name, 'upload' => $upId, 'url' => $original_url, 'type' => $attachInfo['id']);
                        }
                        unset($pdf, $path_name, $name, $url, $upIdn);
                        unset($pdf, $original_name, $name, $original_url, $upIdn);
                        break;

                    /// end of case 2

                    case 3: /// generate pdf from docx --> using Gotenberg
                        /*@unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']) . '_' . $fnum . $attachInfo['lbl'] . '_' . ".docx");
                        @unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']) . '_' . $fnum . $attachInfo['lbl'] . '_' . ".pdf");

                        @unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnum) . $attachInfo['lbl'] . '_' . ".docx");
                        @unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnum) . $attachInfo['lbl'] . '_' . ".pdf");*/

                        require_once(JPATH_LIBRARIES . '/emundus/vendor/autoload.php');
                        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'export.php');

                        $m_Export = new EmundusModelExport;
                        $eMConfig = JComponentHelper::getParams('com_emundus');
                        $gotenberg_activation = $eMConfig->get('gotenberg_activation', 0);

                        $const = array('user_id' => $user->id, 'user_email' => $user->email, 'user_name' => $user->name, 'current_date' => date('d/m/Y', time()));
                        $post = [
                            'TRAINING_CODE' => $fnumInfo[$fnum]['campaign_code'],
                            'TRAINING_PROGRAMME' => $fnumInfo[$fnum]['campaign_label'],
                            'CAMPAIGN_LABEL' => $fnumInfo[$fnum]['campaign_label'],
                            'CAMPAIGN_YEAR' => $fnumInfo[$fnum]['campaign_year'],
                            'USER_NAME' => $fnumInfo[$fnum]['applicant_name'],
                            'USER_EMAIL' => $fnumInfo[$fnum]['applicant_email'],
                            'FNUM' => $fnum
                        ];

                        $special = ['user_dob_age', 'evaluation_radar'];

                        try {
                            $phpWord = new \PhpOffice\PhpWord\PhpWord();
                            $preprocess = $phpWord->loadTemplate(JPATH_SITE . $letter->file);
                            $tags = $preprocess->getVariables();

                            $idFabrik = array();
                            $setupTags = array();

                            foreach ($tags as $i => $val) {
                                $tag = strip_tags($val);
                                if (is_numeric($tag)) {
                                    $idFabrik[] = $tag;
                                } else {
                                    if(strpos($tag, 'IMG_') !== false) {
                                        $setupTags[] = trim(explode(":", $tag)[0]);
                                    } else {
                                        $setupTags[] = $tag;
                                    }
                                }
                            }

                            if (!empty($idFabrik)) {
                                $fabrikElts = $_mFile->getValueFabrikByIds($idFabrik);
                            } else {
                                $fabrikElts = array();
                            }

                            $fabrikValues = array();
                            foreach ($fabrikElts as $elt) {
                                $params = json_decode($elt['params']);
                                $groupParams = json_decode($elt['group_params']);
                                $isDate = ($elt['plugin'] == 'date');
                                $isDatabaseJoin = ($elt['plugin'] === 'databasejoin');

                                if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                                    $fabrikValues[$elt['id']] = $_mFile->getFabrikValueRepeat($elt, [$fnum], $params, $groupParams->repeat_group_button == 1);
                                } else {
                                    if ($isDate) {
                                        $fabrikValues[$elt['id']] = $_mFile->getFabrikValue([$fnum], $elt['db_table_name'], $elt['name'], $params->date_form_format);                   /// $fnum_Array or $fnum ???
                                    } else {
                                        $fabrikValues[$elt['id']] = $_mFile->getFabrikValue([$fnum], $elt['db_table_name'], $elt['name']);                                              /// $fnum_Array or $fnum ???
                                    }
                                }

                                if ($elt['plugin'] == "checkbox" || $elt['plugin'] == "dropdown") {

                                    foreach ($fabrikValues[$elt['id']] as $fnum => $val) {

                                        if ($elt['plugin'] == "checkbox") {
                                            $val = json_decode($val['val']);
                                        } else {
                                            $val = explode(',', $val['val']);
                                        }

                                        if (count($val) > 0) {
                                            foreach ($val as $k => $v) {
                                                $index = array_search(trim($v), $params->sub_options->sub_values);
                                                $val[$k] = JText::_($params->sub_options->sub_labels[$index]);
                                            }
                                            $fabrikValues[$elt['id']][$fnum]['val'] = implode(", ", $val);
                                        } else {
                                            $fabrikValues[$elt['id']][$fnum]['val'] = "";
                                        }
                                    }

                                } elseif ($elt['plugin'] == "birthday") {

                                    foreach ($fabrikValues[$elt['id']] as $fnum => $val) {
                                        $val = explode(',', $val['val']);
                                        foreach ($val as $k => $v) {
                                            if(!empty($v)){
                                                $val[$k] = date($params->details_date_format, strtotime($v));
                                            }
                                        }
                                        $fabrikValues[$elt['id']][$fnum]['val'] = implode(",", $val);
                                    }

                                } else {
                                    if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                                        $fabrikValues[$elt['id']] = $_mFile->getFabrikValueRepeat($elt, [$fnum], $params, $groupParams->repeat_group_button == 1);              /// $fnum_Array or $fnum ???
                                    } else {
                                        $fabrikValues[$elt['id']] = $_mFile->getFabrikValue([$fnum], $elt['db_table_name'], $elt['name']);                                                  /// $fnum_Array or $fnum ???
                                    }
                                }
                            }

                            $preprocess = new \PhpOffice\PhpWord\TemplateProcessor(JPATH_SITE . $letter->file);
                            if (isset($fnumInfo[$fnum])) {
                                $tags = $_mEmail->setTagsWord(@$fnumInfo[$fnum]['applicant_id'], ['FNUM' => $fnum], $fnum, '');

                                foreach ($setupTags as $tag) {
                                    $val = "";
                                    $lowerTag = strtolower($tag);

                                    if (array_key_exists($lowerTag, $const)) {
                                        $preprocess->setValue($tag, $const[$lowerTag]);
                                    } elseif (in_array($lowerTag, $special)) {
                                        switch ($lowerTag) {

                                            // dd-mm-YYYY (YY)
                                            case 'user_dob_age':
                                                $birthday = $_mFile->getBirthdate($fnum, 'd/m/Y', true);
                                                $preprocess->setValue($tag, $birthday->date . ' (' . $birthday->age . ')');
                                                break;

                                            default:
                                                $preprocess->setValue($tag, '');
                                                break;
                                        }
                                    } elseif (!empty(@$fnumInfo[$fnum][$lowerTag])) {
                                        $preprocess->setValue($tag, @$fnumInfo[$fnum][$lowerTag]);
                                    } else {
                                        //$tags = $_mEmail->setTagsWord(@$fnumInfo[$fnum]['applicant_id'], ['FNUM' => $fnum], $fnum, '');
                                        $i = 0;
                                        foreach ($tags['patterns'] as $value) {
                                            if ($value == $tag) {
                                                $val = $tags['replacements'][$i];
                                                break;
                                            }
                                            $i++;
                                        }
                                        // replace tag by image if tag name start by IMG_
                                       if(strpos($tag, 'IMG_') !== false) {
                                            $preprocess->setImageValue($tag, $val);
                                        } else {
                                            $preprocess->setValue($tag, htmlspecialchars($val));
                                       }
                                    }
                                }

                                /// foreach
                                foreach ($idFabrik as $id) {
                                    if (isset($fabrikValues[$id][$fnum])) {
                                        $value = str_replace('\n', ', ', $fabrikValues[$id][$fnum]['val']);
                                        $preprocess->setValue($id, htmlspecialchars($value));
                                    } else {
                                        $preprocess->setValue($id, '');
                                    }
                                }

                                $rand = rand(0, 1000000);

                                /// check if the filename is anonymized -- logically, we should avoid to generate many files which have the same contents, but different name --> bad performance
                                $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);
                                if (!$anonymize_data) {
//                                    $filename = $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']) . '_' . $fnum . $attachInfo['lbl'] . '_' . ".docx";
                                    $eMConfig = JComponentHelper::getParams('com_emundus');
                                    $generated_doc_name = $eMConfig->get('generated_doc_name', "");
                                    if (!empty($generated_doc_name)) {
                                        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
                                        $m_checklist = new EmundusModelChecklist;
                                        $filename = $m_checklist->formatFileName($generated_doc_name, $fnum, $post);
                                    } else {
                                        $filename = $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']);
                                    }
                                    $filename = $filename.$attachInfo['lbl']."-".md5($rand.time()).".docx";
                                } else {
                                    $filename = $this->sanitize_filename($fnum) . $attachInfo['lbl'] . '_' . ".docx";
                                }

                                $original_path = EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'];
                                $original_name = $original_path . DS . $filename;

                                $path = EMUNDUS_PATH_ABS . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'];        /// tmp path (remove '_tmp')
                                $path_name = $path . DS . $filename;

                                $original_url = JURI::base() . EMUNDUS_PATH_REL . $fnumInfo[$fnum]['applicant_id'] . DS;
                                $url = JURI::base() . EMUNDUS_PATH_REL . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'] . '_tmp' . DS;

                                if(!file_exists($original_path)) { mkdir($original_path, 0755, true); }

                                if (!file_exists($path)) { mkdir($path, 0755, true); }

                                /// check if file exists or not
                                if (file_exists($path_name) or file_exists($original_path)) {
                                    $query = 'DELETE FROM #__emundus_uploads 
                                                    WHERE #__emundus_uploads.fnum LIKE ' . $this->_db->quote($fnum) .
                                                        ' AND #__emundus_uploads.filename = ' . $this->_db->quote($filename) .
                                                            ' AND DATE(#__emundus_uploads.timedate) = current_date()';

                                    $this->_db->setQuery($query);
                                    $this->_db->execute();

                                    unlink($original_name);
                                    unlink($path_name);
                                }

                                $preprocess->saveAs($original_name);
                                if ($gotenberg_activation == 1 && $letter->pdf == 1) {
                                    //convert to PDF
                                    $dest = str_replace('.docx', '.pdf', $original_name);
                                    $filename = str_replace('.docx', '.pdf', $filename);
                                    try {
                                        $m_Export->toPdf($original_name, $dest, 'docx', $fnum);
                                    } catch(Exception $e) {
                                        JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                                        return false;
                                    }

                                    copy($original_path . DS . $filename, $path . DS . $filename);

                                    unlink($path . $original_name);
                                    unlink($original_path . DS . $original_name);

                                    $query = 'DELETE FROM #__emundus_uploads 
                                                    WHERE #__emundus_uploads.fnum LIKE ' . $this->_db->quote($fnum) .
                                                        ' AND #__emundus_uploads.filename = ' . $this->_db->quote($filename) .
                                                            ' AND DATE(#__emundus_uploads.timedate) = current_date()';

                                    $this->_db->setQuery($query);
                                    $this->_db->execute();
                                } else {
                                    copy($original_name, $path_name);
                                }

                                $upId = $_mFile->addAttachment($fnum, $filename, $user->id, $fnumInfo[$fnum]['campaign_id'], $letter->attachment_id, '', $canSee);
                                $res->files[] = array('filename' => $filename, 'upload' => $upId, 'url' => $original_url, 'type' => $attachInfo['id']);
                            }
                        } catch (Exception $e) {
                            $res->status = false;
                            $res->msg = JText::_("AN_ERROR_OCURRED") . ':' . $e->getMessage();
                        }
                        break;

                    /// end of case 3

                    case 4:
                        /*@unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']) . '_' . $fnum . $attachInfo['lbl'] . '_' . ".xlsx");
                        @unlink(EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'] . DS . $this->sanitize_filename($fnum) . $attachInfo['lbl'] . '_' . ".xlsx");*/

                        require_once(JPATH_LIBRARIES . '/emundus/vendor/autoload.php');

                        $inputFileName = JPATH_SITE . $letter->file;
                        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
                        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

                        $reader->setIncludeCharts(true);
                        $spreadsheet = $reader->load($inputFileName);

                        $const = array('user_id' => $user->id, 'user_email' => $user->email, 'user_name' => $user->name, 'current_date' => date('d/m/Y', time()));
                        $post = [
                            'TRAINING_CODE' => $fnumInfo[$fnum]['campaign_code'],
                            'TRAINING_PROGRAMME' => $fnumInfo[$fnum]['campaign_label'],
                            'CAMPAIGN_LABEL' => $fnumInfo[$fnum]['campaign_label'],
                            'CAMPAIGN_YEAR' => $fnumInfo[$fnum]['campaign_year'],
                            'USER_NAME' => $fnumInfo[$fnum]['applicant_name'],
                            'USER_EMAIL' => $fnumInfo[$fnum]['applicant_email'],
                            'FNUM' => $fnum
                        ];

                        $special = ['user_dob_age'];

                        if (isset($fnumInfo[$fnum])) {
                            $preprocess = $spreadsheet->getAllSheets(); //Search in each sheet of the workbook

                            foreach ($preprocess as $sheet) {
                                foreach ($sheet->getRowIterator() as $row) {
                                    $cellIterator = $row->getCellIterator();
                                    foreach ($cellIterator as $cell) {

                                        $cell->getValue();

                                        $regex = '/\$\{(.*?)}|\[(.*?)]/';
                                        preg_match_all($regex, $cell, $matches);

                                        $idFabrik = array();
                                        $setupTags = array();

                                        foreach ($matches[1] as $i => $val) {

                                            $tag = strip_tags($val);

                                            if (is_numeric($tag)) {
                                                $idFabrik[] = $tag;
                                            } else {
                                                $setupTags[] = $tag;
                                            }
                                        }

                                        if (!empty($idFabrik)) {
                                            $fabrikElts = $_mFile->getValueFabrikByIds($idFabrik);
                                        } else {
                                            $fabrikElts = array();
                                        }

                                        /// call to file controller
                                        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'files.php');
                                        $_cFiles = new EmundusControllerFiles;

                                        $fabrikValues = $_cFiles->getValueByFabrikElts($fabrikElts, [$fnum]);

                                        foreach ($setupTags as $tag) {
                                            $val = "";
                                            $lowerTag = strtolower($tag);

                                            if (array_key_exists($lowerTag, $const)) {
                                                $cell->setValue($const[$lowerTag]);
                                            } elseif (in_array($lowerTag, $special)) {

                                                // Each tag has it's own logic requiring special work.
                                                switch ($lowerTag) {

                                                    // dd-mm-YYYY (YY)
                                                    case 'user_dob_age':
                                                        $birthday = $_mFile->getBirthdate($fnum, 'd/m/Y', true);
                                                        $cell->setValue($birthday->date . ' (' . $birthday->age . ')');
                                                        break;

                                                    default:
                                                        $cell->setValue('');
                                                        break;
                                                }

                                            } elseif (!empty(@$fnumInfo[$fnum][$lowerTag])) {
                                                $cell->setValue(@$fnumInfo[$fnum][$lowerTag]);
                                            } else {
                                                $tags = $_mEmail->setTagsWord(@$fnumInfo[$fnum]['applicant_id'], ['FNUM' => $fnum], $fnum, '');
                                                $i = 0;
                                                foreach ($tags['patterns'] as $value) {
                                                    if ($value == $tag) {
                                                        $val = $tags['replacements'][$i];
                                                        break;
                                                    }
                                                    $i++;
                                                }
                                                $cell->setValue(htmlspecialchars($val));
                                            }
                                        }
                                        foreach ($idFabrik as $id) {

                                            if (isset($fabrikValues[$id][$fnum])) {
                                                $value = str_replace('\n', ', ', $fabrikValues[$id][$fnum]['val']);
                                                $cell->setValue(htmlspecialchars($value));
                                            } else {
                                                $cell->setValue('');
                                            }
                                        }
                                    }
                                }

                            }
                            $rand = rand(0, 1000000);

                            /// check if the filename is anonymized -- logically, we should avoid to generate many files which have the same contents, but different name --> bad performance
                            $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);
                            if (!$anonymize_data) {
                                //$filename = $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']) . '_' . $fnum . $attachInfo['lbl'] . '_' . ".xlsx";
                                $eMConfig = JComponentHelper::getParams('com_emundus');
                                $generated_doc_name = $eMConfig->get('generated_doc_name', "");
                                if (!empty($generated_doc_name)) {
                                    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
                                    $m_checklist = new EmundusModelChecklist;
                                    $filename = $m_checklist->formatFileName($generated_doc_name, $fnum, $post);
                                } else {
                                    $filename = $this->sanitize_filename($fnumInfo[$fnum]['applicant_name']);
                                }
                                $filename = $filename.$attachInfo['lbl']."-".md5($rand.time()).".xlsx";
                            } else {
                                $filename = $this->sanitize_filename($fnum) . $attachInfo['lbl'] . '_' . ".xlsx";
                            }

                            $original_path = EMUNDUS_PATH_ABS . $fnumInfo[$fnum]['applicant_id'];
                            $original_name = $original_path . DS . $filename;

                            $path = EMUNDUS_PATH_ABS . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'];            /// tmp path (remove '_tmp')
                            $path_name = $path . DS . $filename;

                            $original_url = JURI::base() . EMUNDUS_PATH_REL . $fnumInfo[$fnum]['applicant_id'] . DS;
                            $url = JURI::base() . EMUNDUS_PATH_REL . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'] . '_tmp' . DS;

                            if (!file_exists($path)) { mkdir($path, 0755, true); }

                            if (!file_exists($original_path)) { mkdir($original_path, 0755, true); }

                            /// check if file exists or not
                            if (file_exists($original_name) or file_exists($path_name)) {
                                unlink($original_name);
                                unlink($path_name);

                                $query = 'DELETE FROM #__emundus_uploads 
                                                    WHERE #__emundus_uploads.fnum LIKE ' . $this->_db->quote($fnum) .
                                                        ' AND #__emundus_uploads.filename = ' . $this->_db->quote($filename) .
                                                            ' AND DATE(#__emundus_uploads.timedate) = current_date()';

                                $this->_db->setQuery($query);
                                $this->_db->execute();

                                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                                $writer->setIncludeCharts(true);
                                $writer->save($original_name);

                                copy($original_name, $path_name);

                                $upId = $_mFile->addAttachment($fnum, $filename, $user->id, $fnumInfo[$fnum]['campaign_id'], $letter->attachment_id, '', $canSee);
                                $res->files[] = array('filename' => $filename, 'upload' => $upId, 'url' => $original_url, 'type' => $attachInfo['id']);

                            } else {
                                $upId = $_mFile->addAttachment($fnum, $filename, $user->id, $fnumInfo[$fnum]['campaign_id'], $letter->attachment_id, '', $canSee);

                                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                                $writer->setIncludeCharts(true);
                                $writer->save($original_name);

                                copy($original_name, $path_name);

                                $res->files[] = array('filename' => $filename, 'upload' => $upId, 'url' => $original_url, 'type' => $attachInfo['id']);
                            }
                            break;
                        }
                }
            }

            $_ids = array();
            $getLastUploadIdQuery = "SELECT #__emundus_uploads.* FROM #__emundus_uploads WHERE #__emundus_uploads.fnum LIKE " . $this->_db->quote($fnum) . " GROUP BY #__emundus_uploads.attachment_id ORDER BY attachment_id DESC";

            $this->_db->setQuery($getLastUploadIdQuery);
            $availableUploads = $this->_db->loadObjectList();

            foreach($availableUploads as $_upload) {
                $_ids[] = $_upload->id;
            }

            /// remove all duplicate attachments (just keep the last) -- unlink
            $getDuplicateAttachmentQuery = 'SELECT #__emundus_uploads.* FROM #__emundus_uploads WHERE #__emundus_uploads.id NOT IN ( ' . implode(',', $_ids) . ' ) AND #__emundus_uploads.fnum LIKE ' . $this->_db->quote($fnum);

            $this->_db->setQuery($getDuplicateAttachmentQuery);
            $duplicateAttachments = $this->_db->loadObjectList();

            /// remove unnecessary records for same attachment id in database
            if($replace_document == 1) {
                $deleteDuplicateAttachmentsQuery = 'DELETE FROM #__emundus_uploads WHERE #__emundus_uploads.id NOT IN ( ' . implode(',', $_ids) . ' ) AND #__emundus_uploads.fnum LIKE ' . $this->_db->quote($fnum);
                $this->_db->setQuery($deleteDuplicateAttachmentsQuery);
                $this->_db->execute();
            }

//            foreach($duplicateAttachments as $attachment) {
//                unlink(EMUNDUS_PATH_ABS . $attachment->user_id . DS . $attachment->filename);
//                unlink(EMUNDUS_PATH_ABS . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] .'_' . $fnumInfo[$fnum]['applicant_id'] . DS . $attachment->filename);
//            }
        }

        /// lastly, we get all records in jos_emundus_uploads
        if($replace_document == 1) {
            unset($res->files);

            $availableFilesName = [];

            $getAllUploadsQuery = 'SELECT #__emundus_uploads.* FROM #__emundus_uploads WHERE #__emundus_uploads.fnum IN (' . implode(',', $fnum_Array) . ') AND DATE(#__emundus_uploads.timedate) = current_date() AND #__emundus_uploads.attachment_id IN (' . implode(',', $templates) . ' )';
            $this->_db->setQuery($getAllUploadsQuery);
            $_upAttachments = $this->_db->loadObjectList();

            foreach ($_upAttachments as $_upload) {
                $folder_id = current($_mFile->getFnumsInfos(array($_upload->fnum)))['applicant_id'];
                $res->files[] = array('filename' => $_upload->filename, 'upload' => $_upload->id, 'url' => JURI::base() . EMUNDUS_PATH_REL . $folder_id . DS);
                $availableFilesName[] = $_upload->filename;
            }

            foreach ($fnum_Array as $fnum) {
                $fnumInfo = $_mFile->getFnumsTagsInfos([$fnum]);
                $files = glob(EMUNDUS_PATH_ABS . 'tmp' . DS . $fnumInfo[$fnum]['applicant_name'] . '_' . $fnumInfo[$fnum]['applicant_id'] . DS . '*');

                foreach ($files as $_f) {
                    $fname = end(explode('/', $_f));
                    if (!in_array($fname, $availableFilesName)) {
                        unlink($_f);
                    }
                }
            }
        } else {
            $_idList = array();
            /* get max upload id for each fnum and for each attachment id and in currrent_date() */
            foreach($templates as $tmpl) {
                $getLastUploadIdQuery = 'SELECT MAX(#__emundus_uploads.id) as uid
                                            FROM #__emundus_uploads 
                                                WHERE #__emundus_uploads.fnum IN (' . implode(',', $fnum_Array) . ') 
                                                    AND DATE(#__emundus_uploads.timedate) = current_date() 
                                                        AND #__emundus_uploads.attachment_id IN (' . $tmpl . ' ) 
                                                            GROUP BY fnum';
                $this->_db->setQuery($getLastUploadIdQuery);
                $_idList[$tmpl] = $this->_db->loadAssocList();
            }

            $raw = array_reduce($_idList, 'array_merge', array());
            $out = array();

            foreach($raw as $r) { $out[] = $r['uid']; }

            /* from $out, get the filename of each */
            unset($res->files);
            $getFiles = $_mFile->getAttachmentsById($out);

            foreach($getFiles as $f) {
                $folder_id = current($_mFile->getFnumsInfos(array($f['fnum'])))['applicant_id'];
                $res->files[] = array('filename' => $f['filename'], 'upload' => $f['id'], 'url' => JURI::base() . EMUNDUS_PATH_REL . $folder_id . DS, 'type' => $f['attachment_id']);
            }
        }

        // group letters by candidat

        $fnumsInfos = $_mFile->getFnumsInfos($fnum_Array);

        $res->zip_data_by_candidat = [];
        $res->zip_all_data_by_candidat = [];
        $res->zip_all_data_by_document = [];

        $applicant_id = [];

        foreach ($fnumsInfos as $key => $value) { $applicant_id[] = $value['applicant_id']; }

        $applicant_id = array_unique(array_filter($applicant_id));
        $res->affected_users = count($applicant_id);

        if($showMode == 0) {
            unset($res->zip_all_data_by_document);

            foreach ($applicant_id as $key => $uid) {
                $user_info = $_mUser->getUsersById($uid);           /// change "getUserById" to "getUsersById"

                if($mergeMode == 0) {
                    $_zipName = $user_info[0]->name . '_' . $user_info[0]->id . '_' . date("Y-m-d") . '_' . '.zip';            // make zip file name

                    if(file_exists($tmp_path . $_zipName)) { unlink($tmp_path . $_zipName); }                   // if zip name exist in /tmp/ :: remove

                    $_tmpFolder = EMUNDUS_PATH_ABS . 'tmp' . DS . $user_info[0]->name . '_' . $user_info[0]->id;

                    $_isEmptyTmpFolder = glob($_tmpFolder . DS . '*');

                    $mergeZipAllName = date("Y-m-d") . '-total-by-candidats';                                                    // make zip --all file name
                    $mergeZipAllPath = $tmp_path . $mergeZipAllName;                                                                   // make the zip --all path

                    if(!file_exists($mergeZipAllPath)) { mkdir($mergeZipAllPath, 0755, true); }

                    if(sizeof($_isEmptyTmpFolder) > 0) {
                        if($replace_document == 0) {
                            $keepFiles = [];
                            foreach($res->files as $_f) {
                                $keepFiles[] = $_tmpFolder . DS . $_f['filename'];
                                if(!file_exists($_tmpFolder . DS . $_f['filename'])) {
                                    $index = array_search($_tmpFolder . DS . $_f['filename'], $keepFiles);
                                    unset($keepFiles[$index]);

                                    unlink($_tmpFolder . DS . $_f['filename']);     // remove fake files
                                }
                            }

                            $diffFiles = array_diff($_isEmptyTmpFolder,$keepFiles);
                            foreach($diffFiles as $df) { unlink($df); }
                        }

                        $this->ZipLetter($_tmpFolder, $tmp_path . $_zipName, 'true');
                        $this->copy_directory($_tmpFolder . DS, $mergeZipAllPath . DS . str_replace('_tmp' , '', end(explode('/', $_tmpFolder))));
                    }

                    /// lastly, zip this folder
                    $this->ZipLetter($mergeZipAllPath,$mergeZipAllPath . '.zip', true);                       // zip this new file

                    if(sizeof($_isEmptyTmpFolder) > 0) { $res->zip_data_by_candidat[] = array('applicant_id' => $uid, 'applicant_name' => $user_info[0]->name, 'zip_url' => DS . 'tmp/' . $_zipName); }
                }

                // merge pdf by candidats
                if($mergeMode == 1) {
                    /// if merge mode --> 1, mkdir new directory in / tmp / with suffix "--merge"
                    $mergeDirName = $user_info[0]->name . '_' . $user_info[0]->id . '__merge';                 // for example: 95--merge
                    $mergeDirPath = $tmp_path . $mergeDirName;       // for example: /tmp/95--merge

                    if (!file_exists($mergeDirPath)) { mkdir($mergeDirPath, 0755, true); }

                    /// begin -- merge zip all
                    $mergeZipAllName = date("Y-m-d") . '__merge-total-by-candidats';
                    $mergeZipAllPath = $tmp_path . $mergeZipAllName;

                    if(!file_exists($mergeZipAllPath)) { mkdir($mergeZipAllPath, 0755, true); }
                    /// end -- merge zip all

                    $pdf_files = array();
                    $_tmpFolder = EMUNDUS_PATH_ABS . 'tmp' . DS . $user_info[0]->name . '_' . $user_info[0]->id;

                    $fileList = glob($_tmpFolder . DS . '*');

                    if(sizeof($fileList) > 0 and $replace_document == 0) {
                        $keepFiles = [];
                        foreach($res->files as $_f) {
                            $keepFiles[] = $_tmpFolder . DS . $_f['filename'];
                            if(!file_exists($_tmpFolder . DS . $_f['filename'])) {
                                $index = array_search($_tmpFolder . DS . $_f['filename'], $keepFiles);
                                unset($keepFiles[$index]);
                                unlink($_tmpFolder . DS . $_f['filename']);     // remove fake files
                            }
                        }
                        $diffFiles = array_diff($fileList,$keepFiles);
                        foreach($diffFiles as $df) { unlink($df); }
                        // re-update $fileList
                        $fileList = glob($_tmpFolder . DS . '*');
                    }

                    foreach ($fileList as $filename) {
                        // if extension is pdf --> push into the array $pdf_files
                        $_name = end(explode('/', $filename));
                        $_file_extension = pathinfo($filename)['extension'];

                        if ($_file_extension == "pdf") { $pdf_files[] = $filename; }
                        else {
                            // if not, just copy it to --merge directory
                            copy($filename, $mergeDirPath . DS . $_name);
                        }
                    }

                    if (count($pdf_files) >= 1) {
                        /// check if the merged file exists
                        $mergePdfName = $mergeDirPath . DS . $user_info[0]->name . '__merge.pdf';
                        if (file_exists($mergePdfName, 'F')) { unlink($mergePdfName); }
                        $pdf = new ConcatPdf();
                        $pdf->setFiles($pdf_files);
                        $pdf->concat();
                        $pdf->Output($mergePdfName, 'F');
                    }

                    // last one :: make a zip folder of merge
                    $_mergeZipName = $mergeDirName . '_' . date("Y-m-d") . '.zip';                        // for example: 95--merge.zip

                    $_mergeZipPath = $tmp_path . $_mergeZipName;                                                // for example: / tmp / 95--merge.zip
                    $this->ZipLetter($mergeDirPath, $_mergeZipPath, true);

                    $mergeFiles = glob($mergeDirPath . DS . '*');

                    if(sizeof($mergeFiles) > 0) { $this->copy_directory($mergeDirPath, $mergeZipAllPath . DS . str_replace('_tmp', '', end(explode('/', $mergeDirPath)))); }

                    /// lastly, zip this folder
                    $this->ZipLetter($mergeZipAllPath,$mergeZipAllPath . '.zip', true);

                    /// remove all unzipped files -- no merge files
                    $delete_untotal_files = glob($mergeDirPath . DS . '*');
                    foreach($delete_untotal_files as $_file) { if(is_file($_file)) { unlink($_file); } }
                    rmdir($mergeDirPath);

                    if(sizeof($mergeFiles) > 0) {
                        $res->zip_data_by_candidat[] = array('applicant_id' => $uid, 'applicant_name' => $user_info[0]->name, 'merge_zip_url' => DS . 'tmp/' . $_mergeZipName);
                    }
                }
            }

            /// remove unzipped folder
            $_deleteFolders = glob($mergeZipAllPath . DS . '*');
            foreach($_deleteFolders as $_deleteFolder) { $this->deleteAll($_deleteFolder); }
            rmdir($mergeZipAllPath);
            $res->zip_all_data_by_candidat = DS . 'tmp/' . $mergeZipAllName . '.zip';
        }

        // group letters by document type --> using table "jos_emundus_upload" --> user_id, fnum, campaign_id, attachment_id
        elseif ($showMode == 1) {

            /* get real files */
            $raw = [];
            foreach($res->files as $rf) { $raw[] = $rf['filename']; }

            unset($res->zip_data_by_candidat);
            unset($res->zip_all_data_by_candidat);

            $res->letter_dir = [];

            $zip_dir = [];

            // mkdir --total files
            $zip_All_Name = date("Y-m-d") . '-total-by-documents';
            $zip_All_Path = $tmp_path . $zip_All_Name;

            $zip_All_Merge_Name = date("Y-m-d") . '__merge-total-by-documents';
            $zip_All_Merge_Path = $tmp_path . $zip_All_Merge_Name;

            if($mergeMode == 0) { if(!file_exists($zip_All_Path)) { mkdir($zip_All_Path, 0755, true); }}
            else { if(!file_exists($zip_All_Merge_Path)) { mkdir($zip_All_Merge_Path, 0755, true); } }

            foreach ($templates as $index => $template) {
                $attachInfos = $_mFile->getAttachmentInfos($template);

                $dir_Name = $attachInfos['value'];                                                // unmere file name
                $dir_Name_Path = $tmp_path . $dir_Name;                                         // unmerge file path

                $dir_Merge_Name = $dir_Name . '__merge';                                        // merge file name
                $dir_Merge_Path = $tmp_path . $dir_Merge_Name;                                  // merge file path

                if(!file_exists($dir_Name_Path)) {
                    mkdir($dir_Name_Path, 0755, true);
                    if($mergeMode == 1) { if (!file_exists($dir_Merge_Path)) { mkdir($dir_Merge_Path, 0755, true); } }
                }

                $uploaded_Files = $_mEval->getFilesByAttachmentFnums($template, $fnum_Array);                    // get uploaded file by fnums

                foreach ($uploaded_Files as $key => $file) {
                    $_uRaw = $_mFile->getFnumInfos($file->fnum);                                                // get applicant id (not evaluator id)

                    $_uId = $_uRaw['applicant_id'];
                    $_uName = $_uRaw['name'];

                    //$source = EMUNDUS_PATH_ABS . $file->user_id . '--letters' . DS . $file->filename;
                    $source = EMUNDUS_PATH_ABS . 'tmp' . DS . $_uName . '_' . $_uId . DS . $file->filename;

                    if(!in_array($file->filename, $raw)) {
                        unlink($source);
                    } else {
                        copy($source, $dir_Name_Path . DS . $file->filename);                                       /// copy file
                    }

                    /// copy into /tmp/
                    $_zipName = $dir_Name . '_' . date("Y-m-d") . '.zip';                                   // zip file name (e.g: "Convention de financement")
                    $this->ZipLetter($dir_Name_Path, $tmp_path . $_zipName, 'true');
                    $zip_dir = $tmp_path . $_zipName;                                                               // get zip url

                    if($mergeMode == 1) {
                        $pdf_files = array();
                        $fileList = glob($dir_Name_Path . DS . '*');
                        foreach ($fileList as $filename) {
                            // if extension is pdf --> push into the array $pdf_files
                            $_name = explode($dir_Name_Path . DS, $filename)[1];
                            $_file_extension = pathinfo($filename)['extension'];
                            if ($_file_extension == "pdf") {
                                $pdf_files[] = $filename;
                            } else {
                                // if not, just copy it to --merge directory
                                copy($filename, $dir_Merge_Path . DS . $_name);
                            }
                        }

                        /// from $pdf_files --> concat them
                        if (count($pdf_files) >= 1) {
                            $mergeFileName = $dir_Merge_Path . DS . $attachInfos['value'] . '.pdf';
                            if (file_exists($mergeFileName)) {
                                // remove old file
                                unlink($mergeFileName);
                            }
                            $pdf = new ConcatPdf();
                            $pdf->setFiles($pdf_files);
                            $pdf->concat();
                            $pdf->Output($mergeFileName, 'F');          /// export the merged pdf
                        }

                        /// last one --> zip this --merge into / tmp /
                        $_mergeZipName = $dir_Merge_Name . '_' . date("Y-m-d") . '.zip';

                        $this->copy_directory($dir_Merge_Path, $zip_All_Merge_Path . DS . str_replace('__merge', '', str_replace('_tmp', '', end(explode('/', $dir_Merge_Path)))));

                        $this->ZipLetter($dir_Merge_Path, $tmp_path . $_mergeZipName, true);
                        $this->ZipLetter($zip_All_Merge_Path, $zip_All_Merge_Path . '_' . '.zip', true);
                    } else {
                        $this->copy_directory($dir_Name_Path, $zip_All_Path . DS . str_replace('_tmp', '', end(explode('/', $dir_Name_Path))));

                        $this->ZipLetter($zip_All_Path, $zip_All_Path . '_' . '.zip', true);
                    }
                }

                if($mergeMode == 1) {
                    $res->letter_dir[] = array('letter_name' => $attachInfos['value'], 'zip_merge_dir' => DS . 'tmp/' . $_mergeZipName);

                    // remove --merge path
                    $delete_merge_files = glob($dir_Merge_Path . DS . '*');
                    foreach($delete_merge_files as $_file) { if(is_file($_file)) { unlink($_file); } }
                    rmdir($dir_Merge_Path);
                    unlink($zip_dir);

                } else {
                    $res->letter_dir[] = array('letter_name' => $attachInfos['value'], 'zip_dir' => DS. 'tmp/' . $_zipName);
                }

                $delete_files = glob($dir_Name_Path . DS . '*');

                foreach($delete_files as $_file) { if(is_file($_file)) { unlink($_file); } }

                rmdir($dir_Name_Path);
            }

            if($mergeMode == 1) {
                $_deleteFolders = glob($zip_All_Merge_Path . DS . '*');
                foreach($_deleteFolders as $_deleteFolder) { $this->deleteAll($_deleteFolder); }
                $this->deleteAll($zip_All_Merge_Path);
                $res->zip_all_data_by_document = DS . 'tmp/' . $zip_All_Merge_Name . '_.zip';

            } else {
                $_deleteFolders = glob($zip_All_Path . DS . '*');
                foreach($_deleteFolders as $_deleteFolder) { $this->deleteAll($_deleteFolder); }
                $this->deleteAll($zip_All_Path);
                $res->zip_all_data_by_document = DS . 'tmp/' . $zip_All_Name . '_.zip';
            }
        }

        // remove temporary folders in images/emundus/files/tmp
        foreach($fnum_Array as $key => $fnum) {
            $fnum_info = $_mFile->getFnumInfos($fnum);

            $_tmpFolders = glob(EMUNDUS_PATH_ABS . 'tmp' . DS . '*');
            foreach($_tmpFolders as $_tmpFolder) { $this->deleteAll($_tmpFolder); }
        }

        // build the recap table (just get generated documents of current date)
        if($replace_document == 1) {
            $query = 'SELECT #__emundus_uploads.attachment_id, COUNT(#__emundus_uploads.attachment_id) AS _count 
                        FROM #__emundus_uploads
                            WHERE #__emundus_uploads.fnum in (' . implode(',', $available_fnums) . ') 
                                AND #__emundus_uploads.attachment_id IN (' . implode(',', $templates) . ')
                                    AND DATE(#__emundus_uploads.timedate) = current_date()
                                        GROUP BY #__emundus_uploads.attachment_id';

            $this->_db->setQuery($query);
            $document_count = $this->_db->loadAssocList();
            $res->recapitulatif_count = [];

            foreach ($document_count as $key => $document) {
                $query = "SELECT #__emundus_setup_attachments.value FROM #__emundus_setup_attachments WHERE #__emundus_setup_attachments.id = " . $document['attachment_id'];
                $this->_db->setQuery($query);
                $res->recapitulatif_count[] = array('document' => $this->_db->loadResult(), 'count' => $document['_count']);
            }
        } else {
            $res->recapitulatif_count = [];
            $raw = [];

            foreach($res->files as $file) { $raw[$file['type']] += 1; }

           foreach($raw as $k => $v) {
               $query = "SELECT #__emundus_setup_attachments.value FROM #__emundus_setup_attachments WHERE #__emundus_setup_attachments.id = " . $k;
               $this->_db->setQuery($query);
               $res->recapitulatif_count[] = array('document' => $this->_db->loadResult(), 'count' => $v);
           }
        }

        return $res;
    }

    public function ZipLetter($source, $destination, $include_dir = false) {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        if (file_exists($destination)) {
            unlink ($destination);
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }
        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            if ($include_dir) {
                $arr = explode("/",$source);
                $maindir = $arr[count($arr)- 1];

                $source = "";
                for ($i=0; $i < count($arr) - 1; $i++) {
                    $source .= '/' . $arr[$i];
                }

                $source = substr($source, 1);

                $zip->addEmptyDir($maindir);

            }

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                }
                else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        }
        else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }

    private function sanitize_filename($name) {
        return strtolower(preg_replace(['([\40])', '([^a-zA-Z0-9-])','(-{2,})'], ['_','','_'], preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/', '$1', htmlentities($name, ENT_NOQUOTES, 'UTF-8'))));
    }

    private function copy_directory($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
        return 0;
    }

    private function deleteAll($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file)) { deleteAll($file); }
            else { unlink($file); }
        }
        rmdir($dir);
        return 0;
    }
}
