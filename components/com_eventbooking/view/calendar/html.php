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
use Joomla\Utilities\ArrayHelper;

class EventbookingViewCalendarHtml extends RADViewHtml
{
	public function display()
	{
		$config = EventbookingHelper::getConfig();
		$layout = $this->getLayout();

		$this->showCalendarMenu = $config->activate_weekly_calendar_view || $config->activate_daily_calendar_view;
		$this->config           = $config;

		$this->findAndSetActiveMenuItem();

		#Support Weekly and Daily


		if ($layout == 'weekly')
		{
			$this->displayWeeklyView();

			return;
		}
		elseif ($layout == 'daily')
		{
			$this->displayDailyView();

			return;
		}

		$this->setLayout('default');

		$params = $this->getParams();

		//Set evens alias to EventbookingHelperRoute to improve performance
		$eventsAlias = [];

		/* @var EventbookingModelCalendar $model */
		$model                 = $this->getModel();
		$rows                  = $model->getData();
		$this->currentDateData = $model->getCurrentDate();

		foreach ($rows as $row)
		{
			if ($config->insert_event_id)
			{
				$eventsAlias[$row->id] = $row->id . '-' . $row->alias;
			}
			else
			{
				$eventsAlias[$row->id] = $row->alias;
			}

			if ($row->event_capacity > 0 && $row->total_registrants >= $row->event_capacity)
			{
				$row->eventFull = 1;
			}
			else
			{
				$row->eventFull = 0;
			}
		}

		if ($params->get('hide_full_events'))
		{
			if ($params->get('hide_full_events'))
			{
				$rows = array_filter($rows, function ($row) {
					return $row->eventFull == 0;
				});

				$rows = array_values($rows);
			}
		}

		EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$rows, ['title', 'price_text']]);

		$state = $model->getState();
		$year  = $state->year;
		$month = $state->month;

		$this->data   = EventbookingHelperData::getCalendarData($rows, $year, $month);
		$this->month  = $month;
		$this->year   = $year;
		$this->params = $params;

		$listMonth = [
			Text::_('JANUARY'),
			Text::_('FEBRUARY'),
			Text::_('MARCH'),
			Text::_('APRIL'),
			Text::_('MAY'),
			Text::_('JUNE'),
			Text::_('JULY'),
			Text::_('AUGUST'),
			Text::_('SEPTEMBER'),
			Text::_('OCTOBER'),
			Text::_('NOVEMBER'),
			Text::_('DECEMBER'),];

		$options = [];

		foreach ($listMonth as $key => $monthName)
		{
			$value     = $key + 1;
			$options[] = HTMLHelper::_('select.option', $value, $monthName);
		}

		$this->searchMonth = HTMLHelper::_('select.genericlist', $options, 'month', 'class="input-medium form-select w-auto" onchange="submit();" ', 'value', 'text', (int) $month);

		$options = [];

		for ($i = $year - 3; $i < ($year + 5); $i++)
		{
			$options[] = HTMLHelper::_('select.option', $i, $i);
		}

		$this->searchYear = HTMLHelper::_('select.genericlist', $options, 'year', 'class="input-medium form-select w-auto" onchange="submit();" ', 'value', 'text', $year);

		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$message     = EventbookingHelper::getMessages();

		$categoryIds = array_filter(ArrayHelper::toInteger($this->params->get('category_ids')));

		if (count($categoryIds) == 1)
		{
			$categoryId = $categoryIds[0];
			$category   = EventbookingHelperDatabase::getCategory($categoryId);
			$introText  = $category->description;
		}
		elseif (EventbookingHelper::isValidMessage($this->params->get('intro_text')))
		{
			$introText = $this->params->get('intro_text');
		}
		elseif (EventbookingHelper::isValidMessage($message->{'intro_text' . $fieldSuffix}))
		{
			$introText = $message->{'intro_text' . $fieldSuffix};
		}
		else
		{
			$introText = $message->intro_text;
		}

		EventbookingHelperRoute::$eventsAlias = array_filter($eventsAlias);

		// Use override menu item
		if ($this->params->get('menu_item_id') > 0)
		{
			$this->Itemid = $this->params->get('menu_item_id');
		}

		$this->listMonth = $listMonth;

		$this->introText = $introText;

		$this->state = $model->getState();

		$this->setDocumentMetadata();

		parent::display();
	}

	/**
	 * Display weekly events
	 */
	protected function displayWeeklyView()
	{
		/* @var EventbookingModelCalendar $model */
		$model = $this->getModel();

		$this->events = $model->getEventsByWeek();

		EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$this->events, ['title', 'price_text']]);

		$this->first_day_of_week = $model->getState('date');
		$this->currentDateData   = $model->getCurrentDate();

		parent::display();
	}

	/**
	 * Display daily events
	 */
	protected function displayDailyView()
	{
		EventbookingHelperJquery::colorbox('eb-colorbox-addlocation');

		/* @var EventbookingModelCalendar $model */
		$model = $this->getModel();

		$this->events = $model->getEventsByDaily();
		$this->day    = $model->getState('day');

		EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$this->events, ['title', 'price_text']]);

		$this->currentDateData = $model->getCurrentDate();

		parent::display();
	}
}
