<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_SITE);

class DPCalendarViewBookings extends DPCalendarView
{

	public function display ($tpl = null)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
		$model = JModelLegacy::getInstance('Bookings', 'DPCalendarModel');
		$this->setModel($model, true);

		return parent::display($tpl);
	}

	public function init ()
	{
		$user = JFactory::getUser();
		if ($user->guest)
		{
			JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JFactory::getURI())),
					JText::_('COM_DPCALENDAR_NOT_LOGGED_IN'), 'warning');
			return;
		}

		$this->getModel()->getState();

		$input = JFactory::getApplication()->input;

		// If we don't show the event bookings, show the user bookings
		if (! $input->getInt('e_id'))
		{
			$this->getModel()->setState('filter.my', true);
		}
		else
		{
			$this->getModel()->setState('filter.event_id', $input->getInt('e_id'));
		}

		$this->bookings = $this->get('Items');
		$this->pagination = $this->get('Pagination');
	}
}
