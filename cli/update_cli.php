<?php


use Joomla\CMS\Log\Log;
use FOF40\Database\Installer;

// Initialize Joomla framework
const _JEXEC = 1;

// Load system defines
if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}
const JPATH_COMPONENT_ADMINISTRATOR = JPATH_ADMINISTRATOR . '/components/';

require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_CONFIGURATION . '/configuration.php';
require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_joomlaupdate/models/default.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_installer/models/update.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_installer/models/install.php';

jimport('joomla.application.cli');


class UpdateCli extends JApplicationCli
{
    /**
     * Entry point for the script
     * @return void
     * @throws Exception
     */
    public function doExecute()
    {
        # Initialisation
        JFactory::getApplication('site');
        $executionStartTime = microtime(true);
        $this->db = JFactory::getDbo();

        $short_options = "vhlcu::a";
        $long_options = ["verbose", "help", "list", "core", "update::", "all", "dry-run"];
        $options = getopt($short_options, $long_options);
        $args = (array)$GLOBALS['argv'];

        # Init log files
        $log_files = array(JPATH_BASE . "/logs/update_cli_errors.log", JPATH_BASE . "/logs/update_cli_queries.log");
        foreach ($log_files as $file) {
            if (file_exists($file)) {
                if (!unlink($file)) {
                    $this->out($this->colorLog("$file log file can't be deleted\n", 'e'));
                }
            }
        }
        Log::addLogger(
            array(
                'text_file' => 'update_cli_errors.log',
            ),
            JLog::ALL, array('error'));
        Log::addLogger(
            array(
                'text_file' => 'update_cli_queries.log',
            ),
            JLog::ALL, array('update'));
        $this->firstrun = false;
        # Array of components available for update
        $availableComponents = array('com_emundus', 'com_fabrik', 'com_hikashop', 'com_hikamarket', 'com_falang',
            'com_eventbooking', 'com_dpcalendar', 'com_dropfiles', 'com_extplorer',
            'com_miniorange_saml', 'com_loginguard', 'com_jce', 'com_admintools',
            'com_jumi', 'com_gantry5', 'com_externallogin', 'com_jchoptimize', 'com_securitycheckpro');

        # Array of components with extensions datas
        $this->components = $this->getComponentsElement('extensions', $availableComponents);
        # Init variables
        $this->count_stmt = 0;
        $this->count_fails = 0;
        $this->count_exec = 0;
        $this->verbose = false;

        $this->out("eMundus Database Updater\n");
        # Enable debug mode for counting statements
        $this->db->setDebug(true);
        if (isset($options["v"]) || isset($options["verbose"])) {
            $this->verbose = true;
        }
        if (isset($options["h"]) || isset($options["help"])) {
            $this->doEchoHelp();
        }
        if (isset($options["l"]) || isset($options["list"])) {
            $this->getUpdateList();
        }
        # Update only Joomla core component
        if (isset($options["c"]) || isset($options["core"])) {
            $this->count_exec++;
            $this->updateJoomla();
        }
        # Update all
        if (isset($options["a"]) || isset($options["all"])) {
            $this->count_exec++;
            $this->updateJoomla();
            $this->updateComponents(null, $options);
        }
        # Update 1 to n components (except Joomla)
        if (isset($options["u"]) || isset($options["update"])) {
            # Execute update for all of components name pass to args
            if (sizeof($args) == 2) {
                $this->updateComponents(null, $options);
            } elseif (sizeof($args) > 2) {
                $index = 2;
                while ($index <= sizeof($args) - 1) {
                    $element[] = $args[$index];
                    $index++;
                }
                $this->updateComponents($element, $options);
            }
        }

        if (isset($options["u"]) || isset($options["update"]) || isset($options["a"]) || isset($options["all"]) || isset($options["c"]) || isset($options["core"])) {
            $this->out("###########################################################");

            if ($this->count_fails == 0) {
                $this->out($this->colorLog("$this->count_exec components are up to date", 's'));
            } else {
                $this->out($this->colorLog("$this->count_fails components failed to be updated !", 'e'));
            }

            if ($this->verbose) {
                $this->out($this->colorLog("  -> $this->count_stmt sql statements executed", 'i'));

                # Execution time
                $executionEndTime = microtime(true);
                $seconds = $executionEndTime - $executionStartTime;
                $seconds = substr((string)$seconds, 0, 4);
                $this->out($this->colorLog("\nThis script took $seconds seconds to execute.", 'i'));
            }

            if ($this->count_fails == 0) {
                $this->out($this->colorLog("\nRESULTS: SUCCESS !", 's'));
            } else {
                $this->out($this->colorLog("\nRESULTS: FAILED !", 'e'));
                $this->out("###########################################################");
                exit(1);
            }
            $this->out("###########################################################");
        }
    }

    # Main functions

    /**
     * Update Joomla core component only
     * @return void
     */
    private function updateJoomla()
    {
        # Load Joomla id
        $query = $this->db->getQuery(true);
        $query->select('extension_id')
            ->where($this->db->quoteName('name') . " = 'files_joomla'")
            ->from("#__extensions");
        $this->db->setQuery($query);
        $id = $this->db->loadResult("id");

        # Load xml, version to update, model and logs
        $xml = simplexml_load_file(JPATH_MANIFESTS . '/files/joomla.xml');
        $xml_version = $xml->version;
        $updater = JModelLegacy::getInstance('JoomlaupdateModelDefault');

        $version = $this->getSchemaVersion($id);
        $dir = JPATH_COMPONENT_ADMINISTRATOR . "com_admin/sql/updates/mysql";
        $files = scandir($dir);
        sort($files, SORT_NATURAL);

        $this->out("*--------------------*\n");
        $this->out("UPDATE Joomla " . $version . " to " . preg_split("/.sql/", end($files))[0]);
        
        # Check if update is needed
        if ($this->purgeAndFetchUpdates("joomla") == 1) {
            $this->out($this->colorLog("\nJoomla purge and fetch updates failed !",'e'));
            $this->count_fails++;
            return;
        }

        # Execute update
        $this->global_logs = $this->db->getLog();
        $res = $updater->finaliseUpgrade();

        # Get logs only for Joomla and pop last element if update fails
        $component_logs = $this->getElementLogs();

        if ($err_msg = $this->db->getErrorMsg()) {
            $failed_statement = array_pop($component_logs);
            $failed_statement_without_prefix = str_replace("jos_", "#__", $failed_statement);
        }
        $this->count_stmt += count($component_logs);

        # Add to logger
        foreach ($component_logs as $log) {
            Log::add("[EXEC] Joomla: \n" . str_replace(PHP_EOL, '', $log), Log::INFO, 'update');
            Log::add(str_replace(PHP_EOL, '', $log), Log::INFO, 'update');
        }

        # Log informations according to operation result
        if ($res == 1) {
            $this->out($this->colorLog("Joomla database update successfully completed", 's'));
            // $this->out("Joomla database update successfully completed\n");
            if ($this->verbose) {
                $this->out("    -> " . count($component_logs) . " sql statements executed\n");
            }

        } else {
            foreach ($files as $k => $file) {
                if (is_file($dir . '/' . $file)) {
                    $source = file_get_contents($dir . '/' . $file);
                    if (strpos($source, $failed_statement) === false ^ strpos($source, $failed_statement_without_prefix) === false) {
                        # exit_file -> First element for setting schema version. Second element is the file where there is an error
                        $exit_file[] = $files[$k - 1];
                        $exit_file[] = $files[$k];
                    }
                }
            }
            if (!empty($exit_file)) {
                foreach ($exit_file as $k => $file) {
                    $file = preg_split("/.sql/", $file);
                    $exit_file[$k] = $file[0];
                }
                $this->updateSchema($id, array($exit_file[0]), "end");
            }
            # Log
            $this->out($this->colorLog("\nJoomla database update failed...", 'e'));
            if ($this->verbose) {
                $this->out("    -> " . count($component_logs) . " sql statements executed");
                $this->out("-> " . $err_msg);
            }
            $this->out("-> Take a look to the error log file for more information");

            Log::add("[FAIL] Joomla on " . $exit_file[1], Log::ERROR, 'error');
            Log::add($err_msg, Log::INFO, 'error');
            Log::add(str_replace(PHP_EOL, '', $failed_statement), Log::INFO, 'error');

            $this->count_fails++;
        }
    }

    /**
     * Update one to n components : Retrieve xml and database informations, execute sql statements from schemapath
     * and execute contextual update from scriptfile
     * @param $elements
     * @return false|void
     * @throws Exception
     */
    private function updateComponents($elements = null, $options = null)
    {
        $installer = JInstaller::getInstance();
        $success = true;
        $failure_msg = '';

        # Case where element isn't defined in script parameters -> update all
        $elements = empty($elements) ? array_keys($this->components) : $elements;

        if (empty($elements)) {
            $this->out("Nothing component available for update");
            return false;
            $elements = empty($elements) ? array_keys($this->components) : $elements;

            if (empty($elements)) {
                $this->out("Nothing component available for update");
                return false;
            }

        }

        $this->count_exec += count($elements);
        # Process update for each component listed
        foreach ($elements as $element) {
            # Get component row & load manifest cache
            $elementArr = $this->components[$element];
            $manifest_cache = json_decode($elementArr['manifest_cache'], true);

            # Check xml path
            if ($manifest_cache['filename']) {
                $xml_file = $manifest_cache['filename'] . '.xml';
            } else {
                $xml_file = preg_split("/[_]+/", $elementArr["element"], 2)[1] . '.xml';
            }
            $path = is_dir(JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/') ? JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/' : JPATH_ROOT . '/components/' . $elementArr['element'] . '/';

            # Load xml or fail
            $xml_path = $path . $xml_file;
            if ($element == 'com_extplorer' || $element == 'com_dropfiles') {
                if (empty($manifest_cache['version'])) {
                    $manifest_cache['version'] = $this->refreshManifestCache($elementArr['extension_id'], $elementArr['element']);
                }
                if (!file_exists($xml_path)) {
                    if ($element == 'com_extplorer') {
                        $short_element = str_replace('com_', '', $element);
                        $file = JPATH_ADMINISTRATOR . '/components/' . $element . '/' . $short_element . '.j30.xml';
                    } elseif ($element == 'com_dropfiles') {
                        $file = JPATH_ADMINISTRATOR . '/components/' . $element . '/' . $element . '.xml';
                        $short_element = str_replace('com_', '', $element);
                    }
                    if (file_exists($file)) {
                        $rename_file = JPATH_ADMINISTRATOR . '/components/' . $element . '/' . $short_element . '.xml';
                        rename($file, $rename_file);
                        $xml_path = $rename_file;
                    } else {
                        $xml_path = JPATH_ADMINISTRATOR . '/components/' . $element . '/' . $short_element . '.xml';
                    }
                }
            }
            if (file_exists($xml_path)) {
                $this->manifest_xml = simplexml_load_file($xml_path);
                $this->out("*--------------------*\n");

                $regex = '/^6\.[0-9]*/m';
                preg_match_all($regex, $manifest_cache['version'], $matches, PREG_SET_ORDER, 0);

                # Check if this is the first run for emundus component
                if ($elementArr['element'] == "com_emundus" and (!empty($matches) || $manifest_cache['version'] < "1.33.0")) {
                    $this->firstrun = true;
                    $this->out("** Script first run **");

                    if (empty($options) || !isset($options['dry-run'])) {
                        $this->out('Store translations tags into database for first run');
                        require_once(JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');
                        EmundusHelperUpdate::languageFileToBase();
                        $this->out();
                    }

                    # Set schema version and align manifest cache version
                    $this->schema_version = '1.33.0';
                    $manifest_cache['version'] = '1.33.0';
                    $this->updateSchema($elementArr['extension_id'], null, null, $this->schema_version);
                }

                # Update loop
                if ($this->firstrun or version_compare($manifest_cache['version'], $this->manifest_xml->version, '<=')) {
                    $this->out("UPDATE " . $manifest_cache['name'] . ' (' . $manifest_cache['version'] . ' to ' . $this->manifest_xml->version . ')');

                    # Require scriptfile
                    if ($this->manifest_xml->scriptfile) {
                        $scriptfile = JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/' . $this->manifest_xml->scriptfile;
                        try {
                            if (file_exists($scriptfile) && is_readable($scriptfile)) {
                                require_once $scriptfile;
                            } else {
                                throw new Exception($elementArr['element'] . " scriptfile doesn't exists or is not readable.");
                            }
                        } catch (Exception $e) {
                            $this->out("-> " . $e->getMessage());
                            Log::add($e->getMessage(), Log::WARNING, 'error');
                            continue;
                        }
                    } else {
                        unset($scriptfile);
                    }

                    # Step 1 : Execute SQL files for update
                    if ($this->verbose) {
                        $this->out("SQL Updates...");
                    }
                    $sql_update = $this->parseSchemaUpdates($installer, $elementArr['extension_id'], $elementArr['element'], $manifest_cache['version']);
                    if ($sql_update === false) {
                        $this->out("-> Stop " . $elementArr['element'] . " update");
                        $this->count_fails++;
                        continue;
                    } elseif (!$sql_update[0] == 0) {
                        $this->out("    -> " . $sql_update[0] . " sql statements executed");
                    }

                    if ($this->firstrun) {
                        $this->firstrun = false;
                    }

                    # Step 2 : Check custom updates
                    if ($this->verbose) {
                        $this->out("Custom updates... ");
                    }
                    # Setup adapter
                    $installer->setPath('source', $path);

                    if (!$adapter = $installer->setupInstall('update', true)) {
                        Log::add($elementArr['element'] . "Couldn't detect manifest file", Log::WARNING, 'error');

                        return false;
                    }

                    $scriptClass = $elementArr['element'] . "InstallerScript";
                    if (class_exists($scriptClass)) {
                        # Create a new instance
                        $script = new $scriptClass();

                        try {
                            $this->global_logs = $this->db->getLog();
                            switch ($elementArr["element"]) {
                                case 'com_securitycheckpro':
                                    ob_start();
                                    try {
                                        $installer->setPath('source', JPATH_ROOT);

                                        if (method_exists($scriptClass, 'preflight')) {
                                            $script->preflight('update', $adapter);
                                        }
                                        if (method_exists($scriptClass, 'update')) {
                                            $script->update($adapter);
                                        }
                                        if (method_exists($scriptClass, 'postflight')) {
                                            $script->postflight('update', $adapter);
                                        }
                                    } catch (\RuntimeException $e) {
                                        // Install failed, roll back changes
                                        $this->out($e);
                                        $installer->abort($e->getMessage());
                                        return false;
                                    }
                                    ob_end_clean();
                                    break;
                                case 'com_dpcalendar' :
                                    # Restore previous xml version before & after update because dpcalendar is based on xml to set up the version
                                    $new_version = (string)$this->manifest_xml->version[0];
                                    $this->restoreVersion($xml_path, $manifest_cache['version']);
                                    if ($script->preflight('update', $adapter) === false) {
                                        $success = false;
                                        break;
                                    }
                                    $script->update($adapter);
                                    $this->restoreVersion($xml_path, $new_version);
                                    $script->postflight('update', $adapter);
                                    break;
                                case 'com_emundus':
                                    if (method_exists($scriptClass, 'preflight')) {
                                        $script->preflight('update', $adapter);
                                    }
                                    if (method_exists($scriptClass, 'update')) {
                                        $updates = $script->update($adapter);

                                        foreach ($updates as $update) {
                                            if ($update['status'] === false) {
                                                $success = false;
                                                $failure_msg .= $update['message'] . "\n";
                                            }
                                        }

                                        if (in_array(false, $updates, true)) {
                                            $success = false;
                                            $failure_msg = array_search(false, $updates, true);
                                        }
                                    }
                                    if (method_exists($scriptClass, 'postflight')) {
                                        $script->postflight('update', $adapter);
                                    }

                                    break;
                                default :
                                    ob_start();
                                    if (method_exists($scriptClass, 'preflight')) {
                                        $script->preflight('update', $adapter);
                                    }
                                    if (method_exists($scriptClass, 'update')) {
                                        $script->update($adapter);
                                    }
                                    if (method_exists($scriptClass, 'postflight')) {
                                        $script->postflight('update', $adapter);
                                    }
                                    ob_end_clean();
                                    break;
                            }

                        } catch (\Throwable $e) {
                            if ($this->verbose) {
                                $this->out("-> " . $e->getMessage());
                                $this->out($this->db->getQuery());
                            }
                            $component_logs = $this->getElementLogs();
                            $this->count_stmt += count($component_logs);
                            foreach ($component_logs as $log) {
                                Log::add("[EXEC] " . $element . " : ", Log::INFO, 'update');
                                Log::add(str_replace(PHP_EOL, '', $log), Log::INFO, 'update');
                            }

                            Log::add("[FAIL] " . $element . " : ", Log::ERROR, 'error');
                            Log::add($e->getMessage(), Log::ERROR, 'error');
                            Log::add(str_replace(PHP_EOL, '', $this->db->getQuery()), Log::INFO, 'error');

                            $success = false;
                        }
                    } else {
                        $this->out("-> Scriptfile doesn't exists");
                    }
                } else {
                    $this->out($this->colorLog($elementArr['element'] . " component already up-to-date\n", 's'));
                    $this->updateSchema($elementArr['extension_id'], null, null, $this->manifest_xml->version);
                    continue;
                }
            } else {
                $this->out("-> Manifest path doesn't exists");
                $success = false;
            }

            $this->schema_version = $this->getSchemaVersion($elementArr['extension_id']);

            # Check success of custom updates, if true overwrite new version in xml
            if ($success) {

                # Log updates
                $component_logs = $this->getElementLogs();
                $this->count_stmt += count($component_logs);
                foreach ($component_logs as $log) {
                    Log::add("[EXEC] " . $element . " : ", Log::INFO, 'update');
                    Log::add(str_replace(PHP_EOL, '', $log), Log::INFO, 'update');
                }

                if ($this->verbose) {
                    $this->out("-> OK");
                    $this->out("    -> " . count($component_logs) . " sql statements executed");
                }

                if (empty($options) || !isset($options['dry-run'])) {
                    $manifest_cache['version'] = $this->refreshManifestCache($elementArr['extension_id'], $elementArr['element']);
                }

                if ($this->verbose) {
                    $this->out("\nVersions...");
                    $this->out("-> Schema : " . $this->schema_version);
                    $this->out("-> Manifest cache : " . $manifest_cache['version']);
                    $this->out("-> Manifest : " . $this->manifest_xml->version);
                    $this->out($this->colorLog("\nFinishing update successfuly with : " . $elementArr['name'] . "\n", 's'));
                }
            } else {
                if (empty($failure_msg)) {
                    $this->out($this->colorLog("  -> $element component update failed\n", 'e'));
                } else {
                    $this->out($this->colorLog("  -> $element component update failed with error : $failure_msg\n", 'e'));
                }
                $this->count_fails++;
            }
        }
    }

    # Utils functions

    /**
     * Command Line helper function
     * @return void
     */
    private function doEchoHelp()
    {
        echo <<<EOHELP
            Joomla! CLI Update DB
            
            Operations
              -v            Verbose, logs all sql statements executed
              -c            Update Joomla core files
              -a            Update all
              -u            Update all components (except Joomla files)
              -u NAME       Update extension(s) from input id. (Exemple: -u com_emundus com_fabrik)
              -l            List all updates available whith informations
              -h            Helper
              
              
            Extensions list with element name (30/08/2022)
                Admintools          com_admintools
                Fabrik              com_fabrik
                Jumi                com_jumi
                Falang              com_falang
                Emundus             com_emundus
                Hikashop            com_hikashop
                Security Check Pro  com_securitycheckpro
                Dpcalendar          com_dpcalendar
                Gantry              com_gantry5
                JCH Optimize        com_jchoptimize              
                Dropfiles           com_dropfiles
                Extplorer           com_extplorer
                Hikamarket          com_hikamarket
                Eventbooking        com_eventbooking
                Externallogin       com_externallogin
                JCE                 com_jce
                Loginguard          com_loginguard
                Miniorange          com_miniorange_saml
                
            EOHELP;
    }

    /**
     * Retrieve database rows from ids list
     * @param $table
     * @param $ids
     * @return array|mixed
     */
    private function getElementFromId($table, $ids)
    {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . $table)
            ->where($this->db->quoteName('extension_id') . " IN (" . implode(',', $ids) . ')');
        $this->db->setQuery($query);
        return $this->db->loadAssocList('',);
    }

    /**
     * Retrieve database rows from elements list
     * @param $table
     * @param $comp
     * @return array|mixed
     */
    private function getComponentsElement($table, $comp)
    {
        $query = $this->db->getQuery(true);
        $query->select('extension_id, name, package_id, type, element, manifest_cache')
            ->where($this->db->quoteName('element') . " IN (" . implode(',', $this->db->quote($comp)) . ') AND state != -1 AND extension_id != 10000 AND client_id = 1')
            ->from('#__' . $table);
        $this->db->setQuery($query);
        return $this->db->loadAssocList('element');
    }

    /**
     * Extract specific logs from whole logs array
     * @return array
     */
    private function getElementLogs()
    {
        $logs = $this->db->getLog();
        if ($this->global_logs) {
            foreach ($logs as $key => $value) {
                if (strpos($value, "SELECT") !== false ^ strpos($value, "SHOW") !== false) {
                    unset($logs[$key]);
                } elseif ($key < count($this->global_logs)) {
                    if ($logs[$key] == $this->global_logs[$key]) {
                        unset($logs[$key]);
                    }
                }
            }
        }
        $this->global_logs = $this->db->getLog();
        return $logs;
    }

    /**
     * Retrieve extension version from schema table
     * @param $eid
     * @return mixed|null
     */
    private function getSchemaVersion($eid)
    {
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('version_id')
            ->from('#__schemas')
            ->where('extension_id = ' . $eid);
        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     * Get list of available updates
     * @return void
     */
    private function getUpdateList()
    {
        $mask = "|%5s |%20s | %10s | %10s\n";
        printf($mask, 'Id', 'Element', 'Type', 'Version');
        # Get component row & load manifest cache
        foreach ($this->components as $element) {
            $manifest_cache = json_decode($element['manifest_cache'], true);

            # Check xml path
            if ($manifest_cache['filename']) {
                $xml_file = $manifest_cache['filename'] . '.xml';
            } else {
                $xml_file = preg_split("/[_]+/", $element["element"], 2)[1] . '.xml';
            }
            $path = is_dir(JPATH_ADMINISTRATOR . '/components/' . $element['element'] . '/') ? JPATH_ADMINISTRATOR . '/components/' . $element['element'] . '/' : JPATH_ROOT . '/components/' . $element['element'] . '/';


            # Load xml or fail
            if (file_exists($xml_path = $path . $xml_file)) {
                $this->manifest_xml = simplexml_load_file($xml_path);

                if (version_compare($manifest_cache['version'], $this->manifest_xml->version, '<')) {

                    $key[0] = $element['extension_id'];
                    $key[1] = $element['element'];
                    $key[2] = $element['type'];
                    $key[3] = $this->manifest_xml->version;
                    printf($mask, $key[0], $key[1], $key[2], $key[3]);
                }
            }
        }
    }

    /**
     * Purge updates tables and fetch new updates
     * @param $id
     * @return void
     */
    private function purgeAndFetchUpdates($comp = null)
    {
        // Get the update cache time
        $component = JComponentHelper::getComponent('com_installer');
        $updater = JUpdater::getInstance();
        $minimumStability = JUpdater::STABILITY_STABLE;
        if ($comp == "joomla") {
            $model = JModelLegacy::getInstance('JoomlaupdateModelDefault');
        } else {
            $model = JModelLegacy::getInstance('InstallerModelUpdate');
        }

        $cache_timeout = 3600 * JComponentHelper::getParams('com_installer')->get('cachetimeout', 6, 'int');

        // Purge all updates
        $this->out('-> Purge updates...');
        $model->purge();

        // Find all updates
        $this->out('-> Fetching updates...');
        try {
            if ($comp == "joomla") {
                $model->applyUpdateSite();
                $model->refreshUpdates();
            } else {
                $updater->findUpdates(0, $cache_timeout);
            }
            return 0;
        } catch  (Exception $e) {
            $this->out('-> Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * @param $installer
     * @param $eid
     * @param $element
     * @param $cache_version
     * @return array|false|int[]
     * @throws Exception
     */
    private function parseSchemaUpdates($installer, $eid, $element, $cache_version)
    {
        $db = \JFactory::getDbo();
        $sqlpath = "";
        $update_count = 0;
        $files = array();

        # Get path where sql files are located
        if ($schemapath = $this->manifest_xml->update->schemas->schemapath) {
            $sqlpath = JPATH_COMPONENT_ADMINISTRATOR . $element . '/' . $schemapath;
        }

        # Get files or exit and update schema table
        if (is_dir($sqlpath)) {
            $files = JFolder::files($sqlpath, '\.sql$');
        } else {
            if ($this->verbose) {
                $this->out("-> No SQL Folder");
            }
            $this->updateSchema($eid, $files, null, $this->manifest_xml->version);
            return array(0, 0);
        }

        # If schema version don't exists, set up from manifest cache
        if (!$this->schema_version = $this->getSchemaVersion($eid)) {
            $this->schema_version = $cache_version;
        }


        # We couldn't have schema version ahead of xml version
        try {
            if (version_compare($this->schema_version, $this->manifest_xml->version) > 0) {
                throw new Exception();
            }
        } catch (Exception $e) {
            $this->out("ERROR -> " . $element . " : schema version ahead of manifest file version");
            $this->count_fails++;
            exit();
        }


        # Prepare files for execution
        if (!empty($files)) {
            $files = str_replace('.sql', '', $files);

            # Sorting function for different version formats
            $isVersion = function ($a) {
                return is_numeric(str_replace('.', '', $a));
            };
            $sortFunction = function ($a, $b) use ($isVersion) {
                if ($isVersion($a) && $isVersion($b)) {
                    return version_compare($a, $b);
                } elseif ($isVersion($a)) {
                    return -1;
                } elseif ($isVersion($b)) {
                    return 1;
                } else {
                    return strcasecmp($a, $b);
                }
            };
            usort($files, $sortFunction);

            /*if ($this->schema_version < $files[0] && !$this->firstrun) {
                throw new Exception("Schema version can't be inferior than the first update file. You need to update version in xml of " . $element);
            }*/

            while (end($files) > $this->manifest_xml->version and !empty($files)) {
                array_pop($files);
            }
            while (reset($files) < $cache_version and !empty($files)) {
                array_shift($files);
            }
        }
        if (empty($files)) {
            if ($this->verbose) {
                $this->out("-> No SQL Files");
            }
            $this->updateSchema($eid, $files, null, $this->manifest_xml->version);
            return array(0, 0);
        }

        # Execute queries
        foreach ($files as $file) {

            $buffer = file_get_contents($sqlpath . '/' . $file . '.sql');
            if (0 == filesize($sqlpath . '/' . $file . '.sql')) {
                continue;
            }

            # Graceful exit and rollback if read not successful
            if ($buffer === false) {
                Log::add($element . " : " . $file . ".sql  -> Error SQL Read buffer", Log::ERROR, 'error');
                return array(0, 0);
            }

            # Create an array of queries from the sql file
            $queries = \JDatabaseDriver::splitSql($buffer);

            if (count($queries) === 0) {
                # No queries to process
                continue;
            }

            # Process each query in the $queries array (split out of sql file).
            foreach ($queries as $query) {
                $db->setQuery($db->convertUtf8mb4QueryToUtf8($query));

                # queryString for query log details
                $queryString = $db->replacePrefix((string)$query);
                $queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 120));
                $queryString = (string)$query;
                # Change third parameter of substr for changing length of query log
                $queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 120));

                try {
                    $db->execute();
                } catch (\JDatabaseExceptionExecuting $e) {

                    Log::add("[FAIL] " . $element . " : " . $file . ".sql \n $e->getMessage() \n $this->db->getQuery()", Log::ERROR, 'error');
                    Log::add($e->getMessage(), Log::ERROR, 'error');
                    Log::add(str_replace(PHP_EOL, '', $queryString), Log::INFO, 'error');

                    $this->out("-> Error : " . $e->getMessage());
                    $installer->abort($e->getMessage(), $db->stderr(true));
                    return false;
                }

                Log::add("[EXEC] " . $element . " : " . $file . ".sql  ->", Log::INFO, 'update');
                Log::add(str_replace(PHP_EOL, '', $queryString), Log::INFO, 'update');

                $update_count++;
            }
        }
        # Update the database
        if ($update_count >= 0 and !$this->firstrun) {
            $this->updateSchema($eid, $files, 'end');
        }
        return array($update_count, $files);
    }

    /**
     * @param $ext_id
     * @param $element
     * @return void
     */
    private function refreshManifestCache($ext_id, $element)
    {
        if (is_array($ext_id)) {
            $ext_id = $ext_id[0];
        }
        $installer = JInstaller::getInstance();
        $installer->extension->load($ext_id);

        # For Joomla update method works, we need to rename manifest file for some extensions (extplorer and dropfiles)
        if ($element == 'com_extplorer' or $element == 'com_dropfiles') {
            $comp = $this->getElementFromId('extensions', array($ext_id));
            $manifest = json_decode($comp[0]['manifest_cache'], true);
            $file = JPATH_ADMINISTRATOR . '/components/' . $comp[0]['element'] . '/' . $comp[0]['element'] . '.xml';
            $short_element = str_replace('com_', '', $comp[0]['element']);
            if (file_exists($file)) {
                $rename_file = JPATH_ADMINISTRATOR . '/components/' . $comp[0]['element'] . '/' . $short_element . '.xml';
                rename($file, $rename_file);
            }
        }
        $result = 0;
        $result |= $installer->refreshManifestCache($ext_id);

        # Case for component with non conventional file naming
        if ($element == 'com_extplorer' or $element == 'com_dropfiles') {
            if (file_exists($rename_file)) {
                rename($rename_file, $file);
                $manifest['version'] = (string)$this->manifest_xml->version;
                $manifest = json_encode($manifest);
                $installer->extension->manifest_cache = $manifest;
                $installer->extension->store();
            }
        }
        if ($result != 1) {
            $this->out("-> Refresh manifest cache Failed");
            exit();
        }
        return $installer->manifest->version;
    }

    /**
     * Update version in schema table. Query adapts according to parseSchemaUpdates results
     * @param $eid
     * @param $files
     * @param $method : use it if version isn't identified, use "end" for the last element or "reset" for the first element of files
     * @param $version : needed if there is no files
     * @return void
     */
    private function updateSchema($eid, $files = null, $method = null, $version = null)
    {
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__schemas')
            ->where('extension_id = ' . $eid);
        $db->setQuery($query);

        if ($db->execute()) {
            if ($method && $files) {
                $query->clear()
                    ->insert($db->quoteName('#__schemas'))
                    ->columns(array($db->quoteName('extension_id'), $db->quoteName('version_id')))
                    ->values($eid . ', ' . $db->quote($method($files)));
            } else {
                $query->clear()
                    ->insert($db->quoteName('#__schemas'))
                    ->columns(array($db->quoteName('extension_id'), $db->quoteName('version_id')))
                    ->values($eid . ', ' . $db->quote($version));
            }
            $db->setQuery($query);
            $db->execute();

        }
    }

    /**
     * Restore previous xml version before & after update because contextual update for dpcalendar is based on xml to set up the version
     * @param $xml_path
     * @param $version
     * @return void
     */
    private function restoreVersion($xml_path, $version)
    {
        $dom = new DOMDocument();
        $dom->load($xml_path);
        $dom->formatOutput = true;
        $dom->getElementsByTagName("version")->item(0)->nodeValue = "";
        $dom->getElementsByTagName("version")->item(0)->appendChild($dom->createTextNode($version));
        $dom->save($xml_path);
    }

    /**
     * Display a log with specific type
     * @param $str
     * @param $type
     *
     *
     * @since version 1.33.0
     */
    private function colorLog($str, $type = 'i')
    {
        $results = $str;
        switch ($type) {
            case 'e': //error
                $results = "\033[31m$str \033[0m";
                break;
            case 's': //success
                $results = "\033[32m$str \033[0m";
                break;
            case 'w': //warning
                $results = "\033[33m$str \033[0m";
                break;
            case 'i': //info
                $results = "\033[36m$str \033[0m";
                break;
            default:
                # code...
                break;
        }

        return $results;
    }
}


JApplicationCli::getInstance('UpdateCli')->execute();
