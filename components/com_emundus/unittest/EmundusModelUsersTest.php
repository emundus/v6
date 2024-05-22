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

		$applicant_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$this->assertSame([], $this->m_users->getNonApplicantId($applicant_id), 'User with only applicant profile should not appear in the list of non applicant users');

		$user_id = $this->h_sample->createSampleUser(2, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$nonApplicantIds = $this->m_users->getNonApplicantId($user_id);
		$this->assertNotEmpty($nonApplicantIds, 'User with at least one non applicant profile should appear in the list of non applicant users');

		$user_is_not_applicant = false;
		foreach ($nonApplicantIds as $nonApplicantId) {
			if ($nonApplicantId['user_id'] == $user_id) {
				$user_is_not_applicant = true;
			}
		}
		$this->assertTrue($user_is_not_applicant, 'Non applicant user appears in the list of non applicant users');

		$nonApplicantIds = $this->m_users->getNonApplicantId([$user_id, $applicant_id]);
		$this->assertNotEmpty($nonApplicantIds, 'Passing an array of user ids should return an array of non applicant users');
		$this->assertSame(1, count($nonApplicantIds), 'Since only one of the two users is not an applicant, only one user should appear in the list of non applicant users');

		$this->assertSame([], $this->m_users->getNonApplicantId([$applicant_id, $applicant_id, 'test passing a string instead of an id']), 'Passing an incorrect array should return an empty array');
	}

	public function testaffectToGroups() {
		$this->assertEmpty($this->m_users->affectToGroups([], []), 'Passing an incorrect user id should return false');
		$this->assertEmpty($this->m_users->affectToGroups([['user_id' => 99999]], []), 'Passing an incorrect array of group ids should return false');

		$user_id = $this->h_sample->createSampleUser(2, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$nonApplicantIds = $this->m_users->getNonApplicantId($user_id);
		$this->assertTrue($this->m_users->affectToGroups($nonApplicantIds, [1]), 'Affect user to group, using getNonApplicantId result should return true');
	}

	public function testgetProfileDetails() {

		$this->assertEmpty($this->m_users->getProfileDetails(0), 'Passing an incorrect user id should return false');
		$profile = $this->m_users->getProfileDetails(9);
		$this->assertNotEmpty($profile, 'Passing a correct user id should return an array of profile details');
		$this->assertObjectHasAttribute('label', $profile, 'Profile details should contain label');
		$this->assertObjectHasAttribute('class', $profile, 'Profile details should contain class');
	}

    public function testrepairEmundusUser() {
        $this->assertEmpty($this->m_users->repairEmundusUser(0), 'Passing an empty user id should return false');
        $this->assertEmpty($this->m_users->repairEmundusUser(999999), 'Passing an incorrect user id should return false');

        $user_id = $this->h_sample->createSampleUser(2, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');

        $this->assertTrue($this->m_users->repairEmundusUser($user_id), 'Passing a user id that has an emundus_users line should return true as the function should still be executed correctly');

        // Delete the emundus_users line
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear()
            ->delete($db->quoteName('#__emundus_users'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
        $db->setQuery($query);
        $db->execute();

        $this->assertTrue($this->m_users->repairEmundusUser($user_id), 'Passing a user id that has a missing emundus_users line should return true as the account should be repaired');

        $users = $this->m_users->getUserById($user_id);
        $this->assertNotEmpty($users, 'The user should be found in the database');
        $this->assertEquals($user_id, $users[0]->user_id, 'The user id should be the same');
        $this->assertEquals('Test', $users[0]->firstname, 'The user id should be the same');
        $this->assertEquals('USER', $users[0]->lastname, 'The user id should be the same');
    }
}
