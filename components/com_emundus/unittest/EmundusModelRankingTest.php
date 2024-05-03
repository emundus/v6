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

    public function testCreateHierarchy() {
        $sys_hierarchy = $this->m_ranking->createHierarchy('Hiérarchie Admin', 0, 1);
        $this->assertIsInt($sys_hierarchy);

        $coord_hierarchy = $this->m_ranking->createHierarchy('Hiérarchie coord', 0, 2, $sys_hierarchy);
        $this->assertIsInt($coord_hierarchy);

        $this->expectException(Exception::class); // i should not be able to create on false profile
        $hierarchy = $this->m_ranking->createHierarchy('Hiérarchie sur profil inexistant', 1, 9999);
    }

    public function testGetUserHierarchy() {
        $hierarchy = $this->m_ranking->getUserHierarchy(95, false);
        $this->assertIsNumeric($hierarchy);
    }

    public function testUpdateHierarchy() {
        $coord_hierarchy = $this->m_ranking->getUserHierarchy(95, false);
        $this->assertNotEmpty($coord_hierarchy);
        $sys_hierarchy = $this->m_ranking->getUserHierarchy(62, false);
        $this->assertNotEmpty($sys_hierarchy);

        $updated = $this->m_ranking->updateHierarchy($sys_hierarchy, ['visible_hierarchies' => [$coord_hierarchy]]);
        $this->assertTrue($updated);
    }

    public function testGetFilesUserCanRank()
    {
        $files = $this->m_ranking->getFilesUserCanRank(95);
        $this->assertIsArray($files);
    }

    public function testUpdateFileRank()
    {
        $ranker_user = 95;
        $ranker_hierarchy = $this->m_ranking->getUserHierarchy(95, false);

        $updated = $this->m_ranking->updateFileRanking(0, 95, 1, $ranker_hierarchy);
        $this->assertFalse($updated, "I should not be able to rank a file that does not exist.");

        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);

        // Create a file for another user
        $another_user = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 100000) . '@emundus.test.fr');
        $fnum_1 = $this->h_sample->createSampleFile($campaign_id, $another_user, true, true);

        // Update should work
        $updated = $this->m_ranking->updateFileRanking($fnum_1, $ranker_user, 1, $ranker_hierarchy);
        $this->assertTrue($updated, "I should be able to rank a file that I did not apply for.");

        $fnum_2 = $this->h_sample->createSampleFile($campaign_id, $another_user, true, true);
        $this->assertNotEmpty($fnum_2);

        $updated = $this->m_ranking->updateFileRanking($fnum_2, $ranker_user, 1, $ranker_hierarchy);
        $this->assertTrue($updated, "I should be able to rank a file that I did not apply for and place it on a position that has already been attributed.");

        $old_first_position_new_rank = $this->m_ranking->getFileRanking($fnum_1, $ranker_user, $ranker_hierarchy);
        $this->assertEquals(2, $old_first_position_new_rank, "The file that was first should now be second.");

        $fnum_3 = $this->h_sample->createSampleFile($campaign_id, $another_user, true, true);
        $this->assertNotEmpty($fnum_3);
        $updated = $this->m_ranking->updateFileRanking($fnum_3, $ranker_user, 3, $ranker_hierarchy);
        $this->assertTrue($updated, "I should be able to rank a file that I did not apply for and place it on a new position");
        $third_position = $this->m_ranking->getFileRanking($fnum_3, $ranker_user, $ranker_hierarchy);
        $this->assertEquals($third_position, $third_position, "Position is coherent");

        $updated = $this->m_ranking->updateFileRanking($fnum_1, $ranker_user, -1, $ranker_hierarchy);
        $this->assertTrue($updated, "I should be able to unrank a file that I did not apply for.");

        $old_third_position_new_rank = $this->m_ranking->getFileRanking($fnum_3, $ranker_user, $ranker_hierarchy);
        $this->assertEquals(2, $old_third_position_new_rank, "The file that was third should now be second, because previous second one has been unranked.");

        // Create a file for ranker user, and try to rank it
        // I should catch an exception if I try to rank a file that I apply for.
        $id = $this->h_sample->createSampleFile($campaign_id, $ranker_user, true, true);
        $this->expectException(Exception::class);
        $this->m_ranking->updateFileRanking($id, $ranker_user, 1, $ranker_hierarchy);
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

        $hierarchies = [$this->m_ranking->getUserHierarchy($current_user_id, false)];
        $users = [$current_user_id];
        $response = $this->m_ranking->askUsersToLockRankings(62, $users, $hierarchies);

        //$this->assertTrue($response['asked'], "I should be able to ask users to lock rankings.");

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