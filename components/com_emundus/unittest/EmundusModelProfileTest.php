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
    private $s_helper;

    private $db;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_profile = new EmundusModelProfile;
        $this->m_formbuilder = new EmundusModelFormbuilder;
        $this->db = JFactory::getDbo();
    }

    // simple test case (example)
    public function testFoo() {
        $foo = true;
        $this->assertSame(true, $foo);
    }

    
    /*public function testGetProfileByStatus() {
        // TEST 1 - SUCCESS WAITING
        $user = @EmundusUnittestHelperSamples::createSampleUser();

        $fnum = @EmundusUnittestHelperSamples::createSampleFile(1,$user->id);

        $output_data = array(
                'firstname' => 'Test',
                'lastname' => 'USER',
                'profile' => '9',
                'university_id' => '0',
                'label' => 'Formulaire de base candidat',
                'menutype' => 'menu-profile9',
                'published' => '1',
                'campaign_id' => '1',
        );

        $this->assertSame($output_data, $this->m_profile->getProfileByStatus($fnum));
        //

        $u = JUser::getInstance($user->id);
        $u->delete();
    }*/
}
