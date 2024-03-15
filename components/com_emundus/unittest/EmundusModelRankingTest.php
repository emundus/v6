<?php

use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');
include_once (JPATH_SITE . '/components/com_emundus/models/ranking.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelRankingTest extends TestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->m_ranking = new EmundusModelRanking;
        $this->h_sample = new EmundusUnittestHelperSamples();
        parent::__construct($name, $data, $dataName);
    }

    public function testFoo()
    {
        $this->assertTrue(true);
    }

    public function testGetFilesUserCanRank()
    {
        $files = $this->m_ranking->getFilesUserCanRank(95);
        $this->assertIsArray($files);
    }

    public function testUpdateFileRank()
    {
        $ranker_user = 95;

        $updated = $this->m_ranking->updateFileRanking(0, 95, 1, 1);
        $this->assertFalse($updated, "I should not be able to rank a file that does not exist.");

        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);

        // Create a file for another user
        $another_user = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 100000) . '@emundus.test.fr');
        $fnum_other = $this->h_sample->createSampleFile($campaign_id, $another_user);

        $db = JFactory::getDbo();
        $query = "SELECT id FROM #__emundus_campaign_candidature WHERE fnum = '$fnum_other'";
        $db->setQuery($query);
        $id = $db->loadResult();

        // Update should work
        $updated = $this->m_ranking->updateFileRanking($id, $ranker_user, 1, 1);
        $this->assertTrue($updated, "I should be able to rank a file that I did not apply for.");

        // Create a file for ranker user, and try to rank it
        $fnum = $this->h_sample->createSampleFile($campaign_id, $ranker_user);
        $query = "SELECT id FROM #__emundus_campaign_candidature WHERE fnum = '$fnum'";
        $db->setQuery($query);
        $id = $db->loadResult();

        // I should catch an exception if I try to rank a file that I apply for.
        $this->expectException(Exception::class);
        $this->m_ranking->updateFileRanking($id, $ranker_user, 1, 1);
    }

    public function testAskUsersToLockRankings()
    {
        $current_user_id = 95;
        $users = [];
        $hierarchies = [];

        $response = $this->m_ranking->askUsersToLockRankings($current_user_id, $users, $hierarchies);
        $this->assertFalse($response['asked'], "I should not be able to ask users to lock rankings if there are no users or rankings.");

        $hierarchies = [9999];
        $response = $this->m_ranking->askUsersToLockRankings($current_user_id, [], $hierarchies);
        $this->assertFalse($response['asked'], "I should not be able to ask users to lock rankings if I am not allowed to view this ranking.");

        $users = [9999];
        $response = $this->m_ranking->askUsersToLockRankings($current_user_id, $users, []);
        $this->assertFalse($response['asked'], "I should not be able to ask users to lock rankings if user does not exist.");

        $hierarchies = [2];
        $response = $this->m_ranking->askUsersToLockRankings($current_user_id, $users, $hierarchies);
        $this->assertTrue($response['asked'], "I should be able to ask users to lock rankings.");

        /*
         * Cannot assert on that because mail functions are not supported in tests
        $this->assertEquals(1, sizeof($response['asked_to']), "1 person should have been asked to");
        $hierarchies = [2, 3];
        $response = $this->m_ranking->askUsersToLockRankings($current_user_id, $users, $hierarchies);
        $this->assertEquals(2, sizeof($response['asked_to']), "2 people should have been asked to");
        */


        // if i pass no user expect exception
        $this->expectException(Exception::class);
        $this->m_ranking->askUsersToLockRankings(0, $users, $hierarchies);
    }
}