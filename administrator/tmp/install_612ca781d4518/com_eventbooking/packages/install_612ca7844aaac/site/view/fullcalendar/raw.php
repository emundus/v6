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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class EventbookingViewFullcalendarRaw extends RADView
{
	public function display()
	{
		$rootUri = Uri::root(true);
		$rows    = $this->model->getData();
		$config  = EventbookingHelper::getConfig();
		$Itemid  = Factory::getApplication()->input->getInt('Itemid');

		EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$rows, ['title', 'price_text']]);

		//Set evens alias to EventbookingHelperRoute to improve performance
		$eventsAlias = [];

		foreach ($rows as $row)
		{
			if ($config->insert_event_id)
			{
				$eventsAlias[$row->id] = $row->id . '-' . $row->alias;
			}
			else
			{
				$eventsAlias[$row->id] = $row->alias;
			}
		}

		EventbookingHelperRoute::$eventsAlias = array_filter($eventsAlias);

		$params = EventbookingHelper::getViewParams(Factory::getApplication()->getMenu()->getActive(), ['fullcalendar']);

		if ($config->display_event_in_tooltip)
		{
			EventbookingHelper::callOverridableHelperMethod('Data', 'preProcessEventData', [$rows, 'list']);
		}

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row = $rows[$i];

			if ($config->show_children_events_under_parent_event && $row->parent_id > 0)
			{
				$eventId = $row->parent_id;
			}
			else
			{
				$eventId = $row->id;
			}

			if ($params->get('link_event_to_registration_form') && EventbookingHelperRegistration::acceptRegistration($row))
			{
				if ($row->registration_handle_url)
				{
					$url = $row->registration_handle_url;
				}
				else
				{
					$url = Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $eventId . '&Itemid=' . $Itemid);
				}
			}
			else
			{
				$url = Route::_(EventbookingHelperRoute::getEventRoute($eventId, 0, $Itemid));
			}

			$row->url = $url;

			if ($row->color_code)
			{
				$row->backgroundColor = '#' . $row->color_code;
			}

			if ($row->text_color)
			{
				$row->textColor = '#' . $row->text_color;
			}

			if ($config->show_thumb_in_calendar && $row->thumb)
			{
				$row->thumb = $rootUri . '/media/com_eventbooking/images/thumbs/' . $row->thumb;
			}
			else
			{
				$row->thumb = '';
			}

			if ($config->display_event_in_tooltip)
			{
				$layoutData = [
					'item'     => $row,
					'config'   => $config,
					'nullDate' => Factory::getDbo()->getNullDate(),
					'Itemid'   => $Itemid,
				];

				$row->tooltip = EventbookingHelperHtml::loadCommonLayout('common/calendar_tooltip.php', $layoutData);
			}
			else
			{
				$row->tooltip = '';
			}

			if ($row->event_capacity > 0 && $row->total_registrants >= $row->event_capacity)
			{
				$row->eventFull = 1;
			}
			else
			{
				$row->eventFull = 0;
			}
		}

		if ($params->get('hide_full_events'))
		{
			$rows = array_filter($rows, function ($row) {
				return $row->eventFull == 0;
			});
		}

		echo json_encode($rows);

		Factory::getApplication()->close();
	}
}
