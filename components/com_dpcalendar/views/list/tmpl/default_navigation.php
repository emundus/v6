<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;

// The start value
$start = clone $this->startDate;
$start->modify('+ ' . $this->increment);

// The end value
$end = clone $this->endDate;
$end->modify('+ ' . $this->increment);

// The link to the next page
$nextLink = 'index.php?option=com_dpcalendar&view=list&Itemid=' . JFactory::getApplication()->input->getInt('Itemid') . '&date-start=' . $start->format('U') . '&date-end=' . $end->format('U');

// Modify the start for the prev link
$start->modify('- ' . $this->increment);
$start->modify('- ' . $this->increment);

// Modify the end for the prev link
$end->modify('- ' . $this->increment);
$end->modify('- ' . $this->increment);

// The link to the prev page
$prevLink = 'index.php?option=com_dpcalendar&view=list&Itemid=' . JFactory::getApplication()->input->getInt('Itemid') . '&date-start=' . $start->format('U') . '&date-end=' . $end->format('U');

// The pagination container
$c = $this->root->addChild(new Container('navigation'));
$c->addClass('dp-actions-container', true);
$c->addClass('noprint', true);

// Add the prev button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'      => 'prev',
		'root'    => $c,
		'text'    => '<',
		'title'   => '',
		'onclick' => "location.href='" . JRoute::_($prevLink) . "'"
	)
);

// Add the next button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'      => 'next',
		'root'    => $c,
		'text'    => '>',
		'title'   => '',
		'onclick' => "location.href='" . JRoute::_($nextLink) . "'"
	)
);
