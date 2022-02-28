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

class UpdateCli extends JApplicationCli
{

    private function getUpdateId($table) {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . $table)
            //Exclude Joomla & Gantry5
            ->where($this->db->quoteName('extension_id') . ' NOT LIKE 0 AND' . ($this->db->quoteName('extension_id') . ' NOT LIKE 700 AND') . ($this->db->quoteName('extension_id') . ' NOT LIKE 11970'));
        $this->db->setQuery($query);
        return $this->db->loadAssocList('', 'update_id');
    }

    public function purgeAndFetchUpdates($id = null) {
        // Get the update cache time
        $component = JComponentHelper::getComponent('com_installer');
        $updater = JUpdater::getInstance();
        $minimumStability = JUpdater::STABILITY_STABLE;
        if ($id == 700) {
            $model = JModelLegacy::getInstance('JoomlaupdateModelDefault');
        } else {
            $model = JModelLegacy::getInstance('InstallerModelUpdate');
        }

        $params = $component->params;
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

    public function getInfo() {
        $this->purgeAndFetchUpdates();
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . 'updates')
            //Exclude Joomla & Gantry5
            ->where($this->db->quoteName('extension_id') . ' NOT LIKE 0');
        $this->db->setQuery($query);
        $arr = $this->db->loadAssocList();

        $key = array_values($arr);
        foreach ($key as $k) {
            echo $k['extension_id'] . ' --> ' . $k['name'] . ' (version : ' . $k['version'] . ')' . "\n";
        }
    }

    public function doEchoHelp() {
        echo <<<EOHELP
            Joomla! CLI Update DB
            
            Operations
              -l, --list                  Liste les composants avec une mise à jour disponible
              -h, --help                  Help
              
            Update Filters
              -i, --id                    Met à jour l'extension qui correspond à l'id en etnrée
              -e, --extensions            Toutes les extensions avec une mise à jour disponible
              -c, --core                  Composants Joomla
              -s, --sql                   Mise à jour de la base de données
            
            EOHELP;
    }

    public function queryUpdates($uid)
    {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . 'updates')
            //Exclude Joomla & Gantry5
            ->where($this->db->quoteName('extension_id') . ' LIKE ' . $uid);
        $this->db->setQuery($query);
        return $this->db->loadAssocList('', 'update_id');
    }

    public function updateExtensions($uid = null) {
        $this->out('UPDATE EXTENSIONS...');
        $this->purgeAndFetchUpdates();

        # Update by extension id
        if ($uid != null) {
            $uid = $this->queryUpdates($uid);
        } else {
            $uid = $this->getUpdateId('updates');
        }
        if ($uid == null) {
            $this->out("No update for this extension");
            return false;
        } else {
            $this->out("Update found");
        }


        foreach ($uid as $u) {
            $model = JModelLegacy::getInstance('InstallerModelUpdate');
            $component = JComponentHelper::getComponent('com_installer');
            $params = $component->params;
            $minimum_stability = (int)$params->get('minimum_stability', JUpdater::STABILITY_STABLE);

            echo 'Update : ' . $u . "\n";
            $u = array($u);
            $model->update($u, $minimum_stability);
        }
    }

    public function updateSQLJoomla() {
        $updater = JModelLegacy::getInstance('JoomlaupdateModelDefault');
        $this->purgeAndFetchUpdates(700);
        $res = $updater->finaliseUpgrade();
        if ($res == 1) {
            echo "SQL Update Success...";
        } else {
            echo "SQL Update Failed...";
        }
    }

    public function updateJoomla()
    {
        $this->out('UPDATE JOOMLA...');
        $updater = JModelLegacy::getInstance('JoomlaupdateModelDefault');

        // Make sure we know what the latest version is
        $this->purgeAndFetchUpdates(700);
        // Return a null-object if this is the case
        $version_check = $updater->getUpdateInformation();
        if (is_null($version_check['object'])) {
            echo 'No new updates available' . "\n";
            return 0;
        }

        // Grab the update (ends up in /tmp)
        $this->out("Loading files...");
        $basename = $updater->download();
        $file = $basename['basename'];
        if ($file == null) {
            echo "No files found !";
        } else {

            //TODO: Complete restoration process
            //Create restoration.php (ends up in /com_joomlaupdate)
            //$updater->createRestorationFile($basename);
            //$this->out('Creating restoration...');
            // Extract files to core director
            $this->out("Extracting files...");

            $path = JPATH_ROOT . '/tmp/' . $basename['basename'];
            $zip = new ZipArchive;
            if ($zip->open($path) !== TRUE) {
                die("cannot open archive for writing.");
            } else {
                $zip->extractTo(JPATH_ROOT);
                $zip->close();
            }
            $this->out('Install complete !');

            $this->out('Finalize...');
            $res = $updater->finaliseUpgrade();
            if ($res == 1) {
                echo "SQL Update Success...";
                $updater->cleanUp();
                $this->out('Cleanup...');
            } else {
                echo "SQL Update Failed...";
                return false;
            }
            return true;
        }
    }

    public function installExtension($app, $name)
    {
        $this->out('INSTALL ' . $name);
        $app = JFactory::getApplication();

        $app->input->set('installtype', 'url');
        $app->input->set('install_directory', JPATH_BASE . '/tmp');
        $app->input->set('max_upload_size', '10485760');
        $version = '1.0.0';
        if ($name == 'com_emundus') {
            $app->input->set('install_url', 'http://localhost:81/emundus-updates/packages/com_emundus/com_emundus_1.0.0.zip');
            $this->getInstall();

        } elseif ($name == 'com_fabrik') {
            $fabrik = "http://fabrikar.com/update/fabrik31/package_list.xml";
            $data = simplexml_load_file($fabrik);
            foreach ($data->extension as $ext) {
                $name = (string)$ext['name'];
                $this->out($name);
                if (!strpos($name, "zoom")) {
                    $url = (string)$ext["detailsurl"];
                    $app->input->set('install_url', $url);
                    $this->getInstall($name);
                } else
                    $this->out("Fabrik Form Zoom missing files : no install");

            }

        } elseif ($name == 'hikashop') {
            $hikashop = "http://www.hikashop.com/component/updateme/updatexml/component-hikashop/level-Starter/file-extension.xml";
            $data = simplexml_load_file($hikashop);
            foreach ($data->update as $ext) {
                $version = (string)$ext->targetplatform['version'];
                #$this->out($name);
            }
            if (!strpos($version, "3")) {
                $url = (string)$ext->downloads->downloadurl[1];
                $app->input->set('install_url', $url);
                $app->input->set('installtype', 'url');
                $input = JFactory::getApplication()->input;
                $this->getInstall($name);
                $updateHelper = hikashop_get('helper.update');
                $updateHelper->installExtensions();
            }

        } elseif ($name == 'dpcalendar') {
            $url = "https://joomla.digital-peak.com/download/dpcalendar/dpcalendar-8.2.2/dpcalendar-free-8-2-2.zip?format=zip";
            $name = "dpcalendar";
            $app->input->set('install_url', $url);
            $this->getInstall($name);

        } elseif ($name == "dropfiles") {
            $dropfiles = "https://www.joomunited.com/juupdater_files/dropfiles-update.xml";
            $data = simplexml_load_file($dropfiles);
            foreach ($data->update as $ext) {
                $version = (string)$ext->targetplatform['version'];
                $name = (string)$ext->name;
                $this->out($name);
                if (strpos($version, "3")) {
                    $url = $ext->detailsurl;
                    $app->input->set('install_url', $url);
                    $this->getInstall($name);
                }
            }

        } elseif ($name == 'gantry'){
            $gantry = "https://github.com/gantry/gantry5/releases/download/5.5.5/joomla-pkg_gantry5_v5.5.5.zip";
            $name = "gantry";
            $app->input->set('install_url', $gantry);
            $this->getInstall($name);
        }
    }


    public function getInstall($extension_name){
        $installer = JModelLegacy::getInstance('InstallerModelInstall');
        $result = $installer->install();
        if (!$result) {
            JLog::add($extension_name, JLog::WARNING, 'jerror');
            $this->out("\n" . "Install failed");
        } else { $this->out("\n" . "Install OK");}
    }


    public function doExecute()
    {

        JLog::addLogger(array('text_file' => 'update_cli.log.php'), JLog::ALL, array('jerror, error'));

        $app = JFactory::getApplication('site');
        $app->initialise();
        // Set direct download mode
        $app->input->set('method', 'direct');
        $executionStartTime = microtime(true);
        $this->db = JFactory::getDbo();

        echo "Emundus Update Tool \n\n";
        if ($name = $this->input->get('i', $this->input->get('install'))) {
            $this->installExtension($app, $name);
        }
        if ($this->input->get('c', $this->input->get('core'))) {
            $this->updateJoomla();
        }
        if ($this->input->get('s', $this->input->get('sql'))) {
            $this->updateSQLJoomla();
        }
        if ($this->input->get('h', $this->input->get('help'))) {
            $this->doEchoHelp();
        }
        if ($id = $this->input->get('x', $this->input->get('extension'))) {
            $this->updateExtensions($uid = $id);
        }
        if ($this->input->get('e', $this->input->get('extensions'))) {
            $this->updateExtensions();
        }
        if ($this->input->get('l', $this->input->get('list'))) {
            $this->getInfo();
        }

        # Delete all files in tmp folder
        $path = JPATH_ROOT . '/tmp/';
        if (file_exists($path)) {
            $dir = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }
        }

        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        echo "\n" . "This script took $seconds to execute.";
    }
}

JApplicationCli::getInstance('UpdateCli')->execute();
