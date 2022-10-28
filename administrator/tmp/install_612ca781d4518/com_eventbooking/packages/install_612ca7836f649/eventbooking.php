<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class PlgUserEventbooking extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array    $config   An optional associative array of configuration settings.
	 *
	 */
	public function __construct(&$subject, $config = array())
	{
		if (!file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Remove all subscriptions for the user if configured
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was successfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$this->app)
		{
			return ;
		}

		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$userId = (int) $user['id'];

		if (!$userId)
		{
			return true;
		}

		if ($this->params->get('delete_user_events'))
		{
			$query->select('id')
				->from('#__eb_events')
				->where('created_by = ' . $userId);
			$db->setQuery($query);
			$eventIds = $db->loadColumn();

			if (count($eventIds))
			{
				$this->deleteEvents($eventIds);
			}
		}

		if ($this->params->get('delete_user_registrations'))
		{
			$query->clear()
				->select('id')
				->from('#__eb_registrants')
				->where('user_id = ' . $userId);
			$db->setQuery($query);
			$registrantIds = $db->loadColumn();

			if (count($registrantIds))
			{
				$this->deleteRegistrants($registrantIds);
			}
		}

		return true;
	}

	/**
	 * Method to delete events
	 *
	 * @param   array  $cid
	 */
	private function deleteEvents($cid)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__eb_events')
			->where('parent_id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$cid  = array_merge($cid, $db->loadColumn());
		$cids = implode(',', $cid);

		//Delete price setting for events
		$query->clear()
			->delete('#__eb_event_group_prices')->where('event_id IN (' . $cids . ')');
		$db->setQuery($query);
		$db->execute();

		//Delete categories for the event
		$query->clear()
			->delete('#__eb_event_categories')->where('event_id IN (' . $cids . ')');
		$db->setQuery($query);
		$db->execute();

		// Delete ticket types related to events
		$query->clear()
			->delete('#__eb_ticket_types')
			->where('event_id IN (' . $cids . ')');
		$db->setQuery($query);
		$db->execute();

		// Delete the URLs related to event
		$query->clear()
			->delete('#__eb_urls')
			->where($db->quoteName('view') . '=' . $db->quote('event'))
			->where('record_id IN (' . $cids . ')');
		$db->setQuery($query);
		$db->execute();

		//Delete events themself
		$query->clear()
			->delete('#__eb_events')->where('id IN (' . $cids . ')');
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Method to delete given registrants
	 *
	 * @param   array  $cid
	 *
	 * @return bool
	 */
	private function deleteRegistrants($cid = [])
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$cids = implode(',', $cid);
		$query->select('id')
			->from('#__eb_registrants')
			->where('group_id IN (' . $cids . ')');
		$db->setQuery($query);

		$cid           = array_merge($cid, $db->loadColumn());
		$registrantIds = implode(',', $cid);

		$query->clear()
			->delete('#__eb_field_values')
			->where('registrant_id IN (' . $registrantIds . ')');
		$db->setQuery($query)
			->execute();

		$query->clear()
			->delete('#__eb_registrants')
			->where('id IN (' . $registrantIds . ')');
		$db->setQuery($query)
			->execute();

		$query->clear()
			->delete('#__eb_registrant_tickets')
			->where('registrant_id IN (' . $registrantIds . ')');
		$db->setQuery($query);

		return true;
	}
}
