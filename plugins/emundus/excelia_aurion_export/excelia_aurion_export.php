<?php
/**
 * @package       eMundus
 * @version       6.6.5
 * @author        eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license       GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusExcelia_aurion_export extends JPlugin
{

	var $db;

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->db = JFactory::getDbo();

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.exceliaAurionExport.php'), JLog::ALL, array('com_emundus_exceliaAurionExport'));
	}

	/**
	 * Export all fnums in params to Exceila Aurion
	 * Method is called on the eMundus Export plugin if the Aurion param is set
	 *
	 * @param   array  $fnums
	 *
	 * @return bool
	 *
	 * @since version
	 */
	function onExportFiles($fnums, $type)
	{

		// No need to go further if we are'nt looking for this plugin or have no fnums selected
		if ($type !== 'excelia_aurion' || empty($fnums)) {
			return false;
		}
		else {
			// Get Aurion params to export
			$aurion_url   = $this->params->get('url', null);
			$aurion_login = $this->params->get('login', null);
			$aurion_pass  = $this->params->get('password', null);
			$status       = $this->params->get('status', null);

			// if any one of these params are empty, go no further
			if (empty($aurion_url) || empty($aurion_login) || empty($aurion_pass)) {
				JLog::add('Could not run plugin, missing param', JLog::ERROR, 'com_emundus_exceliaAurionExport');

				return false;
			}

			// Build the query Select
			// emundus setup campaign table
			$campaign_columns = [
				'esc.aurion_id'
			];

			// emundus user table
			$eu_columns = [
				$this->db->quoteName('eu.user_id'),
				$this->db->quoteName('eu.firstname'),
				$this->db->quoteName('eu.lastname'),
				$this->db->quoteName('eu.email'),
				$this->db->quoteName('eu.tel'),
				$this->db->quoteName('eu.country'),
				$this->db->quoteName('eu.nationality')
			];

			// emundus personal details table
			$pd_columns = [
				$this->db->quoteName('epd.email', 'pd_email'),
				$this->db->quoteName('epd.civility'),
				$this->db->quoteName('epd.mobile_1'),
				$this->db->quoteName('epd.skype_id'),
				$this->db->quoteName('epd.street_1'),
				$this->db->quoteName('epd.street_2'),
				$this->db->quoteName('epd.street_3'),
				$this->db->quoteName('epd.city_1'),
				$this->db->quoteName('epd.city_2', 'epd_city2'),
				$this->db->quoteName('epd.birth_date'),
				$this->db->quoteName('epd.country_1')
			];

			// emundus qualification table
			$qualification_columns = [
				$this->db->quoteName('eq.fnum', 'eq_fnum'),
				$this->db->quoteName('eq.first_language'),
				$this->db->quoteName('eq.university'),
				$this->db->quoteName('eq.state'),
				$this->db->quoteName('eq.city'),
				$this->db->quoteName('eq.city_2'),
				$this->db->quoteName('eq.lv1'),
				$this->db->quoteName('eq.lv2'),
				$this->db->quoteName('eq.level'),
				$this->db->quoteName('eq.type'),
				$this->db->quoteName('eq.country', 'eq_country'),
				$this->db->quoteName('eq.etab_sup'),
				$this->db->quoteName('eq.lycee'),
				$this->db->quoteName('eq.type_diplome')
			];

			// emundus scholarship table
			$scholarship_columns = [
				$this->db->quoteName('es.fnum', 'es_fnum'),
				$this->db->quoteName('es.mail_excelia'),
				$this->db->quoteName('es.is_hadicap'),
				$this->db->quoteName('es.formation'),
				$this->db->quoteName('win_sum_repeat.parent_id', 'win_parent_id')
			];

			$spe_columns = [
				$this->db->quoteName('spe_int_alt.id_Module_Specialisation', 'spe_int_alt'),
				$this->db->quoteName('spe_int_cla.id_Module_Specialisation', 'spe_int_cla'),
				$this->db->quoteName('spe_fr_cla.id_Module', 'spe_fr_cla'),
				$this->db->quoteName('spe_fr_alt.id_Module', 'spe_fr_alt')
			];

			$rentrees = [
				$this->db->quoteName('rentree.id_Rentree', 'entrance')
			];

			// emundus session concours table
			$concours_columns = [
				$this->db->quoteName('econ.fnum', 'econ_fnum'),
				$this->db->quoteName('econ.concours_session')
			];


			// Aurion Data
			// data_aurion_37736495 / Internal users
			$aurion_user = [
				$this->db->quoteName('dau.id_Individu'),
				$this->db->quoteName('dau.ID_Type_Convention')
			];

			// data_aurion_39177663 / other users
			$aurion_em_user = [
				$this->db->quoteName('deu.id_Individu', 'aurion_user'),
				$this->db->quoteName('deu.emundus_id')
			];

			// data_aurion_35347585
			$aurion_civility = [
				$this->db->quoteName('dac.Code_Titre'),
				$this->db->quoteName('dac.Sexe')
			];

			// data_aurion_35616031
			$aurion_diplome = [
				$this->db->quoteName('dad.id_TypeDiplome')
			];

			// data_aurion_35581810
			$aurion_nationality = [
				$this->db->quoteName('dan.Libelle', 'aurion_nationality'),
				$this->db->quoteName('dan.id_TypeDeConvention')
			];

			// data_aurion_35584331
			$aurion_city = [
				$this->db->quoteName('dacity.id_Ville', 'aurion_city')
			];

			// data_aurion_37241402
			$aurion_concours_1 = [
				$this->db->quoteName('dacon.id_Module', 'concours_mod'),
				$this->db->quoteName('dacon.id_Concours', 'concours_session')
			];

			// data_aurion_39124065
			$aurion_concours_2 = [
				$this->db->quoteName('dacon2.id_Module', 'concours_mod2'),
				$this->db->quoteName('dacon2.id_Concours', 'concours_session2')
			];

			// data_aurion_39124065
			$aurion_concours_Easy = [
				$this->db->quoteName('daconEasy.id_Module', 'concours_easy_mod'),
				$this->db->quoteName('daconEasy.id_Concours', 'concours_easy_session')
			];

			// data_aurion_56141251
			$coord_concours = [
				$this->db->quoteName('coor_date.id_Module', 'concours_coord_mod'),
				$this->db->quoteName('coor_date.id_Concours', 'concours_coord_session')
			];

			// data_aurion_47672525
			$aurion_lycee = [
				$this->db->quoteName('lycee.id_Lycee')
			];
			// data_aurion_47993857
			$aurion_etab = [
				$this->db->quoteName('etab.id_EtablissementSuperieur', 'id_etab')
			];
			// data_aurion_47974046
			$aurion_type_dip = [
				$this->db->quoteName('type_dip.id_TypediplomeEmundus', 'type_diplome')
			];

			$query = $this->db->getQuery(true);
			// In the query, we merge all the different tables in the select and join them while checking if the rows we get in the aurion tables are published
			$query
				->select(array_merge($campaign_columns, $eu_columns, $pd_columns, $qualification_columns, $scholarship_columns, $concours_columns, $coord_concours, $aurion_user, $aurion_em_user, $aurion_civility, $aurion_diplome, $aurion_nationality, $aurion_concours_1, $aurion_concours_2, $aurion_city, $spe_columns, $rentrees, $aurion_lycee, $aurion_etab, $aurion_type_dip, $aurion_concours_Easy))
				->from($this->db->quoteName('#__emundus_campaign_candidature', 'ecc'))
				->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->db->quoteName('ecc.campaign_id') . ' = ' . $this->db->quoteName('esc.id'))
				->leftJoin($this->db->quoteName('#__emundus_users', 'eu') . ' ON ' . $this->db->quoteName('ecc.applicant_id') . ' = ' . $this->db->quoteName('eu.user_id'))
				->leftJoin($this->db->quoteName('#__emundus_personal_detail', 'epd') . ' ON ' . $this->db->quoteName('ecc.fnum') . ' = ' . $this->db->quoteName('epd.fnum'))
				->leftJoin($this->db->quoteName('#__emundus_qualifications', 'eq') . ' ON ' . $this->db->quoteName('ecc.fnum') . ' = ' . $this->db->quoteName('eq.fnum'))
				->leftJoin($this->db->quoteName('#__emundus_scholarship', 'es') . ' ON ' . $this->db->quoteName('ecc.fnum') . ' = ' . $this->db->quoteName('es.fnum'))
				->leftJoin($this->db->quoteName('#__emundus_concours_sessions', 'econ') . ' ON ' . $this->db->quoteName('ecc.fnum') . ' = ' . $this->db->quoteName('econ.fnum'))
				->leftJoin($this->db->quoteName('data_aurion_37736495', 'dau') . ' ON ' . $this->db->quoteName('es.mail_excelia') . ' = ' . $this->db->quoteName('dau.MailEcole') . ' AND ' . $this->db->quoteName('dau.published') . ' = 1')
				->leftJoin($this->db->quoteName('data_aurion_39177663', 'deu') . ' ON ' . $this->db->quoteName('ecc.applicant_id') . ' = ' . $this->db->quoteName('deu.emundus_id') . ' AND ' . $this->db->quoteName('deu.published') . ' = 1')
				->leftJoin($this->db->quoteName('data_aurion_35347585', 'dac') . ' ON ' . $this->db->quoteName('epd.civility') . ' = ' . $this->db->quoteName('dac.id_Titre') . ' AND ' . $this->db->quoteName('dac.published') . ' = 1')
				->leftJoin($this->db->quoteName('data_aurion_35584331', 'dacity') . ' ON ' . $this->db->quoteName('eq.city') . ' = ' . $this->db->quoteName('dacity.id_Ville') . ' AND ' . $this->db->quoteName('dacity.published') . ' = 1')
				->leftJoin($this->db->quoteName('data_aurion_35616031', 'dad') . ' ON ' . $this->db->quoteName('eu.candidat') . ' = ' . $this->db->quoteName('dad.Code_TypeDiplome') . ' AND ' . $this->db->quoteName('dad.published') . ' = 1')
				->leftJoin($this->db->quoteName('data_aurion_35581810', 'dan') . ' ON ' . $this->db->quoteName('eu.nationality') . ' = ' . $this->db->quoteName('dan.id_Nationalite') . ' AND ' . $this->db->quoteName('dan.published') . ' = 1')
				// Get spe ids
				->leftJoin($this->db->quoteName('data_aurion_37203235', 'spe_fr_cla') . ' ON ' . $this->db->quoteName('es.spe_fr_cla') . ' = ' . $this->db->quoteName('spe_fr_cla.id'))
				->leftJoin($this->db->quoteName('data_aurion_35616195', 'spe_fr_alt') . ' ON ' . $this->db->quoteName('es.spe_fr_alt') . ' = ' . $this->db->quoteName('spe_fr_alt.id'))
				->leftJoin($this->db->quoteName('data_aurion_37203247', 'spe_int_cla') . ' ON ' . $this->db->quoteName('es.spe_int_cla') . ' = ' . $this->db->quoteName('spe_int_cla.id'))
				->leftJoin($this->db->quoteName('data_aurion_37130437', 'spe_int_alt') . ' ON ' . $this->db->quoteName('es.spe_int_alt') . ' = ' . $this->db->quoteName('spe_int_alt.id'))
				->leftJoin($this->db->quoteName('jos_emundus_scholarship_repeat_spe_win_sum', 'win_sum_repeat') . ' ON ' . $this->db->quoteName('es.id') . ' = ' . $this->db->quoteName('win_sum_repeat.parent_id'))

				// Get Rentrees
				->leftJoin($this->db->quoteName('data_aurion_35584301', 'rentree') . ' ON '
					. $this->db->quoteName('es.rentree_fr_cla') . ' = ' . $this->db->quoteName('rentree.id')
					. ' OR ' . $this->db->quoteName('es.rentree_fr_cla') . ' = ' . $this->db->quoteName('rentree.id')
					. ' OR ' . $this->db->quoteName('es.rentree_fr_alt') . ' = ' . $this->db->quoteName('rentree.id')
					. ' OR ' . $this->db->quoteName('es.rentree_int_cla') . ' = ' . $this->db->quoteName('rentree.id')
					. ' OR ' . $this->db->quoteName('es.rentree_int_alt') . ' = ' . $this->db->quoteName('rentree.id')
				)
				// Get concours id
				->leftJoin($this->db->quoteName('data_aurion_37241402', 'dacon') . ' ON ' . $this->db->quoteName('econ.concours_session') . ' = ' . $this->db->quoteName('dacon.id'))
				->leftJoin($this->db->quoteName('data_aurion_39124065', 'dacon2') . ' ON ' . $this->db->quoteName('econ.concours_session_attente') . ' = ' . $this->db->quoteName('dacon2.id'))
				->leftJoin($this->db->quoteName('data_aurion_45601790', 'daconEasy') . ' ON ' . $this->db->quoteName('econ.concours_session_easy') . ' = ' . $this->db->quoteName('daconEasy.id'))
				->leftJoin($this->db->quoteName('data_aurion_56141251', 'coor_date') . ' ON ' . $this->db->quoteName('econ.gestionnaire_date') . ' = ' . $this->db->quoteName('coor_date.id'))
				// Get Lycee and etablissement Sup
				->leftJoin($this->db->quoteName('data_aurion_47672525', 'lycee') . ' ON ' . $this->db->quoteName('eq.lycee') . ' = ' . $this->db->quoteName('lycee.id'))
				->leftJoin($this->db->quoteName('data_aurion_47993857', 'etab') . ' ON ' . $this->db->quoteName('eq.etab_sup') . ' = ' . $this->db->quoteName('etab.id'))
				->leftJoin($this->db->quoteName('data_aurion_47974046', 'type_dip') . ' ON ' . $this->db->quoteName('eq.type_diplome') . ' = ' . $this->db->quoteName('type_dip.id'))
				->where($this->db->quoteName('ecc.fnum') . ' IN (' . implode(', ', $this->db->quote($fnums)) . ')');

			try {
				$this->db->setQuery($query);
				// build the User object
				$users = $this->db->loadObjectList('user_id');

			}
			catch (Exception $e) {
				JLog::add('Could not get applicant info. -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus_exceliaAurionExport');

				return false;
			}

			if (empty($users)) {
				JLog::add('No users found in the aurion export query', JLog::ERROR, 'com_emundus_exceliaAurionExport');

				return false;
			}

			// Now we are going to check if the user
			foreach ($users as $user) {

				// Set the user's spe and entrance value by getting the unique value from the 5 different possibilities
				$user->speciality = array_values(array_filter([$user->spe_int_alt, $user->spe_int_cla, $user->spe_fr_alt, $user->spe_fr_cla, $user->spe_win_sum]))[0];

				// Build the xml file depending if the user exists in Aurion
				if (empty($user->id_Individu) && empty($user->aurion_user)) {
					$xml_export = $this->buildNewUserXml($user);
				}
				else {
					$xml_export = $this->buildExistingUserXml($user);
				}

				if (empty($xml_export)) {
					JLog::add('error while building the xml file', JLog::ERROR, 'com_emundus_exceliaAurionExport');

					return false;
				}

				JLog::add($xml_export, JLog::INFO, 'com_emundus_exceliaAurionExport');
				// build the body with the plugin params and the xml
				$request_body = [
					'login'    => $aurion_login,
					'password' => $aurion_pass,
					'data'     => $xml_export
				];

				// Initialize a cURL session
				$ch = curl_init();
				//Set options for the cURL transfer
				// IMPORTANT: set SSL VerifyPeer to false (curl -k)
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				//set url
				curl_setopt($ch, CURLOPT_URL, $aurion_url);
				// Needs to be a POST as we are sending data
				curl_setopt($ch, CURLOPT_POST, 1);
				// Get the request body and build http query with that array
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_body));
				// Set header
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
				// So we can get the xml response after the exec
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				$info = curl_getinfo($ch);

				// Execute transfer
				$res = curl_exec($ch);

				// Vérification si une erreur est survenue
				if (curl_errno($ch)) {
					JLog::add('Error posting data in Curl' . $info, JLog::ERROR, 'com_emundus_exceliaAurionExport');

					return false;
				}

				// The API almost always responds with a 200OK, however certain errors are in HTML
				$data      = simplexml_load_string($res);
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

				if ($http_code === 200) {

					if ($data->execute == 'false') {
						JLog::add('
						Error parsing XML: this could be an error in the request \n 
						URL: ' . $aurion_url . ' 
						POST DATA: ' . $xml_export . ' 
						RESPONSE BODY: ' . $data->messages[0]->message . '
					', JLog::ERROR, 'com_emundus_exceliaAurionExport');

						return false;
					}

					if ($status) {
						require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
						$m_files = new EmundusModelFiles();
						$m_files->updateState($fnums, $status);
					}

					JLog::add('
						XML SENT : \n
						POST DATA: ' . $xml_export . ' \n
					', JLog::INFO, 'com_emundus_exceliaAurionExport');

				}
				else {
					JLog::add('
						HTTP ERROR: Response not 200 OK \n 
						URL: ' . $aurion_url . ' \n
						POST DATA: ' . $xml_export . ' \n
						RESPONSE CODE: ' . $data->execute[0] . ' \n
						RESPONSE BODY: ' . $data->messages[0]->message . '
					', JLog::ERROR, 'com_emundus_exceliaAurionExport');

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Build XML file for a user that doesn't exist in Aurion
	 *
	 * @param   Object  $user
	 *
	 * @return bool
	 *
	 * @since version
	 */
	function buildNewUserXml($user)
	{

		// $user_key = LASTNAME_FIRSTNAME_SEX_BIRTHDATE
		$user_key = strtoupper($this->replaceUserName($user->lastname)) . "_" . strtoupper($this->replaceUserName($user->firstname)) . "_" . $user->Sexe . "_" . date('dmY', strtotime($user->birth_date));
		// Check if the candidat is applying for the Winter/Summer school
		$winter_spes = $this->winterSummerSpes($user->win_parent_id);

		//Only import skype_id if there is one
		if (!empty($user->skype_id)) {
			$skype = "<coordonnee key='SKYPE_" . $user_key . "' libelle='" . htmlspecialchars($user->skype_id, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "'>
                            <type_coordonnee objet_id='86334' OnRelation='true' ForceImport='true' ForceReplace='true' />
                      </coordonnee>";
		}
		else {
			$skype = "";
		}

		//Only import User address if there is one
		if (!empty($user->street_1)) {
			$address = "<adresse key='ADR_PERSO_" . $user_key . "' A500='" . htmlspecialchars($user->street_1, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "' A501='" . htmlspecialchars($user->street_2, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "' A502='" . htmlspecialchars($user->street_3, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "'>
                        <ville objet_id='" . $user->city_1 . "' ForceImport='true' />
                        <pays objet_id='" . $user->country_1 . "' ForceImport='true' />
                        <type_adresse objet_id='44755' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </adresse>
                    <adresse key='ADR_FACTURATION_" . $user_key . "' A500='" . htmlspecialchars($user->street_1, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "' A501='" . htmlspecialchars($user->street_2, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "' A502='" . htmlspecialchars($user->street_3, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "'>
                        <ville objet_id='" . $user->city_1 . "' ForceImport='true' />
                        <pays objet_id='" . $user->country_1 . "' ForceImport='true' />
                        <type_adresse objet_id='5586412' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </adresse>
                    ";
		}
		else {
			$address = "";
		}


		// Build the user's personal details section
		$individu = "
                <individu key='" . $user_key . "' code='" . strtoupper($this->replaceUserNameWithSpace($user->lastname)) . "' libelle='" . htmlspecialchars(ucfirst($user->firstname), ENT_XML1 | ENT_QUOTES, 'UTF-8') . "' A595='" . date('d-m-Y', strtotime($user->birth_date)) . "' A596='" . $user->Sexe . "' A39153560='" . htmlspecialchars($user->first_language, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "' A39218849='" . $user->user_id . "' A601='true'>
                    
                    <titre objet_id='" . $user->civility . "' ForceImport='true' ForceReplace='true' />
                    <nationalite objet_id='" . $user->nationality . "' ForceImport='true' />
                    
                    <coordonnee key='EMAIL_PERSO_" . $user_key . "' libelle='" . (!empty($user->pd_email) ? $user->pd_email : $user->email) . "'>
                        <type_coordonnee objet_id='44754' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </coordonnee>
                    
                    <coordonnee key='TEL_PORT_" . $user_key . "' libelle='" . (!empty($user->mobile_1) ? $user->mobile_1 : $user->tel) . "'>
                        <type_coordonnee objet_id='86166' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </coordonnee>
                    
                    " . $skype . "
                    
                    " . $address . "
                    
                </individu>";

		// Check if the the user has filled out their qualification form AND their scholarship form, don't import if one of them doesn't have a fnum
		if (!empty($user->es_fnum) && !empty($user->eq_fnum)) {
			if (!empty($user->city)) {
				$qualification_city = htmlspecialchars($this->getOutputLabelFromInput($user->aurion_city, 'id_Ville', 'Nom', 'data_aurion_35584331'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
			}
			else {
				$qualification_city = htmlspecialchars($user->city_2, ENT_XML1 | ENT_QUOTES, 'UTF-8');
			}

			$eq_lycee = !empty($user->lycee) ? "<lycee.client ForceDest='lycee.client§47996272' objet_id='" . $user->id_Lycee . "' ForceImport='true'/>" : "";

			$eq_etablissment = !empty($user->etab_sup) ? "<etablissement_superieur.client objet_id='" . $user->id_etab . "' ForceDest='etablissement_superieur.client§47997720' ForceImport='true'/>" : "";

			$other_etabs = !empty($eq_lycee) ? "A47997723='" . htmlspecialchars($user->university, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "'" : "A37765483='" . htmlspecialchars($user->university, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "'";

			$type_diplome = !empty($user->type_diplome) ? "<typediplome_emundus.client objet_id='" . $user->type_diplome . "' ForceDest='typediplome_emundus.client§48130556' ForceImport='true'/>" : '';
			$eq_country   = !empty($user->eq_country) ? "<pays objet_id='" . $user->eq_country . "' ForceDest='pays§37765785' ForceImport='true'/>" : "";

			$cours = "<cours ForceDest='cours§99785' objet_id='" . $user->speciality . "' ForceImport='true' />";

			// Check if the candidat is applying for the Winter/Summer school
			if (!empty($winter_spes)) {
				$cours = "";
				$cours .= "<cours ForceDest='cours§99785' objet_id='" . $winter_spes[0]->speciality . "' ForceImport='true' />";
			}

			$inscription_module = "
                <inscription_module ForceImport='true' key='" . $user->aurion_id . "_" . $user_key . "'  A3310='" . date('d-m-Y') . "' A87232='true' A46372499='" . $user->is_hadicap . "' " . $other_etabs . "  A37765709='" . $user->state . "' A37765733='" . $qualification_city . "' >
                    
                    <individu  key='" . $user_key . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <module objet_id='" . $user->aurion_id . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <module objet_id='" . $user->aurion_id . "' ForceImport='true'/>
                    
                    <langue objet_id='" . $user->lv1 . "' ForceDest='langue§4649742' ForceImport='true'/>
                    
                    <langue objet_id='" . $user->lv2 . "' ForceDest='langue§4649756' ForceImport='true'/>
                    
                    <niveau_formation objet_id='" . $user->level . "' ForceDest='niveau_formation§37764238' ForceImport='true'/>
                    
                    " . $eq_lycee . "
                    " . $eq_etablissment . "
                    " . $type_diplome . "
                    " . $eq_country . "
      
                    <typediplome_fr_int_.client ForceDest='typediplome_fr_int_.client§101204' objet_id='" . $user->id_TypeDiplome . "' ForceImport='true' />
                    
                    <rentree.client ForceDest='rentree.client§2954426' objet_id='" . ((!empty($winter_spes[0])) ? $winter_spes[0]->win_rentree : $user->entrance) . "' ForceImport='true' />
                   
                    " . $cours . "
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : ($user->formation == 1 ? $this->getAlternance($user->es_fnum) : 103503)) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . ($user->ID_Type_Convention ?: $user->id_TypeDeConvention) . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46311' ForceImport='true' />
                    
                </inscription_module>";
		}
		else {
			$inscription_module = "";
		}

		// Check if the the user has filled out their scholarship form, don't import if no fnum
		if (!empty($user->es_fnum)) {

			if (!empty($winter_spes)) {
				$inscription_cours = "";
				foreach ($winter_spes as $spe) {
					$inscription_cours .= "
                        <inscription_cours ForceImport='true' key='" . $spe->speciality . "_" . $user_key . "'  A2244='" . date('d-m-Y') . "' >
                            
                            <individu key='" . $user_key . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                                <cours objet_id='" . $spe->speciality . "' ForceSource='apprenant'/>
                            </individu>
                            
                            <cours objet_id='" . $spe->speciality . "' ForceImport='true'/>
                            
                            <type_apprenant objet_id='" . (empty($user->formation) ? '' : ($user->formation == 1 ? $this->getAlternance($user->es_fnum) : 103503)) . "' ForceImport='true' />
                            
                            <type_convention objet_id='" . ($user->ID_Type_Convention ?: $user->id_TypeDeConvention) . "' ForceImport='true' />
                            
                            <statut_inscription objet_id='46311' ForceImport='true' />
                        
                        </inscription_cours>";
				}

			}
			else {
				$inscription_cours = "
                <inscription_cours ForceImport='true' key='" . $user->speciality . "_" . $user_key . "'  A2244='" . date('d-m-Y') . "' >
                        
                    <individu  key='" . $user_key . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='" . $user->speciality . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <cours objet_id='" . $user->speciality . "' ForceImport='true'/>
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : ($user->formation == 1 ? $this->getAlternance($user->es_fnum) : 103503)) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . ($user->ID_Type_Convention ?: $user->id_TypeDeConvention) . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46311' ForceImport='true' />
                    
                </inscription_cours>";
			}
		}
		else {
			$inscription_cours = "";
		}

		// Check if the the user has filled out their session_concours form, don't import if no fnum
		// if the concours_mod is empty, we got and check in the alternative table
		if (!empty($user->econ_fnum)) {

			// Filter the modules and sessions to get the one selected
			$concours_mod     = array_values(array_filter([$user->concours_coord_mod, $user->concours_easy_mod, $user->concours_mod, $user->concours_mod2], function ($n) {
				return !empty($n);
			}));
			$concours_session = array_values(array_filter([$user->concours_coord_session, $user->concours_easy_session, $user->concours_session, $user->concours_session2], function ($n) {
				return !empty($n);
			}));

			$inscription_concours = "
                <inscription_concours ForceImport='true' key='" . (!empty($user->concours_easy_session) ? $user->concours_easy_session : (!empty($user->concours_session) ? $user->concours_session : $user->concours_session2)) . "_" . $user_key . "'  A4620='" . date('d-m-Y') . "' >
                    
                    <individu  key='" . $user_key . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='" . $concours_mod[0] . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <concours objet_id='" . $concours_session[0] . "' ForceImport='true'/>
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : ($user->formation == 1 ? $this->getAlternance($user->es_fnum) : 103503)) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . ($user->ID_Type_Convention ?: $user->id_TypeDeConvention) . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46312' ForceImport='true' /> 
                
                </inscription_concours>";

		}
		else {
			$inscription_concours = "";
		}


		$xml = new XMLWriter();
		$xml->openMemory();

		$xml->startElement("importData");

		$xml->startElement("modeSynchrone");
		$xml->text('true');
		$xml->endElement();

		$xml->startElement("database");
		$xml->text('esc_larochelle');
		$xml->endElement();

		$xml->startElement("xml");
		// insert data
		$xml->writeCData("
            <import_candidat DatabaseName='esc_larochelle'>
                
                " . $individu . "
                
                " . $inscription_module . "
                
                " . $inscription_cours . "

                " . $inscription_concours . "

            </import_candidat>
        ");

		// end xml tag
		$xml->endElement();

		// end import_candidat tag
		$xml->endElement();

		return $xml->flush();
	}


	/**
	 * Build XML file for a user that exists in Aurion
	 *
	 * @param   Object  $user
	 *
	 * @return bool
	 *
	 * @since version
	 */
	function buildExistingUserXml($user)
	{

		// $user_key = LASTNAME_FIRSTNAME_SEX_DATE_BIRHTDATE
		$user_key = strtoupper($this->replaceUserName($user->lastname)) . "_" . strtoupper($this->replaceUserName($user->firstname)) . "_" . $user->Sexe . "_" . date('dmY', strtotime($user->birth_date));
		// Check if the candidat is applying for the Winter/Summer school
		$winter_spes = $this->winterSummerSpes($user->win_parent_id);

		// get the user's aurion id
		$user_id = !empty($user->id_Individu) ? $user->id_Individu : $user->aurion_user;

		// Check if the the user has filled out their qualification form AND their scholarship form, don't import if one of them doesn't have a fnum
		if (!empty($user->es_fnum)) {
			if (!empty($user->city)) {
				$qualification_city = htmlspecialchars($this->getOutputLabelFromInput($user->aurion_city, 'id_Ville', 'Nom', 'data_aurion_35584331'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
			}
			else {
				$qualification_city = htmlspecialchars($user->city_2, ENT_XML1 | ENT_QUOTES, 'UTF-8');
			}

			$eq_lycee = !empty($user->lycee) ? "<lycee.client ForceDest='lycee.client§47996272' objet_id='" . $user->id_Lycee . "' ForceImport='true'/>" : "";

			$eq_etablissment = !empty($user->etab_sup) ? "<etablissement_superieur.client objet_id='" . $user->id_etab . "' ForceDest='etablissement_superieur.client§47997720' ForceImport='true'/>" : "";

			$other_etabs = !empty($eq_lycee) ? "A47997723='" . htmlspecialchars($user->university, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "'" : "A37765483='" . htmlspecialchars($user->university, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "'";

			$type_diplome = !empty($user->type_diplome) ? "<typediplome_emundus.client objet_id='" . $user->type_diplome . "' ForceDest='typediplome_emundus.client§48130556' ForceImport='true'/>" : '';
			$eq_country   = !empty($user->eq_country) ? "<pays objet_id='" . $user->eq_country . "' ForceDest='pays§37765785' ForceImport='true'/>" : "";

			$cours = "<cours ForceDest='cours§99785' objet_id='" . $user->speciality . "' ForceImport='true' />";

			// Check if the candidat is applying for the Winter/Summer school
			if (!empty($winter_spes)) {
				$cours = "";
				$cours .= "<cours ForceDest='cours§99785' objet_id='" . $winter_spes[0]->speciality . "' ForceImport='true' />";
			}

			$inscription_module = "
                <inscription_module ForceImport='true' key='" . $user->aurion_id . "_" . $user_key . "'  A3310='" . date('d-m-Y') . "' A87232='true' A46372499='" . $user->is_hadicap . "' " . $other_etabs . "  A37765709='" . $user->state . "' A37765733='" . $qualification_city . "' >

                    <individu objet_id='" . $user_id . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <module objet_id='" . $user->aurion_id . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <module objet_id='" . $user->aurion_id . "' ForceImport='true'/>
                    
                    <langue objet_id='" . $user->lv1 . "' ForceDest='langue§4649742' ForceImport='true'/>
                    
                    <langue objet_id='" . $user->lv2 . "' ForceDest='langue§4649756' ForceImport='true'/>
                    
                    <niveau_formation objet_id='" . $user->level . "' ForceDest='niveau_formation§37764238' ForceImport='true'/>
                                        
                    " . $eq_lycee . "
                    " . $eq_etablissment . "
                    " . $type_diplome . "
                    " . $eq_country . "

                    <typediplome_fr_int_.client ForceDest='typediplome_fr_int_.client§101204' objet_id='" . $user->id_TypeDiplome . "' ForceImport='true' />
                    
                    <rentree.client ForceDest='rentree.client§2954426' objet_id='" . $user->entrance . "' ForceImport='true' />
                    
                    " . $cours . "
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : ($user->formation == 1 ? $this->getAlternance($user->es_fnum) : 103503)) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . ($user->ID_Type_Convention ?: $user->id_TypeDeConvention) . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46311' ForceImport='true' />
                    
                </inscription_module>";
		}
		else {
			$inscription_module = "";
		}

		// Check if the the user has filled out their scholarship form, don't import if no fnum
		if (!empty($user->es_fnum)) {

			if (!empty($winter_spes)) {

				$inscription_cours = "";
				foreach ($winter_spes as $spe) {
					$inscription_cours .= "
                        <inscription_cours ForceImport='true' key='" . $spe . "_" . $user_key . "'  A2244='" . date('d-m-Y') . "' >
                            
                            <individu objet_id='" . $user_id . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                                <cours objet_id='" . $spe . "' ForceSource='apprenant'/>
                            </individu>
                            
                            <cours objet_id='" . $spe . "' ForceImport='true'/>
                            
                            <type_apprenant objet_id='" . (empty($user->formation) ? '' : ($user->formation == 1 ? $this->getAlternance($user->es_fnum) : 103503)) . "' ForceImport='true' />
                            
                            <type_convention objet_id='" . ($user->ID_Type_Convention ?: $user->id_TypeDeConvention) . "' ForceImport='true' />
                            
                            <statut_inscription objet_id='46311' ForceImport='true' />
                        
                        </inscription_cours>";
				}

			}
			else {
				$inscription_cours = "
                <inscription_cours ForceImport='true' key='" . $user->speciality . "_" . $user_key . "'  A2244='" . date('d-m-Y') . "' >
                    
                    <individu objet_id='" . $user_id . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='" . $user->speciality . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <cours objet_id='" . $user->speciality . "' ForceImport='true'/>
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : ($user->formation == 1 ? $this->getAlternance($user->es_fnum) : 103503)) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . ($user->ID_Type_Convention ?: $user->id_TypeDeConvention) . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46311' ForceImport='true' />
                
                </inscription_cours>";
			}
		}
		else {
			$inscription_cours = "";
		}

		// Check if the the user has filled out their session_concours form, don't import if no fnum
		if (!empty($user->econ_fnum)) {

			// Filter the modules and sessions to get the one selected
			$concours_mod     = array_values(array_filter([$user->concours_coord_mod, $user->concours_easy_mod, $user->concours_mod, $user->concours_mod2], function ($n) {
				return !empty($n);
			}));
			$concours_session = array_values(array_filter([$user->concours_coord_session, $user->concours_easy_session, $user->concours_session, $user->concours_session2], function ($n) {
				return !empty($n);
			}));

			$inscription_concours = "
                <inscription_concours ForceImport='true' key='" . (!empty($user->concours_easy_session) ? $user->concours_easy_session : (!empty($user->concours_session) ? $user->concours_session : $user->concours_session2)) . "_" . $user_key . "'  A4620='" . date('d-m-Y') . "' >
                    
                    <individu objet_id='" . $user_id . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='" . $concours_mod[0] . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <concours objet_id='" . $concours_session[0] . "' ForceImport='true'/>
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : ($user->formation == 1 ? $this->getAlternance($user->es_fnum) : 103503)) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . ($user->ID_Type_Convention ?: $user->id_TypeDeConvention) . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46312' ForceImport='true' />
                
                </inscription_concours>";
		}
		else {
			$inscription_concours = "";
		}

		// create XLM
		$xml = new XMLWriter();
		$xml->openMemory();

		$xml->startElement("importData");

		$xml->startElement("modeSynchrone");
		$xml->text('true');
		$xml->endElement();

		$xml->startElement("database");
		$xml->text('esc_larochelle');
		$xml->endElement();

		$xml->startElement("xml");

		// insert data
		$xml->writeCData("
            <import_candidat DatabaseName='esc_larochelle'>
            
                <individu objet_id='" . $user_id . "' A39218849='" . $user->user_id . "' />

                " . $inscription_module . "
                
                " . $inscription_cours . "

                " . $inscription_concours . "
                
            </import_candidat>
        ");

		// end xml tag
		$xml->endElement();

		// end import_candidat tag
		$xml->endElement();

		return $xml->flush();
	}

	function replaceUserName($str)
	{

		$unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
		                        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
		                        'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
		                        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
		                        'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');

		$newstr = strtr($str, $unwanted_array);

		$newstr = preg_replace('/[^a-zA-Z0-9\']/', '', $newstr);
		$newstr = str_replace(array("'", " "), '', $newstr);

		return $newstr;
	}

	function replaceUserNameWithSpace($str)
	{

		$unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
		                        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
		                        'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
		                        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
		                        'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');

		$newstr = strtr($str, $unwanted_array);

		$newstr = preg_replace("/[^\w#& ]/", '', $newstr);
		$newstr = str_replace("'", '', $newstr);

		return $newstr;
	}

	public function getOutputLabelFromInput($input, $inputColumn, $outputColumn, $table)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName($outputColumn))
			->from($db->quoteName($table))
			->where($db->quoteName($inputColumn) . ' = ' . $db->quote($input));

		try {
			$db->setQuery($query);

			return $db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Query error ' . $query->__toString(), JLog::ERROR, 'com_emundus_exceliaAurionExport');

			return $input;
		}
	}


	function getAlternance($fnum)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select([$db->quoteName('da_fr.id_TypeDApprenant', 'alt_fr'), $db->quoteName('da_int.id_TypedApprenant', 'alt_int')])
			->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
			->leftjoin($db->quoteName('data_aurion_37130437', 'da_int') . ' ON ' . $db->quoteName('da_int.id') . ' = ' . $db->quoteName('cc.spe_int_alt'))
			->leftjoin($db->quoteName('data_aurion_35616195', 'da_fr') . ' ON ' . $db->quoteName('da_fr.id') . ' = ' . $db->quoteName('cc.spe_fr_alt'))
			->where($db->quoteName('cc.fnum') . ' LIKE ' . $db->quote($fnum));

		try {

			$db->setQuery($query);

			return array_values(array_filter($db->loadAssoc()))[0];
		}
		catch (Exception $e) {
			JLog::add('Query error ' . $query->__toString(), JLog::ERROR, 'com_emundus_exceliaAurionExport');

			return '';
		}

	}

	function winterSummerSpes($val)
	{

		if (empty($val)) {
			return null;
		}

		$query = $this->db->getQuery(true);

		$query
			->select([$this->db->qn('win_sum.id_Module_Specialisation', 'speciality'), $this->db->qn('win_sum.id_RentreesPossibleINT', 'win_rentree')])
			->from($this->db->qn('jos_emundus_scholarship_repeat_spe_win_sum', 'win_sum_repeat'))
			->leftJoin($this->db->quoteName('data_aurion_42539590', 'win_sum') . ' ON ' . $this->db->quoteName('win_sum.id') . ' = ' . $this->db->quoteName('win_sum_repeat.spe_win_sum'))
			->where($this->db->qn('win_sum_repeat.parent_id') . ' = ' . $val);

		$this->db->setQuery($query);

		try {
			return $this->db->loadObjectList();
		}
		catch (Execption $e) {
			JLog::add('Query error getting winter/summer school speacialities : ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus_exceliaAurionExport');

			return null;
		}
	}
}