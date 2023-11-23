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

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');

include_once(JPATH_ROOT . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_ROOT . '/components/com_emundus/models/vote.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

class EmundusModelVoteTest extends TestCase
{
	private $app;
	private $db;
    private $m_vote;

	private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
		
	    $this->app = Factory::getApplication();
		$this->db = Factory::getDbo();
		
	    $this->h_sample = new EmundusUnittestHelperSamples;
		
        $this->m_vote = new EmundusModelVote();
    }

	public function testGetVotesByUser()
	{
		$ip = '1.1.1.1';
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);
		$username = 'test-candidat-' . rand(0, 1000) . '@emundus.fr';
		$applicant = $this->h_sample->createSampleUser(9, $username);
		$file = $this->h_sample->createSampleFile($campaign,$applicant);

		$query = $this->db->getQuery(true);
		$query->select('id')
			->from($this->db->quoteName('#__emundus_campaign_candidature'))
			->where($this->db->quoteName('fnum') . ' = ' . $this->db->quote($file));
		$this->db->setQuery($query);
		$ccid = $this->db->loadResult();

		// 1. En tant qu'utilisateur non connecté, je n'ai pas encore voté
		$guest_user = Factory::getUser();
		$votes = $this->m_vote->getVotesByUser($guest_user,null,$ip);
		$this->assertIsArray($votes);
		$this->assertEmpty($votes);

		// 2. En tant qu'utilisateur non connecté, je viens de voter
		$this->m_vote->vote('test-votant-guest@emundus.fr', $ccid, $guest_user->id,$ip);
		$votes = $this->m_vote->getVotesByUser($guest_user,null,$ip);
		$this->assertNotEmpty($votes);

		// 3. En tant qu'utilisateur connecté, je n'ai pas encore voté
		$username = 'test-votant-' . rand(0, 1000) . '@emundus.fr';
		$uid = $this->h_sample->createSampleUser(9, $username);
		$registered_user = Factory::getUser($uid);
		$votes = $this->m_vote->getVotesByUser($registered_user,null,$ip);
		$this->assertIsArray($votes);
		$this->assertEmpty($votes);

		// 4. En tant qu'utilisateur connecté, je viens de voter
		$this->m_vote->vote($registered_user->email, $ccid, $registered_user->id,$ip);

		$votes = $this->m_vote->getVotesByUser($registered_user,null,$ip);
		$this->assertNotEmpty($votes);

		$this->h_sample->deleteSampleUser($registered_user->id);
		$this->h_sample->deleteSampleUser($applicant);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);

		$query = $this->db->getQuery(true);
		$query->delete($this->db->quoteName('#__emundus_vote'))
			->where($this->db->quoteName('user') . ' = ' . $this->db->quote($registered_user->id))
			->orWhere($this->db->quoteName('email') . ' LIKE ' . $this->db->quote('test-votant-guest@emundus.fr'));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function testVote()
	{
		$ip = '1.1.1.1';
		$program = $this->h_sample->createSampleProgram();
		$campaign = $this->h_sample->createSampleCampaign($program);
		$username = 'test-candidat-' . rand(0, 1000) . '@emundus.fr';
		$applicant = $this->h_sample->createSampleUser(9, $username);
		$file = $this->h_sample->createSampleFile($campaign,$applicant);

		$query = $this->db->getQuery(true);
		$query->select('id')
			->from($this->db->quoteName('#__emundus_campaign_candidature'))
			->where($this->db->quoteName('fnum') . ' = ' . $this->db->quote($file));
		$this->db->setQuery($query);
		$ccid = $this->db->loadResult();

		// 1. En tant qu'utilisateur non connecté, je peux voter pour un projet
		$guest_user = Factory::getUser();
		$this->assertTrue($this->m_vote->vote('test-votant-guest@emundus.fr', $ccid, $guest_user->id,$ip));

		// 2. En tant qu'utilisateur non connecté je ne peux pas voter 2 fois pour le même projet
		$this->assertFalse($this->m_vote->vote('test-votant-guest@emundus.fr', $ccid, $guest_user->id,$ip));

		// 3. En tant qu'utilisateur connecté, je peux voter un projet
		$username = 'test-votant-' . rand(0, 1000) . '@emundus.fr';
		$uid = $this->h_sample->createSampleUser(9, $username);
		$registered_user = Factory::getUser($uid);
		$this->assertTrue($this->m_vote->vote($registered_user->email, $ccid, $registered_user->id,$ip));

		// 4. En tant qu'utilisateur connecté je ne peux pas voter 2 fois pour le même projet
		$this->assertFalse($this->m_vote->vote($registered_user->email, $ccid, $registered_user->id,$ip));

		$this->h_sample->deleteSampleUser($registered_user->id);
		$this->h_sample->deleteSampleUser($applicant);
		$this->h_sample->deleteSampleCampaign($campaign);
		$this->h_sample->deleteSampleProgram($program['programme_id']);

		$query = $this->db->getQuery(true);
		$query->delete($this->db->quoteName('#__emundus_vote'))
			->where($this->db->quoteName('user') . ' = ' . $this->db->quote($registered_user->id))
			->orWhere($this->db->quoteName('email') . ' LIKE ' . $this->db->quote('test-votant-guest@emundus.fr'));
		$this->db->setQuery($query);
		$this->db->execute();
	}
}
