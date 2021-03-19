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

        //code improving --> from fnum, get campaign_id // workflow_id // status_id --> profile_id from workflow_id
        public function getWorkflowProfileByFnum($fnum, $cid=null) {
            $query = $this->db->getQuery(true);
            $query->select('#__emundus_workflow.id, #__emundus_workflow_item.params')
                ->from($this->db->quoteName('#__emundus_workflow_item'))
                ->leftJoin($this->db->quoteName('#__emundus_workflow') . 'ON' . $this->db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . $this->db->quoteName('#__emundus_workflow.id'))
                ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_workflow.campaign_id') . '=' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id'))
                ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);

            $this->db->setQuery($query);
            $_rawData =  $this->db->loadObjectList();

            $session = JFactory::getSession();
            $aid = $session->get('emundusUser');

            try {
                for ($i = 0; $i <= count($_rawData); $i++) {
                    if (((json_decode($_rawData[$i]->params))->editedStatusSelected) !== $cid) {
                        unset($_rawData[$i]);
                    }
                    else {
                        $aid->profile = json_decode($_rawData[$i]->params)->formNameSelected;
                        $aid->workflow = $_rawData[$i]->id;
                    }
                }

                return $_rawData;
            }
            catch(Exception $e) {
                JLog::add('Could not get profile id by workflow and status -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }
    }
