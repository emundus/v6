<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelcommon extends JModelList {
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    //get all published forms --> use table [ jos_emundus_setup_profiles ] && published == 1
    public function getAllFormsPublished() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_profiles'))
                ->where($db->quoteName('#__emundus_setup_profiles.published = 1'));

            $db->setQuery($query);

            return $db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }


    //get all status --> use table [ jos_emundus_setup_status ]
    public function getAllStatus() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_status'));

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    //get all associated group --> use table [ jos_emundus_setup_groups ]
    public function getAllAssociatedGroup() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_groups'));

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    //get all messages --> use table
    public function getAllMessages() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_emails'));

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }
}