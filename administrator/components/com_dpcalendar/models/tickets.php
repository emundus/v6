<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\Registry\Registry;

JLoader::import('joomla.application.component.modellist');
JLoader::import('components.com_dpcalendar.tables.booking', JPATH_ADMINISTRATOR);

class DPCalendarModelTickets extends JModelList
{

	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id',
					'a.id',
					'name',
					'a.name',
					'state',
					'a.state',
					'start_date',
					'e.start_date',
					'ticket_holder',
					'a.ticket_holder',
					'booking_name',
					'event_title'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_dpcalendar');

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
		$bookingId = $this->getUserStateFromRequest($this->context . '.filter.booking_id', 'filter_booking_id');
		$this->setState('filter.booking_id', !$bookingId ? JFactory::getApplication()->input->get('b_id') : $bookingId);
		$eventId = $this->getUserStateFromRequest($this->context . '.filter.event_id', 'filter_event_id');
		$this->setState('filter.event_id', !$eventId ? JFactory::getApplication()->input->get('e_id') : $eventId);

		$this->setState('params', $params);

		parent::populateState('e.start_date', 'asc');
	}

	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$items = parent::_getList($query, $limitstart, $limit);

		if ($items)
		{
			$user = JFactory::getUser();
			foreach ($items as $item)
			{
				$item->params = new Registry();
				$item->params->set('access-edit', $user->id == $item->user_id || $user->authorise('core.admin', 'com_dpcalendar'));

				$bookingFromSession = JFactory::getSession()->get('booking_id', 0, 'com_dpcalendar');
				if ($user->guest && $bookingFromSession != $item->booking_id)
				{
					$item->params->set('access-edit', false);
				}
			}
		}

		return $items;
	}

	protected function getListQuery()
	{
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($this->getState('list.select', 'a.*'));
		$query->from($db->quoteName('#__dpcalendar_tickets') . ' AS a');

		// Join over the bookings
		$query->select('b.name as booking_name');
		$query->join('LEFT', $db->quoteName('#__dpcalendar_bookings') . ' AS b ON b.id = a.booking_id');

		// Join over the events
		$query->select('e.catid AS event_calid, e.title as event_title, e.start_date, e.end_date, e.all_day, e.show_end_time, e.price as event_prices');
		$query->join('LEFT', $db->quoteName('#__dpcalendar_events') . ' AS e ON e.id = a.event_id');

		// Join over the users for the author.
		$query->select('ua.name AS user_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = b.user_id');

		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int)substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->Quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where(
						'(a.name LIKE ' . $search . ' OR b.name LIKE ' . $search . ' OR ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search .
								 ')');
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where(
						'(a.name LIKE ' . $search . ' OR a.uid LIKE ' . $search . ' OR b.name LIKE ' . $search . ' OR b.email LIKE ' . $search .
								 ' OR e.title LIKE ' . $search . ')');
			}
		}

		$bookingId = $this->getState('filter.booking_id');
		if ($bookingId)
		{
			$query->where('a.booking_id = ' . (int)$bookingId);
		}

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int)$published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1, 2, 3, 4, 5))');
		}

		// Filter by author
		$authorId = $this->getState('filter.ticket_holder');
		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.ticket_holder.include', true) ? '= ' : '<>';
			$query->where('a.user_id ' . $type . (int)$authorId);
		}
		else if ($this->getState('filter.public'))
		{
			$query->where('public = 1');
		}
		else if (!$user->authorise('core.admin', 'com_dpcalendar'))
		{
			if ($user->guest)
			{
				$query->where('public = 1');
			}

			if (JFactory::getApplication()->isSite())
			{
				$query->where('(e.created_by = ' . (int)$user->id . ' or a.user_id = ' . (int)$user->id . ')');
			}
		}

		$eventId = $this->getState('filter.event_id');
		if (is_numeric($eventId))
		{
			$query->where('a.event_id = ' . (int)$eventId);
		}
		if (is_array($eventId))
		{
			JArrayHelper::toInteger($eventId);
			$query->where('a.event_id in (' . implode(',', $eventId) . ')');
		}

		if ($this->getState('filter.my', 0) == 1)
		{
			$query->where('a.user_id = ' . (int)$user->id);
		}

		if ($this->getState('filter.future'))
		{
			$query->where('e.start_date >= ' . $db->q(DPCalendarHelper::getDate()->toSql()));
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'e.start_date')) . ' ' . $db->escape($this->getState('list.direction', 'asc')));

		// Echo nl2br(str_replace('#__', 'a_', $query)); die();
		return $query;
	}

	public function getEvent($eventId = null, $force = false)
	{
		if ($eventId == null)
		{
			$eventId = JFactory::getApplication()->input->get('e_id');
		}
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dpcalendar/models', 'DPCalendarModel');
		$model = JModelLegacy::getInstance('Event', 'DPCalendarModel');
		return $model->getItem($eventId);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.booking_id');
		$id .= ':' . $this->getState('filter.event_id');

		return parent::getStoreId($id);
	}
}
