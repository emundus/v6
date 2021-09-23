<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Class EventbookingModelCommonEvent
 *
 * @property EventbookingTableEvent $data
 */
class EventbookingModelCommonEvent extends RADModelAdmin
{
	public function __construct($config = [])
	{
		$this->triggerEvents = true;

		parent::__construct($config);
	}

	/**
	 * Init event data
	 */
	public function initData()
	{
		parent::initData();

		$db       = $this->getDbo();
		$config   = EventbookingHelper::getConfig();
		$nullDate = $db->getNullDate();

		$this->data->event_date                     = $nullDate;
		$this->data->event_end_date                 = $nullDate;
		$this->data->registration_start_date        = $nullDate;
		$this->data->late_fee_date                  = $nullDate;
		$this->data->cut_off_date                   = $nullDate;
		$this->data->recurring_end_date             = $nullDate;
		$this->data->cancel_before_date             = $nullDate;
		$this->data->early_bird_discount_date       = $nullDate;
		$this->data->registration_type              = $config->get('registration_type', 0);
		$this->data->access                         = $config->get('access', 1);
		$this->data->registration_access            = $config->get('registration_access', 1);
		$this->data->article_id                     = $config->article_id;
		$this->data->ordering                       = 0;
		$this->data->published                      = $config->get('default_event_status', 0);
		$this->data->enable_terms_and_conditions    = 2;
		$this->data->send_emails                    = -1;
		$this->data->activate_tickets_pdf           = $config->get('activate_tickets_pdf', 0);
		$this->data->send_tickets_via_email         = $config->get('send_tickets_via_email', 0);
		$this->data->enable_cancel_registration     = $config->get('default_enable_cancel_registration', 0);
		$this->data->free_event_registration_status = $config->get('default_free_event_registration_status', 1);
		$this->data->recurring_type                 = 0;
		$this->data->recurring_frequency            = 1;
		$this->data->number_days                    = '';
		$this->data->number_weeks                   = '';
		$this->data->number_months                  = '';
		$this->data->activate_waiting_list          = 2;
		$this->data->hidden                         = 0;
	}

	/**
	 * Load event date from database
	 *
	 * @see RADModelAdmin::loadData()
	 */
	public function loadData()
	{
		parent::loadData();

		$config = EventbookingHelper::getConfig();

		if ($config->activate_recurring_event)
		{
			if ($this->data->recurring_type == 1)
			{
				$this->data->number_days          = $this->data->recurring_frequency;
				$this->data->number_weeks         = 0;
				$this->data->number_months        = 0;
				$this->data->weekly_number_months = 0;
			}
			elseif ($this->data->recurring_type == 2)
			{
				$this->data->number_days          = 0;
				$this->data->number_weeks         = $this->data->recurring_frequency;
				$this->data->number_months        = 0;
				$this->data->weekly_number_months = 0;
			}
			elseif ($this->data->recurring_type == 3)
			{
				$this->data->number_days          = 0;
				$this->data->number_weeks         = 0;
				$this->data->number_months        = $this->data->recurring_frequency;
				$this->data->weekly_number_months = 0;
			}
			elseif ($this->data->recurring_type == 4)
			{
				$this->data->number_days          = 0;
				$this->data->number_weeks         = 0;
				$this->data->number_months        = 0;
				$this->data->weekly_number_months = $this->data->recurring_frequency;
			}
		}

		if ($this->data->recurring_type)
		{
			if ($this->data->number_days == 0)
			{
				$this->data->number_days = '';
			}

			if ($this->data->number_weeks == 0)
			{
				$this->data->number_weeks = '';
			}

			if ($this->data->number_months == 0)
			{
				$this->data->number_months = '';
			}

			if ($this->data->recurring_occurrencies == 0)
			{
				$this->data->recurring_occurrencies = '';
			}
		}
	}

	/**
	 * Method to store an event
	 *
	 * @param   RADInput  $input
	 * @param   array     $ignore
	 *
	 * @return void
	 * @throws Exception
	 *
	 */
	public function store($input, $ignore = [])
	{
		$config = EventbookingHelper::getConfig();

		/* @var EventbookingTableEvent $row */
		$row       = $this->getTable();
		$published = true;
		$isNew     = true;

		if ($this->state->id)
		{
			$isNew = false;
			$row->load($this->state->id);
			$published = $row->published;
		}
		else
		{
			$row->created_language = Factory::getLanguage()->getTag();
		}

		$this->beforeStore($row, $input, $isNew);

		// Get filtered data back to
		$data = $input->getData(RAD_INPUT_ALLOWRAW);

		if (isset($data['recurring_type']) && $data['recurring_type'])
		{
			// Recurring event
			$this->storeRecurringEvent($data, $input->getInt('source_id'), $input, $row);
			$input->set('id', $data['id']);

			return;
		}

		$row->bind($data, ['category_id', 'params']);

		$row->event_date              .= ' ' . $data['event_date_hour'] . ':' . $data['event_date_minute'] . ':00';
		$row->event_end_date          .= ' ' . $data['event_end_date_hour'] . ':' . $data['event_end_date_minute'] . ':00';
		$row->registration_start_date .= ' ' . $data['registration_start_hour'] . ':' . $data['registration_start_minute'] . ':00';
		$row->cut_off_date            .= ' ' . $data['cut_off_hour'] . ':' . $data['cut_off_minute'] . ':00';

		if ($config->event_custom_field && isset($data['params']) && is_array($data['params']))
		{
			$row->custom_fields = json_encode($data['params']);
		}

		$this->prepareTable($row, $input->getCmd('task'), $input->getInt('source_id'));

		$row->store();

		$input->set('id', $row->id);

		$this->afterStore($row, $input, $isNew);

		//Trigger event which allows plugins to save it own data
		$app = Factory::getApplication();
		$app->triggerEvent('onAfterSaveEvent', [$row, $data, $isNew]);

		if ($isNew && $app->isClient('site'))
		{
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendNewEventNotificationEmail', [$row, $config]);
		}

		if (!$isNew && $row->parent_id > 0)
		{
			EventbookingHelper::updateParentMaxEventDate($row->parent_id);
		}

		if (!$isNew && !$published && $row->published && $row->created_by)
		{
			// Event status change from Unpublished to Published, send event approved email if event is created from frontend
			$eventCreator = JUser::getInstance($row->created_by);

			if (MailHelper::isEmailAddress($eventCreator->email) && !$eventCreator->authorise('core.admin'))
			{
				EventbookingHelper::callOverridableHelperMethod('Mail', 'sendEventApprovedEmail', [$row, $config, $eventCreator]);
			}
		}

		if (!$isNew && $app->isClient('site'))
		{
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendEventUpdateEmail', [$row, $config]);
		}
	}

	/**
	 * Store the event in case recurring feature activated
	 *
	 * @param   array                   $data
	 * @param   int                     $sourceId
	 * @param   RADInput                $input
	 * @param   EventbookingTableEvent  $row
	 *
	 * @throws Exception
	 */
	protected function storeRecurringEvent(&$data, $sourceId, $input = null, $row = null)
	{
		// Initialize data if not sent, make it backward compatible with override from older versions
		if ($input === null)
		{
			$input = new RADInput();
		}

		if ($row == null)
		{
			/* @var EventbookingTableEvent $row */
			$row = $this->getTable();

			if ($this->state->id)
			{
				$row->load($this->state->id);
			}
		}

		$db       = $this->getDbo();
		$query    = $db->getQuery(true);
		$nullDate = $db->getNullDate();
		$config   = EventbookingHelper::getConfig();

		if ($row->id)
		{
			$isNew = false;
		}
		else
		{
			$isNew = true;
		}

		$task = isset($data['task']) ? $data['task'] : '';

		$row->bind($data, ['category_id', 'params']);

		$row->event_type              = 1;
		$row->event_date              .= ' ' . $data['event_date_hour'] . ':' . $data['event_date_minute'] . ':00';
		$row->event_end_date          .= ' ' . $data['event_end_date_hour'] . ':' . $data['event_end_date_minute'] . ':00';
		$row->registration_start_date .= ' ' . $data['registration_start_hour'] . ':' . $data['registration_start_minute'] . ':00';
		$row->cut_off_date            .= ' ' . $data['cut_off_hour'] . ':' . $data['cut_off_minute'] . ':00';

		$row->weekdays = implode(',', $data['weekdays']);

		//Adjust event start date and event end date
		if ($data['recurring_type'] == 1)
		{
			$eventDates = EventbookingHelper::getDailyRecurringEventDates($row->event_date, $data['recurring_end_date'], (int) $row->recurring_frequency,
				(int) $data['recurring_occurrencies']);
		}
		elseif ($data['recurring_type'] == 2)
		{
			$eventDates = EventbookingHelper::getWeeklyRecurringEventDates($row->event_date, $data['recurring_end_date'], (int) $row->recurring_frequency,
				(int) $data['recurring_occurrencies'], $data['weekdays']);
		}
		elseif ($data['recurring_type'] == 3)
		{
			//Monthly recurring
			$eventDates = EventbookingHelper::getMonthlyRecurringEventDates($row->event_date, $data['recurring_end_date'],
				(int) $row->recurring_frequency, (int) $data['recurring_occurrencies'], $data['monthdays']);
		}
		else
		{
			// Monthly recurring at a specific date in the week
			$eventDates = EventbookingHelper::getMonthlyRecurringAtDayInWeekEventDates($row->event_date, $data['recurring_end_date'], (int) $row->recurring_frequency, (int) $data['recurring_occurrencies'], $data['week_in_month'], $data['day_of_week']);

			$params = new Registry($row->params);
			$params->set('week_in_month', $data['week_in_month']);
			$params->set('day_of_week', $data['day_of_week']);

			$row->params = $params->toString();
		}

		if (strlen(trim($data['event_end_date'])) && $row->event_end_date != $nullDate)
		{
			$eventDuration = abs(strtotime($row->event_end_date) - strtotime($row->event_date));
		}
		else
		{
			$eventDuration = 0;
		}

		if (strlen(trim($data['cut_off_date'])) && $row->cut_off_date != $nullDate)
		{
			$cutOffDuration = strtotime($row->cut_off_date) - strtotime($row->event_date);
		}
		else
		{
			$cutOffDuration = 0;
		}

		if (strlen(trim($row->cancel_before_date)) && $row->cancel_before_date != $nullDate)
		{
			$cancelDuration = abs(strtotime($row->cancel_before_date) - strtotime($row->event_date));
		}
		else
		{
			$cancelDuration = 0;
		}

		if (strlen(trim($row->early_bird_discount_date)) && $row->early_bird_discount_date != $nullDate)
		{
			$earlyBirdDuration = abs(strtotime($row->early_bird_discount_date) - strtotime($row->event_date));
		}
		else
		{
			$earlyBirdDuration = 0;
		}

		if (strlen(trim($data['registration_start_date'])) && $row->registration_start_date != $nullDate)
		{
			$registrationStartDuration = abs(strtotime($row->registration_start_date) - strtotime($row->event_date));
		}
		else
		{
			$registrationStartDuration = 0;
		}

		if (count($eventDates) == 0)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('Invalid recurring setting'), 'error');
			$app->redirect('index.php?option=com_eventbooking&view=events');
		}
		else
		{
			$row->event_date = $eventDates[0];

			if ($eventDuration)
			{
				$row->event_end_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($row->event_date) + $eventDuration);
			}
			else
			{
				$row->event_end_date = '';
			}

		}

		if ($config->event_custom_field && isset($data['params']) && is_array($data['params']))
		{
			$row->custom_fields = json_encode($data['params']);
		}

		$this->prepareTable($row, $task, $sourceId);

		$row->store();

		$data['id'] = $row->id;

		$this->afterStore($row, $input, $isNew);

		if ($isNew)
		{
			array_shift($eventDates);
			$this->addNewChildrenEvents($row, $eventDates, $task, $eventDuration, $cutOffDuration, $cancelDuration, $earlyBirdDuration, $registrationStartDuration);
		}
		elseif (isset($data['update_children_event']))
		{
			list($eventDatesDate, $deleteEventIds) = $this->updateChildrenEvents($row, $eventDates, $eventDuration, $cutOffDuration, $cancelDuration, $earlyBirdDuration, $registrationStartDuration);

			// Add new children events,
			if (count($eventDatesDate))
			{
				$eventDatesDate = array_values($eventDatesDate);

				for ($i = 0, $n = count($eventDatesDate); $i < $n; $i++)
				{
					$eventDatesDate[$i] .= ' ' . HTMLHelper::_('date', $row->event_date, 'H:i:s', null);
				}

				$this->addNewChildrenEvents($row, $eventDatesDate, $task, $eventDuration, $cutOffDuration, $cancelDuration, $earlyBirdDuration, $registrationStartDuration);
			}

			if (count($deleteEventIds))
			{
				foreach ($deleteEventIds as $i => $deleteEventId)
				{
					// Check to see if this event has registrants, if it doesn't have registrants, it is safe to delete
					$query->clear()
						->select('COUNT(*)')
						->from('#__eb_registrants')
						->where('event_id = ' . $deleteEventId)
						->where('(published >= 1 OR payment_method LIKE "os_offline%")');
					$db->setQuery($query);
					$total = $db->loadResult();

					if ($total)
					{
						unset($deleteEventIds[$i]);
					}
				}

				if (count($deleteEventIds))
				{
					$this->delete($deleteEventIds);
				}
			}
		}

		//Trigger event which allows plugins to save it own data
		$app = Factory::getApplication();
		$app->triggerEvent('onAfterSaveEvent', [$row, $data, $isNew]);

		if ($isNew && $app->isClient('site'))
		{
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendNewEventNotificationEmail', [$row, $config]);
		}

		EventbookingHelper::updateParentMaxEventDate($row->id);
	}

	/**
	 * Method to remove events
	 *
	 * @param   array  $cid  Array contains IDs of the events which you want to delete
	 *
	 * @return    boolean    True on success
	 */
	public function delete($cid = [])
	{
		if (!count($cid))
		{
			return true;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('parent_id')
			->from('#__eb_events')
			->where('id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$parentIds = array_filter($db->loadColumn());

		$query->clear()
			->select('id')
			->from('#__eb_events')
			->where('parent_id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$cid  = array_merge($cid, $db->loadColumn());
		$cids = implode(',', $cid);

		//Delete price setting for events
		$query->clear()
			->delete('#__eb_event_group_prices')->where('event_id IN (' . $cids . ')');
		$db->setQuery($query)
			->execute();

		//Delete categories for the event
		$query->clear()
			->delete('#__eb_event_categories')->where('event_id IN (' . $cids . ')');
		$db->setQuery($query)
			->execute();

		// Delete ticket types related to events
		$query->clear()
			->delete('#__eb_ticket_types')
			->where('event_id IN (' . $cids . ')');
		$db->setQuery($query)
			->execute();

		// Delete the URLs related to event
		$query->clear()
			->delete('#__eb_urls')
			->where($db->quoteName('view') . '=' . $db->quote('event'))
			->where('record_id IN (' . $cids . ')');
		$db->setQuery($query)
			->execute();

		//Delete events themself
		$query->clear()
			->delete('#__eb_events')->where('id IN (' . $cids . ')');
		$db->setQuery($query)
			->execute();

		// Update Max end date of parent events
		foreach ($parentIds as $parentId)
		{
			EventbookingHelper::updateParentMaxEventDate($parentId);
		}

		Factory::getApplication()->triggerEvent('onEventsAfterDelete', [$this->context, $cid]);

		return true;
	}

	/**
	 * Get group registration rates for the event
	 *
	 * @return array|mixed
	 */
	public function getPrices()
	{
		$prices = [];

		if ($this->state->id)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__eb_event_group_prices')
				->where('event_id = ' . $this->state->id)
				->order('id');
			$db->setQuery($query);
			$prices = $db->loadObjectList();
		}

		return $prices;
	}

	/**
	 * Prepare data before event data is stored into database
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   RADInput                $input
	 * @param   bool                    $isNew
	 */
	protected function beforeStore($row, $input, $isNew)
	{
		$app    = Factory::getApplication();
		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();

		// Get data and apply filter from request
		if ($app->isClient('administrator') || $user->authorise('core.admin'))
		{
			$data = $input->getData(RAD_INPUT_ALLOWRAW);
		}
		else
		{
			$data = $input->getData();

			// Performing basic filter
			$data['short_description'] = $input->post->get('short_description', '', 'raw');
			$data['description']       = $input->post->get('description', '', 'raw');

			$data['short_description'] = ComponentHelper::filterText($data['short_description']);
			$data['description']       = ComponentHelper::filterText($data['description']);
		}

		// Copy thumb, image, attachment if not set on SaveAsCopy action
		if ($input->getCmd('task') == 'save2copy')
		{
			$this->setDataFromCopiedEvent($input->getInt('source_id'), $data);
		}

		// Hard code event  to all language
		if ($app->isClient('site'))
		{
			$data['language'] = '*';
		}

		// Normalize data
		$this->normalizeData($data);

		// Delete event image if the action is selected
		if (!$isNew && isset($data['del_thumb']) && $row->thumb)
		{
			$this->deleteEventThumb($row);
		}

		// Delete event attachment if the action is selected
		if (!$isNew && isset($data['del_attachment']) && $row->attachment)
		{
			$this->deleteEventAttachment($row);
		}

		// Upload and resize event image
		$this->uploadAndResizeImage($input, $data, $config);

		// Process attachment
		$this->processAttachment($input, $data, $config);

		// Set $data array back to input object
		$input->setData($data);

		parent::beforeStore($row, $input, $isNew);
	}

	/**
	 * Copy event data from copied event in case data is not set
	 *
	 * @param   int    $sourceEventId
	 * @param   array  $data
	 */
	protected function setDataFromCopiedEvent($sourceEventId, &$data)
	{
		/* @var EventbookingTableEvent $row */
		$row = $this->getTable();

		if ($row->load($sourceEventId))
		{
			if (empty($data['attachment']))
			{
				$data['attachment'] = $row->attachment;
			}

			if (empty($data['thumb']))
			{
				$data['thumb'] = $row->thumb;
			}

			if (empty($data['image']))
			{
				$data['image'] = $row->image;
			}
		}
	}

	/**
	 * Normalize data to meet the requested format of the event
	 *
	 * @param   array  $data
	 */
	protected function normalizeData(&$data)
	{
		// Backward compatible handle in case someone customized recurring settings form before
		if (isset($data['recurring_type']) && !isset($data['recurring_frequency']))
		{
			switch ($data['recurring_type'])
			{
				case 1:
					$data['recurring_type'] = $data['number_days'];
					break;
				case 2:
					$data['recurring_type'] = $data['number_weeks'];
					break;
				case 3:
					$data['recurring_type'] = $data['number_months'];
					break;
				case 4:
					$data['recurring_type'] = $data['weekly_number_months'];
					break;
			}
		}

		if (!isset($data['language']))
		{
			$data['language'] = '*';
		}

		// Backward compatible, in case someone overrides event model before
		if (isset($data['recurring_frequency']))
		{
			$data['number_days'] = $data['number_weeks'] = $data['number_months'] = $data['weekly_number_months'] = $data['recurring_frequency'];
		}


		if (!isset($data['weekdays']))
		{
			$data['weekdays'] = [];
		}

		if (!isset($data['monthdays']))
		{
			$data['monthdays'] = '';
		}

		if (empty($data['number_days']))
		{
			$data['number_days'] = 1;
		}

		if (empty($data['number_weeks']))
		{
			$data['number_week'] = 1;
		}

		if (!isset($data['recurring_occurrencies']))
		{
			$data['recurring_occurrencies'] = 0;
		}

		if (empty($data['recurring_end_date']))
		{
			$data['recurring_end_date'] = $this->getDbo()->getNullDate();
		}

		if (empty($data['weekly_number_months']))
		{
			$data['weekly_number_months'] = 1;
		}

		if (isset($data['payment_methods']))
		{
			if ($data['payment_methods'][0] === '')
			{
				$data['payment_methods'] = '';
			}
			else
			{
				$data['payment_methods'] = implode(',', array_filter(ArrayHelper::toInteger($data['payment_methods'])));
			}
		}
		else
		{
			$data['payment_methods'] = '';
		}

		if (empty($data['event_date_hour']))
		{
			$data['event_date_hour'] = '00';
		}

		if (empty($data['event_date_minute']))
		{
			$data['event_date_minute'] = '00';
		}

		if (empty($data['cut_off_hour']))
		{
			$data['cut_off_hour'] = '00';
		}

		if (empty($data['cut_off_minute']))
		{
			$data['cut_off_minute'] = '00';
		}

		if (!empty($data['enable_cancel_registration']) && empty($data['cancel_before_date']))
		{
			$data['cancel_before_date'] = $data['event_date'] . ' ' . $data['event_date_hour'] . ':' . $data['event_date_minute'] . ':00';
		}

		// Reminder backward compatible handle
		if (!empty($data['send_first_reminder']))
		{
			$data['enable_auto_reminder'] = 1;
			$data['remind_before_x_days'] = $data['send_first_reminder'];
		}

		if (!isset($data['send_first_reminder']) && !empty($data['remind_before_x_days']))
		{
			$data['send_first_reminder'] = $data['remind_before_x_days'];
		}

		if (isset($data['send_first_reminder'], $data['send_first_reminder_time']))
		{
			$data['send_first_reminder'] = $data['send_first_reminder'] * $data['send_first_reminder_time'];
		}

		if (isset($data['send_second_reminder'], $data['send_second_reminder_time']))
		{
			$data['send_second_reminder'] = $data['send_second_reminder'] * $data['send_second_reminder_time'];
		}

		if (isset($data['paypal_email']))
		{
			$data['paypal_email'] = trim($data['paypal_email']);
		}

		if (Factory::getApplication()->isClient('administrator') || !empty($data['discount_groups_enabled']))
		{
			if (isset($data['discount_groups']))
			{
				$data['discount_groups'] = implode(',', $data['discount_groups']);
			}
			else
			{
				$data['discount_groups'] = '';
			}
		}

		// Convert date/time values to the format accepted by database
		$config                 = EventbookingHelper::getConfig();
		$dateFormat             = str_replace('%', '', $config->get('date_field_format', '%Y-%m-%d'));
		$dateTimeFormat         = $dateFormat . ' H:i';
		$dateTimeFormatFallback = $dateTimeFormat . ':s';

		$dateFields = [
			'event_date',
			'event_end_date',
			'registration_start_date',
			'cut_off_date',
			'recurring_end_date',
		];

		foreach ($dateFields as $field)
		{
			if (empty($data[$field]))
			{
				continue;
			}

			$date = DateTime::createFromFormat($dateFormat, $data[$field]);

			if ($date !== false)
			{
				$data[$field] = $date->format('Y-m-d');
			}
		}

		$dateTimeFields = [
			'publish_up',
			'publish_down',
			'cancel_before_date',
			'early_bird_discount_date',
			'late_fee_date',
			'registrant_edit_close_date',
		];

		foreach ($dateTimeFields as $field)
		{
			if (empty($data[$field]))
			{
				continue;
			}

			$date = DateTime::createFromFormat($dateTimeFormat, $data[$field]);

			// BC compatible, in case layout was overridden and second is also posted in time
			if ($date === false)
			{
				$date = DateTime::createFromFormat($dateTimeFormatFallback, $data[$field]);
			}

			if ($date !== false)
			{
				$data[$field] = $date->format('Y-m-d H:i:s');
			}
		}

		if (!$this->state->id)
		{
			// Generate data from config if not set from add/edit event form
			$defaultValuesMapping = [
				'published'                      => 'default_event_status',
				'registration_type'              => 'registration_type',
				'access'                         => 'access',
				'registration_access'            => 'registration_access',
				'enable_cancel_registration'     => 'default_enable_cancel_registration',
				'free_event_registration_status' => 'default_free_event_registration_status',
			];

			foreach ($defaultValuesMapping as $eventField => $configKey)
			{
				if (!isset($data[$eventField]) && isset($config->{$configKey}))
				{
					$data[$eventField] = $config->{$configKey};
				}
			}

			// Default data for some fields which could be hidden because of configuration
			$defaultFieldValues = [
				'activate_waiting_list'       => 2,
				'enable_terms_and_conditions' => 2,
			];

			foreach ($defaultFieldValues as $fieldName => $defaultValue)
			{
				if (!array_key_exists($fieldName, $data))
				{
					$data[$fieldName] = $defaultValue;
				}
			}
		}

		if (!empty($data['currency_code']) && empty($data['currency_symbol']) && $data['currency_code'] != $config->currency_code)
		{
			$data['currency_symbol'] = $data['currency_code'];
		}

		$languages = EventbookingHelper::getLanguages();

		if (Multilanguage::isEnabled() && count($languages))
		{
			$translableFields = [
				'title',
				'alias',
				'page_title',
				'page_heading',
				'meta_keywords',
				'meta_description',
				'short_description',
				'description',
				'registration_form_message',
				'registration_form_message_group',
				'user_email_body',
				'user_email_body_offline',
				'thanks_message',
				'thanks_message_offline',
				'registration_approved_email_body',
			];

			foreach ($languages as $language)
			{
				$sef = $language->sef;

				if (!empty($data['use_data_from_default_language_' . $sef]))
				{
					foreach ($translableFields as $translableField)
					{
						if (empty($data[$translableField . '_' . $sef]))
						{
							$data[$translableField . '_' . $sef] = $data[$translableField];
						}
					}
				}
			}
		}
	}

	/**
	 * This is a direct copy of normalizeData for backward compatible purpose
	 *
	 * @param   array  $data
	 */
	protected function sanitizeData(&$data)
	{
		$this->normalizeData($data);
	}

	/**
	 * Delete thumbnail of existing event
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return void
	 */
	protected function deleteEventThumb($row)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__eb_events')
			->where('id != ' . $row->id)
			->where('thumb = ' . $db->quote($row->thumb));
		$db->setQuery($query);
		$total = $db->loadResult();

		if (!$total)
		{
			if (File::exists(JPATH_ROOT . '/media/com_eventbooking/images/' . $row->thumb))
			{
				File::delete(JPATH_ROOT . '/media/com_eventbooking/images/' . $row->thumb);
			}

			if (File::exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $row->thumb))
			{
				File::delete(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $row->thumb);
			}
		}

		$row->thumb = '';
		$row->image = '';
	}

	/**
	 * Method to delete attachment of the event
	 *
	 * @param   EventbookingTableEvent  $row
	 */
	protected function deleteEventAttachment($row)
	{
		$config = EventbookingHelper::getConfig();
		$db     = $this->getDbo();
		$query  = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__eb_events')
			->where('id != ' . $row->id)
			->where('attachment = ' . $db->quote($row->attachment));
		$db->setQuery($query);
		$total = $db->loadResult();

		$attachmentsPath = JPATH_ROOT . '/' . ($config->attachments_path ?: 'media/com_eventbooking') . '/';

		if (!$total && File::exists($attachmentsPath . $row->attachment))
		{
			File::delete($attachmentsPath . $row->attachment);
		}

		$row->attachment = '';
	}

	/**
	 * Upload and resize image for event
	 *
	 * @param   RADInput   $input
	 * @param   array      $data
	 * @param   RADConfig  $config
	 */
	protected function uploadAndResizeImage($input, &$data, $config)
	{
		$thumbImage = $input->files->get('thumb_image');

		if ($thumbImage['name'])
		{
			// Image uploaded for event in frontend, upload and resize
			$this->processUploadImage($thumbImage, $data);
		}

		if (Factory::getApplication()->isClient('administrator'))
		{
			// Image is selected for event from backend, just resize
			$this->processSelectImage($data);
		}
	}

	/**
	 * Upload attachment and store attachment filename into data array
	 *
	 * @param   RADInput   $input
	 * @param   array      $data
	 * @param   RADConfig  $config
	 */
	protected function processAttachment($input, &$data, $config)
	{
		$app = Factory::getApplication();

		//Process attachment
		if ($app->isClient('site'))
		{
			$attachment = $input->files->get('attachment');
		}
		else
		{
			$attachment = $input->files->get('attachment', null, 'raw');
		}

		if ($attachment['name'])
		{
			$pathUpload        = JPATH_ROOT . '/' . ($config->attachments_path ?: 'media/com_eventbooking');
			$allowedExtensions = $config->attachment_file_types;

			if (!$allowedExtensions)
			{
				$allowedExtensions = 'doc|docx|ppt|pptx|pdf|zip|rar|bmp|gif|jpg|jepg|png|swf|zipx';
			}

			$allowedExtensions = explode('|', $allowedExtensions);
			$allowedExtensions = array_map('trim', $allowedExtensions);
			$allowedExtensions = array_map('strtolower', $allowedExtensions);
			$fileName          = $attachment['name'];
			$fileExt           = File::getExt($fileName);

			if (in_array(strtolower($fileExt), $allowedExtensions))
			{
				$fileName = File::makeSafe($fileName);

				if ($app->isClient('administrator'))
				{
					File::upload($attachment['tmp_name'], $pathUpload . '/' . $fileName, false, true);
				}
				else
				{
					File::upload($attachment['tmp_name'], $pathUpload . '/' . $fileName);
				}

				$data['attachment'] = $fileName;
			}
			else
			{
				// Throw notice, but still allow saving the event
				$data['attachment'] = '';
			}
		}

		if (empty($data['attachment']) && !empty($data['available_attachment']))
		{
			$data['attachment'] = $data['available_attachment'];

			if (is_array($data['attachment']))
			{
				$data['attachment'] = implode('|', $data['attachment']);
			}
		}
	}

	/**
	 * Process after event is stored into database
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   RADInput                $input
	 * @param   bool                    $isNew
	 */
	protected function afterStore($row, $input, $isNew)
	{
		parent::afterStore($row, $input, $isNew);

		$data = $input->getData();
		$this->storeEventCategories($row->id, $data, $isNew);
		$this->storeEventGroupRegistrationRates($row->id, $data, $isNew);

		if ($input->getCmd('task') == 'save2copy')
		{
			$sourceEventId = $input->getInt('source_id');

			$db  = $this->getDbo();
			$sql = "INSERT INTO #__eb_field_events(field_id, event_id) SELECT field_id, $row->id FROM #__eb_field_events WHERE event_id = $sourceEventId";
			$db->setQuery($sql);
			$db->execute();
		}
	}

	/**
	 * Store categories of an event
	 *
	 * @param $eventId
	 * @param $data
	 * @param $isNew
	 */
	protected function storeEventCategories($eventId, $data, $isNew)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		if (!$isNew)
		{
			$query->delete('#__eb_event_categories')
				->where('event_id=' . $eventId);
			$db->setQuery($query);
			$db->execute();
		}

		$mainCategoryId = (int) $data['main_category_id'];

		if ($mainCategoryId)
		{
			$query->clear()
				->insert('#__eb_event_categories')
				->columns('event_id, category_id, main_category')
				->values("$eventId, $mainCategoryId, 1");
			$db->setQuery($query);
			$db->execute();
		}

		$categories = isset($data['category_id']) ? $data['category_id'] : [];
		$categories = array_filter(ArrayHelper::toInteger($categories, 0));

		$execute = false;

		$query->clear()
			->insert('#__eb_event_categories')
			->columns('event_id, category_id, main_category');

		for ($i = 0, $n = count($categories); $i < $n; $i++)
		{
			$categoryId = (int) $categories[$i];

			if ($categoryId == $mainCategoryId)
			{
				continue;
			}

			$execute = true;

			$query->values("$eventId, $categoryId, 0");
		}

		if ($execute)
		{
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Store group registration rates of an event
	 *
	 * @param $eventId
	 * @param $data
	 * @param $isNew
	 */
	protected function storeEventGroupRegistrationRates($eventId, $data, $isNew)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		if (!$isNew)
		{
			$query->delete('#__eb_event_group_prices')
				->where('event_id = ' . $eventId);
			$db->setQuery($query);
			$db->execute();
		}

		if (!isset($data['price']) || !isset($data['registrant_number']))
		{
			return;
		}

		$execute           = false;
		$prices            = $data['price'];
		$registrantNumbers = $data['registrant_number'];

		$query->clear()
			->insert('#__eb_event_group_prices')
			->columns('event_id, registrant_number, price');

		for ($i = 0, $n = count($prices); $i < $n; $i++)
		{
			$price            = $prices[$i];
			$registrantNumber = $registrantNumbers[$i];

			if ($registrantNumber > 0 && $price > 0)
			{
				$execute = true;
				$query->values("$eventId, $registrantNumber, $price");
			}
		}

		if ($execute)
		{
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Method to store children events of a recurring event
	 *
	 * @param $row
	 * @param $eventDates
	 * @param $task
	 * @param $eventDuration
	 * @param $cutOffDuration
	 * @param $cancelDuration
	 * @param $earlyBirdDuration
	 * @param $registrationStartDuration
	 */
	protected function addNewChildrenEvents($row, $eventDates, $task, $eventDuration, $cutOffDuration, $cancelDuration, $earlyBirdDuration, $registrationStartDuration)
	{
		$db             = $this->getDbo();
		$config         = EventbookingHelper::getConfig();
		$languages      = EventbookingHelper::getLanguages();
		$nullDate       = $db->getNullDate();
		$isMultilingual = Multilanguage::isEnabled() && count($languages);

		reset($eventDates);

		foreach ($eventDates as $eventDate)
		{
			/* @var EventbookingTableEvent $rowChildEvent */
			$rowChildEvent             = clone $row;
			$rowChildEvent->id         = 0;
			$rowChildEvent->event_date = $eventDate;

			if ($eventDuration)
			{
				$rowChildEvent->event_end_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($eventDate) + $eventDuration);
			}
			else
			{
				$rowChildEvent->event_end_date = '';
			}

			if ($cutOffDuration != 0)
			{
				$rowChildEvent->cut_off_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) + $cutOffDuration);
			}

			if ($cancelDuration)
			{
				$rowChildEvent->cancel_before_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) - $cancelDuration);
			}

			if ($earlyBirdDuration)
			{
				$rowChildEvent->early_bird_discount_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) - $earlyBirdDuration);
			}

			if ($registrationStartDuration)
			{
				$rowChildEvent->registration_start_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) - $registrationStartDuration);
			}

			$rowChildEvent->event_type             = 2;
			$rowChildEvent->parent_id              = $row->id;
			$rowChildEvent->recurring_type         = 0;
			$rowChildEvent->recurring_frequency    = 0;
			$rowChildEvent->weekdays               = '';
			$rowChildEvent->monthdays              = '';
			$rowChildEvent->recurring_end_date     = $nullDate;
			$rowChildEvent->recurring_occurrencies = 0;
			$rowChildEvent->alias                  = $rowChildEvent->title . '-' . HTMLHelper::_('date', $rowChildEvent->event_date, $config->date_format, null);

			if ($isMultilingual)
			{
				// Build alias alias for other languages
				foreach ($languages as $language)
				{
					$sef                              = $language->sef;
					$rowChildEvent->{'alias_' . $sef} = ApplicationHelper::stringURLSafe($rowChildEvent->{'title_' . $sef} . '-' . HTMLHelper::_('date', $rowChildEvent->event_date, $config->date_format, null));
				}
			}

			$this->prepareTable($rowChildEvent, $task);

			$rowChildEvent->store();

			$this->setRelationDataFromParentEvent($row, $rowChildEvent, true);
		}
	}

	/**
	 * Method to update children events with information from parent event
	 *
	 * @param $row
	 * @param $eventDates
	 * @param $data
	 * @param $eventDuration
	 * @param $cutOffDuration
	 * @param $cancelDuration
	 * @param $earlyBirdDuration
	 * @param $registrationStartDuration
	 *
	 * @return array
	 */
	protected function updateChildrenEvents($row, $eventDates, $eventDuration, $cutOffDuration, $cancelDuration, $earlyBirdDuration, $registrationStartDuration)
	{
		$languages      = EventbookingHelper::getLanguages();
		$db             = $this->getDbo();
		$query          = $db->getQuery(true);
		$isMultilingual = Multilanguage::isEnabled() && count($languages);
		$deleteEventIds = [];
		$eventDatesDate = [];

		foreach ($eventDates as $eventDate)
		{
			$eventDatesDate[] = HTMLHelper::_('date', $eventDate, 'Y-m-d', null);
		}

		// The parent event
		$childEventDate = HTMLHelper::_('date', $row->event_date, 'Y-m-d', null);
		$index          = array_search($childEventDate, $eventDatesDate);

		if ($index !== false)
		{
			unset($eventDatesDate[$index]);
		}

		$query->select('id')
			->from('#__eb_events')
			->where('parent_id = ' . $row->id)
			->order('event_date');
		$db->setQuery($query);
		$children = $db->loadColumn();

		if (count($children))
		{
			$fieldsToUpdate = [
				'created_by',
				'category_id',
				'main_category_id',
				'thumb',
				'image',
				'location_id',
				'tax_rate',
				'registration_type',
				'title',
				'short_description',
				'description',
				'access',
				'registration_access',
				'individual_price',
				'price_text',
				'event_capacity',
				'registration_type',
				'max_group_number',
				'discount_type',
				'discount',
				'discount_groups',
				'discount_amounts',
				'enable_cancel_registration',
				'early_bird_discount_amount',
				'early_bird_discount_type',
				'deposit_type',
				'deposit_amount',
				'paypal_email',
				'notification_emails',
				'registration_form_message',
				'registration_form_message_group',
				'admin_email_body',
				'user_email_body',
				'user_email_body_offline',
				'group_member_email_body',
				'thanks_message',
				'thanks_message_offline',
				'registration_approved_email_body',
				'reminder_email_body',
				'second_reminder_email_body',
				'params',
				'currency_code',
				'currency_symbol',
				'custom_field_ids',
				'custom_fields',
				'published',
				'send_first_reminder',
				'first_reminder_frequency',
				'send_second_reminder',
				'second_reminder_frequency',
				'payment_methods',
				'published',
				'members_discount_apply_for',
				'enable_coupon',
				'activate_waiting_list',
				'collect_member_information',
				'prevent_duplicate_registration',
			];

			// Translatable fields
			if ($isMultilingual)
			{
				$translatableFields = [
					'title',
					'page_title',
					'page_heading',
					'meta_keywords',
					'meta_description',
					'price_text',
					'registration_handle_url',
					'short_description',
					'description',
					'registration_form_message',
					'registration_form_message_group',
					'user_email_body',
					'user_email_body_offline',
					'thanks_message',
					'thanks_message_offline',
					'registration_approved_email_body',
					'invoice_format',
				];

				foreach ($languages as $language)
				{
					$sef = $language->sef;

					foreach ($translatableFields as $translatableField)
					{
						$fieldsToUpdate[] = $translatableField . '_' . $sef;
					}
				}
			}

			/* @var EventbookingTableEvent $rowChildEvent */
			$rowChildEvent = $this->getTable();

			foreach ($children as $childId)
			{
				$rowChildEvent->load($childId);
				$childEventDate = HTMLHelper::_('date', $rowChildEvent->event_date, 'Y-m-d', null);
				$index          = array_search($childEventDate, $eventDatesDate);

				if ($index !== false)
				{
					unset($eventDatesDate[$index]);
				}
				else
				{
					$deleteEventIds[] = $rowChildEvent->id;
					continue;
				}

				foreach ($fieldsToUpdate as $field)
				{
					$rowChildEvent->$field = $row->$field;
				}

				// Allow children event to update hour and minute secure
				$rowChildEvent->event_date = HTMLHelper::_('date', $rowChildEvent->event_date, 'Y-m-d', null) . ' ' . HTMLHelper::_('date', $row->event_date, 'H:i:s', null);

				if ($eventDuration)
				{
					$rowChildEvent->event_end_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) + $eventDuration);
				}
				else
				{
					$rowChildEvent->event_end_date = '';
				}

				if ($cutOffDuration != 0)
				{
					$rowChildEvent->cut_off_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) + $cutOffDuration);
				}
				else
				{
					$rowChildEvent->cut_off_date = '';
				}

				if ($cancelDuration)
				{
					$rowChildEvent->cancel_before_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) - $cancelDuration);
				}
				else
				{
					$rowChildEvent->cancel_before_date = '';
				}

				if ($earlyBirdDuration)
				{
					$rowChildEvent->early_bird_discount_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) - $earlyBirdDuration);
				}
				else
				{
					$rowChildEvent->early_bird_discount_date = '';
				}

				if ($registrationStartDuration)
				{
					$rowChildEvent->registration_start_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) - $registrationStartDuration);
				}
				else
				{
					$rowChildEvent->registration_start_date = '';
				}

				$rowChildEvent->store();
				$this->setRelationDataFromParentEvent($row, $rowChildEvent, false);
			}
		}

		return [$eventDatesDate, $deleteEventIds];
	}

	/**
	 * Set event related data (categories, group registration rate, custom fields) from parent event to child event
	 *
	 * @param $row
	 * @param $rowChild
	 * @param $isNew
	 */
	protected function setRelationDataFromParentEvent($row, $rowChild, $isNew)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Delete existing data
		if (!$isNew)
		{
			$query->clear()
				->delete('#__eb_event_categories')
				->where('event_id = ' . $rowChild->id);
			$db->setQuery($query)
				->execute();

			$query->clear()
				->delete('#__eb_event_group_prices')
				->where('event_id = ' . $rowChild->id);
			$db->setQuery($query)
				->execute();
		}

		// Insert new data
		$sql = 'INSERT INTO #__eb_event_categories(event_id, category_id, main_category)'
			. " SELECT $rowChild->id, category_id, main_category FROM #__eb_event_categories WHERE event_id = $row->id ORDER BY id";
		$db->setQuery($sql)
			->execute();

		$sql = 'INSERT INTO #__eb_event_group_prices(event_id, registrant_number, price)'
			. " SELECT $rowChild->id, registrant_number, price FROM #__eb_event_group_prices WHERE event_id = $row->id ORDER BY id";
		$db->setQuery($sql)
			->execute();
	}

	/**
	 * Upload and resize the image uploaded for the event on frontend
	 *
	 * @param   array  $thumbImage
	 * @param   array  $data
	 *
	 * @return void
	 */
	protected function processUploadImage($thumbImage, &$data)
	{
		$fileExt        = StringHelper::strtoupper(File::getExt($thumbImage['name']));
		$supportedTypes = ['JPG', 'PNG', 'GIF', 'JPEG'];

		if (!in_array($fileExt, $supportedTypes))
		{
			return;
		}

		if (File::exists(JPATH_ROOT . '/media/com_eventbooking/images/' . StringHelper::strtolower($thumbImage['name'])))
		{
			$fileName = time() . '_' . StringHelper::strtolower($thumbImage['name']);
		}
		else
		{
			$fileName = StringHelper::strtolower($thumbImage['name']);
		}

		// Replace space in filename with underscore
		$fileName = preg_replace('/\s+/', '_', $fileName);

		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();

		if ($user->id && $config->get('store_images_in_user_folder'))
		{
			if (!Folder::exists(JPATH_ROOT . '/images/com_eventbooking/' . $user->username))
			{
				Folder::create(JPATH_ROOT . '/images/com_eventbooking/' . $user->username);
			}

			if (!Folder::exists(JPATH_ROOT . '/media/com_eventbooking/images/' . $user->username))
			{
				Folder::create(JPATH_ROOT . '/media/com_eventbooking/images/' . $user->username);
			}

			if (!Folder::exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $user->username))
			{
				Folder::create(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $user->username);
			}

			$fileName = $user->username . '/' . $fileName;
		}

		$imagePath = JPATH_ROOT . '/images/com_eventbooking/' . $fileName;
		$thumbPath = JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $fileName;
		File::upload($thumbImage['tmp_name'], $imagePath);

		$width  = (int) $config->thumb_width ?: 200;
		$height = (int) $config->thumb_height ?: 200;

		EventbookingHelper::resizeImage($imagePath, $thumbPath, $width, $height);

		// Resize large event image if configured
		if ($config->resize_event_large_image)
		{
			$width  = (int) $config->large_image_width ?: 800;
			$height = (int) $config->large_image_height ?: 600;

			EventbookingHelper::resizeImage($imagePath, $imagePath, $width, $height);
		}

		$data['thumb'] = $fileName;
		$data['image'] = 'images/com_eventbooking/' . $fileName;
	}

	/**
	 * Resize the image which is selected for the event (from administrator area of the site)
	 *
	 * @param   array  $data
	 *
	 * @return void
	 */
	protected function processSelectImage(&$data)
	{
		$config = EventbookingHelper::getConfig();

		if (EventbookingHelper::useStipEasyImage())
		{
			if (!empty($data['images']['image']))
			{
				$data['image'] = $data['images']['image'];
			}
			else
			{
				$data['image'] = '';
			}
		}

		if (empty($data['image']))
		{
			$data['thumb'] = '';

			return;
		}

		if ($config->get('store_images_in_user_folder') && !empty($data['thumb']) && strpos($data['thumb'], '/') !== false)
		{
			$fileName = $data['thumb'];
		}
		else
		{
			$fileName = basename(EventbookingHelperHtml::getCleanImagePath($data['image']));
		}


		$thumbPath   = JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $fileName;
		$width       = (int) $config->thumb_width ?: 200;
		$height      = (int) $config->thumb_height ?: 200;
		$sourceImage = JPATH_ROOT . '/' . EventbookingHelperHtml::getCleanImagePath($data['image']);


		EventbookingHelper::resizeImage($sourceImage, $thumbPath, $width, $height);

		// Resize large event image if configured
		if ($config->resize_event_large_image)
		{
			$width  = (int) $config->large_image_width ?: 800;
			$height = (int) $config->large_image_height ?: 600;

			EventbookingHelper::resizeImage($sourceImage, $sourceImage, $width, $height);
		}

		$data['thumb'] = $fileName;
	}

	/**
	 * Validate to make sure data entered for event is valid before saving
	 *
	 * @param   RADInput  $input
	 *
	 * @return array
	 */
	public function validateFormInput($input)
	{
		$errors = [];

		// Validate recurring data
		$recurringType = $input->post->getInt('recurring_type');

		// This is recurring event, we need to check and make sure recurring ending is setup
		if ($recurringType)
		{
			$recurringOccurrences = $input->getInt('recurring_occurrencies');
			$recurringEndDate     = $input->getString('recurring_end_date');

			if (empty($recurringOccurrences) && empty($recurringEndDate))
			{
				$errors[] = Text::_('EB_ENTER_RECURRING_ENDING_SETTINGS');
			}
		}

		// Validate frontend image size
		if (Factory::getApplication()->isClient('site'))
		{
			$thumbImage = $input->files->get('thumb_image');

			$errors = array_merge($errors, $this->validateImage($thumbImage));
		}


		// Validate and make sure alias is not duplicated
		$config = EventbookingHelper::getConfig();
		$task   = $input->getCmd('task');
		$alias  = $input->getString('alias');
		$id     = $input->getInt('id', 0);

		if ($task != 'save2copy' && strlen($alias) && !$config->get('insert_event_id'))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from('#__eb_events')
				->where('alias = ' . $db->quote($alias));

			if ($id > 0)
			{
				$query->where('id != ' . $id);
			}

			$db->setQuery($query);

			$count = (int) $db->loadResult();

			if ($count > 0)
			{
				$errors[] = Text::sprintf('EB_EVENT_ALIAS_INVALID', $alias);
			}
		}

		return $errors;
	}

	/**
	 * Validate the uploaded image base on settings from configuration
	 *
	 * @param   array  $image
	 *
	 * @return array
	 */
	protected function validateImage($image)
	{
		// If no image is selected/uploaded, return empty array
		if (empty($image['name']))
		{
			return [];
		}

		// Validate width and height
		$imageInfo = @getimagesize($image['tmp_name']);

		if ($imageInfo === false)
		{
			return [Text::_('EB_INVALID_IMAGE_FILE_TYPE')];
		}

		$config = EventbookingHelper::getConfig();
		$errors = [];

		if ($config->image_max_file_size > 0)
		{
			$maxFileSizeInByte = $config->image_max_file_size * 1024 * 1024;

			if ($image['size'] > $maxFileSizeInByte)
			{
				$errors[] = Text::sprintf('EB_IMAGE_FILE_SIZE_TOO_LARGE', $config->image_max_file_size . ' MB');
			}
		}


		if ($config->image_max_width > 0 || $config->image_max_height > 0)
		{
			list($width, $height, $type, $attr) = $imageInfo;

			if ($width > $config->image_max_width)
			{
				$errors[] = Text::sprintf('EB_IMAGE_WIDTH_TOO_LARGE', $config->image_max_width);
			}

			if ($height > $config->image_max_height)
			{
				$errors[] = Text::sprintf('EB_IMAGE_HEIGHT_TOO_LARGE', $config->image_max_height);
			}
		}

		return $errors;
	}

	/**
	 * Override beforePublish to send event approved email to event creator
	 *
	 * @param   array  $cid
	 * @param   int    $state
	 */
	protected function beforePublish($cid, $state)
	{
		parent::beforePublish($cid, $state);

		if ($state == 1)
		{
			$config = EventbookingHelper::getConfig();

			foreach ($cid as $id)
			{
				/* @var EventbookingTableEvent $row */
				$row = $this->getTable();

				if (!$row->load($id))
				{
					continue;
				}

				if (!$row->created_by)
				{
					continue;
				}

				if ($row->published)
				{
					continue;
				}

				// Event status change from Unpublished to Published, send event approved email if event is created from frontend
				$eventCreator = JUser::getInstance($row->created_by);

				if (MailHelper::isEmailAddress($eventCreator->email) && !$eventCreator->authorise('core.admin'))
				{
					EventbookingHelper::callOverridableHelperMethod('Mail', 'sendEventApprovedEmail', [$row, $config, $eventCreator]);
				}
			}
		}
	}

	protected function afterPublish($cid, $state)
	{
		parent::afterPublish($cid, $state);

		// Update max end date of parent event
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('parent_id')
			->from('#__eb_events')
			->where('parent_id > 0')
			->where('id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$parentIds = $db->loadColumn();

		foreach ($parentIds as $parentId)
		{
			EventbookingHelper::updateParentMaxEventDate($parentId);
		}
	}

	/**
	 * Method to cancel an event
	 *
	 * @param   int  $id
	 */
	public function cancel($id)
	{
		$row = $this->getTable();

		if (!$row->load($id))
		{
			throw new Exception(sprintf('Invalid Event ID %d', $id));
		}

		$row->load($id);

		if ($row->published == 2)
		{
			throw new Exception(Text::sprintf('EB_EVENT_WAS_CANCELLED', $id));
		}

		$row->published = 2;
		$row->store();

		$db = $this->getDbo();

		// Update status of registrants to cancelled
		$query = $db->getQuery(true)
			->update('#__eb_registrants')
			->set('published = 2')
			->where('event_id = ' . $id)
			->where('(published= 1 OR payment_method LIKE "os_offline%")');
		$db->setQuery($query)
			->execute();

		// Now, send emails to all registrants of the event
		$query->clear()
			->select('*')
			->from('#__eb_registrants')
			->where('event_id = ' . $id)
			->where('published = 2');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		EventbookingHelper::callOverridableHelperMethod('Mail', 'sendEventCancelEmails', [$rows]);
	}
}
