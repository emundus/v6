<?php

//namespace emundusfilemaker;

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'classes' . DS . 'api' . DS . 'FileMaker.php');

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'fabrik.php');
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
                $result[''][] = $val;
            }
        }

        return $result;
    }

    /**
     * @return array
     * This function is used to retrieve all mapped emundus attributes with filemaker attributes
     */
    public function retrieveMappingColumnsData(): array
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $mapping_data = array();
        $query->select('filemaker_label,emundus_form_id,portal_data_group_id')
            ->from($db->quoteName($this->getParams()->get('forms_mapping_table_beetween_filemaker_emundus')))
            ->where($db->quoteName('step') . "=" . $this->getParams()->get('mail_trigger_state'));
        try {
            $db->setQuery($query);
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
                    $mapping_data_row->portal_data_emundus_group_id = $sub_row["portal_data_group_id"];
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
                    $mapping_data_row->elements = $this->retrieveAssociatedElementsWithGroup($mapping_data_row->groups_id);
                    $mapping_data[] = $mapping_data_row;
                }
            }
        } catch (\Exception $e) {
            JLog::add('[FABRIK CRON FILEMAKER retrieveMappingColumnsData] ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        return $mapping_data;
    }


    public function retrieveAssociatedElementsWithGroup($groups_id): array
    {
        $associated_elements = array();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('jfe.*,zfe.file_maker_attribute_name')
            ->from($db->quoteName('jos_fabrik_elements', 'jfe'))
            ->leftJoin($this->getParams()->get('attribute_mapping_table_beetween_filemaker_emundus') . ' AS zfe ON zfe.file_maker_assoc_emundus_element = jfe.id')
            ->where('jfe.group_id IN (' . implode(',', $groups_id) . ')')
            ->andWhere('jfe.published = 1')
            ->andWhere('zfe.step =' . $this->getParams()->get('mail_trigger_state'));
        try {
            $db->setQuery($query);
            $associated_elements = $db->loadObjectList();
        } catch (\Exception $e) {
            JLog::add('[FABRIK CRON FILEMAKER retrieveAssociatedElementsWithGroup] ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        return $associated_elements;
    }

    /**
     * @param $limit
     * @param $offset
     * @param $admin_step
     * @return array
     * This function is used to retrieve records from FileMaker database
     */
    public function getRecords($limit, $offset, $admin_step)
    {
        $records = [];
        $file_maker_api = new FileMaker();
        if (!empty($file_maker_api->getAuth()['bear_token'])) {
            try {
                $records = $file_maker_api->findRecord($limit, $offset, $admin_step);
            } catch (\Exception $e) {
                JLog::add('[FABRIK CRON FILEMAKER  GET RECORDS] ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            }
        } else {
            JLog::add('[FILEMAKER ] Failed to login to API', JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }
        return $records;
    }

    /**
     * @param $filesData
     * @param $mapped_columns
     * @return void
     * This function is used to create files in emundus database
     */
    public function createFiles($filesData, $mapped_columns = []): void
    {
        $filemaker = new FileMaker();
        if (empty($filemaker->getAuth()['bear_token'])) {
            JLog::add('[FILEMAKER ] Failed to login to API', JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return;
        }
        foreach ($filesData as $file) {
            if (!empty($file->fieldData->{'zWEB_FORMULAIRES_PROGRAMMATIONS::web_emailContact'})) {
                $user_id = $this->createUserIfNotExist($file->fieldData->{'zWEB_FORMULAIRES_PROGRAMMATIONS::web_emailContact'}, 'Nom Prénom ');
                $this->createSingleFile($file, $user_id, $mapped_columns, $filemaker);
            }
        }
    }

    public function createUserIfNotExist($email, $name): int
    {
        $user_id = 0;
        $email = preg_replace('/\r/', '', $email);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('jos_users'))
            ->where($db->quoteName('email') . '=' . $db->quote($email));
        $db->setQuery($query);
        $user = $db->loadObject();
        $query->clear();

        if (!empty($user)) {
            $user_id = $user->id;
        } else {
            $profile = intval($this->getParams()->get('profile'));
            $m_users = new EmundusModelUsers;
            $h_users = new EmundusHelperUsers;
            $firstname_and_lastname = explode(" ", $name);

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
                JLog::add("Failed to insert jos_users" . $e->getMessage(), JLog::ERROR, 'com_emundus');
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

        }
        return $user_id;
    }

    /**
     * @param $file
     * @param $user_id
     * @param $fnum
     * @return bool
     * This function is used to update emundus file applicant id
     */
    public function updateFileApplicantId($file, $user_id, $fnum):bool
    {
        $result = false;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear()
            ->update($db->quoteName('#__emundus_campaign_candidature'))
            ->set($db->quoteName('applicant_id') . ' = ' . $user_id)
            ->where($db->quoteName('uuid') . "=" . $db->quote($file->fieldData->uuid));
        try {
            $db->setQuery($query);
            $result = $db->execute();
        } catch (Exception $e) {
            JLog::add("[FILEMAKER CRON] Failed to update file applicant id $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        return $result;
    }

    /**
     * @param $file
     * @param $fnum
     * @return bool
     * This function is used to update emundus file project information like intitule, closing date, institut francais interlocuteur
     */
    public function updateProjectInformation($file, $fnum):bool
    {
        $result = false;
        $closing_date_string = str_replace('/', '-', $file->fieldData->Admin_ClosingDate);
        $date = DateTime::createFromFormat('m-d-Y', $closing_date_string);
        $date !== false ? $date_value = $date->format('Y-m-d') : $date_value = $closing_date_string;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear()
            ->update($db->quoteName('jos_emundus_1001_00'))
            ->set($db->quoteName('e_947_8592') . ' = ' . $db->quote($file->fieldData->InterlocuteurIF))
            ->set($db->quoteName('e_947_8593') . ' = ' . $db->quote($file->fieldData->InterlocuteurIF_Email))
            ->set($db->quoteName('e_800_7974') . ' = ' . $db->quote($file->fieldData->Projet_Intitule))
            ->set($db->quoteName('e_796_7967') . ' = ' . $db->quote($date_value))
            ->where($db->quoteName('fnum') . 'LIKE' . $db->quote($fnum));
        try {
            $db->setQuery($query);
            $result = $db->execute();
        } catch (Exception $e) {
            JLog::add("[FILEMAKER CRON] Failed to update project information for  $fnum " . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        return $result;
    }

    /**
     * @param $single_field_data
     * @param $user_id
     * @param $mapped_columns
     * @param $filemaker
     * @return void
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * This function is used to create single file in emundus database
     */
    public function createSingleFile($single_field_data, $user_id, $mapped_columns, $filemaker): void
    {
        $campaign_id = $this->getParams()->get('campaign_id');
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('UTC'));
        $now = $now->format('Y-m-d H:i:s');
        $h_files = new EmundusHelperFiles();
        $m_files = new EmundusModelFiles();
        $m_message = new EmundusControllerMessages();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $emundus_file = $this->getEmundusFile($single_field_data->fieldData->uuid);
        if (empty($emundus_file)) {
            $fnum = $h_files->createFnum($campaign_id, $user_id);
            //while fnum exist int campaign candidature table generate new one
            while (!empty($this->getRowInTable($fnum, '#__emundus_campaign_candidature'))) {
                $fnum = $h_files->createFnum($campaign_id, $user_id);
            }
            $query->clear()
                ->insert($db->quoteName('#__emundus_campaign_candidature'))
                ->columns($db->quoteName(['date_time', 'applicant_id', 'user_id', 'campaign_id', 'fnum', 'uuid', 'uuidConnect', 'recordId']))
                ->values($db->quote($now) . ', ' . $user_id . ', ' . $user_id . ', ' . $campaign_id . ', ' . $db->quote($fnum) . ', ' . $db->quote($single_field_data->fieldData->uuid) . ', ' . $db->quote($single_field_data->fieldData->uuidConnect) . ', ' . $db->quote($single_field_data->recordId));

            try {
                $db->setQuery($query);
                $db->execute();
                $this->insertFileDataToEmundusTables($fnum, $single_field_data, $mapped_columns, $user_id);
                $m_files->updateState($fnum, $this->getParams()->get('mail_trigger_state', 0));
                $m_message->sendEmail($fnum, $this->getParams()->get('mail_template', 83));
                $filemaker->logActionIntoEmundusFileMakerlog(0, $fnum, $single_field_data->fieldData->uuid, $single_field_data->fieldData->uuidConnect, 0, NULL, 1, "layouts/zWEB_FORMULAIRES/_find", "");

            } catch (Exception $e) {
                $fnum = '';
                JLog::add("[FILEMAKER CRON] Failed to create file $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus');
            }

        } else {
            $admin_step = $this->getParams()->get('admin_step');
            if (intval($emundus_file->applicant_id) != intval($user_id)) {

                $applicant_file_update_result = $this->updateFileApplicantId($single_field_data, $user_id, $emundus_file->fnum);
                $project_information_update_result = $this->updateProjectInformation($single_field_data, $emundus_file->fnum);
                if($applicant_file_update_result && $project_information_update_result){
                    $m_message->sendEmail($emundus_file->fnum, $this->getParams()->get('mail_template'));
                }
            }
            $this->updateFile($emundus_file,$single_field_data, $query, $m_files, $m_message, $db, $admin_step, $filemaker, $mapped_columns, $user_id);
        }
    }

    /**
     * @param $emundus_file
     * @param $single_field_data
     * @param $query
     * @param $m_files
     * @param $m_message
     * @param $db
     * @param $admin_step
     * @param $filemaker
     * @param $mapped_columns
     * @param $user_id
     * @return void
     * This function is used to update file based FileMaker uuidConnect value
     */
    public function updateFile($emundus_file, $single_field_data, $query, $m_files, $m_message, $db, $admin_step, $filemaker, $mapped_columns, $user_id): void
    {
        if ($emundus_file->uuidConnect !== $single_field_data->fieldData->uuidConnect) {
            switch ($admin_step) {
                case 'PRE':
                    $query->clear()
                        ->update($db->quoteName('#__emundus_campaign_candidature'))
                        ->set($db->quoteName('uuidConnect') . ' = ' . $db->quote($single_field_data->fieldData->uuidConnect))
                        ->where($db->quoteName('uuid') . "=" . $db->quote($single_field_data->fieldData->uuid));
                    try {
                        $db->setQuery($query);
                        $db->execute();
                        $m_files->updateState($emundus_file->fnum, $this->getParams()->get('mail_trigger_state'));
                        $this->updateProjectInformation($single_field_data, $emundus_file->fnum);
                    } catch (Exception $e) {
                        $fnum = '';
                        JLog::add("[FILEMAKER CRON] Failed to update file status $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus');
                    }
                    break;

                case 'POST':
                    $query->clear()
                        ->update($db->quoteName('#__emundus_campaign_candidature'))
                        ->set($db->quoteName('uuidConnect') . ' = ' . $db->quote($single_field_data->fieldData->uuidConnect))
                        ->where($db->quoteName('uuid') . "=" . $db->quote($single_field_data->fieldData->uuid));
                    try {
                        $db->setQuery($query);
                        $db->execute();
                        if (intval($emundus_file->status === 1)) {
                            $this->insertFileDataToEmundusTables($emundus_file->fnum, $single_field_data, $mapped_columns, $user_id);
                            $m_message->sendEmail($emundus_file->fnum, $this->getParams()->get('mail_template', 83));
                            $filemaker->logActionIntoEmundusFileMakerlog(0, $emundus_file->fnum, $single_field_data->fieldData->uuid, $single_field_data->fieldData->uuidConnect, 3, NULL, 1, "layouts/zWEB_FORMULAIRES/_find", "");
                        }
                        $m_files->updateState($emundus_file->fnum, $this->getParams()->get('mail_trigger_state'));
                    } catch (Exception $e) {
                        $fnum = '';
                        JLog::add("[FILEMAKER CRON] Failed to update file status $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus');
                    }
                    break;
            }
        } else {
            switch ($admin_step) {
                case 'PRE':
                    if (in_array(intval($emundus_file->status), [1, 3])) {
                        $m_files->updateState($emundus_file->fnum, $this->getParams()->get('mail_trigger_state'));
                        $this->updateProjectInformation($single_field_data, $emundus_file->fnum);
                    }
                    break;
                case 'POST':
                    if (in_array(intval($emundus_file->status), [1, 4])) {
                        $m_files->updateState($emundus_file->fnum, $this->getParams()->get('mail_trigger_state'));
                        $this->updateProjectInformation($single_field_data, $emundus_file->fnum);
                    }
                    break;
            }
        }
    }

    public function retrieveRecetteContributionIf($data): array
    {
        $dataContributionInstituFrancais = array_filter(($data)->{'zWEB_FORMULAIRES_RECETTES'}, function ($item) {
            return !empty($item->{'zWEB_FORMULAIRES_RECETTES::EstContributionIF'});
        });
        $modifiedRecettes = array_map(function ($item) {
            $new_item = (array)$item;
            $intitule = $item->{'zWEB_FORMULAIRES_RECETTES::Intitule'};

            $new_key_previsionel = str_replace([" ", ":", "-"], "", ucwords($intitule)) . "_Previsionnel";
            $new_item[$new_key_previsionel] = $new_item["zWEB_FORMULAIRES_RECETTES::Montant_Previsionnel"];

            $new_key_realise = str_replace([" ", ":", "-"], "", ucwords($intitule)) . "_Realise";
            $new_item[$new_key_realise] = $new_item["zWEB_FORMULAIRES_RECETTES::Montant_Realise"];

            $new_key_detail = str_replace([" ", ":", "-"], "", ucwords($intitule)) . "_Detail";
            $new_item[$new_key_detail] = $new_item["zWEB_FORMULAIRES_RECETTES::Detail"];

            $new_key_detail = str_replace([" ", ":", "-"], "", ucwords($intitule)) . "_RecordId";
            $new_item[$new_key_detail] = $new_item["recordId"];

            unset($new_item["zWEB_FORMULAIRES_RECETTES::Intitule"]);
            unset($new_item["zWEB_FORMULAIRES_RECETTES::Ordre"]);
            unset($new_item["zWEB_FORMULAIRES_RECETTES::Montant_Previsionnel"]);
            unset($new_item["zWEB_FORMULAIRES_RECETTES::uuidPrgRecettes"]);
            unset($new_item["zWEB_FORMULAIRES_RECETTES::Montant_Realise"]);
            unset($new_item["zWEB_FORMULAIRES_RECETTES::Detail"]);
            unset($new_item["zWEB_FORMULAIRES_RECETTES::EstContributionIF"]);

            unset($new_item["recordId"]);
            unset($new_item["modId"]);
            return $new_item;
        }, $dataContributionInstituFrancais);

        return array_merge(...$modifiedRecettes);
    }

    public function insertFileDataToEmundusTables($fnum, $single_field_data, $mapped_columns, $user_id): void
    {
        $this->insertGlobalLayoutFormData($fnum, $single_field_data, $mapped_columns, $user_id);
        $this->insertPortalsDatasElements($single_field_data, $mapped_columns, $fnum, $user_id);

    }

    public function insertGlobalLayoutFormData($fnum, $single_field_data, $mapped_columns, $user_id)
    {
        $result =false;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $fabik_helper = new EmundusHelperFabrik();
        //Insertion of ZWEB_FORMULAIRE DATA;
        $ZWEB_FORMULAIRE_MAPPED_ELEMENTS = $this->getAssocElementsWithFileMakerFormLabel($mapped_columns, $this->getParams()->get('filemaker_global_layout'));
        if (!empty($ZWEB_FORMULAIRE_MAPPED_ELEMENTS)) {
            foreach ($ZWEB_FORMULAIRE_MAPPED_ELEMENTS as $row) {
                $now = new DateTime();
                $now->setTimezone(new DateTimeZone('UTC'));
                $now = $now->format('Y-m-d H:i:s');
                $elements_names = ["time_date", "fnum", "user"];
                $elements_assoc_filemaker_attribute = [];
                $elements_values = [$db->quote($now), $fnum, $user_id];
                foreach ($row->elements as $element_row) {
                    if (!empty($element_row->file_maker_attribute_name && $element_row->plugin !== "internalid")) {
                        $elements_names[] = $db->quoteName($element_row->name);
                        $fileMakerAttr = new stdClass();
                        $fileMakerAttr->name = $element_row->file_maker_attribute_name;
                        $fileMakerAttr->plugin = $element_row->plugin;
                        $fileMakerAttr->params = $element_row->params;
                        $fileMakerAttr->id = $element_row->id;
                        $elements_assoc_filemaker_attribute[] = $fileMakerAttr;
                    }
                }
                $recette_contribution_if = $this->retrieveRecetteContributionIf($single_field_data->portalData);
                $field_data = array_merge((array)$single_field_data->fieldData, $recette_contribution_if);
                foreach ($elements_assoc_filemaker_attribute as $val) {
                    switch ($val->plugin) {
                        case 'internalid':
                            break;
                        case 'databasejoin':
                            $target_db_join_element_value = $this->retrieveDataBaseJoinElementJointureInformations($val, $field_data[$val->name]);
                            $elements_values[] = !empty($target_db_join_element_value) ? $db->quote($target_db_join_element_value) : 'NULL';
                            break;
                        case 'birthday':
                        case 'date':
                            $date_string = str_replace('/', '-', $field_data[$val->name]);
                            $date = DateTime::createFromFormat('m-d-Y', $date_string);
                            $date !== false ? $date_value = $date->format('Y-m-d') : $date_value = $date_string;
                            $elements_values[] = !empty($date_value) ? $db->quote($date_value) : 'NULL';
                            break;
                        case 'emundus_phonenumber':
                            $formatted_number = $fabik_helper->getFormattedPhoneNumberValue($field_data[$val->name]);
                            $elements_values[] = !empty($formatted_number) ? $db->quote($formatted_number) : $db->quote($field_data[$val->name]);
                            break;
                        case 'dropdown':
                        case 'radiobutton':
                            $params = json_decode($val->params);
                            $option_sub_labels = array_map(function ($data) {
                                return JText::_($data);
                            }, $params->sub_options->sub_labels);

                            $index = array_search($field_data[$val->name], $option_sub_labels, false);
                            if ($index !== false) {
                                $elements_values[] = $db->quote($params->sub_options->sub_values[$index]);
                            } else {
                                $elements_values[] = !empty($field_data[$val->name]) ? $db->quote($field_data[$val->name]) : 'NULL';
                            }
                            break;

                        case 'checkbox':
                            $params = json_decode($val->params);
                            $values = explode("\r", $field_data[$val->name]);
                            $option_sub_labels = array_map(function ($data) {
                                return JText::_($data);
                            }, $params->sub_options->sub_labels);
                            $elm = array();
                            foreach ($values as $sub_val) {
                                $key = array_search($sub_val, $option_sub_labels);
                                $elm[] = "" . $params->sub_options->sub_values[$key] . "";
                            }
                            $elements_values[] = $db->quote('[' . implode(",",$elm) . ']');
                            break;

                        default :
                            $elements_values[] = !empty($field_data[$val->name]) ? $db->quote($field_data[$val->name]) : 'NULL';
                    }

                }
                if (!empty($elements_names) && !empty($elements_values) && !empty($elements_assoc_filemaker_attribute)) {
                    $elements_values_occurrences = array_count_values($elements_values);
                    if (empty($this->getRowInTable($fnum, $row->db_table_name)) && $elements_values_occurrences["NULL"] !== (sizeof($elements_values) - 3)) {
                        $query->clear();
                        $query->insert($db->quoteName($row->db_table_name))
                            ->columns($elements_names)
                            ->values(implode(",", $elements_values));
                        try {
                            $db->setQuery($query);
                            $result = $db->execute();
                        } catch (Exception $e) {
                            $result =false;
                            JLog::add("[FILEMAKER CRON] Failed to insert row in to table $row->db_table_name for fnum $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus');
                        }
                    }
                }

            }
        }

        return $result;

    }

    public function insertPortalsDatasElements($single_field_data, $mapped_columns, $fnum, $user_id): void
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $portal_data_without_est_contribIf_recette = array_filter(($single_field_data->portalData)->{'zWEB_FORMULAIRES_RECETTES'}, function ($item) {
            return empty($item->{'zWEB_FORMULAIRES_RECETTES::EstContributionIF'});
        });
        ($single_field_data->portalData)->{'zWEB_FORMULAIRES_RECETTES'} = $portal_data_without_est_contribIf_recette;
        $portal_data = (array)$single_field_data->portalData;
        $portal_data_keys = array_keys($portal_data);
        $fabik_helper = new EmundusHelperFabrik();
        foreach ($portal_data_keys as $key) {
            if (!empty($portal_data[$key])) {
                $ZWEB_FORMULAIRE_MAPPED_ELEMENTS = $this->getAssocElementsWithFileMakerFormLabel($mapped_columns, $key);
                if (!empty($ZWEB_FORMULAIRE_MAPPED_ELEMENTS)) {
                    foreach ($ZWEB_FORMULAIRE_MAPPED_ELEMENTS as $row) {
                        $form_groups = array_map('json_encode', $row->groups);
                        $form_groups = array_unique($form_groups);
                        $form_groups = array_map('json_decode', $form_groups);
                        foreach ($form_groups as $group) {
                            $params = json_decode($group->params);
                            $group->elements = [];
                            $group->jointures_params = "";
                            if (intval($params->repeat_group_button) == 1) {
                                $query->clear();
                                $query->select('join_from_table,table_join,table_join_key,table_key')
                                    ->from($db->quoteName('jos_fabrik_joins'))
                                    ->where('group_id =  ' . $group->id)
                                    ->andWhere('table_join_key =' . $db->quote('parent_id'));
                                try {
                                    $db->setQuery($query);
                                    $group->jointures_params = $db->loadObjectList();
                                } catch (Exception $e) {
                                    JLog::add("[FILEMAKER CRON] Failed to get tabele joins params for repeat group  $group->id [FNUM] $fnum" . $e->getMessage(), JLog::ERROR, 'com_emundus');
                                }
                                foreach ($portal_data[$key] as $portal_row) {
                                    $field_data = (array)($portal_row);
                                    $search = $key . '::';
                                    $replace = '';
                                    $field_data_keys = array_map(function ($item) use ($search, $replace) {
                                        return str_replace($search, $replace, $item);
                                    }, array_keys($field_data));

                                    $repeat_elements_values = [];
                                    foreach ($field_data_keys as $row_key) {
                                        $query->clear();
                                        $query->select('jfe.name,jfe.group_id,jfe.id,jfe.plugin,jfe.params,zfe.file_maker_attribute_name')
                                            ->from($db->quoteName('jos_fabrik_elements', 'jfe'))
                                            ->leftJoin($this->getParams()->get('attribute_mapping_table_beetween_filemaker_emundus') . ' AS zfe ON zfe.file_maker_assoc_emundus_element = jfe.id')
                                            ->where('jfe.group_id =  ' . $group->id)
                                            ->andWhere('jfe.published = 1')
                                            ->andWhere('zfe.file_maker_attribute_name = ' . $db->quote($row_key));
                                        try {
                                            $db->setQuery($query);
                                            $val = $db->loadObject();
                                            if (!empty($val) && intval($val->group_id) == intval($row->portal_data_emundus_group_id)) {
                                                switch ($val->plugin) {
                                                    case 'databasejoin':
                                                        $target_db_join_element_value = $this->retrieveDataBaseJoinElementJointureInformations($val, $field_data[$key . "::" . $row_key]);
                                                        $repeat_elements_values[] = array("" . $val->name . "" => $db->quote($target_db_join_element_value));
                                                        break;
                                                    case 'birthday':
                                                    case 'date':
                                                        $date_string = str_replace('/', '-', $field_data[$key . "::" . $row_key]);
                                                        $date = DateTime::createFromFormat('m-d-Y', $date_string);
                                                        $date !== false ? $date_value = $date->format('Y-m-d') : $date_value = $date_string;
                                                        $repeat_elements_values[] = array("" . $val->name . "" => $db->quote($date_value));
                                                        break;
                                                    case 'emundus_phonenumber':
                                                        $formatted_number = $fabik_helper->getFormattedPhoneNumberValue($field_data[$key . "::" . $row_key]);
                                                        $repeat_elements_values[] = array("" . $val->name . "" => !empty($formatted_number) ? $db->quote($formatted_number) : $db->quote($field_data[$key . "::" . $row_key]));
                                                        break;
                                                    case 'dropdown':
                                                    case 'radiobutton':
                                                        $params = json_decode($val->params);
                                                        $option_sub_labels = array_map(function ($data) {
                                                            return JText::_($data);
                                                        }, $params->sub_options->sub_labels);
                                                        $index = array_search($field_data[$key . "::" . $row_key], $option_sub_labels, false);
                                                        if ($index !== false) {
                                                            $repeat_elements_values[] = array("" . $val->name . "" => $db->quote($params->sub_options->sub_values[$index]));
                                                        } else {
                                                            $repeat_elements_values[] = array("" . $val->name . "" => $db->quote($field_data[$key . "::" . $row_key]));
                                                        }
                                                        break;
                                                    case 'checkbox':
                                                        $params = json_decode($val->params);
                                                        $values = explode("\r", $field_data[$key . "::" . $row_key]);
                                                        $option_sub_labels = array_map(function ($data) {
                                                            return JText::_($data);
                                                        }, $params->sub_options->sub_labels);
                                                        $elm = array();
                                                        foreach ($values as $sub_val) {
                                                            $key = array_search($sub_val, $option_sub_labels);
                                                            $elm[] = "" . $params->sub_options->sub_values[$key] . "";
                                                        }
                                                        $repeat_elements_values[] = array("" . $val->name . "" => $db->quote("[" . implode(",", @$elm) . "]"));
                                                        break;

                                                    default :
                                                        if ($row_key === "recordId") {
                                                            $repeat_elements_values[] = array("" . $val->name . "" => $db->quote($field_data[$row_key]));
                                                        } else {
                                                            $repeat_elements_values[] = array("" . $val->name . "" => $db->quote($field_data[$key . "::" . $row_key]));
                                                        }
                                                }
                                            }
                                        } catch (Exception $e) {
                                            JLog::add("[FILEMAKER CRON] Failed to get emundus assoc element for filemaker attr  $key:: $row_key [FNUM] $fnum" . $e->getMessage(), JLog::ERROR, 'com_emundus');
                                        }
                                    }
                                    if (!empty($repeat_elements_values)) {
                                        $group->elements[] = $repeat_elements_values;
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

    /**
     * @param $fnum
     * @param $group
     * @param $user_id
     * @return void
     * This function is used to insert data into repeat groups table
     */
    public function insertDataIntoRepeatGroupsTable($fnum, $group, $user_id): void
    {
        $db = JFactory::getDbo();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('UTC'));
        $now = $now->format('Y-m-d H:i:s');
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
                        $fnum_row_in_parent_table = $this->getRowInTable($fnum, $parent_table);
                        if (empty($fnum_row_in_parent_table)) {
                            $parent_id = $this->insertIntoATable($parent_table, ["time_date", "fnum", "user"], [$db->quote($now), $fnum, $user_id]);
                            if (!empty($parent_id)) {
                                $row_columns[] = "parent_id";
                                $row_columns_real_values[] = $parent_id;
                                $this->insertIntoATable($repeat_table, $row_columns, $row_columns_real_values);
                            }
                        } else {
                            $parent_id = $fnum_row_in_parent_table->id;
                            $row_columns[] = "parent_id";
                            $row_columns_real_values[] = $parent_id;
                            $this->insertIntoATable($repeat_table, $row_columns, $row_columns_real_values);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $element
     * @param $needed
     * @return int|mixed|null
     * this function is used to get the value of a database join element
     */
    public function retrieveDataBaseJoinElementJointureInformations($element, $needed)
    {
        $result = 0;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('table_join,params')
            ->from($db->quoteName('jos_fabrik_joins'))
            ->where('element_id =  ' . $element->id);
        try {
            $db->setQuery($query);
            $result = $db->loadObject();
            $params = json_decode($result->params, true);
            $result = $this->retrieveDatabaseJoinElementValue($result->table_join, $params["join-label"], $needed);
        } catch (Exception $e) {
            JLog::add("[FILEMAKER CRON] Failed to get table joins params for element id   $element " . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        return $result;
    }

    /**
     * @param $db_table
     * @param $column_where
     * @param $needed
     * @return int
     * This function is used to get the value of a database join element value
     */
    public function retrieveDatabaseJoinElementValue($db_table, $column_where, $needed)
    {
        $result = 0;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName($db_table))
            ->where($db->quoteName($column_where) . "=" . $db->quote($needed));
        try {
            $db->setQuery($query);
            $res = $db->loadObject();
            $result = $res->id;
        } catch (Exception $e) {
            JLog::add("[FILEMAKER CRON] Failed to get database join  Element Value in  $db_table " . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        return $result;
    }

    /**
     * @param $db_table_name
     * @param $elements_names
     * @param $elements_values
     * @param $fnum
     * @param $user_id
     * @return int
     * This function is used to insert data into a table
     */
    public function insertIntoATable($db_table_name, $elements_names, $elements_values, $fnum = 0, $user_id = 0):int
    {
        $inserted_id = 0;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear();
        $query->insert($db->quoteName($db_table_name))
            ->columns($elements_names)
            ->values(implode(",", $elements_values));
        try {
            $db->setQuery($query);
            $db->execute();
            $inserted_id = $db->insertid();
        } catch (Exception $e) {
            JLog::add("[FILEMAKER CRON] Failed to insert row in to table $db_table_name for fnum $fnum - $user_id" . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        return $inserted_id;
    }

    public function transformToAssociativeArray($array): array
    {
        $associativeArray = array();
        foreach ($array as $item) {
            $key = key($item);
            $value = reset($item);
            $associativeArray[$key] = $value;
        }
        return $associativeArray;

    }

    /**
     * @param $mapped_columns
     * @param $label
     * @return array
     * This function is used to get all elements with a specific FileMaker Form label
     */
    public function getAssocElementsWithFileMakerFormLabel($mapped_columns, $label): array
    {
        $values = [];
        foreach ($mapped_columns as $row) {
            if ($row->filemaker_form_label == $label) {
                $values[] = $row;
            }
        }
        return $values;
    }

    /**
     * @param $uuid
     * @return mixed|string|null
     * This function is used to get File Row in a table in campaign_candidature table based FileMaker uuid
     */
    public function getEmundusFile($uuid)
    {
        $file = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__emundus_campaign_candidature'))
            ->where($db->quoteName('uuid') . '=' . $db->quote($uuid));
        try {
            $db->setQuery($query);
            $file = $db->loadObject();
        } catch (Exception $e) {
            JLog::add("[FILEMAKER CRON] Failed to check if file already exist for " . $uuid . " " . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        return $file;

    }

    /**
     * @param $fnum
     * @param $db_table_name
     * @return mixed|string|null
     * This function is used to get File Row in a table
     */
    private function getRowInTable($fnum, $db_table_name)
    {
        $file = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName($db_table_name))
            ->where($db->quoteName('fnum') . '=' . $db->quote($fnum));
        try {
            $db->setQuery($query);
            $file = $db->loadObject();
        } catch (Exception $e) {
            JLog::add("[FILEMAKER CRON] Failed to check if file already exist for fnum" . $fnum . " " . $e->getMessage(), JLog::ERROR, 'com_emundus');
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

        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.info.php'], JLog::INFO);
        JLog::addLogger(['text_file' => 'com_emundus.error.php'], JLog::ERROR);
        $mapped_columns = $this->retrieveMappingColumnsData();
        $offset = 1;
        $limit = 20;
        $returned_count = -1;
        while ($returned_count != 0) {
            $find_records_response = $this->getRecords($limit, $offset, $this->getParams()->get('admin_step'));
            if (!empty($find_records_response)) {
                $data_info = $find_records_response->dataInfo;
                $returned_count = $data_info->returnedCount;
                $offset += $limit;

                $this->createFiles($find_records_response->data, $mapped_columns);
            } else {
                $returned_count = 0;
            }
        }
        return true;
    }


}
