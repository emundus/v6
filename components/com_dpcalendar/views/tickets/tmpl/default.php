<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Form;

// Load the JS libraries
DPCalendarHelper::loadLibrary(array('jquery' => true, 'dpcalendar' => true));

// Load the stylesheet
JHtml::_('stylesheet', 'com_dpcalendar/dpcalendar/views/tickets/default.css', ['relative' => true]);

// If we have an event, show an information message
if ($this->event)
{
	JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_DPCALENDAR_VIEW_TICKETS_SHOW_FROM_EVENT', $this->escape($this->event->title)));
}

// If we have a booking, show an information message
if ($this->booking)
{
	JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_DPCALENDAR_VIEW_TICKETS_SHOW_FROM_BOOKING', $this->escape($this->booking->uid)));
}

// The form element
$this->root = new Form(
	'dp-tickets',
	JUri::getInstance()->toString(),
	'adminForm',
	'POST',
	array('form-validate')
);

// User timezone
DPCalendarHelper::renderLayout('user.timezone', array('root' => $this->root));

// Load the header
$this->loadTemplate('header');

// Load the tickets
DPCalendarHelper::renderLayout('tickets.list', array(
		'tickets' => $this->tickets,
		'params'  => $this->params,
		'root'    => $this->root
));

// Load the footer
$this->loadTemplate('footer');

// Render the element tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
