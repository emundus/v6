<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$event = $displayData['event'];
if (! $event)
{
	return;
}
$params = $displayData['params'];
if (! $params)
{
	$params = new JRegistry();
}

$return = JFactory::getApplication()->input->getInt('Itemid', null);
if (! empty($return))
{
	$uri = clone JUri::getInstance();
	$uri = $uri->toString(array(
			'scheme',
			'host',
			'port'
	));
	$return = $uri . JRoute::_('index.php?Itemid=' . $return, false);
}
$user = JFactory::getUser();

$description = '<a href="' . DPCalendarHelperRoute::getEventRoute($event->id, $event->catid) . '" class="dp-event-link">' . $event->title . '</a>';
$description .= '<br/>' . DPCalendarHelper::getDateStringFromEvent($event, $params->get('event_date_format', 'm.d.Y'),
		$params->get('event_time_format', 'g:i a'));
$description .= '<br/>';

if ($params->get('tooltip_show_description', 1))
{
	$description .= JHtml::_('string.truncate', $event->description, 100);
}

$description .=  '<hr id="dp-popup-window-divider"/>';

if (DPCalendarHelperBooking::openForBooking($event))
{
	$description .= ' <a href="' . JRoute::_(DPCalendarHelperRoute::getBookingFormRouteFromEvent($event, $return), false) . '">';
	$description .= JText::_('COM_DPCALENDAR_BOOK');
	$description .= '</a>';
}

$calendar = DPCalendarHelper::getCalendar($event->catid);
if ($calendar->canEdit || ($calendar->canEditOwn && $event->created_by == $user->id))
{
	$description .= ' <a href="' . JRoute::_(DPCalendarHelperRoute::getFormRoute($event->id, $return)) . '">' . JText::_('JACTION_EDIT') . '</a>';
}
if ($calendar->canDelete || ($calendar->canEditOwn && $event->created_by == $user->id))
{
	$description .= ' <a href="' .
			 JRoute::_('index.php?option=com_dpcalendar&task=event.delete&e_id=' . $event->id . '&return=' . base64_encode($return), false) . '">' .
			 JText::_('JACTION_DELETE') . '</a>';
}

echo $description;
