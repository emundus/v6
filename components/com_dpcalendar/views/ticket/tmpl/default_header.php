<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Button;
use CCL\Content\Element\Component\Icon;

// The ticket
$ticket = $this->item;

// The ticket
$event = $this->event;

// The params
$params = $this->params;

/** @var Container $root * */
$root = $this->root->addChild(new Container('actions'));
$root->addClass('noprint', true);
$root->addClass('dp-actions-container', true);

if ($ticket->state == 3 || $ticket->state == 4) {
	// Create the edit button
	DPCalendarHelper::renderLayout(
		'content.button',
		array(
			'id'      => 'pay',
			'type'    => Icon::PLUS,
			'root'    => $root,
			'text'    => 'COM_DPCALENDAR_PAY',
			'onclick' => "location.href='" . DPCalendarHelperRoute::getBookingFormRoute($this->booking->id) . "'"
		)
	);
}

// Create the event button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::INFO,
		'root'    => $root,
		'text'    => 'COM_DPCALENDAR_EVENT',
		'onclick' => "location.href='" . DPCalendarHelperRoute::getEventRoute($event->id, $event->catid) . "'"
	)
);

// Create the booking button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::USERS,
		'root'    => $root,
		'text'    => 'COM_DPCALENDAR_TICKET_FIELD_BOOKING_LABEL',
		'onclick' => "location.href='" . DPCalendarHelperRoute::getBookingRoute($this->booking) . "'"
	)
);

if ($ticket->state == 5) {
	// Create the accept button
	DPCalendarHelper::renderLayout(
		'content.button',
		array(
			'type'    => Icon::OK,
			'root'    => $root,
			'text'    => 'COM_DPCALENDAR_VIEW_BOOKING_INVITE_ACCEPT',
			'onclick' => "location.href='" . DPCalendarHelperRoute::getInviteChangeRoute($this->booking, true, false) . "'"
		)
	);

	// Create the decline button
	DPCalendarHelper::renderLayout(
		'content.button',
		array(
			'type'    => Icon::CANCEL,
			'root'    => $root,
			'text'    => 'COM_DPCALENDAR_VIEW_BOOKING_INVITE_DECLINE',
			'onclick' => "location.href='" . DPCalendarHelperRoute::getInviteChangeRoute($this->booking, false, false) . "'"
		)
	);
} else {
	// Create the edit button
	DPCalendarHelper::renderLayout(
		'content.button',
		array(
			'type'    => Icon::EDIT,
			'root'    => $root,
			'text'    => 'JGLOBAL_EDIT',
			'onclick' => "location.href='" . DPCalendarHelperRoute::getTicketFormRoute($ticket->id) . "'"
		)
	);

	// Create the invoice button
	DPCalendarHelper::renderLayout(
		'content.button',
		array(
			'type'    => Icon::DOWNLOAD,
			'root'    => $root,
			'text'    => 'COM_DPCALENDAR_DOWNLOAD',
			'onclick' => "location.href='" . JRoute::_('index.php?option=com_dpcalendar&task=ticket.pdfdownload&uid=' . $ticket->uid) . "'"
		)
	);
}

// Add the print button
DPCalendarHelper::renderLayout(
	'content.button.print',
	array(
		'root'     => $root,
		'id'       => 'print',
		'selector' => 'dp-ticket'
	)
);
