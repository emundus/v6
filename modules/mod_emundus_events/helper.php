<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die('Access Deny');

class modEmundusEventsHelper {

	public static function getEvents($table)
	{
		$events = [];

		try
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id, title, description, start_date, end_date, link')
				->from($table)
				->where('published = 1')
				->where('end_date >= NOW()')
				->order('start_date ASC');
			$db->setQuery($query);
			$events = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			Log::add('Error: ' . $e->getMessage(), Log::ERROR, 'mod_emundus_events');
		}

		return $events;
	}
}
