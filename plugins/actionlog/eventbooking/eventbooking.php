<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class plgActionlogEventbooking extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @since       6.4.0
	 */
	public function __construct(& $subject, $config)
	{
		// Make sure Akeeba Backup is installed
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking'))
		{
			return;
		}

		if (!ComponentHelper::isEnabled('com_eventbooking'))
		{
			return;
		}

		// No point in logging guest actions
		if (Factory::getUser()->guest)
		{
			return;
		}

		// If any of the above statement returned, our plugin is not attached to the subject, so it's basically disabled
		parent::__construct($subject, $config);
	}

	/**
	 * Log add/edit event action
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   bool                    $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		$message = [];

		if ($isNew)
		{
			$messageKey = 'EB_LOG_EVENT_ADDED';
		}
		else
		{
			$messageKey = 'EB_LOG_EVENT_UPDATED';
		}

		$message['eventlink'] = 'index.php?option=com_eventbooking&view=event&id=' . $row->id;
		$message['title']     = $row->title;

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log publish/unpublish event action
	 *
	 * @param   string  $context
	 * @param   array   $pks
	 * @param           $value
	 */
	public function onEventChangeState($context, $pks, $value)
	{
		$message = [];

		if ($value)
		{
			$messageKey = 'EB_LOG_EVENTS_PUBLISHED';
		}
		else
		{
			$messageKey = 'EB_LOG_EVENTS_UNPUBLISHED';
		}

		$message['ids']          = implode(',', $pks);
		$message['numberevents'] = count($pks);

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log delete events action
	 *
	 * @param   string  $context
	 * @param   array   $pks
	 */
	public function onEventsAfterDelete($context, $pks)
	{
		$message = [];

		$messageKey              = 'EB_LOG_EVENTS_DELETED';
		$message['ids']          = implode(',', $pks);
		$message['numberevents'] = count($pks);

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log add/edit registrant action
	 *
	 * @param   string                       $context
	 * @param   EventbookingTableRegistrant  $row
	 * @param   bool                         $isNew
	 */
	public function onRegistrantAfterSave($context, $row, $isNew)
	{
		$message = [];

		if ($isNew)
		{
			$messageKey = 'EB_LOG_REGISTRANT_ADDED';
		}
		else
		{
			$messageKey = 'EB_LOG_REGISTRANT_UPDATED';
		}

		$message['id']             = $row->id;
		$message['name']           = trim($row->first_name . ' ' . $row->last_name);
		$message['registrantlink'] = 'index.php?option=com_eventbooking&view=registrant&id=' . $row->id;

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log delete registrants action
	 *
	 * @param   string  $context
	 * @param   array   $pks
	 */
	public function onRegistrantsAfterDelete($context, $pks)
	{
		$message = [];

		$messageKey                   = 'EB_LOG_REGISTRANTS_DELETED';
		$message['ids']               = implode(',', $pks);
		$message['numberregistrants'] = count($pks);

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log publish/unpublish event action
	 *
	 * @param   string  $context
	 * @param   array   $pks
	 * @param           $value
	 */
	public function onRegistrantChangeState($context, $pks, $value)
	{
		$message = [];

		if ($value)
		{
			$messageKey = 'EB_LOG_REGISTRANTS_PUBLISHED';
		}
		else
		{
			$messageKey = 'EB_LOG_REGISTRANTS_UNPUBLISHED';
		}

		$message['ids']               = implode(',', $pks);
		$message['numberregistrants'] = count($pks);

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log publish/unpublish event action
	 *
	 * @param   RADModelState  $state
	 * @param   int            $numberRegistrants
	 */
	public function onRegistrantsExport($state, $numberRegistrants)
	{
		$message = [];

		$message['numberregistrants'] = $numberRegistrants;

		if ($eventId = $state->get('filter_event_id'))
		{
			$messageKey    = 'EB_LOG_EVENT_REGISTRANTS_EXPORTED';
			$message['id'] = $eventId;
		}
		else
		{
			$messageKey = 'EB_LOG_REGISTRANTS_EXPORTED';
		}

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log an action
	 *
	 * @param   array   $message
	 * @param   string  $messageKey
	 */
	private function addLog($message, $messageKey)
	{
		$user = Factory::getUser();

		if (!array_key_exists('userid', $message))
		{
			$message['userid'] = $user->id;
		}

		if (!array_key_exists('username', $message))
		{
			$message['username'] = $user->username;
		}

		if (!array_key_exists('accountlink', $message))
		{
			$message['accountlink'] = 'index.php?option=com_users&task=user.edit&id=' . $user->id;
		}

		try
		{
			if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
			{
				/** @var \Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel $model */
				$model = $this->app->bootComponent('com_actionlogs')
					->getMVCFactory()->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);
			}
			else
			{
				// Require action log library
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

				/** @var \ActionlogsModelActionlog $model * */
				$model = \JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
			}

			$model->addLog([$message], $messageKey, 'com_eventbooking', $user->id);
		}
		catch (\Exception $e)
		{
			// Ignore any error
		}
	}
}
