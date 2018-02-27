<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
use CCL\Content\Element\Basic\Container;

defined('_JEXEC') or die();

class DPCalendarControllerTicket extends JControllerForm
{

	protected $text_prefix = 'COM_DPCALENDAR_TICKET';

	public function pdfdownload()
	{
		$model  = $this->getModel('Ticket', 'DPCalendarModel', array('ignore_request' => false));
		$ticket = $model->getItem(array('uid' => $this->input->getCmd('uid')));

		if ($ticket == null) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$fileName = \DPCalendar\Helper\Booking::createTicket($ticket, JComponentHelper::getParams('com_dpcalendar'),
			false);
		if ($fileName) {
			JFactory::getApplication()->close();
		} else {
			JFactory::getApplication()->redirect(DPCalendarHelperRoute::getTicketRoute($ticket));
		}
	}

	public function pdfsend()
	{
		$model  = $this->getModel('Ticket', 'DPCalendarModel', array('ignore_request' => false));
		$ticket = $model->getItem(array('uid' => $this->input->getCmd('uid')));

		if ($ticket == null) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dpcalendar/models', 'DPCalendarModel');
		$event = JModelLegacy::getInstance('Event', 'DPCalendarModel')->getItem($ticket->event_id);

		// Create the ticket details for mail notification
		$params = clone JComponentHelper::getParams('com_dpcalendar');
		$params->set('show_header', false);

		$root = new Container('details');
		DPCalendarHelper::renderLayout(
			'ticket.details',
			array('ticket' => $ticket, 'event' => $event, 'root' => $root, 'params' => $params)
		);
		$root->accept(new \DPCalendar\CCL\Visitor\InlineStyleVisitor());

		$additionalVars = array(
			'ticketDetails' => DPCalendarHelper::renderElement($root),
			'ticketLink'    => DPCalendarHelperRoute::getTicketRoute($ticket, true),
			'ticketUid'     => $ticket->uid,
			'sitename'      => JFactory::getConfig()->get('sitename'),
			'user'          => JFactory::getUser()->name
		);

		JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . '/components/com_dpcalendar');

		$subject = DPCalendarHelper::renderEvents(
			array($event),
			JText::_('COM_DPCALENDAR_TICKET_NOTIFICATION_SEND_SUBJECT'),
			null,
			$additionalVars
		);

		$body = DPCalendarHelper::renderEvents(
			array($event),
			JText::_('COM_DPCALENDAR_TICKET_NOTIFICATION_SEND_BODY'),
			null,
			$additionalVars
		);

		// Send to the ticket holder
		$mailer = JFactory::getMailer();
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->IsHTML(true);
		$mailer->addRecipient($ticket->email);

		// Attache the new ticket
		$params->set('show_header', true);
		$fileName = \DPCalendar\Helper\Booking::createTicket($ticket, $params, true);
		if ($fileName) {
			$mailer->addAttachment($fileName);
		}
		$mailer->Send();
		if ($fileName) {
			JFile::delete($fileName);
		}

		JFactory::getApplication()->enqueueMessage(JText::_('COM_DPCALENDAR_CONTROLLER_SEND_SUCCESS'));

		JFactory::getApplication()->redirect(base64_decode($this->input->getBase64('return')));
	}

	public function save($key = null, $urlVar = 't_id')
	{
		return parent::save($key, $urlVar);
	}

	public function edit($key = null, $urlVar = 't_id')
	{
		return parent::edit($key, $urlVar);
	}

	public function cancel($key = 't_id')
	{
		return parent::cancel($key);
	}
}
