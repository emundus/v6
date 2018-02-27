<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Table;
use CCL\Content\Element\Basic\Table\Row;
use CCL\Content\Element\Basic\Table\Cell;
use CCL\Content\Element\Basic\Link;
use CCL\Content\Element\Component\Icon;

// The params
$params = $this->params;

// The columns
$columns   = array();
$columns[] = JText::_('COM_DPCALENDAR_BOOKING_FIELD_ID_LABEL');
$columns[] = JText::_('COM_DPCALENDAR_VIEW_EVENTS_MODAL_COLUMN_STATE');
$columns[] = JText::_('COM_DPCALENDAR_BOOKING_FIELD_NAME_LABEL');
$columns[] = JText::_('COM_DPCALENDAR_INVOICE_DATE');
$columns[] = JText::_('COM_DPCALENDAR_BOOKING_FIELD_PRICE_LABEL');
$columns[] = JText::_('COM_DPCALENDAR_BOOKING_FIELD_TICKETS_LABEL');

if ($this->bookings) {
	foreach ($this->bookings[0]->jcfields as $field) {
		$columns[] = $field->label;
	}
}

/** @var Table $table * */
$table = $this->root->addChild(new Table('table', $columns));

// Loop trough all the bookings
foreach ($this->bookings as $booking) {
	// Create the row
	$row = $table->addRow(new Row($booking->id));

	// The id cell
	$cell = $row->addCell(new Cell('id'));
	$cell->addChild(new Link('link', DPCalendarHelperRoute::getBookingFormRoute($booking->id)))->addChild(new Icon('edit-icon', Icon::EDIT));
	$cell->addChild(new Link('link', DPCalendarHelperRoute::getBookingRoute($booking)))->setContent(JHtmlString::abridge($booking->uid, 15, 5));

	// The other cells
	$row->addCell(new Cell('status'))->setContent(\DPCalendar\Helper\Booking::getStatusLabel($booking));
	$row->addCell(new Cell('name'))->setContent($booking->name);
	$row->addCell(new Cell('date'))->setContent(
		DPCalendarHelper::getDate(
			$booking->book_date)->format($params->get('event_date_format', 'm.d.Y') . ' ' . $params->get('event_time_format', 'g:i a')
		)
	);
	$row->addCell(new Cell('price'))->setContent(DPCalendarHelper::renderPrice($booking->price, $params->get('currency_symbol', '$')));
	$row->addCell(new Cell('tickets'))->addChild(
		new Link('link', DPCalendarHelperRoute::getTicketsRoute($booking->id))
	)->setContent($booking->amount_tickets);

	foreach ($booking->jcfields as $field) {
		$row->addCell(new Cell('field-' . $field->id))->setContent($field->value);
	}
}
