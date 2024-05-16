<?php


use PHPUnit\Framework\TestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;

ini_set('display_errors', false);
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');
include_once(JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_SITE . '/components/com_emundus/helpers/fabrik.php');

jimport('joomla.user.helper');
jimport('joomla.application.application');
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusHelperFabrikTest extends TestCase
{
    private $h_fabrik;
    private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->h_fabrik = new EmundusHelperFabrik();
        $this->h_sample = new EmundusUnittestHelperSamples();
    }

    public function testFoo()
    {
        $foo = true;
        $this->assertSame(true, $foo);
    }

    public function testgetFormattedPhoneNumberValue()
    {
        $unformatted_phone_number = '';
        $formatted_phone_number = EmundusHelperFabrik::getFormattedPhoneNumberValue($unformatted_phone_number);
        $this->assertSame('', $formatted_phone_number, 'Empty phone number returns empty string');

        $unformatted_phone_number = 'zkljhdqopsjdpzhfklqsjnd';
        $formatted_phone_number = EmundusHelperFabrik::getFormattedPhoneNumberValue($unformatted_phone_number);
        $this->assertSame('', $formatted_phone_number, 'Random string with incorrect characters returns empty string');

        $unformatted_phone_number = '+33 6 12 34 56 78';
        $formatted_phone_number = EmundusHelperFabrik::getFormattedPhoneNumberValue($unformatted_phone_number);
        $this->assertNotEmpty($formatted_phone_number, 'Correct phone number returns not empty string and by default format is E164');
        $this->assertSame('FR+33612345678', $formatted_phone_number, 'Correct phone number returns correct formatted string');

        $unformatted_phone_number = 'FR+33 612 3456 7 8';
        $formatted_phone_number = EmundusHelperFabrik::getFormattedPhoneNumberValue($unformatted_phone_number);
        $this->assertNotEmpty($formatted_phone_number, 'Correct phone number returns not empty string');
        $this->assertSame('FR+33612345678', $formatted_phone_number, 'Correct phone number with weird spacing returns correct formatted string');

        $unformatted_phone_number = 'FR+33 612 3456 7 8';
        $formatted_phone_number = EmundusHelperFabrik::getFormattedPhoneNumberValue($unformatted_phone_number, 2);
        $this->assertNotEmpty($formatted_phone_number, 'Correct phone number returns not empty string');
        $this->assertSame('FR06 12 34 56 78', $formatted_phone_number, 'Setting format 2 (national) returns formatted number correctly');


        $unformatted_phone_number = 'FR+33 612 34za 7 8';
        $formatted_phone_number = EmundusHelperFabrik::getFormattedPhoneNumberValue($unformatted_phone_number, 2);
        $this->assertEmpty($formatted_phone_number, 'Incorrect phone number returns empty string');
    }

    /**
     * @return void
     * @description Test the formatElementValue() method
     * It should return the value formatted in a good way
     * @throws Exception
     */
    public function testformatElementValue()
    {
        // Test cases with empty values
        $this->assertEquals('', EmundusHelperFabrik::formatElementValue('', ''), 'Passing an empty element name and raw value should return nothing');
        $this->assertEquals('', EmundusHelperFabrik::formatElementValue('name', ''), 'Passing an empty raw value should return nothing');
        $this->assertEquals('element', EmundusHelperFabrik::formatElementValue('', 'element'), 'Passing an empty element name should return raw_value');

        $form_id = $this->h_sample->getUnitTestFabrikForm();
		$user_id = $this->h_sample->createSampleUser();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('fe.id, fe.name, fe.group_id as group_id, fe.plugin as plugin, fe.params as params, fe.label as label')
            ->from($db->quoteName('#__fabrik_elements', 'fe'))
            ->leftJoin($db->quoteName('#__fabrik_formgroup', 'ff') . ' ON ff.group_id = fe.group_id')
            ->where($db->quoteName('ff.form_id') . ' = ' . $form_id);

        $db->setQuery($query);
        $elements = $db->loadObjectList();

        foreach ($elements as $element) {

            $params = json_decode($element->params, true);

            switch ($element->plugin) {
                case 'date':
                    $helperDate = new EmundusHelperDate();
                    $date = $helperDate->getNow();
                    $date_format = $params['date_form_format'];
                    $local = $params['date_store_as_local'] ? 1 : 0;

                    $formatted_date = $helperDate->displayDate($date, $date_format, $local);
                    $this->assertEquals($formatted_date, EmundusHelperFabrik::formatElementValue($element->name, $date, $element->group_id));

	                $date = '2024-01-01 10:00:00';
	                $params['date_form_format'] = 'd/m/Y H\hi';
	                $params['date_store_as_local'] = 1;
	                $new_params_json = json_encode($params);

	                $query = $db->getQuery(true);

	                $query->update($db->quoteName('#__fabrik_elements'))
		                ->set($db->quoteName('params') . ' = ' . $db->quote($new_params_json))
		                ->where($db->quoteName('id') . ' = ' . $db->quote($element->id));

	                try {
		                $db->setQuery($query);
		                $db->execute();
	                } catch (Exception $e) {
		                Log::add('components/com_emundus/unittest/EmundusHelperFabrikTest | Error when try to get fabrik elements table data : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), Log::ERROR, 'com_emundus.error');
	                }

	                $formatted_date = EmundusHelperFabrik::formatElementValue($element->name, $date, $element->group_id);

	                $this->assertEquals('01/01/2024 10h00', $formatted_date, 'The date is correctly formatted');

	                break;
	            case 'birthday':

		            $helperDate = new EmundusHelperDate();

		            $format = $params['list_date_format'];

		            $date = $helperDate->getNow();
		            $d = DateTime::createFromFormat($format, $date);
		            if ($d && $d->format($format) == $date)
		            {
			            $formatted_date = EmundusHelperDate::displayDate($date, Text::_('DATE_FORMAT_LC'));
		            }
		            else
		            {
			            $formatted_date = EmundusHelperDate::displayDate($date, $format);
		            }

		            $this->assertEquals($formatted_date, EmundusHelperFabrik::formatElementValue($element->name, $date, $element->group_id), 'The birhday is correctly formatted');

		            $date = '2015-12-01';
		            $params['list_date_format'] = 'd/m/Y';
		            $new_params_json = json_encode($params);

		            $query = $db->getQuery(true);

		            $query->update($db->quoteName('#__fabrik_elements'))
			            ->set($db->quoteName('params') . ' = ' . $db->quote($new_params_json))
			            ->where($db->quoteName('id') . ' = ' . $db->quote($element->id));

		            try {
			            $db->setQuery($query);
			            $db->execute();
		            } catch (Exception $e) {
			            Log::add('components/com_emundus/unittest/EmundusHelperFabrikTest | Error when try to get fabrik elements table data : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), Log::ERROR, 'com_emundus.error');
		            }

		            $this->assertEquals('01/12/2015', EmundusHelperFabrik::formatElementValue($element->name, $date, $element->group_id), 'The birthday is correctly formatted');

		            break;

	            case 'checkbox':
		            $this->assertEquals($params['sub_options']['sub_labels'][0], EmundusHelperFabrik::formatElementValue($element->name, '['.$params['sub_options']['sub_values'][0].']', $element->group_id));
					break;
	            case 'radiobutton':
                case 'dropdown':
					if(isset($params['multiple']) && $params['multiple'] == 1) {
						$this->assertEquals($params['sub_options']['sub_labels'][0], EmundusHelperFabrik::formatElementValue($element->name, '['.$params['sub_options']['sub_values'][0].']', $element->group_id));
					} else
					{
						$this->assertEquals($params['sub_options']['sub_labels'][0], EmundusHelperFabrik::formatElementValue($element->name, $params['sub_options']['sub_values'][0], $element->group_id));
						$this->assertEquals($params['sub_options']['sub_labels'][1], EmundusHelperFabrik::formatElementValue($element->name, $params['sub_options']['sub_values'][1], $element->group_id));
					}
                    break;
                case 'yesno':
                    $this->assertEquals(JText::_('JYES'), EmundusHelperFabrik::formatElementValue($element->name, 1, $element->group_id));
                    $this->assertEquals(JText::_('JNO'), EmundusHelperFabrik::formatElementValue($element->name, 0, $element->group_id));
                    break;
                case 'textarea':
                    $this->assertEquals("This is<br />\n a test", EmundusHelperFabrik::formatElementValue($element->name, "This is
 a test", $element->group_id));
                    break;
                case 'databasejoin':
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);

                    // Query to get the first value of the join_key_column
                    $query->select($db->quoteName($params['join_key_column']))
                        ->from($db->quoteName($params['join_db_name']))
                        ->order($db->quoteName($params['join_key_column']));
                    $db->setQuery($query);
                    $db_values = $db->loadColumn();

                    $query->clear();

	                if (!empty($params['join_val_column_concat'])) {
		                $lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
		                $params['join_val_column_concat'] = str_replace('{thistable}', $params['join_db_name'], $params['join_val_column_concat']);
		                $params['join_val_column_concat'] = str_replace('{shortlang}', $lang, $params['join_val_column_concat']);

		                $query->select($db->quoteName($params['join_val_column_concat']));
	                } else {
		                $query->select($db->quoteName($params['join_val_column']));
	                }

	                $query->from($db->quoteName($params['join_db_name']))
	                    ->order($db->quoteName($params['join_key_column']));

	                $db->setQuery($query);
	                $formatted_value = $db->loadColumn();

	                if ($params['database_join_display_type'] != 'checkbox' && $params['database_join_display_type'] != 'multilist')
	                {
		                $this->assertEquals($formatted_value[0], EmundusHelperFabrik::formatElementValue($element->name, $db_values[0], $element->group_id, $user_id));
	                }
					else
					{
						$this->assertEquals(implode(',',[$formatted_value[0],$formatted_value[4]]), EmundusHelperFabrik::formatElementValue($element->name, [$db_values[0],$db_values[4]], $element->group_id, $user_id));
					}
                    break;

	            case 'cascadingdropdown':

		            $emundusUser = JFactory::getSession()->get('emundusUser');
		            $fnum        = isset($emundusUser->fnum) ? $emundusUser->fnum : '';

		            $query->select('applicant_id')
			            ->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
			            ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));

		            try
		            {
			            $db->setQuery($query);
			            $applicant_id = $db->loadResult();
		            }
		            catch (Exception $e)
		            {
			            JLog::add("Failed to get applicant_id from fnum $fnum : " . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
		            }

		            $query = $db->getQuery(true);

		            $r     = explode('___', Text::_($params['cascadingdropdown_label']));
		            $select = $r[1];
		            $from   = $r[0];

		            $query->select($db->quoteName($select))
			            ->from($db->quoteName($from))
			            ->setLimit(1);

		            $db->setQuery($query);
		            $firstKeyValue = $db->loadResult();

		            $query->clear();

		            $query  = "SELECT " . $select . " FROM " . $from;
		            $query  = preg_replace('#{thistable}#', $from, $query);
		            if(!empty($applicant_id)){
			            $query  = preg_replace('#{my->id}#', $applicant_id, $query);
		            }
		            $query  = preg_replace('#{shortlang}#', substr(JFactory::getLanguage()->getTag(), 0, 2), $query);

		            $db->setQuery($query);
		            $ret = $db->loadResult();
		            $formatted_value = Text::_($ret);

		            $this->assertEquals($formatted_value, EmundusHelperFabrik::formatElementValue($element->name, $firstKeyValue, $element->group_id));
		            break;

	            case 'field':

					$password = 'test';

		            $params['password'] = 1;
		            $new_params_json = json_encode($params);

		            $query = $db->getQuery(true);

		            $query->update($db->quoteName('#__fabrik_elements'))
			            ->set($db->quoteName('params') . ' = ' . $db->quote($new_params_json))
			            ->where($db->quoteName('id') . ' = ' . $db->quote($element->id));

		            try {
			            $db->setQuery($query);
			            $db->execute();
		            } catch (Exception $e) {
			            Log::add('components/com_emundus/unittest/EmundusHelperFabrikTest | Error when try to get fabrik elements table data : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), Log::ERROR, 'com_emundus.error');
		            }

		            $this->assertEquals('******', EmundusHelperFabrik::formatElementValue($element->name, $password, $element->group_id));
		            $params['password'] = 0;
		            $new_params_json = json_encode($params);

		            $query = $db->getQuery(true);

		            $query->update($db->quoteName('#__fabrik_elements'))
			            ->set($db->quoteName('params') . ' = ' . $db->quote($new_params_json))
			            ->where($db->quoteName('id') . ' = ' . $db->quote($element->id));

		            try {
			            $db->setQuery($query);
			            $db->execute();
		            } catch (Exception $e) {
			            Log::add('components/com_emundus/unittest/EmundusHelperFabrikTest | Error when try to get fabrik elements table data : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), Log::ERROR, 'com_emundus.error');
		            }

		            $this->assertEquals($password, EmundusHelperFabrik::formatElementValue($element->name, $password, $element->group_id));

            }
        }
    }

	/**
	 * @return void
	 * @description Test the getElementByAlias() method
	 * It should return the name and database table name storage of the element with the alias passed as parameter
	 */
	public function testgetElementByAlias()
	{
		$this->assertNull($this->h_fabrik->getElementByAlias(""), 'Empty alias should return null');

		$form_id = $this->h_sample->getUnitTestFabrikForm();

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('fe.id, fe.name, fe.params, fl.db_table_name')
			->from($db->quoteName('#__fabrik_elements', 'fe'))
			->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
			->leftJoin($db->quoteName('#__fabrik_lists','fl').' ON '.$db->quoteName('fl.form_id').' = '.$db->quoteName('ffg.form_id'))
			->where($db->quoteName('ffg.form_id') . ' = ' . $form_id);

		$db->setQuery($query);
		$elements = $db->loadObjectList();

		foreach ($elements as $element) {
			$params = json_decode($element->params, true);

			if(empty($params["alias"]))
			{
				$params['alias'] = 'alias' . rand(0, 1000);
				$params = json_encode($params);

				$query = $db->getQuery(true);

				$query->clear()
					->update($db->quoteName('#__fabrik_elements'))
					->set($db->quoteName('params') . ' = ' . $db->quote($params))
					->where($db->quoteName('id') . ' = ' . $db->quote($element->id))
					->limit(1);
				$db->setQuery($query);
				$db->execute();

				$params = json_decode($params, true);
			}

			$element_by_alias = $this->h_fabrik->getElementByAlias($params["alias"], $form_id);
			$this->assertEquals($element->name, $element_by_alias->name, 'The element name obtained should be the same as the element name in the database.');
			$this->assertEquals($element->db_table_name, $element_by_alias->db_table_name, 'The database table name storage obtained should be the same as the database table name storage in the database.');
		}
	}

	/**
	 * @return void
	 * @description Test the getValueByAlias() method
	 * It should return the value of the element with the alias and form number passed as parameters
	 */
	public function testGetValueByAlias()
	{
		$this->assertNull($this->h_fabrik->getValueByAlias("", 1), 'Empty alias should return null');
		$this->assertNull($this->h_fabrik->getValueByAlias("test", ""), 'Empty fnum should return null');

		$form_id = $this->h_sample->getUnitTestFabrikForm();

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('fe.id, fe.name, fe.params, fl.db_table_name ')
			->from($db->quoteName('#__fabrik_elements', 'fe'))
			->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
			->leftJoin($db->quoteName('#__fabrik_lists','fl').' ON '.$db->quoteName('fl.form_id').' = '.$db->quoteName('ffg.form_id'))
			->where($db->quoteName('ffg.form_id') . ' = ' . $form_id);

		$db->setQuery($query);
		$elements = $db->loadObjectList();

		$element_id = 1;

		foreach ($elements as $element) {

			$query->clear()
				->select('tb.fnum')
				->from($db->quoteName($element->db_table_name, 'tb'))
				->where("tb.id = " . $db->quote($element_id));

			$db->setQuery($query);
			$fnum = $db->loadResult();

			if(isset($fnum)) {

				$params = json_decode($element->params, true);

				if(empty($params["alias"])) {
					$params['alias'] = 'alias' . rand(0, 1000);
					$params = json_encode($params);

					$query = $db->getQuery(true);

					$query->clear()
						->update($db->quoteName('#__fabrik_elements'))
						->set($db->quoteName('params') . ' = ' . $db->quote($params))
						->where($db->quoteName('id') . ' = ' . $db->quote($element->id))
						->limit(1);
					$db->setQuery($query);
					$db->execute();

					$params = json_decode($params, true);
				}

				$value = $this->h_fabrik->getValueByAlias($params["alias"], $fnum);
				$value_raw = $this->h_fabrik->getValueByAlias($params["alias"], $fnum, 1);

				$query->clear()
					->select($element->name)
					->from($db->quoteName($element->db_table_name))
					->where("fnum = " . $db->quote($fnum));
				$db->setQuery($query);
				$expected = $db->loadResult();

				if(!empty($expected)) {
					$expected_formatted = EmundusHelperFabrik::formatElementValue($element->name, $expected);
					$this->assertEquals($expected_formatted, $value, 'The value formatted obtained should be the same as the value formatted in the database.');
					$this->assertEquals($expected, $value_raw, 'The value obtained should be the same as the value in the database.');
				}
			}
			$element_id++;
		}
	}
}