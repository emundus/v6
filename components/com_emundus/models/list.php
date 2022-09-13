<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU/GPL
 * @author      Merveille Gbetegan
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelList extends JModelList
{

    // Add Class variables.
    private $db = null;

    /**
     * EmundusModelLogs constructor.
     * @since 3.8.8
     */
    public function __construct()
    {
        parent::__construct();
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'date.php');

        // Assign values to class variables.
        $this->db = JFactory::getDbo();
    }

    public function getListActions($listId, $elementId)
    {
        $data = [];

        if (!empty($listId) && !empty($elementId)) {
            $query = $this->db->getQuery(true);
            $query->select('DISTINCT jfe.label, jfe.name as column_name, jfe.plugin, jfl.db_table_name as db_table_name')
                ->from($this->db->quoteName('#__fabrik_lists', 'jfl'))
                ->leftJoin($this->db->quoteName('#__fabrik_formgroup', 'jffg') . ' ON ' . $this->db->quoteName('jfl.form_id') . ' = ' . $this->db->quoteName('jffg.form_id'))
                ->leftJoin($this->db->quoteName('#__fabrik_elements', 'jfe') . ' ON ' . $this->db->quoteName('jfe.group_id') . ' = ' . $this->db->quoteName('jffg.group_id'))
                ->where($this->db->quoteName('jfl.id') . ' = ' . $listId)
                ->andWhere($this->db->quoteName('jfe.id') . ' = ' . $elementId)
                ->andWhere($this->db->quoteName('jfe.published') . ' = ' . 1);

            $actionsColumns = [];
            $databaseJoinsKeysAndColumns = [];
            $actionsData = [];
            $this->db->setQuery($query);

            try {
                $result = $this->db->loadObject();
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/list | Cannot getting the list action colunmn and data table name: ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            }

            if (!empty($result)) {
                $dbTableName = $result->db_table_name;
                if ($result->plugin == "databasejoin") {
                    $response = $this->retrieveDataBasePluginElementJoinKeyColumnAndTable($result->id);

                    if (!empty($response)) {
                        $params = json_decode($response->params, true);

                        if (!empty($params["join-label"])) {
                            $response->column_real_name = $result->column_name;
                            $column = $response->table_join . '.' . $params["join-label"];
                            $databaseJoinsKeysAndColumns[] = $response;
                            $actionsColumns[] = $column;
                            $actionsColumns[] = $params["pk"] . 'AS ' . $params["join-label"] . '_pk';
                        }
                    }
                } else {
                    $actionsColumns[] = $dbTableName . '.' . $result->column_name;
                }

                if (!empty($actionsColumns)) {
                    $query->clear();
                    $query->select('DISTINCT '.implode(",",$actionsColumns))
                        ->from($this->db->quoteName($dbTableName));

                    if (!empty($databaseJoinsKeysAndColumns)) {
                        foreach ($databaseJoinsKeysAndColumns as $data) {
                            $query->join($data->join_type, $this->db->quoteName($data->table_join) . ' ON ' . $this->db->quoteName($data->table_join . '.' . $data->table_join_key) . ' = ' . $this->db->quoteName($dbTableName . '.' . $data->table_key));
                        }
                    }

                    try {
                        $this->db->setQuery($query);
                        $actionDataResult = $this->db->loadObjectList();
                    } catch (Exception $e) {
                        JLog::add('component/com_emundus/models/list | Cannot getting the list data table content: ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
                        return 0;
                    }
                    $actionsData = $this->removeForeignKeyValueFormDataLoadedIfExistingDatabaseJoinElementInList($databaseJoinsKeysAndColumns, $actionDataResult);
                }

                $data = ["actionsColumns" => $result, "actionsData" => $actionsData];
            }
        }

        return $data;
    }

    public function getList($listId, $listParticularConditionalColumn, $listParticularConditionalColumnValues)
    {
        $data = [];

        if (!empty($listId)) {
            $query = $this->db->getQuery(true);
            $query->select('DISTINCT jfe.name as column_name, jfe.plugin, jfe.filter_type, jfe.params, jfe.label,jfe.id, jfl.db_table_name as db_table_name')
                ->from($this->db->quoteName('#__fabrik_lists', 'jfl'))
                ->leftJoin($this->db->quoteName('#__fabrik_formgroup', 'jffg') . ' ON ' . $this->db->quoteName('jfl.form_id') . ' = ' . $this->db->quoteName('jffg.form_id'))
                ->leftJoin($this->db->quoteName('#__fabrik_groups', 'jfg') . ' ON ' . $this->db->quoteName('jffg.group_id') . ' = ' . $this->db->quoteName('jfg.id'))
                ->leftJoin($this->db->quoteName('#__fabrik_elements', 'jfe') . ' ON ' . $this->db->quoteName('jfe.group_id') . ' = ' . $this->db->quoteName('jffg.group_id'))
                ->where($this->db->quoteName('jfl.id') . ' = ' . $listId)
                ->andWhere('jfg.published = 1')
                ->andWhere($this->db->quoteName('jfe.show_in_list_summary') . ' = ' . 1)
                ->andWhere($this->db->quoteName('jfe.published') . ' = ' . 1)
                ->order($this->db->quoteName('jfe.ordering').' ASC');
            $this->db->setQuery($query);

            try {
                $result = $this->db->loadAssocList();
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/list | Cannot getting the list colunmns and data table name: ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            }

            if (!empty($result)) {
                $dbTableName = $result[0]["db_table_name"];
                $listColumns = [];
                $databaseJoinsKeysAndColumns = [];

                foreach ($result as list('column_name' => $column_name, 'plugin' => $plugin, 'id' => $id,'params'=> $params)) {
                    if ($plugin == "databasejoin") {
                        $response = $this->retrieveDataBasePluginElementJoinKeyColumnAndTable($id);

                        if (!empty($response)) {
                            $params = json_decode($response->params, true);

                            if (!empty($params["join-label"])) {
                                $response->column_real_name = $column_name;
                                $column = $response->table_join . '.' . $params["join-label"];
                                $databaseJoinsKeysAndColumns[] = $response;
                                $listColumns[] = $column;
                                $listColumns[] = $params["pk"] . 'AS ' . $params["join-label"] . '_pk';
                            }
                        }
                    } else {
                        $listColumns[] = $dbTableName . '.' . $column_name;
                    }
                }

                $query->clear()
                    ->select($listColumns)
                    ->from($this->db->quoteName($dbTableName));

                if (!empty($databaseJoinsKeysAndColumns)) {
                    foreach ($databaseJoinsKeysAndColumns as $data) {
                        $query->join($data->join_type, $this->db->quoteName($data->table_join) . ' ON ' . $this->db->quoteName($data->table_join . '.' . $data->table_join_key) . ' = ' . $this->db->quoteName($dbTableName . '.' . $data->table_key));
                    }
                }

                /*** The code below before the try catch is used to get data from table with specific where clause column define in module configuration ******/
                $firstWhere = false;
                foreach ($listParticularConditionalColumn as $column) {
                    $values = explode(',', $column);
                    $values = '"' . join('", "', $values) . '"';

                    if (!empty($column)) {
                        if (!$firstWhere) {
                            $query->where($this->db->quoteName($column) . ' IN (' .$values. ')');
                            $firstWhere = true;
                        } else {
                            $query->andWhere($this->db->quoteName($column) . ' IN (' . $values . ')');
                        }
                    }
                }

                $this->db->setQuery($query);
                try {
                    $listDataResult = $this->db->loadObjectList();
                } catch (Exception $e) {
                    JLog::add('component/com_emundus/models/list | Cannot getting the list data table content: ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
                    return 0;
                }

                foreach ($result as $key => $res) {
                    $result[$key]['label'] = JText::_($res['label']);
                }

                $listData = $this->removeForeignKeyValueFormDataLoadedIfExistingDatabaseJoinElementInList($databaseJoinsKeysAndColumns, $listDataResult);
                $data = ["listColumns" => $result, "listData" => $listData];
            }
        }

        return $data;
    }

    public function retrieveDataBasePluginElementJoinKeyColumnAndTable($elementId)
    {
        $elementDatas = [];

        $query = $this->db->getQuery(true);
        $query->select("table_join,table_key,table_join_key,join_type, params")
            ->from('#__fabrik_joins')
            ->where($this->db->quoteName('element_id') . '=' . $elementId);

        $this->db->setQuery($query);

        try {
            $elementDatas = $this->db->loadObject();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/list | Cannot getting the retrieveDataBasePluginElementJoinKeyColumnAndTable : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
        }

        return $elementDatas;
    }

    public function removeForeignKeyValueFormDataLoadedIfExistingDatabaseJoinElementInList($databasejoinColumnsList, $listData)
    {
        if (count($databasejoinColumnsList) > 0) {
            foreach ($databasejoinColumnsList as $dbJoin) {
                foreach ($listData as $data) {
                    $params = json_decode($dbJoin->params, true);
                    $property = $params["join-label"] . '_pk';
                    $real_name = $dbJoin->column_real_name;
                    $join_name = $params["join-label"];
                    //rename column join to his real name on the object
                    $data->$real_name = $data->$join_name;
                    unset($data->$property);
                    unset($data->$join_name);
                }
            }
        }

        return $listData;
    }

    public function actionSetColumnValueAs($rowId, $value, $dbTablename, $columnName) {
        $updated = false;

        $query = $this->db->getQuery(true);
        $query->update($this->db->quoteName($dbTablename))
              ->set($this->db->quoteName($columnName) .' = '.$this->db->quote($value))
              ->where($this->db->quoteName('id') . ' IN ('. $rowId.')');
        $this->db->setQuery($query);

        try {
            $updated = $this->db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/list | Error when trying to set action column value as  : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
        }

        return $updated;
    }
}
