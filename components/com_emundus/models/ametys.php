<?php
/**
 * @package         Joomla
 * @subpackage      eMundus
 * @link            http://www.emundus.fr
 * @copyright       Copyright (C) 2015 eMundus. All rights reserved.
 * @license         GNU/GPL
 * @author          Benjamin Rivalland
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
/*
if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    use PhpOffice\PhpWord\Exception\rootException;
}
*/
jimport('joomla.application.component.model');
require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS. 'helpers' . DS . 'files.php');

/**
 * Class EmundusModelFiles
 */
class EmundusModelAmetys extends JModelLegacy
{
    public $db;
    public $dbAmetys;

    /**
     * Constructor
     *
     * @since 1.5
     */
    public function __construct()
    {
        parent::__construct();

        include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');

        $mainframe = JFactory::getApplication();

        // Get current menu parameters
        $menu = @JFactory::getApplication()->getMenu();
        $current_menu = $menu->getActive();

        $this->db = JFactory::getDBO();
        $this->dbAmetys = $this->getAmetysDBO();

        /*
        ** @TODO : gestion du cas Itemid absent à prendre en charge dans la vue
        */
        if (empty($current_menu))
            return false;
        
        $menu_params = $menu->getParams($current_menu->id);
        //$em_other_columns = explode(',', $menu_params->get('em_other_columns'));

        $session = JFactory::getSession();
        if (!$session->has('filter_order'))
        {
            $session->set('filter_order', '');
            $session->set('filter_order_Dir', 'desc');
        }

        if(!$session->has('limit'))
        {
            $limit = $mainframe->getCfg('list_limit');
            $limitstart = 0;
            $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

            $session->set('limit', $limit);
            $session->set('limitstart', $limitstart);
        }
        else
        {
            $limit = intval($session->get('limit'));
            $limitstart = intval($session->get('limitstart'));
            $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

            $session->set('limit', $limit);
            $session->set('limitstart', $limitstart);
        }
    }


    /**
     * @return string
     */
    public function _buildContentOrderBy()
    {
        $filter_order = JFactory::getSession()->get('filter_order');
        $filter_order_Dir = JFactory::getSession()->get('filter_order_Dir');

        $can_be_ordering = array();

        if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering))
        {
            return ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
        }

        return '';
    }

    /**
     * @param array $multi_array
     * @param $sort_key
     * @param int $sort
     * @return array|int
     */
    public function multi_array_sort($multi_array = array(), $sort_key, $sort = SORT_ASC)
    {
        if (is_array($multi_array))
        {
            foreach ($multi_array as $key => $row_array)
            {
                if (is_array($row_array))
                {
                    @$key_array[$key] = $row_array[$sort_key];
                }
                else
                {
                    return -1;
                }
            }
        } else {
            return -1;
        }
        if (!empty($key_array))
            array_multisort($key_array, $sort, $multi_array);
        return $multi_array;
    }

    /**
     * Decrypt once a connection password - if its params->encryptedPw option is true
     *
     * @param   JTable  &FabrikTableConnection  Connection
     *
     * @since   6
     *
     * @return  void
     */
    protected function decryptPw(&$cnn)
    {
        if (isset($cnn->decrypted) && $cnn->decrypted)
        {
            return;
        }

        $crypt = EmundusHelperAccess::getCrypt();
        $params = json_decode($cnn->params);

        if (is_object($params) && $params->encryptedPw == true)
        {
            $cnn->password = $crypt->decrypt($cnn->password);
            $cnn->decrypted = true;
        }
    }

    /**
     * Gets object of available connections
     *
     * @return  array  of connection tables id, description
     */
    public function getConnections($description)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*, id AS value, description AS text')->from('#__fabrik_connections')->where('published = 1 and description like "'.$description.'"');
        $db->setQuery($query);
        $connections = $db->loadObjectList();

        foreach ($connections as &$cnn)
        {
            $this->decryptPw($cnn);
        }

        return $connections;
    }

    /**
     * Get Ametys DB connexion connections
     *
     * @return  object of connection Ametys connexion
     */
    public function getAmetysDBO()
    {
        // Construct the DB connexion to Ametys local DB
        $conn = $this->getConnections('ametys');
        $option = array(); //prevent problems

        $option['driver']   = 'mysql';                // Database driver name
        $option['host']     = $conn[0]->host;         // Database host name
        $option['user']     = $conn[0]->user;         // User for database authentication
        $option['password'] = $conn[0]->password;     // Password for database authentication
        $option['database'] = $conn[0]->database;     // Database name
        $option['prefix']   = '';                     // Database prefix (may be empty)

        $db = JDatabaseDriver::getInstance( $option );

        return $db;
    }

    /**
     * Get Ametys programmes
     *
     * @return  array of all programmes
*     CREATE TABLE IF NOT EXISTS `ODF_export_program` (
*  `id_ODF_export_program` varchar(255) NOT NULL COMMENT 'Identifiant ametys',
*->jos_emundus_setup_programmes.notes  `presentation` mediumtext COMMENT 'Présentation: Présentation de la formation',
*  `objectives` mediumtext COMMENT 'Objectifs: Description objectifs de la formation',
*  `qualification` mediumtext COMMENT 'Savoir faire et compétences: Compétences acquises',
*  `teachingOrganization` mediumtext COMMENT 'Organisation: Programme de la formation',
*  `accessCondition` mediumtext COMMENT 'Conditions d''accès: Description des conditions d''admission',
*  `neededPrerequisite` mediumtext COMMENT 'Pré-requis nécessaires: Description des pré-requis nécessaires',
*  `recommendedPrerequisite` mediumtext COMMENT 'Pré-requis recommandés: Description des pré-requis recommandés pour cette formation',
*  `furtherStudy` mediumtext COMMENT 'Poursuite d''études: Poursuite d''études possible suite à cette formation',
*  `studyAbroad` mediumtext COMMENT 'Poursuite d''études à l''étranger: Poursuite d''études à l''étranger suite à cette formation',
*  `targetGroup` mediumtext COMMENT 'Public cible: Public cible de cette formation',
*  `jobOpportunities` mediumtext COMMENT 'Insertion professionnelle: Insertion professionnelle suite à cette formation',
*  `trainingStrategy` mediumtext COMMENT 'Stages et projets tutorés: Politiques et description des stages',
*  `knowledgeCheck` mediumtext COMMENT 'Contrôle des connaissances: Contrôle des connaissances de la formation',
*  `universalAdjustment` mediumtext COMMENT 'Aménagements particuliers: Aménagements particuliers pour cette formation',
*  `additionalInformations` mediumtext COMMENT 'Informations supplémentaires: Informations supplémentaires concernant cette formation',
*  `reorientation` mediumtext COMMENT 'Passerelles et réorientation: Passerelles et réorientation',
*  `expenses` text COMMENT 'Frais de scolarité: plugin.odf:CONTENT_PROGRAM_EXPENSES_DESC',
*  `code` text COMMENT 'Code DIP: Code DIP',
*->jos_emundus_setup_programmes.code  `cdmCode` text COMMENT 'Identifiant CDM-fr: Ce code sera utilisé lors de l''export CDM-fr comme identifiant de l''élément',
*->jos_emundus_setup_programmes.label  `title` text COMMENT 'Libellé: Libellé de la formation',
*  `mention` text COMMENT 'Mention: Mention',
*  `speciality` text COMMENT 'Spécialité: Spécialité',
*  `educationLevel` varchar(255) DEFAULT NULL COMMENT 'Niveau d''étude visé: Niveau d''étude visé',
*  `degree` varchar(255) DEFAULT NULL COMMENT 'Diplôme: Diplôme',
*->jos_emundus_setup_programmes.url  `programWebSiteUrl` text COMMENT 'Site Web: URL du site du diplôme',
*  `programWebSiteLabel` text COMMENT 'Texte du lien: Texte à afficher pour le lien vers le site web',
*  `successRate` text COMMENT 'Taux de réussite: Taux de réussite',
*->jos_emundus_setup_teaching_unity.ects  `ects` text COMMENT 'ECTS: Crédits ECTS',
*  `educationKind` varchar(255) DEFAULT NULL COMMENT 'Nature: Nature du diplome',
*  `duration` text COMMENT 'Durée: Durée de la formation',
*  `educationLanguage` text COMMENT 'Langage: Langue d''enseignement',
*  `numberOfStudents` text COMMENT 'Effectif: Effectif maximum',
*->jos_emundus_setup_teaching_unity.online  `distanceLearning` varchar(255) DEFAULT NULL COMMENT 'Formation à distance: Cochez cette case si il s''agit d''une formation à distance',
*  `internship` varchar(255) DEFAULT NULL COMMENT 'Stage: Stage',
*  `internshipDuration` text COMMENT 'Durée du stage: Durée du stage (en mois)',
*  `internshipAbroad` varchar(255) DEFAULT NULL COMMENT 'Stage à l''étranger: Stage à l''étranger',
*  `internshipAbroadDuration` text COMMENT 'Durée du stage à l''étranger: Durée du stage à l''étranger (en mois)',
*->jos_emundus_setup_campaign.start_date  `registrationStart` date DEFAULT NULL COMMENT 'Date de début d''inscription: Date de début d''inscription à la formation',
*->jos_emundus_setup_campaign.end_date  `registrationDeadline` date DEFAULT NULL COMMENT 'Date de fin d''inscription: Date de fin d''inscription à la formation',
*->jos_emundus_setup_teaching_unity.date_start  `teachingStart` date DEFAULT NULL COMMENT 'Date de début de la formation: Date de début des cours',
*->jos_emundus_setup_programmes.programmes  `catalog` varchar(255) DEFAULT NULL COMMENT 'Catalogue: Catalogue de la formation',
*  `dc_dc_title` text COMMENT 'Titre: Titre du document',
*  `dc_dc_creator` text COMMENT 'Créateur: Nom de la personne, de l''organisation ou du service à l''origine de la rédaction du document',
*  `dc_dc_description` text COMMENT 'Description: Résumé, table des matières, ou texte libre. ',
*  `dc_dc_publisher` varchar(255) DEFAULT NULL COMMENT 'Éditeur: Nom de la personne, de l''organisation ou du service à l''origine de la publication du document',
*  `dc_dc_contributor` text COMMENT 'Contributeur: Nom d''une personne, d''une organisation ou d''un service qui contribue ou a contribué à l''élaboration du document',
*  `dc_dc_date` date DEFAULT NULL COMMENT 'Date: Date de création ou de la date de publication',
*  `dc_dc_type` varchar(255) DEFAULT NULL COMMENT 'Type: Nature ou genre du contenu (DCMITypes)',
*  `dc_dc_format` text COMMENT 'Format: Format physique ou électronique du document (types MIME)',
*  `dc_dc_identifier` text COMMENT 'Identifiant: Identificateur du document (ex: URI, numéros ISBN)',
*  `dc_dc_source` text COMMENT 'Source: Ressource dont dérive le document',
*  `dc_dc_language` varchar(255) DEFAULT NULL COMMENT 'Langue: Langue du document (code de la langue)',
*  `dc_dc_relation` text COMMENT 'Relation: Lien vers une ressource liée',
*  `dc_dc_coverage` text COMMENT 'Portée du document: Couverture',
*  `dc_dc_rights` varchar(255) DEFAULT NULL COMMENT 'Droits: Droits relatifs à la ressource (copyright, lien vers le détenteur des droits)',
*  `internationalEducation` tinyint(1) DEFAULT NULL COMMENT 'Formation incluant des enseignements non-francophones: Formation incluant des enseignements non-francophones',
*  `content_title` text COMMENT 'Titre du contenu',
*  `content_type` text COMMENT 'Type du contenu',
*  `content_language` text COMMENT 'Langue du contenu',
*  `content_creator` text COMMENT 'Createur du contenu',
*  `content_creationDate` datetime DEFAULT NULL COMMENT 'Date de création du contenu',
*  `content_lastContributor` text COMMENT 'Dernier contributeur du contenu',
*  `content_lastModificationDate` datetime DEFAULT NULL COMMENT 'Date de la dernière modification du contenu',
*  `content_lastValidationDate` datetime DEFAULT NULL COMMENT 'Date de la dernière validation du contenu',
*  `content_lastMajorValidationDate` datetime DEFAULT NULL COMMENT 'Date de la dernière validation majeur du contenu',
*  PRIMARY KEY (`id_ODF_export_program`),
*  KEY `educationLevel` (`educationLevel`),
*  KEY `degree` (`degree`),
*  KEY `educationKind` (`educationKind`),
*  KEY `distanceLearning` (`distanceLearning`),
*  KEY `internship` (`internship`),
*  KEY `internshipAbroad` (`internshipAbroad`),
*  KEY `catalog` (`catalog`),
*  KEY `dc_dc_publisher` (`dc_dc_publisher`),
*  KEY `dc_dc_type` (`dc_dc_type`),
*  KEY `dc_dc_language` (`dc_dc_language`),
*  KEY `dc_dc_rights` (`dc_dc_rights`)
*) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPRESSED COMMENT='Formation: Définit une formation';
     */
    public function getProgrammes()
    {
        $query = 'SELECT p.id_ODF_export_program, p.presentation, p.cdmCode, p.title, p.programWebSiteUrl, p.ects, p.distanceLearning, p.registrationStart, p.registrationDeadline, p.catalog, o.title as organisation, o.codeUAI as organisation_code
                  FROM ODF_export_program p 
                  LEFT JOIN ODF_export_program_meta_orgUnit pmo ON pmo.idUp_ODF_export_program=p.id_ODF_export_program 
                  LEFT JOIN ODF_export_orgunit o ON o.id_ODF_export_orgunit=pmo.orgUnit  
                  WHERE p.content_language like "en"';

        try
        {
            $this->dbAmetys->setQuery($query);
            return $this->dbAmetys->loadAssocList('cdmCode');
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }


    /**
     * Get Ametys Cart
     *
     * @return  array of selected programmes recorded in Ametys cart
     *
     **/

    public function getCart()
    {
        $current_user = JFactory::getUser();

        $query = 'SELECT p.cdmCode, p.id_ODF_export_program, p.title
                    FROM ODFCartProgramsUserPref up, ODF_export_program p
                    WHERE up.login like "'.$current_user->email.'"
                    AND p.id_ODF_export_program = up.contentId';

        try
        {
            $this->dbAmetys->setQuery($query);
            return $this->dbAmetys->loadAssocList();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    //

}
