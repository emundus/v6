<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query->select('Event_title, e.id, Event_date, l.label AS location')->from('po_events AS e')->where('where Event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE() INTERVAL 10 DAY)')
->where('Event_confirmed = 1')
->join('LEFT', 'po_events_77_repeat AS loc ON loc.parent_id = e.id')
->join('LEFT', 'po_locations AS l ON l.id = loc.location_id')->
order('e.id');

$db->setQuery($query);

$rows = $db->loadOjectList();
echo "<pre>";print_r($rows);exit;
