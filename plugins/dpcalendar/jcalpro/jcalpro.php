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

JLoader::import('joomla.application.categories');
JModelLegacy::addIncludePath(JPATH_PLUGINS . '/dpcalendar/jcalpro/models', 'DPCalendarJCalProModel');

class PlgDPCalendarJCalPro extends \DPCalendar\Plugin\DPCalendarPlugin
{
	protected $identifier = 'jc';
	private $root = null;

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$root = JCategories::getInstance('JCalPro');
		if ($root != null) {
			$this->root = $root->get('root');
		}
	}

	public function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$model = JModelLegacy::getInstance('Events', 'DPCalendarJCalProModel');
		if (empty($model)) {
			return array();
		}
		$model->getState();
		$model->setState('filter.catid', $calendarId);
		$model->setState('filter.start_date', $startDate);
		$model->setState('filter.end_date', $endDate);
		$model->setState('list.limit', 0);
		$events = $model->getItems();

		$text   = array();
		$text[] = 'BEGIN:VCALENDAR';
		if (!empty($events)) {
			foreach ($events as $e) {
				if (empty($e)) {
					continue;
				}

				$text[] = 'BEGIN:VEVENT';
				$text[] = 'UID:' . md5($e->id . 'JcalPro');
				$text[] = 'CATEGORIES:' . $this->params->get('title-' . $calendarId, 'Default');
				$text[] = 'SUMMARY:' . $e->title;
				$text[] = 'DESCRIPTION:' . $this->replaceNl($e->description);

				$text[] = 'X-COLOR:' . str_replace('#', '', $e->color);
				$text[] = 'X-LANGUAGE:' . $e->language;

				if (!empty($e->location_data)) {
					$text[] = 'LOCATION:' . $this->replaceNl($e->location_data->address);
					if (!empty($e->location_data->latitude) && !empty($e->location_data->longitude)) {
						$text[] = 'GEO:' . $e->location_data->latitude . ';' . $e->location_data->longitude;
					}
				}

				$duration_type = (int)$e->duration_type;
				switch ($duration_type) {
					// All day event
					case JCalPro::JCL_EVENT_DURATION_ALL:
						$text[] = 'DTSTART;VALUE=DATE:' . $e->utc_datetime->format('Ymd');
						$text[] = 'DTEND;VALUE=DATE:' . $e->utc_datetime->format('Ymd');
						break;

					// No end date
					case JCalPro::JCL_EVENT_DURATION_NONE:
						$text[] = 'DTSTART:' . $e->utc_datetime->format('Ymd\THis\Z');
						$e->utc_datetime->modify('+1 hour');
						$text[] = 'DTEND:' . $e->utc_datetime->format('Ymd\THis\Z');
						break;

					// Start and end date
					case JCalPro::JCL_EVENT_DURATION_DATE:
					default:
						$text[] = 'DTSTART:' . $e->utc_datetime->format('Ymd\THis\Z');
						if (isset($e->utc_end_datetime) && !empty($e->utc_end_datetime)) {
							$text[] = 'DTEND:' . $e->utc_end_datetime->format('Ymd\THis\Z');
						} else {
							$tmp = clone $e->utc_datetime;
							$tmp->setTime(23, 59, 00);
							$text[] = 'DTEND:' . $tmp->format('Ymd\THis\Z');
						}
				}
				$text[] = 'END:VEVENT';
			}
		}
		$text[] = 'END:VCALENDAR';

		return $text;
	}

	protected function fetchCalendars($calendarIds = null)
	{
		$calendars = array();
		if ($this->root == null) {
			return $calendars;
		}
		foreach ($this->root->getChildren(true) as $calendar) {
			if (!empty($calendarIds) && !in_array($calendar->id, $calendarIds)) {
				continue;
			}
			$calendars[] = $this->createCalendar($calendar->id, $calendar->title, $calendar->description);
		}

		return $calendars;
	}
}
