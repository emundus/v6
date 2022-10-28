<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class plgEventBookingTicketTypes extends CMSPlugin
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
	 * @param   JTable  $row
	 *
	 * @return array
	 */
	public function onEditEvent($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		return [
			'title' => Text::_('EB_TICKET_TYPES'),
			'form'  => $this->drawSettingForm($row),
		];
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   array                   $data
	 * @param   bool                    $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		// The plugin will only be available in the backend
		if (!$this->canRun($row))
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$config     = EventbookingHelper::getConfig();
		$dateFormat = str_replace('%', '', $config->get('date_field_format', '%Y-%m-%d'));

		// Convert date data to Y-m-d H:i:s format
		$dateFields = [
			'publish_up',
			'publish_down',
		];


		$hasMultipleTicketTypes = 0;
		$ticketTypes            = isset($data['ticket_types']) && is_array($data['ticket_types']) ? $data['ticket_types'] : [];

		$ticketTypeIds = [];
		$ordering      = 1;

		foreach ($ticketTypes as $ticketType)
		{
			if (empty($ticketType['title']))
			{
				continue;
			}

			if (empty($ticketType['weight']))
			{
				$ticketType['weight'] = 1;
			}

			// Convert date fields to correct format
			foreach ($dateFields as $field)
			{
				if ($ticketType[$field] && strpos($ticketType[$field], '0000') === false)
				{
					$datetime = DateTime::createFromFormat($dateFormat . ' H:i', $ticketType[$field]);

					if ($datetime !== false)
					{
						$ticketType[$field] = $datetime->format('Y-m-d H:i:s');
					}
				}
			}

			/* @var EventbookingTableTickettype $rowTicketType */
			$rowTicketType = Table::getInstance('TicketType', 'EventbookingTable');
			$rowTicketType->bind($ticketType);

			// Prevent ticket type data being moved to new event on saveAsCopy
			if ($isNew)
			{
				$rowTicketType->id = 0;
			}

			$rowTicketType->event_id = $row->id;
			$rowTicketType->ordering = $ordering++;
			$rowTicketType->store();
			$ticketTypeIds[]        = $rowTicketType->id;
			$hasMultipleTicketTypes = true;
		}

		if (!$isNew)
		{
			$query->delete('#__eb_ticket_types')
				->where('event_id = ' . $row->id);

			if (count($ticketTypeIds))
			{
				$query->where('id NOT IN (' . implode(',', $ticketTypeIds) . ')');
			}

			$db->setQuery($query)
				->execute();
		}

		$row->has_multiple_ticket_types = $hasMultipleTicketTypes;
		$params                         = new Registry($row->params);
		$params->set('ticket_types_collect_members_information', $data['ticket_types_collect_members_information']);
		$row->params = $params->toString();

		$row->store();

		if ($row->event_type == 1)
		{
			$this->storeTicketTypeForChildrenEvents($row, $ticketTypeIds, $isNew, $hasMultipleTicketTypes);
		}
	}

	/**
	 * Generate invoice number after registrant complete payment for registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 *
	 * @return bool
	 */
	public function onAfterPaymentSuccess($row)
	{
		if (strpos($row->payment_method, 'os_offline') === false)
		{
			$this->processTicketTypes($row);
		}
	}

	/**
	 * Generate invoice number after registrant complete registration in case he uses offline payment
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	public function onAfterStoreRegistrant($row)
	{
		if (strpos($row->payment_method, 'os_offline') !== false)
		{
			$this->processTicketTypes($row);
		}
	}


	/**
	 * Store ticket types data for children events
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   array                   $ticketTypeIds
	 * @param   bool                    $isNew
	 */
	private function storeTicketTypeForChildrenEvents($row, $ticketTypeIds, $isNew, $hasMultipleTicketTypes)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		// Get list of children events
		$query->select('id')
			->from('#__eb_events')
			->where('parent_id = ' . $row->id);
		$db->setQuery($query);
		$childEventIds = $db->loadColumn();

		if (!count($childEventIds))
		{
			$row->event_type = 0;
			$row->store();

			return;
		}

		if ($isNew)
		{
			foreach ($childEventIds as $childEventId)
			{
				$sql = 'INSERT INTO #__eb_ticket_types (event_id, title, discount_rules, description, price, capacity, weight, `access`, max_tickets_per_booking, publish_up, publish_down, ordering, parent_ticket_type_id)'
					. " SELECT $childEventId, title, discount_rules, description, price, capacity, weight, `access`, max_tickets_per_booking, publish_up, publish_down, ordering, id FROM #__eb_ticket_types WHERE event_id = $row->id";
				$db->setQuery($sql);
				$db->execute();
			}
		}
		else
		{
			foreach ($childEventIds as $childEventId)
			{
				foreach ($ticketTypeIds as $ticketTypeId)
				{
					$query->clear()
						->select('*')
						->from('#__eb_ticket_types')
						->where('id = ' . $ticketTypeId);
					$db->setQuery($query);
					$rowParentTicketType = $db->loadObject();

					$query->clear()
						->select('id')
						->from('#__eb_ticket_types')
						->where('event_id = ' . $childEventId)
						->where('parent_ticket_type_id = ' . $rowParentTicketType->id);
					$db->setQuery($query);
					$childEventTicketTypeId = (int) $db->loadResult();

					$weight = (int) $rowParentTicketType->weight ?: 1;

					if ($childEventTicketTypeId)
					{
						// Update data of existing ticket type
						$query->clear()
							->update('#__eb_ticket_types')
							->set('title = ' . $db->quote($rowParentTicketType->title))
							->set('discount_rules = ' . $db->quote($rowParentTicketType->discount_rules))
							->set('description = ' . $db->quote($rowParentTicketType->description))
							->set('price = ' . $db->quote($rowParentTicketType->price))
							->set('capacity = ' . $db->quote($rowParentTicketType->capacity))
							->set('weight = ' . $weight)
							->set('access = ' . $db->quote($rowParentTicketType->access))
							->set('max_tickets_per_booking = ' . $db->quote($rowParentTicketType->max_tickets_per_booking))
							->set('publish_up = ' . $db->quote($rowParentTicketType->publish_up))
							->set('publish_down = ' . $db->quote($rowParentTicketType->publish_down))
							->set('ordering = ' . $db->quote($rowParentTicketType->ordering))
							->where('id = ' . $childEventTicketTypeId);
						$db->setQuery($query)
							->execute();
					}
					else
					{
						$title                = $db->quote($rowParentTicketType->title);
						$discountRules        = $db->quote($rowParentTicketType->discount_rules);
						$description          = $db->quote($rowParentTicketType->description);
						$price                = $db->quote($rowParentTicketType->price);
						$capacity             = $db->quote($rowParentTicketType->capacity);
						$maxTicketsPerBooking = $db->quote($rowParentTicketType->max_tickets_per_booking);
						$publishUp            = $db->quote($rowParentTicketType->publish_up);
						$publishDown          = $db->quote($rowParentTicketType->publish_down);
						$ordering             = $db->quote($rowParentTicketType->ordering);
						$access               = $db->quote($rowParentTicketType->access);

						// Insert new Ticket type data
						$query->clear()
							->insert('#__eb_ticket_types')
							->columns('event_id, title, discount_rules, description, price, capacity, weight, `access`, max_tickets_per_booking, publish_up, publish_down, ordering, parent_ticket_type_id')
							->values("$childEventId, $title, $discountRules, $description ,$price, $capacity, $weight, $access, $maxTicketsPerBooking, $publishUp, $publishDown, $ordering, $rowParentTicketType->id");
						$db->setQuery($query)
							->execute();
					}
				}
			}
		}

		// Remove the deleted ticket types
		$query->clear()
			->delete('#__eb_ticket_types')
			->where('event_id IN (' . implode(',', $childEventIds) . ')');

		if (count($ticketTypeIds))
		{
			$query->where('parent_ticket_type_id NOT IN (' . implode(',', $ticketTypeIds) . ')');
		}

		$db->setQuery($query)
			->execute();

		$query->clear()
			->update('#__eb_events')
			->set('has_multiple_ticket_types = ' . $hasMultipleTicketTypes)
			->where('parent_id = ' . $row->id);
		$db->setQuery($query)
			->execute();
	}

	/**
	 * Process ticket types data after registration is completed:
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	private function processTicketTypes($row)
	{
		$config = EventbookingHelper::getConfig();
		$event  = EventbookingHelperDatabase::getEvent($row->event_id);

		if ($event->has_multiple_ticket_types && $config->get('calculate_number_registrants_base_on_tickets_quantity', 1))
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('a.weight, b.quantity')
				->from('#__eb_ticket_types AS a')
				->innerJoin('#__eb_registrant_tickets AS b ON a.id = b.ticket_type_id')
				->where('b.registrant_id = ' . $row->id);
			$db->setQuery($query);
			$rowTickets        = $db->loadObjectList();
			$numberRegistrants = 0;

			foreach ($rowTickets as $rowTicket)
			{
				$weight = (int) $rowTicket->weight ?: 1;

				$numberRegistrants += $weight * $rowTicket->quantity;
			}

			if ($numberRegistrants > 0)
			{
				$row->number_registrants = $numberRegistrants;
				$row->store();
			}
		}
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		if ($row->id)
		{
			$ticketTypes               = EventbookingHelperData::getTicketTypes($row->id);
			$params                    = new Registry($row->params);
			$collectMembersInformation = $params->get('ticket_types_collect_members_information', 0);
		}
		else
		{
			$ticketTypes = [];

			for ($i = 0; $i <= 4; $i++)
			{
				$ticketType                          = new stdClass;
				$ticketType->id                      = 0;
				$ticketType->title                   = '';
				$ticketType->price                   = '';
				$ticketType->discount_rules          = '';
				$ticketType->description             = '';
				$ticketType->registered              = 0;
				$ticketType->capacity                = '';
				$ticketType->weight                  = '';
				$ticketType->max_tickets_per_booking = '';
				$ticketType->publish_up              = null;
				$ticketType->publish_down            = null;
				$ticketType->access                  = 1;
				$ticketTypes[]                       = $ticketType;
			}

			$collectMembersInformation = 0;
		}

		$form = JForm::getInstance('tickettypes', $this->getFormXML($row));

		$formData['ticket_types'] = [];

		foreach ($ticketTypes as $ticketType)
		{
			$formData['ticket_types'][] = [
				'id'                      => $ticketType->id,
				'title'                   => $ticketType->title,
				'price'                   => $ticketType->price,
				'discount_rules'          => $ticketType->discount_rules,
				'description'             => $ticketType->description,
				'registered'              => $ticketType->registered,
				'capacity'                => $ticketType->capacity,
				'weight'                  => $ticketType->weight,
				'max_tickets_per_booking' => $ticketType->max_tickets_per_booking,
				'publish_up'              => $ticketType->publish_up,
				'publish_down'            => $ticketType->publish_down,
				'access'                  => $ticketType->access,
			];
		}

		$form->bind($formData);

		$layoutData = [
			'collectMembersInformation' => $collectMembersInformation,
			'form'                      => $form,
		];

		return EventbookingHelperHtml::loadCommonLayout('plugins/tickettypes_form.php', $layoutData);
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
		$config           = EventbookingHelper::getConfig();
		$datePickerFormat = $config->get('date_field_format', '%Y-%m-%d') . ' %H:%M';

		// Set some default value for form xml base on component settings

		if (file_exists(JPATH_ROOT.'/plugins/eventbooking/tickettypes/form/override_tickettype.xml'))
		{
			$xml = simplexml_load_file(JPATH_ROOT . '/plugins/eventbooking/tickettypes/form/override_tickettype.xml');
		}
		else
		{
			$xml = simplexml_load_file(JPATH_ROOT . '/plugins/eventbooking/tickettypes/form/tickettype.xml');
		}

		foreach ($xml->field->form->children() as $field)
		{
			if ($field->getName() != 'field' && $field['type'] == 'calendar')
			{
				$field['format'] = $datePickerFormat;
			}

			if ($this->app->isClient('site') && in_array($field['name'], ['title', 'description']))
			{
				unset($field['class']);
			}
		}

		$removedFields = 0;


		if (!$this->params->get('enable_weight'))
		{
			unset($xml->field->form->field[4]);

			$removedFields++;
		}

		if (!$this->params->get('enable_discount_rules'))
		{
			unset($xml->field->form->field[6 - $removedFields]);

			$removedFields++;
		}


		if (!$this->params->get('enable_description', 1))
		{
			unset($xml->field->form->field[7 - $removedFields]);
			$removedFields++;
		}

		if (!$this->params->get('enable_publish_up', 1))
		{
			unset($xml->field->form->field[8 - $removedFields]);
			$removedFields++;
		}

		if (!$this->params->get('enable_publish_down', 1))
		{
			unset($xml->field->form->field[9 - $removedFields]);
			$removedFields++;
		}

		if (!$this->params->get('enable_access', 0))
		{
			unset($xml->field->form->field[10 - $removedFields]);
		}

		return $xml->asXML();
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

		return true;
	}
}
