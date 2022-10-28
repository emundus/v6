<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class EventbookingHelperAcl
{
	/**
	 * Check to see whether the current users can access View List function
	 *
	 * @param   int  $eventId
	 *
	 * @return bool
	 */
	public static function canViewRegistrantList($eventId = 0)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideAcl', 'canViewRegistrantList'))
		{
			return EventbookingHelperOverrideAcl::canViewRegistrantList($eventId);
		}

		static $canViewRegistrantList = null;

		if ($canViewRegistrantList === null)
		{
			$canViewRegistrantList =  Factory::getUser()->authorise('eventbooking.viewregistrantslist', 'com_eventbooking');
		}

		return $canViewRegistrantList;
	}

	/**
	 * Method to check whether the current user can edit the given registration record
	 *
	 * @param   EventbookingTableRegistrant  $rowRegistrant
	 *
	 * @return bool
	 */
	public static function canEditRegistrant($rowRegistrant)
	{
		$user = Factory::getUser();

		if ($user->id && ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking')
				|| $user->get('id') == $rowRegistrant->user_id
				|| $user->get('email') == $rowRegistrant->email)
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to check if the current user is allowed to manage the given registrant and perform necessary actions
	 *
	 * @param $rowRegistrant
	 */
	public static function canManageRegistrant($rowRegistrant)
	{
		$config = EventbookingHelper::getConfig();
		$user   = Factory::getUser();

		if ($user->authorise('core.admin', 'com_eventbooking'))
		{
			return true;
		}

		if (!property_exists($rowRegistrant, 'created_by'))
		{
			$event                     = EventbookingHelperDatabase::getEvent($rowRegistrant->event_id);
			$rowRegistrant->created_by = $event->created_by;
		}

		if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking')
			&& (!$config->only_show_registrants_of_event_owner || $user->id == $rowRegistrant->created_by))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check to see whether this event can be cancelled
	 *
	 * @param   int  $eventId
	 *
	 * @return bool
	 */
	public static function canCancel($eventId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__eb_events')
			->where('id = ' . $eventId)
			->where(' enable_cancel_registration = 1')
			->where('(DATEDIFF(cancel_before_date, NOW()) >=0)');
		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total)
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to check whether the current user can export registrants of certain events or all events
	 *
	 * @param   int  $eventId
	 *
	 * @return bool
	 */
	public static function canExportRegistrants($eventId = 0)
	{
		$user = Factory::getUser();

		if ($user->authorise('core.admin', 'com_eventbooking'))
		{
			return true;
		}

		if ($eventId)
		{
			$config = EventbookingHelper::getConfig();
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true);
			$query->select('created_by')
				->from('#__eb_events')
				->where('id = ' . (int) $eventId);
			$db->setQuery($query);
			$createdBy = (int) $db->loadResult();

			if ($config->only_show_registrants_of_event_owner)
			{
				return $createdBy > 0 && $createdBy == $user->id;
			}
			else
			{
				return ($createdBy > 0 && $createdBy == $user->id) || $user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking');
			}

		}
		else
		{
			return $user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking');
		}
	}

	/**
	 * Check to see whether the current user can change status (publish/unpublish) of the given event
	 *
	 * @param $eventId
	 *
	 * @return bool
	 */
	public static function canChangeEventStatus($eventId)
	{
		$user = Factory::getUser();

		if ($user->get('guest'))
		{
			return false;
		}

		if ($user->authorise('core.admin', 'com_eventbooking'))
		{
			return true;
		}

		if ($user->authorise('core.edit.state', 'com_eventbooking'))
		{
			if (empty($eventId))
			{
				return true;
			}

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('created_by')
				->from('#__eb_events')
				->where('id = ' . (int) $eventId);
			$db->setQuery($query);
			$createdBy = (int) $db->loadResult();

			if ($createdBy == $user->id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Check to see whether the user can cancel registration for the given event
	 *
	 * @param $eventId
	 *
	 * @return bool|int
	 */
	public static function canCancelRegistration($eventId)
	{
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$user   = Factory::getUser();
		$userId = $user->get('id');
		$email  = $user->get('email');

		if (!$userId)
		{
			return false;
		}

		$query->select('id')
			->from('#__eb_registrants')
			->where('event_id = ' . $eventId)
			->where('(user_id = ' . $userId . ' OR email = ' . $db->quote($email) . ')')
			->where('(published = 1 OR (published = 0 AND payment_method LIKE "os_offline%"))');

		$db->setQuery($query);
		$registrantId = $db->loadResult();

		if (!$registrantId)
		{
			return false;
		}

		$currentDate = $db->quote(EventbookingHelper::getServerTimeFromGMTTime('Now', 'Y-m-d H:i:s'));
		$nullDate    = $db->quote($db->getNullDate());

		$query->clear()
			->select('COUNT(*)')
			->from('#__eb_events')
			->where('id = ' . $eventId)
			->where('enable_cancel_registration = 1')
			->where('(cancel_before_date >= ' . $currentDate . ' OR (cancel_before_date = ' . $nullDate . ' AND event_date >= ' . $currentDate . '))');
		$db->setQuery($query);
		$total = $db->loadResult();

		if (!$total)
		{
			return false;
		}

		return $registrantId;
	}

	/**
	 * Check to see whether the current users can add events from front-end
	 */
	public static function checkAddEvent()
	{
		return Factory::getUser()->authorise('eventbooking.addevent', 'com_eventbooking');
	}

	/**
	 * Check to see whether the current user can edit registrant
	 *
	 * @param   int  $eventId
	 *
	 * @return boolean
	 */
	public static function checkEditEvent($eventId)
	{
		$user = Factory::getUser();

		if ($user->get('guest'))
		{
			return false;
		}

		if (!$eventId)
		{
			return false;
		}

		$rowEvent = EventbookingHelperDatabase::getEvent($eventId);

		if (!$rowEvent)
		{
			return false;
		}

		if ($user->authorise('core.edit', 'com_eventbooking') || ($user->authorise('core.edit.own', 'com_eventbooking') && ($rowEvent->created_by == $user->get('id'))))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check to see whether the current user can delete the given registrant
	 *
	 * @param   int  $id
	 *
	 * @return bool
	 */
	public static function canDeleteRegistrant($id = 0)
	{
		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();

		if (!$user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			return false;
		}

		if ($user->authorise('core.delete', 'com_eventbooking'))
		{
			return true;
		}

		if ($config->get('only_show_registrants_of_event_owner') && $config->get('enable_delete_registrants', 1))
		{
			if ($id == 0)
			{
				return true;
			}

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('b.created_by')
				->from('#__eb_registrants AS a')
				->innerJoin('#__eb_events AS b ON a.event_id = b.id')
				->where('a.id = ' . $id);
			$db->setQuery($query);
			$eventCreatorID = $db->loadObject();

			if ($eventCreatorID == $user->id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Method to check whether the current user can edit the event
	 *
	 * @param   EventbookingTableEvent  $event
	 *
	 * @return bool
	 */
	public static function canEditEvent($event)
	{
		$user = Factory::getUser();

		if ($user->authorise('core.edit', 'com_eventbooking')
			|| ($user->authorise('core.edit.own', 'com_eventbooking') && ($event->created_by == $user->id)))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to check whether the current user can publish/unpublish event
	 *
	 * @param   EventbookingTableEvent  $event
	 *
	 * @return bool
	 */
	public static function canPublishUnpublishEvent($event)
	{
		$user = Factory::getUser();

		if ($user->authorise('core.admin', 'com_eventbooking'))
		{
			return true;
		}

		if ($event->created_by == $user->id && $user->authorise('core.edit.state', 'com_eventbooking'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to check to see whether the current user can export registrants of given event
	 *
	 * @param $event
	 *
	 * @return bool
	 */
	public static function canExportEventRegistrant($event)
	{
		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();

		if ($config->only_show_registrants_of_event_owner)
		{
			return $event->created_by > 0 && $event->created_by == $user->id;
		}

		return ($event->created_by > 0 && $event->created_by == $user->id) || $user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking');
	}

	/**
	 * Method to check if user has permission to delete an event in frontend
	 *
	 * @param   int  $id
	 *
	 * @return bool
	 */
	public static function canDeleteEvent($id = 0)
	{
		$config = EventbookingHelper::getConfig();

		// If frontend event deletion is not enabled, return false
		if (!$config->get('enable_delete_events'))
		{
			return false;
		}

		$user = Factory::getUser();

		// User with core.admin permission will be allowed to delete any events
		if (!$id || $user->authorise('core.admin', 'com_eventbooking'))
		{
			return true;
		}


		// Event's owner is allowed to delete own event

		$event = EventbookingHelperDatabase::getEvent($id);

		if ($event->created_by > 0 && $event->created_by == $user->id)
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to check if user has permission to cancel an event in frontend
	 *
	 * @param   int  $id
	 *
	 * @return bool
	 */
	public static function canCancelEvent($id = 0)
	{
		$config = EventbookingHelper::getConfig();

		// If frontend event cancel is not enabled, return false
		if (!$config->get('enable_cancel_events'))
		{
			return false;
		}

		// If invalid ID of the event is passed, return false
		if (!$id)
		{
			return false;
		}

		$user = Factory::getUser();

		// User with core.admin permission will be allowed to cancel any events
		if (!$id || $user->authorise('core.admin', 'com_eventbooking'))
		{
			return true;
		}

		// Event's owner is allowed to delete own event
		$event = EventbookingHelperDatabase::getEvent($id);

		if ($event->created_by > 0 && $event->created_by == $user->id)
		{
			return true;
		}

		return false;
	}
}