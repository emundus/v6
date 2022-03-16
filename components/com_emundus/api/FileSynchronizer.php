<?php

/**
 *Use Guzzle client
 */

namespace apis;

use GuzzleHttp\Client as GuzzleClient;


class FileSynchronizer
{
    /**
     * @var string $type of api used
     */
    var $type = 'alfresco';

    /**
     * @var array $auth
     */
    private var $auth = array();

    /**
     * @var array $headers
     */
    private var $headers = array();

    /**
     * @var string $baseUrl
     */
    private var $baseUrl = '';

    /**
     * @var string $authenticationUrl
     */
    var $authenticationUrl = '';

    /**
     * @var string $modelUrl
     */
    var $modelUrl = '';

    /**
     * @var string $searchUrl
     */
    var $searchUrl = '';

    /**
     * @param $client GuzzleClient
     */
    var $client = null;

    function __construct($type = 'alfresco')
    {
        $this->type = $type;

        $this->setAuth();
        $this->setHeaders();

        $client = new GuzzleClient([
            'base_uri' => $this->getBaseUrl(),
            'headers' => $this->getHeaders()
        ]);
        $this->client = $client;
    }

    function setAuth()
    {
        switch ($this->type) {
            case 'alfresco':
                $this->auth['consumer_key'] = 'eMundus';
                $this->auth['consumer_secret'] = 'eMundus';
                $this->auth['token'] = 'eMundus';
                $this->auth['token_secret'] = 'eMundus';
                break;
            default:
                break;
        }
    }

    function setHeaders()
    {
        $this->headers = array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        );
    }

    function getHeaders()
    {
        return $this->headers;
    }

    function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    function getBaseUrl()
    {
        return $this->baseUrl;
    }

    function post($url, $params)
    {
        try {
            $response = $this->client->post($this->getBaseUrl() . $url, [
                'headers' => $this->getHeaders(),
                'json' => $params
            ]);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    function get($url, $params)
    {

    }

    function put($url, $params)
    {

    }

    function delete($url, $params)
    {

    }

    function setToken()
    {
        $params = array();

        switch ($this->type) {
            case 'alfresco':
                $this->authenticationUrl = '/alfresco/api/-default-/public/authentication/versions/1/tickets';

                $params['userId'] = $this->auth['consumer_key'];
                $params['password'] = $this->auth['consumer_secret'];
                break;
            default:
                break;
        }

        if (!empty($this->authenticationUrl)) {
            $response = $this->post($this->authenticationUrl, $params);

            switch ($this->type) {
                case 'alfresco':
                    if (isset($response->entry->id)) {
                        $this->auth['token'] = $response->data->ticket;
                    }

                    break;
                default:
                    break;
            }
        }
    }

    function checkToken()
    {

    }

    function createFolder()
    {

    }

    function createFile()
    {

    }
}