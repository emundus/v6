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

require_once JPATH_ADMINISTRATOR . '/components/com_hikashop/install.hikashop.php';
require_once JPATH_ADMINISTRATOR . '/components/com_dropfiles/install.php';


/**
 * Update db from sql update files
 */
class UpdateDb extends JApplicationCli
{

//TODO : Config logs

    private function noSqlFile($component)
    {
        echo "No sql file method\n";
        $current_date = date_format(new DateTime(), 'Y-m-d H:i:s');
        try {
            if ($component == 'com_hikashop') {
                com_hikashop_install();
            }
            if ($component == 'com_dropfiles') {
                $dropfiles = new Com_DropfilesInstallerScript();
                $dropfiles->update();
                #$dropfiles->postflight();
            }
            echo "Finishing update successfuly with : " . $component . " at " . $current_date . "\n";
        } catch (Exception $e) {
                echo "\nError during update with : $component at $current_date";
                error_log($e->getMessage() . "\n");
                exit();
            }
    }

    private function getInstalledVersion($db, $component_id)
    {
        $query = $db->getQuery(true);
        $query->select('manifest_cache')
            ->from('#__extensions')
            ->where($db->quoteName('extension_id') . ' = '. $component_id);
            #->where($db->quoteName('type') . ' = "component"');
        $db->setQuery($query);
        $manifestCache = $db->loadResult();

        if ($manifestCache)
        {
            $manifest = json_decode($manifestCache);
            $this->installedVersion = $manifest->version;
        }
    }

    public function doExecute()
    {
        # Array with id (key) name (value) component for update
        $com_array = array(700 => 'com_admin', 10041 => 'com_fabrik', 12338 => 'com_dropfiles', 11373 => 'com_hikashop' );
        $db = JFactory::getDbo();

        echo "Emundus SQL Update Tool \n\n";

        # Loop over all components
        foreach ($com_array as $component) {
            $component_id = array_search($component, $com_array);
            echo "Component id : " . $component_id . "\n";

            $sql_update_path = JPATH_ADMINISTRATOR . '/components/' . $component . '/sql/updates/mysql/';
            # Verify if path is a valid directory
            if (is_dir($sql_update_path)){
                echo "Component directory : " . $sql_update_path . "\n";
            } else {
                # If not try another update method
                echo "Directory doesn't exist : Attempting alternate update method\n";
                $this->noSqlFile($component);
                $this->getInstalledVersion($db, $component_id);
                continue;
            }


            # Query present version id value
            $query = $db->getQuery(true);
            $query->select('version_id')
                ->from('#__schemas')
                ->where($db->qn('extension_id') . ' = ' . $component_id);
            $db->setQuery($query);

            $result_object = $db->loadObject();
            $actual_sql_version = get_object_vars($result_object)['version_id'] . '.sql';
            echo 'Actual version_id : ' . $actual_sql_version . "\n";

            # Init versions array
            $emundus_array = array();
            $emundus_versions = array();

            # # Aggregate array with sql update files from Emundus dedicated folder
            $directory = new DirectoryIterator($sql_update_path);
            foreach ($directory as $file) {
                if ($file->isDot()) continue;
                $filename = $file->getFilename();
                $emundus_array[] = $filename;
            }

            sort($emundus_array, SORT_NATURAL);
            $arr_len = count($emundus_array);

            # display amount of array entries
            echo "Array contain " . ($arr_len - 1) . " versions \n";

            # Debug with echo by string
            foreach ($emundus_array as $v) {
                $version_prefix = preg_split("/.sql/", $v);
                $emundus_versions[] = $version_prefix[0];
                if ($v == $actual_sql_version) {
                    echo "Found result with  : " . $v . "\n";
                }
            }
            # Manage case --> No need to update something, we just check if $actual_version_id correspond to last array key
            if ($emundus_array[$arr_len - 1] == ($actual_sql_version)) {
                echo "\n" . "You are Up-To-Date !\n\n";
                echo "---------------\n\n";
                continue;
            }
            # Manage all others
            foreach ($emundus_array as $v) {
                if ($v == $actual_sql_version) {
                    $position_update = array_search($v, $emundus_array) + 1;
                    echo "Array index position to begin update is : " . $position_update . "\n\n";

                }
            }
            echo "All theses files will be executed : \n";
            if ($position_update) {
                for ($i = $position_update; $i < $arr_len; $i++) {
                    echo $emundus_array[$i] . "\n";
                }
            }

            # Execute .sql files one by one with log
            for ($sqlfile = $position_update; $sqlfile < $arr_len; $sqlfile++) {

                try {
                    # Execute Sql file
                    $query = file_get_contents($sql_update_path . $emundus_array[$sqlfile]);
                    $db->setQuery($query);
                    $current_date = date_format(new DateTime(), 'Y-m-d H:i:s');
                    echo "\nStarting update with : " . $emundus_array[$sqlfile] . " at " . $current_date . "\n";
                    $db->execute();

                    # Update version in jos__schemas table
                    $query = $db->getQuery(true);
                    $field = array($db->qn('version_id') . "= '" . $emundus_versions[$sqlfile] . "'");
                    $condition = array($db->qn('extension_id') . '= 700');
                    $query->update('#__schemas')
                        ->set($field)
                        ->where($condition);
                    $db->setQuery($query);
                    $db->execute();
                    echo "Finishing update successfuly with : " . $emundus_array[$sqlfile] . " at " . $current_date . "\n";

                    # Check version in manifest
                    $this->getInstalledVersion($db, $component_id);
                } catch (Exception $e) {
                    echo "\nError during update with : $emundus_array[$sqlfile] at $current_date";
                    error_log($e->getMessage() . "\n");
                    exit();
                }
            }
        }
    }
}

JApplicationCli::getInstance('UpdateDb')->execute();