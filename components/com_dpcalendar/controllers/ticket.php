<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');

class DPCalendarControllerTicket extends JControllerLegacy
{

	public function checkin()
	{
		$ticket = $this->getModel()->getItem(array('uid' => $this->input->getCmd('uid')));
		if ($ticket) {
			$user    = JFactory::getUser();
			$event   = $this->getModel('Event')->getItem($ticket->event_id);
			$booking = $this->getModel('Booking')->getItem($ticket->booking_id);

			if ($event->created_by != $user->id && !$user->authorise('core.admin', 'com_dpcalendar')) {
				$this->setMessage(JText::_('COM_DPCALENDAR_VIEW_TICKET_NO_PERMISSION'), 'error');
			} else if ($booking->state != 1) {
				$this->setMessage(
					JText::sprintf('COM_DPCALENDAR_VIEW_TICKET_BOOKING_NOT_ACTIVE', \DPCalendar\Helper\Booking::getStatusLabel($booking)), 'error'
				);
			} else if ($ticket->state == 2) {
				$this->setMessage(JText::_('COM_DPCALENDAR_VIEW_TICKET_CHECKED_IN'), 'error');
			} else {
				$model = $this->getModel();
				if ($model->publish($ticket->id, 2)) {
					$this->setMessage(JText::_('COM_DPCALENDAR_VIEW_TICKET_CHECKED_IN'));
				} else {
					$this->setMessage($model->getError(), 'error');
				}
			}
			$this->setRedirect(DPCalendarHelperRoute::getTicketRoute($ticket));
		} else {
			$this->setRedirect(JUri::base());
		}
	}

	public function pdfdownload()
	{
		$model  = $this->getModel('Ticket', 'DPCalendarModel', array('ignore_request' => false));
		$ticket = $model->getItem(array('uid' => $this->input->getCmd('uid')));

		if ($ticket == null) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$fileName = \DPCalendar\Helper\Booking::createTicket($ticket, JComponentHelper::getParams('com_dpcalendar'), false);
		if ($fileName) {
			JFactory::getApplication()->close();
		} else {
			JFactory::getApplication()->redirect(DPCalendarHelperRoute::getTicketRoute($ticket));
		}
	}

	public function getModel($name = 'Ticket', $prefix = 'DPCalendarModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
