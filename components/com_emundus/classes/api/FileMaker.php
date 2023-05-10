<?php
namespace classes\api;

/**
 * @package     com_emundus
 * @subpackage  api
 * @author	eMundus.fr - Merveille Gbetegan
 * @copyright (C) 2023 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use EmundusModelEmails;
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


    public function __construct()
    {
        JLog::addLogger(['text_file' => 'com_emundus.file_maker.php'], JLog::ALL, 'com_emundus.file_maker');

        $this->setAuth();

        if (empty($this->auth['bear_token'])) {
            $this->loginApi();
        } else {
            $this->setHeaders();
            $this->setBaseUrl();
            $this->client = new GuzzleClient([
                'base_uri' => $this->getBaseUrl(),
                'headers' => $this->getHeaders()
            ]);
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
    public function setHeaders(): void
    {
        $auth = $this->getAuth();

        $this->headers = array(
            'Authorization' => 'Bearer ' . $auth['bear_token'],
            'Accept' => 'application/json',
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

    private function loginApi():void{
        $auth = $this->getAuth();

        $this->headers = array(
            'Authorization' => 'Basic ' . $auth['basic_token'],
            'Content-Type' => 'application/json'
        );

        $login_response  = $this->post("/sessions");
        var_dump($login_response);
        die;

        if($login_response->messages[0]->code == "0"){
            $session = JFactory::getSession();
            $session->set('file_maker_bear_token',$login_response->response->token);
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
            $response = $this->client->get($url);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            JLog::add('[GET] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');
            return $e->getMessage();
        }
    }

    private function post($url, $query_body_in_json = null)
    {
        $response = '';

        try {
            $response = $query_body_in_json !== null ? $this->client->post($url, ['body' => $query_body_in_json]) : $this->client->post($url);
            $response = json_decode($response->getBody());
        } catch (\Exception $e) {
            JLog::add('[POST] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');
            $response = $e->getMessage();
        }

        return $response;
    }

    private function patch($url, $query_body_in_json)
    {
        $response = '';

        try {
            $response = $this->client->patch($url, ['body' => $query_body_in_json]);
            $response = json_decode($response->getBody());
        } catch (\Exception $e) {
            JLog::add('[PATCH] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.file_maker');
            $response = $e->getMessage();
        }

        return $response;
    }


}
