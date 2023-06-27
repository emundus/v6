<?php


namespace classes\api;
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');

/**
 * @package     com_emundus
 * @subpackage  api
 * @author    eMundus.fr - Merveille Gbetegan
 * @copyright (C) 2023 eMundus SOFTWARE. All rights reserved.
 * @license    GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use DateTime;
use DateTimeZone;
use EmundusModelApplication;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\MultipartStream;
use JComponentHelper;
use JFactory;
use JLog;
use stdClass;

defined('_JEXEC') or die('Restricted access');

class FileMaker
{
    /**
     * @var array $auth
     */
    private $auth = array();


    /**
     * @var array $headers
     */
    private $headers = array();

    /**
     * @var string $baseUrl
     */
    private $baseUrl = '';

    /**
     * @param GuzzleClient $client
     */
    private $client = null;

    /**
     * @var string[]
     */
    private static $availaibleZwForms = array('zWEB_FORMULAIRES', 'zWEB_FORMULAIRES_RECETTES', 'zWEB_FORMULAIRES_PLANNING',
        'zWEB_FORMULAIRES_PARTICIPANTS', 'zWEB_FORMULAIRES_PARTENAIRES', 'zWEB_FORMULAIRES_DEPENSES', 'zWEB_FORMULAIRES_AUDIENCE', 'zWEB_FORMULAIRES_AIDES');


    private $maxAttempt = 0;

    /**
     * @return int
     */
    public function getMaxAttempt(): int
    {
        return $this->maxAttempt;
    }

    /**
     * @param int $maxAttempt
     */
    public function setMaxAttempt(): void
    {
        ++$this->maxAttempt;
    }

    /**
     * @return string[]
     */
    public static function getAvailaibleZwForms(): array
    {
        return self::$availaibleZwForms;
    }


    public function __construct()
    {
        JLog::addLogger(['text_file' => 'com_emundus.file_maker.php'], JLog::ALL, 'com_emundus.file_maker');

        $this->setAuth();
        $this->setHeaders();
        $this->setBaseUrl();

        $this->client = new GuzzleClient([
            'base_uri' => $this->getBaseUrl(),
            'verify' => false
        ]);


        if (empty($this->auth['bear_token'])) {
            $this->loginApi();
        }
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }


    public function setBaseUrl(): void
    {
        $config = JComponentHelper::getParams('com_emundus');
        $this->baseUrl = $config->get('file_maker_api_base_url', 'https://10.0.0.100/fmi/data/v2/databases/IF_dataAPI');
    }

    /**
     * @return null
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param null $client
     */
    public function setClient($client): void
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($isForLogin = false): void
    {
        $auth = $this->getAuth();

        $this->headers = array(
            'Authorization' => $isForLogin === false ? 'Bearer ' . $auth['bear_token'] : 'Basic ' . $auth['basic_token'],
            'Content-Type' => 'application/json'
        );

    }

    /**
     * @return array
     */
    public function getAuth(): array
    {
        return $this->auth;
    }


    public function setAuth(): void
    {
        $session = JFactory::getSession();
        $config = JComponentHelper::getParams('com_emundus');

        $this->auth['bear_token'] = $session->get('file_maker_bear_token', '');
        $this->auth['basic_token'] = $config->get('file_maker_api_basic_auth_token', '');

    }


    private function get($url, $params = array())
    {
        try {
            $url_params = http_build_query($params);
            $url = !empty($url_params) ? $url . '?' . $url_params : $url;
            $response = $this->client->get($url, ['headers' => $this->getHeaders()]);
            $this->maxAttempt = 0;
            return json_decode($response->getBody());
        } catch (\Exception $e) {

            if ($e->getCode() == 401 && $this->getMaxAttempt() < 3) {
                $this->loginApi();
                $this->get($url, $params);
                $this->setMaxAttempt();
            }
            JLog::add('[GET] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');
            return $e->getMessage();
        }
    }

    private function post($url, $query_body_in_json = null)
    {
        $response = '';

        try {

            $response = $query_body_in_json !== null ? $this->client->post($url, ['body' => $query_body_in_json, 'headers' => $this->getHeaders()]) : $this->client->post($url, ['headers' => $this->getHeaders()]);

            $response = json_decode($response->getBody());
            $this->maxAttempt = 0;

        } catch (\Exception $e) {

            if ($e->getCode() == 401 && $this->getMaxAttempt() < 3) {
                $this->loginApi();
                $this->post($url, $query_body_in_json);
                $this->setMaxAttempt();
            }

            JLog::add('[POST] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');

            $response = $e->getMessage();

        }

        return $response;
    }

    private function upload($url, $filePath, $fileName)
    {
        $response = '';
        $auth = $this->getAuth();

        try {

            $file = fopen($filePath, 'r');


            $boundary = '--------------------------' . microtime(true); // Generate a unique boundary

            $stream = new MultipartStream([
                [
                    'name' => 'filename',
                    'contents' => $fileName,

                ],
                [
                    'name' => 'upload',
                    'contents' => $file
                ]
            ], $boundary);


            $response = $this->client->post($url,
                ['headers' => ['Authorization' => 'Bearer ' . $auth['bear_token'],
                    'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
                ],
                    "body" => $stream
                ]);

            $response = json_decode($response->getBody());


            $this->maxAttempt = 0;
        } catch (\Exception $e) {

            if ($e->getCode() == 401 && $this->getMaxAttempt() < 3) {
                $this->loginApi();
                $this->upload($url, $filePath, $fileName);
                $this->setMaxAttempt();
            }

            JLog::add('[UPLOAD] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');
            $response = $e->getMessage();
        }

        fclose($file);

        return $response;
    }


    private function patch($url, $query_body_in_json)
    {
        $response = '';


        try {

            $response = $this->client->patch($url, ['body' => $query_body_in_json, 'headers' => $this->getHeaders()]);

            $this->maxAttempt = 0;

            $response = json_decode($response->getBody());


        } catch (\Exception $e) {
            if ($e->getCode() == 401 && $this->getMaxAttempt() < 3) {
                $this->loginApi();
                $this->patch($url, $query_body_in_json);
                $this->setMaxAttempt();
            }


            JLog::add('[PATCH] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');

            $response = $e->getMessage();
        }


        return $response;
    }

    private function delete($url)
    {
        $response = '';

        try {

            $response = $this->client->delete($url);
            $response = json_decode($response->getBody());
            $this->maxAttempt = 0;
        } catch (\Exception $e) {

            if ($e->getCode() == 401 && $this->getMaxAttempt() < 3) {
                $this->loginApi();
                $this->delete($url);
                $this->setMaxAttempt();
            }
            JLog::add('[DELETE] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');
            $response = $e->getMessage();
        }

        return $response;
    }

    public function getRecords($recordId = null, $portal = array())
    {
        $url = 'layouts/zWEB_FORMULAIRES/records';
        if ($recordId !== null && !empty($portal)) {
            $url = $url . '/' . $recordId . $portal;
        }
        if ($recordId !== null) {
            $url = $url . '/' . $recordId;
        }
        $records_response = $this->get($url);

        $records = $records_response->response;

        return $records;
    }


    private function loginApi(): void
    {


        $this->setHeaders(true);
        $login_response = $this->post("sessions");

        if ($login_response->messages[0]->code == "0") {
            $session = JFactory::getSession();
            $session->set('file_maker_bear_token', $login_response->response->token);
            $this->setAuth();
            $this->setHeaders();

        } else {

            JLog::add('[FILE_MAKER_API_LOGIN] Failed to login due do  ' . json_encode($login_response->messages), JLog::ERROR, 'com_emundus.file_maker');

        }


    }

    private function logoutApi()
    {

        $session = JFactory::getSession();

        $logout_response = $this->delete("sessions/" . $session->get('file_maker_bear_token'));
        $session->set('file_maker_bear_token', '');

        return $logout_response;
    }

    public function findRecord($limit = 50, $offset = 1, $adminStep = "", $uuidConnect = "null", $zWebFormType = "zWEB_FORMULAIRES", $sort = array())
    {

        if (in_array($zWebFormType, $this->getAvailaibleZwForms())) {
            if (!empty($uuidConnect) || !empty($adminStep)) {

                $url = "layouts/" . $zWebFormType . "/_find";
                $queryBody = ["query" => array([
                    //empty($zWebFormType) ? "uuidConnect" : "zWEB_FORMULAIRES::uuidConnect" => $uuidConnect,
                    //empty($zWebFormType) ? "uuidConnect" : "zWEB_FORMULAIRES::uuidConnect" => $uuidConnect,
                    "Admin_Step" => $adminStep,

                ]),
                    "limit" => $limit,
                    "offset" => $offset
                ];

                $record_response = $this->post($url, json_encode($queryBody));


                return $record_response->response;


            } else {

                JLog::add('[FILE_MAKER]  Empty uuidConnect passed to findRecord method  ', JLog::ERROR, 'com_emundus.file_maker');

                return 0;

            }
        } else {
            throw new Exception('Invalid zFORM_TYPE. It shoulbe one of ' . json_encode($this->getAvailaibleZwForms()));
        }

    }

    public function createRecord($queryBody, $file_maker_form = "zWEB_FORMULAIRES", $fnum, $uuid, $uuidConnect, $status)
    {
        $url = "layouts/" . $file_maker_form . "/records";

        $create_record_response = $this->post($url, json_encode($queryBody));
        $response = "";

        if (!empty($create_record_response->response)) {
            $response = $create_record_response->response;
            $message = $response;
            $action_status = 1;
        } else {
            $action_status = 0;
            $message = $create_record_response;
            $response = $message;
        }

        $this->logActionIntoEmundusFileMakerlog(1, $fnum, $uuid, $uuidConnect, $status, json_encode($queryBody), $action_status, $url, $message);
        return $response;
    }


    public function updateRecord($recordId, $queryBody, $filemakeform = "zWEB_FORMULAIRES", $fnum, $uuid, $uuidConnect, $status)
    {

        if (!empty($recordId)) {

            $url = "layouts/" . $filemakeform . "/records/" . $recordId;
            $update_record_response = $this->patch($url, json_encode($queryBody));
            $response = "";


            if (!empty($update_record_response->response)) {
                $response = $update_record_response;
                $message = $response;
                $action_status = 1;
            } else {
                $action_status = 0;
                $message = $update_record_response;
                $response = $message;
            }

            $this->logActionIntoEmundusFileMakerlog(2, $fnum, $uuid, $uuidConnect, $status, json_encode($queryBody), $action_status, $url, $message);

            return $response;


        } else {

            throw new Exception('Record Id could not be empty');
        }
    }

    public function getMetaDatazWebFroms($zWebFormType)
    {
        if (in_array($zWebFormType, $this->getAvailaibleZwForms())) {
            if (!empty($zWebFormType)) {

                $url = "layouts/" . $zWebFormType;

                $meta_data_response = $this->get($url);
                return $meta_data_response->response;

            } else {

                JLog::add('[FILE_MAKER]  Unable to load metadata for ' . $zWebFormType, JLog::ERROR, 'com_emundus.file_maker');

                return 0;

            }
        } else {
            throw new Exception('Invalid zFORM_TYPE. It shoulbe one of ' . json_encode($this->getAvailaibleZwForms()));
        }

    }

    public function executeFormValidationScriptOnFileMaker($uuidConnect, $fnum, $uuid, $status)
    {
        $url = "layouts/zWEB_FORMULAIRES/script/zWebFormulaire_Validation?script.param=" . $uuidConnect;

        $result = $this->get($url);

        if (!empty($result->response)) {
            $response = $result->response;
            $message = $response;
            $action_status = 1;
        } else {
            $action_status = 0;
            $message = $result;
            $response = $message;
        }

        $this->logActionIntoEmundusFileMakerlog(3, $fnum, $uuid, $uuidConnect, $status, NULL, $action_status, $url, $message);


        return $response;
    }

    public function uploadAllAssocAttachementsAssocToFile($fnum, $applicant_id, $recordId)
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        $query->select('esa.filemaker, eu.filename, eu.local_filename')
            ->from($db->quoteName('#__emundus_uploads', 'eu'))
            ->leftJoin('#__emundus_setup_attachments AS esa ON eu.attachment_id = esa.id')
            ->where($db->quoteName('eu.fnum') . '=' . $db->quote($fnum))
            ->andWhere($db->quoteName('esa.sync') . '= 1');
        $db->setQuery($query);

        try {
            $files = $db->loadObjectList();
            foreach ($files as $file) {
                $file_path = 'images/emundus/files/' . $applicant_id . "/" . $file->filename;
                try {

                    $response = $this->uploadAttachment($recordId, $file_path, $file->filename, $file->filemaker);


                } catch (Exception $e) {

                    JLog::add("[FILEMAKER ] Filed to Upload Attachement to Filemaker " . $fnum . " " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
                    return 0;
                }
            }
            return "OK";

        } catch (Exception $e) {

            JLog::add("[FILEMAKER ] Failed Retrieve File Inofrmation such as step and uuid before post to api" . $fnum . " " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }

    }

    public function uploadAttachment($recordId, $filePath, $fileName, $filemakername)
    {
        if (!empty($fileName) && !empty($filePath)) {

            $url = "layouts/zWEB_FORMULAIRES/records/" . $recordId . "/containers/" . $filemakername . "/1";

            $upload_response = $this->upload($url, $filePath, $fileName);

            return $upload_response->response;
        } else {
            throw new Exception('Filename and Filed Path can\'t be empty');
        }
    }

    public function retrieveMappingColumnsData($step)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $mapping_data = array();
        $config = JComponentHelper::getParams('com_emundus');

        $query->select('filemaker_label,emundus_form_id,portal_data_group_id')
            ->from($db->quoteName($config->get('file_maker_emundus_forms_mapping_table_name')))
            ->where($db->quoteName('step') . "=" . $step);
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


                    $mapping_data_row = new \stdClass();
                    $mapping_data_row->filemaker_form_label = $key;

                    $mapping_data_row->form_id = $sub_row["emundus_form_id"];
                    $mapping_data_row->portal_data_emundus_group_id = $sub_row["portal_data_group_id"];
                    $mapping_data_row->groups_id = array();
                    $mapping_data_row->groups = array();
                    foreach ($result as $val) {
                        $group = new \stdClass();
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

                    $mapping_data_row->elements = $this->retrieveAssociatedElementsWithgroup($mapping_data_row->groups_id, $step);
                    $mapping_data[] = $mapping_data_row;

                }

            }


            return $mapping_data;


        } catch (\Exception $e) {

            JLog::add('[FABRIK CRON FILEMAKER retrieveMappingColumnsData] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return [];
        }

    }


    public function retrieveAssociatedElementsWithgroup($groups_id, $step)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $config = JComponentHelper::getParams('com_emundus');

        $query->select('jfe.*,zfe.file_maker_attribute_name')
            ->from($db->quoteName('jos_fabrik_elements', 'jfe'))
            ->leftJoin($config->get('file_maker_emundus_attribute_mapping_table_name') . ' AS zfe ON zfe.file_maker_assoc_emundus_element = jfe.id')
            //->leftJoin('zweb_formulaires_mapping_938_repeat AS zfe ON zfe.file_maker_assoc_emundus_element = jfe.id')
            ->where('jfe.group_id IN (' . implode(',', $groups_id) . ')')
            ->andWhere('jfe.published = 1')
            ->andWhere('zfe.step =' . $step);


        $db->setQuery($query);

        try {
            return $db->loadObjectList();

        } catch (\Exception $e) {

            JLog::add('[FILEMAKER retrieveAssociatedElementsWithgroup] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');

            return [];
        }

    }


    public function preparePortalDataAndGenralLayoutBeforeSendToFileMaker($zweb_form_name, $mapped_columns, $fnum, $isPortalDataForm = true)
    {
        //$file_maker_api = new FileMaker();
        try {
            $metaDatas = $this->getMetaDatazWebFroms($zweb_form_name);

        } catch (\Exception $e) {
            JLog::add('[FILE_MAKER ] Failed to get Metada ' . $zweb_form_name . '  ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return $e->getMessage();
        }


        $zweb_forms_elements = $this->searchMappedElementsByFileMakerFormLabel($mapped_columns, $zweb_form_name);


        $m_application = new EmundusModelApplication();

        $temp_records_mapping = [];


        if ($isPortalDataForm === true) {
            $recordIdMetaData = new \stdClass();
            $recordIdMetaData->name = "recordId";
            $emundusIdMetaData = new \stdClass();
            $emundusIdMetaData->name = "id";
            $metaDatas->fieldMetaData[] = $recordIdMetaData;
            $metaDatas->fieldMetaData[] = $emundusIdMetaData;
            $group_jointures_params = $this->retrieveJointureInformationOfRepeatGroup($zweb_forms_elements[0]->portal_data_emundus_group_id);


        }


        foreach ($metaDatas->fieldMetaData as $data) {

            foreach ($zweb_forms_elements as $row) {

                $searchValue = $data->name;
                $matching_elements = array_values(array_filter($row->elements, function ($object) use ($searchValue) {
                    return $object->file_maker_attribute_name === $searchValue;
                }));


                if (!empty($matching_elements)) {

                    foreach ($matching_elements as $element_row) {

                        $value = $m_application->getValuesByElementAndFnum($fnum, $element_row->id, $row->form_id, '');


                        if ($isPortalDataForm === true) {

                            if (intval($element_row->group_id) === intval($row->portal_data_emundus_group_id)) {

                                if ($data->name === "recordId") {

                                    $temp_records_mapping[] = array("recordId_emundus_element_name" => $element_row->name);
                                }
                                switch ($element_row->plugin) {
                                    case "date":
                                    case "birthday":
                                        $values = explode(",", $value);
                                        $reformatted = array_map(function ($date_value) {
                                            $dateString = str_replace('.', '-', $date_value);
                                            $date = DateTime::createFromFormat('d-m-Y', $dateString);
                                            if ($date !== false) {

                                                return $date->format('m-d-Y');

                                            } else {
                                                return $dateString;
                                            }
                                        }, $values);
                                        $temp_records_mapping[] = array("" . $zweb_form_name . "::" . $data->name . "" => $reformatted);
                                        break;
                                    default:
                                        $temp_records_mapping[] = array("" . $data->name === "id" || $data->name === "recordId" ? $data->name : $zweb_form_name . "::" . $data->name . "" => explode(",", $value));
                                        break;

                                }

                                $temp_records_mapping[] = array("db_table" => $group_jointures_params->table_join);

                            }
                        } else {

                            switch ($element_row->plugin) {
                                case "date":
                                case "birthday":
                                    $dateString = str_replace('.', '-', $value);
                                    $date = DateTime::createFromFormat('d-m-Y', $dateString);
                                    $reformatted_date = $date !== false ? $date->format('m-d-Y') : $dateString;
                                    $temp_records_mapping[] = array("" . $zweb_form_name . "::" . $data->name . "" => $reformatted_date);
                                    break;
                                default:
                                    $temp_records_mapping[] = array("" . $data->name . "" => $value);
                                    break;
                            }

                        }


                    }
                }


            }
        }


        $array = $this->transformToAssociativeArray($temp_records_mapping);


        if ($isPortalDataForm == true) {


            $keys = array_keys($array);

            $arraySize = !empty($keys[0]) ? count($array[$keys[0]]) : 0;


            $finalArray = array();

            for ($i = 0; $i < $arraySize; $i++) {
                $tempArray = array();
                foreach ($keys as $key) {
                    $value = $key === "db_table" || $key === "recordId_emundus_element_name" ? $array[$key] : $array[$key][$i];

                    $tempArray[$key] = $value == NULL ? "" : $value;
                }

                $finalArray[] = $tempArray;
            }


            return $this->removeAllTuplesWhereAllValuesAreEmpty($finalArray);

            //return $finalArray;
        } else {

            return $array;

        }

    }

    public function removeAllTuplesWhereAllValuesAreEmpty($array)
    {

        $emptyIndexes = array_keys(array_filter($array, function ($assocArray) {
            unset($assocArray["db_table"], $assocArray["recordId_emundus_element_name"], $assocArray["id"]);
            return count(array_filter($assocArray)) === 0;
        }));

        foreach ($emptyIndexes as $index) {
            unset($array[$index]);
        }


        return $array;
    }

    public function checkIfTheIsPortalDataWhereTupleRecordsId_IsEmptyAndCreateTheRecord($array, $filemakerform, $uuidFormulaires, $fnum, $uuidConnect, $status)
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $emptyIndexes = array_keys(array_filter($array, function ($assocArray) {
            return empty($assocArray["recordId"]);
        }));

        //Pour chaque index je crée le record et je update recordId dans la table associée;


        foreach ($emptyIndexes as $index) {
            $record = $array[$index];

            $db_table = $record["db_table"];
            $emundus_recordId_element_name = !empty($record["recordId_emundus_element_name"]) ? $record["recordId_emundus_element_name"] : null;
            $emundus_id = !empty($record["id"]) ? $record["id"] : null;
            unset($record["recordId"]);
            unset($record["db_table"]);

            if (!empty($record["id"])) {
                unset($record["id"]);
            }
            if (!empty($record["recordId_emundus_element_name"])) {
                unset($record["recordId_emundus_element_name"]);
            }


            $records_key = array_map(function ($key) use ($filemakerform) {
                return str_replace($filemakerform . "::", "", $key);
            }, array_keys($record));

            $record = array_combine($records_key, $record);
            $record["uuidFormulaires"] = $uuidFormulaires;

            $queryBody = array("fieldData" => $record);
            //if (!empty($emundus_id)) {

            $response = $this->createRecord($queryBody, $filemakerform, $fnum, $uuidFormulaires, $uuidConnect, $status);

            if (!empty($response->recordId)) {
                ($array[$index])["recordId"] = $response->recordId;

                $query->clear()
                    ->update($db->quoteName($db_table))
                    ->set($db->quoteName($emundus_recordId_element_name) . ' = ' . $db->quote($response->recordId))
                    ->where($db->quoteName('id') . "=" . $emundus_id);
                $db->setQuery($query);

                try {
                    $db->execute();

                } catch (\Exception $e) {


                    JLog::add('[FILE_MAKER ] Failed to update recordId on table  ' . $db_table . ' wehre id = ' . $emundus_id . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
                }
            }
            // }

        }

        //Here I remove all unsable keys for posting data to filemakeer api;
        $finalArray = array_map(function ($tuple) {
            unset($tuple["db_table"]);
            unset($tuple["recordId_emundus_element_name"]);
            unset($tuple["id"]);
            unset($tuple["id"]);

            return $tuple;
        }, $array);


        return $finalArray;
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

    public function logActionIntoEmundusFileMakerlog($action_type, $fnum, $uuid, $uuidConnect, $status, $params = "", $action_status, $endpoint_url, $response_message)
    {

        $db = JFactory::getDbo();
        $config = JFactory::getConfig();
        $timezone = new DateTimeZone($config->get('offset'));
        $now = JFactory::getDate()->setTimezone($timezone);
        $values = [$db->quote($now), $db->quote($action_type), $db->quote($fnum), $db->quote($uuid), $db->quote($uuidConnect), $db->quote($status), $db->quote($params), $db->quote($action_status), $db->quote($endpoint_url), $db->quote(json_encode($response_message))];


        $query = $db->getQuery(true);
        $query->clear();
        $query->insert($db->quoteName('jos_emundus_filemaker_logs'))
            ->columns(['date_time,action,fnum,filemaker_uuid,filemaker_uuidConnect,status,params,action_status,filemaker_endpoint_url,endpoint_called_result_messsage'])
            ->values(implode(",", $values));

        $db->setQuery($query);

        try {

            $db->execute();


        } catch (Exception $e) {

            JLog::add("[FILEMAKER] Failed to insert into emundus_filemaker_logs table " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');

        }
    }

    public function retrieveJointureInformationOfRepeatGroup($group_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear();
        $query->select('join_from_table,table_join,table_join_key,table_key')
            ->from($db->quoteName('jos_fabrik_joins'))
            ->where('group_id =  ' . $group_id);
        $db->setQuery($query);
        try {
            return $db->loadObject();

        } catch (Exception $e) {
            JLog::add("[FILEMAKER] Failed to get table joins params for repeat group  $group_id " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker');
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

            return $result;
        } catch (Exception $e) {

            JLog::add("[FILEMAKER CRON] Failed to get database join  Element Value in  $dbtable " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');
            return 0;
        }
    }

    public function formatCheckBoxValues($string)
    {
        $formattedString = str_replace(['<li>', ' - '], '', $string);

        return str_replace('</li>', "\r", $formattedString);
    }

    public function retrieveCountryReferentials(): void
    {

        $url = "layouts/zWEB_VALEURS_PAYS/_find";
        $queryBody = ["query" => array([
            "estActif" => 1,
        ]),
            "limit" => 500,
        ];

        $countries_list_response = $this->post($url, json_encode($queryBody));


        if (!empty($countries_list_response->response)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            foreach (($countries_list_response->response)->data as $data) {
                $query->clear();
                $query->select('*')
                    ->from($db->quoteName('data_country_institut_francais'))
                    ->where($db->quoteName('uuid') . "=" . $db->quote(($data->fieldData)->uuid));
                $db->setQuery($query);



                try {
                    $result = $db->loadObject();

                    if(!empty($result)){
                        $this->updateCountryReferentials($result->id,$data->fieldData);
                    } else{

                        $this->addCountryToReferential($data->fieldData);
                    }

                } catch (Exception $e) {

                    JLog::add("[FILEMAKER CRON] Failed to check if already exist country in methode retrieveCountryReferentials   " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');

                }


            }

        }

    }

    public function updateCountryReferentials($id, $data): void
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query ->update($db->quoteName('data_country_institut_francais'))
            ->set($db->quoteName('label_fr') . ' = ' . $db->quote($data->Libcog))
            ->set($db->quoteName('label_en') . ' = ' . $db->quote(empty($data->LibcogAnglais) ? $data->Libcog : $data->LibcogAnglais))
            ->set($db->quoteName('estActif') . ' = ' . $db->quote($data->EstActif))
            ->set($db->quoteName('published') . ' = ' . $db->quote($data->EstActif))
            ->where($db->quoteName('id') . "=" . $db->quote($id));

        $db->setQuery($query);

        try {

            $db->execute();

        } catch (Exception $e) {

            JLog::add("[FILEMAKER CRON] Failed to update country in method updateCountryReferential  " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');

        }
    }

    public function addCountryToReferential($data): void
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->clear();
        $query->insert($db->quoteName('data_country_institut_francais'))
            ->columns(['label_fr,label_en,uuid,estActif,published'])
            ->values(implode(",", [$db->quote($data->Libcog), $db->quote(empty($data->LibcogAnglais) ? $data->Libcog : $data->LibcogAnglais), $db->quote($data->uuid), $db->quote($data->EstActif), $db->quote($data->EstActif)]));

        $db->setQuery($query);

        try {

            $db->execute();


        } catch (Exception $e) {

            JLog::add("[FILEMAKER CRON] Failed to add country in method addCountryToReferential   " . $e->getMessage(), JLog::ERROR, 'com_emundus.filemaker_fabrik_cron');

        }
    }


}
