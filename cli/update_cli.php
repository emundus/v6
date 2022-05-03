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
        return $this->db->loadAssocList('', );
    }


    public function queryFromUid($table, $uid)
    {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . $table)
            ->where($this->db->quoteName('extension_id') . ' LIKE ' . $uid);
        $this->db->setQuery($query);
        return $this->db->loadAssocList('');
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
        //$this->purgeAndFetchUpdates();
        $query = $this->db->getQuery(true);

            //Exclude Joomla & Gantry5
        $query->select("u.extension_id, u.update_id,u.element, u.type, u.version, e.name")
            ->where("u.extension_id NOT LIKE 0")
            ->from($this->db->quoteName('#__updates','u'))
            ->join('LEFT', $this->db->quoteName('#__extensions', 'e') . ' ON u.extension_id = e.extension_id');

        $this->db->setQuery($query);
        $arr = $this->db->loadAssocList();
        $mask = "|%5s |%25s | %25s | %35s | %10s | %5s\n";
        printf($mask, 'Id', 'Uid','Element', 'Name','Type', 'Version');

        $key = array_values($arr);

        foreach ($key as $k) {
            printf($mask, $k['extension_id'], $k['update_id'],$k['element'],$k['name'],$k['type'], $k['version']);
        }
    }

    public function doEchoHelp() {
        echo <<<EOHELP
            Joomla! CLI Update DB
            
            Operations
              -l, --list               Liste les composants avec une mise à jour disponible
              -h, --help               Help
              -i, --install             Installe l'extension
              -u, --update             Met à jour l'extension qui correspond à l'id en entrée
              -c, --core               Mise à jour Joomla
              -d, --database           Mise à jour SGBD Joomla
            
            EOHELP;
    }


    public function updateExtensions($id = null) {
        $ext = $this->queryFromUid('extensions', $id);
        $this->out('UPDATE EXTENSIONS : ' . $ext[0]['name']);
        //$this->purgeAndFetchUpdates();

        # Update by extension id
        if ($id != null) {
            $uid = $this->queryFromUid('updates',$id);
            if ($uid != null){
                $this->out("Update found");
            }

        } else {
            $uid = $this->getUpdateId('updates');
            $this->out(sizeof($uid) . " updates found");
        }
        if ($uid == null) {
            $this->out("No update for this extension");
            return false;
        }

        foreach ($uid as $u) {
            $model = JModelLegacy::getInstance('InstallerModelUpdate');
            $component = JComponentHelper::getComponent('com_installer');
            $params = $component->params;
            $minimum_stability = (int)$params->get('minimum_stability', JUpdater::STABILITY_STABLE);

            echo 'Update : ' . $u['name'] . "\n";
            $u = array($u['update_id']);
            // For gantry canChmod() fail -> comment lines 124 to 127 in File.php
            $model->update($u, $minimum_stability);
            if ($model->getState()->result) {
                $this->out("update success");
            } else {
                $this->out('update fails');
            };
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


    public function installExtension($app, $name, $token='')
    {
        $this->out('INSTALL ' . $name);
        $app = JFactory::getApplication();

        $app->input->set('installtype', 'url');
        $app->input->set('install_directory', JPATH_BASE . '/tmp');
        $app->input->set('max_upload_size', '10485760');

        switch ($name) {
            case 'emundus':
                if (!$token) {
                    $this->out('Need to pass an authentication token as argument');
                    exit();}
                $url = "https://git.emundus.io/emundus/cms/tchooz/-/archive/staging/tchooz-staging.zip?private_token=" . $token;
                break;
            case 'fabrik':
                $url = "https://fabrikar.com/index.php?option=com_fabrik&task=plugin.pluginAjax&plugin=fileupload&method=ajax_download&format=raw&element_id=31&formid=3&rowid=3796&repeatcount=0&ajaxIndex=0";
                #$url = "https://github.com/Fabrik/fabrik/archive/master.zip";
                break;
            case 'gantry':
                $url = "https://github.com/gantry/gantry5/releases/download/5.5.5/joomla-pkg_gantry5_v5.5.5.zip";
                break;
            case 'scp':
                // need pro version
                $url = "https://securitycheck.protegetuordenador.com/downloads/securitycheck/securitycheck-3-4-5/com_securitycheck-3-4.5.zip?format=zip";
                #$url = "https://securitycheck.protegetuordenador.com/component/ars/?task=download&view=Item&id=327&format=zip";
                break;
            case 'falang':
                $url = "https://www.faboba.com/component/ars/?view=download&id=397&dummy=my.zip&dlid=1d6d65391429d126157ed4c78f4d3108";
                break;
            case 'dropfiles':
                $url = "https://www.joomunited.com/index.php?option=com_juupdater&task=download.download&extension=dropfiles.zip&infosite=joomunited&version=6.0.1&token=d6bbea49-24be-4fda-91c8-f64f0e44cf87&siteurl=https://vanilla.emundus.io/";
                break;
            case 'dpcalendar':
                $url = "https://joomla.digital-peak.com/download/dpcalendar/dpcalendar-8.2.2/dpcalendar-free-8-2-2.zip?format=zip";
                break;
            /*            case 'hikashop':
                            $url = "";
                            break;
                        case 'jumi':
                            $url = "";
                            break;
                        case 'extplorer':
                            $url = "";
                            break;
                        case 'eventbooking':
                            $url = "";
                            break;
                        case 'jce':
                            $url = "";
                            break;
                        case 'miniorange':
                            $url = "";
                            break;
                        case 'externallogin':
                            $url = "";
                            break;*/
        }

        try {
            $app->input->set('install_url', $url);
            $this->installFromUrl($name);

        } catch (Exception $e) {
            echo $e;
        }
    }


    public function installFromUrl($extension_name){
        $installer = JModelLegacy::getInstance('InstallerModelInstall');
        $result = $installer->install();
        if (!$result) {
            JLog::add($extension_name, JLog::WARNING, 'jerror');

            $this->out("\n" . "Install failed");
        } else { $this->out("\n" . "Install OK");}
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
            if ($name=='emundus' && sizeof($args) == 4) {
                $this->installExtension($app, $name, $args[3]);
            } else {
                $this->installExtension($app, $name);
            }
        }
        if ($this->input->get('c', $this->input->get('core'))) {
            $this->updateJoomla();
        }
        if ($this->input->get('d', $this->input->get('database'))) {
            $this->updateSQLJoomla();
        }
        if ($this->input->get('u', $this->input->get('extension'))) {
            if (sizeof($args) == 2) {
                $this->updateExtensions();
            } elseif (sizeof($args) >= 3) {
                $index = 2;
                while ($index <= sizeof($args)-1) {
                    $id = $args[$index];
                    $this->updateExtensions($uid = $id);
                    $index++;
                }

            }
        }
        if ($this->input->get('h', $this->input->get('help'))) {
            $this->doEchoHelp();
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
