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

/** @var Container $actions * */
$actions = $root->addChild(new Container('actions', array('noprint', 'actions')));
$actions->setProtectedClass('noprint');

// The location search input field
$input = $actions->addChild(
	new Input(
		'location',
		'text',
		'filter-location',
		JFactory::getApplication()->getUserStateFromRequest('com_dpcalendar.map.filter.location', ''),
		array('location'),
		array('placeholder' => JText::_('MOD_DPCALENDAR_MAP_ADDRESS'))
	)
);
$input->addClass('dp-form-input', true);

// Add the submit button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::SEARCH,
		'root'    => $actions,
		'title'   => 'JSEARCH_FILTER',
		'onclick' => "updateDPLocationFrame(this);"
	)
);

// Add the clear button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::DELETE,
		'root'    => $actions,
		'title'   => 'MOD_DPCALENDAR_MAP_CLEAR',
		'onclick' => "jQuery('#dp-module-map-" . $module->id . "-actions-location').val('');updateDPLocationFrame(this);"
	)
);

// Add the select box
$radius = JFactory::getApplication()->getUserState('com_dpcalendar.map.filter.radius', $params->get('radius', 20));
$s      = $actions->addChild(new Select('radius', 'radius'));
$s->addOption(5, 5, 5 == $radius);
$s->addOption(10, 10, 10 == $radius);
$s->addOption(20, 20, 20 == $radius);
$s->addOption(50, 50, 50 == $radius);
$s->addOption(100, 100, 100 == $radius);
$s->addOption(500, 500, 500 == $radius);
$s->addOption(1000, 1000, 1000 == $radius);
$s->addOption(JText::_('JALL'), -1, -1 == $radius);

// Add the length type select box
$length = JFactory::getApplication()->getUserState('com_dpcalendar.map.filter.length_type', $params->get('length_type', 'm'));
$s      = $actions->addChild(new Select('length-type', 'length_type'));
$s->addOption(JText::_('MOD_DPCALENDAR_MAP_LENGTH_TYPE_KILOMETER'), 'm', 'm' == $length);
$s->addOption(JText::_('MOD_DPCALENDAR_MAP_LENGTH_TYPE_MILE'), 'mile', 'mile' == $length);

// Some hidden inputs, needed for the request
$actions->addChild(new Input('itemid', 'hidden', 'itemid', JFactory::getApplication()->input->getInt('Itemid')));
$actions->addChild(new Input('ids', 'hidden', 'ids', implode(',', $params->get('ids'))));
$actions->addChild(new Input('moduleid', 'hidden', 'module_id', $module->id));
