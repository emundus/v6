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

class PlgDPCalendarSQL extends \DPCalendar\Plugin\SyncPlugin
{
	protected $identifier = 'sq';

	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);

		if (empty($calendar)) {
			return '';
		}

		$params = $calendar->params;
		$text   = array();
		try {
			$option = array();

			$option['driver']   = $params->get('driver');
			$option['host']     = $params->get('host_name');
			$option['user']     = $params->get('user');
			$option['password'] = $params->get('pwd');
			$option['database'] = $params->get('db_name');

			$db              = JDatabaseDriver::getInstance($option);
			$existingColumns = $db->getTableColumns($params->get('table_name'));

			$query = $db->getQuery(true);

			$columns = array();

			$columns[] = $params->get('id_column');
			$columns[] = $params->get('title_column');
			$columns[] = $params->get('description_column');
			$columns[] = $params->get('start_date_column');
			$columns[] = $params->get('end_date_column');

			if (key_exists($params->get('all_day_column'), $existingColumns)) {
				$columns[] = $params->get('all_day_column');
			}
			if (key_exists($params->get('rrule_column'), $existingColumns)) {
				$columns[] = $params->get('rrule_column');
			}
			if (key_exists($params->get('location_column'), $existingColumns)) {
				$columns[] = $params->get('location_column');
			}
			if (key_exists($params->get('url_column'), $existingColumns)) {
				$columns[] = $params->get('url_column');
			}
			if (key_exists($params->get('alias_column'), $existingColumns)) {
				$columns[] = $params->get('alias_column');
			}
			if (key_exists($params->get('color_column'), $existingColumns)) {
				$columns[] = $params->get('color_column');
			}
			if (key_exists($params->get('image_column'), $existingColumns)) {
				$columns[] = $params->get('image_column');
			}

			$query->select($db->quoteName($columns));
			$query->from($db->quoteName($params->get('table_name')));

			$startColumn   = $params->get('start_date_column');
			$endColumn     = $params->get('end_date_column');
			$startDate     = $db->quote($startDate->toSql());
			$dateCondition = $startColumn . '  >= ' . $startDate;
			if ($endDate !== null) {
				$endDate       = $db->quote($endDate->toSql());
				$dateCondition = '(' . $endColumn . ' between ' . $startDate . ' and ' . $endDate . ' or ' . $startColumn . ' between ' . $startDate .
					' and ' . $endDate . ' or (' . $startColumn . ' < ' . $startDate . ' and ' . $endColumn . ' > ' . $endDate . '))';
			}

			$now           = DPCalendarHelper::getDate();
			$dateCondition .= ' or ' . $db->quote($now->toSql()) . ' between ' . $startColumn . ' and ' . $endColumn . '';

			if (key_exists($params->get('all_day_column'), $existingColumns)) {
				$dateCondition .= ' or (' . $startColumn . '=' . $db->quote($now->format('Y-m-d')) . ' and ' . $params->get('all_day_column') . '=1)';
				$dateCondition .= ' or (' . $endColumn . '=' . $db->quote($now->format('Y-m-d')) . ' and ' . $params->get('all_day_column') . '=1)';
			}

			$query->where('(' . $dateCondition . ')');

			if ($condition = $params->get('where_condition')) {
				$query->where($condition);
			}

			$db->setQuery($query);

			$rows = $db->loadObjectList();
			if (is_array($rows)) {
				$text[] = 'BEGIN:VCALENDAR';
				foreach ($rows as $row) {
					$text[] = 'BEGIN:VEVENT';

					$startDate = DPCalendarHelper::getDate($row->$startColumn);
					$endDate   = DPCalendarHelper::getDate($row->$endColumn);
					$allDay    = false;
					if (key_exists($params->get('all_day_column'), $existingColumns)) {
						$allDay = $row->{$params->get('all_day_column')} == 1;
					} else {
						$allDay = $startDate->format('H:i') == '00:00' && $endDate->format('H:i') == '00:00';
					}

					if ($allDay) {
						$text[] = 'DTSTART;VALUE=DATE:' . $startDate->format('Ymd');
					} else {
						$text[] = 'DTSTART:' . $startDate->format('Ymd\THis\Z');
					}

					if ($allDay && $startDate->format('U') == $endDate->format('U')) {
						$endDate->modify('+1 day');
					}

					if ($allDay) {
						$text[] = 'DTEND;VALUE=DATE:' . $endDate->format('Ymd');
					} else {
						$text[] = 'DTEND:' . $endDate->format('Ymd\THis\Z');
					}

					$text[] = 'UID:' . $row->{$params->get('id_column')};
					$text[] = 'CATEGORIES:Default';
					$text[] = 'SUMMARY:' . $row->{$params->get('title_column')};
					$text[] = 'DESCRIPTION:' . $this->replaceNl(nl2br($row->{$params->get('description_column')}));

					if (key_exists($params->get('rrule_column'), $existingColumns) && $row->{$params->get('rrule_column')}) {
						$text[] = 'RRULE:' . $row->{$params->get('rrule_column')};
					}
					if (key_exists($params->get('location_column'), $existingColumns)) {
						$text[] = 'LOCATION:' . $row->{$params->get('location_column')};
					}
					if (key_exists($params->get('url_column'), $existingColumns)) {
						$text[] = 'X-URL:' . $row->{$params->get('url_column')};
					}
					if (key_exists($params->get('alias_column'), $existingColumns)) {
						$text[] = 'X-ALIAS:' . $row->{$params->get('alias_column')};
					}
					if (key_exists($params->get('color_column'), $existingColumns)) {
						$text[] = 'X-COLOR:' . $row->{$params->get('color_column')};
					}
					if (key_exists($params->get('image_column'), $existingColumns)) {
						$text[] = 'X-IMAGE:' . $row->{$params->get('image_column')};
					}

					$text[] = 'END:VEVENT';
				}

				$text[] = 'END:VCALENDAR';
			}
		} catch (Exception $e) {
			$this->log($e->getMessage());
		}

		return $text;
	}
}
