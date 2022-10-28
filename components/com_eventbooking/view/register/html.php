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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class EventbookingViewRegisterHtml extends EventbookingViewRegisterBase
{
	use EventbookingViewCaptcha;

	/**
	 * Display interface to user
	 */
	public function display()
	{
		// Load common js code
		$document = Factory::getDocument();
		$config   = EventbookingHelper::getConfig();
		$rootUri  = Uri::root(true);

		if (EventbookingHelperRegistration::isEUVatTaxRulesEnabled())
		{
			$euVatNumberField = $config->eu_vat_number_field;
		}
		else
		{
			$euVatNumberField = '';
		}

		$document->addScriptDeclaration(
			'var siteUrl = "' . EventbookingHelper::getSiteUrl() . '";'
		);

		$document->addScriptOptions('euVatNumberField', $euVatNumberField)
			->addScriptOptions('ebSiteUrl', $rootUri . '/');

		HTMLHelper::_('behavior.core');
		Text::script('EB_INVALID_VATNUMBER', true);

		EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/assets/js/paymentmethods.min.js', ['version' => EventbookingHelper::getInstalledVersion()]);

		$customJSFile = JPATH_ROOT . '/media/com_eventbooking/assets/js/custom.js';

		if (file_exists($customJSFile) && filesize($customJSFile) > 0)
		{
			$document->addScript($rootUri . '/media/com_eventbooking/assets/js/custom.js');
		}

		EventbookingHelper::addLangLinkForAjax();

		$this->bootstrapHelper = EventbookingHelperBootstrap::getInstance();
		$layout                = $this->getLayout();

		if ($layout == 'cart')
		{
			$this->displayCart();

			return;
		}

		$input   = $this->input;
		$eventId = $input->getInt('event_id', 0);
		$event   = EventbookingHelperDatabase::getEvent($eventId);

		$canRegister = $this->validateRegistration($event, $config);

		if ($canRegister === false)
		{
			return;
		}

		$active = Factory::getApplication()->getMenu()->getActive();

		$pageTitle = '';

		// Try to get page title from menu item settings
		if ($active
			&& isset($active->query['view'], $active->query['event_id'])
			&& $active->query['view'] == 'register'
			&& $active->query['event_id'] == $event->id)
		{
			$params = $active->getParams();

			$pageTitle = $params->get('page_title');
		}

		// If page title not set from menu item parameter, use language item
		if (!$pageTitle && !$this->input->getInt('hmvc_call'))
		{
			$language = Factory::getLanguage();

			if (($layout == 'default' || $layout == '') && $language->hasKey('EB_INDIVIDUAL_REGISTRATION_PAGE_TITLE'))
			{
				$pageTitle = Text::_('EB_INDIVIDUAL_REGISTRATION_PAGE_TITLE');
			}
			elseif ($layout == 'group' && $language->hasKey('EB_GROUP_REGISTRATION_PAGE_TITLE'))
			{
				$pageTitle = Text::_('EB_GROUP_REGISTRATION_PAGE_TITLE');
			}
			else
			{
				$pageTitle = Text::_('EB_EVENT_REGISTRATION');
			}

			$pageTitle = str_replace('[EVENT_TITLE]', $event->title, $pageTitle);
			$pageTitle = str_replace('[EVENT_DATE]', HTMLHelper::_('date', $event->event_date, $config->event_date_format, null), $pageTitle);

			$document->setTitle($pageTitle);
		}

		// Breadcrumb
		$this->generateBreadcrumb($event, $layout);

		switch ($layout)
		{
			case 'group':
				EventbookingHelperPayments::writeJavascriptObjects();
				$this->displayGroupForm($event, $input);
				break;
			default:
				$this->displayIndividualRegistrationForm($event, $input);
				break;
		}
	}

	/**
	 * Display individual registration Form
	 *
	 * @param   EventbookingTableEvent  $event
	 * @param   RADInput                $input
	 *
	 * @throws Exception
	 */
	protected function displayIndividualRegistrationForm($event, $input)
	{
		$config  = EventbookingHelper::getConfig();
		$user    = Factory::getUser();
		$userId  = $user->get('id');
		$eventId = $event->id;

		if ($event->event_capacity > 0 && ($event->event_capacity <= $event->total_registrants))
		{
			$waitingList = true;
		}
		else
		{
			$waitingList = false;
		}

		$typeOfRegistration = EventbookingHelperRegistration::getTypeOfRegistration($event);

		$rowFields = EventbookingHelperRegistration::getFormFields($eventId, 0, null, $userId, $typeOfRegistration);

		$captchaInvalid = $input->getInt('captcha_invalid', 0);

		// Add support for deposit payment
		$paymentType = $input->post->getInt('payment_type', $config->get('default_payment_type', 0));

		if ($captchaInvalid)
		{
			$data = $input->post->getData();
		}
		else
		{
			$data = EventbookingHelperRegistration::getFormData($rowFields, $eventId, $userId);

			// IN case there is no data, get it from URL (get for example)
			if (empty($data))
			{
				$data = $input->getData();
			}
		}

		$data['payment_type'] = $paymentType;

		$this->setCommonViewData($config, $data, 'calculateIndividualRegistrationFee();');

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

		$data['use_field_default_value'] = $useDefault;

		$form->bind($data, $useDefault);
		$form->prepareFormFields('calculateIndividualRegistrationFee();');
		$paymentMethod = $input->post->getString('payment_method', EventbookingHelperPayments::getDefautPaymentMethod(trim($event->payment_methods)));

		if ($waitingList)
		{
			$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateIndividualRegistrationFees', [$event, $form, $data, $config, null], 'Helper');
		}
		else
		{
			$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateIndividualRegistrationFees', [$event, $form, $data, $config, $paymentMethod], 'Helper');
		}

		$methods = EventbookingHelperPayments::getPaymentMethods(trim($event->payment_methods));

		if (($event->enable_coupon == 0 && $config->enable_coupon) || in_array($event->enable_coupon, [1, 3]))
		{
			$enableCoupon = 1;

			if (!EventbookingHelperRegistration::isCouponAvailableForEvent($event, 0))
			{
				$enableCoupon = 0;
			}
		}
		else
		{
			$enableCoupon = 0;
		}

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


		if ($config->activate_deposit_feature && $event->deposit_amount > 0)
		{
			$depositPayment = 1;
		}
		else
		{
			$depositPayment = 0;
		}

		$this->loadCaptcha();

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

		$this->hasTicketTypesDiscount = false;

		if ($event->has_multiple_ticket_types)
		{
			$this->ticketTypes = EventbookingHelperData::getTicketTypes($event->id, true);

			foreach ($this->ticketTypes as $ticketType)
			{
				if (strlen(trim($ticketType->discount_rules)))
				{
					$this->hasTicketTypesDiscount = true;
					break;
				}
			}
		}

		$params                          = new Registry($event->params);
		$this->collectMembersInformation = $params->get('ticket_types_collect_members_information', 0);

		if (isset($fees['tickets_members']))
		{
			$this->ticketsMembers = $fees['tickets_members'];
		}

		// Assign these parameters
		$this->paymentMethod        = $paymentMethod;
		$this->config               = $config;
		$this->event                = $event;
		$this->methods              = $methods;
		$this->enableCoupon         = $enableCoupon;
		$this->userId               = $userId;
		$this->depositPayment       = $depositPayment;
		$this->paymentType          = $paymentType;
		$this->form                 = $form;
		$this->waitingList          = $waitingList;
		$this->showPaymentFee       = $showPaymentFee;
		$this->totalAmount          = $fees['total_amount'];
		$this->taxAmount            = $fees['tax_amount'];
		$this->discountAmount       = $fees['discount_amount'];
		$this->lateFee              = $fees['late_fee'];
		$this->depositAmount        = $fees['deposit_amount'];
		$this->amount               = $fees['amount'];
		$this->paymentProcessingFee = $fees['payment_processing_fee'];
		$this->discountRate         = $fees['discount_rate'];
		$this->bundleDiscountAmount = $fees['bundle_discount_amount'];
		$this->fees                 = $fees;

		parent::display();
	}

	/**
	 * Display Group Registration Form
	 *
	 * @param   object    $event
	 * @param   RADInput  $input
	 *
	 * @throws Exception
	 */
	protected function displayGroupForm($event, $input)
	{
		$config = EventbookingHelper::getConfig();
		$user   = Factory::getUser();
		$db     = Factory::getDbo();

		// Check to see whether we need to load ajax file upload script
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__eb_fields')
			->where('fieldtype = "File"')
			->where('published = 1')
			->where(' `access` IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');

		if ($config->custom_field_by_category)
		{
			$eventCategoriesQuery = $db->getQuery(true)
				->select('category_id')
				->from('#__eb_event_categories')
				->where('event_id = ' . $event->id);
			$db->setQuery($eventCategoriesQuery);
			$categoryIds = $db->loadColumn();
			$query->where('(category_id = -1 OR id IN (SELECT field_id FROM #__eb_field_categories WHERE category_id IN (' . implode(',', $categoryIds) . ')))');
		}
		else
		{
			$negEventId = -1 * $event->id;
			$subQuery   = $db->getQuery(true);
			$subQuery->select('field_id')
				->from('#__eb_field_events')
				->where("(event_id = $event->id OR (event_id < 0 AND event_id != $negEventId))");

			$query->where(' (event_id = -1 OR id IN (' . (string) $subQuery . '))');
		}

		$db->setQuery($query);
		$totalFileFields = $db->loadResult();

		if ($totalFileFields)
		{
			EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/assets/js/ajaxupload.min.js');
		}

		$this->event           = $event;
		$this->message         = EventbookingHelper::getMessages();
		$this->fieldSuffix     = EventbookingHelper::getFieldSuffix();
		$this->config          = $config;
		$this->captchaInvalid  = $input->get('captcha_invalid', 0);
		$this->showBillingStep = EventbookingHelperRegistration::showBillingStep($event->id);

		if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants))
		{
			$waitingList = true;
		}
		else
		{
			$waitingList = false;
		}

		$this->bypassNumberMembersStep = false;

		if ($this->event->collect_member_information === '')
		{
			$this->collectMemberInformation = $this->config->collect_member_information;
		}
		else
		{
			$this->collectMemberInformation = $this->event->collect_member_information;
		}

		if ($event->max_group_number > 0 && ($event->max_group_number == $event->min_group_number))
		{
			$session = Factory::getSession();
			$session->set('eb_number_registrants', $event->max_group_number);
			$this->bypassNumberMembersStep = true;
		}

		// This is needed here so that Stripe JS can be loaded using API
		$methods = EventbookingHelperPayments::getPaymentMethods(trim($event->payment_methods));

		$this->waitingList = $waitingList;

		$defaultStep = '';

		if ($this->captchaInvalid)
		{
			if ($this->showBillingStep)
			{
				$defaultStep = 'group_billing';
			}
			else
			{
				$defaultStep = 'group_members';
			}
		}
		elseif ($this->bypassNumberMembersStep)
		{
			if ($this->collectMemberInformation)
			{
				$defaultStep = 'group_members';
			}
			else
			{
				$defaultStep = 'group_billing';
			}
		}

		$this->defaultStep = $defaultStep;

		$this->loadCaptcha(true);

		EventbookingHelperJquery::colorbox('eb-colorbox-term');

		parent::display();
	}

	/**
	 * Display registration page in case shopping cart is enabled
	 *
	 * @throws Exception
	 */
	protected function displayCart()
	{
		$app    = Factory::getApplication();
		$config = EventbookingHelper::getConfig();
		$user   = Factory::getUser();
		$input  = $this->input;
		$userId = $user->get('id');
		$cart   = new EventbookingHelperCart();
		$items  = $cart->getItems();

		if (!count($items))
		{
			$active = Factory::getApplication()->getMenu()->getActive();

			if ($active
				&& isset($active->query['view'], $active->query['layout'])
				&& $active->query['view'] == 'register'
				&& $active->query['layout'] == 'cart')
			{
				$url = Uri::root();
			}
			else
			{
				$url = Route::_('index.php?option=com_eventbooking&Itemid=' . $input->getInt('Itemid', 0));
			}

			$app->enqueueMessage(Text::_('EB_NO_EVENTS_FOR_CHECKOUT'), 'warning');
			$app->redirect($url);
		}

		$eventId   = (int) $items[0];
		$rowFields = EventbookingHelperRegistration::getFormFields(0, 4);

		$captchaInvalid = $input->getInt('captcha_invalid', 0);
		$paymentType    = $input->post->getInt('payment_type', $config->get('default_payment_type', 0));

		if ($captchaInvalid)
		{
			$data = $input->post->getData();
		}
		else
		{
			$data = EventbookingHelperRegistration::getFormData($rowFields, $eventId, $userId);

			// IN case there is no data, get it from URL (get for example)
			if (empty($data))
			{
				$data = $input->getData();
			}
		}

		$data['payment_type'] = $paymentType;

		$this->setCommonViewData($config, $data);

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

		$form->bind($data, $useDefault);
		$form->prepareFormFields('calculateCartRegistrationFee();');
		$paymentMethod = $input->post->getString('payment_method', EventbookingHelperPayments::getDefautPaymentMethod());

		$fees = EventbookingHelper::callOverridableHelperMethod('Registration', 'calculateCartRegistrationFee', [$cart, $form, $data, $config, $paymentMethod, $useDefault], 'Helper');

		$events  = $cart->getEvents();
		$methods = EventbookingHelperPayments::getPaymentMethods();

		//Coupon will be enabled if there is at least one event has coupon enabled, same for deposit payment
		$enableCoupon  = 0;
		$enableDeposit = 0;
		$eventTitles   = [];

		foreach ($events as $event)
		{
			if (in_array($event->enable_coupon, [1, 2, 3]) || ($event->enable_coupon == 0 && $config->enable_coupon))
			{
				$enableCoupon = 1;
			}

			if ($event->deposit_amount > 0)
			{
				$enableDeposit = 1;
			}

			$eventTitles[] = $event->title;
		}


		if ($config->activate_deposit_feature && $enableDeposit)
		{
			$depositPayment = 1;
		}
		else
		{
			$depositPayment = 0;
		}

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

		// Load captcha
		$this->loadCaptcha();

		// Assign these parameters
		$this->paymentMethod        = $paymentMethod;
		$this->config               = $config;
		$this->methods              = $methods;
		$this->enableCoupon         = $enableCoupon;
		$this->userId               = $userId;
		$this->depositPayment       = $depositPayment;
		$this->form                 = $form;
		$this->items                = $events;
		$this->eventTitle           = implode(', ', $eventTitles);
		$this->form                 = $form;
		$this->showPaymentFee       = $showPaymentFee;
		$this->paymentType          = $paymentType;
		$this->formData             = $data;
		$this->useDefault           = $useDefault;
		$this->totalAmount          = $fees['total_amount'];
		$this->taxAmount            = $fees['tax_amount'];
		$this->discountAmount       = $fees['discount_amount'];
		$this->bunldeDiscount       = $fees['bundle_discount_amount'];
		$this->lateFee              = $fees['late_fee'];
		$this->depositAmount        = $fees['deposit_amount'];
		$this->paymentProcessingFee = $fees['payment_processing_fee'];
		$this->amount               = $fees['amount'];
		$this->fees                 = $fees;

		parent::display();
	}

	/**
	 * Generate Breadcrumb for event detail page, allow users to come back to event details
	 *
	 * @param   JTable  $event
	 * @param   string  $layout
	 */
	protected function generateBreadcrumb($event, $layout)
	{
		if ($this->input->getInt('hmvc_call'))
		{
			return;
		}

		$app      = Factory::getApplication();
		$active   = $app->getMenu()->getActive();
		$pathway  = $app->getPathway();
		$menuView = !empty($active->query['view']) ? $active->query['view'] : null;

		if (in_array($menuView, ['calendar', 'fullcalendar', 'categories', 'category', 'upcomingevents']))
		{
			$pathway->addItem($event->title, Route::_(EventbookingHelperRoute::getEventRoute($event->id, 0, $app->input->getInt('Itemid'))));
		}

		if ($layout == 'default')
		{
			$title = Text::_('EB_INDIVIDUAL_REGISTRATION');
			$title = str_replace('[EVENT_TITLE]', $event->title, $title);
			$pathway->addItem($title);
		}
		else
		{
			$title = Text::_('EB_GROUP_REGISTRATION');
			$title = str_replace('[EVENT_TITLE]', $event->title, $title);
			$pathway->addItem($title);
		}
	}

	/**
	 * Method to check and make sure registration is still possible with this even
	 *
	 * @param $event
	 * @param $config
	 */
	protected function validateRegistration($event, $config)
	{
		$app          = Factory::getApplication();
		$user         = Factory::getUser();
		$accessLevels = $user->getAuthorisedViewLevels();

		if (empty($event)
			|| !$event->published
			|| !in_array($event->access, $accessLevels)
			|| !in_array($event->registration_access, $accessLevels)
		)
		{
			if (!$user->id && $event && $event->published)
			{
				$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString())), Text::_('EB_LOGIN_TO_REGISTER'));
			}
			else
			{
				$app->enqueueMessage(Text::_('EB_ERROR_REGISTRATION'), 'error');

				if (!$this->input->getInt('hmvc_call'))
				{
					$app->redirect(Uri::root(), 403);
				}
				else
				{
					return false;
				}
			}
		}

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

			if ($waitingList)
			{
				// If even is not full, we are not in waiting list state
				if (!$event->event_capacity || $event->event_capacity > $event->total_registrants)
				{
					$waitingList = false;
				}
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
				$error = EventbookingHelperRegistration::getRegistrationErrorMessage($event);
				$app->enqueueMessage($error, 'error');

				if (!$this->input->getInt('hmvc_call'))
				{
					$app->redirect(Route::_(EventbookingHelperRoute::getEventRoute($event->id, $event->main_category_id), false));
				}
				else
				{
					return false;
				}
			}

			if ($waitingList && $user->id)
			{
				//Check to see whether the current user has registered for the event
				if ($event->prevent_duplicate_registration === '')
				{
					$preventDuplicateRegistration = $config->prevent_duplicate_registration;
				}
				else
				{
					$preventDuplicateRegistration = $event->prevent_duplicate_registration;
				}

				// Check to see if user joined waiting list before, if Yes, prevent them from joining waiting list again
				if ($preventDuplicateRegistration && $user->id > 0)
				{
					$db    = Factory::getDbo();
					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from('#__eb_registrants')
						->where('event_id = ' . $event->id)
						->where('group_id = 0')
						->where('(user_id = ' . $user->id . ' OR email = ' . $db->quote($user->email) . ')')
						->where('published = 3');
					$db->setQuery($query);
					$total = (int) $db->loadResult();

					// User joined waiting list, prevent them from doing that again
					if ($total)
					{
						$app->enqueueMessage(Text::_('EB_JOINED_WAITING_LIST_ALREADY'), 'error');

						if (!$this->input->getInt('hmvc_call'))
						{
							$app->redirect(Route::_(EventbookingHelperRoute::getEventRoute($event->id, $event->main_category_id), false));
						}
						else
						{
							return false;
						}
					}
				}
			}
		}

		if ($event->event_password)
		{
			$passwordPassed = Factory::getSession()->get('eb_passowrd_' . $event->id, 0);

			if (!$passwordPassed)
			{
				$return = base64_encode(Uri::getInstance()->toString());

				$app->redirect(Route::_('index.php?option=com_eventbooking&view=password&event_id=' . $event->id . '&return=' . $return . '&Itemid=' . $this->Itemid, false));
			}
		}
	}
}
