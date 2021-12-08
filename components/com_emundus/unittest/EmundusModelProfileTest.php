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
include_once (JPATH_SITE . '/components/com_emundus/models/application.php');

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
    private $m_application;

    private $db;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_profile = new EmundusModelProfile;
        $this->m_formbuilder = new EmundusonboardModelformbuilder;
        $this->m_file = new EmundusModelFiles;
        $this->m_application = new EmundusModelApplication;
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

        try {
            $this->assertSame($expected_result, $actual_result);
        } catch(Exception $e) {
            $u = JUser::getInstance($user->id);
            $u->delete();
            JLog::add('Failed test case :: Get Profile By Status -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_workflow');;
            return $e->getMessage();
        }
        
        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    public function testGetStepByFnumEditMode() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(2,$user->id);

        $this->m_file->updateState(array($fnum), 0);

        $actual_result = get_object_vars($this->m_profile->getStepByFnum($fnum));
        $expected_result = array('step' =>  '1', 'editable_status' => ['0','6'], 'output_status' => ['1'], 'start_date' => '2021-12-04 23:00:00', 'end_date' => '2021-12-30 23:00:00', 'msg' => '*** Potentially Edit ***');

        try {
            $this->assertSame($expected_result, $actual_result);
        } catch(Exception $e) {
            $u = JUser::getInstance($user->id);
            $u->delete();
            JLog::add('Failed test case :: Get Step By Fnum + Edit Mode -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_workflow');;
            return $e->getMessage();
        }

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    public function testGetStepByFnumReadModeWithProfile() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(2,$user->id);

        $this->m_file->updateState(array($fnum), 4);

        $actual_result = get_object_vars($this->m_profile->getStepByFnum($fnum));
        $expected_result = array('step' => '2', 'editable_status' => [], 'output_status' => [], 'start_date' => '2022-01-02 23:00:00', 'end_date' => '2022-12-30 23:00:00', 'msg' => '*** Always Read-only ***');

        try {
            $this->assertSame($expected_result, $actual_result);
        } catch(Exception $e) {
            $u = JUser::getInstance($user->id);
            $u->delete();
            JLog::add('Failed test case :: Get Step By Fnum + Read Mode With Profile -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_workflow');;
            return $e->getMessage();
        }

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    public function testGetStepByFnumReadModeWithoutProfile() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(2,$user->id);

        $this->m_file->updateState(array($fnum), 7);

        $actual_result = get_object_vars($this->m_profile->getStepByFnum($fnum));
        $expected_result = array('step' => null, 'editable_status' => [], 'output_status' => [], 'start_date' => '2021-12-06 00:00:00', 'end_date' => '2022-04-30 00:00:00', 'msg' => '*** Always Read-only ***');

        try {
            $this->assertSame($expected_result, $actual_result);
        } catch(Exception $e) {
            $u = JUser::getInstance($user->id);
            $u->delete();
            JLog::add('Failed test case :: Get Step By Fnum + Without Profile -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_workflow');;
            return $e->getMessage();
        }

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    /* expected result :: write mode (?view=form) */
    public function testGetRedirectPage() {
        $mainframe = JFactory::getApplication();
        $offset = $mainframe->get('offset', 'UTC');
        
        $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
        $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
        $now = $dateTime->format('Y-m-d H:i:s');

        /* create new applicant */
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(2,$user->id);

        $this->m_file->updateState(array($fnum), 0);

        /* write mode or read mode */
        $raw = $this->m_profile->getStepByFnum($fnum);

        $inputs = $raw->editable_status;
        $outputs = $raw->output_status;
        $start = $raw->start_date;
        $end = $raw->end_date;
        $menutype = $raw->menutype;

        /* get actual status of fnum */
        $ustatus = $this->m_file::getFnumInfos($fnum)['status'];

        /* */
        $can_edit_form = !in_array($ustatus, $inputs);

        try {
            $this->assertSame(false, false);
        } catch(Exception $e) {
            $u = JUser::getInstance($user->id);
            $u->delete();
            JLog::add('Failed test case :: Test Can Edit Form -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_workflow');;
            return $e->getMessage();
        }

        /* check campaign started */
        /* check deadline passed */

        if(!empty($fnum)) {
            $isPassed = ($now > $end || $now < $start) ? true : false;
            $isStarted = ($now >= $start) ? true : false;
        }
        else{
            $isPassed = ($now > $end || $now < $start) ? true : false;
            $isStarted = ($now >= $start) ? true : false;
        }

        /* expected result : isStarted[true], $isPassed[false] */
        try {
            $this->assertEquals(false, $isPassed);
            $this->assertEquals(false, $isStarted);
        } catch(Exception $e) {
            $u = JUser::getInstance($user->id);
            $u->delete();
            JLog::add('Failed test case :: Time Constraint -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_workflow');;
            return $e->getMessage();
        }

        /* get redirected url, using method getFirstPage() */
        $sess = new stdClass();

        $session = JFactory::getSession();
        $sess->menutype = $menutype;
        $session->set('emundusUser', $sess);

        $actual_url = $this->m_application->getFirstPage();
        $expected_url = 'index.php?option=com_fabrik&view=form&formid=376&Itemid=3212';

        try {
            $this->assertSame($expected_url, $actual_url);
        }
        catch(Exception $e) {
            $u = JUser::getInstance($user->id);
            $u->delete();
            JLog::add('Failed test case :: Get Redirected URL -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_workflow');;
            return $e->getMessage();
        }

        $u = JUser::getInstance($user->id);
        $u->delete();
    }
}
