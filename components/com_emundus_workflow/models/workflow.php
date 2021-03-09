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

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelworkflow extends JModelList
{
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    // fix left join
    public function getAllWorkflows() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            //query string
            $query->clear()
                ->select('#__emundus_workflow.*,#__emundus_setup_campaigns.label')
                ->from($db->quoteName('#__emundus_workflow'))
                ->leftJoin('#__emundus_setup_campaigns ON #__emundus_setup_campaigns.id = #__emundus_workflow.campaign_id');

            //execute query string
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot get all workflow' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //delete workflow --> params:workflow_id [fix]
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

    //create workflow -> campaign_id, user_id, created_at, updated_at
    public function createWorkflow($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

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
                JLog::add('component/com_emundus_workflow/models/item | Cannot create new workflow : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //get workflow by id

    //restore workflow
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
            JLog::add('component/com_emundus_workflow/models/item | Cannot get workflow by id : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }


}
