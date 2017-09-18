<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.syncplugin', JPATH_ADMINISTRATOR);
if (! class_exists('DPCalendarSyncPlugin'))
{
	return;
}

class PlgDPCalendarDPCalendar_Facebook extends DPCalendarSyncPlugin
{

	protected $identifier = 'fb';

	protected function getSyncToken ($calendar)
	{
		// We are doing a raw request because FB supports etags in their
		// response headers https://developers.facebook.com/blog/post/627
		$http = JHttpFactory::getHttp();
		$response = $http->get(
				'https://graph.facebook.com/v2.5/' . $calendar->params->get('page-id') . '/events?access_token=' .
						 $calendar->params->get('access-token'));

		$syncToken = null;
		if (key_exists('ETag', $response->headers))
		{
			$syncToken = $response->headers['ETag'];
		}
		else if (key_exists('Last-Modified', $response->headers))
		{
			$syncToken = $response->headers['Last-Modified'];
		}

		if ($syncToken)
		{
			return $syncToken;
		}
		return parent::getSyncToken($calendar);
	}

	/*
	 * Because the end date of Facebook is exclusive we need to
	 * overwrite that function.
	 */
	public function fetchEvent ($eventId, $calendarId)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar))
		{
			return '';
		}

		$pos = strrpos($eventId, '_');
		if ($pos === false)
		{
			return null;
		}

		JLoader::import('dpcalendar.dpcalendar_facebook.libraries.fb-sdk.facebook', JPATH_PLUGINS);

		$facebook = new Facebook(
				array(
						'appId' => trim($calendar->params->get('app-id')),
						'secret' => trim($calendar->params->get('secret')),
						'cookie' => true
				));

		$url = '/v2.5/' . trim(substr($eventId, 0, $pos)) . '?fields=name,cover,id,start_time,end_time,timezone,place,description,updated_time';

		$facebook->setAccessToken($calendar->params->get('access-token'));
		try
		{
			$fbEvent = $facebook->api($url);
			$event = $this->createEvent($fbEvent['id'], $calendarId);
			$event->uid = $fbEvent['id'];

			$event->all_day = strlen($fbEvent['start_time']) == 10;
			$timezone = 'UTC';
			if (key_exists('timezone', $event))
			{
				$timezone = $fbEvent['timezone'];
			}

			$startDate = DPCalendarHelper::getDate($fbEvent['start_time'], $event->all_day, $timezone);
			$event->start_date = $startDate->toSql();

			$endDate = null;
			if (empty($fbEvent['end_time']))
			{
				$endDate = clone $startDate;
				if (! $event->all_day)
				{
					$endDate->setTime(23, 59, 59);
				}
			}
			else
			{
				$endDate = DPCalendarHelper::getDate($fbEvent['end_time'], $event->all_day, $timezone);
			}
			$event->end_date = $endDate->toSql();

			$event->title = $fbEvent['name'];
			if (isset($fbEvent['description']))
			{
				$event->description = DPCalendarHelper::parseHtml($this->replaceNl(nl2br($fbEvent['description'])));
			}

			// Using place field for location information
			if (key_exists('place', $fbEvent) && isset($fbEvent['place']['location']))
			{
				$location = DPCalendarHelperLocation::format(json_decode(json_encode($fbEvent['place']['location']), false));
				if (key_exists('latitude', $fbEvent['place']['location']) && key_exists('longitude', $fbEvent['place']['location']))
				{
					$location = $fbEvent['place']['location']['latitude'] . ',' . $fbEvent['place']['location']['longitude'];
				}
				$locationObject = DPCalendarHelperLocation::get($location);
				$event->locations = array(
						$locationObject
				);
			}

			$event->url = 'https://www.facebook.com/events/' . $fbEvent['id'];

			if (isset($fbEvent['cover']))
			{
				$event->images = json_encode(array(
						'image1' => $fbEvent['cover']['source']
				));
			}
			return $event;
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
		return null;
	}

	public function fetchEvents ($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$events = parent::fetchEvents($calendarId, $startDate, $endDate, $options);
		foreach ($events as $event)
		{
			$event->description = DPCalendarHelper::parseHtml($event->description);
		}
		return $events;
	}

	protected function getContent ($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar))
		{
			return '';
		}

		JLoader::import('dpcalendar.dpcalendar_facebook.libraries.fb-sdk.facebook', JPATH_PLUGINS);

		$facebook = new Facebook(
				array(
						'appId' => trim($calendar->params->get('app-id')),
						'secret' => trim($calendar->params->get('secret')),
						'cookie' => true
				));

		$url = '/v2.5/' . trim($calendar->params->get('page-id')) .
				 '/events?fields=name,cover,id,start_time,end_time,timezone,place,description,updated_time&limit=10000';
		if ($startDate == null)
		{
			$url .= '&since=' . urlencode(DPCalendarHelper::getDate()->format('c'));
		}
		else
		{
			// Since is exclusive so we need to subtract a second
			$startDate->modify('-1 second');
			$url .= '&since=' . urlencode($startDate->format('c'));
		}

		if ($endDate !== null)
		{
			$url .= '&until=' . urlencode($endDate->format('c'));
		}

		$facebook->setAccessToken($calendar->params->get('access-token'));

		$events = null;
		try
		{
			$events = $facebook->api($url);
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
		if (empty($events))
		{
			return array();
		}

		$text = array();
		$text[] = 'BEGIN:VCALENDAR';
		foreach ($events['data'] as $event)
		{
			$text[] = 'BEGIN:VEVENT';

			$allDay = strlen($event['start_time']) == 10;
			$timezone = 'UTC';
			if (key_exists('timezone', $event))
			{
				$timezone = $event['timezone'];
			}

			$startDate = DPCalendarHelper::getDate($event['start_time'], $allDay, $allDay ? null : $timezone);
			if ($allDay)
			{
				$text[] = 'DTSTART;VALUE=DATE:' . $startDate->format('Ymd');
			}
			else
			{
				$text[] = 'DTSTART:' . $startDate->format('Ymd\THis\Z');
			}

			$endDate = null;
			if (empty($event['end_time']))
			{
				$endDate = clone $startDate;
				if ($allDay)
				{
					$endDate->modify('+1 day');
				}
				else
				{
					$endDate->setTime(23, 59, 59);
				}
			}
			else
			{
				$endDate = DPCalendarHelper::getDate($event['end_time'], $allDay, $allDay ? null : $timezone);
			}
			if ($allDay)
			{
				$text[] = 'DTEND;VALUE=DATE:' . $endDate->format('Ymd');
			}
			else
			{
				$text[] = 'DTEND:' . $endDate->format('Ymd\THis\Z');
			}

			$text[] = 'UID:' . $event['id'];
			$text[] = 'CATEGORIES:' . $calendar->params->get('title', 'Default');
			$text[] = 'SUMMARY:' . $event['name'];
			if (isset($event['description']))
			{
				$text[] = 'DESCRIPTION:' . $this->replaceNl(nl2br($event['description']));
			}

			$text[] = 'X-URL:https://www.facebook.com/events/' . $event['id'];

			// Using place field for location information
			if (key_exists('place', $event) && isset($event['place']['location']))
			{
				$location = $event['place']['location'];
				$text[] = 'LOCATION:' . DPCalendarHelperLocation::format(json_decode(json_encode($location), false));

				if (key_exists('latitude', $location) && key_exists('longitude', $location))
				{
					$text[] = 'GEO:' . $location['latitude'] . ';' . $location['longitude'];
				}
			}

			if (isset($event['cover']))
			{
				$text[] = 'X-IMAGE:' . $event['cover']['source'];
			}

			$text[] = 'END:VEVENT';
		}
		$text[] = 'END:VCALENDAR';

		return $text;
	}

	public function import ()
	{
		$app = JFactory::getApplication();

		$session = JFactory::getSession(array(
				'expire' => 30
		));

		// If we are on the callback from facebook don't save
		if (! $app->input->get('code'))
		{
			$params = $app->input->get('params', array(
					'app-id' => null,
					'secret' => null
			), 'array');
			$session->set('app-id', $params['app-id'], $this->_name);
			$session->set('secret', $params['secret'], $this->_name);
			$session->set('page-id', $params['page-id'], $this->_name);
		}
		$appId = $session->get('app-id', null, $this->_name);
		$secret = $session->get('secret', null, $this->_name);
		$pageId = $session->get('page-id', null, $this->_name);

		if ($app->input->get('code'))
		{
			$session->set('app-id', null, $this->_name);
			$session->set('secret', null, $this->_name);
			$session->set('page-id', null, $this->_name);
		}

		JLoader::import('dpcalendar.dpcalendar_facebook.libraries.fb-sdk.facebook', JPATH_PLUGINS);
		$facebook = new Facebook(array(
				'appId' => trim($appId),
				'secret' => trim($secret),
				'cookie' => false
		));

		$user = $facebook->getUser();
		if ($user)
		{
			try
			{
				$accessToken = $facebook->getAccessToken();
				$pages = $facebook->api('/v2.5/' . $user . '/accounts');
				$pages = $pages['data'];
				if ($pageId)
				{
					if (! is_numeric($pageId))
					{
						$pageId = trim(substr($pageId, strrpos($pageId, "/")), '/');
					}
					$data = $facebook->api('/v2.5/' . $pageId);
					if (isset($data['id']))
					{
						$pages[] = $data;
					}
					else
					{
						$app->enqueueMessage(JText::printf('PLG_DPCALENDAR_FACEBOOK_MESSAGE_NO_PAGE_ID', $pageId), 'warning');
					}
				}

				foreach ($pages as $pageData)
				{
					$page = $data = $facebook->api('/v2.5/' . $pageData['id'] . '?fields=id,name,access_token,about,description,description_html');

					$model = JModelLegacy::getInstance('Extcalendar', 'DPCalendarModel');
					$params = new JRegistry();
					$params->set('app-id', $appId);
					$params->set('secret', $secret);
					$params->set('page-id', $page['id']);
					$params->set('access-token', $page['access_token']);

					$table = JTable::getInstance('Extcalendar', 'DPCalendarTable');
					$table->load(array(
							'title' => $page['name']
					));

					$data = array();
					if ($table->id > 0)
					{
						$data['id'] = $table->id;
					}
					$data['title'] = $page['name'];
					if (key_exists('description_html', $page))
					{
						$data['description'] = $page['description_html'];
					}
					else if (key_exists('description', $page))
					{
						$data['description'] = $page['description'];
					}
					else if (key_exists('about', $page))
					{
						$data['description'] = $page['about'];
					}
					$data['color'] = substr(dechex(crc32($data['title'])), 0, 6);
					$data['params'] = $params->toString();
					$data['plugin'] = 'facebook';

					if (! $model->save($data))
					{
						$app->enqueueMessage($model->getError(), 'warning');
					}
				}

				$app->redirect(
						JFactory::getSession()->get('extcalendarOrigin',
								'index.php?option=com_dpcalendar&view=extcalendars&dpplugin=facebook&tmpl=' . $app->input->get('tmpl'), 'DPCalendar'));
				$app->close();
			}
			catch (FacebookApiException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'warning');
				$user = null;
			}
		}
		if (! $user)
		{
			$uri = JFactory::getURI();
			$loginUrl = $facebook->getLoginUrl(
					array(
							'scope' => 'email,manage_pages,publish_actions,publish_pages',
							'redirect_uri' => $uri->toString(array(
									'scheme',
									'host',
									'port',
									'path'
							)) . '?option=com_dpcalendar&task=plugin.action&dpplugin=facebook&action=import'
					));
			$app->redirect($loginUrl);
			$app->close();
		}
	}

	public function onEventAfterCreate (&$event)
	{
		foreach ($this->fetchCalendars() as $calendar)
		{
			$creationeventupdate = $calendar->params->get('creationeventupdate', 1);
			if ($creationeventupdate)
			{
				$this->publishEvent('create', $event, $calendar);
			}
		}
	}

	public function onEventAfterSave (&$event)
	{
		foreach ($this->fetchCalendars() as $calendar)
		{
			$updateeventupdate = $calendar->params->get('updateeventupdate', 1);
			if ($updateeventupdate)
			{
				$this->publishEvent('save', $event, $calendar);
			}
		}
	}

	public function onEventAfterDelete ($event)
	{
		foreach ($this->fetchCalendars() as $calendar)
		{
			$deletioneventupdate = $calendar->params->get('deletioneventupdate', 0);
			if ($deletioneventupdate)
			{
				$this->publishEvent('delete', $event, $calendar);
			}
		}
	}

	private function publishEvent ($action, $event, $calendar)
	{
		// We don't tweet external events
		if (! is_numeric($event->catid))
		{
			return;
		}

		$params = $calendar->params;
		$ids = $params->get('ids', null);
		if ($ids)
		{
			$eventsinsubcat = $params->get('eventsinsubcat', 0);
			if ($eventsinsubcat)
			{
				$sids = $ids;
				foreach ($sids as $id)
				{
					$cat = JCategories::getInstance('DPCalendar')->get($id);
					if (! $cat)
					{
						continue;
					}
					$children = $cat->getChildren();
					if ($children)
					{
						foreach ($children as $ch)
						{
							if (! in_array($ch->id, $ids))
							{
								$ids[] = $ch->id;
							}
						}
					}
				}
			}
		}
		if (in_array($event->catid, $ids))
		{
			JLoader::import('dpcalendar.dpcalendar_facebook.libraries.fb-sdk.facebook', JPATH_PLUGINS);

			$facebook = new Facebook(
					array(
							'appId' => trim($calendar->params->get('app-id')),
							'secret' => trim($calendar->params->get('secret')),
							'cookie' => true
					));

			$url = '/v2.5/' . trim($calendar->params->get('page-id')) . '/feed';

			$facebook->setAccessToken($calendar->params->get('access-token'));
			$status = '';
			$eventurl = DPCalendarHelperRoute::getEventRoute($event->id, $event->catid, true);
			$eventurl = str_replace('administrator/', '', $eventurl);
			switch ($action)
			{
				case 'create':
					$status = JText::sprintf('PLG_DPCALENDAR_FACEBOOK_STATUS_EVENT_CREATED', $event->title, $eventurl);
					break;
				case 'save':
					$status = JText::sprintf('PLG_DPCALENDAR_FACEBOOK_STATUS_EVENT_UPDATED', $event->title, $eventurl);
					break;
				case 'delete':
					$status = JText::sprintf('PLG_DPCALENDAR_FACEBOOK_STATUS_EVENT_DELETED', $event->title);
					break;
			}
			$params = array(
					'message' => $status
			);

			try
			{
				$reply = $facebook->api($url, 'post', $params);

				if (key_exists('id', $reply))
				{
					$publishUrl = '';
					$parts = explode('_', $reply['id']);
					if (count($parts) >= 2)
					{
						$publishUrl = 'https://www.facebook.com/permalink.php?story_fbid=' . $parts[1] . '&id=' . $parts[0];
					}
					JFactory::getApplication()->enqueueMessage(
							JText::sprintf('PLG_DPCALENDAR_FACEBOOK_PUBLISH_SUCCESS', $publishUrl, $calendar->title));
				}
				else
				{
					$this->log(JText::sprintf('PLG_DPCALENDAR_FACEBOOK_PUBLISH_ERROR', ''));
				}
			}
			catch (Exception $e)
			{
				$this->log(JText::sprintf('PLG_DPCALENDAR_FACEBOOK_PUBLISH_ERROR', $e->getMessage()));
			}
		}
	}
}
