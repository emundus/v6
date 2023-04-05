<?php
/**
 * View class for a list of connections.
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

/**
 * View class for a list of connections.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       1.6
 */

class FabrikAdminViewConnections extends HtmlView
{
	/**
	 * Connection items
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
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		// Initialise variables.
		$app = Factory::getApplication();
		$input = $app->input;
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new RuntimeException(implode("\n", $errors), 500);
		}

		FabrikAdminHelper::setViewLayout($this);
		$this->addToolbar();
//		$this->filterbar = JHtmlSidebar::render();

		FabrikHelperHTML::iniRequireJS();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 *
	 * @return  void
	 */

	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/fabrik.php';
		$canDo	= FabrikAdminHelper::getActions($this->state->get('filter.category_id'));
		ToolBarHelper::title(Text::_('COM_FABRIK_MANAGER_CONNECTIONS'), 'tree-2');

		if ($canDo->get('core.create'))
		{
			ToolBarHelper::addNew('connection.add', 'JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit'))
		{
			ToolBarHelper::editList('connection.edit', 'JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state'))
		{
			if ($this->state->get('filter.state') != 2)
			{
				ToolBarHelper::divider();
				ToolBarHelper::custom('connections.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				ToolBarHelper::custom('connections.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		if (Factory::getUser()->authorise('core.manage', 'com_checkin'))
		{
			ToolBarHelper::custom('connections.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolBarHelper::deleteList('', 'connections.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolBarHelper::trash('connections.trash', 'JTOOLBAR_TRASH');
		}

		if ($canDo->get('core.admin'))
		{
			ToolBarHelper::divider();
			ToolBarHelper::preferences('com_fabrik');
		}

		ToolBarHelper::divider();
		ToolBarHelper::help('JHELP_COMPONENTS_FABRIK_CONNECTIONS', false, Text::_('JHELP_COMPONENTS_FABRIK_CONNECTIONS'));

//		if (FabrikWorker::j3())
//		{
/*
			JHtmlSidebar::setAction('index.php?option=com_fabrik&view=connections');

			$publishOpts = HTMLHelper::_('jgrid.publishedOptions', array('archived' => false));
			JHtmlSidebar::addFilter(
			Text::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			HTMLHelper::_('select.options', $publishOpts, 'value', 'text', $this->state->get('filter.published'), true)
			);
			if (!empty($this->packageOptions))
			{
				array_unshift($this->packageOptions, HTMLHelper::_('select.option', 'fabrik', Text::_('COM_FABRIK_SELECT_PACKAGE')));
				JHtmlSidebar::addFilter(
				Text::_('JOPTION_SELECT_PUBLISHED'),
				'package',
				HTMLHelper::_('select.options', $this->packageOptions, 'value', 'text', $this->state->get('com_fabrik.package'), true)
				);
			}
*/
//		}
	}
}
