<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use PHPUnit\Framework\TestCase;
use Joomla\CMS\Language\Text;
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

    /**
     * @covers EmundusModelUsers::getUserGroupsLabelById
     * Function getUserGroupsLabelById return an array of group(s) details
     * It should return the label of the group(s) the user is in
     * @return void
     */
	public function testgetUserGroupsLabelById() {

		$user_id = $this->h_sample->createSampleUser(2, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$nonApplicantIds = $this->m_users->getNonApplicantId($user_id);
		$this->m_users->affectToGroups($nonApplicantIds, [1]);

		$this->assertEmpty($this->m_users->getUserGroupsLabelById(0), 'Passing an incorrect user id should return null');
		$groups = $this->m_users->getUserGroupsLabelById($user_id);

		foreach ($groups as $groupLabel)
		{
			$this->assertNotEmpty($groupLabel->label, 'Group(s) details should contain label');

			$this->assertEmpty($groupLabel->description, 'Group(s) details should not contain description !');
		}
	}


	/**
     * @covers EmundusModelUsers::getColumnsFromProfileForm
     * Function getColumnsFromProfileForm return an array of columns form details
     * It should return an id, name, plugin, label, group_id and a list of params
     * @return void
     */
    public function testgetColumnsFromProfileForm() {

        $columns = $this->m_users->getColumnsFromProfileForm();
        foreach ($columns as $column)
        {
            $this->assertObjectHasAttribute('id', $column, 'Columns form details should contain id');
            $this->assertObjectHasAttribute('name', $column, 'Columns form details should contain name');
            $this->assertObjectHasAttribute('plugin', $column, 'Columns form details should contain plugin');
            $this->assertObjectHasAttribute('label', $column, 'Columns form details should contain label');
            $this->assertObjectHasAttribute('group_id', $column, 'Columns form details should contain group form id');
            $this->assertObjectHasAttribute('params', $column, 'Columns form details should contain params');

            $this->assertObjectNotHasAttribute('hidden', $column, 'Columns form details should not contain hidden attribute');
            $this->assertObjectNotHasAttribute('published', $column, 'Columns form details should not contain published attribute');
        }
    }

    /**
     * @covers EmundusModelUsers::getJoomlaUserColumns
     * Function getJoomlaUserColumns return an array of joomla user columns
     * It should return an id, name, plugin, label for each element
     * @return void
     */
    public function testgetJoomlaUserColumns() {

        $columns = $this->m_users->getJoomlaUserColumns();
        foreach ($columns as $column)
        {
            $this->assertObjectHasAttribute('id', $column, 'Joomla user columns details should contain id');
            $this->assertObjectHasAttribute('name', $column, 'Joomla user columns details should contain name');
            $this->assertObjectHasAttribute('plugin', $column, 'Joomla user columns details should contain plugin');
            $this->assertObjectHasAttribute('label', $column, 'Joomla user columns details should contain label');

            $this->assertObjectNotHasAttribute('value', $column, 'Joomla user columns details should not contain value attribute');
        }
    }

    /**
     * @covers EmundusModelUsers::getAllInformationsToExport
     * Function getAllInformationsToExport return an array of user data and columns data
     * It should return an array of 2 array (each element of both array containing an array too)
     * @return void
     * @throws Exception
     */
    public function testgetAllInformationsToExport()
    {
        $user_id = $this->h_sample->getSampleUser();

        $this->assertEmpty($this->m_users->getAllInformationsToExport(0), 'Passing an incorrect user id should return null');
        $data = $this->m_users->getAllInformationsToExport($user_id);
        $this->assertNotEmpty($data, 'Passing a correct user id should return an array of data');
        $this->assertCount(2, $data, 'Data array should contain 2 elements');

        foreach ($data as $key => $dataType) {
            foreach ($dataType as $element) {

                $this->assertObjectHasAttribute('id', $element, 'Data array details should contain id');
                $this->assertObjectHasAttribute('name', $element, 'Data array details should contain name');
                $this->assertObjectHasAttribute('plugin', $element, 'Data array details should contain plugin');
                $this->assertObjectHasAttribute('label', $element, 'Data array details should contain label');

                $this->assertObjectNotHasAttribute('hidden', $element, 'Data array form details should not contain hidden attribute');
                $this->assertObjectNotHasAttribute('published', $element, 'Data array form details should not contain published attribute');

                if ($key === 'j_columns') {
                    $this->assertObjectNotHasAttribute('group_id', $element, 'user_data array details should not contain group form id !');
                    $this->assertObjectHasAttribute('value', $element, 'user_data array details should contain value');
                }
                else
                {
                    $this->assertObjectHasAttribute('group_id', $element, 'column array details should contain group form id !');
                    $this->assertObjectNotHasAttribute('value', $element, 'column array details should not contain value');
                }
            }
        }
    }

    /**
     * @covers EmundusModelUsers::getUserDetails
     * @return void
     * It should return an array of all columns (user data and columns data)
     * @throws Exception
     */
    public function testgetUserDetails()
    {
        $user_id = $this->h_sample->getSampleUser();

        $this->assertEmpty($this->m_users->getUserDetails(0), 'Passing an incorrect user id should return null');
        $data = $this->m_users->getUserDetails($user_id);
        $dataBefore = $this->m_users->getAllInformationsToExport($user_id);
        $numberColumns = 0;
        $array_label = array();

        // Count the number of columns  we should have
        foreach ($dataBefore as $dataType) {
            foreach ($dataType as $element) {
                $array_label[] = $element->label;
                $numberColumns += 1;
            }
        }
        foreach ($array_label as $name) {
            $this->assertArrayHasKey($name, $data, "Key '$name' not found in data array");
        }

        $this->assertCount($numberColumns, $data, 'Not the number of columns expected');

    }

	/**
	 * @covers EmundusModelUsers::getUsersByIds
	 * Function getUsersByIds return an array of user(s) details
	 * It should return id, name, email, username, registerDate, params etc... but not password
	 * @return void
	 */
	public function testgetUsersByIds() {

		$user1_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr' );
		$user2_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr' );

		$this->assertEmpty($this->m_users->getUsersByIds(0), 'Passing an incorrect user id should return null');
		$users_array = array($user1_id, $user2_id);
		$data = $this->m_users->getUsersByIds($users_array);
		$this->assertNotEmpty($data, 'Passing correct users id should return an array of data');
		$this->assertCount(count($data), $data, 'Data array should contain as many elements as the number of users id passed');

		foreach ($data as $user_details) {
			$this->assertObjectHasAttribute('id', $user_details, 'User details should contain id');
			$this->assertObjectHasAttribute('name', $user_details, 'User details should contain name');
			$this->assertObjectHasAttribute('email', $user_details, 'User details should contain email');
			$this->assertObjectHasAttribute('username', $user_details, 'User details should contain groups');
			$this->assertObjectHasAttribute('registerDate', $user_details, 'User details should contain profile');
			$this->assertObjectHasAttribute('params', $user_details, 'User details should contain columns');

			$this->assertObjectNotHasAttribute('password', $user_details, 'User details should not contain password');
		}
	}
}

