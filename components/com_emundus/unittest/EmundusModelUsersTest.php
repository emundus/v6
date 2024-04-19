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

    /*
     * Test the getJosUsersById() method
     * Should return an email, an username, a register date and a last connection date (maybe null)
     */
    public function testgetJosUsersById() {

        $this->assertEmpty($this->m_users->getJosUsersById(0), 'Passing an incorrect user id should return null');
        $josUser = $this->m_users->getJosUsersById(95);
        $this->assertNotEmpty($josUser, 'Passing a correct user id should return an array of profile details');

        // Should contain
        $this->assertObjectHasAttribute('email', $josUser, 'Profile details should contain email');
        $this->assertObjectHasAttribute('username', $josUser, 'Profile details should contain username');
        $this->assertObjectHasAttribute('registerDate', $josUser, 'Profile details should contain register date');
        $this->assertObjectHasAttribute('lastvisitDate', $josUser, 'Profile details should contain last connection date (if there is one');

        // Should not contain
        $this->assertObjectNotHasAttribute('password', $josUser, 'Profile details should not contain password !');
    }

    /*
     * Test the getProfileDescriptionById() method
     * Should return a description
     */
    public function testgetProfileDescriptionById() {

        $this->assertEmpty($this->m_users->getProfileDescriptionById(0), 'Passing an incorrect user id should return null');
        $profile = $this->m_users->getProfileDescriptionById(9);
        $this->assertNotEmpty($profile, 'Passing a correct profile id should return an array of profile details');

        // Should contain
        $this->assertObjectHasAttribute('description', $profile, 'Profile details should contain description');

        // Should not contain
        $this->assertObjectNotHasAttribute('label', $profile, 'Profile details should not contain label !');
    }

    /*
     * Test the getUserGroupsLabelById() method
     * Should return a label
     */
    public function testgetUserGroupsLabelById() {

        $this->assertEmpty($this->m_users->getUserGroupsLabelById(0), 'Passing an incorrect user id should return null');
        $groups = $this->m_users->getUserGroupsLabelById(95);
        $this->assertNotEmpty($groups, 'Passing a correct user id should return an array of profile details');
        foreach ($groups as $group)
        {
            // Should contain
            $this->assertObjectHasAttribute('label', $group, 'Group(s) details should contain label');

            // Should not contain
            $this->assertObjectNotHasAttribute('description', $group, 'Group(s) details should not contain description !');
        }
    }

    /*
     * Test the getColumnsForm() method
     * Should return an id, name, plugin and label
     */
    public function testgetColumnsForm() {

        $columns = $this->m_users->getColumnsForm();
        foreach ($columns as $column)
        {
            // Should contain
            $this->assertObjectHasAttribute('id', $column, 'Columns form details should contain id');
            $this->assertObjectHasAttribute('name', $column, 'Columns form details should contain name');
            $this->assertObjectHasAttribute('plugin', $column, 'Columns form details should contain plugin');
            $this->assertObjectHasAttribute('label', $column, 'Columns form details should contain label');

            // Should not contain
            $this->assertObjectNotHasAttribute('group_id', $column, 'Columns form details should not contain group form id !');
        }
    }

    /*
     * Test the getAllInformationsToExport() method
     * Should return an array of 2 array (each element of both array containing an array too)
     */
    public function testgetAllInformationsToExport()
    {

        $this->assertEmpty($this->m_users->getAllInformationsToExport(0), 'Passing an incorrect user id should return null');
        $data = $this->m_users->getAllInformationsToExport(95);
        $this->assertNotEmpty($data, 'Passing a correct user id should return an array of data');
        $this->assertCount(2, $data, 'Data array should contain 2 elements');
        foreach ($data as $key => $dataType) {
            foreach ($dataType as $element) {
                // Should contain
                $this->assertObjectHasAttribute('id', $element, 'Data array details should contain id');
                $this->assertObjectHasAttribute('name', $element, 'Data array details should contain name');
                $this->assertObjectHasAttribute('plugin', $element, 'Data array details should contain plugin');
                $this->assertObjectHasAttribute('label', $element, 'Data array details should contain label');

                // Should not contain
                $this->assertObjectNotHasAttribute('group_id', $element, 'Data array details should not contain group form id !');

                if ($key === 'user_data') {
                    $this->assertObjectHasAttribute('value', $element, 'user_data array details should contain value');
                }
                else
                {
                    $this->assertObjectNotHasAttribute('value', $element, 'column array details should not contain value');
                }

                // Check if it is sorted alphabetically
                for ($i = 1; $i < count($dataType); $i++) {
                    $currentLabel = JText::_($dataType[$i]->label);
                    $previousLabel = JText::_($dataType[$i - 1]->label);
                    $this->assertLessThanOrEqual(true, strcmp($previousLabel, $currentLabel));

                }
            }
        }
    }

    /*
     * Test the getUserDetails() method
     * Should return an array with all columns
     */
    public function testgetUserDetails()
    {
        $this->assertEmpty($this->m_users->getUserDetails(0), 'Passing an incorrect user id should return null');
        $data = $this->m_users->getUserDetails(95);
        $this->assertNotEmpty($data, 'Passing a correct user id should return an array of data');
        $dataBefore = $this->m_users->getAllInformationsToExport(95);
        $numberColumns = 0;
        $arrayName = array();

        // Count the number of columns  we should have
        foreach ($dataBefore as $dataType) {
            foreach ($dataType as $element) {
                $arrayName[] = $element->name;
                $numberColumns += 1;
            }
        }
        foreach ($arrayName as $name) {
            $this->assertArrayHasKey($name, $data, "Key '$name' not found in data array");
        }

        $this->assertCount($numberColumns, $data, 'Not the number of columns expected');
    }
}

