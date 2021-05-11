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

    /// get destination from (id)
    public function getDestinationById($id) {
        if(!empty($id)) {
            try {
                $this->query->clear()
                    ->select('#__users.*')
                    ->from($this->db->quoteName('#__users'))
                    ->where($this->db->quoteName('#__users.id') . '=' . (int)$id);
                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
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
    public function createEmailTriggerForCampaign($trigger, $users, $campaign) {
        try {
            $trigger['user'] = JFactory::getUser()->id;
            $trigger['date_time'] = date('Y-m-d H:i:s');

            //// step 1 --> emundus_setup_emails_trigger
            $this->query->clear()
                ->insert($this->db->quoteName('#__emundus_setup_emails_trigger'))
                ->columns($this->db->quoteName(array_keys($trigger)))
                ->values(implode(',', $this->db->quote(array_values($trigger))));

            $this->db->setQuery($this->query);
            $this->db->execute();
            $_triggerId = $this->db->insertid();

            /// step 2 --> emundus_setup_emails_trigger_campaign_id
            $this->query->clear()
                ->insert($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_campaign_id'))
                ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_campaign_id.parent_id') . '=' . $_triggerId)
                ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_campaign_id.campaign_id') . '=' . $campaign);
            $this->db->setQuery($this->query);
            $this->db->execute();

            /// step 3 --> emundus_setup_emails_trigger_user_id
            if(is_array($users)) {
                foreach($users as $key=>$value) {
                    /// sql scripts to insert trigger for many users
                    $this->query->clear()
                        ->insert($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                        ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id.parent_id') . '=' . $_triggerId)
                        ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id.user_id') . '=' . $this->db->quote($value));
                    $this->db->setQuery($this->query);
                    $this->db->execute();
                }
            }
            else {
                /// sql scripts to insert trigger for single user
                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                    ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id.parent_id') . '=' . $_triggerId)
                    ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id.user_id') . '=' . $this->db->quote($users));
                $this->db->setQuery($this->query);
                $this->db->execute();
            }

            return $_triggerId;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /// update email trigger by campaign id
    public function updateEmailTriggerForCampaign($trigger, $users) {
        if(!empty($trigger) and !empty($users)) {
            try {
                //// step 1 --> update table trigger (trigger, campaign_id)
                $this->query->clear()
                    ->update($this->db->quoteName('#__emundus_setup_emails_trigger'))
                    ->set($this->db->quoteName('#__emundus_setup_emails_trigger.user') . '=' . $this->db->quote(JFactory::getUser()->id))
                    ->set($this->db->quoteName('#__emundus_setup_emails_trigger.date_time') . '=' . $this->db->quote(date('Y-m-d H:i:s')))
                    ->set($this->db->quoteName('#__emundus_setup_emails_trigger.step') . '=' . $this->db->quote($trigger['step']))
                    ->set($this->db->quoteName('#__emundus_setup_emails_trigger.email_id') . '=' . $this->db->quote($trigger['email_id']))
                    ->set($this->db->quoteName('#__emundus_setup_emails_trigger.to_current_user') . '=' . $this->db->quote($trigger['to_current_user']))
                    ->set($this->db->quoteName('#__emundus_setup_emails_trigger.to_applicant') . '=' . $this->db->quote($trigger['to_applicant']))
                    ->where($this->db->quoteName('#__emundus_setup_emails_trigger.id') . '=' . $this->db->quote($trigger['id']));

                $this->db->setQuery($this->query);
                $this->db->execute();

                /// step 2 --> remove all records in emundus_setup_emails_trigger_repeat_user_id with [parent_id === trigger_id]
                $this->query->clear()
                    ->delete($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                    ->where($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id.parent_id') . '=' . $this->db->quote($trigger['id']));

                $this->db->setQuery($this->query);
                $this->db->execute();

                /// step 3 --> re-create new records in emundus_setup_emails_trigger_repeat_user_id with [parent_id === trigger_id] by $users
                if(is_array($users)) {
                    foreach($users as $key=>$value) {
                        /// sql scripts to insert trigger for many users
                        $this->query->clear()
                            ->insert($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                            ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id.parent_id') . '=' . $this->db->quote($trigger['id']))
                            ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id.user_id') . '=' . $this->db->quote($value));
                        $this->db->setQuery($this->query);
                        $this->db->execute();
                    }
                }
                else {
                    /// sql scripts to insert trigger for single user
                    $this->query->clear()
                        ->insert($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                        ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id.parent_id') . '=' . $this->db->quote($trigger['id']))
                        ->set($this->db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id.user_id') . '=' . $this->db->quote($users));
                    $this->db->setQuery($this->query);
                    $this->db->execute();
                }

                return array('data'=>true);
            }
            catch(Exception $e) {

            }
        }
        else {
            return false;
        }
    }

    /// create HTML element
    public function createElement($data, $campaign) {
        if(!empty($data)) {
            $_trigger = null;
            try {
                if($data['element_type'] === 'message') {
                    /// create trigger
                    $_trigger = $this->createEmailTriggerForCampaign(null,null, $campaign);
                }
                else { }

                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_workflow_html_element'))
                    ->columns($this->db->quoteName(array_keys($data)))
                    ->values(implode(',', $this->db->quote(array_values($data))));

                $this->db->setQuery($this->query);
                $this->db->execute();
                $_newID = $this->db->insertid();

                if(!is_null($_trigger)) {
                    return array('id' => $_newID, 'parent_id' => $data['parent_id'], 'trigger' => $_trigger);
                } else {
                    return array('id' => $_newID, 'parent_id' => $data['parent_id']);
                }
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /// update element by id
    public function updateElementById($data) {
        if(!empty($data)) {
            $_uString = "";
            //// in case of destinationSelected === other --> usersSelected is a K-V array
            if($data['params']['destinationSelected'] === 'other' && !empty($data['params']['usersSelected'])) {
                foreach($data['params']['usersSelected'] as $key => $value) {
                    if($value == "true") {
                        $_uString .= (string)$key . ",";
                        $_uLastString = substr_replace($_uString, "", -1);
                        $data['params']['usersSelected'] = $_uLastString;
                    } else {}
                }
            }

            else {
                //// do nothing here ....
            }

            try {
                $this->query->clear()
                    ->update($this->db->quoteName('#__emundus_workflow_html_element'))
                    ->set($this->db->quoteName('#__emundus_workflow_html_element.params') . '=' . $this->db->quote(json_encode($data['params'])))
                    ->where($this->db->quoteName('#__emundus_workflow_html_element.id') . '=' . $this->db->quote($data['id']));
                $this->db->setQuery($this->query);
                return $this->db->execute();
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    /// remove HTML element by id
    public function deleteElement($id) {
        if(!empty($id)) {
            try {
                $this->query->clear()
                    ->delete($this->db->quoteName('#__emundus_workflow_html_element'))
                    ->where($this->db->quoteName('#__emundus_workflow_html_element.id') . '=' . $id);
                $this->db->setQuery($this->query);
                return $this->db->execute();
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /// get all HTMl elements
    public function getAllElements() {
        try {
            $this->query->clear()
                ->select('#__emundus_workflow_html_element.*')
                ->from($this->db->quoteName('#__emundus_workflow_html_element'));

            $this->db->setQuery($this->query);
            return $this->db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /// get all HTML elements by parent type
    public function getElementsByParentType($data) {
        if(!empty($data)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_html_element.*')
                    ->from($this->db->quoteName('#__emundus_workflow_html_element'))
                    ->where($this->db->quoteName('#__emundus_workflow_html_element.parent_type') . '=' . $this->db->quote($data['parent_type']))
                    ->andWhere($this->db->quoteName('#__emundus_workflow_html_element.element_type') . '=' . $this->db->quote($data['element_type']))
                    ->andWhere($this->db->quoteName('#__emundus_workflow_html_element.workflow_id') . '=' . $this->db->quote($data['workflow_id']));

                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            } catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    /// get element by id
    public function getElementById($id) {
        if(!empty($id)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_html_element.*')
                    ->from($this->db->quoteName('#__emundus_workflow_html_element'))
                    ->where($this->db->quoteName('#__emundus_workflow_html_element.id') . '=' . (int)$id);
                $this->db->setQuery($this->query);
                return $this->db->loadObject();
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