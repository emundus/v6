<?php
/**
 *  View class for a list of lists.
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.application.component.view');

class FabrikAdminViewLists extends HtmlView
{
	/**
	 * List items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var  Pagination
	 */
	protected $pagination;

	/**
	 * View state
	 *
	 * @var object
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 */

	public function display($tpl = null)
	{
		switch ($this->getLayout())
		{
			case 'confirmdelete':
				$this->confirmdelete();

				return;
				break;
			case 'import':
				$this->import($tpl);

				return;
				break;
		}
		// Initialise variables.
		$app = Factory::getApplication();
		$input = $app->input;
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new RuntimeException(implode("\n", $errors), 500);

			return false;
		}

		$this->table_groups = $this->get('TableGroups');
		FabrikAdminHelper::setViewLayout($this);
		$this->addToolbar();
//		$this->filterbar = JHtmlSidebar::render();

		FabrikHelperHTML::iniRequireJS();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */

	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/fabrik.php';
		$canDo = FabrikAdminHelper::getActions($this->state->get('filter.category_id'));
		ToolBarHelper::title(Text::_('COM_FABRIK_MANAGER_LISTS'), 'list');

		if ($canDo->get('core.create'))
		{
			ToolBarHelper::addNew('list.add', 'JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit'))
		{
			ToolBarHelper::editList('list.edit', 'JTOOLBAR_EDIT');
		}

		ToolBarHelper::custom('list.copy', 'copy.png', 'copy_f2.png', 'COM_FABRIK_COPY');

		if ($canDo->get('core.edit.state'))
		{
			if ($this->state->get('filter.state') != 2)
			{
				ToolBarHelper::divider();
				ToolBarHelper::custom('lists.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				ToolBarHelper::custom('lists.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		ToolBarHelper::divider();

		if ($canDo->get('core.create'))
		{
			ToolBarHelper::custom('import.display', 'upload.png', 'upload_f2.png', 'COM_FABRIK_IMPORT', false);
		}

		ToolBarHelper::divider();

		if (Factory::getUser()->authorise('core.manage', 'com_checkin'))
		{
			ToolBarHelper::custom('lists.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
		}

		ToolBarHelper::divider();

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolBarHelper::deleteList('', 'lists.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolBarHelper::trash('lists.trash', 'JTOOLBAR_TRASH');
		}

		if ($canDo->get('core.admin'))
		{
			ToolBarHelper::divider();
			ToolBarHelper::preferences('com_fabrik');
		}

		ToolBarHelper::divider();
		ToolBarHelper::help('JHELP_COMPONENTS_FABRIK_LISTS', false, Text::_('JHELP_COMPONENTS_FABRIK_LISTS'));
/*
		JHtmlSidebar::setAction('index.php?option=com_fabrik&view=lists');

		$publishOpts = HTMLHelper::_('jgrid.publishedOptions', array('archived' => false));
		JHtmlSidebar::addFilter(
			Text::_('JOPTION_SELECT_PUBLISHED'), 'filter_published',
			HTMLHelper::_('select.options', $publishOpts, 'value', 'text', $this->state->get('filter.published'), true)
		);
*/		
	}

	/**
	 * Add the page title and toolbar for confirming list deletion
	 *
	 * @return  void
	 */

	protected function addConfirmDeleteToolbar()
	{
		$app = Factory::getApplication();
		$app->input->set('hidemainmenu', true);
		ToolBarHelper::title(Text::_('COM_FABRIK_MANAGER_LIST_CONFIRM_DELETE'), 'list');
		ToolBarHelper::save('lists.dodelete', 'JTOOLBAR_APPLY');
		ToolBarHelper::cancel('list.cancel', 'JTOOLBAR_CANCEL');
		ToolBarHelper::divider();
		ToolBarHelper::help('JHELP_COMPONENTS_FABRIK_LISTS_EDIT', true, 'http://fabrikar.com/wiki/index.php/List_delete_confirmation');
	}

	/**
	 * Add the page title and toolbar for List import
	 *
	 * @return  void
	 */

	protected function addImportToolBar()
	{
		$app = Factory::getApplication();
		$app->input->set('hidemainmenu', true);
		ToolBarHelper::title(Text::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list');
		ToolBarHelper::save('lists.doimport', 'JTOOLBAR_APPLY');
		ToolBarHelper::cancel('list.cancel', 'JTOOLBAR_CANCEL');
	}

	/**
	 * Show a screen asking if the user wants to delete the lists forms/groups/elements
	 * and if they want to drop the underlying database table
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 */

	protected function confirmdelete($tpl = null)
	{
		$this->form = $this->get('ConfirmDeleteForm', 'list');
		$model = $this->getModel('lists');
		$this->items = $model->getDbTableNames();
		$this->addConfirmDeleteToolbar();
		$this->setLayout('confirmdeletebootstrap');

		parent::display($tpl);
	}

	/**
	 * Show a screen allowing the user to import a csv file to create a fabrik table.
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 */

	protected function import($tpl = null)
	{
		$this->form = $this->get('ImportForm');
		$this->addImportToolBar();
		parent::display($tpl);
	}
}
