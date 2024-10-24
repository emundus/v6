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
include_once (JPATH_SITE . '/components/com_emundus/models/form.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelFormTest extends TestCase
{
    private $m_form;
	private $h_sample;


	public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_form = new EmundusModelForm;
	    $this->h_sample = new EmundusUnittestHelperSamples;
    }

	/**
	 * @test
	 * @covers EmundusModelForm::copyAttachmentsToNewProfile()
	 */
	public function testCopyAttachmentsToNewProfile() {
		$base_profile = 9;
		$fake_new_profile = 64567657;

		$copy = $this->m_form->copyAttachmentsToNewProfile(0, $fake_new_profile);
		$this->assertFalse($copy, 'Copy attachments requires a valid old profile id');

		$copy = $this->m_form->copyAttachmentsToNewProfile($base_profile, 0);
		$this->assertFalse($copy, 'Copy attachments requires a valid new profile id');

		$copy = $this->m_form->copyAttachmentsToNewProfile($base_profile, $fake_new_profile);
		$this->assertFalse($copy, 'Copy attachments fails because new profile does not exist');

		$fake_new_profile = $this->h_sample->duplicateSampleProfile($base_profile);
		$this->assertNotEmpty($fake_new_profile, 'Fake new profile created');
		$this->assertNotSame($fake_new_profile, 64567657, 'Fake new profile is not the same as the fake id');
		$copy = $this->m_form->copyAttachmentsToNewProfile($base_profile, $fake_new_profile);
		$this->assertTrue($copy, 'Copy attachments succeeds');
	}

	/**
	 * @test
	 * @covers EmundusModelForm::duplicateForm()
	 */
	public function testDuplicateForm() {
		$pids = [0];
		$duplicate = $this->m_form->duplicateForm($pids);
		$this->assertFalse($duplicate, 'Duplicate form requires a valid profile id');

		// TODO: test duplicate form, error coming from cms language
	}

	public function testcreateFormEval()
	{
		$form_id = $this->m_form->createFormEval();
		$this->assertNotEmpty($form_id, 'Evaluation form creation succeeds');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('jfe.name, jfe.published')
			->from($db->quoteName('#__fabrik_elements', 'jfe'))
			->leftJoin($db->quoteName('#__fabrik_formgroup', 'jffg') . ' ON (' . $db->quoteName('jffg.group_id') . ' = ' . $db->quoteName('jfe.group_id') . ')')
			->where($db->quoteName('jffg.form_id') . ' = ' . $db->quote($form_id));

		$db->setQuery($query);
		$elements = $db->loadAssocList();

		$this->assertNotEmpty($elements, 'Evaluation form elements are not empty');

		$element_names = array_column($elements, 'name');
		$this->assertContains('id', $element_names, 'Evaluation form elements contains id');
		$this->assertContains('time_date', $element_names, 'Evaluation form elements contains time_date');
		$this->assertContains('fnum', $element_names, 'Evaluation form elements contains fnum');
		$this->assertContains('user', $element_names, 'Evaluation form elements contains user');
		$this->assertContains('student_id', $element_names, 'Evaluation form elements contains student_id');

		foreach($elements as $element) {
			if (in_array($element['name'], ['id', 'time_date', 'fnum', 'user', 'student_id'])) {
				$this->assertSame(1, intval($element['published']), 'Evaluation default form elements are published');
			}
		}
	}

	public function testGetProgramsByForm()
	{
		$form_id = $this->m_form->createFormEval();
		$program = $this->h_sample->createSampleProgram();

		$programs = $this->m_form->getProgramsByForm($form_id);
		$this->assertEmpty($programs, 'Programs are empty');

		$this->m_form->associateFabrikGroupsToProgram($form_id, [$program['programme_code']]);

		$programs = $this->m_form->getProgramsByForm($form_id);
		$this->assertNotEmpty($programs, 'Programs are not empty');

		$this->assertSame($program['programme_code'], $programs[0]['code'], 'Programs are the same');
	}

	public function testAssociateFabrikGroupsToProgram()
	{
		$form_id = $this->m_form->createFormEval();
		$program = $this->h_sample->createSampleProgram();

		$associate = $this->m_form->associateFabrikGroupsToProgram($form_id, [$program['programme_code']]);
		$this->assertTrue($associate, 'Associate fabrik groups to program succeeds');

		$second_program = $this->h_sample->createSampleProgram('Program ' . rand(1, 10000));

		// We remove the association of the first program if we associate an other without adding the first one
		$associate = $this->m_form->associateFabrikGroupsToProgram($form_id, [$second_program['programme_code']]);
		$this->assertTrue($associate, 'We can associate fabrik groups to another program');

		$programs = $this->m_form->getProgramsByForm($form_id);
		$this->assertNotSame($program['programme_code'], $programs[0]['code'], 'First programe are not the same');
		$this->assertSame($second_program['programme_code'], $programs[0]['code'], 'Second program are the same');
	}

	public function testGetDatabaseJoinOptions()
	{
		// assert that the functions returns false if the table is not allowed
		$this->assertFalse($this->m_form->getDatabaseJoinOptions('jos_users', 'id', 'password'), 'Not allowed to access users table');
		$this->assertFalse($this->m_form->getDatabaseJoinOptions('jos_emundus_campaign_candidature', 'id', 'status'), 'Not allowed to access candidature table');

		$this->assertNotEmpty($this->m_form->getDatabaseJoinOptions('data_nationality', 'id', 'label_fr'), 'Allowed to access data_nationality table');
	}
}
