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

class Upgradejoomla extends JApplicationCli
{

    private function getExtensionsId($table) {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . $table)
            //Exclude Joomla & Gantry5
            ->where($this->db->quoteName('extension_id') . ' NOT LIKE 0 AND' . ($this->db->quoteName('extension_id') . ' NOT LIKE 700 AND') . ($this->db->quoteName('extension_id') . ' NOT LIKE 11970'));
        $this->db->setQuery($query);
        return $this->db->loadAssocList('','update_id');
    }

    public function purgeAndFetchUpdates(){
        // Get the update cache time
        $component = JComponentHelper::getComponent('com_installer');
        $updater = JUpdater::getInstance();
        $model = JModelLegacy::getInstance('InstallerModelUpdate');
        $params = $component->params;
        $cache_timeout = $params->get('cachetimeout', 6, 'int');
        $cache_timeout = 3600 * $cache_timeout;

        // Purge all updates
        $this->out('Purge updates...');
        $model->purge();
        // Find all updates
        $this->out('Fetching updates...');
        $updater->findUpdates(0, $cache_timeout);

    }

    public function getInfo() {
        $this->purgeAndFetchUpdates();

        $this->out('Fetching info...');
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . 'updates')
            //Exclude Joomla & Gantry5
            ->where($this->db->quoteName('extension_id') . ' NOT LIKE 0 AND' . ($this->db->quoteName('extension_id') . ' NOT LIKE 700 AND') . ($this->db->quoteName('extension_id') . ' NOT LIKE 11970'));
        $this->db->setQuery($query);
        $arr = $this->db->loadAssocList();

        $key = array_values($arr);
        foreach ($key as $k){
            echo $k['extension_id'] . ' --> ' . $k['name']. ' (version : ' . $k['version'] .')' . "\n";
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

    public function updateExtensions($uid=null) {
        $this->out('UPDATE EXTENSIONS...');

        # Update by extension id
        if ($uid!=null) {
            $query = $this->db->getQuery(true);
            $query->select('*')
                ->from('#__' . 'updates')
                //Exclude Joomla & Gantry5
                ->where($this->db->quoteName('extension_id') . ' LIKE '. $uid);
            $this->db->setQuery($query);
            $uid = $this->db->loadAssocList('','update_id');

        } else {
            $uid = $this->getExtensionsId('updates');
        }
        if($uid==null){
            $this->out("No update for this extension");
            return false;
        } else {
            $this->out("Update found");
        }
        $uid = ArrayHelper::toInteger($uid, array());


        $model = JModelLegacy::getInstance('InstallerModelUpdate');


        $component     = JComponentHelper::getComponent('com_installer');
        $params        = $component->params;
        $cache_timeout = (int) $params->get('cachetimeout', 6);
        $cache_timeout = 3600 * $cache_timeout;
        $minimum_stability = (int) $params->get('minimum_stability', JUpdater::STABILITY_STABLE);

        $this->out('Update...');
        $model->update($uid, $minimum_stability);

        $this->out('Purge...');
        $model->purge();

        $this->out('Check update list...');
        $model->findUpdates(0, $cache_timeout, $minimum_stability);

        $this->out('Sucess...');

    }

    public function updateSQLJoomla(){
        $updater = JModelLegacy::getInstance('JoomlaupdateModelDefault');
        $updater->refreshUpdates();
        $updater->applyUpdateSite();
        $res = $updater->finaliseUpgrade();
        if($res==1){
            echo "SQL Update Success...";
        } else{
            echo "SQL Update Failed...";
        }
    }

    public function updateJoomla() {
        $this->out('UPDATE JOOMLA...');
        $updater = JModelLegacy::getInstance('JoomlaupdateModelDefault');

        // Make sure we know what the latest version is
        $this->purgeAndFetchUpdates();
        // Return a null-object if this is the case
        $version_check = $updater->getUpdateInformation();
        if (is_null($version_check['object'])) {
            echo 'No new updates available' . "\n";
            return 0;
        }

        // Grab the update (ends up in /tmp)
        $basename = $updater->download();
        $file = $basename['basename'];

        // Create restoration.php (ends up in /com_joomlaupdate)
        $updater->createRestorationFile($basename);
        $this->out('Creating restoration...');

        //TODO: Complete restoration process

        // Extract files to core directory
        $path = JPATH_ROOT . '/tmp/' . $basename['basename'];
        try {

            $zip = new ZipArchive;
            if ($zip->open($path) === TRUE) {
                $zip->extractTo(JPATH_ROOT);
                $zip->close();
            }
        } catch (Exception $e) {
            echo "! Query for setVersion() fail\n";
        }
        $this->out('Sucess...');

        // Update SQL etc based on the manifest file we got with the update
        $this->out('Finalize...');
        $res = $updater->finaliseUpgrade();
        if($res==1){
            echo "SQL Update Success...";
        } else{
            echo "SQL Update Failed...";
        }

        $updater->cleanUp();
        $this->out('Cleanup...');
    }

    public function doExecute() {
        $app = JFactory::getApplication('site');
        $app->initialise();
        // Set direct download mode
        $app->input->set('method', 'direct');
        $executionStartTime = microtime(true);
        $this->db = JFactory::getDbo();

        echo "Emundus SQL Update Tool \n\n";
        if ($this->input->get('c', $this->input->get('core'))) {
            $this->updateJoomla();
        }
        if ($this->input->get('s', $this->input->get('sql'))) {
            $this->updateSQLJoomla();
        }
        if ($this->input->get('h', $this->input->get('help'))) {
            $this->doEchoHelp();
        }
        if ($id = $this->input->get('i', $this->input->get('id'))) {
            $this->updateExtensions($uid=$id);
        }
        if ($this->input->get('e', $this->input->get('extensions'))) {
            $this->updateExtensions();
        }
        if ($this->input->get('l', $this->input->get('list'))) {
            $this->getInfo();
        }


        # Delete all files in tmp folder
        $path = JPATH_ROOT . '/tmp/';
        if(file_exists($path)) {
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

JApplicationCli::getInstance('Upgradejoomla')->execute();