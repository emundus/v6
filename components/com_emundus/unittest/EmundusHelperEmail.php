<?php


use PHPUnit\Framework\TestCase;

ini_set('display_errors', false);
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');
include_once(JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_SITE . '/components/com_emundus/helpers/emails.php');
include_once(JPATH_SITE . '/administrator/components/com_emundus/helpers/update.php');

jimport('joomla.user.helper');
jimport('joomla.application.application');
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusHelperEmail extends TestCase
{
	private $h_emails;
	private $h_sample;

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->h_emails = new EmundusHelperEmails();
		$this->h_sample = new EmundusUnittestHelperSamples;
	}

	public function testFoo()
	{
		$foo = true;
		$this->assertSame(true, $foo);
	}

	public function testCorrectEmail()
	{
		$this->assertSame(false, $this->h_emails->correctEmail(''), 'Validate empty email returns false');

		$this->assertSame(false, $this->h_emails->correctEmail('@email.com'), 'Validate email with wrong format returns false');
		$this->assertSame(false, $this->h_emails->correctEmail('jeremy.legendreemundus.fr'), 'Validate email with wrong format returns false');
		$this->assertSame(false, $this->h_emails->correctEmail('jeremy.legendre@'), 'Validate email with wrong format returns false');

		$this->assertSame(false, $this->h_emails->correctEmail('jeremy.legendre@wrong.dns'), 'Validate email with wrong dns returns false');

		$this->assertSame(true, $this->h_emails->correctEmail('jeremy.legendre@emundus.fr'), 'Validate correct email format returns true');
	}

	public function testAssertCanSendEmail()
	{
		$this->assertSame(false, $this->h_emails->assertCanSendMailToUser(), 'can send mail returns false if nor user_id nor fnum given');

		$user_id = $this->h_sample->createSampleUser();

		// User with correct email
		if (!empty($user_id)) {
			$this->assertSame(true, $this->h_emails->assertCanSendMailToUser($user_id), 'A new created user with valid adress can receive emails');

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$params = json_encode(array('send_mail' => false));
			$query->clear()
				->update('#__users')
				->set('params = ' . $db->quote($params))
				->where('id = ' . $user_id);
			$db->setQuery($query);
			$db->execute();

			$this->assertSame(false, $this->h_emails->assertCanSendMailToUser($user_id), 'A user with param send email to false does not pass assertCanSendMailToUser function');

			$params = json_encode(array('send_mail' => true));
			$query->clear()
				->update('#__users')
				->set('params = ' . $db->quote($params))
				->where('id = ' . $user_id);
			$db->setQuery($query);
			$db->execute();

			$this->assertSame(true, $this->h_emails->assertCanSendMailToUser($user_id), 'A user with param send email to true pass assertCanSendMailToUser function');

			$query->clear()
				->delete($db->quoteName('#__users'))
				->where('id = ' . $user_id);
			$db->setQuery($query);
			$db->execute();
		}

		// User with incorrect email
		$invalid_email_user_id = $this->h_sample->createSampleUser(1000, 'legendre.jeremy');
		if (!empty($invalid_email_user_id)) {
			$this->assertSame(false, $this->h_emails->assertCanSendMailToUser($invalid_email_user_id), 'A new created user with invalid address can not receive emails');

			$query->clear()
				->delete($db->quoteName('#__users'))
				->where('id = ' . $invalid_email_user_id);
			$db->setQuery($query);
			$db->execute();
		}

		// User with inexisting email dns
		$invalid_email_dns_user_id = $this->h_sample->createSampleUser(1000, 'legendre.jeremy@wrong.dns.wrong');
		if (!empty($invalid_email_dns_user_id)) {
			$this->assertSame(false, $this->h_emails->assertCanSendMailToUser($invalid_email_dns_user_id), 'A new created user with invalid dns in address can not receive emails');

			$query->clear()
				->delete($db->quoteName('#__users'))
				->where('id = ' . $invalid_email_dns_user_id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	public function testGetCustomHeader()
	{
		// By default we doesn't have custom header
		$this->assertSame('', EmundusHelperEmails::getCustomHeader());

		// Add a custom header to emundus component
		EmundusHelperUpdate::updateComponentParameter('com_emundus', 'email_custom_tag', 'X-Mailin-Tag,emundus');

		$this->assertSame('X-Mailin-Tag:emundus', EmundusHelperEmails::getCustomHeader());
	}
}