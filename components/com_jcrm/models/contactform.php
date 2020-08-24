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

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Jcrm model.
 */
class JcrmModelContactForm extends JModelForm {

    var $_item = null;

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
            $id = JFactory::getApplication()->getUserState('com_jcrm.edit.contact.id');
        } else {
            $id = JFactory::getApplication()->input->get('id');
            JFactory::getApplication()->setUserState('com_jcrm.edit.contact.id', $id);
        }
		$this->setState('contact.id', $id);

		// Load the parameters.
        $params = $app->getParams();
        $params_array = $params->toArray();
        if (isset($params_array['item_id'])) {
            $this->setState('contact.id', $params_array['item_id']);
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
				$id = $this->getState('contact.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id)) {

                $user = JFactory::getUser();
                $id = $table->id;
                if ($id) {
					$canEdit = $user->authorise('core.edit', 'com_jcrm.contact.'.$id) || $user->authorise('core.create', 'com_jcrm.contact.'.$id);
				} else {
					$canEdit = $user->authorise('core.edit', 'com_jcrm') || $user->authorise('core.create', 'com_jcrm');
				}
                if (!$canEdit && $user->authorise('core.edit.own', 'com_jcrm.contact.'.$id)) {
                    $canEdit = $user->id == $table->created_by;
                }

                if (!$canEdit) {
                    JError::raiseError('500', JText::_('JERROR_ALERTNOAUTHOR'));
                }

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

		return $this->_item;
	}

	public function getTable($type = 'Contact', $prefix = 'JcrmTable', $config = array()) {
        $this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
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
		$id = (!empty($id)) ? $id : (int)$this->getState('contact.id');

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
		$id = (!empty($id)) ? $id : (int)$this->getState('contact.id');

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

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
     *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm|bool	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) {
		// Get the form.
		$form = $this->loadForm('com_jcrm.contact', 'contactform', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 * @throws Exception
	 * @since    1.6
	 */
	protected function loadFormData() {
		$data = JFactory::getApplication()->getUserState('com_jcrm.edit.contact.data', array());
        if (empty($data)) {
            $data = $this->getData();
        }

        return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param array        The form data.
	 *
	 * @return    mixed        The user id on success, false on failure.
	 * @throws Exception
	 * @since    1.6
	 */
	public function save($data) {
		$id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('contact.id');
        $state = (!empty($data['state'])) ? 1 : 0;
        $user = JFactory::getUser();

        if ($id) {
            //Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_jcrm.contact.'.$id) || $authorised = $user->authorise('core.edit.own', 'com_jcrm.contact.'.$id);
            if ($user->authorise('core.edit.state', 'com_jcrm.contact.'.$id) !== true && $state == 1) { //The user cannot edit the state of the item.
                $data['state'] = 0;
            }
        } else {
            //Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_jcrm');
            if ($user->authorise('core.edit.state', 'com_jcrm.contact.'.$id) !== true && $state == 1) { //The user cannot edit the state of the item.
                $data['state'] = 0;
            }
        }

        if ($authorised !== true) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        $table = $this->getTable();
        if ($table->save($data) === true) {
            return $table->id;
        } else {
            return false;
        }

	}

	function delete($data) {
        $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('contact.id');
        if (JFactory::getUser()->authorise('core.delete', 'com_jcrm.contact.'.$id) !== true) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }
        $table = $this->getTable();
        if ($table->delete($data['id']) === true) {
            return $id;
        } else {
            return false;
        }
    }
}