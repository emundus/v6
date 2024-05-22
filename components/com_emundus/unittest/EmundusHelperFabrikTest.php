<?php


use Joomla\CMS\Factory;
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

	public function testEncryptDatas() {
		$raw_phonenumber = '+33 6 12 34 56 78';
		$encrypted_phonenumber = EmundusHelperFabrik::encryptDatas($raw_phonenumber, 'gKGin8pc4BEqA8cA');
		$this->assertNotEmpty($encrypted_phonenumber, 'Correct phone number returns not empty string when we pass encryption key');
		$this->assertNotSame($raw_phonenumber, $encrypted_phonenumber, 'Encrypted phone number is different from raw phone number');

		$raw_checkbox = '["Valeur 1","Valeur 2"]';
		$encrypted_checkbox = EmundusHelperFabrik::encryptDatas($raw_checkbox);
		$this->assertNotEmpty($encrypted_checkbox, 'Correct phone number returns not empty string, encryption key use secret by default');
		$this->assertNotSame($raw_checkbox, $encrypted_checkbox, 'Encrypted phone number is different from raw phone number');
	}

	public function testDecryptDatas() {
		$raw_phonenumber = '+33 6 12 34 56 78';
		$encrypted_phonenumber = EmundusHelperFabrik::encryptDatas($raw_phonenumber, 'gKGin8pc4BEqA8cA');
		$this->assertNotSame($raw_phonenumber, EmundusHelperFabrik::decryptDatas($encrypted_phonenumber, 'uMcvs401XwYPml9Q'), 'Decrypted phone number is different from raw phone number if we pass wrong key');
		$this->assertSame($raw_phonenumber, EmundusHelperFabrik::decryptDatas($encrypted_phonenumber, 'gKGin8pc4BEqA8cA'), 'Decrypted phone number is the same as raw phone number');

		$raw_checkbox = '["Valeur 1","Valeur 2"]';
		$encrypted_checkbox = EmundusHelperFabrik::encryptDatas($raw_checkbox);
		$this->assertSame($raw_checkbox, EmundusHelperFabrik::decryptDatas($encrypted_checkbox), 'Decrypted checkbox is the same as raw checkbox');
	}

	public function testMigrateEncryptDatas() {
		$cipher = "aes-128-cbc";
		$encryption_key = Factory::getConfig()->get('secret');
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
		
		$datas = [
			[
				'value' => '+33 6 12 34 56 78',
				'plugin' => 'text'
			],
			[
				'value' => '["Valeur 1","Valeur 2"]',
				'plugin' => 'checkbox'
			],
			[
				'value' => 'FR973000100552C255000000002',
				'plugin' => 'iban'
			]
		];

		$datas_old_encrypted = [];
		$datas_new_encrypted = [];

		foreach ($datas as $data) {
			$datas_new_encrypted[] = [
				'plugin' => $data['plugin'],
				'value' => EmundusHelperFabrik::encryptDatas($data['value'], $encryption_key, $cipher, $iv)
			];

			if($data['plugin'] == 'checkbox')
			{
				$contents = json_decode($data['value']);
				$checkbox_encrypted = [];
				foreach ($contents as $index => $subvalue) {
					$checkbox_encrypted[] = openssl_encrypt($subvalue, $cipher, $encryption_key, 0);
				}
				$datas_old_encrypted[] = [
					'plugin' => $data['plugin'],
					'value' => json_encode($checkbox_encrypted)
				];
			}
			// Try to not encrypt iban
			elseif ($data['plugin'] == 'iban') {
				$datas_old_encrypted[] = [
					'plugin' => $data['plugin'],
					'value' =>$data['value']
				];
			}
			else
			{
				$datas_old_encrypted[] = [
					'plugin' => $data['plugin'],
					'value' => openssl_encrypt($data['value'], $cipher, $encryption_key, 0)
				];
			}
		}

		$this->assertNotSame($datas_new_encrypted,$datas_old_encrypted, 'Datas are correctly encrypted with different algorithm');

		$migrated_datas = EmundusHelperFabrik::migrateEncryptDatas($cipher, $cipher, $encryption_key, $encryption_key, $datas_old_encrypted, $iv);
		$this->assertSame($datas_new_encrypted,$migrated_datas, 'Datas are correctly encrypted with same encryption key and same algorithm');
	}
}