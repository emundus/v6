<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarViewMap extends JViewLegacy
{

	public function display ($tpl = null)
	{
		$user = JFactory::getUser();
		$access = 0;
		$params = null;

		if (JRequest::getVar('moduleId', null) != null)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('m.*');
			$query->from('#__modules AS m');
			$query->where('id = ' . JRequest::getInt('moduleId'));
			$db->setQuery($query);
			$module = $db->loadObject();

			if ($module != null)
			{
				$params = new JRegistry($module->params);
				$params->set('map_view_lat', $params->get('lat'));
				$params->set('map_view_long', $params->get('long'));
				$access = $module->access;
			}
		}
		else
		{
			$menu = JFactory::getApplication()->getMenu()->getItem(JRequest::getInt('Itemid'));
			$params = $menu->params;
			$access = $menu->access;
		}
		if ($user->authorise('core.admin', 'com_dpcalendar') || in_array((int) $access, $user->getAuthorisedViewLevels()))
		{
			$this->getModel()->setState('parameters.menu', $params);
			$this->params = $params;
		}
		else
		{
			$this->params = $params;
			JError::raiseWarning(0, 'JERROR_ALERTNOAUTHOR');
		}
		$app = JFactory::getApplication();

		// Initialise variables
		$items = $this->get('Items');
		$state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		if ($items === false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		$params = $app->getParams();

		$tmp = clone $state->params;
		$tmp->merge($params);

		$this->params = $tmp;
		$this->items = $items;
		$this->state = $state;

		parent::display($tpl);
	}
}
