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
use Joomla\CMS\Language\Multilanguage;
use Joomla\Utilities\ArrayHelper;

class EventbookingModelCommoncalendar extends RADModel
{
	/**
	 * Fields which will be returned from SQL query
	 *
	 * @var array
	 */
	public static $fields = [
		'a.id',
		'a.main_category_id',
		'a.parent_id',
		'a.location_id',
		'a.title',
		'a.event_type',
		'a.event_date',
		'a.event_end_date',
		'a.short_description',
		'a.description',
		'a.access',
		'a.registration_access',
		'a.individual_price',
		'a.price_text',
		'a.event_capacity',
		'a.waiting_list_capacity',
		'a.created_by',
		'a.cut_off_date',
		'a.registration_type',
		'a.min_group_number',
		'a.discount_type',
		'a.discount',
		'a.early_bird_discount_type',
		'a.early_bird_discount_date',
		'a.early_bird_discount_amount',
		'a.enable_cancel_registration',
		'a.cancel_before_date',
		'a.params',
		'a.published',
		'a.custom_fields',
		'a.discount_groups',
		'a.discount_amounts',
		'a.registration_start_date',
		'a.registration_handle_url',
		'a.fixed_group_price',
		'a.attachment',
		'a.late_fee_type',
		'a.late_fee_date',
		'a.late_fee_amount',
		'a.event_password',
		'a.currency_code',
		'a.currency_symbol',
		'a.thumb',
		'a.image',
		'a.language',
		'a.alias',
		'a.tax_rate',
		'a.featured',
		'a.has_multiple_ticket_types',
		'a.activate_waiting_list',
		'a.collect_member_information',
		'a.prevent_duplicate_registration',
	];

	/**
	 * The view parameter
	 *
	 * @var \Joomla\Registry\Registry
	 */
	protected $params;

	/**
	 * Build query to get events display on the calendar
	 *
	 * @return JDatabaseQuery
	 */
	protected function buildQuery()
	{
		$config             = EventbookingHelper::getConfig();
		$db                 = $this->getDbo();
		$query              = $db->getQuery(true);
		$locationId         = (int) $this->params->get('location_id');
		$categoryIds        = $this->params->get('category_ids');
		$excludeCategoryIds = $this->params->get('exclude_category_ids');
		$excludeCategoryIds = array_filter(ArrayHelper::toInteger($excludeCategoryIds));
		$categoryIds        = array_filter(ArrayHelper::toInteger($categoryIds));
		$fieldSuffix        = EventbookingHelper::getFieldSuffix();
		$currentDate        = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());

		$query->select(static::$fields)
			->select("DATEDIFF(a.early_bird_discount_date, $currentDate) AS date_diff")
			->select("DATEDIFF(a.event_date, $currentDate) AS number_event_dates")
			->select("TIMESTAMPDIFF(MINUTE, a.late_fee_date, $currentDate) AS late_fee_date_diff")
			->select("TIMESTAMPDIFF(MINUTE, a.event_date, $currentDate) AS event_start_minutes")
			->select("TIMESTAMPDIFF(SECOND, a.registration_start_date, $currentDate) AS registration_start_minutes")
			->select("TIMESTAMPDIFF(MINUTE, a.cut_off_date, $currentDate) AS cut_off_minutes")
			->select('SUM(b.number_registrants) AS total_registrants')
			->select($db->quoteName(['c.name' . $fieldSuffix, 'c.alias' . $fieldSuffix], ['location_name', 'location_alias']))
			->select('c.address AS location_address')
			->select($db->quoteName(['d.color_code', 'd.text_color']))
			->from('#__eb_events AS a')
			->leftJoin('#__eb_registrants AS b ON (a.id = b.event_id ) AND b.group_id = 0 AND (b.published=1 OR (b.published = 0 AND b.payment_method LIKE "os_offline%"))')
			->leftJoin('#__eb_locations AS c ON a.location_id = c.id ')
			->innerJoin('#__eb_categories as d ON a.main_category_id = d.id')
			->where('a.published IN (1, 2)')
			->where('a.hidden = 0')
			->where('a.access in (' . implode(',', Factory::getUser()->getAuthorisedViewLevels()) . ')')
			->group('a.id')
			->order('a.event_date ASC, a.ordering ASC');

		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, ['a.title', 'a.alias', 'a.short_description', 'a.price_text', 'a.registration_handle_url'], $fieldSuffix);
		}

		if ($this->params->get('hide_children_events', 0))
		{
			$query->where('a.parent_id = 0');
		}

		$allCategoryIds = [];

		if ($categoryId = $this->state->get('id'))
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
			$query->where('a.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $allCategoryIds) . '))');
		}

		if ($excludeCategoryIds && !$this->state->mini_calendar)
		{
			$query->where('a.id NOT IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $excludeCategoryIds) . '))');
		}

		if ($locationId)
		{
			$query->where('a.location_id = ' . (int) $locationId);
		}

		$hidePastEventsParam = $this->params->get('hide_past_events', 2);

		if ($hidePastEventsParam == 1 || ($hidePastEventsParam == 2 && $config->hide_past_events))
		{
			$this->applyHidePastEventsFilter($query);
		}

		// Handle publish up and publish down
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		if (Multilanguage::isEnabled())
		{
			$query->where('a.language IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ', "")');
		}

		return $query;
	}

	/**
	 * Apply hide past events filter for calendar
	 *
	 * @param   JDatabaseQuery  $query
	 */
	protected function applyHidePastEventsFilter($query)
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

		$fields = ['a.event_date'];

		if ($config->show_until_end_date)
		{
			$fields[] = 'a.event_end_date';
		}
		else
		{
			$fields[] = 'a.cut_off_date';
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