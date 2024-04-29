<?php


use PHPUnit\Framework\TestCase;
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
        $formatted_phone_number = $this->h_fabrik->getFormattedPhoneNumberValue($unformatted_phone_number);
        $this->assertSame('', $formatted_phone_number, 'Empty phone number returns empty string');

        $unformatted_phone_number = 'zkljhdqopsjdpzhfklqsjnd';
        $formatted_phone_number = $this->h_fabrik->getFormattedPhoneNumberValue($unformatted_phone_number);
        $this->assertSame('', $formatted_phone_number, 'Random string with incorrect characters returns empty string');

        $unformatted_phone_number = '+33 6 12 34 56 78';
        $formatted_phone_number = $this->h_fabrik->getFormattedPhoneNumberValue($unformatted_phone_number);
        $this->assertNotEmpty($formatted_phone_number, 'Correct phone number returns not empty string and by default format is E164');
        $this->assertSame('FR+33612345678', $formatted_phone_number, 'Correct phone number returns correct formatted string');

        $unformatted_phone_number = 'FR+33 612 3456 7 8';
        $formatted_phone_number = $this->h_fabrik->getFormattedPhoneNumberValue($unformatted_phone_number);
        $this->assertNotEmpty($formatted_phone_number, 'Correct phone number returns not empty string');
        $this->assertSame('FR+33612345678', $formatted_phone_number, 'Correct phone number with weird spacing returns correct formatted string');

        $unformatted_phone_number = 'FR+33 612 3456 7 8';
        $formatted_phone_number = $this->h_fabrik->getFormattedPhoneNumberValue($unformatted_phone_number, 2);
        $this->assertNotEmpty($formatted_phone_number, 'Correct phone number returns not empty string');
        $this->assertSame('FR06 12 34 56 78', $formatted_phone_number, 'Setting format 2 (national) returns formatted number correctly');


        $unformatted_phone_number = 'FR+33 612 34za 7 8';
        $formatted_phone_number = $this->h_fabrik->getFormattedPhoneNumberValue($unformatted_phone_number, 2);
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
        $this->assertEquals('', $this->h_fabrik->formatElementValue('', ''), 'Passing an empty element name and raw value should return nothing');
        $this->assertEquals('', $this->h_fabrik->formatElementValue('name', ''), 'Passing an empty raw value should return nothing');
        $this->assertEquals('element', $this->h_fabrik->formatElementValue('', 'element'), 'Passing an empty element name should return raw_value');

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
                    $this->assertEquals($formatted_date, $this->h_fabrik->formatElementValue($element->name, $date, $element->group_id));

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

	                $formatted_date = $this->h_fabrik->formatElementValue($element->name, $date, $element->group_id);

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

		            $this->assertEquals($formatted_date, $this->h_fabrik->formatElementValue($element->name, $date, $element->group_id), 'The birhday is correctly formatted');

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

		            $this->assertEquals('01/12/2015', $this->h_fabrik->formatElementValue($element->name, $date, $element->group_id), 'The birthday is correctly formatted');

		            break;


	            case 'radiobutton':
                case 'checkbox':
                case 'dropdown':
                    $this->assertEquals($params['sub_options']['sub_labels'][0], $this->h_fabrik->formatElementValue($element->name, $params['sub_options']['sub_values'][0], $element->group_id));
                    $this->assertEquals($params['sub_options']['sub_labels'][1], $this->h_fabrik->formatElementValue($element->name, $params['sub_options']['sub_values'][1], $element->group_id));
                    break;
                case 'yesno':
                    $this->assertEquals(JText::_('JYES'), $this->h_fabrik->formatElementValue($element->name, 1, $element->group_id));
                    $this->assertEquals(JText::_('JNO'), $this->h_fabrik->formatElementValue($element->name, 0, $element->group_id));
                    break;
                case 'textarea':
                    $this->assertEquals("This is<br />\n a test", $this->h_fabrik->formatElementValue($element->name, "This is
 a test", $element->group_id));
                    break;
                case 'databasejoin':
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);

                    // Query to get the first value of the join_key_column
                    $query->select($db->quoteName($params['join_key_column']))
                        ->from($db->quoteName($params['join_db_name']))
                        ->setLimit(1);

                    $db->setQuery($query);
                    $firstKeyValue = $db->loadResult();

                    $query = $db->getQuery(true);

	                if ($params['database_join_display_type'] != 'checkbox' && $params['database_join_display_type'] != 'multilist')
	                {
		                if (!empty($params['join_val_column_concat'])) {
			                $lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
			                $params['join_val_column_concat'] = str_replace('{thistable}', $params['join_db_name'], $params['join_val_column_concat']);
			                $params['join_val_column_concat'] = str_replace('{shortlang}', $lang, $params['join_val_column_concat']);

			                $query->select($db->quoteName($params['join_val_column_concat']));
		                } else {
			                $query->select($db->quoteName($params['join_val_column']));
		                }

		                $query->from($db->quoteName($params['join_db_name']));

		                $db->setQuery($query);
		                $formatted_value = $db->loadResult();

		                $this->assertEquals($formatted_value, $this->h_fabrik->formatElementValue($element->name, $firstKeyValue, $element->group_id, $user_id));
	                }
					else
					{
						$select    = $db->quoteName($params['join_val_column']);
						if (!empty($params['join_val_column_concat']))
						{
							$select = 'CONCAT(' . $params['join_val_column_concat'] . ')';
							$select = preg_replace('#{thistable}#', 'jd', $select);
							$select = preg_replace('#{shortlang}#', substr(JFactory::getLanguage()->getTag(), 0, 2), $select);
							if(!empty($applicant_id)){
								$query  = preg_replace('#{my->id}#', $applicant_id, $query);
							}
						}

						$query->clear()
							->select($db->quoteName('fl.db_table_name'))
							->from($db->quoteName('#__fabrik_lists', 'fl'))
							->leftJoin($db->quoteName('#__fabrik_forms', 'ff') . ' ON ' . $db->quoteName('ff.id') . ' = ' . $db->quoteName('fl.form_id'))
							->leftJoin($db->quoteName('#__fabrik_formgroup', 'ffg') . ' ON ' . $db->quoteName('ffg.form_id') . ' = ' . $db->quoteName('ff.id'))
							->leftJoin($db->quoteName('#__fabrik_elements', 'fe') . ' ON ' . $db->quoteName('fe.group_id') . ' = ' . $db->quoteName('ffg.group_id'))
							->where($db->quoteName('fe.group_id') . ' = ' . $db->quote($element->group_id));


						$db->setQuery($query);
						$table = $db->loadResult();

						$query->clear()
							->select($select)
							->from($db->quoteName($table . '_repeat_' . $element->name, 't'))
							->leftJoin($db->quoteName($params['join_db_name'], 'jd') . ' ON ' . $db->quoteName('jd.' . $params['join_key_column']) . ' = ' . $db->quoteName('t.' . $element->name))
							->where($db->quoteName('parent_id') . ' = ' . $db->quote($user_id));

						$db->setQuery($query);
						$formatted_value = $db->loadResult();

						$this->assertEquals($formatted_value, $this->h_fabrik->formatElementValue($element->name, $firstKeyValue, $element->group_id, $user_id));
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

		            $this->assertEquals($formatted_value, $this->h_fabrik->formatElementValue($element->name, $firstKeyValue, $element->group_id));
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

		            $this->assertEquals('******', $this->h_fabrik->formatElementValue($element->name, $password, $element->group_id));
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

		            $this->assertEquals($password, $this->h_fabrik->formatElementValue($element->name, $password, $element->group_id));

            }
        }
    }
}