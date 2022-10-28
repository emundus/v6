<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class EventbookingHelperMail
{
	/**
	 * From Name
	 *
	 * @var string
	 */
	public static $fromName;

	/**
	 * From Email
	 *
	 * @var string
	 */
	public static $fromEmail;

	/**
	 * Helper function for sending emails to registrants and administrator
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADConfig                    $config
	 */
	public static function sendEmails($row, $config)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendEmails'))
		{
			EventbookingHelperOverrideMail::sendEmails($row, $config);

			return;
		}

		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);

		if ($event->send_emails != -1)
		{
			$config->send_emails = $event->send_emails;
		}

		if ($config->send_emails == 3)
		{
			return;
		}

		$userId = $row->user_id ?: null;

		// Load frontend component language if needed
		EventbookingHelper::loadRegistrantLanguage($row);

		if (strlen(trim($event->notification_emails)) > 0)
		{
			$config->notification_emails = $event->notification_emails;
		}

		$mailer = static::getMailer($config, $event);

		if ($event->created_by && $config->send_email_to_event_creator)
		{
			$eventCreator = JUser::getInstance($event->created_by);

			if (MailHelper::isEmailAddress($eventCreator->email) && !$eventCreator->authorise('core.admin'))
			{
				$mailer->addReplyTo($eventCreator->email);
			}
		}

		if ($row->published == 3)
		{
			$typeOfRegistration = 2;
		}
		else
		{
			$typeOfRegistration = 1;
		}

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->id, 4, $row->language, $userId, $typeOfRegistration);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 1, $row->language, $userId, $typeOfRegistration);
		}
		else
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 0, $row->language, $userId, $typeOfRegistration);
		}

		$form = new RADForm($rowFields);
		$data = EventbookingHelperRegistration::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		$replaces = EventbookingHelper::callOverridableHelperMethod('Registration', 'buildTags', [$row, $form, $event, $config], 'Helper');

		// Get group members data
		if ($event->collect_member_information === '')
		{
			$collectMemberInformation = $config->collect_member_information;
		}
		else
		{
			$collectMemberInformation = $event->collect_member_information;
		}

		if ($row->is_group_billing && $collectMemberInformation)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from('#__eb_registrants')
				->where('group_id = ' . $row->id);
			$db->setQuery($query);
			$rowMembers = $db->loadObjectList();
		}
		else
		{
			$rowMembers = [];
		}

		$invoiceFilePath = '';

		if ($config->activate_invoice_feature
			&& $row->invoice_number
			&& ($config->send_invoice_to_admin || $config->send_invoice_to_customer))
		{
			$invoiceFilePath = EventbookingHelper::callOverridableHelperMethod('Helper', 'generateInvoicePDF', [$row]);

			// This is for backward-compatible only in case someone override generateInvoicePDF method
			if (!$invoiceFilePath)
			{
				$invoiceFilePath = JPATH_ROOT . '/media/com_eventbooking/invoices/' . EventbookingHelper::callOverridableHelperMethod('Helper', 'formatInvoiceNumber', [$row->invoice_number, $config, $row]) . '.pdf';
			}
		}

		// Send confirmation email to registrant
		if (in_array($config->send_emails, [0, 2]))
		{
			// Send to billing member, get attachments back and use it to add to attachments to group member email
			$attachments = EventbookingHelper::callOverridableHelperMethod('Mail', 'sendRegistrationEmailToRegistrant',
				[$mailer, $row, $rowMembers, $replaces, $rowFields, $invoiceFilePath]);

			// Send emails to group members
			if ($config->send_email_to_group_members && $row->is_group_billing && count($rowMembers))
			{
				static::sendRegistrationEmailToGroupMembers($mailer, $row, $rowMembers, $replaces, $attachments);
			}

			// Clear attachments
			$mailer->clearAttachments();
			$mailer->clearReplyTos();
		}

		// Send notification emails to admin if needed
		if (in_array($config->send_emails, [0, 1]))
		{
			static::sendRegistrationEmailToAdmin($mailer, $row, $form, $replaces, $rowFields, $invoiceFilePath);
		}
	}

	/**
	 * Send registration email to registrant
	 *
	 * @param   JMail                          $mailer
	 * @param   EventbookingTableRegistrant    $row
	 * @param   EventbookingTableRegistrant[]  $rowMembers
	 * @param   array                          $replaces
	 * @param   array                          $rowFields
	 * @param   string                         $invoiceFilePath
	 *
	 * @return array List of attachments which can be used for group members
	 */
	public static function sendRegistrationEmailToRegistrant($mailer, $row, $rowMembers, $replaces, $rowFields, $invoiceFilePath)
	{
		$message     = EventbookingHelper::getMessages();
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);
		$logEmails   = static::loggingEnabled('new_registration_emails', $config);

		if ($fieldSuffix && strlen($message->{'user_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'user_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->user_email_subject;
		}

		if (!$row->published && strpos($row->payment_method, 'os_offline') !== false)
		{
			$offlineSuffix = str_replace('os_offline', '', $row->payment_method);

			if ($offlineSuffix && $fieldSuffix && EventbookingHelper::isValidMessage($message->{'user_email_body_offline' . $offlineSuffix . $fieldSuffix}))
			{
				$body = $message->{'user_email_body_offline' . $offlineSuffix . $fieldSuffix};
			}
			elseif ($offlineSuffix && EventbookingHelper::isValidMessage($message->{'user_email_body_offline' . $offlineSuffix}))
			{
				$body = $message->{'user_email_body_offline' . $offlineSuffix};
			}
			elseif ($fieldSuffix && EventbookingHelper::isValidMessage($event->{'user_email_body_offline' . $fieldSuffix}))
			{
				$body = $event->{'user_email_body_offline' . $fieldSuffix};
			}
			elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'user_email_body_offline' . $fieldSuffix}))
			{
				$body = $message->{'user_email_body_offline' . $fieldSuffix};
			}
			elseif (EventbookingHelper::isValidMessage($event->user_email_body_offline))
			{
				$body = $event->user_email_body_offline;
			}
			else
			{
				$body = $message->user_email_body_offline;
			}
		}
		else
		{
			if ($fieldSuffix && EventbookingHelper::isValidMessage($event->{'user_email_body' . $fieldSuffix}))
			{
				$body = $event->{'user_email_body' . $fieldSuffix};
			}
			elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'user_email_body' . $fieldSuffix}))
			{
				$body = $message->{'user_email_body' . $fieldSuffix};
			}
			elseif (EventbookingHelper::isValidMessage($event->user_email_body))
			{
				$body = $event->user_email_body;
			}
			else
			{
				$body = $message->user_email_body;
			}
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);
		$body = EventbookingHelperRegistration::processQRCODE($row, $body);

		if ($config->send_invoice_to_customer && $invoiceFilePath)
		{
			$mailer->addAttachment($invoiceFilePath);
		}

		if ($config->get('activate_tickets_pdf') && $config->get('send_tickets_via_email', 1))
		{
			static::addRegistrationTickets($mailer, $row, $rowMembers, $config);
		}

		if ($config->get('send_event_attachments', 1))
		{
			$attachments = static::addEventAttachments($mailer, $row, $event, $config);
		}
		else
		{
			$attachments = [];
		}

		//Generate and send ics file to registrants
		if ($config->send_ics_file)
		{
			$icFile = static::addRegistrationIcs($mailer, $row, $event, $config);

			if ($icFile)
			{
				$attachments[] = $icFile;
			}
		}

		if (MailHelper::isEmailAddress($row->email))
		{
			$sendTos = [$row->email];

			foreach ($rowFields as $rowField)
			{
				if ($rowField->receive_confirmation_email && !empty($replaces[$rowField->name]) && MailHelper::isEmailAddress($replaces[$rowField->name]))
				{
					$sendTos[] = $replaces[$rowField->name];
				}
			}

			static::send($mailer, $sendTos, $subject, $body, $logEmails, 2, 'new_registration_emails');
			$mailer->clearAllRecipients();
		}

		return $attachments;
	}

	/**
	 * Send registration email to group members
	 *
	 * @param   JMail                          $mailer
	 * @param   EventbookingTableRegistrant    $row
	 * @param   EventbookingTableRegistrant[]  $rowMembers
	 * @param   array                          $replaces
	 * @param   array                          $attachments
	 */
	protected static function sendRegistrationEmailToGroupMembers($mailer, $row, $rowMembers, $replaces, $attachments)
	{
		$message     = EventbookingHelper::getMessages();
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);
		$rowLocation = EventbookingHelperDatabase::getLocation($event->location_id, $fieldSuffix);

		if (strlen($message->{'group_member_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'group_member_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->group_member_email_subject;
		}

		if (!$subject)
		{
			return;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($event->{'group_member_email_body' . $fieldSuffix}))
		{
			$body = $event->{'group_member_email_body' . $fieldSuffix};
		}
		elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'group_member_email_body' . $fieldSuffix}))
		{
			$body = $message->{'group_member_email_body' . $fieldSuffix};
		}
		elseif (EventbookingHelper::isValidMessage($event->group_member_email_body))
		{
			$body = $event->group_member_email_body;
		}
		else
		{
			$body = $message->group_member_email_body;
		}

		if (!$body)
		{
			return;
		}

		$userId = $row->user_id ?: null;

		if ($row->published == 3)
		{
			$typeOfRegistration = 2;
		}
		else
		{
			$typeOfRegistration = 1;
		}

		$logEmails = static::loggingEnabled('new_registration_emails', $config);

		$memberReplaces = [];

		if ($config->event_custom_field && file_exists(JPATH_ROOT . '/components/com_eventbooking/fields.xml'))
		{
			foreach ($event->paramData as $customFieldName => $param)
			{
				$memberReplaces[strtoupper($customFieldName)] = $param['value'];
			}
		}

		$memberReplaces['registration_detail'] = $replaces['registration_detail'];
		$memberReplaces['payment_method']      = $replaces['payment_method'];
		$memberReplaces['payment_method_name'] = $replaces['payment_method_name'];

		$memberReplaces['group_billing_first_name'] = $row->first_name;
		$memberReplaces['group_billing_last_name']  = $row->last_name;
		$memberReplaces['group_billing_email']      = $row->email;

		$memberReplaces['event_title']       = $replaces['event_title'];
		$memberReplaces['event_date']        = $replaces['event_date'];
		$memberReplaces['event_end_date']    = $replaces['event_end_date'];
		$memberReplaces['transaction_id']    = $replaces['transaction_id'];
		$memberReplaces['date']              = $replaces['date'];
		$memberReplaces['short_description'] = $replaces['short_description'];
		$memberReplaces['description']       = $replaces['short_description'];
		$memberReplaces['location']          = $replaces['location'];
		$memberReplaces['event_link']        = $replaces['event_link'];

		$memberReplaces['download_certificate_link'] = $replaces['download_certificate_link'];

		$memberFormFields = EventbookingHelperRegistration::getFormFields($row->event_id, 2, $row->language, $userId, $typeOfRegistration);

		foreach ($rowMembers as $rowMember)
		{
			if (!MailHelper::isEmailAddress($rowMember->email))
			{
				continue;
			}

			// Clear attachments sent to billing records
			$mailer->clearAttachments();

			//Build the member form
			$memberForm = new RADForm($memberFormFields);
			$memberData = EventbookingHelperRegistration::getRegistrantData($rowMember, $memberFormFields);
			$memberForm->bind($memberData);
			$memberForm->buildFieldsDependency();
			$fields = $memberForm->getFields();

			foreach ($fields as $field)
			{
				if ($field->hideOnDisplay)
				{
					$fieldValue = '';
				}
				else
				{
					if (is_string($field->value) && is_array(json_decode($field->value)))
					{
						$fieldValue = implode(', ', json_decode($field->value));
					}
					else
					{
						$fieldValue = $field->value;
					}
				}

				$memberReplaces[$field->name] = $fieldValue;
			}

			$memberReplaces['member_detail'] = EventbookingHelperRegistration::getMemberDetails($config, $rowMember, $event, $rowLocation, true, $memberForm);
			$memberReplaces['id']            = $rowMember->id;

			$groupMemberEmailSubject = $subject;
			$groupMemberEmailBody    = $body;

			foreach ($memberReplaces as $key => $value)
			{
				$key                     = strtoupper($key);
				$groupMemberEmailBody    = str_ireplace("[$key]", $value, $groupMemberEmailBody);
				$groupMemberEmailSubject = str_ireplace("[$key]", $value, $groupMemberEmailSubject);
			}

			$groupMemberEmailBody = EventbookingHelper::convertImgTags($groupMemberEmailBody);

			foreach ($attachments as $attachment)
			{
				$mailer->addAttachment($attachment);
			}

			// Create PDF ticket
			if ($row->ticket_code && $row->payment_status == 1)
			{
				$ticketNumber   = EventbookingHelperTicket::formatTicketNumber($event->ticket_prefix, $rowMember->ticket_number, $config);
				$ticketFileName = File::makeSafe($ticketNumber);
				$ticketFilePath = JPATH_ROOT . '/media/com_eventbooking/tickets/' . $ticketFileName;

				if (!file_exists($ticketFilePath))
				{
					$ticketFilePaths = EventbookingHelper::callOverridableHelperMethod('Ticket', 'generateRegistrationTicketsPDF', [$rowMember, $config]);

					foreach ($ticketFilePaths as $ticketFilePath)
					{
						$mailer->addAttachment($ticketFilePath);
					}
				}
				else
				{
					$mailer->addAttachment($ticketFilePath);
				}
			}

			static::send($mailer, [$rowMember->email], $groupMemberEmailSubject, $groupMemberEmailBody, $logEmails, 2, 'new_registration_emails');
			$mailer->clearAllRecipients();
		}
	}

	/**
	 * Send registration email to administrator
	 *
	 * @param   JMail                        $mailer
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADForm                      $form
	 * @param   array                        $replaces
	 * @param   array                        $rowFields
	 * @param   array                        $attachments
	 * @param   string                       $invoiceFilePath
	 */
	protected static function sendRegistrationEmailToAdmin($mailer, $row, $form, $replaces, $rowFields, $invoiceFilePath)
	{
		$message     = EventbookingHelper::getMessages();
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);
		$logEmails   = static::loggingEnabled('new_registration_emails', $config);

		// Send invoice PDF to admin email
		if ($config->send_invoice_to_admin && $invoiceFilePath)
		{
			$mailer->addAttachment($invoiceFilePath);
		}

		// Send attachments which registrants uploaded on registration form to admin
		if ($config->send_attachments_to_admin)
		{
			static::addRegistrationFormAttachments($mailer, $rowFields, $replaces);
		}

		$emails = $emails = explode(',', $config->notification_emails);

		if ($fieldSuffix && strlen($message->{'admin_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'admin_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->admin_email_subject;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($event->{'admin_email_body' . $fieldSuffix}))
		{
			$body = $event->{'admin_email_body' . $fieldSuffix};
		}
		elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'admin_email_body' . $fieldSuffix}))
		{
			$body = $message->{'admin_email_body' . $fieldSuffix};
		}
		elseif (EventbookingHelper::isValidMessage($event->admin_email_body))
		{
			$body = $event->admin_email_body;
		}
		else
		{
			$body = $message->admin_email_body;
		}

		if ($row->payment_method == 'os_offline_creditcard')
		{
			$replaces['registration_detail'] = EventbookingHelperRegistration::getEmailContent($config, $row, true, $form, true);
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);
		$body = EventbookingHelperRegistration::processQRCODE($row, $body);

		if (strpos($body, '[QRCODE]') !== false)
		{
			EventbookingHelper::generateQrcode($row->id);
			$imgTag = '<img src="' . EventbookingHelper::getSiteUrl() . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
			$body   = str_ireplace("[QRCODE]", $imgTag, $body);
		}

		if ($config->send_email_to_event_creator && $event->created_by)
		{
			$eventCreator = JUser::getInstance($event->created_by);

			if (!empty($eventCreator->email)
				&& !$eventCreator->authorise('core.admin')
				&& MailHelper::isEmailAddress($eventCreator->email)
				&& !in_array($eventCreator->email, $emails))
			{
				$emails[] = $eventCreator->email;
			}
		}

		if (MailHelper::isEmailAddress($row->email))
		{
			$mailer->addReplyTo($row->email);
		}

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'new_registration_emails');
	}

	/**
	 * Send email to registrant when admin approves his registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADConfig                    $config
	 */
	public static function sendRegistrationApprovedEmail($row, $config)
	{
		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendRegistrationApprovedEmail'))
		{
			EventbookingHelperOverrideMail::sendRegistrationApprovedEmail($row, $config);

			return;
		}

		$logEmails = static::loggingEnabled('registration_approved_emails', $config);

		EventbookingHelper::loadRegistrantLanguage($row);

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);

		$mailer = static::getMailer($config, $event);

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->id, 4, $row->language);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 1, $row->language);
		}
		else
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 0, $row->language);
		}

		$form = new RADForm($rowFields);
		$data = EventbookingHelperRegistration::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		$replaces = EventbookingHelper::callOverridableHelperMethod('Registration', 'buildTags', [$row, $form, $event, $config], 'Helper');

		if (strlen(trim($event->registration_approved_email_subject)))
		{
			$subject = $event->registration_approved_email_subject;
		}
		elseif (strlen($message->{'registration_approved_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'registration_approved_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->registration_approved_email_subject;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($event->{'registration_approved_email_body' . $fieldSuffix}))
		{
			$body = $event->{'registration_approved_email_body' . $fieldSuffix};
		}
		elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'registration_approved_email_body' . $fieldSuffix}))
		{
			$body = $message->{'registration_approved_email_body' . $fieldSuffix};
		}
		elseif (EventbookingHelper::isValidMessage($event->registration_approved_email_body))
		{
			$body = $event->registration_approved_email_body;
		}
		else
		{
			$body = $message->registration_approved_email_body;
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_replace("[$key]", $value, $subject);
			$body    = str_replace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);
		$body = EventbookingHelperRegistration::processQRCODE($row, $body);

		if (strpos($body, '[QRCODE]') !== false)
		{
			EventbookingHelper::generateQrcode($row->id);
			$imgTag = '<img src="' . EventbookingHelper::getSiteUrl() . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
			$body   = str_ireplace("[QRCODE]", $imgTag, $body);
		}

		if ($config->activate_invoice_feature && $config->send_invoice_to_customer && $row->invoice_number && !$row->group_id)
		{
			$invoiceFilePath = EventbookingHelper::callOverridableHelperMethod('Helper', 'generateInvoicePDF', [$row]);

			if (!$invoiceFilePath)
			{
				$invoiceFilePath = JPATH_ROOT . '/media/com_eventbooking/invoices/' . EventbookingHelper::callOverridableHelperMethod('Helper', 'formatInvoiceNumber', [$row->invoice_number, $config, $row]) . '.pdf';
			}

			$mailer->addAttachment($invoiceFilePath);
		}

		if ($row->ticket_code && $config->get('send_tickets_via_email', 1))
		{
			if ($config->get('multiple_booking'))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select('*')
					->from('#__eb_registrants')
					->where('id = ' . $row->id . ' OR cart_id = ' . $row->id);
				$db->setQuery($query);
				$rowRegistrants = $db->loadObjectList();

				foreach ($rowRegistrants as $rowRegistrant)
				{
					if ($rowRegistrant->ticket_code)
					{
						$ticketFilePaths = EventbookingHelper::callOverridableHelperMethod('Ticket', 'generateRegistrationTicketsPDF', [$rowRegistrant, $config]);

						foreach ($ticketFilePaths as $ticketFilePath)
						{
							$mailer->addAttachment($ticketFilePath);
						}
					}
				}
			}
			else
			{
				$ticketFilePaths = EventbookingHelper::callOverridableHelperMethod('Ticket', 'generateRegistrationTicketsPDF', [$row, $config]);

				foreach ($ticketFilePaths as $ticketFilePath)
				{
					$mailer->addAttachment($ticketFilePath);
				}
			}
		}

		$sendTos = [$row->email];

		foreach ($rowFields as $rowField)
		{
			if ($rowField->receive_confirmation_email && !empty($replaces[$rowField->name]) && MailHelper::isEmailAddress($replaces[$rowField->name]))
			{
				$sendTos[] = $replaces[$rowField->name];
			}
		}

		static::send($mailer, $sendTos, $subject, $body, $logEmails, 2, 'registration_approved_emails');
	}

	/**
	 * Send email to registrant when admin change the status to cancelled
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   object                       $config
	 */
	public static function sendRegistrationCancelledEmail($row, $config)
	{
		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendRegistrationCancelledEmail'))
		{
			EventbookingHelperOverrideMail::sendRegistrationCancelledEmail($row, $config);

			return;
		}

		$logEmails = static::loggingEnabled('registration_cancel_emails', $config);

		$app = Factory::getApplication();

		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

		$event = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);

		if ($app->isClient('administrator'))
		{
			if ($row->language && $row->language != '*')
			{
				$tag = $row->language;
			}
			else
			{
				$tag = EventbookingHelper::getDefaultLanguage();
			}

			Factory::getLanguage()->load('com_eventbooking', JPATH_ROOT, $tag);
		}

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

		if ($fieldSuffix && strlen($message->{'user_registration_cancel_subject' . $fieldSuffix}))
		{
			$subject = $message->{'user_registration_cancel_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->user_registration_cancel_subject;
		}

		if (empty($subject))
		{
			return;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'user_registration_cancel_message' . $fieldSuffix}))
		{
			$body = $message->{'user_registration_cancel_message' . $fieldSuffix};
		}
		else
		{
			$body = $message->user_registration_cancel_message;
		}

		if (empty($body))
		{
			return;
		}

		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		$mailer = static::getMailer($config, $event);

		$replaces = EventbookingHelperRegistration::getRegistrationReplaces($row, $event);

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_replace("[$key]", $value, $subject);
			$body    = str_replace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, [$row->email], $subject, $body, $logEmails, 2, 'registration_cancel_emails');
	}

	/**
	 * Send email when users fill-in waitinglist
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADConfig                    $config
	 */
	public static function sendWaitinglistEmail($row, $config)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendWaitinglistEmail'))
		{
			EventbookingHelperOverrideMail::sendWaitinglistEmail($row, $config);

			return;
		}

		$logEmails = static::loggingEnabled('waiting_list_emails', $config);

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

		$event = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);

		if (strlen(trim($event->notification_emails)) > 0)
		{
			$config->notification_emails = $event->notification_emails;
		}

		$mailer = static::getMailer($config, $event);

		$replaces = EventbookingHelperRegistration::getRegistrationReplaces($row, $event, $row->user_id, $config->multiple_booking);

		//Notification email send to user
		if ($fieldSuffix && strlen($message->{'watinglist_confirmation_subject' . $fieldSuffix}))
		{
			$subject = $message->{'watinglist_confirmation_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->watinglist_confirmation_subject;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'watinglist_confirmation_body' . $fieldSuffix}))
		{
			$body = $message->{'watinglist_confirmation_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->watinglist_confirmation_body;
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_replace("[$key]", $value, $subject);
			$body    = str_replace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		if (MailHelper::isEmailAddress($row->email))
		{
			static::send($mailer, [$row->email], $subject, $body, $logEmails, 2, 'waiting_list_emails');
			$mailer->clearAllRecipients();
		}

		$emails = explode(',', $config->notification_emails);

		if (strlen($message->{'watinglist_notification_subject' . $fieldSuffix}))
		{
			$subject = $message->{'watinglist_notification_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->watinglist_notification_subject;
		}

		if (EventbookingHelper::isValidMessage($message->{'watinglist_notification_body' . $fieldSuffix}))
		{
			$body = $message->{'watinglist_notification_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->watinglist_notification_body;
		}

		$subject = str_ireplace('[EVENT_TITLE]', $event->title, $subject);

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_replace("[$key]", $value, $subject);
			$body    = str_replace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'waiting_list_emails');
	}

	/**
	 * Send notification emails to waiting list users when a registration is cancelled
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADConfig                    $config
	 */
	public static function sendWaitingListNotificationEmail($row, $config)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendWaitingListNotificationEmail'))
		{
			EventbookingHelperOverrideMail::sendWaitingListNotificationEmail($row, $config);

			return;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_registrants')
			->where('event_id=' . (int) $row->event_id)
			->where('group_id = 0')
			->where('published = 3')
			->order('id');
		$db->setQuery($query);
		$registrants = $db->loadObjectList();
		$siteUrl     = Uri::root();

		$logEmails = static::loggingEnabled('waiting_list_notification_emails', $config);

		if (count($registrants))
		{
			$rowEvent = EventbookingHelperDatabase::getEvent($row->event_id);

			$mailer = static::getMailer($config, $rowEvent);

			$message = EventbookingHelper::getMessages();

			$replaces                          = [];
			$replaces['registrant_first_name'] = $row->first_name;
			$replaces['registrant_last_name']  = $row->last_name;

			if (Factory::getApplication()->isClient('site'))
			{
				$replaces['event_link'] = Uri::getInstance()->toString(['scheme', 'user', 'pass', 'host']) . Route::_(EventbookingHelperRoute::getEventRoute($row->event_id, 0, EventbookingHelper::getItemid()));
			}
			else
			{
				$replaces['event_link'] = $siteUrl . EventbookingHelperRoute::getEventRoute($row->event_id, 0, EventbookingHelper::getItemid());
			}

			$replaces['event_title']    = $rowEvent->title;
			$replaces['event_date']     = HTMLHelper::_('date', $rowEvent->event_date, $config->event_date_format, null);
			$replaces['event_end_date'] = HTMLHelper::_('date', $rowEvent->event_end_date, $config->event_date_format, null);

			foreach ($registrants as $registrant)
			{
				if (!MailHelper::isEmailAddress($registrant->email))
				{
					continue;
				}

				// Check to see if user already registered
				$query->clear()
					->select('COUNT(*)')
					->from('#__eb_registrants')
					->where('group_id = 0')
					->where('(published = 1 OR (published = 0 AND payment_method LIKE "os_offline%"))');

				if ($registrant->user_id > 0)
				{
					$query->where('user_id = ' . $registrant->user_id);
				}
				else
				{
					$query->where('email = ' . $db->quote($registrant->email));
				}

				$db->setQuery($query);

				if ($db->loadResult() > 0)
				{
					// Ignore sending email because this user is already registered for the event
					continue;
				}


				$fieldSuffix = EventbookingHelper::getFieldSuffix($registrant->language);

				if (strlen(trim($message->{'registrant_waitinglist_notification_subject' . $fieldSuffix})))
				{
					$subject = $message->{'registrant_waitinglist_notification_subject' . $fieldSuffix};
				}
				else
				{
					$subject = $message->registrant_waitinglist_notification_subject;
				}

				if (empty($subject))
				{
					//Admin has not entered email subject and email message for notification yet, simply return
					return false;
				}

				if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'registrant_waitinglist_notification_body' . $fieldSuffix}))
				{
					$body = $message->{'registrant_waitinglist_notification_body' . $fieldSuffix};
				}
				else
				{
					$body = $message->registrant_waitinglist_notification_body;
				}

				$replaces['first_name'] = $registrant->first_name;
				$replaces['last_name']  = $registrant->last_name;

				foreach ($replaces as $key => $value)
				{
					$key     = strtoupper($key);
					$subject = str_replace("[$key]", $value, $subject);
					$body    = str_replace("[$key]", $value, $body);
				}

				$body = EventbookingHelper::convertImgTags($body);

				static::send($mailer, [$registrant->email], $subject, $body, $logEmails);

				$mailer->clearAddresses();
			}
		}
	}

	/**
	 * Send email when registrants complete deposit payment
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADConfig                    $config
	 */
	public static function sendDepositPaymentEmail($row, $config)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendDepositPaymentEmail'))
		{
			EventbookingHelperOverrideMail::sendDepositPaymentEmail($row, $config);

			return;
		}

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);

		if (strlen(trim($event->notification_emails)) > 0)
		{
			$config->notification_emails = $event->notification_emails;
		}

		$mailer = static::getMailer($config, $event);

		$replaces = EventbookingHelperRegistration::buildDepositPaymentTags($row, $config);

		//Notification email send to user
		if (MailHelper::isEmailAddress($row->email))
		{
			if ($fieldSuffix && strlen($message->{'deposit_payment_user_email_subject' . $fieldSuffix}))
			{
				$subject = $message->{'deposit_payment_user_email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->deposit_payment_user_email_subject;
			}

			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'deposit_payment_user_email_body' . $fieldSuffix}))
			{
				$body = $message->{'deposit_payment_user_email_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->deposit_payment_user_email_body;
			}

			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$body    = str_ireplace("[$key]", $value, $body);
				$subject = str_ireplace("[$key]", $value, $subject);
			}

			if ($row->ticket_code)
			{
				$ticketFilePath = EventbookingHelperTicket::generateTicketsPDF($row, $config);

				// This line is added for backward compatible only, in case someone override the method generateTicketsPDF without returning file path
				if (!$ticketFilePath)
				{
					$ticketFilePath = JPATH_ROOT . '/media/com_eventbooking/tickets/ticket_' . str_pad($row->id, 5, '0', STR_PAD_LEFT) . '.pdf';
				}

				$mailer->addAttachment($ticketFilePath);
			}

			static::send($mailer, [$row->email], $subject, $body);

			$mailer->clearAttachments();
			$mailer->clearAllRecipients();
		}

		$emails = explode(',', $config->notification_emails);

		if (strlen($message->{'deposit_payment_admin_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'deposit_payment_admin_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->deposit_payment_admin_email_subject;
		}

		if (EventbookingHelper::isValidMessage($message->{'deposit_payment_admin_email_body' . $fieldSuffix}))
		{
			$body = $message->{'deposit_payment_admin_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->deposit_payment_admin_email_body;
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, $emails, $subject, $body);
	}

	/**
	 * Send new event notification email to admin and users when new event is submitted in the frontend
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   RADConfig               $config
	 */
	public static function sendNewEventNotificationEmail($row, $config)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendNewEventNotificationEmail'))
		{
			EventbookingHelperOverrideMail::sendNewEventNotificationEmail($row, $config);

			return;
		}

		$logEmails = static::loggingEnabled('new_event_notification_emails', $config);

		$user        = Factory::getUser();
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->created_language);
		$Itemid      = Factory::getApplication()->input->getInt('Itemid');

		$mailer = static::getMailer($config);

		$replaces = [
			'user_id'     => $user->id,
			'username'    => $user->username,
			'name'        => $user->name,
			'email'       => $user->email,
			'event_id'    => $row->id,
			'event_title' => $row->title,
			'event_date'  => HTMLHelper::_('date', $row->event_date, $config->event_date_format, null),
			'event_link'  => Uri::root() . 'index.php?option=com_eventbooking&view=event&layout=form&id=' . $row->id . '&Itemid=' . $Itemid,
		];

		//Notification email send to user
		if ($fieldSuffix && strlen($message->{'submit_event_user_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'submit_event_user_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->submit_event_user_email_subject;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'submit_event_user_email_body' . $fieldSuffix}))
		{
			$body = $message->{'submit_event_user_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->submit_event_user_email_body;
		}

		if ($subject)
		{
			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$subject = str_ireplace("[$key]", $value, $subject);
				$body    = str_ireplace("[$key]", $value, $body);
			}

			$body = EventbookingHelper::convertImgTags($body);

			if (MailHelper::isEmailAddress($user->email))
			{
				static::send($mailer, [$user->email], $subject, $body, $logEmails, 2, 'new_event_notification_emails');
				$mailer->clearAllRecipients();
			}
		}

		$emails = explode(',', $config->notification_emails);
		$emails = array_map('trim', $emails);

		if (strlen($message->{'submit_event_admin_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'submit_event_admin_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->submit_event_admin_email_subject;
		}

		if (!$subject)
		{
			return;
		}

		if (EventbookingHelper::isValidMessage($message->{'submit_event_admin_email_body' . $fieldSuffix}))
		{
			$body = $message->{'submit_event_admin_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->submit_event_admin_email_body;
		}

		$replaces['event_link'] = Uri::root() . 'administrator/index.php?option=com_eventbooking&view=event&id=' . $row->id;

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'new_event_notification_emails');
	}

	/**
	 * Send new event notification email to admin and users when new event is submitted in the frontend
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   RADConfig               $config
	 * @param   JUser                   $eventCreator
	 */
	public static function sendEventApprovedEmail($row, $config, $eventCreator)
	{
		$logEmails = static::loggingEnabled('event_approved_emails', $config);

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->created_language);
		$Itemid      = EventbookingHelper::getItemid();

		$mailer = static::getMailer($config);

		$replaces = [
			'username'    => $eventCreator->username,
			'name'        => $eventCreator->name,
			'email'       => $eventCreator->email,
			'event_id'    => $row->id,
			'event_title' => $row->title,
			'event_date'  => HTMLHelper::_('date', $row->event_date, $config->event_date_format, null),
			'event_link'  => Uri::root() . 'index.php?option=com_eventbooking&view=event&layout=form&id=' . $row->id . '&Itemid=' . $Itemid,
		];

		//Notification email send to user
		if ($fieldSuffix && strlen($message->{'event_approved_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'event_approved_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->event_approved_email_subject;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'event_approved_email_body' . $fieldSuffix}))
		{
			$body = $message->{'event_approved_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->event_approved_email_body;
		}


		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, [$eventCreator->email], $subject, $body, $logEmails, 2, 'event_approved_emails');
	}

	/**
	 * Send notification email to admin when users update their event from frontend
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   RADConfig               $config
	 * @param   JUser                   $eventCreator
	 */
	public static function sendEventUpdateEmail($row, $config)
	{
		$logEmails = static::loggingEnabled('event_update_emails', $config);

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->created_language);

		$eventCreator = JUser::getInstance($row->created_by);

		$mailer = static::getMailer($config);

		$replaces = [
			'username'    => $eventCreator->username,
			'name'        => $eventCreator->name,
			'email'       => $eventCreator->email,
			'event_id'    => $row->id,
			'event_title' => $row->title,
			'event_date'  => HTMLHelper::_('date', $row->event_date, $config->event_date_format, null),
			'event_link'  => Uri::root() . 'administrator/index.php?option=com_eventbooking&view=event&id=' . $row->id,
		];

		//Notification email send to user
		if ($fieldSuffix && strlen($message->{'event_update_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'event_update_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->event_update_email_subject;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'event_update_email_body' . $fieldSuffix}))
		{
			$body = $message->{'event_update_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->event_update_email_body;
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		$emails = explode(',', $config->notification_emails);
		$emails = array_map('trim', $emails);


		static::send($mailer, $emails, $subject, $body, $logEmails, 2, 'event_update_emails');
	}

	/**
	 * Send reminder email to registrants
	 *
	 * @param   int       $numberEmailSendEachTime
	 * @param   string    $bccEmail
	 * @param   Registry  $params
	 *
	 */
	public static function sendReminder($numberEmailSendEachTime = 0, $bccEmail = null, $params = null)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendReminder'))
		{
			EventbookingHelperOverrideMail::sendReminder($numberEmailSendEachTime, $bccEmail, $params);

			return;
		}

		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$config  = EventbookingHelper::getConfig();
		$message = EventbookingHelper::getMessages();
		$mailer  = static::getMailer($config);
		$now     = $db->quote(Factory::getDate('now', Factory::getApplication()->get('offset'))->toSql(true));

		if ($params == null)
		{
			$params = new Registry;
		}

		EventbookingHelper::loadLanguage();

		if ($bccEmail)
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

		if (!$numberEmailSendEachTime)
		{
			$numberEmailSendEachTime = 15;
		}

		$hourFrequencyConditionReminderBefore = "b.send_first_reminder >= TIMESTAMPDIFF(HOUR, $now, b.event_date) AND TIMESTAMPDIFF(HOUR, $now, b.event_date)>=0";
		$dayFrequencyConditionReminderBefore  = "b.send_first_reminder >= DATEDIFF(b.event_date, $now) AND DATEDIFF(b.event_date, $now) >= 0";
		$dayFrequencyConditionReminderAfter   = "DATEDIFF($now, b.event_date) >= ABS(b.send_first_reminder) AND DATEDIFF($now, b.event_date) <= 60";
		$hourFrequencyConditionReminderAfter  = "TIMESTAMPDIFF(HOUR, b.event_date, $now) >= ABS(b.send_first_reminder) AND TIMESTAMPDIFF(HOUR, b.event_date, $now) <= 100";

		$query->select('a.*')
			->select('b.from_name, b.from_email, b.reminder_email_body')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.is_reminder_sent = 0')
			->where('b.send_first_reminder != 0')
			->where("IF(b.send_first_reminder > 0, IF(b.first_reminder_frequency = 'd', $dayFrequencyConditionReminderBefore, $hourFrequencyConditionReminderBefore), IF(b.first_reminder_frequency = 'd', $dayFrequencyConditionReminderAfter, $hourFrequencyConditionReminderAfter))")
			->order('b.event_date, a.register_date');

		if (!$params->get('send_to_group_billing', 1))
		{
			$query->where('a.is_group_billing = 0');
		}

		if (!$params->get('send_to_group_members', 1))
		{
			$query->where('a.group_id = 0');
		}

		if (!$params->get('send_to_unpublished_events', 0))
		{
			$query->where('b.published = 1');
		}

		if ($params->get('only_send_to_paid_registrants', 0))
		{
			$query->where('a.published = 1');
		}
		else
		{
			$query->where('(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published = 0))');
		}

		if ($params->get('only_send_to_checked_in_registrants', 0))
		{
			$query->where('a.checked_in = 1');
		}

		$db->setQuery($query, 0, $numberEmailSendEachTime);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (Exception  $e)
		{
			$rows = [];
		}

		$logEmails = static::loggingEnabled('reminder_emails', $config);

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row = $rows[$i];

			if (!MailHelper::isEmailAddress($row->email))
			{
				continue;
			}

			$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

			if ($fieldSuffix && strlen($message->{'reminder_email_subject' . $fieldSuffix}))
			{
				$emailSubject = $message->{'reminder_email_subject' . $fieldSuffix};
			}
			else
			{
				$emailSubject = $message->reminder_email_subject;
			}

			if (EventbookingHelper::isValidMessage($row->reminder_email_body))
			{
				$emailBody = $row->reminder_email_body;
			}
			elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'reminder_email_body' . $fieldSuffix}))
			{
				$emailBody = $message->{'reminder_email_body' . $fieldSuffix};
			}
			else
			{
				$emailBody = $message->reminder_email_body;
			}

			$replaces = EventbookingHelperRegistration::getRegistrationReplaces($row);

			foreach ($replaces as $key => $value)
			{
				$emailSubject = str_replace('[' . strtoupper($key) . ']', $value, $emailSubject);
				$emailBody    = str_replace('[' . strtoupper($key) . ']', $value, $emailBody);
			}

			$emailBody = EventbookingHelperRegistration::processQRCODE($row, $emailBody);
			$emailBody = EventbookingHelper::convertImgTags($emailBody);

			if ($row->from_name && MailHelper::isEmailAddress($row->from_email))
			{
				$mailer->setSender([$row->from_email, $row->from_name]);
				$useEventSenderSettings = true;
			}
			else
			{
				$useEventSenderSettings = false;
			}

			static::send($mailer, [$row->email], $emailSubject, $emailBody, $logEmails, 2, 'reminder_emails');

			$mailer->clearAddresses();

			if ($useEventSenderSettings)
			{
				// Restore original sender setting
				$mailer->setSender([static::$fromEmail, static::$fromName]);
			}

			$query->clear()
				->update('#__eb_registrants')
				->set('is_reminder_sent = 1')
				->where('id = ' . (int) $row->id);
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Send reminder email to registrants
	 *
	 * @param   int       $numberEmailSendEachTime
	 * @param   string    $bccEmail
	 * @param   Registry  $params
	 *
	 */
	public static function sendSecondReminder($numberEmailSendEachTime = 0, $bccEmail = null, $params = null)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendSecondReminder'))
		{
			EventbookingHelperOverrideMail::sendSecondReminder($numberEmailSendEachTime, $bccEmail, $params);

			return;
		}

		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$config  = EventbookingHelper::getConfig();
		$message = EventbookingHelper::getMessages();
		$mailer  = static::getMailer($config);
		$now     = $db->quote(Factory::getDate('now', Factory::getApplication()->get('offset'))->toSql(true));

		if ($params == null)
		{
			$params = new Registry;
		}

		EventbookingHelper::loadLanguage();

		if ($bccEmail && MailHelper::isEmailAddress($bccEmail))
		{
			$mailer->addBcc($bccEmail);
		}

		if (!$numberEmailSendEachTime)
		{
			$numberEmailSendEachTime = 15;
		}

		$hourFrequencyConditionReminderBefore = "b.send_second_reminder >= TIMESTAMPDIFF(HOUR, $now, b.event_date) AND TIMESTAMPDIFF(HOUR, $now, b.event_date)>=0";
		$dayFrequencyConditionReminderBefore  = "b.send_second_reminder >= DATEDIFF(b.event_date, $now) AND DATEDIFF(b.event_date, $now) >= 0";
		$dayFrequencyConditionReminderAfter   = "DATEDIFF($now, b.event_date) >= ABS(b.send_second_reminder) AND DATEDIFF($now, b.event_date) <= 60";
		$hourFrequencyConditionReminderAfter  = "TIMESTAMPDIFF(HOUR, b.event_date, $now) >= ABS(b.send_second_reminder) AND TIMESTAMPDIFF(HOUR, b.event_date, $now) <= 100";

		$query->select('a.*')
			->select('b.from_name, b.from_email, b.second_reminder_email_body')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->leftJoin('#__eb_locations AS c ON b.location_id = c.id')
			->where('a.is_second_reminder_sent = 0')
			->where('b.send_second_reminder != 0')
			->where("IF(b.send_second_reminder > 0, IF(b.second_reminder_frequency = 'd', $dayFrequencyConditionReminderBefore, $hourFrequencyConditionReminderBefore), IF(b.second_reminder_frequency = 'd', $dayFrequencyConditionReminderAfter, $hourFrequencyConditionReminderAfter))")
			->order('b.event_date, a.register_date');

		if (!$params->get('send_to_group_billing', 1))
		{
			$query->where('a.is_group_billing = 0');
		}

		if (!$params->get('send_to_group_members', 1))
		{
			$query->where('a.group_id = 0');
		}

		if (!$params->get('send_to_unpublished_events', 0))
		{
			$query->where('b.published = 1');
		}

		if ($params->get('only_send_to_checked_in_registrants', 0))
		{
			$query->where('a.checked_in = 1');
		}

		if ($params->get('only_send_to_paid_registrants', 0))
		{
			$query->where('a.published = 1');
		}
		else
		{
			$query->where('(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published = 0))');
		}

		$db->setQuery($query, 0, $numberEmailSendEachTime);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (Exception  $e)
		{
			$rows = [];
		}

		$logEmails = static::loggingEnabled('reminder_emails', $config);

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row = $rows[$i];

			if (!MailHelper::isEmailAddress($row->email))
			{
				continue;
			}

			$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

			if ($fieldSuffix && strlen($message->{'second_reminder_email_subject' . $fieldSuffix}))
			{
				$emailSubject = $message->{'second_reminder_email_subject' . $fieldSuffix};
			}
			else
			{
				$emailSubject = $message->second_reminder_email_subject;
			}

			if (EventbookingHelper::isValidMessage($row->second_reminder_email_body))
			{
				$emailBody = $row->second_reminder_email_body;
			}
			elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'second_reminder_email_body' . $fieldSuffix}))
			{
				$emailBody = $message->{'second_reminder_email_body' . $fieldSuffix};
			}
			else
			{
				$emailBody = $message->second_reminder_email_body;
			}

			$replaces = EventbookingHelperRegistration::getRegistrationReplaces($row);

			foreach ($replaces as $key => $value)
			{
				$emailSubject = str_ireplace('[' . strtoupper($key) . ']', $value, $emailSubject);
				$emailBody    = str_ireplace('[' . strtoupper($key) . ']', $value, $emailBody);
			}

			if ($row->from_name && MailHelper::isEmailAddress($row->from_email))
			{
				$useEventSenderSettings = true;
				$mailer->setSender([$row->from_email, $row->from_name]);
			}
			else
			{
				$useEventSenderSettings = false;
			}

			$emailBody = EventbookingHelperRegistration::processQRCODE($row, $emailBody);
			$emailBody = EventbookingHelper::convertImgTags($emailBody);

			static::send($mailer, [$row->email], $emailSubject, $emailBody, $logEmails, 2, 'reminder_emails');
			$mailer->clearAddresses();

			if ($useEventSenderSettings)
			{
				// Restore original sender for mailer object
				$mailer->setSender([static::$fromEmail, static::$fromName]);
			}

			$query->clear()
				->update('#__eb_registrants')
				->set('is_second_reminder_sent = 1')
				->where('id = ' . (int) $row->id);
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Send deposit payment reminder email to registrants
	 *
	 * @param   int     $numberDays
	 * @param   int     $numberEmailSendEachTime
	 * @param   string  $bccEmail
	 */
	public static function sendDepositReminder($numberDays, $numberEmailSendEachTime = 0, $bccEmail = null)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendDepositReminder'))
		{
			EventbookingHelperOverrideMail::sendDepositReminder($numberDays, $numberEmailSendEachTime, $bccEmail);

			return;
		}

		$config = EventbookingHelper::getConfig();

		$logEmails = static::loggingEnabled('deposit_payment_reminder_emails', $config);


		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$config  = EventbookingHelper::getConfig();
		$message = EventbookingHelper::getMessages();
		$mailer  = static::getMailer($config);
		$Itemid  = EventbookingHelper::getItemid();
		$siteUrl = EventbookingHelper::getSiteUrl();

		if ($bccEmail)
		{
			$mailer->addBcc($bccEmail);
		}

		if (!$numberDays)
		{
			$numberDays = 7;
		}

		if (!$numberEmailSendEachTime)
		{
			$numberEmailSendEachTime = 15;
		}

		$query->select('a.*, b.from_name, b.from_email')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('b.deposit_amount > 0')
			->where('(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published = 0))')
			->where('a.payment_status != 1')
			->where('a.group_id = 0')
			->where('a.is_deposit_payment_reminder_sent = 0')
			->where('b.published = 1')
			->where('DATEDIFF(b.event_date, NOW()) <= ' . $numberDays)
			->where('DATEDIFF(b.event_date, NOW()) >= 0')
			->order('b.event_date, a.register_date');

		$db->setQuery($query, 0, $numberEmailSendEachTime);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (Exception  $e)
		{
			$rows = [];
		}

		foreach ($rows as $row)
		{
			if (!MailHelper::isEmailAddress($row->email))
			{
				continue;
			}

			$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

			if ($fieldSuffix && strlen($message->{'deposit_payment_reminder_email_subject' . $fieldSuffix}))
			{
				$emailSubject = $message->{'deposit_payment_reminder_email_subject' . $fieldSuffix};
			}
			else
			{
				$emailSubject = $message->deposit_payment_reminder_email_subject;
			}

			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'deposit_payment_reminder_email_body' . $fieldSuffix}))
			{
				$emailBody = $message->{'deposit_payment_reminder_email_subject' . $fieldSuffix};
			}
			else
			{
				$emailBody = $message->deposit_payment_reminder_email_body;
			}

			$replaces                         = EventbookingHelperRegistration::getRegistrationReplaces($row);
			$replaces['amount']               = EventbookingHelper::formatCurrency($row->amount - $row->deposit_amount, $config, $row->currency_symbol);
			$replaces['deposit_payment_link'] = $siteUrl . 'index.php?option=com_eventbooking&view=payment&amp;order_number=' . $row->registration_code . '&Itemid=' . $Itemid;

			foreach ($replaces as $key => $value)
			{
				$emailSubject = str_replace('[' . strtoupper($key) . ']', $value, $emailSubject);
				$emailBody    = str_replace('[' . strtoupper($key) . ']', $value, $emailBody);
			}

			if ($row->from_name && MailHelper::isEmailAddress($row->from_email))
			{
				$useEventSenderSetting = true;
				$mailer->setSender([$row->from_email, $row->from_name]);
			}
			else
			{
				$useEventSenderSetting = false;
			}

			$emailBody = EventbookingHelper::convertImgTags($emailBody);
			static::send($mailer, [$row->email], $emailSubject, $emailBody, $logEmails, 1, 'deposit_payment_reminder_emails');
			$mailer->clearAddresses();

			if ($useEventSenderSetting)
			{
				$mailer->setSender([static::$fromEmail, static::$fromName]);
			}

			$query->clear()
				->update('#__eb_registrants')
				->set('is_deposit_payment_reminder_sent = 1')
				->where('id = ' . (int) $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}


	/**
	 * Send deposit payment reminder email to registrants
	 *
	 * @param   int        $numberDaysToSendReminder
	 * @param   int        $numberRegistrants
	 * @param   JRegistry  $params
	 */
	public static function sendOfflinePaymentReminder($numberDaysToSendReminder, $numberRegistrants, $params)
	{
		$config = EventbookingHelper::getConfig();

		$logEmails = static::loggingEnabled('offline_payment_reminder_emails', $config);
		$baseOn    = $params->get('base_on', 0);


		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$config  = EventbookingHelper::getConfig();
		$message = EventbookingHelper::getMessages();
		$mailer  = static::getMailer($config);
		$query->select('a.*, b.from_name, b.from_email')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.published = 0')
			->where('a.group_id = 0')
			->where('a.payment_method LIKE "os_offline%"')
			->where('a.is_offline_payment_reminder_sent = 0')
			->order('a.register_date');

		if ($baseOn == 0)
		{
			$query->where('DATEDIFF(NOW(), a.register_date) >= ' . $numberDaysToSendReminder)
				->where('(DATEDIFF(b.event_date, NOW()) > 0 OR DATEDIFF(b.cut_off_date, NOW()) > 0)');
		}
		else
		{
			$query->where('DATEDIFF(b.event_date, NOW()) <= ' . $numberDaysToSendReminder)
				->where('DATEDIFF(b.event_date, NOW()) >= 0');
		}

		$eventIds = array_filter(ArrayHelper::toInteger($params->get('event_ids')));

		if (count($eventIds))
		{
			$query->where('a.event_id IN (' . implode(',', $eventIds) . ')');
		}

		$db->setQuery($query, 0, $numberRegistrants);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (Exception  $e)
		{
			$rows = [];
		}

		// Load component language
		EventbookingHelper::loadLanguage();

		foreach ($rows as $row)
		{
			if (!MailHelper::isEmailAddress($row->email))
			{
				continue;
			}

			$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

			if ($fieldSuffix && strlen($message->{'offline_payment_reminder_email_subject' . $fieldSuffix}))
			{
				$emailSubject = $message->{'offline_payment_reminder_email_subject' . $fieldSuffix};
			}
			else
			{
				$emailSubject = $message->offline_payment_reminder_email_subject;
			}

			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'offline_payment_reminder_email_body' . $fieldSuffix}))
			{
				$emailBody = $message->{'offline_payment_reminder_email_body' . $fieldSuffix};
			}
			else
			{
				$emailBody = $message->offline_payment_reminder_email_body;
			}

			$replaces = EventbookingHelperRegistration::getRegistrationReplaces($row);

			foreach ($replaces as $key => $value)
			{
				$emailSubject = str_replace('[' . strtoupper($key) . ']', $value, $emailSubject);
				$emailBody    = str_replace('[' . strtoupper($key) . ']', $value, $emailBody);
			}

			if ($row->from_name && MailHelper::isEmailAddress($row->from_email))
			{
				$useEventSenderSetting = true;
				$mailer->setSender([$row->from_email, $row->from_name]);
			}
			else
			{
				$useEventSenderSetting = false;
			}

			$emailBody = EventbookingHelper::convertImgTags($emailBody);
			static::send($mailer, [$row->email], $emailSubject, $emailBody, $logEmails, 1, 'offline_payment_reminder_emails');
			$mailer->clearAddresses();

			if ($useEventSenderSetting)
			{
				$mailer->setSender([static::$fromEmail, static::$fromName]);
			}

			$query->clear()
				->update('#__eb_registrants')
				->set('is_offline_payment_reminder_sent = 1')
				->where('id = ' . (int) $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Send event cancel emails to registrants
	 *
	 * @param   array  $rows
	 */
	public static function sendEventCancelEmails($rows)
	{
		$message = EventbookingHelper::getMessages();
		$config  = EventbookingHelper::getConfig();

		$logEmails = static::loggingEnabled('event_cancel_emails', $config);

		$mailer = static::getMailer($config);

		// Load component language
		EventbookingHelper::loadLanguage();

		foreach ($rows as $row)
		{
			if (!MailHelper::isEmailAddress($row->email))
			{
				continue;
			}

			$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

			if ($fieldSuffix && strlen($message->{'event_cancel_email_subject' . $fieldSuffix}))
			{
				$emailSubject = $message->{'event_cancel_email_subject' . $fieldSuffix};
			}
			else
			{
				$emailSubject = $message->event_cancel_email_subject;
			}

			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'event_cancel_email_body' . $fieldSuffix}))
			{
				$emailBody = $message->{'event_cancel_email_body' . $fieldSuffix};
			}
			else
			{
				$emailBody = $message->event_cancel_email_body;
			}

			$replaces = EventbookingHelperRegistration::getRegistrationReplaces($row);

			foreach ($replaces as $key => $value)
			{
				$emailSubject = str_replace('[' . strtoupper($key) . ']', $value, $emailSubject);
				$emailBody    = str_replace('[' . strtoupper($key) . ']', $value, $emailBody);
			}

			$emailBody = EventbookingHelper::convertImgTags($emailBody);
			static::send($mailer, [$row->email], $emailSubject, $emailBody, $logEmails, 1, 'event_cancel_emails');
			$mailer->clearAddresses();
		}
	}

	/**
	 * Create and initialize mailer object from configuration data
	 *
	 * @param   RADConfig               $config
	 * @param   EventbookingTableEvent  $event
	 *
	 * @return JMail
	 */
	public static function getMailer($config, $event = null)
	{
		$mailer = Factory::getMailer();

		if ($config->reply_to_email)
		{
			$mailer->addReplyTo($config->reply_to_email);
		}

		if ($event && $event->from_name && MailHelper::isEmailAddress($event->from_email))
		{
			$fromName  = $event->from_name;
			$fromEmail = $event->from_email;
		}
		elseif ($config->from_name && MailHelper::isEmailAddress($config->from_email))
		{
			$fromName  = $config->from_name;
			$fromEmail = $config->from_email;
		}
		else
		{
			$fromName  = Factory::getApplication()->get('fromname');
			$fromEmail = Factory::getApplication()->get('mailfrom');
		}

		$mailer->setSender([$fromEmail, $fromName]);

		$mailer->isHtml(true);

		if (empty($config->notification_emails))
		{
			$config->notification_emails = $fromEmail;
		}

		static::$fromName  = $fromName;
		static::$fromEmail = $fromEmail;

		return $mailer;
	}

	/**
	 * Add event's attachments to mailer object for sending emails to registrants
	 *
	 * @param   JMail                        $mailer
	 * @param   EventbookingTableRegistrant  $row
	 * @param   EventbookingTableEvent       $event
	 * @param   RADConfig                    $config
	 *
	 * @return array
	 */
	public static function addEventAttachments($mailer, $row, $event, $config)
	{
		$attachments = [];

		if ($config->multiple_booking)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('attachment')
				->from('#__eb_events')
				->where('id IN (SELECT event_id FROM #__eb_registrants AS a WHERE a.id=' . $row->id . ' OR a.cart_id=' . $row->id . ' ORDER BY a.id)');
			$db->setQuery($query);
			$attachmentFiles = $db->loadColumn();
		}
		elseif ($event->attachment)
		{
			$attachmentFiles = [$event->attachment];
		}
		else
		{
			$attachmentFiles = [];
		}

		// Remove empty value from array
		$attachmentFiles = array_filter($attachmentFiles);
		$attachmentsPath = JPATH_ROOT . '/' . ($config->attachments_path ?: 'media/com_eventbooking') . '/';

		// Add all valid attachments to email
		foreach ($attachmentFiles as $attachmentFile)
		{
			$files = explode('|', $attachmentFile);

			foreach ($files as $file)
			{
				$filePath = $attachmentsPath . $file;

				if ($file && file_exists($filePath))
				{
					$mailer->addAttachment($filePath);
					$attachments[] = $filePath;
				}
			}
		}

		return $attachments;
	}

	/**
	 * Add file uploads to the mailer object for sending to administrator
	 *
	 * @param   JMail  $mailer
	 * @param   array  $rowFields
	 * @param   array  $replaces
	 */
	public static function addRegistrationFormAttachments($mailer, $rowFields, $replaces)
	{
		$attachmentsPath = JPATH_ROOT . '/media/com_eventbooking/files/';

		for ($i = 0, $n = count($rowFields); $i < $n; $i++)
		{
			$rowField = $rowFields[$i];

			if ($rowField->fieldtype == 'File')
			{
				if (isset($replaces[$rowField->name]))
				{
					$fileName = $replaces[$rowField->name];

					if ($fileName && file_exists($attachmentsPath . '/' . $fileName))
					{
						$pos = strpos($fileName, '_');

						if ($pos !== false)
						{
							$originalFilename = substr($fileName, $pos + 1);
						}
						else
						{
							$originalFilename = $fileName;
						}

						$mailer->addAttachment($attachmentsPath . '/' . $fileName, $originalFilename);
					}
				}
			}
		}
	}

	/**
	 * Generate PDF tickets and add to registration email
	 *
	 * @param   JMail                        $mailer
	 * @param   EventbookingTableRegistrant  $row
	 * @param   array                        $rowMembers
	 * @param   RADConfig                    $config
	 *
	 */
	protected static function addRegistrationTickets($mailer, $row, $rowMembers, $config)
	{
		if ($config->get('multiple_booking'))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from('#__eb_registrants')
				->where('id = ' . $row->id . ' OR cart_id = ' . $row->id);
			$db->setQuery($query);
			$rowRegistrants = $db->loadObjectList();

			foreach ($rowRegistrants as $rowRegistrant)
			{
				if (!$rowRegistrant->ticket_code)
				{
					continue;
				}

				$ticketFilePaths = EventbookingHelper::callOverridableHelperMethod('Ticket', 'generateRegistrationTicketsPDF', [$rowRegistrant, $config]);

				foreach ($ticketFilePaths as $ticketFilePath)
				{
					$mailer->addAttachment($ticketFilePath);
				}
			}
		}
		else
		{
			if ($row->ticket_code && $row->payment_status == 1)
			{
				if (count($rowMembers))
				{
					foreach ($rowMembers as $rowMember)
					{
						$ticketFilePaths = EventbookingHelper::callOverridableHelperMethod('Ticket', 'generateRegistrationTicketsPDF', [$rowMember, $config]);

						foreach ($ticketFilePaths as $ticketFilePath)
						{
							$mailer->addAttachment($ticketFilePath);
						}
					}
				}
				else
				{
					$ticketFilePaths = EventbookingHelper::callOverridableHelperMethod('Ticket', 'generateRegistrationTicketsPDF', [$row, $config]);

					foreach ($ticketFilePaths as $ticketFilePath)
					{
						$mailer->addAttachment($ticketFilePath);
					}
				}
			}
		}
	}

	/**
	 * @param   JMail                        $mailer
	 * @param   EventbookingTableRegistrant  $row
	 * @param   EventbookingTableEvent       $event
	 * @param   RADConfig                    $config
	 *
	 * @return string
	 */
	protected static function addRegistrationIcs($mailer, $row, $event, $config)
	{
		$icsFile     = '';
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

		if ($config->multiple_booking)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('a.title, a.event_date, a.event_end_date, a.short_description, b.name')
				->from('#__eb_events AS a')
				->leftJoin('#__eb_locations AS b ON a.location_id =  b.id')
				->innerJoin('#__eb_registrants AS c ON a.id = c.event_id')
				->where("(c.id = $row->id OR c.cart_id = $row->id)")
				->order('c.id');

			if ($fieldSuffix)
			{
				EventbookingHelperDatabase::getMultilingualFields($query, ['a.title', 'a.short_description', 'b.name'], $fieldSuffix);
			}

			$db->setQuery($query);
			$rowEvents = $db->loadObjectList();

			foreach ($rowEvents as $rowEvent)
			{
				$ics = new EventbookingHelperIcs();
				$ics->setEvent($rowEvent)
					->setName($rowEvent->title)
					->setDescription($rowEvent->short_description)
					->setOrganizer(static::$fromEmail, static::$fromName)
					->setStart($rowEvent->event_date)
					->setEnd($rowEvent->event_end_date);

				if ($rowEvent->name)
				{
					$ics->setLocation($rowEvent->name);
				}

				$fileName = ApplicationHelper::stringURLSafe($rowEvent->title) . '.ics';

				$mailer->addAttachment($ics->save(JPATH_ROOT . '/media/com_eventbooking/icsfiles/', $fileName));
			}
		}
		else
		{
			$ics = new EventbookingHelperIcs();
			$ics->setEvent($event)
				->setName($event->title)
				->setDescription($event->short_description)
				->setOrganizer(static::$fromEmail, static::$fromName)
				->setStart($event->event_date)
				->setEnd($event->event_end_date);

			$rowLocation = EventbookingHelperDatabase::getLocation($event->location_id, $fieldSuffix);

			if ($rowLocation)
			{
				$ics->setLocation($rowLocation->name);
			}

			$fileName = ApplicationHelper::stringURLSafe($event->title) . '.ics';
			$mailer->addAttachment($ics->save(JPATH_ROOT . '/media/com_eventbooking/icsfiles/', $fileName));

			$icsFile = JPATH_ROOT . '/media/com_eventbooking/icsfiles/' . $fileName;
		}

		return $icsFile;
	}

	/**
	 * Process sending after all the data has been initialized
	 *
	 * @param   JMail   $mailer
	 * @param   array   $emails
	 * @param   string  $subject
	 * @param   string  $body
	 * @param   bool    $logEmails
	 * @param   int     $sentTo
	 * @param   string  $emailType
	 */
	public static function send($mailer, $emails, $subject, $body, $logEmails = false, $sentTo = 0, $emailType = '')
	{
		if (empty($subject) || empty($body))
		{
			return;
		}

		$emails = array_map('trim', $emails);

		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			if (!MailHelper::isEmailAddress($emails[$i]))
			{
				unset($emails[$i]);
			}
		}

		$emails = array_unique($emails);

		if (count($emails) == 0)
		{
			return;
		}

		$email     = $emails[0];
		$bccEmails = [];
		$mailer->addRecipient($email);

		if (count($emails) > 1)
		{
			unset($emails[0]);
			$bccEmails = $emails;
			$mailer->addBcc($bccEmails);
		}

		$emailBody = EventbookingHelperHtml::loadCommonLayout('emailtemplates/tmpl/email.php', ['body' => $body, 'subject' => $subject]);

		$emailBody = EventbookingHelper::callOverridableHelperMethod('Html', 'processConditionalText', [$emailBody]);

		try
		{
			$mailer->setSubject($subject)
				->setBody($emailBody)
				->Send();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		if ($logEmails)
		{
			$row             = Table::getInstance('Email', 'EventbookingTable');
			$row->sent_at    = Factory::getDate()->toSql();
			$row->email      = $email;
			$row->subject    = $subject;
			$row->body       = $body;
			$row->sent_to    = $sentTo;
			$row->email_type = $emailType;
			$row->store();

			if (count($bccEmails))
			{
				foreach ($bccEmails as $email)
				{
					$row->id    = 0;
					$row->email = $email;
					$row->store();
				}
			}
		}
	}


	/**
	 * Send email to registrant to ask them to make payment for their registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADConfig                    $config
	 *
	 * @return void
	 * @throws Exception
	 */
	public static function sendRequestPaymentEmail($row, $config)
	{
		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendRequestPaymentEmail'))
		{
			EventbookingHelperOverrideMail::sendRequestPaymentEmail($row, $config);

			return;
		}

		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);

		EventbookingHelper::loadComponentLanguage($row->language, true);

		$message = EventbookingHelper::getMessages();

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->id, 4, $row->language);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 1, $row->language);
		}
		else
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 0, $row->language);
		}

		$form = new RADForm($rowFields);
		$data = EventbookingHelperRegistration::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		$replaces = EventbookingHelper::callOverridableHelperMethod('Registration', 'buildTags', [$row, $form, $event, $config], 'Helper');

		if ($row->published == 0)
		{
			if ($fieldSuffix && $message->{'request_payment_email_subject_pdr' . $fieldSuffix})
			{
				$subject = $message->{'request_payment_email_subject_pdr' . $fieldSuffix};
			}
			elseif ($message->request_payment_email_subject_pdr)
			{
				$subject = $message->request_payment_email_subject_pdr;
			}
			elseif ($fieldSuffix && $message->{'request_payment_email_subject' . $fieldSuffix})
			{
				$subject = $message->{'request_payment_email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->request_payment_email_subject;
			}

			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'request_payment_email_body_pdr' . $fieldSuffix}))
			{
				$body = $message->{'request_payment_email_body_pdr' . $fieldSuffix};
			}
			elseif (EventbookingHelper::isValidMessage($message->request_payment_email_body_pdr))
			{
				$body = $message->request_payment_email_body_pdr;
			}
			elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'request_payment_email_body' . $fieldSuffix}))
			{
				$body = $message->{'request_payment_email_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->request_payment_email_body;
			}
		}
		else
		{
			// Deposit payment with partial
			if ($row->deposit_amount > 0 && $row->payment_status != 1)
			{
				if ($fieldSuffix && strlen($message->{'deposit_payment_reminder_email_subject' . $fieldSuffix}))
				{
					$subject = $message->{'deposit_payment_reminder_email_subject' . $fieldSuffix};
				}
				else
				{
					$subject = $message->deposit_payment_reminder_email_subject;
				}

				if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'deposit_payment_reminder_email_body' . $fieldSuffix}))
				{
					$body = $message->{'deposit_payment_reminder_email_subject' . $fieldSuffix};
				}
				else
				{
					$body = $message->deposit_payment_reminder_email_body;
				}

				$replaces['amount'] = EventbookingHelper::formatCurrency($row->amount - $row->deposit_amount, $config, $row->currency_symbol);
			}
			else
			{
				if ($fieldSuffix && $message->{'request_payment_email_subject' . $fieldSuffix})
				{
					$subject = $message->{'request_payment_email_subject' . $fieldSuffix};
				}
				else
				{
					$subject = $message->request_payment_email_subject;
				}

				if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'request_payment_email_body' . $fieldSuffix}))
				{
					$body = $message->{'request_payment_email_body' . $fieldSuffix};
				}
				else
				{
					$body = $message->request_payment_email_body;
				}
			}
		}

		// Make sure subject and message is configured
		if (empty($subject))
		{
			throw new Exception('Please configure request payment email subject in Waiting List Messages tab');
		}

		$mailer = static::getMailer($config, $event);

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		$logEmails = static::loggingEnabled('request_payment_emails', $config);

		static::send($mailer, [$row->email], $subject, $body, $logEmails, 2, 'request_payment_emails');
	}

	/**
	 * Send email to registrant when admin approves his registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADConfig                    $config
	 */
	public static function sendCertificateEmail($row, $config)
	{
		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendCertificateEmail'))
		{
			EventbookingHelperOverrideMail::sendCertificateEmail($row, $config);

			return;
		}

		EventbookingHelper::loadComponentLanguage($row->language, true);

		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);
		$message     = EventbookingHelper::getMessages();
		$mailer      = static::getMailer($config, $event);

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->id, 4, $row->language);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 1, $row->language);
		}
		else
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 0, $row->language);
		}

		$form = new RADForm($rowFields);
		$data = EventbookingHelperRegistration::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		$replaces = EventbookingHelper::callOverridableHelperMethod('Registration', 'buildTags', [$row, $form, $event, $config], 'Helper');

		$subject = $message->certificate_email_subject;
		$body    = $message->certificate_email_body;

		if (empty($subject))
		{
			throw new Exception('Email subject could not be empty. Go to Events Booking -> Emails & Messages and setup Certificate email subject');
		}

		if (empty($body))
		{
			throw new Exception('Email message could not be empty. Go to Events Booking -> Emails & Messages and setup Certificate email body');
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);
		$body = EventbookingHelperRegistration::processQRCODE($row, $body);

		list($fileName, $filePath) = EventbookingHelper::callOverridableHelperMethod('Helper', 'generateCertificates', [[$row], $config]);

		$mailer->addAttachment($filePath, $fileName);

		static::send($mailer, [$row->email], $subject, $body);
	}

	/**
	 * Send email to administrator and user when user cancel his registration
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   RADConfig                    $config
	 */
	public static function sendUserCancelRegistrationEmail($row, $config)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideMail', 'sendUserCancelRegistrationEmail'))
		{
			EventbookingHelperOverrideMail::sendUserCancelRegistrationEmail($row, $config);

			return;
		}

		$logEmails = static::loggingEnabled('registration_cancel_emails', $config);

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$event       = EventbookingHelperDatabase::getEvent($row->event_id, null, $fieldSuffix);

		$mailer = static::getMailer($config, $event);

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->id, 4, $row->language);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 1, $row->language);
		}
		else
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 0, $row->language);
		}

		$form = new RADForm($rowFields);
		$data = EventbookingHelperRegistration::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		$replaces = EventbookingHelper::callOverridableHelperMethod('Registration', 'buildTags', [$row, $form, $event, $config], 'Helper');

		// Do not remove this code. Override event_title to avoid showing multiple event title for shopping cart while only one registration cancelled
		$replaces['event_title'] = $event->title;

		if ($row->published == 4)
		{
			$keyPrefix = 'waiting_list_cancel';
		}
		else
		{
			$keyPrefix = 'registration_cancel';
		}

		if ($fieldSuffix && strlen(trim($message->{$keyPrefix . '_confirmation_email_subject' . $fieldSuffix})))
		{
			$subject = $message->{$keyPrefix . '_confirmation_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->{$keyPrefix . '_confirmation_email_subject'};
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{$keyPrefix . '_confirmation_email_body' . $fieldSuffix}))
		{
			$body = $message->{$keyPrefix . '_confirmation_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->{$keyPrefix . '_confirmation_email_body'};
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, [$row->email], $subject, $body, $logEmails, 2, 'registration_cancel_emails');

		$mailer->clearAllRecipients();

		if ($row->published == 4)
		{
			$subjectKey = 'waiting_list_cancel_notification_email_subject';
			$bodyKey    = 'waiting_list_cancel_notification_email_body';
		}
		else
		{
			$subjectKey = 'registration_cancel_email_subject';
			$bodyKey    = 'registration_cancel_email_body';
		}

		if ($fieldSuffix && strlen(trim($message->{$subjectKey . $fieldSuffix})))
		{
			$subject = $message->{$subjectKey . $fieldSuffix};
		}
		else
		{
			$subject = $message->$subjectKey;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{$bodyKey . $fieldSuffix}))
		{
			$body = $message->{$bodyKey . $fieldSuffix};
		}
		else
		{
			$body = $message->{$bodyKey};
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		// Use notification emails from event if configured
		if (strlen(trim($event->notification_emails)) > 0)
		{
			$config->notification_emails = $event->notification_emails;
		}

		$emails = explode(',', $config->notification_emails);

		if ($config->send_email_to_event_creator)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('email')
				->from('#__users')
				->where('id = ' . (int) $event->created_by);
			$db->setQuery($query);
			$eventCreatorEmail = $db->loadResult();

			if ($eventCreatorEmail && MailHelper::isEmailAddress($eventCreatorEmail))
			{
				$emails[] = $eventCreatorEmail;
			}
		}

		static::send($mailer, $emails, $subject, $body, $logEmails, 1, 'registration_cancel_emails');
	}

	/**
	 * Method to check if the given email type need to be logged
	 *
	 * @param   string     $emailType
	 * @param   RADConfig  $config
	 *
	 * @return bool
	 */
	public static function loggingEnabled($emailType, $config)
	{
		if ($config->get('log_emails'))
		{
			return true;
		}

		if (!empty($config->log_email_types) && in_array($emailType, explode(',', $config->log_email_types)))
		{
			return true;
		}

		return false;
	}
}
