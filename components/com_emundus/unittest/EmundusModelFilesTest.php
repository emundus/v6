<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */


/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use PHPUnit\Framework\TestCase;

ini_set('display_errors', false);
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');
include_once(JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_SITE . '/components/com_emundus/models/files.php');
include_once(JPATH_SITE . '/components/com_emundus/helpers/users.php');

jimport('joomla.user.helper');
jimport('joomla.application.application');
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

class EmundusModelFilesTest extends TestCase{
    private $m_files;
    private $h_sample;
    private $h_users;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_files = new EmundusModelFiles;
        $this->h_sample = new EmundusUnittestHelperSamples;
        $this->h_users = new EmundusHelperUsers;

        $app = JFactory::getApplication();
        $username = 'test-gestionnaire-' . rand(0, 1000) . '@emundus.fr';
        $this->h_sample->createSampleUser(2, $username);
        $logged_in = $app->login([
            'username' => $username,
            'password' => 'test1234'
        ]);

        include_once(JPATH_SITE . '/components/com_emundus/models/profile.php');
        $m_profile = new EmundusModelProfile();
        $m_profile->initEmundusSession();
    }

	public function testFoo()
	{
		$this->assertTrue(true);
	}

    public function testConstruct() {
        $this->assertSame(false, $this->m_files->use_module_filters, 'By default, we do not use new module filters');
    }

    public function testGetUsers()
    {
        $users = $this->m_files->getUsers();
        $this->assertIsArray($users, 'getusers returns an array');

        $user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);
        $fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);


        $users = $this->m_files->getUsers();
        $this->assertNotEmpty($users, 'if a fnum exists, by default get users should return a value');


    }

    public function testgetAllTags()
    {
        $tags = $this->m_files->getAllTags();
        $this->assertIsArray($tags, 'getAllTags returns an array');
        $this->assertNotEmpty($tags, 'getAllTags returns a non-empty array');
    }

    public function testTagFile() {
        $tagged = $this->m_files->tagFile([], []);
        $this->assertFalse($tagged, 'tagFile returns false if no file is given');

        $user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);
        $fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

        $tagged = $this->m_files->tagFile([$fnum], []);
        $this->assertFalse($tagged, 'tagFile returns false if no tag is given');

        $tags = $this->m_files->getAllTags();
        $tagged = $this->m_files->tagFile([$fnum], [$tags[0]['id']], 62);
        $this->assertTrue($tagged, 'tagFile returns true if a file and a tag are given');
    }

    public function testUpdateState() {


    }
}
