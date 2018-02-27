<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


class DPCalendarViewTickets extends \DPCalendar\View\BaseView
{

	public function display ($tpl = null)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
		$model = JModelLegacy::getInstance('Tickets', 'DPCalendarModel');
		$this->setModel($model, true);

		return parent::display($tpl);
	}

	public function init ()
	{
		$this->getModel()->getState();

		$input = JFactory::getApplication()->input;

		// If we don't show the event tickets, show the user tickets
		if (! $input->getInt('e_id'))
		{
			$user = JFactory::getUser();
			if ($user->guest)
			{
				JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JFactory::getURI())),
						JText::_('COM_DPCALENDAR_NOT_LOGGED_IN'), 'warning');
				return;
			}

			$this->getModel()->setState('filter.my', true);
		}
		else
		{
			$this->getModel()->setState('filter.my', false);
			$this->getModel()->setState('filter.event_id', $input->getInt('e_id'));
		}
		$this->event = $input->getInt('e_id') ? $this->getModel()->getEvent($input->getInt('e_id')) : null;
		$this->booking = $input->getInt('b_id') ? JModelLegacy::getInstance('Booking', 'DPCalendarModel')->getItem($input->getInt('b_id')) : null;

		$this->tickets = $this->get('Items');
		$this->pagination = $this->get('Pagination');
	}
}
