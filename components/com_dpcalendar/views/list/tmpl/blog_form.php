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
use CCL\Content\Element\Basic\Form\Select;
use CCL\Content\Element\Component\Icon;
use CCL\Content\Element\Basic\Form;

// Are we in a modal
$tmpl = JFactory::getApplication()->input->getCmd('tmpl');
if ($tmpl) {
	$tmpl = '&tmpl=' . $tmpl;
}

/** @var Form $form * */
$form = $this->root->addChild(
	new Form(
		'form',
		JRoute::_('index.php?option=com_dpcalendar&view=list&Itemid=' . JFactory::getApplication()->input->getInt('Itemid') . $tmpl),
		'blog-form',
		'POST',
		array('form-validate')
	)
);
$form->addClass('noprint', true);
$form->addClass('dp-actions-container', true);

// Hide the form when no filter is set
if (!$this->state->get('filter.search') && !$this->overrideStartDate && !$this->overrideEndDate && !$this->state->get('filter.location')) {
	JFactory::getDocument()->addStyleDeclaration('#dp-list-form {display: none}');
}

$c = $form->addChild(new Container('basic', ['container']));

// The search input field
$i = $c->addChild(new Container('search', ['search']))->addChild(new Input('search-filter', 'text', 'filter-search', $this->state->get('filter.search')));
$i->addAttribute('onchange', 'this.form.submit();');
$i->addAttribute('placeholder', JText::_('JGLOBAL_FILTER_LABEL'));

// Add the datepicker button
$dc = $c->addChild(new Container('date', ['date']));

DPCalendarHelper::renderLayout(
	'content.button.datetime',
	array(
		'id'          => 'start',
		'root'        => $dc,
		'name'        => 'start-date',
		'date'        => $this->overrideStartDate ? $this->startDate : null,
		'title'       => JText::_('COM_DPCALENDAR_FIELD_START_DATE_LABEL'),
		'placeholder' => JText::_('COM_DPCALENDAR_FIELD_START_DATE_LABEL'),
		'onchange'    => 'this.form.submit();',
		'dateFormat'  => $this->params->get('event_form_date_format', 'm.d.Y')
	)
);
DPCalendarHelper::renderLayout(
	'content.button.datetime',
	array(
		'id'          => 'end',
		'root'        => $dc,
		'name'        => 'end-date',
		'date'        => $this->overrideEndDate ? $this->endDate : null,
		'title'       => JText::_('COM_DPCALENDAR_FIELD_END_DATE_LABEL'),
		'placeholder' => JText::_('COM_DPCALENDAR_FIELD_END_DATE_LABEL'),
		'onchange'    => 'this.form.submit();',
		'dateFormat'  => $this->params->get('event_form_date_format', 'm.d.Y')
	)
);

$c = $form->addChild(new Container('location', ['container']));

// The location search input field
$i = $c->addChild(new Container('search', ['search']))->addChild(
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

$ac = $c->addChild(new Container('area', ['area']));

// Add the select box
$radius = $this->state->get('filter.radius', 50);
$s      = $ac->addChild(new Select('radius', 'radius'));
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
$s      = $ac->addChild(new Select('length-type', 'length-type'));
$s->addAttribute('onchange', 'this.form.submit();');
$s->addOption(JText::_('COM_DPCALENDAR_FIELD_CONFIG_MAP_LENGTH_TYPE_METER'), 'm', 'm' == $length);
$s->addOption(JText::_('COM_DPCALENDAR_FIELD_CONFIG_MAP_LENGTH_TYPE_MILE'), 'mile', 'mile' == $length);

// The limit input
$form->addChild(new Input('limitstart', 'hidden', 'limitstart'));
$form->addChild(new Input('filter_order', 'hidden', 'filter_order'));
$form->addChild(new Input('filter_order_Dir', 'hidden', 'filter_order_Dir'));

$c = $form->addChild(new Container('actions'));

// Add the submit button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'      => 'search',
		'type'    => Icon::OK,
		'root'    => $c,
		'text'    => 'JSEARCH_FILTER',
		'onclick' => "this.form.submit();"
	)
);

// Add the clear button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'      => 'cancel',
		'type'    => Icon::CANCEL,
		'root'    => $c,
		'text'    => 'JCLEAR',
		'onclick' => "var x = document.querySelectorAll('#dp-list-form input');for(i = 0; i < x.length; i++){x[i].value = '';}this.form.submit();"
	)
);
