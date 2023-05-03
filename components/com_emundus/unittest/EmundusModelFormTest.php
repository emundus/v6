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
}
