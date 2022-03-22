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

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

class FileSynchronizer
{
    /**
     * @var string $type of api used
     */
    public string $type = 'ged';

    /**
     * @var array $auth
     */
    private array $auth = array();

    /**
     * @var array $headers
     */
    private array $headers = array();

    /**
     * @var string $baseUrl
     */
    private string $baseUrl = '';

    /**
     * @var string $authenticationUrl
     */
    private string $authenticationUrl = '';


    /**
     * @var string $coreUrl
     */
    private string $coreUrl = '';

    /**
     * @var string $modelUrl
     */
    private string $modelUrl = '';

    /**
     * @var string $searchUrl
     */
    private string $searchUrl = '';

    /**
     * @var string $emundusRootDirectory
     */
    private string $emundusRootDirectory = '';

    /**
     * @param $client GuzzleClient
     */
    private $client = null;

    public function __construct($type = 'ged')
    {
        $this->setType($type);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->setConfigFromType();
    }

    private function setConfigFromType()
    {
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

            if (!empty($this->emundusRootDirectory)) {
                // check if emundus root directory exists
                $exists = $this->checkEmundusRootDirectoryExists($this->emundusRootDirectory);

                if (!$exists) {
                    $this->emundusRootDirectory = '';
                }
            }
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

    private function checkEmundusRootDirectoryExists($id)
    {
        $exists = false;
        switch ($this->type) {
            case 'ged':
                $response = $this->get($this->coreUrl . "/nodes/" . $id);
                $exists = !empty($response->entry->id);
                break;
            default:
                break;
        }

        return $exists;
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

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
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
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
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

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
            return $e->getMessage();
        }
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
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
            return $e->getMessage();
        }
    }


    public function addFile($upload_id) {
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

                if (empty($path)) {
                    JLog::add('Could not rewrite path for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus');
                    continue;
                }

                if (substr($path, -1) == '/') {
                    $path = substr($path, 0, -1);
                }
                $filepath = $this->getFilePath($upload_id);
                $filename = $this->getFileName($upload_id, $path);

                if (empty($filepath) || empty($filename)) {
                    JLog::add('Could not get filepath or filename for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus');
                    continue;
                }

                switch ($this->type) {
                    case 'ged':
                        $this->addGEDFile($upload_id, $filename, $filepath, $path);
                        break;
                    default:
                        break;
                }
            }
        }
    }

    public function addGEDFile($upload_id, $filename, $filepath, $relativePath) {
        $saved = false;
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);

        $params = array(
            array(
                'name' => 'name',
                'contents' => $filename . '.' . $ext
            ),
            array(
                'name' => 'nodeType',
                'contents' => 'cm:content',
            ),
            array(
                'name' => 'relativePath',
                'contents' => $relativePath,
            ),
            array(
                'name' => 'filedata',
                'contents' => fopen($filepath, 'r'),
            )
        );

        $response = $this->postFormData($this->coreUrl . "/nodes/$this->emundusRootDirectory/children", $params);

        if (!empty($response->entry)) {
            $saved = $this->saveNodeId($upload_id, $response->entry->id, $relativePath . '/' . $filename);

            if (!$saved) {
                JLog::add('Could not save node id for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus');
            }
        } else {
            JLog::add('Could not add file for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus');
        }

        return $saved;
    }

    public function deleteFile($upload_id) {
        $nodeId = $this->getNodeId($upload_id);

        if (!empty($nodeId)) {
            return $this->delete($this->coreUrl . "/nodes/$nodeId");
        }

        return false;
    }

    private function getRelativePaths()
    {
        $paths = array();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('config')
            ->from('#__emundus_setup_sync')
            ->where("type = " . $db->quote($this->type));
        $db->setQuery($query);

        try {
            $config = $db->loadResult();
            $config = json_decode($config);

            $tree = $config->tree;

            foreach ($tree as $node) {
                $paths[] = $this->createPathsFromTree($node);
            }
        } catch (\Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        return $paths;
    }

    private function createPathsFromTree($node)
    {
        if (empty($node->type) && empty($node->children)) {
            return '';
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
        $unchangedPath = $path;
        $userId = $this->getApplicantId($upload_id);
        $fnum = $this->getFnum($upload_id);

        if (!empty($userId) && !empty($fnum)) {
            if (class_exists('EmundusModelEmails')) {
                $post = [
                    'FNUM' => $fnum,
                    'DOCUMENT_TYPE' => $this->getDocumentType($upload_id),
                    'CAMPAIGN_LABEL' => $this->getCampaignLabel($upload_id),
                    'CAMPAIGN_YEAR' => $this->getCampaignYear($upload_id),
                ];

                $m_emails = new EmundusModelEmails();
                $tags = $m_emails->setTags($userId, $post, $fnum, '', $path);

                foreach ($tags['patterns'] as $key => $pattern) {
                    $tags['patterns'][$key] = str_replace(array('/', '\\'), '', $pattern);
                }

                $path = str_replace($tags['patterns'], $tags['replacements'], $path);
            } else {
                JLog::add('EmundusModelEmails class not found', JLog::ERROR, 'com_emundus');
            }
        }

        if ($path == $unchangedPath) {
            JLog::add('Could not replace types for path ' . $path, JLog::ERROR, 'com_emundus');
            return false;
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
        $saved = false;
        $sync_id = $this->getSyncId();

        if ($sync_id == -1) {
            return $saved;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->insert('#__emundus_uploads_sync')
            ->columns('upload_id, sync_id, state, relative_path, node_id')
            ->values($upload_id . ', ' . $sync_id . ', ' . $db->quote('1') . ', ' . $db->quote($path) . ', ' . $db->quote($node_id));

        $db->setQuery($query);

        try {
            $saved = $db->execute();
        } catch (Exception $e) {
            $app = JFactory::getApplication();
            $app->enqueueMessage($e->getMessage(), 'error');
            JLog::add('Error inserting into #__emundus_uploads_sync: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }

        return $saved;
    }

    private function getSyncId()
    {
        $sync_id = -1;

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

    private function getFileName($upload_id, $path)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('config')
            ->from('#__emundus_setup_sync')
            ->where("type = " . $db->quote($this->type));
        $db->setQuery($query);
        $config = $db->loadResult();
        $config = json_decode($config);

        $filename = $this->replaceTypes($config->name, $upload_id);

        if (empty($filename)) {
            return false;
        }

        $filename = str_replace(' ', '', $filename);

        // check if filename already exists
        $query->clear()
            ->select('relative_path')
            ->from('#__emundus_uploads_sync')
            ->where("relative_path LIKE " . $db->quote($path . '/'. $filename . '%'));

        $db->setQuery($query);

        try {
            $existing_files = $db->loadColumn();

            if (in_array($path . '/' . $filename, $existing_files)) {
                $filename = $this->getUniqueFileName($filename, $path, $existing_files);
            }
        } catch (Exception $e) {
            JLog::add('Error getting existing files: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }

        return $filename;
    }

    private function getUniqueFileName($filename, $path, $existing_files)
    {
        $i = 1;
        $name = $filename;
        while (in_array($path . '/' . $filename, $existing_files)) {
            $filename = $name . '_' . $i;
            $i++;
        }
        return $filename;
    }

    private function getFnum($upload_id)
    {
        $fnum = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('fnum')
            ->from('#__emundus_uploads')
            ->where("id = " . $db->quote($upload_id));
        $db->setQuery($query);

        try {
            $fnum = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting fnum: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }

        return $fnum;
    }



    private function getFilePath($upload_id)
    {
        $filePath = "";
        $user = $this->getApplicantId($upload_id);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('filename')
            ->from('#__emundus_uploads')
            ->where('id = ' . $db->quote($upload_id));
        $db->setQuery($query);

        try {
            $filename = $db->loadResult();
            $filePath = JPATH_BASE . DS . EMUNDUS_PATH_REL . DS . $user . DS . $filename;
        } catch (Exception $e) {
            JLog::add('Error getting filename: ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }

        return $filePath;
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

    private function getCampaignLabel($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('#__emundus_setup_campaigns.label')
            ->from('#__emundus_setup_campaigns')
            ->leftJoin('#__emundus_campaign_candidature ON #__emundus_setup_campaigns.id = #__emundus_campaign_candidature.campaign_id')
            ->leftJoin('#__emundus_uploads ON #__emundus_campaign_candidature.fnum = #__emundus_uploads.fnum')
            ->where('#__emundus_uploads.id = ' . $db->quote($upload_id));

        $db->setQuery($query);
        $campaign_label = $db->loadResult();

        return trim($campaign_label);
    }

    private function getCampaignYear($upload_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('#__emundus_setup_campaigns.year')
            ->from('#__emundus_setup_campaigns')
            ->leftJoin('#__emundus_campaign_candidature ON #__emundus_setup_campaigns.id = #__emundus_campaign_candidature.campaign_id')
            ->leftJoin('#__emundus_uploads ON #__emundus_campaign_candidature.fnum = #__emundus_uploads.fnum')
            ->where('#__emundus_uploads.id = ' . $db->quote($upload_id));

        $db->setQuery($query);
        $year = $db->loadResult();

        return trim($year);
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
}