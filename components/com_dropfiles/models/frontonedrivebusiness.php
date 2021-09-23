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
 * Class DropfilesModelFrontonedrivebusiness
 */
class DropfilesModelFrontonedrivebusiness extends JModelLegacy
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
    public function getTable($type = 'OnedriveBusiness', $prefix = 'DropfilesTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Save file OneDrive Business to database
     *
     * @param array $data Data
     *
     * @return boolean
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
     * Method get all onedrive business file
     *
     * @param integer $catid    Category id
     * @param string  $ordering Ordering
     * @param string  $dir      Direction
     *
     * @return boolean
     * @since  version
     */
    public function getItems($catid, $ordering = 'ordering', $dir = 'asc')
    {
        $app = JFactory::getApplication();
        $dbo = $this->getDbo();
        $query = 'SELECT file_id as id, ext,title,description,size,state, created_time,modified_time, version,hits,';
        $query .= ' custom_icon FROM #__dropfiles_onedrive_business_files WHERE catid=' . $dbo->quote($catid);

        $nullDate = $dbo->quote($dbo->getNullDate());
        $date = JFactory::getDate();
        $nowDate = $dbo->quote($date->toSql());
        $query .= ' AND (publish = ' . $nullDate . ' OR publish <= ' . $nowDate . ')';
        $query .= ' AND (publish_down = ' . $nullDate . ' OR publish_down >= ' . $nowDate . ')';
        $vName = $app->input->get('view', '');
        if ($vName !== 'files') {
            $query .= ' AND state = 1';
        }
        $query .= ' Order By ' . $dbo->escape($ordering . ' ' . $dir);

        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->loadObjectList();
    }

    /**
     * Increasing hits file
     *
     * @param string $file_id File id
     *
     * @return boolean
     * @since  version
     */
    public function incrHits($file_id)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__dropfiles_onedrive_business_files SET hits=(hits+1) WHERE file_id=' . $dbo->quote($file_id);

        $dbo->setQuery($query);

        if (!$dbo->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Method get file by id
     *
     * @param string $id File id
     *
     * @return boolean
     * @since  version
     */
    public function getFile($id)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('file_id as id, title, state, ordering,ext,size,description,catid,hits,version,
                        canview,created_time,modified_time,publish,publish_down,file_tags,author,custom_icon');
        $query->from('#__dropfiles_onedrive_business_files');

        $query->where('BINARY file_id=' . $dbo->quote($id));
        // Filter by publish dates.
        $nullDate = $dbo->quote($dbo->getNullDate());
        $date = JFactory::getDate();

        $nowDate = $dbo->quote($date->toSql());

        $query->where('(publish = ' . $nullDate . ' OR publish <= ' . $nowDate . ')');
        $query->where('(publish_down = ' . $nullDate . ' OR publish_down >= ' . $nowDate . ')');
        $query->where('state = 1');
        $dbo->setQuery($query);

        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->loadObject();
    }

    /**
     * Method delete file onedrive business
     *
     * @param array $files_del Delete files
     *
     * @return void
     * @since  version
     */
    public function deleteFiles($files_del)
    {
        $dbo = $this->getDbo();
        $filter = JFilterInput::getInstance();
        $files_clean = array();
        foreach ($files_del as $value) {
            $files_clean[] = $dbo->quote($filter->clean($value, 'string'));
        }

        $query = 'DELETE From #__dropfiles_onedrive_business_files WHERE file_id IN (' . implode(',', $files_clean) . ')';
        $dbo->setQuery($query);
        $dbo->execute();
    }

    /**
     * Method get all OneDrive Business Categories
     *
     * @return mixed
     */
    public function getAllOneDriveBusinessCategories()
    {
        $dbo = $this->getDbo();
        $query = 'SELECT d.id,d.type,d.cloud_id, c.title, d.path FROM #__categories as c, #__dropfiles as d';
        $query .= " WHERE c.id=d.id AND c.extension = 'com_dropfiles'  AND d.type = 'onedrivebusiness'";
        $query .= ' Order by c.lft ASC';
        $dbo->setQuery($query);
        $dbo->execute();
        $listCat = $dbo->loadObjectList();

        return $listCat;
    }

    /**
     * Method get all OneDrive Business files in database
     *
     * @return array
     */
    public function getAllOneDriveBusinessFilesInDb()
    {
        $dbo = $this->getDbo();
        $query = 'SELECT f.id, f.file_id, f.catid, f.modified_time FROM #__dropfiles_onedrive_business_files AS f';
        $query .= ' Order by f.catid ASC';
        $dbo->setQuery($query);
        $dbo->execute();
        $listFiles = $dbo->loadObjectList();

        $results = array();
        if ($listFiles) {
            foreach ($listFiles as $f) {
                if (!isset($results[$f->catid])) {
                    $results[$f->catid] = array();
                }
                $results[$f->catid][$f->file_id] = $f;
            }
        }
        return $results;
    }
}
