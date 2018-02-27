<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Heading;
use CCL\Content\Element\Basic\Container;

// The upcoming events heading
$root = $this->root->addChild(new Container('events'));
$root->addChild(new Heading('heading', 3))->setContent(JText::_('COM_DPCALENDAR_VIEW_PROFILE_UPCOMING_EVENTS'));

// Some default parameters
$this->params->set('list_show_icon', 0);

// Load the events
DPCalendarHelper::renderLayout(
	'events.list',
	array('root' => $root, 'events' => $this->events, 'params' => $this->params)
);
