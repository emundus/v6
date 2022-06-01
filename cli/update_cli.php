<?php

use Joomla\CMS\Log\Log;
use Joomla\Utilities\ArrayHelper;

const _JEXEC = 1;
error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);
define('JPATH_BASE', dirname(__DIR__));
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_CONFIGURATION . '/configuration.php';
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/');
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_joomlaupdate/models/default.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_installer/models/update.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_installer/models/install.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_installer/models/discover.php';

# TODO : need better logs -> check how to list all queries executed
Log::addLogger(
    array(
        'text_file' => 'update_cli_errors.log.php',
        #'text_entry_format' => '{DATETIME} {PRIORITY} {MESSAGE} {CATEGORY}'
    ),
    JLog::ALL, array('jerror'));
Log::addLogger(
    array(
        'text_file' => 'update_cli_queries.log.php',
        #'text_entry_format' => '{DATETIME} {PRIORITY} {MESSAGE} {CATEGORY}'
    ),
    JLog::ALL, array('update'));


class UpdateCli extends JApplicationCli
{
    # Utils functions
    private function getUpdateId($table, $ids)
    {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . $table)
            ->where($this->db->quoteName('extension_id') . " IN (" . implode(',', $ids) . ')');
        $this->db->setQuery($query);
        return $this->db->loadAssocList('',);
    }

    public function getExtensionId($table, $comp)
    {
        $query = $this->db->getQuery(true);
        $query->select('extension_id')
            ->from('#__' . $table)
            ->where($this->db->quoteName('element') . " = '" . $comp . "'");
        $this->db->setQuery($query);
        return $this->db->loadRow();
    }

    private function getComponentsId($table, $comp)
    {
        $query = $this->db->getQuery(true);
        $query->select('extension_id, name, package_id, type, element, manifest_cache')
            ->where($this->db->quoteName('element') . " IN (" . implode(',', $this->db->quote($comp)) . ') AND state != -1')
            ->from('#__' . $table);
        $this->db->setQuery($query);
        return $this->db->loadAssocList('extension_id');
    }

    public function getComponentArray()
    {
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('extension_id, element, enabled')
            ->from('#__extensions')
            ->where("enabled = 1 AND type = 'component'");
        $db->setQuery($query);
        return $this->db->loadAssocList();
    }

    public function getSchemaVersion($eid)
    {
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('version_id')
            ->from('#__schemas')
            ->where('extension_id = ' . $eid);
        $db->setQuery($query);
        return $db->loadResult();
    }

    public function purgeAndFetchUpdates($id = null)
    {
        // Get the update cache time
        $component = JComponentHelper::getComponent('com_installer');
        $updater = JUpdater::getInstance();
        $minimumStability = JUpdater::STABILITY_STABLE;
        if ($id == 700) {
            $model = JModelLegacy::getInstance('JoomlaupdateModelDefault');
        } else {
            $model = JModelLegacy::getInstance('InstallerModelUpdate');
        }

        $cache_timeout = 3600 * JComponentHelper::getParams('com_installer')->get('cachetimeout', 6, 'int');

        // Purge all updates
        $this->out('Purge updates...');
        $model->purge();
        // Find all updates
        $this->out('Fetching updates...');
        if ($id == 700) {
            $model->applyUpdateSite();
            $model->refreshUpdates();
        } else {
            $updater->findUpdates(0, $cache_timeout);
        }
    }

    public function getUpdateList()
    {
        $this->purgeAndFetchUpdates();
        $query = $this->db->getQuery(true);

        //Exclude Joomla & Gantry5
        $query->select("u.extension_id, u.update_id,u.element, u.type, u.version, e.name")
            ->where("u.extension_id NOT LIKE 0")
            ->from($this->db->quoteName('#__updates', 'u'))
            ->join('LEFT', $this->db->quoteName('#__extensions', 'e') . ' ON u.extension_id = e.extension_id');

        $this->db->setQuery($query);
        $arr = $this->db->loadAssocList();
        $mask = "|%5s |%25s | %25s | %35s | %10s | %5s\n";
        printf($mask, 'Id', 'Uid', 'Element', 'Name', 'Type', 'Version');

        $key = array_values($arr);

        foreach ($key as $k) {
            printf($mask, $k['extension_id'], $k['update_id'], $k['element'], $k['name'], $k['type'], $k['version']);
        }
    }

    public function doEchoHelp()
    {
        echo <<<EOHELP
            Joomla! CLI Update DB
            
            Operations
              -c            Update Joomla core files
              -u            Target all updates (except Joomla files)
              -u ID         Update extension(s) from input id
              -l            List all updates available whith informations
              -h            Helper
              
              
            Extensions list with ID
                Admintools          10003
                Fabrik              10041
                Jumi                10092
                Falang              11244
                Emundus             11369
                Hikashop            11373
                Security Check Pro  11496
                Api                 11545
                Dpcalendar          11739
                Gantry              11852
                JCH Optimize        12161              
                Dropfiles           12338
                Extplorer           12244
                Hikamarket          12938
                Eventbooking        13028
                Externallogin       13111
                JCE                 13338
                Loginguard          13486
                Miniorange          13487
                
            EOHELP;
    }

    private function refreshManifestCache($ext_id = null)
    {
        if (is_array($ext_id)) {
            $ext_id = $ext_id[0];
        }
        $installer = JInstaller::getInstance();
        $installer->extension->load($ext_id);
        # For Joomla update method to works, we need to rename folder for some extensions (extplorer dropfiles & jchoptimize)
        if ($ext_id == '12244' or $ext_id == '12338') {
            $comp = $this->getUpdateId('extensions', array($ext_id));
            $manifest = json_decode($comp[0]['manifest_cache'], true);
            $file = JPATH_ADMINISTRATOR . '/components/' . $comp[0]['element'] . '/' . $manifest['filename'] . '.xml';
            $element = str_replace('com_', '', $comp[0]['element']);
            if (file_exists($file)) {
                rename($file, JPATH_ADMINISTRATOR . '/components/' . $comp[0]['element'] . '/' . $element . '.xml');
            }
        }
        $result = 0;
        $result |= $installer->refreshManifestCache($ext_id);
        if ($result != 1) {
            $this->out("-> Refresh manifest cache Failed");
            exit();
        }
    }

    public function parseSchemaUpdates($installer, $eid, $element)
    {
        $db = \JFactory::getDbo();

        $update_count = 0;
        $files = array();

        if ($schemapath = $this->manifest->update->schemas->schemapath) {
            $sqlpath = JPATH_COMPONENT_ADMINISTRATOR . $element . '/' . $schemapath;
        }

        if (is_dir($sqlpath)) {
            $files = JFolder::files($sqlpath, '\.sql$');
        } else {
            $this->out("-> " . $element . " don't have SQL updates");
            $this->updateSchema($eid, $files, null, $this->manifest->version);
            return array(0, 0);
        }

        if (!$schema_version = $this->getSchemaVersion($eid)) {
            $schema_version = $this->manifest->version->__toString();
        }
        if (empty($files)) {
            $this->out("-> " . $element . " don't have SQL updates");
            $this->updateSchema($eid, $files, null, $this->manifest->version);
            return array(0, 0);
        }
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

        # Search matching file with version in schema table
        foreach ($files as $file) {
            if (strpos($file, $schema_version) !== FALSE) {
                $key = array_search($file, $files);
                break;
            } elseif (version_compare($file, $schema_version) > 0) {
                $key = array_search($file, $files);
                break;
            }
        }
        # Set starting level for sql update
        if ($key >= 0) {
            $begin_update = $files[$key];
        } elseif ($key === null) {
            $begin_update = end($files);
        } else {
            $begin_update = reset($files);
        }

        if ($begin_update === null) {
            $this->updateSchema($eid, $files, null, $this->manifest->version);
            return array(0, 0);
        }

        foreach ($files as $file) {
            if (version_compare($file, $begin_update) > 0) {

                $buffer = file_get_contents($sqlpath . '/' . $file . '.sql');
                if (0 == filesize($sqlpath . '/' . $file . '.sql')) {
                    continue;
                }

                // Graceful exit and rollback if read not successful
                if ($buffer === false) {
                    Log::add($element . " : " . $file . ".sql  --> Error SQL Read buffer", Log::ERROR, 'jerror');
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
                    try {
                        $db->execute();
                    } catch (\JDatabaseExceptionExecuting $e) {
                        Log::add($e->getMessage(), Log::ERROR, 'jerror');
                        Log::add("[FAIL] " . $element . " : " . $file . ".sql  -->" . $queryString, Log::ERROR, 'update');

                        $this->out("-> Error : " . $e->getMessage());
                        $installer->abort($e->getMessage(), $db->stderr(true));

                        # TODO : if fail stop update and rollback
                        #break;
                        return false;
                    }
                    $queryString = (string)$query;
                    # Change third parameter of substr for changing length of query log
                    $queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 120));
                    Log::add("[EXEC] " . $element . " : " . $file . ".sql  -->" . $queryString, Log::INFO, 'update');

                    $update_count++;
                }
            }
        }
        # Update the database
        if ($update_count > 0) {
            $this->updateSchema($eid, $files, 'end');
        } else {
            $this->updateSchema($eid, $files, null, $this->manifest->version);
        }
        return array($update_count, $files);
    }

    private function updateSchema($eid, $files, $method = null, $version = null)
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

    public function customCopy($src, $dst)
    {
        // open the source directory
        $dir = opendir($src);
        $copy_count = 0;
        // Make the destination directory if not exist
        @mkdir($dst, 0777, true);

        // Loop through the files in source directory
        while ($file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {

                    // Recursively calling custom copy function
                    // for sub directory
                    $this->customCopy($src . '/' . $file, $dst . '/' . $file);

                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
            $copy_count++;
        }
        closedir($dir);
        return $copy_count != null;
    }

    public function deleteTmpFiles()
    {
        $path = JPATH_ROOT . '/tmp/';
        if (file_exists($path)) {
            $dir = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }
        }
    }

    private function restoreVersion($xml_path, $version)
    {
        $dom = new DOMDocument();
        $dom->load($xml_path);
        $dom->formatOutput = true;
        $dom->getElementsByTagName("version")->item(0)->nodeValue = "";
        $dom->getElementsByTagName("version")->item(0)->appendChild($dom->createTextNode($version));
        $dom->save($xml_path);

    }

    # Main functions
    public function updateJoomla()
    {
        $updater = JModelLegacy::getInstance('JoomlaupdateModelDefault');
        $this->purgeAndFetchUpdates(700);
        $res = $updater->finaliseUpgrade();
        if ($res == 1) {
            $this->out("SQL Update Success...");
        } else {
            $this->out("SQL Update Failed...");
        }
    }

    public function updateComponents($ids = null)
    {
        $installer = JInstaller::getInstance();
        # Case where id isn't defined in script parameters -> update all
        if (!$ids) {
            $ids = array_keys($this->components);

            if ($ids == null) {
                $this->out("No update for this extension");
                return false;
            }
        } else {
            $ids = array($ids);
        }
        foreach ($ids as $id) {
            # Get component rows & load manifest cache to check values
            $elementArr = $this->components[$id];#$this->queryFromId('extensions', $id);
            $manifest = json_decode($elementArr['manifest_cache'], true);

            $this->out("\n---------------");
            $this->out("\nUPDATE " . $manifest['name'] . ' (' . $manifest['version'] . ')');

            # Check xml path and require scriptfile for custom updates
            if ($manifest['filename']) {
                $xml_file = $manifest['filename'] . '.xml';
            } else {
                $xml_file = preg_split("/[_]+/", $elementArr["element"], 2)[1] . '.xml';
            }
            $path = JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/';


            $path_bis = JPATH_ROOT . '/components/' . $elementArr['element'] . '/';
            if (!is_dir($path)) {
                $path = $path_bis;
            }
            $xml_path = $path . $xml_file;
            if (file_exists($xml_path)) {
                $this->manifest = simplexml_load_file($xml_path);

                # Try to find & import the scriptfile, except for some extensions who don't need one
                $ext_without_scriptfile = array("11852", "13487", "13338");
                if (!in_array($id, $ext_without_scriptfile) or $this->manifest->scriptfile) {
                    try {
                        if ($this->manifest->scriptfile) {
                            $scriptfile = JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/' . $this->manifest->scriptfile;
                        } else {
                            unset($scriptfile);
                        }
                        if (file_exists($scriptfile) && is_readable($scriptfile)) {
                            require_once $scriptfile;
                        } else {
                            throw new Exception("-> Scriptfile doesn't exists or is not readable.");
                        }
                    } catch (Exception $e) {
                        $this->out($e->getMessage());
                        Log::add($e->getMessage(), Log::WARNING, 'jerror');
                        #continue;
                        exit();
                    }
                }
                # Step 1 : Execute SQL files for update
                $this->out("\nSQL Updates...");
                $sql_update = $this->parseSchemaUpdates($installer, $id, $elementArr['element']);
                if ($sql_update === false) {
                    $this->out("-> Stop " . $elementArr['element'] . " update");
                    continue;
                }
                $this->out("-> " . $sql_update[0] . " sql queries executed");

                # Step 2 : Check custom updates
                $this->out("\nCustom updates...");

                # Setup adapter
                $installer->setPath('source', $path);

                if (!$adapter = $installer->setupInstall('update', true)) {
                    $installer->abort(\JText::_("Impossible de détecter le fichier manifest"));
                    return false;
                }

                $scriptClass = $elementArr['element'] . "InstallerScript";
                if (class_exists($scriptClass)) {
                    // Create a new instance
                    $script = new $scriptClass();

                    try {
                        switch ($elementArr["element"]) {
                            case 'com_admintools' :
                            case 'com_fabrik' :
                            case 'com_jumi':
                            case 'com_falang':
                            #case 'com_securitycheckpro':
                            case 'com_hikashop':
                            case 'com_hikamarket':
                            # TODO : set emundus version to 1.xx on the first run
                            case 'com_emundus' :
                            case 'com_jchoptimize':
                            case 'com_loginguard':
                            case 'com_dropfiles' :
                            case 'com_extplorer' :
                            case 'com_eventbooking' :
                            case 'com_externallogin' :
                            #case 'com_api' :
                                try {
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
                                break;
                            case 'com_securitycheckpro':
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
                                break;

                            case 'com_dpcalendar' :
                                # Restore previous xml version before & after update because dpcalendar based on xml for version setting
                                $new_version = (string)$this->manifest->version[0];
                                $this->restoreVersion($xml_path, $manifest['version']);

                                if ($script->preflight('update', $adapter) === false) {
                                    $res = false;
                                    break;
                                }
                                $script->update($adapter);

                                $this->restoreVersion($xml_path, $new_version);

                                $script->postflight('update', $adapter);
                                break;

                            case 'com_jce' :
                                $res = WFInstall::install($adapter);
                                break;

                            default :
                                $this->out("Extension not recognized");
                        }

                        $schema_version = $this->getSchemaVersion($id);

                        # Check success of custom updates, if true overwrite new version in xml
                        if ($res !== false) {
                            $this->refreshManifestCache($id);
                            $this->out("\n-> Schema : " . $schema_version);
                            $this->out("-> Extension : " . $this->manifest->version);
                            $this->out("-> Finishing update successfuly with : " . $elementArr['name']);
                        } else {
                            $this->out("-> Custom update fails");
                        }
                    } catch (\Throwable $e) {
                        $this->out($e->getMessage());
                    }

                } else {
                    $this->out("-> Scripfile doesn't exists");
                }
            } else {
                $this->out("-> Manifest path doesn't exists");
            }
        }
    }


    # Execute function
    public function doExecute()
    {
        $app = JFactory::getApplication('site');
        #$app->initialise();
        // Set direct download mode
        $app->input->set('method', 'direct');
        $executionStartTime = microtime(true);
        $this->db = JFactory::getDbo();

        $short_options = "hlcu::";
        $long_options = ["help", "list", "core", "update::"];
        $options = getopt($short_options, $long_options);

        $args = (array)$GLOBALS['argv'];

        echo "Emundus Update Tool \n\n";

        if (isset($options["h"]) || isset($options["help"])) {
            $this->doEchoHelp();
        }
        if (isset($options["l"]) || isset($options["list"])) {
            $this->getUpdateList();
        }
        if (isset($options["c"]) || isset($options["core"])) {
            $this->updateJoomla();
        }
        if (isset($options["u"]) || isset($options["update"])) {
            # Array of components available for update
            $availableComp = array('com_emundus', 'com_fabrik', 'com_hikashop', 'com_hikamarket', 'com_falang',
                'com_eventbooking', 'com_dpcalendar', 'com_dropfiles', 'com_extplorer',
                'com_miniorange_saml', 'com_loginguard', 'com_jce', 'com_admintools',
                'com_jumi', 'com_gantry5', 'com_externallogin'); #, 'com_jchoptimize','com_securitycheckpro', 'com_api'
            $compArr = $this->getComponentsId('extensions', $availableComp);

            # Array of components with refreshed informations
            $this->components = $this->getComponentsId('extensions', $availableComp);

            if (sizeof($args) == 2) {
                $this->updateComponents($compArr['extension_id']);
            } elseif (sizeof($args) > 2) {

                $index = 2;
                while ($index <= sizeof($args) - 1) {
                    $id = $args[$index];
                    $this->updateComponents($uid = $id);
                    $index++;
                }

            }
        }

        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        $this->out("This script took $seconds to execute.");
    }
}

JApplicationCli::getInstance('UpdateCli')->execute();
