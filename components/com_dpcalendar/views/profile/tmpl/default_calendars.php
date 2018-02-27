<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Element;
use CCL\Content\Element\Basic\Form\Input;
use CCL\Content\Element\Basic\Paragraph;
use CCL\Content\Element\Basic\TextBlock;
use CCL\Content\Element\Component\Icon;
use CCL\Content\Element\Basic\Form;
use CCL\Content\Element\Basic\Heading;
use CCL\Content\Element\Basic\ListContainer;
use CCL\Content\Element\Basic\ListItem;
use CCL\Content\Element\Basic\Link;

// Text when select box is empty
JText::script('COM_DPCALENDAR_CONFIRM_DELETE');

// The item id query parameter
$itemId = '&Itemid=' . $this->input->getInt('Itemid');

// The return parameter
$return = base64_encode(JUri::getInstance());

// The form element
$form = $this->root->addChild(
	new Form(
		'form',
		JRoute::_('index.php?option=com_dpcalendar&view=profile' . $itemId),
		'adminForm',
		'POST',
		array('form-validate')
	)
);

// The
$form->addChild(new Heading('heading', 3))->setContent(JText::_('COM_DPCALENDAR_VIEW_PROFILE_CALENDARS'));

/** @var Container $root * */
$actions = $form->addChild(new Container('actions', array('noprint')));
$actions->setProtectedClass('noprint');

// Add the search box
$search = $actions->addChild(new Input('filter', 'text', 'filter-search', $this->state->get('filter.search')));
$search->addAttribute('onchange', 'this.form.submit();');
$search->addAttribute('placeholder', JText::_('JGLOBAL_FILTER_LABEL'));

// Add the submit button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::SEARCH,
		'root'    => $actions,
		'title'   => 'JSEARCH_FILTER',
		'onclick' => "this.form.submit();"
	)
);

// Add the clear button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::DELETE,
		'root'    => $actions,
		'title'   => 'COM_DPCALENDAR_CLEAR',
		'onclick' => "jQuery('#dp-tickets-actions-filter').val('');"
	)
);

// The limit container
$c = $actions->addChild(new Container('limit-container'));

// The limit text block
$c->addChild(new TextBlock('limit'))->setContent(JText::_('JGLOBAL_DISPLAY_NUM'));

// The limit select box
$c->addChild(new TextBlock('pagination'))->setContent($this->pagination->getLimitBox());

// The calendars list
$list = $form->addChild(new ListContainer('calendars', ListContainer::UNORDERED));

// Loop over the existing calendars
foreach ($this->calendars as $url => $calendar) {
	// The list item
	$item = $list->addListItem(new ListItem($calendar->id));

	$item->addChild(new Container('color', array('color-box'), array('style' => 'background-color: #' . $calendar->calendarcolor)));

	// Check if we are the owner of the calendar
	if (empty($calendar->member_principal_access)) {
		// The delete icon
		$l = $item->addChild(
			new Link(
				'delete',
				JRoute::_('index.php?option=com_dpcalendar&task=davcalendar.delete&return=' . $return . '&c_id=' . (int)$calendar->id)
			)
		);
		$l->addClass('delete-action');
		$l->addChild(new Icon('icon', Icon::DELETE))->addAttribute('title', JText::_('COM_DPCALENDAR_VIEW_PROFILE_DELETE_PROFILE_CALENDAR'));

		// The new event icon
		$l = $item->addChild(new Link('create', DPCalendarHelperRoute::getFormRoute(0, JUri::getInstance(), 'catid=cd-' . (int)$calendar->id)));
		$l->addChild(new Icon('icon', Icon::PLUS))->addAttribute('title', JText::_('COM_DPCALENDAR_VIEW_PROFILE_CREATE_EVENT_IN_CALENDAR'));

		// The edit link
		$l = $item->addChild(
			new Link(
				'edit',
				JRoute::_('index.php?option=com_dpcalendar&task=davcalendar.edit&c_id=' . (int)$calendar->id . $itemId . '&return=' . $return)
			)
		);
		$l->addClass('title');
		$l->setContent($calendar->displayname);
	} else {
		// The text with the information that this calendar is shared
		$text = JText::sprintf(
			'COM_DPCALENDAR_VIEW_PROFILE_SHARED_CALENDAR',
			$calendar->member_principal_name,
			JText::_('COM_DPCALENDAR_VIEW_PROFILE_SHARED_CALENDAR_ACCESS_' . (strpos($calendar->member_principal_access,
					'/calendar-proxy-read') !== false ? 'READ' : 'WRITE'))
		);

		// The lock icon
		$item->addChild(new TextBlock('lock'))->addChild(new Icon('icon', Icon::LOCK, array(), array('title' => $text)));

		// The calendar name
		$item->addChild(new TextBlock('title'))->setContent($calendar->displayname);
	}


	$u = $item->addChild(new TextBlock('url'));
	$u->addChild(new TextBlock('label'))->setContent(JText::_('COM_DPCALENDAR_VIEW_PROFILE_TABLE_CALDAV_URL_LABEL') . ': ');
	$u->addChild(new Link('link', JUri::base() . 'components/com_dpcalendar/caldav.php/' . $url, '_blank'))->setContent($calendar->uri);
}

// Add the new button
DPCalendarHelper::renderLayout(
	'content.button',
	array(
		'type'    => Icon::PLUS,
		'root'    => $form->addChild(new TextBlock('add')),
		'text'    => 'COM_DPCALENDAR_VIEW_PROFILE_CREATE_PROFILE_CALENDAR',
		'onclick' => "location.href='" . JRoute::_('index.php?option=com_dpcalendar&task=davcalendar.add&return=' . $return . '&c_id=0') . "'"
	)
);

// The limit input
$form->addChild(new Input('limitstart', 'hidden', 'limitstart'));
$form->addChild(new Input('filter_order', 'hidden', 'filter_order'));
$form->addChild(new Input('filter_order_Dir', 'hidden', 'filter_order_Dir'));
$form->addChild(new Input('token', 'hidden', 'token', JSession::getFormToken()));

// The pagination container
$c = $form->addChild(new Container('limitstart', array('pagination', 'noprint')));
$c->setProtectedClass('pagination');
$c->setProtectedClass('noprint');

$c->addChild(new Paragraph('counter'))->setContent($this->pagination->getPagesCounter());
$c->addChild(new Element('links'))->setContent($this->pagination->getPagesLinks());
