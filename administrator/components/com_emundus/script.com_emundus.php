<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once JPATH_CONFIGURATION . '/configuration.php';


class com_emundusInstallerScript
{
    protected $firstrun = false;


    public function __construct() {
        # Get component manifest cache
        $this->db = JFactory::getDBO();
        $query = $this->db->getQuery(true);
        $query->select('manifest_cache')
            ->from('#__extensions')
            ->where("element = 'com_emundus'");
        $this->db->setQuery($query);
    }


    /**
     * @param $type
     * @param $parent
     * @return void
     */
    public function install($type, $parent)
    {
    }


    /**
     * @return void
     */
    public function uninstall()
    {
    }


    /**
     * @param $parent
     * @return void
     */
    public function update($parent)
    {
        $manifest_cache = json_decode($this->db->loadObject()->manifest_cache);
        $cache_version = $manifest_cache->version;
        # Check first run
        if ($cache_version == "6.1") {
            $cache_version = (string) $parent->manifest->version;
            $firstrun = true;
        }

        if ($manifest_cache) {
            # First run condition
            if (version_compare($cache_version, '1.33.0', '<') OR $firstrun) {
                # Delete emundus sql files in con_admin
                # $this->deleteOldSqlFiles();

                # Non generic
                $this->updateModulesParams('mod_emundusflow','show_programme' , "0");
                # Change cron fabrik params
                $this->updateFabrikCronParams('Application not sent','log' , "0");
                $this->updateFabrikCronParams('Application not sent','log_email' , "mail@emundus.fr");
                $this->updateFabrikCronParams('Application not sent','cron_rungate' , "1");

                # Update SCP params
                $this->updateSCPParams("pro_plugin", "email_active", "0" );
                $this->updateSCPParams("pro_plugin", "email_on_admin_login", "0" );

                # Generic
                $this->genericUpdateParams("#__modules", "module", "mod_emundusflow", "show_programme", "0");
                $this->genericUpdateParams("#__fabrik_cron", "label", 'Application not sent','log' , "0");
                $this->genericUpdateParams("#__fabrik_cron", "label", 'Application not sent','log_email' , "mail@emundus.fr");
                $this->genericUpdateParams("#__fabrik_cron", "label", 'Application not sent','cron_rungate' , "1");
                #$this->genericUpdateParams("#__securitycheckpro_storage", "storage_key", "pro_plugin", "email_active", "0", array('storage_value', 'storage_key'));
                #$this->genericUpdateParams("#__securitycheckpro_storage", "storage_key", "pro_plugin", "email_on_admin_login", "0", array('storage_value', 'storage_key'));

                # Update lifetime in configuration.php
                $this->updateConfigurationFile("lifetime", "45");
            }
        }
    }


    /**
     * @param $type
     * @param $parent
     * @return void
     */
    public function preflight($type, $parent)
    {
        # Check if PHP version match with extension
        $version = explode('.',PHP_VERSION);
        if($version[0] < 7 || ($version[0] == 7 && $version[1] < 4)) {
            echo '<html><body><h1>This extension works with PHP 7.4.0 or newer.</h1>'.
                '<h2>Please contact your web hosting provider to update your PHP version</h2>'.
                'installation aborted...</body></html>';
            exit;
        }
    }


    /**
     * @param $type
     * @param $parent
     * @return void
     */
    function postflight($type, $parent)
    {
        echo 'Installation terminée avec succès';
    }



    /**
     * Delete -em files in com_admin schemapath
     * @return void
     */
    private function deleteOldSqlFiles() {
        $source = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/mysql';
        $files = scandir($source);
        foreach ($files as $file) {
            if (strpos($file, 'em') !== false AND is_file($file)) JFile::delete($file);
        }
    }


    /**
     * Disable Emundus Plugins
     *
     * @return bool
     */
    protected function getEmundusPlugins()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('*')
            ->from('#__extensions')
            ->where("folder LIKE '%emundus%' OR element LIKE " . $db->q('%emundus%') . " AND type='plugin'");
        $db->setQuery($query)->execute();
        return $this->db->loadObjectList();
    }

    /**
     * Disable Emundus Plugins
     *
     * @return bool
     */
    protected function disableEmundusPlugins($name)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->update('#__extensions')
            ->set('enabled = 0')
            ->where("element LIKE " . $db->q('%'. $name .'%'));

        return $db->setQuery($query)->execute();
    }

    private function updateModulesParams($name, $param, $value) {
        $table = "#__modules";
        $query = $this->db->getQuery(true);
        $this->db->getQuery(true);
        $query->select('id,params')
            ->from($table)
            ->where('module LIKE ' . $this->db->q('%'.$name.'%'));
        $this->db->setQuery($query);
        $rows =  $this->db->loadObjectList();

        foreach ($rows as $row) {
            $params = json_decode($row->params,true);
            $params[$param] = $value;
            # Assign new params value
            $paramsString = json_encode( $params );
            $this->db->setQuery('UPDATE ' . $table . ' SET params = ' .
                $this->db->quote($paramsString) .
                ' WHERE id = ' . $row->id);
            $this->db->query();
        }
    }

    private function genericUpdateParams($table, $where, $name, $param, $valueToSet, $updateParams = null) {
        if (!$updateParams[0]) {
            $updateParams[0] = "params";
        }

        if (!$updateParams[1]) {
            $updateParams[1] = "id";
        }
        $query = $this->db->getQuery(true);
        $this->db->getQuery(true);
        $query->select('*')
            ->from($table)
            ->where($where. ' LIKE ' . $this->db->q('%'.$name.'%'));
        $this->db->setQuery($query);
        $rows =  $this->db->loadObjectList();
        foreach ($rows as $row) {
            $params = json_decode($row->params,true);
            $params[$param] = $valueToSet;
            # Assign new params value
            $paramsString = json_encode( $params );
            $this->db->setQuery('UPDATE ' . $table . ' SET '. $updateParams[0] .' = ' .
                $this->db->quote($paramsString) .
                ' WHERE ' . $updateParams[1] . ' = ' . $row->id );
            $this->db->query();
        }
    }

    private function updateFabrikCronParams($name, $param, $value)
    {
        $table = "#__fabrik_cron";
        $query = $this->db->getQuery(true);
        $this->db->getQuery(true);
        $query->select('id,params')
            ->from($table)
            ->where('label LIKE ' . $this->db->q('%' . $name . '%'));
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList();

        foreach ($rows as $row) {
            $params = json_decode($row->params, true);
            $params[$param] = $value;
            # Assign new params value
            $paramsString = json_encode($params);
            $this->db->setQuery("UPDATE " . $table . " SET params = " .
                $this->db->quote($paramsString) .
                " WHERE id = '" . $row->id . "'");
            $this->db->query();
        }
    }

    private function updateSCPParams($name, $param, $value)
    {
        $table = "#__securitycheckpro_storage";

        $query = $this->db->getQuery(true);
        $this->db->getQuery(true);
        $query->select('storage_key,storage_value')
            ->from($table)
            ->where('storage_key LIKE ' . $this->db->q('%' . $name . '%'));
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList();

        foreach ($rows as $row) {
            $params = json_decode($row->storage_value, true);
            $params[$param] = $value;
            # Assign new params value
            $paramsString = json_encode($params);
            $this->db->setQuery("UPDATE " . $table . " SET storage_value = " .
                $this->db->quote($paramsString) .
                " WHERE storage_key = '" . $row->storage_key . "'");
            $this->db->query();
        }
    }

    private function updateConfigurationFile($param, $value) {
        $formatter = new JRegistryFormatPHP();
        $this->config = new JConfig();
        $this->config->$param = $value;
        $params = array('class' => 'JConfig', 'closingtag' => false);
        $str = $formatter->objectToString($this->config, $params);
        file_put_contents(JPATH_CONFIGURATION . '/configuration.php', $str);

    }




}
