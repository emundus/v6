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


/**
 * Class DropfilesModelGooglefiles
 */
class DropfilesModelGooglefiles extends JModelLegacy
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
    public function getTable($type = 'Google', $prefix = 'DropfilesTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Save google data
     *
     * @param array $data Data
     *
     * @return boolean
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function save($data)
    {
        $table = $this->getTable();

        $key = $table->getKeyName();
        $pk = (!empty($data[$key])) ? $data[$key] : (int)$this->getState($this->getName() . '.id');
        $isNew = true;

        // Allow an exception to be thrown.
        try {
            // Load the row if saving an existing record.
            if ($pk > 0) {
                $table->load($pk);
                $isNew = false;
            }

            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());

                return false;
            }

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }


            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (isset($table->$key)) {
            $this->setState($this->getName() . '.id', $table->$key);
        }

        $this->setState($this->getName() . '.new', $isNew);

        return true;
    }

    /**
     * Methode to retrieve file information
     *
     * @param string $id_file Google file id
     *
     * @return object file, false if an error occurs
     * @since  version
     */
    public function getFile($id_file)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT * FROM #__dropfiles_google_files WHERE file_id=' . $dbo->quote($id_file);
        $dbo->setQuery($query);

        if (!$dbo->execute()) {
            return false;
        }
        return $dbo->loadObject();
    }

    /**
     * Method to add files
     *
     * @param array $obj Object
     *
     * @return void
     * @since  version
     */
    public function addFile($obj)
    {
        $data = json_decode(json_encode($obj), true);
        $date = JFactory::getDate();
        $data['created_time']  = $date->toSql();
        $data['modified_time'] = $date->toSql();
        $this->save($data);
    }

    /**
     * Method to reorder files in database
     *
     * @param array $files Filse
     *
     * @return boolean
     * @since  version
     */
    public function reorder($files)
    {
        $dbo = $this->getDbo();
        foreach ($files as $key => $file) {
            $query = 'UPDATE #__dropfiles_google_files SET ordering = ' . intval($key);
            $query .= ' WHERE file_id=' . $dbo->quote($file);
            $dbo->setQuery($query);
            if (!$dbo->execute()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method get google file items
     *
     * @param string $catid    Category id
     * @param string $ordering Ordering
     * @param string $dir      Direction
     *
     * @return boolean
     * @since  version
     */
    public function getItems($catid, $ordering = 'ordering', $dir = 'asc')
    {
        $dbo = $this->getDbo();
        $query = 'SELECT file_id as id, ext,title,description,size,state,created_time,modified_time,version,hits';
        $query .= ' FROM #__dropfiles_google_files WHERE catid=' . $dbo->quote($catid);
        $query .= ' Order By ' . $dbo->escape($ordering . ' ' . $dir);
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }
        return $dbo->loadObjectList();
    }

    /**
     * Method to delete google file
     *
     * @param string $id_file Google file id
     *
     * @return void
     * @since  version
     */
    public function deleteFile($id_file)
    {
        $dbo = $this->getDbo();
        $query = 'DELETE From #__dropfiles_google_files WHERE file_id =' . $dbo->quote($id_file);
        $dbo->setQuery($query);
        $dbo->execute();
    }


    /**
     * Restore google file
     *
     * @param string  $id       Google file id
     * @param integer $filesize File size
     *
     * @return boolean
     * @since  version
     */
    public function restoreVersion($id, $filesize)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__dropfiles_google_files SET size = ' . intval($filesize);
        $query .= ' WHERE file_id=' . $dbo->quote($id);
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }
        return true;
    }

    /**
     * Create new file base on google file object
     *
     * @param Google_Service_Drive_DriveFile $file   Google file object
     * @param string                         $parent Parent id
     *
     * @return boolean
     * @throws Exception Throw when application can not start
     * @since  4.1.5
     */
    public function createFile($file, $parent)
    {
        if (!$file instanceof Google_Service_Drive_DriveFile) {
            return false;
        }

        $data = $this->generateFileData($file, $parent);

        return $this->save($data);
    }

    /**
     * Move file base on google file object
     *
     * @param Google_Service_Drive_DriveFile $file   Google file object
     * @param string                         $parent Parent id
     *
     * @return boolean
     * @throws Exception Throw when application can not start
     * @since  4.1.5
     */
    public function moveFile($file, $parent)
    {
        if (!$file instanceof Google_Service_Drive_DriveFile) {
            return false;
        }

        $localFile = $this->getFile($file->getId());
        $localFile->catid = $parent;
        $dbo = $this->getDbo();

        return $dbo->updateObject('#__dropfiles_google_files', $localFile, 'id');
    }

    /**
     * Update file
     *
     * @param Google_Service_Drive_DriveFile $file   Google File object
     * @param string                         $parent Parent id
     *
     * @return boolean
     * @since  4.1.5
     */
    public function updateFile($file, $parent)
    {
        if (!$file instanceof Google_Service_Drive_DriveFile) {
            return false;
        }
        $data = $this->generateFileData($file, $parent);

        $localFile = $this->getFile($file->getId());

        $diff = array_diff($data, (array) $localFile);

        if (!empty($diff)) {
            foreach ($diff as $key => $value) {
                $localFile->{$key} = $value;
            }
        }

        $dbo = $this->getDbo();
        return $dbo->updateObject('#__dropfiles_google_files', $localFile, 'id');
    }

    /**
     * Generate file data
     *
     * @param Google_Service_Drive_DriveFile $file   Google File object
     * @param string                         $parent Parent id
     *
     * @return array|boolean
     * @since  4.1.5
     */
    private function generateFileData($file, $parent)
    {
        if (!$file instanceof Google_Service_Drive_DriveFile) {
            return false;
        }

        $data                  = array();
        $data['id']            = 0;
        $data['file_id']       = $file->getId();
        $data['ext']           = $file->getFileExtension() ? $file->getFileExtension() : JFile::getExt($file->getOriginalFilename());
        $data['size']          = $file->getSize();
        $data['title']         = $file->getOriginalFilename() ? JFile::stripExt($file->getName()) : $file->getName();
        $data['description']   = $file->getDescription();
        $data['catid']         = $parent;
        $data['created_time']  = date('Y-m-d H:i:s', strtotime($file->getCreatedTime()));
        $data['modified_time'] = date('Y-m-d H:i:s', strtotime($file->getModifiedTime()));
        $properties          = $file->getAppProperties();
        $file_tags = '';
        $version = '';
        $hits = 0;
        if (!empty($properties)) {
            $file_tags = isset($properties->file_tags) ? $properties->file_tags : '';
            $version   = isset($properties->version) ? $properties->version : '';
            $hits      = isset($properties->hits) ? $properties->hits : 0;
        }
        $data['file_tags']     = $file_tags;
        $data['version']       = $version;
        $data['hits']          = $hits;

        return $data;
    }
}
