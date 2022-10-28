<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class EventbookingHelperDatabase
{
	/**
	 * Get category data from database
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function getCategory($id)
	{
		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->select('*')
			->from('#__eb_categories')
			->where('id = ' . (int) $id);

		if ($fieldSuffix)
		{
			self::getMultilingualFields($query, ['name', 'page_title', 'page_heading', 'meta_keywords', 'meta_description', 'description'], $fieldSuffix);
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get event information from database
	 *
	 * @param   int     $id
	 * @param   string  $currentDate
	 * @param   string  $fieldSuffix
	 *
	 * @return mixed
	 */
	public static function getEvent($id, $currentDate = null, $fieldSuffix = null)
	{
		static $events = [];

		$cacheKey = $id . $currentDate . $fieldSuffix;

		if (!array_key_exists($cacheKey, $events))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			if ($fieldSuffix === null)
			{
				$fieldSuffix = EventbookingHelper::getFieldSuffix();
			}

			if (empty($currentDate))
			{
				$currentDate = EventbookingHelper::getServerTimeFromGMTTime();
			}

			$currentDate = $db->quote($currentDate);

			$query->select('a.*, IFNULL(SUM(b.number_registrants), 0) AS total_registrants')
				->from('#__eb_events AS a')
				->select("DATEDIFF(event_date, $currentDate) AS number_event_dates")
				->select("TIMESTAMPDIFF(MINUTE, a.event_date, $currentDate) AS event_start_minutes")
				->select("DATEDIFF($currentDate, a.late_fee_date) AS late_fee_date_diff")
				->select("TIMESTAMPDIFF(SECOND, registration_start_date, $currentDate) AS registration_start_minutes")
				->select("TIMESTAMPDIFF(MINUTE, cut_off_date, $currentDate) AS cut_off_minutes")
				->select("TIMESTAMPDIFF(MINUTE, $currentDate, early_bird_discount_date) AS date_diff")
				->leftJoin('#__eb_registrants AS b ON (a.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.published = 0 AND b.payment_method LIKE "os_offline%")))')
				->where('a.id=' . (int) $id);

			if ($fieldSuffix)
			{
				self::getMultilingualFields($query, ['a.title', 'a.short_description', 'a.description', 'a.meta_keywords', 'a.meta_description'], $fieldSuffix);
			}

			$query->group('a.id');
			$db->setQuery($query);

			$events[$cacheKey] = $db->loadObject();
		}

		return $events[$cacheKey];
	}

	/**
	 * Method to load location object from database
	 *
	 * @param   int     $id
	 * @param   string  $fieldSuffix
	 *
	 * @return mixed
	 */
	public static function getLocation($id, $fieldSuffix = null)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_locations')
			->where('id=' . (int) $id);

		if ($fieldSuffix === null)
		{
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
		}

		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, ['name', 'alias', 'description'], $fieldSuffix);
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to get group registration rates for an event
	 *
	 * @param $eventId
	 *
	 * @return array
	 */
	public static function getGroupRegistrationRates($eventId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_event_group_prices')
			->where('event_id=' . (int) $eventId)
			->order('id');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get all published categories
	 *
	 * @param   string  $order
	 *
	 * @return mixed
	 */
	public static function getAllCategories($order = 'title')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, parent AS parent_id, name AS title')
			->from('#__eb_categories')
			->where('published=1')
			->order($order);
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get all published events
	 *
	 * @param   string  $order
	 * @param   bool    $hidePastEvents
	 * @param   array   $wheres
	 * @param   string  $fieldSuffix
	 *
	 * @return mixed
	 */
	public static function getAllEvents($order = 'title', $hidePastEvents = false, $wheres = [], $fieldSuffix = null)
	{
		$config = EventbookingHelper::getConfig();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$query->select('id, event_date')
			->select($db->quoteName('title' . $fieldSuffix, 'title'))
			->from('#__eb_events')
			->order($order);

		if ($config->get('hide_unpublished_events_from_events_dropdown', '1') === '1')
		{
			$query->where('published = 1');
		}

		if ($hidePastEvents)
		{
			$currentDate = $db->quote(HTMLHelper::_('date', 'Now', 'Y-m-d'));
			$query->where('(DATE(event_date) >= ' . $currentDate . ' OR DATE(event_end_date) >= ' . $currentDate . ')');
		}

		foreach ($wheres as $where)
		{
			$query->where($where);
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get all published countries
	 *
	 * @param   string  $order
	 *
	 * @return mixed
	 */
	public static function getAllCountries($order = 'name')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, name')
			->from('#__eb_countries')
			->where('published')
			->order($order);
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get all locations in the system
	 *
	 * @param   string  $order
	 *
	 * @return mixed
	 */
	public static function getAllLocations($order = 'name')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, name')
			->from('#__eb_locations')
			->where('published = 1')
			->order($order);
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Helper method to get fields from database table in case the site is multilingual
	 *
	 * @param   JDatabaseQuery  $query
	 * @param   array           $fields
	 * @param   string          $fieldSuffix
	 */
	public static function getMultilingualFields(JDatabaseQuery $query, $fields, $fieldSuffix)
	{
		foreach ($fields as $field)
		{
			$alias  = $field;
			$dotPos = strpos($field, '.');

			if ($dotPos !== false)
			{
				$alias = substr($field, $dotPos + 1);
			}

			$query->select($query->quoteName($field . $fieldSuffix, $alias));
		}
	}

	/**
	 * Apply past events filter to query depends on several config options
	 *
	 * @param   JDatabaseQuery  $query
	 * @param   string          $tableAlias
	 */
	public static function applyHidePastEventsFilter($query, $tableAlias = 'tbl.')
	{
		$config = EventbookingHelper::getConfig();

		if ($config->show_upcoming_events)
		{
			$currentDate = Factory::getDbo()->quote(EventbookingHelper::getServerTimeFromGMTTime());
		}
		else
		{
			$currentDate = Factory::getDbo()->quote(HTMLHelper::_('date', 'Now', 'Y-m-d'));
		}

		$fields = [$tableAlias . 'event_date'];

		if ($config->show_until_end_date)
		{
			$fields[] = $tableAlias . 'event_end_date';
		}
		else
		{
			$fields[] = $tableAlias . 'cut_off_date';
		}

		if ($config->show_children_events_under_parent_event)
		{
			$fields[] = $tableAlias . 'max_end_date';
		}

		$conditions = [];

		// Show until current date time greater than event date time
		if ($config->show_upcoming_events)
		{
			foreach ($fields as $field)
			{
				$conditions[] = $field . ' >= ' . $currentDate;
			}
		}
		else
		{
			foreach ($fields as $field)
			{
				$conditions[] = 'DATE(' . $field . ') >= ' . $currentDate;
			}
		}

		$query->where('(' . implode(' OR ', $conditions) . ')');
	}
}
