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

jimport('joomla.application.component.view');

/**
 * View class for a list of Jcrm.
 */
class JcrmViewSyncs extends JViewLegacy {

    protected $items;
    protected $pagination;
    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        $this->state = $this->get('State');
        
        

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();

        $input = JFactory::getApplication()->input;
        $view = $input->getCmd('view', '');
        JcrmHelper::addSubmenu($view);

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar() {
        require_once JPATH_COMPONENT . '/helpers/jcrm.php';

        $state = $this->get('State');
        $canDo = JcrmHelper::getActions($state->get('filter.category_id'));

        JToolBarHelper::title(JText::_('COM_JCRM_TITLE_SYNCS'), 'syncs.png');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/sync';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
                JToolBarHelper::addNew('sync.add', 'JTOOLBAR_NEW');
            }

            if ($canDo->get('core.edit') && isset($this->items[0])) {
                JToolBarHelper::editList('sync.edit', 'JTOOLBAR_EDIT');
            }
        }

        if ($canDo->get('core.edit.state')) {

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::custom('syncs.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
                JToolBarHelper::custom('syncs.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            } else if (isset($this->items[0])) {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'syncs.delete', 'JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::archiveList('syncs.archive', 'JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
                JToolBarHelper::custom('syncs.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
        }

        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
            if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
                JToolBarHelper::deleteList('', 'syncs.delete', 'JTOOLBAR_EMPTY_TRASH');
                JToolBarHelper::divider();
            } else if ($canDo->get('core.edit.state')) {
                JToolBarHelper::trash('syncs.trash', 'JTOOLBAR_TRASH');
                JToolBarHelper::divider();
            }
        }

        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_jcrm');
        }
    }

}
