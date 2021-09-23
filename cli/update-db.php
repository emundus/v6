<?php

if (php_sapi_name() != 'cli')
{
    exit(1);
}

if( !isset($_SERVER['HTTP_HOST']) )
    $_SERVER['HTTP_HOST'] = 'cms';

if( !isset($_SERVER['HTTP_USER_AGENT']) )
    $_SERVER['HTTP_USER_AGENT'] = 'cms';
// We are a valid entry point.
const _JEXEC = 1;

const JDEBUG = 0;

// Define core extension id
const CORE_EXTENSION_ID = 700;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Iniitialize Application
if( version_compare( JVERSION, '3.2.0', '>=' ) ){
    JFactory::getApplication('cms');
}
else if( version_compare( JVERSION, '3.1.0', '>=' ) ){
    JFactory::getApplication('site');
}
else {
    JFactory::getApplication('administrator');
}
// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

// Load the JApplicationCli class
JLoader::import('joomla.application.cli');
JLoader::import('joomla.application.component.helper');
JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.filesystem.file');

/**
 * Update db from sql update files
 */
class UpdateDb extends JApplicationCli
{

public $sql_update_path = '/Applications/MAMP/htdocs/core/administrator/components/com_admin/sql/updates/mysql/';
public $db = null;
public $config = null;


    public function doExecute()
    {
        $executionStartTime = microtime(true);

        #$tmp_path    = $this->app->get('tmp_path');
        #$log_path    = $this->app->get('log_path');
        #$component   = JComponentHelper::getComponent('com_emundus');

        # Clean tmp

        $emundus_array = array();

        # Query present version id value
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('version_id')
            ->from('#__schemas')
            ->where($db->qn('extension_id') . ' = 700');
        $db -> setQuery($query);

        $result_object = $db->loadObject();
        $actual_version = get_object_vars($result_object)['version_id'] . '.sql';

        echo "Emundus SQL Update Tool \n\n";
        echo 'Actual version_id : ' . $actual_version . "\n\n";

        $sql_update_path ='/Applications/MAMP/htdocs/core/administrator/components/com_admin/sql/updates/mysql/';

        foreach (new DirectoryIterator($sql_update_path) as $file) {
            if($file->isDot()) continue;

            $filename = $file->getFilename();
            $emundus_array[] = $filename;
        }

        sort($emundus_array, SORT_NATURAL);
        $arr_len = count($emundus_array);
        echo "Array contain " . ($arr_len - 1)   . " versions \n";

        foreach ($emundus_array as $v){
            $emundus_versions[] = $v;
            if($v == $actual_version) {
                echo "Found result with  : " . $v . "\n";
            }
        }
        # Manage case --> No need to update something, we just check if $actual_version_id correspond to last array key
        if ($emundus_array[$arr_len-1] == ($actual_version)) {
            echo "\n" . "You are Up-To-Date !";
            #exit();
        }
        # Manage all others
        foreach ($emundus_array as $v){
            if ($v == $actual_version) {
                echo "Array index position to begin update is : " . array_search($v, $emundus_array) . "\n\n";
                $position_update = array_search($v, $emundus_array);
            }
        }
        echo "All theses files will be executed : \n";
        if($position_update){
            for($i = $position_update; $i<$arr_len; $i++) {
                echo $emundus_array[$i] . "\n\n";
            }
        }

        for($sqlfile = $position_update; $sqlfile<$arr_len; $sqlfile++) {
            #$query = $db->getQuery(true);
            $query = file_get_contents($sql_update_path . $emundus_array[$sqlfile]);
            $db->setQuery($query);
            try {
                $currentDate = "23/09/2021";
                echo "Starting update with : " . $emundus_array[$sqlfile] . " at " . $currentDate . "\n";
                $db->execute();
                # $currentDate = new DateTime();
                # $currentDate->format('Y-m-d H:i:s');
            } catch (Exception $e) {}
        echo "Finishing update successfuly with : " . $emundus_array[$sqlfile] . " at " . $currentDate . "\n";
        }
        # Exec time 0.12842



    }
}

JApplicationCli::getInstance('UpdateDb')->execute();