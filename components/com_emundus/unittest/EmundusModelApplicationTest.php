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
include_once(JPATH_SITE . '/components/com_emundus/helpers/access.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelApplicationTest extends TestCase
{
    private $m_application;
	private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_application = new EmundusModelApplication;
		$this->h_sample = new EmundusUnittestHelperSamples;
    }

    public function testGetApplicantInfos(){
        $applicant_infos = $this->m_application->getApplicantInfos(0, []);
        $this->assertSame([], $applicant_infos);

        $applicant_infos = $this->m_application->getApplicantInfos(62, ['jos_users.id']);
        $this->assertNotEmpty($applicant_infos);
        $this->assertSame(intval($applicant_infos['id']), 62);
    }

	public function testGetUserAttachmentsByFnum() {
		if (!defined('EMUNDUS_PATH_ABS')) {
			define('EMUNDUS_PATH_ABS', JPATH_ROOT);
		}

		$attachments = $this->m_application->getUserAttachmentsByFnum('');
		$this->assertSame([], $attachments);

		$user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$program = $this->h_sample->createSampleProgram();
		$campaign_id = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);
		$attachments = $this->m_application->getUserAttachmentsByFnum($fnum);
		$this->assertEmpty($attachments);

		$this->h_sample->createSampleUpload($fnum, $campaign_id, $user_id);
		$this->h_sample->createSampleUpload($fnum, $campaign_id, $user_id,2);
		$attachments = $this->m_application->getUserAttachmentsByFnum($fnum);
		$this->assertNotEmpty($attachments);
		$this->assertSame(count($attachments), 2);


		// attachments should contain 1 element with existsOnServer = false
		$current_attachment = $attachments[0];
		$this->assertSame($current_attachment->existsOnServer, false);

		// attachments should contain profiles attribute
		$this->assertObjectHasAttribute('profiles', $current_attachment);

		// if i use search parameter, only pertinent attachments should be returned
		$search = $attachments[0]->value;
		$attachments = $this->m_application->getUserAttachmentsByFnum($fnum, $search);
		$this->assertNotEmpty($attachments);
		$this->assertSame($attachments[0]->value, $search);
		$this->assertSame(count($attachments), 1);
	}

	public function testuploadAttachment() {
		$upload = $this->m_application->uploadAttachment([]);
		$this->assertSame($upload, false);

		$user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$program = $this->h_sample->createSampleProgram();
		$campaign_id = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

		$data = [];
		$data['key'] = ['fnum', 'user_id', 'campaign_id', 'attachment_id', 'filename', 'local_filename', 'timedate', 'can_be_deleted', 'can_be_viewed'];
		$data['value'] = [$fnum, $user_id, $campaign_id, 1, 'test.pdf', 'test.pdf', date('Y-m-d H:i:s'), 1, 1];

		$upload = $this->m_application->uploadAttachment($data);
		$this->assertGreaterThan(0, $upload);
	}

	/**
	 * @covers EmundusModelApplication::applicantCustomAction
	 * @return void
	 */
	public function testapplicantCustomAction() {
		$done = $this->m_application->applicantCustomAction(0, '');
		$this->assertSame($done, false, 'applicantCustomAction should return false if action and fnum are empty');

		$user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$program = $this->h_sample->createSampleProgram();
		$campaign_id = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

		$done = $this->m_application->applicantCustomAction(0, $fnum);
		$this->assertSame($done, false, 'applicantCustomAction should return false if action is empty');

		// get module params
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, params')
			->from('#__modules')
			->where('module LIKE ' . $db->quote('mod_emundus_applications'))
			->where('published = 1');

		$db->setQuery($query);
		$module = $db->loadAssoc();
		$params = json_decode($module['params'], true);

		$params['mod_em_application_custom_actions'] = [
			'mod_em_application_custom_action1' => [
				'mod_em_application_custom_action_new_status' => 1,
				'mod_em_application_custom_action_status' => [0]
			]
		];

		// update module params
		$query = $db->getQuery(true);
		$query->update('#__modules')
			->set('params = ' . $db->quote(json_encode($params)))
			->where('id = ' . $db->quote($module['id']));

		$db->setQuery($query);
		$db->execute();

		$done = $this->m_application->applicantCustomAction(0, $fnum);
		$this->assertSame($done, false, 'applicantCustomAction should return false if action is not found in module params');

		$done = $this->m_application->applicantCustomAction('mod_em_application_custom_action1', $fnum);
		$this->assertSame($done, true, 'Custom action should be done because file is in correct status');

		$done = $this->m_application->applicantCustomAction('mod_em_application_custom_action1', $fnum);
		$this->assertSame($done, false, 'Action should no longer work because file status has changed');
	}
}
