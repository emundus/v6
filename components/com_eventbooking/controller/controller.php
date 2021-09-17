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
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class EventbookingController extends RADController
{
	use EventbookingControllerDisplay, RADControllerDownload;

	/**
	 * Display information
	 */
	public function display($cachable = false, array $urlparams = [])
	{
		$this->loadAssets();

		$task = $this->getTask();

		switch ($task)
		{
			case 'view_category':
				$this->input->set('view', 'category');
				break;
			case 'group_registration':
				$this->input->set('view', 'register');
				$this->input->set('layout', 'group');
				break;
			case 'cancel':
				$this->input->set('view', 'cancel');
				$this->input->set('layout', 'default');
				break;
			case 'cancel_registration_confirm':
				$this->input->set('view', 'registrationcancel');
				$this->input->set('layout', 'confirmation');
				break;
			#Cart function
			case 'view_cart':
				$this->input->set('view', 'cart');
				$this->input->set('layout', 'default');
				break;
			case 'view_checkout':
			case 'checkout':
				$this->input->set('view', 'register');
				$this->input->set('layout', 'cart');
				break;
			default:
				$view = $this->input->getCmd('view');

				if (!$view)
				{
					$this->input->set('view', 'categories');
					$this->input->set('layout', 'default');
				}

				break;
		}

		parent::display($cachable, $urlparams);
	}

	/**
	 * Process download a registant uploaded file
	 */
	public function download_file()
	{
		$fileName = basename($this->input->getString('file_name'));
		$filePath = JPATH_ROOT . '/media/com_eventbooking/files' . '/' . $fileName;

		if (file_exists($filePath))
		{
			// Check permission
			$canDownload = false;
			$user        = Factory::getUser();

			if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
			{
				// Users with registrants management is allowed to download file
				$canDownload = true;
			}
			elseif ($user->id)
			{
				// User can only download the file uploaded by himself
				$db = Factory::getDbo();

				// Get list of published file upload custom fields
				$query = $db->getQuery(true)
					->select('id')
					->from('#__eb_fields')
					->where('fieldtype = "File"');
				$db->setQuery($query);
				$fieldIds = $db->loadColumn();

				if (count($fieldIds))
				{
					$query->clear()
						->select('COUNT(*)')
						->from('#__eb_registrants AS a')
						->innerJoin('#__eb_field_values AS b ON a.id = b.registrant_id')
						->where('a.user_id = ' . $user->id)
						->where('b.field_id IN (' . implode(',', $fieldIds) . ')')
						->where('b.field_value = ' . $db->quote($fileName));
					$db->setQuery($query);
					$total = (int) $db->loadResult();

					if ($total)
					{
						$canDownload = true;
					}
				}
			}

			if (!$canDownload)
			{
				$this->app->enqueueMessage(Text::_('You do not have permission to download this file'), 'error');
				$this->app->redirect(Uri::root(), 403);

				return;
			}

			$this->processDownloadFile($filePath);
		}
		else
		{
			$this->app->enqueueMessage(Text::_('File does not exist'), 'error');
			$this->app->redirect(Uri::root(), 404);
		}
	}

	/***
	 * Get search parameters from search module and performing redirect
	 */
	public function search()
	{
		$categoryId     = $this->input->getInt('category_id', 0);
		$locationId     = $this->input->getInt('location_id', 0);
		$Itemid         = $this->input->getInt('Itemid', 0);
		$search         = $this->input->getString('search', '');
		$layout         = $this->input->getCmd('layout', '');
		$fromDate       = $this->input->getString('filter_from_date');
		$toDate         = $this->input->getString('filter_to_date');
		$filterAddress  = $this->input->getString('filter_address');
		$filterState    = $this->input->getString('filter_state');
		$filterDistance = $this->input->getInt('filter_distance');

		$url = 'index.php?option=com_eventbooking&view=search';

		if ($categoryId)
		{
			$url .= '&category_id=' . $categoryId;
		}

		if ($locationId)
		{
			$url .= '&location_id=' . $locationId;
		}

		if ($search)
		{
			$url .= '&search=' . $search;
		}

		if ($filterState)
		{
			$url .= '&filter_state=' . $filterState;
		}

		if ($fromDate)
		{
			$url .= '&filter_from_date=' . $fromDate;
		}

		if ($toDate)
		{
			$url .= '&filter_to_date=' . $toDate;
		}

		if ($filterAddress)
		{
			$url .= '&filter_address=' . $filterAddress;
		}

		if ($filterDistance)
		{
			$url .= '&filter_distance=' . $filterDistance;
		}

		if ($layout && ($layout != 'default'))
		{
			$url .= '&layout=' . $layout;
		}

		$url .= '&Itemid=' . $Itemid;

		$this->app->redirect(Route::_($url, false, 0));
	}

	/**
	 * Validate the username, make sure it has not been registered by someone else
	 */
	public function validate_username()
	{
		$db         = Factory::getDbo();
		$query      = $db->getQuery(true);
		$username   = $this->input->getUsername('fieldValue', '');
		$validateId = $this->input->get('fieldId', '', 'none');

		$query->select('COUNT(*)')
			->from('#__users')
			->where('username=' . $db->quote($username));
		$db->setQuery($query);
		$total        = $db->loadResult();
		$arrayToJs    = [];
		$arrayToJs[0] = $validateId;

		if ($total)
		{
			$arrayToJs[1] = false;
		}
		else
		{
			$arrayToJs[1] = true;
		}

		echo json_encode($arrayToJs);

		$this->app->close();
	}

	/**
	 * Validate the email
	 */
	public function validate_email()
	{
		$db           = Factory::getDbo();
		$user         = Factory::getUser();
		$config       = EventbookingHelper::getConfig();
		$query        = $db->getQuery(true);
		$email        = $this->input->get('fieldValue', '', 'string');
		$eventId      = $this->input->getInt('event_id', 0);
		$validateId   = $this->input->get('fieldId', '', 'none');
		$arrayToJs    = [];
		$arrayToJs[0] = $validateId;

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
				$query->select('COUNT(id)')
					->from('#__eb_registrants')
					->where('event_id = ' . $eventId)
					->where('email = ' . $db->quote($email))
					->where('(published = 1 OR (published = 0 AND payment_method LIKE "os_offline%"))');
				$db->setQuery($query);
				$total = $db->loadResult();

				if ($total)
				{
					$arrayToJs[1] = false;
					$arrayToJs[2] = Text::_('EB_EMAIL_REGISTER_FOR_EVENT_ALREADY');
				}
			}
		}

		if (!isset($arrayToJs[1]))
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__users')
				->where('email = ' . $db->quote($email));
			$db->setQuery($query);
			$total = $db->loadResult();

			if (!$total || $user->id || !$config->user_registration)
			{
				$arrayToJs[1] = true;
			}
			else
			{
				$arrayToJs[1] = false;
				$arrayToJs[2] = Text::_('EB_EMAIL_USED_BY_OTHER_CUSTOMER');
			}
		}

		if (!isset($arrayToJs[1]))
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
					$arrayToJs[1] = false;
					$arrayToJs[2] = Text::sprintf('JGLOBAL_EMAIL_DOMAIN_NOT_ALLOWED', $emailDomain);
				}
			}
		}

		echo json_encode($arrayToJs);

		$this->app->close();
	}

	/**
	 * Get list of states for the selected country, using in AJAX request
	 */
	public function get_states()
	{
		$countryName = $this->input->getString('country_name', '');
		$fieldName   = $this->input->getString('field_name', 'state');
		$stateName   = $this->input->getString('state_name', '');

		if (!$countryName)
		{
			$config      = EventbookingHelper::getConfig();
			$countryName = $config->default_country;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('required')
			->from('#__eb_fields')
			->where('name = ' . $db->quote('state'));
		$db->setQuery($query);
		$required = $db->loadResult();
		$class    = $required ? 'validate[required]' : '';

		//get states
		$query->clear()
			->select('state_name AS value, state_name AS text')
			->from('#__eb_states AS a')
			->innerJoin('#__eb_countries AS b ON a.country_id = b.country_id')
			->where('b.name = ' . $db->quote($countryName))
			->order('state_name');
		$db->setQuery($query);
		$states  = $db->loadObjectList();
		$options = [];

		if (count($states))
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT_STATE'));
			$options   = array_merge($options, $states);
		}
		else
		{
			$options[] = HTMLHelper::_('select.option', 'N/A', Text::_('EB_NA'));
		}

		if (EventbookingHelper::isJoomla4())
		{
			echo HTMLHelper::_('select.genericlist', $options, $fieldName, ' class="uk-select form-select ' . $class . '" id="' . $fieldName . '"', 'value', 'text',
				$stateName);
		}
		else
		{
			echo HTMLHelper::_('select.genericlist', $options, $fieldName, ' class="uk-select input-large form-select ' . $class . '" id="' . $fieldName . '"', 'value', 'text',
				$stateName);
		}

		$this->app->close();
	}

	/**
	 * Get depend fields status
	 */
	public function get_depend_fields_status()
	{
		$input          = $this->input;
		$db             = Factory::getDbo();
		$query          = $db->getQuery(true);
		$fieldId        = $input->getInt('field_id', 0);
		$fieldSuffix    = $input->getString('field_suffix', '');
		$languageSuffix = EventbookingHelper::getFieldSuffix();

		//Get list of depend fields
		$allFieldIds = EventbookingHelper::getAllDependencyFields($fieldId);

		$query->select('*')
			->from('#__eb_fields')
			->where('published=1')
			->where('id IN (' . implode(',', $allFieldIds) . ')')
			->order('ordering');

		if ($languageSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, ['title', 'depend_on_options'], $languageSuffix);
		}

		$db->setQuery($query);
		$rowFields    = $db->loadObjectList();
		$masterFields = [];
		$fieldsAssoc  = [];

		foreach ($rowFields as $rowField)
		{
			if ($rowField->depend_on_field_id)
			{
				$masterFields[] = $rowField->depend_on_field_id;
			}

			$fieldsAssoc[$rowField->id] = $rowField;
		}

		$masterFields = array_unique($masterFields);

		if (count($masterFields))
		{
			$hiddenFields = [];

			foreach ($rowFields as $rowField)
			{
				if ($rowField->depend_on_field_id && isset($fieldsAssoc[$rowField->depend_on_field_id]))
				{
					// If master field is hided, then children field should be hided, too
					if (in_array($rowField->depend_on_field_id, $hiddenFields))
					{
						$hiddenFields[] = $rowField->id;
					}
					else
					{
						if ($fieldSuffix)
						{
							$fieldName = $fieldsAssoc[$rowField->depend_on_field_id]->name . '_' . $fieldSuffix;
						}
						else
						{
							$fieldName = $fieldsAssoc[$rowField->depend_on_field_id]->name;
						}

						$masterFieldValues = $input->get($fieldName, '', 'none');

						if (is_array($masterFieldValues))
						{
							$selectedOptions = $masterFieldValues;
						}
						else
						{
							$selectedOptions = [$masterFieldValues];
						}

						$dependOnOptions = json_decode($rowField->depend_on_options);

						if (!count(array_intersect($selectedOptions, $dependOnOptions)))
						{
							$hiddenFields[] = $rowField->id;
						}
					}
				}
			}
		}

		$showFields = [];
		$hideFields = [];

		foreach ($rowFields as $rowField)
		{
			if (in_array($rowField->id, $hiddenFields))
			{
				$hideFields[] = 'field_' . $rowField->name . ($fieldSuffix ? '_' . $fieldSuffix : '');
			}
			else
			{
				$showFields[] = 'field_' . $rowField->name . ($fieldSuffix ? '_' . $fieldSuffix : '');
			}
		}

		echo json_encode(['show_fields' => implode(',', $showFields), 'hide_fields' => implode(',', $hideFields)]);

		$this->app->close();
	}

	/**
	 * Confirm the payment. Used for Paypal base payment gateway
	 */
	public function payment_confirm()
	{
		/* @var EventBookingModelRegister $model */
		$model         = $this->getModel('Register');
		$paymentMethod = $this->input->getString('payment_method');
		$model->paymentConfirm($paymentMethod);
	}

	/**
	 * Process upload file
	 */
	public function upload_file()
	{
		$config     = EventbookingHelper::getConfig();
		$json       = [];
		$pathUpload = JPATH_ROOT . '/media/com_eventbooking/files';

		if (!Folder::exists($pathUpload))
		{
			Folder::create($pathUpload);
		}

		$allowedExtensions = $config->attachment_file_types;

		if (!$allowedExtensions)
		{
			$allowedExtensions = 'doc|docx|ppt|pptx|pdf|zip|rar|bmp|gif|jpg|jepg|png|swf|zipx';
		}

		$allowedExtensions = explode('|', $allowedExtensions);
		$allowedExtensions = array_map('trim', $allowedExtensions);

		$file     = $this->input->files->get('file', [], 'raw');
		$fileName = $file['name'];
		$fileExt  = File::getExt($fileName);

		if (in_array(strtolower($fileExt), $allowedExtensions))
		{
			$canUpload = true;

			if ($config->upload_max_file_size > 0)
			{
				$maxFileSizeInByte = $config->upload_max_file_size * 1024 * 1024;

				if ($file['size'] > $maxFileSizeInByte)
				{
					$json['error'] = Text::sprintf('EB_FILE_SIZE_TOO_LARGE', $config->upload_max_file_size . 'MB');
					$canUpload     = false;
				}
			}

			if ($canUpload)
			{
				$fileName = File::makeSafe($fileName);

				if (File::exists($pathUpload . '/' . $fileName))
				{
					$targetFileName = time() . '_' . $fileName;
				}
				else
				{
					$targetFileName = $fileName;
				}

				File::upload($file['tmp_name'], $pathUpload . '/' . $targetFileName, false, true);

				$json['success'] = Text::sprintf('EB_FILE_UPLOADED', $fileName);
				$json['file']    = $targetFileName;
			}
		}
		else
		{
			$json['error'] = Text::sprintf('EB_FILE_NOT_ALLOWED', $fileExt, implode(', ', $allowedExtensions));
		}

		echo json_encode($json);

		$this->app->close();
	}

	/**
	 * Get profile data of the registrant, return JSON format using for ajax request
	 */
	public function get_profile_data()
	{
		$input   = Factory::getApplication()->input;
		$userId  = $input->getInt('user_id', 0);
		$eventId = $input->getInt('event_id');
		$data    = [];

		if ($userId && $eventId)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($eventId, 0);
			$data      = EventbookingHelperRegistration::getFormData($rowFields, $eventId, $userId);
		}

		if ($userId && !isset($data['first_name']))
		{
			//Load the name from Joomla default name
			$user = Factory::getUser($userId);
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

		if ($userId && !isset($data['email']))
		{
			if (empty($user))
			{
				$user = Factory::getUser($userId);
			}

			$data['email'] = $user->email;
		}

		echo json_encode($data);

		$this->app->close();
	}

	/**
	 * Override getView method to support getting layout from themes
	 *
	 * @param   string  $name
	 * @param   string  $type
	 * @param   string  $layout
	 * @param   array   $config
	 *
	 * @return RADView
	 */
	public function getView($name, $type = 'html', $layout = 'default', array $config = [])
	{
		$theme = EventbookingHelper::getDefaultTheme();

		$paths   = [];
		$paths[] = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/com_eventbooking/' . $name;
		$paths[] = JPATH_ROOT . '/components/com_eventbooking/themes/' . $theme->name . '/' . $name;

		if ($theme->name != 'default')
		{
			$paths[] = JPATH_ROOT . '/components/com_eventbooking/themes/default/' . $name;
		}

		$config['paths'] = $paths;

		return parent::getView($name, $type, $layout, $config);
	}
}
