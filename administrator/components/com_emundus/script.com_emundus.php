<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once JPATH_CONFIGURATION . '/configuration.php';


class com_emundusInstallerScript
{
    protected $manifest_cache;
    protected EmundusHelperUpdate $h_update;

    public function __construct() {
        // Get component manifest cache
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('manifest_cache')
            ->from('#__extensions')
            ->where("element = 'com_emundus'");
        $db->setQuery($query);
        $this->manifest_cache = json_decode($db->loadObject()->manifest_cache);

        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');
        $this->h_update = new EmundusHelperUpdate();
    }


    /**
     * @param $type
     * @param $parent
     *
     *
     * @since version 1.33
     */
    public function install($type, $parent)
    {
    }


    /**
     * Actions to run if we uninstall eMundus component
     *
     * @since version 1.33.0
     */
    public function uninstall()
    {
    }


    /**
     * @param $parent
     *
     *
     * @since version 1.33.0
     */
    public function update($parent)
    {
        $cache_version = $this->manifest_cache->version;

        # Check first run
        $firstrun = false;
        if ($cache_version == "6.1") {
            $cache_version = (string) $parent->manifest->version;
            $firstrun = true;
        }

        if ($this->manifest_cache) {
            # First run condition
            if (version_compare($cache_version, '1.33.0', '<') || $firstrun) {
                # Delete emundus sql files in con_admin
                #$this->deleteOldSqlFiles();

                $this->h_update->updateModulesParams("mod_emundusflow","show_programme" , "0");
                $this->h_update->updateFabrikCronParams("emundusrecall",array("log","log_email","cron_rungate") , array("0","mail@emundus.fr","1"));
                # Update SCP params
                $this->h_update->updateSCPParams("pro_plugin", array("email_active","email_on_admin_login"), array("0","0"));

                $this->h_update->genericUpdateParams("#__modules", "module", "mod_emundusflow", array("show_programme"), array("0"));
                $this->h_update->genericUpdateParams("#__fabrik_cron", "plugin", "emundusrecall", array("log", "log_email", "cron_rungate") , array("0", "mail@emundus.fr", "1"));

                $this->h_update->updateConfigurationFile("lifetime", "30");
            }
        }
    }


    /**
     * @param $type
     * @param $parent
     *
     *
     * @since version 1.33.0
     */
    public function preflight($type, $parent)
    {
        if(version_compare(PHP_VERSION, '7.4.0', '<')) {
            echo '<html><body><h1>This extension works with PHP 7.4.0 or newer.</h1>'.
                '<h2>Please contact your web hosting provider to update your PHP version</h2>'.
                'installation aborted...</body></html>';
            exit;
        }
    }


    /**
     * @param $type
     * @param $parent
     *
     *
     * @since version 1.33.0
     */
    function postflight($type, $parent)
    {
        echo 'Installation terminée avec succès';
    }


    /**
     * Delete old SQL files named ...-em
     *
     * @since version 1.33.0
     */
    private function deleteOldSqlFiles() {
        $source = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/mysql';
        if ($files = scandir($source)) {
            foreach ($files as $file) {
                if (strpos($file, 'em') !== false AND is_file($file)) JFile::delete($file);
            }
        } else {
            echo("Can't scan SQL Files");
        }
    }
}
