<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Element;
use CCL\Content\Element\Basic\Heading;

// The booking to display
$booking = $this->item;

// The root container
$root = $this->root->addChild(new Container('container'));

// Prepare the message
$message = JHTML::_('content.prepare', JText::_($this->params->get('ordertext')));

// The vars for the message
$vars                   = (array)$booking;
$vars['currency']       = $this->params->get('currency', 'USD');
$vars['currencySymbol'] = $this->params->get('currency_symbol', '$');

// The message to display
$root->addChild(new Element('message'))->setContent(DPCalendarHelper::renderEvents(array(), $message, $this->params, $vars));

// The plugin output to display
$root->addChild(new Element('plugin'))->setContent(\DPCalendar\Helper\Booking::getPaymentStatementFromPlugin($booking, $this->params));

// The booking details heading
$h = $root->addChild(new Heading('tickets-heading', 2, array('dp-event-header')));
$h->setProtectedClass('dp-event-header');
$h->setContent(JText::_('COM_DPCALENDAR_INVOICE_TICKET_DETAILS'));

// Add the tickets list
DPCalendarHelper::renderLayout(
	'tickets.list',
	array(
		'tickets' => $this->tickets,
		'params'  => $this->params,
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
		'params'  => $this->params,
		'root'    => $rc
	)
);
