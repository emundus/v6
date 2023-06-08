<?php

//namespace emundusfilemaker;

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS . 'api' . DS . 'FileMaker.php');

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'users.php');


require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

defined('_JEXEC') or die('Restricted access');

use \classes\api\FileMaker;


class PlgFabrik_Cronemundusfilemaker extends PlgFabrik_Cron
{
    public static $offset = 1;


    /**
     * Check if the user can use the plugin
     *
     * @param string $location To trigger plugin on
     * @param string $event To trigger plugin on
     *
     * @return  bool can use or not
     */
    public function canUse($location = null, $event = null)
    {
        return true;
    }

    public function group_by($key, $data)
    {
        $result = array();

        foreach ($data as $val) {
            if (array_key_exists($key, $val)) {
                $result[$val[$key]][] = $val;
            } else {
                $result[""][] = $val;
            }
        }

        return $result;
    }

    public function retrieveMappingColumnsData()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $mapping_data = array();

        $query->select('filemaker_label,emundus_form_id')
            ->from($db->quoteName($this->getParams()->get('forms_mapping_table_beetween_filemaker_emundus')))
            ->where($db->quoteName('step')."=".$this->getParams()->get('mail_trigger_state'));
        $db->setQuery($query);


        try {
            $result = $db->loadAssocList();

            $result_group_by_file_maker_attribute = $this->group_by("filemaker_label", $result);


            foreach ($result_group_by_file_maker_attribute as $key => $value) {

                foreach ($value as $sub_row) {

                    $query->clear();
                    $query->select('jfl.db_table_name,jfg.group_id,fgs.params,jfj.join_from_table,jfj.table_join,jfj.table_join_key,jfj.table_key')
                        ->from($db->quoteName('jos_fabrik_lists', 'jfl'))
                        ->leftJoin('jos_fabrik_formgroup AS jfg ON jfl.form_id = jfg.form_id')
                        ->leftJoin('jos_fabrik_joins AS jfj ON jfl.id = jfj.list_id')
                        ->leftJoin('jos_fabrik_groups AS fgs ON fgs.id = jfg.group_id')
                        ->where('jfl.form_id = ' . $sub_row["emundus_form_id"]);
                    $db->setQuery($query);


                    $result = $db->loadObjectList();


                    $mapping_data_row = new StdClass();
                    $mapping_data_row->filemaker_form_label = $key;

                    $mapping_data_row->form_id = $sub_row["emundus_form_id"];
                    $mapping_data_row->groups_id = array();
                    $mapping_data_row->groups = array();
                    foreach ($result as $val) {
                        $group = new StdClass();
                        $group->id = intval($val->group_id);
                        $group->params = $val->params;

                        $mapping_data_row->db_table_name = $val->db_table_name;
                        $mapping_data_row->groups_id[] = intval($val->group_id);
                        $mapping_data_row->groups[] = $group;


                        $mapping_data_row->join_from_table = $val->join_from_table;
                        $mapping_data_row->table_join = $val->table_join;
                        $mapping_data_row->table_join_key = $val->table_join_key;
                        $mapping_data_row->table_key = $val->table_key;


                    }

                    $mapping_data_row->elements = $this->retrieveAssociatedElementsWithgroup($mapping_data_row->groups_id);
                    $mapping_data[] = $mapping_data_row;

                }

            }


            return $mapping_data;


        } catch (\Exception $e) {

            JLog::add('[FABRIK CRON FILEMAKER retrieveMappingColumnsData] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return [];
        }

    }


    public function retrieveAssociatedElementsWithgroup($groups_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('jfe.*,zfe.file_maker_attribute_name')
            ->from($db->quoteName('jos_fabrik_elements', 'jfe'))
            ->leftJoin($this->getParams()->get('attribute_mapping_table_beetween_filemaker_emundus') . ' AS zfe ON zfe.file_maker_assoc_emundus_element = jfe.id')
            ->where('jfe.group_id IN (' . implode(',', $groups_id) . ')')
            ->andWhere('jfe.published = 1');
        $db->setQuery($query);

        try {
            return $db->loadObjectList();

        } catch (\Exception $e) {

            JLog::add('[FABRIK CRON FILEMAKER retrieveAssociatedElementsWithgroup] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');

            return [];
        }

    }


    public function getRecords($limit, $offset, $adminStep)
    {
        $file_maker_api = new FileMaker();
        try {
            $records = $file_maker_api->findRecord($limit, $offset, $adminStep);
            return $records;
        } catch (\Exception $e) {
            JLog::add('[FABRIK CRON FILEMAKER  GET RECORDS] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return $e->getMessage();
        }


    }

    public function createFiles($filesData, $mappedColumns = [])
    {

        foreach ($filesData as $file) {


            $fieldData = $file->fieldData;


            if (!empty($fieldData->InterlocuteurIF) && !empty($fieldData->InterlocuteurIF_Email)) {

                $user_id = $this->createUserIfNotExist($fieldData->InterlocuteurIF_Email, $fieldData->InterlocuteurIF);

                $this->createSingleFile($file, $user_id, $mappedColumns);


            }

        }

    }


    public function createUserIfNotExist($email, $name)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        $query->select('*')
            ->from($db->quoteName('jos_users'))
            ->where($db->quoteName('email') . '=' . $db->quote($email));
        $db->setQuery($query);

        $user = $db->loadObject();
        $query->clear();

        if (!empty($user)) {
            return $user->id;
        } else {

            $profile = intval($this->getParams()->get('profile'));

            $m_users = new EmundusModelUsers;
            $h_users = new EmundusHelperUsers;
            $firstname_and_lastname = explode(" ", $name);
            $user_id = 0;

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $password = md5($h_users->generateStrongPassword());
            $acl_aro_groups = $m_users->getDefaultGroup($profile);

            $query->insert('#__users')
                ->columns('name, username, email, password')
                ->values($db->quote($name) . ', ' . $db->quote($email) . ', ' . $db->quote($email) . ',' . $db->quote($password));

            try {
                $db->setQuery($query);
                $db->execute();
                $user_id = $db->insertid();

                $query->clear();
                $query->insert('jos_user_usergroup_map')
                    ->columns('user_id,group_id ')
                    ->values($db->quote($user_id) . ', ' . $db->quote($acl_aro_groups[0]));
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e) {
                JLog::add("Failed to insert jos_users" . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            }


            if (!empty($user_id)) {
                $other_param['firstname'] = $firstname_and_lastname[0];
                $other_param['lastname'] = $firstname_and_lastname[1];
                $other_param['profile'] = $profile;
                $other_param['em_oprofiles'] = '';
                $other_param['univ_id'] = 0;
                $other_param['em_groups'] = '';
                $other_param['em_campaigns'] = [];
                $other_param['news'] = '';
                $m_users->addEmundusUser($user_id, $other_param);
            }

            return $user_id;

        }


    }

    public function createSingleFile($singleFieldData, $user_id, $mapped_columns)
    {
        $fieldData = $singleFieldData->fieldData;
        $campaign_id = $this->getParams()->get('campaign_id');
        $config = JFactory::getConfig();
        $timezone = new DateTimeZone($config->get('offset'));
        $now = JFactory::getDate()->setTimezone($timezone);
        $h_files = new EmundusHelperFiles();
        $m_files = new EmundusModelFiles();
        $m_message = new EmundusControllerMessages();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $checkIfFileNotAlreadyExist = $this->checkIfFileNotAlreadyExist($fieldData->uuid);
        if (empty($checkIfFileNotAlreadyExist) && $this->getParams()->get('admin_step') === "PRE") {


            $fnum = $h_files->createFnum($campaign_id, $user_id);

            while (!empty($this->checkIfFnumNotAlreadyExist($fnum, '#__emundus_campaign_candidature'))) {
                $fnum = $h_files->createFnum($campaign_id, $user_id);
            }


            $query->clear()
                ->insert($db->quoteName('#__emundus_campaign_candidature'))
                ->columns($db->quoteName(['date_time', 'applicant_id', 'user_id', 'campaign_id', 'fnum', 'uuid', 'uuidConnect']))
                ->values($db->quote($now) . ', ' . $user_id . ', ' . $user_id . ', ' . $campaign_id . ', ' . $db->quote($fnum) . ', ' . $db->quote($fieldData->uuid) . ', ' . $db->quote($fieldData->uuidConnect));
            $db->setQuery($query);


            try {

                $db->execute();
                $this->insertFileDataToEmundusTables($fnum, $singleFieldData, $mapped_columns, $user_id);
                $m_files->updateState($fnum, $this->getParams()->get('mail_trigger_state', 0));
                $m_message->sendEmail($fnum, $this->getParams()->get('mail_template', 83));
            } catch (Exception $e) {
                $fnum = '';
                $inserted = false;

                JLog::add("[FILEMAKER CRON] Failed to create file $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            }


        } else {

            // here we update file status and portal data information
            $query->clear()
                ->update($db->quoteName('#__emundus_campaign_candidature'))
                ->set($db->quoteName(['uuidConnect']) . ' = ' . $db->quote(json_encode($db->quote($fieldData->uuidConnect))))
                ->where($db->quoteName('uuid') . "LIKE" . $fieldData->uuid);
            $db->setQuery($query);

            try {

                $db->execute();
                $this->insertFileDataToEmundusTables($checkIfFileNotAlreadyExist->fnum, $singleFieldData, $mapped_columns, $user_id);
                $m_files->updateState($checkIfFileNotAlreadyExist->fnum, $this->getParams()->get('mail_trigger_state', 3));
                $m_message->sendEmail($checkIfFileNotAlreadyExist->fnum, $this->getParams()->get('mail_template', 83));

            } catch (Exception $e) {
                $fnum = '';
                $inserted = false;

                JLog::add("[FILEMAKER CRON] Failed to update file status $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            }
        }

    }

    public function insertFileDataToEmundusTables($fnum, $singleFieldData, $mapped_columns, $user_id)
    {
        $this->insertGlobalLayoutFormData($fnum, $singleFieldData, $mapped_columns, $user_id);
        $this->insertPortalsDatasElements($singleFieldData, $mapped_columns, $fnum, $user_id);

    }

    public function insertGlobalLayoutFormData($fnum, $singleFieldData, $mapped_columns, $user_id)
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        //Insertion of ZWEB_FORMULAIRE DATA;
        $ZWEB_FORMULAIRE_MAPPED_ELEMENTS = $this->searchMappedElementsByFileMakerFormLabel($mapped_columns, $this->getParams()->get('filemaker_global_layout'));


        if (!empty($ZWEB_FORMULAIRE_MAPPED_ELEMENTS)) {
            $config = JFactory::getConfig();
            foreach ($ZWEB_FORMULAIRE_MAPPED_ELEMENTS as $row) {

                $timezone = new DateTimeZone($config->get('offset'));
                $now = JFactory::getDate()->setTimezone($timezone);
                $elements_columns_name = ["time_date", "fnum", "user"];
                $elements_columns_assoc_filemaker_attribute_names = [];
                $elements_columns_value = [$db->quote($now), $fnum, $user_id];

                foreach ($row->elements as $element_row) {

                    if (!empty($element_row->file_maker_attribute_name)) {

                        $elements_columns_name[] = $db->quoteName($element_row->name);
                        $fileMakerAttr = new stdClass();
                        $fileMakerAttr->name = $element_row->file_maker_attribute_name;
                        $fileMakerAttr->plugin = $element_row->plugin;
                        $fileMakerAttr->id = $element_row->id;
                        $elements_columns_assoc_filemaker_attribute_names[] = $fileMakerAttr;

                    }
                }


                $fieldData = (array)$singleFieldData->fieldData;

                foreach ($elements_columns_assoc_filemaker_attribute_names as $val) {
                    switch ($val->plugin) {
                        case 'databasejoin':
                            $target_databejoin_element_value = $this->retrieveDataBaseJoinElementJointureInformations($val, $fieldData[$val->name]);
                            $elements_columns_value[] = !empty($target_databejoin_element_value) ? $db->quote($target_databejoin_element_value) : 'NULL';

                            break;
                        case 'date':
                            $date_value = str_replace('/', '-', $fieldData[$val->name]);
                            $elements_columns_value[] = !empty($date_value) ? $db->quote($date_value) : 'NULL';
                            break;

                        case 'birthday':
                            $date_value = str_replace('/', '-', $fieldData[$val->name]);
                            $elements_columns_value[] = !empty($date_value) ? $db->quote($date_value) : 'NULL';
                            break;

                        default :
                            $elements_columns_value[] = !empty($fieldData[$val->name]) ? $db->quote($fieldData[$val->name]) : 'NULL';
                    }

                }


                if (!empty($elements_columns_name) && !empty($elements_columns_value) && !empty($elements_columns_assoc_filemaker_attribute_names)) {
                    $elements_value_occurences = array_count_values($elements_columns_value);

                    if (empty($this->checkIfFnumNotAlreadyExist($fnum, $row->db_table_name)) && $elements_value_occurences["NULL"] !== (sizeof($elements_columns_value) - 3)) {
                        $query->clear();
                        $query->insert($db->quoteName($row->db_table_name))
                            ->columns($elements_columns_name)
                            ->values(implode(",", $elements_columns_value));

                        $db->setQuery($query);


                        try {
                            //$inserted = $db->execute();

                            $db->execute();
                        } catch (Exception $e) {

                            JLog::add("[FILEMAKER CRON] Failed to insert row in to table $row->db_table_name for fnum $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
                        }

                    }
                }

            }
        }

    }

    public function insertPortalsDatasElements($singleFieldData, $mapped_columns, $fnum, $user_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $portalData = (array)$singleFieldData->portalData;
        $portalData_Keys = array_keys($portalData);

        foreach ($portalData_Keys as $key) {
            if (!empty($portalData[$key])) {


                $ZWEB_FORMULAIRE_MAPPED_ELEMENTS = $this->searchMappedElementsByFileMakerFormLabel($mapped_columns, $key);

                if (!empty($ZWEB_FORMULAIRE_MAPPED_ELEMENTS)) {
                    $config = JFactory::getConfig();
                    foreach ($ZWEB_FORMULAIRE_MAPPED_ELEMENTS as $row) {

                        $formGroups = array_map('json_encode', $row->groups);
                        $formGroups = array_unique($formGroups);
                        $formGroups = array_map('json_decode', $formGroups);
                        foreach ($formGroups as $group) {
                            $params = json_decode($group->params);
                            $group->elements = [];
                            $group->jointures_params = "";

                            if (intval($params->repeat_group_button) == 1) {
                                $query->clear();
                                $query->select('join_from_table,table_join,table_join_key,table_key')
                                    ->from($db->quoteName('jos_fabrik_joins'))
                                    ->where('group_id =  ' . $group->id)
                                    ->andWhere('table_join_key =' . $db->quote('parent_id'));
                                $db->setQuery($query);
                                try {
                                    $group->jointures_params = $db->loadObjectList();
                                } catch (Exception $e) {
                                    JLog::add("[FILEMAKER CRON] Failed to get tabele joins params for repeat group  $group->id [FNUM] $fnum" . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');

                                }

                                foreach ($portalData[$key] as $portal_row) {
                                    $fieldData = (array)($portal_row);
                                    $search = $key . '::';
                                    $replace = '';
                                    $fieldData_keys = array_map(function ($item) use ($search, $replace) {
                                        return str_replace($search, $replace, $item);
                                    }, array_keys($fieldData));


                                    $arr_key_cal = [];
                                    foreach ($fieldData_keys as $row_key) {


                                        $query->clear();
                                        $query->select('jfe.name,jfe.id,jfe.plugin,zfe.file_maker_attribute_name')
                                            ->from($db->quoteName('jos_fabrik_elements', 'jfe'))
                                            ->leftJoin($this->getParams()->get('attribute_mapping_table_beetween_filemaker_emundus') . ' AS zfe ON zfe.file_maker_assoc_emundus_element = jfe.id')
                                            ->where('jfe.group_id =  ' . $group->id)
                                            ->andWhere('jfe.published = 1')
                                            ->andWhere('zfe.file_maker_attribute_name = ' . $db->quote($row_key));
                                        $db->setQuery($query);

                                        try {
                                            $val = $db->loadObject();
                                            if (!empty($val)) {
                                                switch ($val->plugin) {
                                                    case 'databasejoin':
                                                        $target_databejoin_element_value = $this->retrieveDataBaseJoinElementJointureInformations($val, $fieldData[$key . "::" . $row_key]);
                                                        $arr_key_cal[] = array("" . $val->name . "" => $db->quote($target_databejoin_element_value));

                                                        break;
                                                    case 'date':
                                                        $date_value = str_replace('/', '-', $fieldData[$key . "::" . $row_key]);
                                                        $arr_key_cal[] = array("" . $val->name . "" => $db->quote($date_value));
                                                        break;

                                                    case 'birthday':
                                                        $date_value = str_replace('/', '-', $fieldData[$key . "::" . $row_key]);
                                                        $arr_key_cal[] = array("" . $val->name . "" => $db->quote($date_value));
                                                        break;

                                                    default :
                                                        $arr_key_cal[] = array("" . $val->name . "" => $db->quote($fieldData[$key . "::" . $row_key]));
                                                }


                                            }
                                        } catch (Exception $e) {
                                            JLog::add("[FILEMAKER CRON] Failed to get emundus assoc element for filemaker attr  $key:: $row_key [FNUM] $fnum" . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');

                                        }

                                    }


                                    if (!empty($arr_key_cal)) {
                                        $group->elements[] = $arr_key_cal;
                                    }


                                }


                                $this->insertDataIntoRepeatGroupsTable($fnum, $group, $user_id);

                            }


                        }

                    }
                }

            }
        }

    }

    public function insertDataIntoRepeatGroupsTable($fnum, $group, $user_id)
    {
        $db = JFactory::getDbo();

        $config = JFactory::getConfig();
        $timezone = new DateTimeZone($config->get('offset'));
        $now = JFactory::getDate()->setTimezone($timezone);

        foreach ($group->jointures_params as $jointures_param) {
            $parent_table = ($jointures_param)->join_from_table;
            $repeat_table = ($jointures_param)->table_join;


            if (!empty($group->elements)) {
                foreach ($group->elements as $row) {
                    $row_values = $this->transformToAssociativeArray(array_values($row));
                    $row_columns = array_keys($row_values);
                    $row_columns_real_values = array_values($row_values);
                    $empty_value_count_occurrence = array_count_values($row_columns_real_values);


                    if ($empty_value_count_occurrence[""] !== sizeof($row_columns_real_values)) {

                        $checkIfNoRowInParentTableForTheTargetFnum = $this->checkIfFnumNotAlreadyExist($fnum, $parent_table);
                        if (empty($checkIfNoRowInParentTableForTheTargetFnum)) {
                            var_dump($repeat_table);
                            var_dump($parent_table);
                            $parent_id = $this->insertIntoATable($parent_table, ["time_date", "fnum", "user"], [$db->quote($now), $fnum, $user_id]);
                            if (!empty($parent_id)) {
                                $row_columns[] = "parent_id";
                                $row_columns_real_values[] = $parent_id;

                                $hey = $this->insertIntoATable($repeat_table, $row_columns, $row_columns_real_values);

                            }

                        } else {
                            $parent_id = $checkIfNoRowInParentTableForTheTargetFnum->id;
                            $row_columns[] = "parent_id";
                            $row_columns_real_values[] = $parent_id;

                            $hey = $this->insertIntoATable($repeat_table, $row_columns, $row_columns_real_values);

                        }


                    }


                }
            }

        }

    }

    public function retrieveDataBaseJoinElementJointureInformations($element, $needed)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('table_join,params')
            ->from($db->quoteName('jos_fabrik_joins'))
            ->where('element_id =  ' . $element->id);
        $db->setQuery($query);
        try {
            $result = $db->loadObject();
            $params = json_decode($result->params, true);

            return $this->retrieveDatabaseJoinElementValue($result->table_join, $params["join-label"], $needed);

        } catch (Exception $e) {

            JLog::add("[FILEMAKER CRON] Failed to get table joins params for element id   $element " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return 0;
        }

    }

    public function retrieveDatabaseJoinElementValue($dbtable, $column_where, $needed)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName($dbtable))
            ->where($db->quoteName($column_where) . "=" . $db->quote($needed));
        $db->setQuery($query);

        try {
            $result = $db->loadObject();

            return $result->id;
        } catch (Exception $e) {

            JLog::add("[FILEMAKER CRON] Failed to get database join  Element Value in  $dbtable " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return 0;
        }
    }

    public function insertIntoATable($db_table_name, $elements_columns_name, $elements_columns_value, $fnum = 0, $user_id = 0)
    {

        echo '<pre>';
        var_dump($elements_columns_value);
        echo '<pre>';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear();
        $query->insert($db->quoteName($db_table_name))
            ->columns($elements_columns_name)
            ->values(implode(",", $elements_columns_value));

        $db->setQuery($query);
        echo '<pre>';
        var_dump($query->__toString());
        echo '</pre>';
        try {
            //$inserted = $db->execute();
            $db->execute();
            return $db->insertid();
        } catch (Exception $e) {
            var_dump('hop hop an error');
            var_dump($query->__toString());
            var_dump($e->getMessage());
            die;
            JLog::add("[FILEMAKER CRON] Failed to insert row in to table $db_table_name for fnum $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return 0;
        }
    }

    public function transformToAssociativeArray($array)
    {
        $associativeArray = array();

        foreach ($array as $item) {
            $key = key($item);
            $value = reset($item);
            $associativeArray[$key] = $value;
        }

        return $associativeArray;

    }


    public function searchMappedElementsByFileMakerFormLabel($mapped_columns, $label)
    {

        $values = [];
        foreach ($mapped_columns as $row) {

            if ($row->filemaker_form_label == $label) {


                $values[] = $row;
            }
        }


        return $values;
    }


    public function checkIfFileNotAlreadyExist($uuid)
    {

        $file = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        $query->select('*')
            ->from($db->quoteName('#__emundus_campaign_candidature'))
            ->where($db->quoteName('uuid') . '=' . $db->quote($uuid));
        $db->setQuery($query);

        try {
            $file = $db->loadObject();

        } catch (Exception $e) {

            JLog::add("[FILEMAKER CRON] Failed to check if file already exist for " . $uuid . " " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }

        return $file;

    }


    public function checkIfFnumNotAlreadyExist($fnum, $db_table_name)
    {

        $file = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        $query->select('*')
            ->from($db->quoteName($db_table_name))
            ->where($db->quoteName('fnum') . '=' . $db->quote($fnum));
        $db->setQuery($query);

        try {
            $file = $db->loadObject();

        } catch (Exception $e) {

            JLog::add("[FILEMAKER CRON] Failed to check if file already exist for fnum" . $fnum . " " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }

        return $file;

    }


    /**
     * Do the plugin action
     *
     * @param array  &$data data
     *
     * @return  mixed  number of records updated
     * @throws Exception
     */
    public function process(&$data, &$listModel)
    {

        $mapped_columns = $this->retrieveMappingColumnsData();
        $offset = 1;
        $limit = 20;
        $returnedCount = -1;

        while ($returnedCount != 0) {


            $find_records_response = $this->getRecords($limit, $offset, $this->getParams()->get('admin_step'));

            if (!empty($find_records_response)) {
                $dataInfo = $find_records_response->dataInfo;

                $returnedCount = $dataInfo->returnedCount;
                $offset += $limit;


                $this->createFiles($find_records_response->data, $mapped_columns);
            }
        }


    }


}
