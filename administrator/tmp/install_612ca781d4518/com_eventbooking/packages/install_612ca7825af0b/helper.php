<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class modEBSliderHelper
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
		$config           = EventbookingHelper::getConfig();
		$displayEventType = $params->get('display_event_type', 'upcoming_events');

		$orderBy        = $params->get('order_by', 'a.event_date');
		$orderDirection = $params->get('order_direction', 'ASC');

		if ($displayEventType == 'upcoming_events')
		{
			if ($orderBy == 'a.event_date' && $orderDirection == 'ASC')
			{
				$model = RADModel::getTempInstance('Upcomingevents', 'EventbookingModel', ['table_prefix' => '#__eb_']);
			}
			else
			{
				$model = RADModel::getTempInstance('Category', 'EventbookingModel', ['table_prefix' => '#__eb_']);

				// Hide past events because it is configured to display upcoming events
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

		foreach ($rows as $event)
		{
			if ($config->show_discounted_price)
			{
				$price = $event->discounted_price;
			}
			else
			{
				$price = $event->individual_price;
			}

			if ($event->price_text)
			{
				$priceDisplay = $event->price_text;
			}
			elseif ($price > 0)
			{
				$symbol       = $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;
				$priceDisplay = EventbookingHelper::formatCurrency($price, $config, $symbol);
			}
			elseif ($config->show_price_for_free_event)
			{
				$priceDisplay = Text::_('EB_FREE');
			}
			else
			{
				$priceDisplay = '';
			}

			$event->priceDisplay = $priceDisplay;
		}

		return $rows;
	}
}