<?php


use PHPUnit\Framework\TestCase;
use Joomla\CMS\Factory;

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
				$query = $db->getQuery(true);

				$query->clear()
					->update($db->quoteName('#__fabrik_elements'))
					->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
					->where($db->quoteName('id') . ' = ' . $element->id)
					->limit(1);
				$db->setQuery($query);
				$db->execute();
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
					$query = $db->getQuery(true);

					$query->clear()
						->update($db->quoteName('#__fabrik_elements'))
						->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
						->where($db->quoteName('id') . ' = ' . $db->quote($element->id))
						->limit(1);
					$db->setQuery($query);
					$db->execute();
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