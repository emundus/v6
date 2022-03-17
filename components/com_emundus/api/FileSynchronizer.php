<?php
/**
 * @package     com_emundus
 * @subpackage  api
 * @author	eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use GuzzleHttp\Client as GuzzleClient;

defined( '_JEXEC' ) or die( 'Restricted access' );

class FileSynchronizer
{
    /**
     * @var string $type of api used
     */
    public $type = 'alfresco';

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
     * @var string $authenticationUrl
     */
    private $authenticationUrl = '';


    /**
     * @var string $coreUrl
     */
    private $coreUrl = '';

    /**
     * @var string $modelUrl
     */
    private $modelUrl = '';

    /**
     * @var string $searchUrl
     */
    private $searchUrl = '';

    /**
     * @var string $emundusRootDirectory
     */
    private $emundusRootDirectory = '';

    /**
     * @param $client GuzzleClient
     */
    private $client = null;

    public function __construct($type = 'alfresco')
    {
        $this->type = $type;

        $this->setAuth();
        $this->setHeaders();
        $this->setBaseUrl();
        $this->setUrls();
        $client = new GuzzleClient([
            'base_uri' => $this->getBaseUrl(),
            'headers' => $this->getHeaders()
        ]);
        $this->client = $client;

        $this->setEmundusRootDirectory();
    }

    private function setAuth()
    {
        $config = JComponentHelper::getParams('com_emundus');

        switch ($this->type) {
            case 'alfresco':
                $this->auth['consumer_key'] = $config->get('external_storage_ged_alfresco_user');
                $this->auth['consumer_secret'] = $config->get('external_storage_ged_alfresco_password');
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
        $config = JComponentHelper::getParams('com_emundus');

        switch ($this->type) {
            case 'alfresco':
                $this->baseUrl = $config->get('external_storage_ged_alfresco_base_url');
                break;
            default:
                break;
        }
    }

    private function getBaseUrl()
    {
        return $this->baseUrl;
    }

    private function setUrls()
    {
        switch ($this->type) {
            case 'alfresco':
                $this->authenticationUrl = 'alfresco/api/-default-/public/authentication/versions/1';
                $this->coreUrl = 'alfresco/api/-default-/public/alfresco/versions/1';
                $this->modelUrl = 'alfresco/api/-default-/public/alfresco/versions/1';
                $this->searchUrl = 'alfresco/api/-default-/public/search/versions/1';
                break;
            default:
                break;
        }
    }

    private function setEmundusRootDirectory()
    {
        $eMConfig = JComponentHelper::getParams('com_emundus');

        switch ($this->type) {
            case 'alfresco':
                $site = $eMConfig->get('external_storage_ged_alfresco_site');
                $response = $this->get($this->coreUrl . "/sites/$site/containers");

                if (!empty($response->list) && !empty($response->list->entries)) {
                    foreach ($response->list->entries as $entry) {
                        if ($entry->entry->folderId == 'documentLibrary') {
                            $documentLibrary = $entry->entry->id;
                            $this->emundusRootDirectory = $documentLibrary;
                        }
                    }
                }
                break;
            default:
                break;
        }
    }

    public function getEmundusRootDirectory()
    {
        return $this->emundusRootDirectory;
    }

    private function post($url, $params = array())
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

    private function get($url, $params = array())
    {
        try {
            // get from alfresco api with authentication and form data
            $response = $this->client->get($url, [
                'auth' => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
                'query' => $params
            ]);

            // return response
            return json_decode($response->getBody());
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function put($url, $params = array())
    {

    }

    private function delete($url, $params = array())
    {

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