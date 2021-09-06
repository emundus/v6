<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;

class EventbookingViewRegisterRaw extends EventbookingViewRegisterBase
{
	use EventbookingViewCaptcha;

	/**
	 * Display Group registration forms to user
	 *
	 * @return void
	 */
	public function display()
	{
		$this->bootstrapHelper = EventbookingHelperBootstrap::getInstance();
		$input                 = $this->input;
		$eventId               = $input->getInt('event_id', 0);
		$event                 = EventbookingHelperDatabase::getEvent($eventId);
		$layout                = $this->getLayout();

		switch ($layout)
		{
			case 'number_members':
				$this->displayNumberMembersForm($event, $input);
				break;
			case 'group_members':
				$this->displayGroupMembersForm($event);
				break;
			case 'group_billing':
				$this->displayGroupBillingForm($event, $input);
				break;
		}
	}

	/**
	 * Display form allow registrant to enter number of members for his group
	 *
	 * @param   Object    $event
	 * @param   RADInput  $input
	 */
	protected function displayNumberMembersForm($event, $input)
	{
		$session           = Factory::getSession();
		$config            = EventbookingHelper::getConfig();
		$numberRegistrants = $session->get('eb_number_registrants', '');

		if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants))
		{
			$waitingList = true;
		}
		else
		{
			$waitingList = false;
		}

		if ($waitingList)
		{
			if ($event->max_group_number)
			{
				$this->maxRegistrants = (int) $event->max_group_number;
			}
			else
			{
				// Hardcode max number of group members
				$this->maxRegistrants = 20;
			}
		}
		else
		{
			$this->maxRegistrants = EventbookingHelper::getMaxNumberRegistrants($event);
		}

		if ($event->min_group_number > 0)
		{
			$this->minNumberRegistrants = $event->min_group_number;
		}
		else
		{
			$this->minNumberRegistrants = (int) $config->get('default_min_number_registrants') ?: 2;
		}

		if ($event->collect_member_information === '')
		{
			$collectMemberInformation = $config->collect_member_information;
		}
		else
		{
			$collectMemberInformation = $event->collect_member_information;
		}

		$this->numberRegistrants        = $numberRegistrants;
		$this->collectMemberInformation = $collectMemberInformation;
		$this->message                  = EventbookingHelper::getMessages();
		$this->fieldSuffix              = EventbookingHelper::getFieldSuffix();
		$this->Itemid                   = $input->getInt('Itemid', 0);
		$this->event                    = $event;
		$this->config                   = $config;

		parent::display();
	}

	/**
	 * Display form allow registrant to enter information of group members
	 *
	 * @param $event
	 *
	 * @throws Exception
	 */
	public function displayGroupMembersForm($event)
	{
		$session           = Factory::getSession();
		$user              = Factory::getUser();
		$config            = EventbookingHelper::getConfig();
		$numberRegistrants = (int) $session->get('eb_number_registrants', '');

		$typeOfRegistration       = EventbookingHelperRegistration::getTypeOfRegistration($event);
		$rowFields                = EventbookingHelperRegistration::getFormFields($event->id, 2, null, null, $typeOfRegistration);
		$useDefaultValueForFields = true;

		//Get Group members form data
		$membersData = $session->get('eb_group_members_data', null);

		if ($membersData)
		{
			$membersData              = unserialize($membersData);
			$useDefaultValueForFields = false;
		}
		elseif ($user->id && $config->populate_group_members_data)
		{
			$membersData = $this->getMembersData($event->id, $rowFields, $numberRegistrants);
		}
		else
		{
			$membersData = [];
		}

		$this->showBillingStep = EventbookingHelperRegistration::showBillingStep($event->id);

		if (!$this->showBillingStep)
		{
			$this->loadCaptcha();
		}

		// Waiting List
		if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants))
		{
			$waitingList = true;
		}
		else
		{
			$waitingList = false;
		}

		$this->bypassNumberMembersStep = false;

		if ($event->max_group_number > 0 && ($event->max_group_number == $event->min_group_number))
		{
			$this->bypassNumberMembersStep = true;
		}

		$this->numberRegistrants        = $numberRegistrants;
		$this->membersData              = $membersData;
		$this->eventId                  = $event->id;
		$this->event                    = $event;
		$this->config                   = $config;
		$this->defaultCountry           = $config->default_country;
		$this->waitingList              = $waitingList;
		$this->useDefaultValueForFields = $useDefaultValueForFields;
		$this->rowFields                = $rowFields;

		parent::display();
	}

	/**
	 * Display billing form allow registrant enter billing information for group registration
	 *
	 * @param   object    $event
	 * @param   RADInput  $input
	 *
	 * @throws Exception
	 */
	public function displayGroupBillingForm($event, $input)
	{
		$session            = Factory::getSession();
		$user               = Factory::getUser();
		$userId             = $user->get('id');
		$config             = EventbookingHelper::getConfig();
		$eventId            = $event->id;
		$typeOfRegistration = EventbookingHelperRegistration::getTypeOfRegistration($event);
		$rowFields          = EventbookingHelperRegistration::getFormFields($eventId, 1, null, null, $typeOfRegistration);
		$groupBillingData   = $session->get('eb_group_billing_data', null);

		$numberRegistrants = $input->getInt('number_registrants', 0);

		// Add support for deposit payment
		$paymentType = $input->post->getInt('payment_type', $config->get('default_payment_type', 0));

		if (!$numberRegistrants)
		{
			$numberRegistrants = (int) $session->get('eb_number_registrants', '');
		}

		if ($groupBillingData)
		{
			$data           = unserialize($groupBillingData);
			$captchaInvalid = 1;
		}
		else
		{
			$captchaInvalid = 0;

			if ($config->auto_populate_billing_data)
			{
				// Get group members data
				$membersData = $session->get('eb_group_members_data', null);

				if ($membersData)
				{
					$membersData = unserialize($membersData);

					if ($config->auto_populate_billing_data == 'first_group_member')
					{
						$memberIndex = 1;
					}
					else
					{
						$memberIndex = (int) $session->get('eb_number_registrants', '');
					}

					foreach ($membersData as $key => $value)
					{
						$pos = strrpos($key, '_' . $memberIndex);

						if ($pos !== false)
						{
							$fieldName        = substr($key, 0, $pos);
							$data[$fieldName] = $membersData[$key];
						}
					}
				}
			}
			else
			{
				$data = EventbookingHelperRegistration::getFormData($rowFields, $eventId, $userId);
			}

			// IN case there is no data, get it from URL (get for example)
			if (empty($data))
			{
				$data = $input->getData();
			}
		}

		$data['payment_type'] = $paymentType;

		$this->setCommonViewData($config, $data, 'calculateGroupRegistrationFee();');

		// Waiting List
		if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants))
		{
			$waitingList = true;
		}
		else
		{
			$waitingList = false;
		}

		//Get data
		$form = new RADForm($rowFields);

		if ($captchaInvalid)
		{
			$useDefault = false;
		}
		else
		{
			$useDefault = true;
		}

		$data['number_registrants'] = $numberRegistrants;

		$form->bind($data, $useDefault);
		$form->prepareFormFields('calculateGroupRegistrationFee();');
		$paymentMethod = $input->post->getString('payment_method', EventbookingHelperPayments::getDefautPaymentMethod(trim($event->payment_methods)));

		if ($waitingList)
		{
			$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateGroupRegistrationFees', [$event, $form, $data, $config, null], 'Helper');
		}
		else
		{
			$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateGroupRegistrationFees', [$event, $form, $data, $config, $paymentMethod], 'Helper');
		}

		$methods = EventbookingHelperPayments::getPaymentMethods(trim($event->payment_methods));

		if (($event->enable_coupon == 0 && $config->enable_coupon) || in_array($event->enable_coupon, [2, 3]))
		{
			$enableCoupon = 1;

			if (!EventbookingHelperRegistration::isCouponAvailableForEvent($event, 1))
			{
				$enableCoupon = 0;
			}
		}
		else
		{
			$enableCoupon = 0;
		}


		if ($config->activate_deposit_feature && $event->deposit_amount > 0)
		{
			$depositPayment = 1;
		}
		else
		{
			$depositPayment = 0;
		}

		// Load captcha
		$this->loadCaptcha();

		// Check to see if there is payment processing fee or not
		$showPaymentFee = false;

		foreach ($methods as $method)
		{
			if ($method->paymentFee)
			{
				$showPaymentFee = true;
				break;
			}
		}

		$squareUpEnabled = false;

		foreach ($methods as $method)
		{
			if ($method->getName() == 'os_squareup')
			{
				$squareUpEnabled = true;
				break;
			}
		}


		// Reset some values if waiting list is activated
		if ($waitingList)
		{
			if (!$config->enable_coupon_for_waiting_list)
			{
				$enableCoupon = false;
			}

			$depositPayment = false;
			$paymentType    = false;
			$showPaymentFee = false;
		}
		else
		{
			$form->setEventId($eventId);
		}

		if ($event->collect_member_information === '')
		{
			$collectMemberInformation = $config->collect_member_information;
		}
		else
		{
			$collectMemberInformation = $event->collect_member_information;
		}

		// Assign these parameters
		$this->paymentMethod            = $paymentMethod;
		$this->config                   = $config;
		$this->event                    = $event;
		$this->methods                  = $methods;
		$this->enableCoupon             = $enableCoupon;
		$this->userId                   = $userId;
		$this->depositPayment           = $depositPayment;
		$this->paymentType              = $paymentType;
		$this->captchaInvalid           = $captchaInvalid;
		$this->form                     = $form;
		$this->squareUpEnabled          = $squareUpEnabled;
		$this->showPaymentFee           = $showPaymentFee;
		$this->waitingList              = $waitingList;
		$this->collectMemberInformation = $collectMemberInformation;
		$this->totalAmount              = $fees['total_amount'];
		$this->taxAmount                = $fees['tax_amount'];
		$this->discountAmount           = $fees['discount_amount'];
		$this->lateFee                  = $fees['late_fee'];
		$this->amount                   = $fees['amount'];
		$this->depositAmount            = $fees['deposit_amount'];
		$this->paymentProcessingFee     = $fees['payment_processing_fee'];
		$this->bundleDiscountAmount     = $fees['bundle_discount_amount'];
		$this->fees                     = $fees;
		$this->numberRegistrants        = $numberRegistrants;

		parent::display();
	}

	/**
	 * Get members data from previous registration to fill-in group members form
	 *
	 * @param   int    $eventId
	 * @param   array  $rowFields
	 * @param   int    $numberRegistrants
	 *
	 * @return array
	 */
	protected function getMembersData($eventId, $rowFields, $numberRegistrants = 0)
	{
		$membersData = [];

		$config = EventbookingHelper::getConfig();
		$user   = Factory::getUser();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);

		// First, try to get Members Data from previous registration of this event
		$query->select('id')
			->from('#__eb_registrants')
			->where('user_id = ' . $user->id)
			->where('event_id = ' . $eventId)
			->where('is_group_billing = 1')
			->where('(published = 1 OR payment_method LIKE "os_offline%")')
			->order('id DESC');

		if ($numberRegistrants > 0)
		{
			$query->where('number_registrants >= ' . $numberRegistrants);
		}

		$db->setQuery($query);
		$groupId = $db->loadResult();

		// If no registration found, get data from a different any event
		if (!$groupId)
		{
			$query->clear()
				->select('id')
				->from('#__eb_registrants')
				->where('user_id = ' . $user->id)
				->where('is_group_billing = 1')
				->where('(published = 1 OR payment_method LIKE "os_offline%")')
				->order('id DESC');

			if ($numberRegistrants > 0)
			{
				$query->where('number_registrants >= ' . $numberRegistrants);
			}

			$db->setQuery($query);
			$groupId = $db->loadResult();
		}

		if ($groupId)
		{
			$query->clear()
				->select('*')
				->from('#__eb_registrants')
				->where('group_id = ' . $groupId);
			$db->setQuery($query);
			$rowMembers = $db->loadObjectList();

			for ($i = 0, $n = count($rowMembers); $i < $n; $i++)
			{
				$rowMember  = $rowMembers[$i];
				$memberData = EventbookingHelperRegistration::getRegistrantData($rowMember, $rowFields);

				foreach ($memberData as $key => $value)
				{
					$index                            = $i + 1;
					$membersData[$key . '_' . $index] = $value;
				}
			}
		}
		else
		{
			// User current user profile data to fill-in for first group member
			$data = EventbookingHelperRegistration::getFormData($rowFields, $eventId, $user->id);

			if ($user->id && !isset($data['first_name']))
			{
				//Load the name from Joomla default name
				$name = $user->name;

				if ($name)
				{
					$pos = strpos($name, ' ');

					if ($pos !== false)
					{
						$data['first_name'] = substr($name, 0, $pos);
						$data['last_name']  = substr($name, $pos + 1);
					}
					else
					{
						$data['first_name'] = $name;
						$data['last_name']  = '';
					}
				}
			}

			if ($user->id && !isset($data['email']))
			{
				$data['email'] = $user->email;
			}

			if (empty($data['country']))
			{
				$data['country'] = $config->default_country;
			}

			foreach ($data as $key => $value)
			{
				$membersData[$key . '_1'] = $value;
			}
		}

		return $membersData;
	}
}