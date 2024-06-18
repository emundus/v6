<?php


namespace classes\api;

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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use phpDocumentor\Reflection\Types\Boolean;
use stdClass;

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'fabrik.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'users.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');

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


	/**
	 * @var int
	 */
    private $maxAttempt = 0;

	private $db;

	private $emConfig;

    public function __construct($login = true)
    {
        Log::addLogger(['text_file' => 'com_emundus.file_maker.php'], Log::ALL, 'com_emundus.file_maker');

		$this->db = JFactory::getDbo();
		$this->emConfig = ComponentHelper::getParams('com_emundus');

        $this->setAuth();
        $this->setHeaders();
        $this->setBaseUrl();

        $this->client = new GuzzleClient([
            'base_uri' => $this->getBaseUrl(),
            'verify' => false
        ]);


        if (empty($this->auth['bear_token']) && $login) {
            $this->loginApi();
        }
    }

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




    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }


    public function setBaseUrl(): void
    {
        $this->baseUrl = $this->emConfig->get('file_maker_api_base_url');
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

        $this->auth['bear_token'] = $session->get('file_maker_bear_token', '');
        $this->auth['basic_token'] = $this->emConfig->get('file_maker_api_basic_auth_token', '');

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
            Log::add('[GET] ' . $e->getMessage(), Log::ERROR, 'com_emundus.file_maker');
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
            Log::add('[POST] ' . $e->getMessage(), Log::ERROR, 'com_emundus.file_maker');
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

            Log::add('[UPLOAD] ' . $e->getMessage(), Log::ERROR, 'com_emundus.file_maker');
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
            Log::add('[PATCH] ' . $e->getMessage(), Log::ERROR, 'com_emundus.file_maker');
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
            Log::add('[DELETE] ' . $e->getMessage(), Log::ERROR, 'com_emundus.file_maker');
            $response = $e->getMessage();
        }

        return $response;
    }

    public function getRecords($recordId = null, $portal = array())
    {
        $url = 'layouts/zWEB_FORMULAIRES/records';
        if ($recordId !== null ) {
            $url = $url . '/' . $recordId;
        }

        if (!empty($portal)) {
            $url = $url . '?portal=' . $portal;
        }

        $records_response = $this->get($url);

        $records = $records_response->response;

        return $records;
    }


    private function loginApi(): boolean
    {
        $this->setHeaders(true);
        $login_response = $this->post("sessions");

        if ($login_response->messages[0]->code == "0") {
            $session = JFactory::getSession();
            $session->set('file_maker_bear_token', $login_response->response->token);
            $this->setAuth();
            $this->setHeaders();
            return true;
        } else {
            Log::add('[FILE_MAKER_API_LOGIN] Failed to login due do  ' . json_encode($login_response->messages), Log::ERROR, 'com_emundus.file_maker');
            return false;
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
        $response = 0;
        if (in_array($zWebFormType, $this->getAvailaibleZwForms())) {
            if (!empty($uuidConnect) || !empty($adminStep)) {

                $url = "layouts/" . $zWebFormType . "/_find";
                $queryBody = ["query" => array([
                    "Admin_Step" => $adminStep,
                ]),
                    "limit" => $limit,
                    "offset" => $offset
                ];
                $record_response = $this->post($url, json_encode($queryBody));
                $response = $record_response->response;
            } else {
                Log::add('[FILE_MAKER]  Empty uuidConnect passed to findRecord method  ', Log::ERROR, 'com_emundus.file_maker');
            }
            return $response;
        } else {
            throw new Exception('Invalid zFORM_TYPE. It shoulbe one of ' . json_encode($this->getAvailaibleZwForms()));
        }

    }

    public function createRecord($queryBody, $file_maker_form = "zWEB_FORMULAIRES", $fnum, $uuid, $uuidConnect, $status)
    {
        $response = '';
        $url = "layouts/" . $file_maker_form . "/records";
        $create_record_response = $this->post($url, json_encode($queryBody));
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
            $response = '';
            $url = "layouts/" . $filemakeform . "/records/" . $recordId;
            $update_record_response = $this->patch($url, json_encode($queryBody));
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
        $response = 0;
        if (in_array($zWebFormType, $this->getAvailaibleZwForms())) {
            if (!empty($zWebFormType)) {

                $url = "layouts/" . $zWebFormType;

                $meta_data_response = $this->get($url);
                $response = $meta_data_response->response;

            } else {
                Log::add('[FILE_MAKER]  Unable to load metadata for ' . $zWebFormType, Log::ERROR, 'com_emundus.file_maker');
            }
            return $response;
        } else {
            throw new Exception('Invalid zFORM_TYPE. It shoulbe one of ' . json_encode($this->getAvailaibleZwForms()));
        }
    }

    public function deleteAllLinkedData($uuidConnect, $fnum, $uuid, $status,$layout,$scriptName = "zWebFormulaire_Delete_AllLinkedData" ){
        //$url = "layouts/".$layout."/script/".$scriptName."?script.param=" . $uuidConnect;
        $url = "layouts/".$layout."/script/".$scriptName."?script.param=" . $uuid;
        $res = $this->get($url);
        if (!empty($res->response)) {
            $response = $res->response;
            $message = $response;
            $action_status = 1;
        } else {
            $action_status = 0;
            $message = $res;
            $response = $message;
        }
        $this->logActionIntoEmundusFileMakerlog(-1, $fnum, $uuid, $uuidConnect, $status, NULL, $action_status, $url, $message);

        return $response;
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
        $result = false;

        $query = $this->db->getQuery(true);
        $query->select('esa.filemaker, eu.filename, eu.local_filename')
            ->from($this->db->quoteName('#__emundus_uploads', 'eu'))
            ->leftJoin('#__emundus_setup_attachments AS esa ON eu.attachment_id = esa.id')
            ->where($this->db->quoteName('eu.fnum') . '=' . $this->db->quote($fnum))
            ->andWhere($this->db->quoteName('esa.sync') . '= 1');
        try {
            $this->db->setQuery($query);
            $files = $this->db->loadObjectList();
            foreach ($files as $file) {
                $file_path = 'images/emundus/files/' . $applicant_id . "/" . $file->filename;
                try {
                    $response = $this->uploadAttachment($recordId, $file_path, $file->filename, $file->filemaker);
                } catch (Exception $e) {
                    Log::add("[FILEMAKER ] Filed to Upload Attachement to Filemaker " . $fnum . " " . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
                }
            }
            $result = true;
        } catch (Exception $e) {
            Log::add("[FILEMAKER ] Failed Retrieve File Inofrmation such as step and uuid before post to api" . $fnum . " " . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }
        return $result;
    }

    public function uploadAttachment($recordId, $filePath, $fileName, $filemakername)
    {
        $response = '';
        if (!empty($fileName) && !empty($filePath)) {

            $url = "layouts/zWEB_FORMULAIRES/records/" . $recordId . "/containers/" . $filemakername . "/1";

            $upload_response = $this->upload($url, $filePath, $fileName);

            $response = $upload_response->response;
        } else {
            throw new Exception('Filename and Filed Path can\'t be empty');
        }

        return $response;
    }

    public function retrieveMappingColumnsData($step):array
    {
        $final_result = [];
	    $mapping_data = array();

		$step_properties = $this->getPropertiesByStep($step);

        $query = $this->db->getQuery(true);

        $query->select('filemaker_label,emundus_form_id,portal_data_group_id')
            ->from($this->db->quoteName($this->emConfig->get('file_maker_emundus_forms_mapping_table_name')))
            ->where($this->db->quoteName('step') . "=" . $step_properties->status);
        try {
            $this->db->setQuery($query);
            $result = $this->db->loadAssocList();
            $result_group_by_file_maker_attribute = $this->group_by("filemaker_label", $result);

            foreach ($result_group_by_file_maker_attribute as $key => $value) {
                foreach ($value as $sub_row) {
                    $query->clear();
                    $query->select('jfl.db_table_name,jfg.group_id,fgs.params,jfj.join_from_table,jfj.table_join,jfj.table_join_key,jfj.table_key')
                        ->from($this->db->quoteName('jos_fabrik_lists', 'jfl'))
                        ->leftJoin('jos_fabrik_formgroup AS jfg ON jfl.form_id = jfg.form_id')
                        ->leftJoin('jos_fabrik_joins AS jfj ON jfl.id = jfj.list_id')
                        ->leftJoin('jos_fabrik_groups AS fgs ON fgs.id = jfg.group_id')
                        ->where('jfl.form_id = ' . $sub_row["emundus_form_id"]);
                    $this->db->setQuery($query);
                    $result = $this->db->loadObjectList();
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
                    $mapping_data_row->elements = $this->retrieveAssociatedElementsWithGroup($mapping_data_row->groups_id, $step_properties->status);
                    $mapping_data[] = $mapping_data_row;
                }
            }
            $final_result = $mapping_data;
        } catch (\Exception $e) {
            Log::add('[FABRIK CRON FILEMAKER retrieveMappingColumnsData] ' . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }
        return $final_result;
    }


    public function retrieveAssociatedElementsWithGroup($groups_id, $step)
    {
        $associated_elements = [];

        $query = $this->db->getQuery(true);
        $query->select('jfe.*,zfe.file_maker_attribute_name')
            ->from($this->db->quoteName('jos_fabrik_elements', 'jfe'))
            ->leftJoin($this->emConfig->get('file_maker_emundus_attribute_mapping_table_name') . ' AS zfe ON zfe.file_maker_assoc_emundus_element = jfe.id')
            ->where('jfe.group_id IN (' . implode(',', $groups_id) . ')')
            ->andWhere('jfe.published = 1')
            ->andWhere('zfe.step =' . $step);
        try {
            $this->db->setQuery($query);
            $associated_elements =  $this->db->loadObjectList();
        } catch (\Exception $e) {
            Log::add('[FILEMAKER retrieveAssociatedElementsWithGroup] ' . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }
        return $associated_elements;
    }


    public function prepareFileMakerPayload($zweb_form_name, $mapped_columns, $fnum, $is_portal_data_form = true):array
    {
        $mapped_records = [];
        try {
            $meta_datas = $this->getMetaDatazWebFroms($zweb_form_name);
        } catch (\Exception $e) {
            Log::add('[FILE_MAKER ] Failed to get Metada ' . $zweb_form_name . '  ' . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }
        $zweb_forms_elements = $this->getAssocElementsWithFileMakerFormLabel($mapped_columns, $zweb_form_name);
        $m_application = new \EmundusModelApplication();
        $temp_records_mapping = [];
        if ($is_portal_data_form === true) {
            $recordId_meta_data = new \stdClass();
            $recordId_meta_data->name = "recordId";
            $emundusId_meta_data = new \stdClass();
            $emundusId_meta_data->name = "id";
            $meta_datas->fieldMetaData[] = $recordId_meta_data;
            $meta_datas->fieldMetaData[] = $emundusId_meta_data;
            $group_jointures_params = $this->retrieveJointureInformationOfRepeatGroup($zweb_forms_elements[0]->portal_data_emundus_group_id);
            if($group_jointures_params === false){
                throw new Exception('Unable to get jointure information for group id '.$zweb_forms_elements[0]->portal_data_emundus_group_id);
            }
        }

        foreach ($meta_datas->fieldMetaData as $data) {
            foreach ($zweb_forms_elements as $row) {
                $search_value = $data->name;
                $matching_elements = array_values(array_filter($row->elements, function ($object) use ($search_value) {
                    return $object->file_maker_attribute_name === $search_value;
                }));
                if (!empty($matching_elements)) {
                    foreach ($matching_elements as $element_row) {
                        $repeat_separator = $is_portal_data_form === true ? "$$$" : ",";
                        $value = $m_application->getValuesByElementAndFnum($fnum, $element_row->id, $row->form_id,1,[],null,true, $repeat_separator);
                        if ($is_portal_data_form === true) {
                            if (intval($element_row->group_id) === intval($row->portal_data_emundus_group_id)) {
                                if ($data->name === "recordId") {
                                    $temp_records_mapping[] = array("recordId_emundus_element_name" => $element_row->name);
                                }
                                switch ($element_row->plugin) {
                                    case "date":
                                    case "birthday":
                                        $values = explode($repeat_separator, $value);
                                        $reformatted = array_map(function ($date_value) {
                                            $dateString = str_replace('.', '-', $date_value);
                                            $date = DateTime::createFromFormat('d-m-Y', $dateString);
                                            if ($date !== false) {
                                                return $date->format('m-d-Y');
                                            } else {
                                                return "";
                                            }
                                        }, $values);
                                        $temp_records_mapping[] = array("" . $zweb_form_name . "::" . $data->name . "" => $reformatted);
                                        break;
                                    default:
                                        $values = explode($repeat_separator, $value);
                                        if(strpos($data->name,"Montant") !== false){
                                            $values = array_map(function($amount){
                                                return str_replace(",",".",$amount);
                                            },$values);
                                        }
                                        $temp_records_mapping[] = array("" . $data->name === "id" || $data->name === "recordId" ? $data->name : $zweb_form_name . "::" . $data->name . "" => $values);
                                        break;
                                }
                                $temp_records_mapping[] = array("db_table" =>  $group_jointures_params->table_join);
                            }
                        } else {
                            switch ($element_row->plugin) {
                                case "date":
                                case "birthday":
                                    $dateString = str_replace('.', '-', $value);
                                    $date = DateTime::createFromFormat('d-m-Y', $dateString);
                                    $reformatted_date = $date !== false ? $date->format('m-d-Y') : "";
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
        $mapped_records = $this->transformToAssociativeArray($temp_records_mapping);
        if ($is_portal_data_form == true) {
            $keys = array_keys($mapped_records);
            $mapped_records_size = !empty($keys[0]) ? count($mapped_records[$keys[0]]) : 0;
            $final_array = array();
            for ($i = 0; $i < $mapped_records_size; $i++) {
                $temp_array = array();
                foreach ($keys as $key) {
                    $value = $key === "db_table" || $key === "recordId_emundus_element_name" ? $mapped_records[$key] : $mapped_records[$key][$i];
                    $temp_array[$key] = $value == NULL ? "" : $value;
                }
                $final_array[] = $temp_array;
            }
            $mapped_records = $this->removeAllTuplesWhereAllValuesAreEmpty($final_array);
        }
        return $mapped_records;
    }

    public function removeAllTuplesWhereAllValuesAreEmpty($data):array
    {
        $empty_indexes = array_keys(array_filter($data, function ($assocArray) {
            unset($assocArray["db_table"], $assocArray["recordId_emundus_element_name"], $assocArray["id"]);
            return count(array_filter($assocArray)) === 0;
        }));
        foreach ($empty_indexes as $index) {
            unset($data[$index]);
        }
        return $data;
    }

    public function createPortalDataIfTupleRecordIdIsEmpty($data, $filemaker_form, $uuid_formulaire, $fnum, $uuid_connect, $status):array
    {

        $query = $this->db->getQuery(true);
        $empty_indexes = array_keys(array_filter($data, function ($assocArray) {
            return empty($assocArray["recordId"]);
        }));
        //Pour chaque index je crée le record et je update recordId dans la table associée;
        foreach ($empty_indexes as $index) {
            $record = $data[$index];
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
            $records_key = array_map(function ($key) use ($filemaker_form) {
                return str_replace($filemaker_form . "::", "", $key);
            }, array_keys($record));

            $record = array_combine($records_key, $record);
            $record["uuidFormulaires"] = $uuid_formulaire;

            $queryBody = array("fieldData" => $record);
            $response = $this->createRecord($queryBody, $filemaker_form, $fnum, $uuid_formulaire, $uuid_connect, $status);

            if (!empty($response->recordId)) {
                ($data[$index])["recordId"] = $response->recordId;
                $query->clear()
                    ->update($this->db->quoteName($db_table))
                    ->set($this->db->quoteName($emundus_recordId_element_name) . ' = ' . $this->db->quote($response->recordId))
                    ->where($this->db->quoteName('id') . "=" . $emundus_id);
                try {
                    $this->db->setQuery($query);
                    $this->db->execute();
                } catch (\Exception $e) {
                    Log::add('[FILE_MAKER ] Failed to update recordId on table  ' . $db_table . ' wehre id = ' . $emundus_id . ' ' . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
                }
            }
        }
        //Here I remove all unsable keys for posting data to filemakeer api;
        $finalArray = array_map(function ($tuple) {
            unset($tuple["db_table"]);
            unset($tuple["recordId_emundus_element_name"]);
            unset($tuple["id"]);
            unset($tuple["id"]);
            return $tuple;
        }, $data);
        return $finalArray;
    }

    public function getAssocElementsWithFileMakerFormLabel($mapped_columns, $label)
    {
        $values = [];
        foreach ($mapped_columns as $row) {
            if ($row->filemaker_form_label == $label) {
                $values[] = $row;
            }
        }
        return $values;
    }

    public function transformToAssociativeArray($array):array
    {
        $associativeArray = array();
        foreach ($array as $item) {
            $key = key($item);
            $value = reset($item);
            $associativeArray[$key] = $value;
        }
        return $associativeArray;
    }

    public function group_by($key, $data):array
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
        $result = false;

        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('UTC'));
        $now = $now->format('Y-m-d H:i:s');
        $values = [$this->db->quote($now), $this->db->quote($action_type), $this->db->quote($fnum), $this->db->quote($uuid), $this->db->quote($uuidConnect), $this->db->quote($status), $this->db->quote($params), $this->db->quote($action_status), $this->db->quote($endpoint_url), $this->db->quote(json_encode($response_message))];
        $query = $this->db->getQuery(true);
        $query->clear();
        $query->insert($this->db->quoteName('jos_emundus_filemaker_logs'))
            ->columns(['date_time,action,fnum,filemaker_uuid,filemaker_uuidConnect,status,params,action_status,filemaker_endpoint_url,endpoint_called_result_messsage'])
            ->values(implode(",", $values));
        try {
            $this->db->setQuery($query);
            $result = $this->db->execute();
        } catch (Exception $e) {
            Log::add("[FILEMAKER] Failed to insert into emundus_filemaker_logs table " . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }
        return $result;
    }

    public function retrieveJointureInformationOfRepeatGroup($group_id)
    {
        $result = false;

        $query = $this->db->getQuery(true);

        $query->select('join_from_table,table_join,table_join_key,table_key')
            ->from($this->db->quoteName('jos_fabrik_joins'))
            ->where('group_id =  ' . $group_id);
        try {
            $this->db->setQuery($query);
           $result = $this->db->loadObject();

        } catch (Exception $e) {
            Log::add("[FILEMAKER] Failed to get table joins params for repeat group  $group_id " . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker');
        }

        return $result;
    }

    public function retrieveDatabaseJoinElementValue($dbtable, $column_where, $needed)
    {
        $result = 0;

        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from($this->db->quoteName($dbtable))
            ->where($this->db->quoteName($column_where) . "=" . $this->db->quote($needed));
        try {
            $this->db->setQuery($query);
            $result = $this->db->loadObject();

        } catch (Exception $e) {
            Log::add("[FILEMAKER CRON] Failed to get database join  Element Value in  $dbtable " . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }

        return $result;
    }

    public function formatCheckBoxValues($string)
    {
        $formatted_string = str_replace(['<li>', ' - '], '', $string);
        return str_replace('</li>', "\r", $formatted_string);
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

            $query = $this->db->getQuery(true);
            foreach (($countries_list_response->response)->data as $data) {
                $query->clear();
                $query->select('*')
                    ->from($this->db->quoteName('data_country_institut_francais'))
                    ->where($this->db->quoteName('uuid') . "=" . $this->db->quote(($data->fieldData)->uuid));
                try {
                    $this->db->setQuery($query);
                    $result = $this->db->loadObject();
                    if(!empty($result)){
                        $this->updateCountryReferentials($result->id,$data->fieldData);
                    } else{
                        $this->addCountryToReferential($data->fieldData);
                    }

                } catch (Exception $e) {
                    Log::add("[FILEMAKER CRON] Failed to check if already exist country in methode retrieveCountryReferentials   " . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
                }
            }
        }
    }

    public function updateCountryReferentials($id, $data)
    {
        $result = false;

        $query = $this->db->getQuery(true);
        $query ->update($this->db->quoteName('data_country_institut_francais'))
            ->set($this->db->quoteName('label_fr') . ' = ' . $this->db->quote($data->Libcog))
            ->set($this->db->quoteName('label_en') . ' = ' . $this->db->quote(empty($data->LibcogAnglais) ? $data->Libcog : $data->LibcogAnglais))
            ->set($this->db->quoteName('estActif') . ' = ' . $this->db->quote($data->EstActif))
            ->set($this->db->quoteName('published') . ' = ' . $this->db->quote($data->EstActif))
            ->where($this->db->quoteName('id') . "=" . $this->db->quote($id));
        try {
            $this->db->setQuery($query);
            $result = $this->db->execute();
        } catch (Exception $e) {
            Log::add("[FILEMAKER CRON] Failed to update country in method updateCountryReferential  " . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');

        }
        return $result;
    }

    public function addCountryToReferential($data)
    {
        $result = false;

        $query = $this->db->getQuery(true);
        $query->clear();
        $query->insert($this->db->quoteName('data_country_institut_francais'))
            ->columns(['label_fr,label_en,uuid,estActif,published'])
            ->values(implode(",", [$this->db->quote($data->Libcog), $this->db->quote(empty($data->LibcogAnglais) ? $data->Libcog : $data->LibcogAnglais), $this->db->quote($data->uuid), $this->db->quote($data->EstActif), $this->db->quote($data->EstActif)]));
        try {
            $this->db->setQuery($query);
            $result = $this->db->execute();
        } catch (Exception $e) {
            Log::add("[FILEMAKER CRON] Failed to add country in method addCountryToReferential   " . $e->getMessage(), Log::ERROR, 'com_emundus.filemaker_fabrik_cron');
        }
        return $result;
    }

	public function createFiles($filesData, $mapped_columns)
	{
		$fnums = [];
		foreach ($filesData as $file) {
			if(is_array($file)) {
				$file = $this->array_to_object($file);
			}

			$email_attribute = $this->emConfig->get('file_maker_email_attribute','zWEB_FORMULAIRES_PROGRAMMATIONS::web_emailContact');
			$name_attribute = $this->emConfig->get('file_maker_name_attribute','');
			if (!empty($file->fieldData->{$email_attribute})) {
				$name = 'Nom Prénom';
				if(!empty($name_attribute) && empty($file->fieldData->{$name_attribute})) {
					$name = $file->fieldData->{$name_attribute};
				}

				$user_id = $this->createUserIfNotExist($file->fieldData->{$email_attribute}, $name);
				$fnums[] = $this->createSingleFile($file, $user_id, $mapped_columns);
			}
		}

		return $fnums;
	}

	public function createUserIfNotExist($email, $name)
	{
		$user_id = 0;
		$email = preg_replace('/\r/', '', $email);

		$query = $this->db->getQuery(true);

		$query->select('*')
			->from($this->db->quoteName('jos_users'))
			->where($this->db->quoteName('email') . '=' . $this->db->quote($email));
		$this->db->setQuery($query);
		$user = $this->db->loadObject();

		if (!empty($user)) {
			$user_id = $user->id;
		} else {
			$profile = 1000;
			$m_users = new \EmundusModelUsers();
			$h_users = new \EmundusHelperUsers();
			$firstname_and_lastname = explode(" ", $name);

			$password = md5($h_users->generateStrongPassword());
			$acl_aro_groups = $m_users->getDefaultGroup($profile);

			$insert_user = [
				'name' => $name,
				'username' => $email,
				'email' => $email,
				'password' => $password
			];
			$insert_user = (object) $insert_user;

			try {
				$this->db->insertObject('#__users', $insert_user);
				$user_id = $this->db->insertid();

				$insert_groupmap = [
					'user_id' => $user_id,
					'group_id' => $acl_aro_groups[0]
				];
				$insert_groupmap = (object) $insert_groupmap;
				$this->db->insertObject('#__user_usergroup_map', $insert_groupmap);
			} catch (Exception $e) {
				Log::add("Failed to insert jos_users" . $e->getMessage(), Log::ERROR, 'com_emundus');
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

	public function createSingleFile($single_field_data, $user_id, $mapped_columns)
	{
		$fnum = '';
		$campaign_id = $this->emConfig->get('file_maker_campaign',1);
		$now = new DateTime();
		$now->setTimezone(new DateTimeZone('UTC'));
		$now = $now->format('Y-m-d H:i:s');

		$h_files = new \EmundusHelperFiles();
		$m_files = new \EmundusModelFiles();
		$m_message = new \EmundusControllerMessages();

		$emundus_file = $this->getEmundusFile($single_field_data->fieldData->uuid);

		$admin_step = $single_field_data->fieldData->{'Admin_Step'};
		$step_properties = $this->getPropertiesByStep($admin_step);

		if (empty($emundus_file)) {
			$fnum = $h_files->createFnum($campaign_id, $user_id);

			//while fnum exist int campaign candidature table generate new one
			while (!empty($this->getRowInTable($fnum, '#__emundus_campaign_candidature'))) {
				$fnum = $h_files->createFnum($campaign_id, $user_id);
			}

			$insert_file = [
				'date_time' => $now,
				'applicant_id' => $user_id,
				'user_id' => $user_id,
				'campaign_id' => $campaign_id,
				'fnum' => $fnum,
				'uuid' => $single_field_data->fieldData->uuid,
				'uuidConnect' => $single_field_data->fieldData->uuidConnect,
				'recordId' => $single_field_data->recordId
			];
			$insert_file = (object) $insert_file;

			try {
				$inserted = $this->db->insertObject('#__emundus_campaign_candidature', $insert_file);

				if($inserted)
				{
					$this->insertFileDataToEmundusTables($fnum, $single_field_data, $mapped_columns, $user_id);

					$m_files->updateState($fnum, $step_properties->status);
					$m_message->sendEmail($fnum, $step_properties->email);
					$this->logActionIntoEmundusFileMakerlog(0, $fnum, $single_field_data->fieldData->uuid, $single_field_data->fieldData->uuidConnect, $step_properties->status, null, 1, "layouts/zWEB_FORMULAIRES/_find", "");
				}

			} catch (Exception $e) {
				$fnum = '';
				Log::add("[FILEMAKER CRON] Failed to create file $fnum - $user_id" . $e->getMessage(), Log::ERROR, 'com_emundus');
			}

		} else {
			$fnum = $emundus_file->fnum;
			// User was updated in Filemaker
			if (intval($emundus_file->applicant_id) != intval($user_id)) {
				$applicant_file_update_result = $this->updateFileApplicantId($single_field_data, $user_id, $emundus_file->fnum);
				$project_information_update_result = $this->updateProjectInformation($single_field_data, $emundus_file->fnum);
				if($applicant_file_update_result && $project_information_update_result){
					$m_message->sendEmail($emundus_file->fnum, $step_properties->email);
				}
			}

			$this->updateFile($emundus_file, $single_field_data, $m_files, $m_message, $admin_step, $mapped_columns, $user_id);
		}

		return $fnum;
	}

	public function getEmundusFile($uuid,$fnum = null)
	{
		$file = new stdClass();


		$query = $this->db->getQuery(true);

		$query->select('*')
			->from($this->db->quoteName('#__emundus_campaign_candidature'))
			->where($this->db->quoteName('uuid') . ' LIKE ' . $this->db->quote($uuid));
		try {
			$this->db->setQuery($query);
			$file = $this->db->loadObject();

			if(empty($file) && !empty($fnum)){
				$query->clear();
				$query->select('*')
					->from($this->db->quoteName('#__emundus_campaign_candidature'))
					->where($this->db->quoteName('fnum') . ' LIKE ' . $this->db->quote($fnum));
				$this->db->setQuery($query);
				$file = $this->db->loadObject();
			}
		} catch (Exception $e) {
			Log::add("[FILEMAKER CRON] Failed to check if file already exist for " . $uuid . " " . $e->getMessage(), Log::ERROR, 'com_emundus');
		}

		return $file;
	}

	public function insertFileDataToEmundusTables($fnum, $single_field_data, $mapped_columns, $user_id)
	{
		$this->insertGlobalLayoutFormData($fnum, $single_field_data, $mapped_columns, $user_id);
		$this->insertPortalsDatasElements($single_field_data, $mapped_columns, $fnum, $user_id);
	}

	public function insertGlobalLayoutFormData($fnum, $single_field_data, $mapped_columns, $user_id)
	{
		$result = false;

		$query = $this->db->getQuery(true);

		$fabik_helper = new \EmundusHelperFabrik();

		//Insertion of ZWEB_FORMULAIRE DATA;
		$ZWEB_FORMULAIRE_MAPPED_ELEMENTS = $this->getAssocElementsWithFileMakerFormLabel($mapped_columns, 'zWEB_FORMULAIRES');
		if (!empty($ZWEB_FORMULAIRE_MAPPED_ELEMENTS)) {
			foreach ($ZWEB_FORMULAIRE_MAPPED_ELEMENTS as $row) {
				$now = new DateTime();
				$now->setTimezone(new DateTimeZone('UTC'));
				$now = $now->format('Y-m-d H:i:s');

				$elements_names = ["time_date", "fnum", "user"];
				$elements_assoc_filemaker_attribute = [];
				$elements_values = [$this->db->quote($now), $fnum, $user_id];
				foreach ($row->elements as $element_row) {
					if (!empty($element_row->file_maker_attribute_name && $element_row->plugin !== "internalid")) {
						$elements_names[] = $this->db->quoteName($element_row->name);
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
							$params = json_decode($val->params);
							$elements_values[] = !empty($target_db_join_element_value) ? $this->db->quote($target_db_join_element_value->{$params->join_key_column}) : 'NULL';
							break;
						case 'birthday':
						case 'date':
							$date_string = str_replace('/', '-', $field_data[$val->name]);
							$date = DateTime::createFromFormat('m-d-Y', $date_string);
							$date !== false ? $date_value = $date->format('Y-m-d') : $date_value = $date_string;
							$elements_values[] = !empty($date_value) ? $this->db->quote($date_value) : 'NULL';
							break;
						case 'emundus_phonenumber':
							$formatted_number = $fabik_helper->getFormattedPhoneNumberValue($field_data[$val->name]);
							$elements_values[] = !empty($formatted_number) ? $this->db->quote($formatted_number) : $this->db->quote($field_data[$val->name]);
							break;
						case 'dropdown':
						case 'radiobutton':
							$params = json_decode($val->params);
							$option_sub_labels = array_map(function ($data) {
								return Text::_($data);
							}, $params->sub_options->sub_labels);

							$index = array_search($field_data[$val->name], $option_sub_labels, false);
							if ($index !== false) {
								$elements_values[] = $this->db->quote($params->sub_options->sub_values[$index]);
							} else {
								$elements_values[] = !empty($field_data[$val->name]) ? $this->db->quote($field_data[$val->name]) : 'NULL';
							}
							break;

						case 'checkbox':
							$params = json_decode($val->params);
							$values = explode("\r", $field_data[$val->name]);
							$option_sub_labels = array_map(function ($data) {
								return Text::_($data);
							}, $params->sub_options->sub_labels);
							$elm = array();
							foreach ($values as $sub_val) {
								$key = array_search($sub_val, $option_sub_labels);
								$elm[] = "" . $params->sub_options->sub_values[$key] . "";
							}
							$elements_values[] = $this->db->quote('[' . implode(",",$elm) . ']');
							break;

						default :
							$elements_values[] = !empty($field_data[$val->name]) ? $this->db->quote($field_data[$val->name]) : 'NULL';
					}

				}
				if (!empty($elements_names) && !empty($elements_values) && !empty($elements_assoc_filemaker_attribute)) {
					$elements_values_occurrences = array_count_values($elements_values);

					if (empty($this->getRowInTable($fnum, $row->db_table_name)) && $elements_values_occurrences["NULL"] !== (sizeof($elements_values) - 3)) {
						$query->clear();
						$query->insert($this->db->quoteName($row->db_table_name))
							->columns($elements_names)
							->values(implode(',', $elements_values));
						try {
							$this->db->setQuery($query);
							$result = $this->db->execute();
						} catch (Exception $e) {
							$result = false;
							Log::add("[FILEMAKER CRON] Failed to insert row in to table $row->db_table_name for fnum $fnum - $user_id" . $e->getMessage(), Log::ERROR, 'com_emundus');
						}
					}
				}

			}
		}

		return $result;
	}

	public function insertPortalsDatasElements($single_field_data, $mapped_columns, $fnum, $user_id)
	{
		$query = $this->db->getQuery(true);
		$portal_data_without_est_contribIf_recette = array_filter(($single_field_data->portalData)->{'zWEB_FORMULAIRES_RECETTES'}, function ($item) {
			return empty($item->{'zWEB_FORMULAIRES_RECETTES::EstContributionIF'});
		});
		($single_field_data->portalData)->{'zWEB_FORMULAIRES_RECETTES'} = $portal_data_without_est_contribIf_recette;
		$portal_data = (array)$single_field_data->portalData;
		$portal_data_keys = array_keys($portal_data);
		$fabik_helper = new \EmundusHelperFabrik();
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
									->from($this->db->quoteName('#__fabrik_joins'))
									->where('group_id =  ' . $group->id)
									->andWhere('table_join_key =' . $this->db->quote('parent_id'));
								try {
									$this->db->setQuery($query);
									$group->jointures_params = $this->db->loadObjectList();
								} catch (Exception $e) {
									Log::add("[FILEMAKER CRON] Failed to get tabele joins params for repeat group  $group->id [FNUM] $fnum" . $e->getMessage(), Log::ERROR, 'com_emundus');
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
											->from($this->db->quoteName('jos_fabrik_elements', 'jfe'))
											->leftJoin($this->db->quoteName('data_filemaker_emundus_forms','zfe') . ' ON zfe.file_maker_assoc_emundus_element = jfe.id')
											->where('jfe.group_id =  ' . $group->id)
											->andWhere('jfe.published = 1')
											->andWhere('zfe.file_maker_attribute_name = ' . $this->db->quote($row_key));
										try {
											$this->db->setQuery($query);
											$val = $this->db->loadObject();
											if (!empty($val) && intval($val->group_id) == intval($row->portal_data_emundus_group_id)) {
												switch ($val->plugin) {
													case 'databasejoin':
														$target_db_join_element_value = $this->retrieveDataBaseJoinElementJointureInformations($val, $field_data[$key . "::" . $row_key]);
														$repeat_elements_values[] = array("" . $val->name . "" => $this->db->quote($target_db_join_element_value));
														break;
													case 'birthday':
													case 'date':
														$date_string = str_replace('/', '-', $field_data[$key . "::" . $row_key]);
														$date = DateTime::createFromFormat('m-d-Y', $date_string);
														$date !== false ? $date_value = $date->format('Y-m-d') : $date_value = $date_string;
														$repeat_elements_values[] = array("" . $val->name . "" => $this->db->quote($date_value));
														break;
													case 'emundus_phonenumber':
														$formatted_number = $fabik_helper->getFormattedPhoneNumberValue($field_data[$key . "::" . $row_key]);
														$repeat_elements_values[] = array("" . $val->name . "" => !empty($formatted_number) ? $this->db->quote($formatted_number) : $this->db->quote($field_data[$key . "::" . $row_key]));
														break;
													case 'dropdown':
													case 'radiobutton':
														$params = json_decode($val->params);
														$option_sub_labels = array_map(function ($data) {
															return Text::_($data);
														}, $params->sub_options->sub_labels);
														$index = array_search($field_data[$key . "::" . $row_key], $option_sub_labels, false);
														if ($index !== false) {
															$repeat_elements_values[] = array("" . $val->name . "" => $this->db->quote($params->sub_options->sub_values[$index]));
														} else {
															$repeat_elements_values[] = array("" . $val->name . "" => $this->db->quote($field_data[$key . "::" . $row_key]));
														}
														break;
													case 'checkbox':
														$params = json_decode($val->params);
														$values = explode("\r", $field_data[$key . "::" . $row_key]);
														$option_sub_labels = array_map(function ($data) {
															return Text::_($data);
														}, $params->sub_options->sub_labels);
														$elm = array();
														foreach ($values as $sub_val) {
															$key = array_search($sub_val, $option_sub_labels);
															$elm[] = "" . $params->sub_options->sub_values[$key] . "";
														}
														$repeat_elements_values[] = array("" . $val->name . "" => $this->db->quote("[" . implode(",", @$elm) . "]"));
														break;

													default :
														if ($row_key === "recordId") {
															$repeat_elements_values[] = array("" . $val->name . "" => $this->db->quote($field_data[$row_key]));
														} else {
															$repeat_elements_values[] = array("" . $val->name . "" => $this->db->quote($field_data[$key . "::" . $row_key]));
														}
												}
											}
										} catch (Exception $e) {
											Log::add("[FILEMAKER CRON] Failed to get emundus assoc element for filemaker attr  $key:: $row_key [FNUM] $fnum" . $e->getMessage(), Log::ERROR, 'com_emundus');
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

	public function retrieveDataBaseJoinElementJointureInformations($element, $needed)
	{
		$result = 0;

		$query = $this->db->getQuery(true);
		$query->select('table_join,params')
			->from($this->db->quoteName('#__fabrik_joins'))
			->where('element_id = ' . $element->id);
		try {
			$this->db->setQuery($query);
			$result = $this->db->loadObject();

			$params = json_decode($result->params, true);
			$result = $this->retrieveDatabaseJoinElementValue($result->table_join, $params["join-label"], $needed);
		} catch (Exception $e) {
			Log::add("[FILEMAKER CRON] Failed to get table joins params for element id   $element->id " . $e->getMessage(), Log::ERROR, 'com_emundus');
		}

		return $result;
	}

	public function insertDataIntoRepeatGroupsTable($fnum, $group, $user_id): void
	{
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
							$parent_id = $this->insertIntoATable($parent_table, ["time_date", "fnum", "user"], [$this->db->quote($now), $fnum, $user_id]);
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

	public function insertIntoATable($db_table_name, $elements_names, $elements_values, $fnum = 0, $user_id = 0)
	{
		$inserted_id = 0;
		$query = $this->db->getQuery(true);
		$query->clear();
		$query->insert($this->db->quoteName($db_table_name))
			->columns($elements_names)
			->values(implode(",", $elements_values));
		try {
			$this->db->setQuery($query);
			$this->db->execute();
			$inserted_id = $this->db->insertid();
		} catch (Exception $e) {
			Log::add("[FILEMAKER CRON] Failed to insert row in to table $db_table_name for fnum $fnum - $user_id" . $e->getMessage(), Log::ERROR, 'com_emundus');
		}
		return $inserted_id;
	}

	public function updateFileApplicantId($file, $user_id, $fnum):bool
	{
		$result = false;

		$query = $this->db->getQuery(true);
		$query->clear()
			->update($this->db->quoteName('#__emundus_campaign_candidature'))
			->set($this->db->quoteName('applicant_id') . ' = ' . $user_id)
			->where($this->db->quoteName('uuid') . "=" . $this->db->quote($file->fieldData->uuid));
		try {
			$this->db->setQuery($query);
			$result = $this->db->execute();
		} catch (Exception $e) {
			Log::add("[FILEMAKER CRON] Failed to update file applicant id $fnum - $user_id" . $e->getMessage(), Log::ERROR, 'com_emundus');
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
		$query = $this->db->getQuery(true);
		$query->clear()
			->update($this->db->quoteName('jos_emundus_1001_00'))
			->set($this->db->quoteName('e_947_8592') . ' = ' . $this->db->quote($file->fieldData->InterlocuteurIF))
			->set($this->db->quoteName('e_947_8593') . ' = ' . $this->db->quote($file->fieldData->InterlocuteurIF_Email))
			->set($this->db->quoteName('e_800_7974') . ' = ' . $this->db->quote($file->fieldData->Projet_Intitule))
			->set($this->db->quoteName('e_796_7967') . ' = ' . $this->db->quote($date_value))
			->where($this->db->quoteName('fnum') . 'LIKE' . $this->db->quote($fnum));
		try {
			$this->db->setQuery($query);
			$result = $this->db->execute();
		} catch (Exception $e) {
			Log::add("[FILEMAKER CRON] Failed to update project information for  $fnum " . $e->getMessage(), Log::ERROR, 'com_emundus');
		}
		return $result;
	}

	public function updateFile($emundus_file, $single_field_data, $m_files, $m_message, $admin_step, $mapped_columns, $user_id)
	{
		$step_properties = $this->getPropertiesByStep($admin_step);

		if ($emundus_file->uuidConnect !== $single_field_data->fieldData->uuidConnect) {
			switch ($admin_step) {
				case 'PRE':
					$update_file = [
						'uuid' => $single_field_data->fieldData->uuid,
						'uuidConnect' => $single_field_data->fieldData->uuidConnect
					];
					$update_file = (object) $update_file;

					try {
						$this->db->updateObject('#__emundus_campaign_candidature', $update_file, 'uuid');

						$m_files->updateState($emundus_file->fnum, $step_properties->status);
						$this->updateProjectInformation($single_field_data, $emundus_file->fnum);
					} catch (Exception $e) {
						$fnum = '';
						Log::add("[FILEMAKER CRON] Failed to update file status $fnum - $user_id" . $e->getMessage(), Log::ERROR, 'com_emundus');
					}
					break;

				case 'POST':
					$update_file = [
						'uuid' => $single_field_data->fieldData->uuid,
						'uuidConnect' => $single_field_data->fieldData->uuidConnect
					];
					$update_file = (object) $update_file;

					try {
						$this->db->updateObject('#__emundus_campaign_candidature', $update_file, 'uuid');

						if (intval($emundus_file->status === 1)) {
							$this->insertFileDataToEmundusTables($emundus_file->fnum, $single_field_data, $mapped_columns, $user_id);
							$m_message->sendEmail($emundus_file->fnum, $step_properties->email);
							$this->logActionIntoEmundusFileMakerlog(0, $emundus_file->fnum, $single_field_data->fieldData->uuid, $single_field_data->fieldData->uuidConnect, $step_properties->status, NULL, 1, "layouts/zWEB_FORMULAIRES/_find", "");
						}
						$m_files->updateState($emundus_file->fnum, $step_properties->status);
					} catch (Exception $e) {
						$fnum = '';
						Log::add("[FILEMAKER CRON] Failed to update file status $fnum - $user_id" . $e->getMessage(), Log::ERROR, 'com_emundus');
					}
					break;
			}
		} else {
			switch ($admin_step) {
				case 'PRE':
					if (in_array(intval($emundus_file->status), $step_properties->open_status)) {
						$m_files->updateState($emundus_file->fnum, $step_properties->status);
						$this->updateProjectInformation($single_field_data, $emundus_file->fnum);
					}
					break;
				case 'POST':
					if (in_array(intval($emundus_file->status), $step_properties->open_status)) {
						$m_files->updateState($emundus_file->fnum, $step_properties->status);
						$this->updateProjectInformation($single_field_data, $emundus_file->fnum);
					}
					break;
			}
		}
	}

	private function getPropertiesByStep($admin_step)
	{
		$step_properties = new stdClass();
		$step_properties->status = 0;
		$step_properties->email = 83;

		$filemaker_steps = $this->emConfig->get('file_maker_steps');
		foreach ($filemaker_steps as $value) {
			if($value->step == $admin_step) {
				$step_properties = $value;
			}
		}

		return $step_properties;
	}

	private function getRowInTable($fnum, $db_table_name)
	{
		$file = new stdClass();

		$query = $this->db->getQuery(true);

		if(!empty($fnum) && !empty($db_table_name))
		{
			$query->select('*')
				->from($this->db->quoteName($db_table_name))
				->where($this->db->quoteName('fnum') . ' LIKE ' . $this->db->quote($fnum));
			try
			{
				$this->db->setQuery($query);
				$file = $this->db->loadObject();
			}
			catch (Exception $e)
			{
				Log::add("[FILEMAKER CRON] Failed to check if file already exist for fnum" . $fnum . " " . $e->getMessage(), Log::ERROR, 'com_emundus');
			}
		}

		return $file;
	}

	private function array_to_object($array) {
		$obj = new stdClass();

		foreach ($array as $k => $v) {
			if (strlen($k)) {
				if (is_array($v)) {
					$obj->{$k} = $this->array_to_object($v); //RECURSION
				} else {
					$obj->{$k} = $v;
				}
			}
		}

		return $obj;
	}


}
