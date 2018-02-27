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

// The booking
$booking = $this->item;

// The params
$params = $this->params;

/** @var Container $root * */
$root = $this->root->addChild(new Container('container'));
$root->addClass('noprint', true);
$root->addClass('dp-actions-container', true);

if ($booking->state == 5) {
	// Create the accept button
	DPCalendarHelper::renderLayout(
		'content.button',
		array(
			'type'    => Icon::OK,
			'root'    => $root,
			'text'    => 'COM_DPCALENDAR_VIEW_BOOKING_INVITE_ACCEPT',
			'onclick' => "location.href='" . DPCalendarHelperRoute::getInviteChangeRoute($booking, true, false) . "'"
		)
	);

	// Create the decline button
	DPCalendarHelper::renderLayout(
		'content.button',
		array(
			'type'    => Icon::CANCEL,
			'root'    => $root,
			'text'    => 'COM_DPCALENDAR_VIEW_BOOKING_INVITE_DECLINE',
			'onclick' => "location.href='" . DPCalendarHelperRoute::getInviteChangeRoute($booking, false, false) . "'"
		)
	);
} else {
	// Create the edit button
	DPCalendarHelper::renderLayout(
		'content.button',
		array(
			'type'    => Icon::EDIT,
			'url'     => DPCalendarHelperRoute::getBookingFormRoute($booking->id),
			'root'    => $root,
			'text'    => $booking->state == 3 ? 'COM_DPCALENDAR_PAY' : 'JGLOBAL_EDIT',
			'onclick' => "location.href='" . DPCalendarHelperRoute::getBookingFormRoute($booking->id) . "'"
		)
	);

	if ($booking->price && $booking->price != '0.00') {
		// Create the invoice button
		DPCalendarHelper::renderLayout(
			'content.button',
			array(
				'type'    => Icon::DOWNLOAD,
				'root'    => $root,
				'text'    => 'COM_DPCALENDAR_INVOICE',
				'onclick' => "location.href='" . JRoute::_('index.php?option=com_dpcalendar&task=booking.invoice&b_id=' . $booking->id) . "'"
			)
		);
	} else {
		$return = '&return=' . base64_encode('index.php?Itemid=' . $this->input->getInt('Itemid'));
		// Create the edit button
		DPCalendarHelper::renderLayout(
			'content.button',
			array(
				'type'    => Icon::DELETE,
				'url'     => DPCalendarHelperRoute::getBookingFormRoute($booking->id),
				'root'    => $root,
				'text'    => 'JACTION_DELETE',
				'onclick' => "location.href='" . JRoute::_('index.php?option=com_dpcalendar&task=bookingform.delete&b_id=' . $booking->id . $return) . "'"
			)
		);
	}
}

// Add the print button
DPCalendarHelper::renderLayout(
	'content.button.print',
	array(
		'root'     => $root,
		'id'       => 'print',
		'selector' => 'dp-booking'
	)
);
