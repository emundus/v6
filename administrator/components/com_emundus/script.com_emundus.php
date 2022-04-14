<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


class com_emundusInstallerScript
{
    public function __construct() {
        $this->db = JFactory::getDBO();

        $query = $this->db->getQuery(true);
        $query->select('manifest_cache')
            ->from('#__extensions')
            ->where("element = 'com_emundus'");
        $this->db->setQuery($query);
    }


    public function install()
    {
    }


    public function uninstall()
    {

    }


    public function updateSQL()
    {
        $manifest = json_decode($this->db->loadObject()->manifest_cache);
        $version = null;
        if ($manifest) {
            // First run condition
            if (version_compare($manifest->version, '6.9.0', '=')) {
                echo "\n--> Update function from script.com_emundus : 6.9.0";
                $version = '6.9.0';
            }
            if (version_compare($manifest->version, '6.9.1', '<')) {
                echo "\n--> Update function from script.com_emundus : 6.9.1";
                $version = '6.9.1';
            }
            if (version_compare($manifest->version, '6.9.2', '<')) {
                echo "\n--> Update function from script.com_emundus : 6.9.2";
                $version = '6.9.2';
            }
        } if ($version == null) {
            echo "--> Nothing to update";
    }
        return $version;
    }



    public function preflight()
    {

    }


    function postflight()
    {

    }
}
