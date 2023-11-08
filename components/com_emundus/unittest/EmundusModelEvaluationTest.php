<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use PHPUnit\Framework\TestCase;

ini_set('display_errors', false);
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');
include_once(JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_SITE . '/components/com_emundus/models/evaluation.php');

jimport('joomla.user.helper');
jimport('joomla.application.application');
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelEvaluationTest extends TestCase
{
	private $m_evaluation;
	private $h_samples;

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->m_evaluation = new EmundusModelEvaluation;
		$this->h_samples    = new EmundusUnittestHelperSamples;
	}

	public function testFoo()
	{
		$this->assertTrue(true);
	}

	public function testgetLettersByProgrammesStatusCampaigns()
	{
		$letters = $this->m_evaluation->getLettersByProgrammesStatusCampaigns();
		$this->assertIsArray($letters, 'getLettersByProgrammesStatusCampaigns should return an array');
		$this->assertEmpty($letters, 'Without parameters, getLettersByProgrammesStatusCampaigns should return an empty array');

		$letter_attachement_id = $this->h_samples->createSampleAttachment();
		$program               = $this->h_samples->createSampleProgram();

		$campaign  = $this->h_samples->createSampleCampaign($program);
		$letter_id = $this->h_samples->createSampleLetter($letter_attachement_id, 2, [$program['programme_code']], [0], [$campaign]);

		$user = $this->h_samples->createSampleUser(9, 'user.test' . rand(0, 1000) . '@emundus.fr');
		$fnum = $this->h_samples->createSampleFile($campaign, $user);

		$letters = $this->m_evaluation->getLettersByProgrammesStatusCampaigns([$program['programme_code']], [0], [$campaign]);
		$this->assertNotEmpty($letters, 'I should retrieve letters by programme status and campaign');

		$letter_ids       = array_column($letters, 'id');
		$letter_id_string = (string) $letter_id;
		$this->assertContains($letter_id_string, $letter_ids, 'I should retrieve the created letter id in the list of letters');

	}

	public function testgetLetterTemplateForFnum()
	{
		$letters = $this->m_evaluation->getLetterTemplateForFnum('');
		$this->assertIsArray($letters, 'getLetterTemplateForFnum should return an array');
		$this->assertEmpty($letters, 'Without parameters, getLetterTemplateForFnum should return an empty array');

		$letter_attachement_id = $this->h_samples->createSampleAttachment();
		$program               = $this->h_samples->createSampleProgram();

		$campaign  = $this->h_samples->createSampleCampaign($program);
		$letter_id = $this->h_samples->createSampleLetter($letter_attachement_id, 2, [$program['programme_code']], [0], [$campaign]);

		$user = $this->h_samples->createSampleUser(9, 'user.test' . rand(0, 1000) . '@emundus.fr');
		$fnum = $this->h_samples->createSampleFile($campaign, $user);

		$letters = $this->m_evaluation->getLetterTemplateForFnum($fnum, [$letter_attachement_id]);
		$this->assertNotEmpty($letters, 'I should retrieve letters by fnum and letter attachement id');
	}
}
