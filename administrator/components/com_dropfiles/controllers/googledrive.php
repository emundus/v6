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

jimport('joomla.filesystem.folder');

/**
 * Class DropfilesControllerGoogledrive
 */
class DropfilesControllerGoogledrive extends JControllerAdmin
{
    /**
     * Proxy for getModel
     *
     * @param string $name   Model name
     * @param string $prefix Model prefix
     * @param array  $config Configuration
     *
     * @return JModelLegacy
     * @since  version
     */
    public function getModel($name = 'Category', $prefix = 'DropfilesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Get authorize Url
     *
     * @return void
     * @since  version
     */
    public function getAuthorizeUrl()
    {
        $google = new DropfilesGoogle();
        $url = $google->getAuthorisationUrl();
        $this->setRedirect($url);
        $this->redirect();
    }

    /**
     * Authenticate GGD
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function authenticate()
    {
        $canDo = DropfilesHelper::getActions();

        if ($canDo->get('core.admin')) {
            $google = new DropfilesGoogle();
            $credentials = $google->authenticate();
            $google->storeCredentials($credentials);

            //Check if dropfiles folder exists and create if not
            $params = JComponentHelper::getParams('com_dropfiles');
            if (!$google->folderExists($params->get('google_base_folder', null))) {
                $folder = $google->createFolder('Dropfiles - ' . JFactory::getApplication()->getCfg('sitename'));
                if ($folder === false) {
                    $redirectDropfiles = 'index.php?option=com_dropfiles&view=dropfiles';
                    $this->setRedirect($redirectDropfiles, JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
                    $this->redirect();
                }
                DropfilesComponentHelper::setParams(array('google_base_folder' => $folder->getId()));
            }
            $this->setRedirect('index.php?option=com_dropfiles&view=googledrive&layout=redirect');
            $this->redirect();
        } else {
            $redirectDropfiles = 'index.php?option=com_dropfiles&view=dropfiles';
            $this->setRedirect($redirectDropfiles, JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->redirect();
        }
    }

    /**
     * Check auth
     *
     * @return boolean
     * @since  version
     */
    public function checkauth()
    {
        $canDo = DropfilesHelper::getActions();
        if ($canDo->get('core.admin')) {
            $google = new DropfilesGoogle();
            return $google->checkAuth();
        } else {
            $redirectDropfiles = 'index.php?option=com_dropfiles&view=dropfiles';
            $this->setRedirect($redirectDropfiles, JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->redirect();
        }
    }

    /**
     * Logout Google
     *
     * @return void
     * @since  version
     */
    public function logout()
    {
        $canDo = DropfilesHelper::getActions();

        if ($canDo->get('core.admin')) {
            $google = new DropfilesGoogle();
            $google->logout();

            // DropfilesComponentHelper::setParams(array('google_base_folder' => ''));
            DropfilesComponentHelper::setParams(array('google_credentials' => '', 'google_watch_changes' => 0));
            // Stop watch changes
            DropfilesCloudHelper::cancelWatchChanges();

            $this->setRedirect($_SERVER['HTTP_REFERER']);
            $this->redirect();
        } else {
            $redirectDropfiles = 'index.php?option=com_dropfiles&view=dropfiles';
            $this->setRedirect($redirectDropfiles, JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->redirect();
        }
    }

    /**
     * List folder cloud in Dropfiles
     *
     * @param array $categories Categories list
     *
     * @return array
     * @since  version
     */
    public function foldersCloudInDropfiles($categories)
    {
        $catCloud = array();
        if (isset($categories)) {
            $thisModel = $this->getModel('categories');
            foreach ($categories as $category) {
                    $oneCat = $thisModel->getOneCatByLocalId($category->parent_id);
                    $parent_cloud_id = 1;
                if (isset($oneCat->cloud_id)) {
                    $parent_cloud_id = $oneCat->cloud_id;
                }
                    $catCloud[$category->cloud_id] = array('id' => $category->id, 'title' => $category->title,
                        'parent_id' => $category->parent_id, 'parent_cloud_id' => $parent_cloud_id);
            }
        }
        return $catCloud;
    }

    /**
     * List sub folders under a folder in Google drive
     *
     * @param string $cloud_id Cloud folder id
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
     * List folder cloud in Google drive
     *
     * @return array|boolean
     * @since  version
     */
    public function foldersCloudInGoogleDrive()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $dropfilesGoogle = new DropfilesGoogle();
        try {
            $lstFolder = $dropfilesGoogle->getListFolder($params->get('google_base_folder'));
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
     * Abort sync progress
     *
     * @throws Exception Throw exception when application can not start
     * @return void
     * @since  5.0.2
     */
    public function abortsync()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $SID = $params->get('dropfiles_google_sid', '');

        header('Content-Type: application/json');
        if ($SID !== '' && isset($SID->running) && $SID->running === 1) {
            $SID->abort = 1;
            $SID->running = 0;
            $this->updateSID($SID);
            // Update files count
            $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
            $categoriesModel->updateFilesCount();
            echo json_encode(array('status'  => true));
            JFactory::getApplication()->close();
            exit();
        }

        echo json_encode(array('status'  => false));
        JFactory::getApplication()->close();
    }

    /**
     * Check sync status
     *
     * @throws Exception Throw exception when application can not start
     * @return void
     * @since  5.2.0
     */
    public function checkSyncStatus()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $SID = $params->get('dropfiles_google_sid', '');

        header('Content-Type: application/json');
        if ($SID === '') {
            echo json_encode(array('status'  => true));
            JFactory::getApplication()->close();
            exit();
        }

        echo json_encode(array('status'  => false));
        JFactory::getApplication()->close();
    }

    /**
     * Sync one Google Drive category
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function syncCategory()
    {
        $app = JFactory::getApplication();
        $cid = $app->input->getInt('id_category', 0);
        $params = JComponentHelper::getParams('com_dropfiles');
        if ($params->get('google_credentials') !== null) {
            $thisModel = $this->getModel('categories');
            $cloudCategory = $thisModel->getOneCatByLocalId($cid);
            //cloud folders in Dropfiles
            $folderCloudInDropfiles = $thisModel->childrenCloudInDropfiles($cid);
            $this->syncOneCategory($cloudCategory, $folderCloudInDropfiles);
        }

        header('Content-Type: application/json');
        echo json_encode(array('status' => true));
        JFactory::getApplication()->close();
    }

    /**
     * Sync Google Drive base folder
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function syncRoot()
    {
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_dropfiles');
        $rootFolder = $params->get('google_base_folder', null);

        if ($params->get('google_credentials') !== null) {
            $thisModel = $this->getModel('categories');

            $cloudCategory = new stdClass();
            $cloudCategory->id = 1; // System root ID
            $cloudCategory->level = 0;
            $cloudCategory->cloud_id = $rootFolder;

            //cloud folders in Dropfiles
            $folderCloudInDropfiles = $thisModel->getTopGoogleCategories();
            $this->syncOneCategory($cloudCategory, $folderCloudInDropfiles);
        }


        header('Content-Type: application/json');
        echo json_encode(array('status' => true));
        $app->close();
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
            $thisModel = $this->getModel('categories');
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
                        //check if this cloud folder is existed, it's maybe in under other category
                        $objectCurrent = $thisModel->getOneCatByCloudId($CloudId);
                        if ($objectCurrent) {
                            // Update parent ID
                            $this->order('first-child', $objectCurrent->id, $cloudCategory->id, false);
                        } else {
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
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        $clsDropfilesHelper->setParams(array('dropfiles_google_last_sync_time' => time()));
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
     * Google sync
     *
     * @param boolean $cron Run this sync via crontab or not
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function googlesync($cron = false)
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $SID = $this->getOrCreateNewSID(); // Get Sync Id or create new one

        if ($SID->running === 1) {
            if (!$cron) {
                header('Content-Type: application/json');
                echo json_encode(
                    array(
                        'status'  => false,
                        'type'    => 'confirm',
                        'message' => JText::_('COM_DROPFILES_GOOGLEDRIVE_SYNC_IS_RUNNING')
                    )
                );
                JFactory::getApplication()->close();
            }
        }

        if (!class_exists('DropfilesComponentHelper')) {
            JLoader::register(
                'DropfilesComponentHelper',
                JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php'
            );
        }

        $clsDropfilesHelper = new DropfilesComponentHelper();

        $SID->running = 1;
        $this->updateSID($SID);
        // $syncLogOption = $params->get('sync_log_option');
        $syncLogOption = 0;
        if ($syncLogOption !== null && $syncLogOption === 1) {
            JLog::addLogger(
                array(
                    // Sets file name
                    'text_file' => 'com_dropfiles.synclog.php',
                    // Sets the format of each line
                    'text_entry_format' => '{DATETIME} {PRIORITY} {CLIENTIP} {MESSAGE}'
                ),
                // Sets all but DEBUG log level messages to be sent to the file
                JLog::ALL & ~JLog::DEBUG,
                // The log category which should be recorded in this file
                array('com_dropfiles')
            );
        }
        if ($params->get('google_credentials') !== null) {
            $thisModel = $this->getModel('categories');
            //folders in Dropfiles
            $folderCloudInDropfiles = $this->foldersCloudInDropfiles($thisModel->getAllCat());
            //folders in Google Drive
            $folderCloudInGoogleDrive = $this->foldersCloudInGoogleDrive();
            if ($syncLogOption === 1) {
                $erros = 'folderCloudInGoogleDrive - ';
                $erros .= json_encode($folderCloudInGoogleDrive) . PHP_EOL;
                $erros .= 'folderCloudInDropfiles - ';
                $erros .= json_encode($folderCloudInDropfiles) . PHP_EOL;
                JLog::add($erros, JLog::INFO, 'com_dropfiles');
            }

            // folders created in Google Drive without have in Dropfiles
            $folders_diff = array();
            $folders_diff_del = array();
            if ($folderCloudInGoogleDrive !== false) {  //to ensure there isn't error when connect with GD
                if (count($folderCloudInDropfiles) > 0) {
                    $folders_diff = array_diff_key($folderCloudInGoogleDrive, $folderCloudInDropfiles);
                    $folders_diff_del = array_diff_key($folderCloudInDropfiles, $folderCloudInGoogleDrive);
                    if ($syncLogOption === 1) {
                        $erros = 'folders_diff - ';
                        $erros .= json_encode($folders_diff) . PHP_EOL;
                        $erros .= 'folders_diff_del - ';
                        $erros .= json_encode($folders_diff_del) . PHP_EOL;
                        JLog::add($erros, JLog::INFO, 'com_dropfiles');
                    }
                    foreach ($folderCloudInDropfiles as $k => $v) {
                        if ($this->isAbortSync()) {
                            $this->removeSID();
                            if (!$cron) {
                                header('Content-Type: application/json');
                                echo json_encode(array('status' => false));
                                JFactory::getApplication()->close();
                            }
                            break;
                        }
                        $objectCurrent = $thisModel->getOneCatByCloudId($k);
                        if ((!empty($folderCloudInGoogleDrive) && isset($folderCloudInGoogleDrive[$k])) &&
                            $folderCloudInDropfiles[$k]['title'] !== $folderCloudInGoogleDrive[$k]['title']) {
                            try {
                                $thisModel->updateTitleById($objectCurrent->id, $folderCloudInGoogleDrive[$k]['title']);
                            } catch (Exception $e) {
                                $erros = 'updateTitleById-Exception: ' . $e->getMessage();
                                JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                            }
                        }
                        if (!empty($folderCloudInGoogleDrive) && isset($folderCloudInGoogleDrive[$k])) {
                            // Fix Sync Children from google drive to dropfiles
                            $localParentId = $folderCloudInDropfiles[$k]['parent_id'];
                            $googleParentId = $folderCloudInGoogleDrive[$k]['parent_id'];

                            if (is_string($googleParentId)) {
                                // Get Local Id from google CloudID
                                $googleLocalParams = $thisModel->getOneCatByCloudId($googleParentId);
                                $googleLocalParentId = $googleLocalParams->id;

                                if ($localParentId !== $googleLocalParentId) {
                                    try {
                                        $this->order('first-child', $objectCurrent->id, $googleLocalParentId, false);
                                    } catch (Exception $e) {
                                        $erros = 'ChangeLocalParentFromCloud-Exception: ' . $e->getMessage();

                                        if ($syncLogOption === 1) {
                                            JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                                        }
                                    }
                                }
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
                $lstCloudIdOnDropfiles = $thisModel->arrayCloudIdDropfiles();
                foreach ($folders_diff as $CloudId => $folderData) {
                    if ($this->isAbortSync()) {
                        $this->removeSID();
                        if (!$cron) {
                            header('Content-Type: application/json');
                            echo json_encode(array('status' => false));
                            JFactory::getApplication()->close();
                        }
                        break;
                    }
                    try {
                        //if has parent_id
                        if (is_string($folderData['parent_id'])) {
                            $check = in_array($folderData['parent_id'], $lstCloudIdOnDropfiles);
                            if (!$check) {
                                //create Parent New
                                //$ParentCloudInfo = $dropfilesGoogle->getFileInfos($folderData['parent_id']);
                                $ParentCloudInfo = $folderCloudInGoogleDrive[$folderData['parent_id']];
                                $newCatId = $thisModel->createOnCategories($ParentCloudInfo['title'], 1, 1);
                                if ($newCatId) {
                                    $thisModel->createOnDropfiles($newCatId, 'googledrive', $folderData['parent_id']);
                                    $lstCloudIdOnDropfiles[] = $folderData['parent_id'];
                                }
                                //create Children New with parent_id in dropfiles
                                if ($newCatId) {
                                    $catRecentCreate = $thisModel->getOneCatByLocalId($newCatId);
                                    $newChildId = $thisModel->createOnCategories(
                                        $folderData['title'],
                                        $catRecentCreate->id,
                                        (int)$catRecentCreate->level + 1
                                    );
                                    if ($newChildId) {
                                        $thisModel->createOnDropfiles(
                                            $newChildId,
                                            'googledrive',
                                            $CloudId
                                        );
                                        $lstCloudIdOnDropfiles[] = $CloudId;
                                    }
                                }
                            } else {
                                //create Children New with parent_id in dropfiles
                                $catOldInfo = $thisModel->getOneCatByCloudId($folderData['parent_id']);
                                $newCatId = $thisModel->createOnCategories(
                                    $folderData['title'],
                                    $catOldInfo->id,
                                    (int)$catOldInfo->level + 1
                                );
                                if ($newCatId) {
                                    $thisModel->createOnDropfiles(
                                        $newCatId,
                                        'googledrive',
                                        $CloudId
                                    );
                                    $lstCloudIdOnDropfiles[] = $CloudId;
                                }
                            }
                        } else {
                            //create Folder New
                            $newCatId = $thisModel->createOnCategories($folderData['title'], 1, 1);
                            if ($newCatId) {
                                $thisModel->createOnDropfiles($newCatId, 'googledrive', $CloudId);
                                $lstCloudIdOnDropfiles[] = $CloudId;
                            }
                        }
                    } catch (Exception $e) {
                        $clsDropfilesHelper->setParams(array('dropfiles_google_last_sync_time' => time()));
                        $this->removeSID();
                        $erros = $e->getMessage();
                        if ($syncLogOption === 1) {
                            JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                        }
                        break;
                    }
                }
            }
            $clsDropfilesHelper->setParams(array('last_log' => date('Y-m-d H:i:s'), 'dropfiles_google_last_sync_time' => time()));
            $this->removeSID();
            // Reset watch changes
            if ((int) $params->get('google_watch_changes', 1) === 1) {
                DropfilesCloudHelper::watchChanges();
            }
            // Update files count
            $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
            $categoriesModel->updateFilesCount();
            if (!$cron) {
                header('Content-Type: application/json');
                echo json_encode(array('status' => true));
                JFactory::getApplication()->close();
            }
        }
        $clsDropfilesHelper->setParams(array('dropfiles_google_last_sync_time' => time()));
        $this->removeSID();
    }

    /**
     * Sync Google Drive on crontask
     *
     * @return void
     * @throws \Exception Application can not start
     * @since  version
     */
    public function googlesyncviacron()
    {
        $this->googlesync(true);
        $step = 0;
        $path_drf_google = JPATH_BASE . '/components/com_dropfiles/controllers/frontgoogle.php';
        require_once $path_drf_google;
        $frontgg = new DropfilesControllerFrontgoogle();
        while ($frontgg->syncFiles(true, $step)) {
            $step++;
        }

        echo json_encode(array('success' => true));
        JFactory::getApplication()->close();
    }

    /**
     * Order item
     *
     * @param string  $position Position
     * @param integer $pk       Current Category id
     * @param integer $ref      Ref category id
     * @param boolean $return   Return result or not
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function order($position, $pk, $ref, $return = true)
    {
        $status = false;
        $message = '';
        $model = $this->getModel();
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.edit')) {
            if ($canDo->get('core.edit.own')) {
                $category = $model->getItem($pk);
                if ($category->created_user_id !== JFactory::getUser()->id) {
                    $message = 'not permitted';
                }
            } else {
                $message = 'not permitted';
            }
        }

        if ((int) $ref === 0) {
            $ref = 1;
        }
        if ($position !== 'after') {
            $position = 'first-child';
        }

        $table = $model->getTable();
        if ($table->moveByReference($ref, $position, $pk)) {
            $status = true;
            $message = $pk . ' ' . $position . ' ' . $ref;
        } else {
            $message = JText::_('COM_DROPFILES_CTRL_MESSAGE_ERROR');
        }

        if ($return) {
            $this->exitStatus($status, $message);
        }
    }

    /**
     * Return a json response
     *
     * @param boolean $status Response status
     * @param array   $datas  Array of datas to return with the json string
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    private function exitStatus($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        JFactory::getApplication()->close();
    }

    /**
     * Get or create new sync id
     *
     * @return mixed|stdClass
     * @since  5.2.0
     */
    private function getOrCreateNewSID()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $SID    = $params->get('dropfiles_google_sid', '');

        if ($SID === '') {
            $SID               = new stdClass();
            $SID->created_time = time();
            $SID->running      = 0; // SID Not running on set
            $SID->abort        = 0;

            $this->updateSID($SID);
        }

        return $SID;
    }

    /**
     * Check current sync aborted
     *
     * @return boolean
     * @since  5.2.0
     */
    private function isAbortSync()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $SID    = $params->get('dropfiles_google_sid', '');

        if ($SID !== '' && isset($SID->abort) && $SID->abort === 1) {
            return true;
        }

        return false;
    }

    /**
     * Update SID
     *
     * @param object $SID Sync id
     *
     * @return void
     * @since  5.2.0
     */
    private function updateSID($SID)
    {
        if (!class_exists('DropfilesComponentHelper')) {
            JLoader::register(
                'DropfilesComponentHelper',
                JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php'
            );
        }
        $helper = new DropfilesComponentHelper();
        $helper->setParams(array('dropfiles_google_sid' => $SID));
    }

    /**
     * Remove SID
     *
     * @return void
     * @since  5.2.0
     */
    private function removeSID()
    {
        $this->updateSID('');
    }
}
