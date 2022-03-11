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
        $res = $this->db->loadObject();
        $manifest = json_decode($res->manifest_cache);

        if ($manifest) {
            if (version_compare($manifest->version, '1.29.0', '>=')) {
                echo '--> Update function from script.com_emundus';
            }
        }
    }



    public function preflight()
    {

    }


    function postflight()
    {

    }
}
