<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.view');
JLoader::import('libraries.fullcalendar.fullcalendar', JPATH_COMPONENT);

class DPCalendarViewEvents extends JViewLegacy
{

	public function display ($tpl = null)
	{
		JRequest::setVar('list.limit', 1000);

		$start = DPCalendarHelper::getDate(JRequest::getInt('date-start'), false, DPCalendarHelper::getDate()->getTimezone()->getName());
		// We always subtract the offset, even when it is negative already
		JRequest::setVar('date-start', $start->format('U') - str_replace('-', '', DPCalendarHelper::getDate()->getTimezone()->getOffset($start)));

		$end = DPCalendarHelper::getDate(JRequest::getInt('date-end'), false, DPCalendarHelper::getDate()->getTimezone()->getName());
		// We always subtract the offset, even when it is negative already
		JRequest::setVar('date-end', $end->format('U') - str_replace('-', '', DPCalendarHelper::getDate()->getTimezone()->getOffset($end)));

		$this->items = $this->get('Items');

		$tmp = clone $this->get('State')->params;
		$tmp->merge(JFactory::getApplication()->getParams());
		$this->params = $tmp;

		$this->compactMode = JRequest::getVar('compact', 0);
		if ($this->compactMode == 1)
		{
			$this->setLayout('compact');
		}

		parent::display($tpl);
	}
}
