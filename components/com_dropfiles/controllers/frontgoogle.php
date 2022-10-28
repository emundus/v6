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

/**
 * Class DropfilesControllerFrontgoogle
 */
class DropfilesControllerFrontgoogle extends JControllerLegacy
{

    /**
     * Google sync files
     *
     * @return void
     * @since  version
     */
    public function index()
    {
        $model = $this->getModel();
        $googleCats = $model->getAllGoogleCategories();
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
        $google = new DropfilesGoogle();
        if ($google->checkAuth()) {
            $files_del = array();
            $gFilesInDb = $model->getAllGoogleFilesInDb();
            foreach ($googleCats as $googlecat) {
                $files = $google->listFilesInFolder($googlecat->cloud_id);
                if (isset($gFilesInDb[$googlecat->cloud_id])) {
                    $files_diff_add = array_diff_key($files, $gFilesInDb[$googlecat->cloud_id]);
                    $files_diff_del = array_diff_key($gFilesInDb[$googlecat->cloud_id], $files);
                    $files_update = array_intersect_key($files, $gFilesInDb[$googlecat->cloud_id]);
                } else {
                    $files_diff_add = $files;
                    $files_diff_del = array();
                    $files_update = array();
                }
                if (!empty($files_update)) {
                    foreach ($files_update as $file_id => $file) {
                        $localFileTime = strtotime($gFilesInDb[$googlecat->cloud_id][$file_id]->modified_time);
                        $need_update = false;
                        if ($localFileTime) {
                            if ($localFileTime < strtotime($file->modified_time)) {
                                $need_update = true;
                            }
                        } else {
                            $need_update = true;
                        }

                        if ($need_update) {
                            $data            = array();
                            $data['id']      = $gFilesInDb[$googlecat->cloud_id][$file_id]->id;
                            $data['file_id'] = $file->id;
                            $data['ext']     = $file->ext;
                            $data['size']    = $file->size;
                            $data['title']   = $file->title;
                            // $data['description'] = $file->description;
                            $data['catid']         = $googlecat->cloud_id;
                            $data['modified_time'] = $file->modified_time;
                            $model->save($data);
                        }
                    }
                }
                if (!empty($files_diff_add)) {
                    //file exist in Google Drive without have in Dropfiles
                    foreach ($files_diff_add as $file_id => $file) {
                        $data                  = array();
                        $data['id']            = 0;
                        $data['file_id']       = $file->id;
                        $data['ext']           = $file->ext;
                        $data['size']          = $file->size;
                        $data['title']         = $file->title;
                        $data['description']   = $file->description;
                        $data['catid']         = $googlecat->cloud_id;
                        $data['created_time']  = $file->created_time;
                        $data['modified_time'] = $file->modified_time;
                        $data['file_tags']     = $file->file_tags;
                        $data['version']       = $file->version;
                        $data['hits']          = $file->hits;
                        $model->save($data);
                    }
                }
                if (!empty($files_diff_del)) {
                    $files_del = array_merge($files_del, array_keys($files_diff_del));
                }
            }
            if (!empty($files_del)) {
                $model->deleteFiles($files_del);
            }
        }
        die();
    }

    /**
     * Sync google files in each category
     *
     * @param boolean $isCron Is this running from a cronjob
     * @param integer $step   Step to run
     *
     * @return boolean|mixed
     * @since  version
     */
    public function syncFolders($isCron = false, $step = 0)
    {
        $app = JFactory::getApplication();
        $model = $this->getModel();
        if (!$isCron) {
            $step = $app->input->getInt('step');
        }
        $googleCats = $model->getAllGoogleCategories();
      //  var_dump($googleCats); die();
        $path_drf_google = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_drf_google);
        $google = new DropfilesGoogle();
        if ($google->checkAuth()) {
            if (!isset($googleCats[$step])) {
                if (!$isCron) {
                    echo json_encode(array('continue' => false));
                } else {
                    return false;
                }

                $app->close();
            }

            $googleCat = $googleCats[$step];
            //folders in Dropfiles
            $folderCloudInDropfiles = $this->childrenCloudInDropfiles($googleCat->id);
            $this->syncOneCategory($googleCat, $folderCloudInDropfiles);
        }

        $step++;
        if (isset($googleCats[$step])) {
            if (!$isCron) {
                echo json_encode(array('continue' => true));
            } else {
                return true;
            }
        } else {
            if (!$isCron) {
                echo json_encode(array('continue' => false));
            } else {
                return false;
            }
        }

        $app->close();
        die();
    }

    /**
     * Sync one Google Drive category
     *
     * @param object $cloudCategory          Cloud category to sync
     * @param array  $folderCloudInDropfiles Cloud folders in Dropfiles
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function syncOneCategory($cloudCategory, $folderCloudInDropfiles)
    {
        $params = JComponentHelper::getParams('com_dropfiles');

        if (!class_exists('DropfilesComponentHelper')) {
            JLoader::register(
                'DropfilesComponentHelper',
                JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php'
            );
        }
        $clsDropfilesHelper = new DropfilesComponentHelper();
        if ($params->get('google_credentials') !== null) {
            //$thisModel = $this->getModel('categories');
            $thisModel = $this->getModel('Categories', 'DropfilesModel');
            //folders in Google Drive
            $folderCloudInGoogleDrive = $this->getChildrenOfCloudFolder($cloudCategory->cloud_id);

            $folders_diff = array();
            $folders_diff_del = array();
            if ($folderCloudInGoogleDrive !== false) {  //to ensure there isn't error when connect with GD
                if (count($folderCloudInDropfiles) > 0) {
                    $folders_diff = array_diff_key($folderCloudInGoogleDrive, $folderCloudInDropfiles);
                    $folders_diff_del = array_diff_key($folderCloudInDropfiles, $folderCloudInGoogleDrive);

                    foreach ($folderCloudInDropfiles as $k => $v) {
                        $objectCurrent = $thisModel->getOneCatByCloudId($k);
                        // case: rename folder on Google Drive
                        if ((!empty($folderCloudInGoogleDrive) && isset($folderCloudInGoogleDrive[$k])) &&
                            $folderCloudInDropfiles[$k]['title'] !== $folderCloudInGoogleDrive[$k]['title']) {
                            try {
                                $thisModel->updateTitleById($objectCurrent->id, $folderCloudInGoogleDrive[$k]['title']);
                            } catch (Exception $e) {
                                $erros = 'updateTitleById-Exception: ' . $e->getMessage();
                                JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                            }
                        }
                    }
                } else {
                    $folders_diff = $folderCloudInGoogleDrive;
                }
            }

            if (count($folders_diff_del) > 0) {
                foreach ($folders_diff_del as $CloudIdDel => $folderDataDel) {
                    $catInfoLocal = $thisModel->getOneCatByCloudId($CloudIdDel);
                    $thisModel->deleteOnDropfiles($CloudIdDel);
                    $thisModel->deleteOnCategories($catInfoLocal->id);
                }
            }

            //if exists diff key array
            if (count($folders_diff) > 0) {
                foreach ($folders_diff as $CloudId => $folderData) {
                    try {
                        //create new cloud folder in dropfiles
                        $newCatId = $thisModel->createOnCategories(
                            $folderData['title'],
                            $cloudCategory->id,
                            (int)$cloudCategory->level + 1
                        );
                        if ($newCatId) {
                            $thisModel->createOnDropfiles(
                                $newCatId,
                                'googledrive',
                                $CloudId
                            );
                        }
                    } catch (Exception $e) {
                        $clsDropfilesHelper->setParams(array('dropfiles_google_last_sync_time' => time()));
                        //var_dump($e->getMessage()); die();
                        break;
                    }
                }
            }

            // Update files count
            $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
            $categoriesModel->updateFilesCount();
        }
    }

    /**
     * List folder cloud in Google drive
     *
     * @param string $cloud_id Cloud ID
     *
     * @return array|boolean
     * @since  version
     */
    public function getChildrenOfCloudFolder($cloud_id)
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $dropfilesGoogle = new DropfilesGoogle();
        try {
            $lstFolder = $dropfilesGoogle->getSubFolders($cloud_id);
        } catch (Exception $e) {
            if ($params->get('sync_log_option') === 1) {
                $erros = $e->getMessage() . $e->getTraceAsString() . PHP_EOL;
                JLog::add($erros, JLog::ERROR, 'com_dropfiles');
            }
            $lstFolder = false;
        }
        return $lstFolder;
    }

    /**
     * Get category children (1 level)
     *
     * @param integer $cid Category ID
     *
     * @return mixed
     * @since  version
     */
    public function childrenCloudInDropfiles($cid)
    {
        $model = $this->getModel();
        $children = $model->getChildrenGoogleCategories($cid, false);
        $results = array();
        if (!empty($children)) {
            // restructure data
            foreach ($children as $child) {
                if (isset($child->cloud_id)) {
                    $results[$child->cloud_id] = array('id' => $child->id, 'title' => $child->title, 'cloud_id' => $child->cloud_id);
                }
            }
        }

        return $results;
    }

    /**
     * Sync google files in each category
     *
     * @param boolean $isCron Is this running from a cronjob
     * @param integer $step   Step to run
     *
     * @return boolean|mixed
     * @since  version
     */
    public function syncFiles($isCron = false, $step = 0)
    {
        $app = JFactory::getApplication();
        $model = $this->getModel();
        $catid = 0;
        if (!$isCron) {
            $step = $app->input->getInt('step');
            $catid = $app->input->getInt('catid');
        }
        if ($catid) {
            $googleCats = $model->getChildrenGoogleCategories($catid);
        } else { // get all google categories
            $googleCats = $model->getAllGoogleCategories();
        }

        $path_drf_google = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_drf_google);
        $google = new DropfilesGoogle();
        if ($google->checkAuth()) {
            $files_del = array();
            $gFilesInDb = $model->getAllGoogleFilesInDb();

            if (!isset($googleCats[$step])) {
                if (!$isCron) {
                    echo json_encode(array('continue' => false));
                } else {
                    return false;
                }

                $app->close();
            }
            $googlecat = $googleCats[$step];
            $files = $google->listFilesInFolder($googlecat->cloud_id);

            if (isset($gFilesInDb[$googlecat->cloud_id])) {
                $files_diff_add = array_diff_key($files, $gFilesInDb[$googlecat->cloud_id]);
                $files_diff_del = array_diff_key($gFilesInDb[$googlecat->cloud_id], $files);
                $files_update = array_intersect_key($files, $gFilesInDb[$googlecat->cloud_id]);
            } else {
                $files_diff_add = $files;
                $files_diff_del = array();
                $files_update = array();
            }


            if (!empty($files_update)) {
                foreach ($files_update as $file_id => $file) {
                    $localFileTime = strtotime($gFilesInDb[$googlecat->cloud_id][$file_id]->modified_time);
                    $need_update = false;
                    if ($localFileTime) {
                        if ($localFileTime < strtotime($file->modified_time)) {
                            $need_update = true;
                        }
                    } else {
                        $need_update = true;
                    }

                    if ($need_update) {
                        $data = array();
                        $data['id'] = $gFilesInDb[$googlecat->cloud_id][$file_id]->id;
                        $data['file_id'] = $file->id;
                        $data['ext'] = $file->ext;
                        $data['size'] = $file->size;
                        $data['title'] = $file->title;
                        // $data['description'] = $file->description;
                        $data['catid'] = $googlecat->cloud_id;
                        $data['modified_time'] = $file->modified_time;

                        $model->save($data);
                    }
                }
            }

            if (!empty($files_diff_add)) {
                //file exist in Google Drive without have in Dropfiles
                foreach ($files_diff_add as $file_id => $file) {
                    $data                  = array();
                    $data['id']            = 0;
                    $data['file_id']       = $file->id;
                    $data['ext']           = $file->ext;
                    $data['size']          = $file->size;
                    $data['title']         = $file->title;
                    $data['description']   = $file->description;
                    $data['catid']         = $googlecat->cloud_id;
                    $data['created_time']  = $file->created_time;
                    $data['modified_time'] = $file->modified_time;
                    $data['file_tags']     = isset($file->file_tags) ? $file->file_tags : '';
                    $data['version']       = isset($file->version) ? $file->version : '';
                    $data['hits']          = isset($file->hits) ? $file->hits : 0;
                    $model->save($data);
                }
            }

            if (!empty($files_diff_del)) {
                $files_del = array_merge($files_del, array_keys($files_diff_del));
            }

            if (!empty($files_del)) {
                $model->deleteFiles($files_del);
            }
        }

        $step++;
        if (isset($googleCats[$step])) {
            if (!$isCron) {
                echo json_encode(array('continue' => true));
            } else {
                return true;
            }
        } else {
            if (!$isCron) {
                echo json_encode(array('continue' => false));
            } else {
                return false;
            }
        }
        // Update files count
        $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
        $categoriesModel->updateFilesCount();
        $app->close();
        die();
    }

    /**
     * Sync files by cloud id
     *
     * @param string $cloudId Cloud Id
     *
     * @return boolean
     * @throws Exception Throw exception when application can not start
     * @since  5.2.0
     */
    public function syncFileByCloudId($cloudId = '')
    {
        if ($cloudId === '') {
             return false;
        }
        $app = JFactory::getApplication();
        $model = $this->getModel();

        if (!class_exists('DropfilesGoogle')) {
            $googleClass = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
            JLoader::register('DropfilesGoogle', $googleClass);
        }

        $google = new DropfilesGoogle();
        $googleCat = $model->getGoogleCategory($cloudId);

        if (is_null($googleCat)) {
            return false;
        }

        $filesDel = array();
        $googleFilesInDb = $model->getFilesListByCloudId($cloudId);
        $googleFilesInCloud = $google->listFilesInFolder($cloudId);

        if (isset($googleFilesInDb)) {
            $files_diff_add = array_diff_key($googleFilesInCloud, $googleFilesInDb);
            $files_diff_del = array_diff_key($googleFilesInDb, $googleFilesInCloud);
            $files_update = array_intersect_key($googleFilesInCloud, $googleFilesInDb);
        } else {
            $files_diff_add = $googleFilesInCloud;
            $files_diff_del = array();
            $files_update = array();
        }

        if (!empty($files_update)) {
            foreach ($files_update as $file_id => $file) {
                $localFileTime = strtotime($googleFilesInDb[$file_id]->modified_time);
                $need_update = false;
                if ($localFileTime) {
                    if ($localFileTime < strtotime($file->modified_time)) {
                        $need_update = true;
                    }
                } else {
                    $need_update = true;
                }

                if ($need_update) {
                    $data = array();
                    $data['id'] = $googleFilesInDb[$file_id]->id;
                    $data['file_id'] = $file->id;
                    $data['ext'] = $file->ext;
                    $data['size'] = $file->size;
                    $data['title'] = $file->title;
                    // $data['description'] = $file->description;
                    $data['catid'] = $cloudId;
                    $data['modified_time'] = $file->modified_time;

                    $model->save($data);
                }
            }
        }

        if (!empty($files_diff_add)) {
            //file exist in Google Drive without have in Dropfiles
            foreach ($files_diff_add as $file_id => $file) {
                $data                  = array();
                $data['id']            = 0;
                $data['file_id']       = $file->id;
                $data['ext']           = $file->ext;
                $data['size']          = $file->size;
                $data['title']         = $file->title;
                $data['description']   = $file->description;
                $data['catid']         = $cloudId;
                $data['created_time']  = $file->created_time;
                $data['modified_time'] = $file->modified_time;
                $data['file_tags']     = isset($file->file_tags) ? $file->file_tags : '';
                $data['version']       = isset($file->version) ? $file->version : '';
                $data['hits']          = isset($file->hits) ? $file->hits : 0;
                $model->save($data);
            }
        }

        if (!empty($files_diff_del)) {
            $files_del = array_merge($filesDel, array_keys($files_diff_del));
        }


        if (!empty($files_del)) {
            $model->deleteFiles($files_del);
        }
        // Update files count
        $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
        $categoriesModel->updateFilesCount();
    }
    /**
     * Method get model
     *
     * @param string $name   Model name
     * @param string $prefix Model prefix
     * @param array  $config Model config
     *
     * @return mixed
     * @since  version
     */
    public function getModel(
        $name = 'frontgoogle',
        $prefix = 'dropfilesModel',
        $config = array('ignore_request' => true)
    ) {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Watch changes from Google Drive
     *
     * @throws Exception Throws when application can not start
     * @return void
     * @since  5.2
     */
    public function listener()
    {
        $app = JFactory::getApplication();
        $data = $this->extractHeader($_SERVER);

        $status = 406;
        if (isset($data['HTTP_X_GOOG_RESOURCE_STATE']) && isset($data['HTTP_X_GOOG_CHANNEL_ID'])) {
            switch ($data['HTTP_X_GOOG_RESOURCE_STATE']) {
                case 'sync':
                    // Do something cool in sync step or do nothing
                    // Got this state when new watch change was made
                    // Check other resource id and remove them
                    $flag = 'synced';
                    break;
                case 'change':
                    // Oh we have a changes
                    $params = JComponentHelper::getParams('com_dropfiles');
                    $watchData = $params->get('dropfiles_google_watch_data', '');
                    if ($watchData === '') {
                        break;
                    }
                    $watchData = json_decode($watchData, true);
                    if (is_array($watchData) && isset($watchData['error'])) {
                        break;
                    }
                    if (!is_array($watchData) ||
                        !isset($watchData['id']) ||
                        $data['HTTP_X_GOOG_CHANNEL_ID'] !== $watchData['id']
                    ) {
                        break;
                    }
                    $lastSyncChanges = (int) $params->get('dropfiles_google_last_sync_changes', 0);
                    $timeout = 5 * 60; // 5 minutes
                    $isTimeout = (time() - $lastSyncChanges) > $timeout;
                    $onSyncChange = (int) $params->get('dropfiles_google_on_sync', 0);

                    // Check other changes progress is running or timeout
                    if ($onSyncChange === 0 || ($onSyncChange === 1 && ($lastSyncChanges === 0 || $isTimeout))) {
                        $this->onChangesReceive();
                        $status = 202;
                    } else {
                        // Send header Drive API will retry with exponential backoff
                        // Document here: https://developers.google.com/drive/api/v3/push#responding-to-notifications
                        $status = 503;
                    }
                    break;
                default:
                    break;
            }
        }

        $app->setHeader('X-PHP-Response-Code', $status, true);
        $app->setHeader('Status', $status, true);
        $app->sendHeaders();
        $app->close();
    }

    /**
     * On change receive
     *
     * @return boolean
     * @since  4.1.5
     */
    private function onChangesReceive()
    {
        $path_drf_google = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_drf_google);
        JLoader::register('DropfilesCloudHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfilescloud.php');

        // Check with previous sync time and is there any sync step is running?
        if (DropfilesCloudHelper::isGoogleWatchExpiry()) {
            // Renew watch changes
            DropfilesCloudHelper::watchChanges();
        }
        JLoader::register(
            'DropfilesComponentHelper',
            JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php'
        );
        $clsDropfilesHelper = new DropfilesComponentHelper();
        // Then get changes list and do the sync progress
        $params = JComponentHelper::getParams('com_dropfiles');
        $lastPageToken = $params->get('dropfiles_google_last_changes_token', '');

        if ($lastPageToken === '') {
            return false;
        }

        $changes = array();
        $newPageToken = '';
        $this->getChanges($lastPageToken, $changes, $newPageToken);

        if (empty($changes)) {
            return false;
        }
        $clsDropfilesHelper->setParams(array('dropfiles_google_on_sync' => 1));
        $this->doSyncByChanges($changes, $newPageToken);
        // Update last sync time and save newPageToken
        $clsDropfilesHelper->setParams(array('dropfiles_google_last_sync_changes' => time(), 'dropfiles_google_on_sync' => 0));
        // When we check watch expiry time to renew?
    }

    /**
     * Do sync by changes
     *
     * @param array  $changes      Changes
     * @param string $newPageToken New page token for next sync
     *
     * @return boolean
     * @throws Exception Throws when application can not start
     * @since  5.2
     */
    private function doSyncByChanges($changes, $newPageToken)
    {
        if (empty($changes)) {
            return false;
        }
        $googleFileModel = $this->getModel('googlefiles');
        if (!$googleFileModel instanceof DropfilesModelGooglefiles) {
            return false;
        }
        $categoriesModel = $this->getModel('categories');
        if (!$categoriesModel instanceof DropfilesModelCategories) {
            return false;
        }

        $categoryModel = $this->getModel('category');
        if (!$categoryModel instanceof DropfilesModelCategory) {
            return false;
        }
        $params       = JComponentHelper::getParams('com_dropfiles');
        $baseFolderId = $params->get('google_base_folder');

        foreach ($changes as $change) {
            // Progress sync by each change
            if ($change->getChangeType() === 'file') {
                $file = $change->getFile();
                if (!$file instanceof Google_Service_Drive_DriveFile) {
                    continue;
                }

                // Check file parents. If this is shared documents from other user it does not provided
                $parents = $file->getParents();
                if ($parents === null || (is_array($parents) && empty($parents))) {
                    continue;
                }

                $parent = $parents[0];
                $action = $this->getChangeAction($file, $parent);

                if (!$action) {
                    continue;
                }

                switch ($action) {
                    case 'file_created':
                        try {
                            $googleFileModel->createFile($file, $parent);
                        } catch (Exception $e) {
                            break;
                        }
                        break;
                    case 'file_moved':
                        try {
                            $googleFileModel->moveFile($file, $parent);
                        } catch (Exception $e) {
                            break;
                        }
                        break;
                    case 'file_modified':
                        try {
                            $googleFileModel->updateFile($file, $parent);
                        } catch (Exception $e) {
                            break;
                        }
                        break;
                    case 'file_removed':
                        try {
                            $googleFileModel->deleteFile($file->getId());
                        } catch (Exception $e) {
                            break;
                        }
                        break;
                    case 'folder_created':
                        try {
                            $localCat = $categoriesModel->getOneCatByCloudId($file->getId());
                            if (empty($localCat)) {
                                // Single folder created
                                $parentCat = $categoriesModel->getOneCatByCloudId($parent);
                                $newCatId = $categoriesModel->createOnCategories($file->getName(), $parentCat->id, $parentCat->level + 1);
                                $categoriesModel->createOnDropfiles($newCatId, 'googledrive', $file->getId());
                                // Sync files in this folder
                                $this->syncFileByCloudId($file->getId());
                                // When drag and drop a category tree
                                $this->syncGoogleToLocalFolder($file->getId());
                            } else { // Sync files only
                                // Sync files in this folder
                                $this->syncFileByCloudId($file->getId());
                            }
                        } catch (Exception $e) {
                            break;
                        }
                        break;
                    case 'folder_moved':
                        try {
                            if ($baseFolderId === $parent) {
                                $parentCatId = 0;
                            } else {
                                $parentCat  = $categoriesModel->getOneCatByCloudId($parent);
                                $parentCatId = (int) $parentCat->id;
                            }

                            $currentCat = $categoriesModel->getOneCatByCloudId($file->getId());

                            $pk       = $currentCat->id; // Catid
                            $ref      = $parentCatId; // Parent
                            $position = 'first-child';

                            $table    = $categoryModel->getTable();
                            $table->moveByReference($ref, $position, $pk);
                        } catch (Exception $e) {
                            break;
                        }
                        break;
                    case 'folder_modified': // Folder rename
                        try {
                            $newName = $file->getName();
                            $currentCat = $categoriesModel->getOneCatByCloudId($file->getId());
                            if ($currentCat && $currentCat->title !== $newName) {
                                $categoryModel->setTitle($currentCat->id, $newName);
                            }
                        } catch (Exception $e) {
                            break;
                        }
                        break;
                    case 'folder_removed':
                        try {
                            // Remove in categories
                            $modelCats = $this->getModel('categories');
                            $localFolder = $modelCats->getOneCatByCloudId($file->getId());

                            $cid = (int) $localFolder->id;

                            $modelCats->deleteCategoriesRecursive($cid, 'googledrive');
                        } catch (Exception $e) {
                            break;
                        }
                        break;
                    default:
                        break;
                }
                // Update files count
                $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
                $categoriesModel->updateFilesCount();
            }
        }
        // Update latest page token

        JLoader::register(
            'DropfilesComponentHelper',
            JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php'
        );

        $clsDropfilesHelper = new DropfilesComponentHelper();
        $clsDropfilesHelper->setParams(array('dropfiles_google_last_changes_token' => $newPageToken));

        return false;
    }

    /**
     * Get change action
     *
     * @param Google_Service_Drive_DriveFile $file   Google file object
     * @param string                         $parent Parent id
     *
     * @return string
     * @since  4.1.5
     */
    private function getChangeAction($file, $parent)
    {
        if (!$file instanceof Google_Service_Drive_DriveFile) {
            return false;
        }

        $model         = $this->getModel();
        $localList     = $model->getAllGoogleCategories();

        // Add google base folder to folder list
        $params        = JComponentHelper::getParams('com_dropfiles');
        $baseFolderId    = $params->get('google_base_folder');
        if ($baseFolderId !== '') {
            $baseFolderObject           = new stdClass();
            $baseFolderObject->cloud_id = $baseFolderId;
            $localList[]                = $baseFolderObject;
        }
        $localFileList = $model->getAllGoogleFilesList();
        $id            = $file->getId();
        $mimeType      = $file->getMimeType();
        $trashed       = $file->getTrashed();
        if ($id === $baseFolderId) {
            return false;
        }
        if (!$this->inFolderList($parent, $localList)) {
            if ($mimeType === 'application/vnd.google-apps.folder' && $this->inFolderList($id, $localList) && $this->isParentFolderChanged($id, $parent, $baseFolderId) && $trashed === false) {
                return 'folder_removed'; // Folder move out of dropfiles categories
            } elseif ($mimeType !== 'application/vnd.google-apps.folder' && $this->inFileList($id, $localFileList) && $this->isParentChanged($id, $parent) && $trashed === false) {
                return 'file_removed'; // File move out of dropfiles categories
            }

            return false;
        }
        if ($mimeType === 'application/vnd.google-apps.folder+shared') {
            return false;
        }

        if ($mimeType === 'application/vnd.google-apps.folder') {
            // Is folder
            if (!$this->inFolderList($id, $localList) && !$this->isParentFolderChanged($id, $parent, $baseFolderId) && $trashed === false) {
                return 'folder_created';
            } elseif ($this->inFolderList($id, $localList) && $this->isParentFolderChanged($id, $parent, $baseFolderId) && $parent !== $baseFolderId && $trashed === false) {
                return 'folder_moved';
            } elseif ($trashed) {
                return 'folder_removed';
            } else {
                return 'folder_modified';
            }
        } else {
            // Is file
            if (!$this->inFileList($id, $localFileList) && !$this->isParentChanged($id, $parent) && $trashed === false) {
                return 'file_created';
            } elseif ($this->inFileList($id, $localFileList) && $this->isParentChanged($id, $parent) && $trashed === false) {
                return 'file_moved';
            } elseif ($trashed) {
                return 'file_removed';
            } else {
                return 'file_modified';
            }
        }
    }

    /**
     * Check is parent changed
     *
     * @param string $fileId      Google file id
     * @param string $newParentId New parent id
     *
     * @return boolean
     * @since  4.1.5
     */
    private function isParentChanged($fileId, $newParentId)
    {
        $modelGoogle = $this->getModel('googlefiles');
        if (!$modelGoogle instanceof DropfilesModelGooglefiles) {
            return null;
        }
        $file = $modelGoogle->getFile($fileId);

        if (!$file) {
            return false;
        }

        if (trim($file->catid) === trim($newParentId)) {
            return false;
        }

        return true;
    }

    /**
     * Is Parent folder changed
     *
     * @param string $folderId     Folder id
     * @param string $newParentId  New parent id
     * @param string $baseFolderId Base folder id
     *
     * @return boolean
     * @since  4.1.5
     */
    private function isParentFolderChanged($folderId, $newParentId, $baseFolderId)
    {
        $categoriesModel  = $this->getModel('categories');
        $localCat         = $categoriesModel->getOneCatByCloudId($folderId);

        $localCatParentId = (int) $localCat->parent_id;

        if ($newParentId === $baseFolderId) {
            $newCatParentId = 0;
        } else {
            $parentCat = $categoriesModel->getOneCatByCloudId($newParentId);
            $newCatParentId = (int) $parentCat->id;
        }

        if ($localCatParentId) {
            if ($localCatParentId !== $newCatParentId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check is folder in local list
     *
     * @param string $id   Folder id
     * @param array  $list Local list
     *
     * @return boolean
     * @since  4.1.5
     */
    private function inFolderList($id, $list)
    {
        foreach ($list as $cat) {
            if (isset($cat->cloud_id) && $id === $cat->cloud_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check is file in local list
     *
     * @param string $id   File id
     * @param array  $list Local files list
     *
     * @return boolean
     * @since  4.1.5
     */
    private function inFileList($id, $list)
    {
        foreach ($list as $cat) {
            if (isset($cat->file_id) && $id === $cat->file_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get changes from Google Drive
     *
     * @param string $pageToken    Page token
     * @param array  $changes      Changes list
     * @param string $newPageToken New page token to save to database
     *
     * @return void
     * @since  4.1.5
     */
    private function getChanges($pageToken, &$changes = array(), &$newPageToken = '')
    {
        $path_drf_google = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_drf_google);
        $google      = new DropfilesGoogle();
        $nextChanges = $google->listChanges($pageToken);

        if ($nextChanges !== false && $nextChanges instanceof Google_Service_Drive_ChangeList) {
            $changes = array_merge($changes, $nextChanges->getChanges());

            // Get next page token if provided
            if ($nextChanges->getNextPageToken()) {
                $newChanges = array();
                $aNewPageToken = $newPageToken;
                $this->getChanges($nextChanges->getNextPageToken(), $newChanges, $aNewPageToken);
                $changes = array_merge($changes, $newChanges);
                $newPageToken = $aNewPageToken;
            }

            // Set new page token if provided
            if ($nextChanges->getNewStartPageToken()) {
                $newPageToken = $nextChanges->getNewStartPageToken();
            }
        }
    }

    /**
     * Extract Google Header from request
     *
     * @param array $headers Headers array
     *
     * @return array
     * @since  5.2
     */
    private function extractHeader($headers)
    {
        $data = array();
        foreach ($headers as $key => $value) {
            if (strpos(strtoupper($key), 'HTTP_X_GOOG') === 0) {
                $data[strtoupper($key)] = $value;
            }
        }

        return $data;
    }

    /**
     * Sync A Google Drive Category with local
     *
     * @param string $cloudId Cloud category id
     *
     * @return boolean
     * @since  5.2.0
     */
    private function syncGoogleToLocalFolder($cloudId)
    {
        // Step 1: get category children
        $google = new DropfilesGoogle();
        $categoriesModel = $this->getModel('categories');
        try {
            $newCategories = $google->getListFolder($cloudId);
            // Step 2: sync with local
            if (count($newCategories) > 0) {
                $listCloudIdInDropfiles = $categoriesModel->arrayCloudIdDropfiles();

                foreach ($newCategories as $CloudId => $folderData) {
                    //if has parent_id
                    if (is_string($folderData['parent_id'])) {
                        $check = in_array($folderData['parent_id'], $listCloudIdInDropfiles);
                        if (!$check) {
                            //create Parent New
                            //$ParentCloudInfo = $dropfilesGoogle->getFileInfos($folderData['parent_id']);
                            $ParentCloudInfo = $newCategories[$folderData['parent_id']];
                            $newCatId = $categoriesModel->createOnCategories($ParentCloudInfo['title'], 1, 1);
                            if ($newCatId) {
                                $categoriesModel->createOnDropfiles($newCatId, 'googledrive', $folderData['parent_id']);
                                $listCloudIdInDropfiles[] = $folderData['parent_id'];
                            }
                            //create Children New with parent_id in dropfiles
                            if ($newCatId) {
                                $catRecentCreate = $categoriesModel->getOneCatByLocalId($newCatId);
                                $newChildId = $categoriesModel->createOnCategories(
                                    $folderData['title'],
                                    $catRecentCreate->id,
                                    (int)$catRecentCreate->level + 1
                                );
                                if ($newChildId) {
                                    $categoriesModel->createOnDropfiles(
                                        $newChildId,
                                        'googledrive',
                                        $CloudId
                                    );
                                    $listCloudIdInDropfiles[] = $CloudId;
                                }
                            }
                        } else {
                            // check exist before create
                            $localCat = $categoriesModel->getOneCatByCloudId($CloudId);
                            if (!$localCat) {
                                //create Children New with parent_id in dropfiles
                                $catOldInfo = $categoriesModel->getOneCatByCloudId($folderData['parent_id']);
                                $newCatId = $categoriesModel->createOnCategories(
                                    $folderData['title'],
                                    $catOldInfo->id,
                                    (int)$catOldInfo->level + 1
                                );
                                if ($newCatId) {
                                    $categoriesModel->createOnDropfiles(
                                        $newCatId,
                                        'googledrive',
                                        $CloudId
                                    );
                                    $listCloudIdInDropfiles[] = $CloudId;
                                }
                            }
                        }
                    } else {
                        //create Folder New
                        $newCatId = $categoriesModel->createOnCategories($folderData['title'], 1, 1);
                        if ($newCatId) {
                            $categoriesModel->createOnDropfiles($newCatId, 'googledrive', $CloudId);
                            $listCloudIdInDropfiles[] = $CloudId;
                        }
                    }

                    // Step 3: Sync file with local
                    $this->syncFileByCloudId($CloudId);
                }
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
