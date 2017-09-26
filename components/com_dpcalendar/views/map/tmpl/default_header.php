<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Form\Input;
use CCL\Content\Element\Component\Icon;
use CCL\Content\Element\Basic\Form\Select;

/** @var Container $root **/
$root = $this->root->addChild(new Container('actions'));
$root->addClass('noprint', true);

// The location search input field
$root->addChild(new Input('location', 'text', 'filter-location', $this->state->get('filter.location')));

// Add the submit button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::SEARCH,
		'root'    => $root,
		'title'   => 'JSEARCH_FILTER',
		'onclick' => "updateDPLocationFrame(this);"
	)
);

// Add the clear button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::DELETE,
		'root'    => $root,
		'title'   => 'COM_DPCALENDAR_CLEAR',
		'onclick' => "jQuery('#dp-map-actions-location').val('');updateDPLocationFrame(this);"
	)
);

// Add the select box
$s = $root->addChild(new Select('radius', 'radius'));
$s->addOption(5, 5, 5 == $this->state->get('filter.radius'));
$s->addOption(10, 10, 10 == $this->state->get('filter.radius'));
$s->addOption(20, 20, 20 == $this->state->get('filter.radius'));
$s->addOption(50, 50, 50 == $this->state->get('filter.radius'));
$s->addOption(100, 100, 100 == $this->state->get('filter.radius'));
$s->addOption(500, 500, 500 == $this->state->get('filter.radius'));
$s->addOption(1000, 1000, 1000 == $this->state->get('filter.radius'));
$s->addOption(JText::_('JALL'), -1, -1 == $this->state->get('filter.radius'));

// Add the length type select box
$s = $root->addChild(new Select('length-type', 'length_type'));
$s->addOption(JText::_('COM_DPCALENDAR_FIELD_CONFIG_MAP_LENGTH_TYPE_METER'), 'm', 'm' == $this->state->get('filter.length_type'));
$s->addOption(JText::_('COM_DPCALENDAR_FIELD_CONFIG_MAP_LENGTH_TYPE_MILE'), 'mile', 'mile' == $this->state->get('filter.length_type'));

// Some hidden inputs, needed for the request
$root->addChild(new Input('itemid', 'hidden', 'itemid', JFactory::getApplication()->input->getInt('Itemid')));
$root->addChild(new Input('ids', 'hidden', 'ids', $ids));
