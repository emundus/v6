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

            $query = $this->db->getQuery(true);
            $query
                ->select(['ecc.applicant_id, da.emundus_id'])
                ->from($this->db->quoteName('#__emundus_campaign_candidature', 'ecc'))
                ->leftJoin($this->db->quoteName('data_aurion_39177663', 'da') . ' ON ' . $this->db->quoteName('da.emundus_id') . ' = '. $this->db->quoteName('ecc.applicant_id'))
                ->where($this->db->quoteName('ecc.fnum') . ' IN ('. implode(',', $this->db->quote($fnums)).')');

            try {
                $this->db->setQuery($query);

                $users = $this->db->loadObjectList('applicant_id');
            } catch (Exception $e) {
                JLog::add('Could not get applicant ids from the campaign table. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                return false;
            }
            
            if (!empty($users)) {

                $em_columns= [
                    'em.firstname',
                    'em.lastname',
                    'em.email',
                    'em.tel',
                    'em.civility',
                    'em.county',
                    'em.nationality',
                    'em.candidat'
                ];

                $pd_columns = [
                    'pd.skype_id',
                    'pd.street_1',
                    'pd.street_2',
                    'pd.street_3',
                    'pd.city_1',
                    'pd.country_1'
                ];

                $qualafication_columns = [
                    'q.first_language',
                    'q.university',
                    'q.state',
                    'q.city',
                    'q.lv1',
                    'q.lv2',
                    'q.level',
                    'q.type',
                    'q.country',
                ];

                foreach ($users as $user) {
                    if (empty($user->emundus_id)) {
                        $xml = $this->buildNewUserXml($user);
                    }
                }
            }
            /*
            require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');
            $m_files = new EmundusModelFiles();

            $fnumsInfo = $m_files->getFnumsInfos($fnums);


            */
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
        $xml = new XMLWriter();
        $xml->openMemory();

        $xml->startElement("import_candidat");

        $xml->startElement("modeSynchrone");
        $xml->text('true');
        $xml->endElement();

        // TODO: Set DB as a var
        $xml->startElement("database");
        $xml->text('esc_larochelle');
        $xml->endElement();

        $xml->startElement("xml");
        $xml->writeCData ( "
            <import_candidat DatabaseName='esc_larochelle'>
                <individu key='TOUILLE_SACHA_M_04122019' code='TOUILLE' libelle='Sacha' A595='04-12-2019' A596='M' A39153560='Danois' A39218849='EM65158' A601='true'>
                    <titre objet_id='86028' ForceImport='true' ForceReplace='true' />
                    <nationalite objet_id='46561' ForceImport='true' />
                    <coordonnee key='EMAIL_PERSO_TOUILLE_SACHA_M_04122019' libelle='sacha.touille@gmail.com'>
                        <type_coordonnee objet_id='44754' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </coordonnee>
                    <coordonnee key='TEL_PORT_TOUILLE_SACHA_M_04122019' libelle='0646201546'>
                        <type_coordonnee objet_id='86166' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </coordonnee>
                    <coordonnee key='SKYPE_TOUILLE_SACHA_M_04122019' libelle='sacha.touille'>
                        <type_coordonnee objet_id='86334' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </coordonnee>
                    <adresse key='ADR_PERSO_TOUILLE_SACHA_M_04122019' A500='25 Rue de Coureilles' A501='Lieu dit Touille' A502='Bat B'>
                        <ville objet_id='45593' ForceImport='true' />
                        <pays objet_id='46727' ForceImport='true' />
                        <type_adresse objet_id='44755' OnRelation='true' ForceImport='true' ForceReplace='true' />
                    </adresse>
                </individu>
                
                <inscription_module ForceImport='true' key='16883254_TOUILLE_SACHA_M_04122019'  A3310='04-12-2019' A37765483='Axcelia' A37765709='17000' A37765733='La Rochelle' >
                    <individu  key='TOUILLE_SACHA_M_04122019' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <module objet_id='16883254' ForceSource='apprenant'/>
                    </individu>
                    <module objet_id='16883254' ForceImport='true'/>
                    <langue objet_id='86017' ForceDest='langue§4649742' ForceImport='true'/>
                    <langue objet_id='' ForceDest='langue§4649756' ForceImport='true'/>
                    <niveau_formation objet_id='95082' ForceDest='niveau_formation§37764238' ForceImport='true'/>
                    <typeetablissement.client objet_id='95123' ForceDest='typeetablissement.client§37765649' ForceImport='true'/>
                    <pays objet_id='46727' ForceDest='pays§37765785' ForceImport='true'/>
                    <typediplome_fr_int_.client ForceDest='typediplome_fr_int_.client§101204' objet_id='86040' ForceImport='true' />
                    <rentree.client ForceDest='rentree.client§2954426' objet_id='88559' ForceImport='true' />
                    <cours ForceDest='cours§99785' objet_id='21131956' ForceImport='true' />
                    <type_apprenant objet_id='103503' ForceImport='true' />
                    <type_convention objet_id='132277' ForceImport='true' />
                    <statut_inscription objet_id='46311' ForceImport='true' />
                </inscription_module>
                
                <inscription_cours ForceImport='true' key='21131956_TOUILLE_SACHA_M_04122019'  A2244='04-12-2019' >
                    <individu  key='TOUILLE_SACHA_M_04122019' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='21131956' ForceSource='apprenant'/>
                    </individu>
                    <cours objet_id='21131956' ForceImport='true'/>
                    <type_apprenant objet_id='103503' ForceImport='true' />
                    <type_convention objet_id='132277' ForceImport='true' />
                    <statut_inscription objet_id='46311' ForceImport='true' />
                </inscription_cours>

                <inscription_concours ForceImport='true' key='38904012_TOUILLE_SACHA_M_04122019'  A4620='04-12-2019' >
                    <individu  key='TOUILLE_SACHA_M_04122019' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='38904011' ForceSource='apprenant'/>
                    </individu>
                    <concours objet_id='38904012' ForceImport='true'/>
                    <type_apprenant objet_id='103503' ForceImport='true' />
                    <type_convention objet_id='132277' ForceImport='true' />
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
        $xml = XMLWriter::text();


        $xml->startElement("import_candidat");

        $xml->startElement("modeSynchrone");
        $xml->text('true');
        $xml->endElement();

        // TODO: Set DB as a var
        $xml->startElement("database");
        $xml->text('esc_larochelle');
        $xml->endElement();

        $xml->startElement("xml");
        $xml->writeCData ( "
            <import_candidat DatabaseName='esc_larochelle'>
                <inscription_module ForceImport='true' key='35937318_TOUILLE_SACHA_M_DATE_04122019'  A3310='04-12-2019' A37765483='Excelia' A37765709='17000' A37765733='La Rochelle' >
                    <individu objet_id='39218992' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <module objet_id='35937318' ForceSource='apprenant'/>
                    </individu>
                    <module objet_id='35937318' ForceImport='true'/>
                    <langue objet_id='' ForceDest='langue§4649742' ForceImport='true'/>
                    <langue objet_id='' ForceDest='langue§4649756' ForceImport='true'/>
                    <niveau_formation objet_id='' ForceDest='niveau_formation§37764238' ForceImport='true'/>
                    <typeetablissement.client objet_id='95123' ForceDest='typeetablissement.client§37765649' ForceImport='true'/>
                    <pays objet_id='46727' ForceDest='pays§37765785' ForceImport='true'/>
                    <typediplome_fr_int_.client ForceDest='typediplome_fr_int_.client§101204' objet_id='86040' ForceImport='true' />
                    <rentree.client ForceDest='rentree.client§2954426' objet_id='88560' ForceImport='true' />
                    <cours ForceDest='cours§99785' objet_id='36422371' ForceImport='true' />
                    <type_apprenant objet_id='103509' ForceImport='true' />
                    <type_convention objet_id='132277' ForceImport='true' />
                    <statut_inscription objet_id='46311' ForceImport='true' />
                </inscription_module>

                <inscription_cours ForceImport='true' key='36422371_TOUILLE_SACHA_M_DATE_04122019'  A2244='04-12-2019' >
                    <individu objet_id='39218992' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                    <cours objet_id='36422371' ForceSource='apprenant'/>
                    </individu><cours objet_id='36422371' ForceImport='true'/>
                    <type_apprenant objet_id='103509' ForceImport='true' />
                    <type_convention objet_id='132277' ForceImport='true' />
                    <statut_inscription objet_id='46311' ForceImport='true' />
                </inscription_cours>

                <inscription_concours ForceImport='true' key='38734563_TOUILLE_SACHA_M_DATE_04122019'  A4620='04-12-2019' >
                    <individu objet_id='39218992' ForceDest='apprenant' Inverted='true' UpdateMode='none' >
                        <cours objet_id='38734562' ForceSource='apprenant'/>
                    </individu>
                    <concours objet_id='38734563' ForceImport='true'/>
                    <type_apprenant objet_id='103509' ForceImport='true' />
                    <type_convention objet_id='132277' ForceImport='true' />
                    <statut_inscription objet_id='46312' ForceImport='true' />
                </inscription_concours>
            </import_candidat>
        " );

        // end xml tag
        $xml->endElement();

        // end import_candidat tag
        $xml->endElement();
    }


}
