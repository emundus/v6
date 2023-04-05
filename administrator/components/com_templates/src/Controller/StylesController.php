<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Template styles list controller class.
 *
 * @since  1.6
 */
class StylesController extends AdminController
{
    /**
     * Method to clone and existing template style.
     *
     * @return  void
     */
    public function duplicate()
    {
        // Check for request forgeries
        $this->checkToken();

        $pks = (array) $this->input->post->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $pks = array_filter($pks);

        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
            }

            $model = $this->getModel();
            $model->duplicate($pks);
            $this->setMessage(Text::_('COM_TEMPLATES_SUCCESS_DUPLICATED'));
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
        }

        $this->setRedirect('index.php?option=com_templates&view=styles');
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  BaseDatabaseModel
     *
     * @since   1.6
     */
    public function getModel($name = 'Style', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    /**
     * Method to set the home template for a client.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function setDefault()
    {
        // Check for request forgeries
        $this->checkToken();

        $pks = (array) $this->input->post->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $pks = array_filter($pks);

        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
            }

            // Pop off the first element.
            $id = array_shift($pks);

            /** @var \Joomla\Component\Templates\Administrator\Model\StyleModel $model */
            $model = $this->getModel();
            $model->setHome($id);
            $this->setMessage(Text::_('COM_TEMPLATES_SUCCESS_HOME_SET'));
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_templates&view=styles');
    }

    /**
     * Method to unset the default template for a client and for a language
     *
     * @return  void
     *
     * @since   1.6
     */
    public function unsetDefault()
    {
        // Check for request forgeries
        $this->checkToken('request');

        $pks = (array) $this->input->get->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $pks = array_filter($pks);

        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
            }

            // Pop off the first element.
            $id = array_shift($pks);

            /** @var \Joomla\Component\Templates\Administrator\Model\StyleModel $model */
            $model = $this->getModel();
            $model->unsetHome($id);
            $this->setMessage(Text::_('COM_TEMPLATES_SUCCESS_HOME_UNSET'));
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_templates&view=styles');
    }
}
