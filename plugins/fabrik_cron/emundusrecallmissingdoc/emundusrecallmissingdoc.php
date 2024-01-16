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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

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
class PlgFabrik_Cronemundusrecallmissingdoc extends PlgFabrik_Cron
{

	/**
	 * Do the plugin action
	 *
	 * @param   array  &$data  data
	 *
	 * @return  int  number of records updated
	 * @throws Exception
	 */
	public function process(&$data, &$listModel)
	{
		jimport('joomla.mail.helper');

		// LOGGER
		jimport('joomla.log.log');
		Log::addLogger(['text_file' => 'com_emundus.emundusrecallmissingdoc.info.php'], Log::INFO, 'com_emundus.emundusrecallmissingdoc');
		Log::addLogger(['text_file' => 'com_emundus.emundusrecallmissingdoc.error.php'], Log::ERROR, 'com_emundus.emundusrecallmissingdoc');

		include_once(JPATH_SITE . '/components/com_emundus/models/emails.php');
		include_once(JPATH_SITE . '/components/com_emundus/controllers/messages.php');
		$c_messages = new EmundusControllerMessages();

		$params   = $this->getParams();
		$eMConfig = ComponentHelper::getParams('com_emundus');

		$reminder_mail_id             = $params->get('reminder_mail_id', '15');
		$reminder_programme_code      = $params->get('reminder_programme_code', '');
		$notify_coordinator           = $params->get('notify_coordinator', 0);
		$coordinator_profile          = $params->get('coordinator_profile', 2);
		$coordinator_reminder_mail_id = $params->get('coordinator_reminder_mail_id', 83);
		$coordinator_reminder_delay = $params->get('coordinator_reminder_delay', 30);

		$this->log = '';

		// Get list of applicants to notify
		$db = FabrikWorker::getDbo();

		$query = $db->getQuery(true);

		$query->select('jecc.applicant_id, GROUP_CONCAT(jecc.fnum SEPARATOR ",") as fnum, GROUP_CONCAT(jesa.id SEPARATOR ",") as attachment_id, GROUP_CONCAT(jesa.value SEPARATOR "|") as attachment_label')
			->from($db->quoteName('#__emundus_campaign_candidature', 'jecc'))
			->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'jesc') . ' ON ' . $db->quoteName('jecc.campaign_id') . ' = ' . $db->quoteName('jesc.id'))
			->leftJoin($db->quoteName('#__emundus_setup_emails_trigger_cron', 'jesetc') . ' ON ' . $db->quoteName('jecc.status') . ' = ' . $db->quoteName('jesetc.step') . ' AND ' . $db->quoteName('jesetc.published') . ' = 1')
			->leftJoin($db->quoteName('#__emundus_uploads', 'jeu') . ' ON ' . $db->quoteName('jecc.fnum') . ' = ' . $db->quoteName('jeu.fnum') . ' AND ' . $db->quoteName('jeu.attachment_id') . ' = ' . $db->quoteName('jesetc.attachment_id'))
			->leftJoin($db->quoteName('#__emundus_setup_attachments', 'jesa') . ' ON ' . $db->quoteName('jesetc.attachment_id') . ' = ' . $db->quoteName('jesa.id'))
			->where($db->quoteName('jesetc.id') . ' IS NOT NULL')
			->where($db->quoteName('jeu.attachment_id') . ' IS NULL')
			->where($db->quoteName('jesetc.date') . ' = CURDATE()');

		if (!empty($reminder_programme_code)) {
			$query->where($db->quoteName('jesc.training') . ' IN (' . $reminder_programme_code . ')');
		}
		$query->group('jecc.applicant_id');

		try {
			$db->setQuery($query);
			$applicants = $db->loadObjectList();
		}
		catch (Exception $e) {
			Log::add('Error getting applicants to be notify with error ' . $e->getMessage() . ' and query : ' . $query, Log::ERROR, 'com_emundus.emundusrecallmissingdoc');

			return false;
		}

		if (!empty($applicants)) {

			foreach ($applicants as $applicant) {
				$missing_doc = explode('|', $applicant->attachment_label);

				$missing_doc_html = '';
				if (count($missing_doc) > 0) {
					$missing_doc_html = '<ul>';
					foreach ($missing_doc as $doc) {
						$missing_doc_html .= '<li>' . $doc . '</li>';
					}
					$missing_doc_html .= '</ul>';
				}

				$applicant->fnum = explode(',', $applicant->fnum)[0];

				$post = array(
					'FNUM'        => $applicant->fnum,
					'MISSING_DOC' => $missing_doc_html,
					'FIRSTNAME'   => explode(',', $applicant->firstname)[0],
					'LASTNAME'    => explode(',', strtoupper($applicant->lastname))[0]
				);

				$c_messages->sendEmail($applicant->fnum, $reminder_mail_id, $post);

				// to avoid been considered as a spam process or DDoS
				sleep(0.1);

			}
		}

		$this->log .= "\n process " . count($applicants) . " applicant(s)";

		if ($notify_coordinator == 1) {
			$coordinators = [];

			$query->clear()
				->select('jecc.applicant_id, eu.firstname, eu.lastname, u.email, ess.value as status, GROUP_CONCAT(jesa.id SEPARATOR ",") as attachment_id, GROUP_CONCAT(jesa.value SEPARATOR "|") as attachment_label')
				->from($db->quoteName('#__emundus_campaign_candidature', 'jecc'))
				->leftJoin($db->quoteName('#__emundus_setup_status','ess').' ON '.$db->quoteName('ess.step').' = '.$db->quoteName('jecc.status'))
				->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'jesc') . ' ON ' . $db->quoteName('jecc.campaign_id') . ' = ' . $db->quoteName('jesc.id'))
				->leftJoin($db->quoteName('#__emundus_setup_emails_trigger_cron', 'jesetc') . ' ON ' . $db->quoteName('jecc.status') . ' = ' . $db->quoteName('jesetc.step') . ' AND ' . $db->quoteName('jesetc.published') . ' = 1')
				->leftJoin($db->quoteName('#__emundus_uploads', 'jeu') . ' ON ' . $db->quoteName('jecc.fnum') . ' = ' . $db->quoteName('jeu.fnum') . ' AND ' . $db->quoteName('jeu.attachment_id') . ' = ' . $db->quoteName('jesetc.attachment_id'))
				->leftJoin($db->quoteName('#__emundus_setup_attachments', 'jesa') . ' ON ' . $db->quoteName('jesetc.attachment_id') . ' = ' . $db->quoteName('jesa.id'))
				->leftJoin($db->quoteName('#__emundus_users', 'eu') . ' ON ' . $db->quoteName('eu.user_id') . ' = ' . $db->quoteName('jecc.applicant_id'))
				->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('jecc.applicant_id'))
				->where($db->quoteName('jesetc.id') . ' IS NOT NULL')
				->where($db->quoteName('jeu.attachment_id') . ' IS NULL')
				->where($db->quoteName('jesetc.date') . ' = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL '.$coordinator_reminder_delay.' DAY), "%Y-%m-%d")');

			if (!empty($reminder_programme_code)) {
				$query->where($db->quoteName('jesc.training') . ' IN (' . $reminder_programme_code . ')');
			}
			$query->group('jecc.applicant_id');

			try {
				$db->setQuery($query);
				$applicants = $db->loadObjectList();
			}
			catch (Exception $e) {
				Log::add('Error getting applicants to be notify with error ' . $e->getMessage() . ' and query : ' . $query, Log::ERROR, 'com_emundus.emundusrecallmissingdoc');

				return false;
			}

			if (!empty($applicants)) {
				$query->clear()
					->select('u.email, eu.firstname, eu.lastname')
					->from($db->quoteName('#__emundus_users', 'eu'))
					->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('eu.user_id') . ' = ' . $db->quoteName('u.id'))
					->leftJoin($db->quoteName('#__emundus_users_profiles', 'eup') . ' ON ' . $db->quoteName('eup.user_id') . ' = ' . $db->quoteName('eu.user_id'))
					->where($db->quoteName('eu.profile') . ' = ' . $coordinator_profile)
					->orWhere($db->quoteName('eup.profile_id') . ' = ' . $coordinator_profile)
					->group('u.id');
				$db->setQuery($query);
				$coordinators = $db->loadObjectList();

				if (!empty($coordinators)) {
					$html = '<ul>';

					foreach ($applicants as $applicant) {
						$missing_doc = explode('|', $applicant->attachment_label);

						if (count($missing_doc) > 0) {
							foreach ($missing_doc as $doc) {
								$html             .= '<li>' . $applicant->firstname . ' ' . $applicant->lastname . ' (<a href="mailto:'.$applicant->email.'" target="_blank">' . $applicant->email . '</a>)' . ' - ' . $applicant->status . ' - ' . $doc . '</li>';
							}
						}
					}

					$html .= '</ul>';

					$post = array(
						'MISSING_DOC_APPLICANTS' => $html,
					);

					foreach ($coordinators as $coordinator) {
						$post['NAME'] = strtoupper($coordinator->lastname) . ' ' . ucfirst($coordinator->firstname);
						$c_messages->sendEmailNoFnum($coordinator->email, $coordinator_reminder_mail_id, $post);
					}
				}
			}
		}

		if($notify_coordinator == 1 ) {
			return count($coordinators) + count($applicants);
		} else {
			return count($applicants);
		}
	}
}
