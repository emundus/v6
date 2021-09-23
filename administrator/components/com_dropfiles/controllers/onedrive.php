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
 * Class DropfilesControllerCloud
 */
class DropfilesControllerOneDrive extends JControllerAdmin
{

    /**
     * Proxy for getModel
     *
     * @param string $name   Model name
     * @param string $prefix Model prefix
     * @param array  $config Configurations
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
     * Get config param onedrive
     *
     * @return array
     * @since  version
     */
    public function getParams()
    {
        return DropfilesCloudHelper::getAllOneDriveConfigs();
    }

    /**
     * Save config param onedrive
     *
     * @param array $data Data
     *
     * @return void
     * @since  version
     */
    public function setParams($data)
    {
        DropfilesCloudHelper::setParamsConfigs($data);
    }

    /**
     * Get config old param onedrive
     *
     * @return array
     * @since  version
     */
    public function getParamsOld()
    {
        return DropfilesCloudHelper::getAllOneDriveConfigsOld();
    }

    /**
     * Save config old param onedrive
     *
     * @param array $data Data
     *
     * @return void
     * @since  version
     */
    public function setParamsOld($data)
    {
        DropfilesCloudHelper::setParamsConfigsOld($data);
    }

    /**
     * Get Authorize Url onedrive
     *
     * @return void
     * @since  version
     */
    public function getAuthorizeUrl()
    {
        $onedrive = new DropfilesOneDrive();
        $url = $onedrive->getAuthorisationUrl();
        $this->setRedirect($url);
        $this->redirect();
    }

    /**
     * Check onedrive permitted
     *
     * True: create new folder in server
     * False: return
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function authenticate()
    {
        $canDo = DropfilesHelper::getActions();

        if ($canDo->get('core.admin')) {
            $onedrive    = new DropfilesOneDrive();
            $credentials = $onedrive->authenticate($_GET['code']);
            $onedrive->storeCredentials($credentials);
            $data                = $this->getParams();
            $dataOld             = $this->getParamsOld();
            $onedriveClientIdOld = empty($dataOld['onedriveKeyOld']) ? 0 : $dataOld['onedriveKeyOld'];
            $onedriveClientId    = empty($data['onedriveKey']) ? - 1 : $data['onedriveKey'];
            $baseFolderIdOld     = empty($dataOld['onedriveBaseFolderIdOld']) ? - 1 : $dataOld['onedriveBaseFolderIdOld'];
            $baseFolderNameOld   = empty($dataOld['onedriveBaseFolderNameOld'])
                ? - 1 : $dataOld['onedriveBaseFolderNameOld'];
            if (!empty($dataOld['onedriveBaseFolderNameOld'])) {
                $baseFolderNameOld = $dataOld['onedriveBaseFolderNameOld'];
            }
            if ($baseFolderIdOld && $onedriveClientId === $onedriveClientIdOld) {
                if ($onedrive->getFolder($baseFolderNameOld, $baseFolderIdOld)) {
                    $data['onedriveBaseFolderId'] = $dataOld['onedriveBaseFolderIdOld'];
                    $data['onedriveBaseFolderName'] = $dataOld['onedriveBaseFolderNameOld'];
                } else {
                    $newentry                       = $this->newEntryOnedrive($onedrive);
                    $idroot                         = $newentry->getId();
                    $nameroot                       = $newentry->getName();
                    $data['onedriveBaseFolderId']   = DropfilesCloudHelper::replaceIdOneDrive($idroot);
                    $data['onedriveBaseFolderName'] = $nameroot;
                    $this->setParamsOld($data);
                }
            } else {
                $newentry                       = $this->newEntryOnedrive($onedrive);
                $idroot                         = $newentry->getId();
                $nameroot                       = $newentry->getName();
                $data['onedriveBaseFolderId']   = DropfilesCloudHelper::replaceIdOneDrive($idroot);
                $data['onedriveBaseFolderName'] = $nameroot;
                $this->setParamsOld($data);
            }
            $data['onedriveConnected'] = 1;
            $this->setParams($data);
            $this->setRedirect('index.php?option=com_dropfiles&view=onedrive&layout=redirect');
            $this->redirect();
        } else {
            $redirect_dropfiles = 'index.php?option=com_dropfiles&view=dropfiles';
            $this->setRedirect($redirect_dropfiles, JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->redirect();
        }
    }

    /**
     * Add new root folder
     *
     * @param DropfilesOneDrive $onedrive Onedrive instance
     *
     * @return OneDrive_Service_Drive_Item
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function newEntryOnedrive($onedrive)
    {
        $newentry = $onedrive->addFolderRoot('Dropfiles - ' . JFactory::getApplication()->getCfg('sitename'));
        $decoded = json_decode($newentry['responsebody'], true);
        return new OneDrive_Service_Drive_Item($decoded);
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
            $onedrive = new DropfilesOneDrive();

            return $onedrive->checkAuth();
        } else {
            $redirect_dropfiles = 'index.php?option=com_dropfiles&view=dropfiles';
            $this->setRedirect($redirect_dropfiles, JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->redirect();
        }
    }

    /**
     * Log out onedrive
     *
     * @return void
     * @since  version
     */
    public function logout()
    {
        $canDo = DropfilesHelper::getActions();

        if ($canDo->get('core.admin')) {
            $onedrive = new DropfilesOneDrive();
            $onedrive->logout();
            $data = $this->getParams();
            $data['onedriveConnected'] = 0;
            $data['onedriveCredentials'] = '';
            $this->setParams($data);

            $this->setRedirect($_SERVER['HTTP_REFERER']);
            $this->redirect();
        } else {
            $redirect_dropfiles = 'index.php?option=com_dropfiles&view=dropfiles';
            $this->setRedirect($redirect_dropfiles, JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->redirect();
        }
    }

    /**
     * Get all onedrive folder in dropfiles
     *
     * @param array $categories Categories
     *
     * @return array
     * @since  version
     */
    public function foldersCloudInDropfiles($categories)
    {
        $catCloud = array();
        if (isset($categories)) {
            foreach ($categories as $category) {
                $thisModel = $this->getModel('categories');
                $oneCat = $thisModel->getOneCatByLocalId($category->parent_id);
                $parent_cloud_id = 1;
                if (isset($oneCat->cloud_id)) {
                    $parent_cloud_id = $oneCat->cloud_id;
                }
                $catCloud[$category->cloud_id] = array('id' => $category->id,
                    'title' => $category->title,
                    'parent_id' => $category->parent_id,
                    'parent_cloud_id' => $parent_cloud_id
                );
            }
        }
        return $catCloud;
    }

    /**
     * Get all folder in onedrive server
     *
     * @return array
     * @since  version
     */
    public function foldersInOneDrive()
    {
        $data = $this->getParams();
        $onedrive = new DropfilesOneDrive();
        $lstFolder = $onedrive->getListFolder($data['onedriveBaseFolderId']);
        return $lstFolder;
    }

    /**
     * Onedrive sync server with client
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function onedrivesync()
    {
        $params = $this->getParams();
        if ($params['onedriveCredentials'] !== null && $params['onedriveCredentials'] !== '') {
            $thisModel = $this->getModel('categories');
            // Folders in Dropfiles
            $folderCloudInDropfiles = $this->foldersCloudInDropfiles($thisModel->getAllOneDriveCat());
            // Folders in OneDrive
            $folderCloudInOneDrive = $this->foldersInOneDrive();
            // Folders created in Google Drive without have in Dropfiles
            $folders_diff = array();
            $folders_diff_del = array();
            if ($folderCloudInOneDrive !== false) {  // To ensure there isn't error when connect with GD
                if (count($folderCloudInDropfiles) > 0) {
                    $folders_diff = array_diff_key($folderCloudInOneDrive, $folderCloudInDropfiles);
                    $folders_diff_del = array_diff_key($folderCloudInDropfiles, $folderCloudInOneDrive);

                    foreach ($folderCloudInDropfiles as $k => $v) {
                        if ((!empty($folderCloudInOneDrive) && isset($folderCloudInOneDrive[$k])) &&
                            $folderCloudInDropfiles[$k]['title'] !== $folderCloudInOneDrive[$k]['title']
                        ) {
                            $objectCurrent = $thisModel->getOneCatByCloudId($k);
                            try {
                                $thisModel->updateTitleById($objectCurrent->id, $folderCloudInOneDrive[$k]['title']);
                            } catch (Exception $e) {
                                $erros = 'updateTitleById-Exception: ' . $e->getMessage();
                                JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                            }
                        }
                        //update children
                        if ((!empty($folderCloudInOneDrive) && isset($folderCloudInOneDrive[$k])) &&
                            $folderCloudInDropfiles[$k]['parent_cloud_id'] !== $folderCloudInOneDrive[$k]['parent_id']
                        ) {
                            $parent_cloud_id_onedrive = $folderCloudInOneDrive[$k]['parent_id'];
                            $item_parent_id = $parent_cloud_id_onedrive;
                            if ($parent_cloud_id_onedrive !== 1) {
                                $item_parent_id = $folderCloudInDropfiles[$parent_cloud_id_onedrive]['id'];
                            }
                            $this->order('first-child', $folderCloudInDropfiles[$k]['id'], $item_parent_id, false);
                        }
                    }
                } else {
                    $folders_diff = $folderCloudInOneDrive;
                }
            }

            if (count($folders_diff_del) > 0) {
                foreach ($folders_diff_del as $CloudIdDel => $folderDataDel) {
                    $catInfoLocal = $thisModel->getOneCatByCloudId($CloudIdDel);
                    $thisModel->deleteOnDropfiles($CloudIdDel);
                    $thisModel->deleteOnCategories($catInfoLocal->id);
                }
            }
            // If exists diff key array
            if (count($folders_diff) > 0) {
                $lstCloudIdOnDropfiles = $thisModel->arrayOneDriveIdDropfiles();
                foreach ($folders_diff as $CloudId => $folderData) {
                    try {
                        // If has parent_id
                        if ($folderData['parent_id'] !== 1) {
                            $check = in_array($folderData['parent_id'], $lstCloudIdOnDropfiles);
                            if (!$check) {
                                // Create Parent New
                                $ParentCloudInfo = $folderCloudInOneDrive[$folderData['parent_id']];
                                $newCatId = $thisModel->createOnCategories($ParentCloudInfo['title'], 1, 1);
                                if ($newCatId) {
                                    $thisModel->createOnDropfiles($newCatId, 'onedrive', $folderData['parent_id']);
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
                                            'onedrive',
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
                                    $thisModel->createOnDropfiles($newCatId, 'onedrive', $CloudId);
                                    $lstCloudIdOnDropfiles[] = $CloudId;
                                }
                            }
                        } else {
                            //create Folder New
                            $newCatId = $thisModel->createOnCategories($folderData['title'], 1, 1);
                            if ($newCatId) {
                                $thisModel->createOnDropfiles($newCatId, 'onedrive', $CloudId);
                                $lstCloudIdOnDropfiles[] = $CloudId;
                            }
                        }
                    } catch (Exception $e) {
                        $erros = $e->getMessage();
                        JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                        break;
                    }
                }
            }
            // Update files count
            $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
            $categoriesModel->updateFilesCount();
            $path_admin_component = JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php';
            JLoader::register('DropfilesComponentHelper', $path_admin_component);
            $clsDropfilesHelper = new DropfilesComponentHelper();
            $clsDropfilesHelper->setParams(array('last_log' => date('Y-m-d H:i:s')));
            echo json_encode(array('status' => true));
            JFactory::getApplication()->close();
        }
    }

    /**
     * Order category in dropfiles
     *
     * @param string  $position Position
     * @param integer $pk       Current category id
     * @param integer $ref      Target category id
     * @param boolean $return   Return result or not
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function order($position, $pk, $ref, $return = true)
    {
        $status = false;
        $model = $this->getModel();
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.edit')) {
            if ($canDo->get('core.edit.own')) {
                $category = $model->getItem($pk);
                if ($category->created_user_id !== JFactory::getUser()->id) {
                    $this->exitStatus('not permitted');
                }
            } else {
                $this->exitStatus('not permitted');
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
}
