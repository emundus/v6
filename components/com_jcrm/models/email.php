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
class JcrmModelEmail extends JModelItem {

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
            $id = JFactory::getApplication()->getUserState('com_jcrm.edit.email.id');
        } else {
            $id = JFactory::getApplication()->input->get('id');
            JFactory::getApplication()->setUserState('com_jcrm.edit.email.id', $id);
        }
        $this->setState('email.id', $id);

        // Load the parameters.
        $params = $app->getParams();
        $params_array = $params->toArray();
        if (isset($params_array['item_id'])) {
            $this->setState('email.id', $params_array['item_id']);
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
                $id = $this->getState('email.id');
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

    public function getTable($type = 'Email', $prefix = 'JcrmTable', $config = array()) {
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
        $id = (!empty($id)) ? $id : (int) $this->getState('email.id');

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
        $id = (!empty($id)) ? $id : (int) $this->getState('email.id');

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

    public function getMailSubject() {
        $dbo = $this->getDbo();
        try {
            $query = "select id, subject from #__emundus_setup_emails where type = 3";
            $dbo->setQuery($query);
            return $dbo->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error in model/email at function getMailSubject, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
        }
    }

    public function getMailContact($name) {
        $dbo = $this->getDbo();
        try {
            $query = "select id, full_name, email from #__jcrm_contacts as c where  (c.full_name like '%".addslashes($name)."%') or (c.email like '%".addslashes($name)."%')  or (c.last_name like '%".addslashes($name)."%')  or (c.first_name like '%".addslashes($name)."%') or (c.organisation like '%".addslashes($name)."%') limit 0, 100";
            $dbo->setQuery($query);
            $res['contacts'] = $dbo->loadAssocList();
            $query = "select id, name from #__jcrm_groups as c where  (c.name like '%".addslashes($name)."%') limit 0, 100";
            $dbo->setQuery($query);
            $res['groups'] = $dbo->loadAssocList();
            return $res;
        } catch(JDatabaseException $e) {
            JLog::add('Error in model/email at function getMailContact, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
        }
    }

    public function getMailBody($id) {
        $dbo = $this->getDbo();

        try {
            $query = "select message, subject from #__emundus_setup_emails where `id` = $id";
            $dbo->setQuery($query);
            return $dbo->loadObject();
        } catch (Exception $e) {
            JLog::add('Error in model/email at function getMailBody, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
        }
    }

    public function getEmailFrom($id) {
        $dbo = $this->getDbo();

        try {
            $query = "select `emailfrom`, `name`  from #__emundus_setup_emails where `id` = $id";
            $dbo->setQuery($query);
            $res = $dbo->loadObject();
            if ($res->emailfrom == '[USER_EMAIL]') {
                $res->emailfrom = JFactory::getUser()->email;
            }
            if ($res->name == '[USER_NAME]') {
                $res->name = JFactory::getUser()->name;
            }

            return $res;
        } catch (Exception $e) {
            JLog::add('Error in model/email at function getEmailFrom, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
        }
    }

    public function getEmailAdr($listId, $orgMail = 'direct') {
        $dbo = $this->getDbo();

        if ($orgMail == 'members' || $orgMail == 'both') {

            // First, let's get all of the institutions in our contact list
            $query = 'SELECT id FROM #__jcrm_contacts
                        WHERE type=1
                        AND id IN ('.implode(', ', $listId).')';
            $dbo->setquery($query);

            try {

                $orgIds = $dbo->loadColumn();

            } catch (Exception $e) {
                error_log($e->getMessage(), 0);
                return $e->getMessage();
            }

            if (sizeof($orgIds) > 0) {

                // Now that we have a list of all organizations, we can get all of the users attached to them.
                $query = 'SELECT c.email, c.full_name, c.organisation, c.last_name, c.first_name,c.phone
                            FROM #__jcrm_contacts as c
                            LEFT JOIN #__jcrm_contact_orga as co ON c.id = co.contact_id
                            WHERE co.org_id IN ('.implode(', ', $orgIds).") AND (c.email NOT LIKE '')";
                $dbo->setQuery($query);

                try {
                    $orgContacts = $dbo->loadAssocList();
                } catch(Exception $e) {
                    error_log($e->getMessage(), 0);
                    return $e->getMessage();
                }
            }
        }

        if ($orgMail == 'direct' || $orgMail == 'both') {

            $query = "select email, full_name, organisation, last_name, first_name, phone
                        from #__jcrm_contacts
                        where id in (".implode(', ', $listId).") and (email not like '')";
            $dbo->setQuery($query);

            try {

                $contacts = $dbo->loadAssocList();

            } catch(Exception $e) {
                error_log($e->getMessage(), 0);
                return $e->getMessage();
            }

        }

        if (isset($orgContacts)) {
        	$contacts = array_merge($contacts, $orgContacts);
        }

        return $contacts;
    }

    public function getContacts($list) {
        $dbo = $this->getDbo();
        $query = "select c.id from #__jcrm_contacts as c
                  join #__jcrm_group_contact as grc on grc.contact_id = c.id where grc.group_id in  (".implode(', ', $list).")
                  order by c.id";
        try {
            $dbo->setQuery($query);
            return $dbo->loadColumn();
        } catch(Exception $e) {
            JLog::add('Error in model/email at function getContacts, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
        }
    }

    public function addMessage($to, $s, $m) {
        $dbo = $this->getDbo();
        try {
            $query = "insert (`user_id`, `email_to`, `state`, `priority`, `subject`, `message`) into #__jcrm_messages
                      values(".JFactory::getUser()->id.", '".$to."', 0, 0, ".$dbo->Quote($s).", ".$dbo->Quote($m)." )";
            $dbo->setQuery($query);
            return $dbo->execute();
        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return $e->getMessage();
        }
    }
}
