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
class JcrmModelEmails extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array();
        }
        parent::__construct($config);
    }

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param null $ordering
	 * @param null $direction
	 *
	 * @throws Exception
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
		$db	= $this->getDbo();
		return $db->getQuery(true);
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
	 * @return  JForm/false  the JForm object or false
	 *
	 * @throws Exception
	 */
    public function getFilterForm() {
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
}
