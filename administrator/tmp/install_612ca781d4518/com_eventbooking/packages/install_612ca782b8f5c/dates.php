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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;

class plgEventBookingDates extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Render setting form
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return array
	 */
	public function onEditEvent($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);

		return ['title' => Text::_('EB_ADDITIONAL_DATES'),
		        'form'  => ob_get_clean(),
		];
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   Boolean                 $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$config     = EventbookingHelper::getConfig();
		$dateFormat = str_replace('%', '', $config->get('date_field_format', '%Y-%m-%d'));
		$dates      = isset($data['dates']) && is_array($data['dates']) ? $data['dates'] : [];

		$additionalEventIds   = [];
		$numberChildrenEvents = 0;

		foreach ($dates as $date)
		{
			if (empty($date['event_date']) || strpos($date['event_date'], '0000') !== false)
			{
				continue;
			}

			// Convert date data to Y-m-d H:i:s format
			$dateFields = [
				'event_date',
				'event_end_date',
				'registration_start_date',
				'cut_off_date',
			];

			foreach ($dateFields as $field)
			{
				if ($date[$field] && strpos($date[$field], '0000') === false)
				{
					$datetime = DateTime::createFromFormat($dateFormat . ' H:i', $date[$field]);

					if ($datetime !== false)
					{
						$date[$field] = $datetime->format('Y-m-d H:i:s');
					}
				}
			}

			$id = isset($date['id']) ? $date['id'] : 0;

			if ($isNew)
			{
				$id = 0;
			}

			if ($id > 0)
			{
				/* @var EventbookingTableEvent $rowEvent */
				$rowEvent = Table::getInstance('Event', 'EventbookingTable');
				$rowEvent->load($id);

				if ($rowEvent->id)
				{
					$query->clear()
						->select('COUNT(*)')
						->from('#__eb_events')
						->where('`alias`  = ' . $db->quote($rowEvent->alias))
						->where('id != ' . $rowEvent->id);
					$db->setQuery($query);
					$total = $db->loadResult();

					if ($total)
					{
						$rowEvent->alias = ApplicationHelper::stringURLSafe($rowEvent->id . '-' . $rowEvent->title . '-' . HTMLHelper::_('date', $rowEvent->event_date, $config->date_format, null));
					}
				}
			}
			else
			{
				$rowEvent     = clone $row;
				$rowEvent->id = 0;
			}

			$rowEvent->bind($date);
			$rowEvent->parent_id          = $row->id;
			$rowEvent->event_type         = 2;
			$rowEvent->is_additional_date = 1;

			if (!$rowEvent->id)
			{
				$rowEvent->alias = ApplicationHelper::stringURLSafe($rowEvent->title . '-' . HTMLHelper::_('date', $rowEvent->event_date, $config->date_format, null));
				$rowEvent->hits  = 0;
			}
            elseif (isset($data['update_data_from_main_event']))
			{
				$fieldsToUpdate = [
					'created_by',
					'category_id',
					'thumb',
					'image',
					'tax_rate',
					'registration_type',
					'title',
					'short_description',
					'description',
					'access',
					'registration_access',
					'individual_price',
					'price_text',
					'registration_type',
					'max_group_number',
					'discount_type',
					'discount',
					'discount_groups',
					'discount_amounts',
					'early_bird_discount_amount',
					'early_bird_discount_type',
					'paypal_email',
					'notification_emails',
					'user_email_body',
					'user_email_body_offline',
					'thanks_message',
					'thanks_message_offline',
					'registration_form_message',
					'registration_form_message_group',
					'reminder_email_body',
					'second_reminder_email_body',
					'registration_approved_email_body',
					'params',
					'currency_code',
					'currency_symbol',
					'custom_field_ids',
					'custom_fields',
					'send_first_reminder',
					'first_reminder_frequency',
					'send_second_reminder',
					'second_reminder_frequency',
					'collect_member_information',
					'payment_methods',
				];

				foreach ($fieldsToUpdate as $field)
				{
					$rowEvent->$field = $row->$field;
				}
			}

			$rowEvent->store();

			$numberChildrenEvents++;

			if ($id == 0)
			{
				$isChildEventNew = true;
			}
			else
			{
				$isChildEventNew = false;
			}

			// Store categories
			$this->storeEventCategories($rowEvent->id, $data, $isChildEventNew);

			// Store price
			$this->storeEventGroupRegistrationRates($rowEvent->id, $data, $isChildEventNew);

			$additionalEventIds[] = $rowEvent->id;
		}


		if ($numberChildrenEvents)
		{
			$row->event_type = 1;
		}
        elseif (!$isNew)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->select('COUNT(*)')
				->from('#__eb_events')
				->where('parent_id = ' . (int) $row->id);
			$db->setQuery($query);
			$total = (int) $db->loadResult();

			if ($total > 0)
			{
				$row->event_type = 1;
			}
			else
			{
				$row->event_type = 0;
			}
		}

		$row->store();

		// Remove the events which are removed by users
		if (!$isNew)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__eb_events')
				->where('parent_id = ' . $row->id)
				->where('is_additional_date = 1');
			$db->setQuery($query);
			$allChildrenEventIds = $db->loadColumn();

			if (count($allChildrenEventIds))
			{
				$deletedEventIds = array_diff($allChildrenEventIds, $additionalEventIds);

				if (count($deletedEventIds))
				{
					$model = new EventbookingModelEvent();

					$model->delete($deletedEventIds);
				}
			}
		}

		if ($numberChildrenEvents)
		{
			$row->max_end_date = EventbookingHelper::updateParentMaxEventDate($row->id);
		}

		// Store status of update data from main event checkbox
		if (isset($data['update_data_from_main_event']))
		{
			$updateDataFromMainEvent = 1;
		}
		else
		{
			$updateDataFromMainEvent = 0;
		}

		$params = new JRegistry($row->params);
		$params->set('update_data_from_main_event', $updateDataFromMainEvent);
		$row->params = $params->toString();
		$row->store();
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   EventbookingTableEvent  $row
	 */
	private function drawSettingForm($row)
	{
		$form              = JForm::getInstance('dates', $this->getFormXML($row));
		$db                = $this->db;
		$query             = $db->getQuery(true);
		$rowEvents         = [];
		$formData['dates'] = [];

		if ($row->id > 0)
		{
			$query->select('id, event_date, event_end_date, cut_off_date, registration_start_date, location_id, event_capacity')
				->from('#__eb_events')
				->where('parent_id = ' . (int) $row->id)
				->where('is_additional_date = 1')
				->order('id');
			$db->setQuery($query);
			$rowEvents = $db->loadObjectList();
		}
		else
		{
			for ($i = 0; $i < $this->params->get('max_number_dates', 3); $i++)
			{
				$rowEvent                          = new stdClass;
				$rowEvent->id                      = 0;
				$rowEvent->event_date              = null;
				$rowEvent->event_end_date          = null;
				$rowEvent->cut_off_date            = null;
				$rowEvent->registration_start_date = null;
				$rowEvent->location_id             = $row->location_id;
				$rowEvent->event_capacity          = $row->event_capacity;
				$rowEvents[]                       = $rowEvent;
			}
		}

		foreach ($rowEvents as $rowEvent)
		{
			$formData['dates'][] = [
				'id'                      => $rowEvent->id,
				'event_date'              => $rowEvent->event_date,
				'event_end_date'          => $rowEvent->event_end_date,
				'cut_off_date'            => $rowEvent->cut_off_date,
				'registration_start_date' => $rowEvent->registration_start_date,
				'location_id'             => $rowEvent->location_id,
				'event_capacity'          => $rowEvent->event_capacity,
			];
		}

		$form->bind($formData);

		if ($row->id)
		{
			$params = new JRegistry($row->params);
			$params->def('update_data_from_main_event', $this->params->get('default_update_data_from_main_event_checkbox_status', 1));

			if ($params->get('update_data_from_main_event'))
			{
				$checked = ' checked="checked"';
			}
			else
			{
				$checked = '';
			}
			?>
            <div class="row-fluid">
                <label class="checkbox">
                    <input type="checkbox" name="update_data_from_main_event" value="1"<?php echo $checked; ?>/>
                    <strong><?php echo Text::_('EB_UPDATE_DATE_FROM_MAIN_EVENT'); ?></strong>
                </label>
            </div>
			<?php
		}
		?>
        <div class="row-fluid eb-additional-dates-container">
			<?php
			foreach ($form->getFieldset() as $field)
			{
				echo $field->input;
			}
			?>
        </div>
		<?php
	}

	/**
	 * Method to get form xml definition. Change some field attributes base on Events Booking config and the event
	 * is being edited
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return string
	 */
	private function getFormXML($row)
	{
		$config = EventbookingHelper::getConfig();
		// Set some default value for form xml base on component settings
		$xml = simplexml_load_file(JPATH_ROOT . '/plugins/eventbooking/dates/form/dates.xml');

		if ($this->app->isClient('site'))
		{
			// Remove fields which are disabled on submit event form
			$removeFields = [];

			if (!$config->get('fes_show_event_end_date', 1))
			{
				$removeFields[] = 'event_end_date';
			}

			if (!$config->get('fes_show_registration_start_date', 1))
			{
				$removeFields[] = 'registration_start_date';
			}

			if (!$config->get('fes_show_cut_off_date', 1))
			{
				$removeFields[] = 'cut_off_date';
			}

			if (!$config->get('fes_show_capacity', 1))
			{
				$removeFields[] = 'event_capacity';
			}

			for ($i = 0, $n = count($xml->field->form->field); $i < $n; $i++)
			{
				$field = $xml->field->form->field[$i];

				if (in_array($field['name'], $removeFields))
				{
					unset($xml->field->form->field[$i]);
				}
			}

			reset($xml->field->form->field);
		}

		$datePickerFormat = $config->get('date_field_format', '%Y-%m-%d') . ' %H:%M';

		foreach ($xml->field->form->children() as $field)
		{
			if ($field->getName() != 'field')
			{
				continue;
			}

			if ($field['type'] == 'calendar')
			{
				$field['format'] = $datePickerFormat;
			}

			if ($row->id > 0)
			{
				if ($field['name'] == 'location_id')
				{
					$field['default'] = $row->location_id;
				}

				if ($field['name'] == 'event_capacity')
				{
					$field['default'] = $row->event_capacity;
				}
			}
		}

		return $xml->asXML();
	}

	/**
	 * Store categories of an event
	 *
	 * @param   int    $eventId
	 * @param   array  $data
	 * @param   bool   $isNew
	 */
	private function storeEventCategories($eventId, $data, $isNew)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);
		if (!$isNew)
		{
			$query->delete('#__eb_event_categories')->where('event_id=' . $eventId);
			$db->setQuery($query);
			$db->execute();
		}
		$mainCategoryId = (int) $data['main_category_id'];

		if ($mainCategoryId)
		{
			$query->clear();
			$query->insert('#__eb_event_categories')
				->columns('event_id, category_id, main_category')
				->values("$eventId, $mainCategoryId, 1");
			$db->setQuery($query);
			$db->execute();
		}

		$categories = isset($data['category_id']) ? $data['category_id'] : [];

		for ($i = 0, $n = count($categories); $i < $n; $i++)
		{
			$categoryId = (int) $categories[$i];
			if ($categoryId && ($categoryId != $mainCategoryId))
			{
				$query->clear();
				$query->insert('#__eb_event_categories')
					->columns('event_id, category_id, main_category')
					->values("$eventId, $categoryId, 0");
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * Store group registration rates of an event
	 *
	 * @param $eventId
	 * @param $data
	 * @param $isNew
	 */
	private function storeEventGroupRegistrationRates($eventId, $data, $isNew)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		if (!$isNew)
		{
			$query->delete('#__eb_event_group_prices')->where('event_id=' . $eventId);
			$db->setQuery($query);
			$db->execute();
		}

		$prices            = $data['price'];
		$registrantNumbers = $data['registrant_number'];

		for ($i = 0, $n = count($prices); $i < $n; $i++)
		{
			$price            = $prices[$i];
			$registrantNumber = $registrantNumbers[$i];

			if (($registrantNumber > 0) && ($price > 0))
			{
				$query->clear()
					->insert('#__eb_event_group_prices')
					->columns('event_id, registrant_number, price')
					->values("$eventId, $registrantNumber, $price");
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * Method to check to see whether the plugin should run
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return bool
	 */
	private function canRun($row)
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		if ($row->parent_id > 0)
		{
			return false;
		}

		return true;
	}
}
