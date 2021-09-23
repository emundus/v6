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
 * Class DropfilesControllerFrontonedrive
 */
class DropfilesControllerFrontonedrive extends JControllerLegacy
{

    /**
     * Sych onedrive files
     *
     * @return void
     * @since  version
     */
    public function index()
    {
        $model = $this->getModel();
        $onedriveCats = $model->getAllOneDriveCategories();
        $path_dropfilesonedrive = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDrive.php';
        JLoader::register('DropfilesOneDrive', $path_dropfilesonedrive);
        $onedrive = new DropfilesOneDrive();
        if ($onedrive->checkAuth()) {
            $files_del = array();
            $gFilesInDb = $model->getAllOneDriveFilesInDb();

            foreach ($onedriveCats as $onedrivecat) {
                $files = $onedrive->listFiles($onedrivecat->cloud_id, $onedrivecat->id);

                $files_new = array();
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $files_new[$file->id] = $file;
                    }
                }

                if (isset($gFilesInDb[$onedrivecat->cloud_id])) {
                    $files_diff_add = array_diff_key($files_new, $gFilesInDb[$onedrivecat->cloud_id]);
                    $files_diff_del = array_diff_key($gFilesInDb[$onedrivecat->cloud_id], $files_new);
                    $files_update = array_intersect_key($files_new, $gFilesInDb[$onedrivecat->cloud_id]);
                } else {
                    $files_diff_add = $files_new;
                    $files_diff_del = array();
                    $files_update = array();
                }

                if (!empty($files_update)) {
                    foreach ($files_update as $file_id => $file) {
                        $localFileTime = strtotime($gFilesInDb[$onedrivecat->cloud_id][$file_id]->modified_time);
                        $need_update = false;
                        if ($localFileTime) {
                            if ($localFileTime < strtotime($file->modified_time)) {
                                $need_update = true;
                            }
                        } else {
                            $need_update = true;
                        }

                        if ($need_update) {
                            $data                  = array();
                            $data['id']            = $gFilesInDb[$onedrivecat->cloud_id][$file_id]->id;
                            $data['file_id']       = $file->id;
                            $data['ext']           = $file->ext;
                            $data['size']          = $file->size;
                            $data['title']         = $file->title;
                            $data['catid']         = $onedrivecat->cloud_id;
                            $data['modified_time'] = $file->modified_time;
                            $model->save($data);
                        }
                    }
                }

                if (!empty($files_diff_add)) {
                    foreach ($files_diff_add as $file_id => $file) {
                        $data                  = array();
                        $data['id']            = 0;
                        $data['title']         = $file->title;
                        $data['file_id']       = $file->id;
                        $data['ext']           = $file->ext;
                        $data['size']          = $file->size;
                        $data['catid']         = $onedrivecat->cloud_id;
                        $data['path']          = '';
                        $data['created_time']  = $file->created_time;
                        $data['modified_time'] = $file->modified_time;
                        $data['author'] = ''; // sync
                        $model->save($data);
                    }
                }

                if (!empty($files_diff_del)) {
                    $files_del = array_merge($files_del, array_keys($files_diff_del));
                }

                if (isset($gFilesInDb[$onedrivecat->cloud_id])) {
                    unset($gFilesInDb[$onedrivecat->cloud_id]);
                }
            }

            // delete files from old categories
            if (!empty($gFilesInDb)) {
                foreach ($gFilesInDb as $lstFiles) {
                    $files_del = array_merge($files_del, array_keys($lstFiles));
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
     * Get model frontonedrive
     *
     * @param string $name   Model name
     * @param string $prefix Model prefix
     * @param array  $config Model config
     *
     * @return mixed
     * @since  version
     */
    public function getModel(
        $name = 'frontonedrive',
        $prefix = 'dropfilesModel',
        $config = array('ignore_request' => true)
    ) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
}
