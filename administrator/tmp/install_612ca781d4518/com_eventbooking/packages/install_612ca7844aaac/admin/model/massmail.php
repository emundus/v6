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
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Table\Table;

class EventbookingModelMassmail extends RADModel
{
	/**
	 * Send email to all registrants of event
	 *
	 * @param   RADInput  $input
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function send($input)
	{
		$data = $input->getData();

		if ($data['event_id'] >= 1)
		{
			$config = EventbookingHelper::getConfig();
			$mailer = EventbookingHelperMail::getMailer($config);
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true);

			$published                      = isset($data['published']) ? $data['published'] : -1;
			$sendToGroupBilling             = isset($data['send_to_group_billing']) ? $data['send_to_group_billing'] : 1;
			$sendToGroupMembers             = isset($data['send_to_group_members']) ? $data['send_to_group_members'] : 1;
			$onlySendToCheckedinRegistrants = isset($data['only_send_to_checked_in_registrants']) ? $data['only_send_to_checked_in_registrants'] : 0;

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


			if (!empty($data['bcc_email']))
			{
				$bccEmails = explode(',', $data['bcc_email']);

				$bccEmails = array_map('trim', $bccEmails);

				foreach ($bccEmails as $bccEmail)
				{
					if (MailHelper::isEmailAddress($bccEmail))
					{
						$mailer->addBcc($bccEmail);
					}
				}
			}

			// Load frontend language file
			$defaultLanguage = EventbookingHelper::getDefaultLanguage();
			EventbookingHelper::loadComponentLanguage($defaultLanguage, true);
			$loadedLanguages = [$defaultLanguage];
			$loadedEvents    = [];

			$event = EventbookingHelperDatabase::getEvent((int) $data['event_id']);

			$eventDate = Factory::getDate($event->event_date, Factory::getApplication()->get('offset'));
			$todayDate = Factory::getDate('now', Factory::getApplication()->get('offset'));

			if ($todayDate >= $eventDate)
			{
				$sendIcs = false;
			}
			else
			{
				$sendIcs = true;
			}


			if ($event->from_name && MailHelper::isEmailAddress($event->from_email))
			{
				$mailer->setSender([$event->from_email, $event->from_name]);
			}

			$query->clear()
				->select('*')
				->from('#__eb_registrants')
				->where('event_id = ' . (int) $data['event_id']);

			if ($published == -1)
			{
				$query->where('(published=1 OR (published = 0 AND payment_method LIKE "os_offline%"))');
			}
			else
			{
				$query->where('published = ' . $published);
			}

			if (!$sendToGroupBilling)
			{
				$query->where('is_group_billing = 0');
			}

			if (!$sendToGroupMembers)
			{
				$query->where('group_id = 0');
			}

			if ($onlySendToCheckedinRegistrants)
			{
				$query->where('checked_in = 1');
			}

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// Attach ICS file
			if ($sendIcs && $config->send_ics_file)
			{
				$ics = new EventbookingHelperIcs();
				$ics->setName($event->title)
					->setDescription($event->short_description)
					->setOrganizer(EventbookingHelperMail::$fromEmail, EventbookingHelperMail::$fromName)
					->setStart($event->event_date)
					->setEnd($event->event_end_date);

				if (!empty($location))
				{
					$ics->setLocation($location->name);
				}

				$fileName = ApplicationHelper::stringURLSafe($event->title) . '.ics';
				$mailer->addAttachment($ics->save(JPATH_ROOT . '/media/com_eventbooking/icsfiles/', $fileName));
			}

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
				$email = $row->email;

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

				$replaces                = EventbookingHelperRegistration::getRegistrationReplaces($row, $event, $row->user_id, $config->multiple_booking);
				$replaces['event_title'] = $event->title;

				$subject = $data['subject'];
				$message = $data['description'];

				foreach ($replaces as $key => $value)
				{
					$key     = strtoupper($key);
					$subject = str_ireplace("[$key]", $value, $subject);
					$message = str_ireplace("[$key]", $value, $message);
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
			}
		}

		return true;
	}
}
