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

defined('_JEXEC') || die;

/**
 * View class for a list of users.
 */
class DropfilesViewUsers extends JViewLegacy
{
    /**
     * The item data.
     *
     * @var object
     */
    protected $items;

    /**
     * The pagination object.
     *
     * @var JPagination
     */
    protected $pagination;

    /**
     * The model state.
     *
     * @var JObject
     */
    protected $state;

    /**
     * Display the view
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void|boolean
     */
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        //$this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->canDo = JHelperContent::getActions('com_dropfiles');

        // Check for errors.
        $errors = $this->get('Errors');
        if (count($errors)) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        // Include the component HTML helpers.
        JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return array  Array containing the field name to sort by as the key and display text as value
     *
     * @since 3.0
     */
    protected function getSortFields()
    {
        return array(
            'a.name' => JText::_('COM_DROPFILES_HEADING_NAME'),
            'a.username' => JText::_('JGLOBAL_USERNAME'),
            'a.block' => JText::_('COM_DROPFILES_HEADING_ENABLED'),
            'a.activation' => JText::_('COM_DROPFILES_HEADING_ACTIVATED'),
            'a.email' => JText::_('JGLOBAL_EMAIL'),
            'a.lastvisitDate' => JText::_('COM_DROPFILES_HEADING_LAST_VISIT_DATE'),
            'a.registerDate' => JText::_('COM_DROPFILES_HEADING_REGISTRATION_DATE'),
            'a.id' => JText::_('JGRID_HEADING_ID')
        );
    }
}
