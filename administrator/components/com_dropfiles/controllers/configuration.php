<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2016 JoomUnited (https://www.joomunited.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */


defined('_JEXEC') || die;

/**
 * Dropfiles Component Dropfiles
 */
class DropfilesControllerConfiguration extends JControllerForm
{
    /**
     * Display the view
     *
     * @param null|string $cachable  Template
     * @param null|string $urlparams Link
     *
     * @return void
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Access check.
        if (JFactory::getUser()->authorise('core.admin', 'com_dropfiles')) {
            // Force it to be the search view
            $this->input->set('view', 'configuration');
            parent::display();
        } else {
//            JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

            $content  = '<div id="system-message-container" class="ju-message-permissions">';
            $content .= '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button>';
            $content .= '<h4 class="alert-heading">'. JText::_('COM_DROPFILES_VIEW_DROPFILES_CHECKERROR') .'</h4><div class="alert-message">'. JText::_('JERROR_ALERTNOAUTHOR');
            $content .= '</div></div></div>';

            echo $content;
        }
    }

    /**
     * Save params
     *
     * @return void
     */
    public function saveParams()
    {
        //Security check
        JSession::checkToken() || die('Invalid Token');

        $result = $this->saveToConfiguration();

        $type = 'success';
        $text = JText::_('COM_DROPFILES_CONFIGURATION_MESSAGE_SAVE_SUCCESS');

        if (!$result) {
            $type = 'error';
            $text = $this->getError();
        }

        JFactory::getSession()->set(
            'sc_configuration_message',
            array(
                'type' => $type,
                'text' => $text
            )
        );
        $this->setRedirect(JRoute::_('index.php?option=com_dropfiles&task=configuration.display', false));
    }

    /**
     * Save params and close
     *
     * @return void
     */
    public function saveParamsAndClose()
    {
        //Security check
        JSession::checkToken() || die('Invalid Token');

        $result = $this->saveToConfiguration();

        if (!$result) {
            $this->setMessage($this->getError(), 'error');
        } else {
            $this->setMessage(JText::_('COM_DROPFILES_CONFIGURATION_MESSAGE_SAVE_SUCCESS'), 'message');
        }

        $this->setRedirect(JRoute::_('index.php?option=com_dropfiles', false));
    }

    /**
     * Save configuration
     *
     * @return boolean
     *
     * @throws Exception When getApplication don't run
     */
    public function saveToConfiguration()
    {
        $input = JFactory::getApplication()->input;
        // If we are on the save command we need the actual data
        $jformData = $input->get('jform', array(), 'array');

        if (empty($jformData)) {
            $this->setError(JText::_('COM_DROPFILES_CONFIGURATION_NO_GET_FORM_DATA_ERROR'));
            return false;
        }

        $params = JComponentHelper::getParams('com_dropfiles');

        if ((int) $params->get('onedriveBusinessConnectedBy', 0) !== 0) {
            $jformData['onedriveBusinessConnectedBy'] = $params->get('onedriveBusinessConnectedBy', 0);
        }
        if ($params->get('onedriveBusinessState', '') !== '') {
            $jformData['onedriveBusinessState'] = $params->get('onedriveBusinessState', '');
        }
        if ($params->get('onedriveBusinessConnected', '') !== '') {
            $jformData['onedriveBusinessConnected'] = $params->get('onedriveBusinessConnected', 0);
        }
        if ($params->get('onedriveBusinessBaseFolder', '') !== '') {
            $jformData['onedriveBusinessBaseFolder'] = $params->get('onedriveBusinessBaseFolder', '');
        }

        $model = $this->getModel();
        $global = $model->saveGlobalConfig($jformData);
        $permissions = $model->savePermissions($jformData);

        if (!$global) {
            $this->setError(JText::_('COM_DROPFILES_CONFIGURATION_MESSAGE_SAVE_GLOBAL_CONFIG_ERROR'));
            return false;
        }

        if (!$permissions) {
            return false;
        }

        return true;
    }

    /**
     * Close configuration
     *
     * @return void
     */
    public function closeConfiguration()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_dropfiles', false));
    }
}
