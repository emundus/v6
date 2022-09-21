<?php
/**
 * @package         Joomla
 * @subpackage      eMundus
 * @link            http://www.emundus.fr
 * @copyright       Copyright (C) 2018 eMundus. All rights reserved.
 * @license         GNU/GPL
 * @author          Benjamin Rivalland
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
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');

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
    public function __construct() {
        parent::__construct();

        $db = JFactory::getDbo();
        $mainframe = JFactory::getApplication();

        JPluginHelper::importPlugin('emundus');

        // Get current menu parameters
        $current_user = JFactory::getUser();
        $menu = @JFactory::getApplication()->getMenu();
        $current_menu = $menu->getActive();

        $Itemid = @JFactory::getApplication()->input->getInt('Itemid', $current_menu->id);
        $menu_params = $menu->getParams($Itemid);

        $h_files = new EmundusHelperFiles;
        $m_users = new EmundusModelUsers;

        $groupAssoc = array_filter($m_users->getUserGroupsProgrammeAssoc($current_user->id));
        $progAssoc = array_filter($this->getAssociatedProgrammes($current_user->id));
        $this->code = array_merge($groupAssoc, $progAssoc);

        $this->locales = substr(JFactory::getLanguage()->getTag(), 0 , 2);

        if (empty($current_menu)) {
            return false;
        }

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
        if ($session->has('adv_cols')) {
            $adv = $session->get('adv_cols');
            if (!empty($adv) && !is_null($adv)) {
                $this->elements_id .= ','.implode(',', $adv);
            }

        }
        $this->elements_values = explode(',', $menu_params->get('em_elements_values'));

        $this->_elements_default = array();
        $this->_elements = $h_files->getElementsName($this->elements_id);

        if (!empty($this->_elements)) {
            foreach ($this->_elements as $def_elmt) {
                $group_params = json_decode($def_elmt->group_attribs);

                if ($def_elmt->element_plugin == 'date') {
                    if (@$group_params->repeat_group_button == 1) {
                        $this->_elements_default[] = '(
                                                        SELECT  GROUP_CONCAT(DATE_FORMAT('.$def_elmt->table_join.'.'.$def_elmt->element_name.', "%d/%m/%Y %H:%i:%m") SEPARATOR ", ")
                                                        FROM '.$def_elmt->table_join.'
                                                        WHERE '.$def_elmt->table_join.'.parent_id = '.$def_elmt->tab_name.'.id
                                                      ) AS `'.$def_elmt->table_join.'___' . $def_elmt->element_name.'`';
                    } else {
                        $this->_elements_default[] = $def_elmt->tab_name.'.'.$def_elmt->element_name.' AS `'.$def_elmt->tab_name.'___'.$def_elmt->element_name.'`';
                    }
                }
                elseif ($def_elmt->element_plugin == 'databasejoin') {
                    $attribs = json_decode($def_elmt->element_attribs);
                    $join_val_column_concat = str_replace('{thistable}', $attribs->join_db_name, $attribs->join_val_column_concat);
                    $join_val_column_concat = str_replace('{shortlang}', substr(JFactory::getLanguage()->getTag(), 0 , 2), $join_val_column_concat);
                    $column = (!empty($join_val_column_concat) && $join_val_column_concat!='')?'CONCAT('.$join_val_column_concat.')':$attribs->join_val_column;

                    // Check if the db table has a published column. So we don't get the unpublished value
                    $db->setQuery("SHOW COLUMNS FROM $attribs->join_db_name LIKE 'published'");
                    $publish_query = ($db->loadResult()) ? " AND $attribs->join_db_name.published = 1 " : '';

                    if (@$group_params->repeat_group_button == 1) {
                        $query = '(
                                    select GROUP_CONCAT('.$column.' SEPARATOR ", ")
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
                                select DISTINCT '.$column.'
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
                }
                elseif ($def_elmt->element_plugin == 'dropdown' || $def_elmt->element_plugin == 'radiobutton' || $def_elmt->element_plugin == 'checkbox') {

                    if (@$group_params->repeat_group_button == 1) {
                        $element_attribs = json_decode($def_elmt->element_attribs);
                        $select = $def_elmt->tab_name . '.' . $def_elmt->element_name;
                        foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                            $select = 'REPLACE(' . $select . ', "' . $value . '", "' .
                                JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
                        }
                        $select = str_replace($def_elmt->tab_name . '.' . $def_elmt->element_name,'GROUP_CONCAT('.$def_elmt->table_join.'.' . $def_elmt->element_name.' SEPARATOR ", ")',$select);

                        $this->_elements_default[] = '(
                                    SELECT ' . $select . '
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
                } elseif ($def_elmt->element_plugin == 'yesno') {
                    if (@$group_params->repeat_group_button == 1) {
                        $this->_elements_default[] = '(
                                                        SELECT REPLACE(REPLACE(GROUP_CONCAT('.$def_elmt->table_join.'.' . $def_elmt->element_name.'  SEPARATOR ", "), "0", "' . JText::_('JNO') . '"), "1", "' . JText::_('JYES') . '")
                                                        FROM '.$def_elmt->table_join.'
                                                        WHERE '.$def_elmt->table_join.'.parent_id = '.$def_elmt->tab_name.'.id
                                                      ) AS `'.$def_elmt->table_join.'___' . $def_elmt->element_name.'`';
                    } else {
                        $this->_elements_default[] = 'REPLACE(REPLACE('.$def_elmt->tab_name.'.'.$def_elmt->element_name.', "0", "' . JText::_('JNO') . '"), "1", "' . JText::_('JYES') . '")  AS '.$def_elmt->tab_name.'___'.$def_elmt->element_name;
                    }
                }else {
                    if (@$group_params->repeat_group_button == 1) {
                        $this->_elements_default[] = '(
                                                        SELECT  GROUP_CONCAT('.$def_elmt->table_join.'.' . $def_elmt->element_name.'  SEPARATOR ", ")
                                                        FROM '.$def_elmt->table_join.'
                                                        WHERE '.$def_elmt->table_join.'.parent_id = '.$def_elmt->tab_name.'.id
                                                      ) AS `'.$def_elmt->table_join.'___' . $def_elmt->element_name.'`';
                    } else {
                        $this->_elements_default[] = $def_elmt->tab_name.'.'.$def_elmt->element_name.' AS '.$def_elmt->tab_name.'___'.$def_elmt->element_name;
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

        if (count($this->col) > 0) {

            $elements_names = '"'.implode('", "', $this->col).'"';

            $h_list = new EmundusHelperList;

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

    /**
     * @return array
     */
    public function getElementsVar() {
        return $this->_elements;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function _buildContentOrderBy() {
        $menu = @JFactory::getApplication()->getMenu();
        $current_menu = $menu->getActive();
        $menu_params = $menu->getParams(@$current_menu->id);
        $em_other_columns = explode(',', $menu_params->get('em_other_columns'));

        $session = JFactory::getSession();
        $filter_order = $session->get('filter_order');
        $filter_order_Dir = $session->get('filter_order_Dir');

        $can_be_ordering = array();
        if (!empty($this->_elements)) {
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
        $can_be_ordering[] = 'jos_emundus_campaign_candidature.form_progress';
        $can_be_ordering[] = 'jos_emundus_campaign_candidature.attachment_progress';
        $can_be_ordering[] = 'fnum';
        $can_be_ordering[] = 'status';
        $can_be_ordering[] = 'name';
        $can_be_ordering[] = 'form_progress';
        $can_be_ordering[] = 'attachment_progress';
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
     * @param array $multi_array
     * @param $sort_key
     * @param int $sort
     * @return array|int
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

    /**
     * @return mixed
     */
    public function getCampaign() {
        $h_files = new EmundusHelperFiles;
        return $h_files->getCampaign();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getCurrentCampaign() {
        $h_files = new EmundusHelperFiles;
        return $h_files->getCurrentCampaign();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getCurrentCampaignsID() {
        $h_files = new EmundusHelperFiles;
        return $h_files->getCurrentCampaignsID();
    }

    /**
     * @param $user
     * @return mixed
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
     * @param $joined
     * @return bool
     */
    public function isJoined($tab, $joined) {

        foreach ($joined as $j) {
            if ($tab == $j) {
                return true;
            }
        }

        return false;
    }


    /**
     * @description : Generate values for array of data for all applicants
     * @param    array $search filters elements
     * @param    array $eval_list reference of result list
     * @param    array $head_val header name
     * @param    object $applicant array of applicants indexed by database column
     **/
    public function setEvalList($search, &$eval_list, $head_val, $applicant) {
        $h_list = new EmundusHelperList;
        if (!empty($search)) {
            foreach ($search as $c) {
                if (!empty($c)) {
                    $name = explode('.', $c);
                    if (!in_array($name[0] . '___' . $name[1], $head_val)) {
                        if ($this->details->{$name[0] . '___' . $name[1]}['group_by'] && array_key_exists($name[0] . '___' . $name[1], $this->subquery) && array_key_exists($applicant->user_id, $this->subquery[$name[0] . '___' . $name[1]])) {
                            $eval_list[$name[0] . '___' . $name[1]] = $h_list->createHtmlList(explode(",",
                                $this->subquery[$name[0] . '___' . $name[1]][$applicant->user_id]));
                        } elseif ($name[0] == 'jos_emundus_training') {
                            $eval_list[$name[1]] = $applicant->{$name[1]};
                        } elseif (!$this->details->{$name[0] . '___' . $name[1]}['group_by']) {
                            $eval_list[$name[0] . '___' . $name[1]] =
                                $h_list->getBoxValue($this->details->{$name[0] . '___' . $name[1]},
                                    $applicant->{$name[0] . '___' . $name[1]}, $name[1]);
                        } else {
                            $eval_list[$name[0].'___'.$name[1]] = $applicant->{$name[0].'___'.$name[1]};
                        }
                    }
                }
            }
        }
    }


    /**
     * @param array $tableAlias
     * @return array
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
                            $index = 0;
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
                                        $sql = 'SELECT join_from_table, table_key, table_join_key FROM #__fabrik_joins WHERE table_join like '.$db->Quote($tab[0]);
                                        $db->setQuery($sql);
                                        $join_from_table = $db->loadObject();

                                        if (!empty($join_from_table->join_from_table)) {
                                            $table = $join_from_table->join_from_table;
                                            $table_join = $tab[0];

                                            // Do not do LIKE %% search on elements that come from a <select>, we should get the exact value.
                                            if (isset($adv_select) && $adv_select) {
                                                $query['q'] .= $table_join.'.'.$tab[1].' like "' . $v . '"';
                                            } else {
                                                $query['q'] .= $table_join.'.'.$tab[1].' like "%' . $v . '%"';
                                            }

                                            /*if (!isset($query[$table])) {
                                                $query[$table] = true;
                                                if (!array_key_exists($table, $tableAlias) && !in_array($table, $tableAlias)) {
                                                    $query['join'] .= ' left join '.$table.' on ' .$table.'.fnum like jos_emundus_campaign_candidature.fnum ';
                                                }
                                            }*/

                                            if (!isset($query[$table_join])) {
                                                $query[$table_join] = true;
                                                try {

                                                    if (!array_key_exists($table_join, $tableAlias) && !in_array($table_join, $tableAlias)) {
                                                        $query['join'] .= ' left join '. $table_join .' on ' . $table . '.id=' . $table_join . '.parent_id';
                                                    }
                                                } catch(Exception $e) {
                                                    if (!array_key_exists($table_join, $tableAlias) && !in_array($table_join, $tableAlias)) {
                                                        $query['join'] .= ' left join '.$tab[0].' on ' .$tab[0].'.fnum like jos_emundus_campaign_candidature.fnum ';
                                                    }
                                                }
                                            }

                                            $query['join'] .= ' left join ' . $table . ' as ' . $table . '_' . $index . ' on ' . $table . '_' . $index  . '.' . $join_from_table->table_key .' like ' . $table_join . '.' . $join_from_table->table_join_key;
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
                                                    $query['join'] .= ' left join '.$tab[0].' on ' .$tab[0].'.fnum like jos_emundus_campaign_candidature.fnum ';
                                                }
                                            }
                                        }
                                    }
                                }

                                $index++;
                            }
                        }
                        break;

                    case 'elements_other':
                        if (!empty($value)) {
                            foreach ($value as $k => $v) {
                                if (!empty($v)) {
                                    $tab = explode('.', $k);
                                    if (count($tab) > 1) {
                                        if ($tab[0] == 'jos_emundus_training') {
                                            $query['q'] .= ' AND ';
                                            $query['q'] .= ' search_'.$tab[0].'.id like "%' . $v . '%"';
                                        } else {
                                            $query['q'] .= ' AND ';
                                            $query['q'] .= $tab[0].'.'.$tab[1].' like "%' . $v . '%"';

                                            if (!isset($query[$tab[0]])) {
                                                $query[$tab[0]] = true;
                                                if (!array_key_exists($tab[0], $tableAlias)) {
                                                    $query['join'] .= ' left join '.$tab[0].' on ' .$tab[0].'.fnum like jos_emundus_campaign_candidature.fnum ';
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
                                    $query['join'] .= ' left join #__emundus_final_grade as fg on fg.fnum like jos_emundus_campaign_candidature.fnum ';
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
                                $query['q'] .= ' AND esc.id IN (' . implode(',', $value) . ') ';
                            }
                        }
                        break;

                    case 'groups':
                        if (!empty($value)) {
                            $query['q'] .= ' and  (ge.group_id=' . $db->Quote($value) . ' OR ge.user_id IN (select user_id FROM #__emundus_groups WHERE group_id=' .$db->Quote($value) . ')) ';

                            if (!isset($query['group_eval'])) {
                                $query['group_eval'] = true;
                                if (!array_key_exists('jos_emundus_groups_eval', $tableAlias)) {
                                    $query['join'] .= ' left join #__emundus_groups_eval as ge on ge.applicant_id = jos_emundus_campaign_candidature.applicant_id and ge.campaign_id = jos_emundus_campaign_candidature.campaign_id ';
                                }
                            }
                        }
                        break;

                    case 'group_assoc':
                        if (!empty($value)) {
                            $query['join'] .= ' 
	                            LEFT JOIN #__emundus_group_assoc as ga on ga.fnum = jos_emundus_campaign_candidature.fnum 
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
                                    $query['join'] .= ' left join #__emundus_groups_eval as ge on ge.applicant_id = jos_emundus_campaign_candidature.applicant_id and ge.campaign_id = jos_emundus_campaign_candidature.campaign_id ';
                                }
                            }
                        }
                        break;

                    /*case 'profile':
                        if(!empty($value) && @$value[0]!=0)
                        {
                            $query['q'] .= 'and (spro.id = ' . $value . ' OR fg.result_for = ' . $value . ' OR ue.user_id IN (select user_id from #__emundus_users_profiles where profile_id = ' . $value . ')) ';

                            if(!isset($query['final_g']))
                            {
                                $query['final_g'] = true;
                                if (!array_key_exists('jos_emundus_final_grade', $tableAlias))
                                    $query['join'] .=' left join #__emundus_final_grade as fg on fg.fnum like jos_emundus_campaign_candidature.fnum ';
                            }
                            if(isset($query['em_user']))
                            {
                                $query['em_user'] = true;
                                if (!array_key_exists('jos_emundus_users', $tableAlias))
                                    $query['join'] .= ' left join #__emundus_users as ue on ue.id = jos_emundus_campaign_candidature.applicant_id ';
                            }
                            if (!array_key_exists('jos_emundus_setup_profiles', $tableAlias))
                                $query['join'] .= ' left join #__emundus_setup_profiles as spro on spro.id = ue.profile ';
                        }
                        break;*/

                    case 'missing_doc':
                        if (!empty($value)) {
                            $query['q'] .=' and (' . $value . ' NOT IN (SELECT attachment_id FROM #__emundus_uploads eup WHERE #__emundus_uploads.user_id = u.id)) ';
                            if (!array_key_exists('jos_emundus_uploads', $tableAlias)) {
                                $query['join'] = ' left join #__emundus_uploads on #__emundus_uploads.user_id = jos_emundus_campaign_candidature.applicant_id ';
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
                            $filt_menu_defined = (isset($filt_menu['status'][0]) && $filt_menu['status'][0] != '' && $filt_menu['status'] != "%" );

                            // session filter is empty
                            if ($value[0] == "%" || !isset($value[0]) || $value[0] == '') {
                                if (!$filt_menu_defined) {
                                    $query['q'] .= ' ';
                                } else {
                                    $query['q'] .= ' and jos_emundus_campaign_candidature.status IN (' . implode(',', $filt_menu['status']) . ') ';
                                }

                            } else {

                                // Check if session filter exist in menu filter, if at least one session filter not in menu filter, reset to menu filter
                                $diff = array();
                                if (is_array($value) && $filt_menu_defined) {
                                    $diff = array_diff($value, $filt_menu['status']);
                                }

                                if (count($diff) == 0) {
                                    $query['q'] .= ' and jos_emundus_campaign_candidature.status IN (' . implode(',', $value) . ') ';
                                } else {
                                    $query['q'] .= ' and jos_emundus_campaign_candidature.status IN (' . implode(',', $filt_menu['status']) . ') ';
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
                                    $query['q'] .= ' and jos_emundus_campaign_candidature.fnum NOT IN (SELECT cc.fnum FROM jos_emundus_campaign_candidature AS cc LEFT JOIN jos_emundus_tag_assoc as ta ON ta.fnum = cc.fnum WHERE ta.id_tag IN (' . implode(',', $not_in) . ')) ';
                                }

                                if (!empty($value)) {
                                    $query['q'] .= ' and eta.id_tag IN ('.implode(',', $value).') ';
                                }
                            }
                        }
                        break;

                    case 'published':
                        if ($value == "-1") {
                            $query['q'] .= ' and jos_emundus_campaign_candidature.published=-1 ';
                        } elseif ($value == 0) {
                            $query['q'] .= ' and jos_emundus_campaign_candidature.published=0 ';
                        } else {
                            $query['q'] .= ' and jos_emundus_campaign_candidature.published=1 ';
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        // force menu filter
        if ((is_array($filt_menu['status']) && count($filt_menu['status']) > 0) && isset($filt_menu['status'][0]) && !empty($filt_menu['status'][0]) && $filt_menu['status'][0] != "%") {
            $query['q'] .= ' AND jos_emundus_campaign_candidature.status IN ("' . implode('","', $filt_menu['status']) . '") ';
        }

        if (isset($filt_menu['programme'][0]) && $filt_menu['programme'][0] == "%"){
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
            $sql_fnum = $and.' jos_emundus_campaign_candidature.fnum IN ("'.implode('","', $this->fnum_assoc).'") ';
        }

        if (!empty($sql_code) || !empty($sql_fnum) ) {
            $query['q'] .= ' AND (' . $sql_code . ' ' . $sql_fnum . ') ';
            $query['q'] .= ' AND esc.published > 0';
        } else if (!empty($params['programme']) && ($params['programme'][0] == "%" || empty($params['programme'][0])) || empty(array_intersect($params['programme'], array_filter($this->code)))) {
            $query['q'] .= ' AND 1=2 ';
        }
        return $query;
    }

    /**
     * @param       $str_array
     * @param array $tableAlias
     *
     * @return array
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
                        $queryGroups['all'] .= ' or (u.id = ' . $val[1] . ' or jos_emundus_campaign_candidature.fnum like "'.$val[1].'%") ';
                    } else {
                        if ($first) {
                            $queryGroups['all'] .= ' and (((u.id = ' . $val[1] . ' or jos_emundus_campaign_candidature.fnum like "'.$val[1].'%") ';
                            $first = false;
                        } else {
                            $queryGroups['all'] .= ' and ((u.id = ' . $val[1] . ' or jos_emundus_campaign_candidature.fnum like "'.$val[1].'%") ';
                        }

                    }

                    if (!in_array('jos_users', $tableAlias)) {
                        $q['join'][] .= ' left join #__users as u on u.id = jos_emundus_campaign_candidature.applicant_id ';
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
                            $q['join'][] .= ' left join #__users as u on u.id = jos_emundus_campaign_candidature.applicant_id ';
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
                            $q['join'][] .= ' left join #__users as u on u.id = jos_emundus_campaign_candidature.applicant_id';
                            $q['users'] = true;
                        }

                        if (!in_array('jos_emundus_users', $tableAlias)){
                            $q['join'][] .= ' left join #__emundus_users as eu on eu.user_id = jos_emundus_campaign_candidature.applicant_id ';
                            $q['em_user'] = true;
                        }
                    }
                }
            }


            if ($val[0] == "FNUM" && is_numeric($val[1])) {
                //possibly fnum ou uid
                if (!empty($queryGroups['fnum'])) {
                    $queryGroups['fnum'] .= ' or (jos_emundus_campaign_candidature.fnum like "'.$val[1].'%") ';
                } else {
                    if ($first) {
                        $queryGroups['fnum'] .= ' and (((jos_emundus_campaign_candidature.fnum like "'.$val[1].'%") ';
                        $first = false;
                    } else {
                        $queryGroups['fnum'] .= ' and ((jos_emundus_campaign_candidature.fnum like "'.$val[1].'%") ';
                    }
                }

                if (!in_array('jos_users', $tableAlias)) {
                    $q['join'][] = ' left join #__users as u on u.id = jos_emundus_campaign_candidature.applicant_id ';
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
                    $q['join'][] = ' left join #__users as u on u.id = jos_emundus_campaign_candidature.applicant_id ';
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
                    $q['join'][] = ' left join #__users as u on u.id = jos_emundus_campaign_candidature.applicant_id ';
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
                    $q['join'][] = ' left join #__users as u on u.id = jos_emundus_campaign_candidature.applicant_id ';
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
                    $q['join'][] .= ' left join #__emundus_users as eu on eu.user_id = jos_emundus_campaign_candidature.applicant_id ';
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
                    $q['join'][] .= ' left join #__emundus_users as eu on eu.user_id = jos_emundus_campaign_candidature.applicant_id ';
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
     * @return mixed
     * @throws Exception
     */
    public function getUsers()
    {
        $session = JFactory::getSession();
        $limitStart = $session->get('limitstart');
        $limit = $session->get('limit');

        return $this->getAllUsers($limitStart, $limit);
    }

    /**
     * @param $limitStart   int     request start
     * @param $limit        int     request limit
     *
     * @return mixed
     * @throws Exception
     */
    public function getAllUsers($limitStart = 0, $limit = 20) {
        $app = JFactory::getApplication();
        $current_menu = $app->getMenu()->getActive();
        if (!empty($current_menu)) {
            $menu_params = $current_menu->params;
            $em_other_columns = explode(',', $menu_params->get('em_other_columns'));
        } else {
            $em_other_columns = array();
        }

        $dbo = $this->getDbo();
        $query = 'select jos_emundus_campaign_candidature.fnum, ss.step, ss.value as status, ss.class as status_class, concat(upper(trim(eu.lastname))," ",eu.firstname) AS name, jos_emundus_campaign_candidature.applicant_id, jos_emundus_campaign_candidature.campaign_id ';

        // prevent double left join on query
        $lastTab = [
            '#__emundus_campaign_candidature', 'jos_emundus_campaign_candidature',
            '#__emundus_setup_status', 'jos_emundus_setup_status',
            '#__emundus_setup_programmes', 'jos_emundus_setup_programmes',
            '#__emundus_setup_campaigns', 'jos_emundus_setup_campaigns',
            '#__users', 'jos_users',
            '#__emundus_users', 'jos_emundus_users',
            '#__emundus_tag_assoc', 'jos_emundus_tag_assoc'
        ];

        if (in_array('overall', $em_other_columns)) {
            $lastTab[] = ['#__emundus_evaluations', 'jos_emundus_evaluations'];
        }

        if (!empty($this->_elements)) {
            $leftJoin = '';
            $lastTab = !isset($lastTab) ? array() : $lastTab;

            foreach ($this->_elements as $elt) {
                if (!in_array($elt->tab_name, $lastTab)) {
                    $leftJoin .= 'LEFT JOIN ' . $elt->tab_name .  ' ON '. $elt->tab_name .'.fnum = jos_emundus_campaign_candidature.fnum ';
                    $lastTab[] = $elt->tab_name;
                }
            }
        }

        if (!empty($this->_elements_default)) {
            $query .= ', '.implode(',', $this->_elements_default);
        }

        $query .= ' FROM #__emundus_campaign_candidature
                    LEFT JOIN #__emundus_setup_status as ss on ss.step = jos_emundus_campaign_candidature.status
                    LEFT JOIN #__emundus_setup_campaigns as esc on esc.id = jos_emundus_campaign_candidature.campaign_id
                    LEFT JOIN #__emundus_setup_programmes as sp on sp.code = esc.training
                    LEFT JOIN #__users as u on u.id = jos_emundus_campaign_candidature.applicant_id
                    LEFT JOIN #__emundus_users as eu on eu.user_id = jos_emundus_campaign_candidature.applicant_id
                    LEFT JOIN #__emundus_tag_assoc as eta on eta.fnum=jos_emundus_campaign_candidature.fnum ';

        if (in_array('overall', $em_other_columns)) {
            $query .= ' LEFT JOIN #__emundus_evaluations as ee on ee.fnum = jos_emundus_campaign_candidature.fnum ';
        }

        $q = $this->_buildWhere($lastTab);
        if (!empty($leftJoin)) {
            $query .= $leftJoin;
        }
        $query .= $q['join'];
        $query .= ' WHERE u.block=0 ' . $q['q'];

        $query .= ' GROUP BY jos_emundus_campaign_candidature.fnum';

        $query .=  $this->_buildContentOrderBy();
        $dbo->setQuery($query);
        try {
            $res = $dbo->loadAssocList();
            $this->_applicants = $res;

            if ($limit > 0) {
                $query .= " limit $limitStart, $limit ";
            }

            /*
            if (JFactory::getUser()->id == 63)
                echo '<hr>FILES:'.str_replace('#_', 'jos', $query).'<hr>';
            */


            $dbo->setQuery($query);
            return $dbo->loadAssocList();

        } catch(Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
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
        $h_files = new EmundusHelperFiles;
        return $h_files->getUserGroups($uid);
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
        $lists = [];

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
                implode(',', EmundusModelFiles::getProfileAcces($user->id)) . ')) ';
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
        try {
            $db = $this->getDbo();

            $query = 'select * from #__emundus_setup_groups where 1';
            $db->setQuery($query);

            $evalGroups['groups'] = $db->loadAssocList();

            $query = 'SELECT DISTINCT(eu.user_id) as user_id, CONCAT( eu.lastname, " ", eu.firstname ) as name, esp.label, u.email
                        FROM #__emundus_users AS eu
                        LEFT JOIN #__users AS u ON u.id=eu.user_id
                        LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile
                        LEFT JOIN #__emundus_users_profiles AS eup ON eup.user_id = eu.user_id
                        WHERE u.block=0 AND eu.profile !=1 AND esp.published !=1';
            $db->setQuery($query);

            $evalGroups['users'] = $db->loadAssocList();

            return $evalGroups;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $groups
     * @param $actions
     * @param $fnums
     * @return bool|mixed
     */
    public function shareGroups($groups, $actions, $fnums) {
        try {
            $db = $this->getDbo();
            $insert = [];

            foreach ($fnums as $fnum) {
                foreach($groups as $group) {
                    foreach ($actions as $action) {
                        $ac = (array) $action;
                        $insert[] = $group.','.$ac['id'].','.$ac['c'].','.$ac['r'].','.$ac['u'].','.$ac['d'].','.$db->quote($fnum);
                    }
                }
            }

            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__emundus_group_assoc'))
                ->where($db->quoteName('group_id').' IN ('.implode(',',$groups).') AND '.$db->quoteName('fnum').' IN ("'.implode('","',$fnums).'")');
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->insert($db->quoteName('#__emundus_group_assoc'))
                ->columns($db->quoteName(['group_id', 'action_id', 'c', 'r', 'u', 'd', 'fnum']))
                ->values($insert);
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            $error = JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.'\n -> '.$e->getMessage();
            JLog::add($error, JLog::ERROR, 'com_emundus');

            return false;
        }
        return true;
    }

    /**
     * @param $users
     * @param $actions
     * @param $fnums
     * @return bool|string
     */
    public function shareUsers($users, $actions, $fnums) {
        $error = null;
        try {

            $db = $this->getDbo();
            $insert = [];

            foreach ($fnums as $fnum) {
                foreach($users as $user) {
                    foreach ($actions as $action) {
                        $ac = (array) $action;
                        $insert[] = $user.','.$ac['id'].','.$ac['c'].','.$ac['r'].','.$ac['u'].','.$ac['d'].','.$db->quote($fnum);
                    }
                }
            }

            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__emundus_users_assoc'))
                ->where($db->quoteName('user_id').' IN ('.implode(',',$users).') AND '.$db->quoteName('fnum').' IN ("'.implode('","',$fnums).'")');
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->insert($db->quoteName('#__emundus_users_assoc'))
                ->columns($db->quoteName(['user_id', 'action_id', 'c', 'r', 'u', 'd', 'fnum']))
                ->values($insert);
            $db->setQuery($query);
            $db->execute();

        } catch (Exception $e) {
            $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
            JLog::add($error, JLog::ERROR, 'com_emundus');
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getAllTags() {
        $query = 'select * from #__emundus_setup_action_tag where 1';
        $db = $this->getDbo();

        try {
            $db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getAllGroups() {
        $query = 'select * from #__emundus_setup_groups where published=1';
        $db = $this->getDbo();

        try {
            $db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            throw $e;
        }
    }

    /** Gets the groups the user is a part of OR if the user has read access on groups, all groups.
     * @return mixed
     * @throws Exception
     */
    public function getUserAssocGroups() {

        $user = JFactory::getUser();

        if (EmundusHelperAccess::asAccessAction(19, 'c', $user->id)) {
            $query = 'select * from #__emundus_setup_groups where published=1';
        } else {
            $query = 'SELECT * from #__emundus_setup_groups AS sg 
					WHERE sg.id IN (SELECT g.group_id FROM jos_emundus_groups AS g WHERE g.user_id = '.$user->id.') AND sg.published = 1';
        }

        $db = $this->getDbo();

        try {
            $db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getAllInstitutions() {
        $query = 'select * from #__categories where extension="com_contact" order by lft';
        $db = $this->getDbo();

        try {
            $db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getAllStatus()
    {
        $query = 'select * from #__emundus_setup_status order by ordering';
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
    public function tagFile($fnums, $tags) {
        try {
            $db = $this->getDbo();
            $user = JFactory::getUser()->id;

            $query_associated_tags = $db->getQuery(true);
            $query ="insert into #__emundus_tag_assoc (fnum, id_tag, user_id) VALUES ";

            if (!empty($fnums) && !empty($tags)) {
                $logger = array();
                foreach ($fnums as $fnum) {
                    // Get tags already associated to this fnum by the current user
                    $query_associated_tags->clear()
                        ->select('id_tag')
                        ->from($db->quoteName('#__emundus_tag_assoc'))
                        ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum))
                        ->andWhere($db->quoteName('user_id') . ' = ' . $db->quote($user));
                    $db->setQuery($query_associated_tags);
                    $tags_already_associated = $db->loadColumn();

                    // Insert valid tags
                    foreach ($tags as $tag) {
                        if(!in_array($tag,$tags_already_associated)) {
                            $query .= '("' . $fnum . '", ' . $tag . ',' . $user . '),';
                            $query_log = 'SELECT label
                                FROM #__emundus_setup_action_tag
                                WHERE id =' . $tag;
                            $db->setQuery($query_log);
                            $log_tag = $db->loadResult();

                            //stock the tag name
                            $logsStd = new stdClass();

                            $logsStd->details = $log_tag;
                            $logger[] = $logsStd;
                        }
                    }

                    if(!empty($logger)) {
                        $logsParams = array('created' => array_unique($logger, SORT_REGULAR));
                    } else {
                        continue;
                    }

                    // Log the tags in the eMundus logging system.
                    EmundusModelLogs::log($user, (int)substr($fnum, -7), $fnum, 14, 'c', 'COM_EMUNDUS_ACCESS_TAGS_CREATE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
                }

                $query = substr_replace($query, ";", -1);
                $db->setQuery($query);
                $db->execute();
            }

            return true;
        }
        catch (Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
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
        $query = 'select t.fnum, sat.class from #__emundus_tag_assoc as t join #__emundus_setup_action_tag as sat on sat.id = t.id_tag where ';
        $user = JFactory::getUser()->id;

        if (is_null($tag)) {
            $query .= ' t.user_id = ' . $user;
            try {
                $db->setQuery($query);
                return $db->loadAssocList('fnum');
            } catch (Exception $e) {
                throw $e;
            }

        } else {

            $user = JFactory::getUser()->id;

            if (is_array($tag))
                $query .= ' t.id_tag IN ('.implode(',',$tag). ') and t.user_id = ' . $user;
            else
                $query .= ' t.id_tag = '.$tag. ' and t.user_id = ' . $user;

            try {
                $db->setQuery($query);
                return $db->loadAssocList();
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * @param $fnums
     * @param $state
     * @return bool|mixed
     */
    public function updateState($fnums, $state) {

        $db = $this->getDbo();
        $dispatcher = JEventDispatcher::getInstance();

        $query = $db->getQuery(true);
        $query->select($db->quoteName('profile'))
            ->from($db->quoteName('#__emundus_setup_status'))
            ->where($db->quoteName('step').' = '.$state);
        $db->setQuery($query);

        try {
            $profile = $db->loadResult();
        } catch (Exception $e) {
            $profile = null;
        }


        try {
            if (is_array($fnums)) {
                foreach ($fnums as $fnum) {

                    $dispatcher->trigger('onBeforeStatusChange', [$fnum, $state]);
                    $dispatcher->trigger('callEventHandler', ['onBeforeStatusChange', ['fnum' => $fnum, 'state' => $state]]);

                    $query = $db->getQuery(true);

                    $query
                        ->update($db->quoteName('#__emundus_campaign_candidature'))
                        ->set($db->quoteName('status').' = '.$state)
                        ->where($db->quoteName('fnum').' LIKE '. $db->Quote($fnum));

                    $db->setQuery($query);
                    $res = $db->execute();
                    $dispatcher->trigger('onAfterStatusChange', [$fnum, $state]);
                    $dispatcher->trigger('callEventHandler', ['onAfterStatusChange', ['fnum' => $fnum, 'state' => $state]]);

                    if (!empty($profile)) {

                        $query = $db->getQuery(true);
                        $query->update($db->quoteName('#__emundus_users'))
                            ->set($db->quoteName('profile').' = '.$profile)
                            ->where($db->quoteName('user_id').' = '.substr($fnum, -7));
                        $db->setQuery($query);
                        $db->execute();

                    }
                }
            }
            else {
                $dispatcher->trigger('onBeforeStatusChange', [$fnums, $state]);
                $dispatcher->trigger('callEventHandler', ['onBeforeStatusChange', ['fnums' => $fnums, 'state' => $state]]);
                $query = $db->getQuery(true);

                $query
                    ->update($db->quoteName('#__emundus_campaign_candidature'))
                    ->set($db->quoteName('status').' = '.$state)
                    ->where($db->quoteName('fnum').' LIKE '. $db->Quote($fnums));

                $db->setQuery($query);
                $res = $db->execute();
                $dispatcher->trigger('onAfterStatusChange', [$fnums, $state]);
                $dispatcher->trigger('callEventHandler', ['onAfterStatusChange', ['fnums' => $fnums, 'state' => $state]]);

                if (!empty($profile)) {
                    $query = $db->getQuery(true);
                    $query->update($db->quoteName('#__emundus_users'))
                        ->set($db->quoteName('profile').' = '.$profile)
                        ->where($db->quoteName('user_id').' = '.substr($fnums, -7));
                    $db->setQuery($query);
                    $db->execute();
                }
            }

            return $res;

        } catch (Exception $e) {

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
    public function updatePublish($fnums, $publish) {

        $dispatcher = JEventDispatcher::getInstance();

        $db = $this->getDbo();
        foreach ($fnums as $fnum) {

            // Log the update in the eMundus logging system.
            // Get the old publish status
            $query = $db->getQuery(true);
            $query->select($db->quoteName('published'))
                ->from($db->quoteName('#__emundus_campaign_candidature'))
                ->where($db->quoteName('fnum').' = '.$fnum);
                $db->setQuery($query);
            $old_publish = $db->loadResult();
            // Before logging, translate the publish id to corresponding label
            // Old publish status
            switch ($old_publish) {
                case(1):
                    $old_publish = JText::_('PUBLISHED');
                break;
                case(0):
                    $old_publish = JText::_('ARCHIVED');
                break;
                case(-1):
                    $old_publish = JText::_('TRASHED');
                break;
            }
            // New publish status
            switch ($publish) {
                case(1):
                    $new_publish = JText::_('PUBLISHED');
                break;
                case(0):
                    $new_publish = JText::_('ARCHIVED');
                break;
                case(-1):
                    $new_publish = JText::_('TRASHED');
                break;
            }
            // Log the update
            $logsParams = array('updated' => []);
            array_push($logsParams['updated'], ['old' => $old_publish, 'new' => $new_publish]);
            EmundusModelLogs::log(JFactory::getUser()->id, (int)substr($fnum, -7), $fnum, 28, 'u', 'COM_EMUNDUS_PUBLISH_UPDATE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));

            // Update publish
            $dispatcher->trigger('onBeforePublishChange', [$fnum, $publish]);
            $dispatcher->trigger('callEventHandler', ['onBeforePublishChange', ['fnum' => $fnum, 'publish' => $publish]]);
            $query = 'update #__emundus_campaign_candidature set published = '.$publish.' WHERE fnum like '.$db->Quote($fnum) ;
            $db->setQuery($query);
            try {
                $res = $db->execute();
            } catch (Exception $e) {
                echo $e->getMessage();
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                return false;
            }
            $dispatcher->trigger('onAfterPublishChange', [$fnum, $publish]);
            $dispatcher->trigger('callEventHandler', ['onAfterPublishChange', ['fnum' => $fnum, 'publish' => $publish]]);
        }
        return $res;
    }

    /**
     * @param   array  $fnums
     *
     * @return mixed|null
     */
    public function getPhotos($fnums = array()) {
        try {
            $db = $this->getDbo();
            $query = 'select emu.id, emu.user_id, c.fnum, emu.filename
                        from #__emundus_uploads as emu
                        left join #__emundus_campaign_candidature as c on c.applicant_id = emu.user_id
                        where attachment_id = 10';
            if (count($fnums) > 0) {
                $query .= ' AND emu.fnum IN ('.implode(',', $db->quote($fnums)).') GROUP BY emu.fnum';
            }
            $db->setQuery($query);
            return $db->loadAssocList('fnum');

        } catch(Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return null;
        }

    }

    /**
     * @return mixed|null
     */
    public function getEvaluatorsFromGroup() {
        try {
            $db = $this->getDbo();
            $query = 'select distinct ga.fnum, u.name, g.title, g.id  from #__emundus_group_assoc  as ga
						left join #__user_usergroup_map as uum on uum.group_id = ga.group_id
						left join #__users as u on u.id = uum.user_id
						left join #__usergroups as g on g.id = ga.group_id
						where 1 order by ga.fnum asc, g.title';
            $db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
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
            return $db->execute() ;
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
    public static function getFnumInfos($fnum) {
        try {
            $db = JFactory::getDBO();
            $query = 'select u.name, u.email, cc.fnum, cc.date_submitted, cc.applicant_id, cc.status, cc.published as state, ss.value, ss.class, c.*, cc.campaign_id
                        from #__emundus_campaign_candidature as cc
                        left join #__emundus_setup_campaigns as c on c.id = cc.campaign_id 
                        left join #__users as u on u.id = cc.applicant_id
                        left join #__emundus_setup_status as ss on ss.step = cc.status
                        where cc.fnum like '.$db->Quote($fnum);
            $db->setQuery($query);
            $fnumInfos = $db->loadAssoc();

            $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);
            if ($anonymize_data) {
                $fnumInfos['name'] = $fnum;
                $fnumInfos['email'] = $fnum;
            }

            return $fnumInfos;

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $fnums
     * @return bool|mixed
     */
    public function getFnumsInfos($fnums, $format = 'array')
    {
        try
        {
            $db = $this->getDbo();
            $query = 'select u.name, u.email, cc.fnum, ss.step, ss.value, sc.label, sc.start_date, sc.end_date, sc.year, sc.id as campaign_id, sc.published, sc.training, cc.applicant_id
                        from #__emundus_campaign_candidature as cc
                        left join #__users as u on u.id = cc.applicant_id
                        left join #__emundus_setup_campaigns as sc on sc.id = cc.campaign_id
                        left join #__emundus_setup_status as ss on ss.step = cc.status
                        where cc.fnum in ("'. implode('","', $fnums).'")';
            $db->setQuery($query);
//echo str_replace('#_', 'jos', $query);
            if ($format == 'array') {
                return $db->loadAssocList('fnum');
            } else {
                return $db->loadObjectList('fnum');
            }
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
            $query = 'select u.name as applicant_name, u.email as applicant_email, u.username as username, cc.fnum, sc.id as campaign_id , sc.label as campaign_label, sc.training as campaign_code,  sc.start_date as campaign_start, sc.end_date as campaign_end, sc.year as campaign_year,  jesp.code as training_code, jesp.label as training_programme, cc.applicant_id as applicant_id, jess.value as application_status, group_concat(jesat.label) as application_tags
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

    /** Gets info for Fabrik tags.
     * @param String $fnum
     * @return bool|mixed
     */
    public function getFnumTagsInfos($fnum)
    {
        try
        {
            $db = $this->getDbo();
            $query = 'select u.name as applicant_name, u.email as applicant_email, u.username as username, cc.fnum, sc.id as campaign_id , sc.label as campaign_label, sc.training as campaign_code,  sc.start_date as campaign_start, sc.end_date as campaign_end, sc.year as campaign_year,  jesp.code as training_code, jesp.label as training_programme, cc.applicant_id as applicant_id, jess.value as application_status, group_concat(jesat.label) as application_tags
                        from jos_emundus_campaign_candidature as cc
                        left join jos_users as u on u.id = cc.applicant_id
                        left join jos_emundus_setup_campaigns as sc on sc.id = cc.campaign_id
                        left join jos_emundus_setup_programmes as jesp on jesp.code = sc.training
                        left join jos_emundus_setup_status as jess on jess.step = cc.status
                        left join jos_emundus_tag_assoc as jeta on jeta.fnum like cc.fnum
                        left join jos_emundus_setup_action_tag as jesat on jesat.id = jeta.id_tag
                        where cc.fnum = '.$fnum;
            $db->setQuery($query);
            return $db->loadAssoc();
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
     * @throws Exception
     */
    public function getAllFnums($assoc_tab_fnums = false) {
        include_once(JPATH_SITE.'/components/com_emundus/models/users.php');
        $m_users = new EmundusModelUsers;

        $current_user = JFactory::getUser();

        $this->code = $m_users->getUserGroupsProgrammeAssoc($current_user->id);

        $groups = $m_users->getUserGroups($current_user->id, 'Column');
        $fnum_assoc_to_groups = $m_users->getApplicationsAssocToGroups($groups);
        $fnum_assoc_to_user = $m_users->getApplicantsAssoc($current_user->id);
        $this->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc_to_user);

        $files = $this->getAllUsers(0, 0);
        $fnums = array();

        if ($assoc_tab_fnums) {
            foreach($files as $file){
                if ($file['applicant_id'] > 0) {
                    $fnums[] = array( 'fnum' => $file['fnum'],
                        'applicant_id' => $file['applicant_id'],
                        'campaign_id' => $file['campaign_id']
                    );
                }
            }
        } else {
            foreach($files as $file){
                $fnums[] = $file['fnum'];
            }
        }

        return $fnums;
    }

    /*
    *   Get values of elements by list of files numbers
    *   @param fnums    List of application files numbers
    *   @param elements     array of element to get value
    *   @return array
    */
        public function getFnumArray($fnums, $elements, $methode=0, $start=0, $pas=0, $raw=0, $defaultElement='') {

            $db = $this->getDbo();
            $locales = substr(JFactory::getLanguage()->getTag(), 0 , 2);

            $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);

            if(empty($defaultElement)){
                if ($anonymize_data) {
                    $query = 'select jos_emundus_campaign_candidature.fnum, esc.label, sp.code, esc.id as campaign_id';
                } else {
                    $query = 'select jos_emundus_campaign_candidature.fnum, u.email, esc.label, sp.code, esc.id as campaign_id';
                }
            }
            else{
                $query = $defaultElement;
            }


            $leftJoin = '';
            $leftJoinMulti = '';
            $tableAlias = [
                'jos_emundus_setup_campaigns' => 'esc',
                'jos_emundus_campaign_candidature' => 'jos_emundus_campaign_candidature',
                'jos_emundus_setup_programmes' => 'sp',
                'jos_users' => 'u',
                'jos_emundus_tag_assoc' => 'eta'
            ];
            $lastTab = array();

            foreach ($elements as $elt) {
                $params_group = json_decode($elt->group_attribs);

                try{
                    $query_isjoin = 'select is_join from jos_fabrik_groups where id = '.$elt->group_id;
                    $db->setQuery($query_isjoin);
                    $is_join = $db->loadResult();
                } catch(Exception $e){
                    JLog::add('Error when get param is_join from group : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                }


                if (!array_key_exists($elt->tab_name, $tableAlias)) {

                    $tableAlias[$elt->tab_name] = $elt->tab_name;

                    if (!isset($lastTab)) {
                        $lastTab = array();
                    }
                    if (!in_array($elt->tab_name, $lastTab)) {
                        $leftJoin .= ' left join '.$elt->tab_name.' on '.$elt->tab_name.'.fnum = jos_emundus_campaign_candidature.fnum ';
                    }

                    $lastTab[] = $elt->tab_name;
                }

                if ($params_group->repeat_group_button == 1 || $is_join == 1) {
                    // Get the table repeat table name using this query
                    $repeat_join_table_query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id=' . $elt->group_id . ' AND table_join_key like "parent_id"';
                    try {
                        $this->_db->setQuery($repeat_join_table_query);
                        $repeat_join_table = $this->_db->loadResult();
                    } catch (Exception $e) {
                        JLog::add('Line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                        throw $e;
                    }
                    if ($methode == 1) {
                        if ($elt->element_plugin == 'databasejoin') {
                            $element_attribs = json_decode($elt->element_attribs);

                            if ($element_attribs->database_join_display_type == "checkbox") {

                                $t = $elt->table_join;

                                $join_query = $db->getQuery(true);

                                $join_query
                                    ->select($db->quoteName('join_from_table'))
                                    ->from($db->quoteName('#__fabrik_joins'))
                                    ->where($db->quoteName('element_id') . ' = ' . $elt->id);

                                $db->setQuery($join_query);

                                try {
                                    $join_table = $db->loadResult();

                                    $join_val_column = !empty($element_attribs->join_val_column_concat)
                                        ? 'CONCAT('.str_replace('{thistable}', 't', str_replace('{shortlang}', $this->locales, $element_attribs->join_val_column_concat)).')'
                                        : 't.'.$element_attribs->join_val_column;

                                    $sub_select = '(
                                        SELECT ' . $t . '.parent_id AS pid, CONCAT("[\"",GROUP_CONCAT('.$join_val_column.' SEPARATOR ", \""), "\"]") AS vals
                                        FROM '.$t.'
                                        LEFT JOIN '.$element_attribs->join_db_name. ' AS  t ON ' .'t.'.$element_attribs->join_key_column.' = '.$t.'.'.$elt->element_name.'
                                        GROUP BY pid
                                  ) sub_select';

                                    $select = '(SELECT GROUP_CONCAT(vals) FROM '. $sub_select. ' WHERE sub_select.pid = ' . $join_table . '.id)';

                                } catch (Exception $e) {
                                    $error = JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage().' :: '.preg_replace("/[\r\n]/", " ", $query);
                                    JLog::add($error, JLog::ERROR, 'com_emundus');
                                }

                            } else {

                                $join_val_column = !empty($element_attribs->join_val_column_concat)?'CONCAT('.str_replace('{thistable}', 't', str_replace('{shortlang}', $this->locales, $element_attribs->join_val_column_concat)).')':'t.'.$element_attribs->join_val_column;

                                $select .= 'FROM '.$tableAlias[$elt->tab_name].'
                                    LEFT JOIN '.$repeat_join_table.' ON '.$repeat_join_table.'.parent_id = '.$tableAlias[$elt->tab_name].'.id
                                    LEFT JOIN '.$element_attribs->join_db_name.' as t ON t.'.$element_attribs->join_key_column.' = '.$repeat_join_table.'.'.$elt->element_name.'
                                    WHERE '.$tableAlias[$elt->tab_name].'.fnum=jos_emundus_campaign_candidature.fnum)';
                            }

                            $query .= ', ' . $select . ' AS ' . $elt->table_join . '___' . $elt->element_name;

                        } elseif ($elt->element_plugin == 'cascadingdropdown') {
                            $element_attribs = json_decode($elt->element_attribs);
                            $cascadingdropdown_id = $element_attribs->cascadingdropdown_id;
                            $r1 = explode('___', $cascadingdropdown_id);
                            $cascadingdropdown_label = $element_attribs->cascadingdropdown_label;
                            $r2 = explode('___', $cascadingdropdown_label);
                            $select = !empty($element_attribs->cascadingdropdown_label_concat)?"CONCAT(".$element_attribs->cascadingdropdown_label_concat.")":$r2[1];
                            $from = $r2[0];

                            // Checkboxes behave like repeat groups and therefore need to be handled a second level of depth.
                            if ($element_attribs->cdd_display_type == 'checkbox') {
                                $select = !empty($element_attribs->cascadingdropdown_label_concat)?" CONCAT(".$element_attribs->cascadingdropdown_label_concat.")":'GROUP_CONCAT('.$r2[1].')';

                                // Load the Fabrik join for the element to it's respective repeat_repeat table.
                                $q = $db->getQuery(true);
                                $q->select([$db->quoteName('join_from_table'), $db->quoteName('table_key'), $db->quoteName('table_join'), $db->quoteName('table_join_key')])
                                    ->from($db->quoteName('#__fabrik_joins'))
                                    ->where($db->quoteName('element_id').' = '.$elt->table_join.'.'.$elt->element_name);
                                $db->setQuery($q);
                                $f_join = $db->loadObject();

                                $where = $r1[1].' IN (
                                SELECT '.$db->quoteName($f_join->table_join.'.'.$f_join->table_key).'
                                FROM '.$db->quoteName($f_join->table_join).' 
                                WHERE '.$db->quoteName($f_join->table_join.'.'.$f_join->table_join_key).' = '.$elt->id.')';
                            } else {
                                $where = $r1[1].'='.$elt->table_join.'.'.$elt->element_name;
                            }

                            $sub_query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
                            $sub_query = preg_replace('#{thistable}#', $from, $sub_query);
                            $sub_query  = preg_replace('#{shortlang}#', $locales, $sub_query);

                            $query .= ', ('.$sub_query.') AS '. $elt->table_join.'___'.$elt->element_name;
                        } elseif ($elt->element_plugin == 'dropdown' || $elt->element_plugin == 'radiobutton') {
                            $select = $elt->table_join.'.'.$elt->element_name;
                            $element_attribs = json_decode($elt->element_attribs);
                            foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                                if(empty($first_replace)) {
                                    $select = "REPLACE(" . $select . ",'" . $value . "','" . $element_attribs->sub_options->sub_labels[$key] . "')";
                                } else {
                                    $select .= ",REPLACE(" . $select . ",'" . $value . "','" . $element_attribs->sub_options->sub_labels[$key] . "')";
                                }
                            }
                            $query .= ', '.$select.' AS '. $elt->table_join.'___'.$elt->element_name;
                            if (!in_array($elt->table_join, $lastTab)) {
                                $leftJoinMulti .= ' left join '.$elt->table_join.' on '.$elt->table_join.'.parent_id='.$elt->tab_name.'.id ';
                            }
                        } else {
                            $query .= ', '.$elt->table_join.'.'.$elt->element_name.' AS '. $elt->table_join.'___'.$elt->element_name;
                            if (!in_array($elt->table_join, $lastTab)) {
                                $leftJoinMulti .= ' left join '.$elt->table_join.' on '.$elt->table_join.'.parent_id='.$elt->tab_name.'.id ';
                            }
                        }
                        $lastTab[] = $elt->table_join;
                    } else {
                        if ($elt->element_plugin == 'databasejoin') {

                            $element_attribs = json_decode($elt->element_attribs);

                            if ($element_attribs->database_join_display_type == "checkbox") {

                                $t = $elt->table_join;

                                $join_query = $db->getQuery(true);

                                $join_query
                                    ->select($db->quoteName('join_from_table'))
                                    ->from($db->quoteName('#__fabrik_joins'))
                                    ->where($db->quoteName('element_id') . ' = ' . $elt->id);

                                $db->setQuery($join_query);

                                try {
                                    $join_table = $db->loadResult();

                                    $join_val_column = !empty($element_attribs->join_val_column_concat) ?
                                        'CONCAT('.str_replace('{thistable}', 't', str_replace('{shortlang}', $this->locales, $element_attribs->join_val_column_concat)).')':
                                        't.'.$element_attribs->join_val_column;

                                    $sub_select = '(
                                    SELECT ' . $t . '.parent_id AS pid, ' . $elt->tab_name.'_'. $elt->group_id.'_repeat.parent_id AS pid2, CONCAT("[\"",GROUP_CONCAT('.$join_val_column.' SEPARATOR ", \""), "\"]") AS vals
                                    FROM '.$t.'
                                    LEFT JOIN '.$element_attribs->join_db_name. ' AS  t ON ' .'t.'.$element_attribs->join_key_column.' = '.$t.'.'.$elt->element_name.'
                                    LEFT JOIN '.$join_table. ' ON ' .$elt->table_join.'.parent_id = '.$join_table.'.id
                                    WHERE '.$t.'.parent_id='.$elt->tab_name.'_'. $elt->group_id.'_repeat.id
                                        GROUP BY pid
                                  ) sub_select';

                                    $select = '(SELECT GROUP_CONCAT(vals) FROM '. $sub_select. ' WHERE pid2 = ' . $elt->tab_name . '.id)';

                                } catch (Exception $e) {
                                    $error = JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage().' :: '.preg_replace("/[\r\n]/", " ", $query);
                                    JLog::add($error, JLog::ERROR, 'com_emundus');
                                }

                            } else {
                                $join_val_column = !empty($element_attribs->join_val_column_concat)?'CONCAT('.str_replace('{thistable}', 't', str_replace('{shortlang}', $this->locales, $element_attribs->join_val_column_concat)).')':'t.'.$element_attribs->join_val_column;

                                if ($methode == 2) {
                                    $select = '(SELECT GROUP_CONCAT('.$join_val_column.' SEPARATOR ", ") ';
                                } else {
                                    $select = '(SELECT GROUP_CONCAT(DISTINCT('.$join_val_column.') SEPARATOR ", ") ';
                                }

                                $select .= 'FROM '.$tableAlias[$elt->tab_name].'
                                    LEFT JOIN '.$repeat_join_table.' ON '.$repeat_join_table.'.parent_id = '.$tableAlias[$elt->tab_name].'.id
                                    LEFT JOIN '.$element_attribs->join_db_name.' as t ON t.'.$element_attribs->join_key_column.' = '.$repeat_join_table.'.'.$elt->element_name.'
                                    WHERE '.$tableAlias[$elt->tab_name].'.fnum=jos_emundus_campaign_candidature.fnum)';
                            }

                            $query .= ', ' . $select . ' AS ' . $elt->table_join . '___' . $elt->element_name;
                        }
                        elseif ($elt->element_plugin == 'cascadingdropdown') {

                            $element_attribs = json_decode($elt->element_attribs);
                            $from = explode('___', $element_attribs->cascadingdropdown_label)[0];
                            $where = explode('___', $element_attribs->cascadingdropdown_id)[1].'='.$repeat_join_table.'.'.$elt->element_name;
                            $join_val_column = !empty($element_attribs->cascadingdropdown_label_concat)?'CONCAT('.str_replace('{thistable}', 't', str_replace('{shortlang}', $this->locales, $element_attribs->cascadingdropdown_label_concat)).')':'t.'.explode('___', $element_attribs->cascadingdropdown_label)[1];

                            $select = '(SELECT GROUP_CONCAT(DISTINCT('.$join_val_column.') SEPARATOR ", ")
                                FROM '.$tableAlias[$elt->tab_name].'
                                LEFT JOIN '.$repeat_join_table.' ON '.$repeat_join_table.'.parent_id = '.$tableAlias[$elt->tab_name].'.id
                                LEFT JOIN '.$from.' as t ON t.'.$where.'
                                WHERE '.$tableAlias[$elt->tab_name].'.fnum=jos_emundus_campaign_candidature.fnum)';

                            $query .= ', ' . $select . ' AS ' . $elt->table_join . '___' . $elt->element_name;
                        } elseif ($elt->element_plugin == 'dropdown' || $elt->element_plugin == 'radiobutton') {
                            $select = 'REPLACE(`'.$elt->table_join . '`.`' . $elt->element_name.'`, "\t", "" )';
                            if($raw == 1){
                                $select = $select;
                            } else{
                                $element_attribs = json_decode($elt->element_attribs);
                                foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                                    $if[] = 'IF(' . $select . '="' . $value . '","' . $element_attribs->sub_options->sub_labels[$key] . '"';
                                    $endif .= ')';
                                }
                                $select = implode(',', $if) . ',' . $select . $endif;
                            }
                            $select = '(SELECT GROUP_CONCAT(DISTINCT('.$select.') SEPARATOR ", ")
                                FROM '.$tableAlias[$elt->tab_name].'
                                LEFT JOIN '.$repeat_join_table.' ON '.$repeat_join_table.'.parent_id = '.$tableAlias[$elt->tab_name].'.id
                                WHERE '.$tableAlias[$elt->tab_name].'.fnum=jos_emundus_campaign_candidature.fnum)';
                            $query .= ', ' . $select . ' AS ' . $elt->tab_name . '___' . $elt->element_name;
                        } elseif ($elt->element_plugin == 'yesno') {
                            $select = 'REPLACE(`'.$elt->table_join . '`.`' . $elt->element_name.'`, "\t", "" )';
                            if($raw == 1){
                                $select = $select;
                            } else{
                                $if[] = 'IF(' . $select . '="0","' . JText::_('JNO') . '"';
                                $endif .= ')';
                                $if[] = 'IF(' . $select . '="1","' . JText::_('JYES') . '"';
                                $endif .= ')';
                                $select = implode(',', $if) . ',' . $select . $endif;
                                $query .= ', ( SELECT GROUP_CONCAT('.$select.' SEPARATOR ", ") ';
                            }

                            $query .= ' FROM '.$elt->table_join.'
                                        WHERE '.$elt->table_join.'.parent_id='.$tableAlias[$elt->tab_name].'.id
                                      ) AS '. $elt->table_join.'___'.$elt->element_name;

                        } else {

                            if ($methode == 2) {
                                $query .= ', ( SELECT GROUP_CONCAT('.$elt->table_join.'.'.$elt->element_name.' SEPARATOR ", ") ';
                            } else {
                                $query .= ', ( SELECT GROUP_CONCAT(DISTINCT('.$elt->table_join.'.'.$elt->element_name.') SEPARATOR ", ") ';
                            }

                            $query .= ' FROM '.$elt->table_join.'
                                        WHERE '.$elt->table_join.'.parent_id='.$tableAlias[$elt->tab_name].'.id
                                      ) AS '. $elt->table_join.'___'.$elt->element_name;
                        }
                    }
                } else {
                    $select = 'REPLACE(`'.$tableAlias[$elt->tab_name] . '`.`' . $elt->element_name.'`, "\t", "" )';
                    $if = array();
                    $endif = '';


                    if ($elt->element_plugin == 'dropdown' || $elt->element_plugin == 'radiobutton') {
                        if($raw == 1){
                            $select = 'REPLACE(`'.$tableAlias[$elt->tab_name] . '`.`' . $elt->element_name.'`, "\t", "" )';
                        }
                        else{
                            $element_attribs = json_decode($elt->element_attribs);
                            foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                                $if[] = 'IF(' . $select . '="' . $value . '","' . $element_attribs->sub_options->sub_labels[$key] . '"';
                                $endif .= ')';
                            }
                            $select = implode(',', $if) . ',' . $select . $endif;
                        }
                    } elseif ($elt->element_plugin == 'yesno') {
                        if ($raw == 1){
                            $select = $select;
                        }
                        else {
                            $if[] = 'IF(' . $select . '="0","' . JText::_('JNO') . '"';
                            $endif .= ')';
                            $if[] = 'IF(' . $select . '="1","' . JText::_('JYES') . '"';
                            $endif .= ')';
                            $select = implode(',', $if) . ',' . $select . $endif;
                        }
                    } elseif ($elt->element_plugin == 'databasejoin') {

                        $element_attribs = json_decode($elt->element_attribs);

                        if ($element_attribs->database_join_display_type == "checkbox") {
                            $select_check = $element_attribs->join_val_column;
                            if (!empty($element_attribs->join_val_column_concat)) {
                                $select_check = $element_attribs->join_val_column_concat;
                                $select_check = preg_replace('#{thistable}#', 'jd', $select_check);
                                $select_check = preg_replace('#{shortlang}#', $this->locales, $select_check);
                            }

                            $t = $tableAlias[$elt->tab_name] . '_repeat_' . $elt->element_name;
                            if($raw == 1){
                                $select = '(
                                    SELECT GROUP_CONCAT(' . $elt->element_name . ' SEPARATOR ", ")
                                    FROM ' . $t . ' AS t
                                    WHERE ' . $tableAlias[$elt->tab_name] . '.id = t.parent_id
                                    )';
                            }else{
                                $select = '(
                                    SELECT GROUP_CONCAT(' . $select_check . ' SEPARATOR ", ")
                                    FROM ' . $t . ' AS t
                                    LEFT JOIN ' . $element_attribs->join_db_name . ' AS jd ON jd.' . $element_attribs->join_key_column . ' = t.' . $elt->element_name . '
                                    WHERE ' . $tableAlias[$elt->tab_name] . '.id = t.parent_id
                                    )';
                            }
                        } else {
                            if($raw == 1){
                                $query .= ', '.$select.' AS '.$tableAlias[$elt->tab_name].'___'.$elt->element_name;
                            }
                            else {
                                $join_val_column = !empty($element_attribs->join_val_column_concat) ? 'CONCAT(' . str_replace('{thistable}', 't', str_replace('{shortlang}', $this->locales, $element_attribs->join_val_column_concat)) . ')' : 't.' . $element_attribs->join_val_column;

                                $select = '(SELECT GROUP_CONCAT(DISTINCT(' . $join_val_column . ') SEPARATOR ", ")
                                FROM ' . $element_attribs->join_db_name . ' as t
                                WHERE t.' . $element_attribs->join_key_column . '=' . $tableAlias[$elt->tab_name] . '.' . $elt->element_name . ')';
                            }
                        }

                    } elseif ($elt->element_plugin == 'cascadingdropdown') {

                        $element_attribs = json_decode($elt->element_attribs);
                        $from = explode('___', $element_attribs->cascadingdropdown_label)[0];
                        $where = explode('___', $element_attribs->cascadingdropdown_id)[1] . '=' . $elt->tab_name . '.' . $elt->element_name;
                        $join_val_column = !empty($element_attribs->cascadingdropdown_label_concat) ? 'CONCAT(' . str_replace('{thistable}', 't', str_replace('{shortlang}', $this->locales, $element_attribs->join_val_column_concat)) . ')' : 't.' . explode('___', $element_attribs->cascadingdropdown_label)[1];

                        if($raw == 1){
                            $select = '(SELECT '. $elt->element_name .'
                            FROM ' . $tableAlias[$elt->tab_name] . ' as t
                            WHERE t.fnum LIKE '.$db->quote($fnums['fnum']).')';
                        }
                        else{
                            $select = '(SELECT GROUP_CONCAT(DISTINCT(' . $join_val_column . ') SEPARATOR ", ")
                            FROM ' . $from . ' as t
                            WHERE t.' . $where . ')';
                        }
                    }

                    $query .= ', ' . $select . ' AS ' . $tableAlias[$elt->tab_name] . '___' . $elt->element_name;

                }
            }

            $query .= ' from #__emundus_campaign_candidature as jos_emundus_campaign_candidature
                        left join #__users as u on u.id = jos_emundus_campaign_candidature.applicant_id
                        left join #__emundus_users as eu on u.id = eu.user_id
                        left join #__emundus_setup_campaigns as esc on esc.id = jos_emundus_campaign_candidature.campaign_id
                        left join #__emundus_setup_programmes as sp on sp.code = esc.training';

            if(!empty($defaultElement)){
                $query .= ' LEFT JOIN #__emundus_tag_assoc as eta ON  eta.fnum = jos_emundus_campaign_candidature.fnum
                        LEFT JOIN #__emundus_setup_action_tag as esat ON esat.id= eta.id_tag
                        LEFT JOIN #__emundus_setup_status as ess ON ess.step = jos_emundus_campaign_candidature.status
                        LEFT JOIN #__emundus_declaration as ed ON ed.fnum = jos_emundus_campaign_candidature.fnum';
            }

            $query .= $leftJoin. ' '. $leftJoinMulti;

            $query .= 'where u.block=0 AND jos_emundus_campaign_candidature.fnum in ("'.implode('","', $fnums).'") ';
            if (preg_match("/emundus_evaluations/i", $query)) {

                $current_user = JFactory::getSession()->get('emundusUser');

                $eval_query = $db->getQuery(true);
                $eval_query
                    ->select('id')
                    ->from($db->quoteName('#__emundus_evaluations'))
                    ->where($db->quoteName('fnum') . ' IN (' . implode(',', $db->quote($fnums)) . ')')
                    ->andWhere($db->quoteName('user') . ' = ' . JFactory::getUser()->id);

                $db->setQuery($eval_query);
                $eval = $db->loadResult();

                if ((!EmundusHelperAccess::asAccessAction(5,  'r', JFactory::getUser()->id) && EmundusHelperAccess::asAccessAction(5,  'c', JFactory::getUser()->id))) {
                    if ((!empty($current_user->fnums) && !empty(array_diff($fnums, array_keys($current_user->fnums)))) || ((@EmundusHelperAccess::isEvaluator($current_user->id) && !@EmundusHelperAccess::isCoordinator($current_user->id)))) {
                        if($eval){
                            $query .= ' AND jos_emundus_evaluations.user = '.JFactory::getUser()->id;
                        }
                    }
                }

            }

            if ($pas != 0) {
                $query .= ' LIMIT '.$pas.' OFFSET '.$start;
            }

            try {
                $db->setQuery($query);
                return $db->loadAssocList();
            } catch (Exception $e) {
                $error = JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage().' :: '.preg_replace("/[\r\n]/", " ", $query);
                JLog::add($error, JLog::ERROR, 'com_emundus');
                JFactory::getApplication()->enqueueMessage($error, 'error');

                return false;
            }
        }

    /**
     * @param $fnums
     * @return bool|mixed
     */
    public function getEvalsByFnum($fnums) {

        try {

            $db = $this->getDbo();
            $query = 'select * from #__emundus_evaluations where fnum in ("'.implode('","', $fnums).'")';
            $db->setQuery($query);
            return $db->loadAssocList('fnum');

        } catch (Exception $e) {
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

    /** Gets the evaluation of a user based on fnum and
     * @param fnum
     * @param evaluator_id
     * @return bool|mixed
     */
    public function getEvalByFnumAndEvaluator($fnum, $evaluator_id) {

        try {

            $db = $this->getDbo();
            $query = 'SELECT * FROM #__emundus_evaluations WHERE fnum = '.$fnum.' AND user = '.$evaluator_id;
            $db->setQuery($query);
            return $db->loadAssocList();

        } catch(Exception $e) {
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
    public function getFilesByFnums($fnums, $attachment_ids = null)
    {
        $db = $this->getDbo();
        if(!empty($attachment_ids))
            $query = 'select fu.* from #__emundus_uploads as fu where fu.fnum in ("'.implode('","', $fnums).'") and fu.attachment_id in ("'.implode('","', $attachment_ids).'") order by fu.fnum';
        else
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
    public function getAssessorsByFnums($fnums,$column = 'uname')
    {
        $query = 'select cc.fnum,  GROUP_CONCAT( DISTINCT u.name ) as uname, GROUP_CONCAT( DISTINCT u.id ) as uids
                  from #__emundus_campaign_candidature as cc
                  left join #__emundus_users_assoc as eua on eua.fnum = cc.fnum
                  left join #__users as u on u.id = eua.user_id
                  where cc.fnum in ("'.implode('","', $fnums).'") group by cc.fnum';
        try
        {
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssocList('fnum', $column);
        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    /**
     * @param $user
     * @return array
     * get list of programmes for associated files
     */
    public function getAssociatedProgrammes($user)
    {
        $query = 'select DISTINCT sc.training
                  from #__emundus_users_assoc as ua
                  LEFT JOIN #__emundus_campaign_candidature as cc ON cc.fnum=ua.fnum
                  left join #__emundus_setup_campaigns as sc on sc.id = cc.campaign_id
                  where ua.user_id='.$user;
        try
        {
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadColumn();
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
        $h_files = new EmundusHelperFiles;
        return $h_files->getMenuList($params);
    }

    /**
     * @return mixed
     */
    public function getActionsACL()
    {
        $h_files = new EmundusHelperFiles;
        return $h_files->getMenuActions();
    }

    /*
    *   Get evaluation Fabrik formid from fnum
    *   @param fnum     fnum to evaluate
    *   @return int     Fabrik formid
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

    /*
    *   Get Decision Fabrik formid from fnum
    *   @param fnum     fnum to evaluate
    *   @return int     Fabrik formid
    */
    /**
     * @param $fnum
     * @return bool|mixed
     */
    public function getDecisionFormidByFnum($fnum)
    {
        try
        {
            $db = $this->getDbo();
            $query = "SELECT form_id
                        FROM `#__fabrik_formgroup`
                        WHERE group_id IN (
                            SELECT esp.fabrik_decision_group_id
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

    /*
    *   Get admission Fabrik formid from fnum
    *   @param fnum     fnum to evaluate
    *   @return int     Fabrik formid
    */
    /**
     * @param $fnum
     * @return bool|mixed
     */
    public function getAdmissionFormidByFnum($fnum) {
        try {

            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select($db->qn('esp.fabrik_applicant_admission_group_id'))
                ->from($db->qn('#__emundus_campaign_candidature', 'ecc'))
                ->leftJoin($db->qn('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->qn('esc.id') . ' = ' . $db->qn('ecc.campaign_id'))
                ->leftJoin($db->qn('#__emundus_setup_programmes', 'esp') . ' ON ' . $db->qn('esp.code') . ' = ' . $db->qn('esc.training'))
                ->where($db->qn('ecc.fnum') . ' LIKE ' . $db->quote($fnum));

            $db->setQuery($query);

            $groups = $db->loadColumn();

            $query
                ->clear()
                ->select($db->qn('form_id'))
                ->from($db->qn('#__fabrik_formgroup'))
                ->where($db->qn('group_id') . ' IN (' . implode(',', $groups) . ')')
                ->order('find_in_set( group_id, " '. implode(", ", $groups) .'")');

            $db->setQuery($query);
            $res = $db->loadColumn();
            return $res;

        } catch (Exception $e) {
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
    public function getAccessorByFnums($fnums) {
        $query = "SELECT jecc.fnum, jesg.id, jesg.label, jesg.class FROM #__emundus_campaign_candidature as jecc
                  LEFT JOIN #__emundus_setup_campaigns as jesc on jesc.id = jecc.campaign_id
                  LEFT JOIN #__emundus_setup_programmes as jesp on jesp.code = jesc.training
                  LEFT JOIN #__emundus_setup_groups_repeat_course as jesgrc on jesgrc.course = jesp.code
                  LEFT JOIN #__emundus_setup_groups as jesg on jesg.id = jesgrc.parent_id
                  LEFT JOIN #__emundus_acl as jea on jea.group_id = jesg.id
                  WHERE jea.action_id = 1 and jea.r = 1 and jecc.fnum in ('".implode("','", $fnums)."')
                  GROUP BY jecc.fnum";
        try {
            $db = $this->getDbo();
            $db->setQuery($query);
            $res = $db->loadAssocList();
            $access = array();
            foreach ($res as $r) {
                $assocTagcampaign = '<span class="label '.$r['class'].'" id="'.$r['id'].'">'.$r['label'].'</span>';
                $access[$r['fnum']] = $assocTagcampaign;
            }

            $query = "SELECT jega.fnum, jesg.label, jesg.class FROM #__emundus_group_assoc as jega
                      LEFT JOIN #__emundus_setup_groups as jesg on jesg.id = jega.group_id
                      where jega.action_id = 1 and jega.r = 1  and jega.fnum in ('".implode("','", $fnums)."')";
            $db = $this->getDbo();
            $db->setQuery($query);
            $res = $db->loadAssocList();

            foreach ($res as $r) {
                $assocTaggroup = '<span class="label '.$r['class'].'">'.$r['label'].'</span>';
                if (isset($access[$r['fnum']])) {
                    $access[$r['fnum']] .= ''.$assocTaggroup;
                } else {
                    $access[$r['fnum']] .= $assocTaggroup;
                }
            }

            $query = $db->getQuery(true);
            $query->select([$db->quoteName('jeua.fnum'), $db->quoteName('ju.name', 'uname'), $db->quoteName('jesp.class')])
                ->from($db->quoteName('#__emundus_users_assoc', 'jeua'))
                ->leftJoin($db->quoteName('#__users', 'ju').' ON '.$db->quoteName('ju.id').' = '.$db->quoteName('jeua.user_id'))
                ->leftJoin($db->quoteName('#__emundus_users', 'jeu').' ON '.$db->quoteName('ju.id').' = '.$db->quoteName('jeu.user_id'))
                ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'jesp').' ON '.$db->quoteName('jeu.profile').' = '.$db->quoteName('jesp.id'))
                ->where($db->quoteName('jeua.action_id').' = 1 AND '.$db->quoteName('jeua.r').' = 1 AND '.$db->quoteName('jeua.fnum').' IN ("'.implode('","', $fnums).'")');
            $db = $this->getDbo();
            $db->setQuery($query);
            $res = $db->loadAssocList();
            foreach ($res as $r) {
                if (isset($access[$r['fnum']])) {
                    $access[$r['fnum']] .= '<span class="label '.$r['class'].'"><span class=\'glyphicon glyphicon-user\'></span> '.$r['uname'].'</span>';
                } else {
                    $access[$r['fnum']] = '<span class="label '.$r['class'].'"><span class=\'glyphicon glyphicon-user\'></span> '.$r['uname'].'</span>';
                }
            }
            return $access;
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus.email');
            return false;
        }
    }

    /**
     * Gets the associated groups, users, or both, for an array of fnums. Used for XLS exports.
     *
     * @param         $fnums
     * @param   bool  $groups Should we get associated groups ?
     * @param   bool  $users
     *
     * @return array|bool
     */
    public function getAssocByFnums($fnums, $groups = true, $users = true) {

        $access = [];
        $db = $this->getDbo();

        if ($groups) {

            $query = "SELECT jecc.fnum, group_concat(jesg.label) AS label
					  FROM #__emundus_campaign_candidature as jecc
	                  LEFT JOIN #__emundus_setup_campaigns as jesc on jesc.id = jecc.campaign_id
	                  LEFT JOIN #__emundus_setup_programmes as jesp on jesp.code = jesc.training
	                  LEFT JOIN #__emundus_setup_groups_repeat_course as jesgrc on jesgrc.course = jesp.code
	                  LEFT JOIN #__emundus_setup_groups as jesg on jesg.id = jesgrc.parent_id
	                  LEFT JOIN #__emundus_acl as jea on jea.group_id = jesg.id
	                  WHERE jea.action_id = 1 and jea.r = 1 and jecc.fnum in ('".implode("','", $fnums)."')
	                  GROUP BY jecc.fnum";
            try {
                $db->setQuery($query);
                $access = $db->loadAssocList('fnum', 'label');
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                return false;
            }

            $query = "SELECT jega.fnum, group_concat(jesg.label) AS label
					  FROM #__emundus_group_assoc as jega
                      LEFT JOIN #__emundus_setup_groups as jesg on jesg.id = jega.group_id
                      WHERE jega.action_id = 1 and jega.r = 1  and jega.fnum in ('".implode("','", $fnums)."')
                      GROUP BY jega.fnum ";

            try {
                $db->setQuery($query);
                $res = $db->loadAssocList('fnum', 'label');
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                return false;
            }

            foreach ($res as $k => $r) {
                if (isset($access[$k])) {
                    $access[$k] .= ','.$r;
                } else {
                    $access[$k] = $r;
                }
            }
        }


        if ($users) {

            $query = $db->getQuery(true);
            $query->select([$db->quoteName('jeua.fnum'), 'group_concat('.$db->quoteName('ju.name').') AS name'])
                ->from($db->quoteName('#__emundus_users_assoc', 'jeua'))
                ->leftJoin($db->quoteName('#__users', 'ju').' ON '.$db->quoteName('ju.id').' = '.$db->quoteName('jeua.user_id'))
                ->leftJoin($db->quoteName('#__emundus_users', 'jeu').' ON '.$db->quoteName('ju.id').' = '.$db->quoteName('jeu.user_id'))
                ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'jesp').' ON '.$db->quoteName('jeu.profile').' = '.$db->quoteName('jesp.id'))
                ->where($db->quoteName('jeua.action_id').' = 1 AND '.$db->quoteName('jeua.r').' = 1 AND '.$db->quoteName('jeua.fnum').' IN ("'.implode('","', $fnums).'")')
                ->group($db->quoteName('jeua.fnum'));

            try {
                $db->setQuery($query);
                $res = $db->loadAssocList('fnum', 'name');
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                return false;
            }


            foreach ($res as $k => $r) {
                if (isset($access[$k])) {
                    $access[$k] .= ','.$r;
                } else {
                    $access[$k] = $r;
                }
            }
        }

        return $access;
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

    public function getTagsByIdFnumUser($tid, $fnum, $user_id)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT * FROM #__emundus_tag_assoc 
                    WHERE id_tag = '.$tid.' AND fnum LIKE "'.$fnum.'" AND user_id = '.$user_id;
        try
        {
            $dbo->setQuery($query);
            $res = $dbo->loadAssocList();
            if(count($res) > 0)
                return true;
            else
                return false;
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
    public function getProgByFnums($fnums) {
        $dbo = $this->getDbo();
        try {
            $query = 'select  jesp.code, jesp.label  from #__emundus_campaign_candidature as jecc
                        left join #__emundus_setup_campaigns as jesc on jesc.id = jecc.campaign_id
                        left join #__emundus_setup_programmes as jesp on jesp.code like jesc.training
                        left join #__emundus_setup_letters_repeat_training as jeslrt on jeslrt.training like jesp.code
                        where jecc.fnum in ("'.implode('","', $fnums).'") and jeslrt.parent_id IS NOT NULL  group by jesp.code order by jesp.code';
            $dbo->setQuery($query);
            return $dbo->loadAssocList('code', 'label');
        } catch(Exception $e) {
            return $e;
        }
    }

    /**
     * @param $code
     * @return Exception|mixed|Exception
     */
    public function getDocsByProg($code) {
        $dbo = $this->getDbo();
        try {
            $query = 'select jesl.title, jesl.template_type, jesl.id as file_id 
                        from #__emundus_setup_letters as jesl
                        left join #__emundus_setup_letters_repeat_training as jeslrt on jeslrt.parent_id = jesl.id
                        where jeslrt.training = '.$dbo->quote($code).' ORDER BY jesl.title';
            $dbo->setQuery($query);
            return $dbo->loadAssocList();
        } catch(Exception $e) {
            return $e;
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAttachmentInfos($id) {
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
    public function addAttachment($fnum, $name, $uid, $cid, $attachment_id, $desc, $canSee = 0) {
        $dbo = $this->getDbo();
        $query = "insert into jos_emundus_uploads (user_id, fnum, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id) values ({$uid}, {$dbo->quote($fnum)}, {$attachment_id}, {$dbo->quote($name)}, {$dbo->quote($desc)}, 0, {$canSee}, {$cid})";
        $dbo->setQuery($query);
        $dbo->execute();
        return $dbo->insertid();
    }

    /**
     * @param $code
     * @param $fnums
     * @return mixed
     */
    public function checkFnumsDoc($code, $fnums) {
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
    public function getAttachmentsById($ids) {
        $dbo = $this->getDbo();
        $query = 'select * from jos_emundus_uploads where id in ("'.implode('","', $ids).'")';
        try {
            $dbo->setQuery($query);
            return $dbo->loadAssocList();
        } catch(Exception $e) {
            throw $e;
        }
    }
    public function getSetupAttachmentsById($ids) {
        $dbo = $this->getDbo();
        $query = 'select * from jos_emundus_setup_attachments where id in ("'.implode('","', $ids).'")';
        try {
            $dbo->setQuery($query);
            return $dbo->loadAssocList();
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $idFabrik
     * @return mixed
     * @throws Exception
     */
    public function getValueFabrikByIds($idFabrik) {

        if (empty($idFabrik)) {
            return [];
        }

        $dbo = $this->getDbo();
        $select = "select jfe.id, jfe.name, jfe.plugin, jfe.params, jfg.params as group_params, jfg.id as group_id, jfl.db_table_name, jfj.table_join
                    from jos_fabrik_elements as jfe
                    left join #__fabrik_formgroup as jff on jff.group_id = jfe.group_id
                    left join #__fabrik_groups as jfg on jfg.id = jff.group_id
                    left join #__fabrik_forms as jff2 on jff2.id = jff.form_id
                    left join #__fabrik_lists as jfl on jfl.form_id = jff2.id
                    LEFT JOIN #__fabrik_joins AS jfj ON jfl.id = jfj.list_id AND jfg.id=jfj.group_id
                    where jfe.id in (".implode(',', $idFabrik).")";
        try {
            $dbo->setQuery($select);
            return $dbo->loadAssocList();
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Find all variables like ${var} or [var] in string.
     *
     * @param string $str
     * @param int $type type of bracket default CURLY else SQUARE
     * @return string[]
     */
    public function getVariables($str, $type='CURLY') {
        if($type == 'CURLY') {
            preg_match_all('/\$\{(.*?)}/i', $str, $matches);
        } elseif ($type == 'SQUARE') {
            preg_match_all('/\[(.*?)]/i', $str, $matches);
        } else {
            preg_match_all('/\{(.*?)}/i', $str, $matches);
        }
        return $matches[1];
    }



    /**
     * Return a date format from php to MySQL.
     *
     * @param string $date_format
     * @return string
     */
    public function dateFormatToMysql($date_format){
        $date_format = str_replace('D', '%D', $date_format);
        $date_format = str_replace('d', '%d', $date_format);
        $date_format = str_replace('M', '%M', $date_format);
        $date_format = str_replace('m', '%m', $date_format);
        $date_format = str_replace('Y', '%Y', $date_format);
        $date_format = str_replace('y', '%y', $date_format);
        $date_format = str_replace('H', '%H', $date_format);
        $date_format = str_replace('h', '%h', $date_format);
        $date_format = str_replace('I', '%I', $date_format);
        $date_format = str_replace('i', '%i', $date_format);
        $date_format = str_replace('S', '%S', $date_format);
        return str_replace('s', '%s', $date_format);
    }


    /**
     * @param         $elt
     * @param   null  $fnums
     * @param         $params
     * @param         $groupRepeat
     *
     * @return mixed
     * @throws Exception
     */
    public function getFabrikValueRepeat($elt, $fnums, $params = null, $groupRepeat) {

        if (!is_array($fnums)) {
            $fnums = [$fnums];
        }

        $tableName = $elt['db_table_name'];
        $tableJoin = $elt['table_join'];
        $name = $elt['name'];
        $plugin = $elt['plugin'];
        $isFnumsNull = ($fnums === null);
        $isDatabaseJoin = ($plugin === 'databasejoin');
        $isMulti = (@$params->database_join_display_type == "multilist" || @$params->database_join_display_type == "checkbox");
        $dbo = $this->getDbo();

        if ($plugin === 'date') {
            $date_form_format = $this->dateFormatToMysql($params->date_form_format);
            $query = 'select GROUP_CONCAT(DATE_FORMAT(t_repeat.' . $name.', '.$dbo->quote($date_form_format).')  SEPARATOR ", ") as val, t_origin.fnum ';
        } elseif ($isDatabaseJoin) {
            if ($groupRepeat) {
                $query = 'select GROUP_CONCAT(t_origin.' . $params->join_val_column . '  SEPARATOR ", ") as val, t_table.fnum ';
            } else {
                if ($isMulti) {
                    $query = 'select GROUP_CONCAT(t_origin.' . $params->join_val_column . '  SEPARATOR ", ") as val, t_elt.fnum ';
                } else {
                    $query = 'select t_origin.' . $params->join_val_column . ' as val, t_elt.fnum ';
                }
            }
        } else {
            $query = 'SELECT  GROUP_CONCAT(t_repeat.' . $name.'  SEPARATOR ", ") as val, t_origin.fnum ';
        }

        if ($isDatabaseJoin) {
            if ($groupRepeat) {
                $tableName2 = $tableJoin;
                if ($isMulti) {
                    $query .= ' FROM ' . $params->join_db_name . ' as  t_origin left join '.$tableName.'_repeat_'.$name .' as t_repeat on t_repeat.' . $name . " = t_origin.".$params->join_key_column . ' left join ' . $tableName2 . ' as t_elt on t_elt.id = t_repeat.parent_id left join '.$tableName.' as t_table on t_table.id = t_elt.parent_id ';
                } else {
                    $query .= ' FROM ' . $params->join_db_name . ' as  t_origin left join '.$tableName2.' as t_elt on t_elt.' . $name . " = t_origin.".$params->join_key_column." left join $tableName as t_table on t_table.id = t_elt.parent_id ";
                }
            } else {
                if ($isMulti) {
                    $query .= ' FROM ' . $params->join_db_name . ' as  t_origin left join '.$tableName.'_repeat_'.$name .' as t_repeat on t_repeat.' . $name . " = t_origin.".$params->join_key_column . ' left join ' . $tableName . ' as t_elt on t_elt.id = t_repeat.parent_id ';
                } else {
                    $query .= ' FROM ' . $params->join_db_name . ' as  t_origin left join '.$tableName.' as t_elt on t_elt.' . $name . " = t_origin.".$params->join_key_column;
                }
            }

        } else {
            $query .= ' FROM ' . $tableJoin . ' as t_repeat  left join '.$tableName.' as t_origin on t_origin.id = t_repeat.parent_id';
        }

        if ($isMulti || $isDatabaseJoin) {
            if ($groupRepeat) {
                $query .= ' where t_table.fnum in ("'.implode('","', $fnums).'") group by t_table.fnum';
            } else {
                $query .= ' where t_elt.fnum in ("'.implode('","', $fnums).'") group by t_elt.fnum';
            }
        } else {
            $query .= ' where t_origin.fnum in ("'.implode('","', $fnums).'") group by t_origin.fnum';
        }

        try{
            $dbo->setQuery($query);

            if (!$isFnumsNull) {
                $res = $dbo->loadAssocList('fnum');
            } else {
                $res = $dbo->loadAssocList();
            }

            return $res;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $fnums
     * @param $tableName
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function getFabrikValue($fnums, $tableName, $name, $dateFormat = null) {

        if (!is_array($fnums))
            $fnums = [$fnums];

        $dbo = $this->getDbo();
        if ($dateFormat !== null) {
            $dateFormat = $this->dateFormatToMysql($dateFormat);
            $query = "select fnum, DATE_FORMAT({$name}, ".$dbo->quote($dateFormat).") as val from {$tableName} where fnum in ('".implode("','", $fnums)."')";
        } else {
            $query = "select fnum, $dbo->quote({$name}) as val from {$tableName} where fnum in ('".implode("','", $fnums)."')";
        }

        try {
            $dbo->setQuery($query);
            return $dbo->loadAssocList('fnum');
        } catch(Exception $e) {
            throw $e;
        }
    }

    public function getStatus() {
        $db = JFactory::getDBO();
        $query = 'SELECT *  FROM #__emundus_setup_status ORDER BY ordering';
        $db->setQuery( $query );
        return $db->loadAssocList('step');
    }

    /**
     * @param $fnum
     * @return bool|mixed
     */
    public function deleteFile($fnum) {

        $dispatcher = JEventDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDeleteFile', $fnum);
        $dispatcher->trigger('callEventHandler', ['onBeforeDeleteFile', ['fnum' => $fnum]]);

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select($db->quoteName('filename'))
            ->from($db->quoteName('#__emundus_uploads'))
            ->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));
        $db->setQuery($query);
        try {
            $files = $db->loadColumn();
        } catch (Exception $e) {
            // Do not hard fail, delete file data anyways.
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }


        // Remove all files linked to the fnum.
        $user_id = (int)substr($fnum, -7);
        $dir = EMUNDUS_PATH_ABS.$user_id.DS;
        if ($dh = @opendir($dir)) {

            while (false !== ($obj = readdir($dh))) {
                if (in_array($obj, $files)) {
                    if (!unlink($dir.$obj)) {
                        JLog::add(JUri::getInstance().' :: Could not delete file -> '.$obj.' for fnum -> '.$fnum, JLog::ERROR, 'com_emundus');
                    }
                }
            }

            closedir($dh);
        }


        $query = 'DELETE FROM #__emundus_campaign_candidature
                    WHERE fnum like '.$db->Quote($fnum);

        try {

            $db->setQuery($query);
            $res = $db->execute();
            $dispatcher->trigger('onAfterDeleteFile', $fnum);
            $dispatcher->trigger('callEventHandler', ['onAfterDeleteFile', ['fnum' => $fnum]]);
            return $res;
        } catch(Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    /*
     * CCIRS functions
     * function to get all sessions linked to a program
     *
     */
    public function programSessions($program) {
        try {
            $db = JFactory::getDbo();

            $query = $db->getQuery(true);
            $query->select('DISTINCT(t.session_code) AS sc, t.*')
                ->from($db->quoteName('#__emundus_setup_programmes', 'p'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'c') . ' ON ' . $db->quoteName('c.training') . ' = ' . $db->quoteName('p.code'))
                ->leftJoin($db->quoteName('#__emundus_setup_teaching_unity', 't') . ' ON ' . $db->quoteName('t.session_code') . ' = ' . $db->quoteName('c.session_code'))
                ->where($db->quoteName('p.id') . ' = ' . $program .
                    ' AND ' . $db->quoteName('t.published') . ' = ' . 1 .
                    ' AND ' . $db->quoteName('t.date_end') . ' >= NOW()')
                ->order('date_start ASC');
            $db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getAppliedSessions($program) {
        try {
            $current_user = JFactory::getUser();
            $db = JFactory::getDbo();

            $query = $db->getQuery(true);
            $query->select('esc.session_code')
                ->from($db->quoteName('#__emundus_setup_campaigns', 'esc'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('ecc.campaign_id') . ' = ' . $db->quoteName('esc.id'))
                ->where($db->quoteName('esc.training') . ' LIKE ' . $db->quote($program). 'and' .$db->quoteName('ecc.applicant_id') . ' = ' . $current_user->id);

            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Gets the user's birthdate.
     *
     * @param null   $fnum The file number to get the birth date from.
     * @param string $format See php.net/date
     * @param bool   $age If true then we also return the current age.
     *
     * @return null
     */
    public function getBirthdate($fnum = null, $format = 'd-m-Y', $age = false) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('birth_date'))->from($db->quoteName('#__emundus_personal_detail'))->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));
        $db->setQuery($query);

        try {
            $datetime = new DateTime($db->loadResult());
            if (!$age) {
                $birthdate = $datetime->format($format);
            } else {

                $birthdate = new stdClass();
                $birthdate->date = $datetime->format($format);

                $now = new DateTime();
                $interval = $now->diff($datetime);
                $birthdate->age = $interval->y;
            }
        } catch (Exception $e) {
            return null;
        }

        return $birthdate;
    }

    public function getDocumentCategory() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($this->_db->quoteName('esa.*'))
            ->from($this->_db->quoteName('#__emundus_setup_attachments','esa'))
            ->order($this->_db->quoteName('esa.category').'ASC');

        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    public function getParamsCategory($idCategory) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('fe.params'))
            ->from($db->quoteName('#__fabrik_elements' , 'fe'))
            ->where($db->quoteName('fe.group_id') . ' = 47');

        $db->setQuery($query);
        $elements = $db->loadObjectList();

        foreach ($elements as $element){
            $params = json_decode($element->params);
        }

        return $params->sub_options->sub_labels[$idCategory];
    }

    /** Gets the category names for the different attachment types.
     *
     * @return mixed
     *
     * @since version
     */
    public function getAttachmentCategories() {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select($db->quoteName('fe.params'))
            ->from($db->quoteName('#__fabrik_elements' , 'fe'))
            ->where($db->quoteName('fe.published') . ' = 1 AND '.$db->quoteName('fe.group_id') . ' = 47 AND '.$db->quoteName('fe.name').' LIKE '.$db->quote('category'));
        $db->setQuery($query);
        $element = $db->loadColumn();

        $return = [];

        if (isset($element[0])) {
            $params = json_decode($element[0]);
            if (!empty($params->sub_options->sub_values)) {
                foreach ($params->sub_options->sub_values as $key => $value) {
                    $return[$value] = $params->sub_options->sub_labels[$key];
                }
            }
        }
        return $return;
    }

    public function selectCity($insee) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $conditions = $db->quoteName('insee_code') . ' LIKE ' . $db->quote($insee);

        $query->select($db->quoteName('name'))
            ->from($db->quoteName('#__emundus_french_cities'))
            ->where($conditions);

        $db->setQuery($query);
        return $db->loadResult();
    }

    public function selectNameCity($name) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $conditions = $db->quoteName('name') . ' LIKE ' . $db->quote($name);

        $query->select($db->quoteName('insee_code'))
            ->from($db->quoteName('#__emundus_french_cities'))
            ->where($conditions);

        $db->setQuery($query);
        return $db->loadResult();
    }

    public function selectMultiplePayment($fnum) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $conditions = $db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum);

        $query->select('multiple_payment, method_payment, sampling_mode')
            ->from($db->quoteName('#__emundus_declaration'))
            ->where($conditions);

        $db->setQuery($query);
        return $db->loadObject();
    }


    /**
     * @param $group_ids
     *
     * @return array|bool
     *
     * @since version
     */
    public function getAttachmentsAssignedToEmundusGroups($group_ids) {

        if (!is_array($group_ids)) {
            $group_ids = [$group_ids];
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $result = [];
        foreach ($group_ids as $group_id) {
            $query
                ->clear()
                ->select($db->quoteName('attachment_id_link'))
                ->from($db->quoteName('#__emundus_setup_groups_repeat_attachment_id_link'))
                ->where($db->quoteName('parent_id').' = '.$group_id);
            $db->setQuery($query);

            try {
                $attachments = $db->loadColumn();

                // In the case of a group having no assigned Fabrik groups, it can get them all.
                if (empty($attachments)) {
                    return true;
                }

                $result = array_merge($result, $attachments);
            } catch (Exception $e) {
                return false;
            }
        }

        if (empty($result)) {
            return true;
        } else {
            return array_keys(array_flip($result));
        }
    }

    public function getFormProgress($fnums) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $fnums_string = implode(',',$fnums);

        $query->select('fnum,form_progress')
            ->from ($db->quoteName('#__emundus_campaign_candidature'))
            ->where($db->quoteName('fnum') . ' IN (' . $fnums_string . ')');
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    public function getAttachmentProgress($fnums) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $fnums_string = implode(',',$fnums);

        $query->select('fnum,attachment_progress')
            ->from ($db->quoteName('#__emundus_campaign_candidature'))
            ->where($db->quoteName('fnum') . ' IN (' . $fnums_string . ')');
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    public function getTagsAssocStatus($status){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $conditions = $db->quoteName('ss.step') . ' = ' . $db->quote($status);

        $query->select('ssrt.tags')
            ->from($db->quoteName('#__emundus_setup_status_repeat_tags', 'ssrt'))
            ->leftJoin($db->quoteName('#__emundus_setup_status', 'ss') . ' ON ' . $db->quoteName('ss.id') . ' = ' . $db->quoteName('ssrt.parent_id'))
            ->where($conditions);

        $db->setQuery($query);

        try{
            return $db->loadColumn();
        }
        catch (Exception $e){
            JLog::add('component/com_emundus/models/files | Error when get tags by status ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }
    }

    public function checkIfSomeoneElseIsEditing($fnum)
    {
        $result = false;
        $user = JFactory::getUser();
        $config = JComponentHelper::getParams('com_emundus');
        $editing_time = $config->get('alert_editing_time', 2);

        $actions = array(1,4,5,10,11,12,13,14);
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('DISTINCT ju.id, ju.name')
            ->from('#__users as ju')
            ->leftJoin('#__emundus_logs as jel ON ju.id = jel.user_id_from')
            ->where($db->quoteName('jel.fnum_to') . ' = ' . $db->quote($fnum))
            ->andWhere('action_id IN (' . implode(',', $actions) . ')')
            ->andWhere('jel.timestamp > ' . $db->quote(date('Y-m-d H:i:s', strtotime('-' . $editing_time . ' minutes'))))
            ->andWhere('jel.user_id_from != ' . $user->id);

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/files | Error when check if someone else is editing ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }

        return $result;
    }

    public function getStatusByStep($step)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('id')
                ->from($db->quoteName('#__emundus_setup_status', 'jess'))
                ->where($db->quoteName('jess.step') . ' = ' . $step);

            $db->setQuery($query);
            $status_id = $db->loadResult();

            return $this->getStatusByID($status_id);
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus/models/files | Error when get status by step ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }
    }

    public function getAllLogs()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->clear()->select('*')->from($db->quoteName('#__emundus_setup_actions', 'jesa'))->order('jesa.id ASC');

        try
        {
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            JLog::add('component/com_emundus/models/files | Error when get all logs' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }
    }
}
