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

        //// from 2 params ==> fnum
        public function getProfileByFnum($fnum,$sid=null) {
            if(!empty($fnum) or isset($fnum)) {
                try {
                    $this->query
                        ->select('#__emundus_workflow_step.*')
                        ->from($this->db->quoteName('#__emundus_workflow_step'))
                        ->leftJoin($this->db->quoteName('#__emundus_workflow') . 'ON' . $this->db->quoteName('#__emundus_workflow.id') . '=' . $this->db->quoteName('#__emundus_workflow_step.workflow_id'))
                        ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_workflow.campaign_id') . '=' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id'))
                        ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);

                    /// right now, we will get step.id, step.workflow_id, step.params
                    $this->db->setQuery($this->query);
                    $_results = $this->db->loadObjectList();

                    $_tempInputList = "";
                    $_tempOutputList = "";

                    $session = JFactory::getSession();
                    $aid = $session->get('emundusUser');

                    $aid->workflow = $_results[0]->id;

                    foreach($_results as $key => $value) {
                        $_tempInputList .= json_decode($value->params)->inputStatus . ',';
                        $_tempOutputList .= json_decode($value->params)->outputStatus . ',';

                        /// input status is in one of the step flow --> get this step id --> ....
                        if((json_decode($value->params)->inputStatus) == $sid) {
                            $_stepID = $value->id;
                            $this->getProfileByStep($_stepID);  //// get profile by step id

                            $aid->profile = json_decode($this->getProfileByStep($_stepID)[0]->params)->formNameSelected;       // profile_id
                            $aid->step_id = $_stepID;
                            $aid->message = 'step and profile founded';
                            continue;          ///stop the loop
                        }

                        else { }
                    }

                    /// trim the last character
                    $_inArrayString = explode(',', substr_replace($_tempInputList ,"",-1));
                    $_outArrayString = explode(',', substr_replace($_tempOutputList ,"",-1));

                    //// if the input status is not in $_inArrayString, but in $_outArrayString
                    if(!in_array($sid, $_inArrayString) && in_array($sid, $_outArrayString)) {
                        // get the first profile of this step flow
//                        $this->getFirstProfileByStep($sid);
                        $this->getFirstProfileOfFirstStep($sid);

                        $aid->profile = json_decode($this->getFirstProfileOfFirstStep($sid)->params)->formNameSelected;
                        $aid->step_id = null;
                        $aid->message = 'get the first profile of the first step';
                    }
                    else if(!in_array($sid, $_inArrayString) && !in_array($sid, $_outArrayString)) {
                        // get the default profile of this campaign
                        $this->getDefaultProfileByFnum($fnum);
                        $aid->profile = $this->getDefaultProfileByFnum($fnum);
                        $aid->step_id = null;
                        $aid->message = 'get the default profile of campaign';
                    }
                    else {
                        ////
                    }

                    return $aid;
                }
                catch(Exception $e) {
                    JLog::add('Could not get profile by fnum -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //// get the profile if from step flow
        public function getProfileByStep($step) {
            if(!empty($step) or isset($step)) {
                try {
                    $this->query->clear()
                        ->select('#__emundus_workflow_item.*')
                        ->from($this->db->quoteName('#__emundus_workflow_item'))
                        ->leftJoin($this->db->quoteName('#__emundus_workflow_step') . 'ON' . $this->db->quoteName('#__emundus_workflow_item.step_id') . '=' . $this->db->quoteName('#__emundus_workflow_step.id'))
                        ->where($this->db->quoteName('#__emundus_workflow_item.item_id') . '=' . 2)
                        ->andWhere($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$step);
                    $this->db->setQuery($this->query);
                    return $this->db->loadObjectList();
                }
                catch(Exception $e) {
                    JLog::add('Could not get profile by step -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //// get the first profile id of the first step of workflow
        public function getFirstProfileOfFirstStep($step) {
            if(!empty($step) or isset($step)) {
                try {
                    /// get the first step
                    $this->query->clear()
                        ->select('min(#__emundus_workflow_step.id)')
                        ->from($this->db->quoteName('#__emundus_workflow_step'));
                    $this->db->setQuery($this->query);
                    $_minStepID = $this->db->loadResult();

                    /// from the step id, get the first profile of this step
                    return $this->getFirstProfileByStep($_minStepID);
                }
                catch(Exception $e) {
                    JLog::add('Could not get the first profile from the first step -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //// get the first profile of a step flow --> get the min(id) && item_id == 2
        public function getFirstProfileByStep($step) {
            if(!empty($step) or isset($step)) {
                try {
                    $this->query->clear()
                        ->select('MIN(#__emundus_workflow_item.id), #__emundus_workflow_item.params')
                        ->from('#__emundus_workflow_item')
                        ->leftJoin($this->db->quoteName('#__emundus_workflow_step') . 'ON' . $this->db->quoteName('#__emundus_workflow_step.id') . '=' . $this->db->quoteName('#__emundus_workflow_item.step_id'))
                        ->where($this->db->quoteName('#__emundus_workflow_item.item_id') .'=' . 2);

                    $this->db->setQuery($this->query);
                    return $this->db->loadObject();
                }
                catch(Exception $e) {
                    JLog::add('Could not get the first profile by step -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //// get the default profile of by fnum
        public function getDefaultProfileByFnum($fnum) {
            if(!empty($fnum) or isset($fnum)) {
                try {
                    $this->query->clear()
                        ->select('#__emundus_setup_profiles.*')
                        ->from($this->db->quoteName('#__emundus_setup_profiles'))
                        ->leftJoin($this->db->quoteName('#__emundus_setup_campaigns') . 'ON' . $this->db->quoteName('#__emundus_setup_profiles.id') . '=' . $this->db->quoteName('#__emundus_setup_campaigns.profile_id'))
                        ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_setup_campaigns.id') . '=' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id'))
                        ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);

                    $this->db->setQuery($this->query);
                    return $this->db->loadResult();
                }
                catch(Exception $e) {
                    JLog::add('Could not get default profile by campaign -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //// get the menu type from profile id
        public function getMenuTypeByProfile($pid) {

            if(!empty($pid) or isset($pid)) {
                try {
                    $this->query->clear()
                        ->select('#__emundus_setup_profiles.*')
                        ->from($this->db->quoteName('#__emundus_setup_profiles'))
                        ->where($this->db->quoteName('#__emundus_setup_profiles.id') . '=' . (int)$pid);
                    $this->db->setQuery($this->query);
                    return $this->db->loadObject(); /// return the menu type
                }
                catch(Exception $e) {
                    JLog::add('Could not get menu type by profile -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //// update the profile id from fnum and profile id --> table jos_emundus_users
        public function updateUserProfile($fnum,$pid) {
            if(!empty($fnum) or isset($fnum)) {
                try {
                    /// first query --> get the user_id by fnum
                    $this->query->clear()
                        ->select('#__emundus_campaign_candidature.*')
                        ->from($this->db->quoteName('#__emundus_campaign_candidature'))
                        ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);

                    $this->db->setQuery($this->query);
                    $_userID = $this->db->loadObject()->user_id;

                    /// second query --> update the data of jos_emundus_users from $_userID
                    $this->query->clear()
                        ->update('#__emundus_users')
                        ->set($this->db->quoteName('#__emundus_users.profile') . '=' . (int)$pid)
                        ->where($this->db->quoteName('#__emundus_users.user_id') . '=' . (int)$_userID);

                    $this->db->setQuery($this->query);
                    $this->db->execute();
                }
                catch(Exception $e) {
                    JLog::add('Could not update the user profile -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }
    }
