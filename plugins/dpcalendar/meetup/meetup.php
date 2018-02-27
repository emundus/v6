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

JLoader::import('dpcalendar.meetup.libraries.meetup.meetup', JPATH_PLUGINS);

class PlgDPCalendarMeetup extends \DPCalendar\Plugin\SyncPlugin
{
	protected $identifier = 'mu';

	public function fetchEvents($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$events = parent::fetchEvents($calendarId, $startDate, $endDate, $options);
		foreach ($events as $event) {
			$event->description = DPCalendarHelper::parseHtml($event->description);
		}

		return $events;
	}

	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return '';
		}

		// try refreshing calendar if necessary
		try {
			$this->refresh($calendar);
		} catch (Exception $e) {
			return '';
		}

		// use access token for authorized access (not re-using so just populate
		// with
		// all of our params for fetching events)
		$meetup = new Meetup(
			array(
				'access_token'   => trim($calendar->params->get('access_token')),
				'text_format'    => 'html',
				'limited_events' => 'false',
				// 'member_id' =>
				// trim($calendar->params->get('memberId')), //confuses
				// call and gets back all groups
				'group_urlname'  => trim($calendar->params->get('group')),
				'status'         => is_array($calendar->params->get('status')) ? implode(',', $calendar->params->get('status')) : trim(
					$calendar->params->get('status')),
				'fields'         => 'event_hosts'
			)
		);

		// check time and bound it by range
		$time = '';
		if (!is_null($startDate)) {
			// @note time is in ms and you need two <one>,<two> but second can
			// be empty for unbounded
			$time .= ($startDate->getTimestamp() * 1000) . ','; // end can be
			// unbounded
			if (!is_null($endDate)) {
				$time .= ($endDate->getTimestamp() * 1000);
			}
		}

		$text     = array();
		$nbevents = 0;
		try {
			// events for the calendar (meetup group)
			$response = strlen($time) ? $meetup->getEvents(array(
				'time' => $time
			)) : $meetup->getEvents();

			// iterate over each event we recieved
			$text[] = 'BEGIN:VCALENDAR';
			foreach ($response->results as $event) {
				$text = array_merge($text, $this->event($calendar, $startDate, $endDate, $event));
				$nbevents++;
			}

			// keep processing additional events we may have, if there's too
			// many they won't be in the original response
			while (($response = $meetup->getNext()) !== null) {
				foreach ($response->results as $event) {
					$text = array_merge($text, $this->event($calendar, $startDate, $endDate, $event));
					$nbevents++;
				}
			}

			$text[] = 'END:VCALENDAR';
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return (intval($nbevents) > 0 ? $text : '');
	}

	protected function refresh(&$calendar)
	{
		// check access expiry (use a 60 second buffer...)
		if (time() >= (intval($calendar->params->get('expires')) - 60)) {
			// get a new access token & update calendar params
			$meetup = new Meetup(
				array(
					'client_id'     => trim($calendar->params->get('key')),
					'client_secret' => trim($calendar->params->get('secret')),
					'refresh_token' => trim($calendar->params->get('refresh_token'))
				)
			);

			try {
				// refresh access creds
				$response = $meetup->refresh();

				// update calendar so access isn't interupted and we can
				// reference the
				// object later in the code
				$calendar->params->set('access_token', $response->access_token);
				$calendar->params->set('refresh_token', $response->refresh_token);
				$calendar->params->set('expires', time() + (intval($response->expires_in)));

				// update table
				$table = JTable::getInstance('Extcalendar', 'DPCalendarTable');
				$table->load(array('title' => $calendar->title));

				$table->bind(array('params' => $calendar->params->toString()));

				// commit to the database (avoid check, we're just clobbering
				// params)
				if (!$table->store()) {
					JFactory::getApplication()->enqueueMessage($table->getError(), 'warning');
				}
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				throw $e;
			}
		}
	}

	protected function event($calendar, $startDate, $endDate, $event)
	{
		$text = array();

		$text[] = 'BEGIN:VEVENT';

		$time = date('Y-m-d H:i', ($event->time / 1000));

		// all day if it's at least 24 hours or more (in milliseconds)?
		$allDay   = (isset($event->duration) && intval($event->duration) >= 86400000);
		$timezone = 'UTC';

		if (isset($event->timezone)) {
			$timezone = $event->timezone;
		}

		// pass in seconds for time which is in milliseconds
		$startDate = DPCalendarHelper::getDate(($event->time / 1000), $allDay, $timezone);
		if ($allDay) {
			$text[] = 'DTSTART;VALUE=DATE:' . $startDate->format('Ymd');
		} else {
			$text[] = 'DTSTART:' . $startDate->format('Ymd\THis\Z');
		}

		$endDate = null;
		if (!isset($event->duration)) {
			$endDate = clone $startDate;
			if ($allDay) {
				$endDate->modify('+1 day');
			} else {
				$endDate->setTime(23, 59, 59);
			}
		} else {
			$endDate = clone $startDate;
			$endDate->modify('+' . ($event->duration / 1000) . ' seconds');
		}

		if ($allDay) {
			$text[] = 'DTEND;VALUE=DATE:' . $endDate->format('Ymd');
		} else {
			$text[] = 'DTEND:' . $endDate->format('Ymd\THis\Z');
		}

		// include event hosts
		if (isset($event->event_hosts)) {
			if (!empty($event->event_hosts)) {
				$organizer = 'ORGANIZER:';
				foreach ($event->event_hosts as $host) {
					$organizer .= $host->member_name . ',';
				}
				$organizer = substr($organizer, 0, -1);
				$text[]    = $organizer;
			}
		}

		$text[] = 'TITLE:' . $event->name;
		$text[] = 'UID:' . md5($event->id . 'Meetup');
		$text[] = 'CATEGORIES:' . $calendar->params->get('group', 'Default');
		$text[] = 'SUMMARY:' . $event->name;
		$text[] = 'DESCRIPTION:' . (isset($event->description) ? $this->replaceNl(nl2br($event->description)) : '');

		$text[] = 'X-URL:' . $event->event_url;
		if (isset($event->venue)) {
			$location = $event->venue->name;
			if (isset($event->venue->address_1)) {
				$location .= ',' . $event->venue->address_1;
			}
			if (isset($event->venue->city)) {
				$location .= ',' . $event->venue->city;
			}
			if (isset($event->venue->state)) {
				$location .= ',' . $event->venue->state;
			}
			if (isset($event->venue->zip)) {
				$location .= ',' . $event->venue->zip;
			}
			$text[] = 'LOCATION: ' . $location;
			if (isset($event->venue->lat) && isset($event->venue->lon)) {
				$text[] = 'GEO:' . $event->venue->lat . ';' . $event->venue->lon;
			}
		}

		$text[] = 'END:VEVENT';

		return $text;
	}

	public function import()
	{
		$app = JFactory::getApplication();

		$session = JFactory::getSession(array('expire' => 30));

		// Not on callback, access params
		if (!$app->input->get('code')) {
			// access all of our params and store them in the session for
			// subsequent use
			$params = $app->input->get('params',
				array(
					'key'      => null,
					'secret'   => null,
					'memberId' => null,
					'status'   => array(
						'past',
						'upcoming'
					)
				), 'array'
			);

			if (!key_exists('status', $params)) {
				$params['status'] = array('past', 'upcoming');
			}

			$session->set('key', $params['key'], $this->_name);
			$session->set('secret', $params['secret'], $this->_name);
			$session->set('memberId', $params['memberId'], $this->_name);
			$session->set('status', $params['status'], $this->_name);
		}

		// access the session on subsequent calls to this routine
		$key      = $session->get('key', null, $this->_name);
		$secret   = $session->get('secret', null, $this->_name);
		$memberId = $session->get('memberId', null, $this->_name);
		$status   = $session->get('status', array('past', 'upcoming'), $this->_name);

		// redirect for authorization steps
		$redirect = JFactory::getURI()->toString(array(
				'scheme',
				'host',
				'port',
				'path'
			)) . '?option=com_dpcalendar&task=plugin.action&dpplugin=meetup&action=import';

		// we're not on the callback
		if (!$app->input->get('code')) {
			// Call the meetup API for authorization
			$params = array(
				'response_type' => "code",
				'client_id'     => $key,
				'redirect_uri'  => $redirect
			);

			$app->redirect(Meetup::AUTHORIZE . '?' . http_build_query($params));
			$app->close();
		} // we're on the callback
		else {
			$expires = $accessToken = $refreshToken = null;

			// we either are processing the code that we requested OR the
			// subsequent response
			// auth details
			try {
				$meetup = new Meetup(
					array(
						"client_id"     => trim($key),
						"client_secret" => trim($secret),
						"redirect_uri"  => $redirect,
						"code"          => $app->input->get('code')
					)
				);

				$response = $meetup->access();

				$accessToken  = $response->access_token;
				$refreshToken = $response->refresh_token;
				$expires      = time() + intval($response->expires_in);
			} catch (Exception $e) {
				$app->enqueueMessage($e->getMessage(), 'error');

				return;
			}

			// clear out parameters
			$session->set('key', null, $this->_name);
			$session->set('secret', null, $this->_name);
			$session->set('memberId', null, $this->_name);
			$session->set('status', null, $this->_name);

			// access all of the groups using the api
			$groups = array();
			try {
				$meetup = new Meetup(array(
						"access_token" => $accessToken,
						'member_id'    => $memberId
					)
				);

				// on an import get all groups, otherwise on a new one check if
				// they specified
				// one or many groups and fetch those
				$response = $meetup->getGroups();
				try {
					foreach ($response->results as $group) {
						$model  = JModelLegacy::getInstance('Extcalendar', 'DPCalendarModel');
						$params = new JRegistry();
						$params->set('group', $group->urlname);
						$params->set('key', $key);
						$params->set('secret', $secret);
						$params->set('memberId', $memberId);
						$params->set('access_token', $accessToken);
						$params->set('refresh_token', $refreshToken);
						$params->set('expires', $expires);
						$params->set('status', $status);

						$table = JTable::getInstance('Extcalendar', 'DPCalendarTable');
						$table->load(array('title' => $group->urlname));

						$data = array();
						if ($table->id > 0) {
							$data['id'] = $table->id;
						}
						$data['title']       = $group->urlname;
						$data['color']       = substr(dechex(crc32($data['title'])), 0, 6);
						$data['description'] = $group->description;
						$data['params']      = $params->toString();
						$data['plugin']      = 'meetup';
						if (!$model->save($data)) {
							$app->enqueueMessage($model->getError(), 'warning');
						}
					}

					$app->redirect(
						JFactory::getSession()->get('extcalendarOrigin',
							'index.php?option=com_dpcalendar&view=extcalendars&dpplugin=meetup&tmpl=' . $app->input->get('tmpl'), 'DPCalendar'));
					$app->close();
				} catch (Exception $e) {
					$app->enqueueMessage($e->getMessage(), 'warning');

					return;
				}
			} catch (Exception $e) {
				$app->enqueueMessage($e->getMessage(), 'error');

				return;
			}
		}
	}
}
