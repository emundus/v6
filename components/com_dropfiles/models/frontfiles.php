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

jimport('joomla.access.access');
jimport('joomla.application.component.modellist');


/**
 * Class DropfilesModelFrontfiles
 */
class DropfilesModelFrontfiles extends JModelList
{


    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     *
     * @see   JController
     * @since 1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'ordering',
                'a.ordering'//,
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param null|string $ordering  Ordering
     * @param null|string $direction Direction
     *
     * @return void
     * @since  version
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();

        // List state information
        $this->setState('list.limit', 1000);

        $this->setState('list.start', 0);

        $this->setState('list.ordering', 'a.ordering');

        $this->setState('list.direction', 'ASC');

        $this->setState('filter.access', true);

        $catid = $app->input->getInt('id', 0);

        $this->setState('filter.category_id', $catid);
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param string $id An identifier string to generate the store id.
     *
     * @return string  A store id.
     *
     * @since 12.2
     */
    protected function getStoreId($id = '')
    {
        // Add the list state to the store id.
        $id .= ':' . $this->getState('list.start');
        $id .= ':' . $this->getState('list.limit');
        $id .= ':' . $this->getState('list.ordering');
        $id .= ':' . $this->getState('list.direction');
        $id .= ':' . $this->getState('filter.category_id');

        return md5($this->context . ':' . $id);
    }

    /**
     * Get the master query for retrieving a list of filess subject to the model state.
     *
     * @return JDatabaseQuery
     * @since  1.6
     */
    public function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.file, ' . // a.alias, a.title_alias, a.introtext, ' .
                'a.ext, ' .
                'a.size, ' .
                'a.hits, ' .
                'a.description, ' .
                'a.version, ' .
                'a.canview, ' .
                'a.catid, a.created_time,a.modified_time,a.custom_icon'
            )
        );

        $query->from('#__dropfiles_files AS a');

        // Join over the categories.
        $query->select('c.title AS category_title, c.path AS category_route,
                        c.access AS category_access, c.alias AS category_alias');
        $query->join('LEFT', '#__categories AS c ON c.id = a.catid');

        // Join over the categories to get parent category titles
        $query->select('parent.title as parent_title, parent.id as parent_id,
                        parent.path as parent_route, parent.alias as parent_alias');
        $query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

        // Join to check for category published state in parent categories up the tree
//      $query->select('c.published, CASE WHEN badcats.id is null THEN c.published ELSE 0 END AS parents_published');
        $query->select('c.published, c.published AS parents_published');
        $subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
        $subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
        $subquery .= 'WHERE parent.extension = ' . $db->quote('com_dropfiles');

        $query->join('LEFT OUTER', '(' . $subquery . ') AS badcats ON badcats.id = c.id');

        // Filter by access level.
        $access = $this->getState('filter.access');
        if ($access) {
            $user = JFactory::getUser();
            $groups = implode(',', $user->getAuthorisedViewLevels());
//          $query->where('a.access IN ('.$groups.')');
            $query->where('c.access IN (' . $groups . ')');
        }

        // Filter by a single or group of categories
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

            // Add subcategory check
            $includeSubcategories = $this->getState('filter.subcategories', false);
            $categoryEquals = 'a.catid ' . $type . (int)$categoryId;

            if ($includeSubcategories) {
                $levels = (int)$this->getState('filter.max_category_levels', '1');
                // Create a subquery for the subcategory list
                $subQuery = $db->getQuery(true);
                $subQuery->select('sub.id');
                $subQuery->from('#__categories as sub');
                $subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
                $subQuery->where('this.id = ' . (int)$categoryId);
                if ($levels >= 0) {
                    $subQuery->where('sub.level <= this.level + ' . $levels);
                }

                // Add the subquery to the main query
                $query->where('(' . $categoryEquals . ' OR a.catid IN (' . $subQuery->__toString() . '))');
            } else {
                $query->where($categoryEquals);
            }
        } elseif (is_array($categoryId) && (count($categoryId) > 0)) {
            Joomla\Utilities\ArrayHelper::toInteger($categoryId);
            $categoryId = implode(',', $categoryId);
            if (!empty($categoryId)) {
                $type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
                $query->where('a.catid ' . $type . ' (' . $categoryId . ')');
            }
        }
        // Filter by publish dates.
        $nullDate = $db->quote($db->getNullDate());
        $date = JFactory::getDate();

        $nowDate = $db->quote($date->toSql());

        $query->where('(a.publish = ' . $nullDate . ' OR a.publish <= ' . $nowDate . ')');
        $query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
        $query->where('a.state = 1');
        // Filter by language
        if ($this->getState('filter.language')) {
            $query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag())
                . ',' . $db->quote('*') . ')');
        }

        // Add the list ordering clause.
        $allowOrdering = array(
            'ordering',
            'ext',
            'title',
            'description',
            'size',
            'created_time',
            'modified_time',
            'version',
            'hits'
        );

        $ordering = $this->getState('list.ordering', 'a.ordering');
        $orderingDirection = $this->getState('list.direction', 'ASC');

        if (empty($ordering) || !in_array($ordering, $allowOrdering)) {
            $ordering = 'ordering';
        }

        $query->order($ordering . ' ' . $orderingDirection);

        $query->group('a.id, a.title, a.catid, a.state, badcats.id, c.title, c.path, c.access, c.alias, ' .
                      'parent.title, parent.id, parent.path, parent.alias, c.published, c.lft, parent.lft, c.id');

        return $query;
    }

    /**
     * Method to get a list of filess.
     *
     * Overriden to inject convert the attribs field into a JParameter object.
     *
     * @return mixed    An array of objects on success, false on failure.
     * @since  1.6
     */
//    public function getItems()
//    {
//        return parent::getItems();
//    }

    /**
     * Get start
     *
     * @return integer
     * @since  version
     */
    public function getStart()
    {
        return 0;
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
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.file, ' . // a.alias, a.title_alias, a.introtext, ' .
                'a.ext, ' .
                'a.size, ' .
                'a.hits, ' .
                'a.description, ' .
                'a.version, ' .
                'a.canview, ' .
                'a.catid, a.created_time,a.modified_time,a.custom_icon'
            )
        );

        $query->from('#__dropfiles_files AS a');

        // Join over the categories.
        $query->select('c.title AS category_title, c.path AS category_route,
                        c.access AS category_access, c.alias AS category_alias');
        $query->join('LEFT', '#__categories AS c ON c.id = a.catid');

        // Join over the categories to get parent category titles
        $query->select('parent.title as parent_title, parent.id as parent_id,
                        parent.path as parent_route, parent.alias as parent_alias');
        $query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

        // Join to check for category published state in parent categories up the tree
//      $query->select('c.published, CASE WHEN badcats.id is null THEN c.published ELSE 0 END AS parents_published');
        $query->select('c.published, c.published AS parents_published');
        $subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
        $subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
        $subquery .= 'WHERE parent.extension = ' . $db->quote('com_dropfiles');

        $query->join('LEFT OUTER', '(' . $subquery . ') AS badcats ON badcats.id = c.id');

        // Filter by access level.
        $access = $this->getState('filter.access');
        if ($access) {
            $user = JFactory::getUser();
            $groups = implode(',', $user->getAuthorisedViewLevels());
//          $query->where('a.access IN ('.$groups.')');
            $query->where('c.access IN (' . $groups . ')');
        }

        // Filter by a single or group of categories
        $categoryId = $id_category;

        if (is_numeric($categoryId)) {
            $type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

            // Add subcategory check
            $includeSubcategories = $this->getState('filter.subcategories', false);
            $categoryEquals = 'a.catid ' . $type . (int)$categoryId;

            if ($includeSubcategories) {
                $levels = (int)$this->getState('filter.max_category_levels', '1');
                // Create a subquery for the subcategory list
                $subQuery = $db->getQuery(true);
                $subQuery->select('sub.id');
                $subQuery->from('#__categories as sub');
                $subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
                $subQuery->where('this.id = ' . (int)$categoryId);
                if ($levels >= 0) {
                    $subQuery->where('sub.level <= this.level + ' . $levels);
                }

                // Add the subquery to the main query
                $query->where('(' . $categoryEquals . ' OR a.catid IN (' . $subQuery->__toString() . '))');
            } else {
                $query->where($categoryEquals);
            }
        } elseif (is_array($categoryId) && (count($categoryId) > 0)) {
            Joomla\Utilities\ArrayHelper::toInteger($categoryId);
            $categoryId = implode(',', $categoryId);
            if (!empty($categoryId)) {
                $type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
                $query->where('a.catid ' . $type . ' (' . $categoryId . ')');
            }
        }
        // Filter by publish dates.
        $nullDate = $db->quote($db->getNullDate());
        $date = JFactory::getDate();

        $nowDate = $db->quote($date->toSql());

        $query->where('(a.publish = ' . $nullDate . ' OR a.publish <= ' . $nowDate . ')');
        $query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
        $query->where('a.state = 1');
        // Filter by language
        if ($this->getState('filter.language')) {
            $query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag())
                . ',' . $db->quote('*') . ')');
        }

        // Add the list ordering clause.
        $allowOrdering = array(
            'ordering',
            'ext',
            'title',
            'description',
            'size',
            'created_time',
            'modified_time',
            'version',
            'hits'
        );

        $ordering = $this->getState('list.ordering', 'a.ordering');
        $orderingDirection = $this->getState('list.direction', 'ASC');

        if (empty($ordering) || !in_array($ordering, $allowOrdering)) {
            $ordering = 'ordering';
        }

        $query->order($ordering . ' ' . $orderingDirection);

        $query->group('a.id, a.title, a.catid, a.state, badcats.id, c.title, c.path, c.access, c.alias, ' .
            'parent.title, parent.id, parent.path, parent.alias, c.published, c.lft, parent.lft, c.id');

        if (!$db->setQuery($query)) {
            return false;
        }
        if (!$db->execute()) {
            return false;
        }

        return $db->loadObjectList();
    }
}
