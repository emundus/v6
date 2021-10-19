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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class EventbookingViewFullcalendarHtml extends RADViewHtml
{
	public function display()
	{
		$document = Factory::getDocument();
		$rootUri  = Uri::root(true);
		$document->addScript($rootUri . '/media/com_eventbooking/fullcalendar/lib/moment.min.js');
		$document->addScript($rootUri . '/media/com_eventbooking/fullcalendar/fullcalendar.min.js');
		$document->addStyleSheet($rootUri . '/media/com_eventbooking/fullcalendar/fullcalendar.min.css');

		$this->params = $this->getParams();

		if ($this->params->get('menu_item_id') > 0)
		{
			$this->Itemid = $this->params->get('menu_item_id');
		}

		$this->setDocumentMetadata();

		parent::display();
	}

	/**
	 * Method to get full calendar options
	 *
	 * @return array
	 */
	protected function getCalendarOptions()
	{
		$config = EventbookingHelper::getConfig();
		$date   = new DateTime('now', new DateTimeZone(Factory::getApplication()->get('offset')));
		$year   = $this->params->get('default_year') ?: $date->format('Y');
		$month  = $this->params->get('default_month') ?: $date->format('m');
		$month  = str_pad($month, 2, '0', STR_PAD_LEFT);

		$buttons = [];

		if ($this->params->get('show_month_button'))
		{
			$buttons[] = 'month';
		}

		if ($this->params->get('show_week_button'))
		{
			$buttons[] = 'agendaWeek';
		}

		if ($this->params->get('show_day_button'))
		{
			$buttons[] = 'agendaDay';
		}

		if (count($buttons) == 1)
		{
			$buttons = [];
		}

		$defaultView = $this->params->get('default_view', 'month');


		$options = [
			'header'           => [
				'left'   => 'prev,next today',
				'center' => 'title',
				'right'  => implode(',', $buttons),
			],
			'defaultView'      => $defaultView,
			'defaultDate'      => $year . '-' . $month . '-' . $date->format('d'),
			'navLinks'         => true,
			'editable'         => false,
			'eventLimit'       => false,
			'weekends'         => (bool) $this->params->get('show_weekend', 1),
			'eventSources'     => [
				Route::_('index.php?option=com_eventbooking&view=fullcalendar&format=raw&Itemid=' . $this->Itemid, false),
			],
			'monthNames'       => [
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
				Text::_('DECEMBER'),
			],
			'monthNamesShort'  => [
				Text::_('JANUARY_SHORT'),
				Text::_('FEBRUARY_SHORT'),
				Text::_('MARCH_SHORT'),
				Text::_('APRIL_SHORT'),
				Text::_('MAY_SHORT'),
				Text::_('JUNE_SHORT'),
				Text::_('JULY_SHORT'),
				Text::_('AUGUST_SHORT'),
				Text::_('SEPTEMBER_SHORT'),
				Text::_('OCTOBER_SHORT'),
				Text::_('NOVEMBER_SHORT'),
				Text::_('DECEMBER_SHORT'),
			],
			'dayNames'         => [
				Text::_('SUNDAY'),
				Text::_('MONDAY'),
				Text::_('TUESDAY'),
				Text::_('WEDNESDAY'),
				Text::_('THURSDAY'),
				Text::_('FRIDAY'),
				Text::_('SATURDAY'),
			],
			'dayNamesShort'    => [
				Text::_('SUN'),
				Text::_('MON'),
				Text::_('TUE'),
				Text::_('WED'),
				Text::_('THU'),
				Text::_('FRI'),
				Text::_('SAT'),
			],
			'displayEventTime' => (bool) $config->show_event_time,
			'dayOfMonthFormat' => $this->params->get('day_of_month_format', 'ddd D/M'),
			'slotLabelFormat'  => $this->params->get('slot_label_format', 'h(:mm)a'),
			'buttonText'       => [
				'today' => Text::_('EB_TODAY'),
				'month' => Text::_('EB_MONTH'),
				'week'  => Text::_('EB_WEEK'),
				'day'   => Text::_('EB_DAY'),
			],
			'firstDay'         => (int) $config->calendar_start_date,
			'views'            => [
				'month'      => [
					'titleFormat' => $this->params->get('title_format_month', 'MMMM YYYY'),
					'timeFormat'  => $this->params->get('time_format_month', 'h:mm a'),
					//'columnFormat' => 'ddd',
					'showNonCurrentDates' => (boolean) $this->params->get('show_non_current_dates', false),
				],
				'agendaWeek' => [
					'titleFormat' => $this->params->get('title_format_week', 'MMM D YYYY'),
					'timeFormat'  => $this->params->get('time_format_week', 'h:mm a'),
					//'columnFormat' => 'ddd M/D',
				],
				'agendaDay'  => [
					'titleFormat' => $this->params->get('title_format_day', 'MMMM D YYYY'),
					'timeFormat'  => $this->params->get('time_format_day', 'h:mm a'),
					//'columnFormat' => 'dddd'
				],
			],
		];

		return $options;
	}
}
