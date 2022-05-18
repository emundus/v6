<?php
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


JLog::addLogger(array('text_file' => 'update_cli_errors.log.php'), JLog::ALL, array('jerror'));
JLog::addLogger(array('text_file' => 'update_cli_queries.log.php'), JLog::INFO, array('Update'));


class UpdateCli extends JApplicationCli
{
    # Utils functions
    private function getUpdateId($table, $ids)
    {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . $table)
            //Exclude Joomla & Gantry5
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

    private function getComponentsId($table, $comp) {
        $query = $this->db->getQuery(true);
        $query->select('extension_id, name, package_id, type, element, manifest_cache')
            ->where($this->db->quoteName('element') . " IN (" . implode(',', $this->db->quote($comp)) . ') AND state != -1 ')
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
              without option           Target all updates
              -l, --list               List all updates whith informations about extension
              -h, --help               Help
              -u, --update             Update extension(s) from id input
              -c, --core               Update Joomla
            
            EOHELP;
    }

    private function refreshManifestCache($ext_id = null)
    {
        if (is_array($ext_id)) {
            $ext_id = $ext_id[0];
        }
        $installer = JInstaller::getInstance();
        $installer->extension->load($ext_id);
        if ($ext_id == '12244' OR $ext_id == '12338' OR $ext_id == '12161') {
            $comp = $this->getUpdateId('extensions', array($ext_id));
            $manifest = json_decode($comp[0]['manifest_cache'], true);
            $file = JPATH_ADMINISTRATOR . '/components/' . $comp[0]['element'] . '/' . $manifest['filename'] . '.xml';
            $element = str_replace('com_', '', $comp[0]['element'] );
            if (file_exists($file)) {
                rename($file, JPATH_ADMINISTRATOR . '/components/' . $comp[0]['element'] . '/' . $element . '.xml');
            }
            if ($ext_id == "12161") {
                $folder = JPATH_ADMINISTRATOR . '/components/' . 'com_jchoptimize';
                rename($folder, JPATH_ADMINISTRATOR . '/components/' . 'com_jch_optimize');
                if(file_exists($file = JPATH_ADMINISTRATOR . '/components/' . 'com_jch_optimize/' . $element . '.xml')) {
                    rename($file, $folder . '/' . 'jch_optimize.xml');
                }
            }
        }
        $result = 0;
        $result |= $installer->refreshManifestCache($ext_id);
        if ($result != 1) {
            $this->out("-> Refresh manifest cache Failed");
            exit();
        }
    }

    public function parseSchemaUpdates($eid, $element)
    {
        $installer = JInstaller::getInstance();
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
            $this->updateSchema($eid, $files,null, $this->manifest->version);
            return array(0, 0);
        }

        if (!$schema_version = $this->getSchemaVersion($eid)) {
            $schema_version = $this->manifest->version->__toString();
            #$schema_version = "0.0.0";
        }
        if (empty($files)) {
            $this->out("-> " . $element . " don't have SQL updates");
            $this->updateSchema($eid, $files,null, $this->manifest->version);
            return array(0, 0);
        }

        $files = str_replace('.sql', '', $files);

        # Sorting function for different version formats
        $isVersion = function($a) { return is_numeric( str_replace('.', '', $a) ); };
        $sortFunction = function($a, $b) use($isVersion) {
            if( $isVersion($a) && $isVersion($b) ) {
                return version_compare($a, $b);
            } elseif( $isVersion($a) ) {
                return -1;
            } elseif( $isVersion($b) ) {
                return 1;
            } else {
                return strcasecmp($a, $b);
            }
        };

        usort($files, $sortFunction);

        foreach($files as $file) {
            if (strpos($file, $schema_version) !== FALSE) {
                $key = array_search($file, $files);
                break;
            } elseif (version_compare($file, $schema_version)>0){
                $key = array_search($file, $files);

                break;
            }
        }
        if ($key >= 0) {
            $begin_update = $files[$key];
        } elseif ($key === null){
            $begin_update = end($files);
        } else {
        $begin_update = reset($files);
        }

        if ($begin_update === null) {
            return array(0, 0);
        }

        foreach ($files as $file) {
            if (version_compare($file, $begin_update) > 0) {

                $buffer = file_get_contents($sqlpath . '/' . $file . '.sql');
                if ( 0 == filesize($sqlpath . '/' . $file . '.sql') ) {continue;}

                // Graceful exit and rollback if read not successful
                if ($buffer === false) {
                    \JLog::add(\JText::sprintf('Error SQL Read buffer'), \JLog::WARNING, 'jerror');
                    return array(0, 0);
                }

                // Create an array of queries from the sql file
                $queries = \JDatabaseDriver::splitSql($buffer);

                if (count($queries) === 0) {
                    // No queries to process
                    continue;
                }

                // Process each query in the $queries array (split out of sql file).
                foreach ($queries as $query) {
                    $db->setQuery($db->convertUtf8mb4QueryToUtf8($query));

                    # queryString for query log details
                    $queryString = (string) $query;
                    $queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 80));
                    try {
                        $db->execute();
                    } catch (\JDatabaseExceptionExecuting $e) {
                        \JLog::add(\JText::sprintf($e->getMessage()), \JLog::WARNING, 'jerror');
                        \JLog::add(\JText::sprintf($file . ".sql failed"), \JLog::INFO, 'Update');
                        \JLog::add(\JText::sprintf("[FAIL] " . $file . ".sql     -->" . $queryString), \JLog::INFO, 'Update');

                        $this->out("-> Error : " . $e->getMessage());
                        $installer->abort($e->getMessage(), $db->stderr(true));
                        # TODO : if fail rollback update --> verify abort
                        # TODO : if fail continue or stop update ?
                        break;
                        #return false;
                    }
                    $queryString = (string) $query;
                    $queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 80));
                    \JLog::add(\JText::sprintf("[EXEC] " . $file . ".sql     -->" . $queryString), \JLog::INFO, 'Update');
                    $update_count++;
                }
            }
        }
        // Update the database
        if($update_count > 0) {
            $this->updateSchema($eid, $files, 'end');
        } else {
            $this->updateSchema($eid, $files,null, $this->manifest->version);
        }
        return array($update_count, $files);
    }

    private function updateSchema($eid, $files, $method=null, $version=null)
    {
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__schemas')
            ->where('extension_id = ' . $eid);
        $db->setQuery($query);

        if ($db->execute()) {
            if($method && $files) {
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


    #Main functions
    public function updateJoomla()
    {
        $updater = JModelLegacy::getInstance('JoomlaupdateModelDefault');
        $this->purgeAndFetchUpdates(700);
        $res = $updater->finaliseUpgrade();
        if ($res == 1) {
            echo "SQL Update Success...";
        } else {
            echo "SQL Update Failed...";
        }
    }

    public function updateComponents($ids = null){
        $installer = JModelLegacy::getInstance('InstallerModelInstall');



        # Case where id isn't defined in script parameters -> update all
        if (!$ids) {
            $ids = array_keys($this->components);
            # $this->purgeAndFetchUpdates();
            # $ids = $this->getUpdateId('updates', $ids);
            #$this->out(sizeof($ids) . " updates found");
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
            $xml_file = $manifest['filename'] . '.xml';
            $xml_path = JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/';
            $xml_path_bis = JPATH_ROOT . '/components/' . $elementArr['element'] . '/';
            if (!is_dir($xml_path)) {
                $xml_path = $xml_path_bis;
            }
            $xml_path = $xml_path . $xml_file;
            if (file_exists($xml_path)) {
                $this->manifest = simplexml_load_file($xml_path);

                try {
                    if ($this->manifest->scriptfile){
                        $scriptfile = JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/' . $this->manifest->scriptfile;
                    } else {
                        $scriptfile = JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/install.php';
                    }
                    if (file_exists($scriptfile) && is_readable($scriptfile)) {
                        require_once $scriptfile;
                    } else {
                        throw new Exception($scriptfile .' does not exists or is not readable.');
                    }
                } catch(Exception $e) {
                    echo $e->getMessage();
                    \JLog::add(\JText::sprintf($e->getMessage()), \JLog::WARNING, 'jerror');

                    exit();
                }

                # Execute SQL files for update
                $this->out("\nSQL Updates...");
                $sql_update = $this->parseSchemaUpdates($id, $elementArr['element']);
                if ($sql_update === false) {
                    $this->out("-> Stop " . $elementArr['element']. " update");
                    continue;
                }
                $this->out("-> " . $sql_update[0] . " sql queries executed");

                // Check custom updates
                $this->out("\nCustom updates...");

                try {

                    if ($elementArr['element'] == 'com_emundus') {
                        $script = new com_emundusInstallerScript();
                        $res = $script->update();

                    } elseif ($elementArr['element'] == 'com_fabrik') {
                        $script = new Com_FabrikInstallerScript;
                        $script->preflight('update', $installer);
                        $res = $script->update($installer);
                        if ($script->postflight('update', $installer) === false) {
                            $res = false;
                        };

                    } elseif ($elementArr['element'] == 'com_dropfiles') {
                        $script = new Com_DropfilesInstallerScript();
                        $script->update();
                        $installer = JInstaller::getInstance();

                        $res = $script->postflight("update", $installer);

                    } elseif ($elementArr['element'] == 'com_hikashop') {
                        $script = new com_hikashopInstallerScript();
                        $pre = $script->preflight('update', $installer);
                        $up = $script->update($installer);
                        $post = $script->postflight('update', $installer);
                        if ($pre and $up and $post) {
                            $res = true;
                        }
                    } elseif ($elementArr['element'] == 'com_hikamarket') {
                        $script = new com_hikamarketInstallerScript();
                        $pre = $script->preflight('update', $installer);
                        $up = $script->update($installer);
                        $post = $script->postflight('update', $installer);
                        if ($pre and $up and $post) {
                            $res = true;
                        }
                    } elseif ($elementArr['element'] == 'com_falang') {
                        #$installer = method_exists($parent, 'getParent') ? $parent->getParent() : $parent->parent;

                        $script = new com_falangInstallerScript();
                        $script->preflight('update', $installer);
                        $script->update($installer);
                        $script->postflight('update', $installer);

                    } elseif ($elementArr['element'] == 'com_securitycheckpro') {
                        $script = new com_SecuritycheckproInstallerScript();
                        $installer = JInstaller::getInstance();

                        $script->preflight('update', $installer);
                        $script->update($installer);
                        $script->postflight('update', $installer);

                    } elseif ($elementArr['element'] == 'com_eventbooking') {
                        $script = new com_eventbookingInstallerScript();
                        $script->preflight('update', $installer);
                        $script->postflight('update', $installer);

                    } elseif ($elementArr['element'] == 'com_extplorer') {
                        $script = new com_extplorerInstallerScript();
                        $script->preflight('update', $installer);
                        $script->update($installer);
                        $script->postflight('update', $installer);

                    } elseif ($elementArr['element'] == 'com_dpcalendar') {
                        $script = new Com_DPCalendarInstallerScript();
                        # Restore previous xml version
                        $new_version = (string)$this->manifest->version[0];
                        $this->restoreVersion($xml_path, $manifest['version']);

                        if ($script->preflight('update', $installer) === false) {
                            $res = false;
                            break;
                        }
                        $script->update($installer);

                        $this->restoreVersion($xml_path, $new_version);

                        $script->postflight('update', $installer);

                    } elseif ($elementArr['element'] == 'com_jce') {
                        $installer = JInstaller::getInstance();
                        $res = WFInstall::install($installer);
                    }
                    # TODO : MiniOrange, LoginGuard

                    $schema_version = $this->getSchemaVersion($id);

                    # Check success of custom updates, if true overwrite new version in xml
                    if ($res !== false) {
                        $this->refreshManifestCache($id);
                        $this->out("\n-> Schema : " . $schema_version);
                        $this->out("-> Extension : " . $this->manifest->version);
                        echo "-> Finishing update successfuly with : " . $elementArr['name'] . "\n";
                    } else {
                        $this->out("-> Custom update fails");
                    }
                } catch (\Throwable $e) {
                    echo $e->getMessage();
                    # TODO : if fail rollback update
                }

            } else {
                $this->out("-> Manifest path not exists");
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

        $args = (array) $GLOBALS['argv'];

        echo "Emundus Update Tool \n\n";

        if ($this->input->get('h', $this->input->get('help'))) {
            $this->doEchoHelp();
        }

        if ($this->input->get('l', $this->input->get('list'))) {
            $this->getUpdateList();
        }

        if ($this->input->get('c', $this->input->get('core'))) {
            $this->updateJoomla();
        }

        if ($this->input->get('u', $this->input->get('update'))) {
            # Array of components available for update
            $availableComp = array('com_jch_optimize','com_emundus','com_fabrik','com_hikashop','com_hikamarket','com_falang',
                'com_securitycheckpro','com_eventbooking','com_dpcalendar', 'com_dropfiles', 'com_extplorer',
                'com_miniorange_saml', 'com_loginguard', 'com_jchoptimize', 'com_jce'); #, 'com_jch_optimize'
            $compArr = $this->getComponentsId('extensions', $availableComp);

           /* foreach ($compArr as $comp) {
                $this->refreshManifestCache($this->getExtensionId('extensions',$comp['element']));
            }*/
            # Array of components with refreshed informations
            $this->components = $this->getComponentsId('extensions', $availableComp);

            if (sizeof($args) == 2) {
                $this->updateComponents($compArr['extension_id']);
            } elseif (sizeof($args) >= 3) {

                $index = 2;
                while ($index <= sizeof($args)-1) {
                    $id = $args[$index];
                    $this->updateComponents($uid = $id);
                    $index++;
                }

            }
        }

        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        echo "\n" . "This script took $seconds to execute.";
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
}

JApplicationCli::getInstance('UpdateCli')->execute();
