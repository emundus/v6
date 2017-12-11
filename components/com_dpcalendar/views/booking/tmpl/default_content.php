<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Heading;

// The booking to display
$booking = $this->item;

// The params
$params = $this->params;

// The root container
$root = $this->root->addChild(new Container('actions'));

// Determine if the booking has a price
$hasPrice = $booking->price && $booking->price != '0.00';

if ($hasPrice) {
	// The invoice heading
	$h = $root->addChild(new Heading('invoice-heading', 2, array('dpcalendar-heading')));
	$h->setProtectedClass('dpcalendar-heading');
	$h->setContent(JText::_('COM_DPCALENDAR_INVOICE_INVOICE_DETAILS'));

	// Set up the payment options
	DPCalendarHelper::renderLayout(
		'content.dl',
		array('root' => $root, 'id' => 'id', 'label' => 'COM_DPCALENDAR_INVOICE_NUMBER', 'content' => $booking->uid)
	);
	DPCalendarHelper::renderLayout(
		'content.dl',
		array(
			'root'    => $root,
			'id'      => 'date',
			'label'   => 'COM_DPCALENDAR_INVOICE_DATE',
			'content' => DPCalendarHelper::getDate($booking->book_date)->format($params->get('event_date_format',
					'm.d.Y') . ' ' . $params->get('event_time_format', 'g:i a'))
		)
	);
	DPCalendarHelper::renderLayout(
		'content.dl',
		array(
			'root'    => $root,
			'id'      => 'price',
			'label'   => 'COM_DPCALENDAR_BOOKING_FIELD_PRICE_LABEL',
			'content' => DPCalendarHelper::renderPrice($booking->price, $params->get('currency_symbol', '$'))
		)
	);
	DPCalendarHelper::renderLayout(
		'content.dl',
		array('root' => $root, 'id' => 'tickets', 'label' => 'COM_DPCALENDAR_BOOKING_FIELD_TICKETS_LABEL', 'content' => $booking->amount_tickets)
	);
	DPCalendarHelper::renderLayout(
		'content.dl',
		array(
			'root'    => $root,
			'id'      => 'processor',
			'label'   => 'COM_DPCALENDAR_BOOKING_FIELD_PROCESSOR_LABEL',
			'content' => $booking->processor ? JText::_('PLG_DPCALENDARPAY_' . strtoupper($booking->processor) . '_TITLE') : ''
		)
	);
	DPCalendarHelper::renderLayout(
		'content.dl',
		array('root' => $root, 'id' => 'status', 'label' => 'JSTATUS', 'content' => \DPCalendar\Helper\Booking::getStatusLabel($booking))
	);
}

// The booking details heading
$h = $root->addChild(new Heading('booking-heading', 2, array('dpcalendar-heading')));
$h->setProtectedClass('dpcalendar-heading');
$h->setContent(JText::_('COM_DPCALENDAR_INVOICE_BOOKING_DETAILS'));

if (!$hasPrice) {
	// Add the uid field as ID
	DPCalendarHelper::renderLayout(
		'content.dl',
		array('root' => $root, 'id' => 'id', 'label' => 'COM_DPCALENDAR_BOOKING_FIELD_ID_LABEL', 'content' => $booking->uid)
	);

	DPCalendarHelper::renderLayout(
		'content.dl',
		array('root' => $root, 'id' => 'tickets', 'label' => 'COM_DPCALENDAR_BOOKING_FIELD_TICKETS_LABEL', 'content' => $booking->amount_tickets)
	);
}

$fields   = array();
$fields[] = (object)array('id' => 'name', 'name' => 'name');
$fields[] = (object)array('id' => 'email', 'name' => 'email');
$fields[] = (object)array('id' => 'telephone', 'name' => 'telephone');
$fields[] = (object)array('id' => 'country', 'name' => 'country', 'label' => 'COM_DPCALENDAR_LOCATION_FIELD_COUNTRY_LABEL');
$fields[] = (object)array('id' => 'province', 'name' => 'province', 'label' => 'COM_DPCALENDAR_LOCATION_FIELD_PROVINCE_LABEL');
$fields[] = (object)array('id' => 'city', 'name' => 'city', 'label' => 'COM_DPCALENDAR_LOCATION_FIELD_CITY_LABEL');
$fields[] = (object)array('id' => 'zip', 'name' => 'zip', 'label' => 'COM_DPCALENDAR_LOCATION_FIELD_ZIP_LABEL');
$fields[] = (object)array('id' => 'street', 'name' => 'street', 'label' => 'COM_DPCALENDAR_LOCATION_FIELD_STREET_LABEL');
$fields[] = (object)array('id' => 'number', 'name' => 'number', 'label' => 'COM_DPCALENDAR_LOCATION_FIELD_NUMBER_LABEL');

$fields = array_merge($fields, $booking->jcfields);

\DPCalendar\Helper\DPCalendarHelper::sortFields($fields, $params->get('booking_fields_order', new stdClass()));

foreach ($fields as $field) {
	if (!$params->get('booking_show_' . $field->name, 1)) {
		continue;
	}
	$label = 'COM_DPCALENDAR_BOOKING_FIELD_' . strtoupper($field->name) . '_LABEL';

	if (isset($field->label)) {
		$label = $field->label;
	}

	$content = '';
	if (property_exists($booking, $field->name)) {
		$content = $booking->{$field->name};
	}
	if (property_exists($field, 'value')) {
		$content = $field->value;
	}

	DPCalendarHelper::renderLayout(
		'content.dl',
		array('root' => $root, 'id' => $field->id, 'label' => $label, 'content' => $content)
	);
}

// Before event trigger
JPluginHelper::importPlugin('content');
$content = JFactory::getApplication()->triggerEvent(
	'onContentBeforeDisplay',
	array(
		'com_dpcalendar.booking',
		&$this->item,
		&$params,
		0
	)
);
$root->addChild(new Container('event-before-display'))->setContent(implode(' ', $content));

// The booking details heading
$h = $root->addChild(new Heading('tickets-heading', 2, array('dpcalendar-heading')));
$h->setProtectedClass('dpcalendar-heading');
$h->setContent(JText::_('COM_DPCALENDAR_INVOICE_TICKET_DETAILS'));

// Add the tickets list
DPCalendarHelper::renderLayout(
	'tickets.list',
	array(
		'tickets' => $this->tickets,
		'params'  => $params,
		'root'    => $root
	)
);

// Add the register output
$rc = $root->addChild(new Container('register', array('noprint')));
$rc->setProtectedClass('noprint');
DPCalendarHelper::renderLayout(
	'booking.register',
	array(
		'booking' => $booking,
		'tickets' => $this->tickets,
		'params'  => $params,
		'root'    => $rc
	)
);
