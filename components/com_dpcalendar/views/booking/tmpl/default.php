<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// Load the required assets
DPCalendarHelper::loadLibrary(array('dpcalendar' => true));

// Load the event stylesheet
JHtml::_('stylesheet', 'com_dpcalendar/dpcalendar/dpcalendar.css', ['relative' => true]);

// The params
$params = $this->params;

// The booking to display
$booking = $this->item;

// The user of the booking
$user = JFactory::getUser($booking->user_id);

// Calculate the amount of tickets
$booking->amount_tickets = 0;
foreach ($this->tickets as $ticket)
{
	if ($ticket->booking_id == $booking->id)
	{
		$booking->amount_tickets++;
	}
}

// Load the language of the payment plugin
$plugin = JPluginHelper::getPlugin('dpcalendarpay', $booking->processor);
if ($plugin)
{
	JFactory::getLanguage()->load('plg_dpcalendarpay_' . $booking->processor, JPATH_PLUGINS . '/dpcalendarpay/' . $booking->processor);
}

// Header with buttons and title
$this->loadTemplate('header');

// Load the content
$this->loadTemplate('content');

// Render the tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
