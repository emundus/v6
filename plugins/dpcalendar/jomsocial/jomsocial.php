<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\Registry\Registry;

JLoader::import('components.com_community.libraries.core', JPATH_ROOT);
if (!class_exists('CFactory')) {
	return;
}

if (!JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR)) {
	return;
}

class PlgDPCalendarJomsocial extends \DPCalendar\Plugin\DPCalendarPlugin
{

	protected $identifier = 'js';

	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		if (!class_exists('CFactory')) {
			return '';
		}

		$events = array();

		CFactory::load('helpers', 'time');
		$model = CFactory::getModel('events');
		$model->setState('limitstart', 0);
		$model->setState('limit', $options->get('limit', 1000));

		$opts = array();
		if ($startDate == null) {
			$startDate = DPCalendarHelper::getDate();
		}
		$opts['startdate'] = $startDate->format('Y-m-d H:i:s');

		if ($endDate == null) {
			$endDate = DPCalendarHelper::getDate();
			$endDate->modify('+5 year');
			$opts['enddate'] = $endDate->format('Y-m-d H:i:s');
		}

		$jsEvents = array();
		if (!JFactory::getUser()->guest && strpos($calendarId, '0') === 0 && $this->params->get('my-events', 1)) {
			$jsEvents = $model->getEvents(null, JFactory::getUser()->id, 'startdate', $options->get('filter', null), false, false, null, $opts);
		}
		if (strpos($calendarId, '0') === false && $this->params->get('categories', 1)) {
			$jsEvents = $model->getEvents($calendarId, null, 'startdate', $options->get('filter', null), false, false, null, $opts);
		}

		$text   = array();
		$text[] = 'BEGIN:VCALENDAR';
		foreach ($jsEvents as $event) {
			if (empty($event)) {
				continue;
			}

			$text[] = 'BEGIN:VEVENT';
			$text[] = 'UID:' . md5($event->id . 'JomSocial');
			$text[] = 'SUMMARY:' . $event->title;
			$text[] = 'DESCRIPTION:' . JFilterInput::getInstance()->clean(preg_replace('/\r\n?/', "\N", $event->description));
			$text[] = 'X-ALT-DESC;FMTTYPE=text/html:' . preg_replace('/\r\n?/', "", $event->description);

			$text[] = 'LOCATION:' . $event->location;
			if (!empty($event->latitude) && (int)$event->latitude != 255 && !empty($event->longitude) && (int)$event->longitude != 255) {
				$text[] = 'GEO:' . $event->latitude . ';' . $event->longitude;
			}

			$params = new Registry($event->params);

			$start = DPCalendarHelper::getDate($event->startdate, $event->allday == 1, $params->get('timezone', null));
			if ($event->allday == 1) {
				$text[] = 'DTSTART;VALUE=DATE:' . $start->format('Ymd');
			} else {
				$text[] = 'DTSTART:' . $start->format('Ymd\THis\Z');
			}

			$end = DPCalendarHelper::getDate($event->enddate, $event->allday == 1, $params->get('timezone', null));
			if ($event->allday == 1) {
				$text[] = 'DTEND;VALUE=DATE:' . $end->format('Ymd');
			} else {
				$text[] = 'DTEND:' . $end->format('Ymd\THis\Z');
			}
			$text[] = 'END:VEVENT';
		}
		$text[] = 'END:VCALENDAR';

		return $text;
	}

	protected function fetchCalendars($calendarIds = null)
	{
		if (!class_exists('CFactory')) {
			return array();
		}

		$calendars = array();
		if (!JFactory::getUser()->guest && $this->params->get('my-events', 1)) {
			$calendars[] = $this->createCalendar('0', JText::_('PLG_DPCALENDAR_JOMSOCIAL_MY_EVENTS_TITLE'), '');
		}
		if ($this->params->get('categories', 1)) {
			$model = CFactory::getModel('events');
			foreach ($model->getAllCategories() as $calendar) {
				if (!empty($calendarIds) && !in_array($calendar->id, $calendarIds)) {
					continue;
				}
				$calendars[] = $this->createCalendar($calendar->id, $calendar->name, $calendar->description);
			}
		}

		return $calendars;
	}

	public function onEventsFetch($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options = null)
	{
		// Disable caching when fetching users events
		if ($this->params->get('my-events', 1)) {
			$this->params->set('cache', 0);
		}

		return parent::onEventsFetch($calendarId, $startDate, $endDate, $options);
	}

	public function onEventAfterCreate(&$event)
	{
		if ($this->params->get('enable-activity-create', 1) == 0) {
			return;
		}

		$this->writeOnStream('create', $event);
	}

	public function onEventAfterSave(&$event)
	{
		if ($this->params->get('enable-activity-save', 0) == 0) {
			return;
		}

		$this->writeOnStream('save', $event);
	}

	public function onEventAfterDelete($event)
	{
		if ($this->params->get('enable-activity-delete', 1) == 0) {
			return;
		}

		$this->writeOnStream('delete', $event);
	}

	private function writeOnStream($action, $event)
	{
		if (!class_exists('CFactory')) {
			return;
		}

		$this->loadLanguage();

		$act           = new stdClass();
		$act->cmd      = 'dpevents.' . $action;
		$act->actor    = JFactory::getUser()->id;
		$act->target   = 0;
		$act->title    = JText::sprintf(
			'PLG_DPCALENDAR_JOMSOCIAL_ACTIVITY_' . strtoupper($action),
			DPCalendarHelperRoute::getEventRoute($event->id, $event->catid, true),
			$event->title
		);
		$act->content  = '';
		$act->app      = 'wall';
		$act->location = \DPCalendar\Helper\Location::format($event->locations);
		$act->cid      = $event->id;

		$params = new CParameter('');
		$params->set('eventid', $event->id);
		$params->set('action', 'dpevents.' . $action);
		$act->params = $params->toString();

		CFactory::load('libraries', 'activities');
		$act->comment_type = 'dpcalendar.event.' . $action;
		$act->comment_id   = CActivities::COMMENT_SELF;

		$act->like_type = 'dpcalendar.event.' . $action;
		$act->like_id   = CActivities::LIKE_SELF;

		CActivityStream::add($act);
	}
}
