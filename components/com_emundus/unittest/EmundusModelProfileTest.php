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
include_once (JPATH_SITE . '/components/com_emundus_onboard/models/formbuilder.php');
include_once(JPATH_SITE.'/components/com_emundus/helpers/files.php');
include_once (JPATH_SITE . '/components/com_emundus/models/files.php');

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
    private $s_helper;
    private $m_file;

    private $db;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_profile = new EmundusModelProfile;
        $this->m_formbuilder = new EmundusonboardModelformbuilder;
        $this->m_file = new EmundusModelFiles;
        $this->db = JFactory::getDbo();
    }

    // simple test case (example)
    public function testFoo() {
        $foo = true;
        $this->assertSame(true, $foo);
    }

    public function testGetProfileByStatus() {
        // TEST 1 - SUCCESS WAITING
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(2,$user->id);

        $this->m_file->updateState(array($fnum), 0);

        $actual_result = $this->m_profile->getProfileByStatus($fnum);
        $expected_result = array('firstname' => 'Test', 'lastname' => 'USER', 'profile' => '1004', 'university_id' => '0', 'label' => 'Formulaire alpha', 'menutype' => 'menu-profile1004', 'published' => '1', 'campaign_id' => '2',);

        $this->assertSame($expected_result, $actual_result);

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    public function testGetStepByFnumEditMode() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(2,$user->id);

        $this->m_file->updateState(array($fnum), 0);

        $actual_result = get_object_vars($this->m_profile->getStepByFnum($fnum));
        $expected_result = array('step' =>  '1', 'editable_status' => ['0','6'], 'output_status' => ['1'], 'start_date' => '2021-12-04 23:00:00', 'end_date' => '2021-12-30 23:00:00', 'msg' => '*** Potentially Edit ***');

        $this->assertSame($expected_result, $actual_result);

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    public function testGetStepByFnumReadModeWithProfile() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(2,$user->id);

        $this->m_file->updateState(array($fnum), 4);

        $actual_result = get_object_vars($this->m_profile->getStepByFnum($fnum));
        $expected_result = array('step' => '2', 'editable_status' => [], 'output_status' => [], 'start_date' => '2022-01-02 23:00:00', 'end_date' => '2022-12-30 23:00:00', 'msg' => '*** Always Read-only ***');

        $this->assertSame($expected_result, $actual_result);

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    public function testGetStepByFnumReadModeWithoutProfile() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(2,$user->id);

        $this->m_file->updateState(array($fnum), 0);

        $actual_result = get_object_vars($this->m_profile->getStepByFnum($fnum));
        $expected_result = array('step' => '1', 'editable_status' => ['0','6'], 'output_status' => ['1'], 'start_date' => '2021-12-04 23:00:00', 'end_date' => '2021-12-30 23:00:00', 'msg' => '*** Potentially Edit ***');

        $this->assertSame($expected_result, $actual_result);

        $u = JUser::getInstance($user->id);
        $u->delete();
    }
}
