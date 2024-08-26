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
        $this->user_sample_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 10000) . '@emundus.test.fr');
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

	/**
	 * @covers EmundusModelUsers::deleteLogsBeforeADate
	 * Function deleteLogsBeforeADate deletes logs before a certain date
	 * It should return the amount of logs deleted
	 * @return void
	 * @throws Exception
	 */
	public function testDeleteLogsBeforeADate()
	{

		$logs = $this->m_logs->deleteLogsBeforeADate('');
		$this->assertEquals(0, $logs, 'No logs should be deleted if no date is given');
		$db = JFactory::getDbo();

		$user_from      = 95;
		$user_to        = $this->user_sample_id;
		$crud           = 'r';
		$params         = '';
		$action         = 1;
		$reference_date1 = '2000-01-01 10:00:00';
		$reference_date2 = '2000-01-01 12:00:00';

		for ($i = 0; $i < 10; $i++)
		{
			$message = 'test' . rand(0, 1000);
			$logged  = $this->m_logs->log($user_from, $user_to, 1, $action, $crud, $message, $params);
			$this->assertTrue($logged, 'Log should be created if all information are given');

			$query = $db->getQuery(true);

			$query->clear()
				->update($db->quoteName('#__emundus_logs'))
				->set($db->quoteName('timestamp') . ' = ' . $db->quote($reference_date1))
				->where($db->quoteName('user_id_from') . ' = ' . $db->quote($user_from))
				->where($db->quoteName('user_id_to') . ' = ' . $db->quote($user_to))
				->where($db->quoteName('fnum_to') . ' = ' . $db->quote(1))
				->where($db->quoteName('action_id') . ' = ' . $db->quote($action))
				->where($db->quoteName('verb') . ' = ' . $db->quote($crud))
				->where($db->quoteName('message') . ' = ' . $db->quote($message))
				->order($db->quoteName('timestamp') . ' DESC')
				->limit(1);

			$db->setQuery($query);
			$db->execute();
		}

		// Create a single log different from the others (timestamp change)
		$message = 'test' . rand(0, 1000);
		$logged  = $this->m_logs->log($user_from, $user_to, 1, $action, $crud, $message, $params);
		$this->assertTrue($logged, 'Log should be created if all information are given');

		$query = $db->getQuery(true);

		$query->clear()
			->update($db->quoteName('#__emundus_logs'))
			->set($db->quoteName('timestamp') . ' = ' . $db->quote($reference_date2))
			->where($db->quoteName('user_id_from') . ' = ' . $db->quote($user_from))
			->where($db->quoteName('user_id_to') . ' = ' . $db->quote($user_to))
			->where($db->quoteName('fnum_to') . ' = ' . $db->quote(1))
			->where($db->quoteName('action_id') . ' = ' . $db->quote($action))
			->where($db->quoteName('verb') . ' = ' . $db->quote($crud))
			->where($db->quoteName('message') . ' = ' . $db->quote($message))
			->order($db->quoteName('timestamp') . ' DESC')
			->limit(1);

		$db->setQuery($query);
		$db->execute();

		$logs = $this->m_logs->deleteLogsBeforeADate(new DateTime('2000-01-01 11:00:00'));
		$this->assertEquals(10, $logs, 'All logs created before 2000-01-01 11:00:00 should be deleted');

		$logs = $this->m_logs->deleteLogsBeforeADate(new DateTime('2000-01-01 13:00:00'));
		$this->assertEquals(1, $logs, 'Log created before 2000-01-01 13:00:00 should be deleted');
	}

	/**
	 * @covers EmundusModelUsers::exportLogsBeforeADate
	 * Function exportLogsBeforeADate exports logs before a certain date
	 * It should return the name of the csv file created
	 * @return void
	 * @throws Exception
	 */
	public function testExportLogsBeforeADate()
	{

		$file_logs = $this->m_logs->exportLogsBeforeADate('');
		$this->assertEquals( '', $file_logs, 'No file should be created if no date is given');
		$file_logs = $this->m_logs->exportLogsBeforeADate(new DateTime('2000-01-01 13:00:00'));
		$this->assertEquals( '', $file_logs, 'No file should be created if no logs are found before the given date');
		$db = JFactory::getDbo();

		$user_from      = 95;
		$user_to        = $this->user_sample_id;
		$crud           = 'r';
		$params         = '';
		$action         = 1;
		$reference_date = '2000-01-01 10:00:00';

		$log_text = 'test' . rand(0, 1000);
		$log      = $this->m_logs->log($user_from, $user_to, 1, $action, $crud, $log_text, $params);
		$this->assertTrue($log, 'Log should be created if all minimum information are given');

		$query = $db->getQuery(true);

		$query->clear()
			->update($db->quoteName('#__emundus_logs'))
			->set($db->quoteName('timestamp') . ' = ' . $db->quote($reference_date))
			->where($db->quoteName('user_id_from') . ' = ' . $db->quote($user_from))
			->where($db->quoteName('user_id_to') . ' = ' . $db->quote($user_to))
			->where($db->quoteName('fnum_to') . ' = ' . $db->quote(1))
			->where($db->quoteName('action_id') . ' = ' . $db->quote($action))
			->where($db->quoteName('verb') . ' = ' . $db->quote($crud))
			->where($db->quoteName('message') . ' = ' . $db->quote($log_text))
			->order($db->quoteName('timestamp') . ' DESC')
			->limit(1);

		$db->setQuery($query);
		$db->execute();

		$file_logs = $this->m_logs->exportLogsBeforeADate(new DateTime('2000-01-01 13:00:00'));
		$this->m_logs->deleteLogsBeforeADate(new DateTime('2000-01-01 13:00:00'));
		$this->assertNotEquals('', $file_logs, 'A file should be created if a date is given');

		unlink($file_logs);
	}
}
