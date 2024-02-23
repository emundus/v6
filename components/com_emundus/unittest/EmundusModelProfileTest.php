<?php
use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once (__DIR__ . '/../models/profile.php');
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');
include_once (JPATH_SITE . '/components/com_emundus/models/formbuilder.php');
include_once(JPATH_SITE.'/components/com_emundus/helpers/files.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

// standard class name test syntax: <class_name>Test extends TestCase

class EmundusModelProfileTest extends TestCase
{
    private $m_profile;
    private $m_formbuilder;
	private $h_sample;

	private $db;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_profile = new EmundusModelProfile;
        $this->m_formbuilder = new EmundusModelFormbuilder;
	    $this->h_sample = new EmundusUnittestHelperSamples;
	    $this->db = JFactory::getDbo();
    }

    // simple test case (example)
    public function testFoo() {
        $foo = true;
        $this->assertSame(true, $foo);
    }

	/**
	 * @covers EmundusModelProfile::getApplicantFnums
	 * @covers EmundusHelperFiles::getApplicantFnums
	 * @return void
	 */
	public function testgetApplicantFnums() {
		$user_id = JFactory::getUser()->id;
		$fnums = $this->m_profile->getApplicantFnums($user_id);
		$this->assertIsArray($fnums);
		$this->assertEmpty($fnums, 'Empty user return empty array');

		$user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 10000) . '@emundus.test.fr');
		$program = $this->h_sample->createSampleProgram();
		$campaign_id = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

		$fnums = $this->m_profile->getApplicantFnums($user_id);
		$this->assertIsArray($fnums);
		$this->assertNotEmpty($fnums);
		$this->assertContains($fnum, array_keys($fnums));
	}
}
