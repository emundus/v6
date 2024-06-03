<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */


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
include_once(JPATH_SITE . '/components/com_emundus/models/files.php');
include_once(JPATH_SITE . '/components/com_emundus/helpers/files.php');
include_once(JPATH_SITE . '/components/com_emundus/helpers/users.php');
include_once(JPATH_SITE . '/components/com_emundus/models/profile.php');
include_once(JPATH_SITE . '/components/com_emundus/models/users.php');

jimport('joomla.user.helper');
jimport('joomla.application.application');
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

class EmundusModelFilesTest extends TestCase{
    private $m_files;
	private $h_files;
	private $h_sample;
    private $h_users;
	public $unit_test_coord_id;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->h_sample = new EmundusUnittestHelperSamples;
        $this->h_users = new EmundusHelperUsers;
		$this->h_files = new EmundusHelperFiles;

        $app = JFactory::getApplication();
        $username = 'test-gestionnaire-' . rand(0, 10000) . '@emundus.fr';
		$password = $this->h_users->generateStrongPassword();
        $this->unit_test_coord_id = $this->h_sample->createSampleUser(2, $username, $password);

		if(!empty($this->unit_test_coord_id)) {
			$logged_in = $app->login([
				'username' => $username,
				'password' => $password
			]);

			if ($logged_in) {
				$m_profile = new EmundusModelProfile;
				$m_profile->initEmundusSession();
			}
		}

	    $this->m_files = new EmundusModelFiles();
    }

	public function testFoo()
	{
		$this->assertTrue(true);
	}

    public function testConstruct() {
        $this->assertSame(false, $this->m_files->use_module_filters, 'By default, we do not use new module filters');
    }

	public function testshareUsers() {
		$user_id = $this->h_sample->createSampleUser(9, 'unit-test-candidat-' . rand(0, 10000) . '@emundus.test.fr');
		$program = $this->h_sample->createSampleProgram('Test partage d\'utilisateurs');
		$campaign_id = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

		$shared = $this->m_files->shareUsers([$this->unit_test_coord_id], EVALUATOR_RIGHTS, [$fnum]);
		$this->assertTrue($shared, 'shareUsers returns true if the sharing is successful');
	}

    public function testgetAllFnums()
    {
	    $fnums = $this->m_files->getAllFnums();
        $this->assertIsArray($fnums, 'getusers returns an array');

        $user_id = $this->h_sample->createSampleUser(9, 'unit-test-candidat-' . rand(0, 1000) . '@emundus.test.fr');
        $program = $this->h_sample->createSampleProgram('Nouveau programme');
	    $campaign_id = $this->h_sample->createSampleCampaign($program, true);
        $fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);
        $this->assertNotEmpty($fnum);

	    $session = JFactory::getSession();
	    $session->set('filt_params', ['programme' => [$program['programme_code']]]);

	    $fnums = $this->m_files->getAllFnums(false, 62);
	    $this->assertNotEmpty($fnums, 'if a fnum exists, by default get users should return a value');
		$this->assertTrue(in_array($fnum, $fnums), 'If a fnum is associated to me. I should see it.');
    }

    public function testgetAllTags()
    {
        $tags = $this->m_files->getAllTags();
        $this->assertIsArray($tags, 'getAllTags returns an array');
        $this->assertNotEmpty($tags, 'getAllTags returns a non-empty array');
    }

	public function testGetAllStatus()
	{
		// 1. Test if the function returns an array
		$all_status = $this->m_files->getAllStatus();
		$this->assertIsArray($all_status, 'getAllTags returns an array');
		$this->assertNotEmpty($all_status, 'getAllTags returns a non-empty array');

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$all_rights_grp = $eMConfig->get('all_rights_group', 1);

		// 2. We affect all rights group to our coordinator user
		$db = JFactory::getDbo();
		$insert = [
			'user_id' => $this->unit_test_coord_id,
			'group_id' => $all_rights_grp,
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_groups', $insert);

		// 2.1 We add a new restricted status in all rights group
		$insert = [
			'parent_id' => $all_rights_grp,
			'status' => $all_status[0]['step']
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_setup_groups_repeat_status', $insert);

		$status = $this->m_files->getAllStatus($this->unit_test_coord_id);
		$this->assertSame(count($all_status), count($status), 'getAllStatus shoud return all status again because we does not update filter_status in group');

		$query = $db->getQuery(true);
		$query->clear()
			->update('#__emundus_setup_groups')
			->set('filter_status = 1')
			->where('id = ' . $all_rights_grp);
		$db->setQuery($query);
		$db->execute();

		$status = $this->m_files->getAllStatus($this->unit_test_coord_id);
		$this->assertSame(1, count($status), 'getAllStatus should return 1 status because we update filter_status in group');

		// 3. We add a second status in all rights group
		$insert = [
			'parent_id' => $all_rights_grp,
			'status' => $all_status[1]['step']
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_setup_groups_repeat_status', $insert);

		$status = $this->m_files->getAllStatus($this->unit_test_coord_id);
		$this->assertSame(2, count($status), 'getAllStatus returns 2 status because we add a new one');

		// 4. We affect a new group that does not have restricted status so we should have all status
		$insert = [
			'user_id' => $this->unit_test_coord_id,
			'group_id' => 2,
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_groups', $insert);

		$status = $this->m_files->getAllStatus($this->unit_test_coord_id);
		$this->assertSame(count($all_status), count($status), 'getAllStatus shoudl return all status because at least one group of my user have not filter_status');

		$query->clear()
			->delete('#__emundus_setup_groups_repeat_status')
			->where('parent_id = ' . $all_rights_grp);
		$db->setQuery($query);
		$db->execute();

		$query->clear()
			->update('#__emundus_setup_groups')
			->set('filter_status = 0')
			->where('id = ' . $all_rights_grp);
		$db->setQuery($query);
		$db->execute();
	}

    public function testTagFile() {
        $tagged = $this->m_files->tagFile([], []);
        $this->assertFalse($tagged, 'tagFile returns false if no file is given');

        $user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 10000) . '@emundus.test.fr');
        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);
        $fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

        $tagged = $this->m_files->tagFile([$fnum], []);
        $this->assertFalse($tagged, 'tagFile returns false if no tag is given');

        $tags = $this->m_files->getAllTags();
        $tagged = $this->m_files->tagFile([$fnum], [$tags[0]['id']], 62);
        $this->assertTrue($tagged, 'tagFile returns true if a file and a tag are given');

	    $tagged = $this->m_files->tagFile([$fnum], [$tags[0]['id']], 62);
	    $this->assertTrue($tagged, 'tagFile should returns true if tag is already associated to the file by the same user');

	    $tagged = $this->m_files->tagFile([$fnum], [$tags[0]['id']], 95);
	    $this->assertTrue($tagged, 'tagFile should returns true if tag is already associated to the file but not by the same user');
    }

    public function testgetStatus()
    {
        $status = $this->m_files->getStatus();
        $this->assertIsArray($status, 'getStatus returns an array');
        $this->assertNotEmpty($status, 'getStatus returns a non-empty array');
    }

    public function testgetFnumsInfos()
    {
        $infos = $this->m_files->getFnumInfos('');
        $this->assertEmpty($infos, 'getFnumInfos returns an empty array if no fnum is given');

        $user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);
        $fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

        $infos = $this->m_files->getFnumInfos($fnum);
        $this->assertNotEmpty($infos, 'getFnumInfos returns an array of data');

        $this->assertArrayHasKey('fnum', $infos, 'the data contains the fnum');
        $this->assertEquals($fnum, $infos['fnum'], 'the fnum is the one passed as parameter');

        $this->assertArrayHasKey('campaign_id', $infos, 'the data contains the campaign_id');
        $this->assertEquals($campaign_id, $infos['campaign_id'], 'the campaign_id is the one passed as parameter');
    }

    public function testUpdateState() {
        $updated = $this->m_files->updateState([], null);
        $this->assertFalse($updated, 'updateState returns false if no file and no new state is given');

        $user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);
        $fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

        $updated = $this->m_files->updateState([$fnum], null);
        $this->assertFalse($updated, 'updateState returns false if no new state is given');

        $updated = $this->m_files->updateState([], 1);
        $this->assertFalse($updated, 'updateState returns false if no file is given');

        $updated = $this->m_files->updateState([$fnum], 1);
        $this->assertTrue($updated['status'], 'updateState returns true if a file and a new state are given');

        $infos = $this->m_files->getFnumInfos($fnum);
        $this->assertArrayHasKey('status', $infos, 'the data contains the state');
        $this->assertEquals(1, $infos['status'], 'the state is the one passed as parameter');
    }

	public function testgetFnumArray2() {
		$fnums = [];
		$elements = [];
		$data = $this->m_files->getFnumArray2($fnums, $elements);
		$this->assertEmpty($data, 'getFnumArray returns an empty array if no fnum is given');

		$element_ids = [];
		$form_id = $this->h_sample->getUnitTestFabrikForm();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('jfe.id')
			->from($db->quoteName('#__fabrik_elements', 'jfe'))
			->leftJoin($db->quoteName('#__fabrik_formgroup', 'jffg') . ' ON jffg.group_id = jfe.group_id')
			->where('jffg.form_id = ' . $form_id)
			->andWhere('jfe.hidden = 0');

		$db->setQuery($query);
		$element_ids = $db->loadColumn();
		$element_ids = implode(',', $element_ids);
		$elements = $this->h_files->getElementsName($element_ids);
		$this->assertNotEmpty($elements, 'getElementsName returns an array of elements');

		$user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 10000) . '@emundus.test.fr');
        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);
        $fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

        $columns = ['user', 'fnum', 'e_797_7973', 'e_797_7974', 'e_797_7975', 'e_797_7976', 'e_797_7977', 'e_797_7978', 'e_797_7979', 'e_797_7980', 'e_797_7981', 'e_797_7982', 'e_797_7983', 'dropdown_multi', 'dbjoin_multi', 'cascadingdropdown'];
        $values = array($user_id, $fnum, 'TEST FIELD', 'TEST TEXTAREA', '["1"]', '2', '3', '65', 'Ajoutez du texte personnalisé pour vos candidats', "<p>S'il vous plait taisez vous</p>", '1', '2023-01-01', '2023-07-13 00:00:00', '["0","1"]', null, '');
        $query->clear()
            ->insert('jos_emundus_unit_test_form')
            ->columns($columns)
            ->values(implode(',', $db->quote($values)));

        $db->setQuery($query);
        $db->execute();
        $insert_id = $db->insertid();

        if (!empty($insert_id)) {
            $query->clear()
                ->insert('jos_emundus_unit_test_form_repeat_dbjoin_multi')
                ->columns(['parent_id', 'dbjoin_multi'])
                ->values($insert_id . ', "17"');

            $db->setQuery($query);
            $db->execute();
        }

		$field_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'field') {
				$field_element = $element;
				break;
			}
		}
		if ($field_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$field_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with field element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($field_element->tab_name . '___' . $field_element->element_name, $data[$fnum], 'the data contains the field element');
			// after we mock the data, we should test that the data is correct
			$this->assertEquals('TEST FIELD', $data[$fnum][$field_element->tab_name . '___' . $field_element->element_name], 'the fnum contains the field element');
		}

		$texarea_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'textarea') {
				$texarea_element = $element;
				break;
			}
		}
		if ($texarea_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$texarea_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with texarea element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($texarea_element->tab_name . '___' . $texarea_element->element_name, $data[$fnum], 'the data contains the textarea element');
            $this->assertEquals('TEST TEXTAREA', $data[$fnum][$texarea_element->tab_name . '___' . $texarea_element->element_name], 'the fnum contains the field element');

        }

		$display_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'display') {
				$display_element = $element;
				break;
			}
		}
		if ($display_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$display_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with display element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($display_element->tab_name . '___' . $display_element->element_name, $data[$fnum], 'the data contains the display element');
		}

		$yesno_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'yesno') {
				$yesno_element = $element;
				break;
			}
		}
		if ($yesno_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$yesno_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with yesno element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($yesno_element->tab_name . '___' . $yesno_element->element_name, $data[$fnum], 'the data contains the yesno element');
			$this->assertContains($data[$fnum][$yesno_element->tab_name . '___' . $yesno_element->element_name], [JText::_('JNO'), JText::_('JYES')], 'the yesno element contains translation for 0 and 1, such as jyes and jno');
		}

		$date_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'date') {
				$date_element = $element;
				break;
			}
		}
		if ($date_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$date_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with date element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($date_element->tab_name . '___' . $date_element->element_name, $data[$fnum], 'the data contains the date element');

			// remove escape characters from the date format
			$data[$fnum][$date_element->tab_name . '___' . $date_element->element_name] = str_replace('\\', '', $data[$fnum][$date_element->tab_name . '___' . $date_element->element_name]);
			$this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $data[$fnum][$date_element->tab_name . '___' . $date_element->element_name], 'the date element contains a date in the correct format');
		}

		$birthday_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'birthday') {
				$birthday_element = $element;
				break;
			}
		}
		if ($birthday_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$birthday_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with birthday element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($birthday_element->tab_name . '___' . $birthday_element->element_name, $data[$fnum], 'the data contains the birthday element');
			$data[$fnum][$birthday_element->tab_name . '___' . $birthday_element->element_name] = str_replace('\\', '', $data[$fnum][$birthday_element->tab_name . '___' . $birthday_element->element_name]);
			$this->assertStringMatchesFormat('%d-%d-%d', $data[$fnum][$birthday_element->tab_name . '___' . $birthday_element->element_name], 'the date element contains a birthday in the correct format');
		}

		$databasejoin_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'databasejoin') {
				$element_attribs = json_decode($element->element_attribs);
				if ($element_attribs->database_join_display_type === 'dropdown' || $element_attribs->database_join_display_type === 'radio') {
					$databasejoin_element = $element;
					break;
				}
			}
		}
		if ($databasejoin_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$databasejoin_element]);
			$this->assertNotFalse($data, 'getFnumArray does not encounter an error');
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with databasejoin element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($databasejoin_element->tab_name . '___' . $databasejoin_element->element_name, $data[$fnum], 'the data contains the databasejoin element');
		}

		$databasejoin_multi_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'databasejoin') {
				$element_attribs = json_decode($element->element_attribs);
				if ($element_attribs->database_join_display_type === 'checkbox' || $element_attribs->database_join_display_type === 'multilist') {
					$databasejoin_multi_element = $element;
					break;
				}
			}
		}
		if ($databasejoin_multi_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$databasejoin_multi_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with databasejoin multi element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($databasejoin_multi_element->table_join . '___' . $databasejoin_multi_element->element_name, $data[$fnum], 'the data contains the databasejoin multi element');
            $this->assertStringContainsString('Charente\-Maritime', $data[$fnum][$databasejoin_multi_element->table_join . '___' . $databasejoin_multi_element->element_name], 'the databasejoin multi element contains the correct value, and escaped data');
		}

		$radio_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'radiobutton') {
				$radio_element = $element;
				break;
			}
		}
		if ($radio_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$radio_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with radiobutton element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($radio_element->tab_name . '___' . $radio_element->element_name, $data[$fnum], 'the data contains the radiobutton element');
		}

		$dropdown_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'dropdown') {
				$element_attribs = json_decode($element->element_attribs);

				if ($element_attribs->multiple == 0) {
					$dropdown_element = $element;
					break;
				}
			}
		}
		if ($dropdown_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$dropdown_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with dropdown element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($dropdown_element->tab_name . '___' . $dropdown_element->element_name, $data[$fnum], 'the data contains the dropdown element');
		}

		$dropdown_multi_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'dropdown') {
				$element_attribs = json_decode($element->element_attribs);
				if ($element_attribs->multiple == 1) {
					$dropdown_multi_element = $element;
					break;
				}
			}
		}
		if ($dropdown_multi_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$dropdown_multi_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with dropdown multiselect element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($dropdown_multi_element->tab_name . '___' . $dropdown_multi_element->element_name, $data[$fnum], 'the data contains the dropdown multiselect element');
			$this->assertNotEmpty($data[$fnum][$dropdown_multi_element->tab_name . '___' . $dropdown_multi_element->element_name], 'the data contains the dropdown multiselect element');
			$this->assertStringNotContainsString('[', $data[$fnum][$dropdown_multi_element->tab_name . '___' . $dropdown_multi_element->element_name], 'dropdown multiselect element, open bracket has been removed');
			$this->assertStringNotContainsString(']', $data[$fnum][$dropdown_multi_element->tab_name . '___' . $dropdown_multi_element->element_name], 'dropdown multiselect element, close bracket has been removed');
		}

		$cascadingdropdown_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'cascadingdropdown') {
				$cascadingdropdown_element = $element;
				break;
			}
		}
		if ($cascadingdropdown_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$cascadingdropdown_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with cascadingdropdown element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($cascadingdropdown_element->tab_name . '___' . $cascadingdropdown_element->element_name, $data[$fnum], 'the data contains the cascadingdropdown element');
		}

		$first_form_elements = [$birthday_element, $date_element, $yesno_element, $display_element, $texarea_element, $field_element, $databasejoin_element, $radio_element, $dropdown_element, $dropdown_multi_element, $databasejoin_multi_element, $cascadingdropdown_element];
		$data = $this->m_files->getFnumArray2([$fnum], $first_form_elements);
		$this->assertNotEmpty($data, 'getFnumArray returns an not empty array of data with all elements');

		// TODO: create a form with all type of elements and where the group is repeatable
		/*$repeat_form_id = 381;
		$query->clear()
			->select('jfe.id')
			->from($db->quoteName('#__fabrik_elements', 'jfe'))
			->leftJoin($db->quoteName('#__fabrik_formgroup', 'jffg') . ' ON jffg.group_id = jfe.group_id')
			->where('jffg.form_id = ' . $repeat_form_id)
			->andWhere('jfe.hidden = 0');

		$db->setQuery($query);
		$element_ids = $db->loadColumn();
		$element_ids = implode(',', $element_ids);
		$elements = $this->h_files->getElementsName($element_ids);
		$this->assertNotEmpty($elements, 'getElementsName returns an array of elements for a form with repeatable group');

		// TODO: create a fnum and writes data in it
		$fnum = '2023070411433500000020000095';


		$field_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'field') {
				$field_element = $element;
				break;
			}
		}
		if ($field_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$field_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with field element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($field_element->tab_name . '___' . $field_element->element_name, $data[$fnum], 'the data contains the field element');
			$this->assertNotEmpty($data[$fnum][$field_element->tab_name . '___' . $field_element->element_name], 'the data contains the field element and is not empty');
		}

		$texarea_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'textarea') {
				$texarea_element = $element;
				break;
			}
		}
		if ($texarea_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$texarea_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with texarea element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($texarea_element->tab_name . '___' . $texarea_element->element_name, $data[$fnum], 'the data contains the textarea element');
			$this->assertNotEmpty($data[$fnum][$texarea_element->tab_name . '___' . $texarea_element->element_name], 'the data contains the textarea element and is not empty');
		}

		$display_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'display') {
				$display_element = $element;
				break;
			}
		}
		if ($display_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$display_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with display element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($display_element->tab_name . '___' . $display_element->element_name, $data[$fnum], 'the data contains the display element');
		}

		$yesno_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'yesno') {
				$yesno_element = $element;
				break;
			}
		}
		if ($yesno_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$yesno_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with yesno element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($yesno_element->tab_name . '___' . $yesno_element->element_name, $data[$fnum], 'the data contains the yesno element');

			$values = explode(',', $data[$fnum][$yesno_element->tab_name . '___' . $yesno_element->element_name]);
			foreach ($values as $value) {
				$this->assertContains(trim($value), [JText::_('JNO'), JText::_('JYES')], 'the value is Yes or No translatation');
			}
		}

		$date_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin == 'date') {
				$date_element = $element;
				break;
			}
		}
		if ($date_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$date_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with date element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($date_element->tab_name . '___' . $date_element->element_name, $data[$fnum], 'the data contains the date element');

			$dates = explode(',', $data[$fnum][$date_element->tab_name . '___' . $date_element->element_name]);
			foreach ($dates as $date) {
				$this->assertStringMatchesFormat('%d/%d/%d %d:%d:%d', $date, 'the date element contains a date in the correct format');
			}
		}

		$birthday_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'birthday') {
				$birthday_element = $element;
				break;
			}
		}
		if ($birthday_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$birthday_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with birthday element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($birthday_element->tab_name . '___' . $birthday_element->element_name, $data[$fnum], 'the data contains the birthday element');

			$dates = explode(',', $data[$fnum][$birthday_element->tab_name . '___' . $birthday_element->element_name]);
			foreach ($dates as $date) {
				$this->assertStringMatchesFormat('%d/%d/%d', trim($date), 'the date element contains a birthday in the correct format');
			}
		}

		$databasejoin_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'databasejoin') {
				$element_attribs = json_decode($element->element_attribs);
				if ($element_attribs->database_join_display_type === 'dropdown' || $element_attribs->database_join_display_type === 'radio') {
					$databasejoin_element = $element;
					break;
				}
			}
		}
		if ($databasejoin_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$databasejoin_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with databasejoin element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($databasejoin_element->tab_name . '___' . $databasejoin_element->element_name, $data[$fnum], 'the data contains the databasejoin element');
		}

		$databasejoin_multi_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'databasejoin') {
				$element_attribs = json_decode($element->element_attribs);
				if ($element_attribs->database_join_display_type === 'checkbox' || $element_attribs->database_join_display_type === 'multilist') {
					$databasejoin_multi_element = $element;
					break;
				}
			}
		}
		if ($databasejoin_multi_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$databasejoin_multi_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with databasejoin multi element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($databasejoin_multi_element->tab_name . '___' . $databasejoin_multi_element->element_name, $data[$fnum], 'the data contains the databasejoin multi element');
		}

		$radio_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'radiobutton') {
				$radio_element = $element;
				break;
			}
		}
		if ($radio_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$radio_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with radiobutton element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($radio_element->tab_name . '___' . $radio_element->element_name, $data[$fnum], 'the data contains the radiobutton element');
		}

		$dropdown_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'dropdown') {
				$element_attribs = json_decode($element->element_attribs);

				if ($element_attribs->multiple == 0) {
					$dropdown_element = $element;
					break;
				}
			}
		}
		if ($dropdown_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$dropdown_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with dropdown element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($dropdown_element->tab_name . '___' . $dropdown_element->element_name, $data[$fnum], 'the data contains the dropdown element');
			$this->assertNotEmpty($data[$fnum][$dropdown_element->tab_name . '___' . $dropdown_element->element_name], 'dropdown in repeat context values returned');
		}

		$dropdown_multi_element = null;
		foreach ($elements as $element) {
			if ($element->element_plugin === 'dropdown') {
				$element_attribs = json_decode($element->element_attribs);
				if ($element_attribs->multiple == 1) {
					$dropdown_multi_element = $element;
					break;
				}
			}
		}
		if ($dropdown_multi_element) {
			$data = $this->m_files->getFnumArray2([$fnum], [$dropdown_multi_element]);
			$this->assertNotEmpty($data, 'getFnumArray returns an array of data with dropdown multiselect element');
			$this->assertNotEmpty($data[$fnum], 'getFnumArray returns an array of data containing the fnum passed as parameter');
			$this->assertArrayHasKey($dropdown_multi_element->tab_name . '___' . $dropdown_multi_element->element_name, $data[$fnum], 'the data contains the dropdown multiselect element');
			$this->assertNotEmpty($data[$fnum][$dropdown_multi_element->tab_name . '___' . $dropdown_multi_element->element_name], 'the data contains the dropdown multiselect element');
			$this->assertStringNotContainsString('[', $data[$fnum][$dropdown_multi_element->tab_name . '___' . $dropdown_multi_element->element_name], 'dropdown multiselect element, open bracket has been removed');
			$this->assertStringNotContainsString(']', $data[$fnum][$dropdown_multi_element->tab_name . '___' . $dropdown_multi_element->element_name], 'dropdown multiselect element, close bracket has been removed');
		}

		$repeat_form_elements = [$field_element, $texarea_element, $display_element, $yesno_element, $date_element, $birthday_element, $databasejoin_element, $databasejoin_multi_element, $radio_element, $dropdown_element, $dropdown_multi_element];
		$data = $this->m_files->getFnumArray2([$fnum], $repeat_form_elements);
		$this->assertNotEmpty($data, 'getFnumArray returns a not empty array of data with all elements and repeatable group');*/

		// calculate time of execution
		//$elements_from_different_forms = array_merge($first_form_elements, $repeat_form_elements);
        $elements_from_different_forms = $first_form_elements;
		$start = microtime(true);
		$data = $this->m_files->getFnumArray2([$fnum], $elements_from_different_forms, true);
		$end = microtime(true);
		$this->assertNotEmpty($data, 'getFnumArray returns a not empty array of data with all elements from different forms');
		$elapsed_new_function_time = $end - $start;

		$start = microtime(true);
		$data = $this->m_files->getFnumArray([$fnum], $elements_from_different_forms);
		$end = microtime(true);
		$elapsed_old_function_time = $end - $start;

		$this->assertGreaterThanOrEqual($elapsed_new_function_time, $elapsed_old_function_time, 'getFnumArray2 is faster than getFnumArray ' . $elapsed_new_function_time . ' vs ' . $elapsed_old_function_time);
	}

    public function testmakeAttachmentsEditableByApplicant()
    {
        $emundus_config = JComponentHelper::getParams('com_emundus');
        $can_edit_back_attachments = $emundus_config->get('can_edit_back_attachments', 0);
        if ($can_edit_back_attachments != 1) {
            $emundus_config->set('can_edit_back_attachments', 1);
        }

        $user_id = $this->h_sample->createSampleUser(9, 'unit-test-candidat-' . rand(0, 1000) . '@emundus.test.fr');
        $program = $this->h_sample->createSampleProgram();
        $campaign_id = $this->h_sample->createSampleCampaign($program);
        $fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);
        $m_users = new EmundusModelUsers;
        $profile_id = $m_users->getProfileIDByCampaignID($campaign_id);

        $this->assertTrue($this->m_files->makeAttachmentsEditableByApplicant(['123123'], 0), 'makeAttachmentsEditableByApplicant returns true if the fnum does not exist');
        $this->assertTrue($this->m_files->makeAttachmentsEditableByApplicant([$fnum], 999999), 'makeAttachmentsEditableByApplicant returns true if the status does not exist');
        $this->assertTrue($this->m_files->makeAttachmentsEditableByApplicant([$fnum], 0), 'makeAttachmentsEditableByApplicant returns true if the fnum and status exist');

        $attachment_id_1 = $this->h_sample->createSampleAttachment();
        $attachment_id_2 = $this->h_sample->createSampleAttachment();
        $this->h_sample->createSampleUpload($fnum, $campaign_id, $user_id, $attachment_id_1);
        $this->h_sample->createSampleUpload($fnum, $campaign_id, $user_id, $attachment_id_2);

        $values = [
            $profile_id.', '.$attachment_id_1,
            $profile_id.', '.$attachment_id_2
        ];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->insert($db->quoteName('#__emundus_setup_attachment_profiles'))
            ->columns($db->quoteName(['profile_id', 'attachment_id']))
            ->values($values);
        $db->setQuery($query);
        $db->execute();

        $query->clear()
            ->update($db->quoteName('#__emundus_uploads'))
            ->set($db->quoteName('can_be_deleted').' = 0')
            ->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum))
            ->andWhere($db->quoteName('attachment_id').' IN ('.$attachment_id_1.','.$attachment_id_2.')')
            ->andWhere($db->quoteName('user_id').' = '.$user_id);
        $db->setQuery($query);
        $db->execute();

        $this->assertTrue($this->m_files->makeAttachmentsEditableByApplicant([$fnum], 0), 'makeAttachmentsEditableByApplicant returns true as the attachments have been updated');

        $query->clear()
            ->select($db->quoteName('attachment_id'))
            ->from($db->quoteName('#__emundus_uploads'))
            ->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum))
            ->andWhere($db->quoteName('attachment_id').' IN ('.$attachment_id_1.','.$attachment_id_2.')')
            ->andWhere($db->quoteName('user_id').' = '.$user_id)
            ->andWhere($db->quoteName('can_be_deleted').' = 1');
        $db->setQuery($query);
        $updated_attachments = $db->loadColumn();

        $this->assertSame([(string)$attachment_id_1, (string)$attachment_id_2], $updated_attachments, 'makeAttachmentsEditableByApplicant attachments should now be editable by the applicant');
    }
}
