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
}
