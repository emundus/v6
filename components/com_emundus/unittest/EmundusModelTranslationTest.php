<?php
use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once (__DIR__ . '/../models/translations.php');
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

// standard class name test syntax: <class_name>Test extends TestCase

class EmundusModelTranslationTest extends TestCase
{
    private $m_translations;

    private $db;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_translations = new EmundusModelTranslations;
        $this->db = JFactory::getDbo();
    }

    // simple test case to check if phpunit working
    public function testFoo() {
        $foo = true;
        $this->assertSame(true, $foo);
    }

    public function testInsertTranslation(){
        // Test insert translation with empty key return false
        $inserted = $this->m_translations->insertTranslation('', 'Test élément avec clé vide', 'fr-FR', '', 'override', 'fabrik_elements', 999999);
        $this->assertFalse($inserted);

        // Make sure $^*()=+\[<?; are not allowed in tag
        $inserted = $this->m_translations->insertTranslation('E[L<$EN^()T_TE\T', 'Test élément avec clé vide', 'fr-FR', '', 'override', 'fabrik_elements', 999999);
        $this->assertFalse($inserted);

        if(empty($this->m_translations->getTranslations('override','fr-FR','','','fabrik_elements',0,'label', 'ELEMENT_TEST'))) {
            // TEST 1 - Insert a basic translation of a fabrik_element
            $this->assertSame(true, $this->m_translations->insertTranslation('ELEMENT_TEST', 'Mon élément de test', 'fr-FR', '', 'override', 'fabrik_elements', 9999, 'label'));
        } else {
            // TEST 2 - Failed waiting - Insert a basic translation of a fabrik_element
            $this->assertSame(false, $this->m_translations->insertTranslation('ELEMENT_TEST', 'Mon élément de test', 'fr-FR', '', 'override', 'fabrik_elements', 9999, 'label'));
        }

        if(empty($this->m_translations->getTranslations('override','en-GB','','','fabrik_elements',0, 'label', 'ELEMENT_TEST'))) {
            // TEST 1 - Insert a basic translation of a fabrik_element in english file
            $this->assertSame(true, $this->m_translations->insertTranslation('ELEMENT_TEST', 'My element', 'en-GB', '', 'override', 'fabrik_elements', 9999, 'label'));
        } else {
            // TEST 2 - Failed waiting - Insert a basic translation of a fabrik_element in english file
            $this->assertSame(false, $this->m_translations->insertTranslation('ELEMENT_TEST', 'My element', 'en-GB', '', 'override', 'fabrik_elements', 9999, 'label'));
        }
    }

    public function testGetTranslations() {
        // TEST 1 - GET ALL FABRIK TRANSLATIONS BY DEFAULT
        $this->assertNotEmpty($this->m_translations->getTranslations());

        // TEST 2 - GET TYPE NOT EXISTING, EMPTY ARRAY HAS TO BE RETURNED
        $this->assertEmpty($this->m_translations->getTranslations('mon_type'));

        // TEST 3 - PASS TYPE NOT STRING, EMPTY ARRAY HAS TO BE RETURNED
        $this->assertEmpty($this->m_translations->getTranslations(1));

        // TEST 4 - GET FABRIK TRANSLATIONS IN FRENCH
        $this->assertNotEmpty($this->m_translations->getTranslations('override','fr-FR'));

        // TEST 5 - GET FABRIK TRANSLATIONS IN ENGLISH
        $this->assertNotEmpty($this->m_translations->getTranslations('override','en-GB'));

        // TEST 6 - GET FABRIK TRANSLATIONS IN LANGUAGE NOT EXISTING
        $this->assertEmpty($this->m_translations->getTranslations('override','pt-PT'));

        // TEST 7 - GET FABRIK OPTIONS of the element 7777
        $this->assertNotEmpty($this->m_translations->getTranslations('override','*','','','',9999));

        // TEST 8 - GET FABRIK ELEMENTS on lang fr-FR
        $this->assertNotEmpty($this->m_translations->getTranslations('override','fr-FR','','','fabrik_elements'));

        // TEST 9 - GET FABRIK ELEMENTS on lang en-GB
        $this->assertNotEmpty($this->m_translations->getTranslations('override','en-GB','','','fabrik_elements'));

        // TEST 10 - GET TRANSLATIONS WITH SEARCH
        $this->assertNotEmpty($this->m_translations->getTranslations('override','*','Mon élément'));
    }

    public function testUpdateTranslations() {
        $override_original_file_size = filesize(JPATH_SITE . '/language/overrides/fr-FR.override.ini');


        // TEST 1 - Update the translations created before in french
        $this->assertSame('ELEMENT_TEST', $this->m_translations->updateTranslation('ELEMENT_TEST','Mon élement modifié','fr-FR'));

        // TEST 2 - Update the translations created before in english
        $this->assertSame('ELEMENT_TEST', $this->m_translations->updateTranslation('ELEMENT_TEST','My updated element','en-GB'));

        // TEST 3 - Failed waiting - Update the translations created before in portuguesh
        $this->assertSame(false, $this->m_translations->updateTranslation('ELEMENT_TEST','My updated element','pt-PT'));

        // TEST 4 - If no tag given, traduction should return false, request sould not work
        $this->assertSame(false, $this->m_translations->updateTranslation('','My updated element','fr-FR'), 'Make sure that we can\'t add empty tag into override file');

        $override_new_file_size = filesize(JPATH_SITE . '/language/overrides/fr-FR.override.ini');
        $this->assertGreaterThanOrEqual($override_original_file_size, $override_new_file_size, 'New override file size is greater or equal than original override file (make sure override file is not destroyed)');

        // TEST 6 - Succes waiting - Update translations of com_emundus not possible so we insert it in override file
        //$this->assertSame(true,$this->m_translations->updateTranslation('COM_EMUNDUS_EMAIL','Un nouvel email','fr-FR','component'));
    }

    public function testDeleteTranslations() {
        // TEST 1 - Delete translation that we manage in other tests
        $this->assertSame(true,$this->m_translations->deleteTranslation('ELEMENT_TEST'));
    }

    public function testgetTranslationsObject(){
        $this->assertIsArray($this->m_translations->getTranslationsObject());
    }

    public function testgetDefaultLanguage(){
        $this->assertIsObject($this->m_translations->getDefaultLanguage());
    }

    public function testgetAllLanguages(){
        $this->assertIsArray($this->m_translations->getAllLanguages());
    }

    public function testgetOrphelins(){
        $this->assertIsArray($this->m_translations->getOrphelins('fr-FR','en-GB'));
    }

    public function testCheckSetup(){
        $this->assertNotNull($this->m_translations->checkSetup());
    }

    public function testGetPlatformLanguages(){
        $this->assertNotEmpty($this->m_translations->getPlatformLanguages());
    }

    public function testCheckTagIsCorrect()
    {
        $this->assertFalse($this->m_translations->checkTagIsCorrect('', 'Ma traduction', 'insert', 'fr'));
        $this->assertFalse($this->m_translations->checkTagIsCorrect('E[L<$EN^()T_TE\T', 'Ma traduction', 'insert', 'fr'));
        $this->assertTrue($this->m_translations->checkTagIsCorrect('MON_ELEMENT', 'Ma traduction', 'insert', 'fr'));
    }
}
