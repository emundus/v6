<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use Joomla\CMS\Factory;
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
include_once(JPATH_ROOT . '/components/com_emundus/models/profile.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

session_start();

class EmundusModelApplicationTest extends TestCase
{
	private $app;
    private $m_application;
	private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

		$this->app = JFactory::getApplication();
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

		$first_attachment_id = $this->h_sample->createSampleAttachment();
		$second_attachment_id = $this->h_sample->createSampleAttachment();
		$this->h_sample->createSampleUpload($fnum, $campaign_id, $user_id, $first_attachment_id);
		$this->h_sample->createSampleUpload($fnum, $campaign_id, $user_id, $second_attachment_id);
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
		$attachment_id = $this->h_sample->createSampleAttachment();

		$data = [];
		$data['key'] = ['fnum', 'user_id', 'campaign_id', 'attachment_id', 'filename', 'local_filename', 'timedate', 'can_be_deleted', 'can_be_viewed'];
		$data['value'] = [$fnum, $user_id, $campaign_id, $attachment_id, 'test.pdf', 'test.pdf', date('Y-m-d H:i:s'), 1, 1];

		$upload = $this->m_application->uploadAttachment($data);
		$this->assertGreaterThan(0, $upload);
	}

	public function testgetTabs() {
		$tabs = $this->m_application->getTabs(0);
		$this->assertSame([], $tabs);
	}

	public function testdeleteTab() {
		$deleted = $this->m_application->deleteTab(0, 0);
		$this->assertSame(false, $deleted);
	}

	public function testmoveToTab() {
		$moved = $this->m_application->moveToTab(0, 0);
		$this->assertSame(false, $moved);
	}

	public function testupdateTabs() {
		$updated = $this->m_application->updateTabs([], 0);
		$this->assertFalse($updated, 'Missing user id');

		$updated = $this->m_application->updateTabs([], 95);
		$this->assertFalse($updated, 'No tabs to update');

		$tab = new stdClass();
		$tab->id = 9999;
		$tab->name = 'Test';
		$tab->ordering = 1;

		$updated = $this->m_application->updateTabs([$tab], 0);
		$this->assertFalse($updated, 'Tab does not exist');

		$tab->id = $this->m_application->createTab('Test', 95);
		$this->assertNotEmpty($tab->id);

		$updated = $this->m_application->updateTabs([$tab], 100);
		$this->assertFalse($updated, 'Tab is not owned by user');

		$updated = $this->m_application->updateTabs([$tab], 95);
		$this->assertTrue($updated, 'Tab updated');

		$tab->id = $tab->id . ' OR 1=1';
		$updated = $this->m_application->updateTabs([$tab], 0);
		$this->assertFalse($updated, 'SQL Injection impossible');
	}

	/**
	 * @covers EmundusModelApplication::isTabOwnedByUser
	 * @return void
	 */
	public function testisTabOwnedByUser() {
		$owned = $this->m_application->isTabOwnedByUser(0, 95);
		$this->assertSame(false, $owned, 'An invalid tab id should return false');

		$owned = $this->m_application->isTabOwnedByUser(1);
		$this->assertSame(false, $owned, 'An invalid user id should return false');

		$tab = new stdClass();
		$tab->name = 'Unit Test ' . time();
		$tab->ordering = 9999;
		$tab->id = $this->m_application->createTab('Test', 95);
		$this->assertNotEmpty($tab->id);

		$owned = $this->m_application->isTabOwnedByUser($tab->id, 95);
		$this->assertSame(true, $owned, 'Tab is owned by user');

		$owned = $this->m_application->isTabOwnedByUser($tab->id, 0);
		$this->assertSame(false, $owned, 'Tab is not owned by user');

		$owned = $this->m_application->isTabOwnedByUser(9999 . ' OR 1=1', 95);
		$this->assertSame(false, $owned, 'SQL Injection impossible');
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

	public function testgetApplicationMenu() {
		$username = 'test-application-coordinator-' . rand(0, 1000) . '@emundus.fr';
		$coordinator = $this->h_sample->createSampleUser(9, $username, 'test1234',  [2,7]);
		$username = 'test-application-applicant-' . rand(0, 1000) . '@emundus.fr';
		$applicant = $this->h_sample->createSampleUser(9, $username);

		$menus = $this->m_application->getApplicationMenu($coordinator);
		$this->assertNotEmpty($menus, 'A coordinator should have access to the application menu');

		$menus = $this->m_application->getApplicationMenu($applicant);
		$this->assertEmpty($menus, 'An applicant should not have access to the application menu');
	}

	public function testgetUserCampaigns() {
		$user_campaigns = $this->m_application->getUserCampaigns(95);
		$this->assertIsArray($user_campaigns, 'getUserCampaigns should return an array');

		$program = $this->h_sample->createSampleProgram();
		$campaign_published = $this->h_sample->createSampleCampaign($program);
		$this->h_sample->createSampleFile($campaign_published, 95);

		$user_campaigns = $this->m_application->getUserCampaigns(95, null, false);
		$this->assertNotEmpty($user_campaigns, 'getUserCampaigns should return an array with at least one campaign');

		$found = false;
		foreach ($user_campaigns as $campaign) {
			if ($campaign->id == $campaign_published) {
				$found = true;
				break;
			}
		}
		$this->assertTrue($found, 'getUserCampaigns should return the created campaign');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__emundus_setup_campaigns')
			->set('published = 0')
			->where('id = ' . $db->quote($campaign_published));
		$db->setQuery($query);
		$db->execute();

		$user_campaigns = $this->m_application->getUserCampaigns(95);
		$found = false;
		foreach ($user_campaigns as $campaign) {
			if ($campaign->id == $campaign_published) {
				$found = true;
				break;
			}
		}
       $this->assertFalse($found, 'getUserCampaigns should not return the unpublished campaign');

		$user_campaigns = $this->m_application->getUserCampaigns(95,null, false);
		$found = false;
		foreach ($user_campaigns as $campaign) {
			if ($campaign->id == $campaign_published) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'getUserCampaigns should return the unpublished campaign if only_published is false');

		$campaign_not_attached_applicant = $this->h_sample->createSampleCampaign($program, true);
		$user_campaigns = $this->m_application->getUserCampaigns(95, $campaign_not_attached_applicant);
		$this->assertEmpty($user_campaigns, 'getUserCampaigns should not return the campaign if the applicant is not attached to it');

	    $query->clear();
		$query->select('distinct(campaign_id)')
				->from('#__emundus_campaign_candidature')
				->where('applicant_id = ' . $db->quote(95));
		$db->setQuery($query);
		$all_user_campaigns = $db->loadColumn();

		$user_campaigns = $this->m_application->getUserCampaigns(95);

		foreach ($user_campaigns as $campaign) {
			$this->assertContains($campaign->id, $all_user_campaigns, 'getUserCampaigns should return all campaigns attached to the applicant');
		}
	}

	public function testgetAttachmentsProgress() {
		$db = Factory::getDbo();

		$this->assertSame($this->m_application->getAttachmentsProgress([]), 0.0, 'getAttachmentsProgress should return 0 if application file does not exist');

		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign, 95);
        $this->assertNotEmpty($fnum);

		$this->assertSame($this->m_application->getAttachmentsProgress($fnum), 0.0, 'getAttachmentsProgress should return 0.0 if no attachments are found');

		$attachment = $this->h_sample->createSampleAttachment();
        $this->assertGreaterThan(0, $attachment);

		$upload_created = $this->h_sample->createSampleUpload($fnum, $campaign, 95, $attachment);
        $this->assertTrue($upload_created);

		$insert = [
			'profile_id' => 9,
			'attachment_id' => $attachment,
			'mandatory' => 1,
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_setup_attachment_profiles', $insert);

		$this->assertGreaterThan(0.0, $this->m_application->getAttachmentsProgress($fnum), 'getAttachmentsProgress should return more than 0.0 if an attachment were found');

		$query = $db->getQuery(true);
		$query->select('count(id)')
			->from('#__emundus_setup_attachment_profiles')
			->where('profile_id = 9')
			->where('mandatory = 1');
		$db->setQuery($query);
		$mandatory_attachments = $db->loadResult();
		$percentage = 1/$mandatory_attachments * 100;

		$this->assertSame($this->m_application->getAttachmentsProgress($fnum), floor($percentage), 'getAttachmentsProgress should return exactly '.$percentage.' if one mandatory attachment is found');

		$this->assertIsArray($this->m_application->getAttachmentsProgress([$fnum]), 'getAttachmentsProgress should return an array if fnum is an array');

		$fnum_2 = $this->h_sample->createSampleFile($campaign, 95);

		$this->assertSame(count($this->m_application->getAttachmentsProgress([$fnum, $fnum_2])),2, 'getAttachmentsProgress should return 2 entries');
	}
}

