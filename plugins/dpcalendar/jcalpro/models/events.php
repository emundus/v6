<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.categories');
JLoader::import('joomla.application.component.model');
JLoader::import('components.com_jcalpro.models.events', JPATH_ADMINISTRATOR);
if (!class_exists('JCalProModelEvents'))
{
	return;
}

JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jcalpro/tables');
JLoader::import('components.com_jcalpro.tables.event', JPATH_ADMINISTRATOR);
JLoader::import('components.com_jcalpro.tables.location', JPATH_ADMINISTRATOR);

class DPCalendarJCalProModelEvents extends JCalProModelEvents
{

	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = parent::getListQuery();

		if ($this->getState('list.end-date', null) !== null)
		{
			$endDate = $db->Quote(DPCalendarHelper::getDate($this->getState('list.end-date'))->toSql());
			$dateCondition = '(a.end_date between ' . $startDate . ' and ' . $endDate . ' or a.start_date between ' . $startDate . ' and ' . $endDate .
					 ')';
		}
		$startDate = $this->getState('filter.start_date');
		$endDate = $this->getState('filter.end_date');
		$dateCondition = '';
		if (!empty($startDate) && !empty($endDate))
		{
			$startDateString = $db->Quote($startDate->toSql());
			$endDateString = $db->Quote($endDate->toSql());
			$dateCondition = '(Event.end_date between ' . $startDateString . ' and ' . $endDateString . ' or Event.start_date between ' .
					 $startDateString . ' and ' . $endDateString . ')';
		}
		else if (!empty($startDate))
		{
			$dateCondition = 'a.start_date  >= ' . $db->Quote($startDate->toSql());
		}
		else if (!empty($endDate))
		{
			$dateCondition = 'a.end_date  <= ' . $db->Quote($endDate->toSql());
		}

		if (!empty($dateCondition))
		{
			$query->where($dateCondition);
		}

		// Echo str_replace('#__', 'a_', $query);die;
		return $query;
	}
}
