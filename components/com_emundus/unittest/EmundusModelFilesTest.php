<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */


/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use PHPUnit\Framework\TestCase;

ini_set('display_errors', false);
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');
include_once(JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_SITE . '/components/com_emundus/models/files.php');

jimport('joomla.user.helper');
jimport('joomla.application.application');
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelFilesTest extends TestCase{
    private $m_files;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_files = new EmundusModelFiles;

        //$coordinator = @EmundusUnittestHelperSamples::createSampleUser(2,'gestionnaire@emundus.fr');
    }

    // Brice
    /*public function testTagFile() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();

        $fnum = @EmundusUnittestHelperSamples::createSampleFile(1,$user->id);
        $tag = @EmundusUnittestHelperSamples::createSampleTag();

        $this->assertSame(true,$this->m_files->tagFile([$fnum],[$tag]));

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    // Merveille
    public function testUpdatePublish() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(1,$user->id);
        $publishvalues = [1,0,-1,-3];
        foreach ($publishvalues as $publish){
            if($publish == -3){
                $this->assertSame(false,$this->m_files->updatePublish($fnum,$publish));
            } else {
                $this->assertSame(true,$this->m_files->updatePublish($fnum,$publish));
            }

        }

    }

    // Jeremy
    public function testUpdateState() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(1,$user->id);
        $status_step = @EmundusUnittestHelperSamples::createSampleStatus();


    }

    // Bazile
    public function testGetFormProgress() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(1,$user->id);

        $this->assertIsArray($this->m_files->getFormProgress([$fnum]));
        //
        // $this->assertIsArray($this->m_files->getFormProgress($fnum));

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    // Benjamin
    public function testDeleteFile() {
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(1,$user->id);

        $this->assertSame(true, $this->m_files->deleteFile($fnum));

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    //Duy
    public function testGetTagsByFnum(){
        $user = @EmundusUnittestHelperSamples::createSampleUser();
        $fnum = @EmundusUnittestHelperSamples::createSampleFile(1,$user->id);

        $this->assertIsArray(true,$this->m_files->getTagsByFnum([$fnum]));

        $u = JUser::getInstance($user->id);
        $u->delete();
    }*/
}
