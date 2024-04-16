<?php
/**
 * @package	eMundus
 * @version	0.0.1
 * @author	eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use classes\api\FileSynchronizer;

defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE.'/components/com_emundus/classes/api/Api.php';
require_once JPATH_SITE.'/components/com_emundus/classes/api/IxParapheur.php';

use classes\api\IxParapheur;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;

class plgEmundusEparapheur extends CMSPlugin {

	private $db;
	private $user;

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		jimport('joomla.log.log');
		Log::addLogger(array('text_file' => 'com_emundus.eparapheur.php'), Log::ALL, array('com_emundus_eparapheur'));

		if(version_compare(JVERSION, '4.0', '>')) {
			$this->db = Factory::getContainer()->get('DatabaseDriver');
			$this->user = Factory::getApplication()->getIdentity();
		} else {
			$this->db = Factory::getDbo();
			$this->user = Factory::getUser();
		}
	}

	/**
	 * @param $args
	 *
	 * @return void
	 *
	 * TODO: Manage circuits, other types of signers
	 */
	function onSyncEparapheur($args): void
	{
		if (!isset($args['fnum']) && !isset($args['signer_email']) && !isset($args['attachment_id']) && !isset($args['file'])) {
			Log::add('Missing parameters', Log::ERROR, 'com_emundus_eparapheur');
			return;
		}

		try {
			$api = new IxParapheur();

			$nature = $args['nature'] ?? $api->getNatures()[0]->identifiant;
			$name   = $args['name'] ?? 'Signature pour le dossier ';

			if (!empty($nature)) {
				$uid = $api->getUtilisateurs($args['signer_email'])[0]->identifiant;

				if (!empty($uid)) {
					$datas = [
						'nom'    => $name . $args['fnum'],
						'nature' => $nature,
						'etapes' => [
							[
								'typeCible'        => 'Utilisateur',
								'type'             => 'Signature',
								'identifiantCible' => $uid
							]
						]
					];

					$result = $api->createDossier($datas);

					if ($result['data']->message == 'ok') {
						$idDossier = $result['data']->payload->identifiant;

						require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'evaluation.php');
						$m_files = new EmundusModelFiles();

						$fnumInfos = $m_files->getFnumInfos($args['fnum']);

						$query = $this->db->getQuery(true);

						$query->select('id')
							->from($this->db->quoteName('#__emundus_files_request'))
							->where($this->db->quoteName('fnum') . ' = ' . $this->db->quote($args['fnum']))
							->where($this->db->quoteName('attachment_id') . ' = ' . $args['attachment_id']);
						$this->db->setQuery($query);
						$files_request_id = $this->db->loadResult();

						if (empty($files_request_id)) {
							$insert = [
								'time_date'     => date('Y-m-d H:i:s'),
								'student_id'    => $fnumInfos['applicant_id'],
								'fnum'          => $args['fnum'],
								'keyid'         => '',
								'attachment_id' => $args['attachment_id'],
								'uploaded'      => 0,
								'email'         => $args['signer_email'],
								'signer_id'     => $idDossier
							];
							$insert = (object) $insert;

							$this->db->insertObject('#__emundus_files_request', $insert);
						}
						else {
							$update = [
								'id'        => $files_request_id,
								'signer_id' => $idDossier
							];
							$update = (object) $update;

							$this->db->updateObject('#__emundus_files_request', $update, 'id');
						}

						$datas = [
							'type'        => 'principal',
							'estASigner'  => true,
							'estPublique' => true
						];

						$document_added = $api->addDocument($idDossier, $datas, $args['file'], JPATH_SITE . '/images/emundus/files/' . $fnumInfos['applicant_id'] . '/' . $args['file']);

						if ($document_added['data']->message == 'ok') {
							$transmis = $api->actionDossier($idDossier);

							if ($transmis['status'] == 200) {
								$logs_params = ['created' => ['signer_id' => 'N°' . $idDossier, 'signer' => $args['signer_email']]];
								EmundusModelLogs::log($this->user->id, (int) substr($args['fnum'], -7), $args['fnum'], 33, 'c', 'COM_EMUNDUS_ACCESS_SYNC_EPARAPHEUR', json_encode($logs_params, JSON_UNESCAPED_UNICODE));
							}
							else {
								throw new Exception('COM_EMUNDUS_ACCESS_SYNC_EPARAPHEUR_FAILED_TRANSMIS',500);
							}
						}
						else {
							throw new Exception('COM_EMUNDUS_ACCESS_SYNC_EPARAPHEUR_FAILED_DOCUMENT',500);
						}
					}
					else {
						throw new Exception('COM_EMUNDUS_ACCESS_SYNC_EPARAPHEUR_FAILED_DOSSIER',500);
					}
				}
				else {
					throw new Exception('COM_EMUNDUS_ACCESS_SYNC_EPARAPHEUR_FAILED_SIGNER',500);
				}
			}
			else {
				throw new Exception('COM_EMUNDUS_ACCESS_SYNC_EPARAPHEUR_FAILED_NATURE',500);
			}
		}
		catch (Exception $e) {
			Log::add($e->getMessage(), Log::ERROR, 'com_emundus_eparapheur');
			$logs_params = ['created' => ['signer_id' => !empty($idDossier) ? $idDossier : '','signer' => $args['signer_email']]];
			EmundusModelLogs::log($this->user->id, (int) substr($args['fnum'], -7), $args['fnum'], 33, 'c', $e->getMessage(), json_encode($logs_params, JSON_UNESCAPED_UNICODE));
		}
	}
}
