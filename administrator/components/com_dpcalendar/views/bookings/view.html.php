<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_ADMINISTRATOR);

class DPCalendarViewBookings extends \DPCalendar\View\BaseView
{

	protected $items;

	protected $pagination;

	protected $state;

	public function init ()
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
	}

	protected function addToolbar ()
	{
		$state = $this->get('State');
		$canDo = DPCalendarHelper::getActions();
		$user = JFactory::getUser();
		$bar = JToolbar::getInstance('toolbar');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('booking.add');
		}
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('booking.edit');
		}
		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('bookings.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('bookings.unpublish', 'JTOOLBAR_UNPUBLISH', true);

			JToolbarHelper::archiveList('bookings.archive');
			JToolbarHelper::checkin('bookings.checkin');
		}
		if ($state->get('filter.state') == - 2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'bookings.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('bookings.trash');
		}

		if ($canDo->get('core.admin', 'com_dpcalendar'))
		{
			JToolbarHelper::custom('bookings.csvexport', 'download', '', 'COM_DPCALENDAR_CSV_EXPORT', false);
		}

		parent::addToolbar();
	}
}
