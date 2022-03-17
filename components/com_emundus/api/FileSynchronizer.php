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
     * @var string $coreUrl
     */
    var $coreUrl = '';

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
    private var $client = null;

    public function __construct($type = 'alfresco')
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

    private function setAuth()
    {
        $eMConfig = JComponentHelper::getParams('com_emundus');

        switch ($this->type) {
            case 'alfresco':
                $this->auth['consumer_key'] = $eMConfig->get('external_storage_ged_alfresco_user');
                $this->auth['consumer_secret'] = $eMConfig->get('external_storage_ged_alfresco_password');
                break;
            default:
                break;
        }
    }

    private function setHeaders()
    {
        $this->headers = array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        );
    }

    private function getHeaders()
    {
        return $this->headers;
    }

    private function setBaseUrl()
    {
        $eMConfig = JComponentHelper::getParams('com_emundus');

        switch ($this->type) {
            case 'alfresco':
                $this->baseUrl = $eMConfig->get('external_storage_ged_alfresco_site');
                break;
            default:
                break;
        }
    }

    private function getBaseUrl()
    {
        return $this->baseUrl;
    }

    private function post($url, $params)
    {
        try {
            // post to alfresco api with authentication and form data
            $response = $this->client->post($url, [
                'auth' => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
                'form_params' => $params
            ]);

            // return response
            return json_decode($response->getBody());
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function get($url, $params)
    {

    }

    private function put($url, $params)
    {

    }

    private function delete($url, $params)
    {

    }

    private function setToken()
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


    private function checkToken()
    {
        if (empty($this->auth['token'])) {
            $this->setToken();
        } else {
            $this->auth['token'] = $this->auth['token'];
        }
    }

    public function addFile($fnum, $file)
    {
        $paths = $this->getRelativePaths($file);

        foreach ($paths as $path) {
            // replace shortcodes
            $this->createFile($path, $file);
        }
    }

    private function getRelativePaths($file)
    {

    }

    public function createFile($relativePath, $file)
    {
        $params = array(
            "filedata" => $file,
            "name" => basename($file),
            "nodeType" => "cm:content",
            "relativePath" => $relativePath,
            "properties" => array(
                "cm:title" => basename($file),
                "cm:description" => "",
            )
        );

        $this->post($this->coreUrl . "/nodes/$this->parentNodeId/children", $params);
    }
}