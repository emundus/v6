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

    // get destinations by (list) of id
    public function getDestinationsByIds($data) {
        if(!empty($data)) {
            try {
                if(count(explode(',', $data)) == 1) {
                    $this->query->clear()
                        ->select('#__emundus_setup_groups.*')
                        ->from($this->db->quoteName('#__emundus_setup_groups'))
                        ->where($this->db->quoteName('#__emundus_setup_groups.id') . '=' . (int)$data);
                    $this->db->setQuery($this->query);
                    return $this->db->loadObject();
                } else {
                    $this->query->clear()
                        ->select('#__users.*')
                        ->from($this->db->quoteName('#__users'))
                        ->where($this->db->quoteName('#__users.id') . 'IN (' . $data . ')');
                    $this->db->setQuery($this->query);
                    return $this->db->loadObjectList();
                }
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
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

    /// create new trigger (jos_emundus_setup_emails_trigger_repeat_campaign_id))
    public function createEmailTriggerForCampaign($trigger, $users) {
        if(!empty($trigger)) {
            try {
                $trigger['user'] = JFactory::getUser()->id;
                $trigger['date_time'] = date('Y-m-d H:i:s');
                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_setup_emails_trigger'))
                    ->columns($this->db->quoteName(array_keys($trigger)))
                    ->values(implode(',', $this->db->quote(array_values($trigger))));

                $this->db->setQuery($this->query);
                $this->db->execute();
                return $this->db->insertid();
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }
}