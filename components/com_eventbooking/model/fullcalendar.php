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

class EventbookingModelFullcalendar extends EventbookingModelCommoncalendar
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */

	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->insert('id', 'int', 0)
			->insert('start', 'string', '')
			->insert('mini_calendar', 'int', 0)
			->insert('end', 'string');

		$this->params = EventbookingHelper::getViewParams(Factory::getApplication()->getMenu()->getActive(), ['fullcalendar']);
	}

	/**
	 * Get monthly events
	 *
	 * @return array|mixed
	 */
	public function getData()
	{
		$db     = $this->getDbo();
		$config = EventbookingHelper::getConfig();
		$date   = Factory::getDate('now', Factory::getApplication()->get('offset'));
		$year   = $this->params->get('default_year') ?: $date->format('Y');
		$month  = $this->params->get('default_month') ?: $date->format('m');

		if (strlen($this->state->start) > 0)
		{
			$this->state->start = substr($this->state->start, 0, 10);
		}

		if (strlen($this->state->end))
		{
			$this->state->end = substr($this->state->end, 0, 10);
		}

		// Calculate start date and end date of the given month
		if (EventbookingHelper::isValidDate($this->state->start))
		{
			$startDate = $this->state->start;
		}
		else
		{
			$date->setDate($year, $month, 1);
			$date->setTime(0, 0, 0);
			$startDate = $date->toSql(true);
		}

		if (EventbookingHelper::isValidDate($this->state->end))
		{
			$endDate = $this->state->end;
		}
		else
		{
			$date->setDate($year, $month, $date->daysinmonth);
			$date->setTime(23, 59, 59);
			$endDate = $date->toSql(true);
		}

		$startDate = $db->quote($startDate);
		$endDate   = $db->quote($endDate);

		$query = $this->buildQuery();
		$query->select($db->quoteName(['a.event_date', 'a.event_end_date'], ['start', 'end']));

		if ($config->show_multiple_days_event_in_calendar)
		{
			$query->where("((a.event_date BETWEEN $startDate AND $endDate) OR (a.event_end_date BETWEEN $startDate AND $endDate) OR (a.event_date <= $startDate AND a.event_end_date >= $endDate))");
		}
		else
		{
			$query->where("a.event_date BETWEEN $startDate AND $endDate");
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
