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
 * Class DropfilesGoogle initialization and connection Google drive
 */
class DropfilesGoogle
{
    /**
     * Params
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
     * Full file resources params
     *
     * @var string $fullFileParams
     */
    protected $fullFileParams = 'id,name,size,fileExtension,description,originalFilename,createdTime,modifiedTime,appProperties,exportLinks,webContentLink';


    /**
     * DropfilesGoogle constructor.
     */
    public function __construct()
    {
        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        require_once 'GoogleV3/packages/autoload.php';

        $this->loadParams();
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
     * Load Params in config
     *
     * @return void
     */
    protected function loadParams()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $this->params = new stdClass();
        $this->params->google_client_id = $params->get('google_client_id');
        $this->params->google_client_secret = $params->get('google_client_secret');
        $this->params->google_credentials = $params->get('google_credentials');
    }

    /**
     * Save params
     *
     * @return void
     */
    protected function saveParams()
    {
        $path_admin_component = JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php';
        JLoader::register('DropfilesComponentHelper', $path_admin_component);
        DropfilesComponentHelper::setParams(array(
                'google_client_id'     => $this->params->google_client_id,
                'google_client_secret' => $this->params->google_client_secret,
                'google_credentials'   => $this->params->google_credentials
            ));
    }

    /**
     * Get GGD Author Url
     *
     * @return string
     */
    public function getAuthorisationUrl()
    {
        if (is_null($this->params->google_client_id) || empty($this->params->google_client_id)) {
            return false;
        }
        $client = new Google_Client();
        $client->setClientId($this->params->google_client_id);
        $google_redirect = JURI::root() . 'administrator/index.php?option=com_dropfiles&task=googledrive.authenticate';
        $client->setRedirectUri($google_redirect);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setState('');
        $client->setScopes(array(
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'));
        $tmpUrl = parse_url($client->createAuthUrl());
        $query = explode('&', $tmpUrl['query']);
        $port = isset($tmpUrl['port']) ? $tmpUrl['port'] : '';
        $return = $tmpUrl['scheme'] . '://' . $tmpUrl['host'] . $port;
        $return .= $tmpUrl['path'] . '?' . implode('&', $query);

        return $return;
    }

    /**
     * Authenticate google drive
     *
     * @return string|boolean
     */
    public function authenticate()
    {
        $code = JFactory::getApplication()->input->get('code', '', 'RAW');
        $client = new Google_Client();
        $client->setClientId($this->params->google_client_id);
        $client->setClientSecret($this->params->google_client_secret);
        $google_redirect = JURI::root() . 'administrator/index.php?option=com_dropfiles&task=googledrive.authenticate';
        $client->setRedirectUri($google_redirect);

        $client->fetchAccessTokenWithAuthCode($code);
        $token = json_encode($client->getAccessToken());
        return $token;
    }

    /**
     * Logout GGD
     *
     * @return void
     */
    public function logout()
    {
        $client = new Google_Client();
        $client->setClientId($this->params->google_client_id);
        $client->setClientSecret($this->params->google_client_secret);
        $client->setAccessToken($this->params->google_credentials);
        $client->revokeToken();
    }

    /**
     * Store Credentials
     *
     * @param string $credentials Credentials
     *
     * @return void
     */
    public function storeCredentials($credentials)
    {
        $this->params->google_credentials = $credentials;
        $this->saveParams();
    }

    /**
     * Get Credentials
     *
     * @return mixed
     */
    public function getCredentials()
    {
        return $this->params->google_credentials;
    }

    /**
     * Check Auth Google drive
     *
     * @return boolean
     */
    public function checkAuth()
    {
        $service = $this->getGoogleService();
        if (!$service) {
            return false;
        }
        try {
            $service->files->generateIds(array('count' => 1));
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Check folder exist in google drive
     *
     * @param string $id Folder id
     *
     * @return boolean
     */
    public function folderExists($id)
    {
        $service = $this->getGoogleService();
        try {
            $service->files->get($id);
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return false;
    }

    /**
     * Create new folder in Google drive
     *
     * @param string $title    Title
     * @param null   $parentId Parent id
     *
     * @return boolean|Google_Service_Drive_DriveFile
     */
    public function createFolder($title, $parentId = null)
    {
        $service = $this->getGoogleService();
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($title);
        $file->setMimeType('application/vnd.google-apps.folder');

        if ($parentId !== null) {
            $file->setParents(array($parentId));
        }
        try {
            $fileId = $service->files->create($file, array('fields' => 'id, name'));
            return $fileId;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Get all item in folder id with ordering
     *
     * @param string $folder_id Folder id
     * @param string $ordering  Ordering
     * @param string $direction Direction
     *
     * @return array|boolean
     */
    public function listFiles($folder_id, $ordering = 'ordering', $direction = 'asc')
    {
        $service = $this->getGoogleService();
        try {
            $q = "'" . $folder_id;
            $q .= "' in parents and trashed=false and mimeType != 'application/vnd.google-apps.folder' ";
            $fields = 'id,name,size,fileExtension,description,originalFilename,createdTime,modifiedTime,appProperties,exportLinks';
            $fs = $service->files->listfiles(array('q' => $q, 'fields' => $fields));
            //$fs = $service->files->listfiles(array('q' =>  "'".$folder_id."' in parents"));

            $files = array();
            foreach ($fs->getFiles() as $f) {
                if ($f instanceof Google_Service_Drive_DriveFile && $f->getMimeType() !== 'application/vnd.google-apps.folder') {
                    $file                = new stdClass();
                    $file->id            = $f->getId();
                    $file->title         = $f->getOriginalFilename() ? JFile::stripExt($f->getName()) : $f->getName();
                    $file->description   = $f->getDescription();
                    $file->ext           = $f->getFileExtension() ? $f->getFileExtension() : JFile::getExt($f->getOriginalFilename());
                    $file->size          = $f->getSize();
                    $file->created_time  = date('Y-m-d H:i:s', strtotime($f->getCreatedTime()));
                    $file->modified_time = date('Y-m-d H:i:s', strtotime($f->getModifiedTime()));
                    $file->version       = '';
                    $file->hits          = 0;
                    $file->ordering      = 0;
                    $properties          = $f->getAppProperties();
                    $file->file_tags     = isset($properties->file_tags) ? $properties->file_tags : '';
                    if ($f->getFileExtension() === null && $f->getSize() === null && $f->getId() !== '') {
                        $ExportLinks = $f->getExportLinks();
                        if ($ExportLinks !== null) {
                            //uksort($ExportLinks, create_function('$a,$b', 'return strlen($a) < strlen($b);'));
                            uksort($ExportLinks, function ($a, $b) {
                                return strlen($a) < strlen($b);
                            });
                            $ext_tmp   = explode('=', reset($ExportLinks));
                            $file->ext = end($ext_tmp);
                        }
                        $file->created_time  = $f->getCreatedTime();
                        $file->modified_time = $f->getModifiedTime();
                        $file->version       = $f->getVersion();
                    }
                    $file->version  = isset($properties->version) ? $properties->version : '';
                    $file->hits     = isset($properties->hits) ? $properties->hits : 0;
                    $file->ordering = isset($properties->order) ? $properties->order : 0;

                    $files[] = $file;
                    unset($file);
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
     * @param array  $a         Input array
     * @param string $subkey    Subkey
     * @param string $direction Direction
     *
     * @return array
     */
    private function subvalSort($a, $subkey, $direction)
    {
        $c = null;
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
     * Get all files in folder
     *
     * @param string $folder_id Folder id
     *
     * @return array|boolean
     */
    public function listFilesInFolder($folder_id)
    {
        $service = $this->getGoogleService();

        try {
            $q = "'" . $folder_id;
            $q .= "' in parents and trashed=false and mimeType != 'application/vnd.google-apps.folder' ";
            $fs = $service->files->listfiles(
                array(
                    'q'        => $q,
                    'fields'   => 'files(id,name,size,fileExtension,originalFilename,createdTime,modifiedTime,appProperties,exportLinks)',
                    'pageSize' => 500
                )
            );
            $fs = $fs->getFiles();

            $files = array();
            foreach ($fs as $f) {
                if ($f instanceof Google_Service_Drive_DriveFile) {
                    $file                = new stdClass();
                    $file->id            = $f->getId();
                    $file->title         = $f->getOriginalFilename() ? JFile::stripExt($f->getName()) : $f->getName();
                    $file->description   = $f->getDescription();
                    $file->ext           = $f->getFileExtension() ? $f->getFileExtension() : JFile::getExt($f->getOriginalFilename());
                    $file->size          = $f->getSize();
                    $file->created_time  = date('Y-m-d H:i:s', strtotime($f->getCreatedTime()));
                    $file->modified_time = date('Y-m-d H:i:s', strtotime($f->getModifiedTime()));
                    $properties          = $f->getAppProperties();
                    if (!empty($properties)) {
                        $file->file_tags = isset($properties->file_tags) ? $properties->file_tags : '';
                        $file->version   = isset($properties->version) ? $properties->version : '';
                        $file->hits      = isset($properties->hits) ? $properties->hits : 0;
                        $file->ordering  = isset($properties->ordering) ? $properties->ordering : 'desc';
                    }

                    if ($f->getFileExtension() === null && $f->getSize() === null && $f->getId() !== '') {
                        $ExportLinks = $f->getExportLinks();
                        if ($ExportLinks !== null) {
                            uksort($ExportLinks, function ($a, $b) {
                                return strlen($a) < strlen($b);
                            });
                            $ext_tmp   = explode('=', reset($ExportLinks));
                            $file->ext = end($ext_tmp);
                        }
                        $file->created_time  = $f->getCreatedTime();
                        $file->modified_time = $f->getModifiedTime();
                    }

                    $files[$file->id] = $file;
                    unset($file);
                } else {
                    return false;
                }
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $files;
    }

    /**
     * Upload file to google drive
     *
     * @param string $filename    File name
     * @param string $fileContent File content
     * @param string $mime        Mime type
     * @param string $id_folder   Folder id
     *
     * @return boolean|Google_Service_Drive_DriveFile
     */
    public function uploadFile($filename, $fileContent, $mime, $id_folder)
    {
        $service = $this->getGoogleService();

        $file = new Google_Service_Drive_DriveFile();

        $file->setParents(array($id_folder));
        $file->setName($filename);
        $file->setMimeType($mime);

        try {
            $insertedFile = $service->files->create(
                $file,
                array('data' => $fileContent, 'mimeType' => $mime, 'uploadType' => 'media')
            );

            return $insertedFile;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Get File object
     *
     * @param Google_Service_Drive_DriveFile $f File object
     *
     * @return stdClass
     */
    public function getFileObj($f)
    {
        $service = $this->getGoogleService();
        $f = $service->files->get($f->getId(), array(
            'fields' => 'id, name, size, description, fileExtension, originalFilename, createdTime, modifiedTime, exportLinks'
        ));
        $file = new stdClass();
        $file->id = $f->getId();
        $file->file_id = $file->id;
        $file->title = $f->getOriginalFilename() ? JFile::stripExt($f->getName()) : $f->getName();
        $file->description = $f->getDescription();
        $file->ext = $f->getFileExtension() ? $f->getFileExtension() : JFile::getExt($f->getOriginalFilename());
        $file->size = $f->getSize();

        $date = JFactory::getDate();
        $file->created_time = $date->setTimestamp(strtotime($f->getCreatedTime()))->toSql();
        $file->modified_time = $date->setTimestamp(strtotime($f->getModifiedTime()))->toSql();

        if ($f->getFileExtension() === null && $f->getSize() === null && $f->getId() !== '') {
            $ExportLinks = $f->getExportLinks();
            if ($ExportLinks !== null) {
                uksort($ExportLinks, function ($a, $b) {
                    return strlen($a) < strlen($b);
                });
                $ext_tmp = explode('=', reset($ExportLinks));
                $file->ext = end($ext_tmp);
            }
            $file->created_time = $date->setTimestamp(strtotime($f->getCreatedTime()))->toSql();
            $file->modified_time = $date->setTimestamp(strtotime($f->getModifiedTime()))->toSql();
        }

        return $file;
    }

    /**
     * Get file info
     *
     * @param string $id       File id
     * @param null   $cloud_id Cloud id
     *
     * @return array|boolean
     */
    public function getFileInfos($id, $cloud_id = null)
    {
        $service = $this->getGoogleService();

        try {
            $file = $service->files->get($id, array(
                'fields' => 'id,name,size,parents,appProperties,description,fileExtension,originalFilename,createdTime,modifiedTime,exportLinks,thumbnailLink'
            ));

            if (!$this->checkParents($file, $cloud_id)) {
                return false;
            }

            $data                  = array();
            $data['id']            = $id;
            $data['title']         = $file->getOriginalFilename() ? JFile::stripExt($file->getName()) : $file->getName();
            $data['description']   = $file->getDescription();
            $data['file']          = $file->getName();
            $data['ext']           = $file->getFileExtension() ? $file->getFileExtension() : JFile::getExt($file->getOriginalFilename());
            $data['created_time']  = date('Y-m-d H:i:s', strtotime($file->getCreatedTime()));
            $data['modified_time'] = date('Y-m-d H:i:s', strtotime($file->getModifiedTime()));
            $data['size']          = $file->getSize();
            // Prepare for thumbnail feature
            $data['thumbnail']     = $file->getThumbnailLink();
            $properties = $file->getAppProperties();

            $data['file_tags']    = isset($properties->file_tags) ? $properties->file_tags : '';
            $data['hits']         = isset($properties->hits) ? $properties->hits : 0;
            $data['version']      = isset($properties->version) ? $properties->version : '';
            $data['publish']      = isset($properties->publish) ? $properties->publish : '';
            $data['ordering']     = isset($properties->order) ? $properties->order : 0;
            $data['publish_down'] = isset($properties->publish_down) ? $properties->publish_down : '';
            $data['canview']      = isset($properties->canview) ? $properties->canview : '';

            if ($file->getFileExtension() === null && $file->getSize() === null && $file->getId() !== null) {
                $ExportLinks = $file->getExportLinks();
                if ($ExportLinks !== null) {
                    //uksort($ExportLinks, create_function('$a,$b', 'return strlen($a) < strlen($b);'));
                    uksort($ExportLinks, function ($a, $b) {
                        return strlen($a) < strlen($b);
                    });
                    $ext_tmp = explode('=', reset($ExportLinks));
                    $data['ext'] = end($ext_tmp);
                    $data['size'] = 0;
                    $data['version'] = $file->getVersion();
                }
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $data;
    }

    /**
     * Save file info
     *
     * @param array $datas    Datas
     * @param null  $cloud_id Cloud id
     *
     * @return boolean
     */
    public function saveFileInfos($datas, $cloud_id = null)
    {
        if (empty($datas['id'])) {
            $datas['id'] = JFactory::getApplication()->input->getString('id');
        }
        $service = $this->getGoogleService();
        try {
            $file = $service->files->get($datas['id'], array(
                'fields' => 'size,name,description,parents,fileExtension,appProperties'
            ));
            $params = array('uploadType' => 'multipart');
            if (!$this->checkParents($file, $cloud_id)) {
                return false;
            }

            $updatedFile = new Google_Service_Drive_DriveFile();
            if (isset($datas['title'])) {
                $updatedFile->setName($datas['title']);
            }
            if (isset($datas['description'])) {
                $updatedFile->setDescription($datas['description']);
            }
            // Uppload new version
            if (isset($datas['data'])) {
                $params['data'] = $datas['data'];
            }

            // Update properties
            $google_file_properties = array('version', 'hits', 'publish', 'publish_down', 'canview');
            $updateProperties = new \stdClass;

            foreach ($google_file_properties as $key) {
                if (!isset($datas[$key])) {
                    continue;
                }
                $updateProperties->{$key} = $datas[$key];
            }
            if (isset($datas['newRevision'])) {
                $params['keepRevisionForever'] = true;
            } else {
                $params['keepRevisionForever'] = false;
            }
            $updatedFile->setAppProperties($updateProperties);

            $result = $service->files->update($datas['id'], $updatedFile, $params);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return  $this->lastError;
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
        $service = $this->getGoogleService();

        try {
            $file = new Google_Service_Drive_DriveFile();
            $file->setName($filename);
            $service->files->update($id, $file, array());
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Insert hits file
     *
     * @param string $id File id
     *
     * @return boolean
     */
    public function incrHits($id)
    {
        $service = $this->getGoogleService();
        try {
            $file = $service->files->get($id, array(
                'fields' => 'id,name,appProperties'
            ));
            $properties = $file->getAppProperties();

            $hits = isset($properties->hits) ? (int) $properties->hits : 0;
        } catch (Exception $e) {
            $hits = 0;
        }

        try {
            $file = new Google_Service_Drive_DriveFile();
            $newProperties = new \stdClass;
            $newProperties->hits = $hits + 1;
            $service->files->update($id, $file, array(
                'appProperties' => $newProperties
            ));
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Reorder items
     *
     * @param array $files Files
     *
     * @return boolean
     */
    public function reorder($files)
    {
        $service = $this->getGoogleService();
        try {
            foreach ($files as $key => $file) {
                $newProperty = new Google_Service_Drive_Property();
                $newProperty->setKey('order');
                $newProperty->setValue($key);
                $newProperty->setVisibility('PRIVATE');
                $service->properties->insert($file, $newProperty);
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }
    /**
     * Download large file by chunks
     *
     * @param object         $file        File object
     * @param string         $contentType Content type
     * @param string         $disposition Content Disposition
     * @param boolean|string $saveToFile  File path
     *
     * @return void
     */
    public function downloadLargeFile($file, $contentType, $disposition = 'attachment', $saveToFile = false)
    {
        $chunkSizeBytes = 5*1024*1024;

        if ($saveToFile === false) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            ob_start();
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: ' . $disposition . '; filename="'.$file->title . '.' . $file->ext . '"');
            header('Expires: 0');
            header('Pragma: public');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Description: File Transfer');
            header('Content-Length: ' . $file->size);


            header('Content-Transfer-Encoding: chunked');
            header('Connection: keep-alive');
            ob_clean();
            flush();
        }

        $client = new Google_Client();
        $client->setClientId($this->params->google_client_id);
        $client->setClientSecret($this->params->google_client_secret);
        $client->setAccessToken($this->params->google_credentials);

        try {
            // Begin Use of MediaFileDownload
            // Call the API with the media download, defer so it doesn't immediately return.
            $client->setDefer(true);
            $service = new Google_Service_Drive($client);

            $request = $service->files->get($file->id, array('alt' => 'media'));

            $media = new Google_Http_MediaFileDownload(
                $client,
                $request,
                $file->mimeType,
                null,
                true,
                $chunkSizeBytes
            );

            $media->setFileSize($file->size);

            $status = true;
            $progress = 0;
            $previousprogress = 0;

            if ($saveToFile !== false) {
                $saveToFileHandler = fopen($saveToFile, 'w+');
            }
            while ($status) {
                $status = $media->nextChunk();

                if (!$status) {
                    break;
                }

                $response = $status;
                $range = explode('-', $response->getHeaderLine('content-range'));
                $range = explode('/', $range[1]);
                $progress = $range[0];
                $mediaSize = $range[1];

                if ($progress > $previousprogress) {
                    if ($saveToFile === false) {
                        // Clean buffer and end buffering
                        while (ob_get_level()) {
                            ob_end_clean();
                        }
                        // Start buffering
                        if (!ob_get_level()) {
                            ob_start();
                        }
                        // Flush the content
                        $contentbody = $response->getBody();
                        echo $contentbody;
                        ob_flush();
                        flush();
                    } elseif (isset($saveToFileHandler)) {
                        // Flush the content
                        $contentbody = $response->getBody();
                        fwrite($saveToFileHandler, $contentbody);
                    }

                    $previousprogress = $progress;
                    usleep(200);
                }
                if (((int) $mediaSize - 1) === (int) $progress) {
                    ob_end_flush();
                    $client->setDefer(false);
                    $status = false;
                    return;
                }
            }

            if ($saveToFile !== false && isset($saveToFileHandler)) {
                fclose($saveToFileHandler);
            }
        } catch (Google_Service_Exception $e) {
            JLog::add('mediadownloadfromgoogledriveincurrentfolder error Google_Service_Exception' . $e->getMessage(), JLog::ERROR, 'dropfiles');
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export -- debug
            JLog::add('mediadownloadfromgoogledriveincurrentfolder error Google_Service_Exception errors ' . var_export($e->getErrors(), true), JLog::ERROR, 'dropfiles');
        } catch (Exception $e) {
            JLog::add('mediadownloadfromgoogledriveincurrentfolder error ' . $e->getMessage(), JLog::ERROR, 'dropfiles');
        }
        $client->setDefer(false);
    }
    /**
     * Download file
     *
     * @param string  $id       File id
     * @param null    $cloud_id Cloud id
     * @param null    $version  Version
     * @param integer $preview  Preview
     * @param boolean $getDatas Download data of file
     *
     * @return boolean|stdClass
     */
    public function download($id, $cloud_id = null, $version = false, $preview = 0, $getDatas = false)
    {
        try {
            $service = $this->getGoogleService();
            $file    = $service->files->get($id, array('fields' => $this->fullFileParams . ',parents,mimeType'));

            $downloadUrl = $file->getWebContentLink();
            $mineType    = $file->getMimeType();

            if ($downloadUrl === null && $file->getSize() === null && $file->getId() !== '') {
                $ExportLinks = $file->getExportLinks();
                if ($ExportLinks !== null) {
                    if ($preview && isset($ExportLinks['application/pdf']) &&
                        strpos($mineType, 'vnd.google-apps') !== false) {
                        $downloadUrl = $ExportLinks['application/pdf'];
                    } else {
                        uksort($ExportLinks, function ($a, $b) {
                            return strlen($a) < strlen($b);
                        });
                        $ext_tmp     = explode('=', reset($ExportLinks));
                        $exportMineTypeKeys = array_keys($ExportLinks);
                        $downloadUrl = reset($ExportLinks);
                    }
                }
            }

            if ($downloadUrl) {
                $ret        = new stdClass();
                $ret->id = $file->getId();
                $ret->description = $file->getDescription();
                $ret->mimeType = $file->getMimeType();
                if (isset($exportMineTypeKeys)) {
                    $ret->exportMineType = reset($exportMineTypeKeys);
                }
                if ($getDatas) {
                    if ($version !== false) {
                        $revision = $service->revisions->get($id, $version, array('fields' => 'id,name,originalFilename'));
                        $fileRequest = $service->revisions->get($id, $version, array('alt' => 'media'));
                    } else {
                        $fileRequest = $service->files->get($id, array('alt' => 'media'));
                    }
                    if ($fileRequest->getStatusCode() === 200) {
                        $ret->datas = $fileRequest->getBody();
                    }
                } else {
                    if ($version !== false) {
                        $revision = $service->revisions->get($id, $version, array('fields' => 'id,name,originalFilename'));
                    }
                    $ret->downloadUrl = $downloadUrl;
                }

                if ($file->getName()) {
                    $ret->title = $file->getOriginalFilename() ? JFile::stripExt($file->getName()) : $file->getName();
                } else {
                    $ret->title = JFile::stripExt($file->getOriginalFilename());
                    $ret->title = JFile::stripExt($ret->title);
                }

                $properties = (object) $file->getAppProperties();
                $version = isset($properties->versionNumber) ? $properties->versionNumber : (isset($properties->version) ? $properties->version : '');
                $ret->versionNumber = $version;
                $ret->version       = $version;
                $ret->mimeType = $mineType;
                $ret->file_tags = isset($properties->file_tags) ? $properties->file_tags : '';
                $ext_file_name = JFile::getExt($file->getOriginalFilename());
                $ret->ext = $file->getFileExtension() ? $file->getFileExtension() : $ext_file_name;
                if (isset($revision) && $revision->getOriginalFilename() !== null) {
                    $ret->size = $revision->getSize();
                } else {
                    $ret->size = $file->getSize();
                }
                if ($file->getFileExtension() === null && isset($ext_tmp)) {
                    $ret->ext = end($ext_tmp);
                }
                if ($preview && strpos($mineType, 'vnd.google-apps') !== false) {
                    $ret->ext = 'pdf';
                }

                return $ret;
            } else {
                // The file doesn't have any content stored on Drive.
                return false;
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Download google documents
     *
     * @param string $id       Google document file id
     * @param string $fileMine Google document mine type
     *
     * @return boolean|expectedClass|WPFDGoogle_Http_Request
     */
    public function downloadGoogleDocument($id, $fileMine)
    {
        try {
            $service = $this->getGoogleService();

            return $service->files->export($id, $fileMine);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();

            return false;
        }
    }

    /**
     * Delete items
     *
     * @param string $id       File id
     * @param null   $cloud_id Cloud id
     *
     * @return boolean
     */
    public function delete($id, $cloud_id = null)
    {
        $service = $this->getGoogleService();
        try {
            $file = $service->files->get($id, array('fields' => 'parents'));
            if (!$this->checkParents($file, $cloud_id)) {
                return false;
            }
            $service->files->delete($id);
        } catch (Exception $e) {
            if ($e->getCode() === 404) { // File already deleted on GD
                return true;
            }

            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Get all file version
     *
     * @param string $id       File id
     * @param null   $cloud_id Cloud id
     *
     * @return array|boolean
     */
    public function listVersions($id, $cloud_id = null)
    {
        $service = $this->getGoogleService();

        try {
            $file = $service->files->get($id, array('fields' => 'parents,headRevisionId'));

            if (!$this->checkParents($file, $cloud_id)) {
                return false;
            }
            $revisions = $service->revisions->listRevisions($id, array(
                'fields' => 'revisions(id,size,modifiedTime)'
            ));
            $revs = array();
            foreach ($revisions->getRevisions() as $revision) {
                if ($revision instanceof Google_Service_Drive_Revision && $revision->getId() !== $file->getHeadRevisionId()) {
                    $rev               = new stdClass();
                    $rev->id           = $id;
                    $rev->id_version   = $revision->getId();
                    $rev->size         = $revision->getSize();
                    $rev->created_time = date('Y-m-d H:i:s', strtotime($revision->getModifiedTime()));
                    $revs[]            = $rev;
                }
            }

            return $revs;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Delete reversion
     *
     * @param string $id       File id
     * @param null   $revision Revision
     * @param null   $cloud_id Cloud id
     *
     * @return boolean
     */
    public function deleteRevision($id, $revision = null, $cloud_id = null)
    {
        $service = $this->getGoogleService();
        try {
            $file = $service->files->get($id, array('fields' => 'parents'));

            if (!$this->checkParents($file, $cloud_id)) {
                return false;
            }
            $service->revisions->delete($id, $revision);
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return $this->lastError;
        }
    }

    /**
     * Update revision
     *
     * @param string $id         File id
     * @param null   $revisionId Revision
     * @param null   $cloud_id   Cloud id
     *
     * @return boolean|Google_Service_Drive_Revision
     */
    public function updateRevision($id, $revisionId = null, $cloud_id = null)
    {
        $service = $this->getGoogleService();

        try {
            $file = $service->files->get($id, array('fields' => 'parents'));

            if (!$this->checkParents($file, $cloud_id)) {
                return false;
            }
            // Get all revisions
            $revisions = $service->revisions->listRevisions($id, array('fields'=>'revisions(id,modifiedTime)'));
            $revisions = $revisions->getRevisions();
            $currentVersion = $service->revisions->get($id, $revisionId, array('fields' =>'modifiedTime'));
            $currentRevisionTime = strtotime($currentVersion->getModifiedTime());
            // Compare with current one
            if (!empty($revisions)) {
                foreach ($revisions as $revision) {
                    if (!$revision instanceof Google_Service_Drive_Revision) {
                        continue;
                    }
                    $revisionTime = strtotime($revision->getModifiedTime());
                    // Delete all newer version
                    if ($revisionTime > $currentRevisionTime) {
                        $this->deleteRevision($id, $revision->getId());
                    }
                }
            }

            return $currentVersion;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return $this->lastError;
        }
    }

    /**
     * Get sub folders in folder
     *
     * @param string $folderId Folder id
     *
     * @throws Exception Throw when application can not start
     * @return array
     */
    public function getSubFolders($folderId)
    {
        $service = $this->getGoogleService();
        $params      = JComponentHelper::getParams('com_dropfiles');
        $base_folder = $params->get('google_base_folder');
        $pageToken = null;
        $datas = array();
        do {
            try {
                $parameters = array();
                $parameters['q'] = 'trashed=false';
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $q = "'" . $folderId;
                $q .= "' in parents and trashed=false and mimeType = 'application/vnd.google-apps.folder' ";
                $fs = $service->files->listfiles(array('q' => $q, 'fields' => 'files/id,files/name', 'pageSize' => 1000));
                $files = $fs->getFiles();

                if ($params->get('sync_log_option') === 1) {
                    $erros = 'folderId - ' . $folderId . PHP_EOL;
                    JLog::add($erros, JLog::INFO, 'com_dropfiles');
                }
                foreach ($files as $f) {
                    if ($f instanceof Google_Service_Drive_DriveFile) {
                        $idFile = $f->getId();
                        if ($folderId !== $base_folder) {
                            $datas[$idFile] = array('title' => $f->getName(), 'parent_id' => $folderId);
                        } else {
                            $datas[$idFile] = array('title' => $f->getName(), 'parent_id' => 1);
                        }
                        if ($params->get('sync_log_option') === 1) {
                            $erros = 'Child - ' . $idFile . ': ' . json_encode($datas[$idFile]) . PHP_EOL;
                            JLog::add($erros, JLog::INFO, 'com_dropfiles');
                        }
                    }
                }
                // $pageToken = $children->getNextPageToken();
            } catch (Exception $e) {
                print 'An error occurred: ' . $e->getMessage() . $e->getTraceAsString();
                if ($params->get('sync_log_option') === 1) {
                    $erros = $e->getMessage() . $e->getTraceAsString() . PHP_EOL;
                    JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                }
                $datas = false;
                $pageToken = null;
                throw new Exception('getFilesInFolder - Google_Http_REST error ' . $e->getCode());
            }
        } while ($pageToken);

        return $datas;
    }

    /**
     * Get files in folder
     *
     * @param string $folderId Folder id
     * @param array  $datas    Data
     *
     * @throws Exception Throw when application can not start
     * @return void
     */
    public function getFilesInFolder($folderId, &$datas)
    {
        $service = $this->getGoogleService();
        $params      = JComponentHelper::getParams('com_dropfiles');
        $base_folder = $params->get('google_base_folder');

        $pageToken = null;
        if ($datas === false) {
            throw new Exception('getFilesInFolder - datas error ');
        }
        if (!is_array($datas)) {
            $datas = array();
        }
        do {
            try {
                $parameters = array();
                $parameters['q'] = 'trashed=false';
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $q = "'" . $folderId;
                $q .= "' in parents and trashed=false and mimeType = 'application/vnd.google-apps.folder' ";
                $fs = $service->files->listfiles(array('q' => $q, 'fields' => 'files/id,files/name', 'pageSize' => 1000));
                $files = $fs->getFiles();

                if ($params->get('sync_log_option') === 1) {
                    $erros = 'folderId - ' . $folderId . PHP_EOL;
                    JLog::add($erros, JLog::INFO, 'com_dropfiles');
                }
                foreach ($files as $f) {
                    if ($f instanceof Google_Service_Drive_DriveFile) {
                        $idFile = $f->getId();
                        if ($folderId !== $base_folder) {
                            $datas[$idFile] = array('title' => $f->getName(), 'parent_id' => $folderId);
                        } else {
                            $datas[$idFile] = array('title' => $f->getName(), 'parent_id' => 1);
                        }
                        if ($params->get('sync_log_option') === 1) {
                            $erros = 'Child - ' . $idFile . ': ' . json_encode($datas[$idFile]) . PHP_EOL;
                            JLog::add($erros, JLog::INFO, 'com_dropfiles');
                        }
                        $this->getFilesInFolder($idFile, $datas);
                    }
                }


                // $pageToken = $children->getNextPageToken();
            } catch (Exception $e) {
                print 'An error occurred: ' . $e->getMessage() . $e->getTraceAsString();
                if ($params->get('sync_log_option') === 1) {
                    $erros = $e->getMessage() . $e->getTraceAsString() . PHP_EOL;
                    JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                }
                $datas = false;
                $pageToken = null;
                throw new Exception('getFilesInFolder - Google_Http_REST error ' . $e->getCode());
            }
        } while ($pageToken);
    }


    /**
     * Get List folder on Google Drive
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
     * @return Google_Service_Drive_DriveFile|boolean
     */
    public function moveFile($fileId, $newParentId)
    {
        $service = $this->getGoogleService();
        $updatedFile = null;
        try {
            $file = $service->files->get($fileId, array('fields' => 'id,parents'));
            $oldParents = $file->getParents();

            $newFile = new Google_Service_Drive_DriveFile();
            $updatedFile = $service->files->update($fileId, $newFile, array(
                'removeParents' => $oldParents,
                'addParents' => $newParentId
            ));

            return $updatedFile;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }


    /**
     * Copy a file.
     *
     * @param string $fileId      File id
     * @param string $newParentId New parent id
     *
     * @return Google_Service_Drive_DriveFile|boolean
     */
    public function copyFile($fileId, $newParentId)
    {
        $service = $this->getGoogleService();
        try {
            $newFile = new Google_Service_Drive_DriveFile();
            $copiedFile = $service->files->copy($fileId, $newFile, array(
                'fields' => 'id, parents',
            ));

            $oldParents = $copiedFile->getParents();
            $updatedFile = $service->files->update($copiedFile->getId(), $newFile, array(
                'removeParents' => $oldParents,
                'addParents' => $newParentId
            ));
            return $updatedFile;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Get all tags file on Google (it seem not use anywhere)
     *
     * @param array $googleCats Google categories
     *
     * @return array|boolean
     */
    public function getAllTagsFileOnGoogle($googleCats)
    {
        $catTags = array();
        if (count($googleCats)) {
            $q_tmp = array();
            foreach ($googleCats as $gCat) {
                $q_tmp[] = " '" . $gCat->cloud_id . "' in parents ";
            }
            $q1 = '(' . implode(' or ', $q_tmp) . ')';

            $client = new Google_Client();
            $client->setClientId($this->params->google_client_id);
            $client->setClientSecret($this->params->google_client_secret);
            try {
                $client->setAccessToken($this->params->google_credentials);
                $service = new Google_Service_Drive($client);
                $q = $q1 . " and trashed=false and mimeType != 'application/vnd.google-apps.folder' ";
                //$q .= " and properties has { key='file_tags' and value !='' and visibility='PRIVATE' }";
                $fs = $service->files->listfiles(array('q' => $q));
                $client->setUseBatch(true);
                $batch = new Google_Http_Batch($client);
                $optParams = array('visibility' => 'PRIVATE');
                $keys = array();
                $fParents = array();
                foreach ($fs as $f) {
                    $fid = $f->getId();
                    $keys[] = $fid;
                    $fParents[$fid] = $f->parents[0]->getId();
                    $req1 = $service->properties->get($fid, 'file_tags', $optParams);
                    $batch->add($req1, $fid);
                }

                $results = $batch->execute();

                foreach ($keys as $key) {
                    $property = $results['response-' . $key];

                    if (is_object($property) && get_class($property) === 'Google_Service_Drive_Property') {
                        $file_tags = $property->getValue();
                        if (!empty($file_tags)) {
                            $pid = $fParents[$key];
                            if (isset($catTags[$pid])) {
                                $catTags[$pid] = array_merge($catTags[$pid], explode(',', $file_tags));
                            } else {
                                $catTags[$pid] = explode(',', $file_tags);
                            }
                        }
                    }
                }


                foreach ($catTags as $key => $tags) {
                    $catTags[$key] = array_unique($tags);
                }

                return $catTags;
            } catch (Exception $e) {
                $this->lastError = $e->getMessage();
                return false;
            }
        }

        return $catTags;
    }

    /**
     * Retrieve a list of File resources.
     *
     * @param string $q Search term
     *
     * @return array List of Google_Service_Drive_DriveFile resources.
     */
    public function getAllFilesInAppFolder($q)
    {
        $service   = $this->getGoogleService();
        $result    = array();
        $pageToken = null;
        $listfiles = array();
        do {
            try {
                $parameters      = array();
                $parameters['q'] = $q;
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $parameters['fields'] = 'files(id,name,fileExtension,size,originalFilename,exportLinks,createdTime,modifiedTime,appProperties)';
                $files                = $service->files->listFiles($parameters);
                $result               = array_merge($result, $files->getfiles());
                $pageToken            = $files->getNextPageToken();
            } catch (Exception $e) {
                $this->lastError = $e->getMessage();

                return false;
            }
        } while ($pageToken);

        foreach ($result as $k => $f) {
            if ($f instanceof Google_Service_Drive_DriveFile) {
                $file                = new stdClass();
                $file->id            = $f->getId();
                $file->title         = $f->getOriginalFilename() ? JFile::stripExt($f->getName()) : $f->getName();
                $file->ext           = $f->getFileExtension() ? $f->getFileExtension() : JFile::getExt($f->getOriginalFilename());
                $file->size          = $f->getSize();
                $file->created_time  = date('Y-m-d H:i:s', strtotime($f->getCreatedTime()));
                $file->modified_time = date('Y-m-d H:i:s', strtotime($f->getModifiedTime()));
                if ($f->getFileExtension() === null && $f->getSize() === null && $f->getId() !== '') {
                    $ExportLinks = $f->getExportLinks();
                    if ($ExportLinks !== null) {
                        //uksort($ExportLinks, create_function('$a,$b', 'return strlen($a) < strlen($b);'));
                        uksort($ExportLinks, function ($a, $b) {
                            return strlen($a) < strlen($b);
                        });
                        $ext_tmp           = explode('=', reset($ExportLinks));
                        $file->ext         = end($ext_tmp);
                        $file->urlDownload = reset($ExportLinks);
                    }
                }
                $file->created_time  = $f->getCreatedTime();
                $file->modified_time = $f->getModifiedTime();
                $properties          = $f->getAppProperties();
                $file->file_tags     = isset($properties->file_tags) ? $properties->file_tags : '';

                $listfiles[] = $file;
            }
        }
        return $listfiles;
    }

    /**
     * Print a file's parents.
     *
     * @param string $fileId ID of the file to print parents for.
     *
     * @return array|boolean
     */
    public function getParentInfo($fileId)
    {
        $service = $this->getGoogleService();
        try {
            $file = $service->files->get($fileId, array(
                'fields' => 'id,name,parents'
            ));
            $parents = $file->getParents();
            if (!isset($parents[0])) {
                return false;
            }
            $item_tmp = $this->getFileInfos($parents[0]);

            return $item_tmp;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Search condition on google
     *
     * @param array  $params Search params
     * @param string $q      Search term
     *
     * @return string
     */
    public function searchCondition($params, $q)
    {
        $folders = $this->getListFolder($params->get('google_base_folder'));
        foreach ($folders as $id => $folder) {
            $q .= " '" . $id . "' in parents or";
        }
        return $q;
    }

    /**
     * Get Google service
     *
     * @return Google_Service_Drive|boolean
     * @since  v5.1.3
     */
    private function getGoogleService()
    {
        if (is_null($this->params->google_client_id) ||
            is_null($this->params->google_client_secret) ||
            is_null($this->params->google_credentials) ||
            empty($this->params->google_client_id) ||
            empty($this->params->google_client_secret) ||
            empty($this->params->google_credentials)) {
            return false;
        }
        try {
            $client = new Google_Client();
            $client->setClientId($this->params->google_client_id);
            $client->setClientSecret($this->params->google_client_secret);
            $client->setAccessToken($this->params->google_credentials);

            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken();
                $newToken = $client->getAccessToken();
                $this->storeCredentials(json_encode($newToken));
            }

            $service = new Google_Service_Drive($client);

            return $service;
        } catch (Exception $ex) {
            $this->lastError = $ex->getMessage();
            return false;
        }
    }

    /**
     * Check parents
     *
     * @param Google_Service_Drive_DriveFile $file     Google Drive File
     * @param string                         $cloud_id Cloud id
     *
     * @return boolean
     * @since  5.1.3
     */
    public function checkParents($file, $cloud_id)
    {
        if ($cloud_id !== null) {
            $parents = $file->getParents();
            foreach ($parents as $parent) {
                if ($parent === $cloud_id) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Get start page token for watch changes
     *
     * @return boolean
     * @since  5.2
     */
    public function getStartPageToken()
    {
        $service = $this->getGoogleService();

        try {
            $response = $service->changes->getStartPageToken(array(
                'fields' => '*'
            ));

            if ($response->getStartPageToken()) {
                return $response->getStartPageToken();
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }

        return false;
    }

    /**
     * Watch changes
     *
     * @param string $pageToken Page token
     * @param string $id        Universally Unique IDentifier (UUID). BINARY(16)
     *
     * @return boolean|array
     * @since  5.2
     */
    public function watchChanges($pageToken, $id)
    {
        $service = $this->getGoogleService();
        $root    = JUri::root();
        $scheme  = parse_url($root, PHP_URL_SCHEME);

//        if ($scheme !== 'https') {
//            return array(
//                'error' => $errorCode,
//                'message' => $message
//            );
//        }

        $callbackUrl = JURI::root() . 'index.php?option=com_dropfiles&task=frontgoogle.listener';
        $channel = new Google_Service_Drive_Channel();
        $channel->setAddress($callbackUrl);
        $channel->setKind('api#channel');
        $channel->setId($id);
        $channel->setType('web_hook'); // Important
        $channel->setToken($pageToken);
        $channel->setExpiration((time() + 604800) * 1000); // 1 week in milliseconds

        try {
            $response = $service->changes->watch($pageToken, $channel, array(
                'fields'   => '*',
                'restrictToMyDrive' => true, // Listen only on My Drive hierarchy to reduce data
                'pageSize' => 500
            ));

            if ($response->getResourceId()) {
                return array(
                    'kind'       => $response->getKind(),
                    'id'         => $response->getId(),
                    'resourceId' => $response->getResourceId(),
                    'token'      => $response->getToken(),
                    'address'    => $response->getAddress(),
                    'expiration' => $response->getExpiration() // Milliseconds
                );
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageData = json_decode($message, true);
            $message = isset($messageData['error']['message']) ? $messageData['error']['message'] : '';
            $errorCode = $e->getCode();
            $this->lastError = $message;

            return array(
                'error' => $errorCode,
                'message' => $message
            );
        }

        return false;
    }

    /**
     * Get list changes
     *
     * @param string $pageToken Page token
     *
     * @return boolean|Google_Service_Drive_ChangeList
     * @since  5.2
     */
    public function listChanges($pageToken)
    {
        $service = $this->getGoogleService();

        try {
            $response = $service->changes->listChanges($pageToken, array(
                'fields'   => '*', // todo: change fields to reduce response size
                'pageSize' => 1000
            ));

            return $response;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Stop watch a channel
     *
     * @param string $id         Channel id
     * @param string $resourceId Resource Id
     *
     * @return boolean
     * @since  5.2
     */
    public function stopWatch($id, $resourceId)
    {
        $service = $this->getGoogleService();

        try {
            $channel = new Google_Service_Drive_Channel();
            $channel->setKind('api#channel');
            $channel->setId($id);
            $channel->setResourceId($resourceId);

            $service->channels->stop($channel);
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}
