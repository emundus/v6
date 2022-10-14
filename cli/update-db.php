<?php header('Content-type: text/plain; charset=utf-8');

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
//TODO : Config logs

    private function loadManifest($component_id)
    {
        $query = $this->db->getQuery(true);
        $query->select('manifest_cache', 'name', 'element')
            ->from('#__extensions')
            ->where($this->db->quoteName('extension_id') . ' = ' . $component_id);
        $this->db->setQuery($query);
        $manifestCache = $this->db->loadResult();

        if ($manifestCache) {
            $manifest = json_decode($manifestCache, true);
            $this->name = $manifest['name'];
            $this->type = $manifest['type'];
        }
        return $manifest;
    }

    private function setVersion($component_id, $manifest)
    {
        if ($component_id == '700') {
            $xml_path = JPATH_ADMINISTRATOR . '/manifests/files/';
            $xml_files = array($xml_path . '/' . 'joomla.xml');
        } else {
            # Get element for init path
            $element = $this->com_extensions[$component_id]['element'];
            $xml_path = JPATH_ADMINISTRATOR . '/components/' . $element . '/';
            $xml_path_bis = JPATH_ROOT . '/components/' . $element . '/';
            # List xml files and compare versions with db
            if (!is_dir($xml_path)) {
                $xml_path = $xml_path_bis;
            }
        }
        if (is_dir($xml_path)) {
            if ($component_id !='700') {
                $directory = new DirectoryIterator($xml_path);
                foreach ($directory as $file) {
                    $filename = $file->getFilename();
                    if (strpos($filename, '.xml')){
                        $xml_files[] = $xml_path . $filename;
                    }
                }
            }

            foreach ($xml_files as $xml) {
                $xmlf = simplexml_load_file($xml);
                if ($xmlf->version) {
                    if ($manifest['version'] < (string)$xmlf->version) {
                        echo $manifest['version'] . " change to " . (string)$xmlf->version . " in table extension\n";
                        $this->manifest['version'] = (string)$xmlf->version;
                        $this->manifest['creationDate'] = (string)$xmlf->creationDate;
                        $this->updateManifest($this->manifest, $component_id);
                    }
                }
            }
        }
    }

    private function updateManifest($manifest, $component_id, $sql=null){
        if ($sql){
            if ($manifest['version'] < $sql)
                $manifest['version'] = $sql;
        }
        $manifestCache = json_encode($manifest);
        # Exscape special chars
        $manifestCache = str_replace(array("\n", "\r", "\t", "'", "\\"), array("\\n", "\\r", "\\t", "''", "\\\\"), $manifestCache);
        $query = $this->db->getQuery(true);
        $field = array($this->db->qn('manifest_cache') . "= '" . $manifestCache . "'");
        $condition = array($this->db->qn('extension_id') . '= ' . $component_id);
        try {
            $query->update('#__extensions')
                ->set($field)
                ->where($condition);
            $this->db->setQuery($query);
            $this->db->execute();
        } catch (Exception $e) {
            echo "! Query for setVersion() fail\n";
        }
    }

    private function getExtensionsId($table) {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . $table);
        $this->db->setQuery($query);
        return $this->db->loadAssocList('extension_id');
        }

    private function getComponentsId($table) {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->where($this->db->qn('type') . " = 'component'")
            ->from('#__' . $table);
        $this->db->setQuery($query);
        return $this->db->loadAssocList('extension_id');
    }

    private function updateTable($table, $field, $condition) {
        $query = $this->db->getQuery(true);
        $query->update($table)
            ->set($field)
            ->where($condition);
        $this->db->setQuery($query);
        $result = $this->db->loadResult();
        $this->db->execute();
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
            echo "Update all components\n\n";
            # All extensions from schemas table
            /*foreach ($this->com_schemas as $component) {
                $component_id = array_search($component, $this->com_schemas);
                $this->doUpdate($component, $component_id);
            }*/

            # All component type from extensions table
            unset($this->com_extensions[700]);
            unset($this->com_components[3]);
            foreach ($this->com_components as $component) {
                $component_id = array_search($component, $this->com_components);
                $this->doUpdate($component, $component_id);
            }
        }
        # Update by extension id
        if ($id = $this->input->get('i', $this->input->get('id'))) {
            $component_isset = isset($this->com_components[$id]) || isset($this->com_schemas[$id]);
            if ($component_isset) {
                    $this->doUpdate($this->com_components[$id], $id);
                } else {
                    echo "-> Id doesn't exists !";
                    return;
                }
            }

        if ($this->input->get('c', $this->input->get('core'))) {
            $component_core = array($this->com_extensions[700], $this->com_components[3]);
            foreach ($component_core as $component) {
                $component_id = $component['extension_id'];
                $this->doUpdate($component, $component_id);
            }
        }
    }

    public function doUpdate($component, $component_id) {
        # Get manifest info
        $this->manifest = $this->loadManifest($component_id);
        if(!$this->manifest) {
            echo "Component manifest not find\n";
            echo "---------------\n\n";
            return;
        }
        $this->setVersion($component_id, $this->manifest);
        echo "Update Component id : " . $component_id . "\n";
        echo "Name : " . $this->manifest['name'] . "\n";
        echo "Type : " . $this->manifest['type'] . "\n";
        echo "Actual version (manifest): " . $this->manifest['version'] . "\n\n";

        # Get update directory path
        if ($component_id == '700') {
            $element = $this->com_extensions[3]['element'];
        } else {
            $element = $this->com_extensions[$component_id]['element'];
        }

        if ($component_id == '3') {
            return;
        }
        $sql_update_path = JPATH_ADMINISTRATOR . '/components/' . $element . '/sql/updates/mysql/';
        # Some components have different directory (ex:SecurityCheck)
        $sql_update_path_bis = JPATH_ADMINISTRATOR . '/components/' . $element . '/sql/updates/';
        # Verify if path is a valid directory
        if (is_dir($sql_update_path)) {
            echo "Component directory : " . $sql_update_path . "\n";
        } else {
            if (is_dir($sql_update_path_bis)) {
                echo "Component directory : " . $sql_update_path_bis . "\n";
                $sql_update_path = $sql_update_path_bis;
            } else {
                # If directory doesn't exist try another update method
                echo "Directory doesn't exist : Attempting alternate update method\n";
                $this->withoutSqlFile($component);
                return;
            }
        }

        # Query present version id value
        $id = $component_id;
        $query = $this->db->getQuery(true);
        $query->select('version_id')
            ->from('#__schemas')
            ->where($this->db->qn('extension_id') . ' = ' . $id);
        $this->db->setQuery($query);

        $result_object = $this->db->loadObject();
        if (get_object_vars($result_object)['version_id'] != null) {
            $actual_sql_version = get_object_vars($result_object)['version_id'] . '.sql';
            echo '-> Actual sql version (schemas) : ' . get_object_vars($result_object)['version_id'] . "\n";
        } else {
            $actual_sql_version = $this->manifest['version'] . '.sql';
        }

        # Init versions array
        $emundus_array = array();
        $emundus_versions = array();

        # # Aggregate array with sql update files from Emundus dedicated folder
        $directory = new DirectoryIterator($sql_update_path);
        foreach ($directory as $file) {
            $filename = $file->getFilename();
            if (strpos($filename, '.sql')){
                $emundus_array[] = $filename;
            }
        }

        usort($emundus_array, 'version_compare');
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
            $is_uptodate=true;
            echo "\n" . "You are Up-To-Date !\n";
            $this->finalizeUpdate($actual_sql_version,$component_id, $is_uptodate);
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
                $query = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $query);
                $this->db->setQuery((string) $query);
                $current_date = date_format(new DateTime(), 'Y-m-d H:i:s');
                echo "\nStarting update with : " . $emundus_array[$sqlfile] . " at " . $current_date . "\n";
                $this->db->execute();
                echo "Finishing update successfuly with : " . $emundus_array[$sqlfile] . " at " . $current_date . "\n";
                $this->finalizeUpdate($emundus_versions[$sqlfile],$component_id);

            } catch (Exception $e) {
                echo "\nError during update with : $emundus_array[$sqlfile] at $current_date . \n";
                echo $e->getMessage() . "\n";
                exit();
            }
        }
    }

    private function withoutSqlFile($component)
    {
        echo "-> No sql file method\n";
        $current_date = date_format(new DateTime(), 'Y-m-d H:i:s');
        try {
            # TODO : array with element & install function
            if ($component['element'] == 'com_hikashop') {
                com_hikashop_install();
                echo "Finishing update successfuly with : " . $component . " at " . $current_date . "\n";
                return;

            }
            if ($component == 'com_dropfiles') {
                $dropfiles = new Com_DropfilesInstallerScript();
                $dropfiles->update();
                $dropfiles->postflight();
                echo "Finishing update successfuly with : " . $component . " at " . $current_date . "\n";
                return;
            }
            else {
                echo "Component not managed by the script\n";
                # TODO : overwrite the contents of the file when starting the script
                $text = $component['element'] . "," . $component['extension_id'] . ";\n";
                $filename = "not_managed_" . $component['type'] . ".txt";
                $fh = fopen($filename, "a");
                fwrite($fh, $text);
                fclose($fh);
                echo "---------------\n\n";
            }
        } catch (Exception $e) {
            echo "\nError during update with : $component at $current_date";
            error_log($e->getMessage() . "\n");
            exit();
        }
    }

    private function finalizeUpdate($actual_sql_version, $component_id, $is_uptodate=null){

        $actual_sql_version = preg_split("/.sql/",$actual_sql_version);
        $actual_sql_version = $actual_sql_version[0];
        $field = array($this->db->qn('version_id') . "= '" . $actual_sql_version . "'");
        $condition = array($this->db->qn('extension_id') . '= ' . $component_id);
        $this->updateTable('#__schemas', $field, $condition);
        $version_prefix = preg_split("/-/", $actual_sql_version);
        $version_prefix = $version_prefix[0];
        $this->updateManifest($this->manifest, $component_id, $sql=$version_prefix);
        echo "Update table schemas & extensions OK\n";
        echo "---------------\n\n";
    }

    public function doEchoHelp()
    {
        echo <<<EOHELP
            Joomla! CLI Update DB
            
            Operations
              -u, --update                Run Update
              -l, --list                  List Components
              -h, --help                  Help
              
            Update Filters
              -i, --id ID                 Update component/extension by ID
              -a, --all                   All Components
              -c, --core                  Composants Joomla
            
            
            EOHELP;
    }

    public function doExecute()
    {
        $executionStartTime = microtime(true);

        $this->db = JFactory::getDbo();
        # List extensions from schema table
        $this->com_schemas = $this->getExtensionsId('schemas');
        # List extensions from extensions table
        $this->com_extensions = $this->getExtensionsId('extensions');
        # List components type from extensions table
        $this->com_components = $this->getComponentsId('extensions');

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

        if ($component = $this->input->get('v', $this->input->get('version'))) {
            $manifest = $this->loadManifest($component);
            $this->setVersion($component, $manifest);
        }

        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        echo "\n" . "This script took $seconds to execute.";
    }
}

JApplicationCli::getInstance('UpdateDb')->execute();