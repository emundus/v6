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

class PlgDPCalendarCSV extends \DPCalendar\Plugin\SyncPlugin
{
	protected $identifier = 'c';

	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return '';
		}

		/** @var \Joomla\Registry\Registry $params */
		$params = $calendar->params;

		// The content
		$content = DPCalendarHelper::fetchContent($params->get('uri', ''));

		if ($content instanceof Exception) {
			$this->log($content->getMessage());

			return '';
		}

		// Remove UTF-8 BOM
		$content = str_replace("\xEF\xBB\xBF", '', $content);

		$lines = explode("\n", trim($content));
		if (!$lines) {
			return;
		}

		$k = array(
			$params->get('all_day', 'all_day')         => $params->get('all_day', 'all_day'),
			$params->get('start_date', 'start_date')   => $params->get('start_date', 'start_date'),
			$params->get('end_date', 'end_date')       => $params->get('end_date', 'end_date'),
			$params->get('title', 'title')             => $params->get('title', 'title'),
			$params->get('description', 'description') => $params->get('description', 'description'),
			$params->get('rrule', 'rrule')             => $params->get('rrule', 'rrule'),
			$params->get('location', 'location')       => $params->get('location', 'location'),
			$params->get('alias', 'alias')             => $params->get('alias', 'alias'),
			$params->get('color', 'color')             => $params->get('color', 'color')
		);
		if ($params->get('has_header', true)) {
			$k = array_flip(str_getcsv(array_shift($lines)));
		}

		$text   = [];
		$text[] = 'BEGIN:VCALENDAR';
		foreach ($lines as $key => $line) {
			$line = trim($line);
			if (empty($line)) {
				continue;
			}
			try {
				$text[] = 'BEGIN:VEVENT';

				$data = str_getcsv($line);

				$allDay = $this->getValue($calendar, $data, $k, 'all_day') == 1;

				$startDate = DPCalendarHelper::getDate($this->getValue($calendar, $data, $k, 'start_date'), $allDay);
				if ($allDay) {
					$text[] = 'DTSTART;VALUE=DATE:' . $startDate->format('Ymd');
				} else {
					$text[] = 'DTSTART:' . $startDate->format('Ymd\THis\Z');
				}
				$endDate = DPCalendarHelper::getDate($this->getValue($calendar, $data, $k, 'end_date'), $allDay);
				if ($allDay) {
					$text[] = 'DTEND;VALUE=DATE:' . $endDate->format('Ymd');
				} else {
					$text[] = 'DTEND:' . $endDate->format('Ymd\THis\Z');
				}

				$text[] = 'UID:' . md5($this->getValue($calendar, $data, $k, 'title') . 'CSV');
				$text[] = 'CATEGORIES:' . $this->params->get('title-' . $calendarId, 'Default');
				$text[] = 'SUMMARY:' . $this->getValue($calendar, $data, $k, 'title');
				$text[] = 'DESCRIPTION:' . str_replace("\n\r", '', $this->getValue($calendar, $data, $k, 'description'));

				$rrule = $this->getValue($calendar, $data, $k, 'rrule', false);
				if (empty($rrule)) {
					// Try out only rule for a more convenient way
					$old = $params->get('rrule');
					$params->set('rrule', 'rule');
					$rrule = $this->getValue($calendar, $data, $k, 'rrule', false);
					$params->set('rrule', $old);
				}
				if (!empty($rrule)) {
					$text[] = 'RRULE:' . $rrule;
				}

				$text[] = 'LOCATION:' . $this->getValue($calendar, $data, $k, 'location', false);

				$text[] = 'X-URL:' . $this->getValue($calendar, $data, $k, 'url', false);
				$text[] = 'X-ALIAS:' . $this->getValue($calendar, $data, $k, 'alias', false);
				$text[] = 'X-COLOR:' . $this->getValue($calendar, $data, $k, 'color', false);

				$text[] = 'END:VEVENT';
			} catch (Exception $e) {
				$this->log($e->getMessage());
			}
		}
		$text[] = 'END:VCALENDAR';

		return $text;
	}

	private function getValue($calendar, $data, $k, $key, $throw = true)
	{
		$column = $calendar->params->get($key, $key);
		if (!key_exists($column, $k)) {
			if ($throw) {
				throw new InvalidArgumentException('Key ' . $key . ' not found to get the value from in the CSV file.');
			}

			return null;
		}
		if (!key_exists($k[$column], $data)) {
			if ($throw) {
				throw new InvalidArgumentException('Key ' . $key . ' not found to get the value from in the CSV file.');
			}

			return null;
		}

		return $data[$k[$column]];
	}
}
