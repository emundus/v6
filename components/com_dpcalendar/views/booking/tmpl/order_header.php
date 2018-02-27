<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Button;
use CCL\Content\Element\Component\Icon;
use CCL\Content\Element\Basic\Heading;

// The booking
$booking = $this->item;

// The params
$params  = $this->params;

/** @var Container $root **/
$root = $this->root->addChild(new Container('container'));
$root->addClass('noprint', true);
$root->addClass('dp-actions-container', true);

// Create the booking link button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'         => Icon::USERS,
		'root'         => $root,
		'text'         => 'COM_DPCALENDAR_TICKET_FIELD_BOOKING_LABEL',
		'onclick'      => "location.href='" . DPCalendarHelperRoute::getBookingRoute($booking) ."'"
	)
);

// Create the invoice button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'         => Icon::DOWNLOAD,
		'root'         => $root,
		'text'         => 'COM_DPCALENDAR_INVOICE',
		'onclick'      => "location.href='" . JRoute::_('index.php?option=com_dpcalendar&task=booking.invoice&b_id=' . $booking->id) ."'"
	)
);

// Add the print button
DPCalendarHelper::renderLayout(
	'content.button.print',
	array(
		'root'         => $root,
		'id'           => 'print',
		'selector'     => 'dp-booking-container'
	)
);

// The invoice heading
$h = $root->addChild(new Heading('order', 2, array('dpcalendar-heading')));
$h->setProtectedClass('dpcalendar-heading');
$h->setContent(JText::_('COM_DPCALENDAR_VIEW_BOOKING_MESSAGE_THANKYOU'));
