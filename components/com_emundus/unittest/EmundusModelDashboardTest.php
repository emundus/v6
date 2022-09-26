<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');
include_once (JPATH_SITE . '/components/com_emundus/models/dashboard.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelDashboardTest extends TestCase
{
    private $m_dashboard;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_dashboard = new EmundusModeldashboard;
    }

    /*public function testGetarticle()
    {
        // TEST 1 : Article is return
        $widget_article = $this->m_dashboard->getarticle(7,1040);
        $this->assertIsString($widget_article);

        // TEST 2 : No article found
        $widget_article = $this->m_dashboard->getarticle(7,9999);
        $this->assertNull($widget_article);
    }

    public function testGetallwidgetsbysize()
    {
        $user = @EmundusUnittestHelperSamples::createSampleUser(2);

        // TEST 1 : Get widgets of full size
        $full_size_widgets = $this->m_dashboard->getallwidgetsbysize(10,$user->id);
        $this->assertCount(4,$full_size_widgets);

        // TEST 2 : Get widgets of size 0, results waiting : no widgets
        $size_1 = $this->m_dashboard->getallwidgetsbysize(0,$user->id);
        $this->assertCount(0,$size_1);

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    public function testRenderchartbytag()
    {
        // TEST 1 : Eval a fusionchart widget
        $widget_fusion_charts = $this->m_dashboard->renderchartbytag(5);
        $this->assertNotEmpty($widget_fusion_charts);

        // TEST 2 : Eval an other widget that return just html
        $widget_html = $this->m_dashboard->renderchartbytag(9);
        $this->assertIsString($widget_html);
    }

    public function testCRUDDashboard()
    {
        $user = @EmundusUnittestHelperSamples::createSampleUser(2);

        //Dashboard is empty at first connection
        $dashboard = $this->m_dashboard->getDashboard($user->id);
        $this->assertEmpty($dashboard);

        if (empty($dashboard)) {
            // Create our dashbord
            $created = $this->m_dashboard->createDashboard($user->id);
            $this->assertTrue($created);

            // Find our dashboard
            $dashboard = $this->m_dashboard->getDashboard($user->id);
            $this->assertNotEmpty($dashboard);

            // Update our dashboard
            $update = $this->m_dashboard->updatemydashboard(8,2, $user->id);
            $this->assertTrue($update);

            // Check if the new widget has been added to our dashboard
            $widgets = $this->m_dashboard->getwidgets($user->id);
            $new_widget = false;
            foreach ($widgets as $widget){
                if($widget->id == 8 && $widget->position == 2){
                    $new_widget = true;
                }
            }
            $this->assertTrue($new_widget);

            // We can try to delete it
            $deleted = $this->m_dashboard->deleteDashboard($user->id);
            $this->assertTrue($deleted);

            // Dashboard is now empty
            $dashboard = $this->m_dashboard->getDashboard($user->id);
            $this->assertEmpty($dashboard);
        }

        $u = JUser::getInstance($user->id);
        $u->delete();
    }

    public function testGetwidgets()
    {
        // TEST 1 : Get widgets of a coordinator
        $user = @EmundusUnittestHelperSamples::createSampleUser(2);

        $dashboard = $this->m_dashboard->getDashboard($user->id);
        $this->assertEmpty($dashboard);

        if (empty($dashboard)) {
            $created = $this->m_dashboard->createDashboard($user->id);
            $this->assertTrue($created);

            $widgets = $this->m_dashboard->getwidgets($user->id);
            $this->assertNotEmpty($widgets);
        }

        $u = JUser::getInstance($user->id);
        $u->delete();

        // TEST 2 : Get widgets of an evaluator
        $user = @EmundusUnittestHelperSamples::createSampleUser(6);

        $dashboard = $this->m_dashboard->getDashboard($user->id);
        $this->assertEmpty($dashboard);

        if (empty($dashboard)) {
            $created = $this->m_dashboard->createDashboard($user->id);
            $this->assertTrue($created);

            $widgets = $this->m_dashboard->getwidgets($user->id);
            $this->assertNotEmpty($widgets);
        }

        $u = JUser::getInstance($user->id);
        $u->delete();
    }*/
}
