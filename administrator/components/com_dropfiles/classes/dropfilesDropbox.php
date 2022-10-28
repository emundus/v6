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

/**
 * Class initialization and connection Dropbox
 */
class DropfilesDropbox
{
    /**
     * Params
     *
     * @var array
     */
    protected $params;

    /**
     * App name
     *
     * @var string
     */
    protected $appName = 'codeUnited/1.0';

    /**
     * Last error
     *
     * @var mixed
     */
    protected $lastError;


    /**
     * DropfilesDropbox constructor.
     */
    public function __construct()
    {
        require_once 'Dropbox/autoload.php';
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
     * Load params
     *
     * @return void
     */
    protected function loadParams()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $this->params = new stdClass();

        $this->params->dropboxKey = trim($params->get('dropbox_key'));
        $this->params->dropboxSecret = trim($params->get('dropbox_secret'));
        $this->params->dropboxCode = trim($params->get('dropbox_authorization_code'));
        $this->params->dropboxToken = isset($params['dropbox_token']) ? $params['dropbox_token'] : '';
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
            'dropbox_key' => $this->params->dropboxKey,
            'dropbox_secret' => $this->params->dropboxSecret,
            'dropbox_authorization_code' => $this->params->dropboxCode,
            'dropbox_token' => $this->params->dropboxToken));
    }

    /**
     * Save Token
     *
     * @param string $code  Code
     * @param string $token Token
     *
     * @return void
     */
    public function saveCodeToken($code, $token)
    {
        $this->params->dropboxCode = trim($code);
        $this->params->dropboxToken = $token;
        $this->saveParams();
    }

    /**
     * Get Web Auth
     *
     * @return \Dropbox\WebAuthNoRedirect
     */
    public function getWebAuth()
    {
        $dropboxKey = 'sss';
        $dropboxSecret = 'dropboxSecret';

        if (!empty($this->params->dropboxKey)) {
            $dropboxKey = $this->params->dropboxKey;
        }
        if (!empty($this->params->dropboxSecret)) {
            $dropboxSecret = $this->params->dropboxSecret;
        }

        $appInfo = new Dropbox\AppInfo($dropboxKey, $dropboxSecret);
        $webAuth = new Dropbox\WebAuthNoRedirect($appInfo, $this->appName);

        return $webAuth;
    }

    /**
     * Get author Url allow user
     *
     * @return string
     */
    public function getAuthorizeDropboxUrl()
    {
        $authorizeUrl = $this->getWebAuth()->start();

        return $authorizeUrl;
    }


    /**
     * Convert the authorization code into an access token.
     *
     * @param string $authCode Authorization code
     *
     * @return array
     */
    public function convertAuthorizationCode($authCode)
    {
        list($accessToken, $dropboxUserId) = $this->getWebAuth()->finish($authCode);
        $list = array('accessToken' => $accessToken,
            'dropboxUserId' => $dropboxUserId
        );
        return $list;
    }


    /**
     * Check Author
     *
     * @return boolean
     */
    public function checkAuth()
    {
        $dropboxToken = $this->params->dropboxToken;
        if (!empty($dropboxToken)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {
        $this->params->dropboxCode = '';
        $this->params->dropboxToken = '';
        $this->saveParams();
    }

    /**
     * Get Dropbox Account
     *
     * @return \Dropbox\Client
     */
    public function getAccount()
    {
        $dropboxToken = $this->params->dropboxToken;
        $dbxClient = new Dropbox\Client($dropboxToken, $this->appName);

        return $dbxClient;
    }


    /**
     * Create Folder to dropbox
     *
     * @param string $title Title
     *
     * @return array|boolean|null
     */
    public function createDropFolder($title)
    {
        try {
            $dropbox = $this->getAccount();
            $path = '/' . $title;

            $result = $dropbox->createFolder($path);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $result;
    }


    /**
     * Delete Folder to dropbox
     *
     * @param string $id Dropbox category id
     *
     * @return boolean
     */
    public function deleteDropbox($id)
    {
        try {
            $dropbox = $this->getAccount();
            $dropbox->delete($id);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }

        return true;
    }


    /**
     * Rename Folder Dropbox
     *
     * @param string $id       File id
     * @param string $filename File name
     *
     * @return boolean
     */
    public function changeDropboxFilename($id, $filename)
    {
        try {
            $dropbox = $this->getAccount();
            $dropbox->move($id, $filename);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }

        return true;
    }


    /**
     * Rename Folder Dropbox
     *
     * @param string $from From
     * @param string $to   To
     *
     * @return boolean|mixed
     */
    public function moveFile($from, $to)
    {
        try {
            $dropbox = $this->getAccount();
            return $dropbox->move($from, $to);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Upload file to Folder Dropbox
     *
     * @param string  $filename  File name
     * @param string  $fileTemp  Temp path
     * @param integer $size      File size
     * @param string  $id_folder Folder id
     *
     * @return boolean|mixed
     */
    public function uploadFile($filename, $fileTemp, $size, $id_folder)
    {
        $f = fopen($fileTemp, 'rb');
        $path = $id_folder . '/' . $filename;

        try {
            $dropbox = $this->getAccount();
            $result = $dropbox->uploadFile($path, Dropbox\WriteMode::add(), $f, $size);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();

            return false;
        }

        return $result;
    }

    /**
     * Get All item in folder id of Dropbox
     *
     * @param string $idFolder Folder id
     *
     * @return mixed
     */
    public function getAllFiles($idFolder)
    {
        $dropbox = $this->getAccount();
        $fs = $dropbox->getMetadataWithChildren($idFolder);
        return $fs['entries'];
    }


    /**
     * List dropbox file
     *
     * @param string $folder_id Folder id
     *
     * @return array|boolean
     */
    public function listDropboxFiles($folder_id)
    {
        try {
            $dropbox = $this->getAccount();
            $fs = $dropbox->getMetadataWithChildren($folder_id);
            if (empty($fs)) {
                return false;
            }
            $files = array();
            foreach ($fs['entries'] as $f) {
                if ($f['.tag'] === 'file') {
                    $files[] = $f;
                }
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $files;
    }


    /**
     * Delete file in Dropbox
     *
     * @param string $id_file File id
     *
     * @return boolean
     */
    public function deleteFileDropbox($id_file)
    {
        try {
            $dropbox = $this->getAccount();
            $fs = $dropbox->getMetadata($id_file);
            $dropbox->delete($fs['path_lower']);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Copy item in dropbox
     *
     * @param string $path_from Path from
     * @param string $path_to   Path to
     *
     * @return boolean|mixed|string
     */
    public function copyFileDropbox($path_from, $path_to)
    {
        try {
            $dropbox = $this->getAccount();
            $fs = $dropbox->copy($path_from, $path_to);
            return $fs;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return $e->getMessage();
        }
    }


    /**
     * Get file info in dropbox
     *
     * @param string $idFile File id
     *
     * @return boolean|mixed|null
     */
    public function getDropboxFileInfos($idFile)
    {
        try {
            $dropbox = $this->getAccount();
            $v = $dropbox->getFileMetadata($idFile);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $v;
    }


    /**
     * Change title
     *
     * @param string $id    File id
     * @param string $title New file title
     *
     * @return boolean
     */
    public function changeFileName($id, $title)
    {
        try {
            $dropbox = $this->getAccount();
            $getFile = $dropbox->getMetadata($id);
            $fpath = pathinfo($getFile['path_lower']);
            $newpath = $fpath['dirname'] . '/' . $title . '.' . $fpath['extension'];
            $dropbox->move($getFile['path_lower'], $newpath);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Save version item
     *
     * @param array $datas Data
     *
     * @return boolean|mixed
     */
    public function saveDropboxVersion($datas)
    {
        try {
            $dropbox = $this->getAccount();
            $getFile = $dropbox->getMetadata($datas['old_file']);
            $f = fopen($datas['new_tmp_name'], 'rb');
            $result = $dropbox->updateFile(
                $getFile['path_lower'],
                $getFile['rev'],
                Dropbox\WriteMode::add(),
                $f,
                $datas['new_file_size']
            );
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $result;
    }

    /**
     * Get version info
     *
     * @param string $idFile File id
     *
     * @return array|boolean
     */
    public function displayDropboxVersionInfo($idFile)
    {
        try {
            $dropbox = $this->getAccount();
            $getFile = $dropbox->getMetadata($idFile);
            $result = $dropbox->listRevisions($getFile['path_lower'], 10);
            $versions = array();
            foreach ($result['entries'] as $v) {
                if ($getFile['rev'] !== $v['rev']) {
                    $fpath = pathinfo($v['path_lower']);
                    $version = new stdClass();
                    $version->ext = $fpath['extension'];
                    $version->size = $v['size'];
                    $version->id = $v['id'];
                    $version->created_time = $v['client_modified'];
                    $version->meta_id = $v['rev'];
                    $versions[] = $version;
                }
            }
            return $versions;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Restore version dropbox
     *
     * @param string $id_file File id
     * @param string $vid     Version id
     *
     * @return boolean
     */
    public function restoreVersion($id_file, $vid)
    {
        try {
            $dropbox = $this->getAccount();
            $getFile = $dropbox->getMetadata($id_file);
            $dropbox->restoreFile($getFile['path_lower'], $vid);
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Download item version
     *
     * @param string $id_file File id
     * @param string $vid     Version id
     *
     * @return boolean
     */
    public function downloadVersion($id_file, $vid)
    {
        try {
            $dropbox = $this->getAccount();
            $getFile = $dropbox->getMetadata($id_file);
            $pinfo = pathinfo($getFile['path_lower']);
            $tempfile = $pinfo['basename'];
            $fd = fopen($tempfile, 'wb');
            $dropbox->getFile($getFile['path_lower'], $fd, $vid);

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($tempfile) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($tempfile));
            readfile($tempfile);
            unlink($tempfile);
            exit;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Change Order items in dropbox
     *
     * @param string  $move     Source file
     * @param string  $location Location move to
     * @param integer $parent   Parent id
     *
     * @return boolean|mixed
     */
    public function changeDropboxOrder($move, $location, $parent)
    {
        try {
            if ($parent !== 0) {
                $fpath = pathinfo($move);
                $baseMove = '/' . $fpath['basename'];
                $newlocation = $location . $baseMove;
                $dropbox = $this->getAccount();
                $result = $dropbox->move($move, $newlocation);
            } else {
                $pinfo = pathinfo($move);
                $basemove = '/' . $pinfo['basename'];
                $dropbox = $this->getAccount();
                $result = $dropbox->move($move, $basemove);
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $result;
    }

    /**
     * Get all folder in dropbox
     *
     * @return array|boolean
     */
    public function listAllFolders()
    {
        $dropbox = $this->getAccount();
        $listfolder = array();
        try {
            $folderMetadatas = $dropbox->getMetadataWithChildren('', true);

            if (count($folderMetadatas['entries']) > 0) {
                foreach ($folderMetadatas['entries'] as $f) {
                    if ($f['.tag'] === 'folder') {
                        $listfolder[$f['id']] = $f;
                    }
                }
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }

        return $listfolder;
    }

    /**
     * Get path by id item
     *
     * @param array $diff_add Diff
     *
     * @return array|boolean
     */
    public function getPathById($diff_add)
    {
        $dropbox = $this->getAccount();
        $listPaths = array();
        try {
            foreach ($diff_add as $v) {
                $content = $dropbox->getMetadata($v);
                $listPaths[$content['path_lower']] = array('path' => $content['path_lower'],
                    'id' => $content['id'],
                    'name' => $content['name'],
                );
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
        return $listPaths;
    }

    /**
     * Download item Dropbox
     *
     * @param string $id_file File id
     *
     * @return array
     */
    public function downloadDropbox($id_file)
    {
        $dropbox = $this->getAccount();

        $tempfile = JPATH_COMPONENT_ADMINISTRATOR . '/tmp';
        $fd = fopen($tempfile, 'wb');
        $fMeta = $dropbox->getFile($id_file, $fd);

        return array($tempfile, $fMeta);
    }


    /**
     * Get path file
     *
     * @param string $id File id
     *
     * @return mixed
     */
    public function getPathFile($id)
    {
        $dropbox = $this->getAccount();
        $meta = $dropbox->getMetadata($id);
        $fpath = pathinfo($meta['path_lower']);
        return $fpath['dirname'];
    }
}
