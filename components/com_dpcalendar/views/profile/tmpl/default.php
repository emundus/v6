<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// Load the profile stylesheet
JHtml::_('stylesheet', 'com_dpcalendar/dpcalendar/views/profile/default.css', ['relative' => true]);

// User timezone
DPCalendarHelper::renderLayout('user.timezone', array('root' => $this->root));

// Load the sharing section
$this->loadTemplate('sharing');

// Load the calendars section
$this->loadTemplate('calendars');

// Load the events
$this->loadTemplate('events');

// Render the element tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
