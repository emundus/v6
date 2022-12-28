<?php
/**
 * @package     com_emundus
 * @subpackage  api
 * @author	eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use GuzzleHttp\Client as GuzzleClient;

class SmartAgenda
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
     * @var string token
     */
    private $token = '';

    /**
     * @param $client GuzzleClient
     */
    private $client = null;

    public function __construct($type = 'ged')
    {
        JLog::addLogger(['text_file' => 'com_emundus.smart_agenda.php'], JLog::ERROR, 'com_emundus.smart_agenda');
        $this->setAuth();
        $this->setHeaders();
        $this->setBaseUrl();
        $client = new GuzzleClient([
            'base_uri' => $this->getBaseUrl(),
            'headers' => $this->getHeaders()
        ]);
        $this->client = $client;
    }

    private function setAuth()
    {
        $config = JComponentHelper::getParams('com_emundus');
        $this->auth['login'] = $config->get('smart_agenda_login');
        $this->auth['pwd'] = $config->get('smart_agenda_pwd');
        $this->auth['api_id'] = $config->get('smart_agenda_api_id');
        $this->auth['api_key'] = $config->get('smart_agenda_api_key');
    }

    private function getAuth(): array
    {
        return $this->auth;
    }

    private function setHeaders()
    {
        $this->headers = array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        );
    }

    private function getHeaders(): array
    {
        return $this->headers;
    }

    private function setBaseUrl()
    {
        $config = JComponentHelper::getParams('com_emundus');
        $this->baseUrl = $config->get('smart_agenda_base_url');
    }

    private function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    private function get($url, $params = array())
    {
        if (empty($params)) {
            $params = ['token' => $this->getToken()];
        }

        try {
            $url_params = http_build_query($params);
            $response = $this->client->get($url . '?' . $url_params);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            JLog::add('[GET] ' .$e->getMessage(), JLog::ERROR, 'com_emundus.smart_agenda');
            return $e->getMessage();
        }
    }

    private function post($url, $json)
    {
        $response = '';
        $token = $this->getToken();

        if (!empty($token)) {
            try {
                $response = $this->client->post($url . '?' . http_build_query(['token' => $this->getToken()]), ['json' => $json]);

                $response = json_decode($response->getBody());
            } catch (\Exception $e) {
                JLog::add('[POST] ' .$e->getMessage(), JLog::ERROR, 'com_emundus.smart_agenda');
                $response = $e->getMessage();
            }
        } else {
            $response = 'Missing api token';
        }

        return $response;
    }

    private function getToken()
    {
        if (empty($this->token)) {
            $this->generateToken();
        }

        return $this->token;
    }

    private function setToken($token)
    {
        $this->token = $token;
    }

    private function generateToken()
    {
        $response = $this->get('token', $this->getAuth());

        if (!empty($response) && !empty($response->token)) {
            $this->setToken($response->token);
        } else {
            JLog::add('Failed to generate token for smart agenda ', JLog::WARNING, 'com_emundus.smart_agenda');
        }
    }

    public function addClient($json)
    {
        $added = false;

        if (!empty($json)) {
            $accepted_json_entries = ["mail", "nom", "prenom", "adresse", "code_postal", "ville", "telephone", "portable", "sexe", "date_naissance", "infos", "sms_actif", "mail_actif", "cc1"];
            $diff = array_diff(array_keys($json), $accepted_json_entries);

            if (empty($diff) && !empty($json['nom']) && !empty($json['prenom']) && !empty($json['mail'])) {
                $added = $this->post('pdo_client', $json);
            } else {
                JLog::add('Tried to ad client without necessary parameters ' . json_encode($json), JLog::WARNING, 'com_emundus.smart_agenda');
            }
        }

        return $added;
    }
}