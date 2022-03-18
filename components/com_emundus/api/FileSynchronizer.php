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

    private function postFormData($url, $params = array())
    {
        try {
            $response = $this->client->post($url, [
                'auth' => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
                'multipart' => $params
            ]);

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
        try {
            $response = $this->client->delete($url, [
                'auth' => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
                'query' => $params
            ]);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    public function addFile($upload_id) {
        // query setup attachment to see if we have to sync file
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('sync')
            ->from('#__emundus_setup_attachments')
            ->leftJoin('#__emundus_uploads ON #__emundus_uploads.attachment_id = #__emundus_setup_attachments.id')
            ->where('#__emundus_uploads.id = ' . $db->quote($upload_id));
        $db->setQuery($query);
        $sync = $db->loadResult();

        if (!empty($sync)) {
            $relativePaths = $this->getRelativePaths();

            foreach ($relativePaths as $relativePath) {
                $path = $this->replaceTypes($relativePath, $upload_id);
                // if last character is a /, remove it
                if (substr($path, -1) == '/') {
                    $path = substr($path, 0, -1);
                }
                $filepath = $this->getFilePath($upload_id);

                $params = array(
                    array(
                        'name' => 'name',
                        'contents' => $this->getFileName($upload_id)
                    ),
                    array(
                        'name' => 'nodeType',
                        'contents' => 'cm:content',
                    ),
                    array(
                        'name' => 'relativePath',
                        'contents' => $path,
                    ),
                    array(
                        'name' => 'filedata',
                        'contents' => fopen($filepath, 'r'),
                    )
                );

                $response = $this->postFormData($this->coreUrl . "/nodes/$this->emundusRootDirectory/children", $params);

                if (!empty($response->entry)) {
                    $this->saveNodeId($upload_id, $response->entry->id, $path);
                }
            }
        }
    }

    public function deleteFile($upload_id) {
        $nodeId = $this->getNodeId($upload_id);

        if (!empty($nodeId)) {
            $response = $this->delete($this->coreUrl . "/nodes/$nodeId");
            return $response;
        }

        return false;
    }

    private function getRelativePaths()
    {
        $paths = array();

        // query setup_sync config for this type
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('config')
            ->from('#__emundus_setup_sync')
            ->where("type = " . $db->quote($this->type));
        $db->setQuery($query);
        $config = $db->loadResult();
        $config = json_decode($config);

        // create all paths from $params['tree']
        $tree = $config->tree;

        // loop through tree and create paths
        foreach ($tree as $node) {
            $paths[] = $this->createPathsFromTree($node);
        }

        return $paths;
    }

    private function createPathsFromTree($node)
    {
        if (empty($node->type) && empty($node->children)) {
            return;
        }

        $path = $node->type;

        if (!empty($node->childrens)) {
            foreach ($node->childrens as $child) {
                $path .= '/' . $this->createPathsFromTree($child);
            }
        }

        return $path;
    }

    private function replaceTypes($path, $upload_id) {
        if (strpos($path, '[CAMPAIGN]') !== false) {
            $path = str_replace('[CAMPAIGN]', $this->getCampaign($upload_id), $path);
        }

        if (strpos($path, '[YEAR]') !== false) {
            $path = str_replace('[YEAR]', $this->getYear($upload_id), $path);
        }

        if (strpos($path, '[FNUM]') !== false) {
            $path = str_replace('[FNUM]', $this->getFnum($upload_id), $path);
        }

        if (strpos($path, '[DOCUMENT_TYPE]') !== false) {
            $path = str_replace('[DOCUMENT_TYPE]', $this->getDocumentType($upload_id), $path);
        }

        if (strpos($path, '[APPLICANT_ID]') !== false) {
            $path = str_replace('[APPLICANT_ID]', $this->getApplicantId($upload_id), $path);
        }

        return $path;
    }

    public function createFolder($parentNodeId, $params)
    {
        return $this->post($this->coreUrl . "/nodes/$parentNodeId/children", $params);
    }

    private function getNodeId($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('node_id')
            ->from('#__emundus_uploads_sync')
            ->where("upload_id = " . $db->quote($upload_id));
        $db->setQuery($query);

        return $db->loadResult();
    }

    private function saveNodeId($upload_id, $node_id, $path)
    {
        // insert row inside #__emundus_uploads_sync
        $sync_id = $this->getSyncId();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->insert('#__emundus_uploads_sync')
            ->columns('upload_id, sync_id, state, relative_path, node_id')
            ->values($upload_id . ', ' . $sync_id . ', ' . $db->quote('1') . ', ' . $db->quote($path) . ', ' . $db->quote($node_id));

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            // enqueue message errror
            $app = JFactory::getApplication();
            $app->enqueueMessage($e->getMessage(), 'error');
            JLog::add('Error inserting into #__emundus_uploads_sync: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }
    }

    private function getSyncId()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
            ->from('#__emundus_setup_sync')
            ->where("type = " . $db->quote($this->type));
        $db->setQuery($query);

        try {
            $sync_id = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting sync_id: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }

        return $sync_id;
    }

    private function getFileName($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('config')
            ->from('#__emundus_setup_sync')
            ->where("type = " . $db->quote($this->type));
        $db->setQuery($query);
        $config = $db->loadResult();
        $config = json_decode($config);

        return $this->replaceTypes($config->name, $upload_id);
    }

    private function getCampaign($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('label')
            ->from('#__emundus_setup_campaigns')
            ->leftJoin('#__emundus_campaign_candidature ON #__emundus_campaign_candidature.campaign_id = #__emundus_setup_campaigns.id')
            ->leftJoin('#__emundus_uploads ON #__emundus_campaign_candidature.fnum = #__emundus_uploads.fnum')
            ->where('#__emundus_uploads.id = ' . $db->quote($upload_id));

        $db->setQuery($query);
        $label = $db->loadResult();

        return trim($label);
    }

    private function getYear($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('year')
            ->from('#__emundus_setup_campaigns')
            ->leftJoin('#__emundus_campaign_candidature ON #__emundus_campaign_candidature.campaign_id = #__emundus_setup_campaigns.id')
            ->leftJoin('#__emundus_uploads ON #__emundus_campaign_candidature.fnum = #__emundus_uploads.fnum')
            ->where('#__emundus_uploads.id = ' . $db->quote($upload_id));

        $db->setQuery($query);
        $year = $db->loadResult();

        return trim($year);
    }

    private function getFnum($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('fnum')
            ->from('#__emundus_uploads')
            ->where('#__emundus_uploads.id = ' . $db->quote($upload_id));

        $db->setQuery($query);
        $fnum = $db->loadResult();

        return trim($fnum);
    }

    private function getDocumentType($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('value')
            ->from('#__emundus_setup_attachments')
            ->leftJoin('#__emundus_uploads ON #__emundus_uploads.attachment_id = #__emundus_setup_attachments.id')
            ->where('#__emundus_uploads.id = ' . $db->quote($upload_id));

        $db->setQuery($query);
        $fnum = $db->loadResult();

        return trim($fnum);
    }

    private function getApplicantId($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('applicant_id')
            ->from('#__emundus_campaign_candidature')
            ->leftJoin('#__emundus_uploads ON #__emundus_campaign_candidature.fnum = #__emundus_uploads.fnum')
            ->where('#__emundus_uploads.id = ' . $db->quote($upload_id));

        $db->setQuery($query);
        $applicant_id = $db->loadResult();

        return trim($applicant_id);
    }

    private function getFilePath($upload_id)
    {
        $filePath = "";

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('filename')
            ->from('#__emundus_uploads')
            ->where('id = ' . $db->quote($upload_id));
        $db->setQuery($query);
        $filename = $db->loadResult();

        $user = $this->getApplicantId($upload_id);

        $filePath = JPATH_BASE . DS . EMUNDUS_PATH_REL . DS . $user . DS . $filename;
        return $filePath;
    }

}