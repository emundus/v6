<?php

/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Emundus records.
 */
class EmundusModelJobs extends JModelList {

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
                'date_time', 'a.date_time',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'user', 'a.user',
                'etablissement', 'a.etablissement',
                'service', 'a.service',
                'intitule_poste', 'a.intitule_poste',
                'domaine', 'a.domaine',
                'nb_poste', 'a.nb_poste',

            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null) {
        // Initialise variables.
        $app = JFactory::getApplication('administrator');

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);

        
		//Filtering etablissement
		$this->setState('filter.etablissement', $app->getUserStateFromRequest($this->context.'.filter.etablissement', 'filter_etablissement', '', 'string'));


        // Load the parameters.
        $params = JComponentHelper::getParams('com_emundus');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.etablissement', 'asc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param	string		$id	A prefix for the store id.
     * @return	string		A store id.
     * @since	1.6
     */
    protected function getStoreId($id = '') {
        // Compile the store id.
        $id.= ':' . $this->getState('filter.search');
        $id.= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
                $this->getState(
                        'list.select', 'DISTINCT a.*'
                )
        );
        $query->from('`#__emundus_emploi_etudiant` AS a');

        
		// Join over the user field 'user'
		$query->select('user.name AS user');
		$query->join('LEFT', '#__users AS user ON user.id = a.user');
		// Join over the foreign key 'etablissement'
		$query->select('#__categories_1753001.title AS categories_title_1753001');
		$query->join('LEFT', '#__categories AS #__categories_1753001 ON #__categories_1753001.id = a.etablissement');

        

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} else if ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.etablissement LIKE '.$search.'  OR  a.service LIKE '.$search.'  OR  a.intitule_poste LIKE '.$search.'  OR  a.domaine LIKE '.$search.' )');
            }
        }

        

		//Filtering etablissement
		$filter_etablissement = $this->state->get("filter.etablissement");
		if ($filter_etablissement) {
			$query->where("a.etablissement = '".$db->escape($filter_etablissement)."'");
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
        $items = parent::getItems();
        
		foreach ($items as $oneItem) {

			if (isset($oneItem->etablissement)) {
				$values = explode(',', $oneItem->etablissement);

				$textValue = array();
				foreach ($values as $value){
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query
							->select($db->quoteName('title'))
							->from('`#__categories`')
							->where($db->quoteName('id') . ' = '. $db->quote($db->escape($value)));
					$db->setQuery($query);
					$results = $db->loadObject();
					if ($results) {
						$textValue[] = $results->title;
					}
				}

			$oneItem->etablissement = !empty($textValue) ? implode(', ', $textValue) : $oneItem->etablissement;

			}
		}
        return $items;
    }

}
