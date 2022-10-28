<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class EventbookingViewEventsHtml extends RADViewList
{
	protected function prepareView()
	{
		parent::prepareView();

		$this->lists['filter_category_id'] = EventbookingHelperHtml::getCategoryListDropdown('filter_category_id', $this->state->filter_category_id, 'class="form-select" onchange="submit();"');

		$options                           = [];
		$options[]                         = HTMLHelper::_('select.option', 0, Text::_('EB_SELECT_LOCATION'), 'id', 'name');
		$options                           = array_merge($options, EventbookingHelperDatabase::getAllLocations());
		$this->lists['filter_location_id'] = HTMLHelper::_('select.genericlist', $options, 'filter_location_id', ' class="form-select" onchange="submit();" ',
			'id', 'name', $this->state->filter_location_id);

		$options                      = [];
		$options[]                    = HTMLHelper::_('select.option', 0, Text::_('EB_EVENTS_FILTER'));
		$options[]                    = HTMLHelper::_('select.option', 1, Text::_('EB_HIDE_PAST_EVENTS'));
		$options[]                    = HTMLHelper::_('select.option', 2, Text::_('EB_HIDE_CHILDREN_EVENTS'));
		$this->lists['filter_events'] = HTMLHelper::_('select.genericlist', $options, 'filter_events', ' class="input-medium form-select" onchange="submit();" ',
			'value', 'text', $this->state->filter_events);

		$this->config = EventbookingHelper::getConfig();
	}
}
