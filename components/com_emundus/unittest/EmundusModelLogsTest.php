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

include_once(JPATH_BASE . 'includes/defines.php' );
include_once(JPATH_BASE . 'includes/framework.php' );
include_once(JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_SITE . '/components/com_emundus/models/application.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelLogsTest extends TestCase
{
    private $m_logs;

    private $h_sample;

    public $user_sample_id = 0;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_logs = new EmundusModelLogs();
        $this->h_sample = new EmundusUnittestHelperSamples();
        $this->user_sample_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
    }

	public function testFoo()
	{
		$this->assertTrue(true);
	}

    public function testgetActionsOnFnum() {
        $logs = $this->m_logs->getActionsOnFnum(0);
        $this->assertEmpty($logs, 'No logs should be returned if no fnum is given');

        $logs = $this->m_logs->getActionsOnFnum('test');
        $this->assertEmpty($logs, 'No logs should be returned if fnum incorrect, but function should not crash');
    }

    public function testLog() {
        $user_from = 0;
        $user_to = 0;
        $fnum = '';
        $action = '';
        $crud = '';
        $message = '';
        $params = '';

        $logged = false;
        $logged = $this->m_logs->log($user_from, $user_to, $fnum, $action, $crud, $message, $params);
        $this->assertFalse($logged, 'Log should not be created if no information is given');

        $user_from = 62; // sysadmin
        $user_to = $this->user_sample_id;
        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);
        $fnum = $this->h_sample->createSampleFile($campaign_id, $user_to);

        $action = 1;
        $crud = 'r';
        $message = 'test';

        $logged = $this->m_logs->log($user_from, $user_to, $fnum, $action, $crud, $message, $params);
        $this->assertFalse($logged, 'Sysadmin 62 is by default excluded from logs');

        $user_from = 95;
        $logged = $this->m_logs->log($user_from, $user_to, $fnum, $action, $crud, $message, $params);
        $this->assertTrue($logged, 'Log should be created if all information is given');

        $logs = $this->m_logs->getActionsOnFnum($fnum);
        $this->assertNotEmpty($logs, 'Logs should be returned if fnum is given');

        // if i specify another action and crud, is should not retrieve the log
        $logs = $this->m_logs->getActionsOnFnum($fnum, null, 2);
        $this->assertEmpty($logs, 'Logs should not be returned if fnum is given but not the action');

        $logs = $this->m_logs->getActionsOnFnum($fnum, null, 1, 'd');
        $this->assertEmpty($logs, 'Logs should not be returned if fnum is given but not the crud');
    }
}
