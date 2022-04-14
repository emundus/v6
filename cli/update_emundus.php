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

JLog::addLogger(array('text_file' => 'update_emundus.log.php'), JLog::ALL, array('jerror'));
JLog::addLogger(array('text_file' => 'sql_emundus.log.php'), JLog::INFO, array('Update'));

$emundusScript = JPATH_COMPONENT_ADMINISTRATOR . 'com_emundus/script.com_emundus.php';
try {
    if (file_exists($emundusScript) && is_readable($emundusScript)) {
        require_once $emundusScript;
    } else {
        throw new Exception('script.com_emundus.php does not exists or is not readable.');
    }
} catch(Exception $e) {
    echo $e->getMessage();
    \JLog::add(\JText::sprintf($e->getMessage()), \JLog::WARNING, 'jerror');

    exit();
}


class UpdateEmundus extends JApplicationCli
{
    public function doEchoHelp()
    {
        echo <<<EOHELP
            Joomla! CLI Update DB
            
            Operations
              -e, --emundus             
            
            EOHELP;
    }

    public function getExtensionId() {
        $db = JFactory::getDbo();
        $res = $db->setQuery("select extension_id from #__extensions where element = 'com_emundus'")->loadRow();

        return $res[0];
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

    private function updateSchema($eid, $files, $method)
    {
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__schemas')
            ->where('extension_id = ' . $eid);
        $db->setQuery($query);

        if ($db->execute())
        {
            $query->clear()
                ->insert($db->quoteName('#__schemas'))
                ->columns(array($db->quoteName('extension_id'), $db->quoteName('version_id')))
                ->values($eid . ', ' . $db->quote($method($files)));
            $db->setQuery($query);
            $db->execute();

        }
    }

    private function refreshManifestCache(){
        $this->out("\nRefresh manifest cache...");
        $installer = JInstaller::getInstance();
        $result = 0;
        $result |= $installer->refreshManifestCache($this->getExtensionId());
        if ($result != 1) {
            $this->out("-> Failed");
            exit();
        } else { $this->out("-> OK");}
    }

    public function emundusToSchemas($version)
    {
        if (!empty($version)) {
            $db = JFactory::getDbo();
            $emundusIdInfos = $db->setQuery("select * from #__schemas where extension_id in (select extension_id from #__extensions where element = 'com_emundus')")->loadRow();
            if (!$emundusIdInfos[0]){
                $emundusIdInfos[0] == '11369';
            }
            if (!$emundusIdInfos[0] AND !$emundusIdInfos[1]){ #!$db->loadAssoc()) {
                $db->setQuery(
                    "insert into #__schemas (extension_id, version_id)
				select extension_id," . $db->quote($version) .
                    " from #__extensions where element = 'com_emundus'");
            } else {
                $db->setQuery(
                    "update #__schemas set `version_id` =" . $db->quote($version) . "WHERE `extension_id` = " . $db->quote($emundusIdInfos[0]));
            }
            $db->execute();
        } else {
            $this->out("Version empty -> Check xml file");
        }
        return $db->setQuery("select * from #__schemas where extension_id in (select extension_id from #__extensions where element = 'com_emundus')")->loadRow();;
    }


    public function parseSchemaUpdates($eid)
    {
        $db = \JFactory::getDbo();
        $update_count = 0;
        $sqlpath = JPATH_COMPONENT_ADMINISTRATOR . 'com_emundus/sql/updates/mysql';
        if (is_dir($sqlpath)) {
            $files = JFolder::files($sqlpath, '\.sql$');
        }
        $version = $this->getSchemaVersion($eid);
        // No version - use initial version.


        if (empty($files)) {
            return $update_count;
        }

        $files = str_replace('.sql', '', $files);
        usort($files, 'version_compare');

        if (!$version) {
            $version = "0.0.0";
            $this->manifest->version = reset($files);
            //$this->refreshManifestCache();
        }

        $key = array_search($version, $files);
        if ($key > 0){
            $this->manifest->version = $files[$key-1];
        } else {
            $this->manifest->version = reset($files);
        }

        foreach ($files as $file)
        {
            if (version_compare($file, $version) > 0)
            {
                $buffer = file_get_contents($sqlpath . '/' . $file . '.sql');

                // Graceful exit and rollback if read not successful
                if ($buffer === false)
                {
                    \JLog::add(\JText::sprintf('Error SQL Read buffer'), \JLog::WARNING, 'jerror');
                    return $update_count;
                }

                // Create an array of queries from the sql file
                $queries = \JDatabaseDriver::splitSql($buffer);

                if (count($queries) === 0) {
                    // No queries to process
                    continue;
                }

                // Process each query in the $queries array (split out of sql file).
                foreach ($queries as $query)
                {
                    $db->setQuery($db->convertUtf8mb4QueryToUtf8($query));
                    try
                    {
                        $db->execute();
                    }
                    catch (\JDatabaseExceptionExecuting $e)
                    {
                        \JLog::add(\JText::sprintf($e->getMessage()), \JLog::WARNING, 'jerror');
                        $this->out("-> Error : " . $e->getMessage());
                        exit();
                    }
                    # queryString for query log details
                    //$queryString = (string) $query;
                    //$queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 80));
                    \JLog::add(\JText::sprintf($file . ".sql executed"), \JLog::INFO, 'Update');
                    $update_count++;
                }
            }
        }
        // Update the database
        $this->updateSchema($eid, $files, 'end');
        return array($update_count, $files);
    }


    public function doExecute()
    {
        $app = JFactory::getApplication('site');
        $app->initialise();
        $executionStartTime = microtime(true);

        echo "Emundus Update Tool \n\n";

        $xml_path = JPATH_ADMINISTRATOR . '/components/com_emundus/emundus.xml';
        $this->refreshManifestCache();
        $this->manifest = simplexml_load_file($xml_path);

        #$version = null;

        // Refresh manifest cache
        if (file_exists($xml_path)) {

        // Add or update extension in schema table
        #$this->out("\nCheck row com_emundus in __schemas table...");
        #$res = $this->emundusToSchemas($version);
        #$this->out("-> extension_id : " . $res[0] . " with version : " . $res[1]);

        // Execute SQL files for update
        $this->out("\nSQL Updates...");
        $sql_update = $this->parseSchemaUpdates($this->getExtensionId());
        $this->out("-> " . $sql_update[0] . " sql queries executed" );

            // Check custom updates
            $this->out("\nCustom updates...");
            try {
                $this->manifest->asXML(JPATH_ADMINISTRATOR . '/components/com_emundus/emundus.xml');
                $this->refreshManifestCache();

                $script_emundus = new com_emundusInstallerScript();
                $version = $script_emundus->updateSQL();

                if($version != null) {
                    $this->manifest->version = $version;
                    $this->manifest->asXML(JPATH_ADMINISTRATOR . '/components/com_emundus/emundus.xml');
                }
                $this->refreshManifestCache();
                $this->out("\nSchema : " . $this->getSchemaVersion($this->getExtensionId()));
                $this->out("Extension : " . $this->manifest->version);

            } catch (\Throwable $e) {echo $e->getMessage();}

        } else { $this->out("-> Manifest path not exists");}

        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        echo "\nThis script took $seconds to execute\n";
    }
}

JApplicationCli::getInstance('UpdateEmundus')->execute();
