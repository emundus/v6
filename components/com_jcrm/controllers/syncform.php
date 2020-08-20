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

/**
 * Sync controller class.
 */
class JcrmControllerSyncForm extends JcrmController {

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @since	1.6
     */
    public function edit() {
        $app = JFactory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_jcrm.edit.sync.id');
        $editId = JFactory::getApplication()->input->getInt('id', null, 'array');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_jcrm.edit.sync.id', $editId);

        // Get the model.
        $model = $this->getModel('SyncForm', 'JcrmModel');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_jcrm&view=syncform&layout=edit', false));
    }

	/**
	 * Method to save a user's profile data.
	 *
	 * @return bool
	 * @throws Exception
	 * @since    1.6
	 */
    public function save() {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = JFactory::getApplication();
        $m_syncform = $this->getModel('SyncForm', 'JcrmModel');

        // Get the user data.
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

        // Validate the posted data.
        $form = $m_syncform->getForm();
        if (!$form) {
            JError::raiseError(500, $m_syncform->getError());
            return false;
        }

        // Validate the posted data.
        $data = $m_syncform->validate($form, $data);

        // Check for errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $m_syncform->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            $input = $app->input;
            $jform = $input->get('jform', array(), 'ARRAY');

            // Save the data in the session.
            $app->setUserState('com_jcrm.edit.sync.data', $jform, array());

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_jcrm.edit.sync.id');
            $this->setRedirect(JRoute::_('index.php?option=com_jcrm&view=syncform&layout=edit&id=' . $id, false));
            return false;
        }

        // Attempt to save the data.
        $return = $m_syncform->save($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_jcrm.edit.sync.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_jcrm.edit.sync.id');
            $this->setMessage(JText::sprintf('Save failed', $m_syncform->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_jcrm&view=syncform&layout=edit&id=' . $id, false));
            return false;
        }


        // Check in the profile.
        if ($return) {
            $m_syncform->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_jcrm.edit.sync.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_JCRM_ITEM_SAVED_SUCCESSFULLY'));
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_jcrm&view=syncs' : $item->link);
        $this->setRedirect(JRoute::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_jcrm.edit.sync.data', null);
    }

    function cancel() {
        
        $app = JFactory::getApplication();

        // Get the current edit id.
        $editId = (int) $app->getUserState('com_jcrm.edit.sync.id');

        // Get the model.
        $m_syncform = $this->getModel('SyncForm', 'JcrmModel');

        // Check in the item
        if ($editId) {
            $m_syncform->checkin($editId);
        }
        
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_jcrm&view=syncs' : $item->link);
        $this->setRedirect(JRoute::_($url, false));
    }

    public function remove() {

        // Initialise variables.
        $app = JFactory::getApplication();
        $m_syncform = $this->getModel('SyncForm', 'JcrmModel');

        // Get the user data.
        $data = array();
        $data['id'] = $app->input->getInt('id');

        // Check for errors.
        if (empty($data['id'])) {
            // Get the validation messages.
            $errors = $m_syncform->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState('com_jcrm.edit.sync.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_jcrm.edit.sync.id');
            $this->setRedirect(JRoute::_('index.php?option=com_jcrm&view=sync&layout=edit&id=' . $id, false));
            return false;
        }

        // Attempt to save the data.
        $return = $m_syncform->delete($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_jcrm.edit.sync.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_jcrm.edit.sync.id');
            $this->setMessage(JText::sprintf('Delete failed', $m_syncform->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_jcrm&view=sync&layout=edit&id=' . $id, false));
            return false;
        }


        // Check in the profile.
        if ($return) {
            $m_syncform->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_jcrm.edit.sync.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_JCRM_ITEM_DELETED_SUCCESSFULLY'));
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_jcrm&view=syncs' : $item->link);
        $this->setRedirect(JRoute::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_jcrm.edit.sync.data', null);
    }

}
