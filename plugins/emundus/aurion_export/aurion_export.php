<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusAurion_export extends JPlugin {

    var $db;
    var $query;

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.aurionExport.php'), JLog::ALL, array('com_emundus'));
    }


    /**
     * Export all fnums in params to Exceila Aurion
     * Method is called on the eMundus Export plugin if the Aurion param is set
    * @param Array $fnums
     * @return bool
     *
     * @since version
     */
    function onExportFiles($fnums, $type) {


        
        if ($type !== 'excelia_aurion' || empty($fnums)) {
            return false;
        }
        else {
            // Get Aurion params to export
            $aurion_url = $this->params->get('url', null);
            $aurion_login = $this->params->get('login', null);
            $aurion_pass = $this->params->get('password', null);

            if (empty($aurion_url) || empty($aurion_login) || empty($aurion_pass)) {
                JLog::add('Could not run plugin, missing param', JLog::ERROR, 'com_emundus');
                return false;
            }


            // Build the query Select
            //eMundus data
            $campaign_columns = [
                'esc.aurion_id'
            ];

            $eu_columns= [
                $this->db->quoteName('eu.user_id'),
                $this->db->quoteName('eu.firstname'),
                $this->db->quoteName('eu.lastname'),
                $this->db->quoteName('eu.email'),
                $this->db->quoteName('eu.tel'),
                $this->db->quoteName('eu.civility'),
                $this->db->quoteName('eu.country'),
                $this->db->quoteName('eu.nationality')
            ];

            $pd_columns = [
                $this->db->quoteName('epd.skype_id'),
                $this->db->quoteName('epd.street_1'),
                $this->db->quoteName('epd.street_2'),
                $this->db->quoteName('epd.street_3'),
                $this->db->quoteName('epd.city_1'),
                $this->db->quoteName('epd.birth_date'),
                $this->db->quoteName('epd.country_1')
            ];

            $qualification_columns = [
                $this->db->quoteName('eq.first_language'),
                $this->db->quoteName('eq.university'),
                $this->db->quoteName('eq.state'),
                $this->db->quoteName('eq.city'),
                $this->db->quoteName('eq.city_2'),
                $this->db->quoteName('eq.lv1'),
                $this->db->quoteName('eq.lv2'),
                $this->db->quoteName('eq.level'),
                $this->db->quoteName('eq.type'),
                $this->db->quoteName('eq.country', 'eq_country')
            ];

            $scholarship_columns = [
                $this->db->quoteName('es.mail_excelia'),
                $this->db->quoteName('es.spe_int_alt'),
                $this->db->quoteName('es.spe_int_cla'),
                $this->db->quoteName('es.spe_fr_cla'),
                $this->db->quoteName('es.spe_fr_alt'),
                $this->db->quoteName('es.rentree_int_alt'),
                $this->db->quoteName('es.rentree_int_cla'),
                $this->db->quoteName('es.rentree_fr_alt'),
                $this->db->quoteName('es.formation'),
                $this->db->quoteName('es.rentree_fr_cla')
            ];

            $concours_columns = [
                $this->db->quoteName('econ.concours_session')
            ];

            // Aurion Data
            // data_aurion_37736495
            $aurion_user = [
                $this->db->quoteName('dau.id_Individu')
            ];

            // data_aurion_39177663
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

            // data_aurion_37241402
            $aurion_concours = [
                $this->db->quoteName('dacon.id_Module', 'concours_mod')
            ];



            $query = $this->db->getQuery(true);

            $query
                ->select(array_merge_recursive($campaign_columns, $eu_columns, $pd_columns, $qualification_columns, $scholarship_columns, $concours_columns, $aurion_user, $aurion_em_user, $aurion_civility, $aurion_diplome, $aurion_nationality, $aurion_concours))
                ->from($this->db->quoteName('#__emundus_campaign_candidature', 'ecc'))
                ->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->db->quoteName('ecc.campaign_id') . ' = '. $this->db->quoteName('esc.id'))
                ->leftJoin($this->db->quoteName('#__emundus_users', 'eu') . ' ON ' . $this->db->quoteName('ecc.applicant_id') . ' = '. $this->db->quoteName('eu.user_id'))
                ->leftJoin($this->db->quoteName('#__emundus_personal_detail', 'epd') . ' ON ' . $this->db->quoteName('ecc.fnum') . ' = '. $this->db->quoteName('epd.fnum'))
                ->leftJoin($this->db->quoteName('#__emundus_qualifications', 'eq') . ' ON ' . $this->db->quoteName('ecc.fnum') . ' = '. $this->db->quoteName('eq.fnum'))
                ->leftJoin($this->db->quoteName('#__emundus_scholarship', 'es') . ' ON ' . $this->db->quoteName('ecc.fnum') . ' = '. $this->db->quoteName('es.fnum'))
                ->leftJoin($this->db->quoteName('#__emundus_concours_sessions', 'econ') . ' ON ' . $this->db->quoteName('ecc.fnum') . ' = '. $this->db->quoteName('econ.fnum'))
                ->leftJoin($this->db->quoteName('data_aurion_37736495', 'dau') . ' ON ' . $this->db->quoteName('es.mail_excelia') . ' = '. $this->db->quoteName('dau.MailEcole'))
                ->leftJoin($this->db->quoteName('data_aurion_39177663', 'deu') . ' ON ' . $this->db->quoteName('ecc.applicant_id') . ' = '. $this->db->quoteName('deu.emundus_id'))
                ->leftJoin($this->db->quoteName('data_aurion_35347585', 'dac') . ' ON ' . $this->db->quoteName('eu.civility') . ' = '. $this->db->quoteName('dac.id_Titre'))
                ->leftJoin($this->db->quoteName('data_aurion_35616031', 'dad') . ' ON ' . $this->db->quoteName('eu.candidat') . ' = '. $this->db->quoteName('dad.Code_TypeDiplome'))
                ->leftJoin($this->db->quoteName('data_aurion_35581810', 'dan') . ' ON ' . $this->db->quoteName('eu.nationality') . ' = '. $this->db->quoteName('dan.id_Nationalite'))
                ->leftJoin($this->db->quoteName('data_aurion_37241402', 'dacon') . ' ON ' . $this->db->quoteName('econ.concours_session') . ' = '. $this->db->quoteName('dacon.id_Concours'))
                ->where($this->db->quoteName('ecc.fnum') . ' IN (' . implode(', ', $this->db->quote($fnums)). ')');

            try {
                $this->db->setQuery($query);

                $users = $this->db->loadObjectList('user_id');

            } catch (Exception $e) {
                JLog::add('Could not get applicant info. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                return false;
            }

            if (empty($users)) {
                return false;
            }

            foreach ($users as $user) {

                // Set the user's spe and entrance value by getting the unique value from the 4 different possibilities
                $user->speciality = array_values(array_filter([$user ->spe_int_alt, $user ->spe_int_cla, $user ->spe_fr_alt, $user ->spe_fr_cla]))[0];
                $user->entrance = array_values(array_filter([$user ->rentree_int_alt, $user ->rentree_int_cla, $user ->rentree_fr_alt, $user ->rentree_fr_cla]))[0];

                if (empty($user->id_Individu)) {
                    $xml_export = $this->buildNewUserXml($user);

                }
                else {
                    $xml_export = $this->buildExistingUserXml($user);
                }

                if(empty($xml_export)) {
                    return false;
                }

                $http = new JHttp();

                $request_body = [
                    'login' => $aurion_login,
                    'password' => $aurion_pass,
                    'data' => $xml_export
                ];

                $response = $http->post($aurion_url, $request_body, ['Content-Type' => 'application/x-www-form-urlencoded']);
                
                if ($response->code === 200) {

                    // The API almost always responds with a 200OK, however certain errors are in HTML
                    $data = simplexml_load_string($response->body);

                    if ($data === false) {
                        JLog::add('
						Error parsing XML: this could be an error in the request \n 
						URL: '.$aurion_url.' \n
						POST DATA: '.$xml_export.' \n
						RESPONSE BODY: '.$response->body.'
					', JLog::ERROR, 'com_emundus');
                        return false;
                    }

                    if ($data->getName() === 'erreur') {
                        JLog::add('
						Error detected: \n 
						URL: '.$aurion_url.' \n
						POST DATA: '.$xml_export.' \n
						ERROR MESSAGE: '.$data->body.'
					', JLog::ERROR, 'com_emundus');
                        return false;
                    }
                }
                else {
                    JLog::add('
						HTTP ERROR: Response not 200 OK \n 
						URL: '.$aurion_url.' \n
						POST DATA: '.$xml_export.' \n
						RESPONSE CODE: '.$response->code.' \n
						RESPONSE BODY: '.$response->body.'
					', JLog::ERROR, 'com_emundus');
                    return false;
                }

            }
        }
    }

    /**
     * Build XML file for a user that doesn't exist in Aurion
     * @param Object $user
     * @return bool
     *
     * @since version
     */
    function buildNewUserXml($user) {

        $user_key = strtoupper($user->lastname) . "_" . strtoupper($user->firstname) . "_" . $user->Sexe . "_" . date('dmY', strtotime($user->birth_date));

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
        $xml->writeCData ( "
            <import_candidat DatabaseName='esc_larochelle'>
                <individu key='" . $user_key . "' code='" . strtoupper($user->lastname) . "' libelle='" . ucfirst($user->lastname) . "' A595='" . date('d-m-Y') . "' A596='" . $user->Sexe . "' A39153560='" . $user->aurion_nationality . "' A39218849='" . $user->user_id . "' A601='true'>
                    
                    <titre objet_id='" . $user->civility . "' ForceImport='true' ForceReplace='true' />
                    <nationalite objet_id='" . $user->nationality . "' ForceImport='true' />
                    
                    <coordonnee key='EMAIL_PERSO_" . $user_key . "' libelle='" . $user->email . "'>
                        <type_coordonnee objet_id='44754' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </coordonnee>
                    
                    <coordonnee key='TEL_PORT_" . $user_key . "' libelle='" . $user->tel . "'>
                        <type_coordonnee objet_id='86166' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </coordonnee>
                    
                    <coordonnee key='SKYPE_" . $user_key . "' libelle='" . $user->skype_id . "'>
                        <type_coordonnee objet_id='86334' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </coordonnee>
                    
                    <adresse key='ADR_PERSO_" . $user_key . "' A500='" . $user->street_1 . "' A501='" . $user->street_2 . "' A502='" . $user->street_3 . "'>
                        <ville objet_id='" . $user->city_1 . "' ForceImport='true' />
                        <pays objet_id='" . $user->country_1 . "' ForceImport='true' />
                        <type_adresse objet_id='44755' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </adresse>
                    
                </individu>
                
                <inscription_module ForceImport='true' key='" . $user->aurion_id . "_" . $user_key . "'  A3310='" . date('d-m-Y') . "' A37765483='" . $user->university . "' A37765709='" . $user->state. "' A37765733='" . (!empty($user->city) ? $user->city : $user->city_2) . "' >
                    
                    <individu  key='" . $user_key . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <module objet_id='" . $user->aurion_id . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <module objet_id='" . $user->aurion_id . "' ForceImport='true'/>
                    
                    <langue objet_id='" . $user->lv1 . "' ForceDest='langue§4649742' ForceImport='true'/>
                    
                    <langue objet_id='" . $user->lv2 . "' ForceDest='langue§4649756' ForceImport='true'/>
                    
                    <niveau_formation objet_id='" . $user->level . "' ForceDest='niveau_formation§37764238' ForceImport='true'/>
                    
                    <typeetablissement.client objet_id='" . $user->type . "' ForceDest='typeetablissement.client§37765649' ForceImport='true'/>
                    
                    <pays objet_id='" . $user->eq_country . "' ForceDest='pays§37765785' ForceImport='true'/>
                    
                    <typediplome_fr_int_.client ForceDest='typediplome_fr_int_.client§101204' objet_id='" . $user->id_TypeDiplome . "' ForceImport='true' />
                    
                    <rentree.client ForceDest='rentree.client§2954426' objet_id='" . $user->entrance . "' ForceImport='true' />
                   
                    <cours ForceDest='cours§99785' objet_id='" . $user->speciality . "' ForceImport='true' />
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : $user->formation==1 ? 103509 : 103503) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . $user->id_TypeDeConvention . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46311' ForceImport='true' />
                    
                </inscription_module>
                
                <inscription_cours ForceImport='true' key='" . $user->speciality . "_" . $user_key . "'  A2244='" . date('d-m-Y') . "' >
                    
                    <individu  key='" . $user_key . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='" . $user->speciality . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <cours objet_id='" . $user->speciality . "' ForceImport='true'/>
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : $user->formation==1 ? 103509 : 103503) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . $user->id_TypeDeConvention . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46311' ForceImport='true' />
                
                </inscription_cours>

                <inscription_concours ForceImport='true' key='" . $user->concours_sessions . "_" . $user_key . "'  A4620='" . date('d-m-Y') . "' >
                    
                    <individu  key='" . $user_key . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='" . $user->concours_mod . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <concours objet_id='" . $user->concours_sessions . "' ForceImport='true'/>
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : $user->formation==1 ? 103509 : 103503) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . $user->id_TypeDeConvention . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46312' ForceImport='true' /> 
                
                </inscription_concours>

            </import_candidat>
        " );

        // end xml tag
        $xml->endElement();

        // end import_candidat tag
        $xml->endElement();

        return $xml->flush();
    }


    /**
     * Build XML file for a user that exists in Aurion
     * @param Object $user
     * @return bool
     *
     * @since version
     */
    function buildExistingUserXml($user) {

        $user_key = $user->aurion_id . "_" . strtoupper($user->lastname) . "_" . strtoupper($user->firstname) . "_" . $user->Sexe . "_DATE_" . date('dmY', strtotime($user->birth_date));
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
        $xml->writeCData ( "
        
            <import_candidat DatabaseName='esc_larochelle'>

                <inscription_module ForceImport='true' key='" . $user_key . "'  A3310='" . date('d-m-Y') . "' A37765483='" . $user->university . "' A37765709='" . $user->state. "' A37765733='" . (!empty($user->city) ? $user->city : $user->city_2) . "' >
                    
                    <individu objet_id='" . $user->id_Individu . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <module objet_id='" . $user->aurion_id . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <module objet_id='" . $user->aurion_id . "' ForceImport='true'/>
                    
                    <langue objet_id='" . $user->lv1 . "' ForceDest='langue§4649742' ForceImport='true'/>
                    
                    <langue objet_id='" . $user->lv2 . "' ForceDest='langue§4649756' ForceImport='true'/>
                    
                    <niveau_formation objet_id='" . $user->level . "' ForceDest='niveau_formation§37764238' ForceImport='true'/>
                    
                    <typeetablissement.client objet_id='" . $user->type . "' ForceDest='typeetablissement.client§37765649' ForceImport='true'/>
                    
                    <pays objet_id='" . $user->eq_country . "' ForceDest='pays§37765785' ForceImport='true'/>
                    
                    <typediplome_fr_int_.client ForceDest='typediplome_fr_int_.client§101204' objet_id='" . $user->id_TypeDiplome . "' ForceImport='true' />
                    
                    <rentree.client ForceDest='rentree.client§2954426' objet_id='" . $user->entrance . "' ForceImport='true' />
                    
                    <cours ForceDest='cours§99785' objet_id='" . $user->speciality . "' ForceImport='true' />
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : $user->formation==1 ? 103509 : 103503) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . $user->id_TypeDeConvention . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46311' ForceImport='true' />
                    
                </inscription_module>

                <inscription_cours ForceImport='true' key='" . $user->speciality . "_" . $user_key . "'  A2244='" . date('d-m-Y') . "' >
                    
                    <individu objet_id='" . $user->id_Individu . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='" . $user->speciality . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <cours objet_id='" . $user->speciality . "' ForceImport='true'/>
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : $user->formation==1 ? 103509 : 103503) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . $user->id_TypeDeConvention . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46311' ForceImport='true' />
                
                </inscription_cours>

                <inscription_concours ForceImport='true' key='" . $user->concours_sessions . "_" . $user_key . "'  A4620='04-12-2019' >
                    
                    <individu objet_id='" . $user->id_Individu . "' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='" . $user->concours_mod . "' ForceSource='apprenant'/>
                    </individu>
                    
                    <concours objet_id='" . $user->concours_sessions . "' ForceImport='true'/>
                    
                    <type_apprenant objet_id='" . (empty($user->formation) ? '' : $user->formation==1 ? 103509 : 103503) . "' ForceImport='true' />
                    
                    <type_convention objet_id='" . $user->id_TypeDeConvention . "' ForceImport='true' />
                    
                    <statut_inscription objet_id='46312' ForceImport='true' />
                
                </inscription_concours>
                
            </import_candidat>
        " );

        // end xml tag
        $xml->endElement();

        // end import_candidat tag
        $xml->endElement();

        return $xml->flush();
    }


}
