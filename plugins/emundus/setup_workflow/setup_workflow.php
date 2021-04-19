<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

//require_once (JPATH_SITE.DS.'components'.DS.'com_emundus_workflow'.DS.'models'.DS.'common.php');

class plgEmundusSetup_workflow extends JPlugin {

        var $db = null;
        var $query = null;
        var $_model = null;
        var $session = null;
        var $aid = null;

        function __construct(&$subject, $config = array()) {
            parent::__construct($subject, $config);

            $this->db = JFactory::getDbo();
            $this->query = $this->db->getQuery(true);

            $this->_model = JModelLegacy::getInstance('common', 'EmundusworkflowModel');

            jimport('joomla.log.log');
            JLog::addLogger(array('text_file' => 'com_emundus.setupWorkflow.php'), JLog::ALL, array('com_emundus_setupWorkflow'));

            $this->session = JFactory::getSession();
            $this->aid = $this->session->get('emundusUser');
        }

        public function onOpenFile($fnum,$sid) {
            /// description --> when open file --> register the session $aid --> menutype // profile_id
            $this->_model->updateSessionTree($fnum,$sid);

            /// get the menu type from profile
            $this->_model->getMenuTypeByProfile($this->aid->profile);

            //update the user profile
            $this->_model->updateUserProfile($fnum, $this->aid->profile);
        }
    }
