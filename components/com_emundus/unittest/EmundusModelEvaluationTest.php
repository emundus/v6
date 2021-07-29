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
include_once (__DIR__ . '/../models/profile.php');
include_once (__DIR__ . '/../models/messages.php');
jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelEvaluationTest extends TestCase {
    private $m_messages;
    private $m_profile;
    private $m_eval;

    public function __construct(?string $name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->m_profile = new EmundusModelProfile;
        $this->m_messages = new EmundusModelMessages;
        $this->m_eval = new EmundusModelEvaluation;
    }

    public function testGetEvaluationById() {
        $out = $this->m_messages->getMessageRecapByFnum('2018121618114600000070000113');
        var_dump($out['message_recap']);die;
    }
}
