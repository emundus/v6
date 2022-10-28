<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class EventbookingViewDiscountHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		$config = EventbookingHelper::getConfig();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, title, event_date')
			->from('#__eb_events')
			->where('published=1')
			->order($config->sort_events_dropdown);

		if ($config->hide_past_events_from_events_dropdown)
		{
			$currentDate = $db->quote(HTMLHelper::_('date', 'Now', 'Y-m-d'));

			if ($this->item->event_ids)
			{
				$query->where('(id IN(' . $this->item->event_ids . ') OR DATE(event_date) >= ' . $currentDate . ' OR DATE(event_end_date) >= ' . $currentDate . ')');
			}
			else
			{
				$query->where('(DATE(event_date) >= ' . $currentDate . ' OR DATE(event_end_date) >= ' . $currentDate . ')');
			}
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$selectedEventIds = [];

		if ($this->item->id)
		{
			$query->clear()
				->select('event_id')
				->from('#__eb_discount_events')
				->where('discount_id=' . $this->item->id);
			$db->setQuery($query);
			$selectedEventIds = $db->loadColumn();
		}

		if ($config->multiple_booking)
		{
			$options                      = [];
			$options[]                    = HTMLHelper::_('select.option', 0, '%');
			$options[]                    = HTMLHelper::_('select.option', 1, $config->currency_symbol);
			$this->lists['discount_type'] = HTMLHelper::_('select.genericlist', $options, 'discount_type', 'class="input-small form-select d-inline-block"', 'value', 'text', $this->item->discount_type);
		}

		$this->lists['event_id'] = EventbookingHelperHtml::getEventsDropdown($rows, 'event_id[]', 'class="input-xlarge form-select advSelect" multiple="multiple" ', $selectedEventIds);
		$this->nullDate          = $db->getNullDate();
		$this->config            = $config;
		$this->datePickerFormat  = $config->get('date_field_format', '%Y-%m-%d');
	}
}
