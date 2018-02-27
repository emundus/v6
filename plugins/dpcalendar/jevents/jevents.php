<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;

if (!JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR)) {
	return;
}

JLoader::import('joomla.filesystem.folder');

class PlgDPCalendarJEvents extends \DPCalendar\Plugin\DPCalendarPlugin
{
	protected $identifier = 'je';

	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, Registry $options)
	{
		if (!JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_jevents')) {
			return '';
		}

		if ($startDate == null) {
			// Default to now
			$startDate = DPCalendarHelper::getDate();
		}
		if ($endDate == null) {
			// Default to max end
			$endDate = DPCalendarHelper::getDate(date('Y-m-d H:i:s', PHP_INT_MAX));
		}

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('DISTINCT e.*, d.*, e.rawdata as ical_data');
		$query->from('#__jevents_vevent e');
		$query->join('LEFT', '#__jevents_vevdetail d ON d.evdet_id = e.detail_id');

		$query->where(($this->params->get('use_categories') ? 'e.catid' : 'e.icsid') . ' = ' . (int)$calendarId);

		$subQuery = $db->getQuery(true);
		$subQuery->select('eventid, min(startrepeat) as min_date, max(endrepeat) as max_date');
		$subQuery->from('#__jevents_repetition');
		$subQuery->group('eventid');

		$query->join('RIGHT', '(' . $subQuery . ') r ON r.eventid = e.detail_id');
		$query->where(
			'(' .
			// Allow if min date is between range
			'r.min_date between ' . $db->q($startDate->toSql(false)) . ' and ' . $db->q($endDate->toSql(false)) .
			// Allow if max date is between range
			' or r.max_date between ' . $db->q($startDate->toSql(false)) . ' and ' . $db->q($endDate->toSql(false)) .
			// Allow if range is between min and max
			' or (r.min_date < ' . $db->q($startDate->toSql(false)) . ' and r.max_date > ' . $db->q($endDate->toSql(false)) . ')' .
			')'
		);

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$text   = array();
		$text[] = 'BEGIN:VCALENDAR';
		foreach ($rows as $event) {
			$data = @unserialize($event->ical_data);
			if (!is_array($data)) {
				$this->log('Ical Data could not being parsed of the event ' . $event->summary);
				continue;
			}

			$text[] = 'BEGIN:VEVENT';
			$text[] = 'X-ACCESS:' . $event->access;
			$text[] = 'X-MODIFIED:' . $event->modified;
			$text[] = 'X-HITS:' . $event->hits;
			$text[] = 'X-URL:' . $event->url;
			foreach ($data as $key => $value) {
				switch (strtolower($key)) {
					case 'dtstart':
						if ($data['allDayEvent'] == 'on') {
							$key   = 'DTSTART;VALUE=DATE';
							$value = DPCalendarHelper::getDate($value, true)->format('Ymd');
						} else {
							$value = DPCalendarHelper::getDate($value, false)->format('Ymd\THis\Z');
						}
						break;
					case 'dtend':
						if ($data['allDayEvent'] == 'on') {
							$key   = 'DTEND;VALUE=DATE';
							$value = DPCalendarHelper::getDate($value + 86400, true)->format('Ymd');
						} else {
							$value = DPCalendarHelper::getDate($value, false)->format('Ymd\THis\Z');
						}
						break;
					case 'rrule':
						$tmp = '';
						foreach ($value as $rKey => $rValue) {
							if (is_array($rValue)) {
								continue;
							}

							if ($rKey == 'BYYEARDAY') {
								$rValue = str_replace('+', '', $rValue);
							}

							if ($rKey == 'UNTIL') {
								$rValue = DPCalendarHelper::getDate($rValue + 86400, false)->format('Ymd\T000000\Z');
							}
							$tmp .= $rKey . '=' . $rValue . ';';
						}
						$value = trim($tmp, ';');
						if (stripos($value, 'FREQ=NONE') !== false) {
							$key = null;
						}
						break;
					case 'description':
						$value = \DPCalendar\Helper\Ical::icalEncode($this->replaceNl($value));
						break;
					case 'publish_down':
					case 'publish_down2':
					case 'publish_up':
					case 'publish_up2':
					case 'alldayevent':
					case 'locakevent':
					case 'x-extrainfo':
						$key = null;
						break;
				}
				if ($key === null) {
					continue;
				}
				$text[] = $key . ':' . $value;
			}

			$text[] = 'END:VEVENT';
		}
		$text[] = 'END:VCALENDAR';

		return $text;
	}

	public function fetchCalendars($calendarIds = null)
	{
		if (!JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_jevents')) {
			return array();
		}

		if ($this->params->get('use_categories')) {
			$calendars = array();
			foreach (JCategories::getInstance('JEvents')->get('root')->getChildren(true) as $category) {
				if (!empty($calendarIds) && !in_array($category->id, $calendarIds)) {
					continue;
				}
				$calendars[] = $this->createCalendar($category->id, $category->title, $category->description);
			}

			return $calendars;
		}

		$query = "select * from #__jevents_icsfile";
		if (!empty($calendarIds)) {
			$query .= " where ics_id IN (" . implode(',', $calendarIds) . ')';
		}
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$calendars = array();
		foreach ($rows as $calendar) {
			$calendars[] = $this->createCalendar($calendar->ics_id, $calendar->label, '');
		}

		return $calendars;
	}
}
