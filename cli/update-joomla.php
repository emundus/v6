<?php

use Joomla\Utilities\ArrayHelper;

const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

define('JPATH_BASE', dirname(__DIR__));

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';
require_once JPATH_LIBRARIES . '/src/Updater/Updater.php';
// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

//define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_joomlaupdate');
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/');

require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_joomlaupdate/models/default.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . 'com_installer/models/update.php';

class Upgradejoomla extends JApplicationCli
{

    private function getExtensionsId($table) {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__' . $table)
            ->where($this->db->quoteName('extension_id') . ' NOT LIKE 0 AND' . ($this->db->quoteName('extension_id') . ' NOT LIKE 700'));
        $this->db->setQuery($query);
        return $this->db->loadAssocList('','update_id');
    }

    public function updateExtensions() {
        $this->out('UPDATE EXTENSIONS...');

        $model = JModelLegacy::getInstance('InstallerModelUpdate');
        $uid = $this->getExtensionsId('updates');
        $uid = ArrayHelper::toInteger($uid, array());

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

    public function updateJoomla() {
        $this->out('UPDATE JOOMLA...');

        // Get a Joomla-instance so createResorationFile() doesn't complain too much.

        // com_joomlaupdate's model
        $updater = JModelLegacy::getInstance('JoomlaupdateModelDefault');

        // Make sure we know what the latest version is
        $updater->refreshUpdates();
        $updater->applyUpdateSite();

        // Return a null-object if this is the case
        $version_check = $updater->getUpdateInformation();
        if (is_null($version_check['object'])) {
            echo 'No new updates available' . "\n";
            return 0;
        }

        $this->out('Fetching updates...');

        // Grab the update (ends up in /tmp)
        $basename = $updater->download();
        $file = $basename['basename'];

        // Create restoration.php (ends up in /com_joomlaupdate)
        $updater->createRestorationFile($basename);
        $this->out('Creating restoration...');

        //TODO: Complete restoration process

        // Extract files to core directory
        $zip = new ZipArchive;
            if ($zip->open(JPATH_ROOT . '/tmp/Joomla_3.10.3-Stable-Update_Package.zip') === TRUE) {
                $zip->extractTo(JPATH_ROOT);
                $zip->close();
            }
        $this->out('Sucess...');

        // Update SQL etc based on the manifest file we got with the update
        $updater->finaliseUpgrade();
        $this->out('Finalize...');

        $updater->cleanUp();
        $this->out('Cleanup...');
    }

    public function doEchoHelp()
    {
        echo <<<EOHELP
            Joomla! CLI Update DB
            
            Operations
              -u, --update                Run Update
              -l, --list                  List Components
              -h, --help                  Help
              
            Update Filters
              -i, --id ID                 Update component/extension by ID
              -a, --all                   All Components
              -c, --core                  Composants Joomla
            
            
            EOHELP;
    }

    public function doExecute()
    {
        $app = JFactory::getApplication('site');
        $app->initialise();

        // Set direct download mode
        $app->input->set('method', 'direct');

        $executionStartTime = microtime(true);

//
        $this->db = JFactory::getDbo();
//        # List extensions from schema table
//        $this->com_schemas = $this->getExtensionsId('schemas');
//        # List extensions from extensions table
//        $this->com_extensions = $this->getExtensionsId('extensions');
//        # List components type from extensions table
//        $this->com_components = $this->getComponentsId('extensions');
        echo "Emundus SQL Update Tool \n\n";

        # List components available for update
        if ($this->input->get('l', $this->input->get('list'))) {
            $this->getInfo();
        }

        if ($this->input->get('e', $this->input->get('extensions'))) {
            $this->updateExtensions();
        }

        if ($this->input->get('c', $this->input->get('core'))) {
            $this->updateJoomla();
        }

        if ($this->input->get('h', $this->input->get('help'))) {
            $this->doEchoHelp();
        }

        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        echo "\n" . "This script took $seconds to execute.";
    }
}


JApplicationCli::getInstance('Upgradejoomla')->execute();