<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR)) {
	return;
}

JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.application.component.model');

class PlgDPCalendarRSEvents extends \DPCalendar\Plugin\DPCalendarPlugin
{
	protected $identifier = 'rs';

	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		if (!JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_rseventspro')) {
			return '';
		}

		$db = JFactory::getDBO();

		$query = "select
				e.*,
				c.title as category,
				l.name as location_name, l.description as location_description, l.address as location_address, l.coordinates as location_coordinates
			  from `#__rseventspro_events` as e
			  inner join `#__rseventspro_taxonomy` as t on e.id=t.ide
			  inner join `#__categories` as c on c.id=t.id
			  left join `#__rseventspro_locations` as l on l.id=e.location
			  where t.id=" . (int)$calendarId;

		$dateCondition = '';
		if ($startDate) {
			$startDate     = $db->Quote($startDate->toSql());
			$dateCondition = 'e.start  >= ' . $startDate;
			if ($endDate !== null) {
				$endDate = $db->Quote($endDate->toSql());

				// Between start and end date
				$dateCondition = 'e.end between ' . $startDate . ' and ' . $endDate . ' or e.start between ' . $startDate . ' and ' . $endDate .
					' or (e.start < ' . $startDate . ' and e.end > ' . $endDate . ') ';

				// If it is a recurring event, repeating end needs to be checked
				$dateCondition .= 'or (e.recurring = 1 and e.repeat_end between ' . $startDate . ' and ' . $endDate;
				$dateCondition .= ' or (e.start < ' . $startDate . ' and e.repeat_end > ' . $endDate . ')) ';
			}
		} else if ($endDate) {
			$dateCondition = '(e.start  <= ' . $endDate . ' or (e.recurring = 1 and e.repeat_end <= ' . $endDate . '))';
		}
		if ($dateCondition) {
			$query .= ' and (' . $dateCondition . ')';
		}
		$db->setQuery($query);
		$events = $db->loadObjectList();

		$text   = array();
		$text[] = 'BEGIN:VCALENDAR';
		if (!empty($events)) {
			foreach ($events as $event) {
				$text[] = 'BEGIN:VEVENT';
				$text[] = 'UID:' . md5($event->id . 'RSEventsPro');
				$text[] = 'CATEGORIES:' . $event->category;
				$text[] = 'SUMMARY:' . $event->name;
				$text[] = 'DESCRIPTION:' . strip_tags($this->replaceNl($event->description));
				$text[] = 'X-ALT-DESC;FMTTYPE=text/html:' . $this->replaceNl($event->description);
				$text[] = 'X-HITS:' . $event->hits;
				$text[] = 'X-URL:' . $event->URL;

				// start
				if ($event->allday) {
					$text[] = 'DTSTART;VALUE=DATE:' . DPCalendarHelper::getDate($event->start, true)->format('Ymd');
				} else {
					$text[] = 'DTSTART:' . DPCalendarHelper::getDate($event->start, false)->format('Ymd\THis\Z');
				}

				// end
				if ($event->allday) {
					$end = DPCalendarHelper::getDate($event->start, true);
					$end->modify('+1 day');
					$text[] = 'DTEND;VALUE=DATE:' . $end->format('Ymd');
				} else {
					$text[] = 'DTEND:' . DPCalendarHelper::getDate($event->end, false)->format('Ymd\THis\Z');
				}

				// recurring event?
				if ($event->recurring) {
					$text[] = $this->createRRule($event);
				}

				// add location + geo (if available)
				if (!is_null($event->location_name)) {
					$text[] = 'LOCATION:' . $event->location_name . (strlen($event->location_address) ? ' (' . $event->location_address . ')' : '');
					if (!is_null($event->location_coordinates)) {
						$text[] = 'GEO:' . str_replace(",", ";", $event->location_coordinates);
					}
				}

				$text[] = 'END:VEVENT';
			}
		}
		$text[] = 'END:VCALENDAR';

		return $text;
	}

	public function fetchCalendars($calendarIds = null)
	{
		if (!JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_rseventspro')) {
			return array();
		}

		$root = JCategories::getInstance('RSEventsPro')->get('root');

		if (empty($root)) {
			return array();
		}

		$calendars = array();
		foreach ($root->getChildren(true) as $calendar) {
			if (!empty($calendarIds) && !in_array($calendar->id, $calendarIds)) {
				continue;
			}
			$calendars[] = $this->createCalendar($calendar->id, $calendar->title, $calendar->description);
		}

		return $calendars;
	}

	protected function createRRule($event)
	{
		$text = 'RRULE:FREQ=';

		switch ($event->repeat_type) {
			default:
			case 1:
				$text .= 'DAILY;';
				break;
			case 2:
				$text .= 'WEEKLY;';
				break;
			case 3:
				$text .= 'MONTHLY;';
				break;
			case 4:
				$text .= 'YEARLY;';
				break;
		}

		// could be zero
		$text .= 'INTERVAL=' . $event->repeat_interval . ';';

		if ($event->repeat_end != JFactory::getDbo()->getNullDate()) {
			$text .= 'UNTIL=' . DPCalendarHelper::getDate($event->repeat_end, false)->format('Ymd\THis\Z') . ';';
		}

		$dayMap = array(
			0 => 'SU',
			1 => 'MO',
			2 => 'TU',
			3 => 'WE',
			4 => 'TH',
			5 => 'FR',
			6 => 'SA'
		);
		switch ($event->repeat_type) {
			default:
			case 1:
			case 2:
				JLoader::import('components.com_rseventspro.helpers.events', JPATH_SITE);
				$rsEventHelper = RSEvent::getInstance($event->id);
				$text          .= 'BYDAY=' . implode(',',
						array_map(function ($x) use ($dayMap) {
							return $dayMap[$x];
						}, $rsEventHelper->repeatEventDays()));
				$text          .= ';';
				break;
			case 3:
				if ($event->repeat_on_type == 1) {
					$text .= 'BYMONTHDAY=' . $event->repeat_on_day;
				}
				if ($event->repeat_on_type == 2) {
					$text .= 'BYDAY=' . $event->repeat_on_day_order . $dayMap[$event->repeat_on_day_type];
				}
				break;
		}

		return $text;
	}
}
