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
include_once(JPATH_SITE.'/components/com_emundus/models/campaign.php');
include_once(JPATH_SITE.'/components/com_emundus/models/programme.php');
include_once(JPATH_SITE.'/components/com_emundus/models/profile.php');
include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');

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
    private $m_programme;
    private $m_profile;
    private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_campaign = new EmundusModelCampaign;
        $this->m_programme = new EmundusModelProgramme;
        $this->m_profile = new EmundusModelProfile;
        $this->h_sample = new EmundusUnittestHelperSamples;
    }

	public function testCampaignWorkflowDatabase() {
		/**
		 * Table emundus_campaign_workflow should exists
		 */

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__emundus_campaign_workflow'));

		try {
			$db->setQuery($query);
			$db->loadObjectList();
			$table_exists = true;
		} catch(Exception $e) {
			$table_exists = false;
		}

		$this->assertTrue($table_exists, 'Table #__emundus_campaign_workflow should exists');

		$query->clear()
			->select('*')
			->from($db->quoteName('#__emundus_campaign_workflow'))
			->where($db->quoteName('display_preliminary_documents') . ' IS NULL')
			->orWhere($db->quoteName('specific_documents') . ' IS NULL');

		try {
			$db->setQuery($query);
			$db->loadObjectList();
			$columns_exists = true;
		} catch(Exception $e) {
			$columns_exists = false;
		}

		$this->assertTrue($columns_exists, 'Table #__emundus_campaign_workflow should have 2 new columns display_preliminary_documents and specific_documents');

		$query->clear()
			->select('*')
			->from($db->quoteName('#__emundus_campaign_workflow_repeat_documents'));

		try {
			$db->setQuery($query);
			$db->loadObjectList();
			$table_exists = true;
		} catch(Exception $e) {
			$table_exists = false;
		}

		$this->assertTrue($table_exists, 'Table #__emundus_campaign_workflow_repeat_documents should exists');
	}

    public function createUnitTestCampaign($program)
    {
        $campaign_id = 0;

        if (!empty($program)) {
            $start_date = new DateTime();
            $start_date->modify('-1 day');
            $end_date = new DateTime();
            $end_date->modify('+1 year');
            $campaign_id = $this->m_campaign->createCampaign([
                'label' =>  json_encode(['fr' => 'Campagne test unitaire', 'en' => 'Campagne test unitaire']),
                'description' => 'Lorem ipsum',
                'short_description' => 'Lorem ipsum',
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'profile_id' => 9,
                'training' => $program['programme_code'],
                'year' => '2022-2023',
                'published' => 1,
                'is_limited' => 0
            ]);
        }

        return $campaign_id;
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

        $created_program = $this->m_programme->addProgram(['label' => 'Programme Test Unitaire']);
        $this->assertNotEmpty($created_program);

        if (!empty($created_program)) {
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
                'training' => $created_program['programme_code'],
                'year' => '2022-2023',
                'published' => 1,
                'is_limited' => 0
            ];

            $new_campaign_id = $this->m_campaign->createCampaign($inserting_datas);
            $this->assertGreaterThan(0, $new_campaign_id, 'Assert campaign creation works.');

            $program = $this->m_campaign->getProgrammeByCampaignID($new_campaign_id);
            $this->assertNotEmpty($program, 'Getting program from campaign id works');
            $this->assertSame($program['code'], $created_program['programme_code'], 'The program code used in creation is retrieved when getting program by the new campaign id');

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


    public function testCreateWorkflow()
    {
        $this->m_campaign->deleteWorkflows();
        $workflow_on_all = $this->m_campaign->createWorkflow(9, [0], 1, null, []);
        $this->assertNotEmpty($workflow_on_all);

        $this->assertFalse($this->m_campaign->canCreateWorkflow(9, [0], []), 'On ne devrait pas pouvoir créer un workflow sur le même statut.');
        $this->assertTrue($this->m_campaign->canCreateWorkflow(9, [1], []), 'On devrait pouvoir créer un workflow sur le même profile, mais un statut différent.');
        $this->assertTrue($this->m_campaign->canCreateWorkflow(9, [0], ['campaigns' => [1]]), 'On devrait pouvoir créer un workflow sur le même statut mais en spécifiant une campagne.');
        $this->assertTrue($this->m_campaign->canCreateWorkflow(9, [0], ['programs' => ['program-1']]), 'On devrait pouvoir créer un workflow sur le même statut mais en spécifiant une campagne.');

        $program = $this->m_programme->addProgram(['label' => 'Programme Test Unitaire']);
        $workflow_on_program = $this->m_campaign->createWorkflow(9, [0], 1, null, ['programs' => [$program['programme_code']]]);
        $this->assertNotEmpty($workflow_on_program);
        $this->assertFalse($this->m_campaign->canCreateWorkflow(9, [0], ['programs' => ['program-1', $program['programme_code']]]), 'On ne devrait plus pouvoir créer un workflow sur le même statut et en spécifiant un progamme commun.');

        $new_campaign_id = $this->createUnitTestCampaign($program);

        if (!empty($new_campaign_id)) {
            $this->assertTrue($this->m_campaign->canCreateWorkflow(9, [0], ['campaigns' => [$new_campaign_id]]), 'On devrait toujours pouvoir créer un workflow sur le même statut mais en spécifiant une campagne.');

            $workflow_on_campaign = $this->m_campaign->createWorkflow(9, [0], 1, null, ['campaigns' => [$new_campaign_id]]);
            $this->assertNotEmpty($workflow_on_campaign);
            $this->assertFalse($this->m_campaign->canCreateWorkflow(9, [0], ['campaigns' => [12, $new_campaign_id, 15]]), 'On ne devrait plus pouvoir créer un workflow sur le même statut-campagne.');
            $this->assertFalse($this->m_campaign->canCreateWorkflow(9, [0], ['programs' => ['test-emundus'], 'campaigns' => [$new_campaign_id]]), 'On ne devrait plus pouvoir créer un workflow sur le même statut-campagne. Même test avec des données de programme.');
        } else {
            JLog:add('Warning, test canCreateWorkflow on campaign has not been launched', JLog::WARNING, 'com_emundus.unittest');
        }
    }

    public function testDeleteWorkflow()
    {
        $this->assertTrue($this->m_campaign->deleteWorkflows(), 'La suppression de workflow fonctionne');
    }

    public function testGetCurrentCampaignWorkflow()
    {
        $this->m_campaign->deleteWorkflows();
        $program = $this->m_programme->addProgram(['label' => 'Programme Test Unitaire']);
        $this->assertNotEmpty($program, 'La création de programme depuis un label fonctionne');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (!empty($program['programme_code'])) {
            $new_campaign_id = $this->createUnitTestCampaign($program);

            $this->assertGreaterThan(0, $new_campaign_id);
            if ($new_campaign_id) {
                $user_id = $this->h_sample->createSampleUser(9, 'user.test.emundus_' . rand() . '@emundus.fr');
                $this->assertGreaterThan(0, $user_id);
                $fnum = $this->h_sample->createSampleFile($new_campaign_id, $user_id);
                $this->assertNotEmpty($fnum, 'La création de dossier test fonctionne');

                $workflow_on_all = $this->m_campaign->createWorkflow(9, [0], 1, null, []);
                $current_file_workflow = $this->m_campaign->getCurrentCampaignWorkflow($fnum);
				$this->assertNotNull($current_file_workflow, 'La phase courante doit être non nulle.');
                $this->assertSame(intval($workflow_on_all), intval($current_file_workflow->id), 'Le dossier est impacté par le workflow qui n\'a ni campagne ni programme par défaut, mais est sur le même statut.');

                $workflow_on_program = $this->m_campaign->createWorkflow(9, [0], 1, null, ['programs' => [$program['programme_code']]]);
                $current_file_workflow = $this->m_campaign->getCurrentCampaignWorkflow($fnum);
                $this->assertSame(intval($workflow_on_program), intval($current_file_workflow->id), 'Le dossier est impacté par le workflow qui a un programme et un statut commun.');

                $workflow_on_campaign = $this->m_campaign->createWorkflow(9, [0], 1, null, ['campaigns' => [$new_campaign_id]]);
                $current_file_workflow = $this->m_campaign->getCurrentCampaignWorkflow($fnum);
                $this->assertSame(intval($workflow_on_campaign), intval($current_file_workflow->id), 'Le dossier est impacté par le workflow qui a une campagne et un statut commun.');

                $profile = $this->m_profile->getProfileByFnum($fnum);
                $this->assertSame(intval($current_file_workflow->profile), intval($profile), 'La récupération de profile prend en compte le workflow');
                $profileByStatus = $this->m_profile->getProfileByStatus($fnum);
                $this->assertSame(intval($current_file_workflow->profile), intval($profileByStatus['profile']));

                $new_workflow_id = $this->m_campaign->createWorkflow(9, [1], 2, null, ['campaigns' => [$new_campaign_id]]);
                $current_file_workflow = $this->m_campaign->getCurrentCampaignWorkflow($fnum);
                $this->assertNotSame(intval($new_workflow_id), intval($current_file_workflow->id), 'Mon dossier au statut Brouillon n\'est pas impacté par la phase sur la même campagne mais sur le statut Envoyé');

                $query->clear()
                    ->update('#__emundus_campaign_candidature')
                    ->set('status = 1' )
                    ->where('fnum LIKE ' . $db->quote($fnum));

                $db->setQuery($query);
                $db->execute();

                $current_file_workflow = $this->m_campaign->getCurrentCampaignWorkflow($fnum, 'Mon dossier au statut 1 récupère le workflow associé à sa campagne');
                $this->assertSame(intval($new_workflow_id), intval($current_file_workflow->id));

                $this->assertTrue($this->m_campaign->deleteWorkflows(), 'La suppression de workflow fonctionne');

				$this->assertObjectHasAttribute('display_preliminary_documents', $current_file_workflow, 'Le workflow contient un attribut "Afficher les Documents à télécharger"');
				$this->assertSame('0', $current_file_workflow->display_preliminary_documents, 'Le workflow contient un attribut "Afficher les Documents à télécharger" à 0 par défaut');

				$this->assertObjectHasAttribute('specific_documents', $current_file_workflow, 'Le workflow contient un attribut "Documents spécifique"');
				$this->assertSame('0', $current_file_workflow->specific_documents, 'Le workflow contient un attribut "Documents spécifique" à 0 par défaut');

				$this->assertObjectHasAttribute('documents', $current_file_workflow, 'Le workflow contient des documents');
				$this->assertSame([], $current_file_workflow->documents, 'Le workflow contient un tableau vide par défaut');
			}
        }
    }

    function testGetAllCampaignWorkflows()
    {
        $this->m_campaign->deleteWorkflows();
        $this->assertEmpty($this->m_campaign->getAllCampaignWorkflows(0), 'Pas de workflow renvoyés si la campagne n\'existe pas.');

        $program = $this->m_programme->addProgram(['label' => 'Programme Test Unitaire']);
        $new_campaign_id = $this->createUnitTestCampaign($program);
        $this->assertEmpty($this->m_campaign->getAllCampaignWorkflows($new_campaign_id), 'Pas encore de workflow sur une nouvelle campagne, nouveau programme');

        $workflow_on_program = $this->m_campaign->createWorkflow(9, [0], 1, null, ['programs' => [$program['programme_code']]]);
        $this->assertSame(1, sizeof($this->m_campaign->getAllCampaignWorkflows($new_campaign_id)), 'getAllCampaignWorkflows renvoie 1 workflow à la création du workflow sur le programme de la campagne');

        $workflow_on_campaign_same_state = $this->m_campaign->createWorkflow(9, [0], 1, null, ['campaigns' => [$new_campaign_id]]);
        $this->assertSame(1, sizeof($this->m_campaign->getAllCampaignWorkflows($new_campaign_id)), 'getAllCampaignWorkflows renvoie 1 seul workflow à la création du workflow sur la campagne avec le même statut d\'entrée que le workflow précédent');

        $this->m_campaign->createWorkflow(9, [1], 1, null, ['programs' => [$program['programme_code']]]);
        $this->assertSame(2,  sizeof($this->m_campaign->getAllCampaignWorkflows($new_campaign_id)));
    }

	function testpinCampaign() {
		$pinned = $this->m_campaign->pinCampaign(9999);
		$this->assertFalse($pinned, 'La campagne 9999 n\'existe pas, donc on ne peut pas la mettre en avant');

		$program = $this->m_programme->addProgram(['label' => 'Programme PIN CAMPAIGN']);
		$campaign_id = $this->createUnitTestCampaign($program);
		$pinned = $this->m_campaign->pinCampaign($campaign_id);
		$this->assertTrue($pinned, 'La campagne existe, on peut la mettre en avant');

		$campaign = $this->m_campaign->getCampaignByID($campaign_id);
		$this->assertSame('1', $campaign['pinned'], 'La campagne est bien mise en avant');

		$new_campaign_id = $this->createUnitTestCampaign($program);
		$pinned = $this->m_campaign->pinCampaign($new_campaign_id);

		// assert old campaign is not pinned anymore
		$campaign = $this->m_campaign->getCampaignByID($campaign_id);
		$this->assertSame('0', $campaign['pinned'], 'La campagne n\'est plus mise en avant');

		// assert new campaign is pinned
		$campaign = $this->m_campaign->getCampaignByID($new_campaign_id);
		$this->assertSame('1', $campaign['pinned'], 'La nouvelle campagne est mise en avant');

		// on duplicate campaign, pinned is not duplicated
		$duplicated = $this->m_campaign->duplicateCampaign($new_campaign_id);
		$this->assertTrue($duplicated, 'La campagne a bien été dupliquée');

		// get the last campaign
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__emundus_setup_campaigns')
			->order('id DESC')
			->setLimit(1);
		$db->setQuery($query);
		$last_campaign_id = $db->loadResult();

		$campaign = $this->m_campaign->getCampaignByID($last_campaign_id);
		$this->assertEmpty($campaign['pinned'], 'La nouvelle campagne dupliquée n\'est pas mise en avant');
	}

	function testunpinCampaign() {
		$unpinned = $this->m_campaign->unpinCampaign(0);
		$this->assertFalse($unpinned, 'La campagne 0 n\'existe pas, donc on ne peut pas la retirer de la mise en avant');

		$program = $this->m_programme->addProgram(['label' => 'Programme UNPIN CAMPAIGN']);
		$campaign_id = $this->createUnitTestCampaign($program);

		$pinned = $this->m_campaign->pinCampaign($campaign_id);
		$unpinned = $this->m_campaign->unpinCampaign($campaign_id);

		$this->assertTrue($unpinned, 'La campagne existe, on peut la retirer de la mise en avant');

		$campaign = $this->m_campaign->getCampaignByID($campaign_id);
		$this->assertSame('0', $campaign['pinned'], 'La campagne n\'est plus mise en avant');

		$this->assertFalse($this->m_campaign->unpinCampaign(['svsfg', 'dsgdfg', 'dsg']), 'Un tableau mal formé ne peut pas être passé en paramètre');
	}

	function testeditDocumentDropfile() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->insert($db->quoteName('#__dropfiles_files'))
			->columns($db->quoteName(['title', 'ext', 'file', 'state']))
			->values(" 'test', 'pdf', 'test.pdf', 1");

		$db->setQuery($query);
		$db->execute();
		$document_id = $db->insertid();

		$updated = $this->m_campaign->editDocumentDropfile($document_id, '');
		$this->assertFalse($updated, 'Le nom du document ne peut pas être vide');

		$too_long_name = 'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest';
		$updated = $this->m_campaign->editDocumentDropfile($document_id, $too_long_name);

		$this->assertTrue($updated, 'Le nom du document a été mis à jour');

		$updated_document = $this->m_campaign->getDropfileDocument($document_id);
		$this->assertSame(200, strlen($updated_document->title), 'Le nom du document a été tronqué à 200 caractères');
	}

	function testduplicateCampaign()
	{
		$program = $this->m_programme->addProgram(['label' => 'Programme DUPLICATE CAMPAIGN']);
		$campaign_id = $this->createUnitTestCampaign($program);

		$duplicated = $this->m_campaign->duplicateCampaign($campaign_id);
		$this->assertTrue($duplicated, 'La campagne a bien été dupliquée');

		$duplicated = $this->m_campaign->duplicateCampaign(0);
		$this->assertFalse($duplicated, 'La campagne 0 n\'existe pas, donc on ne peut pas la dupliquer');
	}
}
