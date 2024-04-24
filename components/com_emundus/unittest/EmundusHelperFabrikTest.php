<?php


use PHPUnit\Framework\TestCase;

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

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('fe.id, fe.name, fe.group_id as group_id, fe.plugin as plugin, fe.params as params')
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
                    $formated_value = "This is<br />\n a test";
                    $this->assertEquals($formated_value, $this->h_fabrik->formatElementValue($element->name, "This is
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

                    $this->assertEquals($formatted_value, $this->h_fabrik->formatElementValue($element->name, $firstKeyValue, $element->group_id));

                    break;
            }
        }
    }
}