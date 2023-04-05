<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\View\Transition;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Workflow\Administrator\Helper\StageHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class to add or edit a transition of a workflow
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The model state
     *
     * @var     object
     * @since   4.0.0
     */
    protected $state;

    /**
     * Form object to generate fields
     *
     * @var    \Joomla\CMS\Form\Form
     *
     * @since  4.0.0
     */
    protected $form;

    /**
     * Items array
     *
     * @var    object
     * @since  4.0.0
     */
    protected $item;

    /**
     * That is object of Application
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     */
    protected $app;

    /**
     * The application input object.
     *
     * @var    \Joomla\CMS\Input\Input
     * @since  4.0.0
     */
    protected $input;

    /**
     * The ID of current workflow
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $workflowID;

    /**
     * The name of current extension
     *
     * @var    string
     * @since  4.0.0
     */
    protected $extension;

    /**
     * The section of the current extension
     *
     * @var    string
     * @since  4.0.0
     */
    protected $section;

    /**
     * Display item view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function display($tpl = null)
    {
        $this->app = Factory::getApplication();
        $this->input = $this->app->input;

        // Get the Data
        $this->state      = $this->get('State');
        $this->form       = $this->get('Form');
        $this->item       = $this->get('Item');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $extension = $this->state->get('filter.extension');

        $parts = explode('.', $extension);

        $this->extension = array_shift($parts);

        if (!empty($parts)) {
            $this->section = array_shift($parts);
        }

        // Get the ID of workflow
        $this->workflowID = $this->input->getCmd("workflow_id");

        // Set the toolbar
        $this->addToolbar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user       = $this->getCurrentUser();
        $userId     = $user->id;
        $isNew      = empty($this->item->id);

        $canDo = StageHelper::getActions($this->extension, 'transition', $this->item->id);

        ToolbarHelper::title(empty($this->item->id) ? Text::_('COM_WORKFLOW_TRANSITION_ADD') : Text::_('COM_WORKFLOW_TRANSITION_EDIT'), 'address');

        $toolbarButtons = [];

        $canCreate = $canDo->get('core.create');

        if ($isNew) {
            // For new records, check the create permission.
            if ($canCreate) {
                ToolbarHelper::apply('transition.apply');
                $toolbarButtons = [['save', 'transition.save'], ['save2new', 'transition.save2new']];
            }

            ToolbarHelper::saveGroup(
                $toolbarButtons,
                'btn-success'
            );

            ToolbarHelper::cancel(
                'transition.cancel'
            );
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            if ($itemEditable) {
                ToolbarHelper::apply('transition.apply');
                $toolbarButtons[] = ['save', 'transition.save'];

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canCreate) {
                    $toolbarButtons[] = ['save2new', 'transition.save2new'];
                    $toolbarButtons[] = ['save2copy', 'transition.save2copy'];
                }
            }

            if (count($toolbarButtons) > 1) {
                ToolbarHelper::saveGroup(
                    $toolbarButtons,
                    'btn-success'
                );
            } else {
                ToolbarHelper::save('transition.save');
            }

            ToolbarHelper::cancel(
                'transition.cancel',
                'JTOOLBAR_CLOSE'
            );
        }

        ToolbarHelper::divider();
    }
}
