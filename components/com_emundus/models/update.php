<?php
/**
 * @package         Joomla
 * @subpackage      eMundus
 * @link            http://www.emundus.fr
 * @copyright       Copyright (C) 2015 eMundus. All rights reserved.
 * @license         GNU/GPL
 * @author          James Dean
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_LIBRARIES . DS . 'php-google-api-client' . DS . 'vendor' . DS . 'autoload.php';

define('APPLICATION_NAME', 'Google Calendar API PHP Emundus');
define('CREDENTIALS_PATH', JPATH_LIBRARIES . DS . 'php-google-api-client' . DS . 'credentials' . DS . 'calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', JPATH_LIBRARIES . DS . 'php-google-api-client' . DS . 'certificates' . DS . 'client_secret.json');

// If modifying these scopes, delete your previously saved credentials
// at __DIR__ . '/credentials/calendar-php-quickstart.json

define('SCOPES', implode(' ', array(
	Google_Service_Calendar::CALENDAR) // CALENDAR_READONLY
));

class EmundusModelUpdate extends JModelLegacy
{
	private $db;

	public function __construct()
	{
		parent::__construct();
		$this->db = JFactory::getDbo();

	}

/// Client Accepts the update
	public function setIgnoreVal($version)
	{
		$query = $this->db->getQuery(true);

		// only change the ignore value to the new update to then hide the update module.
		$fields = array(
			$this->db->quoteName('ignore') . ' = ' . $version
		);

		$query
			->update($this->db->quoteName('#__emundus_version'))
			->set($fields);

		$this->db->setQuery($query);

		try {
			$this->db->execute();

			return true;
		}
		catch (Exception $e) {
			JLog::add('Error getting account type stats from mod_graphs helper at query: ' . $query->__toString(), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

/// Client chooses a date to update
	public function setUpdateDate($date, $userName, $version)
	{


		// $calendarListEntry = $service->calendarList->get('calendarId');
		//var_dump($calendarListEntry->getSummary());
		// Build event object for Google.
		$google_event = new Google_Service_Calendar_Event([
			'summary'     => "Update",
			'description' => $userName . " wants to update their eMundus site " . JURI::base() . " to v" . $version,
			'start.date'  => array(
				'dateTime' => $date,
				'timeZone' => 'Europe/Paris',
			)
		]);
		$calendarId   = 'primary';
		//$event = $service->events->insert($calendarId, $event);
		//$result = $google_api_service->events->insert(41, $google_event);

	}


}
