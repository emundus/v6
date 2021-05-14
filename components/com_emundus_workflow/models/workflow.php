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

    //// get all workflows --> use alias "ew" === emundus_workflow
    public function getAllWorkflows() {
        try {
            $this->query->clear()
                ->select('ew.*, esc.label, u.name')
                ->from($this->db->quoteName('#__emundus_workflow', 'ew'))
                ->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'esc') . 'ON' . $this->db->quoteName('esc.id') . '=' . $this->db->quoteName('ew.campaign_id'))
                ->leftJoin($this->db->quoteName('#__users', 'u') . 'ON' . $this->db->quoteName('u.id') . '=' . $this->db->quoteName('ew.user_id'));

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
        if(!empty($wid)) {
            try {
                $this->query->clear()
                    ->delete($this->db->quoteName('#__emundus_workflow'))
                    ->where($this->db->quoteName('#__emundus_workflow.id') . ' = ' . (int)$wid);

                $this->db->setQuery($this->query);
                return $this->db->execute();
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/workflow | Cannot delete workflow' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// create workflow --> update the created_at, saved_at, last_activity
    public function createWorkflow($data) {
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
        if(!empty($wid)) {
            try {
                $this->query->clear()
                    ->select('ew.*, esc.id')
                    ->from($this->db->quoteName('#__emundus_workflow', 'ew'))
                    ->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'esc') . 'ON' . $this->db->quoteName('ew.campaign_id') . '=' . $this->db->quoteName('esc.id'))
                    ->where($this->db->quoteName('ew.id') . ' = ' . (int)$wid);
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
        if(!empty($data)) {
            try {
                $this->query->update($this->db->quoteName('#__emundus_workflow'))
                    ->set($this->db->quoteName('#__emundus_workflow.workflow_name') . '=' . $this->db->quote($data['workflow_name']))
                    ->where($this->db->quoteName('#__emundus_workflow.id') . '=' . (int)$data['id']);

                $this->db->setQuery($this->query);
                $this->db->execute();

                $this->workflowLastActivity((int)$data['id']);
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
                ->select('esc.id, esc.label')
                ->from($this->db->quoteName('#__emundus_setup_campaigns', 'esc'))
                ->leftJoin($this->db->quoteName('#__emundus_workflow', 'ew') . ' ON ' . $this->db->quoteName('esc.id') . '=' . $this->db->quoteName('ew.campaign_id'))
                ->where($this->db->quoteName('esc.id') . ' NOT IN (SELECT #__emundus_workflow.campaign_id FROM #__emundus_workflow) ');

            $this->db->setQuery($this->query);
            return $this->db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot get available campaigns : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //// saving trigger
    public function workflowLastActivity($wid, $option=null) {
        if(!empty($wid)) {
            try {
                if(is_null($option)) {
                    $this->query->clear()
                        ->update($this->db->quoteName('#__emundus_workflow'))
                        ->set($this->db->quoteName('#__emundus_workflow.last_activity') . '=' . $this->db->quote(date('Y-m-d H:i:s')) .
                            ',' . $this->db->quoteName('#__emundus_workflow.user_id') . '=' . (JFactory::getUser())->id)
                        ->where($this->db->quoteName('#__emundus_workflow.id') . '=' . (int)$wid);
                }

                else if($option == "saved_at") {
                    $this->query->clear()
                        ->update($this->db->quoteName('#__emundus_workflow'))
                        ->set($this->db->quoteName('#__emundus_workflow.saved_at') . '=' . $this->db->quote(date('Y-m-d H:i:s')) .
                            ',' . $this->db->quoteName('#__emundus_workflow.user_id') . '=' . (JFactory::getUser())->id)
                        ->where($this->db->quoteName('#__emundus_workflow.id') . '=' . (int)$wid);
                }
                else {
                    exit;
                }

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
