<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Element;
use CCL\Content\Element\Basic\Heading;

// Load the event stylesheet
JHtml::_('stylesheet', 'com_dpcalendar/dpcalendar/dpcalendar.css', ['relative' => true]);

// The invoice heading
$h = $this->root->addChild(new Heading('cancel', 2, array('dpcalendar-heading')));
$h->setProtectedClass('dpcalendar-heading');
$h->setContent(JText::_('COM_DPCALENDAR_VIEW_BOOKING_MESSAGE_SORRY'));

// Add the message
$this->root->addChild(new Element('message'))->setContent(JHTML::_('content.prepare', JText::_($this->params->get('canceltext'))));

// Render the tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
