<?php

/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Jcrm records.
 */
class JcrmModelContacts extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'last_name', 'a.last_name',
                'first_name', 'a.first_name',
                'organisation', 'a.organisation',
                'email', 'a.email',
                'phone', 'a.phone',
                'jcard', 'a.jcard',

            );
        }
        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since    1.6
     */
    protected function populateState($ordering = null, $direction = null) {

        // Initialise variables.
        $app = JFactory::getApplication();

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
        $this->setState('list.limit', $limit);

        $limitstart = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $limitstart);

        if ($list = $app->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array')) {
            foreach ($list as $name => $value) {
                // Extra validations
                switch ($name) {
                    case 'fullordering':
                        $orderingParts = explode(' ', $value);

                        if (count($orderingParts) >= 2) {
                            // Latest part will be considered the direction
                            $fullDirection = end($orderingParts);

                            if (in_array(strtoupper($fullDirection), array('ASC', 'DESC', ''))) {
                                $this->setState('list.direction', $fullDirection);
                            }

                            unset($orderingParts[count($orderingParts) - 1]);

                            // The rest will be the ordering
                            $fullOrdering = implode(' ', $orderingParts);

                            if (in_array($fullOrdering, $this->filter_fields)) {
                                $this->setState('list.ordering', $fullOrdering);
                            }
                        } else {
                            $this->setState('list.ordering', $ordering);
                            $this->setState('list.direction', $direction);
                        }
                        break;

                    case 'ordering':
                        if (!in_array($value, $this->filter_fields)) {
                            $value = $ordering;
                        }
                        break;

                    case 'direction':
                        if (!in_array(strtoupper($value), array('ASC', 'DESC', ''))) {
                            $value = $direction;
                        }
                        break;

                    case 'limit':
                        $limit = $value;
                        break;

                    // Just to keep the default case
                    default:
                        break;
                }

                $this->setState('list.' . $name, $value);
            }
        }

        // Receive & set filters
        if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array')) {
            foreach ($filters as $name => $value) {
                $this->setState('filter.' . $name, $value);
            }
        }

        $this->setState('list.ordering', $app->input->get('filter_order'));
        $this->setState('list.direction', $app->input->get('filter_order_Dir'));
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return    JDatabaseQuery
     * @since    1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select($this->getState('list.select', 'DISTINCT a.*'));
        $query->from('`#__jcrm_contacts` AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
        $query->where('a.state = 1');

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');

            }
        }



        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    public function getItems() {
        return parent::getItems();
    }

    /**
     * Overrides the default function to check Date fields format, identified by
     * "_dateformat" suffix, and erases the field if it's not correct.
     */
    protected function loadFormData() {
        $app = JFactory::getApplication();
        $filters = $app->getUserState($this->context . '.filter', array());
        $error_dateformat = false;
        foreach ($filters as $key => $value) {
            if (strpos($key, '_dateformat') && !empty($value) && !$this->isValidDate($value)) {
                $filters[$key] = '';
                $error_dateformat = true;
            }
        }
        if ($error_dateformat) {
            $app->enqueueMessage(JText::_("COM_PRUEBA_SEARCH_FILTER_DATE_FORMAT"), "warning");
            $app->setUserState($this->context . '.filter', $filters);
        }

        return parent::loadFormData();
    }

	/**
	 * Checks if a given date is valid and in an specified format (YYYY-MM-DD)
	 *
	 * @param string Contains the date to be checked
	 *
	 * @return bool
	 */
    private function isValidDate($date) {
        return preg_match("/^(19|20)\d\d[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/", $date) && date_create($date);
    }

	/**
	 * Get the filter form
	 *
	 * @param array $data
	 * @param bool  $loadData
	 *
	 * @return  JForm/false  the JForm object or false
	 *
	 * @throws Exception
	 */
    public function getFilterForm($data = array(), $loadData = true) {
        $form = null;

        // Try to locate the filter form automatically. Example: ContentModelArticles => "filter_articles"
        if (empty($this->filterFormName)) {
            $classNameParts = explode('Model', get_called_class());

            if (count($classNameParts) == 2) {
                $this->filterFormName = 'filter_' . strtolower($classNameParts[1]);
            }
        }

        if (!empty($this->filterFormName)) {
            // Get the form.
            $form = new JForm($this->filterFormName);
            $form->loadFile(dirname(__FILE__) . DS . 'forms' . DS . $this->filterFormName . '.xml');
            $filter_data = JFactory::getApplication()->getUserState($this->context, new stdClass);
            $form->bind($filter_data);
        }

        return $form;
    }

    /**
     * Function to get the active filters
     */
    public function getActiveFilters() {
        $activeFilters = false;

        if (!empty($this->filter_fields)) {
            for ($i = 0; $i < count($this->filter_fields); $i++) {
                $filterName = 'filter.' . $this->filter_fields[$i];

                if (property_exists($this->state, $filterName) && (!empty($this->state->{$filterName}) || is_numeric($this->state->{$filterName}))) {
                    $activeFilters = true;
                }
            }
        }

        return $activeFilters;
    }

    private function getParameterFromRequest($paramName, $default = null, $type = 'string') {
        $variables = explode('.', $paramName);
        $input = JFactory::getApplication()->input;

        $nullFound = false;
        if (count($variables) > 1) {
            $data = $input->get($variables[0], null, 'ARRAY');
        } else {
            $data = $input->get($variables[0], null, $type);
        }
        for ($i = 1; $i < count($variables) && !$nullFound; $i++) {
            if (isset($data[$variables[$i]])) {
                $data = $data[$variables[$i]];
            } else {
                $nullFound = true;
            }
        }

        return ($nullFound) ? $default : JFilterInput::getInstance()->clean($data, $type);
    }

	/**
	 * @param        $id
	 * @param int    $index
	 * @param string $q
	 * @param int    $type
	 *
	 * @return mixed
	 */
    public function getAllContacts($id, $index = 0, $q = "", $type = 0) {
		$dbo = $this->getDbo();
		$query = "select c.id, c.full_name, c.type from #__jcrm_contacts as c";
        if (!is_null($id)) {
            $query .= " join #__jcrm_group_contact as grc on grc.contact_id = c.id where grc.group_id = $id and type = $type";
        }
        if (is_null($id)) {
            $query .= " where type = $type ";
        }
        if (!empty($q)) {
            $query .= " and (c.full_name like ".$dbo->quote('%'.$q.'%').") or (c.email like ".$dbo->quote('%'.$q.'%').")  or (c.last_name like ".$dbo->quote('%'.$q.'%').")  or (c.first_name like ".$dbo->quote('%'.$q.'%').") or (c.organisation like ".$dbo->quote('%'.$q.'%').") and type = $type";
        }
        $query .= " order by trim(c.full_name) limit $index, 100";

		try {
			$dbo->setQuery($query);
			return $dbo->loadAssocList();
		} catch(Exception $e) {
			JLog::add('Error in model/contacts at function getAllContacts, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

    /**
     * @param $name
     * @return mixed
     */
    public function getOrgas($name) {
		$dbo = $this->getDbo();
		$query = "select id, organisation from #__jcrm_contacts as c where c.type = 1 and c.organisation like '%".trim(addslashes($name))."%' limit 0, 100";
		try {
			$dbo->setQuery($query);
			return $dbo->loadAssocList();
		} catch(JDatabaseException $e) {
			JLog::add('Error in model/contacts at function getOrgas, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

    /**
     * @return mixed
     * @throws Exception
     */
    public function getGroups() {
		$dbo = $this->getDbo();
		$query = "select * from #__jcrm_groups order by name";
		try {
			$dbo->setQuery($query);
			return $dbo->loadAssocList();
		} catch(JException $e) {
			JLog::add('Error in model/contacts at function getGroups, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

	/**
	 * @param $id
	 * @param $type
	 *
	 * @return mixed
	 */
    public function getNbContacts($id, $type) {
        $dbo = $this->getDbo();
        if (!is_null($id)) {
        	$query = "select count(*) from #__jcrm_contacts as c join #__jcrm_group_contact as grc on grc.contact_id = c.id where grc.group_id = $id and type = $type";
        } else {
        	$query = "select count(*) from #__jcrm_contacts where type = $type";
        }

        try {
            $dbo->setQuery($query);
            return $dbo->loadResult();
        } catch(Exception $e) {
            JLog::add('Error in model/contacts at function getNbContacts, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
        }
    }
}
