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
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * Event Booking Registrant Model
 *
 * @package        Joomla
 * @subpackage     Event Booking
 */
class EventbookingModelCommonRegistrant extends RADModelAdmin
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$this->triggerEvents = true;

		parent::__construct($config);

		$this->state->insert('filter_event_id', 'int', 0);
	}

	/**
	 * Initial registrant data
	 *
	 * @see RADModelAdmin::initData()
	 */
	public function initData()
	{
		parent::initData();

		$this->data->event_id = $this->state->filter_event_id;
	}

	/**
	 * Method to store a registrant
	 *
	 * @param   RADInput  $input
	 * @param   array     $ignore
	 *
	 * @return boolean    True on success
	 *
	 * @throws Exception
	 */
	public function store($input, $ignore = [])
	{
		/* @var EventbookingTableRegistrant $row */
		$row = $this->getTable();
		$id  = $input->getInt('id', 0);

		if ($id)
		{
			$row->load($id);

			if ($row->is_group_billing)
			{
				$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 1, $row->language);
			}
			else
			{
				$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 0, $row->language);
			}
		}
		else
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($input->getInt('event_id'), 0);
		}

		$form = new RADForm($rowFields);
		$form->bind($input->getData());

		if ($id)
		{
			$isNew = false;
			$this->updateRegistration($row, $input, $form);
			Factory::getApplication()->triggerEvent($this->eventAfterSave, [$this->context, $row, $isNew]);
		}
		else
		{
			$isNew = true;
			$this->addNewRegistration($row, $input, $form);
			Factory::getApplication()->triggerEvent($this->eventAfterSave, [$this->context, $row, $isNew]);
		}

		$input->set('id', $row->id);

		return true;
	}

	/**
	 * Store new registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADInput                     $input
	 * @param   RADForm                      $form
	 *
	 * @throws Exception
	 */
	protected function addNewRegistration($row, $input, $form)
	{
		$app    = Factory::getApplication();
		$config = EventbookingHelper::getConfig();

		// Give beforeStore method a chance to prepare the data
		$this->beforeStore($row, $input, true);

		$data = $input->getData();

		// In case number registrants is empty, we set it default to 1
		$data['number_registrants'] = (int) $data['number_registrants'];

		if (empty($data['number_registrants']))
		{
			$data['number_registrants'] = 1;
		}

		$data['transaction_id']    = strtoupper(JUserHelper::genRandomPassword());
		$data['registration_code'] = EventbookingHelperRegistration::getRegistrationCode();
		$data['ticket_qrcode']     = EventbookingHelperRegistration::getTicketCode();

		$row->bind($data);

		$row->event_id = (int) $row->event_id;
		$row->user_id  = (int) $row->user_id;

		if (!$row->payment_method || $row->published == 0)
		{
			$row->payment_method = 'os_offline';
		}

		$row->register_date = Factory::getDate()->toSql();

		// In case total amount is not entered, calculate it automatically
		if ($row->total_amount === '')
		{
			$event = EventbookingHelperDatabase::getEvent($row->event_id);

			if ($event->has_multiple_ticket_types)
			{
				$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateIndividualRegistrationFees', [$event, $form, $data, $config, $row->payment_method], 'Helper');

				$row->total_amount           = round($fees['total_amount'], 2);
				$row->discount_amount        = round($fees['discount_amount'], 2);
				$row->tax_amount             = round($fees['tax_amount'], 2);
				$row->late_fee               = round($fees['late_fee'], 2);
				$row->payment_processing_fee = round($fees['payment_processing_fee'], 2);
				$row->amount                 = round($fees['amount'], 2);
			}
			else
			{
				$rate               = EventbookingHelper::callOverridableHelperMethod('Registration', 'getRegistrationRate', [$data['event_id'], $data['number_registrants']]);
				$feeCalculationTags = [
					'NUMBER_REGISTRANTS' => $row->number_registrants,
					'INDIVIDUAL_PRICE'   => $rate,
				];

				if ($config->event_custom_field && file_exists(JPATH_ROOT . '/components/com_eventbooking/fields.xml'))
				{
					EventbookingHelperData::prepareCustomFieldsData([$event]);

					$filterInput = JFilterInput::getInstance();

					foreach ($event->paramData as $customFieldName => $param)
					{
						$feeCalculationTags[strtoupper($customFieldName)] = $filterInput->clean($param['value'], 'float');
					}
				}

				$row->total_amount = $row->amount = $rate * $data['number_registrants'] + $form->calculateFee($feeCalculationTags);
			}
		}

		if ($row->amount === '')
		{
			$row->amount = $row->total_amount - $row->discount_amount + $row->tax_amount + $row->late_fee + $row->payment_processing_fee;
		}

		$isTicketTypesCollectMembersInformation = false;

		if ($event->has_multiple_ticket_types)
		{
			$params = new Registry($event->params);

			if ($params->get('ticket_types_collect_members_information'))
			{
				$isTicketTypesCollectMembersInformation = true;
			}
		}

		if ($isTicketTypesCollectMembersInformation || $row->number_registrants > 1)
		{
			$row->is_group_billing = 1;
		}
		else
		{
			$row->is_group_billing = 0;
		}

		// Store registration data
		$row->store();

		// Store custom field data
		$form->storeData($row->id, $data);

		// Store tickets
		$this->storeRegistrantTickets($row, $data);

		// Initialize group members data for the group
		if ($isTicketTypesCollectMembersInformation)
		{
			// Add group member records
			$this->storeGroupMembersForTicketTypes($row, $data);
		}

		// Trigger afterStore method to further storing process if needed
		$this->afterStore($row, $input, true);

		// Trigger onAfterStoreRegistrant event
		$app->triggerEvent('onAfterStoreRegistrant', [$row]);

		// Trigger onAfterPaymentSuccess in case registration is marked as paid
		if ($row->published == 1)
		{
			$app->triggerEvent('onAfterPaymentSuccess', [$row]);
		}

		// If this is group registration, we need to collect registrants information before sending email, so return for now
		if ($row->number_registrants != 1)
		{
			return;
		}

		// In case individual registration, we will send notification email to registrant
		EventbookingHelper::loadLanguage();

		if ((int) $row->published === 3)
		{
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendWaitinglistEmail', [$row, $config]);
		}
		else
		{
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendEmails', [$row, $config]);
		}
	}

	/**
	 * Update existing registration data
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADInput                     $input
	 * @param   RADForm                      $form
	 *
	 * @throws Exception
	 */
	protected function updateRegistration($row, $input, $form)
	{
		$app            = Factory::getApplication();
		$user           = Factory::getUser();
		$config         = EventbookingHelper::getConfig();
		$event          = EventbookingHelperDatabase::getEvent($row->event_id);
		$db             = $this->getDbo();
		$query          = $db->getQuery(true);
		$nullDate       = $db->getNullDate();
		$published      = $row->published;
		$fees           = [];
		$recalculateFee = false;

		$currentEventId = $row->event_id;
		$newEventId     = $input->getInt('event_id', 0);

		$config->collect_member_information = EventbookingHelperRegistration::isCollectMembersInformation($event, $config);

		if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking') || empty($row->published))
		{
			$excludeFeeFields = false;
		}
		else
		{
			$excludeFeeFields = true;
		}

		// Give beforeStore method to chance to prepare registration data
		$this->beforeStore($row, $input, false);

		$data = $input->getData();

		// Reset number checked in counter if admin change checked in status
		if ($row->checked_in && isset($data['checked_in']) && $data['checked_in'] == 0)
		{
			$row->checked_in_count = 0;
		}

		$row->bind($data, ['language']);

		$row->user_id  = (int) $row->user_id;
		$row->event_id = (int) $row->event_id;

		if (!$row->registration_code)
		{
			$row->registration_code = EventbookingHelperRegistration::getRegistrationCode();
		}

		// Recalculate registration fees
		if (!empty($data['re_calculate_fee'])
			|| ($row->published == 0 && $app->isClient('site') && $user->id == $row->user_id))
		{
			$recalculateFee = true;

			$fees = $this->recalculateRegistrationFees($row, $data, $form);
		}

		// Group member is changed to new event, need to update data of the orginal group
		if ($row->group_id > 0 && $currentEventId != $newEventId)
		{
			$groupBilling = $this->getTable('Registrant');

			if ($groupBilling->load($row->group_id))
			{
				$groupBilling->number_registrants = $groupBilling->number_registrants - 1;
				$groupBilling->store();

				if ($groupBilling->number_registrants == 0)
				{
					$groupBilling->delete();
				}
			}

			$row->group_id = 0;
		}

		// Reset Email and SMS reminder status when event is changed
		if ($currentEventId != $newEventId)
		{
			$row->is_reminder_sent         = 0;
			$row->is_second_reminder_sent  = 0;
			$row->first_sms_reminder_sent  = 0;
			$row->second_sms_reminder_sent = 0;
		}

		if ($event->has_multiple_ticket_types)
		{
			$params = new Registry($event->params);

			if ($params->get('ticket_types_collect_members_information') && !$row->group_id)
			{
				$row->is_group_billing = 1;
			}
		}

		// Store registration ata
		$row->store();

		// Store custom fields data
		$form->storeData($row->id, $data, $excludeFeeFields);

		//Store group members data
		if ($row->is_group_billing && $config->collect_member_information)
		{
			$this->storeGroupMembers($row, $data, $fees, $recalculateFee, $excludeFeeFields);
		}

		// Store tickets data
		$this->storeRegistrantTickets($row, $data);

		//Update group members records according to group billing record
		if ($row->is_group_billing)
		{
			// Update checked_in status
			$query->update('#__eb_registrants')
				->set('checked_in = ' . (int) $row->checked_in)
				->set('event_id = ' . (int) $row->event_id)
				->where('group_id = ' . $row->id);

			if (strpos($row->payment_method, 'os_offline') !== false)
			{
				$query->set('published = ' . (int) $row->published);
			}

			$db->setQuery($query)
				->execute();
		}

		// Trigger afterStore method to further storing process if needed
		$this->afterStore($row, $input, false);

		// Trigger onAfterEditRegistrant event
		$app->triggerEvent('onAfterEditRegistrant', [$row]);

		if ($row->published == 1 && in_array($published, [0, 3]))
		{
			if (empty($row->payment_date) || $row->payment_date == $nullDate)
			{
				$row->payment_date = Factory::getDate()->toSql();
				$row->store();

				if ($row->is_group_billing)
				{
					$query->clear()
						->update('#__eb_registrants')
						->set('payment_date = ' . $db->quote($row->payment_date))
						->where('group_id = ' . $row->id);
					$db->setQuery($query);
					$db->execute();
				}
			}

			//Change from pending to paid, trigger event, send emails
			$app->triggerEvent('onAfterPaymentSuccess', [$row]);
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendRegistrationApprovedEmail', [$row, $config]);
		}
		elseif ($row->published == 2 && $published != 2)
		{
			// Update status of group members record to cancelled as well
			if ($row->is_group_billing)
			{
				$query->clear()
					->update('#__eb_registrants')
					->set('published = 2')
					->where('group_id = ' . (int) $row->id);
				$db->setQuery($query);
				$db->execute();
			}

			$app->triggerEvent('onRegistrationCancel', [$row]);

			// Load language
			EventbookingHelper::loadRegistrantLanguage($row);

			// Send registration cancelled email to registrant
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendRegistrationCancelledEmail', [$row, $config]);

			//Registration is cancelled, send notification emails to waiting list
			if ($config->activate_waitinglist_feature)
			{
				EventbookingHelper::callOverridableHelperMethod('Mail', 'sendWaitingListNotificationEmail', [$row, $config]);
			}
		}
	}

	/**
	 * Re-calculate registration fees for existing registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   array                        $data
	 * @param   RADForm                      $form
	 *
	 * @return array
	 */
	protected function recalculateRegistrationFees($row, &$data, $form)
	{
		$config = EventbookingHelper::getConfig();

		if ($row->coupon_id)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('code'))
				->from('#__eb_coupons')
				->where('id = ' . $row->coupon_id);
			$db->setQuery($query);
			$data['coupon_code'] = $db->loadResult();
		}

		$event = EventbookingHelperDatabase::getEvent($row->event_id, $row->register_date);

		if ($event->has_multiple_ticket_types)
		{
			$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateIndividualRegistrationFees', [$event, $form, $data, $config, $row->payment_method], 'Helper');
		}
		elseif ($row->is_group_billing)
		{
			$data['number_registrants'] = $row->number_registrants;
			$data['re_calculate_fee']   = true;

			$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateGroupRegistrationFees', [$event, $form, $data, $config, $row->payment_method], 'Helper');
		}
		else
		{
			// Individual registration
			$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateIndividualRegistrationFees', [$event, $form, $data, $config, $row->payment_method], 'Helper');
		}

		$row->total_amount    = round($fees['total_amount'], 2);
		$row->discount_amount = round($fees['discount_amount'], 2);
		$row->tax_amount      = round($fees['tax_amount'], 2);
		$row->amount          = round($fees['amount'], 2);

		return $fees;
	}

	/**
	 * Store group members data for group registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   array                        $data
	 * @param   array                        $fees
	 * @param   bool                         $recalculateFee
	 */
	protected function storeGroupMembers($row, $data, $fees, $recalculateFee, $excludeFeeFields = false)
	{
		$app      = Factory::getApplication();
		$db       = $this->getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = Factory::getDate()->toSql();
		$ids      = (array) $data['ids'];

		if ($recalculateFee)
		{
			$membersTotalAmount    = $fees['members_total_amount'];
			$membersDiscountAmount = $fees['members_discount_amount'];
			$membersTaxAmount      = $fees['members_tax_amount'];
			$membersLateFee        = $fees['members_late_fee'];
		}

		$memberFormFields = EventbookingHelperRegistration::getFormFields($row->event_id, 2);

		for ($i = 0; $i < $row->number_registrants; $i++)
		{
			$memberId = $ids[$i];

			/* @var $rowMember EventbookingTableRegistrant */
			$rowMember = $this->getTable();
			$rowMember->load($memberId);
			$rowMember->event_id       = $row->event_id;
			$rowMember->published      = $row->published;
			$rowMember->payment_method = $row->payment_method;
			$rowMember->transaction_id = $row->transaction_id;
			$rowMember->invoice_number = $row->invoice_number;

			if (!$memberId)
			{
				$rowMember->group_id           = $row->id;
				$rowMember->user_id            = $row->user_id;
				$rowMember->number_registrants = 1;
				$rowMember->register_date      = $nowDate;
			}

			if (!$rowMember->register_date || $rowMember->register_date == $nullDate)
			{
				$rowMember->register_date = $row->register_date;
			}

			if (!$rowMember->registration_code)
			{
				$rowMember->registration_code = EventbookingHelperRegistration::getRegistrationCode();
			}

			$memberForm = new RADForm($memberFormFields);
			$memberForm->setFieldSuffix($i + 1);
			$memberForm->bind($data);
			$memberForm->removeFieldSuffix();
			$memberData = $memberForm->getFormData();
			$rowMember->bind($memberData);

			if ($recalculateFee)
			{
				$rowMember->total_amount    = $membersTotalAmount[$i];
				$rowMember->discount_amount = $membersDiscountAmount[$i];
				$rowMember->late_fee        = $membersLateFee[$i];
				$rowMember->tax_amount      = $membersTaxAmount[$i];
				$rowMember->amount          = $rowMember->total_amount - $rowMember->discount_amount + $rowMember->tax_amount + $rowMember->late_fee;
			}

			$rowMember->store();
			$memberForm->storeData($rowMember->id, $memberData, $excludeFeeFields);

			if (!$memberId)
			{
				// Trigger onAfterStoreRegistrant event
				$app->triggerEvent('onAfterStoreRegistrant', [$rowMember]);

				// Trigger onAfterPaymentSuccess in case registration is marked as paid
				if ($rowMember->published == 1)
				{
					$app->triggerEvent('onAfterPaymentSuccess', [$rowMember]);
				}
			}
		}
	}


	/**
	 * Resend confirmation email to registrant
	 *
	 * @param $id
	 *
	 * @return bool True if email is successfully delivered
	 */
	public function resendEmail($id)
	{
		/* @var EventbookingTableRegistrant $row */
		$row = $this->getTable();
		$row->load($id);

		if ($row->group_id > 0)
		{
			// We don't send email to group members, return false
			return false;
		}

		EventbookingHelper::loadRegistrantLanguage($row);

		$config = EventbookingHelper::getConfig();

		if ($row->published == 3)
		{
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendWaitinglistEmail', [$row, $config]);
		}
		else
		{
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendEmails', [$row, $config]);
		}

		return true;
	}

	/**
	 * Send certificates to registrant
	 *
	 * @param   int  $id
	 *
	 * @return void
	 * @throws Exception
	 */
	public function sendCertificates($id)
	{
		$config = EventbookingHelper::getConfig();

		/* @var EventbookingTableRegistrant $row */
		$row = $this->getTable();
		$row->load($id);

		// Perform basic validation to make sure certificate is not being sent by mistake
		if ($config->download_certificate_if_checked_in && !$row->checked_in)
		{
			throw new Exception(Text::_('EB_CERTIFICATE_CHECKED_IN_REGISTRANTS_ONLY'), 403);
		}

		$rowEvent = EventbookingHelperDatabase::getEvent($row->event_id);

		if ($rowEvent->activate_certificate_feature == 0 || ($rowEvent->activate_certificate_feature == 2 && !$config->activate_certificate_feature))
		{
			throw new Exception(sprintf('Certificate is not enabled for event %s', $rowEvent->title), 403);
		}

		EventbookingHelper::loadRegistrantLanguage($row);

		$config = EventbookingHelper::getConfig();

		EventbookingHelper::callOverridableHelperMethod('Mail', 'sendCertificateEmail', [$row, $config]);
	}

	/**
	 * Resend confirmation email to registrant
	 *
	 * @param $id
	 *
	 * @return bool True if email is successfully delivered
	 * @throws Exception
	 */
	public function sendPaymentRequestEmail($id)
	{
		/* @var EventbookingTableRegistrant $row */
		$row = $this->getTable();
		$row->load($id);

		if ($row->group_id > 0)
		{
			// We don't send email to group members, return false
			throw new Exception('Request payment email could not be ent to group members');
		}

		if ($row->published == 1 && $row->payment_status == 1)
		{
			// We don't send request payment email to paid registration
			throw new Exception('Request payment can only be sent to waiting list or pending registration');
		}

		$config = EventbookingHelper::getConfig();

		EventbookingHelper::callOverridableHelperMethod('Mail', 'sendRequestPaymentEmail', [$row, $config]);
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array  $cid    A list of the primary keys to change.
	 * @param   int    $state  The value of the published state.
	 *
	 * @throws Exception
	 */
	public function publish($cid, $state = 1)
	{
		$app = Factory::getApplication();
		$db  = $this->getDbo();

		if ($state == 1 && count($cid))
		{
			$config = EventbookingHelper::getConfig();

			/* @var EventbookingTableRegistrant $row */
			$row = $this->getTable();

			foreach ($cid as $registrantId)
			{
				$row->load($registrantId);

				if (!$row->published)
				{
					if (empty($row->payment_date) || ($row->payment_date == $db->getNullDate()))
					{
						$row->payment_date = Factory::getDate()->toSql();
						$row->store();
					}

					$row->published = 1;

					// Trigger event
					$app->triggerEvent('onAfterPaymentSuccess', [$row]);

					// Re-generate invoice with Paid status
					if ($config->activate_invoice_feature && $row->invoice_number)
					{
						EventbookingHelper::generateInvoicePDF($row);
					}

					EventbookingHelper::callOverridableHelperMethod('Mail', 'sendRegistrationApprovedEmail', [$row, $config]);
				}
			}

			$app->triggerEvent($this->eventChangeState, [$this->context, $cid, $state]);
		}
		elseif (count($cid))
		{
			$app->triggerEvent($this->eventChangeState, [$this->context, $cid, $state]);
		}

		$cids  = implode(',', $cid);
		$query = $db->getQuery(true)
			->update('#__eb_registrants')
			->set('published = ' . (int) $state)
			->where("(id IN ($cids) OR group_id IN ($cids))");

		if ($state == 0)
		{
			$query->where("payment_method LIKE 'os_offline%'");
		}

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Method to remove registrants
	 *
	 * @access    public
	 * @return    boolean    True on success
	 */
	public function delete($cid = [])
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		/* @var EventbookingTableRegistrant $row */
		$row = $this->getTable();

		if (count($cid))
		{
			$app = Factory::getApplication();

			foreach ($cid as $registrantId)
			{
				$row->load($registrantId);
				$app->triggerEvent('onBeforeDeleteRegistrant', [$row]);

				if ($row->group_id > 0)
				{
					$row->total_amount    = (float) $row->total_amount;
					$row->discount_amount = (float) $row->discount_amount;
					$row->tax_amount      = (float) $row->tax_amount;
					$row->amount          = (float) $row->amount;
					$query->update('#__eb_registrants')
						->set('number_registrants = number_registrants -1')
						->set('total_amount = total_amount - ' . $row->total_amount)
						->set('discount_amount = discount_amount - ' . $row->discount_amount)
						->set('tax_amount = tax_amount - ' . $row->tax_amount)
						->set('amount = amount - ' . $row->amount)
						->where('id=' . $row->group_id);
					$db->setQuery($query)
						->execute();

					$query->clear()
						->select('number_registrants')
						->from('#__eb_registrants')
						->where('id=' . $row->group_id);
					$db->setQuery($query);
					$numberRegistrants = (int) $db->loadResult();

					if ($numberRegistrants == 0)
					{
						$query->clear()
							->delete('#__eb_field_values')->where('registrant_id=' . $row->group_id);
						$db->setQuery($query)
							->execute();

						$query->clear()
							->delete('#__eb_registrants')
							->where('id = ' . $row->group_id);
						$db->setQuery($query)
							->execute();
					}
				}
			}

			$cids = implode(',', $cid);
			$query->clear()
				->select('id')
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
			$db->setQuery($query)
				->execute();

			$app->triggerEvent('onRegistrantsAfterDelete', [$this->context, $cid]);
		}

		return true;
	}

	/**
	 * Checkin registrant
	 *
	 * @param   int   $id
	 * @param   bool  $checkinAllGroupMembers
	 *
	 * @return int
	 */
	public function checkinRegistrant($id, $checkinAllGroupMembers = false)
	{
		/* @var EventbookingTableRegistrant $row */
		$row = $this->getTable();

		if (!$row->load($id))
		{
			return 0;
		}

		if ($row->checked_in)
		{
			return 1;
		}

		if ($row->published == 2)
		{
			return 3;
		}

		$db       = $this->getDbo();
		$query    = $db->getQuery(true);
		$now      = Factory::getDate()->toSql();
		$nullDate = $db->getNullDate();

		if ($row->is_group_billing)
		{
			// Check in group billing record
			if ($checkinAllGroupMembers)
			{
				$row->checked_in_count = $row->number_registrants;
				$row->checked_in       = 1;
				$row->checked_in_at    = $now;
				$row->checked_out_at   = $nullDate;
				$row->store();

				// Check in all other group members belong to this group

				$query->update('#__eb_registrants')
					->set('checked_in_count = 1')
					->set('checked_in = 1')
					->set('checked_in_at = ' . $db->quote($now))
					->set('checked_out_at = ' . $db->quote($nullDate))
					->where('group_id = ' . $row->id);
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				// Get the next group member and check in that member
				$query->select('id')
					->from('#__eb_registrants')
					->where('group_id = ' . $row->id)
					->where('checked_in = 0')
					->order('id');
				$db->setQuery($query);
				$nextGroupMemberId = $db->loadResult();

				if ($nextGroupMemberId > 0)
				{
					/* @var EventbookingTableRegistrant $groupMember */
					$groupMember = $this->getTable();
					$groupMember->load($nextGroupMemberId);
					$groupMember->checked_in_count = 1;
					$groupMember->checked_in       = 1;
					$groupMember->checked_in_at    = $now;
					$groupMember->checked_out_at   = $nullDate;
					$groupMember->store();
				}

				$row->checked_in_count = $row->checked_in_count + 1;

				if ($row->checked_in_count >= $row->number_registrants)
				{
					$row->checked_in_count = $row->number_registrants;
					$row->checked_in       = 1;
					$row->checked_in_at    = $now;
					$row->checked_out_at   = $nullDate;
				}

				$row->store();
			}
		}
		elseif ($row->group_id > 0)
		{
			// Check in a group member record
			$row->checked_in       = 1;
			$row->checked_in_count = 1;
			$row->checked_in_at    = $now;
			$row->checked_out_at   = $nullDate;
			$row->store();

			// Get the group billing record
			/* @var EventbookingTableRegistrant $group */
			$group = $this->getTable();
			$group->load($row->group_id);
			$group->checked_in_count = $group->checked_in_count + 1;

			if ($group->checked_in_count >= $group->number_registrants)
			{
				$group->checked_in_count = $group->number_registrants;
				$group->checked_in       = 1;
				$group->checked_in_at    = $now;
				$group->checked_out_at   = $nullDate;
			}

			$group->store();
		}
		else
		{
			// Check-in individual registration record
			$row->checked_in       = 1;
			$row->checked_in_count = 1;
			$row->checked_in_at    = $now;
			$row->checked_out_at   = $nullDate;
			$row->store();
		}

		if ($row->published == 1)
		{
			return 2;
		}

		return 4;
	}


	/**
	 * Check-in a registration record
	 *
	 * @param   int   $id
	 * @param   bool  $group
	 *
	 * @return int
	 */
	public function checkin($id, $group = false)
	{
		/* @var EventbookingTableRegistrant $row */
		$row = $this->getTable();
		$row->load($id);

		if (empty($row))
		{
			return 0;
		}

		if ($row->checked_in)
		{
			return 1;
		}

		if ($group)
		{
			$row->checked_in_count = $row->number_registrants;
		}
		else
		{
			$row->checked_in_count = $row->checked_in_count + 1;
		}

		if ($row->checked_in_count == $row->number_registrants)
		{
			$row->checked_in     = 1;
			$row->checked_in_at  = Factory::getDate()->toSql();
			$row->checked_out_at = $this->getDbo()->getNullDate();
		}

		$row->store();

		Factory::getApplication()->triggerEvent('onAfterAdminCheckinRegistrant', [$row]);

		return 2;
	}

	/**
	 * Method to batch checkin registrants
	 *
	 * @param   array  $cid
	 *
	 * @return void
	 */
	public function batchCheckin($cid)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->update('#__eb_registrants')
			->set('checked_in = 1')
			->set('checked_in_at = ' . $db->quote(Factory::getDate()->toSql()))
			->where('id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$db->execute();

		Factory::getApplication()->triggerEvent('onAfterAdminBatchCheckinRegistrants', [$cid]);
	}

	/**
	 * Reset check-in status for the registration record
	 *
	 * @param $id
	 *
	 * @throws Exception
	 */
	public function resetCheckin($id)
	{
		/* @var EventbookingTableRegistrant $row */
		$row = $this->getTable();
		$row->load($id);

		if (!$row->load($id))
		{
			throw new Exception(Text::sprintf('Error checkin registration record %s', $id));
		}

		$nowDate = Factory::getDate()->toSql();

		$row->checked_in_count = 0;
		$row->checked_in       = 0;
		$row->checked_out_at   = $nowDate;

		$row->store();

		if ($row->is_group_billing)
		{
			// Uncheckin all group members
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->update('#__eb_registrants')
				->set('checked_in = 0')
				->set('checked_out_at = ' . $db->quote($nowDate))
				->set('checked_in_count = 0')
				->where('group_id = ' . $row->id);
			$db->setQuery($query);
			$db->execute();
		}
		elseif ($row->group_id > 0)
		{
			/* @var EventbookingTableRegistrant $group */
			$group = $this->getTable();
			$group->load($row->group_id);
			$group->checked_in_count = $group->checked_in_count - 1;

			if ($group->checked_in_count < 0)
			{
				$group->checked_in_count = 0;
			}

			$group->checked_in     = 0;
			$group->checked_out_at = $nowDate;
			$group->store();
		}
	}

	/**
	 * Store blank group members data when add a new registration record for ticket types with collect members
	 * information enabled
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   array                        $data
	 */
	private function storeGroupMembersForTicketTypes($row, $data)
	{
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$ticketTypes = EventbookingHelperData::getTicketTypes($row->event_id);

		foreach ($ticketTypes as $ticketType)
		{
			if (!empty($data['ticket_type_' . $ticketType->id]))
			{
				$quantity = (int) $data['ticket_type_' . $ticketType->id];

				for ($i = 0; $i < $quantity; $i++)
				{
					$rowMember                       = Table::getInstance('EventBooking', 'Registrant');
					$rowMember->group_id             = $row->id;
					$rowMember->transaction_id       = $row->transaction_id;
					$rowMember->ticket_qrcode        = EventbookingHelperRegistration::getTicketQRCode();
					$rowMember->event_id             = $row->event_id;
					$rowMember->payment_method       = $row->payment_method;
					$rowMember->payment_status       = $row->payment_status;
					$rowMember->user_id              = $row->user_id;
					$rowMember->register_date        = $row->register_date;
					$rowMember->user_ip              = $row->user_ip;
					$rowMember->registration_code    = EventbookingHelperRegistration::getRegistrationCode();
					$rowMember->total_amount         = $ticketType->price;
					$rowMember->discount_amount      = 0;
					$rowMember->late_fee             = 0;
					$rowMember->tax_amount           = 0;
					$rowMember->amount               = $ticketType->price;
					$rowMember->number_registrants   = 1;
					$rowMember->subscribe_newsletter = $row->subscribe_newsletter;
					$rowMember->agree_privacy_policy = 1;

					$rowMember->store();

					// Store registrant ticket type information
					$query->clear()
						->insert('#__eb_registrant_tickets')
						->columns('registrant_id, ticket_type_id, quantity')
						->values("$rowMember->id, $ticketType->id, 1");
					$db->setQuery($query)
						->execute();
				}
			}
		}
	}

	/**
	 * Store registrant tickets data when the record is created/updated in the backend
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   array                        $data
	 */
	private function storeRegistrantTickets($row, $data)
	{
		$user  = Factory::getUser();
		$event = EventbookingHelperDatabase::getEvent($row->event_id);

		if (!$event->has_multiple_ticket_types || !$user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			return;
		}

		$config = EventbookingHelper::getConfig();
		$db     = $this->getDbo();
		$query  = $db->getQuery(true)
			->delete('#__eb_registrant_tickets')
			->where('registrant_id = ' . $row->id);
		$db->setQuery($query)
			->execute();

		$ticketTypes       = EventbookingHelperData::getTicketTypes($row->event_id);
		$numberRegistrants = 0;

		foreach ($ticketTypes as $ticketType)
		{
			if (!empty($data['ticket_type_' . $ticketType->id]))
			{
				$quantity = (int) $data['ticket_type_' . $ticketType->id];
				$weight   = $ticketType->weight > 1 ? $ticketType->weight : 1;
				$query->clear()
					->insert('#__eb_registrant_tickets')
					->columns('registrant_id, ticket_type_id, quantity')
					->values("$row->id, $ticketType->id, $quantity");
				$db->setQuery($query)
					->execute();

				$numberRegistrants += $quantity * $weight;
			}
		}

		if ($config->calculate_number_registrants_base_on_tickets_quantity)
		{
			$query->clear('')
				->update('#__eb_registrants')
				->set('number_registrants = ' . $numberRegistrants)
				->where('id = ' . $row->id);
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Method to cancel the selected registrations
	 *
	 * @param   array  $cid
	 */
	public function cancelRegistrations($cid = [])
	{
		$app    = Factory::getApplication();
		$config = EventbookingHelper::getConfig();
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);

		// Import plugins
		foreach ($cid as $id)
		{
			/* @var EventbookingTableRegistrant $row */
			$row = $this->getTable();
			$row->load($id);

			if ($row->group_id > 0)
			{
				$app->enqueueMessage(Text::sprintf('Cancelling group member with ID %s is not supported. You need to cancel registration for whole group', $row->id));

				continue;
			}

			if ($row->published == 2)
			{
				// Already cancelled, do nothing
				continue;
			}

			$app->triggerEvent('onRegistrationCancel', [$row]);

			// Waiting List Cancel, change published to 4, Waiting List- Cancelled
			if ($row->published == 3)
			{
				$row->published = 4;
			}
			else
			{
				$row->published = 2;
			}

			$row->store();

			// Update status of group members record to cancelled as well
			if ($row->is_group_billing)
			{
				$query->clear()
					->update('#__eb_registrants')
					->set('published = ' . $row->published)
					->where('group_id = ' . (int) $row->id);
				$db->setQuery($query);
				$db->execute();
			}

			// Send registration cancelled email to registrant
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendRegistrationCancelledEmail', [$row, $config]);

			//Registration is cancelled, send notification emails to waiting list
			if ($config->activate_waitinglist_feature && $row->published == 2)
			{
				$event = EventbookingHelperDatabase::getEvent($row->event_id);

				if ($event->event_capacity > 0 && $event->event_capacity > $event->total_registrants)
				{
					EventbookingHelper::callOverridableHelperMethod('Mail', 'sendWaitingListNotificationEmail', [$row, $config]);
				}
			}
		}
	}

	/**
	 * Send batch emails to selected registrants
	 *
	 * @param   RADInput  $input
	 *
	 * @throws Exception
	 */
	public function batchMail($input)
	{
		$cid          = $input->get('cid', [], 'array');
		$emailSubject = $input->getString('subject');
		$emailMessage = $input->get('message', '', 'raw');
		$bccEmail     = $input->getString('bcc_email', '');

		if (empty($cid))
		{
			throw new Exception('Please select registrants to send mass mail');
		}

		if (empty($emailSubject))
		{
			throw new Exception('Please enter subject of the email');
		}

		if (empty($emailMessage))
		{
			throw new Exception('Please enter message ofthe email');
		}

		// OK, data is valid, process sending email
		$config = EventbookingHelper::getConfig();
		$mailer = EventbookingHelperMail::getMailer($config);
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);

		if (!empty($bccEmail))
		{
			$bccEmails = explode(',', $bccEmail);

			$bccEmails = array_map('trim', $bccEmails);

			foreach ($bccEmails as $bccEmail)
			{
				if (MailHelper::isEmailAddress($bccEmail))
				{
					$mailer->addBcc($bccEmail);
				}
			}
		}

		// Upload file
		$attachment = $input->files->get('attachment', null, 'raw');

		if ($attachment['name'])
		{
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
				$mailer->addAttachment($attachment['tmp_name'], $fileName);
			}
			else
			{
				throw new Exception(Text::sprintf('Attachment file type %s is not allowed', $fileExt));
			}
		}

		// Get list of registration records
		$query->select('a.*')
			->from('#__eb_registrants AS a')
			->where('a.id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Load frontend language file
		$defaultLanguage = EventbookingHelper::getDefaultLanguage();
		EventbookingHelper::loadComponentLanguage($defaultLanguage, true);
		$loadedLanguages = [$defaultLanguage];
		$loadedEvents    = [];

		if ($config->log_emails || in_array('mass_mails', explode(',', $config->get('log_email_types', ''))))
		{
			$logEmails = true;
		}
		else
		{
			$logEmails = false;
		}

		foreach ($rows as $row)
		{
			$subject = $emailSubject;
			$message = $emailMessage;
			$email   = $row->email;

			// If this is not valid email address, continue
			if (!MailHelper::isEmailAddress($email))
			{
				continue;
			}

			// Get registrant language
			if (!$row->language || $row->language == '*')
			{
				$language = $defaultLanguage;
			}
			else
			{
				$language = $row->language;
			}

			if (!in_array($language, $loadedLanguages))
			{
				EventbookingHelper::loadComponentLanguage($language, true);
				$loadedLanguages[] = $language;
			}

			if ($row->user_id > 0)
			{
				$userId = $row->user_id;
			}
			else
			{
				$userId = null;
			}

			if (!isset($loadedEvents[$language . '.' . $row->event_id]))
			{
				$query->clear()
					->select('*')
					->from('#__eb_events')
					->where('id = ' . $row->event_id);

				$fieldSuffix = EventbookingHelper::getFieldSuffix($language);

				if ($fieldSuffix)
				{
					EventbookingHelperDatabase::getMultilingualFields($query, ['title', 'short_description', 'description', 'price_text'], $fieldSuffix);
				}

				$db->setQuery($query);
				$event                                          = $db->loadObject();
				$loadedEvents[$language . '.' . $row->event_id] = $event;
			}
			else
			{
				$event = $loadedEvents[$language . '.' . $row->event_id];
			}

			if ($event->from_name && MailHelper::isEmailAddress($event->from_email))
			{
				$mailer->setSender([$event->from_email, $event->from_name]);

				$useEventSenderSetting = true;
			}
			else
			{
				$useEventSenderSetting = false;
			}

			$replaces                = EventbookingHelperRegistration::getRegistrationReplaces($row, $event, $row->user_id, $config->multiple_booking);
			$replaces['event_title'] = $event->title;

			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$subject = str_replace("[$key]", $value, $subject);
				$message = str_replace("[$key]", $value, $message);
			}

			$message = EventbookingHelperRegistration::processQRCODE($row, $message);
			$message = EventbookingHelper::convertImgTags($message);

			$message = EventbookingHelperHtml::loadCommonLayout('emailtemplates/tmpl/email.php', ['body' => $message, 'subject' => $subject]);

			$mailer->addRecipient($email);
			$mailer->setSubject($subject)
				->setBody($message)
				->Send();

			if ($logEmails)
			{
				$row             = Table::getInstance('Email', 'EventbookingTable');
				$row->sent_at    = Factory::getDate()->toSql();
				$row->email      = $email;
				$row->subject    = $subject;
				$row->body       = $message;
				$row->sent_to    = 2;
				$row->email_type = 'mass_mails';
				$row->store();
			}

			$mailer->clearAddresses();

			if ($useEventSenderSetting)
			{
				// Restore original sender
				$mailer->setSender([EventbookingHelperMail::$fromEmail, EventbookingHelperMail::$fromName]);
			}
		}
	}

	/**
	 * Refund a subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @throws Exception
	 */
	public function refund($row)
	{
		$method = EventbookingHelperPayments::getPaymentMethod($row->payment_method);

		$method->refund($row);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update('#__eb_registrants')
			->set('refunded = 1')
			->where('id = ' . $row->id);
		$db->setQuery($query)
			->execute();
	}
}
