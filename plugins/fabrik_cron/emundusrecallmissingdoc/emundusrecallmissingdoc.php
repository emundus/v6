<?php
/**
 * A cron task to email a recall to applications with missing doc
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecallmissingdoc
 * @copyright   Copyright (C) 2021 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgFabrik_Cronemundusrecallmissingdoc extends PlgFabrik_Cron {

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
	 * @param array  &$data data
	 *
	 * @return  int  number of records updated
	 * @throws Exception
	 */
	public function process(&$data, &$listModel) {
		jimport('joomla.mail.helper');

		// LOGGER
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.emundusrecallmissingdoc.info.php'], JLog::INFO, 'com_emundus.emundusrecallmissingdoc');
		JLog::addLogger(['text_file' => 'com_emundus.emundusrecallmissingdoc.error.php'], JLog::ERROR, 'com_emundus.emundusrecallmissingdoc');

		$params = $this->getParams();
		$eMConfig = JComponentHelper::getParams('com_emundus');

		$reminder_mail_id = $params->get('reminder_mail_id', '15');
		$reminder_programme_code = $params->get('reminder_programme_code', '');
		$reminder_days = $params->get('reminder_days', '30');
		$reminder_deadline = $params->get('reminder_deadline', '30, 15, 7, 1, 0');

		$this->log = '';

		// Get list of applicants to notify
		//AND jesc.admission_start_date <= NOW() AND jesc.admission_end_date >= NOW()
		$db = FabrikWorker::getDbo();
		$query = 'SELECT jecc.applicant_id, GROUP_CONCAT(jecc.fnum SEPARATOR ",") as fnum, GROUP_CONCAT(jesa.id SEPARATOR ",") as attachment_id, GROUP_CONCAT(jesa.value SEPARATOR "|") as attachment_label
			FROM jos_emundus_campaign_candidature as jecc
			LEFT JOIN jos_emundus_setup_campaigns jesc on jecc.campaign_id = jesc.id
			LEFT JOIN jos_emundus_setup_emails_trigger_cron jesetc on jecc.status = jesetc.step AND jesetc.published=1
			LEFT JOIN jos_emundus_uploads jeu on jecc.fnum = jeu.fnum AND jeu.attachment_id=jesetc.attachment_id
			LEFT JOIN jos_emundus_setup_attachments jesa on jesetc.attachment_id = jesa.id
			WHERE jesetc.id IS NOT NULL AND jeu.attachment_id IS NULL
			AND jesetc.date = CURDATE()';

		if (!empty($reminder_programme_code)) {
			$query .= ' AND jesc.training IN ('.$reminder_programme_code.')';
		}
		$query .= ' GROUP BY jecc.applicant_id';

		$db->setQuery($query);

		try {
			$applicants = $db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting applicants to be notify with error '.$e->getMessage().' and query : '.$query, JLog::ERROR, 'com_emundus.emundusrecallmissingdoc');
			return false;
		}

		// Generate emails from template and store it in message table
		if (!empty($applicants)) {
			include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');
			include_once(JPATH_SITE.'/components/com_emundus/controllers/messages.php');
			$c_messages = new EmundusControllerMessages();

			foreach ($applicants as $applicant) {
				$missing_doc = explode('|', $applicant->attachment_label);

				$missing_doc_html = '';
				if (count($applicant->attachment_label) > 0) {
					$missing_doc_html = '<ul>';
					foreach ($missing_doc as $doc) {
						$missing_doc_html .= '<li>'.$doc.'</li>';
					}
					$missing_doc_html .= '</ul>';
				}

				$applicant->fnum = explode(',', $applicant->fnum)[0];

				$post = array(
					'FNUM' => $applicant->fnum,
					'MISSING_DOC' => $missing_doc_html,
	                'FIRSTNAME' => explode(',', $applicant->firstname)[0],
	                'LASTNAME' => explode(',', strtoupper($applicant->lastname))[0]
				);

				$c_messages->sendEmail($applicant->fnum,$reminder_mail_id,$post);

                // to avoid been considered as a spam process or DDoS
                sleep(0.1);

			}
		}

		$this->log .= "\n process " . count($applicants) . " applicant(s)";

		return count($applicants);
	}
}
