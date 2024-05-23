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
	 * @covers EmundusModelUsers::deleteMessagesBeforeADate
	 * Function deleteMessagesBeforeADate deletes messages before a certain date
	 * It should return the amount of messages deleted
	 * @return void
	 * @throws Exception
	 */
	public function testDeleteMessagesBeforeADate()
	{

		$messages = $this->m_messages->deleteMessagesBeforeADate('');
		$this->assertEquals(0, $messages, 'No message should be deleted if no date is given');
		$db = JFactory::getDbo();

		$user_from      = 95;
		$user_to        = 0;
		$reference_date1 = '2000-01-01 10:00:00';
		$reference_date2 = '2000-01-01 12:00:00';

		for ($i = 0; $i < 10; $i++)
		{
			$message_text = 'test' . rand(0, 1000);
			$message      = $this->m_messages->sendMessage($user_to, $message_text, $user_from,true);
			$this->assertTrue($message, 'Message should be created if all minimum information are given');

			$query = $db->getQuery(true);

			$query->clear()
				->update($db->quoteName('#__messages'))
				->set($db->quoteName('date_time') . ' = ' . $db->quote($reference_date1))
				->where($db->quoteName('message') . ' = ' . $db->quote($message_text))
				->where($db->quoteName('user_id_to') . ' = ' . $db->quote($user_to))
				->where($db->quoteName('user_id_from') . ' = ' . $db->quote($user_from))
				->where($db->quoteName('message') . ' = ' . $db->quote($message_text))
				->order($db->quoteName('date_time') . ' DESC')
				->limit(1);

			$db->setQuery($query);
			$db->execute();
		}

		// Create a single message different from the others (date_time change)
		$message_text = 'test' . rand(0, 1000);
		$message      = $this->m_messages->sendMessage($user_to, $message_text, $user_from,true);
		$this->assertTrue($message, 'Message should be created if all minimum information are given');

		$query = $db->getQuery(true);

		$query->clear()
			->update($db->quoteName('#__messages'))
			->set($db->quoteName('date_time') . ' = ' . $db->quote($reference_date2))
			->where($db->quoteName('message') . ' = ' . $db->quote($message_text))
			->where($db->quoteName('user_id_to') . ' = ' . $db->quote($user_to))
			->where($db->quoteName('user_id_from') . ' = ' . $db->quote($user_from))
			->where($db->quoteName('message') . ' = ' . $db->quote($message_text))
			->order($db->quoteName('date_time') . ' DESC')
			->limit(1);

		$db->setQuery($query);
		$db->execute();

		$messages = $this->m_messages->deleteMessagesBeforeADate(new DateTime('2000-01-01 11:00:00'));
		$this->assertEquals(10, $messages, 'All messages created before 2000-01-01 11:00:00 should be deleted');

		$messages = $this->m_messages->deleteMessagesBeforeADate(new DateTime('2000-01-01 13:00:00'));
		$this->assertEquals(1, $messages, 'Message created before 2000-01-01 13:00:00 should be deleted');
	}

	/**
	 * @covers EmundusModelUsers::exportMessagesBeforeADate
	 * Function exportMessagesBeforeADate exports messages before a certain date
	 * It should return the name of the csv file created
	 * @return void
	 * @throws Exception
	 */
	public function testExportMessagesBeforeADate()
	{

		$file_messages = $this->m_messages->exportMessagesBeforeADate('');
		$this->assertEquals( '', $file_messages, 'No file should be created if no date is given');
		$file_messages = $this->m_messages->exportMessagesBeforeADate(new DateTime('2000-01-01 13:00:00'));
		$this->assertEquals( '', $file_messages, 'No file should be created if no messages are found before the given date');
		$db = JFactory::getDbo();

		$user_from      = 95;
		$user_to        = 0;
		$reference_date = '2000-01-01 12:00:00';

		$message_text = 'test' . rand(0, 1000);
		$message      = $this->m_messages->sendMessage($user_to, $message_text, $user_from,true);
		$this->assertTrue($message, 'Message should be created if all minimum information are given');

		$query = $db->getQuery(true);

		$query->clear()
			->update($db->quoteName('#__messages'))
			->set($db->quoteName('date_time') . ' = ' . $db->quote($reference_date))
			->where($db->quoteName('message') . ' = ' . $db->quote($message_text))
			->where($db->quoteName('user_id_to') . ' = ' . $db->quote($user_to))
			->where($db->quoteName('user_id_from') . ' = ' . $db->quote($user_from))
			->where($db->quoteName('message') . ' = ' . $db->quote($message_text))
			->order($db->quoteName('date_time') . ' DESC')
			->limit(1);

		$db->setQuery($query);
		$db->execute();

		$file_messages = $this->m_messages->exportMessagesBeforeADate(new DateTime('2000-01-01 13:00:00'));
		$this->m_messages->deleteMessagesBeforeADate(new DateTime('2000-01-01 13:00:00'));
		$this->assertNotEquals('', $file_messages, 'A file should be created if a date is given');

		unlink($file_messages);
	}
}
