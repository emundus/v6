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
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'jcrm.php');
/**
 * Jcrm model.
 */
class JcrmModelContact extends JModelItem {

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

    public function getTable($type = 'Contact', $prefix = 'JcrmTable', $config = array()) {
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
        $id = (!empty($id)) ? $id : (int) $this->getState('contact.id');

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
        $id = (!empty($id)) ? $id : (int) $this->getState('contact.id');

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


	/**
	 * @param $contact
	 *
	 * @return mixed|null
	 */
	public function addContact($contact) {
		$dbo = $this->getDbo();

		if (!isset($contact->organisation) || empty($contact->organisation)) {
			$contact->organisation = Null;
		}

		$jcard = JcrmFrontendHelper::buildJCard($contact);

		if (!is_null($contact->organisation) && $contact->type == 0) {
			$org = $this->getOrganisationByName($contact->organisation);
			if (is_null($org)) {
				$newOrg = new stdClass();
				$newOrg->last_name="";
				$newOrg->first_name="";
				$newOrg->type=1;
				$newOrg->organisation = $contact->organisation;
				$org = $this->addContact($newOrg);
			}
		}

		$email = (isset($jcard['email']))?$jcard['email'][0]->uri:null;
		$phone = (isset($jcard['phone']))?$jcard['phone'][0]->tel:null;
		$query = "insert into #__jcrm_contacts (`state`, `checked_out`, `created_by`,  `last_name`, `first_name`, `organisation`, `email`, `phone`, `jcard`, `type`, `full_name`) values (1, ".JFactory::getUser()->id.",".JFactory::getUser()->id.",'".addslashes($contact->last_name)."','".addslashes($contact->first_name)."','".addslashes($contact->organisation)."','".$email."','".$phone."','". addcslashes(json_encode((object)$jcard), "\\'")."', ".$contact->type.", '".addslashes($jcard['fn'])."')";

		try {
			$dbo->setQuery($query);
			$dbo->execute();
			$res = $dbo->insertid();

			if (isset($org)) {
				$query = "insert into #__jcrm_contact_orga (`contact_id`, `org_id`) values ($res, ".$org['id'].")";
				$dbo->setQuery($query);
				$dbo->execute();
			}

			if (isset($contact->formGroup)) {
				$this->addUserToGroups($res, $contact->formGroup);
			}

			return $this->getContact($res);
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function addContact, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
			return null;
		}
	}


	/**
	 * @param $id
	 *
	 * @return mixed|null
	 */
	public function getContact($id) {
		$dbo = $this->getDbo();
		$query = "select * from #__jcrm_contacts where id = ".intval($id);

		try {
			$dbo->setQuery($query);
			$res = $dbo->loadAssoc();
			if ($res['type'] == 1) {
				$res['contacts'] = $this->getContactByOrg($res['id']);
			}
			$res['groups'] = $this->getGroupByContact($id);
			foreach ($res['groups'] as $key => $group) {
				$res['groups'][$key]->name = JText::_($group->name);
			}
			$res['formGroup'] = $this->getGroupByContact($id, true);
			return $res;
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function getContact, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

	public function getOrganisationByName($name) {
		$dbo = $this->getDbo();
		$query = "select id, organisation from #__jcrm_contacts where organisation LIKE ".$dbo->Quote($name)." and type = 1";
		try {
			$dbo->setQuery($query);
			return $dbo->loadAssoc();
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function getOrganisationByName, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

	public function update($contact) {
		$dbo = $this->getDbo();
		if (!isset($contact->organisation) || empty($contact->organisation)) {
			$contact->organisation = null;
		}

		try  {
			$jcard = JcrmFrontendHelper::buildJCard($contact);
			$email = (isset($jcard['email']))?$jcard['email'][0]->uri:null;
			$phone = (isset($jcard['phone']))?$jcard['phone'][0]->tel:null;

			if ($contact->type == 0) {
				if (!is_null($contact->organisation)) {
					$org = $this->getOrganisationByName($contact->organisation);
					if (is_null($org)) {
						$newOrg = new stdClass();
						$newOrg->last_name="";
						$newOrg->first_name="";
						$newOrg->type=1;
						$newOrg->organisation = $contact->organisation;
						$org = $this->addContact($newOrg);
					}
				}
				$query = "update #__jcrm_contacts set `checked_out` = ".JFactory::getUser()->id.", `checked_out_time` = '".date('Y-m-d H:i:s')."', `last_name` = '" .addslashes($contact->last_name)."', `first_name` = '". addslashes($contact->first_name)."', `full_name`='".addslashes($jcard['fn'])."', `type` =".$contact->type.", `jcard`='".addcslashes(json_encode((object)$jcard), "\\'")."', `organisation` = '".addslashes($contact->organisation)."'";
				if (isset($org)) {
					$updateJointureQuery = "insert into #__jcrm_contact_orga (`contact_id`, `org_id`) values ($contact->id, ".$org['id'].")";
					$delJointureQuery = "delete from #__jcrm_contact_orga where contact_id = $contact->id";
					$dbo->setQuery($delJointureQuery);
					$dbo->execute();
					$dbo->setQuery($updateJointureQuery);
					$dbo->execute();
				}
			} else {
				$query = "update #__jcrm_contacts set `checked_out` = ".JFactory::getUser()->id.", `checked_out_time` = '".date('Y-m-d H:i:s')."', `full_name`='".addslashes($jcard['fn'])."', `type` =".$contact->type.", `jcard`='".addcslashes(json_encode((object)$jcard), "\\'")."', `organisation` = '".addslashes($contact->organisation)."'";
				$this->_updateContactJcardByOrg($contact);
			}

			if (!is_null($email)) {
				$query .= ", `email` = '".$email."'";
			}

			if (!is_null($phone)) {
				$query .= ", `phone` = '".$phone."'";
			}

			$query .= " where `id` = ".$contact->id;
			if (isset($contact->formGroup)) {
				$this->addUserToGroups($contact->id, $contact->formGroup);
			}
			$dbo->setQuery($query);
			$dbo->execute();

			return $this->getContact($contact->id);
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function update, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

	public function getContactByOrg($id) {
		$dbo = $this->getDbo();
		try {
		    $query = "SELECT contact.*
		    FROM #__jcrm_contacts as contact
		    LEFT JOIN #__jcrm_contact_orga as orga on orga.contact_id = contact.id
		    WHERE orga.org_id =" . $id;
			$dbo->setQuery($query);
			return $dbo->loadObjectList();
		} catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
		}
	}

	private function _updateContactJcardByOrg($orga) {
		$db = $this->getDbo();
		try {
			$contacts = $this->getContactByOrg($orga->id);
			foreach ($contacts as $contact) {
				$jcard = json_decode($contact->jcard);
				$jcard->org = $orga->organisation;
				$contact->organisation = $orga->organisation;
				$contact->jcard = json_encode($jcard);
				$query = "update #__jcrm_contacts set `organisation` = ".$db->Quote($orga->organisation);
				if (isset($contact->jcard)) {
					$query .= ", `jcard` = '".json_encode($jcard)."'";
				}
				$query .= " where `id` = ".$contact->id;
				$db->setQuery($query);
				$db->execute();
			}
		} catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
		}
	}

	public function addGroup($group) {
		$dbo = $this->getDbo();

		$query = "insert into #__jcrm_groups (`name`, `created_by`) VALUES (".$dbo->Quote($group->name).", ".JFactory::getUser()->id.")";

		try {
			$dbo->setQuery($query);
			$dbo->execute();
			return $dbo->insertid();
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function addGroup, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

	public function updateGroup($group) {
		$dbo = $this->getDbo();
		try {
			$query = "update #__jcrm_groups set name=".$dbo->Quote($group->name)." where id = ".$group->id;
			$dbo->setQuery($query);
			return $dbo->execute();
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function updateGroup, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

	public function deleteGroup($id) {
		$dbo = $this->getDbo();
		try {
			$query = "delete from #__jcrm_groups where id = $id";
			$dbo->setQuery($query);
			return $dbo->execute();
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function deleteGroup, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

	public function deleteContact($contact) {
		$dbo = $this->getDbo();
		try {
			$query = "delete from #__jcrm_contacts where id = $contact->id";
			if ($contact->type == 1) {
				$contact->organisation = "";
				$this->_updateContactJcardByOrg($contact);
			}
			$dbo->setQuery($query);
			return $dbo->execute();
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function deleteContact, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

	public function getGroupByContact($cid, $idOnly = false) {
		$dbo = $this->getDbo();
		try {
			if (!$idOnly) {
				$query = "select distinct gr.name, gr.id from #__jcrm_groups as gr join #__jcrm_group_contact as grc on grc.group_id = gr.id where grc.contact_id =" . $cid;
				$dbo->setQuery($query);
				return $dbo->loadObjectList();
			} else {
				$query = "select  gr.id from #__jcrm_groups as gr join #__jcrm_group_contact as grc on grc.group_id = gr.id where grc.contact_id =" . $cid;
				$dbo->setQuery($query);
				return $dbo->loadColumn();
			}
		} catch(Exception $e) {
			error_log($e->getMessage(), 0);
			return false;
		}
	}


	/**
	 * @param $id
	 * @param $groups
	 *
	 * @return bool|mixed
	 */
	public function addUserToGroups($id, $groups) {

		if (empty($groups)) {
			return false;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		try {
			$query->delete($db->quoteName('#__jcrm_group_contact'))
				->where($db->quoteName('contact_id').' = '.$id);
			$db->setQuery($query);
			$db->execute();

			$query->clear()
				->insert($db->quoteName('#__jcrm_group_contact'))
				->columns([$db->quoteName('group_id'), $db->quoteName('contact_id')]);

			foreach ($groups as $group) {
				$query->values($group.', '.$id);
			}

			$db->setQuery($query);
			return $db->execute();

		} catch(Exception $e) {
			error_log($e->getMessage(), 0);
			return false;
		}
	}

	public function getContactIdByGroup($ids) {
		$dbo = $this->getDbo();
		$query = "select c.id from #__jcrm_contacts as c
                  join #__jcrm_group_contact as grc on grc.contact_id = c.id where grc.group_id in  (".implode(', ', $ids).")
                  order by c.id";
		try {
			$dbo->setQuery($query);
			return $dbo->loadColumn();
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function getContactIdByGroup, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}

	/**
	 * @param $id
	 *
	 * @return array
	 */
	public function getGroupsByContact($id) {

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('group_id'))
			->from($db->quoteName('#__jcrm_group_contact'))
			->where($db->quoteName('contact_id').' = '.$db->quote($id));
		try {
			$db->setQuery($query);
			return $db->loadColumn();
		} catch(Exception $e) {
			JLog::add('Error in model/contacts at function getGroups, QUERY: '.str_replace('\n', ' ', $query->__toString()), JLog::ERROR, 'com_jcrm');
			return [];
		}
	}

	/**
	 * Gets JCRM groups based on Label or ID.
	 *
	 * @param String $label
	 *
	 * @return mixed|null
	 */
	public function createOrSelectGroup($label) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__jcrm_groups'))
			->where($db->quoteName('name').' LIKE '.$db->quote($label));

		try {
			$db->setQuery($query);
			$gid = $db->loadColumn();
		} catch (Exception $e) {
			JLog::add('Error in model/contacts at function getGroupByLabel, QUERY: '.str_replace('\n', ' ', $query->__toString()), JLog::ERROR, 'com_jcrm');
			return null;
		}

		if (empty($gid)) {
			$query->clear()
				->insert($db->quoteName('#__jcrm_groups'))
				->columns($db->quoteName('name'))
				->values($db->quote($label));
			try {
				$db->setQuery($query);
				$db->execute();
				$gid = [$db->insertid()];
			} catch (Exception $e) {
				JLog::add('Error in model/contacts at function getGroupByLabel, QUERY: '.str_replace('\n', ' ', $query->__toString()), JLog::ERROR, 'com_jcrm');
				return null;
			}
		}

		return $gid;
	}

	public function getContactIdByOrg($ids) {

		$db = JFactory::getDbo();

		// First, let's get all of the institutions in our contact list
		$query = 'SELECT id FROM #__jcrm_contacts
					WHERE type=1
					AND id IN ('.implode(', ', $ids).')';
		$db->setquery($query);

		try {
			$orgIds = $db->loadColumn();
		} catch (Exception $e) {
			error_log($e->getMessage(), 0);
			return $e->getMessage();
		}

		if (sizeof($orgIds) > 0) {

			// Now that we have a list of all organizations, we can get all of the users attached to them.
			$query = 'SELECT c.id
						FROM #__jcrm_contacts as c
						LEFT JOIN #__jcrm_contact_orga as co ON c.id = co.contact_id
						WHERE co.org_id IN ('.implode(', ', $orgIds).") AND (c.email NOT LIKE '')";
			$db->setQuery($query);

			try {
				return $db->loadColumn();
			} catch (Exception $e) {
				error_log($e->getMessage(), 0);
				return $e->getMessage();
			}
		}
	}

	public function getContacts($ids) {
		$dbo = $this->getDbo();
		$query = "select * from #__jcrm_contacts ";
		if (!empty($ids)) {
			$query .= "where `id` in  (".implode(', ', $ids).")";
		}
		$query .= " order by `full_name`";
		try {
			$dbo->setQuery($query);
			return $dbo->loadAssocList();
		} catch(Exception $e) {
			JLog::add('Error in model/contact at function getContacts, QUERY: '.$query, JLog::ERROR, 'com_jcrm');
		}
	}
}
