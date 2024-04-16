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
use GuzzleHttp\Client;
use GuzzleHttp\Message\Request;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';
require_once JPATH_SITE.'/components/com_emundus/classes/api/Api.php';
use classes\api\IxParapheur;

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emunduseparapheur
 * @since       3.0
 */

class PlgFabrik_Cronemunduseparapheur extends PlgFabrik_Cron {

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
		Log::addLogger(['text_file' => 'com_emundus.emundusrecallmissingdoc.info.php'], Log::INFO, 'com_emundus.emundusrecallmissingdoc');
		Log::addLogger(['text_file' => 'com_emundus.emundusrecallmissingdoc.error.php'], Log::ERROR, 'com_emundus.emundusrecallmissingdoc');

		$app = Factory::getApplication();
		$params = $this->getParams();
		$eMConfig = ComponentHelper::getParams('com_emundus');
		$automated_task_user = $eMConfig->get('automated_task_user', 62);

		$status_to_check = $params->get('status', null);

		$files_requests = [];
		if($status_to_check !== null) {
			$status_to_check = explode(',', $status_to_check);
			$db = FabrikWorker::getDbo();
			$query = $db->getQuery(true);

			$query->select('efr.id,efr.fnum,efr.signer_id,efr.uploaded,efr.attachment_id,efr.email,efr.student_id')
				->from($db->quoteName('#__emundus_files_request','efr'))
				->leftJoin($db->quoteName('#__emundus_campaign_candidature','ecc').' ON '.$db->quoteName('ecc.fnum').' = '.$db->quoteName('efr.fnum'))
				->where($db->quoteName('ecc.status') . ' IN (' . implode(',',$db->quote($status_to_check)) .')')
				->where($db->quoteName('efr.uploaded') . ' = 0')
				->where($db->quoteName('efr.signer_id') . ' IS NOT NULL');
			$db->setQuery($query);
			$files_requests = $db->loadObjectList();

			if(!empty($files_requests)) {
				require_once JPATH_SITE.'/components/com_emundus/classes/api/IxParapheur.php';
				require_once JPATH_SITE.'/components/com_emundus/helpers/checklist.php';
				require_once JPATH_SITE.'/components/com_emundus/models/logs.php';
				require_once JPATH_SITE.'/components/com_emundus/models/files.php';
				require_once JPATH_SITE.'/components/com_emundus/models/application.php';
				$h_checklist = new EmundusHelperChecklist();
				$m_files = new EmundusModelFiles();
				$m_application = new EmundusModelApplication();

				$query->clear()
					->select('esa.id,esa.lbl')
					->from($db->quoteName('#__emundus_setup_attachments','esa'));
				$db->setQuery($query);
				$attachments_lbl = $db->loadAssocList('id');

				$api = new IxParapheur();
				foreach ($files_requests as $file_request) {
					$attachmentType = $m_application->getAttachmentByID($file_request->attachment_id)['value'];
					$edossier = $api->getDossier($file_request->signer_id);

					if($edossier['data']->message == 'ok') {
						if($edossier['data']->payload->statut == 'Traite') {
							$idDocument = $edossier['data']->payload->documents->principal->identifiant;

							$fnumInfos = $m_files->getFnumInfos($file_request->fnum);
							do {
								$nom  = $h_checklist->setAttachmentName($edossier['data']->payload->documents->principal->nom, $attachments_lbl[$file_request->attachment_id]['lbl'], $fnumInfos);
								$path = JPATH_ROOT.'/images/emundus/files/' . $file_request->student_id . '/' . $nom;
							} while(file_exists($path));

							$signed_file = $api->getDocumentContent($idDocument,$path);

							if($signed_file['status'] == 200 && file_exists($path)) {
								$upload = [
									'timedate' => date('Y-m-d H:i:s'),
									'user_id' => $automated_task_user,
									'fnum' => $file_request->fnum,
									'attachment_id' => $file_request->attachment_id,
									'filename' => $nom,
									'can_be_deleted' => 0,
									'can_be_viewed' => 1,
									'size' => filesize($path),
								];
								$upload = (object) $upload;

								$uploaded = $db->insertObject('#__emundus_uploads', $upload);

								if($uploaded) {
									$upload_id = $db->insertid();
									PluginHelper::importPlugin('emundus', 'sync_file');
									$app->triggerEvent('onAfterUploadFile', [['upload_id' => $upload_id]]);

									PluginHelper::importPlugin('emundus', 'custom_event_handler');
									$app->triggerEvent('callEventHandler', ['onAfterFileSignedEparapheur', ['fnum' => $file_request->fnum, 'attachment_id' => $file_request->attachment_id, 'upload_id' => $upload_id, 'email' => $file_request->email, 'signer_id' => $file_request->signer_id]]);

									$query->clear()
										->update($db->quoteName('#__emundus_files_request'))
										->set($db->quoteName('uploaded') . ' = 1')
										->where($db->quoteName('id') . ' = ' . $db->quote($file_request->id));
									$db->setQuery($query);
									$db->execute();

									$logsStd = new stdClass();
									$logsStd->element = '[' . $attachmentType . ']';
									$logsStd->details = $nom;
									$logsParams = array('created' => [$logsStd]);

									EmundusModelLogs::log($automated_task_user, $file_request->student_id, $file_request->fnum, 4, 'c', 'COM_EMUNDUS_ACCESS_ATTACHMENT_CREATE',json_encode($logsParams,JSON_UNESCAPED_UNICODE));
								}
							}
						}
					}
				}
			}
		}


		$this->log = "\n process " . count($files_requests) . " file request(s)";

		return count($files_requests);
	}
}
