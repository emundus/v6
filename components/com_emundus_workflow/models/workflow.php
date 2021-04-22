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

class EmundusworkflowModelworkflow extends JModelList {
    var $db = null;
    var $query = null;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
    }

    //// get all workflows
    public function getAllWorkflows() {
        try {
            $this->query->clear()
                ->select('#__emundus_workflow.*, #__emundus_setup_campaigns.label, #__users.name')
                ->from($this->db->quoteName('#__emundus_workflow'))
                ->leftJoin('#__emundus_setup_campaigns ON #__emundus_setup_campaigns.id = #__emundus_workflow.campaign_id')
                ->leftJoin('#__users ON #__users.id = #__emundus_workflow.user_id');

            $this->db->setQuery($this->query);
            return $this->db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot get all workflow' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    /// delete workflow by id
    public function deleteWorkflow($wid) {
        try{
            $this->query->clear()
                ->delete($this->db->quoteName('#__emundus_workflow'))
                ->where(('#__emundus_workflow.id') . ' = ' . (int)$wid);

            $this->db->setQuery($this->query);
            return $this->db->execute();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot delete workflow' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //// create workflow --> update the last_saved
    public function createWorkflow($data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if(!empty($data)) {
            try {
                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_workflow'))
                    ->columns($this->db->quoteName(array_keys($data)))
                    ->values(implode(',', $this->db->quote(array_values($data))));

                $this->db->setQuery($this->query);
                $this->db->execute();
                return $this->db->insertid();

            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/workflow | Cannot create new workflow : ' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    /// get workflow by id
    public function getWorkflowByID($wid) {
        if(!empty($wid) or isset($wid)) {
            try {
                $this->query->clear()
                    ->select('*')
                    ->from($this->db->quoteName('#__emundus_workflow'))
                    ->where($this->db->quoteName('#__emundus_workflow.id') . ' = ' . (int)$wid);
                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/workflow | Cannot get workflow by id : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    /// change the workflow name --> update
    public function updateWorkflowLabel($data) {
        if(!empty($data) or isset($data)) {
            try {
                $this->query->update($this->db->quoteName('#__emundus_workflow'))
                    ->set($this->db->quoteName('#__emundus_workflow.workflow_name') . '=' . $this->db->quote($data['workflow_name']))
                    ->where($this->db->quoteName('#__emundus_workflow.id') . '=' . (int)$data['id']);

                $this->db->setQuery($this->query);
                $this->db->execute();

                $this->workflowSavingTrigger((int)$data['id']);

                return (object)['message' => true];
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/workflow | Cannot update the workflow name : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// get available campaigns
    public function getAllAvailableCampaigns() {
        try {
            $this->query
                ->select('#__emundus_setup_campaigns.id, #__emundus_setup_campaigns.label')
                ->from($this->db->quoteName('#__emundus_setup_campaigns'))
                ->leftJoin($this->db->quoteName('#__emundus_workflow') . ' ON ' . $this->db->quoteName('#__emundus_setup_campaigns.id') . '=' . $this->db->quoteName('#__emundus_workflow.campaign_id'))
                ->where($this->db->quoteName('#__emundus_setup_campaigns.id') . ' NOT IN (SELECT #__emundus_workflow.campaign_id FROM #__emundus_workflow) ');

            $this->db->setQuery($this->query);
            return $this->db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot get available campaigns : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //// saving trigger
    public function workflowSavingTrigger($wid) {
        if(!empty($wid)) {
            try {
                $this->query->clear()
                    ->update($this->db->quoteName('#__emundus_workflow'))
                    ->set($this->db->quoteName('#__emundus_workflow.updated_at') . '=' . $this->db->quote(date('Y-m-d H:i:s')) .
                        ',' . $this->db->quoteName('#__emundus_workflow.user_id') . '=' . (JFactory::getUser())->id)
                    ->where($this->db->quoteName('#__emundus_workflow.id') . '=' . (int)$wid);

                $this->db->setQuery($this->query);
                return $this->db->execute();
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/workflow | Cannot activate the saving trigger : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }
}
