<?php
/**
 * A cron task to email a recall to incomplet applications
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
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgFabrik_Cronemundusnantesscholargpush extends PlgFabrik_Cron {

	/**
	 * Check if the user can use the plugin
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
	 *
	 * @since 6.9.3
	 */
	public function canUse($location = null, $event = null){
		return true;
	}


	/**
	 * Do the plugin action
	 *
	 * @param   array  &$data data
	 *
	 * @return  int  number of records updated
	 *
	 * @since 6.9.3
	 * @throws Exception
	 */
	public function process(&$data, &$listModel) {
		jimport('joomla.mail.helper');

		// LOGGER
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.emundusnantesscholargpush.info.php'], JLog::INFO, 'com_emundus.emundusnantesscholargpush');
		JLog::addLogger(['text_file' => 'com_emundus.emundusnantesscholargpush.error.php'], JLog::ERROR, 'com_emundus.emundusnantesscholargpush');

		$http = new JHttp();
		$db = JFactory::getDbo();

		$params = $this->getParams();
		$api_url = $params->get('api_url');
		$api_route = $params->get('api_route');
		$token = $params->get('token');
		$status = $params->get('status');
		$tag = $params->get('tag');
		$profile = $params->get('profile');

		$this->log = '';

		$select = [
			$db->quoteName('pd.email','adresseMailExterne'), //NOTE: Required
			'a.bac_year AS anneeBac',
			'YEAR(a.parcours_sup_annee) AS anneeEnsSupInscription1', //NOTE: Required
			'YEAR(a.univ_year) AS anneeInscription1', //NOTE: Required
			$db->quoteName('a.bac_serie','codBac'),
			$db->quoteName('pd.city_1','codCommuneAF'),
			$db->quoteName('cc.id','codDossier'), //NOTE: Required
			$db->quoteName('a.bac_department','codDptBac'),
			$db->quoteName('a.univ_depatment','codDptInscription1'), //NOTE: Required
			$db->quoteName('pd.birth_department','codDptNaissance'),
			'"EMUNDUS" AS codEtablissement', //NOTE: Required
			$db->quoteName('a.univ_establishment_select','codEtbInscription1'), //NOTE: Required
			$db->quoteName('a.bac_mention','codMention'),
			'"N" AS codMilitaire', //NOTE: Required
			$db->quoteName('c.scholarg_code','codNationalite'), //NOTE: Required
			$db->quoteName('c2.scholarg_code','codPaysAF'), //NOTE: Required
			$db->quoteName('c3.scholarg_code','codPaysNaissance'), //NOTE: Required
			'"99" AS codPcsParent1', //NOTE: Required
			$db->quoteName('pd.zipcode_1','codPostalAF'),
			$db->quoteName('a.bac_establishment_type','codTypEtablissement'), //NOTE: Required
			'"99" AS codpcsParent2', //NOTE: Required
			$db->quoteName('pd.street_2','complementAF'),
			$db->quoteName('pd.birth_date','datNaissance'), //NOTE: Required
			$db->quoteName('pd.birth_town','lieuNaissance'), //NOTE: Required
			$db->quoteName('fc.scolarg_label','localiteAF'),
			$db->quoteName('pd.nom_marital','nomUsuel'),
			$db->quoteName('a.parcours_sup_ine','numIne'),
			$db->quoteName('pd.telephone','numPortable'),
			$db->quoteName('pd.telephone','numTelephoneAF'),
			$db->quoteName('pd.street_1','numVoieAF'),
			$db->quoteName('pd.last_name','patronyme'), //NOTE: Required
			$db->quoteName('pd.first_name','prenom1'), //NOTE: Required
			$db->quoteName('pd.second_name','prenom2'),
			$db->quoteName('pd.gender','sexe'), //NOTE: Required
			$db->quoteName('fc.scolarg_label','villeAF'), //NOTE: Required
			$db->quoteName('pd.city_2'),
			$db->quoteName('pd.street_1','voieAF'), //NOTE: Required
			$db->quoteName('cc.fnum'),
            $db->quoteName('a.codSpecialite1'),
            $db->quoteName('a.codSpecialite2'),
		];

		// Get list of files to push
		$query = $db->getQuery(true);
		$query->select($select)
			->from($db->quoteName('jos_emundus_campaign_candidature','cc'))
			->leftJoin($db->quoteName('jos_emundus_personal_detail', 'pd').' ON '.$db->quoteName('cc.fnum').' = '.$db->quoteName('pd.fnum'))
			->leftJoin($db->quoteName('jos_emundus_setup_campaigns', 'sc').' ON '.$db->quoteName('cc.campaign_id').' = '.$db->quoteName('sc.id'))
			->leftJoin($db->quoteName('jos_emundus_academic', 'a').' ON '.$db->quoteName('cc.fnum').' = '.$db->quoteName('a.fnum'))
			->leftJoin($db->quoteName('jos_emundus_country', 'c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('pd.nationality'))
			->leftJoin($db->quoteName('jos_emundus_country', 'c2').' ON '.$db->quoteName('c2.id').' = '.$db->quoteName('pd.country_1'))
			->leftJoin($db->quoteName('jos_emundus_country', 'c3').' ON '.$db->quoteName('c3.id').' = '.$db->quoteName('pd.birth_country'))
			->leftJoin($db->quoteName('data_insee_ref', 'fc').' ON '.$db->quoteName('fc.code').' LIKE '.$db->quoteName('pd.city_1'))
			->where($db->quoteName('sc.profile_id').' = '.$db->quote($profile).' AND '.$db->quoteName('cc.status').' = '.$status.' AND '.$db->quoteName('cc.fnum'). ' NOT IN (SELECT fnum FROM jos_emundus_tag_assoc as eta WHERE id_tag = '.$tag.')');
		$db->setQuery($query);

		try {
			$files = $db->loadObjectList('fnum');
		} catch (Exception $e) {
			JLog::add('Error getting files to be exported : '.$e->getMessage(), JLog::ERROR, 'com_emundus.emundusnantesscholargpush');
			return false;
		}

		$valid_fnums = [];
		foreach ($files as $fnum => $file) {

			// remove fnum from object being sent to API.
			unset($file->fnum);


			$file->codDossier = 'EM'.$file->codDossier;

			// Empty values for non-required fields don't need to be sent.
			if (empty($file->codBac) || $file->codBac == '0000') {
				$file->codBac = "00";
			}
			if (empty($file->codDptBac)) {
				unset($file->codDptBac);
			}
			if (empty($file->codDptNaissance)) {
				unset($file->codDptNaissance);
			}

			// Bac is a required field that not all have filled out, in that case : insert the value for OTHER.
			if (empty($file->codTypEtablissement)) {
				$file->codTypEtablissement = 9;
			}

			$file->villeAF = str_replace('-', ' ', $file->villeAF);

			// Foreigners have their VilleAF in city_2 and not in the list of jos_emundus_french_cities
			if (empty($file->villeAF)) {
				$file->villeAF = $file->city_2;
			}
			unset($file->city_2);

			if (empty($file->villeAF)) {
				$query->clear()
					->select($db->quoteName(['scolarg_label', 'code']))
					->from($db->quoteName('data_insee_ref'))
					->where($db->quoteName('code_postal').' = '.$db->quote($file->codPostalAF));
				$db->setQuery($query);
				try {
					$city = $db->loadObject();

					$file->codCommuneAF = $city->code;
					$file->villeAF = $city->scolarg_label;

				} catch (Exception $e) {
					// villeAF will be empty, too bad.
				}
			}

			if (empty($file->villeAF)) {
				$file->villeAF = 'Ville Inconnue';
			}

			// codEtbInscription1 cannot be empty
			if (empty($file->codEtbInscription1) || $file->codEtbInscription1 == '-1') {
				$file->codEtbInscription1 = '00000000';
			}

			// codDptInscription1 cannot be empty
			if (empty($file->codDptInscription1)) {
				$file->codDptInscription1 = '00';
			}

			// codPaysNaissance cannot be empty
			if (empty($file->codPaysNaissance)) {
				$file->codPaysNaissance = '999';
			}
			if (empty($file->codNationalite)) {
				$file->codNationalite = '999';
			}
			if (empty($file->codPaysAF)) {
				$file->codPaysAF = '999';
			}
            if (empty($file->codSpecialite1)) {
                unset($file->codSpecialite1);
            }
            if (empty($file->codSpecialite2)) {
                unset($file->codSpecialite2);
            }
            
			// Telephone numbers need to be without spaces
			$file->numPortable = str_pad(trim(str_replace(' ', '', $file->numPortable), '_'), 10, "0", STR_PAD_LEFT);
			$file->numTelephoneAF = str_pad(trim(str_replace(' ', '', $file->numTelephoneAF), '_'), 10, "0", STR_PAD_LEFT);

			// Split address into street and number.
			preg_match('/^\d+/', $file->numVoieAF, $matches);
			$file->voieAF = trim(preg_replace('/^\d+/', '', $file->numVoieAF));
			$file->numVoieAF = $matches[0];

			foreach($file as &$prop) {
				$prop = trim($prop);
			}

			// Street numbers longer than 3 chars are not allowed...
			if (strlen($file->numVoieAF) > 3) {
				$file->voieAF = $file->numVoieAF.' '.$file->voieAF;
				unset($file->voieAF);
			}

			JLog::add('Processing file : '.$fnum.' POST json : '.json_encode($file), JLog::INFO, 'com_emundus.emundusnantesscholargpush');

			$response = $http->post($api_url.$api_route, json_encode($file), ['X-Auth-Token' => $token, 'Content-Type' => 'application/json']);

			if ($response->code === 200) {
				$response->body = json_decode($response->body);

				// If we get a litteral OK from the API, add the tag to our file.
				if ($response->body->retour === "OK" || $response->body->retour === "POK") {

					if ($response->body->retour === "POK") {
						JLog::add('POK for file : '.json_encode($file), JLog::ERROR, 'com_emundus.emundusnantesscholargpush');
						JLog::add('POK RESPONSE : '.json_encode($response->body), JLog::ERROR, 'com_emundus.emundusnantesscholargpush');
					}

					JLog::add('POST ok for file ID : '.$file->codDossier, JLog::INFO, 'com_emundus.emundusnantesscholargpush');

					if (!empty($response->body->operationEffectuee->codeEtudiant)) {

						$query->clear()
							->update($db->quoteName('jos_emundus_academic'))
							->set($db->quoteName('num_etudiant').' = '.$db->quote($response->body->operationEffectuee->codeEtudiant))
							->set($db->quoteName('etudiant_nantes').' = '.$db->quote('oui'))
							->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
						$db->setQuery($query);

						try {
							$db->execute();
						} catch (Exception $e) {
							JLog::add('Error updating student ID number : '.$e->getMessage(), JLog::ERROR, 'com_emundus.emundusnantesscholargpush');
						}

					}

					$valid_fnums[] = $fnum;
				}

			} else {
				JLog::add('Error ('.$response->code.') from client when pushing file ID '.$file->codDossier.' (fnum: '.$fnum.'). API response : '.$response->body, JLog::ERROR, 'com_emundus.emundusnantesscholargpush');
			}
		}

		// Do the insert after so we can do one big insert of tags.
		if (!empty($valid_fnums)) {

			$query->clear()
				->insert($db->quoteName('jos_emundus_tag_assoc'))
				->columns($db->quoteName(['id_tag', 'fnum']));

			foreach ($valid_fnums as $fnum) {
				$query->values($db->quote($tag).', '.$db->quote($fnum));
			}

			$db->setQuery($query);

			try {
				$db->execute();
			} catch (Exception $e) {
				JLog::add('Error setting file : '.$e->getMessage(), JLog::ERROR, 'com_emundus.emundusnantesscholargpush');
			}
		}

		$this->log .= "\n process " . count($files) . " user(s)";
		return count($files);
	}
}
