<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class EventbookingViewCalendarRaw extends RADViewHtml
{
	public function display()
	{
		$currentDateData = EventbookingModelCalendar::getCurrentDateData();

		//Initialize default month and year
		$month = $this->input->getInt('month', 0);
		$year  = $this->input->getInt('year', 0);
		$id    = $this->input->getInt('id', 0);

		if (!$month)
		{
			$month = $currentDateData['month'];
		}

		if (!$year)
		{
			$year = $currentDateData['year'];
		}

		$model = RADModel::getTempInstance('Calendar', 'EventbookingModel');

		$model->setState('month', $month)
			->setState('year', $year)
			->setState('id', $id)
			->setState('mini_calendar', 1);

		$rows        = $model->getData();
		$this->data  = EventbookingHelperData::getCalendarData($rows, $year, $month, true);
		$this->month = $month;
		$this->year  = $year;

		$days     = [];
		$startDay = EventbookingHelper::getConfigValue('calendar_start_date');

		for ($i = 0; $i < 7; $i++)
		{
			$days[$i] = EventbookingHelperData::getDayNameHtmlMini(($i + $startDay) % 7, true);
		}

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

		$this->days      = $days;
		$this->listMonth = $listMonth;

		parent::display();
	}
}
