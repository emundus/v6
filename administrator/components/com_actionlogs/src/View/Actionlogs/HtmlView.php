<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\View\Actionlogs;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogsModel;

/**
 * View class for a list of logs.
 *
 * @since  3.9.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items.
     *
     * @var    array
     * @since  3.9.0
     */
    protected $items;

    /**
     * The model state
     *
     * @var    array
     * @since  3.9.0
     */
    protected $state;

    /**
     * The pagination object
     *
     * @var    Pagination
     * @since  3.9.0
     */
    protected $pagination;

    /**
     * Form object for search filters
     *
     * @var    Form
     * @since  3.9.0
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var    array
     * @since  3.9.0
     */
    public $activeFilters;

    /**
     * Setting if the IP column should be shown
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $showIpColumn = false;

    /**
     * Setting if the date should be displayed relative to the current date.
     *
     * @var    boolean
     * @since  4.1.0
     */
    protected $dateRelative = false;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   3.9.0
     *
     * @throws  Exception
     */
    public function display($tpl = null)
    {
        /** @var ActionlogsModel $model */
        $model               = $this->getModel();
        $this->items         = $model->getItems();
        $this->state         = $model->getState();
        $this->pagination    = $model->getPagination();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();
        $params              = ComponentHelper::getParams('com_actionlogs');
        $this->showIpColumn  = (bool) $params->get('ip_logging', 0);
        $this->dateRelative  = (bool) $params->get('date_relative', 1);

        if (\count($errors = $model->getErrors()))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        // Load all actionlog plugins language files
        ActionlogsHelper::loadActionLogPluginsLanguage();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_ACTIONLOGS_MANAGER_USERLOGS'), 'icon-list-2');

        ToolbarHelper::custom('actionlogs.exportSelectedLogs', 'download', '', 'COM_ACTIONLOGS_EXPORT_CSV', true);
        ToolbarHelper::custom('actionlogs.exportLogs', 'download', '', 'COM_ACTIONLOGS_EXPORT_ALL_CSV', false);
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'actionlogs.delete');
        $bar = Toolbar::getInstance('toolbar');
        $bar->appendButton('Confirm', 'COM_ACTIONLOGS_PURGE_CONFIRM', 'delete', 'COM_ACTIONLOGS_TOOLBAR_PURGE', 'actionlogs.purge', false);
        ToolbarHelper::preferences('com_actionlogs');
        ToolbarHelper::help('User_Actions_Log');
    }
}
