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
 * @since     1.6
 */

// no direct access
defined('_JEXEC') || die;
jimport('joomla.filesystem.folder');

/**
 * Class DropfilesControllerCategories
 */
class DropfilesControllerCategories extends JControllerAdmin
{
    /**
     * Proxy for getModel
     *
     * @param string $name   The model name. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array  $config Config
     *
     * @return JModelLegacy
     */
    public function getModel($name = 'Category', $prefix = 'DropfilesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Removes an item.
     *
     * @return void
     */
    public function delete()
    {
        // Check for request forgeries
        JSession::checkToken() || die(JText::_('JINVALID_TOKEN'));
        // Get items to remove from the request.
        $app = JFactory::getApplication();
        $cid = $app->input->getInt('id_category', 0);
        if ($cid) {
            // Get the model.
            $model = $this->getModel();
            JFactory::getApplication()->setUserState('list.limit', 100000);

            $modelCats = $this->getModel('categories');
            $modelCats->setState('category.id', $cid);
            $modelCats->setState('filter.parentId', $cid);
            $modelCats->setState('category.recursive', true);
            $items = $modelCats->getItems();
            $modelCats->setState('category.recursive', false);
            $canDo = DropfilesHelper::getActions();
            if ($canDo->get('core.delete')) {
                if (!$canDo->get('core.edit')) {
                    if ($canDo->get('core.edit.own')) {
                        $gallery = $model->getItem($cid);
                        if ($gallery->created_user_id !== JFactory::getUser()->id) {
                            $this->exitStatus('not permitted');
                        }
                    } else {
                        $this->exitStatus('not permitted');
                    }
                }
            } else {
                $this->exitStatus('not permitted');
            }

            //little hack because joomla always delete children, bug in version 3.1
            $joomla31 = false;
            if (version_compare(DropfilesBase::getJoomlaVersion(), '3.1')) {
                $joomla31 = true;
            }


            $errors = array();
            // Remove the items.
            $modelCat = $this->getModel('category');
            $category = $modelCat->getCategory($cid);
            if ($model->delete($cid)) {
                //todo : delete files from database
                if ($category->type === 'googledrive') {
                    $google = new DropfilesGoogle();
                    if (!$google->delete($category->cloud_id)) {
                        $errors[] = 'error while deleting directory, please delete google drive folder manually'; //todo: translate
                    }
                } elseif ($category->type === 'dropbox') {
                    $dropbox = new DropfilesDropbox();

                    if (!$dropbox->deleteDropbox($category->path)) {
                        $errors[] = 'error while deleting directory, please delete dropbox folder manually';
                    } else {
                        $model->deleteCatDropboxFiles($category->cloud_id);
                    }
                } elseif ($category->type === 'onedrive') {
                    $onedrive = new DropfilesOneDrive();
                    if (!$onedrive->delete($category->cloud_id)) {
                        $errors[] = 'error while deleting directory, please delete onedrive folder manually'; //todo: translate
                    } else {
                        $model->deleteCatOneDriveFiles($category->cloud_id);
                    }
                } elseif ($category->type === 'onedrivebusiness') {
                    $onedriveBusiness = new DropfilesOneDriveBusiness();
                    if (!$onedriveBusiness->delete($category->cloud_id)) {
                        $errors[] = 'error while deleting directory, please delete google drive folder manually'; //todo: translate
                    } else {
                        $model->deleteCatOneDriveBusinessFiles($category->cloud_id);
                    }
                } else {
                    $path = DropfilesBase::getFilesPath($cid[0]);
                    if (is_dir($path)) {
                        if (!JFolder::delete($path)) {
                            $errors[] = 'error while deleting directory, please delete folder ' . $path . ' manually'; //todo: translate
                        }
                    }
                }
                //delete children
                foreach ($items as $item) {
                    if ($item->id === $cid[0]) {
                        continue;
                    }
                    $id = $item->id;
                    $category = $modelCat->getCategory($id);
                    if ($model->delete($id) || $joomla31) {
                        if ($category->type === 'googledrive') {
                            $google = new DropfilesGoogle();
                            if (!$google->delete($category->cloud_id)) {
                                $errors[] = 'error while deleting directory, please delete google drive folder manually'; //todo: translate
                            }
                        } elseif ($category->type === 'dropbox') {
                            $dropbox = new DropfilesDropbox();
                            if (!$dropbox->deleteDropbox($category->path)) {
                                $errors[] = 'error while deleting directory, please delete dropbox folder manually'; //todo: translate
                            } else {
                                $model->deleteCatDropboxFiles($category->cloud_id);
                            }
                        }
                        if ($category->type === 'onedrive') {
                            $onedrive = new DropfilesOneDrive();
                            if (!$onedrive->delete($category->cloud_id)) {
                                $errors[] = 'error while deleting directory, please delete google drive folder manually'; //todo: translate
                            } else {
                                $model->deleteCatOneDriveFiles($category->cloud_id);
                            }
                        }
                        if ($category->type === 'onedrivebusiness') {
                            $onedriveBusiness = new DropfilesOneDriveBusiness();
                            if (!$onedriveBusiness->delete($category->cloud_id)) {
                                $errors[] = 'error while deleting directory, please delete google drive folder manually'; //todo: translate
                            } else {
                                $model->deleteCatOneDriveBusinessFiles($category->cloud_id);
                            }
                        } else {
                            $path = DropfilesBase::getFilesPath($item->id);
                            if (is_dir($path)) {
                                if (!JFolder::delete($path)) {
                                    $errors[] = 'error while deleting directory, please delete folder ' . $path . ' manually'; //todo: translate
                                }
                            }
                        }
                    } else {
                        $errors[] = 'error while deleting category'; //todo: translate
                    }
                }
            } else {
                $errors[] = $model->getError();
            }
            // Update files count
            $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
            $categoriesModel->updateFilesCount();
            if (count($errors)) {
                $this->exitStatus(implode('<br/>', $errors));
            }
            $this->exitStatus(true);
        }
    }

    /**
     * Ordering categories
     *
     * @return void
     */
    public function order()
    {
        JModelLegacy::addIncludePath(JPATH_ROOT . '/administrator/components/com_dropfiles/models/', 'DropfilesModel');
        $modelOneDriveBusinessCategory = JModelLegacy::getInstance('OnedriveBusinessCategory', 'dropfilesModel');
        $app = JFactory::getApplication();
        $position = $app->input->get('position', 'after');
        $pk = $app->input->getInt('pk', null);
        $ref = $app->input->getInt('ref');
        $dragType = $app->input->getString('dragType');
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

        if ((int)$ref === 0) {
            $ref = 1;
        }
        if ($position !== 'after') {
            $position = 'first-child';
        }

        $table = $model->getTable();
        if ($table->moveByReference($ref, $position, $pk)) {
            $thisModel = $this->getModel('categories');
            $this->params = JComponentHelper::getParams('com_dropfiles');
            $params = $this->params;
            if ($dragType === 'googledrive') {
                $dropfilesGoogle = new DropfilesGoogle();
                if ($params->get('google_credentials', '')) {
                    $itemInfo = $thisModel->getOneCatByLocalId($pk);
                    $parentInfo = $thisModel->getOneCatByLocalId($itemInfo->parent_id);
                    if (isset($parentInfo->cloud_id) && $parentInfo->cloud_id !== null) {
                        $dropfilesGoogle->moveFile($itemInfo->cloud_id, $parentInfo->cloud_id);
                    } else {
                        $dropfilesGoogle->moveFile($itemInfo->cloud_id, $params->get('google_base_folder'));
                    }
                }
            } elseif ($dragType === 'dropbox') {
                if ($params->get('dropbox_token', '')) {
                    $dropbox = new DropfilesDropbox();
                    $itemInfo = $thisModel->getOneCatByLocalId($pk);
                    $parentInfo = $thisModel->getOneCatByLocalId($itemInfo->parent_id);
                    $location = $thisModel->getOneCatByLocalId($ref);
                    if ($parentInfo !== null) {
                        $result = $dropbox->changeDropboxOrder(
                            $itemInfo->path,
                            $parentInfo->path,
                            (int)$itemInfo->parent_id
                        );
                    } else {
                        $result = $dropbox->changeDropboxOrder(
                            $itemInfo->path,
                            $location->path,
                            ((int)$itemInfo->parent_id - 1)
                        );
                    }
                    if ($result) {
                        $thisModel->updatePathDropboxById($pk, $result['path_lower']);
                    }
                }
            } elseif ($dragType === 'onedrive') {
                DropfilesCloudHelper::getAllOneDriveConfigs();
                if ($params->get('onedriveCredentials', '')) {
                    $onedrive = new dropfilesOnedrive();
                    $itemInfo = $thisModel->getOneCatByLocalId($pk);
                    $parent_cloud_id = $params->get('onedriveBaseFolderId');
                    $parentInfo = null;
                    if (!empty($itemInfo) && (int)$itemInfo->parent_id !== 1) {
                        $parentInfo = $thisModel->getOneCatByLocalId($itemInfo->parent_id);
                        if ($parentInfo && !empty($itemInfo)) {
                            $parent_cloud_id = $parentInfo->cloud_id;
                        }
                    }
                    if ($parent_cloud_id) {
                        $onedrive->moveFile($itemInfo->cloud_id, $parent_cloud_id);
                    }
                }
            } elseif ($dragType === 'onedrivebusiness') {
                $modelOneDriveBusinessCategory->changeOrder($pk);
            }
            $this->exitStatus(true, $pk . ' ' . $position . ' ' . $ref);
        }
        $this->exitStatus(JText::_('COM_DROPFILES_CTRL_MESSAGE_ERROR'));
    }

    /**
     * Get all tag
     *
     * @return void
     */
    public function getAllTags()
    {
        $model = $this->getModel('categories');
        $cat_tags = $model->getAllTagsFiles();
        // Set Params
        $comHelper = new DropfilesComponentHelper();
        $comHelper->setParams(array('cat_tags' => json_encode($cat_tags)));
        // Convert to available tags format
        $strTags = $comHelper->getAllTagsFiles($cat_tags);
        $session = JFactory::getSession();
        $session->set('all_tags', $strTags);
        $this->exitStatus(true, $strTags);
    }

    /**
     * Return a json response
     *
     * @param mixed $status Status
     * @param array $datas  Array of datas to return with the json string
     *
     * @return void
     */
    private function exitStatus($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        JFactory::getApplication()->close();
    }

    /**
     * Get parents category
     *
     * @return void
     * @throws \Exception Throw if
     * @since  1.0
     */
    public function getParentsCats()
    {
        $app = JFactory::getApplication();
        $modelCats = $this->getModel('categories');
        $cats = $modelCats->getParentsCat($app->input->getInt('id'), $app->input->getInt('displaycatid'));
        $cats = array_reverse($cats);
        echo json_encode($cats);
        die();
    }
}
