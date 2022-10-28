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
 * Class DropfilesControllerDropbox
 */
class DropfilesControllerDropbox extends JControllerAdmin
{

    /**
     * Proxy for getModel
     *
     * @param string $name   Model name
     * @param string $prefix Model prefix
     * @param array  $config Config
     *
     * @return JModelLegacy
     * @since  1.0
     */
    public function getModel($name = 'Category', $prefix = 'DropfilesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Get cloud folders in Dropfiles
     *
     * @param array $categories Category list
     *
     * @return array
     * @since  1.0
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
                    'path' => $category->path,
                    'parent_cloud_id' => $parent_cloud_id
                );
            }
        }
        return $catCloud;
    }

    /**
     * Get folders cloud in Dropbox
     *
     * @return array|boolean
     * @since  1.0
     */
    public function foldersCloudInDropbox()
    {
        $dropfilesDropbox = new DropfilesDropbox();
        try {
            $lstFolder = $dropfilesDropbox->listAllFolders();
            $dropboxPath = array();
            if ($lstFolder) {
                foreach ($lstFolder as $key => $val) {
                    $dropboxPath[$val['path_lower']] = $val;
                }
            }

            foreach ($lstFolder as $key => $val) {
                $path_current        = $lstFolder[$key]['path_lower'];
                $fpath               = pathinfo($path_current);
                $parent_path_dropbox = $fpath['dirname'];
                if (isset($dropboxPath[$parent_path_dropbox])) {
                    $lstFolder[$key]['parent_id'] = $dropboxPath[$parent_path_dropbox]['id'];
                } else {
                    $lstFolder[$key]['parent_id'] = 1;
                }
            }
        } catch (Exception $e) {
            $lstFolder = false;
        }
        return $lstFolder;
    }


    /**
     * Sync dropbox files
     *
     * @throws \Exception Throw when application can not start
     * @return void
     * @since  1.0
     */
    public function sync()
    {
        JLoader::register('DropfilesDropbox', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesDropbox.php');
        $params = JComponentHelper::getParams('com_dropfiles');
        if ($params->get('dropbox_token') !== '') {
            $thisModel = $this->getModel('categories');
            //folders in Dropfiles
            $folderCloudInDropfiles = $this->foldersCloudInDropfiles($thisModel->getAllDropboxCat());
            //folders in Dropbox
            $folderCloudInDropbox = $this->foldersCloudInDropbox();
            //folders created  without have in Dropfiles
            $folders_diff = array();
            $folders_diff_del = array();
            if ($folderCloudInDropbox !== false) {
                if (is_array($folderCloudInDropfiles) && !empty($folderCloudInDropfiles) && count($folderCloudInDropfiles) > 0) {
                    $folders_diff = array_diff_key($folderCloudInDropbox, $folderCloudInDropfiles);
                    $folders_diff_del = array_diff_key($folderCloudInDropfiles, $folderCloudInDropbox);

                    foreach ($folderCloudInDropfiles as $k => $v) {
                        if ((!empty($folderCloudInDropbox) && isset($folderCloudInDropbox[$k])) &&
                            $folderCloudInDropfiles[$k]['title'] !== $folderCloudInDropbox[$k]['name']) {
                            $objectCurrent = $thisModel->getOneCatByCloudId($k);
                            try {
                                $thisModel->updateTitleById($objectCurrent->id, $folderCloudInDropbox[$k]['name']);
                            } catch (Exception $e) {
                                $erros = 'updateTitleById-Exception: ' . $e->getMessage();
                                JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                            }
                        }
                        if ((!empty($folderCloudInDropbox) && isset($folderCloudInDropbox[$k])) &&
                            $folderCloudInDropfiles[$k]['path'] !== $folderCloudInDropbox[$k]['path_lower']) {
                            $objectCurrent = $thisModel->getOneCatByCloudId($k);
                            try {
                                $thisModel->updatePathDropboxById(
                                    $objectCurrent->id,
                                    $folderCloudInDropbox[$k]['path_lower']
                                );
                            } catch (Exception $e) {
                                $erros = 'updatePathById-Exception: ' . $e->getMessage();
                                JLog::add($erros, JLog::ERROR, 'com_dropfiles');
                            }
                        }

                        // Update children from google drive to dropfile
                        if ((!empty($folderCloudInDropbox) && isset($folderCloudInDropbox[$k])) &&
                            $folderCloudInDropfiles[$k]['parent_cloud_id'] !== $folderCloudInDropbox[$k]['parent_id']
                        ) {
                            $parent_cloud_id_dropbox = $folderCloudInDropbox[$k]['parent_id'];
                            $item_parent_id = $parent_cloud_id_dropbox;
                            if ($parent_cloud_id_dropbox !== 1) {
                                $item_parent_id = $folderCloudInDropfiles[$parent_cloud_id_dropbox]['id'];
                            }
                            $this->order('first-child', $folderCloudInDropfiles[$k]['id'], $item_parent_id, false);
                        }
                    }
                } else {
                    $folders_diff = $folderCloudInDropbox;
                }
            }

            if (is_array($folders_diff_del) && !empty($folders_diff_del) && count($folders_diff_del) > 0) {
                foreach ($folders_diff_del as $CloudIdDel => $folderDataDel) {
                    $catInfoLocal = $thisModel->getOneCatByCloudId($CloudIdDel);
                    $thisModel->deleteOnDropfiles($CloudIdDel);
                    $thisModel->deleteOnCategories($catInfoLocal->id);
                }
            }
            //if exists diff key array
            if (is_array($folders_diff) && !empty($folders_diff) && count($folders_diff) > 0) {
                $dropboxPathCats = array();
                $dropboxCats = $thisModel->getAllDropboxCat();
                if ($dropboxCats) {
                    foreach ($dropboxCats as $dropcat) {
                        $dropboxPathCats[$dropcat->cloud_id] = $dropcat;
                    }
                }

                foreach ($folders_diff as $CloudId => $folderData) {
                    $fpath = pathinfo($folderData['path_lower']);
                    $idDropboxDir = $fpath['dirname'];

                    if ($idDropboxDir !== DIRECTORY_SEPARATOR) {
                        //find parent term
                        if (isset($dropboxPathCats[$idDropboxDir])) {
                            $cat = $thisModel->getOneCatByCloudId($dropboxPathCats[$CloudId]->cloud_id);

                            if ($cat) {
                                $newCatId = $thisModel->createOnCategories(
                                    $folderData['name'],
                                    $cat->id,
                                    (int)$cat->level + 1
                                );

                                if ($newCatId) {
                                    $thisModel->createDropboxOnDropfiles(
                                        $newCatId,
                                        'dropbox',
                                        $CloudId,
                                        $folderData['path_lower']
                                    );
                                }
                            }
                        } else {
                            if (isset($dropboxPathCats[$folderData['parent_id']])) {
                                // Get parent id
                                $cat = $thisModel->getOneCatByCloudId($dropboxPathCats[$folderData['parent_id']]->cloud_id);
                                if ($cat) {
                                    // Create new folder if it not have in dropfiles
                                    $newCatId = $thisModel->createOnCategories($folderData['name'], $cat->id, (int) $cat->level + 1);
                                    if ($newCatId) {
                                        $thisModel->createDropboxOnDropfiles(
                                            $newCatId,
                                            'dropbox',
                                            $CloudId,
                                            $folderData['path_lower']
                                        );

                                        $newCatDropfiles           = new stdClass;
                                        $newCatDropfiles->id       = $newCatId;
                                        $newCatDropfiles->type     = 'dropbox';
                                        $newCatDropfiles->cloud_id = $CloudId;
                                        $newCatDropfiles->path     = $folderData['path_lower'];

                                        $dropboxPathCats[$CloudId] = $newCatDropfiles;
                                    }
                                }
                            }
                        }
                    } else {
                        //create Folder New
                        $newCatId = $thisModel->createOnCategories($folderData['name'], 1, 1);
                        if ($newCatId) {
                            $thisModel->createDropboxOnDropfiles(
                                $newCatId,
                                'dropbox',
                                $CloudId,
                                $folderData['path_lower']
                            );

                            $newCatDropfiles           = new stdClass;
                            $newCatDropfiles->id       = $newCatId;
                            $newCatDropfiles->type     = 'dropbox';
                            $newCatDropfiles->cloud_id = $CloudId;
                            $newCatDropfiles->path     = $folderData['path_lower'];

                            $dropboxPathCats[$CloudId] = $newCatDropfiles;
                        }
                    }
                }
            }
            // Update files count
            $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
            $categoriesModel->updateFilesCount();
            JLoader::register(
                'DropfilesComponentHelper',
                JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php'
            );
            $clsDropfilesHelper = new DropfilesComponentHelper();
            $clsDropfilesHelper->setParams(array('dropbox_last_log' => date('Y-m-d H:i:s')));
            echo json_encode(array('status' => true));
            JFactory::getApplication()->close();
        }
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
     * @param boolean $status Reponse status
     * @param array   $datas  Response datas
     *
     * @throws \Exception Throw when application can not start
     * @return void
     *
     * @since 1.0
     */
    private function exitStatus($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        JFactory::getApplication()->close();
    }
}
