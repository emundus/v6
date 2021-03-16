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
use Joomla\CMS\Date\Date;

//Objectif de ce modele --> CRUD

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelworkflow extends JModelList
{
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    // GET ALL
    public function getAllWorkflows() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            //query string
            $query->clear()
                ->select('#__emundus_workflow.*, #__emundus_setup_campaigns.label, #__users.name')
                ->from($db->quoteName('#__emundus_workflow'))
                ->leftJoin('#__emundus_setup_campaigns ON #__emundus_setup_campaigns.id = #__emundus_workflow.campaign_id')
                ->leftJoin('#__users ON #__users.id = #__emundus_workflow.user_id');

            //execute query string
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot get all workflow' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

//    // GET COUNT ALL WORKFLOWS
//    public function getCountAllWorkflows() {
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);
//
//        try {
//            $query->clear()
//                ->select('count(*)')
//                ->from($db->quoteName('#__emundus_workflow'));
//            $db->setQuery($query);
//            return $db->loadResult();
//        }
//        catch(Exception $e) {
//            JLog::add('component/com_emundus_workflow/models/workflow | Cannot get count all workflows' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
//            return $e->getMessage();
//        }
//    }

    //DELETE WORKFLOW BY ID
    public function deleteWorkflow($wid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try{
            $query->clear()
                ->delete($db->quoteName('#__emundus_workflow'))
                ->where(('#__emundus_workflow.id') . ' = ' . (int)$wid);

            $db->setQuery($query);
            return $db->execute();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot delete workflow' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //CREATE WORKFLOW
    public function createWorkflow($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $data['updated_at'] = date('Y-m-d H:i:s');
        if(!empty($data)) {
            try {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_workflow'))
                    ->columns($db->quoteName(array_keys($data)))
                    ->values(implode(',', $db->quote(array_values($data))));

                $db->setQuery($query);
                $db->execute();
                return $db->insertid();

            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/workflow | Cannot create new workflow : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //GET A WORKFLOW BY ID
    public function getWorkflowByID($wid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try{
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_workflow'))
                ->where($db->quoteName('#__emundus_workflow.id') . ' = ' . (int)$wid);
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot get workflow by id : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //UPDATE LAST SAVING TIME OF WORKFLOW BY ID
    public function updateLastSavingWorkflow($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->update($db->quoteName('#__emundus_workflow'))
                ->set($db->quoteName('#__emundus_workflow.updated_at') . '=' . $db->quote(date('Y-m-d H:i:s')) .
                    ',' . $db->quoteName('#__emundus_workflow.user_id') . '=' . (JFactory::getUser())->id
                )
                ->where($db->quoteName('#__emundus_workflow.id') . '=' . (int)$data);
            $db->setQuery($query);
            return $db->execute();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot track workflow last saved : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //GET CAMPAIGN BY ID
    public function getCampaignByID($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_campaigns'))
                ->where($db->quoteName('#__emundus_setup_campaigns.id') . '=' . (int)$data);

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    //CHANGE WORKFLOW NAME --> UPDATE ALL LOGS
    public function updateWorkflow($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update($db->quoteName('#__emundus_workflow'))
                ->set($db->quoteName('#__emundus_workflow.workflow_name') . '=' . $db->quote($data['workflow_name']) .
                    ',' . $db->quoteName('#__emundus_workflow.updated_at') . '=' . $db->quote(date('Y-m-d H:i:s')) .
                    ',' . $db->quoteName('#__emundus_workflow.user_id') . '=' . (JFactory::getUser())->id)
                ->where($db->quoteName('#__emundus_workflow.id') . '=' . (int)$data['id']);

//            var_dump($query->__toString());die;
            $db->setQuery($query);
            return $db->execute();
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
