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

JLoader::import('dpcalendar.caldav.libraries.caldav.caldav-client', JPATH_PLUGINS);

class PlgDPCalendarCalDAV extends \DPCalendar\Plugin\CalDAVPlugin
{
	protected $identifier = 'cad';

	protected function getSyncToken($calendar)
	{
		$client = $this->getCalDAVClient($id = str_replace($this->identifier . '-', '', $calendar->id));
		if (!$client) {
			return parent::getSyncToken($calendar);
		}

		return $client->GetCalendarDetails($this->getUrl($calendar->id))->getctag;
	}

	protected function createCalDAVEvent($uid, $icalData, $calendarId)
	{
		return $this->updateCalDAVEvent($uid, $icalData, $calendarId);
	}

	protected function updateCalDAVEvent($uid, $icalData, $calendarId)
	{
		$client = $this->getCalDAVClient($calendarId);
		if ($client == null) {
			return false;
		}

		$response = $client->DoPUTRequest($this->getUrl($calendarId) . $uid . '.ics', $icalData);

		return $response;
	}

	protected function deleteCalDAVEvent($uid, $calendarId)
	{
		$client = $this->getCalDAVClient($calendarId);
		if ($client == null) {
			return false;
		}

		$response = $client->DoDELETERequest($this->getUrl($calendarId) . $uid . '.ics');

		return $response;
	}

	protected function getOriginalData($uid, $calendarId)
	{
		$client = $this->getCalDAVClient($calendarId);
		if ($client == null) {
			return '';
		}
		$event = $client->GetEntryByUid($uid);

		// On iCloud the event is empty
		if (empty($event)) {
			$entry = $client->GetEntryByHref($this->getUrl($calendarId) . $uid . '.ics');

			return trim(substr($entry, strpos($entry, 'BEGIN:VCALENDAR')));
		}

		return $event[0]['data'];
	}

	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$buffer = '';
		$start  = null;
		if ($startDate != null) {
			$start = $startDate->format('Ymd\THis\Z');
		}
		$end = null;
		if ($endDate != null) {
			$end = $endDate->format('Ymd\THis\Z');
		}

		$client = $this->getCalDAVClient($calendarId);
		if ($client == null) {
			return '';
		}
		$events = @$client->GetEvents($start, $end);

		if (preg_match('/HTTP\/\d\.\d (\d{3})/', $client->GetResponseHeaders(), $status)) {
			$responseCode = intval($status[1]);
			if ($responseCode < 200 || $responseCode > 299) {
				$this->log(strtok($client->GetResponseHeaders(), "\n"));
			}
		}

		foreach ($events as $event) {
			$data = '';
			if (!key_exists('data', $event)) {
				if (key_exists('href', $event) && strpos($event['href'], '.ics')) {
					$data = $client->GetEntryByHref($this->getUrl($calendarId) . $event['href']);
				}
			} else {
				$data = $event['data'];
			}
			if (!empty($data)) {
				$start = strpos($data, 'BEGIN:VEVENT');
				$end   = strrpos($data, 'END:VEVENT');
				if ($start && $end) {
					$buffer .= substr($data, $start, $end - $start + 10) . PHP_EOL;
				}
			}
		}

		$buffer = trim($buffer);
		$buffer = str_replace("BEGIN:VCALENDAR\r\n", '', $buffer);
		$buffer = str_replace("\r\nEND:VCALENDAR", '', $buffer);

		if (empty($buffer)) {
			return '';
		}

		return "BEGIN:VCALENDAR" . PHP_EOL . $buffer . PHP_EOL . "END:VCALENDAR";
	}

	private function getCalDAVClient($calendarId)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return null;
		}

		$params = $calendar->params;

		$client = new CalDAVClient(trim($this->getUrl($calendarId)), $params->get('username'), $params->get('password'));

		return $client;
	}

	private function getUrl($calendarId)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return null;
		}
		$params = $calendar->params;

		return trim($params->get('host'), '/') . '/' . trim($params->get('calendar'), '/') . '/';
	}

	public function import()
	{
		$app = JFactory::getApplication();

		$params = $app->input->get('params', array(
			'host'     => null,
			'username' => ''
		), 'array');
		$host   = trim($params['host'], '/') . '/';

		$response = '';
		if (strpos($host, 'icloud') !== false) {
			$response = $this->fetchFromDav($host, "<A:propfind xmlns:A='DAV:'><A:prop><A:current-user-principal/></A:prop></A:propfind>");
			if (empty($response)) {
				$app->redirect(
					JFactory::getSession()->get('extcalendarOrigin',
						'index.php?option=com_dpcalendar&view=extcalendars&dpplugin=caldav&tmpl=' . $app->input->get('tmpl'), 'DPCalendar'));
				$app->close();
			}
			$response = new SimpleXMLElement($response);

			$userID   = explode("/", $response->response[0]->propstat[0]->prop[0]->{'current-user-principal'}->href);
			$host     .= $userID[1];
			$response = $this->fetchFromDav($host . '/calendars/', "<A:propfind xmlns:A='DAV:'><A:prop><A:displayname/></A:prop></A:propfind>");
		} else {
			$response = $this->fetchFromDav($host . "calendars/" . $params['username'],
				"<A:propfind xmlns:A='DAV:'><A:prop><A:displayname/></A:prop></A:propfind>");
			if (empty($response) || strpos($response, '<') !== 0) {
				$response = $this->fetchFromDav($host, "<A:propfind xmlns:A='DAV:'><A:prop><A:displayname/></A:prop></A:propfind>");
			}
		}

		if (empty($response)) {
			$app->redirect(
				JFactory::getSession()->get('extcalendarOrigin',
					'index.php?option=com_dpcalendar&view=extcalendars&dpplugin=caldav&tmpl=' . $app->input->get('tmpl'), 'DPCalendar'));
			$app->close();
		}

		// Remove the namespaces
		$response = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $response);

		$response = str_ireplace('<d:', '<', $response);
		$response = str_ireplace('</d:', '</', $response);

		$response = new SimpleXMLElement($response);

		$credentials = $app->input->get('params', array(
			'username' => '',
			'password' => ''
		), 'array');
		foreach ($response->response as $cal) {
			$model  = JModelLegacy::getInstance('Extcalendar', 'DPCalendarModel');
			$params = new JRegistry();
			$params->set('host', $host);
			$params->set('username', $credentials['username']);
			$params->set('password', $credentials['password']);

			// Sometimes the calendar href contains the full path which
			// is part of the host variable already.
			$ref = (string)$cal->href;
			$pos = strpos($ref, 'calendars/');
			if ($pos !== false) {
				$ref = substr($ref, $pos);
			}
			$params->set('calendar', '/' . trim($ref, '/'));
			$params->set('action-create', true);
			$params->set('action-edit', true);
			$params->set('action-delete', true);

			$data          = array();
			$data['title'] = (string)$cal->propstat[0]->prop[0]->displayname;
			if (empty($data['title'])) {
				$data['title'] = $params->get('calendar');
			}
			$data['color']  = substr(dechex(crc32($data['title'])), 0, 6);
			$data['params'] = $params->toString();
			$data['plugin'] = 'caldav';

			if (!$model->save($data)) {
				$app->enqueueMessage($model->getError(), 'warning');
			}
		}

		$app->redirect(
			JFactory::getSession()->get('extcalendarOrigin',
				'index.php?option=com_dpcalendar&view=extcalendars&dpplugin=caldav&tmpl=' . $app->input->get('tmpl'), 'DPCalendar'));
		$app->close();
	}

	private function fetchFromDav($url, $xml, $auth = CURLAUTH_BASIC)
	{
		$input = JFactory::getApplication()->input;

		$c = curl_init(trim($url));

		curl_setopt($c, CURLOPT_HTTPHEADER, array(
			"Depth: 1",
			"Content-Type: text/xml; charset='UTF-8'"
		));
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_USERAGENT, 'DPCalendar');

		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

		$params = $input->get('params', array(
			'username' => '',
			'password' => ''
		), 'array');
		curl_setopt($c, CURLOPT_HTTPAUTH, $auth);
		curl_setopt($c, CURLOPT_USERPWD, $params['username'] . ":" . $params['password']);

		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PROPFIND");
		curl_setopt($c, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

		$data = curl_exec($c);

		if ((strpos($data, 'No digest authentication headers were found') !== false
			|| strpos($data, 'No \'Authorization: Digest\' header found.') !== false)
			&& $auth == CURLAUTH_BASIC) {
			return $this->fetchFromDav($url, $xml, CURLAUTH_DIGEST);
		}

		if ($error = curl_error($c)) {
			JFactory::getApplication()->enqueueMessage($error, 'warning');

			return null;
		}
		$info = curl_getinfo($c);

		if (isset($info['http_code']) && $info['http_code'] > 400) {
			JFactory::getApplication()->enqueueMessage('Failed connection with ' . $info['http_code'] . ' error for url ' . $url . '!', 'warning');

			return null;
		}

		curl_close($c);

		return $data;
	}
}
