<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.syncplugin', JPATH_ADMINISTRATOR);
if (!class_exists('DPCalendarSyncPlugin'))
{
	return;
}

class PlgDPCalendarDPCalendar_Exchange extends DPCalendarSyncPlugin
{

	protected $identifier = 'ec';

	private $attachmentCache = array();

	protected function getSyncToken($calendar)
	{
		JLoader::discover('EWSType_',
				JPATH_PLUGINS . DS . 'dpcalendar' . DS . 'dpcalendar_exchange' . DS . 'libraries' . DS . 'php-ews' . DS . 'EWSType');
		JLoader::discover('NTLMSoapClient_',
				JPATH_PLUGINS . DS . 'dpcalendar' . DS . 'dpcalendar_exchange' . DS . 'libraries' . DS . 'php-ews' . DS . 'NTLMSoapClient');
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.ExchangeWebServices', JPATH_PLUGINS);
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.EWSType', JPATH_PLUGINS);
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.NTLMSoapClient', JPATH_PLUGINS);
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.EWS_Exception', JPATH_PLUGINS);
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.EWSAutodiscover', JPATH_PLUGINS);

		$params = $calendar->params;
		$ews = new ExchangeWebServices($params->get('host'), $params->get('username'), $params->get('password'));

		$request = new EWSType_SyncFolderItemsType();
		$request->SyncState = $calendar->sync_token && $calendar->sync_token != 1 ? $calendar->sync_token : null;
		$request->MaxChangesReturned = 1;
		$request->ItemShape = new EWSType_ItemResponseShapeType();
		$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;

		$request->SyncFolderId = new EWSType_NonEmptyArrayOfBaseFolderIdsType();

		if ($id = $this->getCalendarId($params->get('calendar_name'), $ews))
		{
			$request->SyncFolderId->FolderId = new EWSType_FolderIdType();
			$request->SyncFolderId->FolderId->Id = $id;
		}
		else
		{
			$request->SyncFolderId->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
			$request->SyncFolderId->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::CALENDAR;
		}

		$response = $ews->SyncFolderItems($request);
		$changes = $response->ResponseMessages->SyncFolderItemsResponseMessage->Changes;

		if (!property_exists($changes, 'Create') && !property_exists($changes, 'Update') && !property_exists($changes, 'Delete'))
		{
			return $calendar->sync_token;
		}

		return $response->ResponseMessages->SyncFolderItemsResponseMessage->SyncState;
	}

	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar))
		{
			return '';
		}

		JLoader::discover('EWSType_',
				JPATH_PLUGINS . DS . 'dpcalendar' . DS . 'dpcalendar_exchange' . DS . 'libraries' . DS . 'php-ews' . DS . 'EWSType');
		JLoader::discover('NTLMSoapClient_',
				JPATH_PLUGINS . DS . 'dpcalendar' . DS . 'dpcalendar_exchange' . DS . 'libraries' . DS . 'php-ews' . DS . 'NTLMSoapClient');
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.ExchangeWebServices', JPATH_PLUGINS);
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.EWSType', JPATH_PLUGINS);
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.NTLMSoapClient', JPATH_PLUGINS);
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.EWS_Exception', JPATH_PLUGINS);
		JLoader::import('dpcalendar.dpcalendar_exchange.libraries.php-ews.EWSAutodiscover', JPATH_PLUGINS);

		$params = $calendar->params;

		$text = array();
		$text[] = 'BEGIN:VCALENDAR';
		try
		{
			$ews = new ExchangeWebServices($params->get('host'), $params->get('username'), $params->get('password'), ExchangeWebServices::VERSION_2010);
			$request = new EWSType_FindItemType();

			// Use this to search only the items in the parent directory in
			// question or use ::SOFT_DELETED
			$request->Traversal = EWSType_ItemQueryTraversalType::SHALLOW;

			// This identifies the set of properties to return in an item or
			// folder response
			$request->ItemShape = new EWSType_ItemResponseShapeType();
			$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ID_ONLY;

			$properties = array(
					'calendar:UID',
					'calendar:IsRecurring'
			);
			$request->ItemShape->AdditionalProperties = new EWSType_NonEmptyArrayOfPathsToElementType();
			foreach ($properties as $p)
			{
				$entry = new EWSType_PathToUnindexedFieldType();
				$entry->FieldURI = $p;
				$request->ItemShape->AdditionalProperties->FieldURI[] = $entry;
			}

			// Some EWS servers can't handle the request without proper dates
			if ($startDate == null)
			{
				$startDate = DPCalendarHelper::getDate();
			}
			if ($startDate != null && $endDate == null)
			{
				$endDate = clone $startDate;
				$endDate->modify('+6 months');
			}

			$tmpEndDate = clone $startDate;
			$tmpEndDate->modify('+1 month');
			while (true)
			{
				if ($tmpEndDate->format('U') > $endDate->format('U'))
				{
					$tmpEndDate = $endDate;
				}

				// Define the timeframe to load calendar items
				$request->CalendarView = new EWSType_CalendarViewType();
				$request->CalendarView->StartDate = $startDate->format('c');
				$request->CalendarView->EndDate = $tmpEndDate->format('c');

				// Only look in the "calendars folder"
				$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
				if ($id = $this->getCalendarId($params->get('calendar_name'), $ews))
				{
					$request->ParentFolderIds->FolderId = new EWSType_FolderIdType();
					$request->ParentFolderIds->FolderId->Id = $id;
				}
				else
				{
					$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
					$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::CALENDAR;
				}
				// Send request
				$response = $ews->FindItem($request);

				// Check for errors
				if (isset($response->ResponseMessages->FindItemResponseMessage->ResponseClass) &&
						 $response->ResponseMessages->FindItemResponseMessage->ResponseClass == 'Error')
				{
					$this->log($response->ResponseMessages->FindItemResponseMessage->MessageText);
					return '';
				}

				// Loop through each item if event(s) were found in the
				// timeframe specified
				if ($response->ResponseMessages->FindItemResponseMessage->RootFolder->TotalItemsInView > 0)
				{
					$events = $this->getEvents($response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem, $ews);
					foreach ($events as $eventHolder)
					{
						// Check for errors
						if (isset($eventHolder->ResponseClass) && $eventHolder->ResponseClass == 'Error')
						{
							$this->log($eventHolder->MessageText);
							continue;
						}

						$event = $eventHolder->Items->CalendarItem;
						if (isset($event->IsRecurring) && $event->IsRecurring)
						{
							// We fetch the instances later
							continue;
						}

						$this->appendEvent($event, $text, $ews);

						// Adding modified instances
						if (isset($event->Recurrence) && $event->Recurrence)
						{
							if (isset($event->ModifiedOccurrences))
							{
								$instances = $this->getEvents($event->ModifiedOccurrences->Occurrence, $ews);
								foreach ($instances as $instanceHolder)
								{
									if (isset($instanceHolder->ResponseClass) && $instanceHolder->ResponseClass == 'Error')
									{
										$this->log($instanceHolder->MessageText);
										continue;
									}

									$instance = $instanceHolder->Items->CalendarItem;

									$instance->ItemId->Id = $event->ItemId->Id;
									$instance->OriginalDate = $instance->OriginalStart;
									$this->appendEvent($instance, $text, $ews);
								}
							}
						}
					}
				}
				if ($tmpEndDate == $endDate)
				{
					break;
				}
				$tmpEndDate->modify('+1 month');
				$startDate->modify('+1 month');
			}
			$text[] = 'END:VCALENDAR';
			return $text;
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
			return '';
		}
	}

	private function getEvents($eventIds, $ews)
	{
		if (empty($eventIds))
		{
			return array();
		}
		if (!is_array($eventIds))
		{
			$eventIds = array(
					$eventIds
			);
		}

		// Now fetch all properties of the items
		$request = new EWSType_GetItemType();
		$request->Traversal = EWSType_ItemQueryTraversalType::SHALLOW;
		$request->ItemShape = new EWSType_ItemResponseShapeType();
		$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;

		$properties = array(
				'item:Body',
				'calendar:Recurrence',
				'calendar:ModifiedOccurrences'
		);
		$request->ItemShape->AdditionalProperties = new EWSType_NonEmptyArrayOfPathsToElementType();
		foreach ($properties as $p)
		{
			$entry = new EWSType_PathToUnindexedFieldType();
			$entry->FieldURI = $p;
			$request->ItemShape->AdditionalProperties->FieldURI[] = $entry;
		}
		$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
		$request->ItemIds->ItemId = array();

		// Add the item ids
		$addedEvents = array();
		foreach ($eventIds as $event)
		{
			if (isset($event->UID) && key_exists($event->UID, $addedEvents))
			{
				continue;
			}

			if (isset($event->UID))
			{
				$addedEvents[$event->UID] = $event->UID;
			}

			// If it is a recurring event, just add the master
			if (isset($event->IsRecurring) && $event->IsRecurring)
			{
				if ($request->ItemIds->RecurringMasterItemId === null)
				{
					$request->ItemIds->RecurringMasterItemId = array();
				}
				$masterId = new EWSType_RecurringMasterItemIdType();
				$masterId->OccurrenceId = $event->ItemId->Id;
				$masterId->ChangeKey = $event->ItemId->ChangeKey;
				$request->ItemIds->RecurringMasterItemId[] = $masterId;
			}
			else
			{
				$id = new EWSType_ItemIdType();
				$id->Id = $event->ItemId->Id;
				$id->ChangeKey = $event->ItemId->ChangeKey;
				$request->ItemIds->ItemId[] = $id;
			}
		}
		// Send the request
		$response = $ews->GetItem($request);

		$events = $response->ResponseMessages->GetItemResponseMessage;

		if (!is_array($events))
		{
			$events = array(
					$events
			);
		}

		return $events;
	}

	private function appendEvent($event, &$text, $ews)
	{
		$text[] = 'BEGIN:VEVENT';
		$text[] = 'UID:' . (isset($event->UID) && $event->UID ? $event->UID : md5($event->ItemId->Id));

		$startDate = DPCalendarHelper::getDate($event->Start);
		$endDate = DPCalendarHelper::getDate($event->End);

		if ($event->IsAllDayEvent)
		{
			/*
			 * EWS sends the times in UTC with the full time, means when the
			 * event happens in the timezone Berlin on 20.6. then EWS returns as
			 * start date 19.6. 23:00 or 19.6. 22:00 depending on DST, so we
			 * need the Joomla timezone into action.
			 */

			$dateObj = JFactory::getDate($event->Start);
			$dateObj->setTimezone(new DateTimeZone(JFactory::getApplication()->getCfg('offset', 'UTC')));
			$text[] = 'DTSTART;VALUE=DATE:' . $dateObj->format('Ymd', true);

			$dateObj = JFactory::getDate($event->End);
			$dateObj->setTimezone(new DateTimeZone(JFactory::getApplication()->getCfg('offset', 'UTC')));
			$text[] = 'DTEND;VALUE=DATE:' . $dateObj->format('Ymd', true);
		}
		else
		{
			// Set the timezone to properly handle weekly by day rrules, see
			// Case 2066
			$tz = $startDate->getTimezone()->getName();
			if (empty($tz))
			{
				$tz = 'UTC';
			}
			$text[] = 'DTSTART;TZID=' . $tz . ':' . $startDate->format('Ymd\THis', true);
			$text[] = 'DTEND;TZID=' . $tz . ':' . $endDate->format('Ymd\THis', true);
		}

		if ($event->CalendarItemType == 'RecurringMaster')
		{
			// Rrule
			$rrule = '';
			$rec = $event->Recurrence;
			if (isset($rec->DailyRecurrence))
			{
				$rrule .= 'FREQ=DAILY;';
				if (isset($rec->DailyRecurrence->Interval))
				{
					$rrule .= 'Interval=' . $rec->DailyRecurrence->Interval . ';';
				}
			}
			if (isset($rec->WeeklyRecurrence))
			{
				$rrule .= 'FREQ=WEEKLY;';
				if (isset($rec->WeeklyRecurrence->DaysOfWeek))
				{
					$rrule .= 'BYDAY=';
					foreach (explode(' ', $rec->WeeklyRecurrence->DaysOfWeek) as $day)
					{
						$rrule .= strtoupper(substr($day, 0, 2)) . ',';
					}
					$rrule = trim($rrule, ',') . ';';
				}
				if (isset($rec->WeeklyRecurrence->Interval))
				{
					$rrule .= 'Interval=' . $rec->WeeklyRecurrence->Interval . ';';
				}
			}
			if (isset($rec->AbsoluteMonthlyRecurrence))
			{
				$rrule .= 'FREQ=MONTHLY;';
				if (isset($rec->AbsoluteMonthlyRecurrence->DayOfMonth))
				{
					$rrule .= 'BYMONTHDAY=' . $rec->AbsoluteMonthlyRecurrence->DayOfMonth . ';';
				}
				if (isset($rec->AbsoluteMonthlyRecurrence->Interval))
				{
					$rrule .= 'Interval=' . $rec->AbsoluteMonthlyRecurrence->Interval . ';';
				}
			}

			if (isset($rec->RelativeMonthlyRecurrence))
			{
				$rrule .= 'FREQ=MONTHLY;';
				if (isset($rec->RelativeMonthlyRecurrence->DaysOfWeek))
				{
					$rrule .= 'BYDAY=';
					foreach (explode(' ', $rec->RelativeMonthlyRecurrence->DaysOfWeek) as $day)
					{
						$indexes = array(
								'First' => 1,
								'Second' => 2,
								'Third' => 3,
								'Fourth' => 4,
								'Last' => -1
						);
						$rrule .= $indexes[$rec->RelativeMonthlyRecurrence->DayOfWeekIndex] . strtoupper(substr($day, 0, 2)) . ',';
					}
					$rrule = trim($rrule, ',') . ';';
				}
				if (isset($rec->RelativeMonthlyRecurrence->Interval))
				{
					$rrule .= 'Interval=' . $rec->RelativeMonthlyRecurrence->Interval . ';';
				}
			}

			if (isset($rec->AbsoluteYearlyRecurrence))
			{
				$rrule .= 'FREQ=YEARLY;';
				if (isset($rec->AbsoluteYearlyRecurrence->DayOfMonth))
				{
					$rrule .= 'BYMONTHDAY=' . $rec->AbsoluteYearlyRecurrence->DayOfMonth . ';';
					$indexes = array(
							'January' => 1,
							'February' => 2,
							'March' => 3,
							'April' => 4,
							'May' => 5,
							'June' => 6,
							'July' => 7,
							'August' => 8,
							'September' => 9,
							'October' => 10,
							'November' => 11,
							'December' => 12
					);
					$rrule .= 'BYMONTH=' . $indexes[$rec->AbsoluteYearlyRecurrence->Month] . ';';
				}
			}
			if (isset($rec->RelativeYearlyRecurrence))
			{
				$rrule .= 'FREQ=YEARLY;';
				if (isset($rec->RelativeYearlyRecurrence->DaysOfWeek))
				{
					$indexes = array(
							'First' => 1,
							'Second' => 2,
							'Third' => 3,
							'Fourth' => 4,
							'Last' => -1
					);
					$rrule .= 'BYDAY=' . $indexes[$rec->RelativeYearlyRecurrence->DayOfWeekIndex] .
							 strtoupper(substr($rec->RelativeYearlyRecurrence->DaysOfWeek, 0, 2)) . ';';

					$indexes = array(
							'January' => 1,
							'February' => 2,
							'March' => 3,
							'April' => 4,
							'May' => 5,
							'June' => 6,
							'July' => 7,
							'August' => 8,
							'September' => 9,
							'October' => 10,
							'November' => 11,
							'December' => 12
					);
					$rrule .= 'BYMONTH=' . $indexes[$rec->RelativeYearlyRecurrence->Month] . ';';
				}
			}

			if (isset($rec->NumberedRecurrence->NumberOfOccurrences))
			{
				$rrule .= 'COUNT=' . $rec->NumberedRecurrence->NumberOfOccurrences . ';';
			}
			if (isset($rec->EndDateRecurrence->EndDate))
			{
				$rrule .= 'UNTIL=' . DPCalendarHelper::getDate($rec->EndDateRecurrence->EndDate)->format('Ymd\THis\Z') . ';';
			}
			$text[] = 'RRULE:' . trim($rrule, ';');

			// Exdate
			if (isset($event->DeletedOccurrences))
			{
				$tmp = $event->DeletedOccurrences->DeletedOccurrence;
				if (!is_array($tmp))
				{
					$tmp = array(
							$tmp
					);
				}
				$exDate = '';
				foreach ($tmp as $ocurrence)
				{
					$exDate .= str_replace(array(
							'-',
							':'
					), '', $ocurrence->Start) . ',';
				}
				$text[] = 'EXDATE:' . trim($exDate, ',');
			}
		}
		if ($event->CalendarItemType == 'Exception')
		{
			$text[] = 'RECURRENCE-ID:' . str_replace(array(
					'-',
					':'
			), '', $event->OriginalDate);
		}

		if (!trim($event->Subject))
		{
			$event->Subject = JText::_('COM_DPCALENDAR_EVENT_BUSY');
		}
		$text[] = 'SUMMARY:' . $event->Subject;

		if ($event->Body->BodyType == 'HTML')
		{
			$body = str_replace(array(
					"\r\n",
					"\r",
					"\n"
			), " ", $event->Body->_);

			$attachments = $this->storeAttachments($event, $ews);

			if ($attachments)
			{
				preg_match_all('/src="cid:(.*)"/Uims', $body, $matches);

				if (count($matches))
				{
					$search = array();
					$replace = array();

					foreach ($matches[1] as $key => $match)
					{
						if ($key >= count($attachments))
						{
							break;
						}
						$search[] = 'cid:' . $match;
						$replace[] = $attachments[$key];
					}

					$body = str_replace($search, $replace, $body);
				}

				$fileAttachments = array_slice($attachments, count($matches) ? count($matches[1]) : 0);
				foreach ($fileAttachments as $attachment)
				{
					$body .= '<br/><a href="' . $attachment . '">' . $attachment . '</a>';
				}
			}
			$text[] = 'X-ALT-DESC;FMTTYPE=text/html:' . trim(DPCalendarHelperIcal::icalEncode($body));
		}
		else
		{
			$body = str_replace(array(
					"\r\n",
					"\r",
					"\n"
			), "<br/>", $event->Body->_);
			$text[] = 'DESCRIPTION:' . trim(DPCalendarHelperIcal::icalEncode($body));
		}

		if (isset($event->Location))
		{
			$text[] = 'LOCATION:' . $event->Location;
		}
		if (isset($event->DisplayTo))
		{
			$organizer = explode(';', $event->DisplayTo);
			if (!empty($organizer))
			{
				$text[] = 'ORGANIZER:' . $organizer[0];
			}
		}
		if (isset($event->RequiredAttendees) && isset($event->RequiredAttendees->Attendee))
		{
			$bookings = $event->RequiredAttendees->Attendee;
			if (!is_array($bookings))
			{
				$bookings = array(
						$bookings
				);
			}
			foreach ($bookings as $booking)
			{
				if (!isset($booking->Mailbox))
				{
					continue;
				}
				$organizer = explode(';', $event->DisplayTo);
				if (!empty($organizer))
				{
					$text[] = 'ATTENDEE;ROLE=REQ-PARTICIPANT;CN=' . $booking->Mailbox->Name . ':MAILTO:' . $booking->Mailbox->EmailAddress;
				}
			}
		}
		$text[] = 'END:VEVENT';
	}

	private function storeAttachments($event, $ews)
	{
		$items = array();
		if (!empty($event->Attachments->ItemAttachment))
		{
			// ItemAttachment attribute can either be an array or instance
			// of stdClass...
			if (is_array($event->Attachments->ItemAttachment) === false)
			{
				$items[] = $event->Attachments->ItemAttachment;
			}
			else
			{
				$items = $event->Attachments->ItemAttachment;
			}
		}
		if (!empty($event->Attachments->FileAttachment))
		{
			// FileAttachment attribute can either be an array or instance
			// of stdClass...
			if (is_array($event->Attachments->FileAttachment) === false)
			{
				$items[] = $event->Attachments->FileAttachment;
			}
			else
			{
				$items = $event->Attachments->FileAttachment;
			}
		}

		// Getting the attachments
		$attachments = array();
		$cacheDir = JPATH_ROOT . '/cache/plg_dpcalendar_exchange';

		if (!JFolder::exists($cacheDir))
		{
			JFolder::create($cacheDir, '777');
		}
		$cacheDir = $cacheDir . '/attachments/';
		if (!JFolder::exists($cacheDir))
		{
			JFolder::create($cacheDir, '777');
		}
		foreach ($items as $attachment)
		{
			// Don't create attachments multiple times
			if (key_exists($attachment->AttachmentId->Id, $this->attachmentCache))
			{
				$attachments[] = $this->attachmentCache[$attachment->AttachmentId->Id];
				continue;
			}

			$request = new EWSType_GetAttachmentType();
			$request->AttachmentIds = new EWSType_NonEmptyArrayOfRequestAttachmentIdsType();
			$request->AttachmentIds->AttachmentId = new EWSType_RequestAttachmentIdType();
			$request->AttachmentIds->AttachmentId->Id = $attachment->AttachmentId->Id;
			$response = $ews->GetAttachment($request);

			// ResponseCode
			if ($response->ResponseMessages->GetAttachmentResponseMessage->ResponseClass == 'Success')
			{
				// Assuming response was successful ...
				$data = $response->ResponseMessages->GetAttachmentResponseMessage->Attachments->FileAttachment;
				$content = $data->Content;

				if (!$data->Name)
				{
					$data->Name = md5($data->AttachmentId->Id) . '.png';
				}

				$path = $cacheDir . '/' . $data->Name;
				if ($im = imagecreatefromstring($content))
				{
					$ext = pathinfo($path, PATHINFO_EXTENSION);
					if (!$ext)
					{
						$ext = 'png';
						$path .= '.' . $ext;
						$data->Name .= '.' . $ext;
					}
					switch (strtolower($ext))
					{
						case 'png':
							@imagepng($im, $path);
					}
				}
				else
				{
					JFile::write($path, $content);
				}

				$this->attachmentCache[$attachment->AttachmentId->Id] = JUri::root() . 'cache/plg_dpcalendar_exchange/attachments/' . $data->Name;
				$attachments[] = $this->attachmentCache[$attachment->AttachmentId->Id];
			}
		}
		return $attachments;
	}

	private function getCalendarId($calendarName, $ews)
	{
		if (!$calendarName)
		{
			return null;
		}
		$calendarName = trim(strtolower($calendarName));

		$request = new EWSType_FindFolderType();
		$request->Traversal = EWSType_FolderQueryTraversalType::SHALLOW;
		$request->FolderShape = new EWSType_FolderResponseShapeType();
		$request->FolderShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;

		// configure the view
		$request->IndexedPageFolderView = new EWSType_IndexedPageViewType();
		$request->IndexedPageFolderView->BasePoint = 'Beginning';
		$request->IndexedPageFolderView->Offset = 0;

		$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();

		// use a distinguished folder name to find folders inside it
		$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
		$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::CALENDAR;

		$response = $ews->FindFolder($request);

		// Check for errors
		if (isset($response->ResponseMessages->FindFolderResponseMessage->ResponseClass) &&
				 $response->ResponseMessages->FindFolderResponseMessage->ResponseClass == 'Error')
		{
			$this->log($response->ResponseMessages->FindFolderResponseMessage->MessageText);
			return null;
		}

		// Loop through each item if event(s) were found in the
		// timeframe specified
		if ($response->ResponseMessages->FindFolderResponseMessage->RootFolder->TotalItemsInView > 0)
		{
			$folders = $response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder;
			if (!is_array($folders))
			{
				$folders = array(
						$folders
				);
			}
			foreach ($folders as $cal)
			{
				if (trim(strtolower($cal->DisplayName)) == $calendarName)
				{
					return $cal->FolderId->Id;
				}
			}
		}

		return null;
	}
}
