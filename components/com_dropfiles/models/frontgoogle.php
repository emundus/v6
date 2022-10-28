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
 * Class DropfilesModelFrontgoogle
 */
class DropfilesModelFrontgoogle extends JModelLegacy
{
    /**
     * Returns a Table object, always creating it.
     *
     * @param string $type   The table type to instantiate
     * @param string $prefix A prefix for the table class name. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return JTable    A database object
     */
    public function getTable($type = 'Google', $prefix = 'DropfilesTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method save data files
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
     * Methode to retrieve file information
     *
     * @param integer $id_file File id
     *
     * @return object file, false if an error occurs
     * @since  x
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
     * Method get files
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
        $query = 'SELECT file_id as id, ext,title,description,size,state,created_time,modified_time,';
        $query .= ' version,hits,custom_icon FROM #__dropfiles_google_files WHERE catid=' . $dbo->quote($catid);

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
     * Increasing hits of file
     *
     * @param string $file_id File id
     *
     * @return boolean
     * @since  version
     */
    public function incrHits($file_id)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__dropfiles_google_files SET hits=(hits+1) WHERE file_id=' . $dbo->quote($file_id);

        $dbo->setQuery($query);

        if (!$dbo->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Delete files by array
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
            $files_clean[] = $dbo->escape($filter->clean($value, 'string'));
        }
        $query = "DELETE From #__dropfiles_google_files WHERE file_id IN ('" . implode('\',\'', $files_clean) . "')";
        $dbo->setQuery($query);
        $dbo->execute();
    }

    /**
     *  Get all categories of google drive
     *
     * @param integer $catId     Category ID
     * @param boolean $recursive Recursive get children category
     *
     * @return mixed
     *
     * @since version
     */
    public function getChildrenGoogleCategories($catId, $recursive = true)
    {
        $categories = JCategories::getInstance('dropfiles');
        $cat = $categories->get((int)$catId);
        $listCat = array();
        if ($cat) {
            $idList = array();
            $children = $cat->getChildren($recursive);
            if ($recursive) {
                $idList[] =  $catId;
            }
            if (!empty($children)) {
                foreach ($children as $child) {
                    $idList[] = $child->id;
                }
            }

            if (!empty($idList)) {
                $dbo = $this->getDbo();
                $query = 'SELECT d.type,d.cloud_id, c.id, c.title FROM #__categories as c, #__dropfiles as d';
                $query .= " WHERE c.id=d.id AND c.extension = 'com_dropfiles'  AND d.type = 'googledrive' AND c.id IN (". implode(',', $idList).')';
                $query .= ' Order by c.lft ASC';
                $dbo->setQuery($query);
                $listCat = $dbo->loadObjectList();
            }
        }
        return $listCat;
    }

    /**
     *  Get all categories of google drive
     *
     * @return mixed
     *
     * @since version
     */
    public function getAllGoogleCategories()
    {
        $dbo = $this->getDbo();
        $query = 'SELECT d.type,d.cloud_id, c.id, c.title, c.level FROM #__categories as c, #__dropfiles as d';
        $query .= " WHERE c.published = 1 AND c.id=d.id AND c.extension = 'com_dropfiles'  AND d.type = 'googledrive'";
        $query .= ' Order by c.lft ASC';
        $dbo->setQuery($query);
        $dbo->execute();
        $listCat = $dbo->loadObjectList();

        return $listCat;
    }

    /**
     * Get google category by cloudid
     *
     * @param string $cloudId Cloud id
     *
     * @return boolean|mixed
     * @since  5.2.0
     */
    public function getGoogleCategory($cloudId = '')
    {
        if ($cloudId === '') {
            return false;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('d.type', 'd.cloud_id', 'c.title')))
            ->from($db->quoteName('#__categories', 'c'))
            ->join('LEFT', $db->quoteName('#__dropfiles', 'd') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('d.id') . ')')
            ->where($db->quoteName('c.extension') . ' = ' . $db->quote('com_dropfiles') . ' AND ' . $db->quoteName('d.type') . ' = ' . $db->quote('googledrive') . ' AND ' . $db->quoteName('d.cloud_id') . ' = ' . $db->quote($cloudId))
            ->order('c.lft ASC');

        $db->setQuery($query);
        $cat = $db->loadObject();

        return $cat;
    }

    /**
     *  Get files of google in BD
     *
     * @return array
     *
     * @since version
     */
    public function getAllGoogleFilesInDb()
    {
        $listFiles = $this->getAllGoogleFilesList();

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

    /**
     * Get files of google list
     *
     * @return mixed
     * @since  4.1.5
     */
    public function getAllGoogleFilesList()
    {
        $dbo = $this->getDbo();
        $query = 'SELECT f.id, f.file_id, f.catid, f.modified_time FROM #__dropfiles_google_files AS f';
        $query .= ' Order by f.catid ASC';
        $dbo->setQuery($query);
        $dbo->execute();
        $listFiles = $dbo->loadObjectList();

        return $listFiles;
    }

    /**
     * Get files list by google cloudid
     *
     * @param string $cloudId Cloud id
     *
     * @return mixed|null
     * @since  5.2.0
     */
    public function getFilesListByCloudId($cloudId = '')
    {
        if ($cloudId === '') {
            return null;
        }

        $db   = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('file_id', 'id'))
            ->select($db->quoteName(array('title', 'description', 'ext', 'size', 'created_time', 'modified_time')))
            ->from($db->quoteName('#__dropfiles_google_files'))
            ->where($db->quoteName('catid') . ' = ' . $db->quote($cloudId))
            ->order('catid ASC');
        $db->setQuery($query);

        $listFiles = $db->loadObjectList('id');

        return $listFiles;
    }
}
