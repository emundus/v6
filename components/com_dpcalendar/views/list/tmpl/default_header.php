<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Form\Input;
use CCL\Content\Element\Component\Icon;
use CCL\Content\Element\Basic\TextBlock;
use CCL\Content\Element\Basic\Form;

// The form element
$tmpl = JFactory::getApplication()->input->getCmd('tmpl');
if ($tmpl) {
	$tmpl = '&tmpl=' . $tmpl;
}

/** @var Form $form * */
$form = $this->root->addChild(
	new Form(
		'form',
		JRoute::_('index.php?option=com_dpcalendar&view=list&Itemid=' . JFactory::getApplication()->input->getInt('Itemid') . $tmpl),
		'adminForm',
		'POST',
		array('form-validate')
	)
);
$form->addClass('noprint', true);
$form->addClass('dp-actions-container', true);

// The location search input field
$i = $form->addChild(new Input('search-filter', 'text', 'filter-search', $this->state->get('filter.search')));
$i->addAttribute('onchange', 'this.form.submit();');
$i->addAttribute('placeholder', JText::_('JGLOBAL_FILTER_LABEL'));

// Add the submit button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::SEARCH,
		'root'    => $form,
		'title'   => 'JSEARCH_FILTER',
		'onclick' => "this.form.submit();"
	)
);

// Add the clear button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'id'      => 'clear',
		'type'    => Icon::DELETE,
		'root'    => $form,
		'title'   => 'COM_DPCALENDAR_CLEAR',
		'onclick' => "jQuery('#dp-list-form-search').val('');this.form.submit();"
	)
);

$form->addChild(new TextBlock('date'))->setContent(
	$this->startDate->format($this->params->get('list_title_format', 'm.d.Y'), true) .
	' - ' .
	$this->endDate->format($this->params->get('list_title_format', 'm.d.Y'), true)
);

$options               = array();
$options['onchange']   = 'this.form.submit();';
$options['allDay']     = true;
$options['button']     = true;
$options['dateFormat'] = DPCalendarHelper::getComponentParameter('event_form_date_format', 'm.d.Y');
$options['timeFormat'] = DPCalendarHelper::getComponentParameter('event_form_time_format', 'g:i a');

// Add the print button
DPCalendarHelper::renderLayout(
	'content.button.print',
	array(
		'id'       => 'print',
		'root'     => $form,
		'selector' => 'dp-list'
	)
);

// Add the datepicker button
DPCalendarHelper::renderLayout(
	'content.button.datetime',
	array(
		'id'      => 'jump',
		'root'    => $form,
		'name'    => 'jump',
		'date'    => $this->state->get('list.start-date'),
		'options' => $options
	)
);

// The limit input
$this->root->addChild(new Input('limitstart', 'hidden', 'limitstart'));
$this->root->addChild(new Input('filter_order', 'hidden', 'filter_order'));
$this->root->addChild(new Input('filter_order_Dir', 'hidden', 'filter_order_Dir'));
