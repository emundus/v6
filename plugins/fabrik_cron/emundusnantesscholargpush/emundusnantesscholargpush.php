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
			$db->quoteName('pd.email','adresseMailExterne'),
			$db->quoteName('a.bac_year', 'anneeBac'),
			$db->quoteName('a.parcours_sup_annee','anneeEnsSupInscription1'),
			$db->quoteName('a.univ_year','anneeInscription1'),
			$db->quoteName('a.bac_serie','codBac'),
			$db->quoteName('pd.city_1','codCommuneAF'),
			$db->quoteName('cc.id','codDossier'),
			$db->quoteName('a.bac_department','codDptBac'),
			$db->quoteName('a.univ_depatment','codDptInscription1'),
			$db->quoteName('pd.birth_department','codDptNaissance'),
			'"EMUNDUS" AS codEtablissement',
			$db->quoteName('a.univ_establishment','codEtbInscription1'),
			'"" AS codEtbLycee',
			'"1" AS codFamiliale',
			$db->quoteName('a.bac_mention','codMention'),
			'"N" AS codMilitaire',
			$db->quoteName('c.scholarg_code','codNationalite'),
			$db->quoteName('c2.scholarg_code','codPaysAF'),
			$db->quoteName('c3.scholarg_code','codPaysNaissance'),
			'"99" AS codPcsParent1',
			$db->quoteName('pd.zipcode_1','codPostalAF'),
			$db->quoteName('a.bac_establishment_type','codTypEtablissement'),
			'"" AS codcommunenaissance',
			'"99" AS codpcsParent2',
			$db->quoteName('pd.street_2','complementAF'),
			$db->quoteName('pd.city_2','cpEtrangerAF'),
			$db->quoteName('pd.birth_date','datNaissance'),
			$db->quoteName('pd.birth_town','lieuNaissance'),
			$db->quoteName('fc.name','localiteAF'),
			'"0" AS nbrEnfants',
			$db->quoteName('pd.last_name','nomUsuel'),
			$db->quoteName('a.parcours_sup_ine','numIne'),
			$db->quoteName('pd.telephone','numPortable'),
			$db->quoteName('pd.telephone','numTelephoneAF'),
			$db->quoteName('pd.street_1','numVoieAF'),
			$db->quoteName('pd.nom_marital','patronyme'),
			$db->quoteName('pd.first_name','prenom1'),
			$db->quoteName('pd.second_name','prenom2'),
			$db->quoteName('pd.gender','gender'),
			$db->quoteName('fc.name','villeAF'),
			$db->quoteName('pd.street_1','voieAF'),
			$db->quoteName('cc.fnum')
		];

		// TODO : codNationalite needs to look at a different table, not the eMundus one, fuze this.
		// TODO : localieAF, where does it come from ?
		// TODO : Check villeAF

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
	        ->leftJoin($db->quoteName('jos_emundus_french_cities', 'fc').' ON '.$db->quoteName('fc.insee_code').' LIKE '.$db->quoteName('pd.city_1'))
	        ->where($db->quoteName('sc.profile_id').' = '.$db->quote($profile).' AND '.$db->quoteName('cc.status').' = '.$status.' AND '.$db->quoteName('cc.fnum'). ' NOT IN (SELECT fnum FROM jos_emundus_tag_assoc as eta WHERE id_tag = '.$tag.')');
        $db->setQuery($query);

        try {
        	$files = $db->loadObjectList('fnum');
        } catch (Exception $e) {
        	echo '<pre>'; var_dump($e->getMessage()); echo '</pre>'; die;
        	JLog::add('Error getting files to be exported : '.$e->getMessage(), JLog::ERROR, 'com_emundus.emundusnantesscholargpush');
        	return false;
        }


		foreach ($files as $fnum => $file) {

			// remove fnum from object being sent to API.
			unset($file->fnum);

			if (empty($file->codDptBac)) {
				$file->codBac = "";
			}
			if (empty($file->codDptBac)) {
				$file->codDptBac = "";
			}
			if (empty($file->codDptNaissance)) {
				$file->codDptNaissance = "";
			}
			if (empty($file->codTypEtablissement)) {
				$file->codTypEtablissement = "";
			}

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
			}
		}

		$this->log .= "\n process " . count($files) . " user(s)";
		return count($files);
	}
}
