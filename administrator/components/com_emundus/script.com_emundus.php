<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once JPATH_CONFIGURATION . '/configuration.php';


class com_emundusInstallerScript
{
    protected $firstrun = false;
    protected $manifest_cache;
    private JDatabaseDriver $db;

    public function __construct() {
        # Get component manifest cache
        $this->db = JFactory::getDBO();
        $query = $this->db->getQuery(true);
        $query->select('manifest_cache')
            ->from('#__extensions')
            ->where("element = 'com_emundus'");
        $this->db->setQuery($query);
        $this->manifest_cache = json_decode($this->db->loadObject()->manifest_cache);

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
        $cache_version = $this->manifest_cache->version;
        # Check first run
        if ($cache_version == "6.1") {
            $cache_version = (string) $parent->manifest->version;
            $firstrun = true;
        }

        if ($this->manifest_cache) {
            # First run condition
            if (version_compare($cache_version, '1.33.0', '<') || $firstrun) {
                # Delete emundus sql files in con_admin
                #$this->deleteOldSqlFiles();

                $this->updateModulesParams("mod_emundusflow","show_programme" , "0");

                $this->updateFabrikCronParams("emundusrecall",array("log","log_email","cron_rungate") , array("0","mail@emundus.fr","1"));

                $this->updateSCPParams("pro_plugin", array("email_active","email_on_admin_login"), array("0","0"));

                $this->genericUpdateParams("#__modules", "module", "mod_emundusflow", array("show_programme"), array("0"));
                $this->genericUpdateParams("#__fabrik_cron", "plugin", "emundusrecall", array("log", "log_email", "cron_rungate") , array("0", "mail@emundus.fr", "1"));

                $this->updateConfigurationFile("lifetime", "30");

                // Update Gantry5 configuration file for PHP8 compatibility
                $this->updateYamlVariable('offcanvas','16rem',JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml','width');
                $this->updateYamlVariable('breakpoints','48rem',JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml','mobile-menu-breakpoint');
                $this->updateYamlVariable('menu','11rem',JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml','col-width');
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
        if ($files = scandir($source)) {
            foreach ($files as $file) {
                if (strpos($file, 'em') !== false AND is_file($file)) JFile::delete($file);
            }
        } else {
            echo("Can't scan SQL Files");
        }
    }


    /**
     * Disable Emundus Plugins
     *
     * @return bool
     */
    protected function getEmundusPlugins()
    {
        try {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('#__extensions')
                ->where("folder LIKE '%emundus%' OR element LIKE " . $db->q('%emundus%') . " AND type='plugin'");
            $db->setQuery($query);
        } catch (Exception $e){
            echo $e->getMessage();
        }
        return $this->db->loadObjectList();
    }

    /**
     * Disable Emundus Plugins
     *
     * @return bool
     */
    protected function disableEmundusPlugins($name){
        try {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('#__extensions')
                ->set('enabled = 0')
                ->where("element LIKE " . $db->q('%'. $name .'%'));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $db->setQuery($query)->execute();
    }

    private function updateModulesParams($name, $param, $value) {
        try {
            $query = $this->db->getQuery(true);
            $query->select('id,params')
                ->from("#__modules")
                ->where('module LIKE ' . $this->db->q('%'.$name.'%'));
            $this->db->setQuery($query);
            $rows =  $this->db->loadObjectList();
            $query->clear();

            foreach ($rows as $row) {
                $params = json_decode($row->params,true);
                $params[$param] = $value;
                $query->update("#__modules")
                    ->set("params = " . $this->db->quote(json_encode($params)))
                    ->where("id = " . $row->id);
                $this->db->setQuery($query);
                $this->db->execute();
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function genericUpdateParams($table, $where, $name, $param, $valuesToSet, $updateParams = null) {
        if (empty($updateParams[0])) {
            $updateParams[0] = "params";
        }
        if (empty($updateParams[1])) {
            $updateParams[1] = "id";
        }
        try {
            $query = $this->db->getQuery(true);
            $query->select('*')
                ->from($table)
                ->where($where. ' LIKE ' . $this->db->q('%'.$name.'%'));
            $this->db->setQuery($query);
            $rows =  $this->db->loadObjectList();
            $query->clear();
            foreach ($rows as $row) {
                $params = json_decode($row->params,true);
                foreach ($param as $k => $par) {
                    $params[$par] = $valuesToSet[$k];
                    $query->update($table)
                        ->set($updateParams[0] . ' = ' . $this->db->quote(json_encode($params)))
                        ->where($updateParams[1] . ' = ' . $row->id);
                    $this->db->setQuery($query);
                    $this->db->execute();
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function updateFabrikCronParams($name, $param, $value)
    {
        try {

        $query = $this->db->getQuery(true);
        $query->select('id,params')
            ->from("#__fabrik_cron")
            ->where('plugin LIKE ' . $this->db->q('%' . $name . '%'));
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList();

        foreach ($rows as $row) {
            $params = json_decode($row->params, true);
            $params[$param] = $value;
            # Assign new params value
            $query->update("#__fabrik_cron")
                ->set("params = " . $this->db->quote(json_encode($params)))
                ->where("id = " . $row->id);
            $this->db->setQuery($query);
            $this->db->execute();
        }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function updateSCPParams($name, $param, $value)
    {
        try {
            $query = $this->db->getQuery(true);
            $query->select('storage_key,storage_value')
                ->from("#__securitycheckpro_storage")
                ->where('storage_key LIKE ' . $this->db->q('%' . $name . '%'));
            $this->db->setQuery($query);
            $rows = $this->db->loadObjectList();

            foreach ($rows as $row) {
                $params = json_decode($row->storage_value, true);
                $params[$param] = $value;
                # Assign new params value
                $paramsString = json_encode($params);
                $query->update("#__securitycheckpro_storage")
                    ->set("storage_value = " .$this->db->quote($paramsString))
                    ->where("storage_key = " . $row->storage_key);
                $this->db->setQuery($query);
                $this->db->execute();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function updateConfigurationFile($param, $value) {
        $formatter = new JRegistryFormatPHP();
        $this->config = new JConfig();
        $this->config->$param = $value;
        $params = array('class' => 'JConfig', 'closingtag' => false);
        $str = $formatter->objectToString($this->config, $params);

        $config_file = JPATH_CONFIGURATION . '/configuration.php';
        if (file_exists($config_file) and is_writable($config_file)){
            file_put_contents($config_file,$str);
        } else {
            echo ("Update Configuration file failed");
        }
    }

    private function updateYamlVariable($key1,$value,$file,$key2 = null){
        $yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));

        if(!empty($key2) && isset($yaml[$key1])){
            if(isset($yaml[$key1][$key2])) {
                $yaml[$key1][$key2] = $value;
            }
        } elseif (isset($yaml[$key1])){
            $yaml[$key1] = $value;
        } else {
            echo ("Key " . $key1 . ' not found in file ' . $file);
        }

        $new_yaml = \Symfony\Component\Yaml\Yaml::dump($yaml);

        file_put_contents($file, $new_yaml);
    }
}
