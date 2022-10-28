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

defined('_JEXEC') || die;

jimport('joomla.application.component.modeladmin');

/**
 * Class DropfilesModelFile
 */
class DropfilesModelFile extends JModelAdmin
{
    /**
     * Returns a Table object, always creating it.
     *
     * @param string $type   The table type to instantiate
     * @param string $prefix A prefix for the table class name. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return JTable    A database object
     * @since  version
     */
    public function getTable($type = 'File', $prefix = 'DropfilesTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get form file config
     *
     * @param array   $data     File data
     * @param boolean $loadData Load data
     *
     * @return boolean
     * @since  version
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_dropfiles.file', 'file', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Saving data file
     *
     * @param array $data File data
     *
     * @return boolean
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function save($data)
    {
        $modelC                = $this->getInstance('category', 'DropfilesModel');
        $idcat                 = JFactory::getApplication()->input->getInt('catid', 0);
        $category              = $modelC->getCategory($idcat);
        $user                  = JFactory::getUser();
        $data['author']        = $user->get('id');
        $date                  = JFactory::getDate();
        $data['modified_time'] = $date->toSql();
        if ($category->type === 'googledrive') {
            $modelGoogle = $this->getInstance('Googlefiles', 'DropfilesModel');
            $file        = $modelGoogle->getFile($data['id']);
            if ($file) {
                $data['file_id'] = $file->file_id;
                $data['id']      = $file->id;
            }

            return $modelGoogle->save($data);
        } elseif ($category->type === 'dropbox') {
            $modelDropbox = $this->getInstance('dropboxfiles', 'DropfilesModel');
            $file         = $modelDropbox->getFile($data['id']);
            if ($file) {
                $data['file_id'] = $file->file_id;
                $data['id']      = $file->id;

                $dropbox = new DropfilesDropbox();
                $dropbox->changeFileName($file->file_id, $data['title']);
            }

            return $modelDropbox->save($data);
        } elseif ($category->type === 'onedrive') {
            $modelOnedrive = $this->getInstance('onedrivefiles', 'DropfilesModel');
            $file          = $modelOnedrive->getFile($data['id']);
            if ($file) {
                $data['file_id'] = $file->file_id;
                $data['id']      = $file->id;

                $onedrive = new DropfilesOneDrive();
                $onedrive->changeFileName($file->file_id, $data['title']);
            }

            return $modelOnedrive->save($data);
        } elseif ($category->type === 'onedrivebusiness') {
            $modelOnedriveBusiness = $this->getInstance('onedrivebusinessfiles', 'DropfilesModel');
            $file                  = $modelOnedriveBusiness->getFile($data['id']);
            if ($file) {
                $data['file_id']   = $file->file_id;
                $data['id']        = $file->id;

                $onedrivebusiness  = new DropfilesOneDriveBusiness();
                $onedrivebusiness->saveOnDriveBusinessFileInfos($data);
            }

            return $modelOnedriveBusiness->save($data);
        } else {
            return parent::save($data);
        }
    }

    /**
     * Load data form
     *
     * @return mixed
     * @throws Exception Throw when application can not start
     * @since  version
     */
    protected function loadFormData()
    {
        $app  = JFactory::getApplication();
        $type = $app->input->getCmd('type', 'default');

        if ($type === 'googledrive') {
            //$google = new DropfilesGoogle();
            //$data = $google->getFileInfos(JFactory::getApplication()->input->getString('id'));
            $modelGoogle = $this->getInstance('Googlefiles', 'DropfilesModel');
            $data        = $modelGoogle->getFile($app->input->getString('id'));
        } elseif ($type === 'dropbox') {
            $modelDropbox = $this->getInstance('dropboxfiles', 'DropfilesModel');
            $data         = $modelDropbox->getFile($app->input->getString('id'));
        } elseif ($type === 'onedrive') {
            $modelOnedrive = $this->getInstance('onedrivefiles', 'DropfilesModel');
            $data          = $modelOnedrive->getFile($app->input->getString('id'));
        } elseif ($type === 'onedrivebusiness') {
            $modelOnedriveBusiness = $this->getInstance('onedrivebusinessfiles', 'DropfilesModel');
            $data          = $modelOnedriveBusiness->getFile($app->input->getString('id'));
        } else {
            // Check the session for previously entered form data

            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to get file version
     *
     * @param integer $id Version id
     *
     * @return boolean
     * @since  version
     */
    public function getVersion($id)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT v.*, f.catid, f.title, f.ext FROM #__dropfiles_versions as v ';
        $query .= 'LEFT JOIN #__dropfiles_files as f ON v.id_file = f.id WHERE v.id=' . (int)$id;
        if (!$dbo->setQuery($query)) {
            return false;
        }
        if (!$dbo->execute()) {
            return false;
        }
        return $dbo->loadObject();
    }

    /**
     * Method to delete file version
     *
     * @param integer $id      Version id
     * @param integer $id_file File id
     *
     * @return boolean
     * @since  version
     */
    public function deleteVersion($id, $id_file = null)
    {
        $dbo = $this->getDbo();
        $query = 'DELETE FROM #__dropfiles_versions WHERE id=' . (int)$id;
        if ($id_file !== null) {
            $query .= ' AND id_file=' . (int)$id_file;
        }
        if (!$dbo->setQuery($query)) {
            return false;
        }
        if (!$dbo->execute()) {
            return false;
        }
        return true;
    }
}
