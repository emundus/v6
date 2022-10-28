<?php
/**
 * @package     RAD
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2015 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Joomla CMS View List class, used to render list of records from front-end or back-end of your component
 *
 * @package      RAD
 * @subpackage   View
 * @since        2.0
 *
 * @property RADModelList $model
 */
class RADViewList extends RADViewHtml
{
	/**
	 * The model state
	 *
	 * @var RADModelState
	 */
	protected $state;

	/**
	 * List of records which will be displayed
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var JPagination
	 */
	protected $pagination;

	/**
	 * The array which keeps list of "list" options which will used to display as the filter on the list
	 *
	 * @var array
	 */
	protected $lists = [];

	/**
	 * Prepare the view before it is displayed
	 */
	protected function prepareView()
	{
		$this->state      = $this->model->getState();
		$this->items      = $this->model->getData();
		$this->pagination = $this->model->getPagination();

		if ($this->isAdminView)
		{
			$this->lists['filter_state']    = str_replace(['class="inputbox"', 'class="form-select"'], 'class="input-medium form-select"', HTMLHelper::_('grid.state', $this->state->filter_state));
			$this->lists['filter_access']   = HTMLHelper::_('access.level', 'filter_access', $this->state->filter_access, 'class="input-medium form-select" onchange="submit();"', true);
			$this->lists['filter_language'] = HTMLHelper::_('select.genericlist', HTMLHelper::_('contentlanguage.existing', true, true), 'filter_language',
				' class="form-select" onchange="submit();" ', 'value', 'text', $this->state->filter_language);

			// Render sub-menus
			EventbookingHelperHtml::renderSubmenu($this->name);

			$this->addToolbar();
		}
	}

	/**
	 * Method to add toolbar buttons
	 */
	protected function addToolbar()
	{
		$helperClass = $this->viewConfig['class_prefix'] . 'Helper';

		if (is_callable($helperClass . '::getActions'))
		{
			$canDo = call_user_func([$helperClass, 'getActions'], $this->name, $this->state);
		}
		else
		{
			$canDo = call_user_func(['RADHelper', 'getActions'], $this->option, $this->name, $this->state);
		}

		$languagePrefix = $this->viewConfig['language_prefix'];

		if ($this->isAdminView)
		{
			ToolbarHelper::title(Text::_(strtoupper($languagePrefix . '_' . RADInflector::singularize($this->name) . '_MANAGEMENT')), 'link ' . $this->name);
		}

		if ($canDo->get('core.create') && !in_array('add', $this->hideButtons))
		{
			ToolbarHelper::addNew('add', 'JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit') && isset($this->items[0]) && !in_array('edit', $this->hideButtons))
		{
			ToolbarHelper::editList('edit', 'JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.delete') && isset($this->items[0]) && !in_array('delete', $this->hideButtons))
		{
			ToolbarHelper::deleteList(Text::_($languagePrefix . '_DELETE_CONFIRM'), 'delete');
		}

		if ($canDo->get('core.edit.state') && !in_array('publish', $this->hideButtons))
		{
			if (isset($this->items[0]->published) || isset($this->items[0]->state))
			{
				ToolbarHelper::publish('publish', 'JTOOLBAR_PUBLISH', true);
				ToolbarHelper::unpublish('unpublish', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		if ($this->isAdminView && $canDo->get('core.admin'))
		{
			ToolbarHelper::preferences($this->option);
		}
	}
}
