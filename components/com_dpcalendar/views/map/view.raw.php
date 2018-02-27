<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;

class DPCalendarViewMap extends \DPCalendar\View\BaseView
{
	public function display($tpl = null)
	{
		$model = JModelLegacy::getInstance('Events', 'DPCalendarModel');
		$this->setModel($model, true);

		parent::display($tpl);
	}

	public function init()
	{
		$access = 0;
		$params = null;

		if ($this->input->getInt('module-id')) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('m.*');
			$query->from('#__modules AS m');
			$query->where('id = ' . $this->input->getInt('module-id'));
			$db->setQuery($query);
			$module = $db->loadObject();

			if ($module != null) {
				$params = new Registry($module->params);
				$params->set('map_view_lat', $params->get('lat'));
				$params->set('map_view_long', $params->get('long'));
				$params->set('map_date_format', $params->get('date_format', 'm.d.Y'));
				$access = $module->access;
			}
		} else {
			$menu   = $this->app->getMenu()->getItem($this->input->getInt('Itemid'));
			$params = $menu->params;
			$access = $menu->access;
		}

		$this->params->merge($params);

		if ($this->user->authorise('core.admin', 'com_dpcalendar') || in_array((int)$access, $this->user->getAuthorisedViewLevels())) {
			$this->getModel()->setState('parameters.menu', $params);
		} else {
			$this->app->enqueueMessage('JERROR_ALERTNOAUTHOR');
		}

		$this->getModel()->setState('category.id', $params->get('ids'));

		$context = 'com_dpcalendar.map.';

		$location = $this->app->getUserStateFromRequest($context . 'location', 'location');
		if (empty($location)) {
			$location = $params->get('map_view_lat', 47) . ',' . $params->get('map_view_long', 4);
		}
		$this->getModel()->setState('filter.location', $location);
		$this->getModel()->setState('filter.radius', $this->app->getUserStateFromRequest($context . 'radius', 'radius'));
		$this->getModel()->setState('filter.length-type', $this->app->getUserStateFromRequest($context . 'length-type', 'length-type'));
		$this->getModel()->setState('filter.search', $this->app->getUserStateFromRequest($context . 'search', 'search'));

		$this->handleDate('start', $params);
		$this->handleDate('end', $params);

		// Initialise variables
		$items = $this->get('Items');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors));
		}

		if ($items === false) {
			throw new Exception(JText::_('JGLOBAL_CATEGORY_NOT_FOUND'), 404);
		}

		$this->items = $items;
	}

	private function handleDate($type, $params)
	{
		$context = 'com_dpcalendar.map.';
		$date    = null;

		// Get the date from input
		$value = $this->app->input->getString($type . '-date', 'notset');

		// New date from request
		if ($value && $value != 'notset') {
			$date = DPCalendarHelper::getDateFromString($value, 0, true, $params->get('map_date_format', 'm.d.Y'));
		}

		// If not set, get it from the session
		if (!$date && $value == 'notset' && $value = $this->app->getUserState($context . 'end-date')) {
			$date = \DPCalendar\Helper\DPCalendarHelper::getDate($value);
		}

		// Transform the date when exists
		if ($date) {
			$date->setTime(23, 59, 59);
		}

		// Set the date
		$this->state->set('list.' . $type . '-date', $date);
		$this->app->setUserState($context . $type . '-date', $date ? $date->toSql() : null);
	}
}
