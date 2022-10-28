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
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

trait EventbookingViewRegistrants
{
	protected function prepareViewData()
	{
		$app    = Factory::getApplication();
		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();

		if ($app->isClient('site'))
		{
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
		}
		else
		{
			$fieldSuffix = null;
		}

		$filters = [];

		if ($config->hide_disable_registration_events)
		{
			$filters[] = 'registration_type != 3';
		}

		if ($config->only_show_registrants_of_event_owner && !$user->authorise('core.admin', 'com_eventbooking'))
		{
			$filters[] = 'created_by = ' . $user->id;
		}

		$rows                           = EventbookingHelperDatabase::getAllEvents($config->sort_events_dropdown, $config->hide_past_events_from_events_dropdown, $filters, $fieldSuffix);
		$this->lists['filter_event_id'] = EventbookingHelperHtml::getEventsDropdown($rows, 'filter_event_id', 'class="input-xlarge form-select" onchange="submit();"', $this->state->filter_event_id);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('EB_REGISTRATION_STATUS'));
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_PENDING'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_PAID'));

		if ($config->activate_waitinglist_feature)
		{
			$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_WAITING_LIST'));
			$options[] = HTMLHelper::_('select.option', 4, Text::_('EB_WAITING_LIST_CANCELLED'));
		}

		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_CANCELLED'));

		$this->lists['filter_published'] = HTMLHelper::_('select.genericlist', $options, 'filter_published', ' class="input-medium form-select" onchange="submit()" ', 'value', 'text',
			$this->state->filter_published);

		if ($config->activate_checkin_registrants)
		{
			$options                          = [];
			$options[]                        = HTMLHelper::_('select.option', -1, Text::_('EB_CHECKIN_STATUS'));
			$options[]                        = HTMLHelper::_('select.option', 1, Text::_('EB_CHECKED_IN'));
			$options[]                        = HTMLHelper::_('select.option', 0, Text::_('EB_NOT_CHECKED_IN'));
			$this->lists['filter_checked_in'] = HTMLHelper::_('select.genericlist', $options, 'filter_checked_in', ' class="input-medium form-select" onchange="submit()" ', 'value', 'text',
				$this->state->filter_checked_in);
		}

		$rowFields = EventbookingHelperRegistration::getAllEventFields($this->state->filter_event_id);
		$fields    = [];
		$filters   = [];

		$filterFieldsValues = $this->state->get('filter_fields', []);

		foreach ($rowFields as $rowField)
		{
			if ($rowField->filterable)
			{
				$fieldOptions = explode("\r\n", $rowField->values);

				$options   = [];
				$options[] = HTMLHelper::_('select.option', '', $rowField->title);

				foreach ($fieldOptions as $option)
				{
					$options[] = HTMLHelper::_('select.option', $option, $option);
				}

				$filters['field_' . $rowField->id] = HTMLHelper::_('select.genericlist', $options, 'filter_fields[field_' . $rowField->id . ']', ' class="input-medium form-select" onchange="submit();" ', 'value', 'text', ArrayHelper::getValue($filterFieldsValues, 'field_' . $rowField->id));
			}

			if ($rowField->show_on_registrants != 1 || in_array($rowField->name, ['first_name', 'last_name', 'email']))
			{
				continue;
			}

			$fields[$rowField->id] = $rowField;
		}

		if (count($fields))
		{
			$this->fieldsData = $this->model->getFieldsData(array_keys($fields));
		}

		list($ticketTypes, $tickets) = $this->model->getTicketsData();

		$this->fields      = $fields;
		$this->ticketTypes = $ticketTypes;
		$this->tickets     = $tickets;
		$this->filters     = $filters;
		$this->config      = $config;
		$this->coreFields  = EventbookingHelperRegistration::getPublishedCoreFields();
	}
}