<?php

/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Thesis controller class.
 */
class EmundusControllerThesis extends EmundusController {

    /**
     * Method to apply to a thesis.
     *
     * @return  void
     * @since   1.6
     */
    public function apply()
    {
        $app = JFactory::getApplication();
        $user = JFactory::getSession()->get('emundusUser');

        // Get the previous edit id (if any) and the current edit id.
        //$previousId = (int) $app->getUserState('com_emundus.edit.thesis.id');
        $thesisId = $app->input->getInt('id', null, 'array');
        $Itemid = $app->input->getInt('Itemid', null, 'int');
        $tmpl = $app->input->get('tmpl', null, 'CMD');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_emundus.apply.thesis.id', $thesisId);

        // Get the model.
        $model = $this->getModel('Thesis', 'EmundusModel');
        $fnum = $model->apply($user->id, $thesisId);
        if ($fnum) {
            if(is_null($tmpl))
            // Redirect to the selected thesis screen.
                $this->setRedirect(JRoute::_('index.php?option=com_emundus&controller=thesis&task=display&fnum='.$fnum.'&id='.$thesisId.'&Itemid='.$Itemid, false));
            else {
                $this->setMessage(JText::_('COM_EMUNDUS_THESIS_APPLIED'), 'message');
                $this->setRedirect(JRoute::_('index.php?option=com_emundus&view=thesis&tmpl=component&fnum='.$fnum.'&id='.$thesisId.'&Itemid='.$Itemid, false));
            }

        } else {
            JError::raiseWarning( 100, JText::_('COM_EMUNDUS_ERROR') );
            $this->setRedirect(JRoute::_('index.php?option=com_emundus&view=thesiss&Itemid='.$Itemid, false));
        }
    }
    /**
     * Method to display application on a Thesis.
     *
     * @return  void
     * @since   1.6
     */
    public function display($cachable = false, $urlparams = false)
    {
        $app = JFactory::getApplication();
        $user = JFactory::getSession()->get('emundusUser');

        $thesisId = $app->input->getInt('id', null, 'array');
        $fnum = $app->input->get('fnum', null, 'ALNUM');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_emundus.display.thesis.id', $thesisId);

        $user->fnum = $fnum;
        $redirect = 'index.php?option=com_fabrik&view=form&formid=233&Itemid=1573';

        $this->setRedirect(JRoute::_('index.php?option=com_emundus&task=openfile&fnum='.$fnum.'&redirect='.base64_encode($redirect), false));
    }

    /**
     * Method to cancel application on a Thesis.
     *
     * @return  void
     * @since   1.6
     */
    public function cancel()
    {
        $app = JFactory::getApplication();
        $user = JFactory::getSession()->get('emundusUser');

        $thesisId = $app->input->getInt('id', null, 'array');
        $fnum = $app->input->get('fnum', null, 'ALNUM');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_emundus.cancel.thesis.id', $thesisId);

        // Get the model.
        $model = $this->getModel('Thesis', 'EmundusModel');

        if ($model->cancel($user->id, $fnum))
            $this->setMessage(JText::_('COM_EMUNDUS_THESIS_DELETED'));
        else
            JError::raiseWarning( 100, JText::_('COM_EMUNDUS_ERROR') );

        $this->setRedirect(JRoute::_('index.php?option=com_emundus&view=thesiss&Itemid=1617', false));
    }
    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @since   1.6
     */
    public function edit() {
        $app = JFactory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_emundus.edit.thesis.id');
        $editId = JFactory::getApplication()->input->getInt('id', null, 'array');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_emundus.edit.thesis.id', $editId);

        // Get the model.
        $model = $this->getModel('Thesis', 'EmundusModel');

        // Check out the item
        if ($editId)
            $model->checkout($editId);

        // Check in the previous user.
        if ($previousId && $previousId !== $editId)
            $model->checkin($previousId);

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_emundus&view=thesisform&layout=edit', false));
    }

    /**
     * Method to save a user's profile data.
     *
     * @return  void
     * @since   1.6
     */
    public function publish() {
        // Initialise variables.
        $app = JFactory::getApplication();

        //Checking if the user can remove object
        $user = JFactory::getSession()->get('emundusUser');
        if ($user->authorise('core.edit', 'com_emundus') || $user->authorise('core.edit.state', 'com_emundus')) {
            $model = $this->getModel('Thesis', 'EmundusModel');

            // Get the user data.
            $id = $app->input->getInt('id', 0);
            $state = $app->input->getInt('state', 1);

            // Attempt to save the data.
            $return = $model->publish($id, $state);

            // Check for errors.
            if ($return === false)
                $this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');

            // Clear the profile id from the session.
            $app->setUserState('com_emundus.edit.thesis.id', null);

            // Flush the data from the session.
            $app->setUserState('com_emundus.edit.thesis.data', null);

            // Redirect to the list screen.
            $this->setMessage(JText::_('COM_EMUNDUS_ITEM_SAVED_SUCCESSFULLY'));
            $menu = & JFactory::getApplication()->getMenu();
            $item = $menu->getActive();
            $this->setRedirect(JRoute::_($item->link, false));
        } else throw new Exception(500);
    }

    public function remove() {

        // Initialise variables.
        $app = JFactory::getApplication();

        //Checking if the user can remove object
        $user = JFactory::getSession()->get('emundusUser');
        if ($user->authorise($user->authorise('core.delete', 'com_emundus'))) {
            $model = $this->getModel('Thesis', 'EmundusModel');

            // Get the user data.
            $id = $app->input->getInt('id', 0);

            // Attempt to save the data.
            $return = $model->delete($id);


            // Check for errors.
            if ($return === false) {
                $this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
            } else {
                // Check in the profile.
                if ($return)
                    $model->checkin($return);

                // Clear the profile id from the session.
                $app->setUserState('com_emundus.edit.thesis.id', null);

                // Flush the data from the session.
                $app->setUserState('com_emundus.edit.thesis.data', null);

                $this->setMessage(JText::_('COM_EMUNDUS_ITEM_DELETED_SUCCESSFULLY'));
            }

            // Redirect to the list screen.
            $menu = & JFactory::getApplication()->getMenu();
            $item = $menu->getActive();
            $this->setRedirect(JRoute::_($item->link, false));
        } else throw new Exception(500);
    }

}
