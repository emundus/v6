<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Component\Icon;
use CCL\Content\Element\Basic\TextBlock;

$h = $this->root->addChild(new Container('header'));
$h->addClass('noprint', true);
$h->addClass('dp-actions-container', true);

$h->addChild(new TextBlock('date'))->setContent(
	$this->startDate->format($this->params->get('list_title_format', 'm.d.Y'), true) .
	' - ' .
	$this->endDate->format($this->params->get('list_title_format', 'm.d.Y'), true)
);

// Add the new event button when we can
if (DPCalendarHelper::canCreateEvent()) {
	// Determine the return url
	$return = JFactory::getApplication()->input->getInt('Itemid', null);
	if (!empty($return)) {
		$return = 'index.php?Itemid=' . $return;
	}

	// Add the tools button
	DPCalendarHelper::renderLayout(
		'content.button',
		array(
			'id'      => 'add',
			'type'    => Icon::PLUS,
			'root'    => $h,
			'title'   => 'JACTION_CREATE',
			'onclick' => "location.href='" . JRoute::_(DPCalendarHelperRoute::getFormRoute(0, $return)) . "'"
		)
	);
}

// Add the print button
DPCalendarHelper::renderLayout(
	'content.button.print',
	array(
		'id'       => 'print',
		'root'     => $h,
		'selector' => 'dp-list'
	)
);

// Add the search button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'      => 'search-tools',
		'type'    => Icon::SEARCH,
		'root'    => $h,
		'title'   => 'JSEARCH_FILTER',
		'onclick' => "DPCalendar.fadeToggle(document.getElementById('dp-list-form'));"
	)
);
