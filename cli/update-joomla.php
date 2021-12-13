<?php
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

define('JPATH_BASE', dirname(__DIR__));

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_joomlaupdate');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/default.php';

class Upgradejoomla extends JApplicationCli
{
    public function doExecute() {

        // Get a Joomla-instance so createResorationFile() doesn't complain too much.
        $app = JFactory::getApplication('site');
        $app->initialise();

        // Set direct download mode
        $app->input->set('method', 'direct');

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
//        $zip = new ZipArchive;
//            if ($zip->open(JPATH_ROOT . '/tmp/Joomla_3.10.3-Stable-Update_Package.zip') === TRUE) {
//                $zip->extractTo(JPATH_ROOT);
//                $zip->close();
//            }
        $this->out('Sucess...');

        // Update SQL etc based on the manifest file we got with the update
        $updater->finaliseUpgrade();
        $this->out('Finalize...');

        $updater->cleanUp();
        $this->out('Cleanup...');

    }
}


JApplicationCli::getInstance('Upgradejoomla')->execute();