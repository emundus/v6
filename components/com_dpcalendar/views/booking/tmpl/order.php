<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
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

// Header with buttons and title
$this->loadTemplate('header');

// Load the content
$this->loadTemplate('content');

// Render the tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
