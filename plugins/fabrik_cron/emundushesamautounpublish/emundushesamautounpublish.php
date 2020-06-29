<?php
/**
 * A cron task to email a recall and unpublish 2 month old offers on HESAM
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to unpublish 2 month old offers on HESAM and send email reminders
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */
class PlgFabrik_CronEmundushesamautounpublish extends PlgFabrik_Cron {

	/**
	 * Check if the user can use the plugin
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
	 */
	public function canUse($location = null, $event = null) {
		return true;
	}

	/**
	 * Do the plugin action
	 *
	 * @param   array  &$data  data
	 *
	 * @param           $listModel
	 *
	 * @return  int  number of records updated
	 */
	public function process(&$data, &$listModel) {
		jimport('joomla.mail.helper');

		$params = $this->getParams();
		$reminder_mail_id = $params->get('reminder_mail_id', '99');
		$deadline = $params->get('reminder_deadline', '60');
		$status_for_send = $params->get('reminder_status', '1');
		$unpublish_status = $params->get('unpublish_status', '6');

		$this->log = '';

		// Get list of offers to unpublish and notify
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->select('eu.user_id, u.email, eu.firstname, eu.lastname, ecc.fnum, ecc.date_submitted, DATEDIFF(ecc.date_submitted , now()) as left_days, ep.titre')
			->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
			->leftJoin($db->quoteName('#__users', 'u').' ON u.id = ecc.applicant_id')
			->leftJoin($db->quoteName('#__emundus_users', 'eu').' ON eu.user_id = u.id')
			->leftJoin($db->quoteName('#__emundus_projet', 'ep').' ON '.$db->quoteName('ep.fnum').' LIKE '.$db->quoteName('ecc.fnum'))
			->where('ecc.published = 1 AND u.block = 0 AND ecc.status in ('.$status_for_send.') AND DATEDIFF(now(), ecc.date_submitted) IN ('.$deadline.')');

		$db->setQuery($query);
		$offers = $db->loadObjectList();

		// Generate emails from template and store it in message table
		if (!empty($offers)) {

			require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'messages.php');
			require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
			$c_messages = new EmundusControllerMessages();
			$m_files = new EmundusModelFiles();

			foreach ($offers as $offer) {

				$post = array(
					'FNUM' => $offer->fnum,
	                'FIRSTNAME' => $offer->firstname,
	                'LASTNAME' => strtoupper($offer->lastname),
					'OFFER_NAME' => $offer->titre,
					'SITE_URL' => JUri::base()
				);

				// Send the email notifying the offer has been unpublished.
				if ($c_messages->sendEmail($offer->fnum, $reminder_mail_id, $post)) {

					// Set the status to the 'automatically unpublished' status.
					$m_files->updateState($offer->fnum, $unpublish_status);

					// to avoid been considered as a spam process or DDoS
					sleep(0.1);
				}
			}
		}

		$this->log .= "\n process " . count($offers) . " offer(s)";
		return count($offers);
	}
}
