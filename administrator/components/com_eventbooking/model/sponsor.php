<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

class EventbookingModelSponsor extends RADModelAdmin
{
	/**
	 * Store the the speaker - events assignment
	 *
	 * @param         $row
	 * @param         $input
	 * @param   bool  $isNew
	 */
	protected function afterStore($row, $input, $isNew)
	{
		$db       = $this->getDbo();
		$query    = $db->getQuery(true);
		$eventIds = $input->get('event_id', [], 'array');
		$eventIds = array_filter(ArrayHelper::toInteger($eventIds));

		// Delete the old speaker events assignment
		if (!$isNew)
		{
			$query->delete('#__eb_event_sponsors')
				->where('sponsor_id = ' . $row->id);
			$db->setQuery($query)
				->execute();
		}

		if (count($eventIds))
		{
			$query->clear()
				->insert('#__eb_event_sponsors')->columns('sponsor_id, event_id');

			foreach ($eventIds as $eventId)
			{
				$query->values("$row->id, $eventId");
			}

			$db->setQuery($query)
				->execute();
		}
	}
}
