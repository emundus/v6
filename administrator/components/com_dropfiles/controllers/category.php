<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;

$path_admin_category = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components';
$path_admin_category .= DIRECTORY_SEPARATOR . 'com_categories' . DIRECTORY_SEPARATOR . 'controllers';
$path_admin_category .= DIRECTORY_SEPARATOR . 'category.php';
require_once($path_admin_category);

/**
 * Class DropfilesControllerCategory
 */
class DropfilesControllerCategory extends CategoriesControllerCategory
{

    /**
     * Saved category id
     *
     * @var string
     */
    public $savedId;

    /**
     * Set a file title
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function setTitle()
    {
        $app = JFactory::getApplication();
        $id_category = $app->input->getInt('id_category', 0);

        $model = $this->getModel();
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.edit')) {
            if ($canDo->get('core.edit.own')) {
                $category = $model->getItem($id_category);
                if ((int) $category->created_user_id !== (int) JFactory::getUser()->id) {
                    $this->exitStatus('not permitted');
                }
            } else {
                $this->exitStatus('not permitted');
            }
        }

        $title = JFactory::getApplication()->input->getString('title');

        if ($model->setTitle($id_category, $title)) {
            $return = true;
        } else {
            $return = false;
        }
        echo json_encode($return);
        JFactory::getApplication()->close();
    }

    /**
     * Method to add a category
     *
     * @return void
     * @throws \Exception Throw when some thing wrong
     * @since  1.0
     */
    public function addCategory()
    {
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.create')) {
            $this->exitStatus('not permitted');
        }
        $app = JFactory::getApplication();
        $type = $app->input->get('type');
        switch ($type) {
            case 'dropbox':
                $title = JText::_('COM_DROPFILES_DROPBOX_DEFAULT_NAME');
                break;
            case 'googledrive':
                $title = JText::_('COM_DROPFILES_MODEL_CATEGORY_DEFAULT_NAME');
                break;
            case 'onedrive':
                $title = JText::_('COM_DROPFILES_ONEDRIVE_CATEGORY_DEFAULT_NAME');
                break;
            default:
                $title = JText::_('COM_DROPFILES_MODEL_CATEGORY_DEFAULT_NAME');
        }

        $datas                              = array();
        $datas['jform']['extension']        = 'com_dropfiles';
        $datas['jform']['title']            = $title;
        $datas['jform']['alias']            = $title . '-' . date('dmY-h-m-s', time());
        $datas['jform']['parent_id']        = 1;
        $datas['jform']['language']         = '*';
        $datas['jform']['metadata']['tags'] = '';
        // $datas['jform']['published'] = 1;

        //Set state value to retreive the correct table
        $model = $this->getModel();
        $model->setState('category.extension', 'category');

        foreach ($datas as $data => $val) {
            $app->input->set($data, $val, 'POST');
        }
        $app->input->set('id', null, 'POST');
        if ($this->save()) {
            $this->exitStatus(true, array('id_category' => $this->savedId, 'name' => $title));
        }
        $this->exitStatus('error while adding category');
    }


    /**
     * Method run after save J25
     *
     * @param object $model     Model instance
     * @param array  $validData Data
     *
     * @return void
     * @throws \Exception Throw when Model name not found
     * @since  1.0
     */
    protected function postSaveHookJ25(&$model, $validData = array())
    {
        $this->savedId = $model->getState($model->getName() . '.id');
        parent::postSaveHook($model, $validData);
    }

    /**
     * Method run after save J3
     *
     * @param JModelLegacy $model     Model instance
     * @param array        $validData Data
     *
     * @return void
     * @throws \Exception Throw when Model name not found
     * @since  1.0
     */
    protected function postSaveHookJ3(JModelLegacy $model, $validData = array())
    {
        $this->savedId = $model->getState($model->getName() . '.id');
        parent::postSaveHook($model, $validData);
    }

    /**
     * Save params config
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  1.0
     */
    public function setParams()
    {
        $app = JFactory::getApplication();
        $datas = $app->input->get('jform', null, 'default', 'array');

        $model = $this->getModel();
        $canDo = DropfilesHelper::getActions();
        if (!$canDo->get('core.edit')) {
            if ($canDo->get('core.edit.own')) {
                $category = $model->getItem((int)$datas['id']);
                if ((int )$category->created_user_id !== (int) JFactory::getUser()->id) {
                    $this->exitStatus('not permitted');
                }
            } else {
                $this->exitStatus('not permitted');
            }
        }
        $modelC = $this->getModel('config');

        if (!$modelC->save($datas)) {
            $this->exitStatus('error while saving params : ' . $model->getError());
        }
        unset($datas['params']);

        $item = get_object_vars($model->getItem((int)$datas['id']));
        $item['access'] = (int)$datas['access'];
        if (isset($datas['created_user_id']) && (int)$datas['created_user_id'] > 0) {
            $item['created_user_id'] = (int)$datas['created_user_id'];
        } else {
            $item['created_user_id'] = JFactory::getUser()->id;
        }

        //Set state value to retreive the correct table
        $model->setState('category.extension', 'categoryparams');

        $app->input->set('jform', $item, 'POST');
        $id = $this->save();

        if ($id) {
            $this->exitStatus(true);
        }
        // Stop execution if no status return
        die();
    }

    /**
     * Return a json response
     *
     * @param boolean $status Response status
     * @param array   $datas  Array of datas to return with the json string
     *
     * @return void
     * @throws \Exception Thrown when application can not start
     * @since  1.0
     */
    private function exitStatus($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        JFactory::getApplication()->close();
    }


    /**
     * Get redirect to item append
     *
     * @param null   $recordId Record id
     * @param string $urlVar   Url variable
     *
     * @return string
     * @throws \Exception Thrown when application can not start
     * @since  1.0
     */
    public function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        $append = parent::getRedirectToItemAppend($recordId, $urlVar);

        $app = JFactory::getApplication();
        $format = $app->input->get('format', 'raw');

        // Setup redirect info.
        if ($format) {
            $append .= '&format=' . $format;
        }
        return $append;
    }

    /**
     * Check allow edit
     *
     * @param array  $data Data
     * @param string $key  Key
     *
     * @return boolean
     * @since  1.0
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $canDo = DropfilesHelper::getActions();
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
            return true;
        }
        return false;
    }

    /**
     * Check allow add
     *
     * @param array $data Data
     *
     * @return boolean
     * @since  1.0
     */
    protected function allowAdd($data = array())
    {
        $canDo = DropfilesHelper::getActions();
        if ((int)($canDo->get('core.create'))) {
            return true;
        }
        return false;
    }

    /**
     * Method to save a record.
     *
     * @param string $key    The name of the primary key of the URL variable.
     * @param string $urlVar The name of the URL variable if different from the primary key (sometimes required
     * to avoid router collisions).
     *
     * @return boolean  True if successful, false otherwise.
     *
     * @throws \Exception Throw when application not start
     * @since  12.2
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        //JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $app = JFactory::getApplication();
        $lang = JFactory::getLanguage();
        $model = $this->getModel();
        $table = $model->getTable();
        $data = $app->input->get('jform', array(), 'array');
        property_exists($table, 'checked_out');
        $context = $this->option . '.edit.' . $this->context;

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = $app->input->getInt($urlVar);

        // Populate the row id from the session.
        $data[$key] = $recordId;


        // Access check.
        if (!$this->allowSave($data, $key)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->setMessage($this->getError(), 'error');

            $redirect_str = 'index.php?option=' . $this->option . '&view=' . $this->view_list;
            $redirect_str .= $this->getRedirectToListAppend();
            $this->setRedirect(JRoute::_($redirect_str, false));

            return false;
        }

        // Validate the posted data.
        // Sometimes the form needs some posted data, such as for plugins and modules.
        $form = $model->getForm($data, false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');

            return false;
        }

        // to make it work when logged in as administrator on frontend then saving data
        unset($data['published']);
        // Test whether the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $redirect_str = 'index.php?option=' . $this->option . '&view=' . $this->view_item;
            $redirect_str .= $this->getRedirectToItemAppend($recordId, $urlVar);
            $this->setRedirect(JRoute::_($redirect_str, false));
            return false;
        }

        // Attempt to save the data.
        $validData['published'] = 1;
        if (!$model->save($validData)) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
            $this->setMessage($this->getError(), 'error');
            $redirect_str = 'index.php?option=' . $this->option . '&view=' . $this->view_item;
            $redirect_str .= $this->getRedirectToItemAppend($recordId, $urlVar);
            $this->setRedirect(JRoute::_($redirect_str, false));
            return false;
        }

        $text_prefix = $this->text_prefix . ($recordId === 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS';
        $this->setMessage(
            JText::_(
                ($lang->hasKey($text_prefix) ? $this->text_prefix
                    : 'JLIB_APPLICATION') . ($recordId === 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
            )
        );

        // Clear the record id and data from the session.
        $this->releaseEditId($context, $recordId);
        $app->setUserState($context . '.data', null);

        // Redirect to the list screen.
        $redirect_str = 'index.php?option=' . $this->option . '&view=' . $this->view_list;
        $redirect_str .= $this->getRedirectToListAppend();
        $this->setRedirect(JRoute::_($redirect_str, false));

        // Invoke the postSave method to allow for the child class to access the model.
        if (DropfilesBase::isJoomla30()) {
            $this->postSaveHookJ3($model, $validData);
        } else {
            $this->postSaveHookJ25($model, $validData);
        }

        return true;
    }
}
