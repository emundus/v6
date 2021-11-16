<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelworkflows extends JModelList {
    var $db = null;
    var $query = null;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
    }

    //// get all workflows --> use alias "ew" === emundus_workflow
    public function getAllWorkflows() {
        try {
            $this->query->clear()
                ->select('distinct ecw.*, esc.label')
                ->from($this->db->quoteName('#__emundus_campaign_workflow', 'ecw'))
                ->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'esc') . 'ON' . $this->db->quoteName('esc.id') . '=' . $this->db->quoteName('ecw.campaign'));

            $this->db->setQuery($this->query);
            return $this->db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot get all workflow' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }
}
