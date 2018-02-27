<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\TextBlock;
use CCL\Content\Element\Basic\Container;

// The booking
$booking = $this->item;

// The params
$params = $this->params;

/** @var Container $root * */
$root = $this->root->addChild(new Container('actions'));
$root->addClass('noprint', true);
$root->addClass('dp-actions-container', true);

// Add the print button
DPCalendarHelper::renderLayout(
	'content.button.print',
	array(
		'root'     => $root,
		'id'       => 'print',
		'selector' => 'dp-bookings'
	)
);

// The container for the limit
$c = $root->addChild(new Container('limit'));

// The limit text block
$c->addChild(new TextBlock('num'))->setContent(JText::_('JGLOBAL_DISPLAY_NUM'));

// The limit select block
$c->addChild(new TextBlock('pagination'))->setContent($this->pagination->getLimitBox());
