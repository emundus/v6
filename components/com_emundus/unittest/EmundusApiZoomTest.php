<?php
use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once (__DIR__ . '/../models/sync.php');
include_once (__DIR__ . '/../classes/api/Zoom.php');
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusApiZoomTest extends TestCase
{

	/**
	 * @var EmundusApiZoomTest
	 */
	private $zoom;

	private $host_id;
	private $db;

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->db = JFactory::getDbo();

		// those credentials are one of an unimportant test account
		$config = JComponentHelper::getParams('com_emundus');
		$config->set('zoom_account_id', 'fE2QEn57Smy5K4Qish6XMQ');
		$config->set('zoom_client_id', 's98PRnX0QeIAxl9un4Sw');
		$config->set('zoom_client_secret', 'y6kOHCAQkgY54OGPzcHyK42es5T0Zps6');

		$this->testAuthToZoom();
		$this->testgetUsers();
	}

	public function testConstruct()
	{
		$this->assertTrue(true);
	}

	public function testAuthToZoom()
	{
		$auth_succeed = true;

		try {
			$this->zoom = new Zoom();
		} catch (Exception $e) {
			$auth_succeed = false;
		}

		$this->assertTrue($auth_succeed, 'Authentification to Zoom is working properly');
	}

	public function testgetUsers()
	{
		$users = $this->zoom->getUsers();

		$this->assertNotEmpty($users, 'Users are fetched successfully');
		$this->assertObjectHasAttribute('users', $users, 'Users are fetched successfully, users attribute exists');

		$this->host_id = $users->users[0]->id;
	}

	public function testgetUserMeetings()
	{
		$meetings = $this->zoom->getUserMeetings($this->host_id);
		$this->assertNotEmpty($meetings, 'User\'s meetings are fetched successfully');
		$this->assertObjectHasAttribute('meetings', $meetings, 'User\'s meetings are fetched successfully, meetings attribute exists');

	}

	public function  testCreateUser()
	{
		$data = [];
		$user = $this->zoom->createUser($data);
		$this->assertNull($user, 'User is not created, because no data is given');

		/*$data = [
			'email' => 'test-emundus-zoom+'. rand(0, 1000) .'@emundus.fr',
			'first_name' => 'Test',
			'last_name' => 'Emundus',
			'type' => 1
		];
		$user = $this->zoom->createUser($data);

		$this->assertNotEmpty($user, 'User is created successfully');*/
	}

	public function testcreateMeeting()
	{
		$data = [];
		$meeting = $this->zoom->createMeeting($this->host_id, $data);

		$this->assertEmpty($meeting, 'Meeting is not created, because no data is given');
	}

	public function testGetMeeting()
	{
		$meeting = $this->zoom->getMeeting(0);
		$this->assertEmpty($meeting, 'Meeting is not found, because no meeting id is given');
	}
}