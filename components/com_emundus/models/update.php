<?php
/**
 * @package         Joomla
 * @subpackage      eMundus
 * @link            http://www.emundus.fr
 * @copyright       Copyright (C) 2015 eMundus. All rights reserved.
 * @license         GNU/GPL
 * @author          James Dean
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelUpdate extends JModelLegacy {
    private $db;
    private $user;

    public function __construct()
    {
        $this->db = JFactory::getDbo();
        $this->user = JFactory::getUser();

    }

/// Client Accepts the update
    public function setIgnoreVal($version) {
        $query = $this->db->getQuery(true);

        // only change the ignore value to the new update to then hide the update module.
        $fields = array(
            $this->db->quoteName('ignore') . ' = ' . $version
        );

        $query
            ->update($this->db->quoteName('#__emundus_version'))
            ->set($fields);

        $this->db->setQuery($query);

        try {
            $this->db->execute();
            return true;
        } catch(Exception $e) {
            JLog::add('Error getting account type stats from mod_graphs helper at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

}