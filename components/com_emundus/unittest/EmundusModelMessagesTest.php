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
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');
include_once (JPATH_SITE . '/components/com_emundus/models/messages.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelMessagesTest extends TestCase
{
    private $m_messages;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_messages = new EmundusModelMessages;
    }

	public function testFoo()
	{
		$this->assertTrue(true);
	}

	/**
	 * @covers EmundusModelUsers::deleteMessagesAfterADate
	 * Function deleteMessagesAfterADate deletes messages after a certain date
	 * It should return the amount of messages deleted
	 * @return void
	 * @throws Exception
	 */
	public function testDeleteMessagesAfterADate() {

		$this->assertEquals(0, $this->m_messages->deleteMessagesAfterADate(''), 'No logs should be deleted if no date is given');
		$db = JFactory::getDbo();

		$user_from = 95;
		$user_to = 0;
		$reference_date = '2000-01-01 10:00:00';

		for ($i = 0; $i < 10; $i++) {
			$message = 'test' . rand(0, 1000);
			$message_log = $this->m_messages->sendMessage($user_to, $message, $user_from);
			$this->assertTrue($message_log, 'Message should be created if all minimum information are given');

			$query = $db->getQuery(true);

			$query->clear()
				->update($db->quoteName('#__messages'))
				->set($db->quoteName('date_time') . ' = ' . $db->quote($reference_date))
				->where($db->quoteName('message') . ' = ' . $db->quote($message))
				->where($db->quoteName('user_id_to') . ' = ' . $db->quote($user_to))
				->where($db->quoteName('user_id_from') . ' = ' . $db->quote($user_from))
				->order($db->quoteName('date_time') . ' DESC')
				->limit(1);

			$db->setQuery($query);
			$db->execute();

		}
		$this->assertEquals(10, $this->m_messages->deleteMessagesAfterADate(new DateTime('2000-01-01 11:00:00')), 'All logs created in the test should be deleted');
	}
}
