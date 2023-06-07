<?php

//namespace emundusfilemaker;

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS . 'api' . DS . 'FileMaker.php');

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');

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

    public function group_by($key, $data) {
        $result = array();

        foreach($data as $val) {
            if(array_key_exists($key, $val)){
                $result[$val[$key]][] = $val;
            }else{
                $result[""][] = $val;
            }
        }

        return $result;
    }

    public function retrieveMappingColumnsData(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $mapping_data = array();

        $query->select('filemaker_label,emundus_form_id')
              ->from($db->quoteName($this->getParams()->get('forms_mapping_table_beetween_filemaker_emundus')));
        $db->setQuery($query);


        try{
            $result = $db->loadAssocList();

            $result_group_by_file_maker_attribute = $this->group_by("filemaker_label",$result);


            foreach ($result_group_by_file_maker_attribute as $key=>$value){

                foreach ($value as $sub_row){

                    $query->clear();
                    $query->select('jfl.db_table_name,jfg.group_id')
                        ->from($db->quoteName('jos_fabrik_lists','jfl'))
                        ->leftJoin('jos_fabrik_formgroup AS jfg ON jfl.form_id = jfg.form_id')
                        ->where('jfl.form_id = '.$sub_row["emundus_form_id"]);
                    $db->setQuery($query);

                    $result = $db->loadObjectList();

                    $mapping_data_row = new StdClass();
                    $mapping_data_row->filemaker_form_label = $key;

                    $mapping_data_row->form_id = $sub_row["emundus_form_id"];
                    $mapping_data_row->groups_id = array();
                    foreach ($result as $val){

                        $mapping_data_row->db_table_name = $val->db_table_name;
                        $mapping_data_row->groups_id[] = intval($val->group_id);

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


    public function retrieveAssociatedElementsWithgroup($groups_id){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('jfe.*,zfe.file_maker_attribute_name')
            ->from($db->quoteName('jos_fabrik_elements','jfe'))
            ->leftJoin($this->getParams()->get('attribute_mapping_table_beetween_filemaker_emundus').' AS zfe ON zfe.file_maker_assoc_emundus_element = jfe.id')
            ->where('jfe.group_id IN ('.implode(',',$groups_id).')')
            ->andWhere('jfe.published = 1');
        $db->setQuery($query);

        try{
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

    public function createFiles($filesData,$mappedColumns = [])
    {

        foreach ($filesData as $file) {


            $fieldData = $file->fieldData;


            if (!empty($fieldData->InterlocuteurIF) && !empty($fieldData->InterlocuteurIF_Email)) {

                $user_id = $this->createUserIfNotExist($fieldData->InterlocuteurIF_Email, $fieldData->InterlocuteurIF);

                $this->createSingleFile($file, $user_id,$mappedColumns);


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
                ->values($db->quote($name) . ', ' . $db->quote($email) .  ', ' . $db->quote($email) . ',' .  $db->quote($password));

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
            /*$password = $h_users->generateStrongPassword();
            $user                = clone(JFactory::getUser(0));
            $user->name          = $name;
            $user->username      = $email;
            $user->email         = $email;
            $user->password      = md5($password);
            $user->registerDate  = date('Y-m-d H:i:s');
            $user->lastvisitDate = date('Y-m-d H:i:s');

            $user->groups        = array();
            $user->block         = 0;

            $acl_aro_groups = $m_users->getDefaultGroup($profile);
            $user->groups   = $acl_aro_groups;

            $usertype       = $m_users->found_usertype($acl_aro_groups[0]);

            $user->usertype = $usertype;

            //$user->save();
            $user_id = $user->id;*/


            if (!empty($user_id)) {
                $other_param['firstname'] 		= $firstname_and_lastname[0];
                $other_param['lastname'] 		= $firstname_and_lastname[1];
                $other_param['profile'] 		= $profile;
                $other_param['em_oprofiles'] 	= '';
                $other_param['univ_id'] 		= 0;
                $other_param['em_groups'] 		= '';
                $other_param['em_campaigns'] 	= [];
                $other_param['news'] 			= '';
                $m_users->addEmundusUser($user_id, $other_param);
            }

            return $user_id;

        }


    }

    public function createSingleFile($singleFieldData, $user_id,$mapped_columns)
    {
        $fieldData = $singleFieldData->fieldData;
        $campaign_id = $this->getParams()->get('campaign_id');
        $config = JFactory::getConfig();
        $timezone = new DateTimeZone( $config->get('offset'));
        $now = JFactory::getDate()->setTimezone($timezone);
        $h_files = new EmundusHelperFiles();




        if(empty($this->checkIfFileNotAlreadyExist($fieldData->uuid))){



            $fnum = $h_files->createFnum($campaign_id, $user_id);

            while(!empty($this->checkIfFnumNotAlreadyExist($fnum,'#__emundus_campaign_candidature'))){
                $fnum = $h_files->createFnum($campaign_id, $user_id);
            }


            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->clear()
                ->insert($db->quoteName('#__emundus_campaign_candidature'))
                ->columns($db->quoteName(['date_time', 'applicant_id', 'user_id', 'campaign_id', 'fnum', 'uuid', 'uuidConnect']))
                ->values($db->quote($now) . ', ' . $user_id . ', ' . $user_id . ', ' . $campaign_id . ', ' . $db->quote($fnum). ', ' . $db->quote($fieldData->uuid). ', ' . $db->quote($fieldData->uuidConnect));
            $db->setQuery($query);


            try {

                $inserted = $db->execute();



                $this->insertFileDataToEmundusTables($fnum,$singleFieldData,$mapped_columns,$user_id);
            } catch (Exception $e) {
                $fnum = '';
                $inserted = false;

                JLog::add("[FILEMAKER CRON] Failed to create file $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            }

            if (!$inserted) {
                $fnum = '';
            }
        }

    }

    public function insertFileDataToEmundusTables($fnum,$singleFieldData,$mapped_columns,$user_id){


        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        //Insertion of ZWEB_FORMULAIRE DATA;
        $ZWEB_FORMULAIRE_MAPPED_ELEMENTS = $this->searchMappedElementsByFileMakerFormLabel($mapped_columns,$this->getParams()->get('filemaker_global_layout'));


        if(!empty($ZWEB_FORMULAIRE_MAPPED_ELEMENTS)){
            $config = JFactory::getConfig();
                foreach ($ZWEB_FORMULAIRE_MAPPED_ELEMENTS as $row){

                    $timezone = new DateTimeZone( $config->get('offset'));
                    $now = JFactory::getDate()->setTimezone($timezone);
                    $elements_columns_name = ["time_date","fnum","user"];
                    $elements_columns_assoc_filemaker_attribute_names = [];
                    $elements_columns_value = [$db->quote($now),$fnum,$user_id];

                    foreach ($row->elements as $element_row){

                        if(!empty($element_row->file_maker_attribute_name)){

                            $elements_columns_name[] = $db->quoteName($element_row->name);
                            $elements_columns_assoc_filemaker_attribute_names[] = $element_row->file_maker_attribute_name;

                        }
                    }


                    $fieldData = (array) $singleFieldData->fieldData;

                    foreach($elements_columns_assoc_filemaker_attribute_names as $val){
                        $all_values = null;
                        $elements_columns_value[] = !empty($fieldData[$val]) ? $db->quote($fieldData[$val]) : 'NULL';
                    }


                    if(!empty($elements_columns_name) && !empty($elements_columns_value && !empty($elements_columns_assoc_filemaker_attribute_names)) ){
                        $elements_value_occurences = array_count_values($elements_columns_value);
                        if(empty($this->checkIfFnumNotAlreadyExist($fnum,$row->db_table_name)) && $elements_value_occurences["NULL"] !== (sizeof($elements_columns_value)-3)){
                            $query->clear();
                            $query->insert($db->quoteName($row->db_table_name))
                                ->columns($elements_columns_name)
                                ->values(implode(",",$elements_columns_value));

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



    public function searchMappedElementsByFileMakerFormLabel($mapped_columns,$label){

        $values =[];
        foreach ($mapped_columns as $row){

            if($row->filemaker_form_label == $label){


               $values[] = $row;
            }
        }


        return  $values;
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

            JLog::add("[FILEMAKER CRON] Failed to check if file already exist for ". $uuid. " ". $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }

        return $file;

    }


    public function checkIfFnumNotAlreadyExist($fnum,$db_table_name)
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

            JLog::add("[FILEMAKER CRON] Failed to check if file already exist for fnum". $fnum. " ". $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
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
        $records_retrieved = [];
        $mapped_columns = $this->retrieveMappingColumnsData();




        $find_records_response = $this->getRecords(50, $this->offset, $this->getParams()->get('admin_step'));


        if (!empty($find_records_response)) {
            $this->createFiles($find_records_response->data,$mapped_columns);
        }



    }


}
