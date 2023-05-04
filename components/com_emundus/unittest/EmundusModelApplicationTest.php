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
		$this->assertSame(false, $updated, 'No tabs to update');

		$updated = $this->m_application->updateTabs([], 95);
		$this->assertSame(false, $updated, 'No tabs to update');

		$tab = new stdClass();
		$tab->id = 999;
		$tab->name = 'Test';
		$tab->ordering = 1;

		$updated = $this->m_application->updateTabs([['id' => 1, 'name' => 'Test', 'ordering' => 1]], 0);
		$this->assertSame(false, $updated, 'Missing user id');

		$updated = $this->m_application->updateTabs([['id' => 1, 'name' => 'Test', 'ordering' => 1]], 95);
		$this->assertSame(false, $updated, );

		$tab->id = $this->m_application->createTab('Test', 95);
		$this->assertNotEmpty($tab->id);

		$updated = $this->m_application->updateTabs([$tab], 95);
		$this->assertSame(true, $updated, 'Tab updated');

		$tab->id = $tab->id . ' OR 1=1';
		$updated = $this->m_application->updateTabs([$tab], 0);
		$this->assertSame(false, $updated, 'SQL Injection impossible');
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
}
