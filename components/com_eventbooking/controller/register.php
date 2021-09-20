<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

class EventbookingControllerRegister extends EventbookingController
{
	use EventbookingControllerCaptcha;

	/**
	 * Check the entered event password and make sure the entered password is valid
	 */
	public function check_event_password()
	{
		$password = $this->input->get('password', '', 'none');
		$eventId  = $this->input->getInt('event_id', 0);
		$return   = $this->input->getBase64('return');

		/* @var EventBookingModelRegister $model */
		$model   = $this->getModel('Register');
		$success = $model->checkPassword($eventId, $password);

		if ($success)
		{
			Factory::getSession()->set('eb_passowrd_' . $eventId, 1);
			$this->setRedirect(base64_decode($return));
		}
		else
		{
			// Redirect back to password view
			$Itemid = $this->input->getInt('Itemid');
			$url    = Route::_('index.php?option=com_eventbooking&view=password&event_id=' . $eventId . '&return=' . $return . '&Itemid=' . $Itemid, false);
			$this->setMessage(Text::_('EB_INVALID_EVENT_PASSWORD'), 'error');
			$this->setRedirect($url);
		}
	}

	/**
	 * Display individual registration form
	 *
	 * @throws Exception
	 */
	public function individual_registration()
	{
		$user    = Factory::getUser();
		$config  = EventbookingHelper::getConfig();
		$eventId = $this->input->getInt('event_id');

		if (!$eventId)
		{
			return;
		}

		$event = EventbookingHelperDatabase::getEvent($eventId);

		if (!$event)
		{
			return;
		}

		if ($event->event_password)
		{
			$passwordPassed = Factory::getSession()->get('eb_passowrd_' . $event->id, 0);

			if (!$passwordPassed)
			{
				$return = base64_encode(Uri::getInstance()->toString());
				Factory::getApplication()->redirect(Route::_('index.php?option=com_eventbooking&view=password&event_id=' . $event->id . '&return=' . $return . '&Itemid=' . $this->input->getInt('Itemid', 0), false));
			}
		}

		$typeOfRegistration = EventbookingHelperRegistration::getTypeOfRegistration($event);
		$rowFields          = EventbookingHelperRegistration::getFormFields($eventId, 0, null, $user->id, $typeOfRegistration);
		$hasFeeField        = false;

		foreach ($rowFields as $rowField)
		{
			if ($rowField->fee_field)
			{
				$hasFeeField = true;
				break;
			}
		}

		if ($config->simply_registration_process
			&& $event->individual_price == 0
			&& !$event->has_multiple_ticket_types
			&& !$hasFeeField
			&& $user->id)
		{
			if (!EventbookingHelper::callOverridableHelperMethod('Registration', 'acceptRegistration', [$event]))
			{
				if ($event->activate_waiting_list == 2)
				{
					$waitingList = $config->activate_waitinglist_feature;
				}
				else
				{
					$waitingList = $event->activate_waiting_list;
				}

				if ($event->registration_type == 3 || ($event->event_capacity > 0 && ($event->event_capacity > $event->total_registrants)))
				{
					$waitingList = false;
				}

				if ($event->cut_off_date != Factory::getDbo()->getNullDate())
				{
					$registrationOpen = ($event->cut_off_minutes < 0);
				}
				elseif (isset($event->event_start_minutes))
				{
					$registrationOpen = ($event->event_start_minutes < 0);
				}
				else
				{
					$registrationOpen = ($event->number_event_dates > 0);
				}

				if (!$waitingList || !$registrationOpen)
				{
					$app = Factory::getApplication();
					$app->enqueueMessage(Text::_('EB_ERROR_REGISTRATION'), 'error');
					$app->redirect(Route::_(EventbookingHelperRoute::getEventRoute($event->id, $event->main_category_id), false));
				}
			}

			$data = EventbookingHelperRegistration::getFormData($rowFields, $eventId, $user->id);
			$name = $user->name;
			$pos  = strpos($name, ' ');

			if ($pos !== false)
			{
				if (!isset($data['first_name']))
				{
					$data['first_name'] = substr($name, 0, $pos);
				}

				if (!isset($data['last_name']))
				{
					$data['last_name'] = substr($name, $pos + 1);
				}
			}
			else
			{
				if (!$data['first_name'])
				{
					$data['first_name'] = $name;
				}
			}

			$data['email']    = $user->email;
			$data['event_id'] = $eventId;

			/* @var EventbookingModelRegister $model */
			$model  = $this->getModel('Register');
			$return = $model->processIndividualRegistration($data);

			if ($return === 1)
			{
				// Redirect registrants to registration complete page
				$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=complete&Itemid=' . $this->input->getInt('Itemid'), false, false));
			}
			elseif ($return === 2)
			{
				// Redirect to waiting list complete page
				$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=waitinglist&Itemid=' . $this->input->getInt('Itemid'), false, false));
			}
		}
		else
		{
			$this->input->set('view', 'register');
			$this->input->set('layout', 'default');
			$this->display();
		}
	}

	/**
	 * Process individual registration
	 */
	public function process_individual_registration()
	{
		$app     = Factory::getApplication();
		$session = Factory::getSession();
		$user    = Factory::getUser();
		$config  = EventbookingHelper::getConfig();
		$input   = $this->input;
		$eventId = $input->getInt('event_id', 0);

		$event = EventbookingHelperDatabase::getEvent($eventId);

		if (!$event)
		{
			return;
		}

		$errors = [];

		// Validate captcha
		if (!$this->validateCaptcha($this->input))
		{
			$errors[] = Text::_('EB_INVALID_CAPTCHA_ENTERED');
		}

		// Validate username and password
		if (!$user->id && $config->user_registration)
		{
			$errors = array_merge($errors, EventbookingHelperRegistration::validateUsername($input->post->get('username', '', 'raw')));
			$errors = array_merge($errors, EventbookingHelperRegistration::validatePassword($input->post->get('password1', '', 'raw')));
		}

		// Validate email
		$result = $this->validateRegistrantEmail($eventId, $input->get('email', '', 'none'));

		if (!$result['success'])
		{
			$errors[] = $result['message'];
		}

		$data = $input->post->getData();

		if ($formErrors = $this->validateFormData($eventId, 0, $data))
		{
			$errors = array_merge($errors, $formErrors);
		}

		// Validate number slots left
		if ($event->activate_waiting_list == 2)
		{
			$waitingListEnabled = $config->activate_waitinglist_feature;
		}
		else
		{
			$waitingListEnabled = $event->activate_waiting_list;
		}

		if ($event->event_capacity && !$waitingListEnabled)
		{
			$numberRegistrantsAvailable = $event->event_capacity - $event->total_registrants - EventbookingHelperRegistration::countAwaitingPaymentRegistrations($event);

			if ($numberRegistrantsAvailable <= 0)
			{
				$errors[] = Text::_('EB_EVENT_IS_FULL');
			}
		}

		if (($event->event_capacity > 0)
			&& ($event->event_capacity <= $event->total_registrants)
			&& $waitingListEnabled
			&& $event->waiting_list_capacity > 0)
		{
			$numberWaitingListAvailable = $event->waiting_list_capacity - EventbookingHelperRegistration::countNumberWaitingList($event);

			if ($numberWaitingListAvailable <= 0)
			{
				$errors[] = Text::_('EB_EVENT_WAITING_LIST_IS_FULL');
			}
		}

		if ($event->has_multiple_ticket_types)
		{
			$errors = array_merge($errors, $this->validateTickets($event, $input, $data, $waitingListEnabled));
		}

		if (count($errors))
		{
			foreach ($errors as $error)
			{
				$app->enqueueMessage($error, 'error');
			}

			$fromArticle = $input->post->getInt('from_article', 0);

			if ($fromArticle)
			{
				$session->set('eb_form_data', serialize($data));
				$session->set('eb_catpcha_invalid', 1);
				$app->redirect($session->get('eb_artcile_url'));
			}
			else
			{
				$input->set('captcha_invalid', 1);
				$input->set('view', 'register');
				$input->set('layout', 'default');
				$this->display();
			}

			return;
		}

		$session->clear('eb_catpcha_invalid');

		/* @var EventBookingModelRegister $model */
		$model  = $this->getModel('Register');
		$return = $model->processIndividualRegistration($data);

		if ($return === 1)
		{
			// Redirect registrants to registration complete page
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=complete&registration_code=' . $data['registration_code'] . '&Itemid=' . $this->input->getInt('Itemid'), false));
		}
		elseif ($return === 2)
		{
			// Redirect to waiting list complete page
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=waitinglist&Itemid=' . $this->input->getInt('Itemid'), false, false));
		}
	}

	/**
	 * Store number of registrants and return form allow entering group members information
	 */
	public function store_number_registrants()
	{
		$config = EventbookingHelper::getConfig();
		Factory::getSession()->set('eb_number_registrants', $this->input->getInt('number_registrants'));

		$eventId = $this->input->getInt('event_id', 0);
		$event   = EventbookingHelperDatabase::getEvent($eventId);

		if ($event->collect_member_information === '')
		{
			$collectMemberInformation = $config->collect_member_information;
		}
		else
		{
			$collectMemberInformation = $event->collect_member_information;
		}

		if ($collectMemberInformation)
		{
			$this->input->set('view', 'register');
			$this->input->set('layout', 'group_members');
		}
		else
		{
			$this->input->set('view', 'register');
			$this->input->set('layout', 'group_billing');
		}

		$this->display();
	}

	/**
	 * Store group members data and display group billing form
	 */
	public function store_group_members_data()
	{
		$membersData = $this->input->post->getData();
		Factory::getSession()->set('eb_group_members_data', serialize($membersData));
		$eventId         = $this->input->getInt('event_id', 0);
		$showBillingStep = EventbookingHelperRegistration::showBillingStep($eventId);

		if (!$showBillingStep)
		{
			$this->process_group_registration(true);
		}
		else
		{
			$this->input->set('view', 'register');
			$this->input->set('layout', 'group_billing');
			$this->display();
		}
	}

	/**
	 * Store group members data and display group billing form
	 */
	public function validate_and_store_group_members_data()
	{
		$membersData = $this->input->post->getData();
		$session     = Factory::getSession();
		$session->set('eb_group_members_data', serialize($membersData));

		if (isset($membersData['number_registrants']) && $membersData['number_registrants'] > 0)
		{
			$numberRegistrants = (int) $membersData['number_registrants'];
		}
		else
		{
			$numberRegistrants = (int) $session->get('eb_number_registrants', '');
		}

		$eventId            = $this->input->getInt('event_id', 0);
		$event              = EventbookingHelperDatabase::getEvent($eventId);
		$typeOfRegistration = EventbookingHelperRegistration::getTypeOfRegistration($event);
		$memberFormFields   = EventbookingHelperRegistration::getFormFields($eventId, 2, null, null, $typeOfRegistration);

		$errors             = [];
		$response           = [];
		$response['status'] = 'OK';

		for ($i = 1; $i <= $numberRegistrants; $i++)
		{
			$fields     = EventbookingHelperRegistration::getGroupMemberFields($memberFormFields, $i);
			$memberForm = new RADForm($fields);
			$memberForm->setFieldSuffix($i);
			$memberForm->bind($membersData);
			$memberForm->buildFieldsDependency();

			$memberErrors = $memberForm->validate();

			if (count($memberErrors))
			{
				$errors = array_merge($errors, $memberErrors);
			}
		}

		if (count($errors))
		{
			$response['status'] = 'VALIDATION_ERROR';
			$response['errors'] = $errors;
		}
		else
		{
			ob_start();
			$this->input->set('view', 'register');
			$this->input->set('layout', 'group_billing');
			$this->display();
			$response['html'] = ob_get_clean();
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Store billing data and display members form when user click Back button on group registration process
	 */
	public function store_billing_data_and_display_group_members_form()
	{
		// Store billing data
		$billingData = $this->input->post->getData();
		Factory::getSession()->set('eb_group_billing_data', serialize($billingData));

		// Display Group Members form
		$this->input->set('view', 'register');
		$this->input->set('layout', 'group_members');
		$this->display();
	}

	/**
	 * Process group registration
	 */
	public function process_group_registration($bypassBilling = false)
	{
		$app     = Factory::getApplication();
		$session = Factory::getSession();
		$user    = Factory::getUser();
		$config  = EventbookingHelper::getConfig();
		$input   = $this->input;
		$eventId = $input->getInt('event_id');
		$event   = EventbookingHelperDatabase::getEvent($eventId);

		if (!$event)
		{
			return;
		}

		if ($event->collect_member_information === '')
		{
			$collectMemberInformation = $config->collect_member_information;
		}
		else
		{
			$collectMemberInformation = $event->collect_member_information;
		}

		$errors = [];

		if (!$this->validateCaptcha($this->input))
		{
			$errors[] = Text::_('EB_INVALID_CAPTCHA_ENTERED');
		}

		// Validate username and password
		if (!$user->id && $config->user_registration)
		{
			$errors = array_merge($errors, EventbookingHelperRegistration::validateUsername($input->post->get('username', '', 'raw')));
			$errors = array_merge($errors, EventbookingHelperRegistration::validatePassword($input->post->get('password1', '', 'raw')));
		}

		// Validate and make sure group members data is available
		if ($collectMemberInformation)
		{
			$membersData = $session->get('eb_group_members_data', null);

			if ($membersData)
			{
				$membersData = unserialize($membersData);
			}
			else
			{
				$membersData = [];
			}

			if (empty($membersData))
			{
				$errors[] = 'Sorry! Your registration cannot be saved due to session expiration';
			}
		}

		$data = $input->post->getData();

		if ($bypassBilling)
		{
			$membersData = $session->get('eb_group_members_data', null);

			if ($membersData)
			{
				$membersData = unserialize($membersData);
			}
			else
			{
				$membersData = [];
			}

			$memberFormFields = EventbookingHelperRegistration::getFormFields($eventId, 2);

			//Get data from first member
			$firstMemberForm = new RADForm($memberFormFields);
			$firstMemberForm->setFieldSuffix(1);
			$firstMemberForm->bind($membersData);
			$firstMemberForm->removeFieldSuffix();
			$data = array_merge($data, $firstMemberForm->getFormData());

			$input->set('email', $data['email']);
		}

		$result = $this->validateRegistrantEmail($eventId, $input->get('email', '', 'none'));

		if (!$result['success'])
		{
			$errors[] = $result['message'];
		}

		if ($formErrors = $this->validateFormData($eventId, $bypassBilling ? 2 : 1, $data))
		{
			$errors = array_merge($errors, $formErrors);
		}


		if ($input->getInt('number_registrants', 0) > 0)
		{
			$numberRegistrants = $input->getInt('number_registrants', 0);
		}
		else
		{
			$numberRegistrants = (int) $session->get('eb_number_registrants', '');
		}

		// Check to see if there is a valid number registrants
		if (!$numberRegistrants)
		{
			$errors[] = Text::_('Sorry, your session was expired. Please try again!');
		}

		// Validate to prevent over booking
		if ($event->event_capacity)
		{
			$numberRegistrantsAvailable = $event->event_capacity - $event->total_registrants - EventbookingHelperRegistration::countAwaitingPaymentRegistrations($event);

			// If there is space available, registration only possible if number registrants <= available places
			if ($numberRegistrantsAvailable > 0 && $numberRegistrantsAvailable < $numberRegistrants)
			{
				$errors[] = Text::sprintf('EB_NUMBER_REGISTRANTS_ERROR', $numberRegistrants, $numberRegistrantsAvailable);
			}

			if ($event->activate_waiting_list == 2)
			{
				$waitingListEnabled = $config->activate_waitinglist_feature;
			}
			else
			{
				$waitingListEnabled = $event->activate_waiting_list;
			}

			// If there is no space available, registration can only continue if waiting list is enabled
			if ($numberRegistrantsAvailable <= 0 && !$waitingListEnabled)
			{
				$errors[] = Text::sprintf('EB_NUMBER_REGISTRANTS_ERROR', $numberRegistrants, $numberRegistrantsAvailable);
			}

			if ($numberRegistrantsAvailable <= 0 && $waitingListEnabled && $event->waiting_list_capacity > 0)
			{
				$numberWaitingListAvailable = $event->waiting_list_capacity - EventbookingHelperRegistration::countNumberWaitingList($event);

				if ($numberWaitingListAvailable <= $numberRegistrants)
				{
					$errors[] = Text::sprintf('EB_NUMBER_WAITING_LIST_REGISTRANTS_ERROR', $numberRegistrants, $numberWaitingListAvailable);
				}
			}
		}

		if (count($errors))
		{
			foreach ($errors as $error)
			{
				$app->enqueueMessage($error, 'error');
			}

			$session->set('eb_group_billing_data', serialize($data));
			$input->set('captcha_invalid', 1);
			$input->set('view', 'register');
			$input->set('layout', 'group');
			$this->display();

			return;
		}

		/* @var EventBookingModelRegister $model */
		$model  = $this->getModel('Register');
		$return = $model->processGroupRegistration($data);

		if ($return === 1)
		{
			// Redirect registrants to registration complete page
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=complete&registration_code=' . $data['registration_code'] . '&Itemid=' . $this->input->getInt('Itemid'), false));
		}
		elseif ($return === 2)
		{
			// Redirect to waiting list complete page
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=waitinglist&Itemid=' . $this->input->getInt('Itemid'), false));
		}
	}

	/**
	 * Calculate registration fee, then update the information on registration form
	 */
	public function calculate_remainder_fee()
	{
		$row           = Table::getInstance('Registrant', 'EventbookingTable');
		$registrantId  = $this->input->getInt('registrant_id', 0);
		$paymentMethod = $this->input->getString('payment_method', '');

		if ($row->load($registrantId))
		{
			$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateRemainderFees', [$row, $paymentMethod]);
		}
		else
		{
			$fees = ['amount' => 0, 'payment_processing_fee' => 0, 'gross_amount' => 0];
		}

		$config                             = EventbookingHelper::getConfig();
		$response                           = [];
		$response['amount']                 = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($fees['payment_processing_fee'], $config);
		$response['gross_amount']           = EventbookingHelper::formatAmount($fees['gross_amount'], $config);

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Calculate registration fee, then update the information on registration form
	 */
	public function calculate_registration_fee()
	{
		$row           = Table::getInstance('Registrant', 'EventbookingTable');
		$registrantId  = $this->input->getInt('registrant_id', 0);
		$paymentMethod = $this->input->getString('payment_method', '');

		if ($row->load($registrantId))
		{
			$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateRegistrationFees', [$row, $paymentMethod]);
		}
		else
		{
			$fees = ['amount' => 0, 'payment_processing_fee' => 0, 'gross_amount' => 0];
		}

		$config                             = EventbookingHelper::getConfig();
		$response                           = [];
		$response['amount']                 = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($fees['payment_processing_fee'], $config);
		$response['gross_amount']           = EventbookingHelper::formatAmount($fees['gross_amount'], $config);

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Calculate registration fee, then update the information on registration form
	 */
	public function calculate_individual_registration_fee()
	{
		$config             = EventbookingHelper::getConfig();
		$eventId            = $this->input->getInt('event_id', 0);
		$data               = $this->input->post->getData();
		$paymentMethod      = $this->input->getString('payment_method', '');
		$event              = EventbookingHelperDatabase::getEvent($eventId);
		$typeOfRegistration = EventbookingHelperRegistration::getTypeOfRegistration($event);
		$rowFields          = EventbookingHelperRegistration::getFormFields($eventId, 0, null, null, $typeOfRegistration);
		$form               = new RADForm($rowFields);
		$form->bind($data);

		$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateIndividualRegistrationFees', [$event, $form, $data, $config, $paymentMethod], 'Helper');

		$response                           = [];
		$response['total_amount']           = EventbookingHelper::formatAmount($fees['total_amount'], $config);
		$response['discount_amount']        = EventbookingHelper::formatAmount($fees['discount_amount'], $config);
		$response['tax_amount']             = EventbookingHelper::formatAmount($fees['tax_amount'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($fees['payment_processing_fee'], $config);
		$response['amount']                 = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['deposit_amount']         = EventbookingHelper::formatAmount($fees['deposit_amount'], $config);
		$response['coupon_valid']           = $fees['coupon_valid'];
		$response['payment_amount']         = round($fees['amount'], 2);
		$response['vat_number_valid']       = $fees['vat_number_valid'];
		$response['show_vat_number_field']  = $fees['show_vat_number_field'];

		if (isset($fees['tickets_members']))
		{
			$response['tickets_members'] = $fees['tickets_members'];
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Calculate registration fee, then update information on group registration form
	 */
	public function calculate_group_registration_fee()
	{
		$config        = EventbookingHelper::getConfig();
		$eventId       = $this->input->getInt('event_id');
		$data          = $this->input->post->getData();
		$paymentMethod = $this->input->getString('payment_method', '');

		$event              = EventbookingHelperDatabase::getEvent($eventId);
		$typeOfRegistration = EventbookingHelperRegistration::getTypeOfRegistration($event);
		$rowFields          = EventbookingHelperRegistration::getFormFields($eventId, 1, null, null, $typeOfRegistration);
		$form               = new RADForm($rowFields);
		$form->bind($data);

		$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateGroupRegistrationFees', [$event, $form, $data, $config, $paymentMethod], 'Helper');

		$response                           = [];
		$response['total_amount']           = EventbookingHelper::formatAmount($fees['total_amount'], $config);
		$response['discount_amount']        = EventbookingHelper::formatAmount($fees['discount_amount'], $config);
		$response['tax_amount']             = EventbookingHelper::formatAmount($fees['tax_amount'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($fees['payment_processing_fee'], $config);
		$response['amount']                 = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['deposit_amount']         = EventbookingHelper::formatAmount($fees['deposit_amount'], $config);
		$response['coupon_valid']           = $fees['coupon_valid'];
		$response['payment_amount']         = round($fees['amount'], 2);
		$response['vat_number_valid']       = $fees['vat_number_valid'];
		$response['show_vat_number_field']  = $fees['show_vat_number_field'];

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Validate form data, make sure the required fields are entered
	 *
	 * @param   int    $eventId
	 * @param   int    $registrationType
	 * @param   array  $data
	 *
	 * @return array
	 */
	protected function validateFormData($eventId, $registrationType, $data)
	{
		$event              = EventbookingHelperDatabase::getEvent($eventId);
		$typeOfRegistration = EventbookingHelperRegistration::getTypeOfRegistration($event);
		$rowFields          = EventbookingHelperRegistration::getFormFields($eventId, $registrationType, null, null, $typeOfRegistration);

		$form = new RADForm($rowFields);
		$form->bind($data)
			->buildFieldsDependency();

		$errors = $form->validate();

		if ($registrationType == 0 && PluginHelper::isEnabled('eventbooking', 'updatetotalregistrants'))
		{
			$totalRegistrants = 0;

			foreach ($rowFields as $rowField)
			{
				if (strpos($rowField->name, 'number_') === 0 && !empty($data[$rowField->name]))
				{
					$totalRegistrants += (int) $data[$rowField->name];
				}
			}

			$event = EventbookingHelperDatabase::getEvent($eventId);

			if ($event->event_capacity > 0)
			{
				$numberRegistrantsAvailable = $event->event_capacity - $event->total_registrants;

				if ($numberRegistrantsAvailable > 0 && $numberRegistrantsAvailable < $totalRegistrants)
				{
					$errors[] = Text::sprintf('EB_NUMNER_REGISTRANTS_EXCEED_LIMIT', $numberRegistrantsAvailable, $totalRegistrants);
				}
			}
		}

		$config = EventbookingHelper::getConfig();

		// Validate privacy policy
		if ($config->show_privacy_policy_checkbox && empty($data['agree_privacy_policy']))
		{
			$errors[] = Text::_('EB_AGREE_PRIVACY_POLICY_ERROR');
		}

		return $errors;
	}

	/**
	 * Validate to see whether this email can be used to register for this event or not
	 *
	 * @param $eventId
	 * @param $email
	 *
	 * @return array
	 */
	protected function validateRegistrantEmail($eventId, $email)
	{
		$user   = Factory::getUser();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$result = [
			'success' => true,
			'message' => '',
		];

		if (!$config->multiple_booking)
		{
			$event = EventbookingHelperDatabase::getEvent($eventId);

			if ($event->prevent_duplicate_registration === '')
			{
				$preventDuplicateRegistration = $config->prevent_duplicate_registration;
			}
			else
			{
				$preventDuplicateRegistration = $event->prevent_duplicate_registration;
			}

			if ($preventDuplicateRegistration)
			{
				$eventIsFull                      = false;
				$numberAwaitingPaymentRegistrants = EventbookingHelperRegistration::countAwaitingPaymentRegistrations($event);

				if ($event->event_capacity && (($event->total_registrants + $numberAwaitingPaymentRegistrants) >= $event->event_capacity))
				{
					$eventIsFull = true;
				}


				$query->select('COUNT(id)')
					->from('#__eb_registrants')
					->where('event_id = ' . $eventId)
					->where('email = ' . $db->quote($email));

				// Check if user joined waiting list
				if ($eventIsFull)
				{
					$query->where('published = 3');
				}
				else
				{
					$query->where('(published = 1 OR (published = 0 AND payment_method LIKE "os_offline%"))');
				}

				$db->setQuery($query);
				$total = $db->loadResult();

				if ($total)
				{
					$result['success'] = false;
					$result['message'] = Text::_('EB_EMAIL_REGISTER_FOR_EVENT_ALREADY');
				}
			}
		}

		if ($result['success'] && $config->user_registration && !$user->id)
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__users')
				->where('email="' . $email . '"');
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($total)
			{
				$result['success'] = false;
				$result['message'] = Text::_('EB_EMAIL_USED_BY_DIFFERENT_USER');
			}
		}

		if ($result['success'])
		{
			$domains = ComponentHelper::getParams('com_users')->get('domains');

			if ($domains)
			{
				$emailDomain = explode('@', $email);
				$emailDomain = $emailDomain[1];
				$emailParts  = array_reverse(explode('.', $emailDomain));
				$emailCount  = count($emailParts);
				$allowed     = true;

				foreach ($domains as $domain)
				{
					$domainParts = array_reverse(explode('.', $domain->name));
					$status      = 0;

					// Don't run if the email has less segments than the rule.
					if ($emailCount < count($domainParts))
					{
						continue;
					}

					foreach ($emailParts as $key => $emailPart)
					{
						if (!isset($domainParts[$key]) || $domainParts[$key] == $emailPart || $domainParts[$key] == '*')
						{
							$status++;
						}
					}

					// All segments match, check whether to allow the domain or not.
					if ($status === $emailCount)
					{
						if ($domain->rule == 0)
						{
							$allowed = false;
						}
						else
						{
							$allowed = true;
						}
					}
				}

				// If domain is not allowed, fail validation. Otherwise continue.
				if (!$allowed)
				{
					$result['success'] = false;
					$result['message'] = Text::sprintf('JGLOBAL_EMAIL_DOMAIN_NOT_ALLOWED', $emailDomain);
				}
			}
		}

		return $result;
	}

	/**
	 * Validate tickets
	 *
	 * @param   EventbookingTableEvent  $event
	 * @param   RADInput                $input
	 * @param   array                   $data
	 * @param   bool                    $waitingListEnabled
	 *
	 * @return array
	 */
	protected function validateTickets($event, $input, &$data, $waitingListEnabled)
	{
		$errors                    = [];
		$params                    = new \Joomla\Registry\Registry($event->params);
		$collectMembersInformation = $params->get('ticket_types_collect_members_information', 0);
		$ticketTypes               = EventbookingHelperData::getTicketTypes($event->id, true);
		$ticketTypesValues         = explode(',', $data['ticket_type_values']);
		$ticketTypesValues         = array_filter($ticketTypesValues);
		$totalNumberTickets        = 0;

		foreach ($ticketTypesValues as $ticketValue)
		{
			$ticketInfo           = explode(':', $ticketValue);
			$data[$ticketInfo[0]] = $ticketInfo[1];
			$input->set($ticketInfo[0], $ticketInfo[1]);
			$input->post->set($ticketInfo[0], $ticketInfo[1]);
		}

		if ($collectMembersInformation)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($event->id, 2);
		}
		else
		{
			$rowFields = [];
		}

		$count = 0;

		// Validate ticket quantity before submitting
		$ticketTypeSelected = false;

		foreach ($ticketTypes as $ticketType)
		{
			if (!empty($data['ticket_type_' . $ticketType->id]))
			{
				$ticketTypeSelected = true;
				$weight             = (int) $ticketType->weight ?: 1;
				$totalNumberTickets += (int) $data['ticket_type_' . $ticketType->id] * $weight;
			}

			if (!empty($data['ticket_type_' . $ticketType->id]) && $ticketType->capacity > 0)
			{
				// Validate quantity
				if ($ticketType->capacity && !$waitingListEnabled)
				{
					$availableQuantity = $ticketType->capacity - $ticketType->registered;
				}
				elseif ($ticketType->max_tickets_per_booking)
				{
					$availableQuantity = $ticketType->max_tickets_per_booking;
				}
				else
				{
					// Hard code to max 10 tickets
					$availableQuantity = 10;
				}

				$quantity = $data['ticket_type_' . $ticketType->id];

				if ($availableQuantity < $quantity)
				{
					$errors[] = Text::sprintf('EB_TICKET_QUANTITY_INVALID_WARNING', $quantity, $ticketType->title, $availableQuantity);
				}
			}

			// Validate members data for ticket types
			if (!empty($data['ticket_type_' . $ticketType->id]) && $collectMembersInformation)
			{
				$quantity = (int) $data['ticket_type_' . $ticketType->id];

				for ($i = 0; $i < $quantity; $i++)
				{
					$count++;
					$memberForm = new RADForm($rowFields);
					$memberForm->setFieldSuffix($count);
					$memberForm->bind($data);
					$memberForm->buildFieldsDependency();
					$memberErrors = $memberForm->validate();

					if (count($memberErrors))
					{
						foreach ($memberErrors as $memberError)
						{
							$errors[] = Text::sprintf('EB_MEMBER_TICKET_VALIDATION_ERROR', $ticketType->title, $i + 1) . ' ' . $memberError;
						}
					}
				}
			}
		}

		if (!$ticketTypeSelected)
		{
			$errors[] = Text::_('EB_PLEASE_CHOOSE_TICKET_TYPE');
		}

		if ($event->event_capacity && ($event->event_capacity > $event->total_registrants))
		{
			$waitingListEnabled = false;
		}

		// Make sure total number of tickets is smaller than available places
		if (!$waitingListEnabled && $event->event_capacity && (($event->event_capacity - $event->total_registrants) < $totalNumberTickets))
		{
			$errors[] = Text::sprintf('EB_MAX_NUMBER_OF_TICKETS_CAN_PURCHASE', $event->event_capacity - $event->total_registrants);
		}


		return $errors;
	}
}
