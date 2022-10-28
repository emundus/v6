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
 * Class DropfilesOneDriveBusiness initialization and connection OneDrive Business
 */
class DropfilesOneDriveBusiness
{
    /**
     * Onedrive connection config
     *
     * @var array $config
     */
    public $config;
    /**
     * OneDrive Client
     *
     * @var OneDrive_Client
     */
    private $client = null;

    /**
     * File fields
     *
     * @var string
     */
    protected $apifilefields = 'thumbnails,children(top=1000;expand=thumbnails(select=medium,large,mediumSquare,c1500x1500))';

    /**
     * List files fields
     *
     * @var string
     */
    protected $apilistfilesfields = 'thumbnails(select=medium,large,mediumSquare,c1500x1500)';

    /**
     * BreadCrumb
     *
     * @var string
     */
    public $breadcrumb = '';

    /**
     * AccessToken
     *
     * @var string
     */
    private $accessToken;

    /**
     * Refresh token
     *
     * @var string
     */
    private $refreshToken;

    /**
     * Last error
     *
     * @var $lastError
     */
    protected $lastError;

    /**
     * Cloud type
     *
     * @var string
     */
    protected $type = 'onedrive_business';

    /**
     * Debug
     *
     * @var boolean
     */
    private $debug = false;

    /**
     * DropfilesOneDriveBusiness constructor.
     */
    public function __construct()
    {
        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        $path_dropfilescloud  = JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfilescloud.php';
        $path_admin_component = JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php';
        JLoader::register('DropfilesCloudHelper', $path_dropfilescloud);
        JLoader::register('DropfilesComponentHelper', $path_admin_component);
        require_once 'OneDriveBusiness/packages/autoload.php';
        $this->config = DropfilesCloudHelper::getAllOneDriveBusinessConfigs();
        if ($this->client === null && isset($this->config['state']->token->data->access_token)) {
            $this->getClient();
        }
    }

    /**
     * Check Onedrive Business show connect button
     *
     * @return boolean
     */
    public function hasOneDriveButton()
    {
        if (isset($this->config) && (!empty($this->config))) {
            if (!empty($this->config['onedriveBusinessKey']) &&
                !empty($this->config['onedriveBusinessSecret'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check Onedrive Business connection
     *
     * @return boolean
     */
    public function checkConnectOnedrive()
    {
        if (isset($this->config) && (!empty($this->config))) {
            if (!empty($this->config['onedriveBusinessKey']) &&
                !empty($this->config['onedriveBusinessSecret']) &&
                isset($this->config['connected']) &&
                (int) $this->config['connected'] === 1
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        $params  = JComponentHelper::getParams('com_dropfiles');
        $config  = array(
            'onedriveBusinessKey'         => (isset($params['onedriveBusinessKey']) && (string) $params['onedriveBusinessKey'] !== '') ? $params['onedriveBusinessKey'] : '',
            'onedriveBusinessSecret'      => (isset($params['onedriveBusinessSecret']) && (string) $params['onedriveBusinessSecret'] !== '') ? $params['onedriveBusinessSecret'] : '',
            'onedriveBusinessSyncTime'    => isset($params['onedriveBusinessSyncTime']) ? $params['onedriveBusinessSyncTime'] : '30',
            'onedriveBusinessSyncMethod'  => isset($params['onedriveBusinessSyncMethod']) ? $params['onedriveBusinessSyncMethod'] : 'sync_page_curl',
            'onedriveBusinessConnectedBy' => (int)JFactory::getUser()->id,
            'onedriveBusinessBaseFolder'  => isset($params['onedriveBusinessBaseFolder']) ? $params['onedriveBusinessBaseFolder'] : array(),
            'state'                       => isset($params['onedriveBusinessState']) ? $params['onedriveBusinessState'] : array(),
            'connected'                   => (isset($params['onedriveBusinessConnected']) && (int)$params['onedriveBusinessConnected'] === 1) ? (int) $params['onedriveBusinessConnected'] : 0
        );

        $this->config = $config;

        return $this->config;
    }

    /**
     * Save config
     *
     * @param array $config Config
     *
     * @return void
     */
    public function saveConfig($config)
    {
        if (!class_exists('DropfilesComponentHelper')) {
            $path_admin_component = JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php';
            JLoader::register('DropfilesComponentHelper', $path_admin_component);
        }
        if (isset($config['state'])) {
            $config['onedriveBusinessState'] = $config['state'];
            unset($config['state']);
        }
        if (isset($config['connected'])) {
            $config['onedriveBusinessConnected'] = $config['connected'];
            unset($config['connected']);
        }
        if (isset($config['onedriveBusinessLogout'])) {
            if (isset($config['onedriveBusinessState'])) {
                unset($config['onedriveBusinessState']);
            }
            unset($config['onedriveBusinessLogout']);
        }

        DropfilesComponentHelper::setParams($config);
    }

    /**
     * Get authorisation url onedrive business
     *
     * @return string|boolean
     */
    public function getAuthorisationUrl()
    {
        try {
            // Instantiates a OneDrive client bound to your OneDrive application.
            $client = \Krizalys\Onedrive\Onedrive::client($this->config['onedriveBusinessKey']);

            // Gets a log in URL with sufficient privileges from the OneDrive API.
            $authorizeUrl = $client->getLogInUrl(
                array(
                'files.read',
                'files.read.all',
                'files.readwrite',
                'files.readwrite.all',
                'offline_access',
                ),
                JURI::root() . 'administrator/index.php?option=com_dropfiles&task=onedrivebusiness.authenticated',
                'dropfiles-onedrive-business'
            );
            $config = $this->getConfig();
            $config['state'] = $client->getState();
            $this->saveConfig($config);
            return $authorizeUrl;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Authenticate
     *
     * @return boolean
     *
     * @throws Exception Message if errors
     */
    public function authenticate()
    {
        $code = JFactory::getApplication()->input->get('code');
        return $this->createToken($code);
    }

    /**
     * Renew the access token from OAuth. This token is valid for one hour.
     *
     * @param object $client Client
     * @param array  $config Setings
     *
     * @return Client
     */
    public function renewAccessToken($client, $config)
    {
        $client->renewAccessToken($config['onedriveBusinessSecret']);
        $config['state'] = $client->getState();
        $this->saveConfig($config);
        $graph = new \Microsoft\Graph\Graph();
        $graph->setAccessToken($client->getState()->token->data->access_token);
        try {
            $client = \Krizalys\Onedrive\Onedrive::client(
                $config['onedriveBusinessKey'],
                array(
                    'state' => $client->getState()
                )
            );

            return $client;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Read OneDrive app key and secret
     *
     * @return Krizalys\Onedrive\Client|OneDrive_Client|boolean
     */
    public function getClient()
    {
        if (empty($this->config['onedriveBusinessKey']) && empty($this->config['onedriveBusinessSecret'])) {
            return false;
        }

        try {
            if (isset($this->config['state']) && isset($this->config['state']->token->data->access_token)) {
                $graph = new \Microsoft\Graph\Graph();
                $graph->setAccessToken($this->config['state']->token->data->access_token);
                $client = \Krizalys\Onedrive\Onedrive::client(
                    $this->config['onedriveBusinessKey'],
                    array(
                        'state' => $this->config['state']
                    )
                );

                if ($client->getAccessTokenStatus() === -2) {
                    $client = $this->renewAccessToken($client, $this->config);
                }
            } else {
                $client = \Krizalys\Onedrive\Onedrive::client(
                    $this->config['onedriveBusinessKey']
                );
            }

            $this->client = $client;

            return $this->client;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Create token after connected
     *
     * @param string $code Code to access to OneDrive app
     *
     * @return boolean
     *
     * @throws Exception Message if errors
     */
    public function createToken($code)
    {
        try {
            $onedriveconfig = $this->getConfig();

            $client = \Krizalys\Onedrive\Onedrive::client(
                $onedriveconfig['onedriveBusinessKey'],
                array(
                    'state' => isset($onedriveconfig['state']) && !empty($onedriveconfig['state']) ? $onedriveconfig['state'] : array()
                )
            );

            $siteName = JFactory::getApplication()->get('sitename');
            $blogname = trim(str_replace(array(':', '~', '"', '%', '&', '*', '<', '>', '?', '/', '\\', '{', '|', '}'), '', $siteName));

            // Fix onedrive bug, last folder name can not be a dot
            if (substr($blogname, -1) === '.') {
                $blogname = substr($blogname, strlen($blogname) - 1);
            }

            if ($blogname === '') {
                $siteUrl  = JURI::root() ? JURI::root() : '';
                $blogname = parse_url($siteUrl, PHP_URL_HOST);
                if (!$blogname) {
                    $blogname = '';
                } else {
                    $blogname = trim($blogname);
                }
            }

            // Obtain the token using the code received by the OneDrive API.
            $client->obtainAccessToken($onedriveconfig['onedriveBusinessSecret'], $code);
            $graph = new \Microsoft\Graph\Graph();
            $graph->setAccessToken($client->getState()->token->data->access_token);

            if (empty($onedriveconfig['onedriveBusinessBaseFolder'])) {
                $folderName = 'Dropfiles - ' . $blogname;
                $folderName = preg_replace('@["*:<>?/\\|]@', '', $folderName);
                $folderName = rtrim($folderName);
                try {
                    $root = $client->getRoot()->createFolder($folderName);

                    $onedriveconfig['onedriveBusinessBaseFolder'] = array(
                        'id' => $root->id,
                        'name' => $root->name
                    );
                } catch (ConflictException $e) {
                    $root = $client->getDriveItemByPath('/' . $folderName);
                    $onedriveconfig['onedriveBusinessBaseFolder'] = array(
                        'id' => $root->id,
                        'name' => $root->name
                    );
                }
            } else {
                try {
                    $root = $graph
                        ->createRequest('GET', '/me/drive/items/' . $onedriveconfig['onedriveBusinessBaseFolder']->id)
                        ->setReturnType(\Microsoft\Graph\Model\DriveItem::class) // phpcs:ignore PHPCompatibility.Constants.NewMagicClassConstant.Found -- Use to sets the return type of the response object
                        ->execute();
                    $onedriveconfig['onedriveBusinessBaseFolder'] = array(
                        'id' => $root->getId(),
                        'name' => $root->getName()
                    );
                } catch (Exception $ex) {
                    $folderName = 'Dropfiles - ' . $blogname;
                    $folderName = preg_replace('@["*:<>?/\\|]@', '', $folderName);
                    $folderName = rtrim($folderName);
                    $results = $graph->createRequest('GET', '/me/drive/search(q=\'' . $folderName . '\')')
                        // phpcs:ignore PHPCompatibility.Constants.NewMagicClassConstant.Found -- Use to sets the return type of the response object
                        ->setReturnType(Model\DriveItem::class)
                        ->execute();
                    if (isset($results[0])) {
                        $root = new stdClass;
                        $root->id = $results[0]->getId();
                        $root->name = $results[0]->getName();
                    } else {
                        $root = $client->getRoot()->createFolder($folderName);
                    }

                    $onedriveconfig['onedriveBusinessBaseFolder'] = array(
                        'id' => $root->id,
                        'name' => $root->name
                    );
                }
            }

            $token = $client->getState()->token->data->access_token;
            $this->accessToken = $token;
            $onedriveconfig['connected'] = 1;
            $onedriveconfig['state'] = $client->getState();
            // update config and redirect page
            $this->saveConfig($onedriveconfig);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }

        return true;
    }

    /**
     * Logout
     *
     * @return boolean
     */
    public function logout()
    {
        $config = $this->getConfig();
        $config['connected'] = '0';
        $config['onedriveBusinessLogout'] = '1';
        unset($config['state']);
        $this->saveConfig($config);
        return true;
    }

    /**
     * Check Auth
     *
     * @return boolean
     */
    public function checkAuth()
    {
        try {
            $client = $this->getClient();
            if (!$client) {
                return false;
            }
            if ($client->getAccessTokenStatus() === \Krizalys\Onedrive\Constant\AccessTokenStatus::VALID) {
                return true;
            }
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Create OneDrive Folder
     *
     * @param string $title    New folder name
     * @param string $parentId Parent id
     *
     * @return \Krizalys\Onedrive\Proxy\DriveItemProxy
     */
    public function createFolder($title, $parentId = null)
    {
        $parentId = DropfilesCloudHelper::replaceIdOneDrive($parentId, false);
        try {
            $client = $this->getClient();
            $parentFolder = $client->getDriveItemById($parentId);
            return $parentFolder->createFolder($title, array(
                'conflictBehavior' => \Krizalys\Onedrive\Constant\ConflictBehavior::RENAME
            ));
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Upload file to onedrive business
     *
     * @param string  $filename    File name
     * @param array   $file        File info
     * @param string  $fileContent File path
     * @param string  $id_folder   Upload category id
     * @param boolean $replace     Overwrite file name
     *
     * @return array|boolean|OneDrive_Service_Drive_DriveFile
     */
    public function uploadFile($filename, $file, $fileContent, $id_folder, $replace = false)
    {
        if ($replace) {
            $conflictBehavior = \Krizalys\Onedrive\Constant\ConflictBehavior::REPLACE;
        } else {
            $conflictBehavior = \Krizalys\Onedrive\Constant\ConflictBehavior::RENAME;
        }

        $id_folder = DropfilesCloudHelper::replaceIdOneDrive($id_folder, false);
        $client    = $this->client;
        try {
            $folder = $client->getDriveItemById($id_folder);
        } catch (Exception $e) {
            $file['error'] = print 'Upload failed! : ' . $e->getMessage();

            return $file;
        }

        if (!file_exists($fileContent)) {
            $file['error'] = print 'File not exists! Upload failed!';

            return array(
                'file'   => $file
            );
        }

        $stream = fopen($fileContent, 'rb');
        try {
            $uploadSession                  = $folder->startUpload($filename, $stream, array('conflictBehavior' => $conflictBehavior));

            /* @var $uploadedItem \Krizalys\Onedrive\Proxy\DriveItemProxy */
            $uploadedItem                   = $uploadSession->complete();
            $file['name']                   = DropfilesCloudHelper::stripExt($uploadedItem->name);
            $file['id']                     = DropfilesCloudHelper::replaceIdOneDrive($uploadedItem->id);
            $file['createdDateTime']        = $uploadedItem->createdDateTime->format('Y-m-d H:i:s');
            $file['lastModifiedDateTime']   = $uploadedItem->lastModifiedDateTime->format('Y-m-d H:i:s');
            $file['size']                   = $uploadedItem->size;
            return array(
                'file'   => $file
            );
        } catch (ConflictException $e) { // File name already exists
            $file['error'] = print 'Upload failed! : ' . $e->getMessage();
        } catch (Exception $e) {
            $file['error'] = print 'Upload failed! Unknown exception : ' . $e->getMessage();
        }

        if (isset($file['error'])) {
            return array(
                'file'   => $file
            );
        }

        $file['error'] = print 'Upload failed!';
        return array(
            'file'   => $file
        );
    }

    /**
     * Save file information
     *
     * @param array $datas File info
     *
     * @return boolean
     */
    public function saveOnDriveBusinessFileInfos($datas)
    {
        $id     = DropfilesCloudHelper::replaceIdOneDrive($datas['file_id'], false);
        $client = $this->client;
        try {
            $params = array(
                'name' => $datas['title'] . '.' . $datas['ext']
            );
            $client->updateDriveItem($id, $params);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Delete file in onedrive business
     *
     * @param string      $id       File id
     * @param null|string $cloud_id Cloud category id
     *
     * @return boolean
     */
    public function delete($id, $cloud_id = null)
    {
        $id = DropfilesCloudHelper::replaceIdOneDrive($id, false);
        if ($cloud_id !== null) {
            $cloud_id = DropfilesCloudHelper::replaceIdOneDrive($cloud_id, false);
        }

        try {
            $client = $this->client;
            $file = $client->getDriveItemById($id);

            if ($cloud_id !== null) {
                $found  = false;
                if ($file->parentReference->id === $cloud_id) {
                    $found = true;
                }
                if (!$found) {
                    return false;
                }
            }
            $file->delete();
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
     * @param string $filename New file name
     *
     * @return boolean
     */
    public function changeFilename($id, $filename)
    {
        $id = DropfilesCloudHelper::replaceIdOneDrive($id, false);
        try {
            $client = $this->client;
            $file    = $client->getDriveItemById($id);
            $file->rename($filename);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Move a file.
     *
     * @param string $fileId      File id
     * @param string $newParentId Target folder id
     *
     * @return boolean
     */
    public function moveFile($fileId, $newParentId)
    {
        $newParentId = DropfilesCloudHelper::replaceIdOneDrive($newParentId, false);
        $fileIds     = explode(',', $fileId);
        $client      = $this->client;

        /* Set new parent for item */
        try {
            $parentItem  = $client->getDriveItemById($newParentId);
            foreach ($fileIds as $id) {
                $id   = DropfilesCloudHelper::replaceIdOneDrive($id, false);
                $file = $client->getDriveItemById($id);
                $file->move($parentItem);
            }

            return true;
        } catch (Exception $ex) {
            return print 'Failed to move entry: ' . $ex->getMessage();
        }
    }

    /**
     * Move single file and return file infos.
     *
     * @param string $fileId      File id
     * @param string $newParentId Target folder id
     *
     * @return array|boolean
     */
    public function moveFileWithInfo($fileId, $newParentId)
    {
        $newParentId = DropfilesCloudHelper::replaceIdOneDrive($newParentId, false);
        $id          = DropfilesCloudHelper::replaceIdOneDrive($fileId, false);
        $client      = $this->client;
        $fileInfo    = array();

        /* Set new parent for item */
        try {
            $parentItem  = $client->getDriveItemById($newParentId);
            $file        = $client->getDriveItemById($id);
            $newFile     = $file->move($parentItem);

            if ($newFile) {
                $fileInfo['id']             = $newFile->id;
                $fileInfo['title']          = $newFile->name;
                $fileInfo['size']           = $newFile->size;
                $fileInfo['created_time']   = $newFile->createdDateTime;
                $fileInfo['modified_time']  = $newFile->lastModifiedDateTime;
            }

            return $fileInfo;
        } catch (Exception $ex) {
            return print 'Failed to move entry: ' . $ex->getMessage();
        }
    }


    /**
     * Copy a file
     *
     * @param string $fileId      File id
     * @param string $newParentId Target category
     *
     * @return array
     */
    public function copyFile($fileId, $newParentId)
    {
        $newParentId = DropfilesCloudHelper::replaceIdOneDrive($newParentId, false);
        $fileId      = DropfilesCloudHelper::replaceIdOneDrive($fileId, false);
        $client = $this->client;
        try {
            $driveItem = $client->getDriveItemById($fileId);
            $copyTo = $client->getDriveItemById($newParentId);
            $location = $driveItem->copy($copyTo);

            if (!empty($location)) {
                sleep(1);
                $response = file_get_contents($location);
                $response = (array) json_decode($response, true);

                if ($response['status'] === 'completed') {
                    return array('id' => DropfilesCloudHelper::replaceIdOneDrive($response['resourceId']));
                } else {
                    $maxTry = 20;
                    $i = 0;
                    while ($response['status'] !== 'completed') {
                        switch ($response['status']) {
                            case 'completed':
                                return array('id' => DropfilesCloudHelper::replaceIdOneDrive($response['resourceId']));
                            case 'failed':
                                return array();
                            default:
                                sleep(1);
                                $response = file_get_contents($location);
                                $response = (array) json_decode($response, true);
                                break;
                        }

                        $i++;
                        if ($i === $maxTry) {
                            break;
                        }
                    }
                }

                return array();
            }
        } catch (Exception $e) {
            print 'An error occurred: ' . $e->getMessage();
            return array();
        }
    }

    /**
     * Download onedrive business file
     *
     * @param string $fileId File id
     *
     * @return boolean|stdClass
     */
    public function downloadFile($fileId)
    {
        $fileId = DropfilesCloudHelper::replaceIdOneDrive($fileId, false);
        $client = $this->client;
        try {
            /* @var \Krizalys\Onedrive\Proxy\DriveItemProxy $item */
            $item       = $client->getDriveItemById($fileId);
            $ret        = new stdClass();
            $ret->id    = $item->id;

//            $ret->datas = $this->createSharedLink($fileId);
            /* @var GuzzleHttp\Psr7\Stream $httpRequest */
            $httpRequest    = $item->download();
            $ret->datas     = $httpRequest->getContents();
            $ret->title     = DropfilesCloudHelper::stripExt($item->name);
            $ret->ext       = DropfilesCloudHelper::getExt($item->name);
            $ret->size      = $item->size;

            return $ret;
        } catch (Exception $ex) {
            return print 'Failed to add folder';
        }
    }

    /**
     * Get onedrive business file info
     *
     * @param string  $idFile     File id
     * @param integer $idCategory Category id
     *
     * @return array|boolean
     */
    public function getOneDriveBusinessFileInfos($idFile, $idCategory)
    {
        $idFile  = DropfilesCloudHelper::replaceIdOneDrive($idFile, false);
        $client  = $this->client;

        try {
            $file                  = $client->getDriveItemById($idFile);

            $data                  = array();
            $data['ID']            = DropfilesCloudHelper::replaceIdOneDrive($file->id);
            $data['id']            = $data['ID'];
            $data['catid']         = $idCategory;
            $data['title']         = DropfilesCloudHelper::stripExt($file->name);
            $data['post_title']    = $data['title'];
            $data['file']          = '';
            $data['ext']           = DropfilesCloudHelper::getExt($file->name);
            $data['created_time']  = date('Y-m-d H:i:s', strtotime($file->createdDateTime->format('Y-m-d H:i:s')));
            $data['created']       = $data['created_time'];
            $modified_time         = date('Y-m-d H:i:s', strtotime($file->lastModifiedDateTime->format('Y-m-d H:i:s')));
            $data['modified_time'] = $modified_time;
            $data['modified']      = $modified_time;
            $data['file_tags']     = '';
            $data['size']          = $file->size;
            $data['ordering']      = 1;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }

        return $data;
    }

    /**
     * Get List folder on OneDrive Business
     *
     * @param string $folderId Folder id
     *
     * @return array|boolean
     */
    public function getListFolder($folderId)
    {
        $datas = array();
        try {
            $this->getFilesInFolder($folderId, $datas);
        } catch (Exception $ex) {
            return false;
        }

        return $datas;
    }

    /**
     * Get files in folder
     *
     * @param string  $folderId Folder id
     * @param array   $datas    Data return
     * @param boolean $recusive Get all folders
     *
     * @return void
     * @throws Exception Error
     *
     * todo: This only return 200 results
     */
    public function getFilesInFolder($folderId, &$datas, $recusive = true)
    {
        $folderId    = DropfilesCloudHelper::replaceIdOneDrive($folderId, false);
        $client      = $this->client;
        try {
            $folder      = $client->getDriveItemById($folderId);
        } catch (Exception $ex) {
            $this->lastError = 'Get drive item false';
        }

        $params      = $this->getConfig();
        $base_folder = (array) $params['onedriveBusinessBaseFolder'];
        $pageToken   = null;
        if ($datas === false) {
            throw new Exception('getFilesInFolder - datas error ');
        }

        if (!is_array($datas)) {
            $datas = array();
        }
        do {
            try {
                $childs = $folder->getChildren(
                    array(
                        'top' => 500
                    )
                );
                foreach ($childs as $item) {
                    /* @var \Krizalys\Onedrive\Proxy\DriveItemProxy $item */

                    if ($item->folder) {
                        $parentReference = $item->parentReference;
                        $idItem          = DropfilesCloudHelper::replaceIdOneDrive($item->id);
                        $nameItem        = $item->name;
                        if ($idItem !== $base_folder['id']) {
                            $base_folder_id = DropfilesCloudHelper::replaceIdOneDrive($base_folder['id'], false);
                            if ((string) $parentReference->id === (string) $base_folder_id) {
                                $datas[$idItem] = array('title' => $nameItem, 'parent_id' => 0);
                            } else {
                                $datas[$idItem] = array(
                                    'title'     => $nameItem,
                                    'parent_id' => DropfilesCloudHelper::replaceIdOneDrive($parentReference->id)
                                );
                            }
                            if ($recusive) {
                                $this->getFilesInFolder($idItem, $datas);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $datas     = false;
                $pageToken = null;
                throw new Exception('getFilesInFolder - Onedrive Business error ' . $e->getMessage());
            }
        } while ($pageToken);
    }

    /**
     * List files onedrive business
     *
     * @param string $folder_id       Category id
     * @param string $dropfiles_catid Category id in dropfiles
     * @param string $ordering        Ordering
     * @param string $direction       Ordering direction
     * @param array  $listIdFlies     List id files
     *
     * @return array|boolean
     */
    public function listFiles($folder_id, $dropfiles_catid, $ordering = 'ordering', $direction = 'asc', $listIdFlies = array())
    {
        $folder_idck    = DropfilesCloudHelper::replaceIdOneDrive($folder_id, false);
        $client         = $this->getClient();
        try {
            $folder = $client->getDriveItemById($folder_idck);
            $items  = $folder->getChildren(
                array(
                    'top' => 500
                )
            );

            $files = array();

            foreach ($items as $f) {
                if ($f->folder) {
                    continue;
                }
                $idItem     = DropfilesCloudHelper::replaceIdOneDrive($f->id);
                $parentId   = $f->parentReference->id;
                if ($listIdFlies && !in_array($idItem, $listIdFlies)) {
                    continue;
                }
                if ($folder_idck === $parentId) {
                    $file                   = new stdClass();
                    $file->id               = $idItem;
                    $file->ID               = $idItem;
                    $file->title            = DropfilesCloudHelper::stripExt($f->name);
                    $file->post_title       = $file->title;
                    $file->description      = $f->description ? $f->description : '';
                    $file->ext              = DropfilesCloudHelper::getExt($f->name);
                    $file->size             = $f->size;
                    $file->created          = date('Y-m-d H:i:s', strtotime($f->createdDateTime->format('Y-m-d H:i:s')));
                    $file->created_time     = $file->created;
                    $modified_time          = date('Y-m-d H:i:s', strtotime($f->lastModifiedDateTime->format('Y-m-d H:i:s')));
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
}
