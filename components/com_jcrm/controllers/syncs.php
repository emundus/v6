<?php
/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';
require_once JPATH_COMPONENT.DS.'models'.DS.'syncs.php';
require_once JPATH_COMPONENT.DS.'models'.DS.'contact.php';

/**
 * Syncs list controller class.
 */
class JcrmControllerSyncs extends JcrmController {

	public function getdata() {
		$jinput = JFactory::getApplication()->input;
		$page = $jinput->getInt('current', 1);

		$params = JComponentHelper::getParams('com_jcrm');
		$select = $params->get('sync_table_select');
		$tableName = $params->get('sync_table_name');
		$colContact = $params->get('sync_contact_id_column');
		$colAccount = $params->get('sync_account_id_column');
		$nbRef = $params->get('sync_nb_referees');

		$m_syncs = new JcrmModelSyncs();
		$datas = $m_syncs->getData($select, $tableName, $colContact, $colAccount, $nbRef, $page);
		$syncs = array();

		if (!empty($datas)) {
			foreach ($datas as $data) {
				for ($i = 1; $i <= $nbRef; $i++) {
					if (($data['id_account_'.$i] === "0") || ($data['id_contact_'.$i] === "0") && $data['Email_'.$i]) {
						$sync = new stdClass();
						$sync->orga = new stdClass();
						$sync->contact = new stdClass();
						$sync->orga->orgaId = "new";
						$sync->orga->synced = false;
						$sync->contact->synced = false;
						$sync->contact->cId = "new";

						// Suppression des " dans orga
						$neworga = str_replace('"', ' ', $data['Organisation_'.$i]);
						$data['Organisation_'.$i] = $neworga;

						if ($data['id_account_'.$i] == 0) {
							$sync->orga->options = $m_syncs->getSiblingOrgs($data['Organisation_'.$i]);
							if (!empty($sync->orga->options)) {
								$sync->orga->orgaId = $sync->orga->options[0]->id;
							}
						} else {
							$sync->orga->synced = true;
							if ($data['id_contact_'.$i] === "0") {
								$contactCheck = $m_syncs->findContact($data['Email_'.$i]);
								if (!empty($contactCheck)) {
									$sync->contact->options = $contactCheck;
									$sync->contact->cId = intval($contactCheck[0]->id);
								}
							} else {
								$sync->contact->synced = true;
							}
						}
						$sync->contact->index = $i;
						$sync->contact->refId = $data['id'];
						$sync->contact->firstName = $data['First_Name_'.$i];
						$sync->contact->lastName = $data['Last_Name_'.$i];
						$sync->contact->organisation = $data['Organisation_'.$i];
						$sync->contact->email = $data['Email_'.$i];
						$syncs[] = $sync;
					}
				}
			}
		}
		$offset = (($page - 1) * 20);
		$res = array('nbItems' => count($syncs), 'nbPages' => intval((count($syncs)/ 20) + 1), 'toSyncs' => array_slice($syncs, $offset, 20));
		echo json_encode((object) $res);
		exit();
	}

	public function syncorga() {
		$input = (object) json_decode(file_get_contents('php://input'));
		$m_syncs = new JcrmModelSyncs();
		$m_contact = new JcrmModelContact();

		$params = JComponentHelper::getParams('com_jcrm');
		$select = $params->get('sync_table_select');
		$tableName 	= $params->get('sync_table_name');
		$colAccount = $params->get('sync_account_id_column');

		$referent = $m_syncs->getReferent($select, $tableName, $input->refId);
		if ($input->orgaId == "new") {
			$newOrga = JcrmFrontendHelper::buildOrgaFromReferent($referent, $input->index);
			$org = $m_contact->addContact($newOrga);
			$input->orgaId = $org['id'];
		}
		$m_syncs->syncRefOrga($tableName, $colAccount, $input->refId, $input->orgaId, $input->index);
		$cIdDefault = "new";
		if (!empty($referent['Email_'.$input->index])) {
			$contactCheck = $m_syncs->findContact($referent['Email_'.$input->index]);
			if (!empty($contactCheck)) {
				$cIdDefault = $contactCheck[0]->id;
			}
		}
		$res = array('orgaSynced' => 1, 'options' => $contactCheck, 'cIdDefault' => $cIdDefault);
		echo json_encode((object) $res);
		exit();
	}

	/**
	 *
	 */
	public function synccontact() {
		$input = (object) json_decode(file_get_contents('php://input'));
		$m_syncs = new JcrmModelSyncs();
		$m_contact = new JcrmModelContact();

		$params = JComponentHelper::getParams('com_jcrm');
		$select = $params->get('sync_table_select');
		$tableName = $params->get('sync_table_name');
		$colContact = $params->get('sync_contact_id_column');

		$referent = $m_syncs->getReferent($select, $tableName, $input->refId);
		if ($input->contactId == "new") {
			$newContact = JcrmFrontendHelper::buildContactFromReferent($referent, $input->index);

			// Create or get group
			if (!empty($newContact->formGroup)) {
				$newContact->formGroup = $m_contact->createOrSelectGroup($newContact->formGroup);
			}

			$contact = $m_contact->addContact($newContact);
			$input->contactId = $contact['id'];
		}

		$m_syncs->syncRef($tableName, $colContact, $input->refId, $input->contactId, $input->index);
		$res = array('ContactSynced' => 1);

		echo json_encode((object) $res);
		exit();
	}


	public function refresh() {
		$input = (object) json_decode(file_get_contents('php://input'));
		$m_syncs = new JcrmModelSyncs();
		$select = JComponentHelper::getParams('com_jcrm')->get('sync_table_select');
		$tableName = JComponentHelper::getParams('com_jcrm')->get('sync_table_name');
		$colContact = JComponentHelper::getParams('com_jcrm')->get('sync_contact_id_column');
		$colAccount = JComponentHelper::getParams('com_jcrm')->get('sync_account_id_column');
		$referent = $m_syncs->getReferent($select, $tableName, $input->refId);
		$sync = new stdClass();
		$sync->orgaSynced = 0;
		$sync->orgaOptions = array();
		$sync->contactSynced = 0;
		$sync->contactOptions = array();
		$sync->orgaIdDefault = "new";
		$sync->cIdDefault = "new";
		if ($referent[$colAccount.'_'.$input->index] !== "0") {
			$sync->orgaSynced = true;
			if ($referent[$colContact.'_'.$input->index] !== "0") {
				$sync->contactSynced = true;
			} else {
				$sync->contactOptions = $m_syncs->findContact($referent['Email_'.$input->index]);
				if (!empty($sync->contactOptions)) {
					$sync->cIdDefault = $sync->contactOptions[0]->id;
				}
			}
		} else {
			$sync->orgaOptions = $m_syncs->getSiblingOrgs($referent['Organisation_'.$input->index]);
			if (!empty($sync->orgaOptions)) {
				$sync->orgaIdDefault = $sync->orgaOptions[0]->id;
			}
		}
		echo json_encode($sync);
		exit();
	}

	public function ignore() {
		$input = (object) json_decode(file_get_contents('php://input'));
		$m_syncs = new JcrmModelSyncs();
		$tableName = JComponentHelper::getParams('com_jcrm')->get('sync_table_name');
		$colContact = JComponentHelper::getParams('com_jcrm')->get('sync_contact_id_column');
		$colAccount = JComponentHelper::getParams('com_jcrm')->get('sync_account_id_column');
		$res = new stdClass();
		$res->status = $m_syncs->ignore($tableName, $colContact, $colAccount, $input->refId, $input->index);
		echo json_encode($res);
		exit();
	}


	public function ignoreAll() {
		$input = (object) json_decode(file_get_contents('php://input'));
		$m_syncs = new JcrmModelSyncs();

		$params = JComponentHelper::getParams('com_jcrm');
		$tableName = $params->get('sync_table_name');
		$colContact = $params->get('sync_contact_id_column');
		$colAccount = $params->get('sync_account_id_column');

		$res = new stdClass();
		foreach($input as $referent) {
			$res->status = $m_syncs->ignore($tableName, $colContact, $colAccount, $referent->contact->refId, $referent->contact->index);
		}

		echo json_encode($res);
		exit();
	}

	public function validall() {
		$input = (object) json_decode(file_get_contents('php://input'));
		$m_syncs = new JcrmModelSyncs();
		$m_contact = new JcrmModelContact();

		$params = JComponentHelper::getParams('com_jcrm');
		$tableName = $params->get('sync_table_name');
		$select = $params->get('sync_table_select');
		$colContact = $params->get('sync_contact_id_column');
		$colAccount = $params->get('sync_account_id_column');

		$res = new stdClass();
		foreach ($input as $referent) {
			if (!$referent->orga->synced) {

				$ref = $m_syncs->getReferent($select, $tableName, $referent->contact->refId);

				if (!empty($ref['Organisation_'.$referent->contact->index])) {
					$allreadyContact = $m_syncs->getSiblingOrgs($ref['Organisation_'.$referent->contact->index]);
					if ($referent->orga->orgaId == "new") {
						if (!is_null($allreadyContact) && empty($allreadyContact)) {
							$orga = JcrmFrontendHelper::buildOrgaFromReferent($ref, $referent->contact->index);
							$orga = $m_contact->addContact($orga);
							$m_syncs->syncRefOrga($tableName, $colAccount, $referent->contact->refId, $orga['id'], $referent->contact->index);
						}
					} else {
						$m_syncs->syncRefOrga($tableName, $colAccount, $referent->contact->refId, $referent->orga->orgaId, $referent->contact->index);
					}
				}

			} else {

				if (!$referent->contact->synced) {
					$ref = $m_syncs->getReferent($select, $tableName, $referent->contact->refId);
					if (!empty($ref['Last_Name_'.$referent->contact->index])) {
						$allreadyContact = $m_syncs->findContact($ref['Email_'.$referent->contact->index]);
						if ($referent->contact->cId == "new") {
							if (!is_null($allreadyContact) && empty($allreadyContact)) {
								$contact = JcrmFrontendHelper::buildContactFromReferent($ref, $referent->contact->index, $colGroup);
								$contact = $m_contact->addContact($contact);
								$m_syncs->syncRef($tableName, $colContact, $referent->contact->refId, $contact['id'], $referent->contact->index);
							}
						} else {
							$m_syncs->syncRef($tableName, $colContact, $referent->contact->refId, $referent->contact->cId, $referent->contact->index);
						}
					}
				}
			}

			$group = $m_contact->getGroupsByContact($referent->contact->refId);
			if (!empty($group)) {

			}
		}

		$res->status = true;
		echo json_encode($res);
		exit();
	}
}