<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */


// no direct access
defined('_JEXEC') || die;
jimport('joomla.filesystem.file');


/**
 * Class DropfilesOneDrive initialization and connection OneDrive
 */
class DropfilesOneDrive
{
    /**
     * Onedrive params
     *
     * @var array
     */
    protected $params;

    /**
     * Last error
     *
     * @var mixed
     */
    protected $lastError;

    /**
     * Api children
     *
     * @var string
     */
    protected $api_children = 'children(top=1000;expand=thumbnails(select=medium,large,mediumSquare,c1500x1500))';

    /**
     * Api list file fields thumbnail
     *
     * @var string
     */
    protected $apifilefields_thumb = 'thumbnails,';

    /**
     * Api list files fields
     *
     * @var string
     */
    protected $apilistfilesfields = 'thumbnails(select=medium,large,mediumSquare,c1500x1500)';

    /**
     * Breadcrumb
     *
     * @var string
     */
    public $breadcrumb = '';

    /**
     * DropfilesOneDrive constructor.
     */
    public function __construct()
    {
        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        $path_dropfilescloud = JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfilescloud.php';
        JLoader::register('DropfilesCloudHelper', $path_dropfilescloud);
        require_once 'OneDrive/autoload.php';
        $this->loadParams();
    }


    /**
     * Get all onedrive config
     *
     * @return array
     */
    public function getAllOneDriveConfigs()
    {
        return DropfilesCloudHelper::getAllOneDriveConfigs();
    }


    /**
     * Get all onedrive config Old
     *
     * @return mixed|void
     */
    public static function getAllOneDriveConfigsOld()
    {
        return DropfilesCloudHelper::getAllOneDriveConfigsOld();
    }


    /**
     * Save OneDrive Configs
     *
     * @param array $data Data
     *
     * @return boolean
     */
    public function saveOneDriveConfigs($data)
    {
        return DropfilesCloudHelper::setParamsConfigs($data);
    }


    /**
     * Get param config OneDrive
     *
     * @param string $name Name
     *
     * @return array|null
     */
    public function getDataConfigByOneDrive($name)
    {
        return DropfilesCloudHelper::getDataConfigByOneDrive($name);
    }

    /**
     * Get last error
     *
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Load onedrive params
     *
     * @return void
     */
    protected function loadParams()
    {
        $params = $this->getDataConfigByOneDrive('onedrive');
        $this->params = new stdClass();

        $this->params->onedrive_client_id = $params['onedriveKey'];
        $this->params->onedrive_client_secret = $params['onedriveSecret'];
        $params_credentials = '';
        if (isset($params['onedriveCredentials'])) {
            $params_credentials = $params['onedriveCredentials'];
        }
        $this->params->onedrive_credentials = $params_credentials;
    }


    /**
     * Get Authorisation Url OneDrive
     *
     * @return string
     */
    public function getAuthorisationUrl()
    {
        $client = new OneDrive_Client();
        $client->setClassConfig('OneDrive_Task_Runner', 'retries', 3);
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $onedrive_redirect = JURI::root() . 'administrator/index.php?option=com_dropfiles&task=onedrive.authenticate';
        $client->setRedirectUri($onedrive_redirect);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setScopes(array(
            'wl.offline_access',
            'wl.skydrive',
            'onedrive.readwrite'
        ));
        $client->setState(strtr(base64_encode($onedrive_redirect), '+/=', '-_~'));
        $array_config = array(
            'level'          => 'debug', //'warning' or 'debug'
            'log_format'     => "[%datetime%] %level%: %message% %context%\n",
            'date_format'    => 'd/M/Y:H:i:s O',
            'allow_newlines' => true
        );
        $client->setClassConfig('OneDrive_Logger_Abstract', $array_config);

        $tmpUrl = parse_url($client->createAuthUrl());
        $query = explode('&', $tmpUrl['query']);
        $port = isset($tmpUrl['port']) ? $tmpUrl['port'] : '';
        $return = $tmpUrl['scheme'] . '://' . $tmpUrl['host'] . $port;
        $return .= $tmpUrl['path'] . '?' . implode('&', $query);
        return $return;
    }


    /**
     * Save config onedrive
     *
     * @return void
     */
    protected function saveParams()
    {
        $params = $this->getAllOneDriveConfigs();
        $params['onedriveKey'] = $this->params->onedrive_client_id;
        $params['onedriveSecret'] = $this->params->onedrive_client_secret;
        $params['onedriveCredentials'] = $this->params->onedrive_credentials;
        $this->saveOneDriveConfigs($params);
    }

    /**
     * Save config onedrive old
     *
     * @return void
     */
    protected function saveParamsOld()
    {
        $params = $this->getAllOneDriveConfigsOld();
        $params['onedriveKey'] = $this->params->onedrive_client_id;
        $params['onedriveSecret'] = $this->params->onedrive_client_secret;
        $params['onedriveCredentials'] = $this->params->onedrive_credentials;
        $this->saveOneDriveConfigs($params);
    }

    /**
     * Authenticate OneDrive
     *
     * @return string
     */
    public function authenticate()
    {
        $code = JFactory::getApplication()->input->get('code');
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $uri_redirect = JURI::root() . 'administrator/index.php?option=com_dropfiles&task=onedrive.authenticate';
        $client->setRedirectUri($uri_redirect);

        return $client->authenticate($code);
    }

    /**
     * Onedrive log out
     *
     * @return void
     */
    public function logout()
    {
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);
        $client->revokeToken();
    }

    /**
     * Store credentials
     *
     * @param string $credentials Credentials
     *
     * @return void
     */
    public function storeCredentials($credentials)
    {
        $this->params->onedrive_credentials = $credentials;
        $this->saveParams();
    }

    /**
     * Get credentials
     *
     * @return mixed
     */
    public function getCredentials()
    {
        return $this->params->onedrive_credentials;
    }

    /**
     * Check Auth
     *
     * @return boolean
     */
    public function checkAuth()
    {
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);

        try {
            $client->setAccessToken($this->params->onedrive_credentials);
            new OneDrive_Service_Drive($client);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Create new folder in onedrive
     *
     * @param string      $title    Title
     * @param null|string $parentId Parent id
     *
     * @return null|OneDrive_Service_Drive_Item
     */
    public function createFolder($title, $parentId = null)
    {
        $parentId = DropfilesCloudHelper::replaceIdOneDrive($parentId, false);
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);

        $service = new OneDrive_Service_Drive($client);
        $newfolder = new OneDrive_Service_Drive_Item();
        /* set name */
        $newfolder->setName($title);
        $newfolder->setFolder(new OneDrive_Service_Drive_FolderFacet());
        $newfolder['@name.conflictBehavior'] = 'rename';
        $item = null;
        try {
            $item = $service->items->insert($parentId, $newfolder);
        } catch (Exception $ex) {
            $erros = $ex->getMessage() . $ex->getTraceAsString() . PHP_EOL;
            JLog::add($erros, JLog::ERROR, 'com_dropfiles');
        }
        return $item;
    }

    /**
     * Create new root folder
     *
     * @param null|string $new_folder New folder name
     *
     * @return boolean|OneDrive_Service_Drive_Item
     */
    public function addFolderRoot($new_folder = null)
    {
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);
        if ($client === null) {
            return false;
        }
        $service = new OneDrive_Service_Drive($client);
        /* Create new folder object */
        $newfolder = new OneDrive_Service_Drive_Item();
        $newfolder->setName($new_folder);
        $newfolder->setFolder(new OneDrive_Service_Drive_FolderFacet());
        $newfolder['@name.conflictBehavior'] = 'rename';
        /* Do the insert call */
        $newentry = null;
        try {
            $newentry = $service->items->insert('root', $newfolder);
        } catch (Exception $ex) {
            $erros = $ex->getMessage() . $ex->getTraceAsString() . PHP_EOL;
            JLog::add($erros, JLog::ERROR, 'com_dropfiles');
        }

        return $newentry;
    }


    /**
     * Get all file in category
     *
     * @param string  $folder_id       Folder id
     * @param integer $dropfiles_catid Category term id
     * @param string  $ordering        Ordering
     * @param string  $direction       Order direction
     * @param array   $listIdFlies     List files
     *
     * @return array|boolean
     */
    public function listFiles(
        $folder_id,
        $dropfiles_catid,
        $ordering = 'ordering',
        $direction = 'asc',
        $listIdFlies = array()
    ) {
        $folder_idck = DropfilesCloudHelper::replaceIdOneDrive($folder_id, false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        try {
            $client->setAccessToken($this->params->onedrive_credentials);

            $service = new OneDrive_Service_Drive($client);
            $itemsearch = $service->items->get($folder_idck, array('expand' => $apifilefields));
            $contents = $itemsearch->getChildren();

            $files = array();
            foreach ($contents as $f) {
                $isFolder = $f->getFolder();
                if (!$isFolder) {
                    $parentReference = $f->getParentReference();
                    $idItem = DropfilesCloudHelper::replaceIdOneDrive($f->getId());
                    if ($listIdFlies && !in_array($idItem, $listIdFlies)) {
                        continue;
                    }
                    if ($folder_idck === $parentReference->getId()) {
                        $file                   = new stdClass();
                        $file->id               = $idItem;
                        $file->ID               = $idItem;
                        $file->title            = DropfilesCloudHelper::stripExt($f->getName());
                        $file->post_title       = $file->title;
                        $file->description      = $f->getDescription();
                        $file->ext              = DropfilesCloudHelper::getExt($f->getName());
                        $file->size             = $f->getSize();
                        $file->created          = date('Y-m-d H:i:s', strtotime($f->getCreatedDateTime()));
                        $file->created_time     = $file->created;
                        $modified_time          = date('Y-m-d H:i:s', strtotime($f->getLastModifiedDateTime()));
                        $file->modified         = $modified_time;
                        $file->modified_time    = $modified_time;
                        $file->versionNumber    = '';
                        $file->version          = '';
                        $file->hits             = 0;
                        $file->ordering         = 0;
                        $file->file_custom_icon = '';
                        $file->catid            = $dropfiles_catid;

                        $files[] = $file;
                        unset($file);
                    }
                }
            }

            $files = $this->subvalSort($files, $ordering, $direction);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $files;
    }

    /**
     * Subval Sort
     *
     * @param array  $a         Array
     * @param string $subkey    Sub key
     * @param string $direction Direction
     *
     * @return array
     */
    private function subvalSort($a, $subkey, $direction)
    {
        $c = array();
        if (empty($a)) {
            return $a;
        }
        foreach ($a as $k => $v) {
            $b[$k] = strtolower($v->$subkey);
        }
        if ($direction === 'asc') {
            asort($b);
        } else {
            arsort($b);
        }
        foreach ($b as $key => $val) {
            $c[] = $a[$key];
        }
        return $c;
    }

    /**
     * Upload file to server Onedrive
     *
     * @param string  $filename    File name
     * @param array   $file        File
     * @param string  $fileContent File content
     * @param string  $id_folder   Folder id
     * @param boolean $replace     Replace
     *
     * @return array
     */
    public function uploadFile($filename, $file, $fileContent, $id_folder, $replace = false)
    {
        if ($replace) {
            $conflictBehavior = 'replace';
        } else {
            $conflictBehavior = 'rename';
        }
        $id_folder = DropfilesCloudHelper::replaceIdOneDrive($id_folder, false);
        $client_service = $this->getClientServer();
        $client = $client_service['client'];
        $service = $client_service['service'];

        /* Set return Object */
        $return = array('file' => $file, 'status' => array('bytes_down_so_far' => 0,
            'total_bytes_down_expected' => 0,
            'percentage' => 0,
            'progress' => 'starting')
        );

        if (isset($file['error'])) {
            /* Write file */
            $filePath = $fileContent;
            $chunkSizeBytes = 20 * 320 * 1000; // Multiple of 320kb, the recommended fragment size is between 5-10 MB.

            /* Update Mime-type if needed (for IE8 and lower?) */
            if (!function_exists('getMimeType')) {
                $path_mime_types = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR;
                $path_mime_types .= 'components' . DIRECTORY_SEPARATOR . 'com_dropfiles' . DIRECTORY_SEPARATOR;
                $path_mime_types .= 'classes' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
                $path_mime_types .= 'mime-types.php';
                include_once($path_mime_types);
            }
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file['type'] = getMimeType($fileExtension);

            try {
                /* Create new File with parent */
                $body = array('item' => array('name' => $filename, '@name.conflictBehavior' => $conflictBehavior));
                $client->setDefer(true);
                $startupload = $service->items->upload($filename, $id_folder, $body);
                $media = new OneDrive_Http_MediaFileUpload($client, $startupload, null, null, true, $chunkSizeBytes);

                $filesize = filesize($filePath);
                $media->setFileSize($filesize);

                $uploadStatus = false;
                $bytesup = 0;
                $handle = fopen($filePath, 'rb');

                while (!$uploadStatus && !feof($handle)) {
                    set_time_limit(60);
                    $chunk = fread($handle, $chunkSizeBytes);
                    $uploadStatus = $media->nextChunk($chunk);

                    /* Update progress */
                    $bytesup += $chunkSizeBytes;
                    $percentage = (round(($bytesup / $file['size']) * 100));
                    $return['status'] = array('bytes_up_so_far' => $bytesup,
                        'total_bytes_up_expected' => $filesize,
                        'percentage' => $percentage,
                        'progress' => 'uploading');
                }

                fclose($handle);
                if (!isset($uploadStatus['name'])) {
                    $file_name = $uploadStatus->getName();
                } else {
                    $file_name = $uploadStatus['name'];
                }
                if (!isset($uploadStatus['id'])) {
                    $file_id = $uploadStatus->getId();
                } else {
                    $file_id = $uploadStatus['id'];
                }
                $file['name'] = DropfilesCloudHelper::stripExt($file_name);
                $file['id'] = DropfilesCloudHelper::replaceIdOneDrive($file_id);

                if (!isset($uploadStatus['createdDateTime'])) {
                    $file['createdDateTime'] = $uploadStatus->getCreatedDateTime();
                } else {
                    $file['createdDateTime'] = $uploadStatus['createdDateTime'];
                }
                if (!isset($uploadStatus['lastModifiedDateTime'])) {
                    $file['lastModifiedDateTime'] = $uploadStatus->getLastModifiedDateTime();
                } else {
                    $file['lastModifiedDateTime'] = $uploadStatus['lastModifiedDateTime'];
                }
            } catch (Exception $ex) {
                $file['error'] = 'Not uploaded to OneDrive: ' . $ex->getMessage();
                $return['status']['progress'] = 'failed';
            }

            $client->setDefer(false);
        }
        $return['file'] = $file;
        return $return;
    }


    /**
     * Get onedrive item info
     *
     * @param string $idFile     File id
     * @param string $idCategory Category id
     *
     * @return array|boolean
     */
    public function getOneDriveFileInfos($idFile, $idCategory)
    {
        $idFile         = DropfilesCloudHelper::replaceIdOneDrive($idFile, false);
        $apifilefields  = $this->apifilefields_thumb . $this->api_children;
        $client_service = $this->getClientServer();
        $service        = $client_service['service'];

        try {
            $file = $service->items->get($idFile, array('expand' => $apifilefields));

            $data                  = array();
            $data['ID']            = DropfilesCloudHelper::replaceIdOneDrive($file->getId());
            $data['id']            = $data['ID'];
            $data['catid']         = $idCategory;
            $data['title']         = DropfilesCloudHelper::stripExt($file->getName());
            $data['post_title']    = $data['title'];
            $data['file']          = '';
            $data['ext']           = DropfilesCloudHelper::getExt($file->getName());
            $data['created_time']  = date('Y-m-d H:i:s', strtotime($file->getCreatedDateTime()));
            $data['created']       = $data['created_time'];
            $modified_time         = date('Y-m-d H:i:s', strtotime($file->getLastModifiedDateTime()));
            $data['modified_time'] = $modified_time;
            $data['modified']      = $modified_time;
            $data['file_tags']     = '';
            $data['size']          = $file->getSize();
            $data['ordering']      = 1;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }

        return $data;
    }


    /**
     * Save properties onedrive item
     *
     * @param array $datas Datas
     *
     * @return boolean
     */
    public function saveOnDriveFileInfos($datas)
    {
        $id = DropfilesCloudHelper::replaceIdOneDrive($datas['id'], false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $client_service = $this->getClientServer();
        $service = $client_service['service'];
        try {
            $file = $service->items->get($id, array('expand' => $apifilefields));
            $params = array(
                'name' => $datas['title'] . '.' . $datas['ext']
            );
            if (isset($datas['description'])) {
                $params['description'] = $datas['description'];
            }
            if (isset($datas['data'])) {
                $params['content'] = $datas['data'];
            }

            $result = $this->updateItem($service, $file, $params);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }


    /**
     * Change file name
     *
     * @param string $id       File id
     * @param string $filename File name
     *
     * @return boolean
     */
    public function changeFilename($id, $filename)
    {
        $id = DropfilesCloudHelper::replaceIdOneDrive($id, false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);

        try {
            $service = new OneDrive_Service_Drive($client);
            $file = $service->items->get($id, array('expand' => $apifilefields));
            $params = array('name' => $filename);
            $this->updateItem($service, $file, $params);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }


    /**
     * Check File Folder
     *
     * @param string $id       File id
     * @param string $cloud_id Cloud id
     *
     * @return boolean
     */
    public function checkFileFolderValid($id, $cloud_id)
    {
        $id = DropfilesCloudHelper::replaceIdOneDrive($id, false);
        $cloud_id = DropfilesCloudHelper::replaceIdOneDrive($cloud_id, false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);

        try {
            $service = new OneDrive_Service_Drive($client);
            $files = $service->items->get($id, array('expand' => $apifilefields));
            if (!empty($files)) {
                $parent = $files->getParentReference();
                $found = false;
                if ($parent->getId() === $cloud_id) {
                    $found = true;
                }
                if (!$found) {
                    return false;
                } else {
                    return true;
                }
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }

        return false;
    }


    /**
     * Delete items
     *
     * @param string      $id       File id
     * @param null|string $cloud_id Cloud id
     *
     * @return boolean
     */
    public function delete($id, $cloud_id = null)
    {
        $id = DropfilesCloudHelper::replaceIdOneDrive($id, false);
        $cloud_id = DropfilesCloudHelper::replaceIdOneDrive($cloud_id, false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);

        $service = new OneDrive_Service_Drive($client);
        try {
            $files = $service->items->get($id, array('expand' => $apifilefields));
            if (!empty($cloud_id) && $cloud_id !== null) {
                $parent = $files->getParentReference();
                $found = false;
                if ($parent->getId() === $cloud_id) {
                    $found = true;
                }
                if (!$found) {
                    return false;
                }
            }
            $service->items->delete($id);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }


    /**
     * Copy file
     *
     * @param string $fileId      File id
     * @param string $newParentId New parent id
     *
     * @return string
     */
    public function copyFile($fileId, $newParentId)
    {
        $newParentId = DropfilesCloudHelper::replaceIdOneDrive($newParentId, false);
        $fileId = DropfilesCloudHelper::replaceIdOneDrive($fileId, false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);
        $service = new OneDrive_Service_Drive($client);
        $return = '';
        try {
            $copyFile = new OneDrive_Service_Drive_Item();
            $parent = new OneDrive_Service_Drive_ItemReference();
            $item = $service->items->get($fileId, array('expand' => $apifilefields));
            $parent->setId($newParentId);
            $ext_fname = DropfilesCloudHelper::getExt($item->getName());
            $newname = DropfilesCloudHelper::stripExt($item->getName()) . uniqid() . '.' . $ext_fname;
            $copyFile->setName($newname);
            $copyFile->setParentReference($parent);
            $returnCopy = $service->items->copy($fileId, $copyFile, array('Prefer' => $apifilefields));
            $return = $returnCopy['location'];
        } catch (Exception $e) {
            print 'An error occurred: ' . $e->getMessage();
        }
        return $return;
    }


    /**
     * Get all files in folder
     *
     * @param string $folderId Folder id
     * @param array  $datas    Datas
     *
     * @throws Exception Throw when something wrong
     * @return void
     */
    public function getFilesInFolder($folderId, &$datas)
    {
        $folderId = DropfilesCloudHelper::replaceIdOneDrive($folderId, false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $client_service = $this->getClientServer();
        $service = $client_service['service'];
        $params = $this->getDataConfigByOneDrive('onedrive');
        $base_folder_id = $params['onedriveBaseFolderId'];

        $pageToken = null;
        if ($datas === false) {
            throw new Exception('getFilesInFolder - datas error ');
        }

        if (!is_array($datas)) {
            $datas = array();
        }
        do {
            try {
                $results = $service->items->get($folderId, array('expand' => $apifilefields));
                $childs = $results->getChildren();
                foreach ($childs as $f) {
                    $isFolder = $f->getFolder();
                    if ($isFolder) {
                        $parentReference = $f->getParentReference();
                        $idItem = DropfilesCloudHelper::replaceIdOneDrive($f->getId());
                        $nameItem = $f->getName();
                        if ($idItem !== $base_folder_id) {
                            $base_folder_id_rp = DropfilesCloudHelper::replaceIdOneDrive($base_folder_id, false);
                            if ($parentReference->getId() === $base_folder_id_rp) {
                                $datas[$idItem] = array('title' => $nameItem, 'parent_id' => 1);
                            } else {
                                $datas[$idItem] = array('title' => $nameItem,
                                    'parent_id' => DropfilesCloudHelper::replaceIdOneDrive($parentReference->getId()));
                            }
                            $this->getFilesInFolder($idItem, $datas);
                        }
                    }
                }
                // $pageToken = $children->getNextPageToken();
            } catch (Exception $e) {
                print 'An error occurred: ' . $e->getMessage() . $e->getTraceAsString();

                $datas = false;
                $pageToken = null;
                throw new Exception('getFilesInFolder - error ' . $e->getCode());
            }
        } while ($pageToken);
    }

    /**
     * Get List folder on OneDrive
     *
     * @param string $folderId Folder id
     *
     * @return array
     */
    public function getListFolder($folderId)
    {

        $datas = array();
        $this->getFilesInFolder($folderId, $datas);
        return $datas;
    }

    /**
     * Move a file.
     *
     * @param string $fileId      File id
     * @param string $newParentId New parent id
     *
     * @return boolean|mixed
     */
    public function moveFile($fileId, $newParentId)
    {
        $fileId = DropfilesCloudHelper::replaceIdOneDrive($fileId, false);
        $newParentId = DropfilesCloudHelper::replaceIdOneDrive($newParentId, false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $fileIds = explode(',', $fileId);
        $client_service = $this->getClientServer();
        $service = $client_service['service'];

        /* Set new parent for item */
        $newParent = new OneDrive_Service_Drive_ItemReference();
        $newParent->setId($newParentId);
        $params = new OneDrive_Service_Drive_Item();
        $params->setParentReference($newParent);
        $itemUpdate = null;
        try {
            foreach ($fileIds as $id) {
                $item = $service->items->get($id, array('expand' => $apifilefields));
                $itemUpdate = $this->updateItem($service, $item, $params);
            }
        } catch (Exception $ex) {
            $erros = $ex->getMessage() . $ex->getTraceAsString() . PHP_EOL;
            JLog::add($erros, JLog::ERROR, 'com_dropfiles');
            return false;
        }
        return $itemUpdate;
    }

    /**
     * Retrieve a list of File resources.
     *
     * @param array $filters Filters
     *
     * @return array List of OneDrive_Service_Drive_DriveFile resources.
     */
    public function getAllFilesInAppFolder($filters)
    {
        $onedriveBase = $this->getAllOneDriveConfigs();
        $onedriverBaseId = DropfilesCloudHelper::replaceIdOneDrive($onedriveBase['onedriveBaseFolderId'], false);
        $listfiles = array();
        if (!isset($filters['catid'])) {
            $id = $onedriverBaseId;
        } else {
            $id = DropfilesCloudHelper::replaceIdOneDrive($filters['catid'], false);
        }
        if (isset($filters['catid'])) {
            $arrayResults = $this->getFolder('', $id);
            $fileFolder = $arrayResults['contents'];
            $listFileName = array();
            if (isset($filters['q'])) {
                $arraySearch = $this->getFolder($filters['q'], $id);
                $fileSearch = $arraySearch['contents'];
                foreach ($fileSearch as $child) {
                    $is_dir = ($child->getFolder() !== null) ? true : false;
                    if (!$is_dir) {
                        $listFileName[] = $child->getName();
                    }
                }
                foreach ($fileFolder as $child) {
                    if (!empty($listFileName)) {
                        if (in_array($child->getName(), $listFileName)) {
                            if ($this->checkTimeCreate($child, $filters)) {
                                $listfiles[] = $child;
                            }
                        }
                    }
                }
            } else {
                foreach ($fileFolder as $child) {
                    $is_dir = ($child->getFolder() !== null) ? true : false;
                    if (!$is_dir) {
                        if ($this->checkTimeCreate($child, $filters)) {
                            $listfiles[] = $child;
                        }
                    }
                }
            }
        } else {
            if (isset($filters['q'])) {
                $arraySearch = $this->getFolder($filters['q'], $id);
                $fileSearch = $arraySearch['contents'];
                foreach ($fileSearch as $child) {
                    $is_dir = ($child->getFolder() !== null) ? true : false;
                    if (!$is_dir) {
                        if ($this->checkTimeCreate($child, $filters)) {
                            $listfiles[] = $child;
                        }
                    }
                }
            } else {
                $arraySearch = $this->getFolder($filters['q'], $id);
                $fileSearch = $arraySearch['contents'];
                foreach ($fileSearch as $child) {
                    $is_dir = ($child->getFolder() !== null) ? true : false;
                    if (!$is_dir) {
                        if ($this->checkTimeCreate($child, $filters)) {
                            $listfiles[] = $child;
                        }
                    }
                }
            }
        }

        $result = $this->displayFileSeach($listfiles);
        return $result;
    }

    /**
     * Check time create
     *
     * @param object     $f       File
     * @param null|array $filters Filters
     *
     * @return boolean
     */
    private function checkTimeCreate($f, $filters = null)
    {
        $ftime = date('Y-m-d', strtotime($f->getLastModifiedDateTime()));
        $result = false;
        if (isset($filters['cfrom']) && isset($filters['cto'])) {
            if (strtotime($filters['cfrom']) <= strtotime($ftime) && strtotime($ftime) <= strtotime($filters['cto'])) {
                $result = true;
            }
        } elseif (isset($filters['ufrom']) && isset($filters['uto'])) {
            if (strtotime($filters['ufrom']) <= strtotime($ftime) && strtotime($ftime) <= strtotime($filters['uto'])) {
                $result = true;
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Display file search
     *
     * @param array $listfiles List files
     *
     * @return array
     */
    private function displayFileSeach($listfiles)
    {
        $result = array();
        if (!empty($listfiles)) {
            foreach ($listfiles as $f) {
                $parentRef = $f->getParentReference();
                $arrayResults = $this->getFolder('', $parentRef->getId());
                $fileSearch = $arrayResults['folder'];
                $idItem = DropfilesCloudHelper::replaceIdOneDrive($f->getId());
                $termID = DropfilesCloudHelper::getTermIdOneDriveByOneDriveId($idItem);
                $file = new stdClass();
                $file->id = $idItem;
                $file->ID = $file->id;
                $file->title = DropfilesCloudHelper::stripExt($f->getName());
                $file->post_title = $file->title;
                $file->description = $f->getDescription();
                $file->ext = DropfilesCloudHelper::getExt($f->getName());
                $file->size = $f->getSize();
                $file->created = date('Y-m-d H:i:s', strtotime($f->getCreatedDateTime()));
                $file->created_time = $file->created;
                $file->modified = date('Y-m-d H:i:s', strtotime($f->getLastModifiedDateTime()));
                $file->modified_time = $file->modified;
                $file->versionNumber = '';
                $file->version = '';
                $file->hits = 0;
                $file->ordering = 0;
                $file->file_custom_icon = '';
                $file->parentRefId = $fileSearch->getId();
                $file->parentRefName = $fileSearch->getName();

                $config = DropfilesCloudHelper::getOneDriveFileInfos();
                if (!empty($config) && isset($config[$termID]) && isset($config[$termID][$f['id']])) {
                    $file_tags_cof = '';
                    if (isset($config[$termID][$f['id']]['file_tags'])) {
                        $file_tags_cof = $config[$termID][$f['id']]['file_tags'];
                    }
                    $file->file_tags = $file_tags_cof;
                }
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * Revoke token
     *
     * @return boolean
     */
    public function revokeToken()
    {
        //$this->client->revokeToken();
        $this->accessToken = '';
        $this->refreshToken = '';
        $onedriveconfig = array(
            'current_token' => '',
            'refresh_token' => ''
        );
        $onedriveconfig['current_token'] = '';
        $onedriveconfig['refresh_token'] = '';
        return true;
    }

    /**
     * Set redirect URL
     *
     * @param string $location Location
     *
     * @return void
     */
    public function redirect($location)
    {
        if (!headers_sent()) {
            header('Location:' . $location, true, 303);
        } else {
            echo "<script>document.location.href='" . str_replace("'", '&apos;', $location) . "';</script>\n";
        }
    }

    /**
     * Get folders and files
     *
     * @param string $searchfilename Search file name
     * @param string $folderid       Folder Id
     *
     * @return array|boolean
     */
    public function getFolder($searchfilename, $folderid = false)
    {
        $folderid = DropfilesCloudHelper::replaceIdOneDrive($folderid, false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        try {
            $client_service = $this->getClientServer();
            $service = $client_service['service'];
            $results = $service->items->get($folderid, array('expand' => $apifilefields));

            $parents = $results->getParentReference();
            $contents = $results->getChildren();

            if (isset($searchfilename) && $searchfilename !== '') {
                $params = array('id' => $folderid,
                    'q' => stripslashes($searchfilename),
                    'expand' => $this->apilistfilesfields
                );
                $itemsearch = $service->items->search($params);
                $contents = $itemsearch->getValue();
                return array('folder' => $results, 'contents' => $contents, 'parent' => $parents->id);
            }

            return array('folder' => $results, 'contents' => $contents, 'parent' => $parents->id);
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Edit item
     *
     * @param DropfilesGoogle_Service $service Google service
     * @param object                  $item    Item
     * @param array                   $params  Params
     *
     * @return mixed
     */
    public function updateItem($service, $item, $params = array())
    {
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $params_array = array('if-match' => $item->getEtag(), 'expand' => $apifilefields);
        $result = $service->items->patch($item->getId(), $params, $params_array);
        return $result;
    }

    /**
     * Download file
     *
     * @param string $fileId File id
     *
     * @return boolean|stdClass|WP
     */
    public function downloadFile($fileId)
    {
        $fileId = DropfilesCloudHelper::replaceIdOneDrive($fileId, false);
        $apifilefields = $this->apifilefields_thumb . $this->api_children;
        $client_service = $this->getClientServer();
        $client = $client_service['client'];
        $service = $client_service['service'];
        $item = $service->items->get($fileId, array('expand' => $apifilefields));

        $downloadlink = '';
        try {
            $result = $service->items->download($item->getId());
            if ($result || isset($result['location'])) {
                $downloadlink = $result['location'];
            }
            $downloadurl = $downloadlink . '?download=true';

            $request = new OneDrive_Http_Request($downloadurl, 'GET');

            $httpRequest = $client->getAuth()->authenticatedRequest($request);
            if ($httpRequest->getResponseHttpCode() === 200) {
                $ret = new stdClass();
                $ret->datas = $httpRequest->getResponseBody();
                $ret->title = DropfilesCloudHelper::stripExt($item->getName());
                $ret->ext = DropfilesCloudHelper::getExt($item->getName());
                $ret->size = $item->getSize();
                return $ret;
            } else {
                // An error occurred.
                return false;
            }
        } catch (Exception $ex) {
            $erros = 'Failed to add folder' . $ex->getMessage() . $ex->getTraceAsString() . PHP_EOL;
            JLog::add($erros, JLog::ERROR, 'com_dropfiles');
            return false;
        }
    }

    /**
     * ResponseBody copy
     *
     * @param string $url URL
     *
     * @return boolean|mixed
     */
    public function getResponseBodyRequest($url)
    {
        $client_service = $this->getClientServer();
        $client = $client_service['client'];
        $request = new OneDrive_Http_Request($url, 'GET');
        $httpRequest = $client->getAuth()->authenticatedRequest($request);
        if ($httpRequest->getResponseHttpCode() === 200 || $httpRequest->getResponseHttpCode() === 303) {
            return json_decode($httpRequest->getResponseBody());
        } else {
            return false;
        }
    }

    /**
     * Get client server
     *
     * @return array|boolean
     */
    public function getClientServer()
    {
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);
        if ($client === null) {
            return false;
        }
        $service = new OneDrive_Service_Drive($client);
        return array('client' => $client, 'service' => $service);
    }

    /**
     * List file versions
     *
     * @param string      $id       File id
     * @param null|string $cloud_id Cloud id
     *
     * @return array|boolean
     * @since  5.1.1
     */
    public function listVersions($id, $cloud_id = null)
    {
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);
        $id = DropfilesCloudHelper::replaceIdOneDrive($id, false);
        try {
            $service = new OneDrive_Service_Drive($client);

            $revisions = $service->revisions->listRevisions($id);
            $revs = array();
            foreach ($revisions->getValue() as $revision) {
                if ($revision->getId() !== 'current') {
                    $rev             = new stdClass();
                    $rev->id         = DropfilesCloudHelper::replaceIdOneDrive($id, true);
                    $rev->id_version = DropfilesCloudHelper::replaceIdOneDrive($revision->getId(), true);
                    $rev->size         = $revision->getSize();
                    $rev->created_time = date('Y-m-d H:i:s', strtotime($revision->getLastModifiedDateTime()));

                    $revs[] = $rev;
                }
            }

            return $revs;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Get version
     *
     * @param string $id  File id
     * @param string $vid Version id
     *
     * @return boolean|Onedrive_Service_Drive_Revision
     * @since  5.1.1
     */
    public function getVersion($id, $vid)
    {
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);
        $id = DropfilesCloudHelper::replaceIdOneDrive($id, false);
        $vid = DropfilesCloudHelper::replaceIdOneDrive($vid, false);
        try {
            $service = new OneDrive_Service_Drive($client);

            $revision = $service->revisions->get($id, $vid);

            return $revision;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Download version
     *
     * @param string $id  File id
     * @param string $vid Version id
     *
     * @return boolean
     * @since  5.1.1
     */
    public function downloadVersion($id, $vid)
    {
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);
        $id = DropfilesCloudHelper::replaceIdOneDrive($id, false);
        $vid = DropfilesCloudHelper::replaceIdOneDrive($vid, false);
        try {
            $service = new OneDrive_Service_Drive($client);

            $revision = $service->revisions->get($id, $vid);
            $downloadUrl = $revision->getDownloadUrl();

            if (!$downloadUrl) {
                return false;
            }

            $apifilefields = $this->apifilefields_thumb . $this->api_children;

            $item = $service->items->get($id, array('expand' => $apifilefields));
            $filename = $item->getName();
            $size = $revision->getSize();
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            if ($size !== 0) {
                header('Content-Length: ' . $size);
            }
            ob_clean();
            flush();
            // todo: Download large file
            echo file_get_contents($downloadUrl);
            jexit();
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Restore version
     *
     * @param string $id  File id
     * @param string $vid Version id
     *
     * @return mixed|Onedrive_Service_Drive_Revision|string
     * @since  5.1.1
     */
    public function restoreVersion($id, $vid)
    {
        $client = new OneDrive_Client();
        $client->setClientId($this->params->onedrive_client_id);
        $client->setClientSecret($this->params->onedrive_client_secret);
        $client->setAccessToken($this->params->onedrive_credentials);
        $id = DropfilesCloudHelper::replaceIdOneDrive($id, false);
        $vid = DropfilesCloudHelper::replaceIdOneDrive($vid, false);
        try {
            $service = new OneDrive_Service_Drive($client);
            // Get version information to return
            $version = $service->revisions->get($id, $vid);
            // Restore
            $service->revisions->restore($id, $vid);

            return $version;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return $this->lastError;
        }
    }
}
