<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

class EventbookingModelCommonEvents extends RADModelList
{
	/**
	 * Selected event ids which will be exported
	 *
	 * @var array
	 */
	protected $eventIds = [];

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->insert('filter_category_id', 'int', 0)
			->insert('filter_location_id', 'int', 0)
			->insert('filter_events', 'int', 1)
			->insert('filter_upcoming_events', 'int', 0)
			->setDefault('filter_order', 'tbl.event_date');
	}

	/**
	 * Get list of categories belong to each event before it is displayed
	 *
	 * @param   array  $rows
	 */
	protected function beforeReturnData($rows)
	{
		if (count($rows))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->clear()
				->select('id, name')
				->from('#__eb_locations');
			$db->setQuery($query);
			$locations = $db->loadObjectList('id');

			$query->clear()
				->select('a.name, b.main_category FROM #__eb_categories AS a')
				->innerJoin('#__eb_event_categories AS b ON a.id = b.category_id')
				->order('a.ordering');

			foreach ($rows as $row)
			{
				if (isset($locations[$row->location_id]))
				{
					$row->location = $locations[$row->location_id]->name;
				}

				$query->where('event_id=' . $row->id);
				$db->setQuery($query);
				$categories           = $db->loadObjectList();
				$categoryNames        = [];
				$additionalCategories = [];
				foreach ($categories as $category)
				{
					$categoryNames[] = $category->name;
					if ($category->main_category)
					{
						$row->category = $category->name;
					}
					else
					{
						$additionalCategories[] = $category->name;
					}
				}
				$row->category_name         = implode(' | ', $categoryNames);
				$row->additional_categories = implode(' | ', $additionalCategories);
				$query->clear('where');
			}
		}
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*, vl.title AS access_level, SUM(rgt.number_registrants) AS total_registrants');

		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin('#__viewlevels AS vl ON vl.id = tbl.access')
			->leftJoin('#__eb_registrants AS rgt ON (tbl.id = rgt.event_id AND rgt.group_id = 0 AND (rgt.published=1 OR (rgt.published = 0 AND rgt.payment_method LIKE "os_offline%")))');

		return $this;
	}

	/**
	 * Build where clase of the query
	 *
	 * @see RADModelList::buildQueryWhere()
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		if (!empty($this->eventIds))
		{
			$query->where('tbl.id IN (' . implode(',', $this->eventIds) . ')');
		}

		if ($this->state->filter_category_id)
		{
			$allCategoryIds = EventbookingHelperData::getAllChildrenCategories($this->state->filter_category_id);
			$query->where('tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $allCategoryIds) . '))');
		}

		if ($this->state->filter_location_id)
		{
			$query->where('tbl.location_id=' . $this->state->filter_location_id);
		}

		if ($this->state->filter_events == 1)
		{
			$currentDate = $this->getDbo()->quote(HTMLHelper::_('date', 'Now', 'Y-m-d'));
			$query->where('(DATE(tbl.event_date) >= ' . $currentDate . ' OR DATE(tbl.event_end_date) >= ' . $currentDate . ')');
		}
		elseif ($this->state->filter_events == 2)
		{
			$query->where('tbl.parent_id = 0');
		}

		if ($this->state->filter_upcoming_events)
		{
			$currentDate = $this->getDbo()->quote(EventbookingHelper::getServerTimeFromGMTTime());
			$query->where('tbl.event_date >= ' . $currentDate);
		}

		return parent::buildQueryWhere($query);
	}

	protected function buildQueryGroup(JDatabaseQuery $query)
	{
		$query->group('tbl.id');

		return $this;
	}

	/**
	 * Setter method to set selected event ids which will be exported
	 *
	 * @param   array  $eventIds
	 */
	public function setEventIds($eventIds = [])
	{
		$this->eventIds = $eventIds;
	}
}
