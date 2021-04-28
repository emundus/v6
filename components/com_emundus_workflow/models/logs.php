<?php

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModellogs extends JModelList {
    /// instance variables
    var $db = null;
    var $query = null;

    public function __construct($config = array()) {
        parent::__construct($config);

        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
    }

    // main business scripts here
}
