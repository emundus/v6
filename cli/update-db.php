<?php

if (php_sapi_name() != 'cli')
{
    exit(1);
}

if( !isset($_SERVER['HTTP_HOST']) )
    $_SERVER['HTTP_HOST'] = 'cms';

if( !isset($_SERVER['HTTP_USER_AGENT']) )
    $_SERVER['HTTP_USER_AGENT'] = 'cms';

const _JEXEC = 1;

const JDEBUG = 1;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
    require_once dirname(__DIR__) . '/defines.php';
}

// Load defaut defines
if (!defined('_JDEFINES'))
{
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Load Framework
require_once JPATH_BASE . '/includes/framework.php';

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

// Load components files
require_once JPATH_ADMINISTRATOR . '/components/com_hikashop/install.hikashop.php';
require_once JPATH_ADMINISTRATOR . '/components/com_dropfiles/install.php';


/**
 * Update db with sql update files
 */
class UpdateDb extends JApplicationCli
{

    public $db = null;
    public $com_schemas = array();


//TODO : Config logs


    private function getInstalledVersion($component_id)
    {
        $query = $this->db->getQuery(true);
        $query->select('manifest_cache', 'name')
            ->from('#__extensions')
            ->where($this->db->quoteName('extension_id') . ' = ' . $component_id);
        $this->db->setQuery($query);
        $manifestCache = $this->db->loadResult();

        if ($manifestCache) {
            $manifest = json_decode($manifestCache);
            $this->installedVersion = $manifest->version;
            $this->name = $manifest->name;
            if ($component_id == '700'){
                $this->name = 'com_admin';
            }
            $this->type = $manifest->type;
        }
    }

    private function getExtensionsId($table) {
        #$db    = JFactory::getDbo();
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . $table);
        $this->db->setQuery($query);
        return $this->db->loadAssocList('extension_id');
        }

    public function getInfo(){
        $key = array_values($this->com_schemas);
        foreach ($key as $k){
            echo $k. "\n";
        }
    }

    public function checkUpdate()
    {
        # Update all components
        if ($this->input->get('a', $this->input->get('all'))) {
            echo "Update all extensions\n\n";
            foreach ($this->com_schemas as $component) {
                $component_id = array_search($component, $this->com_schemas);
                $this->doUpdate($component, $component_id);
            }
//            foreach ($this->com_extensions as $component) {
//                echo "Extension";
//            }
        }
        # Update by extension id
        if ($id = $this->input->get('i', $this->input->get('id'))) {
            $component_id = isset($this->com_schemas[$id]);
            if ($component_id) {
                $this->doUpdate($this->com_schemas[$id], $id);
            } else {
                echo "-> Id not found in 'schema' table\n";
                $component_id = isset($this->com_extensions[$id]);
                if ($component_id) {
                    echo "-> Id found in 'extension' table\n";

                    $this->doUpdate($this->com_extensions[$id], $id);
                } else {
                    echo "-> Id doesn't exists !";
                }
                return;
            }
        }

        if ($this->input->get('c', $this->input->get('core'))) {
            echo "Core";
        }
    }

    public function doUpdate($component, $component_id) {
        # Get manifest info
        $this->getInstalledVersion($component_id);
        echo "Update Component id : " . $component_id . "\n";
        echo "Name : " . $this->name . "\n";
        echo "Type : " . $this->type . "\n";
        echo "Actual version (manifest) : " . $this->installedVersion . "\n\n";

        # Get update directory path
        $sql_update_path = JPATH_ADMINISTRATOR . '/components/' . $this->name . '/sql/updates/mysql/';

        # Verify if path is a valid directory
        if (is_dir($sql_update_path)) {
            echo "Component directory : " . $sql_update_path . "\n";
        } else {
            # If directory doesn't exist try another update method
            echo "Directory doesn't exist : Attempting alternate update method\n";
            $this->withoutSqlFile($component);

            return;
        }

        # Query present version id value
        $query = $this->db->getQuery(true);
        $query->select('version_id')
            ->from('#__schemas')
            ->where($this->db->qn('extension_id') . ' = ' . $component_id);
        $this->db->setQuery($query);

        $result_object = $this->db->loadObject();
        $actual_sql_version = get_object_vars($result_object)['version_id'] . '.sql';
        echo 'Actual version_id (schemas) : ' . $actual_sql_version . "\n";

        # Init versions array
        $emundus_array = array();
        $emundus_versions = array();

        # # Aggregate array with sql update files from Emundus dedicated folder
        $directory = new DirectoryIterator($sql_update_path);
        foreach ($directory as $file) {
            if ($file->isDot()) continue;
            $filename = $file->getFilename();
            if (strpos($filename, '.html')) continue;
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
            return;
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
        $this->sqlExec($position_update, $sql_update_path, $emundus_array, $arr_len, $emundus_versions, $component_id);
    }

    public function sqlExec($position_update,$sql_update_path,$emundus_array, $arr_len, $emundus_versions, $component_id)
    {
        # Execute .sql files one by one with log
        for ($sqlfile = $position_update; $sqlfile < $arr_len; $sqlfile++) {
            try {
                # Execute Sql file
                $query = file_get_contents($sql_update_path . $emundus_array[$sqlfile]);
                $this->db->setQuery($query);
                $current_date = date_format(new DateTime(), 'Y-m-d H:i:s');
                echo "\nStarting update with : " . $emundus_array[$sqlfile] . " at " . $current_date . "\n";
                $this->db->execute();

                # Update version in jos__schemas table
                $query = $this->db->getQuery(true);
                $field = array($this->db->qn('version_id') . "= '" . $emundus_versions[$sqlfile] . "'");
                $condition = array($this->db->qn('extension_id') . '= ' . $component_id);
                $query->update('#__schemas')
                    ->set($field)
                    ->where($condition);
                $this->db->setQuery($query);
                $this->db->execute();
                echo "Finishing update successfuly with : " . $emundus_array[$sqlfile] . " at " . $current_date . "\n";

                # Check version in manifest
                $this->getInstalledVersion($component_id);
            } catch (Exception $e) {
                echo "\nError during update with : $emundus_array[$sqlfile] at $current_date";
                error_log($e->getMessage() . "\n");
                exit();
            }
        }
    }

    private function withoutSqlFile($component)
    {
        echo "-> No sql file method\n";
        $current_date = date_format(new DateTime(), 'Y-m-d H:i:s');
        try {
            if ($component == 'com_hikashop') {
                com_hikashop_install();
                echo "Finishing update successfuly with : " . $component . " at " . $current_date . "\n";

            }
            if ($component == 'com_dropfiles') {
                $dropfiles = new Com_DropfilesInstallerScript();
                $dropfiles->update();
                #$dropfiles->postflight();
                echo "Finishing update successfuly with : " . $component . " at " . $current_date . "\n";
            }
            else {
                echo "Component not managed by the script\n";
                echo "---------------\n\n";
            }
        } catch (Exception $e) {
            echo "\nError during update with : $component at $current_date";
            error_log($e->getMessage() . "\n");
            exit();
        }
    }

    public function doEchoHelp()
    {
        # $version = _JoomlaCliAutoUpdateVersion;
        echo <<<EOHELP
            Joomla! CLI Update DB
            
            Operations
              -u, --update                Run Update
              -l, --list                  List Components
              -h, --help                  Help
              
            Update Filters
              -i, --id ID                 Update by ID
              -a, --all                   All Components
              -V, --version VER           Version Filter
              -c, --core                  Joomla! Core Packages
            
            
            EOHELP;
    }

    public function doExecute()
    {
        $this->db = JFactory::getDbo();
        $this->com_schemas = $this->getExtensionsId('schemas');
        $this->com_extensions = $this->getExtensionsId('extensions');
        #$this->com_array = array(700 => 'com_admin', 10041 => 'com_fabrik', 12338 => 'com_dropfiles', 11373 => 'com_hikashop');

        echo "Emundus SQL Update Tool \n\n";

        # List components available for update
        if ($this->input->get('l', $this->input->get('list'))) {
            $this->getInfo();
        }

        # Update component
        if ($this->input->get('u', $this->input->get('update'))) {
            $this->checkUpdate();
        }

        if ($this->input->get('h', $this->input->get('help'))) {
            $this->doEchoHelp();
        }

        if ($version = $this->input->get('v', $this->input->get('version'))) {
            echo "Version";
        }
    }
}

JApplicationCli::getInstance('UpdateDb')->execute();