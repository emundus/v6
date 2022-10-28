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

class EventbookingModelCalendar extends EventbookingModelCommoncalendar
{
	protected $currentDate;

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */

	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->insert('year', 'int', 0)
			->insert('month', 'int', 0)
			->insert('date', 'string', '')
			->insert('day', 'string', '')
			->insert('id', 'int', 0)
			->insert('mini_calendar', 'int', 0)
			->insert('mini_calendar_item_id', 'int', 0);

		$this->params = EventbookingHelper::getViewParams(Factory::getApplication()->getMenu()->getActive(), ['calendar']);
	}

	/**
	 * Get monthly events
	 *
	 * @return array|mixed
	 */
	public function getData()
	{
		$config  = EventbookingHelper::getConfig();
		$db      = $this->getDbo();
		$date    = Factory::getDate('now', Factory::getApplication()->get('offset'));
		$nowDate = $date->format('d');

		if ($this->state->mini_calendar_item_id)
		{
			$this->params = EventbookingHelper::getViewParams(Factory::getApplication()->getMenu()->getItem($this->state->mini_calendar_item_id), ['calendar']);
		}

		$year  = $this->state->get('year') ?: $this->params->get('default_year');
		$month = $this->state->get('month') ?: $this->params->get('default_month');

		if (!$year)
		{
			$year = $date->format('Y');
		}

		if (!$month)
		{
			$month = $date->format('m');
		}

		$this->state->set('month', $month)
			->set('year', $year);

		$this->currentDate = static::getCurrentDateData($year . '-' . $month . '-' . $nowDate);

		// Calculate start date and end date of the given month
		$date->setDate($year, $month, 1);
		$date->setTime(0, 0, 0);
		$startDate = $db->quote($date->toSql(true));

		$date->setDate($year, $month, $date->daysinmonth);
		$date->setTime(23, 59, 59);
		$endDate = $db->quote($date->toSql(true));

		$query = $this->buildQuery();

		if ($config->show_multiple_days_event_in_calendar && !$this->state->mini_calendar)
		{
			$query->where("((a.event_date BETWEEN $startDate AND $endDate) OR (a.event_end_date BETWEEN $startDate AND $endDate) OR (a.event_date <= $startDate AND a.event_end_date >= $endDate))");
		}
		else
		{
			$query->where("a.event_date BETWEEN $startDate AND $endDate");
		}

		$db->setQuery($query);

		if ($config->show_multiple_days_event_in_calendar && !$this->state->mini_calendar)
		{
			$rows      = $db->loadObjectList();
			$rowEvents = [];

			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row      = $rows[$i];
				$arrDates = explode('-', $row->event_date);

				if ($arrDates[0] == $year && $arrDates[1] == $month)
				{
					$rowEvents[] = $row;
				}

				$startDateParts = explode(' ', $row->event_date);
				$startTime      = strtotime($startDateParts[0]);
				$startDateTime  = strtotime($row->event_date);
				$endDateParts   = explode(' ', $row->event_end_date);
				$endTime        = strtotime($endDateParts[0]);
				$count          = 0;

				while ($startTime < $endTime)
				{
					$count++;
					$rowNew             = clone $row;
					$rowNew->event_date = date('Y-m-d H:i:s', $startDateTime + $count * 24 * 3600);
					$arrDates           = explode('-', $rowNew->event_date);

					if ($arrDates[0] == $year && $arrDates[1] == $month)
					{
						$rowEvents[]            = $rowNew;
						$rowNew->original_event = $row;
					}

					$startTime += 24 * 3600;
				}
			}

			return $rowEvents;
		}

		return $db->loadObjectList();
	}

	/**
	 * Get events of the given week
	 *
	 * @return array
	 */
	public function getEventsByWeek()
	{
		$db       = $this->getDbo();
		$query    = $this->buildQuery();
		$config   = EventbookingHelper::getConfig();
		$startDay = (int) $config->calendar_start_date;

		// get first day of week of today
		$startWeekDate = $this->state->date;

		if (!EventbookingHelper::isValidDate($startWeekDate))
		{
			$startWeekDate = '';
		}

		if ($startWeekDate)
		{
			$date = Factory::getDate($startWeekDate, Factory::getApplication()->get('offset'));
		}
		else
		{
			$currentDateData = self::getCurrentDateData();
			$date            = Factory::getDate($currentDateData['start_week_date'], Factory::getApplication()->get('offset'));
			$this->state->set('date', $date->format('Y-m-d', true));
		}

		$weekStartDate = Factory::getDate($this->state->date, Factory::getApplication()->get('offset'));
		$activeDate    = Factory::getDate('now', Factory::getApplication()->get('offset'));
		$activeDate->setDate($weekStartDate->format('Y'), $weekStartDate->format('m'), $activeDate->format('d'));

		$this->currentDate = static::getCurrentDateData($activeDate->format('Y-m-d'));

		$date->setTime(0, 0, 0);
		$startDate = $db->quote($date->toSql(true));
		$date->modify('+6 day');
		$date->setTime(23, 59, 59);
		$endDate = $db->quote($date->toSql(true));
		$query->where("(a.event_date BETWEEN $startDate AND $endDate)")
			->order('a.event_date ASC, a.ordering ASC');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$eventsGroupedByWeekDay = [];

		foreach ($rows as $row)
		{
			$row->short_description             = HTMLHelper::_('content.prepare', $row->short_description);
			$weekDay                            = (date('w', strtotime($row->event_date)) - $startDay + 7) % 7;
			$eventsGroupedByWeekDay[$weekDay][] = $row;
		}

		return $eventsGroupedByWeekDay;
	}

	/**
	 * Get events of the given date
	 *
	 * @return mixed
	 */
	public function getEventsByDaily()
	{
		$db    = $this->getDbo();
		$query = $this->buildQuery();

		$day = $this->state->day;

		if (!EventbookingHelper::isValidDate($day))
		{
			$day = '';
		}

		if (!$day)
		{
			$currentDateData = self::getCurrentDateData();
			$day             = $currentDateData['current_date'];
			$this->state->set('day', $day);
		}

		$this->currentDate = static::getCurrentDateData($this->state->day);

		$startDate = $db->quote($day . " 00:00:00");
		$endDate   = $db->quote($day . " 23:59:59");


		$query->where("(a.event_date BETWEEN $startDate AND $endDate)")
			->order('a.event_date ASC, a.ordering ASC');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$row->short_description = HTMLHelper::_('content.prepare', $row->short_description);
		}

		return $rows;
	}

	/**
	 * Get calculated data for current date
	 *
	 * @return mixed
	 */
	public function getCurrentDate()
	{
		return $this->currentDate;
	}

	/**
	 * Get data of current date
	 *
	 * @return array
	 */
	public static function getCurrentDateData($currentDate = 'now')
	{
		$config               = EventbookingHelper::getConfig();
		$startDay             = (int) $config->calendar_start_date;
		$data                 = [];
		$date                 = new DateTime($currentDate, new DateTimeZone(Factory::getApplication()->get('offset')));
		$data['year']         = $date->format('Y');
		$data['month']        = $date->format('m');
		$data['current_date'] = $date->format('Y-m-d');

		if ($startDay == 0)
		{
			$date->modify('Sunday last week');
		}
		else
		{
			$date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
		}

		$data['start_week_date'] = $date->format('Y-m-d');
		$data['end_week_date']   = $date->modify('+6 day')->format('Y-m-d');

		return $data;
	}
}
