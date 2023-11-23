<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use Joomla\CMS\Factory;
use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');

include_once(JPATH_ROOT . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_ROOT . '/components/com_emundus/models/gallery.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

class EmundusModelGalleryTest extends TestCase
{
	private $app;
	private $db;
    private $m_gallery;

	private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
		
	    $this->app = Factory::getApplication();
		$this->db = Factory::getDbo();
		
	    $this->h_sample = new EmundusUnittestHelperSamples;
		
        $this->m_gallery = new EmundusModelGallery();
    }

	public function testCreateGallery()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];

		// 1. En tant que gestionnaire je peux créer un catalogue
		$gallery_id = $this->m_gallery->createGallery($data);
		$this->assertNotEmpty($gallery_id);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		// 2. En tant que développeur je veux qu'une vue SQL soit créée pour chaque catalogue
		$query = 'SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = "' . $this->app->get('db') . '" AND `TABLE_TYPE` = "VIEW"';
		$this->db->setQuery($query);
		$views = $this->db->loadColumn();
		$this->assertContains('jos_emundus_gallery_' . $gallery->list_id, $views);

		// 3. En tant que développeur je veux m'assurer que la liste Fabrik a bien été créee
		$query = $this->db->getQuery(true);
		$query
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__fabrik_lists'))
			->where($this->db->quoteName('id') . ' = ' . $this->db->quote($gallery->list_id));
		$this->db->setQuery($query);
		$this->assertNotEmpty($this->db->loadResult());

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testDeleteGallery()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		// 1. En tant que gestionnaire je veux supprimer un catalogue
		$this->assertTrue($this->m_gallery->deleteGallery($gallery->id));

		// 2. En tant que développeur je veux m'assurer que la vue SQL a bien été supprimée
		$query = 'SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = "' . $this->app->get('db') . '" AND `TABLE_TYPE` = "VIEW"';
		$this->db->setQuery($query);
		$views = $this->db->loadColumn();
		$this->assertNotContains('jos_emundus_gallery_' . $gallery->list_id, $views);

		// 3. En tant que développeur je veux m'assurer que la liste Fabrik a bien été supprimée
		$query = $this->db->getQuery(true);
		$query
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__fabrik_lists'))
			->where($this->db->quoteName('id') . ' = ' . $this->db->quote($gallery->list_id));
		$this->db->setQuery($query);
		$this->assertEmpty($this->db->loadResult());

		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testGetGalleries()
	{
		// 1. Au déploiement, la liste des catalogues est vide
		$this->assertEmpty($this->m_gallery->getGalleries()['datas']);

		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);
		
		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		// 2. En tant que gestionnaire je veux pouvoir récupérer la liste des catalogues
		$this->assertNotEmpty($this->m_gallery->getGalleries()['datas']);

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testGetGalleryById()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		// 1. En tant que gestionnaire je veux accéder aux détails d'un catalogue
		$gallery = $this->m_gallery->getGalleryById($gallery_id);
		$this->assertEquals($gallery_id, $gallery->id);

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testGetGalleryByList()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		// 1. En tant que développeur je veux pouvoir récupérer les données d'un catalogue via l'identifiant de la liste Fabrik
		$gallery = $this->m_gallery->getGalleryByList($gallery->list_id);
		$this->assertEquals($gallery_id, $gallery->id);

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testGetElements()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		// 1. En tant que gestionnaire je veux pouvoir visualiser les élements de formulaire que je peux associer
		$elements = $this->m_gallery->getElements($gallery->campaign_id,$gallery->list_id);
		$this->assertNotEmpty($elements);

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testGetAttachments()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		// 1. En tant que gestionnaire je veux pouvoir visualiser les documents que je peux associer
		$attachments = $this->m_gallery->getAttachments($gallery->campaign_id);
		$this->assertNotEmpty($attachments);

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testUpdateAttribute()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		$elements = $this->m_gallery->getElements($gallery->campaign_id,$gallery->list_id);

		// 1. En tant que gestionnaire je veux pouvoir associer un élément au titre des vignettes de mon catalogue
		$this->assertTrue($this->m_gallery->updateAttribute($gallery->id, 'title', $elements[0]['elements'][0]->fullname));

		// 2. Par défaut mon catalogue n'est pas ouvert au vote
		$this->assertEquals(0,$gallery->is_voting);

		// 3. En tant que gestionnaire je veux ouvrir mon catalogue au vote
		$this->assertTrue($this->m_gallery->updateAttribute($gallery->id, 'is_voting', 1));

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testUpdateList()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		// 1. En tant que gestionnaire je veux modifier le nom de mon catalogue
		$this->assertTrue($this->m_gallery->updateList($gallery->list_id, 'label', 'Catalogue de test 2'));

		// 2. En tant que développeur je ne veux pas qu'il puisse modifier un autre attribut que label et introduction
		$this->assertFalse($this->m_gallery->updateList($gallery->list_id, 'params', '{}'));

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testEditPrefilter()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		// 1. En tant que gestionnaire je veux afficher seulement les dossiers au statut envoyé sur le catalogue
		$this->assertTrue($this->m_gallery->editPrefilter($gallery->list_id, 1));

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testAddTab()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		// 1. En tant que gestionnaire je veux Ajouter un onglet dans la vue détails de mon catalogue
		$tab_id = $this->m_gallery->addTab($gallery->id, 'Onglet N°2');
		$this->assertNotEmpty($tab_id);

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testAddField()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		$elements = $this->m_gallery->getElements($gallery->campaign_id,$gallery->list_id);

		$tab_id = $this->m_gallery->addTab($gallery->id, 'Onglet N°2');

		// 1. En tant que gestionnaire je veux ajouter un élément dans l'onglet de mon catalogue
		$this->assertTrue($this->m_gallery->addField($tab_id, $elements[0]['elements'][0]->fullname));

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testRemoveField()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		$elements = $this->m_gallery->getElements($gallery->campaign_id,$gallery->list_id);

		$tab_id = $this->m_gallery->addTab($gallery->id, 'Onglet N°2');
		$this->m_gallery->addField($tab_id, $elements[0]['elements'][0]->fullname);

		// 1. En tant que gestionnaire je veux ajouter un élément dans l'onglet de mon catalogue
		$this->assertTrue($this->m_gallery->removeField($tab_id, $elements[0]['elements'][0]->fullname));

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testUpdateTabTitle()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);


		$tab_id = $this->m_gallery->addTab($gallery->id, 'Onglet N°2');

		// 1. En tant que gestionnaire je veux modifier le titre d'un onglet de mon catalogue
		$this->assertTrue($this->m_gallery->updateTabTitle($tab_id, 'Onglet N°3'));

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testDeleteTab()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];
		$gallery_id = $this->m_gallery->createGallery($data);

		$gallery = $this->m_gallery->getGalleryById($gallery_id);
		$tab_id = $this->m_gallery->addTab($gallery->id, 'Onglet N°2');

		// 1. En tant que gestionnaire je veux supprimer un onglet de mon catalogue
		$this->assertTrue($this->m_gallery->deleteTab($tab_id));

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}

	public function testUpdateFieldsOrder()
	{
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);

		$data = [
			'gallery_name' => 'Catalogue de test',
			'campaign_id' => $campaign
		];

		$gallery_id = $this->m_gallery->createGallery($data);
		$gallery = $this->m_gallery->getGalleryById($gallery_id);

		$tab_id = $this->m_gallery->addTab($gallery->id, 'Onglet N°2');

		$elements = $this->m_gallery->getElements($gallery->campaign_id,$gallery->list_id);

		$this->m_gallery->addField($tab_id, $elements[0]['elements'][0]->fullname);
		$this->m_gallery->addField($tab_id, $elements[0]['elements'][1]->fullname);

		// 1. En tant que gestionnaire je veux modifier l'ordre des éléments dans un onglet de mon catalogue
		$this->assertTrue($this->m_gallery->updateFieldsOrder($tab_id, implode(',',[$elements[0]['elements'][1]->fullname, $elements[0]['elements'][0]->fullname])));

		$this->m_gallery->deleteGallery($gallery_id);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);
	}
}
