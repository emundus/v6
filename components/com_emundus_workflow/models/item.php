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

class EmundusworkflowModelitem extends JModelList
{
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    //get all items from database
    public function getAllItems() {
        $db = JFactory::getDbo();
        $query =$db->getQuery(true);

        try {
            //query string
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_workflow_item_type'));

            //execute query string
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get all item types' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //get all items by workflow id --> restore workflow
    // select * from jos_emundus_workflow_item where jos_emundus_workflow_item.workflow_id = 28;
    public function getAllItemsByWorkflowId($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        try {
            $query->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$id);

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get all item by workflow' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    public function getInitIDByWorkflow($wid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$wid)
                ->andWhere($db->quoteName('#__emundus_workflow_item.item_id') . '=' . 1);

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get init item by workflow' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    public function getCountItemByID($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('count(*)')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.item_id') . ' = ' . (int)$data['item_id'])
                ->andWhere($db->quoteName('#__emundus_workflow_item.workflow_id') . ' = ' . (int)$data['workflow_id']);


            $db->setQuery($query);
            return $db->loadResult();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot count items' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //create new item --> params = type, name
    public function createItem($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($data)) {


            $query->clear()
                ->insert($db->quoteName('#__emundus_workflow_item'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->quote(array_values($data))));

            try {
                $db->setQuery($query);
                $db->execute();
                return $db->insertid();
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot create new item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //delete selected item --> params = data
    public function deleteItem($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try{
            $query->clear()
                ->delete($db->quoteName("#__emundus_workflow_item"))
                ->where($db->quoteName('#__emundus_workflow_item.id') . ' = ' . (int)$data);

            $db->setQuery($query);

            return $db->execute();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot delete item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //get element by id
    public function getItemByID($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.id') . ' = ' . (int)$id);

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //save all items
    public function saveItems($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $data['last_saved'] = date('Y-m-d H:i:s');

        try {
            $query
                ->update($db->quoteName('#__emundus_workflow_item'))
                ->set($db->quoteName('#__emundus_workflow_item.axisX') . '=' . $data['axisX'] .
                    ',' . $db->quoteName('#__emundus_workflow_item.axisY') . '=' . $data['axisY'] .
                    ',' . $db->quoteName('#__emundus_workflow_item.item_label') . '=' . $db->quote($data['item_label']) .
                    ',' . $db->quoteName('#__emundus_workflow_item.last_saved') . '=' . $db->quote($data['last_saved']))
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$data['id']);

            $db->setQuery($query);
            return $db->execute();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot save item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }
}
