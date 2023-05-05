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
include_once (JPATH_SITE . '/components/com_emundus/models/checklist.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelChecklistTest extends TestCase
{
    private $m_checklist;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_checklist = new EmundusModelChecklist;
    }

	public function testFoo()
	{
		$this->assertTrue(true);
	}

	public function testgetAttachmentsList()
	{
		$attachments = $this->m_checklist->getAttachmentsList();
		$this->assertIsArray($attachments);

		// set session
		$user = new stdClass();
		$user->id = JFactory::getUser()->id;
		$user->profile = 1;
		$user->fnum = '00000000';
		$user->applicant_id = 1;
		$user->email = '';
		$user->fnums = array('00000000');

		JFactory::getSession()->set('emundusUser', $user);

		$attachments = $this->m_checklist->getAttachmentsList();
		$this->assertIsArray($attachments);
	}
}
