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
 * Class DropfilesControllerCloudBusiness
 */
class DropfilesControllerOnedrivebusiness extends JControllerAdmin
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
     * Get config param oneDrive Business
     *
     * @return array
     * @since  version
     */
    public function getParams()
    {
        return DropfilesCloudHelper::getAllOneDriveBusinessConfigs();
    }

    /**
     * Authenticated
     *
     * @return void
     *
     * @throws Exception Throw when application can not start
     */
    public function authenticated()
    {
        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        $path_onedrivebusiness = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDriveBusiness.php';
        JLoader::register('DropfilesOneDriveBusiness', $path_onedrivebusiness);
        $state = JFactory::getApplication()->input->get('state');
        if ($state === 'dropfiles-onedrive-business') {
            $onedrive = new DropfilesOneDriveBusiness();
            if ($onedrive->authenticate()) {
                $this->setRedirect('index.php?option=com_dropfiles&view=onedrivebusiness&layout=redirect');
                $this->redirect();
            }
        }
    }

    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {
        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        $path_onedrivebusiness = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDriveBusiness.php';
        JLoader::register('DropfilesOneDriveBusiness', $path_onedrivebusiness);
        $onedrive = new DropfilesOneDriveBusiness();
        $onedrive->logout();
        $this->setRedirect(JRoute::_('index.php?option=com_dropfiles&task=configuration.display', false));
    }

    /**
     * OneDrive Business sync server with client
     *
     * @return void
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function oneDriveBusinessSync()
    {
        $params = $this->getParams();
        if (isset($params['state']) && $params['state'] !== null && $params['state'] !== '') {
            $thisModel                      = $this->getModel('categories');
            $oneDriveBusinessCats           = $thisModel->getAllOneDriveBusinessCat();
            $folderCloudInDropfiles         = $this->foldersCloudInDropfiles($oneDriveBusinessCats); // Folders in Dropfiles
            $folderCloudInOneDriveBusiness  = $this->foldersInOneDriveBusiness(); // Folders in OneDrive Business
            // Folders created in Google Drive without have in Dropfiles
            $folders_diff           = array();
            $folders_diff_del       = array();

            if ($folderCloudInOneDriveBusiness !== false) {  // To ensure there isn't error when connect with GD
                if (count($folderCloudInDropfiles) > 0) {
                    $folders_diff       = array_diff_key($folderCloudInOneDriveBusiness, $folderCloudInDropfiles);
                    $folders_diff_del   = array_diff_key($folderCloudInDropfiles, $folderCloudInOneDriveBusiness);

                    foreach ($folderCloudInDropfiles as $k => $v) {
                        if ((!empty($folderCloudInOneDriveBusiness) && isset($folderCloudInOneDriveBusiness[$k])) &&
                            $folderCloudInDropfiles[$k]['title'] !== $folderCloudInOneDriveBusiness[$k]['title']) {
                            $objectCurrent = $thisModel->getOneCatByCloudId($k);
                            try {
                                $thisModel->updateTitleById($objectCurrent->id, $folderCloudInOneDriveBusiness[$k]['title']);
                            } catch (Exception $e) {
                                $erros = 'updateTitleById-Exception: ' . $e->getMessage();
                                JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                            }
                        }
                        //update children
                        if ((!empty($folderCloudInOneDriveBusiness) && isset($folderCloudInOneDriveBusiness[$k])) &&
                            $folderCloudInDropfiles[$k]['parent_cloud_id'] !== $folderCloudInOneDriveBusiness[$k]['parent_id']) {
                            $parent_cloud_id_onedrive_business  = $folderCloudInOneDriveBusiness[$k]['parent_id'];
                            $item_parent_id                     = $parent_cloud_id_onedrive_business;
                            if ($parent_cloud_id_onedrive_business !== 0) {
                                $item_parent_id = $folderCloudInDropfiles[$parent_cloud_id_onedrive_business]['id'];
                            }
                            $this->order('first-child', $folderCloudInDropfiles[$k]['id'], $item_parent_id, false);
                        }
                    }
                } else {
                    $folders_diff = $folderCloudInOneDriveBusiness;
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
                $lstCloudIdOnDropfiles = $thisModel->arrayOneDriveBusinessIdDropfiles();
                foreach ($folders_diff as $CloudId => $folderData) {
                    try {
                        // If has parent_id
                        if ($folderData['parent_id'] !== 0) {
                            $check = in_array($folderData['parent_id'], $lstCloudIdOnDropfiles);
                            if (!$check) {
                                // Create Parent New
                                $ParentCloudInfo = $folderCloudInOneDriveBusiness[$folderData['parent_id']];
                                $newCatId = $thisModel->createOnCategories($ParentCloudInfo['title'], 1, 1);
                                if ($newCatId) {
                                    $thisModel->createOnDropfiles($newCatId, 'onedrivebusiness', $folderData['parent_id']);
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
                                            'onedrivebusiness',
                                            $CloudId
                                        );
                                        $lstCloudIdOnDropfiles[] = $CloudId;
                                    }
                                }
                            } else {
                                //create Children New with parent_id in dropfiles
                                $catOldInfo = $thisModel->getOneCatByCloudId($folderData['parent_id']);
                                $newCatId   = $thisModel->createOnCategories(
                                    $folderData['title'],
                                    $catOldInfo->id,
                                    (int)$catOldInfo->level + 1
                                );
                                if ($newCatId) {
                                    $thisModel->createOnDropfiles($newCatId, 'onedrivebusiness', $CloudId);
                                    $lstCloudIdOnDropfiles[] = $CloudId;
                                }
                            }
                        } else {
                            //create Folder New
                            $newCatId = $thisModel->createOnCategories($folderData['title'], 1, 1);
                            if ($newCatId) {
                                $thisModel->createOnDropfiles($newCatId, 'onedrivebusiness', $CloudId);
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
            $categoriesModel        = $this->getModel('Categories', 'DropfilesModel');
            $categoriesModel->updateFilesCount();
            $path_admin_component   = JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php';
            JLoader::register('DropfilesComponentHelper', $path_admin_component);
            $clsDropfilesHelper     = new DropfilesComponentHelper();
            $clsDropfilesHelper->setParams(array('last_log' => date('Y-m-d H:i:s')));
            echo json_encode(array('status' => true));
            JFactory::getApplication()->close();
        }
    }

    /**
     * Get all onedrive business folder in dropfiles
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
                $thisModel          = $this->getModel('categories');
                $oneCat             = $thisModel->getOneCatByLocalId($category->parent_id);
                $parent_cloud_id    = 1;
                if (isset($oneCat->cloud_id)) {
                    $parent_cloud_id = $oneCat->cloud_id;
                }
                $catCloud[$category->cloud_id] = array(
                    'id'                => $category->id,
                    'title'             => $category->title,
                    'parent_id'         => $category->parent_id,
                    'parent_cloud_id'   => $parent_cloud_id
                );
            }
        }
        return $catCloud;
    }

    /**
     * Get all folder in onedrive business server
     *
     * @return array
     * @since  version
     */
    public function foldersInOneDriveBusiness()
    {
        // Config params
        $data               = $this->getParams();
        $oneDriveBusiness   = new DropfilesOneDriveBusiness();
        if (!is_array($data['onedriveBusinessBaseFolder'])) {
            $data['onedriveBusinessBaseFolder'] = (array) $data['onedriveBusinessBaseFolder'];
        }
        $lstFolder          = $oneDriveBusiness->getListFolder($data['onedriveBusinessBaseFolder']['id']);
        return $lstFolder;
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
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function order($position, $pk, $ref, $return = true)
    {
        $status = false;
        $model  = $this->getModel();
        $canDo  = DropfilesHelper::getActions();
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
     * @throws Exception Throw when application can not start
     * @since  version
     */
    private function exitStatus($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        JFactory::getApplication()->close();
    }
}
