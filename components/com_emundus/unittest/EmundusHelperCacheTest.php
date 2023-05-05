<?php

use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once (JPATH_SITE . '/components/com_emundus/helpers/cache.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusHelperCacheTest extends TestCase
{
	private $h_cache;

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
	}

	public function testFoo()
	{
		$foo = true;
		$this->assertSame(true, $foo);
		$config = JFactory::getConfig();
		$config->set('cache_handler', 'file');
	}

	/**
	 * @return void
	 * @covers EmundusHelperCache::__construct
	 */
	public function testConstruct()
	{
		$config = JFactory::getConfig();
		$config->set('caching', 0);

		$this->h_cache = new EmundusHelperCache();
		$this->assertSame(false, $this->h_cache->isEnabled(), 'When cache is disabled, isEnabled() should return false');

		$config->set('caching', 1);
		$this->h_cache = new EmundusHelperCache();
		$this->assertSame(true, $this->h_cache->isEnabled(), 'When cache is enabled, isEnabled() should return true');

		// cache isEnabled should be false if context is not component and cache is only conservative
		$this->h_cache = new EmundusHelperCache('mod_emundus_testunit', '', 0, 'module');
		$this->assertSame(false, $this->h_cache->isEnabled(), 'When cache is conservative, isEnabled() should return false if context is not component');

		$config->set('caching', 2); // cache is now progressive
		$this->h_cache = new EmundusHelperCache('mod_emundus_testunit', '', 0, 'module');
		$this->assertSame(true, $this->h_cache->isEnabled(), 'When cache is progressive, isEnabled() should return true even if context is not component');
	}

	/**
	 * @return void
	 * @covers EmundusHelperCache::get
	 */
	public function testGetter() {
		$config = JFactory::getConfig();
		$config->set('caching', 0);

		$this->h_cache = new EmundusHelperCache();
		$this->assertSame(null, $this->h_cache->get('foo'), 'When cache is disabled, get() should return null');

		$config->set('caching', 1);
		$this->h_cache = new EmundusHelperCache();
		$this->h_cache->clean();
		$this->assertSame(false, $this->h_cache->get('foo'), 'When cache is enabled, get() should return false if key is not set');

		$this->h_cache->set('foo', 'bar');
		$this->assertSame('bar', $this->h_cache->get('foo'), 'When cache is enabled, get() should return value if key is set');
	}

	/**
	 * @return void
	 * @covers EmundusHelperCache::set
	 */
	public function testSetter() {
		$config = JFactory::getConfig();
		$config->set('caching', 0);

		$this->h_cache = new EmundusHelperCache();
		$this->assertSame(false, $this->h_cache->set('foo', 'bar'), 'When cache is disabled, set() should return false');

		$config->set('caching', 1);
		$this->h_cache = new EmundusHelperCache();
		$this->assertSame(true, $this->h_cache->set('foo', 'bar'), 'When cache is enabled, set() should return true');
		$this->assertSame('bar', $this->h_cache->get('foo'), 'When cache is enabled, set() should set value for key');
	}

	/**
	 * @return void
	 * @covers EmundusHelperCache::clean
	 */
	public function testClean() {
		$config = JFactory::getConfig();
		$config->set('caching', 1);

		$this->h_cache = new EmundusHelperCache();
		$this->h_cache->set('foo', 'bar');
		$this->assertSame('bar', $this->h_cache->get('foo'));

		$this->h_cache->clean();
		$this->assertSame(false, $this->h_cache->get('foo'), 'clean() should remove all keys');
	}
}