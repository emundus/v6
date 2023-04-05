<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\View\Item;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The HTML Menus Menu Item View.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The active item
     *
     * @var   CMSObject
     */
    protected $item;

    /**
     * @var  mixed
     */
    protected $modules;

    /**
     * The model state
     *
     * @var   CMSObject
     */
    protected $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var    CMSObject
     * @since  3.7.0
     */
    protected $canDo;

    /**
     * A list of view levels containing the id and title of the view level
     *
     * @var    \stdClass[]
     * @since  4.0.0
     */
    protected $levels;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        $this->state   = $this->get('State');
        $this->form    = $this->get('Form');
        $this->item    = $this->get('Item');
        $this->modules = $this->get('Modules');
        $this->levels  = $this->get('ViewLevels');
        $this->canDo   = ContentHelper::getActions('com_menus', 'menu', (int) $this->state->get('item.menutypeid'));

        // Check if we're allowed to edit this item
        // No need to check for create, because then the moduletype select is empty
        if (!empty($this->item->id) && !$this->canDo->get('core.edit')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // If we are forcing a language in modal (used for associations).
        if ($this->getLayout() === 'modal' && $forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'cmd')) {
            // Set the language field to the forcedLanguage and disable changing it.
            $this->form->setValue('language', null, $forcedLanguage);
            $this->form->setFieldAttribute('language', 'readonly', 'true');

            // Only allow to select categories with All language or with the forced language.
            $this->form->setFieldAttribute('parent_id', 'language', '*,' . $forcedLanguage);
        }

        parent::display($tpl);
        $this->addToolbar();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        $input = Factory::getApplication()->input;
        $input->set('hidemainmenu', true);

        $user       = $this->getCurrentUser();
        $isNew      = ($this->item->id == 0);
        $checkedOut = !(is_null($this->item->checked_out) || $this->item->checked_out == $user->get('id'));
        $canDo      = $this->canDo;
        $clientId   = $this->state->get('item.client_id', 0);

        ToolbarHelper::title(Text::_($isNew ? 'COM_MENUS_VIEW_NEW_ITEM_TITLE' : 'COM_MENUS_VIEW_EDIT_ITEM_TITLE'), 'list menu-add');

        $toolbarButtons = [];

        // If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
        if ($isNew && $canDo->get('core.create')) {
            if ($canDo->get('core.edit')) {
                ToolbarHelper::apply('item.apply');
            }

            $toolbarButtons[] = ['save', 'item.save'];
        }

        // If not checked out, can save the item.
        if (!$isNew && !$checkedOut && $canDo->get('core.edit')) {
            ToolbarHelper::apply('item.apply');

            $toolbarButtons[] = ['save', 'item.save'];
        }

        // If the user can create new items, allow them to see Save & New
        if ($canDo->get('core.create')) {
            $toolbarButtons[] = ['save2new', 'item.save2new'];
        }

        // If an existing item, can save to a copy only if we have create rights.
        if (!$isNew && $canDo->get('core.create')) {
            $toolbarButtons[] = ['save2copy', 'item.save2copy'];
        }

        ToolbarHelper::saveGroup(
            $toolbarButtons,
            'btn-success'
        );

        if (!$isNew && Associations::isEnabled() && ComponentHelper::isEnabled('com_associations') && $clientId != 1) {
            ToolbarHelper::custom('item.editAssociations', 'contract', '', 'JTOOLBAR_ASSOCIATIONS', false, false);
        }

        if ($isNew) {
            ToolbarHelper::cancel('item.cancel');
        } else {
            ToolbarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();

        // Get the help information for the menu item.
        $lang = Factory::getLanguage();

        $help = $this->get('Help');

        if ($lang->hasKey($help->url)) {
            $debug = $lang->setDebug(false);
            $url   = Text::_($help->url);
            $lang->setDebug($debug);
        } else {
            $url = $help->url;
        }

        ToolbarHelper::help($help->key, $help->local, $url);
    }
}
