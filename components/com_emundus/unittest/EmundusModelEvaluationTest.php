<?php
use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');
include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
//require_once(__DIR__ . '/../../../libraries/src/Factory.php');
include_once (__DIR__ . '/../models/evaluation.php');
//include_once (__DIR__ . '/../models/profile.php');
//include_once (__DIR__ . '/../models/messages.php');
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

class EmundusModelEvaluationTest extends TestCase {
//    private $m_messages;
//    private $m_profile;
    private $m_eval;

    public function __construct(?string $name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
//        $this->m_profile = new EmundusModelProfile;
//        $this->m_messages = new EmundusModelMessages;
        $this->m_eval = new EmundusModelEvaluation;
    }

    // simple test case (example)
    public function testFoo() {
        $foo = true;
        $this->assertSame(true, $foo);

        /// output:
        /// PHPUnit 9.5.7 by Sebastian Bergmann and contributors.
        //
        //
        //
        //Time: 00:00.023, Memory: 36.00 MB
        //
        //OK (1 test, 1 assertion)
        //
        //Process finished with exit code 0
    }

    // standard method test name syntax: test<method_name>
    public function testGetAttachmentByIdsSimple() {
        /// success test
        $input_data_refus = array(
            'id' => '166',
            'lbl' => '_rejection_letter',
            'value' => 'Lettre de refus',
            'description' => '<p>Lettre de refus</p>',
            'allowed_types' => 'pdf;doc;docx;xls;xlsx;jpg;odt',
            'nbmax' => '0',
            'ordering' => '0',
            'published' => '1',
            'ocr_keywords' => NULL,
            'category' => NULL,
            'video_max_length' => '60'
        );

        $this->assertSame($input_data_refus, reset($this->m_eval->getAttachmentByIds(array(166))));       // using reset to get the first element of array

        /// failed test --> when failed :: see the differences between actual and expected results (uncomment to test)
        //$this->assertEquals($input_data_refus, reset($this->m_eval->getAttachmentByIds(array(165))));       // using reset to get the first element of array
    }

    // standard method test name syntax: test<method_name>
    public function testGetAttachmentByIdsMultiple() {
        $input_data = array(
            array(
                'id' => '166',
                'lbl' => '_rejection_letter',
                'value' => 'Lettre de refus',
                'description' => '<p>Lettre de refus</p>',
                'allowed_types' => 'pdf;doc;docx;xls;xlsx;jpg;odt',
                'nbmax' => '0',
                'ordering' => '0',
                'published' => '1',
                'ocr_keywords' => NULL,
                'category' => NULL,
                'video_max_length' => '60'
            ),
            array(
                'id' => '167',
                'lbl' => '_pre_admission_letter',
                'value' => 'Lettre de pre-admission',
                'description' => '<p>Lettre de pre-admission</p>',
                'allowed_types' => 'pdf;doc;docx;xls;xlsx;jpg;odt',
                'nbmax' => '0',
                'ordering' => '0',
                'published' => '1',
                'ocr_keywords' => NULL,
                'category' => NULL,
                'video_max_length' => '60'
            ),
            array(
                'id' => '168',
                'lbl' => '_financial_letter',
                'value' => 'Financial Statement',
                'description' => '<p>Financial Statement</p>',
                'allowed_types' => 'pdf;doc;docx;xls;xlsx;jpg;odt',
                'nbmax' => '0',
                'ordering' => '0',
                'published' => '1',
                'ocr_keywords' => NULL,
                'category' => NULL,
                'video_max_length' => '60'
            ),
        );

        // success test
        $this->assertSame($input_data, $this->m_eval->getAttachmentByIds(array(166,167,168)));

        // failed test
        $this->assertSame($input_data, $this->m_eval->getAttachmentByIds(array(166)));
        $this->assertSame($input_data, $this->m_eval->getAttachmentByIds(array(165)));

        // failed test
        $this->assertSame($input_data, $this->m_eval->getAttachmentByIds(array(166,167)));
    }
}
