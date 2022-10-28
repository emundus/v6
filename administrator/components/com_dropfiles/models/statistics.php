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
 * @since     1.6
 */

defined('_JEXEC') || die;


/**
 * Class DropfilesModelStatistics
 */
class DropfilesModelStatistics extends JModelList
{
    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     *
     * @since 1.6
     * @see   JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'cattitle', 'c.title',
                'count_hits',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return void
     *
     * @since 1.6
     */
    protected function populateState($ordering = 'a.hits', $direction = 'desc')
    {
        $app = JFactory::getApplication();

        $selection = $this->getUserStateFromRequest($this->context . '.filter.selection', 'selection');
        $this->setState('filter.selection', $selection);

        $selection_value = $this->getUserStateFromRequest(
            $this->context . '.filter.selection_value',
            'selection_value',
            array()
        );
        $this->setState('filter.selection_value', $selection_value);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'query');
        $this->setState('filter.search', $search);

        $date_from = $this->getUserStateFromRequest($this->context . '.filter.from', 'fdate', '');
        $this->setState('filter.from', $date_from);

        $date_to = $this->getUserStateFromRequest($this->context . '.filter.to', 'tdate', '');
        $this->setState('filter.to', $date_to);

        // List state information.
        parent::populateState($ordering, $direction);
        $limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', 5, 'uint');
        $this->setState('list.limit', $limit);
    }


    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param string $id A prefix for the store id.
     *
     * @return string  A store id.
     *
     * @since 1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.selection');
        $id .= ':' . $this->getState('filter.from');
        $id .= ':' . $this->getState('filter.to');
        $id .= ':' . $this->getState('list.ordering');
        $id .= ':' . $this->getState('list.direction');

        return parent::getStoreId($id);
    }

    /**
     * Method to get selection value
     *
     * @return array
     */
    public function getSelectionValues()
    {
        $selection = $this->getState('filter.selection');
        $options = array();
// Get a db connection.
        $db = JFactory::getDbo();
        $params = JComponentHelper::getParams('com_dropfiles');

        if (!empty($selection)) {
            if ($selection === 'category') {
                $query = $db->getQuery(true);
                $query->select('*')
                    ->from('#__categories AS c, #__dropfiles AS d')
                    ->where($db->quoteName('c.id') . ' = ' . $db->quoteName('d.id'));

                $db->setQuery($query);
                $cats = $db->loadObjectList();
                if ($cats) {
                    foreach ($cats as $cat) {
                        $options[$cat->id] = $cat->title;
                    }
                }
            } elseif ($selection === 'files') {
                $query = $db->getQuery(true);
                $query->select('f.*')
                    ->from('#__dropfiles_files AS f');

                $db->setQuery($query);
                $cats = $db->loadObjectList();
                if ($cats) {
                    foreach ($cats as $cat) {
                        $options[$cat->id] = $cat->title;
                    }
                }
            } elseif ($selection === 'users' && (int) $params->get('track_user_download', 0) === 1) {
                $list = (int) $this->getRegularUserDownloadCount();
                if ($list !== 0) {
                    $query = $db->getQuery(true);
                    $query->select('dl.related_users')->from('#__dropfiles_statistics AS dl')->where('dl.related_users !=0');
                    $db->setQuery($query);
                    $users = $db->loadObjectList();
                    $result = array();
                    if (!empty($users)) {
                        foreach ($users as $key => $value) {
                            array_push($result, $value->related_users);
                            $result = array_unique($result);
                        }
                    }
                    $query = 'SELECT u.* FROM #__users AS u WHERE u.id IN ('. implode(',', $result) .')';

                    $db->setQuery($query);
                    $cats = $db->loadObjectList();
                    if ($cats) {
                        foreach ($cats as $cat) {
                            $options[$cat->id] = $cat->name;
                        }
                    }
                }
            }
        }
        return $options;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return JDatabaseQuery
     *
     * @since 1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_dropfiles');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );
        $query->from('#__dropfiles_files AS a');

        // Join over the categories.
        $query->select('c.title AS cattitle')
            ->join('INNER', '#__categories AS c ON c.id = a.catid');

        $query->select('SUM(ch.count) AS count_hits, ch.related_users AS user_download')
            ->join('INNER', '#__dropfiles_statistics AS ch ON ch.related_id = a.id');
        $date_from = $this->getState('filter.from');
        $date_to = $this->getState('filter.to');
        if ($date_from) {
            $query->where('ch.date >= ' . $db->quote($date_from));
        }

        if ($date_to) {
            $query->where('ch.date <= ' . $db->quote($date_to));
        }
        if (empty($date_from) && empty($date_to)) {
            $dfrom = date('Y-m-d', strtotime('-1 month', time()));
            $dto = date('Y-m-d');
            $query->where('ch.date >= ' . $db->quote($dfrom));
            $query->where('ch.date <= ' . $db->quote($dto));
        }
        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where('a.title LIKE ' . $search);
        }
        $selection = $this->getState('filter.selection');
        if (!empty($selection)) {
            $selection_value = $app->input->get('selection_value', array(), 'array');
            if (!empty($selection_value)) {
                $filter = JFilterInput::getInstance();
                $values_clean = array();
                foreach ($selection_value as $value) {
                    $values_clean[] = $filter->clean($value, 'int');
                }
                Joomla\Utilities\ArrayHelper::toInteger($values_clean);
                if ($selection === 'files') {
                    $query->where('a.id IN (' . implode(',', $values_clean) . ')');
                } elseif ($selection === 'category') {
                    $query->where('c.id IN (' . implode(',', $values_clean) . ')');
                } elseif ($selection === 'users' && (int) $params->get('track_user_download', 0) === 1) {
                    $query->where('ch.related_users IN (' . implode(',', $values_clean) . ')');
                    $query->group('ch.related_users');
                }
            }
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', 'a.hits');
        $orderDir = $this->state->get('list.direction', 'desc');

        $query->order($db->escape($orderCol . ' ' . $orderDir));
        $query->group('a.id');

        return $query;
    }

    /**
     * Get count download by date
     *
     * @param array $fids Files id
     *
     * @return array
     * @since  version
     */
    public function getDownloadCountByDate($fids)
    {

        $db = JFactory::getDbo();
        $filter = JFilterInput::getInstance();
        $files_clean = array();
        $app = JFactory::getApplication();
        $users = $app->input->get('selection_value', array(), 'array');
        foreach ($fids as $value) {
            $files_clean[] = $filter->clean($value, 'int');
        }
        Joomla\Utilities\ArrayHelper::toInteger($files_clean);
        $query = $db->getQuery(true);
        if ($this->getState('filter.selection') ==='users' && !empty($users)) {
            Joomla\Utilities\ArrayHelper::toInteger($users);
            $query->select('f.id, ch.date, ch.count')
                ->from('#__dropfiles_files AS f')
                ->join('INNER', ' #__dropfiles_statistics AS ch ON ch.related_id = f.id')
                ->where('f.id IN (' . implode(',', $files_clean) . ') AND ch.related_users IN (' . implode(',', $users) . ')')
                ->order('ch.date');
        } else {
            $query->select('f.id, ch.date, ch.count')
                ->from('#__dropfiles_files AS f')
                ->join('INNER', ' #__dropfiles_statistics AS ch ON ch.related_id = f.id')
                ->where('f.id IN (' . implode(',', $files_clean) . ')')
                ->order('ch.date');
        }

        $db->setQuery($query);

        $results = $db->loadObjectList();

        $rows = array();
        if (count($results)) {
            foreach ($results as $key => $result) {
                if (!isset($rows[$result->date][$result->id])) {
                    $rows[$result->date][$result->id] = $result->count;
                } else {
                    $rows[$result->date][$result->id] += $result->count;
                }
            }
        }

        return $rows;
    }

    /**
     * Get All download count
     *
     * @return mixed
     *
     * @since version
     */
    public function getAllDownloadCount()
    {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select('s.date, SUM(s.count) as count')
            ->from('#__dropfiles_statistics AS s')
            ->group('s.date');

        $db->setQuery($query);
        $results = $db->loadObjectList();

        return $results;
    }

    /**
     * Get User download
     *
     * @param integer|string $id User id
     *
     * @return object
     *
     * @since version
     */
    public function getUserDownload($id)
    {
        $dbo = JFactory::getDbo();
        $query = 'SELECT u.name FROM #__users AS u WHERE u.id='. (int)$id;
        $dbo->setQuery($query);
        $result = $dbo->loadObject();

        return $result;
    }

    /**
     * Get the number of files downloaded by regular users
     *
     * @return object List
     *
     * @since version
     */
    public function getRegularUserDownloadCount()
    {
        $dbo = JFactory::getDbo();
        $query = 'SELECT Count(*) AS total FROM #__dropfiles_statistics AS dl WHERE dl.related_users !=0';
        $dbo->setQuery($query);
        $result = $dbo->loadObject();

        return $result->total;
    }
}
