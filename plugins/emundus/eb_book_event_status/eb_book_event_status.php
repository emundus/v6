<?php
/**
 * @package       eMundus
 * @version       6.6.5
 * @author        eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license       GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */
class PlgEmundusEb_book_event_status extends JPlugin
{

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.emunduseb_book_event_status.php'), JLog::ALL, array('com_emundus'));
	}


	function onAfterStatusChange($fnum, $state)
	{
		$status_to_check = $this->params->get('eb_book_event_status_step', '');

		// No need to continue if the status is not in the list of statuses
		if (!in_array($state, explode(',', $status_to_check))) {
			return false;
		}

		/// Set the IP
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			//ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			//ip pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
		$m_files = new EmundusModelFiles();

		try {
			$fnumInfos = $m_files->getFnumInfos($fnum);

			$query
				->select('cc.eb_registration,sc.event')
				->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
				->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $db->quoteName('sc.id') . ' = ' . $db->quoteName('cc.campaign_id'))
				->where($db->quoteName('cc.fnum') . ' = ' . $db->quote($fnum));

			$db->setQuery($query);
			$registration = $db->loadObject();

			// TODO: Set states as params in the XML file
			switch ($state) {
				// Unregister the user from the event
				case 3:
					if (!empty($registration->eb_registration)) {
						$query
							->clear()
							->delete('#__eb_registrants')
							->where($db->quoteName('id') . ' = ' . $db->quote($registration->eb_registration));

						$db->setQuery($query);
						$db->execute();
					}
					break;

				// Register the user to the event
				case 4:
					if (empty($registration->eb_registration)) {
						$query
							->clear()
							->select('event')
							->from($db->quoteName('#__emundus_setup_campaigns'))
							->where($db->quoteName('id') . ' = ' . $fnumInfos['id']);

						$db->setQuery($query);
						$event_id = $db->loadResult();

						$columns = ['event_id', 'user_id', 'first_name', 'last_name', 'email', 'number_registrants', 'register_date', 'payment_date', 'published', 'language', 'user_ip'];
						$values  = [$event_id, $fnumInfos['applicant_id'], $db->quote($fnumInfos['name']), $db->quote($fnumInfos['name']), $db->quote($fnumInfos['email']), 1, $db->quote(date('Y-m-d h:i:s')), $db->quote(date('Y-m-d h:i:s')), 1, $db->quote('fr-FR'), $db->quote($ip)];

						$query
							->clear()
							->insert($db->quoteName('#__eb_registrants'))
							->columns($columns)
							->values(implode(',', $values));

						$db->setQuery($query);
						$db->execute();
						$registration = $db->insertid();

						$query
							->clear()
							->update('#__emundus_campaign_candidature')
							->set($db->quoteName('eb_registration') . ' = ' . $db->quote($registration))
							->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));

						$db->setQuery($query);
						$db->execute();
					}
					else {
						$query
							->clear()
							->update('#__eb_registrants')
							->set($db->quoteName('published') . ' = 1')
							->where($db->quoteName('id') . ' = ' . $db->quote($registration->eb_registration));

						$db->setQuery($query);
						$db->execute();
					}
					break;
				default:
					break;
			}
		}
		catch (Exception $e) {
			JLog::add('plugin/emundus/eb_book_event_status error :' . $query->__toString() . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return true;
	}
}
