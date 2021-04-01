<?php
    defined('_JEXEC') or die('Restricted access');

    class plgEmundusSetup_workflow extends JPlugin {

        var $db;
        var $query;

        function __construct(&$subject, $config = array()) {
            parent::__construct($subject, $config);

            $this->db = JFactory::getDbo();
            $this->query = $this->db->getQuery(true);

            jimport('joomla.log.log');
            JLog::addLogger(array('text_file' => 'com_emundus.setupWorkflow.php'), JLog::ALL, array('com_emundus_setupWorkflow'));
        }

        public function onOpenFile($fnum) {
            /// no need to go further if the plugin type is wrong or fnum is empty
            if(empty($fnum)) {
                return false;
            }
            else {
                try {
                    $query = $this->db->getQuery(true);
                    $query->select('#__emundus_workflow.id, #__emundus_workflow.workflow_name')
                        ->from($this->db->quoteName('#__emundus_workflow'))
                        ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') .
                            'ON' . $this->db->quoteName('#__emundus_workflow.campaign_id') .
                            '='  . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id')
                        )
                        ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);
                    $this->db->setQuery($query);

                    if(empty($this->db->loadObject())) {
                        JLog::add('No associated workflow found', JLog::ERROR, 'com_emundus_setupWorkflow');
                        return false;
                    }
                    else {
                        return $this->db->loadObject();
                    }
                }

                catch(Exception $e) {
                    JLog::add('Could not get associated workflow -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                    return false;
                }
            }
        }

        public function getWorkflowByCampaignID($cid) {
            try {
                $query = $this->db->getQuery(true);
                $query->select('*')
                    ->from($this->db->quoteName('#__emundus_workflow'))
                    ->where($this->db->quoteName('#__emundus_workflow.campaign_id') . '=' . (int)$cid);
                $this->db->setQuery($query);
                return $this->db->loadObject();
            }
            catch(Exception $e) {
                JLog::add('Could not get associated workflow -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }

        //get profile_id from workflow_id // status_id
        public function getProfileByWorkflowIDStatusID($wid, $cid) {
            $query = $this->db->getQuery(true);
            $query->select('#__emundus_workflow_item.id, #__emundus_workflow_item.params')
                ->from($this->db->quoteName('#__emundus_workflow_item'))
                ->where($this->db->quoteName('#__emundus_workflow_item.item_id') . '=' . 2)
                ->andWhere($this->db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$wid);
            $this->db->setQuery($query);
            $_params = $this->db->loadObjectList();

            try {
                for ($i = 0; $i <= count($_params); $i++) {
                    if (((json_decode($_params[$i]->params))->editedStatusSelected) !== $cid) {
                        unset($_params[$i]);
                    }
                }
                return $_params;
            }
            catch(Exception $e) {
                JLog::add('Could not get profile id by workflow and status -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }

        public function getWorkflowProfileByFnum($fnum, $cid=null) {
            $query = $this->db->getQuery(true);
            $query->select('#__emundus_workflow.id, #__emundus_workflow_item.params')
                ->from($this->db->quoteName('#__emundus_workflow_item'))
                ->leftJoin($this->db->quoteName('#__emundus_workflow') . 'ON' . $this->db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . $this->db->quoteName('#__emundus_workflow.id'))
                ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_workflow.campaign_id') . '=' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id'))
                ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);

            $this->db->setQuery($query);
            $_rawData =  $this->db->loadObjectList();

            $_workflowID = $_rawData[0]->id;

            unset($_rawData[0]);
            
            $_inputStatusList = array();
            $_outputStatusList = array();

            $_exportData = array();

            $_inArrayString = "";
            $_outArrayString = "";

            // stock session here
            $session = JFactory::getSession();
            $aid = $session->get('emundusUser');
            //

            try {
                for ($i = 1; $i <= count($_rawData); $i++) {
                    $_inArray = explode(',', (json_decode($_rawData[$i]->params))->inputStatus);
                    $_outArray = explode(',', (json_decode($_rawData[$i]->params))->outputStatus);

                    array_push($_inputStatusList, $_inArray);
                    array_push($_outputStatusList, $_outArray);

                    foreach ($_outArray as $key => $value) {
                        $_outArrayString .=  $value . ',';
                    }

                    /// if dossier status exists in inputStatusList --> return profile id
                    foreach ($_inArray as $key => $value) {
                        $_inArrayString .=  $value . ',';
                        if(in_array($cid,$_inArray)) {
                            if ($value == $cid) {
                                array_push($_exportData, $_rawData[$i]);
                                continue;
                            }
                        }
                        else {}
                    }
                }

                $_inArrayString = explode(',', substr_replace($_inArrayString ,"",-1));
                $_outArrayString = explode(',', substr_replace($_outArrayString ,"",-1));

                $aid->workflow = $_workflowID;

                if(isset(json_decode($_exportData[0]->params)->formNameSelected)) {
                    $aid->profile = json_decode($_exportData[0]->params)->formNameSelected;
                }
                else {
                    if (!in_array($cid, $_inArrayString) and in_array($cid, $_outArrayString)) {
                        $_exportData = $this->getFirstProfileByWorkflow($_workflowID);
                        $aid->profile = $_exportData['profile_id'];
                    } else if (!in_array($cid, $_inArrayString) and !in_array($cid, $_outArrayString)) {
                        $_exportData = $this->getProfileFromUnexistantStatus($cid);
                        $aid->profile = $_exportData[0]->profile_id;
                    }
                }

                return 0;
            }

            catch(Exception $e) {
                JLog::add('Could not get profile id by workflow and status -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }

        public function getFirstProfileByWorkflow($wid) {
            $query = $this->db->getQuery(true);

            //get the first profile by workflow
            try {
                $query->select('MIN(id), #__emundus_workflow_item.params')
                    ->from($this->db->quoteName('#__emundus_workflow_item'))
                    ->where($this->db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$wid)
                    ->andWhere($this->db->quoteName('#__emundus_workflow_item.item_id') . '=' . 2);

                $this->db->setQuery($query);

                $_data = $this->db->loadAssoc();

                $_exportData['profile_id'] = json_decode($_data['params'])->formNameSelected;

                return $_exportData;
            }
            catch(Exception $e) {
                JLog::add('Could not get first profile id by workflow -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }

        }

        public function getProfileFromUnexistantStatus($sid) {
            //// status doesn't exist --> get profile by campaign [[ one and only one profile ]]
            $query = $this->db->getQuery(true);

            try {
                $query->select('#__emundus_setup_campaigns.profile_id, #__emundus_workflow.id')
                    ->from($this->db->quoteName('#__emundus_setup_campaigns'))

                    ->leftJoin($this->db->quoteName('#__emundus_workflow') . 'ON' .
                        $this->db->quoteName('#__emundus_workflow.campaign_id') . '=' . $this->db->quoteName('#__emundus_setup_campaigns.id'))

                    ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' .
                        $this->db->quoteName('#__emundus_setup_campaigns.id') . '=' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id'))
                    ->where($this->db->quoteName('#__emundus_campaign_candidature.status') . '=' . (int)$sid);

                $this->db->setQuery($query);

                return $this->db->loadObjectList();
            }

            catch(Exception $e) {
                JLog::add('Could not get profile by status -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }

        public function getMenuTypeByProfile($pid) {
            $query = $this->db->getQuery(true);

            try {
                $query->select('#__emundus_setup_profiles.*')
                    ->from($this->db->quoteName('#__emundus_setup_profiles'))
                    ->where($this->db->quoteName('#__emundus_setup_profiles.id') . '=' . (int)$pid);
                $this->db->setQuery($query);
                return $this->db->loadObjectList()[0]->menutype;
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }

        public function updateEmundusUserProfile($fnum, $pid) {
            $query_get_userid = $this->db->getQuery(true);
            $query_update_profile_by_userid = $this->db->getQuery(true);

            try {
                $query_get_userid->select('#__emundus_users.id')
                    ->from($this->db->quoteName('#__emundus_users'))
                    ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_campaign_candidature.applicant_id') . '=' . $this->db->quoteName('#__emundus_users.user_id'))
                    ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);
                $this->db->setQuery($query_get_userid);
                $_uid = $this->db->loadObject()->id;

                $query_update_profile_by_userid->update($this->db->quoteName('#__emundus_users'))
                    ->set($this->db->quoteName('#__emundus_users.profile') . '=' . (int) $pid)
                    ->where($this->db->quoteName('#__emundus_users.id') . '=' . (int) $_uid);
                $this->db->setQuery($query_update_profile_by_userid);
                return $this->db->execute();
            }

            catch(Exception $e) {
                return $e->getMessage();
            }
        }
    }
