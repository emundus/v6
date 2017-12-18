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
$form = $this->root->addChild(new Form('form', '#', 'map-form', 'POST', array('form-validate')));
$form->addClass('noprint', true);
$form->addClass('dp-actions-container', true);

$c = $form->addChild(new Container('basic', ['form-container']));

// The search input field
$i = $c->addChild(new Input('search', 'text', 'search', $this->state->get('filter.search')));
$i->addAttribute('onchange', 'this.form.submit();');
$i->addAttribute('placeholder', JText::_('JGLOBAL_FILTER_LABEL'));

// Add the datepicker buttons
$date = $this->state->get('list.start-date');
if ($date) {
	$date = \DPCalendar\Helper\DPCalendarHelper::getDateFromString($date, null, true, $this->params->get('event_form_date_format', 'm.d.Y'));
}
DPCalendarHelper::renderLayout(
	'content.button.datetime',
	array(
		'id'          => 'start',
		'root'        => $c,
		'name'        => 'start-date',
		'date'        => $date,
		'title'       => JText::_('COM_DPCALENDAR_FIELD_START_DATE_LABEL'),
		'placeholder' => JText::_('COM_DPCALENDAR_FIELD_START_DATE_LABEL'),
		'onchange'    => 'this.form.submit();',
		'dateFormat'  => $this->params->get('event_form_date_format', 'm.d.Y')
	)
);

$date = $this->state->get('list.end-date');
if ($date) {
	$date = \DPCalendar\Helper\DPCalendarHelper::getDateFromString($date, null, true, $this->params->get('event_form_date_format', 'm.d.Y'));
}
DPCalendarHelper::renderLayout(
	'content.button.datetime',
	array(
		'id'          => 'end',
		'root'        => $c,
		'name'        => 'end-date',
		'date'        => $date,
		'title'       => JText::_('COM_DPCALENDAR_FIELD_END_DATE_LABEL'),
		'placeholder' => JText::_('COM_DPCALENDAR_FIELD_END_DATE_LABEL'),
		'onchange'    => 'this.form.submit();',
		'dateFormat'  => $this->params->get('event_form_date_format', 'm.d.Y')
	)
);

$c = $form->addChild(new Container('location', ['form-container']));

// The location search input field
$i = $c->addChild(
	new Input(
		'text',
		'text',
		'location',
		$this->state->get('filter.location'),
		array('location'),
		array('placeholder' => JText::_('COM_DPCALENDAR_LOCATION'))
	)
);
$i->addAttribute('onchange', 'this.form.submit();');

// Add the select box
$radius = $this->state->get('filter.radius', 50);
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
$length = $this->state->get('filter.length-type');
$s      = $c->addChild(new Select('length-type', 'length-type'));
$s->addAttribute('onchange', 'this.form.submit();');
$s->addOption(JText::_('COM_DPCALENDAR_FIELD_CONFIG_MAP_LENGTH_TYPE_METER'), 'm', 'm' == $length);
$s->addOption(JText::_('COM_DPCALENDAR_FIELD_CONFIG_MAP_LENGTH_TYPE_MILE'), 'mile', 'mile' == $length);

// Some hidden inputs, needed for the request
$form->addChild(new Input('itemid', 'hidden', 'Itemid', JFactory::getApplication()->input->getInt('Itemid')));

$c = $form->addChild(new Container('actions'));

// Add the submit button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'   => 'current-location',
		'type' => Icon::LOCATION,
		'root' => $c,
		'text' => JText::_('COM_DPCALENDAR_VIEW_MAP_LABEL_CURRENT_LOCATION')
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
