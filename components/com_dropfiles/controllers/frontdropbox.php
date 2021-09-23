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
 * Class DropfilesControllerFrontdropbox
 */
class DropfilesControllerFrontdropbox extends JControllerLegacy
{

    /**
     * Dropbox sync file
     *
     * @return void
     * @since  version
     */
    public function index()
    {
        $model = $this->getModel();
        $dropboxCats = $model->getAllDropboxCategories();
        $path_dropfilesDropbox = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesDropbox.php';
        JLoader::register('DropfilesDropbox', $path_dropfilesDropbox);
        $dropbox = new DropfilesDropbox();
        if (!$dropbox->checkAuth()) {
            $files_del = array();
            $gFilesInDb = $model->getAllDropboxFilesInDb();

            foreach ($dropboxCats as $dropboxcat) {
                $files = $dropbox->listDropboxFiles($dropboxcat->path);

                $files_new = array();
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $files_new[$file['id']] = $file;
                    }
                }

                if (isset($gFilesInDb[$dropboxcat->cloud_id])) {
                    $files_diff_add = array_diff_key($files_new, $gFilesInDb[$dropboxcat->cloud_id]);
                    $files_diff_del = array_diff_key($gFilesInDb[$dropboxcat->cloud_id], $files_new);
                    $files_update = array_intersect_key($files_new, $gFilesInDb[$dropboxcat->cloud_id]);
                } else {
                    $files_diff_add = $files_new;
                    $files_diff_del = array();
                    $files_update = array();
                }

                if (!empty($files_update)) {
                    foreach ($files_update as $file_id => $file) {
                        $localFileTime = strtotime($gFilesInDb[$dropboxcat->cloud_id][$file_id]->modified_time);
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
                            $data['id'] = $gFilesInDb[$dropboxcat->cloud_id][$file_id]->id;
                            $data['file_id'] = $file['id'];
                            $data['ext'] = JFile::getExt($file['name']);
                            $data['size'] = $file['size'];
                            $data['title'] = $file['name'];
                            $data['catid'] = $dropboxcat->cloud_id;
                            $data['modified_time'] = $file['server_modified'];
                            $model->save($data);
                        }
                    }
                }

                if (!empty($files_diff_add)) {
                    foreach ($files_diff_add as $file_id => $file) {
                        $data = array();
                        $data['id'] = 0;
                        $data['title'] = JFile::stripExt($file['name']);
                        $data['file_id'] = $file['id'];
                        $data['ext'] = strtolower(JFile::getExt($file['name']));
                        $data['size'] = $file['size'];
                        $data['catid'] = $dropboxcat->cloud_id;
                        $data['path'] = $file['path_lower'];
                        $data['created_time'] = date('Y-m-d H:i:s', strtotime($file['client_modified']));
                        $data['modified_time'] = date('Y-m-d H:i:s', strtotime($file['server_modified']));
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
        // Update files count
        $categoriesModel = $this->getModel('Categories', 'DropfilesModel');
        $categoriesModel->updateFilesCount();
        die();
    }

    /**
     * Sync Dropbox files in each category
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
        $dropboxCats = $model->getAllDropboxCategories();

        if (!$isCron) {
            $step = $app->input->getInt('step');
        }

        $path_dropfilesDropbox = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesDropbox.php';
        JLoader::register('DropfilesDropbox', $path_dropfilesDropbox);
        $dropbox = new DropfilesDropbox();
        if (!$dropbox->checkAuth()) {
            $allFilesInDb = $model->getAllDropboxFilesInDb();
            if (!isset($dropboxCats[$step])) {
                if (!$isCron) {
                    echo json_encode(array('continue' => false));
                } else {
                    return false;
                }

                $app->close();
            }

            $dropboxCat = $dropboxCats[$step];
            $files = $dropbox->listDropboxFiles($dropboxCat->path);
            $files_del = array();
            $files_new = array();
            if (!empty($files)) {
                foreach ($files as $file) {
                    $files_new[$file['id']] = $file;
                }
            }

            if (isset($allFilesInDb[$dropboxCat->cloud_id])) {
                $files_diff_add = array_diff_key($files_new, $allFilesInDb[$dropboxCat->cloud_id]);
                $files_diff_del = array_diff_key($allFilesInDb[$dropboxCat->cloud_id], $files_new);
                $files_update = array_intersect_key($files_new, $allFilesInDb[$dropboxCat->cloud_id]);
            } else {
                $files_diff_add = $files_new;
                $files_diff_del = array();
                $files_update = array();
            }

            if (!empty($files_update)) {
                foreach ($files_update as $file_id => $file) {
                    $localFileTime = strtotime($allFilesInDb[$dropboxCat->cloud_id][$file_id]->modified_time);
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
                        $data['id'] = $allFilesInDb[$dropboxCat->cloud_id][$file_id]->id;
                        $data['file_id'] = $file['id'];
                        $data['ext'] = JFile::getExt($file['name']);
                        $data['size'] = $file['size'];
                        $data['title'] = $file['name'];
                        $data['catid'] = $dropboxCat->cloud_id;
                        $data['modified_time'] = $file['server_modified'];
                        $model->save($data);
                    }
                }
            }

            if (!empty($files_diff_add)) {
                foreach ($files_diff_add as $file_id => $file) {
                    $data = array();
                    $data['id'] = 0;
                    $data['title'] = JFile::stripExt($file['name']);
                    $data['file_id'] = $file['id'];
                    $data['ext'] = strtolower(JFile::getExt($file['name']));
                    $data['size'] = $file['size'];
                    $data['catid'] = $dropboxCat->cloud_id;
                    $data['path'] = $file['path_lower'];
                    $data['created_time'] = date('Y-m-d H:i:s', strtotime($file['client_modified']));
                    $data['modified_time'] = date('Y-m-d H:i:s', strtotime($file['server_modified']));
                    $data['author'] = ''; // sync
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
        if (isset($dropboxCats[$step])) {
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
     * Get model front dropbox
     *
     * @param string $name   Model name
     * @param string $prefix Model prefix
     * @param array  $config Model config
     *
     * @return mixed
     * @since  version
     */
    public function getModel(
        $name = 'frontdropbox',
        $prefix = 'dropfilesModel',
        $config = array('ignore_request' => true)
    ) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
}
