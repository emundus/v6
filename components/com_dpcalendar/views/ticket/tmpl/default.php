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
JHtml::_('stylesheet', 'com_dpcalendar/dpcalendar/views/event/default.css', ['relative' => true]);

// Add some styling inline
JFactory::getDocument()->addStyleDeclaration('#dp-ticket-details-qr-code-image {width:150px;height:150px} #dp-ticket-details-qr-code {text-align:center}');

if ($this->item->state == 2)
{
	JFactory::getApplication()->enqueueMessage(JText::_('COM_DPCALENDAR_VIEW_TICKET_CHECKED_IN'), 'warning');
}

// Load the headers
$this->loadTemplate('header');

// Load the content
$this->loadTemplate('content');

// Render the element tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
