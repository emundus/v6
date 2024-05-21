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
include_once(JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');

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

	public function testEncryptDatas() {
		$raw_phonenumber = '+33 6 12 34 56 78';
		$encrypted_phonenumber = EmundusHelperFabrik::encryptDatas($raw_phonenumber, 'emundus_phonenumber', 'gKGin8pc4BEqA8cA');
		$this->assertNotEmpty($encrypted_phonenumber, 'Correct phone number returns not empty string when we pass encryption key');
		$this->assertNotSame($raw_phonenumber, $encrypted_phonenumber, 'Encrypted phone number is different from raw phone number');

		$raw_checkbox = '["Valeur 1","Valeur 2"]';
		$encrypted_checkbox = EmundusHelperFabrik::encryptDatas($raw_checkbox, 'checkbox');
		$this->assertNotEmpty($encrypted_checkbox, 'Correct phone number returns not empty string, encryption key use secret by default');
		$this->assertNotSame($raw_checkbox, $encrypted_checkbox, 'Encrypted phone number is different from raw phone number');
	}

	public function testDecryptDatas() {
		$raw_phonenumber = '+33 6 12 34 56 78';
		$encrypted_phonenumber = EmundusHelperFabrik::encryptDatas($raw_phonenumber, 'emundus_phonenumber', 'gKGin8pc4BEqA8cA');
		$this->assertNotSame($raw_phonenumber, EmundusHelperFabrik::decryptDatas($encrypted_phonenumber,'emundus_phonenumber', 'uMcvs401XwYPml9Q'), 'Decrypted phone number is different from raw phone number if we pass wrong key');
		$this->assertSame($raw_phonenumber, EmundusHelperFabrik::decryptDatas($encrypted_phonenumber,'emundus_phonenumber', 'gKGin8pc4BEqA8cA'), 'Decrypted phone number is the same as raw phone number');

		$raw_checkbox = '["Valeur 1","Valeur 2"]';
		$encrypted_checkbox = EmundusHelperFabrik::encryptDatas($raw_checkbox, 'checkbox');
		$this->assertSame($raw_checkbox, EmundusHelperFabrik::decryptDatas($encrypted_checkbox,'checkbox'), 'Decrypted checkbox is the same as raw checkbox');
	}
}