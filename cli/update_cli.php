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
              -i --install
            Update Filters
              -x, --extension                    Met à jour l'extension qui correspond à l'id en etnrée
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
            // For gantry canChmod() fail -> comment lines 124 to 127 in File.php
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

    public function packageComponent($component)
    {
        // Set path for component's folders
        $name       = preg_split("/[_]+/", $component, 2);
        $admin_path = JPATH_ADMINISTRATOR . "/components/" . $component . "/";
        $site_path  = JPATH_BASE . "/components/" . $component . "/";
        if (is_dir($admin_path . 'language')) {
            $fr_path    = $admin_path . 'language/fr-FR/fr-FR.' . $component . '.ini';
            $en_path    = $admin_path . 'language/en-GB/en-GB.' . $component . '.ini';
        } else {
            $fr_path = JPATH_BASE . '/language/fr-FR/fr-FR.' . $component . '.ini';
            $en_path = JPATH_BASE . '/language/en-GB/en-GB.' . $component . '.ini';
        }

        $media_path = JPATH_BASE . "/media/" . $component;
        $xml_path   = $admin_path . $name[1] . '.xml';
        // Set destination path
        $dest = JPATH_ROOT . '/tmp/' . $component;
        mkdir($dest);

        // Copy files in tmp folder
        $succes = array();
        if ($component != 'com_emundus_messenger')
        {
            $succes[] = $this->custom_copy($admin_path, $dest . '/admin');
        }
        $succes[] = $this->custom_copy($site_path, $dest . '/site');
        $succes[] = $this->custom_copy($media_path, $dest . '/media/' . $component);
        foreach ($succes as $row)
        {
            if (!$row)
            {
                echo "-> Custom copy failed";
                exit();
            }
        }
        mkdir($dest . '/language');
        #mkdir($dest . '/language/fr-FR/');
        #mkdir($dest . '/language/en-GB/');
        if ((!copy($fr_path, $dest . '/language/fr-FR.' . $component . '.ini')) || (!copy($en_path, $dest . '/language/en-GB.' . $component . '.ini')))
        {
            echo "-> Language copy failed\n";
        }
        if (!copy($xml_path, $dest . '/' . $name[1] . '.xml'))
        {
            $xml_path = $site_path . $name[1] . '.xml';
            if (!copy($xml_path, $dest . '/' . $name[1] . '.xml'))
            {
                echo '-> Xml copy failed';
                exit();
            }
        }
    }

    public function installExtension($app, $name, $token='')
    {
        $this->out('INSTALL ' . $name);
        $app = JFactory::getApplication();

        $app->input->set('installtype', 'url');
        $app->input->set('install_directory', JPATH_BASE . '/tmp');
        $app->input->set('max_upload_size', '10485760');
        if ($name == 'emundus') {
            $app->input->set('install_url', "https://git.emundus.io/emundus/cms/tchooz/-/archive/staging/tchooz-staging.zip?private_token=" . $token);
            $this->getInstall('com_emundus');
        }

        switch ($name) {
            case 'emundus':
                if (!$token) {
                    $this->out('Need to pass an authentication token as argument');
                    exit();}
                $url = "https://git.emundus.io/emundus/cms/tchooz/-/archive/staging/tchooz-staging.zip?private_token=" . $token;
                break;
            case 'fabrik':
                $url = "https://github.com/Fabrik/fabrik/archive/master.zip";
                break;
            case 'gantry':
                $url = "https://github.com/gantry/gantry5/releases/download/5.5.5/joomla-pkg_gantry5_v5.5.5.zip";
                break;
            case 'hikashop':
                $url = "";
                break;
            case 'extplorer':
                $url = "";
                break;
            case 'eventbooking':
                $url = "";
                break;
            case 'scp':
                $url = "https://securitycheck.protegetuordenador.com/component/ars/?task=download&view=Item&id=327&format=zip";
                break;
            case 'falang':
                $url = "";
                break;
            case 'jumi':
                $url = "";
                break;
            case 'dropfiles':
                $url = "https://www.joomunited.com/index.php?option=com_juupdater&task=download.download&extension=dropfiles.zip&infosite=joomunited&version=6.0.1&token=d6bbea49-24be-4fda-91c8-f64f0e44cf87&siteurl=https://vanilla.emundus.io/";
                break;
            case 'dpcalendar':
                $url = "https://joomla.digital-peak.com/download/dpcalendar/dpcalendar-8.2.2/dpcalendar-free-8-2-2.zip?format=zip";
                break;
            case 'jce':
                $url = "";
                break;
            case 'miniorange':
                $url = "";
                break;
            case 'externallogin':
                $url = "";
                break;
        }

        try {
            $p_file = JInstallerHelper::downloadPackage($url);
            if (!$p_file) {
                JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'));
                return false;
            }

            $config = JFactory::getConfig();
            $tmp_dest = $config->get('tmp_path');
            $src_dir =  basename($p_file, '.zip');


            // Unpack the downloaded package file.
            $package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file, true);

            $extractdir = $package['extractdir'] . '/'. $src_dir.'/';
            foreach (new DirectoryIterator($extractdir) as $file) {
                if ($file->isDot()) continue;
                if ((strpos($file, '.txt')) || (strpos($file, '.md'))) continue;

                $filename = $file->getFilename();
                $this->custom_copy($package['dir'] . '/' . $filename . '/', JPATH_BASE . '/' . $filename);
                echo "\n" . JPATH_BASE . '/' . $filename;
            }
        } catch (Exception $e) {
            echo $e;
        }
    }

    public function discover() {

        $discover = InstallerModel::getInstance('InstallerModelDiscover');
        $discover->discover();
        $app = JFactory::getApplication();

        $app->input->set('installtype', 'url');
        $app->input->set('install_directory', JPATH_BASE . '/tmp');
        $app->input->set('max_upload_size', '10485760');

        # Select all extensions not installed (where state = -1)
        $query = $this->db->getQuery(true);
        $query->select('extension_id')
            ->from('#__' . 'extensions')
            ->where($this->db->quoteName('state') . '= -1');
        $this->db->setQuery($query);
        $results = $this->db->loadRowList();

        foreach ($results as $res) {
            $cid[] = $res[0];
        }

        $app->input->set('cid', $cid);
        $discover->discover_install();
    }


    public function getInstall($extension_name){
        $installer = JModelLegacy::getInstance('InstallerModelInstall');
        $result = $installer->install();
        if (!$result) {
            JLog::add($extension_name, JLog::WARNING, 'jerror');
            $this->out("\n" . "Install failed");
        } else { $this->out("\n" . "Install OK");}
    }


    public function custom_copy($src, $dst)
    {
        // open the source directory
        $dir        = opendir($src);
        $copy_count = 0;
        // Make the destination directory if not exist
        @mkdir($dst, 0777, true);

        // Loop through the files in source directory
        while ($file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {

                    // Recursively calling custom copy function
                    // for sub directory
                    $this->custom_copy($src . '/' . $file, $dst . '/' . $file);

                }
                else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
            $copy_count++;
        }
        closedir($dir);
        return $copy_count != null;
    }


    public function doExecute()
    {
        JLog::addLogger(array('text_file' => 'update_cli.log.php'), JLog::ALL, array('jerror'));

        $app = JFactory::getApplication('site');
        $app->initialise();
        // Set direct download mode
        $app->input->set('method', 'direct');
        $executionStartTime = microtime(true);
        $this->db = JFactory::getDbo();
        $args = (array) $GLOBALS['argv'];

        echo "Emundus Update Tool \n\n";


        if ($name = $this->input->get('i', $this->input->get('install'))) {
            if ($name=='emundus' && sizeof($args) > 2) {
                $this->installExtension($app, $name, $args[2]);
            } else {
                $this->installExtension($app, $name);
            }
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
