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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

class EventbookingControllerRegistrant extends RADControllerAdmin
{
	use EventbookingControllerDisplay, RADControllerDownload;

	public function display($cachable = false, array $urlparams = [])
	{
		$this->loadAssets();

		// Check and make sure user is allowed to access to registrant edit page.
		$view = $this->input->getCmd('view', 'registrant');

		switch ($view)
		{
			case 'registrant':
				$id = $this->input->getInt('id');

				if (!$this->allowSave(['id' => $id]))
				{
					$this->app->enqueueMessage(Text::_('NOT_AUTHORIZED'), 'error');
					$this->app->redirect(Uri::root(), 403);
				}
				break;
		}

		parent::display($cachable, $urlparams);
	}

	/**
	 * Save the registration record and back to registration record list
	 */
	public function save()
	{
		parent::save();

		if ($return = $this->input->getBase64('return', ''))
		{
			$this->setRedirect(base64_decode($return));
		}
		else
		{
			$this->setRedirect($this->getViewListUrl());
		}
	}

	/**
	 * Delete the selected registration record
	 */
	public function delete()
	{
		parent::delete();

		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Cancel registration for the event
	 */
	public function cancel()
	{
		$db               = Factory::getDbo();
		$query            = $db->getQuery(true);
		$user             = Factory::getUser();
		$config           = EventbookingHelper::getConfig();
		$id               = $this->input->getInt('id', 0);
		$registrationCode = $this->input->getString('cancel_code', '');
		$fieldSuffix      = EventbookingHelper::getFieldSuffix();

		$language = Factory::getLanguage()->getTag();

		if (Multilanguage::isEnabled() && $config->get('default_menu_item_' . $language))
		{
			$redirectUrl = Route::_('index.php?option=com_eventbooking&Itemid=' . $config->get('default_menu_item_' . $language));
		}
		else if ($config->get('default_menu_item') > 0)
		{
			$redirectUrl = Route::_('index.php?option=com_eventbooking&Itemid=' . $config->get('default_menu_item'));
		}
		else
		{
			$redirectUrl = Uri::root();
		}

		$query->select('a.id, a.event_date, a.cancel_before_date, b.user_id, b.id AS registrant_id')
			->select($db->quoteName('a.title' . $fieldSuffix, 'title'))
			->from('#__eb_events AS a')
			->innerJoin('#__eb_registrants AS b ON a.id = b.event_id');

		if ($id)
		{
			$query->where('b.id = ' . $id);
		}
		else
		{
			$query->where('b.registration_code = ' . $db->quote($registrationCode));
		}

		$db->setQuery($query);
		$rowEvent = $db->loadObject();

		if (!$rowEvent)
		{
			$this->app->enqueueMessage(Text::_('EB_INVALID_ACTION'), 'warning');
			$this->app->redirect($redirectUrl, 404);
		}

		if (($user->id == 0 && !$registrationCode) || ($user->id && ($user->id != $rowEvent->user_id)))
		{
			$this->app->enqueueMessage(Text::_('EB_INVALID_ACTION'), 'warning');
			$this->app->redirect($redirectUrl, 404);
		}

		// Validate cancel before date
		if (!EventbookingHelperRegistration::canCancelRegistrationNow($rowEvent))
		{
			if ($rowEvent->cancel_before_date !== Factory::getDbo()->getNullDate())
			{
				$cancelBeforeDate = Factory::getDate($rowEvent->cancel_before_date, $this->app->get('offset'));
			}
			else
			{
				$cancelBeforeDate = Factory::getDate($rowEvent->event_date, $this->app->get('offset'));
			}

			$msg = Text::sprintf('EB_CANCEL_DATE_PASSED', $cancelBeforeDate->format($config->event_date_format, true));
			$this->app->enqueueMessage($msg, 'warning');
			$this->app->redirect($redirectUrl);
		}

		/* @var EventbookingModelRegister $model */
		$model = $this->getModel('register');
		$model->cancelRegistration($rowEvent->registrant_id);

		$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=registrationcancel&id=' . $rowEvent->registrant_id . '&Itemid=' . $this->input->getInt('Itemid', 0), false));
	}

	/**
	 * Cancel editing a registration record
	 */
	public function cancel_edit()
	{
		if ($return = $this->input->getBase64('return', ''))
		{
			$this->setRedirect(base64_decode($return));
		}
		else
		{
			$this->setRedirect(Route::_(EventbookingHelperRoute::getViewRoute('registrants', $this->input->getInt('Itemid')), false));
		}
	}

	/**
	 * Download invoice associated to the registration record
	 *
	 * @throws Exception
	 */
	public function download_invoice()
	{
		$user = Factory::getUser();

		if (!$user->id)
		{
			$this->app->enqueueMessage(Text::_('You do not have permission to download the invoice'), 'error');
			$this->app->redirect(Uri::root(), 403);
		}

		$id    = $this->input->getInt('id', 0);
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('a.*, b.created_by')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.id = ' . $id);
		$db->setQuery($query);
		$row = $db->loadObject();

		if (!$row)
		{
			throw new Exception(sprintf('There is no registration record with ID %d', $id));
		}

		if (!$row->invoice_number)
		{
			throw new Exception(sprintf('No invoice generated for registration record with ID %d yet', $id));
		}

		if ($row->user_id == $user->id || EventbookingHelperAcl::canManageRegistrant($row))
		{
			$canDownload = true;
		}
		else
		{
			$canDownload = false;
		}

		if (!$canDownload)
		{
			$this->app->enqueueMessage(Text::_('You do not have permission to download the invoice'), 'error');
			$this->app->redirect(Uri::root(), 403);
		}

		// Generate invoice PDF
		EventbookingHelper::loadComponentLanguage($row->language, true);
		$filePath = EventbookingHelper::callOverridableHelperMethod('Helper', 'generateInvoicePDF', [$row]);

		// Handle backward compatible in case the generateInvoicePDF was overridden and does not return file path
		if (!$filePath)
		{
			$config        = EventbookingHelper::getConfig();
			$invoiceNumber = EventbookingHelper::callOverridableHelperMethod('Helper', 'formatInvoiceNumber', [$row->invoice_number, $config, $row]);
			$filePath      = JPATH_ROOT . '/media/com_eventbooking/invoices/' . $invoiceNumber . '.pdf';
		}

		$this->processDownloadFile($filePath);
	}

	/**
	 * Download certificate associated to the registration record
	 *
	 * @throws Exception
	 */
	public function download_certificate()
	{
		/* @var EventbookingTableRegistrant $row */
		$row    = Table::getInstance('registrant', 'EventbookingTable');
		$user   = Factory::getUser();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();

		$downloadCode = $this->input->getString('download_code');

		if (!$user->id && empty($downloadCode))
		{
			throw new Exception(Text::_('You do not have permission to download the certificate'), 403);
		}

		if (!empty($downloadCode))
		{
			$query->select('id')
				->from('#__eb_registrants')
				->where('registration_code = ' . $db->quote($downloadCode));
			$db->setQuery($query);

			$id = (int) $db->loadResult();
		}
		else
		{
			$id = $this->input->getInt('id', 0);
		}

		if (!$row->load($id))
		{
			throw new Exception(Text::_('Invalid Registration Record'), 404);
		}

		if (empty($downloadCode) && $row->user_id != $user->id && $row->email != $user->get('email'))
		{
			throw new Exception(Text::_('You do not have permission to download the certificate'), 403);
		}

		if ($row->published == 0)
		{
			throw new Exception(Text::_('EB_CERTIFICATE_PAID_REGISTRANTS_ONLY'), 403);
		}

		if ($config->download_certificate_if_checked_in && !$row->checked_in)
		{
			throw new Exception(Text::_('EB_CERTIFICATE_CHECKED_IN_REGISTRANTS_ONLY'), 403);
		}

		// Compare current date with event end date
		$currentDate = EventbookingHelper::getServerTimeFromGMTTime();
		$query->clear()
			->select('*')
			->select("TIMESTAMPDIFF(MINUTE, event_end_date, '$currentDate') AS event_end_date_minutes")
			->from('#__eb_events')
			->where('id = ' . $row->event_id);
		$db->setQuery($query);
		$rowEvent = $db->loadObject();

		if ($rowEvent->activate_certificate_feature == 0 || ($rowEvent->activate_certificate_feature == 2 && !$config->activate_certificate_feature))
		{
			throw new Exception(printf('Certificate is not enabled for event %s', $rowEvent->title), 403);
		}

		if ($rowEvent->event_end_date_minutes < 0)
		{
			throw new Exception(Text::_('EB_CERTIFICATE_AFTER_EVENT_END_DATE'), 403);
		}

		list($fileName, $filePath) = EventbookingHelper::callOverridableHelperMethod('Certificate', 'generateCertificates', [[$row], $config]);


		$this->processDownloadFile($filePath, $fileName);
	}

	/**
	 * Download tickets associated to the registration record
	 *
	 * @throws Exception
	 */
	public function download_ticket()
	{
		/* @var EventbookingTableRegistrant $row */
		$row    = Table::getInstance('registrant', 'EventbookingTable');
		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();

		$downloadCode = $this->input->getString('download_code');

		if (!$user->id && empty($downloadCode))
		{
			throw new Exception(Text::_('You do not have permission to download the ticket'), 403);
		}

		if (!empty($downloadCode))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__eb_registrants')
				->where('registration_code = ' . $db->quote($downloadCode));
			$db->setQuery($query);

			$id = (int) $db->loadResult();
		}
		else
		{
			$id = $this->input->getInt('id', 0);
		}

		if (!$row->load($id))
		{
			throw new Exception(Text::_('Invalid Registration Record'), 404);
		}

		if (empty($downloadCode) && $row->user_id != $user->id && $row->email != $user->get('email'))
		{
			throw new Exception(Text::_('You do not have permission to download the ticket'), 403);
		}

		if ($row->published == 0 || $row->payment_status != 1)
		{
			throw new Exception(Text::_('Ticket is only allowed for confirmed/paid registrants'), 403);
		}

		$fileName = '';

		// The person is allowed to download ticket, let process it
		if (!$row->is_group_billing)
		{
			// Individual registration or group member record
			$ticketFilePaths = EventbookingHelper::callOverridableHelperMethod('Ticket', 'generateRegistrationTicketsPDF', [$row, $config]);
			$filePath        = $ticketFilePaths[0];
		}
		else
		{
			$filePath = EventbookingHelperTicket::generateTicketsPDF($row, $config);

			// This line is added for backward compatible only, in case someone override the method generateTicketsPDF without returning file path
			if (!$filePath)
			{
				$fileName = 'ticket_' . str_pad($row->id, 5, '0', STR_PAD_LEFT) . '.pdf';
				$filePath = JPATH_ROOT . '/media/com_eventbooking/tickets/' . $fileName;
			}
		}

		$this->processDownloadFile($filePath, $fileName);
	}

	/**
	 * Export registrants data into a csv file
	 */
	public function export()
	{
		$eventId = $this->input->getInt('event_id', $this->input->getInt('filter_event_id'));

		if (!EventbookingHelperAcl::canExportRegistrants($eventId))
		{
			$this->app->enqueueMessage(Text::_('EB_NOT_ALLOWED_TO_EXPORT'), 'error');
			$this->app->redirect(Uri::root(), 403);
		}

		set_time_limit(0);
		$config = EventbookingHelper::getConfig();
		$model  = $this->getModel('registrants');

		// Fake config data so that registrants model get correct data for export
		if (isset($config->export_group_billing_records))
		{
			$config->set('include_group_billing_in_registrants', $config->export_group_billing_records);
		}

		if (isset($config->export_group_member_records))
		{
			$config->set('include_group_members_in_registrants', $config->export_group_member_records);
		}

		/* @var EventbookingModelRegistrants $model */
		$model->setState('filter_event_id', $eventId)
			->setState('limitstart', 0)
			->setState('limit', 0)
			->setState('filter_order', 'tbl.id')
			->setState('filter_order_Dir', 'ASC');

		$cid = $this->input->get('cid', [], 'raw');

		if (!is_array($cid))
		{
			$cid = explode(',', $cid);
		}

		$cid = array_filter(ArrayHelper::toInteger($cid));

		$model->setRegistrantIds($cid);

		$rows = $model->getData();

		if (count($rows) == 0)
		{
			echo Text::_('There are no registrants to export');

			return;
		}

		$rowFields = EventbookingHelperRegistration::getAllEventFields($eventId);
		$fieldIds  = [];

		foreach ($rowFields as $rowField)
		{
			$fieldIds[] = $rowField->id;
		}

		$fieldValues = $model->getFieldsData($fieldIds);

		list($fields, $headers) = EventbookingHelper::callOverridableHelperMethod('Data', 'prepareRegistrantsExportData', [$rows, $config, $rowFields, $fieldValues, $eventId]);

		PluginHelper::importPlugin('eventbooking');

		// Give plugin a chance to process export data
		$results = $this->app->triggerEvent('onBeforeExportDataToXLSX', [$rows, $fields, $headers, 'registrants_list.xlsx']);

		if (count($results) && $filename = $results[0])
		{
			// There is a plugin handles export, it return the filename, so we just process download the file
			$this->processDownloadFile($filename);

			return;
		}

		EventbookingHelper::callOverridableHelperMethod('Data', 'excelExport', [$fields, $rows, 'registrants_list', $headers]);
	}

	/**
	 * Export registrants data into a csv file
	 */
	public function export_pdf()
	{
		$eventId        = $this->input->getInt('event_id', $this->input->getInt('filter_event_id'));
		$filterOrder    = $this->input->getString('filter_order', 'tbl.id');
		$filterOrderDir = $this->input->getString('filter_order_Dir', 'ASC');

		if (!EventbookingHelperAcl::canExportRegistrants($eventId))
		{
			$this->app->enqueueMessage(Text::_('EB_NOT_ALLOWED_TO_EXPORT'), 'error');
			$this->app->redirect(Uri::root(), 403);
		}

		set_time_limit(0);
		$config = EventbookingHelper::getConfig();
		$model  = $this->getModel('registrants');

		// Fake config data so that registrants model get correct data for export
		if (isset($config->export_group_billing_records))
		{
			$config->set('include_group_billing_in_registrants', $config->export_group_billing_records);
		}

		if (isset($config->export_group_member_records))
		{
			$config->set('include_group_members_in_registrants', $config->export_group_member_records);
		}

		/* @var EventbookingModelRegistrants $model */
		$model->setState('filter_event_id', $eventId)
			->setState('limitstart', 0)
			->setState('limit', 0)
			->setState('filter_order', $filterOrder)
			->setState('filter_order_Dir', $filterOrderDir);

		$cid = $this->input->get('cid', [], 'raw');

		if (!is_array($cid))
		{
			$cid = explode(',', $cid);
		}

		$cid = array_filter(ArrayHelper::toInteger($cid));

		$model->setRegistrantIds($cid);

		$rows = $model->getData();

		if (count($rows) == 0)
		{
			echo Text::_('There are no registrants to export');

			return;
		}

		$rowFields = EventbookingHelperRegistration::getAllEventFields($eventId);
		$fieldIds  = [];

		foreach ($rowFields as $rowField)
		{
			$fieldIds[] = $rowField->id;
		}

		$fieldValues = $model->getFieldsData($fieldIds);

		list($fields, $headers) = EventbookingHelper::callOverridableHelperMethod('Data', 'prepareRegistrantsExportData', [$rows, $config, $rowFields, $fieldValues, $eventId]);

		$filePath = EventbookingHelper::callOverridableHelperMethod('Helper', 'generateRegistrantsPDF', [$rows, $fields, $headers]);

		$this->processDownloadFile($filePath);
	}

	/**
	 * Resend confirmation email to registrants in case they didn't receive it
	 */
	public function resend_email()
	{
		$this->csrfProtection();

		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);

		/* @var EventbookingModelRegistrant $model */
		$model = $this->getModel();
		$ret   = true;

		foreach ($cid as $id)
		{
			$ret = $model->resendEmail($id);
		}

		if ($ret)
		{
			$this->setMessage(Text::_('EB_EMAIL_SUCCESSFULLY_RESENT'));
		}
		else
		{
			$this->setMessage(Text::_('EB_COULD_NOT_RESEND_EMAIL_TO_GROUP_MEMBER'), 'notice');
		}

		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Checkin registrant from given ID
	 *
	 * @throws Exception
	 */
	public function checkin()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$id    = $this->input->getInt('id');

		$query->select('a.*, b.created_by, b.title AS event_title')
			->from('#__eb_registrants AS a')
			->leftJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.id = ' . $id);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();

		if (!$rowRegistrant)
		{
			throw new Exception('Invalid Registration Record:' . $id, 404);
		}

		if (EventbookingHelperAcl::canManageRegistrant($rowRegistrant))
		{
			/* @var EventbookingModelRegistrant $model */
			$model       = $this->getModel();
			$result      = $model->checkinRegistrant($id);
			$messageType = null;

			switch ($result)
			{
				case 0:
					$message     = Text::_('EB_INVALID_REGISTRATION_RECORD');
					$messageType = 'error';
					break;
				case 1:
					$message     = Text::_('EB_REGISTRANT_ALREADY_CHECKED_IN');
					$messageType = 'error';
					break;
				case 2:
					$message = Text::_('EB_CHECKED_IN_SUCCESSFULLY');
					break;
				case 3:
					$message = Text::_('EB_CHECKED_IN_FAIL_REGISTRATION_CANCELLED');
					break;
				case 4:
					$message = Text::_('EB_CHECKED_IN_REGISTRATION_PENDING');
					break;
			}

			$replaces = [
				'FIRST_NAME'         => $rowRegistrant->first_name,
				'LAST_NAME'          => $rowRegistrant->last_name,
				'EVENT_TITLE'        => $rowRegistrant->event_title,
				'REGISTRANT_ID'      => $rowRegistrant->id,
				'NUMBER_REGISTRANTS' => $rowRegistrant->number_registrants,
			];

			foreach ($replaces as $key => $value)
			{
				$message = str_ireplace('[' . $key . ']', $value, $message);
			}

			$this->setRedirect(Route::_(EventbookingHelperRoute::getViewRoute('registrants', null)), $message, $messageType);
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}

	/*
	 * Check in a registrant
	 */
	public function check_in_webapp()
	{
		JSession::checkToken('get');

		if (Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$id = $this->input->getInt('id');

			/* @var EventbookingModelRegistrant $model */
			$model = $this->getModel();

			try
			{
				$model->checkinRegistrant($id, true);
				$this->setMessage(Text::_('EB_CHECKIN_SUCCESSFULLY'));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

			$this->setRedirect(Route::_(EventbookingHelperRoute::getViewRoute('registrants', null)));
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}

	/**
	 * Reset check in for a registrant
	 *
	 * @throws Exception
	 */
	public function reset_check_in()
	{
		JSession::checkToken('get');

		if (Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$id = $this->input->getInt('id');

			/* @var EventbookingModelRegistrant $model */
			$model = $this->getModel();

			try
			{
				$model->resetCheckin($id);
				$this->setMessage(Text::_('EB_RESET_CHECKIN_SUCCESSFULLY'));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

			$this->setRedirect(Route::_(EventbookingHelperRoute::getViewRoute('registrants', null)));
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}

	/**
	 * Method to checkout selected registrants
	 *
	 * @throws Exception
	 */
	public function check_out()
	{
		if (Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$cid = $this->input->get('cid', [], 'array');

			/* @var EventbookingModelRegistrant $model */
			$model = $this->getModel();

			try
			{
				foreach ($cid as $id)
				{
					$model->resetCheckin($id);
				}

				$this->setMessage(Text::_('EB_CHECKOUT_REGISTRANTS_SUCCESSFULLY'));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

			$this->setRedirect(Route::_(EventbookingHelperRoute::getViewRoute('registrants', null)));
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}

	/**
	 * Method to checkin multiple registrants
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function checkin_multiple_registrants()
	{
		JSession::checkToken();

		if (!Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}

		$cid = $this->input->get('cid', [], 'array');

		$cid = ArrayHelper::toInteger($cid);

		if (count($cid))
		{
			/* @var EventbookingModelRegistrant $model */
			$model = $this->getModel();

			// First check to see if there is someone already checked in
			$db    = $model->getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from('#__eb_registrants')
				->where('checked_in = 1')
				->where('id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);
			$rowRegistrant = $db->loadObject();

			if ($rowRegistrant)
			{
				$message = Text::_('EB_REGISTRANT_ALREADY_CHECKED_IN');

				$replaces = [
					'FIRST_NAME'         => $rowRegistrant->first_name,
					'LAST_NAME'          => $rowRegistrant->last_name,
					'EVENT_TITLE'        => $rowRegistrant->event_title,
					'REGISTRANT_ID'      => $rowRegistrant->id,
					'NUMBER_REGISTRANTS' => $rowRegistrant->number_registrants,
				];

				foreach ($replaces as $key => $value)
				{
					$message = str_ireplace('[' . $key . ']', $value, $message);
				}

				$this->setMessage($message, 'error');
			}
			else
			{
				try
				{
					$model->batchCheckin($cid);
					$this->setMessage(Text::_('EB_CHECKIN_REGISTRANTS_SUCCESSFULLY'));
				}
				catch (Exception $e)
				{
					$this->setMessage($e->getMessage(), 'error');
				}
			}
		}

		$this->setRedirect(Route::_(EventbookingHelperRoute::getViewRoute('registrants', null)));
	}

	/**
	 * Refund a registration
	 *
	 * @throws Exception
	 */
	public function refund()
	{
		$id    = $this->input->post->getInt('id', 0);
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, b.created_by')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.id = ' . $id);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();

		if (EventbookingHelperAcl::canManageRegistrant($rowRegistrant) && EventbookingHelperRegistration::canRefundRegistrant($rowRegistrant))
		{
			/**@var EventbookingModelRegistrant $model * */
			$model = $this->getModel('Registrant');

			try
			{
				$model->refund($rowRegistrant);

				$this->setRedirect($this->getViewItemUrl($rowRegistrant->id), Text::_('EB_REGISTRATION_REFUNDED'));
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'error');
				$this->setRedirect($this->getViewItemUrl($rowRegistrant->id), $e->getMessage(), 'error');
			}
		}
		else
		{
			throw new InvalidArgumentException(Text::_('EB_CANNOT_PROCESS_REFUND'));
		}
	}


	/**
	 * Send batch mail to registrants
	 */
	public function batch_mail()
	{
		if (!Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			throw new Exception('You do not have permission to cancel registrations', 403);
		}

		/* @var EventbookingModelRegistrant $model */
		$model = $this->getModel();

		try
		{
			$model->batchMail($this->input);
			$this->setMessage(Text::_('EB_BATCH_MAIL_SUCCESS'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Resend confirmation email to registrants in case they didn't receive it
	 */
	public function cancel_registrations()
	{
		if (!Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			throw new Exception('You do not have permission to cancel registrations', 403);
		}

		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);
		$cid = $this->getManagableIds($cid);

		// For some reasons, no records was selected, don't process further
		if (!$cid)
		{
			echo 'No registration records selected';

			return;
		}

		/* @var EventbookingModelRegistrant $model */
		$model = $this->getModel();
		$model->cancelRegistrations($cid);
		$this->setRedirect($this->getViewListUrl(), Text::_('EB_SUCCESSFULLY_CANCELLED_REGISTRATIONS'));
	}

	/**
	 * Send payment request to selected registrant
	 *
	 * @return void
	 */
	public function request_payment()
	{
		if (!Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			throw new Exception('You do not have permission to request payment', 403);
		}

		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);
		$cid = $this->getManagableIds($cid);

		/* @var EventbookingModelRegistrant $model */
		$model = $this->getModel();

		try
		{
			foreach ($cid as $id)
			{
				$model->sendPaymentRequestEmail($id);
			}

			$this->setMessage(Text::_('EB_REQUEST_PAYMENT_EMAIL_SENT_SUCCESSFULLY'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Resend confirmation email to registrants in case they didn't receive it
	 */
	public function send_certificates()
	{
		if (!Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			throw new Exception('You do not have permission to send certificates', 403);
		}

		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);
		$cid = $this->getManagableIds($cid);

		/* @var EventbookingModelRegistrant $model */
		$model = $this->getModel();

		try
		{
			foreach ($cid as $id)
			{
				$model->sendCertificates($id);
			}

			$this->setMessage(Text::_('EB_CERTIFICATES_SUCCESSFULLY_SENT'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Download Certificates for selected registrants
	 */
	public function download_certificates()
	{
		if (!Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			throw new Exception('You do not have permission to download certificates', 403);
		}

		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);
		$cid = $this->getManagableIds($cid);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_registrants')
			->where('id IN (' . implode(',', $cid) . ')')
			->order('id');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$config = EventbookingHelper::getConfig();

		list($fileName, $filePath) = EventbookingHelper::callOverridableHelperMethod('Helper', 'generateCertificates', [$rows, $config]);

		$this->processDownloadFile($filePath, $fileName);
	}

	/**
	 * Get Managable Registrant Ids by current logged in user
	 *
	 * @param   array  $ids
	 *
	 * @return array
	 */
	protected function getManagableIds($ids)
	{
		$user   = Factory::getUser();
		$config = EventbookingHelper::getConfig();

		// User without super admin permission can only perform actions on the registration records from events managed by himself
		if (!$user->authorise('core.admin', 'com_eventbooking') && $config->only_show_registrants_of_event_owner)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('a.id')
				->from('#__eb_registrants AS a')
				->innerJoin('#__eb_events AS b ON a.event_id = b.id')
				->where('b.created_by = ' . $user->id)
				->where('a.id IN (' . implode(',', $ids) . ')');
			$db->setQuery($query);
			$ids = $db->loadColumn();
		}

		return $ids;
	}

	/**
	 * Get url of the page which display list of records
	 *
	 * @return string
	 */
	protected function getViewListUrl()
	{
		$url = 'index.php?option=com_eventbooking&view=registrants&Itemid=' . EventbookingHelperRoute::findView('registrants', $this->input->getInt('Itemid'));

		return Route::_($url, false);
	}

	/**
	 * Get url of the page which allow adding/editing a record
	 *
	 * @param   int  $recordId
	 *
	 * @return string
	 */
	protected function getViewItemUrl($recordId = null)
	{
		$url = 'index.php?option=' . $this->option . '&view=' . $this->viewItem;

		if ($recordId)
		{
			$url .= '&id=' . $recordId;
		}

		$url .= '&Itemid=' . $this->input->getInt('Itemid', EventbookingHelperRoute::findView('registrants', 0));

		return Route::_($url, false);
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 */
	protected function allowAdd($data = [])
	{
		if (!Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			return false;
		}

		return parent::allowAdd($data);
	}

	/**
	 * Method to check if you can edit a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 */
	protected function allowEdit($data = [], $key = 'id')
	{
		$user = Factory::getUser();
		$row  = Table::getInstance('Registrant', 'EventbookingTable');

		// Invalid registration record for some reason, does not allow saving
		if (!$row->load($data['id']))
		{
			return false;
		}

		// User is editing their own registration history
		if ($row->user_id == $user->id || $row->email == $user->email)
		{
			return true;
		}

		// A manager is editing a registration record, check and make sure he is allowed to edit data
		return EventbookingHelperAcl::canManageRegistrant($row);
	}

	/**
	 * Method to check whether the current user is allowed to delete a record
	 *
	 * @param   int  $id  Record ID
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 */
	protected function allowDelete($id)
	{
		return EventbookingHelperAcl::canDeleteRegistrant($id);
	}

	/**
	 * Method to check whether the current user can change status (publish, unpublish of a record)
	 *
	 * @param   int  $id  Id of the record
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 */
	protected function allowEditState($id)
	{
		if (!Factory::getUser()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			return false;
		}

		return parent::allowEditState($id);
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
