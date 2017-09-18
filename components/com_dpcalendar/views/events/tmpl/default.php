<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$document = JFactory::getDocument();
$document->setMimeEncoding('application/json');

$user = JFactory::getUser();

$data = array();
foreach ($this->items as $event)
{
	$description = JLayoutHelper::render('event.tooltip', array(
			'event' => $event,
			'params' => $this->params
	));

	$locations = array();
	if (! empty($event->locations))
	{
		foreach ($event->locations as $location)
		{
			$locations[] = array(
					'location' => DPCalendarHelperLocation::format($location),
					'latitude' => $location->latitude,
					'longitude' => $location->longitude
			);
		}
	}

	$fgcolor = null;
	if ($this->params->get('adjust_fg_color', '0') == '1')
	{
		$fgcolor = $event->color;
		$rgb = '';
		for ($x = 0; $x < 3; $x ++)
		{
			$c = 255 - hexdec(substr($fgcolor, (2 * $x), 2));
			$c = ($c < 0) ? 0 : dechex($c);
			$rgb .= (strlen($c) < 2) ? '0' . $c : $c;
		}
		$fgcolor = '#' . $rgb;
	}
	if ($this->params->get('adjust_fg_color', '0') == '2')
	{
		$r = hexdec(substr($event->color, 0, 2));
		$g = hexdec(substr($event->color, 2, 2));
		$b = hexdec(substr($event->color, 4, 2));
		$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
		$fgcolor = ($yiq >= 128) ? '#000000' : '#ffffff';
	}
	$data[] = array(
			'id' => $event->id,
			'title' => $this->compactMode == 0 ? htmlspecialchars_decode($event->title) : utf8_encode(chr(160)),
			'start' => DPCalendarHelper::getDate($event->start_date, $event->all_day)->format('c', true),
			'end' => DPCalendarHelper::getDate($event->end_date, $event->all_day)->format('c', true),
			'url' => DPCalendarHelperRoute::getEventRoute($event->id, $event->catid),
			'editable' => DPCalendarHelper::getCalendar($event->catid)->canEdit != false,
			'color' => '#' . $event->color,
			'fgcolor' => $fgcolor,
			'allDay' => $this->compactMode == 0 ? (bool) $event->all_day : true,
			'description' => $description,
			'location' => $locations
	);
}

$messages = JFactory::getApplication()->getMessageQueue();

// Build the sorted messages list
$lists = array();
if (is_array($messages) && count($messages))
{
	foreach ($messages as $message)
	{
		if (isset($message['type']) && isset($message['message']))
		{
			$lists[$message['type']][] = $message['message'];
		}
	}
}

ob_clean();
echo json_encode(array(
		array(
				'data' => $data,
				'messages' => $lists
		)
));

JFactory::getApplication()->close();
