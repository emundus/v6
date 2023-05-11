<?php

namespace classes\api;

/**
 * @package     com_emundus
 * @subpackage  api
 * @author    eMundus.fr - Merveille Gbetegan
 * @copyright (C) 2023 eMundus SOFTWARE. All rights reserved.
 * @license    GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use EmundusModelEmails;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use JComponentHelper;
use JFactory;
use JLog;

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
    private static $availaibleZwForms = array('zWEB_FORMULAIRES_RECETTES', 'zWEB_FORMULAIRES_PLANNING',
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

    private function get($url, $params = array())
    {
        try {
            $url_params = http_build_query($params);
            $url = !empty($url_params) ? $url . '?' . $url_params : $url;
            $response = $this->client->get($url, ['headers' => $this->getHeaders()]);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            if($e->getCode() == 401 && $this->getMaxAttempt() < 3){
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

            if($e->getCode() == 401 && $this->getMaxAttempt() < 3){
                $this->loginApi();
                $this->post($url, $query_body_in_json);
                $this->setMaxAttempt();
            }
            JLog::add('[POST] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');
            $response = $e->getMessage();
        }

        return $response;
    }

    private function patch($url, $query_body_in_json)
    {
        $response = '';

        try {
            $response = $this->client->patch($url, ['body' => $query_body_in_json, 'headers' => $this->getHeaders()]);
            $response = json_decode($response->getBody());
        } catch (\Exception $e) {

            if($e->getCode() == 401 && $this->getMaxAttempt() < 3){
                $this->loginApi();
                $this->patch($url, $query_body_in_json);
                $this->setMaxAttempt();
            }

            JLog::add('[PATCH] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');
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

        $records = $records_response->response->data;

        return $records;
    }

    public function findRecord($uuidConnect, $zWebFormType = null, $sort = array())
    {

        if (in_array($zWebFormType, $this->getAvailaibleZwForms())) {
            if (!empty($uuidConnect)) {

                $url = empty($zWebFormType) ? "layouts/zWEB_FORMULAIRES" : "layouts/" . $zWebFormType . "/_find";

                $queryBody = ["query" => array([
                    empty($zWebFormType) ? "uuidConnect" : "zWEB_FORMULAIRES::uuidConnect" => $uuidConnect,

                ])];

                $record_response = $this->post($url, json_encode($queryBody));


                return $record_response->response->data;


            } else {

                JLog::add('[FILE_MAKER]  Empty uuidConnect passed to findRecord method  ', JLog::ERROR, 'com_emundus.file_maker');

                return 0;

            }
        } else {
            throw new Exception('Invalid zFORM_TYPE. It shoulbe one of ' . json_encode($this->getAvailaibleZwForms()));
        }

    }

    public function updateRecord($recordId, $queryBody)
    {

        if (!empty($recordId)) {

            $url = "layouts/zWEB_FORMULAIRES/records/" . $recordId;
            $update_record_response = $this->patch($url, json_encode($queryBody));
            return $update_record_response->response->data;

        } else {

            throw new Exception('Record Id could not be empty');
        }
    }


}
