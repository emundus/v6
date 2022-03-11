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
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_emundus/script.com_emundus.php';
require_once JPATH_ADMINISTRATOR . '/components/com_hikashop/install.hikashop.php';


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

    public function emundusToSchemas($version)
    {
        if (!empty($version)) {
            $db = JFactory::getDbo();
            $res = $db->setQuery("select * from #__schemas where extension_id in (select extension_id from #__extensions where element = 'com_emundus')")->loadRow();

            if (!$db->loadAssoc()) {
                $db->setQuery(
                    "insert into #__schemas (extension_id, version_id)
				select extension_id," . $db->quote($version) .
                    " from #__extensions where element = 'com_emundus'");
                $db->execute();
            } else {
                $db->setQuery(
                    "update #__schemas set `version_id` =" . $db->quote($version) . "WHERE `extension_id` = " . $db->quote($res[0]));
                $db->execute();
            }
        }
        return $db->setQuery("select * from #__schemas where extension_id in (select extension_id from #__extensions where element = 'com_emundus')")->loadRow();
    }


    public function doExecute()
    {

        JLog::addLogger(array('text_file' => 'update_emundus.log.php'), JLog::ALL, array('jerror'));

        $app = JFactory::getApplication('site');
        $app->initialise();
        // Set direct download mode
        $app->input->set('method', 'direct');
        $executionStartTime = microtime(true);
        $this->db = JFactory::getDbo();

        echo "Emundus Update Tool \n\n";
        if ($name = $this->input->get('e', $this->input->get('emundus'))) {

            $path = JPATH_ADMINISTRATOR . '/components/com_emundus/emundus.xml';
            $version = null;
            if (file_exists($path)) {
                $manifest = simplexml_load_file($path);
                $version = (string)$manifest->version;

                $this->out("Adding com_emundus to __schemas table...");
                $res = $this->emundusToSchemas($version);
                $this->out("-> extension_id : " . $res[0] . " with version : " . $res[1]);

                $this->out("\nCall update function...");
                $script_emundus = new com_emundusInstallerScript();
                $script_emundus->updateSQL();
            }
        }

        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        echo "\nThis script took $seconds to execute\n";
    }
}

JApplicationCli::getInstance('UpdateEmundus')->execute();
