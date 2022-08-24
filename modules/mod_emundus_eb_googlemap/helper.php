<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

class modEventBookingGoogleMapHelper
{
	/**
	 * @param   Joomla\Registry\Registry  $params
	 * @param   int                       $Itemid
	 *
	 * @return array
	 */
	public static function loadAllLocations($params, $Itemid)
	{
		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);

		$categoryIds        = array_filter(ArrayHelper::toInteger($params->get('category_ids', [])));
		$excludeCategoryIds = array_filter(ArrayHelper::toInteger($params->get('exclude_category_ids', [])));
		$locationIds        = $params->get('location_ids');
		$numberEvents       = $params->get('number_events', 10);
		$hidePastEvents     = $params->get('hide_past_events', 1);
		$currentDate        = $db->quote(HTMLHelper::_('date', 'Now', 'Y-m-d'));
		$filterDuration     = $params->get('duration_filter');

		$nullDate    = $db->quote($db->getNullDate());
		$nowDate     = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		$query->select('id, `lat`, `long`, address')
			->select($db->quoteName('name' . $fieldSuffix, 'name'))
			->from('#__eb_locations')
			->where('`lat` != ""')
			->where('`long` != ""')
			->where('published = 1');

		if ($locationIds)
		{
			$query->where('id IN (' . implode(',', array_filter($locationIds)) . ')');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$query->clear()
			->select('a.id, a.title, a.main_category_id')
			->from('#__eb_events AS a')
			->order('a.event_date');

		foreach ($rows as $row)
		{
			$query->clear('where')
				->where('a.location_id = ' . $row->id)
				->where('a.published = 1')
				->where('a.hidden = 0')
				->where('a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
				->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

			if ($categoryIds)
			{
				$query->where('a.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $categoryIds) . '))');
			}

			if ($excludeCategoryIds)
			{
				$query->where('a.id NOT IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $excludeCategoryIds) . '))');
			}

			if ($hidePastEvents)
			{
				if ($config->show_until_end_date)
				{
					$query->where('(DATE(a.event_date) >= ' . $currentDate . ' OR DATE(a.event_end_date) >= ' . $currentDate . ')');
				}
				else
				{
					$query->where('(DATE(a.event_date) >= ' . $currentDate . ' OR DATE(a.cut_off_date) >= ' . $currentDate . ')');
				}
			}


			switch ($filterDuration)
			{
				case 'today':
					$date = Factory::getDate('now', $config->get('offset'));
					$query->where('DATE(a.event_date) = ' . $db->quote($date->format('Y-m-d', true)));
					break;
				case 'tomorrow':
					$date = Factory::getDate('tomorrow', $config->get('offset'));
					$query->where('DATE(a.event_date) = ' . $db->quote($date->format('Y-m-d', true)));
					break;
				case 'this_week':
					$date   = Factory::getDate('now', $config->get('offset'));
					$monday = clone $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
					$monday->setTime(0, 0, 0);
					$fromDate = $monday->toSql(true);
					$sunday   = clone $date->modify('Sunday this week');
					$sunday->setTime(23, 59, 59);
					$toDate = $sunday->toSql(true);
					$query->where('a.event_date >= ' . $db->quote($fromDate))
						->where('a.event_date <= ' . $db->quote($toDate));
					break;
				case 'next_week':
					$date   = Factory::getDate('now', $config->get('offset'));
					$monday = clone $date->modify(('Sunday' == $date->format('l')) ? 'Monday this week' : 'Monday next week');
					$monday->setTime(0, 0, 0);
					$fromDate = $monday->toSql(true);
					$sunday   = clone $date->modify('Sunday next week');
					$sunday->setTime(23, 59, 59);
					$toDate = $sunday->toSql(true);
					$query->where('a.event_date >= ' . $db->quote($fromDate))
						->where('a.event_date <= ' . $db->quote($toDate));
					break;
				case 'this_month':
					$date = Factory::getDate('first day of this month', $config->get('offset'));
					$date->setTime(0, 0, 0);
					$fromDate = $date->toSql(true);
					$date     = Factory::getDate('last day of this month', $config->get('offset'));
					$date->setTime(23, 59, 59);
					$toDate = $date->toSql(true);
					$query->where('a.event_date >= ' . $db->quote($fromDate))
						->where('a.event_date <= ' . $db->quote($toDate));
					break;
				case 'next_month':
					$date = Factory::getDate('first day of next month', $config->get('offset'));
					$date->setTime(0, 0, 0);
					$fromDate = $date->toSql(true);
					$date     = Factory::getDate('last day of next month', $config->get('offset'));
					$date->setTime(23, 59, 59);
					$toDate = $date->toSql(true);
					$query->where('a.event_date >= ' . $db->quote($fromDate))
						->where('a.event_date <= ' . $db->quote($toDate));
					break;
			}

			$query->order('a.event_date, a.ordering');

			$db->setQuery($query, 0, $numberEvents);
			$row->events = $db->loadObjectList();
		}

		// Remove locations without events
		$rows = array_filter($rows, function ($row) {
			return count($row->events) > 0;
		});

		reset($rows);

		foreach ($rows as $row)
		{
			$popupContent   = [];
			$popupContent[] = '<div class="row-fluid">';
			$popupContent[] = '<ul class="bubble">';
			$popupContent[] = '<li class="location_name"><h4>' . $row->name . '</h4></li>';
			$popupContent[] = '<p class="location_address">' . $row->address . '</p>';
			$popupContent[] = '</ul>';

			$popupContent[] = '<ul>';

			foreach ($row->events as $event)
			{
				$popupContent[] = '<li><h4>' . str_replace(['calendrier-des-activites','activites-2/toutes-les-activites-2'],'accueil-activites',HTMLHelper::link(Route::_(EventbookingHelperRoute::getEventRoute($event->id, $event->main_category_id, $Itemid)), addslashes($event->title))) . '</h4></li>';
			}

			$popupContent[] = '</ul>';

			$row->popupContent = implode("", $popupContent);
		}

		return array_values($rows);
	}

    public static function getLocationsByFilter($params){
        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);

        $query->select('distinct el.id as location')
            ->from($db->quoteName('#__eb_events','ee'))
            ->leftJoin($db->quoteName('#__eb_event_categories','ec').' ON '.$db->quoteName('ec.event_id').' = '.$db->quoteName('ee.id'))
            ->leftJoin($db->quoteName('#__eb_locations','el').' ON '.$db->quoteName('el.id').' = '.$db->quoteName('ee.location_id'));
        if((isset($params['category_id']) && !empty($params['category_id'])) && (!isset($params['sub_category_id']) || empty($params['sub_category_id']))){
            $query->where($db->quoteName('ec.category_id') . ' IN (' . $params['category_id'] . ')');
        }
        if(isset($params['sub_category_id']) && !empty($params['sub_category_id'])){
            $query->where($db->quoteName('ec.category_id') . ' IN (' . $params['sub_category_id'] . ')');
        }
        $db->setQuery($query);
        return $db->loadColumn();
    }
}
