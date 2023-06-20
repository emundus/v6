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

	public $use_module_filters = false;

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
		$this->use_module_filters = boolval($menu_params->get('em_use_module_for_filters', false));

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

	            $already_joined_tables = [
		            'jecc' => 'jos_emundus_campaign_candidature',
		            'ss' => 'jos_emundus_setup_status',
		            'esc' => 'jos_emundus_setup_campaigns',
		            'sp' => 'jos_emundus_setup_programmes',
		            'u' => 'jos_users',
		            'eu' => 'jos_emundus_users',
		            'eta' => 'jos_emundus_tag_assoc'
	            ];
				foreach ($already_joined_tables as $alias => $table) {
					if ($def_elmt->tab_name === $table) {
						$def_elmt->tab_name = $alias;
					}

					if ($def_elmt->join_from_table === $table) {
						$def_elmt->join_from_table = $alias;
					}
				}

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
                                          where '.$def_elmt->table_join .'.' . $def_elmt->table_join_key .  '='.$def_elmt->join_from_table.'.id' . '
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
                        } else if( $attribs->database_join_display_type == 'multilist' ) {
	                        $t = $def_elmt->tab_name.'_repeat_'.$def_elmt->element_name;
	                        $query = '(
                                select DISTINCT '.$column.'
                                from '.$attribs->join_db_name.'
                                where `'.$attribs->join_db_name.'`.`'.$attribs->join_key_column.'`=`' . $t . '`.`' . $def_elmt->element_name . '`
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
                elseif ($def_elmt->element_plugin == 'dropdown' || $def_elmt->element_plugin == 'checkbox') {
                    if (@$group_params->repeat_group_button == 1) {
                        $element_attribs = json_decode($def_elmt->element_attribs);
                        $select = $def_elmt->tab_name . '.' . $def_elmt->element_name;
                        foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                            $select = 'REGEXP_REPLACE(' . $select . ', "\\\b' . $value . '\\\b", "' . JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
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
                } elseif ($def_elmt->element_plugin == 'radiobutton') {
	                if (!empty($group_params->repeat_group_button) && $group_params->repeat_group_button == 1) {
		                $element_attribs = json_decode($def_elmt->element_attribs);
		                $select = $def_elmt->tab_name . '.' . $def_elmt->element_name;
		                foreach ($element_attribs->sub_options->sub_values as $key => $value) {
			                $select = 'REGEXP_REPLACE(' . $select . ', "\\\b' . $value . '\\\b", "' . JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
		                }
		                $select = str_replace($def_elmt->tab_name . '.' . $def_elmt->element_name,'GROUP_CONCAT('.$def_elmt->table_join.'.' . $def_elmt->element_name.' SEPARATOR ", ")',$select);
		                $this->_elements_default[] = '(
                                    SELECT ' . $select . '
                                    FROM '.$def_elmt->table_join.'
                                    WHERE '.$def_elmt->table_join.'.parent_id = '.$def_elmt->tab_name.'.id
                                  ) AS `'.$def_elmt->table_join.'___' . $def_elmt->element_name.'`';
	                } else {
		                $element_attribs = json_decode($def_elmt->element_attribs);

						$element_replacement = $def_elmt->tab_name . '___' . $def_elmt->element_name;
		                $select = $def_elmt->tab_name . '.' . $def_elmt->element_name . ' AS ' . $db->quote($element_replacement) . ', CASE ';
		                foreach ($element_attribs->sub_options->sub_values as $key => $value) {
			                $select .= ' WHEN ' . $def_elmt->tab_name . '.' . $def_elmt->element_name . ' = ' . $db->quote($value) . ' THEN ' .  $db->quote(JText::_(addslashes($element_attribs->sub_options->sub_labels[$key]))) ;
		                }
						$select .= ' ELSE ' . $def_elmt->tab_name . '.' . $def_elmt->element_name;
						$select .= ' END AS ' . $db->quote($element_replacement);

		                $this->_elements_default[] = $select;
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
        if (in_array('unread_messages', $em_other_columns)) {
            $this->_elements_default[] = ' COUNT(`m`.`message_id`) AS `unread_messages` ';
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
        $order = ' ORDER BY jecc.date_submitted DESC, jecc.date_time DESC';
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

        $can_be_ordering[] = 'jecc.id';
        $can_be_ordering[] = 'jecc.fnum';
        $can_be_ordering[] = 'jecc.status';
        $can_be_ordering[] = 'jecc.form_progress';
        $can_be_ordering[] = 'jecc.attachment_progress';
        $can_be_ordering[] = 'form_progress';
        $can_be_ordering[] = 'attachment_progress';
        $can_be_ordering[] = 'fnum';
        $can_be_ordering[] = 'status';
        $can_be_ordering[] = 'name';
        $can_be_ordering[] = 'eta.id_tag';

        if (in_array('unread_messages', $em_other_columns)) {
            $can_be_ordering[] = 'unread_messages';
        }
        $campaign_candidature_columns = [
            'form_progress',
            'attachment_progress',
            'status'
        ];


        if (in_array('overall', $em_other_columns)) {
            $can_be_ordering[] = 'overall';
        }

        if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)) {
            if(in_array($filter_order, $campaign_candidature_columns)){
                $filter_order = 'jecc.' . $filter_order;
            }
            $order = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;

            if (strpos($filter_order, 'date_submitted') === false) {
                $order .= ', jecc.date_submitted DESC ';
            }
            if (strpos($filter_order, 'date_time') === false) {
                $order .= ', jecc.date_time DESC ';
            }
        }

        return $order;
    }

    /**
     * @param array $multi_array
     * @param $sort_key
     * @param int $sort
     * @return array|int
     */
    public function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC) {
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
    private function _buildWhere($already_joined_tables = array()) {
        $h_files = new EmundusHelperFiles();

		if ($this->use_module_filters) {
			return $h_files->_moduleBuildWhere($already_joined_tables, 'files', array(
				'fnum_assoc' => $this->fnum_assoc,
				'code' => $this->code
			));
		} else {
			return $h_files->_buildWhere($already_joined_tables, 'files', array(
				'fnum_assoc' => $this->fnum_assoc,
				'code' => $this->code
			));
		}
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
		$user_files = [];

        $app = JFactory::getApplication();
        $current_menu = $app->getMenu()->getActive();
        if (!empty($current_menu)) {
            $menu_params = $current_menu->params;
            $em_other_columns = explode(',', $menu_params->get('em_other_columns'));
        } else {
            $em_other_columns = array();
        }

        $dbo = $this->getDbo();
        $query = 'select jecc.fnum, ss.step, ss.value as status, ss.class as status_class, concat(upper(trim(eu.lastname))," ",eu.firstname) AS name, jecc.applicant_id, jecc.campaign_id ';

        // prevent double left join on query
		$already_joined_tables = [
			'jecc' => 'jos_emundus_campaign_candidature',
			'ss' => 'jos_emundus_setup_status',
			'esc' => 'jos_emundus_setup_campaigns',
			'sp' => 'jos_emundus_setup_programmes',
			'u' => 'jos_users',
			'eu' => 'jos_emundus_users',
			'eta' => 'jos_emundus_tag_assoc'
		];

        if (in_array('overall', $em_other_columns)) {
	        $already_joined_tables['ee'] = 'jos_emundus_evaluations';
        }

        if (in_array('unread_messages', $em_other_columns)) {
	        $already_joined_tables['ec'] = 'jos_emundus_chatroom';
	        $already_joined_tables['m'] = 'jos_messages';
        }

        if (!empty($this->_elements)) {
	        $h_files = new EmundusHelperFiles();
	        $leftJoin = '';

	        foreach ($this->_elements as $elt) {
				$table_to_join = !empty($elt->table_join) ? $elt->table_join : $elt->tab_name;
				$already_join_alias = array_keys($already_joined_tables);

                if (!(in_array($table_to_join, $already_joined_tables)) && !(in_array($table_to_join, $already_join_alias, true))) {
	                if ($h_files->isTableLinkedToCampaignCandidature($table_to_join)) {
						$leftJoin .= 'LEFT JOIN ' . $table_to_join .  ' ON '. $table_to_join .'.fnum = jecc.fnum ';
						$already_joined_tables[] = $table_to_join;
					} else {
						$joined = false;
						$query_find_join = $dbo->getQuery(true);
						foreach ($already_joined_tables as $already_join_alias => $already_joined_table_name) {
							$query_find_join->clear()
								->select('*')
								->from('#__fabrik_joins')
								->where('table_join = ' . $dbo->quote($already_joined_table_name))
								->andWhere('join_from_table = ' . $dbo->quote($table_to_join))
								->andWhere('table_key = ' . $dbo->quote('id'))
								->andWhere('list_id = ' . $dbo->quote($elt->table_list_id));

							$dbo->setQuery($query_find_join);
							$join_informations = $dbo->loadAssoc();

							if (!empty($join_informations)) {
								$already_joined_tables[] = $table_to_join;

								$leftJoin .= ' LEFT JOIN ' . $dbo->quoteName($join_informations['join_from_table']) . ' ON ' . $dbo->quoteName($join_informations['join_from_table'] . '.' . $join_informations['table_key']) . ' = ' . $dbo->quoteName($already_join_alias . '.' . $join_informations['table_join_key']);
								$joined = true;
								break;
							}
						}

						if (!$joined) {
							$element_joins = $h_files->findJoinsBetweenTablesRecursively('jos_emundus_campaign_candidature', $table_to_join);

							if (!empty($element_joins)) {
								$leftJoin .= $h_files->writeJoins($element_joins, $already_joined_tables);
							}
						}
					}
                }
            }
        }

        if (!empty($this->_elements_default)) {
            $query .= ', '.implode(',', $this->_elements_default);
        }

        $query .= ' FROM #__emundus_campaign_candidature as jecc
                    LEFT JOIN #__emundus_setup_status as ss on ss.step = jecc.status
                    LEFT JOIN #__emundus_setup_campaigns as esc on esc.id = jecc.campaign_id
                    LEFT JOIN #__emundus_setup_programmes as sp on sp.code = esc.training
                    LEFT JOIN #__users as u on u.id = jecc.applicant_id
                    LEFT JOIN #__emundus_users as eu on eu.user_id = jecc.applicant_id
                    LEFT JOIN #__emundus_tag_assoc as eta on eta.fnum=jecc.fnum ';

        if (in_array('overall', $em_other_columns)) {
            $query .= ' LEFT JOIN #__emundus_evaluations as ee on ee.fnum = jecc.fnum ';
        }

        if (in_array('unread_messages', $em_other_columns)) {
            $query.= ' LEFT JOIN #__emundus_chatroom as ec on ec.fnum = jecc.fnum
            LEFT JOIN #__messages as m on m.page = ec.id AND m.state = 0 AND m.page IS NOT NULL ';
        }

	    $q = $this->_buildWhere($already_joined_tables);
        if (!empty($leftJoin)) {
            $query .= $leftJoin;
        }
        $query .= $q['join'];
        $query .= ' WHERE u.block=0 ' . $q['q'];

        $query .= ' GROUP BY jecc.fnum';

        $query .=  $this->_buildContentOrderBy();

		try {
	        $dbo->setQuery($query);
	        $this->_applicants = $dbo->loadAssocList();

            if ($limit > 0) {
                $query .= " limit $limitStart, $limit ";
            }

            $dbo->setQuery($query);
	        $user_files = $dbo->loadAssocList();
        } catch(Exception $e) {
			$app->enqueueMessage(JText::_('COM_EMUNDUS_GET_ALL_FILES_ERROR') . ' ' . $e->getMessage(), 'error');
            JLog::add(JUri::getInstance().' :: USER ID : '. JFactory::getUser()->id.' ' . $e->getMessage() . ' -> '. $query, JLog::ERROR, 'com_emundus.error');
        }

		return $user_files;
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
        $pageNavigation .= "<li><a href='#em-data' id='" . ($this->getPagination()->pagesCurrent - 1) . "'><span class='material-icons'>navigate_before</span></a></li>";
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
        $pageNavigation .= "<li><a href='#em-data' id='" . ($this->getPagination()->pagesCurrent + 1) . "'><span class='material-icons'>navigate_next</span></a></li></ul></div>";

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

	        foreach ($fnums as $fnum) {
		        foreach ($users as $user) {

			        $query->clear()
				        ->select('name')
				        ->from($db->quoteName('#__users'))
				        ->where($db->quoteName('id') . ' = ' . $user);
			        $db->setQuery($query);
			        $user_name = $db->loadResult();

			        $logsStd = new stdClass();
			        $logsStd->details = $user_name;
			        $logger[] = $logsStd;

			        if (!empty($logger)) {
				        $logsParams = array('created' => array_unique($logger, SORT_REGULAR));
				        EmundusModelLogs::log(JFactory::getUser()->id, (int) $user, $fnum, 11, 'c', 'COM_EMUNDUS_ACCESS_ACCESS_FILE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
			        }
		        }
	        }

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
		$tags = [];

	    $db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__emundus_setup_action_tag'))
			->order('label');

        try {
            $db->setQuery($query);
	        $tags =  $db->loadAssocList();
        } catch(Exception $e) {
            JLog::add('Failed to get all tags ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
        }

		return $tags;
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
     * @return bool
     */
    public function tagFile($fnums, $tags, $user = null) {
        $tagged = false;

        if (!empty($fnums) && !empty($tags)) {
            JPluginHelper::importPlugin('emundus');
            \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onBeforeTagAdd', ['fnums' => $fnums, 'tag' => $tags]]);

            try {
                $db = $this->getDbo();
                if (empty($user)) {
                    $user = JFactory::getUser()->id;
                }

                $query_associated_tags = $db->getQuery(true);
                $query ="insert into #__emundus_tag_assoc (fnum, id_tag, user_id) VALUES ";

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
                        if (!in_array($tag, $tags_already_associated)) {
                            $query .= '("' . $fnum . '", ' . $tag . ',' . $user . '),';
                            $query_log = 'SELECT label
                                FROM #__emundus_setup_action_tag
                                WHERE id =' . $tag;
                            $db->setQuery($query_log);
                            $log_tag = $db->loadResult();

                            $logsStd = new stdClass();
                            $logsStd->details = $log_tag;
                            $logger[] = $logsStd;
                        }
                    }

                    if (!empty($logger)) {
                        $logsParams = array('created' => array_unique($logger, SORT_REGULAR));
                        EmundusModelLogs::log($user, (int)substr($fnum, -7), $fnum, 14, 'c', 'COM_EMUNDUS_ACCESS_TAGS_CREATE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
                    }
                }

                $query = substr_replace($query, ';', -1);
                $db->setQuery($query);
                $tagged = $db->execute();
            } catch (Exception $e) {
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.error');
            }

            \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onAfterTagAdd', ['fnums' => $fnums, 'tag' => $tags, 'tagged' => $tagged]]);
        }

        return $tagged;
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
        $res = false;

        if (!empty($fnums) && isset($state)) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $fnums = is_array($fnums) ? $fnums : [$fnums];

            try {
                $query->select($db->quoteName('profile'))
                    ->from($db->quoteName('#__emundus_setup_status'))
                    ->where($db->quoteName('step') . ' = ' . $state);
                $db->setQuery($query);
                $profile = $db->loadResult();

                $dispatcher = JEventDispatcher::getInstance();
                $dispatcher->trigger('onBeforeMultipleStatusChange', [$fnums, $state]);
                $trigger = $dispatcher->trigger('callEventHandler', ['onBeforeMultipleStatusChange', ['fnums' => $fnums, 'state' => $state]]);
                foreach($trigger as $responses) {
                    foreach($responses as $response) {
                        if (!empty($response) && isset($response['status']) && $response['status'] === false) {
                            return $response;
                        }
                    }
                }

                $all_status = $this->getStatus();
                $user = JFactory::getUser();
                $user_id = !empty($user->id) ? $user->id : 62;

                foreach ($fnums as $fnum) {
                    $query->clear()
                        ->select('status')
                        ->from('#__emundus_campaign_candidature')
                        ->where('fnum = ' . $db->quote($fnum));

                    $db->setQuery($query);
                    $old_status_step = $db->loadResult();

                    $dispatcher->trigger('onBeforeStatusChange', [$fnum, $state]);
                    $trigger = $dispatcher->trigger('callEventHandler', ['onBeforeStatusChange', ['fnum' => $fnum, 'state' => $state, 'old_state' => $old_status_step]]);
                    foreach($trigger as $responses) {
                        foreach($responses as $response) {
                            if (!empty($response) && isset($response['status']) && $response['status'] === false) {
                                return $response;
                            }
                        }
                    }

                    $query->clear()
                        ->update($db->quoteName('#__emundus_campaign_candidature'))
                        ->set($db->quoteName('status').' = '. $state)
                        ->where($db->quoteName('fnum').' LIKE '. $db->Quote($fnum));

                    $db->setQuery($query);
                    $res = $db->execute();

                    $old_status_lbl = $all_status[$old_status_step]['value'];
                    if ($res) {
                        $logs_params = ['updated' => [['old' => $old_status_lbl, 'new' => $all_status[$state]['value'], 'old_id' => $old_status_step, 'new_id' => $state]]];
                        EmundusModelLogs::log($user_id, (int)substr($fnum, -7), $fnum, 13, 'u', 'COM_EMUNDUS_ACCESS_STATUS_UPDATE', json_encode($logs_params, JSON_UNESCAPED_UNICODE));
                    } else {
                        $logs_params = ['updated' => [['old' => $old_status_lbl, 'new' => $all_status[$state]['value'], 'old_id' => $old_status_step, 'new_id' => $state]]];
                        EmundusModelLogs::log($user_id, (int)substr($fnum, -7), $fnum, 13, 'u', 'COM_EMUNDUS_ACCESS_STATUS_UPDATE_FAILED', json_encode($logs_params, JSON_UNESCAPED_UNICODE));
                    }

                    $dispatcher->trigger('onAfterStatusChange', [$fnum, $state]);
                    $dispatcher->trigger('callEventHandler', ['onAfterStatusChange', ['fnum' => $fnum, 'state' => $state, 'old_state' => $old_status_step]]);

                    if (!empty($profile)) {
                        $query->clear()
                            ->update($db->quoteName('#__emundus_users'))
                            ->set($db->quoteName('profile').' = '.$profile)
                            ->where($db->quoteName('user_id').' = '.substr($fnum, -7));
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            }

            if ($res) {
                $res = [
                    'status' => true,
                    'msg' => $this->sendEmailAfterUpdateState($fnums, $state)
                ];
            }
        }

        return $res;
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


    /** Gets applicant_id from fnum
     * @param String $fnum
     * @return int
     */
    public function getApplicantIdByFnum($fnum)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try
        {
            $query->select($db->quoteName('applicant_id'))
                ->from($db->quoteName('#__emundus_campaign_candidature'))
                ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
            $db->setQuery($query);
            return $db->loadResult();
        }
        catch (Exception $e)
        {
            return 0;
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
                    $if = array();
                    $endif = '';

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
                                    $select = 'REGEXP_REPLACE(' . $select . ', "\\\b' . $value . '\\\b", "' . JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
                                } else {
                                    $select .= ',REGEXP_REPLACE(' . $select . ', "\\\b' . $value . '\\\b", "' . JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
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
                            if($raw != 1){
                                $element_attribs = json_decode($elt->element_attribs);
                                foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                                    $if[] = 'IF(' . $select . '="' . $value . '","' . JText::_($element_attribs->sub_options->sub_labels[$key]) . '"';
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
                            if($raw != 1){
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


                    if ($elt->element_plugin == 'dropdown' || $elt->element_plugin == 'radiobutton'|| $elt->element_plugin == 'checkbox') {
                        if($raw == 1){
                            $select = 'REPLACE(`'.$tableAlias[$elt->tab_name] . '`.`' . $elt->element_name.'`, "\t", "" )';
                        }
                        else{
                            $element_attribs = json_decode($elt->element_attribs);
                            if($elt->element_plugin == 'checkbox'){
                                $if = '';
                            }
                            foreach ($element_attribs->sub_options->sub_values as $key => $value) {
                                if($elt->element_plugin == 'checkbox'){
                                    if(empty($if)) {
                                        $if = 'REGEXP_REPLACE(' . $select . ', "\\\b' . $value . '\\\b", "' . JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
                                    } else {
                                        $if = 'REGEXP_REPLACE(' . $if . ', "\\\b' . $value . '\\\b", "' . JText::_(addslashes($element_attribs->sub_options->sub_labels[$key])) . '")';
                                    }
                                } else {
                                    $if[] = 'IF(' . $select . '="' . $value . '","' . $element_attribs->sub_options->sub_labels[$key] . '"';
                                    $endif .= ')';
                                }

                            }
                            if(is_array($if)) {
                                $select = implode(',', $if) . ',' . $select . $endif;
                            } else {
                                $select = $if;
                            }
                        }
                    } elseif ($elt->element_plugin == 'yesno') {
                        if ($raw != 1) {
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
     * @param $fnum
     * @param $evaluator_id
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
     * @return array|false
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
        $query = "SELECT jecc.fnum, jesg.id, GROUP_CONCAT(jesg.label) as label, GROUP_CONCAT(jesg.class) as class FROM #__emundus_campaign_candidature as jecc
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
                $group_labels = explode(',',$r['label']);
                $class_labels = explode(',',$r['class']);
                foreach ($group_labels as $key => $g_label) {
                    $assocTagcampaign = '<span class="label '.$class_labels[$key].'" id="'.$r['id'].'">'.$g_label.'</span>';
                    $access[$r['fnum']] .= $assocTagcampaign;
                }
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
        $attachments = [];

        if (!empty($ids)) {
            $dbo = $this->getDbo();
            $query = $dbo->getQuery(true);
            $query->select('jeu.fnum, jeu.filename, jeu.id, jecc.applicant_id, jeu.attachment_id')
                ->from('#__emundus_uploads AS jeu')
                ->leftJoin('#__emundus_campaign_candidature AS jecc ON jecc.fnum = jeu.fnum')
                ->where('jeu.id IN (' . implode(',', $ids) . ')');
            try {
                $dbo->setQuery($query);
                $attachments = $dbo->loadAssocList();
            } catch(Exception $e) {
                JLog::add('Failed to get attachment by ids ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            }
        }

        return $attachments;
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
    public function getFabrikValueRepeat($elt, $fnums, $params, $groupRepeat) {

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

        $dbo = JFactory::getDbo();
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
        $all_status = [];

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__emundus_setup_status')
            ->order('ordering ASC');

        try {
            $db->setQuery($query);
            $all_status = $db->loadAssocList('step');
        } catch (Exception $e) {
            JLog::add('Failed to get all status ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
        }

        return $all_status;
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

    public function getUnreadMessages() {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();
        $default = array();


        try {
            $query->select('ecc.fnum, COUNT(m.message_id) as nb')
                ->from($db->quoteName('#__emundus_campaign_candidature','ecc'))
                ->leftJoin($db->quoteName('#__emundus_chatroom','ec').' ON '.$db->quoteName('ec.fnum').' LIKE '.$db->quoteName('ecc.fnum'))
                ->leftJoin($db->quoteName('#__messages','m').' ON '.$db->quoteName('m.page').' = '.$db->quoteName('ec.id') . ' AND ' . $db->quoteName('m.state') . ' = ' . $db->quote(0))
                ->group('ecc.fnum');

            $db->setQuery($query);
            $result = $db->loadAssocList();

            return $result;
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get messages associated to user : '. $user->id . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
    }}


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
            return false;
        }
    }

    public function getAllLogActions()
    {
		$logs = [];

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->clear()->select('*')->from($db->quoteName('#__emundus_setup_actions', 'jesa'))->order('jesa.id ASC');

        try {
            $db->setQuery($query);
	        $logs = $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/files | Error when get all logs' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }

		return $logs;
    }

    /**
     * Copy given fnums and all data with it to another user
     * @param $fnums
     * @param $user_to
     * @return bool
     */
    public function bindFilesToUser($fnums, $user_to)
    {
        $bound_fnums = [false];

        if (!empty($fnums) && !empty($user_to)) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('id')
                ->from('#__emundus_users')
                ->where('user_id = ' . $user_to);

            try {
                $db->setQuery($query);
                $exists = $db->loadResult();
            } catch (Exception $e) {
                $exists = false;
                JLog::add('Failed to check if user exists before binding fnum to him ' . $user_to . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            }

            if ($exists) {
                require_once(JPATH_ROOT . '/components/com_emundus/models/application.php');
                $m_application = new EmundusModelApplication();

                foreach($fnums as $i => $fnum) {
                    $bound_fnums[$i] = false;
                    $campaign_id = 0;
                    $query->clear()
                        ->select('campaign_id')
                        ->from('#__emundus_campaign_candidature')
                        ->where('fnum LIKE ' . $db->q($fnum));

                    try {
                        $db->setQuery($query);
                        $campaign_id = $db->loadResult();
                    } catch (Exception $e) {
                        JLog::add('Failed to retrieve campaign from fnum' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                    }

                    if (!empty($campaign_id)) {
                        $fnum_to = $this->createFile($campaign_id, $user_to, time() + $i);

                        if (!empty($fnum_to)) {
                            $query->clear()
                                ->select('*')
                                ->from('#__emundus_campaign_candidature')
                                ->where('fnum LIKE ' . $db->quote($fnum));
                            $db->setQuery($query);
                            $result = $db->loadObject();

                            if (!empty($result)) {
                                $query->clear()
                                    ->update('#__emundus_campaign_candidature')
                                    ->set('user_id = ' . $result->user_id) // keep track of original user
                                    ->set('submitted = ' . $db->quote($result->submitted))
                                    ->set('date_submitted = ' . $db->quote($result->date_submitted))
                                    ->set('status = ' . $db->quote($result->status))
                                    ->set('copied = 1')
                                    ->set('form_progress = ' . $db->quote($result->form_progress))
                                    ->set('attachment_progress = ' . $db->quote($result->attachment_progress))
                                    ->where('fnum LIKE ' . $db->quote($fnum_to));

                                $db->setQuery($query);
                                $updated = $db->execute();

                                if ($updated) {
                                    $copied = $m_application->copyApplication($fnum, $fnum_to, [], 1, $campaign_id, 1, 1, 0);

                                    if (!$copied) {
                                        JLog::add("Failed to copy fnum $fnum to user $user_to account on fnum $fnum_to", JLog::WARNING, 'com_emundus.logs');
                                    } else {
                                        $bound_fnums[$i] = true;
                                        JLog::add("Succeed to copy fnum $fnum to user $user_to account on fnum $fnum_to", JLog::INFO, 'com_emundus.logs');
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                JLog::add('User ' . $user_to . ' seems to not exists', JLog::WARNING, 'com_emundus.logs');
            }
        }

        return !in_array(false, $bound_fnums, true);
    }

    /**
     * Create file for applicant
     * @param $campaign_id
     * @param $user_id If not given, default to Current User
     * @param $time
     * @return string
     */
    public function createFile($campaign_id, $user_id = 0, $time = null)
    {
        $fnum = '';

        if (!empty($campaign_id)) {
            if (empty($user_id)) {
                $current_user = JFactory::getUser();
                if ($current_user->guest == 1) {
                    JLog::add('Error, trying to create file for guest user. Action unauthorized', JLog::WARNING, 'com_emundus.logs');
                    return '';
                }

                $user_id = $current_user->id;
            }

            if ($time == null) {
                $time = time();
            }

            require_once(JPATH_ROOT . '/components/com_emundus/helpers/files.php');
            $h_files = new EmundusHelperFiles();
            $fnum = $h_files->createFnum($campaign_id, $user_id);

            if (!empty($fnum)) {
                $config = JFactory::getConfig();
                $timezone = new DateTimeZone( $config->get('offset'));
                $now = JFactory::getDate()->setTimezone($timezone);

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->clear()
                    ->insert($db->quoteName('#__emundus_campaign_candidature'))
                    ->columns($db->quoteName(['date_time','applicant_id', 'user_id', 'campaign_id', 'fnum']))
                    ->values($db->quote($now).', '.$user_id.', '.$user_id.', '.$campaign_id.', '.$db->quote($fnum));

                $db->setQuery($query);

                try {
                    $inserted = $db->execute();
                } catch (Exception $e) {
                    $fnum = '';
                    $inserted = false;
                    JLog::add("Failed to create file $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                }

                if (!$inserted) {
                    $fnum = '';
                }
            }
        }

        return $fnum;
    }

    /*
     * TODO: refactor this function  (adapt sendEmailTrigger)
     */
    private function sendEmailAfterUpdateState($fnums, $state)
    {
        $msg = '';

        $app = JFactory::getApplication();
        $email_from_sys = $app->getCfg('mailfrom');
        $fnumsInfos = $this->getFnumsInfos($fnums);
        $status = $this->getStatus();

		$current_user = JFactory::getUser();

        // Get all codes from fnum
        $code = array();

        foreach ($fnumsInfos as $fnum) {
            $code[] = $fnum['training'];

	        if(empty($current_user)){
				$user_from = $fnum['applicant_id'];
			} else {
		        $user_from = $current_user->id;
	        }
        }

        //*********************************************************************
        // Get triggered email
        include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
        $m_email = new EmundusModelEmails;

        $trigger_emails = $m_email->getEmailTrigger($state, $code, 1);
        $toAttach = [];

        if (!empty($trigger_emails)) {
            include_once(JPATH_SITE.'/components/com_emundus/models/users.php');
            require_once (JPATH_SITE . '/components/com_emundus/models/application.php');
            require_once(JPATH_ROOT . '/components/com_emundus/helpers/emails.php');
            $m_users = new EmundusModelUsers;
            $m_application = new EmundusModelApplication();
            $h_emails = new EmundusHelperEmails();


            foreach ($trigger_emails as $trigger_email_id => $trigger_email) {
                // Manage with default recipient by programme
                foreach ($trigger_email as $code => $trigger) {

                    if ($trigger['to']['to_applicant'] == 1) {

                        // Manage with selected fnum
                        foreach ($fnumsInfos as $file) {
                            if ($file['training'] != $code) {
                                continue;
                            }

                            $can_send_mail = $h_emails->assertCanSendMailToUser($file['applicant_id'], $file['fnum']);
                            if (!$can_send_mail) {
                                continue;
                            }

                            $toAttach = [];
                            if(!empty($trigger['tmpl']['attachments'])){
                                $attachments = $m_application->getAttachmentsByFnum($file['fnum'],null, explode(',', $trigger['tmpl']['attachments']));

                                foreach ($attachments as $attachment) {
									if(!empty($attachment->filename)) {
										$toAttach[] = EMUNDUS_PATH_ABS . $file['applicant_id'] . '/' . $attachment->filename;
									}
                                }
                            }
                            if(!empty($trigger['tmpl']['letter_attachment'])){
                                include_once(JPATH_SITE . '/components/com_emundus/models/evaluation.php');
                                $m_eval = new EmundusModelEvaluation();

                                $letters = $m_eval->generateLetters($file['fnum'], explode(',',$trigger['tmpl']['letter_attachment']), 1, 0, 0);
                                foreach($letters->files as $filename){
									if(!empty($filename['filename'])){
										$toAttach[] = EMUNDUS_PATH_ABS . $file['applicant_id'] . '/' . $filename['filename'];
									}
                                }
                            }

                            // Check if user defined a cc address
                            $cc = [];
                            $emundus_user = $m_users->getUserById($file['applicant_id'])[0];
                            if (isset($emundus_user->email_cc) && !empty($emundus_user->email_cc)) {
                                if (preg_match('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/', $emundus_user->email_cc) === 1) {
                                    $cc[] = $emundus_user->email_cc;
                                }
                            }

                            $mailer = JFactory::getMailer();

                            $post = array('FNUM' => $file['fnum'],'CAMPAIGN_LABEL' => $file['label'], 'CAMPAIGN_END' => JHTML::_('date', $file['end_date'], JText::_('DATE_FORMAT_OFFSET1'), null));
                            $tags = $m_email->setTags($file['applicant_id'], $post, $file['fnum'], '', $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

                            $from       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
                            $from_id    = JFactory::getUser()->id;
							$from_id    = empty($from_id) ? 62 : $from_id;
                            $fromname   = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
                            $to         = $file['email'];
                            $subject    = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
                            $body = $trigger['tmpl']['message'];


                            // Add the email template model.
                            if (!empty($trigger['tmpl']['template']))
                                $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $trigger['tmpl']['template']);

                            $body = preg_replace($tags['patterns'], $tags['replacements'], $body);
                            $body = $m_email->setTagsFabrik($body, array($file['fnum']));

                            $mail_from_address = $email_from_sys;

                            // Set sender
                            $sender = [
                                $mail_from_address,
                                $fromname
                            ];

                            $mailer->setSender($sender);
                            $mailer->addReplyTo($from, $fromname);
                            $mailer->addRecipient($to);
                            $mailer->setSubject($subject);
                            $mailer->isHTML(true);
                            $mailer->Encoding = 'base64';
                            $mailer->setBody($body);
                            $mailer->addAttachment($toAttach);

                            if (!empty($cc)) {
                                $mailer->addCc($cc);
                            }

                            try {
                                $send = $mailer->Send();
                            } catch (Exception $e) {
                                JLog::add('eMundus Triggers - PHP Mailer send failed ' . $e->getMessage(), JLog::ERROR, 'com_emundus.email');
                            }

                            if ($send !== true) {
                                $msg .= '<div class="alert alert-dismissable alert-danger">'.JText::_('COM_EMUNDUS_MAILS_EMAIL_NOT_SENT').' : '.$to.' '.$send.'</div>';
                                JLog::add($send, JLog::ERROR, 'com_emundus.email');
                            } else {
                                // Assoc tags if email has been sent
                                if(!empty($trigger['tmpl']['tags'])){
                                    $db = JFactory::getDBO();
                                    $query = $db->getQuery(true);

                                    $tags = array_filter(explode(',',$trigger['tmpl']['tags']));

                                    foreach($tags as $tag) {
                                        try{
                                            $query->clear()
                                                ->insert($db->quoteName('#__emundus_tag_assoc'));
                                            $query->set($db->quoteName('fnum') . ' = ' . $db->quote($file['fnum']))
                                                ->set($db->quoteName('id_tag') . ' = ' . $db->quote($tag))
                                                ->set($db->quoteName('user_id') . ' = ' . $db->quote(JFactory::getUser()->id));

                                            $db->setQuery($query);
                                            $db->execute();
                                        }  catch (Exception $e) {
                                            JLog::add('NOT IMPORTANT IF DUPLICATE ENTRY : Error getting template in model/messages at query :'.$query->__toString(). " with " . $e->getMessage(), JLog::ERROR, 'com_emundus');
                                        }
                                    }
                                }

	                            $message = array(
                                    'user_id_from' => $from_id,
                                    'user_id_to' => $file['applicant_id'],
                                    'subject' => $subject,
                                    'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$to.'</i><br>'.$body,
	                                'email_id' => $trigger_email_id,
                                );
	                            $logged = $m_email->logEmail($message, $file['fnum']);
                                $msg .= JText::_('COM_EMUNDUS_MAILS_EMAIL_SENT').' : '.$to.'<br>';
                                JLog::add($to.' '.$body, JLog::INFO, 'com_emundus.email');
                            }
                        }
                    }

                    foreach ($trigger['to']['recipients'] as $recipient) {
                        $can_send_mail = $h_emails->assertCanSendMailToUser($recipient['id']);
                        if (!$can_send_mail) {
                            continue;
                        }

                        $mailer = JFactory::getMailer();

                        $post = array();
                        $tags = $m_email->setTags($recipient['id'], $post, null, '', $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

                        $from       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
                        $from_id    = 62;
                        $fromname   = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
                        $to         = $recipient['email'];
                        $subject    = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
                        $body       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['message']);
                        $body       = $m_email->setTagsFabrik($body, $fnums);

                        // If the email sender has the same domain as the system sender address.
                        if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
                            $mail_from_address = $from;
                        else
                            $mail_from_address = $email_from_sys;

                        // Set sender
                        $sender = [
                            $mail_from_address,
                            $fromname
                        ];

                        $mailer->setSender($sender);
                        $mailer->addReplyTo($from, $fromname);
                        $mailer->addRecipient($to);
                        $mailer->setSubject($subject);
                        $mailer->isHTML(true);
                        $mailer->Encoding = 'base64';
                        $mailer->setBody($body);
                        $mailer->addAttachment($toAttach);

                        $send = $mailer->Send();
                        if ($send !== true) {
                            $msg .= '<div class="alert alert-dismissable alert-danger">'.JText::_('COM_EMUNDUS_MAILS_EMAIL_NOT_SENT').' : '.$to.' '.$send->__toString().'</div>';
                            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
                        } else {
                            // Assoc tags if email has been sent
                            if(!empty($trigger['tmpl']['tags'])){
                                $db = JFactory::getDBO();
                                $query = $db->getQuery(true);

                                $tags = array_filter(explode(',',$trigger['tmpl']['tags']));

                                foreach($tags as $tag) {
                                    try{
                                        $query->clear()
                                            ->insert($db->quoteName('#__emundus_tag_assoc'));
                                        $query->set($db->quoteName('fnum') . ' = ' . $db->quote($file['fnum']))
                                            ->set($db->quoteName('id_tag') . ' = ' . $db->quote($tag))
                                            ->set($db->quoteName('user_id') . ' = ' . $db->quote(JFactory::getUser()->id));

                                        $db->setQuery($query);
                                        $db->execute();
                                    }  catch (Exception $e) {
                                        JLog::add('NOT IMPORTANT IF DUPLICATE ENTRY : Error getting template in model/messages at query :'.$query->__toString(). " with " . $e->getMessage(), JLog::ERROR, 'com_emundus');
                                    }
                                }
                            }

                            $message = array(
                                'user_id_from' => $from_id,
                                'user_id_to' => $recipient['id'],
                                'subject' => $subject,
                                'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$to.'</i><br>'.$body,
	                            'email_id' => $trigger_email_id,
                            );
                            $m_email->logEmail($message, $file['fnum']);
                            $msg .= JText::_('COM_EMUNDUS_MAILS_EMAIL_SENT').' : '.$to.'<br>';
                            JLog::add($to.' '.$body, JLog::INFO, 'com_emundus.email');
                        }
                    }
                }
            }
        }

        return $msg;
    }

	public function saveFilters($user_id, $name, $filters, $item_id) {
		$saved = false;

		if (!empty($user_id) && !empty($name) && !empty($filters)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__emundus_filters'))
				->columns(['user', 'name', 'constraints', 'mode', 'item_id'])
				->values($user_id . ', ' . $db->quote($name) . ', ' . $db->quote($filters) . ', ' . $db->quote('search') . ', ' . $item_id);

			try {
				$db->setQuery($query);
				$saved = $db->execute();
			} catch (Exception $e) {
				JLog::add('Error saving filter: '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

		return $saved;
	}

	public function getSavedFilters($user_id, $item_id) {
		$filters = array();

		if (!empty($user_id)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, name, constraints')
				->from($db->quoteName('#__emundus_filters'))
				->where('user = ' . $user_id)
				->where('item_id = ' . $item_id)
				->where('mode = ' . $db->quote('search'));

			try {
				$db->setQuery($query);
				$filters = $db->loadAssocList();
			} catch (Exception $e) {
				JLog::add('Error getting saved filters: '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

		return $filters;
	}

	public function updateFilter($user_id, $filter_id, $filters, $item_id) {
		$updated = false;

		if (!empty($user_id) && !empty($filter_id) && !empty($filters)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__emundus_filters'))
				->set('constraints = ' . $db->quote($filters))
				->where('user = ' . $user_id)
				->where('id = ' . $filter_id)
				->where('item_id = ' . $item_id)
				->where('mode = ' . $db->quote('search'));

			try {
				$db->setQuery($query);
				$updated = $db->execute();
			} catch (Exception $e) {
				JLog::add('Error updating filter: '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

		return $updated;
	}
}
