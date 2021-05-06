<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelcommon extends JModelList {
    var $db = null;
    var $query = null;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
    }

    //get all associated group --> use table [ jos_emundus_setup_groups ]
    public function getAllDestinations() {
        try {
            $this->query->clear()
                ->select('*')
                ->from($this->db->quoteName('#__emundus_setup_groups'));

            $this->db->setQuery($this->query);
            return $this->db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    //get destination by id
    public function getDestinationById($did) {
        try {
            $this->query->clear()
                ->select('#__emundus_setup_groups.*')
                ->from($this->db->quoteName('#__emundus_setup_groups'))
                ->where($this->db->quoteName('#__emundus_setup_groups.id') . '=' . (int)$did);
            $this->db->setQuery($this->query);
            return $this->db->loadObject();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /// get all users --> for testing (remove it when finishing)
    public function getAllUsers() {
        try {
            $this->query->clear()
                ->select('#__emundus_users.*, #__users.*')
                ->from($this->db->quoteName('#__emundus_users'))
                ->leftJoin($this->db->quoteName('#__users') . ' ON ' . $this->db->quoteName('#__emundus_users.user_id') . '=' . $this->db->quoteName('#__users.id'));

            $this->db->setQuery($this->query);
            return $this->db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }
}