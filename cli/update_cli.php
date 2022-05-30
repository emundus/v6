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
            ->where($this->db->quoteName('element') . " IN (" . implode(',', $this->db->quote($comp)) . ')') #AND state != -1 ')
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
                Associations        11540
                Fields              11541
                Api                 11545
                Dpcalendar          11739
                Gantry              11852
                Actionlogs          12115
                Privacy             12116
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
        if ($ext_id == '12244' OR $ext_id == '12338') {
            $comp = $this->getUpdateId('extensions', array($ext_id));
            $manifest = json_decode($comp[0]['manifest_cache'], true);
            $file = JPATH_ADMINISTRATOR . '/components/' . $comp[0]['element'] . '/' . $manifest['filename'] . '.xml';
            $element = str_replace('com_', '', $comp[0]['element'] );
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
            $this->updateSchema($eid, $files,null, $this->manifest->version);
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

                        # TODO : if fail stop update and rollback
                        #break;
                        return false;
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

    private function restoreVersion($xml_path, $version)
    {
        $dom = new DOMDocument();
        $dom->load($xml_path);
        $dom->formatOutput = true;
        $dom->getElementsByTagName("version")->item(0)->nodeValue = "";
        $dom->getElementsByTagName("version")->item(0)->appendChild($dom->createTextNode($version));
        $dom->save($xml_path);

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

/*            if ($id == "12161") {
                $this->rename($id);
            }*/

            # Check xml path and require scriptfile for custom updates
            if($manifest['filename']) {
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

                # Try to find the scriptfile, except for some extensions who don't need one
                $ext_without_scriptfile = array("11540", "11541", "11852", "12115", "12116", "13487");
                if (!in_array($id, $ext_without_scriptfile) OR $this->manifest->scriptfile) {
                    try {
                        if ($this->manifest->scriptfile) {
                            $scriptfile = JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/' . $this->manifest->scriptfile;
                        } else {
                            $scriptfile = JPATH_ADMINISTRATOR . '/components/' . $elementArr['element'] . '/install.php';
                        }
                        if (file_exists($scriptfile) && is_readable($scriptfile)) {
                            require_once $scriptfile;
                        } else {
                            throw new Exception("-> Scriptfile does not exists or is not readable.");
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        \JLog::add(\JText::sprintf($e->getMessage()), \JLog::WARNING, 'jerror');

                        exit();
                    }
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
                    switch ($elementArr['element']) {
                        case 'com_admintools' :
/*                            $installer = JInstaller::getInstance();
                            $installer->setPath('source', $path);
                            if (!$adapter = $installer->setupInstall('update', true))
                            {
                                $installer->abort(\JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));
                                return false;
                            }
                            $script = new Com_AdmintoolsInstallerScript;
                            $res = $script->preflight('update', $adapter);
                            $post = $script->postflight('update', $adapter);*/
                            break;

                        case 'com_fabrik' :
                            $script = new Com_FabrikInstallerScript;
                            $script->preflight('update', $installer);
                            $res = $script->update($installer);
                            if ($script->postflight('update', $installer) === false) {
                                $res = false;
                            }
                            break;

                        case 'com_jumi' :
                            $script = new com_jumiInstallerScript();
                            # Empty script
                            $script->preflight('update', $installer);
                            $script->update($installer);
                            $script->postflight('update', $installer);
                            break;

                        case 'com_falang' :
                            $script = new com_falangInstallerScript();
                            $installer = JInstaller::getInstance();
                            $installer->setPath('source', $path);
                            if (!$adapter = $installer->setupInstall('update', true))
                            {
                                $installer->abort(\JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));
                                return false;
                            }
                            $script->preflight('update', $adapter);
                            $script->update($installer);
                            $script->postflight('update', $adapter);
                            break;

                        case 'com_emundus':
                            $script = new com_emundusInstallerScript();
                            $res = $script->update();
                            break;

                        case 'com_hikashop' :
                            $script = new com_hikashopInstallerScript();
                            $pre = $script->preflight('update', $installer);
                            $up = $script->update($installer);
                            $post = $script->postflight('update', $installer);
                            if ($pre and $up and $post) {
                                $res = true;
                            }
                            break;

                        case 'com_securitycheckpro' :
                            $script = new com_SecuritycheckproInstallerScript();
                            $installer = JInstaller::getInstance();
                            $installer->setPath('source', $path);
                            if (!$adapter = $installer->setupInstall('update', true))
                            {
                                $installer->abort(\JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));
                                return false;
                            }
                            $script->preflight('update', $adapter);
                            $script->update($adapter);
                            $script->postflight('update', $adapter);
                            break;

                        case 'com_api' :
                            # TODO : absent du projet core
                            break;

                        case 'com_dpcalendar' :
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
                            break;

                        case 'com_jchoptimize' :
                            $script = new Com_JchoptimizeInstallerScript();
                            $installer = JInstaller::getInstance();
                            $installer->setPath('source', $path);
                            if (!$adapter = $installer->setupInstall('update', true))
                            {
                                $installer->abort(\JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));
                                return false;
                            }
                            $res = $script->preflight('update', $adapter);
                            $script->postflight('update', $adapter);
                            break;
                        case 'com_dropfiles' :
                            $script = new Com_DropfilesInstallerScript();
                            $installer = JInstaller::getInstance();
                            $installer->setPath('source', $path);
                            if (!$adapter = $installer->setupInstall('update', true))
                            {
                                $installer->abort(\JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));
                                return false;
                            }
                            $script->update();
                            $installer = JInstaller::getInstance();
                            $res = $script->postflight("update", $adapter);
                            break;

                        case 'com_extplorer' :
                            $script = new com_extplorerInstallerScript();
                            $script->preflight('update', $installer);
                            $res = $script->update($installer);
                            $script->postflight('update', $installer);
                            break;

                        case 'com_hikamarket' :
                            $script = new com_hikamarketInstallerScript();
                            $pre = $script->preflight('update', $installer);
                            $up = $script->update($installer);
                            $post = $script->postflight('update', $installer);
                            if ($pre and $up and $post) {
                                $res = true;
                            }
                            break;

                        case 'com_eventbooking' :
                            $script = new com_eventbookingInstallerScript();
                            $script->preflight('update', $installer);
                            $script->postflight('update', $installer);
                            break;

                        case 'com_externallogin' :
                            # TODO : find scriptfile
                            break;

                        case 'com_jce' :
                            $installer = JInstaller::getInstance();
                            $res = WFInstall::install($installer);
                            break;

                        case 'com_loginguard' :
                            $script = new Com_LoginguardInstallerScript();
                            $res = $script->preflight('update', $installer);
                            $script->postflight('update', $installer);
                            break;

                        case 'com_fields' AND 'com_associations' AND 'com_gantry5' AND 'com_actionlogs' AND 'com_privacy' AND 'com_miniorange_saml':
                            $this->out("-> " . $elementArr['element'] . " don't have custom script");
                            break;

                        default :
                            $this->out("Extension non reconnue");
                    }

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

        $short_options = "hlcu::";
        $long_options = ["help", "list", "core", "update::"];
        $options = getopt($short_options, $long_options);

        $args = (array) $GLOBALS['argv'];

        echo "Emundus Update Tool \n\n";

        #if ($this->input->get('h', $this->input->get('help'))) {
        if(isset($options["h"]) || isset($options["help"])) {
            $this->doEchoHelp();
        }
        if(isset($options["l"]) || isset($options["list"])) {
        #elseif ($this->input->get('l', $this->input->get('list'))) {
            $this->getUpdateList();
        }
        if(isset($options["c"]) || isset($options["core"])) {
        #elseif ($this->input->get('c', $this->input->get('core'))) {
            $this->updateJoomla();
        }
        if(isset($options["u"]) || isset($options["update"])) {
        #elseif ($this->input->get('u', $this->input->get('update'))) {
            # Array of components available for update
            $availableComp = array('com_emundus','com_fabrik','com_hikashop','com_hikamarket','com_falang',
                'com_securitycheckpro','com_eventbooking','com_dpcalendar', 'com_dropfiles', 'com_extplorer',
                'com_miniorange_saml', 'com_loginguard', 'com_jchoptimize','com_jch_optimize', 'com_jce', 'com_admintools',
                'com_jumi', 'com_associations', 'com_fields', 'com_api', 'com_gantry5', 'com_actionlogs',
                'com_privacy', 'com_externallogin');
            $compArr = $this->getComponentsId('extensions', $availableComp);

            # Array of components with refreshed informations
            $this->components = $this->getComponentsId('extensions', $availableComp);

            if (sizeof($args) == 2) {
                $this->updateComponents($compArr['extension_id']);
            } elseif (sizeof($args) > 2) {

                $index = 2;
                while ($index <= sizeof($args)-1) {
                    $id = $args[$index];
                    $this->updateComponents($uid = $id);
                    $index++;
                }

            }
        }

        else {
            $this->out("Error : invalid option");
            exit();
        }

        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        echo "\n" . "This script took $seconds to execute.";
    }
}

JApplicationCli::getInstance('UpdateCli')->execute();
