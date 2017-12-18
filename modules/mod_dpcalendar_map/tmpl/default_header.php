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
use CCL\Content\Element\Basic\Form\Select;
use CCL\Content\Element\Component\Icon;
use CCL\Content\Element\Basic\Form;

// Are we in a modal
$tmpl = JFactory::getApplication()->input->getCmd('tmpl');
if ($tmpl) {
	$tmpl = '&tmpl=' . $tmpl;
}

/** @var Form $form * */
$form = $root->addChild(new Form('form', '#', 'dp-module-map-form-' . $module->id, 'POST', array('form-validate')));
$form->addClass('noprint', true);
$form->addClass('dp-actions-container', true);

// Some hidden inputs, needed for the request
$form->addChild(new Input('itemid', 'hidden', 'Itemid', JFactory::getApplication()->input->getInt('Itemid')));
$form->addChild(new Input('moduleid', 'hidden', 'module-id', $module->id));

// If the search bar shouldn't be shown add some default values to the form
if (!$params->get('show_search', 1)) {
	$form->addAttribute('style', 'display:none');

	$form->addChild(new Input('length-type', 'hidden', 'length-type', $state->get('filter.length-type', 'mile')));
	$form->addChild(new Input('radius', 'hidden', 'radius', $state->get('filter.radius', 20)));

	return;
}

// Load the stylesheet
JHtml::_('stylesheet', 'mod_dpcalendar_map/default.min.css', ['relative' => true]);

$c = $form->addChild(new Container('basic', ['form-container']));

// The search input field
$i = $c->addChild(new Input('search', 'text', 'search', $state->get('filter.search')));
$i->addAttribute('onchange', 'this.form.submit();');
$i->addAttribute('placeholder', JText::_('JGLOBAL_FILTER_LABEL'));

// Add the datepicker buttons
$date = $state->get('list.start-date');
if ($date) {
	$date = \DPCalendar\Helper\DPCalendarHelper::getDateFromString($date, null, true, $params->get('event_form_date_format', 'm.d.Y'));
}
DPCalendarHelper::renderLayout(
	'content.button.datetime',
	array(
		'id'          => 'start',
		'root'        => $c,
		'name'        => 'start-date',
		'date'        => $date,
		'title'       => JText::_('MOD_DPCALENDAR_MAP_START_DATE'),
		'placeholder' => JText::_('MOD_DPCALENDAR_MAP_START_DATE'),
		'onchange'    => 'this.form.submit();',
		'dateFormat'  => $params->get('event_form_date_format', 'm.d.Y')
	)
);

$date = $state->get('list.end-date');
if ($date) {
	$date = \DPCalendar\Helper\DPCalendarHelper::getDateFromString($date, null, true, $params->get('event_form_date_format', 'm.d.Y'));
}
DPCalendarHelper::renderLayout(
	'content.button.datetime',
	array(
		'id'          => 'end',
		'root'        => $c,
		'name'        => 'end-date',
		'date'        => $date,
		'title'       => JText::_('MOD_DPCALENDAR_MAP_END_DATE'),
		'placeholder' => JText::_('MOD_DPCALENDAR_MAP_END_DATE'),
		'onchange'    => 'this.form.submit();',
		'dateFormat'  => $params->get('event_form_date_format', 'm.d.Y')
	)
);

$c = $form->addChild(new Container('location', ['form-container']));

// The location search input field
$i = $c->addChild(
	new Input(
		'text',
		'text',
		'location',
		$state->get('filter.location'),
		array('location'),
		array('placeholder' => JText::_('MOD_DPCALENDAR_MAP_ADDRESS'))
	)
);
$i->addAttribute('onchange', 'this.form.submit();');

// Add the select box
$radius = $state->get('filter.radius', 50);
$s      = $c->addChild(new Select('radius', 'radius'));
$s->addAttribute('onchange', 'this.form.submit();');
$s->addOption(5, 5, 5 == $radius);
$s->addOption(10, 10, 10 == $radius);
$s->addOption(20, 20, 20 == $radius);
$s->addOption(50, 50, 50 == $radius);
$s->addOption(100, 100, 100 == $radius);
$s->addOption(500, 500, 500 == $radius);
$s->addOption(1000, 1000, 1000 == $radius);
$s->addOption(JText::_('JALL'), -1, -1 == $radius);

// Add the length type select box
$length = $state->get('filter.length-type', 'm');
$s      = $c->addChild(new Select('length-type', 'length-type'));
$s->addAttribute('onchange', 'this.form.submit();');
$s->addOption(JText::_('MOD_DPCALENDAR_MAP_LENGTH_TYPE_KILOMETER'), 'm', 'm' == $length);
$s->addOption(JText::_('MOD_DPCALENDAR_MAP_LENGTH_TYPE_MILE'), 'mile', 'mile' == $length);

$c = $form->addChild(new Container('actions', [$root->getPrefix() . 'actions'], ['ccl-prefix' => 'dp-map-']));

// Add the submit button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'   => 'current-location',
		'type' => Icon::LOCATION,
		'root' => $c,
		'text' => JText::_('MOD_DPCALENDAR_MAP_CURRENT_LOCATION')
	)
);

// Add the submit button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'   => 'search',
		'type' => Icon::OK,
		'root' => $c,
		'text' => 'JSEARCH_FILTER'
	)
);

// Add the clear button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'   => 'cancel',
		'type' => Icon::CANCEL,
		'root' => $c,
		'text' => 'JCLEAR'
	)
);
