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
    private $user = null;
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
        $this->user = JFactory::getUser();
        $this->db = JFactory::getDbo();
    }


    public function getList($listId)
    {

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT jfe.name as column_name, jfe.plugin,jfe.filter_type, jfe.label,jfe.id, jfl.db_table_name as db_table_name')
            ->from($this->db->quoteName('#__fabrik_lists', 'jfl'))
            ->leftJoin($this->db->quoteName('#__fabrik_formgroup', 'jffg') . ' ON ' . $this->db->quoteName('jfl.form_id') . ' = ' . $this->db->quoteName('jffg.form_id'))
            ->leftJoin($this->db->quoteName('#__fabrik_elements', 'jfe') . ' ON ' . $this->db->quoteName('jfe.group_id') . ' = ' . $this->db->quoteName('jffg.group_id'))
            ->where($this->db->quoteName('jfl.id') . ' = ' . $listId)
            ->andWhere($this->db->quoteName('jfe.show_in_list_summary') . ' = ' . 1);

        try {
            $this->db->setQuery($query);
            $result = $this->db->loadAssocList();
            $dbTableName = $result[0]["db_table_name"];

            $listColumns = [];
            $databaseJoinsKeysAndColumns = [];
            $i = 0;
            foreach ($result as list('column_name' => $column_name, 'plugin' => $plugin, 'id'=>$id)) {
                //array_push($listColumns, str_replace(" ","_",$column_name));
                if($plugin == "databasejoin"){
                    $response = $this->retrieveDataBasePluginElementJoinKeyColumnAndTable($id);
                    if($response !=0){
                        /*$data = new stdClass();
                        $data->index = $i;
                        $data->content = $response;*/
                        $params = json_decode($response->params,true);
                        $response->column_real_name= $column_name;
                        $column = $response->table_join.'.'.$params["join-label"];
                        array_push($databaseJoinsKeysAndColumns,$response);
                        array_push($listColumns,$column);
                        array_push($listColumns,$response->table_join.'.id AS '.$params["join-label"].'_pk');
                    }
                } else{
                    array_push($listColumns,$dbTableName.'.'.$column_name);
                }


            }

        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/list | Cannot getting the list colunmns and data table name: ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
        $query->clear();
        $query->select($listColumns)
            ->from($this->db->quoteName($dbTableName));

        if(count($databaseJoinsKeysAndColumns)>0){
            foreach ($databaseJoinsKeysAndColumns as $data){
                $query->join($data->join_type, $this->db->quoteName($data->table_join) . ' ON ' . $this->db->quoteName($data->table_join.'.'.$data->table_join_key) . ' = ' .  $this->db->quoteName($dbTableName.'.'.$data->table_key));
            }
        }


        try {

            $this->db->setQuery($query);

            $listDataResult = $this->db->loadObjectList();

            $listData = $this->removeForeignKeyValueFormDataLoadedIfExistingDatabaseJoinElementInList($databaseJoinsKeysAndColumns,$listDataResult);


        } catch (Exception $e) {
            var_dump($e->getMessage());
            JLog::add('component/com_emundus/models/list | Cannot getting the list data table content: ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }

        return ["listColumns"=>$result,"listData"=>$listData];

    }

    public function retrieveDataBasePluginElementJoinKeyColumnAndTable($elementId){

        $query = $this->db->getQuery(true);
        $query->select("table_join,table_key,table_join_key,join_type, params")
              ->from('#__fabrik_joins')
              ->where($this->db->quoteName('element_id'). '=' .$elementId);

        try {
            $this->db->setQuery($query);

            $result = $this->db->loadObject();

            return $result;

        } catch (Exception $e) {

            JLog::add('component/com_emundus/models/list | Cannot getting the retrieveDataBasePluginElementJoinKeyColumnAndTable : ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }


    }

    public function removeForeignKeyValueFormDataLoadedIfExistingDatabaseJoinElementInList($databasejoinColumnsList, $listData){
        if(count($databasejoinColumnsList)>0) {
            foreach ($databasejoinColumnsList as $dbJoin) {
                foreach ($listData as $data) {
                    $params = json_decode($dbJoin->params, true);
                    $property = $params["join-label"] . '_pk';
                    $real_name = $dbJoin->column_real_name;
                    $join_name =  $params["join-label"];
                    //rename column join to his real name on the object
                     $data->$real_name = $data->$join_name ;
                    unset($data->$property);
                    unset($data->$join_name);
                }
            }
            return $listData;
        } else{
            return $listData;
        }

    }

}
