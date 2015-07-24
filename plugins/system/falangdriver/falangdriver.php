<?php
/**
 * @package     Falang Driver
 * @subpackage  Add Falang Driver
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

//Global definitions use for front
if( !defined('DS') ) {
    define( 'DS', DIRECTORY_SEPARATOR );
}


jimport('joomla.plugin.plugin');

/**
 * Falang Driver Plugin
 */
class plgSystemFalangdriver extends JPlugin
{

    public function __construct(& $subject, $config = array())
    {
        parent::__construct($subject, $config);
	    $this->loadLanguage();

        // This plugin is only relevant for use within the frontend!
        if (JFactory::getApplication()->isAdmin())
        {
            return;
        }
    }

    /**
     * System Event: onAfterInitialise
     *
     * @return	string
     */
    function onAfterInitialise()
    {
        // This plugin is only relevant for use within the frontend!
        if (JFactory::getApplication()->isAdmin())
        {
            return;
        }
        $this->setupDatabaseDriverOverride();
        //fix for joomla > 3.4.0
        $app = JFactory::getApplication();
        if ($app->isSite()) {
            $router = $app->getRouter();

            // attach build rules for translation on SEF
            $router->attachBuildRule(array($this, 'buildRule'));

            // attach build rules for translation on SEF
            $router->attachParseRule(array($this, 'parseRule'));
        }
        //end fix
    }

    //not implemented
    public function buildRule(&$router, &$uri)
    {
        return array();
    }

    public function parseRule(&$router, &$uri) {
        static $done = false;
        if (!$done) {
            $done = true;
            //reload menu
            JFactory::getApplication()->getMenu()->__construct();
            //rewrite Menu route with translated alias
            $app = JFactory::getApplication();
            $menu = $app->getMenu()->getMenu();

        }
        return array();
    }

    public function isFalangDriverActive() {
        $db = JFactory::getDBO();
        if (!is_a($db,"JFalangDatabase")){
           return false;
        }
           return true;
    }

    function onAfterDispatch()
    {
        if (JFactory::getApplication()->isSite() && $this->isFalangDriverActive()) {
            include_once( JPATH_ADMINISTRATOR . '/components/com_falang/version.php');
            $version = new FalangVersion();
            if ($version->_versiontype == 'free'  ) {
                FalangManager::setBuffer();
            }
            return true;
        }
    }


    function setupDatabaseDriverOverride()
    {
        //override only the override file exist
        if (file_exists(dirname(__FILE__) . '/falang_database.php'))
        {

            require_once( dirname(__FILE__) . '/falang_database.php');

            $conf = JFactory::getConfig();

            $host = $conf->get('host');
            $user = $conf->get('user');
            $password = $conf->get('password');
            $db = $conf->get('db');
            $dbprefix = $conf->get('dbprefix');
            $driver = $conf->get('dbtype');
            $debug = $conf->get('debug');

            $options = array('driver' => $driver,"host" => $host, "user" => $user, "password" => $password, "database" => $db, "prefix" => $dbprefix, "select" => true);
            $db = new JFalangDatabase($options);
            $db->debug($debug);


            if ($db->getErrorNum() > 2)
            {
                JError::raiseError('joomla.library:' . $db->getErrorNum(), 'JDatabase::getInstance: Could not connect to database <br/>' . $db->getErrorMsg());
            }

            // replace the database handle in the factory
            JFactory::$database = null;
            JFactory::$database = $db;

            $test = JFactory::getDBO();

        }

    }

    private function setBuffer()
    {
        $doc = JFactory::getDocument();
        $cacheBuf = $doc->getBuffer('component');

        $cacheBuf2 =
            '<div><a title="Faboba : Cr&eacute;ation de composant'.
            'Joomla" style="font-size: 8px;; visibility: visible;'.
            'display:inline;" href="http://www.faboba'.
            '.com" target="_blank">FaLang tra'.
            'nslation syste'.
            'm by Faboba</a></div>';

        if ($doc->_type == 'html')
            $doc->setBuffer($cacheBuf . $cacheBuf2,'component');

    }



}