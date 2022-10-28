<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JLoader::register('EventbookingModelCommonRegistrant', JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/common/registrant.php');

class EventbookingModelRegistrant extends EventbookingModelCommonRegistrant
{
	/**
	 * Load the record from database
	 */
	protected function loadData()
	{
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$currentDate = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
		$query->select('a.*, b.registrant_edit_close_date')
			->select("TIMESTAMPDIFF(MINUTE, registrant_edit_close_date, $currentDate) AS edit_close_minutes")
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.id = ' . (int) $this->state->id);
		$db->setQuery($query);
		$this->data = $db->loadObject();
	}
}
