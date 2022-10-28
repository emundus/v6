<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class EventbookingViewEventsHtml extends RADViewList
{
	protected $lists = [];

	/**
	 * Prepare the view before it's being rendered
	 *
	 * @throws Exception
	 */
	protected function prepareView()
	{
		// Require user to login before allowing access to events management page
		$this->requestLogin();

		parent::prepareView();

		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$filters     = [];

		$user = Factory::getUser();

		if (!$user->authorise('core.admin', 'com_eventbooking'))
		{
			$filters[] = 'submit_event_access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')';
		}

		$this->lists['filter_category_id'] = EventbookingHelperHtml::getCategoryListDropdown('filter_category_id', $this->state->filter_category_id, 'class="form-select" onchange="submit();"', $fieldSuffix, $filters);

		$this->lists['filter_search'] = $this->state->filter_search;

		$options                      = [];
		$options[]                    = HTMLHelper::_('select.option', 0, Text::_('EB_EVENTS_FILTER'));
		$options[]                    = HTMLHelper::_('select.option', 1, Text::_('EB_HIDE_PAST_EVENTS'));
		$options[]                    = HTMLHelper::_('select.option', 2, Text::_('EB_HIDE_CHILDREN_EVENTS'));
		$this->lists['filter_events'] = HTMLHelper::_('select.genericlist', $options, 'filter_events', ' class="form-select input-medium" onchange="submit();" ',
			'value', 'text', $this->state->filter_events);

		$this->findAndSetActiveMenuItem();

		$this->config   = EventbookingHelper::getConfig();
		$this->nullDate = Factory::getDbo()->getNullDate();
		$this->return   = base64_encode(Uri::getInstance()->toString());

		EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$this->items, ['title', 'price_text']]);

		$this->addToolbar();

		// Force layout to default
		$this->setLayout('default');
	}

	protected function addToolbar()
	{
		JLoader::register('JToolbarHelper', JPATH_ADMINISTRATOR . '/includes/toolbar.php');

		$user = Factory::getUser();

		if (EventbookingHelperAcl::checkAddEvent())
		{
			ToolbarHelper::addNew('add', 'JTOOLBAR_NEW');
		}

		if ($user->authorise('core.admin', 'com_eventbooking')
			|| $user->authorise('core.edit', 'com_eventbooking')
			|| $user->authorise('core.edit.own', 'com_eventbooking'))
		{
			ToolbarHelper::editList('edit', 'JTOOLBAR_EDIT');
		}

		if (EventbookingHelperAcl::canDeleteEvent())
		{
			ToolbarHelper::deleteList(Text::_('EB_DELETE_CONFIRM'), 'delete');
		}

		if ($user->authorise('core.admin', 'com_eventbooking') || $user->authorise('core.edit.state', 'com_eventbooking'))
		{
			ToolbarHelper::publish('publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}

		$config = EventbookingHelper::getConfig();

		if ($config->enable_cancel_events)
		{
			ToolbarHelper::custom('cancel_event', 'cancel', 'cancel', 'EB_CANCEL_EVENT', true);
		}
	}
}
