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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

class EventbookingModelList extends RADModelList
{
	/**
	 * Menu parameters
	 *
	 * @var \Joomla\Registry\Registry
	 */
	protected $params;

	/**
	 * Fields which will be returned from SQL query
	 *
	 * @var array
	 */
	public static $fields = [
		'tbl.id',
		'tbl.main_category_id',
		'tbl.location_id',
		'tbl.title',
		'tbl.event_type',
		'tbl.event_date',
		'tbl.event_end_date',
		'tbl.short_description',
		'tbl.description',
		'tbl.access',
		'tbl.registration_access',
		'tbl.individual_price',
		'tbl.price_text',
		'tbl.event_capacity',
		'tbl.waiting_list_capacity',
		'tbl.created_by',
		'tbl.cut_off_date',
		'tbl.registration_type',
		'tbl.min_group_number',
		'tbl.discount_type',
		'tbl.discount',
		'tbl.early_bird_discount_type',
		'tbl.early_bird_discount_date',
		'tbl.early_bird_discount_amount',
		'tbl.enable_cancel_registration',
		'tbl.cancel_before_date',
		'tbl.params',
		'tbl.published',
		'tbl.custom_fields',
		'tbl.discount_groups',
		'tbl.discount_amounts',
		'tbl.registration_start_date',
		'tbl.registration_handle_url',
		'tbl.fixed_group_price',
		'tbl.attachment',
		'tbl.late_fee_type',
		'tbl.late_fee_date',
		'tbl.late_fee_amount',
		'tbl.event_password',
		'tbl.currency_code',
		'tbl.currency_symbol',
		'tbl.thumb',
		'tbl.image',
		'tbl.language',
		'tbl.alias',
		'tbl.tax_rate',
		'tbl.featured',
		'tbl.has_multiple_ticket_types',
		'tbl.activate_waiting_list',
		'tbl.collect_member_information',
		'tbl.prevent_duplicate_registration',
	];

	/**
	 * Fields which could be translated
	 *
	 * @var array
	 */
	protected static $translatableFields = [
		'tbl.title',
		'tbl.alias',
		'tbl.short_description',
		'tbl.description',
		'tbl.price_text',
		'tbl.registration_handle_url',
	];

	/**
	 * The fields which can be used for soring events
	 *
	 * @var array
	 */
	public static $sortableFields = [
		'tbl.event_date',
		'tbl.ordering',
		'tbl.title',
	];

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['table']           = '#__eb_events';
		$config['remember_states'] = false;

		parent::__construct($config);

		$this->state->insert('id', 'int', 0);

		$ebConfig     = EventbookingHelper::getConfig();
		$this->params = EventbookingHelper::getViewParams(Factory::getApplication()->getMenu()->getActive(), [$this->getName()]);

		if ((int) $this->params->get('display_num'))
		{
			$this->setState('limit', (int) $this->params->get('display_num'));
		}
		elseif ((int ) $ebConfig->number_events)
		{
			$this->state->setDefault('limit', (int) $ebConfig->number_events);
		}

		if ($ebConfig->order_events == 2)
		{
			$this->state->set('filter_order', 'tbl.event_date');
		}
		else
		{
			$this->state->set('filter_order', 'tbl.ordering');
		}

		if ($ebConfig->order_direction == 'desc')
		{
			$this->state->set('filter_order_Dir', 'DESC');
		}
		else
		{
			$this->state->set('filter_order_Dir', 'ASC');
		}

		$this->state->insert('search', 'string', '')
			->insert('filter_duration', 'string', $this->params->get('default_duration_filter'))
			->insert('location_id', 'int', 0)
			->insert('category_id', 'id', 0);
	}

	/**
	 * Method to get the current parent category
	 *
	 * @return stdClass|null
	 * @throws Exception
	 */
	public function getCategory()
	{
		if ($categoryId = (int) $this->getState('id'))
		{
			$category = EventbookingHelperDatabase::getCategory($categoryId);

			if ($category)
			{
				// Process content plugin for category description
				$category->description = HTMLHelper::_('content.prepare', $category->description);
			}

			return $category;
		}

		return null;
	}

	/**
	 * Pre-process data before returning to the view for displaying
	 *
	 * @param   array  $rows
	 */
	protected function beforeReturnData($rows)
	{
		if (empty($rows))
		{
			return;
		}

		foreach ($rows as $row)
		{
			if (!$row->image)
			{
				continue;
			}

			$row->image = EventbookingHelperHtml::getCleanImagePath($row->image);
		}

		EventbookingHelper::callOverridableHelperMethod('Data', 'preProcessEventData', [$rows, 'list']);
	}

	/**
	 * Builds SELECT columns list for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryColumns(JDatabaseQuery $query)
	{
		$db          = $this->getDbo();
		$config      = EventbookingHelper::getConfig();
		$currentDate = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		$fieldsToSelect = static::$fields;

		if ($fieldSuffix)
		{
			$fieldsToSelect = array_diff($fieldsToSelect, static::$translatableFields);
		}

		$query->select($fieldsToSelect)
			->select("DATEDIFF(tbl.early_bird_discount_date, $currentDate) AS date_diff")
			->select("DATEDIFF(tbl.event_date, $currentDate) AS number_event_dates")
			->select("TIMESTAMPDIFF(MINUTE, tbl.late_fee_date, $currentDate) AS late_fee_date_diff")
			->select("TIMESTAMPDIFF(MINUTE, tbl.event_date, $currentDate) AS event_start_minutes")
			->select("TIMESTAMPDIFF(SECOND, tbl.registration_start_date, $currentDate) AS registration_start_minutes")
			->select("TIMESTAMPDIFF(MINUTE, tbl.cut_off_date, $currentDate) AS cut_off_minutes")
			->select($db->quoteName(['c.name' . $fieldSuffix, 'c.alias' . $fieldSuffix], ['location_name', 'location_alias']))
			->select('c.address AS location_address')
			->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants');

		if ($config->show_event_creator)
		{
			$query->select('u.name as creator_name');
		}

		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, static::$translatableFields, $fieldSuffix);
		}

		return $this;
	}

	/**
	 * Builds JOINS clauses for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryJoins(JDatabaseQuery $query)
	{
		$config = EventbookingHelper::getConfig();

		$query->leftJoin(
			'#__eb_registrants AS b ON (tbl.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.published = 0 AND b.payment_method LIKE "os_offline%")))')
			->leftJoin('#__eb_locations AS c ON tbl.location_id = c.id ');

		if ($config->show_event_creator)
		{
			$query->leftJoin('#__users as u ON tbl.created_by = u.id');
		}

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		/* @var JApplicationSite $app */
		$app    = Factory::getApplication();
		$db     = $this->getDbo();
		$user   = Factory::getUser();
		$state  = $this->getState();
		$config = EventbookingHelper::getConfig();

		$categoryIds        = $this->params->get('category_ids');
		$excludeCategoryIds = $this->params->get('exclude_category_ids');
		$locationIds        = $this->params->get('location_ids');
		$fromDate           = trim($this->params->get('from_date'));
		$toDate             = trim($this->params->get('to_date'));

		$categoryIds        = array_filter(ArrayHelper::toInteger($categoryIds));
		$excludeCategoryIds = array_filter(ArrayHelper::toInteger($excludeCategoryIds));
		$locationIds        = array_filter(ArrayHelper::toInteger($locationIds));


		$this->applyChildrenEventsFilter($query);

		$query->where('tbl.published IN(1, 2)')
			->where('tbl.hidden = 0')
			->where('tbl.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');

		$categoryId = $this->state->id ?: $this->state->category_id;

		$allCategoryIds = [];

		if ($categoryId)
		{
			if ($config->show_events_from_all_children_categories)
			{
				$allCategoryIds = EventbookingHelperData::getAllChildrenCategories($categoryId);
			}
			else
			{
				$allCategoryIds = [$categoryId];
			}
		}

		if ($categoryIds)
		{
			$allCategoryIds = array_merge($allCategoryIds, $categoryIds);
		}

		if ($allCategoryIds)
		{
			$query->where('tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $allCategoryIds) . '))');
		}

		if ($excludeCategoryIds)
		{
			$query->where('tbl.id NOT IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $excludeCategoryIds) . '))');
		}

		if ($locationIds)
		{
			$query->where('tbl.location_id IN (' . implode(',', $locationIds) . ')');
		}

		if ($state->location_id)
		{
			$query->where('tbl.location_id=' . $state->location_id);
		}

		if ($this->params->get('only_show_featured_events'))
		{
			$query->where('tbl.featured = 1');
		}

		if ($this->params->get('only_display_past_events'))
		{
			$query->where('tbl.event_date < ' . $this->getDbo()->quote(EventbookingHelper::getServerTimeFromGMTTime()));
		}

		$filterCity = $state->filter_city ?: $this->params->get('city');

		if ($filterCity)
		{
			$query->where(' tbl.location_id IN (SELECT id FROM #__eb_locations WHERE LOWER(`city`) = ' . $db->quote(StringHelper::strtolower($filterCity)) . ')');
		}

		if ($state->filter_state)
		{
			$query->where(' tbl.location_id IN (SELECT id FROM #__eb_locations WHERE LOWER(`state`) = ' . $db->quote(StringHelper::strtolower($state->filter_state)) . ')');
		}

		$createdBy = $this->params->get('created_by') ?: $state->created_by;

		if ($createdBy)
		{
			$query->where('tbl.created_by =' . (int) $createdBy);
		}

		if ($fromDate)
		{
			$query->where('tbl.event_date >= ' . $this->getDbo()->quote($fromDate));
		}

		if ($toDate)
		{
			$query->where('tbl.event_date <= ' . $this->getDbo()->quote($toDate));
		}

		// Apply duration filter
		if ($state->filter_duration)
		{
			list($fromDate, $toDate) = EventbookingHelper::getDateDuration($state->filter_duration, true);

			if ($fromDate && $toDate)
			{
				$query->where('tbl.event_date >= ' . $db->quote($fromDate))
					->where('tbl.event_date <= ' . $db->quote($toDate));
			}
		}

		$this->applyKeywordFilter($query);

		if ($app->getLanguageFilter())
		{
			$query->where('tbl.language IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ', "")');
		}

		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
		$query->where('(tbl.publish_up = ' . $nullDate . ' OR tbl.publish_up <= ' . $nowDate . ')')
			->where('(tbl.publish_down = ' . $nullDate . ' OR tbl.publish_down >= ' . $nowDate . ')');


		if ($fieldSuffix = EventbookingHelper::getFieldSuffix())
		{
			$query->where('LENGTH(' . $db->quoteName('tbl.title' . $fieldSuffix) . ') > 0');
		}

		return $this;
	}

	/**
	 * Builds a GROUP BY clause for the query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryGroup(JDatabaseQuery $query)
	{
		$query->group('tbl.id');

		return $this;
	}

	/**
	 * Builds a generic ORDER BY clause based on the model's state
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryOrder(JDatabaseQuery $query)
	{
		$config = EventbookingHelper::getConfig();

		$sort = $this->state->filter_order;

		// This command is needed for order passed from module parameter
		$sort = str_replace('a.', 'tbl.', $sort);

		if (!in_array($sort, static::$sortableFields))
		{
			$sort = 'tbl.event_date';
		}

		$direction = strtoupper($this->state->filter_order_Dir);

		if (!in_array($direction, ['ASC', 'DESC']))
		{
			$direction = '';
		}

		// Display featured events at the top if configured
		if ($config->display_featured_events_on_top)
		{
			$query->order('tbl.featured DESC');
		}

		if ($sort)
		{
			$query->order(trim($sort . ' ' . $direction));
		}

		return $this;
	}

	/**
	 * Method to apply hide past events filter
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return void
	 */
	protected function applyHidePastEventsFilter(JDatabaseQuery $query)
	{
		EventbookingHelperDatabase::applyHidePastEventsFilter($query);
	}

	/**
	 * Method to apply keyword filter, make it easier to customize keyword search behavior
	 *
	 * @param   JDatabaseQuery  $query
	 */
	protected function applyKeywordFilter(JDatabaseQuery $query)
	{
		if (!$this->state->search)
		{
			return;
		}

		$db          = $this->getDbo();
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		if ($config->get('search_events', 'exact') == 'exact')
		{
			$search = $db->quote('%' . $db->escape($this->state->search, true) . '%', false);
			$query->where("(LOWER(" . $db->quoteName('tbl.title' . $fieldSuffix) . ") LIKE $search OR LOWER(" . $db->quoteName('tbl.short_description' . $fieldSuffix) . ") LIKE $search OR LOWER(" . $db->quoteName('tbl.description' . $fieldSuffix) . ") LIKE $search)");
		}
		else
		{
			$words = explode(' ', $this->state->search);

			$wheres = [];

			foreach ($words as $word)
			{
				$word     = $db->quote('%' . $db->escape($word, true) . '%', false);
				$wheres[] = 'LOWER(' . $db->quoteName('tbl.title' . $fieldSuffix) . ') LIKE LOWER(' . $word . ')';
				$wheres[] = 'LOWER(' . $db->quoteName('tbl.short_description' . $fieldSuffix) . ') LIKE LOWER(' . $word . ')';
				$wheres[] = 'LOWER(' . $db->quoteName('tbl.description' . $fieldSuffix) . ') LIKE LOWER(' . $word . ')';
			}

			$query->where('(' . implode(' OR ', $wheres) . ')');
		}
	}

	/**
	 * Method to children events filter
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return void
	 */
	protected function applyChildrenEventsFilter($query)
	{
		$config = EventbookingHelper::getConfig();

		if ($this->params->get('hide_children_events', 0) || $config->show_children_events_under_parent_event)
		{
			$query->where('tbl.parent_id = 0');
		}
	}
}
