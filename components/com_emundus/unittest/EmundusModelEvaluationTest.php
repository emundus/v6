<?php
use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');
include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once (__DIR__ . '/../models/evaluation.php');
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
    private $m_eval;

    public function __construct(?string $name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->m_eval = new EmundusModelEvaluation;
    }

    // simple test case (example)
    public function testFoo() {
        $foo = true;
        $this->assertSame(true, $foo);
    }

    public function testGetAttachmentByIds() {
        /// test case 1
        $first_output_data = array(
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

        $this->assertSame($first_output_data, reset($this->m_eval->getAttachmentByIds(array(166))));

        /// make failed test here .....

        /// test case 2
        $second_output_data = array(
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
        $this->assertSame($second_output_data, $this->m_eval->getAttachmentByIds(array(166,167,168)));

        /// make failed test here .....
    }

    public function testGetLettersByAttachmentIds() {
        $first_output_data = '11';

        // test case 1
        $this->assertSame($first_output_data, reset($this->m_eval->getLettersByAttachmentIds(array(165))));

        /// make failed test here .....

        $second_output_data = array('11','12','13','14','15','16','17');

        // success test
        $this->assertSame($second_output_data, $this->m_eval->getLettersByAttachmentIds(array(165,166,167,168,169)));
    }

    /// test function getLettersByFnums with 2 params: fnums = array(), attachments = array()
    public function testGetLettersByFnums() {
        /// test case 1
        $first_output_data_no_false = array(
            'attachments' => array(
                array(
                    'id'            => '166',
                    'lbl'           => '_rejection_letter',
                    'value'         => 'Lettre de refus',
                    'description'   => '<p>Lettre de refus</p>',
                    'allowed_types' => 'pdf;doc;docx;xls;xlsx;jpg;odt',
                    'nbmax'         => '0',
                    'ordering'      => '0',
                    'published'     => '1',
                    'ocr_keywords'  => NULL,
                    'category'      => NULL,
                    'video_max_length'  => '60'
                )
            ),
            'emails' => array('120')
        );

        $this->assertSame($first_output_data_no_false, $this->m_eval->getLettersByFnums('2018121618114600000070000113', true));
        /// make failed test here ..... with fnum = 2018121916122400000020000121

        $first_output_data_with_false = false;
        $this->assertSame($first_output_data_with_false, $this->m_eval->getLettersByFnums('2018121809575300000080000117', true));
        /// make failed test here .....

        $second_output_data_no_false = array('12');
        $this->assertSame($second_output_data_no_false, $this->m_eval->getLettersByFnums('2018121618114600000070000113', false));
        /// make failed test here ..... with fnum = 2018121916122400000020000121

        $second_output_data_with_false = false;
        $this->assertSame($first_output_data_with_false, $this->m_eval->getLettersByFnums('2018122215550200000080000157', false));
        /// make failed test here .....
    }

    /// test function getLettersByProgrammesStatus with 2 params: programs = array(), status = array()
    public function testGetLettersByProgrammesStatus() {

    }

    /// test function getLettersByFnumsTemplates with 2 params: fnums = array(), templates = array()
    public function testGetLettersByFnumsTemplates() {
        $first_output_data = null;
    }

    /// test function getFilesByAttachmentFnums

}
