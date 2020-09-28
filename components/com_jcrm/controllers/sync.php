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
class JcrmControllerSync extends JcrmController {

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
        $m_sync = $this->getModel('Sync', 'JcrmModel');

        // Check out the item
        if ($editId) {
            $m_sync->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId && $previousId !== $editId) {
            $m_sync->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_jcrm&view=syncform&layout=edit', false));
    }

	/**
	 * Method to save a user's profile data.
	 *
	 * @return void
	 * @throws Exception
	 * @since    1.6
	 */
    public function publish() {
        // Initialise variables.
        $app = JFactory::getApplication();

        //Checking if the user can remove object
        $user = JFactory::getUser();
        if ($user->authorise('core.edit', 'com_jcrm') || $user->authorise('core.edit.state', 'com_jcrm')) {
            $m_sync = $this->getModel('Sync', 'JcrmModel');

            // Get the user data.
            $id = $app->input->getInt('id');
            $state = $app->input->getInt('state');

            // Attempt to save the data.
            $return = $m_sync->publish($id, $state);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(JText::sprintf('Save failed: %s', $m_sync->getError()), 'warning');
            }

            // Clear the profile id from the session.
            $app->setUserState('com_jcrm.edit.sync.id', null);

            // Flush the data from the session.
            $app->setUserState('com_jcrm.edit.sync.data', null);

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
            $m_sync = $this->getModel('Sync', 'JcrmModel');

            // Get the user data.
            $id = $app->input->getInt('id', 0);

            // Attempt to save the data.
            $return = $m_sync->delete($id);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(JText::sprintf('Delete failed', $m_sync->getError()), 'warning');
            } else {
                // Check in the profile.
                if ($return) {
                    $m_sync->checkin($return);
                }

                // Clear the profile id from the session.
                $app->setUserState('com_jcrm.edit.sync.id', null);

                // Flush the data from the session.
                $app->setUserState('com_jcrm.edit.sync.data', null);

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

}
