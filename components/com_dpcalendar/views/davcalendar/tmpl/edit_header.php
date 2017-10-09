<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Button;
use CCL\Content\Element\Component\Icon;

// The location
$location = $this->item;

// The params
$params = $this->params;

/** @var Container $root * */
$root = $this->root->addChild(new Container('actions', array('noprint')));
$root->setProtectedClass('noprint');
$root->addClass('dp-actions-container', true);

// Create the save button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::OK,
		'root'    => $root,
		'text'    => 'JSAVE',
		'onclick' => "Joomla.submitbutton('davcalendar.save')"
	)
);

// Create the cancel button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::CANCEL,
		'root'    => $root,
		'text'    => 'JCANCEL',
		'onclick' => "Joomla.submitbutton('davcalendar.cancel')"
	)
);
