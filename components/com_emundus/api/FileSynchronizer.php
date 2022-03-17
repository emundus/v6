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
    public $type = 'ged';

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

    public function __construct($type = 'ged')
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
            case 'ged':
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
            case 'ged':
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
            case 'ged':
                $this->authenticationUrl = 'alfresco/api/-default-/public/authentication/versions/1';
                $this->coreUrl = 'alfresco/api/-default-/public/alfresco/versions/1';
                $this->modelUrl = 'alfresco/api/-default-/public/alfresco/versions/1';
                $this->searchUrl = 'alfresco/api/-default-/public/search/versions/1';
                break;
            default:
                break;
        }
    }

    public function getEmundusRootDirectory()
    {
        if (empty($this->emundusRootDirectory)) {
            // search emundus root directory in bdd
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('params')
                ->from('#__emundus_setup_sync')
                ->where('type = '.$db->quote($this->type));
            $db->setQuery($query);
            $params = $db->loadResult();
            $params = json_decode($params, true);
            $this->emundusRootDirectory = !empty($params['emundus_root_directory']) ? $params['emundus_root_directory'] : '';
        }

        return $this->emundusRootDirectory;
    }

    private function setEmundusRootDirectory()
    {
        $this->getEmundusRootDirectory();

        if (empty($this->emundusRootDirectory)) {
            switch ($this->type) {
                case 'ged':
                    $documentLibrary = $this->getGEDDocumentLibrary();
                    if (!empty($documentLibrary)) {
                        $found = $this->getGEDEmundusRootDirectory($documentLibrary);

                        if (!$found) {
                            $response = $this->createFolder($documentLibrary, array(
                                'name'=> 'EMUNDUS',
                                'nodeType' => 'cm:folder',
                            ));

                            if (!empty($response)) {
                                $this->emundusRootDirectory = $response->entry->id;
                                $this->saveEmundusRootDirectory();
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        }
    }

    private function getGEDDocumentLibrary()
    {
        $documentLibrary = '';
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $site = $eMConfig->get('external_storage_ged_alfresco_site');
        $response = $this->get($this->coreUrl . "/sites/$site/containers");

        if (!empty($response->list) && !empty($response->list->entries)) {
            foreach ($response->list->entries as $entry) {
                if ($entry->entry->folderId == 'documentLibrary') {
                    $documentLibrary = $entry->entry->id;
                }
            }
        }

        return $documentLibrary;
    }

    private function getGEDEmundusRootDirectory($parentId)
    {
        $found = false;

        // get children
        $response = $this->get($this->coreUrl . "/nodes/$parentId/children");
        if (!empty($response->list) && !empty($response->list->entries)) {

            foreach ($response->list->entries as $entry) {
                // check if properties custom:author is EMUNDUS

                if ($entry->entry->name == 'EMUNDUS') {
                    $this->emundusRootDirectory = $entry->entry->id;
                    $this->saveEmundusRootDirectory();
                    $found = true;
                    break;
                }
            }
        }

        return $found;
    }

    private function saveEmundusRootDirectory()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('params')
            ->from('#__emundus_setup_sync')
            ->where("type = " . $db->quote($this->type));
        $db->setQuery($query);
        $params = $db->loadResult();
        $params = json_decode($params);

        $params->emundus_root_directory = $this->emundusRootDirectory;
        $params = json_encode($params);

        $query = $db->getQuery(true);
        $query->update('#__emundus_setup_sync')
            ->set('params = ' . $db->quote($params))
            ->where("type = " . $db->quote($this->type));
        $db->setQuery($query);

        $db->execute();
    }

    private function post($url, $params = array())
    {
        try {
            $response = $this->client->post($url, [
                'auth' => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
                'json' => $params
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

        $this->post($this->coreUrl . "/nodes/$this->emundusRootDirectory/children", $params);
    }

    public function createFolder($parentNodeId, $params)
    {
        return $this->post($this->coreUrl . "/nodes/$parentNodeId/children", $params);
    }
}