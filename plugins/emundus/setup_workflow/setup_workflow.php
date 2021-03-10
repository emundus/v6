<?php
    defined('_JEXEC') or die('Restricted access');

    class plgEmundusWorkflow_setup_workflow extends JPlugin {

        var $db;
        var $query;

        function __construct(&$subject, $config = array()) {
            parent::__construct($subject, $config);

            $this->db = JFactory::getDbo();
            $this->query = $this->db->getQuery(true);

            jimport('joomla.log.log');
        }

        ////////
    }
