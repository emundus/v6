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
    }
