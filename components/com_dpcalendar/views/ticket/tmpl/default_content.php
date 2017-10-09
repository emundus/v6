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
use CCL\Content\Element\Basic\Link;
use CCL\Content\Element\Basic\Image;
use DPCalendar\Helper\Booking;

// The event of the ticket
$event = $this->event;

// The ticket
$ticket = $this->item;

// The params
$params = $this->params;

// The root container
$root = $this->root->addChild(new Container('details'));

// The return
$return = JFactory::getApplication()->input->get('return') ?: DPCalendarHelperRoute::getEventRoute($event->id, $event->catid);

// The title heading
$h = $root->addChild(new Heading('title-heading', 2, array('dp-event-header')));
$h->setProtectedClass('dp-event-header');
$h->addChild(new Link('title-heading-link', $return))->setContent($event->title);

// The date information
DPCalendarHelper::renderLayout(
	'content.dl',
	array(
		'root'    => $root,
		'id'      => 'date',
		'label'   => 'COM_DPCALENDAR_DATE',
		'content' => DPCalendarHelper::getDateStringFromEvent(
			$event,
			$params->get('event_date_format', 'm.d.Y'), $params->get('event_time_format', 'g:i a')
		)
	)
);

// Add the locations
if (isset($event->locations) && $event->locations) {
	// Loop trough the locations
	$locations = array();
	foreach ($event->locations as $location) {
		$l = new Container(
			'location-' . $location->id,
			array(),
			array('data-latitude' => $location->latitude, 'data-longitude' => $location->longitude, 'data-title' => $location->title)
		);
		$l->addChild(new Link('location-' . $location->id . '-link', DPCalendarHelperRoute::getLocationRoute($location)))
			->setContent($location->title);
		$locations[] = $l;
	}

	// Add the to the tree
	DPCalendarHelper::renderLayout(
		'content.dl',
		array('root' => $root, 'id' => 'location', 'label' => 'COM_DPCALENDAR_LOCATION', 'content' => $locations)
	);
}

// The title heading
$h = $root->addChild(new Heading('tickets-heading', 2, array('dp-event-header')));
$h->setProtectedClass('dp-event-header');
$h->setContent(JText::_('COM_DPCALENDAR_INVOICE_TICKET_DETAILS'));

// The id information
DPCalendarHelper::renderLayout('content.dl',
	array('root' => $root, 'id' => 'id', 'label' => 'COM_DPCALENDAR_BOOKING_FIELD_ID_LABEL', 'content' => $ticket->uid));

// Check if a price type is available
if ($event->price && key_exists($ticket->type, $event->price->label) && $event->price->label[$ticket->type]) {
	// The price type information
	DPCalendarHelper::renderLayout(
		'content.dl',
		array(
			'root'    => $root,
			'id'      => 'type',
			'label'   => 'COM_DPCALENDAR_TICKET_FIELD_TYPE_LABEL',
			'content' => $event->price->label[$ticket->type] . ($event->price->description[$ticket->type] ? $event->price->description[$ticket->type] : '')
		)
	);
}

if ($ticket->price && $ticket->price != '0.00') {
	// The price information
	DPCalendarHelper::renderLayout(
		'content.dl',
		array(
			'root'    => $root,
			'id'      => 'price',
			'label'   => 'COM_DPCALENDAR_FIELD_PRICE_LABEL',
			'content' => DPCalendarHelper::renderPrice($ticket->price)
		)
	);
	DPCalendarHelper::renderLayout(
		'content.dl',
		array('root' => $root, 'id' => 'status', 'label' => 'JSTATUS', 'content' => Booking::getStatusLabel($ticket))
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
$fields[] = (object)array('id' => 'seat', 'name' => 'seat', 'label' => 'COM_DPCALENDAR_TICKET_FIELD_SEAT_LABEL');

$fields = array_merge($fields, $ticket->jcfields);

\DPCalendar\Helper\DPCalendarHelper::sortFields($fields, $params->get('ticket_fields_order', new stdClass()));

foreach ($fields as $field) {
	if (!$params->get('ticket_show_' . $field->name, 1)) {
		continue;
	}
	$label = 'COM_DPCALENDAR_BOOKING_FIELD_' . strtoupper($field->name) . '_LABEL';

	if (isset($field->label)) {
		$label = $field->label;
	}

	$content = '';
	if (property_exists($ticket, $field->name)) {
		$content = $ticket->{$field->name};
	}
	if (property_exists($field, 'value')) {
		$content = $field->value;
	}

	DPCalendarHelper::renderLayout(
		'content.dl',
		array('root' => $root, 'id' => $field->id, 'label' => $label, 'content' => $$content)
	);
}

// Before event trigger
JPluginHelper::importPlugin('content');

$content = JFactory::getApplication()->triggerEvent(
	'onContentBeforeDisplay',
	array(
		'com_dpcalendar.ticket',
		&$ticket,
		&$params,
		0
	)
);
$root->addChild(new Container('event-before-display'))->setContent(implode(' ', $content));

if ($params->get('ticket_show_barcode', 1)) {
	// Creating a QR code is memory intensive
	DPCalendarHelper::increaseMemoryLimit(130 * 1024 * 1024);

	// The image needs to be collected from the output
	ob_start();
	$barcodeobj = new TCPDF2DBarcode(DPCalendarHelperRoute::getTicketCheckinRoute($ticket, true), 'QRCODE,L');
	$barcodeobj->getBarcodePNG(150, 150);
	$imageString = base64_encode(ob_get_contents());
	ob_end_clean();

	// Add the qr code image as base64
	$root->addChild(new Container('qr-code'))->addChild(new Image('image', 'data:image/png;base64,' . $imageString));
}
