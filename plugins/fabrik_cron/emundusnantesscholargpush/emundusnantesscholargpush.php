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

		$this->log = '';


		$select = [
			'pd.email AS adresseMailExterne',
			'a.bac_year AS anneeBac',
			'a.parcours_sup_annee AS anneeEnsSupInscription1',
			'a.univ_year AS anneeInscription1',
			'a.bac_serie AS codBac',
			'pd.city_1 AS codCommuneAF',
			'cc.id AS codDossier',
			'a.bac_department AS codDptBac',
			'a.univ_depatment AS codDptInscription1',
			'pd.birth_department AS codDptNaissance',
			'"EMUNDUS" AS codEtablissement',
			'a.univ_establishment AS codEtbInscription1',
			'"" AS codEtbLycee',
			'"1" AS codFamiliale',
			'a.bac_mention AS codMention',
			'"N" AS codMilitaire',
			'pd.nationality AS codNationalite',
			'pd.country_1 AS codPaysAF',
			'pd.birth_country AS codPaysNaissance',
			'"99" AS codPcsParent1',
			'pd.zipcode_1 AS codPostalAF',
			'a.bac_establishment_type AS codTypEtablissement',
			'pd.birth_town AS codcommunenaissance',
			'"99" AS codpcsParent2',
			'pd.street_2 AS complementAF',
			'pd.city_2 AS cpEtrangerAF',
			'pd.birth_date AS datNaissance',
			'pd.birth_town AS lieuNaissance',
			'pd.city_1 AS localiteAF',
			'"0" AS nbrEnfants',
			'pd.last_name AS nomUsuel',
			'a.parcours_sup_ine AS numIne',
			'pd.telephone AS numPortable',
			'pd.telephone AS numTelephoneAF',
			'pd.street_1 AS numVoieAF',
			'pd.nom_marital AS patronyme',
			'pd.first_name AS prenom1',
			'pd.second_name AS prenom2',
			'pd.gender AS gender',
			'pd.city_1 AS villeAF',
			'pd.street_1 AS voieAF',
			'cc.fnum'
		];

		// TODO : codNationalite needs to look at a different table, not the eMundus one, fuze this.
		// TODO : codecommunenaissance ugh...
		// TODO : localieAF, where does it come from ?
		// TODO : Check villeAF

        // Get list of files to push
        $query = $db->getQuery(true);
        $query->select($db->quoteName($select))
	        ->from($db->quoteName('jos_emundus_campaign_candidature','cc'))
			->leftJoin($db->quoteName('jos_emundus_personal_detail', 'pd').' ON '.$db->quoteName('cc.fnum').' = '.$db->quoteName('pd.fnum'))
	        ->leftJoin($db->quoteName('jos_emundus_academic', 'a').' ON '.$db->quoteName('cc.fnum').' = '.$db->quoteName('a.fnum'))
	        ->where($db->quoteName('cc.status').' = '.$status.' AND '.$db->quoteName('cc.fnum'). ' NOT IN (SELECT fnum FROM jos_emundus_tag_assoc as eta WHERE id_tag = '.$tag.')');
        $db->setQuery($query);

        try {
        	$files = $db->loadObjectList('fnum');
        } catch (Exception $e) {
        	JLog::add('Error getting files to be exported : '.$e->getMessage(), JLog::ERROR, 'com_emundus.emundusnantesscholargpush');
        	return false;
        }


		foreach ($files as $fnum => $file) {

			// remove fnum from object being sent to API.
			unset($file->fnum);

			$response = $http->post($api_url.$api_route, json_encode($file), ['X-Auth-Token' => $token, 'Content-Type' => 'application/json']);

			if ($response->code === 200) {
				$response->body = json_decode($response->body);

				// If we get a litteral OK from the API, add the tag to our file.
				if ($response->body->retour === "OK") {

					// TODO: Refractor to be one big inset for all files instead of multiple.
					$query->clear()
						->insert($db->quoteName('jos_emundus_tag_assoc'))
						->columns($db->quoteName(['id_tag', 'fnum']))
						->values($db->quoteName([$tag, $fnum]));
					$db->setQuery($query);

					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Error getting files to be exported : '.$e->getMessage(), JLog::ERROR, 'com_emundus.emundusnantesscholargpush');
						return false;
					}
				}

			} else {
				JLog::add('Error ('.$response->code.') from client API : '.$response->body);
				continue;
			}
		}

		$this->log .= "\n process " . count($files) . " user(s)";
		return count($files);
	}
}
