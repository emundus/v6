<?php
/**
 * @package   gantry
 * @subpackage core
 * @version   4.1.31 April 11, 2016
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Template style controller class.
 *
 * @package        Joomla.Administrator
 * @subpackage    com_templates
 * @since        1.6
 */
class GantryControllerTemplate extends JControllerForm
{
    /**
     * @var        string    The prefix to use with controller messages.
     * @since    1.6
     */
    protected $text_prefix = 'COM_GANTRY';

    /**
     * Proxy for execute.
     *
     * If the task is an action which modifies data, the component cache is cleared.
     *
     * @since    1.6
     */
    public function execute($task)
    {

        switch ($task) {
            case 'cancel':
                $this->cancel();
                break;
            case 'save':
                $this->save();
                break;
            default:
                parent::execute($task);
                break;
        }


        // Clear the component's cache
        if (!in_array($task, array('display', 'edit', 'cancel'))) {
            $cache = JFactory::getCache();
            $cache->clean('com_gantry');
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @param    string    $key    The name of the primary key of the URL variable.
     *
     * @return    Boolean    True if access level checks pass, false otherwise.
     * @since    1.6
     */
    public function cancel($key = null)
    {
	    gantry_checktoken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = JFactory::getApplication();
        $model = $this->getModel();
        $table = $model->getTable();
        $checkin = property_exists($table, 'checked_out');
        $context = "$this->option.edit.$this->context";

        if (empty($key)) {
            $key = $table->getKeyName();
        }

        $recordId = $app->input->get($key,'','int');

        // Attempt to check-in the current record.
        if ($recordId) {
            // Check we are holding the id in the edit list.
            if (!$this->checkEditId($context, $recordId)) {
                // Somehow the person just went to the form - we don't allow that.
                $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
                $this->setMessage($this->getError(), 'error');
                $this->setRedirect(JRoute::_('index.php?option=com_templates&view=styles', false));

                return false;
            }

            if ($checkin) {
                if ($model->checkin($recordId) === false) {
                    // Check-in failed, go back to the record and display a notice.
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
                    $this->setMessage($this->getError(), 'error');
                    $this->setRedirect('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $key));

                    return false;
                }
            }
        }

        // Clean the session data and redirect.
        $this->releaseEditId($context, $recordId);
        $app->setUserState($context . '.data', null);
        $this->setRedirect(JRoute::_('index.php?option=com_templates&view=styles', false));

        return true;
    }

    public function save($key = null, $urlVar = null)
    {
        $language = JFactory::getLanguage();
        $language->load('com_templates');
        // Check for request forgeries.
	    gantry_checktoken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = JFactory::getApplication();
        $lang = JFactory::getLanguage();
        $model = $this->getModel();
        $table = $model->getTable();
	    $data = $app->input->post->get('jform', array(), 'array');
        // clean up data buy adding home



        $checkin = property_exists($table, 'checked_out');
        $context = "$this->option.edit.$this->context";
        $task = $this->getTask();

        if (empty($key)) {
            $key = $table->getKeyName();
        }

        $recordId = $app->input->getInt($key);

        $session = JFactory::getSession();
        $registry = $session->get('registry');

        if (!$this->checkEditId($context, $recordId)) {
            // Somehow the person just went to the form and saved it - we don't allow that.
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_UNHELD_ID'));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_templates&view=styles', false));

            return false;
        }

        // Populate the row id from the session.
        $data[$key] = $recordId;

        // The save2copy task needs to be handled slightly differently.
        if ($task == 'save2copy') {
            // Check-in the original row.
            if ($checkin && $model->checkin($data[$key]) === false) {
                // Check-in failed, go back to the item and display a notice.
                $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
                $this->setMessage($this->getError(), 'error');
                $this->setRedirect('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId));

                return false;
            }

            // set the master
            if (array_key_exists('master',$data['params']) && $data['params']['master'] == 'true'){
                $data['params']['master'] = $data[$key];
            }

            // Reset the ID and then treat the request as for Apply.
            $data[$key] = 0;
            $data['home'] = 0;
            $data['assigned'] = false;

            $task = 'apply';
        }

        // Access check.
        if (!$this->allowSave($data)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_templates&view=styles', false));

            return false;
        }

        // Attempt to save the data.
        if (!$model->save($data)) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $key), false));

            return false;
        }

        // Save succeeded, check-in the record.
        if ($checkin && $model->checkin($data[$key]) === false) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Check-in failed, go back to the record and display a notice.
            $this->setError(JText::sprintf('JError_Checkin_saved', $model->getError()));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $key));

            return false;
        }

        $this->setMessage(JText::_(($lang->hasKey('COM_TEMPLATES_SAVE_SUCCESS') ? 'COM_TEMPLATES' : 'JLIB_APPLICATION') . '_SAVE_SUCCESS'));

        // Redirect the user and adjust session state based on the chosen task.
        switch ($task)
        {
            case 'apply':
                // Set the record data in the session.
                $recordId = $model->getState($this->context . '.id');
                $this->holdEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $key), false));
                break;

            case 'save2new':
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend(null, $key), false));
                break;

            default:
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                // Redirect to the list screen.
                $this->setRedirect(JRoute::_('index.php?option=com_templates&view=styles', false));
                break;
        }

        // Invoke the postSave method to allow for the child class to access the model.
        $this->postSaveHook($model, $data);

        return true;
    }

    /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$pk = (int) $app->input->getInt('id');
		$this->setState('template.id', $pk);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_gantry');
		$this->setState('params', $params);
	}

    /**
	 * Method to clone and existing template style.
	 */
	public function duplicate()
	{
        $language = JFactory::getLanguage();
        $language->load('com_templates');
		// Check for request forgeries
		gantry_checktoken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = JFactory::getApplication()->input->post->get('cid', array(), 'array');
		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
			}
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(JText::_('COM_TEMPLATES_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_templates&view=styles');
	}

    	/**
	 * Removes an item.
	 *
	 * @since	1.6
	 */
	function delete()
	{
        $language = JFactory::getLanguage();
        $language->load('com_templates');
        $language->load('com_gantry');

		// Check for request forgeries
		gantry_checktoken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid	= JFactory::getApplication()->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		} else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($cid)) {
				$this->setMessage(JText::plural('COM_TEMPLATES_N_ITEMS_DELETED', count($cid)));
			} else {
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_templates&view=styles', false));
	}
}