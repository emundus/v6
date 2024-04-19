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

    /*
     * Test the formatElementValue() method
     * Should return the value formatted in a good way
     */
    public function testformatElementValue()
    {
        $this->assertEquals('', $this->h_fabrik->formatElementValue('', ''), 'Passing an empty element name and raw value should return nothing');
        $this->assertEquals('', $this->h_fabrik->formatElementValue('name', ''), 'Passing an empty raw value should return nothing');
        $this->assertEquals('element', $this->h_fabrik->formatElementValue('', 'element'), 'Passing an empty element nam should return raw_value');

        // Test case with a date
        $this->assertEquals('04/04/2024', $this->h_fabrik->formatElementValue('end_date', "2024-04-04"));

        // Test cases with radiobutton, checkbox and dropdown
        $this->assertEquals('option1', $this->h_fabrik->formatElementValue('checkbox', "1"));
        $this->assertEquals('option2', $this->h_fabrik->formatElementValue('dropdown', "2"));
        $this->assertEquals('option1', $this->h_fabrik->formatElementValue('radiobutton', "1"));

        // Test cases with yes/no
        $this->assertEquals('No', $this->h_fabrik->formatElementValue('yesno', "0"));
        $this->assertEquals('Yes', $this->h_fabrik->formatElementValue('yesno', "1"));

        // Test case with textarea
        $this->assertEquals("Beautiful text.<br />\n        It is awesome !", $this->h_fabrik->formatElementValue('textarea', "Beautiful text.
        It is awesome !"));

        // Test cases with databasejoin
        $this->assertEquals('Argentina, Argentine Republic', $this->h_fabrik->formatElementValue('country', "10"));
        $this->assertEquals('Bissau-guinean', $this->h_fabrik->formatElementValue('nationality', "24"));
        $this->assertEquals('Accepté', $this->h_fabrik->formatElementValue('status', "3"));
        $this->assertEquals('1 paiement / campagne', $this->h_fabrik->formatElementValue('payment_type', "1"));
    }
}