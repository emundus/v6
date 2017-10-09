<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Component\Badge;
use CCL\Content\Element\Basic\Link;
use CCL\Content\Element\Basic\TextBlock;
use CCL\Content\Element\Component\Icon;
use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Heading;
use CCL\Content\Element\Component\Grid;
use CCL\Content\Element\Component\Grid\Column;
use CCL\Content\Element\Component\Grid\Row;

// The params
$params = $this->params;

// The return url
$return = $this->input->getInt('Itemid', null);
if ($return) {
	$return = JRoute::_('index.php?option=com_dpcalendar&Itemid=' . $return);
}

/** @var Container $root * */
$root = $this->root->addChild(new Container('events'));

// Loop over the events
foreach ($this->items as $event) {
	// The start date
	$startDate = DPCalendarHelper::getDate($event->start_date, $event->all_day);

	// The calendar
	$calendar = DPCalendarHelper::getCalendar($event->catid);

	// The list item container
	$item = $root->addChild(new Container($event->id));

	// The heading of the event
	$h = $item->addChild(new Heading('event-header', 2, array('dp-event-header')));
	$h->setProtectedClass('dp-event-header');

	// When we are shown in a modal dialog, make the title clickable
	$url  = str_replace(
		array('?tmpl=component', 'tmpl=component'),
		'',
		DPCalendarHelperRoute::getEventRoute($event->id, $event->catid)
	);
	$link = $h->addChild(new Link('link', $url, '_parent'));
	$link->addChild(new TextBlock('text'))->setContent($event->title);

	// Show the icon when activated
	if ($params->get('list_show_icon')) {
		// The calendar icon element
		$cal = $item->addChild(new Container('calendar-icon', array('calendar-icon')));
		$cal->addChild(new TextBlock('day', array('day')))->setContent($startDate->format('j', true));
		$m = $cal->addChild(new TextBlock('month', array('month')))->setContent($startDate->format('M', true));

		// Add per event the color for the calendar icon
		JFactory::getDocument()->addStyleDeclaration('#' . $m->getId() . ' {background-color: #' . $event->color . '; box-shadow: 0 2px 0 #' . $event->color . ';}');
	}

	if ($this->params->get('list_show_hits', 1)) {
		// The hits element
		$b = $item->addChild(new Badge('hits', array('hits')));
		$b->setContent(JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_HITS') . ':' . $event->hits);
	}

	// The location elements
	if (isset($event->locations) && $event->locations) {
		// Deactivate the description in the tooltip layout
		$params->set('tooltip_show_description', false);

		// The locations container
		$ls = $item->addChild(
			new TextBlock(
				'locations',
				array('locations')
			)
		);

		foreach ($event->locations as $location) {
			$lc = $ls->addChild(new Container($location->id, array('location')));

			// The link to the location
			$l = $lc->addChild(new Link($location->id, DPCalendarHelperRoute::getLocationRoute($location)));
			$l->addClass('location-details', true);
			$l->addClass('location-details', true);
			$l->addAttribute('data-latitude', $location->latitude);
			$l->addAttribute('data-longitude', $location->longitude);
			$l->addAttribute('data-title', $location->title);
			$l->addAttribute('data-color', $event->color);
			$l->setContent($location->title);

			// The tooltip for the map
			$d = $lc->addChild(new TextBlock($location->id . '-description'));
			$d->addClass('location-description', true);
			DPCalendarHelper::renderLayout('event.tooltip', array('event' => $event, 'root' => $d, 'params' => $params));
		}
	}

	// If possible add the book link
	if (\DPCalendar\Helper\Booking::openForBooking($event)) {
		$l = $item->addChild(new Link('book', DPCalendarHelperRoute::getBookingFormRouteFromEvent($event, $return)));
		$l->addChild(new Icon('book-icon', Icon::PLUS, array(), array('title' => JText::_('COM_DPCALENDAR_BOOK'))));
	}

	// If possible add the edit link
	if ($calendar->canEdit || ($calendar->canEditOwn && $event->created_by == $user->id)) {
		$l = $item->addChild(new Link('edit', DPCalendarHelperRoute::getFormRoute($event->id, $return)));
		$l->addChild(new Icon('book-icon', Icon::EDIT, array(), array('title' => JText::_('JACTION_EDIT'))));
	}

	// If possible add the delete link
	if ($calendar->canDelete || ($calendar->canEditOwn && $event->created_by == $user->id)) {
		$l = $item->addChild(
			new Link(
				'edit',
				JRoute::_('index.php?option=com_dpcalendar&task=event.delete&e_id=' . $event->id . '&return=' . base64_encode($return))
			)
		);
		$l->addChild(new Icon('book-icon', Icon::DELETE, array(), array('title' => JText::_('JACTION_DELETE'))));
	}

	// The date element
	$d = $item->addChild(new TextBlock('date', array('date')));
	$d->setContent('(' . JText::_('COM_DPCALENDAR_DATE') . ': ');
	$d->setContent(
		DPCalendarHelper::getDateStringFromEvent(
			$event,
			$params->get('event_date_format', 'm.d.Y'),
			$params->get('event_time_format', 'g:i a')
		),
		true
	);
	$d->setContent(')', true);

	// The calendar element
	$c = $item->addChild(new TextBlock('calendar', array('calendar')));
	$c->setContent(JText::_('COM_DPCALENDAR_CALENDAR') . ': ');
	$c->setContent($calendar != null ? $calendar->title : $event->catid, true);

	// The capacity element
	if ($event->capacity === null) {
		$c = $item->addChild(new TextBlock('capacity', array('capacity')));
		$c->setContent(JText::_('COM_DPCALENDAR_FIELD_CAPACITY_LABEL') . ': ' . JText::_('COM_DPCALENDAR_FIELD_CAPACITY_UNLIMITED'));
	} else {
		if ($event->capacity > 0) {
			$c = $item->addChild(new TextBlock('capacity', array('capacity')));
			$c->setContent(
				JText::_('COM_DPCALENDAR_FIELD_CAPACITY_LABEL') . ': ' . ($event->capacity - $event->capacity_used) . '/' . (int)$event->capacity
			);
		}
	}
	$c = $item->addChild(new TextBlock('price', array('price')));
	$c->setContent(JText::_($event->price ? 'COM_DPCALENDAR_VIEW_BLOG_PAID_EVENT' : 'COM_DPCALENDAR_VIEW_BLOG_FREE_EVENT'));

	// The container with the event description
	$desc = new Container('content');

	// Set the event description as content
	$desc->setContent(JHTML::_('content.prepare', $event->description));

	// The description will be cut when configured
	if ($params->get('list_description_length', null) !== null) {
		// Truncate
		$descTruncated = JHtmlString::truncateComplex(
			$desc->getContent(),
			$params->get('list_description_length', null)
		);
		if ($desc->getContent() != $descTruncated) {
			// Set up for readmore
			$desc->setContent($descTruncated);
			$params->set('access-view', true);
			$event->alternative_readmore = JText::_('COM_DPCALENDAR_READ_MORE');
			$desc->setContent(
				JLayoutHelper::render(
					'joomla.content.readmore',
					array(
						'item'   => $event,
						'params' => $params,
						'link'   => DPCalendarHelperRoute::getEventRoute($event->id, $event->catid)
					)
				),
				true
			);
		}
	}

	// Show the images
	$images = new Container('images');
	DPCalendarHelper::renderLayout('event.images', array('event' => $event, 'root' => $images));

	if (!$images->getChildren()) {
		// No images so append the description directly
		$item->addChild($desc);
	} else {
		// Add the description and images in a grid
		$grid = $item->addChild(new Grid('details'));
		$row  = $grid->addRow(new Row('row'));
		$row->addColumn(new Column('description', 50))->addChild($desc);
		$row->addColumn(new Column('images', 50))->setContent($images->getChildren());
	}

	// Add the price schema
	DPCalendarHelper::renderLayout('schema.event', array('event' => $event, 'root' => $c));
}
