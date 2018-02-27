<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\Utilities\ArrayHelper;

JLoader::import('joomla.application.component.modellist');
JLoader::import('components.com_dpcalendar.tables.booking', JPATH_ADMINISTRATOR);

class DPCalendarModelBookings extends JModelList
{

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id',
				'a.id',
				'name',
				'a.name',
				'state',
				'a.state',
				'book_date',
				'a.book_date',
				'user_name',
				'created_by',
				'a.created_by',
				'event_id',
				'a.event_id'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_dpcalendar');

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);

		$this->setState('filter.my', 0);

		$this->setState('params', $params);

		parent::populateState('a.book_date', 'desc');

		if ($app->isClient('site')) {
			$value = $app->input->get('start', 0, 'uint');
			$this->setState('list.start', $value);
		}
	}

	protected function getListQuery()
	{
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($this->getState('list.select', 'a.*'));
		$query->from($db->quoteName('#__dpcalendar_bookings') . ' AS a');

		// Join over the tickets
		$query->select('count(t.id) as amount_tickets');
		$query->join('LEFT', $db->quoteName('#__dpcalendar_tickets') . ' AS t ON t.booking_id = a.id');

		// Join over the users for the author.
		$query->select('ua.name AS user_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.user_id');

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int)substr($search, 3));
			} elseif (stripos($search, 'author:') === 0) {
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(a.name LIKE ' . $search . ' OR ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			} else {
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.name LIKE ' . $search . ' OR a.email LIKE ' . $search . ' OR a.uid LIKE ' . $search . ')');
			}
		}

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int)$published);
		} elseif ($published === '') {
			$query->where('(a.state IN (0, 1, 3, 4, 5))');
		}

		// Filter by author
		$authorId = $this->getState('filter.created_by');
		if (is_numeric($authorId)) {
			$type = $this->getState('filter.created_by.include', true) ? '= ' : '<>';
			$query->where('a.user_id ' . $type . (int)$authorId);
		}

		$eventId = $this->getState('filter.event_id');
		if (is_numeric($eventId)) {
			$query->where('t.event_id = ' . (int)$eventId);
		}
		if (is_array($eventId)) {
			ArrayHelper::toInteger($eventId);
			$query->where('t.event_id in (' . implode(',', $eventId) . ')');
		}

		// Access rights
		if ($user->guest) {
			// Don't allow to list bookings as guest
			$query->where('1 > 1');
		}

		if ($this->getState('filter.my', 0) == 1) {
			$query->where('a.user_id = ' . (int)$user->id);
		}

		// On front end if we are not an admin only bookings are visible where we are the author of the event
		if (JFactory::getApplication()->isClient('site') && !$user->authorise('core.admin', 'com_dpcalendar')) {
			// Join over the events
			$query->join('LEFT', $db->quoteName('#__dpcalendar_events') . ' AS e ON e.id = t.event_id');
			$query->where('e.created_by = ' . (int)$user->id);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.book_date')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		$query->group('a.id');

		return $query;
	}

	protected function _getListCount($query)
	{
		$query = clone $query;
		$query->clear('select')
			->clear('order')
			->clear('limit')
			->clear('offset')
			->clear('group')
			->select('COUNT(distinct a.id)');

		$this->_db->setQuery($query);

		return (int)$this->_db->loadResult();
	}
}
