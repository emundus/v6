<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Form\Input;
use CCL\Content\Element\Basic\Form\Label;
use CCL\Content\Element\Basic\TextBlock;
use CCL\Content\Element\Component\Icon;

/** @var Container $root **/
$root = $this->root->addChild(new Container('actions'));
$root->addClass('noprint', true);
$root->addClass('dp-actions-container', true);

// Add the search box
$search = $root->addChild(new Input('filter', 'text', 'filter[search]', $this->state->get('filter.search')));
$search->addAttribute('onchange', 'this.form.submit();');
$search->addAttribute('placeholder', JText::_('JGLOBAL_FILTER_LABEL'));

// Add the submit button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'         => Icon::SEARCH,
		'root'         => $root,
		'title'        => 'JSEARCH_FILTER',
		'onclick'      => "this.form.submit();"
	)
);

// Add the clear button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'         => Icon::DELETE,
		'root'         => $root,
		'title'        => 'COM_DPCALENDAR_CLEAR',
		'onclick'      => "jQuery('#dp-tickets-actions-filter').val('');"
	)
);

// Add the print button
DPCalendarHelper::renderLayout(
	'content.button.print',
	array(
		'root'         => $root,
		'id'           => 'print',
		'selector'     => 'adminForm'
	)
);

// On regular list show more options
if (!$this->event && !$this->booking)
{
	// Add a checkbox which allows to search for upcoming events only
	$cb = $root->addChild(new Input('future', 'checkbox', 'filter[future]', 1));
	$cb->addAttribute('onclick', 'this.form.submit();');
	if ($this->state->get('filter.future') == 1)
	{
		$cb->addAttribute('checked', 'checked');
	}

	// The label for the option
	$root->addChild(new Label('future-label', 'filter[future]'))->setContent(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_FUTURE'));
}

// The limit container
$c = $root->addChild(new Container('limit-container'));

// The limit text block
$c->addChild(new TextBlock('limit'))->setContent(JText::_('JGLOBAL_DISPLAY_NUM'));

// The limit select box
$c->addChild(new TextBlock('pagination'))->setContent($this->pagination->getLimitBox());
