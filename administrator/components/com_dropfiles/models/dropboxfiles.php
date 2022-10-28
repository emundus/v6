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
 * Class DropfilesModelDropboxfiles
 */
class DropfilesModelDropboxfiles extends JModelLegacy
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
    public function getTable($type = 'Dropbox', $prefix = 'DropfilesTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to save files dropbox
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

//        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dropfiles/tables');
//
//        $table = JTable::getInstance('Google', 'DropfilesTable');

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
     * Method to retrieve file information
     *
     * @param integer $id_file File id
     *
     * @return object file, false if an error occurs
     * @since  version
     */
    public function getFile($id_file)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT * FROM #__dropfiles_dropbox_files WHERE file_id = BINARY ' . $dbo->quote($id_file);
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }
        return $dbo->loadObject();
    }

    /**
     * Method to reorder files in database
     *
     * @param array $files Files
     *
     * @return boolean
     * @since  version
     */
    public function reorder($files)
    {
        $dbo = $this->getDbo();
        foreach ($files as $key => $file) {
            $query = 'UPDATE #__dropfiles_dropbox_files SET ordering = ' . intval($key);
            $query .= ' WHERE file_id = BINARY ' . $dbo->quote($file);
            $dbo->setQuery($query);
            if (!$dbo->execute()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method to get items dropbox file
     *
     * @param string $catid    Dropbox category id
     * @param string $ordering Ordering
     * @param string $dir      Direction
     *
     * @return mixed
     * @since  version
     */
    public function getItems($catid, $ordering = 'ordering', $dir = 'asc')
    {
        $dbo = $this->getDbo();
        $query = 'SELECT file_id as id, ext,title,description,size,state,created_time,modified_time,version,hits ';
        $query .= ' FROM #__dropfiles_dropbox_files WHERE catid=' . $dbo->quote($catid);
        $query .= ' Order By ' . $dbo->escape($ordering . ' ' . $dir);
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->loadObjectList();
    }

    /**
     * Method to delete dropbox file
     *
     * @param string $id_file File id
     *
     * @return void
     * @since  version
     */
    public function deleteFile($id_file)
    {
        $dbo = $this->getDbo();
        $query = 'DELETE From #__dropfiles_dropbox_files WHERE file_id = BINARY ' . $dbo->quote($id_file);
        $dbo->setQuery($query);
        $dbo->execute();
    }
}
