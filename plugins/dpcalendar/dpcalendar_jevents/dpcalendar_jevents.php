<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.plugin', JPATH_ADMINISTRATOR);
JLoader::import('joomla.filesystem.folder');
if (! class_exists('DPCalendarPlugin'))
{
	return;
}

class PlgDPCalendarDPCalendar_JEvents extends DPCalendarPlugin
{

	protected $identifier = 'je';

	protected function getContent ($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		if (! JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_jevents'))
		{
			return '';
		}

		$query = "select *, e.rawdata as ical_data from #__jevents_vevent e, #__jevents_vevdetail d where e.detail_id = d.evdet_id and icsid=" .
				 (int) $calendarId;
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$text = array();
		$text[] = 'BEGIN:VCALENDAR';
		foreach ($rows as $event)
		{
			$text[] = 'BEGIN:VEVENT';
			$text[] = 'X-ACCESS:' . $event->access;
			$text[] = 'X-MODIFIED:' . $event->modified;
			$text[] = 'X-HITS:' . $event->hits;
			$text[] = 'X-URL:' . $event->url;

			$data = unserialize($event->ical_data);
			foreach ($data as $key => $value)
			{
				switch (strtolower($key))
				{
					case 'dtstart':
						if ($data['allDayEvent'] == 'on')
						{
							$key = 'DTSTART;VALUE=DATE';
							$value = DPCalendarHelper::getDate($value, true)->format('Ymd');
						}
						else
						{
							$value = DPCalendarHelper::getDate($value, false)->format('Ymd\THis\Z');
						}
						break;
					case 'dtend':
						if ($data['allDayEvent'] == 'on')
						{
							$key = 'DTEND;VALUE=DATE';
							$value = DPCalendarHelper::getDate($value + 86400, true)->format('Ymd');
						}
						else
						{
							$value = DPCalendarHelper::getDate($value, false)->format('Ymd\THis\Z');
						}
						break;
					case 'rrule':
						$tmp = '';
						foreach ($value as $rKey => $rValue)
						{
							if ($rKey == 'UNTIL')
							{
								$rValue = DPCalendarHelper::getDate($rValue + 86400, false)->format('Ymd\T000000\Z');
							}
							$tmp .= $rKey . '=' . $rValue . ';';
						}
						$value = trim($tmp, ';');
						if (stripos($value, 'FREQ=NONE') !== false)
						{
							$value = null;
						}
						break;
					case 'description':
						$value = DPCalendarHelperIcal::icalEncode($this->replaceNl($value));
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
				if ($key === null)
				{
					continue;
				}
				$text[] = $key . ':' . $value;
			}

			$text[] = 'END:VEVENT';
		}
		$text[] = 'END:VCALENDAR';
		return $text;
	}

	public function fetchCalendars ($calendarIds = null)
	{
		if (! JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_jevents'))
		{
			return array();
		}

		$query = "select * from #__jevents_icsfile";
		if (! empty($calendarIds))
		{
			$query .= " where ics_id IN (" . implode(',', $calendarIds) . ')';
		}
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$calendars = array();
		foreach ($rows as $calendar)
		{
			$calendars[] = $this->createCalendar($calendar->ics_id, $calendar->label, '');
		}
		return $calendars;
	}
}
