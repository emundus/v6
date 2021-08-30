<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class modEBEventsHelper
{
	/**
	 * Get list of events which will be displayed in the module
	 *
	 * @param   JRegistry  $params
	 *
	 * @throws Exception
	 */
	public static function getData($params)
	{
		$displayEventType = $params->get('display_event_type', 'upcoming_events');
		$itemId           = (int) $params->get('item_id', 0);
		$orderBy          = $params->get('order_by', 'a.event_date');
		$orderDirection   = $params->get('order_direction', 'ASC');

		if ($displayEventType == 'upcoming_events')
		{
			if ($orderBy == 'a.event_date' && $orderDirection == 'ASC')
			{
				$model = RADModel::getTempInstance('Upcomingevents', 'EventbookingModel', ['table_prefix' => '#__eb_']);
			}
			else
			{
				$model = RADModel::getTempInstance('Category', 'EventbookingModel', ['table_prefix' => '#__eb_']);

				// Hide past events
				$params->get('hide_past_events', 1);
			}
		}
		else
		{
			$model = RADModel::getTempInstance('Category', 'EventbookingModel', ['table_prefix' => '#__eb_']);

			$params->set('menu_filter_order', $orderBy);
			$params->set('menu_filter_order_dir', $orderDirection);
		}

		$model->setState('limit', $params->get('number_events', 6));
		$model->setState('filter_duration', $params->get('duration_filter'));

		// Convert module parameters for backward compatible purpose
		if (is_string($params->get('category_ids', '')))
		{
			$params->set('category_ids', explode(',', $params->get('category_ids', '')));
		}

		$params->set('location_ids', (array) $params->get('location_id'));

		if ($displayEventType == 'past_events')
		{
			$params->set('only_display_past_events', 1);
		}

		// The module uses different parameter compare to menu parameters, thus we need this line. Do not remove it
		if (!$params->get('show_children_events', 1))
		{
			$params->set('hide_children_events', 1);
		}

		$model->setParams($params);


		$rows = $model->getData();

		foreach ($rows as $row)
		{
			$categories             = $row->categories;
			$row->number_categories = count($categories);

			if ($row->number_categories > 0)
			{
				$itemCategories = [];

				foreach ($categories as $category)
				{
					$itemCategories[] = '<a href="' . EventbookingHelperRoute::getCategoryRoute($category->id, $itemId) . '" class="ebm-category-link">' . $category->name . '</a>';
				}

				$row->categories     = implode('&nbsp;|&nbsp;', $itemCategories);
				$row->itemCategories = $categories;
			}
		}

		return $rows;
	}
}