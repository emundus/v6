<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR);
JLoader::import('dpcalendar.twitter.libraries.codebird', JPATH_PLUGINS);

class PlgDPCalendarTwitter extends \DPCalendar\Plugin\SyncPlugin
{
	protected $identifier = 'tw';

	protected function getSyncToken($calendar)
	{
		$params = $calendar->params;

		\Codebird\Codebird::setConsumerKey($params->get('apikey', ''), $params->get('apisecret', ''));
		$cb = \Codebird\Codebird::getInstance();
		$cb->setToken($params->get('accesstoken', ''), $params->get('accesstokensecret', ''));

		$conditions = 'count=1';
		if ($calendar->sync_token > 1) {
			$conditions .= '&since_id=' . $calendar->sync_token;
		}
		$tweets = $cb->statuses_userTimeline($conditions);
		foreach ((array)$tweets as $tweet) {
			if (!isset($tweet->id)) {
				continue;
			}

			return $tweet->id;
		}

		return $calendar->sync_token;
	}

	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return '';
		}

		$params = $calendar->params;

		\Codebird\Codebird::setConsumerKey($params->get('apikey', ''), $params->get('apisecret', ''));
		$cb = \Codebird\Codebird::getInstance();
		$cb->setToken($params->get('accesstoken', ''), $params->get('accesstokensecret', ''));

		$cursor = null;
		$text   = array();
		$text[] = 'BEGIN:VCALENDAR';
		while ($cursor > 0 || $cursor === null) {
			$tweets = array();
			if ($cursor === null) {
				$tweets = $cb->statuses_userTimeline();
			} else {
				$tweets = $cb->statuses_userTimeline('cursor=' . $cursor);
			}
			if ($tweets->httpstatus != 200) {
				$this->log('Twitter error: ' . print_r($tweets->errors, true));

				return array();
			}

			$cursor = isset($tweets->next_cursor_str) ? $tweets->next_cursor_str : 0;
			foreach ((array)$tweets as $tweet) {
				if (!is_object($tweet)) {
					continue;
				}
				$text[] = 'BEGIN:VEVENT';

				$start  = DPCalendarHelper::getDate($tweet->created_at);
				$text[] = 'DTSTART:' . $start->format('Ymd\THis\Z');
				$start->modify('+1 minute');
				$text[] = 'DTEND:' . $start->format('Ymd\THis\Z');

				$text[] = 'UID:' . md5($tweet->id . 'Twitter');
				$text[] = 'SUMMARY:' . $tweet->text;
				$text[] = 'DESCRIPTION:' . DPCalendarHelper::parseHtml($tweet->text);
				$text[] = 'LAST-MODIFIED:' . $start->format('Ymd\THis\Z');
				$text[] = 'CREATED:' . $start->format('Ymd\THis\Z');

				if (isset($tweet->place)) {
					$text[] = 'LOCATION:' . $tweet->place->full_name;
				}

				$text[] = 'X-URL:http://twitter.com/' . $tweet->user->screen_name . '/status/' . $tweet->id_str;

				$text[] = 'END:VEVENT';
			}
		}
		$text[] = 'END:VCALENDAR';

		return $text;
	}

	public function onEventAfterCreate(&$event)
	{
		foreach ($this->fetchCalendars() as $calendar) {
			$creationeventupdate = $calendar->params->get('creationeventupdate', 1);
			if ($creationeventupdate) {
				$this->autoTweetEvent('create', $event, $calendar);
			}
		}
	}

	public function onEventAfterSave(&$event)
	{
		foreach ($this->fetchCalendars() as $calendar) {
			$updateeventupdate = $calendar->params->get('updateeventupdate', 1);
			if ($updateeventupdate) {
				$this->autoTweetEvent('save', $event, $calendar);
			}
		}
	}

	public function onEventAfterDelete($event)
	{
		foreach ($this->fetchCalendars() as $calendar) {
			$deletioneventupdate = $calendar->params->get('deletioneventupdate', 0);
			if ($deletioneventupdate) {
				$this->autoTweetEvent('delete', $event, $calendar);
			}
		}
	}

	private function autoTweetEvent($action, $event, $calendar)
	{
		// We don't tweet external events
		if (!is_numeric($event->catid)) {
			return;
		}

		$params = $calendar->params;
		$ids    = $params->get('ids', null);
		if ($ids) {
			$eventsinsubcat = $params->get('eventsinsubcat', 0);
			if ($eventsinsubcat) {
				$sids = $ids;
				foreach ($sids as $id) {
					$cat = JCategories::getInstance('DPCalendar')->get($id);
					if (!$cat) {
						continue;
					}
					$children = $cat->getChildren();
					if ($children) {
						foreach ($children as $ch) {
							if (!in_array($ch->id, $ids)) {
								$ids[] = $ch->id;
							}
						}
					}
				}
			}
		}
		if (empty($ids) || in_array($event->catid, $ids)) {
			\Codebird\Codebird::setConsumerKey($params->get('apikey', ''), $params->get('apisecret', ''));
			$cb = \Codebird\Codebird::getInstance();
			$cb->setToken($params->get('accesstoken', ''), $params->get('accesstokensecret', ''));
			$status   = '';
			$eventurl = DPCalendarHelperRoute::getEventRoute($event->id, $event->catid, true);
			$eventurl = str_replace('administrator/', '', $eventurl);
			switch ($action) {
				case 'create':
					$status = JText::sprintf('PLG_DPCALENDAR_TWITTER_STATUS_EVENT_CREATED', $event->title, $eventurl);
					break;
				case 'save':
					$status = JText::sprintf('PLG_DPCALENDAR_TWITTER_STATUS_EVENT_UPDATED', $event->title, $eventurl);
					break;
				case 'delete':
					$status = JText::sprintf('PLG_DPCALENDAR_TWITTER_STATUS_EVENT_DELETED', $event->title);
					break;
			}
			$status = substr($status, 0, 140);
			$params = array(
				'status' => $status
			);
			$reply  = $cb->statuses_update($params);

			if ($reply->httpstatus == 200) {
				$tweetUrl = 'https://twitter.com/' . $reply->user->screen_name . '/status/' . $reply->id_str;
				JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_DPCALENDAR_TWITTER_PUBLISH_SUCCESS', $tweetUrl, $calendar->title),
					'success');
			} else {
				$error = '';
				if (isset($reply->errors[0]) && isset($reply->errors[0]->message)) {
					$error = $reply->errors[0]->message;
				}
				$this->log(JText::sprintf('PLG_DPCALENDAR_TWITTER_PUBLISH_ERROR', $error));
			}
		}
	}
}
