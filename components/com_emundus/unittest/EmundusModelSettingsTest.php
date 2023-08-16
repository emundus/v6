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
include_once ( JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once ( JPATH_SITE . '/components/com_emundus/helpers/cache.php');
include_once ( JPATH_SITE . '/components/com_emundus/models/settings.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelSettingsTest extends TestCase
{
    private $m_settings;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_settings = new EmundusModelsettings;
	    $config = JFactory::getConfig();
	    $config->set('cache_handler', 'file');
    }

	public function testFoo()
	{
		$foo = true;
		$this->assertSame(true, $foo);
	}

	/**
	 * @return void
	 * @covers EmundusModelSettings::getOnboardingLists
	 */
	public function testgetOnboardingLists() {
		$config = JFactory::getConfig();
		$config->set('caching', 1);

		$lists = $this->m_settings->getOnboardingLists();
		$this->assertNotEmpty($lists);

		// lists should contain at least 3 entries (campaigns, forms and emails)
		$this->assertGreaterThanOrEqual(3, count($lists));

		// if cache is enabled, lists should be cached
		$h_cache = new EmundusHelperCache();
		$lists_cached = $h_cache->get('onboarding_lists');
		$this->assertNotEmpty($lists_cached);
		$this->assertSame($lists, $lists_cached);
	}

	public function testgetStatus() {
		$all_status = $this->m_settings->getStatus();

		$this->assertIsArray($all_status);
		$this->assertNotEmpty($all_status, 'La récupération des status fonctionne');
	}

	public function testcreateStatus() {
		$status = $this->m_settings->createStatus();
		$this->assertNotNull($status, 'La création d\'un status fonctionne');

		$this->assertGreaterThan(0, $status->id, 'La création d\'un status fonctionne');
	}

	public function testgetTags() {
		$all_tags = $this->m_settings->getTags();

		$this->assertIsArray($all_tags);
		$this->assertNotEmpty($all_tags, 'La récupération des étiquettes fonctionne');
	}

	public function testcreateTag() {
		$tag = $this->m_settings->createTag();
		$this->assertNotNull($tag, 'La création d\'une étiquette fonctionne');

		$this->assertGreaterThan(0, $tag->id, 'La création d\'une étiquette fonctionne');
		$this->assertSame($tag->label, 'Nouvelle étiquette', 'Le tag a un titre par défaut');
	}

	public function testupdateTags() {
		$tag = $this->m_settings->createTag();
		$label = 'Nouvelle étiquette modifiée';
		$colors = $this->m_settings->getColorClasses();
		$color = current($colors);
		$color_key = array_search($color, $colors);

		$update = $this->m_settings->updateTags($tag->id, $label, $color);
		$this->assertTrue($update, 'La modification d\'une étiquette fonctionne');

		$tags = $this->m_settings->getTags();
		$tags_found = array_filter($tags, function($t) use ($tag) {
			return $t->id == $tag->id;
		});
		$tag_found = current($tags_found);

		$this->assertSame($label, $tag_found->label, 'Le titre de l\'étiquette a été modifié');
		$this->assertSame('label-' . $color_key, $tag_found->class, 'Le titre de l\'étiquette a été modifié');
	}

	public function testdeleteTag() {
		$tag = $this->m_settings->createTag();
		$delete = $this->m_settings->deleteTag($tag->id);
		$this->assertTrue($delete, 'La suppression d\'une étiquette fonctionne');

		$delete = $this->m_settings->deleteTag(0);
		$this->assertFalse($delete, 'On ne peut pas supprimer une étiquette qui n\'existe pas');
	}

	public function testgetHomeArticle() {
		$article = $this->m_settings->getHomeArticle();

		$this->assertNotNull($article, 'La récupération de l\'article d\'accueil fonctionne');
	}

	public function testgetRgpdArticles() {
		$articles = $this->m_settings->getRgpdArticles();

		$this->assertNotEmpty($articles, 'La récupération des articles RGPD fonctionne');

		$this->assertSame(5, count($articles), 'Je récupère 5 articles RGPD');

		if(empty($articles[0]->id)){
			$this->assertNotEmpty($articles[0]->alias, 'Si le paramètre du module n\'est pas défini on récupère un alias par défaut');
		}
	}
}
