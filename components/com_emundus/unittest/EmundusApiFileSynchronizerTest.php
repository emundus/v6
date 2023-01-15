<?php
use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once (__DIR__ . '/../models/sync.php');
include_once (__DIR__ . '/../classes/api/FileSynchronizer.php');
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

class EmundusApiFileSynchronizerTest extends TestCase
{

    /**
     * @var EmundusApiFileSynchronizer
     */
    private $api;
    private $m_sync;
    private $db;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_sync = new EmundusModelSync;
        $this->db = JFactory::getDbo();
    }

    public function testConstruct()
    {
        $this->assertTrue(true);
    }

    public function testSaveConfig(){
        $config_sample = '{"tree":[{"id":1,"level":1,"type":"[CAMPAIGN_LABEL]","parent":0,"childrens":[{"id":"1_1","level":2,"type":"[CAMPAIGN_YEAR]","parent":1,"childrens":[{"id":"1_1_1","level":3,"type":"[APPLICANT_NAME]","parent":"1_1","childrens":[]}]}]}],"name":"[FNUM]_[DOCUMENT_TYPE]_[APPLICANT_NAME]"}';

        // TEST 1 - GED configuration
        $this->assertSame(true,$this->m_sync->saveConfig($config_sample,'ged'));
    }

    public function testGetConfig(){
        $config_sample = '{"tree":[{"id":1,"level":1,"type":"[CAMPAIGN_LABEL]","parent":0,"childrens":[{"id":"1_1","level":2,"type":"[CAMPAIGN_YEAR]","parent":1,"childrens":[{"id":"1_1_1","level":3,"type":"[APPLICANT_NAME]","parent":"1_1","childrens":[]}]}]}],"name":"[FNUM]_[DOCUMENT_TYPE]_[APPLICANT_NAME]"}';

        // TEST 1 - GED configuration
        $this->m_sync->saveConfig($config_sample,'ged');
        $this->assertNotEmpty($this->m_sync->getConfig('ged'));

        // TEST 2 - FAILED WAITING - Dropbox is not configured for moment
        $this->assertEmpty($this->m_sync->getConfig('dropbox'));
    }

    public function testGetDocuments(){
        // TEST 1 - Get attachments type
        $this->assertIsArray($this->m_sync->getDocuments());
    }

    public function testUpdateDocumentSync(){
        // TEST 1 - Add a document to ged sync
        $this->assertSame(true,$this->m_sync->updateDocumentSync(1,1));

        // TEST 2 - Update a document that not exist, return true
        $this->assertSame(true,$this->m_sync->updateDocumentSync(999,1));

        // TEST 3 - Remove the sync from a document
        $this->assertSame(true,$this->m_sync->updateDocumentSync(1,0));
    }

    public function testUpdateDocumentSyncMethod(){
        // TEST 1 - Add a document to ged sync
        $this->assertSame(true,$this->m_sync->updateDocumentSyncMethod(1,'read'));

        // TEST 2 - Update a document that not exist, return true
        $this->assertSame(true,$this->m_sync->updateDocumentSyncMethod(999,'read'));

        // TEST 3 - Remove the sync from a document
        $this->assertSame(true,$this->m_sync->updateDocumentSyncMethod(1,'write'));

        // TEST 3 - Remove the method from a document
        $this->assertSame(true,$this->m_sync->updateDocumentSyncMethod(1,null));
    }
}
