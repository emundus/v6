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
 * @copyright Copyright (C) 2013 Damien Barr�re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 * @since     1.6
 */

// no direct access
defined('_JEXEC') || die;

jimport('joomla.application.component.modellist');
jimport('joomla.access.access');


/**
 * Class DropfilesModelFiles
 */
class DropfilesModelFiles extends JModelList
{
    /**
     * Allow ordering
     *
     * @var array
     */
    protected $allowedOrdering = array('ordering',
        'type', 'ext', 'title', 'description', 'created_time', 'modified_time', 'size', 'version', 'hits');


    /**
     * Method to get list files query
     *
     * @return string
     * @since  1.6
     */
    public function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'f.id, f.catid, f.file, f.ordering, f.title, f.description,f.ext as type, f.ext' .
                ', f.hits, f.state, f.version, f.size, f.created_time, f.modified_time, f.author' .
                ', f.language'
            )
        );
        $query->from('#__dropfiles_files AS f');

        // Join over the language
        $query->select('f.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = f.language');

        // Filter by category
        $category = $this->getState('filter.category');
        if ($category) {
            $query->where('f.catid = ' . $db->quote($category));
        }

        // Filter on the language.
        $language = $this->getState('filter.language');
        if ($language) {
            $query->where('f.language = ' . $db->quote($language));
        }

        // Add the list ordering clause.
        if ($this->getState('ordering')) {
            $orderCol = $this->state->get('list.ordering', 'ordering');
            $orderDir = $this->state->get('list.direction', 'asc');
        } else {
            $orderCol = 'ordering';
            $orderDir = 'asc';

            $dbo = $this->getDbo();
            $dbo->setQuery('SELECT params FROM #__dropfiles WHERE id=' . (int)$category);
            $dbo->execute();
            $params = $dbo->loadResult();
            $params = json_decode($params);

            if (isset($params->ordering)) {
                if (in_array($params->ordering, $this->allowedOrdering)) {
                    $orderCol = $this->state->get('list.ordering', $params->ordering);
                } else {
                    $orderCol = 'ordering';
                }
            }

            if (isset($params->orderingdir)) {
                if ($params->orderingdir === 'asc' || $params->orderingdir === 'desc') {
                    $orderDir = $this->state->get('list.direction', $params->orderingdir);
                }
            } else {
                $orderDir = 'asc';
            }
        }
        $this->setState('list.ordering', $orderCol);
        $this->setState('list.direction', $orderDir);

        $query->order($db->escape($orderCol . ' ' . $orderDir));
//      dump(nl2br(str_replace('#__','m7rgh_',$query)));

        return $query;
    }


    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param null|string $ordering  Ordering
     * @param null|string $direction Direction
     *
     * @return void
     * @throws Exception Throw when application can not start
     * @since  1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        $category = $app->input->getInt('id_category', 0);
        $this->setState('filter.category', $category);

        $ordering = JFactory::getApplication()->input->getCmd('orderCol', null);
        if ($ordering !== null) {
            if (!in_array($ordering, $this->allowedOrdering)) {
                $ordering = 'ordering';
            } else {
                $direction = JFactory::getApplication()->input->getCmd('orderDir', 'asc');
                if ($direction !== 'desc') {
                    $direction = 'asc';
                }
                $this->setState('ordering', true);
            }
        }
        parent::populateState($ordering, $direction);

        $this->setState('list.limit', 100000);
    }


    /**
     * Method to add a file into database
     *
     * @param array $data Data
     *
     * @return integer row id, false if an error occurs
     * @since  1.6
     */
    public function addFile($data)
    {
        $dbo = $this->getDbo();
        $date = JFactory::getDate();
        $ordering = $this->getNextPosition($data['id_category']);
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['file_tags'])) {
            $data['file_tags'] = '';
        }
        $created_time  = isset($data['created_time']) ? $data['created_time'] : $date->toSql();
        $modified_time = isset($data['modified_time']) ? $data['modified_time'] : $date->toSql();
        $publish       = isset($data['publish']) ? $data['publish'] : $date->toSql();

        $query = 'INSERT INTO #__dropfiles_files (file,catid,state,ordering,title,description,ext,size,created_time,';
        $query .= ' modified_time,publish,author,file_tags)VALUES (' . $dbo->quote($data['file']) . ',';
        $query .= intval($data['id_category']) . ',1,' . intval($ordering) . ',' . $dbo->quote($data['title']) . ',';
        $query .= $dbo->quote($data['description']) . ',' . $dbo->quote($data['ext']) . ',' . (int)$data['size'];
        $query .= ',' . $dbo->quote($created_time) . ',' . $dbo->quote($modified_time) . ',';
        $query .= $dbo->quote($publish) . ',' . $data['author'] . ',' . $dbo->quote($data['file_tags']) . ')';
        $dbo->setQuery($query);

        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->insertid();
    }

    /**
     * Method to retrieve the next file ordering for a category
     *
     * @param integer $id_category Category id
     *
     * @return integer next ordering
     * @since  1.6
     */
    private function getNextPosition($id_category)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT ordering FROM #__dropfiles_files WHERE catid=' . $dbo->quote($id_category);
        $query .= ' ORDER BY ordering DESC LIMIT 0,1';
        $dbo->setQuery($query);

        if ($dbo->execute() && $dbo->getNumRows() > 0) {
            return $dbo->loadResult() + 1;
        }

        return 0;
    }

    /**
     * Method to retrieve file information
     *
     * @param integer $id_file File id
     *
     * @return object|boolean file, false if an error occurs
     * @since  1.6
     */
    public function getFile($id_file)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT * FROM #__dropfiles_files WHERE id=' . $dbo->quote($id_file);
        $dbo->setQuery($query);

        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->loadObject();
    }

    /**
     * Method to reorder
     *
     * @param array $files Files
     *
     * @return boolean result
     * @since  1.6
     */
    public function reorder($files)
    {
        $dbo = $this->getDbo();
        foreach ($files as $key => $file) {
            $query = 'UPDATE #__dropfiles_files SET ordering = ' . intval($key) . ' WHERE id=' . intval($file);
            $dbo->setQuery($query);

            if (!$dbo->execute()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method to delete a file from the database
     *
     * @param integer $id_file File id
     *
     * @return number of affected rows, false if an error occurs
     * @since  1.6
     */
    public function removePicture($id_file)
    {
        $dbo = $this->getDbo();
        $query = 'DELETE FROM #__dropfiles_files WHERE id=' . $dbo->quote($id_file);
        $dbo->setQuery($query);

        if (!$dbo->execute()) {
//            $dbo->getErrorMsg();
            return false;
        }

        return $dbo->getAffectedRows();
    }

    /**
     * Method to retrieve all files information
     *
     * @return object file, false if an error occurs
     * @since  1.6
     */
    public function getAllPictures()
    {
        $dbo   = $this->getDbo();
        $query = 'SELECT * FROM #__dropfiles_files';
        $dbo->setQuery($query);

        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->loadObjectList();
    }

    /**
     * Method to move category file
     *
     * @param integer $id_file     File id
     * @param integer $id_category Category id
     *
     * @return boolean
     * @since  version
     */
    public function moveCatFile($id_file, $id_category)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__dropfiles_files SET catid=' . $dbo->quote($id_category) . ' WHERE id=' . (int)$id_file;

        if (!$dbo->setQuery($query)) {
            return false;
        }

        if (!$dbo->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Method to update file
     *
     * @param array $data File data
     *
     * @return boolean
     * @since  version
     */
    public function updateFile($data)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__dropfiles_files SET file=' . $dbo->quote($data['file']) . ', ext=';
        $query .= $dbo->quote($data['ext']) . ', size=' . $dbo->quote($data['size']) . ' WHERE id=' . (int)$data['id'];

        if (!$dbo->setQuery($query)) {
            return false;
        }

        if (!$dbo->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Method to add file version
     *
     * @param array $data File data
     *
     * @return boolean
     * @since  version
     */
    public function addVersion($data)
    {
        $dbo   = $this->getDbo();
        $query = 'INSERT INTO #__dropfiles_versions (id_file,file,ext,size,created_time) ';
        $query .= ' VALUES (' . (int) $data['id_file'] . ',' . $dbo->quote($data['file']) . ',';
        $query .= $dbo->quote($data['ext']) . ',' . $dbo->quote($data['size']) . ',';
        $query .= $dbo->quote(date('Y-m-d H:i:s')) . ')';

        if (!$dbo->setQuery($query)) {
            return false;
        }

        if (!$dbo->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Method to get file version
     *
     * @param integer $id_file Version id
     *
     * @return boolean
     * @since  version
     */
    public function getVersions($id_file)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT * FROM #__dropfiles_versions WHERE id_file=' . (int)$id_file . ' ORDER BY created_time DESC';
        if (!$dbo->setQuery($query)) {
            return false;
        }
        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->loadObjectList();
    }

    /**
     * Method to get file info version
     *
     * @param integer $id Version id
     *
     * @return boolean
     * @since  version
     */
    public function getInfoVersion($id)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT * FROM #__dropfiles_versions WHERE id=' . (int)$id . ' ';
        if (!$dbo->setQuery($query)) {
            return false;
        }
        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->loadObject();
    }

    /**
     * Method to delete file old version
     *
     * @param integer $id_file     File id
     * @param integer $id_category Category id
     *
     * @return boolean
     * @since  version
     */
    public function deleteOldestVersion($id_file, $id_category)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT id, file FROM #__dropfiles_versions WHERE id_file=' . (int)$id_file;
        $query .= ' ORDER BY created_time ASC LIMIT 1';
        $dbo->setQuery($query);
        $result = $dbo->loadObject();

        if (!empty($result)) {
            $vid = $result->id;
            $query = 'DELETE FROM #__dropfiles_versions WHERE id =' . (int)$vid;
            if (!$dbo->setQuery($query)) {
                return false;
            }
            if ($dbo->execute() === false) {
                return false;
            }

            $version_dir = DropfilesBase::getVersionPath($id_category);
            if (file_exists($version_dir . $result->file)) {
                unlink($version_dir . $result->file);
            }
        }

        return true;
    }

    /**
     * Get file referent to category
     *
     * @param integer|string $id_category   Category id
     * @param array          $list_id_files List files id
     * @param string         $ordering      Ordering
     * @param string         $ordering_dir  Order direction
     *
     * @return array
     */
    public function getFilesRef($id_category, $list_id_files, $ordering, $ordering_dir)
    {
        $modelCate = JModelLegacy::getInstance('Category', 'dropfilesModel');
        $results = ($this->getListOfCate($id_category)) ? $this->getListOfCate($id_category) : array();
        $files = array();
        foreach ($results as $result) {
            if (!in_array($result->id, $list_id_files)) {
                continue;
            }
            $files[] = $result;
        }

        return  $files;
    }

    /**
     * Get file referent to category
     *
     * @param integer|string $id_category Category id
     *
     * @return object
     */
    public function getListOfCate($id_category)
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'f.id, f.catid, f.file, f.ordering, f.title, f.description,f.ext as type, f.ext' .
                ', f.hits, f.state, f.version, f.size, f.created_time, f.modified_time, f.author' .
                ', f.language'
            )
        );
        $query->from('#__dropfiles_files AS f');

        // Join over the language
        $query->select('f.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = f.language');

        // Filter by category
        $category = $id_category;
        if ($category) {
            $query->where('f.catid = ' . $db->quote($category));
        }

        // Filter on the language.
        $language = $this->getState('filter.language');
        if ($language) {
            $query->where('f.language = ' . $db->quote($language));
        }

        // Add the list ordering clause.
        if ($this->getState('ordering')) {
            $orderCol = $this->state->get('list.ordering', 'ordering');
            $orderDir = $this->state->get('list.direction', 'asc');
        } else {
            $orderCol = 'ordering';
            $orderDir = 'asc';

            $dbo = $this->getDbo();
            $dbo->setQuery('SELECT params FROM #__dropfiles WHERE id=' . (int)$category);
            $dbo->execute();
            $params = $dbo->loadResult();
            $params = json_decode($params);

            if (isset($params->ordering)) {
                if (in_array($params->ordering, $this->allowedOrdering)) {
                    $orderCol = $this->state->get('list.ordering', $params->ordering);
                } else {
                    $orderCol = 'ordering';
                }
            }

            if (isset($params->orderingdir)) {
                if ($params->orderingdir === 'asc' || $params->orderingdir === 'desc') {
                    $orderDir = $this->state->get('list.direction', $params->orderingdir);
                }
            } else {
                $orderDir = 'asc';
            }
        }
        $this->setState('list.ordering', $orderCol);
        $this->setState('list.direction', $orderDir);

        $query->order($db->escape($orderCol . ' ' . $orderDir));

        if (!$db->setQuery($query)) {
            return false;
        }
        if (!$db->execute()) {
            return false;
        }
        return $db->loadObjectList();
    }

    /**
     * Set Multi file
     *
     * @param integer|string $id_file   File id
     * @param string         $mtf_param File param
     *
     * @return boolean
     */
    public function setMultiCategoryFile($id_file, $mtf_param)
    {
        // Create a new query object.
        $dbo    = $this->getDbo();
        $query = 'UPDATE #__dropfiles_files SET file_multi_category=' . $dbo->quote($mtf_param) . ' WHERE id=' . (int)$id_file;
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
