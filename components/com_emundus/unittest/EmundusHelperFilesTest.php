<?php

use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');
include_once(JPATH_SITE . '/components/com_emundus/helpers/files.php');

jimport('joomla.user.helper');
jimport('joomla.application.application');
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();


class EmundusHelperFilesTest extends TestCase {

	private $h_files;

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->h_files = new EmundusHelperFiles;
	}

	public function testFoo()
	{
		$foo = true;
		$this->assertSame(true, $foo);
	}

	/**
	 * @test
	 * @covers EmundusHelperFiles::createFnum
	 */
	public function testCreateFnum() {
		$this->assertSame('', EmundusHelperFiles::createFnum(0, 0, false), 'Create fnum with wrong campaign_id and user_id returns empty');
		$this->assertSame('', EmundusHelperFiles::createFnum(0, 95, false), 'Create fnum with wrong campaign_id returns empty');

		if (JFactory::getUser()->id) {
			$this->assertNotEmpty(EmundusHelperFiles::createFnum(1, 0, false), 'Create fnum with empty user_id  will use current user_id and returns not empty');
		} else {
			$this->assertSame('', EmundusHelperFiles::createFnum(1, 0, false), 'Create fnum with nio user id connected or given returns empty');
		}
		$this->assertNotEmpty(EmundusHelperFiles::createFnum(1, 95, false), 'Create fnum with correct campaign_id and user_id returns not empty');
		$this->assertNotEmpty(EmundusHelperFiles::createFnum(1, 95), 'Create fnum with correct campaign_id and user_id and redirect to true returns not empty');
	}

	/**
	 * @test
	 * @covers EmundusHelperFiles::getExportExcelFilter
	 */
	public function testGetExportExcelFilter() {
		$this->assertFalse($this->h_files->getExportExcelFilter(0), 'Get export excel filter with wrong user id returns false');

		$coord_filters = $this->h_files->getExportExcelFilter(95);
		$this->assertNotFalse($coord_filters, 'Get export excel filter with correct user id returns not false');
		$this->assertSame('array', gettype($coord_filters), 'Get export excel filter with correct user id returns an array even if empty');
	}

	public function testfindJoinsBetweenTablesRecursively() {
		$joins = $this->h_files->findJoinsBetweenTablesRecursively('', '');
		$this->assertEmpty($joins, 'Find joins between tables recursively with empty tables returns an empty array');

		$joins = $this->h_files->findJoinsBetweenTablesRecursively('jos_emundus_campaign_candidature', '');
		$this->assertEmpty($joins, 'Find joins between tables recursively with empty table 2 returns an empty array');

		$joins = $this->h_files->findJoinsBetweenTablesRecursively('', 'jos_emundus_setup_campaigns');
		$this->assertEmpty($joins, 'Find joins between tables recursively with empty table 1 returns an empty array');

		$joins = $this->h_files->findJoinsBetweenTablesRecursively('jos_emundus_campaign_candidature', 'jos_emundus_campaign_candidature');
		$this->assertEmpty($joins, 'Find joins between tables recursively with same tables returns an empty array');

		$joins = $this->h_files->findJoinsBetweenTablesRecursively('jos_emundus_campaign_candidature', 'jos_emundus_setup_campaigns');
		$this->assertNotEmpty($joins, 'Find joins between tables recursively with different tables returns not empty array');
		$this->assertSame(1, sizeof($joins), 'Join between jos_emundus_campaign_candidature and jos_emundus_setup_campaigns returns 1 join');
		$this->assertSame('jos_emundus_campaign_candidature.campaign_id = jos_emundus_setup_campaigns.id', $joins[0]['join_from_table'] . '.' . $joins[0]['table_key'] . ' = ' . $joins[0]['table_join'] . '.' . $joins[0]['table_join_key'], 'Join between jos_emundus_campaign_candidature and jos_emundus_setup_campaigns returns correct join');

		$joins = $this->h_files->findJoinsBetweenTablesRecursively('jos_emundus_campaign_candidature', 'jos_emundus_users');
		$this->assertNotEmpty($joins, 'Find joins between tables recursively with different tables returns not empty array');
		$this->assertSame(1, sizeof($joins), 'Join between jos_emundus_campaign_candidature and jos_emundus_users returns 1 join');
		$this->assertSame('jos_emundus_campaign_candidature.applicant_id = jos_emundus_users.user_id', $joins[0]['join_from_table'] . '.' . $joins[0]['table_key'] . ' = ' . $joins[0]['table_join'] . '.' . $joins[0]['table_join_key'], 'Join between jos_emundus_campaign_candidature and jos_emundus_users returns correct join');

		$joins = $this->h_files->findJoinsBetweenTablesRecursively('jos_emundus_evaluations', 'jos_emundus_campaign_candidature');
		$joins_reversed = $this->h_files->findJoinsBetweenTablesRecursively('jos_emundus_campaign_candidature', 'jos_emundus_evaluations');
		$this->assertSame($joins, $joins_reversed, 'Join between jos_emundus_evaluations and jos_emundus_campaign_candidature returns same join as join between jos_emundus_campaign_candidature and jos_emundus_evaluations');
	}

	public function testwriteJoins() {
		$joins = $this->h_files->findJoinsBetweenTablesRecursively('jos_emundus_campaign_candidature', 'jos_emundus_setup_campaigns');
		$already_joined = array();

		$joins_as_string = $this->h_files->writeJoins([], $already_joined);
		$this->assertEmpty($joins_as_string, 'Write joins with empty joins returns empty string');

		$joins_as_string = $this->h_files->writeJoins($joins, $already_joined);
		$this->assertNotEmpty($joins_as_string, 'Write joins with correct joins returns not empty string');
		$this->assertSame(' LEFT JOIN `jos_emundus_setup_campaigns` ON `jos_emundus_setup_campaigns`.`id` = `jos_emundus_campaign_candidature`.`campaign_id`', $joins_as_string, 'Write joins with correct joins returns correct string');
	}

	public function testwriteQueryWithOperator()
	{
		$query_condition = $this->h_files->writeQueryWithOperator(null, null, null);
		$this->assertSame('1=1', $query_condition, 'Write query with null operator returns 1=1 string');

		$query_condition = $this->h_files->writeQueryWithOperator('ecc.fnum', '24343432323', '!=');
		$this->assertSame('(ecc.fnum != \'24343432323\' OR ecc.fnum IS NULL ) ', $query_condition, 'Write query with != operator returns correct string');

		$query_condition = $this->h_files->writeQueryWithOperator('ecc.fnum', '24343432323', '=');
		$this->assertSame('ecc.fnum = \'24343432323\'', $query_condition, 'Write query with = operator returns correct string');

		$query_condition = $this->h_files->writeQueryWithOperator('ecc.fnum', ['24343432323', '24334234234234'], '=');
		$this->assertSame('ecc.fnum IN (\'24343432323\',\'24334234234234\')', $query_condition, 'Write query with = operator and array of values returns correct string with IN');

		$query_condition = $this->h_files->writeQueryWithOperator('ecc.fnum', ['24343432323', '24334234234234'], 'superior');
		$this->assertSame('1=1', $query_condition, 'Write query with > operator and array of values returns 1=1 string, because > operator is not supported with type select');

		$query_condition = $this->h_files->writeQueryWithOperator('ecc.created', ['2023-02-01', ''], 'superior', 'date');
		$this->assertSame('ecc.created > \'2023-02-01\'', $query_condition, 'Write query with superior operator for date filter type works');

		$query_condition = $this->h_files->writeQueryWithOperator('ecc.created', ['2023-02-01', '2023-02-09'], 'between', 'date');
		$this->assertSame('ecc.created BETWEEN \'2023-02-01\' AND \'2023-02-09\'', $query_condition, 'Write query with between operator for date filter type works');

		$query_condition = $this->h_files->writeQueryWithOperator('ecc.created', ['2023-02-01', ''], 'between', 'date');
		$this->assertSame('ecc.created >= \'2023-02-01\'', $query_condition, 'Write query with between operator for date filter type works even if only "from" value is passed');
	}

	public function testgetFabrikElementData() {
		$data = $this->h_files->getFabrikElementData(0);
		$this->assertEmpty($data, 'Get fabrik element data with 0 id returns empty array');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__fabrik_elements')
			->limit(1);

		$db->setQuery($query);
		$element = $db->loadAssoc();

		$data = $this->h_files->getFabrikElementData($element['id']);
		$this->assertNotEmpty($data, 'Get fabrik element data with correct id returns not empty array');
		$this->assertSame($element['id'], $data['element_id'], 'Get fabrik element data with correct id returns correct id');

		// make sure we knwo the name, the plugin, the group_id, the list_id
		$this->assertNotEmpty($data['name'], 'Get fabrik element data with correct id returns not empty name');
		$this->assertNotEmpty($data['plugin'], 'Get fabrik element data with correct id returns not empty plugin');
		$this->assertNotEmpty($data['group_id'], 'Get fabrik element data with correct id returns not empty group_id');
		$this->assertNotEmpty($data['list_id'], 'Get fabrik element data with correct id returns not empty list_id');
	}

	public function test_moduleBuildWhere()
	{
		$where = $this->h_files->_moduleBuildWhere([], 'files', []);
		$this->assertNotEmpty($where, 'Build where with empty filters returns not empty string');

		// $where must contain q and join entries
		$this->assertArrayHasKey('q', $where, 'Build where with empty filters returns q entry');
		$this->assertArrayHasKey('join', $where, 'Build where with empty filters returns join entry');

		$session = JFactory::getSession();
		$session->set('em-quick-search-filters', [
			[
				'scope' => 'everywhere',
				'value' => 'test',
			]
		]);
		$session->set('em-applied-filters', []);

		$where = $this->h_files->_moduleBuildWhere([], 'files', []);
		$this->assertNotEmpty($where['q'], 'Build where with filters returns not empty string');
		$this->assertSame(' AND esc.published > 0 AND (jecc.applicant_id LIKE \'%test%\' OR jecc.fnum LIKE \'%test%\' OR u.username LIKE \'%test%\' OR eu.firstname LIKE \'%test%\' OR eu.lastname LIKE \'%test%\' OR u.email LIKE \'%test%\') AND jecc.published = \'1\'', $where['q'], 'Build where with filters returns correct string');

		$session->set('em-quick-search-filters', [
			[
				'scope' => '',
				'value' => 'test',
			]
		]);
		$where = $this->h_files->_moduleBuildWhere([], 'files', []);
		$this->assertSame(' AND esc.published > 0 AND jecc.published = \'1\'', $where['q'], 'Build where with quick search filters with no scope returns only default filter on published');

		$session->set('em-quick-search-filters', [
			[
				'scope' => 'unhandled_scope',
				'value' => 'test',
			]
		]);
		$where = $this->h_files->_moduleBuildWhere([], 'files', []);
		$this->assertSame(' AND esc.published > 0 AND jecc.published = \'1\'', $where['q'], 'Build where with quick search filters with unhandled scope returns only default filter on published');
	}
}