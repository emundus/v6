<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');
include_once (JPATH_SITE . '/components/com_emundus/models/campaign.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelCampaignTest extends TestCase
{
    private $m_campaign;
    private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_campaign = new EmundusModelCampaign;
        $this->h_sample = new EmundusUnittestHelperSamples;
    }


    public function testCreateDocument()
    {
        $document = [
            'name' => [
                'fr' => ''
            ],
        ];
        $types = [''];

        $created = $this->m_campaign->createDocument($document, $types, null, 9);
        $this->assertFalse($created['status'], 'Assert impossible to create document with empty name');

        $document['name']['fr'] = 'Test';
        $created = $this->m_campaign->createDocument($document, $types, null, 9);
        $this->assertFalse($created['status'], 'Assert impossible to create document with empty types');
    }

    public function testCreateCampaign()
    {
        $new_campaign_id = $this->m_campaign->createCampaign([]);
        $this->assertEmpty($new_campaign_id, 'Assert can not create campaign without data');

        $new_campaign_id = $this->m_campaign->createCampaign(['limit_status' => 1, 'profile_id' => 9]);
        $this->assertEmpty($new_campaign_id, 'Assert can not create campaign without label');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('code')
            ->from($db->quoteName('#__emundus_setup_programmes'));
        $db->setQuery($query);
        $programmes = $db->loadColumn();

        if (!empty($programmes)) {
            $start_date = new DateTime();
            $start_date->modify('-1 day');

            $end_date = new DateTime();
            $end_date->modify('+1 year');

            $inserting_datas = [
                'label' =>  json_encode(['fr' => 'Campagne test unitaire', 'en' => 'Campagne test unitaire']),
                'description' => 'Lorem ipsum',
                'short_description' => 'Lorem ipsum',
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'profile_id' => 9,
                'training' => $programmes[0],
                'year' => '2022-2023',
                'published' => 1
            ];

            $new_campaign_id = $this->m_campaign->createCampaign($inserting_datas);
            $this->assertGreaterThan(0, $new_campaign_id, 'Assert campaign creation works.');

            $program = $this->m_campaign->getProgrammeByCampaignID($new_campaign_id);
            $this->assertNotEmpty($program, 'Getting program from campaign id works');
            $this->assertSame($program['code'], $programmes[0], 'The program code used in creation is retrieved when getting program by the new campaign id');

            $program_by_training = $this->m_campaign->getProgrammeByTraining($program['code']);
            $this->assertNotEmpty($program_by_training->id, 'Assert getting program by his training code works');

            $campaigns_by_program = $this->m_campaign->getCampaignsByProgramId($program_by_training->id);
            $campaign_ids_by_program = [];
            foreach ($campaigns_by_program as $campaign) {
                $campaign_ids_by_program[] = $campaign->id;
            }
            $this->assertTrue(in_array($new_campaign_id, $campaign_ids_by_program), 'Assert campaign is found in getCampaignsByProgramId function');

            $this->assertTrue($this->m_campaign->unpublishCampaign([$new_campaign_id]), 'Assert unpublish campaign works');
            $this->assertTrue($this->m_campaign->publishCampaign([$new_campaign_id]), 'Assert publish campaign works');
            $this->assertTrue($this->m_campaign->pinCampaign($new_campaign_id), 'Assert pin campaign works properly');

            $deleted = $this->m_campaign->deleteCampaign([$new_campaign_id]);
            $this->assertTrue($deleted, 'Campaign deletion works properly');
        }
    }

    public function testUpdateCampaign()
    {
        $updated = $this->m_campaign->updateCampaign([], 1);
        $this->assertFalse($updated, 'Update campaign with empty data does nothing');

        $updated = $this->m_campaign->updateCampaign(['label' => ['fr' => 'Mise à jour de campagne TU', 'en' => 'Mise à jour de campagne TU']], 0);
        $this->assertFalse($updated, 'Update campaign with empty campaign_id does nothing');

        $updated = $this->m_campaign->updateCampaign(['start_date' => null], 0);
        $this->assertFalse($updated, 'Update campaign with empty data start_date stops the update');

        $updated = $this->m_campaign->updateCampaign(['end_date' => null], 0);
        $this->assertFalse($updated, 'Update campaign with empty data end_date stops the update');
    }

    public function testGetAllCampaigns()
    {
        $campaigns = $this->m_campaign->getAllCampaigns();
        $this->assertIsArray($campaigns, 'La fonction de récupération des campagnes renvoie toujours un tableau');
    }

    public function testGetProgrammeByTraining()
    {
        $progam = $this->m_campaign->getProgrammeByTraining('');
        $this->assertEmpty($progam, 'Get programme by training without param returns null');
    }

    public function testGetCurrentCampaignWorkflow()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('code')
            ->from($db->quoteName('#__emundus_setup_programmes'));
        $db->setQuery($query);
        $programmes = $db->loadColumn();

        if (!empty($programmes)) {
            $start_date = new DateTime();
            $start_date->modify('-1 day');

            $end_date = new DateTime();
            $end_date->modify('+1 year');

            $new_campaign_id = $this->m_campaign->createCampaign([
                'label' =>  json_encode(['fr' => 'Campagne test unitaire', 'en' => 'Campagne test unitaire']),
                'description' => 'Lorem ipsum',
                'short_description' => 'Lorem ipsum',
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'profile_id' => 9,
                'training' => $programmes[0],
                'year' => '2022-2023',
                'published' => 1
            ]);

            if ($new_campaign_id) {
                $user_id = $this->h_sample->createSampleUser();

                $fnum = $this->h_sample->createSampleFile($new_campaign_id, $user_id);

                $new_workflow_id = $this->m_campaign->createWorkflow(9, [1], 2, null, ['campaigns' => [$new_campaign_id]]);
                $this->assertEmpty($this->m_campaign->getCurrentCampaignWorkflow($fnum), 'Mon dossier au statut Brouillon n\'est pas impacté par la phase sur le statut envoyé');

                $query->clear()
                    ->update('#__emundus_campaign_candidature')
                    ->set('status = 1' )
                    ->where('fnum LIKE ' . $db->quote($fnum));

                $db->setQuery($query);
                $db->execute();

                $current_file_workflow = $this->m_campaign->getCurrentCampaignWorkflow($fnum);
                $this->assertSame($current_file_workflow->id, $new_workflow_id);
            }
        }
    }
}
