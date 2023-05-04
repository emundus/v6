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
include_once (JPATH_SITE . '/components/com_emundus/models/application.php');

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

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_application = new EmundusModelApplication;
    }

    public function testGetApplicantInfos(){
        $applicant_infos = $this->m_application->getApplicantInfos(0, []);
        $this->assertSame([], $applicant_infos);

        $applicant_infos = $this->m_application->getApplicantInfos(62, ['jos_users.id']);
        $this->assertNotEmpty($applicant_infos);
        $this->assertSame(intval($applicant_infos['id']), 62);
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
