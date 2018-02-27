<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR)) {
	return;
}

class PlgDPCalendarGoogle extends \DPCalendar\Plugin\SyncPlugin
{
	protected $identifier = 'g';

	protected function getSyncToken($calendar)
	{
		$client = $this->getClient($calendar->params->get('client-id'), $calendar->params->get('client-secret'));
		$client->refreshToken($calendar->params->get('refreshToken'));

		$cal = new Google_Service_Calendar($client);

		$params               = array();
		$params['updatedMin'] = DPCalendarHelper::getDate($calendar->sync_date)->format('c');
		$params['maxResults'] = 1;
		$obj                  = $cal->events->listEvents($calendar->params->get('calendarId'), $params);

		return $obj->etag;
	}

	public function fetchEvent($eventId, $calendarId)
	{
		return $this->fetchEventFromGoogle(false, $eventId, $calendarId);
	}

	private function fetchEventFromGoogle($returnGoogleEvent, $eventId, $calendarId)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return null;
		}

		$params = array();

		try {
			$client = $this->getClient($calendar->params->get('client-id'), $calendar->params->get('client-secret'));
			$client->refreshToken($calendar->params->get('refreshToken'));

			$cal = new Google_Service_Calendar($client);

			$eventId = urldecode($eventId);
			$pos     = strrpos($eventId, '_');
			if ($pos === false) {
				return null;
			}

			$googleEvent = $cal->events->get($calendar->params->get('calendarId'), substr($eventId, 0, $pos), $params);
			if (!$googleEvent) {
				return null;
			}
			$obj = $googleEvent;

			// Check if we need an instance
			$s = substr($eventId, $pos + 1);
			if ($s != '0' && $googleEvent->recurrence) {
				$startDate = null;
				if (strlen($s) == 8) {
					$startDate = JFactory::getDate(substr($s, 0, 4) . '-' . substr($s, 4, 2) . '-' . substr($s, 6, 2) . ' 00:00');
				} else {
					$startDate = JFactory::getDate(
						substr($s, 0, 4) . '-' . substr($s, 4, 2) . '-' . substr($s, 6, 2) . ' ' . substr($s, 8, 2) . ':' . substr($s, 10, 2));
				}

				// Exact date google has problems on recurring events
				$startDate->modify('-1 second');
				$params['timeMin'] = $startDate->format('c');
				$startDate->modify('+1 ' . (strlen($s) == 8 ? 'day' : 'hour'));
				$params['timeMax']    = $startDate->format('c');
				$params['maxResults'] = 1;

				$obj = $cal->events->instances($calendar->params->get('calendarId'), substr($eventId, 0, $pos), $params);
				if (count($obj->items) > 0) {
					$googleEvent = $obj->items[0];
				} else {
					$googleEvent = null;
				}
			}

			if (!$googleEvent) {
				return null;
			}

			if ($returnGoogleEvent) {
				return $googleEvent;
			}

			return $this->createEventFromGoogle($googleEvent, $calendar, $obj, $cal->colors->get()->event);
		} catch (Exception $e) {
			$this->log($e);
		}
	}

	public function fetchEvents($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return array();
		}

		if ($startDate == null) {
			$startDate = DPCalendarHelper::getDate();
		}

		$params = array();
		if ($startDate != null) {
			// Exact date google has problems on recurring events
			$startDate->modify('-1 second');
			$params['timeMin'] = $startDate->format('c');
		}
		if ($endDate != null) {
			$params['timeMax'] = $endDate->format('c');
		}
		if ($options->get('filter')) {
			$params['q'] = $options->get('filter');
		}
		$params['maxResults'] = $options->get('limit', 1000);

		if ($options->get('expand', true)) {
			$params['singleEvents'] = 'true';
			$params['orderBy']      = 'startTime';
		}
		try {
			$client = $this->getClient($calendar->params->get('client-id'), $calendar->params->get('client-secret'));
			$client->refreshToken($calendar->params->get('refreshToken'));

			$cal = new Google_Service_Calendar($client);

			$obj          = $cal->events->listEvents($calendar->params->get('calendarId'), $params);
			$googleEvents = $obj->items;

			$order = strtolower($options->get('order', 'asc'));
			usort($googleEvents,
				function ($event1, $event2) use ($order) {
					if (!$event1->start || !$event2->start) {
						return 0;
					}

					$first  = $event1;
					$second = $event2;
					if (strtolower($order) == 'desc') {
						$first  = $event2;
						$second = $event1;
					}

					return strcmp($first->start->date . ' ' . $first->start->dateTime, $second->start->date . ' ' . $second->start->dateTime);
				});

			$colors = $cal->colors->get()->event;
			$events = array();
			foreach ($googleEvents as $googleEvent) {
				$event = $this->createEventFromGoogle($googleEvent, $calendar, $obj, $colors);
				if (!$event || !$this->matchLocationFilterEvent($event, $options)) {
					continue;
				}

				$events[] = $event;
			}

			return $events;
		} catch (Exception $e) {
			$this->log($e);
		}
	}

	protected function getIcalUrl($calendar)
	{
		return 'https://calendar.google.com/calendar/ical/' . $calendar->params->get('calendarId') . '/public/basic.ics';
	}

	public function saveEvent($eventId = null, $calendarId, array $data)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return '';
		}

		if (!empty($data['description']) && strlen($data['description']) > 8192) {
			throw new InvalidArgumentException(JText::sprintf('PLG_DPCALENDAR_GOOGLE_ERROR_DESCRIPTION_LENGTH', strlen($data['description'])));
		}

		try {
			$client = $this->getClient($calendar->params->get('client-id'), $calendar->params->get('client-secret'));
			$client->refreshToken($calendar->params->get('refreshToken'));

			$cal = new Google_Service_Calendar($client);

			$event = new Google_Service_Calendar_Event();
			if (!empty($eventId)) {
				$event = $this->fetchEventFromGoogle(true, $eventId, $calendarId);
			}

			$event->setSummary($data['title']);
			$event->setDescription($data['description']);
			if (isset($data['location'])) {
				$event->setLocation($data['location']);
			}
			if (isset($data['location_ids']) && is_array($data['location_ids'])) {
				$event->setLocation(\DPCalendar\Helper\Location::format(\DPCalendar\Helper\Location::getLocations($data['location_ids'])));
			}

			$allDay    = $data['all_day'] == '1';
			$start     = new Google_Service_Calendar_EventDateTime();
			$startDate = DPCalendarHelper::getDate($data['start_date'], $allDay);
			$timezone  = $startDate->getTimezone()->getName();
			if ($allDay) {
				$start->setDate($startDate->format('Y-m-d'));
				$start->setTimeZone($timezone);
			} else {
				$start->setDateTime($startDate->format('c'));
				$start->setTimeZone($timezone);
			}
			$event->setStart($start);

			$end     = new Google_Service_Calendar_EventDateTime();
			$endDate = DPCalendarHelper::getDate($data['end_date'], $allDay);
			if ($allDay) {
				$endDate->modify('+1 day');
				$end->setDate($endDate->format('Y-m-d'));
				$end->setTimeZone($timezone);
			} else {
				$end->setDateTime($endDate->format('c'));
				$end->setTimeZone($timezone);
			}
			$event->setEnd($end);

			if (isset($data['rrule']) && $data['rrule']) {
				$event->setRecurrence(array(
					'RRULE:' . $data['rrule']
				));
			}

			if (empty($eventId)) {
				$event = $cal->events->insert($calendar->params->get('calendarId'), $event);
			} else {
				$event = $cal->events->update($calendar->params->get('calendarId'), $event->getId(), $event);
			}

			$id = $event->getId() . '_' . ($allDay ? $startDate->format('Ymd') : $startDate->format('YmdHi'));
			if (!empty($data['rrule'])) {
				$id = $event->getId() . '_0';
			}

			return $this->createEvent($id, $calendarId)->id;
		} catch (Exception $e) {
			$this->log($e->getMessage());

			return false;
		}
	}

	public function deleteEvent($eventId = null, $calendarId)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return '';
		}

		try {
			$client = $this->getClient($calendar->params->get('client-id'), $calendar->params->get('client-secret'));
			$client->refreshToken($calendar->params->get('refreshToken'));

			$cal = new Google_Service_Calendar($client);

			$event = $this->fetchEventFromGoogle(true, $eventId, $calendarId);
			$cal->events->delete($calendar->params->get('calendarId'), $event->getId());

			return true;
		} catch (Exception $e) {
			$this->log($e);

			return false;
		}
	}

	public function prepareForm($eventId, $calendarId, $form, $data)
	{
		$form->removeField('color');
		$form->removeField('url');
		$form->removeField('publish_up');
		$form->removeField('publish_down');
		$form->removeField('access');
		$form->removeField('featured');
		$form->removeField('access_content');
		$form->removeField('language');
		$form->removeField('location_ids');
		$form->removeField('metadesc');
		$form->removeField('metakey');

		return true;
	}

	public function import()
	{
		$app = JFactory::getApplication();

		$session = JFactory::getSession(array(
			'expire' => 30
		));

		// If we are on the callback from google don't save
		if (!$app->input->get('code')) {
			$params = $app->input->get('params', array(
				'client-id'     => null,
				'client-secret' => null
			), 'array');
			$session->set('client-id', $params['client-id'], $this->_name);
			$session->set('client-secret', $params['client-secret'], $this->_name);
		}
		$clientId     = $session->get('client-id', null, $this->_name);
		$clientSecret = $session->get('client-secret', null, $this->_name);

		if ($app->input->get('code')) {
			$session->set('client-id', null, $this->_name);
			$session->set('client-secret', null, $this->_name);
		}

		try {
			$client = $this->getClient($clientId, $clientSecret);
			$client->setApprovalPrompt('force');
			if (empty($client)) {
				return;
			}

			if (!$app->input->get('code')) {
				$app->redirect($client->createAuthUrl());
				$app->close();
			}

			$cal   = new Google_Service_Calendar($client);
			$token = $client->authenticate($app->input->get('code', null, null));
			if ($token === true) {
				die();
			}

			if ($token) {
				$client->setAccessToken($token);

				$calendars = $cal->calendarList->listCalendarList();

				$tok = json_decode($token, true);
				foreach ($calendars['items'] as $cal) {
					$model  = JModelLegacy::getInstance('Extcalendar', 'DPCalendarModel');
					$params = new JRegistry();
					$params->set('refreshToken', $tok['refresh_token']);
					$params->set('client-id', $clientId);
					$params->set('client-secret', $clientSecret);
					$params->set('calendarId', $cal['id']);
					$params->set('action-create', true);
					$params->set('action-edit', true);
					$params->set('action-delete', true);

					$data           = array();
					$data['title']  = $cal['summary'];
					$data['color']  = $cal['backgroundColor'];
					$data['params'] = $params->toString();
					$data['plugin'] = 'google';

					if (!$model->save($data)) {
						$app->enqueueMessage($model->getError(), 'warning');
					}
				}
			}
		} catch (Exception $e) {
			$this->log($e->getMessage());
		}
		$app->redirect(
			JFactory::getSession()->get('extcalendarOrigin',
				'index.php?option=com_dpcalendar&view=extcalendars&dpplugin=google&tmpl=' . $app->input->get('tmpl'), 'DPCalendar'));
		$app->close();
	}

	private function getClient($clientId, $clientSecret)
	{
		JLoader::import('dpcalendar.google.libraries.google-php.Google.autoload', JPATH_PLUGINS);

		$client = new Google_Client(
			array(
				'ioFileCache_directory' => JFactory::getConfig()->get('tmp_path') . '/plg_dpcalendar_google/Google_Client'
			));
		$client->setClassConfig('Google_IO_Curl', 'options', array(CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4));
		$client->setApplicationName("DPCalendar");
		$client->setClientId($clientId);
		$client->setClientSecret($clientSecret);
		$client->setScopes(array('https://www.googleapis.com/auth/calendar'));
		$client->setAccessType('offline');

		$uri = !isset($_SERVER['HTTP_HOST']) ? JUri::getInstance('http://localhost') : JFactory::getURI();
		if (filter_var($uri->getHost(), FILTER_VALIDATE_IP)) {
			$uri->setHost('localhost');
		}
		$client->setRedirectUri(
			$uri->toString(array(
				'scheme',
				'host',
				'port',
				'path'
			)) . '?option=com_dpcalendar&task=plugin.action&plugin=google&action=import');

		return $client;
	}

	private function getDate($date, $allDay, $obj)
	{
		if (!$date) {
			return null;
		}
		$time = $date->date;
		if (!$allDay) {
			$time .= ' ' . $date->dateTime;
		}

		$tz = $date->timeZone;
		if (!$tz && isset($obj->timeZone)) {
			$tz = $obj->timeZone;
		}

		$newDate = DPCalendarHelper::getDate($time, $allDay, $allDay ? null : $tz);

		if (!$allDay && $tz) {
			$newDate->setTimezone(new DateTimeZone($tz));
		}

		return $newDate;
	}

	private function createEventFromGoogle($googleEvent, $calendar, $obj, $colors)
	{
		if (!$googleEvent->start) {
			return null;
		}

		$allDay = empty($googleEvent->start->dateTime);

		$startDate = $this->getDate($googleEvent->start, $allDay, $obj);
		$endDate   = $this->getDate($googleEvent->end, $allDay, $obj);
		if ($allDay) {
			$endDate->modify('-1 day');
		}

		$calendarId = $calendar->params->get('calendarId');
		$id         = $googleEvent->id;
		if (strpos($id, '_R') === false &&
			strpos($calendarId, '#sports@group.v.calendar.google.com') === false &&
			strpos($calendarId, '#holiday@group.v.calendar.google.com') === false
		) {
			$pos = strrpos($id, '_');
			if ($pos !== false && $pos !== 0) {
				$id = substr($id, 0, $pos);
			}
		}
		if ($googleEvent->recurringEventId) {
			$id = $googleEvent->recurringEventId;
		}

		if (!empty($googleEvent->recurrence)) {
			$id .= '_0';
		} else {
			$id .= '_' . ($allDay ? $startDate->format('Ymd') : $startDate->format('YmdHi'));
		}

		$at        = strpos($id, '@');
		$delimiter = strrpos($id, '_');
		if ($at !== false && $delimiter !== false) {
			$id = substr_replace($id, '', $at, $delimiter - $at);
		}

		$event                 = $this->createEvent($id, str_replace('g-', '', $calendar->id));
		$event->uid            = $googleEvent->iCalUID;
		$event->start_date     = $startDate->toSql();
		$event->end_date       = $endDate->toSql();
		$event->all_day        = $allDay ? 1 : 0;
		$event->access_content = $calendar->access_content;
		$event->created        = DPCalendarHelper::getDate($googleEvent->created)->toSql();
		$event->modified       = DPCalendarHelper::getDate($googleEvent->updated)->toSql();

		$event->title = $googleEvent->summary;
		if ($calendar->params->get('format-description', '1') == '1') {
			$description        = str_replace('\n', '<br/>', $googleEvent->description);
			$event->description = DPCalendarHelper::parseHtml($description);
		} else {
			$event->description = $googleEvent->description;
		}

		if (!empty($googleEvent->attachments)) {
			$path = JPluginHelper::getLayoutPath('dpcalendar', 'google', 'attachments');

			$buffer             = include $path;
			$event->description .= $buffer;
		}

		// This is the original event
		if (!empty($googleEvent->recurrence)) {
			foreach ($googleEvent->recurrence as $recValue) {
				if (strpos($recValue, 'RRULE') === false) {
					continue;
				}
				$event->rrule = str_replace('RRULE:', '', $recValue);
			}
			$event->original_id = -1;
		} else if ($googleEvent->recurringEventId) {
			$originalAllDay       = empty($googleEvent->originalStartTime->dateTime);
			$recDate              = $this->getDate($googleEvent->originalStartTime, $originalAllDay, $obj);
			$event->recurrence_id = $originalAllDay ? $recDate->format('Ymd') : $recDate->format('Ymd\THis\Z');
			$event->original_id   = substr($event->id, 0, strrpos($event->id, '_')) . '_0';
		}
		if ($googleEvent->location) {
			$event->locations = array(\DPCalendar\Helper\Location::get($googleEvent->location));
		}

		$event->created_by_alias = str_replace('MAILTO:', '', $googleEvent->creator->displayName);

		$event->url = $googleEvent->htmlLink;
		if ($googleEvent->colorId > 0 && !$calendar->color_force) {
			$event->color = str_replace('#', '', $colors[$googleEvent->colorId]->getBackground());
		}

		return $event;
	}
}
