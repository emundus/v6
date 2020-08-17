<?php

/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

/**
 * Jcrm model.
 */
class JcrmModelSync extends JModelItem {

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState() {
        $app = JFactory::getApplication('com_jcrm');

        // Load state from the request userState on edit or from the passed variable on default
        if (JFactory::getApplication()->input->get('layout') == 'edit') {
            $id = JFactory::getApplication()->getUserState('com_jcrm.edit.sync.id');
        } else {
            $id = JFactory::getApplication()->input->get('id');
            JFactory::getApplication()->setUserState('com_jcrm.edit.sync.id', $id);
        }
        $this->setState('sync.id', $id);

        // Load the parameters.
        $params = $app->getParams();
        $params_array = $params->toArray();
        if (isset($params_array['item_id'])) {
            $this->setState('sync.id', $params_array['item_id']);
        }
        $this->setState('params', $params);
    }

	/**
	 * Method to get an ojbect.
	 *
	 * @param integer    The id of the object to get.
	 *
	 * @return    mixed    Object on success, false on failure.
	 * @throws Exception
	 */
    public function &getData($id = null) {
        if ($this->_item === null) {
            $this->_item = false;

            if (empty($id)) {
                $id = $this->getState('sync.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            if ($table->load($id)) {
                // Check published state.
	            $published = $this->getState('filter.published');
                if ($table->state != $published) {
                    return $this->_item;
                }

                // Convert the JTable to a clean JObject.
                $properties = $table->getProperties(1);
                $this->_item = JArrayHelper::toObject($properties, 'JObject');
            } elseif ($error = $table->getError()) {
                $this->setError($error);
            }
        }

		if (isset($this->_item->created_by)) {
			$this->_item->created_by_name = JFactory::getUser($this->_item->created_by)->name;
		}

        return $this->_item;
    }

    public function getTable($type = 'Sync', $prefix = 'JcrmTable', $config = array()) {
        $this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
        return JTable::getInstance($type, $prefix, $config);
    }

	/**
	 * Method to check in an item.
	 *
	 * @param integer        The id of the row to check out.
	 *
	 * @return    boolean        True on success, false on failure.
	 * @throws Exception
	 * @since    1.6
	 */
    public function checkin($id = null) {
        // Get the id.
        $id = (!empty($id)) ? $id : (int) $this->getState('sync.id');

        if ($id) {

            // Initialise the table
            $table = $this->getTable();

            // Attempt to check the row in.
            if (method_exists($table, 'checkin') && !$table->checkin($id)) {
                $this->setError($table->getError());
                return false;
            }
        }

        return true;
    }

	/**
	 * Method to check out an item for editing.
	 *
	 * @param integer        The id of the row to check out.
	 *
	 * @return    boolean        True on success, false on failure.
	 * @throws Exception
	 * @since    1.6
	 */
    public function checkout($id = null) {
        // Get the user id.
        $id = (!empty($id)) ? $id : (int) $this->getState('sync.id');

        if ($id) {

            // Initialise the table
            $table = $this->getTable();

            // Get the current user object.
            $user = JFactory::getUser();

            // Attempt to check the row out.
            if (method_exists($table, 'checkout') && !$table->checkout($user->get('id'), $id)) {
                $this->setError($table->getError());
                return false;
            }
        }

        return true;
    }

    public function getCategoryName($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('title')
                ->from('#__categories')
                ->where('id = ' . $id);
        $db->setQuery($query);
        return $db->loadObject();
    }

    public function publish($id, $state) {
        $table = $this->getTable();
        $table->load($id);
        $table->state = $state;
        return $table->store();
    }

    public function delete($id) {
        $table = $this->getTable();
        return $table->delete($id);
    }

}
