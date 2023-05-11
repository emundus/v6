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
include_once (JPATH_SITE . '/components/com_emundus/models/users.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelUsersTest extends TestCase
{
    private $m_users;
	private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_users = new EmundusModelUsers;
	    $this->h_sample = new EmundusUnittestHelperSamples;
    }

	public function testFoo()
	{
		$this->assertTrue(true);
	}

	/**
	 * @covers EmundusModelUsers::getNonApplicantId
	 * Function getNonApplicantId return an array of array containing user_id entry key
	 * It should only return user_ids that are not only applicant (at least one profile is not an applicant profile)
	 * @return void
	 */
	public function testgetNonApplicantId() {
		$this->assertSame([], $this->m_users->getNonApplicantId(0));

		$user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$this->assertSame([], $this->m_users->getNonApplicantId($user_id), 'User with only applicant profile should not appear in the list of non applicant users');

		$user_id = $this->h_sample->createSampleUser(2, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$nonApplicantIds = $this->m_users->getNonApplicantId($user_id);
		$user_is_not_applicant = false;
		foreach ($nonApplicantIds as $nonApplicantId) {
			if ($nonApplicantId['user_id'] == $user_id) {
				$user_is_not_applicant = true;
			}
		}
		$this->assertTrue($user_is_not_applicant, 'Non applicant user appears in the list of non applicant users');
	}
}
