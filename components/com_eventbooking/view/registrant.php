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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

trait EventbookingViewRegistrant
{
	/**
	 * Build data use on submit event form
	 *
	 * @param   EventbookingTableRegistrant  $item
	 * @param   array                        $categories
	 * @param   array                        $locations
	 */
	public function prepareViewData()
	{
		$user                               = Factory::getUser();
		$db                                 = Factory::getDbo();
		$query                              = $db->getQuery(true);
		$config                             = EventbookingHelper::getConfig();
		$event                              = EventbookingHelperDatabase::getEvent((int) $this->item->event_id);
		$config->collect_member_information = EventbookingHelperRegistration::isCollectMembersInformation($event, $config);
		$item                               = $this->item;

		if ($user->authorise('core.admin', 'com_eventbooking')
			|| $user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$this->userType = 'registrants_manager';
		}
		else
		{
			$this->userType = 'registrant';
		}

		if ($item->id)
		{
			if ($this->item->is_group_billing)
			{
				$rowFields = EventbookingHelperRegistration::getFormFields($this->item->event_id, 1, $this->item->language);
			}
			elseif ($item->group_id > 0)
			{
				$rowFields = EventbookingHelperRegistration::getFormFields($item->event_id, 2, $item->language);

				// Check to see whether this is first group member
				$query->clear()
					->select('id')
					->from('#__eb_registrants')
					->where('group_id = ' . $item->group_id)
					->order('id');
				$db->setQuery($query);

				if ($item->id == $db->loadResult())
				{
					$item->is_first_group_member = true;
				}
			}
			else
			{
				$rowFields = EventbookingHelperRegistration::getFormFields($item->event_id, 0, $item->language);
			}

			$data = EventbookingHelperRegistration::getRegistrantData($item, $rowFields);

			$query->clear()
				->select('*')
				->from('#__eb_registrants')
				->where('group_id=' . $item->id)
				->order('id');
			$db->setQuery($query, 0, $item->number_registrants);
			$rowMembers = $db->loadObjectList();

			$useDefault = false;
		}
		else
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($item->event_id, 0);

			$useDefault = true;
			$data       = [];
			$rowMembers = [];
		}

		if (!isset($data['country']))
		{
			$data['country'] = $config->default_country;
		}

		$form = new RADForm($rowFields);
		$form->bind($data, $useDefault);
		$form->setEventId($item->event_id);

		if (empty($item->disableEdit) && ($this->userType == 'registrants_manager' || $item->published == 0))
		{
			$this->canChangeFeeFields = true;
			$form->prepareFormFields('setRecalculateFee();');
		}
		else
		{
			$this->canChangeFeeFields = false;
		}

		$form->buildFieldsDependency();

		$numberMembers = count($rowMembers);

		if ($this->item->is_group_billing && $config->collect_member_information
			&& ($numberMembers < $this->item->number_registrants))
		{
			for ($i = 0; $i < ($this->item->number_registrants - $numberMembers); $i++)
			{
				$rowMember                     = new RADTable('#__eb_registrants', 'id', $db);
				$rowMember->event_id           = $this->item->event_id;
				$rowMember->group_id           = $this->item->id;
				$rowMember->user_id            = $this->item->user_id;
				$rowMember->number_registrants = 1;
				$rowMember->store();
				$rowMembers[] = $rowMember;
			}
		}

		if (count($rowMembers))
		{
			$this->memberFormFields = EventbookingHelperRegistration::getFormFields($this->item->event_id, 2, $this->item->language);
		}

		if ($event->has_multiple_ticket_types)
		{
			$this->ticketTypes = EventbookingHelperData::getTicketTypes($event->id);

			if ($this->item->id)
			{
				$query->clear()
					->select('*')
					->from('#__eb_registrant_tickets')
					->where('registrant_id = ' . (int) $this->item->id);
				$db->setQuery($query);
				$registrantTickets = $db->loadObjectList('ticket_type_id');
			}
			else
			{
				$registrantTickets = [];
			}

			$this->registrantTickets = $registrantTickets;
		}

		$filters = [];

		if ($config->hide_disable_registration_events)
		{
			$filters[] = 'registration_type != 3';
		}

		$rows = EventbookingHelperDatabase::getAllEvents($config->sort_events_dropdown, $config->hide_past_events_from_events_dropdown, $filters);

		if ($config->hide_past_events_from_events_dropdown && $this->item->id)
		{
			$eventExists = false;

			foreach ($rows as $row)
			{
				if ($row->id == $this->item->event_id)
				{
					$eventExists = true;
					break;
				}
			}

			if (!$eventExists)
			{
				$rows[] = $event;
			}
		}

		$this->lists['event_id'] = EventbookingHelperHtml::getEventsDropdown($rows, 'event_id', 'class="form-select"', $this->item->event_id);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_PENDING'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_PAID'));

		if ($config->activate_waitinglist_feature || in_array($this->item->published, [3, 4]))
		{
			$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_WAITING_LIST'));
			$options[] = HTMLHelper::_('select.option', 4, Text::_('EB_WAITING_LIST_CANCELLED'));
		}

		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_CANCELLED'));

		$this->lists['published'] = HTMLHelper::_('select.genericlist', $options, 'published', ' class="form-select" ', 'value', 'text',
			$this->item->published);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('EB_PAYMENT_STATUS'));
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_PARTIAL_PAYMENT'));

		if (strpos($this->item->payment_method, 'os_offline') !== false)
		{
			$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_DEPOSIT_PAID'));
		}

		$options[]                     = HTMLHelper::_('select.option', 1, Text::_('EB_FULL_PAYMENT'));
		$this->lists['payment_status'] = HTMLHelper::_('select.genericlist', $options, 'payment_status', ' class="form-select" ', 'value', 'text',
			$this->item->payment_status);

		// Payment methods
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_PAYMENT_METHOD'), 'name', 'title');
		$query->clear()
			->select('name, title, params')
			->from('#__eb_payment_plugins')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$methods = $db->loadObjectList();

		$options                       = array_merge($options, $methods);
		$this->lists['payment_method'] = HTMLHelper::_('select.genericlist', $options, 'payment_method', ' class="form-select" ', 'name', 'title',
			$this->item->payment_method ? $this->item->payment_method : 'os_offline');

		if ($config->activate_checkin_registrants)
		{
			$this->lists['checked_in'] = HTMLHelper::_('select.booleanlist', 'checked_in', '', $this->item->checked_in);
		}


		$showPaymentFee = false;

		foreach ($methods as $method)
		{
			$params            = new Joomla\Registry\Registry($method->params);
			$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
			$paymentFeePercent = (float) $params->get('payment_fee_percent');

			if ($paymentFeeAmount != 0 || $paymentFeePercent != 0)
			{
				$showPaymentFee = true;
				break;
			}
		}

		$this->config         = $config;
		$this->event          = $event;
		$this->form           = $form;
		$this->rowMembers     = $rowMembers;
		$this->showPaymentFee = $showPaymentFee;
	}
}