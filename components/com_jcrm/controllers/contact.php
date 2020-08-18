<?php

/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';
require_once(JPATH_COMPONENT.DS.'models'.DS.'contact.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'jcrm.php');

/**
 * Contact controller class.
 */
class JcrmControllerContact extends JcrmController {

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @since	1.6
     */
    public function edit() {
        $app = JFactory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_jcrm.edit.contact.id');
        $editId = JFactory::getApplication()->input->getInt('id', null, 'array');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_jcrm.edit.contact.id', $editId);

        // Get the model.
        $m_contact = $this->getModel('Contact', 'JcrmModel');

        // Check out the item
        if ($editId) {
            $m_contact->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId && $previousId !== $editId) {
            $m_contact->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_jcrm&view=contactform&layout=edit', false));
    }

	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 * @throws Exception
	 * @since    1.6
	 */
    public function publish() {
        // Initialise variables.
        $app = JFactory::getApplication();

        //Checking if the user can remove object
        $user = JFactory::getUser();
        if ($user->authorise('core.edit', 'com_jcrm') || $user->authorise('core.edit.state', 'com_jcrm')) {
            $m_contact = $this->getModel('Contact', 'JcrmModel');

            // Get the user data.
            $id = $app->input->getInt('id');
            $state = $app->input->getInt('state');

            // Attempt to save the data.
            $return = $m_contact->publish($id, $state);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(JText::sprintf('Save failed: %s', $m_contact->getError()), 'warning');
            }

            // Clear the profile id from the session.
            $app->setUserState('com_jcrm.edit.contact.id', null);

            // Flush the data from the session.
            $app->setUserState('com_jcrm.edit.contact.data', null);

            // Redirect to the list screen.
            $this->setMessage(JText::_('COM_JCRM_ITEM_SAVED_SUCCESSFULLY'));
            $menu = JFactory::getApplication()->getMenu();
            $item = $menu->getActive();
            $this->setRedirect(JRoute::_($item->link, false));
        } else {
            throw new Exception(500);
        }
    }

    public function remove() {

        // Initialise variables.
        $app = JFactory::getApplication();

        //Checking if the user can remove object
        $user = JFactory::getUser();
        if ($user->authorise($user->authorise('core.delete', 'com_jcrm'))) {
            $m_contact = $this->getModel('Contact', 'JcrmModel');

            // Get the user data.
            $id = $app->input->getInt('id', 0);

            // Attempt to save the data.
            $return = $m_contact->delete($id);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(JText::sprintf('Delete failed', $m_contact->getError()), 'warning');
            } else {
                // Check in the profile.
                if ($return) {
                    $m_contact->checkin($return);
                }

                // Clear the profile id from the session.
                $app->setUserState('com_jcrm.edit.contact.id', null);

                // Flush the data from the session.
                $app->setUserState('com_jcrm.edit.contact.data', null);

                $this->setMessage(JText::_('COM_JCRM_ITEM_DELETED_SUCCESSFULLY'));
            }

            // Redirect to the list screen.
            $menu = JFactory::getApplication()->getMenu();
            $item = $menu->getActive();
            $this->setRedirect(JRoute::_($item->link, false));
        } else {
            throw new Exception(500);
        }
    }

	public function getcontact() {
		$jinput = JFactory::getApplication()->input;
		$idContact = $jinput->getInt('contact_id', null);
		$model = new JcrmModelContact();

		$contact = $model->getContact($idContact);
		$contact = JcrmFrontendHelper::extractFromJcard($contact);
		if (!is_string($contact)) {
			unset($contact['jcard']);
			echo json_encode((object)($contact));
		} else {
			echo json_encode((object)array('error' => 'JERROR', 'msg' => $contact));
		}
		exit();
	}

	public function addcontact() {
		$request_body = (object) json_decode(file_get_contents('php://input'));
		$m_contact = new JcrmModelContact();
		$contact = $m_contact->addContact($request_body);
		echo json_encode((object)$contact);
		exit();
	}

	public function update() {
		$request_body = (object) json_decode(file_get_contents('php://input'));
		$m_contact = new JcrmModelContact();
		$contact = $m_contact->update($request_body);
		echo json_encode((object)$contact);
		exit();
	}

	public function addgroup() {
		$request_body = (object) json_decode(file_get_contents('php://input'));
		$m_contact = new JcrmModelContact();
		$group = new stdClass();
		$group_id = $m_contact->addGroup($request_body);
		if (!is_string($group_id)) {
			$group->status = true;
			$group->id = $group_id;
		} else {
			$group->status = false;
			$group->error = $group_id;
		}
		echo json_encode($group);
		exit();
	}

	public function updategroup() {
		$request_body = (object) json_decode(file_get_contents('php://input'));
		$m_contact = new JcrmModelContact();
		$contact = $m_contact->updateGroup($request_body);
		echo json_encode((object)$contact);
		exit();
	}

	public function deletegroup() {
		$request_body = (object) json_decode(file_get_contents('php://input'));
		$m_contact = new JcrmModelContact();
		$contact = $m_contact->deleteGroup($request_body->id);
		echo json_encode((object)$contact);
		exit();
	}

	public function deletecontact() {
		$request_body = (object) json_decode(file_get_contents('php://input'));
		$m_contact = new JcrmModelContact();
		$contact = $m_contact->deleteContact($request_body);
		echo json_encode((object)$contact);
		exit();
	}
}
